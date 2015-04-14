{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, currencies.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $pm_currencies}
  <tr>
    <td>{$lng.lbl_cc_currency}:</td>
    <td>
      <select name="{$param_name}">
        {foreach from=$pm_currencies item=c key=code}
          <option value="{$code}"{if $current eq $code} selected="selected"{/if}>{$c} ({$code})</option>
        {/foreach}
      </select>
    </td>
  </tr>
{/if}
