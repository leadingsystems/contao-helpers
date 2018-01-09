<?php

namespace LeadingSystems\Helpers;

class ls_helpers_segmentizer {
	protected $str_segmentationToken = null;
	protected $str_info = null;
	protected $int_tstampLastCall = 0;
	protected $int_tstampExpiration = 0;
	protected $int_numSegmentsTotal = 0;
	protected $int_lastSegment = 0;
	protected $int_currentTurn = 1;
	protected $bln_nextCallIsNewTurn = false;

	protected $int_tstampThisCall = 0;

	protected $int_currentSegment = 1;
	protected $int_lifetimeInSeconds = 60 * 60 * 24; // 1 day

	protected $bln_isNew = true;

	protected $bln_finishWithExtraSegment = false;
	protected $bln_isLastSegment = false;

	public function __construct($str_segmentationToken, $str_info = '', $int_lifetimeInSeconds = 0, $bln_finishWithExtraSegment = false) {
		if (!$str_segmentationToken) {
			throw new \Exception('no segmentationToken given');
		}

		$this->str_segmentationToken = $str_segmentationToken;
		$this->str_info = $str_info;
		if ($int_lifetimeInSeconds) {
			$this->int_lifetimeInSeconds = $int_lifetimeInSeconds;
		}

		$this->bln_finishWithExtraSegment = $bln_finishWithExtraSegment ? true : false;

		$this->int_tstampThisCall = time();
		$this->int_tstampExpiration = $this->int_tstampThisCall + $this->int_lifetimeInSeconds;

		$this->clean();
		$this->read();

		if ($this->int_numSegmentsTotal) {

			$this->determineCurrentSegment();
		}
	}

	public function __destruct() {
		$this->determineTurn();
		$this->write();
	}

	public function __set($str_key, $var_value) {
		switch ($str_key) {
			case 'numSegmentsTotal':
				$this->int_numSegmentsTotal = $var_value > 0 ? ($var_value + ($this->bln_finishWithExtraSegment ? 1 : 0)) : 0;
				$this->determineCurrentSegment();
				break;
		}
	}

	public function __get($str_what) {
		switch ($str_what) {
			case 'currentSegment':
				return $this->int_currentSegment;
				break;

			case 'currentTurn':
				return $this->int_currentTurn;
				break;

			case 'numSegmentsTotal':
				return $this->int_numSegmentsTotal;
				break;

			case 'isLastSegment':
				return $this->bln_isLastSegment;
				break;

			default:
				return null;
				break;
		}
	}

	protected function read() {
		$obj_dbres = \Database::getInstance()
			->prepare("
				SELECT		*
				FROM		`tl_ls_helpers_segmentizer`
				WHERE		`segmentationToken` = ?
			")
			->execute(
				$this->str_segmentationToken
			);

		if ($obj_dbres->numRows) {
			$this->bln_isNew = false;
			$this->int_numSegmentsTotal = $obj_dbres->numSegmentsTotal;
			$this->int_lastSegment = $obj_dbres->lastSegment;
			$this->int_tstampLastCall = $obj_dbres->tstampLastCall;
			$this->int_currentTurn = $obj_dbres->currentTurn;
			$this->bln_nextCallIsNewTurn = $obj_dbres->nextCallIsNewTurn ? true : false;
		}
	}

	protected function write() {
		if ($this->bln_isNew) {
			$obj_dbquery = \Database::getInstance()
				->prepare("
					INSERT INTO 	`tl_ls_helpers_segmentizer`
					SET				`segmentationToken` = ?,
									`info` = ?,
									`tstampLastCall` = ?,
									`tstampExpiration` = ?,
									`numSegmentsTotal` = ?,
									`lastSegment` = ?,
									`currentTurn` = ?,
									`nextCallIsNewTurn` = ?
				")
				->execute(
					$this->str_segmentationToken,
					$this->str_info,
					$this->int_tstampThisCall,
					$this->int_tstampExpiration,
					$this->int_numSegmentsTotal,
					$this->int_currentSegment,
					$this->int_currentTurn,
					$this->bln_nextCallIsNewTurn ? '1' : ''
				);
		} else {
			$obj_dbquery = \Database::getInstance()
				->prepare("
					UPDATE 			`tl_ls_helpers_segmentizer`
					SET				`info` = ?,
									`tstampLastCall` = ?,
									`tstampExpiration` = ?,
									`numSegmentsTotal` = ?,
									`lastSegment` = ?,
									`currentTurn` = ?,
									`nextCallIsNewTurn` = ?
					WHERE			`segmentationToken` = ?
				")
				->limit(1)
				->execute(
					$this->str_info,
					$this->int_tstampThisCall,
					$this->int_tstampExpiration,
					$this->int_numSegmentsTotal,
					$this->int_currentSegment,
					$this->int_currentTurn,
					$this->bln_nextCallIsNewTurn ? '1' : '',
					$this->str_segmentationToken
				);
		}
	}

	protected function clean() {
		$obj_dbquery = \Database::getInstance()
			->prepare("
				DELETE FROM		`tl_ls_helpers_segmentizer`
				WHERE			`tstampExpiration` < ?
			")
			->execute(
				time()
			);
	}

	protected function determineCurrentSegment() {
		if ($this->bln_nextCallIsNewTurn) {
			$this->int_currentSegment = 1;
		} else {
			$this->int_currentSegment = $this->int_lastSegment + 1;
		}

		if (
			$this->int_currentSegment >= $this->int_numSegmentsTotal
			||	$this->int_numSegmentsTotal == 0
		) {
			$this->bln_isLastSegment = true;
		}
	}

	protected function determineTurn() {
		if ($this->bln_nextCallIsNewTurn) {
			$this->int_currentTurn++;
			$this->bln_nextCallIsNewTurn = false;
		}

		if ($this->bln_isLastSegment) {
			$this->bln_nextCallIsNewTurn = true;
		}
	}

	public function next() {
		$this->int_lastSegment++;
		$this->determineCurrentSegment();
	}
}
