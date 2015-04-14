{*
5d60b84b8ae181c9bbff34c0d4da94e3cb55901d, v7 (xcart_4_5_5), 2012-11-27 11:59:43, printable_link.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $printable_link_visible}
  <div class="printable-bar">
    <a href="{$php_url.url|escape}?printable=Y{if $php_url.query_string ne ''}&amp;{$php_url.query_string|escape}{/if}">{$lng.lbl_printable_version}</a>
  </div>
{/if}
