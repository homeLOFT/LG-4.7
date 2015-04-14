{*
273362d2c56ac8a6d6527db18aced1be9d66cf35, v2 (xcart_4_6_4), 2014-06-26 15:51:48, cc_epdq_basic.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>ePDQ Essential</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{$lng.txt_cc_epdq_basic_note|substitute:"http_location":$http_location:"https_location":$https_location}
{capture name=dialog}
    <form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">
        <table cellspacing="10">
            <tr>
                <td>{$lng.lbl_cc_epdq_pspid}:</td>
                <td><input type="text" name="param01" size="32" value="{$module_data.param01|escape}" /></td>
            </tr>
            <tr>
                <td>{$lng.lbl_cc_epdq_basic_passphrase_in}:</td>
                <td><input type="password" name="param02" size="50" value="{$module_data.param02|escape}" /></td>
            </tr>
            <tr>
                <td>{$lng.lbl_cc_epdq_basic_passphrase_out}:</td>
                <td><input type="password" name="param03" size="50" value="{$module_data.param03|escape}" /></td>
            </tr>
            {if $module_data.param04 ne ""}
                {assign var="epdqb_currency" value=$module_data.param04}
            {else}
                {assign var="epdqb_currency" value="GBP"}
            {/if}

            {include file="payments/currencies.tpl" param_name='param04' current=$epdqb_currency}
            <tr>
                <td class="setting-name">{$lng.lbl_cc_testlive_mode}:</td>
                <td>
                    <select name="testmode">
                        <option value="Y"{if $module_data.testmode eq "Y"} selected="selected"{/if}>{$lng.lbl_cc_testlive_test}</option>
                        <option value="N"{if $module_data.testmode eq "N"} selected="selected"{/if}>{$lng.lbl_cc_testlive_live}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>{$lng.lbl_cc_order_prefix}:</td>
                <td><input type="text" name="param05" size="32" value="{$module_data.param05|escape}" /></td>
            </tr>

        </table>
        <br /><br />
        <input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
    </form>
{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
