<?php
class DepentencyComponent extends Component {
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

	public function SortDependenciesForSelect($data) {

		$ModelField = $this->_controller->request->data['id'];

		if(!isset($data['Dropdown'])) return $data;
		if(!isset($data['Dropdown'][$ModelField])) return $data;
		if(!isset($data['Dropdown'][$ModelField]['options'])) return $data;
		if(empty($data['Dropdown'][$ModelField]['options'])) return $data;

		$output = array();

		foreach ($data['Dropdown'][$ModelField]['options'] as $key => $value) {
			$output[] = array('key' => $key,'value' => $value);
		}

		$data['Dropdown'][$ModelField]['sortoptions'] = $output;

		return $data;
	}

	public function GetFromCertificateModulManyTestingmethods($Reportnumber) {


		if($Reportnumber['Reportnumber']['status'] > 0 && $Reportnumber['Reportnumber']['print'] > 0) return $Reportnumber;

		$ReportGenerally = $this->_controller->request->tablenames[0];

		$fields = array();
		$data = array('id' => $ReportGenerally . 'Examiner');

		$fields = $this->_GetDropdownFromCertificateModulManyTestingmethods($Reportnumber,$fields,$data);

		if(isset($fields['Dropdown'][$ReportGenerally . 'Examiner']['options'])){
			$Reportnumber['Dropdowns'][$ReportGenerally]['examiner'] = $fields['Dropdown'][$ReportGenerally . 'Examiner']['options'];
		}

		$fields = $this->_TestingmethodsQualification($Reportnumber,$fields,$data,0);

		if(isset($fields['Dropdown'][$ReportGenerally . 'Examiner']['options'])){
			$Reportnumber['Dropdowns'][$ReportGenerally]['examiner'] = $fields['Dropdown'][$ReportGenerally . 'Examiner']['options'];
		}

		$fields = array();
		$data = array('id' => $ReportGenerally . 'Supervision');
		$fields = $this->_GetDropdownFromCertificateModulManyTestingmethods($Reportnumber,$fields,$data);

		if(isset($fields['Dropdown'][$ReportGenerally . 'Supervision']['options'])){
			$Reportnumber['Dropdowns'][$ReportGenerally]['supervision'] = $fields['Dropdown'][$ReportGenerally . 'Supervision']['options'];
		}

		$fields = $this->_TestingmethodsQualification($Reportnumber,$fields,$data,1);

		if(isset($fields['Dropdown'][$ReportGenerally . 'Supervision']['options'])){
			$Reportnumber['Dropdowns'][$ReportGenerally]['supervision'] = $fields['Dropdown'][$ReportGenerally . 'Supervision']['options'];
		}

		return $Reportnumber;
	}

	public function GetDependenciesForSelect($Reportnumber) {

		if($this->_TestForExaminerModul() === true){
//			$fields = $this->_GetFromCertificateModul($Reportnumber);
			$fields = $this->_GetFromCertificateModulManyTestingmethods($Reportnumber);

		} else {
			$fields = null;
			$fields = $this->_GetFromMasterDropdownDepentendy($Reportnumber,$fields);
			$fields = $this->_GetFromDropdownDepentendy($Reportnumber,$fields);
		}
		if(is_array($fields) && count($fields) == 0){
			$fields = null;
			$fields = $this->_GetFromDropdownDepentendy($Reportnumber,$fields);
		}

		if($fields == null){
			$fields = array();
		}

		return $fields;
	}

	public function GetDependenciesForMultiSelect($Reportnumber) {
		if($this->_TestForExaminerModul() === true){
			$fields = $this->_GetFromCertificateModul($Reportnumber);
		} else {
			$fields = null;
			$fields = $this->_GetFromMasterMultiselectDepentendy($Reportnumber,$fields);
			$fields = $this->_GetFromDropdownMultiDepentendy($Reportnumber,$fields);
		}

		if(count($fields) == 0 || $fields == false){
			$fields = null;
			$fields = $this->_GetFromDropdownDepentendy($Reportnumber,$fields);
		}

		return $fields;
	}

	protected function _TestForExaminerModul() {

		$Testarray = array('supervision','examiner');
		$Modeldata = explode('_',Inflector::underscore($this->_controller->request->data['id']));
		$IsModulField = false;
		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);

		$field = implode('_',$Modeldata);

		foreach ($Testarray as $key => $value) {
			if($value == $field){
				$IsModulField = true;
				break;
			}
		}

		if(Configure::check('CertifcateManagerReportInsert')){
			$CertifcateManagerReportInsert = Configure::read('CertifcateManagerReportInsert');
		}

		if(isset($CertifcateManagerReportInsert) && $CertifcateManagerReportInsert == true && $IsModulField == true) return true;
		else return false;

	}

	protected function _GetFromCertificateModul($Reportnumber) {
		
		$fields = array();

		if($Reportnumber['Reportnumber']['status'] > 0 && $Reportnumber['Reportnumber']['print'] > 0) return $fields;

		$fields = $this->_GetDropdownFromCertificateModul($Reportnumber,$fields);
		$fields = $this->_GetDepentencyFromCertificateModul($Reportnumber,$fields);

		return $fields;
	}

	// Soll die Funktion _GetFromCertificateModul ersetzen ****************
	// Für die Zuordnung des Prüfverfahrens wird hier die Verknüpfungstabelle 
	// certificates_testingmethodes verwendet
	protected function _GetFromCertificateModulManyTestingmethods($Reportnumber){

		$fields = array();

		if($Reportnumber['Reportnumber']['status'] > 0 && $Reportnumber['Reportnumber']['print'] > 0) return $fields;

		$data = $this->_controller->request->data;

		$fields = $this->_GetDropdownFromCertificateModulManyTestingmethods($Reportnumber,$fields,$data);
		$fields = $this->_GetDepentencyFromCertificateModul($Reportnumber,$fields);

		return $fields;

	}

	protected function _TestingmethodsQualification($Reportnumber,$fields,$data,$supervision){

		if(count($fields) > 0) return $fields;

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);
		$testingcompID = AuthComponent::user('Testingcomp.id');
		$testingmethodID = $Reportnumber['Reportnumber']['testingmethod_id'];
	
		if($dropdownValue == 0 && isset($data['val']) && $data['val'] > 0){
			$dropdownValue = intval($data['val']);
		}
	
		$ModelField = $data['id'];
		$Modeldata = explode('_',Inflector::underscore($ModelField));
		$model = implode('_',array($Modeldata[0],$Modeldata[1],$Modeldata[2]));
		$Model = Inflector::camelize($model);
		$verfahren = $Modeldata[1];
	
		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);
	
		$field = implode('_',$Modeldata);
		$Field = Inflector::camelize($field);
	
		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));
	
		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();
	
		$this->_controller->loadModel('Examiner');
		$this->_controller->loadModel('Testingmethod');
		$this->_controller->loadModel('CertificatesTestingmethode');
	
		$Testingmethod = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $testingmethodID)));
		$Testingmethods = $this->_controller->CertificatesTestingmethode->find('all',array('conditions' => array('CertificatesTestingmethode.testingmethod_id' => $testingmethodID)));
	
		$Testingmethods = Hash::extract($Testingmethods,'{n}.CertificatesTestingmethode.certificate_id');
	
		$Invoice = strtoupper($Testingmethod['Testingmethod']['invoice']);
		$ProjectTest = array_intersect($ConditionsTopprojects,array($projectID));
	
		if(count($ProjectTest) == 0) return $fields;
	
		$Conditions['fields'] = array('id','examiner_id');
		$Conditions['conditions']['Certificate.id'] = $Testingmethods;

		if($supervision == 1) $Conditions['conditions']['Certificate.supervisor'] = 1;
	
		$this->_controller->Examiner->Certificate->recursive = -1;
	
		$Certificates = $this->_controller->Examiner->Certificate->find('list',$Conditions);
	
		$this->_controller->Examiner->recursive = -1;
		
		$Examiners = $this->_controller->Examiner->find('all',array(
			'fields' => array('id','name','first_name'),
			'order' => array('name','first_name'),
			'conditions' => array(
				'Examiner.id' => $Certificates,
				'Examiner.testingcomp_id' => $testingcompID,
				'active'=>1
				)
			)
		);
	
		if(count($Examiners) == 0) return $fields;
	
		$result = Hash::combine($Examiners,array('%s %s', '{n}.Examiner.first_name', '{n}.Examiner.name'),'{n}.Examiner.id');
		$result = array_flip($result);
		$result[0] = '';
		natsort($result);
	
		$rules = array('rule' => array(),'length' => 0);
	
		if(!empty($arrayData['settings']->$Model->$field->plausibility_rules)){
			foreach($arrayData['settings']->$Model->$field->plausibility_rules->children() as $key => $value){
				
				$rule = array();
				
				foreach($value as $_key => $_value){
					$rule['type'] = trim($key);
					$rule['model'] = $Model;
					$rule['value'] = $dropdownValue;
					$rule['field'] = Inflector::camelize(trim($_value));
					$rule['thisfield'] = $Field;
				}

				$rules['rule'][] = $rule;
				++$rules['length'];
				
				unset($rule);
			}
		}

		$fields['Dropdown'][$ModelField]['name'] = 'data['.$Model.']['.$field.']';
		$fields['Dropdown'][$ModelField]['options'] = $result;
		$fields['Dropdown'][$ModelField]['length'] = count($result);
		$fields['Dropdown'][$ModelField]['rules'] = $rules;

		return $fields;

	}

	// ersetzt _GetDropdownFromCertificateModul
	protected function _GetDropdownFromCertificateModulManyTestingmethods($Reportnumber,$fields,$data) {

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);
		$testingcompID = AuthComponent::user('Testingcomp.id');
		$testingmethodID = $Reportnumber['Reportnumber']['testingmethod_id'];

		if($dropdownValue == 0 && isset($data['val']) && $data['val'] > 0){
			$dropdownValue = intval($data['val']);
		}

		$ModelField = $data['id'];
		$Modeldata = explode('_',Inflector::underscore($ModelField));
		$model = implode('_',array($Modeldata[0],$Modeldata[1],$Modeldata[2]));
		$Model = Inflector::camelize($model);
		$verfahren = $Modeldata[1];

		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);

		$field = implode('_',$Modeldata);
		$Field = Inflector::camelize($field);

		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

		$this->_controller->loadModel('Examiner');
		$this->_controller->loadModel('Testingmethod');
		$this->_controller->loadModel('CertificatesTestingmethode');

		$Testingmethod = $this->_controller->Testingmethod->find('first',array(
			'conditions' => array(
				'Testingmethod.id' => $testingmethodID
				)
			)
		);

		$Testingmethods = $this->_controller->CertificatesTestingmethode->find('all',array(
			'conditions' => array(
				'CertificatesTestingmethode.testingmethod_id' => $testingmethodID
				)
			)
		);

		$CertificateIds = Hash::extract($Testingmethods,'{n}.CertificatesTestingmethode.certificate_id');

		$ProjectTest = array_intersect($ConditionsTopprojects,array($projectID));

		if(count($ProjectTest) == 0) return $fields;

		$Certificates = $this->_CheckForHigherCertifcation($CertificateIds,$testingmethodID);

		$Conditions['fields'] = array('id','examiner_id');
		$Conditions['conditions']['Certificate.id'] = $Certificates;

		if($Field == 'Supervision') $Conditions['conditions']['Certificate.supervisor'] = 1;

		$this->_controller->Examiner->Certificate->recursive = -1;

		$Certificates = $this->_controller->Examiner->Certificate->find('list',$Conditions);

		$this->_controller->Examiner->recursive = -1;

		$Examiners = $this->_controller->Examiner->find('all',array(
			'fields' => array('id','name','first_name'),
			'order' => array('name','first_name'),
			'conditions' => array(
					'Examiner.id' => $Certificates,
					'Examiner.testingcomp_id' => $testingcompID,
					'active'=> 1
				)
			)
		);

		if(count($Examiners) == 0) return $fields;

		$result = Hash::combine($Examiners,array('%s %s', '{n}.Examiner.first_name', '{n}.Examiner.name'),'{n}.Examiner.id');
		$result = array_flip($result);
		$result[0] = '';
		natsort($result);

		$rules = array('rule' => array(),'length' => 0);

		if(!empty($arrayData['settings']->$Model->$field->plausibility_rules)){
			foreach($arrayData['settings']->$Model->$field->plausibility_rules->children() as $key => $value){
				$rule = array();
				foreach($value as $_key => $_value){
					$rule['type'] = trim($key);
					$rule['model'] = $Model;
					$rule['value'] = $dropdownValue;
					$rule['field'] = Inflector::camelize(trim($_value));
					$rule['thisfield'] = $Field;
			}
				$rules['rule'][] = $rule;
				++$rules['length'];
				unset($rule);
			}
		}

		$fields['Dropdown'][$ModelField]['name'] = 'data['.$Model.']['.$field.']';
		$fields['Dropdown'][$ModelField]['options'] = $result;
		$fields['Dropdown'][$ModelField]['length'] = count($result);
		$fields['Dropdown'][$ModelField]['rules'] = $rules;
		$fields['Depentency'] = array();

		return $fields;
	}

	protected function _CheckForHigherCertifcation($data,$testingmethod){
			
		$Conditions['fields'] = array('id','examiner_id');
		$Conditions['conditions']['Certificate.id'] = $data;
		$Conditions['conditions']['Certificate.deleted'] = 0;
		$Conditions['conditions']['Certificate.active'] = 1;
		$Conditions['conditions']['Certificate.certificate_data_active'] = 1;

		$output = array();

		$this->_controller->Examiner->Certificate->recursive = -1;

		$Certificates = $this->_controller->Examiner->Certificate->find('list',$Conditions);

		if(count($Certificates) == 0) return array();

		unset($Conditions['fields']);
		$Conditions['order'] = array('sector','testingmethod','level');
	
		foreach($Certificates as $key => $value){

			$Conditions['conditions']['Certificate.examiner_id'] = $value;
			$Examiner = $this->_controller->Examiner->Certificate->find('all',$Conditions);

			if (count($Examiner) == 0) continue;

			if(count($Examiner) < 2){

				$output[] = $Examiner[0]['Certificate']['id'];
				continue;

			} 

			// Wenn ein höheres Level einer Zertifizierung existiert
			// und diese nicht in der Verknüpfungstabelle steht
			// muss der Eintrag mit niedrigerem Level entfernt werden
			$Level = Hash::extract($Examiner, '{n}.Certificate.level');

			$MaxLevel = max($Level);

			foreach($Examiner as $_key => $_value){

				if($_value['Certificate']['level'] == $MaxLevel){

					$output[] = $_value['Certificate']['id'];
					break;

				}
			}	
		}

		return $output;
	}

	// wird ersetzt von _GetDropdownFromCertificateModulManyTestingmethods
	protected function _GetDropdownFromCertificateModul($Reportnumber,$fields) {

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);
		$testingcompID = AuthComponent::user('Testingcomp.id');
		$testingmethodID = $Reportnumber['Reportnumber']['testingmethod_id'];

		if($dropdownValue == 0 && isset($this->_controller->request->data['val']) && $this->_controller->request->data['val'] > 0){
			$dropdownValue = intval($this->_controller->request->data['val']);
		}

		$ModelField = $this->_controller->request->data['id'];
		$Modeldata = explode('_',Inflector::underscore($ModelField));
		$model = implode('_',array($Modeldata[0],$Modeldata[1],$Modeldata[2]));
		$Model = Inflector::camelize($model);
		$verfahren = $Modeldata[1];

		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);

		$field = implode('_',$Modeldata);
		$Field = Inflector::camelize($field);

		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

		$this->_controller->loadModel('Examiner');
		$this->_controller->loadModel('Testingmethod');

		$Testingmethods = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $testingmethodID)));

		$Testingmethod = Inflector::camelize($Testingmethods['Testingmethod']['value']);
		$Invoice = strtoupper($Testingmethods['Testingmethod']['invoice']);
		$ProjectTest = array_intersect($ConditionsTopprojects,array($projectID));

		if(count($ProjectTest) == 0) return $fields;

		$Conditions['fields'] = array('id','examiner_id');
		$Conditions['conditions']['Certificate.upper_testingmethod'] = $Invoice;

		if($Field == 'Supervision') $Conditions['conditions']['Certificate.supervisor'] = 1;

		$Certificates = $this->_controller->Examiner->Certificate->find('list',$Conditions);

		if(count($Certificates) == 0) return $fields;

		$this->_controller->Examiner->recursive = -1;
		$Examiners = $this->_controller->Examiner->find('all',array('fields' => array('id','name','first_name'),'conditions' => array('Examiner.id' => $Certificates,'Examiner.testingcomp_id' => $testingcompID,'active'=>1)));

		if(count($Examiners) == 0) return $fields;

		$result = Hash::combine($Examiners,array('%s %s', '{n}.Examiner.first_name', '{n}.Examiner.name'),'{n}.Examiner.id');

		$result = array_flip($result);
		$result[0] = '';
		natsort($result);

		$rules = array('rule' => array(),'length' => 0);

		if(!empty($arrayData['settings']->$Model->$field->plausibility_rules)){
			foreach($arrayData['settings']->$Model->$field->plausibility_rules->children() as $key => $value){
				$rule = array();
				foreach($value as $_key => $_value){
					$rule['type'] = trim($key);
					$rule['model'] = $Model;
					$rule['value'] = $dropdownValue;
					$rule['field'] = Inflector::camelize(trim($_value));
					$rule['thisfield'] = $Field;
			}
				$rules['rule'][] = $rule;
				++$rules['length'];
				unset($rule);
			}
		}

		$fields['Dropdown'][$ModelField]['name'] = 'data['.$Model.']['.$field.']';
		$fields['Dropdown'][$ModelField]['options'] = $result;
		$fields['Dropdown'][$ModelField]['length'] = count($result);
		$fields['Dropdown'][$ModelField]['rules'] = $rules;
		$fields['Depentency'] = array();

		return $fields;
	}

	protected function _GetDepentencyFromCertificateModul($Reportnumber,$fields) {

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$testingcompID = AuthComponent::user('Testingcomp.id');
		$testingmethodID = $Reportnumber['Reportnumber']['testingmethod_id'];

		$step = $this->_controller->request->data['step'];
		$dropdownValue = intval($this->_controller->request->data['val']);
//		if($dropdownValue == 0 && $step == 'update') return $fields;

		$ModelField = $this->_controller->request->data['id'];
		$Modeldata = explode('_',Inflector::underscore($ModelField));
		$model = implode('_',array($Modeldata[0],$Modeldata[1],$Modeldata[2]));
		$Model = Inflector::camelize($model);
		$verfahren = $Modeldata[1];

		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);

		$field = implode('_',$Modeldata);
		$Field = Inflector::camelize($field);

		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		if(empty($arrayData['settings']->$Model->$field->dependencies)) return $fields;

		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

		$this->_controller->loadModel('Examiner');
		$this->_controller->loadModel('Testingmethod');

		$Testingmethods = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $testingmethodID)));
		$Testingmethod = strtoupper($Testingmethods['Testingmethod']['invoice']);

		$ProjectTest = array_intersect($ConditionsTopprojects,array($projectID));

		if(count($ProjectTest) == 0) return $fields;

		if($step == 'update' && $dropdownValue > 0){
			$Examiner = $this->_controller->Examiner->find('first',array('conditions' => array('Examiner.id' => $dropdownValue,'Examiner.testingcomp_id' => $testingcompID)));
			$Examiner = $this->_controller->Qualification->CertificateSummary($this->_controller->Qualification->CertificatesSectors($Examiner), array('main'=>'Certificate','sub'=>'CertificateData'));
		}
		if($step == 'initial') {

			if(!isset($fields['Dropdown'][$ModelField]['options'])) return $fields;
			if(!isset($this->_controller->request->data[$Model][$field])) return $fields;

			// Muss noch geändert werden, da dieses System bei gleichen Vor- und Nachnamen versagt
			foreach($fields['Dropdown'][$ModelField]['options'] as $key => $value){

				if($key == 0) continue;

				if($value == $this->_controller->request->data[$Model][$field]){
					$Examiner = $this->_controller->Examiner->find('first',array('conditions' => array('Examiner.id' => $key,'Examiner.testingcomp_id' => $testingcompID)));
					$Examiner = $this->_controller->Qualification->CertificateSummary($this->_controller->Qualification->CertificatesSectors($Examiner), array('main'=>'Certificate','sub'=>'CertificateData'));
					break;
				}
			}
		}
		if($dropdownValue == 0 && !isset($Examiner)){

			foreach($arrayData['settings']->xpath($Model.'/*[key="'.$field.'"]/dependencies/child') as $_field) {

				$depnetencie_field = trim($_field);
				$base_field_array = explode('_',$depnetencie_field);
				$base_field = end($base_field_array);
				$DepnetencieField = Inflector::camelize($depnetencie_field);

				$fields['Depentency'][$Model . $DepnetencieField]['name'] = 'data['.$Model.']['.$depnetencie_field.']';
				$fields['Depentency'][$Model . $DepnetencieField]['options'] = array();
				$fields['Depentency'][$Model . $DepnetencieField]['length'] = 0;
				$fields['Depentency'][$Model . $DepnetencieField]['rules'] = 0;
			}

			return $fields;
		}

		if(!isset($Examiner)) return $fields;
		if(!isset($Examiner['qualifications'])) return $fields;

		foreach($Examiner['qualifications'] as $key => $value){

			if(empty($value['status_class'])) continue;

			if(array_search($testingmethodID, $Examiner['testingmethods'][$key]) === false) continue;

			switch($value['status_class']){

				case 'certification_time_reached':
				case 'certification_not_valid':

					$Error = $Examiner['summary']['errors'][$value['certificate_data_id']];

					unset($Error['info']);
					unset($Error['certificate']);
					unset($Error['examiner']);

					$ErrorText = implode("\n",$Error);

					if($Error == NULL && $value['status_class'] == "certification_time_reached"){
						if(isset($Examiner['summary']['futurenow'][$value['certificate_data_id']])){
							if(isset($Examiner['summary']['futurenow'][$value['certificate_data_id']]['recertified_soon_date'])){
								$Error = $Examiner['summary']['futurenow'][$value['certificate_data_id']];
								unset($Error['info']);
								unset($Error['certificate']);
								unset($Error['examiner']);
								unset($Error['recertified_soon_date']);
								$ErrorText = implode("\n",$Error);
							}
						}
					}

					foreach($arrayData['settings']->xpath($Model.'/*[key="'.$field.'"]/dependencies/child') as $_field) {

						$depnetencie_field = trim($_field);
						$base_field_array = explode('_',$depnetencie_field);
						$base_field = end($base_field_array);
						$DepnetencieField = Inflector::camelize($depnetencie_field);

						$fields['Depentency'][$Model . $DepnetencieField]['name'] = 'data['.$Model.']['.$depnetencie_field.']';
						$fields['Depentency'][$Model . $DepnetencieField]['options'] = array();
						$fields['Depentency'][$Model . $DepnetencieField]['length'] = 0;

						$fields['ModulError'][$Model . $DepnetencieField]['text'] = $ErrorText;
						$fields['ModulError'][$Model . $DepnetencieField]['length'] = 1;

					}

				break;

				case 'certification_not_valid_soon':
				case 'certification_valid':

					$Success = NULL;

					if(isset($Examiner['summary']['hints'][$value['certificate_data_id']][0])) $Success .= __('Certificate information') . ': ' . $Examiner['summary']['hints'][$value['certificate_data_id']][0];
					if(isset($Examiner['summary']['warnings'][$value['certificate_data_id']][0])) $Success .= __('Certificate information') . ': ' . $Examiner['summary']['warnings'][$value['certificate_data_id']][0];

					$fields['ModulSuccess'][$Model . $Field]['text'] = $Success;
					$fields['ModulSuccess'][$Model . $Field]['length'] = 1;

					foreach($arrayData['settings']->xpath($Model.'/*[key="'.$field.'"]/dependencies/child') as $_field) {

						$depnetencie_field = trim($_field);
						$base_field_array = explode('_',$depnetencie_field);
						$base_field = end($base_field_array);
						$DepnetencieField = Inflector::camelize($depnetencie_field);

						$fields['Depentency'][$Model . $DepnetencieField]['name'] = 'data['.$Model.']['.$depnetencie_field.']';
						$fields['Depentency'][$Model . $DepnetencieField]['options'] = array($value[$base_field] => $value[$base_field]);
						$fields['Depentency'][$Model . $DepnetencieField]['length'] = 1;
					
					}

				break;
			}

			break;

			}

		return $fields;
	}

	protected function _GetFromMasterMultiselectDepentendy($Reportnumber,$fields) {

		$MasterIds = $this->_controller->Drops->CheckAuthorization($Reportnumber);
		if($MasterIds == false) return false;

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);
		$lang = $this->_controller->request->lang;

		if($dropdownValue == 0 && isset($this->_controller->request->data['val']) && $this->_controller->request->data['val'] > 0) $dropdownValue = intval($this->_controller->request->data['val']);

		$this->_controller->loadModel('DropdownsMaster');
		$this->_controller->loadModel('DropdownsMastersDependency');
		$this->_controller->loadModel('DropdownsMastersDependenciesField');

		$ModelField = $this->_controller->request->data['id'];
		$model_field = Inflector::underscore($ModelField);
		$model_field_array = explode('_',$model_field);
		$Model = Inflector::camelize($model_field_array[0]) . Inflector::camelize($model_field_array[1]) . Inflector::camelize($model_field_array[2]);
		$Area = $model_field_array[2] . '_area';
		$verfahren = $model_field_array[1];

		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		unset($model_field_array[0]);
		unset($model_field_array[1]);
		unset($model_field_array[2]);

		$field = implode('_',$model_field_array);

		if(!isset($this->_controller->request->data[$Model][$field])) return $fields;

		$ValueId = $this->_controller->request->data[$Model][$field];

		if(empty($ValueId)) return false;

		$AllIds = array();
		if(is_array($ValueId)){
			foreach ($ValueId as $key => $value) {
				$AllIdsArray = explode(',',$value);
			}
		}

		if(count($AllIdsArray) == 0) return false;


		$options = array('conditions' => array(
				'DropdownsMaster.id' => $dropdownID,
				'DropdownsMaster.modul' => $Area,
				'DropdownsMaster.field' => $field,
				'DropdownsMaster.deleted' => 0
			)
		);

		$this->_controller->DropdownsMaster->recursive = -1;
		$DropdownsMaster = $this->_controller->DropdownsMaster->find('first',$options);

		if(count($DropdownsMaster) == 0) return false;

		$options = array(
			'conditions' => array(
				'DropdownsMastersDependenciesField.dropdowns_masters_id' => $dropdownID,
			),
			'fields' => array('field','field_type','id')
		);

		$DropdownsMastersDependenciesField = $this->_controller->DropdownsMastersDependenciesField->find('all',$options);

		if(count($DropdownsMastersDependenciesField) == 0) return $fields;

		foreach($DropdownsMastersDependenciesField as $key => $value){

			$field = $value['DropdownsMastersDependenciesField']['field'];
			$field_type = $value['DropdownsMastersDependenciesField']['field_type'];

			$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['name'] = 'data['.$Model.']['.trim($field).']';
			$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['options'] = array();
			$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['length'] = 0;

			$options = array(
				'conditions' => array(
					'DropdownsMastersDependency.field' => $field,
					'DropdownsMastersDependency.dropdowns_masters_id' => $dropdownID,
					'DropdownsMastersDependency.dropdowns_masters_data_id' => $AllIdsArray,
				),
				'fields' => array(
					'value','value'
				)
			);

			$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('list',$options);

			if(count($DropdownsMastersDependency) > 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['save'] = $field_type;
				$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['name'] = 'data['.$Model.']['.trim($field).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['options'] = $DropdownsMastersDependency;
				$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['length'] = count($DropdownsMastersDependency);

				$ParentField = trim($arrayData['settings']->$Model->$field->dependencies->parent);

				if(!empty($ParentField)){
					if(!empty($arrayData['settings']->$Model->$ParentField->depentency_save)){
						if(trim($arrayData['settings']->$Model->$ParentField->depentency_save) == 'direct'){

							if(!empty($arrayData['settings']->$Model->$ParentField->edit_after_closing)){
								$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['edit_after_closing'] = 1;
							}

							$fields['Depentency'][$Model . Inflector::camelize(trim($field))]['save'] = 'direct';
					    $fields['Depentency'][$Model . Inflector::camelize(trim($field))]['description'] = trim($arrayData['settings']->$Model->$ParentField->discription->$lang);

						}
					}
				}
			}
		}

		return $fields;

	}

	protected function _GetFromDropdownDepentendy($Reportnumber,$fields) {

		if(isset($fields['Depentency'])) return $fields;

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);

		if($dropdownValue == 0 && isset($this->_controller->request->data['val']) && $this->_controller->request->data['val'] > 0) $dropdownValue = intval($this->_controller->request->data['val']);

		$fields['Depentency'] = array();

		$Modeldata = explode('_',Inflector::underscore($this->_controller->request->data['id']));
		$type = $Modeldata[0];
		$verfahren = $Modeldata[1];
		$model = $Modeldata[2];

		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);

		unset($Modeldata);

		$Modeldata = implode('_',array($type,$verfahren,$model));
		$Model = Inflector::camelize($Modeldata);
		$field = key($this->_controller->request->data[$Model]);
		if(isset($this->_controller->request->data[$Model][$field])){
			if(!is_array($this->_controller->request->data[$Model][$field]) && !empty($this->_controller->request->data[$Model][$field])){
				$dropdownValue = $this->_controller->request->data[$Model][$field];
			}
		}

		$this->_controller->Dependency->Dropdown->recusive = -1;

		$dropdown = $this->_controller->Dependency->Dropdown->find('first', array(
			'conditions'=>array(
				'Dropdown.model' => $Model,
				'Dropdown.field' => $field,
				'Dropdown.id' => $dropdownID
				)
			)
		);

		if(count($dropdown) == 0) return false;
		if($dropdown['Dropdown']['model'] != $Model) return false;

		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		$this->_controller->Dependency->DropdownsValue->recursive = -1;
		$value = $this->_controller->Dependency->DropdownsValue->find('first', array('conditions'=>array('DropdownsValue.id' => $dropdownValue)));

		if(empty($arrayData['settings']->$Model->$field->dependencies)) return false;

		$this->_controller->Dependency->recursive = -1;

		foreach($arrayData['settings']->xpath($Model.'/*[key="'.$field.'"]/dependencies/child') as $_field) {

			$dependencies = $this->_controller->Dependency->find('list', array(
				'fields'=>array('value', 'value'),
				'conditions'=>array(
					'or' => array(
						'Dependency.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
						'Dependency.global' => 1
					),
					'Dependency.field' => trim($_field),
					'Dependency.dropdowns_value_id' => $dropdownValue
				)
			));

			if(count($dependencies) > 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['name'] = 'data['.$Model.']['.trim($_field).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['options'] = $dependencies;
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['length'] = count($dependencies);
			}
			if(count($dependencies) == 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['name'] = 'data['.$Model.']['.trim($_field).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['options'] = array();
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['length'] = 0;
			}
		}

		return $fields;
	}

	protected function _GetFromDropdownMultiDepentendy($Reportnumber,$fields) {

		if(isset($fields['Depentency'])) return $fields;

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);
		$lang = $this->_controller->request->lang;

		$selected = explode(',',$this->_controller->request->data['val']);
		$fields['Depentency'] = array();

		$Modeldata = explode('_',Inflector::underscore($this->_controller->request->data['id']));
		$type = $Modeldata[0];
		$verfahren = $Modeldata[1];
		$model = $Modeldata[2];

		unset($Modeldata[0]);
		unset($Modeldata[1]);
		unset($Modeldata[2]);

		unset($Modeldata);

		$Modeldata = implode('_',array($type,$verfahren,$model));
		$Model = Inflector::camelize($Modeldata);

		$field = key($this->_controller->request->data[$Model]);

		$this->_controller->Dependency->Dropdown->recusive = -1;
		$dropdown = $this->_controller->Dependency->Dropdown->find('first', array('fields'=>array('model', 'field'), 'conditions'=>array('Dropdown.id' => $dropdownID)));

		if(count($dropdown) == 0) return false;
		if($dropdown['Dropdown']['model'] != $Model) return false;

		$arrayData = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		if(isset($this->_controller->request->data[$Model][$field][0]) && !empty($this->_controller->request->data[$Model][$field][0])){

			$selected = explode(',',$this->_controller->request->data[$Model][$field][0]);

			foreach ($selected as $key => $value) {
				$this->_controller->Dependency->DropdownsValue->recursive = -1;
				$values[] = $this->_controller->Dependency->DropdownsValue->find('first', array('conditions'=>array('OR'=>array('DropdownsValue.dropdown_id' => $dropdownID,'DropdownsValue.global' => 1),'DropdownsValue.id' => $value)));
			}

		} else {

			foreach ($selected as $key => $value) {
				$this->_controller->Dependency->DropdownsValue->recursive = -1;
				$values[] = $this->_controller->Dependency->DropdownsValue->find('first', array('conditions'=>array('DropdownsValue.dropdown_id' => $dropdownID,'DropdownsValue.discription' => $value)));
			}

		}

		$dpvaluesids[] = Hash::extract($values, '{n}.DropdownsValue.id');
		if(empty($arrayData['settings']->$Model->$field->dependencies)) return false;

		$this->_controller->Dependency->recursive = -1;
		$dpvaluesids = $dpvaluesids[0];
		foreach($arrayData['settings']->xpath($Model.'/*[key="'.$field.'"]/dependencies/child') as $_field) {

			$dependencies = $this->_controller->Dependency->find('list', array(
				'fields'=>array('value', 'value'),
				'conditions'=>array(
					'OR' => array(
						'Dependency.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
						'Dependency.global' => 1
					),
					'Dependency.field' => trim($_field),
					'Dependency.dropdowns_value_id' => $dpvaluesids
				)
			));

			if(count($dependencies) > 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['name'] = 'data['.$Model.']['.trim($_field).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['options'] = $dependencies;
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['length'] = count($dependencies);

				if(!empty($arrayData['settings']->$Model->$field->depentency_save)){
					if(trim($arrayData['settings']->$Model->$field->depentency_save) == 'direct'){

						$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['save'] = 'direct';
						$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['description'] = trim($arrayData['settings']->$Model->$_field->discription->$lang);
					}
				}
			}
			if(count($dependencies) == 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['name'] = 'data['.$Model.']['.trim($_field).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['options'] = array();
				$fields['Depentency'][$Model . Inflector::camelize(trim($_field))]['length'] = 0;
			}
		}

		return $fields;

	}

  protected function _GetFromMasterDropdownDepentendy($Reportnumber,$fields) {

		$MasterIds = $this->_controller->Drops->CheckAuthorization($Reportnumber);

		if($MasterIds == false) return false;

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->_controller->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->_controller->request->projectvars['VarsArray'][10]);

		if($dropdownValue == 0 && isset($this->_controller->request->data['val']) && $this->_controller->request->data['val'] > 0) $dropdownValue = intval($this->_controller->request->data['val']);

		$this->_controller->loadModel('DropdownsMaster');
		$this->_controller->loadModel('DropdownsMastersData');
		$this->_controller->loadModel('DropdownsMastersDependency');
		$this->_controller->loadModel('DropdownsMastersDependenciesField');



		$ModelField = $this->_controller->request->data['id'];
		$model_field = Inflector::underscore($ModelField);
		$model_field_array = explode('_',$model_field);
		$Model = Inflector::camelize($model_field_array[0]) . Inflector::camelize($model_field_array[1]) . Inflector::camelize($model_field_array[2]);
		$Area = $model_field_array[2] . '_area';


		unset($model_field_array[0]);
		unset($model_field_array[1]);
		unset($model_field_array[2]);

		$field = key($this->_controller->request->data[$Model]);
///		$field = implode('_',$model_field_array);

		if(!isset($this->_controller->request->data[$Model][$field])) return $fields;


		$ValueId = $this->_controller->request->data[$Model][$field];

		$DropdownMasterData = $this->_controller->DropdownsMastersData->find('first',array('conditions' => array('DropdownsMastersData.id' => $ValueId)));

		if(count($DropdownMasterData) == 0) return $fields;

		$dropdownID = $DropdownMasterData['DropdownsMastersData']['dropdowns_masters_id'];

		$options = array(
			'conditions' => array(
				'DropdownsMaster.id' => $dropdownID,
				'DropdownsMaster.field' => $field,
				'DropdownsMaster.modul' => $Area,
				'DropdownsMaster.deleted' => 0
			)
		);
		$DropdownsMaster = $this->_controller->DropdownsMaster->find('first',$options);

		if(count($DropdownsMaster) == 0) return $fields;
		if($DropdownsMaster['DropdownsMaster']['status'] == 1) return $fields;
		if($DropdownsMaster['DropdownsMaster']['dependencies'] == 0) return $fields;

		$options = array(
			'conditions' => array(
				'DropdownsMastersDependenciesField.dropdowns_masters_id' => $dropdownID,
			),
			'fields' => array('field')
		);

		$DropdownsMastersDependenciesField = $this->_controller->DropdownsMastersDependenciesField->find('list',$options);

		if(count($DropdownsMastersDependenciesField) == 0) return $fields;

		foreach($DropdownsMastersDependenciesField as $key => $value){
			$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['name'] = 'data['.$Model.']['.trim($value).']';
			$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['options'] = array();
			$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['length'] = 0;
		}


		if(empty($ValueId)) return $fields;

		$options = array('conditions' => array(
				'DropdownsMaster.id' => $dropdownID,
				'DropdownsMaster.modul' => $Area,
				'DropdownsMaster.field' => $field,
			)
		);

		$DropdownsMaster = $this->_controller->DropdownsMaster->find('first',$options);

		if(count($DropdownsMaster) == 0) return $fields;
		$DropdownsMastersDat = Hash::extract($DropdownsMaster['DropdownsMastersData'], '{n}[id=' . $ValueId . ']');

		if(count($DropdownsMastersDat) == 0) return $fields;

		$DropdownsMastersData = $DropdownsMastersDat[0];
		$DropdownsMastersDataId = $DropdownsMastersData['id'];

		$options = array(
			'conditions' => array(
				'DropdownsMastersDependency.dropdowns_masters_id' => $dropdownID,
				'DropdownsMastersDependency.dropdowns_masters_data_id' => $DropdownsMastersDataId,
			),
			'fields' => array(
				'value','value'
			)
		);

		$fields = array();

		foreach ($DropdownsMastersDependenciesField as $key => $value) {

			if(empty($value)) continue;

			$options['conditions']['DropdownsMastersDependency.field'] = $value;

			$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('list',$options);

			if(count($DropdownsMastersDependency) > 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['name'] = 'data['.$Model.']['.trim($value).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['options'] = $DropdownsMastersDependency;
				$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['length'] = count($DropdownsMastersDependency);
			}
			if(count($DropdownsMastersDependency) == 0) {
				$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['name'] = 'data['.$Model.']['.trim($value).']';
				$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['options'] = array();
				$fields['Depentency'][$Model . Inflector::camelize(trim($value))]['length'] = 0;
			}
		}

		return $fields;

	}

}
