/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Plugin jest alternatywa dla funkcji JS - confirm() oraz alert()
 * Umozliwia wyswietlanie prostych okien dialogowych sluzacych do interakcji z uzytkownikiem
 */
(function($)
{
	var $window;
	var setup;
	
	/**
	 * Konfiguracja i tworzenie okna
	 * @param string messageType Klasa okna dialogowego
	 * @array buttonSet Zbior przyciskow wyswietlanych w oknie
	 */
	function setupWindow(messageType, buttonSet)
	{
		$('body').append('<div id="overlay"></div>');

		$window = $('<div class="' + messageType + '"></div>');
		$window.append('<h1>' + setup.windowTitle + ' <em id="close-message" title="Zamknij"></em></h1>');

		$window.append('<div><p>' + setup.windowMessage + '</p></div>');
		$window.append('<span></span>');

		$.each(buttonSet, function()
		{
			$('span', $window).append('<button type="button" id="' + this.id + '"><strong>' + this.caption + '</strong></button>');
		});

		$('body').append($window);		
		$window.css({'top': 0, 'left': (($(window).width() / 2) - ($window.innerWidth() / 2)) + 'px'});

		$('#close-message').click(function()
		{
			$.closeWindow();
		});
		
		$(window).keydown(function(e)
		{
			keyCode = e.keyCode || window.event.keyCode;

			if (keyCode == 27)
			{
				$.closeWindow();
				$(window).unbind('keydown');
			}
			if (keyCode == 32 && $('button:focus').length)
			{
				$('button:focus').trigger('click');
				return false;
			}
		});
	}

	/**
	 * Okno typu Tak/Nie
	 */
	$.windowConfirm = function(options)
	{		
		/**
		 * Opcje 
		 */
		var defaults = 
		{
			windowTitle: 'Title',
			windowMessage: 'Message',
			
			onYesClick: function()
			{
				
			},
			onNoClick: function()
			{
				$.closeWindow();				
			}			
		}; 

		setup = $.extend(defaults, options);
		setupWindow('confirm-message', [{id: 'button-yes', caption: 'Tak'}, {id: 'button-no', caption: 'Nie'}]);
		
		$('#button-yes').bind('click', setup.onYesClick).focus();
		$('#button-no').bind('click', setup.onNoClick);

		return $window;
	};

	/**
	 * Okienko informacyjne z przyciskiem "OK"
	 */
	$.windowAlert = function(options)
	{
		/**
		 * Opcje 
		 */
		var defaults = 
		{
			windowTitle: 'Title',
			windowMessage: 'Message',

			onOkClick: function()
			{
				$.closeWindow();				
			}			
		};

		setup = $.extend(defaults, options);
		setupWindow('alert-message', [{id: 'button-ok', caption: 'OK'}]);

		$('#button-ok').bind('click', setup.onOkClick).focus();

		return $window;
	};

	/**
	 * Okienko bledu z przyciskiem "OK"
	 */
	$.windowError = function(options)
	{
		/**
		 * Opcje 
		 */
		var defaults = 
		{
			windowTitle: 'Title',
			windowMessage: 'Message',

			onOkClick: function()
			{
				$.closeWindow();				
			}			
		};

		setup = $.extend(defaults, options);
		setupWindow('error-message', [{id: 'button-ok', caption: 'OK'}]);

		$('#button-ok').bind('click', setup.onOkClick).focus();

		return $window;
	};

	/**
	 * Zamykanie okna
	 */
	$.closeWindow = function()
	{
		$window.remove();
		$('#overlay').remove();
		
		$(window).unbind('keydown');
	};
}
)(jQuery);