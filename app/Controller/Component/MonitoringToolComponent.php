<?php
class MonitoringToolComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function GetNewData(){

		$data = $this->__GetResponse();
		$data = $this->__CreateFloatValue($data);
		$data = $this->__SaveRestValue($data);
		$data = $this->__LoadSqlValue($data);
		$data = $this->CreatePlot($data);

		if(isset($data['odometer'])) sort($data['odometer']);
		if(isset($data['lineplot'])) sort($data['lineplot']);

		return $data;
	}

	public function GetData(){

		$data = $this->__GetResponse();
		$data = $this->__CreateFloatValue($data);
//		$data = $this->__SaveRestValue($data);
		$data = $this->__LoadSqlValue($data);
		$data = $this->CreatePlot($data);

		return $data;
	}

	protected function __GetResponse()
	{

		App::uses('HttpSocket', 'Network/Http');
		$HttpSocket = new HttpSocket();
		$response= $HttpSocket->get('https://dipa.iff.fraunhofer.de/inspection40g/');

		return json_decode($response->body);
	}

	protected function __CreateFloatValue($data){

		$fmt = numfmt_create('en_EN', NumberFormatter::DECIMAL);

		$output = array();

		$find = array("ä", "ü", "ö","ß");
		$replace = array("ae", "ue", "oe","ss");

		foreach ($data as $key => $value) {

			if($key == 'Messung_Zeitpunkt'){
				$output[$key] = $value;
				continue;
			}

			$_key = str_replace($find, $replace, $key);

			$output[$_key] = (numfmt_format($fmt, $value));

		}

		$date = new DateTime();
		$date->format('Y-m-d H:i:s');

		$output['timestamp'] = $date->getTimestamp();

		return $output;
	}

	protected function __SaveRestValue($data){

		$this->_controller->Monitoring->create();

		if($this->_controller->Monitoring->save($data)){

			return $data;

		} else {
			return array();
		}
	}

	protected function __LoadSqlValue($data){

		$options = array(
			'limit' => 10,
			'order' => array('id DESC')
		);

		$data = $this->_controller->Monitoring->find('all',$options);

		$Continue = array('id','Messung_Zeitpunkt','timestamp');
		$Rows = array();

		if(count($data) == 0) return array();

		foreach ($data[0]['Monitoring'] as $key => $value) {

			if(in_array($key, $Continue)) continue;

			$Rows[$key] = array();

		}

		foreach ($data as $key => $value) {

			foreach ($Rows as $_key => $_value) {
				$v = Sanitize::paranoid($_key);
				$Rows[$_key]['id'] = $v;
				$Rows[$_key]['values']['y'][] = $value['Monitoring'][$_key];
				$Rows[$_key]['values']['x'][] = $data[$key]['Monitoring']['timestamp'];
			}

		}
		return $Rows;

	}

	public function CreatePlot($data){

		$data['Kessel_Betriebsdruck']['max'] = 10;
		$data['Kessel_Betriebsdruck']['min'] = 6;

		$output = array();

		$data['Aussentemperatur']['measure'] = 'C°';
		$data['Kessel_Brennstoffzufuhr']['measure'] = '%';
		$data['Primaerseite_Volumenstrom']['measure'] = 'm³/h';
		$data['Sekundaerseite_Volumenstrom']['measure'] = 'm³/h';
		$data['Primaerseite_Kesselleistung']['measure'] = 'm³/h';

		$DiagrammPartition = array(
			'odometer' => array(
				'Kessel_Betriebsdruck' => $data['Kessel_Betriebsdruck'],
			),
			'lineplot' => array(
				'Aussentemperatur' => $data['Aussentemperatur'],
				'Kessel_Brennstoffzufuhr' => $data['Kessel_Brennstoffzufuhr'],

				'Primaerseite_Temperatur' => array(
					'Primaerseite_Vorlauftemperatur' => $data['Primaerseite_Vorlauftemperatur'],
					'Primaerseite_Ruecklauftemperatur' => $data['Primaerseite_Ruecklauftemperatur'],
				),
				'Primaerseite_Volumenstrom' => $data['Primaerseite_Volumenstrom'],
				'Primaerseite_Kesselleistung' => $data['Primaerseite_Kesselleistung'],

				'Sekundaerseite_Temperatur' => array(
					'Sekundaerseite_Vorlauftemperatur' => $data['Sekundaerseite_Vorlauftemperatur'],
					'Sekundaerseite_Ruecklauftemperatur' => $data['Sekundaerseite_Ruecklauftemperatur'],
				),
				'Sekundaerseite_Volumenstrom' => $data['Sekundaerseite_Volumenstrom'],
			)
		);

//		$DiagrammPartition['odometer']['Kessel_Betriebsdruck'] = $data['Kessel_Betriebsdruck'];


		foreach ($DiagrammPartition as $key => $value) {

			switch ($key) {
				case 'odometer':

				foreach ($value as $_key => $_value) {

					$DiagrammPartition[$key][$_key] = $this->__CreateOdoMeter($_value);

				}

				break;

				case 'lineplot':

				foreach ($value as $_key => $_value) {

					if(isset($_value['values'])) $DiagrammPartition[$key][$_key] = $this->__CreateLinePlot($_value,$_key);
					else $DiagrammPartition[$key][$_key] = $this->__CreateLinePlotMulti($_value,$_key);

				}

				break;

				default:
					// code...
				break;
			}
		}

//		pr($DiagrammPartition);
/*
		$Wirkungsgrad = ( (array_sum($data['Sekundaerseite_Waermeleistung']['values']['y']) / count($data['Sekundaerseite_Waermeleistung']['values']['y'])) / (array_sum($data['Primaerseite_Kesselleistung']['values']['y']) / count($data['Primaerseite_Kesselleistung']['values']['y'])));

*/
		return $DiagrammPartition;
	}

	protected function __CreateLinePlotMulti($Data,$Key){

		if(count($Data) == 0) return $Data;
		if(!isset($Data[key($Data)]['values']['y'])) return $Data;
		if(!isset($Data[key($Data)]['values']['x'])) return $Data;

		$min = floatval(min($Data[key($Data)]['values']['y']));
		$max = floatval(max($Data[key($Data)]['values']['y']));
		$xline = $Data[key($Data)]['values']['x'];

		$MinScale = 1000;
		$MaxScale = 0;
//pr($Data);
		foreach($Data as $key => $value){

			foreach ($value['values']['y'] as $_key => $_value) {

				if($_value > $MaxScale) $MaxScale = $_value;
				if($_value < $MinScale) $MinScale = $_value;

			}

		}

		$MinScale -= 10 * $MinScale / 100;
		$MaxScale += 10 * $MaxScale / 100;

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('MinScale',$MinScale);
		$ImageView->set('MaxScale',$MaxScale);
		$ImageView->set('xline',$xline);
		$ImageView->set('key',$Key);
		$ImageView->set('data',$Data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/line_plot_multi');
		$Data['diagramm'] = base64_encode($Images);
		$Data['id'] = $Key;
		return $Data;
	}

	protected function __CreateLinePlot($Data,$Key){

		if(!isset($Data['values'])) return $Data;

		$min = floatval(min($Data['values']['y']));
		$max = floatval(max($Data['values']['y']));

		$MinScale = $min - ($min / 10);
		$MaxScale = $max  + ($max  / 10);

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('MinScale',$MinScale);
		$ImageView->set('MaxScale',$MaxScale);
		$ImageView->set('key',$Key);
		$ImageView->set('data',$Data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/line_plot');
		$Data['diagramm'] = base64_encode($Images);

		return $Data;
	}

	protected function __CreateOdoMeter($Data){

		$OdoValue = array_sum($Data['values']['y']) / count($Data['values']['y']);

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$Data);
		$ImageView->set('OdoData',$OdoValue);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/odometer_plot');
		$Data['diagramm'] = base64_encode($Images);

		return $Data;
	}

	public function getTasks (){

			$this->_controller->Cascade->recursive = 1;
			$cascades = $this->_controller->Cascade->find('all');

			foreach ($cascades as $key => $value) {

				if(empty($value['Task'])) continue;

				foreach ($value['Task'] as $_key => $_value) {
					$Task[$_value['date_to']] ['description'] [$value['Cascade']['discription']][$_key]= $_value['description'];
					$Task[$_value['date_to']] ['link'] =  array($value['Cascade']['topproject_id'],$value['Cascade']['id']);

					$timestamp = time();
					$timestampkarenz = strtotime($_value['date_to']) - "604800";
					$timestampkarenz = date("Y-m-d",$timestampkarenz);


					$datenow = date("Y-m-d",$timestamp);
					$datepluskarenz = strtotime($_value['date_to'])+ "604800";
					$datepluskarenz = date("Y-m-d",$datepluskarenz);

					if($datepluskarenz < $datenow){
						$Task[$_value['date_to']] ['class'] = 'error';
 						$diff = $timestamp-(strtotime($_value['date_to']) + "604800");
						$Task[$_value['date_to']] ['karenz'] ="Karenzdatum ". round($diff / 86400)." Tag(e) überschritten" ;
	 				}elseif($datenow > $timestampkarenz ) {
 						$diff = strtotime($_value['date_to']) + "604800" - ($timestamp);
						$Task[$_value['date_to']] ['karenz'] = round($diff / 86400)." Tag(e) bis Karenzdatum" ;
						$Task[$_value['date_to']] ['class'] = 'delay';
					}elseif($datepluskarenz > $datenow){
						$Task[$_value['date_to']] ['class'] = 'plan';
					}
					if ($_value['checked'] == 1) {
						$Task[$_value['date_to']] ['karenz'] = "erledigt";
						$Task[$_value['date_to']] ['class'] = 'okay';
					}

					$Task[$_value['date_to']] ['action'] = 'edit';
				}
			}

			ksort($Task);
			return $Task;
	}
}
