
<?= Form::open('adm/ban/Submit/' . @$ban_id, array('method' => 'post')); ?>
	<fieldset>
		<legend>Blokowanie użytkownika</legend>

		<ol>
			<li>
				<label title="Jeżeli nie znasz nazwy użytkownika, pozostaw słowo Anonim">Nazwa użytkownika</label>
				<?= Form::input('user', $input->post->id(@$userId, @$u1_name, 'Anonim')); ?>
				<ul><?= $filter->formatMessages('user'); ?></ul>
			</li>
			<li>
				<label>E-mail</label>
				<?= Form::input('email', $input->post('email', @$ban_email)); ?>
				<ul><?= $filter->formatMessages('email'); ?></ul>
			</li>
			<li>
				<label>IP</label>
				<?= Form::input('ip', $input->post->ip(@$userIp, @$ban_ip)); ?>
				<ul><?= $filter->formatMessages('ip'); ?></ul>
			</li>
			<li>
				<label>Powód</label>
				<?= Form::textarea('reason', $input->post('reason', @$ban_reason), array('cols' => 40, 'rows' => 10)); ?> <ul><?= $filter->formatMessages('reason'); ?></ul>
				<ul><?= $filter->formatMessages('reason'); ?></ul>
			</li>
			<li>
				<label>Przedawnienie</label>

				<fieldset>
					<ol>
						<li>
							<label><?= Form::radio('timeout', 0, @(bool)!$ban_expire); ?></label>
							Nigdy
						</li>
						<li>
							<label><?= Form::radio('timeout', 1, @(bool)$ban_expire); ?></label>

							<?= Form::day('timeout_day', $expire['mday']); ?>
							<?= Form::month('timeout_month', $expire['mon']); ?>
							<?= Form::year('timeout_year', $expire['year'], date('Y'), date('Y') + 30); ?>
						</li>
					</ol>
				</fieldset>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany', array('class' => 'button')); ?>
			</li>
		</ol>
	</fieldset>
</form>