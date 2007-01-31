<?xml version="1.0" encoding="cp1251"?>
<xsl:stylesheet
	xmlns:xsl      = "http://www.w3.org/1999/XSL/Transform"
	xmlns:identify = "http://coren.numeri.net/namespaces/identify/"
	xmlns:coren    = "http://coren.numeri.net/namespaces/coren/"
>

<xsl:template match='identify:session-start-failure'
identify:dummy="" xsl:exclude-result-prefixes="identify">
	<form action="" method="POST">
	Login:<br/>
	<input type="text" name="logname">
		<xsl:attribute name="value">
			<xsl:value-of select="identify:credentials/identify:logname" />
		</xsl:attribute>
	</input>
	<br/>
	<input type="text" name="password">
		<xsl:attribute name="value">
			<xsl:value-of select="identify:credentials/identify:password" />
		</xsl:attribute>
	</input>
	<br/>
	<input type="submit" />
	</form>
</xsl:template>


</xsl:stylesheet>
