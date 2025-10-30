<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template name="replace-substring">
      <xsl:param name="value" />
      <xsl:param name="from" />
      <xsl:param name="to" />
      <xsl:choose>
         <xsl:when test="contains($value,$from)">
            <xsl:value-of select="substring-before($value,$from)" />
            <xsl:value-of select="$to" />
            <xsl:call-template name="replace-substring">
               <xsl:with-param name="value" select="substring-after($value,$from)" />
               <xsl:with-param name="from" select="$from" />
               <xsl:with-param name="to" select="$to" />
            </xsl:call-template>
         </xsl:when>
         <xsl:otherwise>
            <xsl:value-of select="$value" />
         </xsl:otherwise>
      </xsl:choose>
</xsl:template>
<xsl:template match="/sql">
<!-- fk -->
	<xsl:for-each select="table">
		<xsl:for-each select="row">
			<xsl:for-each select="relation">
				<xsl:text>Table-1:</xsl:text>
				<xsl:value-of select="@table" />
				<xsl:text>;Table-1-Field:</xsl:text>
				<xsl:value-of select="@row" />
				<xsl:text>;Table-2:</xsl:text>
				<xsl:value-of select="../../@name" />
				<xsl:text>;Table-2-Field:</xsl:text>
				<xsl:value-of select="../@name" />
				<xsl:text>
</xsl:text>
			</xsl:for-each>
		</xsl:for-each>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>

