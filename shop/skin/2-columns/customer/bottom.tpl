{*
22f6923a9e65090657fef8e74ebb5436d9e861ee, v9 (xcart_4_6_0), 2013-05-23 14:12:07, bottom.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="box">
  <ul class="helpbox">
    <li><a href="help.php?section=contactus&amp;mode=update">{$lng.lbl_contact_us}</a></li>
    {foreach from=$pages_menu item=p}
      {if $p.show_in_menu eq 'Y'}
        <li><a href="pages.php?pageid={$p.pageid}">{$p.title|amp}</a></li>
      {/if}
    {/foreach}
  </ul>

  <div class="subbox">
    {if $active_modules.Klarna_Payments}
      {include file="modules/Klarna_Payments/footer_logo.tpl"}
    {/if}
    <div class="left">{include file="main/prnotice.tpl"}</div>
    <div class="right">{include file="copyright.tpl"}</div>
  </div>
</div>
