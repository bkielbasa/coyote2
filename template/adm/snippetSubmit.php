<script type="text/javascript">
<!--

editAreaLoader.init({
		id : 'snippet-content',
		syntax: 'php',
		allow_toggle: false,
		start_highlight: true,
		language: 'pl',
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php,c,cpp,java,pas,perl,python,robotstxt,ruby,sql,vb,xml'
});

//-->
</script>

<h1>Edycja skrawka kodu</h1>

<p>Więcej o edycji skrawków kodu, znajdziesz w dokumentacji projektu! Jeżeli snippet posiada opcje konfiguracji
możesz je określić: <code>{{Snippet:Nazwa_Snippetu?pole_konfiguracji=wartość}}</code></p>

<?php if (isset($isFile)) : ?>
<p class="note">UWAGA! Snippet jest fizyczną klasą istniejącą na dysku. Jeżeli nie posiadasz praw zapisu do pliku, nie możesz dokonywać zmian w kodzie.</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label title="Nazwa skrawka kodu. Dany kod będzie wykonywany, w momencie, gdy w dokumencie znajduje się wywołanie {{Snippet:Nazwa_Skrawka}}">Nazwa <em>*</em></label>
				{{Snippet:<?= Form::input('name', $input->post('name', @$snippet_name)); ?>}}
			</li>
			<li>
				<label title="Opcjonalnie. Jeżeli dany skrawek istnieje, fizycznie na dysku, należy określić nazwę pliku/klasy. Plik musi znajdować się w podkatalogu /lib/snippet. Np: /lib/snippet/foo.class.php. Nazwa klasy: Snippet_Foo. Wówczas, w tym polu należy wpisać wartość foo">Nazwa klasy</label>
				<?= Form::input('class', $input->post('class', @$snippet_class)); ?>
			</li>
			<li>
				<label title="Opis nie ma większego znaczenia. Wyświetlany jest na liście dostępnych skrawków kodu">Opis</label>
				<?= Form::input('text', $input->post('text', @$snippet_text)); ?>
			</li>

			<li>
				<label title="Jeżeli skrawek kodu, nie jest fizycznie istniejącą klasą, w tym polu należy umieścić kod PHP danego skrawka">Kod PHP</label>

				<?= Form::textarea('content', $input->post('content', @$snippet_content), array('id' => 'snippet-content', 'style' => 'width: 98%', 'cols' => 110, 'rows' => 35)); ?>
			</li>

			<li>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>