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
 * Shop registration
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v34 (xcart_4_7_0), 2015-02-17 13:29:01, shop_registration.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require_once __DIR__.'/top.inc.php';

require __DIR__.'/init.php';

require $xcart_dir . '/include/safe_mode.php';

$params = array(
    'license_url',
    '_shop_type',
    'UPS_devlicense',
);

foreach ($params as $var) {

    if (isset($_POST[$var])) {

        $_POST[$var] = (string)$_POST[$var];

        $GLOBALS[$var] = (string)$GLOBALS[$var];

    }

}

// Wrong POST request
if (
    $REQUEST_METHOD != 'POST'
    || empty($_POST['license_url'])
    || empty($_POST['_shop_type'])
) {
    func_header_location('home.php');
}

if (strtoupper($_POST['_shop_type']) != strtoupper($shop_type)) {

    echo 'ERROR(1): Wrong license type (X-Cart ' . $shop_type . ' license required to register this installation)';

    exit;

}

if (
    !in_array(
        preg_replace(
            '%^http[s]{0,1}://(www\.)*%i',
            '',
            rtrim(strtolower($_POST['license_url']), XC_WEB_DS) . XC_WEB_DS
        ),
        func_array_map('strtolower', func_get_aliases_list())
    )
) {

    echo 'ERROR(2): Wrong license url';

    exit;

}

$register_data = array(
    'license_url' => addslashes($_POST['license_url']),
);

if (
    !empty($_POST['UPS_devlicense'])
    && md5($_POST['UPS_devlicense']) == $md5_check_devlicense
) {
    $register_data['UPS_devlicense'] = addslashes($_POST['UPS_devlicense']);
}

$license_url = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='license_url'");

if (empty($license_url)) {
    func_array2update(
        'config',
        array(
            'value' => XC_TIME,
        ),
        "name='registration_date'"
    );
}

func_array2update(
    'config',
    array(
        'value' => $register_data['license_url'],
    ),
    "name='license_url'"
);

if (!empty($register_data['UPS_devlicense'])) {
    func_array2update(
        'config',
        array(
            'value' => $register_data['UPS_devlicense'],
        ),
        "name='UPS_devlicense'"
    );
}

$store_version = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='version'");
echo 'SUCCESS: Installation is registered (VERSION: ' . $store_version . ')';

exit;

?>
