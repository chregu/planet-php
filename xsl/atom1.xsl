<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:php="http://php.net/xsl"

xmlns:geo="http://www.w3.org/2005/Atom" 
>
<xsl:import href="../inc/options.xsl"/>
    <xsl:output method="xml" indent="yes" omit-xml-declaration="no"/>


    <xsl:template match="/">

 <feed xmlns="http://www.w3.org/2005/Atom">

            <title>
                <xsl:value-of select="$channelTitle"/>
            </title>
            <link rel="alternate" type="text/html" href="{$channelLink}{substring-after(php:functionString('popoon_helpers_globals::encodedGET','path'),'atom/')}"/>
            <link rel="self" type="text/html" href="{$channelLink}{php:functionString('popoon_helpers_globals::encodedGET','path')}"/>
            <subtitle>
                <xsl:value-of select="$channelDescription"/>
            </subtitle>
            <id><xsl:value-of select="$channelLink"/><xsl:value-of select="php:functionString('popoon_helpers_globals::encodedGET','path')"/></id>
            <generator uri="http://planet-php.net/">
            Planet Ping Aggregator
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
                           <xsl:if test="haswerbung/text() = 1"><xsl:value-of select="$sponsoredEntries"/> </xsl:if>

            <xsl:value-of select="title"/>
       </title>
            <link rel="alternate" type="text/html" href="{link}" title="{title}"/>
            <author>
                <name>
    <xsl:choose>
                <xsl:when test="string-length(blog_author) &gt; 0 ">           
                <xsl:value-of select="blog_author" disable-output-escaping="yes"/>     
<xsl:if test="blog_dontshowblogtitle = 0"> (<xsl:value-of select="blog_title"/>) </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                <xsl:value-of select="blog_title" 
/>
                </xsl:otherwise>
               </xsl:choose> 
</name>
            </author>
            <id> 
            <xsl:value-of select="guid"/>
            </id>
            <updated> <xsl:value-of select="date_iso"/></updated>
            <published><xsl:value-of select="date_iso"/></published>
              <xsl:if test="string-length(tags) &gt; 0">
           
           <xsl:call-template name="printTags">
                <xsl:with-param name="tags" select="tags"/>
                </xsl:call-template>
            </xsl:if>
            
            <content type="html">
            <xsl:call-template name="description"/>
           
            </content>
            
            <xsl:choose>
            
            <xsl:when test="lon/text() and lon != 0">
              <geo:lat><xsl:value-of select="lat"/></geo:lat>
              <geo:long><xsl:value-of select="lon"/></geo:long>
              </xsl:when>
              
            <xsl:when test="blog_lon/text() and blog_lon != 0">
              <geo:lat><xsl:value-of select="blog_lat"/></geo:lat>
              <geo:long><xsl:value-of select="blog_lon"/></geo:long>
              </xsl:when>  
           </xsl:choose>
            
         
        </entry>
    </xsl:template>
     <xsl:template name="printTags">
        <xsl:param name="tags"/>
        <xsl:choose>
            <xsl:when test="contains($tags,' , ')"> 
                <xsl:variable name="tag" select="substring-before($tags,' , ')"/>
                <dc:subject><xsl:value-of select="$tag"/></dc:subject>
                <xsl:call-template name="printTags">
                    <xsl:with-param name="tags" select="substring-after($tags,' , ')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <dc:subject><xsl:value-of select="$tags"/></dc:subject>
            </xsl:otherwise>
        </xsl:choose>
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
