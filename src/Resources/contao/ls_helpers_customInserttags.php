<?php

namespace LeadingSystems\Helpers;

class ls_helpers_customInserttags extends \Controller {
	public function __construct() {
		parent::__construct();
	}

	public function customInserttags($strTag) {
		if (!preg_match('/ls_([^:]*)(::(.*))?$/', $strTag, $matches)) {
			return false;
		}
		$tag = isset($matches[1]) ? $matches[1] : '';
		$params = isset($matches[3]) ? $matches[3] : '';

		switch ($tag) {
			case 'get':
				return \Input::get($params);
				break;
			
			case 'post':
				return \Input::post($params);
				break;

			case 'text':
				$arr_matches = array();
				preg_match_all('/\[\'(.*)\'\]/U', $params, $arr_matches);

				$arr_textPathParts = $arr_matches[1];

				if (!count($arr_textPathParts)) {
					return $params;
				}

				$var_text = $GLOBALS['TL_LANG'];

				foreach ($arr_textPathParts as $str_textPathPart) {
					if ($str_textPathPart === 'TL_LANG') {
						continue;
					}

					if (!key_exists($str_textPathPart, $var_text)) {
						return $params;
					}

					$var_text = $var_text[$str_textPathPart];
				}

				return $var_text;
				break;
		}

		return false;
	}
}