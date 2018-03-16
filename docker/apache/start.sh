#!/usr/bin/env bash

# Sleep for 30 seconds to wait until mysql is up
sleep 30

# Install Shopware
rm -fR /var/www/shopware
mkdir /var/www/shopware
sw install:release --release="5.4.0" --install-dir="/var/www/shopware" --shop-host="localhost" --db-host="shopware5-mysql" --db-user="app" --db-password="app" --db-name="shopware5" --admin-username="styla" --admin-password="support" --admin-name="Styla Admin" --admin-email="support@styla.com"
chown -R www-data:www-data /var/www/shopware

# Configurare Shopware
/var/www/shopware/bin/console sw:firstrunwizard:disable
#/var/www/shopware/bin/console sw:plugin:install SwagDemoDataEN # TODO: Plugin by name "SwagDemoDataEN" was not found ?!?

# Configurate plugin
ln -s /home/root/StylaSEO /var/www/shopware/engine/Shopware/Plugins/Local/Frontend/StylaSEO
/var/www/shopware/bin/console sw:plugin:install StylaSEO
/var/www/shopware/bin/console sw:plugin:activate StylaSEO
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_username "ci-shopware5"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_basedir "magazine"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_api_url "https://client-scripts.stage.eu.magalog.net"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_seo_url "http://seoapi.stage.eu.magalog.net"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_modular_content_username "ci-shopware5-pd"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_modular_content_api "http://frontend-gateway.stage.eu.magalog.net"

# Starting apache
apache2-foreground
