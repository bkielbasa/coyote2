DELIMITER//
CREATE EVENT `topicView` ON SCHEDULE EVERY 1 HOUR STARTS '2010-11-21 12:35:08' ON COMPLETION PRESERVE ENABLE DO BEGIN
	DECLARE topicViews INT DEFAULT 0;
	DECLARE topicId INT;
	
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE cur1 CURSOR FOR SELECT topic_id, topic_view FROM topic_view;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	
	OPEN cur1;
		loop2: LOOP	
			FETCH cur1 INTO topicId, topicViews;
			
			IF done THEN
				LEAVE loop2;
			END IF;
			
			IF topicId IS NOT NULL THEN
				SELECT topicId;
				UPDATE topic SET topic_views = topic_views + topicViews WHERE topic_id = topicId;
			ELSE
				SET done = TRUE;
				LEAVE loop2;
			END IF;
		END LOOP;
	CLOSE cur1;
	
	TRUNCATE topic_view;
END//

DELIMITER ;