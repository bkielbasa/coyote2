# --------------------------------------------------------
# Host:                         127.0.0.1
# Server version:               5.1.41-community-log
# Server OS:                    Win32
# HeidiSQL version:             5.1.0.3508
# Date/time:                    2010-09-13 17:19:25
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `bookmark` (
  `bookmark_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `bookmark_url` varchar(255) NOT NULL COMMENT 'Adres URL (unikalny)',
  `bookmark_host` varchar(255) NOT NULL COMMENT 'Host danego adresu URL',
  `bookmark_rank` smallint(5) NOT NULL DEFAULT '0',
  `bookmark_page` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bookmark_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Moduł zakładki';



CREATE TABLE IF NOT EXISTS `bookmark_rank` (
  `rank_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rank_bookmark` smallint(5) unsigned NOT NULL COMMENT 'Klucz obcy do ID odnośnika',
  `rank_user` mediumint(8) unsigned NOT NULL,
  `rank_time` int(10) unsigned NOT NULL COMMENT 'Data dodania rekordu',
  `rank_value` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Wartość 1 oznacza, że link jest polecany przez daną osobę (-1 w przeciwnym przypadku)',
  PRIMARY KEY (`rank_id`),
  KEY `digg_bookmark` (`rank_bookmark`),
  CONSTRAINT `bookmark_rank_ibfk_1` FOREIGN KEY (`rank_bookmark`) REFERENCES `bookmark` (`bookmark_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista użytkowników, którzy wykopali/polecili dany link';

CREATE TABLE IF NOT EXISTS `bookmark_user` (
  `bookmark_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `bookmark_user` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `bookmark_description` tinytext NOT NULL,
  `bookmark_url` smallint(5) unsigned NOT NULL COMMENT 'Klucz obcy do odnośnika',
  PRIMARY KEY (`bookmark_id`),
  KEY `user` (`bookmark_user`),
  KEY `FK2` (`bookmark_url`),
  CONSTRAINT `bookmark_user_ibfk_1` FOREIGN KEY (`bookmark_url`) REFERENCES `bookmark` (`bookmark_id`) ON DELETE CASCADE,
  CONSTRAINT `bookmark_user_ibfk_2` FOREIGN KEY (`bookmark_user`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista zakładek danego użytkownika';

# Data exporting was unselected.


# Dumping structure for trigger 4p1.onAfterBookmarkRankDelete
SET SESSION SQL_MODE='';
DELIMITER //
CREATE TRIGGER `onAfterBookmarkRankDelete` AFTER DELETE ON `bookmark_rank` FOR EACH ROW UPDATE bookmark SET bookmark_rank = bookmark_rank - OLD.rank_value WHERE bookmark_id = OLD.rank_bookmark//
DELIMITER ;

# Dumping structure for trigger 4p1.onAfterBookmarkRankInsert
SET SESSION SQL_MODE='';
DELIMITER //
CREATE TRIGGER `onAfterBookmarkRankInsert` AFTER INSERT ON `bookmark_rank` FOR EACH ROW UPDATE bookmark SET bookmark_rank = bookmark_rank + NEW.rank_value WHERE bookmark_id = NEW.rank_bookmark//
DELIMITER ;


# Dumping structure for trigger 4p1.onBeforeBookmarkRankUpdate
SET SESSION SQL_MODE='';
DELIMITER //
CREATE TRIGGER `onBeforeBookmarkRankUpdate` BEFORE UPDATE ON `bookmark_rank` FOR EACH ROW BEGIN
 	UPDATE bookmark SET bookmark_rank = bookmark_rank - OLD.rank_value WHERE bookmark_id = OLD.rank_bookmark;
 	UPDATE bookmark SET bookmark_rank = bookmark_rank + NEW.rank_value WHERE bookmark_id = NEW.rank_bookmark;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;