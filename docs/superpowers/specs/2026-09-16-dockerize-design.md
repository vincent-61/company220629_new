# Docker 化设计规格

> 参照 `realchip_new` 于 2026-07-24 完成的 Docker 改造，对本项目（官网管理系统）做同等改造。
> 两个项目部署在**同一台服务器**（39.108.218.82），端口必须错开。

## 目标

将 Laravel 8.65 单体应用容器化，支持本地开发和生产部署，通过 GitHub Actions 自动部署到
CentOS 7.6 服务器（39.108.218.82）。

## 目录结构

```
company220629_new/
├── .github/workflows/
│   └── deploy.yml                      # GitHub Actions 自动部署
├── tasks/
│   └── todo.md                         # 任务清单 + 评审
├── CLAUDE.md                           # 完整项目指南
├── .gitignore                          # .env / code/mysql/* / .DS_Store
├── docker-compose.yml                  # 本地 Docker 编排
├── docker-compose.prod.yml             # 生产环境覆盖层
├── code/
│   ├── project/                        # Laravel 项目（原仓库根的全部文件）
│   │   ├── Dockerfile                  # PHP 8.1-FPM + Nginx
│   │   ├── nginx.conf
│   │   ├── php.ini
│   │   ├── docker-entrypoint.sh
│   │   └── .dockerignore
│   └── mysql/
│       └── .gitkeep                    # MySQL 数据持久化（volume 挂载，git 忽略）
├── docker/
│   └── mysql/
│       └── 01-company220629.sql        # 首次启动自动导入
└── docs/
    ├── server-setup-guide.md
    └── superpowers/
        ├── specs/2026-09-16-dockerize-design.md
        └── plans/2026-09-16-dockerize-plan.md
```

迁移方式：

- `git mv` 根目录全部 Laravel 文件到 `code/project/`，单独一个 commit，git 可识别为 rename
- `git mv docs/company220629.sql docker/mysql/01-company220629.sql`，SQL 只保留一份，避免两份各自漂移

## 服务定义

### project 容器

- **构建**: `code/project/Dockerfile`，基础镜像 `php:8.1-fpm`
- **PHP 扩展**: `pdo_mysql`、`mysqli`、`gd`（intervention/image）、`zip`、`mbstring`、`dom`（phpspreadsheet）、`opcache`、`redis`（pecl，Laravel 8 默认 phpredis 驱动）
- **字体**: `fonts-dejavu-core`，供验证码 TTF 渲染
- **Web 服务器**: Nginx，同容器内运行，监听 80
- **入口**: `docker-entrypoint.sh` → 补目录/权限 → `php-fpm -D` → `nginx -g 'daemon off;'`
- **卷挂载**: `./code/project/storage:/var/www/html/storage`、`./code/project/public/upload:/var/www/html/public/upload`
- **环境变量**: 全部由 docker-compose 的 `environment` 注入，不依赖容器内 `.env`

> 官方 `php:8.1-fpm` 镜像的 `php-fpm.d/docker.conf` 已设 `clear_env = no`，环境变量可正常透传到 worker，无需额外配置。

### mysql 容器

- **镜像**: `mysql:8.0`
- **数据库**: `company220629`，用户 `root`
- **卷挂载**: `./code/mysql:/var/lib/mysql`、`./docker/mysql:/docker-entrypoint-initdb.d:ro`
- **健康检查**: `mysqladmin ping`
- **生产环境不暴露端口**

### redis 容器

- **镜像**: `redis:7-alpine`
- **卷挂载**: `redis_data:/data`
- **生产环境不暴露端口**

## 端口分配（同服务器与 realchip 共存）

| 服务 | realchip（已占用） | 本项目 | 说明 |
| --- | --- | --- | --- |
| project → 80 | `127.0.0.1:8088` | `127.0.0.1:8089` | 宿主机 Nginx 反代目标 |
| mysql → 3306 | `127.0.0.1:3306` | `127.0.0.1:13306` | 仅本地开发用 |
| redis → 6379 | `127.0.0.1:6379` | `127.0.0.1:16379` | 仅本地开发用 |

全部绑 `127.0.0.1`，不暴露到公网。

不冲突的资源：容器名（`company220629-*` vs `realchip-*`）、网络（`company220629-network` vs
`realchip-network`）、卷（compose 按目录名加前缀）。

宿主机 Nginx 新增一个 server 块（`www.company220629.com` → `127.0.0.1:8089`），与 realchip 的
server 块并存。

## 数据库配置

直接写入 `docker-compose.yml`：

| 变量 | 值 |
| --- | --- |
| DB_CONNECTION | mysql |
| DB_HOST | mysql |
| DB_PORT | 3306 |
| DB_DATABASE | company220629 |
| DB_USERNAME | root |
| DB_PASSWORD | root_company220629_202609 |
| DB_PREFIX | app_ |
| REDIS_HOST | redis |
| REDIS_PORT | 6379 |
| REDIS_PREFIX | company_ |
| CACHE_DRIVER | redis |
| SESSION_DRIVER | redis |
| QUEUE_CONNECTION | sync |
| APP_TIMEZONE | PRC |

`CACHE_DRIVER=redis` 是必须的：`SESSION_DRIVER=redis` 走 `CacheBasedSessionHandler`，
cache 驱动为 file 时 session 无法跨容器重启保持。

初始化 SQL `docker/mysql/01-company220629.sql`（13 张 `app_` 前缀表），
**仅在 `code/mysql` 为空时执行一次**。

## 验证码改造

`mews/captcha` 的校验依赖 `BcryptHasher` + Session，在 nginx + php-fpm 容器环境下
`password_verify` 不稳定。改造为纯 Redis token 方案，**前后台都改**。

| 文件 | 改动 |
| --- | --- |
| `app/Common/Captcha.php` | **新增**。`issue(int $ttl = 60): string` 签发 token、`verify($token, $input): bool` 校验并一次性失效、`render(string $code): string` 绘制 PNG |
| `app/Http/Controllers/Admin/CaptchaController.php` | **新增**。`index()` 输出图片、`refresh()` 返回新 token |
| `app/Http/Controllers/Index/CaptchaController.php` | **新增**。同上，供前台使用 |
| `routes/web.php` | 加 `/admin/captcha`、`/admin/captcha/refresh` |
| `routes/home.php` | 加 `/verifyCode`、`/verifyCode/refresh` |
| `Admin/LoginController` | `index()` 签发 token 传给视图；`checkLogin()` 校验，规则去掉 `\|captcha` |
| `Index/MessageController` | `message()` 签发 token；`sendMessage()` 校验，规则去掉 `\|captcha` |
| `resources/views/admin/login/index.blade.php` | hidden `captcha_token` + `refreshCaptcha()` |
| `resources/views/index/message/message.blade.php` | 同上 |

Redis key 格式 `captcha:{token}`，一次性校验后立即 `del`。

TTL 分两档：

| 场景 | TTL | 理由 |
| --- | --- | --- |
| 后台登录 | 60s | 与 realchip 一致，登录操作很快 |
| 前台留言 | 300s | 需填写称呼/电话/邮箱/内容，60s 容易过期 |

过期或校验失败时前端 JS 自动刷新验证码重新获取 token，表单已填内容不丢失。

**前台不能用 `/captcha`**：该路径已被 `mews/captcha` 包的 `CaptchaServiceProvider` 注册
（`captcha/{config?}`），故前台使用 `/verifyCode`。`mews/captcha` 包保留不卸载。

## 生产环境差异（docker-compose.prod.yml）

- `APP_ENV=production`、`APP_DEBUG=false`
- `APP_URL=https://www.company220629.com`
- `APP_KEY`、`DB_PASSWORD` 从服务器 `.env` 注入
- mysql、redis `ports: []`（完全不暴露）

## 部署流程

1. 服务器安装 Docker CE + docker-compose（standalone，CentOS 7 无 compose 插件）
2. 配置 SSH 密钥，GitHub 添加 Secrets：`SSH_HOST`、`SSH_USER`、`SSH_PRIVATE_KEY`
3. 服务器克隆仓库到 `/opt/company220629`
4. 服务器 `/opt/company220629/.env` 写入 `APP_KEY`、`DB_PASSWORD`
5. push main → GitHub Actions SSH → `git pull` → `docker-compose up -d --build` → 清理旧镜像
6. 宿主机 Nginx 新增 server 块反代到 `127.0.0.1:8089`，certbot 申请 SSL

`public/upload/` 不在 git 中，首次部署需从本地 rsync 到服务器，否则网站图片 404。

## 与 realchip 的四处刻意偏离

| # | realchip 做法 | 本项目做法 | 理由 |
| --- | --- | --- | --- |
| 1 | `.dockerignore` 不排除 `vendor` | 排除 `vendor` | 避免 COPY 携带 400MB 且随即被 `composer install` 覆盖 |
| 2 | base compose 的 mysql/redis 端口绑 `0.0.0.0` | 绑 `127.0.0.1` 且端口改为 13306/16379 | 同机双项目需错开端口，同时避免数据库暴露公网 |
| 3 | `generateCode()` 在 CaptchaController 和 LoginController 各复制一份 | 抽到 `app/Common/Captcha.php` 共用 | 前后台都要用，照抄会产生 3 份生成逻辑 + 2 份绘图逻辑 |
| 4 | 后台/前台验证码 TTL 都是 60s | 后台 60s、前台 300s | 前台留言要填称呼/电话/邮箱/内容，60s 不够 |

## 约束

- PHP 8.1（Laravel 8.65 兼容，composer 约束 `^7.3|^8.0`）
- 单容器方案（PHP-FPM + Nginx 同容器）
- MySQL 8.0，不是 PostgreSQL
- 表前缀 `app_`
- CentOS 7.6：cgroup v1、yum 包管理、firewalld
- 域名 `www.company220629.com`，服务器 `39.108.218.82`，部署路径 `/opt/company220629`
- 与 realchip 同机部署，端口不得冲突
