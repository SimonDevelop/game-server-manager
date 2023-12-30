FROM dunglas/frankenphp:latest-php8.3

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
    lsb-release ca-certificates curl gnupg supervisor cron systemd libpq-dev unzip

# Install php extensions
RUN install-php-extensions \
    pgsql \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    gd \
    intl \
    zip

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Install nodejs and yarn
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update && apt-get install nodejs -y && npm i -g yarn

# Copy files project
COPY . /app

# Move supervisor config
RUN mv /app/docker/messenger-worker.conf /etc/supervisor/conf.d/messenger-worker.conf

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
