<?php

namespace LeadingSystems\Helpers;

use Contao\ArrayUtil;

$GLOBALS['FE_MOD']['ls_helpers'] = array(
	'ModuleFlexWidgetTest' => 'LeadingSystems\Helpers\ModuleFlexWidgetTest',
);

ArrayUtil::arrayInsert(
    $GLOBALS['TL_CTE']['texts'],
    array_search('html', array_keys($GLOBALS['TL_CTE']['texts'])) + 1,
    [
        'htmlWrapperStart' => 'LeadingSystems\Helpers\ContentHtmlWrapperStart',
        'htmlWrapperStop' => 'LeadingSystems\Helpers\ContentHtmlWrapperStop',
    ]
);

$GLOBALS['TL_WRAPPERS']['start'][] = 'htmlWrapperStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'htmlWrapperStop';

if (TL_MODE === 'BE') {
    $GLOBALS['LS_API_HOOKS']['apiReceiver_processRequest'][] = array('LeadingSystems\Helpers\ls_shop_apiController_findDoubleFlexContents', 'processRequest');
}