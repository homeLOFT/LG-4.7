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
 * Import international descriptions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v31 (xcart_4_7_0), 2015-02-17 23:56:28, import_lng.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/******************************************************************************
Used cache format:
Products (by Product ID):
    data_type:  PI
    key:        <Product ID>
    value:      [<Product code> | RESERVED]
Products (by Product code):
    data_type:  PR
    key:        <Product code>
    value:      [<Product ID> | RESERVED]
Products (by Product name):
    data_type:  PN
    key:        <Product name>
    value:      [<Product ID> | RESERVED]
Product options (classes) by Product ID / Product code / Product name:
    data_type:  PC
    key:        <Product ID / Product code / Product name>EOL<Class>
    value:      [<Class ID> | RESERVED]

Note: RESERVED is used if ID is unknown
******************************************************************************/

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

$provider_condition = ($single_mode ? '' : " AND $sql_tbl[products].provider='".$import_data_provider."'");

if ($import_step == 'process_row') {
/**
 * PROCESS ROW from import file
 */

    // Check productid / productcode / product
    list($_productid, $_variantid) = func_import_detect_product($values);
    if (is_null($_productid) || ($action == 'do' && empty($_productid))) {
        func_import_module_error('msg_err_import_log_message_14');
        return false;
    }

    // Check class
    foreach ($values['code'] as $k => $c) {
        $v = $values['class'][$k];
        if (empty($v))
            continue;

        // Check class name
        $_classid = func_import_get_pb_cache($values, 'PC', $v);
        if (is_null($_classid) && !empty($_productid) && empty($import_file['drop']['product_options'])) {
            $_classid = func_query_first_cell("SELECT classid FROM $sql_tbl[classes] WHERE class = '".addslashes($v)."' AND productid = '$_productid'");
            if (empty($_classid)) {
                $_classid = NULL;
            } elseif ($action == 'do') {
                func_import_save_pb_cache($values, 'PC', $v, $_classid);
            }
        }
        if (is_null($_classid) || ($action == 'do' && empty($_classid))) {
            func_import_module_error('msg_err_import_log_message_16', array('class' => $v));
            unset($values['class'][$k], $values['option'][$k]);
        }

        $values['classid'][$k] = $_classid;
    }

    $values['productid'] = $_productid;

    $data_row[] = $values;

}
elseif ($import_step == 'finalize') {
/**
 * FINALIZE rows processing: update database
 */

    if (!empty($import_file['drop'][strtolower($section)])) {
        if ($provider_condition) {
            // Search for products created by provider...
            $products_to_delete = db_query("SELECT productid FROM $sql_tbl[products] WHERE 1 ".$provider_condition);
            if ($products_to_delete) {
                while ($value = db_fetch_array($products_to_delete)) {
                    $oids = func_query_column("SELECT classid FROM $sql_tbl[classes] WHERE productid = '$value[productid]'");
                    if (!empty($oids)) {
                        db_query("DELETE FROM $sql_tbl[class_lng] WHERE classid IN ('".implode("','", $oids)."')");
                    }
                }
            }
        }
        else {
        // Delete all products and related information...
            db_query("DELETE FROM $sql_tbl[class_lng]");
        }

        $import_file['drop'][strtolower($section)] = '';
    }

    foreach ($data_row as $row) {

    // Import pricing data...

        foreach ($row['classid'] as $k => $_classid) {
            if (empty($row['class_name'][$k]) && empty($row['descr'][$k]))
                continue;

            $data = array(
                'class'        => addslashes($row['class_name'][$k]),
                'classtext'    => addslashes($row['descr'][$k])
            );

            // Add class language labels
            if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[class_lng] WHERE classid = '$_classid' AND code = '".addslashes($row['code'][$k])."'") == 0) {
                $data['code']        = addslashes($row['code'][$k]);
                $data['classid']    = $_classid;
                func_array2insert('class_lng', $data);
                $result[strtolower($section)]['added']++;

            // Update class language labels
            } else {
                func_array2update('class_lng', $data, "classid = '$_classid' AND code = '".addslashes($row['code'][$k])."'");
                $result[strtolower($section)]['added']++;
            }

            echo ". ";
            func_flush();
        }

    }

// Export data
} elseif ($import_step == 'export') {

    while (($id = func_export_get_row($data)) !== false) {
        if (empty($id))
            continue;

        // Get data
        $mrow = func_query("SELECT $sql_tbl[class_lng].classid, $sql_tbl[class_lng].code, $sql_tbl[class_lng].class as class_name, $sql_tbl[class_lng].classtext as descr, $sql_tbl[classes].class FROM $sql_tbl[classes], $sql_tbl[products], $sql_tbl[class_lng] WHERE $sql_tbl[classes].classid = $sql_tbl[class_lng].classid AND $sql_tbl[class_lng].code = '$current_code' AND $sql_tbl[classes].productid = $sql_tbl[products].productid AND $sql_tbl[classes].productid = '$id'".(empty($provider_sql) ? "" : " AND $sql_tbl[products].provider = '$provider_sql'"));
        if (empty($mrow))
            continue;

        // Get product signature
        $p_row = func_export_get_product($id);
        if (empty($p_row))
            continue;

        foreach ($mrow as $row) {
            $row = func_array_merge($row, $p_row);

            // Write row
            if (!func_export_write_row($row))
                break;
        }
    }

}

?>
