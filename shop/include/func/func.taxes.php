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
 * Taxes-related functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    4f19e819ef1b32fa44cd31084c61ff5d75d41be9, v83 (xcart_4_7_0), 2015-03-03 11:35:06, func.taxes.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

x_load('cart');

abstract class XCTaxesDefs {
    const TAX_SCHEME_GENERAL    = 'TAX_SCHEME_GENERAL';
    const TAX_SCHEME_NO_TAXES   = 'TAX_SCHEME_NO_TAXES';
    const TAX_SCHEME_NO_TAXES_FOR_VALIDATED  = 'TAX_SCHEME_NO_TAXES_FOR_VALIDATED';

    public static function getAvaliableTaxSchemes() {
        return array(
            self::TAX_SCHEME_GENERAL,
            self::TAX_SCHEME_NO_TAXES,
            self::TAX_SCHEME_NO_TAXES_FOR_VALIDATED,
        );
    }
}

/**
 * This function gathers the product taxes information
 */
function func_get_product_taxes(&$product, $userid = 0, $calculate_discounted_price = false, $taxes = '', $tax_options_override = array(), $customer_info = array(), $force_keep_price_deducted_tax = false)
{ // {{{
    global $config;

    // The function changes: $product['taxed_price'] / $product['price_deducted_tax'] / $product['price']

    $amount = (isset($product['amount']) && $product['amount'] > 0) ? $product['amount'] : 1; 

    if (
        $calculate_discounted_price
        && isset($product['discounted_price'])
    ) {
        $price = $product['discounted_price'] / $amount;
    } else {
        $price = $product['price'];
    }

    if (empty($taxes)) {
        $taxes = func_get_product_tax_rates($product, $userid, $tax_options_override, $customer_info);
    }

    x_load('user');
    $_anonymous_userinfo = func_get_anonymous_userinfo();

    $skip_tax_conditions = (empty($userid) && empty($_anonymous_userinfo) && $config['General']['apply_default_country'] != 'Y');

    $price_deducted_tax_flag_product = !empty($product['price_deducted_tax']) ? $product['price_deducted_tax'] : '';
    list($total_tax_value, $price_deducted_tax_flag) = func_taxes_get_total_values($price, $skip_tax_conditions, $price_deducted_tax_flag_product, $taxes);

    if (!empty($total_tax_value)) {
        $product['price'] = $price = $price - $total_tax_value;
    }

    if (!$force_keep_price_deducted_tax) {
        $product['price_deducted_tax'] = $price_deducted_tax_flag;
    }

    $taxed_price = $price;

    $formula_data['ST'] = $price;

    foreach ($taxes as $k=>$tax_rate) {

        // Calculate the tax value

        if (!empty($tax_rate['skip']) || ($skip_tax_conditions)) {
            continue;
        }

        $assessment = func_calculate_assessment($tax_rate['formula'], $formula_data);

        if ($tax_rate['rate_type'] == "%") {
            $tax_rate['tax_value_precise'] = $assessment *  $tax_rate['rate_value'] / 100;
            $tax_rate['tax_value'] = $tax_rate['tax_value_precise'];
        }
        else {
            $tax_rate['tax_value'] = $tax_rate['tax_value_precise'] = $tax_rate['rate_value'];
        }

        $tax_rate['taxed_price'] = $price + $tax_rate['tax_value'];

        if ($tax_rate['display_including_tax'] == 'Y') {
            $taxed_price += $tax_rate['tax_value'];
        }

        $formula_data[$k] = $tax_rate['tax_value'];
        $tax_rate['tax_value'] = $tax_rate['tax_value_precise'] * $amount;

        $taxes[$k] = $tax_rate;
    }

    $product['taxed_price'] = price_format($taxed_price);

    return $taxes;
} // }}}

function func_taxes_get_total_values($price, $skip_tax_conditions, $l_price_deducted_tax_flag_product, $taxes, $amount=1, $skip_tax_mode='')
{//{{{
    $l_price_deducted_tax_flag = '';
    $l_total_tax_percent = 0;
    $l_total_tax_value = 0;
    foreach ($taxes as $tax_rate) {

        if (
            $skip_tax_mode == 'allow_skip_taxes'
            && !empty($tax_rate['skip'])
        ) {
            continue;
        }

        if ($skip_tax_conditions) {
            continue;
        }

        if (
            $l_price_deducted_tax_flag_product == 'Y'
            ||
            (
                !defined('XAOM')
                && $tax_rate['price_includes_tax'] != 'Y'
            )
            || (
                defined('XAOM')
                && $tax_rate['display_including_tax'] != 'Y'
            )
        ) {
            continue;
        }

        if (!preg_match("!\b(DST|ST)\b!", $tax_rate['formula'])) {
            continue;
        }

        if ($tax_rate['rate_type'] == "%") {
            $l_total_tax_percent += $tax_rate['rate_value'];
        }
        else {
            $l_total_tax_value += $tax_rate['rate_value'] * $amount;
        }

        $l_price_deducted_tax_flag = 'Y';
    }

    if (!empty($l_total_tax_percent)) {
        $l_total_tax_value += ($price - $l_total_tax_value) * (1-100 / ($l_total_tax_percent + 100) );
    }

    return array($l_total_tax_value, $l_price_deducted_tax_flag);
}//}}}

/**
 * This function generate the product tax rates array
 */
function func_get_product_tax_rates($product, $userid, $tax_options_override = array(), $customer_info = array())
{ // {{{
    global $sql_tbl, $user_account, $config, $single_mode, $global_store;
    global $active_modules;

    static $saved_tax_rates = array();

    if (!empty($active_modules['TaxCloud'])) {
        return array();
    }

    // Define input data
    $is_array = true;
    if (isset($product['productid'])) {
        $is_array = false;
        $_product = array($product['productid'] => $product);

    } else {
        $_product = array();
        foreach ($product as $k => $p) {
            $_product[$p['productid']] = $p;
        }
    }

    unset($product);

    $membershipid = $user_account['membershipid'];

    if (
        defined('XAOM')
    ) {
        $_taxes = array();

        // Select taxes data
        foreach ($_product as $_id => $_value) {
            if (!empty($_value['new']) && $_value['new']) {
                // Use current data
                $_taxes += func_query_hash("SELECT $sql_tbl[taxes].*, $sql_tbl[product_taxes].productid FROM $sql_tbl[taxes], $sql_tbl[product_taxes] WHERE $sql_tbl[taxes].taxid=$sql_tbl[product_taxes].taxid AND $sql_tbl[product_taxes].productid ='$_id' AND $sql_tbl[taxes].active='Y' ORDER BY $sql_tbl[taxes].priority", "productid");

            } elseif (!empty($_value['extra_data']['taxes'])) {
                // Use saved data
                $_taxes += array($_id => $_value['extra_data']['taxes']);
            }
        }

    } else {
        // Select taxes data
        $_taxes = func_query_hash("SELECT $sql_tbl[taxes].*, $sql_tbl[product_taxes].productid FROM $sql_tbl[taxes], $sql_tbl[product_taxes] WHERE $sql_tbl[taxes].taxid=$sql_tbl[product_taxes].taxid AND $sql_tbl[product_taxes].productid IN ('".implode("','", array_keys($_product))."') AND $sql_tbl[taxes].active='Y' ORDER BY $sql_tbl[taxes].priority", "productid");
    }

    if (empty($_taxes) || !is_array($_taxes)) {
        return array();
    }

    // Define available customer zones
    $zone_account = defined('XAOM') ? $user_account['id'] : $userid;
    $tax_rates = $address_zones = $_tax_names = array();
    foreach ($_taxes as $pid => $_tax) {
        foreach ($_tax as $k => $v) {
            $_tax_names['tax_'.$v['taxid']] = true;
        }
    }

    // Get tax names
    $_tax_names = func_get_languages_alt(array_keys($_tax_names));

    // Get the 'tax_exempt' feature of customer
    static $_customer_tax_exempt = '';

    if ($config['Taxes']['tax_operation_scheme'] == XCTaxesDefs::TAX_SCHEME_GENERAL) {

        if (empty($_customer_tax_exempt)) {
            if (defined('XAOM')) {
                $_customer_tax_exempt = $user_account['tax_exempt'];
            } else {
                $__customer_tax_exempt = func_query_first_cell("SELECT tax_exempt FROM $sql_tbl[customers] WHERE id='$zone_account'");
                $_customer_tax_exempt = empty($__customer_tax_exempt) ? 'N' : $__customer_tax_exempt;
            }
        }

    } elseif ($config['Taxes']['tax_operation_scheme'] == XCTaxesDefs::TAX_SCHEME_NO_TAXES) {

        if (empty($_customer_tax_exempt)) {
            if (defined('XAOM')) {
                $_customer_tax_exempt = empty($user_account['tax_number']) ? 'N' : 'Y';
            } else {
                $__customer_tax_exempt = func_query_first_cell("SELECT tax_number FROM $sql_tbl[customers] WHERE id='$zone_account'");
                $_customer_tax_exempt = empty($__customer_tax_exempt) ? 'N' : 'Y';
            }
        }

    } elseif ($config['Taxes']['tax_operation_scheme'] == XCTaxesDefs::TAX_SCHEME_NO_TAXES_FOR_VALIDATED) {

        if (empty($_customer_tax_exempt)) {
            if (defined('XAOM')) {
                $_customer_tax_exempt = func_does_customer_have_tax_examption($user_account);
            } else {
                $_customer_tax_exempt = func_does_customer_have_tax_examption($customer_info);
            }
        }

    } else {

        $_customer_tax_exempt = '';
    }

    foreach ($_product as $productid => $product) {

        if (
            (!empty($product['free_tax']) && $product['free_tax'] == 'Y')
            || (empty($_taxes[$productid]) || !is_array($_taxes[$productid]))
        ) {
            continue;
        }

        $product_taxes = $_taxes[$productid];
        $taxes = func_tax_override_options($product_taxes, $tax_options_override);

        // Generate tax rates array
        foreach ($taxes as $k => $v) {

            $provider_condition = '';
            if (!$single_mode) {
                $provider_condition = "AND $sql_tbl[tax_rates].provider = '$product[provider]'";
            }

            if (!isset($address_zones[$product['provider']][$v["address_type"]])) {
                $address_zones[$product['provider']][$v["address_type"]] = array_keys(func_get_customer_zones_avail($zone_account, $product['provider'], $v["address_type"]));
            }
            $zones = $address_zones[$product['provider']][$v["address_type"]];

            $tax_rate = array();

            if (!empty($zones) && is_array($zones)) {

                foreach ($zones as $zoneid) {

                    if (!$single_mode && isset($saved_tax_rates[$product['provider']][$v['taxid']][$zoneid][$membershipid])) {

                        // Get saved data (by provider name, zoneid and membershipid)
                        $tax_rate = $saved_tax_rates[$product['provider']][$v['taxid']][$zoneid][$membershipid];

                    } elseif ($single_mode && isset($saved_tax_rates[$v['taxid']][$zoneid][$membershipid])) {

                        // Get saved data (by zoneid and membershipid)
                        $tax_rate = $saved_tax_rates[$v['taxid']][$zoneid][$membershipid];

                    } else {

                        $tax_rate = func_query_first("SELECT $sql_tbl[tax_rates].rateid, $sql_tbl[tax_rates].taxid, $sql_tbl[tax_rates].formula, $sql_tbl[tax_rates].rate_value, $sql_tbl[tax_rates].rate_type FROM $sql_tbl[tax_rates] LEFT JOIN $sql_tbl[tax_rate_memberships] ON $sql_tbl[tax_rate_memberships].rateid = $sql_tbl[tax_rates].rateid WHERE $sql_tbl[tax_rates].taxid = '$v[taxid]' $provider_condition AND $sql_tbl[tax_rates].zoneid = '$zoneid' AND ($sql_tbl[tax_rate_memberships].membershipid = '$membershipid' OR $sql_tbl[tax_rate_memberships].membershipid IS NULL) ORDER BY $sql_tbl[tax_rate_memberships].membershipid DESC");
                        
                        // Use original rate_value/formula/rate_type for aom order calculation bt:0095797
                        if (defined('XAOM') && !empty($global_store['product_taxes'])) {
                            $tax_rate = func_aom_tax_rates_replace($productid, $v['tax_name'], $tax_rate);
                        }    

                        if (!$single_mode) {
                            // Save data (by provider name, zoneid and membershipid)
                            $saved_tax_rates[$product['provider']][$v['taxid']][$zoneid][$membershipid] = $tax_rate;

                        } else {
                            // Save data (by zoneid and membershipid)
                            $saved_tax_rates[$v['taxid']][$zoneid][$membershipid] = $tax_rate;
                        }
                    }

                    if (!empty($tax_rate)) {
                        break;
                    }
                }
            }

            if (empty($tax_rate) || $tax_rate['rate_value'] == 0 || $_customer_tax_exempt == 'Y') {

                if ($v['price_includes_tax'] != 'Y') {
                    continue;
                }

                $tax_rate = func_query_first("SELECT $sql_tbl[tax_rates].rateid, $sql_tbl[tax_rates].taxid, $sql_tbl[tax_rates].formula, $sql_tbl[tax_rates].rate_value, $sql_tbl[tax_rates].rate_type FROM $sql_tbl[tax_rates] LEFT JOIN $sql_tbl[tax_rate_memberships] ON $sql_tbl[tax_rate_memberships].rateid = $sql_tbl[tax_rates].rateid WHERE $sql_tbl[tax_rates].taxid='$v[taxid]' $provider_condition AND ($sql_tbl[tax_rate_memberships].membershipid = '$membershipid' OR $sql_tbl[tax_rate_memberships].membershipid IS NULL) ORDER BY $sql_tbl[tax_rates].rate_value DESC");

                // Use original rate_value/formula/rate_type for aom order calculation bt:0095797
                if (defined('XAOM') && !empty($global_store['product_taxes'])) {
                    $tax_rate = func_aom_tax_rates_replace($productid, $v['tax_name'], $tax_rate);
                }    

                $tax_rate['skip'] = true;
            }

            if (empty($tax_rate['formula'])) {
                $tax_rate['formula'] = $v['formula'];
            }

            if (!isset($tax_rate['rate_value'])) {
                $tax_rate['rate_value'] = 0;
            }

            // Do not overwrite originally saved tax names for aom order calculation bt:0096284
            if (!defined('XAOM') || empty($tax_rate['tax_display_name'])) {
                $tax_rate['tax_display_name'] = isset($_tax_names['tax_' . $v['taxid']]) ? $_tax_names['tax_' . $v['taxid']] : $v['tax_name'];
            }

            if ($is_array) {
                $tax_rates[$productid][$v['tax_name']] = func_array_merge($v, $tax_rate);
            } else {
                $tax_rates[$v['tax_name']] = func_array_merge($v, $tax_rate);
            }
        }
    }

    return $tax_rates;
} // }}}

/**
 * Override xcart_taxes.price_includes_tax or display_including_tax tax options for each tax from an module settings
 */
function func_tax_override_options($taxes, $tax_options_override)
{ // {{{

    if (empty($tax_options_override)) {
        return $taxes;
    }

    foreach ($taxes as $k => $v) {
        foreach ($tax_options_override as $tax_field_name => $overriding_value) {
            if (isset($taxes[$k][$tax_field_name])) {
                $taxes[$k][$tax_field_name] = $overriding_value;
            }
        }
    }

    return $taxes;
} // }}}

/**
 * Override xcart_taxes.price_includes_tax display_including_tax tax options for each tax from Froogle/Amazon_Checkout module settings
 */
function func_tax_get_override_display_including_tax($new_module_value)
{ // {{{
    global $sql_tbl;

    static $has_tax_to_overwrite = 'is_not_set';

    // We will overwrite only 'checked'/'Y' state for xcart_taxes.display_including_tax
    if ($new_module_value != 'Y') {
        return array();
    }

    // We will overwrite only 'checked'/'Y' state for xcart_taxes.display_including_tax
    if ($has_tax_to_overwrite === 'is_not_set') {
        $has_tax_to_overwrite = func_query_first_cell("SELECT taxid FROM $sql_tbl[taxes] WHERE display_including_tax='N' LIMIT 1") > 0;
    }

    // Check if overriding is needed
    if (!$has_tax_to_overwrite) {
        return array();
    }

    return array('display_including_tax' => $new_module_value);
} // }}}

/**
 * This function get the taxed price
 */
function func_tax_price($price, $productid=0, $disable_abs=false, $discounted_price=NULL, $userid=0, $taxes="", $price_deducted_tax=false, $amount=1, $tax_options_override=array())
{ // {{{
    global $sql_tbl, $config, $current_area;

    if (
        empty($userid)
        && !in_array($current_area, array('A','P'))
    ) {
        global $logged_userid;
        $userid = $logged_userid;
    }

    $return_taxes = array();

    $no_discounted_price = false;
    if (is_null($discounted_price)) {
        $discounted_price = $price;
        $no_discounted_price = true;
    }

    if ($productid > 0) {
        // Get product taxes
        if (defined('XAOM')) {
            $product = func_aom_get_product_info($productid);
        } else {
            $product = func_query_first("SELECT productid, provider, free_shipping, shipping_freight, distribution, '$price' as price FROM $sql_tbl[products] WHERE productid='$productid'");
        }
        $taxes = func_get_product_tax_rates($product, $userid, $tax_options_override);
    }

    x_load('user');
    $_anonymous_userinfo = func_get_anonymous_userinfo();

    $skip_tax_conditions = (empty($userid) && empty($_anonymous_userinfo) && $config['General']['apply_default_country'] != 'Y');

    $total_tax_cost = 0;
    $taxed_price = 0;

    if (is_array($taxes)) {
        $price_deducted_tax = $price_deducted_tax ? 'Y' : '';
        list($total_tax_value, $price_deducted_tax_flag) = func_taxes_get_total_values($price, $skip_tax_conditions, $price_deducted_tax, $taxes, $amount, 'allow_skip_taxes');
        if (!empty($total_tax_value)) {
            $price = $price - $total_tax_value;
        }

        list($total_tax_value, $price_deducted_tax_flag) = func_taxes_get_total_values($discounted_price, $skip_tax_conditions, $price_deducted_tax, $taxes, $amount, 'allow_skip_taxes');
        if (!empty($total_tax_value)) {
            $discounted_price = $discounted_price - $total_tax_value;
        }

        $taxed_price = $discounted_price;

        $formula_data['ST'] = $price;
        if (!$no_discounted_price) {
            $formula_data['DST'] = $discounted_price;
        }

        foreach ($taxes as $k=>$v) {

            if ($skip_tax_conditions || !empty($v['skip']) || $v['display_including_tax'] != 'Y') {
                continue;
            }

            if ($v['rate_type'] == "%") {
                $assessment = func_calculate_assessment($v['formula'], $formula_data);
                $tax_value = $assessment * $v['rate_value'] / 100;
            }
            elseif (!$disable_abs) {
                $tax_value = $v['rate_value'] * $amount;
            }

            $formula_data[$v['tax_name']] = $tax_value;

            $total_tax_cost += $tax_value;

            $taxed_price += $tax_value;

            $return_taxes['taxes'][$v['taxid']] = $tax_value;
        }
    }

    $return_taxes['taxed_price'] = $taxed_price;
    $return_taxes['net_price'] = $taxed_price - $total_tax_cost;

    return $return_taxes;
} // }}}

/**
 * This function calculates the assessment according to the formula string
 */
function func_calculate_assessment($formula, $formula_data)
{ // {{{
    $return = 0;
    if (is_array($formula_data)) {
        // Correct the default values...
        if (!isset($formula_data['DST'])) {
            $formula_data['DST'] = $formula_data['ST'];
        }

        if (empty($formula_data['SH'])) {
            $formula_data['SH'] = 0;
        }

        // Preparing math expression...
        $_formula = $formula;
        foreach ($formula_data as $unit=>$value) {
            if (!is_numeric($value)) {
                $value = 0;
            }

            $_formula = preg_replace("/\b".preg_quote($unit,'/')."\b/S", $value, $_formula);
        }

        $to_eval = "\$return = $_formula;";
        // Perform math expression...
        eval($to_eval);
    }

    return $return;
} // }}}

?>
