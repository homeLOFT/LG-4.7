{*
5f3b084d555f5521e1fc0b90bcf4c774274dc258, v1 (xcart_4_6_0), 2013-05-28 11:01:49, reminder.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $cloud_search_reminder}

  {capture name=dialog}
    {$cloud_search_reminder}
  {/capture}

  {include file="location.tpl" location="" alt_content=$smarty.capture.dialog extra='width="100%"' newid="cloud-search-dialog-message" alt_type="I"}

{/if}
