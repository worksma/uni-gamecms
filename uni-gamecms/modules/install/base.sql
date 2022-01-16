SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `pass` varchar(32) DEFAULT NULL,
  `pass_md5` varchar(250) DEFAULT '',
  `type` varchar(2) NOT NULL,
  `server` int NOT NULL,
  `user_id` int DEFAULT '0',
  `active` int NOT NULL DEFAULT '1',
  `link` varchar(255) DEFAULT '',
  `cause` varchar(255) DEFAULT '',
  `price` float NOT NULL DEFAULT '0',
  `pause` int DEFAULT '0',
  `comment` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `admins__services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `service` int NOT NULL,
  `service_time` int NOT NULL,
  `bought_date` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ending_date` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `irretrievable` float NOT NULL DEFAULT '0',
  `rights_und` varchar(25) NOT NULL DEFAULT 'none',
  `immunity_und` int NOT NULL DEFAULT '0',
  `sb_group_und` varchar(120) NOT NULL DEFAULT 'none',
  `previous_group` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `server` int NOT NULL,
  `nick` varchar(250) NOT NULL,
  `reason` varchar(250) NOT NULL,
  `img` varchar(255) NOT NULL,
  `demo` varchar(250) NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `author` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `closed` int NOT NULL DEFAULT '0',
  `bid` int NOT NULL DEFAULT '0',
  `have_answer` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bans__comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ban_id` int NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message_text` text NOT NULL,
  `message_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `comms` (
  `bid` int NOT NULL AUTO_INCREMENT,
  `authid` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `created` int NOT NULL,
  `expired` int NOT NULL,
  `length` int NOT NULL,
  `reason` varchar(64) NOT NULL,
  `admin_id` int NOT NULL,
  `admin_nick` varchar(32) NOT NULL,
  `server_id` int NOT NULL,
  `modified_by` varchar(32) NOT NULL,
  `type` int NOT NULL,
  PRIMARY KEY (`bid`),
  KEY `sid` (`server_id`),
  KEY `type` (`type`),
  KEY `authid` (`authid`),
  KEY `created` (`created`),
  KEY `aid` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `author_id` int NOT NULL,
  `accused_admin_server_id` int NOT NULL,
  `accused_admin_id` int NOT NULL,
  `accused_admin_nick` varchar(256) NOT NULL,
  `screens` varchar(256) NOT NULL,
  `demo` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `judge_id` int NOT NULL DEFAULT '0',
  `have_answer` int NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `accused_profile_id` int NOT NULL,
  `sentence` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `complaints__comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `template` varchar(20) NOT NULL,
  `template_mobile` varchar(20) NOT NULL DEFAULT 'default',
  `violations_number` int NOT NULL,
  `violations_delta` varchar(5) NOT NULL,
  `ban_time` varchar(5) NOT NULL,
  `protect` int NOT NULL,
  `hide_players_id` int NOT NULL DEFAULT '0',
  `top_donators` int NOT NULL DEFAULT '1',
  `top_donators_count` int NOT NULL DEFAULT '5',
  `top_donators_show_sum` int NOT NULL DEFAULT '2',
  `vk_api_version` float NOT NULL DEFAULT '5.131',
  `update_server` int NOT NULL DEFAULT '1',
  `stat` int NOT NULL,
  `stat_number` varchar(5) NOT NULL,
  `show_news` int NOT NULL DEFAULT '3',
  `show_events` int DEFAULT '3',
  `bank` float NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `cont` int NOT NULL DEFAULT '2',
  `col_nick` int NOT NULL DEFAULT '1',
  `col_pass` int NOT NULL DEFAULT '1',
  `col_type` int NOT NULL DEFAULT '1',
  `conf_us` int NOT NULL DEFAULT '1',
  `cote` int NOT NULL DEFAULT '1',
  `widgets_type` int NOT NULL DEFAULT '1',
  `vk_group` int NOT NULL DEFAULT '2',
  `vk_group_id` varchar(80) NOT NULL DEFAULT '97860459',
  `vk_admin` int NOT NULL DEFAULT '2',
  `vk_admin_id` varchar(80) NOT NULL DEFAULT '139146346',
  `disp_last_online` int NOT NULL DEFAULT '1',
  `new_year` int NOT NULL DEFAULT '0',
  `win_day` int NOT NULL DEFAULT '0',
  `copyright_key` varchar(40) NOT NULL DEFAULT '',
  `developer_mode` int NOT NULL DEFAULT '2',
  `off` int NOT NULL DEFAULT '2',
  `dell_admin_time` varchar(20) NOT NULL DEFAULT '2016-10-09 01:00:00',
  `global_ban` int NOT NULL DEFAULT '2',
  `time_zone` varchar(25) NOT NULL DEFAULT 'Etc/GMT-3',
  `protocol` int NOT NULL DEFAULT '1',
  `code` varchar(20) NOT NULL DEFAULT '',
  `cache` int NOT NULL DEFAULT '1',
  `salt` varchar(10) NOT NULL DEFAULT '',
  `secret` varchar(256) NOT NULL DEFAULT 'none',
  `ip_protect` int NOT NULL DEFAULT '2',
  `privacy_policy` int NOT NULL DEFAULT '2',
  `captcha` varchar(50) NOT NULL DEFAULT '2',
  `captcha_client_key` varchar(256) NOT NULL DEFAULT 'none',
  `captcha_secret` varchar(256) NOT NULL DEFAULT 'none',
  `token` int NOT NULL DEFAULT '1',
  `caching` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config` (`id`, `name`, `template`, `template_mobile`, `violations_number`, `violations_delta`, `ban_time`, `protect`, `hide_players_id`, `top_donators`, `top_donators_count`, `top_donators_show_sum`, `vk_api_version`, `update_server`, `stat`, `stat_number`, `show_news`, `show_events`, `bank`, `date`, `cont`, `col_nick`, `col_pass`, `col_type`, `conf_us`, `cote`, `widgets_type`, `vk_group`, `vk_group_id`, `vk_admin`, `vk_admin_id`, `disp_last_online`, `new_year`, `win_day`, `copyright_key`, `developer_mode`, `off`, `dell_admin_time`, `global_ban`, `time_zone`, `protocol`, `code`, `cache`, `salt`, `secret`, `ip_protect`, `privacy_policy`, `captcha`, `token`, `caching`) VALUES
(1, '<<project>>', 'standart', 'standart', 30, '2', '15', 2, 0, 1, 5, 2, 5.131, 1, 2, '3000', 0, 3, 0, '2021-10-13', 2, 1, 1, 1, 2, 2, 2, 2, '', 2, '', 1, 2, 2, 'none', 2, 2, '2021-10-14 02:19:00', 2, 'Etc/GMT-3', 1, '<<code>>', 38, '<<salt>>', 'none', 1, 2, '2', 1, 2);

CREATE TABLE IF NOT EXISTS `config__bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rb` int NOT NULL DEFAULT '2',
  `rb_login` varchar(255) NOT NULL,
  `rb_pass1` varchar(255) NOT NULL,
  `rb_pass2` varchar(255) NOT NULL,
  `rb_commission` int NOT NULL DEFAULT '1',
  `wb` int NOT NULL DEFAULT '2',
  `wb_login` varchar(255) NOT NULL DEFAULT '',
  `wb_pass1` varchar(255) NOT NULL DEFAULT '',
  `wb_num` varchar(255) NOT NULL DEFAULT '',
  `up` int NOT NULL DEFAULT '2',
  `up_type` int NOT NULL DEFAULT '1',
  `up_pass1` varchar(255) NOT NULL DEFAULT '',
  `up_pass2` varchar(255) NOT NULL DEFAULT '',
  `ps` int NOT NULL DEFAULT '2',
  `ps_num` varchar(255) NOT NULL DEFAULT '',
  `ps_pass` varchar(255) NOT NULL DEFAULT '',
  `ps_currency` varchar(3) NOT NULL DEFAULT 'RUB',
  `ps_test` int NOT NULL DEFAULT '0',
  `fk` int NOT NULL DEFAULT '2',
  `fk_login` varchar(255) NOT NULL,
  `fk_pass1` varchar(255) NOT NULL,
  `fk_pass2` varchar(255) NOT NULL,
  `ik` int NOT NULL DEFAULT '2',
  `ik_login` varchar(255) NOT NULL,
  `ik_pass1` varchar(255) NOT NULL,
  `wo` int NOT NULL DEFAULT '2',
  `wo_login` varchar(255) NOT NULL,
  `wo_pass` varchar(255) NOT NULL,
  `ya` int NOT NULL DEFAULT '2',
  `ya_num` varchar(255) DEFAULT '',
  `ya_key` varchar(255) DEFAULT '',
  `qw` int NOT NULL DEFAULT '2',
  `qw_pass` varchar(300) NOT NULL DEFAULT '',
  `enot` int NOT NULL DEFAULT '2',
  `enot_id` varchar(255) NOT NULL,
  `enot_key` varchar(255) NOT NULL,
  `enot_key2` varchar(255) NOT NULL,
  `lp` int NOT NULL DEFAULT '2',
  `lp_public_key` varchar(255) NOT NULL DEFAULT '',
  `lp_private_key` varchar(255) NOT NULL DEFAULT '',
  `ap` int NOT NULL DEFAULT '2',
  `ap_project_id` varchar(255) NOT NULL DEFAULT '',
  `ap_private_key` varchar(255) NOT NULL DEFAULT '',
  `fk_new` int NOT NULL DEFAULT '2',
  `fk_new_login` varchar(255) NOT NULL,
  `fk_new_pass1` varchar(255) NOT NULL,
  `fk_new_pass2` varchar(255) NOT NULL,
  `amarapay` int NOT NULL DEFAULT '2',
  `amarapay_id` varchar(9) NOT NULL DEFAULT '',
  `amarapay_public` varchar(128) NOT NULL DEFAULT '',
  `amarapay_secret` varchar(128) NOT NULL DEFAULT '',
  `freekassa` int NOT NULL DEFAULT '2',
  `freekassa_id` varchar(15) NOT NULL DEFAULT '',
  `freekassa_secret1` varchar(128) NOT NULL DEFAULT '',
  `freekassa_secret2` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config__bank` (`id`, `rb`, `rb_login`, `rb_pass1`, `rb_pass2`, `rb_commission`, `wb`, `wb_login`, `wb_pass1`, `wb_num`, `up`, `up_type`, `up_pass1`, `up_pass2`, `ps`, `ps_num`, `ps_pass`, `ps_currency`, `ps_test`, `fk`, `fk_login`, `fk_pass1`, `fk_pass2`, `ik`, `ik_login`, `ik_pass1`, `wo`, `wo_login`, `wo_pass`, `ya`, `ya_num`, `ya_key`, `qw`, `qw_pass`, `enot`, `enot_id`, `enot_key`, `enot_key2`, `lp`, `lp_public_key`, `lp_private_key`, `ap`, `ap_project_id`, `ap_private_key`, `fk_new`, `fk_new_login`, `fk_new_pass1`, `fk_new_pass2`, `amarapay`, `amarapay_id`, `amarapay_public`, `amarapay_secret`, `freekassa`, `freekassa_id`, `freekassa_secret1`, `freekassa_secret2`) VALUES
(1, 2, '', '', '', 1, 2, '', '', '', 2, 1, '', '', 2, '', '', 'EUR', 0, 2, '', '', '', 2, '', '', 2, '', '', 2, '', '', 2, '', 2, '', '', '', 2, '', '', 2, '', '', 2, '', '', '', 2, '', '', '', 2, '', '', '');

CREATE TABLE IF NOT EXISTS `config__email` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `port` int NOT NULL DEFAULT '25',
  `host` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `charset` varchar(20) NOT NULL DEFAULT 'UTF-8',
  `from_email` varchar(255) NOT NULL DEFAULT '',
  `use_email` int NOT NULL DEFAULT '2',
  `id` int NOT NULL AUTO_INCREMENT,
  `verify_peers` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config__email` (`username`, `port`, `host`, `password`, `charset`, `from_email`, `use_email`, `id`, `verify_peers`) VALUES
('', 465, '', '', 'utf-8', '', 2, 1, 1);

CREATE TABLE IF NOT EXISTS `config__prices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `price1` float NOT NULL DEFAULT '10',
  `price2` float NOT NULL DEFAULT '50',
  `price3` float NOT NULL DEFAULT '100',
  `price2_1` float NOT NULL DEFAULT '10',
  `price2_2` float NOT NULL DEFAULT '50',
  `price2_3` float NOT NULL DEFAULT '100',
  `price4` float NOT NULL DEFAULT '100',
  `discount` int NOT NULL DEFAULT '0',
  `referral_program` int NOT NULL DEFAULT '2',
  `referral_percent` int NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config__prices` (`id`, `price1`, `price2`, `price3`, `price2_1`, `price2_2`, `price2_3`, `price4`, `discount`, `referral_program`, `referral_percent`) VALUES
(1, 10, 50, 100, 10, 50, 100, 100, 0, 1, 5);

CREATE TABLE IF NOT EXISTS `config__secondary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vk_api` int NOT NULL DEFAULT '2',
  `vk_id` varchar(50) DEFAULT '',
  `vk_key` varchar(50) DEFAULT '',
  `vk_service_key` varchar(100) DEFAULT '',
  `steam_api` int NOT NULL DEFAULT '2',
  `steam_key` varchar(50) DEFAULT '',
  `fb_api` int NOT NULL DEFAULT '2',
  `fb_id` varchar(20) DEFAULT NULL,
  `fb_key` varchar(50) DEFAULT NULL,
  `mon_gap` int NOT NULL,
  `mon_time` int NOT NULL,
  `mon_api` int NOT NULL DEFAULT '0',
  `mon_key` varchar(15) NOT NULL DEFAULT '',
  `bans_lim` int NOT NULL DEFAULT '30',
  `muts_lim` int NOT NULL DEFAULT '30',
  `users_lim` int NOT NULL DEFAULT '12',
  `bans_lim2` int NOT NULL DEFAULT '30',
  `news_lim` int NOT NULL DEFAULT '10',
  `stats_lim` int NOT NULL DEFAULT '30',
  `complaints_lim` int NOT NULL DEFAULT '30',
  `stand_rights` int NOT NULL DEFAULT '1',
  `stand_balance` float NOT NULL DEFAULT '0',
  `version` varchar(10) NOT NULL DEFAULT '5.4',
  `col_login` int NOT NULL DEFAULT '30',
  `admins_ids` varchar(80) NOT NULL DEFAULT '1',
  `off_message` varchar(250) NOT NULL DEFAULT 'Сайт находится в стадии разработки',
  `update_link` text NOT NULL,
  `return_services` int NOT NULL DEFAULT '1',
  `bad_nicks_act` int NOT NULL DEFAULT '2',
  `min_amount` float DEFAULT '10',
  `bonuses` int NOT NULL DEFAULT '2',
  `auto_steam_id_fill` int NOT NULL DEFAULT '2',
  `steam_id_format` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config__secondary` (`id`, `vk_api`, `vk_id`, `vk_key`, `vk_service_key`, `steam_api`, `steam_key`, `fb_api`, `fb_id`, `fb_key`, `mon_gap`, `mon_time`, `mon_api`, `mon_key`, `bans_lim`, `muts_lim`, `users_lim`, `bans_lim2`, `news_lim`, `stats_lim`, `complaints_lim`, `stand_rights`, `stand_balance`, `version`, `col_login`, `admins_ids`, `off_message`, `update_link`, `return_services`, `bad_nicks_act`, `min_amount`, `bonuses`, `auto_steam_id_fill`, `steam_id_format`) VALUES
(1, 2, NULL, NULL, NULL, 2, NULL, 2, NULL, NULL, 120, 1634158027, 2, '', 30, 30, 12, 30, 10, 30, 30, 2, 0, '5.4', 30, '1', 'Ведутся технические работы', '', 2, 2, 10, 2, 2, 1);

CREATE TABLE IF NOT EXISTS `config__prefixes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bind_nick_pass` int NOT NULL DEFAULT '1',
  `bind_steam` int NOT NULL DEFAULT '1',
  `bind_steam_pass` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config__prefixes`(`bind_nick_pass`, `bind_steam`, `bind_steam_pass`) VALUES ('1', '1', '1');

CREATE TABLE IF NOT EXISTS `config__strings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL,
  `comment` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `config__strings` (`id`, `data`, `comment`) VALUES
(1, '', ''),
(2, 'a:10:{s:12:\"file_manager\";s:1:\"1\";s:18:\"file_manager_theme\";s:1:\"1\";s:10:\"img_editor\";s:1:\"1\";s:16:\"img_editor_theme\";s:1:\"1\";s:13:\"file_max_size\";s:2:\"10\";s:7:\"ext_img\";s:20:\"jpg jpeg png gif bmp\";s:9:\"ext_music\";s:7:\"mp3 wav\";s:8:\"ext_misc\";s:18:\"zip rar 7z tar iso\";s:8:\"ext_file\";s:184:\"dem doc docx rtf pdf xls xlsx txt csv xhtml psd log fla xml ade adp mdb accdb ppt pptx odt ots ott odb odg otp otg odf ods odp css ai kmz dwg dxf hpgl plt spl step stp iges igs sat cgm\";s:9:\"ext_video\";s:25:\"mpeg m4v mp4 avi flv webm\";}', ''),
(3, 'a:0:{}', '');

CREATE TABLE IF NOT EXISTS `config__updates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `url` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config__updates` (`id`, `name`, `url`) VALUES
(1, 'OVH SAS', 'api.worksma.ru');

CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` int DEFAULT NULL,
  `date` int DEFAULT NULL,
  `content` text NOT NULL,
  `link` varchar(300) DEFAULT '',
  `data_id` int DEFAULT NULL,
  `sec_data_id` int DEFAULT '0',
  `author` int DEFAULT NULL,
  `access` varchar(50) NOT NULL DEFAULT ';',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forums` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `trim` int NOT NULL,
  `last_msg` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forums__messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `author` int NOT NULL,
  `topic` int NOT NULL,
  `edited_by` int NOT NULL DEFAULT '0',
  `edited_time` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forums__section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `trim` int NOT NULL,
  `access` varchar(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forums__topics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `forum_id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `last_msg` int DEFAULT NULL,
  `views` int NOT NULL,
  `author` int NOT NULL,
  `status` int NOT NULL,
  `answers` int NOT NULL,
  `old_date` datetime NOT NULL DEFAULT '2015-09-01 15:00:00',
  `img` varchar(255) NOT NULL DEFAULT 'files/forums_imgs/none.jpg',
  `edited_by` int NOT NULL DEFAULT '0',
  `edited_time` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `last_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action_type` int NOT NULL,
  `date` varchar(20) NOT NULL,
  `count` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `last_online` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `levels__profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` int NOT NULL,
  `name` varchar(512) NOT NULL,
  `experience` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

INSERT INTO `levels__profile` (`id`, `level`, `name`, `experience`) VALUES
(1, 0, 'Серебро - I', 0),
(2, 1, 'Серебро - II', 200),
(3, 2, 'Серебро - III', 500),
(4, 3, 'Серебро - IV', 700),
(5, 4, 'Серебро - Элита', 1000),
(6, 5, 'Серебро - Великий Магистр', 1500),
(7, 6, 'Золотая Звезда - I', 1800),
(8, 7, 'Золотая Звезда - II', 2500),
(9, 8, 'Золотая Звезда - III', 3200),
(10, 9, 'Золотая Звезда - Магистр', 3900),
(11, 10, 'Магистр-Хранитель - I', 5000),
(12, 11, 'Магистр-Хранитель - II', 8000),
(13, 12, 'Магистр-Хранитель - Элита', 10000),
(14, 13, 'Заслуженный Магистр-Хранитель', 15000),
(15, 14, 'Легендарный Беркут', 20000),
(16, 15, 'Легендарный Беркут-Магистр', 30000),
(17, 16, 'Великий Магистр Высшего Ранга', 50000),
(18, 17, 'Всемирная Элита', 100000);

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `menu__sub` int NOT NULL DEFAULT '0',
  `poz` int NOT NULL,
  `for_all` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

INSERT INTO `menu` (`id`, `name`, `link`, `menu__sub`, `poz`, `for_all`) VALUES
(1, 'Главная', '../', 0, 1, 1),
(2, 'Бан лист', '../banlist', 0, 5, 1),
(3, 'Администраторы', '../admins', 0, 7, 1),
(4, 'Магазин', 'none', 2, 3, 2),
(5, 'Профиль', 'none', 5, 2, 2),
(6, 'Поддержка', '../support/', 0, 14, 2),
(7, 'Новости', '../news/', 0, 9, 1),
(8, 'Разбан', '../bans/', 0, 4, 2),
(9, 'Форум', '../forum/', 0, 10, 1),
(10, 'Пользователи', '../users', 0, 11, 2),
(11, 'Статистика', '../stats', 0, 8, 1),
(12, 'Мутлист', '../muts', 0, 6, 1),
(13, 'События проекта', '../events', 0, 13, 1),
(14, 'Жалобы', '../complaints/', 0, 15, 1);

CREATE TABLE IF NOT EXISTS `menu__sub` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `menu` int NOT NULL,
  `poz` int NOT NULL,
  `for_all` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

INSERT INTO `menu__sub` (`id`, `name`, `link`, `menu`, `poz`, `for_all`) VALUES
(1, 'Уведомления', '../notifications', 5, 6, 1),
(2, 'Настройки', '../settings', 5, 5, 1),
(3, 'Кошелек', '../purse', 5, 4, 1),
(4, 'Друзья', '../friends', 5, 3, 1),
(5, 'Сообщения', '../messages', 5, 2, 1),
(6, 'Профиль', '../profile', 5, 1, 1),
(7, 'Выход', '../exit', 5, 9, 1),
(8, 'Услуги', '../my_stores', 5, 8, 1),
(9, 'Магазин привилегий', '../store', 4, 1, 1),
(10, 'Магазин префиксов', '../store/prefixes', 4, 2, 1),
(11, 'Торговая площадка', '/market', 4, 3, 1),
(12, 'Инвентарь', '/inventory', 5, 7, 1);

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `tpls` varchar(500) NOT NULL,
  `active` int NOT NULL DEFAULT '1',
  `info` text NOT NULL,
  `files` text,
  `client_key` varchar(30) NOT NULL DEFAULT 'YDHGABATAMKESPCQMP9S',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `money__actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `shilings` float NOT NULL,
  `author` int NOT NULL,
  `type` int NOT NULL,
  `gave_out` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `money__actions_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `class` varchar(20) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

INSERT INTO `money__actions_types` (`id`, `name`, `class`) VALUES
(1, 'Пополнение счета', 'success'),
(2, 'Покупка прав', 'danger'),
(3, 'Выдано администратором <a href=\"../profile?id={id}\" target=\"_blank\">{login}</a>', 'warning'),
(4, 'Покупка разбана', 'default'),
(5, 'Покупка стикеров', 'danger'),
(6, 'Продление прав', 'danger'),
(7, 'Разблокировка прав', 'danger'),
(8, 'Активация ваучера', 'success'),
(9, 'Покупка размута', 'default'),
(10, 'Возврат средств', 'success'),
(11, 'Реферальное пополнение от <a href=\"../profile?id={id}\" target=\"_blank\">{login}</a>', 'success'),
(12, 'Бонус', 'success'),
(22, 'Покупка в магазине', 'danger');

CREATE TABLE IF NOT EXISTS `monitoring` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(30) NOT NULL,
  `port` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `game` varchar(35) NOT NULL,
  `players_now` int NOT NULL,
  `players_max` int NOT NULL,
  `map` varchar(30) NOT NULL,
  `type` int NOT NULL,
  `sid` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class` int NOT NULL,
  `new_name` varchar(250) NOT NULL,
  `img` varchar(255) NOT NULL,
  `short_text` varchar(500) NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `author` int NOT NULL,
  `views` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news__classes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `news__classes` (`id`, `name`) VALUES
(1, 'Новости проекта');

CREATE TABLE IF NOT EXISTS `news__comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `new_id` int NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `date` datetime NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `type` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file` varchar(100) NOT NULL,
  `url` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(80) NOT NULL,
  `description` varchar(150) NOT NULL DEFAULT 'none',
  `keywords` varchar(150) NOT NULL DEFAULT 'none',
  `kind` int NOT NULL DEFAULT '1',
  `image` varchar(255) NOT NULL DEFAULT 'files/miniatures/standard.jpg',
  `robots` int NOT NULL DEFAULT '1',
  `privacy` int NOT NULL DEFAULT '1',
  `type` int NOT NULL DEFAULT '1',
  `active` int NOT NULL DEFAULT '1',
  `module` int NOT NULL DEFAULT '0',
  `page` int NOT NULL DEFAULT '0',
  `class` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8;

INSERT INTO `pages` (`id`, `file`, `url`, `name`, `title`, `description`, `keywords`, `kind`, `image`, `robots`, `privacy`, `type`, `active`, `module`, `page`, `class`) VALUES
(1, 'modules/index/index.php', '', 'main', 'Главная страница', 'Игровой проект посвященный играм CS1.6, CSS, CG:GO', 'игровой, проект, игра, CS1.6, CSS, CG:GO', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(2, 'modules/admins/index.php', 'admins', 'admins', 'Администраторы', 'none', 'none', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(3, 'modules/index/recovery.php', 'recovery', 'recovery', 'Восстановление пароля', 'Страница восстановления утеряного пароля', 'Восстановить, забыл, пароль', 1, 'files/miniatures/standart.jpg', 2, 1, 1, 1, 0, 0, 0),
(4, 'modules/settings/index.php', 'settings', 'settings', 'Настройки', 'Настройки личного профиля', 'Настройки, профиль', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(5, 'modules/exit/index.php', 'exit', 'exit', 'Выход', 'Выход', 'Выход', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(6, 'modules/events/index.php', 'events', 'events', 'События портала', 'События портала', 'События, портал', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(7, 'modules/friends/index.php', 'friends', 'friends', 'Друзья', 'Друзья', 'Друзья', 1, 'files/miniatures/standart.jpg', 2, 1, 1, 1, 0, 0, 0),
(8, 'modules/users/index.php', 'users', 'users', 'Пользователи', 'Пользователи проекта', 'Пользователи', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(9, 'modules/banlist/index.php', 'banlist', 'banlist', 'Банлист', 'Список заблокированных игроков на серверах проекта', 'Банлист, заблокированные, список ', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(10, 'modules/muts/index.php', 'muts', 'muts', 'Мутлист', 'Список игроков, для которых ограничены средства связи на серверах проекта', 'Мутлист, заблокированные, список', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(11, 'modules/stats/index.php', 'stats', 'stats', 'Статистика', 'Статистика игроков с игровых серверов', 'Список, статистика, игроки', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(12, 'modules/profile/index.php', 'profile', 'profile', 'Профиль {value}', 'Профиль пользователя {value}', 'Профиль, пользователь, {value}', 3, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(13, 'modules/messages/index.php', 'messages', 'messages', 'Мои сообщения', 'Мои сообщения', 'Сообщения, диалоги', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(14, 'modules/purse/index.php', 'purse', 'purse', 'Кошелек', 'Кошелек', 'Кошелек', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(15, 'modules/store/index.php', 'store', 'store', 'Магазин', 'Магазин', 'Магазин', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(16, 'modules/store/my_stores.php', 'my_stores', 'my_stores', 'Управление услугами', 'Управление услугами', 'Управление, услуги', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(17, 'modules/notifications/index.php', 'notifications', 'notifications', 'Уведомления', 'Уведомления', 'Уведомления', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(18, 'modules/users/edit_user.php', 'edit_user', 'edit_user', 'Редактирование пользователя {value}', 'Редактирование пользователя {value}', 'Редактирование, пользователь, {value}', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(19, 'modules/error/index.php', 'error_page', 'error_page', 'Ошибка', 'Страница ошибки', 'Ошибка', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(20, 'modules/news/index.php', 'news', 'news', 'Новости', 'Новости проекта', 'Новости', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(21, 'modules/news/new.php', 'news/new', 'news_new', 'Новость', 'Новость', 'Новость', 2, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(22, 'modules/news/add_new.php', 'news/add_new', 'news_add_new', 'Добавление новости', 'Добавление новости', 'Добавить, новость', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(23, 'modules/news/change_new.php', 'news/change_new', 'news_change_new', 'Редактирование новости', 'Редактирование новости', 'Редактировать, новости', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(24, 'modules/forum/index.php', 'forum', 'forum', 'Форум', 'Форум проекта', 'Форум', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(25, 'modules/forum/forum.php', 'forum/forum', 'forum_forum', 'Раздел форума', 'Раздел форума', 'Форум, раздел', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(26, 'modules/forum/edit_forum.php', 'forum/edit_forum', 'forum_edit_forum', 'Настройка форума', 'Настройка форума', 'Настройка, форум', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(27, 'modules/forum/add_topic.php', 'forum/add_topic', 'forum_add_topic', 'Добавление темы', 'Добавление темы', 'Добавление, темы', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(28, 'modules/forum/edit_topic.php', 'forum/edit_topic', 'forum_edit_topic', 'Редактирование темы', 'Редактирование темы', 'Редактирование, темы', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(29, 'modules/forum/edit_message.php', 'forum/edit_message', 'forum_edit_message', 'Редактирование сообщения', 'Редактирование сообщения', 'Редактирование, сообщения', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(30, 'modules/forum/topic.php', 'forum/topic', 'forum_topic', 'Тема', 'Тема', 'Тема', 2, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(31, 'modules/support/index.php', 'support', 'support', 'Поддержка', 'Поддержка', 'Поддержка', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(32, 'modules/support/add_ticket.php', 'support/add_ticket', 'support_add_ticket', 'Добавление тикета', 'Добавление тикета', 'Добавление, тикет', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(33, 'modules/support/ticket.php', 'support/ticket', 'support_ticket', 'Тикет', 'Тикет', 'Тикет', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(34, 'modules/support/all_tickets.php', 'support/all_tickets', 'support_all_tickets', 'Тикеты', 'Тикеты', 'Тикеты', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(35, 'modules/bans/index.php', 'bans', 'bans', 'Заявки на разбан', 'Заявки на разбан', 'Заявки, разбан', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(36, 'modules/bans/ban.php', 'bans/ban', 'bans_ban', 'Заявка от {value}', 'Заявка на разбан от игрока {value}', 'Заявка на разбан, игрок, {value}', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(37, 'modules/bans/add_ban.php', 'bans/add_ban', 'bans_add_ban', 'Добавление заявки на разбан', 'Добавление заявки на разбан', 'Добавление, заявки, разбан', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(38, 'modules/price_list/index.php', 'price_list', 'price_list', 'Услуги проекта', 'Описание и цены на услуги игрового проекта', 'Купить, админку, випку, цены', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(40, 'modules/admin/index.php', 'admin', 'admin', 'Админ центр', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(41, 'modules/admin/page_editor.php', 'admin/page_editor', 'admin_page_editor', 'Редактор страниц', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(42, 'modules/admin/page_edit.php', 'admin/page_edit', 'admin_page_edit', 'Редактирование страницы', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(43, 'modules/admin/menu_editor.php', 'admin/menu_editor', 'admin_menu_editor', 'Редактор меню', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(44, 'modules/admin/logs.php', 'admin/logs', 'admin_logs', 'Логи и блокировки', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(45, 'modules/admin/admins.php', 'admin/admins', 'admin_admins', 'Администраторы', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(46, 'modules/admin/template.php', 'admin/template', 'admin_template', 'Редактор шаблонов', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(47, 'modules/admin/news.php', 'admin/news', 'admin_news', 'Новости', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(48, 'modules/admin/payments.php', 'admin/payments', 'admin_payments', 'Платежные системы', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(49, 'modules/admin/servers.php', 'admin/servers', 'admin_servers', 'Настройка серверов', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(50, 'modules/admin/store.php', 'admin/store', 'admin_store', 'Настройка услуг', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(51, 'modules/admin/bank.php', 'admin/bank', 'admin_bank', 'Монетизация', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(52, 'modules/admin/users_groups.php', 'admin/users_groups', 'admin_users_groups', 'Группы пользователей', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(53, 'modules/admin/email_settings.php', 'admin/email_settings', 'admin_email_settings', 'Настройка почты', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(54, 'modules/admin/users.php', 'admin/users', 'admin_users', 'Пользователи', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(55, 'modules/admin/edit_user.php', 'admin/edit_user', 'admin_edit_user', 'Редактирование данных пользователя', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(56, 'modules/admins/edit_admins.php', 'edit_admins', 'edit_admins', 'Редактирование администраторов сервера', 'Редактирование администраторов сервера', 'Редактирование, администраторов, сервера', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(57, 'modules/admin/stat.php', 'admin/stat', 'admin_stat', 'Статистика', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(58, 'modules/admin/forum_settings.php', 'admin/forum_settings', 'admin_forum_settings', 'Настройка форума', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(59, 'modules/admin/modules.php', 'admin/modules', 'admin_modules', 'Модули', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(65, 'modules/pages/index.php', 'privacy-policy', 'privacy-policy', 'Политика конфиденциальности', 'Политика, конфиденциальности', 'Политика, конфиденциальности', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 1, 1),
(66, 'modules/pages/index.php', 'processing-of-personal-data', 'processing-of-personal-data', 'Согласие на обработку персональных данных', 'Согласие на обработку персональных данных', 'Согласие на обработку персональных данных', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 1, 1),
(67, 'modules/pages/index.php', 'pages/rules', 'pages_rules', 'Правила', 'Правила, игрового, проекта', 'Правила, игрового, проекта', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 1, 2),
(68, 'modules/pages/index.php', 'pages/baza_znaniy', 'pages_baza_znaniy', 'База знаний', 'База, знаний', 'База, знаний', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 1, 2),
(69, 'modules_extra/buy_key/base/index.php', 'buy_key', 'buy_key', 'Покупка VIP', 'Покупка VIP', 'Покупка, VIP', 1, 'files/miniatures/standart.jpg', 2, 2, 1, 1, 1, 0, 0),
(70, 'modules_extra/buy_key/base/admin/servers.php', 'admin/bk_servers', 'admin_bk_servers', 'Настройка услуг модуля buy_key', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 1, 0, 0),
(71, 'modules_extra/buy_key/base/admin/services.php', 'admin/bk_services', 'admin_bk_services', 'Настройка серверов модуля buy_key', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 1, 0, 0),
(72, 'modules_extra/buy_key/base/index.php', 'buy_key', 'buy_key', 'Покупка VIP', 'Покупка VIP', 'Покупка, VIP', 1, 'files/miniatures/standart.jpg', 2, 2, 1, 1, 1, 0, 0),
(73, 'modules_extra/buy_key/base/admin/servers.php', 'admin/bk_servers', 'admin_bk_servers', 'Настройка услуг модуля buy_key', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 1, 0, 0),
(74, 'modules_extra/buy_key/base/admin/services.php', 'admin/bk_services', 'admin_bk_services', 'Настройка серверов модуля buy_key', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 1, 0, 0),
(75, 'modules/admin/verifications.php', 'admin/verifications', 'admin_page_verification', 'Верификация пользователей', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(76, 'modules/playground/index.php', 'market', 'playground', 'Торговая площадка', 'Торговая площадка', 'Торговая, площадка', 1, 'files/miniatures/standart.jpg', 1, 1, 1, 1, 0, 0, 0),
(77, 'modules/playground/inventory.php', 'inventory', 'inventory', 'Инвентарь', 'Инвентарь профиля', 'инвентарь,профиля', 1, 'files/miniatures/standart.jpg', 1, 1, 1, 1, 0, 0, 0),
(78, 'modules/admin/playground.php', 'admin/market', 'admin_market', 'Маркет', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0),
(79, 'modules/complaints/index.php', 'complaints', 'complaints', 'Жалобы', 'Жалобы', 'Жалобы', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(80, 'modules/complaints/complaint.php', 'complaints/complaint', 'complaints_complaint', 'Жалоба на {value}', 'Жалоба на {value}', 'Жалоба на {value}', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(81, 'modules/complaints/add.php', 'complaints/add', 'complaints_add', 'Добавление жалобы', 'Добавление жалобы', 'Добавление жалобы', 1, 'files/miniatures/standart.jpg', 2, 0, 1, 1, 0, 0, 0),
(82, 'modules/prefixes/index.php', 'store/prefixes', 'prefixes', 'Префиксы', 'Префиксы', 'Префиксы', 1, 'files/miniatures/standart.jpg', 1, 2, 1, 1, 0, 0, 0),
(83, 'modules/admin/prefixes.php', 'admin/prefixes', 'admin_prefixes', 'Префиксы', 'none', 'none', 1, 'files/miniatures/standart.jpg', 0, 0, 2, 1, 0, 0, 0);

CREATE TABLE IF NOT EXISTS `pages__classes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `pages__classes` (`id`, `name`) VALUES
(1, ''),
(2, 'pages');

CREATE TABLE IF NOT EXISTS `pages__content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `pages__content` (`id`, `page_id`, `content`) VALUES
(1, 65, '<div class=\"col-md-12\">\n<h4>1. Определение терминов</h4>\n\n<p>1. Существующая на текущий момент политика конфиденциальности персональных данных (далее - Политика конфиденциальности) работает со следующими понятиями:<br />\n- &quot;Администрация сайта&quot;. Так называют представляющих интересы организации специалистов, в чьи обязанности входит управление сайтом, то есть организация и (или) обработка поступивших на него персональных данных. Для выполнения этих обязанностей они должны чётко представлять, для чего обрабатываются сведения, какие сведения должна быть обработаны, какие действия (операции) должны производиться с полученными сведениями.<br />\n- &quot;Персональные данные&quot; - сведения, имеющие прямое или косвенное отношение к определённому либо определяемому физическому лицу (также называемому субъектом персональных данных).<br />\n- &quot;Обработка персональных данных&quot; - любая операция (действие) либо совокупность таковых, которые Администрация производит с персональными данными. Их могут собирать, записывать, систематизировать, накапливать, хранить, уточнять (при необходимости обновлять или изменять), извлекать, использовать, передавать (распространять, предоставлять, открывать к ним доступ), обезличивать, блокировать, удалять и даже уничтожать. Данные операции (действия) могут выполняться как автоматически, так и вручную.<br />\n- &quot;Конфиденциальность персональных данных&quot; - обязательное требование, предъявляемое к Оператору или иному работающему с данными Пользователя должностному лицу, хранить полученные сведения в тайне, не посвящая в них посторонних, если предоставивший персональные данные Пользователь не изъявил своё согласие, а также отсутствует законное основание для разглашения.<br />\n- &quot;Пользователь сайта&quot; (далее - Пользователь) - человек, посетивший сайт, а также пользующийся его программами и продуктами.<br />\n- &quot;Cookies&quot; - короткий фрагмент данных, пересылаемый веб-браузером или веб-клиентом веб-серверу в HTTP-запросе, всякий раз, когда Пользователь пытается открыть страницу сайта. Фрагмент хранится на компьютере Пользователя.<br />\n- &quot;IP-адрес&quot; - уникальный сетевой адрес узла в компьютерной сети, построенной по протоколу TCP/IP.</p>\n\n<h4>2. Общие положения</h4>\n\n<p>1. Просмотр сайта, а также использование его программ и продуктов подразумевают автоматическое согласие с принятой там Политикой конфиденциальности, подразумевающей предоставление Пользователем персональных данных на обработку.<br />\n2. Если Пользователь не принимает существующую Политику конфиденциальности, Пользователь должен покинуть сайт.<br />\n3. Имеющаяся Политика конфиденциальности распространяется только на сайт. Если по ссылкам, размещённым на сайте последнего, Пользователь зайдёт на ресурсы третьих лиц, сайт за его действия ответственности не несёт.<br />\n4. Проверка достоверности персональных данных, которые решил сообщить принявший Политику конфиденциальности Пользователь, не входит в обязанности Администрации сайта.</p>\n\n<h4>3. Предмет политики конфиденциальности</h4>\n\n<p>1. Согласно проводимой в текущий период Политике конфиденциальности Администрация сайта обязана не разглашать персональные данные, сообщаемые Пользователями, регистрирующимися на сайте, а также обеспечивать этим данным абсолютную конфиденциальность.<br />\n2. Чтобы сообщить персональные данные, Пользователь заполняет расположенные на сайте электронные формы. Персональными данными Пользователя, которые подлежат обработке, являются:<br />\n- его фамилия, имя, отчество;<br />\n- его контактный данные;<br />\n- его электронный адрес (e-mail);<br />\n3. Защита данных, автоматически передаваемых при просмотре рекламных блоков и посещении страниц с установленными на них статистическими скриптами системы (пикселями) осуществляется сайтом. Вот перечень этих данных:<br />\n- IP-адрес;<br />\n- сведения из cookies;<br />\n- сведения о браузере (либо другой программе, через которую становится доступен показ рекламы);<br />\n- время посещения сайта;<br />\n- адрес страницы, на которой располагается рекламный блок;<br />\n- реферер (адрес предыдущей страницы).<br />\n4. Последствием отключения cookies может стать невозможность доступа к требующим авторизации частям сайта.<br />\n5. Cайт собирает статистику об IP-адресах всех посетителей. Данные сведения нужны, чтобы выявить и решить технические проблемы и проконтролировать, насколько законным будет проведение финансовых платежей.<br />\n6. Любые другие неоговорённые выше персональные сведения (о том, когда и какие покупки были сделаны, какой при этом использовался браузер, какая была установлена операционная система и пр.) надёжно хранятся и не распространяются. Исключение существующая Политика конфиденциальности предусматривает для случаев, описанных в п.п. 5.2 и 5.3.</p>\n\n<h4>4. Цели сбора персональной информации пользователя</h4>\n\n<p>1. Сбор персональных данных Пользователя Администрацией сайта проводится ради того, чтобы:<br />\n- Идентифицировать Пользователя, который прошёл процедуру регистрации на сайте, чтобы приобрести товар данного сайта.<br />\n- Открыть Пользователю доступ к персонализированным ресурсам данного сайта.<br />\n- Установить с Пользователем обратную связь, под которой подразумевается, в частности, рассылка запросов и уведомлений, касающихся использования сайта, обработка пользовательских запросов и заявок, оказание прочих услуг.<br />\n- Определить местонахождение Пользователя, чтобы обеспечить безопасность платежей и предотвратить мошенничество.<br />\n- Подтвердить, что данные, которые предоставил Пользователь, полны и достоверны.<br />\n- Создать учётную запись, если Пользователь изъявил на то своё желание.<br />\n- Обрабатывать и получать платежи, оспаривать платёж.<br />\n- Обеспечить Пользователю максимально быстрое решение проблем, встречающихся при использовании сайта, за счёт эффективной клиентской и технической поддержки.<br />\n- Рекламировать товары сайта, если Пользователь изъявит на то своё согласие.<br />\n- Предоставить Пользователю доступ на сайты или сервисы сайта, помогая ему тем самым получать продукты, обновления и услуги.</p>\n\n<h4>5. Способы и сроки обработки персональной информации</h4>\n\n<p>1. Срок обработки персональных данных Пользователя ничем не ограничен. Процедура обработки может проводиться любым предусмотренным законодательством способом. В частности, с помощью информационных систем персональных данных, которые могут вестись автоматически либо без средств автоматизации.<br />\n2. Обработанные Администрацией сайта персональные данные Пользователя могут передаваться третьим лицам, в число которых входят организации почтовой связи, операторы электросвязи. Согласие Пользователя на подобную передачу предусмотрено правилами политики сайта.<br />\n3. Также обработанные Администрацией сайта персональные данные могут передаваться уполномоченным органов государственной власти, если это осуществляется на законных основаниях и в предусмотренном законодательством порядке.<br />\n4. Если персональные данные будут утрачены или разглашены, Пользователь уведомляется об этом Администрацией сайта.<br />\n5. Все действия Администрации сайта направлены на то, чтобы не допустить к персональным данным Пользователя третьих лиц (за исключением п.п. 5.2, 5.3). Последним эта информация не должна быть доступна даже случайно, дабы те не уничтожили её, не изменили и не блокировали, не копировали и не распространяли, а также не совершали прочие противозаконные действия. Для защиты пользовательских данных Администрация располагает комплексом организационных и технических мер.<br />\n6. Если персональные данные будут утрачены либо разглашены, Администрация сайта совместно с Пользователем готова принять все возможные меры, дабы предотвратить убытки и прочие негативные последствия, вызванные данной ситуацией.</p>\n\n<h4>6. Обязательства сторон</h4>\n\n<p>1. В обязанности Пользователя входит:<br />\n- Сообщение соответствующих требованиям сайта сведений о себе.<br />\n- Обновление и дополнение предоставляемых им сведений в случае изменения таковых.<br />\n2. В обязанности Администрации сайта входит:<br />\n- Применение полученных сведений исключительно в целях, обозначенных в п. 4 существующей Политики конфиденциальности.<br />\n- Обеспечение конфиденциальности поступивших от Пользователя сведений. Они не должны разглашаться, если Пользователь не даст на то разрешение. Также Администрация не имеет права продавать, обменивать, публиковать либо разглашать прочими способами переданные Пользователем персональные данные, исключая п.п. 5.2 и 5.3 существующей Политики конфиденциальности.<br />\n- Принятие мер предосторожности, дабы персональные данные Пользователя оставались строго конфиденциальными, точно также, как остаются конфиденциальными такого рода сведения в современном деловом обороте.<br />\n- Блокировка персональных пользовательских данных с того момента, с которого Пользователь либо его законный представитель сделает соответствующий запрос. Право сделать запрос на блокировку также предоставляется органу, уполномоченному защищать права Пользователя, предоставившего Администрации сайта свои данные, на период проверки, в случае обнаружения недостоверности сообщённых персональных данных либо неправомерности действий.</p>\n\n<h4>7. Ответственность сторон</h4>\n\n<p>1. В случае неисполнения Администрацией сайта собственных обязательств и, как следствие, убытков Пользователя, понесённых из-за неправомерного использования предоставленной им информации, ответственность возлагается на неё. Об этом, в частности, утверждает законодательство. Исключение существующая в настоящее время Политика конфиденциальности делает для случаев, отражённых в п.п. 5.2, 5.3 и 7.2.<br />\n2. Но существует ряд случаев, когда Администрация сайта ответственности не несёт, если пользовательские данные утрачиваются или разглашаются. Это происходит тогда, когда они: - Превратились в достояние общественности до того, как были утрачены или разглашены.<br />\n- Были предоставлены третьими лицами до того, как их получила Администрация сайта.<br />\n- Разглашались с согласия Пользователя.</p>\n\n<h4>8. Разрешение споров</h4>\n\n<p>1. Если Пользователь недоволен действиями Администрации сайта и намерен отстаивать свои права в суде, до того как обратиться с иском, он в обязательном порядке должен предъявить претензию (письменно предложить урегулировать конфликт добровольно).<br />\n2. Получившая претензию Администрация обязана в течение 30 календарных дней с даты её получения письменно уведомить Пользователя о её рассмотрении и принятых мерах.<br />\n3. Если обе стороны так и не смогли договориться, спор передаётся в судебный орган, где его должны рассмотреть согласно действующему законодательству.<br />\n4. Регулирование отношений Пользователя и Администрации сайта в Политике конфиденциальности проводится согласно действующему законодательству.</p>\n\n<h4>9. Дополнительные условия</h4>\n\n<p>1. Администрация сайта вправе менять существующую на текущий момент Политику конфиденциальности, не спрашивая согласия у Пользователя.<br />\n2. Вступление в силу новой Политики конфиденциальности начинается после того, как информация о ней будет выложена на сайт сайта, если изменившаяся Политика не подразумевает иного варианта размещения.<br />\n3. Все предложения, пожелания, требования или вопросы по настоящей Политике конфиденциальности следует сообщать путем отправки заявки в разделе сайта: <strong>{{$full_site_host}}support</strong><br />\n4. Прочитать о существующей Политике конфиденциальности можно, зайдя на страницу по <strong>{{$full_site_host}}privacy-policy</strong></p>\n\n<p>&nbsp;</p>\n\n<p><strong>Обновлено &quot;14&quot; сентября 2017 г.</strong></p>\n</div>\n'),
(2, 66, '<div class=\"col-md-12\">\n<p>Настоящим я, далее &ndash; &laquo;Субъект Персональных Данных&raquo;, во исполнение требований Федерального закона от 27.07.2006 г. № 152-ФЗ &laquo;О персональных данных&raquo; (с изменениями и дополнениями) свободно, своей волей и в своем интересе даю свое согласие Администрации сайта (далее &ndash; &laquo;Сайт&raquo;, адрес: {{$full_site_host}} ) на обработку своих персональных данных, указанных при регистрации путем заполнения веб-формы на сайте {{$host}}, направляемой (заполненной) с использованием Сайта.</p>\n\n<p>Под персональными данными я понимаю любую информацию, относящуюся ко мне как к Субъекту Персональных Данных, в том числе мои фамилию, имя, отчество, адрес, образование, профессию, контактные данные (телефон, электронная почта, почтовый адрес), фотографии, иную другую информацию. Под обработкой персональных данных я понимаю сбор, систематизацию, накопление, уточнение, обновление, изменение, использование, распространение, передачу, в том числе трансграничную, обезличивание, блокирование, уничтожение, бессрочное хранение), и любые другие действия (операции) с персональными данными.</p>\n\n<p>Обработка персональных данных Субъекта Персональных Данных осуществляется исключительно в целях регистрации Субъекта Персональных Данных в базе данных сайта с последующим направлением Субъекту Персональных Данных почтовых сообщений и смс-уведомлений, в том числе рекламного содержания, от сайта, его аффилированных лиц и/или субподрядчиков, информационных и новостных рассылок и другой информации рекламно-новостного содержания.</p>\n\n<p>Датой выдачи согласия на обработку персональных данных Субъекта Персональных Данных является дата отправки регистрационной веб-формы с Сайта.</p>\n\n<p>Обработка персональных данных Субъекта Персональных Данных может осуществляться с помощью средств автоматизации и/или без использования средств автоматизации в соответствии с действующим законодательством и внутренними положениями Сайта.</p>\n\n<p>Сайт принимает необходимые правовые, организационные и технические меры или обеспечивает их принятие для защиты персональных данных от неправомерного или случайного доступа к ним, уничтожения, изменения, блокирования, копирования, предоставления, распространения персональных данных, а также от иных неправомерных действий в отношении персональных данных, а также принимает на себя обязательство сохранения конфиденциальности персональных данных Субъекта Персональных Данных. Сайт вправе привлекать для обработки персональных данных Субъекта Персональных Данных субподрядчиков, а также вправе передавать персональные данные для обработки своим аффилированным лицам, обеспечивая при этом принятие такими субподрядчиками и аффилированными лицами соответствующих обязательств в части конфиденциальности персональных данных.</p>\n\n<p>Прочитать о существующей Политике конфиденциальности можно, зайдя на страницу по <strong>{{$full_site_host}}privacy-policy</strong></p>\n\n<p>Я ознакомлен(а), что:<br />\n- настоящее согласие на обработку моих персональных данных, указанных при регистрации на Сайте, направляемых (заполненных) с использованием Cайта, действует в течение 20 (двадцати) лет с момента регистрации на Cайте;<br />\n- согласие может быть отозвано мною на основании заявления в произвольной форме;<br />\n- предоставление персональных данных третьих лиц без их согласия влечет ответственность в соответствии с действующим законодательством.</p>\n</div>\n'),
(3, 67, '<p>Правила проекта</p>\n'),
(4, 68, '<p>База знаний</p>\n');

CREATE TABLE IF NOT EXISTS `pays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `method` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `payid` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `playground` (
  `id` int NOT NULL AUTO_INCREMENT,
  `currency` varchar(64) NOT NULL,
  `secret` varchar(256) NOT NULL DEFAULT 'none',
  `limit_product` int(9) NOT NULL DEFAULT 'none' DEFAULT '9',
  `course` float NOT NULL,
  `bonuses` varchar(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `playground` (`id`, `currency`, `course`, `bonuses`) VALUES
(1, 'поинт', 0.1, '0');

CREATE TABLE IF NOT EXISTS `playground__category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `code_name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `playground__category` (`id`, `name`, `code_name`) VALUES
(1, 'Фон профиля', 'background'),
(2, 'Аватар', 'avatar'),
(3, 'Рамка профиля', 'frame');

CREATE TABLE IF NOT EXISTS `playground__product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `price` float NOT NULL,
  `resource` text NOT NULL,
  `executor` text NOT NULL,
  `id_category` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `playground__purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_product` int NOT NULL,
  `id_category` int NOT NULL,
  `id_user` int NOT NULL,
  `price` float NOT NULL,
  `buy_time` int NOT NULL,
  `active` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `playground__sale` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_product` int NOT NULL,
  `id_category` int NOT NULL,
  `id_seller` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm__dialogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id1` int NOT NULL,
  `user_id2` int NOT NULL,
  `dell_1` int NOT NULL DEFAULT '0',
  `dell_2` int NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `new` varchar(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm__messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id1` int NOT NULL,
  `user_id2` int NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `dialog_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `servers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(30) NOT NULL,
  `port` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `type` int NOT NULL DEFAULT '0',
  `ftp_host` varchar(64) DEFAULT '0',
  `ftp_login` varchar(32) DEFAULT '0',
  `ftp_pass` varchar(32) DEFAULT '0',
  `ftp_port` int NOT NULL DEFAULT '21',
  `db_host` varchar(64) NOT NULL DEFAULT '0',
  `db_user` varchar(32) NOT NULL DEFAULT '0',
  `db_pass` varchar(32) NOT NULL DEFAULT '0',
  `db_db` varchar(32) NOT NULL DEFAULT '0',
  `db_prefix` varchar(32) NOT NULL DEFAULT '0',
  `trim` int NOT NULL,
  `game` varchar(35) NOT NULL,
  `ftp_string` varchar(255) NOT NULL DEFAULT 'cstrike/addons/amxmodx/configs',
  `db_code` int NOT NULL DEFAULT '1',
  `st_type` int NOT NULL DEFAULT '0',
  `st_db_host` varchar(64) NOT NULL DEFAULT '0',
  `st_db_user` varchar(32) NOT NULL DEFAULT '0',
  `st_db_pass` varchar(32) NOT NULL DEFAULT '0',
  `st_db_db` varchar(32) NOT NULL DEFAULT '0',
  `st_db_table` varchar(32) NOT NULL DEFAULT '0',
  `st_db_code` int NOT NULL DEFAULT '0',
  `st_sort_type` int NOT NULL DEFAULT '1',
  `pass_prifix` varchar(10) NOT NULL DEFAULT '_pw',
  `discount` int DEFAULT '0',
  `rcon` int DEFAULT '2',
  `rcon_password` varchar(256) DEFAULT '',
  `show` int DEFAULT '1',
  `united` int NOT NULL DEFAULT '0',
  `binds` varchar(6) NOT NULL DEFAULT '1;1;1;',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `servers__commands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `server_id` int DEFAULT '0',
  `command` varchar(512) NOT NULL,
  `title` varchar(512) NOT NULL,
  `slug` varchar(512) NOT NULL,
  `category` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `servers__commands_params` (
  `command_id` int NOT NULL,
  `name` varchar(32) NOT NULL,
  `title` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `servers__prefixes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_server` int NOT NULL, 
  `id_user` int NOT NULL, 
  `steamid` varchar(64) NOT NULL DEFAULT 'none', 
  `nickname` varchar(32) NOT NULL, 
  `password` varchar(32) NOT NULL DEFAULT 'none', 
  `prefix` varchar(32) NOT NULL, 
  `date_start` varchar(64) NOT NULL DEFAULT '0000-00-00 00:00:00', 
  `date_end` varchar(64) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `servers__prefixes_ban` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_server` int NOT NULL,
  `speech` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `servers__prefixes_term` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_server` int NOT NULL, 
  `price` int NOT NULL, 
  `time` int NOT NULL, 
  `discount` int NOT NULL, 
  `rcon` varchar(128) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `rights` varchar(25) NOT NULL DEFAULT '',
  `server` int NOT NULL,
  `text` text NOT NULL,
  `trim` int NOT NULL DEFAULT '0',
  `immunity` int NOT NULL DEFAULT '0',
  `sale` int NOT NULL DEFAULT '1',
  `users_group` int NOT NULL DEFAULT '0',
  `sb_group` varchar(120) NOT NULL DEFAULT '',
  `show_adm` int NOT NULL DEFAULT '1',
  `discount` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `services__tarifs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service` int NOT NULL,
  `price` float NOT NULL DEFAULT '0',
  `price_renewal` float NOT NULL DEFAULT '0',
  `time` int NOT NULL,
  `discount` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stickers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `stickers` (`id`, `name`) VALUES
(1, 'Ничоси'),
(2, 'Персик'),
(3, 'Животные'),
(4, 'Смайлы'),
(5, 'Мемы');

CREATE TABLE IF NOT EXISTS `thanks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mes_id` int NOT NULL,
  `author` int NOT NULL,
  `topic` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `files` varchar(255) NOT NULL,
  `status` int NOT NULL,
  `date` datetime NOT NULL,
  `author` int NOT NULL,
  `last_answer` datetime NOT NULL,
  `closed` int NOT NULL,
  `have_answer` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tickets__answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `author` int NOT NULL,
  `ticket` int NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(40) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(255) NOT NULL,
  `route` varchar(32) DEFAULT NULL,
  `regdate` datetime NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL DEFAULT '/files/cover/standart.jpg',
  `rights` varchar(10) NOT NULL DEFAULT '0',
  `name` varchar(15) NOT NULL DEFAULT '---',
  `nick` varchar(128) NOT NULL DEFAULT '---',
  `status_message` VARCHAR(128) NOT NULL DEFAULT 'none',
  `level` int NOT NULL DEFAULT '0',
  `experience` int NOT NULL DEFAULT '0',
  `verification` int NOT NULL DEFAULT '0',
  `skype` varchar(32) NOT NULL DEFAULT '---',
  `discord` varchar(32) DEFAULT NULL,
  `vk` varchar(30) NOT NULL DEFAULT '---',
  `birth` date NOT NULL,
  `signature` text NOT NULL,
  `answers` int DEFAULT '0',
  `playground` int DEFAULT '0',
  `shilings` float NOT NULL,
  `stickers` int NOT NULL DEFAULT '0',
  `thanks` int DEFAULT '0',
  `last_activity` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dell` int NOT NULL DEFAULT '0',
  `last_topic` int NOT NULL DEFAULT '0',
  `reit` int DEFAULT '0',
  `proc` int NOT NULL DEFAULT '0',
  `steam_id` varchar(35) NOT NULL DEFAULT '0',
  `steam_api` varchar(100) NOT NULL DEFAULT '0',
  `vk_api` varchar(25) NOT NULL DEFAULT '0',
  `fb` varchar(20) DEFAULT '0',
  `fb_api` varchar(20) DEFAULT '0',
  `active` int NOT NULL DEFAULT '1',
  `im` int NOT NULL DEFAULT '1',
  `telegram` varchar(50) NOT NULL DEFAULT '',
  `prefix` varchar(30) DEFAULT '',
  `game_time` int NOT NULL DEFAULT '0',
  `protect` int NOT NULL DEFAULT '2',
  `invited` int DEFAULT '0',
  `email_notice` int NOT NULL DEFAULT '1',
  `ip` varchar(15) NOT NULL DEFAULT '127.0.0.1',
  `browser` varchar(32) DEFAULT NULL,
  `multi_account` varchar(40) DEFAULT '0',
  `gag` int NOT NULL DEFAULT '2' COMMENT '1 - on, 2 - off',
  `member_online` int DEFAULT '0',
  `game_money` int DEFAULT '0',
  `plugins_settings` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users__application-list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `timeleft` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users__black_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `who` int NOT NULL,
  `whom` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users__blocked` (
  `ip` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `date` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `col` int NOT NULL DEFAULT '3',
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users__comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `author` int NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users__friends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_sender` int NOT NULL,
  `id_taker` int NOT NULL,
  `accept` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users__groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `color` varchar(255) NOT NULL,
  `rights` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `users__groups` (`id`, `name`, `color`, `rights`) VALUES
(1, 'Создатель', '#d1574d;', 'amcdybqfgpltweriojh'),
(2, 'Пользователь', '#45688E;', 'aw'),
(3, 'Временный бан', '#000000', 'z'),
(4, 'Вечный бан', '#404040', 'x');

CREATE TABLE IF NOT EXISTS `users__online` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `time` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `val` int NOT NULL,
  `key` varchar(50) NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;