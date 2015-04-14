{*
48ce763c41afc592c58ff57c35ce90c1b45d157a, v1 (xcart_4_5_1), 2012-05-25 07:23:46, buy_more.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="customer/buttons/button.tpl" button_title=$lng.lbl_buy_more_img|substitute:"AltImagesDir":$AltImagesDir tips_title=$lng.lbl_add_more notitle=true additional_button_class=$additional_button_class|cat:' add-to-cart-button'}
