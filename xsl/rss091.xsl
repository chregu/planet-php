<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:param name="channelLink" select="'http://planet-php.net'"/>
    <xsl:param name="channelTitle" select="'Planet PHP'"/>
    <xsl:param name="channelDescription" select="'People blogging about PHP'"/>


    <xsl:template match="/">
<xsl:processing-instruction name="xml-stylesheet">
 href="http://www.w3.org/2000/08/w3c-synd/style.css" type="text/css"
</xsl:processing-instruction>  
      <rss version="0.91">
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

                <xsl:apply-templates select="/planet/entries/entry"/>
            </channel>
        </rss>
    </xsl:template>

    <xsl:template match="entries/entry">
        <item>
            <title>
<xsl:value-of select="title"/> - <xsl:choose>
                <xsl:when test="string-length(blog_author) &gt; 0 ">           
                <xsl:value-of select="blog_author"/> <xsl:if test="blog_dontshowblogtitle = 0"> (<xsl:value-of select="blog_title"/>) </xsl:if>
                </xsl:when>
                <xsl:otherwise>
               <xsl:value-of select="blog_title"/>
                </xsl:otherwise>
               </xsl:choose> 
            </title>
            <link>
                <xsl:value-of select="link"/>
            </link>
     <pubDate>
                    <xsl:value-of select="date_rfc"/>
                </pubDate>


            <description>
                <xsl:call-template name="description"/>

            </description>
        </item>
    </xsl:template>

    <xsl:template name="description">
        <xsl:choose>
            <xsl:when test="string-length(content_encoded) > 0">
                <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                <xsl:value-of disable-output-escaping="yes" select="content_encoded"/>
                <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                <xsl:value-of disable-output-escaping="yes" select="description"/>
                <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>

