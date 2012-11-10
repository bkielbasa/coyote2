SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `profiler` (
  `profiler_page` tinytext NOT NULL,
  `profiler_time` float NOT NULL,
  `profiler_sql` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `profiler_sql` (
  `sql_id` char(32) NOT NULL,
  `sql_query` tinytext NOT NULL,
  `sql_time` float NOT NULL,
  KEY `sql_id` (`sql_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;