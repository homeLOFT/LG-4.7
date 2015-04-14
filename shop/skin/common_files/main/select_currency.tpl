{*
b76a3b5183f04988d9a5b33e30cd04d794b845f7, v2 (xcart_4_4_5), 2011-12-22 06:24:37, select_currency.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:
*}
<select name="{if $name ne ""}{$name}{else}selected_currency{/if}"{if $id} id="{$id}"{/if}{if $onchange} onchange="{$onchange}"{/if}>
  {foreach from=$currencies item=currency}
  <option value="{if $use_curr_int_code eq "Y"}{$currency.code_int}{else}{$currency.code}{/if}"{if $current_currency eq $currency.code} selected="selected"{/if}>({$currency.code}) {$currency.name}</option>
  {/foreach}
</select>
