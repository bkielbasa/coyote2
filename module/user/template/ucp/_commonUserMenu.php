<li><a href="<?= url('@user'); ?>#user">Konfiguracja profilu</a></li>
<li><a href="<?= url('@user'); ?>#info">Informacje o profilu</a></li>
<li><a href="<?= url('@user?controller=Notify'); ?>">Powiadomienia</a></li>
<li><a href="<?= url('@user?controller=Pm'); ?>">Wiadomości prywatne (<?= User::data('pm_unread'); ?>/<?= User::data('pm'); ?>)</a></li>
<li><a href="<?= url('@user?controller=Watch'); ?>">Obserwowane</a></li>		
<?php include('_partialUserMenu.php'); ?>