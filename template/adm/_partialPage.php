<script type="text/javascript">
<!--
	var pageId = <?= intval(@$id); ?>;
	var baseUrl = '<?= Url::site(); ?>';
	var connector = new Array();

	<?php foreach ($connectorList as $_module => $array) : ?>
	connector['<?= $_module; ?>'] = {
		<?php foreach ($array as $id => $value) : ?>
		'<?= $id; ?>': '<?= $value; ?>',
		<?php endforeach; ?>
	};
	<?php endforeach; ?>

//-->
</script>

<div id="page-tree">
	<ol>
		<li><a title="Zwiń wszystkie gałęzie" id="page-toggle"></a></li>
		<li><a title="Odśwież listę" onclick="pageList(0);" id="page-refresh"></a></li>
		<?php if (!$deleted) : ?>
		<li><a title="Brak dokumentów w koszu" id="page-bin-empty"></a></li>
		<?php else : ?>
		<li><a title="W koszu znajdują się <?= $deleted; ?> dokument(y)." onclick="purge(this);" id="page-bin"></a></li>
		<?php endif; ?>
		<li><a title="Utwórz nową stronę" onclick="$('.page-submit').toggle();" id="page-add"></a></li>
		<li><a title="Szukaj strony" onclick="$('.page-search').toggle();" id="page-find"></a></li>

		<li style="float: right;"><a title="Schowaj panel" onclick="toggleSide();" id="page-hide"></a></li>
	</ol>
	<div id="page-submit" class="page-submit" style="display: none">
		<?= Form::open(url('adm/Page/Submit'), array('method' => 'get')); ?>
			<fieldset>
				<ol>
					<li>
						<label>Moduł:</label>
						<?= Form::select('moduleId', Form::option($modules, 0)); ?>
					</li>
					<li class="connector-row">
						<label>Konektor:</label>
						<?= Form::select('connectorId', Form::option(array(''), 0), array('id' => 'connectorId')); ?>
					</li>	
					<li class="connector-row">
						<?= Form::submit('', 'Utwórz stronę', array('id' => 'add-button')); ?>
					</li>
				</ol>
			</fieldset>
		<?= Form::close(); ?>
	</div>
	<div id="page-submit" class="page-search" style="display: none">
		<fieldset>
			<ol>
				<li>
					<label>Tytuł strony:</label>
					<?= Form::input('subject', ''); ?>
				</li>
				<li>
					<?= Form::button('', 'Szukaj', array('onclick' => 'find();', 'id' => 'add-button')); ?>
				</li>
			</ol>
		</fieldset>
	</div>

	<h2><?= Config::getItem('site.title'); ?></h2>

	
</div>
