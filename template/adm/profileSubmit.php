<script type="text/javascript">
<!--
	$(document).ready(function()
	{
		$('select[name=component]').bind('change', function()
		{
			window.location.href = '<?= url("adm/Profile/Submit"); ?>?componentId=' + $(this).val();
		}
		);

		<?php if (@$field_id) : ?>
		$('select[name=component]').attr('disabled', 'disabled');
		<?php endif; ?>
	});
//-->
</script>

<h1>Edycja pola formularza</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Informacje o polu</legend>

		<ol>
			<li>
				<label>Komponent <em>*</em></label>
				<?= Form::select('component', Form::option($component, $componentId)); ?>
			</li>
			<li>
				<label label="Unikalna nazwa pola">Nazwa pola <em>*</em></label>
				<?= Form::input('name', $input->post('name', @$field_name)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Opis pola - np. Imię i nazwisko">Etykieta pola <em>*</em></label>
				<?= Form::input('text', $input->post('text', @$field_text)); ?>
				<ul><?= $filter->formatMessages('text'); ?></ul>
			</li>
			<li>
				<label title="Możesz ustawić tekst informacyjny dla komponentu - będzie on wyświetlany w atrybucie title">Informacja o polu (podpowiedź)</label>
				<?= Form::textarea('description', $input->post('description', @$field_description), array('cols' => 60, 'rows' => 5)); ?>
				<ul><?= $filter->formatMessages('description'); ?></ul>
			</li>
			<li>
				<label title="Zaznaczenie tej opcji spowoduje, iż nie będą przyjmowane wartości puste">Pole wymagane</label>
				<?= Form::checkbox('required', 1, (bool)$input->post('required', isset($field_required) ? $field_required : false)); ?>
			<li>
				<label title="Pole może być wyświetlane lub egzystować jedynie w bazie danych">Wyświetlaj pole</label>
				<?= Form::checkbox('display', 1, (bool)$input->post('display', isset($field_display) ? $field_display : true)); ?>
			</li>
			<li>
				<label title="Wyświetlaj wartość pola w profilu użytkownika">Wyświetlaj w profilu</label>
				<?= Form::checkbox('profile', 1, (bool) $input->post('profile', isset($field_profile) ? $field_profile : false)); ?>
			</li>
			<li>
				<label title="Pole może istnieć tylko do odczytu (np. jeżeli jedynie skrypt ma dokonywać modyfikacji pola">Tylko do odczytu</label>
				<?= Form::checkbox('readonly', 1, (bool)$input->post('readonly', isset($field_readonly) ? $field_readonly :  false)); ?>
			</li>
			<?= $componentLayout; ?>

			<li>
				<label>Walidator</label>
				<?= Form::select('validator', Form::option($validator, @$field_validator)); ?>
			</li>
			<li>
				<label>Filtry</label>

				<fieldset>
					<ol>
						<?php foreach ($filters as $filterId => $filterName) : ?>
						<li><?= Form::checkbox('filter[]', $filterId, (bool)in_array($filterId, $fieldFilters)); ?> <?= $filterName; ?></li>
						<?php endforeach; ?>
					</ol>
				</fieldset>

			</li>
			<li>
				<label>Uprawnienie</label>
				<?= Form::select('auth', Form::option($auth, @$field_auth)); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>