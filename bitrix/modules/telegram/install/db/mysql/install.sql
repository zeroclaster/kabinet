CREATE TABLE IF NOT EXISTS `b_auth_tokens` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `user_id` INT NOT NULL COMMENT 'ID пользователя Bitrix',
     `token` VARCHAR(64) NOT NULL COMMENT 'Уникальный токен',
    `expire_time` INT NOT NULL COMMENT 'Время истечения (UNIX timestamp)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP, с которого разрешена авторизация',
    `is_used` TINYINT(1) DEFAULT 0 COMMENT 'Флаг использования (0/1)',

    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_token` (`token`),
    KEY `ix_user_id` (`user_id`),
    KEY `ix_expire_time` (`expire_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица для одноразовых токенов авторизации';


ALTER TABLE `b_auth_tokens`
    ADD INDEX `ix_composite` (`user_id`, `expire_time`, `is_used`);

