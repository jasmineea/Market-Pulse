# -------------------------
# Stage 1: Build frontend assets (Vite)
# -------------------------
    FROM node:20-alpine AS frontend

    WORKDIR /app
    
    # Install JS deps first (better caching)
    COPY package*.json ./
    # If you use pnpm/yarn, tell me and I'll swap this section.
    RUN npm ci
    
    # Copy the rest of the app (so Vite can read resources/)
    COPY . .
    
    # Build frontend assets (expects vite.config.js + resources/)
    RUN npm run build
    
    
    # -------------------------
    # Stage 2: PHP + Composer dependencies
    # -------------------------
    FROM php:8.2-cli-alpine AS backend
    
    WORKDIR /var/www/html
    
    # System deps for common Laravel needs
    RUN apk add --no-cache \
        bash \
        curl \
        git \
        unzip \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite \
        sqlite-dev \
        $PHPIZE_DEPS
    
    # PHP extensions typically needed by Laravel
    RUN docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip
    
    # Install Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    
    # Copy app source
    COPY . .
    
    # Install PHP deps (no dev)
    RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    
    # Copy built frontend assets into public/build
    COPY --from=frontend /app/public/build ./public/build
    
    # Make sure storage + cache are writable
    RUN mkdir -p storage bootstrap/cache \
     && chmod -R 775 storage bootstrap/cache
    
    # Add start script
    COPY start.sh /usr/local/bin/start.sh
    RUN chmod +x /usr/local/bin/start.sh
    
    EXPOSE 10000
    
    CMD ["/usr/local/bin/start.sh"]
    