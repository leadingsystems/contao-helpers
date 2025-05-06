<?php

namespace LeadingSystems\HelpersBundle\InsertTag\AsInsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\Input;

#[AsInsertTag('ls_post')]
class Post extends InsertTag
{
	public function customInserttags($strTag, $params) {

        return (string) Input::post($params[0]);
	}
}
