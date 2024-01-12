<?php
namespace LeadingSystems\Helpers;

use Contao\ArrayUtil;
use Contao\Controller;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;

class ls_helpers_controller extends Controller {
	protected static $arrStaticObjects = array();

	/**
	 * Current object instance (Singleton)
	 * @var Input
	 */
	protected static $objInstance;

	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct() {
		$this->import('Database');
		parent::__construct();
	}

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	private function __clone() {}

	/**
	 * Return the current object instance (Singleton)
	 * @return Input
	 */
	public static function getInstance() {
		if (!is_object(self::$objInstance))	{
			self::$objInstance = new self();
		}
		return self::$objInstance;
	}

	/*
	 * Implementing the importStatic method so that we
	 * can use it as a fallback for Contao < 3.0.0
	 */
	public static function importStatic($strClass, $strKey=null, $blnForce=false) {
		$strKey = $strKey ?: $strClass;

		if ($blnForce || !isset(static::$arrStaticObjects[$strKey])) {
			static::$arrStaticObjects[$strKey] = (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();
		}

		return static::$arrStaticObjects[$strKey];
	}

	public static function idFromUuid($str_value = '') {
		if (Validator::isUuid($str_value)) {
			$obj_file = FilesModel::findByUuid($str_value);
			if ($obj_file === null) {
				return '';
			}
			$str_value = $obj_file->id;
		}
		return $str_value;
	}

	public static function uuidFromId($str_value = '') {
		if (is_array($str_value)) {
			return $str_value;
		}
		if (Validator::isUuid($str_value)) {
			return $str_value;
		}

		if (is_numeric($str_value)) {
			$obj_file = FilesModel::findByPk($str_value);

			if ($obj_file === null) {
				return '';
			}

			$str_value = $obj_file->uuid;
		}

		return $str_value;
	}

	public static function convertIsoCountryCode($str_input, $str_inputType = 'iso2') {
		$arr_countryCodeMapping = array(
			'AF' => 'AFG',
			'AX' => 'ALA',
			'AL' => 'ALB',
			'DZ' => 'DZA',
			'AS' => 'ASM',
			'AD' => 'AND',
			'AO' => 'AGO',
			'AI' => 'AIA',
			'AG' => 'ATG',
			'AR' => 'ARG',
			'AM' => 'ARM',
			'AW' => 'ABW',
			'AU' => 'AUS',
			'AT' => 'AUT',
			'AZ' => 'AZE',
			'BS' => 'BHS',
			'BH' => 'BHR',
			'BD' => 'BGD',
			'BB' => 'BRB',
			'BY' => 'BLR',
			'BE' => 'BEL',
			'BZ' => 'BLZ',
			'BJ' => 'BEN',
			'BM' => 'BMU',
			'BT' => 'BTN',
			'BO' => 'BOL',
			'BQ' => 'BES',
			'BA' => 'BIH',
			'BW' => 'BWA',
			'BR' => 'BRA',
			'IO' => 'IOT',
			'VG' => 'VGB',
			'BN' => 'BRN',
			'BG' => 'BGR',
			'BF' => 'BFA',
			'BI' => 'BDI',
			'KH' => 'KHM',
			'CM' => 'CMR',
			'CA' => 'CAN',
			'CV' => 'CPV',
			'KY' => 'CYM',
			'CF' => 'CAF',
			'TD' => 'TCD',
			'CL' => 'CHL',
			'CN' => 'CHN',
			'CX' => 'CXR',
			'CC' => 'CCK',
			'CO' => 'COL',
			'KM' => 'COM',
			'CG' => 'COG',
			'CD' => 'ZAR',
			'CK' => 'COK',
			'CR' => 'CRI',
			'HR' => 'HRV',
			'CU' => 'CUB',
			'CW' => 'CUW',
			'CY' => 'CYP',
			'CZ' => 'CZE',
			'DK' => 'DNK',
			'DJ' => 'DJI',
			'DM' => 'DMA',
			'DO' => 'DOM',
			'TL' => 'TLS',
			'EC' => 'ECU',
			'EG' => 'EGY',
			'SV' => 'SLV',
			'GQ' => 'GNQ',
			'ER' => 'ERI',
			'EE' => 'EST',
			'ET' => 'ETH',
			'FO' => 'FRO',
			'FK' => 'FLK',
			'FJ' => 'FJI',
			'FI' => 'FIN',
			'FR' => 'FRA',
			'GF' => 'GUF',
			'PF' => 'PYF',
			'TF' => 'ATF',
			'GA' => 'GAB',
			'GM' => 'GMB',
			'GE' => 'GEO',
			'DE' => 'DEU',
			'GH' => 'GHA',
			'GI' => 'GIB',
			'GR' => 'GRC',
			'GL' => 'GRL',
			'GD' => 'GRD',
			'GP' => 'GLP',
			'GU' => 'GUM',
			'GT' => 'GTM',
			'GG' => 'GGY',
			'GN' => 'GIN',
			'GW' => 'GNB',
			'GY' => 'GUY',
			'HT' => 'HTI',
			'VA' => 'VAT',
			'HN' => 'HND',
			'HK' => 'HKG',
			'HU' => 'HUN',
			'IS' => 'ISL',
			'IN' => 'IND',
			'ID' => 'IDN',
			'IR' => 'IRN',
			'IQ' => 'IRQ',
			'IE' => 'IRL',
			'IM' => 'IMN',
			'IL' => 'ISR',
			'IT' => 'ITA',
			'CI' => 'CIV',
			'JM' => 'JAM',
			'JP' => 'JPN',
			'JE' => 'JEY',
			'JO' => 'JOR',
			'KZ' => 'KAZ',
			'KE' => 'KEN',
			'KI' => 'KIR',
			'KW' => 'KWT',
			'KG' => 'KGZ',
			'LA' => 'LAO',
			'LV' => 'LVA',
			'LB' => 'LBN',
			'LS' => 'LSO',
			'LR' => 'LBR',
			'LY' => 'LBY',
			'LI' => 'LIE',
			'LT' => 'LTU',
			'LU' => 'LUX',
			'MO' => 'MAC',
			'MK' => 'MKD',
			'MG' => 'MDG',
			'MW' => 'MWI',
			'MY' => 'MYS',
			'MV' => 'MDV',
			'ML' => 'MLI',
			'MT' => 'MLT',
			'MH' => 'MHL',
			'MQ' => 'MTQ',
			'MR' => 'MRT',
			'MU' => 'MUS',
			'YT' => 'MYT',
			'MX' => 'MEX',
			'FM' => 'FSM',
			'MD' => 'MDA',
			'MC' => 'MCO',
			'MN' => 'MNG',
			'ME' => 'MNE',
			'MS' => 'MSR',
			'MA' => 'MAR',
			'MZ' => 'MOZ',
			'MM' => 'MMR',
			'NA' => 'NAM',
			'NR' => 'NRU',
			'NP' => 'NPL',
			'AN' => 'ANT',
			'NL' => 'NLD',
			'NC' => 'NCL',
			'NZ' => 'NZL',
			'NI' => 'NIC',
			'NE' => 'NER',
			'NG' => 'NGA',
			'NU' => 'NIU',
			'NF' => 'NFK',
			'KP' => 'PRK',
			'MP' => 'MNP',
			'NO' => 'NOR',
			'OM' => 'OMN',
			'PK' => 'PAK',
			'PW' => 'PLW',
			'PS' => 'PSE',
			'PA' => 'PAN',
			'PG' => 'PNG',
			'PY' => 'PRY',
			'PE' => 'PER',
			'PH' => 'PHL',
			'PN' => 'PCN',
			'PL' => 'POL',
			'PT' => 'PRT',
			'PR' => 'PRI',
			'QA' => 'QAT',
			'RO' => 'ROU',
			'RU' => 'RUS',
			'RW' => 'RWA',
			'RE' => 'REU',
			'BL' => 'BLM',
			'KN' => 'KNA',
			'SH' => 'SHN',
			'LC' => 'LCA',
			'MF' => 'MAF',
			'PM' => 'SPM',
			'VC' => 'VCT',
			'WS' => 'WSM',
			'SM' => 'SMR',
			'ST' => 'STP',
			'SA' => 'SAU',
			'SN' => 'SEN',
			'RS' => 'SRB',
			'SC' => 'SYC',
			'SL' => 'SLE',
			'SG' => 'SGP',
			'SX' => 'SXM',
			'SK' => 'SVK',
			'SI' => 'SVN',
			'SB' => 'SLB',
			'SO' => 'SOM',
			'ZA' => 'ZAF',
			'GS' => 'SGS',
			'KR' => 'KOR',
			'SS' => 'SSD',
			'ES' => 'ESP',
			'LK' => 'LKA',
			'SD' => 'SDN',
			'SR' => 'SUR',
			'SZ' => 'SWZ',
			'SE' => 'SWE',
			'CH' => 'CHE',
			'SY' => 'SYR',
			'TW' => 'TWN',
			'TJ' => 'TJK',
			'TZ' => 'TZA',
			'TH' => 'THA',
			'TG' => 'TGO',
			'TK' => 'TKL',
			'TO' => 'TON',
			'TT' => 'TTO',
			'TN' => 'TUN',
			'TR' => 'TUR',
			'TM' => 'TKM',
			'TC' => 'TCA',
			'TV' => 'TUV',
			'UG' => 'UGA',
			'UA' => 'UKR',
			'AE' => 'ARE',
			'GB' => 'GBR',
			'US' => 'USA',
			'VI' => 'VIR',
			'UY' => 'URY',
			'UZ' => 'UZB',
			'VU' => 'VUT',
			'VE' => 'VEN',
			'VN' => 'VNM',
			'WF' => 'WLF',
			'EH' => 'ESH',
			'YE' => 'YEM',
			'ZM' => 'ZMB',
			'ZW' => 'ZWE'
		);

		if ($str_inputType != 'iso2') {
			$arr_countryCodeMapping = array_flip($arr_countryCodeMapping);
		}

		return key_exists(strtoupper($str_input), $arr_countryCodeMapping) ? $arr_countryCodeMapping[strtoupper($str_input)] : $str_input;
	}

	/*
	 * This function returns the current request url without some or all GET parameters
	 */
	public static function getUrl($blnEncode = true, $removeKeys = array(), $keepKeys = array())
	{
		$url = StringUtil::ampersand(Environment::get('request'), $blnEncode);

		if (is_array($removeKeys)) {
			foreach ($removeKeys as $v) {
				$url = preg_replace('/(&|&amp;|\?)' . $v . '=.*((&|&amp;)|$)/siU', '\\3', $url);
			}
		} else if ($removeKeys == 'all') {
			$url = preg_replace('/\?.*$/siU', '', $url);

			ArrayUtil::arrayInsert($keepKeys, 0, array('do'));
			$count = 0;
			foreach ($keepKeys as $key) {
				$url = $url . (!$count ? '?' : '&') . $key . '=' . Input::get($key);
				$count++;
			}
		}

		return $url;
	}
}
