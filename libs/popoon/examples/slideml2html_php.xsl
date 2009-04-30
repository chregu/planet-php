<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:s="http://www.oscom.org/2002/SlideML/0.9/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
    <xsl:variable name="page" select="1"/>
    <xsl:template match="/s:slideset">
        
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>Popoon Examples - PHP Conference 2002</title>
            </head>
            <body>
                
                <h1><xsl:value-of select="s:slide[position() = $page]/s:title"/></h1>
                <xsl:apply-templates select="s:slide[position() = $page]/s:content"/>
                <xsl:processing-instruction name="php">
                    <xsl:text> <![CDATA[
					echo "Date/Time: " . strftime("%c");
					echo "<br />";
					echo "IP: " . $_SERVER["REMOTE_ADDR"];
					]]>
                    </xsl:text>
                </xsl:processing-instruction>
            </body>                
        </html>
    </xsl:template>
    
    <xsl:template match="s:content">	
        <xsl:apply-templates/>
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
