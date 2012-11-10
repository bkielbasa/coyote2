/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

$(document).ready(function()
{
	$('.whois-ip').hover(function(e)
	{
		$.ajax(
		{
			url: baseUrl + 'adm/Whois',
			data: {ip: $(this).text()},
			type: 'GET',
			beforeSend: function()
			{
				$('.box-hint').remove();
				$('body').append('<div class="box-hint"><img src="' + baseUrl + 'template/adm/img/ajax-whois-loader.gif" /></div>');

				$('.box-hint').css('top', e.pageY + 15);
				$('.box-hint').css('left', e.pageX);
			},
			success: function(data)
			{
				$('.box-hint').html(data);
			}
		});
	},
	function()
	{
		$('.box-hint').remove();
	});
});