<?php

namespace LeadingSystems\Helpers;

if (!isset($_SESSION['ls_helpers'])) {
	$_SESSION['ls_helpers'] = array();
}

//22.09.2023, tbu, ls_toggleLogClass entfernt, https://lsboard.de/project/18/task/6112

/*
 * Activate the tmp log class like so: http://whatever.de?ls_toggleLogClass=on&ls_useLogClasses=tmp
 * or multiple log classes: http://whatever.de?ls_toggleLogClass=on&ls_useLogClasses=tmp,test1,test2
 * or all log classes: http://whatever.de?ls_toggleLogClass=on
 * 
 * Turn of a single log class like this: http://whatever.de?ls_toggleLogClass=off&ls_useLogClasses=tmp
 * or multiple log classes: http://whatever.de?ls_toggleLogClass=off&ls_useLogClasses=tmp,test1,test2
 * or (for all active log classes): http://whatever.de?ls_toggleLogClass=off
 * 
 * This function writes well-formatted messages to the error log. Using the get
 * parameters 'ls_toggleLogClass' and 'ls_useLogClasses' it is possible to activate
 * logging for specific log classes (see ls_toggleLogClass()). By default, logging
 * is deactivated, except for the logClass 'perm' which will always be logged.
 */
function lsErrorLog($title = '', $var = '', $logClass = '', $mode='regular', $blnReplaceUUIDs = true, $str_logPath = '') {
	if (
			!$logClass
		||	(
					$logClass !== 'perm'
				&&	(
							!isset($_SESSION['ls_helpers']['arr_activatedLogClasses'])
						||	!is_array($_SESSION['ls_helpers']['arr_activatedLogClasses'])
					)
			)
	) {
		return;
	}
	
	if ($logClass !== 'perm') {
		if (
				!in_array($logClass, $_SESSION['ls_helpers']['arr_activatedLogClasses'])
			&&	!in_array('all', $_SESSION['ls_helpers']['arr_activatedLogClasses'])
		) {
			return;
		}
	}

	if (!$str_logPath) {
		$str_logPath = __DIR__.'/log';
	}
	
	if ($blnReplaceUUIDs) {
		$var = replaceUUIDsInErrorLog($var);
	}
	
	$GLOBALS['lsErrorLog']['testcounter'] = isset($GLOBALS['lsErrorLog']['testcounter']) ? $GLOBALS['lsErrorLog']['testcounter'] : 0;
	$GLOBALS['lsErrorLog']['testcounter']++;
	
	if ($mode == 'var_dump') {
		ob_start();
		var_dump($var);
		$varErrorText = ob_get_clean();
	} else if (is_array($var)) {
		ob_start();
		print_r($var);
		$varErrorText = ob_get_clean();
	} else if (is_object($var)) {
		ob_start();
		print_r($var);
		$varErrorText = ob_get_clean();
	} else {
		if ($var === true) {
			$varErrorText = 'TRUE';
		} else if ($var === false) {
			$varErrorText = 'FALSE';
		} else if ($var === null) {
			$varErrorText = 'NULL';
		} else {
			$varErrorText = $var;
		}
	}
	if ($title || $varErrorText) {
		if (!file_exists($str_logPath) || !is_dir($str_logPath)) {
			mkdir($str_logPath);
		}
		error_log('['.$GLOBALS['lsErrorLog']['testcounter'].'] '.($title ? $title."\r\n" : '').$varErrorText."\r\n", 3, $str_logPath.'/lsErrorLog.log');
	}
}

function replaceUUIDsInErrorLog($var) {
	/*
	 * Objects are currently not supported
	 */
	if (is_object($var)) {
		return $var;
	}
	
	if (!is_array($var)) {
		if (\Validator::isUuid($var)) {
			$objFile = \FilesModel::findByUuid($var);
	
			if ($objFile === null) {
				$var = 'UUID ==> NULL';
			} else {
				$var = 'UUID ==> ' . $objFile->path;
			}
		} else if (bin2hex($var) == '00000000000000000000000000000000') {
			$var = bin2hex($var);
		}
	} else {
		foreach ($var as $k => $v) {
			$var[$k] = replaceUUIDsInErrorLog($v);
		}
	}
	return $var;
}

/*
 * Implementing the http_response_code in case it doesn't exist yet, because
 * it is not available before PHP 5.4.0
 */
if (!function_exists('http_response_code')) {
	function http_response_code($newcode = NULL) {
		static $code = 200;
		if($newcode !== NULL) {
			header('X-PHP-Response-Code: '.$newcode, true, $newcode);
			if(!headers_sent()) {
				$code = $newcode;
			}
		}       
		return $code;
	}
}

function addUtf8Bom($str_input) {
	return chr(239).chr(187).chr(191).$str_input;
}

function performanceCheck($key = null, $startStop = 'start', $description = '') {
#	return;
	if (!$key) {
		return;
	}
	
	if (!isset($_SESSION['ls_x']['performanceCheck'][$key])) {
		$_SESSION['ls_x']['performanceCheck'][$key] = array(
			'start' => 0,
			'time' => 0,
			'description' => $description,
			'numStarts' => 0,
			'numStops' => 0
		);
	}
	
	switch ($startStop) {
		case 'start':
			$_SESSION['ls_x']['performanceCheck'][$key]['start'] = microtime(true);
			$_SESSION['ls_x']['performanceCheck'][$key]['numStarts']++;
			break;
			
		case 'stop':
			$_SESSION['ls_x']['performanceCheck'][$key]['time'] += microtime(true) - $_SESSION['ls_x']['performanceCheck'][$key]['start'];
			$_SESSION['ls_x']['performanceCheck'][$key]['start'] = 0;
			$_SESSION['ls_x']['performanceCheck'][$key]['numStops']++;
			break;
	}	
}

function performanceCheckResults() {
#	return;
	if (is_array($_SESSION['ls_x']['performanceCheck'])) {
		foreach ($_SESSION['ls_x']['performanceCheck'] as $key => $arrPerformance) {
			lsErrorLog('Performance check ('.$key.' [starts: '.$arrPerformance['numStarts'].', stops: '.$arrPerformance['numStarts'].']): '. $arrPerformance['description'], $arrPerformance['time'], 'tmp', 'var_dump', false);
			unset($_SESSION['ls_x']['performanceCheck'][$key]);
		}
	}
}

function ls_add($a, $b) {
	return strval(round(($a + $b), 10));
}

function ls_sub($a, $b) {
	return strval(round(($a - $b), 10));
}

function ls_mul($a, $b) {
	return strval(round(($a * $b), 10));
}

function ls_div($a, $b) {
	return strval(round(($a / $b), 10));
}

function ls_getFilePathFromVariableSources($src) {
	if (\Validator::isUuid($src)) {
		$objFile = \FilesModel::findByUuid($src);

		if ($objFile === null) {
			return '';
		}

		$src = $objFile->path;
	} else if (is_numeric($src)) {
		$objFile = \FilesModel::findByPk($src);

		if ($objFile === null) {
			return '';
		}

		$src = $objFile->path;
	} else {
		// Do nothing because the path is already the string required for further processing
	}
	
	return $src;
}

/*
 * This function is made especially for Merconis and probably not of much use for anything but the use case described below.
 *
 * When Merconis used Contao listwizards, values were stored as serialized one-dimensional arrays. In order to process
 * those values, we had to create multi-dimensional arrays using the function "createMultidimensionalArray".
 *
 * Now Merconis uses LSJS widget modules and therefore values are stored as two-dimensional arrays in JSON.
 * Because we wanted the change that came with using LSJS widget modules in Merconis to be as minimally invasive as possible,
 * we didn't want to touch value processing in Merconis at all and the easiest way to do this was to create one-dimensional arrays
 * from the JSON data so that every step that follows can stay exactly the same as before. And that's what this function
 * is needed for.
 */
function createOneDimensionalArrayFromTwoDimensionalArray($arr_input) {
	$arr_oneDimensionalOutput = [];
	if (!is_array($arr_input)) {
		return $arr_oneDimensionalOutput;
	}

	foreach ($arr_input as $arr_level1) {
		foreach ($arr_level1 as $var_valueLevel2) {
			$arr_oneDimensionalOutput[] = $var_valueLevel2;
		}
	}
	return $arr_oneDimensionalOutput;
}

/*
 * Diese Funktion wird verwendet, um eindimensionale Arrays, wie sie von den Contao-Listenwizards erstellt werden,
 * in brauchbare, mehrdimensionale Arrays umzuwandeln.
 * $numRelatedElements bestimmt, wieviele Elemente des eindimensionalen Arrays zusammengehören,
 * $elementNrToUseAsKey gibt an, welches Element ggf. als Key verwendet werden soll.
 */
function createMultidimensionalArray($onedimensionalArray = array(), $numRelatedElements = 2, $elementNrToUseAsKey = 0, $arrAssociativeKeysToUse = array(), $orderByKey = false, $orderDirection = 'asc') {
	
	// bisheriger Mode 2 verwendete das erste Element als Key, Mode 2 verwendete numerische Keys und beide Elemente als Ausgabelemente
	
	if (!isset($onedimensionalArray) || !is_array($onedimensionalArray)) {
		return array();
	}
	
	$multidimensionalArray = array();
	
	$count = 0;
	$key = '';
	$arrTemp = array();
	
	foreach ($onedimensionalArray as $value) {
		$count++;
		
		if ($count == $elementNrToUseAsKey) {
			$key = $value;
		} else {
			$arrTemp[] = $value;
		}
						
		
		if ($count == $numRelatedElements) {
			if ($key) {
				$multidimensionalArray[$key] = count($arrTemp) > 1 ? $arrTemp : $arrTemp[0];
			} else {
				$multidimensionalArray[] = count($arrTemp) > 1 ? $arrTemp : $arrTemp[0];
			}
			$count = 0;
			$key = '';
			$arrTemp = array();
		}

	}
	
	/*
	 * if the property $arrAssociativeKeysToUse is set, the current multidimensional array which uses a numerical
	 * index in the second dimension will be converted in an array which uses an associative index in the second
	 * dimension.
	 * 
	 * e.g. the multidimensional array
	 * 
	 * $arrBefore = array(
	 * 		'a' => array(
	 * 			0 => 'somevalue',
	 * 			1 => 'somevalue'
	 * 		)
	 * );
	 * 
	 * will become
	 * 
	 * $arrBefore = array(
	 * 		'a' => array(
	 * 			'associativeIndexNr1' => 'somevalue',
	 * 			'associativeIndexNr2' => 'somevalue'
	 * 		)
	 * );
	 * 
	 * if the property $arrAssociativeKeysToUse is set to
	 * 
	 * $arrAssociativeKeysToUse = array(
	 * 		0 => 'associativeIndexNr1',
	 * 		1 => 'associativeIndexNr2'
	 * );
	 * 
	 */
	if (is_array($arrAssociativeKeysToUse) && count($arrAssociativeKeysToUse)) {
		$arrTemp = array();
		foreach ($multidimensionalArray as $k1 => $v1) {
			$arrTemp[$k1] = array();
			foreach ($v1 as $k2 => $v2) {
				$arrTemp[$k1][isset($arrAssociativeKeysToUse[$k2]) ? $arrAssociativeKeysToUse[$k2] : $k2] = $v2;
			}
		}
		$multidimensionalArray = $arrTemp;
	}
	
	/*
	 * the property $orderByKey can take either a numeric or an alphanumeric value which defines the key in the
	 * second dimension by which the multidimensional array will be sorted. The Value '0' is valid, so to check
	 * whether or not the array has to be sorted, the parameter has to be compared type sensitive against "false"
	 */
	if ($orderByKey !== false) {
		$arrSorting = array();
		foreach ($multidimensionalArray as $k => $v) {
			$arrSorting[$k] = $v[$orderByKey];
		}
		switch ($orderDirection) {
			case 'asc':
				asort($arrSorting);
				break;
				
			case 'desc':
				arsort($arrSorting);
				break;
		}
		foreach ($arrSorting as $k => $v) {
			$arrSorting[$k] = $multidimensionalArray[$k];
		}
		$multidimensionalArray = $arrSorting;
	}
	
	return $multidimensionalArray;
}

/*
 * This function takes a two-level array as an argument and returns another
 * two-level array holding all possible combinations. The input can be an
 * associative array.
 * 
 * Example:
 * Input: 
 *	$arr_combiTest = array(
		'fruits' => array(
			'fruit1' => 'banana',
			'fruit2' => 'apple'
		),
		'animals' => array('giraffe'),
		'friends' => array(
			'bestfriend' => 'will',
			'secondbestfriend' => 'alicia',
			'whatever' => 'cary'
		)
	);
 * 
 * Output:
 *	Array (
		[0] => Array ([0] => banana [1] => giraffe [2] => will)
		[1] => Array ([0] => banana [1] => giraffe [2] => alicia)
		[2] => Array ([0] => banana [1] => giraffe [2] => cary)
		[3] => Array ([0] => apple [1] => giraffe [2] => will)
		[4] => Array ([0] => apple [1] => giraffe [2] => alicia)
		[5] => Array ([0] => apple [1] => giraffe [2] => cary)
	)
 */
function create_arrayCombinations($arr_arrays, $i = 0) {
	/*
	 * Use array_keys to be able to translate the iterating index
	 * into to the corresponding keys which enables this function to
	 * also handle associative input arrays
	 */
	$arr_keys = array_keys($arr_arrays);
	
    if (!isset($arr_keys[$i])) {
        return array();
    }
	
    if ($i === count($arr_keys) - 1) {
        return $arr_arrays[$arr_keys[$i]];
    }

    /*
	 * Create the combinations for following arrays
	 */
    $arr_followingCombinations = create_arrayCombinations($arr_arrays, $i + 1);

    $arr_resultingCombinations = array();

    /*
	 * Combine each element from the current array ($arr_arrays[$arr_keys[$i]])
	 * with each array from $arr_followingCombinations
	 */
    foreach ($arr_arrays[$arr_keys[$i]] as $var_value) {
        foreach ($arr_followingCombinations as $var_valueOfFollowingCombination) {
            $arr_resultingCombinations[] = array_merge(
				array($var_value),
				is_array($var_valueOfFollowingCombination) ? $var_valueOfFollowingCombination : array($var_valueOfFollowingCombination)
			);
        }
    }

    return $arr_resultingCombinations;
}

function getUrlWithoutParameters($var_parameterName = null, $str_url = null) {
	if (!$str_url) {
		$str_url = \Environment::get('request');
	}

	$arr_url = explode('?', $str_url);
	$str_base = $arr_url[0];
	$str_queryString = isset($arr_url[1]) ? $arr_url[1] : '';

	if ($str_queryString && $var_parameterName) {
		$str_queryString = removeParameterFromQueryString($str_queryString, $var_parameterName);
	}

	$str_url = $str_queryString ? $str_base.'?'.$str_queryString : $str_base;

	return $str_url;
}

function removeParameterFromQueryString($str_queryString, $var_parameterName = null) {
	if (!$str_queryString) {
		return '';
	}

	if (!$var_parameterName) {
		return $str_queryString;
	}

	if (is_array($var_parameterName)) {
		foreach ($var_parameterName as $str_parameterName) {
			$str_queryString = removeParameterFromQueryString($str_queryString, $str_parameterName);
		}
	} else {
		$str_parameterName = $var_parameterName;
		/*
		 * This function expects the query string parameter without a leading & or ? sign. However, to make
		 * the regexp easier, we add exactly one leading & sign and then remove the first character of the
		 * resulting string before we return it
		 */
		$str_queryString = '&'.$str_queryString;
		$str_queryString = preg_replace('/&'.preg_quote($str_parameterName).'=.*(&|$)/U', '$1', $str_queryString);
		$str_queryString = substr($str_queryString, 1);
	}

	return $str_queryString;
}

function addQueryParameters($arr_parameters = null, $str_url = null) {
	if (!$str_url) {
		$str_url = \Environment::get('request');
	}

	if (!is_array($arr_parameters)) {
		return $str_url;
	}

	$int_count = 0;
	foreach ($arr_parameters as $str_parameterKey => $str_parameterValue) {
		$int_count++;
		$str_delimiter = '&';

		if ($int_count === 1) {
			if (strpos($str_url, '?') === false) {
				$str_delimiter = '?';
			}
		}

		$str_url .= $str_delimiter.$str_parameterKey.'='.$str_parameterValue;
	}

	return $str_url;
}