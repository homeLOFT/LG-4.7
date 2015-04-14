CREATE TABLE `xcart_xps_orders` (
  `subscriptionid` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`subscriptionid`,`orderid`),
  KEY `orderid` (`orderid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `xcart_xps_products` (
  `productid` int(11) NOT NULL,
  `subscription_product` char(1) NOT NULL DEFAULT 'N',
  `type` char(1) NOT NULL DEFAULT '',
  `number` int(11) NOT NULL DEFAULT '0',
  `period` char(1) NOT NULL DEFAULT '',
  `reverse` char(1) NOT NULL DEFAULT '',
  `fee` decimal(12,2) NOT NULL,
  `rebill_periods` int(11) NOT NULL,
  PRIMARY KEY (`productid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `xcart_xps_subscriptions` (
  `subscriptionid` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL DEFAULT '0',
  `productid` int(11) NOT NULL DEFAULT '0',
  `fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` char(1) NOT NULL DEFAULT '',
  `attempts` int(11) NOT NULL DEFAULT '0',
  `rebill_periods` int(11) NOT NULL,
  `success_attempts` int(11) NOT NULL,
  `next_date` int(11) NOT NULL DEFAULT '0',
  `real_next_date` int(11) NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT '',
  `number` int(11) NOT NULL DEFAULT '0',
  `period` char(1) NOT NULL DEFAULT '',
  `reverse` char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (`subscriptionid`),
  KEY rs (`real_next_date`, `status`),
  KEY `orderid` (`orderid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `xcart_config` (`name`, `comment`, `value`, `category`, `orderby`, `type`, `defvalue`, `variants`, `validation`, `signature`) VALUES('xps_rebill_attempts', 'Number of rebill attempts for an unsuccessful charge', '3', 'XPayments_Subscriptions', 10, 'numeric', '3', '', '', '');
REPLACE INTO `xcart_config` (`name`, `comment`, `value`, `category`, `orderby`, `type`, `defvalue`, `variants`, `validation`, `signature`) VALUES('xps_rebill_attempt_period', 'How often to attempt rebills for an unsuccessful charge (in days)', '1', 'XPayments_Subscriptions', 20, 'numeric', '1', '', '', '');
REPLACE INTO `xcart_config` (`name`, `comment`, `value`, `category`, `orderby`, `type`, `defvalue`, `variants`, `validation`, `signature`) VALUES('xps_notification_days', 'Notify the customer of the upcoming payment X days in advance', '3', 'XPayments_Subscriptions', 30, 'numeric', '3', '', '', '');

INSERT INTO `xcart_modules` (`module_name`, `module_descr`, `active`, `author`, `tags`) VALUES('XPayments_Subscriptions', 'This module allows to create product subscriptions and process them via X-Payments module.', 'N', 'qualiteam', 'products');
