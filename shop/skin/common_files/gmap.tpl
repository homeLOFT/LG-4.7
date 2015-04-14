{*
65594484f49fb956ba72a6895b7caeb3588e89eb, v4 (xcart_4_4_1), 2010-09-01 11:27:00, gmap.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name="gmap"}
<strong>{$description.name}</strong><br />
({if $description.type eq "shipping"}
{$lng.lbl_shipping_address}
{else}
{$lng.lbl_billing_address}
{/if})<br />
{$description.address}<br />
{$lng.lbl_phone}: {$description.phone}
{/capture}
<a href="javascript:void(0);" onclick="javascript:GMap.showModal('{$address|escape:htmlcompat|escape:javascript}','{$smarty.capture.gmap|escape:htmlcompat|escape:javascript}');" class="gmarker{if $show_on_map eq "1"} gmarker-show-on{/if}">{if $show_on_map eq "1"}{$lng.lbl_gmap_show}{/if}</a>
