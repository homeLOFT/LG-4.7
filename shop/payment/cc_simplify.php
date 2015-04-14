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
 * "Simplify" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v5 (xcart_4_7_0), 2015-02-17 23:56:28, cc_simplify.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

if ($REQUEST_METHOD == 'POST') {
    //
    // Form data is accepted by Simplify and submitted with $simplify_token
    //
    if (empty($active_modules['Simplify'])
        || !func_is_active_payment(XC_SIMPLIFY_CC)) {
        exit;
    }

    x_load('payment');

    x_session_register('cart');
    x_session_register('secure_oid');

    func_pm_load(XC_SIMPLIFY_CC);

    $module_params = func_simplify_get_pm_params();

    if (!empty($module_params)) {
        // Gateway is configured and has required params

        Simplify::$publicKey = $module_params[XC_Simplify_Key_Type::PUBLIC_KEY];
        Simplify::$privateKey = $module_params[XC_Simplify_Key_Type::PRIVATE_KEY];

        Simplify::$userAgent  = 'X-Cart/4.x';

        $simplify_order_referense = $config[XC_SIMPLIFY]['simplify_order_prefix'] . implode('-', $secure_oid);

        $simplify_order_total = number_format($cart['total_cost'], 2, ',', '');

        $simplify_request = array(
            'amount' => $simplify_order_total,
            'token' => $simplify_token,
            'description' => "Order No: $simplify_order_referense",
            'reference' => $simplify_order_referense,
            // A value of 'USD' is only allowed
            'currency' => 'USD'
        );
        
        $simplify_response = '';

        try {

            $simplifyPayment = Simplify_Payment::createPayment($simplify_request);

            $payment_return = array();

            if ($simplifyPayment->paymentStatus == XC_Simplify_Payment_Status::APPROVED) {
                $bill_output['code'] = 1;
                $bill_output['billmes'] = "Payment status: Approved\n\n";

                $payment_return['total'] = intval($simplifyPayment->amount) / 100;

            } else {
                $bill_output['code'] = 2;
                $bill_output['billmes'] = "Payment status: Declined\n\n";
            }

        } catch (Simplify_ApiException $e) {

            $simplify_error_msg  = 'Error code:  ' . $e->getErrorCode() . "\n";
            $simplify_error_msg .= 'Message:     ' . $e->getMessage() . "\n";

            if ($e instanceof Simplify_BadRequestException && $e->hasFieldErrors()) {

                foreach ($e->getFieldErrors() as $fieldError) {

                    $simplify_error_msg .= $fieldError->getFieldName()
                        . ": '" . $fieldError->getMessage()
                        . "' (" . $fieldError->getErrorCode()
                        . ")\n";
                }
            }

            $simplify_error_msg .= 'Reference:   ' . $e->getReference() . "\n";

            if (defined('XC_SIMPLIFY_DEBUG') || defined('DEVELOPMENT_MODE')) {
                $simplify_response = $simplify_error_msg;
            }

            $bill_output['code'] = 2;
            $bill_output['billmes'] = $simplify_error_msg;

        } catch (InvalidArgumentException $e) {

            $bill_output['code'] = 2;
            $bill_output['billmes'] = $e->getMessage();
        }

        if (!empty($simplifyPayment)) {

            if ($simplifyPayment->id) {
                $bill_output['billmes'].= " ID: " . strval($simplifyPayment->id) . "\n";
            }

            if ($simplifyPayment->paymentDate) {
                $bill_output['billmes'].= " PaymentDate: " . strval($simplifyPayment->paymentDate) . "\n";
            }

            if ($simplifyPayment->card->type) {
                $bill_output['billmes'].= " CC type: " . strval($simplifyPayment->card->type) . "\n";
            }

            if ($simplifyPayment->card->last4) {
                $bill_output['billmes'].= " CC last4: " . strval($simplifyPayment->card->last4) . "\n";
            }

            if ($simplifyPayment->authCode) {
                $bill_output['billmes'].= " AuthCode: " . strval($simplifyPayment->authCode) . "\n";
            }
        }

        if (defined('XC_SIMPLIFY_DEBUG') || defined('DEVELOPMENT_MODE')) {
            func_pp_debug_log('simplify', 'I', $simplify_request);
            func_pp_debug_log('simplify', 'R', $simplify_response);
        }
    }
    
    require $xcart_dir.'/payment/payment_ccend.php';
}

?>
