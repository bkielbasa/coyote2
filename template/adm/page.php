<style type="text/css">

.report
{
		background:			url('<?= Url::site(); ?>template/adm/img/pageErrorIcon.png') no-repeat 3px 50%;
		padding-left:		25px !important;
}
</style>

<div id="page" style="overflow: hidden;">
	<?php include('_partialPage.php'); ?>

	<div id="page-content">
		<div class="page-menu">
			<ul>
				<li><a class="focus">Ostatnie zmiany w tekstach</a></li>
				<li><a>Ostatnie edycje</a></li>
				<li><a>Nowe strony</a></li>
				<?php if ($report) : ?>
				<li><a class="report">Raporty</a></li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="menu-block">

			<fieldset style="margin-top: 0;">

				<table id="media" style="font-size: 11px">
					<thead>
						<tr>
							<th>Tytuł</th>
							<th>Użytkownik</th>
							<th>Data modyfikacji</th>
							<th>IP</th>
							<th>Dziennik zmian</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($revision as $row) : ?>
						<tr <?= Text::alternate('class="alternate"', ''); ?>>
							<td><?= Html::a(url('adm/Page/View/' . $row['page_id']), Text::limit($row['page_subject'], 30), array('class' => 'page')); ?></td>
							<td><?= Html::a(url('adm/User/Submit/' . $row['text_user']), $row['user_name'], array('class' => 'user')); ?></td>
							<td><?= User::formatDate($row['text_time']); ?></td>
							<td><?= $row['text_ip']; ?></td>
							<td><?= $row['text_log']; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</fieldset>
			
			<fieldset style="margin-top: 0">
				<table id="media" style="font-size: 11px;">
					<thead>
						<tr>
							<th>Tytuł</th>
							<th>Ścieżka</th>
							<th>Data modyfikacji</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($update as $row) : ?>
						<tr>
							<td><?= Html::a(url('adm/Page/View/' . $row['page_id']), Text::limit($row['page_subject'], 30), array('class' => 'page')); ?></td>
							<td><?= $row['location_text']; ?></td>
							<td><?= User::date($row['page_edit_time']); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>			
			</fieldset>
			
			<fieldset style="margin-top: 0;">
				<table id="media" style="font-size: 11px;">
					<thead>
						<tr>
							<th>Tytuł</th>
							<th>Ścieżka</th>
							<th>Data utworzenia</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($insert as $row) : ?>
						<tr>
							<td><?= Html::a(url('adm/Page/View/' . $row['page_id']), Text::limit($row['page_subject'], 30), array('class' => 'page')); ?></td>
							<td><?= $row['location_text']; ?></td>
							<td><?= User::date($row['page_time']); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>			
			</fieldset>

			<?php if ($report) : ?>
			<fieldset style="margin-top: 0;">
				<table id="media" style="font-size: 11px">
					<thead>
						<tr>
							<th>ID</th>
							<th>Strona</th>
							<th>Użytkownik</th>
							<th>IP</th>
							<th>Data</th>
						</tr>
					</thead>
					<tbody>						
						<?php foreach ($report as $row) : ?>
						<tr <?= Text::alternate('class="alternate"', ''); ?>>
							<td><?= Html::a(url('adm/Report/Submit/' . $row['report_id']), $row['report_id'], array('class' => 'report', 'title' => $row['report_message'])); ?></td>
							<td><?= Html::a(url('adm/Page/View/' . $row['page_id']), $row['page_subject']); ?></td>
							<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name'], array('class' => 'user')); ?></td>
							<td><?= $row['report_ip']; ?></td>
							<td><?= User::formatDate($row['report_time']); ?></td>
						</tr>
						<?php endforeach; ?>						
					</tbody>
				</table>

			</fieldset>
			<?php endif; ?>
		</div>
	</div>
</div>