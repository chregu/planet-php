#!/bin/bash
mysql planet_blogug_ch -f -e 'REPLACE INTO entries_archive SELECT * from entries where dc_date < date_sub(now(), INTERVAL 180 DAY);' && mysql planet_blogug_ch -f -e 'DELETE  from  entries where dc_date < date_sub(now(), INTERVAL 180 DAY);'
php deletestaleentries2links.php 
php deletestalelinks.php 
php deletestaleentries2links.php 
php deletestaleentries2tags.php 
php deletestaletags.php 
php deletestaleentries2tags.php 

