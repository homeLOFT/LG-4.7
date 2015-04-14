{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, phones.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="phones">

  {if $config.Company.company_phone}
    <span class="first">{$lng.lbl_phone_1_title}: {$config.Company.company_phone}</span>
  {/if}

  {if $config.Company.company_phone_2}
    <span class="last">{$lng.lbl_phone_2_title}: {$config.Company.company_phone_2}</span>
  {/if}

</div>

