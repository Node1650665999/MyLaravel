CREATE TABLE `pre_admin`
(
    `id`           int(10) unsigned    NOT NULL AUTO_INCREMENT COMMENT '管理员id',
    `token`        varchar(255)        NOT NULL DEFAULT '' COMMENT 'token',
    `token_expire` int(11) unsigned    NOT NULL DEFAULT '0' COMMENT 'token过期时间',
    `username`     char(50)            NOT NULL DEFAULT '' COMMENT '管理员账号',
    `passwd`       varchar(255)        NOT NULL DEFAULT '' COMMENT '管理员密码',
    `is_super`     tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为超级管理员：1是/0否',
    `appid`        int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '所属应用',
    `created_at`   datetime            NOT NULL COMMENT '创建时间',
    `updated_at`   datetime            NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `uniq_account` (`username`) USING HASH
) ENGINE = InnoDB
  AUTO_INCREMENT = 14
  DEFAULT CHARSET = utf8mb4 COMMENT ='管理员表';


CREATE TABLE `pms_admin_has_permissions`
(
    `admin_id`      bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
    `permission_id` int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '权限id'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='用户-权限中间表';

CREATE TABLE `pms_admin_has_roles`
(
    `admin_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
    `role_id`  int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '角色ID'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='用户-角色中间表';


CREATE TABLE `pms_permissions`
(
    `id`         int(10) unsigned                        NOT NULL AUTO_INCREMENT,
    `appid`      int(10) unsigned                        NOT NULL DEFAULT '0' COMMENT '应用程序id',
    `name`       varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单/按钮名称',
    `route`      varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由标识',
    `permission` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限标识',
    `icon`       varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标class',
    `parent_id`  int(11)                                 NOT NULL DEFAULT '0' COMMENT '父级菜单id',
    `sort`       int(11)                                 NOT NULL DEFAULT '0' COMMENT '排序',
    `type`       int(11)                                 NOT NULL DEFAULT '1' COMMENT '类型：1菜单/2按钮',
    `created_at` timestamp                               NULL     DEFAULT NULL,
    `updated_at` timestamp                               NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `index_permission` (`permission`) USING HASH
) ENGINE = InnoDB
  AUTO_INCREMENT = 100
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='权限表';


CREATE TABLE `pms_roles`
(
    `id`         int(10) unsigned                        NOT NULL AUTO_INCREMENT COMMENT '角色id',
    `name`       varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色名称',
    `created_at` timestamp                               NULL     DEFAULT NULL,
    `updated_at` timestamp                               NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 8
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='角色表';

CREATE TABLE `pms_role_has_permissions`
(
    `role_id`       int(10) unsigned NOT NULL DEFAULT '0',
    `permission_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='角色-权限中间表';

CREATE TABLE `pms_app`
(
    `id`         int(10) unsigned                        NOT NULL AUTO_INCREMENT COMMENT '应用程序id',
    `name`       varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用名称',
    `created_at` timestamp                               NULL     DEFAULT NULL,
    `updated_at` timestamp                               NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='角色表';

CREATE TABLE `pms_login_log`
(
    `id`         bigint(20) unsigned                     NOT NULL AUTO_INCREMENT,
    `admin_id`   int(11) unsigned                        NOT NULL DEFAULT '0' COMMENT '用户ID',
    `admin_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `ip`         varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作ip',
    `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'user_agent',
    `created_at` timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_at` timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 5
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='操作日志表';

CREATE TABLE `pms_operate_log`
(
    `id`         bigint(20) unsigned                     NOT NULL AUTO_INCREMENT,
    `admin_id`   int(11) unsigned                        NOT NULL DEFAULT '0' COMMENT '用户ID',
    `admin_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `uri`        varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口',
    `params`     varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '参数',
    `method`     varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方式：GET、POST、PUT、DELETE、HEAD',
    `ip`         varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作ip',
    `created_at` timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_at` timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 10
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='操作日志表';
