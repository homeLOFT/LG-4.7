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
 * Page generation script for catalog
 *
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @category   X-Cart
 * @package    Modules
 * @subpackage Products Map
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v12 (xcart_4_7_0), 2015-02-17 23:56:28, html_catalog.php, Yuriy
 * @since      4.4.0
 */
if (!defined('XCART_START')) { header('Location: ../../'); die('Access denied');}

$GLOBALS['pmap_generation_flag'] = true;

foreach ($hc_state['catalog_dirs'] as $catdir) {

    $hc_state['catalog'] = $catdir;

    $current_lng = $catdir['code'];

    $pmap_symbols = pmap_get_symbols();

    // process each symbol page in the pmap
    foreach ($pmap_symbols as $pmap_symbol => $pmap_display) {
 
        // get source code of first page
        $pmap_page_params = $additional_hc_data_current['page_params'] . $pmap_symbol;

        list($http_headers, $page_src) = func_fetch_page(
            $site_location['host'] . $site_location['port_str'],
            $site_location['path'] . DIR_CUSTOMER . '/' . $additional_hc_data_current['page_url'],
            'sl=' . $catdir['code'] . $shop_closed_var . '&' . $pmap_page_params,
            $robot_cookies
        );

        // generate hc file for first page
        $pmap_name_func_params = sprintf($additional_hc_data_current['name_func_params'][0], $pmap_symbol, 1);

        convert_page(
            $catdir['path'],
            $page_src,
            $additional_hc_data_current['name_func'],
            array($pmap_name_func_params)
        );

        // find additonal pages
        $pmap_url = $additional_hc_data_current['page_url'] . '?' . $pmap_page_params;
        $pmap_pattern = '/' . preg_quote($pmap_url) . "&[^\"']*page=(\d+)[&\"']/S";

        if (preg_match_all($pmap_pattern, $page_src, $pmap_matches)) {

            $pmap_add_pages = array_unique($pmap_matches[1]);

        } else {

            continue;

        }

        // generate hc files for additional founded pages
        foreach ($pmap_add_pages as $pmap_add_page) {

            if ($pmap_add_page != 1) {

                list($http_headers, $page_src) = func_fetch_page(
                    $site_location['host'] . $site_location['port_str'],
                    $site_location['path'] . DIR_CUSTOMER . '/' . $additional_hc_data_current['page_url'],
                    'sl=' . $catdir['code'] . $shop_closed_var . '&' . $pmap_page_params . '&page=' . $pmap_add_page,
                    $robot_cookies
                );

                $pmap_name_func_params = sprintf($additional_hc_data_current['name_func_params'][0], $pmap_symbol, $pmap_add_page);

                convert_page(
                    $catdir['path'],
                    $page_src,
                    $additional_hc_data_current['name_func'],
                    array($pmap_name_func_params)
                );

            }

        }

    }

}

unset(
    $pmap_symbols, 
    $pmap_symbol, 
    $pmap_display, 
    $pmap_page_params, 
    $pmap_url, 
    $pmap_pattern, 
    $pmap_add_pages, 
    $pmap_matches, 
    $pmap_add_page, 
    $pmap_name_func_params
);

unset(
    $GLOBALS['pmap_generation_flag']
);

?>
