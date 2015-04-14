{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, select_carrier.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<select{if $name} name="{$name}"{/if}{if $id} id="{$id}"{/if}{if $onchange} onchange="{$onchange}"{/if}>
{if $is_ups_carrier_empty ne "Y"}
  <option value="UPS"{if $current_carrier eq "UPS"} selected="selected"{/if}>{$lng.lbl_ups_carrier}</option>
{/if}
{if $is_other_carriers_empty ne "Y"}
  <option value=""{if $current_carrier ne "UPS"} selected="selected"{/if}>{$lng.lbl_other_carriers}</option>
{/if}
</select>
