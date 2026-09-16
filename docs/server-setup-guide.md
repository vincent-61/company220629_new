# 服务器部署指南

服务器：`39.108.218.82`（CentOS 7.6）
域名：`www.company220629.com`
部署路径：`/opt/company220629`

> 本服务器上同时运行 realchip 项目（占用 8088/3306/6379）。本项目使用
> 8089/13306/16379，互不冲突。执行本文档命令时注意不要动 `/opt/realchip`。
>
> **顺带排查（不属于本项目改动范围）：** realchip 的 `docker-compose.prod.yml`
> 用 `ports: []` 来"关闭"数据库端口，但 compose 对 `ports` 是按列表合并的，
> **空列表不会移除任何东西**——只有 `!reset []` 才会。因此该项目的 mysql/redis
> 很可能仍以 `0.0.0.0:3306` / `0.0.0.0:6379` 对外发布（本项目已改用 `!reset []`）。
> 请用下面的命令确认，若确实暴露则用 firewalld 挡住或改用 `!reset []`：
>
> ```bash
> ss -lntp | grep -E ':(3306|6379)\b'
> firewall-cmd --list-ports
> ```

> **CentOS 7 已停止维护（EOL 2024-06-30）。** `mirror.centos.org` 已下线，
> 默认 yum 源和 EPEL 7 都会报 404 / Cannot find a valid baseurl。安装任何包之前，
> 先把源指向归档地址（`vault.centos.org`），否则下面所有 `yum install` 都会失败：
>
> ```bash
> sed -i 's|^mirrorlist=|#mirrorlist=|g' /etc/yum.repos.d/CentOS-*.repo
> sed -i 's|^#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*.repo
> yum clean all && yum makecache
> ```
>
> 需要 EPEL 的话同样改用归档：`https://archives.fedoraproject.org/pub/archive/epel/7/x86_64/`。

> **本机 Docker 与 Compose 版本要求。** `docker-compose.prod.yml` 使用了
> `!reset` 标签，该标签需要 Compose **≥ 2.24.4**。服务器上的 standalone
> `docker-compose` 必须是 v2 且不低于此版本（本文档第三节安装的是 v2.24.6）。

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

> **顺序：先把上面三个 secret 配好，再往 `main` 推代码。**
> 反过来的话，推送触发的第一次部署会在 SSH 连接阶段失败——secret 里没有凭据可读。
> 这**不会影响服务器**（那时服务器通常还没建）。若已经推了，配完 secret 后到仓库的
> **Actions** 页选中那次失败的运行，点 **Re-run jobs** 即可，不需要再提交一次。
>
> 另：部署脚本只在 **push 到 `main`** 时触发（另有 `workflow_dispatch` 可手动触发）。
> `dockerize` 这类开发分支没有上游，推不推都不触发部署。

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
git clone https://github.com/vincent-61/company220629_new.git company220629
cd /opt/company220629
```

> 用 **HTTPS** 地址：仓库是公开的，匿名克隆即可，服务器上不需要配任何 GitHub 凭据。
> 不要换成 SSH 地址——§一 生成的那对密钥是给 GitHub Actions **登录服务器**用的，
> 服务器本身没有对应的 GitHub 私钥，换成 SSH 地址后克隆会失败，
> 而后面的 `git pull origin main` 每次部署都会失败。

创建生产环境变量文件（compose 的 `${APP_KEY}` / `${DB_PASSWORD}` 从这里读）：

```bash
# 只属于这台服务器的两个值，现场生成。不要使用仓库里出现的任何字面量。
echo "APP_KEY=base64:$(openssl rand -base64 32)"          # → 粘到下面的 APP_KEY=
echo "DB_PASSWORD=$(openssl rand -base64 24 | tr -d '/+=' | cut -c1-32)"  # → 粘到下面的 DB_PASSWORD=

cat > /opt/company220629/.env <<'EOF'
APP_KEY=<上面第一行的输出>
DB_PASSWORD=<上面第二行的输出>
EOF
chmod 600 /opt/company220629/.env
```

> **为什么必须现场生成：本仓库是公开的。** `docker-compose.yml` 里提交的
> `APP_KEY` 与 `DB_PASSWORD` 是**本地开发用的默认值**（`docker-compose.yml:13,22,51`；
> APP_KEY 在 `main` 的 `.env.example` 里已经公开）。生产覆盖层把这两个值改成从本文件读
> （`docker-compose.prod.yml:16,17,28`），所以生产用哪一套完全取决于你在这里写什么。
> 照抄仓库里的字面量，等于把生产的 root 口令和 cookie 加密密钥一起公开。
> 本地开发继续用仓库里那套即可——它只绑 `127.0.0.1`，且与生产无关。

> **`DB_PASSWORD` 一旦首次初始化就固定了。** MySQL 官方入口脚本只在数据目录为空时
> 执行 `ALTER USER 'root'@'localhost' IDENTIFIED BY ...`；本项目的 `code/mysql/` 是
> 宿主机 bind mount，所以首次 `up` 之后改 `.env` 里的 `DB_PASSWORD` **不会**改变
> 数据库里已存在的 root 口令，重启也无效，表现为应用每个请求都报
> `SQLSTATE[HY000] [1045]`，而 `docker-compose ps` 里 mysql 仍然是 healthy。
> 要改口令只能二选一：
>
> ```bash
> # 方案 A：容器内改（保留数据）
> docker-compose exec mysql mysql -uroot -p'旧口令' -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '新口令';"
> # 方案 B：清空数据目录重来（会丢数据，首次部署尚未导入时可以这么做）
> docker-compose down && rm -rf code/mysql/* && docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
> ```
>
> 部署脚本已经加了前置校验：`.env` 里 `APP_KEY` 或 `DB_PASSWORD` 缺失或为空时直接
> 报错退出。这一步是必要的——`DB_PASSWORD` 为空时 compose 会代入**空串**，而
> `mysql:8.0` 的入口脚本把「`MYSQL_ROOT_PASSWORD` 已设置但为空」与「未设置」同样处理
> （`[ -z "$MYSQL_ROOT_PASSWORD" ]` 对空串成立），于是打印
> `Database is uninitialized and password option is not specified` 并**退出 1**。
> 结果是 mysql 容器反复重启，`depends_on: condition: service_healthy` 让 project 容器
> 永远起不来，表现为**整站起不来**，不是「静默起了个无口令的库」。
> （已在 `mysql:8.0` 上实测：`docker run --rm -e MYSQL_ROOT_PASSWORD= mysql:8.0`
> 直接报上述错误退出，不会进入 `ready for connections`。）

```bash
# 与部署脚本同款前置校验：首次 up 会把 root 口令永久写进数据目录，不能带着空的或格式错的
# APP_KEY / DB_PASSWORD 起。
# 这里刻意不用 `exit 1`——这段是直接粘进你的 SSH 会话里执行的，`exit` 会把你自己
# 的登录 shell 一起关掉。改为把结论打印出来，由你决定是否继续。
ok=1
grep -qE '^APP_KEY=base64:[A-Za-z0-9+/]{43}=$' /opt/company220629/.env \
  || { echo "ERROR: APP_KEY 缺失或格式非法"; ok=0; }
grep -qE '^DB_PASSWORD=.+' /opt/company220629/.env \
  || { echo "ERROR: DB_PASSWORD 缺失或为空"; ok=0; }
[ "$ok" = 1 ] && echo "校验通过，可以执行下面的 up" || echo ">>> 校验未通过，不要执行下面的 up <<<"
```

首次启动（第一次会自动导入 `docker/mysql/01-company220629.sql`，耗时 1-2 分钟）：

```bash
cd /opt/company220629
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
docker-compose ps
curl -I http://127.0.0.1:8089
```

> 首次启动时 project 容器要等 mysql 通过健康检查（`start_period` 最长 60s）才会启动，
> 紧接着还要等 FPM 就绪。刚 `up -d` 完立刻 curl 可能拿到 502——先用
> `docker-compose ps` 确认 project 是 `Up` 再 curl，不要把这个 502 当成配置错误。

预期：三个容器都是 `Up`，curl 返回 `HTTP/1.1 200 OK`。

上传站点图片（`code/project/public/upload` 不在 git 中，必须从本地同步，约 100MB）：

```bash
# 在本地执行（注意：应用根目录是 code/project/，不是仓库根目录）
rsync -avz --progress code/project/public/upload/ root@39.108.218.82:/opt/company220629/code/project/public/upload/
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
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        client_max_body_size 20M;
    }
}
EOF

nginx -t && systemctl reload nginx
```

> `X-Forwarded-For` 用 `$remote_addr` 覆写而非 `$proxy_add_x_forwarded_for` 追加，是刻意的：
> 容器里的 `TrustProxies` 信任全部代理（`$proxies = '*'`），而 Laravel 取的是转发链**最左**的
> 那个地址。用追加写法时，客户端自己带的 `X-Forwarded-For` 会排在前面并被采信，等于访客可以
> 自己决定 `$request->ip()`，验证码刷新接口的按 IP 限流（`throttle:30,1`）随之失效。
> 本项目是「客户端 → 宿主 nginx → 容器」的单层代理，覆写才是对的。
> 将来若在前面加 CDN，这一行必须改回并配合 `set_real_ip_from` / `real_ip_header`，否则拿到的
> 会是 CDN 节点地址。

> CentOS 上 yum 装的 Nginx，`conf.d/*.conf` 默认已包含在 `http` 块内；若没有，
> 把这行加进 `/etc/nginx/nginx.conf` 的 `http {}`：`include /etc/nginx/conf.d/*.conf;`

---

## 七、申请 SSL 证书

> **先完成文首的 CentOS 7 换源，再执行本节。** certbot 走 EPEL，而 EPEL 7 已随
> CentOS 7 EOL 归档，未换源时 `yum install` 会直接 404（Cannot find a valid baseurl）。
> EPEL 的归档地址与文首相同：`https://archives.fedoraproject.org/pub/archive/epel/7/x86_64/`
> —— 先 `yum install -y epel-release`，再把 `/etc/yum.repos.d/epel*.repo` 里的
> `mirrorlist` 注释掉、`baseurl` 指向该归档地址，最后 `yum clean all && yum makecache`。

```bash
yum install -y certbot python2-certbot-nginx
certbot --nginx -d www.company220629.com -d company220629.com
```

certbot 会自动改写上面的 server 块并加上 443。验证自动续期：

```bash
certbot renew --dry-run
```

---

## 八、部署：首次推送与日常更新

### 首次：合并并推送

服务器按 §一–§七 建好、且 §一 的三个 secret 已配好后，在**本地仓库**执行：

```bash
git checkout main
git merge --ff-only dockerize
git push origin main
```

`--ff-only` 是刻意的：如果这个命令报错，说明 `main` 上有 `dockerize` 没有的提交，
要先看清楚再合，**不要改用 `--no-ff` 蒙混过去**。

推送即触发一次部署。若这时服务器还没就绪，那次失败是预期内的，不用管那个红叉——
建好后按下面的「手动触发」重跑一次即可。

### 日常更新

推送到 `main` 分支即自动部署（GitHub Actions）。

**手动触发 Actions**：仓库页 → **Actions** → 左侧 **Deploy to VPS** → 右上 **Run workflow**。

**在服务器上手动部署**（绕过 Actions）：

```bash
cd /opt/company220629
git pull origin main
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
docker image prune -f
```

查看日志：

```bash
docker-compose logs -f project
docker-compose exec mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" company220629 -e "show tables;"'
docker-compose exec redis redis-cli KEYS 'company_captcha:*'
```

> mysql 那条写成 `sh -c` 是刻意的：`mysql -uroot -p` 会交互式提示输入口令，在非交互
> 环境（CI、`ssh host '命令'`）下会直接失败，手工输入时还会把口令留在 shell history
> 里。容器内本来就有 `MYSQL_ROOT_PASSWORD`，交给容器里的 shell 展开即可，口令值不会
> 进入你手敲的命令和 shell history。（它仍会出现在容器内 `mysql` 进程的 argv 里，这正是 MySQL
> 不建议用 `-p` 传口令的原因；这里的收益是操作侧不留痕，不是进程级隐藏。）
>
> redis 的 `company_` 前缀来自 compose 里的 `REDIS_PREFIX=company_`：Laravel 会把它拼到
> `App\Common\Captcha` 写入的 `captcha:{token}` 前面，所以 Redis 中真实的 key 是
> `company_captcha:{token}`。该前缀用于与其他项目共用同一 Redis 时隔离命名空间，
> **不要**改回不带前缀的 `captcha:*`——那样查出来永远是空集。

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
