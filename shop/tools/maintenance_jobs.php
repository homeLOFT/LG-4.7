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
 * Run maintenance jobs 
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v12 (xcart_4_7_0), 2015-02-17 23:56:28, maintenance_jobs.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require_once '../top.inc.php';

define('DO_NOT_START_SESSION', 1);
define('QUICK_START', true);
define('SKIP_CHECK_REQUIREMENTS.PHP', true);
define('USE_SIMPLE_DB_INTERFACE', true);

if (PHP_SAPI !== 'cli') {
    require_once $xcart_dir . '/init.php';
} else {
    @require_once $xcart_dir . '/init.php';
}

x_load('backoffice');
func_set_time_limit(SECONDS_PER_DAY);

if (PHP_SAPI !== 'cli') {
    $image = file_get_contents($xcart_dir . $smarty_skin_dir . '/images/spacer.gif');
    header('Content-type: image/gif');
    header('Content-Length: ' . strlen($image));
    echo $image;
}

$objOptimizeSQL = new XCOptimizeSQLTables();

if (!$objOptimizeSQL->allowOptimize()) {
    exit();
}

$objOptimizeSQL->lock();

$objOptimizeSQL->runOptimize();

$objOptimizeSQL->unlock();

class XCOptimizeSQLTables {
    const IS_LOCKED = 'IS_LOCKED';
    const IS_FREE = 'IS_FREE';
    const IDLE_TIME = 1200; // 20 min
    const TIME2WAIT_ACTIVE_SESSIONS = 4;
    const TIME2WAIT_2LOAD_CURRENT_PAGE = 20;

    private $max_active_sessions = 3; // Number of max active sessions to run optimize

    public function __construct() { // {{{

        if (PHP_SAPI === 'cli') {
            $this->max_active_sessions = 0;
        }
        // Wait to load current page including images before first SUM(expiry) quiry is run
        sleep(self::TIME2WAIT_2LOAD_CURRENT_PAGE);
    } // }}}

    public function allowOptimize() { // {{{
        global $sql_tbl;

        if (!$this->hasActiveSessions(XC_TIME)) {
            $lock_status = func_get_cache_func('', 'getSqlOptimizationLock');
            $lock_status = $lock_status ? $lock_status['data'] : $lock_status;
            if ($lock_status === self::IS_LOCKED) {
                $is_allow = FALSE;
            } else {
                $is_allow = TRUE;
            }
        } else {
            $is_allow = FALSE;
        }

        return $is_allow;
    } // }}}

    public function lock() { // {{{
        func_save_cache_func(self::IS_LOCKED, '', 'getSqlOptimizationLock');
    } // }}}

    public function unlock() { // {{{
        func_save_cache_func(self::IS_FREE, '', 'getSqlOptimizationLock');
    } // }}}

    public function runOptimize() { // {{{
        $priority_tables = array(XC_TBL_PREFIX.'zones', XC_TBL_PREFIX.'variants', XC_TBL_PREFIX.'pricing', XC_TBL_PREFIX.'products', XC_TBL_PREFIX.'products_categories', XC_TBL_PREFIX.'categories', XC_TBL_PREFIX.'quick_prices', XC_TBL_PREFIX.'quick_flags', XC_TBL_PREFIX.'extra_fields', XC_TBL_PREFIX.'extra_field_values');

        // Optimize priority tables
        foreach($priority_tables as $table) {

            func_optimize_table($table);

            if ($this->hasActiveSessions(XC_TIME)) {
                return FALSE;
            }

            sleep(self::TIME2WAIT_ACTIVE_SESSIONS);
        }

        $tbls = func_query_column("SHOW TABLES");

        if (empty($tbls))
            return TRUE;

        shuffle($tbls);

        // Clear garbage from tables
        if (!$this->hasActiveSessions(XC_TIME)) {
            func_session_delete_expired_unknown_sid();
        }

        if (!$this->hasActiveSessions(XC_TIME)) {
            func_session_delete_expired_session_history();
        }

        // Optimize other tables
        foreach ($tbls as $v) {
            if (in_array($v, $priority_tables))
                continue;

            func_optimize_table($v);

            if ($this->hasActiveSessions(XC_TIME)) {
                return FALSE;
            }

            sleep(self::TIME2WAIT_ACTIVE_SESSIONS);
        }

       return TRUE; 
    } // }}}

    private function hasActiveSessions($time) { // {{{
        global $sql_tbl;
        static $first_expiry_all;

        $active_sessions = func_query_first("SELECT SUM(expiry) AS sum_expiry, COUNT(sessid) AS num_active_sessions FROM $sql_tbl[sessions_data] WHERE " . ($time + XCART_SESSION_LENGTH - self::IDLE_TIME) . " < expiry"); // UNIX_TIMESTAMP()+3600-expiry

        if (!isset($first_expiry_all)) {
            $first_expiry_all = $active_sessions['sum_expiry'];
        }

        return  $active_sessions['sum_expiry'] !== $first_expiry_all 
                ||  $active_sessions['num_active_sessions'] > $this->max_active_sessions;
    } // }}}
    // Fake function to enable cache with getSqlOptimizationLock name
    private final function getSqlOptimizationLock() { // {{{
    } // }}}

}

?>
