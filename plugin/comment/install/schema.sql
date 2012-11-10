CREATE TABLE IF NOT EXISTS `comment` (
  comment_id mediumint(8) unsigned NOT NULL auto_increment COMMENT 'Unikalne ID komentarza',
  comment_module smallint(5) unsigned NOT NULL,
  comment_page int(10) unsigned default NULL,
  comment_user mediumint(8) unsigned NOT NULL COMMENT 'ID użytkownika, który napisał komentarz',
  comment_username varchar(50) NOT NULL COMMENT 'Nazwa użytkownika jeśli włączona jest możliwość pisania dla anonimów',
  comment_time int(10) unsigned NOT NULL default '0' COMMENT 'Data napisania komentarza (timestamp)',
  comment_content text NOT NULL COMMENT 'Treść komentarza',
  comment_ip varchar(15) NOT NULL COMMENT 'IP użytkownika',
  PRIMARY KEY  (comment_id),
  KEY comment_module (comment_module),
  KEY comment_page (comment_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Moduł komentarzy';

ALTER TABLE `comment`
  ADD CONSTRAINT comment_ibfk_2 FOREIGN KEY (comment_page) REFERENCES `page` (page_id) ON DELETE CASCADE,
  ADD CONSTRAINT comment_ibfk_1 FOREIGN KEY (comment_module) REFERENCES module (module_id) ON DELETE CASCADE;