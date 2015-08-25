{*
a2390cf5ce919db6dad78b3cf3209ce1e24d6abe, v1 (xcart_4_6_6), 2014-11-14 19:16:52, ga_code_universal.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<!-- Google Analytics -->
<script type="text/javascript">
//<![CDATA[
{literal}
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
{/literal}
ga('create', '{$config.Google_Analytics.ganalytics_code}', 'auto');
ga('send', 'pageview');

{if 
  $config.Google_Analytics.ganalytics_e_commerce_analysis eq "Y" 
  and $ga_track_commerce eq "Y" 
  and $main eq "order_message"
  and $orders
}
  ga('require', 'ecommerce');

  // Ecommerce Tracking for order_message page
  {foreach from=$orders item="order"}
    ga('ecommerce:addTransaction', {ldelim}
      'id'          : "{$order.order.orderid}",           // order ID - required
      'affiliation' : "{$partner|default:'Main stock'}",  // affiliation or store name
      'revenue'     : "{$order.order.total}",          // total - required
      'shipping'    : "{$order.order.shipping_cost}",  // shipping
      'tax'         : "{$order.order.tax}"    // tax
    {rdelim});

    {foreach from=$order.products item="product"}
      ga('ecommerce:addItem', {ldelim}
        'id'        : "{$order.order.orderid}",           // order ID - required
        'sku'       : "{$product.productcode|wm_remove|escape:javascript}", // SKU/code - required
        'name'      : "{$product.product|wm_remove|escape:javascript}{if $active_modules.Product_Options ne "" and $product.product_options_txt} ({$product.product_options_txt|replace:"\n":", "|wm_remove|escape:javascript}){/if}", // product name
        'category'  : "{$product.category|default:'Unknown category'}", // category or variation
        'price'     : "{$product.price}",          // unit price - required
        'quantity'  : "{$product.amount}"          // quantity - required
      {rdelim});
    {/foreach}

  {/foreach}
  ga('ecommerce:send'); // Send transaction and item data to Google Analytics.
{/if}

//]]>
</script>
<!-- End Google Analytics -->
