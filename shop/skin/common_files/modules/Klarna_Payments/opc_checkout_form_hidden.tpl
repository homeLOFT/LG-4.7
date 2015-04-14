{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, opc_checkout_form_hidden.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<input type="hidden" name="user_ssn" id="place_user_ssn" value="{$cart.klarna_ssn}" />
<input type="hidden" name="user_gender" id="place_user_gender" value="{$cart.user_gender}" />
<input type="hidden" name="user_house_number" id="place_user_house_number" value="{$cart.user_house_number}" />
<input type="hidden" name="user_house_number_ext" id="place_user_house_number_ext" value="{$cart.user_house_number_ext}" />
<input type="hidden" name="de_policy" id="place_de_policy" value="{if $config.Klarna_Payments.user_country eq 'de'}N{else}Y{/if}" />
