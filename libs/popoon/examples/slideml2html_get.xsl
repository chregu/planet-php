<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:s="http://www.oscom.org/2002/SlideML/0.9/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
    <xsl:param name="page" select="1"/>
    <xsl:template match="/s:slideset">
        
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>Popoon Examples - PHP Conference 2002</title>
            </head>
            <body>
                <xsl:if test="s:slide[position() = $page - 1]">
                    <a href="./examples.php?sitemap=sitemap3.xml&amp;page={$page - 1}">prev</a>
                </xsl:if>
                <xsl:text> </xsl:text>
                <xsl:if test="s:slide[position() = $page + 1]">				<a href="./examples.php?sitemap=sitemap3.xml&amp;page={$page + 1}">next</a></xsl:if>
                
                <xsl:for-each select="s:slide[position() = $page]">
                    <h1><xsl:value-of select="s:title"/></h1>
                    <xsl:apply-templates select="s:content"/>
                    
                </xsl:for-each>
                
            </body>                
        </html>
    </xsl:template>
    <xsl:template match="*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
    
</xsl:stylesheet>
