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

CREATE TABLE `pxp_social_wallet` ( 
    `id` INT NOT NULL AUTO_INCREMENT, 
    `title` VARCHAR(128) NOT NULL, 
    `name` VARCHAR(128) NULL DEFAULT NULL,
    `percentage` INT(11) NOT NULL DEFAULT '0', 
    `d_percentage` INT(11) NOT NULL DEFAULT '0',
    `account_number` VARCHAR(128) NULL DEFAULT NULL, 
    `account_type` VARCHAR(128) NULL DEFAULT NULL,
    `bank_code` VARCHAR(128) NULL DEFAULT NULL,
    `recipient_code` VARCHAR(128) NULL DEFAULT NULL,
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


ALTER TABLE `pxp_challenges` 
    ADD `exclusive` INT(11) NOT NULL DEFAULT '0' AFTER `community`;
ALTER TABLE `pxp_info_modal` 
    ADD `use_title` VARCHAR(11) NOT NULL DEFAULT 'off' AFTER `priority`;
ALTER TABLE `pxp_info_modal` 
    CHANGE `content` `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `pxp_posts` 
    ADD `tv` INT(4) NOT NULL DEFAULT '0' AFTER `registered`;
ALTER TABLE `pxp_users` 
    ADD `nationality_id` INT(11) NOT NULL DEFAULT '0' AFTER `website`, 
    ADD `marital_status` VARCHAR(128) NULL DEFAULT NULL AFTER `nationality_id`, 
    ADD `join_reason` VARCHAR(128) NULL DEFAULT NULL AFTER `marital_status`, 
    ADD `profession` VARCHAR(128) NULL DEFAULT NULL AFTER `join_reason`, 
    ADD `acting` INT(4) NOT NULL DEFAULT '0' AFTER `profession`, 
    ADD `like_acting` INT(4) NOT NULL DEFAULT '0' AFTER `acting`, 
    ADD `artist` INT(4) NOT NULL DEFAULT '0' AFTER `like_acting`, 
    ADD `artist_contract` INT(4) NOT NULL DEFAULT '0' AFTER `artist`, 
    ADD `dancer` INT(4) NOT NULL DEFAULT '0' AFTER `artist_contract`, 
    ADD `be_dancer` INT(4) NOT NULL DEFAULT '0' AFTER `dancer`, 
    ADD `signed_model` INT(4) NOT NULL DEFAULT '0' AFTER `be_dancer`, 
    ADD `be_signed_model` INT(4) NOT NULL DEFAULT '0' AFTER `signed_model`, 
    ADD `sports` VARCHAR(128) NULL DEFAULT NULL AFTER `be_signed_model`, 
    ADD `pro_sports` INT(4) NOT NULL DEFAULT '0' AFTER `sports`, 
    ADD `managed_sports` INT(4) NOT NULL DEFAULT '0' AFTER `professional_sports`, 
    ADD `sports_contract` INT(4) NOT NULL DEFAULT '0' AFTER `managed_sports`, 
    ADD `ent_interest` VARCHAR(128) NOT NULL AFTER `sports_contract`, 
    ADD `big_brother_mate` INT(4) NOT NULL DEFAULT '0' AFTER `ent_interest`,
    ADD `dob` DATE NULL DEFAULT NULL AFTER `gender`;

INSERT INTO 
    `pxp_static_pages` (`id`, `page_name`, `content`) 
VALUES 
    (NULL, 'disclaimer', '<form id=\"contact_us_form\" class=\"form row pp_sett_form\"><div class=\"col-md-3\">&nbsp;</div><div class=\"col-md-6\"><div class=\"pp_mat_input\"><input class=\"form-control\" name=\"first_name\" required=\"true\" type=\"text\"> <label>First Name*</label></div><div class=\"pp_mat_input\"><input class=\"form-control\" name=\"last_name\" required=\"true\" type=\"text\"> <label>Last Name*</label></div><div class=\"pp_mat_input\" style=\"margin-bottom: 1.7em;\" data-mce-style=\"margin-bottom: 1.7em;\"><input class=\"form-control\" name=\"email\" required=\"true\" type=\"email\"> <label>Email*</label></div><div class=\"pp_mat_input\"><textarea class=\"form-control\" name=\"message\" rows=\"3\"></textarea> <label>Messages</label></div><div class=\"pp_terms\"><input id=\"terms\" name=\"terms\" type=\"checkbox\" onchange=\"activate_Button(this)\" > <label for=\"terms\"> I agree to the <a href=\"http://localhost/pixelphoto/terms-of-use\" data-ajax=\"ajax_loading.php?app=terms&apph=terms&page=terms-of-use\">Terms of use</a></label></div><div class=\"col-md-3\">&nbsp;</div><div class=\"pp_load_loader\"><button id=\"contact_submit\" class=\"btn btn-info pp_flat_btn\" disabled=\"disabled\" type=\"submit\">Send</button></div><div class=\"clear\">&nbsp;</div></div><div class=\"col-md-3\">&nbsp;</div></form>');
