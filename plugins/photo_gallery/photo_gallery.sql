--
-- Table structure for table `photo_gallery`
--

DROP TABLE IF EXISTS `photo_gallery`;

CREATE TABLE `photo_gallery` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `description` text,
  `directory` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
