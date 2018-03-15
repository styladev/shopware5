#!/usr/bin/env bash

# Install Shopware
rm -fR /var/www/shopware/
sw install:release --release="5.4.0" --install-dir="/var/www/shopware" --shop-host="localhost" --shop-path="/" --db-host="shopware5-mysql" --db-user="app" --db-password="app" --db-name="shopware5" --admin-username="styla" --admin-password="support" --admin-name="Styla Admin" --admin-email="support@styla.com"

# Symlink plugin
# /var/www/shopware/engine/Shopware/Plugins/Local/Frontend/StylaSEO

# Starting apache
apache2-foreground