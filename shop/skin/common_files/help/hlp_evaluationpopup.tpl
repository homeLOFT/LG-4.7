{*
0daafe84fa66383f0fd3d23b209a953ae433ec7b, v2 (xcart_4_5_3), 2012-09-05 14:47:27, hlp_evaluationpopup.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $shop_evaluation eq "WRONG_DOMAIN"}
  <div class="evaluation-notice">
    {if $txt_reg_wrong_domain}
    {$txt_reg_wrong_domain}
    {else}
    {$lng.txt_reg_wrong_domain|substitute:"license_url":$license_url:"wrong_domain":$wrong_domain:"http_location":$http_location}
    {/if}

  </div>
{else}
  <div class="evaluation-notice">
    <p class="evaluation-notice-title">{$lng.txt_evaluation_notice_title|substitute:'XC_Version':$shop_type}</p>
    {$lng.txt_evaluation_notice}
    <br />
    <br />
    <div class='purchase-license'>{$lng.txt_purchase_license}</div><br />
    <a rel="#register_license_hlp_tooltip" id="register_license" class="NeedHelpLink" title="" href="javascript:void(0);" onclick="javascript: clickMore('register_license');">{$lng.lbl_register_license}</a>
    <div id="register_license_note" style="display: none;">{$lng.txt_register_license_steps|substitute:"http_location":$http_location}</div>
  {*include file="main/tooltip_js.tpl" id='register_license_hlp' cz_index='6000' title=$lng.lbl_register_license text=$lng.txt_register_license_steps|substitute:"http_location":$http_location sticky=true width=550*}<br /><br />
    <span class="license-warning">{$lng.txt_evaluation_notice_warning}</span>
  </div>
  <script type="text/javascript">
  //<![CDATA[

  {literal}
    $("a.simple-button").button();

    function clickMore(id) {
      if (!document.getElementById(id) || !document.getElementById(id+'_note'))
        return false;
      
      $('#'+id+'_note').toggle();
    }

  {/literal}
  //]]>
  </script>
{/if}
