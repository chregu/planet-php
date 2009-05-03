<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"

xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:syn="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/"
xmlns:php="http://php.net/xsl"


>
<xsl:import href="../../inc/options.xsl"/>
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:include href="common-new.xsl"/>
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
            <body id="blogug-ch">
              <div id="wrap">
            <xsl:call-template name="bodyhead"/>
          
                <div id="content">
<div id="navigation">
	<!--<ul class="subnav">
		<li><a href="1">subscribe</a></li>
		<li><a href="2">claim</a></li>
		<li><a href="3" class="current">opml</a></li>
		<li><a href="4">click here</a></li>
	</ul>
	<ul class="subnav">
		<li><a href="5">subscribe</a></li>
		<li><a href="6">claim</a></li>
		<li><a href="7">opml</a></li>
		<li><a href="8">click here</a></li>
	</ul>
	<ul class="miscnav">
		<li><a href="9">misc</a></li>
		<li><a href="0">centered</a></li>
		<li><a href="99">stuff</a></li>
		<li><a href="88">buttons ?</a></li>
	</ul>-->
     <xsl:call-template name="rightcol"/>
</div>

<div id="contentxt">

                <xsl:call-template name="middlecol"/>
               
</div>
</div>
</div>
            </body>
        </html>
    </xsl:template>
    <xsl:template name="rightcol">
            <div class="menu">
                <fieldset><legend>Search Planet</legend>
                

                    <form onsubmit="niceURL(); return false;" name="search" method="get" action="/">
                        <input id="searchtext" type="text" size="13" name="search">
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
 
        
<xsl:if test="/planet/tags/tag">
<div class="menu">
                <fieldset>
                
                    <legend>Top Tags last 3 days</legend>
                    <xsl:apply-templates select="/planet/tags/tag"/>
                    

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
                
                    <legend><a href="/tag/{/planet/search/tag}">Related Tags for <br/><xsl:value-of select="/planet/search/tag"/></a></legend>

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
        
    </xsl:template>
 <xsl:template match="tags/tag">
     <a href="/tag/{taggroup}" title="{c}" class="blogTagPad">
 <xsl:value-of select="taggroup"/>&#160;(<xsl:value-of select="c"/>)</a> <xsl:text> </xsl:text>
 </xsl:template>
 
 <xsl:template match="tags/tag" mode="right">
     <a href="/tag/{taggroup}" title="{c}" class="blogTagPad">
  (<xsl:value-of select="c"/>)<xsl:value-of select="taggroup"/></a>  <xsl:text> </xsl:text>
 </xsl:template>
 
  <xsl:template match="links/link">
  <a  title="/search/linkex:{link}" style="text-decoration: none; float: left" href="/search/linkex:{php:functionString('str_replace','%2F','/',php:functionString('urlencode',substring-after(link,'http://')))}">+</a>

     <a  title="{link}" href="{link}"  class="blogLinkPad">
 <xsl:value-of select="php:functionString('popoon_components_generators_planetch::truncate',link,'28')"/>&#160;(<xsl:value-of select="c"/>)</a> 
 </xsl:template>
 
 <xsl:template match="relatedtags/tag">
<a  title="{/planet/search/tag}+{tag}" style="text-decoration: none; float: left" href="/tag/{/planet/search/tag}+{tag}">+</a>
 
 <a  href="/tag/{tag}" title="{tag} ({c})" class="blogLinkPad">
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
                                    <a href="{$searchString}?start={$startEntry + 10}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
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
                                        <a href="{$searchString}?start={$startEntry - 10}">Previous 10 Newer Entries</a>
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
       <h3> <a href="{blog_link}">
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
                    </h3>
           <h4>

                <a href="{link}" class="blogTitle">
                    <xsl:value-of select="title"/>
                </a>
                </h4>
                <p class="date">
                <xsl:text> </xsl:text> 
            (<xsl:value-of select="dc_date"/>)
            </p>
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
                <p class="planetLinks">
                Planet Tags: <xsl:call-template name="printTags">
                <xsl:with-param name="tags" select="tags"/>
                </xsl:call-template>
                </p>
                </xsl:if>
                
                <p class="more">
                <a href="{link}">Link</a>
                <xsl:if test="more">
                <span id="more{id}">
              |  <a onclick="moreInfoStart('{blog_id}','{id}'); return false;" href="/more/{blog_id}/{id}/">More Info on this post </a>
                </span>
                
                </xsl:if>
                </p>
                <div style="display: none" class="morediv" id="morediv{id}"></div>
                
        </div>
    </xsl:template>
    
    <xsl:template name="printTags">
        <xsl:param name="tags"/>
        <xsl:choose>
            <xsl:when test="contains($tags,' , ')"> 
                <xsl:variable name="tag" select="substring-before($tags,' , ')"/>
                <a href="/tag/{$tag}"><xsl:value-of select="$tag"/></a>, 
                <xsl:call-template name="printTags">
                    <xsl:with-param name="tags" select="substring-after($tags,' , ')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <a href="/tag/{$tags}"><xsl:value-of select="$tags"/></a>
            </xsl:otherwise>
        </xsl:choose>
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
    </xsl:template>

    <xsl:template name="htmlheadtitle">
   Planet Switzerland
   </xsl:template>

</xsl:stylesheet>
