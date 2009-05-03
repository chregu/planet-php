<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
<xsl:import href="../../inc/options.xsl"/>
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:param name="webroot"/>
     <xsl:template name="htmlhead">

        <head>

       <meta name="robots" content="noindex, nofollow"/>
            <title><xsl:call-template name="htmlheadtitle"/></title>
            <link rel="shortcut icon" href="/favicon.ico" />
            <link href="/themes/css/style.css" rel="stylesheet" type="text/css"/>
          <script language="JavaScript" src="/js/search.js" ><xsl:text> </xsl:text> </script>
<script language="JavaScript" src="/js/moreinfo.js" ><xsl:text> </xsl:text> </script>
<script language="JavaScript" src="/themes/planet-whatever/js/tooltip.js" ><xsl:text> </xsl:text> </script>

<script language="JavaScript">

var webroot = '<xsl:value-of select="$webroot"/>';
</script>
<meta name="robots" content="noindex,nofollow"/> 
<link rel="alternate" type="application/rss+xml" title="RSS" href="{$channelLink}rss/" />
<link rel="alternate" type="application/rdf+xml" title="RDF" href="{$channelLink}rdf/" />
<link rel="alternate" type="application/x.atom+xml" title="Atom" href="{$channelLink}atom/" />

<link rel="search" href="http://planet.blogug.ch/osd.xml" type="application/opensearchdescription+xml" title="Planet Switzerland" />
        </head>
    </xsl:template>

    <xsl:template name="bodyhead">

        <div id="head">
	   <xsl:call-template name="bodylogo"/>

            <div id="topnavi">
            Switzerland in Blogs - More Design Updates pretty soon
<span>  <a href="http://list.blogug.ch/">list</a> | <a href="http://ping.blogug.ch/">ping</a> | <a href="http://top100.blogug.ch">top100</a></span>
            </div>
        </div>
    </xsl:template>

    <xsl:template name="bodyfooter">

        <div id="footer">
            <div id="edit">
                <a href="/midcom-admin/" class="breadcrumb">Login</a>
                <br/>
                <br/>
<!-- <a href="http://www.midgard-project.org">Midgard CMS</a> -->
            </div>
&#169; 2001-2004 OSCOM.<br/>
            <a href="/" class="breadcrumb">OSCOM</a> &gt; Planet OSCOM</div>


    </xsl:template>
<xsl:template name="bodylogo">
            <h1><a href="/">
Planet Switzerland - beta2.0 - design 0.2 :)
           <!--     <img src="./themes/img/php-planet.png" width="275" height="70" hspace="30" alt="Planet Ping" title="Planet Ping" border="0"/>-->
            </a></h1>
</xsl:template>    

<xsl:template name="commonRightBoxes">

<!--
        <div class="buttons">
            <fieldset>
                <legend>Buttons</legend>
                <img width="80" height="15" src="/themes/img/planet-php-button-1.jpg" alt="Planet Ping"/>
        &#160;
<img width="80" height="15" src="/themes/img/planet-php-button-2.jpg" alt="Planet Ping"/>
                <br/>
                <img width="80" height="15" src="/themes/img/planet-php-button-3.jpg" alt="Planet Ping"/>
            </fieldset>
        </div>
        <div class="menu">

            <fieldset>
                <legend>Contact</legend>
                <a class="blogLinkPad" href="mailto:we@planet-php.net">we@planet-php.net</a>
            </fieldset>
        </div>

        <div class="menu">

            <fieldset>
                <legend>FAQ and Code</legend>
<a class="blogLinkPad" href="http://blog.bitflux.ch/archive/2005/05/26/planet-php-faq.html">Planet Ping FAQ</a>
                <a class="blogLinkPad" href="http://svn.bitflux.ch/repos/public/planet-php/trunk/">Subversion Repository</a>
            </fieldset>

        </div>
-->        <div class="menu">

            <fieldset>
                <legend>Sponsors</legend>
               <div class="nnbe">Hosted by <a class="inlineBlogLink" href="http://www.netzwirt.ch">netzwirt.ch</a> and <a class="inlineBlogLink" href="http://www.bitflux.ch">Bitflux</a>. <br/>
<!--Logo designed by <a class="inlineBlogLink" href="http://viebrock.ca/">Colin Viebrock</a>.-->
</div>
            </fieldset>

        </div>

</xsl:template>

  </xsl:stylesheet>
