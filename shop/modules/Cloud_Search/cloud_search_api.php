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
 * Cloud Search API implementation
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cloud Search
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v7 (xcart_4_7_0), 2015-02-17 23:56:28, cloud_search_api.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header('Location: ../../'); die('Access denied');}

if ($_GET['method'] == 'get_prices' && !empty($_GET['ids'])) {
    func_cloud_search_output_json(func_cloud_search_get_prices($_GET['ids']));
}

if (in_array($_GET['method'], array('products', 'categories', 'pages', 'manufacturers', 'set_secret_key'))) {
	$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
	$limit = !empty($_GET['limit']) ? intval($_GET['limit']) : func_cloud_search_entities_at_once();

	$limit = max(min($limit, func_cloud_search_entities_at_once()), 0);

    $data = array();

    if ('products' === $_GET['method'])
        $data = func_cloud_search_get_products($start, $limit);
    elseif ('categories' === $_GET['method'])
        $data = func_cloud_search_get_categories($start, $limit);
    elseif ('pages' === $_GET['method'])
        $data = func_cloud_search_get_pages($start, $limit);
    elseif ('manufacturers' === $_GET['method'])
        $data = func_cloud_search_get_manufacturers($start, $limit);
    elseif ('set_secret_key' === $_GET['method'])
        $data = func_cloud_search_set_secret_key();

	func_cloud_search_api_output($data);
}

if ('info' === $_GET['method']) {
	func_cloud_search_api_output(func_cloud_search_get_info());
}

if ('install' === $_GET['method']) {
	if (!empty($_GET['key']) && $_GET['key'] === $config['General']['cron_key']) {
		$result = func_cloud_search_install();

		header('Content-type: text/plain');
		print_r($result);
	}
}
