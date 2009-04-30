<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="iso-8859-1" indent="no"
		doctype-public="-//W3C//DTD HTML 4.0 Transitional//EN"/>


<xsl:template match="*">
    <xsl:copy>
        <xsl:for-each select="@*">
            <xsl:copy/>
        </xsl:for-each>
        <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>

</xsl:stylesheet>
