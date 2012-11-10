<tbody>
	<?php foreach ($itemsList as $row) : ?>
	<tr>
		<td class="post-user">
			<?php if (isset($onlineUsers[$row['post_user']])) : ?>
			<strong title="Użytkownik jest online" class="online">
			<?php elseif ($row['post_user'] == User::ANONYMOUS) : ?>
			<strong class="offline">
			<?php else : ?>
			<strong title="Użytkownik jest offline" class="offline">
			<?php endif; ?>

				<?= Topic::getAuthor($row['post_user'], $row['post_username'], $row['user_name']); ?>
			</strong>
		</td>

		<td class="post-time" colspan="2">
			<a title="Link do tego postu" id="id<?= $row['post_id']; ?>" class="post-link <?= $row['post_time'] > $markTime ? 'post-link-new' : ''; ?>" href="<?= url($page->getLocation()); ?>?p=<?= $row['post_id']; ?>#id<?= $row['post_id']; ?>"></a>

			<abbr class="timestamp" title="<?= User::formatDate($row['post_time'], false, false); ?>" data-timestamp="<?= $row['post_time']; ?>"><?= User::date($row['post_time']); ?></abbr>
			<?php if ($isEditable) : ?>
			<span title="<?= $row['post_ip'] . ' (' . $row['post_host'] . ') ' . $row['post_browser']; ?>" class="post-ip">(<?= Text::limit($row['post_ip'] . ' (' . $row['post_host'] . ') ' . $row['post_browser'], 140); ?>)</span>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td class="post-user">

			<?php if ($row['post_user'] > User::ANONYMOUS) : ?>
			<ul>
				<?php if (!empty($row['user_photo'])) : ?>
				<li style="margin: 5px 0 10px 0"><?= Html::img(url('store/_a/' . $row['user_photo'])); ?></li>
				<?php endif; ?>

				<li><label>Rejestracja:</label> <?= User::formatDate($row['user_regdate']); ?></li>
				<li><label>Ostatnio:</label> <?= User::formatDate(isset($onlineUsers[$row['post_user']]) ? $onlineUsers[$row['post_user']] : $row['user_lastvisit']); ?></li>

				<?php if ($row['group_name'] != 'USER' && $row['group_name'] != 'ANONYMOUS') : ?>
				<li><label>Grupa:</label> <?= $row['group_name']; ?></li>
				<?php endif; ?>

				<?php if ($row['user_allow_count'] && User::data('allow_count')) : ?>
				<li><label>Ilość postów:</label> <a rel="nofollow" title="Znajdź posty użytkownika" href="<?= url(Path::connector('forum')) . '?view=user&user=' . $row['user_id'] . '#user'; ?>"><?= number_format($row['user_post']); ?></a></li>
				<?php endif; ?>

			</ul>
			<?php endif; ?>

		</td>
		<td class="post-body">
			<?php if (isset($reportList[$row['post_id']])) : ?>
			<div class="note">
				Ten post został zgłoszony do moderacji!

				<?php foreach ($reportList[$row['post_id']] as $report) : ?>
				<p><strong><?= User::date($report['report_time']); ?></strong>: <?= $report['report_message']; ?></p>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="post-content" data-post-id="<?= $row['post_id']; ?>"><?= $row['post_text']; ?></div>

			<?php if ($row['post_edit_count']) : ?>
			<div class="notice">
				Edytowany <?= $row['post_edit_count'] == 1 ? '1 raz' : ($row['post_edit_count'] . ' razy'); ?>:
				<span class="timestamp" data-timestamp="<?= $row['post_edit_time']; ?>" title="Ostatnia modyfikacja: <?= User::formatDate($row['post_edit_time'], false, false); ?>"><?= User::date($row['post_edit_time']); ?></span>
				przez <?= Html::a(url('@profile?id=' . $row['post_edit_user']), $row['edit_user_name']); ?>

				<?php if (($row['post_user'] == User::$id && User::$id > User::ANONYMOUS) || $isEditable) : ?>
				<a class="post-history-button" title="Zobacz historię zmian tego postu" href="<?= url('@forum/Post/Version/' . $row['post_id']); ?>"></a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div id="post-comment-<?= $row['post_id']; ?>" data-post-id="<?= $row['post_id']; ?>" class="comments" <?= empty($comments[$row['post_id']]) ? 'style="display: none;"' : ''; ?>>

				<?php if (isset($comments[$row['post_id']])) : ?>

				<?php if (($commentSize = sizeof($comments[$row['post_id']])) > 5) : ?>
				<a title="Pokaż pozostałe komentarze" class="show-comments">Pokaż <?= Declination::__($commentSize - 5, array('pozostały', 'pozostałe', 'pozostałe')); ?> <strong><?= $commentSize - 5; ?></strong> <?= Declination::__($commentSize - 5, array('komentarz', 'komentarze', 'komentarzy')); ?></a>
				<?php endif; ?>

				<?php foreach ($comments[$row['post_id']] as $index => $comment) : ?>
				<div <?= $commentSize > 5 && $index < ($commentSize - 5) ? 'style="display: none;"' : ''; ?> id="comment-<?= $comment['comment_id']; ?>" data-comment-id="<?= $comment['comment_id']; ?>">
					<?= $comment['comment_text']; ?> -
					<?= Html::a(url('@profile?id=' . $comment['comment_user']), $comment['user_name'], array('class' => 'user-name', 'data-photo' => $comment['user_photo'], 'data-pm-url' => url('@user?controller=Pm&action=Submit&user=' . $comment['comment_user']), 'data-find-url' => url(Path::connector('forum')) . '?view=user&user=' . $comment['comment_user'] . '#user')); ?>
					<span class="timestamp" data-timestamp="<?= $comment['comment_time']; ?>" title="<?= User::formatDate($comment['comment_time'], false, false); ?>"><?= User::formatDate($comment['comment_time'], false, true); ?></span>

					<?php if (($comment['comment_user'] == User::$id && $isWriteable) || $isEditable) : ?>
					<span title="Edytuj ten komentarz" class="comment-edit"></span>
					<?php endif; ?>
					<?php if (($comment['comment_user'] == User::$id && $isWriteable) || $isRemovable) : ?>
					<span title="Usuń ten komentarz" class="comment-delete"></span>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				<?php endif; ?>

				<?= Form::open('', array('method' => 'post', 'style' => 'display: none;')); ?>
					<fieldset>
						<?= Form::hidden('postId', $row['post_id']); ?>

						<?= Form::textarea('comment', '', array('cols' => 90, 'rows' => 2, 'style' => 'width: 98%;')); ?>

						<p>Pozostało <strong>580</strong> znaków</p>
						<?= Form::submit('', 'Dodaj komentarz'); ?>
					</fieldset>
				<?= Form::close(); ?>
			</div>

		</td>

		<?php if ($isVoteable && !$topic_sticky) : ?>
		<td class="post-vote">
			<?= Form::hidden('', $row['post_id']); ?>

			<?php if ($row['post_user'] != User::$id || User::$id == User::ANONYMOUS) : ?>
			<a class="vote-up <?= User::$id > User::ANONYMOUS && $row['value'] == 1 ? 'vote-up-on' : ''; ?>" title="Kliknij, jeżeli post jest wartościowy (kliknij ponownie, aby cofnąć)">Głosuj na ten post</a>
			<a class="vote-count" title="Ocena postu"><?= $row['post_vote']; ?></a>
			<a class="vote-down <?= User::$id > User::ANONYMOUS && $row['value'] == -1 ? 'vote-down-on' : ''; ?>" title="Kliknij, jeżeli post nie jest wartościowy (kliknij ponownie, aby cofnąć)">Głosuj na ten post</a>
			<?php else : ?>
			<a class="vote-count" title="Ocena postu"><?= $row['post_vote']; ?></a>
			<?php endif; ?>

			<?php if ($topic_solved == $row['post_id']) : ?>
			<a class="<?= User::$id > User::ANONYMOUS && (User::$id == $topicFirstPost['post_user'] || $isEditable) ? 'unsolved' : ''; ?> solved" title="Ta odpowiedź została uznana przez autora jako satysfakcjonująca"></a>
			<?php elseif (User::$id > User::ANONYMOUS && (User::$id == $topicFirstPost['post_user'] || $isEditable) && $topic_first_post_id != $row['post_id']) : ?>
			<a class="unsolved" title="Kliknij, aby ustawić tą odpowiedź jako zaakceptowaną (kliknij ponownie, aby cofnąć)"></a>
			<?php endif; ?>
		</td>
		<?php endif; ?>
	</tr>
	<tr>
		<td class="post-bottom">
			<?php if ($row['post_user'] > User::ANONYMOUS) : ?>
			<a rel="nofollow" title="Znajdź posty użytkownika" href="<?= url(Path::connector('forum')) . '?view=user&user=' . $row['user_id'] . '#user'; ?>" class="search-button post-image-button"></a>
			<?php endif; ?>

			<?php if ($row['post_user'] > User::ANONYMOUS && User::$id > User::ANONYMOUS) : ?>
			<a title="Napisz wiadomość prywatną" href="<?= url('@user?controller=Pm&action=Submit&user=' . $row['post_user']); ?>" class="pm-button post-image-button"></a>
			<?php endif; ?>

			<?php if ($isMergeable && @($postCounter++) > 0) : ?>
			<a title="Połącz z poprzednim postem" href="#id<?= $row['post_id']; ?>" class="merge-button post-image-button"></a>
			<?php endif; ?>

			<?php if (Auth::get('a_')) : ?>
			<a class="block-button post-image-button" title="Banuj tego użytkownika" href="<?= url('adm/Ban/Submit?id=' . $row['post_user'] . '&ip=' . $row['post_ip']); ?>"></a>
			<?php endif; ?>
		</td>
		<td class="post-bottom" colspan="2">

			<?php if (User::$id > User::ANONYMOUS && !$forum_lock && !$topic_lock) : ?>
			<a title="Powiadamiaj o nowych komentarzach do postu" data-post-id="<?= $row['post_id']; ?>" class="subscribe-button post-button <?= $row['subscribe'] ? 'post-button-checked' : ''; ?>">Obserwuj</a>
			<?php endif; ?>
			<?php if (User::$id > User::ANONYMOUS && (!$forum_lock && !$topic_lock || $isEditable)) : ?>
			<a title="Napisz komentarz do postu" data-post-id="<?= $row['post_id']; ?>" class="comment-button post-button">Komentuj</a>
			<?php endif; ?>

			<?php if (!$forum_lock && !$topic_lock) : ?>
			<a rel="nofollow" title="Zgłoś naruszenie w tym poście" href="<?= url('@user?controller=Report&id=' . $page->getId() . '&section=' . $row['post_id'] . '&anchor=' . base64_encode("p=$row[post_id]#id$row[post_id]")); ?>" class="report-button post-button">Raportuj</a>
			<?php endif; ?>

			<?php if ($isWriteable || $isEditable) : ?>

			<a rel="nofollow" title="Odpowiedz cytując ten post" href="<?= url($page->getLocation()) . '?mode=submit&postId=' . $row['post_id']; ?>" class="quote-button post-button">Cytuj</a>

			<?php if ($isRemovable || (User::$id == $row['post_user'] && User::$id > User::ANONYMOUS && $isWriteable && ($topic_replies ? $row['post_id'] != $topic_first_post_id : true))) : ?>
			<a title="Usuń ten post" href="#id<?= $row['post_id']; ?>" class="delete-button post-button">Usuń</a>
			<?php endif; ?>

			<?php if ($isEditable || (User::$id == $row['post_user'] && User::$id > User::ANONYMOUS && ($topic_replies ? $row['post_id'] != $topic_first_post_id : true))) : ?>
			<a title="Edytuj ten post" href="<?= url($page->getLocation()) . '?mode=edit&postId=' . $row['post_id']; ?>" class="edit-button post-button">Edytuj</a>
			<a title="Włącz/wyłącz szybką edycję postu" data-post-id="<?= $row['post_id']; ?>" class="edit-button post-button fast-edit-button">Szybka edycja</a>
			<?php endif; ?>

			<?php endif; ?>

		</td>
	</tr>
	<tr>
		<td colspan="3" class="post-end"></td>
	</tr>
	<?php endforeach; ?>
</tbody>