SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `forum` (
  `forum_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID forum',
  `forum_page` int(10) unsigned NOT NULL,
  `forum_description` varchar(255) DEFAULT NULL COMMENT 'Krótki opis forum',
  `forum_section` varchar(50) NOT NULL COMMENT 'Nazwa sekcji, w której umiesczona ma być kategoria',
  `forum_permission` text NOT NULL,
  `forum_url` varchar(255) DEFAULT NULL COMMENT 'Adres URL jeżeli forum ma służyć jako link (przekierowanie)',
  `forum_topics` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Liczba tematów na forum',
  `forum_posts` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Liczba postów na forum',
  `forum_last_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID ostatniego postu na forum',
  `forum_lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Wartość 1 oznacza, iż forum jest zablokowane (nie można w nim pisać)',
  `forum_prune` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Określa ilość dni po upłynięciu których, posty powinny być kasowane (0 - nie usuwa żadnych)',
  PRIMARY KEY (`forum_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tabela przechowująca informacje o forach';

CREATE TABLE IF NOT EXISTS `forum_marking` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID forum',
  `mark_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Data i czas (timestamp)',
  PRIMARY KEY (`user_id`,`forum_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Informacje o tym, że forum zostało zaznaczone jako przeczyta';

CREATE TABLE IF NOT EXISTS `forum_reason` (
  `reason_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `reason_name` varchar(100) NOT NULL,
  `reason_content` text NOT NULL,
  PRIMARY KEY (`reason_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `post` (
  `post_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID postu',
  `post_topic` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID tematu',
  `post_forum` smallint(5) unsigned NOT NULL COMMENT 'ID forum',
  `post_user` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika',
  `post_username` varchar(20) NOT NULL COMMENT 'Nazwa użytkownika jeśli możliwe jest pisanie przez użytkowników anonimowych',
  `post_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Data i czas napisania posta (timestamp)',
  `post_edit_time` int(10) unsigned DEFAULT NULL COMMENT 'Data i zcas edycji posta (timestamp)',
  `post_edit_user` mediumint(8) unsigned DEFAULT NULL COMMENT 'ID użytkownika, który ostatnio edytował post',
  `post_edit_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `post_enable_smilies` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Wartość 1 oznacza, że w poście będą wyświetlane uśmieszki',
  `post_enable_html` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Wartość 1 oznacza wyświetlanie HTML-a w postach',
  `post_vote` smallint(6) NOT NULL DEFAULT '0',
  `post_text` int(10) unsigned NULL DEFAULT NULL,
  `post_ip` varchar(16) NOT NULL COMMENT 'IP autora postu',
  `post_host` varchar(255) NOT NULL COMMENT 'Host oraz ewentualnie IP wew.',
  `post_browser` varchar(150) NOT NULL COMMENT 'User agent',
  PRIMARY KEY (`post_id`),
  KEY `post_topic` (`post_topic`),
  KEY `post_forum` (`post_forum`),
  KEY `post_time` (`post_time`),
  KEY `post_user` (`post_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Przechowuje posty na forum';
DROP TRIGGER IF EXISTS `onAfterPostInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPostInsert` AFTER INSERT ON `post`
 FOR EACH ROW BEGIN
	UPDATE `topic` SET topic_last_post_id = NEW.post_id, topic_last_post_time = NEW.post_time, topic_replies = (topic_replies + 1) WHERE topic_id = NEW.post_topic;
	UPDATE `forum` SET forum_posts = (forum_posts + 1), forum_last_post_id = NEW.post_id WHERE forum_id = NEW.post_forum;
	SET @topicFirstId = (SELECT topic_first_post_id FROM `topic` WHERE topic_id = NEW.post_topic);

	IF NOT @topicFirstId THEN
		UPDATE `topic` SET topic_first_post_id = NEW.post_id, topic_replies = 0 WHERE topic_id = NEW.post_topic;
	END IF;

 	IF NEW.post_user > 0 THEN
 		UPDATE `user` SET user_post = user_post + 1 WHERE user_id = NEW.post_user;

		INSERT IGNORE topic_user (topic_id, user_id) VALUES(NEW.post_topic, NEW.post_user);
		INSERT post_subscribe (post_id, user_id) VALUES(NEW.post_id, NEW.post_user);
 	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPostDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPostDelete` AFTER DELETE ON `post`
 FOR EACH ROW BEGIN
	SELECT post_id, post_time INTO @postId, @postTime
	FROM post WHERE post_topic = OLD.post_topic
	ORDER BY post_id DESC
	LIMIT 1;

 	UPDATE topic SET topic_replies = (topic_replies -1),
 						  topic_last_post_id = @postId,
 						  topic_last_post_time = @postTime
 	WHERE topic_id = OLD.post_topic;

 	UPDATE forum SET forum_posts = (forum_posts -1),
 	  					  forum_last_post_id = GET_FORUM_LAST_POST(OLD.post_forum)
 	WHERE forum_id = OLD.post_forum;

 	IF OLD.post_user > 0 THEN
 		UPDATE user SET user_post = user_post - 1 WHERE user_id = OLD.post_user;

		if !(SELECT COUNT(post_id) FROM post
  				WHERE post_topic = OLD.post_topic AND post_user = OLD.post_user) THEN

  			DELETE FROM topic_user WHERE topic_id = OLD.post_topic AND user_id = OLD.post_user;

  		END IF;
	END IF;
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `post_attachment` (
  `attachment_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `attachment_post` mediumint(8) unsigned NOT NULL,
  `attachment_name` varchar(100) NOT NULL,
  `attachment_file` varchar(30) NOT NULL,
  `attachment_count` mediumint(8) unsigned NOT NULL,
  `attachment_size` int(10) unsigned NOT NULL,
  `attachment_mime` varchar(50) NOT NULL,
  `attachment_time` int(10) unsigned NOT NULL,
  `attachment_width` smallint(5) unsigned NOT NULL,
  `attachment_height` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `attachment_post` (`attachment_post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `post_comment` (
  `comment_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `comment_user` mediumint(8) unsigned NOT NULL,
  `comment_time` int(10) unsigned NOT NULL,
  `comment_post` mediumint(8) unsigned NOT NULL,
  `comment_text` varchar(580) NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `comment_post` (`comment_post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TRIGGER IF EXISTS `onBeforePostCommentInsert`;
DELIMITER //
CREATE TRIGGER `onBeforePostCommentInsert` BEFORE INSERT ON `post_comment`
 FOR EACH ROW BEGIN
	IF (SELECT COUNT(*) FROM post_comment WHERE comment_post = NEW.comment_post AND comment_user = NEW.comment_user) = 0 THEN
		INSERT IGNORE INTO post_subscribe (post_id, user_id) VALUES(NEW.comment_post, NEW.comment_user);
	END IF;
END//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `post_text` (
  `text_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text_post` mediumint(8) unsigned NOT NULL,
  `text_content` text NOT NULL,
  `text_time` int(10) unsigned NOT NULL,
  `text_user` mediumint(8) unsigned NOT NULL,
  `text_ip` varchar(16) NOT NULL,
  `text_host` varchar(255) NOT NULL,
  `text_browser` varchar(150) NOT NULL,
  PRIMARY KEY (`text_id`),
  KEY `text_post` (`text_post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TRIGGER IF EXISTS `onAfterPostTextInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPostTextInsert` AFTER INSERT ON `post_text`
 FOR EACH ROW BEGIN
	UPDATE post
	SET post_text = NEW.text_id
	WHERE post_id = NEW.text_post;
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `post_subscribe` (
  `post_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `post_id` (`post_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_subscribe`
  ADD CONSTRAINT `post_subscribe_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_subscribe_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`) ON DELETE CASCADE;

DROP TABLE IF EXISTS `post_vote`;

CREATE TABLE IF NOT EXISTS `post_vote` (
  `vote_post` mediumint(8) unsigned NOT NULL,
  `vote_user` mediumint(8) unsigned NOT NULL,
  `vote_value` tinyint(4) NOT NULL,
  `vote_time` int(11) DEFAULT NULL,
  `vote_ip` varchar(16) DEFAULT NULL,
  KEY `post_id` (`vote_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onAfterPostVoteInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPostVoteInsert` AFTER INSERT ON `post_vote`
 FOR EACH ROW BEGIN
 	UPDATE post SET post_vote = post_vote + NEW.vote_value WHERE post_id = NEW.vote_post;
 	UPDATE topic SET topic_vote = topic_vote + NEW.vote_value WHERE topic_first_post_id = NEW.vote_post;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPostVoteUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterPostVoteUpdate` AFTER UPDATE ON `post_vote`
 FOR EACH ROW IF NEW.vote_post != OLD.vote_post THEN
  	UPDATE post SET post_vote = (post_vote + NEW.vote_value) WHERE post_id = NEW.vote_post;
  	UPDATE topic SET topic_vote = (topic_vote + NEW.vote_value) WHERE topic_first_post_id = NEW.vote_post;
 ELSE
  	UPDATE post SET post_vote = ((post_vote - OLD.vote_value) + NEW.vote_value) WHERE post_id = NEW.vote_post;
  	UPDATE topic SET topic_vote = ((topic_vote - OLD.vote_value) + NEW.vote_value) WHERE topic_first_post_id = NEW.vote_post;
 END IF
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPostVoteDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPostVoteDelete` AFTER DELETE ON `post_vote`
 FOR EACH ROW BEGIN
 	UPDATE post SET post_vote = (post_vote - OLD.vote_value) WHERE post_id = OLD.vote_post;
 	UPDATE topic SET topic_vote = (topic_vote - OLD.vote_value) WHERE topic_first_post_id = OLD.vote_post;
 END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `post_accept` (
  `accept_post` mediumint(8) unsigned NOT NULL,
  `accept_topic` mediumint(8) unsigned NOT NULL,
  `accept_user` mediumint(8) unsigned NOT NULL,
  `accept_time` int(10) unsigned DEFAULT NULL,
  `accept_ip` varchar(16) DEFAULT NULL,
  KEY `FK__post` (`accept_post`),
  KEY `accept_topic` (`accept_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela zawiera informacje o zaakceptowanych odpowiedziach w watkach';

CREATE TABLE IF NOT EXISTS `topic` (
  `topic_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID tematu',
  `topic_page` int(10) unsigned NOT NULL,
  `topic_views` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Ilość wyświetleń wiadomości',
  `topic_replies` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Ilość odpowiedzi w temacie',
  `topic_forum` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID forum, w którym znajduje się temat',
  `topic_sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `topic_announcement` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `topic_lock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `topic_poll` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID ewentualnej ankiety dołączonej do wątku',
  `topic_vote` smallint(5) NOT NULL DEFAULT '0',
  `topic_solved` mediumint(8) unsigned NULL DEFAULT NULL,
  `topic_moved_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID forum, z którego został przeniesiony temat',
  `topic_first_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID pierwszego postu w wątku',
  `topic_last_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID ostatniego postu w wątku',
  `topic_last_post_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`topic_id`),
  KEY `topic_forum` (`topic_forum`),
  KEY `topic_last_post_id` (`topic_last_post_id`),
  KEY `topic_page` (`topic_page`),
  KEY `topic_sticky` (`topic_sticky`),
  KEY `topic_solved` (`topic_solved`),
  KEY `topic_last_post_time` (`topic_last_post_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Zawiera listę tematów na forum';
DROP TRIGGER IF EXISTS `onAfterTopicInsert`;
DELIMITER //
CREATE TRIGGER `onAfterTopicInsert` AFTER INSERT ON `topic`
 FOR EACH ROW UPDATE `forum` SET forum_topics = (forum_topics + 1) WHERE forum_id = NEW.topic_forum
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onBeforeTopicUpdate`;
DELIMITER //
CREATE TRIGGER `onBeforeTopicUpdate` BEFORE UPDATE ON `topic`
 FOR EACH ROW BEGIN
 	/**
 	 * Ponizsza instrukcja zostanie wykonana tylko wowczas gdy przenosimy temat do
 	 * innego forum
 	 */
 	IF NEW.topic_forum != OLD.topic_forum THEN

 		SET NEW.topic_moved_id = OLD.topic_forum;

 	   UPDATE `post` SET post_forum = NEW.topic_forum WHERE post_topic = OLD.topic_id;
 		UPDATE `topic_marking` SET forum_id = NEW.topic_forum WHERE topic_id = OLD.topic_id;

 		SET @postCount = (

 			SELECT COUNT(post_id)
 			FROM post
 			WHERE post_topic = NEW.topic_id
 		);

 		UPDATE forum
 		SET forum_topics = forum_topics -1, forum_posts = forum_posts - @postCount, forum_last_post_id = GET_FORUM_LAST_POST(OLD.topic_forum)
 		WHERE forum_id = OLD.topic_forum;

 		UPDATE forum
 		SET forum_topics = forum_topics +1, forum_posts = forum_posts + @postCount, forum_last_post_id = GET_FORUM_LAST_POST(NEW.topic_forum)
 		WHERE forum_id = NEW.topic_forum;

		DELETE FROM page_group WHERE page_id = OLD.topic_page;

  		INSERT INTO page_group (page_id, group_id)
  		SELECT NEW.topic_page, group_id
  		FROM page_group
  		WHERE page_id = (

  			SELECT forum_page
  			FROM forum
  			WHERE forum_id = NEW.topic_forum

  		);

 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onBeforeTopicDelete`;
DELIMITER //
CREATE TRIGGER `onBeforeTopicDelete` BEFORE DELETE ON `topic`
 FOR EACH ROW BEGIN
 	DECLARE postUser MEDIUMINT UNSIGNED;
 	DECLARE done BOOL DEFAULT FALSE;
 	DECLARE cur1 CURSOR FOR SELECT post_user FROM post WHERE post_topic = OLD.topic_id;
 	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

 	SET @postCount = 0;

 	OPEN cur1;
 		WHILE NOT done DO
 			FETCH cur1 INTO postUser;

 			IF NOT done THEN
 				SET @postCount = @postCount + 1;

 				IF postUser > 0 THEN
 					UPDATE user SET user_post = user_post - 1 WHERE user_id = postUser;
 				END IF;
 			END IF;
 		END WHILE;
 	CLOSE cur1;

 	/* nie musimy kasowac danych z post poniewaz mysql zrobi to za nas (klucz obcy) */

 	UPDATE `forum` SET forum_topics = (forum_topics -1),
 							 forum_posts = (forum_posts - @postCount)
 	WHERE forum_id = OLD.topic_forum;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterTopicDelete`;
DELIMITER //
CREATE TRIGGER `onAfterTopicDelete` AFTER DELETE ON `topic`
 FOR EACH ROW BEGIN
	UPDATE forum SET forum_last_post_id = GET_FORUM_LAST_POST(OLD.topic_forum)
	WHERE forum_id = OLD.topic_forum;
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `topic_marking` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID tematu',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID forum',
  `mark_time` int(10) unsigned NOT NULL,
  UNIQUE KEY `topic_id_2` (`topic_id`,`user_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Zawiera informacje o przeczytanych tematach';

CREATE TABLE IF NOT EXISTS `topic_tag` (
  `topic_id` mediumint(8) unsigned NOT NULL,
  `tag_text` varchar(100) NOT NULL,
  KEY `topic_id` (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS topic_user (
  topic_id mediumint(8) unsigned NOT NULL,
  user_id mediumint(8) unsigned NOT NULL,
  UNIQUE KEY topic_user_id (topic_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Polaczenie ID userow oraz ID tematow w jakich brali udzial';

CREATE TABLE IF NOT EXISTS `topic_view` (
  `topic_id` mediumint(8) unsigned NOT NULL,
  `topic_view` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `topic_id` (`topic_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

ALTER TABLE `forum_marking`
  ADD CONSTRAINT `forum_marking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_marking_ibfk_3` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`forum_id`) ON DELETE CASCADE;

ALTER TABLE `post`
  ADD CONSTRAINT `FK_post_data_forum` FOREIGN KEY (`post_forum`) REFERENCES `forum` (`forum_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_post_data_topic` FOREIGN KEY (`post_topic`) REFERENCES `topic` (`topic_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `post_attachment`
  ADD CONSTRAINT `post_attachment_ibfk_1` FOREIGN KEY (`attachment_post`) REFERENCES `post` (`post_id`) ON DELETE CASCADE;

ALTER TABLE `post_comment`
  ADD CONSTRAINT `post_comment_ibfk_1` FOREIGN KEY (`comment_post`) REFERENCES `post` (`post_id`) ON DELETE CASCADE;

ALTER TABLE `post_text`
  ADD CONSTRAINT post_text_ibfk_1 FOREIGN KEY (text_post) REFERENCES post (post_id) ON DELETE CASCADE;

ALTER TABLE `topic`
  ADD CONSTRAINT `topic_ibfk_1` FOREIGN KEY (`topic_forum`) REFERENCES `forum` (`forum_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_ibfk_2` FOREIGN KEY (`topic_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_ibfk_3` FOREIGN KEY (`topic_solved`) REFERENCES `post` (`post_id`) ON DELETE SET NULL;

ALTER TABLE `topic_marking`
  ADD CONSTRAINT `topic_marking_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_marking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_marking_ibfk_3` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`forum_id`) ON DELETE CASCADE;

ALTER TABLE `topic_tag`
  ADD CONSTRAINT `topic_tag_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`topic_id`) ON DELETE CASCADE;

ALTER TABLE `topic_user`
  ADD CONSTRAINT topic_user_ibfk_1 FOREIGN KEY (topic_id) REFERENCES topic (topic_id) ON DELETE CASCADE;

ALTER TABLE `post_vote`
  ADD CONSTRAINT `post_vote_ibfk_1` FOREIGN KEY (`vote_post`) REFERENCES `post` (`post_id`) ON DELETE CASCADE;

ALTER TABLE `post_accept`
  ADD CONSTRAINT `FK__post` FOREIGN KEY (`accept_post`) REFERENCES `post` (`post_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS=1;

DELIMITER $$
DROP FUNCTION IF EXISTS `GET_TOPIC_FIRST_POST`$$
CREATE FUNCTION GET_TOPIC_FIRST_POST(`topicId` MEDIUMINT) RETURNS mediumint(8) UNSIGNED
BEGIN
 	RETURN (SELECT MIN(post_id) FROM post WHERE post_topic = topicId);
END$$

DROP FUNCTION IF EXISTS `GET_TOPIC_LAST_POST`$$
CREATE FUNCTION GET_TOPIC_LAST_POST(`topicId` MEDIUMINT) RETURNS mediumint(8) UNSIGNED
BEGIN
	RETURN (SELECT MAX(post_id) FROM post WHERE post_topic = topicId);
END$$

DROP FUNCTION IF EXISTS `GET_FORUM_LAST_POST`$$
CREATE FUNCTION GET_FORUM_LAST_POST(`forumId` SMALLINT) RETURNS mediumint(9) UNSIGNED
BEGIN
	RETURN (SELECT MAX(post_id) FROM `post` WHERE post_forum = forumId);
END$$

DELIMITER ;