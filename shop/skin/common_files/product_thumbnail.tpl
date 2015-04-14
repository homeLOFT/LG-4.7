{*
e756c491ca9c6f5e4d5f6a7f8d0dc247655660da, v2 (xcart_4_5_3), 2012-08-03 10:21:40, product_thumbnail.tpl, aim
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
{if $image_x ne 0} width="{$image_x}"{/if}
{if $image_y ne 0} height="{$image_y}"{/if} alt="{$product|escape}" title="{$product|escape}" />
{/strip}
