DELIMITER //
CREATE EVENT `pagePublish` ON SCHEDULE
		EVERY 1 MINUTE STARTS '2010-09-08 14:16:49'
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN
	UPDATE page SET page_publish = 0 
	WHERE page_publish = 1 
		AND page_unpublished 
			IS NOT NULL AND page_published < NOW();
	
	UPDATE page SET page_publish = 1 
	WHERE page_publish = 0 
		AND page_published IS NOT NULL AND page_published < NOW() 
			AND (page_published IS NOT NULL OR page_unpublished > NOW());
END//

CREATE EVENT `sessionGc` ON SCHEDULE
		EVERY 5 MINUTE STARTS '2010-09-08 14:51:25'
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN
	
	SET @maxUsers = (SELECT config_value FROM config WHERE config_name = 'session.max');
	SET @currentUsers = (SELECT COUNT(*) FROM `session`);
	
	IF (@maxUsers IS NULL OR @currentUsers > @maxUsers) THEN
		CALL SET_CONFIG('session.max', @currentUsers);
		CALL SET_CONFIG('session.max_time', UNIX_TIMESTAMP());
	END IF;
	
	DELETE FROM `session` WHERE session_stop < (UNIX_TIMESTAMP() - 600);
	CALL SET_CONFIG('session.last_gc', UNIX_TIMESTAMP());
END//