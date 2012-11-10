CREATE TABLE pastebin (
  pastebin_id mediumint(8) unsigned NOT NULL auto_increment,
  pastebin_user mediumint(8) unsigned NOT NULL,
  pastebin_username varchar(100) NOT NULL,
  pastebin_time int(11) NOT NULL default '0',
  pastebin_expire int(11) NOT NULL default '0',
  pastebin_content text NOT NULL,
  pastebin_cache text NOT NULL,
  pastebin_syntax varchar(40) NOT NULL default '',
  pastebin_prev mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (pastebin_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;