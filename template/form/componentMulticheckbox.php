<script type="text/javascript">
<!--
	function deleteOption(element)
	{
		$(element).parent('li').remove();
		refreshOptions();
	}

	function refreshOptions()
	{
		$('#defaultList ol').html('');

		optionList = $('#optionList input');

		for (i = 0; i < optionList.length; i+=2)
		{
			$('#defaultList ol').append('<li><input type="checkbox" name="default[]" value="' + optionList[i].value + '" /> ' + optionList[i + 1].value + '</li>');
		}
	}

	function bind()
	{
		$('.item').bind('keyup', function()
		{
			refreshOptions();
		}
		);
	}

	$(document).ready(function()
	{
		$('input[name=insertField]').bind('click', function()
		{
			$('<li><input class="item" type="text" name="item[name][]" /> = <input class="item" type="text" name="item[value][]" /> <button type="button" class="delete-button" onclick="deleteItem(this);">Usuń opcję</button></li>').appendTo('#optionList');

			refreshOptions();
			bind();
		}
		);		

		bind();
	}
	);
//-->
</script>
<li>
	<label>Lista opcji</label>

	<fieldset id="optionList">
		<legend>Wartość opcji = Etykieta opcji</legend>

		<ol>
			<?php foreach ((array)@$items as $name => $value) : ?>
			<li><?= Form::input('item[name][]', $name, array('class' => 'item')); ?> = <?= Form::input('item[value][]', $value, array('class' => 'item')); ?> <button type="button" class="delete-button" onclick="deleteItem(this);">Usuń opcję</button></li>
			<?php endforeach; ?>			
		</ol>
	</fieldset>
</li>
<li>
	<label>Domyślna opcja</label>

	<fieldset id="defaultList">
		<ol>
			<?php foreach ((array)@$items as $name => $value) : ?>
			<li><?= Form::checkbox('default[]', $name, in_array($name, $default)); ?> <?= $value; ?></li>
			<?php endforeach; ?>
		</ol>
	</fieldset>
</li>
<li>
	<label>&nbsp;</label>
	<?= Form::button('insertField', 'Dodaj nowe pole', array('class' => 'add-button')); ?>
</li>