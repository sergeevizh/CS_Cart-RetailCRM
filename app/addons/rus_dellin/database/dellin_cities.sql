
DROP TABLE IF EXISTS `?:rus_dellin_cities`;
CREATE TABLE IF NOT EXISTS `?:rus_dellin_cities` (
  `city_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `number_city` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(150) NOT NULL DEFAULT '',
  `state` varchar(150) NOT NULL DEFAULT '',
  `code_kladr` varchar(255) NOT NULL DEFAULT '',
  `is_terminal` varchar(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`city_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
