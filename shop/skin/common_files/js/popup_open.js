/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Popup modal dialog widget
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    df07adee291d462827fbe587b7684ab509c64a27, v34 (xcart_4_6_5), 2014-07-17 21:01:28, popup_open.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * Extend UI dialog widget
 */

$.extend($.ui.dialog.prototype, {

  // Load content from script
  load: function(src, title, postData) {

    var o = this;

    $(ajax.messages).unbind('popupDialogCall');

    $(ajax.messages).bind(
      'popupDialogCall',
      function(e, data) {

        switch (data.action) {

          case 'message':
            o.showMessage(data.message);
            o.element.unblock();
            break;

          case 'load':
            o.loadContent(data.src);
            break;
          
          case 'close':
            o.close();
            break;
          
          case 'jsCall':
            if (data.toEval) {
              eval(data.toEval);
            }
            break;
 
        }
      }
    );

    return this.loadContent(src, postData);
  },

  callback: function(state, a, b, c, d) {

    if (!state) {
      xAlert(txt_ajax_error_note, lbl_error, 'E');
      return false;
    }

    return this.onload(a, 'success');
  },

  // Get remove content
  loadContent: function (src, postData) {

    src += (src.search(/\?/) === -1 ? '?' : '&') + 'open_in_layer=Y&is_ajax_request=Y&keep_https=Y';
    var o = this;

    if (postData === undefined) {
      var _type = 'GET';
      var _data = {};
    } else {
      var _type = 'POST';
      var _data = postData;
    }

    return ajax.query.add(
      {
        type: _type,
        url: src,
        data: _data,
        success: function(a, b, c, d) {
          return o.callback(true, a, b, c, d);
        },
        error: function(a, b, c, d) {
          this.close();
          return o.callback(false, a, b, c, d);
        }
      }
    ) !== false;

  },
 
  // Onsubmit handler
  submitHandler: function(f) {

    if (undefined !== f.onsubmit && f.onsubmit && f.onsubmit.constructor != String && !f.onsubmit()) {
        return true;
    }

    if (!checkFormFields(f)) {
      return true;
    }

    var elm = $(f).parents('.popup-dialog').get(0);
   
    $(elm).block();

    var url = f.action;
    url += (url.search(/\?/) === -1 ? '?' : '&') + 'open_in_layer=Y&is_ajax_request=Y&keep_https=Y';

    var o = this;

    return ajax.query.add(
      {
        type: f.method ? f.method.toUpperCase() : 'GET',
        url: url,
        data: $(f).serialize(),
        success: function(a, b, c, d) {
          return o.callback(true, a, b, c, d);
        },
        error: function(a, b, c, d) {
          return o.callback(false, a, b, c, d);
        }
      }
    ) !== false;    

  },

  // Link onclick handler
  clickHandler: function(l) {

    var url = l.href;
    url += (url.search(/\?/) === -1 ? '?' : '&') + 'open_in_layer=Y&is_ajax_request=Y&keep_https=Y';

    var o = this;

    return ajax.query.add(
      {
        type: 'GET',
        url: url,
        data: {},
        success: function(a, b, c, d) {
          return o.callback(true, a, b, c, d);
        },
        error: function(a, b, c, d) {
          return o.callback(false, a, b, c, d);
        }
      }
    ) !== false;    
  },

  // Process onload method
  onload: function(data, s) {

    if (s != 'success') {
      return false;
    }
    
    if (!this.processResponse(data)) {

      if (this.insertData(data)) {
        this.processInsertData();
        this.activate();
        // WA for Firefox.For some reason autofocus html5 attr does not work for Firefox
        $('form', this.element).find("input[autofocus]").eq(0).focus();
      }

    }

    return true;
  },

  // Activate window
  activate: function() {

    if (!this.isOpen()) {
      this.open();
    }

    this.element.unblock();

    if (this.option('height') > this.option('maxHeight')) {
      this.option('height', this.option('maxHeight'));
    }
    
    if (this.option('width') > this.option('maxWidth')) {
      this.option('width', this.option('maxWidth'));
    }
    
    this._position('center');
   
  },

  // Show message
  showMessage: function(data) {
    
    if (data === undefined || !data.type || !data.content) {
      return false;
    }

    var msgbox = $('.ajax-popup-error-message', this.element).get(0);

    if (msgbox !== undefined) {
      var icon = $(document.createElement('span')).attr('class', 'ui-icon ' + (data.type == 'I' ? 'ui-icon-info' : 'ui-icon-alert'));
      var text = $(msgbox).children('p').get(0);

      if (data.type == 'I') {
        $(msgbox).removeClass('ui-state-error').addClass('ui-state-highlight');
      } else {
        $(msgbox).removeClass('ui-state-highlight').addClass('ui-state-error');
      }

      $(msgbox).width(this.element.width());

      $(text).html(data.content).prepend(icon);
      if ($(msgbox).not(':visible')) {
        $(msgbox).show();
      }
    }

  },

  // Process service signatures from AJAX response
  processResponse: function(data) {

    if (!data)
      return false;

    var m, l;

    if (data.search(/\/\* CMD: opener_reload \*\//) != -1) {
      // Opener window reload
      this.close();
      window.location.reload();
      return true;
    }
    
    if (data.search(/\/\* CMD: opener_relocate \*\//) != -1) {
      // Opener window redirect
      this.close();
      if (m = data.match(/window.parent.location = '([^']+)'/)) { 
        window.location = m[1];
      }
      return true;
    }

    if (data.search(/\/\* CMD: window_close \*\//) != -1) {
      // Close current window
      this.close();
      return true;
    }

    try {
      if ((m = data.match(/<meta http-equiv="Refresh" content="[0-9]+;URL=([^"]+)" \/>/)) || (this._ajax && (l = this._ajax.getResponseHeader('Location')))) {

        // Redirect
        if (m)
          l = m[1];

        this.load(l);
        return true;
      }
    } catch (e) { }

    return false;
  },

  // Parse page html and insert content
  insertData: function(data) {

    var m;
    data = data.replace(/\r/g, '');
    m = data.match(new RegExp("<!-- MAIN -->\n*((?:.*\n)+.*)<!-- \/MAIN -->"));
    if (!m)
      m = data.match(new RegExp("<body[^>]*>\n*((?:.*\n)+.*)<\/body>", 'i'));

    if (!m)
      return false;

    this.element.html(m[1]);

    if (m = data.match(new RegExp("<title>(.+)<\/title>"))) {
      this.setTitle(m[1]);
      this.element.find('h1:first').hide();
      this.element.prepend('<div class="ajax-popup-error-message ui-state-highlight ui-corner-all"><p><span class="ui-icon ui-icon-info"></span></p></div>');
    }

    return true;
  },

  // Set dialog title
  setTitle: function(title) {
    $(".ui-dialog-title", this.uiDialogTitlebar).html(title ? title : '');
  },

  // Bind handlers on some events
  processInsertData: function() {

    $(this).trigger('onload');

    var o = this; 

    $('form', this.element).bind('submit', function() {
      return !o.submitHandler(this);
    });

    $('a:not([href^="javascript:"])', this.element).not(".external_link").bind('click', function() {
      return !o.clickHandler(this);
    });

    return true;
  }

});


/**
 * Popup dialog class (jQuery UI dialog) 
 */

function popupOpen(src, title, params, postData) {

  // Close existing dialog
  $('.popup-dialog').dialog('destroy').remove();

  var popup = $(document.createElement('div'))
    .attr('class', 'popup-dialog')
    .css('display', 'none')
    .appendTo('body');

  var dialogOpts = {
    modal:     true, 
    autoOpen:  true,
    draggable: false,
    resizable: false,
    width:     'auto',
    height:    'auto',
    position:  {my : 'center center', at : 'center center'},
    maxHeight: 600,
    maxWidth:  800,
    closeOnEscape: true
  };

  if (undefined !== params) {
    for (var i in params) {
      dialogOpts[i] = params[i];
    }
  }

  try {
    $(popup)
      .dialog(dialogOpts)
      .block()
      .dialog('load', src, title, postData);

  } catch(e) {
    return false;
  }

  // Small hack to close a dialog on window.close() call
  window.close = function() {
    $(popup).dialog('close');
  }

  return true;
}
