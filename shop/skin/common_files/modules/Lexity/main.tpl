{*
17375013a1b53bd72f0720a40110576548453c54, v4 (xcart_4_6_4), 2014-03-24 13:46:28, main.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}

{include file="page_title.tpl" title=$lng.lbl_lexity_title}

{capture name=dialog}

<iframe src="{if $is_https_zone}https{else}http{/if}://lexity.com/embed?p={$lexity_partner_code}&h={$lexity_render_hash}&id={$lexity_merchant_id}&e={$lexity_email}&u={$lexity_store_url}" height="800" width="1000" style="border: 0;"></iframe>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_lexity_iframe_title content=$smarty.capture.dialog extra='width="100%"'}
