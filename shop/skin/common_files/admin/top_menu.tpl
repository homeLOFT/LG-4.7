{*
ff29a71cdf48354b4babee44be8b67e407c3e642, v5 (xcart_4_6_5), 2014-08-29 16:56:37, top_menu.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.General.shop_closed eq "Y"}
    <li class="link-item your-store closed">
        <a href="{$http_location}/home.php?shopkey={$config.General.shop_closed_key}" target="_blank">
            <i class="icon fa fa-desktop"></i>
            <span>{$lng.lbl_your_storefront} <span class="closed">({$lng.lbl_closed})</span></span>
        </a>
        {include file="admin/storefront_status.tpl"}
    </li>
{else}
    <li class="link-item your-store open">
        <a href="{$http_location}" target="_blank">
            <i class="icon fa fa-desktop"></i>
            <span>{$lng.lbl_your_storefront}</span>
        </a>
        {include file="admin/storefront_status.tpl"}
    </li>
{/if}

{include file="admin/help.tpl"}

{include file="admin/quick_search.tpl"}

<li class="menu-item account">
    <a href="#" class="list">
        <span>{$lng.lbl_profile}</span>
    </a>
    <div class="children-block">
        <ul>
            {if $login ne '' and $usertype eq 'B'}
            <li class="menu-item text partner">
                <span>{$lng.lbl_your_partner_id}: <strong>{$logged_userid}</strong></span>
            </li>
            {/if}
            <li class="menu-item text login">
                <span>{$login}</span>
            </li>
            <li class="menu-item ">
                <a href="{$current_area}/register.php?mode=update">
                    <span>{$lng.lbl_profile_details}</span>
                </a>
            </li>
            <li class="menu-item logoff">
                <a href="login.php?mode=logout">
                    <span>{$lng.lbl_logoff}</span>
                </a>
            </li>
        </ul>
    </div>
</li>
