SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_url` tinytext NOT NULL,
  `news_hash` char(32) NOT NULL,
  `news_host` varchar(200) NOT NULL,
  `news_page` int(10) unsigned NOT NULL,
  `news_user` mediumint(8) unsigned NOT NULL,
  `news_score` double NOT NULL,
  `news_rate` smallint(6) NOT NULL,
  `news_priority` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `news_sponsored` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `news_thumbnail` varchar(30) NOT NULL,
  PRIMARY KEY (`news_id`),
  KEY `news_page` (`news_page`),
  KEY `news_score` (`news_score`),
  KEY `news_host` (`news_host`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onBeforeNewsInsert`;
DELIMITER //
CREATE TRIGGER `onBeforeNewsInsert` BEFORE INSERT ON `news`
 FOR EACH ROW BEGIN
	SET NEW.news_score = GET_USER_SCORE(NEW.news_user) * NEW.news_priority;
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `news_vote` (
  `vote_news` int(10) unsigned NOT NULL,
  `vote_user` mediumint(8) unsigned NOT NULL,
  `vote_value` tinyint(4) NOT NULL,
  `vote_time` int(10) unsigned NOT NULL,
  KEY `vote_news` (`vote_news`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onAfterNewsVoteInsert`;
DELIMITER //
CREATE TRIGGER `onAfterNewsVoteInsert` AFTER INSERT ON `news_vote`
 FOR EACH ROW BEGIN
	IF NEW.vote_value = 1 THEN
		UPDATE news SET news_rate = news_rate + 1, news_score = news_score + (GET_USER_SCORE(NEW.vote_user) * 0.01) WHERE news_id = NEW.vote_news;
	ELSE
		UPDATE news SET news_rate = news_rate - 1, news_score = news_score - (GET_USER_SCORE(NEW.vote_user) * 0.2) WHERE news_id = NEW.vote_news;
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterNewsVoteDelete`;
DELIMITER //
CREATE TRIGGER `onAfterNewsVoteDelete` AFTER DELETE ON `news_vote`
 FOR EACH ROW BEGIN
	IF OLD.vote_value = 1 THEN
		UPDATE news SET news_rate = news_rate - 1, news_score = news_score - (GET_USER_SCORE(OLD.vote_user) * 0.01) WHERE news_id = OLD.vote_news;
	ELSE
		UPDATE news SET news_rate = news_rate + 1, news_score = news_score + (GET_USER_SCORE(OLD.vote_user) * 0.2) WHERE news_id = OLD.vote_news;
	END IF;
END
//
DELIMITER ;


ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`news_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `news_vote`
  ADD CONSTRAINT `news_vote_ibfk_1` FOREIGN KEY (`vote_news`) REFERENCES `news` (`news_id`) ON DELETE CASCADE;

DELIMITER $$
CREATE FUNCTION `GET_USER_SCORE`(`userId` MEDIUMINT) RETURNS double
    READS SQL DATA
BEGIN
	DECLARE rate DOUBLE;
	
	SELECT ((UNIX_TIMESTAMP() - user_regdate) / 86400) * 0.01 INTO rate
	FROM `user`
	WHERE user_id = userId;
	
	RETURN rate;
END$$