{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, home.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<?xml version="1.0" encoding="{$default_charset|default:"utf-8"}"?>
<!DOCTYPE html>
{config_load file="$skin_config"}
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"{if $active_modules.Socialize} xmlns:g="http://base.google.com/ns/1.0" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#"{/if}>
<head>
<!-- Google Tag Manager -->
{literal}<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MJVPHCV');</script>{/literal}
<!-- End Google Tag Manager -->
	
  {include file="customer/service_head.tpl"}
</head>
<body{if $body_onload ne ''} onload="javascript: {$body_onload}"{/if} class="{if $container_classes}{foreach from=$container_classes item=c}{$c} {/foreach}{/if}{if $main eq 'catalog' and $current_category.category eq ''}home-container {/if}{$main}-container">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJVPHCV"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<script type="text/javascript" src="/js/wz_tooltip.js" defer></script>
	
{if $active_modules.EU_Cookie_Law ne ""}
{include file="modules/EU_Cookie_Law/info_panel.tpl"}
{/if}
{if $main eq 'product' and $is_admin_preview}
  {include file="customer/main/product_admin_preview_top.tpl"}
{/if}

<div id="header">
       
<!-- bootstrap nav -->
       
<!-- navbar header -->
  
<div class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid" id="navfluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="https://www.leathergroups.com/"><img src="/common/images/logo.png" width="630" height="120" alt="Leather Groups.com"></a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Shop ↓<span class="caret visible-xs-inline-block"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li class="col-sm-3 col-md-2">
              <ul>
				<li class="divider"></li>
			    <li class="drop-down-sub"><a href="https://www.leathergroups.com/blog/customer-order-gallery" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Customer Order Gallery');">Customer Order Gallery ›</a></li>
			    <li class="divider"></li>
                <li class="main-dd-header dropdown-header">Sofas</li>
			    <li class="divider"></li>
                <li><a href="/shop/Leather-Sofas.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather Sofas');">Leather Sofas</a></li>
			    <li><a href="/shop/Small-Scale-Leather-Sofas.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Small Scale Sofas');">Small Scale Sofas</a></li>
			    <li><a href="/shop/Deep-Leather-Sofas.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Deep Sofas');">Deep Sofas</a></li>
			    <li class="divider"></li>
              </ul>
              <ul>
                <li class="main-dd-header dropdown-header">Sectionals</li>
			    <li class="divider"></li>
			    <li><a href="/shop/Leather-Sectional-Sofas.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather Sectionals');">Leather Sectionals</a></li>
			    <li><a href="/shop/Small-Scale-Leather-Sectional-Sofas.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Small Scale Sectionals');">Small Scale Sectionals</a></li>              
			    <li><a href="/shop/Deep-Leather-Sectional-Sofas.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Deep Sectionals');">Deep Sectionals</a></li>
			    <li class="divider"></li>
                <li class="drop-down-sub"><a href="/what-is-a-sectional-sofa.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Sectional Info');">Leather Sectional Info</a></li>
			    <li><a href="/what-is-a-sectional-sofa.html#sectional-configurations" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Sectional Configurations');">- Configurations</a></li>
			    <li><a href="/what-is-a-sectional-sofa.html#sectional-pieces" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Sectional Pieces');">- Pieces used</a></li>
                <li><a href="/what-is-a-sectional-sofa.html#popular-leather-sectional-sofas" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Popular Sectionals');">- Popular Leather Sectionals</a></li>
			    <li class="divider"></li>
              </ul>
            </li>
            <li class="col-sm-3 col-md-2">
              <ul>
                <li class="main-dd-header dropdown-header">Chairs</li>
			    <li class="divider"></li>
                <li><a href="/shop/Leather-Chairs.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather Chairs');">Leather Chairs</a></li>
                <li><a href="/shop/Leather-Swivel-Chairs.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather Swivel Chairs');">Leather Swivel Chairs</a></li>
			    <li><a href="/shop/Deep-Leather-Chairs.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Deep Leather Chairs');">Deep Leather Chairs</a></li>
                <li class="divider"></li>
              </ul>
			  <ul>
                <li class="main-dd-header dropdown-header">Ottomans</li>
			    <li class="divider"></li>
                <li><a href="/shop/Leather-Ottomans.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather Ottomans');">Ottomans</a></li>
			    <li><a href="/shop/Leather-Cocktail-Ottomans.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather Cocktail Ottomans');">Cocktail Ottomans</a></li>
                <li class="divider"></li>	
              </ul>
			  <ul>
			    <li class="drop-down-sub"><a href="/shop/Clearance-Leather-Furniture.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'In Stock Clearance');">In Stock/Clearance</a></li>
			    <li class="divider"></li>
              </ul>
            </li>
			<li class="col-sm-3 col-md-2">
              <ul>
                <li class="main-dd-header dropdown-header">Leather Seating Collections</li>
		        <li class="divider"></li>
                <li><a href="/shop/Arizona-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Arizona Collection');">Arizona</a></li>
			    <li><a href="/shop/Bonham-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Bonham Collection');">Bonham</a></li>
			    <li><a href="/shop/Braxton-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Braxton Collection');">Braxton</a></li>
			    <li><a href="/shop/Bruno-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Bruno Collection');">Bruno</a></li>
			    <li><a href="/shop/Dexter-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Dexter Collection');">Dexter</a></li>
                <li><a href="/shop/Julien-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Julien Collection');">Julien</a></li>
			    <li><a href="/shop/Langston-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Langston Collection');">Langston</a></li>
			    <li><a href="/shop/Midtown-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Midtown Collection');">Midtown</a></li>
			    <li><a href="/shop/Muir-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Muir Collection');">Muir</a></li>
			    <li><a href="/shop/Oscar-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Oscar Collection');">Oscar</a></li>
			    <li><a href="/shop/Reno-Leather-Furniture-Collection.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Reno Collection');">Reno</a></li>    
              </ul>
            </li>
			<li class="col-sm-3 col-md-2">
              <ul>
                <li class="main-dd-header dropdown-header dd-brand"><a href="/order-process.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Ordering Info');">Ordering Info</a></li>
			    <li class="divider"></li>
                <li><a href="/order-process.html#how-to-order" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'How to order');">How to order &rsaquo;</a></li>
			    <li><a href="/order-process.html#after-you-order" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Payment - After you order');">Payment &rsaquo;</a></li>
                <li><a href="/order-process.html#order-flow" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'How long - Order flow');">How long does it take? &rsaquo;</a></li>
			    <li><a href="/order-process.html#leatherTBD" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Leather TBD');">Order before selecting a Leather? &rsaquo;</a></li>
                <li class="divider"></li>
			    <li class="drop-down-sub"><a href="/our-leathers.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Our Leathers');">Leathers</a></li>
                <li><a href="/our-leathers.html#aniline" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Aniline Leathers');">- Full Grain, Aniline</a></li>
			    <li><a href="/our-leathers.html#pigmented" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Pigmented Leathers');">- Full Grain, Pigmented</a></li>
                <li><a href="/our-leathers.html#request-leathers" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Free Samples');">- Free Samples</a></li>
                <li class="divider"></li>
                <li class="drop-down-sub"><a href="/construction.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Construction');">Construction</a></li>
			    <li><a href="/construction.html#suspension" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Suspension');">- Suspension</a></li>
			    <li><a href="/construction.html#frame" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Frame');">- Frame</a></li>
                <li><a href="/construction.html#cushions" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Cushions');">- Cushions</a></li>
			    <li class="divider"></li>
              </ul>
            </li>
            <li id="swaSwitch" class="col-md-4"><a href="/our-leathers.html#request-leathers" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Free Sample Image');"><img class="img-responsive swaDropImg" src="https://lg.imgix.net/lander/Free-Leather-Samples.png?auto=format&q=60"  alt="Request Free Samples at LeatherGroups.com"></a></li>
		    <li class="col-md-12 shop-sec-section">
		      <div style="border-right:none;" class="col-sm-4 section-resource">
			    <span class="heading-shop">Free Samples</span>
			    <span class="detail-shop">Seeing the leathers in person is an key part of the process.  <a href="/our-leathers.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Free-Samples-Btm Section');">Request Free Samples</a></span></div>
			  <div class="col-sm-4 section-resource">
			    <span class="heading-shop">Our Process</span>
			    <span class="detail-shop">We've tried to build a process that creates a great experience for our customers. <a href="/order-process.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Order Process-Btm Section');">Order Process Info</a></span>
			  </div>
			  <div style="border-right:none;" class="col-sm-4 section-resource">
			    <span class="heading-shop">Customization</span>
			    <span class="detail-shop">We can customize just about anything about the Leather Furniture that we build. <a href="/customization.html" onClick="ga('send', 'event', 'Shop Menu', 'Click', 'Customization-Btm Section');">Start Customizing</a></span>
			  </div>
	        </li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Learn ↓<span class="caret visible-xs-inline-block"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li class="col-sm-2">
          <ul>
            <li class="main-dd-header dropdown-header">Resources</li>
			<li class="divider"></li>
			<li><a href="/construction.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Quality Construction');">Quality Construction</a></li>
			<li><a href="/our-leathers.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Our Leathers');">Our Leathers</a></li>
			<li><a href="/order-process.html#leatherTBD" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Leather TBD');">Leather TBD</a></li>
			<li><a href="/shipping.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Shipping');">Shipping</a></li>
			<li><a href="/shipping.html#canada" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Canada Shipping');">Shipping to Canada</a></li>
			<!--<li><a href="/price-match.html">Price Match</a></li>-->
			<li><a href="/financing.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Financing');">Financing</a></li>
          </ul>
        </li>
        <li class="col-sm-2">
          <ul>
            <li class="main-dd-header dropdown-header">Inspiration</li>
			<li class="divider"></li>
			<li class="drop-down-sub"><a href="https://www.leathergroups.com/blog/customer-order-gallery" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Customer Order Gallery');">Customer Order Gallery ›</a></li>
			<li>See photos of actual client orders. Filter by leather or collection</li>
			<li class="divider"></li>
			<li class="drop-down-sub"><a href="/customization.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Customization');">Customization ›</a></li>
			<li>Whether you need custom length, custom arm size, custom height, nail head trim, custom shaping, or just about anything else, we've got you! </li>
			<li class="divider"></li>
          </ul>
        </li>
		<li class="col-sm-2">
          <ul>
            <li class="main-dd-header dropdown-header">About Us</li>
			<li class="divider"></li>
			<li><a href="/about-us.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Our Story');">Our Story</a></li>
            <li><a href="/quality.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Craftsmanship');">Craftsmanship</a></li>
			<li><a href="/order-process.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Order Process');">Our Process</a></li>
			<li><a href="/returns.html"onclick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-Returns');">Returns</a></li>
			<li class="divider"></li>
            <li class="drop-down-sub"><a href="/to-the-trade.html" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu-To the Trade');">To the Trade ›</a></li>
            <li>Designers / Commercial / Hospitality</li>
            <li class="divider"></li>
          </ul>
        </li>
        
        <li class="col-sm-4 hidden-sm learnDrop"><a href="https://www.leathergroups.com/shop/Braxton-Leather-Furniture-Collection.html"><img class="img-responsive learnDropImg" src="https://lg.imgix.net/cat/Braxton-Leather-Furniture-Collection.png?auto=format&q=60"  alt="Braxton (Maxwell) Leather Furniture Collection at LeatherGroups.com"><h2>The Braxton Leather Collection</h2></a></li>	
		<li class="col-md-12 shop-sec-section">
          <div class="col-sm-4 section-resource">
		    <span class="heading-shop">Customer Order Gallery</span>
			<span class="detail-shop">See raw photography of Customer Orders in various configurations and leathers. <a href="/blog/customer-order-gallery" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu Bullet - Customer Order Gallery');">Start Browsing Photos</a></span>
		  </div>
		  <div class="col-sm-4 section-resource">
		    <span class="heading-shop">Our Collections</span>
			<span class="detail-shop">American made Leather Furniture collections in top shelf leathers from Italy. <a href="/shop/homeloft-Leather-Furniture-Collections.html">Shop Collections</a></span>
		  </div>
		  <div style="border-right:none;" class="col-sm-4 section-resource">
		    <span class="heading-shop">Customization</span>
			<span class="detail-shop">We can customize just about anything about the Leather Furniture that we build. <a href="/customization.html">Start Customizing</a></span>
		  </div>
	    </li>
      </ul>
    </li>
    <li class="nonDrop"><a href="/blog/category/customer-testimonials" onClick="ga('send', 'event', 'Main Nav', 'Click', 'Reviews');">Reviews ›</a></li>
	<li class="nonDrop"><span class="swatchNavBtn hidden-xs"><a href="/our-leathers.html#aniline" onClick="ga('send', 'event', 'Main Nav', 'Click', 'Order Samples');">Order Samples</a></span>  
    </li>
	<li class="nonDrop"><span class="swatchNavBtn visible-xs"><a href="/our-leathers.html#aniline" onClick="ga('send', 'event', 'Mobile Main Nav', 'Click', 'Order Samples');">Order Samples</a></span>  
    </li>
  </ul>
  <ul class="nav navbar-nav navbar-right">
	<li class="nonDrop"><span class="ccNavBtn visible-xs"><a href="#" onClick="LiveChatWidget.call('maximize');return false; ga('send', 'event', 'Mobile Main Nav', 'Click', 'Chat with us');">Chat with us</a></span>
    </li>
    <li class="nonDrop"><span class="ccNavBtn visible-xs"><a href="https://leathergroups.force.com/support/s/contactsupport" target="_blank" onClick="ga('send', 'event', 'Mobile Main Nav', 'Click', 'Email Support');">Email us</a></span>
    </li>
	<li class="nonDrop"><a class="glyphicon glyphicon-shopping-cart" href="/shop/cart.php" onClick="ga('send', 'event', 'Main Nav', 'Click', 'Shopping Cart');"></a></li>
    <li class="nonDrop"><a class="glyphicon glyphicon-user" href="/shop/login.php" onClick="ga('send', 'event', 'Main Nav', 'Click', 'My Account');"></a></li>
    <li class="dropdown"><span class="ccNavBtn hidden-xs">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Contact Us</a></span>
        <ul class="dropdown-menu ccNav">
          <li>
            <div class="col-md-3"><h2>Service</h2>
            <p>However you contact us, we're here to help</p></div>
            <div class="col-md-3">
              <h3>Phone</h3>
                <img src="https://lg.imgix.net/lander/Call-Us.png?auto=format&amp;q=60" alt="Call LeatherGroups.com">
                <p class="ccSubNote"><span class="ccSubTel">1(877)888-7632</span> - 9am-5pm PST</p></div>
            <div class="col-md-3">
              <a href="https://leathergroups.force.com/support/s/contactsupport" target="_blank" onClick="ga('send', 'event', 'Nav Contact', 'Click', 'Email Support');"><div class="col-sm-12">
              <h3>Email</h3>
                <img src="https://lg.imgix.net/lander/Email-Us.png?auto=format&amp;q=60" alt="Email LeatherGroups.com">
                <p class="ccSubNote">Click here to send us an email.  We' respond within a few hours at the most.</p></div></a></div>
            <div class="col-md-3">
              <a href="#" onClick="LiveChatWidget.call('maximize');return false; ga('send', 'event', 'Nav Contact', 'Click', 'Chat with us');"><div class="col-sm-12">
			  <h3>Chat</h3>
                <img src="https://lg.imgix.net/lander/Chat-with-Us.png?auto=format&amp;q=60" alt="Chat with us here at LeatherGroups.com">
                <p class="ccSubNote">Jump on Chat with us at the bottom of your screen from 9am-5pm PST</p></div></a></div>
            </li>
          </ul>
        </li>
      </ul>
  </div>
</div>
</div>
<!-- end bootstrap nav -->
       
    </div>

<div id="page-container"{if $page_container_class} class="{$page_container_class}"{/if}>
  <div id="page-container2">
    <div id="content-container">

      {*include file="customer/tabs.tpl"*}

      {if ($main neq 'cart' or $cart_empty) and $main neq 'checkout'}
        {if $main neq "catalog" or $current_category.category neq ""}
          {include file="customer/bread_crumbs.tpl"}
        {/if}
      {/if}

      <div id="content-container2">

        {if $active_modules.Socialize
            and ($config.Socialize.soc_fb_like_enabled eq "Y" or $config.Socialize.soc_fb_send_enabled eq "Y")
        }
          <div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3&appId=230276873661988";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
        {/if}

        {include file="customer/content.tpl"}

      </div>
    </div>

    <div class="clearing">&nbsp;</div>
    
    <div id="footer">

      {include file="customer/bottom.tpl"}

    </div>

    {if $active_modules.Google_Analytics and $config.Google_Analytics.ganalytics_version eq 'Traditional'}
      {include file="modules/Google_Analytics/ga_code.tpl"}
    {/if}

  </div>
</div>
{load_defer_code type="css"}
{include file="customer/service_body_js.tpl"}
{load_defer_code type="js"}

<script
    type="text/javascript"
    async defer
    src="//assets.pinterest.com/js/pinit.js"
></script>

</body>
</html>
