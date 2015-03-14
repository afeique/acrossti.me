# acrosstime

## target environment
Linux, Apache 2.2, PHP 5.4, MySQL 5.1  
staging url: http://dev.acrossti.me/  
production url: http://acrossti.me/  

## local development environment used throughout guide
Ubuntu 14.04, Apache 2.4, PHP 5.5, MySQL 5.5  
local url: http://ldev.acrossti.me/  
local production url: http://l.acrossti.me/  
**This url is a virtualhost setup on your local development environment.**

## requirements
* php >= 5.4
* mcrypt php extension
* mysql server >= 5.1
* pdo and pdo_mysql php extensions
* apache2
* apache2 php5 module
* apache2 mod_rewrite

## installation on linux

### ubuntu 12.04 and 14.04
Install the required packages:

    sudo apt-get install php5 php5-mysql php5-mcrypt apache2 libapache2-mod-php5 mysql-server

### ubuntu 12.04
You will have to add a PPA to fulfill the `php >= 5.4` requirement. 
[Ondřej Surý's PPA for PHP 5.5](https://launchpad.net/~ondrej/+archive/ubuntu/php5) 
is recommended. If you care to match the target environment, install the 
[oldstable PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php5-oldstable). 

To add the PPA for PHP 5.5:

    sudo add-apt-repository ppa:ondrej/php5

Or the PPA for PHP 5.4 (oldstable):

    sudo add-apt-repository ppa:ondrej/php5-oldstable

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

### clone repo
Clone the github repo into the directory of your choice, we're using 
`/var/www/acrossti.me` and the remainder of the readme will
assume this directory:

    git clone git@github.com:afeique/at.git /var/www/acrossti.me

### (optional) set `/var/www` permissions
This is convenient as it allows you to work out of `/var/www` as your non-root 
user:

    sudo adduser $USER www-data
    sudo chown -R $USER:www-data /var/www{,/*}
    sudo chmod -R g+rw /var/www{,/*}

### apache virtualhost
Enable `mod_rewrite`:

    sudo a2enmod rewrite

Create a new configuration in `/etc/apache2/sites-available/` for symfony, 
we're  calling it `acrosstime-dev.conf`. This file will contain two virtualhost
configurations, one for the local development view, and the other for the 
local production view.

    <VirtualHost *:80>
      ServerName ldev.acrossti.me
      DocumentRoot /var/www/acrossti.me/web

      <Directory /var/www/acrossti.me/web>
        DirectoryIndex app_dev.php
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
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
      CustomLog ${APACHE_LOG_DIR}/acrosstime.access.log combined
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
            RewriteRule ^app\.php(/(.*)|$) %{ENV:BASE}/$2 [R=301,L]

            # If the requested filename exists, simply serve it.
            # We only want to let Apache serve files and not directories.
            RewriteCond %{REQUEST_FILENAME} -f
            RewriteRule .? - [L]

            # Rewrite all other queries to the front controller.
            RewriteRule .? %{ENV:BASE}/app.php [L]

        </IfModule>

        <IfModule !mod_rewrite.c>
            <IfModule mod_alias.c>
                # When mod_rewrite is not available, we instruct a temporary redirect of
                # the start page to the front controller explicitly so that the website
                # and the generated links can still be used.
                RedirectMatch 302 ^/$ /app.php/
                # RedirectTemp cannot be used instead
            </IfModule>
        </IfModule>
      </Directory>

      ErrorLog ${APACHE_LOG_DIR}/acrosstime.error.log
      CustomLog ${APACHE_LOG_DIR}/acrosstime.access.log combined
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


