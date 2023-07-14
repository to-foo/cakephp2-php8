<?php
class CsvComponent extends Component {
	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}
	/**
	* Starts up ExportComponent for use in the controller
	*
	* @param Controller $controller A reference to the instantiating controller object
	* @return void
	*/
	function getUploadExcelCsv($file = null) {

		if(!file_exists($file)) return null;

		$arrayData = $this->_controller->Xml->DatafromXml($this->_controller->request->projectvars['VarsArray'][0],'file',null,'orders' . DS . 'templates' . DS . 'upload' . DS);

		$csvData = file_get_contents($file);
		$csvData = mb_convert_encoding($csvData, 'UTF-8',mb_detect_encoding($csvData, 'UTF-8, ISO-8859-1', true));
		if(empty($csvData)) return null;

		$lines = explode(PHP_EOL, $csvData);
		$CsvArray = array();
		$Output = array();
		$x = 0;
		foreach ($lines as $_key => $line) {

			if($_key == 0) continue;

			// das auffinden des Tennzeichens muss noch automatisiert werden
			// Überprüfungen mussen noch eingefügt werden
			$CsvArray[$x] = str_getcsv($line, ';');

			$xx = 0;
			foreach($arrayData['settings']->Order->children() as $__key => $__row){
				if($__row->model == 'Order'){
					if(isset($CsvArray[$x][$xx])){
						$Output[$x][trim($__row->key)] = $CsvArray[$x][$xx];
					}

				$xx++;
				}
			}
/*
			foreach($CsvArray[$x] as $__key => $_CsvArray){
				$CsvArray[$x][$__key] =  strip_tags($_CsvArray);
			}
*/
			$x++;
		}

		unset($CsvArray[0]);

		$last = array_pop($CsvArray);
		$last = array_pop($CsvArray);

		return $Output;
	}


	function convertToWindowsCharset($string) {
		$charset =  mb_detect_encoding($string,"UTF-8, ISO-8859-1, ISO-8859-15",true);
		$string =  mb_convert_encoding($string, "Windows-1252", $charset);
		return $string;
	}

	function exportCsv($data, $fileName = '', $maxExecutionSeconds = 200, $delimiter = ';', $enclosure = '"') {
		$this->_controller->autoRender = false;
		// Flatten each row of the data array

//		$headerRow = $this->getKeysForHeaderRow($flatData);
//		$flatData = $this->mapAllRowsToHeaderRow($headerRow, $flatData);

		if(!empty($maxExecutionSeconds)){
			ini_set('max_execution_time', $maxExecutionSeconds); //increase max_execution_time if data set is very large
		}

		if(empty($fileName)){
			$fileName = "export_".date("Y-m-d").".csv";
		}

		foreach ($data as $key => $value) {
			array_walk($data[$key],array($this,'encodeCSV'));
		}

		$csvFile = fopen('php://output', 'w');

		header('Content-type: application/csv');
		header('Content-Disposition: attachment; filename="'.$fileName.'"');
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		foreach ($data as $key => $value) {
			fputcsv($csvFile, $value, $delimiter, $enclosure);
		}

		while (@ob_end_flush());
		set_time_limit(0);

		fclose($csvFile);
	}

	protected function encodeCSV(&$value, $key){
	    $value = iconv('UTF-8//IGNORE', 'Windows-1252//IGNORE', $value);
	}

	public function flattenArray($array, &$flatArray, $parentKeys = ''){

		foreach($array as $key => $value){
			$chainedKey = ($parentKeys !== '')? $parentKeys.'.'.$key : $key;

			if(is_array($value)){
				$this->flattenArray($value, $flatArray, $chainedKey);
			}
			else {
				$flatArray[$chainedKey] = $value;
			}
		}
	}

	public function getKeysForHeaderRow($data){

		$headerRow = array();

		foreach($data as $key => $value){
			foreach($value as $fieldName => $fieldValue){
				if(array_search($fieldName, $headerRow) === false){
					$headerRow[] = $fieldName;
				}
			}
		}

	return $headerRow;

	}

	public function mapAllRowsToHeaderRow($headerRow, $data){

		$newData = array();

		foreach($data as $intKey => $rowArray){
			foreach($headerRow as $headerKey => $columnName){
				if(!isset($rowArray[$columnName])){
					//$rowArray[$columnName] = '';
					$newData[$intKey][$columnName] = '';
				}
				else {
					$newData[$intKey][$columnName] = $rowArray[$columnName];
				}
			}
		}

	return $newData;

	}
}
