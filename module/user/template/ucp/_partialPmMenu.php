<ul class="box-menu">
		<li <?= $folder == Pm_Model::INBOX ? 'class="focus"' : ''; ?>><a href="<?= url('@user?controller=Pm&folder=' . Pm_Model::INBOX); ?>">Wiadomości (<?= User::data('pm_unread'); ?>/<?= $count[Pm_Model::INBOX]; ?>)</a></li>
		<li <?= $folder == -1 ? 'class="focus"' : ''; ?>><a href="<?= url('@user?controller=Pm&action=Submit'); ?>">Napisz wiadomość</a></li>
	</ul>