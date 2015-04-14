{*
5826ca2935bb2e70bc02b72172263662bae4fde0, v3 (xcart_4_4_5), 2011-11-17 13:12:10, general.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

<p>{$lng.txt_help_zone_title}</p>

{include file="customer/buttons/button.tpl" button_title=$lng.lbl_recover_password href="help.php?section=Password_Recovery" style="link"}

{if $usertype ne 'A'}
{include file="customer/buttons/button.tpl" button_title=$lng.lbl_contact_us href="help.php?section=contactus&mode=update" style="link"}
{/if}
