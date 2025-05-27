{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, bottom.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="box">

  <div class="wrapper-box">

    {if $active_modules.Users_online}
      {include file="modules/Users_online/menu_users_online.tpl"}
    {/if}
    
<!-- Swatch Request Band -->
  
<div class="boot-widget col-md-12 img-band zeroSidePad zeroTopPad zeroBottomPad">
  <div class="imgBandBg swatch-box">
    <h3>Free Samples</h3>
    <p>The best way to understand our Leathers <br><span style="font-size:22px;">We'll send them out right away :)</span></p>
    <a href="https://www.leathergroups.com/our-leathers.html"><span class="blockBtn blueBtn swaBox">Request Samples</span></a>
  </div>
</div> 
  
  <!-- Bottom Area/Nav -->    
    
<footer>
      
    <ul class="footer">
      <li><a href="/shop/login.php">Sign In</a>
      </li>
      <li><a href="/shop/register.php">Register</a>
      </li>
      <li><a href="/shop/cart.php">Shopping Cart</a>
      </li>
      
      <li><a href="https://leathergroups.my.site.com/support/s/contactsupport" target="_blank">Contact Us</a></li>
    </ul>
      
    <ul class="footer">
      <li><a href="/order-process.html">Order Process</a>
      </li>
      <li><a href="/shipping.html">Shipping</a>
      </li>
      <li><a href="/returns.html">Returns</a>
      </li>
      <li><a href="/financing.html">Financing</a>
      </li>
    </ul>
      
    <ul class="footer">
      <li><a href="/our-leathers.html">Our Leathers</a>
      </li>
      <li><a href="/construction.html">Construction</a>
      </li>
      <li><a href="/customization.html">Customization</a>
      </li>
      <li><a href="/blog/category/custom-leather-furniture-order-feed">Customer Order Feed</a>
      </li>
    </ul>
    
    <ul class="footer">
      <li><a href="/about-us.html">Our Story</a>
      </li>
      <li><a href="/quality.html">Craftsmanship</a>
      </li>
      <li><a href="/to-the-trade.html">To the Trade</a>
      </li>
      <li><a href="Terms-and-Conditions.html">Terms and Conditions</a>
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
