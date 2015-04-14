{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, product_bp_icon.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{assign var="TplImages" value="`$SkinDir`/modules/Special_Offers/images"}

{if $product.bonus_points gt 0}
<td align="right" valign="top">
  <table cellspacing="0" cellpadding="0" summary="{$lng.lbl_sp_ttl_bonus_points|escape}">
  <tr>
    <td><img src="{$TplImages}/bp_icon_top_left.gif" alt="" /></td>
    <td class="bp-icon-header">+{$product.bonus_points}</td>
    <td><img src="{$TplImages}/bp_icon_top_right.gif" alt="" /></td>
  </tr>
  <tr>
    <td><img src="{$TplImages}/bp_icon_bottom_left.gif" alt="" /></td>
    <td class="bp-icon-footer">{$lng.lbl_sp_ttl_bonus_points}</td>
    <td><img src="{$TplImages}/bp_icon_bottom_right.gif" alt="" /></td>
  </tr>
  </table>
</td>
{/if}
