<?php

namespace LeadingSystems\Helpers;

require_once(__DIR__.'/../functions.php');
ls_toggleLogClass();

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('LeadingSystems\Helpers\ls_helpers_customInserttags', 'customInserttags');

$GLOBALS['FE_MOD']['ls_helpers'] = array(
	'ModuleFlexWidgetTest' => 'LeadingSystems\Helpers\ModuleFlexWidgetTest',
);