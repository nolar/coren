<?xml version="1.0" encoding="cp1251"?>
<xsl:stylesheet
	xmlns:xsl      = "http://www.w3.org/1999/XSL/Transform"
	xmlns:identify = "http://coren.numeri.net/namespaces/identify/"
	xmlns:coren    = "http://coren.numeri.net/namespaces/coren/"
>

<xsl:template match='identify:session-detect-failure'
identify:dummy="" xsl:exclude-result-prefixes="identify">
	Wow! ���� ������ ���������� ��-�� ������ <xsl:value-of select="identify:code"/>.
	���������! (� ��� ������� ����� �� �������� ���������).
</xsl:template>


</xsl:stylesheet>
