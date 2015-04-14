{*
99f49a017eeaa96cf5c4060c7785548523d6ad12, v5 (xcart_4_6_2), 2014-01-15 17:46:03, cart_checkout_links.tpl, mixon 
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="cart-checkout-links">
{if $active_modules.Wishlist ne "" or $minicart_total_items gt 0}
<hr class="minicart" />
{/if}
{if $minicart_total_items gt 0}
  <ul>
    <li><a href="cart.php">{$lng.lbl_view_cart}</a></li>
    
    {getvar var='paypal_express_active' func='func_get_paypal_express_active'}
    {if !$paypal_express_active and !$amazon_enabled}
      <li><a href="cart.php?mode=checkout">{$lng.lbl_checkout}</a></li>
    {/if}
  </ul>
{/if}
</div>
