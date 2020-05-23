INSERT INTO `pxp_config` (`name`, `value`) VALUES 
('footer_credit', ''),
('social_container', ''),
('social_container_pages', '')

ALTER TABLE `pxp_users` 
CHANGE `ent_interest` `ent_interest` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

-------------------------------------------------------
INSERT INTO `pxp_config` (`name`, `value`) VALUES 
('background_extension', 'png'),
('show_welcome_background', 'on'),
('show_welcome_banner', 'on'),
('banner_extension', 'png'),
('banner_title', ''),
('send_tv_notifs', 'on'),
('api_refresh_interval', '1'),
('hide_affiliate_info', 'on'),
('restrict_tv_creation', 'on'),
('tv_in_tv', 'on');

INSERT INTO `pxp_langs` (`ref`, `lang_key`, `english`) VALUES
('', 'receive_site_notif', 'Site Notifications'),
('', 'new_tv_posts', 'New TV Posts');

ALTER TABLE `pxp_users` 
    ADD `n_on_tv` INT(11) NOT NULL DEFAULT '0' AFTER `n_on_mention`;
