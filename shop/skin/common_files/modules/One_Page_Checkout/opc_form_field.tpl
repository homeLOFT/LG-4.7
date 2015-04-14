{*
b78bdf1df5a3ef546258fb1be768abfcd7ed12b3, v4 (xcart_4_6_6), 2014-11-24 18:46:39, opc_form_field.tpl, mixon 
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="field-container{if $fstate eq "disabled"} disabled{/if}">
  <div class="data-name{if $oneline} oneline{/if}">
    {strip}
      <label {if $field ne ''}for="{$field}"{/if}{if $required eq 'Y'} class="data-required"{/if}>{$name}</label>
      {if $required eq 'Y'}<span class="star">*</span>{/if}
    {/strip}
  </div>

  <div class="data-value{if $oneline} oneline{/if}">
    {$content}
  </div>
  {if $oneline}
    <div class="clearing"></div>
  {/if}
</div>
