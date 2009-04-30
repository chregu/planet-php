<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:map="http://apache.org/cocoon/sitemap/1.0">
    <xsl:output indent="yes"/>

    <xsl:template match="map:include">
        <xsl:variable name="thisNode" select="."/>
        <xsl:for-each select="/map:sitemap/map:include-definitions/map:include-definition[@label = current()/@label]">
            <xsl:apply-templates>
                <xsl:with-param name="children" select="$thisNode/*"/>
            </xsl:apply-templates>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="map:children">
        <xsl:param name="children"/>
        <xsl:choose>
            <xsl:when test="current()/@label">
                <xsl:for-each select="$children[@label = current()/@label]">
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="$children[not(@label)]">
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>

    <xsl:template match="*">
        <xsl:param name="children"/>
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates>
                <xsl:with-param name="children" select="$children"/>
            </xsl:apply-templates>
        </xsl:copy>

    </xsl:template>

    <xsl:template match="map:include-definitions"></xsl:template>
    
    <!-- handle error part -->

    <xsl:template match="map:pipeline">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
            <xsl:call-template name="standardHandleError"/>
        </xsl:copy>


    </xsl:template>
    <!-- handle-errors without map:select -->
    <xsl:template match="map:pipeline/map:handle-errors[map:generate]">
        <map:handle-errors>
            <map:select type="exception">
                <map:otherwise>

                    <xsl:copy-of select="*"/>
                </map:otherwise>
            </map:select>
        </map:handle-errors>
    </xsl:template>


    <xsl:template name="standardHandleError">
        <map:handle-errors>

            <xsl:choose>
                <xsl:when test="not(/map:sitemap/map:pipelines/map:handle-errors)">
                    <map:select type="exception">
                        <map:otherwise>
                            <xsl:call-template name="standardHandleErrorPipeline"/>
                        </map:otherwise>
                    </map:select>
                </xsl:when>

                <xsl:otherwise>
                    <xsl:choose>
                        <xsl:when test="/map:sitemap/map:pipelines/map:handle-errors/map:select[@type = 'exception'] and not(/map:sitemap/map:pipelines/map:handle-errors/map:select/map:otherwise)">
                            <map:select type="exception">
                                <xsl:for-each select="/map:sitemap/map:pipelines/map:handle-errors/map:select[@type = 'exception']/*">
                                    <xsl:copy-of select="."/>
                                </xsl:for-each>

                                <map:otherwise>
                                    <xsl:call-template name="standardHandleErrorPipeline"/>
                                </map:otherwise>
                            </map:select>
                        

                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:copy-of select="/map:sitemap/map:pipelines/map:handle-errors/*"/>
                        </xsl:otherwise>

                    </xsl:choose>
                </xsl:otherwise>
            </xsl:choose>

        </map:handle-errors>
    </xsl:template>


    <xsl:template name="standardHandleErrorPipeline">
        <map:generate type="error">
            <map:parameter name="exception"/>
        </map:generate>
        <map:transform type="xslt" src="constant(BX_POPOON_DIR)/xsl/error2html.xsl"/>
        <map:serialize type="xhtml">
           <map:parameter type="header" name="HTTP" value="503 Service Temporarily Unavailable"/>
          </map:serialize>

    </xsl:template>



</xsl:stylesheet>
