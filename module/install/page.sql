SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `page` (`page_id`, `page_parent`, `page_module`, `page_connector`, `page_subject`, `page_title`, `page_path`, `page_text`, `page_depth`, `page_content`, `page_publish`, `page_published`, `page_unpublished`, `page_time`, `page_edit_time`, `page_delete`, `page_richtext`, `page_cache`, `page_template`, `page_order`, `page_matrix`) VALUES
(1, NULL, 1, 8, 'Strona główna', '', '', 1, 0, 1, 1, NULL, NULL, 1284459045, 1284459045, 0, 0, 1, 'homepage.php', 1, '000000001'),
(2, NULL, 2, 5, 'Logowanie', NULL, 'Logowanie', NULL, 0, 1, 1, NULL, NULL, 1284459091, 1284459091, 0, 0, 1, 'login.php', 2, '000000002'),
(3, NULL, 2, 7, 'Wyloguj', NULL, 'Wyloguj', NULL, 0, 0, 1, NULL, NULL, 1284459181, 1284459181, 0, 0, 0, '', 3, '000000003'),
(4, NULL, 2, 4, 'Rejestracja', NULL, 'Rejestracja', NULL, 0, 1, 1, NULL, NULL, 1284459250, 1284459250, 0, 0, 1, 'register.php', 4, '000000004');

INSERT INTO `page_group` (`page_id`, `group_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(4, 2);

INSERT INTO `page_text` (`text_id`, `text_content`, `text_time`, `text_log`, `text_user`, `text_ip`) VALUES
(1, '== Strona główna została wygenerowana! ==\r\n\r\nZawartość tej strony możesz zmienić w panelu administracyjnym.\r\nAby zalogować się do panelu administracyjnego, musisz posiadać uprawnienia administracyjne.\r\n\r\nPrzydatne linki:\r\n\r\n* <a href="adm">Logowanie do panelu administracyjnego</a>\r\n* <a href="Logowanie">Formularz logowania użytkownika</a>\r\n* <a href="Wyloguj">Wyloguj z systemu</a>\r\n* <a href="Rejestracja">Rejestracja nowego użytkownika</a>', 1284459045, '', 1, '127.0.0.2');

INSERT INTO `page_parser` (`page_id`, `parser_id`) VALUES
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7);