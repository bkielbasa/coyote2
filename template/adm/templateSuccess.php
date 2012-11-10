<div id="template" style="overflow: hidden;">
	<?php include('_partialTemplate.php'); ?>

	<div id="template-content">
		<?php if ($isSuccess) : ?>
		<p class="message">Zmiany zostały zapisane.</p>
		<?php else : ?>
		<p class="error">Zmiany nie mogły zostać zapisane. Prawdopodobnie nie posiadasz praw do zapisu do pliku konfiguracji.</p>
		<?php endif; ?>

		<?php if (!$isBackup) : ?>
		<p class="note">UWAGA! System próbował utworzyć kopię zapasową dotychczasowej konfiguracji. Nie udało się jednak utworzyć kopii pliku <i>config.php</i></p>
		<?php endif; ?>

	</div>
</div>