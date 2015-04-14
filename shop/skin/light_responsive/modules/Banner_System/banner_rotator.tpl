{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, banner_rotator.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*} 

<div id="banner-system-{$banner_location}">

{foreach from=$banners item=banner}

  {if $banner.content ne ''}

    <div style="overflow: hidden; position: relative;">

      <div id="slideshow{$banner.bannerid}" class="banner-system" style="height: {$banner.height|default:$config.Banner_System.default_banner_height|default:1}px; width: {$banner.width|default:$config.Banner_System.default_banner_width|default:1}px; {if $additional_style}{$additional_style}{/if}">
        {foreach from=$banner.content item=content}
          <div id="slideshow_content{$content.id}{if $content.type eq "I"}_I{/if}">
          {if $content.type eq "I"}
            {if $content.url ne ''}<a href="{$content.url|amp}">{/if}<img style="margin: 0px {if $content.image_x ne ''}{math equation="floor(x/2)-floor(y/2)" x=$banner.width|default:$config.Banner_System.default_banner_width|default:1 y=$content.image_x}px{/if};" src="{$content.image_path|amp}" alt="{$content.alt|escape}" width="{$content.image_x}" height="{$content.image_y}" />{if $content.url ne ''}</a>{/if}
          {else} 
            <div class="content" style="height: {$banner.height|default:$config.Banner_System.default_banner_height|default:1}px; width: {$banner.width|default:$config.Banner_System.default_banner_width|default:1}px;">{$content.info}</div>
          {/if}
          </div>
        {/foreach}
      </div>

      {if $banner.nav eq 'Y'}
        <div id="banner_system_navigation">
          <div id="slideshow_page_info{$banner.bannerid}" class="banner_system_navigation"></div>
          <div class="clearing"></div>
        </div>
      {/if}
    </div>

  {/if}

{/foreach}

</div>

<script type="text/javascript">
//<![CDATA[

  function enableBanners{$banner_location}() {ldelim}
    {foreach from=$banners item=banner}
      {if $banner.content ne ''}
        $('#slideshow_page_info{$banner.bannerid}').html('');
        $('#slideshow{$banner.bannerid}').cycle({ldelim}
          fx: '{if $banner.effect eq 'random'}all{else}{$banner.effect}{/if}'
          {if $config.Banner_System.bs_rotation_time_delay ne ''}
            ,timeout: {assign var='time_delay' value=$config.Banner_System.bs_rotation_time_delay*1000}{$time_delay}
          {/if}
          {if $banner.nav eq 'Y'}
            ,pager: '#slideshow_page_info{$banner.bannerid}'
          {/if}
        {rdelim});
      {/if}
    {/foreach}
  {rdelim}

  var lastBannerContainerWidth{$banner_location} = 0;

  resizeBanners{$banner_location} = function() {ldelim}
  
    var bannerContainer{$banner_location} = $('#banner-system-{$banner_location}').parent();

    $('#slideshow{$banner.bannerid}').cycle('stop');

    if (bannerContainer{$banner_location}.width() != lastBannerContainerWidth{$banner_location}) {ldelim}
      lastBannerContainerWidth{$banner_location} = bannerContainer{$banner_location}.width();

      {foreach from=$banners item=banner}
        {if $banner.content ne ''}
          var bannerSlideshow = $('#slideshow{$banner.bannerid}');
          var origSlideshowWidth = {$banner.width|default:$config.Banner_System.default_banner_width|default:1};
          var origSlideshowHeight = {$banner.height|default:$config.Banner_System.default_banner_height|default:1};

          var newWidth = bannerContainer{$banner_location}.width();
          if (newWidth < origSlideshowWidth) {ldelim}
            var k = newWidth / origSlideshowWidth;
            bannerSlideshow.width(newWidth).height(Math.round(origSlideshowHeight * k));
          {rdelim} else {ldelim}
            var k = 1;
            bannerSlideshow.width(origSlideshowWidth).height(origSlideshowHeight);
          {rdelim}
          {foreach from=$banner.content item=content}
            $('#slideshow_content{$content.id}{if $content.type eq 'I'}_I{/if}').removeAttr('style');
            {if $content.type eq 'I'}
              $('#slideshow_content{$content.id}_I img').width(Math.round({$content.image_x} * k)).height(Math.round({$content.image_y} * k)).css('margin', '0px ' + Math.round({math equation="floor(x/2)-floor(y/2)" x=$banner.width|default:$config.Banner_System.default_banner_width|default:1 y=$content.image_x} * k) +'px');
            {else}
              $('#slideshow_content{$content.id} .content').width(bannerSlideshow.width()).height(bannerSlideshow.height());
            {/if}
          {/foreach}
        {/if}
      {/foreach}

    {rdelim}

    enableBanners{$banner_location}();

  {rdelim}

  var bannerResizeTimer{$banner_location} = null;
  $(window).resize(function() {ldelim}
    if (bannerResizeTimer{$banner_location}) clearTimeout(bannerResizeTimer{$banner_location});
    bannerResizeTimer{$banner_location} = setTimeout(resizeBanners{$banner_location},100);
  {rdelim});

  $(document).ready(function() {ldelim}
    enableBanners{$banner_location}();
    resizeBanners{$banner_location}();
  {rdelim});
//]]>
</script>
