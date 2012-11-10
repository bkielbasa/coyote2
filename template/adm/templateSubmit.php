<script type="text/javascript">
<!--
	editAreaLoader.init({
		id : 'template_content',
		syntax: 'html',
		start_highlight: true,
		language: 'pl',
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php'
	});
//-->
</script>

<div id="template" style="overflow: hidden;">
	<?php include('_partialTemplate.php'); ?>

	<div id="template-content">

		<h2 style="float: left;"><?= basename($path); ?></h2>

		<?= Form::open('', array('method' => 'post')); ?>

			<div style="float: right;">
				<?= Form::submit('', 'Zapisz', array('class' => 'accept-button')); ?>
			</div>
			<br style="clear: both;" />

			<div class="page-menu">
				<ul>
					<li><a class="focus">Zawartość</a></li>
					<li><a>Tytuł strony</a></li>
					<li><a>Layout</a></li>
					<li><a>CSS</a></li>
					<li><a>JavaScript</a></li>
				</ul>
			</div>
			<div class="menu-block">
				<fieldset>
					<ol>
						<li>
							<label>Nazwa pliku</label>
							<?= basename($path); ?>
						</li>
						<li>
							<?= Form::textarea('content', $content, array('id' => 'template_content', 'style' => 'width: 97%; height: 600px')); ?>
						</li>
					</ol>
				</fieldset>

				<fieldset>
					<ol>
						<?php foreach ($pageTitle as $tpl => $title) : ?>
						<?php if (strcasecmp($tpl, $tplName) != 0) : ?>
						<li>
							<label title="Tytuł dla tego szablonu jest dziedziczony po szablonie <?= $tpl; ?>">Dziedziczony po: <?= $tpl; ?></label>
							<?= Form::input("{$tpl}_title", $title, array('disabled' => 'disabled')); ?>
						</li>
						<?php endif; ?>
						<?php endforeach; ?>
						<li>
							<hr />
						</li>
						<li>
							<label title="Unikalny tytuł dla tego szablonu. Jeżeli szablon zostanie użyty, tytuł ten zostanie wyświetlony w nagłówku title">Tytuł strony</label>
							<?= Form::input('title', $input->post('title', isset($pageTitle[$tplName]) ? $pageTitle[$tplName] : '')); ?>
						</li>
					</ol>

				</fieldset>

				<fieldset>
					<ol>
						<?php foreach ($pageLayout as $tpl => $layout) : ?>
						<?php if (strcasecmp($tpl, $tplName) != 0) : ?>
						<li>
							<label title="Ten szablon dziedziczy opcję layout po szablonie <?= $tpl; ?>">Dziedziczony po: <?= $tpl; ?></label>
							<?= Form::input("{$tpl}_layout", $layout, array('disabled' => 'disabled')); ?>
						</li>
						<?php endif; ?>
						<?php endforeach; ?>
						<li>
							<hr />
						</li>
						<li>
							<label title="Nazwa szablonu, który będzie stanowił layout dla tego pliku. Więcej informacji w dokumentacji projektu">Layout</label>
							<?= Form::input('layout', $input->post('layout', isset($pageLayout[$tplName]) ? $pageLayout[$tplName] : '')); ?>
						</li>
						<li>
							<label title="Zaznacz tę opcję jeżeli nie chcesz, aby ten szablon dziedziczył layout po szablonach nadrzędnych">&nbsp;</label>
							<?= Form::checkbox('disableLayout', 1, @$pageLayout[$tplName] === false ? true : false); ?> Brak layoutu
						</li>
					</ol>

				</fieldset>

				<fieldset>
					<ol>
						<?php foreach ($pageStylesheet as $tpl => $stylesheets) : ?>
						<?php if (strcasecmp($tpl, $tplName) != 0) : ?>
						<?php foreach ($stylesheets as $stylesheet) : ?>
						<li>
							<label title="Pliki CSS włączone do tego szablonu są dziedziczone po szablonie <?= $tpl; ?>">Dziedziczony po: <?= $tpl; ?></label>
							<?= Form::input("{$tpl}_stylesheet", $stylesheet, array('disabled' => 'disabled')); ?>
						</li>
						<?php endforeach; ?>
						<?php endif; ?>
						<?php endforeach; ?>
						<li>
							<hr />
						</li>
						<li>
							<label title="Możesz ustawić kilka plików CSS oddzielając je znakami przecinka. Przy pomocy operatora '-' możesz także usunąc pliki CSS dziedziczone. Więcej informacji znajdziesz w dokumentacji projektu">CSS</label>
							<?= Form::input('stylesheet', $input->post('stylesheet', isset($pageStylesheet[$tplName]) ? implode(',', $pageStylesheet[$tplName]) : '')); ?>
						</li>
					</ol>

				</fieldset>

				<fieldset>
					<ol>
						<?php foreach ($pageJavascript as $tpl => $javascripts) : ?>
						<?php if (strcasecmp($tpl, $tplName) != 0) : ?>
						<?php foreach ($javascripts as $javascript) : ?>
						<li>
							<label title="Pliki JavaScript dołączone do tego szablonu są dziedziczone po innym szablonie nadrzędnym: <?= $tpl; ?>">Dziedziczony po: <?= $tpl; ?></label>
							<?= Form::input("{$tpl}_stylesheet", $javascript, array('disabled' => 'disabled')); ?>
						</li>
						<?php endforeach; ?>
						<?php endif; ?>
						<?php endforeach; ?>
						<li>
							<hr />
						</li>
						<li>
							<label title="Możesz ustawić kilka plików JavaScript oddzielając je znakiem przecinka. Możesz również usunąć pliki z szablonów nadrzędnych korzystając z operatora '-'. Więcej informacji na ten temat znajdziesz w dokumentacji">JavaScript</label>
							<?= Form::input('javascript', $input->post('javascript', isset($pageJavascript[$tplName]) ? implode(',', $pageJavascript[$tplName]) : '')); ?>
						</li>						
					</ol>
				</fieldset>
			</div>

		<?= Form::close(); ?>
	</div>
</div>