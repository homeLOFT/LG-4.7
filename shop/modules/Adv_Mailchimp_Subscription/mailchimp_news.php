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
 * Process news related operations
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../"); die("Access denied"); }

x_load('files');

x_session_register('search_data');

if (!in_array($mode, array('create', 'update', 'modify', 'import', 'delete')))
    $mode = '';

$targetlist = is_numeric($targetlist) ? intval($targetlist) : '';

if ($REQUEST_METHOD == 'POST' || ($mode == 'messages' && $action == 'send_continue')) {

   if ($mode == 'update') {

        // Update news lists

        if (is_array($posted_data)) {
            foreach ($posted_data as $listid=>$v) {
                $avail = (empty($v['avail']) ? 'N' : 'Y');
                db_query("UPDATE $sql_tbl[mailchimp_newslists] SET show_as_news='$show_as_news', avail='$avail' WHERE listid='$listid'");
            }

            $top_message['content'] = func_get_langvar_by_name('msg_adm_newslists_upd');
        }
    }

   if ($mode == 'import') {
        // Import news lists from Mailchimp
     $mailchimp_lists = func_mailchimp_get_lists();
     
          if  ($mailchimp_lists['Error_message']){
          	$top_message['content'] = func_get_langvar_by_name('msg_adm_mailchimp_newslists_import_error');
          	
          } elseif (count($mailchimp_lists)==0) {
              	$top_message['content'] = func_get_langvar_by_name('msg_adm_mailchimp_newslists_no_lists');
     
          } else {
              $lngcode = empty($edit_lng) ? $shop_language : $edit_lng;
              db_query("DELETE FROM $sql_tbl[mailchimp_newslists] WHERE lngcode='$lngcode'"); 
              foreach ($mailchimp_lists['data'] as $t) {
                  db_query("INSERT INTO $sql_tbl[mailchimp_newslists] ( name, descr, show_as_news, avail , subscribe, lngcode, mc_list_id)
              		VALUES ('".$t['name']."','".$t['name']."','N','Y','Y','".$lngcode."','".strval($t['id'])."')");
             }
             $top_message['content'] = func_get_langvar_by_name('msg_adm_mailchimp_newslists_imported');
          }

        }
  
   if ($mode == 'modify' ) {

        // Create new newslist or edit newslist details

        if (is_array($list)) {
            $list['name'] = @trim($list['name']);
            $list['descr'] = @trim($list['descr']);
            $list = func_array_map('stripslashes',$list);

            $error = array();
            $err = false;
            foreach (array('name', 'descr') as $key) {
                $err = $err || ($error[$key] = empty($list[$key]));
            }

            if (!$err) {
                $list = func_array_map('addslashes',$list);
                $mode = '';
                $list_values = $list;
                func_unset($list_values,'listid');

                if (!empty($list['listid'])) {
                    func_array2update('mailchimp_newslists', $list_values, "listid='$list[listid]'");
                    $top_message['content'] = func_get_langvar_by_name('msg_adm_newslist_upd');
                }
                else {
                    $list_values['lngcode'] = empty($edit_lng) ? $shop_language : $edit_lng;
                    func_array2insert('mailchimp_newslists', $list_values);
                    $list['listid'] = db_insert_id();
                    $top_message['content'] = func_get_langvar_by_name('msg_adm_newslists_add');
                }
            }
            else {
                $top_message['content'] = func_get_langvar_by_name('err_filling_form');
                $top_message['type'] = 'E';
                x_session_register('nwslt_object');
                $nwslt_object['error'] = $error;
                $nwslt_object['list'] = $list;

                func_header_location("mailchimp_news.php?mode=$mode&targetlist=".$list['listid']);
            }
        }

        func_header_location("mailchimp_news.php?mode=modify&targetlist=".$list['listid']);
    }

    func_header_location('mailchimp_news.php');
}

/**
 * Process the GET request
 */

if (!empty($mode))
    $location[count($location)-1][1] = 'mailchimp_news.php';

if (!empty($targetlist)) {
    $list = func_query_first("SELECT * FROM $sql_tbl[mailchimp_newslists] WHERE listid='$targetlist'");

    if ($mode == 'modify')
        $location[] = array($list['name'], '');
    else
        $location[] = array($list['name'], "mailchimp_news.php?mode=modify&targetlist=$targetlist");

 }

if (!empty($list['listid'])) {
    if ($list['lngcode'] != $shop_language && is_array($d_langs) && !in_array($list['lngcode'], $d_langs)) {
        func_header_location("mailchimp_news.php?mode=modify&targetlist=$targetlist&edit_lng=$list[lngcode]&old_lng=$shop_language");
    }
}

if ($mode == 'modify') {

    // Get the news list details and display it

    if (empty($list)) {
        $top_message['content'] = func_get_langvar_by_name('msg_adm_err_newslist_not_exists');
        func_header_location('mailchimp_news.php');
    }

    $smarty->assign('list', $list);
}


if (x_session_is_registered('nwslt_object')) {
    x_session_register('nwslt_object');
    if (is_array($nwslt_object)) {
        foreach ($nwslt_object as $k=>$v)
            $smarty->assign($k, $v);
    }

    x_session_unregister('nwslt_object');
}

if (!empty($targetlist)) {
    $targetlistname = func_query_first_cell("SELECT name FROM $sql_tbl[mailchimp_newslists] WHERE listid='$targetlist'");
    $smarty->assign('targetlistname', $targetlistname);
    $smarty->assign('targetlist', $targetlist);
}

$smarty->assign('need_unsubscribe_section', func_query_first_cell("SELECT listid FROM $sql_tbl[newslist_subscription]"));$smarty->assign('not_empty_list', func_query_first_cell("SELECT listid FROM $sql_tbl[newslist_subscription] WHERE listid='$targetlist'"));

$lists = func_query("SELECT * FROM $sql_tbl[mailchimp_newslists] WHERE lngcode='$shop_language'");
$smarty->assign('lists', $lists);
$smarty->assign('action', $action);
$smarty->assign('mode', $mode);

?>
