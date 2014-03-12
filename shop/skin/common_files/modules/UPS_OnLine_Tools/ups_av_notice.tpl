{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, ups_av_notice.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $postoffice eq ""}

  <table summary="{$lng.lbl_ups_online_tools|escape}">
    <tr>
      <td class="center">{include file="modules/UPS_OnLine_Tools/ups_logo.tpl"}</td>
      <td class="center small-note">
        {$lng.txt_ups_av_notice}<br />
        <br />
        {$lng.txt_ups_trademark_text}
      </td>
    </tr>
  </table>

{else}

  <p class="text-block">
    <strong>{$lng.txt_note}:</strong> {$lng.txt_ups_av_for_customers_only}
  </p>
  <p class="center small-note">{$lng.txt_ups_av_notice2}</p>

{/if}
