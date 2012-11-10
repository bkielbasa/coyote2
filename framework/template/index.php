
<script type="text/javascript">
	function goDelete()
	{
		if (confirm("Czy na pewno chcesz usunąć?"))
		{			
			<?= 'Index/Uninstall'; ?>			
		}
		return false;
	}

	function goExport()
	{
		if (confirm('Czy chcesz eksportować pliki demo?'))
		{
			<?= 'Index/Export'; ?>
		}
		return false;
	}
</script>

<h3>Gratulacje!</h3>

<p>Framework poprawnie wygenerował stronę startową dla tego projektu. Zachęcamy do 
zapoznania się z możliwościami systemu oraz jego obsługą. </p>

<hr />

<p>Przede wszystkim możesz usunąć tę stronę w każdej chwili. Jest ona tylko demonstracją 
możliwości frameworka. Jeżeli nie chcesz widzieć tej strony nigdy więcej po prostu usuń następujące 
pliki:</p>
<ul style="margin: 15px;">
	<?php foreach ($project_files as $file) : ?>
	<li><?= $file; ?></li>
	<?php endforeach; ?>
</ul>
<?= Form::input('Delete', 'Usuń teraz', array('type' => 'button', 'onclick' => 'goDelete()')); ?>
<?= Form::input('Export', 'Eksportuj pliki', array('type' => 'button', 'onclick' => 'goExport()')); ?>

<h1>Ściągnij najnowszą wersję (SVN)</h1>

<ul>
	<li class="download"><a href="http://redmine.boduch.net/projects/coyote">Coyote <?= Core::version(); ?> (framework)</a></li>
</ul>


