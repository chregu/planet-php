<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                xmlns:svg="http://www.w3.org/2000/svg"
                xmlns:office="http://openoffice.org/2000/office"
                xmlns:style="http://openoffice.org/2000/style"
                xmlns:text="http://openoffice.org/2000/text"
                xmlns:table="http://openoffice.org/2000/table"
                xmlns:draw="http://openoffice.org/2000/drawing">

<!--
XML Style Sheet for OpenOffice.org documents

When run through a XSLT processor, this style sheet will convert OpenOffice.org XML
documents to rather clean (X)HTML which will still be manually editable.

Written by Philipp "philiKON" von Weitershausen (philikon@philikon.de),
published under the terms of the Mozilla Public License (MPL)

$Id: ooo2html.xsl,v 1.1 2004/02/02 14:02:47 chregu Exp $
-->

<xsl:output method="html" encoding="UTF-8"/>


<xsl:template match="/">
<html>
  <xsl:apply-templates />
</html>
</xsl:template>


<xsl:template match="//office:body">
<body>
  <xsl:apply-templates />
</body>
</xsl:template>



<!-- Text Content ... pages 123ff file format documentation-->

<!-- Paragraph -->
<xsl:template match="//text:p">
<p>
  <xsl:if test="@text:style-name">            <!-- if this attribute is there, it refers to a style definition -->
    <xsl:call-template name="apply-style" />  <!-- thus, add CSS styles -->
  </xsl:if>
  <xsl:apply-templates />
</p>
</xsl:template>


<!-- Space -->
<xsl:template match="//text:s">
  <xsl:for-each select="@text:c">   <!-- XXX range() function or something... -->
    <xsl:text>&#160;</xsl:text>
  </xsl:for-each>
</xsl:template>


<!-- Tab Stop-->
<xsl:template match="//text:tab-stop">
  <xsl:text>	</xsl:text>
</xsl:template>


<!-- Span -->
<xsl:template match="//text:span">
<span>
  <xsl:if test="@text:style-name">            <!-- if this attribute is there, it refers to a style definition -->
    <xsl:call-template name="apply-style" />  <!-- thus, add CSS styles -->
  </xsl:if>
  <xsl:apply-templates />
</span>
</xsl:template>


<!-- Link -->
<xsl:template match="//text:a">
<a>
  <xsl:attribute name="href"><xsl:value-of select="@xlink:href" /></xsl:attribute>
  <xsl:if test="@office:target-frame-name">
   <xsl:attribute name="target"><xsl:value-of select="@office:target-frame-name" /></xsl:attribute>
  </xsl:if>
  <xsl:apply-templates />
</a>
</xsl:template>


<!-- Bookmark -->
<xsl:template match="//text:bookmark">
<a id="{@text:name}" />
</xsl:template>


<!-- Ordered List  -->
<xsl:template match="//text:ordered-list">
<ol>
  <xsl:apply-templates />
</ol>
</xsl:template>


<!-- Unordered List  -->
<xsl:template match="//text:unordered-list">
<ul>
  <xsl:apply-templates />
</ul>
</xsl:template>


<!-- Ordered List  -->
<xsl:template match="//text:list-item">
<li><xsl:apply-templates /></li>
</xsl:template>


<!-- Line break  -->
<xsl:template match="//text:line-break">
<br />
</xsl:template>


<!-- Table Content ... pages 261ff file format documentation-->

<!-- Table  -->
<xsl:template match="//table:table">
<table>
  <xsl:if test="@table:style-name">            <!-- if this attribute is there, it refers to a style definition -->
    <xsl:call-template name="apply-style">     <!-- thus, add CSS styles -->
      <xsl:with-param name="style-name" select="@table:style-name" />
    </xsl:call-template>
  </xsl:if>
  <xsl:apply-templates />
</table>
</xsl:template>


<!-- Table Header Rows -->
<xsl:template match="//table:table-header-rows">
  <xsl:apply-templates mode="header-row" />
</xsl:template>


<!-- Table Row -->
<xsl:template match="//table:table-row">
<tr>
  <xsl:if test="@table:style-name">            <!-- if this attribute is there, it refers to a style definition -->
    <xsl:call-template name="apply-style">     <!-- thus, add CSS styles -->
      <xsl:with-param name="style-name" select="@table:style-name" />
    </xsl:call-template>
  </xsl:if>
  <xsl:apply-templates />
</tr>
</xsl:template>


<!-- Table Cell -->
<xsl:template match="//table:table-cell">
<td>
  <xsl:if test="@table:style-name">            <!-- if this attribute is there, it refers to a style definition -->
    <xsl:call-template name="apply-style">     <!-- thus, add CSS styles -->
      <xsl:with-param name="style-name" select="@table:style-name" />
    </xsl:call-template>
  </xsl:if>
  <xsl:apply-templates />
</td>
</xsl:template>

<xsl:template match="//table:table-cell" mode="header-row">
<th>
  <xsl:if test="@table:style-name">            <!-- if this attribute is there, it refers to a style definition -->
    <xsl:call-template name="apply-style">     <!-- thus, add CSS styles -->
      <xsl:with-param name="style-name" select="@table:style-name" />
    </xsl:call-template>
  </xsl:if>
  <xsl:apply-templates />
</th>
</xsl:template>



<!-- Draw Content ... pages 362ff file format documentation-->

<xsl:template match="//draw:image">
  <img alt="{@draw:name}" src="{@xlink:href}" />
</xsl:template>



<!-- Styles ... used everywhere -->

<xsl:template name="apply-style">
  <!-- This template is called by text:p and text:span templates in order to
       insert a style attribute with CSS styles -->
  <xsl:param name="style-name" select="@text:style-name" />
  <xsl:attribute name="style">
    <xsl:apply-templates select="//style:style[@style:name=$style-name]/style:properties/@*" mode="style" />
  </xsl:attribute>
</xsl:template>


<!-- Format Attributes -->
<xsl:template match="@fo:*|@style:width" mode="style">
  <!-- The following attributes in the XSL format (fo) namespace are used by OpenOffice.org
       but don't seem to be part of CSS. This may lead to not 100% valid CSS.
       * language
       * country
       * text-shadow
       * text-align-last
       * hyphenate
       * hyphenation-keep
       * hyphenation-remain-char-count
       * hyphenation-push-char-count
       * hyphenation-ladder-count
       * break-before
       * break-after
  -->
  <xsl:value-of select="local-name()" /><xsl:text>:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@fo:text-align" mode="style">
  <xsl:if test=".='start'"   >text-align:left; </xsl:if>
  <xsl:if test=".='center'"  >text-align:center; </xsl:if>
  <xsl:if test=".='end'"     >text-align:right; </xsl:if>
  <xsl:if test=".='justify'" >text-align:justify; </xsl:if>
</xsl:template>


<!-- Style Attributes -->
<xsl:template match="@style:font-name" mode="style">
  <xsl:text>font-family:</xsl:text><xsl:value-of select="local-name()" /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@style:text-underline" mode="style">
  <xsl:text>text-decoration:underline; </xsl:text>
</xsl:template>

<xsl:template match="@style:text-crossing-out" mode="style">
  <xsl:text>text-decoration:line-through; </xsl:text>
</xsl:template>

<xsl:template match="@style:text-blinking" mode="style">
  <xsl:text>text-decoration:blink; </xsl:text>
</xsl:template>

<xsl:template match="@style:text-background-color" mode="style">
  <xsl:text>background-color:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@style:border-line-width" mode="style">
  <xsl:text>border-width:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@style:border-line-width-top" mode="style">
  <xsl:text>border-top-width:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@style:border-line-width-bottom" mode="style">
  <xsl:text>border-bottom-width:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@style:border-line-width-left" mode="style">
  <xsl:text>border-left-width:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<xsl:template match="@style:border-line-width-right" mode="style">
  <xsl:text>border-right-width:</xsl:text><xsl:value-of select="." /><xsl:text>; </xsl:text>
</xsl:template>

<!-- we need this, otherwise the processor will just print the attribute
     contents while we want unmatched attributes not to appear -->
<xsl:template match="@*" mode="style" />


</xsl:stylesheet>

