/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

function toggleSide()
{
	if ($('#template-tree').css('margin-left') == '0px')
	{
		width = $('#template-tree').outerWidth();

		$('#template-tree').animate({
			marginLeft:		'-' + (width - 40) + 'px'
		});
	}
	else
	{
		$('#template-tree').animate({
			marginLeft:		'0px'
		});
	}
}

function toggleBranch()
{
	mode = $(this).attr('class');
	dir = $(this).attr('title');
	branch = this;

	if (mode == 'close')
	{
		if ($(this).parent().children('ul:first').length)
		{
			$(this).parent().children('ul:first').toggle();				
		}
		else
		{
			$.get(baseUrl + 'adm/Template/__template', {'dir': dir},
				function(data)
				{
					$(branch).parent().append(data);

					$('em.close, em.open').bind('click', toggleBranch);
				}
			);
		}

		$(this).removeClass('close');
		$(this).addClass('open');
	}
	else
	{
		$(this).parent().children('ul:first').toggle();	

		$(this).removeClass('open');
		$(this).addClass('close');
	}	
}

function getTemplates(dir)
{
	$.get(baseUrl + 'adm/Template/__template', {'dir': dir},
		function(data)
		{
			$('#template-tree').append(data);
			$('#template-tree ul:first').css('margin-left', '0');

			$('em.close, em.open').bind('click', toggleBranch);
		}
	);
}

$(document).ready(function()
{
	getTemplates('');
	$('#template-tree').css('height', $('#template').outerHeight() + 'px');

	$('#template-content fieldset:not(:eq(0))').hide();
	$('.page-menu li a:eq(0)').addClass('focus');

	$('.page-menu li a').each(function(index)
	{
		$(this).bind('click', function()
		{
			var element = $('#template-content fieldset');

			$('.page-menu li a').removeClass('focus');
			$(this).addClass('focus');

			element.hide();
			$(element[index]).show();
		}
		);
	}
	);

	$('#template-toggle').bind('click', function()
	{
		$('em.open').addClass('close').removeClass('open');
		$('#template-tree ul:not(:eq(0))').hide();
	}
	);
}
);