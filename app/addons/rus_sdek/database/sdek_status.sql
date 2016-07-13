
DROP TABLE IF EXISTS rus_sdek_status;
CREATE TABLE IF NOT EXISTS `?:rus_sdek_status` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `order_id` mediumint(8) NOT NULL,
  `shipment_id` mediumint(8) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `status` varchar(256) NOT NULL,
  `city_code` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
