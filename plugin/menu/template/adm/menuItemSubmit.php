<script type="text/javascript">

	function addParam()
	{
		$('<li><?= Form::input('attributes[key][]', ''); ?> = <?= Form::input('attributes[value][]', '', array('size' => 70)); ?></li>').appendTo('#attributes ol');
	}
</script>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja pozycji menu</legend>

		<ol>
			<li>
				<label>Nazwa menu</label> 
				<?= Html::a(url('adm/Menu/Submit/' . $menu_id), $menu_name); ?>
			</li>
			<li>
				<label>Pozycja macierzysta</label>
				<?= Form::select('parent', Form::option($parent, @$item_parent), @$item_id ? array('disabled' => 'disabled') : array()); ?>
			</li>
			<li>
				<label>Nazwa pozycji</label>
				<?= Form::textarea('name', $input->post('name', @$item_name), array('cols' => 90, 'rows' => 5)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Znacznik HTML odpowiadający za tę pozycję. Domyślnie < li >">Znacznik HTML</label> 
				<?= Form::input('tag', $input->post('tag', @$item_id ? $item_tag : 'li'), array('size' => 5)); ?>
				<ul><?= $filter->formatMessages('tag'); ?></ul>
			</li>
			<li>
				<label title="Krótki opis odnośnika. Zostanie wstawiony w atrybut 'title' odnośnika">Opis</label>
				<?= Form::textarea('description', $input->post('description', @$item_description), array('cols' => 70, 'rows' => 10)); ?> 
				<ul><?= $filter->formatMessages('description'); ?></ul>
			</li>
			<li>
				<label title="Ścieżka może być linkiem wewnętrznym lub zewnętrznym">Ścieżka</label> 
				<?= Form::input('path', $input->post('path', @$item_path), array('size' => 50)); ?> 
				<ul><?= $filter->formatMessages('path'); ?></ul>
			</li>		
			<li>
				<label title="Dodatkowe parametry takie jak nazwa klasy czy inne atrybuty HTML">Dodatkowe parametry</label>

				<fieldset id="attributes">
					<ol>
						<?php foreach ($attributes as $key => $value) : ?>
						<li>
							<?= Form::input('attributes[key][]', $key); ?> = <?= Form::input('attributes[value][]', $value, array('size' => 70)); ?>
						</li>
						<?php endforeach; ?>
					</ol>
				</fieldset>
			</li>
			<li>
				<label>&nbsp;</label> 
				<?= Form::button('', 'Dodaj nowy parametr', array('style' => 'font-size: 0.8em;', 'onclick' => 'addParam()')); ?>
			</li>		
			<li>
				<label title="Pozycja nieaktywna nie będzie wyświetlana na liście">Pozycja aktywna</label>
				<?= Form::checkbox('enable', 1, (bool) $input->post('enable', isset($item_enable) ? $item_enable : true)); ?>
			</li>
			<li>
				<label title="Jeżeli dana pozycja jest aktywna (gdyż np. użytkownik wszedł na stronę do której prowadzi link), możesz określić klasę CSS, która będzie przypisana w atrybucie class">Klasa aktywnej pozycji</label>
				<?= Form::text('focus', $input->post('focus', @$item_focus)); ?>
				<ul><?= $filter->formatMessages('focus'); ?></ul>
			</li>
			<li>
				<label title="Pozycja będzie wyświetlana jedynie zaznaczonym grupom">Wyświetlaj dla grup</label>
				<fieldset>
					<ol>
						<?php foreach ($groups as $groupId => $name) : ?>
						<li>
							<?= Form::checkbox('groups[]', $groupId, in_array($groupId, $itemGroups)); ?> <?= $name; ?>
						</li>
						<?php endforeach; ?>
					</ol>
					<small style="margin: 0;">Jeżeli nie zostanie zaznaczona żadna grupa, pozycja nie będzie widoczna.</small>
				</fieldset>
			</li>
			<li>
				<label>Uprawnienie</label>
				<?= Form::select('auth', Form::option($auth, $input->post('auth', @$item_auth))); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
</form>