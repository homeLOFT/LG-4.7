/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Browser identificator script, sends statistics
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    7b79378dac39e23466f777ebd246768ed6458635, v3 (xcart_4_7_2), 2015-04-16 16:16:33, browser_identificator.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

var scriptNode = document.createElement("script");
scriptNode.type = "text/javascript";
setTimeout(
  function() {
    if (!scriptNode)
      return;

    scriptNode.src = xcart_web_dir + "/adaptive.php?send_browser=" +
      (localIsDOM ? "Y" : "N") + (localIsStrict ? "Y" : "N") + (localIsJava ? "Y" : "N") + "|" + 
      localBrowser + "|" + 
      localVersion + "|" + 
      localPlatform + "|" + 
      (localIsCookie ? "Y" : "N") + "|" + 
      screen.width + "|" + 
      screen.height + "|" + 
      (window.XMLHttpRequest ? "Y" : "N") + "|" +
      current_area;
    document.getElementsByTagName('head')[0].appendChild(scriptNode);
  },
  3000
);
