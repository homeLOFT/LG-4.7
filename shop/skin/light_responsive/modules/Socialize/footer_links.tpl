{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, footer_links.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

{if $active_modules.Socialize and ($config.Socialize.soc_fb_page_url ne "" or $config.Socialize.soc_tw_user_name ne "") and $usertype eq "C"}
  <ul class="soc-footer-links">
    {if $config.Socialize.soc_fb_page_url ne ""}
      <li><a href="{$config.Socialize.soc_fb_page_url}" target="_blank"><span class="fa fa-facebook-square"></span></a></li>
    {/if}
    {if $config.Socialize.soc_tw_user_name ne ""}
      <li><a href="http://twitter.com/#!/{$config.Socialize.soc_tw_user_name}" target="_blank"><span class="fa fa-twitter-square"></span></a></li>
    {/if}
    {if $config.Socialize.soc_pin_username ne ""}
      <li><a href="http://pinterest.com/{$config.Socialize.soc_pin_username}" target="_blank"><span class="fa fa-pinterest-square"></span></a></li>
    {/if}
  </ul>
{/if}
