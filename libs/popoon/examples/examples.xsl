<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
    
    <xsl:template match="/examples">
        
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>Popoon Examples <!-- PHP Conference 2002 --></title>
            </head>
            <body>
			<h1>Popoon Examples <!-- - PHP Conference 2002--></h1>
			<table>
                <xsl:for-each select="example">
				<tr>
                    <xsl:variable name="phpfile">
                        <xsl:choose>
                            <xsl:when test="@link"><xsl:value-of select="@link"/></xsl:when>							
                            <xsl:otherwise>examples.php?sitemap=<xsl:value-of select="@sitemap"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:variable>
                  <td>  <a href="{$phpfile}">Example <xsl:value-of select="position()"/></a>: <xsl:value-of select="text"/>
                    </td>
					<td>

					<a href="{substring-before(@sitemap,'.xml')}.xml"><xsl:value-of select="substring-before(@sitemap,'.xml')"/>.xml</a>
					</td>
					<td>
					<xsl:if test="additional/file">
						<xsl:for-each select="additional/file">
							<a href="{.}"><xsl:value-of select="."/></a>
							<xsl:text> </xsl:text>
						</xsl:for-each>
					</xsl:if>
					</td>
					</tr>
                </xsl:for-each>
				</table>
All examples: <a href="./examples.tgz">examples.tgz</a>

            </body>                
        </html>
    </xsl:template>
    
</xsl:stylesheet>
