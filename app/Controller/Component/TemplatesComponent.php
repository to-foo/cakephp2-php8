<?php
class TemplatesComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function CheckForAttention($data){

		if(!isset($data['Attention'])) return $data;
		if(empty($data['Attention'])) return $data;

		$data['AttentionInfo'] = array();

		foreach ($data['Attention'] as $key => $value) {

  		if(empty($value)) continue;

			foreach ($value as $_key => $_value) {

				if(!isset($data['Template']['data'][$key][$_key])) continue;

				$data['AttentionInfo'][$data['Template']['data'][$key][$_key]['id']] = $data['Template']['data'][$key][$_key];

			}
		}

		unset($data['Template']);
		unset($data['Testingcomp']);
		unset($data['Attention']);

		return $data;
	}

	public function CheckForAttentionEvaluation($data){

		if(!isset($data['Attention'])) return $data;
		if(empty($data['Attention'])) return $data;

		$data['AttentionInfo'] = array();

		foreach ($data['Attention'] as $key => $value) {

  		if(empty($value)) continue;

			foreach ($value as $_key => $_value) {

				$Id = $key . Inflector::camelize($_key);

	//			if(!isset($data['Template']['data'][$key][$_key])) continue;

				$data['AttentionInfo'][$Id]['id'] = $Id;
				$data['AttentionInfo'][$Id]['name'] = 'data[' . $key . '][' . $_key . ']';
				$data['AttentionInfo'][$Id]['value'] = '';

			}
		}

		unset($data['Template']);
		unset($data['TemplatesEvaluation']);
		unset($data['Testingcomp']);
		unset($data['Attention']);

		return $data;
	}

	public function FindEvaluationTemplate($data){
		// ColledtEvaluationTemplate
		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $data['TemplatesEvaluationReportnumber']['template_id'])));

		if(empty($TemplatesEvaluation)) return array();

		$TemplatesEvaluation = $this->ColledtEvaluationTemplate($TemplatesEvaluation);

		if(!isset($TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'])) return array();
		if(empty($TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'])) return array();

		return $TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'];

	}

	public function FindEvaluationTemplateForReport($data,$TemplatesEvaluation){

		$template_id = $data['TemplatesEvaluationReportnumber']['template_id'];
		$evaluation_id = $data['TemplatesEvaluationReportnumber']['evaluation_id'];
		$evaluation_key = 0;

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $template_id)));

		if(empty($TemplatesEvaluation)) return array();

		$TemplatesEvaluation = $this->ColledtEvaluationTemplate($TemplatesEvaluation);

		if(!isset($TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'])) return array();
		if(empty($TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'])) return array();

		$model = key($TemplatesEvaluation['TemplatesEvaluation']['data']);

		$output = array();

		foreach ($TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'][$model] as $key => $value) {

			$output[$model][$key] =
			$TemplatesEvaluation['TemplatesEvaluation']['data'][$model][$evaluation_key][$key];
			$output[$model][$key]['template_id'] = $template_id;
			$output[$model][$key]['evaluation_id'] = $evaluation_id;

		}

		$TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'] = $output;

		return $TemplatesEvaluation['TemplatesEvaluation']['data']['Attention'];

	}

	public function FindTemplates(){

		$data = array();

		if(isset($this->_controller->request->data['Quicksearch']['this_id'])){

			$this_id = explode('_',$this->_controller->request->data['Quicksearch']['this_id']);
			$quicksearch_id = intval($this_id[0]);

			$options = array('conditions' => array('Template.id' => $quicksearch_id));

			$Templates = $this->_controller->Template->find('all',$options);

		} else {
			$Templates = $this->_controller->Template->find('all');
		}

		if(empty($Templates)) return $data;

		foreach ($Templates as $key => $value) {

			$data[$key] = $value;
	//		$data[$key] = $this->CollectEvaluationTemplates($value);

			if(!isset($data[$key]['Testingcomps'])) continue;
			$Testingcomps = array();

			foreach ($data[$key]['Testingcomps'] as $key => $value) {
				$Testingcomps[] = $value['Testingcomp']['name'];
			}

			$data[$key]['TestingcompsString'] = implode(', ',$Testingcomps);

		}

		return $data;
	}

	public function CollectTestingmethods($data){

		$this->_controller->Testingmethod->recursive = -1;

		$Testingmethod = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $data['Template']['testingmethod_id'])));

		$data['Testingmethod'] = $Testingmethod['Testingmethod'];

		return $data;
	}

	public function CollectTestingcoms($data){

		$testingcomps = $this->_controller->Data->BelongsToManySelected($data, 'Template', 'Testingcomp', array('TemplatesTestingcomp','testingcomp_id','templates_id'));

		$data['Testingcomp'] = $testingcomps['Testingcomp']['selected'];

		return $data;
	}

	public function CheckTestingcoms($data){

		$testingcomp_id = $this->_controller->Auth->user('testingcomp_id');

		foreach ($data as $key => $value) {

			if(!isset($value['Testingcomp'])) continue;
			if(count($value['Testingcomp']) == 0) continue;

			$delVal = 0;

			foreach ($value['Testingcomp'] as $_key => $_value) {
				if($_value ==	$testingcomp_id) $delVal++;
			}

			if($delVal == 0) unset($data[$key]);

		}

		return $data;
	}

	public function DeleteTemplate($data){

		if(!isset($this->_controller->request->data['Template'])) return null;
		if(!isset($this->_controller->request->data['Template']['id'])) return null;

		$id = $this->_controller->request->projectvars['VarsArray'][1];

		$this->_controller->loadModel('TemplatesReportnumber');
		$this->_controller->loadModel('TemplatesTestingcomp');

		$Check = $this->_controller->Template->delete($id);

		if($Check === false) return false;

		$Check = $this->_controller->TemplatesReportnumber->deleteAll(array('TemplatesReportnumber.template_id' => $id), false);

		if($Check === false) return false;

		$Check = $this->_controller->TemplatesTestingcomp->deleteAll(array('TemplatesTestingcomp.templates_id' => $id), false);

		if($Check === false) return false;

		return true;
	}

	public function DeleteEvaluationTemplate($data){

		if(!isset($this->_controller->request->data['delete_evaluation_template'])) return $data;

		$id = $this->_controller->request->data['TemplatesEvaluation']['id'];

		$Test = $this->_controller->TemplatesEvaluation->delete($id);

		if($Test === false){
			$this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
		} else {
			$this->_controller->Flash->success(__('The template has been deleted.'), array('key' => 'success'));
			$data['delete_evaluation_template_okay'] = 1;
		}

		return $data;

	}

	public function FindTemplateEvaluationTestingcomp(){

		$this->_controller->loadModel('TemplatesEvaluationReportnumber');
		$this->_controller->loadModel('ReportRtEvaluation');

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluationReportnumber->find('all');

		foreach($TemplatesEvaluation as $key => $value){

			$this->_FindTemplateEvaluationTestingcomp($value['TemplatesEvaluationReportnumber']);

		}
	}

	protected function _FindTemplateEvaluationTestingcomp($data){

		$Evaluation = $this->_controller->ReportRtEvaluation->find('first',array('conditions' => array('ReportRtEvaluation.id' => $data['evaluation_id'])));

		if(count($Evaluation) == 0) return;

		$this->_controller->Reportnumber->recursive = -1;

		$Report = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $Evaluation['ReportRtEvaluation']['reportnumber_id'])));
		
		$data['testingcomp_id'] = $Report['Reportnumber']['testingcomp_id'];
		$data['id'] = $data['template_id'];

		$this->_controller->TemplatesEvaluation->save($data);

	}

	public function FindEvaluationTemplates(){

		if(isset($this->_controller->request->data['Quicksearch']['this_id'])){

			$this_id = explode('_',$this->_controller->request->data['Quicksearch']['this_id']);
			$quicksearch_id = intval($this_id[0]);

			$options = array(
				'conditions' => array(
					'TemplatesEvaluation.id' => $quicksearch_id,
					'TemplatesEvaluation.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id')
				)
			);

			$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('all',$options);

		} else {

			$options = array(
				'conditions' => array(
					'TemplatesEvaluation.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id')
				)
			);

			$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('all');
		}

		$this->_controller->Testingmethod->recursive = -1;

		foreach($TemplatesEvaluation as $key => $value){

			$Testingmethod = $this->_controller->Testingmethod->find('first',array(
				'conditions' => array(
					'Testingmethod.id' => $value['TemplatesEvaluation']['testingmethod_id']
					)
				)
			);

			if(count($Testingmethod) == 0) continue;

			$TemplatesEvaluation[$key]['TemplatesEvaluation']['testingmethod_id'] = $Testingmethod['Testingmethod']['verfahren'];
	
		}

		return $TemplatesEvaluation;

	}

	public function ColledtEvaluationTemplate($data){

		if(empty($data['TemplatesEvaluation']['data'])) return $data;

		$data['TemplatesEvaluation']['data'] = json_decode($data['TemplatesEvaluation']['data'],true);

		if(count($data['TemplatesEvaluation']['data']) == 0) return $data;

		$data['Evaluationmodel'] = key($data['TemplatesEvaluation']['data']);

		$data['Template']['evaluation_temp'] = array_keys($data['TemplatesEvaluation']['data'][$data['Evaluationmodel']][0]);

		return $data;
	}

	public function EditTemplateDescription($data){

		if(!isset($this->_controller->request->data['Template'])) return $data;
		if(!isset($this->_controller->request->data['Template']['save_desctiption'])) return $data;

		$id = $this->_controller->request->data['Template']['id'];

		$update = $this->_controller->request->data;

		$Check = $this->_controller->Template->save($update);

		if($Check !== false){

			$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));

			$data['Template']['name'] = $Template['Template']['name'];
			$data['Template']['description'] = $Template['Template']['description'];

			$this->_controller->Flash->success(__('The template has been saved.'), array('key' => 'success'));
		} else {
			$this->_controller->Flash->error(__('An error has occurred.'), array('key' => 'error'));
		}

		return $data;
	}

	public function EditTemplate($data){

		if(!isset($this->_controller->request->data['edit_template_fields'])) return $data;
		if(!isset($data['Template']['models'])) return $data;

		foreach ($data['Template']['models'] as $key => $value) {

			if(!isset($this->_controller->request->data[$value])) continue;
			if(!isset($data['Template']['data'][$value])) continue;

			foreach ($data['Template']['data'][$value] as $_key => $_value) {

				if(!isset($this->_controller->request->data[$value][$_key])){
					unset($data['Template']['data'][$value][$_key]);
				}
			}
		}

		$data['Template']['data'] = json_encode($data['Template']['data']);
		$data['Template']['evaluation_temp'] = '';

		$id = $data['Template']['id'];

		$update = $data;

		unset($update['Template']['xml']);
		unset($update['Template']['settings']);
		unset($update['Testingcomp']);

		$this->_controller->Template->save($update);

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));

//		$data['Template'] = $Template['Template'];

		$data['Template']['data'] = json_decode($Template['Template']['data'],true);

		$data['Template']['evaluation_temp'] = array();
//		$data['Template']['models'] = $models;

		return $data;
	}

	public function SaveTemplate($data){

		if(!isset($this->_controller->request->data['Template'])) return false;

		$Type = 0;

		if($this->_controller->request->data['Template']['Generally'] == 1 && $this->_controller->request->data['Template']['Specific'] == 1){

			$Type = 0;

		} else {

			if($this->_controller->request->data['Template']['Generally'] == 1 && $this->_controller->request->data['Template']['Specific'] == 0) $Type = 1;
			if($this->_controller->request->data['Template']['Generally'] == 0 && $this->_controller->request->data['Template']['Specific'] == 1) $Type = 2;

		}

		$verfahren = $data['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Testingmethod']['value']);

		$Insert = array();
		$this->_controller->request->data['Template']['testingmethod_id'] = $data['Testingmethod']['id'];

		$data_save = $data;

		$UnsetArray = array('Topproject','Order','Report','Testingcomp','User','EquipmentType','Equipment');

		foreach ($UnsetArray as $key => $value) {
			unset($data_save[$value]);
		}

		$UnsetArray = array('id','reportnumber_id','created','modified');

		foreach ($data['TemplateModels'] as $key => $value) {
			foreach($UnsetArray as $_key => $_value){
				if(isset($data_save[$value][$_value])) unset($data_save[$value][$_value]);
			}
		}

		$Model[1] = 'Report' . $Verfahren . 'Generally';
		$Model[2] = 'Report' . $Verfahren . 'Specific';
		$Model[3] = 'Report' . $Verfahren . 'Evaluation';

		$this->_controller->request->data['Template']['type'] = $Type;
		$this->_controller->request->data['Template']['evaluation_temp'] = '';

		if(isset($data_save[$Model[3]])){
			if(count($data_save[$Model[3]]) > 0){

				unset($data_save[$Model[3]][0][$Model[3]]['id']);
				unset($data_save[$Model[3]][0][$Model[3]]['reportnumber_id']);
				unset($data_save[$Model[3]][0][$Model[3]]['sorting']);
				unset($data_save[$Model[3]][0][$Model[3]]['deleted']);
				unset($data_save[$Model[3]][0][$Model[3]]['result']);
				unset($data_save[$Model[3]][0][$Model[3]]['rep_area']);
				unset($data_save[$Model[3]][0][$Model[3]]['remark']);
				unset($data_save[$Model[3]][0][$Model[3]]['error']);

				$this->_controller->request->data['Template']['evaluation_temp'] = json_encode(array_keys($data_save[$Model[3]][0][$Model[3]]));
			}
		}

		unset($data_save[$Model[3]]);

		if($Type == 1){
			unset($data_save[$Model[2]]);
		}

		if($Type == 2){
			unset($data_save[$Model[1]]);
		}

		$this->_controller->request->data['Template']['data'] = json_encode($data_save);
		$this->_controller->request->data['Testingcomp']['Testingcomp'][] = $this->_controller->Auth->user('testingcomp_id');

		$this->_controller->Template->create();

		if($this->_controller->Template->save($this->_controller->request->data)){

			$this->_controller->request->data = $data;

			$Id = $this->_controller->Template->getLastInsertID();

			$this->_controller->Flash->success(__('The template has been saved. Do you want to edit the template now?'), array('key' => 'success'));
			$this->_controller->set('Id', $Id);
			$this->_controller->set('SubmitDescription', null);
			$this->_controller->set('reportnumber', $data);

			return true;

		} else {

			return false;

			$SubmitDescription = __('Submit',true);

		}

		return true;
	}

	public function CreateFormData($data){

		if(isset($this->_controller->request->data['edit_template'])) return $data;
		if(isset($this->_controller->request->data['delete_evaluation_template'])) return $data;

		$output = array();

		$locale = $this->_controller->Lang->Discription();

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);

		$this->_controller->request->Verfahren = $Verfahren;
		$this->_controller->request->verfahren = $verfahren;

		$xml = $this->_controller->Xml->LoadXmlFileForReport($data['Template']['data']);

		$data['Template']['xml'] = $xml;

		$DataKeys = array('Report' . $Verfahren . 'Generally','Report' . $Verfahren . 'Specific');
		$EvaluationKey = 'Report' . $Verfahren . 'Evaluation';

		$this->_controller->set('DataKeys',$DataKeys);

		foreach ($DataKeys as $key => $value) {

			$this->_controller->loadModel($value);
			$Schema = $this->_controller->{$value}->schema();

			foreach($xml['settings']->$value->children() as $_key => $_value){

				if(!isset($data['Template']['data'][$value][$_key])) continue;

				$Val = $data['Template']['data'][$value][$_key];
				$output[$value][$_key] = $Val;

			}
		}

		foreach ($data['Template']['data']['Reportnumber'] as $key => $value) {
			$output['Reportnumber'][$key] = $value;
		}

		foreach ($data['Template']['data']['Testingmethod'] as $key => $value) {
			$output['Testingmethod'][$key] = $value;
		}

		$data['Template']['models'] = $DataKeys;

		if(isset($data['Template']['data']['Attention'])) $output['Attention'] = $data['Template']['data']['Attention'];
		else $output['Attention'] = array();

		$data['Template']['data'] = $output;

		$data = $this->AddEvaluationsTemplate($data);

		$data = $this->CreateFormSettings($data);

		if(isset($Evaluations)){
			$data['Template']['models'][] = $EvaluationKey;
		}

		return $data;
	}

	public function FindDataKeysForFastview($data){

		$output = array();

		$locale = $this->_controller->Lang->Discription();

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);

		$xml = $this->_controller->Xml->LoadXmlFileForReport($data['Template']['data']);

		$data['Template']['xml'] = $xml;

		$DataKeys = array('Report' . $Verfahren . 'Generally','Report' . $Verfahren . 'Specific');

		foreach ($DataKeys as $key => $value) {

			$this->_controller->loadModel($value);
			$Schema = $this->_controller->{$value}->schema();

			foreach($xml['settings']->$value->children() as $_key => $_value){

				if(!isset($data['Template']['data'][$value][$_key])) continue;

				$Val = $data['Template']['data'][$value][$_key];
				$output[$value][$_key] = $Val;

			}
		}

		$data['Template']['models'] = $DataKeys;

		return $data;
	}

	public function AddEvaluationsTemplate($data){

		if(!isset($data['Template']['evaluation_temp'])) return $data;
		if(is_array($data['Template']['evaluation_temp'])) return $data;

		$data['Template']['evaluation_temp'] = json_decode($data['Template']['evaluation_temp'],true);

		return $data;
	}

	public function CreateEvaluations(){

		if(!isset($this->_controller->request->data['TemplatesEvaluation'])) return false;
		if(!isset($this->_controller->request->data['TemplatesEvaluation']['testingmethod_id'])) return false;
		if(empty($this->_controller->request->data['TemplatesEvaluation']['testingmethod_id'])) return false;

		$id = $this->_controller->request->data['TemplatesEvaluation']['testingmethod_id'];
		$this->_controller->Testingmethod->recursive = -1;

		$Testingmethod = $this->_controller->Testingmethod->find('first',array(
			'conditions' => array(
				'Testingmethod.id' =>$id
				)
			)
		);

		if(count($Testingmethod) == 0) return false;

		$verfahren = $Testingmethod['Testingmethod']['value'];
		$Verfahren = ucfirst($verfahren);
		$Model = 'Report' . $Verfahren . 'Evaluation';

		$Data['Reportnumber']['version'] = $Testingmethod['Testingmethod']['version'];
		$Data['Testingmethod'] = $Testingmethod['Testingmethod'];

		$xml = $this->_controller->Xml->LoadXmlFileForReport($Data);

		$BlackList = array('error','result','rep_area','remark');

		$Save = $this->_controller->request->data['TemplatesEvaluation'];

		$Output = array();

		foreach ($xml['settings']->$Model->children() as $key => $value) {

			if(empty($value->output->screen)) continue;

			$key = trim($value->key);

			$Check = array_search($key,$BlackList);

			if($Check !== false) continue;

			$Output[$Model][0][$key] = array('model' =>$Model, 'field' => $key , 'value' => '-');


		}

		$Save['testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');
		$Save['data'] = json_encode($Output);

		 $this->_controller->TemplatesEvaluation->create();
		 $Test = $this->_controller->TemplatesEvaluation->save($Save);

		 if($Test === false){

			 return false;

		 } else {

			 $TemplatesEvaluationId = $this->_controller->TemplatesEvaluation->getLastInsertID();

			 $FormName['controller'] = 'templates';
			 $FormName['action'] = 'evaluation';
			 $FormName['terms'] = implode('/',array(2,$TemplatesEvaluationId));

			 $this->_controller->set('FormName',$FormName);

			 return true;

		 }
	}

	public function AddEvaluations($evalutions ,$Evaluations,$Model,$data){

		$Welds = array();
		$Fieldsorting = array();

		$HasPosition = false;

		foreach($data['Template']['xml']['settings']->$Model->children() as $key => $value){

			if(trim($value->output->screen) != 4) continue;

			if(trim($value->key) == 'position')	$HasPosition = true;

			$Fieldsorting[] = trim($value->key);
		}

		foreach ($Evaluations as $key => $value) {
			foreach ($value->$Model as $_key => $_value) {

				$evalutions[$Model][$key][$_key] = $_value;

				if($_key == 'description'){
					$Welds[$_value] = $_value;
				}
			}
		}

		$Output = array();

		foreach ($evalutions[$Model] as $key => $value) {

			if(isset($value['id'])) unset($value['id']);
			if(isset($value['reportnumber_id'])) unset($value['reportnumber_id']);
			if(isset($value['deleted'])) unset($value['deleted']);
			if(isset($value['sorting'])) unset($value['sorting']);
			if(isset($value['error'])) unset($value['error']);
			if(isset($value['result'])) unset($value['result']);
			if(isset($value['rep_area'])) unset($value['rep_area']);
			if(isset($value['remark'])) unset($value['remark']);

			foreach ($Fieldsorting as $_key => $_value) {

				if(!isset($value[$_value])) continue;

				if($HasPosition == true) $Output[$value['description']][$value['position']][$_value] = $value[$_value];
				if($HasPosition == false) $Output[$value['description']][$_value] = $value[$_value];
			}

		}

		unset($evalutions[$Model]);

		$evalutions[$Model] = $Output;

		return $evalutions;
	}

	public function CreateFormSettings($data){

		$Output = array();

		foreach ($data['Template']['models']  as $key => $value) {
			foreach($data['Template']['xml']['settings']->{$value}->children() as $_key => $_value){
				 $Output[$value][] = $_value;
			}
		}

		$data['Template']['settings'] = $Output;


		return $data;
	}

	public function ChangeDropdownValues($data){

		if(!isset($data['Dropdowns'])) return $data;

		foreach ($data['Dropdowns'] as $key => $value) {
			foreach ($value as $_key => $_value) {
				foreach ($_value as $__key => $__value) {

					if(is_object($__value)) continue;

					unset($data['Dropdowns'][$key][$_key][$__key]);
					$data['Dropdowns'][$key][$_key][$__value] = $__value;
				}
			}
		}

		return $data;
	}

	public function SaveDropdownValues($data){

		if(!isset($data['Template']['models'])){
			$this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
			return $data;
		}

		if(count($data['Template']['models']) == 0){
			$this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
			return $data;
		}

	//	pr($data['Template']['data']);

		foreach ($data['Template']['models'] as $key => $value) {

				if(!isset($this->_controller->request->data[$value])) return $data;

				foreach($this->_controller->request->data[$value] as $_key => $_value){

					if(is_array($_value)) $_value = implode("
",$_value);

					$data['Template']['data'][$value][$_key] = $_value;

				}
		}

		$SaveData = $data['Template'];
		$SaveData['data'] = json_encode($SaveData['data']);
		$SaveData['evaluation_temp'] = json_encode($SaveData['evaluation_temp']);

		unset($SaveData['xml']);
		unset($SaveData['settings']);
		unset($SaveData['models']);

		$test = $this->_controller->Template->save($SaveData);

		if($test != false) $this->_controller->Flash->success(__('The data has been saved'), array('key' => 'success'));
		else $this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));

		return $data;

	}

	public function FormatData($data,$Mod){

		if(!isset($data['Template']['data'])) return $data;

		$locale = $this->_controller->Lang->Discription();

		$Json = json_decode($data['Template']['data'],true);
		$Output = array();

		$verfahren = $Json['Testingmethod']['value'];
		$Verfahren = ucfirst($Json['Testingmethod']['value']);

		$DataKeys = array('Report' . $Verfahren . 'Generally','Report' . $Verfahren . 'Specific');

		foreach ($DataKeys as $key => $value) {

			if(!isset($Json[$value])) continue;

			foreach($Json[$value] as $_key => $_value){

				$Array = array(
					'id' => $value . Inflector::camelize($_key),
					'name' => 'data['.$value.']['.$_key.']',
					'value' => $_value,
				);

				$Output[$value][$_key] = $Array;
			}
		}

		if($Mod == 'encode')$data['Template']['data'] = json_encode($Output);
		if($Mod == 'decode')$data['Template']['data'] = $Output;

		$data['Attention'] = array();

		if(isset($Json['Attention'])) $data['Attention'] = $Json['Attention'];

		return $data;
	}

	public function FormatDataEvaluation($data,$Mod){

		if(!isset($data['TemplatesEvaluation']['data'])) return $data;

		$locale = $this->_controller->Lang->Discription();

		$Json = json_decode($data['TemplatesEvaluation']['data'],true);
		$Output = array();

		$verfahren = explode('_',Inflector::underscore(key($Json)));
		$verfahren = $verfahren[1];
		$Verfahren = ucfirst($verfahren);

		$DataKeys = array('Report' . $Verfahren . 'Evaluation');

		foreach ($DataKeys as $key => $value) {

			if(!isset($Json[$value])) continue;

			foreach($Json[$value] as $_key => $_value){

				$Array = array(
					'id' => $value . Inflector::camelize($_key),
					'name' => 'data['.$value.']['.$_key.']',
					'value' => $_value,
				);

				$Output[$value][$_key] = $Array;
			}
		}

		if($Mod == 'encode')$data['Template']['data'] = json_encode($Output);
		if($Mod == 'decode')$data['Template']['data'] = $Output;

		$data['Attention'] = array();

		if(isset($Json['Attention'])) $data['Attention'] = $Json['Attention'];

		return $data;
	}

	public function TemplatesDropdown($data){

		if(Configure::read('TemplateManagement') == false) return $data;

		$this->_controller->loadModel('Template');

		$TemplatesTestingcompIds = $this->_controller->Template->TemplatesTestingcomp->find('list',array(
			'conditions' => array(
					'TemplatesTestingcomp.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id')
				)
			)
		);


		if(count($TemplatesTestingcompIds )== 0) return $data;

		$Templates = $this->_controller->Template->find('all',array(
			'fields' => array('id','name','description'),
			'conditions' => array(
				'Template.testingmethod_id' => $data['Testingmethod']['id'],
				'Template.id' => $TemplatesTestingcompIds
				)
			)
		);

		foreach ($Templates as $key => $value) {
			$Templates[$key] = $this->CollectTestingcoms($Templates[$key]);
		}

		$Templates = $this->CheckTestingcoms($Templates);

		if(count($Templates) == 0) return $data;

		$data['Templates'] = $Templates;

		return $data;
	}

	public function RemoveEvaluationResults($data){

		$verfahren = $data['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Testingmethod']['value']);
		$Model = 'Report' . $Verfahren . 'Evaluation';

		if(!isset($data[$Model])) return $data;
		if(count($data[$Model]) == 0) return $data;

		foreach ($data[$Model] as $key => $value) {

			if($value[$Model]['deleted'] > 0) unset($data[$Model][$key]);

			$data[$Model][$key][$Model]['error'] = '';
			$data[$Model][$key][$Model]['remark'] = '';
			$data[$Model][$key][$Model]['rep_area'] = '';
			$data[$Model][$key][$Model]['result'] = 0;
			$data[$Model][$key][$Model]['id'] = 0;
			$data[$Model][$key][$Model]['reportnumber_id'] = 0;

			unset($data[$Model][$key][$Model]['created']);
			unset($data[$Model][$key][$Model]['modified']);
		}

		return $data;
	}

	public function FindUpdateMod($data){

		if(!isset($this->_controller->request->data['data_class'])) return $data;

		$Classes = explode(' ',$this->_controller->request->data['data_class']);

		$Test = array_search('edit_complete_weld', $Classes);

		if($Test != false) $data['DataEditWeld'] = 1;

		$Test = array_search('edit_complete_position', $Classes);

		if($Test != false) $data['DataEditPos'] = 1;

		$Test = array_search('edit_complete_template', $Classes);

		if($Test != false) $data['DataEditTemp'] = 1;

		$Test = array_search('dupli_pos', $Classes);

		if($Test != false) $data['DupliPos'] = 1;

		$Test = array_search('delete_pos', $Classes);

		if($Test != false) $data['DeletePos'] = 1;

		return $data;
	}

	public function DeleteEvaluationData($data){

		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));
		$Template['Template']['data'] = json_decode($Template['Template']['data'],true);

		if(isset($data['DeletePos'])) $Template = $this->_DeleteEvaluationDataPos($Template);

		$Check = $this->_SaveUpdateEvaluationData($Template);

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));
		$Template['Template']['data'] = json_decode($Template['Template']['data'],true);

		if($Check == true) $Template['Message']['success'] = __('The value has been saved',true);
		else $Template['Message']['error'] = __('An error has occurred',true);

		return $data;

	}

	protected function _DeleteEvaluationDataPos($data){

		if(!isset($this->_controller->request->data['data_weld'])) return $data;
		if(!isset($this->_controller->request->data['data_position'])) return $data;

		$weld = $this->_controller->request->data['data_weld'];
		$pos = $this->_controller->request->data['data_position'];

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);
		$Model = 'Report' . $Verfahren . 'Evaluation';
		$Count = count($data['Template']['data'][$Model]);
		++$Count;

		$Output = array();

		foreach($data['Template']['data'][$Model] as $key => $value){

			if($value[$Model]['description'] == $weld && $value[$Model]['position'] == $pos) continue;

			$Output[] = $value;

		}

		$data['Template']['data'][$Model] = $Output;

		return $data;
	}

	public function DupliEvaluationData($data){

		if(isset($this->_controller->request->data['edit_template'])) return $data;
		if(isset($this->_controller->request->data['delete_evaluation_template'])) return $data;

		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));
		$Template['Template']['data'] = json_decode($Template['Template']['data'],true);

		if(isset($data['DupliPos'])) $Template = $this->_DupliEvaluationDataPos($Template);

		$Check = $this->_SaveUpdateEvaluationData($Template);

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));
		$Template['Template']['data'] = json_decode($Template['Template']['data'],true);

		if($Check == true) $Template['Message']['success'] = __('The value has been saved',true);
		else $Template['Message']['error'] = __('An error has occurred',true);

		return $Template;

	}

	protected function _DupliEvaluationDataPos($data){

		if(!isset($this->_controller->request->data['data_weld'])) return $data;
		if(!isset($this->_controller->request->data['data_position'])) return $data;

		$weld = $this->_controller->request->data['data_weld'];
		$pos = $this->_controller->request->data['data_position'];

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);
		$Model = 'Report' . $Verfahren . 'Evaluation';
		$Count = count($data['Template']['data'][$Model]);
		++$Count;

		foreach($data['Template']['data'][$Model] as $key => $value){

			if($value[$Model]['description'] != $weld) continue;
			if($value[$Model]['position'] != $pos) continue;

			$value[$Model]['position'] .= ' ' . __('Copy',true);

			$data['Template']['data'][$Model][$Count] = $value;

			break;

		}

		return $data;
	}

	public function UpdateEvaluationData($data){

		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));
		$Template['Template']['data'] = json_decode($Template['Template']['data'],true);

		if(isset($data['DataEditPos'])) $Template = $this->_UpdateEvaluationDataPos($Template);
		if(isset($data['DataEditWeld'])) $Template = $this->_UpdateEvaluationDataWeld($Template);
		if(isset($data['DataEditTemp'])) $Template = $this->_UpdateEvaluationDataTemp($Template);

		if(!isset($Template['Update'])) return $data;

		$Check = $this->_SaveUpdateEvaluationData($Template);

		$Template = $this->_controller->Template->find('first',array('conditions' => array('Template.id' => $id)));
		$Template['Template']['data'] = json_decode($Template['Template']['data'],true);

		if($Check == true) $Template['Message']['success'] = __('The value has been saved',true);
		else $Template['Message']['error'] = __('An error has occurred',true);

		return $Template;
	}

	protected function _SaveUpdateEvaluationData($data){

		unset($data['Template']['xml']);
		unset($data['Template']['settings']);
		unset($data['Template']['models']);

		$data['Template']['data'] = json_encode($data['Template']['data']);

		$test = $this->_controller->Template->save($data['Template']);

		if($test == false) return false;
		else return true;

	}

	protected function _UpdateEvaluationDataTemp($data){

		if(!isset($this->_controller->request->data['data_weld'])) return $data;
		if($this->_controller->request->data['data_weld'] != 0) return $data;
		if(!isset($this->_controller->request->data['data_field'])) return $data;
		if(isset($this->_controller->request->data['data_position'])) return $data;

		$field = $this->_controller->request->data['data_field'];
		$val = $this->_controller->request->data['value'];

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);
		$Model = 'Report' . $Verfahren . 'Evaluation';

		foreach($data['Template']['data'][$Model] as $key => $value){

			if(!isset($value[$Model][$field])) continue;

			$data['Template']['data'][$Model][$key][$Model][$field] = $val;

		}

		$data['Update'] = 1;

		return $data;

	}

	protected function _UpdateEvaluationDataWeld($data){

		if(!isset($this->_controller->request->data['data_weld'])) return $data;
		if(!isset($this->_controller->request->data['data_field'])) return $data;
		if(isset($this->_controller->request->data['data_position'])) return $data;

		$weld = $this->_controller->request->data['data_weld'];
		$field = $this->_controller->request->data['data_field'];
		$val = $this->_controller->request->data['value'];

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);
		$Model = 'Report' . $Verfahren . 'Evaluation';

		foreach($data['Template']['data'][$Model] as $key => $value){

			if($value[$Model]['description'] != $weld) continue;
			if(!isset($value[$Model][$field])) continue;

			$data['Template']['data'][$Model][$key][$Model][$field] = $val;

		}

		$data['Update'] = 1;

		return $data;
	}

	protected function _UpdateEvaluationDataPos($data){

		if(!isset($this->_controller->request->data['data_weld'])) return $data;
		if(!isset($this->_controller->request->data['data_position'])) return $data;
		if(!isset($this->_controller->request->data['data_field'])) return $data;

		$weld = $this->_controller->request->data['data_weld'];
		$pos = $this->_controller->request->data['data_position'];
		$field = $this->_controller->request->data['data_field'];
		$val = $this->_controller->request->data['value'];

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);
		$Model = 'Report' . $Verfahren . 'Evaluation';

		foreach($data['Template']['data'][$Model] as $key => $value){

			if($value[$Model]['description'] != $weld) continue;
			if($value[$Model]['position'] != $pos) continue;
			if(!isset($value[$Model][$field])) continue;

			$data['Template']['data'][$Model][$key][$Model][$field] = $val;

		}

		$data['Update'] = 1;

		return $data;
	}

	public function SaveEvaluationTemplate($data){

		if($this->_controller->request->projectvars['VarsArray'][1] > 0) return $data;

		if(!isset($this->_controller->request->data['save_template'])) return $data;
		if(!isset($this->_controller->request->data['TemplatesEvaluations'])) return $data;
		if(empty($this->_controller->request->data['TemplatesEvaluations'])) return $data;

		$verfahren = $data['Template']['data']['Testingmethod']['value'];
		$Verfahren = ucfirst($data['Template']['data']['Testingmethod']['value']);

		$EvaluationKey = 'Report' . $Verfahren . 'Evaluation';

		$Output = array();

		$count = 0;
		$empty = true;

		foreach ($this->_controller->request->data['TemplatesEvaluations'] as $key => $value) {
			foreach($value as $_key => $_value){

				if(!empty($_value)) $empty = false;
				if(!is_array($_value)) $empty = false;

				foreach ($_value as $__key => $__value) {

					$Output[$EvaluationKey][$count][$__key]['model'] = $EvaluationKey;
					$Output[$EvaluationKey][$count][$__key]['field'] = $__key;
					$Output[$EvaluationKey][$count][$__key]['value'] = $__value;

				}
				$count++;
			}
		}

		if($empty === true) return $data;

		$Insert = array(
			'name' => $this->_controller->request->data['template_name'],
			'description' => $this->_controller->request->data['template_descripton'],
			'templates_id' => $data['Template']['id'],
			'testingmethod_id' => $data['Template']['data']['Testingmethod']['id'],
			'data' => json_encode($Output),
		);

		$this->_controller->TemplatesEvaluation->create();
		$this->_controller->TemplatesEvaluation->save($Insert);
		$TemplatesEvaluationId = $this->_controller->TemplatesEvaluation->getLastInsertID();

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $TemplatesEvaluationId)));

		$TemplatesEvaluation['TemplatesEvaluation']['data'] = json_decode($TemplatesEvaluation['TemplatesEvaluation']['data'],true);

		$data['TemplatesEvaluation'] = $TemplatesEvaluation['TemplatesEvaluation'];

		$data['Evaluationmodel'] = key($data['TemplatesEvaluation']['data']);

		return $data;
	}

	public function UpdateEvaluationTemplateDesctiption($data){

		if($this->_controller->request->projectvars['VarsArray'][1] == 0) return $data;
		if(!isset($this->_controller->request->data['edit_template'])) return $data;

//		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$val = trim($this->_controller->request->data['value']);
		$id = intval($this->_controller->request->data['id']);
		$field = $this->_controller->request->data['field'];

		$Insert = array(
			'id' => $id,
			$field => $val,
		);

		$Test = $this->_controller->TemplatesEvaluation->save($Insert);

		if($Test == false){
			$data['Message']['error'] = __('An error has occurred',true);
		} else {
			$data['Message']['success'] = __('The value has been saved',true);
		}

		return $data;

	}

	public function DeleteEvaluationTemplateDesctiption($data){

		if($this->_controller->request->projectvars['VarsArray'][1] == 0) return $data;
		if(!isset($this->_controller->request->data['delete_evaluation_template'])) return $data;

		$id = $this->_controller->request->projectvars['VarsArray'][1];

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));

		if(count($TemplatesEvaluation ) == 0) return $data;

		$data['TemplatesEvaluation'] = $TemplatesEvaluation['TemplatesEvaluation'];

		if($this->_controller->TemplatesEvaluation->delete($id)){
			$data['Message']['success'] = __('The value has been deleted',true);
		} else {
			$data['Message']['error'] = __('An error has occurred',true);
		}

		return json_encode($data);

	}

	public function UpdateEvaluationTemplate($data){

		if($this->_controller->request->projectvars['VarsArray'][1] == 0) return $data;
		if(!isset($this->_controller->request->data['edit_template'])) return $data;
		if(!isset($data['TemplatesEvaluation']['data'])) return $data;
		if(empty($data['TemplatesEvaluation']['data'])) return $data;


		$id = $this->_controller->request->projectvars['VarsArray'][1];
		$EvaluationKey = key($data['TemplatesEvaluation']['data']);

		$verfahren = explode('_',Inflector::underscore($EvaluationKey));
		$verfahren = $verfahren[1];
		$Verfahren = ucfirst($verfahren);

		$this->_controller->Testingmethod->recursive = -1;
		$Testingmethod = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.value' => $verfahren)));

		$Output = array();

		$count = 0;
		$empty = true;

		foreach ($this->_controller->request->data['TemplatesEvaluations'] as $key => $value) {
			foreach($value as $_key => $_value){

				if(!empty($_value)) $empty = false;
				if(!is_array($_value)) $empty = false;

				foreach ($_value as $__key => $__value) {

					$Output[$EvaluationKey][$count][$__key]['model'] = $EvaluationKey;
					$Output[$EvaluationKey][$count][$__key]['field'] = $__key;
					$Output[$EvaluationKey][$count][$__key]['value'] = $__value;

				}

				$count++;
			}
		}

		if($empty === true) return $data;

		if(isset($data['TemplatesEvaluation']['data']['Attention'])) $Output['Attention'] = $data['TemplatesEvaluation']['data']['Attention'];

		$Insert = array(
			'id' => $id,
			'templates_id' => 0,
			'testingmethod_id' => $Testingmethod['Testingmethod']['id'],
			'data' => json_encode($Output),
		);

		$Test = $this->_controller->TemplatesEvaluation->save($Insert);

		if($Test == false){
			$this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
		} else {
			$this->_controller->Flash->success(__('The value has been saved'), array('key' => 'success'));
		}

		return $data;
	}

	public function ApplyTemplate($data){

		if($this->_controller->Session->check('ApplyTemplate') === false) return $data;

		$ApplyTemplate = $this->_controller->Session->read('ApplyTemplate');

		$Ids = array();

		foreach($ApplyTemplate as $key => $value){

			if($value == 0) continue;

			$pos = strpos($key,'check_');

			if($pos === false) continue;

			$array = explode('_',$key);

			if(isset($array[1])) $Ids[] = $array[1];
		}

		if(count($Ids) == 0) return $data;

		$data = $this->_ApplyTemplate($data,$Ids);

		$this->_controller->set('delete_current_evaluations', true);

		return $data;
	}

	protected function _ApplyTemplate($data,$Ids){

		if(count($Ids) == 0) return $data;

		$Evaluationmodel = $data['Evaluationmodel'];

		if(count($data['TemplatesEvaluation']['data'][$Evaluationmodel]) == 0) return $data;

		$this->_controller->loadModel($Evaluationmodel);

		// Bewertungen anhand der Nähte holen
		$Evaluations = $this->_controller->{$Evaluationmodel}->find('list',array(
			'group' => array('description'),
			'fields' => array('description'),
			'conditions' => array(
				$Evaluationmodel . '.id' => $Ids
				)
			)
		);

		if(count($Evaluations) == 0) return $data;
		
		$Template = $data['TemplatesEvaluation']['data'][$Evaluationmodel];
		$Welds = array();
		$DelIds = array();

		foreach($Evaluations as $key => $value){

			$Evaluation = $this->_controller->{$Evaluationmodel}->find('all',array(
				'conditions' => array(
					$Evaluationmodel . '.id' => $Ids,
					$Evaluationmodel . '.description' => $value
					)
				)
			);
			
			if(count($Evaluation) != count($Template)) {

				$this->_controller->Flash->warning(__('More data was selected than is in the template.'),array('key' => 'warning'));
				continue;
			}

			$Evaluation = Hash::extract($Evaluation, '{n}.'.$Evaluationmodel);

			$Welds[]['Welds'] = $Evaluation;
		}

		$data['TemplatesEvaluation']['template'] = array();

		$DelIds = Hash::extract($Welds, '{n}.Welds.{n}.id');

		$out =array();
	
		foreach($Welds as $key => $value){

			$out = $this->_mountEvaluationTemplate($data,$Template,$value['Welds'],$out);

		}

		unset($data['TemplatesEvaluation']['data'][$Evaluationmodel]);
		$data['TemplatesEvaluation']['data'][$Evaluationmodel]= $out;

		$this->_controller->Session->write('ApplyTemplateDelIds',$DelIds);

		return $data;
	}

	protected function _mountEvaluationTemplate($data,$template,$value,$out){


		if(count($template) != count($value)) return $data;

		$Evaluationmodel = $data['Evaluationmodel'];

		// Immer die Werte aus der Auswertung einsetzen
		// Sollte später mal aus der Datenbank kommen
		$ReplaceEveryTime = array('description','welder_no','weld_system_uid','sheet_no');

		// Nie die Werte aus der Auswertung einsetzen
		$ReplaceNever = array('position','dimension','film_dimension','image_no','film_focus_distance','BGZ','thickness');

		$ReplaceEveryTime = array_flip($ReplaceEveryTime);
		$ReplaceNever = array_flip($ReplaceNever);

		foreach($value as $key => $value){

			$temp = $template[$key];
			$output = array();

			foreach($value as $_key => $_value){

				if(!isset($temp[$_key])) continue;

				$output[$_key]['model'] = $temp[$_key]['model'];
				$output[$_key]['field'] = $temp[$_key]['field'];
				$output[$_key]['value'] = '';

				if(isset($ReplaceNever[$_key])){
					$output[$_key]['value'] = $temp[$_key]['value'];	
					continue;
				}

				// Wenn der Templatewert immer getauscht werden soll
				// nur wenn schon ein Wert in der Auswertung vorhanden ist
				// Wird der Wert aus der Auswertung eingesetzt
				if(isset($ReplaceEveryTime[$_key]) && !empty($_value)){

					$output[$_key]['value'] = $_value;
//					$DelIds[$Evaluations[$key][$Evaluationmodel]['id']] = $Evaluations[$key][$Evaluationmodel]['id'];
//					unset($Ids[$DelId]);
					continue;

				}

				// Wenn der Templatewert leer ist wird immer der Wert 
				// aus der vorhanden Auswertung gesetzt
				if(empty($_value)){

					$output[$_key]['value'] = $temp[$_key]['value'];
//					$DelIds[$Evaluations[$key][$Evaluationmodel]['id']] = $Evaluations[$key][$Evaluationmodel]['id'];
//					unset($Ids[$DelId]);
					continue;

				}
			}

			$outputSor = array();

			foreach($data['Template']['evaluation_temp'] as $_key => $_value){
				$outputSor[$_value] = $output[$_value];
			}

			$out[] = $outputSor;
		}

		return $out;
	}

	public function CollectEvaluationTemplate($data){

		if(isset($this->_controller->request->data['Reportnumber']['MassSelect'])) return $data;

		$id = 0;

		if($this->_controller->request->projectvars['VarsArray'][1] > 0) $id = $this->_controller->request->projectvars['VarsArray'][1];
		if(isset($this->_controller->request->data['TemplatesEvaluation']['id'])) $id = $this->_controller->request->data['TemplatesEvaluation']['id'];

		if(empty($id)) return $data;
		if($id == 0) return $data;

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));

		if(count($TemplatesEvaluation ) == 0) return $data;

		$this->_controller->Testingmethod->recursive = -1;

		$Testingmethod = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $TemplatesEvaluation['TemplatesEvaluation']['testingmethod_id'])));

		$verfahren = $Testingmethod['Testingmethod']['value'];
		$Verfahren = ucfirst($Testingmethod['Testingmethod']['value']);

		$EvaluationKey = 'Report' . $Verfahren . 'Evaluation';

		$data['Evaluationmodel'] = $EvaluationKey;

		$TemplatesEvaluation['TemplatesEvaluation']['data'] = json_decode($TemplatesEvaluation['TemplatesEvaluation']['data'],true);

		$data['TemplatesEvaluation'] = $TemplatesEvaluation['TemplatesEvaluation'];

		$Model = $data['Evaluationmodel'];

		if(!isset($data['TemplatesEvaluation']['data'][$Model][0]));

		$data['Template']['evaluation_temp'] = array_keys($data['TemplatesEvaluation']['data'][$Model][0]);

		return $data;

	}

	public function SaveEvaluationTemplateDescription($data){

		if(!isset($this->_controller->request->data['TemplatesEvaluation'])) return $data;
		if(empty($this->_controller->request->data['TemplatesEvaluation'])) return $data;

		$Test = $this->_controller->TemplatesEvaluation->save($this->_controller->request->data['TemplatesEvaluation']);

		if($Test == false){
			$this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
		} else {

			$VarsArray = $this->_controller->request->projectvars['VarsArray'];
			$VarsArray[1] = 0;

			$FormName['controller'] = 'templates';
			$FormName['action'] = 'edit';
			$FormName['terms'] = implode('/',$VarsArray);

			$this->_controller->set('FormName',$FormName);

			$this->_controller->Flash->success(__('The value has been saved'), array('key' => 'success'));
		}

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $this->_controller->request->data['TemplatesEvaluation']['id'])));

		$TemplatesEvaluation['TemplatesEvaluation']['data'] = json_decode($TemplatesEvaluation['TemplatesEvaluation']['data'],true);

		$data['TemplatesEvaluation'] = $TemplatesEvaluation['TemplatesEvaluation'];
		$data['Evaluationmodel'] = key($TemplatesEvaluation['TemplatesEvaluation']['data']);

		return $data;
	}

	public function CollectEvaluationTemplates($data){

		$testingmethod_id = $data['Reportnumber']['testingmethod_id'];

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('all',array('conditions' => array('TemplatesEvaluation.testingmethod_id' => $data['Reportnumber']['testingmethod_id'])));

		$data['TemplatesEvaluation'] = $TemplatesEvaluation;

		return $data;
	}

	public function CreateEvaluationData($data){

		unset($data['Template']['data']);
		unset($data['Template']['xml']);
		unset($data['Template']['settings']);

		if(!isset($this->_controller->request->data['TemplateEvaluation'])) return $data;
		if(empty($this->_controller->request->data['TemplateEvaluation'])) return $data;

		foreach ($this->_controller->request->data['TemplateEvaluation']['data'] as $key => $value) {

			$data['TemplateEvaluation']['data'][] = array('field' => $key,'value' => $value);

		}


		$Output = array();

		return $data;
	}

	protected function _DeleteReportEvaluationData(){

		if(!isset($this->_controller->request->data['TemplatesEvaluation'])) return;
		if(!isset($this->_controller->request->data['TemplatesEvaluation']['remove_test_areas'])) return;
		if($this->_controller->request->data['TemplatesEvaluation']['remove_test_areas'] == 0) return;

		$id = $this->_controller->request->data['TemplatesEvaluation']['id'];

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));

		if(count($TemplatesEvaluation) == 0) return false;

		$TemplatesEvaluation['TemplatesEvaluation']['data'] = json_decode($TemplatesEvaluation['TemplatesEvaluation']['data'],true);

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$reportnumberid = $this->_controller->request->projectvars['VarsArray'][4];

		$this->_controller->loadModel('Reportnumber');
		$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumberid)));

		if(count($Reportnumber) == 0) return;

		$ReportEvaluation = key($TemplatesEvaluation['TemplatesEvaluation']['data']);
		$this->_controller->loadModel($ReportEvaluation);

		if($this->_controller->Session->check('ApplyTemplateDelIds') === true && count($this->_controller->Session->read('ApplyTemplateDelIds')) > 0){

			$this->_controller->{$ReportEvaluation}->deleteAll(array($ReportEvaluation . '.id' => $this->_controller->Session->read('ApplyTemplateDelIds')), false);

		} else {

			$this->_controller->{$ReportEvaluation}->deleteAll(array($ReportEvaluation . '.reportnumber_id' => $reportnumberid), false);

		}
	}

	public function ApplyEvaluationTemplate(){

		if(!isset($this->_controller->request->data['TemplatesEvaluation'])) return false;

		$id = $this->_controller->request->data['TemplatesEvaluation']['id'];

		$TemplatesEvaluation = $this->_controller->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));

		if(count($TemplatesEvaluation) == 0) return false;

		$TemplatesEvaluation['TemplatesEvaluation']['data'] = json_decode($TemplatesEvaluation['TemplatesEvaluation']['data'],true);

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$reportnumberid = $this->_controller->request->projectvars['VarsArray'][4];

		$this->_controller->loadModel('Reportnumber');
		$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumberid)));

		if(count($Reportnumber) == 0) return false;

		$xml = $this->_controller->Xml->LoadXmlFileForReport($Reportnumber);

		$ReportEvaluation = key($TemplatesEvaluation['TemplatesEvaluation']['data']);
		$this->_controller->loadModel($ReportEvaluation);

		$Template = $this->CollectEvaluationTemplate(array());
		$Template = $this->ApplyTemplate($Template);

		$Insert = $this->_CreateInsertDataMany($Template,$Reportnumber);

		$this->_DeleteReportEvaluationData();
		$Insert = $this->_CheckWeldDescription($Insert,$Reportnumber,$ReportEvaluation);

//		$Insert = $this->_CreateInsertDataSingle($TemplatesEvaluation,$xml,$Reportnumber);
		$Insert = $this->_SaveInsertData($Insert,$Template);

		if($Insert != false){

			$this->_controller->Session->delete('ApplyTemplateDelIds');
			$this->_controller->Session->delete('ApplyTemplate');
	
		}

		return true;
	}

	protected function _CreateInsertDataMany($data,$reportnumber){

		if(!isset($data['Evaluationmodel'])) return false;

		$ReportEvaluation = $data['Evaluationmodel'];

		if(!isset($data['TemplatesEvaluation']['data'][$ReportEvaluation])) return false;
		if(count($data['TemplatesEvaluation']['data'][$ReportEvaluation]) == 0) return false;

		$Insert = array();

		foreach($data['TemplatesEvaluation']['data'][$ReportEvaluation] as $key => $value){

			$Insert[$key]['id'] = 0;
			$Insert[$key]['reportnumber_id'] = $reportnumber['Reportnumber']['id'];

			foreach($value as $_key => $_value){

				$Insert[$key][$_key] = $_value['value'];

			}
		}

		return $Insert;
	}

	protected function _CreateInsertDataSingle($data,$xml,$reportnumber){

		$reportnumberid = $this->_controller->request->projectvars['VarsArray'][4];

		$ReportEvaluation = key($data['TemplatesEvaluation']['data']);

		if(empty($xml['settings']->$ReportEvaluation)) return false;

		$this->_controller->loadModel($ReportEvaluation);

		if(!isset($data['TemplatesEvaluation']['data'][$ReportEvaluation])) return false;
		if(empty($data['TemplatesEvaluation']['data'][$ReportEvaluation])) return false;

		$Insert = array();

		foreach($xml['settings']->$ReportEvaluation->children() as $key => $value){

			if(empty($value->output->screen)) continue;
			if(trim($value->output->screen) != 4) continue;

			foreach ($data['TemplatesEvaluation']['data'][$ReportEvaluation] as $_key => $_value) {
				$Insert[$_key]['reportnumber_id'] = $reportnumberid;
				$Insert = $this->_CollectDataForInsert($Insert,$value,$_value,$_key);
			}

		}

		$Insert = $this->_CheckWeldDescription($Insert,$reportnumber,$ReportEvaluation);

		return $Insert;
	}

	protected function _CheckWeldDescription($data,$Reportnumber,$Model){

		$options = array(
			'group' => array('description'),
			'fields' => array('description'),
			'conditions' => array(
				$Model.'.reportnumber_id' => $Reportnumber['Reportnumber']['id']
			)
		);

		$Descriptions = $this->_controller->{$Model}->find('list',$options);

		$DescriptionsFlip = array_flip($Descriptions);

		foreach($data as $key => $value){

			$data[$key] = $this->_CheckWeldDescriptionRecursive($value,$DescriptionsFlip);

		}

		return $data;
	}

	protected function _CheckWeldDescriptionRecursive($data,$description){

		if(isset($description[$data['description']])){
			$data['description'] .= ' - ' . __('Copy');
			$data = $this->_CheckWeldDescriptionRecursive($data,$description);

		} else {
			return $data;
		}

		return $data;
	}

	protected function _SaveInsertData($data,$Template){

		if(empty($data)) return $data;

		$this->_controller->loadModel('TemplatesEvaluationReportnumber');
		$TemmplateId = $Template['TemplatesEvaluation']['id'];
		$Model = $Template['Evaluationmodel'];

		foreach ($data as $key => $value) {
			
			$this->_controller->{$Model}->create();
			$Test = $this->_controller->{$Model}->save($value);

			if($Test == false) return false;

			$LastId = $this->_controller->{$Model}->getLastInsertID();
			
			$this->_controller->TemplatesEvaluationReportnumber->deleteAll(array('TemplatesEvaluationReportnumber.evaluation_id' => $TemmplateId), false);

			$Update = array(
				'evaluation_id' => $LastId,
				'template_id' => $TemmplateId,
			);

			$this->_controller->TemplatesEvaluationReportnumber->create();
			$this->_controller->TemplatesEvaluationReportnumber->save($Update);

		}

		return $data;

	}

	protected function _CollectDataForInsert($Insert,$xml,$data,$x){

		$model = trim($xml->model);
		$field = trim($xml->key);

		if(!isset($data[$field])) return $Insert;

		$val = $this->_AddValueByType($xml,$data[$field]['value']);

		$Insert[$x][$field] = $val;

		return $Insert;

	}

	protected function _AddValueByType($xml,$value){

		if(trim($xml->fieldtype) == 'radio') $value = $this->_AddValueByTypeRadio($xml,$value);

		return $value;
	}

	protected function _AddValueByTypeRadio($xml,$value){

		if(empty($xml->radiooption)) return $value;
		if(empty($xml->radiooption->value)) return $value;

		$radiooptions = $xml->radiooption->value;
		$radiooptions = json_encode($radiooptions);
		$radiooptions = json_decode($radiooptions,true);

		foreach ($radiooptions as $key => $val) {
			if($val == $value){
				$value = $key;
				break;
			}
		}

		return $value;
	}

	public function SaveReport($data){
		$reportnumber_id = $this->_controller->request->data['reportnumber_id'];

		$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumber_id)));

		if(count($Reportnumber) == 0) return $data;

		$xml = $this->_controller->Xml->LoadXmlFileForReport($Reportnumber);

		foreach ($data as $key => $value) {

			$this->_controller->loadModel($key);
			$ModelData = $this->_controller->$key->find('first',array('fields'=>array($key.'.id'),'conditions'=>array($key.'.reportnumber_id'=>$reportnumber_id)));

			$this->_controller->loadModel($key);
			$Insert = array();

			foreach ($value as $_key => $_value) {

				if(isset($xml['settings']->$key->$_key->ticketdataobject)) {
					if(isset($xml['settings']->$key->$_key->ticketdata)){
							$reportdata = '';
							$reportdata = $this->_controller->$key->find('first',array('fields'=>array($_key),'conditions'=>array($key.'.reportnumber_id'=>$reportnumber_id)));
							if(!empty($reportdata[$key][$_key])&& $reportdata[$key][$_key] <> ''){
									unset($data[$key][$_key]);
								 continue;
							}
					}
				}

				$Field = explode($key,$_value['id']);
				$Field = Inflector::underscore($Field[1]);

				$Insert[$Field] = $_value['value'];

			}
			$Insert['id'] =$ModelData [$key]['id'];
			$Insert['reportnumber_id'] = $reportnumber_id;

			if(!empty($Insert)){
				 $this->_controller->$key->save($Insert);
				 	$this->_controller->Data->Archiv($reportnumber_id,ucfirst($Reportnumber['Testingmethod']['value']));
					$this->_controller->Autorisierung->Logger($reportnumber_id,array('Template loaded'));
			}
		}

		return $data;
	}

	public function AddEvaluationDescriptionTemplate($data){

		$Testingmethod = $this->_controller->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $data['TemplatesEvaluation']['testingmethod_id'])));
		$locale = $this->_controller->Lang->Discription();

		$Data['Reportnumber']['version'] = $Testingmethod['Testingmethod']['version'];
		$Data['Testingmethod'] = $Testingmethod['Testingmethod'];

		$xml = $this->_controller->Xml->LoadXmlFileForReport($Data);

		$Model = $data['Evaluationmodel'];

		$data['Template']['evaluation_temp_description'] = array();

		foreach($data['Template']['evaluation_temp'] as $key => $value){
			$data['Template']['evaluation_temp_description'][] = trim($xml['settings']->{$Model}->{$value}->discription->{$locale});
		}

		return $data;
	}
}
