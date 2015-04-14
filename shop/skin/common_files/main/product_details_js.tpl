{*
895a6757fade3bcc31db48359a3044a8b2afc388, v6 (xcart_4_5_0), 2012-04-06 13:01:27, product_details_js.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var requiredFields = [
  ['productcode', "{$lng.lbl_sku|strip_tags|wm_remove|escape:javascript}", false],
  ['product', "{$lng.lbl_product_name|strip_tags|wm_remove|escape:javascript}", false],
  {if $config.SEO.clean_urls_enabled eq "Y"} ['clean_url', "{$lng.lbl_clean_url|strip_tags|wm_remove|escape:javascript}", false], {/if}
  ['descr', "{$lng.lbl_description|strip_tags|wm_remove|escape:javascript}", false],
  ['product_lng[product]', "{$lng.lbl_description|strip_tags|wm_remove|escape:javascript}", false]
];

{literal}
if ($.browser.mozilla) {
  $.event.add(
    window,
    "load",
    function() {
      var fld = $('select[name="membershipids[]"]', document.modifyform).parents('tr').get(0);
      if (fld) {
        fld.style.display = 'none';
        setTimeout(
          function() {
            fld.style.display = '';
          },
          200
        );
      }
    }
  );
}

function ChangeTaxesBoxStatus(s) {
  if (s.form.elements.namedItem('taxes[]'))
    s.form.elements.namedItem('taxes[]').disabled = s.options[s.selectedIndex].value == 'Y';
}

function switchPDims(c) {
  var names = ['length', 'width', 'height', 'separate_box', 'items_per_box'];
  for (var i = 0; i < names.length; i++) {
    var e = c.form.elements.namedItem(names[i]);
    if (e) {
      e.disabled = !c.checked;

      // "Ship in a separate box" depends on "Use the dimensions of this product for shipping cost calculation" bt:84873
      if (names[i] == 'separate_box' && !c.checked)
        e.checked = false;
    }  
  }

  switchSSBox(c.form.elements.namedItem('separate_box'));
}

function switchSSBox(c) {
  if (!c)
    return;

  c.form.elements.namedItem('items_per_box').disabled = !c.checked || !c.form.elements.namedItem('small_item').checked;
}
{/literal}
//]]>
</script>
