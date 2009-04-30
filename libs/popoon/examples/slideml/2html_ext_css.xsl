<?xml version="1.0" encoding="iso-8859-1"?>
   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:s="http://www.oscom.org/2003/SlideML/1.0/"
        xmlns="http://www.w3.org/1999/xhtml"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:dcterms="http://purl.org/dc/terms/"
        xmlns:xi="http://www.w3.org/2001/XInclude"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:exsl="http://exslt.org/common"
        extension-element-prefixes="exsl"
        xmlns:regexp="http://exslt.org/regular-expressions"
        exclude-result-prefixes="s dc dcterms xi xsi exsl regexp">

    <xsl:param name="page"/>

    <xsl:param name="position"/>

    <xsl:variable name="br">
        <xsl:text>
		</xsl:text>
    </xsl:variable>
                

    <xsl:template match="/s:slideset/s:slide">
    
        <xsl:call-template name="slide">
            <xsl:with-param name="position">
                <xsl:value-of select="$position"/>
            </xsl:with-param>
        </xsl:call-template>
        
    </xsl:template>

    <xsl:template match="/">            
        <xsl:choose>       
            
            <xsl:when test="$page='title'">
                <xsl:apply-templates select="/s:slideset/s:metadata" />
            </xsl:when>
            
           <xsl:when test="$page='toc'">   
            <xsl:call-template name="toc" />
           </xsl:when>
            
           
           <xsl:otherwise>
                <xsl:apply-templates select="/s:slideset/s:slide[position()=$position]"/>   
           </xsl:otherwise>
           
        </xsl:choose>         
                            
    </xsl:template>
     
      

<!--     here we make the title slide     -->

    <xsl:template name="title" match="/s:slideset/s:metadata">

        <xsl:variable name="nextSlide">
            <xsl:text>slide_1.html</xsl:text>
        </xsl:variable>

        <xsl:variable name="prevSlide">
           toc.html
        </xsl:variable>

        <html>
            <xsl:call-template name="head">
                <xsl:with-param name="page">title</xsl:with-param>
            </xsl:call-template>

            <body onload="loaded()">
                <xsl:value-of select="$br"/>
                <div id="all">
                    <div class="content">
                        <div class="slidecap">
                            <div class="slidetitle">
                                <table border="0" width="100%">
                                    <tr>
                                        <td valign="bottom">
                                            <span class="lg"></span>
                                        </td>

                                        <td valign="bottom">
                                            <a href="http://www.phpug.ch">
						<img valign="top" src="img/phpug_logo.gif" align="right" alt="php user group schweiz" border="0"/>
					    </a>
                                        </td>


                                    </tr>
                                </table>
			

                            </div>
                            <p class="none"></p>

                            <div class="nav2">
                                <xsl:call-template name="navigation">
                                    <xsl:with-param name="page">title</xsl:with-param>
                                    <xsl:with-param name="nextSlide" select="$nextSlide"/>
                                    <xsl:with-param name="prevSlide" select="$prevSlide"/>
                                </xsl:call-template>
                            </div>
                        </div>

                        <div class="slidecontent" id="slidecontent">

                            <h1 class="titel">
                                <xsl:value-of select="s:title"/>
                            </h1>
                            <div class="date">Date: <xsl:value-of select="dc:date"/>
                            </div>


<!-- more than one author -->
                            <xsl:choose>
                                <xsl:when test="s:authorgroup">

                                    <div class="author">
                                        <xsl:for-each select="s:authorgroup/s:author">
                                            <xsl:choose>
                                                <xsl:when test="string-length(normalize-space(s:email)) &gt; 0">
                                                    <a href="mailto:{normalize-space(s:email)}" title="send mail to: {normalize-space(s:email/text())}">
                                                        <xsl:value-of select="normalize-space(s:givenname/text())"/>
                                                        <xsl:text> </xsl:text>
                                                        <xsl:value-of select="normalize-space(s:familyname/text())"/>
                                                    </a>
                                                </xsl:when>

                                                <xsl:otherwise>
                                                    <xsl:value-of select="normalize-space(s:givenname/text())"/>
                                                    <xsl:text> </xsl:text>
                                                    <xsl:value-of select="normalize-space(s:familyname/text())"/>
                                                </xsl:otherwise>
                                            </xsl:choose>

                                            <xsl:text> </xsl:text>

                                            <xsl:if test="s:authorblurb">
                                                <a href="#" onClick="MM_openBrWindow('bio_{translate(s:givenname/text(),'ÄÖÜäöüàéè','AOUaouaee')}_{translate(s:familyname/text(),'ÄÖÜäöüàéè','AOUaouaee')}.html','bio_{translate(s:givenname/text(),'ÄÖÜäöüàéè','AOUaouaee')}_{translate(s:familyname/text(),'ÄÖÜäöüàéè','AOUaouaee')}','scrollbars=yes,width=700,height=500')">
                                                    <img src="bio.gif" title="show bio from: {s:givenname/text()} {s:familyname/text()}" border="0"/>
                                                </a>
                                            </xsl:if>

                                            <xsl:text>, </xsl:text>

                                            <xsl:choose>
                                                <xsl:when test="s:orgname/@s:href">
                                                    <a href="{normalize-space(s:orgname/@s:href)}" target="_blank" title="visit {normalize-space(s:orgname/text())} website">
                                                        <xsl:value-of select="normalize-space(s:orgname/text())"/>
                                                    </a>
                                                </xsl:when>

                                                <xsl:otherwise>
                                                    <xsl:value-of select="s:orgname"/>
                                                </xsl:otherwise>
                                            </xsl:choose>
                                            <br/>
                                        </xsl:for-each>
                                    </div>
                                </xsl:when>

                                <xsl:otherwise>

		   <!-- only one author -->

                                    <div class="author">
                                        <xsl:choose>
                                            <xsl:when test="s:author/s:email">
                                                <a href="mailto:{normalize-space(s:author/s:email)}" title="send mail to: {normalize-space(s:author/s:email/text())}">
                                                    <xsl:value-of select="normalize-space(s:author/s:givenname/text())"/>
                                                    <xsl:text> </xsl:text>
                                                    <xsl:value-of select="normalize-space(s:author/s:familyname/text())"/>
                                                </a>
                                            </xsl:when>

                                            <xsl:otherwise>
                                                <xsl:value-of select="normalize-space(s:author/s:givenname/text())"/>
                                                <xsl:text> </xsl:text>
                                                <xsl:value-of select="normalize-space(s:author/s:familyname/text())"/>
                                            </xsl:otherwise>
                                        </xsl:choose>

                                        <xsl:text> </xsl:text>

                                        <xsl:if test="s:author/s:authorblurb">
                                            <a href="#" onClick="MM_openBrWindow('bio_{translate(s:author/s:givenname/text(),'ÄÖÜäöüàéè','AOUaouaee')}_{translate(s:author/s:familyname/text(),'ÄÖÜäöüàéè','AOUaouaee')}.html','bio_{translate(s:author/s:givenname/text(),'ÄÖÜäöüàéè','AOUaouaee')}_{translate(s:author/s:familyname/text(),'ÄÖÜäöüàéè','AOUaouaee')}','scrollbars=yes,width=700,height=500')">
                                                <img src="bio.gif" title="show bio from: {s:author/s:givenname/text()} {s:author/s:familyname/text()}" border="0"/>
                                            </a>
                                        </xsl:if>

                                        <xsl:text>, </xsl:text>

                                        <xsl:choose>
                                            <xsl:when test="s:author/s:orgname/@s:href">
                                                <a href="{normalize-space(s:author/s:orgname/@s:href)}" target="_blank" title="visit {normalize-space(s:author/s:orgname/text())} website">
                                                    <xsl:value-of select="normalize-space(s:author/s:orgname/text())"/>
                                                </a>
                                            </xsl:when>

                                            <xsl:otherwise>
                                                <xsl:value-of select="s:author/s:orgname"/>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </div>
                                </xsl:otherwise>
                            </xsl:choose>
                            <p/>
                            <div class="author">
                                <xsl:value-of select="s:confgroup/s:conftitle"/>, 
<xsl:value-of select="s:confgroup/s:address"/>
                            </div>

                            <p/>
                            <div class="author">
                                <xsl:value-of select="s:abstract"/>

                            </div>
                        </div>
                        <div class="clear">&#xA0;</div>

                        <div class="nav">
                            <xsl:call-template name="navigation">
                                <xsl:with-param name="page">title</xsl:with-param>
                                <xsl:with-param name="nextSlide" select="$nextSlide"/>
                                <xsl:with-param name="prevSlide" select="$prevSlide"/>
                            </xsl:call-template>

                        </div>

                        <xsl:call-template name="footer">
                            <xsl:with-param name="page">title</xsl:with-param>
                            <xsl:with-param name="nextSlide" select="$nextSlide"/>
                        </xsl:call-template>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>



<!--     here we make the TOC slide     -->

    <xsl:template name="toc">
        <xsl:variable name="nextSlide">
            slide_1.html
        </xsl:variable>

        <xsl:variable name="prevSlide">
          title.html
        </xsl:variable>

        <html>
            <xsl:call-template name="head">
                <xsl:with-param name="page">toc</xsl:with-param>
            </xsl:call-template>

            <body onload="loaded()">
                <xsl:value-of select="$br"/>
                <div id="all">
                    <div class="content">
                        <div class="slidecap">
                            <div class="slidetitle">
                                <table border="0" width="100%">
                                    <tr>
                                        <td valign="bottom">
                                            <span class="lg">
                                                <a href="title.html">
                                                    <xsl:value-of select="/s:slideset/s:metadata/s:title"/>
                                                </a>
                                            </span>
                                        </td>

                                        <td valign="bottom">
                                            <a href="http://www.phpug.ch/">
						<img valign="top" src="img/phpug_logo.gif" align="right" alt="php user group schweiz" border="0"/>
					    </a>
                                        </td>

                                    </tr>
                                </table>
                            </div>
                            <p class="none"></p>

                            <div class="nav2">
                                <xsl:call-template name="navigation">
                                    <xsl:with-param name="page">toc</xsl:with-param>
                                    <xsl:with-param name="nextSlide" select="$nextSlide"/>
                                    <xsl:with-param name="prevSlide" select="$prevSlide"/>
                                </xsl:call-template>
                            </div>
                        </div>

                        <div class="slidecontent" id="slidecontent">

                            <h1 class="titel">Table Of Contents</h1>

                            <xsl:for-each select="s:slideset/s:slide">
                                <div>
                                    <span class="toc">
                                        <a href="slide_{position()}.html">
                                            <xsl:text>- </xsl:text>
                                            <xsl:value-of select="s:title"/>
                                        </a>
                                    </span>
                                </div>
                            </xsl:for-each>

                        </div>
                        <div class="clear">&#xA0;</div>

                        <div class="nav">
                            <xsl:call-template name="navigation">
                                <xsl:with-param name="page">toc</xsl:with-param>
                                <xsl:with-param name="nextSlide" select="$nextSlide"/>
                                <xsl:with-param name="prevSlide" select="$prevSlide"/>
                            </xsl:call-template>
                        </div>

                        <xsl:call-template name="footer">
                            <xsl:with-param name="page">toc</xsl:with-param>
                        </xsl:call-template>

                    </div>
                </div>
            </body>
        </html>
    </xsl:template>


<!-- here we make the xhtml code for the bio -->

    <xsl:template name="bio">
        <xsl:param name="author"/>
        <html>
            <xsl:call-template name="head"/>

            <body>
                <xsl:value-of select="$br"/>
                <div id="all">
                    <div class="content">
                        <div class="slidecap">
                            <div class="slidetitle">
                                <span class="lg">

                                    <xsl:choose>
                                        <xsl:when test="$author/s:email">
                                            <a class="next" href="mailto:{normalize-space($author/s:email)}" title="send mail to: {normalize-space($author/s:email)}">
                                                <xsl:value-of select="normalize-space($author/s:givenname/text())"/>
                                                <xsl:text> </xsl:text>
                                                <xsl:value-of select="normalize-space($author/s:familyname/text())"/>
                                                <xsl:text>, </xsl:text>
                                                <xsl:value-of select="$author/s:jobtitle/text()"/>
                                            </a>
                                        </xsl:when>

                                        <xsl:otherwise>
                                            <a class="next" href="#">
                                                <xsl:value-of select="$author/s:givenname/text()"/>
                                                <xsl:text> </xsl:text>
                                                <xsl:value-of select="$author/s:familyname/text()"/>
                                                <xsl:text>, </xsl:text>
                                                <xsl:value-of select="$author/s:jobtitle/text()"/>
                                            </a>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                </span>
                            </div>
                            <p class="none"></p>

                            <div class="nav2">
                                <xsl:call-template name="notesnavigation"/>
                            </div>
                        </div>

                        <div class="slidenotecontent">

                            <xsl:if test="starts-with($author/s:orglogo/@s:href, 'http')">

                                <xsl:choose>
                                    <xsl:when test="starts-with($author/s:orgname/@s:href, 'http')">
                                        <a href="{normalize-space($author/s:orgname/@s:href)}" target="_blank" title="visit {normalize-space($author/s:orgname/text())} website">
                                            <img src="{$author/s:orglogo/@s:href}" border="0"/>
                                        </a>
                                    </xsl:when>

                                    <xsl:otherwise>
                                        <img src="{$author/s:orglogo/@s:href}" alt="company logo" border="0"/>
                                    </xsl:otherwise>

                                </xsl:choose>
                                <br/>
                                <br/>

                            </xsl:if>

                            <xsl:copy-of select="$author/s:authorblurb/*"></xsl:copy-of>
                        </div>
                        <div class="clear">&#xA0;</div>

<!--				<div class="nav">
				<xsl:call-template name="notesnavigation"/>
				</div>
-->
                        <xsl:call-template name="footer"/>

                    </div>
                </div>
            </body>
        </html>
    </xsl:template>



<!--     here we make the Head     -->

    <xsl:template name="head">

        <xsl:param name="page"/>
        <xsl:variable name="nextSlide">
            <xsl:choose>
                <xsl:when test="$page = 'title'">slide_1.html</xsl:when>
                <xsl:when test="$page = 'toc'">slide_1.html</xsl:when>
                <xsl:when test="$position!=count(//s:slide)">slide_<xsl:value-of select="$position +1"/>.html</xsl:when>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="prevSlide">
            <xsl:choose>
                <xsl:when test="$page = 'title'">slide_1.html</xsl:when>
                <xsl:when test="$page = 'toc'">slide_1.html</xsl:when>
                <xsl:when test="$position = 1">title.html</xsl:when>
                <xsl:when test="$position!=count(//s:slide)">slide_<xsl:value-of select="$position -1"/>.html</xsl:when>
            </xsl:choose>
        </xsl:variable>


        <head>
            <xsl:value-of select="$br"/>

            <meta name="DC.Title" content="{/s:slideset/s:metadata/s:title}"/>
            <xsl:value-of select="$br"/>

<!-- more than one author -->

            <xsl:choose>
                <xsl:when test="/s:slideset/s:metadata/s:authorgroup">
                    <xsl:element name="meta">
                        <xsl:attribute name="name">DC.Creator</xsl:attribute>
                        <xsl:attribute name="content">
                            <xsl:for-each select="/s:slideset/s:metadata/s:authorgroup/s:author">
                                <xsl:value-of select="normalize-space(s:givenname/text())"/>
                                <xsl:text> </xsl:text>

                                <xsl:choose>
                                    <xsl:when test="position()=last()">
                                        <xsl:value-of select="normalize-space(s:familyname/text())"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="normalize-space(s:familyname/text())"/>
                                        <xsl:text>, </xsl:text>
                                    </xsl:otherwise>
                                </xsl:choose>

                            </xsl:for-each>
                        </xsl:attribute>
                    </xsl:element>
                    <xsl:value-of select="$br"/>

                    <xsl:element name="meta">
                        <xsl:attribute name="name">Author</xsl:attribute>
                        <xsl:attribute name="content">
                            <xsl:for-each select="/s:slideset/s:metadata/s:authorgroup/s:author">
                                <xsl:value-of select="normalize-space(s:givenname/text())"/>
                                <xsl:text> </xsl:text>

                                <xsl:choose>
                                    <xsl:when test="position()=last()">
                                        <xsl:value-of select="normalize-space(s:familyname/text())"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="normalize-space(s:familyname/text())"/>
                                        <xsl:text>, </xsl:text>
                                    </xsl:otherwise>
                                </xsl:choose>

                            </xsl:for-each>
                        </xsl:attribute>
                    </xsl:element>
                    <xsl:value-of select="$br"/>

                </xsl:when>

                <xsl:otherwise>

<!-- only one author -->

                    <meta name="DC.Creator" content="{/s:slideset/s:metadata/s:author/s:givenname} {/s:slideset/s:metadata/s:author/s:familyname}"/>
                    <xsl:value-of select="$br"/>
                    <meta name="Author" content="{/s:slideset/s:metadata/s:author/s:givenname} {/s:slideset/s:metadata/s:author/s:familyname}"/>
                    <xsl:value-of select="$br"/>

                </xsl:otherwise>
            </xsl:choose>

            <meta name="DC.Subject" content="{/s:slideset/s:metadata/dc:subject}"/>
            <xsl:value-of select="$br"/>
            <meta name="DC.Date" content="{/s:slideset/s:metadata/dc:date}"/>
            <xsl:value-of select="$br"/>
            <meta name="DC.Rights" content="{/s:slideset/s:metadata/dc:rights}"/>
            <xsl:value-of select="$br"/>
            <meta name="DC.Format" content="text/html"/>
            <xsl:value-of select="$br"/>
            <meta name="DC.Language" content="{/s:slideset/@xml:lang}"/>
            <xsl:value-of select="$br"/>
            <meta name="DC.Type" content="presentation"/>
            <xsl:value-of select="$br"/>
            <meta name="Keywords" content="{/s:slideset/s:metadata/dc:subject}"/>
            <xsl:value-of select="$br"/>
            <meta name="Robots" content="all"/>
            <xsl:value-of select="$br"/>
            <meta name="Copyright" content="{/s:slideset/s:metadata/dc:rights}"/>
            <xsl:value-of select="$br"/>
            <xsl:value-of select="$br"/>

            <xsl:choose>
                <xsl:when test="$page = 'toc'">
                    <title>
                        <xsl:value-of select="/s:slideset/s:metadata/s:title"/>
                    </title>
                    <xsl:value-of select="$br"/>
                </xsl:when>

                <xsl:when test="$page = 'title'">
                    <title>
                        <xsl:value-of select="/s:slideset/s:metadata/s:title"/>
                    </title>
                    <xsl:value-of select="$br"/>
                </xsl:when>

                <xsl:otherwise>
                    <title>
                        <xsl:value-of select="s:title"/>
                    </title>
                    <xsl:value-of select="$br"/>
                </xsl:otherwise>
            </xsl:choose>

            <xsl:value-of select="$br"/>
            <link rel="stylesheet" href="styles.css" type="text/css"/>
            <xsl:value-of select="$br"/>


            <script type="text/javascript">

function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

//<xsl:text disable-output-escaping="yes"><![CDATA[

function loaded() {

	if (navigator.userAgent.indexOf("MSIE") > 0) {
		document.attachEvent("onkeypress",onKeyPressIE);
		document.getElementById("slidecontent").style.height = "600px";

	} else {
    	    document.addEventListener("keypress",onKeyPress,false);
    	    document.getElementById("slidecontent").style.minHeight = "600px";

    	}

}

function onKeyPress(e)
{
        switch (e.keyCode)
        {
            case e.DOM_VK_LEFT:
            switchSlide(']]></xsl:text>
                <xsl:value-of select="$prevSlide"/>
                <xsl:text disable-output-escaping="yes"><![CDATA[');
            e.preventDefault();
            break;
            case e.DOM_VK_RIGHT:
            switchSlide( ']]></xsl:text>
                <xsl:value-of select="$nextSlide"/>
                <xsl:text disable-output-escaping="yes"><![CDATA[');
            e.preventDefault();
            break;
            case e.DOM_VK_HOME:
            window.location.href = 'title.html';
            e.preventDefault();
            break;
            case e.DOM_VK_END:
            window.location.href = 'toc.html';
            e.preventDefault();
            break;

        }
        switch (e.charCode)
        {

            case e.DOM_VK_SPACE: // backspace
            switchSlide( ']]></xsl:text>
                <xsl:value-of select="$nextSlide"/>
                <xsl:text disable-output-escaping="yes"><![CDATA[');
        	e.preventDefault();

            break;
        }

}

function onKeyPressIE(e) {
	switch(window.event.keyCode)
	{
		case 32:
	        switchSlide(']]></xsl:text>
                <xsl:value-of select="$nextSlide"/>
                <xsl:text disable-output-escaping="yes"><![CDATA[');
        	return false;
	}

}

function switchSlide(href) {

	if (href) {
		window.location.href = href;
	}

}
]]>
</xsl:text>
            </script>

        </head>
        <xsl:value-of select="$br"/>

    </xsl:template>



<!--     here we make the Footer     -->

    <xsl:template name="footer">
        <div class="foot">&#xA9; copyright <xsl:value-of select="substring-before(/s:slideset/s:metadata/dc:date,'-')"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="/s:slideset/s:metadata/dc:rights"/>
        </div>
    </xsl:template>


<!--     here we make the navigation     -->

    <xsl:template name="navigation">
        <xsl:param name="page"/>
        <xsl:param name="nextSlide"/>
        <xsl:param name="prevSlide"/>

        <span class="navl">

            <xsl:choose>
                <xsl:when test="$page = 'title'">
                    <span class="navigationinactive">
                        <strong>&#xA0;Home</strong>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:when>

                <xsl:otherwise>
                    <span class="navigationactive">&#xA0;<a href="title.html"><strong>Home</strong>
                        </a>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:otherwise>
            </xsl:choose>

            <xsl:choose>
                <xsl:when test="$page = 'toc'">
                    <span class="navigationinactive">
                        <strong>TOC</strong>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:when>

                <xsl:otherwise>
                    <span class="navigationactive">
                        <a href="toc.html">
                            <strong>TOC</strong>
                        </a>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:otherwise>
            </xsl:choose>

            <xsl:choose>
                <xsl:when test="$page = 'title'">
                    <span class="navigationinactive">
                        <strong>&#xAB;&#xA0;</strong>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:when>

                <xsl:when test="$position=1">
                    <span class="navigationactive">
                        <a href="title.html">
                            <strong>&#xAB;&#xA0;</strong>
                        </a>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:when>

                <xsl:otherwise>
                    <span class="navigationactive">
                        <a href="slide_{$position -1}.html">
                            <strong>&#xAB;&#xA0;</strong>
                        </a>&#xA0;&#xA0;|&#xA0;&#xA0;</span>
                </xsl:otherwise>
            </xsl:choose>


<!--     here we make the next for the title slide     -->

            <xsl:choose>
                <xsl:when test="string-length($nextSlide) = 0">
                    <span class="navigationinactive">
                        <strong>&#xA0;&#xBB;</strong>
                    </span>
                </xsl:when>
                <xsl:otherwise>
                    <span class="navigationactive">
                        <a href="{$nextSlide}">
                            <strong>&#xA0;&#xBB;</strong>
                        </a>
                    </span>
                </xsl:otherwise>
            </xsl:choose>
        </span>


<!--     here we make the text Slide .. of ..  and the show notes   -->

        <xsl:choose>
            <xsl:when test="$page = 'title'">
                <span class="slidenumber">
                    <xsl:text>Title - Slide</xsl:text>
                </span>
            </xsl:when>

            <xsl:when test="$page = 'toc'">
                <span class="slidenumber">
                    <xsl:text>TOC - Slide</xsl:text>
                </span>
            </xsl:when>

            <xsl:otherwise>
                <span class="slidenumber">
                    <span class="navr">
                        <xsl:text>|&#xA0;&#xA0;</xsl:text>
                    </span>
                    <strong>
                        <xsl:text>Slide </xsl:text>
                        <xsl:value-of select="$position"/>
                    </strong>
                    <xsl:text> of </xsl:text>
                    <xsl:value-of select="count(//s:slide)"/>
                </span>

                <xsl:if test="s:notes">
                    <span class="slidenumber">
                        <a href="#" onClick="MM_openBrWindow('slidenotes_{$position}.html','Notes','scrollbars=yes,width=700,height=500')">
                            <img src="note_icon.gif" alt="show notes" border="0"/>
                        </a>
                    </span>
                    <span class="navr">
                        <xsl:text>&#xA0;&#xA0;</xsl:text>
                    </span>
                </xsl:if>
            </xsl:otherwise>
        </xsl:choose>
        <br class="clear"/>

    </xsl:template>



<!--     here we make the navigation for the notes     -->

    <xsl:template name="notesnavigation">

        <span class="slidenumber">
            <span class="navr">
                <xsl:text>|</xsl:text>
            </span>
            <span class="navigationactive">
                <a href="#" onClick="window.close()">
                    <strong>&#xA0;close window</strong>
                </a>
            </span>
        </span>
        <br class="clear"/>

    </xsl:template>




<!-- here we make the xhtml code for the slides 1 to ... -->

    <xsl:template name="slide">
        <xsl:param name="position"/>
        <!-- <xsl:value-of select="/s:slideset/s:slide[$position]"/> -->

        <xsl:variable name="nextSlide">
            <xsl:choose>
                <xsl:when test="$page = 'title'">slide_1.html</xsl:when>
                <xsl:when test="$page = 'toc'">slide_1.html</xsl:when>
                <xsl:when test="$position!=count(//s:slide)">slide_<xsl:value-of select="$position + 1"/>.html</xsl:when>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="prevSlide">
            <xsl:choose>
                <xsl:when test="$page = 'title'">slide_1.html</xsl:when>
                <xsl:when test="$page = 'toc'">slide_1.html</xsl:when>
                <xsl:when test="$position!=count(//s:slide)">slide_<xsl:value-of select="$position - 1"/>.html</xsl:when>
            </xsl:choose>
        </xsl:variable>


        <html>
            <xsl:call-template name="head"/>

            <body onload="loaded()">
                <xsl:value-of select="$br"/>
                <div id="all">
                    <div class="content">
                        <div class="slidecap">
                            <div class="slidetitle">
                                <table border="0" width="100%">
                                    <tr>
                                        <td valign="bottom">
                                            <span class="lg">
                                                <a href="title.html">
                                                    <xsl:value-of select="/s:slideset/s:metadata/s:title"/>
                                                </a>
                                            </span>
                                        </td>

                                        <td valign="bottom">
<a href="http://www.phpug.ch/">
                                            <img valign="top" src="img/phpug_logo.gif" align="right" alt="php user group schweiz" border="0"/>
</a>   
                                     </td>

                                    </tr>
                                </table>

                            </div>
                            <p class="none"></p>

                            <div class="nav2">
                                <xsl:call-template name="navigation">
                                    <xsl:with-param name="nextSlide" select="$nextSlide"/>
                                    <xsl:with-param name="prevSlide" select="$prevSlide"/>
                                </xsl:call-template>
                            </div>
                        </div>

                        <div class="slidecontent" id="slidecontent">

                            <h1 class="titel">
                                <xsl:value-of select="s:title"/>
                            </h1>

                            <xsl:apply-templates select="s:content"/>

                        </div>
                        <div class="clear">&#xA0;</div>

                        <div class="nav">
                            <xsl:call-template name="navigation">
                                <xsl:with-param name="nextSlide" select="$nextSlide"/>
                                <xsl:with-param name="prevSlide" select="$prevSlide"/>
                            </xsl:call-template>

                        </div>

                        <xsl:call-template name="footer"/>
                    </div>
                </div>
            </body>
        </html>

    </xsl:template>



<!-- here we make the xhtml code for the slidenotes 1 to ... -->

    <xsl:template name="slidenotes">
        <xsl:param name="position"/>
        <xsl:value-of select="s:slideset/s:slide/s:notes[position() = $position]"/>

        <html>
            <xsl:call-template name="head"/>

            <body>
                <xsl:value-of select="$br"/>
                <div id="all">
                    <div class="content">
                        <div class="slidecap">
                            <div class="slidetitle">
                                <table border="0" width="100%">
                                    <tr>
                                        <td valign="bottom">
                                            <span class="lg">
                                                <a class="next" href="#">
                                                    <xsl:text>Notes from Slide Nr. </xsl:text>
                                                    <xsl:value-of select="$position"/>
                                                </a>
                                            </span>
                                        </td>
                                        <!--
                                        <td valign="bottom">
											<img valign="top" src="bitflux_logo.gif" align="right" alt="" width="185" height="30" border="0"/>
			     </td>
                 
                 -->
                                    </tr>
                                </table>
			

                            </div>
                            <p class="none"></p>

                            <div class="nav2">
                                <xsl:call-template name="notesnavigation"/>
                            </div>
                        </div>

                        <div class="slidenotecontent">

                            <h1 class="titel">
                                <xsl:value-of select="s:title"/>
                            </h1>

                            <xsl:apply-templates select="s:notes"/>

                        </div>
                        <div class="clear">&#xA0;</div>

                        <div class="nav">
                            <xsl:call-template name="notesnavigation"/>
                        </div>

                        <xsl:call-template name="footer"/>
                    </div>
                </div>
            </body>
        </html>

    </xsl:template>


    <xsl:template match="s:notes//code">
        <pre>
            <xsl:copy>
                <xsl:for-each select="@*">
                    <xsl:copy/>
                </xsl:for-each>
                <xsl:apply-templates/>
            </xsl:copy>
        </pre>

    </xsl:template>

    <xsl:template match="s:notes//*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="s:notes">
        <xsl:apply-templates />
    </xsl:template>

<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->

    <xsl:template match="s:content//code">
        <pre>
            <xsl:copy>
                <xsl:for-each select="@*">
                    <xsl:copy/>
                </xsl:for-each>
                <xsl:apply-templates/>
            </xsl:copy>
        </pre>
    </xsl:template>

    <xsl:template match="s:content//*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="s:content">
        <xsl:apply-templates />
    </xsl:template>



</xsl:stylesheet>