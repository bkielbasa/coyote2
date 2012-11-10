<script type="text/javascript">
	var checked = false;

	$(document).ready(function()
	{
		$('#selectAll').bind('click', function()
		{
			$('input:checkbox').attr('checked', !checked);
			checked = !checked;
		}
		);
	});
</script>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<h1>Uprawnienia dostępu</h1>

<p>Na tej zakładce możesz regulować uprawnienia grup dla różnych kategorii forum dyskusyjnych.
Wybierz z poniższej listy, nazwę grupy oraz nazwę kategorii, aby ustalić uprawnienia.</p>

<?= Form::open('', array('method' => 'get')); ?>
	<fieldset>
		<legend>Ustawienia dostępu dla forum</legend>

		<ol>
			<li>
				<label>Forum</label> 
				<?= Form::select('forumId', Form::option($forumList, $input->get->forumId)); ?>
			</li>
			<li>
				<label>Grupa</label>
				<?= Form::select('groupId', Form::option($groupList, $input->get->groupId)); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Ustaw prawa dostępu'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<?php if ($input->get->forumId && $input->get->groupId) : ?>
<?= Form::open('', array('method' => 'post')); ?>
	<table>	
		<caption>Uprawnienia dostępu</caption>

		<thead>
			<tr>
				<th>Opcja</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($optionList as $optionId => $row) : ?>
			<tr>
				<td><?= $row['label']; ?></td>
				<td class="checkbox"><?= Form::checkbox("permission[$optionId]", 1, (bool) $row['value']); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td>

				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>	
				</td>
			</tr>
		</tfoot>
	</table>

	<?= Form::submit('', 'Zapisz ustawienia'); ?>	
<?= Form::close(); ?>
<?php endif; ?>