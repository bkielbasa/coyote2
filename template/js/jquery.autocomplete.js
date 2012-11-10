/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

(function($)
{
	$.fn.autocomplete = function(options)
	{
		var defaults =
		{
			className: 'auto-complete',
			autoSubmit: true,
			minLength: 2,
			url: '',
			delay: 200
		};

		var	setup = $.extend(defaults, options);
		var $this = $(this);
		var	$ul = $('<ul style="display: none;" class="' + setup.className + '"></ul>');
		var	timeId = 0;
		var index = -1;

		var onHide = function()
		{
			$ul.html('').hide();
		};

		var onClick = function(e)
		{
			if ($('li.hover', $ul).length)
			{
				$this.val($('li.hover', $ul).text());

				if (setup.autoSubmit)
				{
					$this.parents('form').submit();
				}

				e.preventDefault();
			}
			onHide();
		};

		var onSelect = function(position)
		{
			var length = $('li', $ul).length;

			if (length > 0)
			{
				if (position >= length)
				{
					position = 0;
				}
				else if (position < 0)
				{
					position = length -1;
				}
				index = position;

				$('li', $ul).removeClass('hover');
				$('li:eq(' + index + ')', $ul).addClass('hover');
			}
		};

		$this.bind(($.browser.opera ? "keypress" : "keydown"), function(e)
		{
			var keyCode = e.keyCode || window.event.keyCode;
			switch (keyCode)
			{
				// down
				case 40:

					onSelect(index + 1);
					break;

				case 38:

					onSelect(index - 1);
					break;

				case 13:

					onClick(e);
					break;

				case 27:

					onHide();
					e.preventDefault();
					break;

				default:

					clearTimeout(timeId);
					timeId = setTimeout(function()
					{
						if ($.trim($this.val()).length > setup.minLength)
						{
							$.get(setup.url, {q: $this.val()}, function(html)
							{
								$ul.html(html).hide();

								if ($('li', $ul).length > 0)
								{
									var p = $this.offset();

									$('li', $ul)
										.click(onClick)
										.hover(
										function()
										{
											$('li', $ul).removeClass('hover');
											$(this).addClass('hover');
										},
										function()
										{
											$(this).removeClass('hover');
										}
									);

									$ul.css(
										{
											'width': $this.innerWidth(),
											'top': p.top + $this.outerHeight(),
											'left': p.left

										}).show();

									index = -1;
								}
							});
						}
						else
						{
							onHide();
						}

					}, setup.delay);
			}
		});
		$ul.appendTo(document.body);

		$(document).bind('click', function(e)
		{
			var $target = $(e.target);

			if ($target.not($ul))
			{
				onHide();
			}
		});
	};
})(jQuery);
