<h1>Instalacja zakończona</h1>

<p>Gratulacje! Projekt został zainstalowany. Po kliknięciu przycisku OK, zostaniesz przeniesiony do panelu
administracyjnego. <br /><strong>Pamiętaj, aby usunąć z serwera folder /module/install.</strong>.</p>

<?= Form::open(Url::__('adm'), array('method' => 'post')); ?>
<?= Form::submit('', 'Ok, instalacja zakończona'); ?>
<?= Form::close(); ?>
