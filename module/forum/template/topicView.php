<script type="text/javascript">
<!--
	<?php if (isset($reasonList)) : ?>
	var reasonList = new Array();

	<?php foreach ($reasonList as $key => $item) : ?>
	reasonList['<?= $key; ?>'] = '<?= $item; ?>';
	<?php endforeach; ?>
	<?php endif; ?>

	var hash = '<?= $hash; ?>';
	var preventAjax = false;

	$(document).ready(function()
	{
		$.posting.init({currentUrl: '<?= url($page->getLocation()); ?>', topicId: '<?= $topic_id; ?>'});

		<?php if (User::$id > User::ANONYMOUS) : ?>
		$('#quick-form').localStorage();
		<?php endif; ?>

		$('select[name=page]').change(function()
		{
			window.location.href = '<?= url($page->getLocation()); ?>?page=' + $(this).val();
		});

		$('#post select[name=forum]').change(function()
		{
			window.location.href = $(this).val();
		});

		<?php if ($pagination->getTotalPages() > 1 && $pagination->getCurrentPage() > 1) : ?>
		$('table.post:eq(0) > tbody').hide();
		<?php endif; ?>

		$('#search-top input[name=q][autocomplete=off]').autocomplete({url: '<?= url(Path::connector('forumSearch')); ?>'});
		$('#search-top').mouseenter(function()
		{
			$(this).addClass('search-holder');
		});
	});
//-->
</script>

<div id="page">
	<?= $page->getContent(); ?>
</div>

<a style="display: none;" title="Strona główna forum" href="<?= url('@forum'); ?>" data-shortcut="g+i">Strona główna forum</a>
<a style="display: none;" title="Strona glówna kategorii forum" href="<?= url($categoryUrl); ?>" data-shortcut="g+f">Kategoria forum</a>

<?php if ($topic_lock) : ?>
<p class="note" id="lock-message" style="margin-bottom: 16px">Temat został <strong>zablokowany</strong><?= $lockInfo['user_name'] ? ' dnia ' . User::formatDate($lockInfo['log_time'], false, false) . ' przez ' . Html::a(url('@profile?id=' . $lockInfo['log_user']), $lockInfo['user_name']) : ''; ?>. Nie możesz odpowiadać oraz edytować postów w tym temacie.</p>
<?php endif; ?>

<?php if (isset($moveInfo)) : ?>
<p class="note">
	Ten wątek został przeniesiony przez użytkownika <?= Html::a(url('@profile?id=' .  $moveInfo['log_user']), $moveInfo['user_name']); ?>, <?= User::formatDate($moveInfo['log_time']); ?> z kategorii <?= Html::a(url($moveInfo['location_text']), $moveInfo['page_subject']); ?>

	<?php if ($moveInfo['log_message']) : ?>
	<br /><strong>Powód przeniesienia:</strong> <?= $moveInfo['log_message']; ?>
	<?php endif; ?>
</p>
<?php endif; ?>

<div id="body">
	<ul class="f-menu-top">
		<li id="thread-button" class="focus"><h1><a title="<?= $page->getSubject(); ?>"><?= $page->getSubject(); ?></a></h1></li>
		<?php if ($isEditable) : ?>
		<li id="log-button"><a>Dziennik zdarzeń</a></li>
		<?php endif; ?>

		<?php if ($isWriteable || $isEditable) : ?>
		<li id="reply-top" title="Napisz odpowiedź w tym temacie (skrót: r)"><a rel="nofollow" href="<?= url($page->getLocation()) . '?mode=submit'; ?>" data-shortcut="r"><span>Odpowiedz</span></a></li>
		<li id="submit-top" title="Napisz nowy temat w tej kategorii (skrót: n)" style="margin-right: 10px;"><a rel="nofollow" href="<?= url($categoryUrl) . '?mode=submit'; ?>" data-shortcut="n"><span>Nowy temat</span></a></li>
		<?php endif; ?>

		<li id="search-top">
			<?= Form::open(Path::connector('forumSearch')); ?>
				<fieldset>
					<?= Form::text('q', '', array('autocomplete' => 'off', 'placeholder' => 'Szukaj na forum...')); ?><a title="Szukaj na forum" href="<?= url(Path::connector('forumSearch')); ?>" class="search-submit-button"></a>
				</fieldset>
			<?= Form::close(); ?>
		</li>
	</ul>

	<div id="post">
		<table cellspacing="1" class="post page" style="margin-top: -2px; margin-bottom: 0">
			<thead>
				<tr>
					<td colspan="3" class="post-begin">
						<?php if ($isEditable) : ?>
						<a class="admin-button" id="edit-subject-button" title="Kliknij, aby szybko zmienić tytuł wątku">Zmień tytuł</a>
						<?php endif; ?>

						<?php if ($isLockable) : ?>
						<?php if ($topic_lock) : ?>
						<a id="lock-button" class="admin-button lock" title="Wątek jest zablokowany. Kliknij, aby odblokować wątek">Odblokuj wątek</a>
						<?php else : ?>
						<a id="lock-button" class="admin-button" title="Kliknij, aby zablokować wątek">Zablokuj wątek</a>
						<?php endif; ?>
						<?php endif; ?>

						<?php if ($isMoveable) : ?>
						<dl id="move-button" class="admin-button">
							<dt title="Przenieść ten temat do innej kategorii forum">Przenieś wątek</dt>
							<dd>
								<ul>
									<?php foreach ($forumList as $forumId => $name) : ?>
									<li title="Przenieś wątek do kategorii '<?= $name; ?>'"><a href="#<?= $forumId; ?>"><?= $name; ?></a></li>
									<?php endforeach; ?>
								</ul>
							</dd>
						</dl>
						<?php endif; ?>

						<?php if ($isEditable && ($isStickable || $isAnnounceable)) : ?>
						<dl id="status-button" class="admin-button">
							<dt title="Zmień status wątku">Status wątku</dt>
							<dd>
								<ul style="overflow-y: auto">
									<?php if ($isStickable) : ?>
									<li><a data-status="sticky"><?= $page->isSticky() ? 'Odklej' : 'Przyklej'; ?></a></li>
									<?php endif; ?>
									<?php if ($isAnnounceable) : ?>
									<li><a data-status="announcement"><?= $page->isAnnouncement() ? 'Normalny' : 'Ogłoszenie'; ?></a></li>
									<?php endif; ?>
								</ul>
							</dd>
						</dl>
						<?php endif; ?>

						<?php if (User::$id > User::ANONYMOUS) : ?>
						<?php if ($isWatched) : ?>
						<a title="Kliknij, aby zaprzestać obserwacji tematu" id="watch-button" class="watch-on admin-button">Wątek obserwowany</a>
						<?php else : ?>
						<a title="Kliknij, aby obserwować ten temat" id="watch-button" class="admin-button">Obserwuj wątek</a>
						<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php if (isset($poll)) : ?>
				<tr>
					<td colspan="3" class="post-begin"><?php $poll->display(); ?></td>
				</tr>
				<?php endif; ?>
			</thead>

			<?php $itemsList = array($topicFirstPost); ?>
			<?php include('_partialPostList.php'); ?>

			<tfoot>
				<tr>
					<td colspan="3" id="topic-toggle">
						<a title="Pokaż/ukryj treść pytania"></a>
					</td>
				</tr>
			</tfoot>
		</table>

		<?php if ($isEditable) : ?>
		<div id="log-spot" class="page" style="display: none;">

			<h2>Lista zdarzeń</h2>

			<div class="bubble">
				<table>
					<thead>
						<tr>
							<th>Data i czas</th>
							<th>Użytkownik</th>
							<th>IP</th>
							<th>Akcja</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($log) : ?>
						<?php foreach ($log as $row) : ?>
						<tr <?= Text::alternate('', 'class="even"'); ?>>
							<td><?= User::date($row['log_time']); ?></td>
							<td style="text-align: center;">
								<?php if (Auth::get('a_')) : ?>
								<a class="block-button" title="Banuj tego użytkownika" href="<?= url('adm/Ban/Submit?id=' . $row['log_user'] . '&ip=' . $row['log_ip']); ?>"></a>
								<?php endif; ?>

								<?= $row['log_user'] > User::ANONYMOUS ? Html::a(url('@profile?id=' . $row['log_user']), $row['user_name']) : $row['user_name']; ?>
							</td>
							<td style="text-align: center;"><?= $row['log_ip']; ?></td>
							<td>
								<strong><?= isset($logTypes[$row['log_type']]) ? $logTypes[$row['log_type']] : $row['log_type']; ?></strong><br />
								» <?= $row['log_message']; ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php else : ?>
						<tr>
							<td colspan="4" style="text-align: center;">Brak informacji w bazie danych.</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<h2>Osoby, które czytały ten temat</h2>

			<div class="bubble">
				<table>
					<thead>
						<tr>
							<th>Użytkownik</th>
							<th>Data i czas</th>
							<th>Ostatnio z IP</th>
							<th>Ostatnia wizyta</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($marking) : ?>
						<?php foreach ($marking as $row) : ?>
						<tr>
							<td><?= Html::a(url('@profile?id=' . $row['user_id']), $row['user_name'], array('class' => ($row['session_id'] ? 'online' : 'offline'))); ?></td>
							<td style="text-align: center;"><?= User::date($row['mark_time']); ?></td>
							<td style="text-align: center;"><?= def($row['user_ip'], 'Brak informacji'); ?></td>
							<td style="text-align: center;"><?= User::date($row['user_lastvisit']); ?></td>
						</tr>
						<?php endforeach; ?>
						<?php else : ?>
						<tr>
							<td colspan="3" style="text-align: center;">Brak informacji w bazie danych.</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php endif; ?>

		<?php if (!$postList) : ?>
		<ul class="f-menu-bottom">
			<?php if ($isWriteable) : ?>
			<li id="reply-bottom" title="Napisz odpowiedź w tym temacie"><a rel="nofollow" href="<?= url($page->getLocation()) . '?mode=submit'; ?>"><span>Odpowiedz</span></a></li>
			<?php endif; ?>
		</ul>
		<?php endif; ?>

		<?php if ($tags) : ?>
		<div id="tags">
			<h3>Tagi:</h3>

			<?php foreach ($tags as $word => $row) : ?>
			<?= Html::a(url($forumUrl) . '?view=all&tag=' . rawurlencode($word), $word, array('style' => "font-size: {$row['size']}px", 'title' => "{$row['weight']} tematów oznaczonym tym tagiem")); ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ($postList) : ?>
		<?php if ($isVoteable && !$topic_sticky) : ?>
		<ul class="f-menu-top p-menu">
			<?= Topic::buildTopicMenu($sort); ?>
		</ul>
		<?php endif; ?>

		<table cellspacing="1" class="post page" style="margin-top: -2px; margin-bottom: 0">
			<thead>
				<tr>
					<td colspan="3" class="post-begin">
						<?php if ($pagination->getTotalPages() > 1) : ?>
						<?= $pagination; ?>
						<?php endif; ?>

						<span class="total-items">
							<?= number_format($pagination->getTotalItems(), 0, ',', ' '); ?> <?= Declination::__($pagination->getTotalItems(), array('odpowiedź', 'odpowiedzi', 'odpowiedzi')); ?>

							<ul>
								<li>
									<a href="<?= url($page->getLocation()); ?>?export=atom" class="feed">Eksportuj do formatu Atom</a>
								</li>
							</ul>
						</span>

					</td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3" class="topic-end">
						<?= Form::open(Path::connector('forumSearch'), array('id' => 'box-forum-search')); ?>
							<fieldset>
								<?= Form::hidden('t', $topic_id); ?>
								<?= Form::input('q', htmlspecialchars(def($input->get['q'])), array('style' => 'width: 150px', 'placeholder' => 'Szukaj w tym wątku...')); ?><?= Form::submit('', 'Szukaj w tym wątku'); ?>
							</fieldset>
						<?= Form::close(); ?>

						<a id="topic-return" title="Przejdź do kategorii forum" href="<?= url($categoryUrl); ?>"></a>
						<?= Form::select('forum', Form::option(array_flip(array_map('url', array_flip($forumList))), url($categoryUrl))); ?>

						<?php if ($pagination->getTotalPages() > 1) : ?>
						<?= $pagination; ?>
						<?php endif; ?>
					</td>
				</tr>
			</tfoot>

			<?php $itemsList = &$postList; ?>
			<?php include('_partialPostList.php'); ?>


		</table>
		<?php endif; ?>
	</div>
</div>

<?php if ($postList) : ?>
<ul class="f-menu-bottom">
	<?php if ($isWriteable || $isEditable) : ?>
	<li id="reply-bottom" title="Napisz odpowiedź w tym temacie"><a rel="nofollow" href="<?= url($page->getLocation()) . '?mode=submit'; ?>"><span>Odpowiedz</span></a></li>
	<?php endif; ?>
</ul>
<?php endif; ?>

<?php if ($isWriteable && User::$id > User::ANONYMOUS) : ?>
<?= Form::open(url($page->getLocation()) . '?mode=submit&topicId=' . $page->getTopicId(), array('id' => 'quick-form', 'method' => 'post')); ?>
	<fieldset>
		<?= Form::hidden('watch', $isWatched || !$hasUserPost); ?>
		<?= Form::hidden('enableSmilies', (bool) User::data('allow_smilies')); ?>

		<?= Form::textarea('content', 'Kliknij, aby napisać szybką odpowiedź...', array('rows' => 1, 'cols' => 60)); ?>
		<?= Form::submit('', 'Wyślij odpowiedź', array('title' => 'Kliknij, aby dodać odpowiedź (Ctrl+Enter)')); ?>
	</fieldset>
<?= Form::close(); ?>
<?php endif; ?>

<div style="overflow: hidden;">
	<a id="feed-button" href="<?= url($page->getLocation()); ?>?export=atom" title="Eksportuj posty do nagłówków Atom">atom</a>
	<p style="float: right;">Ilość odpowiedzi na stronę <?= Form::select('page', Form::option($pageList, $perPage)); ?></p>
</div>

<div id="users-online">
	<div><?= count($usersOnline) + $anonymousUsersOnline; ?> użytkownik(ów) przegląda ten temat (<?= $anonymousUsersOnline; ?> gości)</div>

	<p><?= implode(', ', $usersOnline); ?></p>
</div>