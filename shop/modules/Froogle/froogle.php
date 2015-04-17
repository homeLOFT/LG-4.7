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
 * Froogle export module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v104 (xcart_4_7_0), 2015-02-17 23:56:28, froogle.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

define('FROOGLE_TAIL', '...');
define('FROOGLE_TAIL_LEN', strlen(constant('FROOGLE_TAIL')));
define('FROOGLE_MAX_DESCRIPTION_LENGTH', 10 * 1024); //The content in an attribute in an item exceeds 10 KB.

x_session_register('store_froogle_lng');

x_load('backoffice','taxes','product');

set_time_limit(0);

$location[] = array(func_get_langvar_by_name('lbl_froogle_export'), '');
include $xcart_dir.'/include/import_tools.php';

$is_ftp_module = '';
if(function_exists('ftp_connect') && !empty($config['Froogle']['froogle_username']) && !empty($config['Froogle']['froogle_password']))
    $is_ftp_module = 'Y';

$froogle_host = 'uploads.google.com';

x_session_register('store_froogle_filename');

// Export data
if (!empty($active_modules['Froogle']) && $REQUEST_METHOD == 'POST' && $mode == 'fcreate') {
    if (empty($froogle_file)) {
        if (empty($config['froogle_export_file'])) {
            $froogle_file = ($config['Froogle']['froogle_username'] ? $config['Froogle']['froogle_username'] : "froogle").".txt";
        } else {
            $froogle_file = $config['froogle_export_file'];
        }
    } elseif($froogle_file != $config['froogle_export_file']) {
        func_array2insert('config', array('name' => 'froogle_export_file', 'value' => $froogle_file, 'type' => 'text'), true);
    }
    $store_froogle_filename = $froogle_file;

    $froogle_location = $config['Froogle']['froogle_used_https_links'] == 'Y' ? $https_location : $http_location;

    $froogle_file = func_get_files_location() . XC_DS . $froogle_file;

    $fp = func_fopen($froogle_file, 'w', true);

    if ($fp !== false) {
        // Write file header

        // Full header:
        // title\tdescription\tlink\timage_link\tid\texpiration_date\tlabel\tprice\tprice_type\tcurrency\tpayment_accepted\tpayment_notes\tquantity\tbrand\tupc\tisbn\tmemory\tprocessor_speed\tmodel_number\tsize\tweight\tcondition\tcolor\tactor\tartist\tauthor\tformat\tproduct_type\tlocation

/*
        $file_header = "title\tdescription\tlink\timage_link\tid\tprice\tcurrency\tavailability\tshipping_weight\texpiration_date\tbrand\tcondition\tproduct_type";
*/
        // Define header for basic attributes
        $file_header = 
            /* Basic Product Information */
            "id\t" .
            "item_group_id\t" .             // for Variant Products only
            "title\t" .
            "description\t" .
            "product_type\t" .
            "link\t" .
            "image_link\t" .
            //"additional_image_link\t" .   // Additional URLs of images of the item
            "condition\t" .
            /* Availability & Price */
            "availability\t" .
            "price\t" . 
            //"currency\t" .                // combined with price. retired?
            /* Tax & Shipping */
            //"shipping\t" .                // provided in Google Merchant Center
            //"tax\t" .                     // provided in Google Merchant Center
            "shipping_weight\t" .
            /* Additional attributes - recommended */
            "expiration_date\t" .
            /* Unique Product Identifiers */
            "mpn\t" .
			"brand"
        ;

        // Define header for additional attributes
        $additional_attributes = array_keys(func_froogle_get_additional_attributes());
        foreach ($additional_attributes as $k=>$v) {
            $file_header .= "\t$v";
        }

        fputs($fp, $file_header."\n");

        $where = '';
        $fields = '';
        $joins = '';
        $variantid = '"variantid",'; // if (empty($active_modules['Product_Options']))

        if ($config['General']['show_outofstock_products'] != 'Y') {
            if (!empty($active_modules['Product_Options'])) {
                $where .= " AND " . XCVariantsSQL::getVariantField('avail') . ' > 0 ';
            } else {
                $where .= " AND $sql_tbl[products].avail > '0'";
            }
        }

        $joins .= "
            INNER JOIN $sql_tbl[pricing]
                ON $sql_tbl[pricing].productid = $sql_tbl[products].productid AND $sql_tbl[pricing].membershipid = '0'";

        $group_by = "GROUP BY $sql_tbl[products].productid";

        if (!empty($active_modules['Product_Options'])) {
            $variantid = "$sql_tbl[variants].variantid,";

            $joins .= XCVariantsSQL::getJoinQueryAllRows() . " AND $sql_tbl[pricing].variantid = $sql_tbl[variants].variantid";

            $fields .= ',' .
                XCVariantsSQL::getVariantField('productcode') . ' as productcode,' . 
                XCVariantsSQL::getVariantField('avail') . ' as avail,' .
                XCVariantsSQL::getVariantField('weight') . ' as weight';
            // select SKU for variant
            $group_by .= ','.
                XCVariantsSQL::getVariantField('productcode');
        }

        $froogle_lng = func_validate_language_code($froogle_lng);
        $joins .= " INNER JOIN {$sql_tbl['products_lng_' . $froogle_lng]} ON $sql_tbl[products].productid = {$sql_tbl['products_lng_' . $froogle_lng]}.productid";
        $fields .= ", {$sql_tbl['products_lng_' . $froogle_lng]}.* ";

        if (!empty($active_modules['Manufacturers'])) {
            $fields .= ",
                IF ($sql_tbl[manufacturers_lng].manufacturer != '', $sql_tbl[manufacturers_lng].manufacturer, $sql_tbl[manufacturers].manufacturer) as manufacturer";
            $joins .= "
                LEFT JOIN $sql_tbl[manufacturers]
                    ON $sql_tbl[products].manufacturerid = $sql_tbl[manufacturers].manufacturerid
                LEFT JOIN $sql_tbl[manufacturers_lng]
                    ON $sql_tbl[products].manufacturerid = $sql_tbl[manufacturers_lng].manufacturerid AND $sql_tbl[manufacturers_lng].code = '$froogle_lng'";
        }

        $query = "
            SELECT
                $variantid
                $sql_tbl[products].productcode as main_productcode,
                $sql_tbl[products].*,
                $sql_tbl[products_categories].categoryid,
                $sql_tbl[pricing].price
                $fields
            FROM 
                $sql_tbl[products_categories]
                INNER JOIN $sql_tbl[quick_prices]
                INNER JOIN $sql_tbl[products]
            $joins
            WHERE
                $sql_tbl[products].productid = $sql_tbl[products_categories].productid
                AND $sql_tbl[quick_prices].productid = $sql_tbl[products].productid
                AND $sql_tbl[products].forsale = 'Y'
                AND $sql_tbl[products_categories].avail = 'Y'
                AND $sql_tbl[pricing].quantity = 1
                $where
            $group_by
            HAVING (
                /* limit to 1 product for testing */
                /*xcart_products.productid = '16129' AND*/
                $sql_tbl[pricing].price > '0'
                OR $sql_tbl[products].product_type = 'C'
             )
             ORDER BY {$sql_tbl['products_lng_' . $froogle_lng]}.product
        ";

        $products = db_query($query);


        x_load('category');

        $cnt = 0;

        while ($product = db_fetch_array($products)) {

            // Define product additional attributes, and combine them with variants data

            $product_additional_attributes = func_froogle_get_product_additional_attributes($product['productid']);

        if (
            is_array($product_additional_attributes) 
            && !empty($product_additional_attributes)
        ) {

            if (
                !empty($active_modules['Product_Options']) &&
                !empty($product['variantid'])
            ) {
                // Get additional data for product variant
                $joins = "";
                $fields = "";

                $joins .= "
                    LEFT JOIN $sql_tbl[product_options_lng]
                        ON $sql_tbl[class_options].optionid = $sql_tbl[product_options_lng].optionid AND $sql_tbl[product_options_lng].code = '$froogle_lng'
                    LEFT JOIN $sql_tbl[class_lng]
                        ON $sql_tbl[classes].classid = $sql_tbl[class_lng].classid AND $sql_tbl[class_lng].code = '$froogle_lng'
                ";
                $fields .= "
                    IF(xcart_product_options_lng.option_name != '', xcart_product_options_lng.option_name, xcart_class_options.option_name) as option_name,
                    IF(xcart_class_lng.class != '', xcart_class_lng.class, xcart_classes.class) as class
                ";

                $query = "
                    SELECT
                        $fields
                    FROM ($sql_tbl[variant_items])
                    LEFT JOIN $sql_tbl[class_options]
                        ON $sql_tbl[class_options].optionid = $sql_tbl[variant_items].optionid
                    LEFT JOIN $sql_tbl[classes]
                        ON $sql_tbl[classes].classid = $sql_tbl[class_options].classid
                    $joins
                    WHERE $sql_tbl[variant_items].variantid = '$product[variantid]'
                ";

                $raw_product_options_data = func_query($query);

                /* structure of $raw_product_options_data array
                    Array
                        (
                            [option_name] => XXL
                            [class] => Size
                        )
                 */                
                $product_options_data = array();
                foreach ($raw_product_options_data as $k => $v) {
                    if (array_key_exists(strtolower($v['class']), $product_additional_attributes)) {
                        $product_additional_attributes[strtolower($v['class'])] = $v['option_name'];
                    }
                }
                unset($raw_product_options_data);
            }

            $product_additional_attributes = "\t" . implode("\t", $product_additional_attributes);

        } // if (is_array($product_additional_attributes) && !empty($product_additional_attributes))

        $product_additional_attributes = empty($product_additional_attributes) ? '' : $product_additional_attributes; 

            // Define product category path

            $catids = func_get_category_path($product['categoryid']);

            $cats = '';

            if (!empty($catids)) {
                $cats = func_query("SELECT categoryid, category FROM $sql_tbl[categories] WHERE categoryid IN ('".implode("','", $catids)."') AND avail = 'Y'");
                $catids = array_flip($catids);
                if (!empty($cats)) {
                    if (count($cats) != count($catids))
                        continue;

                    foreach ($cats as $k => $v) {
                        if (isset($catids[$v['categoryid']])) {
                            $catids[$v['categoryid']] = $v['category'];
                        }
                    }

                    $cats = str_replace("\t", " ", implode(" > ", $catids));
                }
            }

            // Define full description
            if (!empty($product['fulldescr']))
            if (!empty($product['fulldescr']) && strlen($product['fulldescr']) < FROOGLE_MAX_DESCRIPTION_LENGTH)
                $product['descr'] = $product['fulldescr'];

            // Define product image
            $image_ids = array();

            // Use the largest, full-size image according to https://support.google.com/merchants/answer/188494
            $prefered_image_type = 'P';
            if (
                !empty($product['variantid'])
                && !empty($active_modules['Product_Options'])
            ) {
                $image_ids['W'] = $product['variantid'];
                $prefered_image_type = 'W';
            }

            $image_ids['P'] = $product['productid'];
            $image_ids['T'] = $product['productid'];

            $image_data = func_get_image_url_by_types($image_ids, $prefered_image_type);

            if (!empty($image_data['image_url'])) {
                $tmbn = $image_data['image_url'];

                if (strpos($tmbn, $https_location) !== false) {
                    $tmbn = str_replace($https_location, $froogle_location, $tmbn);
                }
            } else {
                $tmbn = '';
            }

            $config['General']['apply_default_country'] = 'Y';
            $customer_info = array(
                'city' => $config['General']['default_city'],
                'state' => $config['General']['default_state'],
                'country' => $config['General']['default_country'],
                'zipcode' => $config['General']['default_zipcode'],
                'id' => 0
            );

            if (!empty($active_modules['Product_Options']))
                $product['price'] += func_get_default_options_markup($product['productid'], $product['price']);

            $product_taxed_price = func_tax_price($product['price'], $product['productid'], false, NULL, $customer_info['id'], '', false, 1, func_froogle_get_tax_options());
            $product['price'] = $product_taxed_price['taxed_price'];
            if ($config['Froogle']['froogle_used_https_links'] == 'Y') {
                $product_url = $https_location.constant('DIR_CUSTOMER')."/product.php?productid=".$product['productid'];
            } else {
                $product_url = $http_location.constant('DIR_CUSTOMER').'/'.func_get_resource_url('product', $product['productid'], '', false);
            }

            $appearence = func_get_appearance_data($product);
            if ($appearence['buy_now_buttons_enabled'] || $product['product_type'] == 'C')
                $availability = 'in stock';
            else    
                $availability = 'out of stock';

            // Post string
            $post =
                // unique identifier for all products (including variant products) - productcode, not productid
                $product['productcode']."\t".
                // do not include any data into item_group_id attribute if not variant product
                (($product['productcode'] != $product['main_productcode']) ? $product['main_productcode'] : "")."\t".
                func_froogle_convert($product['product'], 70)."\t".
                func_froogle_convert($product['descr'], 9900)."\t".
                (!empty($cats) ? $cats : '')."\t".
                $product_url."\t".
                $tmbn."\t".
                "new\t".
                $availability."\t". 
                // currency is combined with price
                number_format(round($product['price'], 2), 2, ".", "")." ".
                (empty($config['Froogle']['froogle_currency']) ? "USD" : $config['Froogle']['froogle_currency'])."\t".
                $product['weight'] . ' ' . $config['Froogle']['froogle_weight_unit']."\t".
                date("Y-m-d", XC_TIME+(empty($config['Froogle']['froogle_expiration_date']) ? 0.5 : $config['Froogle']['froogle_expiration_date'])*86400)."\t".
                $product['productcode']."\t".
                func_froogle_convert($product['manufacturer'], 256).
                $product_additional_attributes
            ;

            assert('count(explode("\t", $post)) == count(explode("\t", $file_header))');

            fputs($fp, $post."\n");
            $cnt++;
            if ($cnt % 100 == 0) {
                echo '.';
                if($cnt % 5000 == 0) {
                    echo "<br />\n";
                }

                func_flush();
            }
        }

        fclose($fp);
        func_chmod_file($froogle_file);

        $top_message['type'] = 'I';
        $top_message['content'] = func_get_langvar_by_name('msg_adm_froogle_file_success');
    }
    else {
        $top_message['type'] = 'E';
        $top_message['content'] = func_get_langvar_by_name(
            'msg_adm_froogle_file_unsuccess',
            array(
                'file_path' => $froogle_file,
                'dir' => func_get_files_location()
            )
        );
    }

    if ($froogle_lng)
        $store_froogle_lng = $froogle_lng;

    func_header_location('froogle.php');
}
elseif(!empty($active_modules['Froogle']) && $REQUEST_METHOD == 'POST' && $mode == 'fdownload' && $froogle_file) {
    $froogle_file = func_get_files_location().XC_DS.$froogle_file;
    // Download export file
    if (!file_exists($froogle_file)) {
        $top_message['content'] = func_get_langvar_by_name("lbl_file_not_found");
        $top_message['type'] = "E";
        func_header_location('froogle.php');
    }

    header("Content-type: application/force-download");
    header("Content-Disposition: attachment; filename=".basename($froogle_file));
    func_readfile($froogle_file);
    exit;
}
elseif(!empty($active_modules['Froogle']) && $REQUEST_METHOD == 'POST' && $mode == 'fupload' && $froogle_file && $is_ftp_module) {
    $store_froogle_filename = $froogle_file;
    $froogle_file = func_get_files_location().XC_DS.$froogle_file;
    // Upload export file to Froogle server
    if (!file_exists($froogle_file)) {
        $top_message['content'] = func_get_langvar_by_name("lbl_file_not_found");
        $top_message['type'] = "E";
        func_header_location('froogle.php');
    }

    if (function_exists('ftp_connect')) {
        $ftp = ftp_connect($froogle_host);
        $top_message['type'] = 'E';
        if($ftp && @ftp_login($ftp, $config['Froogle']['froogle_username'], $config['Froogle']['froogle_password'])) {
            ftp_pasv($ftp, true);
            $fp = func_fopen($froogle_file, 'r', true);
            if ($fp) {
                if (@ftp_fput($ftp, basename($froogle_file), $fp, FTP_BINARY)) {
                    $top_message['content'] = func_get_langvar_by_name('msg_adm_froogle_success');
                    $top_message['type'] = 'I';
                }
                else {
                    $top_message['content'] = func_get_langvar_by_name('msg_adm_err_froogle_FTP_write_failed');
                }

                fclose($fp);
            }
            else {
                $top_message['content'] = func_get_langvar_by_name('msg_adm_err_froogle_file_not_found');
            }

            ftp_quit($ftp);
        }
        else {
            $top_message['content'] = func_get_langvar_by_name('msg_adm_err_froogle_FTP_failed');
        }
    }
    else {
        @exec("ftp -v -u ftp://".$config['Froogle']['froogle_username'].":".$config['Froogle']['froogle_password']."@".$froogle_host."/".func_shellquote(basename($froogle_file))." ".func_shellquote($froogle_file)." 2>&1", $res);
        $res = @implode("\n", $res);
        if (strpos($res, "226 ") !== false) {
            $top_message['content'] = func_get_langvar_by_name('msg_adm_froogle_success');
            $top_message['type'] = 'I';
        }
        else {
            $top_message['type'] = 'E';
            $top_message['content'] = func_get_langvar_by_name('msg_adm_err_froogle_FTP_failed');
        }
    }

    func_header_location('froogle.php');
}

$smarty->assign('froogle_file', $store_froogle_filename);
$smarty->assign('def_froogle_file', ($config['Froogle']['froogle_username'] ? $config['Froogle']['froogle_username'] : "froogle").".txt");

$smarty->assign('is_ftp_module', $is_ftp_module);

$smarty->assign('main', 'froogle_export');

$smarty->assign('froogle_lng', $store_froogle_lng ? $store_froogle_lng : $shop_language);

// Assign the current location line
$smarty->assign('location', $location);

?>
