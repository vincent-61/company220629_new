# Docker 化实施计划

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 将 Laravel 8.65 官网管理系统容器化，本地用 Docker Compose 跑通，通过 GitHub Actions 自动部署到 CentOS 7.6 服务器（39.108.218.82），与同机的 realchip 项目端口错开。

**Architecture:** 三个容器 —— project（PHP 8.1-FPM + Nginx 同容器，监听 80）、mysql（MySQL 8.0）、redis（Redis 7-Alpine）。MySQL 数据持久化到 `./code/mysql`。生产环境用 `docker-compose.prod.yml` 覆盖环境变量并移除 mysql/redis 端口。验证码从 `mews/captcha`（BcryptHasher + Session，容器内不可靠）改为纯 Redis token 方案，前后台都改。

**Tech Stack:** Docker, Docker Compose, PHP 8.1-FPM, Nginx, MySQL 8.0, Redis 7-Alpine, GitHub Actions (appleboy/ssh-action@v1)

**Spec:** `docs/superpowers/specs/2026-09-16-dockerize-design.md`

## Global Constraints

- PHP 8.1（`composer.json` 约束 `^7.3|^8.0`），Laravel 8.65
- 单容器方案（PHP-FPM + Nginx 同容器，容器内端口 80）
- MySQL 8.0，数据库名 `company220629`，用户 `root`，密码 `root_company220629_202609`，表前缀 `app_`
- Redis 7-Alpine，`REDIS_PREFIX=company_`
- `CACHE_DRIVER=redis` 必须与 `SESSION_DRIVER=redis` 同时设置
- 端口：project `127.0.0.1:8089`、mysql `127.0.0.1:13306`、redis `127.0.0.1:16379`，全部绑 `127.0.0.1`
- 容器名 `company220629-{project,mysql,redis}`，网络 `company220629-network`
- 后台验证码 TTL 60s，前台验证码 TTL 300s，Redis key 格式 `captcha:{token}`
- CentOS 7.6 服务器（cgroup v1、yum、firewalld），部署路径 `/opt/company220629`，域名 `www.company220629.com`
- 服务器上用 standalone `docker-compose` 命令，**不是** `docker compose` 插件
- 与 realchip 同机部署，不得占用其 8088/3306/6379
- 所有代码注释、提交信息、文档用中文

---

### Task 1: 目录重构（Laravel 应用迁入 `code/project/`）

**Files:**
- Move: 仓库根目录全部 Laravel 文件 → `code/project/`
- Move: `docs/company220629.sql` → `docker/mysql/01-company220629.sql`
- Create: `code/mysql/.gitkeep`

**Interfaces:**
- Produces: `code/project/` 作为 Docker 构建上下文，后续所有 Dockerfile / nginx.conf / php.ini / 入口脚本都在此目录内；`docker/mysql/01-company220629.sql` 作为 MySQL 首次启动的初始化脚本

- [ ] **Step 1: 执行迁移**

在仓库根目录执行：

```bash
set -e
mkdir -p code/project code/mysql docker/mysql

# 保留在根目录的条目
KEEP=".git .DS_Store .idea .claude docs code docker"

for f in $(ls -A); do
  case " $KEEP " in *" $f "*) continue ;; esac
  # 已跟踪文件用 git mv 保留重命名历史；未跟踪文件（vendor/.env 等）直接 mv
  git mv "$f" code/project/ 2>/dev/null || mv "$f" code/project/
done

git mv docs/company220629.sql docker/mysql/01-company220629.sql
touch code/mysql/.gitkeep
```

- [ ] **Step 2: 核对搬迁结果**

```bash
ls -A                       # 根目录只应剩 .git .gitignore 缺失前的残留 + docs code docker .idea .DS_Store
ls -A code/project/         # 应含 app artisan bootstrap composer.json composer.lock config database public resources routes storage tests vendor .env .user.ini .gitignore 等
ls -A docker/mysql/         # 01-company220629.sql
git status --short | head -40
```

预期：`git status` 显示大量 `R`（renamed）记录，而不是 `D` + `??`。

- [ ] **Step 3: 提交**

```bash
git add -A
git commit -m "[docker] 重构目录：Laravel 应用迁入 code/project，SQL 移入 docker/mysql"
```

---

### Task 2: 创建 Dockerfile

**Files:**
- Modify: `code/project/composer.json`（新增 `config.allow-plugins`）
- Create: `code/project/Dockerfile`

**Interfaces:**
- Consumes: `code/project/` 构建上下文；`php.ini`、`nginx.conf`、`docker-entrypoint.sh`（Task 3/4/5 产出，本任务先引用，构建在第 17 步之前不会执行）
- Produces: 镜像 `company220629-project`，`EXPOSE 80`，`ENTRYPOINT ["docker-entrypoint.sh"]`

- [ ] **Step 1: 给 composer.json 加 allow-plugins**

`composer.lock` 里有一个 composer-plugin 类型包：`composer/package-versions-deprecated`。`php:8.1-fpm` 自带的 Composer 是 2.2+，非交互模式下会拒绝加载未授权的插件直接报错。

把 `code/project/composer.json` 的 `config` 段：

```json
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
```

改为：

```json
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "composer/package-versions-deprecated": true
        }
    },
```

- [ ] **Step 2: 创建 Dockerfile**

创建 `code/project/Dockerfile`：

```dockerfile
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
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 创建工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . .

# 确保所需目录存在（composer install 的 post-autoload-dump 需要 bootstrap/cache）
RUN mkdir -p /var/www/html/bootstrap/cache /var/www/html/storage/framework/{cache,sessions,views}

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
```

> 如果第 17 步构建时 `php -m | grep -q mbstring` 或 `grep -q dom` 失败，说明该镜像版本未内置这两个扩展。修法是：apt 增加 `libonig-dev libxml2-dev`，并在 `docker-php-ext-install` 列表里补上 `mbstring dom`。

- [ ] **Step 3: 提交**

```bash
git add code/project/Dockerfile code/project/composer.json
git commit -m "[docker] 新增 Dockerfile（PHP 8.1-FPM + Nginx），composer.json 授权插件"
```

---

### Task 3: 创建 nginx.conf

**Files:**
- Create: `code/project/nginx.conf`

**Interfaces:**
- Consumes: 容器内 `/var/www/html/public` 作为 web root，PHP-FPM 监听 `127.0.0.1:9000`（`php:8.1-fpm` 默认）
- Produces: Nginx server 配置，被 Dockerfile `COPY` 到 `/etc/nginx/sites-available/default`

- [ ] **Step 1: 创建 nginx.conf**

创建 `code/project/nginx.conf`：

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 只有 public/index.php 允许走 FastCGI，其余 .php 一律 404，防止绕过入口
    location = /index.php {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 120s;
    }

    location ~ \.php$ {
        return 404;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 20M;
}
```

- [ ] **Step 2: 提交**

```bash
git add code/project/nginx.conf
git commit -m "[docker] 新增 nginx.conf"
```

---

### Task 4: 创建 php.ini

**Files:**
- Create: `code/project/php.ini`

**Interfaces:**
- Produces: PHP 配置，被 Dockerfile `COPY` 到 `/usr/local/etc/php/php.ini`

- [ ] **Step 1: 创建 php.ini**

创建 `code/project/php.ini`（PHP 8.1 生产配置，注意不要写 PHP 8.1 已废弃的 `opcache.fast_shutdown`）：

```ini
[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func =
serialize_precision = -1
disable_functions =
disable_classes =
zend.enable_gc = On
expose_php = Off
max_execution_time = 120
max_input_time = 60
memory_limit = 256M
error_reporting = E_ALL
display_errors = Off
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
html_errors = On
error_log = /var/log/php_errors.log
variables_order = "GPCS"
request_order = "GP"
register_argc_argv = Off
auto_globals_jit = On
post_max_size = 20M
auto_prepend_file =
auto_append_file =
default_mimetype = "text/html"
default_charset = "UTF-8"
doc_root =
user_dir =
enable_dl = Off
file_uploads = On
upload_max_filesize = 20M
max_file_uploads = 20
allow_url_fopen = On
allow_url_include = Off
default_socket_timeout = 60

[CLI Server]
cli_server.color = On

[Date]
date.timezone = Asia/Shanghai

[Pdo_mysql]
pdo_mysql.cache_size = 2000
pdo_mysql.default_socket=

[mail function]
SMTP = localhost
smtp_port = 25
mail.add_x_header = Off

[MySQLi]
mysqli.max_persistent = -1
mysqli.allow_persistent = On
mysqli.max_links = -1
mysqli.cache_size = 2000
mysqli.default_port = 3306
mysqli.default_socket =
mysqli.default_host =
mysqli.default_user =
mysqli.default_pw =
mysqli.reconnect = Off

[mysqlnd]
mysqlnd.collect_statistics = On
mysqlnd.collect_memory_statistics = Off

[bcmath]
bcmath.scale = 0

[Session]
session.save_handler = files
session.use_strict_mode = 0
session.use_cookies = 1
session.use_only_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.cookie_httponly =
session.cookie_samesite =
session.serialize_handler = php
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440
session.referer_check =
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.sid_length = 26
session.trans_sid_tags = "a=href,area=href,frame=src,form="
session.sid_bits_per_character = 5

[Assertion]
zend.assertions = -1

[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

- [ ] **Step 2: 提交**

```bash
git add code/project/php.ini
git commit -m "[docker] 新增 php.ini"
```

---

### Task 5: 创建 docker-entrypoint.sh

**Files:**
- Create: `code/project/docker-entrypoint.sh`

**Interfaces:**
- Consumes: 挂载卷 `/var/www/html/storage`、`/var/www/html/public/upload`
- Produces: 容器入口，前台运行 nginx（PID 1）

- [ ] **Step 1: 创建 docker-entrypoint.sh**

创建 `code/project/docker-entrypoint.sh`：

```sh
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
```

- [ ] **Step 2: 加可执行权限**

```bash
chmod +x code/project/docker-entrypoint.sh
```

- [ ] **Step 3: 提交**

```bash
git add code/project/docker-entrypoint.sh
git commit -m "[docker] 新增容器入口脚本"
```

---

### Task 6: 创建 .dockerignore

**Files:**
- Create: `code/project/.dockerignore`

**Interfaces:**
- Produces: 构建上下文排除规则，缩小镜像、避免携带本地文件

- [ ] **Step 1: 创建 .dockerignore**

创建 `code/project/.dockerignore`：

```dockerignore
.git
.gitignore
.env
.env.*
.DS_Store
.idea
.vscode
.user.ini
node_modules
vendor
public/hot
public/storage
public/upload
storage/*.key
storage/framework
storage/logs
.phpunit.result.cache
docker-compose*.yml
Dockerfile
*.md
tests
phpunit.xml
```

> 三条非 realchip 的排除项，理由如下：
> - `vendor`：本地 72MB，镜像内会被 `composer install` 重新生成
> - `public/upload`：本地 100MB 用户上传文件，运行时由 volume 挂载提供，不进镜像
> - `.user.ini`：内容为 `open_basedir=/mnt/hgfs/Project/evaluation:/tmp/:/proc/`，是历史 VM 环境残留，在容器内指向不存在的路径，必须排除

- [ ] **Step 2: 提交**

```bash
git add code/project/.dockerignore
git commit -m "[docker] 新增 .dockerignore"
```

---

### Task 7: 创建 docker-compose.yml

**Files:**
- Create: `docker-compose.yml`

**Interfaces:**
- Consumes: `code/project/Dockerfile`、`docker/mysql/01-company220629.sql`
- Produces: 三个服务 `project` / `mysql` / `redis`，网络 `company220629-network`，卷 `redis_data`；供 Task 8 覆盖、Task 17 验证

- [ ] **Step 1: 创建 docker-compose.yml**

创建仓库根目录 `docker-compose.yml`：

```yaml
services:
  project:
    build:
      context: ./code/project
      dockerfile: Dockerfile
    container_name: company220629-project
    ports:
      - "127.0.0.1:${PROJECT_HOST_PORT:-8089}:80"
    environment:
      - APP_NAME=官网管理系统
      - APP_ENV=local
      - APP_KEY=base64:37EEUd3g7goqcBVM+gL3nKSusFmA3oBcH50pNXc0Wbw=
      - APP_DEBUG=true
      - APP_URL=http://localhost:8089
      - APP_TIMEZONE=PRC
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=company220629
      - DB_USERNAME=root
      - DB_PASSWORD=root_company220629_202609
      - DB_PREFIX=app_
      - REDIS_HOST=redis
      - REDIS_PASSWORD=null
      - REDIS_PORT=6379
      - REDIS_PREFIX=company_
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
      - QUEUE_CONNECTION=sync
      - LOG_CHANNEL=stack
      - LOG_LEVEL=debug
    volumes:
      - ./code/project/storage:/var/www/html/storage
      - ./code/project/public/upload:/var/www/html/public/upload
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_started
    networks:
      - company220629-network

  mysql:
    image: mysql:8.0
    container_name: company220629-mysql
    ports:
      - "127.0.0.1:${MYSQL_HOST_PORT:-13306}:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root_company220629_202609
      MYSQL_DATABASE: company220629
    volumes:
      - ./code/mysql:/var/lib/mysql
      - ./docker/mysql:/docker-entrypoint-initdb.d:ro
    networks:
      - company220629-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: company220629-redis
    ports:
      - "127.0.0.1:${REDIS_HOST_PORT:-16379}:6379"
    volumes:
      - redis_data:/data
    networks:
      - company220629-network

networks:
  company220629-network:
    driver: bridge

volumes:
  redis_data:
```

> 本项目不用 `code/db`，MySQL 数据目录是 `code/mysql`，与 realchip 命名保持一致。

- [ ] **Step 2: 校验 compose 语法**

```bash
docker compose config --quiet && echo "compose 语法 OK"
```

预期：输出 `compose 语法 OK`（无 warning）。

- [ ] **Step 3: 提交**

```bash
git add docker-compose.yml
git commit -m "[docker] 新增 docker-compose.yml"
```

---

### Task 8: 创建 docker-compose.prod.yml

**Files:**
- Create: `docker-compose.prod.yml`

**Interfaces:**
- Consumes: `docker-compose.yml`（同名服务、字段合并）
- Produces: 生产环境覆盖层，由 `docker-compose -f docker-compose.yml -f docker-compose.prod.yml` 加载

- [ ] **Step 1: 创建 docker-compose.prod.yml**

创建仓库根目录 `docker-compose.prod.yml`：

```yaml
# 生产环境覆盖层 —— 只写与 docker-compose.yml 的差异
# 使用: docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
#
# 注意：docker compose 对 ports 是「合并」而非「替换」，
# 因此这里绝不重写 project 的 ports，mysql/redis 用空数组显式清空。

services:
  project:
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_URL=https://www.company220629.com
      - APP_KEY=${APP_KEY}
      - DB_PASSWORD=${DB_PASSWORD}

  mysql:
    ports: []

  redis:
    ports: []
```

- [ ] **Step 2: 校验叠加结果**

```bash
APP_KEY=base64:37EEUd3g7goqcBVM+gL3nKSusFmA3oBcH50pNXc0Wbw= DB_PASSWORD=x \
  docker compose -f docker-compose.yml -f docker-compose.prod.yml config 2>/dev/null \
  | grep -A4 "mysql:" | head -20
```

预期：`mysql` 服务下**没有** `ports` 段；`project` 的 `APP_ENV` 为 `production`。

- [ ] **Step 3: 提交**

```bash
git add docker-compose.prod.yml
git commit -m "[docker] 新增生产环境 compose 覆盖层"
```

---

### Task 9: 创建 GitHub Actions 部署文件

**Files:**
- Create: `.github/workflows/deploy.yml`

**Interfaces:**
- Consumes: 服务器 `/opt/company220629` 下的 git 仓库与 `.env`
- Produces: push 到 `main` 时通过 SSH 自动部署

- [ ] **Step 1: 创建 deploy.yml**

创建 `.github/workflows/deploy.yml`：

```yaml
name: Deploy to VPS

on:
  push:
    branches: [main]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            set -e
            cd /opt/company220629
            git pull origin main
            docker-compose -f docker-compose.yml -f docker-compose.prod.yml down
            docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
            docker image prune -f
```

> 服务器是 CentOS 7.6，用的是 standalone `docker-compose` 二进制，不是 `docker compose` 插件，此处命令不可替换。
> 需要在 GitHub 仓库 Settings → Secrets 里配置三个 secret：`SSH_HOST`（39.108.218.82）、`SSH_USER`（如 `root`）、`SSH_PRIVATE_KEY`。

- [ ] **Step 2: 提交**

```bash
git add .github/workflows/deploy.yml
git commit -m "[docker] 新增 GitHub Actions 自动部署"
```

---

### Task 10: 更新根 .gitignore

**Files:**
- Modify: `.gitignore`（新文件，原 Laravel 版已随 Task 1 移到 `code/project/.gitignore`）

**Interfaces:**
- Produces: 根目录忽略规则：`.env`、`code/mysql/*`、`.DS_Store`

- [ ] **Step 1: 确认原 .gitignore 已随目录迁移**

```bash
git ls-files code/project/.gitignore    # 应输出该路径
git ls-files .gitignore                 # 应无输出
```

- [ ] **Step 2: 创建新的根 .gitignore**

创建仓库根目录 `.gitignore`：

```gitignore
# Docker
.env
code/mysql/*
!code/mysql/.gitkeep

# macOS
.DS_Store
```

- [ ] **Step 3: 提交**

```bash
git add .gitignore code/mysql/.gitkeep
git commit -m "[docker] 新增根 .gitignore"
```

---

### Task 11: 新增 `app/Common/Captcha.php`

**Files:**
- Create: `code/project/app/Common/Captcha.php`

**Interfaces:**
- Consumes: `Illuminate\Support\Facades\Redis`
- Produces:
  - `Captcha::TTL_ADMIN` = 60、`Captcha::TTL_HOME` = 300（int 常量）
  - `Captcha::issue(int $ttl = self::TTL_ADMIN): string` —— 返回 token，同时把 4 位验证码写入 Redis
  - `Captcha::verify(?string $token, ?string $input): bool` —— 一次性校验，无论成败都删除 key
  - `Captcha::render(string $token): ?string` —— 返回 PNG 二进制；token 不存在或已过期返回 `null`
  - Task 12 / Task 13 的控制器直接调用这三个方法

> 与 spec 的差异：spec 写的是 `render(string $code)`。实现改成接收 token，让「查 Redis」这一步留在 Captcha 内部，控制器不直接碰 Redis key。

- [ ] **Step 1: 创建 Captcha 类**

创建 `code/project/app/Common/Captcha.php`：

```php
<?php

namespace App\Common;

use Illuminate\Support\Facades\Redis;

/**
 * 验证码（纯 Redis token 方案）
 *
 * mews/captcha 依赖 BcryptHasher + Session，在 nginx + php-fpm 容器环境下
 * password_verify 不稳定，故改为自实现：Redis 存明文验证码，token 作为 key。
 *
 * @author VincentZheng <1092161320@qq.com> 2026-09-16
 */
class Captcha
{
    /** Redis key 前缀 */
    const KEY_PREFIX = 'captcha:';

    /** 后台登录验证码有效期（秒） */
    const TTL_ADMIN = 60;

    /** 前台留言验证码有效期（秒）—— 需要填表单，放宽到 5 分钟 */
    const TTL_HOME = 300;

    /** 字符集：剔除 0/o、1/l/i、5/s、8/b 等易混淆字符 */
    const CHARSET = '2346789abcdefghjkmnpqrtuvwxyzABCDEFGHJKMNPQRTUVWXYZ';

    /** 验证码位数 */
    const LENGTH = 4;

    const WIDTH = 120;
    const HEIGHT = 36;

    /**
     * 签发验证码，返回 token
     *
     * @param int $ttl 有效期（秒）
     *
     * @return string
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public static function issue(int $ttl = self::TTL_ADMIN): string
    {
        $token = md5(uniqid((string) mt_rand(), true));
        Redis::setex(self::KEY_PREFIX . $token, $ttl, self::generateCode());
        return $token;
    }

    /**
     * 一次性校验：无论成功失败都销毁 token，防止重放
     *
     * @param string|null $token
     * @param string|null $input
     *
     * @return bool
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public static function verify(?string $token, ?string $input): bool
    {
        if (empty($token) || empty($input)) {
            return false;
        }

        $code = Redis::get(self::KEY_PREFIX . $token);
        Redis::del(self::KEY_PREFIX . $token);

        if (empty($code)) {
            return false;
        }

        return strtolower(trim($input)) === strtolower($code);
    }

    /**
     * 按 token 渲染 PNG 图片
     *
     * @param string $token
     *
     * @return string|null PNG 二进制；token 无效或已过期返回 null
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public static function render(string $token): ?string
    {
        if (empty($token)) {
            return null;
        }

        $code = Redis::get(self::KEY_PREFIX . $token);
        if (empty($code)) {
            return null;
        }

        $width = self::WIDTH;
        $height = self::HEIGHT;
        $img = imagecreatetruecolor($width, $height);

        // 背景
        $bg = (int) imagecolorallocate($img, 236, 242, 244);
        imagefilledrectangle($img, 0, 0, $width, $height, $bg);

        // 干扰线
        for ($i = 0; $i < 6; $i++) {
            $c = (int) imagecolorallocate($img, rand(120, 200), rand(120, 200), rand(120, 200));
            imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $c);
        }

        // 噪点
        for ($i = 0; $i < 100; $i++) {
            $c = (int) imagecolorallocate($img, rand(100, 200), rand(100, 200), rand(100, 200));
            imagesetpixel($img, rand(0, $width), rand(0, $height), $c);
        }

        // 字符
        $font = self::getFontPath();
        $textColor = (int) imagecolorallocate($img, 44, 62, 80);
        $length = strlen($code);
        for ($i = 0; $i < $length; $i++) {
            $x = 10 + $i * 26;
            $y = rand(22, 30);
            $angle = rand(-15, 15);
            if ($font !== null) {
                imagettftext($img, 20, $angle, $x, $y, $textColor, $font, $code[$i]);
            } else {
                imagestring($img, 5, $x, $y - 12, $code[$i], $textColor);
            }
        }

        ob_start();
        imagepng($img);
        $binary = ob_get_clean();
        imagedestroy($img);

        return $binary === false ? null : $binary;
    }

    /**
     * 生成随机验证码
     *
     * @return string
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    private static function generateCode(): string
    {
        $charset = self::CHARSET;
        $max = strlen($charset) - 1;
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= $charset[rand(0, $max)];
        }
        return $code;
    }

    /**
     * 查找可用 TTF 字体（容器内由 fonts-dejavu-core 提供）
     *
     * @return string|null
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    private static function getFontPath(): ?string
    {
        $candidates = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
}
```

- [ ] **Step 2: 语法检查**

```bash
php -l code/project/app/Common/Captcha.php
```

预期：`No syntax errors detected in code/project/app/Common/Captcha.php`

- [ ] **Step 3: 提交**

```bash
git add code/project/app/Common/Captcha.php
git commit -m "[captcha] 新增 Captcha 类（纯 Redis token 方案）"
```

---

### Task 12: 后台登录验证码改造

**Files:**
- Create: `code/project/app/Http/Controllers/Admin/CaptchaController.php`
- Modify: `code/project/app/Http/Controllers/Admin/LoginController.php:26-51`
- Modify: `code/project/routes/web.php:5-17`（新增 use）、`:33-35` 之后（新增路由）
- Modify: `code/project/resources/views/admin/login/index.blade.php:53-56`、`:125`

**Interfaces:**
- Consumes: `App\Common\Captcha`（Task 11）—— `issue(int $ttl): string`、`verify(?string $token, ?string $input): bool`、`render(string $token): ?string`、常量 `TTL_ADMIN`
- Produces:
  - HTTP `GET /admin/captcha?token={token}` → `image/png`；token 无效返回 410
  - HTTP `GET /admin/captcha/refresh` → `{"token":"..."}` JSON
  - 登录视图变量 `$captcha_token`（string）
  - `checkLogin` 接受表单字段 `captcha` + `captcha_token`

- [ ] **Step 1: 创建后台 CaptchaController**

创建 `code/project/app/Http/Controllers/Admin/CaptchaController.php`：

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Common\Captcha;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 后台验证码
 *
 * @author VincentZheng <1092161320@qq.com> 2026-09-16
 */
class CaptchaController extends Controller
{
    /**
     * 输出验证码图片
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public function index(Request $request)
    {
        $png = Captcha::render((string) $request->input('token', ''));
        if ($png === null) {
            // token 缺失或已过期，前端收到非图片响应会触发 onerror → refreshCaptcha()
            return response('', 410);
        }

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * 刷新验证码，返回新 token
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public function refresh()
    {
        return response()->json(['token' => Captcha::issue(Captcha::TTL_ADMIN)]);
    }
}
```

- [ ] **Step 2: 改 LoginController**

把 `code/project/app/Http/Controllers/Admin/LoginController.php` 改为：

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Common\Captcha;
use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\LoginLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller {

    protected $loginLogic;

    public function __construct(LoginLogic $loginLogic)
    {
        $this->loginLogic = $loginLogic;
    }

    /**
     * 登录页
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public function index()
    {
        return view('admin.login.index', [
            'captcha_token' => Captcha::issue(Captcha::TTL_ADMIN),
        ]);
    }

    /**
     * 检查登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public function checkLogin(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'captcha' => 'required',
            'captcha_token' => 'required',
        ], [
            'captcha.required' => '验证码不能为空',
            'captcha_token.required' => '验证码已失效，请点击图片刷新',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 校验验证码（一次性，校验后即销毁）
        $captchaOk = Captcha::verify(
            $request->input('captcha_token'),
            $request->input('captcha')
        );
        if (!$captchaOk) {
            return $this->fail(1001, '验证码不正确。');
        }

        // 3: 执行检测登录操作
        $username = $request->input('username');
        $password = $request->input('password');
        $ip = $request->getClientIp();
        $checkLogin = $this->loginLogic->checkLogin($username, $password, $ip);
        if ($checkLogin['code'] !== 0) {
            return $this->fail($checkLogin['code'], $checkLogin['message']);
        } else {
            return $this->success('登录成功');
        }
    }

    /**
     * 退出登录
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-16
     */
    public function loginOut()
    {
        session([
            'login_admin_id' => null,
            'login_username' => null,
            'login_role_id' => null,
        ]);

        return $this->success('退出登录成功');
    }



}
```

- [ ] **Step 3: 加后台验证码路由**

在 `code/project/routes/web.php` 顶部 use 区，`use App\Http\Controllers\Admin\LoginController;` 之前插入一行：

```php
use App\Http\Controllers\Admin\CaptchaController;
```

在 `Route::prefix('/admin')->group(function () {` 之后、`Route::get('/', [IndexController::class, 'index']);` 之前插入：

```php
    // 验证码（登录页使用，无需登录态）
    Route::get('/captcha', [CaptchaController::class, 'index']);
    Route::get('/captcha/refresh', [CaptchaController::class, 'refresh']);
```

- [ ] **Step 4: 改登录页视图**

在 `code/project/resources/views/admin/login/index.blade.php` 中：

把第 53 行的

```html
                    <input type="text" name="captcha" lay-verify="required|captcha" placeholder="图形验证码" autocomplete="off" class="layui-input verification captcha">
```

改为

```html
                    <input type="text" name="captcha" lay-verify="required" placeholder="图形验证码" autocomplete="off" class="layui-input verification captcha">
                    <input type="hidden" name="captcha_token" id="captchaToken" value="{{ $captcha_token }}">
```

把第 55 行的

```html
                        <img id="captchaPic" src="{{ captcha_src('flat') }}" onclick="this.src='{{captcha_src('flat')}}'+Math.random()">
```

改为

```html
                        <img id="captchaPic" src="/admin/captcha?token={{ $captcha_token }}" onclick="refreshCaptcha()" onerror="refreshCaptcha()">
```

在 `<script>` 块内、`layui.use(['form'], function () {` **之前**插入：

```javascript
    // 刷新验证码：向后台换一个新 token，再按新 token 取图
    function refreshCaptcha() {
        $.get('/admin/captcha/refresh', function (res) {
            $('#captchaToken').val(res.token);
            $('#captchaPic').attr('src', '/admin/captcha?token=' + res.token + '&' + Math.random());
        });
    }
```

把第 125 行的

```javascript
                            $("#captchaPic").attr("src", "{{ captcha_src('flat') }}" + Math.random());
```

改为

```javascript
                            refreshCaptcha();
```

- [ ] **Step 5: 语法检查**

```bash
php -l code/project/app/Http/Controllers/Admin/CaptchaController.php
php -l code/project/app/Http/Controllers/Admin/LoginController.php
php -l code/project/routes/web.php
grep -rn "captcha_src" code/project/resources/views/admin/ || echo "后台视图已无 captcha_src 残留"
```

预期：三条 `No syntax errors detected`，最后一条输出 `后台视图已无 captcha_src 残留`。

- [ ] **Step 6: 提交**

```bash
git add code/project/app/Http/Controllers/Admin/CaptchaController.php \
        code/project/app/Http/Controllers/Admin/LoginController.php \
        code/project/routes/web.php \
        code/project/resources/views/admin/login/index.blade.php
git commit -m "[captcha] 后台登录改用 Redis token 验证码"
```

---

### Task 13: 前台留言验证码改造

**Files:**
- Create: `code/project/app/Http/Controllers/Index/CaptchaController.php`
- Modify: `code/project/app/Http/Controllers/Index/MessageController.php:15-49`
- Modify: `code/project/routes/home.php:3-9`（新增 use）、`:54` 之后（新增路由）
- Modify: `code/project/resources/views/index/message/message.blade.php:42-43`、`:72`、`:61-80`

**Interfaces:**
- Consumes: `App\Common\Captcha`（Task 11）—— 同 Task 12，TTL 用常量 `TTL_HOME`
- Produces:
  - HTTP `GET /verifyCode?token={token}` → `image/png`；token 无效返回 410
  - HTTP `GET /verifyCode/refresh` → `{"token":"..."}` JSON
  - 留言视图变量 `$captcha_token`（string）
  - `sendMessage` 接受表单字段 `captcha` + `captcha_token`

> 前台不能用 `/captcha`：该路径已被 `mews/captcha` 的 `CaptchaServiceProvider` 全局注册（`captcha/{config?}`），会与本项目路由冲突。

- [ ] **Step 1: 创建前台 CaptchaController**

创建 `code/project/app/Http/Controllers/Index/CaptchaController.php`：

```php
<?php

namespace App\Http\Controllers\Index;

use App\Common\Captcha;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 前台验证码
 *
 * @author VincentZheng <1092161320@qq.com> 2026-09-16
 */
class CaptchaController extends Controller
{
    /**
     * 输出验证码图片
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public function index(Request $request)
    {
        $png = Captcha::render((string) $request->input('token', ''));
        if ($png === null) {
            return response('', 410);
        }

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * 刷新验证码，返回新 token
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public function refresh()
    {
        return response()->json(['token' => Captcha::issue(Captcha::TTL_HOME)]);
    }
}
```

- [ ] **Step 2: 改 MessageController**

把 `code/project/app/Http/Controllers/Index/MessageController.php` 改为：

```php
<?php

namespace App\Http\Controllers\Index;

use App\Common\Captcha;
use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\MessageLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{

    public function message(Request $request)
    {
        return view('index.message.message', [
            'captcha_token' => Captcha::issue(Captcha::TTL_HOME),
        ]);
    }

    public function sendMessage(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            // 'name' => 'required',
            // 'phone' => 'required',
            // 'email' => 'required|email',
            'content' => 'required',
            'captcha' => 'required',
            'captcha_token' => 'required',
        ], [
            'name.required' => '您的称呼不能为空',
            'email.required' => '邮箱地址不能为空',
            'email.email' => '邮箱地址格式有误',
            'phone.required' => '联系电话不能为空',
            'content.required' => '您的需求信息不能为空',
            'captcha.required' => '验证码不能为空',
            'captcha_token.required' => '验证码已失效，请点击图片刷新',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1002, $msg);
        }

        // 2: 校验验证码（一次性，校验后即销毁）
        $captchaOk = Captcha::verify(
            $request->input('captcha_token'),
            $request->input('captcha')
        );
        if (!$captchaOk) {
            return $this->fail(1002, '验证码不正确。');
        }

        // 3: 获取请求数据
        $data = $request->all();
        $data['ip'] = $request->getClientIp();

        // 4: 执行留言
        $res = MessageLogic::sendMessage($data);
        return $this->fail($res['code'], $res['message']);
    }
}
```

- [ ] **Step 3: 加前台验证码路由**

在 `code/project/routes/home.php` 的 use 区，`use App\Http\Controllers\Index\MessageController;` 之后插入一行：

```php
use App\Http\Controllers\Index\CaptchaController;
```

在 `Route::post('/sendMessage', [MessageController::class, 'sendMessage']); // 新闻栏目` 之后插入：

```php
// 验证码（前台不能用 /captcha，该路径被 mews/captcha 包占用）
Route::get('/verifyCode', [CaptchaController::class, 'index']);
Route::get('/verifyCode/refresh', [CaptchaController::class, 'refresh']);
```

- [ ] **Step 4: 改留言页视图**

在 `code/project/resources/views/index/message/message.blade.php` 中：

把第 42-43 行的

```html
                                        <input type="text" class="code" name="captcha" value="" placeholder="验证码" data-required="required" null="请输入验证码" maxlength="4">
                                        <img id="captchaPic" src="{{ captcha_src('flat') }}" onclick="this.src='{{captcha_src('flat')}}'+Math.random()">
```

改为

```html
                                        <input type="text" class="code" name="captcha" value="" placeholder="验证码" data-required="required" null="请输入验证码" maxlength="4">
                                        <input type="hidden" name="captcha_token" id="captchaToken" value="{{ $captcha_token }}">
                                        <img id="captchaPic" src="/verifyCode?token={{ $captcha_token }}" onclick="refreshCaptcha()" onerror="refreshCaptcha()">
```

把整个 `<script type="text/javascript">` 块（第 61-80 行）替换为：

```javascript
    <script type="text/javascript">
        // 刷新验证码：向后台换一个新 token，再按新 token 取图
        function refreshCaptcha() {
            $.get('/verifyCode/refresh', function (res) {
                $('#captchaToken').val(res.token);
                $('#captchaPic').attr('src', '/verifyCode?token=' + res.token + '&' + Math.random());
            });
        }

        $(function() {
            $('#messageButton').on("click", function() {
                $.ajax({
                    url: '/sendMessage?_token={{ csrf_token() }}',
                    type: 'POST',
                    dataType: 'json',
                    data: $("#form").serialize(),
                    success: function (res) {
                        alert(res.message);
                        if (res.code !== 0) {
                            // 验证码错误或已过期：换新 token，表单已填内容保留
                            refreshCaptcha();
                        } else {
                            location.reload();
                        }
                    }
                })
            });
        });
    </script>
```

- [ ] **Step 5: 语法检查**

```bash
php -l code/project/app/Http/Controllers/Index/CaptchaController.php
php -l code/project/app/Http/Controllers/Index/MessageController.php
php -l code/project/routes/home.php
grep -rn "captcha_src" code/project/resources/views/ || echo "视图已无 captcha_src 残留"
```

预期：三条 `No syntax errors detected`，最后一条输出 `视图已无 captcha_src 残留`。

- [ ] **Step 6: 提交**

```bash
git add code/project/app/Http/Controllers/Index/CaptchaController.php \
        code/project/app/Http/Controllers/Index/MessageController.php \
        code/project/routes/home.php \
        code/project/resources/views/index/message/message.blade.php
git commit -m "[captcha] 前台留言改用 Redis token 验证码"
```

---

### Task 14: 创建服务器配置指南

**Files:**
- Create: `docs/server-setup-guide.md`

**Interfaces:**
- Produces: 从裸机到 HTTPS 上线的完整操作手册

- [ ] **Step 1: 创建 docs/server-setup-guide.md**

创建 `docs/server-setup-guide.md`：

````markdown
# 服务器部署指南

服务器：`39.108.218.82`（CentOS 7.6）
域名：`www.company220629.com`
部署路径：`/opt/company220629`

> 本服务器上同时运行 realchip 项目（占用 8088/3306/6379）。本项目使用
> 8089/13306/16379，互不冲突。执行本文档命令时注意不要动 `/opt/realchip`。

---

## 一、SSH 密钥配置

在**本地机器**生成专用部署密钥（已有可跳过）：

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/company220629_deploy -N ""
```

把公钥装到服务器：

```bash
ssh-copy-id -i ~/.ssh/company220629_deploy.pub root@39.108.218.82
```

验证免密登录：

```bash
ssh -i ~/.ssh/company220629_deploy root@39.108.218.82 "echo ok"
```

把**私钥内容**填到 GitHub 仓库 Secrets：

| Secret 名 | 值 |
| --- | --- |
| `SSH_HOST` | `39.108.218.82` |
| `SSH_USER` | `root` |
| `SSH_PRIVATE_KEY` | `~/.ssh/company220629_deploy` 的完整内容 |

```bash
cat ~/.ssh/company220629_deploy   # 复制输出（含 BEGIN/END 行）
```

---

## 二、安装 Docker CE

CentOS 7.6 用 yum 安装，并配置国内镜像加速：

```bash
yum install -y yum-utils
yum-config-manager --add-repo https://mirrors.aliyun.com/docker-ce/linux/centos/docker-ce.repo
yum install -y docker-ce docker-ce-cli containerd.io
systemctl enable docker && systemctl start docker
```

配置镜像加速（国内拉取 Docker Hub 镜像慢）：

```bash
mkdir -p /etc/docker
cat > /etc/docker/daemon.json <<'EOF'
{
  "registry-mirrors": ["https://docker.1ms.run", "https://docker.xuanyuan.me"]
}
EOF
systemctl restart docker
docker info | grep -A3 "Registry Mirrors"
```

> CentOS 7.6 是 cgroup v1，Docker CE 会自动适配，无需额外参数。

---

## 三、安装 docker-compose

CentOS 7 上 Docker 官方已不再提供 compose 插件，必须装 standalone 二进制：

```bash
curl -L "https://github.com/docker/compose/releases/download/v2.24.6/docker-compose-$(uname -s)-$(uname -m)" \
  -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
docker-compose --version
```

> 部署脚本里用的是 `docker-compose` 而非 `docker compose`，就是为此。

---

## 四、防火墙配置

```bash
systemctl enable firewalld && systemctl start firewalld
firewall-cmd --permanent --add-service=ssh
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload
firewall-cmd --list-all
```

> 8089/13306/16379 都绑在 `127.0.0.1` 上，不需要也不应该开放。
> 阿里云安全组同样只需放行 22 / 80 / 443。

---

## 五、克隆项目并首次启动

```bash
mkdir -p /opt && cd /opt
git clone <仓库地址> company220629
cd /opt/company220629
```

创建生产环境变量文件（compose 的 `${APP_KEY}` / `${DB_PASSWORD}` 从这里读）：

```bash
cat > /opt/company220629/.env <<'EOF'
APP_KEY=base64:37EEUd3g7goqcBVM+gL3nKSusFmA3oBcH50pNXc0Wbw=
DB_PASSWORD=root_company220629_202609
EOF
chmod 600 /opt/company220629/.env
```

首次启动（第一次会自动导入 `docker/mysql/01-company220629.sql`，耗时 1-2 分钟）：

```bash
cd /opt/company220629
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
docker-compose ps
curl -I http://127.0.0.1:8089
```

预期：三个容器都是 `Up`，curl 返回 `HTTP/1.1 200 OK`。

上传站点图片（`public/upload` 不在 git 中，必须从本地同步，约 100MB）：

```bash
# 在本地执行
rsync -avz --progress public/upload/ root@39.108.218.82:/opt/company220629/code/project/public/upload/
```

---

## 六、宿主 Nginx 反向代理

realchip 已经占用了宿主 Nginx 的 80/443，本项目新增一个 server 块即可。

```bash
cat > /etc/nginx/conf.d/company220629.conf <<'EOF'
server {
    listen 80;
    server_name www.company220629.com company220629.com;

    location / {
        proxy_pass http://127.0.0.1:8089;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 20M;
    }
}
EOF

nginx -t && systemctl reload nginx
```

> CentOS 上 yum 装的 Nginx，`conf.d/*.conf` 默认已包含在 `http` 块内；若没有，
> 把这行加进 `/etc/nginx/nginx.conf` 的 `http {}`：`include /etc/nginx/conf.d/*.conf;`

---

## 七、申请 SSL 证书

```bash
yum install -y certbot python2-certbot-nginx
certbot --nginx -d www.company220629.com -d company220629.com
```

certbot 会自动改写上面的 server 块并加上 443。验证自动续期：

```bash
certbot renew --dry-run
```

---

## 八、日常更新

推送到 `main` 分支即自动部署（GitHub Actions）。手动部署：

```bash
cd /opt/company220629
git pull origin main
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
docker image prune -f
```

查看日志：

```bash
docker-compose logs -f project
docker-compose exec mysql mysql -uroot -p company220629 -e "show tables;"
docker-compose exec redis redis-cli KEYS 'captcha:*'
```

---

## 九、架构图

```
                      公网
                       │
              ┌────────▼────────┐
              │  宿主 Nginx     │  80 / 443（realchip 共用同一 Nginx）
              │  certbot SSL    │
              └───┬─────────┬───┘
      127.0.0.1:8089    127.0.0.1:8088
                  │         │
        ┌─────────▼──┐  ┌───▼────────┐
        │ company220629 │ │ realchip   │
        │  project    │  │  project   │
        │ (nginx+fpm) │  │            │
        └──┬───────┬──┘  └────────────┘
           │       │
   company220629-network（独立 bridge 网络）
           │       │
    ┌──────▼──┐ ┌──▼─────┐
    │  mysql  │ │ redis  │   端口只在网络内，宿主仅 127.0.0.1
    │  8.0    │ │ 7      │
    └─────────┘ └────────┘
```
````

- [ ] **Step 2: 提交**

```bash
git add docs/server-setup-guide.md
git commit -m "[docs] 新增服务器部署指南"
```

---

### Task 15: 创建 CLAUDE.md

**Files:**
- Create: `CLAUDE.md`

**Interfaces:**
- Produces: 项目级 Claude Code 指南（技术栈、目录约定、三层架构、Docker 命令）

- [ ] **Step 1: 创建 CLAUDE.md**

创建仓库根目录 `CLAUDE.md`：

````markdown
# CLAUDE.md

本文件为 Claude Code 在本仓库工作时的项目指南。

## 项目概述

官网管理系统（企业官网 + 后台管理），Laravel 8.65 / PHP 8.1。
前台（PC + WAP）展示产品、新闻、资质，提供在线留言；后台管理全部内容与留言。

## 技术栈

- PHP 8.1、Laravel 8.65
- MySQL 8.0，表前缀 `app_`
- Redis（Session / Cache / 验证码）
- 前端：jQuery + Layui 2.6.8（后台）、原生 jQuery（前台）
- 容器：Docker Compose（project / mysql / redis 三容器）

## 目录结构

```
company220629_new/
├── code/project/          # Laravel 应用 + Dockerfile / nginx.conf / php.ini / 入口脚本
│   └── app/
│       ├── Common/        # 公共类（Captcha、helper.php）
│       ├── Http/
│       │   ├── Controllers/   # 控制器：Admin（后台）/ Index（PC 前台）/ Wap（手机前台）
│       │   ├── Logic/         # 业务逻辑层
│       │   └── Middleware/    # check.admin.login 等
│       └── Models/        # 模型层
├── code/mysql/            # MySQL 数据目录（volume 挂载，git 忽略）
├── docker/mysql/          # 初始化 SQL，首次启动自动导入
├── docker-compose.yml
├── docker-compose.prod.yml
├── docs/
│   ├── server-setup-guide.md
│   └── superpowers/{specs,plans}/
└── tasks/todo.md
```

## 架构约定

三层架构，**严格分层，不要跨层调用**：

```
Controller（参数校验、调 Logic、返回 JSON）
    → Logic（业务逻辑、事务）
        → Model（数据访问）
```

- 控制器继承 `App\Http\Controllers\Controller`，用 `$this->success($msg, $data)` /
  `$this->fail($code, $msg)` 返回；上传接口用 `uploadSuccess()` / `uploadFail()`
  （注意上传接口返回字段是 `msg` 而不是 `message`）
- 后台路由前缀 `/admin`，登录态中间件 `check.admin.login`
- 路由分两个文件：`routes/web.php`（后台）、`routes/home.php`（前台 + WAP）
- 视图通过 `AppServiceProvider` 的 `View::share` 拿到全局变量
  `$title`、`$staticUrl`、`$staticAdminUrl`、`$staticWapUrl`

## 验证码

`mews/captcha` 包仍在依赖里但**已不使用**（BcryptHasher + Session 在容器内不可靠）。
现用 `App\Common\Captcha`：

- `Captcha::issue(int $ttl): string` —— 签发 token，Redis key `captcha:{token}`
- `Captcha::verify(?string $token, ?string $input): bool` —— 一次性校验，校验后即删
- `Captcha::render(string $token): ?string` —— 渲染 PNG，token 失效返回 null

后台 TTL 60s（`Captcha::TTL_ADMIN`），前台 300s（`Captcha::TTL_HOME`）。
前台图片地址是 `/verifyCode`，**不能**用 `/captcha` —— 该路径被 `mews/captcha`
的 ServiceProvider 全局注册占用。

## Docker

本地开发：

```bash
docker compose up -d --build          # 启动，访问 http://localhost:8089
docker compose logs -f project
docker compose exec project sh
docker compose down                   # 停止
docker compose down -v                # 停止并删除 redis 数据卷
```

生产部署（服务器上）：

```bash
cd /opt/company220629
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f project
```

端口分配（与同机 realchip 错开）：

| 服务 | 宿主机端口 | 容器内 |
| --- | --- | --- |
| project | 127.0.0.1:8089 | 80 |
| mysql | 127.0.0.1:13306 | 3306 |
| redis | 127.0.0.1:16379 | 6379 |

数据库操作：

```bash
docker compose exec mysql mysql -uroot -proot_company220629_202609 company220629
docker compose exec redis redis-cli
```

## 注意事项

- MySQL 初始化 SQL 只在 `code/mysql/` 为空时执行一次；要重跑必须先清空该目录
- `public/upload` 不在 git 中，新环境部署后需 rsync 上传
- 服务器是 CentOS 7.6，用 standalone `docker-compose`，不是 `docker compose` 插件
- 改端口要同时改 `docker-compose.yml` 和 `docs/server-setup-guide.md`
````

- [ ] **Step 2: 提交**

```bash
git add CLAUDE.md
git commit -m "[docs] 新增 CLAUDE.md 项目指南"
```

---

### Task 16: 创建任务清单 tasks/todo.md

**Files:**
- Create: `tasks/todo.md`

**Interfaces:**
- Produces: 本次 Docker 化的任务勾选表 + 文件清单 + 约束核对

- [ ] **Step 1: 创建 tasks/todo.md**

创建 `tasks/todo.md`：

```markdown
# Docker 化任务清单

- [ ] 1. 目录重构（应用迁入 code/project）
- [ ] 2. 创建 Dockerfile
- [ ] 3. 创建 nginx.conf
- [ ] 4. 创建 php.ini
- [ ] 5. 创建 docker-entrypoint.sh
- [ ] 6. 创建 .dockerignore
- [ ] 7. 创建 docker-compose.yml
- [ ] 8. 创建 docker-compose.prod.yml
- [ ] 9. 创建 GitHub Actions 部署文件
- [ ] 10. 更新根 .gitignore
- [ ] 11. 新增 app/Common/Captcha.php
- [ ] 12. 后台登录验证码改造
- [ ] 13. 前台留言验证码改造
- [ ] 14. 创建服务器配置指南
- [ ] 15. 创建 CLAUDE.md
- [ ] 16. 创建 tasks/todo.md
- [ ] 17. 本地构建验证

## 评审

### 完成内容

待填。

### 约束核对

| 约束 | 落实方式 |
| --- | --- |
| 与 realchip 端口不冲突 | project 8089、mysql 13306、redis 16379，全部绑 127.0.0.1 |
| 容器名不冲突 | company220629-{project,mysql,redis} |
| 网络不冲突 | company220629-network |
| 单容器 | php-fpm -D + nginx 前台，同容器 |
| 表前缀 app_ | DB_PREFIX=app_ |
| 验证码可用 | 纯 Redis token，后台 60s / 前台 300s |
```

- [ ] **Step 2: 提交**

```bash
git add tasks/todo.md
git commit -m "[docs] 新增 Docker 化任务清单"
```

---

### Task 17: 本地构建验证

**Files:**
- Modify: `tasks/todo.md`（回填评审）

**Interfaces:**
- Consumes: Task 1-16 的全部产出
- Produces: 可上线结论，或失败点清单

- [ ] **Step 1: 清理旧状态后构建启动**

```bash
cd /Users/vincentzheng/ProjectPHP/company220629_new
docker compose down -v
rm -rf code/mysql/* code/mysql/.gitkeep && touch code/mysql/.gitkeep
docker compose up -d --build
```

预期：三个容器启动。首次 MySQL 初始化约 1-2 分钟。

- [ ] **Step 2: 确认容器与初始化 SQL**

```bash
docker compose ps
docker compose exec -T mysql mysql -uroot -proot_company220629_202609 \
  -e "use company220629; show tables;" | head -20
```

预期：三个容器 `Up`（mysql 为 `Up (healthy)`）；输出 13 张 `app_` 前缀表。

- [ ] **Step 3: 确认 PHP 扩展与时区**

```bash
docker compose exec -T project php -m | grep -E "^(pdo_mysql|mysqli|gd|zip|mbstring|dom|redis|opcache)$"
docker compose exec -T project php -r "echo date_default_timezone_get(), PHP_EOL;"
docker compose exec -T project ls -l /var/www/html/public/storage
```

预期：8 个扩展全部列出；时区 `Asia/Shanghai`；`public/storage` 是指向
`/var/www/html/storage/app/public` 的符号链接。

- [ ] **Step 4: 验证前台页面与静态资源**

```bash
for p in / /index.html /about.html /contact.html /product.html /news.html /qualification.html /message.html /wap/; do
  printf "%-22s %s\n" "$p" "$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8089$p)"
done
curl -s -o /dev/null -w "static: %{http_code}\n" "http://localhost:8089/static/image/1589001488755051.jpg"
```

预期：全部 `200`（`/static/image/...` 若该图不存在则为 404，换一张 `public/static/image/` 下真实存在的文件名重试）。

- [ ] **Step 5: 验证前台验证码流程**

```bash
# 1) 取留言页，拿到 captcha_token
TOKEN=$(curl -s http://localhost:8089/message.html | grep -o 'name="captcha_token" id="captchaToken" value="[^"]*"' | sed 's/.*value="//;s/"//')
echo "token=$TOKEN"

# 2) 取图片，确认是 PNG
curl -s -D- -o /tmp/captcha.png "http://localhost:8089/verifyCode?token=$TOKEN" | grep -i "content-type"
file /tmp/captcha.png

# 3) 从 Redis 读出明文验证码
CODE=$(docker compose exec -T redis redis-cli --raw GET "captcha:$TOKEN")
echo "code=$CODE"
docker compose exec -T redis redis-cli TTL "captcha:$TOKEN"     # 预期 ≤300

# 4) 提交留言，确认入库
curl -s -X POST "http://localhost:8089/sendMessage" \
  -H "Cookie: XSRF-TOKEN=$(curl -s -c /tmp/cj http://localhost:8089/message.html >/dev/null; grep XSRF-TOKEN /tmp/cj | awk '{print $7}')" \
  --data-urlencode "content=容器化验证测试" \
  --data-urlencode "captcha=$CODE" \
  --data-urlencode "captcha_token=$TOKEN"
```

预期：第 2 步 `Content-Type: image/png` 且 `file` 识别为 PNG；第 3 步 TTL 在 295-300 之间；第 4 步返回 JSON。

> CSRF 校验若失败，直接从页面 HTML 里取 `_token` 值带在 `?_token=` 查询串上（页面里的
> `_token` 是明文，`XSRF-TOKEN` cookie 是加密的，二者不通用）：
> `curl -s http://localhost:8089/message.html | grep -o '_token[^&"]*' | head -1`

- [ ] **Step 6: 确认留言落库、验证码已销毁**

```bash
docker compose exec -T mysql mysql -uroot -proot_company220629_202609 \
  -e "use company220629; select message_id, content, ip, create_time from app_message order by message_id desc limit 3;"
docker compose exec -T redis redis-cli EXISTS "captcha:$TOKEN"   # 预期 0（一次性已删）
```

从 `app_message` 里删除本次测试数据。

- [ ] **Step 7: 验证后台验证码与登录**

```bash
# 1) 取登录页 token
LTOKEN=$(curl -s http://localhost:8089/admin/login/index | grep -o 'id="captchaToken" value="[^"]*"' | sed 's/.*value="//;s/"//')
curl -s -o /dev/null -w "captcha img: %{http_code} %{content_type}\n" "http://localhost:8089/admin/captcha?token=$LTOKEN"
docker compose exec -T redis redis-cli TTL "captcha:$LTOKEN"     # 预期 ≤60

# 2) 用错误验证码提交，应被拦下
curl -s -X POST "http://localhost:8089/admin/login/checkLogin" \
  --data-urlencode "username=admin" --data-urlencode "password=x" \
  --data-urlencode "captcha=zzzz" --data-urlencode "captcha_token=$LTOKEN"
```

预期：第 1 步 `200 image/png`，TTL ≤60；第 2 步返回
`{"code":1001,"message":"验证码不正确。"}`。

> 这一步**故意用错密码**：只要返回的是「验证码不正确」而不是
> 「验证码不能为空」，就证明 token 校验链路是通的，不需要知道后台真实密码。

- [ ] **Step 8: 验证会话写入 Redis**

```bash
docker compose exec -T redis redis-cli KEYS "company_*" | head
```

预期：能看到 `company_` 前缀的 session key，证明 `SESSION_DRIVER=redis` +
`CACHE_DRIVER=redis` 生效。

- [ ] **Step 9: 验证生产覆盖层**

```bash
APP_KEY=base64:37EEUd3g7goqcBVM+gL3nKSusFmA3oBcH50pNXc0Wbw= DB_PASSWORD=x \
  docker compose -f docker-compose.yml -f docker-compose.prod.yml config > /tmp/prodconf.yml
grep -c "published:" /tmp/prodconf.yml      # 预期只有 project 一个服务有 published 端口
grep -A2 "APP_ENV" /tmp/prodconf.yml | head -5
```

预期：`published` 只出现在 project 下，`APP_ENV` 为 `production`。

- [ ] **Step 10: 停止容器并回填评审**

```bash
docker compose down
```

把 `tasks/todo.md` 的 17 个复选框全部改为 `[x]`，并在「完成内容」补上文件清单与验证结果。

- [ ] **Step 11: 提交**

```bash
git add tasks/todo.md
git commit -m "[docs] 回填 Docker 化任务清单评审"
```

---

## Self-Review 检查

**1. Spec 覆盖**

| Spec 章节 | 对应 Task |
| --- | --- |
| 目录结构 / 迁移方式 | Task 1 |
| project 容器（扩展、字体、入口、卷、clear_env 说明） | Task 2、4、5 |
| mysql 容器 | Task 7 |
| redis 容器 | Task 7 |
| 端口分配（8089/13306/16379，绑 127.0.0.1） | Task 7、8 |
| 数据库配置（含 CACHE_DRIVER=redis 的理由） | Task 7 |
| 验证码改造（含 TTL 两档、`/verifyCode` 绕开 mews/captcha） | Task 11、12、13 |
| 生产环境差异 | Task 8、9 |
| 部署流程 6 步 | Task 9、14 |
| 与 realchip 四处刻意偏离 | Task 6（1、excl vendor）、Task 7（2、端口）、Task 11（3、共用 Captcha 类）、Task 11/12/13（4、前台 300s） |
| 约束（PHP 8.1 / 单容器 / MySQL 8.0 / app_ / CentOS 7.6 / 域名 / 同机） | Global Constraints + Task 7、9、14 |

无遗漏。另外计划里增加了 spec 未提但必须做的两项：`composer.json` 的
`allow-plugins`（Task 2 Step 1，不做构建直接失败）、排除 `.user.ini`
（Task 6，其 `open_basedir` 指向不存在的路径）。

**2. 占位符扫描**

无 TBD / TODO / 「稍后补充」。Task 16 的「完成内容」写「待填」，是任务清单本身的
初始状态，Task 17 Step 10 会回填。

**3. 类型一致性**

- `Captcha::issue(int $ttl = self::TTL_ADMIN): string` —— Task 11 定义，Task 12/13 调用
- `Captcha::verify(?string $token, ?string $input): bool` —— Task 11 定义，Task 12/13 调用
- `Captcha::render(string $token): ?string` —— Task 11 定义，Task 12/13 的 `index()` 调用
- `Captcha::TTL_ADMIN` / `Captcha::TTL_HOME` —— Task 11 定义，Task 12/13 使用
- 视图变量统一为 `$captcha_token`（Task 12/13 的控制器传入，Blade 渲染）
- 表单字段统一为 `captcha` + `captcha_token`（视图 hidden input 与控制器 `input()` 一致）
- 路由前缀：后台 `/admin/captcha`、前台 `/verifyCode`（Task 12/13 定义，Task 17 验证）
