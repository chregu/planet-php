<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:dir="http://apache.org/cocoon/directory/2.0"
		xmlns:zip="http://apache.org/cocoon/zip-archive/1.0"> 
 
		<xsl:param name="dirprefix"/>
 
		<xsl:template match="/">
				<zip:archive>
						<xsl:for-each select="//dir:directory">
						
								<!-- Directories don't need to be defined since only those with
										a file will be added
								
								
								<xsl:variable name="directoryPath">
										<xsl:for-each select="ancestor::*">
												<xsl:value-of select="@name"/>
												<xsl:text>/</xsl:text>
										</xsl:for-each>
								</xsl:variable>
								<xsl:variable name="DirToBeZipped">
										<xsl:value-of select="concat($directoryPath,@name,'/')"/>
								</xsl:variable>
								
								<zip:entry name="{$DirToBeZipped}" src="{$dirprefix}{$DirToBeZipped}"/>
								
								-->
								
								<xsl:for-each select="./dir:file">
										<xsl:variable name="filePath">
												<xsl:for-each select="ancestor::*[@name!='']">
														<xsl:value-of select="@name"/>
														<xsl:text>/</xsl:text>
												</xsl:for-each>
										</xsl:variable>
										<xsl:variable name="fileToBeZipped">
												<xsl:value-of select="concat($filePath,@name)"/>
										</xsl:variable>
										<zip:entry name="{$fileToBeZipped}" src="{$dirprefix}{$fileToBeZipped}"/>
								</xsl:for-each>
						</xsl:for-each>
				</zip:archive>
		</xsl:template>
 </xsl:stylesheet>
