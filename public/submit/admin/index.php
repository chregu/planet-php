<?php

$dom = new domdocument();
$xsl = new domdocument();
$xsl->load("../../themes/planet-php/submit-admin.xsl");


$xslt = new xsltprocessor();
$xslt->registerPHPFunctions();





include("../../inc/config.inc.php");

include("MDB2.php");

$db = MDB2::connect($BX_config['dsn']);

$db->query("set names 'utf8';");

if (!empty($_POST['id'])) {
    
    
    if (!empty($_POST['accept'])) {
        
        
        if ($_POST['accept'] == 'accept') {
            $mailtext = 'Hi

We\'d like to inform you, that your ' . PROJECT_NAME_HR .' submission for 
'. $_POST['url'].' was accepted and should show up in the next
(max. 30) minutes on ' . PROJECT_URL . '

Thanks for your submission

The ' . PROJECT_NAME_HR . ' team.
';
            updatePost($db,1);
            
            
            $query = "insert into feeds (link, author) VALUES (".$db->quote($_POST['rss']).",".$db->quote($_POST['name']).")";
            $res = $db->query($query);
            if ($db->isError($res)) {
                die("a DB errror happened: " . $res->getUserInfo());
            } else {
                // FIXME: XSS all over
                sendMail($_POST['email'],"Your " . PROJECT_NAME_HR . " submission for ". $_POST['url']. " was accepted",$mailtext);
                $xsl->load("../../themes/planet-php/static.xsl");
                $dom->loadXML('<html><body>Blog added and mail sent.
                <br/><a href="./">Back</a>
                </body></html>');
            }
            
        } else {
            updatePost($db,0);
            
            
            header("Location: ./");
        }
        
    } else if (!empty($_POST['reallyreject'])) 
    {
        
        if (!empty($_POST['rejectreason'])) {
            
            updatePost($db,2);
            
            sendMail($_POST['email'],"Your " . PROJECT_NAME_HR . " submission for ". $_POST['url']. " was rejected",$_POST['rejectreason']);
                $xsl->load("../../themes/planet-php/static.xsl");
                $dom->loadXML('<html><body>Blog NOT added and mail sent.
                <br/><a href="./">Back</a>
                </body></html>');
            
        } else {
             header("Location: ./");
            
        }
            
        
    }
} else {
    
    
    $query = "select * from submissions where state = 0;";
    $res = $db->query($query);
    if ($db->isError($res)) {
        print "An Error happened";
        print $res->getUserInfo();
        die();
    } else {
        $xml="<results>";
        
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $xml .="<entry>";
            foreach($row as $key => $value) {
                
                $xml .= '<'.$key.'>'.htmlspecialchars($value).'</'.$key.'>';   
            }
            $xml .="</entry>";
            
        }
        
        $xml  .= '</results>';
        $dom->loadXML($xml);
        
    }
}
$xslt->importStylesheet($xsl);

print ($xslt->transformToXml($dom));

function getPostValue($name) {
    
    if (!empty($_POST[$name])) {
        return $_POST[$name];
    }
    return "";
    
}


function sendMail($to,$subject,$text) {
    $header = "From: " . PROJECT_NAME_HR . " <" . PROJECT_ADMIN . ">\n";
    $header .= "Bcc: " . PROJECT_ADMIN_EMAIL;
    mail($to,$subject,$text,$header);
    
}

function updatePost($db,$state) {
    $query = "update submissions ";
    $query .= "set rss = " . $db->quote($_POST['rss']) .",";
    $query .= " name = " . $db->quote($_POST['name']) .",";
    $query .= " url = " . $db->quote($_POST['url']) .",";
    $query .= " email = " . $db->quote($_POST['email']) .",";
    $query .= " description = " . $db->quote($_POST['description']) . ",";
    $query .= " state = ".$state;
    $query .= " where id = " . $_POST['id'];
    
    $res = $db->query($query);
    if ($db->isError($res)) {
        die("a DB errror happened: " . $res->getUserInfo());
    } 
}

