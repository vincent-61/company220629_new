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

宿主机 Nginx 新增一个 server 块（`www.yunqingelec.com` → `127.0.0.1:8089`），与 realchip 的
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
- `APP_URL=https://www.yunqingelec.com`
- `APP_KEY`、`DB_PASSWORD` 从服务器 `.env` 注入
- mysql、redis 用 `ports: !reset []` 移除端口（完全不暴露）
  > 原写的是 `ports: []`，**那是错的**：compose 对 `ports` 按列表合并，空列表不移除任何东西，
  > 端口会原样保留。只有 `!reset`（Compose ≥ 2.24.4）才会真正清除。已在 v2.24.6 实测确认。

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
- 域名 `www.yunqingelec.com`，服务器 `39.108.218.82`，部署路径 `/opt/company220629`
- 与 realchip 同机部署，端口不得冲突

## 实施期修订

本节记录实现过程中推翻或补充了正文的裁定。正文保留原样以便追溯；两者冲突时，以本节为准。
（本节的每一条都对应实现期间的一次实测或一次有据的裁定，不是事后追认。）

| # | 正文原文 | 实际实现 | 原因 |
| --- | --- | --- | --- |
| 1 | §生产环境差异：「`ports: []`（完全不暴露）」 | `ports: !reset []` | compose 对 `ports` 是按**列表合并**的，空列表不移除任何端口，端口会原样保留；只有 `!reset`（Compose ≥ 2.24.4）才真正清除。已在真实 v2.24.6 二进制上实测确认。正文该处已就地更正。 |
| 2 | §验证码改造：「Redis key 格式 `captcha:{token}`」 | 线上键为 `company_captcha:{token}` | Laravel 会把 `REDIS_PREFIX=company_` 前置到该连接的**所有**键（`config/database.php` 的 redis 连接 → `RedisManager` → `PhpRedisConnector` 的 `OPT_PREFIX`）。不带前缀的键根本不存在，`EXISTS` 返回 0 会被误读成「已被一次性消费」。 |
| 3 | §验证码改造：`Captcha.php` 只规定 `issue/verify/render` 三个方法 | 另加：token 用 `bin2hex(random_bytes(16))`、验证码字符用 `random_int()`、`verify()` 先 `del` 再判空、绘图的 `ob_get_clean()` 置于 `finally` | 生成源由非 CSPRNG（`md5(uniqid(mt_rand()))`）换成 CSPRNG；「先删后判」才是真正的一次性语义（先判后删会让空输入路径留下活键）；`finally` 保证异常路径不泄漏输出缓冲。 |
| 4 | 正文未提及限流 | `/admin/captcha/refresh` 与 `/verifyCode/refresh` 加 `throttle:30,1`；`TrustProxies::$proxies = '*'` | token 签发端点无限流时，单个 IP 即可无限量往 Redis 写键；前台 TTL 是 300s，窗口内积累的活跃键数量只受请求速率限制，没有上限。两项必须同批落地：不信任代理时 `$request->ip()` 对所有访客都返回同一个 docker 网关地址，per-route 限流会退化成**全局单桶**，比不限流更糟。两个验证码**索引**路由刻意不限流（渲染图片本就需要刷新）。**（最终评审补正）**原只对两个 `refresh` 端点限流，但签发 token 的还有两条页面渲染路由，论据所指的洞并未关死；已给 `/message.html` 与 `/admin/login/index` 补 `throttle:60,1`。 |
| 5 | §生产环境差异未列日志项 | 加 `LOG_LEVEL=error`、`LOG_CHANNEL=daily` | 生产沿用 `stack`（单文件）会让 `laravel.log` 无界增长。 |
| 6 | §部署流程未提部署前校验 | workflow 在 `git pull` 前校验 `.env` 中 `APP_KEY`、`DB_PASSWORD` 非空，缺失即中止部署 | 缺 `DB_PASSWORD` 时 compose 打印一行警告并把 `MYSQL_ROOT_PASSWORD` 代入空串；`mysql:8.0` 入口脚本对「已设置但为空」与「未设置」同样按未指定处理，直接报 `Database is uninitialized and password option is not specified` 退出 1，mysql 反复重启、project 因 `service_healthy` 永远起不来——是**整站起不来**，不是静默的无口令库（实测，见 `docs/server-setup-guide.md`）。原写「会静默起一个无口令的数据库」是错的。 |
| 7 | §mysql 容器「健康检查: `mysqladmin ping`」 | `mysqladmin ping -h 127.0.0.1 -u root -p"$MYSQL_ROOT_PASSWORD"` | 镜像内的 `mysqladmin` 默认走 unix socket，探针无法反映 TCP 服务是否可用；无凭据的 TCP 探针在部分配置下会被服务器拒绝。 |
| 8 | §目录结构只列 `.dockerignore` 需排除 `vendor` | 额外排除 `.user.ini` | 仓库里的 `.user.ini` 把 `open_basedir` 指向容器内并不存在的路径，打进镜像会限制 PHP 的文件访问范围。 |
| 9 | 正文未提及验证码刷新的失败路径 | 两个 blade 的 `refreshCaptcha()` 补 `error` 回调 | 第 4 条的限流是本项目新增的，触发 429 时原 `$.get` 无失败回调，刷新会静默失效——即「点验证码没反应」。这条回归由限流引入，必须与它同批修复。 |
| 10 | 正文未规定宿主 Nginx 的转发头 | 指南第六节改为 `proxy_set_header X-Forwarded-For $remote_addr;`（覆写） | 容器内 `TrustProxies::$proxies = '*'` 信任全部代理，Laravel 取转发链最左地址；用 `$proxy_add_x_forwarded_for` 追加时客户端自带的值会被采信，`$request->ip()` 由访客决定，第 4 条的按 IP 限流随之失效。单层代理下覆写才正确；将来加 CDN 需改用 `set_real_ip_from`。 |
| 11 | §部署流程：「`public/upload/` 不在 git 中，首次部署需从本地 rsync 到服务器」 | 本地源路径为 `code/project/public/upload/` | Task 1 把应用整体 `git mv` 进 `code/project/`，仓库根目录下没有 `public/`（`ls public` → No such file or directory）。原样执行会直接报路径不存在，或在某个残留目录上同步成功却同步了空内容，站点图片全 404。**本 spec 第 167 行与 plan 第 1681/1685 行仍保留迁移前的不带 `code/project` 的旧写法**，属历史文本，不改写；执行时以本节与本条为准。 |

### 遗留观察（非本计划引入，未修改）

- `VerifyCsrfToken::$except = ['login/*']` 匹配不到 `admin/login/checkLogin`：Laravel 的
  `inExceptArray()` 按整条路径匹配，路由挪到 `admin/` 前缀下之后这条豁免就已失效。
  后果是 CSRF 反而**更严**（该接口现在真的校验 token），不是漏洞。两个表单的 blade 都显式携带 CSRF token（`{{ csrf_token() }}`：后台是 `data` 里拼 `&_token=`，前台是拼在 URL 查询串上），浏览器路径正常。留着不动，仅记录，避免下次有人以为它还在生效。
- compose 里的 `APP_TIMEZONE=PRC` **不生效**：`code/project/config/app.php:70` 把 `'timezone'` 硬编码为 `'PRC'`，全仓库没有任何 PHP 代码读取 `APP_TIMEZONE`（`git grep APP_TIMEZONE` 只命中 `.env.example:33` 与 `docker-compose.yml:16`）。改这个环境变量不会改变时区。`php.ini` 的 `date.timezone` 只在 Laravel 引导之前有效——Laravel 启动时用 `config('app.timezone')`（`config/app.php:70` 硬编码 `'PRC'`）覆盖它；两者同为 UTC+08:00 故行为等价，但**要改时区只能改 `config/app.php:70`**。保留该变量只是为了与 `.env.example` 保持一致。

### 未随修订更新的历史文本（spec 正文与 plan 正文）

本节声明一条**总规则**，随后给出若干高危实例的索引。

**总规则：本 spec 与 plan 正文中所有围栏代码块，以及正文里的具体命令与参数，都是修订前的
版本，一律不得照抄。** 实现以仓库中的实际文件为准：`docker-compose.yml`、
`docker-compose.prod.yml`、`code/project/` 下的 `Dockerfile` / `nginx.conf` / `php.ini` /
`docker-entrypoint.sh` / `php-fpm.d/www.conf`、`app/Common/Captcha.php`、两个验证码 blade、
`routes/{web,home}.php`、`docs/server-setup-guide.md`、`CLAUDE.md`。

为什么按类别声明而不是逐行穷尽：本清单的前两版都是逐行列举，两轮之后仍各有遗漏——plan 的
代码块与它的正文是两份彼此独立的错误副本，逐行列举追不上。**下面的行号是高危实例索引，
不是穷尽保证**；与本节、修订表或 plan 头部指针冲突时，一律以修订侧与仓库实际文件为准。

- **宿主 Nginx 转发头**（照抄会直接重新打开客户端可控 `$request->ip()`，使第 4 条的按 IP
  限流失效，即修订表第 10 条要修的那个洞）：`plan:1704` —— 该行是
  `proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;`，实际实现为
  `docs/server-setup-guide.md` 里的 `$remote_addr` 覆写。
- **验证码生成与一次性语义**（Task 11 的整段代码块 `plan:784-963` 全部未随修订更新）：
  `plan:830`（`md5(uniqid((string) mt_rand(), true))` 非 CSPRNG，实际为
  `bin2hex(random_bytes(16))`）、`plan:847-849`（先判空再删，空输入会留下活键，实际为先删后判）、
  `plan:891-899` / `plan:907-908` / `plan:937`（`rand()`，实际为 `random_int()`）、
  `plan:916-919`（`ob_start()`/`imagepng()`/`ob_get_clean()` 无 `try/finally`）——
  以上均为修订表第 3 条覆盖，以 `code/project/app/Common/Captcha.php` 为准。
- **生产覆盖层代码块内容不全**：`plan:639-653` 除了那两处 `ports: []`，还缺
  `MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}` 与 `LOG_LEVEL=error` / `LOG_CHANNEL=daily`。
  照抄会得到一个 root 口令仍是基础文件字面量、而应用按 `.env` 认证的 mysql——
  永久 `1045` 且 `docker-compose ps` 显示 healthy，正是 `docker-compose.prod.yml` 注释警告的
  那个故障。以 `docker-compose.prod.yml` 为准。
- **Redis 键名**：`plan:2049`、`plan:2051`、`plan:2072`、`plan:2083` 用 `captcha:$TOKEN`
  不带 `company_` 前缀；`plan:2072` 的「预期 0（一次性已删）」在不存在的键上**恒真**，
  正是修订表第 2 条描述的误判。以修订表第 2 条与 `docs/server-setup-guide.md` 为准。
- **mysql 健康检查**：`spec:66`、`plan:577`（`mysqladmin ping`，且缺 `retries: 10` /
  `start_period: 60s`）—— 以修订表第 7 条与 `docker-compose.yml` 为准。
- **PHP 错误日志路径**：`plan:308`（`error_log = /var/log/php_errors.log`，照抄会让
  `docker logs` 里看不到任何 PHP 错误，与入口脚本 `-D` 那条同类）—— 以
  `code/project/php.ini` 的 `/proc/self/fd/2` 为准。
- **Task 17 验证命令**：`plan:2018`、`plan:2023`（`opcache` 的 grep 与「预期 8 个扩展
  全部列出」永远不可能通过，`php -m` 输出的是 `Zend OPcache`）、`plan:2070-2071`
  （`create_time` 列不存在，dump 里是 `created_at`）—— 二者均已记在 `tasks/todo.md` 的偏差表。
- **入口脚本 `-D`**：`spec:55`、`plan:435`（可直接复制的 `php-fpm -D` 代码块，照抄会复现 FPM
  日志丢失）、`plan:1971` —— 一律以 `tasks/todo.md:34` 与 `code/project/docker-entrypoint.sh` 为准。
- **Redis 键前缀**：`plan:31`（Global Constraints）、`plan:1750`、`plan:1865` —— 以修订表第 2 条为准
  （`spec:135` 已由第 2 条覆盖，一并注明）。
- **「volume 挂载」措辞**：`spec:31`、`plan:1832`（MySQL 数据目录那处，与已更正的 `CLAUDE.md:30`
  同源）、`spec:56`、`plan:495` —— 实为宿主目录 bind mount（`docker-compose.yml:34/35/54` 三处都是
  `./宿主目录:容器路径`），以 `docker-compose.yml` 与修订表第 11 条为准。
- **MySQL 诊断命令的口令写法**：`plan:1749`（`mysql -uroot -p` 交互式提示，非交互环境直接失败）
  —— 以 `docs/server-setup-guide.md:297` 的 `sh -c '… -p"$MYSQL_ROOT_PASSWORD" …'` 为准。
- **验证码刷新的失败路径（429）**：`plan:1211`、`plan:1438`（两个可直接复制的 `refreshCaptcha()`
  代码块，无 `.fail`，照抄会复现「点验证码没反应」）—— 以修订表第 9 条与两个 blade 为准。
- **`!reset` / 生产覆盖层清端口**：`plan:636-637`（注释里的错误理由）、`plan:649`、`plan:652`
  （`docker-compose.prod.yml` 代码块里的 `ports: []`，照抄**不会**移除端口，会复现第 1 条要修的
  那个暴露问题）—— 以修订表第 1 条与 `docker-compose.prod.yml` 为准。
- **上传路径**：`spec:167`、`plan:1681`、`plan:1685`、`plan:1911` —— 已由第 11 条覆盖，此处只做索引。
