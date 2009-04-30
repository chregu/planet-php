-- MySQL dump 10.11
--
-- Host: localhost    Database: planet-php
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny1
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MYSQL40' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `blogs` (
  `ID` int(11) NOT NULL auto_increment,
  `link` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` tinytext,
  `changed` timestamp NOT NULL default '0000-00-00 00:00:00',
  `author_old` varchar(100) NOT NULL default '',
  `dontshowblogtitle` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `link` (`link`)
) TYPE=MyISAM AUTO_INCREMENT=33958;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `entries`
--

DROP TABLE IF EXISTS `entries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entries` (
  `ID` int(11) NOT NULL default '0',
  `feedsID` int(11) NOT NULL default '0',
  `title` tinytext,
  `link` tinytext NOT NULL,
  `description` text,
  `dc_date` datetime default '0000-00-00 00:00:00',
  `dc_creator` varchar(100) default NULL,
  `content_encoded` text,
  `changed` timestamp NOT NULL,
  `md5` varchar(32) NOT NULL default '',
  `guid` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `guid` (`guid`(250)),
  KEY `rss_feed_ID` (`feedsID`),
  FULLTEXT KEY `search` (`description`,`content_encoded`,`title`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `feeds`
--

DROP TABLE IF EXISTS `feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feeds` (
  `ID` int(11) NOT NULL auto_increment,
  `blogsID` int(11) NOT NULL default '0',
  `link` varchar(255) NOT NULL default '',
  `changed` timestamp NOT NULL,
  `cats` varchar(255) NOT NULL default '',
  `section` varchar(50) NOT NULL default 'default',
  `author` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `rssURL` (`link`),
  KEY `blogID` (`blogsID`),
  KEY `section` (`section`)
) TYPE=MyISAM AUTO_INCREMENT=351;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `planet_seq`
--

DROP TABLE IF EXISTS `planet_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `planet_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) TYPE=MyISAM AUTO_INCREMENT=34031;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `url` varchar(200) NOT NULL default '',
  `rss` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `changed` timestamp NOT NULL,
  `email` varchar(200) NOT NULL default '',
  `state` tinyint(4) NOT NULL default '0',
  `rejectreason` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=612;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-04-30 16:02:31
