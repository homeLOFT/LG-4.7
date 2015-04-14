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
 * Database-related functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v133 (xcart_4_7_0), 2015-02-17 23:56:28, func.db.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

interface XCDbMethods {
    public function affected_rows($link = false);
    public function close($link = false);
    //protected function connect($sql_host, $sql_user, $sql_password);
    public function errno($link = false);
    public function error($link = false);
    public function fetch_array($result, $flag = MYSQL_BOTH);
    public function fetch_assoc($result);
    public function fetch_field($result, $num = 0);
    public function fetch_row($result);
    public function has_fetch_rows($row);
    public function field_name($result, $field_offset);
    public function field_type($result, $field_offset);
    public function free_result($result);
    public function get_client_info();
    public function get_server_info($link = false);
    public function insert_id($link = false);
    public function num_fields($result);
    public function num_rows($result);
    public function query($query, $link = false);
    public function query_async($query, $link = false);
    public function result($result, $row, $field = 0);
    public function select_db($sql_db, $link = false);

    // From XCDbCommon class
    public static function getApi();
    public static function dbInit($sql_host, $sql_user, $sql_password);
    //protected static function getObject();
    public function __destruct();
    public function query_delayed($data);
}

abstract class XCDbCommon {
    protected $link;
    protected $delayedQueries;
    protected $compatibleReturnWithMysql = true; // Indicate that we must return compatible with mysql ext results

    public static function getApi() { // {{{
        static $res;
        if (isset($res)) {
            return $res;
        }
        if (
            defined('XC_FORCE_MYSQLI')
            || defined('DEVELOPMENT_MODE')
            || !function_exists('mysql_connect')
        ) {
            // MySQL Improved Extension
            $res = 'mysqli';
        } else {
            // Old MySQL Original MySQL Extension
            $res = 'mysql';
        }
        return $res;
    } // }}}

    public static function dbInit($sql_host, $sql_user, $sql_password) { // {{{
        global $sql_obj;

        // __Destruct() is called here
        // $sql_obj __Destruct() and sql_obj->Connect must be called in one method to make sure $this->link is not null
        $sql_obj = XCDbCommon::getObject();

        return $sql_obj->connect($sql_host, $sql_user, $sql_password);
    } // }}}

    protected static function getObject() { // {{{
        if (XCDbCommon::getApi() == 'mysqli') {
            // MySQL Improved Extension
            $_sql_obj = new XCDBmysqlI();
        } else {
            // Old MySQL Original MySQL Extension
            $_sql_obj = new XCDBmysql();
        }
        return $_sql_obj;
    } // }}}

    public function __destruct() { // {{{
        if (!empty($this->delayedQueries)) {
            foreach ($this->delayedQueries as $k=>$data_query) {
                if (empty($data_query['disable_async'])) {
                    $this->query_async($data_query['query']);
                } else {
                    $this->query($data_query['query']);
                }
                unset($this->delayedQueries[$k]);
            }
        }
        if (defined('XCART_INSTALLER') || defined('XCART_INSTALL')) {
            // myquery in installers uses static $db_link; Does not close this connection
            return true;
        }
        if (!empty($this->link)) {
            return $this->close($this->link);
        }
    } // }}}

    public function query_delayed($data) { // {{{
        if (empty($data['key'])) {
            $this->delayedQueries[] = $data;
        } else {
            $this->delayedQueries[$data['key']] = $data;
        }
        return true;
    } // }}}
}

class XCDBmysql extends XCDbCommon implements XCDbMethods {
    public function affected_rows($link = false) { // {{{
        return mysql_affected_rows(!empty($link) ? $link : $this->link);
    } // }}}

    public function close($link = false) { // {{{
        if (!empty($link) || !empty($this->link)) {
            return mysql_close(!empty($link) ? $link : $this->link);
        } else {
            return mysql_close();
        }
    } // }}}

    protected function connect($sql_host, $sql_user, $sql_password) { // {{{
        if (defined('X_PHP550_COMPAT'))
            $this->link = @mysql_connect($sql_host, $sql_user, $sql_password);
        else
            $this->link = mysql_connect($sql_host, $sql_user, $sql_password);

        return $this->link;
    } // }}}

    public function errno($link = false) { // {{{
        if (!empty($link) || !empty($this->link)) {
            return mysql_errno(!empty($link) ? $link : $this->link);
        } else {
            return mysql_errno();
        }
    } // }}}

    public function error($link = false) { // {{{
        if (!empty($link) || !empty($this->link)) {
            return mysql_error(!empty($link) ? $link : $this->link);
        } else {
            return mysql_error();
        }
    } // }}}

    public function fetch_array($result, $flag = MYSQL_BOTH) { // {{{
        if (empty($flag)) {
            $flag = MYSQL_ASSOC;
        }
        return mysql_fetch_array($result, $flag);
    } // }}}

    public function fetch_assoc($result) { // {{{
        return mysql_fetch_assoc($result);
    } // }}}

    public function fetch_field($result, $num = 0) { // {{{
        return mysql_fetch_field($result, $num);
    } // }}}

    public function fetch_row($result) { // {{{
        return mysql_fetch_row($result);
    } // }}}

    public function has_fetch_rows($row) { // {{{
        return $row !== false;
    } // }}}

    public function field_name($result, $field_offset) { // {{{
        return mysql_field_name($result, $field_offset);
    } // }}}

    public function field_type($result, $field_offset) { // {{{
        return mysql_field_type($result, $field_offset);
    } // }}}

    public function free_result($result) { // {{{
        return mysql_free_result($result);
    } // }}}

    public function get_client_info() { // {{{
        return mysql_get_client_info();
    } // }}}

    public function get_server_info($link = false) { // {{{
        return mysql_get_server_info(!empty($link) ? $link : $this->link);
    } // }}}

    public function insert_id($link = false) { // {{{
        return mysql_insert_id(!empty($link) ? $link : $this->link);
    } // }}}

    public function num_fields($result) { // {{{
        return mysql_num_fields($result);
    } // }}}

    public function num_rows($result) { // {{{
        return mysql_num_rows($result);
    } // }}}

    public function query($query, $link = false) { // {{{
        return mysql_query($query, !empty($link) ? $link : $this->link);
    } // }}}

    public function query_async($query, $link = false) { // {{{
        // Old MySql did not support async mode
        return $this->query($query, $link);
    } // }}}

    public function result($result, $row, $field = 0) { // {{{
        return mysql_result($result, $row, $field);
    } // }}}

    public function select_db($sql_db, $link = false) { // {{{
        if (!empty($link) || !empty($this->link)) {
            return mysql_select_db($sql_db, !empty($link) ? $link : $this->link);
        } else {
            return mysql_select_db($sql_db);
        }
    } // }}}

} // class XCDBmysql

class XCDBmysqlI extends XCDbCommon implements XCDbMethods {
    protected $syncQueryIsBlocked; // Indicate that last query was async

    public function __construct() { // {{{
        if (defined('DEVELOPMENT_MODE')) {
            // Detect all problem's calls in DEVELOPMENT_MODE
            $this->compatibleReturnWithMysql = false;
        }
    } // }}}

    public function close($link = false) { // {{{
        return mysqli_close(!empty($link) ? $link : $this->link);
    } // }}}

    protected function connect($sql_host, $sql_user, $sql_password) { // {{{
        if (strpos($sql_host, ':') !== false) {
            list($host, $port_socket) = explode(':', $sql_host);
        } else {
            $host = $sql_host;
            $port_socket = '';
        }

        if (empty($host)) {
            $host = ini_get(XCDbCommon::getApi() . '.default_host');
        }

        if (
            defined('X_PHP530_COMPAT')
            && !empty($host)
        ) {
            // opens a persistent connection
            $host = 'p:' . $host;
        }

        if (is_numeric($port_socket)) {
            $this->link = mysqli_connect($host, $sql_user, $sql_password, '', $port_socket);
        } elseif (!empty($port_socket)) {
            $this->link = mysqli_connect($host, $sql_user, $sql_password, '', ini_get("mysqli.default_port"), $port_socket);
        } else {
            $this->link = mysqli_connect($host, $sql_user, $sql_password);
        }

        return $this->link;
    } // }}}

    public function fetch_array($result, $flag = MYSQLI_ASSOC) { // {{{
        if (empty($flag)) {
            $flag = MYSQLI_ASSOC;
        }
        $res = mysqli_fetch_array($result, $flag);

        if (is_null($res) && $this->compatibleReturnWithMysql) {
            $res = false;
        }
        return $res;
    } // }}}

    public function affected_rows($link = false) { // {{{
        return mysqli_affected_rows(!empty($link) ? $link : $this->link);
    } // }}}

    public function errno($link = false) { // {{{
        $_link = !empty($link) ? $link : $this->link;
        return (is_object($_link)
            ? mysqli_errno($_link)
            : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    } // }}}

    public function error($link = false) { // {{{
        $_link = !empty($link) ? $link : $this->link;
        return (is_object($_link)
            ? mysqli_error($_link)
            : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    } // }}}

    public function fetch_assoc($result) { // {{{
        $res = mysqli_fetch_assoc($result);
        if (is_null($res) && $this->compatibleReturnWithMysql) {
            $res = false;
        }
        return $res;
    } // }}}

    public function fetch_field($result, $num = 0) { // {{{
        return (((($___mysqli_tmp = mysqli_fetch_field_direct($result, $num)) && is_object($___mysqli_tmp)) ? ( (!is_null($___mysqli_tmp->primary_key = ($___mysqli_tmp->flags & MYSQLI_PRI_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->multiple_key = ($___mysqli_tmp->flags & MYSQLI_MULTIPLE_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->unique_key = ($___mysqli_tmp->flags & MYSQLI_UNIQUE_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->numeric = (int)(($___mysqli_tmp->type <= MYSQLI_TYPE_INT24) || ($___mysqli_tmp->type == MYSQLI_TYPE_YEAR) || ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? ($___mysqli_tmp->type == MYSQLI_TYPE_NEWDECIMAL) : 0)))) && (!is_null($___mysqli_tmp->blob = (int)in_array($___mysqli_tmp->type, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB)))) && (!is_null($___mysqli_tmp->unsigned = ($___mysqli_tmp->flags & MYSQLI_UNSIGNED_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->zerofill = ($___mysqli_tmp->flags & MYSQLI_ZEROFILL_FLAG) ? 1 : 0)) && (!is_null($___mysqli_type = $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = (($___mysqli_type == MYSQLI_TYPE_STRING) || ($___mysqli_type == MYSQLI_TYPE_VAR_STRING)) ? "type" : "")) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && in_array($___mysqli_type, array(MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT, MYSQLI_TYPE_LONG, MYSQLI_TYPE_LONGLONG, MYSQLI_TYPE_INT24))) ? "int" : $___mysqli_tmp->type)) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && in_array($___mysqli_type, array(MYSQLI_TYPE_FLOAT, MYSQLI_TYPE_DOUBLE, MYSQLI_TYPE_DECIMAL, ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? constant("MYSQLI_TYPE_NEWDECIMAL") : -1)))) ? "real" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_TIMESTAMP) ? "timestamp" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_YEAR) ? "year" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && (($___mysqli_type == MYSQLI_TYPE_DATE) || ($___mysqli_type == MYSQLI_TYPE_NEWDATE))) ? "date " : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_TIME) ? "time" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_SET) ? "set" : $___mysqli_tmp->type)) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_ENUM) ? "enum" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_GEOMETRY) ? "geometry" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_DATETIME) ? "datetime" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && (in_array($___mysqli_type, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB)))) ? "blob" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_NULL) ? "null" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type) ? "unknown" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->not_null = ($___mysqli_tmp->flags & MYSQLI_NOT_NULL_FLAG) ? 1 : 0)) ) : false ) ? $___mysqli_tmp : false);
    } // }}}

    public function fetch_row($result) { // {{{
        $res = mysqli_fetch_row($result);
        if (is_null($res) && $this->compatibleReturnWithMysql) {
            $res = false;
        }
        return $res;
    } // }}}

    public function has_fetch_rows($row) { // {{{
        if ($this->compatibleReturnWithMysql)
            return $row !== false && !is_null($row);
        else
            return !is_null($row);
    } // }}}

    public function field_name($result, $field_offset) { // {{{
        return ((($___mysqli_tmp = mysqli_fetch_field_direct($result, $field_offset)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
    } // }}}

    public function field_type($result, $field_offset) { // {{{
        return ((is_object($___mysqli_tmp = mysqli_fetch_field_direct($result, $field_offset)) && !is_null($___mysqli_tmp = $___mysqli_tmp->type)) ? ((($___mysqli_tmp = (string)(substr(( (($___mysqli_tmp == MYSQLI_TYPE_STRING) || ($___mysqli_tmp == MYSQLI_TYPE_VAR_STRING) ) ? "string " : "" ) . ( (in_array($___mysqli_tmp, array(MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT, MYSQLI_TYPE_LONG, MYSQLI_TYPE_LONGLONG, MYSQLI_TYPE_INT24))) ? "int " : "" ) . ( (in_array($___mysqli_tmp, array(MYSQLI_TYPE_FLOAT, MYSQLI_TYPE_DOUBLE, MYSQLI_TYPE_DECIMAL, ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? constant("MYSQLI_TYPE_NEWDECIMAL") : -1)))) ? "real " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_TIMESTAMP) ? "timestamp " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_YEAR) ? "year " : "" ) . ( (($___mysqli_tmp == MYSQLI_TYPE_DATE) || ($___mysqli_tmp == MYSQLI_TYPE_NEWDATE) ) ? "date " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_TIME) ? "time " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_SET) ? "set " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_ENUM) ? "enum " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_GEOMETRY) ? "geometry " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_DATETIME) ? "datetime " : "" ) . ( (in_array($___mysqli_tmp, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB))) ? "blob " : "" ) . ( ($___mysqli_tmp == MYSQLI_TYPE_NULL) ? "null " : "" ), 0, -1))) == "") ? "unknown" : $___mysqli_tmp) : false);
    } // }}}

    public function free_result($result) { // {{{
        return ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
    } // }}}

    public function get_client_info() { // {{{
        return mysqli_get_client_info();
    } // }}}

    public function get_server_info($link = false) { // {{{
        $_link = !empty($link) ? $link : $this->link;
        return ((is_null($___mysqli_res = mysqli_get_server_info($_link))) ? false : $___mysqli_res);
    } // }}}

    public function insert_id($link = false) { // {{{
        $_link = !empty($link) ? $link : $this->link;
        return ((is_null($___mysqli_res = mysqli_insert_id($_link))) ? false : $___mysqli_res);
    } // }}}

    public function num_fields($result) { // {{{
        return (($___mysqli_tmp = mysqli_num_fields($result)) ? $___mysqli_tmp : false);
    } // }}}

    public function num_rows($result) { // {{{
        return mysqli_num_rows($result);
    } // }}}

    public function query($query, $link = false, $resultmode = MYSQLI_STORE_RESULT) { // {{{
        $_link = !empty($link) ? $link : $this->link;
        if (!empty($this->syncQueryIsBlocked)) {
            // We must read async result before run any sync/async queries
            mysqli_reap_async_query($_link);
            $this->syncQueryIsBlocked = false;
        }
        return mysqli_query($_link, $query, $resultmode);
    } // }}}

    public function query_async($query, $link = false) { // {{{
        global $config;

        if (
            !empty($config['db_allow_mysqli_async'])
            && defined('MYSQLI_ASYNC')
        ) {
            // Now all sync queries are blocked
            $res = $this->query($query, $link, MYSQLI_ASYNC);
            $this->syncQueryIsBlocked = true;
            return $res;
        } else {
            return $this->query($query, $link);
        }
    } // }}}

    public function result($result, $row, $field = 0) { // {{{

        mysqli_data_seek($result, $row);
        $datarow = $this->fetch_row($result);
        return empty($datarow) ? false : $datarow[$field];
    } // }}}

    public function select_db($sql_db, $link = false) { // {{{
        $_link = !empty($link) ? $link : $this->link;
        return mysqli_select_db($_link, $sql_db);
        // alternative return ((bool)mysqli_query( $_link, "USE $sql_db"));
    } // }}}
} // class XCDBmysqlI

/**
 * Database abstract layer functions
 */
function db_connect($sql_host, $sql_user, $sql_password)
{
    return XCDbCommon::dbInit($sql_host, $sql_user, $sql_password);
}

function db_select_db($sql_db)
{
    global $sql_obj;
    return $sql_obj->select_db($sql_db);
}

function db_query($query, $delayed_query_type = false)
{
    global $debug_mode;
    global $mysql_autorepair, $sql_max_allowed_packet;
    global $memcache, $config, $sql_tbl, $sql_obj;

    if ($delayed_query_type) {
        return func_add_delayed_query($query, $delayed_query_type);
    }

    if (defined('START_TIME')) {

        global $__sql_time;

        $t = func_microtime();

    }

    if (
        !defined('REMOVE_MCACHE_CONFIG')
        && $memcache
        && func_detect_config_changes($query)
    ) {
        define('REMOVE_MCACHE_CONFIG', 1);
    }

    if (
        $sql_max_allowed_packet
        && strlen($query) > $sql_max_allowed_packet
    ) {

        // Check max. allowed packet size
        global $current_location, $REMOTE_ADDR, $login;

        $len = strlen($query);

        $query = substr($query, 0, 1024) . '...';

        $mysql_error = '10001 : The size of the data package being transmitted is greater than maximum allowed by the server';

        $msg  = 'Site                : ' . $current_location . "\n";
        $msg .= 'Remote IP           : ' . $REMOTE_ADDR . "\n";
        $msg .= 'Logged as           : ' . $login. "\n";
        $msg .= 'Query length        : ' . $len . "\n";
        $msg .= 'Max. allowed packet : ' . $sql_max_allowed_packet . "\n";
        $msg .= 'SQL query           : ' . $query . "\n";
        $msg .= 'Error code          : 10001' . "\n";
        $msg .= 'Description         : The size of the data package being transmitted is greater than maximum allowed by the server';

        db_error_generic($query, $mysql_error, $msg);

        return false;
    }

    __add_mark();

    if (
        stristr($query, "select ") !== false
        && isset($config['General']['use_old_products_lng'])
        && $config['General']['use_old_products_lng'] == 'Y'
    ) {
        $query = func_transform_lng_query_ext($query, 'xcart_products_lng');
        $query = func_transform_lng_query_base($query, 'xcart_products', $sql_tbl['products_lng_current']);
    }

    $result = $sql_obj->query($query);
    if (
        defined('DEVELOPMENT_MODE')
        && preg_match('/^(SELECT|DESCRIBE|SHOW) /i', $query)
        && !empty($result)
    ) {
       db_sql_resource($result, $query);
    }

    if (
        !$result
        && preg_match('/Lost connection|server has gone away/i', $sql_obj->error())
    ) {

        $sql_obj->close();
        db_connection();

        $result = $sql_obj->query($query);

    }

    $t_end = func_microtime();

    if (defined('START_TIME')) {

        $__sql_time += func_microtime() - $t;

    }

    // Auto repair

    if (
        !$result
        && $mysql_autorepair
        && ($mysql_error = $sql_obj->error())
        && preg_match("/(?:'(\S+)\.(?:MYI|MYD))|(?:Table\s+'(\S+)'\s+is\s+marked\s+as\s+crashed\s+and\s+should\s+be\s+repaired)|(?:Incorrect\s+key\s+file\s+for\s+table\s+'(\S+)'.\s+try\s+to\s+repair\s+it)|(?:Old\s+key\s+file\s+for\s+table\s+'(\S+)'.\s+repair\s+it.)/i", $mysql_error, $m)
    ) {

        $m = array_values(array_filter($m));
        $m = $m[1];

        $m = preg_replace('%.*[\\\/]%', '', $m);
        $m = preg_replace('%\.MY[ID]$%', '', $m);

        error_log('Repairing table ' . $m, 0);

        $mysql_error = $sql_obj->errno() . " : " . $mysql_error;

        if ($debug_mode == 1 || $debug_mode == 3) {

            echo '<strong color="darkred">Repairing table ' . $m . '...</strong>' . $mysql_error . '<br />';

            flush();

        }

        if (!empty($m)) {
            if (strpos($m, '`') === false)
                $m = '`' . $m . '`';

            $result = $sql_obj->query('REPAIR TABLE ' . $m . ' EXTENDED');
        }

        if (!$result) {

            if (!empty($m))
                $query = 'REPAIR TABLE ' . $m . ' EXTENDED';

            error_log('Repairing table ' . $m . ' failed: ' . $mysql_error, 0);

        } else {

            $result = $sql_obj->query($query);

        }

    }

    if (
        $debug_mode == 1
        && db_error($result, $query)
    ) {
        $_func = function_exists('func_get_backtrace_html')
            ? 'func_get_backtrace_html'
            : 'debug_backtrace';

        print_r($_func());
        exit;
    }

    if (!$result) {
        db_error($result, $query);
    }

    $explain = array();

    if (
        defined('BENCH')
        && constant('BENCH')
        && !defined('BENCH_BLOCK')
        && !defined('BENCH_DISPLAY')
        && defined('BENCH_DISPLAY_TYPE')
        && constant('BENCH_DISPLAY_TYPE') == 'A'
        && !strncasecmp('SELECT', $query, 6)
    ) {
        $r = $sql_obj->query('EXPLAIN ' . $query);

        if ($r !== false) {

            while ($arr = db_fetch_array($r))
                $explain[] = $arr;

            db_free_result($r);

        }

    }

    __add_mark(
        array(
            'query'   => $query,
            'explain' => $explain,
        ),
        'SQL'
    );

    return $result;
}

function db_result($result, $offset)
{
    global $sql_obj;
    return $sql_obj->result($result, $offset);
}

function db_fetch_row($result)
{
    global $sql_obj;
    return $sql_obj->fetch_row($result);
}

function db_fetch_array($result, $flag = '')
{
    global $sql_obj;
    return $sql_obj->fetch_array($result, $flag);
}

function db_fetch_field($result, $num = 0)
{
    global $sql_obj;
    return $sql_obj->fetch_field($result, $num);
}

function db_field_name($result, $field_offset = 0)
{
    global $sql_obj;
    return $sql_obj->field_name($result, $field_offset);
}

function db_free_result($result)
{
    global $sql_obj;
    if (empty($result)) {
        return false;
    }

    if (defined('DEVELOPMENT_MODE')) {
        db_sql_resource($result, FALSE);
    }
    @$sql_obj->free_result($result);
}

function db_num_rows($result)
{
    global $sql_obj;
    $res = $result ? intval($sql_obj->num_rows($result)) : false;
    return $res;
}

function db_num_fields($result)
{
    global $sql_obj;
    return intval($sql_obj->num_fields($result));
}

function db_insert_id()
{
    global $sql_obj;
    return $sql_obj->insert_id();
}

function db_affected_rows()
{
    global $sql_obj;
    return $sql_obj->affected_rows();
}

function db_error($mysql_result, $query)
{
    global $login, $REMOTE_ADDR, $current_location;
    global $sql_obj;

    if ($mysql_result)
        return false;

    $mysql_error = $sql_obj->errno() . ' : ' . $sql_obj->error();

    $msg  = 'Site        : ' . $current_location . "\n";
    $msg .= 'Remote IP   : ' . $REMOTE_ADDR . "\n";
    $msg .= 'Logged as   : ' . $login . "\n";
    $msg .= 'SQL query   : ' . $query . "\n";
    $msg .= 'Error code  : ' . $sql_obj->errno() . "\n";
    $msg .= 'Description : ' . $sql_obj->error();

    db_error_generic($query, $mysql_error, $msg);

    return true;
}

function db_error_generic($query, $query_error, $msg)
{
    global $debug_mode, $config, $mysql_error_count;

    $mysql_error_count ++;

    $email = false;

    if ('Y' == $config['Email_Note']['admin_sqlerror_notify']) {
        $email = array($config['Company']['site_administrator']);
    }

    if ($debug_mode == 1 || $debug_mode == 3) {

        echo '<b><font color="darkred">INVALID SQL: </font></b>' . htmlspecialchars($query_error) . '<br />';
        echo '<b><font color="darkred">SQL QUERY FAILURE:</font></b>' . htmlspecialchars($query) . '<br />';

        flush();
    }

    $do_log = ($debug_mode == 2 || $debug_mode == 3);

    if (
        $email !== false
        || $do_log
    ) {

        if (!defined('SKIP_CHARSET_SELECTION')) {
            define('SKIP_CHARSET_SELECTION', 1);
        }

        x_log_add('SQL', $msg, true, 1, $email, !$do_log);

    }

}

function db_prepare_query($query, $params)
{
    static $prepared = array();

    if (!empty($prepared[$query])) {

        $info = $prepared[$query];
        $tokens = $info['tokens'];

    } else {

        $tokens = preg_split('/((?<!\\\)\?)/S', $query, -1, PREG_SPLIT_DELIM_CAPTURE);

        $count = 0;

        foreach ($tokens as $k => $v) {
            if ($v === '?') $count ++;
        }

        $info = array (
            'tokens'      => $tokens,
            'param_count' => $count,
        );

        $prepared[$query] = $info;

    }

    if (count($params) != $info['param_count']) {
        return array (
            'info'     => 'mismatch',
            'expected' => $info['param_count'],
            'actual'   => count($params),
        );
    }

    $pos = 0;

    foreach ($tokens as $k => $val) {

        if ($val !== '?') continue;

        if (!isset($params[$pos])) {
            return array (
                'info'     => 'missing',
                'param'    => $pos,
                'expected' => $info['param_count'],
                'actual'   => count($params),
            );
        }

        $val = $params[$pos];

        if (is_array($val)) {

            $val = func_array_map('addslashes', $val);

            $val = implode("','", $val);

        } else {

            $val = addslashes($val);
        }

        $tokens[$k] = "'" . $val . "'";

        $pos ++;

    }

    return implode('', $tokens);
}

// New DB API: Executing parameterized queries
// Example1:
//   $query = "SELECT * FROM table WHERE field1=? AND field2=? AND field3='\\?'"
//   $params = array (val1, val2)
//   query to execute:
//      "SELECT * FROM table WHERE field1='val1' AND field2='val2' AND field3='\\?'"
// Example2:
//   $query = "SELECT * FROM table WHERE field1=? AND field2 IN (?)"
//   $params = array (val1, array(val2,val3))
//   query to execute:
//      "SELECT * FROM table WHERE field1='val1' AND field2 IN ('val2','val3')"
/**
 * Warning:
 *  1) all parameters must not be escaped with addslashes()
 *  2) non-parameter symbols '?' must be escaped with a '\'
 */
function db_exec($query, $params = array())
{
    global $config, $login, $REMOTE_ADDR, $current_location;

    if (!is_array($params))
        $params = array ($params);

    $prepared = db_prepare_query($query, $params);

    if (!is_array($prepared)) {

        return db_query($prepared);

    }

    $error = 'Query preparation failed';

    if ('mismatch' == $prepared['info']) {

        $error .= ': parameters mismatch (passed ' . $prepared['actual'] . ', expected ' . $prepared['expected'] . ')';

    } elseif ('missing' == $prepared['info']) {

        $error .= ': parameter ' . $prepared['param'] . ' is missing';

    }

    $msg  = 'Site        : ' . $current_location . "\n";
    $msg .= 'Remote IP   : ' . $REMOTE_ADDR . "\n";
    $msg .= 'Logged as   : ' . $login . "\n";
    $msg .= 'SQL query   : ' . $query . "\n";
    $msg .= 'Description : ' . $error;

    db_error_generic($query, $error, $msg);

    return false;
}

function db_sql_resource($resource = 'get_sql_resources', $value = '') {
    static $sql_resources = array();

    if ($resource === 'get_sql_resources')
        return $sql_resources;

    if (is_object($resource))
        $resource = spl_object_hash($resource);
    else
        $resource = (string)$resource;

    if (empty($value))
        unset($sql_resources[$resource]);
    else
        $sql_resources[$resource] = substr($value, 0, 100) . print_r(func_get_backtrace(), true);

    return TRUE;
}

/**
 * Execute mysql query and store result into associative array with
 * column names as keys
 */
function func_query($query, $use_cache = false)
{
    if ($use_cache)
        return db_get_cache($query);

    $result = false;

    if ($p_result = db_query($query)) {

        while ($arr = db_fetch_array($p_result))
            $result[] = $arr;

        db_free_result($p_result);

    }

    return $result;
}

/**
 * Execute mysql query and store result into associative array with
 * column names as keys and then return first element of this array
 * If array is empty return array().
 */
function func_query_first($query, $use_cache = FALSE, $limit_first = TRUE) { // {{{
    $query = trim($query);

    // Add limit directive for the performance reason
    if (
        $limit_first
        && stripos($query, 'LIMIT ') === FALSE
        && stripos($query, 'SELECT') !== FALSE
    ) {
        $query .= ' LIMIT 1';
    }
    assert('preg_match("/^[(]{0,1}SELECT\s/Ssi", $query) /*'.__FUNCTION__.' Non-SELECT query is passed*/');

    if ($use_cache) {
      return db_get_cache($query, 'first');
    }

    if ($p_result = db_query($query)) {
        $result = db_fetch_array($p_result);
        db_free_result($p_result);
    }

    return !empty($result) && is_array($result) ? $result : array();
} // }}}

/**
 * Execute mysql query and store result into associative array with
 * column names as keys and then return first cell of first element of this array
 * If array is empty return false.
 */
function func_query_first_cell($query, $use_cache = false, $limit_first = true)
{
    $query = trim($query);

    // Add limit directive for the performance reason
    if (
        $limit_first
        && stripos($query, 'LIMIT ') === FALSE
        && stripos($query, 'SELECT') !== FALSE
    ) {
        $query .= ' LIMIT 1';
    }
    assert('preg_match("/^SELECT\s/Ssi", $query) /*'.__FUNCTION__.' Non-SELECT query is passed*/');

    if ($use_cache) {
        return db_get_cache($query, 'first_cell');
    }

    if ($p_result = db_query($query)) {

        $result = db_fetch_row($p_result);

        db_free_result($p_result);

    }

    return is_array($result) ? $result[0] : false;
}

function func_query_column($query, $column = 0)
{
    $result = array();

    $fetch_func = is_int($column)
        ? 'db_fetch_row'
        : 'db_fetch_array';

    if ($p_result = db_query($query)) {

        while ($row = $fetch_func($p_result))
            $result[] = $row[$column];

        db_free_result($p_result);
    }

    return $result;
}

/**
 * Add default values for fields with empty DEFAULT VALUES
 */
function func_add_blob_default_values($tbl, $arr)
{
    global $sql_tbl;

    $blob_fields = array (
        $sql_tbl['categories'] =>
            array(
                'description' => '',
            ),
        $sql_tbl['config'] =>
            array(
                'value' => '',
                'defvalue' => '',
                'variants' => '',
            ),
        $sql_tbl['products_lng_en'] =>
            array(
                'descr' => '',
                'fulldescr' => '',
            ),
        $sql_tbl['orders'] =>
            array(
                'notes' => '',
                'details' => '',
                'customer_notes' => '',
                'extra' => '',
            ),
    );

    foreach (array('products_lng_de','products_lng_fr','products_lng_sv') as $lng_table) {
        if (isset($sql_tbl[$lng_table]))
            $blob_fields[$sql_tbl[$lng_table]] = $blob_fields[$sql_tbl['products_lng_en']];
    }

    if (!empty($sql_tbl[$tbl]))
        $tbl = $sql_tbl[$tbl];

    if (!isset($blob_fields[$tbl]))
        return $arr;

    $arr = func_array_merge($blob_fields[$tbl], $arr);

    return $arr;
}

/**
 * Insert array data to table
 */
function func_array2insert ($tbl, $arr, $is_replace = false, $is_ignore = false, $delims = array())
{
    global $sql_tbl;

    if (empty($tbl) || empty($arr) || !is_array($arr))
        return false;

    if (!empty($sql_tbl[$tbl]))
        $tbl = $sql_tbl[$tbl];

    $query = $is_replace ? 'REPLACE' : ('INSERT' . ($is_ignore ? ' IGNORE' : ''));

    if (defined('DEVELOPMENT_MODE'))
        $arr = func_add_blob_default_values($tbl, $arr);

    $arr_keys = array_keys($arr);
    $default_delims = array_fill_keys($arr_keys, "'");

    func_check_tbl_fields($tbl, $arr_keys);

    foreach ($arr_keys as $k => $v) {

        if (!preg_match('/^`.*`$/Si', $v, $out)) {

            $arr_keys[$k] = "`" . $v . "`";

        }

    }

    $delims = array_merge($default_delims, $delims);

    foreach ($arr as $k => $v) {

        if (!preg_match('/^["\'].*["|\']$/Si', $v, $out)) {

            $arr[$k] = $delims[$k] . $v . $delims[$k];

        }

    }
    $arr_values = array_values($arr);

    $query .= ' INTO ' . $tbl . ' (' . implode(', ', $arr_keys) . ') VALUES (' . implode(', ', $arr_values) . ')';

    $r = db_query($query);

    if ($r) {

        return db_insert_id();

    }

    return false;
}

/**
 * Update array data to table + where statament
 */
function func_array2update ($tbl, $arr, $where = '', $skip_quotes = false)
{
    global $sql_tbl;

    if (
        empty($tbl)
        || empty($arr)
        || !is_array($arr)
    ) {
        return false;
    }

    if ($sql_tbl[$tbl])
        $tbl = $sql_tbl[$tbl];

    $r = array();

    foreach ($arr as $k => $v) {

        if (!(
            ($k{0} == '`')
            && ($k{strlen($k) - 1} == '`')
            )
        ) {
            $k = "`$k`";
        }

        if (
            !$skip_quotes
            && !preg_match('/^["\'].*["|\']$/Si', $v, $out)
        ) {
            $v = "'" . $v . "'";
        }

        $r[] = $k . "=" . $v;

    }

    func_check_tbl_fields($tbl, array_keys($arr));

    $query = 'UPDATE ' . $tbl . ' SET ' . implode(', ', $r) . ($where ? ' WHERE ' . $where : '');

    return db_query($query);
}

function func_query_hash($query, $column = FALSE, $is_multirow = TRUE, $only_first = FALSE) { // {{{
    $result = array();
    $is_multicolumn = FALSE;
    if ($p_result = db_query($query)) {
        if ($column === FALSE) {
            // Get first field name
            $column = db_field_name($p_result, 0);
        } elseif (is_array($column)) {
            if (count($column) == 1) {
                $column = current($column);
            } else {
                $is_multicolumn = TRUE;
            }
        }

        while ($row = db_fetch_array($p_result)) {
            // Get key(s) column value and remove this column from row
            if ($is_multicolumn) {
                $keys = array();
                foreach ($column as $c) {
                    $keys[] = &$row[$c];
                    unset($row[$c]);
                }

                $keys = implode('"]["', $keys);
            } else {
                $key = $row[$column];
                func_unset($row, $column);
            }

            if ($only_first)
                $row = array_shift($row);

            if ($is_multicolumn) {
                // If keys count > 1
                if ($is_multirow) {
                    eval('$result["' . $keys . '"][] = $row;');
                } else {
                    eval('$is = isset($result["' . $keys . '"]);');
                    if (!$is) {
                        eval('$result["' . $keys . '"] = $row;');
                    }
                }
            } elseif ($is_multirow) {
                $result[$key][] = $row;
            } elseif (!isset($result[$key])) {
                $result[$key] = $row;
            }
        }

        db_free_result($p_result);
    }

    return $result;
} // }}}

/**
 * Generate SQL-query relations
 */
function func_generate_joins($joins, $parent = false)
{
    $str = '';

    foreach ($joins as $jname => $j) {

        if (!isset($j['parent']))
            $j['parent'] = false;

        if (
            (
                empty($parent)
                && !empty($j['parent'])
            ) || (
                !empty($parent)
                && $parent != $j['parent']
            )
        ) {
            continue;
        }

        $str .= func_build_join($jname, $j);

        unset($joins[$jname]);

        list($js, $tmp) = func_generate_joins($joins, (empty($j['tblname']) ? $jname : $j['tblname']));

        $str .= $tmp;

        $keys = array_diff(array_keys($joins), array_keys($js));

        if (!empty($keys)) {

            foreach ($joins as $k => $v) {

                if (in_array($k, $keys))
                    unset($joins[$k]);

            }

        }

    }

    if (empty($parent) && !empty($joins)) {

        foreach ($joins as $jname => $j) {

            $str .= func_build_join($jname, $j);

        }

        unset($joins);

    }

    return false === $parent
        ? $str
        : array($joins, $str);
}

/**
 * Get [LEFT | INNER] JOIN string
 */
function func_build_join($jname, $join)
{
    global $sql_tbl;

    $str = ' ' . (!empty($join['is_inner']) ? 'INNER' : 'LEFT') . ' JOIN ';

    if (!empty($join['tblname'])) {

        $str .= $sql_tbl[$join['tblname']] . " as " . $jname;

    } else {

        $str .= $sql_tbl[$jname];

    }

    $str .= ' ON ' . $join['on'];

    return $str;
}

/**
 * Check table fields names
 */
function func_check_tbl_fields($tbl, $fields)
{

    if (defined('USE_SIMPLE_DB_INTERFACE'))
        return true;

    static $storage = array();

    if (empty($storage))
        $storage = func_data_cache_get('sql_tables_fields');

    global $sql_tbl;

    if (empty($fields))
        return;

    if (!is_array($fields)) {
        func_403(77);
    }

    if (!is_array($tbl)) {
        $tbl = array($tbl);
    }

    $fields_orig = array();

    foreach ($tbl as $t) {

        if (isset($sql_tbl[$t])) {

            $t = $sql_tbl[$t];

        }

        $t = strtolower($t);
        if (!isset($storage[$t])) {
            func_403(78);
        }

        $fields_orig = func_array_merge($fields_orig, $storage[$t]);
    }

    $fields_orig = array_unique($fields_orig);

    $res = array_diff($fields, $fields_orig);

    if (!empty($res)) {
        func_403(79);
    }

}

/*
* Function to make compatible custom code which uses xcart_products with new xcart_products_lng_en database schema
*/
function func_transform_lng_query_base($query, $base_table, $current_lng_table)
{
    if (empty($current_lng_table))
        return $query;

    if (stristr($query, 'xcart_products_lng') !== false)
        return $query;

    if (!preg_match("/SELECT.*$base_table\b(?:\s+(as)\s+(\w+)|\s+(\w+)|())/i", $query, $arr))
        return $query;

    $code = str_replace('xcart_products_lng_', '', $current_lng_table);

    if (empty($code))
        return $query;

    unset($arr[0]);
    settype($arr, 'array');

    $arr = array_map('strtolower', $arr);

    $reserved_words = array('use','inner','join','partition','ignore','force','cross','straight_join', 'left', 'right', 'outer', 'natural','order','where','limit','group','having','procedure','into','using');

    // Get possible alias
    if ($arr[1] == 'as')
        $alias = $arr[2];
    elseif(!in_array($arr[3], $reserved_words))
        $alias = $arr[3];
    else
        $alias = '';

    // replace xcart_products to (xcart_products inner join xcart_products_lng_en on xcart_products.productid=xcart_products_lng_en.productid)
    if (empty($alias)) {
        $query = preg_replace("/([ ,]+)productid\b/i", "\\1$base_table.productid", $query);
        $query = preg_replace("/(select .* from .*?)\b$base_table\b/i", "\\1(".$base_table." INNER JOIN $current_lng_table ON $base_table.productid=$current_lng_table.productid)", $query, 1);
    } else {
        $query = preg_replace("/([ ,]+)productid\b/i", "\\1$alias.productid", $query);
        $query = preg_replace("/(select .* from .*?)\b$base_table(?:\s+(as)\s+(\w+)|\s+(\w+)|())/i", "\\1(".$base_table." $alias INNER JOIN $current_lng_table ON $alias.productid=$current_lng_table.productid)", $query, 1);
    }

    return $query;
}
/*
* Function to make compatible custom code which uses xcart_products_lng with new xcart_products_lng_en database schema
*/
function func_transform_lng_query_ext($query, $lng_table)
{
    global $all_languages;
    if (empty($all_languages))
        return $query;

    $_languages = array_keys($all_languages);
    $all_codes = implode('|', $_languages);

    if (!preg_match("/SELECT.*$lng_table(?:\s+(as)\s+(\w+)|\s+(\w+)|()).*[\s.]code\s*=\s*['\"]($all_codes)['\"]/i", $query, $arr))
        return $query;

    unset($arr[0]);
    settype($arr, 'array');
    $code = $arr[5];
    $arr = array_map('strtolower', $arr);

    $reserved_words = array('use','inner','join','partition','ignore','force','cross','straight_join', 'left', 'right', 'outer', 'natural','order','where','limit','group','having','procedure','into','using');

    // Get possible alias
    if ($arr[1] == 'as')
        $alias = $arr[2];
    elseif(!in_array($arr[3], $reserved_words))
        $alias = $arr[3];
    else
        $alias = '';


    // replace 'xcart_products_lng.code="en"' TO '1=1' . All match have to be replaced
    $query = preg_replace("/($lng_table\.|$alias\.|\s)code\s*=\s*['\"]($code)['\"]/i", ' 1=1', $query);

    if (empty($alias)) {
        // replace 'xcart_products left join xcart_products_lng' TO 'xcart_products inner join xcart_products_lng'
        // natural left join is not changed
        $query = preg_replace("/(?<!NATURAL)(\sleft|\sright) join $lng_table/i", " inner join $lng_table", $query);

        // replace xcart_products_lng to xcart_products_lng_en
        $query = preg_replace("/\b$lng_table\b/i", $lng_table . '_' . $code, $query);
    } else {
        $query = preg_replace("/(?<!NATURAL)(\sleft|\sright) join $lng_table(?:\s+(as)\s+(\w+)|\s+(\w+)|())/i", " inner join $lng_table $alias", $query);
        $query = preg_replace("/\b$lng_table(?:\s+(as)\s+(\w+)|\s+(\w+)|())/i", $lng_table . '_' . $code . " $alias", $query);
    }

    return $query;
}

/**
 * The function creates a DB connection
 */

function db_connection ($_sql_host = '', $_sql_user = '', $_sql_db = '', $_sql_password = '')
{
    global $sql_host, $sql_user, $sql_db, $sql_password, $debug_mode, $display_critical_errors;

    if ($_sql_host == '') $_sql_host = $sql_host;
    if ($_sql_user == '') $_sql_user = $sql_user;
    if ($_sql_db == '') $_sql_db = $sql_db;
    if ($_sql_password == '') $_sql_password = $sql_password;

    $db_connect_limit = 5;
    $error_text = '';
    $web_error_text = '';
    $sql_connection = false;
    $sql_select_db = false;

    while ($db_connect_limit-- > 0 && !$sql_connection) {
        $sql_connection = @db_connect($_sql_host, $_sql_user, $_sql_password);
    }

    if (!$sql_connection) {

        $error_text .=  db_error_msg();

        if ($display_critical_errors)
            $web_error_text .= db_error_msg(true);
    } else {

        $sql_select_db = db_select_db($_sql_db);
    }

    if (
        $sql_connection
        && !$sql_select_db
    ) {
        $error_text .= db_error_msg();

        if ($display_critical_errors)
            $web_error_text .= db_error_msg(true);
    }

    if (!$sql_connection || !$sql_select_db) {

        $config_php = '<b><a href="http://help.x-cart.com/index.php?title=X-Cart:Config.php">config.php</a></b>';

        if ($debug_mode > 1)
            func_direct_log('sql', $error_text);

        func_show_error_page(
            'Cannot connect to MySQL server',
            $web_error_text,
            'Please make sure of the following:
            <ul>
                <li>The MySQL credentials in the ' . $config_php . ' file are correct.</li>
                <li>Your MySQL server is installed and is up and running properly.</li>
                <li>If you have recently updated the MySQL credentials in the config.php file, moved your store to another server, or updated the password of your hosting control panel (some control panels update the MySQL password automatically if you change the main password), make sure the MySQL credentials specified in your store\'s ' . $config_php . ' file match the MySQL credentials provided by your hosting.</li>
            </ul>'
        );
    } else {
        db_set_mode();
        db_set_options();
    }

    return $sql_connection;
}

function db_set_mode() { // {{{
    global $sql_obj;

    if (!defined('X_MYSQL_VERSION')) {
        $_mysql_version = preg_match("/^(\d+\.\d+\.\d+)/", $sql_obj->get_server_info(), $match);
        if ($_mysql_version) {
            define('X_MYSQL_VERSION', $match[1]);
        }
    }

    if (defined('X_MYSQL_VERSION')) {
        if (version_compare(X_MYSQL_VERSION, '5.0.0') >= 0) {
            if (defined('DEVELOPMENT_MODE'))
                db_query("SET sql_mode = 'MYSQL40,TRADITIONAL'");
            else
                db_query("SET sql_mode = 'MYSQL40'");
        }

        if (version_compare(X_MYSQL_VERSION, '5.0.17') > 0)
            define('X_MYSQL5_COMP_MODE', true);

        if (version_compare(X_MYSQL_VERSION, '5.0.18') == 0)
            define('X_MYSQL5018_COMP_MODE', true);
    }
} // }}}

function db_set_options() { // {{{
    global $sql_charset, $sql_collation;
    global $config, $sql_max_allowed_packet;

    db_query("SET NAMES '$sql_charset' COLLATE '$sql_collation'");

    $sql_vars = func_data_cache_get('sql_vars');

    $sql_max_allowed_packet = intval($sql_vars['max_allowed_packet']);

    if (is_numeric($sql_vars['lower_case_table_names'])) {
        define('X_MYSQL_LOWER_CASE_TABLE_NAMES', intval($sql_vars['lower_case_table_names']));
    }

    if (defined('DEVELOPMENT_MODE') && constant('DEVELOPMENT_MODE')) {
        if (empty($sql_vars['sql_big_selects']) || strcasecmp($sql_vars['sql_big_selects'], 'ON') === 0)
            db_query('SET SESSION SQL_BIG_SELECTS=OFF');
        if (intval($sql_vars['max_join_size']) > SQL_MAX_JOIN_SIZE) {
            db_query('SET SESSION MAX_JOIN_SIZE=' . SQL_MAX_JOIN_SIZE);
        }
    } else {
        if (empty($sql_vars['sql_big_selects']) || strcasecmp($sql_vars['sql_big_selects'], SQL_BIG_SELECTS) !== 0)
            db_query('SET SESSION SQL_BIG_SELECTS=' . SQL_BIG_SELECTS);
        if (SQL_BIG_SELECTS == 'OFF' && intval($sql_vars['max_join_size']) < SQL_MAX_JOIN_SIZE)
            db_query('SET SESSION MAX_JOIN_SIZE=' . SQL_MAX_JOIN_SIZE);
    }

    if (defined('DEVELOPMENT_MODE')) {

        if (1
            && !empty($sql_vars['query_cache_type'])
            && $sql_vars['query_cache_type'] != 'OFF'
        ) {
            db_query('SET SESSION query_cache_type=0');
        }

        if (
            !empty($sql_vars['slow_query_log'])
            && $sql_vars['slow_query_log'] == 'ON'
        ) {
            db_query('SET SESSION long_query_time=5');
        }
    }
    /**
     * Initialize character set of database. Used in func_translit function
     */
    if (isset($sql_vars['character_set_client'])) {
        $config['db_charset'] = $sql_vars['character_set_client'];
    }

} // }}}

/**
 * Compose mySQL error text containing error number, error message with links to mysql.com
 */

function db_error_msg($web = false)
{
    global $sql_obj;
    return (false !== $web)
        ? '<a href="http://search.mysql.com/search?q=' . $sql_obj->errno() . '">' . $sql_obj->errno() . '</a>: ' . $sql_obj->error() . "<br /><br />\n"
        : $sql_obj->errno() . ': ' . $sql_obj->error() . "\n";
}

/**
 * SQL cache handler
 */
function db_get_cache($query, $type = '')
{
    global $memcache;

    if ($memcache) {

        return func_get_data_from_func(
            'inner_sql',
            'func_dc_sql',
            array(
                md5($query),
                $query,
                $type,
            ),
            SQL_DATA_CACHE_TTL
        );

    }

    return func_data_cache_get(
        'sql',
        array(
            'md5'   => md5($query),
            'query' => $query,
            'type'  => $type,
        )
    );
}

function func_detect_config_changes($query)
{
    global $sql_tbl;

    $query = strtoupper($query);

    return false !== strpos($query, strtoupper($sql_tbl['config']))
        && (
            false !== strpos($query, 'UPDATE')
            || false !== strpos($query, 'INSERT')
            || false !== strpos($query, 'REPLACE')
        );
}

function func_dev_check_mysql_free_result_calls() {
    $resources = db_sql_resource();
    assert('empty($resources) /*'.__FUNCTION__.' Some queries was not free via mysql_free_result()' . print_r($resources, true) . '*/');
}

function func_add_delayed_query($query, $query_type)
{
    global $sql_tbl;

    db_query("INSERT INTO $sql_tbl[delayed_queries] (query_type, query, date) VALUES('$query_type','" . addslashes($query) . "','".XC_TIME."')");
}

/**
 * Run insert queries in batch,
 * Possible run func_run_delayed_query('views_stats%'); func_run_delayed_query(); func_run_delayed_query('views_stats_products');
 */
function func_run_delayed_query($query_type = '')
{
    global $sql_tbl, $current_area;

    $flush_threshold = array(
        'views_stats_products' => 10
    );

    // Do not use sorting for views_stats_products/views_stats_categories query types
    $sort_by = "ORDER BY id ASC";
    if (strpos($query_type, 'views_stats') !== false)
        $sort_by = '';

    if (strpos($query_type, '%') !== false) {
        $where = " WHERE query_type LIKE '$query_type' ";

    } elseif(!empty($query_type)) {
        $where = " WHERE query_type='$query_type' ";

    } else {
        $where = '';
    }

    $flush_queries = true;
    if (
        $current_area == 'C'
        && isset($flush_threshold[$query_type])
    ) {
        $count = func_query_first_cell("SELECT COUNT(id) FROM $sql_tbl[delayed_queries] $where");
        if ($count < intval($flush_threshold[$query_type]))
            $flush_queries = false;
    }

    if ($flush_queries) {
        $res = db_query("SELECT query FROM $sql_tbl[delayed_queries] $where $sort_by");

        $is_found = false;
        while ($row = db_fetch_array($res)) {
            db_query($row['query']);
            $is_found = true;
        }

        db_free_result($res);

        if ($is_found) {
            db_query("DELETE FROM $sql_tbl[delayed_queries] $where");
            return true;
        }
    }

    return false;
}

function func_backup_table_in_service_table($orig_tbl, $clear_original='no')
{
    global $sql_tbl;

    if (!empty($sql_tbl[$orig_tbl]))
        $orig_tbl = $sql_tbl[$orig_tbl];

    $copy_to_table = $orig_tbl . '_copy';

    $res = db_query("CREATE table IF NOT EXISTS `$copy_to_table` LIKE `$orig_tbl`");
    $res = $res && db_query("DELETE FROM `$copy_to_table`");
    $res = $res && db_query("INSERT INTO `$copy_to_table` SELECT * FROM `$orig_tbl`");

    if ($clear_original == 'clear_original')
        $res = $res && db_query("DELETE FROM `$orig_tbl`");

    return $res;
}

function func_restore_table_from_service_table($orig_tbl, $drop_copy = 'yes')
{
    global $sql_tbl;

    if (!empty($sql_tbl[$orig_tbl]))
        $orig_tbl = $sql_tbl[$orig_tbl];

    $copy_from_table = $orig_tbl . '_copy';

    db_query("DELETE FROM `$orig_tbl`");
    db_query("INSERT INTO `$orig_tbl` SELECT * FROM `$copy_from_table`");
    if ($drop_copy == 'yes') {
        db_query("DROP TABLE `$copy_from_table`");
    }

    return true;
}

function func_session_delete_expired_unknown_sid() {
    global $sql_tbl;
    return db_query("DELETE FROM $sql_tbl[session_unknown_sid] WHERE expiry_date < '" . XC_TIME . "'");
}

function func_session_delete_expired_session_history() {
    global $sql_tbl;
    return db_query("DELETE FROM $sql_tbl[session_history] WHERE dest_xid NOT IN (SELECT sessid FROM $sql_tbl[sessions_data]) AND xid NOT IN (SELECT sessid FROM $sql_tbl[sessions_data])");
}

?>
