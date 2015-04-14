{*
0cd6aad2baf5c043df8cab7e5080a1c389009774, v3 (xcart_4_6_1), 2013-07-11 13:41:57, paypal_pec.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

<div class="paypal-pec">
  <h2>{$lng.lbl_paypal_using_pec}</h2>
  <div class="img-box">
    <img src="{$ImagesDir}/paypal_ec.png" alt="" />
    <div class="clearing"></div>
    <form action="payment_methods.php" method="post" name="addpaypalform">
      <input type="hidden" name="mode" value="add_paypal" />
    
      <button type="submit">{$lng.lbl_paypal_add_paypal}</button>
    </form> 
    <a href="javascript:popup('http://www.paypal.com/en_US/m/demo/18077_ec.html',570,365);">{$lng.lbl_paypal_see_quick_demo}</a>

  </div>
  {$lng.lbl_paypal_pec_desc}
  <div class="clearing"></div>
</div>
