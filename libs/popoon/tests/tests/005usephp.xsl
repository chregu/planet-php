<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
   <xsl:param name="title" select="'sample2html'"/>
    <xsl:template match="/">
        
        <html>
            <head>
                <title><xsl:value-of select="$title"/></title>
            </head>
            <body>
                
                <h1><xsl:value-of select="/html/body"/></h1>
                 <xsl:processing-instruction name="php">
                    <xsl:text> <![CDATA[
                    echo "\nThis is a PHP echo string\n";
                    ]]>
                    </xsl:text>
                </xsl:processing-instruction>
            </body>                
        </html>
    </xsl:template>
	
    
</xsl:stylesheet>
