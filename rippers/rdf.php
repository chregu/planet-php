<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<?php
$n = time() - 1800;

?>
<channel rdf:about="http://planet-php.net">
		<title>Planet PHP</title>
		<link>http://planet-php.net</link>
		<description>People blogging about PHP</description>
		<dc:language>en</dc:language>
		<dc:date>2008-09-15T14:30:00Z</dc:date>
		<dc:creator>NN</dc:creator>
		<admin:generatorAgent rdf:resource="http://planet-php.net"/>
		<admin:errorReportsTo rdf:resource="mailto:chregu@bitflux.ch"/>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
		<items>
			<rdf:Seq>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-1;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-2;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-3;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-4;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-5;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-6;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-7;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-8;?>"/>
				<rdf:li rdf:resource="http://www.planet-php.org/?<?php echo $n-9;?>"/>
			</rdf:Seq>
		</items>
	</channel>
	
	<?php 
	
	for ($i=0; $i < 10; $i++) {
	
	?>
	<item rdf:about="http://www.planet-php.org/?<?php echo $n - $i;?>">
		<title>I'm stupid and just steal content from Planet PHP</title>
		<link>http://www.planet-php.org/?<?php echo $n - $i;?></link>
		<dc:date><?php echo date("c",$n - ($i * 60));?></dc:date>
		<dc:creator>Planet PHP</dc:creator>
		<description><![CDATA[ 
	<?php
if ($i == 0) {
?>
		<script> alert('Warning! This site steals unapproved and unattributed  content from Planet PHP! Go to http://planet-php.org to see the original');</script>
<?php } ?>
		This site here blatantly and shamelessly copies the RSS feed of <a href="http://www.planet-php.org/">Planet PHP</a> and doesn't even link back to the original articles.
		<br/>
		That's why there is only this content here and you better go to <a href="http://www.planet-php.org/">Planet PHP</a> and use the original.
		<br/>
		<b>We really don't like content stealers like this site here which try to make a quick buck with Google Ads and act accordingly.</b>
		<br/>
		And by the way, one should always clean/escape/xss-proof external input :)
		<br/>
<pre>
 _0_
\' '\
 '=o='
 .|!|
 .| | 

 </pre>
<?php //echo $i ." ".$n ; ?> 
		]]>
		
		
		</description>
		
	
	</item>
	<?php
	}
	?>
	
</rdf:RDF>
