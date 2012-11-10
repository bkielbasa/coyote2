/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

$(document).ready(function()
{
	Date.prototype.asInteger = function()
	{
		return String(this.getFullYear()) + String(this.getMonth() + 1) + String((this.getDate() < 10 ? '0' : '') + this.getDate());
	};

	Date.prototype.getHour = function()
	{
		return (this.getHours() < 10 ? '0' : '') + this.getHours();
	}

	Date.prototype.getMinute = function()
	{
		return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes();
	}

	function declination(value, declinationSet)
	{
		if (value == 1)
		{
			return declinationSet[0];
		}
		else
		{
			unit = value % 10;
			decimal = Math.round((value % 100) / 10);

			if ((unit == 2 || unit == 2 || unit == 3 || unit == 4) && (decimal != 1))
			{
				return declinationSet[1];
			}
			else
			{
				return declinationSet[2];
			}
		}
	}

	function getTimeSpan(remote)
	{
		return Math.abs(Math.round(new Date().getTime() / 1000) - remote);
	}

	function getDiffMinute(remote)
	{
		return Math.round(getTimeSpan(remote) / 60);
	}

	function getDiffSecond(remote)
	{
		return getTimeSpan(remote);
	}

	var countTime = function()
	{
		$('.timestamp[data-timestamp]').each(function()
		{
			timestamp = $(this).attr('data-timestamp');

			if (getDiffMinute(timestamp) <= 60)
			{
				if (getDiffSecond(timestamp) >= 60)
				{
					$(this).text(getDiffMinute(timestamp) + ' ' + declination(getDiffMinute(timestamp), ['minuta', 'minuty', 'minut']) + ' temu');
				}
				else
				{
					$(this).text(getDiffSecond(timestamp) + ' ' + declination(getDiffSecond(timestamp), ['sekunda', 'sekundy', 'sekund']) + ' temu');
				}
			}
			else if (getDiffMinute(timestamp) < 100)
			{
				$(this).text('1 godz. temu');
			}
			else
			{
				currDate = new Date((new Date().getTime()));
				currValue = currDate.asInteger();

				spanDate = new Date(timestamp * 1000);
				spanValue = spanDate.asInteger();

				if (spanValue == currValue)
				{
					$(this).text('dzisiaj, ' + spanDate.getHour() + ':' + spanDate.getMinute());
				}
				else if (spanValue == currValue -1)
				{
					$(this).text('wczoraj, ' + spanDate.getHour() + ':' + spanDate.getMinute());
				}
				else
				{
					$(this).text($(this).attr('title'));
				}
			}
		});
	};
	setInterval(countTime, 30000); // 30 sek

	/**
	 * Ilosc NIEprzeczytanych powiadomien
	 */
	var notifyUnread = 0;
	var pmUnread = 0;
	var pageTitle = $('head title').text();

	$(document).click(function(e)
	{
		var $target = $(e.target);

		/*
		 * Chodzi o to, aby po kliknieciu w tlo strony (lub inny element),
		 * poza przyciskami do powiadomien i wiadomosci prywatnych -- menu
		 * bylo zamykane.
		 *
		 * Czy mozna to zrobic inaczej niz przy pomocy tego warunku z is() ?
		 */
		if (!$target.is('#notify-icon, #notify-icon a, #notify-icon span, #pm-icon, #pm-icon a, #pm-icon span'))
		{
			$('#box-notify-menu, #box-pm-menu').hide();
			$('#pm-icon, #notify-icon').removeClass('focus');
		}
	});

	$('#notify-icon a').each(function(i, element)
	{
		var anchor = $(element).attr('href');

		var amount = anchor.substr(anchor.indexOf('#') + 8);
		if (amount > 0)
		{
			$(element).parent().append('<span class="amount">' + amount + '</span>');
		}
		notifyUnread = amount;

		$(element).removeAttr('href');
	});

	$('#pm-icon a').each(function(i, element)
	{
		var anchor = $(element).attr('href');
		var amount = anchor.substr(anchor.indexOf('#') + 4);

		if (amount > 0)
		{
			$(element).parent().append('<span class="amount">' + amount + '</span>');
		}

		pmUnread = amount;
		$(element).removeAttr('href');
	});

	var prevTitle;
	var isAnimation = false;

	if (typeof userId != 'undefined')
	{
		setInterval(function()
		{
			$.get('/User/ajax/session', function(data)
			{
				data.notify = parseInt(data.notify);
				if (data.notify != notifyUnread)
				{
					if (!data.notify)
					{
						$('#notify-icon .amount').remove();
						$('head title').text(pageTitle);
						$('head link[rel=icon]').attr('href', baseUrl + 'template/img/favicon.png');
					}
					else
					{
						$('head title').text('(' + (data.notify) + ') ' + pageTitle);

						if (notifyUnread > 0)
						{
							$('#notify-icon .amount').text(data.notify);
						}
						else
						{
							$('#notify-icon').append('<span class="amount">' + data.notify + '</span>');
						}

						$('head link[rel=icon]').attr('href', baseUrl + 'template/img/xicon/favicon' + Math.min(data.notify, 6) + '.png');
					}

					$('#notify-icon ul').remove();
					notifyUnread = data.notify;
				}

				data.pm = parseInt(data.pm);
				if (data.pm > pmUnread)
				{
					if (pmUnread > 0)
					{
						$('#pm-icon .amount').text(data.pm);
					}
					else
					{
						$('#pm-icon').append('<span class="amount">' + data.pm + '</span>');
					}

					$('#pm-icon ul').remove();
					pmUnread = data.pm;

					prevTitle = $('head title').text();
					isAnimation = true;

					var clip1 = function()
					{
						setTimeout(function()
						{
							if (isAnimation)
							{
								$('head title').text('Masz nową wiadomość');
								clip2();
							}

						}, 1000);
					};

					var clip2 = function()
					{
						setTimeout(function()
						{
							{
								$('head title').text(prevTitle);
								clip1();
							}

						}, 1000);
					};

					clip1();

					$('#container').one('mouseover', function()
					{
						isAnimation = false;
					});
				}

			}, 'json');

		}, 120000); // 2 min
	}

	$('#notify-icon, #pm-icon').click(function()
	{
		$(this).toggleClass('focus');
	});

	$('#bar-menu-ul .user-icon').hover(function()
	{
		$('#pm-icon, #notify-icon').removeClass('focus');
		$('#box-notify-menu, #box-pm-menu').hide();
	});

	$('#notify-icon').click(function()
	{
		var element = $(this);

		if ($(this).hasClass('focus'))
		{
			$('#pm-icon').removeClass('focus');

			if (!$('ul', element).length)
			{
				$.ajax(
				{
					dataType: 'json',
					type: 'GET',
					url: '/User/ajax/notify',
					beforeSend: function()
					{
						element.append('<ul id="box-notify-menu"><li><h5>Powiadomienia</h5></li></ul>');
						$('#box-notify-menu').append('<li class="ajax-loader"><img width="32" height="32" src="' + baseUrl + 'template/img/ajax-loader.gif" /></li>');
					},
					success: function(data)
					{
						$('.ajax-loader').remove();

						if (data.header.length > 0)
						{
							headers = data.header;

							for (var item in headers)
							{
								if (!headers[item].url)
								{
									headers[item].url = baseUrl + 'User/Notify';
								}
								li = '<li' + ((headers[item].read > 0 && headers[item].read < data.sessionStart) ? '' : ' class="unread"') + '>' +
										'<a title="Od ' + headers[item].userName + '. ' + headers[item].plain + '" href="' + headers[item].url + '">' +
										'<img alt="Zdjęcie" width="35" height="30" src="' + baseUrl + (headers[item].photo.length > 0 ? ('store/_a/' + headers[item].photo) : 'template/img/avatar.jpg') + '" />' +
										'<span>' + headers[item].message + '</span>' +
										'<small>' + headers[item].time + '</small></a>' +
									 '</li>';

								$('ul', element).append(li);

								if (headers[item].read == 0)
								{
									--notifyUnread;
								}
							}

							$('ul', element).append('<li><h6><a href="' + baseUrl + 'User/Notify">Zobacz wszystkie powiadomienia</a></h6></li>');
							if ($('.amount', element).length)
							{
								if (notifyUnread > 0)
								{
									$('.amount', element).text(notifyUnread);
									$('head link[rel=icon]').attr('href', baseUrl + 'template/img/xicon/favicon' + Math.min(notifyUnread, 6) + '.png');
								}
								else
								{
									$('.amount', element).remove();
									$('head title').text(pageTitle);

									$('head link[rel=icon]').attr('href', baseUrl + 'template/img/favicon.png');
								}
							}
						}
					}
				});
			}

			$('#box-notify-menu, #box-pm-menu').hide();
			$('ul', element).show();
		}
		else
		{
			$('#box-notify-menu').hide();
		}
	});

	$('#pm-icon').click(function()
	{
		var element = $(this);

		if ($(this).hasClass('focus'))
		{
			$('#notify-icon').removeClass('focus');

			if (!$('ul', element).length)
			{
				$.ajax(
				{
					dataType: 'json',
					type: 'GET',
					url: '/User/ajax/pm',
					beforeSend: function()
					{
						element.append('<ul id="box-pm-menu"><li><h5>Wiadomości <a id="submit-message" href="' + baseUrl + 'User/Pm/Submit">Napisz wiadomość</a></h5></li></ul>');
						$('#box-pm-menu').append('<li class="ajax-loader"><img width="32" height="32" src="' + baseUrl + 'template/img/ajax-loader.gif" /></li>');
					},
					success: function(data)
					{
						$('.ajax-loader').remove();

						if (data.pm.length > 0)
						{
							for (var item in data.pm)
							{
								li = '<li ' + (data.pm[item].unread ? 'class="unread"' : '') + ' title="' + data.pm[item].message + '">' +
										'<a href="' + data.pm[item].url + '">' +
										'<img alt="" width="35" height="30" src="' + baseUrl + (data.pm[item].photo.length > 0 ? ('store/_a/' + data.pm[item].photo) : 'template/img/avatar.jpg') + '" />' +
										'<span>' + data.pm[item].header + '</span>' +
										'<small>' + data.pm[item].recipient + '; ' + data.pm[item].time + '</small></a>' +
									  '</li>';

								$('ul', element).append(li);
							}

							$('ul', element).append('<li><h6><a href="' + baseUrl + 'User/Pm">Zobacz wszystkie wiadomości</a></h6></li>');
						}
					}
				});
			}

			$('#box-notify-menu, #box-pm-menu').hide();
			$('ul', element).show();
		}
		else
		{
			$('#box-pm-menu').hide();
		}
	});
});