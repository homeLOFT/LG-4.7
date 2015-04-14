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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v19 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Subscription wrapper for Mailchimp service  (listGrowthHistory method)
 *
 * @param mixed $listid id of Mailchimp accout
 * @param mixed $apikey apikey of Mailchimp accout
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_mailchimp_list_history($listid = false, $apikey = false)
{
    global $config;

    if (!$apikey) {
        $apikey = $config['Mailchimp_Subscription']['mailchimp_apikey'];
    }

    if (!$listid) {
        $listid = $config['Mailchimp_Subscription']['mailchimp_id'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_return = $mailchimp_api->listGrowthHistory($listid);

    if ($mailchimp_api->errorCode) {

        $mailchimp_response['Error_code']         = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message']     = $mailchimp_api->errorMessage;

    } else {

        $mailchimp_response['Response']         = $mailchimp_return;

    }

    return $mailchimp_response;
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
function func_mailchimp_subscribe($userinfo, $listid = false, $apikey = false)
{
    global $config;

    if (false === $apikey) {
        $apikey = $config['Mailchimp_Subscription']['mailchimp_apikey'];
    }

    if (false === $listid) {
        $listid = $config['Mailchimp_Subscription']['mailchimp_id'];
    }

    $mailchimp_api = new MCAPI($apikey);

    $mailchimp_merge_vars = array('');

    $mailchimp_merge_vars = array(
        'FName' => isset($userinfo['firstname']) ? $userinfo['firstname'] : 
            (isset($userinfo['b_firstname']) ? $userinfo['b_firstname'] :
            (isset($userinfo['s_firstname']) ? $userinfo['s_firstname'] : '')),
        'LName' => isset($userinfo['lastname']) ? $userinfo['lastname'] :
            (isset($userinfo['b_lastname']) ? $userinfo['b_lastname'] :
            (isset($userinfo['s_lastname']) ? $userinfo['s_lastname'] : '')),
        'email' => $userinfo['email'],
        'phone' => $userinfo['phone'],
        'website' => $userinfo['url'],
        'address' => array(
                       'addr1'   => $userinfo['b_address'],
                       'city'    => $userinfo['b_city'],
                       'state'   => $userinfo['b_state'],
                       'zip'     => $userinfo['b_zipcode'],
                       'country' => $userinfo['b_country']
                     )

    );

    $mailchimp_return = $mailchimp_api->listSubscribe($listid, $userinfo['email'], $mailchimp_merge_vars);

    if ($mailchimp_api->errorCode) {

        $mailchimp_response['Error_code']         = $mailchimp_api->errorCode;
        $mailchimp_response['Error_message']     = $mailchimp_api->errorMessage;

    } else {
        $mailchimp_response['Response']         = $mailchimp_return;
    }

    return $mailchimp_response;
}

?>
