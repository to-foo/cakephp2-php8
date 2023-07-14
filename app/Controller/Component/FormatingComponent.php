<?php
class FormatingComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
		App::uses('CakeNumber', 'Utility');
	}

	public function ValueForSQL($schema,$value) {

		$output = null;

		switch($schema['type']) {
			case 'float':
			// Komma in Punkt umwandeln
			$Value = str_replace(",",".", $value);

			$Value = floatval($Value);

			$Value = CakeNumber::format($Value,array(
				'places' => 2,
				'escape' => false,
				'before' => false,
				'decimals' => '.',
				'thousands' => ''
			));


			$Decimal = explode(',',$schema['length']);
			$ValueArray = explode('.',$Value);

			if(count($Decimal) == 2) {
				$Places = intval($Decimal[0]) - intval($Decimal[1]);
				$DecimalPlaces = intval($Decimal[1]);
			}
			if(count($Decimal) == 1) {
				$Places = intval($Decimal[0]);
				$DecimalPlaces = 0;
			}

			$ValueArrayPlaces = strlen(intval($ValueArray[0]));

			if($ValueArrayPlaces > $Places) return false;

			$output = $Value;

			return $output;

			break;
		}
	}

	public function ValueForView($schema,$value) {

		$output = null;
		$Value = $value;
		$decimals = '.';

		if($this->_controller->request->lang == 'deu') $decimals = ',';
		if($this->_controller->request->lang == 'eng') $decimals = '.';

		switch($schema['type']) {
			case 'float':

			// Komma in Punkt umwandeln
			$Value = str_replace(",",".", $value);
			$Value = floatval($Value);

			$Decimal = explode(',',$schema['length']);

			if(count($Decimal) == 2) {
				$Places = intval($Decimal[0]) - intval($Decimal[1]);
				$DecimalPlaces = intval($Decimal[1]);
			}
			if(count($Decimal) == 1) {
				$Places = 0;
				$DecimalPlaces = 0;
			}

			$Value = CakeNumber::format($Value,array(
    			'places' => $DecimalPlaces,
    			'escape' => false,
				'before' => false,
    			'decimals' => $decimals,
    			'thousands' => ''
			));

			break;
		}

		$output = $Value;

		return $output;
	}

	public function DateFromExcel($Date) {

		$DateArray = explode('.',$Date);

		$Date = NULL;

		if(count($DateArray) == 3){
			if(strlen($DateArray[2]) == 2){
				$Date = '20' . $DateArray[2] . '-' . $DateArray[1] . '-' . $DateArray[0];
			}
			elseif(strlen($DateArray[2]) == 4){
				$Date = $DateArray[2] . '-' . $DateArray[1] . '-' . $DateArray[0];
			} else {
				return NULL;
			}
		} else {
			return NULL;
		}

		return $Date;
	}
}
