<?php

namespace LeadingSystems\Helpers;

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ls_helpers_segmentizer'] = array
(
    'config' => array(
        'dataContainer' => DC_Table::class,
        'sql' => array
        (
            'keys' => array
            (
                'segmentationToken'   => 'primary'
            )
        )

	),
	'fields' => array
    (
        'segmentationToken' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
        'info' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
        'tstampLastCall' => array
        (
		    'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
        'tstampExpiration' => array
        (
		    'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
        'numSegmentsTotal' => array
        (
		    'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
        'lastSegment' => array
        (
		    'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
        'currentTurn' => array
        (
		    'sql'                     => "int(10) unsigned NOT NULL default '1'"
		),
        'nextCallIsNewTurn' => array
        (
		    'sql'                     => "char(1) unsigned NOT NULL default ''"
		)
	)
);
