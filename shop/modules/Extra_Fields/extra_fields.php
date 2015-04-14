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
 * Gets extra-fields related data
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v44 (xcart_4_7_0), 2015-02-17 23:56:28, extra_fields.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

$provider_condition = ($single_mode ? '' : " AND $sql_tbl[extra_fields].provider='$extra_fields_provider' ");
if (!empty($productid) && in_array(AREA_TYPE, array('C', 'B'))) {
    // LEFT JOIN is needed to obtain default value from xcart_extra_fields table
    $_join_type = $config['Extra_Fields']['display_default_extra_fields'] == 'Y' ? 'LEFT' : 'INNER';
    $extra_fields = func_query("SELECT $sql_tbl[extra_fields].*, $sql_tbl[extra_field_values].value as field_value, IF($sql_tbl[extra_fields_lng].field != '', $sql_tbl[extra_fields_lng].field, $sql_tbl[extra_fields].field) as field FROM $sql_tbl[extra_fields] $_join_type JOIN $sql_tbl[extra_field_values] ON $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_field_values].fieldid AND $sql_tbl[extra_field_values].productid = '$productid' LEFT JOIN $sql_tbl[extra_fields_lng] ON $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_fields_lng].fieldid AND $sql_tbl[extra_fields_lng].code = '$shop_language' WHERE 1 $provider_condition ORDER BY $sql_tbl[extra_fields].orderby");

} else {
    if ($productid) {
        $extra_fields = func_query("SELECT $sql_tbl[extra_fields].*, $sql_tbl[extra_field_values].value as field_value, IF($sql_tbl[extra_fields_lng].field != '', $sql_tbl[extra_fields_lng].field, $sql_tbl[extra_fields].field) as field FROM $sql_tbl[extra_fields] LEFT JOIN $sql_tbl[extra_field_values] ON $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_field_values].fieldid AND $sql_tbl[extra_field_values].productid = '$productid' LEFT JOIN $sql_tbl[extra_fields_lng] ON $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_fields_lng].fieldid AND $sql_tbl[extra_fields_lng].code = '$shop_language' WHERE 1 $provider_condition ORDER BY $sql_tbl[extra_fields].orderby");
    } else {
        $extra_fields = func_query("SELECT $sql_tbl[extra_fields].*, IF($sql_tbl[extra_fields_lng].field != '', $sql_tbl[extra_fields_lng].field, $sql_tbl[extra_fields].field) as field FROM $sql_tbl[extra_fields] LEFT JOIN $sql_tbl[extra_fields_lng] ON $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_fields_lng].fieldid AND $sql_tbl[extra_fields_lng].code = '$shop_language' WHERE 1 $provider_condition ORDER BY $sql_tbl[extra_fields].orderby");
    }
}

if (!empty($extra_fields)) {

 
    if ($productid) {
        foreach($extra_fields as $_rf_index => $_ef) {
            $extra_fields[$_rf_index]['is_value'] = trim($_ef['field_value']) == '' ? '' : 'Y';
        }
    }

    if (in_array(AREA_TYPE, array('C', 'B')) && $config["Extra_Fields"]["display_default_extra_fields"] == 'Y') {
        foreach ($extra_fields as $ef_k=>$ef_v) {
            $_def_is_value = trim($ef_v['value']) == '' ? '' : 'Y';
            if (empty($ef_v['field_value']) && $ef_v['is_value'] != 'Y' && $_def_is_value == 'Y') {
                $extra_fields[$ef_k]['is_value'] = 'Y';
                $extra_fields[$ef_k]['field_value'] = $ef_v['value'];
            }
        }
    }

    if (in_array(AREA_TYPE, array('C', 'B')) && !$product_info["allow_active_content"]) {
        foreach ($extra_fields as $k => $v) {
            $extra_fields[$k]['field_value'] = func_xss_free($v['field_value']);
        }
    }

    $smarty->assign('extra_fields', $extra_fields);
}

?>
