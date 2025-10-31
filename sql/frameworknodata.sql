-- MySQL dump 10.14  Distrib 5.5.68-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: gonzoshop
-- ------------------------------------------------------
-- Server version	5.5.68-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_payments`
--

DROP TABLE IF EXISTS `account_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  `payment_amount` decimal(5,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_pages`
--

DROP TABLE IF EXISTS `admin_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dbf_key_name` varchar(32) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dbf_key_name` (`dbf_key_name`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_templates`
--

DROP TABLE IF EXISTS `admin_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dbf_key_name` varchar(32) DEFAULT NULL,
  `template_name` varchar(64) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `template_description` varchar(255) DEFAULT NULL,
  `template` text,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dbf_key_name` (`dbf_key_name`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `administrators`
--

DROP TABLE IF EXISTS `administrators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `last_login_time` datetime DEFAULT NULL,
  `admin_password` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alerts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `typeID` int(11) NOT NULL DEFAULT '0',
  `title` text COLLATE latin1_general_ci NOT NULL,
  `description` text COLLATE latin1_general_ci NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_visible` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_due` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `originator_userID` int(11) NOT NULL DEFAULT '0',
  `assigned_userID` int(11) NOT NULL DEFAULT '0',
  `closed` int(11) NOT NULL DEFAULT '0',
  `emailed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alerts_archive`
--

DROP TABLE IF EXISTS `alerts_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alerts_archive` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `description` text COLLATE latin1_general_ci NOT NULL,
  `date_visible` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_due` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `originator_userID` int(11) NOT NULL DEFAULT '0',
  `assigned_userID` int(11) NOT NULL DEFAULT '0',
  `closed` int(11) NOT NULL DEFAULT '0',
  `emailed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `artist_images`
--

DROP TABLE IF EXISTS `artist_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artist_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artist` int(11) DEFAULT NULL,
  `image` varchar(128) DEFAULT NULL,
  `default_image` tinyint(1) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `artist_mailing_lists`
--

DROP TABLE IF EXISTS `artist_mailing_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artist_mailing_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artist` int(11) DEFAULT NULL,
  `email_address` varchar(64) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `artists`
--

DROP TABLE IF EXISTS `artists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artist` varchar(128) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `image` varchar(128) DEFAULT NULL,
  `home_page` text,
  `new_artist` tinyint(1) DEFAULT NULL,
  `featured_artist` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `parent_artist` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6947 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `artists_mailing_list`
--

DROP TABLE IF EXISTS `artists_mailing_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artists_mailing_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artist` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `email_address` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audio_sections`
--

DROP TABLE IF EXISTS `audio_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audio_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `checkout_modules`
--

DROP TABLE IF EXISTS `checkout_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checkout_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `checkout_itemisation_text` varchar(64) DEFAULT NULL,
  `admin_itemisation_text` varchar(32) DEFAULT NULL,
  `key_name` varchar(32) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `class_file` varchar(64) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `page_display` tinyint(1) DEFAULT NULL,
  `page_display_ordering` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms`
--

DROP TABLE IF EXISTS `cms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) DEFAULT NULL,
  `cms_variable` varchar(32) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_key_options`
--

DROP TABLE IF EXISTS `config_key_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_key_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `function` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` int(11) DEFAULT NULL,
  `config_name` varchar(64) DEFAULT NULL,
  `function` varchar(255) DEFAULT NULL,
  `config_value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuration_sections`
--

DROP TABLE IF EXISTS `configuration_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(64) DEFAULT NULL,
  `section_description` text,
  `plugin` int(11) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_details`
--

DROP TABLE IF EXISTS `contact_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_name` varchar(64) DEFAULT NULL,
  `address_1` varchar(64) DEFAULT NULL,
  `address_2` varchar(64) DEFAULT NULL,
  `address_3` varchar(64) DEFAULT NULL,
  `address_4` varchar(64) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contacts_ticket_list`
--

DROP TABLE IF EXISTS `contacts_ticket_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_ticket_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactID` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `short_description` text COLLATE latin1_general_ci NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `alert_ID` int(11) NOT NULL DEFAULT '0',
  `alert_title` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `alert_closed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dbf_key_name` varchar(32) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `subtitle` varchar(120) DEFAULT NULL,
  `value` text,
  `content_section` int(11) DEFAULT NULL,
  `browser_title` varchar(128) DEFAULT NULL,
  `keywords` varchar(512) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `realname` varchar(32) DEFAULT NULL,
  `append_file` varchar(64) DEFAULT NULL,
  `add_to_sitemap` tinyint(1) DEFAULT NULL,
  `assign_to_web_site` int(11) DEFAULT NULL,
  `default_template` int(11) DEFAULT NULL,
  `html_page_name` varchar(64) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `requires_login` tinyint(1) DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hide_from_administrators` tinyint(1) DEFAULT NULL,
  `javascript` text,
  `css` text,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=218 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `content_sections`
--

DROP TABLE IF EXISTS `content_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(64) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `ID` int(5) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Formal_name` varchar(100) NOT NULL DEFAULT '',
  `Continent` varchar(20) NOT NULL DEFAULT '',
  `Type` varchar(30) NOT NULL DEFAULT '',
  `Subtype` varchar(30) NOT NULL DEFAULT '',
  `Sovereignty` varchar(50) NOT NULL DEFAULT '',
  `eu_country` tinyint(1) DEFAULT NULL,
  `Capital` varchar(50) NOT NULL DEFAULT '',
  `Currency_code` varchar(10) NOT NULL DEFAULT '',
  `Currency_name` varchar(30) NOT NULL DEFAULT '',
  `Telephone_code` varchar(10) NOT NULL DEFAULT '',
  `FIPS_10_4` varchar(10) NOT NULL DEFAULT '',
  `ISO_2_letter_code` varchar(5) NOT NULL DEFAULT '',
  `ISO_3_letter_code` varchar(5) NOT NULL DEFAULT '',
  `ISO_number` varchar(5) NOT NULL DEFAULT '',
  `TLD` varchar(5) NOT NULL DEFAULT '',
  `shipping_zone` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=275 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit_card_details`
--

DROP TABLE IF EXISTS `credit_card_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_card_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` int(11) DEFAULT NULL,
  `card_type` varchar(16) DEFAULT NULL,
  `card_number` varchar(32) DEFAULT NULL,
  `start_date` varchar(8) DEFAULT NULL,
  `expiry_date` varchar(8) DEFAULT NULL,
  `issue_number` int(11) DEFAULT NULL,
  `cv2` int(11) DEFAULT NULL,
  `name_on_card` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6834 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `description` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `base_currency` char(1) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'N',
  `conversion_rate` double NOT NULL DEFAULT '1',
  `symbol_text` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `symbol_picture` varchar(75) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `image_flag` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `enabled` char(1) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbf_internal_messages`
--

DROP TABLE IF EXISTS `dbf_internal_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbf_internal_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `message` text,
  `resolved` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `distributors`
--

DROP TABLE IF EXISTS `distributors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distributors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `uk` tinyint(1) DEFAULT NULL,
  `us` tinyint(1) DEFAULT NULL,
  `contact_name` varchar(64) DEFAULT NULL,
  `email_address` varchar(64) DEFAULT NULL,
  `address_1` varchar(64) DEFAULT NULL,
  `address_2` varchar(64) DEFAULT NULL,
  `address_3` varchar(64) DEFAULT NULL,
  `county_or_state` varchar(64) DEFAULT NULL,
  `post_code` varchar(64) DEFAULT NULL,
  `telephone` varchar(32) DEFAULT NULL,
  `telephone_2` varchar(32) DEFAULT NULL,
  `distributor_notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documentation`
--

DROP TABLE IF EXISTS `documentation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_directory` varchar(64) DEFAULT NULL,
  `html_page_name` varchar(128) DEFAULT NULL,
  `page_content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `double_lookup_test`
--

DROP TABLE IF EXISTS `double_lookup_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `double_lookup_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_list` varchar(64) DEFAULT NULL,
  `lookup_list` varchar(64) DEFAULT NULL,
  `third_list` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `download_urls`
--

DROP TABLE IF EXISTS `download_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `download_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_data_id` int(11) DEFAULT NULL,
  `download_url` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12254 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_configuration`
--

DROP TABLE IF EXISTS `email_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `mail_from_name` varchar(64) DEFAULT NULL,
  `mail_from_address` varchar(64) DEFAULT NULL,
  `mail_to_address` varchar(64) DEFAULT NULL,
  `mail_type` enum('text','html') DEFAULT NULL,
  `additional_headers` text,
  `email_template` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esoteric_audio`
--

DROP TABLE IF EXISTS `esoteric_audio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esoteric_audio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `image` varchar(64) DEFAULT NULL,
  `description` text,
  `real_audio_file` varchar(64) DEFAULT NULL,
  `mp3_file` varchar(64) DEFAULT NULL,
  `relates_to_artist` int(11) DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_date` date DEFAULT NULL,
  `audio_section` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esoteric_audio_to_sections`
--

DROP TABLE IF EXISTS `esoteric_audio_to_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esoteric_audio_to_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radio_show` int(11) DEFAULT NULL,
  `section` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_manager`
--

DROP TABLE IF EXISTS `file_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) DEFAULT NULL,
  `description` varchar(64) DEFAULT NULL,
  `directory` varchar(64) DEFAULT NULL,
  `limit_file_types` varchar(64) DEFAULT NULL,
  `list_type` varchar(16) DEFAULT NULL,
  `default_no_per_page` tinyint(4) DEFAULT NULL,
  `back_end_admin_only` tinyint(4) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  `default_interface` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_manager_actions`
--

DROP TABLE IF EXISTS `file_manager_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_manager_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(32) DEFAULT NULL,
  `code_to_run` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_manager_interfaces`
--

DROP TABLE IF EXISTS `file_manager_interfaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_manager_interfaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interface_name` varchar(32) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_manager_macro_actions`
--

DROP TABLE IF EXISTS `file_manager_macro_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_manager_macro_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `macro_id` int(11) DEFAULT NULL,
  `action` varchar(128) DEFAULT NULL,
  `variables` varchar(128) DEFAULT NULL,
  `action_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_manager_macros`
--

DROP TABLE IF EXISTS `file_manager_macros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_manager_macros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `directory` varchar(128) DEFAULT NULL,
  `file_type` varchar(128) DEFAULT NULL,
  `lock_to_folder` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_manager_options`
--

DROP TABLE IF EXISTS `file_manager_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_manager_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interface` int(11) DEFAULT NULL,
  `file_manager_option` varchar(32) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files2`
--

DROP TABLE IF EXISTS `files2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files2` (
  `FileID` int(5) NOT NULL AUTO_INCREMENT,
  `Hash` varchar(32) NOT NULL DEFAULT '',
  `Filename` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `Basename` varchar(100) NOT NULL DEFAULT '',
  `Type` varchar(10) NOT NULL DEFAULT '',
  `Path` varchar(100) NOT NULL DEFAULT '',
  `Directory1` varchar(50) NOT NULL DEFAULT '',
  `Directory2` varchar(50) NOT NULL DEFAULT '',
  `Directory3` varchar(50) NOT NULL DEFAULT '',
  `Width` int(5) NOT NULL DEFAULT '0',
  `Height` int(5) NOT NULL DEFAULT '0',
  `Artist` varchar(50) NOT NULL DEFAULT '',
  `Album` varchar(50) NOT NULL DEFAULT '',
  `Title` varchar(50) NOT NULL DEFAULT '',
  `Number` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`FileID`),
  UNIQUE KEY `Hash` (`Hash`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filter_key_options`
--

DROP TABLE IF EXISTS `filter_key_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter_key_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interface_type` varchar(32) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `notes` text,
  `related_to_field` tinyint(1) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `parent_value` varchar(64) DEFAULT NULL,
  `set_values` varchar(255) DEFAULT NULL,
  `text` varchar(64) DEFAULT NULL,
  `ordering` tinyint(4) DEFAULT NULL,
  `default_value` varchar(32) DEFAULT NULL,
  `element_type` varchar(16) DEFAULT NULL,
  `generated_from_single_fields` tinyint(4) DEFAULT NULL,
  `advanced_field_option` tinyint(4) DEFAULT NULL,
  `group_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=195 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filter_keys`
--

DROP TABLE IF EXISTS `filter_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) DEFAULT NULL,
  `field` varchar(32) DEFAULT NULL,
  `value` varchar(512) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  `user_type` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1702 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_name` varchar(64) DEFAULT NULL,
  `source_data` varchar(128) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  `parent_filter` int(11) DEFAULT NULL,
  `filter_type` varchar(32) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=226 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flight_logistics_config`
--

DROP TABLE IF EXISTS `flight_logistics_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight_logistics_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_seq` int(11) DEFAULT NULL,
  `sku_field_in_products_table` varchar(32) DEFAULT NULL,
  `default_currency` varchar(8) DEFAULT NULL,
  `test_mode` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flight_logistics_order_post_responses`
--

DROP TABLE IF EXISTS `flight_logistics_order_post_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight_logistics_order_post_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `flight_order_number` int(11) DEFAULT NULL,
  `flight_return_status` varchar(16) DEFAULT NULL,
  `flight_return_string` varchar(255) DEFAULT NULL,
  `post_time` datetime DEFAULT NULL,
  `full_posted_string` text,
  `manual_resend_date` datetime DEFAULT NULL,
  `manual_resend_status` varchar(16) DEFAULT NULL,
  `manual_resend_return_string` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11266 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flight_post_backup_mid_daintess`
--

DROP TABLE IF EXISTS `flight_post_backup_mid_daintess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight_post_backup_mid_daintess` (
  `id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) DEFAULT NULL,
  `flight_order_number` int(11) DEFAULT NULL,
  `flight_return_status` varchar(16) DEFAULT NULL,
  `flight_return_string` varchar(255) DEFAULT NULL,
  `post_time` datetime DEFAULT NULL,
  `full_posted_string` text,
  `manual_resend_date` datetime DEFAULT NULL,
  `manual_resend_status` varchar(16) DEFAULT NULL,
  `manual_resend_return_string` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flight_post_backup_pre_hawkwind`
--

DROP TABLE IF EXISTS `flight_post_backup_pre_hawkwind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight_post_backup_pre_hawkwind` (
  `id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) DEFAULT NULL,
  `flight_order_number` int(11) DEFAULT NULL,
  `flight_return_status` varchar(16) DEFAULT NULL,
  `flight_return_string` varchar(255) DEFAULT NULL,
  `post_time` datetime DEFAULT NULL,
  `full_posted_string` text,
  `manual_resend_date` datetime DEFAULT NULL,
  `manual_resend_status` varchar(16) DEFAULT NULL,
  `manual_resend_return_string` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flight_sales_data`
--

DROP TABLE IF EXISTS `flight_sales_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight_sales_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_ref` varchar(32) DEFAULT NULL,
  `cat_no` varchar(16) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `sell_price` decimal(5,2) DEFAULT NULL,
  `line_price` decimal(5,2) DEFAULT NULL,
  `address_ref` varchar(64) DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `invoice_company_address_1` varchar(128) DEFAULT NULL,
  `invoice_company_address_2` varchar(128) DEFAULT NULL,
  `invoice_company_address_3` varchar(64) DEFAULT NULL,
  `invoice_company_address_4` varchar(64) DEFAULT NULL,
  `invoice_company_address_5` varchar(32) DEFAULT NULL,
  `invoice_company_address_6` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `folder_info`
--

DROP TABLE IF EXISTS `folder_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folder_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder` varchar(128) DEFAULT NULL,
  `folder_text` varchar(1024) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `genre` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `products_available` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `google_analytics_code`
--

DROP TABLE IF EXISTS `google_analytics_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `google_analytics_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) DEFAULT NULL,
  `code` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `headers_and_footers`
--

DROP TABLE IF EXISTS `headers_and_footers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `headers_and_footers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(32) DEFAULT NULL,
  `item_content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homepage_ads`
--

DROP TABLE IF EXISTS `homepage_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_image` varchar(128) DEFAULT NULL,
  `auto_generate_popup_content` tinyint(1) DEFAULT NULL,
  `ad_content` text,
  `page_content` text,
  `promoted_product` int(11) DEFAULT NULL,
  `alternate_product_image` varchar(128) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interfaces`
--

DROP TABLE IF EXISTS `interfaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interfaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` int(11) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `template` int(11) DEFAULT NULL,
  `filter` int(11) DEFAULT NULL,
  `row_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(64) DEFAULT NULL,
  `logo` varchar(128) DEFAULT NULL,
  `home_page` text,
  `new_label` tinyint(1) DEFAULT NULL,
  `featured_label` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=870 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `licencees`
--

DROP TABLE IF EXISTS `licencees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licencees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_name` varchar(128) DEFAULT NULL,
  `address_1` varchar(128) DEFAULT NULL,
  `address_2` varchar(128) DEFAULT NULL,
  `address_3` varchar(128) DEFAULT NULL,
  `country_or_state` varchar(128) DEFAULT NULL,
  `post_code` varchar(128) DEFAULT NULL,
  `country` varchar(64) DEFAULT NULL,
  `tel` varchar(64) DEFAULT NULL,
  `mobile` varchar(64) DEFAULT NULL,
  `email_address` varchar(64) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=144 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `literature_products`
--

DROP TABLE IF EXISTS `literature_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `literature_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_number` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `unit_of_issue` int(11) DEFAULT NULL,
  `max_order_quantity` int(11) DEFAULT NULL,
  `price_per_issue` float DEFAULT NULL,
  `pdf` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=713 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `literature_sales`
--

DROP TABLE IF EXISTS `literature_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `literature_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordered_by` int(11) DEFAULT NULL,
  `date_placed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `complete` tinyint(4) DEFAULT NULL,
  `delivery_address` int(11) DEFAULT NULL,
  `cost_centre_no` varchar(32) DEFAULT NULL,
  `sub_total` float DEFAULT NULL,
  `post_and_packing` float DEFAULT NULL,
  `full_total` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=130 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `literature_sales_products`
--

DROP TABLE IF EXISTS `literature_sales_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `literature_sales_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `literature_sections`
--

DROP TABLE IF EXISTS `literature_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `literature_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailing_list`
--

DROP TABLE IF EXISTS `mailing_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailing_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT NULL,
  `email_address` varchar(128) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_confirmed` datetime DEFAULT NULL,
  `confirmation_key_string` varchar(64) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=845 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailing_lists`
--

DROP TABLE IF EXISTS `mailing_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailing_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_name` varchar(64) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `list_email_address` varchar(64) DEFAULT NULL,
  `list_email_address_name` varchar(64) DEFAULT NULL,
  `content_type` varchar(64) DEFAULT NULL,
  `headers` text,
  `confirmation_email_template` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailouts`
--

DROP TABLE IF EXISTS `mailouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `template_description` varchar(255) DEFAULT NULL,
  `notes` text,
  `mail_status` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_interface`
--

DROP TABLE IF EXISTS `master_interface`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_interface` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `icon` varchar(32) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `helptip` varchar(255) DEFAULT NULL,
  `helptext` mediumtext,
  `parents` varchar(16) DEFAULT NULL,
  `master_table` varchar(64) DEFAULT NULL,
  `category` varchar(32) DEFAULT NULL,
  `ordering` tinyint(4) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  `configuration_section` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `name` (`name`,`helptip`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(32) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) DEFAULT NULL,
  `item_text` varchar(128) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  `user_type` varchar(16) DEFAULT NULL,
  `restricted_to` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=153 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moodgetstest`
--

DROP TABLE IF EXISTS `moodgetstest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moodgetstest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `textvalue` varchar(64) DEFAULT NULL,
  `intvalue` int(11) DEFAULT NULL,
  `cboxval` tinyint(1) DEFAULT NULL,
  `my_date` date DEFAULT NULL,
  `date_and_time` datetime DEFAULT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT NULL,
  `item_text` text,
  `active` enum('Yes','No') DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) DEFAULT NULL,
  `locked` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_articles`
--

DROP TABLE IF EXISTS `news_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_articles` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `latest_news` int(11) NOT NULL DEFAULT '0',
  `title` varchar(200) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  `short_description` text COLLATE latin1_general_ci NOT NULL,
  `full_description` text COLLATE latin1_general_ci NOT NULL,
  `thumb_image` varchar(200) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `detail_image` varchar(200) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enabled` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsprint`
--

DROP TABLE IF EXISTS `newsprint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsprint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `summary` text,
  `pdf_link` varchar(128) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `cover_image` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_product_attributes`
--

DROP TABLE IF EXISTS `order_product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_product_id` int(11) DEFAULT NULL,
  `attribute_name` varchar(128) DEFAULT NULL,
  `attribute_value` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_products`
--

DROP TABLE IF EXISTS `order_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17557 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_statuses`
--

DROP TABLE IF EXISTS `order_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) DEFAULT NULL,
  `order_status` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_total_extras`
--

DROP TABLE IF EXISTS `order_total_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_total_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `module` varchar(64) DEFAULT NULL,
  `amount` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_user_data`
--

DROP TABLE IF EXISTS `order_user_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `telephone` varchar(128) DEFAULT NULL,
  `billing_address` varchar(128) DEFAULT NULL,
  `billing_country` int(11) DEFAULT NULL,
  `delivery_address` varchar(128) DEFAULT NULL,
  `delivery_country` int(11) DEFAULT NULL,
  `order_notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9373 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordered_by` int(11) DEFAULT NULL,
  `non_account_order` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivery_address` int(11) DEFAULT NULL,
  `po_number` varchar(32) DEFAULT NULL,
  `comment` varchar(512) DEFAULT NULL,
  `paid` tinyint(1) DEFAULT NULL,
  `date_paid` datetime DEFAULT NULL,
  `complete` tinyint(4) DEFAULT NULL,
  `total_amount` decimal(8,2) DEFAULT NULL,
  `purchased_through_account` varchar(128) DEFAULT NULL,
  `posted_to_flight` tinyint(1) DEFAULT NULL,
  `payment_method` varchar(32) DEFAULT NULL,
  `shipping_total` decimal(5,2) DEFAULT NULL,
  `grand_total` decimal(8,2) DEFAULT NULL,
  `origin` varchar(32) DEFAULT NULL,
  `order_country` int(11) DEFAULT NULL,
  `vatable` tinyint(1) DEFAULT NULL,
  `pre_order` tinyint(1) DEFAULT NULL,
  `preorder_date_shipped` datetime DEFAULT NULL,
  `paypal_preorder_reminder_sent` datetime DEFAULT NULL,
  `preorder_auth_fail` datetime DEFAULT NULL,
  `sagepay_auth_attempts` int(11) DEFAULT NULL,
  `preorder_pay_immediately` tinyint(1) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16088 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_logs`
--

DROP TABLE IF EXISTS `page_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `note` varchar(32) DEFAULT NULL,
  `view_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=218818 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_modules`
--

DROP TABLE IF EXISTS `payment_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `key_name` varchar(64) DEFAULT NULL,
  `checkout_option_text` varchar(128) DEFAULT NULL,
  `checkout_option_text_extra` varchar(128) DEFAULT NULL,
  `order_on_checkout_page` smallint(6) DEFAULT NULL,
  `payment_icon` varchar(128) DEFAULT NULL,
  `forwarding_page_text` varchar(255) DEFAULT NULL,
  `module_specific_payment_function` varchar(128) DEFAULT NULL,
  `https` tinyint(1) DEFAULT NULL,
  `class_filename` varchar(128) DEFAULT NULL,
  `class_name` varchar(64) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `restricted_to_user_types` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_raw_data_to_orders`
--

DROP TABLE IF EXISTS `payment_raw_data_to_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_raw_data_to_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_method` varchar(16) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paypal_config`
--

DROP TABLE IF EXISTS `paypal_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT NULL,
  `API_username` varchar(128) DEFAULT NULL,
  `api_password` varchar(128) DEFAULT NULL,
  `api_signature` varchar(64) DEFAULT NULL,
  `currency` varchar(16) DEFAULT NULL,
  `success_url` varchar(128) DEFAULT NULL,
  `error_url` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paypal_transaction_details`
--

DROP TABLE IF EXISTS `paypal_transaction_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_transaction_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `internal_sale_id` int(11) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `timestamp` varchar(64) DEFAULT NULL,
  `correlation_id` varchar(64) DEFAULT NULL,
  `ack` varchar(64) DEFAULT NULL,
  `version` varchar(64) DEFAULT NULL,
  `build` varchar(64) DEFAULT NULL,
  `transaction_id` varchar(64) DEFAULT NULL,
  `transaction_type` varchar(64) DEFAULT NULL,
  `payment_type` varchar(64) DEFAULT NULL,
  `order_time` varchar(64) DEFAULT NULL,
  `amount` varchar(64) DEFAULT NULL,
  `tax_amount` varchar(64) DEFAULT NULL,
  `currency_code` varchar(8) DEFAULT NULL,
  `payment_status` varchar(32) DEFAULT NULL,
  `pending_reason` varchar(32) DEFAULT NULL,
  `reason_code` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5594 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pcosts2`
--

DROP TABLE IF EXISTS `pcosts2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcosts2` (
  `id` int(11) NOT NULL DEFAULT '0',
  `product` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cost` decimal(5,2) DEFAULT NULL,
  `cost_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tablename` varchar(64) DEFAULT NULL,
  `permission_type` varchar(16) DEFAULT NULL,
  `setting` varchar(32) DEFAULT NULL,
  `operator` varchar(7) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=179 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `php_code`
--

DROP TABLE IF EXISTS `php_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `php_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `code` text,
  `filename` varchar(64) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(32) DEFAULT NULL,
  `description` varchar(512) NOT NULL DEFAULT '',
  `database_tables` varchar(512) DEFAULT NULL,
  `plugin_directory` varchar(64) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `code_link_front` varchar(64) DEFAULT NULL,
  `code_link_back` varchar(64) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preorder_bot_report`
--

DROP TABLE IF EXISTS `preorder_bot_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preorder_bot_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `action_taken` varchar(128) DEFAULT NULL,
  `action_status` tinyint(1) DEFAULT NULL,
  `reported` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16020 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `press_releases`
--

DROP TABLE IF EXISTS `press_releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `press_releases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `summary` varchar(512) DEFAULT NULL,
  `archive_date` date DEFAULT NULL,
  `article_text` text,
  `relates_to_label` int(11) DEFAULT NULL,
  `relates_to_artist` int(11) DEFAULT NULL,
  `relates_to_product` int(11) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `preview_image` varchar(128) DEFAULT NULL,
  `youtube_video_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2343 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `price_formats`
--

DROP TABLE IF EXISTS `price_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `format_name` varchar(32) DEFAULT NULL,
  `pdp` decimal(5,2) DEFAULT NULL,
  `web_price` decimal(5,2) DEFAULT NULL,
  `usd_price` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `price_lookup_table`
--

DROP TABLE IF EXISTS `price_lookup_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_lookup_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pricing_format` varchar(16) DEFAULT NULL,
  `pdp` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prices`
--

DROP TABLE IF EXISTS `prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prices` (
  `PriceID` int(5) NOT NULL AUTO_INCREMENT,
  `DealerPrice` decimal(6,2) NOT NULL DEFAULT '0.00',
  `RetailPrice` decimal(6,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`PriceID`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_attribute_action_options`
--

DROP TABLE IF EXISTS `product_attribute_action_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_action_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_attribute_options`
--

DROP TABLE IF EXISTS `product_attribute_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute` int(11) DEFAULT NULL,
  `attribute_option` varchar(128) DEFAULT NULL,
  `attribute_action` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_attributes`
--

DROP TABLE IF EXISTS `product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(128) DEFAULT NULL,
  `field_type` varchar(64) DEFAULT NULL,
  `field_width` int(11) DEFAULT NULL,
  `append_field_with` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_costs`
--

DROP TABLE IF EXISTS `product_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_costs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cost` decimal(6,2) DEFAULT NULL,
  `cost_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_data`
--

DROP TABLE IF EXISTS `product_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_data` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ver` varchar(9) COLLATE latin1_general_ci NOT NULL DEFAULT '1.00.00',
  `template` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `product_type` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `product_subtype` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `category_name` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `subcategory_name` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `min_order_qty` int(11) NOT NULL DEFAULT '0',
  `max_order_qty` int(11) NOT NULL DEFAULT '0',
  `code` varchar(15) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `title` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `short_description` text COLLATE latin1_general_ci NOT NULL,
  `full_description` text COLLATE latin1_general_ci NOT NULL,
  `thumbnail_name` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `thumbnail_size` int(11) NOT NULL DEFAULT '0',
  `thumbnail_width` int(11) NOT NULL DEFAULT '0',
  `thumbnail_height` int(11) NOT NULL DEFAULT '0',
  `mainpic_name` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `mainpic_size` int(11) NOT NULL DEFAULT '0',
  `mainpic_width` int(11) NOT NULL DEFAULT '0',
  `mainpic_height` int(11) NOT NULL DEFAULT '0',
  `net_price` double NOT NULL DEFAULT '0',
  `sales_taxable_percentage` double NOT NULL DEFAULT '100',
  `weight` double NOT NULL DEFAULT '0',
  `hidden` int(11) NOT NULL DEFAULT '0',
  `available` int(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stock_control` int(11) NOT NULL DEFAULT '0',
  `stock_level` int(11) NOT NULL DEFAULT '1',
  `attr_code` varchar(8) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `attr_desc_1` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `attr_value_1` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `attr_desc_2` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `attr_value_2` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `disp_pref_list` int(11) NOT NULL DEFAULT '0',
  `associated_file_list` text COLLATE latin1_general_ci NOT NULL,
  `stationary_override_option` int(11) NOT NULL DEFAULT '0',
  `new_resource` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1263 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_descriptions`
--

DROP TABLE IF EXISTS `product_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_number` varchar(32) DEFAULT NULL,
  `description` text,
  `sales_point_1` varchar(512) DEFAULT NULL,
  `sales_point_2` varchar(512) DEFAULT NULL,
  `sales_point_3` varchar(512) DEFAULT NULL,
  `sales_point_4` varchar(512) DEFAULT NULL,
  `sales_point_5` varchar(512) DEFAULT NULL,
  `sales_point_6` varchar(255) DEFAULT NULL,
  `sales_point_7` varchar(255) DEFAULT NULL,
  `sales_point_8` varchar(255) DEFAULT NULL,
  `sales_point_9` varchar(255) DEFAULT NULL,
  `sales_point_10` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_formats`
--

DROP TABLE IF EXISTS `product_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `format` varchar(16) DEFAULT NULL,
  `shipping_units` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_licencees`
--

DROP TABLE IF EXISTS `product_licencees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_licencees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` int(11) DEFAULT NULL,
  `licencee` int(11) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1619 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relates_to_product` int(11) DEFAULT NULL,
  `title` varchar(256) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `web_link` varchar(256) DEFAULT NULL,
  `review_text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=574 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_tracklist_separates`
--

DROP TABLE IF EXISTS `product_tracklist_separates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_tracklist_separates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `track` varchar(512) DEFAULT NULL,
  `cat_no` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24255 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_tracklists`
--

DROP TABLE IF EXISTS `product_tracklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_tracklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` int(11) DEFAULT NULL,
  `tracklist` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1531 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_tracklists_pre_html`
--

DROP TABLE IF EXISTS `product_tracklists_pre_html`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_tracklists_pre_html` (
  `id` int(11) NOT NULL DEFAULT '0',
  `product` int(11) DEFAULT NULL,
  `tracklist` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_types`
--

DROP TABLE IF EXISTS `product_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type` varchar(64) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `icon` varchar(64) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` int(11) DEFAULT NULL,
  `category_name` int(11) DEFAULT NULL,
  `min_order_qty` int(11) NOT NULL DEFAULT '0',
  `max_order_qty` int(11) NOT NULL DEFAULT '0',
  `catalogue_number` varchar(32) DEFAULT NULL,
  `barcode` varchar(16) DEFAULT NULL,
  `artist` int(11) DEFAULT NULL,
  `title` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `format` varchar(32) DEFAULT NULL,
  `genre` int(11) DEFAULT NULL,
  `full_description` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `image` varchar(128) DEFAULT NULL,
  `pdp` decimal(5,2) DEFAULT NULL,
  `price_format` varchar(32) DEFAULT NULL,
  `price` decimal(5,2) DEFAULT NULL,
  `previous_price` decimal(5,2) DEFAULT NULL,
  `previous_price_dollars` decimal(5,2) DEFAULT NULL,
  `price_in_dollars` decimal(5,2) DEFAULT NULL,
  `stock_quantity` varchar(16) DEFAULT NULL,
  `in_stock` tinyint(1) DEFAULT NULL,
  `quantity_in_stock` int(11) DEFAULT NULL,
  `special_web_price` decimal(5,2) DEFAULT NULL,
  `flash_sale_price` decimal(5,2) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT NULL,
  `hidden_on_trade_site` tinyint(1) DEFAULT NULL,
  `hidden_on_eu` tinyint(1) DEFAULT NULL,
  `hidden_on_us` tinyint(1) DEFAULT NULL,
  `available` tinyint(1) DEFAULT NULL,
  `available_for_pre_order` tinyint(1) DEFAULT NULL,
  `deleted` date DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `us_release_date` date DEFAULT NULL,
  `date_available_on_gonzo` date DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_in_stock` datetime DEFAULT NULL,
  `associated_file_list` varchar(512) DEFAULT NULL,
  `label` int(11) DEFAULT NULL,
  `youtube_embed_code` varchar(512) DEFAULT NULL,
  `youtube_id` varchar(16) DEFAULT NULL,
  `web_site_exclusive` tinyint(1) DEFAULT NULL,
  `cost_database` tinyint(1) DEFAULT NULL,
  `origination_costs_charged` tinyint(1) DEFAULT NULL,
  `origination_costs_to_be_recharged` int(11) DEFAULT NULL,
  `finished_stock` tinyint(1) DEFAULT NULL,
  `finished_stock_usa` tinyint(1) DEFAULT NULL,
  `parts_supplied` tinyint(1) DEFAULT NULL,
  `deal_type` varchar(128) DEFAULT NULL,
  `reporting_frequency` int(11) DEFAULT NULL,
  `report_to` varchar(16) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_term` int(11) DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `advance_paid` tinyint(1) DEFAULT NULL,
  `advance_amount` decimal(5,2) DEFAULT NULL,
  `advance_amount_currency` varchar(4) DEFAULT NULL,
  `fixed_rate_per_unit` tinyint(1) DEFAULT NULL,
  `fixed_rate_per_unit_full_price` decimal(5,2) DEFAULT NULL,
  `fixed_rate_per_unit_full_price_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_per_territory` tinyint(1) DEFAULT NULL,
  `fixed_rate_uk` decimal(5,2) DEFAULT NULL,
  `fixed_rate_uk_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_europe` decimal(5,2) DEFAULT NULL,
  `fixed_rate_europe_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_usa` decimal(5,2) DEFAULT NULL,
  `fixed_rate_usa_percentage` decimal(5,2) DEFAULT NULL,
  `fixed_rate_usa_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_far_east` decimal(5,2) DEFAULT NULL,
  `fixed_rate_far_east_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_mailorder_sales` decimal(5,2) DEFAULT NULL,
  `fixed_rate_mailorder_sales_currency` varchar(4) DEFAULT NULL,
  `profit_share` decimal(5,2) DEFAULT NULL,
  `commission` decimal(5,2) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `percentage_of_dealer_price` decimal(5,2) DEFAULT NULL,
  `percentage_of_wholesale_price` decimal(5,2) DEFAULT NULL,
  `bought_stock_rate` decimal(5,2) DEFAULT NULL,
  `bought_stock_rate_currency` varchar(4) DEFAULT NULL,
  `balance` decimal(5,2) DEFAULT NULL,
  `balance_currency` varchar(4) DEFAULT NULL,
  `publishing_paid_direct` tinyint(1) DEFAULT NULL,
  `direct_publishing_rate` decimal(5,2) DEFAULT NULL,
  `notes_on_accounting` varchar(512) DEFAULT NULL,
  `current_retentions_balance` decimal(5,2) DEFAULT NULL,
  `current_retentions_balance_currency` varchar(4) DEFAULT NULL,
  `retention_held` tinyint(1) DEFAULT NULL,
  `retention` decimal(5,2) DEFAULT NULL,
  `retention_period` int(11) DEFAULT NULL,
  `notes_on_retentions` varchar(512) DEFAULT NULL,
  `fixed_rate_per_unit_mid_price` decimal(5,2) DEFAULT NULL,
  `fixed_rate_per_unit_mid_price_currency` varchar(8) DEFAULT NULL,
  `e_rights` tinyint(1) DEFAULT NULL,
  `e_royalty_type` int(11) DEFAULT NULL,
  `e_royalty_rate` decimal(5,2) DEFAULT NULL,
  `sub_licence_rights` varchar(16) DEFAULT NULL,
  `mcps` tinyint(1) DEFAULT NULL,
  `mcps_scheme` varchar(64) DEFAULT NULL,
  `mcps_number` varchar(64) DEFAULT NULL,
  `territory` varchar(32) DEFAULT NULL,
  `vp_press_mailout` tinyint(1) DEFAULT NULL,
  `mailout_date` date DEFAULT NULL,
  `press_mailout_recharged` tinyint(1) DEFAULT NULL,
  `press_mailout_recharged_date` date DEFAULT NULL,
  `sub_licence_rate` decimal(5,2) DEFAULT NULL,
  `sublicencing_royalty_rate` decimal(5,2) DEFAULT NULL,
  `uk_distributor` int(11) DEFAULT NULL,
  `us_distributor` int(11) DEFAULT NULL,
  `us_list_price` decimal(5,2) DEFAULT NULL,
  `usd_no` varchar(32) DEFAULT NULL,
  `musea_ref` varchar(64) DEFAULT NULL,
  `priority` varchar(16) DEFAULT NULL,
  `mastering_done_by` varchar(64) DEFAULT NULL,
  `mastering_budget` decimal(5,2) DEFAULT NULL,
  `deadline_for_receiving_master` date DEFAULT NULL,
  `master_received` tinyint(1) DEFAULT NULL,
  `master_received_date` date DEFAULT NULL,
  `artwork_done_by` varchar(64) DEFAULT NULL,
  `artwork_budget` decimal(5,2) DEFAULT NULL,
  `artwork_deadline` date DEFAULT NULL,
  `artwork_received` tinyint(1) DEFAULT NULL,
  `artwork_received_date` date DEFAULT NULL,
  `dvd_region` varchar(32) DEFAULT NULL,
  `dvd_screen` varchar(32) DEFAULT NULL,
  `dvd_sound` varchar(32) DEFAULT NULL,
  `dvd_classification` varchar(32) DEFAULT NULL,
  `dvd_running_time` varchar(32) DEFAULT NULL,
  `special_instructions` text,
  `stock_to_artist_price` decimal(5,2) DEFAULT NULL,
  `stock_to_artist_price_usa` decimal(5,2) DEFAULT NULL,
  `stock_to_artist_free_copies_sent` tinyint(1) DEFAULT NULL,
  `stock_to_artist_free_copies` smallint(5) unsigned DEFAULT NULL,
  `is_download` tinyint(1) DEFAULT NULL,
  `transfer_to_gonzo` date DEFAULT NULL,
  `orig` tinyint(1) DEFAULT NULL,
  `allow_pre_orders` tinyint(1) DEFAULT NULL,
  `artist_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=15820 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_to_product_attributes`
--

DROP TABLE IF EXISTS `products_to_product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_to_product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `attribute_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_to_web_portals`
--

DROP TABLE IF EXISTS `products_to_web_portals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_to_web_portals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `portal_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=528 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qcalendar`
--

DROP TABLE IF EXISTS `qcalendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qcalendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` varchar(2) NOT NULL,
  `month` varchar(2) NOT NULL,
  `year` varchar(4) NOT NULL,
  `link` enum('none','url','div') DEFAULT 'none',
  `url` text,
  `hr` tinyint(2) NOT NULL DEFAULT '0',
  `min` tinyint(2) NOT NULL DEFAULT '0',
  `category_id` int(11) DEFAULT NULL,
  `email_alert` tinyint(1) NOT NULL DEFAULT '0',
  `sms_alert` tinyint(1) NOT NULL DEFAULT '0',
  `cron_email` varchar(255) NOT NULL,
  `cron_sms_number` varchar(255) DEFAULT NULL,
  `short_desc` text NOT NULL,
  `long_desc` text NOT NULL,
  `short_desc_image` text,
  `long_desc_image` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `qcalendar_category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qcalendar_category`
--

DROP TABLE IF EXISTS `qcalendar_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qcalendar_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_desc` text NOT NULL,
  `long_desc` text NOT NULL,
  `short_desc_image` text NOT NULL,
  `long_desc_image` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queries`
--

DROP TABLE IF EXISTS `queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query_name` varchar(64) DEFAULT NULL,
  `query` varchar(2000) DEFAULT NULL,
  `base_table_to_edit` varchar(128) DEFAULT NULL,
  `default_filter` int(11) DEFAULT NULL,
  `description` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radio_sections`
--

DROP TABLE IF EXISTS `radio_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radio_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radio_shows_to_sections`
--

DROP TABLE IF EXISTS `radio_shows_to_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radio_shows_to_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radio_show` int(11) DEFAULT NULL,
  `section` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registered_filters`
--

DROP TABLE IF EXISTS `registered_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registered_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tablename` varchar(64) DEFAULT NULL,
  `filter_id` int(11) DEFAULT NULL,
  `limit_interface_types_to` varchar(32) DEFAULT NULL,
  `site_or_admin` varchar(32) DEFAULT 'apply_to_both',
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registered_modules`
--

DROP TABLE IF EXISTS `registered_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registered_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(32) DEFAULT NULL,
  `module_filename` varchar(32) DEFAULT NULL,
  `module_permissions` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registration_requests`
--

DROP TABLE IF EXISTS `registration_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `processed` int(11) NOT NULL DEFAULT '0',
  `username` varchar(64) COLLATE latin1_general_ci DEFAULT NULL,
  `first_name` varchar(64) COLLATE latin1_general_ci DEFAULT NULL,
  `second_name` varchar(64) COLLATE latin1_general_ci DEFAULT NULL,
  `job_title` varchar(64) COLLATE latin1_general_ci DEFAULT NULL,
  `organisation` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `officename` int(11) DEFAULT NULL,
  `addr1` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `addr2` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `addr3` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `town` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `county` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `postcode` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `country` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `telephone` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `direct_telephone` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `mobile` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fax` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `date_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4588 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sagepay_configuration`
--

DROP TABLE IF EXISTS `sagepay_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sagepay_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FQDN` varchar(64) DEFAULT NULL,
  `server` varchar(16) DEFAULT NULL,
  `vendor_name` varchar(32) DEFAULT NULL,
  `simulator_vendor_name` varchar(32) DEFAULT NULL,
  `currency` varchar(8) DEFAULT NULL,
  `transaction_type` varchar(16) DEFAULT NULL,
  `partner_id` varchar(16) DEFAULT NULL,
  `protocol` float DEFAULT NULL,
  `completion_url` varchar(64) DEFAULT NULL,
  `failure_url` varchar(64) DEFAULT NULL,
  `not_authed_message` int(11) DEFAULT NULL,
  `malformed_message` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sagepay_responses`
--

DROP TABLE IF EXISTS `sagepay_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sagepay_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `VendorTxCode` varchar(50) NOT NULL DEFAULT '',
  `TxType` varchar(32) NOT NULL DEFAULT '',
  `Amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Currency` varchar(3) NOT NULL DEFAULT '',
  `BillingFirstnames` varchar(20) DEFAULT NULL,
  `BillingSurname` varchar(20) DEFAULT NULL,
  `BillingAddress1` varchar(100) DEFAULT NULL,
  `BillingAddress2` varchar(100) DEFAULT NULL,
  `BillingCity` varchar(40) DEFAULT NULL,
  `BillingPostCode` varchar(10) DEFAULT NULL,
  `BillingCountry` varchar(2) DEFAULT NULL,
  `BillingState` varchar(2) DEFAULT NULL,
  `BillingPhone` varchar(20) DEFAULT NULL,
  `DeliveryFirstnames` varchar(20) DEFAULT NULL,
  `DeliverySurname` varchar(20) DEFAULT NULL,
  `DeliveryAddress1` varchar(100) DEFAULT NULL,
  `DeliveryAddress2` varchar(100) DEFAULT NULL,
  `DeliveryCity` varchar(40) DEFAULT NULL,
  `DeliveryPostCode` varchar(10) DEFAULT NULL,
  `DeliveryCountry` varchar(2) DEFAULT NULL,
  `DeliveryState` varchar(2) DEFAULT NULL,
  `DeliveryPhone` varchar(20) DEFAULT NULL,
  `CustomerEMail` varchar(100) DEFAULT NULL,
  `VPSTxId` varchar(64) DEFAULT NULL,
  `SecurityKey` varchar(10) DEFAULT NULL,
  `TxAuthNo` bigint(20) NOT NULL DEFAULT '0',
  `AVSCV2` varchar(50) DEFAULT NULL,
  `AddressResult` varchar(20) DEFAULT NULL,
  `PostCodeResult` varchar(20) DEFAULT NULL,
  `CV2Result` varchar(20) DEFAULT NULL,
  `GiftAid` tinyint(4) DEFAULT NULL,
  `ThreeDSecureStatus` varchar(50) DEFAULT NULL,
  `CAVV` varchar(40) DEFAULT NULL,
  `RelatedVendorTxCode` varchar(50) DEFAULT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `AddressStatus` varchar(20) DEFAULT NULL,
  `PayerStatus` varchar(20) DEFAULT NULL,
  `CardType` varchar(15) DEFAULT NULL,
  `PayPalPayerID` varchar(15) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_post_string` text,
  `log_point` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8508 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `select_lists`
--

DROP TABLE IF EXISTS `select_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `select_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) DEFAULT NULL,
  `field_name` varchar(64) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setup_variables`
--

DROP TABLE IF EXISTS `setup_variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setup_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(64) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping`
--

DROP TABLE IF EXISTS `shipping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `apply_sales_tax1` char(1) COLLATE latin1_general_ci NOT NULL DEFAULT 'Y',
  `handling_charge_applied` int(11) NOT NULL DEFAULT '0',
  `handling_charge_value` double NOT NULL DEFAULT '0',
  `shipping_weight_applied` int(11) NOT NULL DEFAULT '0',
  `shipping_charge_value` double NOT NULL DEFAULT '0',
  `enabled` int(11) NOT NULL DEFAULT '0',
  `default_zone` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_rate_per_quantity_by_zone`
--

DROP TABLE IF EXISTS `shipping_rate_per_quantity_by_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_rate_per_quantity_by_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` int(11) DEFAULT NULL,
  `initial_item_rate` decimal(5,2) DEFAULT NULL,
  `additional_item_rate` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_rate_per_quantity_by_zone_usa`
--

DROP TABLE IF EXISTS `shipping_rate_per_quantity_by_zone_usa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_rate_per_quantity_by_zone_usa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` int(11) DEFAULT NULL,
  `initial_item_rate` decimal(5,2) DEFAULT NULL,
  `additional_item_rate` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_rates_per_product`
--

DROP TABLE IF EXISTS `shipping_rates_per_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_rates_per_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` int(11) DEFAULT NULL,
  `dependency_field` varchar(32) DEFAULT NULL,
  `dependency_operator` varchar(8) DEFAULT NULL,
  `dependency_value` varchar(64) DEFAULT NULL,
  `zone` int(11) DEFAULT NULL,
  `initial_item_rate` decimal(5,2) DEFAULT NULL,
  `additional_item_rate` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_rates_per_product_usa`
--

DROP TABLE IF EXISTS `shipping_rates_per_product_usa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_rates_per_product_usa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` int(11) DEFAULT NULL,
  `dependency_field` varchar(32) DEFAULT NULL,
  `dependency_operator` varchar(8) DEFAULT NULL,
  `dependency_value` varchar(64) DEFAULT NULL,
  `zone` int(11) DEFAULT NULL,
  `initial_item_rate` decimal(5,2) DEFAULT NULL,
  `additional_item_rate` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_weight_bands`
--

DROP TABLE IF EXISTS `shipping_weight_bands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_weight_bands` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) NOT NULL DEFAULT '0',
  `net_price` double NOT NULL DEFAULT '0',
  `weight` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=142 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_zones`
--

DROP TABLE IF EXISTS `shipping_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopping_cart_configuration`
--

DROP TABLE IF EXISTS `shopping_cart_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopping_cart_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `cart_default_web_site` int(11) DEFAULT NULL,
  `cart_template` int(11) DEFAULT NULL,
  `mail_orders_from_name` varchar(64) DEFAULT NULL,
  `mail_orders_from_address` varchar(64) DEFAULT NULL,
  `mail_orders_to_admin` tinyint(1) DEFAULT NULL,
  `mail_orders_to` varchar(64) DEFAULT NULL,
  `mail_orders_subject` varchar(64) DEFAULT NULL,
  `mail_order_to_customer` tinyint(1) DEFAULT NULL,
  `customer_email_subject` varchar(64) DEFAULT NULL,
  `email_confirmation_template` int(11) DEFAULT NULL,
  `customer_email_confirmation_template` int(11) DEFAULT NULL,
  `send_preliminary_order_notifications` tinyint(1) DEFAULT NULL,
  `preliminary_notification_template` int(11) DEFAULT NULL,
  `preliminary_notification_subject` varchar(64) DEFAULT NULL,
  `email_user_data_template` int(11) DEFAULT NULL,
  `default_currency_symbol` varchar(8) DEFAULT NULL,
  `shipping_modules_installed` varchar(128) DEFAULT NULL,
  `payment_modules_installed` varchar(128) DEFAULT NULL,
  `products_table` varchar(64) DEFAULT NULL,
  `categories_table` varchar(64) DEFAULT NULL,
  `allow_multiple_quantities` tinyint(1) DEFAULT NULL,
  `product_details_page` varchar(128) DEFAULT NULL,
  `category_list_page` varchar(128) DEFAULT NULL,
  `base_category_list_page` int(11) DEFAULT NULL,
  `inner_category_list_page` int(11) DEFAULT NULL,
  `products_list_page` varchar(128) DEFAULT NULL,
  `order_more_links_to` varchar(128) DEFAULT NULL,
  `buy_requires_login` tinyint(1) DEFAULT NULL,
  `no_login_user_details_form_filter` int(11) DEFAULT NULL,
  `golden_account_active` tinyint(1) DEFAULT NULL,
  `product_title_fields` varchar(128) DEFAULT NULL,
  `price_field` varchar(64) DEFAULT NULL,
  `calculate_price_field_by_function` varchar(128) DEFAULT NULL,
  `user_details_confirm_template` int(11) DEFAULT NULL,
  `check_stock_at_checkout` tinyint(1) DEFAULT NULL,
  `external_stock_check_function` varchar(255) DEFAULT NULL,
  `external_order_post_function` varchar(255) DEFAULT NULL,
  `run_code_on_place_order_success` varchar(255) DEFAULT NULL,
  `hide_product_attributes_in_cart` tinyint(1) DEFAULT NULL,
  `email_when_back_in_stock` tinyint(1) DEFAULT NULL,
  `source_identifier` varchar(16) DEFAULT NULL,
  `approve_terms` tinyint(1) DEFAULT NULL,
  `default_cart` tinyint(1) DEFAULT NULL,
  `record_payments_separately` tinyint(1) DEFAULT NULL,
  `customer_preorder_email_confirmation_template` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopping_cart_mailinglist_config`
--

DROP TABLE IF EXISTS `shopping_cart_mailinglist_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopping_cart_mailinglist_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_as_category` varchar(64) DEFAULT NULL,
  `filter` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopping_cart_mailinglist_data`
--

DROP TABLE IF EXISTS `shopping_cart_mailinglist_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopping_cart_mailinglist_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_as_category_fieldname` varchar(128) DEFAULT NULL,
  `field_as_category_value` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `time_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9276 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stationery_category_costs`
--

DROP TABLE IF EXISTS `stationery_category_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stationery_category_costs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `in_cost` float DEFAULT NULL,
  `out_cost` float DEFAULT NULL,
  `extra_unit_cost` float DEFAULT NULL,
  `percentage_for_delivery` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='product status options';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_statuses`
--

DROP TABLE IF EXISTS `stock_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT NULL,
  `status_text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `style_sheet_data`
--

DROP TABLE IF EXISTS `style_sheet_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `style_sheet_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `style_sheet_id` int(11) DEFAULT NULL,
  `stylesheet` text,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `style_sheets`
--

DROP TABLE IF EXISTS `style_sheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `style_sheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  `style_sheet` text,
  `filename` varchar(32) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sys_form_log`
--

DROP TABLE IF EXISTS `sys_form_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_form_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(16) DEFAULT NULL,
  `uuid` varchar(128) DEFAULT NULL,
  `gen_time` datetime DEFAULT NULL,
  `update_table` varchar(64) DEFAULT NULL,
  `row_identifier` varchar(64) DEFAULT NULL,
  `form_type` varchar(16) DEFAULT NULL,
  `filter` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `user_session` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=96585202 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table_meta`
--

DROP TABLE IF EXISTS `table_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  `configuration_section` int(11) DEFAULT NULL,
  `notes` varchar(128) DEFAULT NULL,
  `system_dump_param` varchar(16) DEFAULT NULL,
  `system_dump_ids` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table_options`
--

DROP TABLE IF EXISTS `table_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(32) NOT NULL,
  `table_option` varchar(128) NOT NULL,
  `option_value` varchar(128) DEFAULT NULL,
  `system` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table_relations`
--

DROP TABLE IF EXISTS `table_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_1` varchar(32) DEFAULT NULL,
  `field_in_table_1` varchar(32) DEFAULT NULL,
  `table_2` varchar(32) DEFAULT NULL,
  `field_in_table_2` varchar(32) DEFAULT NULL,
  `relationship` varchar(64) DEFAULT NULL,
  `many_to_many_link_table` varchar(64) DEFAULT NULL,
  `master_table_name_field` varchar(64) DEFAULT NULL,
  `hide_from_system_lists` tinyint(1) DEFAULT NULL,
  `system_graphic` varchar(64) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template_defaults`
--

DROP TABLE IF EXISTS `template_defaults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_defaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template_registry`
--

DROP TABLE IF EXISTS `template_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interface` varchar(64) DEFAULT NULL,
  `template` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dbf_key_name` varchar(32) DEFAULT NULL,
  `template_name` varchar(64) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `template_description` varchar(255) DEFAULT NULL,
  `template` text,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2035 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territores`
--

DROP TABLE IF EXISTS `territores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `territores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `territory` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territories`
--

DROP TABLE IF EXISTS `territories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `territories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `territory` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territories_blank`
--

DROP TABLE IF EXISTS `territories_blank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `territories_blank` (
  `ID` int(11) NOT NULL DEFAULT '0',
  `external_id` int(11) DEFAULT NULL,
  `category_name` int(11) DEFAULT NULL,
  `min_order_qty` int(11) NOT NULL DEFAULT '0',
  `max_order_qty` int(11) NOT NULL DEFAULT '0',
  `catalogue_number` varchar(32) DEFAULT NULL,
  `barcode` varchar(16) DEFAULT NULL,
  `artist` int(11) DEFAULT NULL,
  `title` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `format` varchar(32) DEFAULT NULL,
  `genre` int(11) DEFAULT NULL,
  `full_description` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `image` varchar(128) DEFAULT NULL,
  `pdp` decimal(5,2) DEFAULT NULL,
  `price_format` varchar(32) DEFAULT NULL,
  `price` decimal(5,2) DEFAULT NULL,
  `price_in_dollars` decimal(5,2) DEFAULT NULL,
  `stock_quantity` varchar(16) DEFAULT NULL,
  `in_stock` tinyint(1) DEFAULT NULL,
  `quantity_in_stock` int(11) DEFAULT NULL,
  `special_web_price` decimal(5,2) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT NULL,
  `hidden_on_eu` tinyint(1) DEFAULT NULL,
  `hidden_on_us` tinyint(1) DEFAULT NULL,
  `available` tinyint(1) DEFAULT NULL,
  `available_for_pre_order` tinyint(1) DEFAULT NULL,
  `deleted` date DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `us_release_date` date DEFAULT NULL,
  `date_available_on_gonzo` date DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `associated_file_list` varchar(512) DEFAULT NULL,
  `label` int(11) DEFAULT NULL,
  `youtube_embed_code` varchar(512) DEFAULT NULL,
  `youtube_id` varchar(16) DEFAULT NULL,
  `web_site_exclusive` tinyint(1) DEFAULT NULL,
  `cost_database` tinyint(1) DEFAULT NULL,
  `origination_costs_charged` tinyint(1) DEFAULT NULL,
  `origination_costs_to_be_recharged` int(11) DEFAULT NULL,
  `finished_stock` tinyint(1) DEFAULT NULL,
  `finished_stock_usa` tinyint(1) DEFAULT NULL,
  `parts_supplied` tinyint(1) DEFAULT NULL,
  `deal_type` varchar(128) DEFAULT NULL,
  `reporting_frequency` int(11) DEFAULT NULL,
  `report_to` varchar(16) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_term` int(11) DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `advance_paid` tinyint(1) DEFAULT NULL,
  `advance_amount` decimal(5,2) DEFAULT NULL,
  `advance_amount_currency` varchar(4) DEFAULT NULL,
  `fixed_rate_per_unit` tinyint(1) DEFAULT NULL,
  `fixed_rate_per_unit_full_price` decimal(5,2) DEFAULT NULL,
  `fixed_rate_per_unit_full_price_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_per_territory` tinyint(1) DEFAULT NULL,
  `fixed_rate_uk` decimal(5,2) DEFAULT NULL,
  `fixed_rate_uk_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_europe` decimal(5,2) DEFAULT NULL,
  `fixed_rate_europe_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_usa` decimal(5,2) DEFAULT NULL,
  `fixed_rate_usa_percentage` decimal(5,2) DEFAULT NULL,
  `fixed_rate_usa_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_far_east` decimal(5,2) DEFAULT NULL,
  `fixed_rate_far_east_currency` varchar(8) DEFAULT NULL,
  `fixed_rate_mailorder_sales` decimal(5,2) DEFAULT NULL,
  `fixed_rate_mailorder_sales_currency` varchar(4) DEFAULT NULL,
  `profit_share` decimal(5,2) DEFAULT NULL,
  `commission` decimal(5,2) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `percentage_of_dealer_price` decimal(5,2) DEFAULT NULL,
  `percentage_of_wholesale_price` decimal(5,2) DEFAULT NULL,
  `bought_stock_rate` decimal(5,2) DEFAULT NULL,
  `bought_stock_rate_currency` varchar(4) DEFAULT NULL,
  `balance` decimal(5,2) DEFAULT NULL,
  `balance_currency` varchar(4) DEFAULT NULL,
  `publishing_paid_direct` tinyint(1) DEFAULT NULL,
  `direct_publishing_rate` decimal(5,2) DEFAULT NULL,
  `notes_on_accounting` varchar(512) DEFAULT NULL,
  `current_retentions_balance` decimal(5,2) DEFAULT NULL,
  `current_retentions_balance_currency` varchar(4) DEFAULT NULL,
  `retention_held` tinyint(1) DEFAULT NULL,
  `retention` decimal(5,2) DEFAULT NULL,
  `retention_period` int(11) DEFAULT NULL,
  `notes_on_retentions` varchar(512) DEFAULT NULL,
  `fixed_rate_per_unit_mid_price` decimal(5,2) DEFAULT NULL,
  `fixed_rate_per_unit_mid_price_currency` varchar(8) DEFAULT NULL,
  `e_rights` tinyint(1) DEFAULT NULL,
  `e_royalty_type` int(11) DEFAULT NULL,
  `e_royalty_rate` decimal(5,2) DEFAULT NULL,
  `sub_licence_rights` varchar(16) DEFAULT NULL,
  `mcps` tinyint(1) DEFAULT NULL,
  `mcps_scheme` varchar(64) DEFAULT NULL,
  `mcps_number` varchar(64) DEFAULT NULL,
  `territory` varchar(32) DEFAULT NULL,
  `vp_press_mailout` tinyint(1) DEFAULT NULL,
  `mailout_date` date DEFAULT NULL,
  `press_mailout_recharged` tinyint(1) DEFAULT NULL,
  `press_mailout_recharged_date` date DEFAULT NULL,
  `sub_licence_rate` decimal(5,2) DEFAULT NULL,
  `sublicencing_royalty_rate` decimal(5,2) DEFAULT NULL,
  `uk_distributor` int(11) DEFAULT NULL,
  `us_distributor` int(11) DEFAULT NULL,
  `us_list_price` decimal(5,2) DEFAULT NULL,
  `usd_no` varchar(32) DEFAULT NULL,
  `musea_ref` varchar(64) DEFAULT NULL,
  `priority` varchar(16) DEFAULT NULL,
  `mastering_done_by` varchar(64) DEFAULT NULL,
  `mastering_budget` decimal(5,2) DEFAULT NULL,
  `deadline_for_receiving_master` date DEFAULT NULL,
  `master_received` tinyint(1) DEFAULT NULL,
  `master_received_date` date DEFAULT NULL,
  `artwork_done_by` varchar(64) DEFAULT NULL,
  `artwork_budget` decimal(5,2) DEFAULT NULL,
  `artwork_deadline` date DEFAULT NULL,
  `artwork_received` tinyint(1) DEFAULT NULL,
  `artwork_received_date` date DEFAULT NULL,
  `dvd_region` varchar(32) DEFAULT NULL,
  `dvd_screen` varchar(32) DEFAULT NULL,
  `dvd_sound` varchar(32) DEFAULT NULL,
  `dvd_classification` varchar(32) DEFAULT NULL,
  `dvd_running_time` varchar(32) DEFAULT NULL,
  `special_instructions` text,
  `stock_to_artist_price` decimal(5,2) DEFAULT NULL,
  `stock_to_artist_price_usa` decimal(5,2) DEFAULT NULL,
  `stock_to_artist_free_copies_sent` tinyint(1) DEFAULT NULL,
  `stock_to_artist_free_copies` smallint(5) unsigned DEFAULT NULL,
  `is_download` tinyint(1) DEFAULT NULL,
  `transfer_to_gonzo` date DEFAULT NULL,
  `orig` tinyint(1) DEFAULT NULL,
  `allow_pre_orders` tinyint(1) DEFAULT NULL,
  `artist_name` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test_releases`
--

DROP TABLE IF EXISTS `test_releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_releases` (
  `ReleaseID` int(5) NOT NULL AUTO_INCREMENT,
  `CatNumber` varchar(50) NOT NULL DEFAULT '',
  `Artist` varchar(50) NOT NULL DEFAULT '1',
  `ArtistIndex` varchar(50) NOT NULL DEFAULT '',
  `Label` varchar(50) NOT NULL DEFAULT '0',
  `Title` varchar(100) DEFAULT NULL,
  `DealerPrice` decimal(6,2) NOT NULL DEFAULT '0.00',
  `Barcode` varchar(20) DEFAULT NULL,
  `Territory` varchar(20) NOT NULL DEFAULT '0',
  `Genre` varchar(20) NOT NULL DEFAULT '0',
  `Format` varchar(20) NOT NULL DEFAULT '0',
  `DVD_region` char(3) NOT NULL DEFAULT '',
  `DVD_screen_ratio` varchar(10) NOT NULL DEFAULT '',
  `DVD_sound` varchar(10) NOT NULL DEFAULT '',
  `DVD_classification` varchar(5) NOT NULL DEFAULT '',
  `Status` char(1) NOT NULL DEFAULT 'A',
  `Information` text,
  `Sales_point_1` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_2` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_3` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_4` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_5` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_6` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_7` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_8` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_9` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_10` varchar(100) NOT NULL DEFAULT '',
  `Stock_level` int(5) NOT NULL DEFAULT '10',
  `Tags` varchar(100) NOT NULL DEFAULT '',
  `MCPS_ID` int(10) NOT NULL DEFAULT '0',
  `ReleaseDate` date DEFAULT NULL,
  `OfferID` int(5) DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReleaseID`),
  UNIQUE KEY `CatNumber_2` (`CatNumber`),
  KEY `names` (`Title`),
  KEY `CatNumber` (`CatNumber`),
  FULLTEXT KEY `Artist` (`Title`,`Artist`),
  FULLTEXT KEY `Search2006` (`Artist`,`Title`,`Label`,`Format`,`Tags`)
) ENGINE=MyISAM AUTO_INCREMENT=1861 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test_shipping_rates`
--

DROP TABLE IF EXISTS `test_shipping_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_shipping_rates` (
  `ID` int(5) NOT NULL AUTO_INCREMENT,
  `Territory` varchar(30) NOT NULL DEFAULT '',
  `Unit_price` decimal(6,2) NOT NULL DEFAULT '0.00',
  `Multiple_price` double(6,2) NOT NULL DEFAULT '0.00',
  `ProductCode` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ProductCode` (`ProductCode`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test_table_2`
--

DROP TABLE IF EXISTS `test_table_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_table_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_entry_field` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ticket_details`
--

DROP TABLE IF EXISTS `ticket_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ticket_text` text,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ticket_queries`
--

DROP TABLE IF EXISTS `ticket_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_queries` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `adminID` int(11) NOT NULL DEFAULT '0',
  `name` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `telephone` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `email_addr` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `enquiry` text COLLATE latin1_general_ci NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addr1` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `addr2` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `addr3` varchar(150) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `town` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `county` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `postcode` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `country` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(16) DEFAULT NULL,
  `last_updated_by` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tiki_lounge_mailing_list`
--

DROP TABLE IF EXISTS `tiki_lounge_mailing_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiki_lounge_mailing_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tis2008_releases`
--

DROP TABLE IF EXISTS `tis2008_releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tis2008_releases` (
  `ID` int(6) NOT NULL AUTO_INCREMENT COMMENT 'Test1234567890 Test1234567890 Test1234567890 Test1234567890 Test1234567890 Test1234567890 Test1234567890 Test1234567890 ',
  `CatNumber` varchar(50) NOT NULL DEFAULT '',
  `Artist` varchar(50) NOT NULL DEFAULT '1',
  `ArtistIndex` varchar(50) NOT NULL DEFAULT '',
  `Label` varchar(50) NOT NULL DEFAULT '0',
  `Title` varchar(100) DEFAULT NULL,
  `DealerPrice` decimal(6,2) NOT NULL DEFAULT '0.00',
  `RoyaltyRate` decimal(6,2) NOT NULL DEFAULT '4.00',
  `Barcode` varchar(20) DEFAULT NULL,
  `Territory` varchar(20) NOT NULL DEFAULT '0',
  `Genre` varchar(20) NOT NULL DEFAULT '0',
  `Format` varchar(20) NOT NULL DEFAULT '0',
  `DVD_region` char(3) NOT NULL DEFAULT '',
  `DVD_screen_ratio` varchar(10) NOT NULL DEFAULT '',
  `DVD_sound` varchar(10) NOT NULL DEFAULT '',
  `DVD_classification` varchar(5) NOT NULL DEFAULT '',
  `Status` char(1) NOT NULL DEFAULT 'A',
  `Information` text,
  `Sales_point_1` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_2` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_3` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_4` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_5` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_6` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_7` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_8` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_9` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_10` varchar(100) NOT NULL DEFAULT '',
  `Stock_level` int(5) NOT NULL DEFAULT '10',
  `Tags` varchar(100) NOT NULL DEFAULT '',
  `Media` varchar(100) NOT NULL DEFAULT '',
  `MCPS_ID` int(10) NOT NULL DEFAULT '0',
  `ReleaseDate` date DEFAULT NULL,
  `OfferID` int(5) DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `CatNumber_2` (`CatNumber`),
  KEY `names` (`Title`),
  KEY `Timestamp` (`Timestamp`),
  FULLTEXT KEY `Artist` (`Title`,`Artist`),
  FULLTEXT KEY `Search2006` (`Artist`,`Title`,`Label`,`Format`,`Tags`)
) ENGINE=MyISAM AUTO_INCREMENT=2101 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tis2008_tracks`
--

DROP TABLE IF EXISTS `tis2008_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tis2008_tracks` (
  `ID` int(6) NOT NULL AUTO_INCREMENT,
  `DiscNumber` int(2) NOT NULL DEFAULT '0',
  `Number` int(2) NOT NULL DEFAULT '0',
  `Name` text,
  `CatNumber__tis2008_releases__Cat` int(6) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `releases` (`CatNumber__tis2008_releases__Cat`),
  FULLTEXT KEY `Name` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=21807 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tis_releases`
--

DROP TABLE IF EXISTS `tis_releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tis_releases` (
  `ReleaseID` int(5) NOT NULL AUTO_INCREMENT,
  `CatNumber` varchar(50) NOT NULL DEFAULT '',
  `USDNumber` varchar(50) NOT NULL DEFAULT '',
  `MuseaNo` varchar(50) NOT NULL DEFAULT '',
  `Artist` varchar(50) NOT NULL DEFAULT '1',
  `ArtistIndex` varchar(50) NOT NULL DEFAULT '',
  `Label` varchar(50) NOT NULL DEFAULT '0',
  `Title` varchar(100) DEFAULT NULL,
  `DealerPrice` decimal(6,2) NOT NULL DEFAULT '0.00',
  `RoyaltyRate` decimal(6,2) NOT NULL DEFAULT '4.00',
  `Barcode` varchar(20) DEFAULT NULL,
  `Territory` varchar(20) NOT NULL DEFAULT '0',
  `Genre` varchar(20) NOT NULL DEFAULT '0',
  `Format` varchar(20) NOT NULL DEFAULT '0',
  `DVD_region` char(3) NOT NULL DEFAULT '',
  `DVD_screen_ratio` varchar(10) NOT NULL DEFAULT '',
  `DVD_sound` varchar(10) NOT NULL DEFAULT '',
  `DVD_classification` varchar(5) NOT NULL DEFAULT '',
  `Status` char(1) NOT NULL DEFAULT 'A',
  `Information` text,
  `Sales_point_1` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_2` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_3` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_4` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_5` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_6` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_7` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_8` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_9` varchar(100) NOT NULL DEFAULT '',
  `Sales_point_10` varchar(100) NOT NULL DEFAULT '',
  `Stock_level` int(5) NOT NULL DEFAULT '10',
  `Tags` varchar(100) NOT NULL DEFAULT '',
  `Media` varchar(100) NOT NULL DEFAULT '',
  `MCPS_ID` int(10) NOT NULL DEFAULT '0',
  `ReleaseDate` date DEFAULT NULL,
  `OfferID` int(5) DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReleaseID`),
  UNIQUE KEY `CatNumber_2` (`CatNumber`),
  KEY `names` (`Title`),
  KEY `Timestamp` (`Timestamp`),
  FULLTEXT KEY `Artist` (`Title`,`Artist`),
  FULLTEXT KEY `Search2006` (`Artist`,`Title`,`Label`,`Format`,`Tags`)
) ENGINE=MyISAM AUTO_INCREMENT=2433 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tis_releases_export`
--

DROP TABLE IF EXISTS `tis_releases_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tis_releases_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CatNumber` varchar(32) DEFAULT NULL,
  `Barcode` varchar(32) DEFAULT NULL,
  `Territory` varchar(32) DEFAULT NULL,
  `DVDR` varchar(32) DEFAULT NULL,
  `DVDSR` varchar(32) DEFAULT NULL,
  `DVDSound` varchar(32) DEFAULT NULL,
  `DVDClass` varchar(32) DEFAULT NULL,
  `Status` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ui_desktop_software`
--

DROP TABLE IF EXISTS `ui_desktop_software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ui_desktop_software` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(64) DEFAULT NULL,
  `anchor_id` varchar(64) DEFAULT NULL,
  `mui_function` varchar(255) DEFAULT NULL,
  `window_name` varchar(64) DEFAULT NULL,
  `loadMethod` varchar(16) DEFAULT NULL,
  `contentURL` varchar(128) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `window_x` int(11) DEFAULT NULL,
  `window_y` int(11) DEFAULT NULL,
  `default_for_all_admins` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `url_aliases`
--

DROP TABLE IF EXISTS `url_aliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(32) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  `url_group_ID` int(11) DEFAULT NULL,
  `virtual_query_string` varchar(255) DEFAULT NULL,
  `query_string_variable` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `us_state_codes`
--

DROP TABLE IF EXISTS `us_state_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `us_state_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `us_state_code` char(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) DEFAULT NULL,
  `email_address` varchar(50) DEFAULT NULL,
  `title` varchar(16) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `second_name` varchar(50) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `password_clear` varchar(16) DEFAULT NULL,
  `password_clear_date` datetime DEFAULT NULL,
  `address_1` varchar(128) DEFAULT NULL,
  `address_2` varchar(128) DEFAULT NULL,
  `address_3` varchar(128) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  `county_or_state` varchar(128) DEFAULT NULL,
  `us_billing_state` varchar(16) DEFAULT NULL,
  `zip_or_postal_code` varchar(32) DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `delivery_address_1` varchar(128) DEFAULT NULL,
  `delivery_address_2` varchar(128) DEFAULT NULL,
  `delivery_address_3` varchar(128) DEFAULT NULL,
  `delivery_city` varchar(64) DEFAULT NULL,
  `delivery_county_or_state` varchar(128) DEFAULT NULL,
  `us_delivery_state` varchar(16) DEFAULT NULL,
  `delivery_zip_or_postal_code` varchar(32) DEFAULT NULL,
  `delivery_country` int(11) DEFAULT NULL,
  `telephone_no` varchar(32) DEFAULT NULL,
  `mobile_no` varchar(32) DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `delete_user` tinyint(1) DEFAULT NULL,
  `same_as_billing_address` tinyint(1) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17101 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_config`
--

DROP TABLE IF EXISTS `user_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(64) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_desktop_icons`
--

DROP TABLE IF EXISTS `user_desktop_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_desktop_icons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `icon` int(11) DEFAULT NULL,
  `icon_visual` varchar(32) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_desktops`
--

DROP TABLE IF EXISTS `user_desktops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_desktops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `background_image` varchar(128) DEFAULT NULL,
  `admin_home_page` int(11) DEFAULT NULL,
  `admin_home_page_key` varchar(32) DEFAULT NULL,
  `theme` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_type_permission_types`
--

DROP TABLE IF EXISTS `user_type_permission_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_type_permission_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission` varchar(64) DEFAULT NULL,
  `permission_values` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_type_permissions`
--

DROP TABLE IF EXISTS `user_type_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_type_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` int(11) DEFAULT NULL,
  `permission` int(11) DEFAULT NULL,
  `permission_value` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_types`
--

DROP TABLE IF EXISTS `user_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(32) DEFAULT NULL,
  `user_type_description` text,
  `hierarchial_order` tinyint(4) DEFAULT NULL,
  `admin_access` tinyint(1) DEFAULT NULL,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `video_on_demand`
--

DROP TABLE IF EXISTS `video_on_demand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `video_on_demand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artist` int(11) DEFAULT NULL,
  `title` varchar(256) DEFAULT NULL,
  `link` varchar(256) DEFAULT NULL,
  `embed_code` text,
  `short_description` varchar(256) DEFAULT NULL,
  `description` text,
  `image` varchar(128) DEFAULT NULL,
  `running_time` time DEFAULT NULL,
  `date_uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `publish_date` date DEFAULT NULL,
  `available` tinyint(1) DEFAULT NULL,
  `in_stock` tinyint(1) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `video_on_demand_categories`
--

DROP TABLE IF EXISTS `video_on_demand_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `video_on_demand_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `video_on_demand_videos_to_categories`
--

DROP TABLE IF EXISTS `video_on_demand_videos_to_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `video_on_demand_videos_to_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vod_to_vod_sections`
--

DROP TABLE IF EXISTS `vod_to_vod_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vod_to_vod_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vod_id` int(11) DEFAULT NULL,
  `vod_section_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher_actions`
--

DROP TABLE IF EXISTS `voucher_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(64) DEFAULT NULL,
  `function_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher_codes`
--

DROP TABLE IF EXISTS `voucher_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_number` varchar(32) DEFAULT NULL,
  `voucher_action` int(11) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT NULL,
  `discount_amount` decimal(5,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `product_to_add` int(11) DEFAULT NULL,
  `apply_to_single_product_only` int(11) DEFAULT NULL,
  `apply_to_products_in_category` int(11) DEFAULT NULL,
  `restrict_to_user_type` int(11) DEFAULT NULL,
  `order_total_greater_than` decimal(5,2) DEFAULT NULL,
  `single_use_per_user` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher_codes_per_user`
--

DROP TABLE IF EXISTS `voucher_codes_per_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_codes_per_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_number` varchar(32) DEFAULT NULL,
  `voucher_action` int(11) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT NULL,
  `discount_amount` decimal(5,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `product_to_add` int(11) DEFAULT NULL,
  `apply_to_single_product_only` int(11) DEFAULT NULL,
  `apply_to_products_in_category` int(11) DEFAULT NULL,
  `restrict_to_user_type` int(11) DEFAULT NULL,
  `order_total_greater_than` decimal(5,2) DEFAULT NULL,
  `single_use_per_user` tinyint(1) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `voucher_used` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher_codes_used`
--

DROP TABLE IF EXISTS `voucher_codes_used`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_codes_used` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `voucher_code` varchar(32) DEFAULT NULL,
  `user_voucher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_articles`
--

DROP TABLE IF EXISTS `web_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_articles` (
  `ID` int(5) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL DEFAULT '',
  `HTML_body` text NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_artistnews`
--

DROP TABLE IF EXISTS `web_artistnews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_artistnews` (
  `NewsID` int(5) NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) DEFAULT NULL,
  `Body` text,
  `Photo` varchar(100) NOT NULL DEFAULT '',
  `Photo_filename` varchar(80) NOT NULL DEFAULT '',
  `ExpiryDate` date DEFAULT NULL,
  `EntryDate` date DEFAULT NULL,
  PRIMARY KEY (`NewsID`)
) ENGINE=MyISAM AUTO_INCREMENT=382 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_newsprint`
--

DROP TABLE IF EXISTS `web_newsprint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_newsprint` (
  `IssueID` int(5) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) NOT NULL DEFAULT '',
  `Description` varchar(100) NOT NULL DEFAULT '',
  `Cover_scan_filename` varchar(50) NOT NULL DEFAULT '',
  `PDF_filename` varchar(50) NOT NULL DEFAULT '',
  `Issue_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`IssueID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_photos`
--

DROP TABLE IF EXISTS `web_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_photos` (
  `PhotoID` int(5) NOT NULL AUTO_INCREMENT,
  `Gallery` varchar(50) NOT NULL DEFAULT '',
  `Filename` varchar(80) NOT NULL DEFAULT '',
  `Caption` varchar(200) NOT NULL DEFAULT '',
  `Credit` varchar(50) NOT NULL DEFAULT '',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PhotoID`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_portals`
--

DROP TABLE IF EXISTS `web_portals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_portals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `url` varchar(120) DEFAULT NULL,
  `currency` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_radio`
--

DROP TABLE IF EXISTS `web_radio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_radio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `image` varchar(64) DEFAULT NULL,
  `description` text,
  `real_audio_file` varchar(64) DEFAULT NULL,
  `mp3_file` varchar(120) DEFAULT NULL,
  `ogg_vorbis_file` varchar(120) DEFAULT NULL,
  `relates_to_artist` int(11) DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_date` date DEFAULT NULL,
  `radio_section` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=404 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_site_configuration_overrides`
--

DROP TABLE IF EXISTS `web_site_configuration_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_site_configuration_overrides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` int(11) DEFAULT NULL,
  `config_variable` varchar(64) DEFAULT NULL,
  `config_value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_site_data`
--

DROP TABLE IF EXISTS `web_site_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_site_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_site_id` int(11) DEFAULT NULL,
  `param` varchar(32) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_sites`
--

DROP TABLE IF EXISTS `web_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `default_site` tinyint(1) DEFAULT NULL,
  `system` tinyint(4) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dbf_key_name` varchar(32) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `widget` text,
  `system` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zz_testfileselect`
--

DROP TABLE IF EXISTS `zz_testfileselect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zz_testfileselect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filefield` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-31  0:03:44
