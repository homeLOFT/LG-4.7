{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, head.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="wrapper-box">

  <div class="line1">
    <div class="logo">
      <a href="http://www.leathergroups.com"><img src="../common/images/logo.png" alt="{$config.Company.company_name}" /></a>
    </div>

    {include file="customer/menu_cart.tpl"}

    <div class="line3">

      <div class="items">

        <div class="item">
          {include file="customer/header_links.tpl"}
        </div>

        <div class="item">
          {include file="customer/language_selector.tpl"}
        </div>

      </div>

      

    </div>
    <div class="contact"><a href="http://support.leathergroups.com/customer/portal/emails/new" class="email-link" title="Send us an Email"></a></div>
    <div class="tap2call"><a href="tel:+18778887632" class="call-link" title="Tap to call us"><span class="fa fa-phone"></span></a>
    </div>

  </div>
  <div class="clearing"></div>

  {include file="customer/mobile_header.tpl"}

  {include file="customer/noscript.tpl"}

</div><!--/wrapper-box-->
