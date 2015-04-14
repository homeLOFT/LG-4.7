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
 * Class is used to cache X-Cart data. Singleton.
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v21 (xcart_4_7_0), 2015-02-17 23:56:28, class.xc_cache_lite.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

global $xcart_dir;
require_once $xcart_dir."/include/lib/PEAR/Cache_Lite/Lite.php";

class XC_Cache_Lite extends Cache_Lite {
    private $defaultCacheDir;
    private $defaultHashedDirectoryLevel = 0;
    private $defaultFileNameProtection = false;
    const CACHE_VERSION = '2';

    public static function get_instance()
    {//{{{
        static $cache_lite = false;

        if (!$cache_lite) {
            global $var_dirs, $xcart_fs_permissions_map;

            assert('!empty($var_dirs) /* XC_Cache_Lite::get_instance */');
            if (empty($var_dirs))
                $_cache_dir = dirname(__FILE__)."/../../var/cache/";
            else
                $_cache_dir = $var_dirs['cache'] . '/';

            $options = array(
                'cacheDir' => $_cache_dir,
                'lifeTime' => DATA_CACHE_TTL,
                'automaticSerialization' => true,
                'fileNameProtection' => false,
            );

            if (function_exists('func_get_php_execution_mode'))
                $options['hashedDirectoryUmask'] = $xcart_fs_permissions_map['var'.XC_DS.'cache']['dir'][func_get_php_execution_mode()];
            else
                $options['hashedDirectoryUmask'] = $xcart_fs_permissions_map['var'.XC_DS.'cache']['dir']['nonprivileged'];

            $self_class = __CLASS__;
            $cache_lite = new $self_class($options);
            $cache_lite->defaultCacheDir = $_cache_dir;
            $cache_lite->defaultHashedDirectoryLevel = $cache_lite->_hashedDirectoryLevel;
            $cache_lite->defaultFileNameProtection = false;

            if (defined('DEVELOPMENT_MODE')) {
                $cache_lite->setToDebug();
            }
        }
        
        return $cache_lite;
    }//}}}

    /*
     Overwrite to support ability to cache false/null values
    */
    public function save($data, $id = NULL, $group = 'default')
    {//{{{
        return parent::save(array('data' => $data, 'v' => self::CACHE_VERSION), $id, $group);
    }//}}}

    public function raiseError($msg, $code)
    {//{{{
        if (
            !defined('DEVELOPMENT_MODE')
            || !constant('DEVELOPMENT_MODE')
        ) {
            return true;
        }

        trigger_error("XC_Cache_Lite ERROR: $msg\n CODE: $code", E_USER_WARNING);
    }//}}}

    public function setLifeTime($newLifeTime)
    {// {{{
        if (empty($newLifeTime)) {
            parent::setLifeTime(DATA_CACHE_TTL);
        } else {
            parent::setLifeTime($newLifeTime);
        }
    } // }}}

    public function setCacheDir($newCacheDir) { // {{{
        if (empty($newCacheDir)) {
            parent::setOption('cacheDir', $this->defaultCacheDir);
        } else {
            parent::setOption('cacheDir', $this->defaultCacheDir . $newCacheDir . '/');
        }
    } // }}}

    public function setHashedDirectoryLevel($newHashedDirectoryLevel) { // {{{
        if (empty($newHashedDirectoryLevel)) {
            parent::setOption('hashedDirectoryLevel', $this->defaultHashedDirectoryLevel);
        } else {
            parent::setOption('hashedDirectoryLevel', $newHashedDirectoryLevel);
        }
    } // }}}

    public function setFileNameProtection($newFileNameProtection) { // {{{
        if (empty($newFileNameProtection)) {
            parent::setOption('fileNameProtection', $this->defaultFileNameProtection);
        } else {
            parent::setOption('fileNameProtection', $newFileNameProtection);
        }
    } // }}}
}

?>
