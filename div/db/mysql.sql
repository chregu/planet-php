-- MySQL dump 10.11
--
-- Host: localhost    Database: planet_blogug_ch
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
  `changed` timestamp NOT NULL,
  `author` varchar(100) NOT NULL default '',
  `dontshowblogtitle` tinyint(4) NOT NULL default '1',
  `dontshow` tinyint(4) NOT NULL default '0',
  `generator` varchar(200) NOT NULL default '',
  `listID` int(11) NOT NULL default '0',
  `lon` decimal(8,5) default '0.00000',
  `lat` decimal(8,5) default '0.00000',
  `city` varchar(100) default NULL,
  `canton` varchar(100) default NULL,
  `country` varchar(100) default NULL,
  `continent` varchar(100) default NULL,
  `tags` text NOT NULL,
  `noncommercial` tinyint(4) NOT NULL default '0',
  `inopml` tinyint(4) NOT NULL default '0',
  `last_statuscode` int(11) NOT NULL default '200',
  `last_changed` date default '0000-00-00',
  `last_infotext` text NOT NULL,
  `notOnTopList` tinyint(4) NOT NULL default '0',
  `sup_id` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `link` (`link`),
  KEY `city` (`city`),
  KEY `canton` (`canton`)
) TYPE=MyISAM AUTO_INCREMENT=742957;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `blogs2btags`
--

DROP TABLE IF EXISTS `blogs2btags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `blogs2btags` (
  `id` int(11) NOT NULL auto_increment,
  `blogs_id` int(11) NOT NULL default '0',
  `btags_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `btags_id` (`btags_id`)
) TYPE=MyISAM AUTO_INCREMENT=29645;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `branchen`
--

DROP TABLE IF EXISTS `branchen`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `branchen` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=3106;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `btags`
--

DROP TABLE IF EXISTS `btags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `btags` (
  `id` int(11) NOT NULL auto_increment,
  `btag` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `btag` (`btag`)
) TYPE=MyISAM AUTO_INCREMENT=7025;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `comments` (
  `ID` int(11) NOT NULL default '0',
  `entriesID` int(11) NOT NULL default '0',
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
  UNIQUE KEY `guid` (`guid`),
  KEY `rss_feed_ID` (`entriesID`),
  FULLTEXT KEY `search` (`content_encoded`,`title`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `enclosures`
--

DROP TABLE IF EXISTS `enclosures`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `enclosures` (
  `id` int(11) NOT NULL auto_increment,
  `entries_id` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `length` int(11) NOT NULL default '0',
  `type` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `entries_id` (`entries_id`)
) TYPE=MyISAM AUTO_INCREMENT=25364;
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
  `link` varchar(255) NOT NULL default '',
  `description` text,
  `dc_date` datetime default '0000-00-00 00:00:00',
  `dc_creator` varchar(100) default NULL,
  `content_encoded` text,
  `tags` mediumtext,
  `changed` timestamp NOT NULL,
  `md5` varchar(32) NOT NULL default '',
  `guid` varchar(255) NOT NULL default '',
  `lon` decimal(8,6) default NULL,
  `lat` decimal(8,5) default NULL,
  `commentRss` varchar(255) default NULL,
  `city` varchar(100) default NULL,
  `canton` varchar(100) default NULL,
  `country` varchar(100) default NULL,
  `continent` varchar(100) default NULL,
  `lang` varchar(2) NOT NULL default '',
  `hasHCard` tinyint(4) NOT NULL default '0',
  `hasHCal` tinyint(4) NOT NULL default '0',
  `hasHReview` tinyint(4) NOT NULL default '0',
  `hasHListing` tinyint(4) NOT NULL default '0',
  `hasEnclosure` tinyint(4) NOT NULL default '0',
  `hasWerbung` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `rss_feed_ID` (`feedsID`),
  KEY `dc_date` (`dc_date`),
  KEY `lang` (`lang`),
  KEY `guid` (`guid`),
  KEY `link` (`link`),
  FULLTEXT KEY `search` (`content_encoded`,`title`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `entries2links`
--

DROP TABLE IF EXISTS `entries2links`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entries2links` (
  `id` int(11) NOT NULL auto_increment,
  `entries_id` int(11) NOT NULL default '0',
  `links_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tags_id` (`links_id`),
  KEY `entries_id` (`entries_id`)
) TYPE=MyISAM AUTO_INCREMENT=1409030;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `entries2tags`
--

DROP TABLE IF EXISTS `entries2tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entries2tags` (
  `id` int(11) NOT NULL auto_increment,
  `entries_id` int(11) NOT NULL default '0',
  `tags_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tags_id` (`tags_id`),
  KEY `entries_id` (`entries_id`)
) TYPE=MyISAM AUTO_INCREMENT=1293025;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `entries2words`
--

DROP TABLE IF EXISTS `entries2words`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entries2words` (
  `id` int(11) NOT NULL auto_increment,
  `entries_id` int(11) NOT NULL default '0',
  `words_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tags_id` (`words_id`),
  KEY `entries_id` (`entries_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `entries_archive`
--

DROP TABLE IF EXISTS `entries_archive`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entries_archive` (
  `ID` int(11) NOT NULL default '0',
  `feedsID` int(11) NOT NULL default '0',
  `title` tinytext,
  `link` varchar(255) NOT NULL default '',
  `description` text,
  `dc_date` datetime default '0000-00-00 00:00:00',
  `dc_creator` varchar(100) default NULL,
  `content_encoded` text,
  `tags` mediumtext,
  `changed` timestamp NOT NULL,
  `md5` varchar(32) NOT NULL default '',
  `guid` varchar(255) NOT NULL default '',
  `lon` decimal(8,6) default NULL,
  `lat` decimal(8,5) default NULL,
  `commentRss` varchar(255) default NULL,
  `city` varchar(100) default NULL,
  `canton` varchar(100) default NULL,
  `country` varchar(100) default NULL,
  `continent` varchar(100) default NULL,
  `lang` varchar(2) NOT NULL default '',
  `hasHCard` tinyint(4) NOT NULL default '0',
  `hasHCal` tinyint(4) NOT NULL default '0',
  `hasHReview` tinyint(4) NOT NULL default '0',
  `hasHListing` tinyint(4) NOT NULL default '0',
  `hasEnclosure` tinyint(4) NOT NULL default '0',
  `hasWerbung` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `rss_feed_ID` (`feedsID`),
  KEY `dc_date` (`dc_date`),
  KEY `lang` (`lang`),
  KEY `guid` (`guid`),
  KEY `link` (`link`)
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
  `active` tinyint(4) NOT NULL default '0',
  `get` tinyint(4) NOT NULL default '1',
  `listID` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `rssURL` (`link`),
  KEY `blogID` (`blogsID`)
) TYPE=MyISAM AUTO_INCREMENT=4086;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `generators`
--

DROP TABLE IF EXISTS `generators`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `generators` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `gengroup` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=226;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `geodb_coordinates`
--

DROP TABLE IF EXISTS `geodb_coordinates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `geodb_coordinates` (
  `loc_id` int(11) NOT NULL default '0',
  `lon` double default NULL,
  `lat` double default NULL,
  `coord_type` int(11) NOT NULL default '0',
  `coord_subtype` int(11) default NULL,
  `valid_since` date default NULL,
  `date_type_since` int(11) default NULL,
  `valid_until` date NOT NULL default '0000-00-00',
  `date_type_until` int(11) NOT NULL default '0',
  KEY `coord_loc_id_idx` (`loc_id`),
  KEY `coord_lon_idx` (`lon`),
  KEY `coord_lat_idx` (`lat`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `geodb_hierarchies`
--

DROP TABLE IF EXISTS `geodb_hierarchies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `geodb_hierarchies` (
  `loc_id` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `id_lvl1` int(11) NOT NULL default '0',
  `id_lvl2` int(11) default NULL,
  `id_lvl3` int(11) default NULL,
  `id_lvl4` int(11) default NULL,
  `id_lvl5` int(11) default NULL,
  `id_lvl6` int(11) default NULL,
  `id_lvl7` int(11) default NULL,
  `id_lvl8` int(11) default NULL,
  `id_lvl9` int(11) default NULL,
  `valid_since` date default NULL,
  `date_type_since` int(11) default NULL,
  `valid_until` date NOT NULL default '0000-00-00',
  `date_type_until` int(11) NOT NULL default '0',
  KEY `hierarchy_loc_id_idx` (`loc_id`),
  KEY `hierarchy_level_idx` (`level`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `geodb_textdata`
--

DROP TABLE IF EXISTS `geodb_textdata`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `geodb_textdata` (
  `loc_id` int(11) NOT NULL default '0',
  `text_val` varchar(255) NOT NULL default '',
  `text_type` int(11) NOT NULL default '0',
  `text_locale` varchar(5) default NULL,
  `is_native_lang` smallint(1) default NULL,
  `is_default_name` smallint(1) default NULL,
  `valid_since` date default NULL,
  `date_type_since` int(11) default NULL,
  `valid_until` date NOT NULL default '0000-00-00',
  `date_type_until` int(11) NOT NULL default '0',
  KEY `text_lid_idx` (`loc_id`),
  KEY `text_type_idx` (`text_type`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `geodb_type_names`
--

DROP TABLE IF EXISTS `geodb_type_names`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `geodb_type_names` (
  `type_id` int(11) NOT NULL default '0',
  `type_locale` varchar(5) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  UNIQUE KEY `type_id` (`type_id`,`type_locale`),
  KEY `tid_tnames_idx` (`type_id`),
  KEY `locale_tnames_idx` (`type_locale`),
  KEY `name_tnames_idx` (`name`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `geolocation`
--

DROP TABLE IF EXISTS `geolocation`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `geolocation` (
  `street` varchar(200) NOT NULL default '',
  `town` varchar(200) NOT NULL default '',
  `plz` int(11) NOT NULL default '0',
  `country` varchar(20) NOT NULL default '',
  `lon` float default '0',
  `lat` float default '0',
  KEY `street` (`street`),
  KEY `town` (`town`),
  KEY `plz` (`plz`),
  KEY `country` (`country`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `htmllinks`
--

DROP TABLE IF EXISTS `htmllinks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `htmllinks` (
  `id` int(11) NOT NULL auto_increment,
  `outgoingblog_id` int(11) NOT NULL default '0',
  `incomingblog_id` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL,
  `firsttime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=29151;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `htmllinks_cache`
--

DROP TABLE IF EXISTS `htmllinks_cache`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `htmllinks_cache` (
  `link` varchar(255) binary NOT NULL default '',
  `blogs_id` int(11) NOT NULL default '0',
  `changed` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`link`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `links` (
  `id` int(11) NOT NULL auto_increment,
  `link` varchar(255) NOT NULL default '',
  `hide` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag` (`link`)
) TYPE=MyISAM AUTO_INCREMENT=972916;
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
) TYPE=MyISAM AUTO_INCREMENT=743494 PACK_KEYS=0;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `saved`
--

DROP TABLE IF EXISTS `saved`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `saved` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(200) NOT NULL default '',
  `search` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=9;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(255) NOT NULL default '',
  `taggroup` varchar(255) NOT NULL default '',
  `count` int(11) default NULL,
  `hide` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag` (`tag`),
  KEY `taggroup` (`taggroup`)
) TYPE=MyISAM AUTO_INCREMENT=166071;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tmp`
--

DROP TABLE IF EXISTS `tmp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tmp` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `gengroup` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=154;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `words`
--

DROP TABLE IF EXISTS `words`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `words` (
  `id` int(11) NOT NULL auto_increment,
  `word` varchar(255) NOT NULL default '',
  `hide` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag` (`word`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-05-03  7:16:10
