{*
cbbf01fde6ad5261122f17ed13914a23bd36a2ef, v6 (xcart_4_6_5), 2014-10-18 12:46:37, buttons_row.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{if ($active_modules.Socialize ne "" and ($detailed or ($matrix and $config.Socialize.soc_plist_matrix eq "Y") or (!$matrix and $config.Socialize.soc_plist_plain eq "Y"))) and (!$ie_ver or $ie_ver gt 6)}

  <div class="{if !$matrix}buttons-row {/if}soc-buttons-row">

    {assign var="href" value=$href|@func_make_clean_url}

    {if $smarty.get.block eq 'buy_now' or $smarty.get.block eq 'product_details'}
      {assign var="ajax_result" value=true}
    {/if}

    {if $config.Socialize.soc_fb_like_enabled eq "Y"}
      <div class="soc-item{if $matrix} top-margin-2{/if}">

        {capture name="fb_like_n_send"}
          <div class="fb-like" data-href="{$href}" data-send="{if $config.Socialize.soc_fb_send_enabled eq "Y"}true{else}false{/if}" data-layout="{if $matrix}box_count{else}button_count{/if}" data-show-faces="false"></div>
        {/capture}

        {if $ajax_result}
          {$smarty.capture.fb_like_n_send}
        <script type="text/javascript">
          //<![CDATA[
              FB.XFBML.parse();
            //]]>
          </script>
        {else}
          <script type="text/javascript">
            //<![CDATA[
            document.write('{$smarty.capture.fb_like_n_send|strip}');
          //]]>
        </script>
        {/if}

      </div>
    {/if}

    {if $config.Socialize.soc_fb_like_enabled eq "N" && $config.Socialize.soc_fb_send_enabled eq "Y"}
      <div class="soc-item{if $matrix} top-margin-42{/if}">

        {capture name="fb_send"}
          <div class="fb-send" data-href="{$href}"></div>
        {/capture}

        {if $ajax_result}
          {$smarty.capture.fb_send}
        <script type="text/javascript">
          //<![CDATA[
              FB.XFBML.parse();
            //]]>
          </script>
        {else}
          <script type="text/javascript">
            //<![CDATA[
            document.write('{$smarty.capture.fb_send|strip}');
          //]]>
        </script>
        {/if}

      </div>
    {/if}

    {if $config.Socialize.soc_tw_enabled eq "Y"}
      <div class="soc-item{if $matrix} top-margin-2{/if}">

        {capture name="tw_share"}
          <a href="http://twitter.com/share" class="twitter-share-button" data-url="{$href}" data-count="{if $matrix}vertical{else}horizontal{/if}"{if $config.Socialize.soc_tw_user_name} data-via="{$config.Socialize.soc_tw_user_name}"{/if}>{$lng.lbl_soc_tweet}</a>
        {/capture}

        {if $ajax_result}
          {$smarty.capture.tw_share}
        <script type="text/javascript">
          //<![CDATA[
              twttr.widgets.load();
          //]]>
        </script>
        {else}
          <script type="text/javascript">
            //<![CDATA[
            document.write('{$smarty.capture.tw_share|strip}');
            //]]>
          </script>
        {/if}

      </div>
      {if $matrix && $config.Socialize.soc_pin_enabled eq "Y" && $config.Socialize.soc_ggl_plus_enabled eq "Y"}
        <div class="clearing"></div>
      {/if}
    {/if}

    {if $config.Socialize.soc_ggl_plus_enabled eq "Y"}
      <div class="soc-item{if $matrix} top-margin-2{/if}">
        
        {capture name="ggl_plus"}
          <div class="g-plusone" data-size="{if $matrix}tall{else}medium{/if}" data-href="{$href}"></div>
        {/capture}
        
        {if $ajax_result}
          {$smarty.capture.ggl_plus}
            <script type="text/javascript">
              //<![CDATA[
                  gapi.plusone.go();
              //]]>
            </script>
        {else}
          <script type="text/javascript">
            //<![CDATA[
            document.write('{$smarty.capture.ggl_plus|strip}');
            //]]>
          </script>
        {/if}

      </div>
    {/if}

    {if $config.Socialize.soc_pin_enabled eq "Y"}
      <div class="soc-item{if $matrix} top-margin-5{/if}">

        {capture name="pinterest"}

          {assign var="image_url" value=$product.image_url|default:$product.tmbn_url}
          {assign var="descr" value=$product.descr}

          <a href="http://pinterest.com/pin/create/button/?url={$href|escape:"url"}&media={$image_url|escape:"url"}&description={$descr|strip|escape:"url"|truncate:500}" class="pin-it-button" count-layout="{if $matrix}vertical{else}horizontal{/if}">{$lng.lbl_soc_pin_it}</a>

        {/capture}
        {if $ajax_result}
          {$smarty.capture.pinterest}
          <script type="text/javascript">
            //<![CDATA[
            pin_it();
            //]]>
          </script>
        {else}
          <script type="text/javascript">
            //<![CDATA[
            document.write('{$smarty.capture.pinterest|strip}');
            //]]>
          </script>
        {/if}
      </div>
    {/if}

    <div class="clearing"></div>
  </div>
  <div class="clearing"></div>

{/if}
