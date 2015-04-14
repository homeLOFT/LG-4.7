{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, new_arrivals_show_date.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

{if $new_arrivals_show_date eq 'Y' && $config.New_Arrivals.show_date_row_in_customer_area eq "Y"}
  <div class="new_arrivals_date">{$lng.lbl_added}:&nbsp;{$product.add_date|date_format:$config.Appearance.date_format}</div>
{/if}
