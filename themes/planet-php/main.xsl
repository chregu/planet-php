<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"

xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:syn="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/"

>
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:include href="common.xsl"/>
    <xsl:include href="planetarium.xsl"/>
    <xsl:param name="startEntry" value="'0'"/>
    <xsl:variable name="searchString" select="/planet/search/string"/>
    <xsl:template match="/">

        <html>

            <xsl:call-template name="htmlhead"/>
            <body >
                <xsl:call-template name="bodyhead"/>

                <xsl:call-template name="middlecol"/>
                <xsl:call-template name="rightcol"/>
<script language="JavaScript" src="/js/search.js" type="text/javascript">
</script>
            </body>
        </html>
    </xsl:template>
    <xsl:template name="rightcol">
        <div id="rightcol">
            <div class="menu">
                <fieldset>
                    <legend>Search Planet PHP </legend>

                    <form onsubmit="niceURL(); return false;" name="search" method="get" action="/">
                        <input id="searchtext" type="text" name="search">
                            <xsl:if test="/planet/search/string">
                                <xsl:attribute name="value">
                                    <xsl:value-of select="/planet/search/string"/>
                                </xsl:attribute>
                            </xsl:if>
                        </input>
                       <input class="submit" type="submit" value="Go"/>
                    </form>
		    <a id="searchbarLink" href="javascript:addEngine()">Mozilla Searchbar</a>
                </fieldset>
            </div>
            <div class="menu">
                <fieldset>
                    <legend>Twitter</legend>

                    <div class="nnbe">Follow <a class="inlineBlogLink" href="http://twitter.com/planetphp">@planetphp</a> on Twitter</div>
            </fieldset>
            </div>
<!--
<div class="menu" >
<fieldset style="padding-right: 0px; ">
<legend>Sponsored Links</legend>
<script type="text/javascript">
google_ad_client = "pub-9425542660030190";
google_ad_width = 250;
google_ad_height = 250;
google_ad_format = "250x250_as";
google_ad_channel ="";
google_color_border = "FFFFFF";
google_color_bg = "FFFFFF";
google_color_link = "666699";
google_color_url = "666699";
google_color_text = "000000";
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</fieldset>
</div>
-->
<!--
  <div class="menu">
<fieldset><legend><a href="http://del.icio.us/tag/php" title="PHP Radar">del.icio.us/tag/php</a></legend>
         <xsl:for-each select="/planet/rdf:RDF/rss:item[position() &lt; 11]">
        <a title="{rss:description} Categories: {dc:subject}" class="blogLinkPad" href="{rss:link}"><xsl:value-of select="rss:title"/></a>
         </xsl:for-each>
             </fieldset>
         </div>
        <div class="menu">
        <fieldset><legend>PEAR/PECL Releases</legend>
         <xsl:apply-templates select="/planet/entries[@section='releases']"/>
             </fieldset>
         </div>
       
-->        

            <div class="menu">
                <fieldset>
                    <legend>Blogs</legend>

                    <xsl:apply-templates select="/planet/blogs/blog"/>
                </fieldset>
            </div>
            <xsl:call-template name="buttons"/>
        </div>
    </xsl:template>

    <xsl:template match="blogs/blog">
<xsl:if test="maxdate &gt; border">
        <a href="{link}" class="blogLinkPad">
    <xsl:choose>
                <xsl:when test="string-length(author) &gt; 0 ">           
                <xsl:value-of select="author"/>     
<xsl:if test="dontshowblogtitle = 0"> (<xsl:value-of select="title"/>) </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                <xsl:value-of select="title"/>
                </xsl:otherwise>
               </xsl:choose> 
        </a>
</xsl:if>
    </xsl:template>
    
    
        <xsl:template match="/planet/entries[@section='releases']/entry">

        <a href="{link}" class="blogLinkPad">
            <xsl:value-of select="title"/>
        </a>
    </xsl:template>
    <xsl:template name="middlecol">
        <div id="middlecontent">
            <xsl:apply-templates select="/planet/entries[@section='default']/entry"/>
            <xsl:variable name="nextEntries">
                <xsl:choose>
                    <xsl:when test="(/planet/search/count - (/planet/search/start + 10)) &gt;= 10">10</xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="(/planet/search/count - (/planet/search/start + 10))"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <div id="pageNavi">
                <fieldset>
                    <legend>More Entries</legend>

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
                    <br/>
                </fieldset>
            </div>

        </div>

    </xsl:template>

    <xsl:template match="entries[@section='default']/entry">
        <div class="box">
            <fieldset>
                <legend>
                    <a href="{blog_link}">
    <xsl:choose>
                <xsl:when test="string-length(blog_author) &gt; 0 ">           
                <xsl:value-of select="blog_author"/>     
<xsl:if test="blog_dontshowblogtitle = 0"> (<xsl:value-of select="blog_title"/>) </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                <xsl:value-of select="blog_title"/>
                </xsl:otherwise>
               </xsl:choose> 
                    </a>

                </legend>

                <a href="{link}" class="blogTitle">
                    <xsl:value-of select="title"/>
                </a>
                <xsl:text> </xsl:text> 
            (<xsl:value-of select="dc_date"/> UTC)
<div class="feedcontent" >
<xsl:choose>
<xsl:when test="string-length(content_encoded) &gt; 0">
             <xsl:value-of select="content_encoded" disable-output-escaping="yes"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="description" disable-output-escaping="yes"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>

                <a href="{link}">Link</a>
            </fieldset>
        </div>
    </xsl:template>


    <xsl:template name="buttons">
        <div class="buttons">
            <fieldset>
                <legend>Links</legend>
                <a href="./rss/">
                    <img border="0" alt="RSS 0.92" src="/images/rss092.gif" width="80" height="15"/>
                </a>
                 &#160;
                 <a href="./rdf/"><img border="0" alt="RDF 1." src="/images/rss1.gif" width="80" height="15"/>
                </a>


                <br/>
                <a href="./atom/">
                    <img border="0" alt="Atom Feed" src="/images/atompixel.png" width="80" height="15"/>
                </a>
                  &#160;
                  <a href="http://www.popoon.org/"><img alt="100% Popoon" border="0" src="/images/popoon.png" width="80" height="15"/>
                </a>

                <br/>
                <a href="http://www.php.net/">
                    <img border="0" alt="PHP5 powered" src="/images/phppowered.png" width="80" height="15"/>
                </a>
                  &#160;
                  <a href="http://pear.php.net/"><img alt="PEAR" border="0" src="/images/pearpowered.png" width="80" height="15"/>
                </a>
            </fieldset>
        </div>


        <xsl:call-template name="commonRightBoxes"/>
        <div class="menu">
            <xsl:call-template name="planetarium"/>
        </div>

        </xsl:template>
    <xsl:template name="htmlheadtitle">
   Planet PHP
   </xsl:template>

</xsl:stylesheet>
