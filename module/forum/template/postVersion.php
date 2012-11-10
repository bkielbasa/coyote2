<style type="text/css">
ins
{
		color: 					#405a04;
		background-color:		#d1e1ad;

}

del
{
		background-color: 		#e5bdb2;
		color: 					#a82400;
		text-decoration: 		line-through;
}
</style>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<ul class="f-menu-top">
	<li id="thread-button" class="focus"><h1><a href="<?= url($page->getLocation()); ?>" title="<?= $page->getSubject(); ?>"><?= $page->getSubject(); ?></a></h1></li>
</ul>

<table cellspacing="1" class="post page" style="margin-top: -2px; margin-bottom: 0">
	<thead>
		<tr>
			<td colspan="3" class="post-begin">
				<?php if (isset($input->get->diff)) : ?>
				<a title="Kliknij, aby ukryć różnicę" id="diff-button" class="admin-button diff-on" href="<?= url('@forum/Post/Version/' . $postData['post_id']); ?>">Ukryj różnicę</a>
				<?php else : ?>
				<a title="Kliknij, aby pokazać różnicę wersji" id="diff-button" class="admin-button" href="<?= url('@forum/Post/Version/' . $postData['post_id']); ?>?diff=1">Pokaż różnicę w wersji</a>
				<?php endif; ?>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3" class="topic-end">

			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php foreach ($version as $row) : ?>
		<tr id="version-<?= $row['revision']; ?>">
			<td class="post-user">
				<strong title="Użytkownik jest offline" class="<?= !isset($onlineUsers[$row['text_user']]) ? 'offline' : 'online'; ?>">
					<?php if ($row['text_user'] == User::ANONYMOUS) : ?>
					<?= $postData['post_username']; ?>
					<?php else : ?>
					<?= Html::a(url('@profile?id=' . $row['text_user']), $row['user_name']); ?>
					<?php endif; ?>
				</strong>
			</td>

			<td class="post-time" colspan="2">
				<a href="#version-<?= $row['revision']; ?>">#<?= $row['revision']; ?></a>

				<abbr class="timestamp" title="<?= User::formatDate($row['text_time'], false, false); ?>" data-timestamp="<?= $row['text_time']; ?>"><?= User::date($row['text_time']); ?></abbr>
				<?php if ($isEditable) : ?>
				<span title="<?= $row['text_browser']; ?>" class="post-ip">(<?= Text::limit($row['text_ip'] . ' (' . $row['text_host'] . ') ' . $row['text_browser'], 110); ?>)</span>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td class="post-user">

				<?php if ($row['text_user'] > User::ANONYMOUS) : ?>
				<ul>
					<?php if (!empty($row['user_photo'])) : ?>
					<li style="margin: 5px 0 10px 0"><?= Html::img(url('store/_a/' . $row['user_photo'])); ?></li>
					<?php endif; ?>

					<li><label>Rejestracja:</label> <?= User::formatDate($row['user_regdate']); ?></li>
					<li><label>Ostatnio:</label> <?= User::formatDate($row['user_lastvisit']); ?></li>

					<?php if ($row['user_allow_count'] && User::data('allow_count')) : ?>
					<li><label>Ilość postów:</label> <a rel="nofollow" title="Znajdź posty użytkownika" href="<?= url(Path::connector('forum')) . '?view=user&user=' . $row['user_id'] . '#user'; ?>"><?= number_format($row['user_post']); ?></a></li>
					<?php endif; ?>

				</ul>
				<?php endif; ?>

			</td>
			<td class="post-body">
				<?= empty($row['content']) ? $row['text_content'] : $row['content']; ?>
			</td>
		</tr>
		<tr>
			<td class="post-bottom"></td>
			<td class="post-bottom">
				<?php if ($postData['post_text'] != $row['text_id'] && $isEditable) : ?>
				<a title="Cofnij do tej wersji postu" href="<?= url('@forum/Post/Revert/' . $postData['post_id'] . '?id=' . $row['text_id']); ?>" class="revert-button post-button">Cofnij do tej wersji postu</a>
				<?php endif; ?>

				<a rel="nofollow" title="Zobacz wersję źródłową" href="<?= url('@forum/Post/Source/' . $postData['post_id'] . '?id=' . $row['text_id']); ?>" class="source-button post-button">Zobacz wersję źródłową</a>
			</td>
		</tr>
		<tr>
			<td colspan="3" class="post-end"></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>