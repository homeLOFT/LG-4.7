{*
22f6923a9e65090657fef8e74ebb5436d9e861ee, v5 (xcart_4_6_0), 2013-05-23 14:12:07, bottom.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="box">
  <div class="subbox">
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top" style="padding-left: 20px;">
			<div class="bottom-title">{$lng.lbl_help}</div>
		        {include file="customer/help/menu.tpl"}
		</td>

		<td valign="top" align="right" style="padding-right: 20px;">
			{if $active_modules.Socialize}
				{include file="modules/Socialize/footer_links.tpl"}
			{/if}
      {if $active_modules.Klarna_Payments}
        {include file="modules/Klarna_Payments/footer_logo.tpl"}
      {/if}
		        {include file="customer/phones.tpl"}
			
		</td>
	</tr>
	</table>
	<div class="bottom-line"></div>
	<div class="copyright">{include file="copyright.tpl"}</div>
  </div>
</div>
