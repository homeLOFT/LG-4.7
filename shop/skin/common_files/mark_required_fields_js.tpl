{*
13cd7f2a4eeeb071125e384d732532375d012673, v3 (xcart_4_4_0_beta_2), 2010-06-08 06:17:37, mark_required_fields_js.tpl, igoryan 
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){ldelim}

  markEmptyFields($('form[name={$form}]'));
  
  {if $errfields ne ''}
    {foreach from=$errfields key=f item=v}
      $('#{$f}').addClass('err');
    {/foreach}
  {/if}
{rdelim});
//]]>
</script>
