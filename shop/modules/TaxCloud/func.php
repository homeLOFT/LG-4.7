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
 * TaxCloud module functions 
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    6c812efa43d3738da2788208bb6afd837e502fd6, v18 (xcart_4_7_1), 2015-03-21 15:35:21, func.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

// Include classes library from TaxCloud SDK
require_once $taxcloud_module_dir . XC_DS . 'sdk' . XC_DS . 'classes.php';


/**
 * Return true if TaxCloud is configured
 * 
 * @return boolean
 */
function func_taxcloud_is_module_enabled()
{
    global $config;

    return class_exists('SoapClient')
        && !empty($config['TaxCloud']['taxcloud_api_id'])
        && !empty($config['TaxCloud']['taxcloud_api_key'])
        && !empty($config['TaxCloud']['taxcloud_usps_id']);
}

/**
 * Get taxes for cart
 * 
 * @param array   &$products     Products array
 * @param array   $customer      Customer profile data
 * @param float   $shipping_cost Shipping cost
 * @param boolean $dummy         Dummy lookup request: ignore cache and address verification errors
 *
 * @return void
 *
 * @see func_calculate_taxes
 */
function func_taxcloud_get_tax_rates(&$products, $customer, $shipping_cost, $dummy = false)
{
    global $config, $taxcloud_ignore_address_validation_error, $cart;

    $error = null;
    $cart['tax_cloud_is_not_used'] = null;

    $isAddressVerified = false;

    $addresses = array();

    $addressTypes = array('origin', 'shipping'); // billing address does not validated

    $hasIncompleteAddresses = false;

    foreach ($addressTypes as $addressType) {

        $address = func_taxcloud_get_address($addressType, $customer, $error);
        if (is_null($address)) {
            return FALSE;
        }
        $hasIncompleteAddresses = $hasIncompleteAddresses || !func_taxcloud_is_address_complete($address);

        if (isset($address)) {

            $verifiedAddress = func_taxcloud_verify_address($address, $error);
        
            if (!isset($verifiedAddress)) {
    
                if (empty($error)) {
                    $error = 'unknown error';
                }

                break;

            } else {
                $addresses[$addressType] = $verifiedAddress;
            }

        } else {
            $cart['tax_cloud_is_not_used'] = true;
            return FALSE;
        }
    }

    if ($dummy || $hasIncompleteAddresses) {
        $error = null;
    }

    if (empty($customer['id'])) {
        $customer['id'] = 0;
    }

    $lookupError = null;

    if ((empty($error) || $taxcloud_ignore_address_validation_error) && isset($address)) {
        $taxes = func_taxcloud_lookup_tax($products, $addresses['origin'], $addresses['shipping'], $shipping_cost, $customer['id'], $lookupError, $dummy);
    }

     if ($dummy || $hasIncompleteAddresses) {
        $lookupError = null;
    }

    if (!empty($error) || !empty($lookupError)) {
        func_taxcloud_add_top_message(!empty($error) ? $error : $lookupError);
    }

    if (empty($taxes) || (!empty($cart['taxcloud_certificateID']) && 0 == $taxes['total'] && 'Y' == $config['TaxCloud']['taxcloud_hide_zero_taxes'])) {
        $taxes = array(
            'total'    => 0,
            'shipping' => 0,
        );
    }

    return $taxes;
}

/**
 * Add top message
 *
 * @param string $message Message
 *  
 * @return void
 */
function func_taxcloud_add_top_message($message)
{
    global $top_message;

    $top_message['content'] = '[TaxCloud] ' . $message;
    $top_message['type'] = 'E';
}

/**
 * Get Address object from userdata array or company address
 * 
 * @param string $addressType Address stype (origin, billing, shipping)
 * @param array  $userdata    Array of user profile data
 * @param string &$error      Error message
 *  
 * @return Address
 */
function func_taxcloud_get_address($addressType, $userdata, &$error)
{
    if ('origin' == $addressType) {

        global $config;

        if ('US' !== $config['Company']['location_country']) {
            $error = func_get_langvar_by_name('taxcloud_err_origin_address', false, false, true);
            return null;
        }

        $userdata = array();
        $userdata['o_address'] = $config['Company']['location_address'];
        $userdata['o_city'] = $config['Company']['location_city'];
        $userdata['o_state'] = $config['Company']['location_state'];
        $userdata['o_zipcode'] = $config['Company']['location_zipcode'];

        $prefix = 'o_';

    } else {
        $prefix = ('billing' == $addressType) ? 'b_' : 's_';
    }

    if (!empty($userdata[$prefix . 'country']) && 'US' !== $userdata[$prefix . 'country']) {
        // Set a special error for non-US addresses
        $error = 'NON-US ADDRESS';
        return null;
    }

    $address = new Address();

    $address->setAddress1(!empty($userdata[$prefix . 'address']) ? $userdata[$prefix . 'address'] : '');

    if (!empty($userdata[$prefix . 'address_2'])) { 
        $address->setAddress2($userdata[$prefix . 'address_2']);
    }

    $address->setCity($userdata[$prefix . 'city']);
    $address->setState($userdata[$prefix . 'state']);

    preg_match('/^(\d{5})([- ](\d{4}))?$/', $userdata[$prefix . 'zipcode'], $matches);

    if (isset($matches[1])) {
        $address->setZip5($matches[1]);

        if (isset($matches[3])) {
            $address->setZip4($matches[3]);
        }

    } else {
        $address = null;
        $error = func_get_langvar_by_name('taxcloud_err_wrong_zipcode', false, false, true);
    }

    return $address;
}

/**
 * Check if address is complete
 *
 * @param Address $address Address object
 *
 * @return boolean
 */
function func_taxcloud_is_address_complete($address)
{
    $result = true;

    $fields = array(
        'Address1',
        'City',
        'State',
        'Zip5',
    );

    foreach ($fields as $field) {
        // One of getAddress1(), getCity(), getState() or getZip5()
        $methodName = 'get' . $field;
        $value = $address->$methodName();
        if (empty($value)) {
            $result = false;
            break;
        }
    }

    return $result;
}

/**
 * Verify an address using TaxCloud service. Addresses need to be verified through this service
 * before they are used for other web service calls since they require the complete 9 digit zip code
 * to look up the tax accurately. 
 *
 * @param Address $address Address object
 * @param string $err
 *
 * return Address|null
 */
function func_taxcloud_verify_address($address, &$error)
{
    global $config, $taxcloud_ignore_address_validation_error;

    $origAddress = $address;

    $error = null;

    // Verify the address through the TaxCloud verify address service
    $params = array(
        'uspsUserID' => $config['TaxCloud']['taxcloud_usps_id'],
        'address1'   => strval($address->getAddress1()),
        'address2'   => strval($address->getAddress2()),
        'city'       => strval($address->getCity()),
        'state'      => strval($address->getState()),
        'zip5'       => strval($address->getZip5()),
        'zip4'       => strval($address->getZip4()),
    );

    $verifyaddressresponse = func_taxcloud_do_transaction('verifyAddress', $params, $error);

    if (isset($verifyaddressresponse) && empty($error)) {
        
        if ($verifyaddressresponse->{'VerifyAddressResult'}->ErrNumber == 0) {
            // Use the verified address values
            $address->setAddress1($verifyaddressresponse->{'VerifyAddressResult'}->Address1);
            $address->setAddress2(isset($verifyaddressresponse->{'VerifyAddressResult'}->Address2) ? $verifyaddressresponse->{'VerifyAddressResult'}->Address2 : null);
            $address->setCity($verifyaddressresponse->{'VerifyAddressResult'}->City);
            $address->setState($verifyaddressresponse->{'VerifyAddressResult'}->State);
            $address->setZip5($verifyaddressresponse->{'VerifyAddressResult'}->Zip5);
            $address->setZip4($verifyaddressresponse->{'VerifyAddressResult'}->Zip4);

        } else {
            $error = $verifyaddressresponse->{'VerifyAddressResult'}->ErrDescription;
            if (!$taxcloud_ignore_address_validation_error) {
                $address = null;
            }
        }
    }

    if (!empty($error)) {

        $addressFields = array(
            $origAddress->getAddress1(),
            $origAddress->getAddress2(),
            $origAddress->getCity(),
            $origAddress->getState(),
            $origAddress->getZip5(),
            $origAddress->getZip4(),
        );

        $addressStr = '';

        foreach ($addressFields as $k => $field) {
            if (!empty($field)) {
                $addressStr .= $field . ', ';
            }
        }

        $addressStr = preg_replace('/(, )*$/', '', $addressStr);

        $error = func_get_langvar_by_name('taxcloud_err_wrong_address', array('address' => $addressStr), false, true) . $error;
    }

    return $address;
}

/**
 * Look up tax using TaxCloud web services
 *
 * @param array   &$product    Products array
 * @param Address $origin      Origin address object
 * @param Address $destination Destination address object
 * @param float   $shipping    Shipping cost
 * @param string  &$error      Error message
 * @param boolean $ignoreCache Flag: ignore cache for lookup transaction
 */
function func_taxcloud_lookup_tax(&$products, $origin, $destination, $shipping, $userid, &$error, $ignoreCache = false)
{
    global $config, $cart, $XCARTSESSID;

    $result = array();

    if (!is_null($origin) && !is_null($destination)) {

        $cartItems = Array();

        $maxCartId = 0;

        $productsHash = array();

        foreach ($products as $k => $product) {

            if (!empty($product['deleted'])) {
                continue;
            }

            $cartItem = new CartItem();

            $itemId = $k;

            $productsHash[$itemId] = $k;

            if (!empty($product['options'])) {
                $productid = (string)$product['productid'] . '-' . implode(':', array_keys($product['options'])) . '-' . implode(':', $product['options']); 

            } else {
                $productid = $product['productid'];
            }

            $productid = substr($productid, 0, 50);
            $cartItem->setItemID($productid); // Product ID
            $cartItem->setIndex($itemId); // Each cart item must have a unique index

            $tic = func_taxcloud_get_tic($product['productid']);

            $cartItem->setTIC($tic);

            $cartItem->setPrice($product['display_price']); // Price of each item
            $cartItem->setQty($product['amount']); // Quantity

            $cartItems[] = $cartItem;

            $maxCartId = max($itemId, $maxCartId);
        }

        //Shipping as a cart item - shipping needs to be taxed
        $cartItem = new CartItem();
        $cartItem->setItemID('shipping');
        $cartItem->setIndex($maxCartId + 1);
        $cartItem->setTIC(11010);
        $cartItem->setPrice($shipping);  // The shipping cost from your cart
        $cartItem->setQty(1);
        $cartItems[] = $cartItem;

        if (empty($cart['taxcloud_cartid'])) {
            $cart['taxcloud_cartid'] = md5(sprintf('%s-%d-%d', $XCARTSESSID, $userid, XC_TIME));
        }

        $params = array(
            'customerID'        => $userid,
            'cartID'            => $cart['taxcloud_cartid'],
            'cartItems'         => $cartItems,
            'origin'            => $origin,
            'destination'       => $destination,
            'deliveredBySeller' => ($config['TaxCloud']['taxcloud_delivered_by_seller'] == 'Y'),
        );

        if (!isset($cart['taxcloud_certificateID']) && $userid) {
            $cart['taxcloud_certificateID'] = func_taxcloud_get_customer_certificateid($userid);
        }

        if (!empty($cart['taxcloud_certificateID'])) {

            $certs = func_taxcloud_get_exemption_certificates($userid);

            if (is_array($certs)) {
                foreach ($certs as $cert) {
                    if ($cert->CertificateID == $cart['taxcloud_certificateID']) {
                        $params['exemptCert'] = func_taxcloud_get_exemption_certificate_from_obj($cert);
                        break;
                    }
                }
            }

            if (empty($params['exemptCert'])) {
                $cart['taxcloud_certificateID'] = '';
            }
        }

        $lookupResponse = func_taxcloud_do_transaction('lookup', $params, $error, $ignoreCache);

        if (empty($error)) {

            $lookupResult = $lookupResponse->{'LookupResult'};

            if ($lookupResult->ResponseType == 'OK' || $lookupResult->ResponseType == 'Informational') {

                $cartItemsResponse = $lookupResult->{'CartItemsResponse'};
                $cartItemResponse = $cartItemsResponse->{'CartItemResponse'};

                $taxID = 'TAXCLOUD_TAX';

                $taxName = func_get_languages_alt('tax_' . 'taxcloud');

                $productTaxesPattern = array(
                    'taxid' => 'taxcloud',
                    'tax_name' => $taxID,
                    'price_includes_tax' => 'N',
                    'display_including_tax' => 'N',
                    'tax_display_name' => $taxName,
                    'tax_value_precise' => 0,
                    'tax_value' => 0,
                    'taxed_price' => 0,
                );

                $_taxes = $productTaxesPattern;
                $_taxes['tax_cost'] = 0;
                $_taxes['taxcost_without_shipping'] = 0;
                $_taxes['shipping_taxcost'] = 0;


                if (is_array($cartItemResponse)) {

                    foreach ($cartItemResponse as $c) {

                        $cartId = $c->CartItemIndex;

                        if (isset($productsHash[$cartId])) {
                            // Product tax
                            $product = $products[$productsHash[$cartId]];

                            $product['taxed_price'] = $c->TaxAmount + $product['discounted_price'];
                            $product['price_deducted_tax'] = $product['discounted_price'];
                            // $products[$key]['display_price'] = $c->TaxAmount + $product['dissplay_price'];

                            $_productTaxes = $productTaxesPattern;
                            $_productTaxes['tax_value_precise'] = $c->TaxAmount;
                            $_productTaxes['tax_value'] = $c->TaxAmount;
                            $_productTaxes['taxed_price'] = $product['taxed_price'];

                            $product['taxes'] = array($taxID => $_productTaxes);

                            $products[$productsHash[$cartId]] = $product;

                            $_taxes['taxcost_without_shipping'] += $c->TaxAmount;

                        } else {
                            // Shipping tax
                            $_taxes['shipping_taxcost'] += $c->TaxAmount;
                        }

                        $_taxes['tax_cost'] += $c->TaxAmount;
                    }
                }

                $taxes = array(
                    'total'    => $_taxes['tax_cost'],
                    'shipping' => $_taxes['shipping_taxcost'],
                    'taxes'    => array(
                        $taxID => $_taxes,
                    ),
                );

                $result = $taxes;

            } else {

                $errMsgs = $lookupResult->{'Messages'};
                $errMsg = array();

                foreach($errMsgs as $err) {
                    $errMsg[] = func_get_langvar_by_name('taxcloud_err_lookup', false, false, true) . $err->{'Message'};
                }

                $error = implode('<br />', $errMsg);
            }
        }
    }

    return $result;
}

/**
 * Authorized
 *
 * @param integer $userid  Customer ID
 * @param integer $cartid  Unique cart ID
 * @param integer $orderid Order ID
 * @param string  &$error  Error message
 *
 * @return boolean
 */
function func_taxcloud_authorized($userid, $cartid, $orderid, &$errMsg)
{
    $result = false;
    $error = '';

    $dup = 'This purchase has already been marked as authorized';

    $params = array(
        'customerID'     => $userid,
        'cartID'         => $cartid,
        'orderID'        => $orderid,
        'dateAuthorized' => date('c'), // Current date - example of format: '2010-09-08T00:00:00'
    );

    $authorizedResponse = func_taxcloud_do_transaction('authorized', $params, $error);

    if (empty($error)) {

        $authorizedResult = $authorizedResponse->{'AuthorizedResult'};

        if ($authorizedResult->ResponseType == 'OK') {
            $result = true;

        } else {

            $msgs = $authorizedResult->{'Messages'};
            $respMsg = $msgs->{'ResponseMessage'};

            // duplicate means the the previous call was good. Therefore, consider this to be good
            if (trim ($respMsg->Message) == $dup) {
                $result = true;

            } else {
                $error = $respMsg->Message;
            }
        }
    }

    return $result;
}

/**
 * Captured
 *
 * @param integer $orderid Order ID
 * @param string  &$error  Error message
 *
 * @return boolean
 */
function func_taxcloud_captured($orderid, &$errMsg)
{
    $result = false;
    $error = '';

    $dup = "This purchase has already been marked as captured";

    $params = array(
        'orderID'      => $orderid,
        'dateCaptured' => date('c'), // Current date - example of format: '2010-09-08T00:00:00';
    );

    $capturedResponse = func_taxcloud_do_transaction('captured', $params, $error);

    if (empty($error)) {

        $capturedResult = $capturedResponse->{'CapturedResult'};

        if ($capturedResult->ResponseType == 'OK') {
            $result = true;

        } else {

            $msgs = $capturedResult->{'Messages'};
            $respMsg = $msgs->{'ResponseMessage'};

            // duplicate means the the previous call was good. Therefore, consider this to be good
            if (trim ($respMsg->Message) == $dup) {
                $result = true;

            } else {
                $error = $respMsg->Message;
            }
        }
    }

    return $result;
}

/**
 * AuthorizedWithCapture
 *
 * @param integer $userid  Customer ID
 * @param integer $cartid  Unique cart ID
 * @param integer $orderid Order ID
 * @param string  &$error  Error message
 *
 * @return boolean
 */
function func_taxcloud_authorized_with_capture($userid, $cartid, $orderid, &$error)
{
    $result = false;
    $error = '';

    $dup = "This purchase has already been marked as authorized";

    // Current date - example of format: '2010-09-08T00:00:00';
    $dateAuthorized = date('c');

    $params = array(
        'customerID'     => $userid,
        'cartID'         => $cartid,
        'orderID'        => $orderid,
        'dateAuthorized' => $dateAuthorized,
        'dateCaptured'   => $dateAuthorized,
    );

    $authorizedResponse = func_taxcloud_do_transaction('authorizedWithCapture', $params, $error);

    if (empty($error)) {

        $authorizedResult = $authorizedResponse->{'AuthorizedWithCaptureResult'};

        if ($authorizedResult->ResponseType == 'OK') {
            $result = true;

        } else {

            $msgs = $authorizedResult->{'Messages'};
            $respMsg = $msgs->{'ResponseMessage'};

            // duplicate means the the previous call was good. Therefore, consider this to be good
            if (trim ($respMsg->Message) == $dup) {
                $result = true;

            } else {
                $error = $respMsg->Message;
            }
        }
    }

    return $result;
}

/**
 * Create ExemptionCertificate object and initialize it with specified data
 *
 * @param array $data Array of data
 *  
 * @return ExemptionCertificate
 */
function func_taxcloud_get_exemption_certificate($data)
{
    $cert = new ExemptionCertificate();

    $detail = new ExemptionCertificateDetail();

    foreach ($data as $key => $value) {
        $method = 'set' . ucfirst($key);
        if (method_exists($detail, $method)) {
            $detail->{$method}($value);
        }
    }

    $detail->setSinglePurchase(!isset($data['BlanketPurchase']));

    $taxID = new TaxID();
    $taxID->setTaxType($data['TaxType']);
    $taxID->setIDNumber($data['IDNumber']);
    $taxID->setStateOfIssue($data['StateOfIssue']);

    $detail->setPurchaserTaxID($taxID);

    $state = new ExemptState();
    $state->setStateAbbr($data['ExemptState']);

    $detail->addExemptState($state);

    $detail->setCreatedDate(date('c'));

    $cert->setDetail($detail);

    return $cert;
}

/**
 * Create ExemptionCertificate object and initialize it with specified data
 *
 * @param array $obj Object received in TaxCloud response (getExemptionCertificates)
 *  
 * @return ExemptionCertificate
 */
function func_taxcloud_get_exemption_certificate_from_obj($obj)
{
    $cert = new ExemptionCertificate();

    $detail = new ExemptionCertificateDetail();

    $cert->setCertificateID($obj->CertificateID);

    if ($obj->Detail->ExemptStates) {
        foreach ((array)$obj->Detail->ExemptStates as $stateObj) {
            $state = new ExemptState($stateObj->StateAbbr, $stateObj->ReasonForExemption, $stateObj->IdentificationNumber);
            $detail->addExemptState($state);
        }
    }

    $detail->setSinglePurchase($obj->Detail->SinglePurchase);
    $detail->setSinglePurchaseOrderNumber($obj->Detail->SinglePurchaseOrderNumber);

    $detail->setPurchaserFirstName($obj->Detail->PurchaserFirstName);
    $detail->setPurchaserLastName($obj->Detail->PurchaserLastName);
    $detail->setPurchaserAddress1($obj->Detail->PurchaserAddress1);
    //$detail->setPurchaserAddress2($obj->Detail->PurchaserAddress2);
    $detail->setPurchaserCity($obj->Detail->PurchaserCity);
    $detail->setPurchaserState($obj->Detail->PurchaserState);
    $detail->setPurchaserZip($obj->Detail->PurchaserZip);

    $businessType = new BusinessType($obj->Detail->PurchaserBusinessType);
    $detail->setPurchaserBusinessType($businessType);

    $exemptionReason = new ExemptionReason($obj->Detail->PurchaserExemptionReason);
    $detail->setPurchaserExemptionReason($exemptionReason);

    $detail->setPurchaserExemptionReasonValue($obj->Detail->PurchaserExemptionReasonValue);
    $detail->setCreatedDate($obj->Detail->CreatedDate);

    $taxID = new TaxID();
    $taxID->setTaxType($obj->Detail->PurchaserTaxID->TaxType);
    $taxID->setIDNumber($obj->Detail->PurchaserTaxID->IDNumber);

    $detail->setPurchaserTaxID($taxID);

    $cert->setDetail($detail);

    return $cert;
}

/**
 * Add exemption certificate
 *
 * @param ExemptionCertificate $exemptionCertificate Certificate object
 * @param integer              $customerID           Customer ID
 *
 * @return boolean
 */
function func_taxcloud_add_exemption_certificate($exemptionCertificate, $customerID)
{
    global $cart;

    $result = false;

    $params = array(
        'customerID' => $customerID,
        'exemptCert' => $exemptionCertificate,
    );

    $addExemptionResponse = func_taxcloud_do_transaction('addExemptCertificate', $params, $error);

    if (isset($addExemptionResponse) && empty($error)) {

        $addExemptionResult = $addExemptionResponse->{'AddExemptCertificateResult'};

        if ($addExemptionResult->ResponseType == 'OK') {

            $cart['taxcloud_certificateID'] = $addExemptionResult->CertificateID;
            $cart['taxcloud_renew_certs'] = true;

            $result = true;

        } else {

            $msgs = $addExemptionResult->{'Messages'};
            $respMsg = $msgs->{'ResponseMessage'};
            $error = $respMsg->Message;
        }
    }

    return $result;
}

/**
 * Get a list of exemption certificates for the given customer.
 * This list contains blanket and single use certificates. Normally you would only display blanket certificates to the users.
 *
 * @param integer $customerID Customer ID
 *
 * @return array
 */
function func_taxcloud_get_exemption_certificates($customerID)
{
    global $cart;

    $result = array();

    $error = null;

    if (empty($customerID)) {
        return $result;
    }

    $params = array(
        'customerID' => $customerID,
    );

    if (!empty($cart['taxcloud_renew_certs'])) {
        $ignoreCache = true;
        $cart['taxcloud_renew_certs'] = false;

    } else {
        $ignoreCache = false;
    }

    $getExemptCertificatesResponse = func_taxcloud_do_transaction('getExemptCertificates', $params, $error, $ignoreCache);

    if (isset($getExemptCertificatesResponse) && empty($error)) {

        $getCertificatesRsp = $getExemptCertificatesResponse->{'GetExemptCertificatesResult'};
        $exemptCertificatesArray = $getCertificatesRsp->{'ExemptCertificates'};

        $exemptCertificates = @$exemptCertificatesArray->{'ExemptionCertificate'};

        if (is_array($exemptCertificates)) {
            $result = $exemptCertificates;

        } elseif (!empty($exemptCertificates)) {
            $result = array($exemptCertificates);
        }
    }

    return $result;
}

/**
 * Delete a stored exemption certificate for a customer
 * 
 * @param string $certID Certificate ID
 *
 * @return boolean
 */
function func_taxcloud_delete_exemption_certificate($certID)
{
    global $cart;

    $result = false;

    $params = array( 
        'certificateID' => $certID,
    );

    $deleteExemptCertificateResponse = func_taxcloud_do_transaction('deleteExemptCertificate', $params, $error, true);

    if (isset($deleteExemptCertificateResponse) && empty($error)) {
        $result = true;
        $cart['taxcloud_renew_certs'] = true;
    }

    return $result;
}

/** 
 * Returned
 *
 * @param integer $orderid  Order ID
 * @param array   $products Ordered products
 * @param float   $shipping Shipping cost (or null if shipping tax should no be returned)
 * @param string  &$error   Error message
 *
 * @return boolean
 */
function func_taxcloud_returned($orderid, $products, $shipping = null, &$error)
{
    $result = false;

    $cartItems = array();

    $maxCartId = 0;

    foreach ($products as $key => $product) {

        if (!isset($product['taxcloud_returned'])) {
    
            $cartItem = new CartItem();

            if (!empty($product['options'])) {
                $productid = (string)$product['productid'] . '-' . implode('', array_keys($product['options'])) . '-' . implode('', $product['options']); 

            } else {
                $productid = $product['productid'];
            }

            $productid = substr($productid, 0, 50);
            $cartItem->setItemID($productid);
            $cartItem->setIndex($key);
            $cartItem->setTIC(func_taxcloud_get_tic($product['productid']));  
            $cartItem->setPrice($product['price']);
            $cartItem->setQty($product['amount']);

            $cartItems[] = $cartItem;

            $maxCartId = max($key, $maxCartId);
        }
    }

    if (isset($shipping)) {

        $cartItem = new CartItem();

        $cartItem->setItemID('shipping');
        $cartItem->setIndex($maxCartId + 1);
        $cartItem->setTIC(11010);
        $cartItem->setPrice($shipping);
        $cartItem->setQty(1);

        $cartItems[] = $cartItem;

    }

    $params = array(
        'orderID'      => $orderid,
        'cartItems'    => $cartItems,
        'returnedDate' => date('c'),
    ); 

    $returnedResponse = func_taxcloud_do_transaction('Returned', $params, $error, true);

    if (isset($returnedResponse) && empty($error)) {

        $returnedResult = $returnedResponse->{'ReturnedResult'};

        if ($returnedResult->ResponseType == 'OK') {
            $result = true;

        } else {

            $msgs = $returnedResult->{'Messages'};
            $respMsg = $msgs->{'ResponseMessage'};
            $error = $respMsg->Message;
        }
    }

    return $result;
}


/**
 * Retrieves a product's TIC ID.
 *
 * @param $product_id
 *
 * @return string
 */
function func_taxcloud_get_tic($product_id)
{
    global $sql_tbl, $config;

    $tic = func_query_first_cell("SELECT taxcloud_tic FROM $sql_tbl[products] WHERE productid = $product_id");

    if ('Y' == $config['TaxCloud']['taxcloud_force_default_tic']) {
        $tic = $config['TaxCloud']['taxcloud_default_tic'];
    }

    if (empty($tic)) {
        $tic = '00000';
    }

    return $tic;
}

/**
 * Get certificate ID which user has used in last order
 * 
 * @param integer $userid Customer ID
 *
 * @return string
 */
function func_taxcloud_get_customer_certificateid($userid)
{
    global $sql_tbl;

    $certificateID = '';

    // Find last order of customer
    $lastOrderID = func_query_first_cell("SELECT orderid FROM $sql_tbl[orders] WHERE userid='$userid' ORDER BY orderid DESC");

    // Get stored 'taxcloud_certificateID' value (if it is exists) from last otder
    if (!empty($lastOrderID)) {
        $certificateID = func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid='$lastOrderID' AND khash='taxcloud_certificateID'");
    }

    return $certificateID;
}

/**
 * Initialize and return SoapClient object
 * 
 * @return SoapClient
 */
function func_taxcloud_get_soap_client()
{
    global $taxcloud_wsdl;
    static $taxcload_soap_client;

    if (!isset($taxcload_soap_client)) {
        $taxcload_soap_client = new SoapClient($taxcloud_wsdl, array('trace' => true));
    }

    return $taxcload_soap_client;
}

/**
 * Perform transaction
 * 
 * @param string  $method      Transaction method
 * @param array   $params      Transaction parameters
 * @param string  &$error      Error message
 * @param boolean $ignoreCache Flag: ignore cache if true
 *
 * @return object
 */
function func_taxcloud_do_transaction($method, $params, &$error, $ignoreCache = false)
{
    global $config;

    $client = func_taxcloud_get_soap_client();

    $params['apiLoginID'] = $config['TaxCloud']['taxcloud_api_id'];
    $params['apiKey']     = $config['TaxCloud']['taxcloud_api_key'];

    $cacheKey = $method . '-' . md5(serialize($params));

    if (!$ignoreCache) {
        $response = func_taxcloud_get_cached_response($cacheKey);
    }

    if (!isset($response)) {

        try {
            $response = $client->{$method}($params);
            taxcloud_log(array('request' => $method, 'params' => $params, 'response' => $response));

        } catch (Exception $e) {

            try {
                $response = $client->{$method}($params);
                taxcloud_log(array('request' => 'Retry ' . $method, 'params' => $params, 'response' => $response));
    
            } catch (Exception $e) {
                $error = $e->getMessage();
                taxcloud_log(array('request' => $method, 'params' => $params, 'error' => $error));
            }
        }

        if (empty($error)) {
            func_taxcloud_save_response_in_cache($cacheKey, $response);
        }
    }

    return $response;
}

/**
 * Get value from cache
 * 
 * @param string $key Cache cell key
 *
 * @return object
 */
function func_taxcloud_get_cached_response($key)
{
    global $sql_tbl, $XCARTSESSID;

    $data = unserialize(func_query_first_cell("SELECT cell_value FROM $sql_tbl[taxcloud_cache] WHERE sessid='$XCARTSESSID' AND cell_key = '" . addslashes($key) . "'"));

    return !empty($data) && is_object($data) ? $data : null;
}

/**
 * Save response object to the cache
 * 
 * @param string $key   Cache cell key
 * @param object $value Soap object - response from TaxCloud server
 *
 * @return void
 */
function func_taxcloud_save_response_in_cache($key, $value)
{
    global $sql_tbl, $XCARTSESSID;

    if (!empty($value)) {
        func_array2insert('taxcloud_cache', array('cell_key' => $key, 'sessid' => $XCARTSESSID, 'cell_value' => addslashes(serialize($value))), true);
    }
}

/**
 * Add data to log file 
 *
 * @param mixed   $data        Data for logging
 * @param boolean $checkOption Flag: when true then $data will be added to log file if $taxcloudLogAllRequests is also true 
 *
 * @return void
 */
function taxcloud_log($data, $checkOption = false)
{
    global $taxcloudLogAllRequests;

    if (!$checkOption || $taxcloudLogAllRequests) {
        x_log_add('TAXCLOUD', var_export($data, true));
    }
}

