<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:s="http://www.oscom.org/2002/SlideML/0.9/" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:html="http://www.w3.org/1999/xhtml" 
    xmlns:fo="http://www.w3.org/1999/XSL/Format"
    >
    
    <xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
    <xsl:param name="page" select="1"/>
    <xsl:include href="Xhtml2fo.xsl"/>
    <xsl:template match="/s:slideset">
        
        <xsl:for-each select="s:slide[position() = $page]">
            
            <!-- atributes -->
            
            <!-- paper size -->
            <xsl:param name="paper-width">210mm</xsl:param>
            <xsl:param name="paper-height">297mm</xsl:param>
            <xsl:param name="paper-margin-top">8mm</xsl:param>
            <xsl:param name="paper-margin-bottom">8mm</xsl:param>
            <xsl:param name="paper-margin-left">8mm</xsl:param>
            <xsl:param name="paper-margin-right">8mm</xsl:param>
            
            <!-- header and hooter extension  -->
            <xsl:param name="header-extention-size">5mm</xsl:param>
            <xsl:param name="footer-extention-size">5mm</xsl:param>
            
            <!-- printing item in header and footer -->
            <xsl:param name="title-print-in-header" select="true()"/>
            <xsl:param name="page-number-print-in-footer" select="true()"/>
            
            <!-- body -->
            <xsl:param name="body-start-indent">5mm</xsl:param>
            <xsl:param name="body-end-indent">5mm</xsl:param>
            <!-- copied from html:head -->            
            <fo:root>
                <!-- copied from html:body -->            
                <xsl:call-template  name="layout-master-set"/>

                <fo:page-sequence master-reference="BodyPage">
                    <fo:static-content flow-name="xsl-region-before">
                        <fo:block text-align="center" font-size="small">
                            <xsl:if test="$title-print-in-header"><xsl:value-of select="/html:html/html:head/html:title" /></xsl:if>
                        </fo:block>
                    </fo:static-content>
                    <fo:static-content flow-name="xsl-region-after">
                        <xsl:if test="$page-number-print-in-footer">
                            <fo:block text-align="center" font-size="small">
                                - <fo:page-number/> -
                            </fo:block>
                        </xsl:if>
                    </fo:static-content>
                    <fo:flow flow-name="xsl-region-body" >
                        <xsl:apply-templates select="s:title"/>
                        <xsl:apply-templates select="s:content/*"/>
                        
                        
                    </fo:flow>
                </fo:page-sequence>
                
                
                
            </fo:root>
            
        </xsl:for-each>
        
        
        
    </xsl:template>
    
    <xsl:template match="s:title">
        <!-- copied from html:h1 -->
        <fo:block  xsl:use-attribute-sets="h1">
            <xsl:apply-templates />
        </fo:block>
    </xsl:template>
    
    
</xsl:stylesheet>
