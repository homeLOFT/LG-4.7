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

if (!empty($include_func)) {
    require_once $xcart_dir . XC_DS . 'modules' . XC_DS . 'Bcse_Ga4' . XC_DS . 'func.php';
}

if (!empty($include_init)) {
    include_once $xcart_dir . XC_DS . 'modules' . XC_DS . 'Bcse_Ga4' . XC_DS . 'init.php';
}
