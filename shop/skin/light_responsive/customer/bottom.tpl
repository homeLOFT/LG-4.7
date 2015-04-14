{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, bottom.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="box">

  <div class="wrapper-box">

    {if $active_modules.Users_online}
      {include file="modules/Users_online/menu_users_online.tpl"}
    {/if}
    
    <footer>  
      <ul class="footer"><span>My Account</span>
        <li><a href="/shop/login.php">Sign In</a>
        </li>
        <li><a href="/shop/register.php">Register</a>
        </li>
        <li><a href="/shop/login.php">Order History</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1432288-tracking-shipments">Track An Order</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1433285-contact-us">Contact Us</a>
        </li>
      </ul>
      
      <ul class="footer"><span>Info & Policies</span>
        <li><a href="http://support.leathergroups.com/customer/portal/topics/615801-ordering-from-leathergroups-com/articles">Ordering Information</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1433253-privacy-policy">Privacy Policy</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1433265-security">Secure Shopping</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/topics/615846-shipping-information/articles">Shipping Policy</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434643-return-policy">30-Day Return Policy</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434895-our-satisfaction-guarantee">Our Guarantee</a>
        </li>
      </ul>
      
      <ul class="footer"><span>Shop with Confidence</span>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434894-why-shop-with-leathergroups-com-">Why Buy From Us</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/topics/664561-sales-promotions/articles">Sales And Promotions</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434891-shop-confidently-with-supreme-service">Customer Service</a>
        </li>
        <li><a href="/shop/cart.php">Shopping Cart</a>
        </li>
        <li><a href="/blog">Leather Furniture Blog</a>
        </li>
        <li><a href="javascript:CreateBookmarkLink('Leather Groups.com', 'http://www.leathergroups.com')">Bookmark This Site</a>
        </li>
      </ul>
      
      <ul class="footer"><span>About homeloft</span>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434885-who-we-are">Who We Are</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434888-our-mission">Our Mission</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1434895-our-satisfaction-guarantee">Our Guarantee</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1433280-affiliate-program?b_id=2410">Affiliate Program</a>
        </li>
        <li><a href="http://support.leathergroups.com/customer/portal/articles/1433273-become-a-supplier-?b_id=2410">Become A Supplier</a>
        </li>
      </ul>
    </footer>
    
    <div class="subbox">
      <div class="copyright">
        {include file="copyright.tpl"}
      </div>
      {if $active_modules.Socialize}
        {include file="modules/Socialize/footer_links.tpl"}
      {/if}
    </div>

  </div><!--/wrapper-box-->

</div>
