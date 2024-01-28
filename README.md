修改自 

- [pereorga/minimalist-web-notepad](https://github.com/pereorga/minimalist-web-notepad)
- [fghwett/minimalist-web-notepad](https://github.com/fghwett/minimalist-web-notepad)

## 版本选择说明

> php-mysql 数据库版，php 实现
> 
> golang 本地版，go 实现，记得在运行文件 `pb` 所在的目录下创建 `_tmp` 目录

## 安装 php-mysql 版本

下载文件，相应的文件结构应如下

```
.
├── index.php
├── favicon.svg
├── script.js 
└── styles.css 
```

打开 index.php，填写 `$host $username $password $dbname`，分别对应 MySQL 数据库的 `地址 用户名 密码 数据库名称`

登陆 MySQL 数据库，输入以下 SQL 命令，检测数据表是否需要初始化操作

1. 判断数据库中是否存在 notes 的数据表

```sql
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'notes';
```
 
如果正常显示了 `notes`，则结束此阶段。如果没有，则进行第二步

2. 初始化数据表

```sql
CREATE TABLE notes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    note_name VARCHAR(64) NOT NULL,
    note_content MEDIUMTEXT,
    PRIMARY KEY (id),
    UNIQUE KEY (note_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

在没有错误其他的前提下，如果检测数据表正确，但无法正常写入内容，说明 notes 数据表可能不是由本项目所创建的，表结构有所不同，请考虑更改 index.php 中的数据表名称，并初始化（别忘了修改初始化命令中的 `notes` 为新名称）。或删除原有的 notes 表，重新初始化

如果使用 Apache 网页服务，下载 `.htaccess` 文件，放到网站目录

如果使用 Nginx 网页服务，在配置文件中添加下列内容

```
location / {
    rewrite ^/([a-zA-Z0-9_-]+)$ /index.php?note=$1;
}
```
