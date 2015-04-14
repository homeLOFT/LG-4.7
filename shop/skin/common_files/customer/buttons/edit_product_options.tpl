{*
9623094953dbf5fe49f616e7df2288d19fe500c6, v2 (xcart_4_5_2), 2012-07-10 13:47:10, edit_product_options.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if not $target}
  {assign var="target" value="cart"}
{/if}
{include file="customer/buttons/button.tpl" button_title=$lng.lbl_edit_options href="javascript: popupOpen('popup_poptions.php?target=`$target`&amp;id=`$id`');" style=$style|default:"link" link_href="popup_poptions.php?target=`$target`&amp;id=`$id`" target="_blank" additional_button_class=$additional_button_class}
