/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * X-Cart context help widget controller
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    f2c96ddb72c96605401a2025154fc219a84e9e75, v3 (xcart_4_6_1), 2013-08-19 12:16:49, context_help.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

;(function () {

	var pluginName = 'xcartHelpWidget';

	(function ($, window, document, undefined) {
		var Widget = function (root, opts) {
			var w = this;

			w.results = $('.context-help-results', root);
			w.form    = $('.context-help-form', root),
			w.input   = $('.context-help-input', root);

			w.cache		= {};

			opts = $.extend({}, {
				searchAsYouTypeDelay: 300,
				requestTimeout: 			3000
			}, opts);

			Handlebars.registerHelper('seeMoreResults', function(results) {
				if (results && results.length > opts.numExpandedResults) {
					var text = opts.seeMoreCaption.replace(/\{\{n\}\}/gi, results.length - opts.numExpandedResults);

					return new Handlebars.SafeString('<li class="text see-more-results"><a href="#">' + text + '</a></li>');
				}

				return '';
			});

			Handlebars.registerHelper('resultVisibility', function(index) {
			  var style = index < opts.numExpandedResults ? '' : 'style="display: none;"';

			  return new Handlebars.SafeString(style);
			});

			var resultsSource = 
				  '{{#if autocorrection}}'
			  + '  <li class="autocorrection text">' + opts.autocorrectionTpl + '</li>'
			  + '{{/if}}'
			  + '{{#each results}}'
			  + '  <li class="page" {{resultVisibility @index}}>'
			  + '    <div class="title">'
			  + '      <a href="{{this.url}}" target="_blank" title="{{{this.title}}}">{{{this.titleHi}}}</a>'
			  + '    </div>'
			  + '    <div class="content">'
			  + '      {{{this.content}}}'
			  + '    </div>'
			  + '  </li>'
			  + '{{/each}}'
			  + '{{seeMoreResults results}}'
			  + '{{#unless results}}'
			  + '  <li class="no-results text">' + opts.noResultsText + '</li>'
			  + '{{/unless}}';

			var errorsSource = 
				  '{{#if connError}}'
			  + '  <li class="connection-error text">' + opts.connErrorText + '</li>'
			  + '{{/if}}';

			var resultsTemplate = Handlebars.compile(resultsSource),
				errorsTemplate = Handlebars.compile(errorsSource);

			function renderSuccess(data) {
				w.results.html(resultsTemplate(data));

				w.results.find('.see-more-results a').click(function () {
					w.results.find('.page').show();

					$(this).hide();

					return false;
				});
			}

			function renderError(data) {
				w.results.html(errorsTemplate(data));
			}

			function search() {
				var q = w.input.val();

				if (w.cache[q]) {
					renderSuccess(w.cache[q]);

				} else {
					w.results.addClass('searching').block();

					$.ajax(opts.searchApiUrl, {
						type: 'GET',
						dataType: 'jsonp',
						q: q,
						data: {q: q},
						timeout: opts.requestTimeout,
						success: function (data) {
							if (this.q == q) {
								renderSuccess(data);

								w.results.removeClass('searching');

								w.cache[q] = data;
							}
						},
						error: function () {
							if (this.q == q) {
								renderError({connError: true});

								w.results.removeClass('searching');
							}
						}
					});
				}

				return false;
			}

			$('.context-help-form').submit(search);

      if (opts.searchAsYouType) {
        w.input.keydown(function () {
        	if (w.timeoutId)
        		clearTimeout(w.timeoutId);

          w.timeoutId = setTimeout(function () { w.form.submit(); }, opts.searchAsYouTypeDelay);
        });
      }

			w.autoSearchPerformed = false;
		};

		Widget.prototype = {
			submit: function () {
			},

			autoSearch: function () {
				if (!this.autoSearchPerformed) {
          if (this.input.val())
            this.form.submit();

					this.autoSearchPerformed = true;
				}
			}
		};

		$.fn[pluginName] = function (data) {
			var args = arguments;

			return this.each(function () {
				var plugin = $.data(this, 'plugin_' + pluginName);

				if (!plugin) {
					$.data(this, 'plugin_' + pluginName, new Widget(this, data));
				} else {
					if (typeof plugin[data] == 'function') {
						plugin[data].apply(plugin, Array.prototype.slice.call(args, 1));
					}
				}
			});
		};

	})(jQuery, window, document);


	$(function () {

		var dtools = $('.dialog-tools'),
			opts = contextHelpSettings;

		if (dtools.length) {
			var helpHeader = $('<li>', {
				'class': 'dialog-header-help dialog-tools-nonactive',
				'text' : opts.widgetTitle,
				'click': function () {
					dialog_tools_activate('help');

					helpWidget.find('.context-help-root')[pluginName]('autoSearch');
				}
			});

			var helpWidget = $('<div>', {
				'class': 'dialog-tools-content dialog-tools-help hidden',
				'text' : 'Help widget root'
			});

			var q = $('h1').text() || $('#location span:last').text();

			helpWidget.html(
					'<div class="context-help-root">'
			  + '  <form method="get" action="#" class="context-help-form">'
			  + '    <input type="text" class="context-help-input" value="' + q + '" placeholder="' + opts.inputPlaceholder + '" />'
			  + '    <input type="submit" class="context-help-submit" value="' + opts.buttonTitle + '" />'
			  + '  </form>'

			  + '  <ul class="context-help-results">'
			  + '  </ul>'

			  + '  <div class="context-help-hide">'
			  + '    <a href="#">' + opts.hideTabCaption + '</a>'
			  + '  </div>'
			  + '  <div class="clearing"></div>'
			  + '</div>'
			);
			
			dtools.find('.dialog-tools-header').append(helpHeader);

			dtools.find('.dialog-tools-box').append(helpWidget);

			dtools.find('.context-help-submit').button();

			helpWidget.find('.context-help-root')[pluginName](contextHelpSettings);

			if ($('.dialog-tools-header li').length == 1) {
				helpHeader.click();
			}

			$('.context-help-hide a').click(function () {
				xAlert(opts.hideHelpTabText, opts.hideTabCaption, 'I');

				return false;
			});
		}

	});

})();
