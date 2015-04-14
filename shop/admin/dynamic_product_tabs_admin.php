<?php
/********************************************************************************
| Dynamic Product Tabs
| Copyright WebsiteCM Software Inc.
| All rights reserved.
| License: http://www.websitecm.com/downloads/license-agreement.pdf
********************************************************************************
| Tab Editing Container File
********************************************************************************/

/*******************************************************************************
| Default X-Cart includes
********************************************************************************/

define('IS_MULTILANGUAGE', false);
define('USE_TRUSTED_POST_VARIABLES',1);
$trusted_post_variables = array('content');

require './auth.php';
require $xcart_dir.'/include/security.php';

/*******************************************************************************
| Dynamic Product Tabs Executing File
********************************************************************************/
include $xcart_dir.'/modules/DynamicProductTabs/tabs_admin.php';


/*******************************************************************************
| Display Page
********************************************************************************/
$smarty->assign('location', $location);
@include $xcart_dir.'/modules/gold_display.php';

func_display('admin/home.tpl',$smarty);

/****************************************************************************************
| Function: wcmXCartVersion
|
| Purpose:
| 	Return the version of x-cart being run to process accordingly
|
|	Input:
|		N/A
|
|	Return:
|		The first 3 characters of the version: i.e. 3.4, 3.5, 4.0, 4.1, 4.2, 4.3 etc.
|
****************************************************************************************/
if (!function_exists('wcmXCartVersion'))
{
	function wcmXCartVersion()
	{
		global $sql_tbl;
		$return = func_query_first("SELECT value FROM $sql_tbl[config] WHERE name='version'");
		return substr($return['value'],0,3);
	}
}
// Assign x-cart version
$smarty->assign('wcmXCartVersion',wcmXCartVersion());
?>