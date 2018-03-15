#!/usr/bin/env bash

# Sleep for 30 seconds to wait until mysql is up
sleep 30

# Install Shopware
rm -fR /var/www/shopware
mkdir /var/www/shopware
sw install:release --release="5.4.0" --install-dir="/var/www/shopware" --shop-host="localhost" --shop-path="/" --db-host="shopware5-mysql" --db-user="app" --db-password="app" --db-name="shopware5" --admin-username="styla" --admin-password="support" --admin-name="Styla Admin" --admin-email="support@styla.com"
chown -R www-data:www-data /var/www/shopware

# TODO: Symlink plugin
#ln -s /home/root/StylaSEO /var/www/shopware/engine/Shopware/Plugins/Local/Frontend/StylaSEO

# TODO: install demo data:
#/var/www/shopware/bin/console sw:plugin:install SwagDemoDataEN

# TODO: configurate plugin settings e.g.:
#/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_username {{ styla_username }}

# Starting apache
apache2-foreground