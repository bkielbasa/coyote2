<h1>Usuwanie modułu</h1>

<p class="note"><b>UWAGA!</b> Zamierzasz usunąć moduł z systemu. Może to spowodować utratę pewnych danych!</p>

<?php if (@$tablesExists) : ?>
<p class="note"><b>UWAGA!</b> System wykrył, iż ten moduł przechowuje w bazie dane. Jeżeli go odinstalujesz, dane zostaną usunięte!</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Usuwanie modułu</legend>

		<ol>
			<?php if (@$tablesExists) : ?>
			<li>
				<label>Wykryto bazę danych modułu</label>

				<fieldset>
					<ol>
						<li>
							<?= Form::radio('delete', 0, true); ?> Pozostaw dane w bazie 
						</li>
						<li>
							<?= Form::radio('delete', 1, false); ?> Usuń dane z bazy danych
						</li>
					</ol>
				</fieldset>
			</li>
			<?php endif; ?>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Usuń moduł', array('class' => 'delete-module-button')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>