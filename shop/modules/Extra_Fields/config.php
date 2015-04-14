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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v31 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }
/**
 * Global definitions for Extra fields module
 */

if (defined('IS_IMPORT')) {
    $modules_import_specification['EXTRA_FIELDS'] = array(
        'script'        => '/modules/Extra_Fields/import.php',
        'permissions'    => 'AP',
        'need_provider'    => true,
        'orderby'        => 40,
        'is_language'    => true,
        'export_sql'    => "SELECT $sql_tbl[extra_fields].fieldid FROM $sql_tbl[extra_fields], $sql_tbl[extra_fields_lng] WHERE $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_fields_lng].fieldid AND $sql_tbl[extra_fields_lng].code = '{{code}}' GROUP BY $sql_tbl[extra_fields].fieldid",
        'table'         => 'extra_fields',
        'key_field'     => 'fieldid',
        'columns'        => array(
            'fieldid'        => array(
                'type'        => 'N',
                'default'    => 0),
            'service_name'    => array(
                'is_key'    => true,
                'required'    => true),
            'code'            => array(
                'type'        => 'C',
                'required'  => true,
                'array'        => true),
            'field'            => array(
                'array'        => true),
            'default'        => array(),
            'orderby'        => array(
                'type'        => 'N',
                'default'    => 0),
            'active'        => array(
                'type'        => 'B',
                'default'    => 'Y'),
        )
    );

    $modules_import_specification['PRODUCTS_EXTRA_FIELD_VALUES'] = array(
        'script'                => '/modules/Extra_Fields/import_values.php',
        'permissions'            => 'AP',
        'need_provider'            => true,
        'orderby'                => 2,
        'parent'                => 'PRODUCTS',
        'import_note'            => 'txt_import_note_products_extra_fields_values',
        'onstartimportsection'    => 'func_ef_before_import',
        'oninitexport'            => 'func_ef_init_export',
        'oninitimport'            => 'func_ef_init_import',
        'export_sql'            => "SELECT $sql_tbl[extra_field_values].productid FROM $sql_tbl[extra_fields], $sql_tbl[extra_field_values] WHERE $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_field_values].fieldid GROUP BY $sql_tbl[extra_field_values].productid",
        'table'         => 'extra_field_values',
        'key_field'     => 'productid',
        'columns'                => array(
            'productid'                => array(
                'type'                    => 'N',
                'is_key'                => true,
                'default'                => 0),
            'productcode'            => array(
                'is_key'                => true),
            'product'                => array(
                'is_key'                => true),
        )
    );

}
if (defined('TOOLS')) {
    $tbl_keys['extra_fields.provider'] = array(
        'keys' => array('extra_fields.provider' => 'customers.id'),
        'where' => "customers.usertype IN ('A','P')",
        'fields' => array('field')
    );
    $tbl_keys['extra_field_values.productid'] = array(
        'keys' => array('extra_field_values.productid' => 'products.productid'),
        'fields' => array('fieldid')
    );
    $tbl_keys['extra_field_values.fieldid'] = array(
        'keys' => array('extra_field_values.fieldid' => 'extra_fields.fieldid'),
        'fields' => array('productid')
    );
    $tbl_demo_data['Extra_Fields'] = array(
        'extra_fields' => '',
        'extra_fields_lng' => '',
        'extra_field_values' => ''
    );
}

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Extra_Fields';
/*
 Load module functions
*/
if (!empty($include_func))
    require_once $_module_dir . XC_DS . 'func.php';
?>
