<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
 xmlns="http://www.w3.org/1999/xhtml"
>
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
/>
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
	<xsl:apply-templates />

    </xsl:template>
<xsl:template match="body[@id='serendipity_comment_page']">
        <div id="middlecontent">
	<xsl:apply-templates/>
</div>

</xsl:template>

<xsl:template match="div[@id='content']">
        <div id="middlecontent">
	<xsl:apply-templates/>
</div>
</xsl:template>
	<xsl:template match="div[@class='serendipity_Entry_Date']">
	  <xsl:apply-templates select="h4"/>
</xsl:template>

    <xsl:template match="h4">
        <div class="box">
            <fieldset>
                <legend>
                    <xsl:value-of select="../h3"/>
                </legend>
                <a href="{a/@href}" class="blogTitle">
                    <xsl:value-of select="."/>
                </a>
                <xsl:variable name="thisEntry" select="following-sibling::div[@class='serendipity_entry'][position() = 1]"/>
		<xsl:apply-templates select="$thisEntry"/>
            </fieldset>
        </div>
    </xsl:template>

<xsl:template match="span[@class='serendipity_entry_body_folded']">
                <div class="feedcontent">
<xsl:apply-templates/>               
                </div>
</xsl:template>
<xsl:template match="div[@class='serendipity_entryFooter']">
                <div class="feedcontent">
			<xsl:apply-templates/>      
		</div>
</xsl:template>

<xsl:template match="div[@id= 'content']/div[@class='serendipity_entryFooter']">
<div class="box">
<fieldset>
			<xsl:apply-templates/>               
</fieldset>
</div>
</xsl:template>
<!--
-->
<xsl:template match="div[@class='serendipity_commentsTitle']">
<xsl:choose>
<xsl:when test="ancestor::div[@class='serendipity_entry']">
                <h4><xsl:copy-of select="*|text()"/></h4>
		<xsl:for-each select="../div[@class='serendipity_comment']">
			<xsl:copy-of select="."/><br/>
		</xsl:for-each>
</xsl:when>
<xsl:otherwise>
        <div class="box">
            <fieldset>
                <legend><xsl:copy-of select="*|text()"/></legend>
		<xsl:for-each select="../div[@class='serendipity_comment']">
			<xsl:copy-of select="."/><br/>
		</xsl:for-each>
            </fieldset>
        </div>

</xsl:otherwise>

</xsl:choose>
    </xsl:template>
<xsl:template match="div[@class='serendipityCommentForm']">
</xsl:template>
<xsl:template match="div[@class='serendipity_comment']">
</xsl:template>

<xsl:template match="div[@class='serendipity_comment_source']">
<div class="box">
<fieldset>
<xsl:apply-templates/>
</fieldset>
</div>
</xsl:template>


<xsl:template match="div[@class='serendipity_commentsTitle'][position() = last()]">
<xsl:choose>

		<xsl:when test="text() ='Trackbacks'">
<h2>Trackbacks</h2>
</xsl:when>
<xsl:otherwise>
<xsl:choose>
<xsl:when test="ancestor::div[@class='serendipity_entry']">
                <h4><xsl:copy-of select="*|text()"/></h4>

		<xsl:copy-of select="../div[@class='serendipityCommentForm']"/>

</xsl:when>
<xsl:otherwise>
        <div class="box">
            <fieldset>
                <legend><xsl:copy-of select="*|text()"/></legend>

		<xsl:copy-of select="../div[@class='serendipityCommentForm']"/>
            </fieldset>
        </div>
</xsl:otherwise>
</xsl:choose>

</xsl:otherwise>
</xsl:choose>
    </xsl:template>

    

    <xsl:template name="htmlheadtitle">
  Planet PHP  - Blog 

<xsl:if test="count(//h4) = 1">
- <xsl:value-of select="//h4"/>
</xsl:if>
   </xsl:template>

<xsl:template match="div[@id='serendipityRightSideBar']">
<div id="rightcol">
<xsl:apply-templates/>
<xsl:call-template name="commonRightBoxes"/>
</div>
</xsl:template>

<xsl:template match="div[@class='serendipitySideBarItem']">
<div class="menu">
<fieldset><legend><xsl:value-of select="h3"/></legend>
<xsl:apply-templates select="div[@class='serendipitySideBarContent']"/>
</fieldset>
</div>

</xsl:template>

<xsl:template match="script">
    <xsl:copy>
        <xsl:for-each select="@*">
            <xsl:copy/>
        </xsl:for-each>
		<xsl:value-of disable-output-escaping="yes" select="."/>
	</xsl:copy>
</xsl:template>
<xsl:template match="img|div|A|a|font|br|ul|li|ol|table|tr|td|hr|form|input|textarea|select|option|span|embed">
    <xsl:copy>
        <xsl:for-each select="@*">
            <xsl:copy/>
        </xsl:for-each>
        <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>

<!-- here come the tags which don't have attributes...-->
<xsl:template match="i|b|h1|h2|h3|h5|h6|pre|code|p|sub|sup|br">
    <xsl:copy>
        <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>
<!--<xsl:template name="bodylogo">
  <a href="/blog/">
                <img src="./themes/img/php-planet.png" width="275" height="70" hspace="30" alt="Planet PHP" title="Planet PHP" border="0"/>
            </a>
</xsl:template>    
-->

</xsl:stylesheet>
