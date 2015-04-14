{********************************************************************************
| Dynamic Product Tabs
| Copyright WebsiteCM Software Inc.
| All rights reserved.
| License: http://www.websitecm.com/downloads/license-agreement.pdf
********************************************************************************
| Tab Administration
********************************************************************************}


{if $wcmXCartVersion lt "4.3" AND $active_modules.HTML_Editor}
	{include file="modules/HTML_Editor/editor.tpl"}
{/if}

<script type="text/javascript">
<!--
var requiredFields = [
  ['name', "{$lng.lbl_dpt_name|escape:javascript}", false],
  ['content', "{$lng.lbl_dpt_content|escape:javascript}", false]
]
-->
</script>

{include file="check_required_fields_js.tpl"}

<h1>{$lng.lbl_dpt_mod_title}</h1>

{*******************************************************************************
| Listing of tabs
********************************************************************************}

{capture name="dialog"}

    {* Listing of tabs *}
    {if $WCMtabs eq ""}
        {$lng.lbl_dpt_no_tabs}
    {else}
        <table cellpadding="6" cellspacing="1" border="0">
        <tr class="TableHead">
            <td>{$lng.lbl_dpt_name}</td>
            <td>{$lng.lbl_dpt_order}</td>
            <td>{$lng.lbl_dpt_related_module}</td>
            <td colspan="2">{$lng.lbl_dpt_status}</td>
        </tr>
        {section name="tab" loop=$WCMtabs}
        <tr class="{cycle values="'',TableSubHead"}">
            <td valign="top">
            	 <a href="dynamic_product_tabs_admin.php?tabid={$WCMtabs[tab].tabid}&page={if $smarty.get.page gt 0}{$smarty.get.page|escape}{/if}#form">{$WCMtabs[tab].name|escape:'html'|truncate:75}</a>
            </td>
            <td valign="top">
             {$WCMtabs[tab].tab_order}
            </td>
            <td valign="top">
             {if $WCMtabs[tab].display_name eq "custom_send_to_friend"}{$lng.lbl_send_to_friend}{else}{$WCMtabs[tab].display_name}{/if}
            </td>
               <td valign="top">
                {if $WCMtabs[tab].status eq 1}{$lng.lbl_enabled}{else}{$lng.lbl_disabled}{/if}
            </td>            
            <td valign="top">
                <a onclick="return confirmDelete();" href="dynamic_product_tabs_admin.php?tabdelete={$WCMtabs[tab].tabid}&tabid={if $smarty.get.tabid gt 0}{$smarty.get.tabid|escape}{/if}">X</a>
            </td>
         
        </tr>
        {/section}
    
        </table>
        
    
    {/if}
    
    <br /><br />{$lng.lbl_dpt_module_settings}

{/capture}
{include file="dialog.tpl" content=$smarty.capture.dialog title=$lng.lbl_dpt_tabs_admin extra='width="100%"'}

<br />
<br />

{*******************************************************************************
| Add/Modify existing tab
********************************************************************************}

<a name="form"></a>

{capture name="dialog"}

<form name="updateform" action="dynamic_product_tabs_admin.php" method="POST" onsubmit="javascript: return checkRequired(requiredFields);">
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="tabid" value="{$WCMtab.tabid|escape}" />

<table border="0" cellpadding="4" cellspacing="2">

<tr>
<td valign="top">{$lng.lbl_dpt_tab_name}:</td>
<td valign="top"><font class="Star">*</font></td>
<td valign="top">
	<input type="text" id="name" name="name" maxlength="128" size="32" value="{$WCMtab.name}" />
</td>
</tr>

<tr>
<td valign="top">{$lng.lbl_dpt_content}:</td>
<td valign="top"><font class="Star">*</font></td>
<td valign="top">
	{include file="main/textarea.tpl" name="content" cols=100 rows=10 data=$WCMtab.content width="400px"}
    <div style="margin-top: 4px; width: 400px; padding: 4px; border: 1px solid #CCCCCC; background-color: #EAEAEA;">
    {$lng.lbl_dpt_module_content_note}
    </div>
</td>
</tr>

<tr>
<td valign="top">{$lng.lbl_dpt_order}:</td>
<td valign="top"></td>
<td valign="top">
		<input type="text" id="tab_order" name="tab_order" maxlength="11" size="11" value="{$WCMtab.tab_order}" />
</td>
</tr>

<tr>
<td valign="top">{$lng.lbl_dpt_related_module}:</td>
<td valign="top"></td>
<td valign="top">
<select name="module_name">
    <option value=""{if $WCMtab.module_name eq ""} selected{/if}>No Associated Module</option>
    {section name=names loop=$WCMmodules}
           <option value="{$WCMmodules[names].module_name}"{if $WCMtab.module_name eq $WCMmodules[names].module_name} selected{/if}>{$WCMmodules[names].display_name} {if $WCMmodules[names].active ne "Y"}[{$lng.lbl_disabled}]{/if}</option>
    {/section}
</select>
<div style="margin-top: 4px; width: 220px; padding: 4px; border: 1px solid #CCCCCC; background-color: #EAEAEA;">
{$lng.lbl_dpt_module_name_note}
</div>
</td>
</tr>


<tr>
<td valign="top">{$lng.lbl_dpt_status}:</td>
<td valign="top"></td>
<td valign="top">
    <select name="status" id="status" />
    <option value="1"{if $WCMtab.status eq "1"} selected{/if}>{$lng.lbl_enabled}</option>
    <option value="0"{if $WCMtab.status eq "0"} selected{/if}>{$lng.lbl_disabled}</option>
    </select>
</td>
</tr>

<tr>
<td valign="top" colspan="3" class="TableHead">
	<input type="submit" value="{$lng.lbl_save}" name="{$lng.lbl_save|escape}">
</td>
</tr>

</table>

</form>

{* Format title depending on if adding or modifying for admin clarification *}
{if $WCMtab.tabid gt 0}
	{assign var="tempHeader" value=$lng.lbl_dpt_modify}
{else}
	{assign var="tempHeader" value=$lng.lbl_dpt_add}
{/if}

{/capture}
{include file="dialog.tpl" content=$smarty.capture.dialog title="`$tempHeader` `$lng.lbl_dpt_tab`" extra='width="100%"'}

{* Admin code to confirm deletions *}
{literal}
<script language="javascript">
<!--
function confirmDelete()
{
	var agree=confirm("{/literal}{$lng.lbl_dpt_confirm_delete}{literal}");
	if (agree) return true ;
	else return false ;
}
// -->
</script>
{/literal}

