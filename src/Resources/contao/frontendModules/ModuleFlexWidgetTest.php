<?php

namespace LeadingSystems\Helpers;

use Contao\System;

class ModuleFlexWidgetTest extends \Module
{
	public function generate()
	{
        $container = System::getContainer();
        $request = $container->get('request_stack')->getCurrentRequest();

        if($request && $container->get('contao.routing.scope_matcher')->isBackendRequest($request))
        {
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### LS FlexWidget Test ###';
			return $objTemplate->parse();
		}
		return parent::generate();
	}

	public function compile()
	{
		$this->Template = new \FrontendTemplate('ls_flexWidget_frontendModuleTest');

        $session = \System::getContainer()->get('contaoHelpers.session')->getSession();
        $session_leadingSystems =  $session->get('leadingSystems', []);

		$obj_tfw_firstname = new FlexWidget(
			array(
				'str_uniqueName' => 'tfw_firstname',
				'arr_validationFunctions' => array(
					array(
						'str_className' => 'LeadingSystems\Helpers\FlexWidgetValidator',
						'str_methodName' => 'isFilled'
					)
				),
				'str_label' => 'Vorname',
				'str_allowedRequestMethod' => 'post',
				'arr_moreData' => array(
					'headline' => 'Gib Deinen Vornamen hier ein:'
				),
				'var_value' => isset($session_leadingSystems['test']['tfw_firstname']) ? $session_leadingSystems['test']['tfw_firstname'] : ''
			)
		);

		$obj_tfw_lastname = new FlexWidget(
			array(
				'str_uniqueName' => 'tfw_lastname',
				'arr_validationFunctions' => array(
					array(
						'str_className' => 'LeadingSystems\Helpers\FlexWidgetValidator',
						'str_methodName' => 'isFilled'
					)
				),
				'str_label' => 'Nachname',
				'str_allowedRequestMethod' => 'post',
				'arr_moreData' => array(
					'headline' => 'Gib Deinen Nachnamen hier ein:'
				),
				'var_value' => isset($session_leadingSystems['test']['tfw_lastname']) ? $session_leadingSystems['test']['tfw_lastname'] : ''
			)
		);

		if (\Input::post('FORM_SUBMIT') == 'testFlexWidget') {
			if (
				!$obj_tfw_firstname->bln_hasErrors
				&& !$obj_tfw_lastname->bln_hasErrors
			) {
                $session_leadingSystems['test']['tfw_firstname'] = $obj_tfw_firstname->getValue();
                $session_leadingSystems['test']['tfw_lastname'] = $obj_tfw_lastname->getValue();
                $session->set('leadingSystems', $session_leadingSystems);
				\Controller::reload();
			}
		}

		$this->Template->str_tfw_firstname = $obj_tfw_firstname->getOutput();
		$this->Template->str_tfw_lastname = $obj_tfw_lastname->getOutput();
	}
}