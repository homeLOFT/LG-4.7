{*
ae358ce250fbd3bbf9959d19e35d695f1a5f8e08, v2 (xcart_4_5_4), 2012-10-24 07:22:03, footer_links.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Socialize and ($config.Socialize.soc_fb_page_url ne "" or $config.Socialize.soc_tw_user_name ne "") and $usertype ne "A"}
  <table cellspacing="0" cellpadding="0">
    <tr>
      {if $config.Socialize.soc_tw_user_name ne ""}
        <td><a href="http://twitter.com/#!/{$config.Socialize.soc_tw_user_name}"><img src="{$AltImagesDir}/custom/twitter.gif" alt="{$lng.lbl_soc_twitter}" /></a></td>
          {/if}
          {if $config.Socialize.soc_fb_page_url ne ""}
        <td><a href="{$config.Socialize.soc_fb_page_url}"><img src="{$AltImagesDir}/custom/facebook.gif" alt="{$lng.lbl_soc_facebook}" /></a></td>
          {/if}
          {if $config.Socialize.soc_pin_username ne ""}
        <td><a href="http://pinterest.com/{$config.Socialize.soc_pin_username}" target="_blank"><img src="{$AltImagesDir}/custom/pinterest.gif" title="{$lng.lbl_soc_pinterest}" alt="{$lng.lbl_soc_pinterest}" /></a></td>
          {/if}
    </tr>
  </table>
{/if}
