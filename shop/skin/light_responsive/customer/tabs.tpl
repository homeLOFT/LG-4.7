{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, tabs.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $speed_bar}

  {capture name=menu}
    {section name=tabs loop=$speed_bar step=-1}
      <li{if $speed_bar[tabs].current} class="current"{/if}><a href="{$speed_bar[tabs].link|amp}">{$speed_bar[tabs].title}</a></li>
    {/section}
  {/capture}

  {if $mode eq 'plain_list'}

    <ul>
      {$smarty.capture.menu}
    </ul>

  {else}

    <div class="navbar">
      <ul class="nav navbar-nav">
        {$smarty.capture.menu}
      </ul>
      <div class="clearing"></div>
    </div>

  {/if}

{/if}
