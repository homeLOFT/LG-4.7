/*e1db5cf03524aef7b3d94390d4b4baa6311fd42b, v2 (xcart_4_5_5), 2013-02-07 17:35:38, pinterest.js, aim*/

function pin_it () {
  var o = document,
  c = pinterest_options;

  var r = function (h) {
    var e = c.endpoint,
    m = "?",
    a, i, f, b;
    f = [];
    b = [];
    var j = {},
    g = o.createElement("IFRAME"),
    q = h.getAttribute(c.att.count) || false,
    n = h.getAttribute(c.att.layout) || "horizontal";
    f = h.href.split("?")[1].split("#")[0].split("&");
    a = 0;
    for (i = f.length; a < i; a += 1) {
      b = f[a].split("=");
      j[b[0]] = b[1]
    }
    a = f = 0;
    for (i = c.vars.req.length; a < i; a += 1) {
      b = c.vars.req[a];
      if (j[b]) {
        e = e + m + b + "=" + j[b];
        m = "&"
      }
      f += 1
    }
    if (j.media && j.media.indexOf("http") !== 0) f = 0;
    if (f === i) {
      a = 0;
      for (i = c.vars.opt.length; a < i; a += 1) {
        b = c.vars.opt[a];
        if (j[b]) e = e + m + b + "=" + j[b]
      }
      e = e + "&layout=" + n;
      if (q !== false) e += "&count=1";
      g.setAttribute("src", e);
      g.setAttribute("scrolling", "no");
      g.allowTransparency = true;
      g.frameBorder = 0;
      g.style.border = "none";
      g.style.width = c.layout[n].width + "px";
      g.style.height = c.layout[n].height + "px";
      h.parentNode.replaceChild(g, h)
    } else h.parentNode.removeChild(h)
  };
        
  $('a.pin-it-button').each(function(){
    r(this);
  });
}
