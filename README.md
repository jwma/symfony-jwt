Symfony JWT Demo
==========

使用 `lcobucci/jwt` 库和 `Symfony` 自身提供是事件系统实现了一套 `JWT` 用户身份验证的解决方案。

部署
---
1. 安装依赖
`composer install`

2. 配置参数
编辑 app/config/parameters.yml
```yaml
parameters:
    database_host: 127.0.0.1
    database_port: 3306
    database_name: your_db
    database_user: root
    database_password: root
    # ...
    secret: your_secret
    jwt_ttl: 3600
    jwt_login_uri: /admin/api/security/login
    jwt_logout_uri: /admin/api/security/logout
```

3. 创建数据库，如果在 parameters.yml 配置的用户有创建数据库的权限
`app/console doctrine:database:create`

4. 创建数据表
`app/console doctrine:schema:update --force`

创建后台用户
---
`app/console app:create-admin-user yourusername yourpassword`

运行服务器
---
`app/console server:run 127.0.0.1:8080`