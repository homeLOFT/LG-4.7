{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, home.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<?xml version="1.0" encoding="{$default_charset|default:"utf-8"}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml"{if $active_modules.Socialize} xmlns:g="http://base.google.com/ns/1.0" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#"{/if}>
<head>
  {include file="customer/service_head.tpl"}
</head>
<body{if $body_onload ne ''} onload="javascript: {$body_onload}"{/if} class="{if $container_classes}{foreach from=$container_classes item=c}{$c} {/foreach}{/if}{if $main eq 'catalog' and $current_category.category eq ''}home-container {/if}{$main}-container">
<script type="text/javascript" src="/js/wz_tooltip.js"></script>
<script>
$(document).ready( function() {
    $("#nav-promo").load("/nav-promo.html");
});
</script>
{if $active_modules.EU_Cookie_Law ne ""}
{include file="modules/EU_Cookie_Law/info_panel.tpl"}
{/if}
{if $main eq 'product' and $is_admin_preview}
  {include file="customer/main/product_admin_preview_top.tpl"}
{/if}

<div id="header">
       
<!-- bootstrap nav -->
       
<div class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid" id="navfluid">
    <div class="navbar-header">
       <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="https://www.leathergroups.com/"><img src="/common/images/logo.png"  alt="Leather Groups.com"></a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

    <ul class="nav navbar-nav">
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Shop <span class="caret visible-xs-inline-block"></span></a>
        <ul class="dropdown-menu" role="menu">
          <li class="col-sm-3 col-md-2">
            <ul>
              <li class="main-dd-header dropdown-header dd-brand">COLLECTIONS</li>
		      <li class="divider"></li>
              <li><a href="/shop/Arizona-Leather-Furniture-Collection.html">Arizona</a></li>
			  <li><a href="/shop/Bonham-Leather-Furniture-Collection.html">Bonham</a></li>
			  <li><a href="/shop/Braxton-Leather-Furniture-Collection.html">Braxton</a></li>
			  <li><a href="/shop/Dexter-Leather-Furniture-Collection.html">Dexter</a></li>
              <li><a href="/shop/Julien-Leather-Furniture-Collection.html">Julien</a></li>
			  <li><a href="/shop/Langston-Leather-Furniture-Collection.html">Langston</a></li>
              <li><a href="/shop/Las-Vegas-Leather-Furniture-Collection.html">Las Vegas	</a></li>
			  <li><a href="/shop/Midtown-Leather-Furniture-Collection.html">Midtown</a></li>
			  <li><a href="/shop/Oscar-Leather-Furniture-Collection.html">Oscar</a></li>
			  <li><a href="/shop/Reno-Leather-Furniture-Collection.html">Reno</a></li>
            </ul>
            <ul id="nav-promo">
            </ul>
          </li>
          <li class="col-sm-3 col-md-2">
            <ul>
              <li class="main-dd-header dropdown-header">Sofas</li>
			  <li class="divider"></li>
              <li><a href="/shop/Leather-Sofas.html">Leather Sofas</a></li>
			  <li><a href="/shop/Fabric-Sofas.html">Fabric Sofas</a></li>
			  <li><a href="/shop/Small-Scale-Leather-Sofas.html">Small Scale Sofas</a></li>
			  <li><a href="/shop/Deep-Leather-Sofas.html">Deep Sofas</a></li>
            </ul>
            <ul>
              <li class="main-dd-header dropdown-header">Chairs</li>
			  <li class="divider"></li>
              <li><a href="/shop/Leather-Chairs.html">Leather Chairs</a></li>
              <li><a href="/shop/Leather-Swivel-Chairs.html">Leather Swivel Chairs</a></li>
			  <li><a href="/shop/Deep-Leather-Chairs">Deep Leather Chairs</a></li>
              <li class="divider"></li>
            </ul>
          </li>
          <li class="col-sm-3 col-md-2">
            <ul>
              <li class="main-dd-header dropdown-header">Sectionals</li>
			  <li class="divider"></li>
			  <li><a href="/shop/Leather-Sectional-Sofas.html">Leather Sectionals</a></li>
			  <li><a href="/shop/Fabric-Sectional-Sofas.html">Fabric Sectionals</a></li>
			  <li><a href="/shop/Small-Scale-Leather-Sectional-Sofas.html">Small Scale Sectionals</a></li>              
			  <li><a href="/shop/Deep-Leather-Sectional-Sofas.html">Deep Sectionals</a></li>
            </ul>
            <ul>
              <li class="main-dd-header dropdown-header">Ottomans</li>
			  <li class="divider"></li>
              <li><a href="/shop/Leather-Ottomans.html">Ottomans</a></li>
			  <li><a href="/shop/Leather-Cocktail-Ottomans.html">Cocktail Ottomans</a></li>              <li class="divider"></li>
            </ul>
          </li>
          <li class="col-sm-3 col-md-2">
            <ul>
              <li class="main-dd-header dropdown-header">Helpful Info</li>
			  <li class="divider"></li>
              <li><a href="/order-process.html#how-to-order">When you're ready to order »</a></li>
              <li><a href="/order-process.html#order-flow">How long does it take to receive the furniture?</a></li>
              <li class="divider"></li>
              <li class="drop-down-sub">CONSTRUCTION</li>
			  <li><a href="/construction.html#suspension">- Suspension</a></li>
			  <li><a href="/construction.html#frame">- Frame</a></li>
              <li><a href="/construction.html#cushions">- Cushions</a></li>
			  <li class="divider"></li>
              <li class="drop-down-sub">LEATHER</li>
              <li><a href="/our-leathers.html">- Our Leathers</a></li>
              <li><a href="/order-process.html#leatherTBD">- Ordering as Leather TBD</a></li>
              <li class="divider"></li>
              <li class="drop-down-sub">TO THE TRADE</li>
              <li><a href="/to-the-trade.html">- Designers / Commercial / Hospitality</a></li>
              <li class="divider"></li>
            </ul>
          </li>
          <li id="swaSwitch" class="col-md-4">
					<a href="/our-leathers.html#request-leathers"><img class="img-responsive swaDropImg" src="https://leathergroups.imgix.net/Free-Leather-Samples.png?auto=format&q=60"  alt="Request Free Samples at LeatherGroups.com"></a>
					</li>
				<li class="col-md-12 shop-sec-section">
						<div class="col-sm-3 section-resource">
						<h4>Additional Info:</h4>
						</div>
						<div class="col-sm-3 section-resource">
						<span class="heading-shop">Financing</span>
						<span class="detail-shop">We offer Deferred Interest Financing options through Synchrony Financial. <a href="/financing.html">Apply Now</a></span>
						</div>
						<div class="col-sm-3 section-resource">
						<span class="heading-shop">Our Process</span>
						<span class="detail-shop">We've tried to build a process that creates a great experience for our customers. <a href="/order-process.html">Learn More</a></span>
						</div>
						<div style="border-right:none;" class="col-sm-3 section-resource">
						<span class="heading-shop">Customization</span>
						<span class="detail-shop">We can customize just about anything about the Leather Furniture that we build. <a href="/customization.html">Start Customizing</a></span>
						</div>
					</li>
      </ul>
    </li>
    <li class="nonDrop"><a href="/shop/homeloft-Leather-Furniture-Collections.html">Collections</a></li>
    <li class="nonDrop"><a href="/blog/category/customer-testimonials">Reviews</a></li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">Learn <span class="caret visible-xs-inline-block"></span></a>
      <ul class="dropdown-menu" role="menu">
        <li class="col-sm-2">
          <ul>
            <li class="main-dd-header dropdown-header">About Us</li>
			<li class="divider"></li>
			<li><a href="/about-us.html">Our Story</a></li>
            <li><a href="/quality.html">Craftsmanship</a></li>
			<li><a href="/order-process.html">Our Process</a></li>
			<li><a href="/returns.html">Returns</a></li>
			<li><a href="/to-the-trade.html">To the Trade</a></li>
          </ul>
        </li>
        <li class="col-sm-2">
          <ul>
            <li class="main-dd-header dropdown-header">Resources</li>
			<li class="divider"></li>
			<li><a href="/construction.html">Quality Construction</a></li>
			<li><a href="/our-leathers.html">Our Leathers</a></li>
			<li><a href="/our-fabrics.html">Our Fabrics</a></li>
			<li><a href="/order-process.html#leatherTBD">Leather TBD</a></li>
			<li><a href="/shipping.html">Shipping</a></li>
			<li><a href="/shipping.html#canada">Shipping to Canada</a></li>
			<li><a href="/price-match.html">Price Match</a></li>
			<li><a href="/financing.html">Financing</a></li>
          </ul>
        </li>
        <li class="col-sm-2">
          <ul>
            <li class="main-dd-header dropdown-header">Inspiration</li>
			<li class="divider"></li>
            <li><a href="/customization.html">Customization</a></li>
			<li><a href="https://www.leathergroups.com/blog/customer-order-gallery" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu List - Customer Order Gallery');">Customer Order Gallery </a></li>
          </ul>
        </li>
        <li class="col-sm-4 hidden-sm learnDrop">
					<a href="https://www.leathergroups.com/shop/Braxton-Leather-Furniture-Collection.html"><img class="img-responsive learnDropImg" src="https://lg-cat.imgix.net/Braxton-Leather-Furniture-Collection.png?auto=format&q=60"  alt="Braxton (Maxwell) Leather Furniture Collection at LeatherGroups.com">
                    <h2>The Braxton Leather Collection</h2></a>
					</li>	
				<li class="col-md-12 shop-sec-section">
						<div class="col-sm-3 section-resource">
						<h4>More About Us:</h4>
						</div>
						<div class="col-sm-3 section-resource">
						<span class="heading-shop">Customer Order Gallery</span>
						<span class="detail-shop">See raw photography of Customer Orders in various configurations and leathers. <a href="/blog/customer-order-gallery" onClick="ga('send', 'event', 'Learn Menu', 'Click', 'Learn Menu Bullet - Customer Order Gallery');">Start Browsing Photos</a></span>
						</div>
						<div class="col-sm-3 section-resource">
						<span class="heading-shop">Our Collections</span>
						<span class="detail-shop">American made Leather Furniture collections in top shelf leathers from Italy. <a href="/shop/homeloft-Leather-Furniture-Collections.html">Shop Collections</a></span>
						</div>
						<div style="border-right:none;" class="col-sm-3 section-resource">
						<span class="heading-shop">Customization</span>
						<span class="detail-shop">We can customize just about anything about the Leather Furniture that we build. <a href="/customization.html">Start Customizing</a></span>
						</div>
					</li>
      </ul>
    </li>
  </ul>
  <ul class="nav navbar-nav navbar-right">
        <li class="dropdown"><span class="swatchNavBtn hidden-xs">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Samples</a></span>
          <ul class="dropdown-menu swatchNav">
            <li>
            <form id="contact-form" method="post" action="/common/php/contact.php" role="form" autocomplete="off">
            <h2>Free Samples</h2>
            <p>Nothing compares to seeing them in person</p>
            <div class="row swatchNavImg">
              <div class="col-sm-6 swatchNavCol"><h3>Full Grain, Full Aniline Leathers</h3><div class="row swaGrid">
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Brompton-Cocoa-Mocha.jpg" alt="Italian Brompton Cocoa Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Brompton-Cocoa-Mocha2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Brompton-Cocoa-Mocha"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Brompton-Classic-Vintage.jpg" alt="Italian Brompton Classic Vintage Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Brompton-Classic-Vintage2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Brompton-Classic-Vintage"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Brompton-Walnut.jpg" alt="Italian Brompton Walnut Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Brompton-Walnut2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Brompton-Walnut"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Cocoa.jpg" alt="Italian Berkshire Cocoa Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Cocoa2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Cocoa"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Oxblood.jpg" alt="Italian Berkshire Oxblood Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Oxblood2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Oxblood"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Pewter.jpg" alt="Italian Berkshire Pewter Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Pewter2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Pewter"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Camel.jpg" alt="Italian Berkshire Camel Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Camel2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Camel"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Burlap.jpg" alt="Italian Berkshire Burlap Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Burlap2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Burlap"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Anthracite.jpg" alt="Italian Berkshire Anthracite Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Anthracite2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Anthracite"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Berkshire-Chestnut.jpg" alt="" onMouseOver="Tip('<img src=/color/swatches/el/Berkshire-Chestnut2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Berkshire-Chestnut"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Beige.jpg" alt="Italian Burnham Leather - Beige Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Beige2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Beige"></label></div>
    			<div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Black.jpg" alt="Italian Burnham Leather - Black Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Black2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Black"></label></div>
    			<div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Dove.jpg" alt="Italian Burnham Leather - Dove Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Dove2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Dove"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Molasses.jpg" alt="Italian Burnham Leather - Molasses Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Molasses2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Molasses"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Parchment.jpg" alt="Italian Burnham Leather - Parchment Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Parchment2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Parchment"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Slate.jpg" alt="Italian Burnham Leather - Slate Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Slate2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Slate"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Burnham-Sycamore.jpg" alt="Italian Burnham Leather - Sycamore" onMouseOver="Tip('<img src=/color/swatches/el/Burnham-Sycamore2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Burnham-Sycamore"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Glove-Timberwolf.jpg" alt="Italian Glove Timberwolf Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Glove-Timberwolf2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Glove-Timberwolf"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Glove-Truffle.jpg" alt="Italian Glove Truffle Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Glove-Truffle2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Glove-Truffle"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Glove-Chestnut.jpg" alt="Italian Glove Chestnut Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Glove-Chestnut2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Glove-Chestnut"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Glove-Buckskin.jpg" alt="Italian Glove Buckskin Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Glove-Buckskin2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Glove-Buckskin"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Glove-Midnight-Blue.jpg" alt="Italian Glove Midnight Blue Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Glove-Midnight-Blue2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Glove-Midnight-Blue"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Glove-Ruby.jpg" alt="Italian Glove Ruby Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Glove-Ruby2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Glove-Ruby"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Domaine-Bronze.jpg" alt="Italian Domaine Bronze Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Domaine-Bronze2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Domaine-Bronze"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Brentwood-Tan.jpg" alt="Italian Brentwood Tan Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Brentwood-Tan2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Brentwood-Tan"></label></div>
				<div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Range-Chocolate.jpg" alt="Range Chocolate Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Range-Chocolate2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Range-Chocolate"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Range-Black.jpg" alt="Range Black Full Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Range-Black2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Range-Black"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Halter-Chaps.jpg" alt="Halter Chaps Top Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Halter-Chaps2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Halter-Chaps"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Halter-Oatmeal.jpg" alt="Halter Oatmeal Top Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Halter-Oatmeal2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Halter-Oatmeal"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Halter-Ranch.jpg" alt="Halter Ranch Top Grain Leather" onMouseOver="Tip('<img src=/color/swatches/el/Halter-Ranch2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Halter-Ranch"></label></div>
              </div>
            </div>
            <div class="col-sm-6 swatchNavCol"><h3>Full Grain Pigmented Leathers</h3>
              <div class="row swaGrid">
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Buckskin.jpg" alt="Jet Buckskin" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Buckskin-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Buckskin"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Sand.jpg" alt="Jet Sand" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Sand-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Sand"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Antique.jpg" alt="Jet Antique" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Antique-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Antique"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Acajou.jpg" alt="Jet Acajou" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Acajou-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Acajou"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Peatmoss.jpg" alt="Jet Peatmoss" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Peatmoss-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Peatmoss"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Chestnut.jpg" alt="Jet Chestnut" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Chestnut-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Chestnut"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Root.jpg" alt="Jet Root" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Root-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Root"></label></div>
                <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Carubo.jpg" alt="Jet Carubo" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Carubo-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Carubo"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Dash.jpg" alt="Jet Dash" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Dash-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Dash"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Soul-White.jpg" alt="Jet Soul White" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Soul-White-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Soul-White"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Lemon-Grass.jpg" alt="Jet Lemon Grass" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Lemon-Grass-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Lemon-Grass"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Parrot.jpg" alt="Jet Parrot" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Parrot-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Parrot"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Turquoise.jpg" alt="Jet Turquoise" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Turquoise-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Turquoise"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Indigo.jpg" alt="Jet Indigo" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Indigo-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Indigo"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Iron.jpg" alt="Jet Iron" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Iron-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Iron"></label></div>
              <div class="col-md-2 col-sm-2 col-xs-3 swatchSelect swatchTile"><a href="#" onClick="return false;"><img src="/color/swatches/el/Jet-Black-Ink.jpg" alt="Jet Black Ink" onMouseOver="Tip('<img src=/color/swatches/el/Jet-Black-Ink-2.jpg width=400 height=300>');" onMouseOut="UnTip()"/></a><label>
                  <input type="checkbox" name="Jet-Black-Ink"></label></div>
            </div>
          </div>
        </div>
    <div class="messages"></div>

    <div class="controls">

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <input id="form_name" type="text" name="name" class="form-control" placeholder="Full Name" required data-error="We need a name!">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <input id="form_address" type="text" name="address" class="form-control" placeholder="Full Street Address" required data-error="Where are we going to be sending these?">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <input id="form_email" type="email" name="email" class="form-control" placeholder="Email" required data-error="We'll need an email to send tracking :)">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <input id="form_city" type="text" name="city" class="form-control" placeholder="City" required data-error="Don't forget your City!">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-phone">
                    <input id="form_phone" type="tel" name="phone" class="form-control" placeholder="Phone Number is Optional">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
	<div class="col-sm-10">
		<select class="form-control" id="state" name="state" required data-error="Don't forget your State/Province!">
			<option value="">State/Province</option>
			<option value="AK">Alaska</option>
			<option value="AL">Alabama</option>
			<option value="AR">Arkansas</option>
			<option value="AZ">Arizona</option>
			<option value="CA">California</option>
			<option value="CO">Colorado</option>
			<option value="CT">Connecticut</option>
			<option value="DC">District of Columbia</option>
			<option value="DE">Delaware</option>
			<option value="FL">Florida</option>
			<option value="GA">Georgia</option>
			<option value="HI">Hawaii</option>
			<option value="IA">Iowa</option>
			<option value="ID">Idaho</option>
			<option value="IL">Illinois</option>
			<option value="IN">Indiana</option>
			<option value="KS">Kansas</option>
			<option value="KY">Kentucky</option>
			<option value="LA">Louisiana</option>
			<option value="MA">Massachusetts</option>
			<option value="MD">Maryland</option>
			<option value="ME">Maine</option>
			<option value="MI">Michigan</option>
			<option value="MN">Minnesota</option>
			<option value="MO">Missouri</option>
			<option value="MS">Mississippi</option>
			<option value="MT">Montana</option>
			<option value="NC">North Carolina</option>
			<option value="ND">North Dakota</option>
			<option value="NE">Nebraska</option>
			<option value="NH">New Hampshire</option>
			<option value="NJ">New Jersey</option>
			<option value="NM">New Mexico</option>
			<option value="NV">Nevada</option>
			<option value="NY">New York</option>
			<option value="OH">Ohio</option>
			<option value="OK">Oklahoma</option>
			<option value="OR">Oregon</option>
			<option value="PA">Pennsylvania</option>
			<option value="PR">Puerto Rico</option>
			<option value="RI">Rhode Island</option>
			<option value="SC">South Carolina</option>
			<option value="SD">South Dakota</option>
			<option value="TN">Tennessee</option>
			<option value="TX">Texas</option>
			<option value="UT">Utah</option>
			<option value="VA">Virginia</option>
			<option value="VT">Vermont</option>
			<option value="WA">Washington</option>
			<option value="WI">Wisconsin</option>
			<option value="WV">West Virginia</option>
			<option value="WY">Wyoming</option>
            <option value="">Canadian Provinces</option>
            <option value="AB">Alberta</option>
			<option value="BC">British Columbia</option>
			<option value="MB">Manitoba</option>
			<option value="NB">New Brunswick</option>
			<option value="NL">Newfoundland and Labrador</option>
			<option value="NS">Nova Scotia</option>
			<option value="ON">Ontario</option>
			<option value="PE">Prince Edward Island</option>
			<option value="QC">Quebec</option>
			<option value="SK">Saskatchewan</option>
			<option value="NT">Northwest Territories</option>
			<option value="NU">Nunavut</option>
			<option value="YT">Yukon</option>
		</select>
        <div class="help-block with-errors"></div>
	</div>
</div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <input id="form_zip" type="tel" name="zip" class="form-control" placeholder="Zip" required data-error="We need a zip code!">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <div class="g-recaptcha" data-sitekey="6Lf-kBQUAAAAADNsN1l1Rp7eAq68Sntj2SDzKTfV" style="float:right;"></div>
            </div>
          </div>
          <div class="col-md-6">
                <input type="submit" class="btn btn-success btn-send disabled" value="Send my free samples">
          </div>
          <div class="col-md-6 col-xs-12"><p class="nav-content-det-note"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span>&nbsp; NOTE: If your are using a Yahoo or AOL email address, please email us your request <a style="font-weight:400;" href="https://leathergroups.force.com/support/s/contactsupport" target="_blank">HERE</a>. Do not use this form.</p>
           </div>
        </div>	
        <div class="row">
            <div class="col-md-12">
            </div>
        </div>
    </div>

</form></li>
            
          </ul>
        </li>
        <li class="dropdown"><span class="ccNavBtn hidden-xs">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Help</a></span>
          <ul class="dropdown-menu ccNav">
            <li>
            <div class="col-md-3"><h2>Service</h2>
            <p>However you contact us, we're here to help</p></div>
            <div class="col-md-3">
              <h3>Phone</h3>
                <img src="https://leathergroups.imgix.net/Call-Us.png?auto=format&amp;q=60" alt="Call LeatherGroups.com">
                <p class="ccSubNote"><span class="ccSubTel">1(877)888-7632</span> - 7am-7pm PST</p></div>
            <div class="col-md-3">
              <a href="https://leathergroups.force.com/support/s/contactsupport" target="_blank"><div class="col-sm-12">
              <h3>Email</h3>
                <img src="https://leathergroups.imgix.net/Email-Us.png?auto=format&amp;q=60" alt="Email LeatherGroups.com">
                <p class="ccSubNote">Click here to send us an email.  We' respond within a few hours at the most.</p></div></a></div>
            <div class="col-md-3">
              <h3>Chat</h3>
                <img src="https://leathergroups.imgix.net/Chat-with-Us.png?auto=format&amp;q=60" alt="Chat with us here at LeatherGroups.com">
                <p class="ccSubNote">Jump on with us right now at the bottom of your screen & we'll help you on the spot!</p></div>
            </li>
          </ul>
        </li>
        <li class="nonDrop"><a class="glyphicon glyphicon-shopping-cart" href="/shop/cart.php"></a></li>
        <li class="nonDrop"><a class="glyphicon glyphicon-user" href="/shop/login.php"></a></li>
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

<!-- Google Code for Remarketing Tag -->
<!--------------------------------------------------
Remarketing tags may not be associated with personally identifiable information or placed on pages related to sensitive categories. See more information and instructions on how to setup the tag on: http://google.com/ads/remarketingsetup
--------------------------------------------------->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1071958683;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/1071958683/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>

<a href="https://plus.google.com/100479727098477541780" rel="publisher"></a>

{literal}
<script type="text/javascript">
adroll_adv_id = "JEHEX27TQJA2HG7QVBM2DH";
adroll_pix_id = "UL5SJ3VDO5CGDN4ANSB5ZP";
(function () {
var oldonload = window.onload;
window.onload = function(){
   __adroll_loaded=true;
   var scr = document.createElement("script");
   var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
   scr.setAttribute('async', 'true');
   scr.type = "text/javascript";
   scr.src = host + "/j/roundtrip.js";
   ((document.getElementsByTagName('head') || [null])[0] ||
    document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
   if(oldonload){oldonload()}};
}());
</script>

<style type='text/css'>
	.embeddedServiceHelpButton .helpButton .uiButton {
		background-color: #5b8aab;
		font-family: "Arial", sans-serif;
	}
	.embeddedServiceHelpButton .helpButton .uiButton:focus {
		outline: 1px solid #5b8aab;
	}
</style>

<script type='text/javascript' src='https://service.force.com/embeddedservice/5.0/esw.min.js'></script>
<script type='text/javascript'>
	var initESW = function(gslbBaseURL) {
		embedded_svc.settings.displayHelpButton = true; //Or false
		embedded_svc.settings.language = ''; //For example, enter 'en' or 'en-US'

		embedded_svc.settings.defaultMinimizedText = 'Chat with a Leather Expert'; //(Defaults to Chat with an Expert)
		//embedded_svc.settings.disabledMinimizedText = '...'; //(Defaults to Agent Offline)

		//embedded_svc.settings.loadingText = ''; //(Defaults to Loading)
		//embedded_svc.settings.storageDomain = 'yourdomain.com'; //(Sets the domain for your deployment so that visitors can navigate subdomains during a chat session)

		// Settings for Chat
		//embedded_svc.settings.directToButtonRouting = function(prechatFormData) {
			// Dynamically changes the button ID based on what the visitor enters in the pre-chat form.
			// Returns a valid button ID.
		//};
		//embedded_svc.settings.prepopulatedPrechatFields = {}; //Sets the auto-population of pre-chat form fields
		//embedded_svc.settings.fallbackRouting = []; //An array of button IDs, user IDs, or userId_buttonId
		//embedded_svc.settings.offlineSupportMinimizedText = '...'; //(Defaults to Contact Us)

		embedded_svc.settings.enabledFeatures = ['LiveAgent'];
		embedded_svc.settings.entryFeature = 'LiveAgent';

		embedded_svc.init(
			'https://leathergroups.my.salesforce.com',
			'https://leathergroups.force.com/support',
			gslbBaseURL,
			'00D6g000006uqDF',
			'LG_Chat_Team',
			{
				baseLiveAgentContentURL: 'https://c.la1-c2-ia5.salesforceliveagent.com/content',
				deploymentId: '5726g0000005daW',
				buttonId: '5736g0000005f8v',
				baseLiveAgentURL: 'https://d.la1-c2-ia5.salesforceliveagent.com/chat',
				eswLiveAgentDevName: 'LG_Chat_Team',
				isOfflineSupportEnabled: true
			}
		);
	};

	if (!window.embedded_svc) {
		var s = document.createElement('script');
		s.setAttribute('src', 'https://leathergroups.my.salesforce.com/embeddedservice/5.0/esw.min.js');
		s.onload = function() {
			initESW(null);
		};
		document.body.appendChild(s);
	} else {
		initESW('https://service.force.com');
	}
</script>

<script type="text/javascript" src="//downloads.mailchimp.com/js/signup-forms/popup/embed.js" data-dojo-config="usePlainJson: true, isDebug: false"></script><script type="text/javascript">require(["mojo/signup-forms/Loader"], function(L) { L.start({"baseUrl":"mc.us1.list-manage.com","uuid":"3a207743b8ba80508fe2aeb12","lid":"66bbd06486"}) })</script>

{/literal}

</body>
</html>
