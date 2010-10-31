<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:param name="startEntry" select="0"/>  
    
    <xsl:template name="htmlhead">
        <head>
            <xsl:call-template name="htmlheadcommon"/>
        </head>
    </xsl:template>

    <xsl:template name="htmlheadcommon">
        <title><xsl:call-template name="htmlheadtitle"/></title>
        <link rel="icon" href="/themes/planet-pear/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="/themes/planet-pear/favicon.ico" type="image/x-icon" /> 
        <link href="/themes/planet-pear/css/style.css" rel="stylesheet" type="text/css" />

        <xsl:if test="$startEntry > 0">
            <meta name="robots" content="noindex,follow"/> 
        </xsl:if>
        <link rel="alternate" type="application/rss+xml" title="RSS" href="http://feeds.feedburner.com/PLANETPEAR" />
        <link rel="alternate" type="application/atom+xml" title="Atom" href="http://feeds.feedburner.com/PLANETPEAR-ATOM" />
    </xsl:template>
    <xsl:template name="bodyhead">

        <div id="head">
	    <xsl:call-template name="bodylogo"/>

            <div id="topnavi">
                All news in one place
            </div>
        </div>

    </xsl:template>

    <xsl:template name="bodyfooter">

        <div id="footer">
            <div id="edit">
                <a href="/midcom-admin/" class="breadcrumb">Login</a>
                <br />
                <br />
            </div>
            &#169; 2001-2004 OSCOM.<br/>
            <a href="/" class="breadcrumb">OSCOM</a> &gt; Planet OSCOM
        </div>

    </xsl:template>

    <xsl:template name="bodylogo">
        <a href="/">
            <img src="/themes/planet-pear/img/pear-planet.png" width="305" height="70" hspace="30" alt="Planet PEAR" title="Planet PEAR" border="0"/>
        </a>
    </xsl:template>    

    <xsl:template name="commonRightBoxes">
        <div class="buttons">
            <fieldset>
                <legend>Link the Planet</legend>
                <code>
<![CDATA[
<a href="http://www.planet-pear.org/">Planet PEAR</a>
]]>
                </code>
            </fieldset>
        </div>
        <div class="menu">

            <fieldset>
                <legend>Contact</legend>
                <a class="blogLinkPad" href="http://twitter.com/klimpong">@klimpong</a>
            </fieldset>

        </div>

        <div class="menu">

            <fieldset>
                <legend>FAQ and Code</legend>
                <a class="blogLinkPad" href="http://blog.liip.ch/archive/2005/05/26/planet-php-faq.html">Planet PHP FAQ</a>
                <a class="blogLinkPad" href="/submit/">Add your PHP blog</a>
                <a class="blogLinkPad" href="http://github.com/till/planet-php/">Code on GitHub</a>
            </fieldset>

        </div>
        <div class="menu">

            <fieldset>
                <legend>Sponsors</legend>
                <div class="nnbe">
                    Hosted by <a class="inlineBlogLink" href="http://till.klampaeckel.de">till</a><br />
                    Logo designed by <a class="inlineBlogLink" href="http://viebrock.ca/">Colin Viebrock</a>.
                </div>
            </fieldset>

        </div>

    </xsl:template>
    <xsl:template match="object" mode="xhtml">
    </xsl:template>

</xsl:stylesheet>
