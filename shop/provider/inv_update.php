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
 * Inventory update interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Provder interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    d10493edd2a9037b90dc5bfc183fafe13b096151, v66 (xcart_4_7_2), 2015-04-20 10:59:49, inv_update.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('files');

func_set_time_limit(1800);

$location[] = array(func_get_langvar_by_name('lbl_update_inventory'), '');

if ($REQUEST_METHOD=="POST") {

    $provider_condition= ($single_mode ? '' : " AND $sql_tbl[products].provider='$logged_userid'");

    // Check post_max_size exceeding

    func_check_uploaded_files_sizes('userfile', 522);

    $userfile = func_move_uploaded_file('userfile', 522);
    $pids = $err_rows = array();
    $updated = 0;

    if ($fp = func_fopen($userfile,'r',true)) {

        while ($columns = fgetcsv ($fp, 65536, $delimiter)) {

            if (empty($columns[0])) {
                continue;
            }

            $orig_row = strip_tags(htmlentities(implode($delimiter, $columns)));

            if (count($columns) < 2) {
                $err_rows[] = $orig_row;
                continue;
            }

            $columns[0] = addslashes($columns[0]);

            $pid = func_query_first_cell ("SELECT productid FROM $sql_tbl[products] WHERE (productcode='$columns[0]' OR productid = '$columns[0]') $provider_condition");
            $vid = 0;
            if (!empty($active_modules['Product_Options'])) {
                if (!empty($provider_condition)) {
                    $join_product_query = " INNER JOIN $sql_tbl[products] ON $sql_tbl[products].productid=$sql_tbl[variants].productid";
                } else {
                    $join_product_query = '';
                }
                $vid = func_query_first_cell("SELECT $sql_tbl[variants].variantid FROM $sql_tbl[variants] $join_product_query WHERE $sql_tbl[variants].productcode='$columns[0]' AND ".XCVariantsSQL::isVariantRow().$provider_condition);
            }

            if (empty($pid) && empty($vid)) {
                continue;
            }

            if (!empty($pid)) {
                $pids[] = $pid;
            } else {
                $pids[] = XCVariantsSQL::getProductidByVariantid($vid);
            }

            if ($what == 'p') {

                // Check price value
                if (!func_is_price($columns[1])) {
                    $err_rows[] = $orig_row;
                    continue;
                } else {
                    $columns[1] = func_detect_price($columns[1]);
                }

                if (strlen($columns[2]) == 0 || $columns[2] < 1) {
                    $columns[2] = 1;
                }

                $membershipid = func_detect_membership(trim($columns[3]));
                if (!empty($pid)) {
                    db_query ("UPDATE $sql_tbl[pricing] SET price='".$columns[1]."' WHERE productid='$pid' AND quantity='".(int)$columns[2]."' AND membershipid = '$membershipid' AND variantid = '0'");
                }
                if (!empty($vid)) {
                    db_query ("UPDATE $sql_tbl[pricing] SET price='".$columns[1]."' WHERE quantity='".(int)$columns[2]."' AND membershipid = '$membershipid' AND variantid = '$vid'");
                }

                // Notify customers about a price drops if needed
                if (!empty($active_modules['Product_Notifications'])) {
                    func_product_notifications_trigger($pid, $vid, 'P');
                }

            } else {

                $columns[1] = abs(intval($columns[1]));

                if (empty($pid))
                    $_productid = XCVariantsSQL::getProductidByVariantid($vid);
                else
                    $_productid = $pid;
                if (!empty($pid)) {
                    db_query ("UPDATE $sql_tbl[products] SET avail='".(int)$columns[1]."' WHERE productid='$pid' $provider_condition");
                // Do not run XCVariantsChange::update in favour of RepairIntegrity below
                }
                if (!empty($vid)) {
                    $var_changes = array('avail' => "'" . (int)$columns[1] . "'");
                    XCVariantsChange::update($_productid, $vid, $var_changes);
                }

                // Notify customers about a price drops if needed
                if (!empty($active_modules['Product_Notifications'])) {
                    func_product_notifications_trigger($pid, $vid, 'B');
                    func_product_notifications_trigger($pid, $vid, 'L');
                }
            }

            $updated++;
        }

        $smarty->assign('main', 'inv_updated');
        $smarty->assign('updated_items', $updated);

        // Display rows with invalid formats for provider (no more then 200 rows on page)

        if (!empty($err_rows)) {
            $smarty->assign('err_rows', array_slice($err_rows, 0, 200));
        }

        if (!empty($pids)) {
            func_build_quick_flags($pids);
            func_build_quick_prices($pids);
        }

        fclose($fp);

    } else {

        $smarty->assign('main', 'error_inv_update');
    }
    @unlink($userfile);

} else {

    $smarty->assign ('main', 'inv_update');
}

$smarty->assign('upload_max_filesize', func_convert_to_megabyte(func_upload_max_filesize()));

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$dialog_tools_data = array('help' => true);
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('provider/home.tpl',$smarty);
?>
