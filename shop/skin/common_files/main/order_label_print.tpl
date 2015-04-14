{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, order_label_print.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{$order.tracking}
  {$customer.s_firstname|default:$customer.firstname}
  {$customer.s_lastname|default:$customer.lastname}

  {$customer.s_address}
  {$customer.s_address_2}
  {$customer.s_city} {$customer.s_state_text}
  {$customer.s_zipcode} {$customer.s_country_text}

