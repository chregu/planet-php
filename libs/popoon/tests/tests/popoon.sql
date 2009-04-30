# MySQL dump 8.13
#
# Host: localhost    Database: xmltest
#--------------------------------------------------------
# Server version	3.23.36

#
# Table structure for table 'albums'
#

CREATE TABLE albums (
  id int(11) NOT NULL auto_increment,
  bandsID int(11) NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  year smallint(6) NOT NULL default '0',
  comment text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table 'albums'
#

INSERT INTO albums VALUES (1,1,'BlaBla',1998,'Their first one');
INSERT INTO albums VALUES (2,1,'More Talks',2000,'The second one');
INSERT INTO albums VALUES (3,2,'All your base...',1999,'The Classic');

#
# Table structure for table 'bands'
#

CREATE TABLE bands (
  id int(11) NOT NULL auto_increment,
  name varchar(50) NOT NULL default '',
  birth_year int(11) NOT NULL default '0',
  birth_place varchar(50) NOT NULL default '',
  genre varchar(50) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table 'bands'
#

INSERT INTO bands VALUES (1,'The Blabbers',1998,'London','Rock\'n\'Roll');
INSERT INTO bands VALUES (2,'Only Stupids',1997,'New York','Hip Hop');

