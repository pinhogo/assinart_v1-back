FROM php:8.3-fpm

ENV APP_ROOT=/home/site/wwwroot \
    PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libsqlite3-dev \
        libwebp-dev \
        libzip-dev \
        nginx \
        pkg-config \
        sqlite3 \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && for ext in mbstring fileinfo pdo_sqlite pdo_pgsql gd zip opcache; do \
        if ! php -m | grep -qiE "(^| )${ext}$"; then \
            docker-php-ext-install -j"$(nproc)" "${ext}"; \
        fi; \
    done \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /home/site/wwwroot

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . /home/site/wwwroot

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && rm -rf bootstrap/cache/*.php database/*.sqlite .env* composer.phar \
        storage/logs/*.log storage/framework/views/*.php \
    && cp public/.user.ini /usr/local/etc/php/conf.d/99-azure-user.ini \
    && printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=16\nopcache.max_accelerated_files=10000\nopcache.validate_timestamps=0\n" \
        > /usr/local/etc/php/conf.d/90-opcache.ini \
    && printf "[www]\nlisten = 127.0.0.1:9000\ncatch_workers_output = yes\ndecorate_workers_output = no\n" \
        > /usr/local/etc/php-fpm.d/zz-azure-sim.conf \
    && cp azure/nginx.conf /etc/nginx/sites-available/default \
    && ln -sfn /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/conf.d/default.conf \
    && chmod +x azure/startup.sh azure/backup.sh docker/azure-sim/entrypoint.sh \
    && cp docker/azure-sim/entrypoint.sh /usr/local/bin/azure-sim-entrypoint.sh

EXPOSE 8080

HEALTHCHECK --interval=15s --timeout=5s --start-period=25s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["/usr/local/bin/azure-sim-entrypoint.sh"]
