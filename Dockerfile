FROM php:5.6

RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get -y install php5-curl curl lynx-cur vim htop zip

EXPOSE 80

ADD / /var/www/html/reporter
WORKDIR /var/www/html/reporter

RUN mkdir /var/log/reporter && touch /var/log/reporter/output.log

RUN cd /var/www/html/reporter

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN composer install --prefer-source --no-interaction
