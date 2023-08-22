<?php
/*
 * +-----------------------------------------------------------------------+
 * | BCSE Google Analytics 4                                               |
 * +-----------------------------------------------------------------------+
 * | Copyright (c) 2022 BCSE LLC. dba BCS Engineering                      |
 * +-----------------------------------------------------------------------+
 * |                                                                       |
 * | BCSE Google Analytics 4 is subject for version 2.0                    |
 * | of the BCSE proprietary license. That license file can be found       |
 * | bundled with this package in the file BCSE_LICENSE. A copy of this    |
 * | license can also be found at                                          |
 * | http://www.bcsengineering.com/license/BCSE_LICENSE_2.0.txt            |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
*/
if (!defined('XCART_START')) {
    die("Access denied");
}

if (!defined('AREA_TYPE')) {
    return;
}

if (AREA_TYPE == 'C') {
    x_session_register('_bcse_ga4_saved_logged_userid', false);
    x_session_register('_bcse_ga4_saved_paymentid', false);
    x_session_register('_bcse_ga4_saved_shippingid', false);

    if (!empty($paymentid)) {
        $_bcse_ga4_saved_paymentid = $paymentid;
    }

    if (!empty($shippingid)) {
        $_bcse_ga4_saved_shippingid = $shippingid;
    }

    if (!empty($config['Bcse_Ga4']['bcse_ga4_account'])) {
        $smarty->assign('bcse_ga4_enabled', true);
        $smarty->assign('bcse_ga4_account', $config['Bcse_Ga4']['bcse_ga4_account']);
        $smarty->assign('bcse_ga4_debug', bcse_ga4_is_debug_mode());
        $smarty->register_outputfilter('bcse_ga4_outputfilter');
    }
}
