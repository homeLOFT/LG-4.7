{*
e6c9ab1e8b9c6c9cd78718d74740f680184ba36c, v1 (xcart_4_5_3), 2012-08-03 07:47:34, create_order.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.lbl_location_create_order}

<a name="CreateOrder"></a>
<br />

{$lng.txt_create_order_info}

<br />
<script type="text/javascript" src="{$SkinDir}/js/popup_users_open.js"></script>

<form name="createorderform" method="get" action="create_order.php">
  <input type="hidden" name="userids" value="" />
  <input type="hidden" name="mode" value="create" />
  <br />
  {$lng.lbl_customer}: <input class="create-order-customer" size="64" name="users" value="{$lng.lbl_not_selected_new_cust}" disabled="disabled" />
  <input type="button" value="{$lng.lbl_select_customer|strip_tags:false|escape}" onclick="javascript: open_popup_users('createorderform', '~~email~~ (~~firstname~~ ~~lastname~~)', false, true);" />
  <br />
  <br />
  <input type="submit" value="{$lng.lbl_create_order|escape}" />
</form>
