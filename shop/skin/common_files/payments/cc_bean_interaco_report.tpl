{*
2e4457b15a0a26fb318a798b150f6fe2045ad0c0, v1 (xcart_4_4_5), 2011-12-23 12:52:28, cc_bean_interaco_report.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:
*}

{if $plainText eq "Y"}

{$lng.lbl_cc_beani_trn_result_title}
---------------------------------------------------

{$lng.lbl_cc_beani_trnid}: {$order.extra.interaco.interaco_trnId}
{$lng.lbl_cc_beani_ioinstname}: {$order.extra.interaco.interaco_ioInstName}
{$lng.lbl_cc_beani_ioconfcode}: {$order.extra.interaco.interaco_ioConfCode}
{$lng.lbl_cc_beani_authcode}: {$order.extra.interaco.interaco_authCode}
{$lng.lbl_cc_beani_messageid}: {$order.extra.interaco.interaco_messageId}
{$lng.lbl_cc_beani_message}: {$order.extra.interaco.interaco_messageText}

{else}

<br /><br />
<strong>{$lng.lbl_cc_beani_trn_result_title}</strong>
<table style="margin-left: 15px; margin-top: 5px;">
  <tr>
    <td>{$lng.lbl_cc_beani_trnid}:</td>
    <td>{$order.extra.interaco.interaco_trnId}</td>
  </tr>
  <tr>
    <td>{$lng.lbl_cc_beani_ioinstname}:</td>
    <td>{$order.extra.interaco.interaco_ioInstName}</td>
  </tr>
  <tr>
    <td>{$lng.lbl_cc_beani_ioconfcode}:</td>
    <td>{$order.extra.interaco.interaco_ioConfCode}</td>
  </tr>
  <tr>
    <td>{$lng.lbl_cc_beani_authcode}:</td>
    <td>{$order.extra.interaco.interaco_authCode}</td>
  </tr>
  <tr>
    <td>{$lng.lbl_cc_beani_messageid}:</td>
    <td>{$order.extra.interaco.interaco_messageId}</td>
  </tr>
  <tr>
    <td>{$lng.lbl_cc_beani_message}:</td>
    <td>{$order.extra.interaco.interaco_messageText}</td>
  </tr>
</table>

{/if}
