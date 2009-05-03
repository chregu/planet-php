<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://purl.org/rss/1.0/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<xsl:import href="../inc/options.xsl"/>
    
<xsl:output method="xml" indent="yes" omit-xml-declaration="no"/>




    <xsl:template match="/">
<xsl:processing-instruction name="xml-stylesheet">
 href="http://www.w3.org/2000/08/w3c-synd/style.css" type="text/css"
</xsl:processing-instruction>
        <rdf:RDF>
            <channel rdf:about="{$channelLink}">

                <title>
                    <xsl:value-of select="$channelTitle"/>
                </title>
                <link>
                    <xsl:value-of select="$channelLink"/>
                </link>
                <description>
                    <xsl:value-of select="$channelDescription"/>
                </description>
                <dc:language>en</dc:language>
                <dc:date>
                    <xsl:value-of select="/planet/entries/entry[1]/date_iso"/>
                </dc:date>

                <dc:creator>NN</dc:creator>
                <admin:generatorAgent rdf:resource="http://planet-php.net"/>
                <admin:errorReportsTo rdf:resource="mailto:chregu@bitflux.ch"/>
                <sy:updatePeriod>hourly</sy:updatePeriod>
                <sy:updateFrequency>1</sy:updateFrequency>
                <sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>


                <items>
                    <rdf:Seq>
                        <xsl:for-each select="/planet/entries/entry">
                            <rdf:li rdf:resource="{link}"/>
                        </xsl:for-each>
                    </rdf:Seq>
                </items>

            </channel>
            <xsl:apply-templates select="/planet/entries/entry"/>
        </rdf:RDF>
    </xsl:template>
    <xsl:template match="entries/entry">

        <item rdf:about="{link}">
            <title>
               <xsl:if test="haswerbung/text() = 1"><xsl:value-of select="$sponsoredEntries"/> </xsl:if>

                <xsl:value-of select="title"/>
            </title>
            <link>
                <xsl:value-of select="link"/>
            </link>
      
            <dc:date>
                <xsl:value-of select="date_iso"/>
            </dc:date>
            <dc:creator>
		<xsl:choose>
		<xsl:when test="string-length(blog_author) &gt; 0 ">
                <xsl:value-of select="blog_author"/>
<xsl:if test="blog_dontshowblogtitle = 0"> (<xsl:value-of select="blog_title"/>) </xsl:if>
		</xsl:when>
		<xsl:otherwise>
                <xsl:value-of select="blog_title"/>
		</xsl:otherwise>
	       </xsl:choose>

            </dc:creator>
   <!--         <dc:subject>Blogging</dc:subject>-->
            <description>
                <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                <xsl:value-of disable-output-escaping="yes" select="description"/>
                <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
            </description>
            <content:encoded>
                <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                <xsl:value-of disable-output-escaping="yes" select="content_encoded"/>
                <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
            </content:encoded>
        </item>
    </xsl:template>
    
    


</xsl:stylesheet>
