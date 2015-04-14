{*
f2a6bb8e9f03427774bebc311ec0f0acf6ef942f, v3 (xcart_4_4_2), 2010-10-21 13:48:30, pages.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$page_data.title|amp}</h1>

{capture name=dialog}

  {if $page_content ne ''}

    {if $config.General.parse_smarty_tags eq "Y"}
      {eval var=$page_content}
    {else}
      {$page_content|amp}
    {/if}

  {/if}

{/capture}
{include file="customer/dialog.tpl" title=$page_data.title content=$smarty.capture.dialog noborder=true}
