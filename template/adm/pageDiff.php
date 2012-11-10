<style type="text/css">
ins
{
		color:			green;
}

del
{
		font-weight:	bold;
		color:			red;
}
</style>

<div id="page" style="overflow: hidden;">
	<?php include('_partialPage.php'); ?>

	<div id="page-content">

		<div style="float: right;">
			<?= Form::button('', 'Przenieś', array('onclick' => 'window.location.href = \'' . url('adm/Page/Move/' . $page_id) . '\'', 'class' => 'move-button')); ?>
			<?= Form::button('', 'Kopiuj', array('onclick' => 'window.location.href = \'' . url('adm/Page/Copy/' . $page_id) . '\'', 'class' => 'copy-button')); ?>
			<?= Form::button('', 'Edytuj', array('onclick' => 'window.location.href = \'' . url('adm/Page/Submit/' . $page_id) . '\'', 'class' => 'edit-button')); ?>
			<?php if (!$page_delete) : ?>
			<?= Form::button('', 'Usuń', array('id' => 'delete', 'class' => 'delete-button')); ?>
			<?php else : ?>
			<?= Form::button('', 'Przywróć', array('id' => 'restore', 'class' => 'restore-button')); ?>
			<?php endif; ?>
		</div>
		<br style="clear: both;" />

		<h3>Porównanie wersji z dnia <?= User::formatDate($r1Time); ?> oraz <?= User::formatDate($r2Time); ?></h3>

		<pre>
<?= $diff; ?>
		</pre>


	</div>
</div>