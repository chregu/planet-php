<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
<xsl:param name="startEntry" value="'0'"/>
    <xsl:variable name="searchString" select="/planet/search/string"/>
    
    <xsl:template match="/">

        <html>

            <xsl:call-template name="htmlhead"/>
            <body>
                <xsl:call-template name="bodyhead"/>
                <xsl:call-template name="leftcol"/>
                <xsl:call-template name="middlecol"/>
               
            </body>
        </html>
    </xsl:template>
    <xsl:template name="leftcol">

        <div id="menu">
          <H2>Planet OSCOM</H2>
<P>A collection of blog postings from people and communities involved in the <A href="/get-involved/">OSCOM process</A>. <a href="/planet-oscom/about/">Learn more</a>.</P>
<div class="menu">
            <h2>Search Planet OSCOM </h2>

                    <form onsubmit="niceURL(); return false;" name="search" method="get" action="/">
                        <input id="searchtext" type="text" name="search" size="12">
                            <xsl:if test="/planet/search/string">
                                <xsl:attribute name="value">
                                    <xsl:value-of select="/planet/search/string"/>
                                </xsl:attribute>
                            </xsl:if>
                        </input>
                       <input class="submit" type="submit" value="Go"/>
                    </form>
		        
            </div>

<h2>This feed courtesy of</h2>
        <xsl:apply-templates select="/planet/blogs/blog"/>
       
        <div  style="padding-left: 5px; line-height: 18px; padding-top: 15px;">
       <a href="./rss/">
                    <img border="0" alt="RSS 0.92" src="/images/rss092.gif" width="80" height="15"/>
                </a>
                 <xsl:text> </xsl:text>
                 <a href="./rdf/"><img border="0" alt="RDF 1." src="/images/rss1.gif" width="80" height="15"/>
                </a>


                <br/>
                <a href="./atom/">
                    <img border="0" alt="Atom Feed" src="/images/atompixel.png" width="80" height="15"/>
                </a>
                <xsl:text> </xsl:text>
                  <a href="http://www.bitflux.ch/developer/cms/popoon.html"><img alt="100% Popoon" border="0" src="/images/popoon.png" width="80" height="15"/>
                </a>

                <br/>
                <a href="http://www.php.net/">
                    <img border="0" alt="PHP5 powered" src="/images/phppowered.png" width="80" height="15"/>
                </a>
                  <xsl:text> </xsl:text>
                  <a href="http://planet-php.net/"><img alt="Planet PHP powered" title="Planet PHP powered" border="0" src="/images/planet-php-button-2.jpg" width="80" height="15"/>
                </a>
       
              </div>
          </div> 
        
 
    </xsl:template>
<xsl:template match="blogs/blog">

 <div class="nnbe" style="padding-left: 10px;">
                <a href="{link}" class="nnbr"><xsl:value-of select="title"/></a>
            </div>  
            
            
            
            
</xsl:template>
    <xsl:template name="middlecol">
        <div id="content">
            <xsl:apply-templates select="/planet/entries/entry"/>
             <xsl:variable name="nextEntries">
                <xsl:choose>
                    <xsl:when test="(/planet/search/count - (/planet/search/start + 10)) &gt;= 10">10</xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="(/planet/search/count - (/planet/search/start + 10))"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            
            
              <div id="pageNavi">
                

                    <span style="float: right;">

                        <xsl:if test="$nextEntries &gt; 0">
                            <xsl:choose>
                                <xsl:when test="$searchString">
                                    <a href="/search/{$searchString}?start={$startEntry + 10}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
                                </xsl:when>
                                 <xsl:otherwise>
                                    <a href="/?start={$startEntry + 10}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:if>
                   

                    </span>
                    <span style="float: left;">
                        <xsl:choose>
                            <xsl:when test="$startEntry = 0 and $nextEntries &lt;= 0">
                             No More Entries
                             </xsl:when>
                            <xsl:when test="$startEntry &gt;= 10">
                                <xsl:choose>
                                    <xsl:when test="$searchString">
                                        <a href="/search/{$searchString}?start={$startEntry - 10}">Previous 10 Newer Entries</a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <a href="/?start={$startEntry - 10}">Previous 10 Newer Entries</a>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </xsl:when>

                        </xsl:choose>

                    </span>
            
            </div>
            
           <xsl:call-template name="bodyfooter"/>
        </div>
    </xsl:template>

    <xsl:template match="entries/entry">
        
        <div class="rss-item">
	<h2  class="rss-item-title">  <a href="{link}">
                <xsl:value-of select="title"/>
            </a>
      
  </h2>
  <div class="rss-item-postinfo">
             <a href="{blog_link}"><xsl:value-of select="blog_title"/></a>
          
            <xsl:text> </xsl:text> 
            - <xsl:value-of select="dc_date"/>
            </div>
       <p class="feedcontent">
             <xsl:value-of select="content_encoded" disable-output-escaping="yes"/>
             </p>
           
        </div>
    </xsl:template>



    <xsl:template name="htmlhead">

        <head>

            <title>OSCOM - Planet OSCOM</title>
            <link href="http://www.oscom.org/style.css" rel="stylesheet" type="text/css"/>
        <script language="JavaScript" src="/js/search.js" >
</script>

  <link rel="alternate" type="application/rss+xml" title="RSS" href="http://planet.oscom.org/rss/" />
  <link rel="alternate" type="application/rdf+xml" title="RDF" href="http://planet.oscom.org/rdf/" />
  <link rel="alternate" type="application/atom+xml" title="Atom" href="http://planet.oscom.org/atom/" />
        </head>
    </xsl:template>

    <xsl:template name="bodyhead">

      <div id="head">
  <a href="http://www.oscom.org/"><img src="http://www.oscom.org/attachment/72c7a5e5939d06b8545b6a41cc703144/09365eda8d22e97058317cd3147a370a/oscom-logo.png"
   width="329" height="54" alt="OSCOM - Open Source Content Management" 
   title="OSCOM - Open Source Content Management" border="0" /></a>
  <div id="topnavi">
    <a href="http://www.oscom.org/" class="navigationwhite">Home</a><xsl:text> </xsl:text>
<a href="http://www.oscom.org/events/" class="navigationwhite">Events</a><xsl:text> </xsl:text>
<a href="http://www.oscom.org/matrix/index.html" class="navigationwhite">CMS Matrix</a><xsl:text> </xsl:text>

<a href="http://www.oscom.org/standards/" class="navigationwhite">Standards</a><xsl:text> </xsl:text>
<a href="http://www.oscom.org/projects/" class="navigationwhite">Projects</a><xsl:text> </xsl:text>
<a href="http://www.oscom.org/get-involved/" class="navigationwhite">Get Involved</a><xsl:text> </xsl:text>
<a href="http://planet.oscom.org/" class="navigationselected">Planet OSCOM</a><xsl:text> </xsl:text>
<a href="http://www.oscom.org/gallery/" class="navigationwhite">Photos</a><xsl:text> </xsl:text>
<a href="http://www.oscom.org/wiki/" class="navigationwhite">Wiki</a><xsl:text> </xsl:text>

  </div>
</div>

    </xsl:template>

    <xsl:template name="bodyfooter">

        <div id="footer">
           
&#169; 2001-2004 OSCOM.<br/>
            <a href="/" class="breadcrumb">OSCOM</a> &gt; Planet OSCOM</div>


    </xsl:template>

</xsl:stylesheet>
