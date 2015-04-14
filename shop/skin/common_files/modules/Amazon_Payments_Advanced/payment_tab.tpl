{*
ca6d4d775068e2aac0daac39cbaff45582b9cb6b, v1 (xcart_4_6_4), 2014-04-24 17:41:53, payment_tab.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{if $active_modules.Amazon_Payments_Advanced}
  {include file="admin/main/configuration.tpl" configuration=$amazon_pa_configuration option="Amazon_Payments_Advanced"}
{else}
  <br />
  <center>
  <form action="amazon_pa_order.php" method="get">
  <input type="hidden" name="mode" value="amazon_pa_enable_module" />
  <div class="main-button">
    <input type="submit" class="big-main-button configure-style" value="{$lng.lbl_enable} {$lng.module_name_Amazon_Payments_Advanced}" />
  </div>
  <br />
  </form>
  </center>
{/if}

