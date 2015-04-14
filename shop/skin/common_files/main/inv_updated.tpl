{*
c7511200e002766d62dd481aef6b95fa4a84cd03, v2 (xcart_4_4_0), 2010-07-19 13:30:01, inv_updated.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
{if $updated_items gt 0}
  {$lng.txt_inv_updated}
  <br />
{/if}
{if $err_rows}
  <font class="Star">{$lng.txt_inv_invalid_format}</font>
  <br />
  {foreach from=$err_rows item=err}
    <pre>{$err}</pre>
  {/foreach}
  <br />
{/if}
{include file="buttons/go_back.tpl"}
