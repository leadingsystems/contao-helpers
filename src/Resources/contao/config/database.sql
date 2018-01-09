CREATE TABLE `tl_ls_helpers_segmentizer` (
  `segmentationToken` varchar(255) NOT NULL default '',
  `info` varchar(255) NOT NULL default '',
	`tstampLastCall` int(10) unsigned NOT NULL default '0',
	`tstampExpiration` int(10) unsigned NOT NULL default '0',
	`numSegmentsTotal` int(10) unsigned NOT NULL default '0',
	`lastSegment` int(10) unsigned NOT NULL default '0',
	`currentTurn` int(10) unsigned NOT NULL default '1',
	`nextCallIsNewTurn` char(1) NOT NULL default ''
  PRIMARY KEY  (`segmentationToken`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;