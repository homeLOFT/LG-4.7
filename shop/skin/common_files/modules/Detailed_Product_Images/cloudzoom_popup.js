function cloudzoom_change_image_size(image_width, image_height) {
  image = document.getElementById('product_thumbnail');
  if (!image) {
    return false;
  }

  image.width = image_width;
  image.height = image_height;

  return true;
}

function cloudzoom_change_popup_params(params) {
  cloudzoom_popup = document.getElementById('cloud_zoom_image');
  if (!cloudzoom_popup) {
    return false;
  }

  cloudzoom_popup.rel = params;

  return true;
}
