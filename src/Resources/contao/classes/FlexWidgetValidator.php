<?php

namespace LeadingSystems\Helpers;

class FlexWidgetValidator {
	public static function isFilled(FlexWidget $obj_flexWidget) {
		if (!$obj_flexWidget->getValue()) {
			throw new \Exception('field empty');
		}
	}

}