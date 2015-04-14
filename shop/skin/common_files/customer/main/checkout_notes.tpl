{*
9faacf0d475a27006f6705032e250c09d04cd25e, v3 (xcart_4_5_3), 2012-08-13 09:07:12, checkout_notes.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="customer/subheader.tpl" title=$lng.txt_notes class="grey"}

<table cellspacing="0" class="data-table" summary="{$lng.lbl_customer_notes|escape}">
  <tr>
    <td class="data-name">{$lng.lbl_customer_notes}:</td>
    <td><textarea cols="70" rows="10" name="Customer_Notes"></textarea></td>
  </tr>

  {if $active_modules.XAffiliate eq "Y" and $partner eq '' and $config.XAffiliate.ask_partnerid_on_checkout eq 'Y'}
    {include file="partner/main/checkout_partner.tpl"}
  {/if}

{if $active_modules.Adv_Mailchimp_Subscription ne ''}
  {include file="modules/Adv_Mailchimp_Subscription/customer/main/mailchimp_checkout_notes.tpl"}
{elseif $active_modules.Mailchimp_Subscription ne ''}
  <tr>
    <td class="data-name"><label for="mailchimp_subscription">{$lng.lbl_mailchimp_subscription}:</label></td>
    <td><input type="checkbox" id="mailchimp_subscription" name="mailchimp_subscription" value="Y" /></td>
  </tr>
{/if}

</table>
