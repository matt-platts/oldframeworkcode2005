--
-- Table structure for table `photo_gallery_items`
--

DROP TABLE IF EXISTS `photo_gallery_items`;
CREATE TABLE `photo_gallery_items` (
  `id` int(11) NOT NULL auto_increment,
  `gallery_id` int(11) default NULL,
  `title` varchar(64) default NULL,
  `image` varchar(128) default NULL,
  `description` text,
  `active` enum('1','0') default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

