FROM akeinhell/docker-nginx-php7.1:latest

WORKDIR /tmp
ADD composer.json /tmp
ADD composer.lock /tmp
RUN composer install --no-scripts --no-autoloader

ADD package.json /tmp
ADD yarn.lock /tmp
RUN yarn install


WORKDIR /var/www
COPY . /var/www/
RUN cp -a /tmp/node_modules /var/www/
RUN cp -a /tmp/vendor /var/www/

RUN npm run build

RUN composer dump-autoload --optimize

#COPY public/dist ./public/dist


