{*
068e287de35808bddd9e529ce0a5e46e4b197eee, v3 (xcart_4_6_2), 2014-01-10 19:12:15, buy.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.Appearance.buynow_button_enabled eq "Y"}
<script type="text/javascript">
//<![CDATA[
  products_data[{$p.productid}] = {ldelim}{rdelim};
//]]>
</script>
  {if $login ne ''}{assign var="is_logged_in" value=1}{/if}
  {include_cache file="customer/main/buy_now.tpl" product=$p is_matrix_view=$is_matrix_view login=$is_logged_in|default:'' smarty_get_cat=$smarty.get.cat smarty_get_page=$smarty.get.page smarty_get_quantity=$smarty.get.quantity is_a2c_popup=true}
{/if}
