{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, payment_dd.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $payment_cc_data.disable_ccinfo ne "Y"}

  <table cellspacing="0" class="data-table">

    {if $payment_cc_data.c_template ne ""}
      {include file=$payment_cc_data.c_template}
    {else}
      {include file="customer/main/register_ddinfo.tpl"}
    {/if}

  </table>

{else}

  {$lng.disable_chinfo_msg}
  <br />

{/if}
