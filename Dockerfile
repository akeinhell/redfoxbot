FROM akeinhell/docker-nginx-php7.1

ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION v8.0.0

# Install nvm with node and npm
RUN curl https://raw.githubusercontent.com/creationix/nvm/v0.33.5/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH      $NVM_DIR/v$NODE_VERSION/bin:$PATH

WORKDIR /tmp
ADD composer.json /tmp
ADD composer.lock /tmp
RUN composer install --no-scripts --no-autoloader

ADD package.json /tmp
ADD webpack.config.js /tmp
ADD yarn.lock /tmp
RUN yarn install


WORKDIR /var/www
COPY . /var/www/
RUN cp -a /tmp/node_modules /var/www/
RUN cp -a /tmp/vendor /var/www/

RUN npm run build

ADD . ./
RUN composer dump-autoload --optimize

#COPY public/dist ./public/dist


