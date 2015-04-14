{*
75eb1e48064361a5b25ba08e2509c5cb97322282, v4 (xcart_4_6_2), 2013-10-04 08:15:08, info_panel.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $view_info_panel eq "Y"}
<div id="eucl_panel">
  <div id="eucl_panel_msg">{$lng.txt_eu_cookie_law_panel_msg}&nbsp;</div>
  <div id="eucl_panel_btn">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_eucl_change_settings tips_title=$lng.lbl_eucl_change_settings href="javascript: return func_change_cookie_settings();" additional_button_class="light-button"}
    &nbsp;&nbsp;&nbsp;&nbsp;
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_close tips_title=$lng.lbl_close href="javascript: return func_down_eucl_panel();" additional_button_class="light-button"}
    <div id="eucl_panel_countdown">&nbsp;</div>
  </div>
  <div class="clearing"></div>
</div>
{/if}
