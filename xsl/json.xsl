<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:php="http://php.net/xsl"
>
    <xsl:output method="xml" indent="no" omit-xml-declaration="no"/>

    <xsl:param name="cb" />


    <xsl:template match="/">
    <xsl:if test="$cb != '0'">
    <xsl:value-of select="$cb"/>(
    </xsl:if>[<xsl:apply-templates select="/planet/entries/entry"/>]   
<xsl:if test="$cb != '0'">);
    </xsl:if>
</xsl:template>

    <xsl:template match="entries/entry">{"title": <xsl:value-of select="php:functionString('json_encode',title)"/>,
"link": <xsl:value-of select="php:functionString('json_encode',link)"/>, 
"author": <xsl:choose>
            <xsl:when test="string-length(blog_author) &gt; 0 ">           
            <xsl:value-of select="php:functionString('json_encode',blog_author)"/>     
            </xsl:when>
            <xsl:otherwise>
            <xsl:value-of select="php:functionString('json_encode',blog_title)"/>
            </xsl:otherwise>
           </xsl:choose>,
"guid": <xsl:value-of select="php:functionString('json_encode',guid)"/>,
"updated": <xsl:value-of select="php:functionString('json_encode',date_iso)"/>,
"content": <xsl:call-template name="description"/>,
"id": <xsl:value-of select="id"/>
}<xsl:if test="position() != last()">,</xsl:if>
    </xsl:template>
    
    <xsl:template name="description">
        <xsl:choose>
            <xsl:when test="string-length(content_encoded) > 0">
                <xsl:value-of disable-output-escaping="yes" select="php:functionString('json_encode',content_encoded)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of disable-output-escaping="yes" select="php:functionString('json_encode',description)"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
