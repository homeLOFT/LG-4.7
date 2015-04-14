{*
a1580ee342f6e555ba19c91abfbcd23548cd4c32, v4 (xcart_4_6_5), 2014-09-01 16:22:07, storefront_status.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $login and $usertype eq 'A' and $need_storefront_link}
    <div class="children-block">
        <ul>
            {if $config.General.shop_closed eq "Y"}
                <li class="link-item frontend-closed">
                    <div class="link-item-block">
                        <span class="storefront-title storefront-closed">{$lng.lbl_storefront_closed}</span>[<a href="{$storefront_link|amp}">{$lng.lbl_open}</a>]
                        <span class="private-link-storefront-block">{$lng.lbl_access_store_via} <a href="{$http_location}/home.php?shopkey={$config.General.shop_closed_key}">{$lng.lbl_private_link}</a></span>
                    </div>
                </li>
            {else}
                <li class="menu-item frontend-opened">
                    <div class="link-item-block">
                        <span class="storefront-title storefront-opened">{$lng.lbl_storefront_open}</span>[<a href="javascript:void(0);" onclick="javascript:if(confirm('{$lng.lbl_open_storefront_warning|wm_remove|escape:'javascript'}'))window.location='{$storefront_link|amp}';">{$lng.lbl_close}</a>]
                    </div>
                </li>
            {/if}
        </ul>
    </div>
{/if}
