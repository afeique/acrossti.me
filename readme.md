# acrosstime

## target environments
Linux, Apache 2.2, PHP 5.5, MySQL 5.1  
staging: http://dev.acrossti.me/  
production: http://acrossti.me/  

## local development environment covered in guide
Ubuntu 12.04, Ubuntu 14.04, Windows 7  
Apache 2.4, PHP 5.5, MySQL 5.5  
local development: http://ldev.acrossti.me/  
local testing: http://l.acrossti.me/

**These are virtualhosts setup on your local development environment.**

### differences between environments

The "local development" environment is one where error output has been enabled 
in PHP and Symfony is running in debug mode. Ideally, if you have the xdebug 
PHP extension, you will also be able to set breakpoints and step through code 
to more effectively isolate issues.

In contrast, "local testing" is configured to mirror the staging and 
production environment configurations, with PHP error output suppressed 
and Symfony running in production mode. This primarily allows for a cleaner 
view, as Symfony in debug mode adds a fair bit of overhead to aid with 
development. 

If something works in the "local development" environment, there's no reason 
for it to fail in your "local testing" environment save server configuration 
issues (e.g. misconfigured Apache virtualhost).

Both "staging" and "production" environments have PHP error output suppressed 
and Symfony running in production mode. The "staging" environment is designed 
to be a non-live system running test data (possibly a snapshot of the live 
database) to allow for testing on production hardware.

## requirements
* php >= 5.5
* mcrypt php extension
* mysql server >= 5.1
* pdo and pdo_mysql php extensions
* apache2
* apache2 php5 module
* apache2 mod_rewrite

## setup on ubuntu linux

### ubuntu 12.04 and 14.04
Install the required packages:

    sudo apt-get install php5 php5-mysql php5-mcrypt apache2 libapache2-mod-php5 mysql-server

### ubuntu 12.04
You will have to add a PPA to fulfill the `php >= 5.5` requirement. 
[Ondřej Surý's PPA for PHP 5.5](https://launchpad.net/~ondrej/+archive/ubuntu/php5) 
is recommended. 

To add the PPA for PHP 5.5:

    sudo add-apt-repository ppa:ondrej/php5

After that:

    sudo apt-get update
    sudo apt-get install php5 php5-mcrypt

### nginx
Nginx works fine as an alternative to Apache for development, but setting it up 
correctly involves some extra configuration wizardry. In particular, `php5-fpm` 
(FastCGI Process Manager) should be installed and nginx needs to have the 
correct permissions to run it. Some additional work may be required to ensure 
`php5-fpm` is running the `mcrypt` module. These details are left out of the 
guide for now.

### (recommended) install composer globally
To install `composer` globally to your `$PATH`: 

    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin

If you change the `install-dir`, make sure the directory is in your `$PATH`.

## configuration on linux

### php5
Enable PHP5 modules:

    sudo php5enmod mcrypt mysql pdo pdo_mysql


### (optional) install symfony installer globally

To install the symfony installer and have the `symfony` command available 
globally, run:

    curl -LsS http://symfony.com/installer > symfony.phar
    sudo mv symfony.phar /usr/local/bin/symfony
    sudo chmod a+x /usr/local/bin/symfony

This should not be necessary unless you have a need to run 
`symfony new <project>` to create a new symfony project.

### (recommended) install composer globally

It may be useful to install composer globally:

    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod a+x /usr/local/bin/composer

### clone repository
Clone the github repository into the directory you plan to serve the files from, 
we're using `/var/www/acrossti.me`.

HTTPS:
    
    git clone https://github.com/afeique/acrossti.me.git /var/www/acrossti.me

SSH:

    git clone git@github.com:afeique/acrossti.me.git /var/www/acrossti.me

### (optional) set `/var/www` permissions
This is convenient as it allows you to work out of `/var/www` as your non-root 
user:

    sudo adduser $USER www-data
    sudo chown -R $USER:www-data /var/www{,/*}
    sudo chmod -R g+rw /var/www{,/*}

### hosts configuration 
Add the following lines to `/etc/hosts`:

    127.0.0.1   ldev.acrossti.me
    127.0.0.1   l.acrossti.me

### virtualhost configuration
Enable `mod_rewrite`:

    sudo a2enmod rewrite

Create a new configuration in `/etc/apache2/sites-available/` for symfony, 
we're  calling it `acrosstime-dev.conf`. This file will contain two virtualhost
configurations, one for the local development view, and the other for the 
local production view.

*Note:* the development view disables `.htaccess` files in the `web/` directory
whereas the production view utilizes the `.htaccess` present in the `web/`
directory.

    <VirtualHost *:80>
      ServerName ldev.acrossti.me
      DocumentRoot /var/www/acrossti.me/web

      <Directory /var/www/acrossti.me/web>
        DirectoryIndex app_dev.php
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
        Require all granted
        #Order allow,deny
        Allow from all
        <IfModule mod_rewrite.c>
            RewriteEngine On

            # Determine the RewriteBase automatically and set it as environment variable.
            # If you are using Apache aliases to do mass virtual hosting or installed the
            # project in a subdirectory, the base path will be prepended to allow proper
            # resolution of the app.php file and to redirect to the correct URI. It will
            # work in environments without path prefix as well, providing a safe, one-size
            # fits all solution. But as you do not need it in this case, you can comment
            # the following 2 lines to eliminate the overhead.
            RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
            RewriteRule ^(.*) - [E=BASE:%1]

            # Sets the HTTP_AUTHORIZATION header removed by apache
            RewriteCond %{HTTP:Authorization} .
            RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

            # Redirect to URI without front controller to prevent duplicate content
            # (with and without `/app.php`). Only do this redirect on the initial
            # rewrite by Apache and not on subsequent cycles. Otherwise we would get an
            # endless redirect loop (request -> rewrite to front controller ->
            # redirect -> request -> ...).
            # So in case you get a "too many redirects" error or you always get redirected
            # to the start page because your Apache does not expose the REDIRECT_STATUS
            # environment variable, you have 2 choices:
            # - disable this feature by commenting the following 2 lines or
            # - use Apache >= 2.3.9 and replace all L flags by END flags and remove the
            #   following RewriteCond (best solution)
            RewriteCond %{ENV:REDIRECT_STATUS} ^$
            RewriteRule ^app_dev\.php(/(.*)|$) %{ENV:BASE}/$2 [R=301,L]

            # If the requested filename exists, simply serve it.
            # We only want to let Apache serve files and not directories.
            RewriteCond %{REQUEST_FILENAME} -f
            RewriteRule .? - [L]

            # Rewrite all other queries to the front controller.
            RewriteRule .? %{ENV:BASE}/app_dev.php [L]
        </IfModule>

        <IfModule !mod_rewrite.c>
            <IfModule mod_alias.c>
                # When mod_rewrite is not available, we instruct a temporary redirect of
                # the start page to the front controller explicitly so that the website
                # and the generated links can still be used.
                RedirectMatch 302 ^/$ /app_dev.php/
                # RedirectTemp cannot be used instead
            </IfModule>
        </IfModule>
      </Directory>

      ErrorLog ${APACHE_LOG_DIR}/acrosstime.error.log
      CustomLog ${APACHE_LOG_DIR}/acrosstime.access.log common
    </VirtualHost>

    <VirtualHost *:80>
      ServerName l.acrossti.me
      DocumentRoot /var/www/acrossti.me/web

      <Directory /var/www/acrossti.me/web>
        DirectoryIndex app.php
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
        #Order allow,deny
        Allow from all
      </Directory>

      ErrorLog ${APACHE_LOG_DIR}/acrosstime.error.log
      CustomLog ${APACHE_LOG_DIR}/acrosstime.access.log common
    </VirtualHost>

    
Enable the new site configuration:

    sudo a2ensite acrosstime-dev.conf

Restart Apache:

    sudo service apache2 restart

### symfony dev permissions

In order for symfony to not throw permissions errors every time it tries to 
either access or write to `app/logs` and `app/cache`, those directories must
have their user ownership set to the same user that Apache2 runs under,
typically `www-data` in Ubuntu:

    sudo chown -R www-data:www-data /var/www/acrossti.me/app/cache
    sudo chown -R www-data:www-data /var/www/acrossti.me/app/logs

If these permissions are not set, viewing `local.acrossti.me` will render
a blank page. If you `cat /var/logs/apache2/acrosstime.error.log`, you will
most likely see a number of 404 errors as symfony is trying to access cache
files that could not be created due to permission errors.

### running symfony using the php server

Symfony can be run for debug purposes using

    php app/console run:server

This will create a local php server that can be accessed at
`http://localhost:8000`. You will be greeted by the default symfony debug
"Welcome" screen. However, this guide prefers to run symfony using the Apache2 
virtualhost we configured earlier. Using this setup, you will alway be able to
access your local development environment via `http://ldev.acrossti.me/` and 
never need to maintain commandline instances of the php server. Your local 
development environment will also better reflect the production environment.

## setup on windows 7

### install git
[Download the msysGit installer](https://git-for-windows.github.io/). Run it.
Install to any location.


### setup ssh keys
If you plan on using git with SSH, follow Github's directions for 
[setting up SSH keys](https://help.github.com/articles/generating-ssh-keys/).

### installation of apache, php, mysql
For convenience, we will be using the [Uniform Server](http://www.uniformserver.com/).
[Download the latest version](http://sourceforge.net/projects/miniserver/files/) and
then [obtain the PHP 5.5 module](http://sourceforge.net/projects/miniserver/files/Uniform%20Server%20ZeroXI/ZeroXImodules/).

Install the Uniform Server to a location of your choosing, then copy the 
PHP 5.5 module to that directory. Run the self-extracting installer for the 
PHP module.

To ensure there are no conflicts, it is a good idea at this point to turn off
any existing web or MySQL servers you have running and listening on the default
ports.

### hosts configuration
Open the Start menu. Search for `cmd`, then right-click `cmd.exe` and run in 
Administrator mode.

At the prompt, type:

    cd %SystemRoot%\System32\drivers\etc
    notepad hosts

This will open your Windows 7 `hosts` file in notepad with administrative 
privileges. Add the following lines to your `hosts` file:

    127.0.0.1   ldev.acrossti.me
    127.0.0.1   l.acrossti.me

### virtualhost configuration
Go to the root folder you installed the Uniform Server to.

Open `core\apache2\httpd.conf`.

Search for the line:

    Include conf/extra/httpd-vhosts.conf

Make sure it is uncommented.

Next, open `core\apache2\conf\extra\httpd-vhosts.conf`.

Add the following virtualhost configurations:

    <VirtualHost *:${AP_PORT}>
      ServerName ldev.acrossti.me
      DocumentRoot ${US_ROOTF_WWW}/acrossti.me/web

      <Directory ${US_ROOTF_WWW}/acrossti.me/web>
        DirectoryIndex app_dev.php
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
        Require all granted
        #Order allow,deny
        Allow from all
        <IfModule mod_rewrite.c>
            RewriteEngine On

            # Determine the RewriteBase automatically and set it as environment variable.
            # If you are using Apache aliases to do mass virtual hosting or installed the
            # project in a subdirectory, the base path will be prepended to allow proper
            # resolution of the app.php file and to redirect to the correct URI. It will
            # work in environments without path prefix as well, providing a safe, one-size
            # fits all solution. But as you do not need it in this case, you can comment
            # the following 2 lines to eliminate the overhead.
            RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
            RewriteRule ^(.*) - [E=BASE:%1]

            # Sets the HTTP_AUTHORIZATION header removed by apache
            RewriteCond %{HTTP:Authorization} .
            RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

            # Redirect to URI without front controller to prevent duplicate content
            # (with and without `/app.php`). Only do this redirect on the initial
            # rewrite by Apache and not on subsequent cycles. Otherwise we would get an
            # endless redirect loop (request -> rewrite to front controller ->
            # redirect -> request -> ...).
            # So in case you get a "too many redirects" error or you always get redirected
            # to the start page because your Apache does not expose the REDIRECT_STATUS
            # environment variable, you have 2 choices:
            # - disable this feature by commenting the following 2 lines or
            # - use Apache >= 2.3.9 and replace all L flags by END flags and remove the
            #   following RewriteCond (best solution)
            RewriteCond %{ENV:REDIRECT_STATUS} ^$
            RewriteRule ^app_dev\.php(/(.*)|$) %{ENV:BASE}/$2 [R=301,L]

            # If the requested filename exists, simply serve it.
            # We only want to let Apache serve files and not directories.
            RewriteCond %{REQUEST_FILENAME} -f
            RewriteRule .? - [L]

            # Rewrite all other queries to the front controller.
            RewriteRule .? %{ENV:BASE}/app_dev.php [L]
        </IfModule>

        <IfModule !mod_rewrite.c>
            <IfModule mod_alias.c>
                # When mod_rewrite is not available, we instruct a temporary redirect of
                # the start page to the front controller explicitly so that the website
                # and the generated links can still be used.
                RedirectMatch 302 ^/$ /app_dev.php/
                # RedirectTemp cannot be used instead
            </IfModule>
        </IfModule>
      </Directory>

      ErrorLog logs/acrosstime.error.log
      CustomLog logs/acrosstime.access.log common
    </VirtualHost>

    <VirtualHost *:${AP_PORT}>
      ServerName l.acrossti.me
      DocumentRoot ${US_ROOTF_WWW}/acrossti.me/web

      <Directory ${US_ROOTF_WWW}/acrossti.me/web>
        DirectoryIndex app.php
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
        #Order allow,deny
        Allow from all
      </Directory>

      ErrorLog logs/acrosstime.error.log
      CustomLog logs/acrosstime.access.log common
    </VirtualHost>

### clone repository
Open a Git Bash insie the directory the Uniform Server instance is set to
serve files from. By default, this is `www` within the root directory.

HTTPS:

    git clone https://github.com/afeique/acrossti.me.git

SSH:

    git clone git@github.com:afeique/acrossti.me.git


### start apache and mysql
Open your Uniform Server Controller and start Apache and MySQL using the buttons.
You should now be able to access the local development and testing URLs.

### (recommended) install composer
Go to the root directory of your Uniform Server install. From there, navigate to
`core\php55`. Make a note of the full path, we will refer to it as `%php_path%`.

Open a command-line. At the prompt, run:

    set PATH=%path%;%php_path%

This modifies the Windows path within the context of the command-line shell you have
open. To test your modification, run:

    php -v
    PHP 5.5.29 (cli) (built: Sep  2 2015 16:47:09)
    Copyright (c) 1997-2015 The PHP Group
    Zend Engine v2.5.0, Copyright (c) 1998-2015 Zend Technologies

Next, download the [Composer installer for Windows](https://getcomposer.org/doc/00-intro.md#installation-windows). Install Composer, then test your Composer install:

    composer -V
    Composer version 1.0-dev (7267b2ed9063ef64e7dda86421a928a802558fdb) 2015-09-14 14:48:45

**Note: you may have to append your `%php_path%` to your system `%path%` 
every time you open a new command-line shell and wish to use Composer.**

For convenience, you can permanently add your %php_path% to your system `
%path%`, or alternatively, create a specialized command-line shortcut that 
runs a `.cmd` or `.bat` file to setup variables for you. The instructions 
for doing so are omitted for now.
