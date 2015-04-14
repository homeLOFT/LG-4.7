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
 * Checkout by Amazon
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v48 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 *
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

function func_acheckout_debug($message, $xml=false)
{
    global $acheckout_global_log, $acheckout_global_xml_log;
    global $acheckout_log_detailed_data;

    if (!defined('ACHECKOUT_DEBUG') || empty($message))
        return true;
    
    if (!$xml) {
        $acheckout_global_log .= $message . "\n";
    } elseif ($acheckout_log_detailed_data) {
        if (func_is_latin1($message)) {
            $message = preg_replace('/^\s*/m', '', $message);
            // func_xml_format does not support multibyte encoding
            $message = func_xml_format($message, 2);
        }            
        $acheckout_global_xml_log .= $message . "\n";
    }        
    
    return true;
}

/**
 * Attention! Must be called from global scope
 */
function func_acheckout_restore_session_n_global($skey)
{
    global $cart, $sql_tbl, $user_account, $products;

    $sessid = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '$skey'");

    x_session_id($sessid);
    x_session_register('login');#nolint
    x_session_register('login_type');#nolint
    x_session_register('logged_userid');#nolint
    x_session_register('cart');#nolint
    x_session_register('user_tmp', array());#nolint
    x_session_register('current_carrier', 'UPS');#nolint

    // Do not initialize session vars from global. Use session values
    global $login, $login_type, $logged_userid, $cart, $user_tmp, $current_carrier;

    if (!empty($logged_userid)) {
        $user_account['membershipid'] = func_query_first_cell("SELECT membershipid FROM $sql_tbl[customers] WHERE id='$logged_userid'");
    }

    $products = func_amazon_products_in_cart($cart);

    $sess_result = !empty($cart);

    if (empty($sess_result)) {
        $saved_cart = func_query_first_cell("SELECT cart FROM $sql_tbl[amazon_data] WHERE ref='$skey'");
        if (!empty($saved_cart)) {
            $cart = unserialize($saved_cart);
        }       
    } 

    return $sess_result;
}

function func_amazon_sort_tax_rates($a, $b)
{
    if ($a['zone_rating'] == $b['zone_rating'])
        return 0;

    return ($a['zone_rating'] < $b['zone_rating'] ? 1 : -1);
}

function func_acheckout_save_log()
{
    global $acheckout_global_log, $acheckout_global_xml_log;
    global $acheckout_log_detailed_data;

    if (!defined('ACHECKOUT_DEBUG'))
        return true;

    if (!empty($acheckout_global_log)) {

        list($_usec, $_sec) = explode(" ", constant('XCART_START_TIME'));
        list($_usec2, $_sec2) = explode(" ", microtime());

        $acheckout_global_log .= "\t+ Running time (in seconds): " . (($_usec2 + $_sec2) - ($_usec + $_sec)) . "\n";

        if (ACHECKOUT_DEBUG != 1) {
            // Preparing for sending to e-mail
            $emails_array = explode(',', ACHECKOUT_DEBUG);
            x_log_add('acheckout', $acheckout_global_log, false, 0, $emails_array, true);
        }
        else
            x_log_add('acheckout', $acheckout_global_log);
    }

    if (!empty($acheckout_global_xml_log)) {
        x_log_add('acheckout_xml', $acheckout_global_xml_log);
    }

    return true;
}

function func_acheckout_wait_for_orders_from_callback($skey, $wait_time = 20)
{
    global $sql_tbl;

    $order_status = func_query_first_cell("SELECT param2 FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");

    if (empty($order_status)) {

        // Return before callback
        $counter = $wait_time;

        do {
            sleep(1);
            $order_status = func_query_first_cell("SELECT param2 FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
        } while (
            empty($order_status)
            && $counter-- > 0
        );

    }

    return true;
}

function func_amazon_detect_state($state, $country, $zipcode = '')
{
    global $sql_tbl;
    $state = trim($state);

    if ($s_code = func_query_first_cell("SELECT code FROM $sql_tbl[states] WHERE country_code='$country' AND (state='$state' OR code='$state')")) {
        return $s_code;
    } elseif ($country == 'GB') {

        x_load('user');
        if ($code = func_detect_state_by_zipcode($country, $zipcode)) {
            return $code;
        }
    }

    return 'Other';
}

/**
 * This function prepares string $str for including into the XML request
 */
function func_pre_encode_4XML($str)
{

    if (empty($str))
        return $str;

    $str = str_replace(array("&", "<", ">", chr(163), chr(174)), array("&#x26;", "&#x3c;", "&#x3e;", "&#xA3;", "&#xAE;"), $str);

    if (function_exists('mb_detect_encoding')) {
        global $e_langs;
        global $default_charset;
        static $_charsets = false;

        // Define static $_charsets var at once
        if (empty($_charsets)) {
            if (in_array(strtoupper($default_charset), array('KOI8-R','CP866','WINDOWS-1251','CP1251')))
                $_charsets = 'RU';
            else {
                $_charsets = $e_langs;
                array_unshift($_charsets, 'UTF-8');
                $_charsets = array_values(array_unique($_charsets));
            }
        }

        if ($_charsets == 'RU') {
            #use predefined charset for russian codepages.
            $charset = $default_charset;
        } else {
            #http://bugs.php.net/bug.php?id=38138 mb_detect_encoding can return false in some cases
            $charset = mb_detect_encoding($str, $_charsets);
        }

        if ($charset == 'UTF-8')
            return $str;
        elseif (empty($charset))
            return mb_convert_encoding($str, "UTF-8", "ISO-8859-1");
        else
            return mb_convert_encoding($str, "UTF-8", $charset);
    }

    return utf8_encode($str);
}

function func_amazon_encode($str, $limit=0)
{
    $str = func_pre_encode_4XML($str);

    if (!empty($limit)) {
        if (function_exists('mb_substr'))
            $str = mb_substr($str, 0, $limit, 'UTF-8');    
        else
            $str = substr($str, 0, $limit);
    }

    return $str;
}

function func_amazon_encode_cart($cart)
{
    global $config;

    $return = 'order:'.base64_encode($cart);
    if (!empty($config['Amazon_Checkout']['amazon_secret_key'])) {
        $signature = func_amazon_sign($cart);
        $return = 'type:merchant-signed-order/aws-accesskey/1;'.$return.';signature:'.$signature.';aws-access-key-id:'.$config['Amazon_Checkout']['amazon_access_key'];
    } else {
        $return = 'type:unsigned-order;'.$return;
    }

    return $return;
}


function func_amazon_get_checkout_data()
{
    global $config, $acheckout_saved_ips, $cart, $userinfo, $CLIENT_IP, $PROXY_IP;

    if (func_is_cart_empty($cart))
        return array();

    $checkout_data = array();

    x_load('xml');
    func_acheckout_debug('*** XML REQUEST PREPARE Ver2');
    $xml_Order = func_amazon_xml1_Order($cart, $userinfo);
    func_acheckout_debug("*** XML REQUEST IS READY Ver2:\n\n" . $xml_Order . "\n\n", true);
    $encoded_cart = func_amazon_encode_cart($xml_Order);


    //Save session data
    x_session_register('acheckout_saved_ips');
    $acheckout_saved_ips = array('ip' => $CLIENT_IP, 'proxy_ip' => $PROXY_IP);

    // Cannot be removed as called from func_display where x_session_finish is disabled
    x_session_save();

    $checkout_data['merchantId'] = $config['Amazon_Checkout']['amazon_mid'];
    $checkout_data['value'] = $encoded_cart;

    return $checkout_data;
}


function func_amazon_get_choosen_shipping3($cart, $products, $shipping_name, $amazon_shipping)
{
    // Get the shipping methods list with rates
    $_allowed_shipping_methods = func_get_shipping_methods_list($cart, $products, $cart['userinfo']);
    
    // Generate the full list of shipping methods for current address
    $shipping = array();
    if (!empty($_allowed_shipping_methods)) {
        foreach ($_allowed_shipping_methods as $_ship_method) {
            if (func_insert_trademark($_ship_method['shipping'], 'use_alt') == $shipping_name) {
                $shipping['shippingid'] = $_ship_method['shippingid'];
                $shipping['shipping'] = $_ship_method['shipping'];
                $shipping['rate'] = $_ship_method['rate'];
                break;
            }
        }
    }
    
    // Reserve way to resolve shipping 
    if (empty($shipping)) {
        $shipping = $cart['amazon_shippings'][$amazon_shipping];
    }

    return $shipping;
}

/**
 * Generate URL for the 'Merchant URL' field on the 'Checkout Pipeline Settings'->sellercentral.amazon.com
 */
function func_amazon_get_merchant_URL()
{
    global $https_location;

    return $https_location . '/payment/ps_amazon.php';
}

function func_amazon_get_order_details3($parsed, $amazon_products, $amazon_oid, $type = 'NEWORDERNOTIFICATION')
{
    $order_details = '';
    $order_details .= "AmazonOrderId: $amazon_oid\n\n";
    $order_details .= "OrderChannel: ".func_array_path($parsed, "$type/PROCESSEDORDER/ORDERCHANNEL/0/#")."\n\n";

    $order_details .= "Ordered products:\n\n";
    $a_total = 0;
    $a_total_shipping = 0;
    foreach ($amazon_products as $_prd) {
        $order_details .= "SKU: $_prd[productcode]\n";
        $order_details .= "AmazonOrderItemCode: ".func_array_path($_prd['raw_data'],"AMAZONORDERITEMCODE/0/#")."\n";
        $order_details .= "CartId: ".func_array_path($_prd['raw_data'],"CARTID/0/#")."\n";
        $order_details .= "\n";
    }

    return $order_details;
}

/**
 * Override xcart_taxes.price_includes_tax display_including_tax tax options for each tax from Amazon_Checkout module settings
 */
function func_amazon_get_tax_options() { //{{{
    global $config;
    x_load('taxes');
    return func_tax_get_override_display_including_tax($config['Amazon_Checkout']['amazon_display_including_tax']);

} //}}}

function func_amazon_get_taxes($cart)
{
    global $sql_tbl, $single_mode, $cart;
    global $user_account;

    $membershipid = intval($user_account['membershipid']);

    static $_zones_cache = array();
    static $result_cache = array();

    if (empty($cart['products']) || !is_array($cart['products']))
        return false;

    $products = $cart['products'];

    $md5_args = md5(serialize(array($products, $membershipid)));
    if (isset($result_cache[$md5_args])) {
        return $result_cache[$md5_args];
    }

    $productids = array();
    foreach ($products as $k => $p) {
        $productids[$p['productid']] = $p;
    }

    $_product_taxes = func_query_hash("SELECT $sql_tbl[taxes].*, $sql_tbl[product_taxes].productid FROM $sql_tbl[taxes], $sql_tbl[product_taxes], $sql_tbl[products] WHERE $sql_tbl[products].productid=$sql_tbl[product_taxes].productid AND $sql_tbl[taxes].taxid=$sql_tbl[product_taxes].taxid AND $sql_tbl[products].free_tax!='Y' AND $sql_tbl[product_taxes].productid IN ('".implode("','", array_keys($productids))."') AND $sql_tbl[taxes].active='Y' ORDER BY $sql_tbl[taxes].priority", "productid");

    if (empty($_product_taxes)) {
        $result_cache[$md5_args] = false;
        return false;
    }

    $taxes = array();
    $have_tax_rates = false;

    // This rating is used for ordering of the rates within tax-rules container
    $zone_element_rating = array(
        'C' => 1,
        'S' => 1000,
        'G' => 2000,
        'Z' => 3000,
        'A' => 4000
    );

    // Gather the tax rates details
    foreach ($_product_taxes as $productid => $_taxes) {

        if (isset($taxes[$_taxes[0]['tax_name']]))
            continue;

        $taxes[$_taxes[0]['tax_name']] = $_taxes[0];
        $rates = func_query("SELECT $sql_tbl[tax_rates].* FROM $sql_tbl[tax_rates] LEFT JOIN $sql_tbl[tax_rate_memberships] ON $sql_tbl[tax_rates].rateid=$sql_tbl[tax_rate_memberships].rateid WHERE $sql_tbl[tax_rates].taxid='{$_taxes[0]['taxid']}' AND ($sql_tbl[tax_rate_memberships].membershipid = '$membershipid' OR $sql_tbl[tax_rate_memberships].membershipid IS NULL)");

        $_tax_rates = array();

        if (is_array($rates)) {

            $have_tax_rates = true;
            $_tax_rate_tmp = array();

            // Gather the rate zones details
            $_total_rates = count($rates);

            for ($i = 0; $i < $_total_rates; $i++) {

                $_zone = array();
                $_zones = array();

                $zone_cache_key = $rates[$i]['zoneid'];

                if (isset($_zones_cache[$zone_cache_key])) {
                    // Get zone details from cache
                    $_zones = $_zones_cache[$zone_cache_key];
                }
                else {
                    // Gather zone details for tax rate
                    $_zones_result = func_query("SELECT $sql_tbl[zone_element].* FROM $sql_tbl[zone_element] WHERE zoneid='$zone_cache_key'");

                    if (!empty($_zones_result))
                        foreach ($_zones_result as $_current_zone)
                            $_zone['zone'][$_current_zone['field_type']][] = $_current_zone['field'];

                    $multiple_rate = array();

                    if (!empty($_zone['zone']['Z']) && count($_zone['zone']['Z']) > 1)
                        $multiple_rate['Z'] = $_zone['zone']['Z'];
                    elseif (!empty($_zone['zone']['S']) && count($_zone['zone']['S']) > 1)
                        $multiple_rate['S'] = $_zone['zone']['S'];

                    if (!empty($multiple_rate)) {
                        foreach ($multiple_rate as $k=>$_mzones) {
                            $_zone_tmp = $_zone;
                            foreach ($_mzones as $_mzone) {
                                $_zone_tmp['zone'][$k] = array($_mzone);
                                $_zones[] = $_zone_tmp;
                            }
                        }
                    }
                    else
                        $_zones[] = $_zone;

                    foreach ($_zones as $k=>$_zone) {
                        if (empty($_zone)) continue;
                        $_zones[$k]['zone_rating'] = 0;
                        foreach ($_zone['zone'] as $_zone_type=>$_zone_arr)
                            $_zones[$k]['zone_rating'] += $zone_element_rating[$_zone_type] * count($_zone_arr);
                    }

                    $_zones_cache[$zone_cache_key] = $_zones;

                }

                $_tmp_rate = array();
                $_tmp_rate = $rates[$i];

                foreach ($_zones as $_zone) {
                    $_tmp_rate['zone'] = @$_zone['zone'];
                    $_tmp_rate['zone_rating'] = @$_zone['zone_rating'];
                    $taxes[$_taxes[0]['tax_name']]['rates'][] = $_tmp_rate;
                }

            } // for ($i = 0; $i < $_total_rates; $i++)

            usort($taxes[$_taxes[0]['tax_name']]['rates'], 'func_amazon_sort_tax_rates');
        }
    }

    if (!$have_tax_rates) {
        $result_cache[$md5_args] = false;
        return false;
    }

    if (!$single_mode) {
        $taxes_pro = array();
        foreach ($taxes as $_tax_name => $_tax) {
            $_rates_tmp = array();
            foreach ($_tax['rates'] as $_rate)
                $_rates_tmp[$_tax_name.'_'.$_rate['provider']][] = $_rate;
            $taxes_pro[$_tax_name.'_'.$_rate['provider']] = $_tax;
            $taxes_pro[$_tax_name.'_'.$_rate['provider']]['rates'] = $_rates_tmp;
        }
        $taxes = $taxes_pro;
    }

    $result_cache[$md5_args] = $taxes;
    return $taxes;

}

function func_amazon_get_totals3($parsed, $amazon_products, $amazon_oid, $type = 'NEWORDERNOTIFICATION')
{
    $a_total = 0;
    $a_total_shipping = 0;
    foreach ($amazon_products as $_prd) {
        $charges = func_array_path($_prd['raw_data'],"ITEMCHARGES/COMPONENT");
        $p_total = 0;
        $p_total_shipping = 0;
        foreach ($charges as $charge) {
            $_type = func_array_path($charge,"TYPE/0/#");
            $_charge = func_array_path($charge,"CHARGE/AMOUNT/0/#");
            if ($_type != 'PrincipalPromo' && $_type != 'ShippingPromo') {
                $p_total += $_charge;
            } else {
                $p_total -= $_charge;
            }

            if ($_type == 'Shipping')
                $p_total_shipping += $_charge;
        }
        $a_total += $p_total;
        $a_total_shipping += $p_total_shipping;
    }

    return array('shipping_cost'=>$a_total_shipping, 'total_cost'=>$a_total);
}

function func_amazon_get_shipping_methods12($cart)
{
    global $single_mode, $config, $user_account;
    static $result_cache = array();

    $md5_args = md5(serialize(array(
        $cart, $user_account,
        $config['Shipping'],
        $config['General'],
    )));

    if (isset($result_cache[$md5_args])) {
        return $result_cache[$md5_args];
    }

    // Option adjustment to avoid empty($userinfo) checking
    // $userinfo will be provided by Amazon in callback
    $config['Shipping']['enable_all_shippings'] = 'Y';
    $userinfo = $cart['userinfo'];
    $products = func_amazon_products_in_cart($cart);

    $_need_shipping = func_cart_is_need_shipping($cart, $products, $userinfo, 'dont_check_free_ship_coupons');

    if (empty($_need_shipping)) {
        $result_cache[$md5_args] = false;
        return false;
    }        

    if (!$single_mode)
        $number_of_providers = count(func_get_products_providers($products));
    else
        $number_of_providers = 1;

    // Some options require adjustment
    $config['Shipping']['enable_all_shippings'] = 'N';

    // Emulate the 'apply_default_country' option enabled
    $config['General']['apply_default_country'] = 'Y';
    $config['General']['default_country'] = $userinfo['b_country'];
    $config['General']['default_zipcode'] = $userinfo['b_zipcode'];
    $config['General']['default_state'] = $userinfo['b_state'];
    $config['General']['default_city'] = $userinfo['b_city'];

    $intershipper_recalc = 'Y';

    x_load('shipping');
    // Get list of all shipping methods that are potentially available for customers
    $shipping_methods = func_get_shipping_methods_list($cart, $products, $userinfo);

    $result_cache[$md5_args] = $shipping_methods;
    return $shipping_methods;
}

function func_amazon_get_server_settings() {
    global $config;

    $settings = array(
        'USD' => array( // US version
            'amazon_host' => 'amazon.com',
            'widget_url' => 'https://static-na.payments-amazon.com/cba/js/us',
            'CBA_weight_unit' => 'lb',
            'XC_weight_unit' => 'lbs', // Weight units compatible with func_units_convert
        ),
        'GBP' => array( // UK version
            'amazon_host' => 'amazon.co.uk',
            'widget_url' => 'https://static-eu.payments-amazon.com/cba/js/gb',
            'CBA_weight_unit' => 'kg',
            'XC_weight_unit' => 'kg',
        ),
        'EUR' => array( // DE version
            'amazon_host' => 'amazon.de',
            'widget_url' => 'https://static-eu.payments-amazon.com/cba/js/de',
            'CBA_weight_unit' => 'kg',
            'XC_weight_unit' => 'kg',
        ),
    );

    if (empty($settings[$config['Amazon_Checkout']['amazon_currency']])) {
        $settings = $settings['USD'];
    } else {
        $settings = $settings[$config['Amazon_Checkout']['amazon_currency']];
    } 

    return $settings;
}

function func_amazon_handle_cancel($skey)
{
    global $xcart_catalogs, $sql_tbl;

    func_cart_unlock();

    if (func_is_acheckout_enabled()) {
        func_acheckout_debug("\t+ Customer canceled Amazon Checkout transaction");
        func_acheckout_debug("\t+ skey: $skey");
    }        

    if (!empty($skey)) {
        db_query("DELETE FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
        db_query("DELETE FROM $sql_tbl[amazon_data] WHERE ref='$skey'");
    } 

    func_header_location($xcart_catalogs['customer']."/cart.php");
}

function func_amazon_handle_return($skey, $amznPmtsOrderIds = '')
{
    global $config, $active_modules, $sql_tbl, $xcart_catalogs;
    global $cart, $current_location, $smarty, $session_orders_cba;

    // Customer returned back to X-Cart
    func_acheckout_debug("\t+ Customer returned back to the shop");
    func_acheckout_debug("\t+ skey: $skey");

    if (!empty($skey)) {

        // Display 'Please wait page'
        $smarty->webmaster_mode = false;

        func_flush(func_display('customer/main/payment_wait.tpl', $smarty, false));

        if (!defined('NO_RSFUNCTION')) {

            register_shutdown_function('func_payment_footer');

        }
        
        $wait_for_callback_sec = ($config['Amazon_Checkout']['amazon_order_message_page'] == 'widget' ? 2 : 20);
        func_acheckout_wait_for_orders_from_callback($skey, $wait_for_callback_sec);

        if (
            $config['Amazon_Checkout']['amazon_order_message_page'] == 'widget'
            && !empty($amznPmtsOrderIds)
        ) {
            $redirect_url = $xcart_catalogs['customer']. "/cart.php?mode=order_message_widget&amazon_orderid=" . $amznPmtsOrderIds;
            x_session_register('session_orders_cba', array());
            $session_orders_cba[] = $amznPmtsOrderIds;
            
            func_acheckout_debug("\t+ Amazon Order ids: " . implode(',', $session_orders_cba));
            func_acheckout_debug("\t+ Redirect to: $redirect_url");
            func_header_location($redirect_url);
        }

        $ret = func_query_first("SELECT param2,param3,param4 FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
        $order_status = $ret['param2'];
        $_orderids = $ret['param3'];

        x_session_register('cart');

        db_query("DELETE FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
        db_query("DELETE FROM $sql_tbl[amazon_data] WHERE ref='$skey'");

        func_cart_unlock();

        if (empty($order_status) || $order_status == 'F') {
            $bill_error = "error_ccprocessor_error";
            $reason = "&bill_message=".urlencode($ret['param4']);
            $redirect_url = $current_location.DIR_CUSTOMER."/error_message.php?error=".$bill_error.$reason;
        }
        else {
            // Reload current session data as it was changed from callback
            global $XCARTSESSID;
            x_session_id($XCARTSESSID);
            $cart = '';
            $redirect_url = $xcart_catalogs['customer']."/cart.php?mode=order_message&orderids=$_orderids";
        }

        func_acheckout_debug("\t+ Order status: $order_status");
        func_acheckout_debug("\t+ Redirect to: $redirect_url");

        func_header_location($redirect_url);

    } else {
        func_header_location($xcart_catalogs['customer']."/cart.php");
    }       

    return true;
}

function func_amazon_header_exit($code)
{
    global $_SERVER;
    $codes = array(500 => 'Internal Server Error', 403 => 'Forbidden', 503 => 'Service Unavailable');
    @header("$_SERVER[SERVER_PROTOCOL] $code ".$codes[$code]);
    exit;
}

function func_amazon_is_response_error($parsed, $cart, $type = 'ORDERCALCULATIONSREQUEST')
{
    $need_shipping = func_array_path($parsed, $type.'/ORDERCALCULATIONCALLBACKS/CALCULATESHIPPINGRATES/0/#') === 'true';

    if ($need_shipping) {
        $amazon_shipping_methods = func_amazon_get_shipping_methods12($cart);
        if (empty($amazon_shipping_methods)) {
            func_acheckout_debug("\t+ INVALID_SHIPPING_ADDRESS-No shipping methods available for this address.");
            $xml = <<<OUT
            <Error>
                <Code>INVALID_SHIPPING_ADDRESS</Code>
                <Message>No shipping methods available for this address.</Message>
            </Error>
OUT;
            return $xml;
        }
    }

    return false;
}

function func_amazon_is_validated_callback()
{
    $check_sign = func_amazon_sign($_POST['UUID'].$_POST['Timestamp']);

    return $check_sign == $_POST['Signature'];
}

function func_amazon_customer_info($_addr)
{
    $customer_info['s_country'] = $customer_info['b_country'] = $_addr['COUNTRYCODE'][0]['#'];
    $customer_info['s_address'] = $customer_info['b_address'] = @$_addr['ADDRESSFIELDONE'][0]['#']."\n".@$_addr['ADDRESSFIELDTWO'][0]['#'];
    $customer_info['s_city']    = $customer_info['b_city']    = $_addr['CITY'][0]['#'];
    $customer_info['s_zipcode'] = $customer_info['b_zipcode'] = $_addr['POSTALCODE'][0]['#'];
    $customer_info['s_state']   = $customer_info['b_state']   = func_amazon_detect_state($_addr['STATE'][0]['#'], $customer_info['s_country'], $customer_info['s_zipcode']);
    $customer_info['s_phone']   = $customer_info['b_phone']   = $_addr['PHONENUMBER'][0]['#'];
    
    $log_addr = implode(",", array_unique($customer_info));
    $log_addr = str_replace("\n","", $log_addr);
    func_acheckout_debug("\t+ Parsed address: $log_addr");

    $customer_info['address'] = array();
    $customer_info['address']['B']['country'] = $customer_info['b_country'];
    $customer_info['address']['B']['address'] = $customer_info['b_address'];
    $customer_info['address']['B']['city']    = $customer_info['b_city'];
    $customer_info['address']['B']['state']   = $customer_info['b_state'];
    $customer_info['address']['B']['zipcode'] = $customer_info['b_zipcode'];
    $customer_info['address']['B']['phone']   = $customer_info['b_phone'];

    if (!empty($_addr['NAME'][0]['#']))
        list($customer_info['address']['B']['firstname'], $customer_info['address']['B']['lastname']) = explode(' ', $_addr['NAME'][0]['#'], 2);

    $customer_info['address']['S'] = $customer_info['address']['B'];
    return $customer_info;
}

function func_amazon_products_in_cart($cart) {//{{{
    global $user_account;

    return func_products_in_cart($cart, (!empty($user_account['membershipid']) ? $user_account['membershipid'] : ''), func_amazon_get_tax_options());
}//}}}

function func_amazon_post_response($data, $name)
{
    global $config;

    $return = $name.'='.rawurlencode($data);
    if (!empty($config['Amazon_Checkout']['amazon_secret_key'])) {
        $return .= '&Signature='.rawurlencode(func_amazon_sign($data));
        $return .= '&aws-access-key-id='.rawurlencode($config['Amazon_Checkout']['amazon_access_key']);
    }

    echo $return;

    return true;
}

// This function creates base64 encoded HMAC_SHA1 signature
function func_amazon_sign($data)
{
    global $config;
    $return = base64_encode(hmac_sha1($data, $config['Amazon_Checkout']['amazon_secret_key']));
    return $return;
}


function func_amazon_submit_encoded_cart($encoded_cart)
{
    global $smarty, $amazon_host, $config;

    // Lock cart for all operations
    func_cart_lock('by_Amazon_Checkout');

    echo func_display('modules/Amazon_Checkout/waiting.tpl', $smarty, false);
    $fields = array("order-input" => $encoded_cart);
    func_create_payment_form("https://$amazon_host/checkout/".$config['Amazon_Checkout']['amazon_mid'], $fields, "Checkout by Amazon");
    return true;
}

function func_amazon_xml12_ShippingMethods($cart)
{
    global $config;

    $shipping_methods = func_amazon_get_shipping_methods12($cart);

    $shipping_xml = '';
    if (!empty($shipping_methods)) {
        func_acheckout_debug("\t+ Shipping methods: " . count($shipping_methods) . " are allowed");

        foreach ($shipping_methods as $_ship_method) {

            $_ship_method['shipping'] = func_amazon_encode(func_insert_trademark($_ship_method['shipping'], 'use_alt'));

            $ShipmentBased_rate = func_hash2xml(array(
                'Amount' => $_ship_method['rate'],
                'CurrencyCode' => $config['Amazon_Checkout']['amazon_currency']
            ));
            
            $shipping_xml .= <<<OUT
            <ShippingMethod>
                <ShippingMethodId>$_ship_method[shippingid]</ShippingMethodId>
                <ServiceLevel>$_ship_method[amazon_service]</ServiceLevel>
                <Rate><ShipmentBased>$ShipmentBased_rate</ShipmentBased></Rate>
                <IncludedRegions>
                    <PredefinedRegion>WorldAll</PredefinedRegion>
                </IncludedRegions>
                <DisplayableShippingLabel>$_ship_method[shipping]</DisplayableShippingLabel>
            </ShippingMethod>
OUT;
        }

        $shipping_xml = "<ShippingMethods>$shipping_xml</ShippingMethods>";
    } else {
        func_acheckout_debug("\t+ Shipping list is empty");
    }

    return $shipping_xml;
}

function func_amazon_xml1_CancelUrl($unique_id)
{
    global $current_location;

    return "<CancelUrl>$current_location/payment/ps_amazon.php?mode=cancel&#x26;skey=$unique_id</CancelUrl>";
}

function func_amazon_xml1_Cart_Items($cart)
{
    $items = array();
    $items = func_array_merge($items, func_amazon_xml1_Cart_Items_products($cart));
    $items = func_array_merge($items, func_amazon_xml1_Cart_Items_giftcerts($cart));
    if (!empty($items))
        $xml = "<Items>".implode("", $items)."</Items>";
    else 
        $xml = '';

    return $xml;            
}

function func_amazon_xml1_Cart_Items_products($cart)
{
    global $config, $shop_language;

    $products = func_amazon_products_in_cart($cart);

    if (empty($products)) {
        func_acheckout_debug("\t+ products array is empty");
        return array(); 
    } else {
        func_acheckout_debug("\t+ " . count($products) . " products sending");
    }       

    x_load('clean_urls');

    $items = array();
    // Generate products list
    foreach ($products as $product) {
        $Description    = func_payment_product_description($product);
        $Description    = func_amazon_encode($Description, 255);

        $URL = htmlspecialchars(func_get_resource_url("product", $product['productid']));

        $image_ids = array('P' => $product['productid'] , 'T' => $product['productid']);
        $image_data = func_get_image_url_by_types($image_ids, 'P');
        $Image_URL = htmlspecialchars($image_data['image_url']);
        if (!is_url($Image_URL)) {
            $Image_URL = func_get_default_image('T', 'get_absolute_link');
        }

        $items[] = 
            "<Item>".
                "<SKU>".$product['productcode'] . '|' . $product['cartid']."</SKU>".
                "<MerchantId>{$config['Amazon_Checkout']['amazon_mid']}</MerchantId>".
                "<Title>".func_amazon_encode($product['product'], 100)."</Title>".
                "<Description>$Description</Description>".
                "<Price>".
                    "<Amount>{$product['taxed_price']}</Amount>".
                    "<CurrencyCode>{$config['Amazon_Checkout']['amazon_currency']}</CurrencyCode>".
                "</Price>".
                "<Quantity>$product[amount]</Quantity>".
                "<Weight>".func_amazon_xml1_Item_Weight($product['weight'])."</Weight>".
                "<URL>$URL</URL>".
                "<Images><Image><URL>$Image_URL</URL></Image></Images>".
                "<ItemCustomData><ProductType>product</ProductType></ItemCustomData>".
            "</Item>";
    }

    return $items;
}

function func_amazon_xml1_Cart_Items_giftcerts($cart)
{
    global $config;

    if (empty($cart['giftcerts']))
        return array(); 

    func_acheckout_debug("\t+ " . count($cart['giftcerts']) . " giftcerts sending");

    foreach ($cart['giftcerts'] as $gcindex => $_giftcert) {

        $_descr = func_amazon_encode(func_get_langvar_by_name('lbl_recipient', '', false, true) . ': ' . $_giftcert['recipient'], 255);
        $_title = func_amazon_encode(func_get_langvar_by_name('lbl_gift_certificate', '', false, true), 100);
        $SKU = md5(implode($_giftcert)) . '|' . $gcindex . 'gc';

        $items[] = <<<ITEM
            <Item>
                <SKU>$SKU</SKU>
                <MerchantId>{$config['Amazon_Checkout']['amazon_mid']}</MerchantId>
                <Title>$_title</Title>
                <Description>$_descr</Description>
                <Price>
                    <Amount>{$_giftcert['amount']}</Amount>
                    <CurrencyCode>{$config['Amazon_Checkout']['amazon_currency']}</CurrencyCode>
                </Price>
                <Quantity>1</Quantity>
                <ItemCustomData><ProductType>giftcert</ProductType></ItemCustomData>
            </Item>
ITEM;
    }

    return $items;
}

function func_amazon_xml1_CartCustomData($unique_id)
{
    $xml = <<<CUSTOM_DATA
    <CartCustomData>
        <Ref>$unique_id</Ref>
    </CartCustomData>
CUSTOM_DATA;

    return $xml;
}

function func_amazon_xml1_Item_Weight($product_weight)
{
    global $config;

    $Weight = '';
    if (!empty($product_weight)) {
        $settings = func_amazon_get_server_settings();
        $Weight = array(
            'Amount' => func_units_convert(func_weight_in_grams($product_weight), 'g', $settings['XC_weight_unit'], 2),
            'Unit' => $settings['CBA_weight_unit'],
        );
        $Weight = func_hash2xml($Weight);
    }

    return $Weight;
}

function func_amazon_xml1_Order($cart, $userinfo)
{
    global $login, $logged_userid;

    $unique_id = func_generate_n_save_uniqueid('txt_acheckout_impossible_error');

    func_acheckout_debug("\t+ skey: $unique_id");
    func_acheckout_debug("\t+ login: $login, logged_userid: $logged_userid");

    settype($cart['userinfo'], 'array');
    $cart['userinfo'] = func_array_merge($cart['userinfo'], $userinfo);
    $ShippingMethods            = func_amazon_xml12_ShippingMethods($cart);
    $ReturnUrl                  = func_amazon_xml1_ReturnUrl($unique_id);
    $CancelUrl                  = func_amazon_xml1_CancelUrl($unique_id);

    $OrderCalculationCallbacks  = func_amazon_xml1_OrderCalculationCallbacks($cart, $unique_id);

    // Cart
    $Cart_Items = func_amazon_xml1_Cart_Items($cart);
    $CartCustomData = func_amazon_xml1_CartCustomData($unique_id);

    $xml = <<<OUT
    <?xml version="1.0" encoding="UTF-8"?>
    <Order xmlns="http://payments.amazon.com/checkout/2009-05-15/">
        <Cart>
            $Cart_Items
            $CartCustomData
        </Cart>
        $ShippingMethods
        $ReturnUrl
        $CancelUrl
        $OrderCalculationCallbacks
        <DisablePromotionCode>true</DisablePromotionCode>
    </Order>
OUT;
    
    $xml = trim($xml);
    return $xml;
}

function func_amazon_xml1_ReturnUrl($unique_id)
{
    global $current_location;

    return "<ReturnUrl>$current_location/payment/ps_amazon.php?mode=continue&#x26;skey=$unique_id</ReturnUrl>";
}

function func_amazon_xml1_OrderCalculationCallbacks($cart, $unique_id)
{
    global $config, $https_location, $http_location;

    if (func_constant('DISABLE_AMAZON_CALLBACKS'))
        return '';

    // Option adjustment to avoid empty($userinfo) checking
    // $userinfo will be provided by Amazon in callback
    $config['Shipping']['enable_all_shippings'] = 'Y';
    $products = func_amazon_products_in_cart($cart);

    $need_shipping = func_cart_is_need_shipping($cart, $products, $cart['userinfo'], 'dont_check_free_ship_coupons');

    $_taxes = func_amazon_get_taxes($cart);
    $need_tax = !empty($_taxes);

    $need_promotions = ($cart['display_subtotal'] > $cart['display_discounted_subtotal']);

    $script_location = ($config['Amazon_Checkout']['amazon_test_mode'] == 'N' ? $https_location : $http_location);

    $OrderCalculationCallbacks = array(
        'CalculateTaxRates' =>  $need_tax ? 'true' : 'false',
        'CalculatePromotions' => $need_promotions ? 'true' : 'false',
        'CalculateShippingRates' => $need_shipping ? 'true' : 'false',
        'OrderCallbackEndpoint' => "$script_location/payment/ps_amazon.php?skey=$unique_id",
        'ProcessOrderOnCallbackFailure' => defined('DEVELOPMENT_MODE') ? 'false' : true
    );
    $OrderCalculationCallbacks = func_hash2xml($OrderCalculationCallbacks);

    $requested_sections = '';
    if ($need_tax)
        $requested_sections .= '|CalculateTaxRates';

    if ($need_promotions)        
        $requested_sections .= '|CalculatePromotions';

    if ($need_shipping)
        $requested_sections .= '|CalculateShippingRates';

    func_acheckout_debug("\t+ Callbacks $requested_sections| are requested");

    $xml = "<OrderCalculationCallbacks>$OrderCalculationCallbacks</OrderCalculationCallbacks>";

    return $xml;
}

function func_amazon_xml2_Address($parsed, $type = 'ORDERCALCULATIONSREQUEST')
{
    $_address_id = func_array_path($parsed, "$type/CALLBACKORDERS/CALLBACKORDER/ADDRESS/ADDRESSID/0/#");
    $xml = "<AddressId>$_address_id</AddressId>";
    return $xml;
}

function func_amazon_xml2_CallbackOrderItems($parsed, $cart, $type = 'ORDERCALCULATIONSREQUEST')
{
    $requested_items = func_array_path($parsed, "$type/CALLBACKORDERCART/CALLBACKORDERCARTITEMS/CALLBACKORDERCARTITEM");

    if (empty($requested_items)) {
        func_amazon_header_exit(403);
    }

    $CallbackOrderItems = array();
    $ShippingMethodIds = func_amazon_xml2_CallbackOrderItem_ShippingMethodIds($cart);
    $products = func_amazon_products_in_cart($cart);

    settype($cart['giftcerts'], 'array');
    settype($products, 'array');

    foreach ($requested_items as $_k => $_v) {
        $_v = $_v['#'];

        $_itemid = func_array_path($_v,"CALLBACKORDERITEMID/0/#");
        $ProductType = func_array_path($_v,"ITEM/ITEMCUSTOMDATA/PRODUCTTYPE/0/#");
        
        list($_sku, $_cartid) = explode("|", func_array_path($_v,"ITEM/SKU/0/#"));
        if ($ProductType == 'product') {
            foreach ($products as $k => $product) {
                if ($product['cartid'] == $_cartid) {
                    $item['CallbackOrderItemId'] = $_itemid;
                    $CallbackOrderItems[] = <<<OUT
                        <CallbackOrderItem>
                            <CallbackOrderItemId>$_itemid</CallbackOrderItemId>
                            $ShippingMethodIds
                        </CallbackOrderItem>
OUT;
                    break;
                }   
            }  
        } elseif ($ProductType == 'giftcert') {
            $gcindex = intval($_cartid);
            foreach ($cart['giftcerts'] as $k => $giftcert) {
                if ($k == $gcindex) {
                    $item['CallbackOrderItemId'] = $_itemid;
                    
                    $CallbackOrderItems[] = <<<OUT
                        <CallbackOrderItem>
                            <CallbackOrderItemId>$_itemid</CallbackOrderItemId>
                            $ShippingMethodIds
                        </CallbackOrderItem>
OUT;
                    break;
                }   
            }  
        }
    }  

    return implode("", $CallbackOrderItems);
}

function func_amazon_xml2_CallbackOrderItem_ShippingMethodIds($cart)
{
    $amazon_shipping_methods = func_amazon_get_shipping_methods12($cart);

    if (empty($amazon_shipping_methods))
        return '';

    // Enable shipping methods for each product
    $ids = array();
    foreach ($amazon_shipping_methods as $sv) {
        $ids[] = "<ShippingMethodId>{$sv['shippingid']}</ShippingMethodId>";
    }  

    return "<ShippingMethodIds>".implode("", $ids)."</ShippingMethodIds>";
}

function func_amazon_xml2_CartTaxAmounts($cart)
{
    global $config;

    $amazon_shipping_methods = func_amazon_get_shipping_methods12($cart);

    if (empty($amazon_shipping_methods))
        return '';

    $tax_amounts = $log = array();
    foreach ($amazon_shipping_methods as $sv) {
        $log[] = " shippingid{$sv['shippingid']}-{$sv['tax_cost']} ";
        $tax_amounts[] = <<<OUT
            <CartTaxAmount>
                <ShippingMethodId>{$sv['shippingid']}</ShippingMethodId>
                <TaxAmount>
                        <Amount>{$sv['tax_cost']}</Amount>
                        <CurrencyCode>{$config['Amazon_Checkout']['amazon_currency']}</CurrencyCode>
                </TaxAmount>
            </CartTaxAmount>
OUT;
    }  
    func_acheckout_debug("\t+ Taxes per shippings:" . implode(",", $log));
    return "<CartTaxAmounts>".implode("", $tax_amounts)."</CartTaxAmounts>";
}

function func_amazon_xml2_OrderCalculationsResponse($parsed, $type = 'ORDERCALCULATIONSREQUEST')
{
    global $sql_tbl, $login, $logged_userid, $cart, $user_tmp, $current_carrier, $user_account, $config;

    // Get addresses, shipping methods and coupon codes from XML request
    $customer_info = func_amazon_customer_info(func_array_path($parsed, "$type/CALLBACKORDERS/CALLBACKORDER/ADDRESS/0/#"));
    $customer_info['membershipid'] = intval(@$user_account['membershipid']);
    $customer_info['usertype'] = 'C';

    if (!empty($logged_userid))
        $customer_info['id'] = $logged_userid;
        
    settype($cart['userinfo'], 'array');
    $cart['userinfo'] = func_array_merge($cart['userinfo'], $customer_info);
    $cart = func_set_cart_address($cart, 'S', $customer_info['address']['S']);
    $cart = func_set_cart_address($cart, 'B', $customer_info['address']['B']);

    $Promotions = $CartPromotionId = $CartTaxAmounts = $log_sending = '';

    // ShippingMethods section
    $ShippingMethods = func_amazon_xml12_ShippingMethods($cart);
    
    // CartTaxAmounts section
    $need_tax = func_array_path($parsed, $type.'/ORDERCALCULATIONCALLBACKS/CALCULATETAXRATES/0/#') === 'true';
    if ($need_tax) {
        $CartTaxAmounts = func_amazon_xml2_CartTaxAmounts($cart);
        $log_sending .= '|CartTaxAmounts';
    } 

    // Promotions section
    $need_promotions = func_array_path($parsed, $type.'/ORDERCALCULATIONCALLBACKS/CALCULATEPROMOTIONS/0/#') === 'true';
    if ($need_promotions) {
        $Promotions = func_amazon_xml2_Promotions($cart);
        $log_sending .= '|Promotions';
        $CartPromotionId = '<CartPromotionId>total_discount_id</CartPromotionId>';
    }        

    if (!empty($ShippingMethods)) {
        $log_sending .= '|ShippingMethods';
    }

    func_acheckout_debug("\t+ $log_sending| sections sending");

    $Response = func_amazon_xml2_Response($parsed, $cart);
    $xml = <<<OUT
    <?xml version="1.0" encoding="UTF-8"?>
    <OrderCalculationsResponse xmlns="http://payments.amazon.com/checkout/2009-05-15/">
        <Response>$Response</Response>
        $Promotions
        $ShippingMethods
        $CartPromotionId
        $CartTaxAmounts
    </OrderCalculationsResponse>
OUT;
    
    $xml = trim($xml);
    return $xml;
}

function func_amazon_xml2_Promotions($cart)
{
    global $config; 

    $discount = abs($cart['display_subtotal'] - $cart['display_discounted_subtotal']);

    $xml = <<<DATA
    <Promotions>
        <Promotion>
            <PromotionId>total_discount_id</PromotionId>
            <Description>Total cart discount</Description>
            <Benefit>
                <FixedAmountDiscount>
                    <Amount>$discount</Amount>
                    <CurrencyCode>{$config['Amazon_Checkout']['amazon_currency']}</CurrencyCode>
                </FixedAmountDiscount>
            </Benefit>
        </Promotion>
    </Promotions>
DATA;

    return $xml;
}

function func_amazon_xml2_Response($parsed, $cart)
{
    if ($xml_error = func_amazon_is_response_error($parsed, $cart))
        return $xml_error;

    $xml = ''.
    '<CallbackOrders>'. 
        '<CallbackOrder>'. 
            '<Address>'.func_amazon_xml2_Address($parsed).'</Address>'.
            '<CallbackOrderItems>'.func_amazon_xml2_CallbackOrderItems($parsed, $cart).'</CallbackOrderItems>'.
        '</CallbackOrder>'.
    '</CallbackOrders>';

    return $xml;
}

/**
 * This function checks if Amazon Checkout button must be enabled or disabled
 */
function func_tpl_is_acheckout_button_enabled()
{
    global $cart, $smarty;
    static $res;

    if (isset($res))
        return $res;
    
    $res = true;    
    $main = $smarty->getTemplateVars('main');

    // Do not show CBA button on OPC/FLC page
    if ($main == 'checkout')
        $res = false;

    if ($res) {
        x_load('cart');
        if (func_cart_is_zero_total_cost($cart))
            $res = false;
    }    
        

    $res = $res && func_is_acheckout_enabled();
    return $res;
}

/**
 * Check if Amazon Checkout can be used 
 */
function func_is_acheckout_enabled()                                                                                          
{
    global $amazon_enabled;
    return isset($amazon_enabled) ? $amazon_enabled : false;
} 

if (!function_exists('hmac_sha1')) {
    function hmac_sha1($data, $key) {
        $blocksize = 64;

        if (strlen($key) > $blocksize) {
            $key = pack('H*', sha1($key));
        }

        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*', sha1(($key^$opad).pack('H*', sha1(($key^$ipad).$data))));

        return $hmac;
    }

}

function func_amazon_log_raw_post_get_data()
{
    global $var_dirs, $acheckout_log_detailed_data, $PROXY_IP, $CLIENT_IP;

    $postdata = func_get_raw_post_data();

    if (defined('ACHECKOUT_DEBUG') && $acheckout_log_detailed_data) {
        // Save received data to the unique log file
        $filename = $var_dirs['log'] . "/acheckout-" . date("Ymd-His") . "-" . uniqid(mt_rand()) . '.log.php';
        if ($fd = @fopen($filename, "a+")) {
    
            $str[] = "PROXY_IP: $PROXY_IP";
            $str[] = "CLIENT_IP: $CLIENT_IP";
    
            ob_start();
            echo "\n_GET:\n";
            print_r($_GET);
            echo "\n_POST:\n";
            print_r($_POST);
            echo "\nHTTP_RAW_POST_DATA:\n";
            print_r($postdata);
            $str[] = ob_get_contents();
            ob_end_clean(); 
            fwrite($fd, "<?php die(); ?>\n\n" . implode("\n\n", $str));
            fclose($fd);
            func_chmod_file($filename);
        }
    }
}

function func_acheckout_init() { //{{{

    global $config, $smarty, $active_modules, $amazon_enabled, $amazon_host, $amazon_widget_url;
    global $acheckout_log_detailed_data, $acheckout_global_log, $acheckout_global_xml_log;

    if (defined('QUICK_START')) {
        return;
    }

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        if (function_exists('func_add_event_listener')) {
            func_add_event_listener('module.ajax.toggle', 'func_acheckout_on_module_toggle');
        }
    }

    $_domains = func_amazon_get_server_settings();

    // URL for sending HTTPS requests
    $amazon_host = $config['Amazon_Checkout']['amazon_test_mode'] == 'Y' 
        ? "payments-sandbox.$_domains[amazon_host]"
        : "payments.$_domains[amazon_host]";

    $amazon_widget_url = $config['Amazon_Checkout']['amazon_test_mode'] == 'Y' 
        ? "$_domains[widget_url]/sandbox/PaymentWidgets.js"
        : "$_domains[widget_url]/PaymentWidgets.js";

    $smarty->assign('amazon_host', $amazon_host);
    $smarty->assign('amazon_widget_url', $amazon_widget_url);

    if (func_constant('AREA_TYPE') != 'C') {
        return;
    }

    if (!$amazon_enabled) {
        // Disable module for customer area
        unset($active_modules['Amazon_Checkout']);
        $smarty->assign('active_modules', $active_modules);
        return;
    }

    $acheckout_log_detailed_data = false;
    if (defined('ACHECKOUT_DEBUG')) {
        // Logging enabled
        if (ACHECKOUT_DEBUG == 1)
            $acheckout_log_detailed_data = true;

        $acheckout_global_log = '';
        $acheckout_global_xml_log = '';

        register_shutdown_function('func_acheckout_save_log');
    }

} //}}}

function func_acheckout_on_module_toggle($module_name, $module_new_state) {

    global $sql_tbl, $active_modules, $smarty;

    if (
        $module_name == 'Amazon_Checkout'
        && $module_new_state == true
        && !empty($active_modules['Amazon_Payments_Advanced'])
    ) {
        db_query("UPDATE $sql_tbl[modules] SET active = 'N' WHERE module_name = 'Amazon_Payments_Advanced'");
        return 'modules.php';
    }
}


?>
