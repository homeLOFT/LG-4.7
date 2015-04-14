{*
fafb6e680e507242d4d3dc09c88872c868cb70b4, v1 (xcart_4_5_0), 2012-04-06 10:32:27, product_modify.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:

TaxCloud module: TIC (Taxability Information Code)
*}

{if $config.TaxCloud.taxcloud_force_default_tic ne "Y"}

<tr>
  {if $geid ne ''}<td class="TableSubHead">&nbsp;</td>{/if}
  <td colspan="2"><hr /></td>
</tr>
<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="fields[taxcloud_data][taxcloud_tic]" /></td>{/if}
  <td class="FormButton">{include file="main/tooltip_js.tpl" title=$lng.taxcloud_lbl_tic text=$lng.txt_taxcloud_tic_descr id="taxcloud_tooltip" sticky=true}</td>
  <td class="ProductDetails">
    <input type="hidden" id="taxcloudTIC" name="taxcloud_data[taxcloud_tic]" value="{$product.taxcloud_tic|default:$config.TaxCloud.taxcloud_default_tic}" />
    <input type="text" id="taxcloudTaxClassText" />
  </td>
</tr>

<script type="text/javascript">

{literal}
  $(document).ready(function() {
    // use this to reset a single form
    $("form[name=modifyform]").submit(function() {
      $("#taxcloudTIC").val($("#taxcloudTaxClassText").val());
    });
  });
{/literal}

  //currentTic must be declared/set, even if TIC has not already been specified.
  var currentTic = "{$product.taxcloud_tic|default:$config.TaxCloud.taxcloud_default_tic}";

  // CSS Class to by used for links
  var linkClass = 'NeedHelpLink';

  // CSS to be used by the drop-down-menu list
  var dropdownListCss = "font-size:small;background-color:#ECECFF;border:solid 1px #BBBBFF;font-family:'Trebuchet MS', Arial, Helvetica, sans-serif;";

  // CSS to be used to display TIC selection path
  var resultsListCss = "color:#666666; text-decoration:none; cursor:default;"

{literal}
  //the ID of the HTML form field to be replaced
  var fieldID = "taxcloudTaxClassText"; 

  (function () {
    var tcJsHost = (("https:" == document.location.protocol) ? "https:" : "http:");
    var ts = document.createElement('script');
    ts.type = 'text/javascript';
    ts.async = true;
    ts.src = tcJsHost + '//taxcloud.net/jquery.tic2.public.js';
    var t = document.getElementsByTagName('script')[0];
    t.parentNode.insertBefore(ts, t);
  })();

{/literal}
</script>

{/if}

