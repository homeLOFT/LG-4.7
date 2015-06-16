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
 * Configuration options processing.
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

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

if ($REQUEST_METHOD == 'POST') {

    if (
        empty($adv_mailchimp_apikey)
    ) {

    	$top_message['content'] = func_get_langvar_by_name("txt_please_enter_all_required_info");

        func_header_location("configuration.php?option=Adv_Mailchimp_Subscription");

    } else {
      
       $mailchimp_lists = func_mailchimp_get_lists(0, $adv_mailchimp_apikey);
       if (!empty($mailchimp_lists['Error_message'])) {
           $top_message['content'] = func_get_langvar_by_name(
                "txt_mailchimp_error_txt",
                array(
                    "error_txt" => $mailchimp_lists['Error_message'],
                )
            );
           func_header_location("configuration.php?option=Adv_Mailchimp_Subscription");

        } else {
        
        if ($mailchimp_lists) {
//            db_query("DELETE FROM $sql_tbl[mailchimp_newslists]");
//            foreach ($mailchimp_lists as $t) {
//               $lngcode = empty($edit_lng) ? $shop_language : $edit_lng;
//               db_query("INSERT INTO $sql_tbl[mailchimp_newslists] ( name, descr, show_as_news, avail , subscribe, lngcode, mc_list_id)
//              		VALUES ('".$t['name']."','".$t['name']."','N','Y','Y','".$lngcode."','".strval($t['id'])."')");
//            }
            $top_message['content'] = func_get_langvar_by_name('msg_adm_mailchimp_connection_configured');
       }
        
            func_array2update(
                'config',
                array(
                    'value' => $adv_mailchimp_apikey,
                ),
                "name = 'adv_mailchimp_apikey' AND category = 'Adv_Mailchimp_Subscription'"
            );
         
            func_array2update(
                'config',
                array(
                    'value' => $adv_mailchimp_analytics == 'on' ? 'Y' : '',
                ),
                "name = 'adv_mailchimp_analytics' AND category = 'Adv_Mailchimp_Subscription'"
            );
            
            func_array2update(
                'config',
                array(
                    'value' => $adv_mailchimp_register_opt == 'on' ? 'Y' : '',
                ),
                "name = 'adv_mailchimp_register_opt' AND category = 'Adv_Mailchimp_Subscription'"
            );
            
        }

    }

}

?>
