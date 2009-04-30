<?xml version="1.0"?>
<xsl:stylesheet version="1.0"  xmlns:metal="http://xml.zope.org/namespaces/metal" xmlns:bxf="http://bitflux.org/functions" xmlns:tal="http://xml.zope.org/namespaces/tal" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xslout="whatever" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" extension-element-prefixes="func">

    <xsl:namespace-alias stylesheet-prefix="xslout" result-prefix="xsl"/>
    <func:function name="bxf:tales">
        <xsl:param name="path"/>
        <xsl:choose>
            <xsl:when test="$path = ''">
                <func:result select="'node()'"/>
            </xsl:when>
            <xsl:otherwise>
                <func:result select="$path"/>
            </xsl:otherwise>
        </xsl:choose>
    </func:function>

    <xsl:template match="/">
        <xslout:stylesheet version="1.0" exclude-result-prefixes="xhtml bxf tal">
            <xslout:output encoding="utf-8" method="xml" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
            <xsl:apply-templates select="//*[@tal:include]" mode="init"/>
            <xsl:apply-templates select="//*[@tal:match]" mode="init"/>
            <xsl:apply-templates select="//*[@metal:use-macro]" mode="init"/>
            <xslout:template match="/">

                <xsl:apply-templates/>
            </xslout:template>
            <!--copy all elements -->
            <xslout:template match="*">
                <xslout:copy>
                    <xslout:apply-templates select="@*"/>
                    <xslout:apply-templates/>
                </xslout:copy>
            </xslout:template>
            
            <!-- copy all attributes -->
            <xslout:template match="@*">
                <xslout:copy-of select="."/>
            </xslout:template>
        </xslout:stylesheet>

    </xsl:template>

    <xsl:template match="*[@tal:condition]" priority="10">
        <xslout:if test="{bxf:tales(@tal:condition)}">
            <xsl:apply-templates/>
        </xslout:if>
    </xsl:template>

    <xsl:template match="*[@metal:use-macro]">
        <xsl:variable name="doc" select="substring-before(@metal:use-macro,'#')"/>
        <xsl:variable name="path" select="substring-after(@metal:use-macro,'#')"/>
        <xsl:apply-templates select="document($doc)//*[@metal:define-macro = $path]"/>
     </xsl:template>
     
     <xsl:template match="*[@metal:use-macro]" mode="init">
        <xsl:variable name="doc" select="substring-before(@metal:use-macro,'#')"/>
        <xsl:variable name="path" select="substring-after(@metal:use-macro,'#')"/>
        <xsl:apply-templates select="document($doc)//*[@metal:define-macro = $path]" mode="init"/>
     </xsl:template>
     
     <xsl:template match="text()" mode ="init">
        <xsl:if test="ancestor::*[@tal:match]">
            <xsl:copy/>
        </xsl:if>
     </xsl:template>
     
     
    
    <xsl:template match="@metal:define-macro">
    </xsl:template>
    <xsl:template match="*[@tal:content]">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:call-template name="copy-value-apply">
                <xsl:with-param name="path" select="@tal:content"/>
            </xsl:call-template>
        </xsl:copy>
    </xsl:template>


    <xsl:template match="*[@tal:replace]">
        <xsl:call-template name="copy-value-apply">
            <xsl:with-param name="path" select="@tal:replace"/>
        </xsl:call-template>
    </xsl:template>


    <xsl:template match="*[@tal:repeat]">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:variable name="v" select="substring-before(@tal:repeat,' ')"/>
            <xsl:variable name="x" select="substring-after(@tal:repeat,' ')"/>
            <xslout:for-each select="{bxf:tales($x)}">
                <xslout:variable name="{$v}" select="."/>
                <xsl:apply-templates/>
            </xslout:for-each>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:if test="namespace-uri() != 'http://xml.zope.org/namespaces/tal'">
            <xsl:copy-of select="."/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="*[@tal:match]"/>

    <xsl:template match="*[@tal:include]" mode="init">
        <xsl:call-template name="talIncludes">
            <xsl:with-param name="include" select="@tal:include"/>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="talIncludes">
        <xsl:param name="include"/>
        <xsl:choose>
            <xsl:when test="contains($include,' ')">
                <xslout:include href="{substring-before($include,' ')}"/>
                <xsl:call-template name="talIncludes">
                    <xsl:with-param name="include" select="substring-after($include,' ')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xslout:include href="{$include}"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="*[@tal:match]" mode="init">
        <xslout:template match="{@tal:match}">
            <xsl:apply-templates/>
        </xslout:template>
    </xsl:template>
    

      <!-- outputs value-of, copy-of our apply-templates of $path depending on the first param
              "/foo/bar" ->             <xsl:value-of select="/foo/bar"/> 
              "text /foo/bar" ->        <xsl:value-of select="/foo/bar"/>
              "structure /foo/bar" ->   <xsl:copy-of select="/foo/bar"/>
         -->
    <xsl:template name="copy-value-apply">
        <xsl:param name="path"/>
        <xsl:variable name="mode">
            <xsl:value-of select="substring-before($path,' ')"/>
        </xsl:variable>
        <xsl:variable name="spath">
            <xsl:value-of select="substring-after($path,' ')"/>
        </xsl:variable>

        <xsl:choose>
           <!-- if no mode, use value-of -->
           <xsl:when test="$path ='structure'">
                <xslout:apply-templates select="{bxf:tales('')}"/>
           </xsl:when>
            <xsl:when test="$mode = ''">
                <xslout:value-of select="{bxf:tales($path)}"/>
            </xsl:when>
            <xsl:when test="$mode = 'text'">
                <xslout:value-of select="{bxf:tales($spath)}"/>
            </xsl:when>
            <xsl:when test="$path = 'structure .'">
                <xslout:copy>
                    <xslout:apply-templates select="@*"/>
                    <xslout:apply-templates select="{bxf:tales('')}"/>
                </xslout:copy>
            </xsl:when>
            <xsl:when test="$mode = 'structure'">
                <xslout:apply-templates select="{bxf:tales($spath)}"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="@tal:attributes">
        <xsl:call-template name="talAttribute">
            <xsl:with-param name="attr" select="."/>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="talAttribute">
        <xsl:param name="attr"/>
        <xsl:choose>
            <xsl:when test="contains($attr,'; ')">

                <xsl:call-template name="talAttribute">
                    <xsl:with-param name="attr" select="substring-after($attr,'; ')"/>
                </xsl:call-template>
                <xsl:call-template name="outputTalAttribute">
                    <xsl:with-param name="attr" select="substring-before($attr,'; ')"/>
                </xsl:call-template>
            </xsl:when>

            <xsl:otherwise>
                <xsl:call-template name="outputTalAttribute">
                    <xsl:with-param name="attr" select="$attr"/>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="outputTalAttribute">
        <xsl:param name="attr"/>
        <xsl:variable name="name" select="substring-before($attr,' ')"/>
        <xsl:variable name="value" select="substring-after($attr,' ')"/>
        <xslout:attribute name="{$name}">
            <xslout:value-of select="{bxf:tales($value)}"/>
        </xslout:attribute>

    </xsl:template>

    <xsl:template match="*">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
</xsl:stylesheet>