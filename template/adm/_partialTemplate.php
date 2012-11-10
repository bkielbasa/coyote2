<script type="text/javascript">
<!--
	var baseUrl = '<?= Url::site(); ?>';
//-->
</script>

<div id="template-tree">
	<ol>
		<li><a title="Zwiń wszystkie gałęzie" id="template-toggle"></a></li>

		<li style="float: right;"><a title="Schowaj panel" onclick="toggleSide();" id="page-hide"></a></li>
	</ol>

	<h2><?= Config::getItem('site.title'); ?></h2>	
	
</div>
