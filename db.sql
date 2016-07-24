/*
 * @Author: printempw
 * @Date:   2016-07-24 13:37:40
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-07-24 14:49:24
 */

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

--
-- Database: `skin-v3`
--

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `score` int(11) NOT NULL,
  `avatar` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `permission` int(11) NOT NULL DEFAULT '0',
  `last_sign_at` datetime NOT NULL,
  `register_at` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `closets`
--

CREATE TABLE IF NOT EXISTS `closets` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `textures` longtext NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `player_name` varchar(50) NOT NULL,
  `preference` varchar(10) NOT NULL,
  `tid_steve` int(11) NOT NULL,
  `tid_alex` int(11) NOT NULL,
  `tid_cape` int(11) NOT NULL,
  `last_modified` datetime NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `textures`
--

CREATE TABLE IF NOT EXISTS `textures` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` varchar(10) NOT NULL,
  `likes` int(11) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `size` int(11) NOT NULL,
  `uploader` int(11) NOT NULL,
  `public` int(11) NOT NULL,
  `upload_at` datetime NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `options`
--

CREATE TABLE IF NOT EXISTS `options` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(50) NOT NULL,
  `option_value` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `options`
--

INSERT INTO `options` (`id`, `option_name`, `option_value`) VALUES
(1, 'site_url', ''),
(2, 'site_name', 'Blessing Skin'),
(3, 'site_description', '开源的 PHP Minecraft 皮肤站'),
(4, 'user_can_register', '1'),
(5, 'regs_per_ip', '3'),
(6, 'api_type', '0'),
(7, 'announcement', '欢迎使用 Blessing Skin Server 3.0！'),
(8, 'color_scheme', 'skin-blue'),
(9, 'home_pic_url', './assets/images/bg.jpg'),
(10, 'current_version', '3.0-beta'),
(11, 'custom_css', ''),
(12, 'custom_js', ''),
(13, 'update_url', 'https://work.prinzeugen.net/update.json'),
(14, 'allow_chinese_playername', '1'),
(15, 'show_footer_copyright', '1'),
(16, 'comment_script', ''),
(17, 'user_initial_score', '1000'),
(18, 'sign_gap_time', '24');
