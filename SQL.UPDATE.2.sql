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

----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pxp_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `plan_name` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pxp_config` (`name`, `value`) VALUES 
('use_coupon', 'on'),
('coupon_system', 'on'),
('coupon_prefix', 'on');

INSERT INTO `pxp_langs` (`ref`, `lang_key`, `english`) VALUES
('', 'use_coupon', 'Use Coupon'),
('', 'coupon', 'Coupon'),
('', 'coupons', 'Coupons');
