{*
a05b39e3b339fe4b45124a8bbe72f7d9695310a6, v2 (xcart_4_5_1), 2012-05-29 06:40:05, membership_signup.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
  <td class="data-name">{$lng.lbl_signup_for_membership}</td>
  <td></td>
  <td>
    <select name="pending_membershipid">
      <option value="0">{$lng.lbl_not_member}</option>
      {foreach from=$membership_levels item=v}
        <option value="{$v.membershipid}"{if $userinfo.pending_membershipid eq $v.membershipid} selected="selected"{/if}>{$v.membership}</option>
      {/foreach}
    </select>
  </td>
</tr>
