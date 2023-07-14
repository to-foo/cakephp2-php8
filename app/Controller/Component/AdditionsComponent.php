<?php
class AdditionsComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	// funktioniert ähnlich der Autoload-Funktion von php
	public function Add($xml) {
		if(Configure::check('Additions') === false) return false;
		else $Additions = Configure::read('Additions');

		if(!isset($this->_controller->request->data['Testingmethod'])) return false;

		$verfahren = $this->_controller->request->data['Testingmethod']['value'];
		$Verfahren = ucfirst($verfahren);

		$ReportAdditions = 'Report' . $Verfahren . 'Additions';

		if(empty($xml['settings']->$ReportAdditions)) return false;
		else $AdditionsXml = $xml['settings']->$ReportAdditions;

		if(count($AdditionsXml->children()) == 0) return false;

		if(!is_array($Additions)) return false;
  	$return = array();

		foreach($Additions as $_key => $_Additions){

			if(!method_exists($this, $_key)) continue;
			if(empty($AdditionsXml->$_key)) continue;
			if(!is_object($AdditionsXml->$_key)) continue;

			$output = call_user_func(array($this, $_key), $AdditionsXml->$_key, 1);

			if($output != false) {

				$this->_controller->set($_key,$output);
				$return[$_key] = $output;

			}
		}


		return $return;
	}

	public function PositioningTable($Additions,$Step){
		$output = array();

		if($Step == 1) $output = $this->_PositioningTableCreate($Additions,$Step);

		return $output;

	}

	public function ImageSelection($Additions,$Step){

		$output = array();

		if(!isset($this->_controller->request->data['Report']['id'])) return false;
		if(!isset($this->_controller->request->data['Testingmethod']['id'])) return false;
		if(!isset($this->_controller->request->data['Testingmethod']['value'])) return false;

		if($this->_controller->request->data['Report']['id'] == 0) return false;
		if($this->_controller->request->data['Testingmethod']['id'] == 0) return false;
		if($this->_controller->request->data['Testingmethod']['value'] == '') return false;

		$reportID = $this->_controller->request->data['Report']['id'];
		$testingmethodID = $this->_controller->request->data['Testingmethod']['id'];
		$verfahren = $this->_controller->request->data['Testingmethod']['value'];
		$Verfahren = ucfirst($verfahren);

		if(is_dir(Configure::read('root_folder') . Inflector::underscore('ImageSelection') . DS) == false) return false;

		$ImagePfad = Configure::read('root_folder') . Inflector::underscore('ImageSelection') . DS . $reportID . DS . $testingmethodID . DS;

		$allFiles = scandir($ImagePfad);

		foreach ($allFiles as $_key => $_file) {
			if(filetype($ImagePfad . $_file) != 'file') continue;

			$pathinfo = pathinfo($ImagePfad . $_file);
			if(file_get_contents($pathinfo['dirname'] . DS . $pathinfo['basename']) != false){
			 	$output[$_key] = array(
									'file_content' => file_get_contents($pathinfo['dirname'] . DS . $pathinfo['basename']),
									'activity' => 1
									);
			}
		};

		// das Reitermenü mit dem Eintrag für die zusätzliche Funktion ergänzen
		// wird gleich ins Objekt geschrieben
		// Könnte man in eine extra Funktion auslagern
		$NewKey = count($this->_controller->request->data['EditMenue']['Menue']) + 1;

		$this->_controller->request->data['EditMenue']['Menue'][$NewKey] = array(
	 			'id' => 'ImageSelectionArea',
	 			'class' => 'deaktive',
	 			'discription' => __('Image selection',true),
	 			'rel' => 'ImageSelectionArea'
		);

		return $output;
	}

	public function Logger($id,$message=array()) {
		$this->_controller->loadModel('Log');
		$Message = array('Content'=>array());

		// falls die Keys aus Ziffern bestehen, muss das korregiert werden, sonst kommt eine Fehlermeldung
		if(isset($message) && count($message) > 0){
			foreach($message as $_key => $_message){
				$Message['Content']['n'.$_key] = $_message;
			}
		}

		$XmlMessage = Xml::fromArray($Message, array('format' => 'tags')); // You can use Xml::build() too
		$XmlMessageXML = $XmlMessage->asXML();
	}

	public function PositioningTableDublicate($Verfahren,$settings,$dataUpdate,$dataOld){

		if(Configure::check('Additions') === false) return $dataUpdate;

		$ReportAdditions = 'Report' . $Verfahren . 'Additions';

		if(empty($settings->$ReportAdditions)) return $dataUpdate;
		if(empty($settings->$ReportAdditions->PositioningTable)) return $dataUpdate;
		if(empty($settings->$ReportAdditions->PositioningTable->value)) return $dataUpdate;

		$AdditionsModel = trim($settings->$ReportAdditions->PositioningTable->model);

		foreach($settings->$ReportAdditions->PositioningTable->value->children() as $key => $value){

			if(!isset($dataOld[$AdditionsModel][trim($key)])) continue;
			if(trim($value->dublicate) != 'yes') continue;

			$dataUpdate[$AdditionsModel][trim($key)] = $dataOld[$AdditionsModel][trim($key)];

		}

		return $dataUpdate;
	}

	protected function _PositioningTableCreate($Additions,$Step){

		$reportnumber_id = $this->_controller->request->data['Reportnumber']['id'];
		$lang = $this->_controller->request->lang;

		$output = array();

		$rows = 0;
		$columns = 0;

		if(empty($Additions->model)) return false;
		if(empty($Additions->head)) return false;

		$model = trim($Additions->model);

		if(!$this->_controller->loadModel($model)) return;
		$schema = $this->_controller->$model->schema();

		$ModelData = $this->_controller->$model->find('first',array('conditions'=>array($model . '.reportnumber_id' => $reportnumber_id)));

		if(count($ModelData) == 0) return false;

		// damit Extract ohne Fehlermeldung funktioniert
		// muss das Objekt in ein Array umgewandelt werden
		$HeadArray = (array) $Additions->head;

		$ExtractedHead = Hash::extract($HeadArray['value'], '{n}.field');
		$ExtractedHeadValue = Hash::extract($HeadArray['value'], '{n}.description.' . $lang);
		unset($HeadArray);

		$InsertHeadData = array();
		$InsertHeadCounter = 0;

		foreach($ExtractedHead as $_key => $_ExtractedHead){

			if(empty($_ExtractedHead)) continue;

			if(!isset($schema[$_ExtractedHead])){
				unset($ExtractedHead[$_key]);
				continue;
			}

			// Wenn alle Tabellenkopffelder noch keinen Datenbankeintrag haben
			// gehen wir davon aus, dass es der erste Eintrag ist
			// und die Datena aus der XML-Datei übernommen werden
			if($ModelData[$model][$ExtractedHead[$_key]] == '') $InsertHeadCounter++;

			$InsertHeadData[$ExtractedHead[$_key]] = $ExtractedHeadValue[$_key];
		}
		// ist die Anzahl der Tabellenfelder gleich der leeren
		// Tabellenfelder, erfolgt der Eintrag
		if($InsertHeadCounter == count($InsertHeadData)){
			$InsertHeadData['id'] = $ModelData[$model]['id'];
			$ModelData = $this->_controller->$model->save($InsertHeadData);
		}

		$ModelData = $this->_controller->$model->find('first',array('conditions'=>array($model . '.reportnumber_id' => $reportnumber_id)));

		// Tabellenheader befüllen
		foreach($Additions->head->value as $_key => $_children){

			$spanA = null;
			$spanE = null;

			// die Kopfzeile bearbeitbar machen
			// wenn eine SQL-Spalte angegeben ist
			if(!empty($_children->field) && trim($_children->field)  != ''){
				$spanA = '<span class="edit_in_table" id="' . trim($_children->field) . '">';
				$spanE = '</span>';
			}

			$description = trim($_children->description->$lang);

			if(isset($ModelData[$model][trim($_children->field)])) $description = $ModelData[$model][trim($_children->field)];

			$output['head'][] = $spanA . $description . $spanE;
		}

		if(!empty($Additions->firstcolumn)) {
			foreach($Additions->firstcolumn->children() as $_key => $_Additions){

				$row = trim($_Additions->tableoutput->row) - 1;
				$column = trim($_Additions->tableoutput->column) - 1;

				// Spalten- und Zeilenwerte dürfen nie kleiner als Null sein
				if($row < 0) return false;
				if($column < 0) return false;

				$celldata = trim($_Additions->description->$lang);

				$output['body'][$row][$column] = $celldata;
			}
		}

		// Tabellenbody füllen
		foreach($Additions->value->children() as $_key => $_Additions){

			$field = trim($_Additions->key);

			// der Computer fängt immer mit Null an, zuzählen
			$row = trim($_Additions->tableoutput->row) - 1;
			$column = trim($_Additions->tableoutput->column) - 1;

			// Spalten- und Zeilenwerte dürfen nie kleiner als Null sein
			if($row < 0) return false;
			if($column < 0) return false;

			$celldata = null;

			// das Array für die Tabellenausgabe im View generieren
			if(isset($this->_controller->request->data[trim($_Additions->model)][trim($_Additions->key)])){

				$spanA = '<span class="edit_in_table" id="' . $field . '">';
				$spanE = '</span>';

				$value = $this->_controller->request->data[trim($_Additions->model)][$field];

				$value = $this->_controller->Formating->ValueForView($schema[$field],$value);

				$celldata = array(
								$spanA  . $value . $spanE,
								array(
									'class' => 'edit_in_table',
									'id' => $field
									)
								);

				$output['body'][$row][$column] = $celldata;

			} else {
				$output['body'][$row][$column] = $ModelData[trim($_Additions->model)][$field];
			}
		}

		// Prüfung der Daten auf Plausibilität
		// Tabellenkopf und Tabellenbody müsen vorhanden sein
		if(count($output) != 2) return false;
		if(count($output['head']) == 0) return false;
		if(count($output['body']) == 0) return false;
		// Tabellenkopf und Tabellenbody müssen die gleiche Spaltenanzahl haben
//pr($output);

		foreach($output['body'] as $_key => $_output) {
			if(count($_output) != count($output['head'])) return false;
		}

		// das Reitermenü mit dem Eintrag für die zusätzliche Funktion ergänzen
		// wird gleich ins Objekt geschrieben
		// Könnte man in eine extra Funktion auslagern
		if(isset($this->_controller->request->data['EditMenue']['Menue'])){
			$NewKey = count($this->_controller->request->data['EditMenue']['Menue']) + 1;

			$this->_controller->request->data['EditMenue']['Menue'][$NewKey] = array(
	 			'id' => 'PositionTableArea',
	 			'class' => 'deaktive',
	 			'discription' => __('Locating table',true),
	 			'rel' => 'PositionTableArea'
			);
		}

		isset ($Additions->name->$lang) ? $output['title'] = trim($Additions->name->$lang) : $output['title']='';

		return $output;

	}
	public function Sumevaluation($Additions,$Step){
		$reportnumber_id = $this->_controller->request->data['Reportnumber']['id'];


		$model = trim($Additions->model);
		$key = trim($Additions->key);
		if(!$this->_controller->loadModel($model)) return;
		$schema = $this->_controller->$model->schema();

		$ModelData = $this->_controller->$model->find('list',array('fields'=>array($key),'conditions'=>array($model . '.reportnumber_id' => $reportnumber_id,$model.'.deleted <'=> 1 )));
	if(count($ModelData) == 0) return false;


		foreach ($ModelData as $key => $value) {
			$ModelData[$key]=	preg_replace('/,/', '.', $value);
		}
		$out = trim($Additions->discription).' '.array_sum($ModelData);

		$Summe['out'] =$out;
		$Summe['outputlastrow'] = 1;
	//	$Summe['sum'.$key] ['data'] =
	//	$Summe['sum'.$key] ['output'] = true;


		return $Summe;

	}
}
