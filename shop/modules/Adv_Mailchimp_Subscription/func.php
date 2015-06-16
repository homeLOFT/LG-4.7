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
 * Functions for the Mailchimp subscription 
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

if (!defined('XCART_START')) {
    header('Location: ../');
    die('Access denied');
}

/**
 * Subscription wrapper for Mailchimp service  (listSubscribe method)
 *
 * @param string $email_address E-mail
 * @param mixed  $listid        id of Mailchimp account
 * @param mixed  $apikey        apikey of Mailchimp account
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_mailchimp_get_lists($listid = false, $apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_merge_vars = array('');

    $mailchimp_response = array();

    $mailchimp_return = $mailchimp_api->lists($mailchimp_merge_vars, 0, 25);
    if ($mailchimp_api->errorCode) {
        $mailchimp_return['Error_code']    = $mailchimp_api->errorCode;
        $mailchimp_return['Error_message'] = $mailchimp_api->errorMessage;
    }

    return $mailchimp_return; 
}

function func_mailchimp_get_campaigns($listid = false, $apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_merge_vars = array('');

    $mailchimp_response = array();

    $mailchimp_return = $mailchimp_api->campaigns($mailchimp_merge_vars, 0, 25);
    if ($mailchimp_api->errorCode) {
    }

    return $mailchimp_return; 
}

function func_mailchimp_get_list_by_email($email, $apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }
    
    $mailchimp_api = new MCAPI($apikey);
    $mailchimp_return = array();

    if (!empty($email)){
        $mailchimp_return = $mailchimp_api->listsForEmail($email);
    }

    if ($mailchimp_api->errorCode) {
        $mailchimp_response['Error_code']    = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message'] = $mailchimp_api->errorMessage;
        $mailchimp_return = array();
    }
        
    return $mailchimp_return;  
}
 
function func_adv_mailchimp_subscribe($email_address, $user_info, $listid = false,  $apikey = false, $register_opt)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_merge_vars = $user_info;

    $mailchimp_response = array();

    $mailchimp_return = $mailchimp_api->listSubscribe(
        $listid,
        $email_address,
        $mailchimp_merge_vars,
        'html',
        $register_opt,
        true
    );

    if ($mailchimp_api->errorCode) {
        $mailchimp_response['Error_code']    = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message'] = $mailchimp_api->errorMessage;

    } else {
        $mailchimp_response['Response'] = $mailchimp_return;
    }

    return $mailchimp_response;
}

function func_mailchimp_update($email_address, $listid, $mailchimp_updates, $apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_merge_vars = $mailchimp_updates;
    $mailchimp_response = array();

    $mailchimp_return = $mailchimp_api->listUpdateMember($listid, $email_address, $mailchimp_merge_vars);

    if ($mailchimp_api->errorCode) {

        $mailchimp_response['Error_code']    = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message'] = $mailchimp_api->errorMessage;

    } else {
        $mailchimp_response['Response'] = $mailchimp_return;
    }

    return $mailchimp_response;
}

function func_mailchimp_unsubscribe($email_address, $listid = false, $apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_merge_vars = array('');
    $mailchimp_response = array();

    $mailchimp_return = $mailchimp_api->listUnsubscribe( $listid, $email_address, $mailchimp_merge_vars);

    if ($mailchimp_api->errorCode) {

        $mailchimp_response['Error_code']    = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message'] = $mailchimp_api->errorMessage;

    } else {
        $mailchimp_response['Response'] = $mailchimp_return;
    }

    return $mailchimp_response;
}

function func_mailchimp_campaign_ecomm_add_order($cm_order,$email,$apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Adv_Mailchimp_Subscription']['adv_mailchimp_apikey'];
    }

    $mailchimp_api = new MCAPI($apikey);
    $mailchimp_merge_vars = array('campaign_id'=>$cm_order['campaign_id']);
    $mailchimp_return = $mailchimp_api->campaigns($mailchimp_merge_vars,0,25);
    $list_id = $mailchimp_return['data'][0]['list_id'];
    $tmp = $mailchimp_api->listMemberInfo($list_id, $email);
    $cm_order['email_id'] = $tmp['data'][0]['id'];
    $mailchimp_response = array();
    $mailchimp_return = $mailchimp_api->campaignEcommOrderAdd($cm_order);

    if ($mailchimp_api->errorCode) {

        $mailchimp_response['Error_code']    = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message'] = $mailchimp_api->errorMessage;

    } else {
        $mailchimp_response['Response'] = $mailchimp_return;
    }

    return $mailchimp_response;
}

function func_mailchimp_adv_campaign_commission($orderid)
{
global $mailchimp_campaignid, $config;
if (
    $config['Adv_Mailchimp_Subscription']['adv_mailchimp_analytics']
    && $mailchimp_campaignid
   ) {
    x_load('order');
    $order = func_order_data($orderid);

    $cm_order = array(
        'id'          => $order['order']['orderid'],
        'campaign_id' => $mailchimp_campaignid,
        'email_id'    => '123456',
        'total'       => $order['order']['total'],
        'order_date'  => $order['order']['date'],
        'shipping'    => $order['order']['shipping_cost'],
        'tax'         => $order['order']['tax'],
        'store_id'    => '1111111',
        'store_name'  => 'xcart',
        'plugin_id'   => '1214',
        'items'       => array(),
    );

    foreach ($order['products'] as $pr) {
        $cm_order['items'][] = array(
            'line_num'      => '',
            'product_id'    => $pr['productid'],
            'product_name'  => $pr['product'],
            'category_id'   => 1,
            'category_name' => $pr['product'],
            'qty'           => $pr['amount'],
            'cost'          => $pr['price'],
        );
    }
    return func_mailchimp_campaign_ecomm_add_order($cm_order, $order['order']['email']);
    }
}

function func_mailchimp_batch_subscribe($userinfo)
{
    global $shop_language, $mailchimp_subscription, $sql_tbl, $mc_newslists;

    if ( $userinfo['email'] && $mailchimp_subscription) {

        $mailchimp_user_info = array(
            'FName' => $userinfo['firstname'],
            'LName' => $userinfo['lastname'],
            'email' => $userinfo['email'],
            'phone' => $userinfo['b_phone'],
            'website' => $userinfo['url'],
            'address' => array(
                           'addr1'   => $userinfo['b_address'], 
                           'city'    => $userinfo['b_city'],
                           'state'   => $userinfo['b_state'],
                           'zip'     => $userinfo['b_zipcode'],
                           'country' => $userinfo['b_country'] 
                         )
        ); 
        foreach ($mailchimp_subscription as $key => $id) {
            func_adv_mailchimp_subscribe(
                $userinfo['email'],
                $mailchimp_user_info,
                $key,
                false,
                true
            ); 
        }
    }
}

function func_mailchimp_resubscribe()
{
    global $sql_tbl, $shop_language, $config;
    global $firstname, $lastname, $url, $email;
    global $old_userinfo,$mailchimp_subscription,$mc_newslists;

    $mc_newslists = func_query("SELECT mc_list_id FROM $sql_tbl[mailchimp_newslists] WHERE avail='Y'");

    if (!empty($mc_newslists) && !func_is_ajax_request() && $email) {
        $mailchimp_user_info = array(
            'FName' => $firstname,
            'LName' => $lastname,
            'phone' => $old_userinfo['b_phone'],
            'website' => $old_userinfo['url'],
            'address' => array(
                           'addr1'   => $old_userinfo['b_address'], 
                           'city'    => $old_userinfo['b_city'],
                           'state'   => $old_userinfo['b_state'],
                           'zip'     => $old_userinfo['b_zipcode'],
                           'country' => $old_userinfo['b_country'] 
                         )
          
        ); 
        $mc_nls = array();
        foreach($mc_newslists as $mc_lt){
            $mc_nls[] = $mc_lt['mc_list_id'];
        }
             
        $mailchimp_cur_subs = array();
        $mailchimp_cur_subs = func_mailchimp_get_list_by_email($old_userinfo['email']);
        $mailchimp_cur_subs = array_intersect($mailchimp_cur_subs,$mc_nls);
        $mailchimp_ext_subs = array();
        $mailchimp_ext_subs = func_mailchimp_get_list_by_email($email);
        $mailchimp_ext_subs = array_intersect($mailchimp_ext_subs,$mc_nls);

        $mailchimp_subs_keys = array();
        if (is_array($mailchimp_subscription)) {
            $mailchimp_subs_keys = array_keys($mailchimp_subscription);
        }

        $mailchimp_delid = array_diff($mailchimp_cur_subs, $mailchimp_subs_keys);
        $mailchimp_insid = array_diff($mailchimp_subs_keys, $mailchimp_cur_subs,$mailchimp_ext_subs);
        $mailchimp_updid = array_intersect($mailchimp_cur_subs, $mailchimp_subs_keys);
        $mailchimp_updid = array_diff($mailchimp_updid, $mailchimp_ext_subs);

        foreach ($mailchimp_delid as $id) {
            $mailchimp_response = func_mailchimp_unsubscribe($old_userinfo['email'], $id); 
        }

        if (
            count($mailchimp_updid) > 0
            && ($old_userinfo['email'] != stripslashes($email) || $old_userinfo['firstname'] != $firstname  )
        ) {
            foreach ($mailchimp_updid as $id) {
                func_mailchimp_update(
                    $old_userinfo['email'],
                    $id,
                    array(
                        'EMAIL' => $email,
                        'FName' => $firstname,
                        'LName' => $lastname,  
                    )
                );
            }
        }
        if ($config['Adv_Mailchimp_Subscription']['adv_mailchimp_register_opt'] == 'Y') {
          $reg_opt = true;
        }
        else {
          $reg_opt = false;
        }
        foreach ($mailchimp_insid as $id) {
            $mailchimp_response = func_adv_mailchimp_subscribe($email, $mailchimp_user_info, $id, false,  $reg_opt); 
        }
    }
}
         
function func_mailchimp_new_adv_campaign_commission(){

    global $mailchimp_campaignid;

    x_session_register ('mailchimp_campaignid');


    if (!empty($_GET['mc_cid'])) {
            $mailchimp_campaignid = $_GET['mc_cid'];
            $thirty_days = 60 * 60 * 24 * 30;
            $expiry = mktime(0, 0, 0, date('m'), date('d'), date('Y') + 1);
            func_setcookie('mailchimp_campaignid', $mailchimp_campaignid, XC_TIME + $thirty_days);
            func_setcookie('mailchimp_campaignid_time', XC_TIME + $thirty_days, XC_TIME + $thirty_days);
    }
}

function func_mailchimp_save_subscription($mailchimp_subscription) {

    global $saved_userinfo, $user;

    if (is_array($mailchimp_subscription)) {
        $saved_userinfo[$user]['mailchimp_subscription'] = $mailchimp_subscription;
    }

}

function func_mailchimp_get_subscription($userinfo) {

    global $mailchimp_subscription;

    if (empty($userinfo['mailchimp_subscription'])) {
        $tmp_lists = func_mailchimp_get_list_by_email($userinfo['email']);
        if (is_array($tmp_lists)) {
            $mailchimp_subscription = array();
            foreach ($tmp_lists as $v) {
                $mailchimp_subscription[$v] = true;
            }
        }
   } else {
      $mailchimp_subscription = $userinfo['mailchimp_subscription'];
   }

}

function func_mailchimp_assign_to_smarty(){

    global $smarty, $sql_tbl, $mailchimp_subscription, $mc_newslists, $shop_language;

    if (isset($mailchimp_subscription)) {
         $smarty->assign('mailchimp_subscription', $mailchimp_subscription);
    }
    $mc_newslists = func_query("SELECT * FROM $sql_tbl[mailchimp_newslists] WHERE avail='Y' AND subscribe='Y' AND lngcode='$shop_language'");
    $smarty->assign('mc_newslists', $mc_newslists);
}

function func_mailchimp_unsubscribe_newslists($email){

    global $sql_tbl, $mc_newslists, $redirect_to;

    $mc_newslists = func_query("SELECT * FROM $sql_tbl[mailchimp_newslists] WHERE avail='Y'");
    if (count($mc_newslists) > 0) {
       foreach ($mc_newslists as $list){
           $mailchimp_response = func_mailchimp_unsubscribe($email, $list['mc_list_id']);
       }
   }
   func_header_location($redirect_to . '/home.php?mode=unsubscribed&email=' . urlencode(stripslashes($email)));
}
