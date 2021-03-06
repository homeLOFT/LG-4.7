{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, currency_box.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $is_int eq "Y"}
{assign var="value" value=$value|string_format:"%d"}
{else}
{assign var="value" value=$value|default:0|formatprice}
{/if}

{capture name="currency_input_box"}
<td>
  <input type="text" name="{$box_name}"{if $box_id ne ""} id="{$box_id}"{/if} size="8" value="{$value}"{if $is_disabled} disabled="disabled"{/if} />
</td>
{/capture}

{if $curr_symbol eq ""}
{assign var="curr_symbol" value=$config.General.currency_symbol}
{/if}

{assign var="currency_box" value="<td"}

{if $curr_id ne ""}
{assign var="currency_box" value=$currency_box|cat:" id='`$curr_id`'"}
{/if}

{assign var="currency_box" value=$currency_box|cat:">`$curr_symbol`</td>"}

<td nowrap="nowrap">{$label}</td>
{$config.General.currency_format|replace:"$":$currency_box|replace:"x":$smarty.capture.currency_input_box}

{if $extra ne ""}
<td {$extra}>&nbsp;</td>
{/if}
