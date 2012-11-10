/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

var buttonSet = [

	new Button({
		name: 'Bold',
		openWith: "**",
		closeWith: "**",
		className: 'wiki-bold',
		title: 'Pogrubienie'
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
	new Button({
		name: 'Variable',
		openWith: '@@',
		closeWith: '@@',
		className: 'wiki-var',
		title: 'Zmienne'
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
	}),
	new Separator(),
	new Button({
		name: 'plain',
		className: 'wiki-plain',
		openWith: '<plain>',
		closeWith: '</plain>',
		title: 'Brak formatowania',
		text: ' '
	})
];

var onSubmitShortcut = function(e)
{
	if (e.ctrlKey && e.keyCode == 13)
	{
		$(this).closest('form').submit();
	}
};

$(function()
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

	$('.message-header').click(function(e)
	{
		var $target = $(e.target);

		if (!$target.is('.delete-icon'))
		{
			$('.snippet', this).toggle();
			$('.date', this).toggle();

			$(this).next().toggle();

			if ($('.snippet', this).is(':hidden'))
			{
				$('td', this).css('border-bottom', 'none');
			}
			else
			{
				$('td', this).css('border-bottom', '1px solid #DADFE0');
			}
		}
	});

	$('#message-form textarea').one('focus', function()
	{
		$(this).prev('img').remove();
		$(this).wikiEditor(buttonSet).height('50px');

		$('.wiki-editor').css('overflow', 'hidden').append('<input type="submit" value="Wyślij" style="float: left; margin-left: 10px; font-size: 11px" tabindex="2"/>');
		$(this).keydown(onSubmitShortcut);

		$('form').submit(function()
		{
			$(':submit', this).attr('disabled', 'disabled');
		});

		setTimeout(function()
		{
			$('#message-form textarea').autogrow().focus();

		}, 200);
	});

	$('.delete-icon, .trash').click(function()
	{
		var pmId = $(this).attr('data-pm-id');

		$.windowConfirm(
		{
			windowTitle: 'Usuwanie wiadomości',
			windowMessage: 'Czy chcesz usunąć wiadomość?',
			onYesClick: function()
			{
				window.location.href = deleteUrl + pmId;
			}
		});
	});
})