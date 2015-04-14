{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, event_modify_menu.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="giftreg-menu">

  <p>{$lng.lbl_giftreg_tools}:</p>
  <hr />

  <ul>

    <li{if $smarty.get.mode eq ""} class="giftreg-menu-hl-item"{/if}>
      <a href="giftreg_manage.php?eventid={$eventid}">{$lng.lbl_giftreg_edit_event_info}</a>
    </li>

    <li{if $mode eq "products"} class="giftreg-menu-hl-item"{/if}>
      <a href="giftreg_manage.php?eventid={$eventid}&amp;mode=products">{$lng.lbl_giftreg_view_wishlist}</a>
    </li>

    <li{if $mode eq "maillist"} class="giftreg-menu-hl-item"{/if}>
      <a href="giftreg_manage.php?eventid={$eventid}&amp;mode=maillist">{$lng.lbl_giftreg_edit_recipients_list}</a>
    </li>

    <li{if $mode eq "send"} class="giftreg-menu-hl-item"{/if}>
      <a href="giftreg_manage.php?eventid={$eventid}&amp;mode=send">{$lng.lbl_giftreg_send_notification}</a>
    </li>

    <li{if $mode eq "gb"} class="giftreg-menu-hl-item"{/if}>
      <a href="giftreg_manage.php?eventid={$eventid}&amp;mode=gb">{$lng.lbl_giftreg_edit_guestbook}</a>
    </li>

  </ul>
  <div class="clearing"></div>

</div>
