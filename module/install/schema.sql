SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS `accessor`;
CREATE TABLE IF NOT EXISTS `accessor` (
  `accessor_from` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID dokumentu w którym znajduje się odnośnik',
  `accessor_to` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID dokumentu do którego prowadzi odnośnik',
  UNIQUE KEY `accessor_from_2` (`accessor_from`,`accessor_to`),
  KEY `accessor_to` (`accessor_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Zawiera informacje o powiązaniach między artykułami (linki)';
DROP TRIGGER IF EXISTS `onAfterAccessorInsert`;
DELIMITER //
CREATE TRIGGER `onAfterAccessorInsert` AFTER INSERT ON `accessor`
 FOR EACH ROW CALL CLEAR_CACHE(NEW.accessor_from)
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterAccessorDelete`;
DELIMITER //
CREATE TRIGGER `onAfterAccessorDelete` AFTER DELETE ON `accessor`
 FOR EACH ROW CALL CLEAR_CACHE(OLD.accessor_from)
//
DELIMITER ;

DROP TABLE IF EXISTS `actkey`;
CREATE TABLE IF NOT EXISTS `actkey` (
  `actkey` char(10) NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `adm_menu`;
CREATE TABLE IF NOT EXISTS `adm_menu` (
  `menu_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID menu',
  `menu_parent` smallint(5) unsigned NOT NULL COMMENT 'ID rodzica',
  `menu_text` varchar(100) NOT NULL COMMENT 'Etykieta menu',
  `menu_auth` varchar(20) NOT NULL COMMENT 'Dodatkowe uprawnienie, które wymagane jest, aby możliwy był dostęp do tej zakładki',
  `menu_controller` varchar(100) NOT NULL COMMENT 'Nazwa kontrolera',
  `menu_action` varchar(100) NOT NULL COMMENT 'Nazwa akcji ',
  `menu_order` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT 'Kolejność wyświetlania zakładki',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Informacje o menu w panelu administracyjnym';
DROP TRIGGER IF EXISTS `onBeforeAdmMenuInsert`;
DELIMITER //
CREATE TRIGGER `onBeforeAdmMenuInsert` BEFORE INSERT ON `adm_menu`
 FOR EACH ROW SET NEW.menu_order = (SELECT IFNULL(MAX(menu_order), 0) FROM adm_menu WHERE menu_parent = NEW.menu_parent) + 1
//
DELIMITER ;

DROP TABLE IF EXISTS `adm_session`;
CREATE TABLE IF NOT EXISTS `adm_session` (
  `session_id` varchar(32) NOT NULL COMMENT 'ID sesji (z tabeli session)',
  `session_user_id` mediumint(8) unsigned NOT NULL COMMENT 'ID zalogowanego użytkownika',
  `session_time` int(10) unsigned NOT NULL COMMENT 'Czas logowania',
  PRIMARY KEY (`session_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Lista zalogowanych w panelu administracyjnym';

DROP TABLE IF EXISTS `attachment`;
CREATE TABLE IF NOT EXISTS `attachment` (
  `attachment_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalny ID załącznika',
  `attachment_name` varchar(50) NOT NULL COMMENT 'Nazwa pliku',
  `attachment_file` varchar(30) NOT NULL COMMENT 'Nazwa pliku na serwerze',
  `attachment_size` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Rozmiar pliku',
  `attachment_mime` varchar(50) NOT NULL,
  `attachment_time` int(10) unsigned NOT NULL,
  `attachment_user` mediumint(8) unsigned NOT NULL,
  `attachment_image` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Wartość posiada wartość 1 jeżeli załącznik jest obrazkiem',
  `attachment_width` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Szerokość obrazka jeśli załącznik jest obrazkiem',
  `attachment_height` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Wysokość obrazka jeżeli załącznik jest obrazem',
  PRIMARY KEY (`attachment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tabela przechowuje informacje o załącznikach do tekstów';
DROP TRIGGER IF EXISTS `onBeforeAttachmentDelete`;
DELIMITER //
CREATE TRIGGER `onBeforeAttachmentDelete` BEFORE DELETE ON `attachment`
 FOR EACH ROW BEGIN
 	DECLARE done BOOL;
 	DECLARE pageId INT;
 	DECLARE cur CURSOR FOR SELECT page_id FROM page WHERE page_text IN(
 
 		SELECT DISTINCT text_id
 		FROM page_attachment
 		WHERE attachment_id = OLD.attachment_id
  	);
  	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
 
  	OPEN cur;
  		REPEAT
  			FETCH cur INTO pageId;
 
 			
 			DELETE FROM page_cache WHERE cache_page = pageId;
 
 			
 		 	DELETE FROM page_cache WHERE cache_page IN(
 
 		 		SELECT page_id
 		 		FROM page
 		 		WHERE page_text IN(
 
 			 		SELECT DISTINCT text_id
 			 		FROM page_template
 			 		WHERE pageId = pageId
 			 	)
 		 	);
 
  		UNTIL done END REPEAT;
  	CLOSE cur;
 
  END
//
DELIMITER ;

DROP TABLE IF EXISTS `auth_data`;
CREATE TABLE IF NOT EXISTS `auth_data` (
  `data_group` mediumint(8) unsigned NOT NULL COMMENT 'ID grupy (klucz obcy do tabeli coyote_group)',
  `data_option` smallint(5) unsigned NOT NULL COMMENT 'ID opcji (klucz obcy do tabeli coyote_auth_option)',
  `data_value` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Wartość konfiguracyjna. Moze przybrać wartość 1 lub zero',
  KEY `data_role` (`data_group`),
  KEY `data_option` (`data_option`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='Informacje o kontroli dostępu dla grup';

DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE IF NOT EXISTS `auth_group` (
  `user_id` mediumint(8) unsigned NOT NULL COMMENT 'ID użytkownika (klucz obcy z tabeli coyote_useR)',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT 'ID grupy',
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='Lista użytkowników i grup do których przynależą';
DROP TRIGGER IF EXISTS `onAfterAuthGroupInsert`;
DELIMITER //
CREATE TRIGGER `onAfterAuthGroupInsert` AFTER INSERT ON `auth_group`
 FOR EACH ROW BEGIN
 	SET @groupId = (SELECT group_id FROM `group` WHERE group_name = "USER");
 
 	IF @groupId != NEW.group_id THEN
 		UPDATE `user` SET user_permission = "", user_group = NEW.group_id WHERE user_id = NEW.user_id;
 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterAuthGroupDelete`;
DELIMITER //
CREATE TRIGGER `onAfterAuthGroupDelete` AFTER DELETE ON `auth_group`
 FOR EACH ROW BEGIN 
	SET @groupId = (SELECT group_id FROM auth_group WHERE user_id = OLD.user_id ORDER BY group_id DESC LIMIT 1);
	UPDATE `user` SET user_permission = "", user_group = @groupId WHERE user_id = OLD.user_id; 
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `auth_option`;
CREATE TABLE IF NOT EXISTS `auth_option` (
  `option_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID opcji (unikalne)',
  `option_text` varchar(17) NOT NULL COMMENT 'Nazwa opcji dostępu',
  `option_label` varchar(100) NOT NULL COMMENT 'Krótki opis pozycji',
  `option_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Domyślna wartość konfiguracji. Może przyjąć wartość 1 lub zero',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_text` (`option_text`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Access Control List; lista opcji dostępu';
DROP TRIGGER IF EXISTS `onAfterOptionInsert`;
DELIMITER //
CREATE TRIGGER `onAfterOptionInsert` AFTER INSERT ON `auth_option`
 FOR EACH ROW BEGIN
 	INSERT INTO auth_data (data_group, data_option, data_value) SELECT group_id, NEW.option_id, NEW.option_default FROM `group`;
 	UPDATE `user` SET user_permission = "" WHERE user_permission != '';
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `ban`;
CREATE TABLE IF NOT EXISTS `ban` (
  `ban_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `ban_user` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ban_ip` varchar(50) NOT NULL COMMENT 'Pole może zawierać wyrażenia regularne',
  `ban_email` varchar(100) DEFAULT NULL,
  `ban_expire` int(10) unsigned NOT NULL DEFAULT '0',
  `ban_reason` varchar(255) NOT NULL,
  `ban_creator` mediumint(8) unsigned DEFAULT NULL,
  `ban_flood` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ban_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `block`;
CREATE TABLE IF NOT EXISTS `block` (
  `block_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID bloku',
  `block_name` varchar(100) NOT NULL COMMENT 'Nazwa bloku',
  `block_region` varchar(100) NOT NULL COMMENT 'Nazwa regionu do którego przynależy blok (może to być wartość pusta)',
  `block_plugin` smallint(5) unsigned DEFAULT NULL COMMENT 'ID pluginu do którego odwołuje się dany blok',
  `block_item` mediumint(8) unsigned DEFAULT NULL COMMENT 'ID danego elementu (klucz obcy do item_id z tabeli block_item)',
  `block_trigger` smallint(5) unsigned DEFAULT NULL COMMENT 'ID triggera, który będzie wywoływany w momencie wyświetlenia bloku',
  `block_auth` varchar(20) NOT NULL COMMENT 'Dodatkowe uprawnienie konieczne do wyświetlenia bloku',
  `block_header` text NOT NULL COMMENT 'Kod HTML (lub PHP) który zostanie wyświetlony wraz z blokiem (nagłówek)',
  `block_footer` text NOT NULL COMMENT 'Kod HTML (lub PHP), który zostanie wyświetlony wraz z blokiem (stopka)',
  `block_pages` text NOT NULL COMMENT 'Lista stron na których blok zostanei wyświetlony (lub pominięty)',
  `block_scope` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Opcje wyświetlania. Określa, czy blok będzie wyświetlany, czy ukrywany dla stron określonych w block_pages',
  `block_cache` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Opcje cachowania. Określa, czy blok powinien być zapisywany w cache',
  `block_style` varchar(10) NOT NULL,
  `block_order` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT 'Kolejność wyświetlania bloku',
  PRIMARY KEY (`block_id`),
  KEY `block_trigger` (`block_trigger`),
  KEY `block_module` (`block_plugin`),
  KEY `block_item` (`block_item`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tabela przechowuje informacje o blokach';
DROP TRIGGER IF EXISTS `onBeforeBlockInsert`;
DELIMITER //
CREATE TRIGGER `onBeforeBlockInsert` BEFORE INSERT ON `block`
 FOR EACH ROW SET NEW.block_order = (SELECT IFNULL(MAX(block_order), 0) FROM block WHERE block_region = NEW.block_region) + 1
//
DELIMITER ;

DROP TABLE IF EXISTS `block_group`;
CREATE TABLE IF NOT EXISTS `block_group` (
  `group_id` mediumint(8) unsigned NOT NULL COMMENT 'ID grupy',
  `block_id` smallint(5) unsigned NOT NULL COMMENT 'ID bloku',
  KEY `group_id` (`group_id`),
  KEY `block_id` (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Określa dla jakich grup, dany blok powinien być wyświetlany';

DROP TABLE IF EXISTS `block_item`;
CREATE TABLE IF NOT EXISTS `block_item` (
  `item_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `item_plugin` smallint(5) unsigned NOT NULL,
  `item_data` varchar(255) NOT NULL,
  `item_text` varchar(255) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `item_plugin` (`item_plugin`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

DROP TABLE IF EXISTS `broken`;
CREATE TABLE IF NOT EXISTS `broken` (
  `broken_from` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID dokumentu, w którym znajduje się link (klucz obcy do page.page_id)',
  `broken_path` varchar(255) NOT NULL COMMENT 'Tytuł artykułu, który nie istnieje',
  KEY `broken_from` (`broken_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista pustych, nieaktywnych linków';

DROP TABLE IF EXISTS `censore`;
CREATE TABLE IF NOT EXISTS `censore` (
  `censore_text` varchar(255) NOT NULL,
  `censore_replacement` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `component`;
CREATE TABLE IF NOT EXISTS `component` (
  `component_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `component_name` varchar(50) NOT NULL,
  `component_text` varchar(100) NOT NULL,
  PRIMARY KEY (`component_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `config_name` varchar(120) NOT NULL COMMENT 'Nazwa klucza',
  `config_value` varchar(255) NOT NULL COMMENT 'Wartość klucza',
  UNIQUE KEY `config_name` (`config_name`),
  UNIQUE KEY `config_name_2` (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Dodatkowe opcje konfiguracyjne projektu ';

DROP TABLE IF EXISTS `connector`;
CREATE TABLE IF NOT EXISTS `connector` (
  `connector_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `connector_module` smallint(5) unsigned NOT NULL,
  `connector_name` varchar(50) NOT NULL,
  `connector_class` varchar(50) NOT NULL,
  `connector_text` varchar(50) NOT NULL,
  `connector_controller` varchar(50) NOT NULL,
  `connector_action` varchar(50) NOT NULL,
  `connector_folder` varchar(50) NOT NULL,
  PRIMARY KEY (`connector_id`),
  KEY `connector_module` (`connector_module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
  `content_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `content_type` varchar(50) NOT NULL,
  PRIMARY KEY (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `email`;
CREATE TABLE IF NOT EXISTS `email` (
  `email_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `email_name` varchar(50) NOT NULL,
  `email_description` varchar(255) NOT NULL,
  `email_text` text NOT NULL,
  `email_subject` varchar(255) NOT NULL,
  `email_format` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`email_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `field`;
CREATE TABLE IF NOT EXISTS `field` (
  `field_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `field_module` smallint(5) unsigned NOT NULL,
  `field_order` smallint(5) unsigned NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_text` varchar(100) NOT NULL,
  `field_description` varchar(255) NOT NULL,
  `field_component` tinyint(3) unsigned NOT NULL,
  `field_required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Określa czy podanie wartości tego pola jest wymagane czy też nie',
  `field_default` varchar(255) NOT NULL,
  `field_display` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `field_profile` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_readonly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_auth` varchar(20) NOT NULL,
  `field_validator` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`field_id`),
  KEY `field_module` (`field_module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
DROP TRIGGER IF EXISTS `onBeforefieldInsert`;
DELIMITER //
CREATE TRIGGER `onBeforefieldInsert` BEFORE INSERT ON `field`
 FOR EACH ROW SET NEW.field_order = (SELECT IFNULL(MAX(field_order), 0) FROM field WHERE field_module = NEW.field_module) + 1
//
DELIMITER ;

DROP TABLE IF EXISTS `field_filter`;
CREATE TABLE IF NOT EXISTS `field_filter` (
  `field_id` smallint(5) unsigned NOT NULL,
  `filter_id` tinyint(3) unsigned NOT NULL,
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Filtry przypisane do danego pola';

DROP TABLE IF EXISTS `field_item`;
CREATE TABLE IF NOT EXISTS `field_item` (
  `item_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `item_field` smallint(5) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_value` varchar(255) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `option_field` (`item_field`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Pozycje w komponentach wielokrotnego wyboru lub listach';

DROP TABLE IF EXISTS `field_option`;
CREATE TABLE IF NOT EXISTS `field_option` (
  `option_field` smallint(5) unsigned NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  UNIQUE KEY `option_unique` (`option_field`,`option_name`),
  KEY `option_field` (`option_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Zawiera niestandardowe opcje dla danego komponentu';

DROP TABLE IF EXISTS `filter`;
CREATE TABLE IF NOT EXISTS `filter` (
  `filter_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `filter_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `filter_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `group`;
CREATE TABLE IF NOT EXISTS `group` (
  `group_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID grupy',
  `group_name` varchar(40) NOT NULL COMMENT 'Nazwa grupy',
  `group_desc` varchar(85) NOT NULL COMMENT 'Krótki opis grupy',
  `group_leader` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika, który jest liderem grupy (najczęściej założyciel)',
  `group_display` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Określa, czy grupa powinna być wyświetlana na liście',
  `group_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Określa, czy grupa jest grupą otwartą',
  `group_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Typ grupy (wartość 0 mają grupy systemowe - nie do usunięcia)',
  UNIQUE KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista grup';
DROP TRIGGER IF EXISTS `onAfterGroupInsert`;
DELIMITER //
CREATE TRIGGER `onAfterGroupInsert` AFTER INSERT ON `group`
 FOR EACH ROW BEGIN
 	INSERT INTO `auth_data` (data_group, data_option, data_value) SELECT NEW.group_id, option_id, option_default FROM `auth_option`;
 
    IF NEW.group_leader > 0 THEN
 		INSERT INTO auth_group (user_id, group_id) VALUES(NEW.group_leader, NEW.group_id);
 		UPDATE `user` SET user_permission = "" WHERE user_id = NEW.group_leader;
    END IF;
  END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterGroupUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterGroupUpdate` AFTER UPDATE ON `group`
 FOR EACH ROW BEGIN
      SELECT user_id INTO @leader FROM `auth_group` WHERE group_id = NEW.group_id AND user_id = NEW.group_leader;
 
      IF @leader = 0 THEN
          INSERT INTO `auth_group` (user_id, group_id) VALUES(NEW.group_leader, NEW.group_id);
          UPDATE `user` SET user_permission = "" WHERE user_id = NEW.group_leader;
      END IF;
  END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onBeforeGroupDelete`;
DELIMITER //
CREATE TRIGGER `onBeforeGroupDelete` BEFORE DELETE ON `group`
 FOR EACH ROW BEGIN
 	SET @groupId = (SELECT group_id FROM `group` WHERE group_name = "USER");
	
 	UPDATE `user` SET user_permission = "", user_group = @groupId WHERE user_id IN(
 		SELECT user_id FROM `auth_group` WHERE group_id = OLD.group_id
 	);
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `location`;
CREATE TABLE IF NOT EXISTS `location` (
  `location_page` int(10) unsigned NOT NULL,
  `location_text` text NOT NULL,
  `location_children` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onAfterLocationInsert`;
DELIMITER //
CREATE TRIGGER `onAfterLocationInsert` AFTER INSERT ON `location`
 FOR EACH ROW BEGIN
 	INSERT INTO accessor (accessor_from, accessor_to) SELECT broken_from, NEW.location_page FROM broken WHERE broken_path = NEW.location_text ON DUPLICATE KEY UPDATE accessor_to = NEW.location_page;
 	DELETE FROM broken WHERE broken_path = NEW.location_text;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterLocationUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterLocationUpdate` AFTER UPDATE ON `location`
 FOR EACH ROW BEGIN
	IF NEW.location_text != OLD.location_text THEN
 
  		INSERT INTO broken (broken_from, broken_path)
  			SELECT accessor_from,
  					 OLD.location_text
  			FROM accessor
  			WHERE accessor_to = OLD.location_page;
 
  		DELETE FROM accessor WHERE accessor_to = OLD.location_page;
 
  		INSERT INTO accessor (accessor_from, accessor_to)
  			SELECT broken_from, OLD.location_page
  			FROM broken
  			WHERE broken_path = NEW.location_text;
 
  		DELETE FROM broken WHERE broken_path = NEW.location_text;
 
   	IF (SELECT redirect_page FROM redirect WHERE redirect_path = OLD.location_text) IS NULL THEN
 	  		INSERT INTO redirect (redirect_path, redirect_page) VALUES(OLD.location_text, NEW.location_page);
 	  	END IF;
 	  	
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterLocationDelete`;
DELIMITER //
CREATE TRIGGER `onAfterLocationDelete` AFTER DELETE ON `location`
 FOR EACH ROW BEGIN
 	INSERT INTO broken (broken_from, broken_path) SELECT accessor_from, OLD.location_text FROM accessor WHERE accessor_to = OLD.location_page;
 	DELETE FROM accessor WHERE accessor_to = OLD.location_page;
END
//
DELIMITER ;

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_user` int(10) unsigned NOT NULL,
  `log_page` int(10) unsigned DEFAULT NULL,
  `log_time` int(10) unsigned NOT NULL,
  `log_type` varchar(255) NOT NULL,
  `log_ip` varchar(15) NOT NULL,
  `log_message` varchar(500) NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_page` (`log_page`),
  KEY `log_type` (`log_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `meta`;
CREATE TABLE IF NOT EXISTS `meta` (
  `meta_page` int(10) unsigned NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  UNIQUE KEY `meta_page` (`meta_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `module`;
CREATE TABLE IF NOT EXISTS `module` (
  `module_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID modułu',
  `module_name` varchar(50) NOT NULL COMMENT 'Nazwa modułu (jednocześnie nazwa katalogu w ktorym się znajduje)',
  `module_text` varchar(100) NOT NULL COMMENT 'Nazwa opisowa modułu',
  `module_version` varchar(10) NOT NULL,
  `module_type` tinyint(1) unsigned NOT NULL COMMENT 'Wartość zero oznacza moduł systemwoy (nei do usunięcia). Domyślnie wartość 1',
  PRIMARY KEY (`module_id`),
  KEY `module_name` (`module_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista modułów włączonych do projektu';

DROP TABLE IF EXISTS `module_config`;
CREATE TABLE IF NOT EXISTS `module_config` (
  `config_module` smallint(5) unsigned NOT NULL,
  `config_field` smallint(5) unsigned NOT NULL,
  `config_page` int(10) unsigned DEFAULT NULL,
  `config_value` varchar(255) NOT NULL,
  KEY `config_module` (`config_module`),
  KEY `config_page` (`config_page`),
  KEY `config_field` (`config_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `module_plugin`;
CREATE TABLE IF NOT EXISTS `module_plugin` (
  `module_id` smallint(5) unsigned NOT NULL COMMENT 'ID modułu',
  `plugin_id` smallint(5) unsigned NOT NULL COMMENT 'ID modułu, który jest przyłączony do modułu module_id',
  KEY `module_id` (`module_id`),
  KEY `plugin_id` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista pluginów dołączonych do modułu';

DROP TABLE IF EXISTS `notify`;
CREATE TABLE IF NOT EXISTS `notify` (
  `notify_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `notify_trigger` varchar(100) NOT NULL,
  `notify_class` varchar(100) NOT NULL,
  `notify_module` smallint(5) unsigned NOT NULL,
  `notify_plugin` smallint(5) unsigned DEFAULT NULL,
  `notify_email` smallint(5) unsigned DEFAULT NULL,
  `notify_name` varchar(100) NOT NULL,
  `notify_message` tinytext NOT NULL,
  `notify_default` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`notify_id`),
  KEY `notify_module` (`notify_module`),
  KEY `notify_plugin` (`notify_plugin`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onAfterNotifyInsert`;
DELIMITER //
CREATE TRIGGER `onAfterNotifyInsert` AFTER INSERT ON `notify`
 FOR EACH ROW BEGIN
 	IF NEW.notify_default > 0 THEN
 		INSERT INTO notify_user (notify_id, user_id, notifier)
 			SELECT NEW.notify_id, user_id, NEW.notify_default
 			FROM `user`;
 	END IF;
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `notify_header`;
CREATE TABLE IF NOT EXISTS `notify_header` (
  `header_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `header_notify` smallint(5) unsigned NOT NULL,
  `header_sender` mediumint(8) unsigned NOT NULL,
  `header_recipient` mediumint(8) unsigned NOT NULL,
  `header_time` int(10) unsigned NOT NULL,
  `header_message` varchar(255) NOT NULL,
  `header_url` varchar(255) NOT NULL,
  `header_read` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`header_id`),
  KEY `header_notify` (`header_notify`),
  KEY `header_recipient` (`header_recipient`),
  KEY `header_url` (`header_url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onAfterNotifyHeaderInsert`;
DELIMITER //
CREATE TRIGGER `onAfterNotifyHeaderInsert` AFTER INSERT ON `notify_header`
 FOR EACH ROW BEGIN
 	UPDATE `user`
 	SET user_notify = (user_notify + 1), user_notify_unread = (user_notify_unread + 1)
 	WHERE user_id = NEW.header_recipient;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterNotifyHeaderUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterNotifyHeaderUpdate` AFTER UPDATE ON `notify_header`
 FOR EACH ROW BEGIN
 	IF NEW.header_read != 0 AND OLD.header_read = 0 THEN
 		UPDATE `user` SET user_notify_unread = user_notify_unread -1
 		WHERE user_id = NEW.header_recipient;
 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterNotifyHeaderDelete`;
DELIMITER //
CREATE TRIGGER `onAfterNotifyHeaderDelete` AFTER DELETE ON `notify_header`
 FOR EACH ROW BEGIN
 	UPDATE `user`
 	SET user_notify = (user_notify -1), user_notify_unread = (user_notify_unread - IF(OLD.header_read > 0, 0, 1))
 	WHERE user_id = OLD.header_recipient;
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `notify_user`;
CREATE TABLE IF NOT EXISTS `notify_user` (
  `notify_id` smallint(5) unsigned NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `notifier` tinyint(3) unsigned NOT NULL DEFAULT '1',
  KEY `notify_id` (`notify_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `page`;
CREATE TABLE IF NOT EXISTS `page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_parent` int(10) unsigned DEFAULT NULL,
  `page_module` smallint(5) unsigned NOT NULL,
  `page_connector` smallint(5) unsigned NOT NULL,
  `page_subject` varchar(255) NOT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `page_path` varchar(255) NOT NULL,
  `page_text` int(10) unsigned DEFAULT NULL,
  `page_depth` smallint(5) unsigned NOT NULL DEFAULT '0',
  `page_order` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `page_matrix` text NOT NULL,
  `page_content` tinyint(3) unsigned NOT NULL,
  `page_publish` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `page_published` datetime DEFAULT NULL,
  `page_unpublished` datetime DEFAULT NULL,
  `page_time` int(10) unsigned NOT NULL,
  `page_edit_time` int(10) unsigned NOT NULL,
  `page_delete` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `page_richtext` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `page_cache` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `page_template` varchar(255) NOT NULL,
  PRIMARY KEY (`page_id`),
  KEY `page_parent` (`page_parent`),
  KEY `page_path` (`page_path`),
  KEY `page_connector` (`page_connector`),
  KEY `page_text` (`page_text`),
  KEY `page_edit_time` (`page_edit_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onBeforePageInsert`;
DELIMITER //
CREATE TRIGGER `onBeforePageInsert` BEFORE INSERT ON `page`
 FOR EACH ROW BEGIN
	IF (NEW.page_parent IS NULL OR NEW.page_parent = 0) THEN
		SET NEW.page_depth = 0;
		
		SET NEW.page_order = (SELECT IFNULL(MAX(page_order), 0) FROM page WHERE page_parent IS NULL) + 1;
		SET NEW.page_matrix = LPAD(NEW.page_order, 9, '0');
	ELSE
		SELECT page_depth, page_matrix INTO @pageDepth, @pageMatrix
		FROM page
		WHERE page_id = NEW.page_parent;
		
		SET @pageOrder = (SELECT IFNULL(MAX(page_order), 0) FROM page WHERE page_parent = NEW.page_parent) + 1;
		
		SET NEW.page_order = @pageOrder;
		SET NEW.page_depth = @pageDepth + 1;
		SET NEW.page_matrix = CONCAT_WS('/', @pageMatrix, LPAD(NEW.page_order, 9, '0'));		
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPageInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPageInsert` AFTER INSERT ON `page`
 FOR EACH ROW BEGIN
	INSERT INTO path (parent_id, child_id, `length`) VALUES(NEW.page_id, NEW.page_id, 0);
	
	INSERT INTO path (parent_id, child_id, `length`) 
	SELECT parent_id, NEW.page_id, `length` + 1 
	FROM path
	WHERE child_id = NEW.page_parent;
		
	INSERT INTO location (location_page, location_text, location_children) VALUES(NEW.page_id, GET_LOCATION(NEW.page_id), 0);
	
	IF NEW.page_parent IS NOT NULL THEN
	
		UPDATE location
		INNER JOIN path ON child_id = NEW.page_parent
		SET location_children = GET_CHILDREN(location_page)
		WHERE location_page = parent_id;
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPageUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterPageUpdate` AFTER UPDATE ON `page`
 FOR EACH ROW BEGIN
 
 	IF NEW.page_path != OLD.page_path THEN
 
 		UPDATE location
		INNER JOIN path ON parent_id = NEW.page_id
		SET location_text = GET_LOCATION(location_page)		
		WHERE location_page = child_id; 
 		
 		DELETE FROM page_template WHERE page_id = OLD.page_id;
 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onBeforePageDelete`;
DELIMITER //
CREATE TRIGGER `onBeforePageDelete` BEFORE DELETE ON `page`
 FOR EACH ROW BEGIN
	IF OLD.page_text IS NOT NULL THEN
 
	  	IF ((SELECT COUNT(*) FROM page WHERE page_text = OLD.page_text) = 1) THEN
	 
	  		DELETE FROM page_text WHERE text_id IN(
	 
	  			SELECT text_id
	  			FROM page_version
	  			WHERE page_id = OLD.page_id
	  		);
	  	END IF;
	END IF;
	
	DELETE FROM page_tag WHERE page_id = OLD.page_id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPageDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPageDelete` AFTER DELETE ON `page`
 FOR EACH ROW BEGIN
	IF OLD.page_depth > 0 THEN 
	
		UPDATE location
		INNER JOIN path ON child_id = OLD.page_parent
		SET location_children = GET_CHILDREN(location_page)
		WHERE location_page = parent_id;
	END IF;
END
//
DELIMITER ;

DROP TABLE IF EXISTS `page_attachment`;
CREATE TABLE IF NOT EXISTS `page_attachment` (
  `text_id` int(10) unsigned NOT NULL,
  `attachment_id` smallint(5) unsigned DEFAULT NULL,
  KEY `text_id` (`text_id`),
  KEY `attachment_id` (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `page_cache`;
CREATE TABLE IF NOT EXISTS `page_cache` (
  `cache_page` int(10) unsigned NOT NULL,
  `cache_content` longtext NOT NULL,
  `cache_time` int(10) unsigned NOT NULL,
  KEY `cache_page` (`cache_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `page_group`;
CREATE TABLE IF NOT EXISTS `page_group` (
  `page_id` int(10) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  KEY `page_id` (`page_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `page_parser`;
CREATE TABLE IF NOT EXISTS `page_parser` (
  `page_id` int(10) unsigned NOT NULL,
  `parser_id` smallint(5) unsigned NOT NULL,
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `page_template`;
CREATE TABLE IF NOT EXISTS `page_template` (
  `text_id` int(10) unsigned NOT NULL COMMENT 'Wersja tekstu',
  `page_id` int(10) unsigned NOT NULL COMMENT 'Szablon',
  KEY `text_id` (`text_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `page_text`;
CREATE TABLE IF NOT EXISTS `page_text` (
  `text_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID wersji tekstu (pole auto_increment), unikalny ID rekordu',
  `text_content` longtext NOT NULL COMMENT 'Zawartość tekstu',
  `text_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Czas utworzenia rewizji',
  `text_log` varchar(200) DEFAULT NULL COMMENT 'Dziennik zmian w wersji',
  `text_user` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika ',
  `text_ip` varchar(40) NOT NULL COMMENT 'IP edycji',
  PRIMARY KEY (`text_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Rewizje artykułów';

DROP TABLE IF EXISTS `page_version`;
CREATE TABLE IF NOT EXISTS `page_version` (
  `page_id` int(10) unsigned NOT NULL,
  `text_id` int(10) unsigned NOT NULL,
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onAfterPageVersionInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPageVersionInsert` AFTER INSERT ON `page_version`
 FOR EACH ROW BEGIN
 	UPDATE page SET page_text = NEW.text_id WHERE page_id = NEW.page_id;
 END
//
DELIMITER ;

CREATE TABLE `page_tag` (
  `page_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  KEY `page_id` (`page_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TRIGGER IF EXISTS `onAfterPageTagInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPageTagInsert` AFTER INSERT ON `page_tag`
 FOR EACH ROW BEGIN
    UPDATE tag SET tag_weight = tag_weight + 1 WHERE tag_id = NEW.tag_id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPageTagDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPageTagDelete` AFTER DELETE ON `page_tag`
 FOR EACH ROW BEGIN
    UPDATE tag SET tag_weight = tag_weight - 1 WHERE tag_id = OLD.tag_id;    
    SET @tagWeight = (SELECT tag_weight FROM tag WHERE tag_id = OLD.tag_id);

    IF (@tagWeight = 0) THEN
        DELETE FROM tag WHERE tag_id = OLD.tag_id;
    END IF;
END
//
DELIMITER ;

DROP TABLE IF EXISTS `parser`;
CREATE TABLE IF NOT EXISTS `parser` (
  `parser_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parser_name` varchar(50) NOT NULL,
  `parser_text` varchar(100) NOT NULL,
  `parser_description` varchar(255) NOT NULL,
  `parser_order` smallint(5) unsigned NOT NULL,
  `parser_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`parser_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `onBeforeParserInsert`;
DELIMITER //
CREATE TRIGGER `onBeforeParserInsert` BEFORE INSERT ON `parser`
 FOR EACH ROW SET NEW.parser_order = (SELECT IFNULL(MAX(parser_order), 0) FROM parser) + 1
//
DELIMITER ;

DROP TABLE IF EXISTS `path`;
CREATE TABLE IF NOT EXISTS `path` (
  `path_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL,
  `child_id` int(10) unsigned NOT NULL,
  `length` int(10) unsigned NOT NULL,
  PRIMARY KEY (`path_id`),
  UNIQUE KEY `tree_parent` (`parent_id`,`child_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `plugin`;
CREATE TABLE IF NOT EXISTS `plugin` (
  `plugin_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(100) NOT NULL,
  `plugin_text` varchar(100) NOT NULL,
  `plugin_version` varchar(10) NOT NULL,
  `plugin_enable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm`;
CREATE TABLE IF NOT EXISTS `pm` (
  `pm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pm_read` int(10) unsigned NULL DEFAULT NULL,
  `pm_subject` varchar(100) NOT NULL,
  `pm_from` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pm_to` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pm_time` int(10) unsigned NOT NULL DEFAULT '0',
  `pm_folder` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `pm_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `pm_trunk` varchar(10) NOT NULL DEFAULT '',
  `pm_text` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pm_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TRIGGER IF EXISTS `onAfterPmInsert`;
DELIMITER //
CREATE TRIGGER `onAfterPmInsert` AFTER INSERT ON `pm`
 FOR EACH ROW BEGIN
 	IF NEW.pm_folder = 1 THEN
 		UPDATE user SET user_pm = user_pm + 1, user_pm_unread = user_pm_unread + 1 WHERE user_id = NEW.pm_to;
 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPmUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterPmUpdate` AFTER UPDATE ON `pm`
 FOR EACH ROW BEGIN
 	IF NEW.pm_read IS NOT NULL AND OLD.pm_read IS NULL AND NEW.pm_folder = 1 THEN
 		UPDATE user SET user_pm_unread = user_pm_unread - 1 WHERE user_id = NEW.pm_to;
 	END IF;
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterPmDelete`;
DELIMITER //
CREATE TRIGGER `onAfterPmDelete` AFTER DELETE ON `pm`
 FOR EACH ROW BEGIN
 	SET @pm = (SELECT COUNT(*) FROM pm WHERE pm_text = OLD.pm_text);
 
 	IF NOT @pm THEN
 	   DELETE FROM pm_text WHERE pm_text = OLD.pm_text;
 	END IF;
 
 	IF OLD.pm_folder = 1 OR OLD.pm_folder = 3 THEN
 	   UPDATE user SET user_pm = user_pm - 1 WHERE user_id = OLD.pm_to;
 
 	   IF OLD.pm_read = 0 OR OLD.pm_read IS NULL THEN
 	       UPDATE user SET user_pm_unread = user_pm_unread - 1 WHERE user_id = OLD.pm_to;
 	   END IF;
 	END IF;
  END
//
DELIMITER ;

DROP TABLE IF EXISTS `pm_text`;
CREATE TABLE IF NOT EXISTS `pm_text` (
  `pm_text` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pm_message` text NOT NULL,
  PRIMARY KEY (`pm_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `redirect`;
CREATE TABLE IF NOT EXISTS `redirect` (
  `redirect_path` tinytext NOT NULL,
  `redirect_page` int(10) unsigned NOT NULL,
  KEY `redirect_page` (`redirect_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `report_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `report_close` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `report_page` int(10) unsigned NOT NULL,
  `report_user` mediumint(8) unsigned NOT NULL,
  `report_email` varchar(50) NOT NULL,
  `report_ip` varchar(15) NOT NULL,
  `report_time` int(10) unsigned NOT NULL,
  `report_message` text NOT NULL,
  `report_section` varchar(100) NOT NULL COMMENT 'Dodatkowe pole, ktore moze byc wykorzystywane przez moduly',
  `report_anchor` varchar(100) NOT NULL COMMENT 'Dodatkowe parametry GET prowadzace do elementu strony z raportem',
  PRIMARY KEY (`report_id`),
  KEY `report_page` (`report_page`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `richtext`;
CREATE TABLE IF NOT EXISTS `richtext` (
  `richtext_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `richtext_name` varchar(50) NOT NULL,
  `richtext_path` varchar(50) NOT NULL,
  PRIMARY KEY (`richtext_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `scheduler`;
CREATE TABLE IF NOT EXISTS `scheduler` (
  `scheduler_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `scheduler_name` varchar(50) NOT NULL,
  `scheduler_description` varchar(255) NOT NULL,
  `scheduler_module` smallint(5) unsigned NOT NULL,
  `scheduler_class` varchar(100) NOT NULL,
  `scheduler_method` varchar(100) NOT NULL,
  `scheduler_frequency` int(10) unsigned DEFAULT NULL COMMENT 'Czestotliwosc (w sekundach) wykonywania danego zadania',
  `scheduler_time` time DEFAULT NULL,
  `scheduler_lunch` int(10) unsigned NOT NULL COMMENT 'Ostatnie wykonanie danej czynnosci',
  `scheduler_enable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `scheduler_lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Wartosc 1 oznacza, ze regula jest w trakcie wykonywania (zadanie jest zablokowane)',
  PRIMARY KEY (`scheduler_id`),
  KEY `scheduler_module` (`scheduler_module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `search`;
CREATE TABLE IF NOT EXISTS `search` (
  `search_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `search_name` varchar(50) NOT NULL,
  `search_class` varchar(50) NOT NULL,
  `search_enable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `search_default` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`search_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Mechanizmy szukania wlaczone/zainstalowane w systemie';

DROP TABLE IF EXISTS `search_queue`;
CREATE TABLE IF NOT EXISTS `search_queue` (
  `page_id` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `search_top10`;
CREATE TABLE IF NOT EXISTS `search_top10` (
  `top10_query` varchar(200) NOT NULL,
  `top10_weight` int(10) unsigned NOT NULL DEFAULT '1',
  UNIQUE KEY `top10_query` (`top10_query`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `session_id` char(32) NOT NULL COMMENT 'Unikalne ID sesji',
  `session_user_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'ID użytkownika',
  `session_ip` varchar(16) NOT NULL COMMENT 'IP użytkownika',
  `session_start` int(11) NOT NULL DEFAULT '0' COMMENT 'Utworzenie sesji (pierwsze logowanie)',
  `session_stop` int(11) NOT NULL DEFAULT '0' COMMENT 'Ostatnia aktywność',
  `session_robot` varchar(100) DEFAULT NULL COMMENT 'Ewentualna nazwa robota sieciowego',
  `session_page` varchar(255) NOT NULL COMMENT 'Strona na której aktualnie przebywa użytkownik',
  `session_browser` varchar(150) NOT NULL COMMENT 'User-agent',
  UNIQUE KEY `session_id` (`session_id`),
  KEY `session_user_id` (`session_user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Lista użytkowników zalogowanych w systemie';
DROP TRIGGER IF EXISTS `onBeforeSessionDelete`;
DELIMITER //
CREATE TRIGGER `onBeforeSessionDelete` BEFORE DELETE ON `session`
 FOR EACH ROW BEGIN
 	UPDATE `user` SET user_lastvisit = OLD.session_stop, user_visits = (user_visits + 1), user_ip = OLD.session_ip WHERE user_id > 0 AND user_id = OLD.session_user_id;
  	INSERT INTO `session_log` VALUES(OLD.session_id, OLD.session_user_id, OLD.session_start, OLD.session_stop, OLD.session_page, OLD.session_ip, OLD.session_robot);
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `session_log`;
CREATE TABLE IF NOT EXISTS `session_log` (
  `log_session` char(32) NOT NULL,
  `log_user` mediumint(8) unsigned NOT NULL,
  `log_start` int(10) unsigned NOT NULL,
  `log_stop` int(10) unsigned NOT NULL,
  `log_page` varchar(255) NOT NULL,
  `log_ip` varchar(16) NOT NULL,
  `log_robot` varchar(100) DEFAULT NULL,
  KEY `log_start` (`log_start`),
  KEY `log_session` (`log_session`),
  KEY `log_ip` (`log_ip`),
  KEY `log_user` (`log_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `snippet`;
CREATE TABLE IF NOT EXISTS `snippet` (
  `snippet_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `snippet_name` varchar(50) NOT NULL DEFAULT '',
  `snippet_text` varchar(255) NOT NULL,
  `snippet_class` varchar(50) DEFAULT NULL,
  `snippet_content` text NOT NULL,
  `snippet_user` mediumint(8) unsigned NOT NULL,
  `snippet_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`snippet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_text` varchar(100) NOT NULL,
  `tag_weight` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_text` (`tag_text`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `trigger`;
CREATE TABLE IF NOT EXISTS `trigger` (
  `trigger_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID triggera',
  `trigger_name` varchar(100) NOT NULL COMMENT 'Nazwa triggera',
  `trigger_header` varchar(255) NOT NULL COMMENT 'Nagłowek zawierający listę parametrów, które będą przekazywane do zdarzenia (dla zaawansowanych)',
  `trigger_description` varchar(255) NOT NULL COMMENT 'Opis działania triggera',
  `trigger_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Typ triggera (0 - specjalny - nie można usunąć)',
  PRIMARY KEY (`trigger_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista triggerów możliwych do wykorzystania ';

DROP TABLE IF EXISTS `trigger_event`;
CREATE TABLE IF NOT EXISTS `trigger_event` (
  `event_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `event_trigger` smallint(5) unsigned NOT NULL COMMENT 'ID triggera (klucz obcy)',
  `event_name` varchar(100) DEFAULT NULL COMMENT 'Nazwa zdarzenia (zalecane jest, aby była to nazwa unikalna w obrębie danego triggera)',
  `event_class` varchar(255) DEFAULT NULL COMMENT 'Nazwa klasy, z której zostanie wywołana metoda (opcjonalnie)',
  `event_function` varchar(255) DEFAULT NULL COMMENT 'Nazwa funkcji lub metody klasy (opcjonalnie)',
  `event_path` varchar(255) DEFAULT NULL COMMENT 'Ścieżka, w której znajduje się plik PHP, który zostanie załadowany',
  `event_params` varchar(255) DEFAULT NULL COMMENT 'Dodatkowy parametr, który zostanie przekazany do metody/funkcji (opcjonalnie)',
  `event_eval` text COMMENT 'Kod PHP, który zostanie wykonany w momencie wystąpienia zdarzenia (opcjonalnie)',
  PRIMARY KEY (`event_id`),
  KEY `event_trigger` (`event_trigger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tabela przechowująca listę zdarzeń dla triggerów';

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID użytkownika. ID = 0 zarezerwowane jest dla użytkownika anonimowego.',
  `user_name` varchar(50) NOT NULL,
  `user_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Wartość 1 oznacza, że konto jest aktywne',
  `user_confirm` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Wartość 1 oznacza, że użytkownik potwierdził autentyczność adresu e-mail',
  `user_group` mediumint(8) unsigned NOT NULL DEFAULT '2' COMMENT 'Domyślna grupa użytkownika',
  `user_permission` mediumtext NOT NULL,
  `user_salt` varchar(34) NOT NULL,
  `user_password` char(64) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_regdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Data utworzenia konta (timestamp)',
  `user_dateformat` varchar(32) NOT NULL DEFAULT '%Y-%m-%d %H:%M',
  `user_flood` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Pole może być wykorzystywane przy blokadzie floodowania',
  `user_lastvisit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Data ostatniej wizyty (timestamp)',
  `user_visits` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_ip` varchar(16) NOT NULL COMMENT 'Ostatnia aktywność (IP)',
  `user_ip_access` varchar(255) NOT NULL DEFAULT '' COMMENT 'Adresy IP z jakich możliwy jest dostęp do danego konta',
  `user_ip_login` varchar(255) NOT NULL COMMENT 'IP ostatniego logowania',
  `user_ip_invalid` varchar(255) DEFAULT NULL COMMENT 'IP ostatniego nieudanego logowania',
  `user_alert_login` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Powiadamianie o udanym logowaniu włączone (1)',
  `user_alert_access` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Powiadomienie o nieudanej próbie dostępu do konta (1)',
  `user_pm` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Ilość wiadomości prywatnych w skrzynce odbiorczej',
  `user_pm_unread` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Liczba nieprzeczytanych wiadomości',
  `user_notify` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Liczba powiadomień',
  `user_notify_unread` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Liczba nieprzeczytanych powiadomień',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tabela przechowuje dane zarejestrowanych użytkowników.';
DROP TRIGGER IF EXISTS `onAfterUserInsert`;
DELIMITER //
CREATE TRIGGER `onAfterUserInsert` AFTER INSERT ON `user`
 FOR EACH ROW BEGIN
 	IF NEW.user_id > 1 THEN
 		SET @group_id = (SELECT group_id FROM `group` WHERE group_name = "USER");
 		INSERT INTO auth_group (user_id, group_id) VALUES(NEW.user_id, @group_id);
 
 		INSERT INTO notify_user (notify_id, user_id, notifier)
 			SELECT notify_id, NEW.user_id, notify_default
 			FROM notify
 			WHERE notify_default > 0;
 	END IF;
 END
//
DELIMITER ;

DROP TABLE IF EXISTS `validator`;
CREATE TABLE IF NOT EXISTS `validator` (
  `validator_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `validator_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `validator_regexp` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `validator_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`validator_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `watch`;
CREATE TABLE IF NOT EXISTS `watch` (
  `page_id` int(10) unsigned NOT NULL COMMENT 'ID strony',
  `user_id` mediumint(8) unsigned NOT NULL COMMENT 'ID użytkownika obserwującego tekst',
  `watch_time` int(10) unsigned NOT NULL,
  `watch_module` smallint(5) unsigned NOT NULL,
  `watch_plugin` smallint(5) unsigned DEFAULT NULL,
  KEY `user_id` (`user_id`),
  KEY `page_id` (`page_id`),
  KEY `watch_module` (`watch_module`),
  KEY `watch_plugin` (`watch_plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista obserwowanych tekstów';
DROP TABLE IF EXISTS `page_v`;

DROP VIEW IF EXISTS `page_v`;
CREATE VIEW `page_v` AS select `page`.`page_id` AS `page_id`,`page`.`page_parent` AS `page_parent`,`page`.`page_module` AS `page_module`,`page`.`page_connector` AS `page_connector`,`page`.`page_subject` AS `page_subject`,`page`.`page_title` AS `page_title`,`page`.`page_text` AS `page_text`,`page`.`page_path` AS `page_path`,`page`.`page_depth` AS `page_depth`,`page`.`page_content` AS `page_content`,`page`.`page_publish` AS `page_publish`,`page`.`page_published` AS `page_published`,`page`.`page_unpublished` AS `page_unpublished`,`page`.`page_delete` AS `page_delete`,`page`.`page_richtext` AS `page_richtext`,`page`.`page_cache` AS `page_cache`,`page`.`page_template` AS `page_template`,`page`.`page_time` AS `page_time`,`page`.`page_edit_time` AS `page_edit_time`,`page_text`.`text_id` AS `text_id`,`page_text`.`text_content` AS `text_content`,`page_text`.`text_time` AS `text_time`,`page_text`.`text_log` AS `text_log`,`page_text`.`text_user` AS `text_user`,`page_text`.`text_ip` AS `text_ip`,`meta`.`meta_page` AS `meta_page`,`meta`.`meta_title` AS `meta_title`,`meta`.`meta_description` AS `meta_description`,`meta`.`meta_keywords` AS `meta_keywords`,`location`.`location_text` AS `location_text`,`location`.`location_children` AS `location_children`,`content`.`content_type` AS `content_type`,`page_cache`.`cache_time` AS `cache_time`,`page_cache`.`cache_content` AS `cache_content` from (((((`page` join `location` on((`location`.`location_page` = `page`.`page_id`))) left join `content` on((`content`.`content_id` = `page`.`page_content`))) left join `page_text` on((`page_text`.`text_id` = `page`.`page_text`))) left join `page_cache` on((`page_cache`.`cache_page` = `page`.`page_id`))) left join `meta` on((`meta`.`meta_page` = `page`.`page_id`)));

ALTER TABLE `accessor`
  ADD CONSTRAINT `accessor_ibfk_1` FOREIGN KEY (`accessor_from`) REFERENCES `page` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accessor_ibfk_2` FOREIGN KEY (`accessor_to`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `actkey`
  ADD CONSTRAINT `actkey_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `auth_data`
  ADD CONSTRAINT `auth_data_ibfk_1` FOREIGN KEY (`data_group`) REFERENCES `group` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auth_data_ibfk_2` FOREIGN KEY (`data_option`) REFERENCES `auth_option` (`option_id`) ON DELETE CASCADE;

ALTER TABLE `auth_group`
  ADD CONSTRAINT `auth_group_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auth_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE;

ALTER TABLE `block`
  ADD CONSTRAINT `block_ibfk_5` FOREIGN KEY (`block_trigger`) REFERENCES `trigger` (`trigger_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `block_ibfk_6` FOREIGN KEY (`block_item`) REFERENCES `block_item` (`item_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `block_ibfk_7` FOREIGN KEY (`block_plugin`) REFERENCES `plugin` (`plugin_id`) ON DELETE SET NULL;

ALTER TABLE `block_group`
  ADD CONSTRAINT `block_group_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `block_group_ibfk_2` FOREIGN KEY (`block_id`) REFERENCES `block` (`block_id`) ON DELETE CASCADE;

ALTER TABLE `block_item`
  ADD CONSTRAINT `block_item_ibfk_1` FOREIGN KEY (`item_plugin`) REFERENCES `plugin` (`plugin_id`) ON DELETE CASCADE;

ALTER TABLE `broken`
  ADD CONSTRAINT `broken_ibfk_1` FOREIGN KEY (`broken_from`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `connector`
  ADD CONSTRAINT `connector_ibfk_1` FOREIGN KEY (`connector_module`) REFERENCES `module` (`module_id`) ON DELETE CASCADE;

ALTER TABLE `field`
  ADD CONSTRAINT `field_ibfk_1` FOREIGN KEY (`field_module`) REFERENCES `module` (`module_id`) ON DELETE CASCADE;

ALTER TABLE `field_filter`
  ADD CONSTRAINT `field_filter_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `field` (`field_id`) ON DELETE CASCADE;

ALTER TABLE `field_item`
  ADD CONSTRAINT `field_item_ibfk_1` FOREIGN KEY (`item_field`) REFERENCES `field` (`field_id`) ON DELETE CASCADE;

ALTER TABLE `field_option`
  ADD CONSTRAINT `field_option_ibfk_1` FOREIGN KEY (`option_field`) REFERENCES `field` (`field_id`) ON DELETE CASCADE;

ALTER TABLE `location`
  ADD CONSTRAINT `location_ibfk_1` FOREIGN KEY (`location_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `meta`
  ADD CONSTRAINT `meta_ibfk_1` FOREIGN KEY (`meta_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `module_config`
  ADD CONSTRAINT `module_config_ibfk_1` FOREIGN KEY (`config_module`) REFERENCES `module` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_config_ibfk_2` FOREIGN KEY (`config_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_config_ibfk_3` FOREIGN KEY (`config_field`) REFERENCES `field` (`field_id`) ON DELETE CASCADE;

ALTER TABLE `module_plugin`
  ADD CONSTRAINT `module_plugin_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_plugin_ibfk_2` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`plugin_id`) ON DELETE CASCADE;

ALTER TABLE `notify`
  ADD CONSTRAINT `notify_ibfk_1` FOREIGN KEY (`notify_module`) REFERENCES `module` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notify_ibfk_2` FOREIGN KEY (`notify_plugin`) REFERENCES `plugin` (`plugin_id`) ON DELETE CASCADE;

ALTER TABLE `notify_header`
  ADD CONSTRAINT `notify_header_ibfk_1` FOREIGN KEY (`header_notify`) REFERENCES `notify` (`notify_id`) ON DELETE CASCADE;

ALTER TABLE `notify_user`
  ADD CONSTRAINT `notify_user_ibfk_1` FOREIGN KEY (`notify_id`) REFERENCES `notify` (`notify_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notify_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `page`
  ADD CONSTRAINT `page_ibfk_1` FOREIGN KEY (`page_connector`) REFERENCES `connector` (`connector_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_ibfk_2` FOREIGN KEY (`page_parent`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `page_attachment`
  ADD CONSTRAINT `page_attachment_ibfk_2` FOREIGN KEY (`attachment_id`) REFERENCES `attachment` (`attachment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_attachment_ibfk_3` FOREIGN KEY (`text_id`) REFERENCES `page_text` (`text_id`) ON DELETE CASCADE;

ALTER TABLE `page_cache`
  ADD CONSTRAINT `page_cache_ibfk_1` FOREIGN KEY (`cache_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `page_group`
  ADD CONSTRAINT `page_group_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE;

ALTER TABLE `page_parser`
  ADD CONSTRAINT `page_parser_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `page_template`
  ADD CONSTRAINT `page_template_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_template_ibfk_3` FOREIGN KEY (`text_id`) REFERENCES `page_text` (`text_id`) ON DELETE CASCADE;

ALTER TABLE `page_version`
  ADD CONSTRAINT `page_version_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `path`
  ADD CONSTRAINT `path_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `path_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `redirect`
  ADD CONSTRAINT `redirect_ibfk_1` FOREIGN KEY (`redirect_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`report_page`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `scheduler`
  ADD CONSTRAINT `scheduler_ibfk_1` FOREIGN KEY (`scheduler_module`) REFERENCES `module` (`module_id`) ON DELETE CASCADE;

ALTER TABLE `trigger_event`
  ADD CONSTRAINT `trigger_event_ibfk_1` FOREIGN KEY (`event_trigger`) REFERENCES `trigger` (`trigger_id`) ON DELETE CASCADE;

ALTER TABLE `watch`
  ADD CONSTRAINT `watch_ibfk_4` FOREIGN KEY (`watch_plugin`) REFERENCES `plugin` (`plugin_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watch_ibfk_5` FOREIGN KEY (`watch_module`) REFERENCES `module` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watch_ibfk_6` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;

ALTER TABLE `page_tag`
  ADD CONSTRAINT `page_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_tag_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE;
  
DELIMITER $$
CREATE  PROCEDURE `CLEAR_CACHE`(IN `pageId` INT)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
 	/* usuniecie tekstu z cache */
 	DELETE FROM page_cache WHERE cache_page = pageId;
 	/* jezeli dana strona jest szablonem, usuniecie z cache tekstow, ktore uzywaja tego szablonu */
 	DELETE FROM page_cache WHERE cache_page IN(
	 	
	 	SELECT page_id FROM page 
	 	WHERE page_text IN(
	 	
			SELECT text_id FROM page_template 
			WHERE page_id = pageId
		)
	);
END$$

CREATE  FUNCTION `GET_CHILDREN`(`pageId` INT)
	RETURNS smallint(6)
	LANGUAGE SQL
	DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	RETURN (
	
		SELECT COUNT(*) -1
		FROM path
		WHERE parent_id = pageId
	);
END$$

CREATE  FUNCTION `GET_DEPTH`(`pageId` INT)
	RETURNS mediumint(9)
	LANGUAGE SQL
	DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	RETURN (
		SELECT COUNT(*) -1
		FROM path
		WHERE child_id = pageId
	);
END$$

CREATE  FUNCTION `GET_LOCATION`(`pageId` INT)
	RETURNS text
	LANGUAGE SQL
	NOT DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	RETURN (	
		SELECT GROUP_CONCAT(page_path ORDER BY `length` DESC SEPARATOR '/')
		FROM path
		INNER JOIN page ON page_id = parent_id
		WHERE child_id = pageId
	); 
END$$

CREATE  FUNCTION `GET_MATRIX`(`pageId` INT)
	RETURNS text
	LANGUAGE SQL
	DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	RETURN (
		SELECT GROUP_CONCAT(LPAD(page_order, 9, '0') ORDER BY `length` DESC SEPARATOR '/')
		FROM path
		INNER JOIN page ON page_id = parent_id
		WHERE child_id = pageId
	);
END$$

CREATE  FUNCTION `GET_MODULE_ID`(`moduleName` VARCHAR(100))
	RETURNS smallint(6)
	LANGUAGE SQL
	DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	SELECT module_id INTO @moduleId FROM module WHERE module_name = moduleName;
	RETURN @moduleId;
END$$

CREATE  FUNCTION `GET_PLUGIN_ID`(`pluginName` VARCHAR(100))
	RETURNS smallint(6)
	LANGUAGE SQL
	DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	SELECT plugin_id INTO @pluginId FROM plugin WHERE plugin_name = pluginName;
	RETURN @pluginId;
END$$

CREATE  FUNCTION `PAGE_ACCESS`(`pageId` INT, `userId` MEDIUMINT)
	RETURNS tinyint(1)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
 	SET @result = (
 
 		SELECT COUNT(g.user_id)
 		FROM page_group p
 		JOIN auth_group g ON g.group_id = p.group_id AND g.user_id = userId
 		WHERE p.page_id = pageId
 	);
 
 	RETURN IF(@result > 0, TRUE, FALSE);
END$$

CREATE  PROCEDURE `PAGE_MOVE`(IN `pageId` INT, IN `parentId` INT)
	LANGUAGE SQL
	NOT DETERMINISTIC
	MODIFIES SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN	
	-- pobranie ID rodzica oraz polozenuia (order) strony
	SELECT page_parent, page_order INTO @pageParent, @pageOrder
	FROM page
	WHERE page_id = pageId;
	
	-- jezeli ID rodzicow jest rozne, wiemy, ze mamy przenisc a nie zmienic kolejnosc
	IF (!(@pageParent <=> parentId)) THEN
		
		-- pobranie nowej wartosci order
		SELECT MAX(page_order) INTO @maxOrder
		FROM page 
		WHERE page_parent <=> parentId;
		
		-- uaktualnienie wiersza
		UPDATE page 
		SET page_parent = parentId, page_order = IFNULL(@maxOrder, 0) + 1
		WHERE page_id = pageId;
		
		IF @pageParent IS NOT NULL THEN
			
			CREATE TEMPORARY TABLE temp_tree AS
			SELECT t2.path_id FROM path t1 
			JOIN path t2 on t1.child_id = t2.child_id
	      WHERE t1.parent_id = pageId AND t2.`length` > t1.`length`;
			
			DELETE FROM path WHERE path_id IN (SELECT * FROM temp_tree);
			DROP TABLE temp_tree;
			
			-- uaktualnienie liczby okreslajacej "dzieci" w galeziach macierzystych
			UPDATE location
			INNER JOIN path ON child_id = @pageParent
			SET location_children = GET_CHILDREN(location_page)			
			WHERE location_page = parent_id;
	     
		END IF;
			
		IF parentId IS NOT NULL THEN -- przenosimy galaz do innej galezi glownej
			
			INSERT INTO path (parent_id, child_id, `length`)
			SELECT t1.parent_id, t2.child_id, t1.`length` + t2.`length` + 1
			FROM path t1, path t2
			WHERE t1.child_id = parentId AND t2.parent_id = pageId;
			
			UPDATE location
			INNER JOIN path ON child_id = parentId
			SET location_children = GET_CHILDREN(location_page)			
			WHERE location_page = parent_id;
		END IF;
				
		-- uaktualnienie danych w galeziach - dzieciach
		UPDATE page, location
		INNER JOIN path AS t ON t.parent_id = pageId
		SET location_text = GET_LOCATION(page_id), page_depth = GET_DEPTH(page_id), page_matrix = GET_MATRIX(page_id)
		WHERE page_id = t.child_id AND location_page = t.child_id;
			
	END IF;	
END$$

CREATE  PROCEDURE `PAGE_ORDER`(IN `pageId` INT, IN `pageOrder` SMALLINT)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN	
	-- pobranie aktualnej pozycji danej strony
	SELECT page_parent, page_order INTO @pageParent, @pageOrder
	FROM page
	WHERE page_id = pageId;
	
	IF !(pageOrder <=> @pageOrder) THEN
		
		-- strona na ktora zamienimy sie miejscami
		SELECT page_id INTO @currPageId
		FROM page
		WHERE page_parent <=> @pageParent AND page_order = pageOrder;
		
		IF @currPageId IS NOT NULL THEN
		
			START TRANSACTION;
			
			UPDATE page AS p1, page AS p2                   
			SET p1.page_order = pageOrder, p2.page_order = @pageOrder
			WHERE	p1.page_parent <=> @pageParent 
				AND p2.page_parent <=> @pageParent
					AND p1.page_id = pageId 
						AND p2.page_id = @currPageId;
			
			UPDATE page 
			INNER JOIN path ON parent_id = pageId
			SET page_matrix = GET_MATRIX(child_id)
			WHERE page_id = child_id;
			
			UPDATE page
			INNER JOIN path ON parent_id = @currPageId
			SET page_matrix = GET_MATRIX(child_id)
			WHERE page_id = child_id;
			
			COMMIT;
		
		
		END IF;
	END IF; 
END$$

CREATE  PROCEDURE `SET_CONFIG`(IN `configKey` VARCHAR(100), IN `configValue` VARCHAR(255))
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
 	INSERT INTO config (config_name, config_value) VALUES(configKey, configValue) ON DUPLICATE KEY UPDATE config_value = configValue;
END$$