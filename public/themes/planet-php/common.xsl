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
            <link rel="shortcut icon" href="/favicon.ico" />
            <link href="/themes/css/style.css" rel="stylesheet" type="text/css"/>

<xsl:if test="$startEntry > 0">
<meta name="robots" content="noindex,follow"/> 
</xsl:if>
<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.planet-php.org/rss/" />
<link rel="alternate" type="application/rdf+xml" title="RDF" href="http://www.planet-php.org/rdf/" />
<link rel="alternate" type="application/atom+xml" title="Atom" href="http://www.planet-php.org/atom/" />
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
                <br/>
                <br/>
<!-- <a href="http://www.midgard-project.org">Midgard CMS</a> -->
            </div>
&#169; 2001-2004 OSCOM.<br/>
            <a href="/" class="breadcrumb">OSCOM</a> &gt; Planet OSCOM</div>


    </xsl:template>
<xsl:template name="bodylogo">
            <a href="/">
                <img src="/themes/img/php-planet.png" width="275" height="70" hspace="30" alt="Planet PHP" title="Planet PHP" border="0"/>
<!--                <img src="http://www.liip.ch:2001/php-planet.png" width="275" height="70" hspace="30" alt="Planet PHP" title="Planet PHP" border="0"/>-->
            </a>
</xsl:template>    

<xsl:template name="commonRightBoxes">

      

        <div class="menu">

            <fieldset>
                <legend>FAQ and Code</legend>
                
<a class="blogLinkPad" href="http://blog.liip.ch/archive/2005/05/26/planet-php-faq.html">Planet PHP FAQ</a>
<a class="blogLinkPad" href="/submit/">Add your PHP blog</a>
                <a class="blogLinkPad" href="http://github.com/chregu/planet-php/">Code on GitHub</a>
<!--                <a class="blogLinkPad" href="http://svn.bitflux.ch/repos/public/planet-php/trunk/">Subversion Repository</a>-->
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
                <legend>Sponsors</legend>
               <div class="nnbe">Hosted by <a class="inlineBlogLink" href="http://www.netzwirt.ch">netzwirt.ch</a> and <a class="inlineBlogLink" href="http://www.liip.ch">Liip</a>. <br/>
               Maintained by <a class="inlineBlogLink" href="http://chregu.tv">Chregu Stocker</a>, <a class="inlineBlogLink" href="http://schlitt.info/">Tobias Schlitt</a> and more.<br/>
               
Logo designed by <a class="inlineBlogLink" href="http://viebrock.ca/">Colin Viebrock</a>.
<br/>
Twitter account by <a class="inlineBlogLink" href="http://sepehr.ws/">Sepehr Lajevardi</a>
</div>
            </fieldset>

        </div>
          <div class="buttons">
            <fieldset>
                <legend>Buttons</legend>
                <img width="80" height="15" src="/themes/img/planet-php-button-1.jpg" alt="Planet PHP"/>
        &#160;
<img width="80" height="15" src="/themes/img/planet-php-button-2.jpg" alt="Planet PHP"/>
                <br/>
                <img width="80" height="15" src="/themes/img/planet-php-button-3.jpg" alt="Planet PHP"/>
            </fieldset>
        </div>

</xsl:template>
<xsl:template match="object" mode="xhtml"> </xsl:template>

  </xsl:stylesheet>
