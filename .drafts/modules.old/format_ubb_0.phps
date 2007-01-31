<?php defined('CORENINPAGE') or die('Hack!');
//todo: переместить callback в аргументы метода __content__, чтобы она не мешалась в полях.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class format_ubb_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $paired_tags;		// list of known tags, that are used in open-close manner
protected $atomic_tags;		// list of known tags, that are used without closing
protected $quoter_tags;		// list of known tags, that disables other tags inside
protected $hide_unknown;	// boolean. true - hide unknown tags. false - show them as is.
protected $hide_orphan;		// boolean. true - hide orphan  tags. false - show them as is.
protected $strict_close;	// boolean. true - tag must be closed with [/tag]. false - [/] is enough.

private   $callback; // function to use for formatting tags (if it's not null).

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->paired_tags = array();
	$paired_tags = core::find_scalar(array($configs), array('paired_tags'), null);
	$paired_tags = core::explode_scalar(strtolower($paired_tags));
	foreach ($paired_tags as $paired_tag)
		if ($paired_tag{0} != '/')
			$this->paired_tags[] = $paired_tag;

	$this->atomic_tags = array();
	$atomic_tags = core::find_scalar(array($configs), array('atomic_tags'), null);
	$atomic_tags = core::explode_scalar(strtolower($atomic_tags));
	foreach ($atomic_tags as $atomic_tag)
		if ($atomic_tag{0} != '/')
			$this->atomic_tags[] = $atomic_tag;

	$this->quoter_tags = array();
	$quoter_tags = core::find_scalar(array($configs), array('quoter_tags'), null);
	$quoter_tags = core::explode_scalar(strtolower($quoter_tags));
	foreach ($quoter_tags as $quoter_tag)
		if ($quoter_tag{0} != '/')
			$this->quoter_tags[] = $quoter_tag;

	$this->hide_unknown = core::find_scalar(array($configs), array('hide_unknown'), null);
	$this->hide_orphan  = core::find_scalar(array($configs), array('hide_orphan' ), null);
	$this->strict_close = core::find_scalar(array($configs), array('strict_close'), null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function format ($args)
{
	$this->callback = isset($args['callback']) && is_callable($args['callback']) ? $args['callback'] : null;
	$text = isset($args['text']) ? $args['text'] : '';

	$tagstack = array(0=>array('tag'=>'', 'val'=>'', 'txt'=>''));
	$quotelvl = 0;
	while ($text != '')
	{
		if (preg_match('/^ (.*?) \\[(.*?)\\] (.*) $/six', $text, $matchtag))
		{
			$tagstack[count($tagstack)-1]['txt'] .= $matchtag[1];
			$temptag = $matchtag[2];//??? strlolower()? trim()?
			$text = $matchtag[3];

			if (($temptag != '') && ($temptag{0} == '/'))
			{
				if ((count($tagstack) > 1) && (!$this->strict_close || (substr_compare($temptag, $tagstack[count($tagstack)-1]['tag'], 1) == 0)))
				{
					if ($quotelvl && in_array($tagstack[count($tagstack)-1]['tag'], $this->quoter_tags))
						$quotelvl--;
					$item = array_pop($tagstack);
					$tagstack[count($tagstack)-1]['txt'] .= $this->__content($item['tag'], $item['val'], $item['txt'], $quotelvl, $item['opener'], $temptag);
				} else
				{
					$tagstack[count($tagstack)-1]['txt'] .= $this->hide_orphan ? '' : '[' . $temptag . ']';
				}
			} else
			{
				if (preg_match('/^ (.*?) = (.*) $/six', $temptag, $matchval))
				{
					$tag = $matchval[1];
					$val = $matchval[2];
				} else
				{
					$tag = $temptag;
					$val = null;
				}

				if (in_array($tag, $this->atomic_tags))
				{
					$tagstack[count($tagstack)-1]['txt'] .= $this->__content($tag, $val, null, $quotelvl, $temptag, null);
				} else
				{
					if (in_array($tag, $this->quoter_tags)) $quotelvl++;
					array_push($tagstack, array('tag'=>$tag, 'val'=>$val, 'txt'=>'', 'opener'=>$temptag));
				}
			}
		} else
		{
			$tagstack[count($tagstack)-1]['txt'] .= $text;
			$text = '';
		}

		if (($text == '') && (count($tagstack) > 1))
			$text = '[/' . $tagstack[count($tagstack)-1]['tag'] . ']';
	}
	return nl2br($tagstack[0]['txt']);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Formats content of tag according to level of quoting and module configs.
protected function __content ($tag, $value, $content, $quotelvl, $opener, $closer)
{
	$result = null;

	if (is_null($result) && $quotelvl)
		$result = '' .
			(is_null($opener ) ? '' : '[' . $opener. ']').
			(is_null($content) ? '' :       $content    ).
			(is_null($closer ) ? '' : '[' . $closer. ']');

	if (is_null($result) && !is_null($this->callback))
	{
		$result = call_user_func($this->callback, $tag, $value, $content);
	}

	if (is_null($result))
	{
		$result = core::template($tag, array('tag'=>$tag, 'value'=>$value, 'content'=>$content));
	}

	if (is_null($result))
	{
		if ($this->hide_unknown || in_array($tag, $this->paired_tags) || in_array($tag, $this->atomic_tags) || in_array($tag, $this->quoter_tags))
		{
			$result = $content;
		} else
		{
			$result = '' .
				(is_null($opener ) ? '' : '[' . $opener. ']').
				(is_null($content) ? '' :       $content    ).
				(is_null($closer ) ? '' : '[' . $closer. ']');
		}
	}

	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>