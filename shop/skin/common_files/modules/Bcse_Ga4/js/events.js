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
(function ($) {
  'use strict';

  var ga4Data;

  function pageLoadEvents () {
    var $script = $('#bcse-ga4-data');

    if ($script.length) {
      ga4Data = JSON.parse($script.text());

      $script.remove();

      if (ga4Data.events) {
        ga4Data.events.forEach(function (ga4Event) {
          gtag('event', ga4Event.name, ga4Event.data);
        });
      }

      if (ga4Data.ecommerce) {
        $(ajax.messages).on('cartChanged', function(e, data) {
          return cartChanged(data);
        });

        $(document).on('click', '.item-box a', function (e) {
          var $content = $(this).closest('.item-box');

          var productid = false;
          $content.find('script').each(function () {
            var match = /products_data\[(.*)\]/mi.exec($(this).text())
            if (match && !productid) {
              productid = match[1];
            }
          });

          if (productid) {
            selectItem(productid)
          }
        });

        if (ga4Data.checkout_module == 'One_Page_Checkout') {
          $(document).on('click', 'input[name="shippingid"]', function () {
            var id = parseInt($('input[name="shippingid"]:checked').val());
            if (id) {
              selectShipping(id);
            }
          });

          $(document).on('click', 'input[name="paymentid"]', function () {
            var id = parseInt($('input[name="paymentid"]:checked').val());
            if (id) {
              selectPayment(id);
            }
          });
        }
      }
    }
  }

  function getVar (name) {
    if (ga4Data && ga4Data.vars) {
      return ga4Data.vars[name] || false;
    }

    return null;
  }

  function cartChanged (data) {
    if (data.changes) {
      Object.keys(data.changes).forEach(function (index) {
        var change = data.changes[index];

        if (change.changed > 0) {
          addToCart(change.productid, change.changed);
        }

        if (change.changed < 0) {
          removeFromCart(change.productid, Math.abs(change.changed));
        }
      });
    }
  }

  function addToCart (productid, quantity) {
    var product = getProductById(productid);
    var currency = getVar('currency');

    if (product) {
      gtag('event', 'add_to_cart', {
        currency: currency,
        value: quantity * product.price,
        items: [Object.assign(product, {
          quantity: quantity
        })]
      });
    }
  }

  function removeFromCart (productid, quantity) {
    var product = getProductById(productid);
    var currency = getVar('currency');

    if (product) {
      gtag('event', 'remove_from_cart', {
        currency: currency,
        value: quantity * product.price,
        items: [Object.assign(product, {
          quantity: quantity
        })]
      });
    }
  }

  function selectItem (productid) {
    var product = getProductById(productid);

    if (product) {
      var id = getVar('item_list_id'),
        name = getVar('item_list_name');

      gtag('event', 'select_item', {
        item_list_id: id,
        item_list_name: name,
        items: [product]
      });
    }
  }

  function selectShipping (shippingId) {
    var cart = getVar('cart'),
      shippingMethods = getVar('shippingMethods'),
      currency = getVar('currency');

    if (shippingMethods && shippingMethods[shippingId]) {
      var shipping = shippingMethods[shippingId];
      gtag('event', 'add_shipping_info', {
        currency: currency,
        value: cart.total,
        coupon: cart.coupon,
        shipping_tier: shipping.name,
        items: Object.values(cart.items)
      });
    }
  }

  function selectPayment (paymentId) {
    var cart = getVar('cart'),
      paymentMethods = getVar('paymentMethods');

    if (paymentMethods && paymentMethods[paymentId]) {
      var payment = paymentMethods[paymentId];
      gtag('event', 'add_payment_info', {
        coupon: cart.coupon,
        payment_type: payment,
        items: Object.values(cart.items)
      });
    }
  }

  function getProductById (productid) {
    var main_productid = getVar('main_productid');
    var product = getVar('product');
    var products = getVar('products');

    if (main_productid == productid && product) {
      return product;
    }

    if (products) {
      var found = false;

      Object.keys(products).forEach(function (pid) {
        if (parseInt(productid) == parseInt(pid)) {
          found = products[pid];
        }
      });

      if (found) {
        return found;
      }
    }

    return false;
  }

  $(document).ready(pageLoadEvents);
})(jQuery);
