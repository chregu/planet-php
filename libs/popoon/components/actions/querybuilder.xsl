<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:xslt="lala">
<xsl:output method="xml" encoding="iso-8859-1" />
<xsl:namespace-alias stylesheet-prefix="xslt" result-prefix="xsl"/>
<xsl:variable name="vars" select="/builder/query"/>

<xsl:template match="/">
	<xslt:stylesheet version="1.0" >
	<xslt:output method="text" encoding="iso-8859-1"/>
		<xsl:apply-templates select="/builder/query"/>
		<xslt:template match="/">
			<xsl:apply-templates select="/builder/*[not (name() = 'query')] | /builder/text()"/>	
		</xslt:template>
	</xslt:stylesheet>



</xsl:template>

<xsl:template match="/builder/query">
	<xsl:for-each select="*">
	<xslt:variable name="{name()}" select="'{.}'"/>
	</xsl:for-each>
</xsl:template>

<xsl:template match="if[@exists]">
	<xsl:if test="/builder/query/*[name() = current()/@exists]">
		<xsl:choose>
			<xsl:when test="@test">
				<xslt:if test="{@test}">
					<xsl:apply-templates/>
				</xslt:if>
			</xsl:when>
			<xsl:otherwise>
				<xslt:if test="${@exists}">
					<xsl:apply-templates/>
				</xslt:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
</xsl:template>

<xsl:template match="value-of">
	<xslt:value-of select="{@select}"/>
</xsl:template>

<xsl:template match="if">
		<xslt:if test="{@test}">
			<xsl:apply-templates/>
		</xslt:if>
</xsl:template>


<xsl:template match="choose">
	<xslt:choose>
		<xsl:apply-templates/>
	</xslt:choose>
	
</xsl:template>


<xsl:template match="choose/when">
	<xslt:when test="{@test}">
		<xsl:apply-templates/>
	</xslt:when>
</xsl:template>

<xsl:template match="choose/otherwise">
	<xslt:otherwise>
		<xsl:apply-templates/>
	</xslt:otherwise>
</xsl:template>



<xsl:template match="text()">
<xsl:value-of select="."/>

</xsl:template> 

<xsl:template match="*">
<xsl:text>
</xsl:text>	<xsl:value-of select="name()"/> not allowed/implemented here
<xsl:value-of select="normalize-space(.)"/><xsl:text> </xsl:text>

</xsl:template>

<xsl:template match="/builder">
	<xsl:copy>
        <xsl:for-each select="@*">
            <xsl:copy/>
        </xsl:for-each>
        <xsl:apply-templates/>
    </xsl:copy>
</xsl:template >	
</xsl:stylesheet>
