<?php

/* include_once("../inc/config.inc.php");
    include_once("MDB2.php");
    $db = MDB2::connect($BX_config['dsn']);
mysql_select_db("planet_blogug_ch");

var_Dump(getLocalData(  47.3281,8.478));
*/
function getLocalData($lat,$lon) {
    
    
    //get loc_id
    $query = " select loc_id, pow($lon - lon, 2) + pow($lat - lat, 2) as dist from geodb_coordinates  where not(isnull(lon)) order by dist LIMIT 1;";
    
    $res = mysql_query($query);
    
    $row = mysql_fetch_assoc($res);
    $data = array(
        'city' => 'unknown',
        'canton' => 'unknown',
        'country' => 'unknown',
        'continent' => 'unknown'
    );
    
    //get Ort
    if ($row['dist'] > 0.03) {
        return $data;
    }
    $data['city'] = getName($row['loc_id']);
    
    
    $query = "select * from geodb_hierarchies where loc_id = ".$row['loc_id'];
    $res = mysql_query($query);
    while ($row = mysql_fetch_assoc($res)) {
        
    
        $data['canton'] = getName($row['id_lvl3']);
        $data['country'] = getName($row['id_lvl2']);
        $data['continent'] = getName($row['id_lvl1']);
    }
    return $data;
}

//$data['ort'] = $row['text_val'];





function getName($loc_id) { 
    
    $query = "select text_val, text_locale from geodb_textdata where text_type = '500100000' and loc_id = ".$loc_id ." order by text_locale" ;
    $res = mysql_query($query);
    $values = array();
    while ($row = mysql_fetch_assoc($res)) {
        switch($row['text_locale']) {
            case 'en':
            $values[1] = $row['text_val'];
            break 2;
            case 'de':
            $values[2] = $row['text_val'];
            break;
            case 'fr':
            $values[3] = $row['text_val'];
            break;
            case 'it':
            $values[3] = $row['text_val'];
            break;
            case 'rm':
            $values[4] = $row['text_val'];
            break;
            case null:
            $values[10] = $row['text_val'];
            break;
            default: 
            $values[11] = $row['text_val'];
            break;
        }
    }
    ksort($values);
    
    return array_shift($values);
    
}
