
DROP TABLE IF EXISTS rus_sdek_call_recipient;
CREATE TABLE IF NOT EXISTS `?:rus_sdek_call_recipient` (
  `call_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) NOT NULL,
  `shipment_id` mediumint(8) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `shipment_date` varchar(10) NOT NULL DEFAULT '',
  `timebag` varchar(8) NOT NULL DEFAULT '',
  `timeend` varchar(8) NOT NULL DEFAULT '',
  `recipient_name` varchar(256) NOT NULL DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '',
  `recipient_cost` decimal(12,2) NOT NULL DEFAULT '0',
  `address` varchar(256) NOT NULL DEFAULT '',
  `pvz_code` varchar(8) NOT NULL DEFAULT '',
  `call_comment` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`call_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
