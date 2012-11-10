/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

$(document).ready(function()
{
	/**
	 * Tablica aktualnie wcisnietych znakow
	 */
	var keysDown = new Array();
	/**
	 * Tablica elementow strony wraz ze skrotami klawiaturowymi
	 */
	var objects = new Array();
	/**
	 * Zmienna oznacza, czy element typu <input>, <textarea> posiada focus
	 */
	var hasFocus = false;

	$('[data-shortcut]').each(function()
	{
		objects[$(this).attr('data-shortcut').toUpperCase()] = this;
	});

	$(document).keydown(function(e)
	{
		if (!hasFocus)
		{
			if (!e.ctrlKey)
			{
				keysDown.push(e.keyCode);
			}

			for (var item in objects)
			{
				var chars = item.split('+');
				var keys = 0;

				for (var char in chars)
				{
					for (var i in keysDown)
					{
						if (keysDown[i] == chars[char].charCodeAt(0))
						{
							++keys;
						}
					}
				}

				if (keys == chars.length && !e.ctrlKey)
				{
					$(objects[item]).click();

					if ($(objects[item]).attr('href'))
					{
						window.location = $(objects[item]).attr('href');
					}
				}
			}
		}
	});

	$(document).keyup(function(e)
	{
		if (!hasFocus)
		{
			for (var char in keysDown)
			{
				if (e.keyCode == keysDown[char])
				{
					keysDown.splice(char, 1);
				}
			}
		}
	});

	$('body').delegate('input, textarea', 'focus', function()
	{
		hasFocus = true;
	});

	$('body').delegate('input, textarea', 'blur', function()
	{
		hasFocus = false;
	});
});