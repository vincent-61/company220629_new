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
