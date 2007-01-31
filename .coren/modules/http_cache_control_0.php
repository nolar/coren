<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
# ��������� ��� ��������. ������� �������� ������� ������������� ���������.
# 0) public	- ����������� � ���������(shared) �����.
# 1) private	- ����������� ������ � ������ ���� ����� ����������.
# 2) dynamic	- �������������, ������ ��� ������������ � ������ ������ ���������������.
# 3) paranoid	- �� �� ��� � dynamic, �� ��� � ��������� �� ����� � � ������ ���� �� ������ ��.
#
# �� ������� ����� ������� 0, �� ���� �������� ���������. ����� ��������� ������� ���������������
# ���������. �������� ������� ������. ���� ������ � ������� ����������� ��������� �������� ������
# ��� ������� 0 � 1. ��� ������� 2 � 3 ����� ����������� ������ �������� �� ����� ��������� ������.
#
# ����� ��������� ������ ���� � ������������ �� ���� �������, �� ����� ����� ������ �� ������� 0, 1.
# ������ ��� �� ������� 2 � 3 ������ �� ����������; � ������-�� ����� ��������� �� ���������� �
# �������� ������� ����������� �� ������ ����� (� ��� ����� ����������).
#
# ����� ��������� ����� ���������� ������ � ������� ���������, � ����� ����������� - ������ �
# ������� ����������. ������ �� ������ �����.
#
# � ���������, � ��������� (������, �� ����� ������) ��� ������ etags � ������ ��������� ���������
# http/1.1, ������� ��������� ����� ����� ��������� ������������, � ������ ����� ��� �� �����������.
# � ������� ���� ����� �������� ���� ����� RFC2068 � ���������� ��� ������� �� ����� ����������.
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class http_cache_control_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
//TODO: ������� ��� ������ �������, ������������� ����������� �� ����.
//TODO: - ���/���� ������� �����������; ���� ������, �� ���� ��������� ���������, � ��� - ������ �������� ��������
//TODO: - ��������� �������� last-modified, ���� �� ����� ������� �� ���� ������� ����
//TODO: - ����������� �������� last-modified
//TODO: - ����������� ���������� varylist
//TODO: - ����������� ������� �������������
//TODO: ���
#
//???protected $min_expire;
//???protected $max_expire;
#
protected $level;
protected $starttime;
protected $modified;
protected $expires;
protected $varylist;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

//???	if (isset($configs['min_expire'])) $this->min_expire = (integer) $configs['min_expire'];
//???	if (isset($configs['max_expire'])) $this->max_expire = (integer) $configs['max_expire'];

	$this->starttime = time();
	$this->level = 0;
	$this->varylist = array();

//???	coren::handler('on_cache_level_public'  , 'cache-level-public'  );
//???	coren::handler('on_cache_level_private' , 'cache-level-private' );
//???	coren::handler('on_cache_level_dynamic' , 'cache-level-dynamic' );
//???	coren::handler('on_cache_level_paranoid', 'cache-level-paranoid');
//???	coren::handler('on_cache_stamp_modified', 'cache-stamp-modified');
//???	coren::handler('on_cache_stamp_expires' , 'cache-stamp-expires' );
//???	coren::handler('on_cache_vary_header'   , 'cache-vary-header'   );
//???	coren::handler('on_shutdown'            , coren::event_for_stage_epiwork);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
#... now it understand expire after some timeout in seconds, and expire reset (null).
#
public function cache_level_public ($data)
{
	$this->level = max($this->level, 0);
}
#
####################################################################################################
#
public function cache_level_private ($data)
{
	$this->level = max($this->level, 1);
}
#
####################################################################################################
#
public function cache_level_dynamic ($data)
{
	$this->level = max($this->level, 2);
	$this->expires = $this->starttime;
}
#
####################################################################################################
#
public function cache_level_paranoid ($data)
{
	$this->level = max($this->level, 3);
	$this->expires = $this->starttime;
}
#
####################################################################################################
#
public function cache_stamp_modified ($data)
{
	$timestamp = isset($data['timestamp']) && is_numeric($data['timestamp']) ? $data['timestamp'] : null;
	$time = (!is_null($timestamp) ? (float) $timestamp :
	        (null));
	if (!is_null($time) && (is_null(self::$modified) || (self::$modified < $time)))
		self::$modified = $time;
}
#
####################################################################################################
#
public function cache_stamp_expires ($data)
{
	$timestamp = isset($data['timestamp']) && is_numeric($data['timestamp']) ? $data['timestamp'] : null;
	$timedelay = isset($data['timedelay']) && is_numeric($data['timedelay']) ? $data['timedelay'] : null;
	$time = (!is_null($timestamp) && !is_null($timedelay) ? min((float) $timestamp, (float) $timedelay + $this->starttime) :
	        (                        !is_null($timedelay) ? (float) $timedelay + $this->starttime :
	        (!is_null($timestamp)                         ? (float) $timestamp :
	        (null))));
	if (!is_null($time) && (is_null(self::$expires) || ($time < self::$expires)))
		self::$expires = $time;
}
#
####################################################################################################
#
public function cache_vary_header ($data)
{
	$header = isset($data['header']) && is_string($data['header']) ? $data['header'] : null;
	if (!is_null($header) && ($header != ''))
		$this->varylist[] = $header;
}
#
####################################################################################################
#
public function send ($data)
{
	if (headers_sent())
		throw new exception("Can not send cache-control headers, because headers were sent already.");

	// Last-modified:
	if (!is_null($this->modified))
	{
		header(sprintf("Last-modified: %s", gmdate("r", $this->modified)));
	}

	// Expires:
	if (!is_null($this->expires))
	{
		header(sprintf("Expires: %s", $this->expires > $this->starttime ? gmdate("r", $this->expires) : "0"));
		$maxage = sprintf("max-age=%d,must-revalidate,", max(0, $this->expires - $this->starttime));
	} else
	{
		$maxage = null;
	}

	// Cache-control: & Pragma:
	switch ($this->level)
	{
		case 0:
			header("Cache-control: {$maxage}public");
			header("Pragma: public");
			break;
		case 1:
			header("Cache-control: {$maxage}private");
			header("Pragma: private");
			break;
		case 2:
			header("Cache-control: {$maxage}no-cache");
			header("Pragma: no-cache");
			break;
		case 3:
			header("Cache-control: {$maxage}no-store");
			header("Pragma: no-store");
			break;
		default:
			throw new exception("Bad cache level ({$this->level}).");
	}

	// Vary:
	if (!empty($this->varylist))
	{
		header(sprintf("Vary: %s", implode(',', array_unique($this->varylist))));
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>