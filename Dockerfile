# Dockerfile
FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql zip bcmath \
    && pecl install redis && docker-php-ext-enable redis

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www

# Пользователь для запуска (не обязательно, но для удобства)
RUN useradd -G www-data,root -u 1000 -d /home/dev dev \
    && mkdir -p /home/dev/.composer \
    && chown -R dev:dev /home/dev

USER dev

EXPOSE 9000
CMD ["php-fpm"]