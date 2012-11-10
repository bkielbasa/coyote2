SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

TRUNCATE `adm_menu`;
ALTER TABLE `adm_menu` AUTO_INCREMENT = 1;

INSERT INTO `adm_menu` (`menu_id`, `menu_parent`, `menu_text`, `menu_auth`, `menu_controller`, `menu_action`, `menu_order`) VALUES
(1, 0, 'Strony', 'a_', 'page', 'main', 1),
(2, 1, 'Raport', 'a_', 'report', 'main', 1),
(3, 0, 'Zasoby', 'a_', 'media', 'main', 2),
(4, 0, 'Konfiguracja', 'a_', 'admin', 'main', 3),
(5, 0, 'Użytkownicy', 'a_', 'user', 'main', 4),
(6, 0, 'Uprawnienia', 'a_auth', 'auth', 'main', 5),
(7, 0, 'Moduły', 'a_module', 'module', 'main', 6),
(8, 0, 'Wtyczki', 'a_', 'plugin', 'main', 7),
(9, 3, 'Regiony', 'a_', 'region', 'main', 1),
(10, 3, 'Bloki', 'a_', 'block', 'main', 2),
(11, 3, 'Załączniki', 'a_', 'attachment', 'main', 3),
(12, 3, 'Szablony', 'a_', 'template', 'main', 4),
(13, 3, 'Szablony e-mail', 'a_', 'email', 'main', 5),
(14, 4, 'Menu PA', 'a_', 'admin', 'adm', 1),
(15, 4, 'Reguły routingu', 'a_', 'route', 'main', 2),
(16, 4, 'Triggery', 'a_', 'trigger', 'main', 3),
(17, 4, 'Cache', 'a_', 'admin', 'cache', 4),
(18, 4, 'Łączniki', 'a_', 'connector', 'main', 5),
(19, 4, 'Parsery', 'a_', 'parser', 'main', 6),
(20, 4, 'Skrawki kodu', 'a_', 'snippet', 'main', 7),
(21, 4, 'Status systemu', 'a_', 'status', 'main', 8),
(22, 4, 'PHP Info', 'a_', 'phpinfo', 'main', 9),
(23, 4, 'Cenzura', 'a_', 'censore', 'main', 10),
(24, 4, 'Zadania', 'a_', 'scheduler', 'main', 11),
(25, 4, 'Powiadomienia', 'a_', 'notify', 'main', 12),
(26, 5, 'Dodaj użytkownika', 'a_', 'user', 'submit', 1),
(27, 5, 'Lista zablokowanych', 'a_', 'ban', 'main', 2),
(28, 5, 'Aktualnie zalogowani', 'a_', 'user', 'logged', 3),
(29, 5, 'Ostatnie wizyty', 'a_', 'user', 'visit', 4),
(30, 5, 'Grupy', 'a_group', 'group', 'main', 5),
(31, 5, 'Napisz wiadomość', 'a_', 'pm', 'main', 6),
(32, 5, 'Pola profilu użytkownika', 'a_', 'profile', 'main', 7),
(33, 6, 'Access Control List', 'a_auth', 'auth', 'acl', 1),
(34, 6, 'Uprawnienia panelu administracyjnego', 'a_auth', 'auth', 'adm', 2),
(35, 7, 'Pola konfiguracji', 'a_module', 'field', 'main', 1),
(36, 7, 'Zainstalowane moduły', 'a_module', 'module', 'module', 2),
(37, 8, 'Zainstalowane wtyczki', 'a_', 'plugin', 'present', 1),
(38, 0, 'Dziennik zdarzeń', 'a_', 'log', 'main', 8),
(39, 0, 'Wyszukiwarka', 'a_', 'search', 'main', 9),
(40, 39, 'Indeksacja', 'a_', 'search', 'index', 10),
(41, 39, 'Top 10', 'a_', 'search', 'top10', 8),
(42, 1, 'Szukaj', 'a_', 'filter', 'main', 2);

INSERT INTO `scheduler` (`scheduler_id`, `scheduler_name`, `scheduler_description`, `scheduler_module`, `scheduler_class`, `scheduler_method`, `scheduler_frequency`, `scheduler_lunch`, `scheduler_enable`) VALUES 
(1, 'indexQueue', 'Usługa indeksacji treści na stronie.', 1, 'search', 'buildIndex', 60, 1283787921, 0);

INSERT INTO `auth_option` (`option_id`, `option_text`, `option_label`, `option_default`) VALUES
(1, 'a_', 'Dostęp do panelu administracyjnego', 0),
(2, 'a_auth', 'Ustawienia dostępu', 0),
(3, 'a_group', 'Edycja grup', 0),
(4, 'a_user', 'Edycja użytkownika', 0),
(5, 'a_module', 'Dostęp do modułów', 0),
(6, 'a_password', 'Zmiana hasła użytkownika', 0),
(7, 'a_config', 'Zapis konfiguracji do pliku XML', 0),
(8, 'a_media', 'Podgląd/Edycja plików', 0),
(9, 'a_plugin', 'Konfiguracja wtyczek', 0),
(10, 'a_template', 'Konfiguracja szablonów', 0);

TRUNCATE `component`;
ALTER TABLE `component` AUTO_INCREMENT = 1;

INSERT INTO `component` (`component_id`, `component_name`, `component_text`) VALUES
(1, 'text', 'Kontrolka tekstowa'),
(2, 'select', 'Lista rozwijalna'),
(3, 'radio', 'Pole jednokrotnego wyboru'),
(4, 'multicheckbox', 'Pola wielokrotnego wyboru'),
(5, 'photo', 'Fotografia'),
(6, 'int', 'Liczba całkowita'),
(7, 'checkbox', 'Checkbox'),
(8, 'textarea', 'Pole wieloliniowe');

INSERT INTO `field` (`field_id`, `field_module`, `field_order`, `field_name`, `field_text`, `field_description`, `field_component`, `field_required`, `field_default`, `field_display`, `field_readonly`, `field_auth`, `field_validator`) VALUES 
(1, 2, 1, 'user_photo', 'Fotografia', '', 5, 0, '', 1, 0, '', 0);

INSERT INTO `field_option` (`option_field`, `option_name`, `option_value`) VALUES
(1, 'background', '#FFF'),
(1, 'height', '90'),
(1, 'maxSize', '5MB'),
(1, 'suffix', 'jpg,jpeg,gif,png'),
(1, 'thumbnailWidth', '120'),
(1, 'width', '120');

ALTER TABLE `user` ADD `user_photo` VARCHAR(20) NOT NULL DEFAULT '';

INSERT INTO `field` (`field_id`, `field_module`, `field_order`, `field_name`, `field_text`, `field_description`, `field_component`, `field_required`, `field_default`, `field_display`, `field_readonly`, `field_auth`, `field_validator`) VALUES
(2, 1, 1, 'enableMenu', 'Włącz wyświetlanie menu', 'Włącza wyświetlanie menu w przypadku, gdy na danej stronie istnieją kategorie potomne', 7, 0, '1', 1, 0, 'a_', NULL);

INSERT INTO `connector` (`connector_id`, `connector_module`, `connector_name`, `connector_class`, `connector_text`, `connector_controller`, `connector_action`, `connector_folder`) VALUES
(1, 1, 'document', 'document', 'Dokument tekstowy', 'document', 'main', ''),
(2, 1, 'accessor', 'accessor', 'Odnośnik', 'accessor', 'main', ''),
(3, 1, 'binary', 'binary', 'Plik binarny', 'binary', 'main', ''),
(4, 2, 'register', 'register', 'Rejestracja', 'register', 'main', ''),
(5, 2, 'login', 'login', 'Logowanie', 'login', 'main', ''),
(6, 1, 'error', 'error', 'Strona błędu', 'error', 'main', ''),
(7, 2, 'logout', 'logout', 'Wylogowanie', 'logout', 'main', ''),
(8, 1, 'homepage', 'homepage', 'Strona główna', 'homepage', 'main', '');

INSERT INTO `content` (`content_id`, `content_type`) VALUES
(1, 'text/html'),
(2, 'text/plain'),
(3, 'text/css'),
(4, 'text/xml'),
(5, 'text/javascript'),
(6, 'application/xhtml+xml');


INSERT INTO `email` (`email_id`, `email_name`, `email_description`, `email_text`, `email_subject`, `email_format`) VALUES
(1, 'userConfirm', 'Informacje o potwierdzeniu adresu e-mail', 'Witaj {name},\r\n\r\nDziękujemy za rejestrację w serwisie {site_title}. Aby potwierdzić autentyczność tego adresu, e-mail, prosimy o kliknięcie w poniższy odnośnik:\r\n\r\n{site_url}User/Confirm/Email?id={id}&key={key}\r\n\r\n-- \r\nPozrawiamy\r\n{site_title}', 'Potwierdzenie adresu e-mail ', 1),
(2, 'userPassword', 'Przypomnienie hasla', 'Witaj {name}\r\n\r\nAby wygenerować nowe hasło do Twojego konta, kliknij poniższy odnośnik:\r\n\r\n{site_url}User/Password/Change?id={id}&key={key}\r\n\r\nJeżeli nie żądałeś wygenerowania hasła, prosimy o zignorowanie tego listu!\r\nŻadanie wysłane z IP: {ip}\r\n\r\n-- \r\nPozdrawiamy\r\n{site_title}', 'Przypomnienie hasla', 1),
(3, 'userRegister', 'E-mail wysłany po udanej rejestracji', 'Witaj {name}\r\n\r\nDziękujemy za rejestrację w serwisie {site_title}.\r\n\r\n-- \r\nPozdrawiamy!', 'Witamy w serwisie!', 1),
(4, 'onPmSubmit', 'Szablon powiadomień o nowej wiadomości prywatnej', 'Witaj {user_name},\r\n\r\n<br /><br />Masz nową wiadomość od użytkownika {sender}:\r\n\r\n<hr />{body}<hr />\r\n\r\n<br /><br />-- <br />Pozdrawiamy,<br />{site_title}', 'Nowa wiadomość od {sender} w serwisie {site_title}', 2);

INSERT INTO `filter` (`filter_id`, `filter_name`, `filter_description`) VALUES
(1, 'htmlspecialchars', 'Konwersja znaków HTML'),
(2, 'strip_tags', 'Usuwanie znaków HTML'),
(3, 'trim', 'Usuwanie białych znaków z początku i końca'),
(4, 'intval', 'Liczby całkowite'),
(5, 'stripurl', 'Usuwa URL (tylko nowym użytkownikom)');

INSERT INTO `group` (`group_id`, `group_name`, `group_desc`, `group_leader`, `group_display`, `group_open`, `group_type`) VALUES
(1, 'ANONYMOUS', '', 0, 0, 0, 0),
(2, 'USER', '', 0, 0, 0, 0);

INSERT INTO `module` (`module_id`, `module_name`, `module_text`, `module_version`, `module_type`) VALUES
(1, 'main', 'Moduł systemowy', '1.0-rc2', 0),
(2, 'user', 'Moduł użytkownika', '1.0-rc2', 0);

INSERT INTO `parser` (`parser_id`, `parser_name`, `parser_text`, `parser_description`, `parser_order`, `parser_default`) VALUES
(1, 'php', 'Parsuj kod PHP (zaawansowane)', 'Po włączeniu tej opcji, kod źródłowy PHP będzie parsowany', 1, 0),
(2, 'html', 'Usuwaj znaczniki HTML', 'Usuwa niebezpieczne znaczniki xHTML, pozostawiając tylko podstawowe', 2, 1),
(3, 'wiki', 'Konwertuj znaczniki Wiki', 'Umożliwia zamianę specjalnych znaczników na kod HTML', 3, 1),
(4, 'highlight', 'Kolorowanie składnii', 'Po włączeniu tej opcji konwertowany będzie znacznik code', 4, 1),
(5, 'url', 'Konwertuj adresy URL oraz e-mail', 'Po włączeniu tej opcji, adresy e-mail oraz URL będą konwertowane do znacznika HTML - a', 5, 1),
(6, 'br', 'Łam linie tekstu', 'Po włączeniu tej opcji, znaki nowej linii będą zamieniane na znacznik br', 6, 1),
(7, 'snippet', 'Konwertuj skrawki kodu', 'Włączenie tej opcji spowoduje, iż system będzie odszukiwał w tekscie deklaracji {{Snippet:Nazwa_klasy}}', 7, 1),
(8, 'censore', 'Cenzura tekstu', 'Włączenie tej opcji spowoduje, iż cenzurę określonych fraz w tekstach', 7, 0);

INSERT INTO `richtext` (`richtext_id`, `richtext_name`, `richtext_path`) VALUES
(1, 'TinyMCE', 'tinymce/tiny_mce,adm/tinymce'),
(2, 'Editarea', 'editarea/edit_area_full,adm/editarea'),
(3, 'WikiEditor', 'jquery,wikieditor/jquery.wikieditor,adm/wikieditor');

INSERT INTO `snippet` (`snippet_id`, `snippet_name`, `snippet_text`, `snippet_class`, `snippet_content`, `snippet_user`, `snippet_time`) VALUES
(1, 'Feed', 'Snippet prezentujący ostatnie zmiany w dokumentach', 'feed', '', 2, 1264799106),
(2, 'Content', 'Generuje spis treści na podstawie dokumentów - dzieci danej kategorii', 'Content', '', 2, 1265732571);

INSERT INTO `search` (`search_id`, `search_name`, `search_class`, `search_enable`, `search_default`) VALUES (1, 'MySQL', 'mysql', 0, 1);

INSERT INTO `trigger` (`trigger_id`, `trigger_name`, `trigger_header`, `trigger_description`, `trigger_type`) VALUES
(1, 'system.onBeforeAction', '', '', 0),
(2, 'system.onBeforeStart', '', '', 0),
(3, 'system.onAfterStart', '', '', 0),
(4, 'system.onDisplay', '', '', 0),
(5, 'system.onShutdown', '', '', 0),
(6, 'application.onRegionDisplay', '', '', 0),
(7, 'application.onTriggerSubmit', '', '', 0),
(8, 'application.onTriggerDelete', '', '', 0),
(9, 'application.onEventSubmit', '', '', 0),
(10, 'application.onEventDelete', '', '', 0),
(11, 'application.onBlockSubmit', '', '', 0),
(12, 'application.onBlockDelete', '', '', 0),
(13, 'application.onRouteSubmit', '', 'Wywoływany w momencie zapisywania konfiguracji routingu.', 0),
(14, 'application.onRouteDelete', '', 'Wywoływany w momencie usuwania reguły routingu.', 0),
(15, 'application.onModuleInstall', '', '', 0),
(16, 'application.onModuleUninstall', '', '', 0),
(17, 'application.onUserSubmit', 'mixed &$data', 'Wywoływany w momencie zapisywania danych użytkownika. W parametrze, do zdarzenia przekazywana jest tablica z danymi, które mają zostać zapisane.', 0),
(18, 'application.onUserDelete', '', '', 0),
(19, 'application.onUserLogin', 'int $user_id, string $user_password', 'Wywoływany w momencie logowania użytkownika. W parametrzach przekazywany jest ID użytkownika oraz jego hasło.', 0),
(20, 'application.onUserLogout', 'int $user_id', 'Wywoływany w momencie, gdy użytkownik zażąda wylogowania z systemu. W parametrze przekazywany jest ID użytkownika.', 0),
(21, 'application.onUserRegister', 'mixed &$data', 'Wywoływany w momencie rejestracji użytkownika. W parametrze przekazywana jest tablica zawierająca dane rejestrowanego użytkownika.', 0),
(22, 'application.onUserRegisterComplete', 'mixed &$data', 'Wywoływany po rejestracji użytkownika. W parametrze przekazywana jest tablica zawierająca informacje o zarejestrowanym użytkowniku.', 0),
(23, 'application.onUserLoginComplete', 'int $user_id', 'Wywoływany w momencie zalogowania użytkownika. W parametrze przekazywany jest ID użytkownika.', 0),
(24, 'application.onUserLogoutComplete', 'int $user_id', 'Wywoływany po wylogoawniu użytkownika. W parametrze przekazywany jest ID użytkownika.', 0),
(25, 'application.onTriggerRename', '', '', 0),
(26, 'system.onTriggerCall', 'array $data', '', 0),
(27, 'application.onBlockDisplay', '', '', 0),
(58, 'application.onPageSubmit', '', '', 0),
(59, 'application.onPageSubmitComplete', '', '', 0),
(60, 'application.onTextSubmitComplete', '', '', 0),
(61, 'application.onPageCache', '', '', 0),
(62, 'application.onPageDisplay', '', '', 0),
(63, 'application.onPageDelete', '', '', 0),
(64, 'application.onPageDeleteComplete', '', '', 0),
(65, 'application.onPageCopy', '', '', 0),
(66, 'application.onPageCopyComplete', '', '', 0),
(67, 'application.onPageMove', '', '', 0),
(68, 'application.onPageMoveComplete', '', '', 0),
(69, 'application.onPmSubmitComplete', '', '', 0);

INSERT INTO `validator` (`validator_id`, `validator_name`, `validator_regexp`, `validator_message`) VALUES
(1, 'Tylko liczby', '^([0-9]+)?$', 'Podana wartość nie jest prawidłową liczbą'),
(2, 'Dowolne znaki', '.*', ''),
(3, 'Znaki alfanumeryczne i wybrane symbole', '^([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.=:|#_ ()[\\]^-]+)?$', 'Wartość zawiera nieprawidłowe znaki'),
(4, 'Liczby zmiennoprzecinkowe', '^([0-9]*\\.?[0-9]*)?$', 'Podana wartość nie jest prawidłową liczbą zmiennoprzecinkową');

INSERT INTO `notify` (`notify_id`, `notify_trigger`, `notify_module`, `notify_plugin`, `notify_email`, `notify_name`, `notify_message`, `notify_default`) VALUES
(1, 'application.onPmSubmitComplete', 2, NULL, 4, 'Powiadamiaj o nowej wiadomości prywatnej', 'Nowa wiadomość od: {sender}', 3);