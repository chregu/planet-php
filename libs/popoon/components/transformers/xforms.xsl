<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xforms="http://www.w3.org/2002/xforms"
    >
<xml:param name="projectDir"/>
    <xsl:variable name="langdefs" select="document(concat($projectDir,'/xml/langdefs.xml'))/root"/>
	<xsl:param name="lang" select="'de'"/>
    <xsl:param name="challenge" select="'none'"/>
    
    <xsl:param name="userID" select="'0'"/>
    <xsl:param name="uploaddir" select="'0'"/>
    <xsl:output method="html" encoding="iso-8859-1" indent="no" doctype-public="-//W3C//DTD HTML 4.0 Transitional//EN"/>
    <xsl:param name="tdclass" select="'graugross'"/>
    <xsl:param name="inputclass" select="'formsgross'"/>
    <xsl:param name="submitclass" select="'formssubmit'"/>

    <xsl:param name="inputsize" select="'40'"/>
    <xsl:param name="uploadinputsize" select="'30'"/>
    
    <xsl:param name="error" />   
    <xsl:param name="message" /> 	
    <xsl:param name="secondtd" ><![CDATA[<td><img src="/images/0.gif" /></td>]]></xsl:param>
    
    <xsl:template match="xforms:xform">
        <xsl:if test="string-length($error) > 0">
            <font color="red"><xsl:value-of disable-output-escaping="yes" select="$langdefs/lang/entry[@ID = $error]/text[@lang= $lang]"/></font>
        </xsl:if>
        <xsl:if test="string-length($message) > 0">
            <font color="red"><xsl:value-of disable-output-escaping="yes" select="$langdefs/lang/entry[@ID = $message]/text[@lang=$lang]"/></font>
        </xsl:if>
        <xsl:if test="not (xforms:extension/popoon/@hide) and not (xforms:extension/popoon/@hideOnChangedMessage and ($error = 'Ihre Daten wurden geaendert.' or $message ='Ihre Daten wurden geaendert.'))">
            <form name="{@id}" method="{xforms:submitInfo/@method}" action="{xforms:submitInfo/@action}" enctype="multipart/form-data">
                
		
    <!-- htmlarea stuff by mediagonal sa, see below for more !-->
    <!-- BEGIN !-->
		<xsl:if test="//xforms:textarea[@class='htmlarea']">
			<script language="Javascript1.2"><![CDATA[	
				// load htmlarea
				
				_editor_url = "/js/htmlarea/";                  // URL to htmlarea files
				var win_ie_ver = parseFloat(navigator.appVersion.split("MSIE")[1]);
				
				if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
				if (navigator.userAgent.indexOf('Linux')      >= 0) { win_ie_ver = 0; }
				if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
				if (navigator.userAgent.indexOf('Gecko')      >= 0) { win_ie_ver = 0; }
				if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
				
				
				if (win_ie_ver >= 5.5) {
					document.write('<scr' + 'ipt src="' +_editor_url+ 'editor.js"');
					document.write(' language="Javascript1.2"></scr' + 'ipt>');  
				} 
				else { 
					document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>'); 
				}
			]]></script>
		</xsl:if>
    <!-- END !-->

	
		<xsl:if test="//xforms:secret">
                    <xsl:attribute name="onsubmit" >return doChallengeResponse();</xsl:attribute>
                    <script language="javascript" src="/js/md5.js"></script>
                    <script language="javascript">
                        
                        function doChallengeResponse() {
                        str = MD5(document.<xsl:value-of select="@id"/>.popoon_password.value) + ":" +
                        document.<xsl:value-of select="@id"/>.challenge.value;
                        document.<xsl:value-of select="@id"/>.response.value = MD5(str);
                        document.<xsl:value-of select="@id"/>.popoon_password.value = "";
                        return true;
                        }
                        
		   </script>
		
                </xsl:if>
		
		
		
		
                <table>
                    <xsl:apply-templates select="..//xforms:*[@xform = current()/@id]" mode="xform"/>
                </table>
            </form>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="xforms:input" mode="xform">
        <tr><td class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}">
	    	<input size="{$inputsize}" class="{$inputclass}" type="text" name="{@ref}">		
		
		<!-- use default value if given and no other value present !--> 
		<xsl:choose>		
			<xsl:when test="xforms:value[not(@class) or @class != default]">
				<xsl:attribute name="value">
					<xsl:value-of select="xforms:value[not(@class) or @class != default]" />
				</xsl:attribute>
			</xsl:when>
			
			<xsl:otherwise>		
				<xsl:attribute name="value">
					<xsl:value-of select="xforms:value[@class='default']" />
				</xsl:attribute>		
			</xsl:otherwise>				
		</xsl:choose>
		</input>
	    </td>
	 </tr>
    </xsl:template>

    <xsl:template match="xforms:hidden" mode="xform">
		<input type="hidden" name="{@ref}" value="{xforms:value}"/>
    </xsl:template>
    
    <xsl:template match="xforms:select" mode="xform">
        <tr><td class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}">
                <input size="{$inputsize}"  type="checkbox" name="{@ref}" >
                    <xsl:if test="xforms:value=1">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
        </td></tr>
    </xsl:template>

  
    <!-- htmlarea stuff by mediagonal sa !-->
    <!-- BEGIN !-->
    <xsl:template name='assign-config'  mode="xform">
		<xsl:param name='$ref'/>
		<xsl:param name='$config'/>
		
		
		<xsl:for-each select="$config">
			<xsl:if test="@attribute">
				<xsl:value-of select='$ref'/>_config.<xsl:value-of select='@attribute'/> = 
				<xsl:if test="@type!='array'">
					<xsl:text>"</xsl:text>
				</xsl:if>	
				<xsl:value-of select='@value'/>
				<xsl:if test="@type!='array'">
					<xsl:text>"</xsl:text>
				</xsl:if>;	
			</xsl:if>
		</xsl:for-each>
		
	</xsl:template>  
	
    <xsl:template match="xforms:textarea[@class='htmlarea']" mode="xform">
        	
	<tr><td class="{$tdclass}" valign="top">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}">
	    
	       <textarea id="{@ref}" name="{@ref}">	       	

			<!-- use default value if given and no other value present !--> 
			<xsl:choose>		
				<xsl:when test="xforms:value[not(@class) or @class != default]">					
						<xsl:value-of select="xforms:value[not(@class) or @class != default]" />
				</xsl:when>
				
				<xsl:otherwise>	
					<xsl:value-of select="xforms:value[@class='default']" />							
				</xsl:otherwise>				
			</xsl:choose>
		</textarea>
	       
	       <script language="JavaScript1.2" defer="true"> 
	       	        var <xsl:value-of select="@ref"/>_config = new Object();

				<xsl:call-template name="assign-config">
					<xsl:with-param name="ref" select="@ref"/>
					<xsl:with-param name="config" select="xforms:extension/popoon"/>
				</xsl:call-template>   
	       	       
			editor_generate('<xsl:value-of select="@ref"/>', <xsl:value-of select="@ref"/>_config);
			
	      </script>
	
		
	
	     
	       
        </td></tr>
    </xsl:template>
    
    <!-- htmlarea stuff by mediagonal sa !-->
    <!-- END !-->
     
    <xsl:template match="xforms:textarea" mode="xform">
        <tr><td class="{$tdclass}" valign="top">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}">
                <textarea name="{@ref}" cols="{$inputsize}" rows="10">
		<!-- use default value if given and no other value present !--> 
			<xsl:choose>		
				<xsl:when test="xforms:value[not(@class) or @class != default]">					
						<xsl:value-of select="xforms:value[not(@class) or @class != default]" />
				</xsl:when>
				
				<xsl:otherwise>	
					<xsl:value-of select="xforms:value[@class='default']" />							
				</xsl:otherwise>				
			</xsl:choose>
		</textarea>
        </td></tr>
    </xsl:template>
    
    <xsl:template match="xforms:secret[@mode='challenge']" mode="xform">
        <tr><td class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}"><input size="{$inputsize}" class="{$inputclass}" type="password" name="popoon_password" />
                <input type="hidden" name="challenge" value="{$challenge}"/>
                <input type="hidden" name="response"/>			
        </td></tr>
    </xsl:template>
    
    <xsl:template match="xforms:secret[@mode='change_password']" mode="xform">
        
        <tr><td class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang and @position=1]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}"><input size="{$inputsize}" class="{$inputclass}" type="password" name="{@ref}" />
        </td></tr>
        <tr><td class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang and @position=2]"/></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td class="{$tdclass}"><input 
                size="{$inputsize}" class="{$inputclass}" type="password" name="popoon_password_compare_{@ref}" />
        </td></tr>
        
    </xsl:template>
    
    
    
    <xsl:template match="xforms:*[xforms:extension/html]" mode="xform">
        <xsl:apply-templates select="xforms:extension/html/*" mode="html"/>
    </xsl:template>
    
    <xsl:template match="xforms:submit" mode="xform">
        <tr><td></td>
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            <td>
                <input type="submit" class="{$submitclass}" name="popoon_action_xforms" value="{xforms:caption[@xml:lang=$lang]}" onclick="{@onclick}"/>
        </td></tr>
    </xsl:template>
    
    <!-- picture upload -->
    <xsl:template match="xforms:upload" mode="xform">
        <tr><td valign="middle" class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
           
	   <td valign="middle">	
			<xsl:if test="string-length(xforms:value) &gt; 0 and @preview != 'no' and @jsinsert != 'yes'">
				<a href="{$uploaddir}{$userID}.{xforms:value}" target="_blank"><img src="{$uploaddir}{$userID}.{xforms:value}" width="40"/></a>
			</xsl:if>
            <xsl:if test="string-length(xforms:value) &gt; 0 and @jsinsert = 'yes'">
				
                    <img onclick="window.returnValue='{$uploaddir}{$userID}.{xforms:value}'; window.close();" 
                         src="{$uploaddir}{$userID}.{xforms:value}" 
                         width="40"/>
                 
			</xsl:if>
            </td>
            
            <td valign="middle">
                <input type="file" size="{$uploadinputsize}" name="{@ref}" />
                <input type="hidden" name="popoon_files_old_{@ref}" value="{xforms:value}"/>
                <input type="hidden" name="popoon_files_type_{@ref}" value="img"/>
        </td></tr>
    </xsl:template>
    
    <!-- document upload -->
    <xsl:template match="xforms:upload[@class='doc']" mode="xform">
        
          <tr><td valign="middle" class="{$tdclass}"><xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
           
            <td class="{$tdclass}" valign="middle" colspan="2">
                <input type="file" size="{$uploadinputsize}" name="{@ref}" />
                <input type="hidden" name="popoon_files_old_{@ref}" value="{xforms:value}"/>
                <input type="hidden" name="popoon_files_type_{@ref}" value="doc"/>
        </td></tr>
        
        <tr><td></td>
	   <td class="{$tdclass}" valign="middle" colspan="2">
			<xsl:if test="string-length(xforms:value) &gt; 0 and @preview != 'no' and @jsinsert != 'yes'">
				<a href="{$uploaddir}{$userID}.{xforms:value}" target="_blank"><xsl:value-of select="xforms:value"/></a>
			</xsl:if>
            <xsl:if test="string-length(xforms:value) &gt; 0 and @jsinsert = 'yes'">				
                    <img src="/js/htmlarea/images/ed_insert_file.gif" 
                              title="{xforms:value}" alt="{xforms:value}" 
                              onclick="var retval = new Array('{$uploaddir}{$userID}.{xforms:value}', '{xforms:value}'); window.returnValue=retval; window.close();"/> 
                    <xsl:value-of select="xforms:value"/>  
                    <a href="{$uploaddir}{$userID}.{xforms:value}"> (<xsl:value-of select="$langdefs/lang/entry[@ID = 'Betrachten']/text[@lang= $lang]" />)</a><br /><br />
	    </xsl:if>
            </td>
          </tr>
    </xsl:template>
    
    <xsl:template match="xforms:*" >
    </xsl:template>
    
    <xsl:template match="xforms:select1" mode="xform">
        
        <tr><td class="{$tdclass}">
            <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></td>
            
            <xsl:value-of disable-output-escaping="yes" select="$secondtd"/>
            
            <td class="{$tdclass}">
                <select name="{@ref}" class="{$inputclass}">
                    <xsl:for-each select="xforms:item">
                        <option value="{xforms:value}">
                            <xsl:if test="xforms:value = ../xforms:value">
                                <xsl:attribute name="selected" value="selected"/>
                            </xsl:if>
                        <xsl:value-of select="xforms:label[@xml:lang=$lang]"/></option>
                    </xsl:for-each>
                </select>
        </td></tr>
    </xsl:template>
    
    <xsl:template match="*" mode="html">
        
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates mode="html"/>
        </xsl:copy>
    </xsl:template >
    
     <xsl:template match="input[@type='submit']" mode="html">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates mode="html"/>
        </xsl:copy>
    </xsl:template >
    
    <xsl:template match="input" mode="html">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
			<xsl:variable name="value" select="ancestor::xforms:select/xforms:value[not(@name) or @name=current()/@name]"/>			
            <xsl:attribute name="value"><xsl:value-of select="$value"/></xsl:attribute>
            <xsl:apply-templates mode="html"/>
        </xsl:copy>
    </xsl:template >
    
    <xsl:template match="input[@type = 'radio']" mode="html">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:if test="ancestor::xforms:select1/xforms:value = @value">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:apply-templates mode="html"/>
        </xsl:copy>
    </xsl:template>
	
    <xsl:template match="input[@type = 'checkbox']" mode="html">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
			<xsl:variable name="value" select="ancestor::xforms:select/xforms:value[not(@name) or @name=current()/@name]"/>
            <xsl:if test="$value = @value">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:apply-templates mode="html"/>
        </xsl:copy>
    </xsl:template>
    
    
    <xsl:template match="option" mode="html">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:if test="ancestor::xforms:select1/xforms:value = @value">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:apply-templates mode="html"/>
        </xsl:copy>
    </xsl:template >
    
    
    <xsl:template match="*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template >
    
</xsl:stylesheet>
