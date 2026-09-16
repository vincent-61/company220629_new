# Docker 化任务清单

- [x] 1. 目录重构（应用迁入 code/project）
- [x] 2. 创建 Dockerfile
- [x] 3. 创建 nginx.conf
- [x] 4. 创建 php.ini
- [x] 5. 创建 docker-entrypoint.sh
- [x] 6. 创建 .dockerignore
- [x] 7. 创建 docker-compose.yml
- [x] 8. 创建 docker-compose.prod.yml
- [x] 9. 创建 GitHub Actions 部署文件
- [x] 10. 更新根 .gitignore
- [x] 11. 新增 app/Common/Captcha.php
- [x] 12. 后台登录验证码改造
- [x] 13. 前台留言验证码改造
- [x] 14. 创建服务器配置指南
- [x] 15. 创建 CLAUDE.md
- [x] 16. 创建 tasks/todo.md
- [x] 17. 本地构建验证

## 评审

### 完成内容

**新增/修改文件**

| 文件 | 说明 |
| --- | --- |
| `docker-compose.yml` | 三服务编排：project / mysql / redis，端口全部绑 `127.0.0.1` |
| `docker-compose.prod.yml` | 生产覆盖层，用 `ports: !reset []` 清掉 mysql/redis 端口 |
| `code/project/Dockerfile` | PHP 8.1-fpm + nginx 单容器，构建期 `storage:link` |
| `code/project/nginx.conf` | 站点配置，root 指向 `public` |
| `code/project/php.ini` | `date.timezone = Asia/Shanghai`、上传/内存等参数 |
| `code/project/docker-entrypoint.sh` | `php-fpm &` 后 `exec nginx -g 'daemon off;'`（**刻意不用 `-D`**：`-D` 会强制 daemonize，使 `docker.conf` 里的 `error_log`/`access_log` 指向 `/dev/null`，FPM 日志全部丢失） |
| `code/project/.dockerignore` | 排除 `vendor`、`.user.ini`（其 `open_basedir` 指向不存在的路径） |
| `.github/workflows/deploy.yml` | 推送 main 自动部署 |
| `.gitignore` | 忽略 `.env`、`code/mysql/*`（保留 `.gitkeep`） |
| `code/mysql/.gitkeep` | 占位，保持数据目录存在且不入库 |
| `docker/mysql/01-company220629.sql` | 首次初始化导入（143KB，13 张表） |
| `code/project/app/Common/Captcha.php` | 纯 Redis token 验证码，后台 60s / 前台 300s |
| `code/project/app/Http/Controllers/Admin/CaptchaController.php` | `/admin/captcha`、`/admin/captcha/refresh` |
| `code/project/app/Http/Controllers/Index/CaptchaController.php` | `/verifyCode`、`/verifyCode/refresh` |
| `code/project/app/Http/Controllers/Admin/LoginController.php` | 登录验证码改走 `Captcha::verify` |
| `code/project/app/Http/Controllers/Index/MessageController.php` | 留言验证码改走 `Captcha::verify` |
| `code/project/resources/views/admin/login/index.blade.php` | 验证码字段改为 `captcha` + `captcha_token` |
| `code/project/resources/views/index/message/message.blade.php` | 同上，图片源改为 `/verifyCode` |
| `docs/server-setup-guide.md` | CentOS 7.6 服务器部署指南 |
| `CLAUDE.md` | 项目指南 |
| `tasks/todo.md` | 本清单 |

**本地验证结果（Task 17，全部为实测输出）**

| 验证项 | 命令 | 实测结果 | 结论 |
| --- | --- | --- | --- |
| 构建启动 | `docker compose up -d --build` | 三容器 Created → Started，启动后约 12 秒内转为 `Up (healthy)` | 通过 |
| 初始化 SQL | `show tables` | 13 张 `app_` 前缀表 | 通过 |
| PHP 扩展 | `php -m` | 7 项匹配 + `Zend OPcache`（见下方说明） | 通过 |
| 时区 | `php -r 'echo date_default_timezone_get();'` | `Asia/Shanghai` | 通过 |
| storage 软链 | `ls -l public/storage` | `→ /var/www/html/storage/app/public` | 通过 |
| 前台 9 条路由 | `curl -o /dev/null -w '%{http_code}'` | 全部 200；静态图 200 | 通过 |
| 前台验证码 | `/verifyCode?token=` | `image/png`，`PNG 120 x 36`；Redis TTL 299 | 通过 |
| 前台留言入库 | `POST /sendMessage` | `{"code":0,"message":"提交成功"}`，`app_message.message_id=3`，content 十六进制与 UTF-8 逐字节一致 | 通过 |
| 验证码一次性 | `redis-cli EXISTS company_captcha:<token>` | `0` | 通过 |
| 后台验证码 | `/admin/captcha?token=` | `200 image/png`，Redis TTL 60 | 通过 |
| 后台错误验证码 | `POST /admin/login/checkLogin` | `{"code":1001,"message":"验证码不正确。"}` | 通过 |
| 后台正确验证码 | 同上，验证码取自 Redis | `{"code":1002,"message":"用户名或密码错误"}`，证明验证码闸门放行 | 通过 |
| 会话存储 | 解密 `_session` cookie 得 session id | 命中 `company__cache:<id>`，TTL 7167，值为含 `_previous.url` 的 session 序列化数据 | 通过 |
| 端口绑定 | `docker ps` | `127.0.0.1:8089->80`、`127.0.0.1:13306->3306`、`127.0.0.1:16379->6379`，无 `0.0.0.0` | 通过 |
| 生产覆盖层 | `docker compose -f ... -f docker-compose.prod.yml config` | 仅 project 保留 `published: "8089"`，mysql/redis 无 ports；`APP_ENV=production` | 通过 |

**偏差与说明**

1. `php -m` 中 opcache 的模块名是 `Zend OPcache`，所以 `grep -E "^(...|opcache)$"`
   永远匹配不到它，实测列出 7 项而非计划书写的 8 项。`php -i` 确认
   `opcache.enable => On`（CLI 下 `enable_cli => Off`，属正常默认），扩展本身已加载。
2. 验证码 Redis key 带 Laravel 前缀，实际为 `company_captcha:{token}` /
   `company__cache:{session_id}`（`REDIS_PREFIX=company_`）。直接查 `captcha:{token}`
   会得到空值 / `-2` / `0`，那是「键不存在」而非「已删除」——`ttl 299`、`ttl 60`
   都必须在带前缀的键上观测。
3. 计划书 Step 5 提交留言的命令只带 `content`/`captcha`/`captcha_token`，而
   `MessageLogic::sendMessage` 还要读 `name`/`phone`/`email`，缺键触发
   ErrorException 被 `catch` 成 `{"code":1003,"message":"提交失败"}`；补上三个字段后返回
   `code:0`。这是计划书命令的问题，不是应用缺陷。
4. 计划书 Step 5/Step 7 的 POST 未带 CSRF token，实测返回 419（`VerifyCsrfToken::$except`
   里的 `login/*` 匹配不到 `admin/login/checkLogin`）。改为从页面取明文 `_token`
   并携带同一 session cookie jar 后，两个接口均返回预期 JSON。
5. `app_message` 没有 `create_time` 列（plan Step 6 的命令会报
   `ERROR 1054 Unknown column`），实际列为 `created_at`（int 时间戳）。
6. 首次 MySQL 初始化实测十余秒即完成（`Ready for start up` 出现在 05:29:48），远快于计划书的 1-2 分钟，
   因为导入的是 143KB 纯 SQL，无大表填充。

### 约束核对

| 约束 | 落实方式 | 实测 |
| --- | --- | --- |
| 与 realchip 端口不冲突 | project 8089、mysql 13306、redis 16379，全部绑 127.0.0.1 | 三个端口均实测为 `127.0.0.1:*`，无公网绑定 |
| 容器名不冲突 | company220629-{project,mysql,redis} | 一致 |
| 网络不冲突 | company220629-network | 一致 |
| 单容器 | `php-fpm &` + `exec nginx` 前台，同容器 | 一致 |
| 表前缀 app_ | DB_PREFIX=app_ | 13 张表全为 `app_` 前缀 |
| 验证码可用 | 纯 Redis token，后台 60s / 前台 300s | TTL 实测 60 / 299；`/verifyCode` 与 `captcha/{config?}` 并存不冲突 |
