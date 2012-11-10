<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Usuń cache</legend>

		<ol>
			<li>
				<label>&nbsp;</label>
				Naciśnij ten przycisk, aby wyczyścić dane z cache!
			</li>
			<li>
				<label>Usuń:</label>
				
				<fieldset>
				
					<ol>
						<li><?= Form::checkbox('cache[permission]', 1, true); ?> Cache uprawnień</li>
						<li><?= Form::checkbox('cache[text]', 1, true); ?> Cache tekstów</li>
						<li><?= Form::checkbox('cache[general]', 1, true); ?> Cache generalny</li>	
					</ol>
				</fieldset>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('delete', 'Czyść dane z cache'); ?>
			</li>
		</ol>
	</fieldset>
</form>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Adapter cache</legend>

		<ol>
			<li>
				<label>&nbsp;</label>
				<?= Form::select('adapter', Form::option($adapter, Config::getItem('cache.adapter'))); ?>
			
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz'); ?>
			</li>
		</ol>

	</fieldset>
</form>