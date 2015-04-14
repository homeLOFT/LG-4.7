{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, minicart_total.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="minicart">
  {if $minicart_total_items gt 0}

    <div class="full">

      <span class="minicart-items-value">{$minicart_total_items}</span>
      <span class="minicart-items-label">{$lng.lbl_sp_items}</span>
      <span class="minicart-items-delim">/</span>
      {capture name=tt assign=val}
        {currency value=$minicart_total_cost}
      {/capture}
      {include file="main/tooltip_js.tpl" class="minicart-items-total help-link" title=$val text=$lng.txt_minicart_total_note}

      {getvar var='paypal_express_active' func='func_get_paypal_express_active'}
      {if !$paypal_express_active}
        <div class="minicart-checkout-link"><a href="cart.php?mode=checkout">{$lng.lbl_checkout}</a></div>
      {/if}

    </div>

  {else}

    <div class="empty">

      <span class="minicart-items-value">{$minicart_total_items}</span>
      <span class="minicart-items-label">{$lng.lbl_sp_items}</span>
      <span class="minicart-empty-text">{$lng.lbl_cart_is_empty}</span>

    </div>

  {/if}

{if $minicart_total_standalone}
{load_defer_code type="css"}
{load_defer_code type="js"}
{/if}
</div>
