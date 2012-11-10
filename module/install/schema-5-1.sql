DELIMITER $$
DROP EVENT IF EXISTS `pagePublish`$$
CREATE EVENT `pagePublish` ON SCHEDULE EVERY 1 MINUTE STARTS '2010-09-08 14:16:49' ON COMPLETION PRESERVE ENABLE DO BEGIN
	UPDATE page SET page_publish = 0 
	WHERE page_publish = 1 
		AND page_unpublished 
			IS NOT NULL AND page_unpublished < NOW();
	
	UPDATE page SET page_publish = 1 
	WHERE page_publish = 0 
		AND page_published IS NOT NULL AND page_published < NOW() 
			AND (page_unpublished IS NULL OR page_unpublished > NOW());
END$$

DROP EVENT IF EXISTS `sessionGc`$$
CREATE EVENT `sessionGc` ON SCHEDULE EVERY 5 MINUTE STARTS '2010-09-08 14:51:25' ON COMPLETION PRESERVE ENABLE DO BEGIN
	SET @maxUsers = (SELECT config_value FROM config WHERE config_name = 'session.max');
	SET @currentUsers = (SELECT COUNT(*) FROM `session`);
	
	IF (@maxUsers IS NULL OR @currentUsers > @maxUsers) THEN
		CALL SET_CONFIG('session.max', @currentUsers);
		CALL SET_CONFIG('session.max_time', UNIX_TIMESTAMP());
	END IF;
	
	DELETE FROM `session` WHERE session_stop < (UNIX_TIMESTAMP() - 600);
	CALL SET_CONFIG('session.last_gc', UNIX_TIMESTAMP());
END$$

DROP EVENT IF EXISTS `notifyDelete`$$
CREATE EVENT `notifyDelete` ON SCHEDULE EVERY 1 DAY ON COMPLETION PRESERVE ENABLE DO BEGIN
	DELETE FROM notify_header WHERE header_read != 0 AND header_read < (UNIX_TIMESTAMP() - (2629744 * 3));
END$$