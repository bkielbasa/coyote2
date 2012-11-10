<h1>Konfiguracja administratora (5/5)</h1>

<p>Wprowadź dane administratora. Dzięki tym danym będziesz się mógł zalogować do panelu
administracyjnego.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Login <em>*</em></label>
				<?= Form::input('name', $input->post->name($session->name, 'Admin')); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label>Hasło <em>*</em></label>
				<?= Form::password('password', $input->post->password($session->password)); ?>
				<ul><?= $filter->formatMessages('password'); ?></ul>
			</li>
			<li>
				<label>Hasło (powtórnie) <em>*</em></label>
				<?= Form::password('password_c', $input->post->password_c($session->password_c)); ?>
				<ul><?= $filter->formatMessages('password_c'); ?></ul>
			</li>
			<li>
				<label>E-mail</label>
				<?= Form::input('email', $input->post->email($session->email)); ?>
				<ul><?= $filter->formatMessages('email'); ?></ul>
			</li>
		</ol>
	</fieldset>

	<?= Form::button('', 'Powrót', array('class' => 'prev-button', 'onclick' => 'window.location.href = \'' . Url::__('Db') . '\'')); ?>
	<?= Form::submit('', 'Zakończ', array('class' => 'next-button')); ?>
<?= Form::close(); ?>