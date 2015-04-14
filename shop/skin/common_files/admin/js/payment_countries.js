/* vim: set ts=2 sw=2 sts=2 et: */
function in_array(what, where) {
    for(var i = 0; i < where.length; i++) {
        if(what == where[i]) { 
            return true;
        }
    }

    return false;
}

function change_payment_country() {
  var el = document.getElementsByName('payment_country')[0];
  var selected_value = el.value;

  var payments_list = document.getElementsByName('processor')[0];
  payments_list.parentNode.replaceChild(stored_payment_gateways.cloneNode(true), payments_list);
  
  if (selected_value != 'ALL') {
    populate_select_box('processor', selected_value);
  }
}

function populate_select_box(name, country_code) {
  var node = document.getElementsByName(name)[0];

  if (!node)
    return;

  for (var i = 1; i < node.options.length; i++) {
    if (
        node.options[i].value != 'via_xp'
        && payment_countries[node.options[i].value] !== undefined
        && !in_array(country_code, payment_countries[node.options[i].value])
    ) {
      node.remove(i);
      i = i - 1;
    }
  }

  for (i = 0; i < node.children.length; i++) {
    var child = node.children[i];
    if (child.children.length == 0 && child.nodeName == "OPTGROUP") {
      child.parentNode.removeChild(child);
      i = i - 1;
    }
  }
}

function change_payment_banner() {
  var banner_id = 'payment-banner-iframe';
  var banner_selector = '#' + banner_id;
  var banner_node = document.getElementById(banner_id);
  var banner_inner_code = $(banner_selector).text();
  var selected_value = document.getElementsByName('payment_country')[0].value;
  var replacement_id = default_banner_id;

  var pattern = /(.*)(zoneid=)(\d+)(.*)/;
  
  if (payment_banners[selected_value] !== undefined) {
    replacement_id = payment_banners[selected_value];
  }

  banner_node.src = banner_node.src.replace(pattern, "$1$2" + replacement_id + "$4");
  $(banner_selector).html(banner_inner_code.replace(pattern, "$1$2" + replacement_id + "$4"));
  return;
}

$(document).ready(function() {
  change_payment_country();
  change_payment_banner();
});
