FROM node as webpack
ADD package.json /var/knowfox/
ADD webpack.mix.js /var/knowfox/
ADD resources /var/knowfox/resources
ADD public /var/knowfox/public
WORKDIR /var/knowfox
RUN yarn
RUN yarn run production

FROM composer
RUN docker-php-ext-install pdo_mysql
RUN mkdir /var/www && chown www-data:www-data /var/www
ADD --chown=www-data:www-data . /var/www/knowfox
WORKDIR /var/www/knowfox
COPY --from=webpack /var/knowfox/public/css/ ./public/css/
COPY --from=webpack /var/knowfox/public/js/ ./public/js/
RUN su -s /usr/bin/composer www-data install
