{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, home_page_banner.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Banner_System and $top_banners ne ''}
  {include file="modules/Banner_System/banner_rotator.tpl" banners=$top_banners banner_location='T'}
{elseif $active_modules.Demo_Mode and $active_modules.Banner_System}
  {include file="modules/Demo_Mode/banners.tpl"}
{else}
  <div class="catpromo">{$lng.lbl_catpromo}</div>
  <div class="welcome-img">
    <a href="Braxton-Leather-Furniture-Collection">
    <img src="/common/images/welcome/Braxton-Leather-Furniture-Collection.jpg" alt="The Braxton Leather Furniture Collection" title="The Braxton Leather Furniture Collection" />      <h1>The Braxton Leather Collection</h1>
        <p>Made in America.  Select from a variety of pieces and sizes. 2 Depths, Premium Leathers and Down Seating</p>
    </a>
  </div>
  <div class="welcome-img">
    <a href="Langston-Leather-Furniture-Collection">
    <img src="/common/images/welcome/Langston-Leather-Furniture-Collection.jpg" alt="The Langston Leather Furniture Collection" title="The Langston Leather Furniture Collection" />      <h1>The Langston Leather Collection</h1>
        <p>A traditional classic, available in 43" &amp; 48" Depths. Roll Arms, wrapped in Premium Leathers. Made in America</p>
    </a>
  </div>
  <div class="welcome-img">
    <a href="Arizona-Leather-Sectional-Sofa-with-Chaise-Top-Grain-Aniline-Leather">
    <img src="/common/images/welcome/Arizona-Leather-Sofa-Chaise-Sectional.jpg" alt="The Arizona Leather Sofa Chaise Sectional" title="The Arizona Leather Sofa Chaise Sectional" />      <h1>Arizona Leather Sofa Chaise Sectional</h1>
        <p>A Customer favorite available in rich premium leathers with clean lines and tufted seat cushions.</p>
    </a>
  </div>
  <div class="welcome-img">
    <a href="Luke-Leather-Furniture.html">
    <img src="/common/images/welcome/Luke-Leather-Furniture.jpg" alt="The Mark Leather Sofa by Luke Leather" title="The Mark Leather Sofa by Luke Leather" />
    <h1>Luke Leather Furniture</h1>
        <p>Quick Ship Italian Leather Furniture by Luke Leather.  Great quality, real leather furniture at great prices, designed in the US, built in Italy</p>
    </a>
  </div>
  <div class="welcome-img">
    <a href="Lane-Leather-Furniture.html">
    <img src="/common/images/welcome/Lane-Leather-Furniture.jpg" alt="The Emerson Leather Sofa by Lane Furniture" alt="The Emerson Leather Sofa by Lane Furniture" title="The Emerson Leather Sofa by Lane Furniture" />      
    <h1>Lane Leather Furniture</h1>
        <p>American made leather furniture, stocked in some styles and colors and at guaranteed lowest pricing. Lane's specialty is reclining leather furniture!</p>
    </a>
  </div>
  <div class="welcome-img">
    <a href="https://www.sofagroups.com" target="blank">
    <img src="/common/images/welcome/Fabric-Upholstered-Furniture-at-SofaGroups.jpg" alt="Fabric Upholstered Sofa Collections" title="Fabric Upholstered Sofa Collections" />        
    <h1>Fabric Upholstered Sofa Collections</h1>
        <p>If you like our styles, but would love some pieces in fabric, or a fabric piece to compliment some leather furniture, we've got you covered!</p>
    </a>
  </div>
{/if}
