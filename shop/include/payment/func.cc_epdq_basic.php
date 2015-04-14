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
 * Functions for "ePDQ basic" payment module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v3 (xcart_4_7_0), 2015-02-17 23:56:28, func.cc_epdq_basic.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Get currencies list
 */
function func_cc_epdq_basic_get_currencies($list)
{
    $currencies = array();

    foreach ($list as $key => $value) {
        $currencies[$value['code']] = $value['name'];
    }

    return $currencies;
}

/**
 * Sign request according to ePDQ basic rules
 *
 * @param array $epdq_fields
 * @param string $epdq_key
 * @param boolean $sha_out
 *
 * @return boolean
 */
function func_cc_epdq_basic_sign_request($epdq_fields, $epdq_key, $sha_out = false)
{
    if (!is_array($epdq_fields)) {
        return false;
    }

    $SHA_ALLOWED_PARAMS = array();

    // Basic e-Commerce 9: Appendix: List of parameters to be included in SHA calculations

    $SHA_IN_PARAMS = array(
        'ACCEPTANCE',
        'ACCEPTURL',
        'ADDMATCH',
        'ADDRMATCH',
        'AIACTIONNUMBER',
        'AIAGIATA',
        'AIAIRNAME',
        'AIAIRTAX',
        'AIBOOKIND*XX*',
        'AICARRIER*XX*',
        'AICHDET',
        'AICLASS*XX*',
        'AICONJTI',
        'AIDEPTCODE',
        'AIDESTCITY*XX*',
        'AIDESTCITYL*XX*',
        'AIEXTRAPASNAME*XX*',
        'AIEYCD',
        'AIFLDATE*XX*',
        'AIFLNUM*XX*',
        'AIGLNUM',
        'AIINVOICE',
        'AIIRST',
        'AIORCITY*XX*',
        'AIORCITYL*XX*',
        'AIPASNAME',
        'AIPROJNUM',
        'AISTOPOV*XX*',
        'AITIDATE',
        'AITINUM',
        'AITINUML*XX*',
        'AITYPCH',
        'AIVATAMNT',
        'AIVATAPPL',
        'ALIAS',
        'ALIASOPERATION',
        'ALIASUSAGE',
        'ALLOWCORRECTION',
        'AMOUNT',
        'AMOUNT*XX*',
        'AMOUNTHTVA',
        'AMOUNTTVA',
        'BACKURL',
        'BATCHID',
        'BGCOLOR',
        'BLVERNUM',
        'BIC',
        'BIN',
        'BRAND',
        'BRANDVISUAL',
        'BUTTONBGCOLOR',
        'BUTTONTXTCOLOR',
        'CANCELURL',
        'CARDNO',
        'CATALOGURL',
        'CAVV_3D',
        'CAVVALGORITHM_3D',
        'CERTID',
        'CHECK_AAV',
        'CIVILITY',
        'CN',
        'COM',
        'COMPLUS',
        'CONVCCY',
        'COSTCENTER',
        'COSTCODE',
        'CREDITCODE',
        'CUID',
        'CURRENCY',
        'CVC',
        'CVCFLAG',
        'DATA',
        'DATATYPE',
        'DATEIN',
        'DATEOUT',
        'DCC_COMMPERC',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERC',
        'DCC_REF',
        'DCC_SOURCE',
        'DCC_VALID',
        'DECLINEURL',
        'DEVICE',
        'DISCOUNTRATE',
        'DISPLAYMODE',
        'ECI',
        'ECI_3D',
        'ECOM_BILLTO_POSTAL_CITY',
        'ECOM_BILLTO_POSTAL_COUNTRYCODE',
        'ECOM_BILLTO_POSTAL_COUNTY',
        'ECOM_BILLTO_POSTAL_NAME_FIRST',
        'ECOM_BILLTO_POSTAL_NAME_LAST',
        'ECOM_BILLTO_POSTAL_POSTALCODE',
        'ECOM_BILLTO_POSTAL_STREET_LINE1',
        'ECOM_BILLTO_POSTAL_STREET_LINE2',
        'ECOM_BILLTO_POSTAL_STREET_NUMBER',
        'ECOM_CONSUMERID',
        'ECOM_CONSUMER_GENDER',
        'ECOM_CONSUMEROGID',
        'ECOM_CONSUMERORDERID',
        'ECOM_CONSUMERUSERALIAS',
        'ECOM_CONSUMERUSERPWD',
        'ECOM_CONSUMERUSERID',
        'ECOM_ESTIMATEDELIVERYDATE',
        'ECOM_PAYMENT_CARD_EXPDATE_MONTH',
        'ECOM_PAYMENT_CARD_EXPDATE_YEAR',
        'ECOM_PAYMENT_CARD_NAME',
        'ECOM_PAYMENT_CARD_VERIFICATION',
        'ECOM_SHIPMETHODDETAILS',
        'ECOM_SHIPMETHODSPEED',
        'ECOM_SHIPMETHODTYPE',
        'ECOM_SHIPTO_COMPANY',
        'ECOM_SHIPTO_DOB',
        'ECOM_SHIPTO_ONLINE_EMAIL',
        'ECOM_SHIPTO_POSTAL_CITY',
        'ECOM_SHIPTO_POSTAL_COUNTRYCODE',
        'ECOM_SHIPTO_POSTAL_COUNTY',
        'ECOM_SHIPTO_POSTAL_NAME_FIRST',
        'ECOM_SHIPTO_POSTAL_NAME_LAST',
        'ECOM_SHIPTO_POSTAL_NAME_PREFIX',
        'ECOM_SHIPTO_POSTAL_POSTALCODE',
        'ECOM_SHIPTO_POSTAL_STATE',
        'ECOM_SHIPTO_POSTAL_STREET_LINE1',
        'ECOM_SHIPTO_POSTAL_STREET_LINE2',
        'ECOM_SHIPTO_POSTAL_STREET_NUMBER',
        'ECOM_SHIPTO_TELECOM_FAX_NUMBER',
        'ECOM_SHIPTO_TELECOM_PHONE_NUMBER',
        'ECOM_SHIPTO_TVA',
        'ED',
        'EMAIL',
        'EXCEPTIONURL',
        'EXCLPMLIST',
        'EXECUTIONDATE*XX*',
        'FACEXCL*XX*',
        'FACTOTAL*XX*',
        'FIRSTCALL',
        'FLAG3D',
        'FONTTYPE',
        'FORCECODE1',
        'FORCECODE2',
        'FORCECODEHASH',
        'FORCEPROCESS',
        'FORCETP',
        'GENERIC_BL',
        'GIROPAY_ACCOUNT_NUMBER',
        'GIROPAY_BLZ',
        'GIROPAY_OWNER_NAME',
        'GLOBORDERID',
        'GUID',
        'HDFONTTYPE',
        'HDTBLBGCOLOR',
        'HDTBLTXTCOLOR',
        'HEIGHTFRAME',
        'HOMEURL',
        'HTTP_ACCEPT',
        'HTTP_USER_AGENT',
        'IBAN',
        'INCLUDE_BIN',
        'INCLUDE_COUNTRIES',
        'INVDATE',
        'INVDISCOUNT',
        'INVLEVEL',
        'INVORDERID',
        'ISSUERID',
        'IST_MOBILE',
        'ITEM_COUNT',
        'ITEMATTRIBUTES*XX*',
        'ITEMCATEGORY*XX*',
        'ITEMCOMMENTS*XX*',
        'ITEMDESC*XX*',
        'ITEMDISCOUNT*XX*',
        'ITEMFDMPRODUCTCATEG*XX*',
        'ITEMID*XX*',
        'ITEMNAME*XX*',
        'ITEMPRICE*XX*',
        'ITEMQUANT*XX*',
        'ITEMQUANTORIG*XX*',
        'ITEMUNITOFMEASURE*XX*',
        'ITEMVAT*XX*',
        'ITEMVATCODE*XX*',
        'ITEMWEIGHT*XX*',
        'LANGUAGE',
        'LEVEL1AUTHCPC',
        'LIDEXCL*XX*',
        'LIMITCLIENTSCRIPTUSAGE',
        'LINE_REF',
        'LINE_REF1',
        'LINE_REF2',
        'LINE_REF3',
        'LINE_REF4',
        'LINE_REF5',
        'LINE_REF6',
        'LIST_BIN',
        'LIST_COUNTRIES',
        'LOGO',
        'MANDATEID',
        'MAXITEMQUANT*XX*',
        'MERCHANTID',
        'MODE',
        'MTIME',
        'MVER',
        'NETAMOUNT',
        'OPERATION',
        'ORDERID',
        'ORDERSHIPCOST',
        'ORDERSHIPMETH',
        'ORDERSHIPTAX',
        'ORDERSHIPTAXCODE',
        'ORIG',
        'OR_INVORDERID',
        'OR_ORDERID',
        'OWNERADDRESS',
        'OWNERADDRESS2',
        'OWNERCTY',
        'OWNERTELNO',
        'OWNERTELNO2',
        'OWNERTOWN',
        'OWNERZIP',
        'PAIDAMOUNT',
        'PARAMPLUS',
        'PARAMVAR',
        'PAYID',
        'PAYMETHOD',
        'PM',
        'PMLIST',
        'PMLISTPMLISTTYPE',
        'PMLISTTYPE',
        'PMLISTTYPEPMLIST',
        'PMTYPE',
        'POPUP',
        'POST',
        'PSPID',
        'PSWD',
        'RECIPIENTACCOUNTNUMBER',
        'RECIPIENTDOB',
        'RECIPIENTLASTNAME',
        'RECIPIENTZIP',
        'REF',
        'REFER',
        'REFID',
        'REFKIND',
        'REF_CUSTOMERID',
        'REF_CUSTOMERREF',
        'REGISTRED',
        'REMOTE_ADDR',
        'REQGENFIELDS',
        'RNPOFFERT',
        'RTIMEOUT',
        'RTIMEOUTREQUESTEDTIMEOUT',
        'SCORINGCLIENT',
        'SEQUENCETYPE',
        'SETT_BATCH',
        'SID',
        'SIGNDATE',
        'STATUS_3D',
        'SUBSCRIPTION_ID',
        'SUB_AM',
        'SUB_AMOUNT',
        'SUB_COM',
        'SUB_COMMENT',
        'SUB_CUR',
        'SUB_ENDDATE',
        'SUB_ORDERID',
        'SUB_PERIOD_MOMENT',
        'SUB_PERIOD_MOMENT_M',
        'SUB_PERIOD_MOMENT_WW',
        'SUB_PERIOD_NUMBER',
        'SUB_PERIOD_NUMBER_D',
        'SUB_PERIOD_NUMBER_M',
        'SUB_PERIOD_NUMBER_WW',
        'SUB_PERIOD_UNIT',
        'SUB_STARTDATE',
        'SUB_STATUS',
        'TAAL',
        'TAXINCLUDED*XX*',
        'TBLBGCOLOR',
        'TBLTXTCOLOR',
        'TID',
        'TITLE',
        'TOTALAMOUNT',
        'TP',
        'TRACK2',
        'TXTBADDR2',
        'TXTCOLOR',
        'TXTOKEN',
        'TXTOKENTXTOKENPAYPAL',
        'TYPE_COUNTRY',
        'UCAF_AUTHENTICATION_DATA',
        'UCAF_PAYMENT_CARD_CVC2',
        'UCAF_PAYMENT_CARD_EXPDATE_MONTH',
        'UCAF_PAYMENT_CARD_EXPDATE_YEAR',
        'UCAF_PAYMENT_CARD_NUMBER',
        'USERID',
        'USERTYPE',
        'VERSION',
        'WBTU_MSISDN',
        'WBTU_ORDERID',
        'WEIGHTUNIT',
        'WIN3DS',
        'WITHROOT',
    );

    $SHA_OUT_PARAMS = array(
        'AAVADDRESS',
        'AAVCHECK',
        'AAVMAIL',
        'AAVNAME',
        'AAVPHONE',
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'AMOUNT',
        'BIC',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COMPLUS',
        'CREATION_STATUS',
        'CURRENCY',
        'CVCCHECK',
        'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATESOURCE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE',
        'DCC_VALIDHOURS',
        'DIGESTCARDNO',
        'ECI',
        'ED',
        'ENCCARDNO',
        'FXAMOUNT',
        'FXCURRENCY',
        'IBAN',
        'IP',
        'IPCTY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
        'NCERRORCARDNO',
        'NCERRORCN',
        'NCERRORCVC',
        'NCERRORED',
        'ORDERID',
        'PAYID',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
        'STATUS',
        'SUBBRAND',
        'SUBSCRIPTION_ID',
        'TRXDATE',
        'VC',
    );

    if ($sha_out) {
        $SHA_ALLOWED_PARAMS = $SHA_OUT_PARAMS;
    } else {
        $SHA_ALLOWED_PARAMS = $SHA_IN_PARAMS;
    }

    // Even though some parameters are (partially) returned in lower case by the system, for the
    // SHA-OUT calculation each parameter must be put in upper case. page 16

    $epdq_fields = array_change_key_case($epdq_fields, CASE_UPPER);

    // All params should arranged alphabetically
    ksort($epdq_fields);

    // Resulting string to sign
    $epdq_basic_string = '';

    // Basic e-Commerce 6: Security: Check prior to Payment, page 12
    // Appendix: List of parameters to be included in SHA calculations, page 18
    //
    // Parameters that do not have a value should NOT be included in the string to hash
    //
    // All parameters must be sorted following the order in the List of parameters to be included in
    // SHA calculations (SHA-OUT)

    foreach($epdq_fields as $key => $value) {
        // Skip empty values from signature string
        // not using 'empty' as it treats 0 as an empty value
        if (strlen(strval($value)) > 0) {
            // Check if key in allowed param list
            if (in_array($key, $SHA_ALLOWED_PARAMS)) {
                $epdq_basic_string .= "$key=" . $value . $epdq_key;
            }
        }
    }

    // Sign in request using SHA-512 hashing algorithm
    return strtoupper(hash('sha512', $epdq_basic_string));
}

/**
 * Check ePDQ request signature
 *
 * @param array $epdq_fields
 * @param string $epdq_key
 *
 * @return boolean
 */
function func_cc_epdq_basic_check_signature($epdq_fields, $epdq_key)
{
    if (!empty($epdq_fields['SHASIGN'])) {

        $request_signature = strtoupper($epdq_fields['SHASIGN']);
        unset($epdq_fields['SHASIGN']);
        $checked_signature = func_cc_epdq_basic_sign_request($epdq_fields, $epdq_key, true);

        return ($checked_signature == $request_signature);
    }

    return false;
}

/**
 * Translate ePDQ basic status to X-Cart one
 *
 * @param integer $epdq_status
 *
 * @return integer
 */
function func_cc_epdq_basic_process_status($epdq_status)
{
    $epdq_status_msg = array(
        0 => 'Incomplete or invalid',
        1 => 'Cancelled by client',
        2 => 'Authorisation refused',
        4 => 'Order stored',
        41 => 'Waiting client payment',
        5 => 'Authorised',
        51 => 'Authorisation waiting',
        52 => 'Authorisation not known',
        59 => 'Author. to get manually',
        6 => 'Authorised and canceled',
        61 => 'Author. deletion waiting',
        62 => 'Author. deletion uncertain',
        63 => 'Author. deletion refused',
        7 => 'Payment deleted',
        71 => 'Payment deletion pending',
        72 => 'Payment deletion uncertain',
        73 => 'Payment deletion refused',
        74 => 'Payment deleted (not accepted)',
        75 => 'Deletion processed by merchant',
        8 => 'Refund',
        81 => 'Refund pending',
        82 => 'Refund uncertain',
        83 => 'Refund refused',
        84 => 'Payment declined by the acquirer (will be debited)',
        85 => 'Refund processed by merchant',
        9 => 'Payment requested',
        91 => 'Payment processing',
        92 => 'Payment uncertain',
        93 => 'Payment refused',
        94 => 'Refund declined by the acquirer',
        95 => 'Payment processed by merchant',
        97 => 'Being processed (intermediate technical status)',
        98 => 'Being processed (intermediate technical status)',
        99 => 'Being processed (intermediate technical status)'
    );

    // X-Cart statuses
    // 1 approved, 2 declined, 3 queue, 4 cmpi declined, 5 split checkout

    $xc_code = 2;

    $epdq_status = strval($epdq_status);

    switch (substr($epdq_status, 0, 1)) {
        case 5:
            //  5 Authorised
            //  51 Authorisation waiting
            if ($epdq_status == '5' || $epdq_status == '51') {
                $xc_code = 1;
            }
            break;
        case 9:
            //  9 Payment requested
            //  91 Payment processing
            if ($epdq_status == '9' || $epdq_status == '91') {
                $xc_code = 1;
            }
            break;
    }

    $epdq_status = intval($epdq_status);

    $epdq_message = isset($epdq_status_msg[$epdq_status])? $epdq_status_msg[$epdq_status] :'Unknown status';

    return array($xc_code, $epdq_message);
}

?>
