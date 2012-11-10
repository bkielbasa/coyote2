<h1>Usuwanie wtyczki</h1>

<p class="note"><b>UWAGA!</b> Zamierzasz usunąć wtyczkę z systemu. Może to spowodować utratę pewnych danych!</p>

<?php if (@$tablesExists) : ?>
<p class="note"><b>UWAGA!</b> System wykrył, iż ten wtyczka przechowuje w bazie dane. Jeżeli ją odinstalujesz, dane zostaną usunięte!</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Usuwanie wtyczki</legend>

		<ol>
			<?php if (@$tablesExists) : ?>
			<li>
				<label>Wykryto bazę danych wtyczki</label>

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
				<?= Form::submit('', 'Usuń wtyczkę', array('class' => 'delete-plugin-button')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>