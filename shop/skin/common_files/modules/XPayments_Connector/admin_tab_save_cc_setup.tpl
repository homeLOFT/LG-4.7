{*
163da91a21837e50d82e542d62cde62d0cfe7931, v2 (xcart_4_6_4), 2014-06-28 11:37:10, admin_tab_save_cc_setup.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $no_save_cc_processors_avail}
  {$lng.txt_xpc_no_save_cc_processors_avail}
{else}
  {$lng.txt_xpc_saved_cards_admin_note}
  <br /><br />
  <form action="configuration.php?option=XPayments_Connector" method="POST" name="xpc_mapping_rules">
  {include file="modules/XPayments_Connector/admin_options_list.tpl" options_list=$xpc_configuration.save_cc}
  {include file="modules/XPayments_Connector/admin_hidden_options.tpl" skip='save_cc'}
  <br /><br />
  <div class="main-button">
    <input type="submit" value="{$lng.lbl_apply_changes}" class="big-main-button" />
  </div>
  </form>
{/if}
