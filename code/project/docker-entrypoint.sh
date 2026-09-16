#!/bin/sh
set -e

# storage / public/upload 是宿主机挂载卷，容器内可能是新的空目录，先补齐子目录
mkdir -p /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/public/upload

# 修复挂载卷属主；只 chmod 目录不 chmod 文件，避免污染宿主机上 git 跟踪文件的权限位
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/upload 2>/dev/null || true
find /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/upload -type d -exec chmod 775 {} + 2>/dev/null || true

# 启动 PHP-FPM（后台）
php-fpm -D

# 启动 Nginx（前台，作为容器主进程）
nginx -g 'daemon off;'
