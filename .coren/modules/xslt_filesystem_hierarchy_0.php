<?php defined('CORENINPAGE') or die('Hack!');
//!!! перемеиновать в xslt_from_hierarchy_0
//!!! добавить xslt_from_database, xslt_from_catalog, xslt_from_...
####################################################################################################
####################################################################################################
####################################################################################################
#
# Хранение XSLT в иерархии каталогов вида .../implementer/module/method/identifier/format.xsl
# В каждом поле есть спец-значение "@", которое испоьлзуется для любого значения в информации об xml-данных.
# Итого существует 32 комбинации как для одного и того же типа данных может быть собраны разные xslt-файлы.
# Причем файл .../@/@/@/@/@.xsl подгружается всегда, независимо от того, были ли данные вообще. Это всеобщий XSLT.
# Разумеется, каждый файл подгружается только один раз, чтобы избежать дублирования xslt-шаблонов.
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class xslt_filesystem_hierarchy_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $dir_path    ;
protected $dir_absolute;
protected $dir_required;
protected $dir_automake;
protected $dir_umask   ;
protected $dir         ;
#
private $itemstates = array();
private $foundfiles = array();
private $datas      = array();
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (!coren::depend('_path_normalizer_0'))
		throw new exception("Tool '_path_normalizer_0' missed.");

	if(isset($configs['dir_path'    ])) $this->dir_path     = $configs['dir_path'    ];
	if(isset($configs['dir_absolute'])) $this->dir_absolute = $configs['dir_absolute'];
	if(isset($configs['dir_required'])) $this->dir_required = $configs['dir_required'];
	if(isset($configs['dir_automake'])) $this->dir_automake = $configs['dir_automake'];
	if(isset($configs['dir_umask'   ])) $this->dir_umask    = $configs['dir_umask'   ];
	$this->dir = _path_normalizer_0::normalize_dir($this->dir_path, $this->dir_absolute ? null : SITEPATH);
	if ($this->dir_automake && !file_exists($this->dir))
	{
		$old_umask = umask(octdec($this->dir_umask));
		mkdir($this->dir, 0777, true);
		umask($old_umask);
	}
	if ($this->dir_required && !is_dir($this->dir))
	{
		throw new exception("Required directory '{$this->dir}' does not exist.");
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function on_new_data ($data)
{
	$identifier = $data['identifier'];
	$this->datas[] = array(
		'implementer'	=> $data[null]['implementer'],
		'module'	=> $data[null]['module'     ],
		'method'	=> $data[null]['method'     ],
		'identifier'	=> $data[      'identifier' ]);
}
#
####################################################################################################
#
function on_populate_xslt ($data)
{
	$format = coren::get_xsl_format();
	if ($format == '') return;

	$files = array();
	if (file_exists($filename = "{$this->dir}.xsl"          )) $files[] = $filename;
	if (file_exists($filename = "{$this->dir}${format}/.xsl")) $files[] = $filename;
	foreach (coren::names() as $identifier)
	if ($identifier != '')
	{
		//???todo: normalize identifier to filename here: all special chars to "_".
		if (file_exists($filename = "{$this->dir}{$identifier}.xsl"          )) $files[] = $filename;
		if (file_exists($filename = "{$this->dir}${format}/{$identifier}.xsl")) $files[] = $filename;
	}

	foreach ($files as $file)
	{
		$xslf = new DOMDocument();
		$xslf->load($file);

		$xslt = coren::xsl();
		//todo: optimize this: remove fragment, append immediately:
		$frag = $xslt->createDocumentFragment();
		for ($i = 0; $i < $xslf->documentElement->childNodes->length; $i++)
			$frag->appendChild($xslt->importNode($xslf->documentElement->childNodes->item($i), true));
		$xslt->documentElement->appendChild($frag);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>