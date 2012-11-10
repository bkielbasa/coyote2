<style type="text/css">
.close
{
		text-decoration:		line-through;
}
</style>

<div id="page" style="overflow: hidden;">
	<?php include('_partialPage.php'); ?>

	<div id="page-content">

		<div style="float: right;">
			<?= Form::button('', 'Przenieś', array('onclick' => 'window.location.href = \'' . url('adm/Page/Move/' . $page_id) . '\'', 'class' => 'move-button')); ?>
			<?= Form::button('', 'Kopiuj', array('onclick' => 'window.location.href = \'' . url('adm/Page/Copy/' . $page_id) . '\'', 'class' => 'copy-button')); ?>
			<?= Form::button('', 'Edytuj', array('onclick' => 'window.location.href = \'' . url('adm/Page/Submit/' . $page_id) . '\'', 'class' => 'edit-button')); ?>
			<?php if (!$page_delete) : ?>
			<?= Form::button('', 'Usuń', array('id' => 'delete', 'class' => 'delete-button')); ?>
			<?php else : ?>
			<?= Form::button('', 'Przywróć', array('id' => 'restore', 'class' => 'restore-button')); ?>
			<?php endif; ?>
			<?= Form::button('', 'Usuń permanentnie', array('id' => 'remove', 'class' => 'delete-button')); ?>
		</div>
		<br style="clear: both;" />

		<?php if ($hasOpenReport) : ?>
		<p class="note">Ta strona posiada otwarte raporty. Możesz je przeglądać na zakładce <i>Raporty</i></p>
		<?php endif; ?>

		<div class="page-menu">
			<ul>
				<li><a class="focus">Podsumowanie</a></li>
				<li><a>Historia i autorzy</a></li>
				<li><a>Raporty</a></li>
				<li><a>Obserwatorzy</a></li>
				
			</ul>
		</div>
		<div class="menu-block">
		
			<fieldset>
				<ol>
					<li>
						<label>Tytuł</label>
						<?= $page_subject; ?>
					</li>
					<li>
						<label>Długi tytuł</label>
						<?= def($page_title, '<i>Nieokreślono</i>'); ?>
					</li>
					<li>
						<label>Łącznik</label>
						<?= $connectorText; ?>
					</li>
					<li>
						<label>Ścieżka</label>
						<?= $path; ?>
					</li>
					<li>
						<label>Szablon</label>
						<?= def($page_template, '<i>Nieokreślono</i>'); ?>
					</li>
					<li>
						<label>Typ zawartości</label>
						<?= $contentType; ?>
					</li>
					<li>	
						<hr />
					</li>
					<li>
						<label>Data utworzenia</label>
						<?= User::formatDate($page_time); ?>
					</li>
					<li>
						<label>Data ostatniej zmiany</label>
						<?= User::formatDate($page_edit_time); ?>
					</li>
					<li>
						<hr />
					</li>
					<li>
						<label>Status</label>
						<?= $page_delete ? 'W koszu' : ($page_publish ? 'Opublikoway' : 'Niepublikowany'); ?>
					</li>
					<li>
						<label>Data opublikowania</label>
						<?= $page_published ? $page_published : '<i>Nieokreślono</i>'; ?>
					</li>
					<li>
						<label>Data zakończnia publikacji</label>
						<?= $page_unpublished ? $page_unpublished : '<i>Nieokreślono</i>'; ?>
					</li>
				</ol>
			</fieldset>

			<?= Form::open(url('adm/Page/Diff')); ?>
				<fieldset style="margin-top: 0">
					<?= Form::hidden('id', $page_id); ?>
					<table id="media" style="width: 97%; margin: auto;">
						<thead>
							<tr>
								<th style="padding-left: 0; padding-right: 0; width: 25px"></th>
								<th style="padding-left: 0; padding-right: 0; width: 25px"></th>
								<th>Data utworzenia</th>
								<th>ID rewizji</th>
								<th>Nazwa użytkownika</th>
								<th>IP</th>
								<th>Opis zmian</th>
							</tr>
						</thead>
						<tbody>
							<?php if ($versions) : ?>
							<?php foreach ($versions as $row) : ?>
							<tr>
								<td style="padding-left: 0; padding-right: 0;">
									<?php if ($count < sizeof($versions) -1) :?>
									<?= Form::radio('r1', $row['text_id']); ?>
									<?php endif; ?>
								</td>
								<td style="padding-left: 0; padding-right: 0;">
									<?php if (++$count > 1) : ?>
									<?= Form::radio('r2', $row['text_id']); ?>
									<?php endif; ?>
								</td>
								<td><?= User::formatDate($row['text_time']); ?></td>
								<td><?= Html::a(url('adm/Page/Revision/' . $page_id . '?r=' . $row['text_id']), $row['text_id']); ?></td>
								<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name'], array('class' => 'user')); ?></td>
								<td><?= $row['text_ip']; ?></td>
								<td><?= $row['text_log']; ?></td>
							</tr>
							<?php endforeach; ?>
							<?php else : ?>
							<tr>
								<td colspan="7" style="text-align: center;">Brak tekstu przypisanego do artykułu</td>
							</tr>
							<?php endif; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="7">
									<?= Form::submit('', 'Zobacz różnice'); ?>
								
								</td>
							</tr>
						</tfoot>
					</table>

				</fieldset>
			<?= Form::close(); ?>

			<fieldset style="margin-top: 0">
				<table id="media" style="width: 97%; margin: auto;">
					<thead>
						<tr>
							<th>ID</th>
							<th>Data napisania</th>
							<th>Użytkownik</th>
							<th>IP</th>
							<th>Treść</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($report) : ?>
						<?php foreach ($report as $row) : ?>
						<tr <?= $row['report_close'] ? 'class="close"' : ''; ?>>
							<td><?= Html::a(url('adm/Report/Submit/' . $row['report_id']), $row['report_id']); ?></td>
							<td><?= User::formatDate($row['report_time']); ?></td>
							<td><?= $row['report_user'] > User::ANONYMOUS ? Html::a(url('adm/User/Submit/' . $row['report_user']), $row['user_name'], array('class' => 'user')) : 'Anonim'; ?></td>
							<td><?= $row['report_ip']; ?></td>
							<td><span title="<?= $row['report_message']; ?>"><?= Text::limit($row['report_message'], 50); ?></span></td>
						</tr>
						<?php endforeach; ?>
						<?php else : ?>
						<tr>
							<td colspan="5" style="text-align: center;">Brak raportów dotyczących tej strony.</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>

			</fieldset>

			<fieldset style="margin-top: 0">
				<table id="media" style="width: 97%; margin: auto;">
					<thead>
						<tr>
							<th>Nazwa użytkownika</th>
							<th>Data obserwacji</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($watch) : ?>
						<?php foreach ($watch as $row) : ?>
						<tr>
							<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name'], array('class' => 'user')); ?></td>
							<td><?= User::formatDate($row['watch_time']); ?></td>
						</tr>
						<?php endforeach; ?>
						<?php else : ?>
						<tr>
							<td colspan="2" style="text-align: center;">Nikt nit obserwuje tej strony.</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>				
			</fieldset>

		</div>
	</div>
</div>