#!/usr/bin/env bash

# Install Shopware
cd "$(dirname /var/www/shopware)"
sw install:release --release="5.4.0" --install-dir="." --shop-host="localhost" --shop-path="/" --db-host="shopware5-mysql" --db-user="app" --db-password="app" --db-name="shopware5" --admin-username="styla" --admin-password="support" --admin-name="Styla Admin" --admin-email="support@styla.com"

# Starting apache
apache2-foreground