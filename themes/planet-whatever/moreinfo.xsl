<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"
        xmlns:php="http://php.net/xsl"
        exclude-result-prefixes="php">
    <xsl:param name="webroot"/>

    <xsl:template match="/">

        <div>
        <xsl:variable name="blog" select="/planet/blog/blog[1]"/>
        <xsl:variable name="entry" select="/planet/entry/entry[1]"/>
        <a style="float: left;" onclick="return openMore('div{$entry/id}',false)" href="#" class="listMore">x</a>
        <h5>More info on this post:</h5>
        <p>All links go to external sites, except the (Planet Search) ones</p>
            <xsl:variable name="postlinks" select="/planet/postlinks"/>
            <xsl:if test="$postlinks/link">
                
                    <h6>Swiss blogs linking to this post (<a href="{$webroot}search/linkex:{substring-after($entry/link,'http://')}">Planet Search</a>)</h6>
                     <xsl:apply-templates select="$postlinks/link"/>
                
            </xsl:if>
            
            <xsl:variable name="bloglinks" select="/planet/bloglinks"/>
            <xsl:if test="$bloglinks/link">
                    <h6>Swiss blogs linking to this blog in general (<a href="{$webroot}search/link:{substring-after($blog/link,'http://')}">Planet Search</a>)</h6>
                    <xsl:apply-templates select="$bloglinks/link[position() &lt; 6]"/>
                    <xsl:if test="$bloglinks/link[6]">
                    <a href="#" onclick="this.style.display='none'; return openMore('blog{/planet/search/entry}');">More (<xsl:value-of select="count($bloglinks/link) - 5"/>)</a>
                    <div id="moreblog{/planet/search/entry}" style="display: none;">
                        <xsl:apply-templates select="$bloglinks/link[position() &gt; 5]"/>
                    </div>
                    </xsl:if>
                    
                
            </xsl:if>
            
            <h6>General Blog Info</h6>
            <xsl:variable name="top100" select="/planet/top100/link"/>

            
           
            <a href="http://www.blogug.ch/gallery/{/planet/blog/blog/listid}.png.html"><img style="float: right;"  onmouseout="openMore('Gall{/planet/search/entry}',false)" onmouseover="openMore('Gall{/planet/search/entry}',true)" border="0" src="http://www.blogug.ch/files/swissblogs/thumbs/{/planet/blog/blog/listid}.png"/></a>
            <img class="gallBig" src="http://www.blogug.ch/files/swissblogs/{/planet/blog/blog/listid}.png" id="moreGall{/planet/search/entry}"/>
            <p>
Blog Title: <xsl:value-of select="$blog/title"/>
                <br/>
Blog Link: <a href="{$blog/link}"><xsl:value-of select="$blog/link"/>
                </a> (<a href="{$webroot}search/site:{substring-after($blog/link,'http://')}">Planet Search</a>)
                <br/>
                <xsl:if test="$blog/description/text()">
Blog Description: <xsl:value-of select="$blog/description"/>
                    <br/>
                </xsl:if>
Blog Tags: 
<xsl:call-template name="printTags">
<xsl:with-param name="tags" select="$top100/tags"/>
</xsl:call-template>
<br/>
                Feedlink: <a href="{$blog/feed_link}"><xsl:value-of select="$blog/feed_link"/>
                </a>
                <xsl:text> </xsl:text>
                (<a href="feed:{$blog/feed_link}">Subscribe</a>)
                <br/>
   
Last Post: <xsl:value-of select="$blog/lastpost"/>
</p>
      <xsl:if test="number($blog/lon) != 0 and $blog/lon/text()">
      <h6>Blog Geo Information</h6>
    Coordinates: <xsl:value-of select="$blog/lat"/>/<xsl:value-of select="$blog/lon"/> (<a href="http://multimap.com/map/browse.cgi?lat={$blog/lat}&amp;lon={$blog/lon}&amp;scale=2000000&amp;icon=x">Multimap</a>)<br/> 
    City: <xsl:value-of select="$blog/city"/> (<a href="{$webroot}search/city:{$blog/city}">Planet Search</a>)<br/>
    Canton: <xsl:value-of select="$blog/canton"/> (<a href="{$webroot}search/canton:{$blog/canton}">Planet Search</a>)<br/>
    Country: <xsl:value-of select="$blog/country"/> (<a href="{$webroot}search/country:{$blog/country}">Planet Search</a>)<br/>
    </xsl:if>       
            <xsl:if test="$top100/rang &gt; 0">
                    <h6>Top 100 info</h6>
<p>
Top 100 Rank: <xsl:value-of select="$top100/rang"/>
                    <br/>
Incoming Blogs: <xsl:value-of select="$top100/blogs"/>
                    <br/>
Incoming Links: <xsl:value-of select="$top100/links"/>
                    <br/>
                    <a href=" http://top100.blogug.ch/archive.php?id={$top100/id}">Top 100 History</a>
               </p>
                
            </xsl:if>

            

        </div>
    </xsl:template>
<xsl:template match="link">

<p>                            <a href="{url}">
                                <xsl:value-of select="entry_title"/>
                            </a>
                            <br/>
                            <xsl:if test="string-length(blog_title/text()) &gt; 0">
by <a href="{blog_link}"><xsl:value-of select="blog_title"/>
                            </a>
                           
                            </xsl:if>
                           

on <xsl:value-of select="date"/>
                       </p>
                        
                        </xsl:template>
                        
                         <xsl:template name="printTags">
        <xsl:param name="tags"/>
        <xsl:choose>
            <xsl:when test="contains($tags,' ')"> 
                <xsl:variable name="tag" select="substring-before($tags,' ')"/>
                <a href="http://list.blogug.ch/tag/{$tag}"><xsl:value-of select="$tag"/></a>, 
                <xsl:call-template name="printTags">
                    <xsl:with-param name="tags" select="substring-after($tags,' ')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <a href="http://list.blogug.ch/tag/{$tags}"><xsl:value-of select="$tags"/></a>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
