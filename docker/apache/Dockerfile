FROM php:7.1-apache

RUN apt-get update -qq && apt-get install -y -qq \
    zip \
    unzip \
    git

WORKDIR /home/root

# Install sw-cli-tools
RUN git clone https://github.com/shopwareLabs/sw-cli-tools.git

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename=composer
RUN php -r "unlink('composer-setup.php');"

# Update dependencies of sw-cli-tools
WORKDIR /home/root/sw-cli-tools
RUN composer -n install

# Create symlink for sw-cli tools
RUN ln -s /home/root/sw-cli-tools/bin/sw /usr/bin/sw

# Install shopware5
WORKDIR /var/www/shopware
RUN sw install:release --release="5.4.0" --install-dir="." --shop-host="localhost" --shop-path="/" --db-host="localhost" --db-user="root" --db-password="admin" --db-name="shopware5" --admin-username="{{ shopware_config.admin_name }}" --admin-password="support" --admin-name="Styla Admin" --admin-email="support@styla.com"
