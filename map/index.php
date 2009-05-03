<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


<script type="text/javascript" src="http://api.orino.ch/inc/lib.js?r=1">
</script>
<script type="text/javascript" src="http://api.orino.ch/inc/js.php?preset=mapwidget&r=1">
</script>

 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="robots" content="noindex, nofollow" />
  <title>
   Planet Switzerland Map
  </title>
  <link rel="shortcut icon" href="/favicon.ico" />
  <link href="/themes/css/style-list.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">

var feedid2 = '<?php
if (!empty($_GET['local'])) {
    $url = "http://planet.blogug.ch/georss/search/geo:?limit=500&icon=local";
} else {
    $url =  "http://planet.blogug.ch/georss/search/geo:?limit=500";
}
print md5($url);
//print substr(base64_encode(md5($url)), 0, 10);

?>';

var layerids = '<?php

if (!empty($_GET['today'])) {
    print 'blogug_0_day';
}
else {
        print '';
}
print "';\n";
/*if (!empty($_GET['BLx'])) {
    print "var BLx = ". $_GET['BLx'] .";\n"; 
    print "var BLy = ". $_GET['BLy'] .";\n";
    print "var TRx = ". $_GET['TRx'] .";\n";
    print "var TRy = ". $_GET['TRy'] .";\n";
}*/
?>


</script>
<script type="text/javascript" src="./map.js">
</script>


<style type="text/css">
#mapContainer {
    height: 600px;
    width: 900px;
    
}
a {
    color: #333;
	
    font-weight: bold;
    text-decoration: none;
}
a:visited {}
a:hover {
	border-bottom: 1px solid #f00;
}

body {
    font-size: 10px;
}

#footer p {
    margin-left: 5px;
    
}

#footer {
    padding-top: 10px;
}
</style>
<title></title>
</head>
<body onload="startMap()">

 <div id="wrap">
   <div id="header">
    <h1>
     <a href="http://blogug.ch/" title="independent blog usergroup"><span>blog ug</span></a>
    </h1>
    <ul id="nav">

     <li class="first-child">
      <a href="http://list.blogug.ch/">list</a>
     </li>
     <li>
      <a href="http://ping.blogug.ch/">ping</a>
     </li>
     <li>
      <a href="http://top100.blogug.ch/">top100</a>

     </li>
     <li>
      <a href="http://planet.blogug.ch/" >planet</a>
     </li>
     <li>
      <a href="http://stats.blogug.ch/">stats</a>
     </li>
     
     <li>
      <a href="http://planet.blogug.ch/map/" class="current">map</a>
     </li>
     
    </ul>

   </div>
   <div id="middlecontent">

   <div id="mapContainer">
   
   </div>

   <p>

   <input type="checkbox" onclick="toggleLayer(this)"

<?php 
if (!empty($_GET['today'])) {
    print "checked='checked'";
}

?>
/>only blogs with posts from the last 24 hours

| <a href="http://planet.blogug.ch/map/" id="permalink">Permalink</a>
</p>
<div id="footer">


    <p>
    swiss blogs data from  <a href="http://blogug.ch/">blogug.ch</a>. 
    More about this map <a href="http://blog.bitflux.ch/archive/2006/09/12/high-resolution-map-of-latest-swiss-blog-posts.html">here</a> and <a href="http://blog.bitflux.ch/archive/2006/09/07/blogug-map-in-google-earth.html">here</a>
    </p>

    <p>Created and maintained by <a href="http://chregu.tv/">Christian Stocker</a>. Design by <a href="http://sis.slowli.com">sis</a>.
    
    </p>
    <br/>
    <br/>

    <!--
    <rdf:RDF xmlns="http://web.resource.org/cc/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
    <Work rdf:about="">
    <license rdf:resource="http://creativecommons.org/licenses/by/2.5/" />
    </Work>
    </rdf:RDF>
    -->
</div>


</div>
   


   
</div>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">                 
_uacct = "UA-424540-3";
urchinTracker();
</script>
</body>
</html>
