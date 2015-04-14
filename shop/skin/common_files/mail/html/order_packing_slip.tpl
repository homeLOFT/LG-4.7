{*
48555ac37d172f4c72987d68316aa905b9bd8c36, v1 (xcart_4_7_0), 2015-01-23 00:53:05, order_packing_slip.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}

{config_load file="$skin_config"}

<div class="packing-slip">
    <div class="company-logo">
        <img src="{$ImagesDir}/companyname_small.gif" alt="" />
        {* {$config.Company.company_name} *}
    </div>
    <div class="page-title">
        <h1>{$lng.lbl_packing_slip}</h1>
        <span>{$order.date|date_format:$config.Appearance.datetime_format}</span>
    </div>
    <div class="packing-info">
        <table>
            <tr>
                <td class="company-info">
                    <table>
                        <tr>
                            <td>
                                <strong>{$lng.lbl_address}:</strong>
                            </td>
                            <td>
                                {$config.Company.location_address}, {$config.Company.location_city}<br />
                                {$config.Company.location_zipcode}{if $config.Company.location_country_has_states}, {$config.Company.location_state_name}{/if}<br />
                                {$config.Company.location_country_name}<br />
                                {if $config.Company.company_phone}
                                    {$lng.lbl_phone}: {$config.Company.company_phone}<br />
                                {/if}
                                {if $config.Company.company_fax}
                                    {$lng.lbl_fax}: {$config.Company.company_fax}<br />
                                {/if}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{$lng.lbl_order_date}:</strong>
                            </td>
                            <td>
                                {$order.date|date_format:$config.Appearance.date_format}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{$lng.lbl_order_id}:</strong>
                            </td>
                            <td>
                                {$order.orderid}
                            </td>
                        </tr>
                        {if $order.PO_Number}
                        <tr>
                            <td>
                                <strong>{$lng.lbl_po_number}:</strong>
                            </td>
                            <td>
                                {$order.PO_Number}
                            </td>
                        </tr>
                        {/if}
                    </table>
                </td>
                <td class="customer-info">
                    <table>
                        <tr class="bill-to">
                            <td>
                                <strong>{$lng.lbl_bill_to}:</strong>
                            </td>
                            <td>
                                {if $customer.default_address_fields.title}
                                    {$order.b_title|escape}&nbsp;
                                {/if}
                                {if $customer.default_address_fields.firstname}
                                    {$order.b_firstname|escape}
                                {/if}
                                {if $customer.default_address_fields.lastname}
                                    {$order.b_lastname|escape}<br />
                                {/if}
                                {if $customer.default_address_fields.address}
                                    {$order.b_address|escape}<br />
                                    {if $order.b_address_2}
                                        {$order.b_address_2|escape}<br />
                                    {/if}
                                {/if}
                                {if $customer.default_address_fields.city}
                                    {$order.b_city|escape},
                                {/if}
                                {if $customer.default_address_fields.state}
                                    {$order.b_statename|escape},
                                {/if}
                                {if $customer.default_address_fields.county and $config.General.use_counties eq 'Y'}
                                    {$order.b_countyname|escape},
                                {/if}
                                {if $customer.default_address_fields.zipcode}
                                    {include file="main/zipcode.tpl" val=$order.b_zipcode zip4=$order.b_zip4 static=true},
                                {/if}
                                {if $customer.default_address_fields.country}
                                    {$order.b_country|escape}
                                {/if}
                                {if $customer.default_address_fields.phone and $order.b_phone}
                                    <br />{$order.b_phone|escape}
                                {/if}
                                {if $customer.default_address_fields.fax and $order.s_fax}
                                    <br />{$order.b_fax|escape}
                                {/if}

                                {foreach from=$customer.additional_fields item=v}

                                    {if $v.section eq 'B' and $v.value.B ne ''}
                                        <strong>{$v.title}: </strong>&nbsp;{$v.value.B}<br />
                                    {/if}

                                {/foreach}
                            </td>
                        </tr>
                        <tr class="ship-to">
                            <td>
                                <strong>{$lng.lbl_ship_to}:</strong>
                            </td>
                            <td>
                                {if $customer.default_address_fields.title}
                                    {$order.s_title|escape}&nbsp;
                                {/if}
                                {if $customer.default_address_fields.firstname}
                                    {$order.s_firstname|escape}
                                {/if}
                                {if $customer.default_address_fields.lastname}
                                    {$order.s_lastname|escape}<br />
                                {/if}
                                {if $customer.default_address_fields.address}
                                    {$order.s_address|escape}<br />
                                    {if $order.s_address_2}
                                        {$order.s_address_2|escape}<br />
                                    {/if}
                                {/if}
                                {if $customer.default_address_fields.city}
                                    {$order.s_city|escape},
                                {/if}
                                {if $customer.default_address_fields.state}
                                    {$order.s_statename|escape},
                                {/if}
                                {if $customer.default_address_fields.county and $config.General.use_counties eq 'Y'}
                                    {$order.s_countyname|escape},
                                {/if}
                                {if $customer.default_address_fields.zipcode}
                                    {include file="main/zipcode.tpl" val=$order.s_zipcode zip4=$order.s_zip4 static=true},
                                {/if}
                                {if $customer.default_address_fields.country}
                                    {$order.s_country|escape}
                                {/if}
                                {if $customer.default_address_fields.phone and $order.s_phone}
                                    <br />{$order.s_phone|escape}
                                {/if}
                                {if $customer.default_address_fields.fax and $order.s_fax}
                                    <br />{$order.s_fax|escape}
                                {/if}

                                {foreach from=$customer.additional_fields item=v}

                                    {if $v.section eq 'B' and $v.value.S ne ''}
                                        <strong>{$v.title}: </strong>&nbsp;{$v.value.S}<br />
                                    {/if}

                                {/foreach}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="packing-details">
        <table>
            <tr>
                <th>{$lng.lbl_sku}</th>
                <th>{$lng.lbl_product}</th>
                <th>{$lng.lbl_unit_type}</th>
                <th>{$lng.lbl_order_quantity}</th>
                <th>{$lng.lbl_ship_quantity}</th>
            </tr>

            {assign var="items_total" value=0}

            {foreach from=$products item=product}

            <tr>
                <td>{$product.productcode}</td>
                <td>
                    <span>{$product.product}</span>
                    {if $product.product_options ne '' and $active_modules.Product_Options}
                        <table summary="{$lng.lbl_product_options|escape}" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><strong>{$lng.lbl_options}:</strong></td>
                                <td>
                                    {include file="modules/Product_Options/display_options.tpl" options=$product.product_options options_txt=$product.product_options_txt force_product_options_txt=$product.force_product_options_txt}
                                </td>
                            </tr>
                        </table>
                    {/if}
                </td>
                <td>{$lng.lbl_item}</td>
                <td>{$product.amount}</td>
                <td></td>
            </tr>

            {assign var="items_total" value=$items_total + $product.amount}

            {/foreach}

            <tr>
                <td></td>
                <td></td>
                <td><strong>{$lng.lbl_total}:</strong></td>
                <td>{$items_total}</td>
                <td></td>
            </tr>
        </table>
        <div class="customer-message">
            {$lng.lbl_customer_notes}: {$order.customer_notes|nl2br}
        </div>
    </div>
    <div class="thank-you-message">
        {if $config.Company.company_phone}
        <p>
            {$lng.txt_please_contact_customer_service|replace:"#phone_number#":$config.Company.company_phone}
        </p>
        {/if}
        <p><strong>{$lng.txt_thank_you_for_business}</strong></p>
    </div>
</div>
