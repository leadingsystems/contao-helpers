<?php

namespace LeadingSystems\Helpers;

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('LeadingSystems\Helpers\ls_helpers_customInserttags', 'customInserttags');

$GLOBALS['FE_MOD']['ls_helpers'] = array(
	'ModuleFlexWidgetTest' => 'LeadingSystems\Helpers\ModuleFlexWidgetTest',
);

array_insert(
    $GLOBALS['TL_CTE']['texts'],
    array_search('html', array_keys($GLOBALS['TL_CTE']['texts'])) + 1,
    [
        'htmlWrapperStart' => 'LeadingSystems\Helpers\ContentHtmlWrapperStart',
        'htmlWrapperStop' => 'LeadingSystems\Helpers\ContentHtmlWrapperStop',
    ]
);

$GLOBALS['TL_WRAPPERS']['start'][] = 'htmlWrapperStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'htmlWrapperStop';