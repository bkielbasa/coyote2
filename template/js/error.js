/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Plugin jQuery wyswietlajacy dymki z bledami przy danym komponencie formularza
 */
(function($)
{
	$.fn.setError = function(errorMessage, options)
	{
		/**
		 * Opcje 
		 */
		var defaults = 
		{	
			/*
			 * Szerokosc dymku
			 */
			width: 		'auto',
			/**
			 * Odstep od prawej krawedzi komponentu (w poziomie)
			 */
			offsetX:	0,
			/**
			 * Odstep od gornej krawedzi komponentu
			 * Domyslna wartosc jest ujemna poniewaz wysokosc komponentow jest mniejsza 
			 * niz wysokosc dymkow przez co same dymki wygladaja jak "nierowne"
			 */
			offsetY:	-2,
			/**
			 * Niestandardowe polozenie dymku w poziomie (w px)
			 */
			positionX:	0,
			/**
			 * Niestandardowe polozenie dymku w pionie (w px)
			 */
			positionY:	0
		}; 

		var options = $.extend(defaults, options);  		
		var object = $(this);

		var errorBox = $('<div id="boxBase" class="box-error" style="display: none; position: absolute;"><div class="box-error-top"><div></div></div><div class="box-error-content"></div><div class="box-error-bottom"><div></div></div></div>');
		$('.box-error-content', errorBox).html(errorMessage);
		errorBox.attr('id', 'box-' + object.attr('name'));
					
		var p = object.offset();
		var positionX = (!options.positionX ? p.left + object.outerWidth() : options.positionX) + options.offsetX;
		var positionY = (!options.positionY ? p.top : options.positionY) + options.offsetY;

		errorBox.css(
			{
				'top':		positionY,
				'left':		positionX,
				'width':	options.width				
			}
		);

		errorBox.show();
		$('body').append(errorBox);		
		
	};

	$.fn.hideError = function()
	{
		var object = $(this);

		$('#box-' + object.attr('name')).remove();
	};
}
)(jQuery);