<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. ������ includes, excludes, ����� ����������� �� ��������������, � ��������������� �� ���, � ������ ������ ����
//todo: 1. ��� �������: ������ �� �������, ������� �� ����� (�������?), � ������,
//todo:    ������� �� ���������. � ����� ����� ��������� (ALL*included)-excluded.
//todo:    ���� included �� �����, ������� ��� �� ����� ������ (����� ���������). ???
//todo:    ���� excluded �� �����, ������ �� ��������� �� ����, ��� �����.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class logger_database_0_table_absent extends exception {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class logger_database_0 extends module implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $autocreate;

protected $disabled;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->autocreate = isset($configs['autocreate']) ? $configs['autocreate'] : null;

	$this->disabled = false;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function core_event ($args)
{
	if (!$this->disabled)
	{
		try
		{
			core::db('write', $args);
		}
		catch (logger_database_0_table_absent $exception)
		{
			if ($this->autocreate)
			{
				core::db('create');
				try
				{
					core::db('write', $args);
				}
				catch (logger_database_0_table_absent $exception)
				{
					$this->disabled = true;
					throw $exception;
				}
			} else
			{
				$this->disabled = true;
				throw $exception;
			}
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>