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

function bcse_ga4_is_debug_mode ()
{
    global $config;

    if (empty($config['Bcse_Ga4']['bcse_ga4_debug_ips'])) {
        return false;
    }

    $ips = explode(',', $config['Bcse_Ga4']['bcse_ga4_debug_ips']);
    $ips = array_map('trim', $ips);
    $ips = array_unique($ips);

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return in_array($ip, $ips);
}

function bcse_ga4_ecommerce_events ()
{
    return [
        'add_payment_info',
        'add_shipping_info',
        'add_to_cart',
        'add_to_wishlist',
        'begin_checkout',
        'generate_lead',
        'purchase',
        'refund',
        'remove_from_cart',
        'select_item',
        'select_promotion',
        'view_cart',
        'view_item',
        'view_item_list',
        'view_promotion',
    ];
}

function bcse_ga4_outputfilter ($html, $smarty)
{
    global $smarty, $config, $logged_userid, $_bcse_ga4_saved_logged_userid;

    $main = $smarty->getTemplateVars('main');

    $pageData = [
        'ecommerce'       => $config['Bcse_Ga4']['bcse_ga4_ecommerce'] == 'Y' ? 1 : 0,
        'checkout_module' => $smarty->getTemplateVars('checkout_module'),
        'vars'            => bcse_ga4_page_vars(),
    ];

    $events = [];

    if ($main == 'order_message') {
        $purchaseEvents = bcse_ga4_purchase_events();

        if (!empty($purchaseEvents)) {
            $events = array_merge($events, $purchaseEvents);
        }
    }

    if ($main == 'product') {
        $productEvents = bcse_ga4_product_events();

        if (!empty($productEvents)) {
            $events = array_merge($events, $productEvents);
        }
    }

    if ($main == 'search') {
        $searchEvents = bcse_ga4_search_events();

        if (!empty($searchEvents)) {
            $events = array_merge($events, $searchEvents);
        }
    }

    if ($main == 'catalog' && $smarty->getTemplateVars('current_category')) {
        $categoryEvents = bcse_ga4_category_events();

        if (!empty($categoryEvents)) {
            $events = array_merge($events, $categoryEvents);
        }
    }

    if ($main == 'manufacturer_products') {
        $manufacturerEvents = bcse_ga4_manufacturer_events();

        if (!empty($manufacturerEvents)) {
            $events = array_merge($events, $manufacturerEvents);
        }
    }

    if ($main == 'cart') {
        $cartEvents = bcse_ga4_cart_events();

        if (!empty($cartEvents)) {
            $events = array_merge($events, $cartEvents);
        }
    }

    if ($main == 'checkout') {
        $checkoutEvents = bcse_ga4_checkout_events();

        if (!empty($checkoutEvents)) {
            $events = array_merge($events, $checkoutEvents);
        }
    }

    if ($logged_userid && $logged_userid != $_bcse_ga4_saved_logged_userid) {
        $events[] = [
            'name' => 'login',
            'data' => [
                'method' => 'Website',
            ],
        ];
    }

    $_bcse_ga4_saved_logged_userid = $logged_userid;

    if (!empty($events)) {
        if ($config['Bcse_Ga4']['bcse_ga4_ecommerce'] != 'Y') {
            $events = array_filter($events, function ($event) {
                return !in_array($event['name'], bcse_ga4_ecommerce_events());
            });
        }

        if (!empty($events)) {
            $pageData['events'] = $events;
        }
    }

    if (!empty($pageData)) {
        $script = sprintf('<script id="bcse-ga4-data" type="application/json">%s</script>', json_encode($pageData));
        $html = preg_replace('`(</body>)`i', $script . '$1', $html);
    }

    return $html;
}

function bcse_ga4_purchase_events ()
{
    global $smarty, $config;

    x_session_unregister('_bcse_ga4_saved_paymentid');
    x_session_unregister('_bcse_ga4_saved_shippingid');

    $orders = $smarty->getTemplateVars('orders');

    $events = [];

    if (!empty($orders)) {
        foreach ($orders as $orderData) {
            $items = [];

            $order = $orderData['order'];
            $products = $orderData['products'];

            $itemIndex = 0;
            foreach ($products as $product) {
                $items[] = [
                    'item_id' => $product['productcode'],
                    'item_name' => $product['product'],
                    'affiliation' => $config['Company']['company_name'],
                    'coupon' => $order['coupon'],
                    'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
                    'index' => ++$itemIndex,
                    'price' => $product['price'],
                    'quantity' => $product['amount'],
                    'discount' => 0,
                ];
            }

            $events[] = [
                'name' => 'purchase',
                'data' => [
                    'transaction_id' => $order['orderid'],
                    'affiliation' => $config['Company']['company_name'],
                    'value' => $order['total'],
                    'tax' => $order['tax'],
                    'shipping' => $order['shipping_cost'],
                    'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
                    'coupon' => $order['coupon'],
                    'items' => $items,
                ]
            ];
        }
    }

    return $events;
}

function bcse_ga4_product_events ()
{
    global $smarty, $config;

    $product = $smarty->getTemplateVars('product');

    $events = [];

    $events[] = [
        'name' => 'view_item',
        'data' => [
            'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
            'value' => $product['taxed_price'],
            'items' => [[
                'item_id' => $product['productcode'],
                'item_name' => $product['product'],
                'affiliation' => $config['Company']['company_name'],
                'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
                'discount' => 0,
                'index' => 0,
                'price' => $product['taxed_price'],
                'quantity' => 1,
            ]]
        ]
    ];

    return $events;
}

function bcse_ga4_search_events ()
{
    global $search_data;

    $events = [];

    if (
        !empty($search_data)
        &&
        !empty($search_data['products'])
        &&
        !empty($search_data['products']['substring'])
    ) {
        $events[] = [
            'name' => 'search',
            'data' => [
                'search_term' => $search_data['products']['substring'],
            ],
        ];
    }

    if ($itemListEvent = bcse_ga4_item_list_event('search', 'Search Results')) {
        $events[] = $itemListEvent;
    }

    return $events;
}

function bcse_ga4_category_events ()
{
    global $smarty, $config;

    $events = [];

    if ($category = $smarty->getTemplateVars('current_category')) {
        if ($itemListEvent = bcse_ga4_item_list_event('category_' . $category['categoryid'], $category['category'], 'cat_products')) {
            $events[] = $itemListEvent;
        }
    }

    return $events;
}

function bcse_ga4_manufacturer_events ()
{
    global $smarty, $config;

    $events = [];

    if ($manufacturer = $smarty->getTemplateVars('manufacturer')) {
        if ($itemListEvent = bcse_ga4_item_list_event('manufacturer_' . $manufacturer['manufacturerid'], $manufacturer['manufacturer'])) {
            $events[] = $itemListEvent;
        }
    }

    return $events;
}

function bcse_ga4_cart_events ()
{
    global $smarty, $config;

    $cart = $smarty->getTemplateVars('cart');
    $products = $smarty->getTemplateVars('products');

    $events = [];

    if (!empty($products)) {
        $items = [];

        $itemIndex = 0;
        foreach ($products as $product) {
            $items[] = bcse_ga4_product_to_item($product, $product['amount'], ++$itemIndex, $cart['coupon']);
        }

        $events[] = [
            'name' => 'view_cart',
            'data' => [
                'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
                'value' => $cart['subtotal'],
                'items' => $items,
            ],
        ];
    }

    return $events;
}

function bcse_ga4_checkout_events ()
{
    global $smarty, $config, $cart, $_bcse_ga4_saved_paymentid, $_bcse_ga4_saved_shippingid;

    $events = [];

    if (!empty($cart) && !empty($cart['products'])) {
        $items = [];
        $itemIndex = 0;
        foreach ($cart['products'] as $product) {
            $items[] = bcse_ga4_product_to_item($product, $product['amount'], ++$itemIndex, $cart['coupon']);
        }

        $events[] = [
            'name' => 'begin_checkout',
            'data' => [
                'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
                'value' => $cart['total_cost'],
                'coupon' => $cart['coupon'],
                'items' => $items,
            ],
        ];

        if (!empty($cart['shippingid']) && !empty($cart['delivery']) && $cart['shippingid'] != $_bcse_ga4_saved_shippingid) {
            $_bcse_ga4_saved_shippingid = $cart['shippingid'];
            x_session_save('_bcse_ga4_saved_shippingid');

            $events[] = [
                'name' => 'add_shipping_info',
                'data' => [
                    'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
                    'value' => $cart['total_cost'],
                    'coupon' => $cart['coupon'],
                    'shipping_tier' => $cart['delivery'],
                    'items' => $items,
                ],
            ];
        }

        if (!empty($cart['paymentid']) && $cart['paymentid'] != $_bcse_ga4_saved_paymentid) {
            $_bcse_ga4_saved_paymentid = $cart['paymentid'];
            x_session_save('_bcse_ga4_saved_paymentid');

            $paymentMethods = $smarty->getTemplateVars('payment_methods');

            if (!empty($paymentMethods)) {
                foreach ($paymentMethods as $paymentMethod) {
                    if ($paymentMethod['paymentid'] == $cart['paymentid']) {
                        $events[] = [
                            'name' => 'add_payment_info',
                            'data' => [
                                'coupon' => $cart['coupon'],
                                'payment_type' => $paymentMethod['payment_method'],
                                'items' => $items,
                            ],
                        ];
                    }
                }
            }
        }
    }

    return $events;
}

function bcse_ga4_item_list_event ($eventId, $eventName, $productsVar = 'products')
{
    global $smarty, $config;

    $products = $smarty->getTemplateVars($productsVar);

    if (!$products) {
        return $products;
    }

    $items = [];

    $itemIndex = 1;
    foreach ($products as $product) {
        $items[] = bcse_ga4_product_to_item($product, 1, $itemIndex++);
    }

    return [
        'name' => 'view_item_list',
        'data' => [
            'item_list_id' => $eventId,
            'item_list_name' => $eventName,
            'items' => $items,
        ],
    ];
}

function bcse_ga4_page_vars ()
{
    global $smarty, $config, $cart;

    $main = $smarty->getTemplateVars('main');

    $vars = [
        'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
    ];

    if ($product = $smarty->getTemplateVars('product')) {
        $vars['main_productid'] = $product['productid'];
        $vars['product'] = bcse_ga4_product_to_item($product);
    }

    foreach (['cat_products', 'products', 'f_products'] as $productsVar) {
        if ($products = $smarty->getTemplateVars($productsVar)) {
            if (!isset($vars['products'])) {
                $vars['products'] = [];
            }

            foreach ($products as $product) {
                $vars['products'][$product['productid']] = bcse_ga4_product_to_item($product);
            }
        }
    }

    if (!empty($cart) && !empty($cart['products'])) {
        $vars['cart'] = [
            'subtotal' => $cart['display_subtotal'],
            'shipping' => $cart['shipping_cost'],
            'tax' => $cart['tax_cost'],
            'total' => $cart['total_cost'],
            'coupon' => $cart['coupon'],
            'items' => [],
        ];

        foreach ($cart['products'] as $product) {
            $vars['cart']['items'][$product['cartid']] = bcse_ga4_product_to_item($product, $product['amount']);
        }

        $paymentMethods = $smarty->getTemplateVars('payment_methods');

        if (!empty($paymentMethods)) {
            $vars['paymentMethods'] = [];
            foreach ($paymentMethods as $paymentMethod) {
                $vars['paymentMethods'][$paymentMethod['paymentid']] = $paymentMethod['payment_method'];
            }
        }

        $shippingMethods = $smarty->getTemplateVars('shipping');

        if (!empty($shippingMethods)) {
            $vars['shippingMethods'] = [];
            foreach ($shippingMethods as $shippingMethod) {
                $vars['shippingMethods'][$shippingMethod['shippingid']] = [
                    'name' => $shippingMethod['shipping'],
                    'rate' => $shippingMethod['rate'],
                ];
            }
        }
    }

    if ($main == 'catalog' && ($category = $smarty->getTemplateVars('current_category'))) {
        $vars['item_list_id'] = 'category_' . $category['categoryid'];
        $vars['item_list_name'] = $category['category'];
    }

    if ($main == 'search') {
        $vars['item_list_id'] = 'search';
        $vars['item_list_name'] = 'Search results page';
    }

    return $vars;
}

function bcse_ga4_product_to_item ($product, $quantity =  1, $index = 0, $coupon = '')
{
    global $config;

    return [
        'item_id' => $product['productcode'],
        'item_name' => $product['product'],
        'affiliation' => $config['Company']['company_name'],
        'currency' => $config['Bcse_Ga4']['bcse_ga4_currency'],
        'price' => $product['taxed_price'],
        'quantity' => $quantity,
        'index' => $index,
        'coupon' => $coupon,
    ];
}
