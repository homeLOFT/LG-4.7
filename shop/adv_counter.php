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
 * Counter for Advertising campaigns
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v32 (xcart_4_7_0), 2015-02-17 13:29:01, adv_counter.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/top.inc.php';

define('DO_NOT_START_SESSION', 1);
define('QUICK_START', true);
define('SKIP_CHECK_REQUIREMENTS.PHP', true);
define('USE_SIMPLE_DB_INTERFACE', true);

require __DIR__.'/init.php';

if (func_query_first_cell("SELECT active FROM $sql_tbl[modules] WHERE module_name='XAffiliate'") != 'Y')
    exit;

$include_func = true;
include $xcart_dir . '/modules/XAffiliate/config.php';

if (!empty($change_banner_stats_mode)) {
    func_add_banner_view_statistic($partner, $bid, 'check_banner', @$productid, @$categoryid, @$manufacturerid);
}
elseif (!empty($campaignid)) {
    if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[partner_adv_campaigns] WHERE campaignid = '$campaignid'")) {
        $limit = 0;
        do {
            func_array2insert(
                'partner_adv_clicks',
                array(
                    'campaignid' => $campaignid,
                    'add_date'   => XC_TIME + $limit, //Try to insert the click
                ),
                false, // Use INSERT
                true // Use INSERT IGNORE
            );
            $limit++;
            $id = db_affected_rows();

            // Make an attempt to insert the click 4 times
        } while ($id != 1 && $limit < 4);
    }
}

header("Content-type: image/gif");

func_readfile($xcart_dir . $smarty_skin_dir . '/images/spacer.gif', true);

?>
