{*
155446750e3e6b7cd3a5762fcd3a2d4b4ccef150, v4 (xcart_4_6_5), 2014-09-24 08:39:41, admin_tab_payment_methods.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $no_active_payment_methods}
<div class="xpc-no-active-pm-warning">
  <span><img src="{$ImagesDir}/icon_warning.gif" alt="" /></span>{$lng.txt_xpc_no_active_payment_methods}<span></span>
</div>
<br />
{/if}

<form action="xpc_admin.php?mode=update_payment_methods" method="POST">

<table cellpadding="5" cellspacing="1" border="0">

  <tr class="TableHead">
    <td>{$lng.lbl_active}</td>
    <td>{$lng.lbl_payment_method}</td>
    <td>{$lng.lbl_xpc_cc_currency}</td>
    <td>{$lng.lbl_xpc_use_recharges}</td>
  </tr>

  {foreach from=$cc_processors item=pm}
  <tr{cycle values=', class="TableSubHead"'}>
    <td>
      <input type="checkbox" name="active[]" value="{$pm.paymentid}" {if $pm.active eq "Y"}checked="checked"{/if}/>
    </td>
    <td>{$pm.module_name}</td>
    <td>{if $pm.currency}{$pm.currency_name} ({$pm.currency}){else}{$lng.lbl_unknown}{/if}</td>
    <td>
      {if $pm.can_recharge}
        <input type="checkbox" name="use_recharges[]" value="{$pm.paymentid}" {if $pm.use_recharges eq "Y"}checked="checked"{/if}/>
      {else}
        {$lng.txt_not_available}
      {/if}
    </td>
    {if $pm.paypal_error}
      <td class="xpc-payment-error-cell">
        {include file="main/tooltip_js.tpl" text=$lng.txt_xpc_paypal_dp_note type="img" alt_image="icon_warning_small.gif"}
      </td>
    {/if}
  </tr>
  {/foreach}

</table>

<input type="submit" value="{$lng.lbl_update}">

</form>

<br />
<br />

{$lng.txt_xpc_pm_config_note_2|substitute:'url':$xp_backend_url}

<br />
<br />

{include file="main/subheader.tpl" title=$lng.lbl_xpc_import_payment_methods class="black"}

{$lng.txt_xpc_import_payment_methods_warn}

<br />
<br />

<input type="button" name="import_payment_methods" value="{$lng.lbl_xpc_request_payment_methods|strip_tags:false|escape}" onclick="javascript: self.location='xpc_admin.php?mode=import_payment_methods';" />
