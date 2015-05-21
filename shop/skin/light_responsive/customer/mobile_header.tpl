{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, mobile_header.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="mobile-header">
  <ul class="nav nav-pills">

    <li class="dropdown">
      <a id="main-menu-toggle" class="dropdown-toggle" href="#">
        <span class="fa fa-bars"></span>
      </a>
      <div id="main-menu-box" class="dropdown-menu">

        {include file="customer/tabs.tpl" mode="plain_list"}
        
      </div>
    </li>

    <li class="dropdown">
      <a id="search-toggle" class="dropdown-toggle" href="#">
        <span class="fa fa-search"></span>
      </a>
      <div id="search-box" class="dropdown-menu">

        {include file="customer/search.tpl"}

      </div>
    </li>

    <li class="dropdown">
      <a id="account-toggle" class="dropdown-toggle" href="#">
        <span class="fa fa-user"></span>
      </a>
      <div id="account-box" class="dropdown-menu">

        {include file="customer/header_links.tpl" mode="plain_list"}

      </div>
    </li>
    
    <li class="mob-custcare"><a class="custcare-link fa fa-comments-o fa-lg" href="#" rel="noindex">Customer Care</a>
    </li>

  </ul>
</div>
