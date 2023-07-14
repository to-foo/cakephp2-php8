<?php
class ReportComponent extends Component {
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

	public function CheckErrorCode($data) {

		$Model = key($this->_controller->request->data);
		$Field = key($this->_controller->request->data[$Model]);

		if($Field != 'error') return $data;

		$blacklist = array(',',';',':','-');

		$data = str_replace($blacklist,' ',$data);
		$data = preg_replace("/\s\s+/"," ",$data);
		
		$data_array = explode(" ",$data);

		if(count($data_array) == 0) return $data;

		$this->_controller->loadModel('ErrorNumber');

		$ErrorNumber = $this->_controller->ErrorNumber->find('list',array(
			'conditions' => array(
				'ErrorNumber.number' => $data_array
				)
			)
		);

		if(count($data_array) != count($ErrorNumber)) return false;

		return $data;

	}

	public function CheckWeldpositionBeforSave($data){

		$Model = key($this->_controller->request->data);

		if(!isset($this->_controller->request->data[$Model]['position'])) return true;

		$DataId = $this->_controller->request->data[$Model]['id'];
		$NewWeldPosition = trim($this->_controller->request->data[$Model]['position']);

		$Exists = $this->_controller->{$Model}->find('first',array(
			'conditions' => array(
					$Model . '.id' => $DataId,
				)
			)
		);

		$CurrentWeldDescription = $Exists[$Model]['description'];
		$CurrentWeldPosition = $Exists[$Model]['position'];
		$ReportnumberId = $data['Reportnumber']['id'];

		$Check = $this->_controller->{$Model}->find('list',array(
			'conditions' => array(
				$Model . '.description' => $CurrentWeldDescription,
				$Model . '.position' => $NewWeldPosition,
				$Model . '.reportnumber_id' => $ReportnumberId,
			)
			)
		);

		if(empty($Check)) return true;
		else return false;

	}

	public function CheckWelddescriptionBeforSave($data){

		$Model = key($this->_controller->request->data);

		if(!isset($this->_controller->request->data[$Model]['description'])) return true;

		$DataId = $this->_controller->request->data[$Model]['id'];
		$NewWeldDescription = trim($this->_controller->request->data[$Model]['description']);

		$ExistsDescription = $this->_controller->{$Model}->find('first',array(
			'fields' => array('description'),
			'conditions' => array(
					$Model . '.description' => $NewWeldDescription,
					$Model . '.reportnumber_id' => $data['Reportnumber']['id'],
				)
			)
		);

		if(empty($ExistsDescription)) return true;

		$CurrentDescription = $this->_controller->{$Model}->find('first',array(
			'fields' => array('description'),
			'conditions' => array(
					$Model . '.id' => $DataId,
				)
			)
		);

		$CurrentWeldDescription = $CurrentDescription[$Model]['description'];

		$CurrentIds = $this->_controller->{$Model}->find('list',array(
			'conditions' => array(
				$Model . '.description' => $CurrentWeldDescription,
				$Model . '.reportnumber_id' => $data['Reportnumber']['id'],
			)
			)
		);

		$NewIds = $this->_controller->{$Model}->find('list',array(
			'conditions' => array(
				$Model . '.description' => $NewWeldDescription,
				$Model . '.reportnumber_id' => $data['Reportnumber']['id'],
			)
			)
		);

		if(empty(array_intersect($CurrentIds, $NewIds))) return false;
		else return true;

	}

	public function DataIdForEditable(){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$evalId = $this->_controller->request->projectvars['VarsArray'][5];
		$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$data = $this->_controller->request->data;

		$Class = $data['Class'];

		unset($data['Class']);
		unset($data['json_true']);
		unset($data['ajax_true']);

		$Model = key($data);

		$evalution_id = $this->_controller->request->data[$Model]['id'];
		$output = array($this->_controller->request->data[$Model]['id']);

		$ClassArray = explode(' ',$Class);

		$this->_controller->loadModel($Model);

		$key = array_search('editall', $ClassArray);

		if($key !== false){

			$output = $this->_controller->{$Model}->find('list',array('conditions' => array($Model . '.reportnumber_id' => $id)));

			if(count($output) > 0) return $output;

		};

		$key = array_search('editweld', $ClassArray);

		if($key !== false){

			$weld = $this->_controller->{$Model}->find('first',array('conditions' => array($Model . '.id' => $evalution_id)));

			if(count($weld) == 0) return $output;

			$output = $this->_controller->{$Model}->find('list',
				array(
					'conditions' => array(
						$Model . '.reportnumber_id' => $id,
						$Model . '.description' => $weld[$Model]['description']
					)
				)
			);

			if(count($output) > 0) return $output;

		};

		return $output;

	}

	public function AddSaveJsonUrl($data){

		$url = Router::url(
			array_merge(
				array(
					'controller' => $this->_controller->request->params['controller'],
					'action' => 'savejson',
				),
				$this->_controller->request->projectvars['VarsArray']
			)
		);

		$SaveJsonUrl = explode('/',$url);

//		if($SaveJsonUrl[8] == 0) $SaveJsonUrl[9] = 0;

		$data['SaveJsonUrl'] = implode('/',$SaveJsonUrl);

		return $data;

	}

	public function AddElementsForNewEval($data){

		if($data['paginationOverview']['current_id'] != 0) return $data;
		$data['paginationOverview']['dropdownmenue'] = array(__('New entry',true) => array('key' => 0,'value' => __('New entry',true)));
		$data['paginationOverview']['position_url'] = array(0 => $data['EditJsonUrl']);
		$data['paginationOverview']['current_weld_description'] = 0;
		$data['paginationOverview']['current_weld_description_bread'] = __('New entry',true);
		$data['paginationOverview']['current_positon_description'] = __('New entry',true);
		$data['paginationOverview']['current_result'] = 0;

//pr($data['paginationOverview']);
//die();

		return $data;
	}

	public function ChangeKeysForJson($data){

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		foreach ($data[$ReportEvaluation] as $key => $value) {
			$data['Camelize'][$ReportEvaluation][Inflector::camelize($key)] = $value;
			$data['Underscore'][$ReportEvaluation][Inflector::camelize($key)] = $key;
		}

		if(!isset($data['paginationOverview']['dropdownmenue'])) return $data;

		$output = array();

		foreach($data['paginationOverview']['dropdownmenue'] as $key => $value){

			if(!is_array($value)) continue;

			$out = array();
			foreach ($value as $_key => $_value) {
				$out[] = array('key' => $_key,'value' => $_value);
			}
			$output[] = $out;
		}

		if(count($output) > 0) $data['paginationOverview']['dropdownmenue'] = $output;

		return $data;
	}

	public function DuplicateEvaluation($data) {

		if($this->_controller->request->projectvars['VarsArray'][6] == 1) $data = $this->__DuplicateEvaluationWeld($data);
		if($this->_controller->request->projectvars['VarsArray'][6] == 0) $data = $this->__DuplicateEvaluationPosition($data);

		$data = $this->FunctionsUrlUpdate($data);

		return $data;
	}

	public function FunctionsUrlUpdate($data) {

		if(!isset($data['SaveJsonUrl'])) return $data;

		$FunctionsUrl = explode('/',$data['SaveJsonUrl']);

		if(isset($data['paginationOverview']['current_id']) && $data['paginationOverview']['current_id'] == 0) $FunctionsUrl[9] = 0;

		$FunctionsUrl[2] = 'duplicatevalution';
		$data['DupliJsonUrl'] = implode('/',$FunctionsUrl);

		$FunctionsUrl[2] = 'deleteevalution';
		$data['DeleteJsonUrl'] = implode('/',$FunctionsUrl);

		$FunctionsUrl[2] = 'editevalution';
		$data['EditJsonUrl'] = implode('/',$FunctionsUrl);

		$FunctionsUrl[2] = 'printweldlabel';
		$data['LabelJsonUrl'] = implode('/',$FunctionsUrl);

		$FunctionsUrl[2] = 'editevalution';
		$FunctionsUrl[9] = 1;
		$data['BreadcrubUrl'] = implode('/',$FunctionsUrl);

		return $data;
	}

	public function StatusTest($data){

		// Wenn die Prüfberichtsmappe gesperrt ist
	 	if(isset($data['Reportnumber']['no_rights']) && $data['Reportnumber']['no_rights'] == 1) {
			$data['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));
	 	}

	 	// Wenn die Prüfberichtsmappe gesperrt ist
	 	if($this->writeprotection) {
			$data['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));
			$data['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));
	 	}

	 	// Wenn der Prüfbericht abgerechnet ist
	 	if($data['Reportnumber']['settled'] > 0){
			$data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is closed!'));
	 	}

	 	// Wenn der Prüfbericht gelöscht ist
	 	if($data['Reportnumber']['delete'] > 0){
			$data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is marked as deleted!'));
	 	}

	 	// Wenn der Prüfbericht deaktiviert ist
	 	if($data['Reportnumber']['deactive'] > 0){
			$data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is marked as deactivate!'));
	 	}

	 	// Wenn der Prüfbericht geschlossen ist
	 	if($data['Reportnumber']['status'] > 0){

			if(!isset($data['Reportnumber']['revision_write'])){

				$data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is closed!'));

			}

			if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1){
			}
	 	}

		return $data;

	}

	public function DeleteEvalution($data){

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		if(count($data[$ReportEvaluation]) == 0) return $data;

		$options = array(
			'conditions' => array(
				$ReportEvaluation . '.reportnumber_id' => $data['Reportnumber']['id'],
				$ReportEvaluation . '.description' => $data[$ReportEvaluation]['description'],
			)
		);

		$EvaluationCount = $this->_controller->$ReportEvaluation->find('list',$options);

		if(count($EvaluationCount) == 1) $this->_controller->request->projectvars['weldedit'] = 1;

		if($this->_controller->request->projectvars['weldedit'] == 0) $data = $this->__DeleteEvalutionPosition($data);
		if($this->_controller->request->projectvars['weldedit'] == 1) $data = $this->__DeleteEvalutionDescription($data);

		return $data;
	}

	protected function __DeleteEvalutionPosition($data){

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$Delete = $this->_controller->$ReportEvaluation->delete($data[$ReportEvaluation]['id']);
//		$Delete = true;

		if($Delete === false){

			$data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be deleted. Please try again.'));

			return $data;

		}

		$data['FlashMessages'][]  = array('type' => 'success','message' => __('The value has been deleted.'));
		$data['deletetElement'][$data[$ReportEvaluation]['id']]  = $data[$ReportEvaluation]['description'] . '/' . $data[$ReportEvaluation]['position'];
/*
		$this->Search->DeleteLinkingEvaluationEntries($report,$LastRecord);

		$LogArray['id'] = $evalId;
		if(isset($welddelete)) $LogArray['welddelete'] = $welddelete;
		$this->Autorisierung->Logger($id,$LogArray);

		// Wenn in diesem Auftrag ein Progress dargestellt werdn soll
		$this->Data->DevelopmentReset($orderID,$evalutionAllId);

		$TransportArray['last_value'] = implode(',',$id_for_revision);

		$report['Reportnumber']['revision_progress'] > 0 ?$this->Data->SaveRevision($report,$TransportArray,$action_for_revision):'';

		$this->Data->Archiv($this->request->reportnumberID,$Verfahren);

		$this->Session->setFlash(__('Evalution Area deleted'));
		$this->Data->SetResultReportnumber($id,$ReportEvaluation);
*/
		$options = array('conditions' =>
			array(
				$ReportEvaluation . '.reportnumber_id' => $data[$ReportEvaluation]['reportnumber_id'],
				$ReportEvaluation . '.description' => $data[$ReportEvaluation]['description'],
			)
		);

		$Count = $this->_controller->$ReportEvaluation->find('all',$options);

		if($Count == 0){

			$url = Router::url(
				array_merge(
					array(
						'controller' => $this->_controller->request->params['controller'],
						'action' => 'edit',
					),
					$this->_controller->request->projectvars['VarsArray']
				)
			);

			$SaveAjaxUrl = explode('/',$url);
			$SaveAjaxUrl[8] = 0;
			$SaveAjaxUrl[9] = 0;
			$SaveAjaxUrl[11] = 0;
			unset($SaveAjaxUrl[0]);
			$data['SaveAjaxUrl'] = implode('/',$SaveAjaxUrl);

		} else {

			$data[$ReportEvaluation] = $Count[0][$ReportEvaluation];
			$this->_controller->request->projectvars['VarsArray'][6] = 1;

			$url = Router::url(
				array_merge(
					array(
						'controller' => $this->_controller->request->params['controller'],
						'action' => 'savejson',
					),
					$this->_controller->request->projectvars['VarsArray']
				)
			);

			$SaveJsonUrl = explode('/',$url);
			$SaveJsonUrl[8] = $data[$ReportEvaluation]['id'];
			$SaveJsonUrl[9] = 1;
			$SaveJsonUrl[11] = 0;

			$data['SaveJsonUrl'] = implode('/',$SaveJsonUrl);
		}

		return $data;
	}

	protected function __DeleteEvalutionDescription($data){

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$options = array(
			'conditions' => array(
				$ReportEvaluation . '.reportnumber_id' => $data['Reportnumber']['id'],
				$ReportEvaluation . '.description' => $data[$ReportEvaluation]['description'],
			)
		);

		$Evaluation = $this->_controller->$ReportEvaluation->find('list',$options);

		if(count($Evaluation) == 0){

			$data['FlashMessages'][]  = array('type' => 'error','message' => __('No data could be found.'));

		}

		$Delete = $this->_controller->$ReportEvaluation->deleteAll(array($ReportEvaluation . '.id' => $Evaluation), false);

		if($Delete === false){

			$data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be deleted. Please try again.'));

			return $data;

		}

		$data['FlashMessages'][]  = array('type' => 'success','message' => __('The value has been deleted.'));
		$data['deletetElement'][$data[$ReportEvaluation]['id']]  = $Evaluation;

		/*
				$this->Search->DeleteLinkingEvaluationEntries($report,$LastRecord);

				$LogArray['id'] = $evalId;
				if(isset($welddelete)) $LogArray['welddelete'] = $welddelete;
				$this->Autorisierung->Logger($id,$LogArray);

				// Wenn in diesem Auftrag ein Progress dargestellt werdn soll
				$this->Data->DevelopmentReset($orderID,$evalutionAllId);

				$TransportArray['last_value'] = implode(',',$id_for_revision);

				$report['Reportnumber']['revision_progress'] > 0 ?$this->Data->SaveRevision($report,$TransportArray,$action_for_revision):'';

				$this->Data->Archiv($this->request->reportnumberID,$Verfahren);

				$this->Session->setFlash(__('Evalution Area deleted'));
				$this->Data->SetResultReportnumber($id,$ReportEvaluation);
		*/

		$url = Router::url(
			array_merge(
				array(
					'controller' => $this->_controller->request->params['controller'],
					'action' => 'savejson',
				),
				$this->_controller->request->projectvars['VarsArray']
			)
		);

		$SaveAjaxUrl = explode('/',$url);
		$SaveAjaxUrl[2] = 'edit';
		$SaveAjaxUrl[8] = 0;
		$SaveAjaxUrl[9] = 0;
		$SaveAjaxUrl[11] = 0;
		unset($SaveAjaxUrl[0]);
		$data['SaveAjaxUrl'] = implode('/',$SaveAjaxUrl);

		$url = Router::url(
			array_merge(
				array(
					'controller' => $this->_controller->request->params['controller'],
					'action' => 'savejson',
				),
				$this->_controller->request->projectvars['VarsArray']
			)
		);

		$SaveJsonUrl = explode('/',$url);
		$SaveJsonUrl[8] = $data[$ReportEvaluation]['id'];
		$SaveJsonUrl[9] = 1;
		$SaveJsonUrl[11] = 0;

		$data['SaveJsonUrl'] = implode('/',$SaveJsonUrl);

		return $data;
	}

	protected function __DuplicateEvaluationWeld($data){

		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$x = 0;

		$dataDupli = array();

		foreach($data['EvaluationDuplicaten'] as $key => $_data){

			$dataDupli = $this->__CollectDataForDuplicaten($x,$dataDupli,$_data[$ReportEvaluation],$data['xml']['settings']->$ReportEvaluation->children(),$ReportEvaluation,'description');

			$x++;

		}

		$this->_controller->$ReportEvaluation->saveAll($dataDupli);
		$LastID = $this->_controller->$ReportEvaluation->getInsertID();

		$this->_controller->request->projectvars['evalId'] = $LastID;
		$this->_controller->request->projectvars['VarsArray'][8] = $LastID;

		$url = Router::url(
			array_merge(
				array(
					'controller' => $this->_controller->request->params['controller'],
					'action' => 'savejson',
				),
				$this->_controller->request->projectvars['VarsArray']
			)
		);

		$SaveJsonUrl = explode('/',$url);
		$SaveJsonUrl[8] = $LastID;
		$data['SaveJsonUrl'] = implode('/',$SaveJsonUrl);

		$this->_controller->Autorisierung->Logger($id,Hash::extract($dataDupli, '{n}.' . $ReportEvaluation));

		if($data['Reportnumber']['revision_progress'] > 0){

			$actio_array = Hash::extract($data['EvaluationDuplicaten'], '{n}.'.$ReportEvaluation.'.id');

			$TransportArray['model'] = $ReportEvaluation;
			$TransportArray['row'] = null;
			$TransportArray['this_value'] = null;
			$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');
			$TransportArray['last_id_for_radio'] = 0;
		 	$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');
			$TransportArray['last_value'] = implode(',',$actio_array);

			$this->_controller->Data->SaveRevision($data,$TransportArray,'duplicatevalution/weld');

		}

		return $data;
	}

	protected function __DuplicateEvaluationPosition($data){

		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$dataDupli = $this->__CollectDataForDuplicaten(0,array(),$data[$ReportEvaluation],$data['xml']['settings']->$ReportEvaluation->children(),$ReportEvaluation,'position');
		$dataDupli = $this->__PositionValueDropdown($dataDupli,$data);

		if($dataDupli === false){

			$data['FlashMessages'][]  = array('type' => 'error','message' => __('Test sections cannot be added.'),'field' => 'Position');

			return $data;

		}

		$this->_controller->$ReportEvaluation->create();
		$this->_controller->$ReportEvaluation->save($dataDupli[0]);

		$LastID = $this->_controller->$ReportEvaluation->getInsertID();

		$this->_controller->request->projectvars['evalId'] = $LastID;
		$this->_controller->request->projectvars['VarsArray'][8] = $LastID;

		$url = Router::url(
			array_merge(
				array(
					'controller' => $this->_controller->request->params['controller'],
					'action' => 'savejson',
				),
				$this->_controller->request->projectvars['VarsArray']
			)
		);

		$SaveJsonUrl = explode('/',$url);
		$SaveJsonUrl[8] = $LastID;
		$data['SaveJsonUrl'] = implode('/',$SaveJsonUrl);

		$this->_controller->Autorisierung->Logger($id,Hash::extract($dataDupli, '{n}.' . $ReportEvaluation));

		if($data['Reportnumber']['revision_progress'] > 0){

			$actio_array = Hash::extract($data['EvaluationDuplicaten'], '{n}.'.$ReportEvaluation.'.id');

			$TransportArray['model'] = $ReportEvaluation;
			$TransportArray['row'] = null;
			$TransportArray['this_value'] = null;
			$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');
			$TransportArray['last_id_for_radio'] = 0;
		 	$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');
			$TransportArray['last_value'] = implode(',',$actio_array);

			$this->_controller->Data->SaveRevision($data,$TransportArray,'duplicatevalution/position');

		}

		return $data;
	}

	protected function __PositionValueDropdown($dupli,$data){

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		if(empty($data['xml']['settings']->$ReportEvaluation->position->select->fields)) return $dupli;

		$FieldData = $this->__CollectDropDownDataFromField($data,$data['xml']['settings']->$ReportEvaluation->position);

		if(count($FieldData) == 0) return $dupli;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		$Test = $this->_controller->$ReportEvaluation->find('count',array(
			'conditions' => array(
					$ReportEvaluation . '.reportnumber_id' => $id,
					$ReportEvaluation . '.position' => $FieldData
				)
			)
		);

		if($Test == count($FieldData)){

			return false;

		}

		$Test = $this->_controller->$ReportEvaluation->find('count',array(
			'conditions' => array(
					$ReportEvaluation . '.reportnumber_id' => $id,
					$ReportEvaluation . '.position' => ''
				)
			)
		);

		if($Test > 0) return false;

		foreach ($dupli as $key => $value) {
			$dupli[$key][$ReportEvaluation]['position'] = '';
		}

		return $dupli;
	}

	protected function __CollectDataForDuplicaten($x,$dupli,$data,$xml,$ReportEvaluation,$mod){

		$id = $this->_controller->request->projectvars['VarsArray'][4];

		foreach ($xml as $_key => $_value) {

			if(empty($_value->dublicate)) continue;
			if(trim($_value->dublicate) != 'yes') continue;

			$dupli[$x][$ReportEvaluation][trim($_value->key)] = $data[trim($_value->key)];

		}

		$dupli[$x][$ReportEvaluation]['reportnumber_id'] = $id;
		$dupli[$x][$ReportEvaluation][$mod] = $dupli[$x][$ReportEvaluation][$mod] . ' ('.__('Copy', true).')';

		return $dupli;
	}

	public function RelMenueValidationErros($data,$xml){

		if(!isset($this->_controller->request['data']['rel_menue'])) return $xml;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

	 	$rel_menue = $this->_controller->request['data']['rel_menue'];
	 	$this->_controller->Session->write('editmenue',array($id => $rel_menue));

		if(!isset($this->_controller->request['data']['error'])) return $xml;

		$ReportEvaluation = $data['Tablenames']['Evaluation'];


		$ShowValidationErros = $this->_controller->request['data']['field_id'];

		$Evaluation = $data[$ReportEvaluation];

		unset($data[$ReportEvaluation]);

		$data[$ReportEvaluation][0][$ReportEvaluation] = $Evaluation;

		$ValidationErrors = $this->_controller->Reportnumber->getValidationErrors($xml, $data);

		unset($data[$ReportEvaluation]);

		$data[$ReportEvaluation] = $Evaluation ;

		$ValidationErrorsJSON = array();

		foreach($ValidationErrors as $_key => $_data){
			foreach($_data as $__key => $__data){
				$xml['settings']->$ReportEvaluation->$__key->validate->error = 1;
			}
		}

		return $xml;

	}

	public function EditMenue(){

		$editmenue = array();

	 	$editmenue[0] = array(
	 			'id' => 'EditGeneral',
	 			'action' => 'edit',
	 			'class' => 'deaktive',
	 			'discription' => __('Show General infos',true),
	 			'rel' => 'General'
	 	);
	 	$editmenue[1] = array(
	 			'id' => 'EditSpecify',
	 			'action' => 'edit',
	 			'class' => 'deaktive',
	 			'discription' => __('Show Specify infos',true),
	 			'rel' => 'Specify'
	 	);
	 	$editmenue[2] = array(
	 			'id' => 'TestingArea',
	 			'action' => 'edit',
	 			'class' => 'deaktive',
	 			'discription' => __('Show Testing area',true),
	 			'rel' => 'TestingArea'
	 	);
	 	$editmenue[3] = array(
	 			'id' => 'EditEvalution',
	 			'class' => 'active',
	 			'discription' => __('Edit Evalution',true),
	 			'rel' => 'EditEvalution'
	 	);

		return $editmenue;
	}

	public function UpdateReportRepairStatus($Model){

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$evalId = $this->_controller->request->projectvars['VarsArray'][5];
		$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$Options = array('conditions' => array($Model . '.reportnumber_id' => $id));
		$Test = $this->_controller->$Model->find('all',$Options);

		if(count($Test) == 0) return;

		$Results = Hash::extract($Test, '{n}.' . $Model . '.result');
		$Result = array_search(2, $Results);

		if($Result !== false){
			$Save = array('id' => $id, 'result' => 2);
			$this->_controller->Reportnumber->save($Save);
			return;
		}

		$Result = array_search(0, $Results);

		if($Result !== false){
			$Save = array('id' => $id, 'result' => 0);
			$this->_controller->Reportnumber->save($Save);
			return;
		}

		$Save = array('id' => $id, 'result' => 1);
		$this->_controller->Reportnumber->save($Save);
		return;

	}

	public function CheckReportStatusBeforSave($reportnumber){

		// ACHTUNG das FlashMessage Array muss noch angepasst werden
		// ist in disem Branch noch ohne Funktion und Auswirkung

		// Wenn die Prüfberichtsmappe gesperrt ist
	 	if(isset($reportnumber['Reportnumber']['no_rights']) && $reportnumber['Reportnumber']['no_rights'] == 1) {
			$reportnumber['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));

	 		return false;
	 	}

	 	// Wenn die Prüfberichtsmappe gesperrt ist
	 	if($this->writeprotection) {
			$reportnumber['FlashMessages'][]  = array('type' => 'error','message' => __('Report is writeprotected - changes will not be saved.'));
	 		return false;
	 	}

	 	// Wenn der Prüfbericht abgerechnet ist
	 	if($reportnumber['Reportnumber']['settled'] > 0){
			$reportnumber['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is closed!'));
	 		return false;
	 	}

	 	// Wenn der Prüfbericht gelöscht ist
	 	if($reportnumber['Reportnumber']['delete'] > 0){
			$reportnumber['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is marked as deleted!'));
	 		return false;
	 	}

	 	// Wenn der Prüfbericht deaktiviert ist
	 	if($reportnumber['Reportnumber']['deactive'] > 0){
			$reportnumber['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is marked as deactivate!'));
	 		return false;
	 	}

	 	// Wenn der Prüfbericht geschlossen ist
	 	if($reportnumber['Reportnumber']['status'] > 0){

			$reportnumber = $this->_controller->Data->RevisionCheckTime($reportnumber);

			if(!isset($reportnumber['Reportnumber']['revision_write'])){
				$reportnumber['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. The report is closed!'));
	 			return false;
			}
	 	}

		return true;

	}

	public function SetModelNames($reportnumber) {

		if(!isset($reportnumber['Reportnumber'])) return $reportnumber;

	 	$verfahren = $reportnumber['Testingmethod']['value'];
	 	$Verfahren = ucfirst($verfahren);
	 	$this->_controller->request->verfahren = $verfahren;
	 	$this->_controller->request->Verfahren = $Verfahren;
	 	$ReportGenerally = 'Report'.$Verfahren.'Generally';
	 	$ReportSpecific = 'Report'.$Verfahren.'Specific';
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 	$ReportArchiv = 'Report'.$Verfahren.'Archiv';
	 	$ReportSettings = 'Report'.$Verfahren.'Setting';
	 	$ReportPdf = 'Report'.$Verfahren.'Pdf';

		$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation,$ReportArchiv,$ReportSettings,$ReportPdf);
		$this->_controller->request->tablenames = $ReportArray;

		$TableNames = array();

		foreach ($ReportArray as $key => $value) {
			$underscore = Inflector::underscore($value);
			$underscore = explode('_',$underscore);
			$TableNames[Inflector::camelize($underscore[2])] = $value;
		}

		$reportnumber['Tablenames'] = $TableNames;

		return $reportnumber;

	}

	public function GetReportData($data,$Models) {

		$data = $this->__CollectModelsForThisFunction($data,$Models);
		$ReportArray = $data['CurrentTables'];
		$xml = $data['xml'];

		if(count($ReportArray) == 0) return $data;

		foreach ($ReportArray as $key => $value) {

			$this->_controller->loadModel($value);

			$Find = strpos($value, 'Evaluation');

			if($Find === false) $Method = 'first';
			else $Method = 'all';

			switch ($Method) {

				case 'first':
				$options = array('conditions' => array($value.'.reportnumber_id' => $data['Reportnumber']['id']));
				$record = $this->_controller->$value->find('first', $options);
				$data[$value] = $record[$value];
				break;

				case 'all':

				if(isset($this->_controller->request->projectvars['evalId']) && $this->_controller->request->projectvars['evalId'] > 0){
					$ThisMethod = 'first';
				} else {
					$ThisMethod = 'all';
				}

				if($this->_controller->request->params['action'] == 'editevalution' && $this->_controller->request->projectvars['evalId'] == 0){

					$ThisMethod = 'first';
					$data = $this->__AddEmptyDataFields($data);

					return $data;

				}

				$options = array(
			 			'conditions' => array(
							$value.'.reportnumber_id' => $data['Reportnumber']['id'],
							'deleted'=> 0
						),
			 			'order' => array(
			 					$value.'.sorting' => 'asc',
			 					$value.'.description' => 'asc',
			 					$this->_controller->$value->primaryKey=>'asc'
			 			)
			 	);

				if($ThisMethod == 'all'){

					$options = $this->__OverwriteEvaluationOrder($options,$value,'screen');
					$record = $this->_controller->$value->find($ThisMethod, $options);
					$data[$value] = $record;
					$data = $this->__RebuildEvaluations($data,$record,$value,$xml);

				} else {

					$options['conditions'][$value . '.id'] = $this->_controller->request->projectvars['evalId'];

					$record = $this->_controller->$value->find($ThisMethod, $options);

					if(count($record) > 0) $data[$value] = $record[$value];
					else $data[$value] = array();
				}

				break;

				default:
				$options = array('conditions' => array($value.'.reportnumber_id' => $data['Reportnumber']['id']));
				$record = $this->_controller->$value->find('first', $options);
				$data[$value] = $record[$value];
				break;

			}
		}

		return $data;

	}

	protected function __AddEmptyDataFields($data){

		if($this->_controller->request->projectvars['evalId'] > 0) return $data;

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		foreach ($data['xml']['settings']->$ReportEvaluation->children() as $key => $value) {

			if(empty($value->output->screen)) continue;
			if(trim($value->model) != $ReportEvaluation) continue;

			$data[$ReportEvaluation][trim($value->key)] = '';

			if(empty($value->fieldtype)) continue;
			if(trim($value->fieldtype == 'radio')) $data[$ReportEvaluation][trim($value->key)] = 0;

		}

		$data[$ReportEvaluation]['reportnumber_id'] = $data['Reportnumber']['id'];

		return $data;
	}

	public function CollectDataForEvaluationDuplicaten($data) {

		if($this->_controller->request->params['action'] != 'duplicatevalution') return $data;

		if($this->_controller->request->projectvars['VarsArray'][6] == 1) $data = $this->__CollectDataForEvaluationDuplicatenWeld($data);
		if($this->_controller->request->projectvars['VarsArray'][6] == 0) $data = $this->__CollectDataForEvaluationDuplicatenPosition($data);

		return $data;
	}

	protected function __CollectDataForEvaluationDuplicatenWeld($data) {

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$Description = $this->_controller->$ReportEvaluation->find('first', array('conditions' => array($ReportEvaluation . '.id' => $this->_controller->request->projectvars['evalId'])));

		if(count($Description) == 0) return $Data;

		$options = array(
				'conditions' => array(
					$ReportEvaluation.'.reportnumber_id' => $data['Reportnumber']['id'],
					$ReportEvaluation.'.description' => $Description[$ReportEvaluation]['description'],
					'deleted'=> 0
				)
		);

		$record = $this->_controller->$ReportEvaluation->find('all', $options);

		if(count($record) == 0) return $data;

		$data['EvaluationDuplicaten'] = $record;

		return $data;
	}

	protected function __CollectDataForEvaluationDuplicatenPosition($data) {

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$options = array(
				'conditions' => array(
					$ReportEvaluation.'.reportnumber_id' => $data['Reportnumber']['id'],
					$ReportEvaluation.'.id' => $this->_controller->request->projectvars['evalId'],
					'deleted'=> 0
				)
		);

		$record = $this->_controller->$ReportEvaluation->find('all', $options);

		if(count($record) == 0) return $data;

		$data['EvaluationDuplicaten'] = $record;

		return $data;
	}

	public function WeldSorting($data) {

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$welds = array();

		if(isset($data[$ReportEvaluation])) $welds = $this->__WeldSorting($data[$ReportEvaluation]);

		$data['Welds'] = $welds;

		return $data;
	}

	public function DropdownJson($data) {

		// Die Dropdown müssen für Jeditabel vorbereitet werden
	 	$Dropdown = array();

	 	if(isset($data['JSON'])) $Dropdown = $data['JSON'];

		return $Dropdown;
	}

	public function DevelopmentsEnabled($data) {

		if(Configure::check('DevelopmentsEnabled') == false) return $data;
		if(Configure::read('DevelopmentsEnabled') != true) return $data;
		if($data['Order']['id'] == 0) return $data;
		if(count($data['Welds']) == 0) return $data;

		// Wenn in diesem Auftrag ein Progress dargestellt werdn soll
		$ReportEvaluation = $data['Tablenames']['Evaluation'];
		$Welds = $data['Welds'];

		$this->_controller->loadModel('Development');
	 	$this->_controller->loadModel('Order');

	 	$order = $this->_controller->Order->find('first',array('conditions' => array('Order.id' => $data['Order']['id'])));
		$Development = $this->_controller->Development->DevelopmentData->find('first',array('conditions' => array('DevelopmentData.order_id' => $data['Order']['id'])));

		if(count($Development) == 0) return $data;

		$order['Development'] = $Development;

	 foreach($Welds as $_key => $_reportnumbers) {

	 		foreach($_reportnumbers[$ReportEvaluation]['weld'] as $__key => $_welds){

	 			$development = $this->_controller->Development->DevelopmentData->find('first',array('conditions' => array('DevelopmentData.evaluation_id 	' => $_welds['id'])));

				if(count($development) > 0){

	 				$Welds[$_key][$ReportEvaluation]['development'] = $development['DevelopmentData']['result'];
	 				break;

	 			} else {
	 				$Welds[$_key][$ReportEvaluation]['development'] = 0;
	 			}
	 		}
	 	}

		$data['Welds'] = $Welds;

		return $data;

	}

	public function CheckRevisionValues($data) {

		$modelsarray = array($data['Tablenames']['Evaluation']);

		$revisions = array();

		$this->_controller->loadModel('Revision');
	 	$this->_controller->Revision->recursive = -1;

    foreach ($modelsarray as $model => $value) {

    	if(!empty($data['xml']['settings']->$value)) {

      	$ReportSetting = $data['xml']['settings']->$value;

        foreach ($ReportSetting->children() as $field => $fieldvalue) {

          if($fieldvalue->model == $value) {

						$options = array(
							'conditions' => array(
								'Revision.reportnumber_id' => $data['Reportnumber'] ['id'],
								'Revision.order_id' => $data['Reportnumber']['order_id'],
								'Revision.topproject_id' =>$data['Reportnumber']['topproject_id'],
								'Revision.report_id' =>$data['Reportnumber']['report_id'],
								'Revision.model' => $value,
								'Revision.row' => trim($fieldvalue->key)
								)
						);

            if (!empty($tbid)) $options['conditions'] ['Revision.table_id'] = $tbid;

						$revision = $this->_controller->Revision->find('all',$options);

						foreach ($revision as $_rkey => $_rvalue) {

							$r = array(
								'revision' => $_rvalue['Revision']['revision'],
								'last_value' => $_rvalue['Revision']['last_value'],
								'this_value' => $_rvalue['Revision']['this_value'],
								'created' => $_rvalue['Revision']['created'],
								'model' => $_rvalue['Revision']['model'],
								'field' => $_rvalue['Revision']['row'],
								'id' => $_rvalue['Revision']['table_id'],
							);

							if(!empty($fieldvalue->fieldtype)){
								$last_value = intval($r['last_value']);
								$this_value = intval($r['this_value']);
								$r['last_value'] = trim($fieldvalue->radiooption->value[$last_value]);
								$r['this_value'] = trim($fieldvalue->radiooption->value[$this_value]);
							}

							$revisions[$value][$_rvalue['Revision']['table_id']][trim($fieldvalue->key)] = 	$r;
						}
					}
				}
			} else {

				$options = array(
					'conditions' => array(
						'Revision.reportnumber_id' => $data['Reportnumber'] ['id'],
						'Revision.order_id' => $data['Reportnumber']['order_id'],
						'Revision.topproject_id' =>$data['Reportnumber']['topproject_id'],
						'Revision.report_id' =>$data['Reportnumber']['report_id'],
						'Revision.model' => $value,
						)
					);

				if (!empty($tbid)) $options['conditions'] ['Revision.table_id'] = $tbid;

				$revision = $this->_controller->Revision->find('all',$options);

				foreach ($revision as $_rkey => $_rvalue) {
					$revisions [$value] [$_rvalue['Revision']['table_id']] = 1 ;
				}
			}
		}

		$data['RevisionValues'] = $revisions;

		return $data;
  }

	public function GetRepairReport($reportnumber) {

		if(Configure::check('RepairManager') == false) return $reportnumber;
		if(Configure::read('RepairManager') != true) return $reportnumber;

	 	$verfahren = $this->_controller->request->verfahren;
	 	$Verfahren = $this->_controller->request->Verfahren;
		$ReportEvaluation = $reportnumber['Tablenames']['Evaluation'];


		$reportnumber['Repairs']['Statistic']['count'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['progress'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['open'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['success'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['error'] = 0;

		$this->_controller->request->Model = $ReportEvaluation;

		$this->_controller->loadModel('Repair');

		if(!isset($reportnumber[$ReportEvaluation])) return $reportnumber;

		foreach($reportnumber[$ReportEvaluation] as $_key => $_data){

			if($_data[$ReportEvaluation]['deleted'] == 1) continue;

			$optionsRepair = array('conditions' => 
				array(
					'Repair.reportnumber_id' => $reportnumber['Reportnumber']['id'],
					'Repair.evaluation_id_mistake' => $_data[$ReportEvaluation]['id']
				)
			);

			$Repair = $this->_controller->Repair->find('first',$optionsRepair);

			if(count($Repair) == 0 && $_data[$ReportEvaluation]['result'] != 2) continue;

			if(count($Repair) == 0 && $_data[$ReportEvaluation]['result'] == 2){

				++$reportnumber['Repairs']['Statistic']['count'];
				++$reportnumber['Repairs']['Statistic']['status']['open'];
				$reportnumber['Repairs'][$_key]['mistake'] = $_data;
				$reportnumber['Repairs'][$_key]['class'] = 'open';

				continue;
			}

			++$reportnumber['Repairs']['Statistic']['count'];

			$optionsRepairreport = array('conditions' => 
				array(
					'Reportnumber.id' => $Repair['Repair']['reportnumber_id']
				)
			);

			$RepairReport = $this->_controller->Reportnumber->find('first',$optionsRepairreport);

			if(count($RepairReport) == 0) continue;

			if($RepairReport['Reportnumber']['status'] < 2){

				$reportnumber['Repairs'][$_key]['history'] = array();
				$reportnumber['Repairs'][$_key]['mistake'] = $_data;
				$reportnumber['Repairs'][$_key]['class'] = 'progress';


				$optionsEvaluation = array(
										'conditions' => array(
											$ReportEvaluation.'.id' => $Repair['Repair']['evaluation_id'],
											$ReportEvaluation.'.deleted' => 0
											)
										);

				$Evaluation = $this->_controller->$ReportEvaluation->find('first',$optionsEvaluation);

				$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];

				if($Evaluation[$ReportEvaluation]['result'] == 0){
					$repair_class = 'progress';
					$repair_message = __('Repair report created and in progress.',true);
					++$reportnumber['Repairs']['Statistic']['status']['progress'];
				}
				if($Evaluation[$ReportEvaluation]['result'] == 1){
					$repair_class = 'progress';
					$repair_message = __('Repair report created and in progress, the report is not closed.',true);
					++$reportnumber['Repairs']['Statistic']['status']['progress'];
				}
				if($Evaluation[$ReportEvaluation]['result'] == 2){
					$repair_class = 'progress';
					$repair_message = __('Repair report created and in progress, the report is not closed.',true);
					++$reportnumber['Repairs']['Statistic']['status']['progress'];
				}

				$Evaluation[$ReportEvaluation]['class_for_repair_view'] = $repair_class;
				$Evaluation[$ReportEvaluation]['message_for_repair_view'] = $repair_message;

				$reportnumber['Repairs'][$_key]['class'] = $repair_class;
				$reportnumber['Repairs'][$_key]['history'][] = $Evaluation;

				continue;
			}

			$optionsEvaluation = array(
									'conditions' => array(
										$ReportEvaluation.'.id' => $Repair['Repair']['evaluation_id'],
										$ReportEvaluation.'.deleted' => 0
									)
								);

			$Evaluation = $this->_controller->$ReportEvaluation->find('first',$optionsEvaluation);

			if(count($Evaluation) == 0) continue;

			$reportnumber['Repairs'][$_key]['history'] = array();
			$reportnumber['Repairs'][$_key]['mistake'] = $_data;

			if($Evaluation[$ReportEvaluation]['result'] == 0){
				$reportnumber['Repairs'][$_key]['class'] = 'open';
				$repair_message = __('No repair reports created.',true);
				++$reportnumber['Repairs']['Statistic']['status']['open'];
			}
			if($Evaluation[$ReportEvaluation]['result'] == 1){
				$reportnumber['Repairs'][$_key]['class'] = 'success';
				$repair_message = __('Repair completed successfully.',true);
				++$reportnumber['Repairs']['Statistic']['status']['success'];
			}
			if($Evaluation[$ReportEvaluation]['result'] == 2){
				$reportnumber['Repairs'][$_key]['class'] = 'error';
				$repair_message = __('Repair failed.',true);
				++$reportnumber['Repairs']['Statistic']['status']['error'];
			}

			$Evaluation[$ReportEvaluation]['class_for_repair_view'] = $reportnumber['Repairs'][$_key]['class'];
			$Evaluation[$ReportEvaluation]['message_for_repair_view'] = $repair_message;

			$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];

			if($Evaluation[$ReportEvaluation]['result'] == 2){

				$Output['evaluation'] = array();

				$reportnumber = $this->SearchRepairEvaluations($reportnumber,$_key,$Evaluation,$ReportEvaluation,$Output,false);

			} else {
				array_push($reportnumber['Repairs'][$_key]['history'],$Evaluation);
			}
		}

		return $reportnumber;
	}

	public function SearchRepairEvaluations($reportnumber,$_key,$data,$model,$Output,$repairs) {

		if($repairs == false) $optionsRepair = array('conditions' => array('Repair.evaluation_id_mistake' => $data[$model]['id']));
		elseif(is_array($repairs)) $optionsRepair = array('conditions' => array('Repair.evaluation_id_mistake' => $repairs[$model]['id']));

		array_push($Output['evaluation'],$data);

		$Repair = $this->_controller->Repair->find('first',$optionsRepair);

		if(count($Repair) == 0){
			$Output[$model]['class_for_repair_view'] = 'open';
			$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];
			$reportnumber['Repairs'][$_key]['class'] = 'open';
			$Output['class'] = 'open';
			return $reportnumber;
		}

		$optionsRepairreport = array('conditions' => array('Reportnumber.id' => $Repair['Repair']['reportnumber_id']));
		$RepairReport = $this->_controller->Reportnumber->find('first',$optionsRepairreport);

		if($RepairReport['Reportnumber']['status'] < 2){

			$repair_message = __('Repair report created and in progress',true);

			$optionsEvaluation = array(
									'conditions' => array(
										$model.'.id' => $Repair['Repair']['evaluation_id'],
										$model.'.deleted' => 0
										)
									);

			$Evaluation = $this->_controller->$model->find('first',$optionsEvaluation);
			$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];
			$Evaluation[$model]['class_for_repair_view'] = 'progress';
			$Evaluation[$model]['message_for_repair_view'] = $repair_message;

			$Output['class'] = 'progress';
			$reportnumber['Repairs'][$_key]['class'] = 'progress';
			$data[$model]['class_for_repair_view'] = 'progress';
			array_push($Output['evaluation'],$Evaluation);
			$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];
			return $reportnumber;
		}

		if(count($Repair) == 0){

			$reportnumber['Repairs'][$_key]['class'] = 'open';
			$Output['class'] = 'open';
			return $reportnumber;

		} else {

			$optionsEvaluation = array(
									'conditions' => array(
										$model.'.id' => $Repair['Repair']['evaluation_id'],
										$model.'.deleted' => 0
										)
									);

			$Evaluation = $this->_controller->$model->find('first',$optionsEvaluation);

			$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];

			if($Evaluation[$model]['result'] == 0){

				$repair_message = __('Repair report created and in progress.',true);

				$Output['class'] = 'open';
				$reportnumber['Repairs'][$_key]['class'] = 'open';
				$Evaluation[$model]['message_for_repair_view'] = $repair_message;
				$Evaluation[$model]['class_for_repair_view'] = 'open';
				array_push($Output['evaluation'],$Evaluation);
				$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];

				return $reportnumber;

			}
			if($Evaluation[$model]['result'] == 1){

				$repair_message = __('Repair completed successfully.',true);

				$Output['class'] = 'success';
				$reportnumber['Repairs'][$_key]['class'] = 'success';
				$Evaluation[$model]['message_for_repair_view'] = $repair_message;
				$Evaluation[$model]['class_for_repair_view'] = 'success';
				array_push($Output['evaluation'],$Evaluation);
				$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];

				--$reportnumber['Repairs']['Statistic']['status']['error'];
				++$reportnumber['Repairs']['Statistic']['status']['success'];

				return $reportnumber;

			}
			if($Evaluation[$model]['result'] == 2){

				$repair_message = __('Repair failed.',true);

				$Evaluation['class'] = 'error';
				$reportnumber['Repairs'][$_key]['class'] = 'error';
				$Evaluation[$model]['message_for_repair_view'] = $repair_message;
				$Evaluation[$model]['class_for_repair_view'] = 'error';
				$Evaluation[$model]['message_for_repair_view'] = __('Repair failed',true);

				$reportnumber = $this->SearchRepairEvaluations($reportnumber,$_key,$Evaluation,$model,$Output,null);

				return $reportnumber;
			}
		}
	}

	public function MapRepairs($data) {

		if(Configure::check('RepairManager') == false) return $data;
		if(Configure::read('RepairManager') != true) return $data;

		if(!isset($data['Repairs'])) return $reportnumber;

		$Model = $data['Tablenames']['Evaluation'];

		foreach($data['Repairs'] as $_key => $_data){

//			if($_key == 'Statistic') continue;

			foreach($_data as $__key => $__data){
				if($__key == 'history'){
					$LastElement = end($__data);
					$data[$Model][$_key][$Model]['class_for_repair_view'] = $LastElement[$Model]['class_for_repair_view'];
					$data[$Model][$_key][$Model]['RepairReport'] = $LastElement['Reportnumber'];
					unset($LastElement);
				}
			}
		}

		return $data;
	}

	public function GetRepairStatus($data) {

		if(Configure::check('RepairManager') == false) return $data;
		if(Configure::read('RepairManager') != true) return $data;

		foreach($data['Repairs'] as $_key => $_data){
			if(isset($_data['class'])) $ReportRepairStatus[$_data['class']] = $_data['class'];
		}

		if(isset($ReportRepairStatus['error'])){
			$data['Repairs']['Statistic']['ReportRepairStatus'] = 'error';
			return $data;
		}
		if(isset($ReportRepairStatus['open'])){
			$data['Repairs']['Statistic']['ReportRepairStatus'] = 'open';
			return $data;
		}
		if(isset($ReportRepairStatus['progress'])){
			$data['Repairs']['Statistic']['ReportRepairStatus'] = 'progress';
			return $data;
		}
		if(isset($ReportRepairStatus['success'])){
			$data['Repairs']['Statistic']['ReportRepairStatus'] = 'success';
			return $data;
		}

		$data['Repairs']['Statistic']['ReportRepairStatus'] = 'none';

		return $data;

	}

	public function CheckDevelopmentsEnabled($data){

		if(Configure::check('DevelopmentsEnabled') === false) return $data;
		if(Configure::read('DevelopmentsEnabled') === false) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

		$this->_controller->loadModel('OrdersDevelopments');
		$this->_controller->loadModel('Development');
		$this->_controller->loadModel('Order');

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		if(!isset($data[$ReportEvaluation])) return $data;
		if(count($data[$ReportEvaluation]) == 0) return $data;

		$order = $this->_controller->Order->find('first',array('conditions' => array('Order.id' => $orderID)));
		$OrdersDevelopments = $this->_controller->OrdersDevelopments->find('all',array('conditions' => array('OrdersDevelopments.order_id' => $orderID)));

		if(count($OrdersDevelopments) == 0)  return $data;

		$development_evalutions = array();

		$development_evalutions = $this->_controller->$ReportEvaluation->find('list',
				array(
						'conditions' =>
						array(
								'reportnumber_id' => $data[$ReportEvaluation]['reportnumber_id'],
								'description' => $data[$ReportEvaluation]['description']
						),
						'order' => array('id ASC')
				)
				);

		$development = $this->_controller->Development->DevelopmentData->find('first',
				array(
						'conditions' =>
						array(
								'DevelopmentData.evaluation_id 	' => $development_evalutions
						)
				)
				);

		if(count($development) == 2){
			if($development['DevelopmentData']['result'] == 0){
				$data['development']['result'] = 0;
				$data['development']['evaluation_id'] = $development['DevelopmentData']['evaluation_id'];
			}
			if($development['DevelopmentData']['result'] == 1){
				$data['development']['result'] = 1;
				$data['development']['evaluation_id'] = $development['DevelopmentData']['evaluation_id'];
			}
			if($development['DevelopmentData']['result'] == 2){
				$data['development']['result'] = 2;
				$data['development']['evaluation_id'] = $development['DevelopmentData']['evaluation_id'];
			}
		}
		else {
			$data['development']['result'] = 0;
			if(isset($data[$ReportEvaluation])){
				$data['development']['evaluation_id'] = $data[$ReportEvaluation]['id'];
			}
			else {
				$data['development']['evaluation_id'] = 0;
			}
		}

		return $data;

	}

	public function WeldEdit($data,$weldedit) {

		if($weldedit != 1) return $data;
		if($this->_controller->request->projectvars['evalId'] == 0) return $data;
		if(!isset($data['xml'])) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$editid = $this->_controller->request->projectvars['VarsArray'][5];

		$xml = $data['xml'];

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		if(empty($data['xml']['settings']->$ReportEvaluation->position)) return $data;

 		// Sind mehr als 1 Nahtbereich vorhanden?
 		$CountWeld = ($this->_controller->$ReportEvaluation->find('count',array(
 				'conditions' => array(
 						'reportnumber_id' => $data[$ReportEvaluation]['reportnumber_id'],
 						'description' => $data[$ReportEvaluation]['description'],
 						'deleted' => 0
 				)
 		)));

		if($CountWeld == 1) return $data;

 		// Alle zur Naht gehörenden Datensätze holen
 		$evaluations = $this->_controller->$ReportEvaluation->find('all', array('conditions'=>array(
 				$ReportEvaluation.'.description' => $data[$ReportEvaluation]['description'],
 				$ReportEvaluation.'.reportnumber_id' => $data[$ReportEvaluation]['reportnumber_id'],
 				$ReportEvaluation.'.deleted' => 0
 		)));

 		// Datenarray abbauen und dabei gleiche Daten in allen Nähten merken
 		$intersect = array_shift($evaluations);

 		while(!empty($evaluations)) {
 			$_data = array_shift($evaluations);
 			$intersect = array($ReportEvaluation=>array_intersect_assoc($intersect[$ReportEvaluation], $_data[$ReportEvaluation]));
 		}

		$xml_not_in_user = array();

 		// Datenarray mit den gemerkten Feldern wieder aufbauen
 		if(($CountWeld) > 1){
 			foreach($xml['evaluationoutput'] as $key1 => $_arrayData) {
 				if(isset($intersect[$ReportEvaluation][trim($_arrayData->key)])) {
 					$evaluations[$ReportEvaluation][trim($_arrayData->key)] = $intersect[$ReportEvaluation][trim($_arrayData->key)];
 				} else {
					$xml_not_in_user[] = trim($xml['evaluationoutput'][$key1]->model) . Inflector::camelize(trim($xml['evaluationoutput'][$key1]->key));
 					unset($xml['evaluationoutput'][$key1]);
 				}
 			}
 		}

		$evaluations[$ReportEvaluation]['id'] = $editid;
		$evaluations[$ReportEvaluation]['reportnumber_id'] = $id;

		if(count($evaluations) == 0){

			return $data;

		}

		if(!isset($evaluations[$data['Tablenames']['Evaluation']])) return $data;

		$data[$data['Tablenames']['Evaluation']] = $evaluations[$data['Tablenames']['Evaluation']];

		$data['XmlNotInUse'] = $xml_not_in_user;

		return $data;

	}

	protected function __GetWeldPaginationResultsDiscPos($data,$value,$key,$paginationOverview){

		if(!is_array($value)) return $paginationOverview;
		if(count($value) == 0) return $paginationOverview;

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		if(!isset($data[$ReportEvaluation]['position'])) return $paginationOverview;

		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$Output = array();

		$optionsSortingOverview = array(
				'fields' => array('id','result'),
				'conditions' => array(
						$ReportEvaluation.'.reportnumber_id' => $id,
						'deleted' => 0
				),
		);

		$optionsSortingOverview['fields'] = array('id','result');

		foreach ($value as $_key => $_value) {

			$optionsSortingOverview['conditions'][$ReportEvaluation . '.description'] = $key;
			$optionsSortingOverview['conditions'][$ReportEvaluation . '.position'] = $_value;

			$Result = $this->_controller->$ReportEvaluation->find('list',$optionsSortingOverview);

			if(count($Result) != 1) continue;

			$paginationOverview['position'][$key][key($Result)] = $Result[key($Result)];

			unset($optionsSortingOverview['conditions'][$ReportEvaluation . '.description']);

			unset($optionsSortingOverview['conditions'][$ReportEvaluation . '.position']);

		}

		if (isset($paginationOverview['position']) && !empty($paginationOverview['position'][$key])) {
			if(array_key_exists($key, $paginationOverview['position'])){
				if(array_search(2, $paginationOverview['position'][$key]) !== false){
					$paginationOverview['discription'][$key] = 2;
					return $paginationOverview;
				}
				if(array_search(0, $paginationOverview['position'][$key]) !== false){
					$paginationOverview['discription'][$key] = 0;
					return $paginationOverview;
				}
				if(array_search(1, $paginationOverview['position'][$key]) !== false){
					$paginationOverview['discription'][$key] = 1;
				}
			}
		}
		return $paginationOverview;
	}

	protected function __GetWeldPaginationResultsDisc($data,$value,$key,$paginationOverview){

		$ReportEvaluation = $data['Tablenames']['Evaluation'];

		$id = $this->_controller->request->projectvars['VarsArray'][4];
		$Output = array();

		$optionsSortingOverview = array(
				'fields' => array('id','result'),
				'conditions' => array(
					$ReportEvaluation.'.reportnumber_id' => $id,
					$ReportEvaluation.'.id' => $value,
					$ReportEvaluation.'.deleted' => 0
				),
		);

		$optionsSortingOverview['fields'] = array('id','result');

		$Result = $this->_controller->$ReportEvaluation->find('list',$optionsSortingOverview);

		if(count($Result) != 1) return $paginationOverview;

		$paginationOverview['position'][$key][key($Result)] = $Result[key($Result)];

		if(array_search(2, $paginationOverview['position'][$key]) !== false){
			$paginationOverview['discription'][$key] = 2;
			return $paginationOverview;
		}
		if(array_search(0, $paginationOverview['position'][$key]) !== false){
			$paginationOverview['discription'][$key] = 0;
			return $paginationOverview;
		}
		if(array_search(1, $paginationOverview['position'][$key]) !== false){
			$paginationOverview['discription'][$key] = 1;
		}

		return $paginationOverview;

	}

	protected function __GetWeldPaginationResultsUrl($key,$weld){

		$url = Router::url(
			array_merge(
				array(
					'controller' => $this->_controller->request->params['controller'],
					'action' => 'savejson',
				),
				$this->_controller->request->projectvars['VarsArray']
			)
		);

		$PositionUrlArray = explode('/',$url);
		$PositionUrlArray[2] = 'editevalution';
		$PositionUrlArray[8] = $key;
		$PositionUrlArray[9] = $weld;
		$PositionUrlArray[11] = 0;
		$PositionUrl = implode('/',$PositionUrlArray);

		return $PositionUrl;

	}

	public function GetWeldPagination($data,$weldedit) {

		$ReportEvaluation = $data['Tablenames']['Evaluation'];
		$lang = $this->_controller->request->lang;

		if(!isset($data['xml']['settings']->$ReportEvaluation->position) && isset($data[$ReportEvaluation]['position'])) unset($data[$ReportEvaluation]['position']);


		// Die zugehörigen Prüfergebnisse werden aus der Datenbank geholt
	 	$pagination_links = array();

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

	 	if($weldedit == 0){
	 		$optionsSorting = array(
	 				'conditions' => array($ReportEvaluation.'.reportnumber_id' => $id, 'deleted'=>0),
	 				'order' => array(
	 						$ReportEvaluation.'.sorting' => 'asc',
	 						$ReportEvaluation.'.description' => 'asc',
	 						$this->_controller->$ReportEvaluation->primaryKey=>'asc'
	 				)
	 		);

	 		$pagination_links['next']['desc'] = __('Next testing area',true);
	 		$pagination_links['prev']['desc'] = __('Previous test area',true);
	 	}

	 	if($weldedit == 1){
	 		$optionsSorting = array(
	 				'fields' => array('id','description'),
	 				'conditions' => array(
	 						$ReportEvaluation.'.reportnumber_id' => $id,
	 						'deleted'=>0
	 				),
	 				'order' => array(
	 						$ReportEvaluation.'.sorting' => 'asc',
	 						$ReportEvaluation.'.description' => 'asc',
	 						$this->_controller->$ReportEvaluation->primaryKey=>'asc'
	 				)
	 		);

	 		$pagination_links['next']['desc'] = __('Next weld',true);
	 		$pagination_links['prev']['desc'] = __('Previous weld',true);
	 	}

	 	$pagination_links['next']['id'] = 0;
	 	$pagination_links['prev']['id'] = 0;

	 	$pagination = $this->_controller->$ReportEvaluation->find('list',$optionsSorting);

		natsort($pagination);

	 	if(isset($data[$ReportEvaluation]['position'])){

			$hasPositionColum = true;

	 		$this->_controller->set('hasPositionColum', true);

	 		$optionsSortingOverview = array(
	 				'fields' => array('id','position','description'),
	 				'conditions' => array(
	 						$ReportEvaluation.'.reportnumber_id' => $id,
	 						'deleted'=>0
	 				),
	 				'order' => array(
	 						$ReportEvaluation.'.sorting' => 'asc',
	 						$ReportEvaluation.'.description' => 'asc',
//	 						$ReportEvaluation.'.position' => 'asc',
	 						$this->_controller->$ReportEvaluation->primaryKey=>'asc'
	 				)
	 		);

	 	} else {

			$hasPositionColum = false;

	 		$this->_controller->set('hasPositionColum', false);

	 		$optionsSortingOverview = array(
	 				'fields' => array('description','id'),
	 				'conditions' => array(
	 						$ReportEvaluation.'.reportnumber_id' => $id,
	 						'deleted'=>0
	 				),
	 				'order' => array(
	 						$ReportEvaluation.'.sorting' => 'asc',
	 						$ReportEvaluation.'.description' => 'asc',
	 						$this->_controller->$ReportEvaluation->primaryKey=>'asc'
	 				)
	 		);
	 	}

		$pagination = array_unique($pagination);

		$evaluation = $this->_controller->$ReportEvaluation->find('list',$optionsSortingOverview);

		if($weldedit == 1){

			$evaluation_natsort = array();

			foreach ($pagination as $key => $value) {
				$evaluation_natsort[$value] = $evaluation[$value];
			}

			$evaluation = $evaluation_natsort;

		} else {

			$natsort = array_keys($evaluation);
			$evaluation_natsort = array();

			natsort($natsort);

			foreach ($natsort as $key => $value) {
				$evaluation_natsort[$value] = $evaluation[$value];
			}

			$evaluation = $evaluation_natsort;

			unset($natsort);
			unset($evaluation_natsort);

		}

		if(count($evaluation) == 0){

			$paginationOverview = array();
			if(isset($data[$ReportEvaluation]['position'])) $paginationOverview['results']['position'][0] = array(0 => 0);
			$paginationOverview['results']['discription'][0] = 0;
			if(isset($data[$ReportEvaluation]['position'])) $paginationOverview['real_struktur'][0] = array(0 => $data[$ReportEvaluation]['position']);
			$paginationOverview['current_weld'][0] = 0;
			if(isset($data[$ReportEvaluation]['position'])) $paginationOverview['current_position'][0] = $data[$ReportEvaluation]['position'];
			$paginationOverview['current_weld_edit_status'] = 0;
			$paginationOverview['current_id'] = 0;
			$paginationOverview['weld_struktur_id'][0] = 0;

			$current_weld_description  = trim($data['xml']['settings']->$ReportEvaluation->description->discription->$lang) . ': ' . 0 . ' ';

			if($paginationOverview['current_weld_edit_status'] == 1) $current_weld_description .= __('Click to open the test sections mode.');
			else $current_weld_description .= '. ' . __('Click to open the complete test area mode.');

			$paginationOverview['weld_tooltip'][0] = $current_weld_description;

			if(isset($data[$ReportEvaluation]['position'])){

				$current_position_tooltip  = trim($data['xml']['settings']->$ReportEvaluation->position->discription->$lang) . ': ' . $data[$ReportEvaluation]['position'] . '. ';
				$paginationOverview['position_tooltip'][0] = $current_weld_description . '/ ' . $current_position_tooltip;

			}

			if($paginationOverview['current_weld_edit_status'] == 1) $paginationOverview['section_mode'] = __('Complete test area mode',true);
			else $paginationOverview['section_mode'] = __('Test section mode',true);

			$paginationOverview['dropdownmenue'] = array();

			$data['paginationOverview'] = $paginationOverview;

			return $data;
		}

		$paginationOverview = array();

		unset($optionsSortingOverview['fields']);
		unset($optionsSortingOverview['order']);

		$optionsSortingOverview['fields'] = array('id','result');

		$paginationOverview['results'] = array();

		foreach ($evaluation as $key => $value) {

			$paginationOverview['results']= $this->__GetWeldPaginationResultsDiscPos($data,$value,$key,$paginationOverview['results']);
			$paginationOverview['results']= $this->__GetWeldPaginationResultsDisc($data,$value,$key,$paginationOverview['results']);

		}

		$paginationOverview['real_struktur'] = $evaluation;

		if(isset($data[$ReportEvaluation]['position'])){

	 		if(isset($data[$ReportEvaluation]['id'])){
				$paginationOverview['current_weld'][$data[$ReportEvaluation]['description']] = $data[$ReportEvaluation]['id'];
				$paginationOverview['current_position'][$data[$ReportEvaluation]['id']] = $data[$ReportEvaluation]['position'];
			} else {
				$paginationOverview['current_weld'][$data[$ReportEvaluation]['description']] = 0;
				$paginationOverview['current_position'][0] = $data[$ReportEvaluation]['position'];
			}

	 		$prev_array = array();
	 		$next_array = array();

	 		foreach($paginationOverview['real_struktur'] as $_key => $_paginationOverview){

	 			if(is_array($_paginationOverview)){
	 				$_positions = $_paginationOverview;
	 				$position_ = array_splice($_paginationOverview,1);
	 				$paginationOverview['weld_struktur'][$_key] = $_paginationOverview[key($_paginationOverview)];
	 				$paginationOverview['weld_struktur_id'][$_key] = key($paginationOverview['real_struktur'][$_key]);
	 				$paginationOverview['paging_weld'][$paginationOverview['weld_struktur_id'][$_key]]['current_weld'] = array($paginationOverview['weld_struktur_id'][$_key] => $_key);
	 				if(isset($_prevkey)){
	 					$paginationOverview['paging_weld'][$paginationOverview['weld_struktur_id'][$_key]]['prev_weld'] = $prev_array;
	 					$paginationOverview['paging_weld'][$_key_for_next]['next_weld'] = array($paginationOverview['weld_struktur_id'][$_key] => $_key);
	 				}
	 				$prev_array = array($paginationOverview['weld_struktur_id'][$_key] => $_key);
	 				foreach($_positions as $__key => $__paginationOverview){

	 					$paginationOverview['position_struktur'][$__key] = $__paginationOverview;
	 					$paginationOverview['position_struktur_id'][] = $__key;
	 					$paginationOverview['position_struktur_weld'][$__key] = array($_key,$__paginationOverview);
	 					$paginationOverview['paging_position'][$__key]['current_position'] = array($__key => $__paginationOverview);

	 					if(isset($_prevvalue_position))$paginationOverview['paging_position'][$__key]['prev_position'] = array($_prevkey_position => $_prevvalue_position);
	 					if(isset($_prevvalue_position))$paginationOverview['paging_position'][$_prevkey_position]['next_position'] = array($__key => $__paginationOverview);
	 					$_prevvalue_position = $__paginationOverview;
	 					$_prevkey_position = $__key;
	 				}
	 			}

	 			$_key_for_next = $paginationOverview['weld_struktur_id'][$_key];
	 			$_prevvalue = $paginationOverview['weld_struktur_id'][$_key];
	 			$_prevkey = $_key;
	 		}
	 	}

		elseif(isset($data[$ReportEvaluation]['description']) && !isset($data[$ReportEvaluation]['position'])){

			if(isset($data[$ReportEvaluation])){
	 			$paginationOverview['current_weld'][$data[$ReportEvaluation]['description']] = isset($data[$ReportEvaluation]['id']) && !empty($data[$ReportEvaluation]['id']) ? $data[$ReportEvaluation]['id'] : '';
			}

			$prev_array = array();
	 		$next_array = array();
	 		$paginationOverview['weld_struktur_id'] = array_flip($paginationOverview['real_struktur']);
	//		$paginationOverview['weld_struktur_id'][$_key] = key($paginationOverview['real_struktur'][$_key]);
	 		$paginationOverview['paging_weld'] = array();

	 		foreach($paginationOverview['real_struktur'] as $_key => $_paginationOverview){
	 			$paginationOverview['paging_weld'][$_paginationOverview]['current_weld'] = array($_paginationOverview => $_key);
	 			if(isset($_prevkey))$paginationOverview['paging_weld'][$_paginationOverview]['prev_weld'] = array($_prevvalue => $_prevkey);
	 			if(isset($_prevkey))$paginationOverview['paging_weld'][$_prevvalue]['next_weld'] = array($_paginationOverview => $_key);
	 			$_prevvalue = $_paginationOverview;
	 			$_prevkey = $_key;

	 		}
	 	}

		if(isset($data[$ReportEvaluation]['id'])) $paginationOverview['current_id'] = $data[$ReportEvaluation]['id'];
		else $paginationOverview['current_id'] = 0;

		if(isset($data[$ReportEvaluation]['description'])){

			$paginationOverview['current_weld_description'] = $data[$ReportEvaluation]['description'];
			$paginationOverview['current_weld_description_bread'] = __('Edit Evalution', true) . ' ' . $data[$ReportEvaluation]['description'];
			if(isset($data[$ReportEvaluation]['position'])) $paginationOverview['current_positon_description'] = $data[$ReportEvaluation]['position'];
			$paginationOverview['current_result'] = $data[$ReportEvaluation]['result'];
			$paginationOverview['current_weld_edit_status'] = $this->_controller->request->projectvars['VarsArray'][6];

			if($paginationOverview['current_weld_edit_status'] == 1) $paginationOverview['section_mode'] = __('Complete test area mode',true);
			else $paginationOverview['section_mode'] = __('Test section mode',true);

			$current_weld_description  = trim($data['xml']['settings']->$ReportEvaluation->description->discription->$lang) . ': ' . $data[$ReportEvaluation]['description'] . ' ';

			$paginationOverview['current_function_tooltip'] = $current_weld_description;

			if($paginationOverview['current_weld_edit_status'] == 0 && !empty($data['xml']['settings']->$ReportEvaluation->position->discription->$lang) && isset($data[$ReportEvaluation]['position'])){

				$current_position_tooltip  = trim($data['xml']['settings']->$ReportEvaluation->position->discription->$lang) . ': ' . $data[$ReportEvaluation]['position'] . '. ';

				$paginationOverview['current_position_tooltip'] = $current_weld_description . '/ ' . $current_position_tooltip;

			}

			if($paginationOverview['current_weld_edit_status'] == 1) $current_weld_description .= __('Click to open the test sections mode.');
			else $current_weld_description .= '. ' . __('Click to open the complete test area mode.');

			$paginationOverview['current_weld_tooltip'] = $current_weld_description;

		}

		foreach ($paginationOverview['real_struktur'] as $key => $value) {

			$current_weld_description  = trim($data['xml']['settings']->$ReportEvaluation->description->discription->$lang) . ': ' . $key . ' ';

			if($paginationOverview['current_weld_edit_status'] == 1) $current_weld_description .= __('Click to open the test sections mode.');
			else $current_weld_description .= '. ' . __('Click to open the complete test area mode.');

			$paginationOverview['weld_tooltip'][$key] = $current_weld_description;


			if(!is_array($value)){

				$paginationOverview['position_url'][$value] = $this->__GetWeldPaginationResultsUrl($value,1);
				continue;

			}

			foreach ($value as $_key => $_value) {

				$paginationOverview['position_url'][$_key] = $this->__GetWeldPaginationResultsUrl($_key,0);
//				$paginationOverview['position_tooltip'][$_key] = $current_weld_description;
				if(isset($data['xml']['settings']->$ReportEvaluation->position)) $current_position_tooltip  = trim($data['xml']['settings']->$ReportEvaluation->position->discription->$lang) . ': ' . $_value . '. ';

				if(isset($current_position_tooltip)) $paginationOverview['position_tooltip'][$_key] = $current_weld_description . '/ ' . $current_position_tooltip;

			}
		}

		if($weldedit == 1 && $this->_controller->request->projectvars['VarsArray'][5] == 0){
/*
			$paginationOverview['real_struktur'][0] = array(0 => '');
			$paginationOverview['results']['discription'][0] = 0;
			$paginationOverview['weld_struktur_id'][0] = 0;
			$paginationOverview['weld_tooltip'][0] = __('New entry',true);
*/
		}

		if(!isset($data[$ReportEvaluation]['position'])){

			$paginationOverview['dropdownmenue'] = array_flip($paginationOverview['real_struktur']);
			$paginationOverview['weld_struktur_id'] = array_flip($paginationOverview['weld_struktur_id']);
			$paginationOverview['has_no_position'] = 1;

			$data['paginationOverview'] = $paginationOverview;

			return $data;
		}

		$paginationOverview['dropdownmenue'] = array();

		$i = 1;

		foreach ($paginationOverview['real_struktur'] as $key => $value) {

			$paginationOverview['dropdownmenue'][$i] = array($key => $key);
			$paginationOverview['dropdownmenue'][$i] = array_replace($paginationOverview['dropdownmenue'][$i],$value);

			$i++;

		}

		$data['paginationOverview'] = $paginationOverview;

		return $data;
	}

	public function DropdownData($data,$xml) {

		$verfahren = $this->_controller->request->verfahren;
		$Verfahren = $this->_controller->request->Verfahren;

		$this->_controller->loadModel('Dropdown');
		$this->_controller->loadModel('DropdownsValue');
		$this->_controller->loadModel('DropdownsMaster');
		$this->_controller->loadModel('DropdownsMastersData');

		$this->_controller->loadModel('DropdownsMastersReport');
		$this->_controller->loadModel('DropdownsMastersTestingcomp');
		$this->_controller->loadModel('DropdownsMastersTestingmethod');
		$this->_controller->loadModel('DropdownsMastersTopproject');

		$this->_controller->Dropdown->recursive = -1;
		$this->_controller->DropdownsValue->recursive = -1;
		$this->_controller->DropdownsMaster->recursive = -1;
		$this->_controller->DropdownsMastersData->recursive = -1;

		if($this->_controller->Auth->user('Roll.id') == 1){
			$this->_controller->loadModel('Testingcomp');
			$this->_controller->Testingcomp->recursive = -1;
			$ProjectMember = $this->_controller->Autorisierung->ProjectMember($this->_controller->request->projectvars['VarsArray'][0]);
			$testingcomps = $this->_controller->Testingcomp->find('all',array('conditions' => array('Testingcomp.id' => $ProjectMember,'Testingcomp.roll_id >' => 4)));
		}

		$data['CustomDropdownFields'] = array();

		foreach($data['TablesInUse'] as $key => $value){

			foreach($xml['settings']->$value as $settings){
				foreach($settings as $_settings){

					$Model = trim($_settings->model);
					$Field = trim($_settings->key);

					$Output = $this->__CollectDropDownData($data,$_settings);

					if(!empty($_settings->select->fields)) $Output = $this->__CollectDropDownDataFromField($data,$_settings);

					if(is_array($Output)) $data['Dropdowns'][$Model][$Field] = $Output;

				}
			}
		}

		return $data;
	}

	protected function _CheckForMasterDropdown($xml){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

		$Areas = array('TestingArea' => 'evaluation_area','EditSpecify' => 'specific_area','EditGeneral' => 'generally_area');

		$Model = trim($xml->model);
		$Field = trim($xml->key);

		$Area = $this->_controller->Session->read('editmenue');

		if($Area == null) return false;
		if(empty($Area)) return false;
		if(!is_array($Area)) return false;
		if(!isset($Areas[$Area[$id]])) return false;

		$Options = array(
			'conditions' => array(
			'DropdownsMaster.field' => $Field,
			'DropdownsMaster.modul' => $Areas[$Area[$id]],
			'DropdownsMaster.deleted' => 0
			)
		);

		$DropdownsMaster = $this->_controller->DropdownsMaster->find('first',$Options);

		if(count($DropdownsMaster) == 0) return false;

		// hier muss es mit den Überprüfungen weitergehen

	}

	protected function __CollectDropDownDataFromField($data,$xml){

		if(empty($xml->select->model)) return null;
		if(isset($xml->fieldtype) && trim($xml->fieldtype) == 'checkbox') return null;
		if(isset($xml->fieldtype) && trim($xml->fieldtype) == 'radio') return null;
		if(empty($xml->select->fields)) return null;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$editid = $this->_controller->request->projectvars['VarsArray'][5];
		$Model = trim($xml->select->model);

		$model = explode('_',Inflector::underscore($Model));
		$Part = Inflector::camelize($model[2]);

		$Out = $this->GetReportData($data,array($Part));
		$Model = $Out['TablesInUse'][$Part];

		if(!isset($Out[$Model])) return null;
		if(count($Out[$Model]) == 0) return null;

		$Output = array();

		foreach ($xml->select->fields->children() as $key => $value) {

			$Field = trim($value);
			if(isset($Out[$Model][$Field])) $Output[$Out[$Model][$Field]] = $Out[$Model][$Field];

		}

		if(count($Output) > 0) return $Output;
		else return null;

	}

	protected function __CollectDropDownData($data,$xml){

		if(empty($xml->select->model)) return null;
		if(isset($xml->fieldtype) && trim($xml->fieldtype) == 'checkbox') return null;
		if(isset($xml->fieldtype) && trim($xml->fieldtype) == 'radio') return null;
		if(!empty($xml->select->model->fields)) return null;


//		if(array_search(strtolower(trim($xml->select->custom_field)), array('1','yes')) !== false) return null;
		if(count($values = $xml->xpath('select/static/value'))) return null;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

		$Check = $this->_CheckForMasterDropdown($xml);

		if(is_array($Check)) return $Check;

		$Model = trim($xml->model);
		$Field = trim($xml->key);
		$Lang = trim($this->_controller->Lang->Discription());

		$Data = array();

		$Options = array(
			'conditions' => array(
				'Dropdown.report_id' => $data['Reportnumber']['report_id'],
				'Dropdown.testingmethod_id' => $data['Reportnumber']['testingmethod_id'],
				'Dropdown.field' => $Field,
				'Dropdown.model' => $Model
			)
		);

		if(AuthComponent::user('Testingcomp.roll_id') > 4){
			$TestingCompOptionDropdownsValue['DropdownsValue.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
			$GlobalOptionDropdownsValue['DropdownsValue.global'] = 1;
			$Options['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
		} else {
			$TestingCompOptionDropdownsValue = null;
			$GlobalOptionDropdownsValue = null;
			$Options['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
		}

		$Dropdown = $this->_controller->Dropdown->find('first',$Options);

		if(count($Dropdown) == 0) return null;

		$Options = array(
			'fields' => array(
				'id','discription'
			),
			'conditions' => array(
				'DropdownsValue.dropdown_id' => $Dropdown['Dropdown']['id'],
				'DropdownsValue.global' => NULL,
				$TestingCompOptionDropdownsValue,
				'DropdownsValue.discription !=' => ''
			)
		);

		$DropdownsValue = $this->_controller->DropdownsValue->find('list',$Options);

		$options = array(
			'fields' => array(
				'id','discription'
			),
			'conditions' => array(
				'DropdownsValue.model' => $Model,
				'DropdownsValue.field' => $Field,
				'DropdownsValue.discription !=' => '',
				'DropdownsValue.global' => 1,
				'DropdownsValue.report_id' => $data['Reportnumber']['report_id'],
				)
			);

		$DropdownsValueGlobal = $this->_controller->DropdownsValue->find('list',$options);

		$DropdownsDiff = array_diff($DropdownsValueGlobal,$DropdownsValue);

		foreach ($DropdownsDiff as $key => $value) {
			if(!isset($DropdownsValue[$key])) $DropdownsValue[$key] = $value;
		}

		natsort($DropdownsValue);

		return $DropdownsValue;

	}

	protected function __RebuildEvaluations($data,$record,$value,$xml) {

		unset($data[$value]);

		// Das Array mit den Prüfergebnissen wird umgemodelt und in das Reportsarray integriert
		foreach($record as $_Evaluation){
			foreach($_Evaluation as $key => $__Evaluation){
				if(isset($xml['settings']->$value->result->radiooption) && isset($__Evaluation['result']) && !isset($data['has_result'])){
					$data['has_result'] = 1;
				}
				if(isset($xml['settings']->$value->result->radiooption) && isset($__Evaluation['error']) && !isset($data['has_error'])){
					$data['has_error'] = 1;
				}

				$data[$value][][$value] = $__Evaluation;
			}
		}

		return $data;

	}

	protected function __OverwriteEvaluationOrder($data,$ReportEvaluation,$type) {

		if(Configure::check('EvaluationOrder') === false) return $data;

		$EvaluationOrder = Configure::read('EvaluationOrder');
		$direction = 'asc';

		if(!isset($EvaluationOrder[$type])) return $data;
		if(!is_array($EvaluationOrder[$type])) return $data;
		if(!isset($EvaluationOrder[$type]['fields'])) return $data;
		if(!is_array($EvaluationOrder[$type]['fields'])) return $data;
		if(count($EvaluationOrder[$type]['fields']) == 0) return $data;

		unset($data['order']);

		$data['order'][$ReportEvaluation . '.sorting'] = 'asc';

		foreach ($EvaluationOrder[$type]['fields'] as $key => $value) {
			if(isset($EvaluationOrder[$type]['direction'][$key])) $data['order'][$ReportEvaluation . '.' . trim($value)] = trim($EvaluationOrder[$type]['direction'][$key]);
			else $data['order'][$ReportEvaluation . '.' . trim($value)] = $direction;
		}

		return $data;
	}

	protected function __CollectModelsForThisFunction($reportnumber,$Models){

		foreach ($Models as $key => $value) {
			 if(isset($reportnumber['Tablenames'][$value])){

				 $reportnumber['CurrentTable'] = $reportnumber['Tablenames'][$value];
				 $reportnumber['CurrentTables'][] = $reportnumber['Tablenames'][$value];
				 $reportnumber['TablesInUse'][$value] = $reportnumber['Tablenames'][$value];

			 }
		}

		return $reportnumber;
	}

	protected function __WeldSorting($data, $modus = null) {

		if(!isset($data)) return array();
		if(count($data) == 0) return array();

		$datasort = Hash::extract($data,'{n}.{s}[sorting>0]');

		//var_dump($datasort);
		// Daten für den Auswertungsberich vorbereiten
		// damit die Schleife eine neue Naht erkennt
		// wird ein Array mit den Nahtbezeichnungen erstellt

		$weldDiscription = array();
		$welds = array();

		foreach($data as $dataWeld){
			$weldDiscription[] = $dataWeld[key($dataWeld)]['description'];
			$Model = key($dataWeld);
		}
		// doppelte Werte werden entfernt und es wird neu sortiert
		$weldDisc = array_unique($weldDiscription);
		ksort($weldDisc);

		foreach($weldDisc as $key => $_weldDisc){

			$welds[$key][$Model]['discription'] = $_weldDisc;

			foreach($data as $_data){
				if($_data[$Model]['description'] == $_weldDisc){

					$welds[$key][$Model]['id'] = $_data[$Model]['id'];

					if (Configure::check('SortSheetNumber') && Configure::read('SortSheetNumber') == true) {
	         	isset( $_data[$Model]['sheet_no'])&& !empty($_data[$Model]['sheet_no'])?$welds[$key][$Model]['sheet_no'] =  $_data[$Model]['sheet_no']:'';
					}

					$welds[$key][$Model]['reportnumber_id'] = $_data[$Model]['reportnumber_id'];
				 	$welds[$key][$Model]['weld'][] = $_data[$Model];
				}
			}
		}

		if(!empty($datasort)){

			if(!empty($modus) && $modus == 'archiv') {

				$returnarray = array();
				$weldsarchiv = Hash::extract($welds,'{n}.{s}.weld');

				foreach ($weldsarchiv as $wakey => $wavalue) {

					$returnarray = array_merge($returnarray,$wavalue);

				}

				return $returnarray;

			} else {

				return $welds;
			}

		} else {

		//Standart nach Naht und Bereich sorieren, ich denke das kann fest so bleiben da dies eigentlich immer die Schlüsselfelder sind im Auswertungsbereich
		$welds =	Hash::sort($welds, '{n}.{s}.discription', 'asc','natural');

		foreach ($welds as $weldkey => $weldvalue) {

			$wa = Hash::extract($weldvalue,'{s}.weld');
			$wa = $wa[0];
			$check = Hash::check($wa, '{n}.position');

			if($check == true) {

				$neworder = 	Hash::sort($wa, '{n}.position', 'asc','natural');
				$welds[$weldkey][$Model] ['weld'] = $neworder;

				}
			}
		}


		//var_dump($welds); die();
		// Blattnummer sortieren, dass habe ich extra gelassen um fehler und Konfigurationsaufwand zu minimieren
	  if (Configure::check('SortSheetNumber') && Configure::read('SortSheetNumber') == true) {

			foreach ($welds as $wk => $wv){

				if(!isset($wv[$Model]['sheet_no'])) return $welds;

				if (!empty($wv[$Model]['sheet_no'])) {

					$weldsheets[$wv[$Model]['sheet_no']][]= $wv;

				} else {

					return $welds;

				}
			}

			ksort($weldsheets);

			foreach ($weldsheets as $wsk => $wsv){
				foreach ($wsv as $_wsvk => $_wsvv) {
					$weldsnew[] =  $_wsvv;
				}
			}

			if(!empty($weldsnew)){

				$welds = array();
				$welds = $weldsnew;

			}
		}

		//Wenn die PDF gedruckt werden soll oder archiviert wird->Daten dafür aufbereiten im geerigenten Fortmat
		if(!empty($modus) && $modus == 'archiv') {

			$returnarray = array();
			$weldsarchiv = Hash::extract($welds,'{n}.{s}.weld');

			foreach ($weldsarchiv as $wakey => $wavalue) {

				$returnarray = array_merge($returnarray,$wavalue);

			}

			return $welds;

		}

		return $welds;

	}

	public function EnforceAssignedReports(){

		$id = $this->_controller->Sicherheit->numeric($this->_controller->request->reportnumberID);
	 	$this->_controller->Autorisierung->IsThisMyReport($id);

	 	$this->_controller->Reportnumber->recursive = -1;
	 	$reportnumber = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $id)));

		$this->_EnforceReports($reportnumber);
		$this->_DeforceReports($reportnumber);

		$FormName['controller'] = 'reportnumbers';
		$FormName['action'] = 'edit';
		$FormName['terms'] = implode('/', $this->_controller->request->projectvars['VarsArray']);
		$this->_controller->set('FormName', $FormName);
		$this->_controller->set('reportnumbers', $reportnumber);

	}

	public function SortAssignedReports($reportnumber){

		if(!isset($this->_controller->request->data['sort'])) return;
		if(empty($this->_controller->request->data['sort'])) return;

		$this->AssignedReportDeleteSubindex($reportnumber['Reportnumber']['id']);

		$Update = array();
		$subindex = 0;

		foreach($this->_controller->request->data['sort'] as $key => $value){

			if($value['id'] == 0) continue;

			$Update = array('id' => $value['id'],'subindex' => $subindex++);

			$this->_controller->Reportnumber->save($Update);

		}

		$this->_controller->Flash->success(__('Sort successful',true), array('key' => 'success'));

	}

	public function AssignedReportRewriteSubindex($id){

		$AssignedReportIds = $this->_controller->Reportnumber->find('list', array('conditions'=>array('Reportnumber.parent_id' => $id)));

		// Index zurückstellen zur Sicherheit
		$Test = $this->_controller->Reportnumber->updateAll(
		    array('Reportnumber.subindex' => 0),
		    array('Reportnumber.id' => $AssignedReportIds)
		);

		$Subindex = 0;

		foreach ($AssignedReportIds as $key => $value) {

			$Update = array(
		 		'id' => $value,
		 		'subindex' => $Subindex++
		 	);

			$Test = $this->_controller->Reportnumber->save($Update);

		}

	}

	public function AssignedReportDeleteSubindex($id){

		$AssignedReportIds = $this->_controller->Reportnumber->find('list', array('conditions'=>array('Reportnumber.parent_id' => $id)));

		// Index zurückstellen zur Sicherheit
		$Test = $this->_controller->Reportnumber->updateAll(
		    array('Reportnumber.subindex' => 0),
		    array('Reportnumber.id' => $AssignedReportIds)
		);

	}

	protected function _EnforceReports($reportnumber){

		if($reportnumber['Reportnumber']['enforce_number'] == 1) return;

		$id = $this->_controller->Sicherheit->numeric($this->_controller->request->reportnumberID);

		$this->AssignedReportRewriteSubindex($id);

		$Update = array(
			'id' => $id,
			'enforce_number' => 1
		);

		$Test = $this->_controller->Reportnumber->save($Update);

		if($Test != false) $this->_controller->Flash->success(__('subordinate reports show parent reportnumber',true), array('key' => 'success'));
		elseif($Test === false) $this->_controller->Flash->error(__('An error has occurredr',true), array('key' => 'error'));

	}

	protected function _DeforceReports($reportnumber){

		if($reportnumber['Reportnumber']['enforce_number'] == 0) return;

		$id = $this->_controller->Sicherheit->numeric($this->_controller->request->reportnumberID);

		$this->AssignedReportDeleteSubindex($id);

		$Update = array(
			'id' => $id,
			'enforce_number' => 0
		);

		$Test = $this->_controller->Reportnumber->save($Update);

		if($Test != false) $this->_controller->Flash->success(__('subordinate reports show original reportnumber',true), array('key' => 'success'));
		elseif($Test === false) $this->_controller->Flash->error(__('An error has occurredr',true), array('key' => 'error'));

	}

	public function MasterAssignedReport($reportnumber){

		$this->_saveAssignedReport($reportnumber);
		$this->_saveMasterReport($reportnumber);

	}

	protected function _saveAssignedReport($reportnumber){

		if(!isset($this->_controller->request->data['assigned_id'])) return;
		if($this->_controller->request->data['assigned_id'] == 0) return;
		if($reportnumber['Testingmethod']['allow_children'] == 0) return;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		$ChildReports = $this->_controller->Reportnumber->find('list', array('fields' => array('subindex'),'conditions'=>array('Reportnumber.parent_id' => $this->_controller->request->reportnumberID)));
		$AssignedReports = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $this->_controller->request->data['assigned_id'])));

		if(isset($this->_controller->request->data['setGenerally']) && $this->_controller->request->data['setGenerally'] == 1) {

			$MasterModel = 'Report'.ucfirst($reportnumber['Testingmethod']['value']).'Generally';
			$AssignedModel = 'Report'.ucfirst($AssignedReports['Testingmethod']['value']).'Generally';

			$this->_controller->loadModel($MasterModel);
			$this->_controller->loadModel($AssignedModel);

			$MasterData = $this->_controller->$MasterModel->find('first', array('conditions'=>array($MasterModel.'.reportnumber_id' => $id)));
			$AssignedData = $this->_controller->$AssignedModel->find('first', array('conditions'=>array($AssignedModel.'.reportnumber_id' => $this->_controller->request->data['assigned_id'])));

			$orderforGeneral['id'] = $AssignedData[$AssignedModel]['id'];
			$orderforGeneral['reportnumber_id'] = $this->_controller->request->data['assigned_id'];

			foreach($MasterData[$MasterModel] as $key => $value) {

				if(array_search($key, array('id', 'reportnumber_id', 'created', 'modified')) !== false) continue;
				$orderforGeneral[$key] = $value;
				if($this->_controller->$AssignedModel->hasField($key)) $orderforGeneral[$key] = $value;

			}

			$this->_controller->$AssignedModel->save($orderforGeneral);
			$this->_controller->Flash->success(__('The data has been accepted.',true), array('key' => 'success'));

		}

		if(!empty($ChildReports)) $MaxSubindex = max($ChildReports);
		else $MaxSubindex = 0;

		$UpdateAssignedData = array('id' => $this->_controller->request->data['assigned_id'],'parent_id' => $id,'subindex' => ++$MaxSubindex);

		$this->_controller->Reportnumber->save($UpdateAssignedData);

		$this->_controller->Flash->success(__('The report has been assigned.',true), array('key' => 'success'));

	}

	protected function _saveMasterReport($reportnumber){

		if(!isset($this->_controller->request->data['master_id'])) return;
		if($this->_controller->request->data['master_id'] == 0) return;
		if($reportnumber['Testingmethod']['allow_children'] == 1) return;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		if(isset($this->_controller->request->data['setGenerally']) && $this->_controller->request->data['setGenerally'] == 1) {

			$CurrentModel = 'Report'.ucfirst($reportnumber['Testingmethod']['value']).'Generally';

			$this->_controller->Reportnumber->recursive = 0;
			$oldReport = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $this->_controller->request->data['master_id'])));

			$MasterModel = 'Report'.ucfirst($oldReport['Testingmethod']['value']).'Generally';

			$this->_controller->loadModel($MasterModel);
			$this->_controller->loadModel($CurrentModel);

			$MasterData = $this->_controller->$MasterModel->find('first', array('conditions'=>array($MasterModel.'.reportnumber_id' => $this->_controller->request->data['master_id'])));
			$CurrentData = $this->_controller->$CurrentModel->find('first', array('conditions'=>array($CurrentModel.'.reportnumber_id' => $id)));

			$orderforGeneral['id'] = $CurrentData[$CurrentModel]['id'];
			$orderforGeneral['reportnumber_id'] = $id;

			foreach($MasterData[$MasterModel] as $key => $value) {

				if(array_search($key, array('id', 'reportnumber_id', 'created', 'modified')) !== false) continue;
				$orderforGeneral[$key] = $value;
				if($this->_controller->$CurrentModel->hasField($key)) $orderforGeneral[$key] = $value;

			}

			$this->_controller->$CurrentModel->save($orderforGeneral);

			$this->_controller->Flash->success(__('The data has been accepted.',true), array('key' => 'success'));

		}

		$UpdateMasterData = array('id' => $this->_controller->request->data['master_id'], 'master' => 1);
		$this->_controller->Reportnumber->save($UpdateMasterData);

		$ChildReports = $this->_controller->Reportnumber->find('list', array('fields' => array('subindex'),'conditions'=>array('Reportnumber.parent_id' => $this->_controller->request->data['master_id'])));
		$MaxSubindex = max($ChildReports);

		$UpdateAssignedData = array('id' => $id,'parent_id' => $this->_controller->request->data['master_id'],'subindex' => ++$MaxSubindex);
		$this->_controller->Reportnumber->save($UpdateAssignedData);

		$this->_controller->Flash->success(__('The report has been assigned.',true), array('key' => 'success'));

		$FormName['controller'] = 'reportnumbers';
		$FormName['action'] = 'edit';
		$FormName['terms'] = implode('/', $this->_controller->request->projectvars['VarsArray']);
		$this->_controller->set('FormName', $FormName);

	}
}
