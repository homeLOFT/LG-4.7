{*
ba13c258746d2b6726f772cad5f8c7d607e2edd5, v6 (xcart_4_7_0), 2015-02-27 15:55:06, service_body_js.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

{if $amazon_enabled}
    <script type="text/javascript" src="{$amazon_widget_url}"></script>
{/if}

{if $active_modules.Amazon_Payments_Advanced}
  {include file="modules/Amazon_Payments_Advanced/service_body.tpl"}
{/if}

