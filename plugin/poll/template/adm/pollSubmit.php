<script type="text/javascript">
//<![CDATA[
	function addOption()
	{
		$('#option').append('<li><?= Form::input('option[]', ''); ?></li>');
	}

	$(document).ready(function()
	{
		$('input:checkbox').bind('change', function()
		{
			if ($(this).is(':checked'))
			{
				$(this).parent().remove();
			}
		}
		);
	}
	);
//]]>
</script>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja ankiety</legend>

		<ol>
			<li>
				<label>Tytuł ankiety</label> 
				<?= Form::input('title', $input->post('title', @$poll_title)); ?>
				<ul><?= $filter->formatMessages('title'); ?></ul>
			</li>
			<li>
				<label>Liczba możliwych odpowiedzi</label> 
				<?= Form::select('max_item', Form::option($maxItemsList, $input->post('max_item', @$poll_max_item))); ?> 
				<ul><?= $filter->formatMessages('max_item'); ?></ul>
			</li>
			<li>
				<label>Data rozpoczecia</label> 
				<?= Form::day('start_d', $start_d); ?> 
				<?= Form::month('start_m', $start_m); ?> 
				<?= Form::year('start_y', $start_y, date('Y'), date('Y') + 1); ?>  &nbsp; 
				<?= Form::hour('start_h', $start_h); ?> : 
				<?= Form::minute('start_i', $start_i); ?>
			</li>
			<li>
				<label>Długość działania:</label> 
				<?= Form::text('length', $input->post('length', @$poll_length / Time::DAY), array('style' => 'width: 20px')); ?> dni
				<ul><?= $filter->formatMessages('length'); ?></ul>
			</li>
			<li>
				<label>Ankieta aktywna</label> 
				<?= Form::select('enable', Form::option(array('Nie', 'Tak'), @$poll_enable)); ?>
				<ul><?= $filter->formatMessages('enable'); ?></ul>
			</li>
			<li>
				<label>Odpowiedzi</label>
				
				<?= Form::textarea('items', $input->post('items', implode("\n", $items)), array('cols' => 65, 'rows' => 10)); ?>
				<ul><?= $filter->formatMessages('item'); ?></ul>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zapisz ankietę'); ?>
			</li>
		</ol>
	</fieldset>
</form>