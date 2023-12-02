FROM dunglas/frankenphp:latest-php8.2

# Environements
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV APP_RUNTIME="Runtime\FrankenPhpSymfony\Runtime"
ENV SERVER_NAME=:8080
ENV APP_ENV=dev
ENV TZ=Europe/Paris

# Install supervisor and cron
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
    lsb-release gnupg supervisor cron systemd default-mysql-client libpq-dev

# Install php extensions
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip

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

# Move config files
RUN mv /app/docker/Caddyfile /etc/caddy/Caddyfile
RUN mv /app/docker/app.ini /usr/local/etc/php/conf.d/app.ini

# Configure cron
RUN systemctl enable cron
RUN (crontab -l; echo "#!/bin/bash") | crontab
RUN (crontab -l; echo "BASH_ENV=/etc/environment") | crontab
RUN (crontab -l; echo "PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin") | crontab
RUN (crontab -l; echo "*/5 * * * * php /app/bin/console cron:server:check >> /var/log/cron.log 2>&1") | crontab
RUN touch /var/log/cron.log

# Run commands for image app
RUN rm -rf /app/vendor && \
    rm -rf /app/node_modules && \
    rm -rf /app/.git && \
    rm -rf /app/.env.local && \
    chown -R 1000:1000 /app && \
    chmod -R 777 /app/var && \
    cd /app && \
    composer install --ignore-platform-reqs && \
    yarn && yarn encore prod
    
WORKDIR /app

ENTRYPOINT ["./docker/docker.sh"]

EXPOSE 8080
