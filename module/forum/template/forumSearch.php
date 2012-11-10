<script type="text/javascript">

	var preventAjax = true;

	$(document).ready(function()
	{
		$('select[name=forum]').live('change', function()
		{
			window.location.href = $(this).val();
		});

		$('#adv-search-button').click(function()
		{
			$('#adv-search').toggle();
		});

		$('#box-forum-search input[name=q]').focus();
		$('#adv-search input[name=user]').autocomplete({url: '<?= url($page->getLocation()); ?>?mode=finduser', autoSubmit: false});

	});
</script>

<ul id="auto-complete" style="display: none;"></ul>
<a style="display: none;" title="Strona główna forum" href="<?= url('@forum'); ?>" data-shortcut="g+i">Strona główna forum</a>

<div id="page">
	<?= $page->getContent(); ?>
</div>

<?php if ($note) : ?>
<p class="note"><?= $note; ?></p>
<?php endif; ?>

<?php if ($error) : ?>
<p class="error"><?= $error; ?></p>
<?php endif; ?>

<?= Form::open('', array('name' => 'form', 'id' => 'box-forum-search', 'class' => 'box-forum-search-main')); ?>
	<fieldset>
		<?= Form::input('q', $query, array('id' => 'q', 'placeholder' => 'Wpisz szukane słowa, aby wyszukać na forum', 'autocomplete' => 'off', 'style' => 'width: 440px')); ?><input type="submit" value=""/>
		<a id="adv-search-button">Szukanie zaawansowane</a>
	</fieldset>
<?= Form::close(); ?>

<?=  Form::open('', array('method' => 'get', 'id' => 'adv-search', 'style' => 'display: none')); ?>

	<h3>Znajdź posty zawierające...</h3>

	<fieldset>
		<?= Form::hidden('q'); ?>
		<ol>
			<li>
				<label>wszystkie słowa:</label>
				<?= Form::text('qaa', !empty($qaa) ? htmlspecialchars($qaa) : htmlspecialchars($input->get['q']), array('autocomplete' => 'off')); ?>
				<small>Wpisz słowa kluczowe które chcesz wyszukać. Zwróci posty, które zawierają wszystkie szukane słowa</small>
			</li>
			<li>
				<label>dokładnie to wyrażenie:</label>
				<?= Form::text('qf', htmlspecialchars($qf), array('autocomplete' => 'off')); ?>
				<small>Szuka dokładnie takiej frazy. By zrobić to za pomocą pola wyszukiwania, wpisz frazę w cudzysłowie - np. <tt>"programowanie sieciowe"</tt></small>
			</li>
			<li>
				<label>dowolne z tych słów:</label>
				<?= Form::text('qa', htmlspecialchars($qa), array('autocomplete' => 'off')); ?>
				<small>By zrobić to za pomocą pola wyszukiwania, użyj operatorów - np. <tt>programowanie OR sieciowe</tt></small>
			</li>
			<li>
				<label>żadne z tych słów:</label>
				<?= Form::text('qn', htmlspecialchars($qn), array('autocomplete' => 'off')); ?>
				<small>Posty, które nie zawierają podanych słów. W normalnym polu wyszukiwania możesz użyć operatorów +- - np. <tt>+programowanie -sieciowe</tt></small>
			</li>
		</ol>
	</fieldset>

	<div style="float: left">
		<h3>Możesz zawęzić wyniki do...</h3>

		<fieldset>
			<ol>
				<li>
					<label>Tagu:</label>
					<?= Form::text('tag', $input->get['tag']); ?>
					<small>Wątki, w których znajduje się post muszą zawierać wskazany post</small>
				</li>
				<li>
					<label>Autora postu:</label>
					<?= Form::text('user', $input->get['user'], array('autocomplete' => 'off')); ?>
					<small>Filtruje posty i wyświetla tylko te, których użytkownik jest autorem</small>
				</li>
				<!--<li>
					<label></label>
					<?= Form::checkbox('firstPost', 1, isset($input->get->firstPost)); ?> Tylko wątki, których autor jest założycielem
				</li>-->
				<?php if (Auth::get('a_')) : ?>
				<li>
					<label>IP autora:</label>
					<?= Form::text('ip', $input->get['ip']); ?>
					<small>Filtruje posty pod kątem adresu IP</small>
				</li>
				<?php endif; ?>
				<li>
					<label>Szukaj w:</label>
					<?= Form::checkbox('in[]', 'subject', !isset($input->get->in) ? true : in_array('subject', (array) $input->get['in'])); ?> Tytule
					<?= Form::checkbox('in[]', 'text', !isset($input->get->in) ? true : in_array('text', (array) $input->get['in'])); ?> Treści
					<?= Form::checkbox('in[]', 'tag', !isset($input->get->in) ? false : in_array('tag', (array) $input->get['in'])); ?> Tagach
					<?= Form::checkbox('in[]', 'comment', !isset($input->get->in) ? true : in_array('comment', (array) $input->get['in'])); ?> Komentarzach
				<li>
					<label>Kategoria forum:</label>
					<?= Form::select('f', Form::option($categoryList, $input->get['f']), array('multiple' => 'multiple', 'size' => 10, 'style' => 'width: 245px')); ?>
					<small>Możesz zaznaczyć kilka kategorii przytrymując klawisz Shift i klikając na nazwę kategorii</small>
				</li>
				<li>
					<label></label>
					<?= Form::submit('', 'Szukaj'); ?>
				</li>
			</ol>
		</fieldset>
	</div>

	<h3>Priorytety wyszukiwania</h3>

	<fieldset>
		<ol>
			<li>
				<label>Tytuł:</label>
				<?= Form::select('boost[subject]', Form::option($boostList, def($input->get['boost']['subject'], $defaultBoost['subject']))); ?>
			</li>
			<li>
				<label>Treść:</label>
				<?= Form::select('boost[text]', Form::option($boostList, def($input->get['boost']['text'], $defaultBoost['text']))); ?>
			</li>
			<li>
				<label>Tagi:</label>
				<?= Form::select('boost[tag]', Form::option($boostList, def($input->get['boost']['tag'], $defaultBoost['tag']))); ?>
			</li>
			<li>
				<label>Komentarze:</label>
				<?= Form::select('boost[comment]', Form::option($boostList, def($input->get['boost']['comment'], $defaultBoost['comment']))); ?>
			</li>
		</ol>
	</fieldset>

	<h3>Sortowanie wyników</h3>

	<fieldset>
		<ol>
			<li>
				<label>Sortowanie:</label>
				<?= Form::select('sort', Form::option($sortList, def($input->get->sort, 'score'))); ?>
			</li>
			<li>
				<label>Kierunek:</label>
				<?= Form::select('order', Form::option($orderList, def($input->get->order, 'desc'))); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<br style="clear: both" />

<div id="body" style="margin-top: 10px">

	<?php if (!empty($suggestion)) : ?>
	<p style="margin: 10px 0; font-size: 15px">Spróbuj też: <?= $suggestion; ?></p>
	<?php endif; ?>

	<?php if (!empty($hits)) : ?>
	<div id="post">
		<table cellspacing="1" class="post page" style="margin-top: -2px; margin-bottom: 0">
			<thead>
				<tr>
					<td colspan="3" class="post-begin">
						<?php if ($pagination->getTotalPages() > 1) : ?>
						<?= $pagination; ?>
						<?php endif; ?>

						<span style="float: right; padding: 4px 5px 4px 19px; margin-right: 5px ">
							<?= number_format($pagination->getTotalItems(), 0, ',', ' '); ?> <?= Declination::__($pagination->getTotalItems(), array('post', 'posty', 'postów')); ?>
						</span>

					</td>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($hits as $row) : ?>
				<?php if (!empty($row['post_id'])) : ?>
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

					<td class="post-subject" colspan="2">
						<?= Html::a(url($row['location']) . '?p=' . $row['post_id'] . '#id' . $row['post_id'], $row['page_subject']); ?>
					</td>
				</tr>
				<tr>
					<td class="post-user">

						<abbr class="timestamp" style="font-size: 10px; color: #666;" title="<?= User::formatDate($row['post_time'], false, false); ?>" data-timestamp="<?= $row['post_time']; ?>"><?= User::date($row['post_time']); ?></abbr>

						<ul>

							<li><?= $row['breadcrumb']; ?></li>
							<li></li>
							<li><span>Postów:</span> <?= $row['topic_replies']; ?></li>
							<li><span>Wyświetleń:</span> <?= $row['topic_views']; ?></li>

						</ul>

					</td>
					<td class="post-body">

						<div class="post-content" data-post-id="<?= $row['post_id']; ?>"><?= $row['post_text']; ?></div>

						<?php if (isset($comments[$row['post_id']])) : ?>
						<div class="comments">

							<?php foreach ($comments[$row['post_id']] as $index => $comment) : ?>
							<div id="comment-<?= $comment['comment_id']; ?>">
								<?= $comment['comment_text']; ?> -
								<?= Html::a(url('@profile?id=' . $comment['comment_user']), $comment['user_name'], array('class' => 'user-name', 'data-photo' => $comment['user_photo'], 'data-pm-url' => url('@user?controller=Pm&action=Submit&user=' . $comment['comment_user']), 'data-find-url' => url(Path::connector('forum')) . '?view=user&user=' . $comment['comment_user'] . '#user')); ?>
								<span class="timestamp" data-timestamp="<?= $comment['comment_time']; ?>" title="<?= User::formatDate($comment['comment_time'], false, false); ?>"><?= User::formatDate($comment['comment_time'], false, true); ?></span>
							</div>
							<?php endforeach; ?>

						</div>
						<?php endif; ?>

					</td>

					<td class="post-vote">
						<a class="vote-count" title="Ocena postu"><?= $row['post_vote']; ?></a>

						<?php if ($row['topic_solved'] == $row['post_id']) : ?>
						<a class="solved" title="Ta odpowiedź została uznana przez autora jako satysfakcjonująca"></a>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="post-bottom">

					</td>
					<td class="post-bottom" colspan="2">

					</td>
				</tr>
				<tr>
					<td colspan="3" class="post-end"></td>
				</tr>
			<?php endif; ?>
			<?php endforeach; ?>
			</tbody>
			<tfoot>

				<tr>
					<td colspan="3" class="topic-end" style="overflow: hidden">
						<?= Form::select('forum', Form::option($htmlForumList)); ?>

						<?php if ($pagination->getTotalPages() > 1) : ?>
						<p style="float: left;"><?= $pagination; ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</tfoot>

		</table>
	</div>
	<?php endif; ?>
</div>

