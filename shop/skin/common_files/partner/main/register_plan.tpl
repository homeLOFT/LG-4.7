{*
d6345eaee1778dc91ac4b8928f1589653b9d0bcf, v4 (xcart_4_5_1), 2012-06-11 12:17:49, register_plan.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $plans}

<tr> 
  <td height="20" colspan="3"><b>{$lng.lbl_affiliate_plans}</b><hr size="1" noshade="noshade" /></td>
</tr>

{if $is_admin_user}

  <tr>
    <td align="right">{$lng.lbl_affiliate_plan}</td>
    <td>&nbsp;</td>
    <td nowrap="nowrap">

      <select name="plan_id">
        <option value='0'>{$lng.lbl_none}</option>
        {foreach from=$plans item=v}
          <option value="{$v.plan_id|escape}"{if $userinfo.plan_id eq $v.plan_id} selected="selected"{/if}>{$v.plan_title}</option>
        {/foreach}
      </select>

    </td>
  </tr>

{else}

  <tr>
    <td align="right">{$lng.lbl_affiliate_plan}</td>
    <td>&nbsp;</td>
    <td nowrap="nowrap">
      {if $userinfo.plan_id}
        {foreach from=$plans item=v}
          {if $userinfo.plan_id eq $v.plan_id}
            {$v.plan_title|escape}
          {/if}
        {/foreach}
      {else}
        {$lng.lbl_none}
      {/if}

      <input type="hidden" name="plan_id" value="{$userinfo.plan_id|escape|default:0}" />

    </td>
  </tr>

{/if}

<tr>
  <td align="right">{$lng.lbl_signup_for_partner_plan}</td>
  <td>&nbsp;</td>
  <td nowrap="nowrap">

    <select name="pending_plan_id">
      <option value='0'>{$lng.lbl_none}</option>
      {foreach from=$plans item=v}
        <option value="{$v.plan_id|escape}"{if $userinfo.pending_plan_id eq $v.plan_id} selected="selected"{/if}>{$v.plan_title|escape}</option>
      {/foreach}
    </select>

  </td>
</tr>

{/if}
