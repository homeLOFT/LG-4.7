{*
0d0465915bddae9202730b9dcd479c6724c60147, v3 (xcart_4_5_5), 2013-01-11 13:17:36, want_more.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{literal}
  <style type="text/css">
    #soc-want-more-box {
      position: relative;
    }
    #soc-want-more-box .top-message-info {
      position: static;
      background: transparent none;
      width: 100%;
      left: 0;
    }
    #soc-want-more-box .window-link {
      position: absolute;
      top: 10px;
      right: 10px;
      background: transparent no-repeat right center;
      padding-right: 21px;
      cursor: pointer;
      line-height: 18px;
      overflow: visible;
    }
    #soc-want-more-box a.close {
      background-image: url({/literal}{$ImagesDir}{literal}/popup_header_cross.gif);
    }
    #soc-want-more-box a.open {
      background-image: url({/literal}{$SkinDir}{literal}/modules/Socialize/images/question.gif);
    }
  </style>
  <script type="text/javascript">
    $(function(){
      $('#soc-want-more-box a.window-link').click(function(){
        
        var aj_mode = ($(this).hasClass('close')) ? 'close' : 'open';

        $.ajax({
          url: 'configuration.php',
          type: 'GET',
          data: {option: 'Socialize',
                 ajax_mode: aj_mode
                }
        });
        
        $('#soc-want-more-box .dialog-tools-box, #soc-want-more-box a.open, #soc-want-more-box a.close').toggle();
          
      });
    });
  </script>
{/literal}
<div id="soc-want-more-box">
  <a class="window-link close" href="javascript: void(0);" style="display: {$want_more_box_mode};">{$lng.lbl_close}</a>
  <div class="dialog-tools-box ui-corner-all" style="display: {$want_more_box_mode};">
    <table cellspacing="0" class="soc-want-more-message">
      <tr>
        <td>
          <a href="configuration.php?option=fCommerce_Go"><img src="http://www.x-cart.com/images/socialize.png" alt="" style="vertical-align: middle; margin-right: 8px;" /></a>
        </td>
        <td>
          Want more? <br /> Make your store <a href="configuration.php?option=fCommerce_Go">socialized</a>!
        </td>
      </tr>
    </table>
  </div>
  <a class="window-link open" href="javascript: void(0);" style="display: {if $want_more_box_mode eq 'none'}block{else}none{/if}">{$lng.lbl_want_more}</a>
</div>

