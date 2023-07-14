<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class XmlComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ArraytoXml($va1) {
		App::import('Vendor', 'ArrayToXml', array('file' => 'xml/ArraytoXml.php'));
		$xml = ArrayToXml::createXML('archiv', $va1);
		return htmlentities($xml->saveXML());
	}

	public function XmltoArray($va1,$va2,$va3,$pfad = null) {

		if($va2 == 'string') $xml = Xml::build(html_entity_decode($va1));

		if($va2 == 'file') {
			if($va3 != null){
				if(
				isset(
				$this->_controller->request->projectvars['reportnumberID']) &&
				$this->_controller->request->projectvars['reportnumberID'] > 0  &&
				$this->_controller->request->params['controller'] != 'searchings' &&
				$this->_controller->request->params['controller'] != 'dropdowns' &&
				$this->_controller->request->params['action'] != 'linking'){

					$this->_controller->loadModel('Reportnumber');
					$this->_controller->Reportnumber->recursive = 0;
					$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id' => $this->_controller->request->projectvars['reportnumberID'])));

					$xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $Reportnumber['Testingmethod']['value'] . DS . (isset($Reportnumber['Reportnumber']['version']) && intval($Reportnumber['Reportnumber']['version']) > 1 ? $Reportnumber['Reportnumber']['version'] : 1) . DS;

				} else {

					$this->_controller->loadModel('Testingmethod');
					$Testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.value' => $va1)));

					if(count($Testingmethod) > 0){
						$xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $Testingmethod['Testingmethod']['value'] . DS . $Testingmethod['Testingmethod']['version'] . DS;
					} else {
						$xml_folder = Configure::read('xml_folder');
					}
				}

				if ($va3 == 'TechnicalPlace') {

					$technicalplacevariant = $this->_controller->request->projectvars['VarsArray'][2];
					$this->_controller->loadModel('Technicalplacevariant');
					$TechnicalPlaceVariant = $this->_controller->Technicalplacevariant->find('first',array('conditions'=>array('Technicalplacevariant.id' => $technicalplacevariant)));

					if(file_exists($xml_folder . 'technicalplace' . DS . 'Technicalplace'.$TechnicalPlaceVariant['Technicalplacevariant']['value'].'.xml') === true){
						$xml_folder = $xml_folder . 'technicalplace' . DS ;
					}
				}

			} else {

				$xml_folder = Configure::read('xml_folder');


				switch($va1) {
					case ('data_dependency_display_zfp'):
					if(file_exists($xml_folder . 'advance' . DS . 'data_dependency_display_zfp.xml') === true){
						$xml_folder = $xml_folder . 'advance' . DS ;
					}
					case ('data_dependency_display_inspection'):
					if(file_exists($xml_folder . 'advance' . DS . 'data_dependency_display_inspection.xml') === true){
						$xml_folder = $xml_folder . 'advance' . DS ;
					}
					case ('data_dependency_display_psv'):
					if(file_exists($xml_folder . 'advance' . DS . 'data_dependency_display_psv.xml') === true){
						$xml_folder = $xml_folder . 'advance' . DS ;
					}
					case ('Order'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'orders' . DS . 'projects' . DS . $projectID . DS . 'Order.xml') === true){
						$xml_folder = $xml_folder . 'orders' . DS . 'projects' . DS . $projectID . DS;
					}
					break;
					case ('display'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'displays' . DS . 'projects' . DS . $projectID . DS . 'display.xml') === true){
						$xml_folder = $xml_folder . 'displays' . DS . 'projects' . DS . $projectID . DS;
					}
					break;
					case ('search_landingpage'):
					$xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
					break;
					case ('search_additional'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS . 'search_additional.xml') === true){
						$xml_folder = $xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS;
					} else {
						$xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
					}
					break;
					case ('search_standard'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS . 'search_standard.xml') === true){
						$xml_folder = $xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS;
					} else {
						$xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
					}
					break;
					case ('search_report_standard'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS . 'search_report_standard.xml') === true){
						$xml_folder = $xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS;
					} else {
						$xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
					}
					break;

					case ('device_search_standard'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS . 'device_search_standard.xml') === true){
						$xml_folder = $xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS;
					} else {
						$xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
					}
					break;
					case ('device_search_monitoring'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS . 'device_search_monitoring.xml') === true){
						$xml_folder = $xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS;
					} else {
						$xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
					}
					break;
					case ('display_expediting'):
					$projectID = $this->_controller->request->projectvars['VarsArray'][0];
					if(file_exists($xml_folder . 'expediting' . DS . 'projects' . DS . $projectID . DS . 'display_expediting.xml') === true){
						$xml_folder = $xml_folder . 'expediting' . DS . 'projects' . DS . $projectID . DS;
					} else {
						$xml_folder = $xml_folder . 'expediting' . DS . 'default' . DS;
					}
					break;
					case ('Testinginstruction'):
					if(file_exists($xml_folder . 'testinstruction' . DS . 'Testinginstruction.xml') === true){
						$xml_folder = $xml_folder . 'testinstruction' . DS;
					} else {
					}
					break;
					case ('TestinginstructionsData'):
					if(file_exists($xml_folder . 'testinstruction' . DS . 'TestinginstructionsData.xml') === true){
						$xml_folder = $xml_folder . 'testinstruction' . DS;
					} else {
					}
					break;
				}
			}

			if($pfad != null){
				$xml_folder .= $pfad;
			}

			$folder = new Folder($xml_folder);
			$file = $folder->findRecursive($va1.'.xml');

			if(is_array($file) && !empty($file)) {
				$value = $xml_folder.basename(reset($file));
				//$value = $xml_folder.str_replace($xml_folder, '', reset($file));
			}

			if(isset($value)){
				$xml = Xml::build($value);
			}
			else {

				$xml = new SimpleXmlElement('<settings></settings>');
				CakeLog::write('error', '[load XML] Can\'t load xml from '.$va2.': '.$va1 . ' ' . $xml_folder);
				CakeLog::write('error', Debugger::trace());
//				$this->_controller->Session->setFlash(__('An error occoured while loading settings. Please notify the Administrator.'));
			}
		}

		return $xml;
	}

	public function SaveWaitingXml($va1,$va2) {

		$this->_controller->loadModel('Order');
		$ExaminiersXML['waitings'] = $va1;
		$XmlToDatabase = Xml::fromArray($ExaminiersXML, array('format' => 'tags')); // You can use Xml::build() too
		$XmlToDatabaseXML = $XmlToDatabase->asXML();
		$orderData = array(
					'id' => $va2,
					'waiting_times' => $XmlToDatabaseXML
				);
		$this->_controller->Order->save($orderData);
	}

	public function DatafromXmlForReport($verfahren,$Reportnumber) {

		$xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $Reportnumber['Testingmethod']['value'] . DS . (isset($Reportnumber['Reportnumber']['version']) && intval($Reportnumber['Reportnumber']['version']) > 1 ? $Reportnumber['Reportnumber']['version'] : 1) . DS;
		$folder = new Folder($xml_folder);
		$file = $folder->findRecursive($verfahren.'.xml');

		if(is_array($file) && !empty($file)) {

			$value = $xml_folder.basename(reset($file));

		} else {
	
			$xml = new SimpleXmlElement('<settings></settings>');
			CakeLog::write('error', '[load XML] Can\'t load xml from: '. $xml_folder . ' for ' . $Reportnumber['Reportnumber']['id']);
			CakeLog::write('error', Debugger::trace());
			return $xml;

		}

		if(isset($value)){
			$xml['settings'] = Xml::build($value);
		} else {

			$xml = new SimpleXmlElement('<settings></settings>');
			CakeLog::write('error', '[load XML] Can\'t load xml from: '. $xml_folder . ' for ' . $Reportnumber['Reportnumber']['id']);
			CakeLog::write('error', Debugger::trace());
//			$this->_controller->Session->setFlash(__('An error occoured while loading settings. Please notify the Administrator.'));
		}

		return $xml;
	}

	public function CollectXMLFromFile($val) {

		$Output = array();

		$xml_folder = Configure::read('xml_folder');
		$file = $xml_folder . $val . '.xml';

		if(!file_exists($file)) return $Output;

		$xml = Xml::build($file);

		if(!is_object($xml)) return $Output;

		$Output = $xml;

		return $Output;

	}

	public function LoadXmlFile($Path,$File) {

		$xml_folder = Configure::read('xml_folder');

		$folder = new Folder($xml_folder . $Path . DS . $File . DS);
		$filename = $File . '.xml';

		$file = $folder->findRecursive($filename);

		if(is_array($file) && !empty($file)) {
			$value = $folder->path . basename(reset($file));
		}

		if(!empty($value)){
			$xml = Xml::build($value);
		}
		else {

			$xml = new SimpleXmlElement('<settings></settings>');
			CakeLog::write('error', '[load XML] Can\'t load xml from ' . $$folder->path . $File);
			CakeLog::write('error', Debugger::trace());

		}

		$output['settings'] = $xml;

		return $output;
	}

	public function LoadXmlFileForReport($Data) {

		$Output = array();

		if(is_object($Data)) $Output = $this->__LoadXmlFileForReportObject($Data);
		if(is_array($Data)) $Output = $this->__LoadXmlFileForReportArray($Data);

		return $Output;

	}

	protected function __LoadXmlFileForReportObject($Data) {

		if(empty($Data->Reportnumber)) return $this->LoadXmlFileForReportReturnEmpty();
		if(empty($Data->Testingmethod)) return $this->LoadXmlFileForReportArray();

		$verfahren = $Data->Testingmethod->value;
		$Verfahren = ucfirst($Data->Testingmethod->value);
		$version = $Data->Reportnumber->version;

		$xml_folder = Configure::read('xml_folder');
		$xml_folder .= 'testingmethod' . DS . $verfahren . DS . $version . DS;

		$filename = $verfahren . '.xml';

		$folder = new Folder($xml_folder);
		$file = $folder->findRecursive($filename);

		if(is_array($file) && !empty($file)) {
			$value = $folder->path . basename(reset($file));
		} else {
			return $this->LoadXmlFileForReportReturnEmpty();
		}

		if(!empty($value)){
			$xml = Xml::build($value);
			$output['settings'] = $xml;
		}
		else {
			return $this->LoadXmlFileForReportReturnEmpty();
		}

		return $output;

	}

	protected function __LoadXmlFileForReportArray($Data) {

		if(empty($Data['Reportnumber'])) return $this->LoadXmlFileForReportReturnEmpty();
		if(empty($Data['Testingmethod'])) return $this->LoadXmlFileForReportReturnEmpty();

		$verfahren = $Data['Testingmethod']['value'];
		$Verfahren = ucfirst($Data['Testingmethod']['value']);
		$version = $Data['Reportnumber']['version'];

		$xml_folder = Configure::read('xml_folder');
		$xml_folder .= 'testingmethod' . DS . $verfahren . DS . $version . DS;

		$filename = $verfahren . '.xml';

		$folder = new Folder($xml_folder);
		$file = $folder->findRecursive($filename);

		if(is_array($file) && !empty($file)) {
			$value = $folder->path . basename(reset($file));
		} else {
			return $this->LoadXmlFileForReportReturnEmpty();
		}

		if(!empty($value)){
			$xml = Xml::build($value);
			$output['settings'] = $xml;
		}
		else {
			return $this->LoadXmlFileForReportReturnEmpty();
		}

		return $output;

	}

	protected function LoadXmlFileForReportReturnEmpty() {

		$xml = new SimpleXmlElement('<settings></settings>');
		CakeLog::write('error', '[load XML] Can\'t load xml from ' . $$folder->path . $File);
		CakeLog::write('error', Debugger::trace());

		$output['settings'] = $xml;

		return $output;

	}

	// soll von LoadXmlFile ersetzt werden
	public function DatafromXml($va1,$va2,$va3,$pfad = null, $va4 = null) {

		$output = array();

		$output['settings'] = $this->XmltoArray($va1,$va2,$va3,$pfad);

		if($output['settings'] == null) return $output;

		$Verfahren = $va3;

		$allTables = array();
		$model = $this->_controller->modelClass;

		foreach($this->_controller->$model->tableToModel as $tableToModel) {
			$allTables[] = $tableToModel;
		}

		if(
			$va1 != 'ExaminerMonitoringData' &&
			$va1 != 'ExaminerMonitoring' &&
			$va1 != 'Order' &&
			$va1 != 'CertificateData' &&
			$va1 != 'CertificateDataRefresh' &&
			$va1 != 'DeviceCertificate' &&
			$va1 != 'CertificateDataAsk' &&
			$va1 != 'CertificateDataRefresh'&&
			$va1 != 'WelderCertificateData' &&
			$va1 != 'WelderCertificateDataRefresh' &&
			$va1 != 'WelderCertificateDataAsk' &&
			$va1 != 'WelderCertificateDataRefresh' &&
			$va1 != 'AdvancesDataDependency' &&
			$va1 != 'CascadeGroup'
		){

			// Noch die drei wichtigsten Tabellen hinzufügen
			$allTables[] = 'Report'.$Verfahren.'Generally';
			$allTables[] = 'Report'.$Verfahren.'Specific';
			$allTables[] = 'Report'.$Verfahren.'Evaluation';

			foreach($allTables as $x) {
				foreach($output['settings']->$x as $tables) {
					foreach($tables as $values) {
						if($values->output->screen == 1) {
							$output['headoutput'][] = $values;
						}
						if($values->output->screen == 2) {
							$output['generaloutput'][] = $values;
						}
						if($values->output->screen == 3) {
							$output['specificoutput'][] = $values;
						}
						if($values->output->screen == 4) {
							$output['evaluationoutput'][] = $values;
						}
					}
				}
			}
		}

		if($va1 == 'CertificateData' ||$va1 == 'CertificateDataAsk' || $va1 == 'CertificateDataRefresh'){

			foreach($output['settings']->CertificateData as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}

		if($va1 == 'TestingstructionsData'){

			foreach($output['settings']->TestingstructionsData as $tables) {
				foreach($tables as $values) {
					$output['headoutput'][] = $values;
				}
			}
		}

		if($va1 == 'WelderCertificateData' ||$va1 == 'WelderCertificateDataAsk' ||$va1 == 'WelderCertificateDataRefresh'){

			foreach($output['settings']->WelderCertificateData as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}
		if($va1 == 'DeviceCertificate' || $va1 == 'ExaminerMonitoring' || $va1 == 'ExaminerMonitoringData' || $va1 == 'WelderMonitoring'){

			foreach($output['settings']->$va1 as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}

		if($va1 == 'DeviceCertificateEdit' || $va1 == 'DeviceCertificateRefresh' ){

			foreach($output['settings']->DeviceCertificateData as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}


		if($va1 == 'ExaminerMonitoringRefresh' || $va1 =='ExaminerMonitoringEdit'){
			foreach($output['settings']->ExaminerMonitoringData as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}
     if($va1 == 'WelderMonitoringRefresh' || $va1 =='WelderMonitoringEdit'){
                    foreach($output['settings']->WelderMonitoringData as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}

		}

		if($va1 == 'EyecheckData' || $va1 == 'EyecheckDataRefresh'){

			foreach($output['settings']->EyecheckData as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}

		if($va1 == 'Order'){

			foreach($output['settings']->Order as $tables) {
				foreach($tables as $values) {
					$output['orderoutput'][] = $values;
				}
			}
		}

		if($va3 == 'CascadeGroup'){

				if($va4 <> null){
					foreach($output['settings']->$va4 as $tables) {
						foreach($tables as $values) {
							$output['headoutput'][] = $values;
						}
					}
				}
		}

		return $output;
	}

	public function ArchivDatafromXml($va1) {

	 	$this->_controller->loadModel('Testingmethod');
	 	$optionsTestingmethod = array('conditions' => array('Testingmethod.id' => $this->_controller->request->projectvars['evalId']));
	 	$testingmethod = $this->_controller->Testingmethod->find('first',$optionsTestingmethod);

		if(count($testingmethod) == null) return;

		$xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $testingmethod['Testingmethod']['value'] . DS . $testingmethod['Testingmethod']['version'] . DS . $va1.'.xml';

		if(file_exists($xml_folder)) {
			$value = $xml_folder;
			$xml = Xml::build($value);
			$xmlString = $xml->asXML();
			return htmlentities($xmlString);
		}
	}

	public function TestXmlExists($va1) {

		$output = false;

	 	$this->_controller->loadModel('Testingmethod');
	 	$optionsTestingmethod = array('conditions' => array('Testingmethod.value' => $va1));
	 	$testingmethod = $this->_controller->Testingmethod->find('first',$optionsTestingmethod);

		$Verfahren = ucfirst($testingmethod['Testingmethod']['value']);

		$Models['Archiv'] = 'Report' . $Verfahren . 'Archiv';
		$Models['Evaluation'] = 'Report' . $Verfahren . 'Evaluation';
		$Models['Generally'] = 'Report' . $Verfahren . 'Generally';
		$Models['Setting'] = 'Report' . $Verfahren . 'Setting';
		$Models['Specific'] = 'Report' . $Verfahren . 'Specific';

		if(count($testingmethod) == null) return;

		$model_folder = Configure::read('root_folder') . 'models' . DS;
		$xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $testingmethod['Testingmethod']['value'] . DS . $testingmethod['Testingmethod']['version'] . DS . $va1.'.xml';
		if(file_exists($xml_folder)) {

			$output = true;
		} else {
			$output = false;
		}

		// Test ob alle Modeldateien vorhanden sind
		foreach($Models as $_key => $_Models) if(file_exists($model_folder . $_Models . '.php') == false) return false;

		return $output;

	}

	// Funktion zum Upload und Verarbeitung der Auftrags CSV-Datei
	public function OrderfileUpload($project,$typ) {

		$result = null;

		$this->layout = 'redirect';

		$projectURL = $this->_controller->Session->read('projectURL');
		$this->_controller->Session->write('uploadOk', null);
		$this->_controller->Session->write('uploadError', null);
		$this->_controller->Session->write('CsvError', null);

		// Wenn die Daten vorhanden sind
		if(isset($this->_controller->request->data['Order']['upload_true']) && $this->_controller->request->data['Order']['upload_true'] == 1){
			// empfangenen Daten in ein Array schreiben
			$data = $this->_controller->request->data;
			// die Id des aktuellen Projektes hinzufügen
			$data['Order']['topproject_id'] = $project;
			// muss noch geändert werden
			$fileselect = 'OrderDescription';

			// hochgeladener Dateityp testen
//			if($data['Order']['file']['type'] != 'application/cvs') {
			if($data['Order']['file']['type'] == '') {
				die('wrong file '.$data['Order']['file']['type']);
			}
			else {
				// Datei zum Auslesen öffnen
				$cvsData = fopen($data['Order']['file']['tmp_name'], 'r');
				$cvsArray = array();
				$cvsArrayUtf8 = array();
				// und CVS Upload in ein Array einlesen
				while($zeile = fgetcsv($cvsData,500,';')) {
					$cvsArray[] = $zeile;
				}
				// Die Daten aus dem Array werden nach utf8 konvertiert
				// auf schädlichen Code und Gültigkeit der Datei überprüft
				$x = 0;
				$xx = 0;
				$CsvError = array();
				foreach($cvsArray as $_cvsArray) {
					$xx = 0;
					foreach($_cvsArray as $__cvsArray) {
						$cvsArrayUtf8[$x][$xx] = utf8_encode(trim($__cvsArray));
						$cvsArrayUtf8[$x][$xx] = strip_tags($cvsArrayUtf8[$x][$xx]);
						$cvsArrayUtf8[$x][$xx] = str_replace(chr(13), "", $cvsArrayUtf8[$x][$xx]);
						$xx++;
					}
					$x++;
				}

				fclose($cvsData);

				// Das zutreffenden Vorlageschema wird geladen
				// Alle Zeilen des CVS-Arrays mit Formulardaten
				if(file_exists(Configure::read('xml_folder').$fileselect.'.xml')) {
					$SchemaCsvArray = Xml::build(Configure::read('xml_folder').$fileselect.'.xml');
				}
				elseif(file_exists($_SERVER['DOCUMENT_ROOT'].$this->_controller->request->base.'/app/xml/'.$fileselect.'.xml')){
					$SchemaCsvArray = Xml::build($_SERVER['DOCUMENT_ROOT'].$this->_controller->request->base.'/app/xml/'.$fileselect.'.xml');
				}
				// Wenn die Serverwebroot in der Cakephp-webroot liegt
				elseif(file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,-7).'/xml/'.$fileselect.'.xml')){
					$SchemaCsvArray = Xml::build(substr($_SERVER['DOCUMENT_ROOT'],0,-7).'/xml/'.$fileselect.'.xml');
				}
				else {
					die('Error, no Templatefile 1 found.');
				}

				// Vorlage und hochgeladene Datei wird verglichen
				$x = 0;
				foreach($SchemaCsvArray as $key => $item){
					// Die Buchstaben werden aus dem Schlüssel gefiltert
					$_key = explode('row',$key);
					$xx = 0;
					foreach($item as $ikey => $detail){
						$_ikey = explode('col',$ikey);
						if($detail != $cvsArrayUtf8[$_key[1]][$_ikey[1]]) {
							$CsvError[] = array($x, $xx, '"'.$detail.'"');
						}
					$xx++;
					}
				$x++;
				}
			}

			// Wenn beim einlesen der Uploaddatei etwas schief gegangen ist
			// werden die Errormeldungen in ein HTML-Objekt geschrieben und als Session gespeichert
			if(count($CsvError) > 0) {
				$result .= '<div class="error">';
				$result .= '<div id="message">';
				$result .= '<h3>'.__('Error message', true).'</h3>';
				$result .= '<p>'.__('The uploaded file could not be processed, the scheme of the file is incorrect. Check the file and try again.', true).'</p>';
				$result .= '<ul>';

				foreach($CsvError as $_CsvError) {
					$result .= '<li>'.$_CsvError.'</li>';
				}
				$result .= '</ul></div></div>';
			}

			$this->_controller->Session->write('uploadOk', $cvsArrayUtf8);
			$this->_controller->Session->write('CsvError', $CsvError);

			// Wenn das Schema der hochgeladenen Datei gültig ist
			$cvsArrayUtf8['file'] = $typ;
			$result  = '<div id="status">success</div>';
			$result .= '<div id="message">'.__('Successfully uploaded', true).'<br />';
			$result .= '</div>';
			$this->_controller->set('redirect', $this->_controller->Navigation->afterEDIT('orders/create/'.$projectURL,0));
			$this->_controller->render(null);
		}
	}

	public function exportXML($data, $fileName = '', $maxExecutionSeconds = 200){

		$this->_controller->autoRender = false;

		if(!empty($maxExecutionSeconds)){
			ini_set('max_execution_time', $maxExecutionSeconds);
		}

		if(empty($fileName)){
			$fileName = "export_".date("Y-m-d").".xml";
		}

		$xmlFile = fopen('php://output', 'w');

		header('Content-type: application/xml');
		header('Content-Disposition: attachment; filename="'.$fileName.'"');
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		fputs($xmlFile,html_entity_decode($data));

		while (@ob_end_flush());
		set_time_limit(0);

		fclose($xmlFile);

	}
}
