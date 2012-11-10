<h1>Szczegóły dotyczące raportu</h1>

<?php if ($report_close) : ?>
<p class="note">Dany raport został zamknięty przez użytkownika <?= $report_user; ?> dnia <?= User::formatDate($report_time); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Ścieżka do strony</label>
				<?= Html::a(url($location_text), $location_text); ?>
			</li>
			<li>
				<label>Data utworzenia</label>
				<?= User::formatDate($report_time); ?>
			</li>
			<li>
				<label>Użytkownik</label>
				<?= $user_id > User::ANONYMOUS ? Html::a(url('adm/User/Submit/' . $user_id), $user_name) : $user_name; ?>
			</li>
			<li>
				<label>IP</label>
				<?= Html::a(url('adm/Ban/Submit?ip=' . $report_ip . '&id=' . $user_id), $report_ip); ?>
			</li>
			<li>
				<span style="font-size: 12px;">
					<?= nl2br(str_replace('  ', '&nbsp;', $report_message)); ?>
				</span>
			</li>
			<?php if (!$report_close) : ?>
			<li>
				<label title="Opcjonalnie. Wiadomość zostanie przesłana na adres e-mail">Wiadomość dla użytkownika</label>
				<?= Form::textarea('content', '', array('cols' => 90, 'rows' => 10)); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zamknij raport'); ?>
			</li>
			<?php endif; ?>

		</ol>
	</fieldset>
<?= Form::close(); ?>