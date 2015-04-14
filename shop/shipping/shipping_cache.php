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

//2b39e63712da5477e1aaf5cfa80d1370f583bce9, v19 (xcart_4_7_0), 2015-02-17 23:56:28, shipping_cache.php, Yuriy

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

function func_is_shipping_result_in_cache($md5_str)
{
    global $sql_tbl, $XCARTSESSID;

    return func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping_cache] WHERE md5_request='$md5_str' AND sessid='$XCARTSESSID'") > 0;
}

function func_save_shipping_result_to_cache($md5_str, $result, $init_result = array())
{
    global $sql_tbl, $XCARTSESSID;

    if (!empty($init_result) && is_array($init_result)) {
        foreach (array_keys($init_result) as $k)
            func_unset($result, $k);
    }

    if (empty($result))
        return;

    func_array2insert('shipping_cache', array('md5_request' => $md5_str, 'sessid' => $XCARTSESSID, 'response' => addslashes(serialize($result))), true);
}

function func_get_shipping_result_from_cache($md5_str, $result = array())
{
    global $sql_tbl, $XCARTSESSID;

    if (!is_array($result))
        $result = array();

    $return = unserialize(func_query_first_cell("SELECT response FROM $sql_tbl[shipping_cache] WHERE sessid='$XCARTSESSID' AND md5_request='$md5_str'"));
    if (empty($result))
        return $return;

    if (!empty($return) && is_array($return)) {
        foreach ($return as $v)
            $result[] = $v;
    }

    return $result;
}
?>
