<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:s="http://www.oscom.org/2002/SlideML/0.9/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
    <xsl:param name="page" select="1"/>
    <xsl:template match="/s:slideset">
	<s:slide>
        	<xsl:apply-templates select="s:slide[position() = $page]/s:title"/>
        	<xsl:apply-templates select="s:slide[position() = $page]/s:content"/>
</s:slide>


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
