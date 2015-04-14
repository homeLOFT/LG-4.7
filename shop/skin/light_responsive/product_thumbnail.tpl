{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, product_thumbnail.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{strip}
<img
{if $id ne ''} id="{$id}"{/if} src="
{if $tmbn_url}
{$tmbn_url|amp}
{else}
{if $full_url}
{$current_location}
{else}
{$xcart_web_dir}
{/if}
/image.php?type={$type|default:"T"}&amp;id={$productid}
{/if}
"
alt="{$product|escape}" title="{$product|escape}" />
{/strip}
