{*
360ada72394a9e7807de2899991006cc0634afe8, v2 (xcart_4_4_0_beta_2), 2010-06-21 13:19:48, customer_manufacturers_list.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_manufacturers}</h1>

{capture name=dialog}

  {include file="customer/main/navigation.tpl"}

  <ul class="manufacturers-list list-item">
    {foreach from=$manufacturers item=v}
      <li><a href="manufacturers.php?manufacturerid={$v.manufacturerid|amp}">{$v.manufacturer|escape}</a></li>
    {/foreach}
  </ul>

  {include file="customer/main/navigation.tpl"}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_manufacturers content=$smarty.capture.dialog noborder=true}
