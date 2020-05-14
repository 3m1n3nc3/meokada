INSERT INTO `pxp_config` (`name`, `value`) VALUES 
('footer_credit', ''),
('social_container', ''),
('social_container_pages', '')

ALTER TABLE `pxp_users` 
CHANGE `ent_interest` `ent_interest` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
