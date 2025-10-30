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
-- Table structure for table `product_types`
--

DROP TABLE IF EXISTS `product_types`;
CREATE TABLE `product_types` (
  `id` int(11) NOT NULL auto_increment,
  `product_type` varchar(64) default NULL,
  `parent` int(11) default NULL,
  `icon` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_types`
--


/*!40000 ALTER TABLE `product_types` DISABLE KEYS */;
LOCK TABLES `product_types` WRITE;
INSERT INTO `product_types` VALUES (1,'Stationery',NULL,NULL),(2,'Business Cards',1,'business_cards.gif'),(3,'Compliment Slips',1,'compliment_slips.gif'),(4,'Logo Headed Paper',1,'logohead.gif'),(5,'Letter Headed Paper',1,'letterhead_paper.gif'),(6,'Labels',1,'labels.gif'),(7,'Clock Cards',1,'clock_cards.gif'),(8,'Continuation Paper',1,'continuation_paper.gif'),(9,'Customer Support Unit Cards',1,'csu.gif'),(10,'DL Envelopes',1,'dl_envelopes.gif'),(11,'Poly Bags',1,'polybags.gif'),(12,'Fitting Instructions',1,'fitting_instructions.gif'),(13,'Templates',1,'templates.gif'),(14,'Velcro',1,'velcro.gif'),(15,'Signs',1,'signs.gif'),(16,'Non Conformance Log',1,'nclog.gif'),(17,'Stores Requisition Pad',1,'requisition_pad.gif'),(48,'Type 515E',12,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_types` ENABLE KEYS */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL auto_increment,
  `ordered_by` int(11) default NULL,
  `order_date` date default NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `delivery_address` int(11) default NULL,
  `po_number` varchar(32) default NULL,
  `comment` varchar(512) default NULL,
  `complete` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `orders`
--


/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
LOCK TABLES `orders` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;

--
-- Table structure for table `product_attributes`
--

DROP TABLE IF EXISTS `product_attributes`;
CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL auto_increment,
  `attribute_name` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_attributes`
--


/*!40000 ALTER TABLE `product_attributes` DISABLE KEYS */;
LOCK TABLES `product_attributes` WRITE;
INSERT INTO `product_attributes` VALUES (1,'Name'),(2,'Job Title'),(3,'Business Name'),(4,'Division'),(5,'Building Name'),(6,'Address'),(7,'Town'),(8,'Zip/Postcode'),(9,'Country'),(10,'Telephone'),(11,'Direct Telephone'),(12,'Fax'),(13,'Mobile'),(14,'Email Address'),(15,'Web Address');
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_attributes` ENABLE KEYS */;

--
-- Table structure for table `order_product_attributes`
--

DROP TABLE IF EXISTS `order_product_attributes`;
CREATE TABLE `order_product_attributes` (
  `id` int(11) NOT NULL auto_increment,
  `order_product_id` int(11) default NULL,
  `attribute_name` varchar(128) default NULL,
  `attribute_value` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_product_attributes`
--


/*!40000 ALTER TABLE `order_product_attributes` DISABLE KEYS */;
LOCK TABLES `order_product_attributes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_product_attributes` ENABLE KEYS */;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `ID` int(11) NOT NULL auto_increment,
  `category_name` int(11) default NULL,
  `min_order_qty` int(11) NOT NULL default '0',
  `max_order_qty` int(11) NOT NULL default '0',
  `code` varchar(15) character set latin1 collate latin1_general_ci NOT NULL default '',
  `title` varchar(150) character set latin1 collate latin1_general_ci NOT NULL default '',
  `full_description` text character set latin1 collate latin1_general_ci NOT NULL,
  `thumbnail_name` varchar(128) default NULL,
  `net_price` double NOT NULL default '0',
  `hidden` tinyint(1) default NULL,
  `available` tinyint(1) default NULL,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `associated_file_list` varchar(512) default NULL,
  `stationery_override_option` int(11) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `products`
--


/*!40000 ALTER TABLE `products` DISABLE KEYS */;
LOCK TABLES `products` WRITE;
INSERT INTO `products` VALUES (133,12,0,0,'5615Q','Type 5615Q (Qty 1000 per unit)','Type 5615Q (Qty 1000 per unit) English','t_5615QFitInstruction.jpg',215,0,1,'2005-11-25 11:46:29','2006-02-17 12:18:31','5615QFitInstruction.pdf',NULL),(134,12,0,0,'5615Q','Type 5615Q (Qty 1000 per unit) - German','<p>Type 5615Q (Qty 1000 per unit)</p>','',215,0,1,'0000-00-00 00:00:00','2009-04-27 16:47:04','',NULL),(132,12,0,0,'FI5610Q','Type 5610Q (Qty 1000 per unit)','Type 5610Q (Qty 1000 per unit)','',215,0,1,'2005-11-25 11:45:15','2005-11-25 11:51:49','',NULL),(124,12,0,0,'FI 5615Q','Type 5615Q (Qty 1000 per unit)','Type 5615Q (Qty 1000 per unit)','',215,0,1,'2005-11-25 11:13:06','2005-11-25 11:19:09','',NULL),(123,2,0,0,'URG1','URGENT Single Sided (Qty 250 per unit)','URGENT Single Sided (Qty 250 per unit)','t_Manchester_BCard.jpg',110,0,1,'2005-11-21 14:45:04','2006-10-19 15:26:43','',1),(122,2,0,0,'DS BC1','Double Sided Business Cards (Qty 250 per unit)','Double Sided Business Cards (Qty 250 per unit)','t_Manchester_BCard.jpg',120,0,1,'2005-11-21 14:41:03','2006-12-08 10:01:56','',2),(118,11,0,0,'PB1','PPS Poly Bags (Qty 1000 per unit)','<p>Poly bags (Qty 1000 per unit)</p>','',50,0,1,'2005-11-10 13:39:56','2006-12-01 12:21:37','',0),(26,2,0,0,'BC1','Business Card (Qty 250 per unit)','<p>Business Card, single sided</p>','t_Manchester_BCard.jpg',45,0,1,'2005-10-27 12:15:32','2006-10-19 15:26:29','',1),(29,3,0,0,'CS M1','Comp Slip Man (Qty 1000 slips per unit)','<p>Qty 1000 slips per unit - Compliment Slip Manchester</p>','t_JCManchesterOfficeCSlipNEW.jpg',55,0,1,'2005-10-27 12:40:31','2006-10-19 15:27:02','',0),(30,3,0,0,'CS Slou','Comp Slip Slough (Qty 1000 slips per unit)','Compliment Slip Slough (Qty 1000 slips per unit','t_JohnCraneSloughCSlip.jpg',55,0,1,'2005-10-27 12:41:26','2006-10-19 15:28:02','',NULL),(31,3,0,0,'CS NI','Compliment Slip Northern Ireland (Qty 1000 Slips per unit)','Compliment Slip Northern Ireland (Qty 1000 Slips per unit)','t_JCNIOfficeCSlipNEW.jpg',55,0,1,'2005-10-27 12:42:59','2005-11-18 10:28:21','',NULL),(34,4,0,0,'Logo Port','Logo Headed Paper Potrait  (Qty 1000 per unit)','Logo Headed Paper Potrait','t_PortLogoPaer.jpg',65,0,1,'2005-10-27 14:12:11','2006-04-03 16:01:27','',NULL),(32,3,0,0,'CS Sw','Compliment Slip Swansea (Qty 1000 slips per unit)','Compliment Slip Swansea (Qty 1000 slips per unit)','t_JCSwanseaOfficeCSlipNEW.jpg',55,0,1,'2005-10-27 13:20:09','2005-11-18 10:28:47','',NULL),(35,4,0,0,'LP Land','Logo Headed Paper Landscape  (Qty 1000 per unit)','Landscape Logo Headed Paper','t_LandscapeLogoPaper.jpg',65,0,1,'2005-10-27 14:39:15','2006-04-03 16:01:12','',NULL),(36,5,0,0,'LH EAA','EAA Letterhead  (Qty 1000 per unit)','John Crane EAA Letterheaded Paper','t_EAASloughLHead.jpg',65,0,1,'2005-10-27 14:47:48','2006-04-03 16:02:11','',NULL),(37,5,0,0,'HO LH','Head Office Letterhead (Qty 1000 per unit)','Head Office Letterhead','t_HeadOfficeSloughLHead.jpg',65,0,1,'2005-10-27 14:51:42','2006-04-03 16:01:57','',NULL),(38,5,0,0,'UK LH','Slough Letterhead  (Qty 1000 per unit)','Letterheaded Paper Slough UK','t_SloughUKLHead.jpg',65,0,1,'2005-10-27 14:55:38','2006-04-03 16:02:52','',NULL),(39,5,0,0,'LH Man','Letterhead Manchester  (Qty 1000 per unit)','Letterheaded Paper Manchester','t_JC_ManchesterOfficeLHead.jpg',65,0,1,'2005-10-27 14:56:57','2006-10-19 15:28:29','',NULL),(40,5,0,0,'NI LH','Northern Ireland Letterhead  (Qty 1000 per unit)','<p>Letterheaded Paper Northern Ireland</p>','t_JC_NIrelandLH.jpg',65,0,1,'2005-10-27 14:58:12','2006-10-19 15:29:50','',0),(41,7,0,0,'CC 1','Clock Cards (Qty 1000 per unit)','','t_ClockCards2.jpg',105,0,1,'2005-10-27 15:00:18','2006-03-24 13:56:29','',0),(42,6,0,0,'L1','49 x 89 2 Across computer label (Qty 6000 per unit)','49 x 89 2 Across computer label\r\n (Qty 6000 per unit)','',36,0,1,'2005-10-27 15:03:25','2005-11-18 10:32:00','',NULL),(43,6,0,0,'L2','99 x 153 Computer Label (Qty 3000 per unit)','99 x 153 Computer Label\r\n (Qty 3000 per unit)','',45,0,1,'2005-10-27 15:05:52','2005-11-18 10:32:22','',NULL),(44,6,0,0,'Red L1','Red Numbered Label (Qty 1000 per unit)','Red Numbered Label (Qty 1000 per unit)','',7.25,0,1,'2005-10-27 15:07:10','2006-07-10 15:28:33','',NULL),(45,6,0,0,'Blue L1','Blue Numbered 2up (Qty 5 rolls of 2000)','Blue Numbered 2up\r\nnumbered 1000 - 5000','',442,0,1,'2005-10-27 15:09:11','2005-11-18 10:32:49','',NULL),(46,8,0,0,'CP1','Continuation Paper (Qty 1000 per unit)','Continuation Paper (Qty 1000 per unit)','t_JC_Cont-Sht.jpg',55,0,1,'2005-10-27 15:11:00','2005-11-18 10:29:30','',NULL),(47,9,0,0,'CSU','Customer Support Unit Card (Qty 250 per unit)','Customer Support Unit Card (Qty 250 per unit)','t_CustomerSupportUnit.jpg',45,0,1,'2005-10-27 15:13:59','2005-11-18 10:30:08','',NULL),(48,17,0,0,'SRP 1','Stores Requisition Pad (Qty 20 per unit)','Stores Requisition Pad (Qty 20 per unit)\r\n\r\nSize A5\r\nTop Sheet - Pink\r\nMiddle Sheet - White\r\nBottom Sheet - Yellow\r\n','t_StoresRequisitionPad.jpg',205,0,1,'2005-10-28 09:44:24','2005-11-18 11:00:13','',NULL),(49,10,0,0,'DL 1','Slough DL Envelope (Qty1000 per unit)','Slough DL Envelope (Qty1000 per unit)','t_SloughEnvelope[3462].jpg',90,0,1,'2005-10-28 09:50:26','2005-11-18 10:30:58','',NULL),(50,10,0,0,'DL 2','Manchester DL Envelope (Qty 1000 per unit)','Manchester DL Envelope (Qty 1000 per unit)','t_ManchesterEnvelope[3930].jpg',90,0,1,'2005-10-28 09:55:06','2005-11-18 10:30:24','',NULL),(51,6,0,0,'GL','Gummed Label (Qty 1000 per unit)','Gummed Label (Qty 1000 per unit)','t_Gummedlabel.jpg',127,0,1,'2005-10-28 12:08:36','2005-11-18 10:34:01','',NULL),(52,16,0,0,'NCL1','Non Conformance Log (Qty 1000 per unit)','<p>Non Conformance Log (Qty 1000 per unit)</p>','t_NonConformaceLog.jpg',132.5,0,1,'2005-10-28 12:11:23','2005-11-18 10:59:44','',0),(131,12,0,0,'FI5610Q','Type 5610Q (Qty 1000 per unit)','Type 5610Q (Qty 1000 per unit)','t_5610QFitInstruction.jpg',215,0,1,'2005-11-25 11:44:58','2006-02-17 12:16:29','5610QFitInstruction.pdf',NULL),(128,12,0,0,'FI 5615Q','Type 5615Q (Qty 1000 per unit)','Type 5615Q (Qty 1000 per unit)','',215,0,1,'2005-11-25 11:34:07','2005-11-25 11:34:17','',NULL),(135,12,0,0,'FI 21','Type 21 (Qty 1000 per unit)','Type 21 (Qty 1000 per unit)','t_FittingInstruction21_5706.jpg',215,0,1,'2005-11-25 11:48:41','2006-02-17 10:48:50','FittingInstruction21_5706.pdf',NULL),(137,12,0,0,'FI 59U','Type 59U (Qty 1000 per unit)','Type 59U (Qty 1000 per unit)','t_59UFitInstruction_5710.jpg',215,0,1,'2005-11-25 11:56:29','2006-02-17 10:35:09','59UFitInstruction_5710.pdf',NULL),(138,12,0,0,'FI 109','Type 109 (Qty 1000 per unit)','Type 109 (Qty 1000 per unit)','t_109FitInstruction.jpg',215,0,1,'2005-11-25 11:57:29','2006-02-17 12:17:32','109FitInstruction.pdf',NULL),(139,48,0,0,'FI 515E','Type 515E (Qty 1000 per unit)','Type 515E (Qty 1000 per unit)','t_515EFitInstruction_5712.jpg',215,0,1,'2005-11-25 12:00:39','2006-02-01 14:59:43','515EFitInstruction_5712.pdf',NULL),(140,12,0,0,'FI 8-1','Type 8-1 (Qty 1000 per unit)','Type 8-1 (Qty 1000 per unit)','t_8-1FitInstruction_5718.jpg',215,0,1,'2005-11-25 12:01:43','2006-02-17 10:46:31','8-1FitInstruction_5718.pdf',NULL),(141,12,0,0,'FI 8-1T','Type 8-1T (Qty 1000 per unit)','Type 8-1T (Qty 1000 per unit)','t_Pagesfrom8-1TFitInstruction.jpg',215,0,1,'2005-11-25 12:03:47','2006-02-15 12:49:13','8-1TFitInstruction.pdf',NULL),(142,12,0,0,'FI 8B1','Type 8B1 (Qty 1000 per unit)','Type 8B1 (Qty 1000 per unit)','t_Pagesfrom8B1FitInstruction.jpg',215,0,1,'2005-11-25 12:04:48','2006-02-15 12:52:10','8B1FitInstruction.pdf',NULL),(143,12,0,0,'Fi 8B1T','Type 8B1T (Qty 1000 per unit)','Type 8B1T (Qty 1000 per unit)','t_Pagesfrom8B1TFitInstruction.jpg',215,0,1,'2005-11-25 12:08:13','2006-02-15 12:55:15','8B1TFitInstruction.pdf',NULL),(144,12,0,0,'FI 9T','Type 9T (Qty 1000 per unit)','Type 9T (Qty 1000 per unit)','t_Pagesfrom9TFitInstruction.jpg',215,0,1,'2005-11-25 12:09:01','2006-02-15 13:01:03','9TFitInstruction.pdf',NULL),(145,12,0,0,'FI 51B','Type 51B (Qty 1000 per unit)','Type 51B (Qty 1000 per unit)','t_Pagesfrom51BFitInstruction.jpg',215,0,1,'2005-11-25 12:09:58','2006-02-15 12:58:02','51BFitInstruction.pdf',NULL),(146,12,0,0,'FI 51BMS','Type 51BMS (Qty 1000 per unit)','Type 51BMS (Qty 1000 per unit)','t_Pagesfrom51BMSFitInstruction.jpg',215,0,1,'2005-11-25 12:11:02','2006-02-15 13:05:30','51BMSFitInstruction.pdf',NULL),(147,12,0,0,'FI 52B','Type 52B (Qty 1000 per unit)','Type 52B (Qty 1000 per unit)','t_Pagesfrom52BFitInstruction.jpg',215,0,1,'2005-11-25 12:11:50','2006-02-15 13:04:19','52BFitInstruction.pdf',NULL),(148,12,0,0,'FI 209','Type 209 (Qty 1000 per unit)','Type 209 (Qty 1000 per unit)','t_Pagesfrom209FitInstruction.jpg',215,0,1,'2005-11-25 12:12:36','2006-02-15 13:07:50','209FitInstruction.pdf',NULL),(149,12,0,0,'FI 209B','Type 209B (Qty 1000 per unit)','Type 209B (Qty 1000 per unit)','t_Pagesfrom209BFitInstruction.jpg',215,0,1,'2005-11-25 12:13:22','2006-02-15 13:06:40','209BFitInstruction.pdf',NULL),(150,12,0,0,'FI 2','Type 2 (Qty 1000 per unit)','Type 2 (Qty 1000 per unit)','t_2FitInstruction.jpg',215,0,1,'2005-11-25 12:14:17','2006-02-15 12:38:21','2FitInstruction.pdf',NULL),(151,12,0,0,'FI 502','Type 502 (Qty 1000 per unit)','Type 502 (Qty 1000 per unit)','t_Pagesfrom502FitInstruction.jpg',215,0,1,'2005-11-25 12:21:08','2006-02-15 13:11:25','502FitInstruction.pdf',NULL),(152,12,0,0,'FI 1A','Type 1A','Type 1A fitting instruction','t_1AFittingInstuction_5677_1.jpg',215,0,1,'2005-12-09 12:04:32','2006-02-17 10:33:30','1AFittingInstuction_5677.pdf',NULL),(341,12,0,0,'58U','Type 58U (Qty 1000 per unit)','Type 58U','t_Pagesfrom811658UFitInstructions.jpg',215,0,1,'2006-02-15 13:52:30','2006-03-02 10:53:27','',NULL),(342,12,0,0,'Type 20','Type 20 (Qty 1000 per unit)','Type 20 (Qty 1000 per unit) ','t_20FitInstruction.jpg',215,0,1,'2006-02-17 12:21:00','2006-10-05 11:02:44','',NULL),(343,12,0,0,'Type 10T and 10','Type 10T and 10R (Qty 1000 per unit)','Type 10T and 10R','t_10T&10RFitInstruction.jpg',0,0,1,'2006-02-17 12:24:21','2006-02-17 12:24:21','',NULL),(416,12,0,0,'Type 5610','Type 5610','Type 5610','t_4323_5610FitInstruction.jpg',0,0,1,'2006-03-03 10:54:29','2006-03-03 10:54:29','',NULL),(417,12,0,0,'Type 5620','Type 5620','Type 5620','t_5620FitInstruction[4325].jpg',215,0,1,'2006-03-03 10:58:39','2006-10-05 11:03:56','',NULL),(697,6,0,0,'Plain Blue Labe','Plain Blue Labels (Qty 1000 per unit)','Plain Blue Labels (Qty 1000 per unit)','',202,0,1,'2006-07-17 10:57:20','2006-07-17 10:57:20','',NULL),(690,15,0,0,'FS1','Factory Sign','Factory Signs','t_JC_Sign.jpg',0,0,1,'2006-06-30 16:22:05','2006-06-30 16:48:54','',3),(592,12,0,0,'109B','109B','','t_109BFitInstruction.jpg',215,0,1,'2006-05-05 17:09:24','2007-04-10 14:55:03','109BFitInstruction.pdf',NULL),(597,12,0,0,'Type 1','Type 1','Type 1','t_62991FitInstruction.jpg',215,0,1,'2006-05-09 12:30:41','2006-10-05 11:01:59','',NULL),(598,12,0,0,'Type 2100','Type 2100','Type 2100','t_2100FitInstruction[4320].jpg',215,0,1,'2006-05-09 12:34:48','2006-10-05 11:03:10','',NULL),(599,48,0,0,'FI 515E','Type 515E (Qty 1000 per unit) - Spanish','<p>Type 515E (Qty 1000 per unit)</p>','t_8169515EFitInstructionSPA.jpg',215,0,1,'0000-00-00 00:00:00','2009-04-27 16:43:33','<p>8169515EFitInstructionSPA.pdf</p>',NULL),(600,12,0,0,'Type 2800ER','Type 2800ER','Type 2800ER','t_Pagesfrom8375Type2800ER.jpg',215,0,1,'2006-05-09 13:13:01','2006-10-05 11:03:22','',NULL),(601,12,0,0,'L Series','L Series','L Series','t_LSeriesFitInstruct-Swe[4356].jpg',215,0,1,'2006-05-09 13:14:49','2006-10-05 11:02:17','',NULL),(616,14,0,0,'VR','Velcro Hook','','t_velcro.jpg',40,0,1,'2006-05-12 14:28:24','2006-12-12 12:34:12','',NULL),(617,14,0,0,'VL','Velcro Loop','','t_velcro.jpg',40,0,1,'2006-05-12 14:35:24','2006-12-12 12:34:22','',NULL),(714,5,0,0,'Queens Award LH','Queens Award Slough (Qty 1000 per unit)','Queens Award Slough','t_JC_SloughLHead.jpg',65,0,1,'2006-08-21 14:38:10','2007-04-10 16:35:56','',NULL),(716,2,0,0,'Queens Award Si','Queens Award Single Sided','Queens Award Single Sided','t_Slough_QA_BCard.jpg',45,0,1,'2006-08-21 14:40:20','2006-10-19 15:26:15','',1),(725,13,0,0,'JC Memorandum','JC Memorandum','JC Memorandum','',0,0,1,'2006-09-11 13:09:09','2006-09-11 13:09:20','JCMemo-Template.doc',NULL),(726,13,0,0,'JC Fax','JC Fax','JC Fax','',0,0,1,'2006-09-11 13:09:42','2006-09-11 13:09:50','JCFax-Template-2.doc',NULL),(752,11,0,0,'PPS Blue Bags','PPS Blue Bags (Qty 1000 per unit)','PPS Blue Bags','',90,0,1,'2006-10-05 10:59:45','2006-10-05 11:15:46','',NULL),(754,6,0,0,'Test Acceptance','50 x 30mm stickets (Qty 1000 per unit)','Test Acceptance Sticker','t_JCTestAcceptance_50x30mm.jpg',582,0,1,'2006-10-20 11:46:13','2006-10-20 11:46:13','',NULL),(785,15,0,0,'Performance Plu','Performance Plus','Performance Plus','t_9022A4PPlusLogo.jpg',0,0,1,'2006-11-20 12:24:49','2006-11-20 12:25:04','',3),(786,15,0,0,'NW Service Cent','NW Service Centre','NW Service Centre','t_9010ServiceC.Ext.Signv2.jpg',0,0,1,'2006-11-20 12:25:58','2006-11-20 12:26:17','9010ServiceC.Ext.Signv2.pdf',3),(1067,6,0,0,'333/1190 (Qty 5','333/1190 (Qty 5000)','333/1190 (Qty 5000)','t_JCLabel.jpg',180,0,1,'2007-08-10 16:42:34','2007-08-10 17:02:07','JCLabel.pdf',NULL),(1069,6,0,0,'50 rolls of 5 c','Daily labels 1-100 (Qty - 25000)','50 rolls of 5 colours','',795,0,1,'2007-08-28 16:50:12','2007-08-29 12:36:47','',NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

--
-- Table structure for table `order_products`
--

DROP TABLE IF EXISTS `order_products`;
CREATE TABLE `order_products` (
  `id` int(11) NOT NULL auto_increment,
  `order_id` int(11) default NULL,
  `product_id` int(11) default NULL,
  `quantity` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_products`
--


/*!40000 ALTER TABLE `order_products` DISABLE KEYS */;
LOCK TABLES `order_products` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_products` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

