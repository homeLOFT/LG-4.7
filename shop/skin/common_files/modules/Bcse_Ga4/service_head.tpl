{*
 * +-----------------------------------------------------------------------+
 * | BCSE Google Analytics 4                                               |
 * +-----------------------------------------------------------------------+
 * | Copyright (c) 2022 BCSE LLC. dba BCS Engineering                      |
 * +-----------------------------------------------------------------------+
 * |                                                                       |
 * | BCSE Google Analytics 4 is subject for version 2.0                    |
 * | of the BCSE proprietary license. That license file can be found       |
 * | bundled with this package in the file BCSE_LICENSE. A copy of this    |
 * | license can also be found at                                          |
 * | http://www.bcsengineering.com/license/BCSE_LICENSE_2.0.txt            |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
*}
{if $bcse_ga4_enabled}
<!-- Global site tag (gtag.js) - Google Analytics -->
<script>
  {literal}
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  {/literal}

  {if $bcse_ga4_debug}
  {literal}
  gtag('config', '{/literal}{$bcse_ga4_account}{literal}', {'debug_mode': true});
  {/literal}
  {else}
  gtag('config', '{$bcse_ga4_account}');
  {/if}
</script>

{load_defer type="js" file="modules/Bcse_Ga4/js/events.js"}
{/if}
