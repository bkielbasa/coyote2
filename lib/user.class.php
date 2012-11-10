<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa wyjatku stopujaca dalsze dzialanie witryny ze wzgledu na blokade (ban)
 */
class BanException extends Exception
{
	private $ban_id;

	function __construct($ban_id, $message = '', $code = 0)
	{
		// przekazanie ID blokady do prywatnego pola
		$this->ban_id = $ban_id;
		parent::__construct($message, $code);
	}

	/**
	 * Metoda wyswietla komunikat blokady wraz z formularzem, dzieki ktoremu
	 * mozliwe jest skontaktowanie sie z osoba, ktora nalozyla blokade
	 */
	public function displayMessage()
	{
		$core = &Core::getInstance();
		$ban = &$core->load->model('ban');

		// odczytanie ID bana
		$query = $ban->find($this->ban_id);
		// pobranie informacji zwiazanych z blokada
		extract($data = $query->fetchAssoc(DB::FETCH_ARRAY));

		if ($ban_expire && time() > $ban_expire)
		{
			$ban->delete('ban_id = ' . $this->ban_id);

			Box::information('Dostęp odblokowany!', __('Upłynął termin blokady. Możesz teraz bez przeszkód korzystać z serwisu.'), Url::base());
			exit;
		}

		try
		{
			if ($core->input->getMethod() == Input::POST)
			{
				if ($ban_flood && time() > $ban_flood)
				{
					throw new Exception('You already sent an email!');
				}
				$core->load->library('email');

				if (!$u1_email)
				{
					Load::loadFile('lib/validate.class.php', false);
					$validate = new Validate_Email;

					if ($validate->isValid($core->input->post->email) !== true)
					{
						throw new Exception('You\'r e-mail address is wrong!');
					}
				}

				$core->email->to($u2_email);
				$core->email->from(!$u1_email ? $core->input->post('email') : $u1_email);
				$core->email->subject(__('Wiadomość od zablokowanego użytkownika'));
				$core->email->message($core->input->post('message') . "\n\n\nBan ID: {$this->ban_id}\nIP: " . $core->input->getIp());

				$core->email->send();

				$ban->update(array('ban_flood' => time() + 3600), "ban_id = {$this->ban_id}");


				echo 'Message was sent!';
				exit;
			}
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}

		$view = $core->load->view('error/BanException', (array)$data);
		$view->expired = $ban_expire ? date('d-m-Y H:i', $ban_expire) : 'Never';

		echo $view;
	}
}

/**
 * Klasa obslugi uzytkownika
 */
final class User
{
	/**
	 * ID anonimowego uzytkownika (ID pola user_id z tabeli user)
	 */
	const ANONYMOUS = 0;

	/**
	 * ID sesji. ID nadawane jest na czas przebywania w serwisie
	 */
	public static $sid;
	/**
	 * IP uzytkownika
	 */
	public static $ip;
	/**
	 * ID uzytkownika (odczytywane z tabeli user)
	 */
	public static $id = self::ANONYMOUS;

	/**
	 * URL do strony na ktorej aktualnie przebywa user
	 */
	private static $page;
	/**
	 * Referencja do jadra systemu
	 */
	private static $core;
	/**
	 * Referencja do modelu Session
	 */
	private static $session;
	/**
	 * Tablica danych z ciastka _data
	 */
	private static $sessiondata = array();
	/**
	 * Tablica danych uzytkownika (odczytana z bazy danych)
	 */
	public static $data = array();
	/**
	 * Ewentualna nazwa robota sieciowego
	 */
	private static $robot;
	/**
	 * User-agent
	 */
	private static $browser;

	/**
	 * Glowna metoda ladujaca dane uzytkownika
	 */
	public static function load()
	{
		self::$core = &Core::getInstance();
		Log::add('User class initialized', E_DEBUG);
		$time = time();

		self::$session = self::$core->load->model('session');
		self::$sid = self::$core->input->cookie('sid');

		if (self::$sid)
		{
			if (strlen(self::$sid) != 32)
			{
				Log::add('Invalid session key!', E_ERROR);
				self::$sid = '';
			}
		}
		self::$ip = self::$core->input->getIp();
		self::$page = self::$core->input->getPage();
		self::$browser = self::$core->input->getUserAgent();

		self::$robot = (string) self::$core->input->isRobot();

		// usuwanie z adresu nazwy katalogu, w ktorym znajduje sie projekt
		self::$page = preg_replace('#^' . self::$core->input->getScriptPath() . '#', '', self::$page);
		// usuniecie z adresu nazwy pliku front controllera jezeli taki nie ma byc uzywany w URL'ach
		if (!Config::getItem('core.frontController'))
		{
			self::$page = str_replace('index.php', '', self::$page);
		}

		/*
		 * Aby uznac, ze user jest zalogowany w serwisie, potrzebujemy nastepujacych rzeczy:
		 * - ciastka sid z 32 znakowym ID sesji
		 * - ciastka data z serializowana tablica ID usera oraz kluczem (sprawdzanym przy porownywaniu hasla)
		 * - rekord w tabeli session (rekord zawiera m.in. id sesji - sid)
		 *
		 * Jezeli spelnione sa dwa pierwsze warunki, user zostaje zalogowany automatycznie (jezeli do tej pory nie jest),
		 * bo to oznacza, ze tak postanowil logujac sie i zaznaczjac "Loguj przy nastepnej wizycie"
		 */
		if (self::$core->input->cookie('data'))
		{
			self::$sessiondata = @unserialize(stripslashes(User::decrypt(self::$core->input->cookie('data'))));

			// ID usera pobrane z cookie. Na tym etapie nie mamy pewnosci, ze ID jest prawidlowe
			// jezeli user posiada ciastko data, oznacza, to ze user jest zalogowany. Nalezy to
			// potwierdzic w dalszych krokach
			self::$id = (int) self::$sessiondata['user_id'];
		}
		$config = array();

		$query = self::$core->db->select()->get('config');
		foreach ($query as $row)
		{
			Config::setItem($row['config_name'], $row['config_value']);
		}

		/*
		 * GC zostaje wywolany tylko jezeli system jest obslugiwany przez
		 * MySQL w wersji nizszej niz 5.1. W wersji 5.1. uruchamiany jest event
		 */
		if (Config::getItem('databases.default.event-scheduler') != '1')
		{
			/* metode gc() wywolujemy jedynie co jakis czas */
			if ($time > Config::getItem('session.last_gc') + Config::getItem('session.gc'))
			{
				self::gc();
			}
		}
		/*
		 * Podstawowy warunek. Sprawdza czy jest ID sesji. Jezeli brak, to znaczy ze:
		 * - uzytkownik wchodzi na strone po raz pierwszy
		 * - zamknal przegladarke, ID (ciastko) wygaslo i wchodzi ponownie
		 * - jest botem lub nie akceptuje cookies
		 * - umyslnie usunal sid, lecz istnieje u niego ciastko "data"
		 *
		 * Moze zaistniec sytuacja, w ktorej user nie ma SID'a poniewaz zamknal przegladarke, a ta
		 * usunela ciastko. LECZ sesja nadal figuruje w bazie danych, bo GC nie zdarzyl usunac
		 * sesji z tabeli
		 */
		if (!self::$sid)
		{
			/*
			 * sprawdzenie, czy w zaden uzytkownik online nie ma tego IP.
			 */
			$query = self::$session->getByIp(self::$ip);

			/*
			 * Jezeli ten warunek zostanie spelniony, mamy do czynienia z botem albo uzytkownikiem
			 * ktorego sesja wygasla lecz mimo wszystko figuruje w bazie danych jako uzytkownik
			 * online (mechanizm GC jeszcze go nie usunal), gdyz np. usunal recznie cookie lub zamknal
			 * przegladarke, ktora usunela mu je automatycznie.
			 *
			 * Albo: uzytkownik wchodzi z innej przegladarki z tego samego ip
			 */
			if (count($query))
			{
				/*
				 * Odczytujemy rekord z bazy przypisany temu ID. W dalszym kroku decydujemy co z nim zrobic
				 *
				 * Moze to oznacac, ze user jest botem, lub wchodzi z innej przegladarki, z tego samego IP.
				 * Moze to rowniez oznaczac, ze userzy korzystaja z tej samej sieci wewnetrznej
				 *
				 * Tak czy inaczej nie chcemy duplikowac sesji w bazie danych, z tego wzgledu, ze user
				 * nie akceptuje ciasteczek
				 */
				$result = $query->fetchAssoc();

				/*
				 * Jezeli user jest botem (mozemy to zalozyc, ale nie jestesmy pewni na 100% ze wzgledu na mozliwosc
				 * podszywania sie pod boty przez normalnych uzytkownikow) a w dodatku Id uzytkownika z odczytanego
				 * rekordu, wskazuje na anonima, spokojnie przypisujemy userowi SID odczytany z bazy danych
				 */
				if (self::$robot && $result['session_user_id'] == User::ANONYMOUS && User::$id == User::ANONYMOUS)
				{
					self::$sid = $result['session_id'];
				}
				/*
				 * Jezeli odczytany rekord wskazuje na anonima, a w dodatku user-agent jest taki sam,
				 * uznajemy, ze to ten sam user, nalezy mu przywrocic SID
				 */
				elseif ($result['session_user_id'] == User::ANONYMOUS && User::$id == User::ANONYMOUS && $result['session_browser'] == self::$browser)
				{
					self::$sid = $result['session_id'];
				}
				/*
				 * Jezeli obecne jest ciastko "data" (ID uesra nie != 0), mozemy zalozyc, ze cos stalo sie z ciastekiem SID.
				 * Jezeli dodatkowo system stwierdzi, ze taki user jest obecnie online, a w dodatku - obydwoje
				 * korzystaja z tej samej przegladarki, mozemy przyjac zalozenie, ze jest to ten sam czlowiek.
				 * Jednak jest to tylko zalozenie, ktore zostanie zweryfikowane podczas porownywania klucza
				 * z haslem
				 */
				elseif (User::$id > User::ANONYMOUS && $result['session_user_id'] == User::$id && ($result['session_browser'] == self::$browser))
				{
					self::$sid = $result['session_id'];
				}
				/*
				 * Jezeli zaden z powyzszych warunkow nie zostal spelniony - generujemy dla usera nowy SID.
				 * Byc moze user wchodzi z innej przegladarki. Bedzie to skutkowac tym, ze jego bedzie
				 * podwojnie zalogowany... trudno
				 */
				else
				{
					self::$sid = md5(uniqid(self::$ip));
				}

				self::$core->output->setCookie('sid', self::$sid, $time + Time::YEAR);

				/*
				 * Uaktualnienie informacji w tabeli bazie danych. W metodzie update() nastepuje rowniez
				 * sprawdzenie hasla danego uzytkownika
				 */
				self::update();
			}
			/*
			 * Uzytkownik nie figuruje w bazie jako uzytkownik online. Sesja definitywnie wygasla
			 */
			else
			{
				/*
				 * Proba zapisania ciastka z ID sesji oraz utworzenie rekordu w tabeli.
				 * Uzytkownik od teraz jest online
				 */
				self::create();
			}
		}
		/*
		 * Ciastko z session-id istnieje. Moze to oznaczac, ze:
		 *
		 * - uzytkownik byl tu juz wczesniej, korzystajac z tej przegladarki (to na pewno)
		 * - moze to byc kolejne odswiezenie strony (uzytkownik jest zalogowany)
		 * - uzytkownik nie jest zalogowany ale byl tu juz wczesniej - tym swiadczy SID
		 */
		else
		{
			self::update();
		}
	}

	/**
	 * Utworzenie nowej sesji uzytkownika w bazie danych. Innymi slowy ustawienie iz, user jest online
	 */
	private static function create()
	{
		/*
		 * To zapytanie pobiera informacje o uzytkowniku z bazy danych. Nawet jezeli uzytkownikiem
		 * jest anonim, to rowniez, w bazie danych znajduja sie informacje odnosnie tego rekordu
		 */
		$query = self::$session->getByUserId(self::$id);

		/*
		 * Ten warunek zawsze powinien zakonczyc sie pozytywnie. Nawet w przypadku anonimow.
		 * Jezeli nie, nastepuje proba wlamu na nieistniejace ID usera
		 */
		if (count($query))
		{
			/*
			 * Jezeli self::$id > 0 oznaca, to, ze uzytkownik posiada ciastko "data". Oznacza, ze byl tu
			 * wczesniej oraz ma wlaczona opcje automatycznego logowania po kolejnej wizycie.
			 * Nalezy zweryfikowac jego tozsamosc porownujac haslo z bazy danych, z kluczem z cookie
			 */
			if (self::$id > self::ANONYMOUS)
			{
				if ($query->fetchField('user_ip_access'))
				{
					if (!self::isIpAccess($query->fetchField('user_ip_access')))
					{
						self::$core->load->model('user')->logout();

						self::$id = User::ANONYMOUS;
						$query = self::$session->getByUserId(self::$id); // ladowanie danych uzytkownika anonimowego
					}
				}

				/*
				 * Haslo nie zgadza sie z kluczem. Moze to byc spowodowane:
				 * - proba wlamu
				 * - nieaktualne ciastko z kluczem
				 * - user z poziomu innej przegladarki zmienil haslo do swojego profilu, a na tej,
				 * przegladarce pozostal stary klucz
				 */
				if (strcmp(md5($query->fetchField('user_password')), self::$sessiondata['key']) !== 0)
				{
					self::$core->load->model('user')->logout();

					self::$id = User::ANONYMOUS;
					$query = self::$session->getByUserId(self::$id); // ladowanie danych uzytkownika anonimowego
				}

				if (!$query->fetchField('user_active'))
				{
					self::$core->load->model('user')->logout();
					throw new UserErrorException('Zostałeś wylogowany w systemu. Twoje konto zostało dezaktywowane lub niepotwierdziłeś rejestracji poprzez link aktywacyjny!');
				}
			}

			// wszystko ok. przypisujemy dane usera do tablicy
			self::$data = $query->rewind()->fetchAssoc();
		}
		else
		{
			self::$core->output->setCookie('data', '', time() - 300000);
			self::$id = self::ANONYMOUS;

			Log::add('Hacking attempt. User ID: ' . self::$id . ' does not exists', E_ERROR);
		}

		$time = time();

		if ($robot = self::$core->input->isRobot())
		{
			// doatkowe dzialania wprzypadku gdy jest to bot...
			// przypisanie nazwy dla robota
			self::$robot = $robot;
		}

		// generujemy session-id tylko jezeli jest puste
		if (!self::$sid)
		{
			self::$sid = md5(uniqid(self::$ip));
		}
		self::$core->output->setCookie('sid', self::$sid, $time + Time::YEAR);

		$ban = &self::$core->load->model('ban');

		// sprawdzenie, czy uzytkownik jest zbanowany
		$query = $ban->getBanId(self::$id, self::$ip, (self::$id > self::ANONYMOUS ? self::$data['user_email'] : ''));
		try
		{
			if (count($query))
			{
				throw new BanException($query->fetchField('ban_id'));
			}
		}
		catch (BanException $e)
		{
			$e->displayMessage();
			exit;
		}

		try
		{
			// uaktualnienie (lub dodanie) danych sesji w bazie danych
			self::$session->update(self::$sid, self::$id, self::$ip, self::$page, self::$robot, self::$browser);
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	 * Uaktualnienie sesji w bazie danych
	 */
	private static function update()
	{
		$time = time();

		/*
		 * Jezeli dysponujemy ciastkiem z ID sesji, sprawdzamy, czy user jest aktualnie online.
		 */
		$query = self::$session->getBySessionId(self::$sid);

		/*
		 * Ta instrukcja generalnie NIE powinna zostac spelniona. Taki warunek zostanie spelniony
		 * tylko wowczas, gdy istnieje ciastko aktualnej sesji (sid), a w bazie danych garbace
		 * collector juz dana sesje "sprzatnal". Wowczas nalezy od nowa utworzyc sesje dla usera
		 */
		if (!count($query))
		{
			return self::create();
		}

		if (self::$id > self::ANONYMOUS)
		{
			if ($query->fetchField('user_ip_access'))
			{
				if (!self::isIpAccess($query->fetchField('user_ip_access')))
				{
					self::$core->load->model('user')->logout();

					self::$id = User::ANONYMOUS;
					$query = self::$session->getByUserId(self::$id); // ladowanie danych uzytkownika anonimowego
				}
			}

			/*
			 * Za kazdym razem, musimy potwierdzic autentycznosc usera. W tym celu sprawdzamy
			 * jego haslo, porownujac z kluczem zapisanym w cookies
			 */
			if (strcmp(md5($query->fetchField('user_password')), self::$sessiondata['key']) !== 0)
			{
				self::$core->load->model('user')->logout();

				self::$id = User::ANONYMOUS;
				$query = self::$session->getByUserId(self::$id); // ladowanie danych uzytkownika anonimowego
			}

			if (!$query->fetchField('user_active'))
			{
				self::$core->load->model('user')->logout();
				throw new UserErrorException('Zostałeś wylogowany w systemu. Twoje konto zostało dezaktywowane lub niepotwierdziłeś rejestracji poprzez link aktywacyjny!');
			}
		}
		self::$data = $query->rewind()->fetchAssoc();

		if (empty(self::$data['session_stop']) || $time - self::$data['session_stop'] > 60)
		{
			self::$session->update(self::$sid, self::$id, self::$ip, self::$page, self::$robot, self::$browser);
		}
	}

	private static function gc()
	{
		self::$session->gc();
	}

	/**
	 * Zwraca wartosc danej komorki ustawien uzytkownika.
	 * Klucz przyjmuje rowniez skrocona wersje bez prefixu:
	 * @example
	 * <code>
	 * echo User::data('name');
	 * echo User::data('user_name');
	 * </code>
	 * @param string $key
	 * @return mixed
	 */
	public static function data($key)
	{
		if (isset(self::$data['user_' . $key]))
		{
			return self::$data['user_' . $key];
		}
		return (isset(self::$data[$key]) ? self::$data[$key] : false);
	}

	/**
	 * Formatuje date i czas na podstawie podanych parametrow
	 * @param string $datetime Czas w formacie UTC
	 * @param string|bool $format Format wyswietlania daty i czasu (zgodnie z funkcja strftime())
	 * @param bool Wartosc TRUE spowoduje, iz obliczona bedzie roznica czasu i zamiast daty
	 * bedzie wyswietlana roznica - np. 30 sekund temu, 31 minut temu
	 */
	public static function formatDate($datetime, $format = false, $formatSpan = true)
	{
		/* jezeli nie zostal podany format, uzywamy tego z wlasciwosci date_format */
		$format = (!$format ? self::$data['user_dateformat'] : $format);

		if ($formatSpan)
		{
			/**
			 * Jezeli roznica w minutach jest mniejsza niz godzina,
			 * obliczamy roznice w minutach/sekundach
			 */
			if (Time::diffMinute($datetime) <= 60)
			{
				if (Time::diffSecond($datetime) >= 60)
				{
					return Time::diffMinute($datetime) . ' ' . Declination::__(Time::diffMinute($datetime), array('minuta', 'minuty', 'minut')) . ' temu';
				}
				else
				{
					return Time::diffSecond($datetime) . ' ' . Declination::__(Time::diffSecond($datetime), array('sekunda', 'sekundy', 'sekund')) . ' temu';
				}
			}
			elseif (Time::diffMinute($datetime) < 100)
			{
				return '1 godz. temu';
			}
			else
			{
				$currValue = date('Ymd', time());
				$spanValue = date('Ymd', $datetime);

				if ($spanValue == $currValue)
				{
					return __('today') . ', ' . date('H:i', $datetime);
				}
				elseif ($spanValue == $currValue -1)
				{
					return __('yesterday') . ', ' . date('H:i', $datetime);
				}
				else
				{
					return Time::format($datetime, $format);
				}
			}
		}
		else
		{
			return Time::format($datetime, $format);
		}
	}

	/**
	 * Metoda o identycznym dzialaniu co metoda formatDate()
	 * @param $datetime timestamp
	 * @see User::formatDate
	 */
	public static function date($datetime)
	{
		return self::formatDate($datetime);
	}

	/**
	 * Zwraca sciezke strony na ktorej przebywa uzytkownik
	 * @return string
	 */
	public static function getPage()
	{
		return self::$page;
	}

	public static function getUsersSession($page)
	{
		$page = str_replace('*', '%', $page);
		$query = self::$core->db->select()
								->from('session')
								->leftJoin('user', 'user_id = session_user_id')
								->where('session_page LIKE ?', '/' . $page)
								->get();

		return $query->fetchAll();
	}

	public static function encrypt($data)
	{
		if (Config::getItem('session.crypt') && extension_loaded('mcrypt'))
		{
			$iv_size = mcrypt_get_iv_size(MCRYPT_XTEA, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

			$data = mcrypt_encrypt(MCRYPT_XTEA, Config::getItem('session.crypt'), $data, MCRYPT_MODE_ECB, $iv);
			$data = base64_encode($data);
		}

		return $data;
	}

	public static function decrypt($data)
	{
		if (Config::getItem('session.crypt') && extension_loaded('mcrypt'))
		{
			$iv_size = mcrypt_get_iv_size(MCRYPT_XTEA, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

			$data = mcrypt_decrypt(MCRYPT_XTEA, Config::getItem('session.crypt'), base64_decode($data), MCRYPT_MODE_ECB, $iv);
		}

		return $data;
	}

	public static function isIpAccess($ipAccess = null)
	{
		$ip = $ipAccess ? $ipAccess : self::data('ip_access');

		if (!$ip)
		{
			return true;
		}

		$hasAccess = false;
		$ip = explode('.', $ip);

		for ($i = 0; $i < count($ip); $i += 4)
		{
			if (preg_match('#^' . str_replace('*', '.*', str_replace('.', '\.', implode('.', array_slice($ip, $i, 4)))) . '$#', self::$ip))
			{
				$hasAccess = true;
				break;
			}
		}

		return $hasAccess;
	}
}

?>