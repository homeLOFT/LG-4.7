{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, content.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $top_message}
  {include file="main/top_message.tpl"}
{/if}

{if $main eq 'cart'}

  <div class="checkout-buttons">
    {if $active_modules.POS_System ne "" && $user_is_pos_operator eq "Y"}
      {include file="modules/POS_System/process_order_button.tpl" position="top"}
    {else}
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping style="div_button" href=$stored_navigation_script additional_button_class="checkout-1-button"}
    {if !$std_checkout_disabled and !$amazon_enabled and !$paypal_express_active}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout style="div_button" href="cart.php?mode=checkout" additional_button_class="checkout-3-button"}
    {/if}
    {/if}
  </div>
  <div class="clearing"></div>

  {include file="customer/main/cart.tpl"}

{else}

  {include file="modules/One_Page_Checkout/opc_main.tpl"}

{/if}
<div class="clearing"></div>
<div class="opc_term_display">
<h1>Terms and Conditions</h1>
By using this website, LeatherGroups.com and or/by purchasing products from LeatherGroups.com by phone, or on the LeatherGroups.com website, you the customer agree to become bound by the terms and conditions of this agreement, and this agreement shall govern and control your use of this web site and any and all current or future orders and/or purchases you may make by phone, through or because of this website.
              
<h2>Shipping</h2>
LeatherGroups.com is currently offering free "White Glove" inside delivery in the contiguous 48 United States on all leather furniture orders, except for some items other than leather furniture, or ottomans purchased without other items.  Shipping is currently also free for Ottomans and some items other than leather furniture.  Those items may be shipped ground, freight, or any other method required to complete delivery, and will not include "White Glove" inside delivery, unless otherwise specified. Some of these items may be part of a "White Glove" inside delivery if they are ordered with leather furniture and if they are shipped from the same origin warehouse. Orders outside of the contiguous 48 states will incur an extra charge.  Our carriers may apply an additional charge if the customer fails to be available for a scheduled delivery appointment and those charges will be the responsibility of the customer. "In-Stock" items are usually shipped from the warehouse within 1-3 weeks of payment of the order. Typical time to delivery on "In-Stock" orders is between 3-5 weeks. "In-Stock" orders refer to any product style, in a specific color, that is specifically indicated as "In-Stock" on the product page. Special Orders are usually shipped from the warehouse in approximately 6-8 weeks. Typical time to delivery on Special Orders is 8-10 weeks. Any production and delivery times provided are estimates and are not guaranteed. Special Orders may take additional time to produce and cannot be cancelled after 48 hours from the time of your order. "In-Stock" orders may be cancelled with no charge if the order is cancelled prior to shipment.  Once an "In-Stock" order has shipped from the origin warehouse, a cancellation will result in round trip shipping charges and a 15% re-stock fee being deducted from the customer refund per our Cancellation and Return Policy. LeatherGroups.com cannot be responsible for measuring doorways or determining if items will fit into the customer's home, or any room within the customer's home.  It is the sole responsibility of the customer to determine based on the dimensions provided if the items will fit into the home. On "White Glove" inside deliveries, if delivery to the designated room within the customer's home involves any potential damage to the home or to the furniture, some delivery providers offer the option to sign a damage waiver or the delivery will be made to another area designated by the customer. We cannot accept returns of "Special Order" items if they fail to fit into your home, or room of choice within your home. "In-Stock" items in this situation can be returned under our Cancellation and Return Policy.
<h2>White Glove Delivery</h2>
Items are inspected by the delivery provider prior to leaving the origin warehouse, 
and a team of two delivery professionals will deliver the furniture to the customer. 
White Glove Delivery offered by LeatherGroups.com is defined as: transportation to the customer home from the factory, inside placement of furniture, up to 2nd floor, unwrapping, and basic set-up. Once the items have been picked up by the carrier, inspected, and matched with a route to the customer's location, the carrier will contact the customer to schedule delivery of the items. Time frames 
for deliveries are performed Monday thru Friday during normal business hours. 
Some carriers may offer weekend deliveries, but this is not guaranteed.<br>
<h2>Damaged Items</h2>
Items are quality checked at the warehouse prior to shipment. All items are insured 
during shipment. Items must be inspected upon delivery and any damage must be 
noted on the bill of lading while the delivery professionals are still present. 
Only damaged items may be refused. In the event that any item(s) are damaged, 
they will be repaired or replaced at the sole discretion of LeatherGroups.com. 
In the event of a repair, LeatherGroups.com will pay to repair the damaged item(s). 
In the event of a replacement, LeatherGroups.com will pay for transportation necessary 
to exchange the items. LeatherGroups.com will not issue credits, or replacement 
shipments for damage not noted on the Bill of Lading prior to departure of the 
carrier.
<h2>Cancellation and Return Policy</h2>
<strong>*In-Stock</strong> 
- "In-Stock" orders are any product, in a specific color, that is specifically indicated on that product page as being an "In-Stock" product.  "In-Stock" orders that have not shipped from the origin warehouse may cancelled at no charge. If an "In-Stock" order has shipped from the warehouse, or been received by the customer it can be cancelled or returned. LeatherGroups.com will issue a refund of the purchase 
price excluding round trip shipping costs and a 15% restocking fee. Return Authorization must be obtained from LeatherGroups.com within 72 hours of receipt of the items and prior to sending back any items. The customer is responsible for returning the items in the original packaging and in unused condition. Returned items not in the original packaging or with damage may cause an additional repair, or repackaging fee to be deducted from the customer refund.<br><br>
If an "In-Stock" product arrives damaged, and it cannot be repaired, LeatherGroups.com will pay to return the item and send a replacement to the customer. As part of the terms of the purchase, if the customer decides not to accept repair of 
the item(s) at the expense of LeatherGroups.com, or replacement of the item(s) at the expense of LeatherGroups.com, the item(s) can be returned, however, round trip shipping costs and a 15% re-stock fee will be deducted from the refund per the terms of our Cancellation and Return Policy.<br><br>
If an "In-Stock" order is refused 
because of color, seating firmness, size, or the customer decides they do not 
like the product, our Cancellation and Return Policy for "In-Stock" items will apply. 
Associated shipping costs and re-stock fees will be deducted from the customer 
refund.<br><br>
<strong>*Special Order</strong> - "Special Orders" are defined as any order of any product that is not specifically noted as being in stock in the color chosen by the customer and that LeatherGroups.com has produced to fill a specific customer order. "Special Orders" may only be cancelled within 48 hours of the order and require a non-refundable 50% deposit which will only be refunded if the order is cancelled within the 48 hour grace period. "Special Orders" cannot be returned under our normal Cancellation and Return Policy.<br><br>
If a "Special Order" arrives damaged, and it cannot be repaired, LeatherGroups.com 
will pay to return the item and send a replacement to the customer. As a part of the terms of the purchase, if the customer 
decides not to accept repair of the item(s) at the expense of LeatherGroups.com, or replacement of the item(s) at the expense of LeatherGroups.com, the 
item cannot be returned.<br><br>
If a "Special Order" is refused because of color, seating 
firmness, size, or the customer decides they do not like the product, LeatherGroups.com 
will not accept the item and the customer will be responsible for any additional 
shipping charges incurred. No refunds will be issued for Special Orders.
<h2>Full Grain Leather</h2>
Full Grain leather is leather that is not corrected, meaning that scars and natural markings are not "corrected" or buffed/sanded away as with Corrected Top Grain leather, or other more processed leathers. Natural scars and markings are celebrated as part of the natural beauty of Full Grain leather. Unless a scar affects comfort or is affecting the structural integrity of the leather, we will not repair, issue credits, replace parts or replace furniture for natural scars on full grain leather furniture.
<h2>Dimensions</h2>
All dimensions for our leather furniture are approximate. This is because padded, upholstered furniture can vary from stated dimensions due to the solid wood frames being wrapped in padding and having leather tightly wrapped around them.  Unless dimensions are off by over 2 inches, we will not repair, issue credits, or replace furniture for normal dimensional variances. 
<h2>Color Accuracy</h2>
LeatherGroups.com makes every effort to provide the most accurate pictures of the merchandise sold by LeatherGroups.com. All digital photography used to represent the products sold by LeatherGroups.com are studio shots of the actual models that are sold. Computer monitors can vary in display, and leather is an imperfect product with shade variations a very real possibility. LeatherGroups.com will not be held responsible for matching any colors to your existing decor, or any shade variances inherent with all leather products. Any "In-Stock" Order return due to color is subject to our Cancellation and Return Policy. Special Orders cannot be returned.
<h2>Sales Tax</h2>
LeatherGroups.com is not required to collect sales tax on orders delivered outside of California. California residents will be charged the applicable sales tax rate of the city and county for the location of the delivery.
<h2>Payment</h2>
For "In-Stock" orders, payment in full is required at the time of purchase in order to initiate the order. For "Special Orders" LeatherGroups.com will allow the customer to place a 50% non-refundable deposit, if it is specified to us at the time of the order. Otherwise, LeatherGroups.com reserves the right to charge the entire amount of the order at the time of purchase.
<h2>Warranties</h2>
All products offered through LeatherGroups.com come with the full, standard manufacturer warranty. These warranties vary in length from one year to limited lifetime. All specific details regarding warranties to a particular piece of furniture can be relayed to you by a LeatherGroups.com representative and may be displayed on our product detail pages. Manufacturer warranty begins at the time of delivery of your merchandise. LeatherGroups.com will not be responsible for any indirect, special or consequential damages related to warranty of product, delivery or delivery timing other than specifically described. Manufacturers may dispatch a trained repair or evaluation professional to handle warranty claims, however, the customer is responsible for any transportation charges between customer location and local repair facility, except when indicated in a specific third party warrany product. The manufacturers of products sold at LeatherGroups.com reserve the right to repair, or replace, at their discretion, any pieces included in a warranty claim.
<h3>Copright, Content and Applicable Law</h3>All content included and/or 
used on this Web site, such as logos, text, graphics, photographs, images, and 
software is the property of LeatherGroups.com or its suppliers, and is copyrighted 
unless otherwise noted, and is protected by US and international copyright laws. 
LeatherGroups.com makes no representations or warranties of any kind, expressed 
or implied, as to the operation of this Web site or the information, content or 
materials thereof. The use and browsing on this Web site are at the risk of the 
user. LeatherGroups.com assumes no responsibility, and will not be liable for 
any damages to hardware, or loss of data, as a result of accessing, browsing, 
or downloading from LeatherGroups.com. LeatherGroups.com is created, owned and 
controlled by LeatherGroups.com, a division of homeLOFT Incorporated, in San Diego County, California, USA. As such, the laws of the state of California will govern these disclaimers, terms and conditions 
without giving effect to any principles of conflict of laws, and the venue of 
any action shall only be in San Diego County, California, USA. LeatherGroups.com 
reserves the right to make changes to our web site, LeatherGroups.com, and these 
disclaimers, terms and conditions at any time. The Customer is bound by any such 
revisions and will be responsible for periodic visitation to LeatherGroups.com, 
to review the current terms and conditions.<br>
<br>              If you have additional questions, or require assistance, please 
              call: <strong>1.877.888.SOFA(7632)</strong>
</div>
