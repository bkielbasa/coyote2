$(function()
{
	var infoBox = $('#infobox-wrapper');

	infoBox.css({'top': 0, 'left': (($(window).width() / 2) - (infoBox.innerWidth() / 2)) + 'px'});

	$(':button', infoBox).click(function()
	{
		$.ajax({url: baseUrl + 'infobox/__close', dataType: 'jsonp', type: 'GET', data: {id: infoBox.attr('data-infobox-id')}});
		$('#infobox-wrapper, #infobox-layer').remove();

		$(window).off('keyup.infobox');
	});

	$('.infobox-close', infoBox).click(function()
	{
		$('#infobox-wrapper, #infobox-layer').remove();

		var cookies = document.cookie.split(';');
		var cookie = [];
		var lifeTime = infoBox.attr('data-infobox-lifetime');

		for (var item in cookies)
		{
			if (item == cookiePrefix + 'infobox')
			{
				cookie = cookies[item].split(',');
			}
		}

		cookie.push(infoBox.attr('data-infobox-id'));
		document.cookie = cookiePrefix + 'infobox=' + cookie.join(',') + ';expires=0;path=/;domain=' + cookieDomain;

		$(window).off('keyup.infobox');
	});

	$(window).on('keyup.infobox', function(e)
	{
		if (e.which == 27)
		{
			$('.infobox-close').click();
		}
	});

	$('#infobox-layer').one('click', function()
	{
		$('.infobox-close').click();
	});

});