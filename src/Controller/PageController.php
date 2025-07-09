<?php

namespace LeadingSystems\HelpersBundle\Controller;

use Contao\PageModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageController extends AbstractController
{
    private static array $cache_pageModel = [];

    public static function getPageDetails(int $pageId): ?PageModel {
        if (!isset(self::$cache_pageModel[$pageId])) {
            self::$cache_pageModel[$pageId] = PageModel::findWithDetails($pageId);
        }
        return self::$cache_pageModel[$pageId];
    }
}

