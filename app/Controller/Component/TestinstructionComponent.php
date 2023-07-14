<?php
class TestinstructionComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	// funktioniert ähnlich der Autoload-Funktion von php
	public function Authorization($projectID,$reportID,$testingmethodID,$testingcompID) {

		$Output = array();

		$Options['conditions'] = array();

		if(count($projectID) > 0)$Options['conditions']['topproject_id'] = $projectID;
		if(count($reportID) > 0)$Options['conditions']['report_id'] = $reportID;
		if(count($testingmethodID) > 0)$Options['conditions']['testingmethod_id'] = $testingmethodID;
		if(count($testingcompID) > 0)$Options['conditions']['testingcomp_id'] = $testingcompID;

//		$Options['fields'] = array('testingstruction_id','testingstruction_id');
//		$Options['group'] = array('testingstruction_id');

		$this->_controller->loadModel('TestinginstructionsAuthorization');
		$TestinginstructionsAuthorization = $this->_controller->TestinginstructionsAuthorization->find('all',$Options);

		$Output['Result'] = $TestinginstructionsAuthorization;
		$Output['TestingstructionIDs'] = array_unique(Hash::extract($TestinginstructionsAuthorization, '{n}.TestinginstructionsAuthorization.testingstruction_id'));

		return $Output;

	}

	public function SaveAuthorization($data,$id) {

		if(count($data) == 0) return false;

		$this->_controller->TestinginstructionsAuthorization->deleteAll(array('TestinginstructionsAuthorization.testingstruction_id' => $id), false);

		foreach ($data as $key => $value) {

			if(count($value) == 0) continue;

			foreach ($value as $_key => $_value) {

				if(count($_value) != 5) continue;

				$conditions['conditions'] = $_value;

				$TestinginstructionsAuthorization = $this->_controller->TestinginstructionsAuthorization->find('first',$conditions);

				if(count($TestinginstructionsAuthorization) == 0){

					$insert = array();

					foreach($conditions['conditions'] as $__key => $__value){
						$insert_value = array_values($__value);
						$insert[$__key] = $insert_value[0];
					}

					$this->_controller->TestinginstructionsAuthorization->create();
					$this->_controller->TestinginstructionsAuthorization->save($insert);
				}
			}
		}
	}

	public function MergeDescriptions($data,$xml,$testingmethod,$locale) {

		$verfahren = $testingmethod['Testingmethods']['value'];
		$Verfahren = Inflector::camelize($verfahren);

		$Models = $this->CreateModels($Verfahren);

		$xml = $xml['settings'];

		$TypeOptions[0] = 'Erläuterung';
		$TypeOptions[1] = 'Hinweis';
		$TypeOptions[2] = 'Pflichtfeld';

		foreach ($Models as $key => $value) {

			$model = $value['model'];

			foreach ($data['TestinginstructionsData'] as $_key => $_value) {

				$field = $_value['field'];

				if($_value['model'] != $model) continue;
				if(!isset($xml->$model->$field)) continue;

				$data['TestinginstructionsData'][$_key]['model_real_name'] = $data['TestinginstructionsData'][$_key]['model'];
				$data['TestinginstructionsData'][$_key]['model_real_field'] = $data['TestinginstructionsData'][$_key]['field'];
				$data['TestinginstructionsData'][$_key]['model'] = $value['description'];
				$data['TestinginstructionsData'][$_key]['field'] = trim($xml->$model->$field->discription->$locale);
				$data['TestinginstructionsData'][$_key]['type'] = $TypeOptions[$data['TestinginstructionsData'][$_key]['type']];

			}
		}

		return $data;
	}

	public function AreaDivision($data,$testingmethod) {

		if(!isset($data['TestinginstructionsData'])) return $data;
		if(count($data['TestinginstructionsData']) == 0) return $data;

		$verfahren = $testingmethod['Testingmethods']['value'];
		$Verfahren = Inflector::camelize($verfahren);

		$Devisions['Division'] = array('Generally','Specific','Evaluation');
		$Devisions['Models'] = array('Report' . $Verfahren . 'Generally','Report' . $Verfahren . 'Specific','Report' . $Verfahren . 'Evaluation');
		$Devisions['Models'] = array('Report' . $Verfahren . 'Generally','Report' . $Verfahren . 'Specific','Report' . $Verfahren . 'Evaluation');
		$Devisions['Class'] = array('generally_area','specific_area','evaluation_area');
		$Devisions['Results'] = array();

		foreach ($Devisions['Models'] as $key => $value) {
			$Devisions['Results'][$Devisions['Division'][$key]] = Hash::extract($data['TestinginstructionsData'], '{n}[model_real_name='.$value.']');
			$Devisions['Results'][$Devisions['Division'][$key]] = Hash::sort($Devisions['Results'][$Devisions['Division'][$key]], '{n}.field', 'asc');
		}

		$data['Devisions'] = $Devisions;

		return $data;
	}

	public function CreateFieldOptions($xml,$models,$data,$used,$locale){

		$output = array();

		if(count($models) == 0) return $output;

		$xml = $xml['settings'];

		$used = array_flip($used);

		if(count($data) > 0) $current = $data['TestinginstructionsData']['field'];
		else $current = null;

		foreach ($models as $key => $value) {

				if(!isset($xml->$key)) continue;

				foreach ($xml->$key->children() as $_key => $_value) {

					if(empty($_value->output->screen)) continue;

					$field = trim($_value->key);

					if($current == null){
						if(isset($used[$field])) continue;
						$output[$key][$field] = trim($_value->discription->$locale);
						continue;
					}

					if(isset($used[$field]) && $current != $field) continue;

					$output[$key][$field] = trim($_value->discription->$locale);

				}
		}

		return $output;
	}

	public function CreateModels($Verfahren){

		$Models['ReportGenerally']['model'] = 'Report'.$Verfahren.'Generally';
		$Models['ReportGenerally']['description'] = __('Object data',true);
		$Models['ReportSpecific']['model'] = 'Report'.$Verfahren.'Specific';
		$Models['ReportSpecific']['description'] = __('Testing data',true);
		$Models['ReportEvaluation']['model'] = 'Report'.$Verfahren.'Evaluation';
		$Models['ReportEvaluation']['description'] = __('Evaluations',true);

		return $Models;

	}

	public function CheckFormularData($data,$xml) {

		if(Configure::check('TestinginstructionManager') == false) return false;
		if(Configure::read('TestinginstructionManager') != true) return false;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$testingmethodID = $data['Reportnumber']['testingmethod_id'];

		$this->_controller->loadModel('TestinginstructionsAuthorization');

		$Options['conditions'] = array();
		$Options['conditions']['topproject_id'] = $projectID;
		$Options['conditions']['report_id'] = $reportID;
		$Options['conditions']['testingmethod_id'] = $testingmethodID;
		$Options['conditions']['testingcomp_id'] = AuthComponent::user('testingcomp_id');

		$TestinginstructionsAuthorization = $this->_controller->TestinginstructionsAuthorization->find('first',$Options);

		if(count($TestinginstructionsAuthorization) == 0) return false;

		$this->_controller->loadModel('Testinginstruction');

		$Options['conditions'] = array();
		$Options['conditions']['Testinginstruction.id'] = $TestinginstructionsAuthorization['TestinginstructionsAuthorization']['testingstruction_id'];
		$Options['conditions']['Testinginstruction.status'] = 1;
		$Options['conditions']['Testinginstruction.deleted'] = 0;

		$Testinginstruction = $this->_controller->Testinginstruction->find('first',$Options);

		if(count($Testinginstruction) == 0) return false;

		$this->_controller->loadModel('TestinginstructionsData');

		$Options['conditions'] = array();
		$Options['conditions']['TestinginstructionsData.testinginstruction_id'] = $Testinginstruction['Testinginstruction']['id'];

		$TestinginstructionData = $this->_controller->TestinginstructionsData->find('all',$Options);
		if(count($TestinginstructionData) == 0) return false;

		$this->MatchFormularData($TestinginstructionData,$xml);

		return;
	}

	public function MatchFormularData($Testinginstructions,$xml) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];

		$output = array();

		if(Configure::check('TestinginstructionManager') == false) return false;
		if(Configure::read('TestinginstructionManager') != true) return false;

		if(count($Testinginstructions) == 0) return;

		$this->_controller->loadModel('TestinginstructionsIrregularitie');

		$lang = $this->_controller->request->lang;

		$this->_controller->request->testinstruction = (object) array('data' => array(),'deviation' => array());

		foreach($this->_controller->request->tablenames as $_key => $_data){

			$Result = Hash::extract($Testinginstructions, '{n}.TestinginstructionsData[model=' . $_data . ']');

			if(count($Result) == 0) continue;

			foreach($Result as $__key => $__data){

				$options = array(
	              'conditions' => array(
	                'TestinginstructionsIrregularitie.reportnumber_id' => $reportnumberID,
	                'TestinginstructionsIrregularitie.testinginstruction_data_id' => $__data['id'],
	              )

	          );

	      $TestingstructionIrregularitie = $this->_controller->TestinginstructionsIrregularitie->find('first', $options);

				if(count($TestingstructionIrregularitie) > 0) $this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['reason'] = $TestingstructionIrregularitie['TestinginstructionsIrregularitie']['reason'];

				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['data_id'] = $__data['id'];
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['data_description'] = $__data['description'];
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['current_description'] = __('Current value in this testing report',true);

				if(!empty($xml->{$__data['model']}->{$__data['field']}->select->model)){
					$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['html_field'] = 'dropdown';
				} else {
					$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['html_field'] = false;
				}
				if(!empty($this->_controller->request->data[$__data['model']][$__data['field']])){
					$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['current'] = $this->_controller->request->data[$__data['model']][$__data['field']];
				} else {
					$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['current'] = false;
				}

				if(!empty($xml->{$__data['model']}->{$__data['field']}->discription->$lang)){
					$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['title'] = trim($xml->{$__data['model']}->{$__data['field']}->discription->$lang);
				}

				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['remark'] = $__data['remark'];
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['remark_description'] = __('Remark',true);
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['value'] = $__data['value'];
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['value_description'] = __('Value according to test specification',true);
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['field'] = Inflector::camelize($__data['field']);
				$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['error_description'] = __('This entry is not compatible with the test specification',true);

				switch($__data['type']){
					case 0:

						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['hint'] = true;
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['hint_description'] = __('This entry is an explanation',true);
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['rule'] = false;
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['option'] = false;

					break;

					case 1:

						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['option'] = true;
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['option_description'] = __('This entry is an option',true);
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['rule'] = false;
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['hint'] = false;

					break;

					case 2:

						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['rule'] = true;
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['rule_description'] = __('This entry is a rule and must be adopted',true);
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['hint'] = false;
						$this->_controller->request->testinstruction->data[$__data['model']][$__data['field']]['option'] = false;

						if(empty($this->_controller->request->data[$__data['model']][$__data['field']])){
						} else {
							if($__data['value'] != $this->_controller->request->data[$__data['model']][$__data['field']]){
								$this->_controller->request->testinstruction->deviation[$__data['model']][$__data['field']]['value']['right'] = $__data['value'];
								$this->_controller->request->testinstruction->deviation[$__data['model']][$__data['field']]['value']['wrong'] = $this->_controller->request->data[$__data['model']][$__data['field']];
							}
						}

					break;
				}
			}
		}

		if(!isset($this->_controller->request->testinstruction->deviation)) $this->_controller->request->testinstruction->deviation['Model'] = 0;

		return;
	}
}
