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
 * Show image
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v86 (xcart_4_7_0), 2015-02-17 13:29:01, image.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/top.inc.php';

define('QUICK_START', true);
define('SKIP_CHECK_REQUIREMENTS.PHP', true);

define('SET_EXPIRE', XC_TIME + 864000);

if (!isset($_GET['tmp'])) {

    define('DO_NOT_START_SESSION', 1);

}

require __DIR__.'/init.php';

x_load('files');

$is_substitute = !isset($_GET['ts']);

if (empty($id)) {

    $id = false;

}

if (
    empty($type)
    || !is_string($type)
    || !isset($config['available_images'][$type])
) {

    $type = 'T';

} else {

    $type = strtoupper($type);

}

$image      = '';
$image_type = '';
$image_path = '';
$image_size = 0;

if (isset($_GET['tmp'])) {

    x_session_register('file_upload_data');

    if (!empty($file_upload_data[$type])) {

        $image_posted = $file_upload_data[$type];

        if (
            $image_posted['date'] == 0
            || (XC_TIME - $image_posted['date']) > 1800
        ) {

            func_unset($file_upload_data, $type);

            unset($image_posted);

        } elseif (
            !empty($image_posted['file_path'])
            && $image_posted['id'] == $id
            && $image_posted['type'] == $type
        ) {
            $image_path = $image_posted['file_path'];
            $image_type = $image_posted['image_type'];
            $image_size = (!empty($image_posted['file_size'])) ? $image_posted['file_size'] : $image_posted['image_size'];
        }

    }

}

$orig_type = $type;

if (
    zerolen($image)
    && zerolen($image_path)
    && isset($config['available_images'][$type])
    && !empty($sql_tbl['images_' . $type])
    && !empty($id)
) {
    $hash_types = array();

    $i = 0;

    $max_attempts = count($config['available_images']);

    while ($i++ < $max_attempts) {
        // counting attempts to prevent infinite loop

        $_table = $sql_tbl['images_' . $type];

        $_field = (($config['available_images'][$type] == 'U')
            ? 'id'
            : 'imageid'
        );

        $result = db_query('SELECT image, image_path, image_type, md5, image_size, filename FROM ' . $_table . ' WHERE ' . $_field . '=\'' . $id . '\' LIMIT 1');

        if (
            $result
            && db_num_rows($result) > 0
        ) {

            list(
                $image,
                $image_path,
                $image_type,
                $md5,
                $image_size,
                $_filename
            ) = db_fetch_row($result);

            if (
                zerolen($image)
                && zerolen($image_path)
                && !zerolen($_filename)
            ) {

                x_load('image');

                $image_path = func_image_dir($type) . '/' . $_filename;

            }

            db_free_result($result);

            break;

        }

        if ($is_substitute) {

            if (
                !empty($config['substitute_images'][$type])
                && isset($config['available_images'][$config['substitute_images'][$type]])
                && !isset($hash_types[$config['substitute_images'][$type]])
            ) {

                $type = $config['substitute_images'][$type];

                $hash_types[$type] = true;

                continue;
            }

            if (
                $type == 'W'
                && !empty($active_modules['Product_Options'])
            ) {

                $tmp_id = XCVariantsSQL::getProductidByVariantid($id);

                if ($tmp_id) {

                    $id                = $tmp_id;
                    $type              = 'P';
                    $hash_types[$type] = true;

                    continue;

                }

            }

        }

        db_free_result($result);

        break;

    }

    // content of image ($image) takes precedence on
    // reading file from filesystem ($image_path)

    if (!zerolen($image)) {

        $image_path = '';

        if ($config['setup_images'][$type]['md5_check'] == 'Y') {

            $image_md5 = md5($image);

        }
    }

    if (
        !zerolen($image_path)
        && !is_url($image_path)
    ) {

        if (!is_readable($image_path)) {

            $image_path = '';

        } elseif ($config['setup_images'][$type]['md5_check'] == 'Y') {

            $image_md5 = func_md5_file($image_path);

        }

    }

    if (
        (
            !zerolen($image)
            || !zerolen($image_path)
        )
        && $config['setup_images'][$type]['md5_check'] == 'Y'
        && $image_md5 !== $md5
    ) {

        $image = $image_path = '';

    }

}

if (
    zerolen($image)
    && zerolen($image_path)
) {
    // when image is not available, use the "default image"

    $type = $orig_type;

    $image_path = $default_image;

    if (
        isset($config['setup_images'][$type]['default_image'])
        && $config['setup_images'][$type]['default_image'] != $default_image
        && !isset($_GET['ts'])
    ) {
        $image_path = $config['setup_images'][$type]['default_image'];
    }

    $tmp = func_get_image_size($image_path);

    $image_size = $tmp[0];

    $image_type = empty($tmp[3])
        ? 'image/gif'
        : $tmp[3];

}

header('Content-Type: ' . $image_type);

if ($image_size > 0) {

    header('Content-Length: ' . $image_size);

    if (
        defined('BENCH')
        && constant('BENCH')
    ) {
        $__smarty_size += $image_size;
    }

}

if (zerolen($image)) {

    func_readfile($image_path, true);

} else {

    echo $image;

}

?>
