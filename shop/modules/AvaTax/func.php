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
 * AvaTax module functions 
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v16 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

// Include classes library from AvaTax SDK
require_once $avatax_module_dir . XC_DS . 'sdk' . XC_DS . 'AvaTax.php';

define('AVATAX_REQUEST_CACHE_TTL', 3600);

/*
 * Admin area configuration controller
 */
function func_avatax_configuration_controller()
{
    global $REQUEST_METHOD, $mode, $top_message;

    if ($REQUEST_METHOD == 'POST') {
        if ($mode == 'test_connection') {
            x_session_register('top_message');

            $result = func_avatax_ping();

            if ($result['success']) {
                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('lbl_success'),
                );
            } else {
                $top_message = array(
                    'type'      => 'E',
                    'content'   => $result['error'],
                );
            }

            func_header_location('configuration.php?option=AvaTax');
        }
    }
}

/*
 * AvaTax ping service
 */
function func_avatax_ping()
{
    $client = new TaxServiceSoap();

    try {
        $result = $client->ping('');

        if($result->getResultCode() != SeverityLevel::$Success) {
            $error = '';

            foreach($result->Messages() as $msg) {
                $error .= $msg->Name() . ': ' . $msg->Summary() . "\n";
            }

            $result = array('success' => false, 'error' => $error);

        } else {
            $result = array('success' => true);
        }

    } catch(SoapFault $e) {

        $result = array('success' => false, 'error' => 'Exception: ' . $e->faultstring);
    }

    return $result;
}

/*
 * Generates aggregate hash code for any number of given arguments
 */
function func_avatax_get_hashcode()
{
    return md5(serialize(func_get_args()));
}

/*
 * Gets a function and it's arguments and calls it's memoized variant
 */
function func_avatax_memoize($func, $args)
{
    // Per-request cache storage:
    static $cache = array();

    $cacheKey = func_avatax_get_hashcode($func, $args);

    if (!isset($cache[$cacheKey])) {
        // Try to get from persistent cache:
        $dbCachedResult = func_avatax_cache_get($cacheKey);

        if ($dbCachedResult === null) {
            $dbCachedResult = call_user_func_array($func, $args);

            func_avatax_cache_set($cacheKey, $dbCachedResult);
        }

        $cache[$cacheKey] = $dbCachedResult;
    }

    return $cache[$cacheKey];
}

/*
 * Returns taxes in X-Cart format (memoized)
 */
function func_avatax_get_taxes($products, $customerInfo, $shippingCost)
{
    $customerInfo = array_intersect_key($customerInfo, array(
        's_country'     => 1,
        's_city'        => 1,
        's_state'       => 1,
        's_address'     => 1,
        's_address_2'   => 1,
        's_zipcode'     => 1,
        'tax_exempt'    => 1,
        'id'            => 1,
        'email'         => 1,
    ));

    return func_avatax_memoize('func_avatax_get_taxes_internal', array($products, $customerInfo, $shippingCost));
}

/*
 * Save taxes to AvaTax (used when placing an order)
 * Taxes will later be committed when order is processed
 */
function func_avatax_save_taxes($all_products, $provider, $customerInfo, $shippingCost, $orderid)
{
    global $single_mode;

    if (!empty($all_products) && is_array($all_products)) {
        $provider_products = array();

        foreach ($all_products as $product) {
            if ($single_mode || $product['provider'] == $provider) {
                $provider_products[] = $product;
            }
        }

        func_avatax_get_taxes_internal($provider_products, $customerInfo, $shippingCost, $orderid);
    }
}

/*
 * Return products
 */
function func_avatax_return($returnid)
{
    $returnData = func_return_data($returnid);

    func_avatax_get_taxes_internal(
        array($returnData['product']),
        $returnData['userinfo'],
        0,
        $returnData['order']['orderid'],
        true,
        $returnData['order']['date']
    );
}

/*
 * Checks if tax calculations are enabled in some particular country/state
 */
function func_avatax_tax_calc_enabled_for_country_state($country, $state)
{
    global $config;

    if ($config['AvaTax']['avatax_us_and_ca_only'] == 'Y') {
        if ($country != 'US' && $country != 'CA')
            return false;
    }

    $filterStates = trim($config['AvaTax']['avatax_us_states_only']);
    if ($country == 'US' && !empty($filterStates)) {
        $filter = explode("\n", $filterStates);

        if (is_array($filter) && !empty($filter)) {
            foreach ($filter as $k => $v) {
                if (!trim($v)) {
                    unset($filter[$k]);
                } else {
                    $filter[$k] = strtoupper(trim($v));
                }
            }

            if (!empty($filter)) {
                if (!in_array($state, $filter))
                    return false;
            }
        }
    }

    return true;
}

/*
 * Checks if address validations are enabled in some particular country/state
 */
function func_avatax_av_enabled_for_country_state($country, $state)
{
    global $config;

    $filter = $config['AvaTax']['avatax_av_country'];

    if ($filter == 'ALL') {
        return in_array($country, array('US', 'CA'));
    } else {
        return $filter == $country;
    }
}

/*
 * Returns tax code associated with specific product
 */
function func_avatax_get_product_tax_code($productid)
{
    global $sql_tbl;

    return func_query_first_cell("
        SELECT avatax_tax_code
        FROM $sql_tbl[products]
        WHERE productid = '$productid'
    ");
}

/*
 * Actual func_avatax_get_taxes implementation
 */
function func_avatax_get_taxes_internal($products, $customerInfo, $shippingCost, $orderid = null, $isReturn = false, $orderDate = 0)
{
    global $config, $XCARTSESSID;

    $result = array(
        'total'     => 0,
        'shipping'  => 0,
    );

    $enabledForDest = func_avatax_tax_calc_enabled_for_country_state($customerInfo['s_country'], $customerInfo['s_state']);

    if (!func_avatax_is_tax_calculation_enabled() || !$enabledForDest)
        return $result;

    if (!class_exists('XCTaxesDefs')) {
        x_load('taxes');
    }

    $customerIsExempt = !empty($customerInfo['tax_exempt'])
        && $customerInfo['tax_exempt'] == 'Y'
        && $config['Taxes']['tax_operation_scheme'] == XCTaxesDefs::TAX_SCHEME_GENERAL;

    $origin = new AvalaraAddress();
    $origin->setLine1($config['Company']['location_address']);
    $origin->setCity($config['Company']['location_city']);
    $origin->setRegion($config['Company']['location_state']);
    $origin->setCountry($config['Company']['location_country']);
    $origin->setPostalCode($config['Company']['location_zipcode']);

    $destination = new AvalaraAddress();
    $destination->setLine1($customerInfo['s_address']);
    $destination->setLine2(!empty($customerInfo['s_address_2']) ? $customerInfo['s_address_2'] : '');
    $destination->setCity($customerInfo['s_city']);
    $destination->setRegion($customerInfo['s_state']);
    $destination->setCountry($customerInfo['s_country']);
    $destination->setPostalCode($customerInfo['s_zipcode']);

    $request = new GetTaxRequest();
    $request->setOriginAddress($origin);
    $request->setDestinationAddress($destination);

    $request->setCompanyCode($config['AvaTax']['avatax_company_code']);

    if ($isReturn)
        $docType = DocumentType::$ReturnInvoice;
    else
        $docType = !empty($orderid) ? DocumentType::$SalesInvoice : DocumentType::$SalesOrder;

    $request->setDocType($docType);
    $request->setDocCode(!empty($orderid) ? func_avatax_construct_doc_code($orderid) : '');
    $request->setDocDate(date('Y-m-d'));

    $customer_code = ( !empty($customerInfo['id']) ? ($customerInfo['id'] . '-') : '' ) .
        $customerInfo['email'] . '-' .
        $customerInfo['s_zipcode'] . '-' .
        $customerInfo['s_state'] . '-' .
        $customerInfo['s_country'];

    // CustomerCode: string [50], required. The client application customer reference code. This is required since it is the key to the Exemption Certificate Management Service in the Admin Console.
    $customer_code = substr($customer_code, 0, 50);
    $request->setCustomerCode($customer_code);
    // Discounts already applied to line items amounts:
    $request->setDiscount(0.00);
    $request->setPurchaseOrderNo('');
    $request->setExemptionNo($customerIsExempt ? '1' : '');
    $request->setDetailLevel('Tax');
    $request->setReferenceCode('');
    $request->setLocationCode('');

    if ($isReturn) {
        $request->setCommit(true);
        $taxOverride = new TaxOverride();
        $taxOverride->setTaxOverrideType(TaxOverrideType::$TaxDate);
        $taxOverride->setTaxDate(date('Y-m-d', $orderDate));
        $taxOverride->setReason('Return');
        $request->setTaxOverride($taxOverride);
    }

    $lineNum = 1;
    foreach ($products as $p) {
        $line = new Line();
        $line->setNo((string)($lineNum++));
        $line->setItemCode($p['productcode']);
        $line->setDescription($p['product']);
        $line->setTaxCode(func_avatax_get_product_tax_code($p['productid']));
        $line->setQty($isReturn ? $p['returns'][0]['amount'] : $p['amount']);
        $line->setDiscounted(false);

        $amount = $p['display_discounted_price'];
        $line->setAmount($isReturn ? -$amount : $amount);

        $lines[] = $line;
    }

    if ($shippingCost > 0) {    
        $line = new Line();
        $line->setNo('Shipping');
        $line->setItemCode('Shipping');
        $line->setDescription('Shipping costs');
        $line->setTaxCode('FR020100');
        $line->setQty(1);
        $line->setAmount((float)$shippingCost);
        $line->setDiscounted(false);

        $lines[] = $line;
    }

    $request->setLines($lines);

    try {
        $client = new TaxServiceSoap();

        $getTaxResult = $client->getTax($request);

        if ($getTaxResult->getResultCode() == SeverityLevel::$Success) {
            $result['total'] = $getTaxResult->getTotalTax();

            foreach($getTaxResult->getTaxLines() as $ctl) {
                foreach($ctl->getTaxDetails() as $ctd) {
                    $taxName = $ctd->getTaxName();
                    $taxAmount = $ctd->getTax();

                    if (!isset($result['taxes'][$taxName])) {
                        $result['taxes'][$taxName] = array(
                            'taxid'                 => $taxName,
                            'tax_name'              => $taxName,
                            'price_includes_tax'    => 'N',
                            'display_including_tax' => 'N',
                            'tax_display_name'      => $taxName,
                            'tax_cost'              => $taxAmount,
                            'shipping_taxcost'     => $taxAmount,
                            'taxcost_without_shipping'  => $taxAmount,
                        );
                    } else {
                        $result['taxes'][$taxName]['tax_cost'] += $taxAmount;
                        $result['taxes'][$taxName]['shipping_taxcost'] += $taxAmount;
                        $result['taxes'][$taxName]['taxcost_without_shipping'] += $taxAmount;
                    }
                }
            }

        } else {
            foreach($getTaxResult->getMessages() as $msg) {
                func_avatax_log($msg->getName() . ': ' . $msg->getSummary());
            }
        }

    } catch(SoapFault $exception) {
        $msg = 'GetTax Exception: ' . $exception->faultstring;

        func_avatax_log($msg);
    }

    return $result;
}

/*
 * Commit tax to AvaTax when order is processed/completed
 */
function func_avatax_commit_taxes($order_data)
{
    global $config;

    $request = new PostTaxRequest();

    $request->setDocCode(func_avatax_construct_doc_code($order_data['order']['orderid']));
    $request->setDocType(DocumentType::$SalesInvoice);
    $request->setCompanyCode($config['AvaTax']['avatax_company_code']);
    $request->setDocDate(date('Y-m-d'));
    $request->setTotalAmount($order_data['order']['total'] - $order_data['order']['tax']);
    $request->setTotalTax($order_data['order']['tax']);
    $request->setCommit(true);
    
    try {
        $client = new TaxServiceSoap();

        $result = $client->postTax($request);

        if ($result->getResultCode() != SeverityLevel::$Success) {
            foreach ($result->getMessages() as $msg) {
                func_avatax_log($msg->getName() . ': ' . $msg->getSummary());
            }
        }

    } catch(SoapFault $exception) {
        $msg = 'PostTax Exception: ' . $exception->faultstring;

        func_avatax_log($msg);
    }
}

/*
 * Cancel tax when an order is deleted
 */
function func_avatax_cancel_taxes($orderid)
{
    global $config;

    $request = new CancelTaxRequest();

    $request->setCancelCode(CancelCode::$DocVoided);
    $request->setDocCode(func_avatax_construct_doc_code($orderid));
    $request->setDocType(DocumentType::$SalesInvoice);
    $request->setCompanyCode($config['AvaTax']['avatax_company_code']);
    
    try {
        $client = new TaxServiceSoap();

        $result = $client->cancelTax($request);

        if ($result->getResultCode() != SeverityLevel::$Success) {
            foreach ($result->getMessages() as $msg) {
                func_avatax_log($msg->getName() . ': ' . $msg->getSummary());
            }
        }

    } catch(SoapFault $exception) {
        $msg = 'CancelTax Exception: ' . $exception->faultstring;

        func_avatax_log($msg);
    }
}

/*
 * Provides address validation facility
 */
function func_avatax_validate_address($address)
{
    global $config;

    $enabledForDest = func_avatax_av_enabled_for_country_state($address['country'], $address['state']);

    if ($config['AvaTax']['avatax_enable_address_validation'] != 'Y' || !$enabledForDest) {
        return array(
            'status'    => true,
            'errors'    => array(),
        );
    }

    return func_avatax_memoize('func_avatax_validate_address_internal', func_get_args());
}

/*
 * Actual address validation function implementation
 */
function func_avatax_validate_address_internal($address)
{
    $errors = array();

    try {
        $client = new AddressServiceSoap();

        $addr = new AvalaraAddress();

        $addr->setLine1($address['address']);
        $addr->setLine2(!empty($address['address_2']) ? $address['address_2'] : '');
        $addr->setCity($address['city']);
        $addr->setRegion($address['state']);
        $addr->setCountry($address['country']);
        $addr->setPostalCode($address['zipcode']);

        $textCase = TextCase::$Mixed;
        $coordinates = 1;

        $request = new ValidateRequest($addr, ($textCase ? $textCase : TextCase::$Default), $coordinates);
        $result = $client->Validate($request);

        if($result->getResultCode() != SeverityLevel::$Success) {
            foreach($result->getMessages() as $msg) {
                if ($msg->getName() && $msg->getSummary()) {

                    $translated = func_avatax_translate_exception($msg->getName());

                    $errors[] = array(
                        'fields'    => array(),
                        'error'     => $translated ? $translated : ($msg->getName() . ': ' . $msg->getSummary())
                    );
                }
            }
        }
                    
    } catch(SoapFault $exception) {
        $msg = 'Address Validation Exception: ' . $exception->faultstring;

        func_avatax_log($msg);

        $errors[] = array(
            'fields'    => array(),
            'error'     => $msg
        );
    }

    return array(
        'status' => empty($errors),
        'errors' => $errors
    );
}

/*
 * Translates AvaTax exception name to its X-Cart representation
 */
function func_avatax_translate_exception($e)
{
    $mappings = array(
        'RegionCodeError'           => 'err_avatax_region_code',
        'CountryError'              => 'err_avatax_country',
        'AddressRangeError'         => 'err_avatax_address_range',
        'AddressError'              => 'err_avatax_address',
        'InsufficientAddressError'  => 'err_avatax_insufficient_address',
        'PostalCodeError'           => 'err_avatax_postal_code',
        'UnsupportedCountryError'   => 'err_avatax_unsupported_country',
    );

    if (isset($mappings[$e]))
        return func_get_langvar_by_name($mappings[$e], false, false, true);
    else
        return null;
}

/*
 * Returns a DocCode string representing specific order in AvaTax system
 */
function func_avatax_construct_doc_code($orderid)
{
    return 'ORDER ' . $orderid;
}

/*
 * DB-based cache functions
 */
function func_avatax_cache_get($key)
{
    global $sql_tbl;

    $value = func_query_first_cell("
        SELECT value
        FROM $sql_tbl[avatax_cache]
        WHERE cache_key = '$key'
            AND expiration > " . constant('XC_TIME') . "
    ");

    return !empty($value) ? unserialize($value) : null;
}

function func_avatax_cache_set($key, $value)
{
    global $sql_tbl;

    func_array2insert(
        'avatax_cache',
        array(
            'cache_key' => $key,
            'value'     => serialize($value),
            'expiration'=> constant('XC_TIME') + constant('AVATAX_REQUEST_CACHE_TTL'),
        ),
        true
    );

    func_avatax_cache_remove_expired();
}

function func_avatax_cache_remove_expired()
{
    global $sql_tbl;

    db_query("DELETE FROM $sql_tbl[avatax_cache] WHERE expiration < " . constant('XC_TIME'));
}

/*
 * Simple error logger
 */
function func_avatax_log($msg)
{
    x_log_add('avatax', $msg);
}

/*
 * Check if module is configured
 */
function func_avatax_is_module_configured()
{   
    global $config;

    return class_exists('SoapClient')
        && !empty($config['AvaTax']['avatax_account_number'])
        && !empty($config['AvaTax']['avatax_license_key']);
}

/*
 * Checks if tax calculation should be done via AvaTax
 */
function func_avatax_is_tax_calculation_enabled()
{
    global $config;

    return func_avatax_is_module_configured()
        && $config['AvaTax']['avatax_enable_tax_calculation'] == 'Y';
}

/*
 * Module initialization 
 */
function func_avatax_init()
{

    if ('configuration.php' == basename($_SERVER['PHP_SELF']) && $_GET['option'] == 'AvaTax') {
        func_avatax_configuration_controller();
    }

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        func_add_event_listener('module.ajax.toggle', 'func_avatax_on_module_toggle');
    }
}

/*
 * Make sure that AvaTax will not be used together with TaxCloud
 */
function func_avatax_on_module_toggle($module_name, $active)
{

    $return = NULL;

    if ($active) {

        global $sql_tbl, $active_modules, $smarty;

        $modules = array('AvaTax', 'TaxCloud');

        if (in_array($module_name, $modules)) {
            foreach ($modules as $module) {
                if ($module != $module_name && !empty($active_modules[$module])) {
                    db_query("UPDATE $sql_tbl[modules] SET active='N' WHERE module_name='$module'");      
                    // Assign redirect url to reload modules page
                    $return = 'modules.php';
                }
            }

            $active_modules = func_get_active_modules(true);
            $smarty->assignByRef('active_modules', $active_modules);
        }

    }

    return $return;

}
