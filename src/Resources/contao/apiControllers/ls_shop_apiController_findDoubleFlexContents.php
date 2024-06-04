<?php

namespace LeadingSystems\Helpers;

use Contao\Database;
use Contao\Input;
use Merconis\Core\ls_shop_languageHelper;

class ls_shop_apiController_findDoubleFlexContents
{
	protected static $objInstance;

	/** @var \LeadingSystems\Api\ls_apiController $obj_apiReceiver */
	protected $obj_apiReceiver = null;

	protected function __construct() {}

	private function __clone()
	{
	}

	public static function getInstance()
	{
		if (!is_object(self::$objInstance)) {
			self::$objInstance = new self();
		}

		return self::$objInstance;
	}

	public function processRequest($str_resourceName, $obj_apiReceiver)
	{
		if (!$str_resourceName || !$obj_apiReceiver) {
			return;
		}

		$this->obj_apiReceiver = $obj_apiReceiver;

		/*
		 * If this class has a method that matches the resource name, we call it.
		 * If not, we don't do anything because another class with a corresponding
		 * method might have a hook registered.
		 */
		if (method_exists($this, $str_resourceName)) {
			$this->{$str_resourceName}();
		}
	}

	/**
	 * Find Double FlexContents, optional parameter: limit
	 */
	protected function apiResource_findDoubleFlexContents()
	{
        //limit both product and variant, limit=10 means max.10 products and max.10 variants
		$limit = Input::get('limit') ? intval(Input::get('limit')) : null;


        $this->obj_apiReceiver->success();
        $this->obj_apiReceiver->set_data(array(
            'info' => 'every product and variant with double flexcontent',
            'ProductsWithDoubleFlexKey' => $this->getIdsWithDoubleFlexContents('tl_ls_shop_product', $limit),
            'VariantsWithDoubleFlexKey' => $this->getIdsWithDoubleFlexContents('tl_ls_shop_variant', $limit)
        ));
	}

    protected function getIdsWithDoubleFlexContents($tbTable, $limit)
    {
        $productIdsWithArrayFlexContent = [];

        $objMethod = "";

        $languages = ls_shop_languageHelper::getAllLanguages();

        $strSelect = "";

        foreach( $languages as $language){
            $strSelect .= ", flex_contents_".$language;
        }

        if($limit){
            $limit = " LIMIT ".$limit;
        }

        if($tbTable == 'tl_ls_shop_product') {

            $objMethod = Database::getInstance()->execute("
                SELECT		id, flex_contentsLanguageIndependent".$strSelect."
                FROM		tl_ls_shop_product
                ".$limit."
                ");
        }
        if($tbTable == 'tl_ls_shop_variant') {
            $objMethod = Database::getInstance()->execute("
                SELECT		id, pid, flex_contentsLanguageIndependent".$strSelect."
                FROM		tl_ls_shop_variant
                ".$limit."
                ");
        }

        $arrProducts = $objMethod->fetchAllAssoc();

        foreach( $arrProducts as $productInfo){

            $arrKeyFlexContents = [];

            foreach( $languages as $language) {
                $arrWithFlexContents = createMultidimensionalArray(createOneDimensionalArrayFromTwoDimensionalArray(json_decode($productInfo['flex_contents_'.$language])), 2, 1);


                foreach ($arrWithFlexContents as $key => $flexContent) {
                    if (is_array($flexContent)) {
                        $arrKeyFlexContents[$language][] = $key;
                    }
                }
            }

            $arrWithFlexContentsLanguageIndependent =  createMultidimensionalArray(createOneDimensionalArrayFromTwoDimensionalArray(json_decode($productInfo['flex_contentsLanguageIndependent'])), 2, 1);
            $arrKeyFlexContentsLanguangeIndependent = [];

            foreach( $arrWithFlexContentsLanguageIndependent as $key => $flexContent){
                if(is_array($flexContent)){
                    $arrKeyFlexContentsLanguangeIndependent[] = $key;
                }
            }


            if(($arrKeyFlexContents || $arrKeyFlexContentsLanguangeIndependent) && $tbTable == 'tl_ls_shop_product'){
                $productIdsWithArrayFlexContent[] = [
                    'productId' => $productInfo['id'],
                    'doubleKey' => $arrKeyFlexContents,
                    'doubleKeyLanguangeIndependent' => $arrKeyFlexContentsLanguangeIndependent
                ];
            }
            if(($arrKeyFlexContents || $arrKeyFlexContentsLanguangeIndependent) && $tbTable == 'tl_ls_shop_variant'){
                $productIdsWithArrayFlexContent[] = [
                    'productId' => $productInfo['pid'],
                    'variantId' => $productInfo['id'],
                    'doubleKey' => $arrKeyFlexContents,
                    'doubleKeyLanguangeIndependent' => $arrKeyFlexContentsLanguangeIndependent
                ];
            }

        }

        return $productIdsWithArrayFlexContent;
    }


}