SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `menu` (
  `menu_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unikalne ID menu',
  `menu_name` varchar(100) NOT NULL COMMENT 'Nazwa menu',
  `menu_auth` varchar(20) NOT NULL COMMENT 'Dodatkowe uprawnienie potrzebne, aby wyświetlić menu',
  `menu_tag` varchar(10) NOT NULL DEFAULT 'ul' COMMENT 'Znacznik HTML odpowiedzialny za wyświetlanie listy',
  `menu_attributes` text NOT NULL COMMENT 'Serializowana tablica zawierająca dodatkowe atrybuty dla znacznika HTML',
  `menu_separator` varchar(255) NOT NULL COMMENT 'Znaki stanowiące separator dla pozycji menu (opcjonalnie)',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Moduł menu';
DROP TRIGGER IF EXISTS `onAfterMenuInsert`;
DELIMITER //
CREATE TRIGGER `onAfterMenuInsert` AFTER INSERT ON `menu`
 FOR EACH ROW BEGIN
	INSERT INTO block_item (item_plugin, item_data, item_text) SELECT GET_PLUGIN_ID('menu'), NEW.menu_id, NEW.menu_name;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterMenuUpdate`;
DELIMITER //
CREATE TRIGGER `onAfterMenuUpdate` AFTER UPDATE ON `menu`
 FOR EACH ROW BEGIN
	UPDATE block_item SET item_text = NEW.menu_name WHERE item_plugin = GET_PLUGIN_ID('menu') AND item_data = NEW.menu_id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `onAfterMenuDelete`;
DELIMITER //
CREATE TRIGGER `onAfterMenuDelete` AFTER DELETE ON `menu`
 FOR EACH ROW BEGIN
	DELETE FROM block_item WHERE item_plugin = GET_PLUGIN_ID('menu') AND item_data = OLD.menu_id;
END
//
DELIMITER ;

CREATE TABLE `menu_group` (
  `group_id` mediumint(8) unsigned NOT NULL COMMENT 'ID grupy',
  `item_id` smallint(5) unsigned NOT NULL COMMENT 'ID pozycji z tabeli coyote_menu_item',
  KEY `group_id` (`group_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Uprawnienia do wyświetlenia pozycji menu';

CREATE TABLE `menu_item` (
  `item_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID pozycji',
  `item_menu` smallint(5) unsigned NOT NULL COMMENT 'ID menu (klucz obcy)',
  `item_parent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `left_id` smallint(5) unsigned NOT NULL,
  `right_id` smallint(5) unsigned NOT NULL,
  `item_name` varchar(500) NOT NULL COMMENT 'Etykieta pozycji menu',
  `item_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Wartość 1 określa, że pozycja będzie aktywna (wyświetlana)',
  `item_tag` varchar(10) NOT NULL DEFAULT 'li' COMMENT 'Znacznik HTML odpowiadający za wyświetlenie pozycji',
  `item_auth` varchar(20) NOT NULL COMMENT 'Dodatkowe uprawnienie potrzebne, aby wyświetlić tę pozycję menu',
  `item_description` varchar(255) NOT NULL COMMENT 'Opis pozycji menu (wyświetlany w atrybucie title odnośnika)',
  `item_attributes` text NOT NULL COMMENT 'Serializowana tablica dodatkowych atrybutów dla pozycji menu',
  `item_path` varchar(255) NOT NULL COMMENT 'Ścieżka lub URL do którego prowadzić ma odnośbnik',
  `item_focus` varchar(20) NOT NULL COMMENT 'Nazwa klasy CSS ktora bedzie ustawiana jezeli pozycja jest aktywna',
  PRIMARY KEY (`item_id`),
  KEY `item_menu` (`item_menu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Lista pozycji danego menu';


ALTER TABLE `menu_group`
  ADD CONSTRAINT `menu_group_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_group_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_item` (`item_id`) ON DELETE CASCADE;

ALTER TABLE `menu_item`
  ADD CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`item_menu`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;