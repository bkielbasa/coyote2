/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

$(document).ready(function()
{
	$('#news-form :text').focus(function()
	{
		if ($(this).val() == 'Dodaj link do katalogu nowości...')
		{
			$(this).val('');
			$('#news-form :submit').show();
		}
		
	}).blur(function()
	{
		if ($(this).val() == '')
		{
			$(this).val('Dodaj link do katalogu nowości...');
			$('#news-form :submit').hide();
		}
	});

	$('#news .top, #news .bottom').click(function()
	{
		news = $(this).parents('.news').attr('id');			
		newsId = news.substr(5);

		value = $(this).attr('class') == 'top' ? 1 : -1;			

		$.ajax(
		{
			url: pageLocation,
			data: {'id': newsId, 'value': value},
			type: 'POST',
			dataType: 'plain',
			beforeSend: function()
			{
			},
			success: function(data)
			{
				$('div div strong', $('#' + news)).text(data);										
			}			
		});
	});
});