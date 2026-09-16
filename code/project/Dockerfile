# 后端 Dockerfile：PHP-FPM + Nginx 单容器
FROM php:8.1-fpm

# 使用阿里云 Debian 源，加速 apt-get
RUN sed -i 's|deb.debian.org|mirrors.aliyun.com|g' /etc/apt/sources.list.d/debian.sources \
    && sed -i 's|security.debian.org|mirrors.aliyun.com|g' /etc/apt/sources.list.d/debian.sources

# 安装系统依赖
# fonts-dejavu-core：验证码 TTF 渲染所需字体
RUN apt-get update && apt-get install -y \
    nginx \
    zip unzip git curl libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    fonts-dejavu-core \
    --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展
# mbstring / dom / xml / simplexml 官方 php:8.1-fpm 镜像已内置，此处仅断言，不重复编译
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    opcache \
    && php -m | grep -q pdo_mysql \
    && php -m | grep -q mbstring \
    && php -m | grep -q dom

# 安装 Redis 扩展（Laravel 8 默认 phpredis 驱动）
RUN pecl install redis \
    && docker-php-ext-enable redis

# 配置 PHP
COPY php.ini /usr/local/etc/php/php.ini

# 配置 Nginx
COPY nginx.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 创建工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . .

# 确保所需目录存在（composer install 的 post-autoload-dump 需要 bootstrap/cache）
RUN mkdir -p /var/www/html/bootstrap/cache \
             /var/www/html/storage/framework/cache \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs

# 安装依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

RUN php artisan storage:link --force

# 暴露端口
EXPOSE 80

# 启动脚本
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
