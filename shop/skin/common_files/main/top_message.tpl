{*
f2c96ddb72c96605401a2025154fc219a84e9e75, v1 (xcart_4_6_1), 2013-08-19 12:16:49, top_message.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $top_message.content ne ''}
<script type="text/javascript">
//<![CDATA[
  $(document).ready(
    function() {ldelim}
      {if !$top_message.in_popup}
        showTopMessage('{$top_message.content|escape:javascript}', '{$top_message.type|lower|default:"i"}', '{$top_message.anchor}');
      {else}
        xAlert('{$top_message.content|escape:javascript}', '', '{$top_message.type}');
      {/if}
    {rdelim}
  );
//]]>
</script>
{/if}
