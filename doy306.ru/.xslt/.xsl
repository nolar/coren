<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
	xmlns:xsl      = "http://www.w3.org/1999/XSL/Transform"
	xmlns:identify = "http://coren.numeri.net/namespaces/identify/"
	xmlns:coren    = "http://coren.numeri.net/namespaces/coren/"
>

<xsl:output method="html" indent="yes" encoding="utf-8" />

<!-- это чтобы убрать все необработанные теги из подситемы идентификации -->
<xsl:template match='identify:abc' identify:dummy="" xsl:exclude-result-prefixes="identify">
</xsl:template>

<!-- это чтобы убрать вообще все необработанные теги -->
<xsl:template match="*">
</xsl:template>

<!-- это чтобы необработанные теги копровались целиком, а не только текстом -->
<xsl:template match="*">
    <xsl:copy>
        <xsl:apply-templates select="@*" />
        <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>
<xsl:template match="text()">
    <xsl:value-of select="." disable-output-escaping="yes"/>
</xsl:template>
<xsl:template match="@*|node()">
    <xsl:copy>
        <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
</xsl:template>
<!--
-->


<xsl:template match="/">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="/coren:data"
identify:dummy="" xsl:exclude-result-prefixes="identify">
	<html>
	<head>
		<title><xsl:value-of select="$title"/></title>
	</head>
	<body>
		<table class="layout">
			<tr>
				<td>TL</td>
				<td>
				<xsl:choose>
					<xsl:when test="identify:account-detect-success">
						<xsl:text>Welcome, </xsl:text>
						<xsl:value-of select="identify:account-detect-success/identify:information/identify:logname"/>
						<xsl:text>!</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						Welcome, anonymous visitor!
					</xsl:otherwise>
				</xsl:choose>
				</td>
				<td>TR</td>
			</tr>
			<tr>
				<td>ML</td>
				<td>
					<xsl:apply-templates select="*[@coren:slot='main' or not(@coren:slot)]"/>
				</td>
				<td>MR</td>
			</tr>
		</table>
	</body>
	</html>
	Admin Email: <xsl:value-of select="$admin_email"/>
</xsl:template>


</xsl:stylesheet>
