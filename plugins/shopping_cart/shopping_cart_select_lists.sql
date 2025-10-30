-- MySQL dump 10.10
--
-- Host: localhost    Database: jcmnew
-- ------------------------------------------------------
-- Server version	5.0.22

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
-- Table structure for table `select_lists`
--

DROP TABLE IF EXISTS `select_lists`;
CREATE TABLE `select_lists` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(32) default NULL,
  `field_name` varchar(32) default NULL,
  `item` varchar(128) default NULL,
  `system` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `select_lists`
--
-- WHERE:  id IN (55,56,57,65,66,68,71)


/*!40000 ALTER TABLE `select_lists` DISABLE KEYS */;
LOCK TABLES `select_lists` WRITE;
INSERT INTO `select_lists` (table_name,field_name,item,system) VALUES ('products','category_name','SQL:SELECT id,product_type from product_types',0),('products_to_product_attributes','product_id','SQL:SELECT id,title from products',0),('products_to_product_attributes','attribute_id','SQL:SELECT id,attribute_name from product_attributes',0),('order_products','product_id','SQL:SELECT id,title from products',0),('order_products','order_id','SQL:SELECT id from orders',0),('product_types','parent','SQL:SELECT id,product_type FROM product_types',0),('orders','complete','SQL:SELECT id,order_status from order_statuses',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `select_lists` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

