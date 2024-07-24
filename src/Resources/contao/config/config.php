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
