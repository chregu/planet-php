<?php


 include_once("../inc/config.inc.php");
    include_once("MDB2.php");
    include_once("../libs/geo.php");
    $db = MDB2::connect($BX_config['dsn']);
   
    
    $query = " select id, link,lon,lat from blogs where lon != 0 and lat != 0 ";
    
    $res = $db->query($query);
    
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        
        $data = getLocalData($row['lat'],$row['lon']);
        $query = 'update blogs set 
                    city = '.$db->quote($data['city']). ',
                    canton = '.$db->quote($data['canton']). ',
                    country =  '.$db->quote($data['country']). ',
                    continent =  '.$db->quote($data['continent']). '
                    where id = '.$row['id'];
        
                    $db->query($query);
                    
                    var_dump($data);
        
    }
    
    ?>