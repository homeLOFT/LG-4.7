{*
fd8c2fe2c0ee6c51f6b52254e568530b77acb4f9, v25 (xcart_4_6_4), 2014-04-29 11:12:49, onload_js.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=onload_js}

{if $smarty.get.is_install_preview}
{literal}
/*  Fix problem with refreshing of the skin preview   */
/*  during skin installation */
var _ts = new Date();
$('link').each(function(){
$(this).attr('href', this.href + '?' + _ts.valueOf());
});
{/literal}
{/if}

{if $config.SEO.clean_urls_enabled eq "Y"}
{literal}
/*  Fix a.href if base url is defined for page */
function anchor_fix() {
var links = document.getElementsByTagName('A');
var m;
var _rg = new RegExp("(^|" + self.location.host + xcart_web_dir + "/)#([\\w\\d_-]+)$");
for (var i = 0; i < links.length; i++) {
  if (links[i].href && (m = links[i].href.match(_rg))) {
    links[i].href = 'javascript:void(self.location.hash = "' + m[2] + '");';
  }
}
}

if (window.addEventListener)
window.addEventListener("load", anchor_fix, false);

else if (window.attachEvent)
window.attachEvent("onload", anchor_fix);
{/literal}
{/if}

{literal}
function initDropOutButton() {

  if ($(this).hasClass('activated-widget'))
    return;

  $(this).addClass('activated-widget');

  var dropOutBoxObj = $(this).parent().find('.dropout-box');

  /* Process the onclick event on a dropout button  */
  $(this).click(
    function(e) {
      e.stopPropagation();
      $('.dropout-box').removeClass('current');
      dropOutBoxObj
        .toggle()
        .addClass('current');
      $('.dropout-box').not('.current').hide();
      if (dropOutBoxObj.offset().top + dropOutBoxObj.height() - $('#center-main').offset().top - $('#center-main').height() > 0) {
        dropOutBoxObj.css('bottom', '-2px');
      }
    }
  );

  /* Click on a dropout layer keeps the dropout content opened */
  $(this).parent().click(
    function(e) {
      e.stopPropagation();
    }
  );

  /* shift the dropout layer from the right hand side  */
  /* if it's out of the main area */
  var borderDistance = ($("#center-main").offset().left + $("#center-main").outerWidth()) - ($(this).offset().left + dropOutBoxObj.outerWidth());
  if (!isNaN(borderDistance) && borderDistance < 0) {
    dropOutBoxObj.css('left', borderDistance+'px');
  }

}

$(document).ready( function() {
  $('body').click(
    function() {
      $('.dropout-box')
        .filter(function() { return $(this).css('display') != 'none'; } )
        .hide();
    }
  );
  $('div.dropout-container div.drop-out-button').each(initDropOutButton);
}
);
{/literal}

{literal}
$(document).ready( function() {

$('form').not('.skip-auto-validation').each(function() {
  applyCheckOnSubmit(this);
});

$(document).on(
  'click','a.toggle-link', 
  function(e) {
    $('#' + $(this).attr('id').replace('link', 'box')).toggle();
  }
);

});
{/literal}

{literal}
if (products_data == undefined) {
var products_data = [];
}
{/literal}

var txt_are_you_sure = '{$lng.txt_are_you_sure|wm_remove|escape:"javascript"}';

{/capture}
{load_defer file="onload_js" direct_info=$smarty.capture.onload_js type="js" queue="1"}

{if $active_modules.EU_Cookie_Law ne ""}
{include file="modules/EU_Cookie_Law/init.tpl"}
{/if}

{if $active_modules.Product_Options ne ""}
  {load_defer file="modules/Product_Options/func.js" type="js"}
{/if}

{load_defer file="js/check_quantity.js" type="js"}
{if $products or $free_products or $cat_products}
{if $active_modules.Feature_Comparison and not $printable and $products_has_fclasses}
{load_defer file="modules/Feature_Comparison/products_check.js" type="js"}
{/if}
{/if}

