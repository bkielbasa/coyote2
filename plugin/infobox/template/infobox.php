<style type="text/css">@import url('<?= url('plugin/infobox/template/css/infobox.css'); ?>');</style>
<script type="text/javascript">
	var cookiePrefix = '<?= Config::getItem('cookie.prefix'); ?>';
	var cookieDomain = '<?= Config::getItem('cookie.host'); ?>';
</script>

<script src="<?= url('plugin/infobox/template/js/infobox.js'); ?>" type="text/javascript"></script>

<div id="infobox-layer"></div>
<div id="infobox-wrapper" tabindex="0" data-infobox-id="<?= $infobox_id; ?>" data-infobox-lifetime="<?= $infobox_lifetime; ?>">
	<h1><?= $infobox_title; ?></h1>
	<a class="infobox-close" title="Kliknij, aby zamknąć"></a>

	<div class="infobox-content"><?= $infobox_content; ?></div>
	<div class="infobox-bottom">

		<button tabindex="1">Ok, nie pokazuj więcej tego komunikatu</button>
	</div>

</div>