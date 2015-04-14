{*
a8e964f2c83e1c84a64a519fd96fbd55fe13fb5d, v4 (xcart_4_6_6), 2014-11-15 17:09:17, payment_form.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" src="https://www.simplify.com/commerce/v1/simplify.js"></script>
{load_defer file="modules/Simplify/lib/jquery.bind-first.min.js" type="js"}
{load_defer file="modules/Simplify/main.js" type="js"}
{load_defer file="css/font-awesome.min.css" type="css"}
<div id="simplify-payment-form"{if $config.Simplify.simplify_testmode eq "Y"}class="test-mode"{/if}>
    <div class="simplify-cc-header">
        <h2>{$lng.lbl_simplify_secure_payment}</h2>
    </div>
    <div class="simplify-cc-content">
        <div style="display: none;">
            <input id="cc-addressCountry" type="hidden" value="{$userinfo.b_country|escape}">
            <input id="cc-addressState" type="hidden" value="{$userinfo.b_state|escape}">
            <input id="cc-addressCity" type="hidden" value="{$userinfo.b_city|escape}">
            <input id="cc-addressLine1" type="hidden" value="{$userinfo.b_address|escape}">
            <input id="cc-addressLine2" type="hidden" value="{$userinfo.b_address_2|escape}">
            <input id="cc-addressZip" type="hidden" value="{$userinfo.b_zip|escape}">
        </div>
        <div>
            <label>{$lng.lbl_simplify_name}: </label>
            <input id="cc-name" type="text" size="40" maxlength="50" autocomplete="off" value="{$userinfo.b_firstname} {$userinfo.b_lastname}" autofocus />
        </div>
        <div>
            <label>{$lng.lbl_simplify_cc_number}: </label>
            <input id="cc-number" type="text" size="40" maxlength="20" autocomplete="off" value="" autofocus />
        </div>
        <div class="simplify-logo">
            <label>{$lng.lbl_simplify_cvs}: </label>
            <input id="cc-cvc" type="text" size="4" maxlength="4" autocomplete="off" value=""/>
        </div>
        <div>
            <label>{$lng.lbl_simplify_expiry_date}: </label>
            <select id="cc-expMonth">
                <option value="01">{$lng.lbl_month_abbr_1}</option>
                <option value="02">{$lng.lbl_month_abbr_2}</option>
                <option value="03">{$lng.lbl_month_abbr_3}</option>
                <option value="04">{$lng.lbl_month_abbr_4}</option>
                <option value="05">{$lng.lbl_month_abbr_5}</option>
                <option value="06">{$lng.lbl_month_abbr_6}</option>
                <option value="07">{$lng.lbl_month_abbr_7}</option>
                <option value="08">{$lng.lbl_month_abbr_8}</option>
                <option value="09">{$lng.lbl_month_abbr_9}</option>
                <option value="10">{$lng.lbl_month_abbr_10}</option>
                <option value="11">{$lng.lbl_month_abbr_11}</option>
                <option value="12">{$lng.lbl_month_abbr_12}</option>
            </select>
            <select id="cc-expYear">
                <option value="14">2014</option>
                <option value="15">2015</option>
                <option value="16">2016</option>
                <option value="17">2017</option>
                <option value="18">2018</option>
                <option value="19">2019</option>
                <option value="20">2020</option>
                <option value="21">2021</option>
                <option value="22">2022</option>
                <option value="23">2023</option>
                <option value="24">2024</option>
                <option value="25">2025</option>
            </select>
        </div>
    </div>
</div>
{*
   The input fields for card data do not have a "name" attribute.
   This is done so the data is not stored on the server when the form is submitted.
   This ensures we do not have to worry about data encryption of card holder data.
*}
{getvar var='simplify_payment_id' func='func_simplify_get_payment_id'}
{if $config.Simplify.simplify_testmode eq "Y"}
    {assign var='simplify_public_key' value=$config.Simplify.simplify_test_public_key}
{else}
    {assign var='simplify_public_key' value=$config.Simplify.simplify_live_public_key}
{/if}
<script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {ldelim}
        new ajax.widgets.simplify('{$simplify_payment_id}', '{$simplify_public_key}');
    {rdelim});
    //]]>
</script>
