<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
<xsl:import href="main-list.xsl"/>
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    
    
    <xsl:template name="middlecol">
     <div id="middlecontent" class="static">
    <xsl:copy-of select="/xhtml:html/xhtml:body/node()"/>
     </div>
    </xsl:template>
    
      
         <xsl:template name="htmlheadtitle">
  Planet Switzerland  - <xsl:value-of select="/xhtml:html/xhtml:head/xhtml:title"/>
   </xsl:template>
    
    </xsl:stylesheet>