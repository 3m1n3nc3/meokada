CREATE TABLE IF NOT EXISTS `pxp_exclusive_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `pro_level` int NOT NULL DEFAULT '0',
  `icon` text COLLATE utf8mb4_general_ci,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `pxp_social_wallet` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `percentage` int NOT NULL DEFAULT '0',
  `d_percentage` int NOT NULL DEFAULT '0',
  `account_number` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_type` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_code` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recipient_code` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paidout` int DEFAULT '0',
  `balance` int DEFAULT '0',
  `description` text COLLATE utf8mb4_general_ci,
  `public` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pxp_config` (`name`, `value`) VALUES 
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
    ADD `managed_sports` INT(4) NOT NULL DEFAULT '0' AFTER `pro_sports`, 
    ADD `sports_contract` INT(4) NOT NULL DEFAULT '0' AFTER `managed_sports`, 
    ADD `ent_interest` VARCHAR(128) NOT NULL AFTER `sports_contract`, 
    ADD `big_brother_mate` INT(4) NOT NULL DEFAULT '0' AFTER `ent_interest`,
    ADD `dob` DATE NULL DEFAULT NULL AFTER `gender`;

INSERT INTO `pxp_static_pages` (`id`, `page_name`, `content`) VALUES 
(NULL, 'disclaimer', '<form id=\"contact_us_form\" class=\"form row pp_sett_form\"><div class=\"col-md-3\">&nbsp;</div><div class=\"col-md-6\"><div class=\"pp_mat_input\"><input class=\"form-control\" name=\"first_name\" required=\"true\" type=\"text\"> <label>First Name*</label></div><div class=\"pp_mat_input\"><input class=\"form-control\" name=\"last_name\" required=\"true\" type=\"text\"> <label>Last Name*</label></div><div class=\"pp_mat_input\" style=\"margin-bottom: 1.7em;\" data-mce-style=\"margin-bottom: 1.7em;\"><input class=\"form-control\" name=\"email\" required=\"true\" type=\"email\"> <label>Email*</label></div><div class=\"pp_mat_input\"><textarea class=\"form-control\" name=\"message\" rows=\"3\"></textarea> <label>Messages</label></div><div class=\"pp_terms\"><input id=\"terms\" name=\"terms\" type=\"checkbox\" onchange=\"activate_Button(this)\" > <label for=\"terms\"> I agree to the <a href=\"http://localhost/pixelphoto/terms-of-use\" data-ajax=\"ajax_loading.php?app=terms&apph=terms&page=terms-of-use\">Terms of use</a></label></div><div class=\"col-md-3\">&nbsp;</div><div class=\"pp_load_loader\"><button id=\"contact_submit\" class=\"btn btn-info pp_flat_btn\" disabled=\"disabled\" type=\"submit\">Send</button></div><div class=\"clear\">&nbsp;</div></div><div class=\"col-md-3\">&nbsp;</div></form>');

INSERT INTO `pxp_info_modal` (`title`, `content`, `status`, `in_pages`, `priority`, `use_title`) VALUES
('Community Terms', '&lt;div id=&quot;r1-2&quot; class=&quot;result results_links_deep highlight_d result--url-above-snippet&quot; data-domain=&quot;stackoverflow.com&quot; data-hostname=&quot;stackoverflow.com&quot; data-nir=&quot;1&quot;&gt;&lt;div class=&quot;result__body links_main links_deep&quot;&gt;&lt;div class=&quot;result__snippet js-result-snippet&quot;&gt;I think it&#039;s first worth noting that without javascript (plain&amp;nbsp;&lt;strong&gt;html&lt;/strong&gt;), the&amp;nbsp;&lt;strong&gt;form&lt;/strong&gt;&amp;nbsp;element submits when clicking either the &amp;lt;input type=&quot;submit&quot; value=&quot;submit&amp;nbsp;&lt;strong&gt;form&lt;/strong&gt;&quot;&amp;gt; or &amp;lt;button&amp;gt;submits&amp;nbsp;&lt;strong&gt;form&lt;/strong&gt;&amp;nbsp;too&amp;lt;/button&amp;gt;. In javascript you can&amp;nbsp;&lt;strong&gt;prevent&lt;/strong&gt;&amp;nbsp;that by using an event handler and calling e.preventDefault() on button click, or&amp;nbsp;&lt;strong&gt;form&lt;/strong&gt;&amp;nbsp;submit.&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div id=&quot;r1-3&quot; class=&quot;result results_links_deep highlight_d result--url-above-snippet&quot; data-domain=&quot;www.w3schools.com&quot; data-hostname=&quot;www.w3schools.com&quot; data-nir=&quot;1&quot;&gt;&lt;div class=&quot;result__body links_main links_deep&quot;&gt;&lt;br data-mce-bogus=&quot;1&quot;&gt;&lt;/div&gt;&lt;/div&gt;', 'on', 'community_terms', 100, 'on');

INSERT INTO `pxp_exclusive_plans` (`title`, `description`, `pro_level`, `icon`, `status`) VALUES
('{{LANG community_challenge}}', 'Most of these icons are modified version of the awesome Feather icons with several new additions. They are licensed under MIT License - free for personal &amp; commercial use. Most of these icons are modified version of the awesome Feather icons with several new additions. They are licensed under MIT License - free for personal &amp; commercial use. Most of these icons are modified version of the awesome Feather icons with several new additions. They are licensed under MIT License - free for personal &amp; commercial use. Most of these icons are modified version of the awesome Feather icons with several new additions. They are licensed under MIT License - free for personal &amp; commercial use.', 3, '&lt;svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;#0bb865&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-community&quot;&gt;&lt;path d=&quot;M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2&quot;&gt;&lt;/path&gt;&lt;circle cx=&quot;9&quot; cy=&quot;7&quot; r=&quot;4&quot;&gt;&lt;/circle&gt;&lt;path d=&quot;M23 21v-2a4 4 0 0 0-3-3.87&quot;&gt;&lt;/path&gt;&lt;path d=&quot;M16 3.13a4 4 0 0 1 0 7.75&quot;&gt;&lt;/path&gt;&lt;/svg&gt;', 1),
('{{LANG big_brother_challenge}}', 'Social DisancingSocial Disancing Social Disancing Social Disancing Social Disancing Social Disancing', 2, '&lt;svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;#0bb865&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-eye&quot;&gt;&lt;path d=&quot;M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z&quot;&gt;&lt;/path&gt;&lt;circle cx=&quot;12&quot; cy=&quot;12&quot; r=&quot;3&quot;&gt;&lt;/circle&gt;&lt;/svg&gt;', 1),
('{{LANG philanthropist_challenge}}', '', 1, '&lt;svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;#0bb865&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-wallet&quot;&gt;&lt;path d=&quot;M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3&quot;&gt;&lt;/path&gt;&lt;/svg&gt;', 1),
('{{LANG learning_challenge}}', '', 0, '&lt;svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;#0bb865&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-wallet&quot;&gt;&lt;path d=&quot;M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z&quot;/&gt;&lt;path d=&quot;M14 3v5h5M16 13H8M16 17H8M10 9H8&quot;/&gt;&lt;/svg&gt;', 1),
('{{LANG fun_challenge}}', '', 0, '&lt;svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;#0bb865&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-wallet&quot;&gt;&lt;path d=&quot;M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z&quot;&gt;&lt;/path&gt;&lt;line x1=&quot;7&quot; y1=&quot;7&quot; x2=&quot;7.01&quot; y2=&quot;7&quot;&gt;&lt;/line&gt;&lt;/svg&gt;', 1);

INSERT INTO `pxp_langs` (`ref`, `lang_key`, `english`) VALUES
('', 'community', 'Community'),
('', 'navigation', 'Navigation'),
('', 'enter', 'Enter'),
('', 'social_wallet', 'Social Wallet'),
('', 'community_challenge', 'Community Reward'),
('', 'basic_challenge', 'Basic Challenge'),
('', 'paid_challenge', 'Paid Challenge'),
('', 'big_brother_challenge', 'Big Brother Online'),
('', 'philanthropist_challenge', 'Philanthropist (Give Away)'),
('', 'learning_challenge', 'Learning Challenge'),
('', 'fun_challenge', 'Fun Challenge'),
('', 'challenge', 'Challenge'),
('', 'view', 'View'),
('', 'thanks_for_donating', 'Your donations will go a long way to help our charity causes and our affiliated charity organization achieve the aim of bringing relief materials and support to people suffering from the effects of the COVID 19 Outbreak.');
