<style type="text/css">

@import url('<?= url('plugin/comment/template/css/comment.css'); ?>');

</style>

<div id="box-comment">
	<?php if (@$comment) : ?>
	<h3><?= count($comment); ?> <?= Declination::__(count($comment), array('komentarz', 'komentarze', 'komentarzy')); ?></h3>

	<?php foreach ($comment as $row) : ?>
	<?php require('_partialComment.php'); ?>
	<?php endforeach; ?>
	<?php endif; ?>

	<?php if ($enableAnonymous == 2) : ?>
	<!-- komentarze wyłączone -->
	<?php else : ?>
	<?php if (User::$id > User::ANONYMOUS || (User::$id == User::ANONYMOUS && $enableAnonymous == 1)) : ?>

	<h3>Dodaj komentarz</h3>

	<script type="text/javascript" src="<?= url('plugin/comment/template/js/comment.js'); ?>"></script>
	<script type="text/javascript" src="<?= url('template/js/window.js'); ?>"></script>

	<div class="photo" style="padding-top: 5px">

		<div>
			<?php if (User::data('photo')) : ?>
			<img src="<?= url('store/_a/' . User::data('photo')); ?>" alt="Twoje zdjęcie" width="70" height="60" />
			<?php else : ?>
			<img src="<?= url('plugin/comment/template/img/avatar.jpg'); ?>" alt="Brak avatara" width="70" height="60" />
			<?php endif; ?>
		</div>
	</div>

	<?= Form::open(url('Comment/Submit/' . @$comment_id), array('id' => 'comment-form', 'method' => 'post')); ?>
		<fieldset>
			<?= Form::hidden('moduleId', $moduleId); ?>
			<?= Form::hidden('pageId', $pageId); ?>

			<ol>
				<li>
					<?= Form::textarea('content', '', array('cols' => 110, 'rows' => 5)); ?>
				</li>
				<?php if (User::$id == User::ANONYMOUS && $enableAnonymous == 1) : ?>
				<li>
					<label style="text-align: left; width: 50px">Autor <em>*</em></label>
					<?= Form::input('username', ''); ?>
				</li>
				<?php endif; ?>
				<?php if (User::$id > User::ANONYMOUS) : ?>
				<li>
					<?= Form::checkbox('notify', 1, $isWatched); ?>
					<span>
						<?php if ($isWatched) : ?>
						Informowanie o nowych komentarzach na tej stronie
						<?php else : ?>
						Zaznacz, aby obserwować komentarze na tej stronie
						<?php endif; ?>
					</span>
				</li>
				<?php endif; ?>
				<li>
					<?= Form::submit('', 'Zapisz komentarz'); ?>
				</li>
			</ol>
		</fieldset>
	<?= Form::close(); ?>

	<?php endif; ?>
	<?php endif; ?>
</div>

