ajax.widgets.minicart.obj.prototype._constructMinicartButton = function() {
  if (this.minicartButton)
    return false;

//  this.minicartButton = $('.ajax-minicart-icon', this.elm);
  this.minicartButton = $('.minicart-block');

  if (this.minicartButton.length == 0)
    return false;

  this.elm.addClass('ajax-minicart');

  this.minicartButton
    .addClass('minicart-button')
    .click(this._callbackMB);

  return true;  
}
