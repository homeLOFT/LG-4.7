{*
2a30536f400a253c86be1a18eb00603b4ad45f18, v1 (xcart_4_5_1), 2012-06-22 11:52:57, buy_now.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="customer/buttons/button.tpl" button_title=$lng.lbl_buy_now_img|substitute:"AltImagesDir":$AltImagesDir tips_title=$lng.lbl_buy_now notitle=true additional_button_class=$additional_button_class|cat:' add-to-cart-button'}
