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
> 报错退出。这一步是必要的——`DB_PASSWORD` 为空时 compose 会代入空串，MySQL 会以
> **无口令的 root** 初始化，而应用照样连得上，属于静默故障。

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
