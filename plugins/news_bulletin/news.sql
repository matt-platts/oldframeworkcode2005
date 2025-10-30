--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) default NULL,
  `item_text` text,
  `active` enum('Yes','No') default NULL,
  `date_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified_by` int(11) default NULL,
  `locked` enum('0','1') default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
