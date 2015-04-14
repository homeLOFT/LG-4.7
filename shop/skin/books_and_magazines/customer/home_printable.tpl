{*
99f49a017eeaa96cf5c4060c7785548523d6ad12, v7 (xcart_4_6_2), 2014-01-15 17:46:03, home_printable.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
<?xml version="1.0" encoding="{$default_charset|default:"utf-8"}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  {include file="customer/service_head.tpl"}


</head>
<body{$reading_direction_tag}{if $body_onload ne ''} onload="javascript: {$body_onload}"{/if} class="printable{foreach from=$container_classes item=c} {$c}{/foreach}">
<div id="page-container">
  <div id="page-container2">


    <div id="content-container">
      <div id="content-container2">
        <div id="center">
          <div id="center-main">
            {if $main eq 'cart' or $main eq 'checkout' or $main eq "order_message" or $main eq "order_message_widget"}
              {include file="customer/evaluation.tpl"}
            {/if}
<!-- central space -->

            {include file="customer/bread_crumbs.tpl"}

            {if $main ne "cart" and $main ne "checkout" and $main ne "order_message" and $main ne "order_message_widget"}
              {if $amazon_enabled}
                {include file="modules/Amazon_Checkout/amazon_top_button.tpl"}
              {/if}
            {/if}

            {if $page_title}
              <h1>{$page_title}</h1>
            {/if}

            {if $active_modules.Special_Offers ne ""}
              {include file="modules/Special_Offers/customer/new_offers_message.tpl"}
            {/if}

            {include file="customer/home_main.tpl"}

<!-- /central space -->

          </div>
        </div>
      </div>

    </div>

  </div>
</div>

    <div id="footer">
      {include file="customer/bottom.tpl"}
    </div>

    <div id="header">
      {include file="customer/head.tpl"}
    </div>


{capture assign=printing_code}
$(document).ready(function(){ldelim}
  window.print();
{rdelim});
{/capture}
{load_defer file="printing_code" direct_info=$printing_code type="js"}

{load_defer_code type="css"}
{load_defer_code type="js"}
</body>
</html>
