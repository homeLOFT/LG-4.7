{*
a6b2c90ab352d713d9274c023a3e1a53d2657239, v18 (xcart_4_7_1), 2015-03-05 10:04:06, help.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<li class="menu-item help">
    <i class="icon fa fa-question-circle"></i>
    <a href="http://help.x-cart.com/index.php?title=X-Cart:User_manual_contents&amp;utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
        <span>{$lng.lbl_help}</span>
    </a>
  <div class="children-block">
    <ul>
        <li class="menu-item">
            <a href="http://help.x-cart.com/index.php?title=X-Cart:FAQs&amp;utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_xcart_faqs}</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="http://help.x-cart.com/index.php?title=X-Cart:User_manual_contents&amp;utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_xcart_manuals}</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="http://forum.x-cart.com/?utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_community_forums}</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="https://bt.x-cart.com/bug_report_page.php?project_id=54&amp;product_version={$config.version}&amp;utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_post_bug}</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="http://ideas.x-cart.com/forums/32109-x-cart-classic-4-x?utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_suggest_feature}</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="http://www.x-cart.com/license-agreement-classic.html?utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_license_agreement}</span>
            </a>
        </li>
        {if $shop_evaluation}
        <li class="menu-item">
            <a href="http://www.x-cart.com/purchasing_shopping_cart_software.html?utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_purchase_paid_license}</span>
            </a>
        </li>
        {/if}
        <li class="menu-item support-link">
            <a href="https://secure.x-cart.com/customer.php?area=center&amp;target=customer_info&amp;utm_source=xcart&amp;utm_medium=help_menu_link&amp;utm_campaign=help_menu" target="_blank">
                <i class="icon fa fa-external-link"></i>
                <span>{$lng.lbl_get_support_assistance}</span>
            </a>
        </li>
    </ul>
  </div>
</li>
