<?xml version="1.0" encoding="ISO-8859-1"?>

<xsl:stylesheet version="1.0"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#">

<!--
Author: Zoran Kovacevic (http://www.kovacevic.nl/blog).
License: GPL (http://www.opensource.org/licenses/gpl-license.php)
-->
	<xsl:template match="/">

		
		<xsl:apply-templates select="/rss/channel"/>
	</xsl:template>
	
	<xsl:template match="/rss/channel">
	
		<kml xmlns="http://earth.google.com/kml/2.1">
			<Document>
			
				<Style id="poi">
					<IconStyle>
					<scale>0.5</scale>
						<Icon>
							<href>http://planet.blogug.ch/images/poi/blogug100.png</href>
						</Icon>
					</IconStyle>
				</Style>
			
				<name>Blogposts from Switzerland</name>
				<Folder>
					<name>Blogposts from Switzerland</name>
					<xsl:for-each select="item">
						
						<xsl:if test="geo:lat/text() and geo:long/text()">
							<Placemark id="{generate-id()}">
								<name>
									<xsl:value-of select="title"/>
								</name>
								<description>
									<xsl:copy-of select="description/node()"/>
								
								</description>
								<LookAt>
									<longitude>
										<xsl:value-of select="geo:long"/>
									</longitude>
									<latitude>
										<xsl:value-of select="geo:lat"/>
									</latitude>
									<range>4000</range>
									<tilt>45</tilt>
									<heading>0</heading>
								</LookAt>
								<styleUrl>#poi</styleUrl>
								<Point>
									<coordinates><xsl:value-of select="geo:long"/>,<xsl:value-of select="geo:lat"/>,0</coordinates>
								</Point>
							</Placemark>
						</xsl:if>
					</xsl:for-each>
				</Folder>
			</Document>
		</kml>
	
	</xsl:template>

</xsl:stylesheet>
