{*
5248dd57d73626c5b877d678e4a33bec46c4a1c5, v4 (xcart_4_4_5), 2011-11-10 09:53:44, minicart_total.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<span class="minicart">
{strip}
  {if $minicart_total_items gt 0}
    <span class="full">
      {currency value=$minicart_total_cost assign=total}
      <span class="minicart-items-value">{$minicart_total_items}</span>&nbsp;
      <span class="minicart-items-label">{$lng.lbl_sp_items}</span>&nbsp;
      <span class="minicart-items-delim">/</span>&nbsp;
      {include file="main/tooltip_js.tpl" class="minicart-items-total help-link" title=$total text=$lng.txt_minicart_total_note}
    </span>
  {else}
    <span class="empty">
      <strong>{$lng.lbl_cart_is_empty}</strong>
    </span>
  {/if}
{/strip}
{if $minicart_total_standalone}
{load_defer_code type="css"}
{load_defer_code type="js"}
{/if}
</span>
