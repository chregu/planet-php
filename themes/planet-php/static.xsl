<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
<xsl:include href="common.xsl"/>
    <xsl:template match="/">

        <html>

            <xsl:call-template name="htmlhead"/>
            <body>
                <xsl:call-template name="bodyhead"/>

                <xsl:call-template name="middlecol"/>
                
                
            </body>
        </html>
    </xsl:template>
    
    
    <xsl:template name="middlecol">
     <div id="content">
    <xsl:copy-of select="/html/body"/>
     </div>
    </xsl:template>
    
      
         <xsl:template name="htmlheadtitle">
  Planet PHP  - <xsl:value-of select="/html/head/title"/>
   </xsl:template>
    
    </xsl:stylesheet>