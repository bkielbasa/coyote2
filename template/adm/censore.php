<script type="text/javascript">
<!--

	var checked = false;

	$(document).ready(function()
	{
		$('select').change(function()
		{
			if ($(this).val() == 2)
			{
				$('tbody td[colspan]').remove();
				$('<tr><td><?= Form::text('text[]', ''); ?></td><td><?= Form::text('replacement[]', ''); ?></td><td class="checkbox"><?= Form::checkbox('', 1, false); ?></td></tr>').appendTo('tbody');
			}
			else if ($(this).val() == 3)
			{
				$('#censore').trigger('submit');
			}
			else if ($(this).val() == 1)
			{
				$.each($('input:checked'), function(i, element)
				{
					$(element).parent().parent().remove();
					$('#censore').trigger('submit');
				});
			}

			$(this).val(0);
		});

		$('#selectAll').bind('click', function()
		{
			$('input:checkbox').attr('checked', !checked);
			checked = !checked;
		});
	});
//-->
</script>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<h1>Konfiguracja cenzury</h1>

<p>Opcja cenzury umożliwia parserowi <i>censore</i> usunięcie z tekstu wybranych słow
i zastąpienie go odpowiednikiem. Poniższa tabela pozwoli na konfiguracje cenzurownych fraz.
Wartości w kolumnie <b>Fraza do odszukania</b> mogą zawierać wyrażenia regularne.</p>

<?= Form::open('', array('id' => 'censore', 'method' => 'post')); ?>
	<table>
		<thead>
			<tr>
				<th>Fraza do odszukania</th>
				<th>Fraza do zastąpienia</th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php if ($censore) : ?>
			<?php foreach ($censore as $text => $replacement) : ?>
			<tr>
				<td><?= Form::text('text[]', htmlspecialchars($text)); ?></td>
				<td><?= Form::text('replacement[]', htmlspecialchars($replacement)); ?></td>
				<td class="checkbox"><?= Form::checkbox('', 1, false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="3" style="text-align: center;">Brak zdefiniowanych słów w bazie danych.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<select>
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nowe</option>
						<option value="3">Zapisz zmiany</option>
					</select>

				</td>
				<td class="checkbox"><a id="selectAll" title="Zaznacz wszystko"></a></td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>