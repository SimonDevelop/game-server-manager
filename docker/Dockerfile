FROM dunglas/frankenphp:latest

# Environements
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV APP_RUNTIME="Runtime\FrankenPhpSymfony\Runtime"
ENV SERVER_NAME=:8080
ENV APP_ENV=dev

# Install supervisor
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    lsb-release gnupg supervisor

# Install php extensions
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    amqp \
    redis

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Install nodejs and yarn
RUN curl -sL https://deb.nodesource.com/setup_lts.x --output nodesjssetup_lts.x.sh && \
    /bin/sh nodesjssetup_lts.x.sh && \
    rm -f nodesjssetup_lts.x.sh
RUN apt-get update && apt-get install nodejs -y && npm i -g yarn

# Copy files project
COPY . /app

# Move supervisor config
RUN mv /app/docker/messenger-worker.conf /etc/supervisor/conf.d/messenger-worker.conf

# Run commands for image app
RUN rm -rf /app/vendor && \
    rm -rf /app/node_modules && \
    rm -rf /app/.git && \
    rm -rf /app/.env.local && \
    chown -R 1000:1000 /app && \
    chmod -R 777 /app/var && \
    cd /app && \
    composer install --ignore-platform-req=php && \
    yarn && yarn encore prod
    
WORKDIR /app

ENTRYPOINT ["./docker/docker.sh"]

EXPOSE 8080
