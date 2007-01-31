<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class _dom_builder_0 extends coren_tool
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public static function build_nodetree ($document, $ns_uri, $ns_prefix, $name, $value)
{
	if (is_null($ns_uri))
	{
		$result = $document->createElement($name);
	} else
	if (is_null($ns_prefix))
	{
		$result = $document->createElementNS($ns_uri, $name);
	} else
	{
		$result = $document->createElementNS($ns_uri, $ns_prefix . ':' . $name);
	}

	if (is_array($value))
	{
		foreach ($value as $key => $val)
		$result->appendChild(self::build_nodetree($document, $ns_uri, $ns_prefix, $key, $val));
	} else
	if (is_scalar($value))
	{
		$result->appendChild($document->createTextNode($value));
	}
	return $result;
}
#
####################################################################################################
#
public static function build_attribute ($document, $ns_uri, $ns_prefix, $name, $value)
{
	if (is_null($ns_uri))
	{
		$result = $document->createAttribute($name);
	} else
	if (is_null($ns_prefix))
	{
		$result = $document->createAttributeNS($ns_uri, $name);
	} else
	{
		$result = $document->createAttributeNS($ns_uri, $ns_prefix . ':' . $name);
	}

	if (is_array($value))
	{
		throw new exception("Value of attribute can not be an array.");
	} else
	if (is_scalar($value))
	{
		$result->appendChild($document->createTextNode($value));
	}
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>