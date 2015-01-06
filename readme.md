# acrosstime

## target environment
Linux, Apache 2.2, PHP 5.4, MySQL 5.1  
staging url: http://dev.acrossti.me/
production url: http://acrossti.me/

## recommended development environment
Ubuntu 14.04, Apache 2.2, PHP 5.5, MySQL 5.5  
development url: http://local.acrossti.me  
**This url is a virtualhost setup on your local development environment.**

## requirements
* php >= 5.4
* mcrypt php extension

## installation on linux

### ubuntu 12.04 and 14.04
Install the required packages:

    sudo apt-get install php5 php5-mysql php5-mcrypt apache2 libapache2-mod-php5 mysql-server

### ubuntu 12.04
You will have to add a PPA to fulfill the `php >= 5.4` requirement. [Ondřej Surý's PPA for PHP 5.5](https://launchpad.net/~ondrej/+archive/ubuntu/php5) is recommended. If you care to match the target environment, install the [oldstable PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php5-oldstable). 

To add the PPA for PHP 5.5:

    sudo add-apt-repository ppa:ondrej/php5

Or the PPA for PHP 5.4 (oldstable):

    sudo add-apt-repository ppa:ondrej/php5-oldstable

After that:

    sudo apt-get update
    sudo apt-get install php5 php5-mcrypt

### nginx
Nginx works fine as an alternative to Apache for development, but setting it up correctly involves some extra configuration wizardry. In particular, `php5-fpm` (FastCGI Process Manager) should be installed and nginx needs to have the correct permissions to run it. Some additional work may be required to ensure `php5-fpm` is running the `mcrypt` module. These details are left out of the guide for now.

### (recommended) install composer globally
To install `composer` globally to your `$PATH`: 

    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin

If you change the `install-dir`, make sure the directory is in your `$PATH`.

## configuration on linux

### php5
Enable PHP5 modules:

    sudo php5enmod mcrypt mysql pdo pdo_mysql


### staging/production php compatibility
When on the staging/production server, `php` on the commandline actually runs PHP 5.2. In order to run PHP 5.4, you must use `php-5.4`. 

Because of this, `composer.json` has been modified to use the command `php-5.4 artisan` when running scripts for `post-install-cmd`, `post-update-cmd`, and `post-create-project-cmd`. This will cause `composer install` to fail when you run it on your development machine.

To fix that, create the appropriate symlink to your system's php:

    sudo ln -s $(which php) $(dirname $(which php))/php-5.4

For a fun (read: uselessly complicated) visual confirmation, try:

    echo "$(php --version)" > php-version
    echo "$(php-5.4 --version)" > php-5.4-version
    diff php-version php-5.4-version

Your output should be nothing, indicating both versions are identical.

### clone repo
Clone the github repo into the directory of your choice, we're using `/var/www/local.acrossti.me`:

    git clone git@github.com:afeique/at.git /var/www/local.acrossti.me

Run `composer install` from the directory you cloned into:

    cd /var/www/local.acrossti.me
    composer install

### (optional) set `/var/www` permissions
This is convenient as it allows you to work out of `/var/www` as your non-root user:

    sudo adduser $USER www-data
    sudo chown -R $USER:www-data /var/www{,/*}
    sudo chmod -R g+rw /var/www{,/*}

### apache virtualhost
Enable `mod_rewrite`:

    sudo a2enmod rewrite.load

Create a new configuration in `/etc/apache2/sites-available/`, we're calling it `acrosstime-dev.conf`:
    
    <VirtualHost *:80>
        ServerName acrossti.me
        ServerAlias local.acrossti.me
        ServerAdmin root@acrossti.me
        DocumentRoot /var/www/local.acrossti.me/public
        <Directory /var/www/local.acrossti.me/public>
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


