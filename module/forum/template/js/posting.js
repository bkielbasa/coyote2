/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Obsluga Ctrl+Enter umozliwiajaca szybkie wyslanie formularza
 */
var onSubmitShortcut = function(e)
{
	if (e.ctrlKey && e.keyCode == 13)
	{
		var form = $(this).closest('form');

		// nieco slabe rozwiazane: jezeli element z formularza jest nieaktywny - nie submitujemy formularza
		if ($(':input::visible:first', form).is(':enabled'))
		{
			form.submit();
		}
	}
};

/**
 * Kolor podswietlenia komentarza czy postu
 */
var HIGHLIGHT_COLOR = '#FFF4B8';
/**
 * Czas podswietlenia komentarza lub postu
 */
var HIGHLIGHT_DELAY = 1500;

(function($)
{
	$.fn.autogrow = function()
	{
		/**
		 * @see http://code.google.com/p/gaequery/source/browse/trunk/src/static/scripts/jquery.autogrow-textarea.js?r=2
		 */
		return this.each(function()
		{
	        var $this       = $(this),
	            minHeight   = $this.height(),
	            maxHeight	= 300,
	            lineHeight  = $this.css('lineHeight');

	        var shadow = $('<div></div>').css(
	        {
	            position:   'absolute',
	            top:        -10000,
	            left:       -10000,
	            width:      $(this).width() - parseInt($this.css('paddingLeft')) - parseInt($this.css('paddingRight')),
	            fontSize:   $this.css('fontSize'),
	            fontFamily: $this.css('fontFamily'),
	            lineHeight: $this.css('lineHeight'),
	            resize:     'none'
	        }).appendTo(document.body);

	        var update = function()
	        {
	            var times = function(string, number)
	            {
	                for (var i = 0, r = ''; i < number; i ++) r += string;
	                return r;
	            };

	            var val = this.value.replace(/</g, '&lt;')
	                                .replace(/>/g, '&gt;')
	                                .replace(/&/g, '&amp;')
	                                .replace(/\n$/, '<br/>&nbsp;')
	                                .replace(/\n/g, '<br/>')
	                                .replace(/ {2,}/g, function(space) { return times('&nbsp;', space.length -1) + ' ' });

	            shadow.html(val);

	            $(this).css('height', Math.max(Math.min(shadow.height() + 18, maxHeight), minHeight));
	        }

	        $(this).change(update).keyup(update).keydown(update);
	        update.apply(this);

	    });
	};

	$.fn.localStorage = function()
	{
		if (typeof localStorage != 'undefined')
		{
			var $textarea	= $('textarea:not(.poll-items)', this);
			var $this 		= $(this);

			if (typeof config.topicId != 'undefined')
			{
				var dataIndex = 'post-' + config.topicId;

				if (localStorage.getItem(dataIndex) && (!$textarea.val().length || $textarea.val() == 'Kliknij, aby napisać szybką odpowiedź...'))
				{
					$textarea.val($.parseJSON(localStorage.getItem(dataIndex)).content);
				}

				try
				{
					for (var item in localStorage)
					{
						if (item.substr(0, 4) == 'post')
						{
							var time = new Date().getTime() / 1000;

							if ($.parseJSON(localStorage.getItem(item)).timestamp < time - 3600)
							{
								localStorage.removeItem(item);
							}
						}
					}
				}
				catch (e)
				{
				}

				$this.submit(function()
				{
					localStorage.removeItem(dataIndex);
					$textarea.unbind('keyup');
				});

				$textarea.keyup(function()
				{
					try
					{
						localStorage.setItem(dataIndex, JSON.stringify({'content': $textarea.val(), 'timestamp': new Date().getTime() / 1000}));
					}
					catch (e)
					{
						localStorage.clear();
					}
				});
			}
		}
	};

	$.fn.findUsers = function()
	{
		return this.each(function()
		{
			var $textarea = $(this);
			var cursorPosition = -1;
			var index = -1;
			var timeId = 0;
			var	$ul = $('<ul style="display: none;" class="auto-complete"></ul>');

			var getCursorPosition = function()
			{
				if ($textarea[0].selectionStart || $textarea[0].selectionStart == 0)
				{
					return $textarea[0].selectionStart;
				}
				else if (document.selection)
				{
					$textarea.focus();
					var sel = document.selection.createRange();

					sel.moveStart('character', -$textarea.value.length);
					return (sel.text.length);
				}
			};

			var getUserNamePosition = function(caretPosition)
			{
				var i = caretPosition;
				var result = -1;

				while (i > caretPosition - 50 && i >= 0)
				{
					var $val = $textarea.val()[i];

					if ($val == ' ')
					{
						break;
					}
					else if ($val == '@')
					{
						if (i == 0 || $textarea.val()[i - 1] == ' ' || $textarea.val()[i - 1] == "\n")
						{
							result = i + 1;
							break;
						}
					}
					i--;
				}

				return result;
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

			$textarea.bind('keyup click', function(e)
			{
				var userName = '';
				var keyCode = e.keyCode/* || window.event.keyCode*/;
				var caretPosition = getCursorPosition();

				var startIndex = getUserNamePosition(caretPosition);

				if (startIndex > -1)
				{
					userName = $textarea.val().substr(startIndex, caretPosition - startIndex);
				}

				var onClick = function()
				{
					var $text = $('li.hover', $ul).text();

					if ($text.length)
					{
						if ($text.indexOf(' ') > -1 || $text.indexOf('.') > -1)
						{
							$text = '{' + $text + '}';
						}
						$textarea.val($textarea.val().substr(0, startIndex) + $text + $textarea.val().substring(caretPosition)).trigger('change').focus();
					}
					$ul.html('').hide();
				};

				switch (keyCode)
				{
					// esc
					case 27:

						$ul.html('').hide();
						break;

					// down
					case 40:

						onSelect(index + 1);
						break;

					case 38:

						onSelect(index - 1);
						break;

					case 13:

						onClick();

						break;

					default:

						if (userName.length >= 2)
						{
							clearTimeout(timeId);

							timeId = setTimeout(function()
							{
								$.get(config.currentUrl + '?mode=finduser', {q: userName}, function(html)
								{
									$ul.html(html).hide();
									var length = $('li', $ul).length;

									if (length > 0)
									{
										var p = $textarea.offset();

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
											'width': $textarea.outerWidth(),
											'top': $textarea.outerHeight() + p.top + 1,
											'left': p.left

										}).show();

										index = -1;
									}
								});

							}, 200);
						}
						else
						{
							$ul.html('').hide();
						}

					break;
				}
			}).keydown(function(e)
			{
				var keyCode = e.keyCode;

				if ((keyCode == 40 || keyCode == 38 || keyCode == 13 || keyCode == 27) && $ul.is(':visible'))
				{
					e.preventDefault();
					return false;
				}
			});

			$ul.appendTo(document.body);

			$(document).bind('click', function(e)
			{
				var $target = $(e.target);

				if ($target.not($ul))
				{
					$ul.html('').hide();
				}
			});
		});
	};

	/*
	 * Zaladowanie edytora Wiki
	 */
	$.fn.loadWikiEditor = function()
	{
		$forumHighlightBox = new ComboBox({
			name: 'highlight',
			title: 'Wstaw znacznik kolorowania składni',
			text: ' ',
			items: [

			        { id: 0, value: 'Kolorowanie składni' },
			        { id: 'asm', value: 'Assembler' },
			        { id: 'bash', value: 'Bash' },
			        { id: 'c', value: 'C' },
			        { id: 'cpp', value: 'C++' },
			        { id: 'csharp', value: 'C#' },
			        { id: 'css', value: 'CSS' },
			        { id: 'delphi', value: 'Delphi' },
			        { id: 'diff', value: 'Diff' },
			        { id: 'fortan', value: 'Fortran' },
			        { id: 'html', value: 'HTML' },
			        { id: 'ini', value: 'INI' },
			        { id: 'java', value: 'Java' },
			        { id: 'javascript', value: 'JavaScript' },
			        { id: 'jquery', value: 'jQuery' },
			        { id: 'pascal', value: 'Pascal' },
			        { id: 'perl', value: 'Perl' },
			        { id: 'php', value: 'PHP' },
			        { id: 'plsql', value: 'PL/SQL' },
			        { id: 'python', value: 'Python' },
			        { id: 'ruby', value: 'Ruby' },
			        { id: 'sql', value: 'SQL' },
			        { id: 'vbnet', value: 'Visual Basic.NET' },
			        { id: 'xml', value: 'XML' }
			]
		});

		$('textarea[name=content]', this).wikiEditor(

			[
				new Button({
					name: 'Bold',
					openWith: "**",
					closeWith: "**",
					className: 'wiki-bold',
					title: 'Pogrubienie',
				}),
				new Button({
					name: 'Italic',
					openWith: "//",
					closeWith: "//",
					className: 'wiki-italic',
					title: 'Kursywa'
				}),
				new Button({
					name: 'Underline',
					openWith: '__',
					closeWith: '__',
					className: 'wiki-underline',
					title: 'Podkreślenie'
				}),
				new Button({
					name: 'Strike',
					openWith: '<del>',
					closeWith: '</del>',
					className: 'wiki-strike',
					title: 'Przekreślenie'
				}),
				new Button({
					name: 'Teletype',
					openWith: "''",
					closeWith: "''",
					className: 'wiki-teletype',
					title: 'Tekst o stałych odstępach'
				}),
				new Separator(),
				new Button({
					name: 'List numbers',
					className: 'wiki-ul',
					openWith: "\n# ",
					closeWith: '',
					title: 'Lista numerowana',
					text: ' '
				}),
				new Button({
					name: 'List bullets',
					className: 'wiki-ol',
					openWith: "\n* ",
					closeWith: '',
					title: 'Lista wypunktowana',
					text: ' '
				}),
				new Separator(),
				new Button({
					name: 'Sub',
					className: 'wiki-sub',
					openWith: ',,',
					closeWith: ',,',
					title: 'Indeks dolny',
					text: ' '
				}),
				new Button({
					name: 'Sup',
					className: 'wiki-sup',
					openWith: '^',
					closeWith: '^',
					title: 'Indeks górny',
					text: ' '
				}),
				new Separator(),
				new Button({
					name: 'Table',
					className: 'wiki-table',
					openWith: "\n||=Nagłówek 1||Nagłówek 2\n||Kolumna 1||Kolumna 2",
					closeWith: '',
					title: 'Tabela',
					text: ' '
				}),
				new Button({
					name: 'Quote',
					className: 'wiki-quote',
					openWith: '<quote>',
					closeWith: '</quote>',
					title: 'Cytat',
					text: ' '
				}),
				new Button({
					name: 'Image',
					className: 'wiki-image',
					openWith: '<image>',
					closeWith: '</image>',
					title: 'Wstaw obraz',
					text: ''
				}),
				new Button({
					name: 'Ort',
					className: 'wiki-ort',
					openWith: '<ort>',
					closeWith: '</ort>',
					title: 'Oznacz błąd ortograficzny',
					text: ' '
				}),
				new Separator(),
				new Button({
					name: 'Code',
					className: 'wiki-code',
					openWith: '<code>',
					closeWith: '</code>',
					title: 'Kod źródłowy',
					text: ' '
				}),
				new Button({
					name: 'Delphi',
					className: 'wiki-delphi',
					openWith: '<code=delphi>',
					closeWith: '</code>',
					title: 'Kod źródłowy Delphi',
					text: ' '
				}),
				new Button({
					name: 'PHP',
					className: 'wiki-php',
					openWith: '<code=php>',
					closeWith: '</code>',
					title: 'Kod źródłowy PHP',
					text: ' '
				}),
				new Button({
					name: 'C',
					className: 'wiki-c',
					openWith: '<code=c>',
					closeWith: '</code>',
					title: 'Kod źródłowy C',
					text: ' '
				}),
				new Button({
					name: 'Cplusplus',
					className: 'wiki-cpp',
					openWith: '<code=cpp>',
					closeWith: '</code>',
					title: 'Kod źródłowy C++',
					text: ' '
				}),
				new Button({
					name: 'Csharp',
					className: 'wiki-csharp',
					openWith: '<code=csharp>',
					closeWith: '</code>',
					title: 'Kod źródłowy C#',
					text: ' '
				}),
				new Button({
					name: 'SQL',
					className: 'wiki-sql',
					openWith: '<code=sql>',
					closeWith: '</code>',
					title: 'Kod źródłowy SQL',
					text: ' '
				}),
				$forumHighlightBox,
				new Separator(),
				new Button({
					name: 'plain',
					className: 'wiki-plain',
					openWith: '<plain>',
					closeWith: '</plain>',
					title: 'Brak formatowania',
					text: ' '
				})
			]
		);

		var submitForm = $('form', this);

		$('textarea[name=content]', this).keydown(onSubmitShortcut).autogrow();
		$(':input[tabindex=1]').focus();

		$('.wiki-container', this).addClass('editor-tab').attr('title', 'Treść').before('<ol class="attachment-menu"></ol>');
		$('.editor-tab[title]').each(function()
		{
			$('.attachment-menu', submitForm).append('<li>' + $(this).attr('title') + '</li>');
		});

		$('.editor-tab', this).removeAttr('title');

		$('.attachment-menu li:first', this).addClass('active');
		$('.attachment-menu li', this).click(function()
		{
			// dlaczego to nie dziala przy wielu formularzach? :/
			//$('.attachment-menu li', submitForm).removeClass('active');
			$(this).parent().children('li').removeClass('active');
			$(this).addClass('active');

			var current = $('.attachment-menu li', submitForm).index(this);

			$('.editor-tab', submitForm).hide();
			$('.editor-tab:eq(' + current + ')', submitForm).show();
			$('#box-content').remove();
		});

		submitForm.localStorage();
		$('textarea', submitForm).findUsers();

		$(submitForm).submit(function()
		{
			var valueList = {};
			$(':input', this).each(function()
			{
				if (this.name == 'attachment')
				{
					// continue
				}
				else if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio')
				{
					if ($(this).is(':checked'))
					{
						valueList[this.name] = $(this).val();
					}
				}
				else
				{
					valueList[this.name] = $(this).val();
				}
			});

			$.ajax(
			{
				type: 'POST',
				url: this.action,
				data: valueList,
				timeout: 10000,
				beforeSend : function()
				{
					$('ol.attachment-menu li:first').trigger('click'); // przejscie na pierwsza zakladke, aby wyswietlic ewentualne bledy

					$('input[type=submit]', submitForm).val('Proszę czekać...');
					$(':input', submitForm).attr('disabled', 'disabled');
				},
				success : function(data, textStatus, xhr)
				{
					$('div[id|=box]').remove();

					if (data.errors)
					{
						for (field in data.errors)
						{
							$(':input[name=' + field + ']', submitForm).setError(data.errors[field]);
						}
					}
					else if (xhr.getResponseHeader('Content-type') == 'text/plain')
					{
						window.location.href = data;
					}
					else
					{
						alert('Wystąpił błąd podczas dodawania postu, lecz prawdopodobnie Twój post został zapisany');
					}
				},
				complete: function(xhr, textStatus)
				{
					$('input[type=submit]', submitForm).val('Wyślij');
					$(':input', submitForm).removeAttr('disabled');
				},
				error: function(xhr, textStatus, thrownError)
				{
					if (textStatus == 'timeout')
					{
						alert('Czas odpowiedzi serwera został przekroczony. Być może połączenie z internetem zostało utracone.');
					}
					else
					{
						if (xhr.getResponseHeader('Content-type') == 'text/plain')
						{
							alert('Błąd: ' + xhr.responseText);
						}
						else
						{
							alert('Wystąpił błąd. Twój post prawdopodobnie nie został dodany. Jeżeli problem będzie się powtarzał, skontaktuj się z administratorem witryny');
						}
					}
				}
			});

			return false;
		});

		$('input[name=subject]', submitForm).blur(function()
		{
			var li = $(this).parent();

			if ($(this).val().split(' ').length < 2
					|| $(this).val().length < 10)
			{
				$('small', li).text('Temat wiadomości wydaje się zbyt krótki. Prosimy dodać więcej szczegółów.').css('color', 'red');
			}
			else
			{
				$('small', li).text('');

				$.get(config.currentUrl, {mode: 'find', like: $(this).val()},
					function(data)
					{
						$('li#related').remove();
						$(data).insertAfter(li);

						$('.box-error').remove();
					}
				);
			}
		});

		$('.attachment-menu li:last', this).click(function()
		{
			var postText = $('textarea[name=content]', submitForm).val();
			$('.editor-tab:last', submitForm).addClass('preview-tab');

			var valueList = {};
			$(':input', submitForm).each(function()
			{
				if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio')
				{
					if ($(this).is(':checked'))
					{
						valueList[this.name] = $(this).val();
					}
				}
				else
				{
					valueList[this.name] = $(this).val();
				}
			});

			$.ajax(
			{
				type: 'POST',
				url: (config.currentUrl.indexOf('?') == -1 ? (config.currentUrl + '?mode=preview') : (config.currentUrl + '&mode=preview')),
				dataType: 'html',
				data: valueList,
				beforeSend : function()
				{
					$('.editor-tab:last', submitForm).html('<div class="page-loader"></div>');
				},
				success : function(data)
				{
					$('.editor-tab:last', submitForm).html(data);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					alert('Błąd: ' + xhr.responseText);
				}
			});

		});

		$('.attachments').delegate('.delete-attachment-button', 'click', function()
		{
			var parent = $(this).parents('tr');
			var uniqid = parent.attr('id').substr(3);

			$('.attachment-' + uniqid).remove();
			$(submitForm).append('<input type="hidden" name="attachment[' + uniqid + ']" value="delete" />');

			parent.remove();
			$('.upload-attachment-button', submitForm).val('Dodaj załącznik (plików: ' + (15 - $('.attachment-row').length) + ')');
			$('.attachments table tbody tr:odd').addClass('even');
		});

		$('.attachments').delegate('.append-attachment-button', 'click', function()
		{
			var suffix = $(this).text().split('.').pop().toLowerCase();
			var prefix = (suffix == 'png' || suffix == 'jpg' || suffix == 'jpeg' || suffix == 'gif') ? 'Image' : 'File';

			$('textarea', submitForm).insertAtCaret('{{' + prefix + ':', '}}', $(this).text());
			$('ol.attachment-menu li:first').trigger('click');
		});

		function appendAttachment(data)
		{
			$('.attachments tr td[colspan]').remove();

			$(submitForm).append('<input type="hidden" name="attachment[' + data.uniqid + ']" value="' + data.name + '" class="attachment-' + data.uniqid + '" />');
			$('<tr id="id-' + data.uniqid + '" class="attachment-row"><td><a class="append-attachment-button" title="Wstaw załącznik do tekstu">' + data.name + '</a></td><td style="text-align: center;">' + data.mime + '</td><td style="text-align: center;">' + data.time + ' </td><td>' + Math.ceil(data.size) + ' kB</td><td><a title="Usuń załącznik" class="delete-attachment-button"></a></td></tr>').appendTo($('.attachments table tbody', submitForm));

			$('.attachments table tbody tr:odd').addClass('even');
		}

		$.getScript(scriptPath + '/ajaxupload.js', function()
		{
			new AjaxUpload($('.upload-attachment-button', submitForm),
			{
				action: (config.currentUrl.indexOf('?') == -1 ? (config.currentUrl + '?mode=attachment') : (config.currentUrl + '&mode=attachment')),
				name: 'attachment',
				responseType: 'json',
				onSubmit : function()
				{
					if ($('.attachment-row').length >= 15)
					{
						$.windowError({windowTitle: 'Błąd', windowMessage: 'Przekroczono maksymalną ilość (15) dozwolonych załączników w tekście'});
						return false;
					}

					this.disable();
					$('.upload-attachment-button', submitForm).val('Proszę czekać...');
				},
				onChange: function(file, extension)
				{
				},
				onComplete : function(file, data)
				{
					if (data.error)
					{
						$.windowError({windowTitle: 'Błąd', windowMessage: data.error});
					}
					else
					{
						appendAttachment(data);
					}

					this.enable();
					$('.upload-attachment-button', submitForm).val('Dodaj załącznik (plików: ' + (15 - $('.attachment-row').length) + ')');
				}
			});
		});

		$(':input[name=content]', submitForm)[0].onpaste = function(e)
		{
			if ($.browser.webkit)
			{
				var items = event.clipboardData.items;

				if (items.length)
				{
					var blob = items[0].getAsFile();
					var fr = new FileReader();

					fr.onloadstart = function(e)
					{
						applyAjaxLoader($(':input[name=content]', submitForm));
					};

					fr.onload = function(e)
					{
						if ($('.attachment-row').length >= 15)
						{
							$.windowError({windowTitle: 'Błąd', windowMessage: 'Przekroczono maksymalną ilość (15) dozwolonych załączników w tekście'});
							return false;
						}

						if (event.total > 2097152)
						{
							$.windowError({windowTitle: 'Błąd', windowMessage: 'Rozmiar pliku jest zbyt duży'});
							return false;
						}

						var mime = /^data:image/g;

						if (!mime.test(e.target.result))
						{
							return false;
						}

						$.post((config.currentUrl.indexOf('?') == -1 ? (config.currentUrl + '?mode=paste') : (config.currentUrl + '&mode=paste')), {'data': e.target.result}, function(result)
						{
							appendAttachment(result);
							$('textarea', submitForm).insertAtCaret('{{Image:', '}}', result.name);

							removeAjaxLoader();

						}, 'json');
					};

					fr.readAsDataURL(blob);
				}
			}
		};
	};
})(jQuery);


$(function()
{
	$('#post').delegate('.delete-button', 'click', function()
	{
		var postId = $(this).attr('href').substr(3);

		$window = $.windowConfirm(
		{
			windowTitle: 'Usuwanie postu',
			windowMessage: 'Czy na pewno chcesz usunąć ten post?',
			onYesClick: function()
			{
				$('#button-yes').addClass('disable').unbind('click');

				/**
				 * Ze wzgledow bezpieczenstwa wysylamy zadanie POST - nie GET
				 */
				$.ajax(
				{
					url: config.currentUrl,
					type: 'POST',
					data: $.extend({mode: 'delete', 'hash': hash, reason: $('select[name=reason]').val(), 'postId': postId}, config),
					success : function(data)
					{
						window.location = data;
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						alert(xhr.responseText);
						$('#button-yes').removeClass('disable');
					}
				});
			}
		});

		if (reasonList.length)
		{
			$('<select name="reason"><option value="0">-- wybierz powód usunięcia --</option></select>').appendTo($('div', $window));
			for (var item in reasonList)
			{
				$('select[name=reason]').append('<option value="' + item + '">' + reasonList[item] + '</option>');
			}
		}

		setTimeout(function() { $('#button-yes').focus() }, 100); // dla FF
	});

	$('#post').delegate('.merge-button', 'click', function()
	{
		var postId = $(this).attr('href').substr(3);

		$window = $.windowConfirm(
		{
			windowTitle: 'Łączenie postu',
			windowMessage: 'Czy na pewno chcesz połączyć ten post z poprzednim?',
			onYesClick: function()
			{
				$('#button-yes').addClass('disable');

				/**
				 * Ze wzgledow bezpieczenstwa wysylamy zadanie POST - nie GET
				 */
				$.ajax(
				{
					url: config.currentUrl,
					type: 'POST',
					data: $.extend({mode: 'merge', 'hash': hash, 'postId': postId}, config),
					success : function(data)
					{
						window.location = data;
						$('#button-yes').removeClass('disable');

						window.location.reload(true);
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						alert(xhr.responseText);
						$('#button-yes').removeClass('disable');
					}
				});
			}
		});
	});

	$('#move-button li a').click(function()
	{
		var forumPath = $(this).attr('href').substr(1);

		$window = $.windowConfirm(
		{
			windowTitle: 'Przenoszenie tematu',
			windowMessage: 'Czy na pewno chcesz przenieść ten temat?',
			onYesClick: function()
			{
				$('#button-yes').addClass('disable');

				/**
				 * Ze wzgledow bezpieczenstwa wysylamy zadanie POST - nie GET
				 */
				$.ajax(
				{
					url: config.currentUrl,
					data: $.extend({mode: 'move', reason: $('select[name=reason]').val(), path: forumPath, 'hash': hash}, config),
					type: 'POST',
					success : function(data, textStatus, xhr)
					{
						if (xhr.getResponseHeader('Content-type') == 'text/plain')
						{
							window.location.href = data;
						}
						else
						{
							alert('Wystąpił błąd podczas przenoszenia wątku. Strona nie istnieje lub wątek został już przeniesiony');
						}
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						alert('Błąd: ' + xhr.responseText);
						$('#button-yes').removeClass('disable');
					}
				});
			}
		});

		$('<select name="reason"><option value="0">-- wybierz powód przeniesienia --</option></select>').appendTo($('div', $window));
		for (var item in reasonList)
		{
			$('select[name=reason]').append('<option value="' + item + '">' + reasonList[item] + '</option>');
		}

		setTimeout(function() { $('#button-yes').focus() }, 100); // dla FF
	});

	$('#status-button li a').click(function()
	{
		var statusId = $(this).attr('data-status');

		$window = $.windowConfirm(
		{
			windowTitle: 'Zmień status wątku',
			windowMessage: 'Czy na pewno chcesz zmienić status wątku?',
			onYesClick: function()
			{
				$('#button-yes').addClass('disable');

				/**
				 * Ze wzgledow bezpieczenstwa wysylamy zadanie POST - nie GET
				 */
				$.ajax(
				{
					url: config.currentUrl,
					type: 'POST',
					data: $.extend({mode: 'status', 'hash': hash, status: statusId}, config),
					success : function(data)
					{
						$('#button-yes').removeClass('disable');

						$.closeWindow();
						window.location.reload(true);
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						alert(xhr.responseText);
						$('#button-yes').removeClass('disable');
					}
				});
			}
		});
	});

	$('#edit-subject-button').click(function()
	{
		$window = $.windowConfirm(
		{
			windowTitle: 'Zmień tytuł wątku',
			windowMessage: 'Czy na pewno chcesz zmienić tytuł wątku na następujący?',
			onYesClick: function()
			{
				$('#button-yes').addClass('disable');

				/**
				 * Ze wzgledow bezpieczenstwa wysylamy zadanie POST - nie GET
				 */
				$.ajax(
				{
					url: config.currentUrl,
					type: 'POST',
					data: $.extend({mode: 'subject', 'hash': hash, subject: $('#subject').val()}, config),
					success : function(data)
					{
						$('#button-yes').removeClass('disable');

						window.location.reload(true);
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						alert(xhr.responseText);
						$('#button-yes').removeClass('disable');
					}
				});
			}
		});

		$('<input type="text" id="subject" value="' + $('#thread-button a').attr('title').replace(/"/g, '&quot;') + '" />').appendTo($('div', $window));

		$(window).unbind('keydown');
		$('#subject').keydown(function(e)
		{
			keyCode = e.keyCode || window.event.keyCode;

			if (keyCode == 27)
			{
				$.closeWindow();
			}
			if (keyCode == 13)
			{
				$('#button-yes').trigger('click');
				return false;
			}
		}).focus();
	});

	var mutex = false; // dla idiotow ktorzy klikaja dwa razy na przycisk do glosowania

	$('#post').delegate('.vote-up, .vote-down', 'click', function()
	{
		if (mutex)
		{
			return false;
		}
		var element = this;

		var postId = $(element).parent().find(':input[type=hidden]').val();
		var isUpButton = $(element).hasClass('vote-up');

		$.ajax(
		{
			url: config.currentUrl,
			data: {mode: 'vote', 'postId': postId, 'value': (isUpButton ? 1 : -1), 'hash': hash},
			type: 'POST',
			beforeSend : function()
			{
				mutex = true;
			},
			success : function(data)
			{
				if (isUpButton)
				{
					$(element).toggleClass('vote-up-on');
					$(element).parent().find('.vote-down').removeClass('vote-down-on');
				}
				else
				{
					$(element).toggleClass('vote-down-on');
					$(element).parent().find('.vote-up').removeClass('vote-up-on');
				}

				$(element).parent().find('.vote-count').text(data);
				mutex = false;
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				mutex = false;
				$.windowAlert({windowTitle: 'Błąd', windowMessage: xhr.responseText});
			}
		});
	});

	$('#post').delegate('.unsolved', 'click', function()
	{
		var element = this;
		var postId = $(this).prevAll(':input[type=hidden]').val();

		$.ajax(
		{
			url: config.currentUrl,
			type: 'POST',
			data: {mode: 'solved', 'postId': postId},
			success: function()
			{
				$(element).toggleClass('solved');
				$('.unsolved').not(element).removeClass('solved');
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				$(element).removeClass('solved');
				$.windowError({windowTitle: 'Błąd', windowMessage: xhr.responseText});
			}
		});
	});

	$('#watch-button').click(function()
	{
		var element = this;

		$.post(config.currentUrl, {mode: 'watch', 'topicId': config.topicId},
			function(data)
			{
				if (data == '1')
				{
					$(element).addClass('watch-on').text('Wątek obserwowany').attr('title', 'Kliknij, aby zaprzestać obserwacji wątku');
					$('#quick-form input[name=watch]').val(1);
				}
				else
				{
					$(element).removeClass('watch-on').text('Obserwuj wątek').attr('title', 'Kliknij, aby obserwować ten wątek');
					$('#quick-form input[name=watch]').val(0);
				}
			}
		);
	});

	$('#lock-button').click(function()
	{
		var element = $(this);

		$.ajax(
		{
			url: config.currentUrl,
			type: 'POST',
			dataType: 'json',
			data: {mode: 'lock', topicId: config.topicId, 'hash': hash},
			beforeSend : function()
			{
				element.text('Proszę czekać...');
			},
			success : function(data)
			{
				if (data.error)
				{
					alert(data.error);
				}
				if (data.result)
				{
					element.addClass('lock').text('Odblokuj temat').attr('title', 'Temat jest zablokowany. Kliknij, aby odblokować temat');
				}
				else
				{
					element.removeClass('lock').text('Zablokuj temat').attr('title', 'Kliknij, aby zablokować temat');
					$('#lock-message').remove();
				}
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				alert('Błąd: ' + xhr.responseText);
			}
		});
	});

	var editForm = {};
	var postContent = {};
	var postData = {};

	$('#post').delegate('.fast-edit-button', 'click', function()
	{
		var $button = $(this);
		var postId = $(this).attr('data-post-id');

		$button.toggleClass('post-button-checked');
		var element = $('.post-content[data-post-id=' + postId + ']');

		var onFastEditSubmit = function()
		{
			var fastForm = this;

			$.ajax(
			{
				url: config.currentUrl + '?mode=fastedit&postId=' + postId,
				type: 'POST',
				dataType: 'json',
				data: {content: $('textarea', fastForm).val()},
				beforeSend: function()
				{
					$(':input', fastForm).attr('disabled', 'disabled');
				},
				success: function(data)
				{
					$(':input', fastForm).removeAttr('disabled');

					if (data.error)
					{
						$.getScript(scriptPath + '/error.js', function()
						{
							$('textarea', fastForm).setError(data.error);
						});
					}
					else
					{
						/*
						 * Ponizsze ID bedzie mial "dymek" z bledem podpowiedzi (jezeli w ogole istnieje)
						 */
						$('#box-').hide();

						element.hide().html(data.content).fadeIn(900);
						$button.removeClass('post-button-checked');

						// aplikowanie paska narzedziowego dla kodu zrodlowego, ktore nie jest kolorowany
						$('.post-content[data-post-id=' + postId + '] pre.text').each(applySyntaxToolbar);
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					$(':input', fastForm).removeAttr('disabled');

					if (xhr.status == 404)
					{
						alert('Podany wątek został usunięty');
					}
					else
					{
						alert(xhr.responseText);
					}
				}
			});

			return false;
		};

		if ($button.hasClass('post-button-checked'))
		{
			postContent[postId] = element.html();

			$.ajax(
			{
				url: config.currentUrl,
				type: 'GET',
				data: $.extend({mode: 'fastedit', postId: $(this).attr('data-post-id')}, config),
				success : function(data)
				{
					postData[postId] = data;
					element.html('');

					editForm[postId] = $('<form><textarea></textarea><input title="Kliknij, aby zapisać (Ctrl+Enter)" type="submit" value="Zapisz zmiany" /><br style="clear: both;" /></form>').hide();
					element.append(editForm[postId]);

					editForm[postId].show().submit(onFastEditSubmit);
					$('textarea', editForm[postId]).val(data).keydown(onSubmitShortcut).autogrow().findUsers().focus();
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					alert(xhr.responseText);
				}
			});

			$('.syntax-toolbar[data-post-id="' + postId + '"]').remove();
		}
		else
		{
			if (postData[postId] != $('textarea', editForm[postId]).val())
			{
				$.windowConfirm(
				{
					windowTitle: 'Zapisać zmiany?',
					windowMessage: 'Zmiany nie zostały zapisane. Zapisać?',
					onYesClick: function()
					{
						$.closeWindow();
						editForm[postId].submit();
					},
					onNoClick: function()
					{
						$.closeWindow();
						element.html(postContent[postId]);
					}
				});
			}
			else
			{
				element.html(postContent[postId]);
			}

			// aplikowanie paska narzedziowego dla kodu zrodlowego, ktore nie jest kolorowany
			$('.post-content[data-post-id=' + postId + '] pre.text').each(applySyntaxToolbar);
		}
	});

	/*
	 * Obsluga POST dla formularza szybkiej odpowiedzi
	 */
	$('#quick-form').submit(function()
	{
		var form = this;
		var fields = {'hash': hash, 'content': $('textarea[name=content]', form).val(),	'enableSmilies': $('input[name=enableSmilies]').val()};

		$(':input', form).attr('disabled', 'disabled');

		if ($(':input[name=watch]', form).val() == '1')
		{
			fields['watch'] = true;
		}

		$.ajax(
		{
			type: 'POST',
			url: this.action,
			data: fields,
			timeout: 10000,
			beforeSend : function()
			{
				$('input[type=submit]', form).val('Proszę czekać...');
			},
			success : function(data, textStatus, xhr)
			{
				if (data.errors)
				{
					$.windowError({windowTitle: 'Błąd', windowMessage: 'Proszę wpisać treść'});
				}
				else if (xhr.getResponseHeader('Content-type') == 'text/plain')
				{
					$('textarea', form).val('');
					window.location.href = data;
				}
				else
				{
					alert('Wystąpił błąd podczas dodawania postu, lecz prawdopodobnie Twój post został zapisany');
				}
			},
			complete: function(xhr, textStatus)
			{
				$('input[type=submit]', form).val('Wyślij');
				$(':input', form).removeAttr('disabled');
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				if (ajaxOptions == 'timeout')
				{
					alert('Czas odpowiedzi serwera został przekroczony. Być może połączenie z internetem zostało utracone.');
				}
				else
				{
					if (xhr.getResponseHeader('Content-type') == 'text/plain')
					{
						alert('Błąd: ' + xhr.responseText);
					}
					else
					{
						alert('Wystąpił błąd. Twój post prawdopodobnie nie został dodany. Jeżeli problem będzie się powtarzał, skontaktuj się z administratorem witryny');
					}
				}
			}
		});

		return false;
	});

	/*
	 * Obsluga zdarzenia onFocus dla <textarea> szybkiej odpowiedzi
	 */
	$('#quick-form textarea').focus(function()
	{
		if ($(this).val() == 'Kliknij, aby napisać szybką odpowiedź...')
		{
			$(this).val('');
		}

		$('#quick-form :submit').show();

	}).autogrow().keydown(onSubmitShortcut).findUsers();

	$.fn.commentInsert = function()
	{
		var form = this;

		var postId = $('input[name=postId]', form).val();
		var commentId = $('input[name=commentId]', form).val();

		var comment = jQuery.trim($('textarea', form).val());

		if (comment.length == 0 || comment.length > 580)
		{
			$.getScript(scriptPath + '/error.js', function()
			{
				$('textarea', form).setError('Długość komentarza musi mieścić się w zakresie 0-580 znaków');
			});

			return false;
		}
		else
		{
			$('#box-comment').remove();
		}

		$.ajax(
		{
			url: config.currentUrl + (typeof commentId != 'undefined' ? '?id=' + commentId : ''),
			dataType: 'json',
			data: {mode: 'comment', 'postId': postId, 'comment': comment, 'hash': hash},
			type: 'POST',
			beforeSend : function()
			{
				$(':input', form).attr('disabled', 'disabled');
				$(':submit', form).val('Proszę czekać...');
			},
			success : function(data)
			{
				$('#box-comment').remove();

				if (typeof commentId == 'undefined')
				{
					form.before('<div data-comment-id="' + data.id + '" style="display: none;">' + data.text + ' - ' + data.user + ' <span title="' + data.date + '" class="timestamp" data-timestamp="' + data.timestamp + '">' + data.time + '</span> <span title="Edytuj ten komentarz" class="comment-edit">&nbsp;</span> <span title="Usuń ten komentarz" class="comment-delete">&nbsp;</span></div>');
					form.prev('div:first').css('background-color', HIGHLIGHT_COLOR).fadeIn(1200);

					setTimeout(function()
					{
						form.prev('div:first').animate({backgroundColor: '#fafafa'}, HIGHLIGHT_DELAY);
					}, 1000);
				}
				else
				{
					form.parent().hide().html(data.text + ' - ' + data.user + ' <span title="' + data.date + '" class="timestamp" data-timestamp="' + data.timestamp + '">' + data.time + '</span> <span title="Edytuj ten komentarz" class="comment-edit">&nbsp;</span> <span title="Usuń ten komentarz" class="comment-delete">&nbsp;</span>').fadeIn(1200).removeClass('edit');
				}

				$('textarea', form).val('');
				form.hide();

				$('#post .comment-button[data-post-id=' + postId + ']').removeClass('post-button-checked');

				if (data.isSubscribe)
				{
					$('#post .subscribe-button[data-post-id=' + postId + ']').addClass('post-button-checked');
				}
			},
			complete: function()
			{
				$(':input', form).removeAttr('disabled');
				$(':submit', form).val('Dodaj komentarz');
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				if (xhr.status == 404)
				{
					alert('Podany wątek został usunięty');
				}
				else
				{
					alert('Błąd: ' + xhr.responseText);
				}
			}
		});

		return false;
	};

	$('#post').delegate('.comment-button', 'click', function()
	{
		$(this).toggleClass('post-button-checked');

		var element = $('#post-comment-' + $(this).attr('data-post-id'));
		var form = element.find('form');
		form.toggle();

		if (form.css('display') == 'block')
		{
			element.show();
			form.find('textarea').focus();
		}
		else
		{
			if (!$('div', element).length)
			{
				element.hide();
			}
		}
	});

	$('#post .comments').delegate('form', 'submit', function(e)
	{
		$(this).commentInsert();

		e.preventDefault();
		return false;
	})
	.delegate('textarea', 'keyup', function(e)
	{
		if (parseInt($(this).val().length) > 580)
		{
			$(this).val($(this).val().substr(0, 580));
		}
		$('p strong', $(this).parents('form')).text(580 - parseInt($(this).val().length));
	})
	.delegate(':submit', 'click', function(e)
	{
		$(this).parents('form').commentInsert();

		e.preventDefault();
		return false;
	});

	/*
	 * <textarea> w komentarzach
	 */
	$('#body .comments textarea').keydown(onSubmitShortcut);
	$('#body .comments textarea').one('focus', function()
	{
		$(this).keydown(onSubmitShortcut).autogrow().findUsers();
	});

	/*
	 * Obsluga klikniecia przycisku "Pokaz pozostale" w komentarzach
	 */
	$('#post .comments').delegate('.show-comments', 'click', function()
	{
		$(this).nextAll('div:hidden').fadeIn(1000);
		$(this).remove();
	});

	$('#post .comments').delegate('.comment-delete', 'click', function()
	{
		var element = $(this);
		var commentId = element.parent().attr('data-comment-id');

		$.getScript(scriptPath + '/window.js', function()
		{
			$.windowConfirm(
			{
				windowTitle: 'Usuwanie komentarza',
				windowMessage: 'Czy na pewno chcesz usunąć ten komentarz?',
				onYesClick: function()
				{
					$.closeWindow();

					$.post(config.currentUrl, {mode: 'comment', id: commentId, 'hash': hash},
						function(data)
						{
							element.parent().fadeOut(900);
						}
					);
				}
			});
		});
	});

	$('#post .comments').delegate('.comment-edit', 'click', function()
	{
		var element = $(this).parent('div');
		element.addClass('edit');

		var commentId = element.attr('data-comment-id');
		var commentContent = element.html();

		var postId = element.parents('div.comments').attr('data-post-id');

		$.ajax(
		{
			url: config.currentUrl,
			type: 'GET',
			data: $.extend({mode: 'comment', id: commentId}, config),
			success : function(data)
			{
				charCount = 580 - data.length;
				element.html('');

				var form = $('<form><input type="hidden" name="postId" value="' + postId + '" /><input type="hidden" name="commentId" value="' + commentId + '" /><textarea cols="90" rows="2" style="width: 98%"></textarea><p>Pozostało <strong>' + charCount + '</strong> znaków</p><input title="Kliknij, aby zapisać (Ctrl+Enter)" type="submit" value="Zapisz zmiany" /><input type="reset" title="Anuluj" value="Anuluj" /></form><br style="clear: both;" />').hide();
				element.append(form);

				form.show();
				$('textarea', form).val(data).keydown(onSubmitShortcut).autogrow().findUsers().focus();

				$('input[type=reset]', form).click(function()
				{
					element.html(commentContent).removeClass('edit');
				});
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				if (xhr.status == 404)
				{
					alert('Podany wątek został usunięty');
				}
				else
				{
					alert(xhr.responseText);
				}
			}
		});
	});

	$('#post .post-bottom').delegate('.subscribe-button', 'click', function()
	{
		$(this).toggleClass('post-button-checked');
		$.post(config.currentUrl, {mode: 'subscribe', id: $(this).attr('data-post-id'), 'hash': hash});
	});

	$('#topic-toggle').click(function()
	{
		$(this).parents('table').children('tbody').toggle('fast');
	});

	var toolTipTimer;

	$('#post').delegate('a.login, a.user-name', 'mouseover mouseout', function(e)
	{
		if (e.type == 'mouseover')
		{
			clearTimeout(toolTipTimer);
			var toolTip = $('.tool-tip-wrapper');

			if (!toolTip.length)
			{
				var toolTip = $('<div class="tool-tip-wrapper"><div></div></div>');
				toolTip.appendTo(document.body);
			}

			toolTip.children('div').html('<img style="vertical-align: middle" width="20" height="20" src="' + $(this).data('photo') + '" /> <a style="padding-left: 2px; padding-right: 30px;" href="' + $(this).attr('href') + '">' + ($(this).text().charAt(0) == '@' ? $(this).text().substr(1) : $(this).text()) + '</a> <a title="Znajdź posty użytkownika" class="find-post-button" href="' + $(this).data('find-url') + '"></a>  <a title="Napisz wiadomość prywatną" class="send-message-button" href="' + $(this).data('pm-url') + '"></a>');

			var p = $(this).offset();
			var height = $(this).outerHeight();
			var width = $(this).outerWidth();

			toolTip.css(
			{
				top: (p.top - height) - toolTip.outerHeight(),
				left: p.left + width / 2 - toolTip.outerWidth() / 2

			}).fadeIn('fast');

		}
		else if (e.type == 'mouseout')
		{
			toolTipTimer = setTimeout(function()
			{
				$('.tool-tip-wrapper').fadeOut('fast');

			}, 500);
		}

	});

	$('body').delegate('.tool-tip-wrapper div', 'mouseover mouseout', function(e)
	{
		if (e.type == 'mouseover')
		{
			clearTimeout(toolTipTimer);

		}
		else if (e.type == 'mouseout')
		{
			toolTipTimer = setTimeout(function()
			{
				$('.tool-tip-wrapper').hide();

			}, 500);
		}
	});

	var buttonSet = [

		{ id: 'sql', title: 'SQL'},
		{ id: 'csharp', title: 'C#' },
		{ id: 'cpp', title: 'C++' },
		{ id: 'c', title: 'C' },
		{ id: 'php', title: 'PHP' },
		{ id: 'delphi', title: 'Delphi' }

	];

	var itemList = [

		{ id: 'asm', value: 'Assembler' },
		{ id: 'bash', value: 'Bash' },
		{ id: 'c', value: 'C' },
		{ id: 'cpp', value: 'C++' },
		{ id: 'csharp', value: 'C#' },
		{ id: 'css', value: 'CSS' },
		{ id: 'delphi', value: 'Delphi' },
		{ id: 'diff', value: 'Diff' },
		{ id: 'fortan', value: 'Fortran' },
		{ id: 'html', value: 'HTML' },
		{ id: 'ini', value: 'INI' },
		{ id: 'java', value: 'Java' },
		{ id: 'javascript', value: 'JavaScript' },
		{ id: 'jquery', value: 'jQuery' },
		{ id: 'pascal', value: 'Pascal' },
		{ id: 'perl', value: 'Perl' },
		{ id: 'php', value: 'PHP' },
		{ id: 'plsql', value: 'PL/SQL' },
		{ id: 'python', value: 'Python' },
		{ id: 'ruby', value: 'Ruby' },
		{ id: 'sql', value: 'SQL' },
		{ id: 'vbnet', value: 'Visual Basic.NET' },
		{ id: 'xml', value: 'XML' }
	];

	var zIndex = 1000;

	var applySyntaxToolbar = function()
	{
		var $this = $(this);

		if ($this.height() < 20)
		{
			return this;
		}
		var toolBar = $('<div data-post-id="' + $this.parents('.post-content').data('post-id') + '" class="syntax-toolbar syntax-toolbar-opacity"><a class="syntax-toolbar-button button-misc" title="Zmień kolorowanie składni"></a><ul></ul></div>').css('z-index', zIndex--);
		var p = $(this).offset();

		$(buttonSet).each(function()
		{
			toolBar.prepend('<a title="' + $(this).attr('title') + '" class="syntax-toolbar-button button-'+ $(this).attr('id') + '" data-lang="' + $(this).attr('id') + '"></a>');
		});

		$('.button-misc', toolBar).click(function()
		{
			$('ul', toolBar).toggle();
			$(this).toggleClass('button-misc-toggle');
		});

		$(itemList).each(function()
		{
			toolBar.children('ul').append('<li data-lang="' + $(this).attr('id') + '">' + $(this).attr('value') + '</li>');
		});

		$('li, a.syntax-toolbar-button[data-lang]', toolBar).click(function()
		{
			var item = this;

			$.ajax(
			{
				url: config.currentUrl,
				type: 'POST',
				data: $.extend({mode: 'highlight', 'language': $(item).data('lang'), content: $this.text()}, config),
				success : function(data)
				{
					$this.html($(data).html());
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					if (xhr.status == 404)
					{
						alert('Podany wątek został usunięty');
					}
					else
					{
						alert(xhr.responseText);
					}
				}
			});
		});

		toolBar.css({'top':p.top + 10, 'left':p.left + $this.width() - 170}).appendTo($('body'));
	};

	$(window).load(function()
	{
		$('#post pre.text').each(applySyntaxToolbar);
	});

	$('body').delegate('.syntax-toolbar', 'mouseover mouseout', function(e)
	{
		if (e.type == 'mouseover')
		{
			$(this).removeClass('syntax-toolbar-opacity');
		}
		else if (e.type == 'mouseout')
		{
			$(this).addClass('syntax-toolbar-opacity');
		}
	});

	$('body').delegate('.syntax-toolbar', 'mouseleave', function()
	{
		$('ul', this).hide();
		$('.button-misc', this).removeClass('button-misc-toggle');
	});

	$(window).resize(function()
	{
		$('#post pre.text').each(function()
		{
			var $this = $('.syntax-toolbar[data-post-id=' + $(this).parents('.post-content').data('post-id') + ']');

			if ($(this).width() > 400)
			{
				var p = $(this).offset();
				$this.css('left', p.left + $(this).width() - 170).show();
			}
			else
			{
				$this.hide();
			}
		});
	});

	if ('onhashchange' in window)
	{
		var onHashChange = function()
		{
			var prefix = window.location.hash.charAt(1);

			if (prefix == 'i')
			{
				var postId = parseInt(window.location.hash.substring(3));
				if (postId)
				{
					$('#id' + postId).parents('tr').next('tr').children('td').css('background-color', HIGHLIGHT_COLOR);

					$('#container').one('mousemove', function()
					{
						$('#id' + postId).parents('tr').next('tr').children('td').animate({backgroundColor: '#FAFAFA'}, HIGHLIGHT_DELAY);
					});
				}

			}
			else
			{
				var commentId = parseInt(window.location.hash.substring(9));
				if (commentId)
				{
					var commentBox = $('#comment-' + commentId);

					if (commentBox.is(':hidden'))
					{
						$('div:hidden', commentBox.parent()).show();
						$('.show-comments', commentBox.parent()).remove();
					}
					commentBox.css('background-color', HIGHLIGHT_COLOR);

					$('#container').one('mousemove', function()
					{
						$('#comment-' + commentId).animate({backgroundColor: '#FAFAFA'}, HIGHLIGHT_DELAY);
					});
				}
			}
		};

		window.onhashchange = onHashChange;
		onHashChange();
	}

	/// FF suxx... realizowanie text-overflow: ellipsis w ff
	if ($.browser.mozilla && $.browser.version < '7.0')
	{
		$('#thread-button a').each(function()
		{
			var maxWidth = $(this).css('max-width').substring(0, 3); // obciecie suffixu "px" zakladamy, ze szerokosc jest 3-cyfrowa

			var ellipsis = $('<span></span>').css(
			{
				position:			'absolute',
				top:				-10000,
				left:				-10000
			}).appendTo('body');

			ellipsis.text($(this).attr('title'));

			if (ellipsis.width() > maxWidth)
			{
				// poki co - wartosc wpisana na stale (30)
				$(this).text($(this).text().substr(0, 40) + '...');
			}
		});
	}

});

/*
 Color animation jQuery-plugin
 http://www.bitstorm.org/jquery/color-animation/
 Copyright 2011 Edwin Martin <edwin@bitstorm.org>
 Released under the MIT and GPL licenses.
 */
(function(d){function i(){var b=d("script:first"),a=b.css("color"),c=false;if(/^rgba/.test(a))c=true;else try{c=a!=b.css("color","rgba(0, 0, 0, 0.5)").css("color");b.css("color",a)}catch(e){}return c}function g(b,a,c){var e="rgb"+(d.support.rgba?"a":"")+"("+parseInt(b[0]+c*(a[0]-b[0]),10)+","+parseInt(b[1]+c*(a[1]-b[1]),10)+","+parseInt(b[2]+c*(a[2]-b[2]),10);if(d.support.rgba)e+=","+(b&&a?parseFloat(b[3]+c*(a[3]-b[3])):1);e+=")";return e}function f(b){var a,c;if(a=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(b))c=
	[parseInt(a[1],16),parseInt(a[2],16),parseInt(a[3],16),1];else if(a=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(b))c=[parseInt(a[1],16)*17,parseInt(a[2],16)*17,parseInt(a[3],16)*17,1];else if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(b))c=[parseInt(a[1]),parseInt(a[2]),parseInt(a[3]),1];else if(a=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(b))c=[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10),parseFloat(a[4])];return c}
	d.extend(true,d,{support:{rgba:i()}});var h=["color","backgroundColor","borderBottomColor","borderLeftColor","borderRightColor","borderTopColor","outlineColor"];d.each(h,function(b,a){d.fx.step[a]=function(c){if(!c.init){c.a=f(d(c.elem).css(a));c.end=f(c.end);c.init=true}c.elem.style[a]=g(c.a,c.end,c.pos)}});d.fx.step.borderColor=function(b){if(!b.init)b.end=f(b.end);var a=h.slice(2,6);d.each(a,function(c,e){b.init||(b[e]={a:f(d(b.elem).css(e))});b.elem.style[e]=g(b[e].a,b.end,b.pos)});b.init=true}})(jQuery);