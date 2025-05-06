<?php

namespace LeadingSystems\HelpersBundle\InsertTag\AsInsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\Input;

#[AsInsertTag('ls_get')]
class Get extends InsertTag
{

	public function customInserttags($strTag, $params) {

        return (string) Input::get($params[0]);
	}
}
