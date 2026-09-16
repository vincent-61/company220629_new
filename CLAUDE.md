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
├── code/mysql/            # MySQL 数据目录（宿主目录 bind mount，git 忽略）
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

- `Captcha::issue(int $ttl): string` —— 签发 token，Redis key `captcha:{token}`（Laravel 会前置 `REDIS_PREFIX=company_`，**线上真实键名是 `company_captcha:{token}`**，查 `captcha:*` 永远空集）
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
