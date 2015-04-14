{* f59133b7b20d358a6fd0fbe8cb159553d7446418, v2 (xcart_4_6_0), 2013-05-13 16:49:46, configuration.tpl, aim *}
<tr style="display: none;">
  <td width="30" id="fb_init_head">
    {if $active_modules.HTML_Editor && $config.version lt 4.3}
      {include file="modules/HTML_Editor/editor.tpl"}
    {/if}
    <style type="text/css">
      {include file="modules/fCommerce_Go/admin/main.css"}
    </style>
    <script type="text/javascript">
      var jQ = false;
      function initJQ () {ldelim}
      if(typeof(jQuery) == 'undefined') {ldelim}
      if (!jQ) {ldelim}
      var s = document.createElement('script');
      s.setAttribute('src', '{$protocol}://code.jquery.com/jquery.min.js');
      s.setAttribute('type', 'text/javascript');
      window.document.getElementsByTagName('head')[0].appendChild(s);
      jQ = true;
      {rdelim}
        setTimeout('initJQ()', 50);
      {rdelim} else {ldelim}
      {include file="modules/fCommerce_Go/admin/func.js"}
      {rdelim}
      {rdelim}
        initJQ();
    </script>
  </td>
  <td width="60%"> &nbsp; </td>
  <td width="40%"> &nbsp; </td>
</tr>

<tr>
  <td colspan="3" class="TableSeparator">

    {capture name="dialog"}

      {if $config.version gte 4.3}
        <h2 style="font-size: 16px;">{$lng.lbl_fb_adm_module_note}</h2>
      {/if}

      <div class="module-plan-note">
        {$lng.txt_fb_adm_module_plan_note}
      </div>

      <table class="module-plan-buttons">
        <tr>
          <td style="padding: 0 25px 0 0;" class="main-button">

            {assign var="subscribe_url" value=$current_location|@urlencode}

            {if $config.version gte 4.4}
              <button class="big-main-button subscribe-button" type="button" onclick="javascript: window.location.href='http://connector.qtmsoft.com/subscribe.php?url={$subscribe_url}&plan=1073741983&adm=Y';">{$lng.lbl_fb_adm_subscribe}</button>
            {else}
              {include file="buttons/button.tpl" button_title=$lng.lbl_fb_adm_subscribe href="http://connector.qtmsoft.com/subscribe.php?url=`$subscribe_url`&plan=1073741983&adm=Y" style='button'}
            {/if}

          </td>
          <td style="padding: 0;">
            {include file="buttons/button.tpl" button_title=$lng.lbl_fb_adm_compare_plans href="http://www.x-cart.com/f-commerce-go.html" target="_blank"}
          </td>
        </tr>
      </table>

    {/capture}
    {if $config.version lt 4.3}
      {include file="dialog_message.tpl" title=$lng.lbl_fb_adm_module_note alt_content=$smarty.capture.dialog extra='width="100%"' newid="dialog_fb_module_note"}
    {else}
      {include file="location.tpl" location="" alt_content=$smarty.capture.dialog extra='width="100%"' newid="dialog_fb_module_note" alt_type="I" image_none=""}
    {/if}

    <h2 class="fb-shop-header" style="padding-top: 0px;">{$lng.lbl_fb_adm_select_categories}</h2>
  </td>
</tr>

<tr>
  <td nowrap="nowrap">
    <b>{$lng.opt_products_per_page}</b>
  </td>
  <td colspan="2">
    <input type="text" size="5" name="fb_shop_config[per_page]" value="{$fb_shop_config.per_page|formatnumeric|default:6}" />
  </td>
</tr>

<tr>
  <td nowrap="nowrap">
    <b>{$lng.lbl_fb_adm_category_descr}</b>
  </td>
  <td colspan="2">
    <input type="checkbox" name="fb_shop_config[show_cat_descr]"{if $fb_shop_config.show_cat_descr eq 'Y'} checked="checked"{/if} value="Y" />
  </td>
</tr>

<tr>
  <td colspan="3">
    {if $fb_categories}

      {strip}
        {if $has_sublevels}
          <div class="fb-shop-check-line">
            <a href="javascript: void(0);" class="expand-all">{$lng.lbl_fb_adm_expand_all}</a>&nbsp;/&nbsp;
            <a href="javascript: void(0);" class="collapse-all">{$lng.lbl_fb_adm_collapse_all}</a>
          </div>
        {/if}
      {/strip}

      {include file="modules/fCommerce_Go/admin/fb_categories.tpl" level=0 roots=0 categories=$fb_categories}

    {else}

      {$lng.txt_no_categories}

    {/if}
  </td>
</tr>
<tr>
  <td colspan="3">
    <a name="categories_note"></a>
    <div class="div-note categories-note">
      *<b>{$lng.lbl_note}:</b>
      {$lng.txt_fb_adm_categories_note|substitute:'current_location':$current_location}
    </div>
  </td>
</tr>

<tr>
  <td colspan="3" class="TableSeparator">
    <h2 class="fb-shop-header">{$lng.lbl_fb_adm_advanced_settings}</h2>
  </td>
</tr>

<tr>
  <td colspan="3" class="TableSeparator">
    <h3 class="fb-shop-header">{$lng.lbl_fb_adm_configure_homepage}</h3>
  </td>
</tr>

{if $active_modules.Bestsellers ne ''}
  <tr>
    <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_show_bestsellers}</b> </td>
    <td colspan="2"> <input type="checkbox" name="fb_shop_config[bestsellers]"{if $fb_shop_config.bestsellers eq 'Y'} checked="checked"{/if} value="Y" /> </td>
  </tr>

{/if}

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_show_featureds}</b> </td>
  <td colspan="2"> <input type="checkbox" name="fb_shop_config[featured]"{if $fb_shop_config.featured eq 'Y'} checked="checked"{/if} value="Y" /> </td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_show_cat_thumbs}</b> </td>
  <td colspan="2"><input type="checkbox" name="fb_shop_config[cat_icons]"{if $fb_shop_config.cat_icons eq 'Y'} checked="checked"{/if} value="Y" /> </td>
</tr>

<tr>
  <td colspan="2" class="TableSeparator">
    <h3 class="fb-shop-header">{$lng.lbl_fb_adm_custom_html_content}</h3>
  </td>
  <td style="text-align: right;">
    {if $all_languages_cnt > 1}
      {$lng.lbl_language}: {include file="main/language_selector_short.tpl" script="configuration.php?option=fCommerce_Go&"}
    {/if}
    <input type="hidden" name="shop_language" value="{$shop_language}" />
  </td>
</tr>

<tr>
  <td colspan="3">
    <div class="fb-shop-texarea-title">
      {$lng.txt_fb_adm_custom_content_title}:
    </div>
    {include file="main/textarea.tpl" name="gpg_key[fb_shop_header_text]" data=$fb_shop_config.header_text cols=45 rows=12 width="100%" btn_rows=4}
  </td>
</tr>

<tr>
  <td colspan="3">
    <div class="fb-shop-texarea-title">
      {$lng.txt_fb_adm_different_content_title}:
    </div>
    {include file="main/textarea.tpl" name="gpg_key[fb_shop_header_text_liked]" data=$fb_shop_config.header_text_liked cols=45 rows=12 width="100%" btn_rows=4}
  </td>
</tr>

<tr>
  <td colspan="3" class="TableSeparator">
    <h3 class="fb-shop-header">{$lng.lbl_fb_adm_images_options}</h3>
  </td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_cat_thumb_width}</b> </td>
  <td colspan="2"><input type="text" size="10" name="fb_shop_config[cat_tmbn_width]" value="{$fb_shop_config.cat_tmbn_width|formatnumeric|default:90}" /> </td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_cat_thumb_height}</b> </td>
  <td colspan="2"><input type="text" size="10" name="fb_shop_config[cat_tmbn_height]" value="{$fb_shop_config.cat_tmbn_height|formatnumeric|default:90}" /> </td>
</tr>

<tr>
  <td colspan="3" class="small-separate"><img src="{$ImagesDir}/spacer.gif"></td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_prod_thumb_width}</b> </td>
  <td colspan="2"><input type="text" size="10" name="fb_shop_config[prod_tmbn_width]" value="{$fb_shop_config.prod_tmbn_width|formatnumeric|default:120}" /> </td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_prd_thumb_height}</b> </td>
  <td colspan="2"><input type="text" size="10" name="fb_shop_config[prod_tmbn_height]" value="{$fb_shop_config.prod_tmbn_height|formatnumeric|default:120}" /> </td>
</tr>

<tr>
  <td colspan="3" class="small-separate"><img src="{$ImagesDir}/spacer.gif"></td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_prod_image_width}</b> </td>
  <td colspan="2"><input type="text" size="10" name="fb_shop_config[prod_image_width]" value="{$fb_shop_config.prod_image_width|formatnumeric|default:200}" /> </td>
</tr>

<tr>
  <td nowrap="nowrap"> <b>{$lng.lbl_fb_adm_prod_image_height}</b> </td>
  <td colspan="2"><input type="text" size="10" name="fb_shop_config[prod_image_height]" value="{$fb_shop_config.prod_image_height|formatnumeric|default:200}" /> </td>
</tr>

<tr>
  <td colspan="3">
    <div class="div-note">
      *<b>{$lng.lbl_note}:</b>
      {$lng.txt_fb_adm_images_note}
    </div>
  </td>
</tr>

<tr>
  <td width="30"> &nbsp; </td>
  <td width="60%"> &nbsp; </td>
  <td width="40%"> &nbsp; </td>
</tr>
