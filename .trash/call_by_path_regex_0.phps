<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
# Модуль вызовает другой (обычно нерезидентный) модуль в реакцию на событие генерации данных,
# но только если путь в http-запросе удовлетворяет регулярному выражению (regex) либо glob-маске.
# Если путь не соответствует условиям срабатывания, то ничего никогда не вызывается.
#
# Фактически это способ с помощью одного класса (implementer'а) реализовать автоматическое
# определение какой контент на какой странице должен генерироваться. Может использоваться либо
# в обычных путях и разбросанных файлах скриптов, которые однотипно ссылаются на ядро и ничего
# сами не делают и не определяют, либо с помощью механизма mod_rewrite сервера apache.
#
# Пример для mod_rewrite:
#
# <IfModule mod_rewrite.c>
#	RewriteEngine on
#	RewriteCond %{REQUEST_FILENAME} !-f
#	RewriteCond %{REQUEST_FILENAME} !-d
#	RewriteRule ^(.*)$ index.php/$1 [L]
# </IfModule>
#
# Этот пример показывает как забросить все запросы ко всем ресурсам сайта (или подкаталога сайта)
# в один единственный файл, который ссылается на ядро. При запросе таких ресурсов ядро будет
# все видеть так, будто оно получило запрос прямым обращением к файлу в соответствующем каталоге.
# И набор модулей данного класса могут решить какие данные генерировать на основании пути в запросе,
# хотя и существует один-единственный файл скрипта в корне сайта/каталога.
#
# Этот модуль также делает некоторые различия для главного и вспомогательного контента на странице.
# Если какой-то другой модуль через систему событий заявил что он сам будет генерировать главный
# контент, а в нашем модуле тоже предплагается главный контент, то мы его уже не будем генерировать.
# С другой стороны, если контент данного модуля помечен как вспомогательный, то он будет всегда
# генерироваться вне зависимости от наличия главного контента в других модулях.
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class call_by_path_regex_0 extends coren_object
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $regex;
protected $event;
protected $sticky;//???
protected $data;
protected $slot;
#
####################################################################################################
#
public function __construct ($configs)
{
	$this->regex  = isset($configs['regex']) ? $configs['regex'] : null;
	$this->event  = isset($configs['event']) ? $configs['event'] : null;
//???	$this->sticky = isset($configs['sticky']) ? $configs['sticky'] : null;
//???	$this->data   = isset($configs['data'  ]) ? $configs['data'  ] : null;
//???	$this->data   = (($temp = @unserialize($this->data)) !== false) ? $temp : null;

	$this->slot   = isset($configs['slot'  ]) ? $configs['slot'  ] : null;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function work ($data)
{
	if (!is_null($this->slot) && coren::slot_used($this->slot)) return;

	$request_uri = $_SERVER['REQUEST_URI'];
	//!!!todo: handle absence of variable
	$request_parsed = parse_url($request_uri);
	//!!!todo: handle FALSE result
	$request_path = $request_parsed['path'];
	var_dump($this->regex, $request_path);
	if (preg_match($this->regex, $request_path))
	{
		coren::event($this->event);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>