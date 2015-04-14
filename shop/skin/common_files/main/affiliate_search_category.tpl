{*
354ddee0da1a1cedb4c4489229ed956d8c5b0c22, v2 (xcart_4_4_4), 2011-08-29 10:02:16, affiliate_search_category.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{if $categories}

  <script type="text/javascript" src="{$SkinDir}/js/affiliate_categories.js"></script>

  {capture name=dialog}

    {include file="main/affiliate_categories.tpl" level=0}

  {/capture}
  {include file="dialog.tpl" content=$smarty.capture.dialog title=$lng.lbl_categories extra='width="100%"'}

  <br />
{/if}
