<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"

xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:syn="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/"
xmlns:php="http://php.net/xsl"


>
<xsl:import href="../../inc/options.xsl"/>
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:include href="common.xsl"/>
<xsl:param name="webroot"/>
    
    <xsl:param name="startEntry" value="'0'"/>
    <xsl:variable name="searchString">
    <xsl:choose>
    <xsl:when test="/planet/search/string">/search/<xsl:value-of select="/planet/search/string"/></xsl:when>
    <xsl:when test="/planet/search/tag">/tag/<xsl:value-of select="/planet/search/tag"/></xsl:when>
    </xsl:choose>
    </xsl:variable>
    <xsl:template match="/">

        <html>

            <xsl:call-template name="htmlhead"/>
            <body onload="enableTooltips()">
   <xsl:call-template name="bodyhead"/>

                <xsl:call-template name="middlecol"/>
                <xsl:call-template name="rightcol"/>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-424540-3";
urchinTracker();
</script>

            </body>
        </html>
    </xsl:template>
    <xsl:template name="rightcol">
        <div id="rightcol">
            <div class="menu">
                <fieldset>
                    <legend>Search Planet Switzerland </legend>

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
<!--		    <a id="searchbarLink" href="javascript:addEngine()">Mozilla Searchbar</a>-->
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
       
        
<xsl:if test="/planet/tags/tag">
<div class="menu">
                <fieldset>
                
                    <legend>Top Tags last 3 days</legend>
<div style="float: left;">
                    <xsl:apply-templates select="/planet/tags/tag[position() &lt;= 15]"/>
                    </div>
<div style="text-align: right;  margin-left: 120px;">
                    <xsl:apply-templates select="/planet/tags/tag[position() &gt; 15]" mode="right"/>
                    </div>

                    </fieldset>
            </div>
            </xsl:if>
            
<xsl:if test="/planet/links/link">
<div class="menu">
                <fieldset>
                
                    <legend>Top Links last 7 days</legend>

                    <xsl:apply-templates select="/planet/links/link"/>
                </fieldset>
            </div>
            </xsl:if>
            
        
<xsl:if test="/planet/relatedtags/tag">
<div class="menu">
                <fieldset>
                
                    <legend><a href="{$webroot}tag/{/planet/search/tag}">Related Tags for <br/><xsl:value-of select="/planet/search/tag"/></a></legend>

                    <xsl:apply-templates select="/planet/relatedtags/tag"/>
                </fieldset>
            </div>
            </xsl:if>
<!--            
            <div class="menu">
                <fieldset>
                    <legend>Blogs</legend>

                    <xsl:apply-templates select="/planet/blogs/blog"/>
                </fieldset>
            </div>-->
            <xsl:call-template name="buttons"/>
        </div>
    </xsl:template>
 <xsl:template match="tags/tag">
     <a href="{$webroot}tag/{taggroup}" title="{c}" class="blogLinkPad">
 <xsl:value-of select="taggroup"/> (<xsl:value-of select="c"/>)</a> 
 </xsl:template>
 
 <xsl:template match="tags/tag" mode="right">
     <a href="{$webroot}tag/{taggroup}" title="{c}" class="blogLinkPad">
  (<xsl:value-of select="c"/>) <xsl:value-of select="taggroup"/></a> 
 </xsl:template>
 
  <xsl:template match="links/link">
  <a  title="/search/linkex:{link}" style="text-decoration: none; float: left" href="{$webroot}search/linkex:{php:functionString('str_replace','%2F','/',php:functionString('urlencode',substring-after(link,'http://')))}">+</a>

     <a style="margin-left: 10px;" title="{link}" href="{link}"  class="blogLinkPad">
 <xsl:value-of select="php:functionString('popoon_components_generators_planetch::truncate',link,'38')"/>&#160;(<xsl:value-of select="c"/>)</a> 
 </xsl:template>
 
 <xsl:template match="relatedtags/tag">
<a  title="{/planet/search/tag}+{tag}" style="text-decoration: none; float: left" href="{$webroot}tag/{/planet/search/tag}+{tag}">+</a>
 
 <a style="margin-left: 10px;" href="{$webroot}tag/{tag}" title="{tag} ({c})" class="blogLinkPad">
     <xsl:value-of select="tag"/><!-- (<xsl:value-of select="c"/>, <xsl:value-of select="c2"/>)--></a> 
 
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
                    <xsl:when test="(/planet/search/count - (/planet/search/start + 15)) &gt;= 15">15</xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="(/planet/search/count - (/planet/search/start + 15))"/>
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
                                    <a href="{$searchString}?start={$startEntry + 15}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
                                </xsl:when>
                                 <xsl:otherwise>
                                    <a href="{$webroot}?start={$startEntry + 15}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:if>
                   

                    </span>
                    <span style="float: left;">
                        <xsl:choose>
                            <xsl:when test="$startEntry = 0 and $nextEntries &lt;= 0">
                             No More Entries
                             </xsl:when>
                            <xsl:when test="$startEntry &gt;= 15">
                                <xsl:choose>
                                    <xsl:when test="$searchString">
                                        <a href="{$searchString}?start={$startEntry - 15}">Previous 15 Newer Entries</a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <a href="{$webroot}?start={$startEntry - 15}">Previous 15 Newer Entries</a>
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
            (<xsl:value-of select="dc_date"/>)
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
                <xsl:if test="string-length(tags) &gt; 0">
                Planet Tags: <xsl:call-template name="printTags">
                <xsl:with-param name="tags" select="tags"/>
                </xsl:call-template>
                <br/><br/>
                </xsl:if>
                <a href="{link}">Link</a>
                <xsl:if test="more">
                <span id="more{id}">
              |  <a onclick="moreInfoStart('{blog_id}','{id}'); return false;" href="/moreinfo/{blog_id}/{id}/">More Info on this post </a>
                </span>
                <div style="display: none" id="morediv{id}"></div>
                </xsl:if>
            </fieldset>
        </div>
    </xsl:template>
    
    <xsl:template name="printTags">
        <xsl:param name="tags"/>
        <xsl:choose>
            <xsl:when test="contains($tags,' , ')"> 
                <xsl:variable name="tag" select="substring-before($tags,' , ')"/>
                <a href="{$webroot}tag/{$tag}"><xsl:value-of select="$tag"/></a>, 
                <xsl:call-template name="printTags">
                    <xsl:with-param name="tags" select="substring-after($tags,' , ')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <a href="{$webroot}tag/{$tags}"><xsl:value-of select="$tags"/></a>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>


    <xsl:template name="buttons">
        <div class="buttons">
            <fieldset>
                <legend>Links</legend>
                <a href="/rss/">
                    <img border="0" alt="RSS 0.92" src="/images/rss092.gif" width="80" height="15"/>
                </a>
                 &#160;
                 <a href="/rdf/"><img border="0" alt="RDF 1." src="/images/rss1.gif" width="80" height="15"/>
                </a>


                <br/>
                <a href="/atom/">
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
    </xsl:template>

    <xsl:template name="htmlheadtitle">
   Planet Switzerland
   </xsl:template>

</xsl:stylesheet>
