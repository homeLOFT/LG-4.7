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
 * Functions for Froogle module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v15 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }


/**
 * Translation string to frogle-compatibility-string
 */
function func_froogle_convert($str, $max_len = false)
{
    static $tbl = false;

    if ($tbl === false)
        $tbl = array_flip(get_html_translation_table(HTML_ENTITIES));

    $str = str_replace(array("\n","\r","\t"), array(" ", '', " "), $str);
    $str = strip_tags($str);
    $str = strtr($str, $tbl);

    if ($max_len > 0 && strlen($str) > $max_len) {
        $str = preg_replace("/\s+?\S+.{".intval(strlen($str)-$max_len-1+FROOGLE_TAIL_LEN)."}$/Ss", '', $str).FROOGLE_TAIL;
        if (strlen($str) > $max_len)
            $str = substr($str, 0, $max_len-FROOGLE_TAIL_LEN).FROOGLE_TAIL;
    }

    if (empty($str)) {
        $str = 'Not specified';
    }

    return $str;
}

/**
 * Get additional attributes from special named extra fields
 */
function func_froogle_get_additional_attributes($get_ids = false)
{
    global $active_modules, $sql_tbl, $froogle_additional_attributes;
    static $result = array();

    $md5_args = $get_ids;
    if (isset($result[$md5_args])) {
        return $result[$md5_args];
    }

    $additional_attributes = array();

    if (!empty($active_modules["Extra_Fields"])) {
        if ($get_ids) {
            $additional_attributes = func_query_column("
                SELECT fieldid 
                FROM $sql_tbl[extra_fields] WHERE service_name IN ('" . implode("','", $froogle_additional_attributes) . "')
                ORDER BY orderby; 
            ");
        } else {
            $additional_attributes = func_query_hash("
                SELECT DISTINCT service_name, value 
                FROM $sql_tbl[extra_fields] WHERE service_name IN ('" . implode("','", $froogle_additional_attributes) . "')
                ORDER BY orderby", "service_name", false, true);
        }    
    }

    $result[$md5_args] = $additional_attributes;
    assert('is_array($additional_attributes) /*return '.__FUNCTION__.'*/');
    return $additional_attributes;
}

/**
 * Get additional attributes for product
 */
function func_froogle_get_product_additional_attributes($pid, $delim="\t")
{
    global $sql_tbl, $active_modules;

    $product_additional_attributes = array();

    if (empty($pid))
        return $product_additional_attributes;

    $additional_attributes = func_froogle_get_additional_attributes();
    if (!empty($active_modules["Extra_Fields"]) && !empty($additional_attributes)) {

        $additional_attributes_ids = func_froogle_get_additional_attributes('get_ids');
        $additional_attributes_cond = "'".implode("','",$additional_attributes_ids)."'";

        $product_additional_attributes = func_query_hash("
        SELECT $sql_tbl[extra_fields].service_name, $sql_tbl[extra_field_values].value
          FROM $sql_tbl[extra_fields]
         INNER JOIN $sql_tbl[extra_field_values] USING(fieldid)
        WHERE
           $sql_tbl[extra_field_values].fieldid IN ($additional_attributes_cond)
           AND $sql_tbl[extra_field_values].productid = '$pid'", "service_name", false, true);

        $product_additional_attributes = array_merge($additional_attributes, $product_additional_attributes);

        assert('count($additional_attributes) == count($product_additional_attributes)');
    }

    return $product_additional_attributes;
}

/**
 * Override xcart_taxes.price_includes_tax display_including_tax tax options for each tax from Froogle module settings
 */
function func_froogle_get_tax_options() { //{{{
    global $config;

    return func_tax_get_override_display_including_tax($config['Froogle']['froogle_display_including_tax']);
} //}}}

?>
