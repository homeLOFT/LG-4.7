{*
eeb1d23403325d645e279a52171e3a2813494712, v2 (xcart_4_4_4), 2011-08-22 13:37:03, popup_link.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.HTML_Editor.editor eq "ckeditor"}
  {assign var=popup_height value=500}
  {assign var=popup_width value=800}
{elseif $config.HTML_Editor.editor eq "innovaeditor"}
  {assign var=popup_height value=470}
  {assign var=popup_width value=630}
{elseif $config.HTML_Editor.editor eq "tinymce"}
  {assign var=popup_height value=460}
  {assign var=popup_width value=680}
{/if}

<div class="AELinkBox"{if $width} style="width: {$width};"{/if}>
  <a href="javascript:void(0);" onclick="javascript: if (isHTML_Editor) window.open('{$catalogs.admin}/wysiwyg.php?id={$id|escape}','WYSIWYG','width={$popup_width},height={$popup_height},toolbar=no,status=no,scrollbars=yes,resizable=yes,menubar=no,location=no,direction=no'); else if (window.txt_advanced_editor_warning) alert(txt_advanced_editor_warning);">{$lng.lbl_advanced_editor}</a>
</div>

