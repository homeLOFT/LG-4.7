{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, affiliate_search_manufacturer.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

{if $manufacturers}

  {capture name=dialog}
  <ul class="xaff-categories">
    {foreach from=$manufacturers item=m}
      <li><a href="partner_banners.php?bannerid={$banner.bannerid}&amp;get=1&amp;manufacturerid={$m.manufacturerid}">{$m.manufacturer}</a></li>
    {/foreach}
  </ul>
  {/capture}
  {include file="dialog.tpl" content=$smarty.capture.dialog title=$lng.lbl_manufacturers extra='width="100%"'}

{/if}
