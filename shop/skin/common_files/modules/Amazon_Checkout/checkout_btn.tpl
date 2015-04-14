{*
bdba23e63d47131e81d453a59ac6edd5b12881bb, v9 (xcart_4_6_3), 2014-02-27 11:55:58, checkout_btn.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{getvar func='func_tpl_is_acheckout_button_enabled'}
{if $func_tpl_is_acheckout_button_enabled}
{if $config.amazon_integration_version eq '2'}
  {getvar var='amazon_checkout_data' func='func_amazon_get_checkout_data'}
  {if not $std_checkout_disabled or $paypal_express_active}
    {if $top_button ne 'Y'}
      <p>{$lng.lbl_or_use}</p>
    {/if}
  {/if}
  <div id="{$abtn_id|default:"cbaButton1"}"/>
  <script>
    $(document).ready(function(){ldelim}
      if (typeof(CBA) === 'object' && typeof(CBA.Widgets.StandardCheckoutWidget) === 'function')
      {ldelim}
        new CBA.Widgets.StandardCheckoutWidget(
          {ldelim}
            merchantId: '{$amazon_checkout_data.merchantId}',
            orderInput: {ldelim}format: "XML", value: "{$amazon_checkout_data.value}"{rdelim}, 
            buttonSettings: {ldelim}size:'large',color:'orange',background:'white'{rdelim}
          {rdelim}).render("{$abtn_id|default:"cbaButton1"}"); 
      {rdelim}
    {rdelim});
  </script>
  </div>
{else}
  <div class="acheckout-cart-buttons">
    <div>
      {if not $std_checkout_disabled or $paypal_express_active}
        {if $top_button ne 'Y'}
          <p>{$lng.lbl_or_use}</p>
        {/if}
      {/if}
        <a href="cart.php?mode=acheckout"><img alt="" src="https://{$amazon_host}/gp/cba/button?color=orange&amp;cartOwnerId={$config.Amazon_Checkout.amazon_mid}&amp;size={if $btn_size eq ''}large{else}{$btn_size}{/if}&amp;background=white" /></a>
    </div>
  </div>
{/if}
{/if}
