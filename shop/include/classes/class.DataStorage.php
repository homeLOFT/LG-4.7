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
 * Logging functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v6 (xcart_4_7_0), 2015-02-17 23:56:28, class.DataStorage.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

class FileRawDataStorage { // {{{
    var $signature = X_LOG_SIGNATURE;
    var $sgn_len = X_LOG_SIGNATURE_LENGTH;
    var $data = NULL;

    function __construct($file) { // {{{
        $this->file = $file;
        $this->sgn_len = strlen($this->signature);
        $this->get();
//        $this->print_r();
    } // }}}

    function get() { // {{{
        $this->load();
    } // }}}

    function pack() { // {{{
        return $this->data;
    } // }}}

    function unpack($data) { // {{{
        return $data;
    } // }}}

    function load() { // {{{
        $this->data = FALSE;
        if (file_exists($this->file) && filesize($this->file) > $this->sgn_len) {
            $fs = filesize($this->file);
            $fp = fopen($this->file, 'rb');
            if ($fp) {
                fseek($fp, $this->sgn_len);
                $data = fread($fp, $fs - $this->sgn_len);
                $this->data = $this->unpack($data);
                fclose($fp);
            }
        }
    } // }}}

    function save() { // {{{
        $fp = fopen($this->file, 'wb');
        if ($fp) {
            fwrite($fp, $this->signature);
            fwrite($fp, $this->pack());
            fclose($fp);
            func_chmod_file($this->file);
        }
    } // }}}

    function update($data) { // {{{
        if ($data !== $this->data) {
            $this->data = $data;
            $this->save();
        }
    } // }}}

    function print_r() { // {{{
        $file = $this->file.".".$this->get_suffix().".txt";
        file_put_contents($file, print_r($this->data, TRUE));
        func_chmod_file($file);
    } // }}}

    function get_suffix() { // {{{
        return get_class($this);
    } // }}}
} // }}}

class FileDataStorage extends FileRawDataStorage { // {{{
    function pack() { // {{{
        return serialize($this->data);
    } // }}}

    function unpack($data) { // {{{
        return unserialize($data);
    } // }}}
} // }}}

class PHPIniDataStorage extends FileDataStorage { // {{{
    function get() { // {{{
        $this->get_local_ini_settings();
        $this->filter();
    } // }}}

    function get_local_ini_settings() { // {{{
        $this->data = ini_get_all();
        foreach ($this->data as $k => $v) {
            $lv = &$v['local_value'];
            if (is_string($lv))
                $this->data[$k] = func_html_entity_decode($lv);
            else
                $this->data[$k] = $lv;
        }
    } // }}}

    function filter() { // {{{
        // these options are set in config.php
        func_unset(
            $this->data,
            'error_log',
            'ignore_repeated_errors',
            'log_errors',
            'log_errors_max_len',
            'magic_quotes_runtime',
            'session.bug_compat_warn',
            'max_execution_time',
            'mbstring.internal_encoding'
        );
    } // }}}
} // }}}

