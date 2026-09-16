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
# 不用 -D：-D 会强制 daemonize=yes，覆盖 zz-docker.conf 里的 daemonize = no 并脱离 stdio，
# 使 docker.conf 的 error_log/access.log = /proc/self/fd/2 指向 /dev/null，日志全部丢失。
# 用 & 后台化可保持 stdio 与容器相连，FPM 日志因此进入 docker logs。
php-fpm &
fpm_pid=$!

# 等待 FPM 监听就绪（最多 10s）。
# 这一步替代 -D 时代由 set -e 提供的启动失败检测：若 FPM 因配置错误退出，立即失败，
# 而不是让 nginx 起来之后满屏 502。
i=0
while [ "$i" -lt 50 ]; do
    if php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);' 2>/dev/null; then
        break
    fi
    kill -0 "$fpm_pid" 2>/dev/null || { echo "php-fpm 启动失败，容器退出" >&2; exit 1; }
    i=$((i + 1))
    sleep 0.2
done

# 启动 Nginx（前台，作为容器主进程）
# exec：让 nginx 取代 sh 成为 PID 1。否则 sh 会吞掉 docker stop 发来的 SIGTERM，
# nginx 收不到信号，每次停止都要耗尽宽限期后被 SIGKILL。
exec nginx -g 'daemon off;'
