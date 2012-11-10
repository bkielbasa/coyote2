<div id="comment-<?= $row['comment_id']; ?>">
	<div class="photo">

		<div>
			<?php if (!empty($row['user_photo'])) : ?>
			<img src="<?= url('store/_a/' . $row['user_photo']); ?>" alt="Avatar: <?= $row['user_name']; ?>" width="70" height="60" />
			<?php else : ?>
			<img src="<?= url('plugin/comment/template/img/avatar.jpg'); ?>" alt="Brak avatara" width="70" height="60" />
			<?php endif; ?>
		</div>

	</div>
	<span>
		<?php if (Auth::get('a_')) : ?>
		<a title="Załóż bana dla tego użytkownika" href="<?= url('adm/Ban/Submit?id=' . $row['comment_user'] . '&amp;ip=' . $row['comment_ip']); ?>" class="comment-block"></a>
		<?php endif; ?>
		<?php if ((User::$id > User::ANONYMOUS && User::$id == $row['comment_user']) || Auth::get('c_edit')) : ?>
		<a title="Edytuj ten komentarz" href="<?= url('Comment/Edit/' . $row['comment_id']); ?>" class="comment-edit"></a>
		<?php endif; ?>
		<?php if ((User::$id > User::ANONYMOUS && User::$id == $row['comment_user'] && $enableDelete == 1) || ($enableDelete == 1 && Auth::get('c_delete'))) : ?>
		<a title="Usuń ten komentarz" href="<?= url('Comment/Delete/' . $row['comment_id']); ?>" class="comment-delete"></a>
		<?php endif; ?>

		Napisany <?= User::formatDate($row['comment_time']); ?> przez <?= $row['user_id'] > User::ANONYMOUS ? Html::a(url('@profile?id=' . $row['user_id']), $row['user_name']) : $row['comment_username']; ?>
	</span>

	<p>
		<?= $row['comment_content']; ?>
	</p>

</div>