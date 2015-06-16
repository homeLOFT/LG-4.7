{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}
{if $mc_newslists ne ''}
  {if $hide_header eq ''}
    <tr>
      <td class="register-section-title" colspan="3">
        <div>
          <label>{$lng.lbl_newsletter}</label>
        </div>
      </td>
    </tr>
  {/if}
  <tr>
    <td colspan="3">{$lng.lbl_newsletter_signup_text}</td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
    <td>
      {foreach from=$mc_newslists item=n}
        {assign var="mc_list_id" value=$n.mc_list_id}
        <div class="news-register-item">
          <input type="checkbox" id="mailchimp_subscription_{$n.mc_list_id}" name="mailchimp_subscription[{$n.mc_list_id}]"{if $mailchimp_subscription[$mc_list_id] ne ""} checked="checked"{/if} />
          <label for="mailchimp_subscription_{$n.mc_list_id}">{$n.name}</label>
          <br />
        </div>
      {/foreach}
    </td>
  </tr>
{/if}
