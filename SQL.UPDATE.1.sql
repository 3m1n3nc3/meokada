CREATE TABLE `pxp_exclusive_plans` (
    `id` INT(11) NOT NULL AUTO_INCREMENT, 
    `title` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `description` TEXT NULL DEFAULT NULL, 
    `pro_level` INT(11) NOT NULL DEFAULT '0',  
    `icon` TEXT NULL DEFAULT NULL, 
    `status` INT(4) NOT NULL DEFAULT '1', 
    PRIMARY KEY (`id`), 
    INDEX `title` (`title`)
) ENGINE = InnoDB;

ALTER TABLE `pxp_challenges` ADD `exclusive` INT(11) NOT NULL DEFAULT '0' AFTER `community`;

CREATE TABLE `pxp_social_wallet` ( 
    `id` INT NOT NULL AUTO_INCREMENT, 
    `title` VARCHAR(128) NOT NULL, 
    `name` VARCHAR(128) NULL DEFAULT NULL,
    `percentage` INT(11) NOT NULL DEFAULT '0', 
    `d_percentage` INT(11) NOT NULL DEFAULT '0',
    `account_number` INT(11) NULL DEFAULT NULL, 
    `account_type` VARCHAR(128) NULL DEFAULT NULL,
    `bank_code` VARCHAR(128) NULL DEFAULT NULL,
    `paidout` INT(11) NULL DEFAULT NULL,
    `balance` INT(11) NULL DEFAULT NULL, 
    `description` TEXT NULL DEFAULT NULL,
    `public` INT(11) NOT NULL DEFAULT '0' AFTER `description`,
    PRIMARY KEY (`id`), 
    INDEX `title` (`title`)
) ENGINE = InnoDB;

INSERT INTO 
    `pxp_config` (`id`, `name`, `value`) 
VALUES 
    ('social_wallet', 'on'), 
    ('exclusive_system', 'on'), 
    ('auto_pay_wallet', 'off'), 
    ('auto_pay_wallet_limit', '5000'); 
