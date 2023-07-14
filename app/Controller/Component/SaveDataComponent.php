<?php
class SaveDataComponent extends Component {
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

	public function LastValue($Data){

		$ReportModel = $Data['ReportModel'];
		$Field = key($this->_controller->request->data[$ReportModel]);

		if($Data['SafeVars']['evalId'] == 0){

			$Data['last_value'] = '';

			return $Data;

		}

		if(isset($Data['SafeVars']['evalId']) && $Data['SafeVars']['evalId'] > 0){

			$this->_controller->$ReportModel->id = $Data['SafeVars']['evalId'];

		} else {

			$Id = $this->_controller->$ReportModel->find('first',array('conditions' => array($ReportModel . '.reportnumber_id' => $Data['SafeVars']['id'])));
			$this->_controller->$ReportModel->id = $Id[$ReportModel]['id'];

		}

		$Data['last_value'] = $this->_controller->$ReportModel->field($Field);

		return $Data;
	}

	public function ThisValue($Data){

		$ReportModel = $Data['ReportModel'];
		$Field = key($this->_controller->request->data[$ReportModel]);

		$this_value = $this->_controller->request->data[$ReportModel][$Field];

		if(is_array($this_value)) $this_value = implode(',',$this_value);

		$this_value = $this->__SanitizeInput($this_value);

		$Data['this_value'] = $this_value;

		return $Data;
	}

	public function LastIdForRadio($Data){

		$ReportModel = $Data['ReportModel'];

		$Data['last_id_for_radio'] = $ReportModel . Inflector::camelize(key($this->_controller->request->data[$ReportModel]));

		return $Data;
	}

	public function TransportArrayOne($Data){

		$ReportModel = $Data['ReportModel'];
		$Field = key($this->_controller->request->data[$ReportModel]);
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		// diese Array wird der Revison übergeben, wenn nötig
		$TransportArray['model'] = $ReportModel;
		$TransportArray['row'] = $Field;
		$TransportArray['this_id'] = $Data['this_id'];
		$TransportArray['last_value'] = $Data['last_value'];
		$TransportArray['this_value'] = $Data['this_value'];
		$TransportArray['last_id_for_radio'] = $Data['last_id_for_radio'];
		$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');

		$Data['TransportArray'] = $TransportArray;

		return $Data;
	}

	public function TransportArrayTwo($Data){

		$ReportModel = $Data['ReportModel'];
		$Field = key($this->_controller->request->data[$ReportModel]);
		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$_id = reset($Data['_id']);

		$TransportArray['table_id'] = $_id;
		$TransportArray['last_value'] = $Data['lastRecord'][$ReportModel][$Data['field_for_saveField']];
		$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');
		$TransportArray['last_value'] = $Data['lastRecord'][$ReportModel][$Data['field_for_saveField']];

		$Data['LastRecord'] = $TransportArray;

		return $Data;
	}

	public function StatusTest($Data){

		// Wenn die Prüfberichtsmappe gesperrt ist
	 	if(isset($Data['Reportnumber']['no_rights']) && $Data['Reportnumber']['no_rights'] == 1) {
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));
	 	}

	 	// Wenn die Prüfberichtsmappe gesperrt ist
	 	if($this->writeprotection) {
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));
	 		$this->set('error', __('Report is writeprotected - changes will not be saved.'));
	 	}

	 	// Wenn der Prüfbericht abgerechnet ist
	 	if($Data['Reportnumber']['settled'] > 0){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is closed!'));
	 	}

	 	// Wenn der Prüfbericht gelöscht ist
	 	if($Data['Reportnumber']['delete'] > 0){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is marked as deleted!'));
	 	}

	 	// Wenn der Prüfbericht deaktiviert ist
	 	if($Data['Reportnumber']['deactive'] > 0){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is marked as deactivate!'));
	 	}

	 	// Wenn der Prüfbericht geschlossen ist
	 	if($Data['Reportnumber']['status'] > 0){

			$Data = $this->_controller->Data->RevisionCheckTime($Data);

			if(!isset($Data['Reportnumber']['revision_write'])){

				$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is closed!'));

			}

			if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1){
			}
	 	}

		$Request = $this->_controller->Data->CheckDate();

		if($Request === false){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. Date format not valide.'));
	 	}

		return $Data;

	}

	public function CreateOption($Data){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$ReportModel = $Data['ReportModel'];
		$Data['evalId'] = $evalId;

		if(strpos($ReportModel,'Evaluation') === false){
	 		$Data['option'] = array($ReportModel.'.reportnumber_id' => $id);

			return $Data;
	 	}

 		$Data['id_for_development'] = array($evalId);

		$Data['aktion_for_revision'] = 'editevaluation';
		$Data['TransportArray']['table_id'] = $evalId;

 		if($evalId == 0){
 			$this->_controller->$ReportModel->create();
 			$this->_controller->$ReportModel->save(array('reportnumber_id' => $id));
 			$Data['evalId'] = $this->_controller->$ReportModel->getInsertID();
 			$Data['id_for_development'] = array($Data['evalId']);

			$aktion_for_revision = 'addevaluation';

			$SaveJsonUrlArray = explode('/',$this->_controller->request->here);

//			if($SaveJsonUrlArray[8] == 0) $SaveJsonUrlArray[9] = 0;

			$SaveJsonUrlArray[8] = $Data['evalId'];

			$SaveJsonUrl = implode('/',$SaveJsonUrlArray);

			$Data['SaveJsonUrl'] = $SaveJsonUrl;

			$EditJsonUrlArray = $SaveJsonUrlArray;

			$EditJsonUrlArray[2] = 'editevalution';
			$Data['EditJsonUrl'] = implode('/',$EditJsonUrlArray);

			$EditJsonUrlArray[2] = 'duplicatevalution';
			$Data['DupliJsonUrl'] = implode('/',$EditJsonUrlArray);

			$EditJsonUrlArray[2] = 'deleteevalution';
			$Data['DeleteJsonUrl'] = implode('/',$EditJsonUrlArray);

			$EditJsonUrlArray[2] = 'printweldlabel';
			$Data['LabelJsonUrl'] = implode('/',$EditJsonUrlArray);
 		}

 		$Data['option'] = array($ReportModel.'.reportnumber_id' => $id,$ReportModel.'.id' => $Data['evalId'],$ReportModel.'.deleted' => 0);

		return $Data;

	}

	public function WeldEdit($Data){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		if($evalId == 0) return $Data;

		$ReportModel = $Data['ReportModel'];
		$Field = key($this->_controller->request->data[$ReportModel]);

		$Data['WeldEdit'] = $weldedit;

		if(strpos($ReportModel,'Evaluation') === false) return $Data;

		// IDs von allen Nahtabschnitten mit der selben Description holen
		$_ids = $this->_controller->$ReportModel->find('all',
				array(
						'fields' => array($ReportModel.'.id'),
						'joins' => array(
								array(
										'alias' => 'O'.$ReportModel,
										'table' => $this->_controller->$ReportModel->table,
										'conditions' => array(
												'O'.$ReportModel.'.description='.$ReportModel.'.description',
												$ReportModel.'.reportnumber_id' => $id
										)
								)
						),
						'conditions' => array(
								'O'.$ReportModel.'.id' => $evalId,
								'O'.$ReportModel.'.reportnumber_id' => $id,		// Nur Evaluations des aktuellen Prüfberichts bearbeiten
								$ReportModel.'.reportnumber_id' => $id,
								$ReportModel.'.deleted' => 0
						)
				)
		);

		$EvaluationIds = Hash::extract($_ids, '{n}.' . $ReportModel . '.id');

		$Data['EvaluationIds'] = $EvaluationIds;

		if($weldedit == 0) return $Data;

		$id_for_development = array();
		$_id = array();
		$this_value_for_saveField = '';

		foreach($_ids as $__ids){
			$id_for_development[] = $__ids[$ReportModel]['id'];
		}

		$_option = array(
			$ReportModel => array(
				$Field => $Data['this_value']
			)
		);

		$option = array_map(function($elem) use($_option) { return array_merge_recursive($elem, $_option); }, $_ids);

		$field_for_saveField = $Field;
		$_id = $id_for_development;
		$this_value_for_saveField = $Data['this_value'];

		$Data['option'] = $option;
		$Data['id_for_development'] = $id_for_development;
		$Data['_id'] = $_id;
		$Data['this_value_for_saveField'] = $this_value_for_saveField;
		$Data['field_for_saveField'] = $field_for_saveField;

		return $Data;
	}

	public function CheckWeldResult($Data){

		if(!isset($Data['EvaluationIds'])) return $Data;
		if(count($Data['EvaluationIds']) == 0) return $Data;

		$ReportModel = $Data['ReportModel'];

		$Options = array(
			'fields' => array('id','result'),
			'conditions' => array(
				$ReportModel.'.id' => $Data['EvaluationIds'],
				$ReportModel.'.reportnumber_id' => $Data['Reportnumber']['id'],
				$ReportModel.'.deleted' => 0
			)
		);

		$Result = $this->_controller->$ReportModel->find('list',$Options);

		$TestArray = array(0,1,2);

		foreach ($TestArray as $key => $value) $Test[$key] = array_search($value, $Result);
//var_dump($Test);
		if($Test[1] !== false) $Data['WeldResult'] = 1;

		if($Test[0] !== false) $Data['WeldResult'] = 0;

		if($Test[2] !== false) $Data['WeldResult'] = 2;

		return $Data;

	}

	public function CheckEvaluationDescriptions($Data){

		$ReportModel = $Data['ReportModel'];

		if(key($this->_controller->request->data[$ReportModel]) == 'position') $Data = $this->_CheckEvaluationDescriptionPosition($Data);
		if(key($this->_controller->request->data[$ReportModel]) == 'description') $Data = $this->_CheckEvaluationDescriptionDescription($Data);

		return $Data;
	}

	protected function _CheckEvaluationDescriptionDescription($Data){

		$ReportModel = $Data['ReportModel'];
		$evalId = $this->_controller->request->projectvars['VarsArray'][5];

		$options = array(
			'conditions' => array(
				$ReportModel . '.id' => $evalId
			)
		);

		$Orginal = $this->_controller->$ReportModel->find('first',$options);

		$options = array(
			'fields' => array('description'),
			'conditions' => array(
				$ReportModel . '.reportnumber_id' => $Data['Reportnumber']['id'],
			)
		);

		$Check = $this->_controller->$ReportModel->find('list',$options);

		$Check = array_unique($Check);

		$key = array_search($Data['this_value'], $Check);

		if($key !== false){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The following value already exists') . ': "' . $Data['this_value'] . '"');

			return $Data;
		}

		return $Data;

	}

	protected function _CheckEvaluationDescriptionPosition($Data){

		$Testingmethod =
		$this->_controller->Reportnumber->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id' 	=>$Data['Reportnumber']['testingmethod_id'])));
		if(strpos($Testingmethod['Testingmethod']['value'],'ut') !== false){
			return $Data;
		}
		$ReportModel = $Data['ReportModel'];
		$evalId = $this->_controller->request->projectvars['VarsArray'][5];
//pr($Data);
		$options = array(
			'conditions' => array(
				$ReportModel . '.id' => $evalId
			)
		);

		$Orginal = $this->_controller->$ReportModel->find('first',$options);

		$options = array(
			'fields' => array('position'),
			'conditions' => array(
				$ReportModel . '.id !=' => $Orginal[$ReportModel]['id'],
				$ReportModel . '.reportnumber_id' => $Data['Reportnumber']['id'],
				$ReportModel . '.description' => $Orginal[$ReportModel]['description']
			)
		);

		$Check = $this->_controller->$ReportModel->find('list',$options);

		$key = array_search($Data['this_value'], $Check);

		if($key !== false){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The following value already exists') . ': "' . $Data['this_value'] . '"');

			return $Data;
		}

		return $Data;

	}

	public function CleanErrors($Data){

		if($Data['field_for_saveField'] != 'error') return $Data;
		if(empty($Data['this_value'])) return $Data;

		$Model = Inflector::underscore($Data['ReportModel']);
		$Model = explode('_',$Model);

		if($Model[2] != 'evaluation') return $Data;

		$Test = explode(',',$Data['this_value']);

		if(count($Test) == 0) return $Data;

		$CleanValueArray = array();

		foreach ($Test as $key => $value) {

			if(empty($value)) continue;

			$CleanValueArray[] = trim($value);

		}

		if(count($CleanValueArray) == 0) return $Data;

		$CleanValue = implode(',',$CleanValueArray);
		$Data['this_value'] = $CleanValue;
		$Data['this_value_for_saveField'] = $CleanValue;

		if(count($Data['option']) > 0){
			foreach ($Data['option'] as $key => $value) {

				if(!isset($Data['option'][$key][$Data['ReportModel']])) continue;
				if(!isset($Data['option'][$key][$Data['ReportModel']]['error'])) continue;

				$Data['option'][$key][$Data['ReportModel']]['error'] = $CleanValue;

			}
		}

		return $Data;
	}

	public function PositionEdit($Data){

		if(isset($Data['this_value_for_saveField'])) return $Data;

		$ReportModel = $Data['ReportModel'];
		$Field = key($this->_controller->request->data[$ReportModel]);

		$_id = $this->_controller->$ReportModel->find('list',array('conditions'=> $Data['option'], 'limit'=>1, 'fields'=>array('id','id')));

		$option = array($ReportModel.'.id' => reset($_id), $ReportModel.'.'.$Field => $Data['this_value']);
		$option = array(Hash::expand($option));
		$field_for_saveField = $Field;
		$this_value_for_saveField = $Data['this_value'];

		$Data['option'] = $option;
		$Data['_id'] = $_id;
		$Data['this_value_for_saveField'] = $this_value_for_saveField;
		$Data['field_for_saveField'] = $field_for_saveField;

		return $Data;
	}

	public function LastRecord($Data){

		$ReportModel = $Data['ReportModel'];
		$id = reset($Data['_id']);

		$Data['lastRecord'] = $this->_controller->$ReportModel->findById($id);

		return $Data;
	}

	public function SaveData($Data){

		if(!is_array($Data['option'])){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. Please try again!'));
			return $Data;

		}

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$ReportModel = $Data['ReportModel'];

		if(isset($Data['field_for_saveField']) && isset($Data['this_value_for_saveField']) && !isset($Data['id_for_development'])){

			$this->_controller->$ReportModel->id = reset($Data['_id']);

			$this->_controller->$ReportModel->saveField($Data['field_for_saveField'],$Data['this_value_for_saveField']);

			if(Configure::check('SignatoryDeleteRequired') && Configure::read('SignatoryDeleteRequired') == true){
					$this->_controller->Data->RequiredFieldDeleteSignatory($Data,$Data['field_for_saveField']);
			}

			$Data['LogArray'] = array($ReportModel => array($Data['field_for_saveField'] => $Data['this_value_for_saveField']));


			if($Data['this_value_for_saveField'] == ''){
				$Data['FlashMessages'][]  = array('type' => 'success','message' => __('The value has been removed.'));
	 		}
	 		else {
				$Data['FlashMessages'][]  = array('type' => 'success','message' => __('The value has been saved.'));
	 		}

			// Wenn ein Prüfbericht aufgerufen und bearbeitet wird
	 		if($this->_controller->Session->check('Id_for_refresh') == true){
	 			$this->_controller->Session->delete('Id_for_refresh');
	 		}

			$Data['SaveOkay']  = true;

		}
		else {

			foreach($Data['option'] as $_key => $_option) {

				$Data['lastRecord'] = $this->_controller->$ReportModel->findById($_option[$ReportModel]['id']);

				$Data['TransportArray']['last_value'] = $Data['lastRecord'][$ReportModel][$Data['field_for_saveField']];
				$Data['TransportArray']['table_id'] = $_option[$ReportModel]['id'];
				$Data['TransportArray']['reason'] = $this->_controller->Session->read('revision'.$id.'reason');

				if(isset($Data['TransportArray'])) $reportnumber['LastRecord'] = $Data['TransportArray'];

				$this->_controller->$ReportModel->id = $_option[$ReportModel]['id'];
				$this->_controller->$ReportModel->saveField($Data['field_for_saveField'], $_option[$ReportModel][$Data['field_for_saveField']]);
				$this->_controller->Search->UpdateTable($Data,$ReportModel,$Data['field_for_saveField'],$_option[$ReportModel][$Data['field_for_saveField']]);

				$Data['LogArray'] = array($ReportModel => array($Data['field_for_saveField'] => $_option[$ReportModel][$Data['field_for_saveField']]));
				$Data['FlashMessages'][]  = array('type' => 'success','message' => __('The value has been saved.'));
				$Data['SaveOkay']  = true;
			}
		}

		if(isset($Data['WeldEdit']) && $Data['WeldEdit'] == 1 && $Data['field_for_saveField'] == 'description'){
			$Data['paginationOverview']['new_value'] = $Data['this_value'];
			$Data['paginationOverview']['last_value'] = $Data['last_value'];
		}

		if(isset($Data['WeldEdit']) && $Data['WeldEdit'] == 0 && $Data['field_for_saveField'] == 'position'){
			$Data['paginationOverview']['new_value'] = $Data['this_value'];
			$Data['paginationOverview']['last_value'] = $Data['last_value'];
		}

		return $Data;
	}

	public function SaveRevision($Data){

		if(!Configure::check('RevisionInReport') || Configure::read('RevisionInReport') == false) return $Data;
		if($Data['Reportnumber']['revision_progress'] == 0) return $Data;

		$ReportModel = $Data['ReportModel'];

		$output = array();

		$this->_controller->loadModel('Revision');

		$input = array_merge($Data['TransportArray'],$Data['Reportnumber']);

		$input['reportnumber_id'] = $input['id'];
		$input['action'] = $Data['aktion_for_revision'];
		$input['user_id'] = $this->_controller->Auth->user('id');
		unset($input['id']);

		$id_array = explode(',',$input['table_id']);
		asort($id_array);

		$VerfahrenArray = explode('_',Inflector::underscore($ReportModel));
		$verfahren = $VerfahrenArray[1];
		$Verfahren = Inflector::camelize($verfahren);

		foreach($id_array as $_id_array){

			$options = array('conditions' => array($ReportModel . '.id' => trim($_id_array)));
			$LastValue = $this->_controller->$ReportModel->find('first',$options);

			if(count($LastValue) > 0){

				if(isset($LastValue[$ReportModel][$input['row']])){

					$input['last_value'] = $input['last_value'];
					$input['this_value'] = $input['this_value'];
					$input['table_id'] = $LastValue[$ReportModel]['id'];
					$input['row'] = $input['row'];

				}
				else {

					$input['last_value'] = $LastValue[$ReportModel]['id'];
					$input['table_id'] = 0;
					$input['row'] = '';
					$input['this_value'] = '';

				}

				if($input['last_value'] != $input['this_value']){

					$this->_controller->Revision->create();
					if($this->_controller->Revision->save($input)) $output[] = $this->_controller->Revision->getLastInsertId();

				}
			}
		}

		$Data['xml'] = $this->_controller->Xml->DatafromXml($verfahren,'file',$Verfahren);
		$Data['Tablenames']['Evaluation'] = $ReportModel;
		$Data = $this->_controller->Report->CheckRevisionValues($Data);

		unset($Data['xml']);

		return $Data;

	}

	public function SaveTechnicalPlace($Data){

		if(!isset($Data['SaveOkay'])) return $Data;
		if($Data['SaveOkay'] != true) return $Data;

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$ReportModel = $Data['ReportModel'];

		//Technical place aktualisieren
		if(preg_match('/TechnicalPlace/', $Data['this_id'])) {

			$_data = $this->_controller->$ReportModel->find('first', array('fields'=>array('id', 'technical_place_level1', 'technical_place_level2', 'technical_place_level3'), 'conditions'=>array('reportnumber_id'=>$id)));

			$technical_place = $_data[$ReportModel]['technical_place_level1'].'='.$_data[$ReportModel]['technical_place_level2'].'='.$_data[$ReportModel]['technical_place_level3'];
			$technical_place = trim(preg_replace('/[=]+/', '=', $technical_place), '=');
			$saveData[$ReportModel] = array('technical_place'=>$technical_place);

			$this->_controller->$ReportModel->save($saveData);
			$this->_controller->Autorisierung->Logger($id, $saveData);
		}

		return $Data;
	}

	public function UpdateLastUser($Data){

		if(!isset($Data['SaveOkay'])) return $Data;
		if($Data['SaveOkay'] != true) return $Data;

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$ReportModel = $Data['ReportModel'];

		// Reportnumberstabelle, User aktualisieren
		$reportdata = array('id' => $id,'modified_user_id' => $this->_controller->Auth->user('id'));
		$this->_controller->Reportnumber->save($reportdata);

		if(strpos($ReportModel,'Evaluation') !== false && isset($Data['LogArray'][$ReportModel])){
			$Data['LogArray'] = $Data['LogArray'][$ReportModel];
			$Data['LogArray']['id'] = $evalId;
		}

		return $Data;
	}

	public function UpdateReportnumberResult($Data){

		if(!isset($Data['SaveOkay'])) return $Data;
		if($Data['SaveOkay'] != true) return $Data;

		$ReportModel = $Data['ReportModel'];

		if(strpos($ReportModel,'Evaluation') === false) return $Data;
		if($Data['field_for_saveField'] != 'result') return $Data;

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];


		$evalne = $this->_controller->$ReportModel->find('all',array(
			'conditions' => array(
				$ReportModel.'.reportnumber_id'=>$id,
				$ReportModel.'.deleted'=>0,
				$ReportModel.'.result'=>2
				)
			)
		);

		if(isset($evalne) && !empty($evalne)){
			$reportdata = array('id' => $id,'modified_user_id' => $this->_controller->Auth->user('id'),'result' => 2);
		} else {
			$reportdata = array('id' => $id,'modified_user_id' => $this->_controller->Auth->user('id'),'result' => 1);
		}

		$this->_controller->Reportnumber->save($reportdata);

		return $Data;
	}

	public function UpdateDevelopmentData($Data){

		if(!isset($Data['SaveOkay'])) return $Data;
		if($Data['SaveOkay'] != true) return $Data;

		$ReportModel = $Data['ReportModel'];

		if(Configure::check('DevelopmentsEnabled') === false) return $Data;
		if(Configure::read('DevelopmentsEnabled') === false) return $Data;
		if(strpos($ReportModel,'Evaluation') === false) return $Data;
		if($Data['field_for_saveField'] != 'result') return $Data;

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		if($weldedit == 0 && $evalId > 0 && count($Data['id_for_development']) == 1){

			$forDevelopment = $this->_controller->$ReportModel->find('list',array('fields'=>array('description'),'conditions'=>array($ReportModel.'.reportnumber_id'=>$id)));
			$id_for_development = array_keys($forDevelopment,$forDevelopment[$Data['id_for_development'][0]]);

			$this->_controller->Data->DevelopmentReset($orderID,$id_for_development);
			$Data['DevelopmentReset'] = true;

		}



		return $Data;
	}

	public function DeleteElements($Data,$DelArray){

		if(count($DelArray) == 0) return $Data;

		foreach ($DelArray as $key => $value) {

			if(!isset($Data[$value])) continue;

			unset($Data[$value]);

		}

		return $Data;
	}

	protected function __SanitizeInput($Data) {

		$Data = Sanitize::html($Data, array('remove' => true));
		$Data = html_entity_decode($Data);

		return $Data;
	}
}
