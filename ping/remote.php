<?php

$ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, "http://php5.bitflux.org/local.php");
//curl_setopt($ch, CURLOPT_URL, "http://www.planet-php.org/ping/local.php");
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
$res = curl_exec($ch);
print $res;
// close cURL resource, and free up system resources
curl_close($ch);
