
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:bxco="http://bitflux.org/config/1.0">
    <xsl:output method="html" encoding="iso-8859-1" indent="no"/>
    <xsl:param name="screenid" select="0"/>
    <xsl:param name="lang" select="'fr'"/>
    <xsl:variable name="realid">
    <xsl:choose>
    <xsl:when test="$screenid = 0"><xsl:value-of select="/bxco:wizard/bxco:screen/@id" /></xsl:when>
    <xsl:otherwise><xsl:value-of select="$screenid"/></xsl:otherwise>
    </xsl:choose>
    
</xsl:variable>
    
    <xsl:template match="/">
    <div class="wizard">
        <xsl:for-each select="/bxco:wizard/bxco:screen[@id = $realid]">
            <form name="bx_foo" action="" method="POST">
            <input type="hidden" name="nextPage" value="{bxco:nextScreen}"/>
             <input type="hidden" name="thisPage" value="{$realid}"/>
                <xsl:apply-templates/>
                <xsl:if test="bxco:submit">
                    <input type="submit">
                        <xsl:attribute name="value">
                            <xsl:call-template name="lookup" >  
                                <xsl:with-param name="ID" select="bxco:submit/@name"/>
                            
                            </xsl:call-template>
                        </xsl:attribute>
                    </input>
                </xsl:if>

            </form>

        </xsl:for-each>
</div>

<xsl:value-of select="$realid"/>
    </xsl:template>

    <xsl:template match="bxco:section">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="bxco:prevScreen">
    </xsl:template>
    
<xsl:template match="bxco:nextScreen">
    </xsl:template>
    
    <xsl:template match="bxco:group">
        <div class="wizardGroup">
            <div class="wizardGroupTitle">
                <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@name"/>
                </xsl:call-template>
            </div>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="bxco:fields">
        <xsl:apply-templates/>
    </xsl:template>
    

    <xsl:template match="bxco:section/bxco:teaser">
        <div class="wizardTeaser">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
        </div>
    </xsl:template>

    <xsl:template match="bxco:fields//bxco:field[@type='text']">
        <p>  <xsl:if test="@error">
        <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID">
                    <xsl:value-of select="@name"/>
                </xsl:with-param>
            </xsl:call-template>
            <input type="text" name="bx_fw[{@name}]" value="{@value}"/>
        </p>
    </xsl:template>

     <xsl:template match="bxco:fields//bxco:field[@type='longtext']">
        <p>  <xsl:if test="@error">
        <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID">
                    <xsl:value-of select="@name"/>
                </xsl:with-param>
            </xsl:call-template><br/>
            <textarea name="bx_fw[{@name}]" cols="40" rows="8"><xsl:value-of select="@value" /></textarea>
        </p>
    </xsl:template>
    
    
    
    <xsl:template match="bxco:fields//bxco:field[@type='checkbox']">
        <p>
            <input type="checkbox" name="bx_fw[{@name}]" value="1">
            <xsl:if test="@value=1">
            <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            </input>
            
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
            
            <if test="@subtitle">
               <div class="wizardKlein">
                    <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@subtitle"/>
                </xsl:call-template>
                </div>
            </if>
            <xsl:if test="@error">
            <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
        </p>
    </xsl:template>
    
     <xsl:template match="bxco:fields//bxco:field[@type='checkboxtext']">
        <p>
            <input type="checkbox" name="bx_fw[{@name}]" value="1">
            <xsl:if test="@value=1">
            <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            </input>
            
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>&#160;
            <input type="text" name="bx_fw[{@name}_text]" />
             <if test="@subtitle">
               <div class="wizardKlein">
                    <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@subtitle"/>
                </xsl:call-template>
                </div>
            </if>
            
            <xsl:if test="@error">
            <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
        </p>
    </xsl:template>
    
    
    
    <xsl:template match="bxco:field[@type='msg']">
        <p>
           
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template match="bxco:fields//bxco:field[@type='radio']">
        <div class="wizardGroup">
          <xsl:if test="@error">
           
            </xsl:if>
            <div class="wizardGroupTitle">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
            </div>
            
            <div class="wizardError">
            <xsl:call-template name="lookup">
               <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            <xsl:for-each select="bxco:option">

                <input type="radio" name="bx_fw[{../@name}]" value="{@value}">
                <xsl:if test="../@value = @value">
                <xsl:attribute name="checked">checked</xsl:attribute>
                </xsl:if>
                </input>
                
                <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@name"/>
                </xsl:call-template>
                <xsl:if test="@type = 'text'">
                    <input type="text" name="bx_fw[{@name}_text]" /> 
                </xsl:if>
                
                <xsl:if test="../@orientation != 'horizontal'">
                    <br/>
                </xsl:if>
                <xsl:if test="@subtitle">
                <div class="wizardKlein">
                    <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@subtitle"/>
                </xsl:call-template>
                </div>
                </xsl:if>
                
                
            </xsl:for-each>
        </div>
    </xsl:template>
    

    <xsl:template name="lookup">
        <xsl:param name="ID"/>
        <xsl:choose>
        <xsl:when test="/bxco:wizard/bxco:lang/bxco:entry[@ID=$ID]/bxco:text[@lang = $lang]">
        <xsl:value-of select="/bxco:wizard/bxco:lang/bxco:entry[@ID=$ID]/bxco:text[@lang = $lang]" disable-output-escaping="yes"/>
        </xsl:when>
        <xsl:otherwise>
        <xsl:value-of select="/bxco:wizard/bxco:lang/bxco:entry[@ID=$ID]/bxco:text[@lang = 'de']" disable-output-escaping="yes"/>
        
        </xsl:otherwise>
        </xsl:choose>
      </xsl:template>
      


</xsl:stylesheet>

