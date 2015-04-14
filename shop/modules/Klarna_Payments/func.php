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
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v10 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_SESSION_START')) {
    header('Location: ../');
    die('Access denied');
}

function func_klarna_create_config(&$klarna, $payment = 'invoice', $country_code = '') {

    global $sql_tbl, $xcart_dir, $config, $var_dirs;

    switch ($payment) {
        
        case 'invoice':
            $processor = 'cc_klarna.php';
            break;

        case 'part_payment':
            $processor = 'cc_klarna_pp.php';
            break;
        
        default:
            $processor = 'cc_klarna.php';
            break;
    }
    
    $module_params = func_query_first("SELECT * FROM $sql_tbl[ccprocessors] WHERE processor='$processor'");
    
    $country_code = strtolower($country_code);
    $payment_mode = ($config['Klarna_Payments']['klarna_testmode_' . $country_code] == 'Y') ? Klarna::BETA : Klarna::LIVE;
    
    if (empty($country_code)) {

        $country_code = KlarnaCountry::getCode($config['Klarna_Payments']['user_country']);
    }
   
    list($klarna_country, $klarna_language, $klarna_currency) = func_klarna_get_location_codes($country_code);

    $klarna_currency = KlarnaCurrency::fromCode(strtolower($klarna_currency));
    
    $klarna->config(
        $config['Klarna_Payments']['klarna_eid_' . $country_code], // Merchant ID
        $config['Klarna_Payments']['klarna_shared_secret_' . $country_code], // Shared Secret
        $klarna_country, // Country
        $klarna_language, // Language
        $klarna_currency, // Currency
        $payment_mode,             // Server 
        'json',                    // PClasses storage 
        $var_dirs['klarna_pclass_dir'] . '/pclasses_' . $country_code . '.json',
        true,                      // SSL
        true                       // Remote logging of response times of xmlrpc calls
    );

}

function func_klarna_get_monthly_cost($sum, $flag, $pclassid = false) {
    
    global $config, $active_modules, $store_currency;

    $cost = $sum;

    if (!empty($active_modules['XMultiCurrency'])) {
            
        list($tmp_country, $tmp_language, $tmp_currency) = func_klarna_get_location_codes($config['Klarna_Payments']['user_country']);
        $customer_currency = func_mc_get_currency($tmp_currency);
        $currency = func_mc_get_currency($store_currency);
        $cost = $cost * (isset($customer_currency['rate']) && $customer_currency['is_default'] < 1 && 0 < doubleval($customer_currency['rate']) ? $customer_currency['rate'] : 1);
    }
    
    if (!$config['Klarna_Payments']['part_payment_enabled']) {
        
        return false;
    }
    
    if ($flag != KlarnaFlags::CHECKOUT_PAGE && $config['Klarna_Payments']['klarna_min_summ_for_pp_' . $config['Klarna_Payments']['user_country']] > $cost) {
        
        return false;
    }
    
    if (!func_klarna_check_nl_amount_restiction($cost)) {
        
        return false;
    }

    $k = new Klarna();

    func_klarna_create_config($k, 'part_payment', $config['Klarna_Payments']['user_country']);
    
    if (!$pclassid) {
        $pclass = $k->getCheapestPClass($cost, $flag);
    } else {
        $pclass = $k->getPClass($pclassid);
    }
    
    if ($pclass) {

        $value = KlarnaCalc::calc_monthly_cost($cost, $pclass, $flag);
        if (!empty($active_modules['XMultiCurrency'])) {
            if (!empty($customer_currency) && $customer_currency['is_default'] < 1) {
                $value_primary = $value / $customer_currency['rate'];
                $value = $value_primary * (isset($currency['rate']) && $currency['is_default'] < 1 && 0 < doubleval($currency['rate']) ? $currency['rate'] : 1);
            }
        }    
        return price_format($value);
    }
    
    return false;
}

function func_klarna_get_pclasses($country = '') {
    
    $k = new Klarna();

    func_klarna_create_config($k, 'part_payment', $country);
    
    $pclasses = $k->getPClasses($type = null);
    
    if (empty($pclasses)) {

        $k->fetchPClasses();
        $pclasses = $k->getPClasses($type = null);
    }

    if (!empty($pclasses)) {
        
        $return = array();
        foreach ($pclasses as $k => $pclass) {
            
            $return[$k]['description'] = $pclass->getDescription();
            $return[$k]['months'] = $pclass->getMonths();
            $return[$k]['start_fee'] = $pclass->getStartFee();
            $return[$k]['invoice_fee'] = $pclass->getInvoiceFee();
            $return[$k]['interest_rate'] = $pclass->getInterestRate();
            $return[$k]['min_amount'] = $pclass->getMinAmount();
            $return[$k]['country'] = $pclass->getCountry();
            $return[$k]['id'] = $pclass->getId();
            $return[$k]['type'] = $pclass->getType();
            $return[$k]['expire'] = $pclass->getExpire();

        }

        return $return;
    }
    return false;
}

function func_klarna_fetch_pclasses($country = '') {

    $k = new Klarna();

    func_klarna_create_config($k, 'part_payment', $country);
    
    $k->fetchPClasses();
   
    $pclasses = func_klarna_get_pclasses($country);
    
    return $pclasses;
}

function func_klarna_get_address($ssn) {

    global $config;

    $country = $config['Klarna_Payments']['user_country'];
    if ($country != 'se') {

        return false;
    }

    $k = new Klarna();

    func_klarna_create_config($k, '', $country);

    $k->setCountry($country);

    $addrs = $k->getAddresses($pno = $ssn);
    
    if (!empty($addrs)) {
        
        $addresses = array();

        foreach ($addrs as $k => $addr) {
            
            if ($addr->isCompany) {
                $addresses[$k]['company'] = func_convert_encoding($addr->getCompanyName(), 'ISO-8859-1', 'UTF-8');
            }
            $addresses[$k]['firstname'] = func_convert_encoding($addr->getFirstName(), 'ISO-8859-1', 'UTF-8');
            $addresses[$k]['lastname'] = func_convert_encoding($addr->getLastName(), 'ISO-8859-1', 'UTF-8');
            $addresses[$k]['address'] = func_convert_encoding($addr->getStreet(), 'ISO-8859-1', 'UTF-8');
            $addresses[$k]['zipcode'] = $addr->getZipCode();
            $addresses[$k]['city'] = func_convert_encoding($addr->getCity(), 'ISO-8859-1', 'UTF-8');
            $addresses[$k]['country'] = strtoupper($addr->getCountryCode());
            $addresses[$k]['countryname'] = func_get_country($addresses[$k]['country']);
        }

        return $addresses;
    }

    return false;
}

function func_klarna_update_compaigns_and_redirect($cc_processor) {
    
    global $config, $top_message;

    if ($cc_processor == 'cc_klarna_pp.php' && !empty($config['Klarna_Payments']['klarna_avail_countries'])) {
        
        foreach ($config['Klarna_Payments']['klarna_avail_countries'] as $country) {
            
            func_klarna_fetch_pclasses($country);
        }
        x_session_register('top_message');
        $top_message = array(
            'type'    => 'I',
            'content' => func_get_langvar_by_name('lbl_klarna_campaigns_updated'),
        );
        func_header_location("cc_processing.php?mode=update&cc_processor=$cc_processor");
    }
}

function func_klarna_set_pclasses($cc_processor, &$smarty) {
    
    global $config;
    
    if (empty($config['Klarna_Payments']['klarna_avail_countries'])) {
        
        return;
    }
        
    if ($cc_processor == 'cc_klarna_pp.php') {
        
        foreach ($config['Klarna_Payments']['klarna_avail_countries'] as $country) {
 
            $pclasses[$country]['pclasses'] = func_klarna_get_pclasses($country);
            $pclasses[$country]['countryname'] = func_get_country($country);
        }

        $smarty->assign('pclasses', $pclasses);
    } 
}

function func_klarna_calculate_pclasses_monthly_cost($cart, &$smarty, $selected_pclass = false) {
    
    global $config, $store_currency, $active_modules, $userinfo;
    
    list($payment_country, $payment_language, $payment_currency) = func_klarna_get_location_codes($config['Klarna_Payments']['user_country']);
    $total_cost = $cart['total_cost'];
    if (!empty($active_modules['XMultiCurrency'])) {
        $primary_currency = func_mc_get_primary_currency();
        if ($primary_currency != $payment_currency) {
            
            $currency = func_mc_get_currency($payment_currency);
            $total_cost = $total_cost * (isset($currency['rate']) && $currency['is_default'] < 1 && 0 < doubleval($currency['rate']) ? $currency['rate'] : 1);
        }
    }

    $pclasses = func_klarna_get_pclasses($config['Klarna_Payments']['user_country']);

    if (!empty($pclasses)) {
        
        foreach ($pclasses as $k => $v) {
            
            if ($v['min_amount'] > $total_cost) {
                
                unset($pclasses[$k]);
                continue;
            }

            $pclasses[$k]['monthly_cost'] = func_klarna_get_monthly_cost($cart['total_cost'], KLARNA_CHECKOUT_PAGE, $v['id']); 
        }
    }
    
    $smarty->assign('selected_pclass', ($selected_pclass) ? $selected_pclass : $pclasses[0]['id']);
    $smarty->assign('klarna_pclasses', $pclasses);
    $smarty->assign('klarna_pclasses_count', count($pclasses));
    
}

function func_klarna_check_input_params($module_params, &$userinfo, $user_ssn = false, $selected_pclass = false) {
    global $top_message;
    
    if (
        (
            in_array($module_params['processor'], array('cc_klarna_pp.php', 'cc_klarna.php'))
            && !$user_ssn
        )    
        || (
            !$selected_pclass
            && $module_params['processor'] == 'cc_klarna_pp.php'
        )
    ) {
        
        x_session_register('top_message');
        if (!$user_ssn) {
            $top_message = array(
                'type' => 'E',
                'content' => func_get_langvar_by_name('err_klarna_ssn_needed')
            );
        } elseif (!$selected_pclass) {

            $top_message = array(
                'type' => 'E',
                'content' => func_get_langvar_by_name('err_klarna_select_pclass')
            );
        }

        return false;
    }
   
    if (in_array($module_params['processor'], array('cc_klarna_pp.php', 'cc_klarna.php'))) {
        $userinfo['ssn'] = $user_ssn;
        if (in_array($userinfo['b_country'], array('DE', 'NL'))) {
            
            preg_match('/^([\d]+)-([\d]+)-([\d]+)$/', $user_ssn, $m);
            $userinfo['ssn'] = $m[3] . $m[2] . $m[1];
        }
        global $user_gender;

        $userinfo['user_gender'] = $user_gender;

    }

    return true;
}

function func_klarna_set_opc_payments_update_flag($checkout_module, &$smarty) {
    
    global $config;

    $update_payment_methods = ($checkout_module == 'One_Page_Checkout' && ($config['Klarna_Payments']['invoice_payment_enabled'] || $config['Klarna_Payments']['part_payment_enabled']));

    $smarty->assign('update_payment_methods', $update_payment_methods);
}

function func_klarna_update_addresses($cart, &$addresses) {
   
    if (!empty($cart['use_klarna_address']) && $cart['use_klarna_address'] == 'Y' && !empty($cart['klarna_address'])) {
        $addresses['S'] = func_array_merge($addresses['S'], $cart['klarna_address']);
        $addresses['B'] = func_array_merge($addresses['B'], $cart['klarna_address']);
    }
}

function func_klarna_check_currency_avail($currency) {
    
    global $active_modules, $sql_tbl;
    
    if (empty($active_modules['XMultiCurrency'])) {

        return false;
    }

    if (func_query_first_cell($sql = "SELECT COUNT(*) FROM $sql_tbl[mc_currencies] WHERE code = '$currency'") < 1) {
        
        return false;
    }

    return true;
}

function func_klarna_get_location_codes($country) {
    
    switch ($country) {

        case 'no':
            $payment_country = KlarnaCountry::NO;
            $payment_language = KlarnaLanguage::NB;
            $payment_currency = 'NOK';
            break;

        case 'nl':
            $payment_country = KlarnaCountry::NL;
            $payment_language = KlarnaLanguage::NL;
            $payment_currency = 'EUR';
            break;

        case 'dk':
            $payment_country = KlarnaCountry::DK;
            $payment_language = KlarnaLanguage::DA;
            $payment_currency = 'DKK';
            break;

        case 'fi':
            $payment_country = KlarnaCountry::FI;
            $payment_language = KlarnaLanguage::FI;
            $payment_currency = 'EUR';
            break;

        case 'de':
            $payment_country = KlarnaCountry::DE;
            $payment_language = KlarnaLanguage::DE;
            $payment_currency = 'EUR';
            break;

        case 'se':
            $payment_country = KlarnaCountry::SE;
            $payment_language = KlarnaLanguage::SV;
            $payment_currency = 'SEK';
            break;

        default:
            return NULL;
            break;

    }

    return array($payment_country, $payment_language, $payment_currency);

}

function func_klarna_check_avail() {
    
    global $config;

    return (($config['Klarna_Payments']['invoice_payment_enabled'] || $config['Klarna_Payments']['part_payment_enabled']) && in_array($config['Klarna_Payments']['user_country'], $config['Klarna_Payments']['klarna_avail_countries']));
}

function func_klarna_create_goods_list(&$klarna, $products, $payment_currency_rate = 1) {

    global $default_charset;

    if (!empty($products)) {
        
        foreach ($products as $product) {
            
            $tax_value = 0;
            if (!empty($product['taxes'])) {

                foreach ($product['taxes'] as $tax) {
                    
                    if ($tax['rate_type'] == '%') {

                        $tax_value += $tax['rate_value'];

                    } else {

                        $tax_value += ($tax['rate_value'] / $product['price']) * 100;
                    }
                }
            }

            $klarna->addArticle(
                $qty = func_convert_encoding($product['amount'], $default_charset, 'ISO-8859-1'), //Quantity
                $artNo = func_convert_encoding($product['productcode'], $default_charset, 'ISO-8859-1'), //Article number
                $title = func_convert_encoding(addslashes($product['product']), $default_charset, 'ISO-8859-1'), //Article name/title
                $price = func_convert_encoding($product['display_price'] * $payment_currency_rate, $default_charset, 'ISO-8859-1'),
                $vat = $tax_value, //19% VAT
                $discount = 0,
                $flags = KlarnaFlags::INC_VAT //Price is including VAT.
            );
        }
    }
}

function func_klarna_add_coupon(&$klarna, $cart, $payment_currency_rate = 1) {
    
    if (empty($cart['coupon']) || $cart['coupon'] == 0) {
        
        return;
    }
    $taxes = $cart['taxes'];
    $tax_value = 0;
    if ($taxes) {
        foreach ($taxes as $tax) {
            
            if ($tax['rate_type'] == '%') {

                $tax_value += $tax['rate_value'];

            } else {

                $tax_value += ($tax['rate_value'] / $cart['coupon_discount']) * 100;
            }
        }
    }

    $klarna->addArticle(
        $qty = 1, //Quantity
        $artNo = 'discount coupon', //Article number
        $title = $cart['coupon'], //Article name/title
        $price = - ($cart['coupon_discount'] * $payment_currency_rate),
        $vat = $tax_value, //19% VAT
        $discount = 0,
        $flags = KlarnaFlags::INC_VAT //Price is including VAT.
    );

}

function func_klarna_add_shipping_cost(&$klarna, $shipping_cost, $taxes = false) {
    
    global $default_charset;

    $tax_value = 0;
    if ($taxes) {
        foreach ($taxes as $tax) {
            
            if ($tax['rate_type'] == '%') {

                $tax_value += $tax['rate_value'];

            } else {

                $tax_value += ($tax['rate_value'] / $shipping_cost) * 100;
            }
        }
    }
    $klarna->addArticle(
        $qty = 1,
        $artNo = '',
        $title = func_convert_encoding('Shipping fee', $default_charset, 'ISO-8859-1'),
        $price = func_convert_encoding($shipping_cost, $default_charset, 'ISO-8859-1'),
        $vat = $tax_value,
        $discount = 0,
        $flags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_SHIPMENT //Price is including VAT and is shipment fee
    );

}

function func_klarna_add_payment_surcharge(&$klarna, $surcharge, $taxes = false) {
    
    global $userinfo, $sql_tbl, $config, $default_charset;

    if (empty($taxes) && !empty($userinfo)) {
        $user_zone = func_get_customer_zone_ship($userinfo['id'], '', 'D');
        $taxes = func_query("SELECT * FROM $sql_tbl[tax_rates] WHERE taxid = '" . $config['Klarna_Payments']['klarna_invoice_tax'] . "' AND zoneid = '$user_zone'");
    }

    $tax_value = 0;
    if ($taxes && $config['Klarna_Payments']['klarna_invoice_tax'] == 'Y') {

        foreach ($taxes as $tax) {
            
            if ($tax['rate_type'] == '%') {

                $tax_value += $tax['rate_value'];

            } else {

                $tax_value += ($tax['rate_value'] / $surcharge) * 100;
            }
        }
        $_flags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_HANDLING;

    } else {

        $_flags = KlarnaFlags::IS_HANDLING;
    }
    $klarna->addArticle(
        $qty = 1,
        $artNo = '',
        $title = func_convert_encoding('Handling fee', $default_charset, 'ISO-8859-1'),
        $price = func_convert_encoding($surcharge, $default_charset, 'ISO-8859-1'),
        $vat = $tax_value,
        $discount = 0,
        $flags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_HANDLING //Price is including VAT and is handling fee
    );

}

function func_klarna_add_addresses(&$klarna, $userinfo, $payment_country) {

    global $default_charset;

    list($street, $house_num, $house_ext) = func_klarna_split_address($userinfo['b_address']);
    $billing_addr = new KlarnaAddr(
        $email = func_convert_encoding($userinfo['email'], $default_charset, 'ISO-8859-1'),
        $telno = '', //We skip the normal land line phone, only one is needed.
        $cellno = func_convert_encoding($userinfo['phone'], $default_charset, 'ISO-8859-1'),
        $fname = func_convert_encoding($userinfo['b_firstname'], $default_charset, 'ISO-8859-1'),
        $lname = func_convert_encoding($userinfo['b_lastname'], $default_charset, 'ISO-8859-1'),
        $careof = '',  //No care of, C/O.
        $street = func_convert_encoding($street, $default_charset, 'ISO-8859-1'), //For DE and NL specify street number in houseNo.
        $zip = func_convert_encoding($userinfo['b_zipcode'], $default_charset, 'ISO-8859-1'),
        $city = func_convert_encoding($userinfo['b_city'], $default_charset, 'ISO-8859-1'),
        $country = func_convert_encoding($payment_country, $default_charset, 'ISO-8859-1'),
        $houseNo = func_convert_encoding($house_num, $default_charset, 'ISO-8859-1'), //For DE and NL we need to specify houseNo.
        $houseExt = func_convert_encoding($house_ext, $default_charset, 'ISO-8859-1') //Only required for NL.
    );

    list($street, $house_num, $house_ext) = func_klarna_split_address($userinfo['s_address']);
    $shipping_addr = new KlarnaAddr(
        $email = func_convert_encoding($userinfo['email'], $default_charset, 'ISO-8859-1'),
        $telno = '', //We skip the normal land line phone, only one is needed.
        $cellno = func_convert_encoding($userinfo['phone'], $default_charset, 'ISO-8859-1'),
        $fname = func_convert_encoding($userinfo['s_firstname'], $default_charset, 'ISO-8859-1'),
        $lname = func_convert_encoding($userinfo['s_lastname'], $default_charset, 'ISO-8859-1'),
        $careof = '',  //No care of, C/O.
        $street = func_convert_encoding($street, $default_charset, 'ISO-8859-1'), //For DE and NL specify street number in houseNo.
        $zip = func_convert_encoding($userinfo['s_zipcode'], $default_charset, 'ISO-8859-1'),
        $city = func_convert_encoding($userinfo['s_city'], $default_charset, 'ISO-8859-1'),
        $country = func_convert_encoding($payment_country, $default_charset, 'ISO-8859-1'),
        $houseNo = func_convert_encoding($house_num, $default_charset, 'ISO-8859-1'), //For DE and NL we need to specify houseNo.
        $houseExt = func_convert_encoding($house_ext, $default_charset, 'ISO-8859-1') //Only required for NL.
    );

    if (!empty($userinfo['company'])) {
        $billing_addr->setCompanyName($userinfo['company']);
        $shipping_addr->setCompanyName($userinfo['company']);
    }

    $klarna->setAddress(KlarnaFlags::IS_BILLING, $billing_addr);
    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $shipping_addr);
}

function func_klarna_change_names(&$payment_methods) {
    
    global $config;
    
    if (empty($payment_methods) || !is_array($payment_methods)) {
        
        return;
    }

    foreach ($payment_methods as $k => $v) {
        
        if ($v['processor_file'] == 'cc_klarna.php') {
            
            $payment_methods[$k]['payment_method'] = func_get_langvar_by_name('lbl_klarna_invoice_name_' . $config['Klarna_Payments']['user_country'], false, false, true);
        }

        if ($v['processor_file'] == 'cc_klarna_pp.php') {
            
            $payment_methods[$k]['payment_method'] = func_get_langvar_by_name('lbl_klarna_account_name_' . $config['Klarna_Payments']['user_country'], false, false, true);
        }
    }
}

function func_klarna_split_address($address)
{
    // Get everything up to the first number with a regex
    $hasMatch = preg_match('/^[^0-9]*/', $address, $match);

    // If no matching is possible, return the supplied string as the street
    if (!$hasMatch) {
        return array($address, "", "");
    }

    // Remove the street from the address.
    $address = str_replace($match[0], "", $address);
    $street = trim($match[0]);

    // Nothing left to split, return
    if (strlen($address == 0)) {
        return array($street, "", "");
    }
    // Explode address to an array
    $addrArray = explode(" ", $address);

    // Shift the first element off the array, that is the house number
    $housenumber = array_shift($addrArray);

    // If the array is empty now, there is no extension.
    if (count($addrArray) == 0) {
        return array($street, $housenumber, "");
    }

    // Join together the remaining pieces as the extension.
    $extension = implode(" ", $addrArray);

    return array($street, $housenumber, $extension);
}

function func_klarna_set_currency_symbol_for_monthly_cost() {

    global $active_modules, $store_currency, $smarty;
    
    if (!empty($active_modules['XMultiCurrency']) && $store_currency) {
        
        $currency = func_mc_get_currency($store_currency);

        if (!empty($currency)) {
             
            $smarty->assign('store_currency_symbol', $currency['symbol']);
        }
    }

}

function func_klarna_do($order, $action) {

    global $sql_tbl, $http_location, $config;
    
    if (empty($order['order']['extra']['reservation_id'])) {
        
        return false;
    }   

    $k = new Klarna();
    
    if (!$k) {
        return false;
    }
    func_klarna_create_config($k, 'invoice', $order['userinfo']['b_country']);
    
    if ($action == 'activate') {

        $check_result = $k->checkOrderStatus($order['order']['extra']['reservation_id'], 0);
        
        if ($check_result == KlarnaFlags::ACCEPTED) {
            $result = $k->activate($order['order']['extra']['reservation_id']);
    
            if ($result[0] == 'ok') {
        
                func_array2insert(
                    'order_extras',
                    array(
                        'orderid' => $order['order']['orderid'],
                        'khash' => 'klarna_invoice_id',
                        'value' => $result[1]
                    ),
                    true
                );

                $status = true;
                $err_msg = '';
                
                if ($config['Klarna_Payments']['klarna_auto_send_email'] == 'Y') {
                    
                    $email_result = $k->emailInvoice($result[1]);
                }

            } else {
                $status = false;
                $err_msg = $result[1];
            }
        } else {

            if ($check_result == KlarnaFlags::DENIED) {

                func_header_location("process_order.php?orderid=" . $order['order']['orderid'] . "&mode=decline");

            } else {

                $status = false;
                $err_msg = func_get_langvar_by_name('txt_klarna_reservation_pending');
                
            }
        }

    } elseif ($action == 'cancel') {
        
        $result = $k->cancelReservation($order['order']['extra']['reservation_id']);

        $status = ($result) ? true : false;
    }

    return array($status, $err_msg, array());
 
}

function func_klarna_check_order_status($orderid) {

    global $sql_tbl, $http_location, $config;
    global $top_message;

    $order = func_order_data($orderid);
    if (empty($order['order']['extra']['reservation_id'])) {
        
        return false;
    }   

    $k = new Klarna();
    
    if (!$k) {
        return false;
    }
    func_klarna_create_config($k, 'invoice', $order['userinfo']['b_country']);
    
    $check_result = $k->checkOrderStatus($order['order']['extra']['reservation_id'], 0);
    
    x_session_register('top_message');

    $top_messag = array(
        'type' => 'I',
    );
    switch ($check_result) {

        case KlarnaFlags::ACCEPTED:
            $top_message['content'] = func_get_langvar_by_name('lbl_klarna_is_accepted');
            db_query("UPDATE $sql_tbl[orders] SET klarna_order_status = 'A' WHERE orderid = '" . $order['order']['orderid'] . "'");
            break;

        case KlarnaFlags::PENDING:
            $top_message['content'] = func_get_langvar_by_name('lbl_klarna_is_pending');
            break;

        case KlarnaFlags::DENIED:
            $top_message['content'] = func_get_langvar_by_name('lbl_klarna_is_denied');
            func_header_location("process_order.php?orderid=" . $order['order']['orderid'] . "&mode=decline");
            break;
    }

    func_header_location('order.php?orderid=' . $order['order']['orderid']);
}

function func_klarna_delete_orders($orders_to_delete) {

    global $sql_tbl;

    $klarna_payment_ids = func_query_column("SELECT paymentid FROM $sql_tbl[ccprocessors] WHERE processor LIKE 'cc_klarna%'");

    if (empty($klarna_payment_ids)) {
        
        return;
    }

    $klarna_orders = func_query_column("SELECT orderid FROM $sql_tbl[orders] WHERE paymentid IN ('" . implode("','", $klarna_payment_ids) . "') AND status = 'A'");

    if (empty($klarna_orders)) {
        
        return;
    }

    foreach ($klarna_orders as $orderid) {
        x_load('payment');
        func_payment_do_void($orderid);
    }
}

function func_klarna_check_nl_amount_restiction($cost) {
    
    global $config;
    
    if ($config['Klarna_Payments']['user_country'] == 'nl' && $cost > 250) {
        
        return false;
    }

    return true;
}

function func_klarna_exception_handler($exception) {
    
    global $HTTP_REFERER, $top_message, $is_ajax_request, $klarna_err_redirect_count;

    $error = $exception->getMessage();

    if (strpos($error, 'Syntax error in template') !== false) {
        print_r($error);
    }

    x_log_add('PHP', $error, true);
    
    x_session_register('top_message');
    $top_message = array(
        'type'    => 'E',
        'content' => func_convert_encoding($error, 'ISO-8859-1', 'UTF-8')
    );

    if ($is_ajax_request == 'Y') {
        
        func_reload_parent_window();

    } else {
        x_session_register('klarna_err_redirect_count', 0);
        $klarna_err_redirect_count++;

        if ($klarna_err_redirect_count <= 4) {
            func_header_location($HTTP_REFERER);
        } else {
            //To avoid cycling on error . The page isn't redirecting properly
            x_session_save();
            exit;
        }
    }
}

function func_klarna_correct_payments($payment_methods) {
    
    global $config, $cart;

    if (!empty($payment_methods) && is_array($payment_methods)) {

        foreach ($payment_methods as $k => $p) {

            if (
                (
                    $p['processor_file'] == 'cc_klarna.php'
                    && !$config['Klarna_Payments']['invoice_payment_enabled']
                ) || (
                    $p['processor_file'] == 'cc_klarna_pp.php'
                    && (
                        !$config['Klarna_Payments']['part_payment_enabled']
                        || !func_klarna_check_nl_amount_restiction($cart['total_cost'])
                    )
                )
            ) {

                unset($payment_methods[$k]);
            }
        }
    }

    func_klarna_change_names($payment_methods);

    return $payment_methods;

}

function func_klarna_address_merge(&$userinfo, $cart) {

    if (!empty($userinfo) && !empty($cart['klarna_address']) && $cart['use_klarna_address'] == 'Y') {
        
        $userinfo['address']['B'] = func_array_merge($userinfo['address']['B'], $cart['klarna_address']);
        $userinfo['address']['S'] = func_array_merge($userinfo['address']['S'], $cart['klarna_address']);

        foreach ($cart['klarna_address'] as $k => $v) {
            
            $userinfo['b_'.$k] = $cart['klarna_address'][$k];
            $userinfo['s_'.$k] = $cart['klarna_address'][$k];
        }
    }

}

function func_klarna_on_module_toggle($module_name, $active) {

    global $sql_tbl;

    if ($module_name == 'Klarna_Payments') {
        db_query("UPDATE $sql_tbl[payment_methods] SET active = '" . (($active) ? 'Y' : 'N') . "' WHERE processor_file LIKE 'cc_klarna%'");
    }

}

?>
