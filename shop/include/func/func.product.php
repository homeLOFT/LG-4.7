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
 * Functions related to products functionality
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v256 (xcart_4_7_0), 2015-02-17 23:56:28, func.product.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Delete product from products table + all associated information
 * $productid - product's id
 */

abstract class XCProduct {
    const SELECT_PRODUCT_CUSTOMER_MODE = 0; // is equal to old false value
    const SELECT_PRODUCT_SHOW_DISABLED = 1; // is equal to old true value
    const SELECT_PRODUCT_ADMIN_PREVIEW = 2;
}

class XCSearchProducts {
    const MAX_LIMIT_TO_DISABLE_GROUPBY = 20;
    const POSSIBLE_PRODUCT_DUPLICATES = 40;
    const SKIP_ALL_POSSIBLE = 'SKIP_ALL_POSSIBLE';
    const SHOW_PRODUCTNAME = 'SHOW_PRODUCTNAME';
    const SHOW_TAXED_PRICE = 'SHOW_TAXED_PRICE';
    const PRODUCT_NUMBER_2SKIP_GROUP_BY = 70000;
    static $tblsToCheckAvailability = array('category_memberships','product_memberships','products_categories');

    public static function isQueryWithGroupBy($limit, $use_group_by = TRUE) { // {{{
        return $limit > self::MAX_LIMIT_TO_DISABLE_GROUPBY || empty($limit) || $use_group_by;
    } // }}}

    public static function getLimitWithDuplicates($limit) { // {{{
        return self::POSSIBLE_PRODUCT_DUPLICATES*$limit;
    } // }}}

    public static function getThumbDimsByIds($ids) { // {{{
        global $sql_tbl;

        static $res;

        $_key = md5(serialize($ids));

        if (isset($res[$_key])) {
            return $res[$_key];
        }

        $res[$_key] = func_query_hash("SELECT id, image_x, image_y FROM $sql_tbl[images_T] WHERE id IN ('" . implode("','", $ids) . "')", 'id', false);

        return $res[$_key];
    } // }}}

    public static function getSkipTablesByTemplate($tpl) { // {{{

        static $must_have_tables_with_includes;

        if (!isset($must_have_tables_with_includes)) {
            $must_have_tables_with_includes = array(
                self::SKIP_ALL_POSSIBLE => array(),
                self::SHOW_PRODUCTNAME => array(self::getSelfMustHaveTablesByTemplate(self::SHOW_PRODUCTNAME)),
                'main/popup_product.tpl' => array(
                    self::getSelfMustHaveTablesByTemplate(self::SHOW_PRODUCTNAME),
                    self::getSelfMustHaveTablesByTemplate('main/popup_product.tpl'),
                ),
                'customer/simple_products_list.tpl' => array(
                    self::getSelfMustHaveTablesByTemplate('customer/simple_products_list.tpl'),
                    self::getSelfMustHaveTablesByTemplate('modules/On_Sale/on_sale_icon.tpl'),
                    self::getSelfMustHaveTablesByTemplate('product_thumbnail.tpl')
                ),
                'modules/Add_to_cart_popup/product_added.tpl' => array(
                    self::getSelfMustHaveTablesByTemplate('modules/Add_to_cart_popup/product_added.tpl'),
                    self::getSelfMustHaveTablesByTemplate('product_thumbnail.tpl'),
                ),
                'modules/Recently_Viewed/content.tpl' => array(
                    self::getSelfMustHaveTablesByTemplate('modules/Recently_Viewed/content.tpl'),
                    self::getSelfMustHaveTablesByTemplate('modules/On_Sale/on_sale_icon.tpl'),
                ),
                'modules/Feature_Comparison/popup_product.tpl' => array(
                    self::getSelfMustHaveTablesByTemplate(self::SHOW_PRODUCTNAME),
                    self::getSelfMustHaveTablesByTemplate('modules/Feature_Comparison/popup_product.tpl'),
                ),
                'modules/Upselling_Products/related_products.tpl' => array(
                    self::getSelfMustHaveTablesByTemplate(self::SHOW_TAXED_PRICE),
                    self::getSelfMustHaveTablesByTemplate('customer/simple_products_list.tpl'),
                ),
            );
            $must_have_tables_with_includes['modules/Recommended_Products/recommends.tpl'] = $must_have_tables_with_includes['modules/Upselling_Products/related_products.tpl'];
            $must_have_tables_with_includes['modules/Bestsellers/bestsellers.tpl'] = $must_have_tables_with_includes['modules/Upselling_Products/related_products.tpl'];
        }

        if (isset($must_have_tables_with_includes[$tpl])) {
            $must_have_tables = $must_have_tables_with_includes[$tpl];
            $tables = array('classes', 'extra_fields', 'images_T', 'product_features', 'product_taxes', 'variants', 'pricing');
            foreach($must_have_tables as $must_tables) {
                $tables = array_diff($tables, $must_tables);
            }

            return $tables;
        } else {
            assert('FALSE /* '.__FUNCTION__.': tables for the template is not defined */');
            return array();
        }

        return array();

    } // }}}

    private static function getSelfMustHaveTablesByTemplate($tpl) { // {{{
        global $active_modules, $current_area;
        /* images_T is used to get 
            product.tmbn_url
            product.tmbn_x
            product.tmbn_y
        */

        /* variants is used to get 
            product.avail
            product.def
            product.is_product_row
            product.is_variant
            product.productcode
            product.variantid
            product.weight
        */

        /* classes is used to get 
            product.is_product_options
        */

        /*
            pricing is used to get
                product.taxed_price
                product.price
        */

        $self_must_have_tables = array(
            self::SHOW_PRODUCTNAME => array('products_lng_current'),
            self::SHOW_TAXED_PRICE => array('product_taxes'),
            'main/popup_product.tpl' => array('products_categories'),
            'product_thumbnail.tpl' => array('images_T', 'products_lng_current'),
            'customer/simple_products_list.tpl' => array('images_T', 'pricing', 'products_lng_current'),
            'modules/Add_to_cart_popup/product_added.tpl' => array('images_T', 'pricing', 'products_lng_current','classes'),
            'modules/Recently_Viewed/content.tpl' => array('images_T', 'pricing', 'products_lng_current','product_taxes'),
            'modules/Feature_Comparison/popup_product.tpl' => array('product_features','products_categories'),
        );

        if (!empty($active_modules['On_Sale'])) {
            $self_must_have_tables['modules/On_Sale/on_sale_icon.tpl'] = $self_must_have_tables['product_thumbnail.tpl'];
        }

        if (isset($self_must_have_tables[$tpl])) {
            if (
                in_array('variants', $self_must_have_tables[$tpl])
                && $current_area == 'C'
            ) {
                // Do not disable pricing to allow condition 'quick_prices.variantid = variants.variantid AND quick_prices.priceid = pricing.priceid'
                $self_must_have_tables[$tpl][] = 'pricing';
            }
            return $self_must_have_tables[$tpl];
        } else {
            return array();
        }
    } // }}}
    
}

abstract class XCSQLRanges {
    protected $sqlOffset;
    protected $rangeSize;
    protected $is_display_np_products;

    protected function queryProducts($query_in, $force_regenerate = FALSE) { // {{{
        global $sql_tbl;

        $md5_query = md5($query_in);
        $query = $query_in;
        $md5_args = $this->getRangeName($md5_query, $this->sqlOffset, $this->rangeSize);

        if ($force_regenerate !== XCRangeProductIds::FORCE_REGENERATE) {
            $data = func_get_cache_func($md5_args, 'getRangeProductIds');
            $data = $data ? $data['data'] : $data;
            if (is_array($data)) {
                if (
                    $this->is_display_np_products
                    && !empty($data)
                ) {
                    $this->setCurrentRange($md5_args);
                }

                return $data;
            }
        }

        if (
            stripos($query, XCRangeProductIds::getSqlMINPriceField()) !== false
            && preg_match('/ORDER BY .*\b' . XCRangeProductIds::getSqlMINPriceField('get_alias_name') . '\b/i', $query)
        ) {
            // To avoid sql error related to 'GROUP BY price '
            $substr_fields = "$sql_tbl[products].productid, " . XCRangeProductIds::getSqlMINPriceField();
        } else {
            $substr_fields = "$sql_tbl[products].productid";
        }

        $query = preg_replace('/SELECT .* FROM /', "SELECT $substr_fields FROM ", $query);

        // We do not need second price column in func_query_column
        $ids = func_query_column($query . " LIMIT {$this->sqlOffset}, {$this->rangeSize}");
        if (empty($ids)) {
            $ids = array();
        }

        func_save_cache_func($ids, $md5_args, 'getRangeProductIds');

        if ($this->is_display_np_products) {
            func_save_cache_func($query_in, $md5_query, 'getRangeProductIdsQuery');

            if (!empty($ids)) {
                $this->setCurrentRange($md5_args);
            }
        }

        return $ids;

    } // }}}

    protected function getOffsetRangeSizeFromName($range_name) { // {{{
        return explode('.', $range_name);
    } // }}}

    protected function getRangeName($md5_query, $sql_offset, $range_size) { // {{{
        return $md5_query . ".$sql_offset.$range_size";
    } // }}}

    protected function setCurrentRange($range_in) { // {{{
        global $last_search_range;

        if (empty($range_in)) {
            x_session_unregister('last_search_range', TRUE);
        } else {
            x_session_register('last_search_range');
            $last_search_range = $range_in;
        }
    } // }}}

}


class XCRangeProductIds extends XCSQLRanges {
    const FORCE_REGENERATE = 'FORCE_REGENERATE';
    const MIN_RANGE_SIZE = 1500;
    const CACHE_ACCURACY_LIMIT = 0.7;
    const TOTAL_ITEMS_PRECISION = 1000;

    private $max_nav_pages;
    private $objects_per_page;

    private $offsetInRange;
    private $totalItemsInRange;
    private $totalItemsOnPage;
    private $query;

    public function __construct($query, $first_page, $objects_per_page) { // {{{
        global $config;
        $this->query = $query;
        $this->objects_per_page = empty($objects_per_page) ? 10 : $objects_per_page;

        $this->max_nav_pages = max(intval($config['Appearance']['max_nav_pages']), 1);

        $rangeSize = ($this->max_nav_pages+2) * $this->objects_per_page;
        $rangeSize = max(intval(self::MIN_RANGE_SIZE / $this->objects_per_page) * $this->objects_per_page, $rangeSize, $this->objects_per_page);
        $offset = intval($first_page / $rangeSize) * $rangeSize;

        $this->sqlOffset = $offset;
        $this->offsetInRange = $first_page >= $offset 
            ? $first_page - $offset
            : $first_page;

        $this->rangeSize = $rangeSize;
        $this->totalItemsInRange = 0;
        $this->totalItemsOnPage = 0;
        $this->is_display_np_products = ($config['Appearance']['display_np_products'] == 'Y');
    } // }}}

    public function queryWithCachedIds() { // {{{
        
        $ids = $this->getRangeProductIds($this->query);
        $res = $this->queryWithCachedIdsSub($this->query, $ids);

        // Regenerate cache if at least one subrange(exclude last) has 70% of total items
        $check_cache_value = min($this->objects_per_page, $this->totalItemsOnPage);
        if (
            $this->totalItemsInRange > 0
            && $this->totalItemsOnPage > 0
            && (
                empty($res)
                || count($res) < $check_cache_value * self::CACHE_ACCURACY_LIMIT
            )
        ) {
            $ids = $this->getRangeProductIds($this->query, self::FORCE_REGENERATE);
            $res = $this->queryWithCachedIdsSub($this->query, $ids);
        }

        return $res;
    } // }}}

    public function getTotalItems() { // {{{
        return $this->totalItemsInRange;
    } // }}}

    public function canUseTotalItems() { // {{{
        return $this->rangeSize > $this->totalItemsInRange && empty($this->sqlOffset);
    } // }}}

    public static function calculateApproximateTotals($total_items) { // {{{
        $total_rough_pages = 0;
        if (
            empty($total_items)
            || $total_items < self::TOTAL_ITEMS_PRECISION/10
        ) {
            return $total_items;
        }
        $i = 1;
        while (empty($total_rough_pages)) {
            $total_rough_pages = intval($total_items / (self::TOTAL_ITEMS_PRECISION / $i)) * self::TOTAL_ITEMS_PRECISION / $i;
            $i *= 10;
        }

        return $total_rough_pages;
    } // }}}

    public static function getSqlMINPriceField($mode = '') { // {{{
        global $sql_tbl;
        if ($mode == 'get_alias_name') {
            return 'price';
        } else {
            return 'MIN(' . $sql_tbl['pricing'] . '.price) AS price';
        }
    } // }}}

    private function queryWithCachedIdsSub($query, $ids) { // {{{
        global $sql_tbl;
        $this->totalItemsOnPage = 0;

        if ($this->totalItemsInRange < $this->offsetInRange)
            return array();
        else
            $ids = array_slice($ids, $this->offsetInRange, $this->objects_per_page);

        if (empty($ids))
            return array();

        $this->totalItemsOnPage = count($ids);

        $ids = array_filter($ids);
        if (empty($ids))
            return array();

        $cond = "WHERE $sql_tbl[products].productid IN (".implode(",", $ids).")";

        if (strpos($query, ' WHERE ') !== FALSE)
            $query_limit = str_replace(' WHERE ', " $cond AND " , $query);
        elseif (strpos($query, ' GROUP BY ') !== FALSE)
            $query_limit = str_replace(' GROUP BY ', " $cond GROUP BY " , $query);
        elseif (strpos($query, ' ORDER BY ') !== FALSE)
            $query_limit = str_replace(' ORDER BY ', " $cond ORDER BY " , $query);

        $res = func_query($query_limit);
        return $res;
    } // }}}

    private function getRangeProductIds($query, $force_regenerate = FALSE) { // {{{

        $ids = $this->queryProducts($query, $force_regenerate);
        $this->totalItemsInRange = count($ids);

        return $ids;

    } // }}}

    // Fake function to enable cache with GetRangeProductIdsQuery name
    private final function getRangeProductIdsQuery() { // {{{
    } // }}}

}

class XCRangeProductIdsAdmin {

    public static function isCacheRegenerationNeeded($new_product_info, $old_product_info, $is_variant, $geid, $fields) { // {{{
        global $config;

        $is_new_product = empty($old_product_info);

        // avail factor
        if (
            $config['General']['unlimited_products'] != 'Y'
            && $config['General']['show_outofstock_products'] != 'Y'
            && !$is_variant
        ) {
            // New avail is more then min_amount
            $stock_is_changed = ($new_product_info['avail'] >= $new_product_info['min_amount']);

            if (
                !$is_new_product
                && !($geid && !empty($fields['avail']))
            ) {
                // Previously the product was out of stock
                $stock_is_changed = $stock_is_changed && ($old_product_info['avail'] < $old_product_info['min_amount']);
            }
        } else {
            $stock_is_changed = $is_new_product;
        }

        // forsale factor
        $forsale_is_changed = ($new_product_info['forsale'] == 'Y');
        if (
            !$is_new_product
            && !($geid && !empty($fields['forsale']))
        ) {
            // Previously the product was disabled
            $forsale_is_changed = $forsale_is_changed && ($old_product_info['forsale'] != 'Y');
        }

        // category factor
        if (!$is_new_product) {
            $category_is_changed = $old_product_info['categoryid'] != $new_product_info['categoryid'];
            $category_is_changed = $category_is_changed || ($geid && !empty($fields['categoryid']));

            if ($config['General']['check_main_category_only'] != 'Y') {
                $main_cat_index = array_search($old_product_info['categoryid'], $old_product_info['categoryids']);
                if ($main_cat_index !== false) {
                    unset($old_product_info['categoryids'][$main_cat_index]);
                }
                $category_is_changed = $category_is_changed || ($geid && !empty($fields['categoryids']));
                $category_is_changed = $category_is_changed
                    || count($old_product_info['categoryids']) != count($new_product_info['categoryids']) 
                    || array_diff($old_product_info['categoryids'], $new_product_info['categoryids']);
            }

        } else {
            $category_is_changed = false;
        }

        return $stock_is_changed || $forsale_is_changed || $category_is_changed;
    } // }}}

    public static function dropCache() { // {{{
        return func_data_cache_clear('getRangeProductIds');
    } // }}}

}

class XCNextPrevProducts extends XCSQLRanges {
    const NEXT = '>=';
    const PREV = '<=';

    private $range;
    private $product;
    public function __construct($product_info, $range_in = '') { // {{{
        global $last_search_range, $config;

        if (empty($range_in)) {
            x_session_register('last_search_range');
            $range_in = $last_search_range;
        }
        $this->range = $range_in;
        $this->product = $product_info;
        $this->is_display_np_products = ($config['Appearance']['display_np_products'] == 'Y');
    } // }}}

    public function getNextPrev() { // {{{
        global $sql_tbl;

        $result = array('next' => array(), 'prev' => array());
        $performance_max_limit = 10;

        foreach (array('next' => self::NEXT, 'prev' => self::PREV) as $ind => $nextPrev) {
            $base_productid = $this->product['productid'];
            $iteration_num = 0;
            do {
                // Unset in cache founded disabled product from previous iteration
                if ($iteration_num > 0) {
                    $this->unsetProductInRange($next_prev_id);
                }

                $next_prev_id = $this->getRangeDefNextPrev($nextPrev, $base_productid);
                $base_productid = $next_prev_id;
                $iteration_num++;
            } while(
                !empty($this->range)
                && $next_prev_id !== 0
                && $iteration_num <= $performance_max_limit
                && $this->productIsDisabled($next_prev_id)
            );

            if (
                !empty($next_prev_id)
                && $iteration_num < $performance_max_limit
            ) {
                $result[$ind]['productid'] = $next_prev_id;
            }
        }

        return array_values($result);
    } // }}}

    protected function setCurrentRange($range) { // {{{
        parent::setCurrentRange($range);
        $this->range = $range;
    } // }}}

    private function getRangeDefNextPrev($nextPrev = self::NEXT, $base_productid) { // {{{

        if (!empty($this->range)) {
            $range = $this->findCurrentRangeByProductId($base_productid, $this->range);
            $this->setCurrentRange($range);
        }

        if (empty($this->range)) {
            return $this->getDefaultNextPrev($nextPrev);
        }

        $ids = self::_func_get_cache_func($this->range, 'getRangeProductIds');

        $current_prod_ind = is_array($ids) ? array_search($base_productid, $ids) : FALSE;
        if ($current_prod_ind !== FALSE) {
            // Current product is in the current search range
            list($md5_query, $this->sqlOffset, $this->rangeSize) = $this->getOffsetRangeSizeFromName($this->range);

            $_next_prev = self::searchNextPrevInArray($ids, $nextPrev, $current_prod_ind);
            if ($_next_prev !== FALSE) {
                // Next/Prev product is in the current range
                $nextPrevId = $_next_prev;
            } elseif(
                $this->sqlOffset > 0
                || $nextPrev == self::NEXT
            ) {
                // Next/Prev product may be in the next/previous range
                $next_prev_range_query = self::_func_get_cache_func($md5_query, 'getRangeProductIdsQuery');

                if (empty($next_prev_range_query)) {
                    $nextPrevId = 0;
                } else {
                    // Try to query last product in the previous range
                    if ($nextPrev == self::NEXT) {
                        $this->sqlOffset = abs($this->sqlOffset + $this->rangeSize);
                    } else {
                        $this->sqlOffset = abs($this->sqlOffset - $this->rangeSize);
                    }

                    $ids = $this->queryProducts($next_prev_range_query);
                    if (!empty($ids)) {
                        $i = 0;
                        $array_size = count($ids);
                        if ($nextPrev == self::PREV) {
                            // Search last non-empty element in the range
                            do {
                                $i++;
                                $nextPrevId = $ids[$array_size - $i];
                            } while (empty($nextPrevId) && $i < $array_size);
                        } else {
                            // Search first non-empty element in the range
                            do {
                                $nextPrevId = $ids[$i];
                                $i++;
                            } while (empty($nextPrevId) && $i < $array_size);
                        }
                    } else {
                        $nextPrevId = 0;
                    }
                }
            } elseif ($nextPrev == self::PREV) {
                // This is first range and the first product
                $nextPrevId = 0;
            }

            return $nextPrevId;
        }
        $this->setCurrentRange('');

        return $this->getDefaultNextPrev($nextPrev);
    } // }}}

    private function getDefaultNextPrev($nextPrev = self::NEXT) { // {{{
        global $config, $active_modules, $sql_tbl, $user_account;
        $sql_order = array(
            self::NEXT => 'ASC',
            self::PREV => 'DESC',
        );

        $query['query'] = " AND $sql_tbl[products_categories].main='Y' AND $sql_tbl[products_categories].categoryid='{$this->product['categoryid']}'";

        $orderby = $config['Appearance']['products_order']
            ? $config['Appearance']['products_order']
            : 'orderby';

        $query['orderbys'] = array(func_get_product_sql_orderby($orderby) . ' ' . $sql_order[$nextPrev]);
        $query['query'] .= " AND " . $this->getNextCondByOrderBy($orderby, $nextPrev);

        if ($orderby != 'title') {
            $query['orderbys'][] = "$sql_tbl[products_lng_current].product $sql_order[$nextPrev]";
            $query['query'] .= " AND " . $this->getNextCondByOrderBy('title', $nextPrev);
        }
        $query['skip_tables'] = XCSearchProducts::getSkipTablesByTemplate(XCSearchProducts::SKIP_ALL_POSSIBLE);

        $ids = func_search_products(
            $query,
            (isset($user_account) && isset($user_account['membershipid']))
                ? max(intval($user_account['membershipid']), 0)
                : 0,
            'skip_orderby',
            2,
            TRUE
        );

        $nextPrevId = $this->product['productid'];
        while (!empty($ids) && $nextPrevId == $this->product['productid']) {
            $nextPrev = array_shift($ids);
            $nextPrevId = $nextPrev['productid'];
        }

        if ($nextPrevId != $this->product['productid']) {
            $id = $nextPrevId;
        } else {
            $id = 0;
        }

        return $id;
    } // }}}

    private function getNextCondByOrderBy($orderby, $nextPrev) { // {{{
        global $sql_tbl;

        $where_rules = array (              
            'title'       => "product $nextPrev '" . addslashes($this->product['product']) . "'",
            'quantity'    => "$sql_tbl[products].avail $nextPrev '{$this->product['avail']}'",
            'orderby'     => "$sql_tbl[products_categories].orderby $nextPrev '{$this->product['orderby']}'",
            'price'       => "price $nextPrev '{$this->product['price']}'",
            'productcode' => "$sql_tbl[products].productcode $nextPrev '" . addslashes($this->product['productcode']) . "'",
        );

        if (!isset($where_rules[$orderby]))
            $orderby = 'title';

        return $where_rules[$orderby];

    } // }}}

    private function findCurrentRangeByProductId($productid, $range) { // {{{

        // Try to search in the current range
        $ids = self::_func_get_cache_func($range, 'getRangeProductIds');

        $current_prod_ind = is_array($ids) ? array_search($productid, $ids) : FALSE;
        if ($current_prod_ind !== FALSE)
            return $range;

        list($md5_query, $sqlOffset, $rangeSize) = $this->getOffsetRangeSizeFromName($range);

        // Try to search in the next range
        $ids = self::_func_get_cache_func($this->getRangeName($md5_query, $sqlOffset+$rangeSize, $rangeSize), 'getRangeProductIds');
        $current_prod_ind = is_array($ids) ? array_search($productid, $ids) : FALSE;
        if ($current_prod_ind !== FALSE)
            return $this->getRangeName($md5_query, $sqlOffset+$rangeSize, $rangeSize);

        // Try to search in the previous range
        if ($sqlOffset-$rangeSize >= 0) {
            $ids = self::_func_get_cache_func($this->getRangeName($md5_query, $sqlOffset-$rangeSize, $rangeSize), 'getRangeProductIds');
            $current_prod_ind = is_array($ids) ? array_search($productid, $ids) : FALSE;
            if ($current_prod_ind !== FALSE)
                return $this->getRangeName($md5_query, $sqlOffset-$rangeSize, $rangeSize);
        }

        return '';

    } // }}}

    private function productIsDisabled($productid) { // {{{
        global $sql_tbl, $user_account;

        $productid = intval($productid);

        if (empty($productid))
            return TRUE;

        $query['query'] = " AND $sql_tbl[products].productid = '$productid'";
        $query['use_group_by'] = FALSE;
        $query['skip_tables'] = XCSearchProducts::getSkipTablesByTemplate(XCSearchProducts::SKIP_ALL_POSSIBLE);
        $ids = func_search_products($query, @$user_account['membershipid'], 'skip_orderby', 1, TRUE);

        return empty($ids);
    } // }}}

    private function unsetProductInRange($productid) { // {{{
        global $sql_tbl, $user_account;

        if (empty($productid))
            return TRUE;

        $ids = func_get_cache_func($this->range, 'getRangeProductIds');
        $ids = $ids ? $ids['data'] : $ids;
        $current_prod_ind = is_array($ids) ? array_search($productid, $ids) : FALSE;

        if ($current_prod_ind !== FALSE) {
            $ids[$current_prod_ind] = 0;
            func_save_cache_func($ids, $this->range, 'getRangeProductIds');
        }

        return TRUE;
    } // }}}



    private static function searchNextPrevInArray($ids, $l_nextPrev, $current_ind) { // {{{
        $res = FALSE;
        do {
            if ($l_nextPrev == self::NEXT) {
                $index = $current_ind + 1;
            } else {
                $index = $current_ind - 1;
            }

            if (!isset($ids[$index])) {
                $res = FALSE;
                break;
            }

            if (!empty($ids[$index])) {
                $res = $ids[$index];
                break;
            }
            $current_ind = $index;

        } while(1);

        return $res;
    } // }}}

    private static function _func_get_cache_func($cache_key, $name) { // {{{
        static $res_hashes = array();
        $_key = $cache_key . $name;

        if (isset($res_hashes[$_key])) {
            $res = $res_hashes[$_key];
        } else {
            $res = func_get_cache_func($cache_key, $name);
            $res = $res ? $res['data'] : $res;
            $res_hashes[$_key] = $res;
        }

        return $res;
    } // }}}

}

class XCProductSalesStats {

    const RESTORE_VALUE = 'RESTORE_VALUE';

    const DIRTY_LIMIT_MULT = 3;
    const THRESHOLD_DECREASE_DELIMETER = 2;

    public static function insertNewRow($productid, $sales_stats = 0) { // {{{
        global $sql_tbl;

        if (empty($productid)) {
            return FALSE;
        }
        $productid = intval($productid);$sales_stats = intval($sales_stats);
        return db_query("INSERT INTO $sql_tbl[product_sales_stats] (productid, sales_stats) VALUES ($productid, $sales_stats)");
    } // }}}

    public static function updateRow($productid, $sales_stats) { // {{{
        global $sql_tbl;

        if (empty($productid)) {
            return FALSE;
        }
        $sales_stats = intval($sales_stats);
        return db_query("UPDATE $sql_tbl[product_sales_stats] SET sales_stats='$sales_stats' WHERE productid=" . intval($productid));
    } // }}}

    public static function deleteRow($productid) { // {{{
        global $sql_tbl;
        return db_query("DELETE FROM $sql_tbl[product_sales_stats] WHERE productid=" . intval($productid));
    } // }}}

    public static function deleteAll() { // {{{
        global $sql_tbl;
        return db_query("DELETE FROM $sql_tbl[product_sales_stats]");
    } // }}}

    public static function repairIntegrity($productid, $mode = '') { // {{{
        global $sql_tbl;

        if (empty($productid)) {

            db_query("INSERT INTO $sql_tbl[product_sales_stats] ( SELECT productid, 0 FROM $sql_tbl[products] WHERE productid NOT IN ( SELECT productid FROM $sql_tbl[product_sales_stats] ) )");
            if ($mode == self::RESTORE_VALUE) {
                db_query("UPDATE $sql_tbl[product_sales_stats] pss INNER JOIN $sql_tbl[order_details_stats] ods ON pss.productid=ods.productid SET pss.sales_stats=sum_amount WHERE pss.sales_stats=0");
            }
            db_query("DELETE FROM $sql_tbl[product_sales_stats] WHERE productid NOT IN (SELECT productid FROM $sql_tbl[products])");
        } else {
            $where_cond = " AND productid='$productid' ";

            db_query("INSERT INTO $sql_tbl[product_sales_stats] ( SELECT productid, 0 FROM $sql_tbl[products] WHERE productid NOT IN ( SELECT productid FROM $sql_tbl[product_sales_stats] WHERE 1 $where_cond) $where_cond )");
            if ($mode == self::RESTORE_VALUE) {
                db_query("UPDATE $sql_tbl[product_sales_stats] pss INNER JOIN $sql_tbl[order_details_stats] ods ON pss.productid=ods.productid SET pss.sales_stats=sum_amount WHERE pss.productid='$productid' AND pss.sales_stats=0");
            }

        }

        return TRUE;
    } // }}}

    public static function repairIntegrityThresholds($orig_cats, $cats2repair, $membershipid) { // {{{
        global $sql_tbl, $config;

        $cats2repair = array_diff($orig_cats, $cats2repair);

        if (empty($cats2repair)) {
            return FALSE;
        }

        db_query("DELETE FROM $sql_tbl[category_threshold_bestsellers] WHERE membershipid NOT IN (SELECT membershipid FROM $sql_tbl[memberships]) AND membershipid>0");

        $tmp_select = implode(",'$membershipid',0 UNION ALL SELECT ", $cats2repair);
        db_query("INSERT INTO $sql_tbl[category_threshold_bestsellers] SELECT " . $tmp_select . ",'$membershipid',0");
        if (empty($config['home_threshold_sales_stats'])) {
            db_query("REPLACE INTO $sql_tbl[config] (name, value, variants, defvalue) VALUES ('home_threshold_sales_stats',1,'','')");
        }
        return array_fill_keys($cats2repair, '0');
    } // }}}

    public static function adjustThresholds($bestsellers, $categories_thresholds, $membershipid) { // {{{
        global $sql_tbl, $config;

        $min_count = $config['Bestsellers']['number_of_bestsellers'] * self::DIRTY_LIMIT_MULT;
        $cats2update = array();
        $current_count = count($bestsellers);
        $current_threshold = max($categories_thresholds);
        if ($current_count < $min_count) {
            // Decrease thresholds
            $new_threshold = max(1, intval($current_threshold / self::THRESHOLD_DECREASE_DELIMETER));
            foreach($categories_thresholds as $categoryid=>$category_threshold) {
                if ($category_threshold > $new_threshold) {
                    $cats2update[$categoryid] = $new_threshold;
                }
            }
        } elseif($current_count >= $min_count) {
            // Increase thresholds
            $new_threshold = max(1, intval($bestsellers[$current_count-1]['sales_stats']));
            if ($current_threshold < $new_threshold) {
                $cat_indexes2update = array_keys($categories_thresholds, $current_threshold);
                if (!empty($cat_indexes2update)) {
                    foreach($cat_indexes2update as $categoryid) {
                        $cats2update[$categoryid] = max(1, $new_threshold--);
                    }
                }

            }
        }

        if (!empty($cats2update)) {
            $query = "REPLACE INTO $sql_tbl[category_threshold_bestsellers] VALUES ";
            foreach($cats2update as $categoryid=>$new_threshold) {
                $query .= "('$categoryid', '$membershipid', '$new_threshold'),";

                if (
                    empty($categoryid)
                    && empty($membershipid)
                ) {
                    db_query("UPDATE $sql_tbl[config] SET value='$new_threshold' WHERE name='home_threshold_sales_stats'");
                }
            }
            $query = rtrim($query, ',');

            db_query($query);
        }


        return TRUE;
    } // }}}

    public static function increase($products) { // {{{
        global $active_modules, $sql_tbl, $config;

        if (!is_array($products)) {
            return FALSE;
        }

        foreach ($products as $product) {
            $productid = $product['productid'];
            $amt = $product['amount'];
            if (empty($amt)) {
                continue;
            }

            db_query("UPDATE $sql_tbl[product_sales_stats] SET sales_stats = (sales_stats + '$amt') WHERE productid = '$productid'");

            if (db_affected_rows() < 1) {
                self::repairIntegrity($productid);
                db_query("UPDATE $sql_tbl[product_sales_stats] SET sales_stats = (sales_stats + '$amt') WHERE productid = '$productid'");
            }
        }

        return TRUE;
    } //}}}
}

class XCUpsellingProducts {
    private $upsell_type;
    private $period_for_also_bought_in_month;
    private $final_limit;

    // Limit for aux queries to minimize problems with memberships and variants
    private $dirty_limit;
    const DAY_PER_YEAR = '365';

    public function __construct($upsell_type, $period_for_also_bought_in_month, $final_limit) { // {{{
        $this->upsell_type = $upsell_type;
        $this->period_for_also_bought_in_month = $period_for_also_bought_in_month;
        $this->final_limit = $final_limit;
        $this->dirty_limit = $this->final_limit * XCSearchProducts::POSSIBLE_PRODUCT_DUPLICATES;
    } // }}}

    /**
     * Get products related to $productid by $upsell_type criteria
     */
    public function getUpsellingProducts($productid) { // {{{
        global $active_modules, $sql_tbl, $config, $user_account;

        // Get Upselling Products without products availability complex checking
        $dirty_pids = $this->getDirtyUpsellingProducts($productid);

        if (empty($dirty_pids)) {
            return array();
        }

        $query = array();
        $query['query'] = " AND $sql_tbl[products].productid IN ('" . implode("','", $dirty_pids) . "')";

        $query['skip_tables'] = XCSearchProducts::getSkipTablesByTemplate(XCSearchProducts::SKIP_ALL_POSSIBLE);

        // Check products availability, do not get products data
        $final_pids = func_search_products(
            $query,
            (isset($user_account) && isset($user_account['membershipid']))
                ? max(intval($user_account['membershipid']), 0)
                : 0,
            'skip_orderby',
            $this->dirty_limit,
            TRUE
        );

        if (empty($final_pids)) {
            return array();
        }

        $dirty_count = count($dirty_pids);
        if (count($final_pids) == $dirty_count) {
            if ($dirty_count > $this->final_limit) {
                $dirty_pids = array_slice($dirty_pids, 0, $this->final_limit);
            }
            return $dirty_pids;
        }

        array_walk($final_pids, create_function('&$val, $key', '$val = $val["productid"];'));

        $result = array();

        // Restore ordering from original $dirty_pids
        $final_count = 0;

        foreach($dirty_pids as $dirty_pid) {
            $prod_is_available = array_search($dirty_pid, $final_pids);

            if ($prod_is_available !== FALSE) {
                $result[] = $dirty_pid;
                $final_count++;
            }

            if ($final_count >= $this->final_limit) {
                break;
            }
        }

        return $result;
    } // }}}

    private function getDirtyUpsellingProducts($productid) { // {{{
        global $active_modules, $sql_tbl, $config;

        $upsell_type = $this->upsell_type;
        $period_for_also_bought_in_month = $this->period_for_also_bought_in_month;
        $dirty_limit = $this->dirty_limit;

        if (empty($dirty_limit)) {
            return array();
        }
        assert('self::checkCacheKey(get_object_vars($this)) /* '.__METHOD__.': Current cache_key is $upsell_type.$period_for_also_bought_in_month.L.($this->final_limit). Correct on error*/');

        $period_for_also_bought_in_month = max($period_for_also_bought_in_month, 1);
        $cache_key = $productid.$upsell_type.$period_for_also_bought_in_month.'L' . ($this->final_limit);

        $data = func_get_cache_func($cache_key, 'getDirtyUpsellingProducts');
        $data = $data ? $data['data'] : $data;
        if (is_array($data)) {
            return $data;
        }

        $avail_cond = '';
        if (
            $config['General']['unlimited_products'] != 'Y'
            && $config['General']['show_outofstock_products'] != 'Y'
        ) {
            if (
                empty($active_modules['Product_Options'])
                || !XCVariantsSQL::isVariantsExist()
            ) {
                $avail_cond = ' AND p.avail>0';
            } else {
                // Do not JOIN xcart_variants table via
                // $avail_join = XCVariantsSQL::getJoinQueryAllRows(); for performance purpose,dirty limit is used
            }
        }

        $lastcount = 0;
        $pids = array();

        if (
            in_array($upsell_type, array('Show_Related', 'Show_Related_Shuffled', 'Show_Related_and_Bought'))
            && !empty($active_modules['Upselling_Products'])
        ) {

            if ($upsell_type == 'Show_Related') {
                $rnd_join = '';
                $orderby = 'pl.orderby';
            } else {
                func_refresh_product_rnd_keys();
                $rnd_join = "JOIN $sql_tbl[product_rnd_keys] rnd ON rnd.productid = p.productid";
                $orderby = 'rnd.rnd_key';
            }

            $pids = func_query_column("
                SELECT p.productid
                FROM $sql_tbl[products] p
                JOIN $sql_tbl[product_links] pl ON pl.productid2 = p.productid
                JOIN $sql_tbl[products_categories] pc ON p.productid = pc.productid AND pc.main = 'Y' AND pc.avail = 'Y'
                $rnd_join
                WHERE p.forsale = 'Y'
                    AND pl.productid1 = '$productid'
                    AND p.productid != '$productid'
                    $avail_cond
                ORDER BY $orderby
                LIMIT $dirty_limit
            ");

            $lastcount = count($pids);

        }

        $period_for_also_bought_in_days = intval(self::DAY_PER_YEAR/12 * $period_for_also_bought_in_month);
        if (
            in_array($upsell_type, array('Show', 'Show_Both', 'Show_Related_and_Bought'))
            && $lastcount < $dirty_limit
        ) {

            $this->deleteExpired();

            // Search products bought in the same Order
            $pidsBoughtOrders = func_query_column("
                SELECT od2.productid
                FROM $sql_tbl[order_details_stats] od
                JOIN $sql_tbl[order_details_stats] od2 ON 
                    od.productid = '$productid'
                    AND od.orderid = od2.orderid
                    AND od2.date > '" . (XC_TIME - $period_for_also_bought_in_days * SECONDS_PER_DAY) . "'
                JOIN $sql_tbl[products] p ON p.forsale = 'Y' AND od2.productid = p.productid $avail_cond
                JOIN $sql_tbl[products_categories] pc ON p.productid = pc.productid AND pc.main = 'Y' AND pc.avail = 'Y'
                GROUP BY od2.productid
                ORDER BY od2.sum_amount DESC
                LIMIT " . ($dirty_limit + $lastcount) . "
            ", 0);

            $count_notInRelatedOrders = count($pidsBoughtOrders);

            if (($dirty_limit - $lastcount - $count_notInRelatedOrders) > 0) {
                // Search products bought by the same user
                $pidsBoughtUsers = func_query_column("
                    SELECT od2.productid
                    FROM $sql_tbl[order_details_stats] od
                    JOIN $sql_tbl[order_details_stats] od2 ON 
                        od.productid = '$productid'
                        AND od.userid = od2.userid
                        AND od2.userid > 0
                        AND od2.date > '" . (XC_TIME - $period_for_also_bought_in_days * SECONDS_PER_DAY) . "'
                    JOIN $sql_tbl[products] p ON p.forsale = 'Y' AND od2.productid = p.productid $avail_cond
                    JOIN $sql_tbl[products_categories] pc ON p.productid = pc.productid AND pc.main = 'Y' AND pc.avail = 'Y'
                    GROUP BY od2.productid
                    ORDER BY od2.sum_amount DESC
                    LIMIT " . ($dirty_limit + $lastcount + $count_notInRelatedOrders) . "
                ", 0);
            } else {
                $pidsBoughtUsers = array();
            }

            $pids = array_merge($pids, $pidsBoughtOrders, $pidsBoughtUsers);
            $pids = array_unique($pids);

            $lastcount = count($pids);

        }
        
        if (
            in_array($upsell_type, array('Show_Random', 'Show_Both'))
            && $lastcount < $dirty_limit
        ) {

            func_refresh_product_rnd_keys();
            if (!empty($pids)) {
                $max_rnd_number = $pids[0];
            } else {
                $max_rnd_number = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products]");
            }
            $rnd = mt_rand(1, $max_rnd_number);
            $_sort_order = $rnd % 2 ? 'DESC' : 'ASC';
            $_direction = (mt_rand()&1) ? '>=' : '<=';

            $pidsRandom = func_query_column("
                SELECT p.productid
                FROM $sql_tbl[products] p
                JOIN $sql_tbl[products_categories] pc ON p.productid = pc.productid AND pc.main = 'Y' AND pc.avail = 'Y'
                JOIN $sql_tbl[product_rnd_keys] rnd ON rnd.productid = p.productid
                WHERE p.forsale = 'Y'
                    AND rnd.rnd_key $_direction $rnd
                    $avail_cond
                ORDER BY rnd.rnd_key $_sort_order 
                LIMIT " . ($dirty_limit - $lastcount) . "
            ");

            $pids = array_merge($pids, $pidsRandom);
        }

        $pids = array_unique($pids);

        $current_product_ind = array_search($productid, $pids);
        if ($current_product_ind !== FALSE) {
            unset($pids[$current_product_ind]);
        }

        $pids = array_values($pids);

        if (count($pids) > $dirty_limit) {
            $pids = array_slice($pids, 0, $dirty_limit);
        }

        func_save_cache_func($pids, $cache_key, 'getDirtyUpsellingProducts');
        return $pids;
    } // }}}

    public static function decreaseAlsoBoughtAmounts($orderid, $userid, $date, $products) { // {{{
        return XCUpsellingProducts::updateAlsoBoughtAmounts($orderid, $userid, $date, $products, '-');
    } // }}}

    public static function increaseAlsoBoughtAmounts($orderid, $userid, $date, $products) { // {{{
        return XCUpsellingProducts::updateAlsoBoughtAmounts($orderid, $userid, $date, $products, '+');
    } // }}}

    private static function updateAlsoBoughtAmounts($orderid, $userid, $date, $products, $symbol='+') { // {{{
        global $active_modules, $sql_tbl, $config;

        if (!is_array($products)) {
            return FALSE;
        }

        foreach ($products as $product) {

            if ($product['product_type'] == 'C' && !empty($active_modules['Product_Configurator']))
                continue;

            $exists = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[order_details_stats] WHERE productid='$product[productid]' AND orderid='$orderid' AND userid='$userid'") > 0;

            if (
                $symbol == '+'
                && !$exists
            ) {
                db_query("INSERT INTO $sql_tbl[order_details_stats] (orderid,productid,date,userid) VALUES ('$orderid','$product[productid]','$date','$userid')");
                $exists = TRUE;

            }

            if ($exists) {
                db_query("UPDATE $sql_tbl[order_details_stats] SET sum_amount=sum_amount$symbol'$product[amount]' WHERE productid='$product[productid]'");
            }
        }

        return TRUE;
    } //}}}

    private static function checkCacheKey($obj_properties) { // {{{
        return
            count($obj_properties) == 4
            && isset($obj_properties['upsell_type'])
            && isset($obj_properties['period_for_also_bought_in_month'])
            && isset($obj_properties['final_limit'])
            && isset($obj_properties['dirty_limit'])
        ;
    } //}}}

    /**
     * Delete expired order_details_stats
     *
     * @return void
     * @see    ____func_see____
     */
    private function deleteExpired() { // {{{
        global $config, $sql_tbl, $active_modules;

        $wait_time_2_delete_expired = XC_TIME - (SECONDS_PER_DAY * 20);

        settype($config['last_also_bought_stats_refresh'], 'int');
        if ($config['last_also_bought_stats_refresh'] > $wait_time_2_delete_expired) {
            return FALSE;
        }

        $a2c_period_for_also_bought = (
                !empty($active_modules['Add_to_cart_popup'])
                && in_array($config['Add_to_cart_popup']['a2c_upselling_type'], array('Show', 'Show_Both', 'Show_Related_and_Bought'))
            )
                ? $config['Add_to_cart_popup']['a2c_period_for_also_bought']
                : 0;

        $rp_period_for_also_bought = (
                !empty($active_modules['Recommended_Products'])
                && in_array($config['Recommended_Products']['rp_upselling_type'], array('Show', 'Show_Both', 'Show_Related_and_Bought'))
            )
                ? $config['Recommended_Products']['rp_period_for_also_bought']
                : 0;

        $base_period = max($a2c_period_for_also_bought, $rp_period_for_also_bought);

        if (empty($base_period)) {
            return FALSE;
        }

        $base_period_in_days = intval(self::DAY_PER_YEAR/12 * $base_period);
        db_query("DELETE FROM $sql_tbl[order_details_stats] WHERE `date` < " . (XC_TIME - $base_period_in_days * SECONDS_PER_DAY));

        func_array2insert(
            'config',
            array(
                'name' => 'last_also_bought_stats_refresh',
                'value' => XC_TIME
            ),
            TRUE
        );

        return TRUE;
    } //}}}


}

function func_delete_product($productid, $update_categories = true, $delete_all = false)
{
    global $sql_tbl, $xcart_dir, $smarty, $active_modules;

    x_load('backoffice', 'category', 'image', 'export');

    if ($delete_all === true) {

        db_query("DELETE FROM $sql_tbl[delivery]");
        db_query("DELETE FROM $sql_tbl[discount_coupons]");
        db_query("DELETE FROM $sql_tbl[download_keys]");
        db_query("DELETE FROM $sql_tbl[extra_field_values]");
        db_query("DELETE FROM $sql_tbl[featured_products]");
        db_query("DELETE FROM $sql_tbl[ge_products]");
        db_query("DELETE FROM $sql_tbl[pricing]");
        db_query("DELETE FROM $sql_tbl[product_bookmarks]");
        db_query("DELETE FROM $sql_tbl[product_links]");
        db_query("DELETE FROM $sql_tbl[product_memberships]");
        db_query("DELETE FROM $sql_tbl[product_reviews]");
        db_query("DELETE FROM $sql_tbl[product_rnd_keys]");
        db_query("DELETE FROM $sql_tbl[product_taxes]");
        db_query("DELETE FROM $sql_tbl[product_votes]");
        db_query("DELETE FROM $sql_tbl[products]");
        db_query("DELETE FROM $sql_tbl[products_categories]");
        db_query("DELETE FROM $sql_tbl[wishlist]");
        XCProductSalesStats::deleteAll();

        func_delete_entity_from_lng_tables('products_lng_');

        func_delete_images('T');
        func_delete_images('P');
        func_delete_images('D');

        // Feature comparison module
        if (func_is_defined_module_sql_tbl('Feature_Comparison', 'product_features')) {
            db_query("DELETE FROM $sql_tbl[product_features]");
            db_query("DELETE FROM $sql_tbl[product_foptions]");
        }

        if (func_is_defined_module_sql_tbl('Refine_Filters', 'rf_product_features')) {
            func_rf_trigger_event('delete_all_products');
        }

        // Product options module
        if (func_is_defined_module_sql_tbl('Product_Options', 'class_options')) {

            db_query("DELETE FROM $sql_tbl[classes]");
            db_query("DELETE FROM $sql_tbl[class_options]");
            db_query("DELETE FROM $sql_tbl[class_lng]");
            db_query("DELETE FROM $sql_tbl[product_options_lng]");
            db_query("DELETE FROM $sql_tbl[product_options_ex]");
            db_query("DELETE FROM $sql_tbl[product_options_js]");
            db_query("DELETE FROM $sql_tbl[variant_items]");
            db_query("DELETE FROM $sql_tbl[variant_backups]");
            db_query("DELETE FROM $sql_tbl[variants]");

            func_delete_images('W');

        }

        if (!empty($active_modules['POS_System'])) {
            XCPosChange::deleteVariantUpc();
            XCPosChange::deleteProductUpc();
        }

        // Product configurator module
        if (func_is_defined_module_sql_tbl('Product_Configurator', 'pconf_products_classes')) {

            db_query("DELETE FROM $sql_tbl[pconf_products_classes]");
            db_query("DELETE FROM $sql_tbl[pconf_class_specifications]");
            db_query("DELETE FROM $sql_tbl[pconf_class_requirements]");
            db_query("DELETE FROM $sql_tbl[pconf_wizards]");
            db_query("DELETE FROM $sql_tbl[pconf_slots]");
            db_query("DELETE FROM $sql_tbl[pconf_slot_rules]");
            db_query("DELETE FROM $sql_tbl[pconf_slot_markups]");
        }

        // Magnifier module
        if (func_is_defined_module_sql_tbl('Magnifier', 'images_Z')) {

            db_query("DELETE FROM $sql_tbl[images_Z]");

            $dir_z = func_image_dir('Z');

            if (is_dir($dir_z) && file_exists($dir_z))
                func_rm_dir($dir_z);
        }

        // Special Offers
        if (func_is_defined_module_sql_tbl('Special_Offers', 'offer_bonus_params')) {

            db_query("DELETE FROM $sql_tbl[offer_bonus_params] WHERE param_type = 'P'");
            db_query("DELETE FROM $sql_tbl[offer_condition_params] WHERE param_type = 'P'");
            db_query("DELETE FROM $sql_tbl[offer_product_params]");

        }

        // Advanced Customer Reviews
        if (func_is_defined_module_sql_tbl('Advanced_Customer_Reviews', 'product_review_votes')) {

            db_query("DELETE FROM $sql_tbl[product_review_votes]");

        }

        // Product Notifications module
        if (func_is_defined_module_sql_tbl('Product_Notifications', 'product_notifications')) {
            db_query("DELETE FROM $sql_tbl[product_notifications]");
        }

        if ($update_categories) {

            $res = db_query("SELECT categoryid FROM $sql_tbl[categories]");

            func_recalc_product_count($res);
        }

        func_data_cache_get('fc_count', array('Y'), true);
        func_data_cache_get('fc_count', array('N'), true);

        db_query("DELETE FROM $sql_tbl[quick_flags]");
        db_query("DELETE FROM $sql_tbl[quick_prices]");
        db_query("DELETE FROM $sql_tbl[clean_urls] WHERE resource_type = 'P'");
        db_query("DELETE FROM $sql_tbl[clean_urls_history] WHERE resource_type = 'P'");

        func_export_range_erase('PRODUCTS');

        return true;
    }

    $product_categories = XCProductsCategoriesSQL::getProductCategories($productid);

    db_query("DELETE FROM $sql_tbl[delivery] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[extra_field_values] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[featured_products] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[ge_products] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[pricing] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[product_links] WHERE productid1='$productid' OR productid2='$productid'");
    db_query("DELETE FROM $sql_tbl[product_memberships] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[product_rnd_keys] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[products] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[products_categories] WHERE productid='$productid'");

    XCProductSalesStats::deleteRow($productid);

    func_delete_image($productid, 'T');
    func_delete_image($productid, 'P');
    func_delete_image($productid, 'D');

    // Feature comparison module
    if (func_is_defined_module_sql_tbl('Feature_Comparison', 'product_foptions')) {

        db_query("DELETE FROM $sql_tbl[product_features] WHERE productid='$productid'");
        db_query("DELETE FROM $sql_tbl[product_foptions] WHERE productid='$productid'");
    }

    if (func_is_defined_module_sql_tbl('Refine_Filters', 'rf_product_foptions')) {
        func_rf_trigger_event('delete_product', array('id' => $productid));
    }

    // Product options module
    if (func_is_defined_module_sql_tbl('Product_Options', 'class_options')) {

        $classes = func_query_column("SELECT classid FROM $sql_tbl[classes] WHERE productid='$productid'");
        db_query("DELETE FROM $sql_tbl[classes] WHERE productid='$productid'");
        if (!empty($classes)) {
            $options = func_query_column("SELECT optionid FROM $sql_tbl[class_options] WHERE classid IN ('".implode("','", $classes)."')");
            db_query("DELETE FROM $sql_tbl[class_lng] WHERE classid IN ('".implode("','", $classes)."')");
            if (!empty($options)) {
                db_query("DELETE FROM $sql_tbl[class_options] WHERE classid IN ('".implode("','", $classes)."')");
                db_query("DELETE FROM $sql_tbl[product_options_lng] WHERE optionid IN ('".implode("','", $options)."')");
                db_query("DELETE FROM $sql_tbl[product_options_ex] WHERE optionid IN ('".implode("','", $options)."')");
                db_query("DELETE FROM $sql_tbl[variant_items] WHERE optionid IN ('".implode("','", $options)."')");
                db_query("DELETE FROM $sql_tbl[variant_backups] WHERE optionid IN ('".implode("','", $options)."')");
            }
        }

        x_load_module('Product_Options');
        db_query("DELETE FROM $sql_tbl[product_options_js] WHERE productid='$productid'");
        $vids = db_query("SELECT variantid FROM $sql_tbl[variants] WHERE productid='$productid' AND " . XCVariantsSQL::isVariantRow($productid));

        if (!empty($active_modules['POS_System'])) {
            XCPosChange::deleteVariantUpc($productid);
            XCPosChange::deleteProductUpc($productid);
        }

        if ($vids) {
            while ($row = db_fetch_array($vids)) {
                func_delete_image($row['variantid'], "W");
            }
            db_free_result($vids);
        }
        db_query("DELETE FROM $sql_tbl[variants] WHERE productid='$productid'");
    }

    // Magnifier module
    if (func_is_defined_module_sql_tbl('Magnifier', 'images_Z')) {

        db_query("DELETE FROM $sql_tbl[images_Z] WHERE id = '$productid'");
        $dir_z = func_image_dir('Z').XC_DS.$productid;
        if (is_dir($dir_z) && file_exists($dir_z))
            func_rm_dir($dir_z);
    }

    // Special Offers
    if (func_is_defined_module_sql_tbl('Special_Offers', 'offer_bonus_params')) {

        db_query("DELETE FROM $sql_tbl[offer_bonus_params] WHERE param_type = 'P' AND param_id = '$productid'");
        db_query("DELETE FROM $sql_tbl[offer_condition_params] WHERE param_type = 'P' AND param_id = '$productid'");
        db_query("DELETE FROM $sql_tbl[offer_product_params] WHERE productid = '$productid'");
    }

    db_query("DELETE FROM $sql_tbl[product_taxes] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[product_votes] WHERE productid='$productid'");

    // Advanced Customer Reviews
    if (func_is_defined_module_sql_tbl('Advanced_Customer_Reviews', 'product_review_votes')) {

        $review_ids = func_query_column("SELECT review_id FROM $sql_tbl[product_reviews] WHERE productid='$productid'");
        if (is_array($review_ids)) {
            db_query("DELETE FROM $sql_tbl[product_review_votes] WHERE review_id IN ('" . implode("', '", $review_ids) . "')");
        }

    }

    db_query("DELETE FROM $sql_tbl[product_reviews] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[download_keys] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[discount_coupons] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[wishlist] WHERE productid='$productid'");
    db_query("DELETE FROM $sql_tbl[product_bookmarks] WHERE productid='$productid'");

    func_delete_entity_from_lng_tables('products_lng_', $productid, 'productid');
    // Product configurator module
    if (func_is_defined_module_sql_tbl('Product_Configurator', 'pconf_products_classes')) {

        $classes = func_query_column("SELECT classid FROM $sql_tbl[pconf_products_classes] WHERE productid='$productid'");
        if (!empty($classes)) {

            // Delete all classification info related with this product

            db_query("DELETE FROM $sql_tbl[pconf_class_specifications] WHERE classid IN ('".implode("','", $classes)."')");
            db_query("DELETE FROM $sql_tbl[pconf_class_requirements] WHERE classid IN ('".implode("','", $classes)."')");
        }

        db_query("DELETE FROM $sql_tbl[pconf_products_classes] WHERE productid='$productid'");

        // Delete configurable product

        $steps = func_query_column("SELECT stepid FROM $sql_tbl[pconf_wizards] WHERE productid='$productid'");
        if (!empty($steps)) {

            // Delete the data related with wizards' steps

            $slots = func_query_column("SELECT slotid FROM $sql_tbl[pconf_slots] WHERE stepid IN ('".implode("','", $steps)."')");
            if (!empty($slots)) {

                // Delete data related with slots

                db_query("DELETE FROM $sql_tbl[pconf_slots] WHERE stepid IN ('".implode("','", $steps)."')");
                db_query("DELETE FROM $sql_tbl[pconf_slot_rules] WHERE slotid IN ('".implode("','", $slots)."')");
                db_query("DELETE FROM $sql_tbl[pconf_slot_markups] WHERE slotid IN ('".implode("','", $slots)."')");
            }
        }

        db_query("DELETE FROM $sql_tbl[pconf_wizards] WHERE productid='$productid'");
    }

    // Product Notifications module
    if (func_is_defined_module_sql_tbl('Product_Notifications', 'product_notifications')) {
        db_query("DELETE FROM $sql_tbl[product_notifications] WHERE productid='$productid'");
    }

    // Update product count for Categories

    if ($update_categories && !empty($product_categories)) {
        $cats = array();
        foreach ($product_categories as $c) {
            $cats = array_merge($cats, func_get_category_path($c));
        }
        $cats = array_unique($cats);
        func_recalc_product_count($cats);
    }

    func_data_cache_get('fc_count', array('Y'), true);
    func_data_cache_get('fc_count', array('N'), true);

    db_query("DELETE FROM $sql_tbl[quick_flags] WHERE productid = '$productid'");
    db_query("DELETE FROM $sql_tbl[quick_prices] WHERE productid = '$productid'");

    // Delete Clean URLs data.
    db_query("DELETE FROM $sql_tbl[clean_urls] WHERE resource_type = 'P' AND resource_id = '$productid'");
    db_query("DELETE FROM $sql_tbl[clean_urls_history] WHERE resource_type = 'P' AND resource_id = '$productid'");

    func_export_range_erase('PRODUCTS', $productid);

    return true;
}

function func_filter_product_mode($mode) {
    return preg_replace('/[^a-z0-9_]/i', '', $mode);
}

function func_search_products_query_count($search_query_count, $distinct_is_used, $cache = SKIP_CACHE) {
    if (USE_SQL_DATA_CACHE) {
        
        if ($distinct_is_used) {
            $total_items = func_query_first_cell($search_query_count, true);
        } else {
            $_res_tmp = func_query($search_query_count, true);
            
            $total_items = is_array($_res_tmp) ? count($_res_tmp) : 0;
        }
    
    } else {
        $md5_args = md5($search_query_count);

        if (
            $cache === USE_CACHE
            && $data = func_get_cache_func($md5_args, 'search_products_query_count')
        ) {
            return $data['data'];
        }
        
        if ($distinct_is_used) {
            $total_items = func_query_first_cell($search_query_count, false);
        } else {
            if ($_res = db_query($search_query_count)) {
                
                $total_items = db_num_rows($_res);
                
                db_free_result($_res);
            
            }
        }

        if ($cache === USE_CACHE) {
            func_save_cache_func($total_items, $md5_args, 'search_products_query_count');
        }
    
    }

    return $total_items;
}

/**
 * Search for products in products database
 */
function func_search_products($query, $membershipid, $orderby = '', $limit = '', $id_only = false, $use_tiny_thumbnails = false)
{
    global $current_area, $user_account, $active_modules, $xcart_dir, $current_location, $single_mode;
    global $store_language, $sql_tbl, $shop_language;
    global $config;
    global $cart, $logged_userid;
    global $active_modules;


    x_load('files', 'taxes', 'category');
    x_load_module('Product_Options');


    // Generate ORDER BY rule

    if ($orderby != 'skip_orderby') {
        assert('!in_array("products_categories", $query["skip_tables"]) /* '.__FUNCTION__.': skip_tables["products_categories"] can be used only with $orderby == "skip_orderby" to avoid problems with default orderby order */');
    }

    if (empty($orderby)) {

        $orderby = func_get_product_sql_orderby();

    } elseif ($orderby == 'skip_orderby') {
        $orderby = '';
    }
    $query_2check = $query;
    unset($query_2check['skip_tables']);
    if (!empty($query_2check['from_tbls'])) {
        foreach ($query_2check['from_tbls'] as $_table => $_table_suffix) {
            $query_2check['from_tbls'][$_table] = $sql_tbl[$_table] . $_table_suffix;
        }
    }
    $query_2check = serialize($query) . $orderby;

    // Initialize service arrays

    $fields      = array();
    $from_tbls   = array();
    $inner_joins = array();
    $left_joins  = array();
    $where       = array();
    $groupbys    = array();
    $orderbys    = array();
    $skip_tables = array();
    $use_group_by = TRUE;

    if (is_array($query)) {
        foreach (
            array(
                'fields',
                'from_tbls',
                'inner_joins',
                'left_joins',
                'where',
                'groupbys',
                'orderbys',
                'skip_tables',
                'use_group_by',
            ) as $fn
        ) {
            if (isset($query[$fn]))
                $$fn = $query[$fn];
        }

        $query = isset($query['query']) ? $query['query'] : '';
    }

    if (!empty($skip_tables)) {
        $skip_tables = array_combine($skip_tables, $skip_tables);
    }

    if (
        stripos($query_2check, 'price') !== FALSE
        || stripos($query_2check, $sql_tbl['pricing']) !== FALSE
        || (
            empty($skip_tables['variants'])
            && $current_area == 'C'
        )
    ) {
        $skip_tables['pricing'] = FALSE;
        assert('stripos($query_2check, "list_price") === FALSE /* '.__FUNCTION__.': list_price is used in query.Change stripos to preg_match */');
    }

    // Generate membershipid condition

    $membershipid_condition = '';

    if (
        $current_area == 'C'
        && empty($skip_tables['category_memberships'])
        && empty($skip_tables['product_memberships'])
        && empty($skip_tables['products_categories'])
    ) {

        $where[] = "(" . $sql_tbl['category_memberships'] . ".membershipid = '" . $membershipid . "' OR " . $sql_tbl['category_memberships'] . ".membershipid IS NULL)";
        $where[] = "(" . $sql_tbl['product_memberships'] . ".membershipid = '" . $membershipid . "' OR " . $sql_tbl['product_memberships'] . ".membershipid IS NULL)";
        $skip_tables['products_categories'] = $skip_tables['category_memberships'] = $skip_tables['product_memberships'] = FALSE;

    } else {
        if (
            !isset($skip_tables['category_memberships'])
            && stripos($query_2check, $sql_tbl['category_memberships']) === FALSE
        ) {
            $skip_tables['category_memberships'] = 'category_memberships';
        }

        if (
            !isset($skip_tables['product_memberships'])
            && stripos($query_2check, $sql_tbl['product_memberships']) === FALSE
        ) {
            $skip_tables['product_memberships'] = 'product_memberships';
        }
    }

    // Generate products availability condition

    if (
        in_array($current_area, array('C','B'))
        && $config['General']['show_outofstock_products'] != 'Y'
    ) {

        if (!empty($active_modules['Product_Options'])) {

            $where[] = "(".XCVariantsSQL::getVariantField('avail')." > 0 OR $sql_tbl[products].product_type NOT IN ('','N'))";
            $skip_tables['variants'] = FALSE;

            if ($current_area == 'C') {
                // Do not disable pricing to allow condition 'quick_prices.variantid = variants.variantid AND quick_prices.priceid = pricing.priceid'
                $skip_tables['pricing'] = FALSE; 
            }

        } else {

            $where[] = "($sql_tbl[products].avail > '0' OR $sql_tbl[products].product_type NOT IN ('','N'))";

        }

    }

    if (stripos($query_2check, 'sales_stats') !== FALSE) {
        $inner_joins['product_sales_stats'] = array(
            'on' => "$sql_tbl[product_sales_stats].productid = $sql_tbl[products].productid"
        );
        $skip_tables['product_sales_stats'] = FALSE;
    }

    if (empty($skip_tables['pricing'])) {
        $inner_joins['pricing'] = array(
            'on' => "$sql_tbl[pricing].productid = $sql_tbl[products].productid AND $sql_tbl[pricing].quantity = '1'"
        );
    }

    if (
        (
            empty($id_only)
            && empty($skip_tables['products_lng_current'])
        )
        || stripos($query_2check, $sql_tbl['products_lng_current']) !== FALSE
    ) {
        $inner_joins['products_lng_current'] = array(
            'on' => "$sql_tbl[products_lng_current].productid = $sql_tbl[products].productid"
        );
        $fields[] = $sql_tbl['products_lng_current'] . ".product";
        $skip_tables['products_lng_current'] = FALSE;
    }

    if (
        empty($skip_tables['category_memberships'])
        || stripos($query_2check, $sql_tbl['products_categories']) !== FALSE 
        || stripos($query_2check, 'categoryid') !== FALSE 
        ||
            (
                in_array($current_area, array('C','B'))
                && empty($skip_tables['products_categories'])
            )
    ) {
        $inner_joins['products_categories'] = array(
            'on' => XCProductsCategoriesSQL::getInnerJoinWithProductsCondition($current_area)
        );

        $skip_tables['products_categories'] = FALSE;
    } else {
        $skip_tables['products_categories'] = 'products_categories';
    }


    if (
        $config['General']['check_main_category_only'] == 'Y'
        && $current_area == 'C'
        && empty($skip_tables['products_categories'])
    ) {
        $inner_joins['products_categories']['on'] .= " AND $sql_tbl[products_categories].main = 'Y'";
    }


    $fields[] = $sql_tbl['products'] . '.productid';
    $fields[] = $sql_tbl['products'] . '.provider';
    $fields[] = $sql_tbl['products'] . ".productcode";
    $fields[] = $sql_tbl['products'] . ".avail";
    $fields[] = $sql_tbl['products'] . ".min_amount";
    $fields[] = $sql_tbl['products'] . ".list_price";
    $fields[] = $sql_tbl['products'] . ".distribution";

    if (empty($skip_tables['pricing'])) {
        if ($current_area != 'C') {

            $fields[] = "MIN($sql_tbl[pricing].price) as price";
            $use_group_by = TRUE;

        } else {

            if ($membershipid == 0 || empty($active_modules['Wholesale_Trading'])) {

                $fields[] = "$sql_tbl[pricing].price";

                $membershipid_string = "= '0'";

            } else {

                $fields[] = "MIN($sql_tbl[pricing].price) as price";

                $membershipid_string = "IN ('$membershipid', '0')";
                $use_group_by = TRUE;

            }

            $inner_joins['quick_prices'] = array(
                'on' => "$sql_tbl[quick_prices].productid = $sql_tbl[products].productid AND $sql_tbl[quick_prices].membershipid $membershipid_string AND $sql_tbl[quick_prices].priceid = $sql_tbl[pricing].priceid"
            );

        }
    }

    if ($current_area == 'C' && !$single_mode) {

        $inner_joins['ACHECK'] = array(
            'tblname' => 'customer_enabled_activities',
            'on' => "$sql_tbl[products].provider=ACHECK.userid",
        );

    }

    if (
        empty($skip_tables['category_memberships'])
        && empty($skip_tables['products_categories'])
    ) {
        $left_joins['category_memberships'] = array(
            'on' => "$sql_tbl[category_memberships].categoryid = $sql_tbl[products_categories].categoryid"
        );
    }

    if (empty($skip_tables['product_memberships'])) {
        $left_joins['product_memberships'] = array(
            'on' => "$sql_tbl[product_memberships].productid = $sql_tbl[products].productid"
        );
    }

    if (empty($skip_tables['pricing'])) {
        if (
            empty($membershipid)
            || empty($active_modules['Wholesale_Trading'])
        ) {
            $where[] = "$sql_tbl[pricing].membershipid = '0'";
        } else {
            $where[] = "$sql_tbl[pricing].membershipid IN ('$membershipid', '0')";
        }
    }

    if ($current_area == 'C') {
        
        $where[] = "$sql_tbl[products].forsale='Y'";

        if (empty($active_modules['Product_Configurator'])) {
            $where[] = "$sql_tbl[products].product_type <> 'C'";
        }
    }

    if (
        $current_area == 'C'
        && !empty($active_modules['Product_Options'])
        && empty($skip_tables['variants'])
    ) {

        $where[] = XCVariantsSQL::isProductAndVariantsPrice();

    }

    $groupbys[] = "$sql_tbl[products].productid";

    if (!empty($orderby))
        $orderbys[] = $orderby;

    // Check if product have prodyct class (Feature comparison)

    if (
        !empty($active_modules['Feature_Comparison'])
        && $current_area == 'C'
        && empty($skip_tables['product_features'])
    ) {

        global $comparison_list_ids;

        $left_joins['product_features'] = array(
            'on' => "$sql_tbl[product_features].productid = $sql_tbl[products].productid"
        );

        $fields[] = "$sql_tbl[product_features].fclassid";

    }

    // Check if product have product options (Product options)

    if (!empty($active_modules['Product_Options'])) {

        if (empty($skip_tables['classes'])) {
            $left_joins['classes'] = array(
                'on' => "$sql_tbl[classes].productid = $sql_tbl[products].productid"
            );
        }

        if (empty($skip_tables['variants'])) {
            if ($current_area == 'C') {

                $_var_join = array(
                    'on' => XCVariantsSQL::getJoinQueryAllRowsCondition() . " AND $sql_tbl[quick_prices].variantid = $sql_tbl[variants].variantid",
                );
                if (XCVariantsSQL::isVariantsExist())
                    $inner_joins['variants'] = $_var_join;
                else
                    $left_joins['variants'] = $_var_join;

                $fields[] = "$sql_tbl[quick_prices].variantid";

                global $variant_properties;

                foreach ($variant_properties as $property) {
                    $fields[] = XCVariantsSQL::getVariantField($property) . ' AS ' . $property;
                }

            } else {

                $_var_join = array(
                    'on' => XCVariantsSQL::getJoinQueryAllRowsCondition(),
                );

                if (XCVariantsSQL::isVariantsExist())
                    $inner_joins['variants'] = $_var_join;
                else
                    $left_joins['variants'] = $_var_join;

            }
            $fields[] = XCVariantsSQL::isHaveVariant('is_variant');
        }

        if (empty($skip_tables['classes'])) {
            $fields[] = "IF($sql_tbl[classes].classid IS NULL,'','Y') as is_product_options";
        }

    }

    if (
        ($config['setup_images']['T']['location'] == 'FS' || $use_tiny_thumbnails)
        && empty($skip_tables['images_T'])
    ) {

        $left_joins['images_T'] = array(
            'on' => "$sql_tbl[images_T].id = $sql_tbl[products].productid"
        );

        $fields[] = "$sql_tbl[images_T].image_path";
        $fields[] = "$sql_tbl[images_T].imageid";

    }

    if (
        $current_area == 'C'
        && empty($skip_tables['product_taxes'])
    ) {

        $left_joins['product_taxes'] = array(
            'on' => "$sql_tbl[product_taxes].productid = $sql_tbl[products].productid"
        );

        $fields[] = "$sql_tbl[product_taxes].taxid";

    }

    if (!empty($active_modules['On_Sale'])) {
        func_on_sale_search_products_set_fields($fields);
    }    

    // Generate search query

    foreach ($inner_joins as $j) {
        if (!empty($j['fields']) && is_array($j['fields']))
            $fields = func_array_merge($fields, $j['fields']);
    }

    foreach ($left_joins as $j) {
        if (!empty($j['fields']) && is_array($j['fields']))
            $fields = func_array_merge($fields, $j['fields']);
    }

    if ($id_only != true) {
        $search_query = "SELECT " . implode(", ", $fields) . " FROM ";
    } else {
        if (
            XCSearchProducts::isQueryWithGroupBy($limit, $use_group_by)
            && count($groupbys) == 1
            && stripos($groupbys[0], 'productid') !== FALSE
        ) {
            $search_query = "SELECT DISTINCT $sql_tbl[products].productid FROM ";
            $use_group_by = TRUE;
            $groupbys = array();
        } else {
            $search_query = "SELECT $sql_tbl[products].productid FROM ";
        }
    }

    if (!empty($from_tbls)) {
        $_from_tbls = array();

        foreach ($from_tbls as $_table => $_table_suffix) {
            if (XCSearchProducts::isQueryWithGroupBy($limit, $use_group_by)) {
                // Do not force indexes when GROUP BY is used
                $_from_tbls[$_table] = $sql_tbl[$_table];
            } else {
                $_from_tbls[$_table] = $sql_tbl[$_table] . $_table_suffix;
            }
        }

        if (!isset($from_tbls['products'])) {
            $search_query .= implode(" INNER JOIN ", $_from_tbls) . " INNER JOIN ";
        } else {
            $search_query .= implode(" INNER JOIN ", $_from_tbls);
        }

    }

    if (!isset($from_tbls['products'])) {
        $search_query .= $sql_tbl['products'];
    }

    foreach ($inner_joins as $ijname => $ij) {

        $search_query .= " INNER JOIN ";

        if (!empty($ij['tblname'])) {
            $search_query .= $sql_tbl[$ij['tblname']]." as ".$ijname;
        } else {
            $search_query .= $sql_tbl[$ijname];
        }

        $search_query .= " ON ".$ij['on'];

        foreach ($left_joins as $ljname => $lj) {

            if (empty($lj['parent']) || $lj['parent'] != $ijname)
                continue;

            $search_query .= " LEFT JOIN ";

            if (!empty($lj['tblname'])) {
                $search_query .= $sql_tbl[$lj['tblname']]." as ".$ljname;
            } else {
                $search_query .= $sql_tbl[$ljname];
            }

            $search_query .= " ON ".$lj['on'];

        }

    }

    foreach ($left_joins as $ljname => $lj) {

        if (!empty($lj['parent']))
            continue;

        $search_query .= " LEFT JOIN ";

        if (!empty($lj['tblname'])) {
            $search_query .= $sql_tbl[$lj['tblname']] . " as " . $ljname;
        } else {
            $search_query .= $sql_tbl[$ljname];
        }

        $search_query .= " ON ".$lj['on'];

    }

    if (!empty($where))
        $search_query .= " WHERE " . implode(" AND ", $where);
    
    $search_query .= $query;

    $limit = max(intval($limit), 0);


    if (XCSearchProducts::isQueryWithGroupBy($limit, $use_group_by)) {
        if (!empty($groupbys)) {
            $search_query .= " GROUP BY " . implode(", ", $groupbys);
            $group_by_is_added = true;
        }
    }


    if (!empty($orderbys)) {
        $search_query .= " ORDER BY " . implode(", ", $orderbys);
    } elseif(!empty($group_by_is_added)) {
        // http://dev.mysql.com/doc/refman/5.7/en/select.html#idm140169758571744
        $search_query .= " ORDER BY NULL ";
    }

    if (XCSearchProducts::isQueryWithGroupBy($limit, $use_group_by)) {
        if (!empty($limit))
            $search_query .= " LIMIT " . $limit;

        $result = func_query($search_query, USE_SQL_DATA_CACHE);
    } else {
        
        $uniq_result = array();
        $portion_num = 0;
        while(count($uniq_result) < $limit) {
            $limit_counted = XCSearchProducts::getLimitWithDuplicates($limit);
            $result = func_query(
                $search_query . ' LIMIT ' . $limit_counted*$portion_num . ',' . $limit_counted, 
                USE_SQL_DATA_CACHE
            );
            if (empty($result))
                break;

            foreach ($result as $k=>$v) {
                if (!isset($uniq_result[$v['productid']])) {
                    $uniq_result[$v['productid']] = $v;

                    if (count($uniq_result) >= $limit)
                        break;
                } 

            }

            $portion_num++;
        }

        $result = array_values($uniq_result);
    }

    if ($id_only == true) {
        return $result;
    }

    $ids = array();

    if (!empty($result)) {
        foreach($result as $v) {
            $ids[] = $v['productid'];
        }
    }

    if (
        $result
        && in_array($current_area, array('C','B'))
    ) {
        // Post-process the result products array

        if (
            !empty($active_modules['Extra_Fields'])
            && empty($skip_tables['extra_fields'])
        ) {
            $products_ef = func_query_hash("SELECT $sql_tbl[extra_fields].*, $sql_tbl[extra_field_values].*, IF($sql_tbl[extra_fields_lng].field != '', $sql_tbl[extra_fields_lng].field, $sql_tbl[extra_fields].field) as field FROM $sql_tbl[extra_field_values], $sql_tbl[extra_fields] LEFT JOIN $sql_tbl[extra_fields_lng] ON $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_fields_lng].fieldid AND $sql_tbl[extra_fields_lng].code = '$shop_language' WHERE $sql_tbl[extra_fields].fieldid = $sql_tbl[extra_field_values].fieldid AND $sql_tbl[extra_field_values].productid IN (".implode(",", $ids).") AND $sql_tbl[extra_fields].active = 'Y' ORDER BY $sql_tbl[extra_fields].orderby", "productid");
        }

        if (
            !empty($active_modules['Product_Options'])
            && !empty($ids)
            && empty($skip_tables['pricing'])
        ) {
            $_prices = $result;

            array_walk($_prices, create_function('&$val, $key', '$val = $val["price"];'));
            $options_markups = func_get_default_options_markup_list(array_combine($ids , $_prices));
        }

        foreach ($result as $key => $value) {

            if (empty($skip_tables['pricing'])) {
                $value['taxed_price'] = $result[$key]['taxed_price'] = $value['price'];
            }

            if (
                !empty($active_modules['Product_Options'])
                && !empty($options_markups[$value['productid']])
                && empty($skip_tables['pricing'])
            ) {
                // Add product options markup
                if ($result[$key]['price'] != 0)
                    $result[$key]['price'] += $options_markups[$value['productid']];

                $result[$key]['taxed_price'] = $result[$key]['price'];

                $value = $result[$key];

            }

            if (
                !empty($cart)
                && !empty($cart['products'])
                && $current_area == 'C'
            ) {

                // Update quantity for products that already placed into the cart

                $in_cart = 0;

                settype($value['variantid'], 'int');
                foreach ($cart['products'] as $cart_item) {
                    if (
                        $cart_item['productid'] == $value['productid']
                        && $cart_item['variantid'] == $value['variantid']
                    ) {
                        $in_cart += $cart_item['amount'];
                    }
                }

                $result[$key]['avail'] -= $in_cart;

                if ($result[$key]['avail'] < 0) {
                    $result[$key]['avail'] = 0;
                } 

            }

            if (
                !empty($active_modules['Extra_Fields'])
                && isset($products_ef[$value['productid']])
            ) {
                $result[$key]['extra_fields'] = $products_ef[$value['productid']];
            }

            // Get thumbnail's URL (uses only if images stored in FS)
            if (empty($skip_tables['images_T'])) {
                if ($use_tiny_thumbnails && !empty($result[$key]['imageid'])) {

                    $thumb_url_data = func_image_cache_get_image('T', 'tinythmbn', $result[$key]['imageid']);

                    if (!empty($thumb_url_data)) {
                        $result[$key]['tmbn_url'] = $thumb_url_data['url'];
                        $result[$key]['image_x'] = $thumb_url_data['width'];
                        $result[$key]['image_y'] = $thumb_url_data['height'];
                        unset($thumb_url_data);
                    } else {
                        $result[$key]['image_x'] = $config['Appearance']['tiny_thumbnail_width'];
                    }

                } else {

                    if ($config['setup_images']['T']['location'] == 'FS') {
                        $value['is_thumbnail'] = !is_null($value['image_path']);
                    }

                    $image_ids['T'] = $value['productid'];
                    $image_ids['P'] = $value['productid'];

                    if (isset($value['is_thumbnail']) && $value['is_thumbnail'] && !empty($value['image_path'])) {

                        // FS thumbnail is available. It is not required to process P image
                        $image_data['image_url'] = func_get_image_url($value['productid'], 'T', $value['image_path']);

                    } elseif ($config['setup_images']['T']['location'] == 'FS') {

                        // FS thumbnail is not available. It is not required to process T image
                        unset($image_ids['T']);

                        $image_data = func_get_image_url_by_types($image_ids, 'P');

                    } else {

                        $image_data = func_get_image_url_by_types($image_ids, 'T');

                    }

                    $result[$key]['tmbn_url'] = $image_data['image_url'];

                    unset($result[$key]['image_path']);
                    $thumb_dims = XCSearchProducts::getThumbDimsByIds($ids);

                    $dims_tmp = isset($thumb_dims[$value['productid']])
                        ? $thumb_dims[$value['productid']]
                        : $config['setup_images']['T'];

                    $result[$key] = func_array_merge($result[$key], $dims_tmp);

                    $_limit_width = $config['Appearance']['simple_thumbnail_width'];
                    $_limit_height = $config['Appearance']['simple_thumbnail_height'];
                    $result[$key] = func_get_product_tmbn_dims($result[$key], $_limit_width, $_limit_height);

                }
            }

            if (
                $current_area == 'C'
                && !empty($value['taxid'])
            ) {
                $result[$key]['taxes'] = func_get_product_taxes($result[$key], $logged_userid);
            }

            if (empty($skip_tables['pricing']) && !empty($active_modules['XPayments_Subscriptions'])) {
                $result[$key] = func_xps_attachPlanToProduct($result[$key]);
            }

        } // foreach ($result as $key => $value)

    }

    return $result;
}

/**
 * Put all product info into $product array
 */
function func_select_product($id, $membershipid, $redirect_if_error = TRUE, $clear_price = FALSE, $always_select = XCProduct::SELECT_PRODUCT_CUSTOMER_MODE, $prefered_image_type = 'P')
{
    global $logged_userid, $login_type, $current_area, $single_mode, $cart, $current_location;
    global $store_language, $sql_tbl, $config, $active_modules;

    x_load('files','taxes', 'image', 'category');

    $in_cart = 0;

    $id = intval($id);

    $membershipid = intval($membershipid);

    $p_membershipid_condition = $membershipid_condition = '';
    // For compatibility with old $always_select=false/true values
    $always_select = ($always_select === false ? XCProduct::SELECT_PRODUCT_CUSTOMER_MODE : $always_select);
    $always_select = ($always_select === true ? XCProduct::SELECT_PRODUCT_SHOW_DISABLED : $always_select);

    x_load_module('Product_Options');
    $is_customer_area_mode = ($current_area == 'C' && $always_select !== XCProduct::SELECT_PRODUCT_ADMIN_PREVIEW);
    if ($is_customer_area_mode) {

        $membershipid_condition = " AND ($sql_tbl[category_memberships].membershipid = '$membershipid' OR $sql_tbl[category_memberships].membershipid IS NULL) ";
        $p_membershipid_condition = " AND ($sql_tbl[product_memberships].membershipid = '$membershipid' OR $sql_tbl[product_memberships].membershipid IS NULL) ";
        $price_condition = " AND $sql_tbl[quick_prices].membershipid ".((empty($membershipid) || empty($active_modules['Wholesale_Trading'])) ? "= '0'" : "IN ('$membershipid', '0')")." AND $sql_tbl[quick_prices].priceid = $sql_tbl[pricing].priceid";

    } else {

        $price_condition = " AND $sql_tbl[pricing].membershipid = '0' AND $sql_tbl[pricing].quantity = '1' AND " . XCVariantsSQL::isProductPrice();

    }

    if (
        $current_area == 'C'
        && !empty($cart)
        && !empty($cart['products'])
    ) {

        foreach ($cart['products'] as $cart_item) {

            if ($cart_item['productid'] == $id) {

                $in_cart += $cart_item['amount'];

            }

        }

    }

    $login_condition = '';

    if (!$single_mode) {
        $login_condition = ((!empty($logged_userid) && $login_type == 'P') ? "AND $sql_tbl[products].provider='$logged_userid'" : "");
    }

    $add_fields = '';

    $join = '';

    if ($current_area == 'C') {
        $join .= " INNER JOIN $sql_tbl[quick_flags] ON $sql_tbl[products].productid = $sql_tbl[quick_flags].productid";
    }    

    if (!empty($active_modules['Product_Options']) && $current_area != "C" && $current_area != "B") {
        $join .= XCVariantsSQL::getJoinQueryAllRows();
        $add_fields .= ", " . XCVariantsSQL::isHaveVariant('is_variants');
    }

    if (!empty($active_modules['Feature_Comparison'])) {
        $join .= " LEFT JOIN $sql_tbl[product_features] ON $sql_tbl[product_features].productid = $sql_tbl[products].productid";
        $add_fields .= ", $sql_tbl[product_features].fclassid";
    }

    if (!empty($active_modules['Manufacturers'])) {
        $join .= " LEFT JOIN $sql_tbl[manufacturers] ON $sql_tbl[manufacturers].manufacturerid = $sql_tbl[products].manufacturerid";
        $add_fields .= ", $sql_tbl[manufacturers].manufacturer";
    }

    if (!empty($active_modules['Special_Offers'])) {
        $join .= " LEFT JOIN $sql_tbl[offer_product_params] ON $sql_tbl[offer_product_params].productid = $sql_tbl[products].productid";
        $add_fields .= ", $sql_tbl[offer_product_params].sp_discount_avail, $sql_tbl[offer_product_params].bonus_points";
    }

    if ($is_customer_area_mode) {

        $add_fields .= ", $sql_tbl[quick_flags].*, $sql_tbl[quick_prices].variantid, $sql_tbl[quick_prices].priceid";

        if (empty($membershipid) || empty($active_modules['Wholesale_Trading'])) {
            $membershipid_condition = " = '0'";
        } else {
            $membershipid_condition = " IN ('$membershipid', 0)";
        }

        $join .= " INNER JOIN $sql_tbl[quick_prices] ON $sql_tbl[products].productid = $sql_tbl[quick_prices].productid AND $sql_tbl[quick_prices].membershipid $membershipid_condition ";
    }
    $join .= " INNER JOIN $sql_tbl[products_lng_current] ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid ";

    $join .= " LEFT JOIN $sql_tbl[product_memberships] ON $sql_tbl[product_memberships].productid = $sql_tbl[products].productid";

    if ($current_area == 'C' && empty($active_modules['Product_Configurator'])) {
        $login_condition .= " AND $sql_tbl[products].product_type <> 'C' AND $sql_tbl[products].forsale <> 'B' ";
    }

    $join .= " LEFT JOIN $sql_tbl[clean_urls] ON $sql_tbl[clean_urls].resource_type = 'P' AND $sql_tbl[clean_urls].resource_id = $sql_tbl[products].productid";

    $add_fields .= ", $sql_tbl[clean_urls].clean_url, $sql_tbl[clean_urls].mtime";

    $product = func_query_first("SELECT $sql_tbl[products].*, $sql_tbl[products].avail-$in_cart AS avail, MIN($sql_tbl[pricing].price) as price $add_fields , $sql_tbl[products_lng_current].* FROM $sql_tbl[pricing] INNER JOIN $sql_tbl[products] ON $sql_tbl[pricing].productid = $sql_tbl[products].productid AND $sql_tbl[products].productid='$id' $join WHERE 1 ".$login_condition.$p_membershipid_condition.$price_condition." GROUP BY $sql_tbl[products].productid ORDER BY NULL");

    $category = XCProductsCategoriesSQL::getProductCategory($id, $is_customer_area_mode);
    if (!empty($category)) {
        $categoryid = $category['categoryid']; 
    } else {
        $categoryid = FALSE;
    }

    // Check product's provider Activity
    if (
        !$single_mode
        && $is_customer_area_mode
        && !empty($product)
    ) {
        x_load('user'); // 'user' for XCUserActivity class
        if (!XCUserActivity::isActivatedUser($product['provider'])) {
            $product = array();
            $provider_is_disabled = TRUE;
        }
    }

    // Error handling

    if (
        !$product
        || !$categoryid
    ) {

        if ($redirect_if_error) {

            $product_is_exists = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products] WHERE productid = '$id'") > 0;

            if ($product_is_exists) {
                // Try to recover the product
                if (!$categoryid) {
                    db_query("UPDATE $sql_tbl[products_categories] SET main='Y' WHERE productid = '$id' LIMIT 1");
                }

                if (in_array($current_area, array('A','P'))) {
                    // Try to recover the product
                    x_load_module('Product_Options');
                    if (
                        empty($provider_is_disabled)
                        && XCVariantsSQL::isVariantsExist()
                        && func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[variants] WHERE productid = '$id'") == 0
                    ) {
                        XCVariantsChange::repairIntegrity($id);
                    }

                    if (
                        empty($provider_is_disabled)
                        && func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products_lng_current] WHERE productid = '$id'") == 0) {
                        x_load('backoffice');
                        func_repair_lng_integrity('products_lng_', $sql_tbl['products'], 'productid', "'restored_product' AS product, 'restored_product' AS descr, 'restored_product' AS fulldescr, '' AS keywords");
                    }
                }

                if (!empty($provider_is_disabled)) {
                    func_403(100);
                } elseif (!$categoryid && !empty($product)) {
                    func_403(101);
                } else {
                    func_403(33);
                }
            } else {
                func_page_not_found();
            }

        } else {

            return false;

        }

    }

    $product['productid'] = $id;
    $product['categoryid'] = $categoryid;
    $product['orderby'] = $category['orderby'];

    $tmp = func_query_column("SELECT membershipid FROM $sql_tbl[product_memberships] WHERE productid = '$product[productid]'");

    if (!empty($tmp) && is_array($tmp)) {

        $product['membershipids'] = array();

        foreach ($tmp as $v) {
            $product['membershipids'][$v] = 'Y';
        }
    }

    if (!empty($product['variantid']) && !empty($active_modules['Product_Options'])) {

        $tmp = XCVariantsSQL::getVariantById($product['productid'], $product['variantid']);

        if (!empty($tmp)) {

            func_unset($tmp, 'def');

            $product = func_array_merge($product, $tmp);

        } else {

            func_unset($product, 'variantid');

        }

    }

    // Detect product thumbnail and image
    $image_ids = array();

    if (
        !empty($product['variantid'])
        && !empty($active_modules['Product_Options'])
        && (
            $current_area == 'C'
            || $current_area == 'B'
        )
    ) {
        $image_ids['W'] = $product['variantid'];
    }

    $image_ids['P'] = $product['productid'];
    $image_ids['T'] = $product['productid'];

    $image_data = func_get_image_url_by_types($image_ids, $prefered_image_type);

    $product['taxed_price'] = $product['price'];

    if (
        $current_area == 'C'
        || $current_area == 'B'
    ) {

        // Check if product is not available for sale

        if (empty($active_modules['Egoods']))
            $product['distribution'] = '';

        global $pconf;

        if ($product['forsale'] == 'B' && empty($pconf)) {

            if (
                isset($cart['products'])
                && is_array($cart['products'])
            ) {

                foreach ($cart['products'] as $k => $v) {

                    if ($v['productid'] == $product['productid']) {

                        $pconf = $product['productid'];

                        break;

                    }

                }

            }

            if (empty($pconf)) {

                x_session_register('configurations');

                global $configurations;

                if (!empty($configurations)) {

                    foreach ($configurations as $c) {

                        if (empty($c['steps']) || !is_array($c['steps']))
                            continue;

                        foreach ($c['steps'] as $s) {
                            if (empty($s['slots']) || !is_array($s['slots']))
                                continue;

                            foreach($s['slots'] as $sl) {
                                if ($sl['productid'] == $product["productid"]) {
                                    $pconf = $product['productid'];
                                    break;
                                }
                            }

                        }

                    }

                }

            }

        }

        if (
            $always_select === XCProduct::SELECT_PRODUCT_CUSTOMER_MODE
            && (
                $product['forsale'] == 'N'
                || (
                    $product['forsale'] == 'B'
                    && empty($pconf)
                )
            )
        ) {

            if ($redirect_if_error)
                func_header_location("error_message.php?product_disabled");
            else
                return false;

        }

        if (
            $current_area == 'C'
            && !$clear_price
        ) {

            // Calculate taxes and price including taxes

            global $logged_userid;

            $orig_price = $product['price'];

            $product['taxes'] = func_get_product_taxes($product, $logged_userid);

            // List price corrections
            if (($product['taxed_price'] != $orig_price) && ($product['list_price'] > 0))
                $product['list_price'] = price_format($product['list_price'] * $product['taxed_price'] / $orig_price);
        }

    } else {

        $product['is_thumbnail'] = func_query_first_cell("SELECT id FROM $sql_tbl[images_T] WHERE id = '$product[productid]'") != false;
        $product['is_pimage'] = func_query_first_cell("SELECT id FROM $sql_tbl[images_P] WHERE id = '$product[productid]'") != false;

        if ($product['is_thumbnail']) {

            list($x, $y) = func_crop_dimensions(
                $image_data['images']['T']['x'],
                $image_data['images']['T']['y'],
                $config['images_dimensions']['T']['width'],
                $config['images_dimensions']['T']['height']
            );

            if (
                $image_data['images']['T']['x'] <= $x
                && $image_data['images']['T']['y'] <= $y
            ) {
                $x = $image_data['images']['T']['x'];
                $y = $image_data['images']['T']['y'];
            }

            $image_data['images']['T']['new_x'] = $x;
            $image_data['images']['T']['new_y'] = $y;

        }

        if ($product['is_pimage']) {

            list($x, $y) = func_crop_dimensions(
                $image_data['images']['P']['x'],
                $image_data['images']['P']['y'],
                $config['images_dimensions']['P']['width'],
                $config['images_dimensions']['P']['height']
            );

            if (
                $image_data['images']['P']['x'] <= $x
                && $image_data['images']['P']['y'] <= $y
            ) {
                $x = $image_data['images']['P']['x'];
                $y = $image_data['images']['P']['y'];
            }

            $image_data['images']['P']['new_x'] = $x;
            $image_data['images']['P']['new_y'] = $y;

        }

    }

    // Add product features
    if (
        !empty($active_modules['Feature_Comparison'])
        && $product['fclassid'] > 0
    ) {
        $product['features'] = func_get_product_features($product['productid']);
    }

    if (!empty($active_modules['Refine_Filters'])) {
        func_rf_get_product_custom_classes($product);
    }

    if (
        !empty($active_modules['Special_Offers'])
        && empty($product['sp_discount_avail'])
    ) {
        $product['sp_discount_avail'] = 'Y';
    }

    $product['producttitle'] = $product['product'];

    if (
        $current_area == 'C'
        || $current_area == 'B'
    ) {

        $product['descr']         = func_eol2br($product['descr']);
        $product['fulldescr']     = func_eol2br($product['fulldescr']);

        $product['allow_active_content'] = func_get_allow_active_content($product['provider']);

        if (!$product['allow_active_content']) {
            $product['descr']         = func_xss_free($product['descr']);
            $product['fulldescr']     = func_xss_free($product['fulldescr']);
        }

    }

    // Get thumbnail's URL (uses only if images stored in FS)

    if (is_array($image_data)) {

        if (
            $current_area == 'C'
            || $current_area == 'B'
        ) {
            list($image_data['image_x'], $image_data['image_y']) = func_crop_dimensions(
                $image_data['image_x'],
                $image_data['image_y'],
                $config['Appearance']['image_width'],
                $config['Appearance']['image_height']
            );
        }

        $product = array_merge($product, $image_data);

    }

    $product['clean_urls_history'] = func_query_hash("SELECT id, clean_url FROM $sql_tbl[clean_urls_history] WHERE resource_type = 'P' AND resource_id = '".$product['productid']."' ORDER BY mtime DESC", "id", false, true);

    if (!empty($active_modules['XPayments_Subscriptions'])) {
        $product = func_xps_attachPlanToProduct($product);
    }

    $product['appearance'] = func_get_appearance_data($product);

    if (
        !empty($active_modules['Customer_Reviews'])
        && $config['Customer_Reviews']['customer_voting'] == 'Y'
    ) {
        $product['rating_data'] = func_get_product_rating($product['productid']);
    }

    if (
        !empty($active_modules['Advanced_Customer_Reviews'])
    ) {
        func_acr_select_product($product);
    }

    if (
        $current_area != 'C'
        && $current_area != 'B'
    ) {
        $product['add_categoryids'] = func_query_hash($a = "SELECT categoryid, productid FROM $sql_tbl[products_categories] WHERE main = 'N' AND productid='$id'", 'categoryid', false, true);
    }
    
    return $product;
}

/**
 * Get delivery options by product ID
 */
function func_select_product_delivery($id)
{
    global $sql_tbl;

    return func_query("select $sql_tbl[shipping].*, count($sql_tbl[delivery].productid) as avail from $sql_tbl[shipping] left join $sql_tbl[delivery] on $sql_tbl[delivery].shippingid=$sql_tbl[shipping].shippingid and $sql_tbl[delivery].productid='$id' where $sql_tbl[shipping].active='Y' group by shippingid");
}

/**
 * Add data to service array (Group editing of products functionality)
 */
function func_ge_add($data, $geid = false)
{
    global $sql_tbl, $XCARTSESSID;

    if (strlen($geid) < 32)
        $geid = md5(uniqid(mt_rand()));

    if (!is_array($data))
        $data = array($data);

    $query_data = array(
        'sessid' => $XCARTSESSID,
        'geid'   => $geid,
    );

    foreach ($data as $pid) {

        if (empty($pid))
            continue;

        $query_data['productid'] = $pid;

        func_array2insert(
            'ge_products',
            $query_data
        );

    }

    return $geid;
}

/**
 * Get length of service array (Group editing of products functionality)
 */
function func_ge_count($geid)
{
    global $sql_tbl;

    return func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[ge_products] WHERE geid = '$geid'");
}

/**
 * Get next line of service array (Group editing of products functionality)
 */
function func_ge_each($geid, $limit = 1, $productid = 0)
{
    global $__ge_res, $sql_tbl;

    if (
        !is_bool($__ge_res)
        && (
            !is_resource($__ge_res)
            && !is_object($__ge_res)
        )
    ) {
        $__ge_res = false;
    }

    if ($__ge_res === true) {

        $__ge_res = false;

        return false;

    } elseif ($__ge_res === false) {

        $__ge_res = db_query("SELECT productid FROM $sql_tbl[ge_products] WHERE geid = '$geid'");

        if (!$__ge_res) {

            $__ge_res = false;

            return false;

        }

    }

    $res = true;
    $ret = array();

    $limit = intval($limit);

    if ($limit <= 0)
        $limit = 1;

    $orig_limit = $limit;

    while (($limit > 0) && ($res = db_fetch_row($__ge_res))) {

        if ($productid == $res[0])
            continue;

        $ret[] = $res[0];

        $limit--;

    }

    if (!$res) {

        func_ge_reset($geid);

        $__ge_res = !empty($ret);

    }

    if (empty($ret))
        return false;

    return ($orig_limit == 1)
        ? $ret[0]
        : $ret;
}

/**
 * Check element of service array (Group editing of products functionality)
 */
function func_ge_check($geid, $id)
{
    global $sql_tbl;

    return (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[ge_products] WHERE geid = '$geid' AND productid = '$id'") > 0);
}

/**
 * Reset pointer of service array (Group editing of products functionality)
 */
function func_ge_reset($geid)
{
    global $__ge_res;

    if ($__ge_res !== false)
        @db_free_result($__ge_res);

    $__ge_res = false;
}

/**
 * Get stop words list
 */
function func_get_stopwords($code = false) { //{{{
    global $xcart_dir, $shop_language;

    if ($code === false)
        $code = $shop_language;

    // English stopwords copied from include/stopwords_en.php to increase performance
    $stopwords_en = array('i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an', 'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very');

    if ($code == 'en') {
        return $stopwords_en;
    }

    $code = strtolower($code);
    if (!file_exists($xcart_dir . '/include/stopwords_' . $code . '.php'))
        return false;

    $stopwords = array();

    // Other languages
    include $xcart_dir . '/include/stopwords_' . $code  .'.php';

    return $stopwords;
} //}}}

/**
 * Create unique product code (SKU)
 */
function func_generate_sku($provider, $prefix = false, $max = 0)
{
    global $sql_tbl, $logged_userid, $active_modules;

    $max_productcode_len = 32;

    if (empty($prefix) || strlen($prefix) > 26)
        $prefix = 'SKU';

    if (empty($provider))
        $provider = $logged_userid;

    $len = strlen($prefix);
    $cnt = 100;

    if (!empty($active_modules['Simple_Mode']))
        $provider_cond = '';
    else
        $provider_cond = " AND $sql_tbl[products].provider = '" . $provider . "'";

    $max_p = intval(func_query_first_cell("SELECT MAX(SUBSTRING(productcode, ".($len+1).")) as max FROM $sql_tbl[products] WHERE SUBSTRING(productcode, 1, $len) = '".addslashes($prefix)."' $provider_cond"));

    $max_v = empty($active_modules['Product_Options'])
        ? 0
        : intval(func_query_first_cell("SELECT MAX(SUBSTRING($sql_tbl[variants].productcode, ".($len+1).")) AS max FROM $sql_tbl[products] ".XCVariantsSQL::getJoinQueryVariants()." WHERE SUBSTRING($sql_tbl[variants].productcode, 1, $len) = '".addslashes($prefix)."' $provider_cond"));

    $max = max($max_p, $max_v);

    do {

        $sku_new = $prefix . ++$max;

    } while (
        strlen($sku_new) <= $max_productcode_len
        && !func_sku_is_unique(addslashes($sku_new), $provider)
        && $cnt-- > 0
    );

    if (
        strlen($sku_new) > $max_productcode_len
        || !func_sku_is_unique(addslashes($sku_new), $provider)
    ) {

        $cnt = 100;

        do {

            $sku_new = substr($prefix . md5(uniqid('SKU', true)), 0, $max_productcode_len);

        } while (
            !func_sku_is_unique(addslashes($sku_new), $provider)
            && $cnt-- > 0
        );

        if (!func_sku_is_unique(addslashes($sku_new), $provider)) {

            do {

                $sku_new = md5(uniqid('SKU', true));

            }  while (!func_sku_is_unique(addslashes($sku_new), $provider));

        }

    }

    return $sku_new;
}

/**
 * Check SKU - exists or not
 */
function func_sku_is_unique($sku, $provider, $add_condition='')
{
    global $sql_tbl, $active_modules;

    if (!empty($active_modules['Simple_Mode']))
        $provider_cond = '';
    else
        $provider_cond = " AND $sql_tbl[products].provider = '" . $provider . "'";

    if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products] WHERE productcode = '$sku' $add_condition $provider_cond") > 0)
        return false;

    return (
        empty($active_modules['Product_Options'])
        || XCVariantsSQL::isSkuUnique($sku, $provider_cond)
    );
}

/**
 * Get product's title
 */
function func_get_product_title($productid)
{
    global $sql_tbl, $config;

    if (!is_int($productid) || $productid < 1)
        return false;

    $product = func_query_first("SELECT $sql_tbl[products].title_tag, $sql_tbl[products_categories].categoryid FROM $sql_tbl[products] INNER JOIN $sql_tbl[products_categories] ON $sql_tbl[products].productid = $sql_tbl[products_categories].productid AND $sql_tbl[products_categories].main = 'Y' WHERE $sql_tbl[products].productid = '$productid'");

    if (!is_array($product) || count($product) == 0)
        return false;

    $product['title_tag'] = trim($product['title_tag']);

    if (empty($product['title_tag'])) {

        x_load('category');

        $ids = array_reverse(func_get_category_path($product['categoryid']));

        $parents = func_query_hash("SELECT categoryid, title_tag FROM $sql_tbl[categories] WHERE categoryid IN ('".implode("', '", $ids)."') AND override_child_meta = 'Y'", "categoryid", false);

        while ((list(, $cid) = each($ids)) && empty($product['title_tag'])) {

            $parents[$cid]['title_tag'] = trim($parents[$cid]['title_tag']);

            if (empty($product['title_tag']) && !empty($parents[$cid]['title_tag']))
                $product['title_tag'] = $parents[$cid]['title_tag'];

        }

    }

    if (empty($product['title_tag']))
        $product['title_tag'] = trim($config['SEO']['site_title']);

    return $product['title_tag'];
}

/**
 * Get product's meta description and meta keywords data
 */
function func_get_product_meta($productid)
{
    global $sql_tbl, $config;

    if (!is_int($productid) || $productid < 1)
        return false;

    $product = func_query_first("SELECT $sql_tbl[products].meta_description, $sql_tbl[products].meta_keywords, $sql_tbl[products_categories].categoryid FROM $sql_tbl[products] INNER JOIN $sql_tbl[products_categories] ON $sql_tbl[products].productid = $sql_tbl[products_categories].productid AND $sql_tbl[products_categories].main = 'Y' WHERE $sql_tbl[products].productid = '$productid'");

    if (!is_array($product) || count($product) == 0)
        return false;

    $product['meta_description'] = trim($product['meta_description']);
    $product['meta_keywords']    = trim($product['meta_keywords']);

    if (
        empty($product['meta_description'])
        || empty($product['meta_keywords'])
    ) {

        $ids = array_reverse(func_get_category_path($product['categoryid']));

        $parents = func_query_hash("SELECT categoryid, meta_description, meta_keywords FROM $sql_tbl[categories] WHERE categoryid IN ('".implode("', '", $ids)."') AND override_child_meta = 'Y'", "categoryid", false);

        while ((list(,$cid) = each($ids)) && (empty($product['meta_description']) || empty($product['meta_keywords']))) {

            $parents[$cid]['meta_description']     = trim($parents[$cid]['meta_description']);
            $parents[$cid]['meta_keywords']     = trim($parents[$cid]['meta_keywords']);

            if (empty($product['meta_description']) && !empty($parents[$cid]['meta_description']))
                $product['meta_description'] = $parents[$cid]['meta_description'];

            if (empty($product['meta_keywords']) && !empty($parents[$cid]['meta_keywords']))
                $product['meta_keywords'] = $parents[$cid]['meta_keywords'];
        }
    }

    if (empty($product['meta_description']))
        $product['meta_description'] = trim($config['SEO']['meta_descr']);

    if (empty($product['meta_keywords']))
        $product['meta_keywords'] = trim($config['SEO']['meta_keywords']);

    return array(
        $product['meta_description'],
        $product['meta_keywords'],
    );
}

function func_get_product_sql_orderby($orderby = '') {
    global $config, $sql_tbl;

    if (empty($orderby)) {
        $orderby = $config['Appearance']['products_order']
            ? $config['Appearance']['products_order']
            : 'orderby';
    }

    $orderby_rules = array (
        'title'       => $sql_tbl['products_lng_current'] . '.product',
        'quantity'    => $sql_tbl['products'] . '.avail',
        'orderby'     => $sql_tbl['products_categories'] . '.orderby',
        'quantity'    => $sql_tbl['products'] . '.avail',
        'price'       => 'price',
        'productcode' => $sql_tbl['products'] . '.productcode',
    );

    return isset($orderby_rules[$orderby]) ? $orderby_rules[$orderby] : $sql_tbl['products'] . '.productid';
}

function func_get_allow_active_content($provider) { // {{{
    static $result;
    global $sql_tbl, $active_modules;

    if (!empty($active_modules['Simple_Mode'])) {
        return TRUE;
    }

    if (!isset($result[$provider])) {
        $provider_id = intval($provider);
        assert('$provider === "$provider_id" /* '.__FUNCTION__.': $provider is not a number */');
        $user = func_query_first("SELECT usertype, trusted_provider FROM $sql_tbl[customers] WHERE id=$provider_id");
        $result[$provider] = ($user['trusted_provider'] == 'Y' || $user['usertype'] != 'P');
    }

    return $result[$provider];
} // }}}

/**
 * Check if product is added to cart
 */
function func_is_product_added_to_cart($product, $cart)
{
    if (is_array($cart) && is_array($cart['products'])) {
        foreach ($cart['products'] as $cartProduct) {
            if ($cartProduct['productid'] == $product['productid']) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Get appearance product service data
 */
function func_get_appearance_data($product)
{
    global $config, $active_modules, $current_area, $login, $is_comparison_list, $cart;

    $appearance = array(
        'empty_stock'   => $config['General']['unlimited_products'] != "Y"
            && (
                $product['avail'] <= 0
                || $product['avail'] < $product['min_amount']
            ),

        'has_price' => $product['taxed_price'] > 0
            || (
                !empty($product['variantid'])
                && isset($product['variants_has_price'])
                && !empty($product['variants_has_price'])
            ),

        'has_market_price' => $product['list_price'] > 0
            && $product['taxed_price'] < $product['list_price'],

        'buy_now_enabled'  => $current_area == 'C'
            && $config['Appearance']['buynow_button_enabled'] == "Y",

        'buy_now_form_enabled' => $product['price'] > 0
            || (
                !empty($active_modules['Special_Offers'])
                && isset($product['use_special_price'])
            ) || $product['product_type'] == 'C',

        'min_quantity' => max(1, $product['min_amount']),

        'max_quantity' => $config['General']['unlimited_products'] == "Y"
            ?       max($config['Appearance']['max_select_quantity'], $product['min_amount'])
            : min(  max($config['Appearance']['max_select_quantity'], $product['min_amount']), $product['avail']),

        'buy_now_buttons_enabled' => $config['General']['unlimited_products'] == "Y"
            || (
                $product['avail'] > 0
                && $product['avail'] >= $product['min_amount']
            ) || (
                !empty($product['variantid'])
                && $product['avail'] > 0
            ),

        'force_1_amount' => $product['distribution'],

        'added_to_cart' => func_is_product_added_to_cart($product, $cart),
    );

    $appearance['quantity_input_box_enabled'] = $config['Appearance']['show_quantity_as_box'] == 'Y';

    $appearance['is_auction'] = !(
        (
            $appearance['empty_stock']
            && !empty($product['variantid'])
        ) || (
            $product['taxed_price'] != 0
            || (
                !empty($product['variantid'])
                && isset($product['variants_has_price'])
                && $product['variants_has_price']
            ) || (
                !empty($active_modules['Special_Offers'])
                && isset($product['use_special_price'])
                && $product['use_special_price']
            )
        )
    );

    if ($appearance['has_market_price'])
        $appearance['market_price_discount'] = sprintf("%3.0f", 100 - ($product['taxed_price'] / $product['list_price']) * 100);

    $cart_enabled_product_options = isset($product['is_product_options']) && $product['is_product_options'] == 'Y'
        ? $config['Product_Options']['buynow_with_options_enabled'] != 'Y'
        : true;

    $cart_enabled_avail = $config['General']['unlimited_products'] == "Y"
        ? true
        : $product['avail'] > 0 || empty($product['variantid']) || !$product['variantid'];

    $appearance['buy_now_cart_enabled'] = $appearance['buy_now_form_enabled'] && $cart_enabled_product_options && $cart_enabled_avail;

    $appearance['loop_quantity'] = $appearance['max_quantity'] + 1;

    $appearance['buy_now_add2wl_enabled'] = (
        $login
        || $config['Wishlist']['add2wl_unlogged_user'] == 'Y'
    ) && !empty($active_modules['Wishlist'])
    && $appearance['buy_now_buttons_enabled'];

    // Add to list button
    global $giftreg_events;

    if (
        $appearance['buy_now_add2wl_enabled']
        && (
            (
                !empty($active_modules['Feature_Comparison'])
                && !empty($product['fclassid'])
            ) || (
                !empty($active_modules['Gift_Registry'])
                && isset($giftreg_events)
                && !empty($giftreg_events)
            )
        )
    ) {
        $appearance['dropout_actions'] = array(
            'W' => true,
            'C' => (!empty($active_modules['Feature_Comparison']) && (!empty($product["fclassid"]))),
            'G' => (!empty($active_modules['Gift_Registry']) && !empty($giftreg_events)),
        );
    }

    return $appearance;
}

/**
 * Correct some depending ship box data settings bt:84873
 */
function func_adjust_ship_box_data($data, $small_item = 'N', $separate_box = 'N', $items_per_box = 1)
{
    if (func_num_args() == 1 && is_array($data))
        extract($data);

    $items_per_box = intval($items_per_box);

    if ($small_item == 'Y') {
        $data['separate_box'] = 'N';
    }

    if ($separate_box == 'Y')
        $data['items_per_box'] = ($items_per_box > 0) ? $items_per_box : 1;

    return $data;
}

/*
 * Get product thumbnail image dims with defined limits
 */
function func_get_product_tmbn_dims($product, $limit_width, $limit_height)
{
    if (
        !empty($product['image_x']) 
        && !empty($product['image_y'])
    ) {
        x_load('image');

        $product['tmbn_x'] = $product['image_x'];
        $product['tmbn_y'] = $product['image_y'];

        $need_resize = ($product['tmbn_x'] > $limit_width || $product['tmbn_y'] > $limit_height);

        if ($need_resize) {
            list(
                $product['tmbn_x'],
                $product['tmbn_y']
            ) = func_get_proper_dimensions(
                $product['image_x'],
                $product['image_y'],
                $limit_width,
                $limit_height
            );
        }
    }

    return $product;
}

/*
 * Get product_key depending on the New_Arrivals and/or On_Sale modules
 */
function func_tpl_get_product_key($product, $featured) {
    global $smarty, $active_modules;

    $return = false;

    if (!empty($product)) {
        if (
            !empty($active_modules['New_Arrivals'])
            && $smarty->getTemplateVars('is_new_arrivals_products') == 'Y'
        ) {
            $return = $product['productid'] . '_na_' . $product['add_date'] . '_' . $featured;
        } elseif (
            !empty($active_modules['On_Sale'])
            && $smarty->getTemplateVars('is_on_sale_products') == 'Y'
        ) {
            $return = $product['productid'] . '_os_' . $product['add_date'] . '_' . $featured;
        } else {
            $return = $product['productid'] . '_' . $product['add_date'] . '_' . $featured;
        }
    }
   
    return $return;
}

?>
