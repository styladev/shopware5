#!/usr/bin/env bash

# Sleep for 30 seconds to wait until mysql is up
sleep 30

# Install Shopware
rm -fR /var/www/shopware
mkdir /var/www/shopware
sw install:release --release="5.4.0" --install-dir="/var/www/shopware" --shop-host="localhost" --db-host="shopware5-mysql" --db-user="app" --db-password="app" --db-name="shopware5" --admin-username="styla" --admin-password="styla" --admin-name="Styla Admin" --admin-email="support@styla.com"
chown -R www-data:www-data /var/www/shopware

# Copy plugin
ln -s /home/root/StylaSEO /var/www/shopware/engine/Shopware/Plugins/Default/Frontend/StylaSEO

# Configurare Shopware
/var/www/shopware/bin/console sw:firstrunwizard:disable
/var/www/shopware/bin/console sw:plugin:refresh

/var/www/shopware/bin/console sw:store:download SwagDemoDataEN
/var/www/shopware/bin/console sw:plugin:install SwagDemoDataEN
/var/www/shopware/bin/console sw:plugin:activate SwagDemoDataEN

# Configurate plugin
/var/www/shopware/bin/console sw:plugin:install StylaSEO
/var/www/shopware/bin/console sw:plugin:activate StylaSEO
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_username "ci-shopware5"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_basedir "magazine"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_api_url "https://client-scripts.stage.eu.magalog.net"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_seo_url "http://seoapi.stage.eu.magalog.net"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_modular_content_username "ci-shopware5-pd"
/var/www/shopware/bin/console sw:plugin:config:set StylaSEO styla_modular_content_api "http://frontend-gateway.stage.eu.magalog.net"

# Add Styla snippet into product page template
sed -i ':a;N;$!ba;s/\n/{stylalb}/gm' /var/www/shopware/themes/Frontend/Bare/frontend/detail/content.tpl
sed -i 's/[^}]\(<\/div>\s*{stylalb}\s*{\/block}\)$/{$styla_seo_content}\1/gm' /var/www/shopware/themes/Frontend/Bare/frontend/detail/content.tpl
sed -i 's/{stylalb}/\n/gm' /var/www/shopware/themes/Frontend/Bare/frontend/detail/content.tpl

# Finalize
cp -f /home/root/config.php /var/www/shopware/config.php
/var/www/shopware/bin/console sw:cache:clear

# Starting apache
apache2-foreground
