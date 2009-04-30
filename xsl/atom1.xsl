<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" indent="yes" omit-xml-declaration="no"/>

    <xsl:param name="channelLink" select="'http://www.planet-php.net/'"/>
    <xsl:param name="channelTitle" select="'Planet PHP'"/>
    <xsl:param name="channelDescription" select="'People blogging about PHP'"/>


    <xsl:template match="/">

 <feed xmlns="http://www.w3.org/2005/Atom">

            <title>
                <xsl:value-of select="$channelTitle"/>
            </title>
            <link rel="alternate" type="text/html" href="{$channelLink}"/>
            <link rel="self" type="text/html" href="{$channelLink}atom/"/>
            <subtitle>
                <xsl:value-of select="$channelDescription"/>
            </subtitle>
            <id>
                <xsl:value-of select="$channelLink"/>
            </id>
            <generator uri="http://planet-php.net/">
            Planet PHP Aggregator
            </generator>
            <!--<copyright type="text/plain" mode="escaped">All rights reserved, all wrongs reversed. Bring lawyers, guns, and money</copyright>
            -->

            <updated>
                <xsl:value-of select="/planet/entries/entry[1]/date_iso"/>
            </updated>

            <xsl:apply-templates select="/planet/entries/entry"/>
        </feed>
    </xsl:template>

    <xsl:template match="entries/entry">
        <entry xmlns="http://www.w3.org/2005/Atom">
            <title type="text">	
       <xsl:value-of select="title"/>
       </title>
            <link rel="alternate" type="text/html" href="{link}" title="{title}"/>
            <author>
                <name>
    <xsl:choose>
                <xsl:when test="string-length(blog_author) &gt; 0 ">           
                <xsl:value-of select="blog_author"/>     
<xsl:if test="blog_dontshowblogtitle = 0"> (<xsl:value-of select="blog_title"/>) </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                <xsl:value-of select="blog_title"/>
                </xsl:otherwise>
               </xsl:choose> 
</name>
            </author>
            <id> 
            <xsl:value-of select="guid"/>
            </id>
            <updated> <xsl:value-of select="date_iso"/></updated>
            <published><xsl:value-of select="date_iso"/></published>
          
            <content type="html">
            <xsl:call-template name="description"/>
            </content>
        </entry>
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
