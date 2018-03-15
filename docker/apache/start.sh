#!/usr/bin/env bash

# Sleep for 5 seconds to wait until mysql is up
sleep 5

# Install Shopware
rm -fR /var/www/shopware
mkdir /var/www/shopware
sw install:release --release="5.4.0" --install-dir="/var/www/shopware" --shop-host="localhost" --shop-path="/" --db-host="shopware5-mysql" --db-user="app" --db-password="app" --db-name="shopware5" --admin-username="styla" --admin-password="support" --admin-name="Styla Admin" --admin-email="support@styla.com"

# Symlink plugin
# /var/www/shopware/engine/Shopware/Plugins/Local/Frontend/StylaSEO

# Starting apache
apache2-foreground