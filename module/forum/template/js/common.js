/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

var config;
/**
 * Sciezka do katalogu ze skryptami JS
 */
var scriptPath;

function applyAjaxLoader(element)
{
	var p = element.offset();
	$('body').append('<div id="ajax-layer"></div>');

	$('#ajax-layer').css(
	{
		top: 	p.top,
		left: 	p.left,
		width: 	element.innerWidth(),
		height: element.height()
	});
	$('body').append('<div id="ajax-loader">Proszę czekać...</div>');

	$('#ajax-loader').css('top', p.top + 100);
}

function removeAjaxLoader()
{
	$('#ajax-loader').remove();
	$('#ajax-layer').remove();
}

(function($)
{
	jQuery.posting =
	{
		init: function(vars)
		{
			config = $.extend(config, vars);

			/**
			 * Dzieki odczytaniu sciezki do jQuery mozemy ustalic, gdzie
			 * polozone sa pozostale skrypty JS, ktore sa wymagane do prawidlowego
			 * dzialania.
			 *
			 * @todo Do zmiany! Sciezka do katalogu powinna byc ustawiana w szablonie
			 */
			$('script[src*="jquery"]').each(function(i, element)
			{
				var path = element.src.match(/^(.+)\/jquery-/);

				if (path)
				{
					scriptPath = path[1];
				}
			});
		}
	};

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

	$.ajaxSort = function(queryString)
	{
		$.ajax(
		{
			url: config.currentUrl + queryString,
			datatype: 'html',
			beforeSend : function()
			{
				applyAjaxLoader($('.topic tbody'));
			},
			success : function(data)
			{
				removeAjaxLoader();
				$('.topic').replaceWith(data);
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				alert('Błąd: ' + xhr.responseText);
			}
		});

		return false;
	};

	$.fn.ajaxFilter = function(filterUrl)
	{
		var element = $(this);

		$.ajax(
		{
			url: filterUrl,
			dataType: 'html',
			beforeSend : function()
			{
				applyAjaxLoader(element);
			},
			success : function(data)
			{
				removeAjaxLoader();
				element.replaceWith(data);
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				alert('Błąd: ' + xhr.responseText);
			}
		});
	};


	/**
	 * @todo refaktoryzacja!
	 */
	$.fn.userTags = function()
	{
		var element = this;

		var tagEditMode = false;
		var applyUserTags = function()
		{
			$('tr').removeClass('topic-tagged');

			$('input[type=hidden]', element).each(function()
			{
				value = 't-' + encodeURIComponent($.trim($(this).val())).replace('%', '');
				$('tr[class*=' + value + ']').addClass('topic-tagged');
			});
		};

		var saveUserTags = function(input)
		{
			$.ajax(
				{
					url: config.currentUrl,
					type: 'POST',
					dataType: 'json',
					data: {mode: 'saveUserTags', tags: $(input).val()},
					beforeSend: function()
					{
						$(input).attr('disabled', 'disabled');
					},
					success: function(data)
					{
						tagEditMode = false;

						$('div', element).html('');
						$('input[type=hidden]', element).remove();

						if (typeof(data) !== 'undefined')
						{
							for (tag in data)
							{
								if (tag != '')
								{
									$(element).append('<input type="hidden" name="userTags[]" value="' + tag + '" />');
									$('div', element).append('<a href="' + config.currentUrl + '?tag=' + encodeURIComponent(tag) + '">' + tag + '</a> × ' + data[tag] + ' ');
								}
							}
						}

						$('input[type=text]', element).remove();
						$('div', element).toggle();

						applyUserTags();
					}
				});
		};

		$('input[type=text]', element).live('keypress', function(e)
		{
			if (e.keyCode == '13')
			{
				saveUserTags(this);
			}
		});

		$('strong span, cite', element).click(function()
		{
			tagEditMode = !tagEditMode;

			if (tagEditMode)
			{
				userTags = new Array();

				$('input[type=hidden]', element).each(function(index)
				{
					userTags[index] = $(this).val();
				});
				$('<input type="text" value="' + userTags.join(', ') + '" />').insertAfter($('div', element));
			}
			else
			{
				$('input[type=text]', element).remove();
			}

			$('div', element).toggle();
		});

		if ($('input[type=hidden]', element).length > 0)
		{
			applyUserTags();
		}
	};

})(jQuery);

$(function()
{
	$('.br').live('click', function(e)
	{
		$.ajaxSort($(this).attr('href'));

		e.preventDefault();
		return false;
	});

	if (!preventAjax)
	{
		$('#thread-button').not('.prevent').click(function()
		{
			$('#log-button').removeClass('focus');
			$('#post').show();

			$(this).addClass('focus');

			/**
			 * Jezeli istieja dwie tabele o klasie .page, to znaczy, ze w danym
			 * temacie sa odpowiedzi. Ukrywamy wszystko, co znajduje sie PRZED
			 * odpowiedziami w danym temacie
			 */
			if ($('table.page').length > 1)
			{
				$('table.page:eq(1)').prevAll('div.page').hide();
			}
			else
			{
				$('.page').hide();
			}
			/**
			 * Pokazujemy formularz z trescia pytania
			 */
			$('table.page:eq(0)').show();
		});


		$('#log-button').click(function()
		{
			$('#thread-button').show();
			$('#post').show();
			$('.f-menu-top:not(.p-menu) li').removeClass('focus');

			$(this).addClass('focus');

			if ($('table.page').length > 1)
			{
				$('table.page:eq(1)').prevAll('.page').hide();
			}
			else
			{
				$('.page').hide();
			}

			$('#log-spot').show();

		});

		$('ul.f-menu-top li:not(#submit-top, #reply-top, #search-top)').live('click', function(e)
		{
			$(this).parent('ul').children('li').removeClass('focus');
			$(this).addClass('focus');

			e.preventDefault();
			return false;
		});

		$('ul.f-menu-top li').not('#log-button, #thread-button, #submit-top, #submit-bottom, #reply-top, #reply-bottom, #search-top').click(function(e)
		{
			$('.f-menu-bottom li').removeClass('focus');

			var anchor = $('a', this).attr('href');
			var filterHash = anchor.replace(/^.*#/, '');

			anchor = anchor.substr(0, anchor.indexOf('#'));

			if ($(this).parent().hasClass('p-menu'))
			{
				$('table.page:eq(1)').show().nextAll('.page').hide();
				$('table.page:eq(1) tbody').ajaxFilter(anchor);
			}
			else
			{
				$('.page').hide().eq(0).show();

				//if (filterHash != currMode)
				{
					$('#body table').ajaxFilter(anchor);
					currMode = filterHash;
				}
			}

			window.location.hash = filterHash;

		});
	}

	$('.unread').each(function()
	{
		$(this).attr('title', 'Kliknij, aby oznaczyć jako przeczytany');
	});

	$.fn.markRead = function()
	{
		var element = $(this);

		// oznaczenie watku jako przeczytanego
		if (element.hasClass('unread'))
		{
			var topicId = $(this).parent().parent().attr('id').substr(6);
			$(element).attr('class', $(element).attr('class').replace('-new unread', ''));
			$(element).attr('title', '');

			var icon = $(element).parent().next().children('a.topic-last');
			icon.addClass('topic-last-read').attr('href', icon.attr('href').replace('unread', 'last'));

			$.get(config.currentUrl, {mode: 'markRead', 'topicId': topicId});
		}
		else if (element.attr('id') == 'mark-read-button')
		{
			$('.forum-icon-new').attr({'class': 'forum-icon-normal', title: 'Na forum nie ma nowych postów'});
			$('.sub-unread').removeClass('sub-unread');

			$('span[class^=topic-icon]').each(function()
			{
				$(this).attr('class', $(this).attr('class').replace('-new unread', ''));
			});

			$('a.topic-last').remove();
			$(element).text('Wątki odznaczone');

			$.get(config.currentUrl, {mode: 'markRead', 'forumId': config.forumId});
		}
		else
		{
			var forumId = $(this).parent().parent().attr('id').replace('forum-', '');
			$(element).attr(
			{
				title:		'Na forum nie ma nowych postów',
				'class':	'forum-icon-normal'
			});

			$('.sub-unread', $(element).parent().parent()).removeClass('sub-unread');
			$.get(config.currentUrl, {mode: 'markRead', 'forumId': forumId});
		}
	};

	$('.unread').live('click', function()
	{
		$(this).markRead();
	});

	$('.forum-icon-new').live('click', function()
	{
		$(this).markRead();
	});

	$('#mark-read-button').live('click', function()
	{
		$(this).markRead();
	});

	/*
	 * Klikniecie na przycisk "Szukaj"
	 */
	$('#body a.search-submit-button').click(function()
	{
		if ($(this).prev(':input').val() == 'Szukaj na forum...')
		{
			$(this).prev(':input').val('');
		}

		$(this).parents('form').submit();
		return false;
	});

	/*
	 * Klikniecie ikonki powodujacej "zwiniecie" sie for w danej kategorii
	 */
	$('#forum a.section-toggle').click(function()
	{
        var section = $(this).parents('tr');

        $.post(config.currentUrl + '?mode=section', {id: section.next('tr').attr('id').substring(6)});

		$(this).toggleClass('toggle');
	    section.nextUntil('tr.section').toggle();
	});

	/*
	 * W przypadku gdy obrazy wstawione w tresci posta sa wieksze niz szerokosc strony, zmniejszamy ich (obrazow) szerokosc
	 */
	var documentWidth = $('#body').width();

	$('#body .post-content img').load(function()
	{
		if ($(this).width() > documentWidth * 0.7)
		{
			$(this).css('width', documentWidth * 0.7);
		}
	});

	$('#user-tags').userTags();
});