<p class="error"><b>UWAGA!</b> Zamierasz usunąć forum wraz zawartością!</p>

<?= Form::open('', array('method' => 'post', 'onsubmit' => 'return confirm(\'Czy na pewno chcesz dokonać operacji?\');')); ?>
	<fieldset>
		<legend>Usuwanie forum</legend>
		
		<ol>
			<?php if ($forum_type == Forum_Model::NORMAL) : ?>
			<li>
				<b>Zawartość</b>

				<fieldset>
					<ol>
						<li>
							<label><?= Form::radio('result', 'del', false); ?> Usuń</label>
						</li>
						<li>
							<label><?= Form::radio('result', 'mov', true); ?> Przenieś do: </label> 
							<?= Form::select('forum', Form::option($forum, 0, true, $forumAttributes)); ?>
						</li>
					</ol>
				</fieldset>
			</li>
			<?php endif; ?>
			<li>
				<label>&nbsp;</label> 
				<?= Form::submit('', 'Usuń forum'); ?>
			</li>
		</ol>
	</fieldset>
</form>