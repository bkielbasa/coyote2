
<?php if (!empty($user_photo)) : ?>
<div class="photo"><img width="120" src="<?= Url::site(); ?>store/_a/<?= $user_photo; ?>" /></div>
<?php endif; ?>

<fieldset>
	<ol>
		<li>
			<label>Nazwa użytkownika</label>
			<?= $user_name; ?>
		</li>
		<li>
			<label>Data rejestracji</label>
			<?= User::formatDate($user_regdate); ?>
		</li>
		<li>
			<label>Ostatnia wizyta</label>
			<?= User::formatDate($user_lastvisit); ?>
		</li>
		<li>
			<label>Ilość wizyt</label>
			<?= $user_visits; ?>
		</li>
		<?php foreach ($fieldList as $fieldName => $fieldText) : ?>
		<li>
			<label><?= $fieldText; ?></label>
			<?= $$fieldName; ?>
		</li>
		<?php endforeach; ?>
		<li>
			<label></label>
			<?= Html::a(url('@user?controller=Pm&action=Submit?user=' . $user_id), 'Napisz wiadomość', array('title' => 'Napisz wiadomość do tego użytkownika', 'id' => 'user-send-message-button')); ?>
		</li>
		<li>
			<label></label>
			<?= Html::a(url('@forum?view=user&user=' . $user_id) . '#user', 'Wyświetl posty tego użytkownika', array('title' => 'Znajdź wszystkie posty tego użytkownika', 'id' => 'user-find-post-button')); ?>
		</li>
		<?php if (Auth::get('a_')) : ?>
		<li>
			<label></label>
			<?= Html::a(url('@adm/User/Submit/' . $user_id), 'Panel użytkownika', array('Przejdź do edycji użytkownika', 'id' => 'user-edit-profile-button'))?>
		</li>
		<li>
			<label></label>
			<?= Html::a(url('@adm/Ban/Submit?id=' . $user_id . '&ip=' . $user_ip), 'Blokada użytkownika', array('id' => 'user-ban-button', 'title' => 'Załóż bana na tego użytkownika'))?>
		</li>
		<?php endif; ?>
	</ol>
</fieldset>
