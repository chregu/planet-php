<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>
<xsl:param name="username" select="'ll'"/>

<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="generator"
content="HTML Tidy for Linux/x86 (vers 1st March 2002), see www.w3.org" />
<title></title>
</head>
<body>
<h2>
Hello <xsl:value-of select="$username"/>
</h2>

</body>
</html>

    </xsl:template>
    
</xsl:stylesheet>
