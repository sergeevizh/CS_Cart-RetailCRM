
DROP TABLE IF EXISTS rus_sdek_call_courier;
CREATE TABLE IF NOT EXISTS `?:rus_sdek_call_courier` (
  `call_courier_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) NOT NULL,
  `shipment_id` mediumint(8) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `call_courier_date` varchar(10) NOT NULL DEFAULT '',
  `timebag` varchar(8) NOT NULL DEFAULT '',
  `timeend` varchar(8) NOT NULL DEFAULT '',
  `lunch_timebag` varchar(8) NOT NULL DEFAULT '',
  `lunch_timeend` varchar(8) NOT NULL DEFAULT '',
  `weight` decimal(12,2) NOT NULL DEFAULT '0',
  `comment_courier` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`call_courier_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
