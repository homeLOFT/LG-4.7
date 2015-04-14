/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Login with PayPal script
 */

var pplogin_object = {clicked: false, popupw: null};

function open_pplogin_popup(url) {

  if (!pplogin_object.clicked) {

    pplogin_object.clicked = true;

    var height = 550;
    var width = 400;

    var top = (screen.height/2)-(height/2);
    var left = (screen.width/2)-(width/2);

    pplogin_object.popupw = window.open (url, 'PPLOGIN_identity_window_', 'location=yes,status=no,scrollbars=no,menubar=no,toolbar=no,width='+width+',height='+height+',top='+top+',left='+left);
    
    var interval = window.setInterval(function() {
        try {
            if (pplogin_object.popupw == null || pplogin_object.popupw.closed) {
                window.clearInterval(interval);
                pplogin_object.clicked = false;
            }
        }
        catch (e) {
        }
    }, 1000);

  } else {
      
      if (pplogin_object.popupw) {
          pplogin_object.popupw.focus();
      }
  }

  return true;
}
