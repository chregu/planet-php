
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:bxco="http://bitflux.org/config/1.0">
    <xsl:output method="text" encoding="iso-8859-1" indent="no"/>
    

    <xsl:template match="/">
    
        create table <xsl:value-of select="/bxco:wizard/bxco:screen/@table"/> (
        `ID` int(11) NOT NULL auto_increment,
        <xsl:text>
        </xsl:text>
        
        <xsl:for-each select="/bxco:wizard/bxco:screen//bxco:field[@type != 'msg']">
        
        <xsl:apply-templates select="."/>
        ,
        </xsl:for-each>
        
PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
    </xsl:template>

    <xsl:template match="bxco:field[@type='msg']"></xsl:template>

    <xsl:template match="bxco:field">
        <xsl:value-of select="@name"/>
        <xsl:text>
    </xsl:text>
    </xsl:template>
     <xsl:template match="bxco:field[@type='session']">
        <xsl:value-of select="@name"/> varchar(250) default NULL
   </xsl:template> 

    <xsl:template match="bxco:field[@type='text']">
        <xsl:value-of select="@name"/> varchar(250) default NULL
   </xsl:template>
   
   
    <xsl:template match="bxco:field[@type='longtext']">
        <xsl:value-of select="@name"/> text default NULL
   </xsl:template>
    
    
   <xsl:template match="bxco:field[@type='checkbox']">
        <xsl:value-of select="@name"/> tinyint default NULL
   </xsl:template>
   
   <xsl:template match="bxco:field[@type='checkboxtext']">
        <xsl:value-of select="@name"/> tinyint default NULL,
         <xsl:value-of select="@name"/>_text varchar(250) default NULL
   </xsl:template>
   
    <xsl:template match="bxco:field[@type='radio']">
        <xsl:value-of select="@name"/> enum (<xsl:for-each select="bxco:option">'<xsl:value-of select="@value"/>' 
            <xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>)  NULL,
        <xsl:for-each select="bxco:option">
        <xsl:if test="@type = 'text'">
             <xsl:value-of select="@name"/>_text varchar(250) default NULL
             <xsl:if test="position() != last()">,</xsl:if>
        </xsl:if>
        </xsl:for-each>
        
        
   </xsl:template>
</xsl:stylesheet>
