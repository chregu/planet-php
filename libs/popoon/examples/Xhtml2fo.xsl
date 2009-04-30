<?xml version="1.0" encoding="utf-8"?>
<!--Initial version was developed by Hiroshi Obata (Antenna House, Inc.) and distributed in January 2001.
On October 26, 2001, This is slightly changed by T. Kobayashi (Antenna House, Inc) and released as sample XSLT Stylesheet and is distributed from http://www.antennahouse.com.
-->
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:html="http://www.w3.org/1999/xhtml">
     
     <xsl:output method="xml"
version="1.0" indent="yes"
encoding="UTF-8"/>
    <!-- atributes -->
 
    <!-- page size -->
    <xsl:param name="page-width">8.5in</xsl:param>
    <xsl:param name="page-height">11in</xsl:param>
    <xsl:param name="page-margin-top">15mm</xsl:param>
    <xsl:param name="page-margin-bottom">15mm</xsl:param>
    <xsl:param name="page-margin-left">20mm</xsl:param>
    <xsl:param name="page-margin-right">20mm</xsl:param>

    <!-- page header and hooter extent  -->
    <xsl:param name="page-header-extent">13mm</xsl:param>
    <xsl:param name="page-footer-extent">13mm</xsl:param>

    <!-- printing item in header and footer -->
    <xsl:param name="title-print-in-header" select="true()"/>
    <xsl:param name="page-number-print-in-footer" select="true()"/>


   <xsl:template   match="html:html">
      <fo:root>
      <xsl:if test="@lang">
      <xsl:attribute name="xml:lang"><xsl:value-of select="@lang" /></xsl:attribute>
      </xsl:if>
      <xsl:if test="@xml:lang">
      <xsl:attribute name="xml:lang"><xsl:value-of select="@xml:lang" /></xsl:attribute>
      </xsl:if>
      <xsl:call-template  name="layout-master-set"/>
      <xsl:apply-templates select="html:body"/>
      </fo:root>
   </xsl:template>
   
   <!-- Headings -->
   <xsl:attribute-set name="h1">
        <xsl:attribute name="space-before">1em</xsl:attribute>
        <xsl:attribute name="space-after">0.5em</xsl:attribute>
        <xsl:attribute name="font-size">xx-large</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">black</xsl:attribute>
        <xsl:attribute name="keep-with-next">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h2">
        <xsl:attribute name="space-before">1em</xsl:attribute>
        <xsl:attribute name="space-after">0.5em</xsl:attribute>
        <xsl:attribute name="font-size">x-large</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">black</xsl:attribute>
        <xsl:attribute name="keep-with-next">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h3">
        <xsl:attribute name="space-before">1em</xsl:attribute>
        <xsl:attribute name="space-after">0.5em</xsl:attribute>
        <xsl:attribute name="font-size">large</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">black</xsl:attribute>
        <xsl:attribute name="keep-with-next">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h4">
        <xsl:attribute name="space-before">1em</xsl:attribute>
        <xsl:attribute name="space-after">0.5em</xsl:attribute>
        <xsl:attribute name="font-size">medium</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">black</xsl:attribute>
        <xsl:attribute name="keep-with-next">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h5">
        <xsl:attribute name="space-before">1em</xsl:attribute>
        <xsl:attribute name="space-after">0.5em</xsl:attribute>
        <xsl:attribute name="font-size">small</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">black</xsl:attribute>
        <xsl:attribute name="keep-with-next">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h6">
        <xsl:attribute name="space-before">1em</xsl:attribute>
        <xsl:attribute name="space-after">0.5em</xsl:attribute>
        <xsl:attribute name="font-size">x-small</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">black</xsl:attribute>
        <xsl:attribute name="keep-with-next">always</xsl:attribute>
    </xsl:attribute-set>
    
    <!-- Block level Attribute -->
    <!-- p-->
    <xsl:attribute-set name="p">
    	<xsl:attribute name="text-align">justify</xsl:attribute>
        <xsl:attribute name="text-indent">1em</xsl:attribute>
        <xsl:attribute name="space-before">0.6em</xsl:attribute>
        <xsl:attribute name="space-after">0.6em</xsl:attribute>
    </xsl:attribute-set>
    
    <!-- pre -->
    <xsl:attribute-set name="pre">
        <xsl:attribute name="font-family">monospace</xsl:attribute>
        <xsl:attribute name="white-space">pre</xsl:attribute>
        <xsl:attribute name="wrap-option">wrap</xsl:attribute>
        <xsl:attribute name="text-indent">1em</xsl:attribute>
        <xsl:attribute name="space-before">0.6em</xsl:attribute>
        <xsl:attribute name="space-after">0.6em</xsl:attribute>
    </xsl:attribute-set>
    <!-- blockquote -->
    <xsl:attribute-set name="blockquote">
        <xsl:attribute name="start-indent">inherit + 4em </xsl:attribute>
        <xsl:attribute name="end-indent">inherit + 4em</xsl:attribute>
        <xsl:attribute name="text-indent">1em</xsl:attribute>
        <xsl:attribute name="space-before">0.6em</xsl:attribute>
        <xsl:attribute name="space-after">0.6em</xsl:attribute>
        <xsl:attribute name="margin-top">1em</xsl:attribute>
        <xsl:attribute name="margin-bottom">1em</xsl:attribute>
    </xsl:attribute-set>
    <!-- address -->
    <xsl:attribute-set name="address">
        <xsl:attribute name="font-style">italic</xsl:attribute>
    </xsl:attribute-set>
    
    <!-- ul -->
    <!-- for list-block -->
    <xsl:attribute-set name="ul">
        <xsl:attribute name="provisional-distance-between-starts">1em</xsl:attribute>
        <xsl:attribute name="provisional-label-separation">1em</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="ul-li">
    </xsl:attribute-set>
    
    <!-- ol -->
    <!-- for list-block -->
    <xsl:attribute-set name="ol">
        <xsl:attribute name="provisional-distance-between-starts">1em</xsl:attribute>
        <xsl:attribute name="provisional-label-separation">1em</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="ol-li">
    </xsl:attribute-set>
    <!-- dl dt dd -->
    <xsl:attribute-set name="dl">
    </xsl:attribute-set>
    <xsl:attribute-set name="dt">
    </xsl:attribute-set>
    <xsl:attribute-set name="dd">
        <xsl:attribute name="start-indent">inherit +1em</xsl:attribute>
        <xsl:attribute name="space-before">0.6em</xsl:attribute>
        <xsl:attribute name="space-after">0.6em</xsl:attribute>
 
    </xsl:attribute-set>

    <!-- Text -->
    <xsl:attribute-set name="em">
        <xsl:attribute name="font-style">italic</xsl:attribute>
        </xsl:attribute-set>
    <xsl:attribute-set name="strong">
        <xsl:attribute name="font-weight">bolder</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="cite">
        <xsl:attribute name="font-style">italic</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="dfn">
        <xsl:attribute name="font-style">italic</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="code">
        <xsl:attribute name="font-family">monospace</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="samp">
        <xsl:attribute name="font-family">monospace</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="kbd">
        <xsl:attribute name="font-family">monospace</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="var">
        <xsl:attribute name="font-style">italic</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="abbr">
    </xsl:attribute-set>

    <xsl:attribute-set name="acronym">
    </xsl:attribute-set>


    <xsl:attribute-set name="sup">
        <xsl:attribute name="baseline-shift">super</xsl:attribute>
        <xsl:attribute name="font-size">.83em</xsl:attribute>
    </xsl:attribute-set>


    <xsl:attribute-set name="sub">
        <xsl:attribute name="baseline-shift">sub</xsl:attribute>
        <xsl:attribute name="font-size">.83em</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="a">
        <xsl:attribute name="text-decoration">underline</xsl:attribute>
        <xsl:attribute name="color">blue</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="div">
        <xsl:attribute name="start-indent">5mm</xsl:attribute>
        <xsl:attribute name="end-indent">5mm</xsl:attribute>
    </xsl:attribute-set>

    <!-- Table -->
   
    <xsl:attribute-set name="table">
    </xsl:attribute-set>
    
    <xsl:attribute-set name="table-caption">
        <xsl:attribute name="text-align">center</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="tr">
    </xsl:attribute-set>
    
    <xsl:attribute-set name="th">
  <xsl:attribute name="background-color">#DDDDDD</xsl:attribute>
  <xsl:attribute name="border-style">solid</xsl:attribute>
  <xsl:attribute name="border-width">1pt</xsl:attribute>
  <xsl:attribute name="padding-start">0.3em</xsl:attribute>
  <xsl:attribute name="padding-end">0.2em</xsl:attribute>
  <xsl:attribute name="padding-before">2pt</xsl:attribute>
  <xsl:attribute name="padding-end">2pt</xsl:attribute>
    </xsl:attribute-set>
     
    <xsl:attribute-set name="td">
    <xsl:attribute name="border-style">solid</xsl:attribute>
  <xsl:attribute name="border-width">1pt</xsl:attribute>
  <xsl:attribute name="padding-start">0.3em</xsl:attribute>
  <xsl:attribute name="padding-end">0.2em</xsl:attribute>
  <xsl:attribute name="padding-before">2pt</xsl:attribute>
  <xsl:attribute name="padding-end">2pt</xsl:attribute></xsl:attribute-set>



   <!-- Head -->
   <xsl:template   match="html:head">
   </xsl:template>

  
   <xsl:template   match="html:title">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:base">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:meta">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:link">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:style">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:script">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:noscript">
      <xsl:apply-templates />
   </xsl:template>
   
   <xsl:template name="layout-master-set">
   <fo:layout-master-set>
       <fo:simple-page-master
                         page-width="{$page-width}"
 page-height="{$page-height}"
                         master-name="BodyPage">
       <fo:region-body   
                         margin-top="{$page-margin-top}"
                         margin-right="{$page-margin-right}"
                         margin-bottom="{$page-margin-bottom}"
                         margin-left="{$page-margin-left}" />
       <fo:region-before extent="{$page-header-extent}"
                         display-align="after"/>
       <fo:region-after  extent="{$page-footer-extent}"
                         display-align="before"/>
       </fo:simple-page-master>
       <fo:simple-page-master
                         page-width="{$page-width}"
                         page-height="{$page-height}"
                         master-name="CoverPage">
       <fo:region-body   margin-top="{$page-margin-top}"
                         margin-right="{$page-margin-right}"
                         margin-bottom="{$page-margin-bottom}"
                         margin-left="{$page-margin-left}" />
       </fo:simple-page-master>
   </fo:layout-master-set>
   </xsl:template>
   
   <!-- Body -->
   <xsl:template   match="html:body">
   <fo:page-sequence master-reference="BodyPage">
   <fo:title><xsl:value-of select="/html:html/html:head/html:title" /></fo:title>
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
   <xsl:apply-templates />
    </fo:flow>
</fo:page-sequence>

   </xsl:template>
 
  
   <xsl:template   match="html:div">
      <fo:block xsl:use-attribute-sets="div"><xsl:apply-templates /></fo:block>
   </xsl:template>

  
  <xsl:template match="html:p">
      <fo:block xsl:use-attribute-sets="p"><xsl:apply-templates /></fo:block>
  </xsl:template>

  
   <xsl:template match="html:h1" >
   <fo:block  xsl:use-attribute-sets="h1">
      <xsl:apply-templates />
   </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:h2" >
   <fo:block xsl:use-attribute-sets="h2">
      <xsl:apply-templates />
   </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:h3"  >
   <fo:block xsl:use-attribute-sets="h3">
      <xsl:apply-templates />
   </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:h4" >
   <fo:block xsl:use-attribute-sets="h4">
      <xsl:apply-templates />
   </fo:block>
   </xsl:template>

   <xsl:template   match="html:h5"  >
   <fo:block xsl:use-attribute-sets="h5">
      <xsl:apply-templates />
   </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:h6" >
   <fo:block xsl:use-attribute-sets="h6">
      <xsl:apply-templates />
   </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:ul">
   <fo:list-block  xsl:use-attribute-sets="ul">
       <xsl:apply-templates/>
   </fo:list-block>
   </xsl:template>

   <xsl:template   match="html:ul/html:li">
   <fo:list-item>
    <fo:list-item-label xsl:use-attribute-sets="ul-li" end-indent="label-end()">
      <fo:block><fo:character character="&#x2022;" />
      </fo:block>
    </fo:list-item-label>
    <fo:list-item-body start-indent="body-start()">
      <fo:block>
        <xsl:apply-templates/>
      </fo:block>
    </fo:list-item-body>
   </fo:list-item>
   </xsl:template>
  
   <xsl:template   match="html:ol">
   <fo:list-block  xsl:use-attribute-sets="ol">
       <xsl:apply-templates />
   </fo:list-block>
   </xsl:template>

   <xsl:template   match="html:ol/html:li">
   <fo:list-item>
    <fo:list-item-label xsl:use-attribute-sets="ol-li" end-indent="label-end()">
      <fo:block>
      <xsl:number format="1." />
      </fo:block>
    </fo:list-item-label>
    <fo:list-item-body start-indent="body-start()">
      <fo:block>
        <xsl:apply-templates/>
      </fo:block>
    </fo:list-item-body>
   </fo:list-item>
   </xsl:template>

  
   <xsl:template   match="html:dl">
      <fo:block xsl:use-attribute-sets="dl">
          <xsl:apply-templates />
      </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:dt">
      <fo:block xsl:use-attribute-sets="dt">
          <xsl:apply-templates />
      </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:dd">
      <fo:block xsl:use-attribute-sets="dd">
          <xsl:apply-templates />
      </fo:block>
   </xsl:template>

  
    <xsl:template   match="html:address">
        <fo:block xsl:use-attribute-sets="address">
            <xsl:apply-templates />
        </fo:block>
    </xsl:template>

  
   <xsl:template   match="html:hr">
      <fo:block space-before="1em" space-after="1em">
          <fo:leader leader-pattern="rule" rule-style="groove" leader-length="100%"/>
      </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:pre">
      <fo:block xsl:use-attribute-sets="pre">
          <xsl:apply-templates />
      </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:blockquote">
      <fo:block xsl:use-attribute-sets="blockquote">
          <xsl:apply-templates />
      </fo:block>
   </xsl:template>

  
   <xsl:template   match="html:ins">
        <xsl:comment>Inserted By ins tag </xsl:comment>
        <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:del">
   <xsl:comment> Deleted By del tag </xsl:comment>
   </xsl:template>

  
   <xsl:template   match="html:a[@href]">
      <fo:inline xsl:use-attribute-sets="a" ><xsl:apply-templates /></fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:span">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:bdo">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:br">
   <fo:block />
   </xsl:template>

  
   <xsl:template   match="html:em">
      <fo:inline xsl:use-attribute-sets="em">
      <xsl:apply-templates />
      </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:strong">
      <fo:inline xsl:use-attribute-sets="strong">
      <xsl:apply-templates />
      </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:dfn">
     <fo:inline xsl:use-attribute-sets="dfn"><xsl:apply-templates /></fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:code">
     <fo:inline xsl:use-attribute-sets="code">
     <xsl:apply-templates />
     </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:samp">
       <fo:inline xsl:use-attribute-sets="samp">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:kbd">
       <fo:inline xsl:use-attribute-sets="kbd">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:var">
       <fo:inline xsl:use-attribute-sets="var">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:cite">
      <fo:inline xsl:use-attribute-sets="cite">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:abbr">
       <fo:inline xsl:use-attribute-sets="abbr">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:acronym">
       <fo:inline xsl:use-attribute-sets="acronym">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:q">
       <xsl:comment>q -- Not Support</xsl:comment>
       <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:sub">
       <fo:inline xsl:use-attribute-sets="sub">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:sup">
       <fo:inline xsl:use-attribute-sets="sup">
       <xsl:apply-templates />
       </fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:tt">
      <fo:inline font-family="monospace"><xsl:apply-templates /></fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:i">
      <fo:inline font-style="italic"><xsl:apply-templates /></fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:b">
         <fo:inline font-weight="bolder"><xsl:apply-templates /></fo:inline>
   </xsl:template>
   
   <xsl:template   match="html:big">
         <fo:inline font-size="larger"><xsl:apply-templates /></fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:small">
        <fo:inline font-size="smaller"><xsl:apply-templates /></fo:inline>
   </xsl:template>

  
   <xsl:template   match="html:object">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template match="html:param">
      <xsl:apply-templates />
   </xsl:template>

  
   <xsl:template   match="html:html/html:body/html:img">
   <fo:block>
   <fo:external-graphic src="{@src}">
   <xsl:if test="@height">
   <xsl:attribute name="content-height">
     <xsl:value-of select="@height" />px
   </xsl:attribute>
   </xsl:if>
   <xsl:if test="@width">
     <xsl:attribute name="content-width">
       <xsl:value-of select="@width" />px
     </xsl:attribute>
   </xsl:if>
   </fo:external-graphic>
   </fo:block>
   </xsl:template>
   
   <xsl:template   match="html:img">
   <fo:external-graphic src="{@src}">
   <xsl:if test="@height">
   <xsl:attribute name="content-height">
     <xsl:value-of select="@height" />px
   </xsl:attribute>
   </xsl:if>
   <xsl:if test="@width">
     <xsl:attribute name="content-width">
       <xsl:value-of select="@width" />px
     </xsl:attribute>
   </xsl:if>
   </fo:external-graphic>
   </xsl:template>

  
   <xsl:template   match="html:map">
   </xsl:template>

  
   <xsl:template   match="html:area">
   </xsl:template>

  
   <xsl:template   match="html:form">
   </xsl:template>

  
   <xsl:template   match="html:label">
   </xsl:template>

  
   <xsl:template   match="html:input">
   </xsl:template>

  
   <xsl:template   match="html:select">
   </xsl:template>

  
   <xsl:template   match="html:optgroup">
   </xsl:template>

  
   <xsl:template   match="html:option">
   </xsl:template>

  
   <xsl:template   match="html:textarea">
   </xsl:template>

  
   <xsl:template   match="html:fieldset">
    </xsl:template>

  
   <xsl:template   match="html:legend">
   </xsl:template>

  
   <xsl:template   match="html:button">
   </xsl:template>

  
  <xsl:template match="html:table">
    <fo:table-and-caption>
    <xsl:if test="html:caption">
        <fo:table-caption xsl:use-attribute-sets="table-caption">
            <xsl:apply-templates select="html:caption"/>
        </fo:table-caption>
   </xsl:if>
   <fo:table xsl:use-attribute-sets="table">
       <xsl:if test="@width">
       <xsl:attribute name="width"><xsl:value-of select="@width" /></xsl:attribute>
       </xsl:if><xsl:if test="@border">
       <xsl:attribute name="border-style"><xsl:value-of select="@border" /></xsl:attribute>
       </xsl:if>

       <xsl:apply-templates select="html:col | html:colgroup"/>
       <xsl:apply-templates select="html:thead"/>
       <xsl:apply-templates select="html:tfoot"/>
       <xsl:choose>
       <xsl:when test="html:tbody">
           <xsl:apply-templates select="html:tbody"/>
       </xsl:when>
       <xsl:otherwise>
           <fo:table-body>
                <xsl:apply-templates select="html:tr"/>
           </fo:table-body>
       </xsl:otherwise>
       </xsl:choose>
   </fo:table>
   </fo:table-and-caption>
   </xsl:template>

  
   <xsl:template   match="html:caption">
      <fo:block><xsl:apply-templates /></fo:block>
   </xsl:template>

  
   <xsl:template   match="html:thead">
   <fo:table-header>
      <xsl:apply-templates select="html:tr"/>
   </fo:table-header>
   </xsl:template>

  
   <xsl:template   match="html:tfoot">
   <fo:table-footer>
      <xsl:apply-templates select="html:tr"/>
   </fo:table-footer>
   </xsl:template>

  
   <xsl:template   match="html:tbody">
   <fo:table-body>
      <xsl:apply-templates select="html:tr"/>
   </fo:table-body>
   </xsl:template>

   <xsl:template   match="html:colgroup">
     <xsl:choose>
      <xsl:when test="html:col">
        <xsl:apply-templates select="html:col"/>
      </xsl:when>
      <xsl:otherwise>    <xsl:variable name="width">
    <xsl:choose>
      <xsl:when test="@width"><xsl:value-of select="@width" /></xsl:when>
      <xsl:otherwise></xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:variable name="align">
    <xsl:choose>
      <xsl:when test="@align"><xsl:value-of select="@align" /></xsl:when>
      <xsl:otherwise>inherit</xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:variable name="span">
    <xsl:choose>
      <xsl:when test="@span"><xsl:value-of select="@span" /></xsl:when>
      <xsl:otherwise>1</xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:call-template name="make-column">
    <xsl:with-param name="width" select="$width" />
    <xsl:with-param name="align" select="$align" />
    <xsl:with-param name="span" select="$span" />
    </xsl:call-template>

      </xsl:otherwise>
     </xsl:choose>
   </xsl:template>
   
   
   <xsl:template   match="html:colgroup/html:col">
    <xsl:variable name="width">
    <xsl:choose>
      <xsl:when test="@width"><xsl:value-of select="@width" /></xsl:when>
      <xsl:otherwise>
        <xsl:if test="../@width" >
        <xsl:value-of select="../@width" />
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:variable name="align">
    <xsl:choose>
      <xsl:when test="@align"><xsl:value-of select="@align" /></xsl:when>
        <xsl:otherwise>
          <xsl:choose>
            <xsl:when test="../@align" >
              <xsl:value-of select="../@align" />
            </xsl:when>
            <xsl:otherwise>1</xsl:otherwise>
          </xsl:choose>
       </xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:variable name="span">
    <xsl:choose>
      <xsl:when test="@span"><xsl:value-of select="@span" /></xsl:when>
      <xsl:otherwise>1</xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:call-template name="make-column">
    <xsl:with-param name="width" select="$width" />
    <xsl:with-param name="align" select="$align" />
    <xsl:with-param name="span" select="$span" />
    </xsl:call-template>

   </xsl:template>
   
  <xsl:template match="html:table/html:col">
    <xsl:variable name="width">
    <xsl:choose>
      <xsl:when test="@width"><xsl:value-of select="@width" /></xsl:when>
      <xsl:otherwise></xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:variable name="align">
    <xsl:choose>
      <xsl:when test="@align"><xsl:value-of select="@align" /></xsl:when>
      <xsl:otherwise>inherit</xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:variable name="span">
    <xsl:choose>
      <xsl:when test="@span"><xsl:value-of select="@span" /></xsl:when>
      <xsl:otherwise>1</xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:call-template name="make-column">
    <xsl:with-param name="width" select="$width" />
    <xsl:with-param name="align" select="$align" />
    <xsl:with-param name="span" select="$span" />
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template name="make-column">
  <xsl:param name="width" />
  <xsl:param name="align" />
  <xsl:param name="span" />
  <fo:table-column>
  <xsl:attribute name = "column-width">
     <xsl:value-of select="$width" />
  </xsl:attribute>
   <xsl:attribute name = "number-columns-repeated">
  <xsl:value-of select="$span" />
  </xsl:attribute>
  <xsl:attribute name="text-align">
      <xsl:choose>
      <xsl:when test="$align = 'left'">left</xsl:when>
      <xsl:when test="$align = 'center'">center</xsl:when>
      <xsl:when test="$align = 'right'">right</xsl:when>
      <xsl:otherwise>start</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
  </fo:table-column>
  </xsl:template>

  <xsl:template match="html:tr">
    <fo:table-row xsl:use-attribute-sets="tr">
        <xsl:apply-templates select="html:th | html:td"/>
    </fo:table-row>
  </xsl:template>

  <xsl:template match="html:th">
     <fo:table-cell xsl:use-attribute-sets="th">
        <xsl:call-template name="make-cell" />
     </fo:table-cell>
  </xsl:template>

  <xsl:template match="html:td">
    <fo:table-cell xsl:use-attribute-sets="td">
        <xsl:call-template name="make-cell" />
    </fo:table-cell>
  </xsl:template>
 
 
 <xsl:template name="make-cell">

    <!--<xsl:attribute name="text-align">from-table-column(text-align)</xsl:attribute>-->

   <xsl:if test="@colspan">
      <xsl:attribute name="number-columns-spanned">
        <xsl:value-of select="@colspan"/>
      </xsl:attribute>
   </xsl:if>
   <xsl:if test="@rowspan">
      <xsl:attribute name="number-rows-spanned">
        <xsl:value-of select="@rowspan"/>
      </xsl:attribute>
   </xsl:if>
   <xsl:if test="@align">
      <xsl:attribute name="text-align">
        <xsl:choose>
        <xsl:when test="@align = 'left'">left</xsl:when>
        <xsl:when test="@align = 'center'">center</xsl:when>
        <xsl:when test="@align = 'right'">right</xsl:when>
        <xsl:otherwise>inherit</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
   </xsl:if>
   <fo:block>
   <xsl:apply-templates />
   </fo:block>
  </xsl:template>

 

</xsl:stylesheet>