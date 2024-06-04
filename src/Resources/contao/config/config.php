<?php

namespace LeadingSystems\Helpers;

use Contao\System;
use Symfony\Component\HttpFoundation\Request;

$GLOBALS['FE_MOD']['ls_helpers'] = array(
	'ModuleFlexWidgetTest' => 'LeadingSystems\Helpers\ModuleFlexWidgetTest',
);

if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['LS_API_HOOKS']['apiReceiver_processRequest'][] = array('LeadingSystems\Helpers\ls_shop_apiController_findDoubleFlexContents', 'processRequest');
}

/*
 * @toDo Remove this Content Element. An LSCE is created as a replacement.
 * LSBoard Task 6349
 */
//ArrayUtil::arrayInsert(
//    $GLOBALS['TL_CTE']['texts'],
//    array_search('html', array_keys($GLOBALS['TL_CTE']['texts'])) + 1,
//    [
//        'htmlWrapperStart' => 'LeadingSystems\Helpers\ContentHtmlWrapperStart',
//        'htmlWrapperStop' => 'LeadingSystems\Helpers\ContentHtmlWrapperStop',
//    ]
//);
//
//$GLOBALS['TL_WRAPPERS']['start'][] = 'htmlWrapperStart';
//$GLOBALS['TL_WRAPPERS']['stop'][] = 'htmlWrapperStop';