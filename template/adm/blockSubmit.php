<script type="text/javascript">
//<![CDATA[

	function pluginSelect(pluginId)
	{
		$.get('<?= url('adm/Block/__item'); ?>', {id: pluginId},
			function(data)
			{
				$('#plugin-item option').remove();
				$('#plugin-item select').append(new Option('--', 0));
				var items = 0;

				for (var index in data)
				{
					$('#plugin-item select').append(new Option(data[index], index));
					++items;
				}

				if (items > 0)
				{
					$('#plugin-item').show();

					<?php if (@$block_item) : ?>
					$('#plugin-item option[value="' + <?= $block_item; ?> + '"]').attr('selected', 'selected');
					<?php endif; ?>
				}
				else
				{
					$('#plugin-item').hide();
				}

			},
			'json'
		);
	}

	$(document).ready(function()
	{
		$("select[name=plugin]").bind('change', function()
		{
			pluginSelect($(this).val());
		});

		<?php if (@$block_plugin) : ?>
		pluginSelect(<?= $block_plugin; ?>);
		<?php endif; ?>

		$('.page-menu li:eq(0) a').addClass('focus');
		$('.menu-block fieldset.mf:gt(0)').hide();

		$('.page-menu li a').bind('click', function()
		{
			var index = $('.page-menu li a').index(this);

			$('.page-menu li a').removeClass('focus');
			$(this).addClass('focus');

			$('.menu-block fieldset.mf').hide();
			$('.menu-block fieldset.mf:eq(' + index + ')').show();
		});
	});

	editAreaLoader.init({
		id : 'ta-style',
		syntax: 'css',
		allow_toggle: true,
		start_highlight: true,
		language: 'pl',
		min_height: 400,
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php,c,cpp,java,pas,perl,python,robotstxt,ruby,sql,vb,xml'
	});

	editAreaLoader.init({
		id : 'ta-footer',
		syntax: 'css',
		allow_toggle: true,
		start_highlight: true,
		language: 'pl',
		min_height: 400,
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php,c,cpp,java,pas,perl,python,robotstxt,ruby,sql,vb,xml'
	});

	editAreaLoader.init({
		id : 'ta-header',
		syntax: 'css',
		allow_toggle: true,
		start_highlight: true,
		language: 'pl',
		min_height: 400,
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php,c,cpp,java,pas,perl,python,robotstxt,ruby,sql,vb,xml'
	});

//]>
</script>

<h1>Konfiguracja bloku</h1>

<p>Zarówno nagłówek jak i stopka mogą zawierać kod PHP. Musi on jednak zostać zawarty pomiędzy znaczniki &lt;?php oraz ?&gt;.</p>

<div class="page-menu">
	<ul>
		<li><a>Konfiguracja bloku</a></li>
		<li><a>Nagłówek</a></li>
		<li><a>Stopka</a></li>
		<li><a>Style CSS</a></li>
	</ul>
</div>

<?= Form::open('adm/Block/Submit/' . @$block_id, array('method' => 'post')); ?>
	<div class="menu-block">
		<fieldset class="mf">
			<legend>Konfiguracja bloku</legend>

			<ol>
				<li>
					<label>Nazwa bloku</label>
					<?= Form::input('name', $input->post('name', @$block_name)); ?>
					<ul><?= $filter->formatMessages('name'); ?></ul>
				</li>
				<li>
					<label title="Region, w którym zostanie wyświetlony blok">Region</label>
					<?= Form::select('region', Form::option($regions, $input->post('region', @$block_region))); ?>
					<ul><?= $filter->formatMessages('region'); ?></ul>
				</li>
				<li>
					<label title="Wtyczka, która zostanie przypisany do danego bloku">Wtyczka</label>
					<?= Form::select('plugin', Form::option($plugins, $input->post('plugin', @$block_plugin))); ?>
					<ul><?= $filter->formatMessages('plugin'); ?></ul>
				</li>
				<li id="plugin-item" style="display: none;">
					<label>Element wtyczki</label>
					<?= Form::select('item', Form::option(array(0 => ''), 0)); ?>
				</li>
				<li>
					<label title="Trigger, który zostanie wywołany wraz z wyświetleniem bloku (tylko w wersji NIEcachowanej!)">Trigger</label>
					<?= Form::select('trigger', Form::option($triggers, $input->post('trigger', @$block_trigger))); ?><ul><?= $filter->formatMessages('trigger'); ?></ul>
				</li>
				<li>
					<label title="Opcja cachowania danego bloku">Cachowanie</label>
					<?= Form::select('cache', Form::option($caches, @$block_cache)); ?>
				</li>
				<li>
					<label title="Domyślnie, blok jest wyświetlany na każdej stronie">Opcje wyświetlania</label>

					<fieldset>
						<ol>
							<li>
								<?= Form::radio('scope', Block::SHOW, @$block_scope == Block::SHOW); ?> Wyświetlaj na wszystkich stronach poza wymienionymi
							</li>
							<li>
								<?= Form::radio('scope', Block::HIDE, @$block_scope == Block::HIDE); ?> Wyświetlaj tylko na wymienionych stronach
							</li>
							<li>
								<?= Form::textarea('pages', $input->post('pages', @$block_pages), array('cols' => 60, 'rows' => 10)); ?>
							</li>
							<li>
								<small style="margin-left: 0">Adresy mogą zawierać znak *, który oznacza jakąkolwiek wartość. Można również uzywać nazw metod routingu - np. <i>@foo</i></small>
							</li>
						</ol>
					</fieldset>
				</li>
				<li>
					<label title="Brak zaznaczenia jakiejkolwiek grupy spowoduje, iż blok nie będzie wyświetlany!">Wyświetlaj dla grup</label>
					<fieldset>
						<ol>
							<?php foreach ($groups as $groupId => $name) : ?>
							<li><?= Form::checkbox('groups[]', $groupId, in_array($groupId, $blockGroups)); ?> <?= $name; ?></li>
							<?php endforeach; ?>

							<li>
								<small style="margin-left: 0">Jeżeli żadna grupa nie zostanie zaznaczona, blok nie będzie wyświetlany.</small>
							</li>
						</ol>
					</fieldset>
				</li>
				<li>
					<label title="Uprawnienie, które musi posiadać użytkownik, aby zobaczyć blok">Uprawnienie</label>
					<?= Form::select('auth', Form::option($auth, $input->post('auth', @$block_auth))); ?>
				</li>
				<li>
					<label></label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset class="mf">
			<ol>
				<li>
					<label title="Kod HTML, który zostanie wyświetlony przed właściwą treścią bloku." style="padding-bottom: 10px">Nagłówek</label>
					<?= Form::textarea('header', $input->post('header', @$block_header), array('id' => 'ta-header', 'rows' => 30, 'cols' => 100, 'style' => 'width: 98%')); ?>
					<ul><?= $filter->formatMessages('header'); ?></ul>
				</li>

				<li>
					<label></label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset class="mf">
			<ol>
				<li>
					<label title="Dodatkowy kod HTML, który zostanie wyświetlony po właściwej zawartości bloku" style="padding-bottom: 10px">Stopka</label>
					<?= Form::textarea('footer', $input->post('footer', @$block_footer), array('rows' => 30, 'cols' => 100, 'id' => 'ta-footer', 'style' => 'width: 98%')); ?>
					<ul><?= $filter->formatMessages('footer'); ?></ul>
				</li>

				<li>
					<label></label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset class="mf">
			<ol>
				<li>
					<label title="Zaawansowane. Możesz edytować style CSS, które będą wyświetlane razem z tym blokiem" style="padding-bottom: 10px">Kod CSS</label>
					<?= Form::textarea('style', $input->post('style', @$block_style), array('id' => 'ta-style', 'cols' => 100, 'rows' => 30, 'style' => 'width: 98%; height: 500px')); ?>
				</li>

				<li>
					<label></label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>
	</div>
</form>
