/* 0d0465915bddae9202730b9dcd479c6724c60147, v1 (xcart_4_5_5), 2013-01-11 13:17:36, func.js, random */
/*{literal}*/
$(function(){

  $('ul.fb-shop-categories-list li.fb-shop-category-item span.expand-collapse').click(function(){

    var parent_li = $(this).parent('li');

    $(this).toggleClass('closed').parent('li').toggleClass('opened').children('input.expanded-category').toggle(
      function(){
        var ex_val = (($(this).val() == 'Y') ? 'N' : 'Y');
        $(this).val(ex_val);
      }
      );

  });

  $('div.fb-shop-check-line a').click(function(){

    if ($(this).hasClass('expand-all')) {
      $('ul.fb-shop-categories-list li.fb-shop-category-item span.expand-collapse').removeClass('closed')
      .parent('li').addClass('opened').children('input.expanded-category').val('Y');
    }

    if ($(this).hasClass('collapse-all')) {
      $('ul.fb-shop-categories-list li.fb-shop-category-item span.expand-collapse').addClass('closed')
      .parent('li').removeClass('opened').children('input.expanded-category').val('N');
    }

  });

  $('ul.fb-shop-categories-list input:checkbox').click(function(){

    if ($(this).is(':checked')) {
      $(this).parents('li').children('label').children('input:checkbox').attr('checked', true);
      $(this).parent('label').parent('li').find('li').children('label').children('input:checkbox').attr('checked', true);
    } else {
      $(this).parent('label').parent('li').find('li').children('label').children('input:checkbox').attr('checked', false);
    }

  });

});
/*{/literal}*/
