<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"

xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:syn="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/"
xmlns:php="http://php.net/xsl"


><xsl:import href="main-new.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    
  

    <xsl:template match="entries[@section='default']/entry">
    <xsl:if test="position() = 1 or substring-before(dc_date,', ') != substring-before(preceding-sibling::*[position() = 1]/dc_date,', ')">
    <h5 class="date"><xsl:value-of select="substring-before(dc_date,', ')"/></h5>
    
    </xsl:if>
       
        <div >
        <xsl:choose>
        <xsl:attribute name="class">
        <xsl:when test="position() mod 2 = 1">box listView</xsl:when>
        <xsl:otherwise>box listView od</xsl:otherwise>
        </xsl:choose>
        <div class="col1">
        <xsl:value-of select="substring-after(dc_date,', ')"/>
        </div>
        <div class="col2">
        
               <a class="listText" id="text{id}" onclick="moreInfoStart('{id}'); return false;" href="#{id}">+</a>
        
        
               <a class="listMore"  id="more{id}" onclick="moreInfoStart('{blog_id}','{id}'); return false;" href="#{blog_id}_{id}">i</a>
                    
                <a href="{link}" ><xsl:value-of select="title"/></a>
        
        </div>
        <div class="col3">
        <a  href="{blog_link}"><xsl:value-of select="blog_title"/> </a>
        
         </div>
          
         <div style="display: none" class="moreDiv" id="morediv{id}"></div>
         

            
        </div>
    </xsl:template>
    
   <xsl:template name="middlecol">
        <div id="middlecontent">
            <xsl:apply-templates select="/planet/entries[@section='default']/entry"/>
            <xsl:variable name="nextEntries">
                <xsl:choose>
                    <xsl:when test="(/planet/search/count - (/planet/search/start + 100)) &gt;= 100">100</xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="(/planet/search/count - (/planet/search/start + 100))"/>
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
                                    <a href="{$webroot}{substring-after($searchString,'/')}?start={$startEntry + 100}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
                                </xsl:when>
                                 <xsl:otherwise>
                                    <a href="{$webroot}?start={$startEntry + 100}">Next <xsl:value-of select="$nextEntries"/> Older Entries</a>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:if>
                   

                    </span>
                    <span style="float: left;">
                        <xsl:choose>
                            <xsl:when test="$startEntry = 0 and $nextEntries &lt;= 0">
                             No More Entries
                             </xsl:when>
                            <xsl:when test="$startEntry &gt;= 100">
                                <xsl:choose>
                                    <xsl:when test="$searchString">
                                        <a href="{$webroot}{substring-after($searchString,'/')}?start={$startEntry - 100}">Previous 100 Newer Entries</a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <a href="{$webroot}?start={$startEntry - 100}">Previous 100 Newer Entries</a>
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

</xsl:stylesheet>
