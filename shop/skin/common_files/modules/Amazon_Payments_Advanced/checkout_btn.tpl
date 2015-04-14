{*
ef6c41a6557f5a31d47852d99a30c9a778c71a5d, v3 (xcart_4_7_0), 2014-12-02 17:28:59, checkout_btn.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $amazon_pa_enabled}
<div>
    {if not $std_checkout_disabled or $paypal_express_active}
      <p>{$lng.lbl_or_use}</p>
    {/if}
    <div id="payWithAmazonDiv_{$btn_place}">
      {assign var="btn_host" value="amazon.com"}
      {if $config.Amazon_Payments_Advanced.amazon_pa_currency eq 'EUR'}
        {assign var="btn_host" value="amazon.de"}
      {elseif $config.Amazon_Payments_Advanced.amazon_pa_currency eq 'GBP'}
        {assign var="btn_host" value="amazon.co.uk"}
      {/if}
      <img src="https://{if $config.Amazon_Payments_Advanced.amazon_pa_mode eq 'live'}payments{else}payments-sandbox{/if}.{$btn_host}/gp/widgets/button?sellerId={$config.Amazon_Payments_Advanced.amazon_pa_sid}&size=large&color=orange" style="cursor: pointer;" />
    </div>
    <script type="text/javascript">
    //<![CDATA[
    if (typeof func_amazon_pa_put_button != 'undefined') {ldelim}
      func_amazon_pa_put_button('payWithAmazonDiv_{$btn_place}');
    {rdelim}
    //]]>
    </script>
</div>
{/if}

