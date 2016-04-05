/*
* @Author: printempw
* @Date:   2016-04-03 16:22:11
* @Last Modified by:   printempw
* @Last Modified time: 2016-04-05 14:08:28
*/

CREATE TABLE IF NOT EXISTS `{$prefix}users` (
  `uid` int(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `preference` varchar(10) NOT NULL,
  `hash_steve` varchar(64) DEFAULT '',
  `hash_alex` varchar(64) DEFAULT '',
  `hash_cape` varchar(64) DEFAULT '',
  `last_modified` datetime,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}options` (
  `option_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(50) NOT NULL,
  `option_value` longtext,
  PRIMARY KEY (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{$prefix}options` (`option_name`, `option_value`) VALUES
('site_url',           ''),
('site_name',          '{$sitename}'),
('site_description',   'Minecraft 皮肤站'),
('current_version',    '2.4'),
('user_can_register',  '1'),
('user_default_skin',  ''),
('regs_per_ip',        '2'),
('upload_max_size',    '1024'),
('api_type',           '0'),
('announcement',       '这是默认的公告~'),
('data_adapter',       ''),
('encryption',         'MD5'),
('data_table_name',    'authme for example'),
('data_column_uname',  'username'),
('data_column_passwd', 'password'),
('data_column_ip',     'ip'),
('color_scheme',       'skin-blue'),
('home_pic_url',       './assets/images/bg.jpg'),
('google_font_cdn',    'moefont'),
('custom_css',         ''),
('custom_js',          ''),
('update_url',         'https://work.prinzeugen.net/update.json');

