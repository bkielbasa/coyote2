/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

$(document).ready(function()
{
	$('label[title]').hintBox();
}
);

(function($)
{
	$.fn.hintBox = function()
	{
		return this.each(function()
		{
			$(this).hover(hoverIn, hoverOut).addClass('hint-label');
		}
		);
	};

	function hoverIn(e)
	{
		var title = $(this).attr('title');
		$(this).attr('title', '');

		$('#content').append('<div class="box-hint">' + title + '</div>');

		$('.box-hint').css('top', e.pageY + 15);
		$('.box-hint').css('left', e.pageX);
	}

	function hoverOut()
	{
		$(this).attr('title', $('.box-hint').html());
		$('.box-hint').remove();
	}

}
)(jQuery);