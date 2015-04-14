/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Category Selector functions
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    ca665af77f64f59a6fceb508a286bfed4e55f844, v1 (xcart_4_5_3), 2012-09-03 08:27:13, category_selector.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function init() {
  if ( isNN )
    document.captureEvents(Event.MOUSEMOVE)
    document.onmousemove = handleMouseMove;
}

function handleMouseMove(evt) {
  mouseX = !isIE ? evt.pageX : window.event.clientX;
  mouseY = !isIE ? evt.pageY : window.event.clientY;

  return true;
}

function hideTitle(id) {
  var layer = document.getElementById(id);
  layer.style.display = "none"; 
}

function showTitle(value, position) {
  if (value.length < 40) {
    return;
  }
  if (!isIE) {
    var layer = document.getElementById('layer');
    setTimeout("hideTitle('layer');", 3000);
    layer.innerHTML = value;
  } else {
    var layer = document.getElementById('iframe');
    setTimeout("hideTitle('iframe');", 3000);
    layer.style.width = value.length * 6;
    layer.contentWindow.document.body.innerHTML = value;
    layer.contentWindow.document.body.style.fontSize = "12px";
    layer.contentWindow.document.body.style.marginLeft = "0px";
    layer.contentWindow.document.body.style.marginTop = "0px";
    layer.contentWindow.document.body.style.background = "#FFFBD3";
  }
    layer.style.display = "";
    if (position == 'left') {
      var length = layer.style.width.substr(0, layer.style.width.length - 2);
      layer.style.left = (mouseX - length) + "px";
    } else if (position == 'right') {
      layer.style.left = mouseX+"px";
    }
    layer.style.top = mouseY+"px";
}

var isNN = document.layers ? true : false;
var isIE = document.all ? true : false;
var mouseX;
var mouseY;

init();
