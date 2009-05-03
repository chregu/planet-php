<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:php="http://php.net/xsl"
        xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
        xmlns:math="http://exslt.org/math"
        extension-element-prefixes="math">

    <xsl:import href="../inc/options.xsl"/>


    <xsl:template match="/">

        <rss
                xmlns:ymaps="http://api.maps.yahoo.com/Maps/V1/AnnotatedMaps.xsd"
                xmlns:rs="http://www.microsoft.com/schemas/rss/sse"
                xmlns:georss="http://www.georss.org/georss"
                xmlns:gml="http://www.opengis.net/gml"
                xmlns:bluemaps="http://blue24.com/2006/#"
                xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
                version="2.0">
            <channel>
                <title>
                    <xsl:value-of select="$channelTitle"/>
                </title>
                <link>
                    <xsl:value-of select="$channelLink"/>
                </link>
                <description>
                    <xsl:value-of select="$channelDescription"/>
                </description>
                <language>en</language>
                <ttl>60</ttl>

                <xsl:choose>
                    <xsl:when test="php:functionString('popoon_helpers_globals::GET','icon') = 'local'">
                        <category
                                bluemaps:description="Blogug 0 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_0_day</category>
                        <category
                                bluemaps:description="Blogug 1 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_1_day</category>

                        <category
                                bluemaps:description="Blogug 2 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_2_day</category>

                        <category
                                bluemaps:description="Blogug 3 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_3_day</category>

                        <category
                                bluemaps:description="Blogug 4 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_4_day</category>

                        <category
                                bluemaps:description="Blogug 5 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_5_day</category>

                        <category
                                bluemaps:description="Blogug 6 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_6_day</category>

                        <category
                                bluemaps:description="Blogug 7 Day"
                                bluemaps:smallIcon="http://mobi2.endoxon.com/images/local/local_poi_8_red.png"
                                bluemaps:bigIcon="http://www.local.ch/static/images/pois/red.png">blogug_7_day</category>
                        


                    </xsl:when>
                    <xsl:otherwise>
                        <category
                                bluemaps:description="Blogug 0 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug100.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug100.png">blogug_0_day</category>
                        <category
                                bluemaps:description="Blogug 1 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug90.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug90.png">blogug_1_day</category>
                        <category
                                bluemaps:description="Blogug 2 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug80.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug80.png">blogug_2_day</category>
                        <category
                                bluemaps:description="Blogug 3 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug70.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug70.png">blogug_3_day</category>
                        <category
                                bluemaps:description="Blogug 4 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug60.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug60.png">blogug_4_day</category>
                        <category
                                bluemaps:description="Blogug 5 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug50.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug50.png">blogug_5_day</category>
                        <category
                                bluemaps:description="Blogug 6 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug40.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug40.png">blogug_6_day</category>
                        <category
                                bluemaps:description="Blogug 7 Day"
                                bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug30.png"
                                bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug30.png">blogug_7_day</category>
           <!--     <category
                        bluemaps:description="Blogug 8 Day"
                        bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug20.png"
                        bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug20.png">blogug_8_day</category>
                <category
                        bluemaps:description="Blogug 9 Day"
                        bluemaps:smallIcon="http://planet.blogug.ch/images/poi/blogug10.png"
                        bluemaps:bigIcon="http://planet.blogug.ch/images/poi/blogug10.png">blogug_9_day</category>
               -->

                    </xsl:otherwise>
                </xsl:choose>

                <xsl:apply-templates select="/planet/entries/entry"/>
            </channel>

            <xsl:text>
            </xsl:text>

        </rss>
    </xsl:template>

    <xsl:template match="entries/entry" name="entry">
    <xsl:param name="blogcoor" select="'false'"/>
    <xsl:variable name="geotagged">
                <xsl:if test="$blogcoor = 'false' and lon/text() != 0 and blog_lon/text() != 0 and lon/text() != blog_lon/text()">

                    <xsl:variable name="dlat" select="(number(math:power(2,number(lat/text()) - number(blog_lat/text()))) )"/>
                    <xsl:variable name="dlon" select="(number(math:power(2,number(lon/text()) - number(blog_lon/text()))) )"/>
                    <xsl:if test="(number($dlat) != 1 and number($dlon) != 1) and $dlat + $dlon &gt; 0.00001">true</xsl:if>

                </xsl:if>
                
                </xsl:variable>
        <item>
            <title>Blog: <xsl:value-of select="blog_title"/>
            </title>
            <xsl:choose>
                <xsl:when test="link/text() != ''">
                    <link>
                        <xsl:value-of select="link"/>
                    </link>
                    <guid isPermaLink="false">
                        <xsl:value-of select="link"/>
                    </guid>

                </xsl:when>
                <xsl:otherwise>
                    <guid isPermaLink="false">
                        <xsl:value-of select="blog_title"/>-<xsl:value-of select="title"/>
                    </guid>
                </xsl:otherwise>
            </xsl:choose>
            <pubDate>
                <xsl:value-of select="date_rfc"/>
            </pubDate>
            <xsl:choose>

                <xsl:when test="$blogcoor = 'false' and lon/text() and lon != 0">
                    <geo:lat>
                        <xsl:value-of select="lat"/>
                    </geo:lat>
                    <geo:long>
                        <xsl:value-of select="lon"/>
                    </geo:long>
                </xsl:when>

                <xsl:when test="blog_lon/text() and blog_lon != 0">
                    <geo:lat>
                        <xsl:value-of select="blog_lat"/>
                    </geo:lat>
                    <geo:long>
                        <xsl:value-of select="blog_lon"/>
                    </geo:long>
                </xsl:when>
            </xsl:choose>
            <xsl:variable name="daysago" select="php:functionString('popoon_helpers_date::daydiff',date_iso)"/>
            <category>blogug_<xsl:value-of select="$daysago"/>_day</category>
 
            <description>
                <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                <xsl:choose>
                    <xsl:when test="$daysago = 0">
                In the last 24 hours
                </xsl:when>
                    <xsl:when test="$daysago = 1">
                1 day ago
                </xsl:when>

                    <xsl:when test="$daysago >= 7">
                More than a week ago
                </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$daysago"/> days ago
                </xsl:otherwise>
                </xsl:choose>
                    (<xsl:value-of disable-output-escaping="yes" select="dc_date"/>)<br/>
                <a href="{link}">
                    <xsl:value-of disable-output-escaping="yes" select="title"/>
                </a>
               
                
                <xsl:if test="$geotagged = 'true' and $blogcoor = 'false' ">
                  <br/> Geotagged post. Main blog coordinates are on a difference place.
                </xsl:if>
                <xsl:if test="$blogcoor = 'true' ">
                <br/> This last post was done on a different place than here. These are only the main blog coordinates.
                </xsl:if>

                <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
            </description>
        </item>
         <xsl:if test="$geotagged = 'true' and $blogcoor = 'false' and count(preceding-sibling::entry/blog_id[text() = current()/blog_id/text()]) = 0">
         <xsl:call-template name="entry">
         <xsl:with-param name="blogcoor" select="'true'"/>
         </xsl:call-template>
         </xsl:if>
        <xsl:text>
            </xsl:text>
            
             

    </xsl:template>

</xsl:stylesheet>

