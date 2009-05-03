<?xml version="1.0"?>

<!-- CVS $Id: error2html.xslt,v 1.14 2004/02/02 16:10:41 stevenn Exp $ -->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:error="http://apache.org/cocoon/error/2.0">

  <xsl:param name="contextPath"/>

  <!-- let sitemap override default page title -->
  <xsl:param name="pageTitle" select="//error:notify/error:title"/>

  <xsl:template match="error:notify">
 
    <html>
      <head>
        <title>
          <xsl:value-of select="$pageTitle"/>
        </title>
        <style>
          h1 { color: #336699; text-align: left; margin: 0px 0px 30px 0px; padding: 0px; border-width: 0px 0px 1px 0px; border-style: solid; border-color: #336699;}
          .userInfo , p.message { padding: 10px 30px 10px 30px; font-weight: bold; font-size: 130%; border-width: 1px; border-style: dashed; border-color: #336699; }
          p.code { padding: 10px 30px 10px 30px; font-weight: bold; font-size: 130%; border-width: 1px; border-style: dashed; border-color: #336699; }
          
          p.location { padding: 10px 30px 20px 30px; border-width: 0px 0px 1px 0px; border-style: solid; border-color: #336699;}
          p.topped { padding-top: 10px; border-width: 1px 0px 0px 0px; border-style: solid; border-color: #336699; }
          pre { font-size: 100%; }
          .userInfo {font-weight: normal; font-size: 100%;}
          .description {  font-weight: bold; font-size: 130%;}
        </style>
      </head>
      <body>
        <h1><xsl:value-of select="$pageTitle"/></h1>

        <p class="message">
          Message: <xsl:value-of select="error:message"/>
        </p>

         <p class="code">
          Code: <xsl:value-of select="error:code"/>
        </p>
        
        <xsl:if test="error:extra[@description = 'userInfo']">
        <p class="userInfo ">
       <span class="description">userInfo: </span><br/>
       <xsl:copy-of select="error:extra[@description = 'userInfo']"/>
      </p>
      </xsl:if>
        
        <p class="location">
          In File <xsl:value-of select="error:file"/>
          Line    <xsl:value-of select="error:line"/>
        </p>
         
        

        <xsl:apply-templates select="error:extra"/>

        <p class="topped">
        Error... more text here
        </p>

      </body>
    </html>
  </xsl:template>

  <xsl:template match="error:extra">
    <xsl:choose>
    <xsl:when test="@description = 'userInfo'">
    
    </xsl:when>
     <xsl:when test="contains(@description,'stacktrace')">
      <p class="stacktrace">
       <span class="description"><xsl:value-of select="@description"/></span>
       <pre id="{@description}" style="display: block">
         <xsl:value-of select="translate(.,'&#13;',' ')"/>
       </pre>
      </p>
     </xsl:when>
     <xsl:otherwise>
      <p class="message">
       <span class="description"><xsl:value-of select="@description"/>: </span>
       <xsl:value-of select="."/>
      </p>
     </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
