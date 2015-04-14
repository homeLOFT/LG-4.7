{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, profile_data.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<table class="block-grid data-table">

  <tr>
    <td colspan="2" class="section">
      <b>{$lng.lbl_account_information}</b>
    </td>
  </tr>

  {if $config.email_as_login ne 'Y'}
    <tr>
      <td class="name"><tt>{$lng.lbl_username}:</tt></td>
      <td class="value"><tt>{$userinfo.login}</tt></td>
    </tr>
  {/if}

  <tr>
    <td class="name"><tt>{$lng.lbl_email}:</tt></td>
    <td class="value"><tt>{$userinfo.email}</tt></td>
  </tr>

  {if $password_reset_key}
    <tr>
      <td class="name"><tt>{$lng.lbl_password_reset_url}:</tt></td>
      <td class="value"><tt><a href="{if $config.Security.use_https_login eq 'Y'}{$https_location}{else}{$http_location}{/if}{if $userpath ne ''}{$userpath}{/if}/change_password.php?password_reset_key={$password_reset_key}&amp;user={$userinfo.id}">{if $config.Security.use_https_login eq 'Y'}{$https_location}{else}{$http_location}{/if}{if $userpath ne ''}{$userpath}{/if}/change_password.php?password_reset_key={$password_reset_key}&user={$userinfo.id}</a></tt></td>
    </tr>
  {/if}
 
  <tr>
    <td colspan="2" class="section"><b>{$lng.lbl_personal_information}</b></td>
  </tr>

  {if $userinfo.default_fields.title}
    <tr>
      <td class="name"><tt>{$lng.lbl_title}:</tt></td>
      <td class="value"><tt>{$userinfo.title|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.default_fields.firstname}
    <tr>
      <td class="name"><tt>{$lng.lbl_first_name}:</tt></td>
      <td class="value"><tt>{$userinfo.firstname|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.default_fields.lastname}
    <tr>
      <td class="name"><tt>{$lng.lbl_last_name}:</tt></td>
      <td class="value"><tt>{$userinfo.lastname|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.default_fields.company}
    <tr> 
      <td class="name"><tt>{$lng.lbl_company}:</tt></td>
      <td class="value"><tt>{$userinfo.company|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.default_fields.ssn}
    <tr>
      <td class="name"><tt>{$lng.lbl_ssn}:</tt></td>
      <td class="value"><tt>{$userinfo.ssn|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.default_fields.tax_number}
    <tr> 
      <td class="name"><tt>{$lng.lbl_tax_number}:</tt></td>
      <td class="value"><tt>{$userinfo.tax_number|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.membership}
    <tr> 
      <td class="name"><tt>{$lng.lbl_membership}:</tt></td>
      <td class="value"><tt>{$userinfo.membership|default:'-'}</tt></td>
    </tr>
  {/if}

  {if $userinfo.pending_membership ne $userinfo.membership}
    <tr> 
      <td class="name"><tt>{$lng.lbl_signup_for_membership}:</tt></td>
      <td class="value"><tt>{$userinfo.pending_membership|default:'-'}</tt></td>
    </tr>
  {/if}
  
  {foreach from=$userinfo.additional_fields item=v}
    {if $v.section eq 'P'}
      <tr>
        <td class="name"><tt>{$v.title}:</tt></td>
        <td class="value"><tt>{$v.value|default:'-'}</tt></td>
      </tr>
    {/if}
  {/foreach}

  {if $userinfo.field_sections.A}
    <tr>
      <td colspan="2" class="section"><b>{$lng.lbl_additional_information}</b></td>
    </tr>

    {foreach from=$userinfo.additional_fields item=v}
      {if $v.section eq 'A' or $v.section eq 'C'}
        <tr>
          <td class="name"><tt>{$v.title}:</tt></td>
          <td class="value"><tt>{$v.value}</tt></td>
        </tr>
      {/if}
    {/foreach}
  {/if}

    <tr>
      <td colspan="2" class="section"></td>
    </tr>

</table>
