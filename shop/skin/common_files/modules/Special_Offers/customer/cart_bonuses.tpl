{*
686a4c944b1bab093a0d60e12aff76ce8db05664, v3 (xcart_4_7_0), 2015-02-18 13:16:20, cart_bonuses.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $cart.bonuses ne ""}
  <div class="offers-cart">
    <strong>{$lng.lbl_sp_cart_bonuses_title}</strong>

    <ul>
      {if $cart.bonuses.points ne 0}
        <li>{$lng.lbl_sp_cart_bonuses_bp|substitute:"num":$cart.bonuses.points}</li>
      {/if}
      {if $cart.bonuses.memberships ne ""}
        <li>{$lng.lbl_sp_cart_bonuses_memberships}<br />
          {foreach name=memberships from=$cart.bonuses.memberships item=membership}
            {$membership}
            {if not $smarty.foreach.memberships.last}
              {$lng.lbl_or}
            {/if}
          {/foreach}
        </li>
      {/if}
    </ul>

  </div>

  <hr />

{/if}
