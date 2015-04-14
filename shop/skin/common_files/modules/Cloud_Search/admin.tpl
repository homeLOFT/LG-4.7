{*
5f3b084d555f5521e1fc0b90bcf4c774274dc258, v1 (xcart_4_6_0), 2013-05-28 11:01:49, admin.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $usertype eq 'A' and $smarty.get.option eq 'Cloud_Search'}

<script type="text/javascript" src="//cloudsearch.x-cart.com/static/cloud_search_xcart_admin.js">
</script>

<link rel="stylesheet" type="text/css" href="{$SkinDir}/modules/Cloud_Search/admin.css" />

{/if}
