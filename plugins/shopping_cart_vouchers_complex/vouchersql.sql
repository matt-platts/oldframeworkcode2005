-- MySQL dump 10.11
--
-- Host: localhost    Database: medicodev2
-- ------------------------------------------------------
-- Server version	5.0.77

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `voucher_actions`
--

DROP TABLE IF EXISTS `voucher_actions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `voucher_actions` (
  `id` int(11) NOT NULL auto_increment,
  `action` varchar(64) default NULL,
  `function_name` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `voucher_actions`
--

LOCK TABLES `voucher_actions` WRITE;
/*!40000 ALTER TABLE `voucher_actions` DISABLE KEYS */;
INSERT INTO `voucher_actions` VALUES (1,'Free Shipping On Order','free_shipping_on_order'),(2,'XX DELETED XX',''),(3,'Discount Order By Percentage','discount_order_by_percentage'),(4,'Add Product To Order','add_product_to_order'),(5,'Discount Order By Set Amount','discount_order_by_set_amount');
/*!40000 ALTER TABLE `voucher_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher_codes`
--

DROP TABLE IF EXISTS `voucher_codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `voucher_codes` (
  `id` int(11) NOT NULL auto_increment,
  `voucher_number` varchar(32) default NULL,
  `voucher_action` int(11) default NULL,
  `description` varchar(512) default NULL,
  `discount_percentage` int(11) default NULL,
  `discount_amount` decimal(5,2) default NULL,
  `active` tinyint(1) default NULL,
  `valid_from` date default NULL,
  `valid_to` date default NULL,
  `product_to_add` int(11) default NULL,
  `apply_to_single_product_only` int(11) default NULL,
  `apply_to_products_in_category` int(11) default NULL,
  `restrict_to_user_type` int(11) default NULL,
  `order_total_greater_than` decimal(5,2) default NULL,
  `single_use_per_user` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `voucher_codes`
--

LOCK TABLES `voucher_codes` WRITE;
/*!40000 ALTER TABLE `voucher_codes` DISABLE KEYS */;
INSERT INTO `voucher_codes` VALUES (1,'SQUIRREL',3,'Remove 20% from order (products only)',20,'0.00',NULL,'2011-04-27','2012-10-09',NULL,0,0,0,NULL,NULL),(2,'ANT',5,'Removes £10 from the order',0,'10.00',1,'1999-04-01','2016-08-07',0,0,0,0,'0.00',0),(3,'DOG',1,'Gives free shipping',0,'0.00',NULL,'2000-02-01','2012-07-03',NULL,0,0,0,'0.00',0);
/*!40000 ALTER TABLE `voucher_codes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-06-15 21:15:29
