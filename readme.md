# acrosstime

## target environment
Linux, Apache 2.2, PHP 5.4, MySQL 5.1  
staging url: http://dev.acrossti.me/  
production url: http://acrossti.me/  

## recommended local development environment
Ubuntu 14.04, Apache 2.2, PHP 5.5, MySQL 5.5  
recommended local url: http://local.acrossti.me/  
**This url is a virtualhost setup on your local development environment.**

## requirements
* php >= 5.4
* mcrypt php extension
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
`/var/www/local.acrossti.me` and the remainder of the readme will
assume this directory:

    git clone git@github.com:afeique/at.git /var/www/local.acrossti.me

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
we're  calling it `acrosstime-dev.conf`:
    
    <VirtualHost *:80>
        ServerName acrossti.me
        ServerAlias local.acrossti.me
        ServerAdmin root@acrossti.me
        DocumentRoot /var/www/local.acrossti.me/web
        <Directory /var/www/local.acrossti.me/web>
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
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

    sudo chown -R www-data:www-data /var/www/local.acrossti.me/app/cache
    sudo chown -R www-data:www-data /var/www/local.acrossti.me/app/logs

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
access your local development environment via `http://local.acrossti.me/` and 
never need to maintain commandline instances of the php server. Your local 
development environment will also better reflect the production environment.

### symfony web/.htaccess for local development

If you see `Oops! An Error Occurred The server returned a “404 Not Found”`
when trying to access `http://local.acrossti.me/`, it means that symfony is 
running correctly, but is running using production `.htaccess` settings. 

This error exists because the codebase is still so new that there are no 
production routes in place under `config/routing.yml`. In the future, 
once production routes are in place, this error will become obsolete.

If you `cat /var/logs/apache2/acrosstime.error.log` you will notice that
Apache2 does not actually produce a 404, meaning the 404 is generated
entirely by symfony.

To fix this error, we must configure `web/.htaccess` to run symfony in dev
mode.

By default, the git repo should be configured for running symfony in dev mode;
this means that `mod_rewrite` in `web/.htaccess` is configured to use
`web/app_dev.php` as its front-controller. For production, this will change
to `web/app.php`. To switch between the two, comment lines containing
`app_dev.php`, and uncomment lines containing `app.php`.


