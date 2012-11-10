<script type="text/javascript">
<!--

	var hash = '<?= $hash; ?>';
	var preventAjax = true;

	$(function()
	{
		$.posting.init({currentUrl: '<?= url($page->getLocation()); ?>', forumId: '<?= $page->getForumId(); ?>', topicId: 0});
		$('.page').loadWikiEditor();

		var toolTipHints = $('#hint-subject, #hint-content, #hint-tag, #hint-username');

		$(':input[name=subject], :input[name=content], :input[name=tag], :input[name=username]').focus(function()
		{
			if ('hint-' + $(this).attr('name') != $('.sidebar-hint:visible').attr('id'))
			{
				toolTipHints.each(function()
				{
					$(this).hide();
				});

				$('#hint-' + $(this).attr('name')).fadeIn(1000);
			}
		});

		if ($('input[name=antispam]').length)
		{
			$('input[name=postkey]').val($('input[name=antispam]').val()).parent().hide();
		}

		/*
		 * Tymczasowy kod do edukowania uzytkownikow: jezeli pierwszym
		 * znakiem w polu temat jest "[", to oznacza, ze uzytkownik chce wstawic prefiks
		 * w temacie. Wyswietlamy informacje, czy nie chce przypadkiem umiescic
		 * tagu zamiast prefiksu
		 */
		$('input[name=subject]').keyup(function(e)
		{
			if ($.trim($('input[name=subject]').val()).substr(0, 1) == '[')
			{
				if (!$('#box-subject').length)
				{
					$('input[name=subject]').setError('Jeżeli chcesz w temacie umieścić prefiks, wstaw go w polu <b><i>Tagi</i></b>');
					$(':submit').attr('disabled', 'disabled');
				}
			}
			else
			{
				$('input[name=subject]').hideError();
				$(':submit').removeAttr('disabled');
			}
		});

		if ($('input[name=tag]').length)
		{
			var p = $('input[name=tag]').position();

			$('.box-hint').css('top', p.top);
			$('.box-hint').css('left', p.left + 240).show();

			$('.box-hint-content a').click(function()
			{
				$('input[name=tag]').val($('input[name=tag]').val() + $(this).attr('href').substr(1) + ' ');

				return false;
			});

			setTimeout(function()
			{
				$('.box-hint').remove();
			}, 6000);
		}
	});
//-->
</script>

<a style="display: none;" title="Strona główna forum" href="<?= url('@forum'); ?>" data-shortcut="g+i">Strona główna forum</a>

<div class="box-hint" style="display: none; position: absolute;">
	<div class="box-hint-top"><div></div></div>

	<div class="box-hint-content">
		W polu <i>Tagi</i> możesz umieścić nazwy języków czy technologii - np. <a href="#C++">C++</a>, <a href="#SQL">SQL</a>, <a href="#jQuery">jQuery</a>, <a href="#CSS">CSS</a>
	</div>

	<div class="box-hint-bottom"><div></div></div>
</div>

<div id="body">
	<ul class="f-menu-top">
		<?= Topic::buildForumMenu(''); ?>

 		<li id="submit-top" class="focus" title="Napisz nowy temat w tym dziale"><a><span>Nowy temat</span></a></li>
	</ul>
	<div style="clear: both;"></div>

	<div class="page">
		<div id="sidebar">

			<div id="hint-subject" class="sidebar-hint">
				<h4>Jak tytułować wątki</h4>

				<p>► Staraj się nadawać wątkom znaczące tematy!</p>
				<p>► Unikaj jednowyrazowych tematów wątków!</p>
				<p>► Wątki o temacie <cite>pomoc</cite>, <cite>pomocy</cite>, <cite>help</cite> będą usuwane!</p>
				<p>► Sprawdź w wyszukiwarce, czy wątek podobny do Twojego nie pojawił się wcześniej na forum.</p>
				<p>► Czy Twój wątek rzeczywiście pasuje do tej kategorii?</p>
				<p>► Unikaj stosowania prefiksów w tytułach wątków! Zamiast tego używaj <strong>tagów</strong>!</p>
			</div>

			<div id="hint-content" class="sidebar-hint" style="display: none;">
				<h4>Jak formatować treść postu</h4>

				<p>► Kod źródłowy umieszczaj pomiędzy znacznikami <code>&lt;code&gt;</code> a <code>&lt;/code&gt;</code> np. <code>&lt;code=php&gt; &lt;/code&gt;</code></p>
				<p>► Stosuj <code><strong>**pogrubienie**</strong> i <cite>//pochylenie//</cite></code></p>
				<p>► <code><tt>''**brak formatowania**''</tt></code></p>
				<p>► <code><kbd>`polecenia języka programowania`</kbd></code></p>
				<p>► Symbole <code>*</code> i <code>#</code> na początku linii powodują wypunktowanie i numerowanie</p>
				<p>► Zwracaj uwagę na gramatykę i ortografię!</p>
				<p>► <cite>Ctrl+Enter</cite> publikuje post.</p>
				<p>► <cite>Shift+Tab</cite> tworzy wcięcie.</p>
				<p>► <cite>Ctrl+V</cite> wkleja obrazek ze schowka (<strong>tylko Chrome</strong>)</p>
			</div>

			<div id="hint-tag" class="sidebar-hint" style="display: none;">
				<h4>Jak tagować wątki</h4>

				<p>► Tagi pozwalają grupować podobne wątki.</p>
				<p>► Oddzielaj tagi przecinkiem lub spacją.</p>
				<p>► Unikaj zaimków i przyimków.</p>
				<p>► Symbol <tt>-</tt> służy do łączenia wyrazów - np. <tt>bazy-danych</tt>.</p>
			</div>

			<div id="hint-username" class="sidebar-hint" style="display: none;">
				<h4>Nazwa użytkownika</h4>

				<p>► Jesteś anonimowym użytkownikiem - <?= Html::a(url('Rejetracja'), 'zarejestruj się'); ?>.</p>
				<p>► Anonimowi użytkownicy nie mogą edytować ani komentować postów.</p>
				<p>► Anonimowi użytkownicy nie dostają powiadomień (o usunięciu, przeniesieniu wątku).</p>
			</div>

		</div>

		<?= $form; ?>
	</div>

</div>