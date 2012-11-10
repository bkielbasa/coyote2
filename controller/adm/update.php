<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Kontroler sluzy do automatycznej aktualizacji 
 */
class Update_Controller extends Adm
{
	const UPDATE_URL = 'http://4programmers.net/trac/coyote/browser/trunk/4programmers/config/config.xml.default?format=txt';
	const SCHEMA_URL = 'http://4programmers.net/trac/coyote/wiki/DB/%s?format=txt';
	
	public function db($db = 0)
	{
		if ($db = intval($db))
		{
			return $this->updateDb($db);
		}
		$config = @file_get_contents(self::UPDATE_URL);
		if (!$config)
		{
			throw new AcpErrorException('Nie można połączyć się z serwerem. Prosimy spróbować później!');
		}
		$xml = simplexml_load_string($config);
		$return = array();

		if (($update_db = (int)$xml->build_date) > Config::getItem('build_date'))
		{
			$update_str = preg_replace('#^(\d{4})(\d{2})(\d{2})$#', '$3-$2-$1', $update_db);
			$info = 'Dostępna jest nowsza wersja struktury bazy danych z dnia ' . html_a('http://4programmers.net/trac/coyote/wiki/DB/' . $update_db, $update_str) . '!';

			if (!Box::confirm('Akualizacja bazy danych', $info . '<br />System spróbuje dokonać aktualizacji struktury bazy danych. <br /><br/ > <strong>UWAGA!</strong> Zalecane jest wykonanie kopii zapasowej bazy. Czy chcesz kontynuować?', 
				'', url('adm/Update/DB/' . $update_db), 'adm/confirmation_box'))
			{
				Box::information('Anulowano', 'Operacja aktualizacji została anulowana.', url('adm/Admin'), 'adm/information_box');
				exit;
			}
		}
		else
		{
			Box::information('Anulowano', 'Posiadasz aktualną strukturę bazy danych. Aktualizacja nie jest konieczna.', url('adm/Admin'), 'adm/information_box');
		}
	}

	private function updateDb($schema)
	{ 
		if (!is_writeable('config/config.xml'))
		{
			throw new AcpErrorException('Nie można zapisać konfiguracji do pliku XML. Proszę zmienić prawa dostępu do pliku <i>/config/config.xml</i> na <strong>0666</strong> i spróbować ponownie!');
		}
		if (!is_writeable('config/'))
		{
			throw new AcpErrorException('Katalog /config nie posiada praw do zapisu. Proszę zmienić prawa do zapisu do tego katalogu na <strong>0777</strong> i spróbować ponownie!');
		}

		$content = @file_get_contents(sprintf(self::SCHEMA_URL, $schema));
		if (!$content)
		{
			throw new AcpErrorException('Nie można połączyć się z serwerem. Prosimy spróbować później!');
		}
		$content = str_replace("\r\n", "\n", $content);

		preg_match("#\{\{\{\n\#\!sql(.*?)\}\}\}#is", $content, $matches);
		if (!@$matches[1])
		{
			throw new AcpErrorException('Nie można dokonać automatycznej aktualizacji. Brak instrukcji SQL do wykonania!');
		}
		file_put_contents('config/' . $schema . '.sql', $matches[1]);
		$this->load->helper('sql');

		try
		{
			import_sql('config/' . $schema . '.sql');
		}
		catch (Exception $e)
		{
			$this->db->rollback();

			Log::add($e->getMessage(), E_ERROR);
			throw new AcpErrorException('Aktualizacja nie powiodła się. Dokonaj aktualizacji ręcznie. <br />Błąd: <code>' . $e->getMessage() . '</code>');
		}

		$xml = simplexml_load_file('config/config.xml');
		$xml->build_date = (int)$schema;
		file_put_contents('config/config.xml', $xml->asXml(), LOCK_EX);

		@unlink('config/' . $schema . '.sql');

		Box::information('Aktualizacja dokonana!', 'Aktualizacja została przeprowadzona prawidłowo!', url('adm/Admin'), 'adm/information_box');
		exit;
	}
}
?>