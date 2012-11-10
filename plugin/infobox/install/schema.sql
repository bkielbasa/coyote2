CREATE TABLE IF NOT EXISTS `infobox` (
  `infobox_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `infobox_title` varchar(100) NOT NULL,
  `infobox_content` text NOT NULL,
  `infobox_time` int(10) unsigned NOT NULL,
  `infobox_lifetime` int(10) unsigned NOT NULL,
  `infobox_enable` tinyint(1) unsigned NOT NULL,
  `infobox_priority` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`infobox_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `infobox_marking` (
  `infobox_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  KEY `infobox_id` (`infobox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `infobox_marking`
--
ALTER TABLE `infobox_marking`
  ADD CONSTRAINT `infobox_marking_ibfk_1` FOREIGN KEY (`infobox_id`) REFERENCES `infobox` (`infobox_id`) ON DELETE CASCADE;