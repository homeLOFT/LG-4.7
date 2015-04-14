{*
fafb6e680e507242d4d3dc09c88872c868cb70b4, v1 (xcart_4_5_0), 2012-04-06 10:32:27, exemption_js.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:

TaxCloud module: JS for customer exemption dialog

*}

{literal}
<script type="text/javascript">

  var certLink = 'xmptlink';
  var ajaxLoad = true;
  var reloadWithSave = true;
  var certSelectUrl = 'cart.php?mode=checkout&action=taxcloud_select';
  var merchantNameForCert = "{/literal}{$config.Company.company_name}{literal}";

  // Save a new exemption cert
  var saveCertUrl = 'cart.php?mode=checkout&action=taxcloud_save';

  // List existing exemption certs for customer
  var certListUrl = 'cart.php?mode=checkout&action=taxcloud_list';

  // Delete exemption certificate
  var certRemoveUrl = 'cart.php?mode=checkout&action=taxcloud_remove';

  // Reload the cart/checkout page after selecting a certificate (will force a new sales tax lookup with the exemption cert applied so rate will return zero)
  // If set to false, the script will not ask the customer to reload.
  var withConfirm = false;

  // Use this to pass the certificate id to the server for any reason
  var hiddenCertificateField = "taxcloud_exemption_certificate";

  // Please do not edit the following line.
  var clearUrl = "?time="+new Date().getTime().toString(); // prevent caching

  (function () {
    var ts = document.createElement('script');
    ts.type = 'text/javascript'; ts.async = true;

{/literal}
    ts.src = '{$current_location}/skin/common_files/modules/TaxCloud/cert.min.js' + clearUrl; var t = document.getElementsByTagName('script')[0]; t.parentNode.insertBefore(ts, t);
{literal}

  })();

</script>
{/literal}

