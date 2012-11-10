<h1>Podgląd pliku <?= $path; ?></h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Nazwa pliku:</label>
				<?= Form::input('name', $input->post('name', basename($path)), array('size' => 50)); ?>
			</li>
			<li>
				<img src="<?= Url::site(); ?><?= $path; ?>" />
			</li>
			<li>
				<?= Form::submit('', 'Zmień nazwę pliku'); ?>
				<?= Form::button('', 'Anuluj', array('class' => 'cancel-button', 'onclick' => 'history.go(-1)')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>