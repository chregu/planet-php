<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
        xmlns:php="http://php.net/xsl"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:include href="common.xsl"/>
    
    <xsl:param name="error" />
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
        <div id="middlecontent">
            <xsl:apply-templates select="/html/body" mode="xhtml"/>
        </div>
    </xsl:template>
      

    <xsl:template name="htmlheadtitle">
  Planet PHP  - <xsl:value-of select="/html/head/title"/>
    </xsl:template>
   

    <xsl:template match="input/@value[. = '']" mode="xhtml">
        <xsl:attribute name="{local-name()}">
            <xsl:value-of select="php:functionString('getPostValue',../@name)"/>
        </xsl:attribute>
    </xsl:template>

     <xsl:template match="textarea" mode="xhtml">
        <textarea>
         <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:value-of select="php:functionString('getPostValue',@name)"/>
        </textarea>
    </xsl:template>
    
    
    <xsl:template match="error" mode="xhtml">
    <xsl:if test="$error != ''">
    <h2><xsl:value-of select="$error"/>
    </h2>
    </xsl:if>
    </xsl:template>
    
    
    
    <xsl:template match="*" mode="xhtml">
        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:apply-templates mode="xhtml"/>
        </xsl:element>
    </xsl:template>



    <xsl:template match="@*" mode="xhtml">
        <xsl:copy-of select="."/>
    </xsl:template>

</xsl:stylesheet>