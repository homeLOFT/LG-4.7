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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v10 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

/**
 * Get proper language code for the Facebook js-library including
 */

function func_get_facebook_lang_code($store_language) {

    global $sql_tbl, $fb_langs;

    $store_language_name = func_query_first_cell("SELECT language FROM $sql_tbl[language_codes] WHERE code = '$store_language'");

    return !empty($fb_langs[$store_language_name]) ? $fb_langs[$store_language_name] : 'en_US';
}

function func_make_clean_url($url) {

    global $config, $current_location;

    if (!is_url($url)) {
        
        if ($config['SEO']['clean_urls_enabled'] == 'Y') {

            $url = func_clean_url_get('P', $url);
            
        } else {

            $url = $current_location . '/product.php?productid=' . $url;

        }

    }

    return $url;
}

function func_soc_want_more_drawings($tpl, &$smarty) {

    global $want_more_content;

    $tpl = preg_replace('/<div id="want-more" style="margin: -15px 0;">/', '<div id="want-more" style="margin: -15px 0;">' . $want_more_content, $tpl);

    return $tpl;
}

function func_soc_init() { //{{{

    global $smarty, $HTTPS, $HTTP_USER_AGENT, $option, $ajax_mode, $sql_tbl, $want_more_content;

    $smarty->register_modifier('func_get_facebook_lang_code', 'func_get_facebook_lang_code');
    $smarty->register_modifier('func_make_clean_url', 'func_make_clean_url');

    /*
      HTTP(S) detection
     */
    $smarty->assign('current_protocol', ($HTTPS) ? 'https' : 'http');

    /*
      W3C Validator detection
     */
    $smarty->assign('is_w3c_validator', strpos($HTTP_USER_AGENT, 'W3C_Validator') !== false);

    /*
      "Want more?" message drawing
     */

    if (func_constant('AREA_TYPE') == 'A' && !empty($option) && $option == 'Socialize') {

        if (!empty($ajax_mode)) {

            switch ($ajax_mode) {
                case 'close':
                    db_query("UPDATE $sql_tbl[config] SET value = 'none' WHERE name = 'soc_want_more_box'");
                    break;
                case 'open':
                    db_query("UPDATE $sql_tbl[config] SET value = 'block' WHERE name = 'soc_want_more_box'");
                    break;
            }

            exit();
        }

        $smarty->assign('want_more_box_mode', func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name = 'soc_want_more_box'"));

        $want_more_content = func_display('modules/Socialize/want_more.tpl', $smarty, false);

        $smarty->register_outputfilter('func_soc_want_more_drawings');
    }

} //}}}

?>
