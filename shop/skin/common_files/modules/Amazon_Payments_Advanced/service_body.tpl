{*
2177942f6502972343136b91d2371dfe7971ebb0, v2 (xcart_4_6_5), 2014-09-05 17:49:07, service_body.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $amazon_pa_enabled}
  {if $config.Amazon_Payments_Advanced.amazon_pa_mode eq 'live'}
    {assign var="wdt_url" value="https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js"}
    {if $config.Amazon_Payments_Advanced.amazon_pa_currency eq 'EUR'}
      {assign var="wdt_url" value="https://static-eu.payments-amazon.com/OffAmazonPayments/de/js/Widgets.js"}
    {elseif $config.Amazon_Payments_Advanced.amazon_pa_currency eq 'GBP'}
      {assign var="wdt_url" value="https://static-eu.payments-amazon.com/OffAmazonPayments/uk/js/Widgets.js"}
    {/if}
  {else}
    {assign var="wdt_url" value="https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js"}
    {if $config.Amazon_Payments_Advanced.amazon_pa_currency eq 'EUR'}
      {assign var="wdt_url" value="https://static-eu.payments-amazon.com/OffAmazonPayments/de/sandbox/js/Widgets.js"}
    {elseif $config.Amazon_Payments_Advanced.amazon_pa_currency eq 'GBP'}
      {assign var="wdt_url" value="https://static-eu.payments-amazon.com/OffAmazonPayments/uk/sandbox/js/Widgets.js"}
    {/if}
  {/if}
  <script type='text/javascript' src='{$wdt_url}?sellerId={$config.Amazon_Payments_Advanced.amazon_pa_sid}'></script>
{/if}

{load_defer file="modules/Amazon_Payments_Advanced/func.js" type="js"}

<script type="text/javascript">
//<![CDATA[
var AMAZON_PA_CONST = {ldelim}
  SID: '{$config.Amazon_Payments_Advanced.amazon_pa_sid}',
  MODE: '{$config.Amazon_Payments_Advanced.amazon_pa_mode}'
{rdelim}
//]]>
</script>

