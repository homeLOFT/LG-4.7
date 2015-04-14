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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v5 (xcart_4_7_0), 2015-02-17 23:56:28, checkout_init.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }


if (!empty($action) && preg_match('/^taxcloud/', $action)) {

    if ('taxcloud_clear' == $action) {
        // Remove exemption certificate from the current cart
        $cart['taxcloud_certificateID'] = '';
        func_header_location('cart.php?mode=checkout');

    } else {

        // Process ajax-requests from Exemption certificates dialog

        if ('taxcloud_select' == $action) {
            // Save selected certificate ID in the cart
            $cart['taxcloud_certificateID'] = $_POST['certificateID'];

        } elseif ('taxcloud_list' == $action) {
            // Get the list of certficates of current user and print this in json format
            $certs = func_taxcloud_get_exemption_certificates($userinfo['id']);
            $certs = func_json_encode(array('cert_list' => $certs ? $certs : array()));
        
            $certsStr = preg_replace('/"Detail"/', '"ExemptionCertificateDetail"', $certs);
            $certsStr = preg_replace('/"ExemptStates"/', '"ArrayOfExemptStates"', $certsStr);

            die(sprintf('taxcloudCertificates(%s)', $certsStr));

        } elseif ('taxcloud_save' == $action) {
            // Create certificate
            $cert = func_taxcloud_get_exemption_certificate($_POST);
            func_taxcloud_add_exemption_certificate($cert, $userinfo['id']);

        } elseif ('taxcloud_remove' == $action) {
            // Remove certificate
            func_taxcloud_delete_exemption_certificate($_POST['certificateID']);
        }

        die();
    }
}
