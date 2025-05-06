<?php

namespace LeadingSystems\HelpersBundle\InsertTag\AsInsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;

#[AsInsertTag('ls_text')]
class Text extends InsertTag
{
	public function customInserttags($strTag, $params) {

        $param = $params[0];

        $arr_matches = array();
        preg_match_all('/\[\'(.*)\'\]/U', html_entity_decode($param, ENT_QUOTES), $arr_matches);
        $arr_textPathParts = $arr_matches[1] ?? [];

        $value = $param;

        if (count($arr_textPathParts)) {
            $var_text = $GLOBALS['TL_LANG'];
            $pathFound = true;

            foreach ($arr_textPathParts as $str_textPathPart) {
                if ($str_textPathPart === 'TL_LANG') {
                    continue;
                }
                if (!is_array($var_text) || !key_exists($str_textPathPart, $var_text)) {
                    $pathFound = false;
                    break;
                }
                $var_text = $var_text[$str_textPathPart];
            }

            if ($pathFound) {
                $value = (string) $var_text;
            } else {
                $value = $param;
            }
        }

        return $value;
    }

}
