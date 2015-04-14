{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, banners.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Banner_System}

{include file="modules/Demo_Mode/banner_images.tpl"}

<script type="text/javascript">
//<![CDATA[

  function enableBannersdemo() {ldelim}
        $('#slideshow-demo').cycle({ldelim}
          fx: 'fade'
          {if $config.Banner_System.bs_rotation_time_delay ne ''}
            ,timeout: {assign var='time_delay' value=$config.Banner_System.bs_rotation_time_delay*1000}{$time_delay}
          {/if}
        {rdelim});
  {rdelim}

  var lastBannerContainerWidthdemo = 0;

  resizeBannersdemo = function() {ldelim}
  
    var bannerContainerdemo = $('#banner-system-demo');

    $('#slideshow-demo').cycle('stop');

    if (bannerContainerdemo.width() != lastBannerContainerWidthdemo) {ldelim}
      lastBannerContainerWidthdemo = bannerContainerdemo.width();

          var bannerSlideshow = $('#slideshow-demo');
          var origSlideshowWidth = 855;
          var origSlideshowHeight = 261;

          var newWidth = bannerContainerdemo.width();
          if (newWidth < origSlideshowWidth) {ldelim}
            var k = newWidth / origSlideshowWidth;
            bannerSlideshow.width(newWidth).height(Math.round(origSlideshowHeight * k));
          {rdelim} else {ldelim}
            var k = 1;
            bannerSlideshow.width(origSlideshowWidth).height(origSlideshowHeight);
          {rdelim}
            $('#slideshow-demo div').removeAttr('style');
            $('#slideshow-demo div img').width(Math.round(855 * k)).height(Math.round(261 * k));

    {rdelim}

    enableBannersdemo();

  {rdelim}

  var bannerResizeTimerdemo = null;
  $(window).resize(function() {ldelim}
    if (bannerResizeTimerdemo) clearTimeout(bannerResizeTimerdemo);
    bannerResizeTimerdemo = setTimeout(resizeBannersdemo,100);
  {rdelim});

  $(document).ready(function() {ldelim}
    enableBannersdemo();
    resizeBannersdemo();
  {rdelim});
//]]>
</script>

{/if}
