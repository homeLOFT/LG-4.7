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
 * Module functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v11 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

/**
 * Returns true if Lexity is configured and can be used, assign several variables into Smarty 
 */
function func_is_lexity_enabled() {

    global $config, $smarty, $xcart_catalogs, $lexity_partner_code;

    $is_enabled = is_object($smarty) && !defined('IS_ROBOT') && !defined('QUICK_START');

    if ($is_enabled) {
        $smarty->assign('lexity_partner_code', $lexity_partner_code);
        $smarty->assign(
            'lexity_merchant_id',
            !empty($config['Lexity_hidden']['lexity_merchant_id'])
                ? $config['Lexity_hidden']['lexity_merchant_id']
                : func_lexity_generate_merchant_id()
            );

        if (func_constant('AREA_TYPE') == 'A') {
            $smarty->assign('lexity_render_hash', func_lexity_get_render_hash());
            $smarty->assign('lexity_email', urlencode($config['Lexity']['lexity_email']));
            $smarty->assign('lexity_store_url', urlencode($xcart_catalogs['customer']));

        } else {
            $smarty->assign('lexity_embed_hash', func_lexity_get_embed_hash());
        }
    }

    return $is_enabled;
}

/**
 * Generates random value for merchant identification and save this in the configuration
 */
function func_lexity_generate_merchant_id() {

    global $sql_tbl;

    $merchant_id = substr(md5(XC_TIME . mt_rand(0, XC_TIME)), 8, 8);

    func_array2insert(
        $sql_tbl['config'],
        array(
            'name' => 'lexity_merchant_id',
            'category' => 'Lexity_hidden',
            'value' => $merchant_id,
        ),
        true
    );

    return $merchant_id;
}

/**
 * Generates and returns value for 'embed_hash'
 */
function func_lexity_get_embed_hash() {

    global $config, $lexity_shared_key;
    
    return md5('e' . $config['Lexity_hidden']['lexity_merchant_id'] . $lexity_shared_key);
}

/**
 * Generates and returns value for 'render_hash'
 */
function func_lexity_get_render_hash() {

    global $config, $lexity_shared_key;
    
    return md5('r' . $config['Lexity_hidden']['lexity_merchant_id'] . $lexity_shared_key);
}

/**
 * Add menu to Lexity page on home page
 */
function func_lexity_update_dialog_tools() {

    global $dialog_tools_data;

    $dialog_tools_data['right'][] = array(
        'link'  => 'lexity.php',
        'title' => func_get_langvar_by_name('lbl_lexity_menu'),
    );
}

function func_lexity_init() { //{{{

    global $smarty;

    $smarty->assign('is_lexity_enabled', func_is_lexity_enabled());

} //}}}

?>
