<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>            |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT QUALITEAM SOFTWARE LTD   |
| (hereinafter referred to as "THE AUTHOR") OF REPUBLIC OF CYPRUS IS          |
| FURNISHING OR MAKING AVAILABLE TO YOU WITH THIS AGREEMENT (COLLECTIVELY,    |
| THE "SOFTWARE"). PLEASE REVIEW THE FOLLOWING TERMS AND CONDITIONS OF THIS   |
| LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY     |
| INSTALLING, COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND YOUR COMPANY   |
| (COLLECTIVELY, "YOU") ARE ACCEPTING AND AGREEING TO THE TERMS OF THIS       |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT, DO |
| NOT INSTALL OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL  |
| PROPERTY RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT FOR  |
| SALE OR FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY  |
| GRANTED BY THIS AGREEMENT.                                                  |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

/**
 * Module configuration
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v33 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }
/**
 * Global definitions for Manufacturers module
 */

$css_files['Manufacturers'][] = array();

$config['available_images']['M'] = "U";
if (defined('IS_IMPORT')) {
    $modules_import_specification['PRODUCTS']['columns']['manufacturerid'] = array('default'  => 0);
    $modules_import_specification['PRODUCTS']['columns']['manufacturer'] = array('default'  => 0);
}

if (defined('TOOLS')) {
    $tbl_keys['images_M.id'] = array(
        'keys' => array('images_M.id' => 'manufacturers.manufacturerid'),
        'fields' => array('imageid')
    );
    $tbl_keys['manufacturers_lng.manufacturerid'] = array(
        'keys' => array('manufacturers_lng.manufacturerid' => 'manufacturers.manufacturerid'),
        'fields' => array('manufacturerid', 'manufacturer')
    );
    $tbl_keys['manufacturers_lng.code'] = array(
        'keys' => array( 'manufacturers_lng.code' => 'language_codes.code'),
        'fields' => array('manufacturerid', 'manufacturer'),
    );
    $tbl_keys['manufacturers.provider'] = array(
        'keys'         => array(
            'manufacturers.provider' => 'customers.id',
        ),
        'on'         => "customers.usertype IN ('A','P')",
        'fields' => array('manufacturerid', 'manufacturer','provider'),
    );


    $tbl_demo_data['Manufacturers']= array(
        'manufacturers' => '',
        'manufacturers_lng' => '',
        'clean_urls' => "resource_type = 'M'",
        'clean_urls_history' => "resource_type = 'M'",
        'images_M' => 'images'
    );
}

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Manufacturers';
/*
 Load module functions
*/
if (!empty($include_func))
    require_once $_module_dir . XC_DS . 'func.php';

?>
