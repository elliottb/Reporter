FROM php:5.6

RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get -y install php5-curl curl lynx-cur vim htop zip

EXPOSE 80

# Copy site into place.
ADD / /var/www/html/reporter
WORKDIR /var/www/html/reporter

#RUN sudo chown -R www-data:www-data /var/www
#RUN chmod go-rwx /var/www
#RUN chmod go+x /var/www
#RUN chgrp -R www-data /var/www
#RUN chmod -R go-rwx /var/www
#RUN chmod -R g+rx /var/www
#RUN chmod -R g+rwx /var/www

RUN php -v
RUN cd /var/www/html/reporter && php composer.phar install
