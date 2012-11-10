<script type="text/javascript">
<!--
	$(document).ready(function()
	{
		$('.bar-solr a').click(function()
		{
			if ($('#search-option').css('display') == 'block')
			{
				$(this).text('Pokaż opcje');
			}
			else
			{
				$(this).text('Ukryj opcje');
			}

			$('#search-option').toggle('fast');
		});

		$('#q').autocomplete({url: '<?= url($page->getLocation() . '/__suggest'); ?>'}).focus();
	});
//-->
</script>

<ul id="auto-complete" style="display: none;">

</ul>

<?= $page->getContent(); ?>
<div id="box-solr">
	<?= Form::open('', array('name' => 'form')); ?>
		<fieldset>
			<ol>
				<li>
					<label>Szukane słowa</label>
					<?= Form::input('q', htmlspecialchars($solr->getQueryString()), array('id' => 'q', 'autocomplete' => 'off')); ?><input type="submit" value=""/>
				</li>
			</ol>
		</fieldset>
	<?= Form::close(); ?>
</div>

<?php if (isset($input->get->q)) : ?>
<div class="bar-solr">
	<a>Pokaż opcje</a>

	<?php if ($solr->getTotalRows()) : ?>
	<div>
		Wyniki <b><?= $pagination->getCurrentItem() + 1; ?> - <?= min($solr->getTotalRows(), $pagination->getCurrentItem() + $pagination->getItemsPerPage()); ?></b> z <b><?= number_format($pagination->getTotalItems(), 0, ',', ' '); ?></b> znalezione w <b><?= Text::formatBenchmark($solr->getTotalTime()); ?></b>.
	</div>
	<?php endif; ?>

</div>
<?php endif; ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p class="pager">Strony <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>

<?php if ($solr->getSuggestion()) : ?>
<p class="search-suggestion">
	Czy chodziło Ci o: <a href="<?= url($page->getLocation() . '?q=' . $solr->getSuggestion()); ?>"><?= $solr->getSuggestion(); ?></a>?
</p>
<?php endif; ?>

<div id="search-box">
	<div id="search-option" style="display: none">

		<?php foreach ($options as $optionKey => $rowset) : ?>
		<ul>
			<?php foreach ($rowset as $key => $value) : ?>
			<?php if ($key == $searchOptions[$optionKey]) : ?>
			<li><strong><?= $value; ?></strong></li>
			<?php else : ?>
			<li><a href="<?= $baseUrl; ?>&<?= $optionKey; ?>=<?= $key; ?>"><?= $value; ?></a></li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php endforeach; ?>

		<?php if ($facet = $solr->getFacets()) : ?>
		<ul>
			<?php if ($input->get->cat) : ?>
			<li><a href="<?= $baseUrl; ?>">Wszystkie kategorie</a></li>
			<?php else : ?>
			<li><strong>Wszystkie kategorie</strong></li>
			<?php endif; ?>

			<?php foreach ($facet['connector'] as $connectorId => $index) : ?>
			<?php if ($input->get->cat == $connectorId) : ?>
			<li><strong><?= $connectorList[$connectorId]; ?> (<?= $index; ?>)</strong></li>
			<?php elseif ($index > 0) : ?>
			<li><a href="<?= $baseUrl; ?>&cat=<?= $connectorId; ?>"><?= $connectorList[$connectorId]; ?> (<?= $index; ?>)</a></li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>

	</div>

	<div id="search-results">

		<ul>
			<?php if ($solr->getTotalRows()) : ?>
			<?php foreach ($hits as $hit) : ?>
			<li>
				<h2><?= Html::a($hit['url'], $hit['title'], $hit['description'] ? array('title' => $hit['description']) : array()); ?></h2>

				<p><?= $hit['body'] ? $hit['body'] : $hit['description']; ?></p>
				<?= Html::a($hit['url'], url($hit['location']) . '&nbsp;<small>[' . Time::span($hit['timestamp']) . ']</small>', array('class' => 'search-link')); ?>

			</li>
			<?php endforeach; ?>
			<?php endif; ?>
		</ul>

	</div>
</div>
<?php if (isset($input->get->q) && !$solr->getTotalRows()) : ?>

<div class="search-information">
	Podana fraza <b><?= htmlspecialchars($solr->getQueryString()); ?></b> nie została odnaleziona.

	<ul>
		<li>Sprawdź, czy słowa kluczowe zostały poprawnie napisane</li>
		<li>Spróbuj użyć mniejszej liczby słów kluczowych</li>
		<?php if ($searchOptions[Solr_Controller::SEARCH_DATE] != Solr_Controller::ALL) : ?>
		<li><a href="<?= $baseUrl; ?>&d=a">Szukaj wyników o dowolnej dacie indeksacji</a></li>
		<?php endif; ?>
	</ul>
</div>
<?php endif; ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p class="pager">Strony <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>

<br style="clear: both;" />