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
 * Import/export customer reviews
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v31 (xcart_4_7_0), 2015-02-17 23:56:28, import.php, Yuriy
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
Deleted product data:
    data_type:  DP
    key:        <Product ID>
    value:      <Flags>

Note: RESERVED is used if ID is unknown
******************************************************************************/

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

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
                    db_query("DELETE FROM $sql_tbl[product_reviews] WHERE productid = '$value[productid]'");
                }
            }
        }
        else {
        // Delete all products and related information...
            db_query("DELETE FROM $sql_tbl[product_reviews]");
        }

        $import_file['drop'][strtolower($section)] = '';
    }

    foreach ($data_row as $row) {

    // Import pricing data...

        // Delete old data
        $tmp = func_import_get_cache('DP', $row['productid']);
        if (strpos($tmp, 'r') === false) {
            db_query("DELETE FROM $sql_tbl[product_reviews] WHERE productid = '$row[productid]'");
            func_import_save_cache('DP', $row['productid'], $tmp."r");
        }

        // Import customer review
        foreach ($row['message'] as $k => $v) {
            $data = array(
                'productid'    => $row['productid'],
                'message'    => $v,
                'email'        => $row['email'][$k]
            );

            $data = func_addslashes($data);
            func_array2insert('product_reviews', $data);
            $result['customer_reviews']['added']++;
        }

        echo ". ";
        func_flush();

    }

// Export data
} elseif ($import_step == 'export') {

    while ($productid = func_export_get_row($data)) {
        if (empty($productid))
            continue;

        // Get product signature
        $p_row = func_export_get_product($productid);

        // Get data
        $row = func_query("SELECT email, message FROM $sql_tbl[product_reviews] WHERE productid = '$productid'");

        if (empty($p_row) || empty($row))
            continue;

        foreach ($row as $v) {
            $v = func_array_merge($v, $p_row);

            // Export row
            if (!func_export_write_row($v))
                break;
        }

    }
}

?>
