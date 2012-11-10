<script type="text/javascript">
<!--

	var parentPageSubject = '<?= $parentPageSubject; ?>';
//-->
</script>
<div id="page" style="overflow: hidden;">
	<?php include('_partialPage.php'); ?>

	<div id="page-content">

		<div style="float: right;">
			<?= Form::button('', 'Edytuj', array('onclick' => 'window.location.href = \'' . url('adm/Page/Submit/' . $page_id) . '\'', 'class' => 'edit-button')); ?>
			<?php if (!$page_delete) : ?>
			<?= Form::button('', 'Usuń', array('id' => 'delete', 'class' => 'delete-button')); ?>
			<?php else : ?>
			<?= Form::button('', 'Przywróć', array('id' => 'restore', 'class' => 'restore-button')); ?>
			<?php endif; ?>
		</div>
		<br style="clear: both;" />

		<p class="note">UWAGA! Zamierasz przenieść wybraną gałąź. Może to sprawić, że linki prowadzące do tej strony zostaną utracone!</p>

		<?= Form::open('', array('method' => 'post')); ?>
			<fieldset>
				<ol>
					<li>
						<label title="Kliknij na ikonę obok, a następnie kliknij na wybraną stronę w panelu po lewej">Strona bazowa</label>
						<?= Form::input('page_parent', $input->post('page_parent')); ?>
					</li>

					<li>
						<label></label>
						<?= Form::submit('', 'Przenieś', array('class' => 'move-button')); ?>
					</li>
				</ol>
			</fieldset>
		<?= Form::close(); ?>

	</div>
</div>