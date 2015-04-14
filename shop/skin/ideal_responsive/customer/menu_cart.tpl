{*
8ec8b858917c921a726e2197071ce74d020e5ead, v1 (xcart_4_6_0), 2013-04-02 16:43:59, menu_cart.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.General.ajax_add2cart eq 'Y' and $main ne 'cart' and $main ne 'checkout'}
  {include file="customer/ajax.minicart.tpl" _include_once=1}
{/if}

{capture name=menu}

{include file="customer/minicart_total.tpl"}


{/capture}
{if $config.General.ajax_add2cart eq 'Y' and $main ne 'cart' and $main ne 'checkout' and $minicart_total_items gt 0}
  {assign var=additional_class value="menu-minicart ajax-minicart"}
{else}
  {assign var=additional_class value="menu-minicart"}
{/if}
{if $minicart_total_items gt 0}
  {assign var=additional_class value="`$additional_class` full-mini-cart"}
{/if}
{include file="customer/menu_dialog.tpl" title=$lng.lbl_your_cart content=$smarty.capture.menu minicart=true}
