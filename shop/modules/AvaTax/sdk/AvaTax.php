<?php
/**
 * AvaTax.php
 *
 * @package Base
 */

/**
 * Defines class loading search path
 */
function avatax_autoloader($class_name)
{
	$path=dirname(__FILE__).'/classes/'.$class_name . '.class.php';

	if(!file_exists($path))
	{
		$path=dirname(__FILE__).'/classes/BatchSvc/'.$class_name . '.class.php';

	}

    if(file_exists($path))
	   require_once $path;
}

spl_autoload_register('avatax_autoloader');

function EnsureIsArray($obj)
{
    if (is_object($obj))
        $item[0] = $obj;
    else
        $item = (array)$obj;
    
    return $item;
}
