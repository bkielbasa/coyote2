SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS poll;
CREATE TABLE IF NOT EXISTS poll (
  poll_id smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID ankiety',
  poll_user mediumint(8) unsigned NOT NULL COMMENT 'ID użytkownika',
  poll_title varchar(100) NOT NULL COMMENT 'Temat ankiety',
  poll_start int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Data rozpoczęcia ankiety',
  poll_length int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Data zakończenia ankiety',
  poll_votes smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Liczba oddanych głosów w tej ankiecie',
  poll_max_item tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Ilość możliwych odpowiedzi (domyślnie: 1)',
  poll_enable tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Wartość 1 oznacza, że ankieta jest aktywna',
  PRIMARY KEY (poll_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Moduł ankiet';
DROP TRIGGER IF EXISTS `onAfterPollInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPollInsert` AFTER INSERT ON `poll`
 FOR EACH ROW INSERT INTO block_item (item_plugin, item_data, item_text) SELECT GET_PLUGIN_ID('poll'), NEW.poll_id, NEW.poll_title
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPollUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterPollUpdate` AFTER UPDATE ON `poll`
 FOR EACH ROW BEGIN
 	IF NEW.poll_title <> OLD.poll_title THEN
  		UPDATE block_item SET item_text = NEW.poll_title WHERE item_plugin = GET_PLUGIN_ID('poll') AND item_data = NEW.poll_id;
 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPollDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPollDelete` AFTER DELETE ON `poll`
 FOR EACH ROW DELETE FROM block_item WHERE item_plugin = GET_PLUGIN_ID('poll') AND item_data = OLD.poll_id
//
DELIMITER ;

DROP TABLE IF EXISTS poll_item;
CREATE TABLE IF NOT EXISTS poll_item (
  item_id tinyint(3) unsigned NOT NULL COMMENT 'Unikalne ID pozycji w ankiecie',
  item_text varchar(100) NOT NULL COMMENT 'Etykieta odpowiedzi',
  item_poll smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID ankiety (klucz obcy)',
  item_total mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Ilość głosów oddanych na tę pozycję',
  KEY item_poll (item_poll)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tabela przechowuje odpowiedzi do ankiet';

DROP TABLE IF EXISTS poll_vote;
CREATE TABLE IF NOT EXISTS poll_vote (
  vote_poll smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID ankiety (klucz obcy)',
  vote_user mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika',
  vote_item tinyint(3) unsigned NOT NULL,
  vote_ip varchar(16) NOT NULL,
  KEY vote_poll (vote_poll),
  KEY vote_user (vote_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Przechowuje ID użytkowników, którzy oddali głos w ankiecie';
DROP TRIGGER IF EXISTS `onAfterPollVoteInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPollVoteInsert` AFTER INSERT ON `poll_vote`
 FOR EACH ROW BEGIN
	UPDATE poll_item SET item_total = item_total + 1 
	WHERE item_poll = NEW.vote_poll AND item_id = NEW.vote_item;
	
	UPDATE poll SET poll_votes = poll_votes + 1
	WHERE poll_id = NEW.vote_poll;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPollVoteDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPollVoteDelete` AFTER DELETE ON `poll_vote`
 FOR EACH ROW BEGIN
	UPDATE poll_item SET item_total = item_total - 1 
	WHERE item_poll = OLD.vote_poll AND item_id = OLD.vote_item;
	
	UPDATE poll SET poll_votes = poll_votes - 1
	WHERE poll_id = OLD.vote_poll;
END
//
DELIMITER ;


ALTER TABLE `poll_item`
  ADD CONSTRAINT poll_item_ibfk_1 FOREIGN KEY (item_poll) REFERENCES poll (poll_id) ON DELETE CASCADE;

ALTER TABLE `poll_vote`
  ADD CONSTRAINT poll_vote_ibfk_2 FOREIGN KEY (vote_user) REFERENCES `user` (user_id) ON DELETE CASCADE,
  ADD CONSTRAINT poll_vote_ibfk_1 FOREIGN KEY (vote_poll) REFERENCES poll (poll_id) ON DELETE CASCADE;