<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
        xmlns:php="http://php.net/xsl"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:import href="common.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="error" />
    <xsl:template match="/">

        <html>

            <xsl:call-template name="htmlhead"/>
            <body>
                <xsl:call-template name="bodyhead"/>

                <xsl:call-template name="middlecol"/>


            </body>
        </html>
    </xsl:template>


    <xsl:template name="middlecol">
        <div id="middlecontent">
            <xsl:for-each select="/results/entry">
                <div class="box" id="submit">
                    <fieldset>
                        <legend>
                            <xsl:value-of select="name"/>
                        </legend>
                        <form action="." method="POST" id="submit">
                            <label for="name">Your name: </label>
                            <input id="name" value="{name}" name="name"/>
                            <br/>
                            <label for="name">Your email: </label>
                            <input id="email" value="{email}" name="email"/>
                            <br/>

                            <label for="url">Your Blog URL: </label>
                            <input id="url" value="{url}" name="url"/>
                            <xsl:text> </xsl:text>
                            <a href="{url}">Link</a><br/>
                            <label for="rss">Your RSS/Atom URL:</label>
                            <input id="rss" value="{rss}" name="rss"/>
                            <xsl:text> </xsl:text>
                              <a href="{rss}">Link</a><xsl:text> </xsl:text> 
                              <a href="http://validator.w3.org/feed/check.cgi?url={rss}">Validate</a>
                              <br/>
Why should your blog be on Planet PHP:<br/>
                            <textarea name="description" id="description" cols="40" rows="10">
                                <xsl:value-of select="description"/>
                            </textarea>
                            <br/>
                            <br/>
                            <input type="submit" name="accept" value="update only"/>
                            <br/>
                            
                            <input type="submit" onclick="return confirm('Are you sure to ACCEPT this blog ({url})');" name="accept" value="accept"/>
                            <br/>
                            <input type="button" onclick="openReject({id})" name="reject" value="reject"/>
                            <br/>
                            <input style="display: none" type="hidden" name="id" value="{id}"/>
                            <div id="reject{id}" style="display: none">
Please write a rejectreason (gets mailed to the blogowner): <br/>

                                <textarea name="rejectreason" cols="40" rows="10"></textarea><br/>
                                <input type="submit" onclick="return confirm('Are you sure to REJECT this blog ({url})');" name="reallyreject" value="reallyreject"/>
                            </div>
                        </form>

                    </fieldset>
                </div>
        

            </xsl:for-each>
        </div>
    </xsl:template>


    <xsl:template name="htmlheadtitle">
    Planet PHP  - <xsl:value-of select="/html/head/title"/>
    </xsl:template>

    <xsl:template match="*" mode="xhtml">
        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:apply-templates mode="xhtml"/>
        </xsl:element>
    </xsl:template>



    <xsl:template match="@*" mode="xhtml">
        <xsl:copy-of select="."/>
    </xsl:template>

    <xsl:template name="htmlhead">
        <head>
            <xsl:call-template name="htmlheadcommon"/>
<script type="text/javascript">
function openReject(id) {
var rej = document.getElementById("reject" + id);
if (rej.style.display != 'block') {
    rej.style.display = 'block';
    } else {
    rej.style.display = 'none';
    }
}
</script>
        </head>

    </xsl:template>

</xsl:stylesheet>
