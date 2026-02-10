# -------------------------
# Stage 1: Build frontend assets (Vite)
# -------------------------
    FROM node:20-alpine AS frontend

    WORKDIR /app
    
    # Install JS deps first (better caching)
    COPY package*.json ./
    RUN npm ci
    
    # Copy the rest of the app (so Vite can read resources/)
    COPY . .
    
    # Build frontend assets (expects vite.config.js + resources/)
    RUN npm run build
    
    
    # -------------------------
    # Stage 2: PHP + Composer + required extensions
    # -------------------------
    FROM php:8.2-cli-alpine AS app
    
    WORKDIR /var/www/html
    
    # System dependencies
    # - postgresql-dev: required for pdo_pgsql
    # - icu-dev: intl
    # - libzip-dev: zip
    # - oniguruma-dev: mbstring support (Laravel commonly needs it)
    # - sqlite/sqlite-dev: keep for local/dev or any sqlite usage
    # - $PHPIZE_DEPS: build deps for PHP extensions
    RUN apk add --no-cache \
        bash \
        curl \
        git \
        unzip \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        sqlite \
        sqlite-dev \
        $PHPIZE_DEPS
    
    # PHP extensions needed by Laravel + Postgres
    RUN docker-php-ext-install \
        intl \
        mbstring \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        pdo_pgsql \
        zip
    
    # Install Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    
    # Copy app source
    COPY . .
    
    # Install PHP deps (production)
    RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    
    # Copy built frontend assets into public/build
    COPY --from=frontend /app/public/build ./public/build
    
    # Ensure Laravel writable dirs exist + permissions
    RUN mkdir -p storage bootstrap/cache \
     && chmod -R 775 storage bootstrap/cache
    
    # Add start script
    COPY start.sh /usr/local/bin/start.sh
    RUN chmod +x /usr/local/bin/start.sh
    
    EXPOSE 10000
    
    CMD ["/usr/local/bin/start.sh"]
    