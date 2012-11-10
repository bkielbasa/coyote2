<script type="text/javascript">

$(document).ready(function()
{
	$('textarea[name=text]').wikiEditor(buttonSet);

	$('textarea[name=text]').keydown(onSubmitShortcut);
	$('form').submit(function()
	{
		$(':submit', this).attr('disabled', 'disabled');
	});

	$('input[name=to]').autocomplete({url: '<?= url('@user?controller=Pm&action=__username'); ?>', autoSubmit: false});
});

</script>

<?php $selected = 'pm-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box" style="margin-top: 40px">
	<ul id="auto-complete" style="display: none;"></ul>

	<div class="box box-pm" style="margin-top: 40px;">
		<?php include('_partialPmMenu.php'); ?>

		<?= Form::open('', array('method' => 'post')); ?>
			<fieldset>
				<ol>
					<li>
						<label>Odbiorca <em>*</em></label>
						<?= Form::input('to', $input->post('to', $to), array('autocomplete' => 'off')); ?>
						<ul><?= $filter->formatMessages('to'); ?></ul>
					</li>
					<li>
						<?= Form::textarea('text', $input->post('text', $text), array('cols' => 90, 'rows' => 15, 'style' => 'width: 98%', 'tabindex' => 1)); ?>
						<ul><?= $filter->formatMessages('text'); ?></ul>
					</li>
					<li>
						<?= Form::submit('', 'Wyślij'); ?>
					</li>
				</ol>
			</fieldset>
		<?= Form::close(); ?>
	</div>
</div>

<div style="clear: both;"></div>