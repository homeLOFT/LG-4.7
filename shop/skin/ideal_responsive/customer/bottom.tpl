{*
22f6923a9e65090657fef8e74ebb5436d9e861ee, v4 (xcart_4_6_0), 2013-05-23 14:12:07, bottom.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="box">
	<div class="footer-links">
			{include file="customer/help/menu.tpl"}
	</div>
    <div class="copyright">
			{include file="copyright.tpl"}
    </div>
      <div class="prnotice">
        {include file="main/prnotice.tpl"}
      </div>
  {if $active_modules.Users_online}
    {include file="modules/Users_online/menu_users_online.tpl"}
  {/if}
</div>
