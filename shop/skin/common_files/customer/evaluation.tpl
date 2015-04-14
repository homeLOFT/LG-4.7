{*
c870606f3933b2e401836c5dba327b3816c08bc5, v9 (xcart_4_6_1), 2013-08-15 12:55:02, evaluation.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{*Synchronize changes with shop_closed_evaluation.html*}
{if $shop_evaluation eq 'EVALUATION' and $show_evaluation_notice}
<div class="evaluation-notice">
  <p class="evaluation-notice-title">{$lng.txt_evaluation_notice_title|substitute:'XC_Version':$shop_type}</p>
  <p>{$lng.txt_evaluation_notice}</p>
  <div class="evaluation-notice-button">
    {include file='customer/buttons/button.tpl' button_title=$lng.lbl_purchase_license href='http://www.x-cart.com/buy.html?utm_source=xcart&amp;utm_medium=licence_message_customer_link&amp;utm_campaign=licence_message_customer' target='_blank'}
    <div class="clearing"></div>
  </div>
  <p class="license-warning">{$lng.txt_evaluation_notice_warning}</p>
</div>
{/if}
