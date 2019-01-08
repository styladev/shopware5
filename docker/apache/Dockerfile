FROM php:7.1-apache

RUN apt-get update -qq && apt-get install -y -qq \
        libicu-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libcurl4-openssl-dev \
        software-properties-common  \
        libcurl3 curl \
        git \
        zip \
        unzip \
        inotify-tools

RUN apt-get update -qq && apt-get install -y -qq \
        build-essential \
        libxml2-dev libxslt1-dev zlib1g-dev \
        git \
        mysql-client \
        sshpass \
        nano \
        sudo \
        vim \
        graphviz \
        netcat-openbsd

RUN docker-php-ext-install iconv mcrypt mbstring \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install curl \
    && docker-php-ext-install intl \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql

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

# Configurate apache
ADD /docker/apache/vhosts.conf /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite

# Copying Shopware config file
ADD /docker/apache/config.php /home/root/config.php

# Install Shopware
ADD /docker/apache/start.sh /home/root/start.sh
RUN chmod 777 /home/root/start.sh
CMD /home/root/start.sh
