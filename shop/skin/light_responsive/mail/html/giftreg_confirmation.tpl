{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, giftreg_confirmation.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" salutation=$recipient_data.recipient_name}</h1>

{$lng.eml_giftreg_confirmation_msg|substitute:"sender":"`$userinfo.title` `$userinfo.firstname` `$userinfo.lastname`"}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{capture name="row"}
{$lng.lbl_event}: <b>{$event_data.title}</b>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{capture name="block1"}
<table class="tiny-button radius skinned">
  <tr>
    <td>
      <a href="{$http_customer_location}/giftregs.php?cc={$confirmation_code}">{$lng.eml_giftreg_click_to_confirm}</a>
    </td>
  </tr>
</table>
{/capture}

{capture name="block2"}
<table class="tiny-button radius secondary">
  <tr>
    <td>
      <a href="{$http_customer_location}/giftregs.php?cc={$decline_code}">{$lng.eml_giftreg_click_to_decline}</a>
    </td>
  </tr>
</table>
{/capture}

{include file="mail/html/responsive_row.tpl" block1=$smarty.capture.block1 block2=$smarty.capture.block2}

{include file="mail/html/signature.tpl"}
