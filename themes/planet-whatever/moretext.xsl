<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"
        xmlns:php="http://php.net/xsl"
        exclude-result-prefixes="php">
    <xsl:param name="webroot"/>

    <xsl:template match="/">

        <div>
        
        <xsl:for-each select="/planet/entries/entry[1]">
        <a style="float: left;" onclick="return openMore('div{id}',false)" href="#" class="listMore">x</a>
        
        <h5> <a href="{blog_link}"><xsl:value-of select="blog_title"/></a></h5>
       <a href="{link}" class="blogTitle">
                    <xsl:value-of select="title"/>
                </a>
                <xsl:text> </xsl:text> 
            (<xsl:value-of select="dc_date"/>)
<div class="feedcontent" >
<xsl:choose>
<xsl:when test="string-length(content_encoded) &gt; 0">
             <xsl:value-of select="content_encoded" disable-output-escaping="yes"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="description" disable-output-escaping="yes"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
                <xsl:if test="string-length(tags) &gt; 0">
                Planet Tags: <xsl:call-template name="printTags">
                <xsl:with-param name="tags" select="tags"/>
                </xsl:call-template>
                <br/><br/>
                </xsl:if>
               <!-- <a href="{link}">Link</a>-->
        
    
        
        </xsl:for-each>
        </div>
    </xsl:template>
   <xsl:template name="printTags">
        <xsl:param name="tags"/>
        <xsl:choose>
            <xsl:when test="contains($tags,' , ')"> 
                <xsl:variable name="tag" select="substring-before($tags,' , ')"/>
                <a href="{$webroot}tag/{$tag}"><xsl:value-of select="$tag"/></a>, 
                <xsl:call-template name="printTags">
                    <xsl:with-param name="tags" select="substring-after($tags,' , ')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <a href="{$webroot}tag/{$tags}"><xsl:value-of select="$tags"/></a>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
