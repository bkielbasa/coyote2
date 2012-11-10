<table id="forum">
	<thead>
		<?php if (strval($page) == 'Connector_Forum') : ?>
		<tr>
			<th colspan="5" class="pagination">

				<span class="total-items">
					<?= count($forum); ?> <?= Declination::__(count($forum), array('kategoria', 'kategorie', 'kategorii')); ?> forum

					<ul>
						<li>
							<a id="mark-read-button" class="mark" title="Odznacz kategorie jako przeczytane">Odznacz jako przeczytane</a>
						</li>
						<li>
							<a href="<?= url($page->getLocation()); ?>?export=atom" class="feed" title="Wyświetl najnowsze wątki w formacie atom">Pobierz nagłówki Atom</a>
						</li>
						<?php if (User::$id > User::ANONYMOUS) : ?>
						<li>
							<a href="<?= url($page->getLocation()); ?>?view=mine#mine" class="mine" title="Wyświetl wątki mojego autorstwa lub te, w których brałem udział">Wyświetl moje wątki</a>
						</li>
						<?php endif; ?>
					</ul>
				</span>
			</th>
		</tr>
		<?php endif; ?>
		<tr>
			<th style="width: 5%"></th>
			<th style="width: 50%"></th>
			<th style="width: 10%">Wątki</th>
			<th style="width: 10%">Posty</th>
			<th style="width: 25%">Ostatni post</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($forum as $row) : ?>
		<?php @$index++; ?>
		<?php @$totalTopics += $row['forum_topics']; ?>
		<?php @$totalPosts += $row['forum_posts']; ?>

		<?php if ($row['forum_section']) : ?>
		<tr class="section">
			<td colspan="2">
				<h2><?= $row['forum_section']; ?></h2>
			</td>
			<td><?= $index <= 1 ? '' : 'Wątki'; ?></td>
			<td><?= $index <= 1 ? '' : 'Posty'; ?></td>
			<td>
				<?= $index <= 1 ? '' : 'Ostatni post'; ?>

				<a class="section-toggle <?= $row['is_hidden'] ? 'toggle' : ''; ?>"></a>
			</td>
		</tr>
		<?php endif; ?>

		<tr id="forum-<?= $row['forum_id']; ?>" <?= $row['is_hidden'] ? 'style="display: none"' : ''; ?>>
			<td class="forum-icon">
				<?php if ($row['forum_lock']) : ?>
				<span class="forum-icon-lock" title="Forum jest zablokowane"></span>
				<?php elseif ($row['forum_unread']) : ?>
				<span class="forum-icon-new" title="Kliknij, aby odnzaczyć jako przeczytane"></span>
				<?php else : ?>
				<span class="forum-icon-normal" title="Na forum nie ma nowych postów"></span>
				<?php endif; ?>
			</td>
			<td class="forum-subject">
				<h3><?= Html::a(url($row['location_text']), $row['page_subject'], array('title' => def($row['page_title'], $row['page_subject']))); ?></h3>

				<?= $row['forum_description']; ?>
				<?php if (isset($row['children'])) : ?>
				<div>
					<strong>Podforum</strong>:

					<ul>
						<?php foreach ($row['children'] as $children) : ?>
						<li <?= $children['forum_unread'] ? 'class="sub-unread" title="Na tym forum istnieją nieprzeczytane posty"' : ''; ?>><?= Html::a(url($children['location_text']), $children['page_subject']); ?></li>
						<?php endforeach; ?>
					</ul>

				</div>
				<?php endif; ?>
			</td>

			<?php if ($row['forum_url']) : ?>
			<td colspan="5">Ilość przekierowań: <?= $row['forum_topics']; ?></td>
			<?php else : ?>
			<td><?= number_format($row['forum_topics']); ?></td>
			<td><?= number_format($row['forum_posts']); ?></td>
			<td class="forum-last-post">
				<?php if ($row['forum_last_post_id']) : ?>
				<span class="timestamp" data-timestamp="<?= $row['post_time']; ?>" title="<?= User::formatDate($row['post_time'], false, false); ?>"><?= User::date($row['post_time']); ?></span>
				<span>
					<strong>Temat:</strong>
					<?php if ($row['topic_unread']) : ?>
					<a rel="nofollow" title="Przejdź do pierwszego nieczytanego postu" class="topic-last" href="<?= url($row['topic_path']) . '?view=unread'; ?>"></a>
					<?php endif; ?>

					<?= Html::a(url($row['topic_path'] . '?p=' . $row['forum_last_post_id'] . '#id' . $row['forum_last_post_id']), Text::limitHtml($row['topic_subject'], 22), array('title' => $row['topic_subject'])); ?>
				</span>
				<span><strong>Autor:</strong> <?= $row['post_user'] > User::ANONYMOUS ? Html::a(url('@profile?id=' . $row['post_user']), $row['user_name']) : $row['post_username']; ?></span>
				<?php else : ?>
				--
				<?php endif; ?>
			</td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<?php if (strval($page) == 'Connector_Forum') : ?>
	<tfoot>
		<tr>
			<td colspan="2">
				<a href="<?= url($page->getLocation()); ?>?export=atom" class="feed" title="Wyświetl najnowsze wątki w formacie atom">Pobierz nagłówki Atom</a>
				<a href="<?= url($page->getLocation()); ?>?view=mine#mine" class="mine" title="Wyświetl wątki mojego autorstwa lub te, w których brałem udział">Wyświetl moje wątki</a>
			</td>
			<td><?= number_format($totalTopics); ?></td>
			<td><?= number_format($totalPosts); ?></td>
			<td><a id="mark-read-button" class="mark" title="Odznacz kategorie jako przeczytane">Odznacz jako przeczytane</a></td>
		</tr>
	</tfoot>
	<?php endif; ?>
</table>