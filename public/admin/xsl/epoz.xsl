<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
 xmlns:metal="http://xml.zope.org/namespaces/metal"
xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <xsl:output method="xml" indent="yes" omit-xml-declaration="no"/>
    <xsl:param name="src"/>
    
   

    <xsl:template match="*">

        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="/xhtml:html/xhtml:body//xhtml:iframe[@id='epoz-editor']/@src">
        <xsl:attribute name="src"><xsl:value-of select="$src"/></xsl:attribute>
           <xsl:attribute name="dst"><xsl:value-of select="$src"/></xsl:attribute>
    </xsl:template>
    
    <xsl:template match="/xhtml:html/xhtml:head//metal:css">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:apply-templates/>
        
        </xsl:copy>
        
        
    </xsl:template>
    
    
    <xsl:template match="@*">
     <xsl:copy/>
    </xsl:template>
    <xsl:template match="@src">
        <xsl:attribute name="src">../epoz/common/<xsl:value-of select="."/></xsl:attribute>
    </xsl:template>
<xsl:template match="@href">
        <xsl:attribute name="href">../epoz/common/<xsl:value-of select="."/></xsl:attribute>
    </xsl:template>
</xsl:stylesheet>
