{*
c863034063dde05a16bb4f2a2984f56cd779cc10, v7 (xcart_4_5_5), 2013-01-28 14:29:28, service_head.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{get_title page_type=$meta_page_type page_id=$meta_page_id}
{include file="customer/meta.tpl"}
{include file="customer/service_js.tpl"}
{include file="customer/service_css.tpl"}

<link rel="shortcut icon" type="image/png" href="{$current_location}/favicon.ico" />

{if $config.SEO.canonical eq 'Y'}
  <link rel="canonical" href="{$current_location}/{$canonical_url}" />
{/if}
{if $config.SEO.clean_urls_enabled eq "Y"}
  <base href="{$catalogs.customer}/" />
{/if}

{if $active_modules.Refine_Filters}
  {include file="modules/Refine_Filters/service_head.tpl"}
{/if}

{if $active_modules.Socialize ne ""}
  {include file="modules/Socialize/service_head.tpl"}
{/if}

{if $active_modules.Lexity ne ""}
  {include file="modules/Lexity/service_head.tpl"}
{/if}

{load_defer_code type="css"}
{load_defer_code type="js"}

<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/common/css/lander.css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script>
$(function () {

    $('#contact-form').validator();

    $('#contact-form').on('submit', function (e) {
        if (!e.isDefaultPrevented()) {
            var url = "/common/php/contact.php";

            $.ajax({
                type: "POST",
                url: url,
                data: $(this).serialize(),
                success: function (data)
                {
                    var messageAlert = 'alert-' + data.type;
                    var messageText = data.message;

                    var alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';
                    if (messageAlert && messageText) {
                        $('#contact-form').find('.messages').html(alertBox);
                        $('#contact-form')[0].reset();
						grecaptcha.reset();
                    }
                }
            });
            return false;
        }
    })
});
</script>
<script src="/common/js/validator.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>  
<!-- Facebook Pixel Code -->
<script>
{literal}
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '520592005064672');
  fbq('track', 'PageView');
  {/literal}
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=520592005064672&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->

{* WCM - Dynamic Product Tabs *}
{if $main eq "product" AND $active_modules.WCM_Dynamic_Product_Tabs}
<script language="javascript" type="text/javascript" src="{$SkinDir}/modules/DynamicProductTabs/jquery.tabs.pack.js"></script>
<script type="text/javascript" language="javascript">
<!--
{literal}
$(function() { $(".wcmtabcontainer").tabs(); });
{/literal} -->
</script>
{* Get the Skin Name and then load the skin using it *} 
<link rel="stylesheet" href="{$SkinDir}/modules/DynamicProductTabs/themes/{$config.WCM_Dynamic_Product_Tabs.color_theme}/jquery.tabs.css" />
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="{$SkinDir}/modules/DynamicProductTabs/jquery.tabs-ie.css" type="text/css" media="projection, screen">
<![endif]-->
{/if}
{* / WCM - Dynamic Product Tabs *}
{literal}<script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"en","siteId":237585,"cookiePolicyInOtherWindow":true,"banner":{"backgroundColor":"#5b8aab"},"cookiePolicyId":716182}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script>{/literal}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/1.0.10/purify.min.js"></script>