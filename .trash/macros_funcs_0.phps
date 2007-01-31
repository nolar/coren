<?php defined('COREINPAGE') or die('Hack!');
//!!!todo: переделать его под новую систему ядра. пока он выпадает нафиг, и мы им не поьлзуемся.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class macros_funcs_0 extends module
{

/**************************************************************************************************
 * Data structures.
 **************************************************************************************************/

private $path;
private $cache;
private $prefix;

////////////////////////////////////////////////////////////////////////////////////////////////////
// Module maintenance.
////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

/*	$this->prefix = isset($configs['prefix']) ? $configs['prefix'] : 'macros';
	$path = str_replace("\\", "/", $path);
	if (is_null($path)) throw new exception('Misconfig: path');

	if (((strlen($path) < 1) || ($path[0] !== '/')) && ((strlen($path) < 3) || (substr($path, 1, 2) !== ':/')))
		$path = SITEPATH . $path;
	$this->path = rtrim($path, "/");

	$this->cache = array();
*/
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////////////////////////////////////

/**************************************************************************************************
 * MACROS()
 *
 * category	- strictly string, never null, never empty
 * values	- null or array of scalars, maybe empty
 * item		- strictly string, never null, never empty
 * data		- optional data of any type
 **************************************************************************************************/

function macros ($data)
{
/*	// Get parameters.
	$category = $data['category'];
	$values   = $data['values'  ];
	$item     = $data['item'    ];

	// Try to retrieve execfunc name from cache if it is there.
	if (array_key_exists($category, $this->cache) && array_key_exists($item, $this->cache[$category]))
	{
		// Get execfunc name from cache, even if it is null.
		$execfunc = $this->cache[$category][$item];
	} else

	// Search for execfunc through all possible category values only if it is not in cache.
	{
		// Make sure we have an array of category values. Possibly empty, but array.
		if (!is_array($values)) $values = array();

		// Find execfunc, which handles requested item in requested category.
		$execfunc = null;
		foreach ($values as $value) if (is_null($execfunc))
		{
			// Generate name of execfunc we a checking for.
			$candidate = $this->prefix . '_' . $category . '_' . $value . '_' . $item;

			// Split full item name into parts.
			$fileparts = explode('_', $category . '_' . $value . '_' . $item);

			// Search most concrete file with this execfunc defined.
			while (!empty($fileparts) && is_null($execfunc))
			{
				// Generate name of file from available parts.
				$filename = $this->path . '/' . implode('_', $fileparts) . '.php';

				// Try to load this file if it was not loaded earlier.
				if (is_file($filename))
					{require_once($filename);}

				// Check if required execfunc became avaliable.
				if (is_callable($candidate))
					$execfunc = $candidate;

				// Remove rightest part from list of filename parts.
				array_pop($fileparts);
			}
		}

		// Either execfunc was found or not, remember result into cache.
		if (!isset($this->cache[$category])) $this->cache[$category] = array();
		if (!isset($this->cache[$category][$item])) $this->cache[$category][$item] = $execfunc;
	}

	// If execfunc exists, use it to retrieve value and return result.
	if (isset($execfunc))
	{
		ob_start();
		call_user_func($execfunc, $data['data']);
		return ob_get_clean();
	} else

	// If no execfunc was found, just return null and allow other modules to search.
	{
		return null;
	}
*/
}

/**************************************************************************************************
 * FIN.
 **************************************************************************************************/
}

?>