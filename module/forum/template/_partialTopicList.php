<table class="topic page" style="margin-bottom: 0px;">
	<thead>
		<tr>
			<th colspan="7" class="pagination">
				<?php if ($pagination->getTotalPages() > 1) : ?>
				<?= $pagination; ?>
				<?php endif; ?>

				<span class="total-items">
					<?= number_format($pagination->getTotalItems(), 0, ',', ' '); ?> tematów

					<ul>
						<li>
							<a id="mark-read-button" class="mark" title="Oznacz posty jako przeczytane">Oznacz jako przeczytane</a>
						</li>
						<li>
							<a href="<?= url($page->getLocation()); ?>?export=atom" class="feed">Pobierz nagłówki Atom</a>
						</li>
					</ul>
				</span>
			</th>
		</tr>

		<tr>
			<th style="width: 2%"></th>
			<th style="min-width: 46%"><a rel="nofollow" title="Sortuj według daty utworzenia wątku" class="br <?= $sort == 'id' ? ($order == 'ASC' ? 'desc' : 'asc') : ''; ?>" href="<?= $sortLinks['id']; ?>">Temat</a></th>

			<?php if (!$forumId) : ?>
			<th style="width: 15%"><a rel="nofollow" title="Sortuj według kategorii forum" class="br <?= $sort == 'forum' ? ($order == 'ASC' ? 'desc' : 'asc') : ''; ?>" href="<?= $sortLinks['forum']; ?>">Forum</a></th>
			<?php endif; ?>

			<th style="min-width: 11%"><a rel="nofollow" title="Sortuj według ilości odpowiedzi" class="br <?= $sort == 'replies' ? ($order == 'ASC' ? 'desc' : 'asc') : ''; ?>" href="<?= $sortLinks['replies']; ?>">Odpowiedzi</a></th>
			<th style="min-width: 11%"><a rel="nofollow" title="Sortuj według ilości wyświetleń" class="br <?= $sort == 'views' ? ($order == 'ASC' ? 'desc' : 'asc') : ''; ?>" href="<?= $sortLinks['views']; ?>">Wyświetleń</a></th>
			<?php if ((!isset($isVoteable) || $isVoteable) && $forumId) : ?>
			<th style="width: 8%"><a rel="nofollow" title="Sortuj według ilości głosów" class="br <?= $sort == 'votes' ? ($order == 'ASC' ? 'desc' : 'asc') : ''; ?>" href="<?= $sortLinks['votes']; ?>">Głosów</a></th>
			<?php endif; ?>
			<th style="min-width: 20%;"><a rel="nofollow" title="Sortuj według daty napisania ostatniego postu" class="br <?= $sort == 'time' ? ($order == 'ASC' ? 'desc' : 'asc') : ''; ?>" href="<?= $sortLinks['time']; ?>">Ostatni post</a></th>
		</tr>
	</thead>

	<tbody>
		<?php if ($topic) : ?>
		<?php $isSticky = false; ?>

		<?php foreach ($topic as $row) : ?>

		<?php if ($row['topic_sticky'] && strval($page) != 'Connector_Forum') : $isSticky = true; ?>
		<?php elseif (!$row['topic_sticky'] && $isSticky) : ?>
		<tr>
			<td colspan="7" class="sticky">
				Pozostałe wątki
			</td>
		</tr>
		<?php $isSticky = false; ?>
		<?php endif; ?>

		<tr id="topic-<?= $row['topic_id']; ?>"

			<?php if (isset($topicClass[$row['topic_page']])) : ?>
			class="<?= implode(' ', $topicClass[$row['topic_page']]); ?>"
			<?php endif; ?>
			>
			<td class="topic-icon">
				<span class="<?= Topic_Model::getStatus($row); ?>"></span>
			</td>
			<td class="topic-subject <?= $sort == 'subject' ? 'sort' : ''; ?>">

				<?php if (isset($topicTags[$row['topic_page']])) : ?>
				<dl class="topic-tags">
					<dt><?= Html::a(url($page->getLocation() . '?tag=' . rawurlencode($topicTags[$row['topic_page']][0])), $topicTags[$row['topic_page']][0], array('title' => 'Zobacz wątki zawierające ten tag')); ?></dt>

					<?php if (sizeof($topicTags[$row['topic_page']]) > 1) : ?>
					<dd>
						<ul>
							<?php for ($i = 1; $i < sizeof($topicTags[$row['topic_page']]); $i++) : ?>
							<li><?= Html::a(url($page->getLocation() . '?tag=' . rawurlencode($topicTags[$row['topic_page']][$i])), $topicTags[$row['topic_page']][$i]); ?></li>
							<?php endfor; ?>
						</ul>
					</dd>
					<?php endif; ?>
				</dl>
				<?php endif; ?>

				<?php if ($row['topic_unread']) : ?>
				<a rel="nofollow" title="Przejdź do pierwszego nieczytanego postu" class="topic-last" href="<?= url($row['topic_path']) . '?view=unread'; ?>"></a>
				<?php else : ?>
				<a rel="nofollow" title="Przejdź do ostatniego postu" class="topic-last-read" href="<?= url($row['topic_path']) . '?p=' . $row['p2_id'] . '#id' . $row['p2_id']; ?>"></a>
				<?php endif; ?>

				<?php if ($row['topic_solved']) : ?>
				<a rel="nofollow" title="Zobacz zaakceptowaną odpowiedź na to pytanie" class="topic-solved" href="<?= url($row['topic_path'] . "?p=$row[topic_solved]#id$row[topic_solved]"); ?>"></a>
				<?php endif; ?>

				<?php if ($row['topic_moved_id']) : ?>
				<span title="Wątek przeniesiony z działu: <?= $row['forum_moved_subject']; ?>">[» <?= $row['forum_moved_subject']; ?>]</span>
				<?php endif; ?>

				<h2><?= Html::a(url($row['topic_path']), Text::limitHtml($row['topic_subject'], 55 - Text::length($row['forum_moved_subject']) - (isset($topicTags[$row['topic_page']][0]) ? Text::length($topicTags[$row['topic_page']][0]) : 0)), array('title' => $row['topic_subject'])); ?></h2>

				<dl class="topic-pagination">
					<dt><abbr class="timestamp" data-timestamp="<?= $row['p1_time']; ?>" title="<?= User::formatDate($row['p1_time'], false, false); ?>"><?= User::date($row['p1_time']); ?></abbr> przez <?= Topic::getAuthor($row['p1_user'], $row['p1_username'], $row['u1_name']); ?></dt>

					<?php if ($row['topic_replies'] > max(10, $model->forum->setting->getPostsPerPage())) : ?>
					<dd>
						<?= Topic::pagination(url($row['topic_path']), $row['topic_replies'], $model->forum->setting->getPostsPerPage()); ?>
					</dd>
					<?php endif; ?>
				</dl>
			</td>

			<?php if (isset($row['forum_subject'])) : ?>
			<td><?= Html::a(url($row['forum_location']), $row['forum_subject']); ?></td>
			<?php endif; ?>

			<td <?= $sort == 'replies' ? 'class="sort"' : ''; ?>><?= number_format($row['topic_replies']); ?></td>
			<td <?= $sort == 'views' ? 'class="sort"' : ''; ?>><?= number_format($row['topic_views']); ?></td>

			<?php if ((!isset($isVoteable) || ($isVoteable)) && $forumId) : ?>
			<?php if ($row['topic_sticky']) : ?>
			<td>-</td>
			<?php else : ?>
			<td><strong title="Liczba głosów oddanych na ten wątek" class="vote <?= $row['topic_vote'] < 0 ? 'negative' : ($row['topic_vote'] > 0 ? 'positive' : ''); ?>"><?= $row['topic_vote']; ?></strong></td>
			<?php endif; ?>
			<?php endif; ?>

			<td class="topic-last <?= $sort == 'time' ? 'sort' : ''; ?>">
				<span class="timestamp" data-timestamp="<?= $row['p2_time']; ?>" title="<?= User::formatDate($row['p2_time'], false, false); ?>"><?= User::date($row['p2_time']); ?></span><br />
				<?= Topic::getAuthor($row['p2_user'], $row['p2_username'], $row['u2_name']); ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php else : ?>
		<tr>
			<td colspan="6" style="text-align: center;">Brak wątków spełniających kryteria.</td>
		</tr>
		<?php endif; ?>

		<tr>
			<td colspan="6" class="topic-nav">
				<?= Form::open(Path::connector('forumSearch'), array('id' => 'box-forum-search')); ?>
					<fieldset>
						<?= Form::input('q', htmlspecialchars(def($input->get['q'], 'Szukaj na forum...')), array('style' => 'width: 150px')); ?><?= Form::submit('', 'Szukaj na forum'); ?>
					</fieldset>
				<?= Form::close(); ?>

				<a id="topic-return" title="Przejdź do tej kategorii" href="<?= url($page->getLocation()); ?>"></a>
				<?= Form::select('forum', Form::option(array_flip(array_map('url', array_flip($forumList))), url($page->getLocation()))); ?>

				<?php if ($pagination->getTotalPages() > 1) : ?>
				<?= $pagination; ?>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>