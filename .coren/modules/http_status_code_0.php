<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class http_status_code_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $code;
protected $text;
protected $modifyable;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['code'])) $this->code = $configs['code'];
	if (isset($configs['text'])) $this->text = $configs['text'];

	if (isset($configs['modifyable'])) $this->modifyable = $configs['modifyable'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function set ($data)
{
	if ($this->modifyable)
	{
		//!!!!!!!??? �������� ��� � ����� �� �����
	}
}
#
####################################################################################################
#
public function send ($data)
{
	if (!is_null($this->code))
	{
		//??? �������� HTTP/1.0 �� 1.1 ��� ��������������� �������? � �� ��� ������ ��� ����� ���� 1.0?
		//??? ��� ������ ���������� �� �������? $_SERVER['SERVER_PROTOCOL']
		var_dump(sprintf("HTTP/1.0 %03d%s", (integer) $this->code, is_null($this->text) ? "" : ' ' . $this->text));
		header(sprintf("HTTP/1.0 %03d%s", (integer) $this->code, is_null($this->text) ? "" : ' ' . $this->text));
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>