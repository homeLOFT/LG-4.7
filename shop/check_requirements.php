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
 * Check necessary server requirements
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v120 (xcart_4_7_0), 2015-02-17 23:56:28, check_requirements.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * This script checks requirements
 */

if (
    defined('XCART_EXT_ENV')
    || in_array(
        basename($_SERVER['PHP_SELF']),
        array(
            'image.php',
            'banner.php',
        )
    )
) {
    return;
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'top.inc.php';

define('XCREQUIREMENTS_VERBOSE_MODE', 'VERBOSE_MODE');
define('XCREQUIREMENTS_LIVE_MODE', 'LIVE_MODE');

/**
 * Try to set needed values for some options
 */
ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase', 0);
func_cr_main();

/**
 * Supplementary functions
 */

function func_cr_init($init_mode = XCREQUIREMENTS_VERBOSE_MODE) { // {{{
$CHECK_REQUIREMENTS = array();
$CHECK_REQUIREMENTS['req_vars'] = array();

$CHECK_REQUIREMENTS['req_vars']['PHP version'] = array (
    'req_val' => '5.3.0',
    'critical' => 1,
    'msg' => "PHP upgrade is required",
);

$CHECK_REQUIREMENTS['req_vars']['PHP Server API'] = array (
    'req_val' => 'cgi-fcgi',
    'critical' => 0,
    'msg' => 'It is recommended to use Server API = FastCGI',
);

$CHECK_REQUIREMENTS['req_vars']['Perl-compatible regular expressions'] = array (
    'req_val' => TRUE,
    'critical' => 1,
    'param' => 'pcre',
	'mode' => 'extension_loaded',
);

$CHECK_REQUIREMENTS['req_vars']['MySQL/MySQLI support'] = array (
    'req_val' => TRUE,
    'critical' => 1,
    'param' => 'mysql|mysql',
	'mode' => 'func_cr_is_mysql_enabled',
);

$CHECK_REQUIREMENTS['req_vars']['Posix extension'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'posix',
	'mode' => 'extension_loaded',
);

$CHECK_REQUIREMENTS['req_vars']['OpenSSL support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'openssl',
	'mode' => 'extension_loaded',
	'msg' => 'OpenSSL extension is not installed. This may result in the usage of keys with poor resistance to cryptanalysis.',
);

$CHECK_REQUIREMENTS['req_vars']['Entropy file support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => '/dev/urandom',
    'mode' => 'is_readable',
    'msg' => '/dev/urandom is not readable. Your OS does not provide Entropy file support or there is a current open_basedir restriction in effect for this file. This is a security related setting. You can disregard this warning if you have OpenSSL support enabled and your PHP version is 5.3.7 or later.',

);

$CHECK_REQUIREMENTS['req_vars']['cURL support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'curl',
	'mode' => 'extension_loaded',
	'msg' => 'the software functionality will be limeted',
);

$CHECK_REQUIREMENTS['req_vars']['BCrypt support'] = array (
   'req_val' => 1,
   'critical' => FALSE,
   'param' => 'CRYPT_BLOWFISH',
   'mode' => 'func_cr_constant',
   'msg' => 'You are using a PHP version without <a href="http://php.net/crypt" target="_blank">bcrypt(CRYPT_BLOWFISH)</a> support. If you continue to use this version, a cryptographic hash function with poor resistance to cryptanalysis will be used for storing passwords. To ensure the security of user passwords at your store, it is strongly recommended that you upgrade your PHP version to the latest stable release (Do not use PHP versions prior to 5.3.7).',
);

$CHECK_REQUIREMENTS['req_vars']['MCrypt support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'mcrypt',
	'mode' => 'extension_loaded',
	'msg' => 'software emulation will be used',		
);

$CHECK_REQUIREMENTS['req_vars']['FTP support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'ftp',
	'mode' => 'extension_loaded',
	'msg' => 'Required for the Froogle/Google Base module',
);

$CHECK_REQUIREMENTS['req_vars']['MBString support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'mbstring',
	'mode' => 'extension_loaded',
);

$CHECK_REQUIREMENTS['req_vars']['iconv support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'iconv',
	'mode' => 'extension_loaded',
);

if (
    $init_mode == XCREQUIREMENTS_VERBOSE_MODE
    && func_cr_is_development_mode()
) {
    $CHECK_REQUIREMENTS['req_vars']['Memcache support'] = array (
        'req_val' => TRUE,
        'critical' => FALSE,
        'param' => 'memcache',
        'mode' => 'extension_loaded',
    );
}

$CHECK_REQUIREMENTS['req_vars']['XML support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'xml',
	'mode' => 'extension_loaded',
);

$CHECK_REQUIREMENTS['req_vars']['GD support'] = array (
    'req_val' => TRUE,
    'critical' => 0,
    'param' => 'gd',
	'mode' => 'extension_loaded',
);
$CHECK_REQUIREMENTS['req_vars']['zlib support'] = array (
    'req_val' => TRUE,
    'critical' => 0,
    'param' => 'zlib',
	'mode' => 'extension_loaded',
);

$CHECK_REQUIREMENTS['req_vars']['SOAP support'] = array (
    'req_val' => TRUE,
    'critical' => FALSE,
    'param' => 'soap',
	'mode' => 'extension_loaded',
);

$CHECK_REQUIREMENTS['req_vars']['safe_mode'] = array (
    'req_val' => FALSE,
    'critical' => 1,
	'param' => 'safe_mode',
	'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['file_uploads'] = array (
    'req_val' => TRUE,
    'critical' => 1,
	'param' => 'file_uploads',
	'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['sql.safe_mode'] = array (
    'req_val' => FALSE,
    'critical' => TRUE,
	'param' => 'sql.safe_mode',
	'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['magic_quotes_runtime'] = array (
    'req_val' => FALSE,
    'critical' => 0,
	'param' => 'magic_quotes_runtime',
    'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['magic_quotes_sybase'] = array (
    'req_val' => FALSE,
    'critical' => 0,
	'param' => 'magic_quotes_sybase',
	'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['zlib.output_compression'] = array (
    'req_val' => TRUE,
    'critical' => 0,
    'param' => 'zlib.output_compression',
    'mode' => 'ini_get_bool',
	'dep' => 'zlib support',
);

$CHECK_REQUIREMENTS['req_vars']['register_globals'] = array (
    'req_val' => FALSE,
    'critical' => 1,
    'param' => 'register_globals',
    'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['implicit_flush'] = array (
    'req_val' => FALSE,
    'critical' => 0,
    'param' => 'implicit_flush',
    'mode' => 'ini_get_bool',
);

$CHECK_REQUIREMENTS['req_vars']['allow_url_fopen'] = array (
    'req_val' => TRUE,
    'critical' => 0,
    'param' => 'allow_url_fopen',
    'mode' => 'ini_get_bool',
    'msg' => 'Enable this option if you need the remote images upload to work',
);

$CHECK_REQUIREMENTS['req_vars']['allow_url_include'] = array (
    'req_val' => FALSE,
    'critical' => 0,
    'param' => 'allow_url_include',
    'mode' => 'ini_get_bool',
    'msg' => 'Disable this option for security reason',
);

if (
    $init_mode == XCREQUIREMENTS_VERBOSE_MODE
    && func_cr_is_development_mode()
) {
    $CHECK_REQUIREMENTS['req_vars']['always_populate_raw_post_data'] = array (
        'req_val' => TRUE,
        'critical' => 0,
        'param' => 'always_populate_raw_post_data',
        'mode' => 'ini_get_bool',
    );
}

/*
 *  Misc parameters
 *
 **/
$CHECK_REQUIREMENTS['req_vars']['upload_max_filesize'] = array (
    'req_val' => '2M',
    'critical' => 0,
	'param' => 'upload_max_filesize',
    'msg' => 'May be too low',
);

$CHECK_REQUIREMENTS['req_vars']['GD Version'] = array (
    'req_val' => '2.0',
    'critical' => 0,
	'dep' => 'GD support',
);

$CHECK_REQUIREMENTS['req_vars']['SOAP support'] = array (
    'req_val' => TRUE,
    'critical' => 0,
    'param' => 'soap',
	'mode' => 'extension_loaded',
    'msg' => 'SOAP support is disabled. This may result in preventing usage of the latest Fedex shipping API\'s integartion.'
); // for Fedex

$CHECK_REQUIREMENTS['req_vars']['DOM support'] = array (
    'req_val' => TRUE,
    'critical' => 0,
    'param' => 'dom',
	'mode' => 'extension_loaded',
    'msg' => 'DOM support is disabled. This may result in preventing usage of the latest Fedex shipping API\'s integartion.'
); // for Fedex

static $used_functions = array();
if ($init_mode != XCREQUIREMENTS_LIVE_MODE) {
    @include dirname(__FILE__).'/include/used_functions.php';
}

$CHECK_REQUIREMENTS['req_vars']['disabled functions list'] = array (
    'req_val' => (isset($used_functions) && is_array($used_functions)) ? $used_functions : array(),
    'real_val' => array(),
    'critical' => 0,
    'msg' => 'Some functionality may be lost', 
);

$CHECK_REQUIREMENTS['show_details'] = 0;
$CHECK_REQUIREMENTS['dis_func'] = 0;
$CHECK_REQUIREMENTS['requirements'] = array(40,99,41,32,119,119,119,46,120,45,99,97,114,116,46,99,111,109);

    // Check only critical requirements
    if ($init_mode == XCREQUIREMENTS_LIVE_MODE) {
        foreach ($CHECK_REQUIREMENTS['req_vars'] as $k => $v) {
            $option = $CHECK_REQUIREMENTS['req_vars'][$k];
            if (empty($option['critical'])) {
                unset($CHECK_REQUIREMENTS['req_vars'][$k]);
            }
        }
    }

    return $CHECK_REQUIREMENTS;
} // }}}

function func_cr_check(&$CHECK_REQUIREMENTS) { // {{{
foreach ($CHECK_REQUIREMENTS['req_vars'] as $k => $v) {
	$option = &$CHECK_REQUIREMENTS['req_vars'][$k];
    $param = &$option['param'];
	$real_val = &$option['real_val'];
	$req_val = &$option['req_val'];
	$trigger = &$option['trigger'];
	$critical = &$option['critical'];
	$msg = &$option['msg'];
	$mode = &$option['mode'];
	$dep = &$option['dep'];
	$dep_verify = empty($dep) || empty($CHECK_REQUIREMENTS['req_vars'][$dep]['trigger']);

	if ($dep_verify) {
		if (!empty($mode)) {
			$real_val = $mode($param);
            $trigger = $real_val !== $req_val;
		} else {
		// legacy code {{{
    switch ($k) {
    /*
     *  PHP configuration options
     *
     **/
    case "disabled functions list":
        $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = array_filter(preg_split('/[, ]/', ini_get("disable_functions")));
        if (empty($CHECK_REQUIREMENTS['req_vars'][$k]['real_val'])) {
            $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = FALSE;
        } elseif (is_array($CHECK_REQUIREMENTS['req_vars'][$k]['real_val'])) {
            foreach ($CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] as $func) {
                if (in_array($func, $v['req_val'])) {
                    $CHECK_REQUIREMENTS['dis_func'] = 1;
                    $CHECK_REQUIREMENTS['req_vars'][$k]['trigger'] = 1;
                    break;
                }
            }

            unset($func);
            $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = implode(", ", $CHECK_REQUIREMENTS['req_vars'][$k]['real_val']);
        } elseif (!function_exists('phpinfo')) {
            $CHECK_REQUIREMENTS['dis_func'] = 1;
            $CHECK_REQUIREMENTS['req_vars'][$k]['trigger'] = 1;
            $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = 'phpinfo';
        } else {
            $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = 'Empty';
        }

        $CHECK_REQUIREMENTS['req_vars'][$k]['req_val'] = "Off or should not contain any of the following functions:<br />\n".implode(",<br />\n", $CHECK_REQUIREMENTS['req_vars'][$k]['req_val']);

        break;

    case 'upload_max_filesize':
        $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = ini_get($param);
        $CHECK_REQUIREMENTS['req_vars'][$k]['trigger'] = ((int)$CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] < (int)$CHECK_REQUIREMENTS['req_vars'][$k]['req_val']);
        break;

    /*
     *  Other settings
     *
     **/
    case "PHP version":
        $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = phpversion();
        if ($CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] < $v['req_val'])
            $CHECK_REQUIREMENTS['req_vars'][$k]['trigger'] = 1;
        break;

    case "PHP Server API":
        $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = PHP_SAPI;
        if (!stristr(PHP_SAPI, 'fcgi'))
            $CHECK_REQUIREMENTS['req_vars'][$k]['trigger'] = 1;
        break;

    case "GD Version":
        $CHECK_REQUIREMENTS['req_vars'][$k]['real_val'] = (function_exists("gd_info") && $gd_config = gd_info()) ? $gd_config['GD Version'] : 'n/a';
        $CHECK_REQUIREMENTS['req_vars'][$k]['trigger'] = isset($gd_config['GD Version']) ? !preg_match('/[^0-9]*2\./', $gd_config['GD Version']) : 1;
        break;

	}
		// }}}
		}
	} else {
		$trigger = 1;
		$real_val = "Dependency failed:<br />\n$dep";
	}

    if ($trigger && $critical) {
        $CHECK_REQUIREMENTS['show_details'] = 1;
    }
}
} // }}}

function func_cr_main() { // {{{
    $verbose_mode = 
        (   TRUE
            && isset($_GET['checkrequirements'])
            && !empty($_GET['auth_code'])
            && func_check_auth_code($_GET['auth_code'])
        );

    if ($verbose_mode)
        $CHECK_REQUIREMENTS = func_cr_init(XCREQUIREMENTS_VERBOSE_MODE);
    else 
        $CHECK_REQUIREMENTS = func_cr_init(XCREQUIREMENTS_LIVE_MODE);

    if (
        isset($CHECK_REQUIREMENTS['show_requirements'])
        || isset($_GET['trigger'])
    ) {
        foreach ($CHECK_REQUIREMENTS['requirements'] as $val)
            echo chr($val);
        exit;
    }

    func_cr_check($CHECK_REQUIREMENTS);
    if (
        !$verbose_mode
        && $CHECK_REQUIREMENTS['show_details']
    ) {
        // Init CHECK_REQUIREMENTS array again in VERBOSE_MODE
        $CHECK_REQUIREMENTS = func_cr_init(XCREQUIREMENTS_VERBOSE_MODE);
        func_cr_check($CHECK_REQUIREMENTS);
    }

    if (
        $CHECK_REQUIREMENTS['show_details']
        || $verbose_mode
    ) {
        func_cr_print($CHECK_REQUIREMENTS);
    }
} // }}}

function func_cr_print($CHECK_REQUIREMENTS) { // {{{
    global $xcart_dir;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Checking requirements...</title>
<style type="text/css">
<!--
BODY {
    MARGIN: 0px;
    PADDING: 0px;
}
FORM {
    MARGIN: 0px;
}
TABLE {
    WIDTH: 100%;
}
TABLE,IMG {
    BORDER: 0px;
}
TABLE TH {
    BACKGROUND-COLOR: #AAAAAA;
}
TD,TH {
    TEXT-ALIGN: left;
    VERTICAL-ALIGN: top;
}
TR.Pass {
    BACKGROUND-COLOR: #EEFFEE;
}

TR.PassSelected {
    BACKGROUND-COLOR: #DDFFDD;
}

TR.Failed {
    BACKGROUND-COLOR: #FFEEEE;
}

TR.FailedSelected {
    BACKGROUND-COLOR: #FFDDDD;
}

TR.Warning {
    BACKGROUND-COLOR: #EEEEFF;
}

TR.WarningSelected {
    BACKGROUND-COLOR: #DDDDFF;
}

.Pass, .PassSelected {
    COLOR: #00CC00;
}

.Failed, .FailedSelected {
    COLOR: #CC0000;
}

.Warning, .WarningSelected {
    COLOR: #0000CC;
}

.critical {
    FONT-WEIGHT: bold;
}

-->
</style>
</head>
<body>

<table cellpadding="2" cellspacing="2">
<tr>
    <th>Option</th>
    <th>Expected value</th>
    <th>Detected value</th>
    <th>Status</th>
    <th>Comments</th>
</tr>

<tr>
    <td>Operation system</td>
    <td align="center">-</td>
    <td align="center">
<?php
list($os_type, $tmp) = explode(" ", php_uname());
echo $os_type;
?>
    </td>
    <td align="center"><font class="OK">OK</font></td>
    <td>&nbsp;</td>
</tr>
<?php
/**
 * Display results in the HTML format
 */
$i = TRUE;
foreach ($CHECK_REQUIREMENTS['req_vars'] as $k => $v) {
    $CHECK_REQUIREMENTS['status'] = '';
    $CHECK_REQUIREMENTS['msg'] = "&nbsp;";
    if (!empty($CHECK_REQUIREMENTS['req_vars'][$k]['trigger'])) {
        switch ($k) {
        case 'magic_quotes_gpc':
            $CHECK_REQUIREMENTS['status'] = 'Warning';
            $CHECK_REQUIREMENTS['msg'] = "Emulation is used";
            break;
        default:
            $critical = &$CHECK_REQUIREMENTS['req_vars'][$k]['critical'];
            $CHECK_REQUIREMENTS['status'] = !isset($critical) || $critical ? 'Failed' : 'Warning';
            $msg = &$CHECK_REQUIREMENTS['req_vars'][$k]['msg'];
            $CHECK_REQUIREMENTS['msg'] = !isset($msg) ? "Please check php.ini to correct problem" : $msg;
        }
    }

    if (!empty($v['critical'])) {
        $class = 'critical ';
    } else {
        $class = '';
    }

    if (in_array($CHECK_REQUIREMENTS['status'], array('Failed', 'Warning'))) {
        $class .= $CHECK_REQUIREMENTS['status'];
    } else {
        $class .= 'Pass';
    }

    if ($i = !$i) {
        $class .= 'Selected';
    }

?>
<tr class="<?php echo $class; ?>">
    <td><?php echo $k; ?></td>
    <td align="center"><?php echo func_echo_bool($CHECK_REQUIREMENTS['req_vars'][$k]['req_val']); ?></td>
    <td align="center"><?php echo func_echo_bool($v['real_val']); ?></td>
    <td align="center"><?php echo $CHECK_REQUIREMENTS['status'] ? $CHECK_REQUIREMENTS['status'] : "OK"; ?></td>
    <td><?php echo $CHECK_REQUIREMENTS['msg']; ?></td>
</tr>
<?php
}

unset($k, $v);
?>
</table>

<?php
if ($CHECK_REQUIREMENTS['show_details']) {
?>
<br />
Please contact your host administrators and ask them to correct PHP-settings for your site according to the requirements above.<br />
<br />
<?php
}

?>

<table cellpadding="2" cellspacing="2">
<tr>
    <th>Directory</th>
    <th>Permissions</th>
    <th>Required</th>
    <th>Comments</th>
</tr>

<tr>
    <td> (root) <?php echo $xcart_dir; ?></td>
    <td align="center"><?php echo sprintf("%o",fileperms($xcart_dir)); ?></td>
    <td align="center">xx755</td>
    <td></td>
</tr>

<tr class="Selected">
    <td> (customer) <?php echo DIR_CUSTOMER; ?></td>
    <td align="center"><?php if (file_exists($xcart_dir.DIR_CUSTOMER)) echo sprintf("%o",fileperms($xcart_dir.DIR_CUSTOMER)); else echo "does not exist"; ?></td>
    <td align="center">xx755</td>
    <td></td>
</tr>

<tr>
    <td> (admin) <?php echo DIR_ADMIN; ?></td>
    <td align="center"><?php if (file_exists($xcart_dir.DIR_ADMIN)) echo sprintf("%o",fileperms($xcart_dir.DIR_ADMIN)); else echo "does not exist"; ?></td>
    <td align="center">xx755</td>
    <td></td>
</tr>

<tr class="Selected">
    <td> (provider) <?php echo DIR_PROVIDER; ?></td>
    <td align="center"><?php if (file_exists($xcart_dir.DIR_PROVIDER)) echo sprintf("%o",fileperms($xcart_dir.DIR_PROVIDER)); else echo "does not exist"; ?></td>
    <td align="center">xx755</td>
    <td></td>
</tr>

<tr>
    <td> (partner) <?php echo DIR_PARTNER; ?></td>
    <td align="center"><?php if (file_exists($xcart_dir.DIR_PARTNER)) echo sprintf("%o",fileperms($xcart_dir.DIR_PARTNER)); else echo "does not exist"; ?></td>
    <td align="center">xx755</td>
    <td></td>
</tr>

</table>
<br />
</body>
</html>
<?php
exit; // if details are displayed, don't load X-Cart store page.
} // }}}

function func_cr_constant($constant) { // {{{
    if (defined($constant))
        return constant($constant);
    else       
        return false;  
} // }}}

function func_cr_is_mysql_enabled($param) { // {{{
    return extension_loaded('mysqli') || extension_loaded('mysql');
} // }}}

function func_check_auth_code($code) { // {{{
    $config = dirname(__FILE__).'/config.php';
    if (!file_exists($config)) {
        return TRUE;
    }

    $text = array_filter(file($config), 'func_check_auth_code_filter');
    if (empty($text)) {
        return FALSE;
    }

    eval(current($text));
    return $installation_auth_code == $code;
} // }}}

function func_cr_is_development_mode() { // {{{
    static $res;
    if (isset($res)) {
        return $res;
    }

    $config = dirname(__FILE__) . '/config.php';
    if (!file_exists($config)) {
        $res = FALSE;
        return FALSE;
    }

    $config_text = file_get_contents($config);

    $res =  preg_match('/^define..DEVELOPMENT_MODE./m', $config_text);
    return $res;
} // }}}

function func_check_auth_code_filter($var) { // {{{
    return 1 === preg_match('/^\$installation_auth_code = /', $var);
} // }}}

function func_echo_bool($val) { // {{{
    if (is_string($val)) {
        return $val;
    } elseif (is_bool($val)) {
        return $val ? "On" : "Off";
    } else {
        return print_r($val, TRUE);
    }
} // }}}

function ini_get_bool($param) { // {{{
    static $settings;

    if (empty($settings)) {
        $settings = ini_get_all();
    }

    if (empty($settings[$param]['local_value'])) {
        return FALSE;
    }

    $value = trim($settings[$param]['local_value']);
    if ($value === "1" || strcasecmp($value,'on') === 0) {
        return TRUE;
    }

    if ($value === "0" || strcasecmp($value,'off') === 0) {
        return FALSE;
    }

    assert('FALSE; /* '.__FUNCTION__.': Unknown boolean value in php configuration */');
    return !empty($settings[$param]['local_value']);
} // }}}
?>
