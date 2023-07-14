<?php
//test
class ExpeditingToolComponent extends Component {
	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	protected function _ProjectVars(){

		$Var['projectID'] = $this->_controller->request->projectvars['VarsArray'][0];
		$Var['cascadeID'] = $this->_controller->request->projectvars['VarsArray'][1];
		$Var['supplierID'] = $this->_controller->request->projectvars['VarsArray'][2];
		$Var['expeditingTypID'] = $this->_controller->request->projectvars['VarsArray'][3];
		$Var['expeditingID'] = $this->_controller->request->projectvars['VarsArray'][4];
		$Var['medienID'] = $this->_controller->request->projectvars['VarsArray'][5];

		if($Var['projectID'] == 0) return false;
		if($Var['cascadeID'] == 0) return false;
		if($Var['supplierID'] == 0) return false;

		if(isset($this->_controller->request->data['Expediting']['id']) && 	$Var['expeditingID'] == 0) $Var['expeditingID'] = $this->_controller->request->data['Expediting']['id'];

		return $Var;

	}

	public function Filter($var){

		return false;
	}

	protected function _filter_callback_not_null($var){

		if($var != null) return $var;

	}

	public function CreateTempateForm($data){

		if(isset($this->_controller->request->data['ExpeditingTemplate']['Template'])) return $data;
		if(isset($this->_controller->request->data['ExpeditingTemplate']['id'])) return $data;
	
		if(isset($data['Expediting']) && count($data['Expediting']) > 0){
			
			if ($this->_controller->request->params['action'] != 'deleteexpeditings') {
   				 $this->_controller->Flash->warning(__('An expediting already exists.'), array('key' => 'warning'));
				$this->_controller->Flash->warning(__('The existing Expediting and all attached files will be deleted.'),array('key' => 'warning'));
			}

			if ($this->_controller->request->params['action'] == 'deleteexpeditings') {
				$this->_controller->Flash->error(__('The existing Expediting and all attached files will be deleted.'),array('key' => 'error'));
			}

		}

		$this->_controller->Expeditingset->recursive = -1;	

		$Expeditingset = $this->_controller->Expeditingset->find('list',array(
				'conditions' => array(
				//TODO: mögliche Berechtigungen???
				),
				'fields' => array(
					'id', 'name'
				),
				'order' => array(
					'name'
				)
			)
		);

		$this->_controller->set('Expeditingset',$Expeditingset);
		
		return $data;
	
	}

	public function EditExpeditingset($data){

		if(!isset($this->_controller->request->data['Expeditingset'])) return $data;

		$Insert = $this->_controller->request->data['Expeditingset'];

		if($this->_controller->Expeditingset->save($Insert)){

			$ExpeditingSetId = $this->_controller->request->data['Expeditingset']['id'];

			$data = $this->CollectExpeditingset();

			$data = $this->_CreateTempateData($data,$ExpeditingSetId);

			$this->_controller->Flash->success(__('The value has been saved.'),array('key' => 'success'));

			return $data;

		} else {

		}
	}

	public function AddExpeditingStepData($data){

		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return $data;

		$ExpeditingSetId = $this->_controller->request->projectvars['VarsArray'][3];

		$data = $this->_CollectTempateData($data,$ExpeditingSetId);
		$data = $this->_CollectAllRolls($data);
		$data = $this->_CollectExpeditingTypsAll($data);

		if(!isset($this->_controller->request->data['ExpeditingSetsExpeditingType'])) return $data;

		$Insert = $this->_controller->request->data['ExpeditingSetsExpeditingType'];

		$Insert['expediting_set_id'] = $ExpeditingSetId;

		unset($Insert['Roll']);
		unset($Insert['UrlLoadLastPage']);

		$ExpeditingStepId = $Insert['expediting_type_id'];

		if($this->_controller->Expeditingset->ExpeditingSetsExpeditingType->save($Insert)){

			$Conditions = array(
				'ExpeditingSetsExpeditingRoll.expediting_set_id' => $ExpeditingSetId,
				'ExpeditingSetsExpeditingRoll.expediting_type_id' => $ExpeditingStepId,
			);
	
			$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->deleteAll($Conditions, false);

			$Rolls = $this->_controller->request->data['ExpeditingSetsExpeditingType']['Roll'];

			if(count($Rolls) > 0){
				foreach($Rolls as $key => $value){

					$Insert = array(
						'expediting_set_id' => $ExpeditingSetId,
						'expediting_type_id' => $ExpeditingStepId,
						'roll_id' => $value,
					);

					$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->create();
					$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->save($Insert);

				}
			}

			$VarsArray = $this->_controller->request->projectvars['VarsArray'];

			$VarsArray[4] = 0;

			$FormName['controller'] = 'expeditings';
			$FormName['action'] = 'edittemplate';
			$Terms = implode('/',$VarsArray);
			$FormName['terms'] = $Terms;

			$this->_controller->set('FormName',$FormName);

			$this->_controller->Flash->success(__('The value has been saved.'),array('key' => 'success'));

		} else {
			
			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));

		}

		return $data;
	}

	public function DeleteTemplate($data){

		if(!isset($this->_controller->request->data['Expeditingset'])) return $data;
		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return $data;
		
		$ExpeditingsetId = $this->_controller->request->data['Expeditingset']['id'];

		if($this->_controller->Expeditingset->delete($ExpeditingsetId)){

			$Conditions = array('ExpeditingSetsExpeditingType.expediting_set_id' => $ExpeditingsetId);
			$this->_controller->Expeditingset->ExpeditingSetsExpeditingType->deleteAll($Conditions, false);

			$Conditions = array('ExpeditingSetsExpeditingRoll.expediting_set_id' => $ExpeditingsetId);
			$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->deleteAll($Conditions, false);

			$VarsArray = $this->_controller->request->projectvars['VarsArray'];

			$VarsArray[4] = 0;
			$VarsArray[3] = 0;

			$FormName['controller'] = 'expeditings';
			$FormName['action'] = 'indextemplate';
			$Terms = implode('/',$VarsArray);
			$FormName['terms'] = $Terms;

			$this->_controller->set('FormName',$FormName);

			$this->_controller->Flash->success(__('The value has been deleted.'),array('key' => 'success'));


		} else {

			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));

		}

		return $data;
	}

	public function DeleteExpeditingStepData($data){

		if(!isset($this->_controller->request->data['ExpeditingSetsExpeditingType'])) return $data;
		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return $data;
		if($this->_controller->request->projectvars['VarsArray'][4] == 0) return $data;	
		
		$ExpeditingSetId = $this->_controller->request->data['ExpeditingSetsExpeditingType']['id'];

		if($this->_controller->Expeditingset->ExpeditingSetsExpeditingType->delete($ExpeditingSetId)){

			$Conditions = array(
				'ExpeditingSetsExpeditingRoll.expediting_set_id' => $data['ExpeditingSetsExpeditingType']['expediting_set_id'],
				'ExpeditingSetsExpeditingRoll.expediting_type_id' => $data['ExpeditingSetsExpeditingType']['expediting_type_id'],
			);
	
			$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->deleteAll($Conditions, false);

			$VarsArray = $this->_controller->request->projectvars['VarsArray'];

			$VarsArray[4] = 0;

			$FormName['controller'] = 'expeditings';
			$FormName['action'] = 'edittemplate';
			$Terms = implode('/',$VarsArray);
			$FormName['terms'] = $Terms;

			$this->_controller->set('FormName',$FormName);

			$this->_controller->Flash->success(__('The value has been deleted.'),array('key' => 'success'));


		} else {

			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));

		}

		return $data;
	}

	public function EditExpeditingStepData($data){

		if(!isset($this->_controller->request->data['ExpeditingSetsExpeditingType'])) return $data;
		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return $data;
		if($this->_controller->request->projectvars['VarsArray'][4] == 0) return $data;		

		$Insert = $this->_controller->request->data['ExpeditingSetsExpeditingType'];

		unset($Insert['Roll']);
		unset($Insert['UrlLoadLastPage']);

		if($this->_controller->Expeditingset->ExpeditingSetsExpeditingType->save($Insert)){

			$Conditions = array(
				'ExpeditingSetsExpeditingRoll.expediting_set_id' => $data['ExpeditingSetsExpeditingType']['expediting_set_id'],
				'ExpeditingSetsExpeditingRoll.expediting_type_id' => $data['ExpeditingSetsExpeditingType']['expediting_type_id'],
			);
	
			$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->deleteAll($Conditions, false);

			$Rolls = $this->_controller->request->data['ExpeditingSetsExpeditingType']['Roll'];

			if(count($Rolls) > 0){
				foreach($Rolls as $key => $value){

					$Insert = array(
						'expediting_set_id' => $data['ExpeditingSetsExpeditingType']['expediting_set_id'],
						'expediting_type_id' => $data['ExpeditingSetsExpeditingType']['expediting_type_id'],
						'roll_id' => $value,
					);

					$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->create();
					$this->_controller->Expeditingset->ExpeditingSetsExpeditingRoll->save($Insert);

				}
			}

			$VarsArray = $this->_controller->request->projectvars['VarsArray'];

			$VarsArray[4] = 0;

			$FormName['controller'] = 'expeditings';
			$FormName['action'] = 'edittemplate';
			$Terms = implode('/',$VarsArray);
			$FormName['terms'] = $Terms;

			$this->_controller->set('FormName',$FormName);

			$this->_controller->Flash->success(__('The value has been saved.'),array('key' => 'success'));

		} else {
			
			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));

		}

		return $data;
	}

	public function EditExpeditingStepDataShow($data){

		if(isset($this->_controller->request->data['ExpeditingSetsExpeditingType'])) return $data;
		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return $data;
		if($this->_controller->request->projectvars['VarsArray'][4] == 0) return $data;

		$ExpeditingSetId = $this->_controller->request->projectvars['VarsArray'][3];
		$ExpeditingStepId = $this->_controller->request->projectvars['VarsArray'][4];

		$ExpeditingSetsExpeditingType = $this->_controller->Expeditingset->ExpeditingSetsExpeditingType->find('first',array(
			'conditions' => array(
					'ExpeditingSetsExpeditingType.id' => $ExpeditingStepId
				)
			)
		);

		$ExpeditingType = $this->_controller->ExpeditingType->find('first',
		array(
			'conditions' => array(
				'ExpeditingType.id' => $ExpeditingSetsExpeditingType['ExpeditingSetsExpeditingType']['expediting_type_id'],
				'ExpeditingType.deleted' => 0,
				)
			)
		);

		$ExpeditingSetsExpeditingType['ExpeditingSetsExpeditingType']['description'] = $ExpeditingType['ExpeditingType']['description'];

		$data['ExpeditingSetsExpeditingType'] = $ExpeditingSetsExpeditingType['ExpeditingSetsExpeditingType'];

		if(!isset($data['Template'])) return $data;

		foreach($data['Template'] as $key => $value){

			if($value['Expediting']['id'] == $ExpeditingStepId) break;
		}

		unset($data['Template']);
	
		$data['ExpeditingSetsExpeditingType'] = $value['Expediting'];
		$data['Roll'] = $value['Roll'];

		$data = $this->_CollectAllRolls($data);

		return $data;
	}

	public function CreateTempateDataShow($data){

		$data = $this->_SaveExpeditingset($data);
		


		unset($data['Expediting']);

		return $data;
	}

	protected function _SaveExpeditingset($data) {

		if(!isset($this->_controller->request->data['Expeditingset'])) return $data;

		$Insert = $this->_controller->request->data['Expeditingset'];

		if (empty($Insert['id'])) {
    		$Insert['id'] = 0;
			$Message = __('The value has been saved.');
		}

		if($this->_controller->Expeditingset->save($Insert)){

			if(isset($Message)){

				$ExpeditingSetId = $this->_controller->Expeditingset->getLastInsertID();

				$VarsArray = $this->_controller->request->projectvars['VarsArray'];
				$VarsArray[3] = $ExpeditingSetId;

				$FormName['controller'] = 'expeditings';
				$FormName['action'] = 'edittemplate';
				$Terms = implode('/',$VarsArray);
				$FormName['terms'] = $Terms;
	
			} else {
				$ExpeditingSetId = $Insert['id'];
			}	

			


			$Expeditingset = $this->_controller->Expeditingset->find('first',array(
				'conditions' => array(
						'Expeditingset.id' => $ExpeditingSetId
					)
				)
			);

			$data['Expeditingset'] = $Expeditingset['Expeditingset'];

			if(isset($Message)) $this->_controller->Flash->success($Message,array('key' => 'success'));

			$this->_controller->set('FormName',$FormName);

		} 
	
    	return $data;
	}	

	public function EditTempateDataShow($data){

		$TemplateId = 0;

		if($this->_controller->request->projectvars['VarsArray'][3] > 0) $TemplateId = $this->_controller->request->projectvars['VarsArray'][3];
		if(isset($this->_controller->request->data['TemplateId'])) $TemplateId = $this->_controller->request->data['TemplateId'];
		if($TemplateId == 0) return $data;

		$data = $this->_CollectTempateData($data);

		return $data;
	}

	public function CreateTempateData($data){

		$ExpeditingSetId = 0;

		if(isset($this->_controller->request->data['ExpeditingTemplate']['Template'])) $ExpeditingSetId = $this->_controller->request->data['ExpeditingTemplate']['Template'];
		if($this->_controller->request->projectvars['VarsArray'][3] > 0) $ExpeditingSetId = $this->_controller->request->projectvars['VarsArray'][3];

		if($ExpeditingSetId == 0) return $data;
		if(isset($this->_controller->request->data['ExpeditingTemplate']['id'])) return $data;

		if(isset($data['Expediting']) && count($data['Expediting']) > 0){
			$this->_controller->Flash->error(__('The existing Expediting and all attached files will be deleted.'),array('key' => 'error'));
		}
		$data = $this->_CreateTempateData($data,$ExpeditingSetId);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'addtemplate', 'terms' => $this->_controller->request->projectvars['VarsArray']);
		
		$this->_controller->set('SettingsArray',$SettingsArray);

		return $data;
	}

	public function ExpedtigintDeleteAll($data){


		if(!isset($this->_controller->request->data['ExpeditingTemplate']['DeleteExpeditingId'])) return $data;

		$data = $this->_DeleteExistingExpediting($data);

		if($data != false) {

			$FormName['controller'] = 'suppliers';
			$FormName['action'] = 'overview';
			$Terms = implode('/',$this->_controller->request->projectvars['VarsArray']);
			$FormName['terms'] = $Terms;

			$this->_controller->Flash->success(__('The expediting has been deleted'),array('key' => 'success'));

			$this->_controller->set('FormName',$FormName);
	
		} else {
			$this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
		}

		return $data;

	}

	public function RemoveUsedExpeditingtyps($data){

		if(!isset($data['ExpeditingTypes'])) return $data;
		if(!isset($data['Template'])) return $data;

		$UsedExpeditingtype = Hash::extract($data['Template'], '{n}.Expediting.expediting_type_id');

		if(count($UsedExpeditingtype)== 0) return $data;

		$ExpeditingTypes = $this->_controller->ExpeditingType->find('list',
			array(
				'fields' => array('id','description'),
				'order' => array('description'),
				'conditions' => array(
					'ExpeditingType.id' => $UsedExpeditingtype
				)
			)
		);

		if(count($ExpeditingTypes)== 0) return $data;

		$Check = array_intersect($data['ExpeditingTypes'], $ExpeditingTypes);

		if(count($Check)== 0) return $data;

		$Check = array_keys($Check);

		foreach($Check as $key => $value){
			unset($data['ExpeditingTypes'][$value]);
		}

		return $data;
	}

	public function CreateTempateSave($data){

		if(!isset($this->_controller->request->data['ExpeditingTemplate']['id'])) return $data;

		$data = $this->_DeleteExistingExpediting($data);

		$ExpeditingSetId = $this->_controller->request->data['ExpeditingTemplate']['id'];

		$data = $this->_CreateTempateData($data,$ExpeditingSetId);
		$data = $this->_CreateTempateSave($data);

		if($data != false) {

			$FormName['controller'] = 'suppliers';
			$FormName['action'] = 'overview';
			$Terms = implode('/',$this->_controller->request->projectvars['VarsArray']);
			$FormName['terms'] = $Terms;
			
			$this->_controller->set('FormName',$FormName);
	
		} else {

			$this->_controller->Flash->error(__('An error has occurred'),array('key' => 'error'));

		}

		return $data;
	}

	protected function _CreateTempateSave($data){

		$ExpeditingSetId = $this->_controller->request->data['ExpeditingTemplate']['id'];
		$Sequence = 1;

		foreach($data['Template'] as $key => $value){

			$insert['Expediting'] = $value['Expediting'];

			$insert['Expediting']['supplier_id'] = $data['Supplier']['id'];
			$insert['Expediting']['expediting_type_id'] = $insert['Expediting']['expediting_type_id'];
			$insert['Expediting']['sequence'] = $Sequence;

			unset($insert['Expediting']['id']);
			unset($insert['Expediting']['description']);
			unset($insert['Expediting']['remark']);
			unset($insert['Expediting']['period_datum']);
			unset($insert['Expediting']['period_sign']);
			unset($insert['Expediting']['period_time']);
			unset($insert['Expediting']['period_measure']);
			unset($insert['Expediting']['hold_witness_point_description']);
			unset($insert['Expediting']['hold_witness_point_class']);
			unset($insert['Expediting']['date_ist']);
			unset($insert['Expediting']['date_current']);
			
			$this->_controller->Expediting->create();

			if($this->_controller->Expediting->save($insert['Expediting'])){

				$ExpeditingId = $this->_controller->Expediting->getLastInsertID();

				$insert = array();
				$insert['Supplier']['id'] = $data['Supplier']['id'];
				$insert['Supplier']['expeditingset_id'] = $ExpeditingSetId;
				$this->_controller->Supplier->save($insert['Supplier']);
			
				$insert = array();
				$insert['ExpeditingsRoll']['expediting_id'] = $ExpeditingId;

				foreach($value['Roll'] as $_key => $_value){

					$insert['ExpeditingsRoll']['roll_id'] = $_value;

					$this->_controller->ExpeditingsRoll->create();
					$this->_controller->ExpeditingsRoll->save($insert['ExpeditingsRoll']);

				}	

			} else {

				return false;

			}

			$Sequence++;
		}

		$this->_controller->Flash->success(__('Expediting created.'),array('key' => 'success'));

		return $data;

	}

	protected function _CollectTempateData($data){

		if(!isset($data['Expeditingset'])) return $data;
		if(!isset($data['ExpeditingSetsExpeditingType'])) return $data;
		if(count($data['ExpeditingSetsExpeditingType']) == 0) return $data;

		$ExpeditingSetId = $this->_controller->request->projectvars['VarsArray'][3];

		foreach($data['ExpeditingSetsExpeditingType'] as $key => $value){

			$ExpeditingType = $this->_controller->ExpeditingType->find('first',array(
				'conditions' => array(
					'ExpeditingType.id' => $value['expediting_type_id']
					)
				)
			);

			$data['ExpeditingSetsExpeditingType'][$key]['description'] = $ExpeditingType['ExpeditingType']['description'];
		}

		$output = array('Template' => array());

		foreach($data['ExpeditingSetsExpeditingType'] as $key => $value){

			switch ($value['hold_witness_point']) {
				case 0:
				$value['hold_witness_point'] = 0;
				$value['hold_witness_point_description'] = __('Indefinite',true);
				$value['hold_witness_point_class'] = 'icon_hold_witness_undefinite';
				break;

				case 1:
				$value['hold_witness_point'] = 1;
				$value['hold_witness_point_description'] = __('Witness point',true);
				$value['hold_witness_point_class'] = 'icon_hold_witness_witness';
				break;

				case 2:
				$value['hold_witness_point'] = 2;
				$value['hold_witness_point_description'] = __('Hold point',true);
				$value['hold_witness_point_class'] = 'icon_hold_witness_hold';
				break;

			}

			$value['date_ist'] = '';
			$value['remark'] = '';
			$value['date_current'] = '';
			$value['finished'] = 0;
			$value['stop_mail'] = 0;
			$value['stop_for_next_step'] = 0;

			if($value['hold_witness_point'] == 2) $value['stop_for_next_step'] = 1;

			$output['Template'][$key]['Expediting'] = $value;

			$roll_ids = Hash::extract($data['ExpeditingSetsExpeditingRoll'], '{n}[expediting_set_id='.$ExpeditingSetId.'][expediting_type_id='.$value['expediting_type_id'].'].roll_id');
		
			$Roll = $this->_controller->Roll->find('list',array('conditions' => array('Roll.id' => $roll_ids)));

			if (count($Roll) > 0) {
				$output['Template'][$key]['Roll'] = $roll_ids;
				$output['Template'][$key]['Rollname'] = $Roll;
			}
		}

		$data['Template'] = $output['Template'];

		return $data;
	}

	protected function _CreateTempateData($data,$ExpeditingSetId){

		//		if(isset($data['Expeditingset'])) return $data;

		$Expeditingset = $this->_controller->Expeditingset->find('first',array(
			'conditions' => array(
				'Expeditingset.id' => $ExpeditingSetId
				),
			)
		);

		if(count($Expeditingset) == 0) return $data;

		if(count($Expeditingset['ExpeditingSetsExpeditingType']) == 0){

			
			$data['Expeditingset'] = $Expeditingset['Expeditingset'];
			return $data;

		} 

		$data['Expeditingset'] = $Expeditingset['Expeditingset'];

		foreach($Expeditingset['ExpeditingSetsExpeditingType'] as $key => $value){

			$ExpeditingType = $this->_controller->ExpeditingType->find('first',array(
				'conditions' => array(
					'ExpeditingType.id' => $value['expediting_type_id']
					)
				)
			);

			$value['description'] = $ExpeditingType['ExpeditingType']['description'];

			$Expeditingset['ExpeditingType'][$key] = $value;
		}

		// Check ob die Datumsfelder im Objekt vorhanden sind
		$Fields = Hash::extract($Expeditingset['ExpeditingType'], '{n}.period_datum');
		$Fields = array_unique($Fields);
		$Fields = array_filter($Fields, array($this, '_filter_callback_not_null'));

		foreach($Fields as $key => $value){

			if(empty($data['Supplier'][$value])) {

				$EditUrl = Router::url(array_merge(
					array(
						'controller' => 'suppliers', 
						'action' => 'edit'
						),
					$this->_controller->request->projectvars['VarsArray']
					)
				);

				$this->_controller->Flash->warning(__('In order to create an expediting, it is necessary to enter the order and delivery date.'),array('key' => 'warning'));
				$this->_controller->set('EditUrl',$EditUrl);
				return false;

			}
		}

		foreach($Expeditingset['ExpeditingType'] as $key => $value){

			if(empty($value['period_datum'])) continue;

			$period_datum_field = $value['period_datum'];
			$period_datum = $data['Supplier'][$period_datum_field];

			$date = new DateTime($period_datum);
			$periode = $value['period_sign'] . $value['period_time'] . ' ' . $value['period_measure']; 

			$date->modify($periode);
			$Expeditingset['ExpeditingType'][$key]['date_soll'] = $date->format('Y-m-d');

		}

		$output = array('Template' => array());

		foreach($Expeditingset['ExpeditingType'] as $key => $value){

			$hold = Hash::extract($Expeditingset['ExpeditingSetsExpeditingType'], '{n}[expediting_type_id='.$value['expediting_type_id'].'].hold_witness_point');

			switch ($hold[0]) {
				case 0:
				$value['hold_witness_point'] = 0;
				$value['hold_witness_point_description'] = __('Indefinite',true);
				$value['hold_witness_point_class'] = 'icon_hold_witness_undefinite';
				break;

				case 1:
				$value['hold_witness_point'] = 1;
				$value['hold_witness_point_description'] = __('Witness point',true);
				$value['hold_witness_point_class'] = 'icon_hold_witness_witness';
				break;

				case 2:
				$value['hold_witness_point'] = 2;
				$value['hold_witness_point_description'] = __('Hold point',true);
				$value['hold_witness_point_class'] = 'icon_hold_witness_hold';
				break;

			}

			$value['date_ist'] = '';
			$value['remark'] = '';
			$value['date_current'] = '';
			$value['finished'] = 0;
			$value['stop_mail'] = 0;
			$value['stop_for_next_step'] = 0;

			if($value['hold_witness_point'] == 2) $value['stop_for_next_step'] = 1;

			$output['Template'][$key]['Expediting'] = $value;

			$roll_ids = Hash::extract($Expeditingset['ExpeditingSetsExpeditingRoll'], '{n}[expediting_set_id='.$ExpeditingSetId.'][expediting_type_id='.$value['expediting_type_id'].'].roll_id');
		
			$Roll = $this->_controller->Roll->find('list',array('conditions' => array('Roll.id' => $roll_ids)));

			if (count($Roll) > 0) {
				$output['Template'][$key]['Roll'] = $roll_ids;
				$output['Template'][$key]['Rollname'] = $Roll;
			}
		}

		$data['Template'] = $output['Template'];

		return $data;

	}

	private function _DeleteExistingExpediting($data){

		$data = $this->_DeleteExpeditings($data);
		$data = $this->_DeleteExpeditingRolls($data);
		$data = $this->_DeleteExpeditingEvents($data);
		$data = $this->_DeleteExpeditingReportnumbers($data);
		$data = $this->_DeleteExpeditingFiles($data);
		$data = $this->_DeleteExpeditingImages($data);

		return $data;
	}

	private function _DeleteExpeditings($data){

		$SupplierId = $data['Supplier']['id'];

		$this->_controller->Expediting->deleteAll(array('Expediting.supplier_id' => $SupplierId), false);

		return $data;
	}

	private function _DeleteExpeditingRolls($data){

		$ExpeditingIds = Hash::extract($data['Expediting'], '{n}.Expediting.id');

		$this->_controller->ExpeditingsRoll->deleteAll(array('ExpeditingsRoll.expediting_id' => $ExpeditingIds), false);

		return $data;
	}

	private function _DeleteExpeditingEvents($data){

		$SupplierId = $data['Supplier']['id'];

		$this->_controller->ExpeditingEvent->deleteAll(array('ExpeditingEvent.supplier_id' => $SupplierId), false);

		return $data;
	}

	private function _DeleteExpeditingReportnumbers($data){

		$ExpeditingIds = Hash::extract($data['Expediting'], '{n}.Expediting.id');

		$this->_controller->ExpeditingReportnumber->deleteAll(array('ExpeditingReportnumber.expediting_id' => $ExpeditingIds), false);
	
		return $data;
	}

	private function _DeleteExpeditingImages($data){


		$SupplierId = $data['Supplier']['id'];

		$Supplierimages = $this->_controller->Supplierimage->find('list',array(
			'conditions' => array(
				'Supplierimage.supplier_id' => $SupplierId
				)
			)
		);

		if(count($Supplierimages) == 0) return $data;

		foreach($Supplierimages as $key => $value){

			$Supplierimage = $this->_controller->Supplierimage->find('first',array(
				'conditions' => array(
					'Supplierimage.id' => $value
					)
				)
			);

			if($this->_controller->Supplierimage->delete($value)){

			} else {

			}

			// Speicherpfad für den Upload
			$delPath = Configure::read('supplier_folder') . $Supplierimage['Supplierfile']['supplier_id'] . DS . $Supplierimage['Supplierfile']['file'];
			$delFile = new File($delPath);
			
			// Wenn die Dateien existieren werden sie gelöscht
			if(file_exists($delFile->path)){
				if($delFile->delete()){
				}
				else {
				}
			}
			else {

			}
		}

		return $data;
	}

	private function _DeleteExpeditingFiles($data){


		$SupplierId = $data['Supplier']['id'];

		$Supplierfiles = $this->_controller->Supplierfile->find('list',array(
			'conditions' => array(
				'Supplierfile.supplier_id' => $SupplierId
				)
			)
		);

		if(count($Supplierfiles) == 0) return $data;

		foreach($Supplierfiles as $key => $value){

			$Supplierfile = $this->_controller->Supplierfile->find('first',array(
				'conditions' => array(
					'Supplierfile.id' => $value
					)
				)
			);

			if($this->_controller->Supplierfile->delete($value)){

			} else {

			}

			// Speicherpfad für den Upload
			$delPath = Configure::read('supplier_folder') . $Supplierfile['Supplierfile']['supplier_id'] . DS . $Supplierfile['Supplierfile']['file'];
			$delFile = new File($delPath);
			
			// Wenn die Dateien existieren werden sie gelöscht
			if(file_exists($delFile->path)){
				if($delFile->delete()){
				}
				else {
				}
			}
			else {

			}
		}

		return $data;
	}

	public function AddNewExpeditingObject(){

		$data = array();

		$this->_controller->Cascade->recursive = -1;

		$CascadeMax = $this->_controller->Cascade->find('first',array(
			'fields' => array('MAX(Cascade.level)')
			)
		);

		if(empty($CascadeMax)) return $data;

		$MaxLevel = $CascadeMax[0]['MAX(`Cascade`.`level`)'] - 1;

		$Cascades = $this->_controller->Cascade->find('list',array(
				'conditions' => array('Cascade.level' => $MaxLevel)
			)
		);

		foreach ($Cascades as $key => $value) {

			$data['Cascade'][] = $this->GetCurrentCascade($value);

		}

		return $data;
	}

	public function GetCurrentCascade($id){

		$this->_controller->Cascade->recursive = -1;

		$Cascade = $this->_controller->Cascade->find('first',array(
				'conditions' => array('Cascade.id' => $id)
			)
		);

		$Parents[] = $Cascade;
		$Parents = $this->GetAllParentCascades($Parents);

		krsort($Parents);

		return $Parents;

	}

	public function GetAllParentCascades($data){

		$end = end($data);

		if($end['Cascade']['level'] == 0) return $data;

		$this->_controller->Cascade->recursive = -1;
		$Cascades = $this->_controller->Cascade->find('first',array(
				'conditions' => array('Cascade.id' => $end['Cascade']['parent'])
			)
		);

		if(empty($Cascades)) return $data;

		$data[] = $Cascades;

		$data = $this->GetAllParentCascades($data);

		$this->_controller->request->data = $data;

		return $data;

	}

	public function CheckTestingcompsCascades($data){

		if(!isset($data['Testingcomp']['Testingcomp'])) return true;
		if(empty($data['Testingcomp']['Testingcomp'])) return true;

		$this->_controller->loadModel('TestingcompsCascade');

		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$TestingcompIds = $data['Testingcomp']['Testingcomp'];

		$ParentList = $this->_controller->Navigation->CascadeGetParentList($cascadeID,array());

		$ParentListIds = Hash::extract($ParentList, '{n}.Cascade.id');

		foreach ($TestingcompIds as $key => $value) {
			foreach ($ParentListIds as $_key => $_value) {

			}
		}

		return true;
	}

	public function SupplierTableInfos($data){

		if(!isset($this->_controller->request->data['CascadeId'])) return $data;
		if($this->_controller->request->data['CascadeId'] == 0) return $data;
		if(isset($data['Supplier'])) return $data;
		if(!isset($data['Cascades'])) return $data;

		$CascadeId = $this->_controller->request->data['CascadeId'];

		$AllCascadesUnder = $this->_CascadeGetChildrenList(intval($CascadeId));

		if(empty($AllCascadesUnder)) $data = $this->SelectSuplier($data);
		else $data['AllCascadesUnder'] = $AllCascadesUnder;

		$data = $this->SelectSuplier($data);

		unset($data['xml']);
		unset($data['BreadcrumpArray']);
		unset($data['CascadeForBread']);
		unset($data['Cascades']);
		unset($data['ExpeditingNavi']);
		unset($data['CascadeForBread']);
		unset($data['ExpeditingLinks']);
		unset($data['HeadLineFirst']);
		unset($data['ChildrenList']);
		unset($data['ParentCasace']);
		unset($data['AllCascadesUnder']);
		unset($data['CascadeIds']);
		unset($data['SelectOrderByTestingcomp']);
		unset($data['SupplierOption']);
		unset($data['PlannerOption']);
		unset($data['AreaOfResponsibilityOption']);
		unset($data['Supplier']);
		unset($data['Testingcomps']);
		unset($data['Testingcomp']);
		unset($data['HeadLine']);

		$data['DataId'] = $CascadeId;

		return $data;
	}

	protected function _CascadeGetChildrenList($id) {

		$output[] = intval($id);

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',array('fields' => array('id'),'conditions'=>array('Cascade.parent'=>$id)));

		if(count($Cascade) > 0){
			foreach($Cascade as $_key => $_Cascade){
				$output_2 = $this->_CascadeGetChildrenList($_Cascade);
				foreach($output_2 as $__key => $__output_2){
					$output[] = $__output_2;
				}
			}
		}

		return $output;
	}

	public function SaveSupplierData($data){

		if(!isset($this->_controller->request->data['Supplier'])) return NULL;

		$topproject_id = $data['Cascade']['topproject_id'];
		$cascade_id = $data['Cascade']['id'];
		$discription = $data['Cascade']['discription'];

		$Check = $this->_SaveSupplierDataCheckValues($data);

		if($Check === false) return false;

		$insert = $this->_controller->request->data;

		$insert['Order'] = $insert['Supplier'];

		$insert['Supplier']['testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');
		$insert['Supplier']['cascade_id'] = $cascade_id;
		$insert['Supplier']['topproject_id'] = $topproject_id;

		$insert['Order']['testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');
		$insert['Order']['cascade_id'] = $cascade_id;
		$insert['Order']['topproject_id'] = $topproject_id;

		if($this->_controller->Order->save($insert['Order'])){

			$OrderId = $this->_controller->Order->getLastInsertID();
			$insert['Supplier']['order_id'] = $OrderId;

		} else {

			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));
			return false;

		}

		$this->_controller->Supplier->create();

		if($this->_controller->Supplier->save($insert['Supplier'])){

			$SupplierId = $this->_controller->Supplier->getLastInsertID();

			$FormName = array();
			$FormName['controller'] = 'suppliers';
			$FormName['action'] = 'overview';

			$Terms = array($this->_controller->request->projectvars['VarsArray'][0],$this->_controller->request->projectvars['VarsArray'][1],$SupplierId);

			$FormName['terms'] = implode('/',$Terms);

			$this->_controller->set('FormName',$FormName);

			$this->_controller->Flash->success(__('Value was saved'),array('key' => 'success'));

			return true;

		} else {

			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));
			return false;

		}
	}

	protected function _SaveSupplierDataCheckValues($data){

		$cascade_id = $data['Cascade']['id'];
		$discription = $data['Cascade']['discription'];
		$equipment = $this->_controller->request->data['Supplier']['equipment'];

		$Supplier = $this->_controller->Supplier->find('first',array(
			'conditions' => array(
					'Supplier.cascade_id' => $cascade_id,
					'Supplier.equipment LIKE' => '%' . $equipment . '%'
				)
			)
		);

		if(!empty($Supplier)){
			$this->_controller->Flash->error(__('This value already exists.'),array('key' => 'error'));
			return false;
		}

		return true;
	}

	public function SaveSupplierDataFast(){

		$Data = array();
		$Request = array();

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetails($Data);
		$Data = $this->_CheckExpeditingDetails($Data);
		$Save = $this->_SaveFastExpeditingEvent($Data);

		if($Save == false) return $Data;

		$Request = $this->_SaveExpeditingSollDateFast($Data,$Request);
		$Request = $this->_SaveExpeditingFileDescriptionFast($Data,$Request);

		$Data = $this->_CollectExpeditingDetails($Data);

		$Request['expediting'] = $Data['Expediting'];

		$Request = $this->_UpdateCurrentExpediting($Request);

		$this->_controller->set('request',json_encode($Request));

		return $Data;

	}

	public function RemoveDataForAdd($data){

		$testingcomp_id = $this->_controller->Auth->user('testingcomp_id');
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

//		$SchemaOrder = $this->Order->schema();
//		$SchemaSupplier = $this->Supplier->schema();

		unset($data['Expediting']);
		unset($data['BreadcrumpArray']);
		unset($data['Supplier']['id']);
		unset($data['Supplier']['unit']);
		unset($data['Supplier']['created']);
		unset($data['Supplier']['modified']);
		unset($data['Order']['id']);
		unset($data['Order']['created']);
		unset($data['Order']['modified']);

		$data = $this->_DeleteValueFromArray($data,'Order');
		$data = $this->_DeleteValueFromArray($data,'Supplier');

		$data['Order']['topproject_id'] = $testingcomp_id;
		$data['Order']['cascade_id'] = $cascadeID;

		$data['Supplier']['topproject_id'] = $testingcomp_id;
		$data['Supplier']['cascade_id'] = $cascadeID;
		$data['Supplier']['stop_mail'] = 0;

		$data['Emailaddress']['selected'] = array();
		$data['Testingcomp']['selected'] = array($testingcomp_id => $testingcomp_id);

		return $data;
	}

	protected function _DeleteValueFromArray($data,$model){

		$SchemaOrder = $this->_controller->{$model}->schema();

		foreach ($SchemaOrder as $key => $value) {

			if(!isset($data[$model][$key])) continue;

			$val = '';
			if($value['type'] == 'integer') 	$val = 0;

			$data[$model][$key] = $val;

		}

		return $data;
	}

	public function CascadeGetFromTestingcomp() {

		$this->_controller->loadModel('Order');
		$this->_controller->loadModel('CascadesOrder');
		$this->_controller->loadModel('TestingcompsCascade');

		$OrderIds = $this->_controller->Order->OrdersTestingcomp->find('list',array(
			'fields' => array(
				'order_id',
				'order_id'
				)	, 
			'conditions' => array(
				'OrdersTestingcomp.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id')
				)
			)
		);
	
		$CascadeIds = $this->_controller->CascadesOrder->find('list',array(
			'fields' => array(
				'cascade_id',
				'cascade_id'
				), 
			'conditions' => array(
				'CascadesOrder.order_id' => $OrderIds
				)
			)
		);

		$CascadeIds = $this->_controller->TestingcompsCascade->find('list',array(
			'fields' => array(
				'cascade_id',
				'cascade_id'
				), 
			'conditions' => array(
				'TestingcompsCascade.cascade_id' => $CascadeIds,
				'TestingcompsCascade.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
				)
			)
		);

		return $CascadeIds;
	}

	public function CascadeGetNextChildren($id,$fields) {

		if(!is_array($fields)) return false;
		if(count($fields) == 0) return false;

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',array('fields' => $fields,'conditions'=>array('Cascade.parent'=>$id)));

		if($this->_controller->Auth->user('roll_id') == 1) return $Cascade;

		$ParentList = array();
		$CascadeOutput = array();

		$FromTestingcomp = $this->CascadeGetFromTestingcomp();

		if(count($FromTestingcomp) == 0) return array();

		foreach($FromTestingcomp as $_key => $_data){
			$ParentList = array_merge($ParentList, $this->_controller->Navigation->CascadeGetParentList($_key,array()));
		}

		if(count($ParentList) == 0) return array();

		foreach($ParentList as $_key => $_data){

			if(!isset($_data['Cascade'])) continue;

			if(isset($Cascade[$_data['Cascade']['id']])) $CascadeOutput[$_data['Cascade']['id']] = $Cascade[$_data['Cascade']['id']];

		}

		if(count($CascadeOutput) == 0) return false;

		return $CascadeOutput;

	}

	public function SelectCascadeInfosStart($data){

		if(!empty($data['Cascades'])) return $data;
		if(empty($data['ChildrenList'])) return $data;
		if($this->_controller->request->projectvars['VarsArray'][0] > 0) return $data;

		foreach($data['ChildrenList'] as $key => $value){

			$Supplier = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.cascade_id' => $key)));
			$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $key)));

			if($Cascade['Cascade']['orders'] == 1 && empty($Supplier)) continue;

			$data['Cascades'][$key] = $Cascade;

		}

		return $data;
	}

	public function SelectCascadeInfos($data){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		$ChildIds = $this->CascadeGetNextChildren($cascadeID,array('id','orders'));

		$data['Cascades'] = array();

		if(empty($ChildIds)) return $data;

		foreach ($ChildIds as $key => $value) {

			$Supplier = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.cascade_id' => $key)));
			$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $key)));

			if($Cascade['Cascade']['orders'] == 1 && empty($Supplier)) continue;

			$data['Cascades'][$key] = $Cascade;

		}

		return $data;
	}

	public function SelectCascadeSkipSuppliers($data){

		$topproject_id = $this->_controller->request->projectvars['VarsArray'][0];
		$cascade_id = $this->_controller->request->projectvars['VarsArray'][1];

		$CheckCount = count($this->_controller->Navigation->CascadeGetChildrenList($cascade_id));

		if($CheckCount != 2) return $data;

		if(!isset($data['Cascades'])) return $data;
		if(empty($data['Cascades'])) return $data;

		$Suppliers = array();

		foreach ($data['Cascades'] as $key => $value) {

			$cascadeIDs = $this->_controller->Navigation->CascadeGetChildrenList($value['Cascade']['id']);

			if(count($cascadeIDs) > 1) continue;

			$Supplier = $this->_SelectCascadeSuppliersForSkip($value);

			if(empty($Supplier['Suppliers'])){

				$data['Suppliers'] = array();

				return $data;

			}

			foreach ($Supplier['Suppliers'] as $_key => $_value) {
				$Suppliers[] = $_value;
			}
		}

		if(!empty($Suppliers)){
			$data['Cascades'] = array();
		}

		$data['Suppliers'] = $Suppliers;

		$data['AddExpeditingUrl']['controller'] = 'expeditings';
		$data['AddExpeditingUrl']['action'] = 'addexpeditingobject';
		$data['AddExpeditingUrl']['parm'] = array($topproject_id,$cascade_id);

		return $data;
	}

	public function SelectCascadeSkipSuppliersNoEntrys($data){

		$topproject_id = $this->_controller->request->projectvars['VarsArray'][0];
		$cascade_id = $this->_controller->request->projectvars['VarsArray'][1];

		$CheckCount = count($this->_controller->Navigation->CascadeGetChildrenList($cascade_id));

		if($CheckCount > 1) return $data;
		if(!empty($data['Cascades'])) return $data;
		if(isset($data['Suppliers'])) return $data;

		$data['AddExpeditingUrl']['controller'] = 'expeditings';
		$data['AddExpeditingUrl']['action'] = 'addexpeditingobject';
		$data['AddExpeditingUrl']['parm'] = array($topproject_id,$cascade_id);

		return $data;
	}

	protected function _SelectCascadeSuppliersForSkip($data){

		if(!isset($data)) return $data;
		if(empty($data)) return $data;

		$projectID = $data['Cascade']['topproject_id'];
		$cascadeID = $data['Cascade']['id'];
		$locale = $this->_controller->Lang->Discription();


		$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $cascadeID)));
		if(empty($Cascade)) return $data;

		$data['Cascade'] = $Cascade['Cascade'];

		$Suppliers = $this->_controller->Supplier->find('all',array(
			'order' => array(),
			'conditions' => array(
				'Supplier.deleted' => 0,
				'Supplier.cascade_id' => $cascadeID,
				)
			)
		);

		if(empty($Suppliers)) return $data;

		$data['Suppliers'] = array();

		$Var2 = $this->_controller->request->projectvars['VarsArray'][2];

		foreach ($Suppliers as $key => $value) {

			$this->_controller->request->projectvars['VarsArray'][2] = $value['Supplier']['id'];
			$Test = $this->CollectSupplierData(array());
			$Test = $this->CollectSupplierData($Test['Expediting']);

			unset($Test['CascadeForBread']);
			unset($Test['emailaddress']);
			unset($Test['BreadcrumpArray']);

			$data['Suppliers'][] = $Test;

		}

		$this->_controller->request->projectvars['VarsArray'][2] = $Var2;

		return $data;
	}

	public function AddLinkWhenEmpty($data){

		if(isset($data['Cascades']) && count($data['Cascades']) > 0) return $data;
		if(isset($data['Suppliers']) && count($data['Suppliers']) > 0) return $data;

		$topproject_id = $this->_controller->request->projectvars['VarsArray'][0];
		$cascade_id = $this->_controller->request->projectvars['VarsArray'][1];

		$data['AddExpeditingUrl']['controller'] = 'expeditings';
		$data['AddExpeditingUrl']['action'] = 'addexpeditingobject';
		$data['AddExpeditingUrl']['parm'] = array($topproject_id,$cascade_id);

		return $data;
	}

	public function SelectCascadeSuppliers($data){

		if(!isset($data['Cascades'])) return $data;
		if(!empty($data['Cascades'])) return $data;
		if(count($data['Cascades']) == 0) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $cascadeID)));

		if(empty($Cascade)) return $data;

		$data['Cascade'] = $Cascade['Cascade'];

		$Suppliers = $this->_controller->Supplier->find('all',array(
			'order' => array(),
			'conditions' => array(
				'Supplier.deleted' => 0,
				'Supplier.cascade_id' => $cascadeID,
				)
			)
		);

		if(empty($Suppliers)) return $data;

		$data['Suppliers'] = array();

		$Var2 = $this->_controller->request->projectvars['VarsArray'][2];

		foreach ($Suppliers as $key => $value) {

			$this->_controller->request->projectvars['VarsArray'][2] = $value['Supplier']['id'];
			$Test = $this->CollectSupplierData(array());
			$Test = $this->CollectSupplierData($Test['Expediting']);

			unset($Test['CascadeForBread']);
			unset($Test['emailaddress']);
			unset($Test['BreadcrumpArray']);

			$data['Suppliers'][] = $Test;

		}

		$this->_controller->request->projectvars['VarsArray'][2] = $Var2;

		return $data;
	}

	public function SelectSuplier($data){

		if(!isset($data['AllCascadesUnder'])) return $data;
		if(empty($data['AllCascadesUnder'])) return $data;

		$options['Supplier.deleted'] = 0;
		$options['Supplier.cascade_id'] = $data['AllCascadesUnder'];

		if(isset($this->_controller->request->data['Statistic']['supplier']) && !empty($this->_controller->request->data['Statistic']['supplier'])){

			$options['Supplier.supplier'] = $this->_controller->request->data['Statistic']['supplier'];
			$options['Supplier.supplier !='] = '';

			$data['Statistic']['selected']['supplier'] = $data['SupplierOptionValue'][$this->_controller->request->data['Statistic']['supplier']];
		}

		if(isset($this->_controller->request->data['Statistic']['ordered']) && !empty($this->_controller->request->data['Statistic']['ordered'])){

			if($this->_controller->request->data['Statistic']['ordered'] == 1){
				$options['Supplier.delivery_no'] = '';
			}
			if($this->_controller->request->data['Statistic']['ordered'] == 2){
				$options['Supplier.delivery_no !='] = '';
			}
		}

		if(isset($this->_controller->request->data['Statistic']['planner']) && !empty($this->_controller->request->data['Statistic']['planner'])){

			$options['Supplier.planner'] = $this->_controller->request->data['Statistic']['planner'];
			$options['Supplier.planner !='] = '';

			//$data['PlannerOptionValue'] = array_flip($PlannerOption);
			$data['Statistic']['selected']['planner'] = $data['PlannerOptionValue'][$this->_controller->request->data['Statistic']['planner']];

		}

		if(isset($this->_controller->request->data['Statistic']['area_of_responsibility']) && !empty($this->_controller->request->data['Statistic']['area_of_responsibility'])){

			$options['Supplier.area_of_responsibility'] = $this->_controller->request->data['Statistic']['area_of_responsibility'];
			$options['Supplier.area_of_responsibility !='] = '';

			$data['AreaOfResponsibilityOptionValue'] = array_flip($data['AreaOfResponsibilityOption']);
			$this->_controller->request->data['Statistic']['selected']['area_of_responsibility'] = $data['AreaOfResponsibilityOptionValue'][$this->_controller->request->data['Statistic']['area_of_responsibility']];
		}

		if(count($data['SelectOrderByTestingcomp']) > 0){
			$options['Supplier.order_id'] = $data['SelectOrderByTestingcomp'];
		}

		$this->_controller->paginate = array(
			'conditions' => array($options),
			'order' => array('unit' => 'asc','equipment' => 'asc'),
			'limit' => 500
		);

		$Supplier = $this->_controller->paginate('Supplier');

		$Testingcomp = $this->_controller->Testingcomp->Testingcompcat->find('first',array('conditions' => array('Testingcompcat.identification' => array('sp'))));
		$Testingcomps = Hash::combine($Testingcomp, 'Testingcomp.{n}.id','Testingcomp.{n}.name');

		if(empty($Supplier)) return $data;


		foreach($Supplier as $key => $_Supplier){

			if(isset($Testingcomps[$Supplier[$key]['Supplier']['supplier']])) $Supplier[$key]['Supplier']['supplier'] = $Testingcomps[$Supplier[$key]['Supplier']['supplier']];

			$Expeditings = $this->_controller->Expediting->find('all',array('order' => array('Expediting.sequence ASC'), 'conditions' => array('Expediting.supplier_id' => $_Supplier['Supplier']['id'])));

			$Priority = 5;

			foreach($Expeditings as $__key => $__Expeditings){
				$Expeditings[$__key] = $this->ExpeditingTimePeriode($Expeditings,$__Expeditings,$__key);
				if($Expeditings[$__key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$__key]['Expediting']['priority'];
			}

			$Supplier[$key]['Expediting'] = $Expeditings;

			$Supplier[$key]['Supplier']['priority'] = $Priority;

			if(isset($this->request->data['Statistic']['status']) && !empty($this->request->data['Statistic']['status'])){
				if($Priority != $this->request->data['Statistic']['status']){

					unset($Supplier[$key]);
					continue;

				}
			}

			++$data['StatusMessages']['PriorityStatus'][$data['StatusMessages']['StatusOptionForBread'][$Supplier[$key]['Supplier']['priority']]]['count'];
		}

		$data['Supplier'] = $Supplier;
		$data['Testingcomp'] = $Testingcomp;
		$data['Testingcomps'] = $Testingcomps;

		return $data;
	}

	public function ExpeditingLinks($data){

		// quick and dirty
		$ExpeditingLinks['overview']['controller'] = 'suppliers';
		$ExpeditingLinks['overview']['action'] = 'overview';
		$ExpeditingLinks['files']['controller'] = 'suppliers';
		$ExpeditingLinks['files']['action'] = 'files';
		$ExpeditingLinks['images']['controller'] = 'suppliers';
		$ExpeditingLinks['images']['action'] = 'images';
		$ExpeditingLinks['detail']['controller'] = 'expeditings';
		$ExpeditingLinks['detail']['action'] = 'detail';
		$ExpeditingLinks['control']['controller'] = 'suppliers';
		$ExpeditingLinks['control']['action'] = 'control';
		$ExpeditingLinks['add']['controller'] = 'suppliers';
		$ExpeditingLinks['add']['action'] = 'add';
		$ExpeditingLinks['statistic']['controller'] = 'suppliers';
		$ExpeditingLinks['statistic']['action'] = 'statistic';
		$ExpeditingLinks['index']['controller'] = 'suppliers';
		$ExpeditingLinks['index']['action'] = 'index';
		$ExpeditingLinks['pdf']['controller'] = 'suppliers';
		$ExpeditingLinks['pdf']['action'] = 'pdf';

		$ExpeditingLinks = $this->_controller->Autorisierung->AclCheckLinks($ExpeditingLinks);

		$data['ExpeditingLinks'] = $ExpeditingLinks;

		return $data;
	}

	public function TableFilterOptions($data){

		$data = $this->_TableFilterOptionsSupplier($data);
		$data = $this->_TableFilterOptionsPlanner($data);
		$data = $this->_TableFilterOptionsAreaOfResponsibility($data);

		return $data;
	}

	protected function _TableFilterOptionsAreaOfResponsibility($data){

		if(!isset($data['AllCascadesUnder'])) return $data;
		if(!isset($data['SelectOrderByTestingcomp'])) return $data;

		$AllCascadesUnder = $data['AllCascadesUnder'];
		$SelectOrderByTestingcomp = $data['SelectOrderByTestingcomp'];

		$AreaOfResponsibilityOptionFirst = array(0 => '');
		$AreaOfResponsibilityOption = $this->_controller->Supplier->find('all',array('order' => array('area_of_responsibility'), 'fields' => array('Supplier.area_of_responsibility DISTINCT'),'conditions' => array('Supplier.area_of_responsibility !=' => '','Supplier.deleted' => 0,'Supplier.cascade_id' => $AllCascadesUnder,'Supplier.order_id' => $SelectOrderByTestingcomp)));
		$AreaOfResponsibilityOption = Hash::extract($AreaOfResponsibilityOption, '{n}.Supplier.area_of_responsibility');

		$AreaOfResponsibilityOption = array_merge($AreaOfResponsibilityOptionFirst, $AreaOfResponsibilityOption);

		$data['AreaOfResponsibilityOption'] = $AreaOfResponsibilityOption;

		return $data;
	}

	protected function _TableFilterOptionsPlanner($data){

		if(!isset($data['AllCascadesUnder'])) return $data;
		if(!isset($data['SelectOrderByTestingcomp'])) return $data;

		$AllCascadesUnder = $data['AllCascadesUnder'];
		$SelectOrderByTestingcomp = $data['SelectOrderByTestingcomp'];

		$PlannerOptionFirst = array(0 => '');
		$PlannerOption = $this->_controller->Supplier->find('all',array('order' => array('planner'), 'fields' => array('Supplier.planner DISTINCT'),'conditions' => array('Supplier.planner !=' => '','Supplier.deleted' => 0,'Supplier.cascade_id' => $AllCascadesUnder,'Supplier.order_id' => $SelectOrderByTestingcomp)));
		$PlannerOption = Hash::extract($PlannerOption, '{n}.Supplier.planner');

		$PlannerOption = array_merge($PlannerOptionFirst, $PlannerOption);

		$data['PlannerOption'] = $PlannerOption;

		return $data;
	}

	protected function _TableFilterOptionsSupplier($data){

		if(!isset($data['AllCascadesUnder'])) return $data;
		if(!isset($data['SelectOrderByTestingcomp'])) return $data;

		$AllCascadesUnder = $data['AllCascadesUnder'];
		$SelectOrderByTestingcomp = $data['SelectOrderByTestingcomp'];

		$SupplierOptionFirst = array(0 => '');
		$SupplierOption = $this->_controller->Supplier->find('all',array('order' => array('supplier'), 'fields' => array('Supplier.supplier DISTINCT'),'conditions' => array('Supplier.supplier !=' => '','Supplier.deleted' => 0,'Supplier.cascade_id' => $AllCascadesUnder,'Supplier.order_id' => $SelectOrderByTestingcomp)));
		$SupplierOption = Hash::extract($SupplierOption, '{n}.Supplier.supplier');

		$SupplierOption = array_merge($SupplierOptionFirst, $SupplierOption);

		$data['SupplierOption'] = $SupplierOption;

		return $data;
	}

	public function ExpeditingBreadCrump($data){

		$breads = array();

		$breads[] = array(
						'discription' => __('Expediting manager', true),
						'controller' => 'expeditings',
						'action' => 'index',
						'pass' => null
		);

		if(!isset($data['HeadLineFirst'])) return $breads;

		if(is_array($data['HeadLineFirst'])){

			$breads[] = array(
							'discription' => $data['HeadLineFirst']['discription'],
							'controller' => 'suppliers',
							'action' => 'index',
							'pass' => $data['HeadLineFirst']['pass']
			);

		}

		return $breads;
	}

	public function ChildrenListCorrection($data){

		if(!isset($data['Statistic'])) return $data;

		$ChildrenListCorrection = array();
		$ChildrenListCorrectionArray = array();
		$CurrentLevel = ++$data['CascadeForBread'][0]['Cascade']['level'];

		foreach($data['AllCascadesUnder'] as $key => $value){
			$ChildrenListCorrection[$key] = $this->_controller->Navigation->CascadeGetParentList($value,array());
		}

		foreach($ChildrenListCorrection as $key => $value){
			foreach($value as $__key => $__data){

				if($__data['Cascade']['level'] != $CurrentLevel) continue;

				$ChildrenListCorrectionArray[$__data['Cascade']['id']] = $__data['Cascade']['discription'];
			}
		}

		$data['ChildrenList'] = $ChildrenListCorrectionArray;

		return $data;
	}

	public function ExpeditingStatusMessages($data){

		$output = array();

		$output['PriorityStatus'] = array('critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);

		$output['StatusOptionForBread'] = array_flip($output['PriorityStatus']);

		$output['StatusOption'] = array(1 => __('critical',true),2 => __('delayed',true),3 => __('plan',true),4 => __('future',true),5 => __('finished',true));

		$output['PriorityText'] = array(
						'critical' => __('Critical (soll date > current date + waiting period)',true),
						'delayed' => __('Delayed (soll date > current date and <  current date + waiting period)',true),
						'plan' => __('In plan (soll date < current date)',true),
						'future' => __('No date informations stored',true),
						'finished' => __('Completed',true)
					);

		foreach($output['PriorityStatus'] as $key => $value){

			unset($output['PriorityStatus'][$key]);
			$output['PriorityStatus'][$key]['count'] = 0;
			$output['PriorityStatus'][$key]['text'] = $output['PriorityText'][$key];

		}

		foreach($output['StatusOptionForBread'] as $_key => $_data){
			if(!isset($output['PriorityStatus'][$_data])) unset($output['StatusOption'][$_key]);
		}

		$output['Priority'] = array(1 => 'icon_critical',2 => 'icon_delayed',3 => 'icon_plan',4 => 'icon_future',5 => 'icon_finished');


		$data['StatusMessages'] = $output;

		return $data;
	}

	public function FindNotAssignedOrders(){


		$Output = array();

		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

		$this->_controller->Order->recursive = -1;
		$this->_controller->Cascade->recursive = -1;
		$Order = $this->_controller->Order->find(
			'list',array(
				'conditions'=> array(
					'Order.topproject_id' => $ConditionsTopprojects
				)
			)
		);

		if(count($Order) == 0) return $Output;

		$Supplier = $this->_controller->Supplier->find(
			'list',array(
				'fields' => array('order_id'),
				'conditions' => array(
					'Supplier.order_id' => $Order
				)
			)
		);


		$Diff = array_diff($Order, $Supplier);

		if(count($Diff) == 0) return $Output;

		$Order = $this->_controller->Order->find(
			'list',array(
				'conditions'=> array(
					'Order.deleted' => 0,
					'Order.id' => $Diff
				)
			)
		);

		if(count($Order) == 0) return $Output;

		$Order = $this->_controller->Order->find(
			'list',array(
				'fields' => array('cascade_id'),
				'conditions'=> array(
					'Order.cascade_id !=' => 0,
					'Order.deleted' => 0,
					'Order.id' => $Diff
				)
			)
		);

		$Cascade = $this->_controller->Cascade->find(
			'all',array(
				'conditions'=> array(
					'Cascade.id' => $Order
				)
			)
		);

		$Output = $Order;

		return $Output;

	}

	public function AddXml($Data){

		$xml = $this->_controller->Xml->DatafromXml('display_expediting','file',null);

		return $xml;
	}

	public function MergeExpeditingsToSuppliers($Data){

		foreach($Data['Supplier'] as $key => $value){

//			if(isset($Testingcomps[$Supplier[$key]['Supplier']['supplier']])) $Supplier[$key]['Supplier']['supplier'] = $Testingcomps[$Supplier[$key]['Supplier']['supplier']];

			$Expeditings = $this->_controller->Expediting->find('all',array('order' => array('Expediting.sequence ASC'), 'conditions' => array('Expediting.supplier_id' => $value['Supplier']['id'])));

			$Priority = 5;

			foreach($Expeditings as $_key => $__Expeditings){
				$Expeditings[$_key] = $this->ExpeditingTimePeriode($Expeditings,$__Expeditings,$_key);
				if($Expeditings[$_key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$_key]['Expediting']['priority'];
			}

			$Data['Supplier'][$key]['Expediting'] = $Expeditings;
			$Data['Supplier'][$key]['Supplier']['priority'] = $Priority;

//			++$Data['PriorityStatus'][ $Data['PriorityStatus'][ $Data['Supplier'][$key]['Supplier']['priority'] ] ]['count'];
		}

		return $Data;
	}

	public function SendFilesPerMail($Data){

		if(!isset($this->_controller->request->data['Expediting'])) return $Data;
		if(count($this->_controller->request->data['Expediting']['emailaddress']) == 0) return $Data;

		$MailData['FromMail'] = 'expediting@trmgo.de';
		$MailData['FromName'] = 'TRM - Expediting';
		$MailData['Subject'] = $Data['HeadLine'];
		$MailData['ExpeditingsMessage'] = $this->_controller->request->data['Expediting']['notes'];

		if(Configure::check('ExpeditingFromMail') == true) $MailData['FromMail'] = Configure::read('ExpeditingFromMail');
		if(Configure::check('ExpeditingFromName') == true) $MailData['FromName'] = Configure::read('ExpeditingFromName');

		$EmailaddressesIds = $this->_controller->request->data['Expediting']['emailaddress'];
		$Emailaddresses = array();

		foreach ($EmailaddressesIds as $key => $value) {

			foreach($Data['emailaddress'] as $_key => $_value) {

				if(!isset($_value[$value])) continue;

				$email = explode(' ',$_value[$value]);
				$MailData['ToMail'][] = trim($email[0]);

			}
		}
		
		if(count($MailData['ToMail']) == 0){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('No email adresses available.'));
			return $Data;
		}

		$MailData = $this->_FetchFileInfosForAttachment($MailData,$Data);
		$Data = $this->_controller->Email->sendMailWithAttachment($MailData,$Data);

		return $Data;
	}

	protected function _FetchFileInfosForAttachment($MailData,$Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;
		if(!isset($Data['Supplierfile'])) return $Data;
		if(!isset($Data['Supplierfile']['file_exists'])) return $Data;
		if($Data['Supplierfile']['file_exists'] != 1) return $Data;

		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$savePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

		if(isset($Data['Supplierfile']['file'])){

			$MailData['Attachmets'][$Data['Supplierfile']['basename']]['filesize'] = filesize($savePath . $Data['Supplierfile']['file']);
			$MailData['Attachmets'][$Data['Supplierfile']['basename']]['file'] = $savePath . $Data['Supplierfile']['file'];
			$MailData['Attachmets'][$Data['Supplierfile']['basename']]['mimetype'] = finfo_file($finfo, $savePath . $Data['Supplierfile']['file']);
			$MailData['Attachmets'][$Data['Supplierfile']['basename']]['contentId'] = $Data['Supplierfile']['file'];

		}

		return $MailData;
	}

	public function CollectFiles(){

//		$this->_controller->autoRender = false;

		if(!isset($this->_controller->request->data['Expediting']['files'])) return false;
		if(empty($this->_controller->request->data['Expediting']['files'])) return false;

		if($this->_controller->request->params['action'] == 'sendmanyfiles'){
			if(!isset($this->_controller->request->data['Expediting']['emailaddress'])) return false;
			if(empty($this->_controller->request->data['Expediting']['emailaddress'])) return false;
		}

		if($this->_controller->Session->check('CollectedExpeditingFiles') == true){
			$this->_controller->Session->delete('CollectedExpeditingFiles');
		}

		$Vars = $this->_ProjectVars();

		if($Vars == false) return;

		$Fake[] = array('fake' => 'fake');
		$Data = array();

		$Files = explode(',',$this->_controller->request->data['Expediting']['files']);

		if(count($Files) == 0) return false;

		foreach ($Files as $key => $value) {

			$File = $this->_CollectSupplierSinglefile($Fake,$value);

			unset($File[0]);
			$Data[] = $File;

		}

		$this->_controller->request->projectvars['VarsArray'][3] = 0;

		$savePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

		$Microtime = microtime();
		$Microtime = str_replace(array('.',' '),'_',$Microtime);

		$saveFile = Configure::read('root_folder') .  $Vars['supplierID'] .'_'.$Microtime . '_' . uniqid() . '.zip';

		ob_start();

		$zip = new ZipArchive;

		$zip->open($saveFile,  ZipArchive::CREATE);

		$x = 1;

		foreach ($Data as $key => $value) {

			if($value['Supplierfile']['file_exists'] != 1) continue;

			$zip->addFile($savePath . $value['Supplierfile']['file'], $x . '_' . $value['Supplierfile']['basename']);

			$x++;

		}

		$zip->close();

		$file = $saveFile;

		while (ob_get_level()) {
			ob_end_clean();
		}

		if(!is_file($file)) return false;
		if(!is_readable($file)) return false;

		$this->_controller->Session->write('CollectedExpeditingFiles', $file);

		if($this->_controller->Session->check('CollectedExpeditingFiles') == true) return true;

	}

	public function SendFiles(){

		if(isset($this->_controller->request->data['Suppliers']['files'])) return false;
		if($this->_controller->Session->check('CollectedExpeditingFiles') == false) return false;

		$this->_controller->autoRender = false;

		$file = $this->_controller->Session->consume('CollectedExpeditingFiles');

		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/zip");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($file));
		header("Content-Disposition: attachment; filename=\"".basename($file)."\"");

		readfile($file);
		unlink($file);

	}

	public function SendManyFilesPerMail($Data){

		if(!isset($this->_controller->request->data['Expediting'])) return $Data;
		if(!isset($Data['Supplierfile'])) return $Data;
		if(count($Data['Supplierfile']) == 0) return $Data;

		if(!isset($this->_controller->request->data['Expediting']['emailaddress'])) return $Data;
		if(empty($this->_controller->request->data['Expediting']['emailaddress'])){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('No email adresses available.'));
			return $Data;
		}

		$MailData['FromMail'] = 'expediting@trmgo.de';
		$MailData['FromName'] = 'TRM - Expediting';
		$MailData['Subject'] = $Data['HeadLine'];
		$MailData['ExpeditingsMessage'] = $this->_controller->Auth->user('name') . ' ' . __('sends you a file ') . "\n\n";
		$MailData['ExpeditingsMessage'] .= $this->_controller->request->data['Expediting']['notes'];

		if(Configure::check('ExpeditingFromMail') == true) $MailData['FromMail'] = Configure::read('ExpeditingFromMail');
		if(Configure::check('ExpeditingFromName') == true) $MailData['FromName'] = Configure::read('ExpeditingFromName');

		$EmailaddressesIds = $this->_controller->request->data['Expediting']['emailaddress'];
		$Emailaddresses = array();

		foreach ($EmailaddressesIds as $key => $value) {

			if(isset($Data['emailaddress'][$value])){
				$email = explode(' ',$Data['emailaddress'][$value]);
				$MailData['ToMail'][] = trim($email[0]);
			}
		}

		if(count($MailData['ToMail']) == 0){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('No email adresses available.'));
			return $Data;
		}

//		$Data = $this->_FetchZipArchivAndSend($Data,$MailData);
		$Data = $this->_FetchFilesAndSend($Data,$MailData);

		return $Data;

	}

	protected function _FetchFilesAndSend($Data,$MailData){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		unset($Data['FlashMessages']);

		$MaxFileSizeSingle = 10000000; // 10 MB
		$MaxFileSizeAll = 20000000; // 20 MB
		$FileSizeAll = 0;

		$Data = $this->_AndSend($Data,$MailData,$FileSizeAll);

		return $Data;
	}

	protected function _AndSend($Data,$MailData,$FileSizeAll){

		if(count($Data['Supplierfile']) == 0) return $Data;

		$Vars = $this->_ProjectVars();

		$MaxFileSizeSingle = 10000000; // 10 MB
		$MaxFileSizeAll = 20000000; // 20 MB

		$savePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$x = 1;

		foreach ($Data['Supplierfile'] as $key => $value) {

			if(!isset($value['Supplierfile']['file_exists'])) continue;
			if($value['Supplierfile']['file_exists'] != 1) continue;

			if($value['Supplierfile']['filesize'] > $MaxFileSizeSingle){

				$Data['FlashMessages'][]  = array('type' => 'error','message' => $value['Supplierfile']['basename'] . ' ' . __('is larger than') . ' ' . $this->HumanFilesize($MaxFileSizeSingle) . ' ' . __('and cannot be sent.'));
				continue;
			}

			$FileSizeAll += $value['Supplierfile']['filesize'];

			if($FileSizeAll > $MaxFileSizeAll){
				$FileSizeAll = 0;
				break;
			}

			$MailData['Attachmets'][$x . '_' . $value['Supplierfile']['basename']]['filesize'] = $value['Supplierfile']['filesize'];
			$MailData['Attachmets'][$x . '_' . $value['Supplierfile']['basename']]['file'] = $savePath . $value['Supplierfile']['file'];
			$MailData['Attachmets'][$x . '_' . $value['Supplierfile']['basename']]['mimetype'] = finfo_file($finfo, $savePath . $value['Supplierfile']['file']);
			$MailData['Attachmets'][$x . '_' . $value['Supplierfile']['basename']]['contentId'] = $value['Supplierfile']['file'];

			unset($Data['Supplierfile'][$key]);

			$x++;

		}

		if(!isset($MailData['Attachmets'])){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The email could not be sent'));
			return $Data;

		}

		$Data = $this->_controller->Email->sendMailWithAttachment($MailData,$Data);

		unset($MailData['Attachmets']);

		$Data = $this->_AndSend($Data,$MailData,$FileSizeAll);

		return $Data;

	}

	protected function _FetchZipArchivAndSend($Data,$MailData){

		if($this->_controller->Session->check('CollectedExpeditingFiles') == false){

			unset($Data['FlashMessages']);
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('No Data found.'));

			return $Data;
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$file = $this->_controller->Session->consume('CollectedExpeditingFiles');

		$MailData['Attachmets']['file.zip']['filesize'] = filesize($file);
		$MailData['Attachmets']['file.zip']['file'] = $file;
		$MailData['Attachmets']['file.zip']['mimetype'] = finfo_file($finfo, $file);
		$MailData['Attachmets']['file.zip']['contentId'] = $file;

		$Data = $this->_controller->Email->sendMailWithAttachment($MailData,$Data);

		unlink($file);

		return $Data;
	}

	function HumanFilesize($bytes, $decimals = 2) {
	  $sz = 'BKMGTP';
	  $factor = floor((strlen($bytes) - 1) / 3);
	  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public function CheckArrayDiff($Data1,$Data2,$Keys){

		$Return = array();
		$Whitelist = array('modified');

		if(!is_array($Keys)) return $Return;
		if(count($Keys) == 0) return $Return;

		foreach ($Keys as $key => $value) {

			if(!isset($Data1[$value])) continue;
			if(!isset($Data2[$value])) continue;

			$Check1 = array_diff($Data1[$value],$Data2[$value]);
			$Check2 = array_diff($Data2[$value],$Data1[$value]);

			if(!empty($Check1) && !empty($Check2)) $Return[$value] = array_merge($Check1,$Check2);
			if(!empty($Check1) && empty($Check2)) $Return[$value] = $Check1;
			if(empty($Check1) && !empty($Check2)) $Return[$value] = $Check2;
			else $Return[$value] = $Check1;

		}

		foreach ($Return as $key => $value) {

			foreach ($Whitelist as $_key => $_value) if(isset($value[$_value])) unset($Return[$key][$_value]);

			if(count($Return[$key]) == 0) unset($Return[$key]);

		}

		return $Return;

	}

	public function ExpeditingSelect($data){

		$data['ExpeditingSelect'] = array(
			'controller' => 'suppliers',
			'action' => 'index',
		);

		return $data;
	}

	public function ExpeditingNavi($data){

		$ParentCascaeTerm = $this->_controller->request->projectvars['VarsArray'];
		$ParentCascaeTerm[1] = $data['ParentCasace'];
		$ParentCascaeTerm[2] = 0;

		$data['ExpeditingNavi']['StatisticLink'] = array(
			'discription' => __('Show statistic of this selection',true),
			'controller' => 'suppliers',
			'action' => 'statistic',
			'pass' => $this->_controller->request->projectvars['VarsArray'],
			'class' => 'expediting_navi icon_statistik',
		);

		$data['ExpeditingNavi']['AddLink'] = array(
			'discription' => __('Add Expediting',true),
			'controller' => 'suppliers',
			'action' => 'add',
			'pass' => $ParentCascaeTerm,
			'class' => 'modal icon_add',
		);

		if($data['ParentCasace'] > 0){

			$data['ExpeditingNavi']['UpwardLink'] = array(
				'discription' => __('Show parent cascade',true),
				'controller' => 'suppliers',
				'action' => 'index',
				'pass' => $ParentCascaeTerm,
				'class' => 'expediting_navi icon_upward',
			);

		}

		return $data;
	}

	public function CollectSupplierDataStatistic($id = 0){

		$Data = array();

		$Vars = $this->_ProjectVars();

		$Data = $this->_CollectSupplierDetails($Data,$id);
		$Data = $this->_CollectRkls($Data);
		$Data = $this->_CollectOrders($Data);
		$Data = $this->_CollectExpeditingDetails($Data,$id);
		$Data = $this->_CollectExpeditingTyps($Data);
		$Data = $this->_SupplierExpediting($Data);

		unset($Data['emailaddress']);
		unset($Data['ToMail']);
		unset($Data['Emailaddress']);
		unset($Data['Testingcomp']);

		return $Data;
	}

	protected function _SupplierExpediting($data){

		
		if(!isset($data['Expediting'])) return $data;
		if(count($data['Expediting']) == 0) return $data;

		$Priority = 5;
		$ExpeditingTypes = $data['ExpeditingTypes'];

		if(!isset($data['Expeditingstatistic'])) $data['Expeditingstatistic'] = array();

		foreach($data['Expediting'] as $key => $value){

			$ExpeditingType = $value['Expediting']['expediting_type_id'];
			
			if($data['Expediting'][$key]['Expediting']['priority'] < $Priority) $Priority = $data['Expediting'][$key]['Expediting']['priority'];

			$data['Supplier']['priority'] = $Priority;

			if(!isset($data['Expeditingstatistic'][$ExpeditingTypes[$ExpeditingType]][$data['Expediting'][$key]['Expediting']['class']])) $data['Expeditingstatistic'][$ExpeditingTypes[$ExpeditingType]][$data['Expediting'][$key]['Expediting']['class']] = 0;

			++$data['Expeditingstatistic'][$ExpeditingTypes[$ExpeditingType]][$data['Expediting'][$key]['Expediting']['class']];

		}

		return $data;
	}

	public function SupplierOutputs($Output,$Supplier){

		if(!isset($Supplier['Expediting'])) return $Output;
		if(count($Supplier['Expediting']) == 0) return $Output;

		$SupplierId = $Supplier['Supplier']['id'] ;
		$Expediting = $Supplier['Expediting'] ;
		$StatusTotalArray = array('total' => 0,'critical' => 0,'delayed' => 0,'plan' => 0,'future' => 0,'finished' => 0);


		if(!isset($Output['Output'][$SupplierId])) $Output['Output'][$SupplierId] = array();
		if(!isset($Output['OutputDetail'][$SupplierId])) $Output['OutputDetail'][$SupplierId] = array();

		foreach($Expediting as $key => $data){

			$ExpeditingType = $data['Expediting']['expediting_type_id'];

			if(!isset($Output['Output'][$SupplierId][$ExpeditingType])){

				$Output['Output'][$SupplierId][$ExpeditingType] = $StatusTotalArray;

			} 

			++$Output['Output'][$SupplierId][$ExpeditingType][$Expediting[$key]['Expediting']['class']];

		}

		return $Output;
	}

	public function SupplierOutput($data,$Supplier){

		$PriorityStatus = array('total' => 1,'critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);
		$PriorityStatusFlip = array_flip($PriorityStatus);

		$ExpeditingTypes = $this->_CollectExpeditingTypsAll($Supplier);
		$ExpeditingTypes = $ExpeditingTypes['ExpeditingTypes'];

		$Name = $Supplier['Supplier']['unit'];
		$Id = $Supplier['Supplier']['id'];

		$_data = $data['Output'][$Id];
		$_key = $Id;

//		foreach($Output as $_key => $_data){

			$StatusTotalArray = array('total' => 0,'critical' => 0,'delayed' => 0,'plan' => 0,'future' => 0,'finished' => 0);
			$ExpeditingOptionCount = count($ExpeditingTypes);

			foreach($_data as $__key => $__data){

				$data['Output'][$_key][$__key]['total'] = count($Supplier['Expediting']);

				if(
					isset($this->request->data['Statistic']['status']) &&
					!empty($this->request->data['Statistic']['status']) &&
					isset($data['Output'][$_key][$__key][$PriorityStatusFlip[$this->request->data['Statistic']['status']]]) &&
					$data['Output'][$_key][$__key][$PriorityStatusFlip[$this->request->data['Statistic']['status']]] < 1
					){
					unset($data['Output'][$_key][$__key]);
					--$ExpeditingOptionCount;
				}

				


				foreach($__data as $___key => $___data){

					if(isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
						if($PriorityStatusFlip[$this->request->data['Statistic']['status']] != $___key) continue;
					}

					//$StatusTotalArray[$___key] += @count($data['OutputDetail'][$_key][$__key][$___key]);
				}
			}

			$StatusTotalArray['total'] = count($Supplier['Expediting']);

			$data['Output'][$_key][__('Total',true)] = $StatusTotalArray;

			if(isset($_key) && $data['Output'][$_key][__('Total',true)]['total'] == 0) {
				unset($data['Output'][$_key]);
//				continue;
			}

			if(count($data['Output'][$_key]) == 1 && key($data['Output'][$_key]) == __('Total',true)) unset($data['Output'][$_key]);

			if(isset($data['Output'][$_key]) && count($data['Output'][$_key]) == 2 && isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
				unset($data['Output'][$_key][__('Total',true)]);
				$ThisKey = key($data['Output'][$_key]);
				$FirstElement = array_shift($data['Output'][$_key]);
				$data['Output'][$_key][$ThisKey] = $FirstElement;
				$data['Output'][$_key][__('Total',true)] = $FirstElement;
			}
//		}

		$data['Output'][$Name]['Expeditings'] = $data['Output'][$Id];

		unset($data['Output'][$Name]['Expeditings']['Gesamnt']);

		foreach($data['Output'][$Name]['Expeditings'] as $key => $value){

			unset($value['total']);
			unset($data['Output'][$Name]['Expeditings'][$key]);

			if(isset($ExpeditingTypes[$key])) $data['Output'][$Name]['Expeditings'][$ExpeditingTypes[$key]] = $value;

		}

		ksort($data['Output'][$Name]['Expeditings']);

		$data['Output'][$Name]['Supplier'] = $Supplier['Supplier'];

		unset($data['Output'][$Id]);

		return $data;
	}

	// Löschen
	public function SupplierReplaceName($Output,$Statistic,$Supplier){

		$Return = array();
		$PriorityStatus = array('total' => 0,'critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);

		$ExpeditingTypes = $this->_CollectExpeditingTypsAll($Supplier);
		$ExpeditingTypes = $ExpeditingTypes['ExpeditingTypes'];

		foreach($Output['Output'] as $key => $value){

			$SupplierId = $Supplier['Supplier']['id'];
			$ThisSupplier = $Supplier;

			unset($value['Gesamt']);

			$Return[$Supplier['Supplier']['unit']]['Expeditings'] = $value;
			$Return[$Supplier['Supplier']['unit']]['Supplier'] = $ThisSupplier['Supplier'];

			foreach($Return[$Supplier['Supplier']['unit']]['Expeditings'] as $_key => $_value){

				unset($_value['total']);

				if(!isset($ExpeditingTypes[$_key])) continue;

				foreach($PriorityStatus as $__key => $__value){

					if(!isset($_value[$__key])) $_value[$__key] = 0;

				}

				$Return[$Supplier['Supplier']['unit']]['Expeditings'][$ExpeditingTypes[$_key]] = array();

				unset($Return[$Supplier['Supplier']['unit']]['Expeditings'][$_key]);

			}	

		}

		$Returnkeys = array_keys($Return);
		natsort($Returnkeys);

		$ReturnSort = array();

		foreach($Returnkeys as $key => $value){
			$ReturnSort[$value] = $Return[$value];
		}

//		$Output['Output'] = $ReturnSort;
		$Statistic['Output'] = array_merge($Statistic['Output'], $ReturnSort);


		return $Statistic;
	}

	public function CollectSupplierData($id = 0){

		$Data = array();

		$Vars = $this->_ProjectVars();

		$Data = $this->_CollectSupplierDetails($Data,$id = 0);
		$Data = $this->_CollectRkls($Data);
		$Data = $this->_CollectOrders($Data);
		$Data = $this->_CollectExpeditingDetails($Data);
		$Data = $this->_CollectExpeditingTyps($Data);
		$Data = $this->_CollectSupplierfile($Data);
		$Data = $this->_CollectSupplierimage($Data,300,300);
		$Data = $this->_AddTimePeriodes($Data);

		$Data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($Vars['cascadeID']));

		$Data = $this->_ParentCasace($Data);
		$Data = $this->_HeadLine($Data);

		$Data = $this->_Submenue($Data);

		return $Data;
	}

	public function FormatHeadline($data){
		
		if(!isset($data['HeadLine'])) return$data;

		$data['HeadLineFirst'] = array_shift($data['HeadLine']);

		$data['HeadLine'] = Hash::extract($data['HeadLine'], '{n}.discription');
		$data['HeadLine'] = implode(' > ',$data['HeadLine']);

		return $data;
	}

	public function SubmenueOnlyCascades($data){

		$Controller = 'suppliers';
		$Action = 'index';

		$Output = array();

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	
		if(Configure::check('ExpeditingStartCascade') && Configure::read('ExpeditingStartCascade') == 0) {
			
			$Output[0]['title'] = __('Overview',true);
			$Output[0]['controller'] = $Controller;
			$Output[0]['action'] = $Action;
			$Output[0]['params'] = array(0,1);
			$Output[0]['class'] = 'ajax home_link ';
	
		}

		if(!isset($data['CascadeForBread'])) return $data;

		if(!is_array($data['CascadeForBread'])) return $data;
		if(count($data['CascadeForBread']) == 0){
			
			$data['BreadcrumpArray'] = $Output;
			return $data;

		} 

		asort($data['CascadeForBread']);

		$x = 1;
		foreach ($data['CascadeForBread'] as $key => $value) {

			$Class = 'ajax ';

			if($x == 0) $Class .= 'home_link ';

			$Output[$x]['title'] = $value['Cascade']['discription'];
			$Output[$x]['controller'] = $Controller;
			$Output[$x]['action'] = $Action;
			$Output[$x]['params'] = array($value['Cascade']['topproject_id'],$value['Cascade']['id']);
			$Output[$x]['class'] = $Class;

			$x++;
		}

		$data['BreadcrumpArray'] = $Output;

		return $data;

	}

	public function DuplicateSupplierData($Data){

		if(!isset($this->_controller->request->data['Supplier'])) return null;

		if(!isset($Data['Supplier'])) return false;

		$unset = array('Xml','BreadcrumpArray','HeadLine','HeadLineFirst','CascadeForBread','Supplierimage','Supplierfile','ExpeditingTypes','emailaddress');

		foreach ($unset as $key => $value) {

			if(!isset($Data[$value])) continue;
			unset($Data[$value]);

		}

		foreach ($Data['Expediting'] as $key => $value) {

			unset($Data['Expediting'][$key]['ExpeditingEvent']);

		}

		$Data = $this->_CollectExpeditingRolls($Data);

		$equipment = $this->_controller->request->data['Supplier']['equipment'];

		$Data['Supplier']['equipment'] = $equipment;
		$Data['Order']['auftrags_nr'] = $equipment;

		unset($Data['Supplier']['id']);
		unset($Data['Order']['id']);

		$Data['Supplier']['order_id'] = 0;

		if($this->_controller->Order->save($Data['Order'])){

			$OrderId = $this->_controller->Order->getLastInsertID();

			$Order = $this->_controller->Order->find('first',array('conditions' => array('Order.id' => $OrderId)));

			$Data['Order'] = $Order;
			$Data['Supplier']['order_id'] = $OrderId;

		} else {

			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));
			return false;

		}

		$this->_controller->Supplier->create();

		if($this->_controller->Supplier->save($Data['Supplier'])){

			$SupplierId = $this->_controller->Supplier->getLastInsertID();

			$Supplier = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.id' => $SupplierId)));
			$Data['Supplier'] = $Supplier;

		} else {

			$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));
			return false;

		}

		if(!isset($Data['Expediting'])) return $Data;

		foreach ($Data['Expediting'] as $key => $value) {

			unset($Data['Expediting'][$key]['Expediting']['id']);

			$Data['Expediting'][$key]['Expediting']['supplier_id'] = $SupplierId ;
			$Data['Expediting'][$key]['Expediting']['finished'] = 0;
			$Data['Expediting'][$key]['Expediting']['trend'] = 0;
			$Data['Expediting'][$key]['Expediting']['status'] = 0;
			$Data['Expediting'][$key]['Expediting']['date_ist'] = '';
			$Data['Expediting'][$key]['Expediting']['date_current'] = '';
			$Data['Expediting'][$key]['Expediting']['remark'] = '';

			$this->_controller->Expediting->create();
			$Check = $this->_controller->Expediting->save($Data['Expediting'][$key]);

			if($Check === false){

				$this->_controller->Flash->error(__('An error has occurred.'),array('key' => 'error'));
				return false;

			}

		}

		$FormName = array();
		$FormName['controller'] = 'suppliers';
		$FormName['action'] = 'overview';

		$Terms = array($this->_controller->request->projectvars['VarsArray'][0],$this->_controller->request->projectvars['VarsArray'][1],$SupplierId);

		$FormName['terms'] = implode('/',$Terms);

		$this->_controller->set('FormName',$FormName);

		$this->_controller->Flash->success(__('Value was saved'),array('key' => 'success'));


		return $Data;
	}

	public function FirstSubmenueOnlyCascades($data){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		if(count($data['BreadcrumpArray']) > 0) return $data;

		$output = array('1' => 
			array(
			'title' => __('Overview'),
			'controller' => 'suppliers',
			'action' => 'index',
			'params' => array(0,1),
			'class' => 'ajax',	
			)
		);

		$data['BreadcrumpArray'] = $output;

		return $data;
	}

	public function CollectSupplierDataAdd(){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		$data = array();

		$this->_controller->Cascade->recursive = -1;

		$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $cascadeID)));

		$data['Cascade'] = $Cascade['Cascade'];

		$data = $this->_CollectSupplierDetailsAdd($data);
		$data = $this->_CollectExpeditingTypsAll($data);
		$data = $this->_CollectOrders($data);
		$data = $this->_CollectRkls($data);

		$data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($cascadeID));

		$data = $this->_ParentCasace($data);
		$data = $this->_HeadLine($data);

		$data = $this->_Submenue($data);

		pr($data);

		return $data;
	}

	public function CollectExpeditingset(){

		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return array();

		$ExpeditingSetId = $this->_controller->request->projectvars['VarsArray'][3];

		$Expeditingset = $this->_controller->Expeditingset->find('first',array(
			'conditions' => array(
					'Expeditingset.id' => $ExpeditingSetId
				)
			)
		);

		return $Expeditingset;
	}

	public function CollectCurrentExpeditingset(){

		if($this->_controller->request->projectvars['VarsArray'][3] == 0) return array();
		if($this->_controller->request->projectvars['VarsArray'][4] == 0) return array();

		$ExpeditingSetId = $this->_controller->request->projectvars['VarsArray'][3];
		$ExpeditingStepId = $this->_controller->request->projectvars['VarsArray'][4];

		$data = array();

		$Expeditingset = $this->_controller->Expeditingset->find('first',array(
			'conditions' => array(
					'Expeditingset.id' => $ExpeditingSetId,
				)
			)
		);

		if(count($Expeditingset) == 0) return $data;

		$data['Expeditingset'] = $Expeditingset['Expeditingset'];
		$data['ExpeditingSetsExpeditingRoll'] = $Expeditingset['ExpeditingSetsExpeditingRoll'];

		$ExpeditingSetsExpeditingType = $this->_controller->Expeditingset->ExpeditingSetsExpeditingType->find('first',array(
			'conditions' => array(
					'ExpeditingSetsExpeditingType.id' => $ExpeditingStepId,
				)
			)
		);

		if(count($ExpeditingSetsExpeditingType) == 0) return $data;

		$data['ExpeditingSetsExpeditingType'] = $ExpeditingSetsExpeditingType['ExpeditingSetsExpeditingType'];

		$data['Roll'] = Hash::extract($data['ExpeditingSetsExpeditingRoll'], '{n}[expediting_set_id='.$ExpeditingSetId.'][expediting_type_id='.$data['ExpeditingSetsExpeditingType']['expediting_type_id'].'].roll_id');

		$ExpeditingType = $this->_controller->ExpeditingType->find('first',
			array(
				'conditions' => array(
					'ExpeditingType.id' => $ExpeditingSetsExpeditingType['ExpeditingSetsExpeditingType']['expediting_type_id'],
					'ExpeditingType.deleted' => 0,
				)
			)
		);

		$data['ExpeditingSetsExpeditingType']['description'] = $ExpeditingType['ExpeditingType']['description'];

		$data = $this->_CollectAllRolls($data);

		return $data;
	}

	public function CollectSupplierDataEdit(){

		$Data = array();

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetails($Data);
		$Data = $this->_CollectExpeditingTypsAll($Data);
		$Data = $this->_CollectExpeditingTypCurrent($Data);
		$Data = $this->_CollectOrders($Data);
		$Data = $this->_CollectRkls($Data);
		$Data = $this->_CollectEmailToExpedingRoll($Data);

		$Data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($Vars['cascadeID']));

		$Data = $this->_ParentCasace($Data);
		$Data = $this->_HeadLine($Data);

		$Data = $this->_Submenue($Data);

		return $Data;
	}

	public function CollectSupplierDataExpeditingAdd(){

		$Data = array();

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetails($Data);
		$Data = $this->_CollectAllExpeditingTyps($Data);
		$Data = $this->_CollectExpeditingRolls($Data);
		$Data = $this->_CollectExpeditingAddAdminRolls($Data);
		$Data = $this->_CollectOpenExpeditingSteps($Data);

		$Data['Expediting']['date_soll'] = '0000-00-00';
		$Data['Expediting']['date_ist'] = '0000-00-00';

		return $Data;

	}

	public function CollectSupplierDataExpeditingEdit(){

		$Data = array();

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetail($Data);
		$Data = $this->_CollectExpeditingTyps($Data);

		$Data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($Vars['cascadeID']));

		$Data = $this->_ParentCasace($Data);
		$Data = $this->_HeadLine($Data);

		$Data = $this->_Submenue($Data);

		return $Data;

	}

	public function CollectSupplierDataSingleModal($type){

		$Data = array();

		$Vars = $this->_ProjectVars();
		if($Vars == false) return $Data;

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectOrders($Data);

		if($type == 'image') $Data = $this->_CollectSupplierSingleimage($Data,$Vars['medienID'],500,800);

		if($type == 'file'){

			$Data = $this->_CollectSupplierSinglefile($Data,$Vars['medienID']);
			$Data = $this->_CollectSupplierMassactionfiles($Data);

		}

		$Data = $this->_CollectExpeditingDetail($Data);

		$Data= $this->_controller->Data->BelongsToManySelected($Data,'Expediting','Roll',array('ExpeditingsRolls','roll_id','expediting_id'));
		$Data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($Vars['cascadeID']));

		$Data = $this->_HeadLine($Data);

		if($type == 'image' && isset($Data['Supplierimage']['basename'])) $Data['HeadLine'] .= ' > ' . $Data['Supplierimage']['basename'];
		if($type == 'file' && isset($Data['Supplierfile']['basename'])) $Data['HeadLine'] .= ' > ' . $Data['Supplierfile']['basename'];

		return $Data;
	}

	public function CollectExpeditingEventsSingleModal(){

		$Data = array();

		$Vars = $this->_ProjectVars();
		if($Vars == false) return $Data;

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetail($Data);
		$Data = $this->CollectExpeditingDetailByExpeditingType($Data);

		$Data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($Vars['cascadeID']));

		$Data = $this->_HeadLine($Data);

		return $Data;
	}

	public function SendMessageAfterEdit($Data,$DataBefore){

		if(!isset($this->_controller->request->data['Expediting'])){

			if($Data['Supplier']['stop_mail'] == 1){
				$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('The email delivery for this equipment is disabled.',true));
				return $Data;
			}

			if(isset($Data['Expediting']['stop_mail']) && $Data['Expediting']['stop_mail'] == 1){
				$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('The email delivery for this expediting step is disabled.',true));
				return $Data;
			}

			if(!isset($Data['ToMail'])){
				$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No email adresses available.'));
				return $Data;
			}

			if(count($Data['ToMail']) == 0){
				$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No email adresses available.'));
				return $Data;
			}

			$Data = $this->CheckEmailRoll($Data);

			if(count($Data['ToMail']) > 0){
				$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('An email with information about the changes will been sent to the following email addresses:',true) . ' ' . implode(', ',$Data['ToMail']));
			} else {
				$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No email adresses available.',true));
			}


			return $Data;

		}

		if($Data['Expediting']['stop_mail'] == 1) return $Data;

		$CheckArrayDiff = $this->CheckArrayDiff($Data,$DataBefore,array('Supplier','Expediting'));
		$Data = $this->_CollectOrders($Data);

//		if(count($CheckArrayDiff) == 0) return $Data;

		$MailData['FromMail'] = 'expediting@trmgo.de';
		$MailData['FromName'] = 'TRM - Expediting';

		if(Configure::check('ExpeditingFromMail') == true) $MailData['FromMail'] = Configure::read('ExpeditingFromMail');
		if(Configure::check('ExpeditingFromName') == true) $MailData['FromName'] = Configure::read('ExpeditingFromName');

		$MailData['Subject'] = $Data['HeadLine'];

		$MailData['ExpeditingsMessage'] = NULL;

		if(isset($this->_controller->request->data['Expediting'])) $MailData = $this->_CreateExpeditingMessage($MailData,$Data,$DataBefore,$CheckArrayDiff);

		$ExpeditingsRoll = $this->_controller->ExpeditingsRoll->find('all',array('conditions' => array('expediting_id' => $Data['Expediting']['id'])));
		$Data['ExpeditingsRoll'] = Hash::extract($ExpeditingsRoll, '{n}.ExpeditingsRoll.roll_id');

		if(count($Data['ExpeditingsRoll']) == 0) return $Data;

		$StopMail = false;

		$Data = $this->_controller->Data->BelongsToManySelected($Data,'Order','Testingcomp',array('OrdersTestingcomps','testingcomp_id','order_id'));
		$Data = $this->_controller->Data->BelongsToManySelected($Data,'Order','Emailaddress',array('OrdersEmailadresses','emailadress_id','order_id'));

		$Options = array(
			'conditions' => array(
				'Emailaddress.roll' => $Data['ExpeditingsRoll'],
				'Emailaddress.id' => $Data['Emailaddress']['selected']
			),
			'fields' => array('id','email')
		);

		$MailData['ToMail'] = $this->_controller->Order->Emailaddress->find('list',$Options);

		if(count($MailData['ToMail']) == 0) return $Data;
		if($MailData['ExpeditingsMessage'] == NULL) return $Data;

		$Data['ToMail'] = $MailData['ToMail'];

		$SendMesage = $this->_controller->Email->sendStatusExpeditingMail($MailData,$Data);

		if($SendMesage == false) $Data['FlashMessages'][]  = array('type' => 'error','message' => __('An error occurred while sending the mail.',true));
		else $Data['FlashMessages'][]  = array('type' => 'success','message' => __('An email with information about the changes has been sent to the following email addresses:',true) . ' ' . implode(', ',$Data['ToMail']));

		return $Data;
	}


	public function CheckEmailRoll($data){

		if(!isset($data['ToMail'])) return $data;
		if(empty($data['ToMail'])) return $data;

		foreach ($data['ToMail'] as $key => $value) {

			$Emailaddress = $this->_controller->Emailaddress->find('first',
				array(
					'conditions' => array(
						'Emailaddress.id' => $key,
						'roll' => $data['Roll']['selected']
					)
				)
			);

			if(empty($Emailaddress)) unset($data['ToMail'][$key]);

		}


		return $data;
	}

	public function SendMessageAfterUpload($Data){

		if($Data['Supplier']['stop_mail'] == 1){
			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('The email delivery for this equipment is disabled.',true));
			return $Data;
		}

		if($Data['Expediting']['stop_mail'] == 1){
			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('The email delivery for this expediting step is disabled.',true));
			return $Data;
		}

		if(!isset($Data['ToMail'])){
			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No email adresses available.'));
			return $Data;
		}

		if(count($Data['ToMail']) == 0){
			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No email adresses available.'));
			return $Data;
		}

		$MailData['FromMail'] = 'expediting@trmgo.de';
		$MailData['FromName'] = 'TRM - Expediting';

		if(Configure::check('ExpeditingFromMail') == true) $MailData['FromMail'] = Configure::read('ExpeditingFromMail');
		if(Configure::check('ExpeditingFromName') == true) $MailData['FromName'] = Configure::read('ExpeditingFromName');

		$MailData['Subject'] = $Data['HeadLine'] . ' ' . $Data['Expediting']['description']  . ' ' . __('File upload',true);

		$MailData['ExpeditingsMessage'] = $Data['MailMessage'];

		$MailData['ToMail'] = $Data['ToMail'];

		$StopMail = false;

		if(count($MailData['ToMail']) == 0) return $Data;
		if($MailData['ExpeditingsMessage'] == NULL) return $Data;

		$SendMesage = $this->_controller->Email->sendStatusExpeditingMail($MailData,$Data);

		if($SendMesage == false) $Data['FlashMessages'][]  = array('type' => 'error','message' => __('An error occurred while sending the mail.',true));
		else $Data['FlashMessages'][]  = array('type' => 'success','message' => __('An email with information about the changes has been sent to the following email addresses:',true) . ' ' . implode(', ',$Data['ToMail']));

		return $Data;
	}

	public function ExpeditingFileDelete($Data){

		$Vars = $this->_ProjectVars();
		if($Vars == false) return $Data;

		if(!isset($this->_controller->request->data['Expediting'])) return $Data;
		if(!isset($this->_controller->request->data['Expediting']['delete'])) return $Data;
		if($this->_controller->request->data['Expediting']['delete'] != 1) return $Data;
		if(!isset($this->_controller->request->data['Expediting']['files'])) return $Data;
		if(empty($this->_controller->request->data['Expediting']['files'])) return $Data;

		if(!isset($Data['Supplierfile'])) return $Data;
		if(count($Data['Supplierfile']) == 0) return $Data;

		unset($Data['FlashMessages']);

		$delPath = Configure::read('supplier_folder') . $Data['Supplier']['id'] . DS;

		foreach ($Data['Supplierfile'] as $key => $value) {

			if($this->_controller->Supplierfile->delete($value['Supplierfile']['id'])){

				$delFile = new File($delPath . $value['Supplierfile']['file']);

				if(file_exists($delFile->path)) $delFile->delete();

			} else {

				$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The following file could not be deleted') . ': ' . $value['Supplierfile']['basename']);
				return $Data;

			}

			$Data['FlashMessages'][]  = array('type' => 'success','message' => __('The following file was deleted') . ': ' . $value['Supplierfile']['basename']);

		}

		$FormName['controller'] = 'suppliers';
		$FormName['action'] = 'overview';
		$Terms = implode('/',$this->_controller->request->projectvars['VarsArray']);
		$FormName['terms'] = $Terms;
		$this->_controller->set('FormName',$FormName);

		return $Data;
	}

	public function ExpeditingFileUpload($Data,$Type){

		$Vars = $this->_ProjectVars();
		if($Vars == false) return $Data;

		if(!isset($_FILES)) return $Data;
		if(count($_FILES) == 0) return $Data;

		$Data = $this->CollectExpeditingDetailByExpeditingType($Data);

		$ImageExtension = $this->_GetFileExtensions($Type);

		$savePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS ;
		$fileinfo = pathinfo($_FILES['file']['name']);

		if(!isset($ImageExtension[$fileinfo['extension']])) return $Data;

		if($_FILES['file']['error'] > 0){

			$max_upload = (int)(ini_get('upload_max_filesize'));
			$Data['FlashMessages'][]  = array('type' => 'error','message' => Configure::read('FileUploadErrors.' . $_FILES['data']['error']['Supplier']['file']) . ' ' . __('Your file is bigger than',true) . ' ' . $max_upload . 'MB');

			return $Data;
		}

		$Microtime = microtime();
		$Microtime = str_replace(array('.',' '),'_',$Microtime);

		$saveFile = $Vars['supplierID'] .'_'.$Microtime . '_' . uniqid() . '.' . $fileinfo['extension'];
		if(!file_exists($savePath)) $dir = new Folder($savePath, true, 0755);

		move_uploaded_file($_FILES['file']["tmp_name"],$savePath.'/'.$saveFile);

		$dataFiles = array(
				'supplier_id' => $Data['Supplier']['id'],
				'file' => $saveFile,
				'basename' => $fileinfo['basename'],
				'expediting_type_id' => $this->_controller->request->data['ExpeditingType'],
				'user_id' => $this->_controller->Auth->user('id')
			);

		$Save = $this->_SaveUploadFileData($dataFiles,$Type);

		$this->_controller->Autorisierung->Logger($Data['Supplier']['id'],$dataFiles);

		$Data['FlashMessages'][]  = array('type' => 'success','message' => __('File uploaded.',true));

		$Data['MailMessage']  = "Es wurde eine Datei zum Expediting hinzugefügt.\n";
		$Data['MailMessage'] .= __('Place/Equipment',true) . " " . $Data['HeadLine'] . ' ' . $Data['Expediting']['description'] . "\n";
		$Data['MailMessage'] .= __('Uploader',true) . " " . $this->_controller->Auth->user('name') . " (" . $this->_controller->Auth->user('Testingcomp.firmenname') . ")\n";
		$Data['MailMessage'] .= __('Name of file') . " " . $fileinfo['basename'];

		$Data = $this->SendMessageAfterUpload($Data);

		return $Data;
	}

	public function AddUnusedExpeditingSteps($data){

		if(!isset($this->_controller->request->data['ExpeditingSetsExpeditingType'])) return $data;

		$insert = $this->_controller->request->data['ExpeditingSetsExpeditingType'];

		$period_datum_field = $insert['period_datum'];
		$period_datum = $data['Supplier'][$period_datum_field];
		$period_sign = $insert['period_sign'];
		$period_time = $insert['period_time'];
		$period_measure = $insert['period_measure'];

		$date = new DateTime($period_datum);
		$periode = $period_sign . $period_time . ' ' . $period_measure; 
		$date->modify($periode);

		$insert['supplier_id'] = $data['Supplier']['id'];
		$insert['date_soll'] = $date->format('Y-m-d');

		$sequence = Hash::extract($data['Expediting'], '{n}.Expediting.sequence');
		$sequence = max($sequence);
		
		$insert['sequence'] = ++$sequence;

		$this->_controller->Expediting->create();

		if($this->_controller->Expediting->save($insert)){

			$ExpeditingId = $this->_controller->Expediting->getLastInsertID();
		
			$insert = array();
			$insert['ExpeditingsRoll']['expediting_id'] = $ExpeditingId;

			foreach ($insert['Roll'] as $_key => $_value) {
    			
				$insert['ExpeditingsRoll']['roll_id'] = $_value;

    			$this->_controller->ExpeditingsRoll->create();
    			$this->_controller->ExpeditingsRoll->save($insert['ExpeditingsRoll']);
}

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'overview';
				$Terms = implode('/',$this->_controller->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->_controller->set('FormName',$FormName);
	
		} else {

			return false;

		}

		return $data;
	}

	public function CollectUnusedExpeditingSteps($data){

		$value = $this->_CollectExpeditingTypsAll($data);
		$data = $this->_CollectAllRolls($data);

		if(count($value['ExpeditingTypes']) == 0){

			$data['UnusedExpeditingTypes'] = array();
			return $data;

		}

		$data['UnusedExpeditingTypes'] = array_diff($value['ExpeditingTypes'],$data['ExpeditingTypes']);

		if(count($data['UnusedExpeditingTypes']) == 0){

			$this->_controller->Flash->warning(__('There are no more expediting steps available.',true),array('key' => 'warning'));

			$data['UnusedExpeditingTypes'] = array();
			return $data;

		}

		$data['UnusedExpeditingTypes'][0] = '';

		natsort($data['UnusedExpeditingTypes']);

		return $data;
	}

	public function ExpeditingAddStep(){

		$Data = array();

		if(!isset($this->_controller->request->data['Expediting'])) return false;
		if(!isset($this->_controller->request->data['Roll'])) return false;
		if(empty($this->_controller->request->data['Expediting']['ExpeditingTypes'])) return 'error';
		if(empty($this->_controller->request->data['Expediting']['date_soll'])) return 'error';
		if(empty($this->_controller->request->data['Roll'])) return 'error';
		if(count($this->_controller->request->data['Roll']['Roll']) == 0) return 'error';

		if(DateTime::createFromFormat('Y-m-d', $this->_controller->request->data['Expediting']['date_soll']) == false) return 'error';

		$Vars = $this->_ProjectVars();

		if($Vars == false) return 'error';

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetails($Data);
		$Data = $this->_CollectAllExpeditingTyps($Data);

		$ExpeditingType = $this->_controller->request->data['Expediting']['ExpeditingTypes'];

		if(!isset($Data['ExpeditingTypes'][$ExpeditingType])) return false;

		$Expeditings = Set::classicExtract($Data['Expediting'], '{n}.Expediting.{id|sequence|date_soll}');

		$Insert = array(
			'supplier_id' => $Vars['supplierID'],
			'sequence' => 0,
			'expediting_type_id' => $this->_controller->request->data['Expediting']['ExpeditingTypes'],
			'hold_witness_point' => $this->_controller->request->data['Expediting']['hold_witness_point'],
			'date_soll' => $this->_controller->request->data['Expediting']['date_soll'],
		);

		$this->_controller->Expediting->create();
		$this->_controller->Expediting->save($Insert);
		$insertedId = $this->_controller->Expediting->getLastInsertId();

		foreach ($this->_controller->request->data['Roll']['Roll'] as $key => $value) {

			$Insert = array(
				'roll_id' => $value,
				'expediting_id' => $insertedId,
			);

			$this->_controller->ExpeditingsRoll->create();

			if($this->_controller->ExpeditingsRoll->save($Insert)){

			} else {
			}
		}

		$this->_controller->set('ExpeditingAddId',$insertedId);

		return $Data;

	}

	public function SaveSupplierDataExpeditingEditDetail($Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		$Data = $this->_CollectAllRolls($Data);

		$Data = $this->_SaveEditExpediting($Data);

		$Data = $this->_CollectSupplierDetails($Data);
		$Data = $this->_CollectExpeditingDetails($Data);
		$Data = $this->_CheckExpeditingDetails($Data);
		$Data = $this->_CollectExpeditingDetail($Data);
		$Data = $this->GetExpeditingType($Data);
		$Data = $this->_CollectAllRolls($Data);
		$Data = $this->_CollectOrders($Data);

		if($Data == false) return false;

		$Data= $this->_controller->Data->BelongsToManySelected($Data,'Expediting','Roll',array('ExpeditingsRolls','roll_id','expediting_id'));

		$Data['CascadeForBread'] = $this->_controller->Navigation->CascadeGetBreads(intval($Vars['cascadeID']));

		$Data = $this->_ParentCasace($Data);
		$Data = $this->_HeadLine($Data);
		$Data = $this->_Submenue($Data);

		return $Data;

	}

	public function CollectExpeditingDetailByExpeditingType($Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		if(
			!isset($this->_controller->request->data['ExpeditingType']) && 
			!isset($this->_controller->request->data['Expediting']['expediting_type']) && 
			$Vars['expeditingID'] == 0
		){
			return $Data;
		} 

		if(isset($this->_controller->request->data['ExpeditingType'])) $ExpeditingTypId = $this->_controller->request->data['ExpeditingType'];
		if(isset($this->_controller->request->data['Expediting']['expediting_type'])) $ExpeditingTypId = $this->_controller->request->data['Expediting']['expediting_type'];

		if(!isset($ExpeditingTypId) && isset($Vars['expeditingID']) && $Vars['expeditingID'] > 0){
			$ExpeditingTypId = $Vars['expeditingTypID'];
		}

		if(!isset($ExpeditingTypId)){
			$Data['FlashMessages'][]  = array();
			return $Data;
		}

		if($ExpeditingTypId == 0) return $Data;


		$Expediting = $this->_controller->Expediting->find('first',
			array(
				'order' => array('sequence ASC'),
				'conditions' => array(
					'Expediting.deleted' => 0,
					'Expediting.supplier_id' => $Vars['supplierID'],
					'Expediting.expediting_type_id' => $ExpeditingTypId
				)
			)
		);

		if(count($Expediting) == 0) return $Data;

		$Expediting = $this->_controller->Data->BelongsToManySelected($Expediting,'Expediting','Roll',array('ExpeditingsRolls','roll_id','expediting_id'));

		$Data['Expediting'] = $Expediting['Expediting'];
		$Data['Roll'] = $Expediting['Roll'];

		$this->_controller->request->projectvars['VarsArray'][3] = $Expediting['Expediting']['id'];

		$Data = $this->GetExpeditingType($Data);
		$Data = $this->GetExpeditingEvent($Data);

		if($Data['Supplier']['stop_mail'] == 1){
			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('The email delivery for this equipment is disabled.',true));
			return $Data;
		}

		if($Data['Expediting']['stop_mail'] == 1){
			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('The email delivery for this expediting step is disabled.',true));
			return $Data;
		}

		$Data = $this->_CollectOrders($Data);

		if(count($Data['Roll']['selected']) == 0){

			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No email adresses available.'));
			$Data['ToMail'] = array();
			$Data['Emailaddress']['selected'] = array();

		} else {

			$ToMail = array();
			$Emailadress = array();

			foreach($Data['Roll']['selected'] as $key => $value){

				foreach($Data['Emailaddress']['selected'] as $_key => $_value){

					$EmailList = $this->_controller->Order->Emailaddress->find('first',array(
						'conditions'=>array(
							'Emailaddress.id' => $_value,
							'Emailaddress.roll' => $value,
							
							)
						)
					);

					if(count($EmailList) == 0) continue;

					$ToMail[$EmailList['Emailaddress']['id']] = $EmailList['Emailaddress']['email'];
					$Emailadress[$EmailList['Emailaddress']['id']] = $EmailList['Emailaddress']['id'];

				}
			}

			$Data['ToMail'] = $ToMail;
			$Data['Emailaddress']['selected'] = $Emailadress;

			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('An email with information about this action will been sent to the following email addresses:',true) . ' ' . implode(', ',$Data['ToMail']));

		}

		return $Data;
	}

	protected function _AddTimePeriodes($data){

		if(!isset($data['ExpeditingSetsExpeditingType'])) return $data;
		if(!isset($data['Expediting'])) return $data;
		if(count($data['ExpeditingSetsExpeditingType']) == 0) return $data;
		if(count($data['Expediting']) == 0) return $data;

		foreach ($data['Expediting'] as $key => $value){

			$expediting_type_id = $value['Expediting']['expediting_type_id'];

			$ExpeditingSetsExpeditingType = Hash::extract($data['ExpeditingSetsExpeditingType'], '{n}[expediting_type_id='.$expediting_type_id.']');

			unset($ExpeditingSetsExpeditingType[0]['id']);
			unset($ExpeditingSetsExpeditingType[0]['expediting_type_id']);
			unset($ExpeditingSetsExpeditingType[0]['sorting']);
			unset($ExpeditingSetsExpeditingType[0]['hold_witness_point']);
			unset($ExpeditingSetsExpeditingType[0]['karenz']);

			$data['Expediting'][$key]['Expediting'] = array_merge($data['Expediting'][$key]['Expediting'],$ExpeditingSetsExpeditingType[0]);

		}

		return $data;
	}

	public function _CollectAllRolls($Data){

		$this->_controller->Roll->recursive = -1;
		$Roll = $this->_controller->Roll->find('list');
		$Data['Rolls'] = $Roll;
		return $Data;

	}

	public function _CollectExpeditingRolls($Data){

		foreach ($Data['Expediting'] as $key => $value) {
			$Data['Expediting'][$key] = $this->_controller->Data->BelongsToManySelected($value,'Expediting','Roll',array('ExpeditingsRolls','roll_id','expediting_id'));
		}

		return $Data;

	}

	public function _CollectExpeditingAddAdminRolls($Data){

		$Data['Roll']['selected'] = 1;

		return $Data;

	}

	public function _CollectOpenExpeditingSteps($Data){


		foreach ($Data['Expediting'] as $key => $value) {
			if(isset($Data['ExpeditingTypes'][$value['Expediting']['expediting_type_id']])) unset($Data['ExpeditingTypes'][$value['Expediting']['expediting_type_id']]);
		}

		unset($Data['Expediting']);

		return $Data;

	}

	public function _CollectRkls($Data){

		if(!isset($Data['Supplier']['rkl_id'])) return $Data;
		if($Data['Supplier']['rkl_id'] == 0) return $Data;

		$Rkl = $this->_controller->Rkl->find('first',array('conditions'=> array('Rkl.id' => $Data['Supplier']['rkl_id'])));

		if(count($Rkl) == 0) return $Data;

		$Data = array_merge($Data,$Rkl);

		$xml = $this->_controller->Xml->DatafromXml('Rkl','file',null);

		if(!isset($Data['Xml'])) $Data['Xml'] = array();

		if(isset($xml['settings'])) $Data['Xml']['Rkl'] = $xml['settings'];

		return $Data;

	}

	public function _CollectOrders($Data){

		if(!isset($Data['Supplier']['order_id'])) return $Data;
		if($Data['Supplier']['order_id'] == 0) return $Data;

		$this->_controller->Order->recursive = -1;

		$Order = $this->_controller->Order->find('first',array('conditions'=> array('Order.id' => $Data['Supplier']['order_id'])));
		if(count($Order) == 0) return $Data;

		$Order = $this->_controller->Data->BelongsToManySelected($Order,'Order','Testingcomp',array('OrdersTestingcomps','testingcomp_id','order_id'));
		$Order = $this->_controller->Data->BelongsToManySelected($Order,'Order','Emailaddress',array('OrdersEmailadresses','emailadress_id','order_id'));

		$Data = array_merge($Data,$Order);

		$testingcomps = $this->_controller->Order->Testingcomp->find('list',array(
				'fields'=>array('id','name'),
				'order' => array('name'),
				'conditions'=>array('Testingcomp.id' => $Data['Testingcomp']['selected'])
			)
		);

		$EmailList = $this->_controller->Order->Emailaddress->find('list',array(
				'order' => array('email'),
				'fields'=>array('id','email','testingcomp_id'),
				'conditions'=>array('Emailaddress.testingcomp_id' => $Data['Testingcomp']['selected'])
			)
		);

		$emailaddress = array();

		$this->_controller->Roll->recursive = -1;

		foreach($EmailList as $key => $value){

			if(count($value) == 0) continue;

			foreach($value as $_key => $_value){

				$ThisMail = $this->_controller->Order->Emailaddress->find('first',array(
					'conditions' => array('Emailaddress.id' => $_key)
					)
				);

				if(count($ThisMail) == 0) continue;

				$Roll = $this->_controller->Roll->find('first',array('conditions' => array('Roll.id' => $ThisMail['Emailaddress']['roll'])));

				if(count($Roll) == 0) continue;

				$EmailList[$key][$_key] = $_value . ' (' . $Roll['Roll']['name']. ')';
	
			}

			natcasesort($value);

		}

		foreach($testingcomps as $key => $value){

			$emailaddress[$value] = $EmailList[$key];

		}

		$Data['emailaddress'] = $emailaddress;

		$Data['ToMail'] = $this->_controller->Order->Emailaddress->find('list',array(
			'conditions' => array(
				'Emailaddress.id' => $Data['Emailaddress']['selected']
				),
			'order' => array('email'),
			'fields'=>array('id','email')
			)
		);

		return $Data;
	}

	public function _CollectEmailToExpedingRoll($Data){

		if(!isset($Data['Emailaddress'])) return $Data;
		if(count($Data['Emailaddress']) == 0) return $Data;
		if(!isset($Data['Emailaddress']['selected'])) return $Data;
		if(count($Data['Emailaddress']['selected']) == 0) return $Data;
		if(!isset($Data['ToMail'])) return $Data;
		if(count($Data['ToMail']) == 0) return $Data;
		if(!isset($Data['Expediting'])) return $Data;
		if(count($Data['Expediting']) == 0) return $Data;

		foreach($Data['Expediting'] as $key => $value){

			if(count($value['Roll']) == 0) continue;

			$ThisMail = $this->_controller->Order->Emailaddress->find('all',array(
				'conditions' => array(
					'Emailaddress.roll' => $value['Roll'],
					'Emailaddress.id' => $Data['Emailaddress']['selected']
					)
				)
			);

			if(count($ThisMail) == 0) continue;

			$Data['Expediting'][$key]['Emailaddresses'] = $ThisMail;
	
		}
		
		return $Data;
	}
		

	public function _CollectExpeditingTypsAll($Data){

		$ExpeditingTypes = $this->_controller->ExpeditingType->find('list',
			array(
				'fields' => array('id','description'),
				'order' => array('description'),
				'conditions' => array('ExpeditingType.deleted' => 0)
			)
		);

		$Data['ExpeditingTypes'] = $ExpeditingTypes;

		return $Data;
	}

	public function _CollectExpeditingTypCurrent($Data){

		if(!isset($this->_controller->request->data['ExpeditingType']) && !isset($this->_controller->request->data['Expediting']['expediting_type'])) return $Data;

		if(isset($this->_controller->request->data['ExpeditingType']))$ExpeditingTypesID = $this->_controller->request->data['ExpeditingType'];
		if(isset($this->_controller->request->data['Expediting']['expediting_type']))$ExpeditingTypesID = $this->_controller->request->data['Expediting']['expediting_type'];

		if(!isset($ExpeditingTypesID)) return;

		$ExpeditingType = $this->_controller->ExpeditingType->find('first',
			array(
				'conditions' => array(
				'ExpeditingType.id' => $ExpeditingTypesID,
				'ExpeditingType.deleted' => 0,
				)
			)
		);

		if(count($ExpeditingType) == 0) return $Data;

		$Data['ExpeditingType'] = $ExpeditingType['ExpeditingType'];

		return $Data;
	}

	public function _CollectExpeditingTyps($Data){

		if(!isset($Data['Expediting'])) return $Data;

		$ExpeditingTypesIDs = Hash::extract($Data['Expediting'], '{n}.Expediting.expediting_type_id');

		$ExpeditingTypesIDs = array_unique($ExpeditingTypesIDs);

		$Data['ExpeditingTypes'] = array();
//		$Data['ExpeditingTypes'][0] = __('Not assigned',);

		$ExpeditingTypes = $this->_controller->ExpeditingType->find('list',
			array(
				'fields' => array('id','description'),
				'order' => array('description'),
				'conditions' => array(
					'ExpeditingType.id' => $ExpeditingTypesIDs,
					'ExpeditingType.deleted' => 0,
				)
			)
		);

		if(count($ExpeditingTypes) == 0) return $Data;

		if(count($Data['Expediting']) == 0){

			$Data['ExpeditingTypes'] = $ExpeditingTypes;
			return $Data;

		}

		natsort($ExpeditingTypes);

		$Data['ExpeditingTypes'] = $ExpeditingTypes;

		return $Data;
	}

	public function _CollectAllExpeditingTyps($Data){

		$Data['ExpeditingTypes'] = array();
//		$Data['ExpeditingTypes'][0] = __('Not assigned',);

		$ExpeditingTypes = $this->_controller->ExpeditingType->find('list',
			array(
				'fields' => array('id','description'),
				'order' => array('description'),
				'conditions' => array('ExpeditingType.deleted' => 0)
			)
		);

		if(count($ExpeditingTypes) == 0) return $Data;

		$Data['ExpeditingTypes'] = $ExpeditingTypes;

		return $Data;
	}

	protected function _CollectSupplierDetailsAdd($Data){

		$Supplier = $this->_controller->Supplier->find('first');
		$Data = array_merge($Data,$Supplier);

		return $Data;
	}

	protected function _CollectSupplierDetails($Data,$Id = 0){

		$locale = $this->_controller->Lang->Discription();

		if($Id == 0){

			$Vars = $this->_ProjectVars();
			$Id = $Vars['supplierID'];

		}

		if($Id == 0) return $Data;

		$Supplier = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.id' => $Id)));

		$Cascadegroup = $this->_controller->CascadeGroup->find('first',array('conditions'=>array('CascadeGroup.id' => $Supplier['Supplier']['equipment_typ'])));

		if(count($Cascadegroup) > 0) $Supplier['Supplier']['equipment_name'] = $Cascadegroup['CascadeGroup'][$locale];

		$Data = array_merge($Data,$Supplier);

		if($Supplier['Supplier']['expeditingset_id'] > 0){

			$Expeditingset = $this->_controller->Expeditingset->find('first',array(
				'conditions' => array(
						'Expeditingset.id' => $Supplier['Supplier']['expeditingset_id']
					)
				)
			);


			$Data = array_merge($Data,$Expeditingset);

		}

		return $Data;
	}

	protected function _CollectExpeditingDetail($Data){

		if(count($Data) == 0) return $Data;

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		if($Vars['expeditingID'] == 0) return $Data;

		$Expediting = $this->_controller->Expediting->find('first',
			array(
				'order' => array('sequence ASC'),
				'conditions' => array(
					'Expediting.id' => $Vars['expeditingID'],
					'Expediting.deleted' => 0,
					'Expediting.supplier_id' => $Vars['supplierID']
				)
			)
		);


		$Expediting = $this->GetExpeditingType($Expediting);
		$Expediting = $this->GetExpeditingEvent($Expediting);

		if(isset($Expediting['Expediting'])) $Data['Expediting'] = $Expediting['Expediting'];
		if(isset($Expediting['ExpeditingEvent'])) $Data['ExpeditingEvent'] = $Expediting['ExpeditingEvent'];

		return $Data;
	}

	protected function _CheckExpeditingDetails($Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		if($Vars['expeditingID'] == 0) return $Data;

		foreach ($Data['Expediting'] as $key => $value) {

			if($value['Expediting']['id'] == $Vars['expeditingID'] && $value['Expediting']['stop_for_next_step'] == 1){

				$Data['ExpeditingStopEditable']['date_ist'] = 1;

				if(isset($this->_controller->request->data['Expediting']['data_ist'])) $this->_controller->Flash->warning(__('This step cannot be completed. A previous holding point was not processed.',true),array('key' => 'warning'));

			  break;
			}
		}

		return $Data;
	}

	protected function _DateCorrectionByTemplate($data){

		if($data['Supplier']['expeditingset_id'] == 0) return $data;
		if(!isset($data['Expeditingset'])) return $data;
		if(!isset($data['ExpeditingSetsExpeditingType'])) return $data;
		if(count($data['ExpeditingSetsExpeditingType']) == 0) return $data;

		if($data['Supplier']['dynamic_termination'] == 0) return $data;

		foreach($data['Expediting'] as $key => $value){

			$expediting_type_id = $value['Expediting']['expediting_type_id'];

			$ExpeditingSetsExpeditingType = Hash::extract($data['ExpeditingSetsExpeditingType'], '{n}[expediting_type_id='.$expediting_type_id.']');

			if(count($ExpeditingSetsExpeditingType) == 0) continue;
			if(!isset($ExpeditingSetsExpeditingType[0])) continue;

			$ExpeditingSetsExpeditingType = $ExpeditingSetsExpeditingType[0];
			$period_field = $ExpeditingSetsExpeditingType['period_datum'];

			if(empty($period_field)) continue;

			$period_datum = $data['Supplier'][$period_field];
			$date = new DateTime($period_datum);
			$periode = $ExpeditingSetsExpeditingType['period_sign'] . $ExpeditingSetsExpeditingType['period_time'] . ' ' . $ExpeditingSetsExpeditingType['period_measure']; 
			$date->modify($periode);

			$data['Expediting'][$key]['Expediting']['date_soll'] = $date->format('Y-m-d');
		}

		return $data;
	}

	protected function _CollectExpeditingDetails($Data,$Id = 0){

		if($Id == 0){

			$Vars = $this->_ProjectVars();
			$Id = $Vars['supplierID'];

		}

		if(count($Data) == 0) return $Data;

		$Options['order'] = array('sequence ASC');

		$Options['conditions'] = array(
			'Expediting.deleted' => 0,
			'Expediting.supplier_id' => $Id 
		);

		if(isset($this->_controller->request->data['Expediting']['expediting_type'])){
			$Options['conditions']['Expediting.expediting_type_id'] = $this->_controller->request->data['Expediting']['expediting_type'];
		}

		$Expediting = $this->_controller->Expediting->find('all',$Options);

		$Data['Expediting'] = $Expediting;

		if(empty($Expediting)) return $Data;

		$Data = $this->_DateCorrectionByTemplate($Data);

		$StopForNextStep = 0;

		foreach($Data['Expediting'] as $key => $value){

			switch ($Data['Expediting'][$key]['Expediting']['hold_witness_point']) {
				case 0:
				$Data['Expediting'][$key]['Expediting']['hold_witness_point_description'] = __('Indefinite',true);
				$Data['Expediting'][$key]['Expediting']['hold_witness_point_class'] = 'icon_hold_witness_undefinite';
				break;

				case 1:
				$Data['Expediting'][$key]['Expediting']['hold_witness_point_description'] = __('Witness point',true);
				$Data['Expediting'][$key]['Expediting']['hold_witness_point_class'] = 'icon_hold_witness_witness';
				break;

				case 2:
				$Data['Expediting'][$key]['Expediting']['hold_witness_point_description'] = __('Hold point',true);
				$Data['Expediting'][$key]['Expediting']['hold_witness_point_class'] = 'icon_hold_witness_hold';
				break;

			}

			$Data['Expediting'][$key] = $this->ExpeditingTimePeriode($Data['Expediting'],$value,$key);
			$Data['Expediting'][$key] = $this->GetExpeditingType($Data['Expediting'][$key]);
			$Data['Expediting'][$key] = $this->GetExpeditingEvent($Data['Expediting'][$key]);

			$Data['Expediting'][$key]['Expediting']['stop_for_next_step'] = $StopForNextStep;

			if($Data['Expediting'][$key]['Expediting']['hold_witness_point'] == 2 && $Data['Expediting'][$key]['Expediting']['class'] != 'finished') $StopForNextStep = 1;

			$CurrentDate = new DateTime();

			if($Data['Expediting'][$key]['Expediting']['date_ist'] != null){
				$Data['Expediting'][$key]['Expediting']['finished'] = 1;
				$Data['Expediting'][$key]['Expediting']['date_current'] = $Data['Expediting'][$key]['Expediting']['date_ist'];
			} else {
				$Data['Expediting'][$key]['Expediting']['finished'] = 0;
				$Data['Expediting'][$key]['Expediting']['date_current'] = $CurrentDate->format('Y-m-d');
			}

			$Rolls = $this->_controller->ExpeditingsRoll->find('list',array(
				'fields' => array('roll_id'),
				'conditions' => array(
					'ExpeditingsRoll.expediting_id' => $Data['Expediting'][$key]['Expediting']['id']
					)
				)
			);

			$Data['Expediting'][$key]['Roll'] = $Rolls;

		}

//		$Data['Expediting'] = $Expediting;

		return $Data;

	}

	protected function _CollectSupplierfile($Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		if(count($Data) == 0) return $Data;

		$savePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

		$Supplierfile = $this->_controller->Supplierfile->find('all',array('conditions' => array('Supplierfile.supplier_id' => $Vars['supplierID'])));

		if(count($Supplierfile) == 0) return $Data;

		foreach($Supplierfile as $_key => $_Supplierfile){

			$Supplierfile[$_key]['Supplierfile'] = $this->_CollectUser($Supplierfile[$_key]['Supplierfile']);

			if(file_exists($savePath . $_Supplierfile['Supplierfile']['file']) == false) $Supplierfile[$_key]['Supplierfile']['file_exists'] = false;
			if(file_exists($savePath . $_Supplierfile['Supplierfile']['file']) == true) $Supplierfile[$_key]['Supplierfile']['file_exists'] = true;
		}

		$this->_controller->Expediting->recursive = -1;

		foreach($Supplierfile as $key => $value){

			$Exp = $this->_controller->Expediting->find('first',
				array(
					'conditions' => array(
						'Expediting.supplier_id' => $value['Supplierfile']['supplier_id'],
						'Expediting.expediting_type_id' => $value['Supplierfile']['expediting_type_id'],
					)
				)
			);

			$Supplierfile[$key]['Supplierfile']['expediting_id'] = $Exp['Expediting']['id'];

		}

		// Die Datein müssen nach der Expeditingreihenfolge geordnet werden
		$Files = Hash::extract($Supplierfile, '{n}.Supplierfile');
		$Expediting = Hash::extract($Data['Expediting'], '{n}.Expediting.description');
		$ExpeditingFlip = array_flip($Data['ExpeditingTypes']);
		$Expediting = array_flip($Expediting);

		foreach ($Expediting as $key => $value){
			$Expediting[$key] = $ExpeditingFlip[$key];
		} 

		$Expediting = array_flip($Expediting);

		foreach ($Expediting as $key => $value) {
			$StatusOpen = Hash::extract($Files, '{n}[expediting_type_id=' . $key . ']');
			$Data['Supplierfile'][$key] = $StatusOpen;

		}

		return $Data;
	}

	protected function _CollectSupplierimage($Data,$Height,$Width){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		if(count($Data) == 0) return $Data;

		$savePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

		$Supplierimage = $this->_controller->Supplierimage->find('all',array('conditions' => array('Supplierimage.supplier_id' => $Vars['supplierID'])));

		if(count($Supplierimage) == 0) return $Data;

		foreach($Supplierimage as $_key => $_Supplierfile){

			if(file_exists($savePath . $_Supplierfile['Supplierimage']['file']) == false) $Supplierimage[$_key]['Supplierimage']['file_exists'] = false;

			if(file_exists($savePath . $_Supplierfile['Supplierimage']['file']) == true){
				$Supplierimage[$_key]['Supplierimage']['file_exists'] = true;
				$Supplierimage[$_key]['Supplierimage']['imagedata'] = 'data:png;base64,'.$this->_controller->Image->Tumbs($savePath . $_Supplierfile['Supplierimage']['file'],null,$Height,$Width,null);
			}
		}

		foreach($Supplierimage as $key => $value){

			$Exp = $this->_controller->Expediting->find('first',
				array(
					'conditions' => array(
						'Expediting.supplier_id' => $value['Supplierimage']['supplier_id'],
						'Expediting.expediting_type_id' => $value['Supplierimage']['expediting_type_id'],
					)
				)
			);

			$Supplierimage[$key]['Supplierimage']['expediting_id'] = $Exp['Expediting']['id'];

		}


		// Die Datein müssen nach der Expeditingreihenfolge geordnet werden
		$Files = Hash::extract($Supplierimage, '{n}.Supplierimage');
		$Expediting = Hash::extract($Data['Expediting'], '{n}.Expediting.description');
		$ExpeditingFlip = array_flip($Data['ExpeditingTypes']);
		$Expediting = array_flip($Expediting);

		foreach ($Expediting as $key => $value) $Expediting[$key] = $ExpeditingFlip[$key];

		$Expediting = array_flip($Expediting);

		foreach ($Expediting as $key => $value) {

			$StatusOpen = Hash::extract($Files, '{n}[expediting_type_id=' . $key . ']');
			$Data['Supplierimage'][$key] = $StatusOpen;

		}

		return $Data;
	}

	protected function _CollectSupplierSingleimage($Data,$Id,$Height,$Width){

		$Vars = $this->_ProjectVars();

		$id = $Id;

		if($Vars == false) return $Data;

		if(count($Data) == 0) return $Data;

		$Supplierimage = $this->_controller->Supplierimage->find('first', array('conditions' => array('Supplierimage.id' => $id)));

	 	if(count($Supplierimage) == 0) return $Data;

		$imagePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

	 	if(!file_exists($imagePath . $Supplierimage['Supplierimage']['file'])) return $Data;

		$Supplierimage['Supplierimage']['file_exists'] = true;
		$Supplierimage['Supplierimage']['imagedata'] = 'data:png;base64,'.$this->_controller->Image->Tumbs($imagePath . $Supplierimage['Supplierimage']['file'],null,$Height,$Width,null);

//		$Supplierimage['Supplierimage']['imData'] = 'data:png;base64,' . $this->_controller->Image->Tumbs($imagePath,null,300,500,null);

		$Data['Supplierimage'] = $Supplierimage['Supplierimage'];

		return $Data;
	}

	protected function _CollectSupplierMassactionfiles($Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;
		if(count($Data) == 0) return $Data;
		if(!isset($this->_controller->request->data['Expediting']['files'])) return $Data;
		if(empty($this->_controller->request->data['Expediting']['files'])) return $Data;
		$FilesIds = explode(',',$this->_controller->request->data['Expediting']['files']);

		if(count($FilesIds) == 0) return $Data;

		$Supplierfiles = $this->_controller->Supplierfile->find('all', array('conditions' => array('Supplierfile.id' => $FilesIds)));

		if(count($Supplierfiles) == 0) return $Data;

		$filePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

		$Data['Supplierfile'] = $Supplierfiles;
		$Data['Supplierfilesize'] = 0;

		foreach ($Data['Supplierfile'] as $key => $value) {

			if(file_exists($filePath . $value['Supplierfile']['file'])){

				$FileSize = filesize($filePath . $value['Supplierfile']['file']);

				if($FileSize == false) continue;

				$Data['Supplierfile'][$key]['Supplierfile']['file_exists'] = true;

				$Data['Supplierfile'][$key]['Supplierfile']['filesize'] = $FileSize;
				$Data['Supplierfilesize'] += $FileSize;

			}
		}

		$Data['Suppliers']['files'] = $this->_controller->request->data['Expediting']['files'];

		$Filenames = Hash::extract($Data['Supplierfile'], '{n}.Supplierfile.basename');

		if($this->_controller->request->params['action'] == 'sendmanyfiles') $Data['FlashMessages'][] = array('type' => 'success','message' => __('The following files will be sent',true) . ': ' . implode(', ',$Filenames));
		if($this->_controller->request->params['action'] == 'delmanyfiles') $Data['FlashMessages'][] = array('type' => 'warning','message' => __('The following files will be deleted',true) . ': ' . implode(', ',$Filenames));

		return $Data;

	}

	protected function _CollectSupplierSinglefile($Data,$Id){

		$Vars = $this->_ProjectVars();

		$id = $Id;
/*
		if($id == 0  && isset($this->_controller->request->data['Supplierfile']['id'])){
			$id = $this->_controller->request->data['Supplierfile']['id'];
		}
*/
		if($id == 0) return $Data;

		if($Vars == false) return $Data;

		if(count($Data) == 0) return $Data;

		$Supplierfile = $this->_controller->Supplierfile->find('first', array('conditions' => array('Supplierfile.id' => $id)));

		if(count($Supplierfile) == 0) return $Data;

		$Data['Supplierfile'] = $Supplierfile['Supplierfile'];

		$filePath = Configure::read('supplier_folder') . $Vars['supplierID'] . DS;

	 	if(!file_exists($filePath . $Supplierfile['Supplierfile']['file'])) return $Data;

		$Supplierfile['Supplierfile']['file_exists'] = true;

		$Data['Supplierfile'] = $Supplierfile['Supplierfile'];

		return $Data;
	}

	protected function _CollectUser($Data){

		if(!isset($Data['user_id'])) return $Data;
		if($Data['user_id'] == 0) return $Data;

		$this->_controller->User->recursive = -1;

		$User = $this->_controller->User->find('first',array('conditions' => array('User.id' => $Data['user_id'])));

		if(!isset($User['User']['name'])) return $Data;

		$Data['user_name'] = $User['User']['name'];

		return $Data;
	}

	protected function _CreateExpeditingMessage($MailData,$Data,$DataBefore,$CheckArrayDiff){

		$MailData['ExpeditingsMessage'] = NULL;
		$MailData['ExpeditingsMessage'] .= "\n";

		$MailData['ExpeditingsMessage'] .= $MailData['Subject'];


		$MailData['ExpeditingsMessage'] .= "\n\n";
		$MailData['ExpeditingsMessage'] .= $DataBefore['Expediting']['description'];
		$MailData['ExpeditingsMessage'] .= "\n\n";

		$MailData['ExpeditingsMessage'] .= __('The following changes have been made ',true);

		$MailData['ExpeditingsMessage'] .= "\n\n";

		foreach ($CheckArrayDiff as $key => $value) {

			foreach ($value as $key => $value) {

				$MailData['ExpeditingsMessage'] .= " • " . __($key,true) . ": " . $value . "\n";

			}
		}

		$MailData['ExpeditingsMessage'] .= "\n\n";
		$MailData['ExpeditingsMessage'] .= __('Current date Status',true);
		$MailData['ExpeditingsMessage'] .= "\n\n";

		$Expeditings = $this->_CollectExpeditingDetails($Data);

		foreach ($Expeditings['Expediting'] as $key => $value) {

			$MailData['ExpeditingsMessage'] .= $value['Expediting']['description'] . "\n";
			$MailData['ExpeditingsMessage'] .= "Soll: " . $value['Expediting']['date_soll'] . "\n";
			$MailData['ExpeditingsMessage'] .= "Ist: " . $value['Expediting']['date_ist'] . "\n\n";

		}

		$ExpeditingStatus = $this->ExpeditingStatus($Data['Order']['id']);

		$CurrentExpeditingStatus = NULL;

		switch ($ExpeditingStatus[1]['class'][0]) {
			case 'icon_critical':
			$CurrentExpeditingStatus = 'kritisch/critical';
			break;
			case 'icon_delayed':
			$CurrentExpeditingStatus = 'verspätet/delayed';
			break;
			case 'icon_plan':
			$CurrentExpeditingStatus = 'im Plan/in plan';
			break;
			case 'icon_future':
			$CurrentExpeditingStatus = 'keine Termine/no appointments';
			break;
			case 'icon_finished':
			$CurrentExpeditingStatus = 'Abgeschlossen/finished';
			break;
		}

		$Data['CurrentExpeditingStatus'] = $CurrentExpeditingStatus;

		if(isset($Data['CurrentExpeditingStatus'])){
			$ExpeditingsStatus = "---------------------------------------------------------------------------------------------------\n";
			$ExpeditingsStatus .= "Diese Bestellung befindet sich in folgendem Status: " . strtoupper($Data['CurrentExpeditingStatus']) . " \nThis order is in the following status: " . strtoupper($Data['CurrentExpeditingStatus']) . "\n";
			$ExpeditingsStatus .= "---------------------------------------------------------------------------------------------------\n\n\n";

			$MailData['ExpeditingsMessage'] = $ExpeditingsStatus . $MailData['ExpeditingsMessage'];
		}

		return $MailData;
	}

	protected function _UpdateCurrentExpediting($Data){	
		
		if(!isset($Data['data'])) return $Data;

		$id = $Data['data']['id'];

		foreach ($Data['expediting'] as $key => $value) {

			if($value['Expediting']['id'] == $id){

				$Data['data']['class'] = $value['Expediting']['class'];

				break;
			}
		}

		return $Data;
	}

	protected function _SaveExpeditingSollDateFast($Data,$Request){

		if(!isset($this->_controller->request->data['Expediting'])) return $Request;

		if(isset($this->_controller->request->data['Expediting']['date_ist'])){

			if(isset($Data['ExpeditingStopEditable']['date_ist']) && $Data['ExpeditingStopEditable']['date_ist'] == 1){

				$Request['value'] = 'Error';
				return $Request;

			}
			if($this->_controller->request->data['Expediting']['date_ist'] == '1970-01-01'){
				$this->_controller->request->data['Expediting']['date_ist'] = 'NULL';
			}
		}

		$Request['data'] = $this->_controller->request->data['Expediting'];

		if($this->_controller->Expediting->save($this->_controller->request->data['Expediting'])){

			$ValKey = key($this->_controller->request->data['Expediting']);

			$Request['value'] = 'Success';
			$Request['data']['class'] = 'finished';
			$Request['data']['data'] = $this->_controller->request->data['Expediting'][$ValKey];

		} else {

			$Request['value'] = 'Error';

		}

		return $Request;
	}

	protected function _SaveExpeditingFileDescriptionFast($Data,$Request){

		if(!isset($this->_controller->request->data['Supplierfile'])) return $Request;

		if($this->_controller->Supplierfile->save($this->_controller->request->data['Supplierfile'])) $Request['value'] = 'Success';
		else $Request['value'] = 'Error';

		return $Request;

	}

	public function _SaveEditExpediting($Data){

		if(!isset($this->_controller->request->data['Expediting'])) return $Data;

		$Data['Request'] = $this->_controller->request->data;

		if(isset($Data['ExpeditingStopEditable']['date_ist']) && $Data['ExpeditingStopEditable']['date_ist'] == 1) $this->_controller->request->data['Expediting']['date_ist'] = '';

		if(empty($this->_controller->request->data['Expediting']['date_soll'])){

			$Data['FlashMessages'][] = array('type' => 'error','message' => __('Wrong date entered.',true));

			return $Data;

		}

		$Save = $this->_SaveExpeditingSupplier($Data);

		if($Save == false){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('Wrong date entered.',true));

			return false;

		}

		$Save = $this->_UpdateAllRolls($Data);

		if($Save == false){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('Wrong date entered.',true));

			return false;

		}

		$Save = $this->_SaveExpeditingEvent($Data);

		if($Save == false){

			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('Wrong date entered.',true));

			return false;

		}

		$Data['FlashMessages'][]  = array('type' => 'success','message' => __('Value has been saved.',true));

		$FormName['controller'] = 'suppliers';
		$FormName['action'] = 'overview';
		$Terms = implode('/',$this->_controller->request->projectvars['VarsArray']);
		$FormName['terms'] = $Terms;
		$this->_controller->set('FormName',$FormName);

		return $Data;
	}

	public function _SaveExpeditingSupplier($Data){

		$Save = $this->_controller->Expediting->save($this->_controller->request->data['Expediting']);

		if($Save == false) return false;

		$this->_controller->request->data['Supplier']['id'] = $Data['Supplier']['id'];

		$Save = $this->_controller->Supplier->save($this->_controller->request->data['Supplier']);

		if($Save == false) return false;

		return true;

	}

	public function _UpdateAllRolls($Data){

		$this->_controller->ExpeditingsRoll->deleteAll(array('ExpeditingsRoll.expediting_id' => $Data['Expediting']['id']), false);

		if(!isset($this->_controller->request->data['Roll'])) return true;
		if(count($this->_controller->request->data['Roll']) == 0) return true;	
		if(empty($this->_controller->request->data['Roll']['Roll'])) return true;

		foreach ($this->_controller->request->data['Roll']['Roll'] as $key => $value) {

			$Insert = array(
				'roll_id' => $value,
				'expediting_id' => $Data['Expediting']['id'],
			);

			$Create = $this->_controller->ExpeditingsRoll->create();
			$Save = $this->_controller->ExpeditingsRoll->save($Insert);

			if($Save == false) return false;

		}

		return true;
	}

	protected function _ParentCasace($Data){

		$Vars = $this->_ProjectVars();

		if($Vars == false) return $Data;

		$ParentCasace = $this->_controller->Navigation->CascadeGetParentId(intval($Vars['cascadeID']));

		if($ParentCasace == 0) return $Data;

		$Data['ParentCasace'] = $ParentCasace;

		return $Data;
	}

	protected function _HeadLine($Data){

		$HeadLine = $this->_controller->Navigation->CascadeCreateBreadcrumblist(array(),$Data['CascadeForBread']);

		$HeadLineFirst = array_shift($HeadLine);
		$HeadLine = Hash::extract($HeadLine, '{n}.discription');
		$HeadLine = implode(' > ',$HeadLine);

		$Data['HeadLineFirst'] = $HeadLineFirst;
		$Data['HeadLine'] = $HeadLine;

		return $Data;

	}

	protected function _Submenue($Data){

		if(!isset($Data['CascadeForBread'])) return $Data;

		if(!is_array($Data['CascadeForBread'])) return $Data;
		if(count($Data['CascadeForBread']) == 0) return $Data;

		$Controller = 'suppliers';
		$Action = 'index';

		$output = array();

		$ExpeditingStartCascade = false;

		if(Configure::check('ExpeditingStartCascade') && Configure::read('ExpeditingStartCascade') < 1) $ExpeditingStartCascade = true;

		if($ExpeditingStartCascade === true){

			$StartCascade = Configure::read('ExpeditingStartCascade');
			$StartExpeditingType = Configure::read('ExpeditingStartExpeditingType');

			$Output[0]['title'] = __('Overview',true);
			$Output[0]['controller'] = $Controller;
			$Output[0]['action'] = $Action;
			$Output[0]['params'] = array($StartCascade,$StartExpeditingType);
			$Output[0]['class'] = 'ajax home_link ';

			$x = 1;

		} else {

			$x = 0;

		}

		asort($Data['CascadeForBread']);
		
		foreach ($Data['CascadeForBread'] as $key => $value) {

			$Class = 'ajax ';

			if($x == 0 && $ExpeditingStartCascade === true) $Class .= 'home_link ';

			$Output[$x]['title'] = $value['Cascade']['discription'];
			$Output[$x]['controller'] = $Controller;
			$Output[$x]['action'] = $Action;
			$Output[$x]['params'] = array($value['Cascade']['topproject_id'],$value['Cascade']['id']);
			$Output[$x]['class'] = $Class;

			$x++;
		}

		array_pop($Output);

		if(isset($Data['Supplier']['equipment'])) $SuppLink['title'] = $Data['Supplier']['equipment'];
		$SuppLink['controller'] = $Controller;
		$SuppLink['action'] = 'overview';
		$SuppLink['params'] = $this->_controller->request->projectvars['VarsArray'];
		$SuppLink['class'] = 'ajax ';
		$SuppLink['end'] = 1;

		$Output[] = $SuppLink;

		$Data['BreadcrumpArray'] = $Output;

		return $Data;
	}

	public function SortExpeditings(){

		$Vars = $this->_ProjectVars();

		$Data = array();

		$Data['Message'] = __('Error',true);
		$Request = array();
		$ExpeditingEvnets = array();

		if($Vars == false) return $Data;

		if(!isset($this->_controller->request->data['SortableIDs'])) return $Data;
		if(!isset($this->_controller->request->data['Sequence'])) return $Data;
		if(!isset($this->_controller->request->data['Counter'])) return $Data;

		if(empty($this->_controller->request->data['SortableIDs'])) return $Data;
		if(empty($this->_controller->request->data['Sequence'])) return $Data;
		if(empty($this->_controller->request->data['Counter'])) return $Data;

		$Request['SortableIDs'] = explode(',',$this->_controller->request->data['SortableIDs']);
		$Request['Sequence'] = explode(',',$this->_controller->request->data['Sequence']);
		$Request['Counter'] = explode(',',$this->_controller->request->data['Counter']);

		foreach ($Request['SortableIDs']  as $key => $value) {

			if(!isset($Request['Sequence'][$key])) return $Data;
			if(!isset($Request['Counter'][$key])) return $Data;

			$Conditions = array('conditions' => array(
				'Expediting.id' => $Request['SortableIDs'][$key],
				'Expediting.sequence' => $Request['Sequence'][$key],
				'Expediting.deleted' => 0,
				)
			);

			$Expediting = $this->_controller->Expediting->find('first',$Conditions);

			if(count($Expediting) == 0) return $Data;

			$Update = array(
				'id' => $Request['SortableIDs'][$key],
				'sequence' => $Request['Counter'][$key],
			);

			$this->_controller->Expediting->save($Update);

		}

		$Expeditings = $this->_controller->Expediting->find('list',array(
			'fields' => array('sequence'),
			'order' => array('Expediting.sequence ASC'),
			'conditions' => array(
				'Expediting.deleted' => 0,
				'Expediting.supplier_id' => $Vars['supplierID']
				)
			)
		);

		foreach ($Expeditings as $key => $value) {

			$ExpeditingEvnets[] = array('rev' => $key, 'rel' => $value);
		}

		$Data['Message'] = __('Success',true);
		$Data['Sequence'] = $ExpeditingEvnets;

		return $Data;
	}

	public function GetStatisticDiagrammData($ReturnMode){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		$PriorityStatus = array('total' => 0,'critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);
		$PriorityStatusFlip = array_flip($PriorityStatus);
		$PriorityStatusArray = array('total' => 0,'critical' => 0,'delayed' => 0,'plan' => 0,'future' => 0,'finished' => 0);
		$PriorityStatusArrayDetail = array('total' => 0,'critical' => array(),'delayed' => array(),'plan' => array(),'future' => array(),'finished' => array());

		$PriorityText = array(
						'critical' => __('Critical (soll date > current date + waiting period)',true),
						'delayed' => __('Delayed (soll date > current date and <  current date + waiting period)',true),
						'plan' => __('In plan (soll date < current date)',true),
						'future' => __('No date informations stored',true),
						'finished' => __('Completed',true)
		);

		$barcolors = array(
			'total'=>'#05c8c1',
            'critical'=>'#af1f21',
            'delayed'=>'#cf8d22',
            'plan'=>'#3c5bbe',
            'future'=>'#868686',
            'finished'=>'#5d8431'
		);

		$this->_controller->loadModel('Expediting');
		$this->_controller->Expediting->recursive = 0;

		$ExpeditingOption = $this->_controller->Expediting->find('list',array(
			'fields' => array('Expediting.expediting_type_id'),
			'group' => array('expediting_type_id'),
			'conditions' => array(
				'Expediting.deleted' => 0,
				)
			)
		);

		$ExpeditingTypes = $this->_controller->ExpeditingType->find('list',array('fields' => array('id','description')));

		$ExpeditingOption = array();
		$ExpeditingOptionArray = array();


		foreach($ExpeditingTypes as $_key => $_data){
			$ExpeditingOption[$_data] = $PriorityStatusArray;
			$ExpeditingOptionArray[$_data] = $PriorityStatusArrayDetail;
		}

		$DiagrammData = array();
		$Output = $this->StatisticDetail01();

		$DataForPlot = array();

		$breads = $this->_controller->Navigation->Breads(null);
		$CascadeForBread = $this->_controller->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->_controller->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);
		$DeleteElement = array_shift($breads);
		$DeleteElement = array_shift($breads);
		$Headline = __('Overview',true);

		foreach($breads as $_key => $_data){
			$Headline .= ' > ' . $_data['discription'];
		}

		$SupplierOptionConditions['conditions']['Supplier.equipment_typ !='] = '';
		$SupplierOptionConditions['conditions']['Supplier.deleted'] = 0;
		$SupplierOptionConditions['conditions']['Supplier.cascade_id'] = $Output['AllCascadesUnder'];
		$SupplierOptionConditions['conditions']['Supplier.order_id'] = $Output['SelectOrderByTestingcomp'];

		if(isset($this->_controller->request->data['Statistic']['supplier']) && !empty($this->_controller->request->data['Statistic']['supplier'])){
			$Headline .= ' > ' . $this->_controller->request->data['Statistic']['supplier'];
			$SupplierOptionConditions['conditions']['Supplier.supplier'] = $this->_controller->request->data['Statistic']['supplier'];
		}
		if(isset($this->_controller->request->data['Statistic']['planner']) && !empty($this->_controller->request->data['Statistic']['planner'])){
			$Headline .= ' / ' . $this->_controller->request->data['Statistic']['planner'];
			$SupplierOptionConditions['conditions']['Supplier.planner'] = $this->_controller->request->data['Statistic']['planner'];
		}

		// nur das eine Projekt anzeigen
		/*
		if(isset($this->_controller->request->data['diagramm']) && !empty($this->_controller->request->data['diagramm'])){
			$Headline .= ' / ' . $this->_controller->request->data['diagramm'];
			foreach($Output['Output'] as $_key => $_data){
				if($_key != $this->_controller->request->data['diagramm']){
					unset($Output['Output'][$_key]);
				}
			}
		}
		*/
		
		foreach($Output['Output'] as $_key => $_data){

			$DataForPlot[$_key]['TotalEquipments']['all'] = 0;
			$DataForPlot[$_key]['MaxEquipments'] = 0;

			$SupplierOptionConditions['order'] = array('equipment_typ');
			$SupplierOptionConditions['fields'] = array('Supplier.equipment_typ DISTINCT');

			$SupplierOption = $this->_controller->Supplier->find('all',$SupplierOptionConditions);

			$SupplierOption = Hash::extract($SupplierOption, '{n}.Supplier.equipment_typ');

			if(count($SupplierOption) == 0) continue;

			foreach($SupplierOption as $__key => $__data){

				$SupplierOptionConditions['conditions']['Supplier.equipment_typ'] = $__data;
				$SupplierOptionConditions['fields'] = array();

				$SupplierOptionType = $this->_controller->Supplier->find('list',$SupplierOptionConditions);

				$DataForPlot[$_key]['TotalEquipments']['all'] += count($SupplierOptionType);

				$DiagrammData[$_key][$__data]['option'] = $SupplierOptionType;
				$DiagrammData[$_key][$__data]['status'] = $PriorityStatusArray;

				unset($SupplierOptionConditions['conditions']['Supplier.equipment_typ']);

			}
		}

		foreach($DiagrammData as $_key => $_data){

			foreach($_data as $__key => $__data){

				foreach($__data['option'] as $___key => $___data){

					$ExpeditingConditions['order'] = array('sequence');
					$ExpeditingConditions['conditions']['Expediting.supplier_id'] = $___data;
					$ExpeditingConditions['conditions']['Expediting.deleted'] = 0;

					$Expediting = $this->_controller->Expediting->find('all',$ExpeditingConditions);

					$Priority = 6;

					$DataForPlot[$_key]['BarplotData']['total'][$__key] = 0;
					$DataForPlot[$_key]['BarplotData']['critical'][$__key] = 0;
					$DataForPlot[$_key]['BarplotData']['delayed'][$__key] = 0;
					$DataForPlot[$_key]['BarplotData']['plan'][$__key] = 0;
					$DataForPlot[$_key]['BarplotData']['future'][$__key] = 0;
					$DataForPlot[$_key]['BarplotData']['finished'][$__key] = 0;

					foreach($Expediting as $____key => $____data){

						$Expediting[$____key] = $this->ExpeditingTimePeriode($Expediting,$____data,$____key);

						if($Expediting[$____key]['Expediting']['priority'] < $Priority) $Priority = $Expediting[$____key]['Expediting']['priority'];
					}

					if(isset($PriorityStatusFlip[$Priority])) ++$DiagrammData[$_key][$__key]['status'][$PriorityStatusFlip[$Priority]];

					foreach($DiagrammData[$_key][$__key]['status']  as $____key => $____data){
						$DataForPlot[$_key]['BarplotData'][$____key][$__key] = $____data;
					}
					
				}


				$DataForPlot[$_key]['BarplotData']['total'][$__key] = array_sum($DiagrammData[$_key][$__key]['status']);
				if($DataForPlot[$_key]['BarplotData']['total'][$__key] > $DataForPlot[$_key]['MaxEquipments']) $DataForPlot[$_key]['MaxEquipments'] = $DataForPlot[$_key]['BarplotData']['total'][$__key];
				$DataForPlot[$_key]['TickLabels'][] = $__key;
				
			}
		}

		$TableData = array();

		if(isset($SupplierOption) && isset($DataForPlot[key($DiagrammData)]['BarplotData'])){

			$TableData[0] = array_merge(array(__('total',true)),$SupplierOption);

			foreach($DataForPlot[key($DiagrammData)]['BarplotData'] as $_key => $_data){
				$row = array($_key);
				foreach($_data as $__key => $__data)array_push($row,$__data);
				$TableData[] = $row;
			}
		}

		$locale = $this->_controller->Lang->Discription();

		foreach($TableData[0] as $key => $value){

			if($key == 0) continue;

			$CascadeGroup = $this->_controller->CascadeGroup->find('first',array('conditions' => array('CascadeGroup.id' => $value)));
			$CascadeGroupName = $CascadeGroup['CascadeGroup'][$locale];

			$TableData[0][$key] = $CascadeGroupName;

		}

		$ImageDimension = array();

		$ImageDimension['ImageWidth'] = 0;
		$ImageDimension['ImageHight'] = 0;

		if(isset($DataForPlot[key($DiagrammData)]['BarplotData']) && count($DataForPlot[key($DiagrammData)]['BarplotData']) > 0) {

			$ImageDimension['PlotCount'] = count($DataForPlot[key($DiagrammData)]['BarplotData']['total']);
			$ImageDimension['PlotHightCount'] = $DataForPlot[key($DiagrammData)]['MaxEquipments'];

			$ImageDimension['BarHight'] = 100;
			$ImageDimension['BarWidth'] = 130;

			$ImageDimension['ImageWidthMax'] = $ImageDimension['BarWidth'] * 12;
			$ImageDimension['ImageWidthMin'] = $ImageDimension['BarWidth'] * 5;

			$ImageDimension['ImageWidth'] = $ImageDimension['BarWidth'] * $ImageDimension['PlotCount'];
			$ImageDimension['ImageHight'] = $ImageDimension['BarHight'] * $ImageDimension['PlotHightCount'];

			if($ImageDimension['ImageWidth'] > $ImageDimension['ImageWidthMax']) $ImageDimension['ImageWidth'] = $ImageDimension['ImageWidthMax'];
			if($ImageDimension['ImageWidth'] < $ImageDimension['ImageWidthMin']) $ImageDimension['ImageWidth'] = $ImageDimension['ImageWidthMin'];
			if($ImageDimension['ImageHight'] < 350) $ImageDimension['ImageHight'] = 350;
			if($ImageDimension['ImageHight'] > 700) $ImageDimension['ImageHight'] = 700;

			$ImageDimension['ImageWidth'] + 50;
			$ImageDimension['ImageHight'] + 100;
		}
			$Data['DataForPlot'] = $DataForPlot;
			
			foreach($DataForPlot[key($DiagrammData)]['TickLabels'] as $key => $value){
				
				$CascadeGroup = $this->_controller->CascadeGroup->find('first',array('conditions' => array('CascadeGroup.id' => $value)));
				$CascadeGroupName = $CascadeGroup['CascadeGroup'][$locale];

				unset($DataForPlot[key($DiagrammData)]['TickLabels'][$key]);
				$DataForPlot[key($DiagrammData)]['TickLabels'][$key] = $CascadeGroupName;

				if(isset($DataForPlot['ordered'])){

					unset($DataForPlot['ordered']['TickLabels'][$key]);
					$DataForPlot['ordered']['TickLabels'][$key] = $CascadeGroupName;
	

				}
				if(isset($DataForPlot['not_ordered'])){

					unset($DataForPlot['not_ordered']['TickLabels'][$key]);
					$DataForPlot['not_ordered']['TickLabels'][$key] = $CascadeGroupName;
	
				}
				if(isset($DataForPlot['all_orders'])){

					unset($DataForPlot['all_orders']['TickLabels'][$key]);
					$DataForPlot['all_orders']['TickLabels'][$key] = $CascadeGroupName;
	
				}


				
			}

			if($ReturnMode == true){

			$this->_controller->autoRender = false;

			$ImageDimension['ImageWidth'] = $ImageDimension['ImageWidth'] * 2;
			$ImageDimension['ImageHight'] = $ImageDimension['ImageHight'] * 2;
			$Data['barcolors'] = $barcolors;
			$Data['ImageDimension'] = $ImageDimension;
			$Data['DataForPlot'] = $DataForPlot;
			$Data['DiagrammData'] = $DiagrammData;
			$Data['TableData'] = $TableData;
			$Data['PriorityStatus'] = $PriorityStatus;
			$Data['Headline'] = $Headline;
			$Data['Key'] = $DiagrammData;

			return $Data;

		} else {

			$this->_controller->set('barcolors',$barcolors);
			$this->_controller->set('ImageDimension',$ImageDimension);
			$this->_controller->set('DataForPlot',$DataForPlot);
			$this->_controller->set('DiagrammData',$DiagrammData);
			$this->_controller->set('TableData',$TableData);
			$this->_controller->set('PriorityStatus',$PriorityStatus);
			$this->_controller->set('Headline',$Headline);
			$this->_controller->set('Key',key($DiagrammData));

			$this->_controller->layout = 'blank';
			$this->_controller->autoRender = false;

			if(count($DataForPlot) > 0) $this->_controller->render('diagramm/diagramm', 'jpgraph');
			else $this->_controller->render('diagramm/nodata', 'jpgraph');

		}

	}

	public function GetTimeLineDelivering($data){

		$Output['soll'] = array();
		$Output['ist'] = array();
		$Output['sum'] = array();

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$xScaleDates = array();

		$CurrentDate = new DateTime();
		$CurrentDate->format('Y-m-d');
		$CurrentDate = new DateTime(date_format($CurrentDate, 'Y-m-d'));
		$CurrentDate = $CurrentDate->getTimestamp();

		foreach ($data as $key => $value) {

			if($key == 'statistic') continue;

			foreach ($value as $_key => $_value) {

				if(!isset($_value['suppliers'])) continue;

				foreach ($_value['suppliers'] as $__key => $__value) {

					if(!isset($__value['expeditings'])) continue;
					if(count($__value['expeditings']) == 0) continue;

					foreach ($__value['expeditings'] as $___key => $___value) {

						$xScaleDates[$___value['Expediting']['date_ist_timestamp']] = $___value['Expediting']['date_ist_timestamp'];
						$xScaleDates[$___value['Expediting']['date_soll_timestamp']] = $___value['Expediting']['date_soll_timestamp'];

						if(isset($xScaleDates[$___value['Expediting']['date_soll_timestamp']])){

							if(!isset($Output['soll'][$___value['Expediting']['date_soll_timestamp']])) $Output['soll'][$___value['Expediting']['date_soll_timestamp']] = 0;

							++$Output['soll'][$___value['Expediting']['date_soll_timestamp']];

						}

						if(isset($xScaleDates[$___value['Expediting']['date_ist_timestamp']])){


							if(!isset($Output['ist'][$___value['Expediting']['date_ist_timestamp']])) $Output['ist'][$___value['Expediting']['date_ist_timestamp']] = 0;

							++$Output['ist'][$___value['Expediting']['date_ist_timestamp']];

						}
					}
				}
			}
		}


		unset($xScaleDates[0]);
		unset($Output['ist'][0]);
		unset($Output['soll'][0]);

		asort($xScaleDates);
		asort($Output['ist']);
		asort($Output['soll']);

		$counter_soll = 0;
		$counter_ist = 0;

		$Data['xscale'] = array();
		$Data['soll'] = array();
		$Data['ist'] = array();

		$x = 0;

		foreach ($xScaleDates as $key => $value) {

			$Data['xscale'][$x] = $value;

			if(isset($Output['soll'][$key])) $counter_soll += $Output['soll'][$key];
			if(isset($Output['ist'][$key])) $counter_ist += $Output['ist'][$key];

			$Data['soll'][$x] = $counter_soll;

			if($key > $CurrentDate) $Data['ist'][$x] = 'x';
			else $Data['ist'][$x] = $counter_ist;

			$x++;

		}
/*
		sort($Data['ist']);
		sort($Data['soll']);
		sort($Data['xscale']);
*/

		return $Data;

	}

	public function GetTimeRangeDelivering(){

		$DeliveringSequenceId = 8;
		$CurrentDate = new DateTime();
		$CurrentDate->format('Y-m-d');
		$CurrentDate = new DateTime(date_format($CurrentDate, 'Y-m-d'));
		$CurrentDate = $CurrentDate->getTimestamp();

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		$cascadeIDs = $this->_controller->Navigation->CascadeGetChildrenList($cascadeID);

		if(count($cascadeIDs) == 0) return false;

		$Options = array(
			'fields' => array('area_of_responsibility'),
			'order' => array('area_of_responsibility'),
			'group' => array('area_of_responsibility'),
			'conditions' => array(
				'Supplier.cascade_id' => $cascadeIDs
			)
		);

		if(isset($this->_controller->request->data['Statistic'])){
			if(isset($this->_controller->request->data['Statistic']['area_of_responsibility'])){
				$Options['conditions']['Supplier.area_of_responsibility'] = $this->_controller->request->data['Statistic']['area_of_responsibility'];
				$this->_controller->set('SupplierIndexFilter',$this->_controller->request->data['Statistic']['area_of_responsibility']);
			}
			if(isset($this->_controller->request->data['Statistic']['supplier'])){
//				$Options['conditions']['Supplier.supplier'] = $this->_controller->request->data['Statistic']['supplier'];
//				$this->_controller->set('SupplierIndexFilter',$this->_controller->request->data['Statistic']['supplier']);
			}
		} else {
			if($this->_controller->Session->check('SupplierIndexFilter') === true){

				$SupplierIndexFilter = $this->_controller->Session->read('SupplierIndexFilter');

				if(isset($SupplierIndexFilter[$projectID][$cascadeID])){

					if(isset($SupplierIndexFilter[$projectID][$cascadeID]['area_of_responsibility']) && !isset($Options['conditions']['Supplier.area_of_responsibility'])){
						$Options['conditions']['Supplier.area_of_responsibility'] = $SupplierIndexFilter[$projectID][$cascadeID]['area_of_responsibility'];
						$this->_controller->set('SupplierIndexFilter',$SupplierIndexFilter[$projectID][$cascadeID]['area_of_responsibility']);
					}
/*
					if(isset($SupplierIndexFilter[$projectID][$cascadeID]['supplier']) && !isset($Options['conditions']['Supplier.supplier'])){
						$Options['conditions']['Supplier.supplier'] = $SupplierIndexFilter[$projectID][$cascadeID]['supplier'];
						$SupplierFilter = $Options['conditions']['Supplier.supplier'];
//						$this->_controller->set('SupplierIndexFilter',$SupplierIndexFilter[$projectID][$cascadeID]['area_of_responsibility']);
					}
*/
				}
			}
		}

		$ResponsibilityArea = $this->_controller->Supplier->find('list',$Options);

		if(count($ResponsibilityArea) == 0) return false;

		if(count($ResponsibilityArea) > 1) sort($ResponsibilityArea);

		$Options = array(
			'fields' => array('supplier'),
			'group' => array('supplier'),
			'order' => array('supplier'),
			'conditions' => array(
				'Supplier.cascade_id' => $cascadeIDs
			)
		);

		if(isset($SupplierFilter)){
			$Options['conditions']['Supplier.supplier'] = $SupplierFilter;
		}
		$Suppliers = $this->_controller->Supplier->find('list',$Options);

		if(count($Suppliers) == 0) return false;
		sort($Suppliers);

		$this->_controller->loadModel('Expediting');
		$this->_controller->Expediting->recursive = -1;

		$Output = array();

		$Output['statistic']['expeditings_complete'] = 0;
		$Output['statistic']['expeditings_evaluable'] = 0;
		$Output['statistic']['start_soll_date'] = 0;
		$Output['statistic']['end_soll_date'] = 0;
		$Output['statistic']['start_ist_date'] = 0;
		$Output['statistic']['end_ist_date'] = 0;
		$Output['statistic']['expeditings_not_complete'] = 0;
		$Output['statistic']['days_arrears_count'] = 0;
		$Output['statistic']['days_arrears_numbers'] = 0;
		$Output['statistic']['days_arrears_array'] = array();
		$Output['statistic']['days_arrears'] = 0;

		foreach ($ResponsibilityArea as $key => $value) {

			if(empty($key)) $value_ = __('No responsibilyty',true);
			else $value_ = $value;

			$Output['responsibility'][$value_]['statistic']['expeditings_complete'] = 0;
			$Output['responsibility'][$value_]['statistic']['expeditings_evaluable'] = 0;
			$Output['responsibility'][$value_]['statistic']['start_soll_date'] = 0;
			$Output['responsibility'][$value_]['statistic']['end_soll_date'] = 0;
			$Output['responsibility'][$value_]['statistic']['start_ist_date'] = 0;
			$Output['responsibility'][$value_]['statistic']['end_ist_date'] = 0;
			$Output['responsibility'][$value_]['statistic']['expeditings_not_complete'] = 0;
			$Output['responsibility'][$value_]['statistic']['days_arrears_count'] = 0;
			$Output['responsibility'][$value_]['statistic']['days_arrears_numbers'] = 0;
			$Output['responsibility'][$value_]['statistic']['days_arrears_array'] = array();
			$Output['responsibility'][$value_]['statistic']['days_arrears'] = 0;

			foreach ($Suppliers as $_key => $_value) {

				if($_value == '') $_value_ = __('No supplier',true);
				else $_value_ = $_value;

				$Options = array(
					'conditions' => array(
						'Supplier.cascade_id' => $cascadeIDs,
						'Supplier.area_of_responsibility' => $value,
						'Supplier.supplier' => $_value
					)
				);

				$SupplierIDs = $this->_controller->Supplier->find('list',$Options);

				if(count($SupplierIDs) == 0) continue;

				$Options = array(
					'fields' => array('date_soll','date_ist','status','supplier_id'),
					'order' => array('date_soll'),
					'conditions' => array(
						'Expediting.supplier_id' => $SupplierIDs,
						'Expediting.deleted' => 0,
						'Expediting.sequence' => $DeliveringSequenceId
					)
				);

				$Expeditings = $this->_controller->Expediting->find('all',$Options);

				if(count($Expeditings) == 0) continue;

				$first_soll_date = 0;
				$last_soll_date = 0;
				$days_arrears_counter = 0;
				$days_arrears = array();

				$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_complete'] = count($Expeditings);
				$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_evaluable'] = 0;


				foreach ($Expeditings as $__key => $__value) {

					$Expeditings[$__key]['Expediting']['date_ist_timestamp'] = $this->SqlDateToTimestamp($__value['Expediting']['date_ist']);
					$Expeditings[$__key]['Expediting']['date_soll_timestamp'] = $this->SqlDateToTimestamp($__value['Expediting']['date_soll']);

					 // Wenn weder Soll- noch Istdatum vergeben wurde wird das als Fehler gewertet
					 // und fließt nicht in die Statistik ein
					if($Expeditings[$__key]['Expediting']['date_soll_timestamp'] == 0 && $Expeditings[$__key]['Expediting']['date_ist_timestamp'] == 0){

						if(!isset($Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_end_solldate'])) $Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_end_solldate'] = 1;
						else ++$Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_end_solldate'];

						unset($Expeditings[$__key]);
						continue;

					}

					if($Expeditings[$__key]['Expediting']['date_ist_timestamp'] > 0){
						if(!isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'])) {

							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'] = $Expeditings[$__key]['Expediting']['date_ist'];

						} elseif(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date']) && $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'] > $Expeditings[$__key]['Expediting']['date_ist']) {

							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'] = $Expeditings[$__key]['Expediting']['date_ist'];

						}

						if(!isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'])) {

							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'] = $Expeditings[$__key]['Expediting']['date_ist'];

						} elseif(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date']) && $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'] < $Expeditings[$__key]['Expediting']['date_ist']){

							$Output['responsibility']['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'] = $Expeditings[$__key]['Expediting']['date_ist'];

						}
					}

					// Wenn das Istdatum angegeben ist aber kein Solldatum wird das als Fehler gewertet
					// und fließt nicht in die Statisik eine
					if($Expeditings[$__key]['Expediting']['date_soll_timestamp'] == 0 && $Expeditings[$__key]['Expediting']['date_ist_timestamp'] > 0){

						if(!isset($Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_solldate'])) $Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_solldate'] = 1;
						else ++$Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_solldate'];

					}

					if($Expeditings[$__key]['Expediting']['date_ist_timestamp'] == 0){

						if(!isset($Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_istdate'])) $Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_istdate'] = 1;
						else ++$Output['responsibility'][$value_]['suppliers'][$_value_]['error']['no_start_istdate'];

					}

					if($first_soll_date == 0 && $Expeditings[$__key]['Expediting']['date_soll_timestamp'] > 0){

						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date'] = $__value['Expediting']['date_soll'];
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'] = $__value['Expediting']['date_soll'];
						$first_soll_date = 1;
						$last_soll_date = $__value['Expediting']['date_soll'];

					}

					if($Expeditings[$__key]['Expediting']['date_soll_timestamp'] > 0){
						if($__value['Expediting']['date_soll'] > $last_soll_date){
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'] = $__value['Expediting']['date_soll'];
						}
					}

					if($Expeditings[$__key]['Expediting']['date_soll_timestamp'] < $Expeditings[$__key]['Expediting']['date_ist_timestamp']){
						$date_diff = round(($Expeditings[$__key]['Expediting']['date_ist_timestamp'] - $Expeditings[$__key]['Expediting']['date_soll_timestamp']) / 86400, 0);
					} else {
						$date_diff = 0;
					}

					if($date_diff > 0 && $Expeditings[$__key]['Expediting']['date_soll_timestamp'] > 0 && $Expeditings[$__key]['Expediting']['date_ist_timestamp'] > 0){
						$days_arrears_counter++;
						$days_arrears[$__key] = $date_diff;
					}

					if($Expeditings[$__key]['Expediting']['date_soll_timestamp'] > 0 && $Expeditings[$__key]['Expediting']['date_ist_timestamp'] > 0){
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_evaluable']++;
					}

					if(count($days_arrears) > 0) {

						$days_arrears_sum = array_sum($days_arrears);
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_array'] = $days_arrears;
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_count'] = $days_arrears_counter;

						if($days_arrears_sum == 0){
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears'] = 0;
						} elseif($days_arrears_sum > 0){
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears'] = round($days_arrears_sum / $days_arrears_counter, 0);
						}

					}

					if(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date']) && isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'])){
						if($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date'] < $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date']){
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_diagramm_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date'];
						} else {
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_diagramm_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'];
						}
					}

					if(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date']) && isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'])){
						if($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'] > $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date']){
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_diagramm_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'];
						} else {
							$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_diagramm_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'];
						}
					}

					if(!isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears'])){
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears'] = 0;
					}

					if($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_evaluable'] > 0 && $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_complete'] > 0){
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['progress'] = round($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_evaluable'] / $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_complete'],2);
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['percent'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['progress'] * 100 . '%';
					} else {
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['progress'] = 0;
						$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['percent'] = 0;
					}



					$Expeditings[$__key]['Expediting']['date_diff_days'] = $date_diff;

				}

				$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_not_complete'] = count($Expeditings);
				$Output['responsibility'][$value_]['suppliers'][$_value_]['expeditings'] = $Expeditings;

				$Output['responsibility'][$value_]['statistic']['expeditings_complete'] += $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_complete'];
				$Output['responsibility'][$value_]['statistic']['expeditings_evaluable'] += $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_evaluable'];
				$Output['responsibility'][$value_]['statistic']['expeditings_not_complete'] += $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['expeditings_not_complete'];

				// Startdatum erfassen
				if(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date'])){
					if($Output['responsibility'][$value_]['statistic']['start_soll_date'] == 0){
						$Output['responsibility'][$value_]['statistic']['start_soll_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date'];
					} elseif($Output['responsibility'][$value_]['statistic']['start_soll_date'] > 0 && $Output['responsibility'][$value_]['statistic']['start_soll_date'] > $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date']){
						$Output['responsibility'][$value_]['statistic']['start_soll_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_soll_date'];
					}
				}

				if(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'])){
					if($Output['responsibility'][$value_]['statistic']['start_ist_date'] == 0){
						$Output['responsibility'][$value_]['statistic']['start_ist_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'];
					} elseif($Output['responsibility'][$value_]['statistic']['start_ist_date'] > 0 && $Output['responsibility'][$value_]['statistic']['start_ist_date'] > $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date']){
						$Output['responsibility'][$value_]['statistic']['start_ist_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['start_ist_date'];
					}
				}

				// Enddatum erfassen
				if(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'])){
					if($Output['responsibility'][$value_]['statistic']['end_soll_date'] == 0){
						$Output['responsibility'][$value_]['statistic']['end_soll_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'];
					}
					if($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'] > $Output['responsibility'][$value_]['statistic']['end_soll_date']){
						$Output['responsibility'][$value_]['statistic']['end_soll_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_soll_date'];
					}
				}

				if(isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'])){
					if($Output['responsibility'][$value_]['statistic']['end_ist_date'] == 0){
						$Output['responsibility'][$value_]['statistic']['end_ist_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'];
					}
					if($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'] > $Output['responsibility'][$value_]['statistic']['end_ist_date']){
						$Output['responsibility'][$value_]['statistic']['end_ist_date'] = $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['end_ist_date'];
					}
				}

				if($Output['responsibility'][$value_]['statistic']['start_soll_date'] < $Output['responsibility'][$value_]['statistic']['start_ist_date']){
					$Output['responsibility'][$value_]['statistic']['start_diagramm_date'] = $Output['responsibility'][$value_]['statistic']['start_soll_date'];
				} else {
					$Output['responsibility'][$value_]['statistic']['start_diagramm_date'] = $Output['responsibility'][$value_]['statistic']['start_ist_date'];
				}

				if($Output['responsibility'][$value_]['statistic']['end_soll_date'] > $Output['responsibility'][$value_]['statistic']['end_ist_date']){
					$Output['responsibility'][$value_]['statistic']['end_diagramm_date'] = $Output['responsibility'][$value_]['statistic']['end_soll_date'];
				} else {
					$Output['responsibility'][$value_]['statistic']['end_diagramm_date'] = $Output['responsibility'][$value_]['statistic']['end_ist_date'];
				}

				if($Output['responsibility'][$value_]['statistic']['expeditings_evaluable'] > 0 && $Output['responsibility'][$value_]['statistic']['expeditings_complete'] > 0){
					$Output['responsibility'][$value_]['statistic']['progress'] = round($Output['responsibility'][$value_]['statistic']['expeditings_evaluable'] / $Output['responsibility'][$value_]['statistic']['expeditings_complete'],2);
					$Output['responsibility'][$value_]['statistic']['percent'] = $Output['responsibility'][$value_]['statistic']['progress'] * 100 . '%';
				} else {
					$Output['responsibility'][$value_]['statistic']['progress'] = 0;
					$Output['responsibility'][$value_]['statistic']['percent'] = 0;
				}

				// Verzug erfassen
				if(
					isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears']) &&
					isset($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_count']) &&
					$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears'] > 0 &&
					$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_count'] > 0
				){
					$Output['responsibility'][$value_]['statistic']['days_arrears_count'] += $Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_count'];
					$Output['responsibility'][$value_]['statistic']['days_arrears_numbers'] += array_sum($Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_array']);
					$Output['responsibility'][$value_]['statistic']['days_arrears_array'] = array_merge($Output['responsibility'][$value_]['statistic']['days_arrears_array'],$Output['responsibility'][$value_]['suppliers'][$_value_]['statistic']['days_arrears_array']);
				}
			}

			if($Output['responsibility'][$value_]['statistic']['days_arrears_numbers'] > 0 && $Output['responsibility'][$value_]['statistic']['days_arrears_count'] > 0){
				$Output['responsibility'][$value_]['statistic']['days_arrears'] = round($Output['responsibility'][$value_]['statistic']['days_arrears_numbers']  / $Output['responsibility'][$value_]['statistic']['days_arrears_count'],0);
			}

			// Gesamtstartdatum erfassen
			if(isset($Output['responsibility'][$value_]['statistic']['start_soll_date'])){
				if($Output['statistic']['start_soll_date'] == 0){
					$Output['statistic']['start_soll_date'] = $Output['responsibility'][$value_]['statistic']['start_soll_date'];
				} elseif($Output['statistic']['start_soll_date'] > 0 && $Output['statistic']['start_soll_date'] > $Output['responsibility'][$value_]['statistic']['start_soll_date']) {
					$Output['statistic']['start_soll_date'] = $Output['responsibility'][$value_]['statistic']['start_soll_date'];
				}
			}
			if(isset($Output['responsibility'][$value_]['statistic']['start_ist_date'])){
				if($Output['statistic']['start_ist_date'] == 0){
					$Output['statistic']['start_ist_date'] = $Output['responsibility'][$value_]['statistic']['start_ist_date'];
				} elseif($Output['statistic']['start_ist_date'] > 0 && $Output['statistic']['start_ist_date'] > $Output['responsibility'][$value_]['statistic']['start_ist_date']) {
					$Output['statistic']['start_ist_date'] = $Output['responsibility'][$value_]['statistic']['start_ist_date'];
				}
			}

			// Gesamtenddatum erfassen
			if(isset($Output['responsibility'][$value_]['statistic']['end_soll_date'])){
				if($Output['statistic']['end_soll_date'] == 0){
					$Output['statistic']['end_soll_date'] = $Output['responsibility'][$value_]['statistic']['end_soll_date'];
				}
				if($Output['responsibility'][$value_]['statistic']['end_soll_date'] > $Output['statistic']['end_soll_date']){
					$Output['statistic']['end_soll_date'] = $Output['responsibility'][$value_]['statistic']['end_soll_date'];
				}
			}
			if(isset($Output['responsibility'][$value_]['statistic']['end_ist_date'])){
				if($Output['statistic']['end_ist_date'] == 0){
					$Output['statistic']['end_ist_date'] = $Output['responsibility'][$value_]['statistic']['end_ist_date'];
				}
				if($Output['responsibility'][$value_]['statistic']['end_ist_date'] > $Output['statistic']['end_ist_date']){
					$Output['statistic']['end_ist_date'] = $Output['responsibility'][$value_]['statistic']['end_ist_date'];
				}
			}

			if($Output['statistic']['start_soll_date'] < $Output['statistic']['start_ist_date']){
				$Output['statistic']['start_diagramm_date'] = $Output['statistic']['start_soll_date'];
			} else {
				$Output['statistic']['start_diagramm_date'] = $Output['statistic']['start_ist_date'];
			}

			if($Output['statistic']['end_soll_date'] > $Output['statistic']['end_ist_date']){
				$Output['statistic']['end_diagramm_date'] = $Output['statistic']['end_soll_date'];
			} else {
				$Output['statistic']['end_diagramm_date'] = $Output['statistic']['end_ist_date'];
			}

			$Output['statistic']['expeditings_complete'] += $Output['responsibility'][$value_]['statistic']['expeditings_complete'];
			$Output['statistic']['expeditings_evaluable'] += $Output['responsibility'][$value_]['statistic']['expeditings_evaluable'];
			$Output['statistic']['expeditings_not_complete'] += $Output['responsibility'][$value_]['statistic']['expeditings_not_complete'];
			$Output['statistic']['days_arrears_count'] += $Output['responsibility'][$value_]['statistic']['days_arrears_count'];

			$Output['statistic']['days_arrears_array'] = array_merge($Output['statistic']['days_arrears_array'],$Output['responsibility'][$value_]['statistic']['days_arrears_array']);

			if(count($Output['statistic']['days_arrears_array']) > 0){
				$Output['statistic']['days_arrears'] = round(array_sum($Output['statistic']['days_arrears_array']) / $Output['statistic']['days_arrears_count'],0);
			}

			unset($Output['responsibility'][$value_]['statistic']['days_arrears_array']);
		}

		$Date = new DateTime($Output['statistic']['start_diagramm_date']);
		$Date->sub(new DateInterval('P1M'));
		$Output['statistic']['start_padding_date'] = $Date->format('Y-m-d');

		$Date = new DateTime($Output['statistic']['end_diagramm_date']);
		$Date->add(new DateInterval('P1M'));
		$Output['statistic']['end_padding_date'] = $Date->format('Y-m-d');

		$Output['statistic']['progress'] = round($Output['statistic']['expeditings_evaluable'] / $Output['statistic']['expeditings_complete'],2);
		$Output['statistic']['percent'] = $Output['statistic']['progress'] * 100 . '%';

		return $Output;

	}

	public function SqlDateToTimestamp($sql_date){

		$date_ist = explode('-',$sql_date);

		if(count($date_ist) == 3){

			if(checkdate($date_ist[1], $date_ist[2], $date_ist[0]) == false) return 0;

			$date = date_create($sql_date);

			return $date->getTimestamp();

		} else {

			return 0;

		}
	}

	public function RequestPostdataSession(){

		if($this->_controller->request->params['action'] != 'index') return;
		if(isset($this->_controller->request->data['Statistic'])) return;

		if(
			isset($this->_controller->request->data['refresh_supplier_index_filter']) &&
			$this->_controller->request->data['refresh_supplier_index_filter'] == 1 &&
			!isset($this->_controller->request->data['Statistic'])
		){
			$this->_controller->Session->delete(
				'SupplierIndexFilter.' .
				$this->_controller->request->projectvars['VarsArray'][0] . '.' .
				$this->_controller->request->projectvars['VarsArray'][1]
			);

			return;
		}

		$RequstData = $this->_controller->Session->read(
			'SupplierIndexFilter.' .
			$this->_controller->request->projectvars['VarsArray'][0] . '.' .
			$this->_controller->request->projectvars['VarsArray'][1]
		);

		if(!empty($RequstData)){
			$this->_controller->request->data['Statistic'] = $RequstData;
		}
	}

	public function SavePostdataSession(){

		if($this->_controller->request->params['action'] != 'index') return;
		if(!isset($this->_controller->request->data['Statistic'])) return;

		$this->_controller->Session->delete(
			'SupplierIndexFilter.' .
			$this->_controller->request->projectvars['VarsArray'][0] . '.' .
			$this->_controller->request->projectvars['VarsArray'][1]
		);

		$this->_controller->Session->write(
			'SupplierIndexFilter.' .
			$this->_controller->request->projectvars['VarsArray'][0] . '.' .
			$this->_controller->request->projectvars['VarsArray'][1]
		,
		$this->_controller->request->data['Statistic']
		);

	}

	public function GetExpeditingEvent($Expediting){

		$this->_controller->ExpeditingEvent->recursive = -1;

		$ExpeditingEvent = $this->_controller->ExpeditingEvent->find('all',array(
				'fields' => array('id','date_soll','date_ist','remark','created','user_id'),
				'conditions' => array(
					'ExpeditingEvent.expediting_id' => $Expediting['Expediting']['id'],
					'ExpeditingEvent.expediting_type_id' => $Expediting['Expediting']['expediting_type_id'],
				)
			)
		);

		if(count($ExpeditingEvent) == 0) return $Expediting;

		$Expediting['ExpeditingEvent'] = array();

		foreach ($ExpeditingEvent as $key => $value) {
			$Expediting['ExpeditingEvent'][] = $value['ExpeditingEvent'];
		}

		return $Expediting;
	}

	public function GetExpeditingType($Expediting){


		$ExpeditingType = $this->_controller->ExpeditingType->find('first',
			array(
				'conditions' => array(
					'ExpeditingType.id' => $Expediting['Expediting']['expediting_type_id'],
					'ExpeditingType.deleted' => 0,
				)
			)
		);

		if(count($ExpeditingType) == 0) return $Expediting;

		$Expediting['Expediting']['description'] = $ExpeditingType['ExpeditingType']['description'];

		return $Expediting;
	}

	public function _SaveFastExpeditingEvent($Data){

		$Vars = $this->_ProjectVars();
		if(!isset($Vars['expeditingID'])) return false;

		$Expediting = $this->_controller->Expediting->find('first',
			array(
				'order' => array('sequence ASC'),
				'conditions' => array(
					'Expediting.deleted' => 0,
					'Expediting.id' => $Vars['expeditingID']
				)
			)
		);

		if(count($Expediting) == 0) return $Data;

		$CheckDateSollChange = $this->_CheckDateSollChange($Expediting);
		$CheckDateIstChange = $this->_CheckDateIstChange($Expediting);

		if($CheckDateIstChange === false && $CheckDateSollChange === false) return $Data;

		$InsertData['ExpeditingEvent']['topproject_id'] = $Data['Supplier']['topproject_id'];
		$InsertData['ExpeditingEvent']['cascade_id'] = $Data['Supplier']['cascade_id'];
		$InsertData['ExpeditingEvent']['order_id'] = $Data['Supplier']['order_id'];
		$InsertData['ExpeditingEvent']['supplier_id'] = $Data['Supplier']['id'];
		$InsertData['ExpeditingEvent']['testingcomp_id'] = $Data['Supplier']['testingcomp_id'];
		$InsertData['ExpeditingEvent']['user_id'] = AuthComponent::user('id');
		$InsertData['ExpeditingEvent']['expediting_id'] = $Expediting['Expediting']['id'];
		$InsertData['ExpeditingEvent']['expediting_type_id'] = $Expediting['Expediting']['expediting_type_id'];
		$InsertData['ExpeditingEvent']['date_soll'] = $Expediting['Expediting']['date_soll'];
		$InsertData['ExpeditingEvent']['date_ist'] = $Expediting['Expediting']['date_ist'];
		$InsertData['ExpeditingEvent']['status'] = $Expediting['Expediting']['status'];
		$InsertData['ExpeditingEvent']['karenz'] = $Expediting['Expediting']['karenz'];
		$InsertData['ExpeditingEvent']['sequence'] = $Expediting['Expediting']['sequence'];
		$InsertData['ExpeditingEvent']['discription'] = $Expediting['Expediting']['description'];
		$InsertData['ExpeditingEvent']['remark'] = $Expediting['Expediting']['remark'];

		$this->_controller->ExpeditingEvent->create();

		if($this->_controller->ExpeditingEvent->save($InsertData)){

			return $Data;

		} else {

			return false;

		}

		return $Data;
	}

	public function _SaveExpeditingEvent($Data){

		$CheckDateSollChange = $this->_CheckDateSollChange($Data);
		$CheckDateIstChange = $this->_CheckDateIstChange($Data);

		if($CheckDateSollChange == false && $CheckDateIstChange == false) return $Data;

		if(!is_array($Data)) return false;
		if(count($Data) == 0) return false;

		$InsertData['ExpeditingEvent']['topproject_id'] = $Data['Supplier']['topproject_id'];
		$InsertData['ExpeditingEvent']['cascade_id'] = $Data['Supplier']['cascade_id'];
		$InsertData['ExpeditingEvent']['order_id'] = $Data['Supplier']['order_id'];
		$InsertData['ExpeditingEvent']['supplier_id'] = $Data['Supplier']['id'];
		$InsertData['ExpeditingEvent']['testingcomp_id'] = $Data['Supplier']['testingcomp_id'];
		$InsertData['ExpeditingEvent']['user_id'] = AuthComponent::user('id');
		$InsertData['ExpeditingEvent']['expediting_id'] = $Data['Expediting']['id'];
		$InsertData['ExpeditingEvent']['expediting_type_id'] = $Data['Expediting']['expediting_type_id'];
		$InsertData['ExpeditingEvent']['date_soll'] = $Data['Expediting']['date_soll'];
		$InsertData['ExpeditingEvent']['date_ist'] = $Data['Expediting']['date_ist'];
		$InsertData['ExpeditingEvent']['status'] = $Data['Expediting']['status'];
		$InsertData['ExpeditingEvent']['karenz'] = $Data['Expediting']['karenz'];
		$InsertData['ExpeditingEvent']['sequence'] = $Data['Expediting']['sequence'];
		$InsertData['ExpeditingEvent']['discription'] = $Data['Expediting']['description'];
		$InsertData['ExpeditingEvent']['remark'] = $Data['Expediting']['remark'];

		$this->_controller->ExpeditingEvent->create();

		if($this->_controller->ExpeditingEvent->save($InsertData)){

			return $Data;

		} else {

			$this->_controller->Flash->error(__('Wrong date entered.',true),array('key' => 'error'));
			return false;

		}
	}

	public function SupplierDelete($orderID){

		if(Configure::check('ExpeditingManager') == false) return;
		if(Configure::read('ExpeditingManager') == false) return;

		$this->_controller->loadModel('Supplier');
		$this->_controller->loadModel('Expediting');

		$Supplier = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.id' => $orderID)));

		unset($Supplier['Supplier']['created']);
		unset($Supplier['Supplier']['modified']);

		$Supplier['Supplier']['deleted'] = 1;

		$this->_controller->Supplier->save($Supplier);

	}

	public function CascadeGetChildrenList($id) {

		$output[] = intval($id);

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',array('fields' => array('id'),'conditions'=>array('Cascade.parent'=>$id)));
		$this->_controller->loadModel('TestingcompsCascades');
		$CascadeTestingcompIds = $this->_controller->TestingcompsCascades->find('all',array('fields'=>array('testingcomp_id','cascade_id'),'conditions'=>array('testingcomp_id'=>$this->_controller->Auth->user('testingcomp_id'),'cascade_id'=>$Cascade)));
		
		$CascadeTestingcompIds = Hash::extract($CascadeTestingcompIds, '{n}.TestingcompsCascades.cascade_id');

		$Cascade = array();
		$Cascade = $CascadeTestingcompIds;
			if(count($Cascade) > 0){
			foreach($Cascade as $_key => $_Cascade){
				$output_2 = $this->CascadeGetChildrenList($_Cascade);
				foreach($output_2 as $__key => $__output_2){
					$output[] = $__output_2;
				}
			}
		}

		return $output;
	}

	public function StatisticDetail01(){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];

		$this->_controller->Autorisierung->ConditionsTopprojectsTest($projectID);

		$ThisCascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $cascadeID)));

		// Noch eine Überprüfung einbauen, Berechtigungszugriff
		$AllCascadesUnder = $this->CascadeGetChildrenList(intval($cascadeID));

		// Falls das Array mit den Cascaden nochmals gefilter werden soll, wenn entsprechende Postdaten kommen
		
		$AllCascadesUnder = $this->SelectExpeditingFilter($AllCascadesUnder);
		$SelectOrderByTestingcomp = $this->_controller->Data->SelectOrderByTestingcomp();
		
		$SupplierOption = $this->_controller->Supplier->find('list',
			array(
				'fields' => array(
//					'Supplier.area_of_responsibility DISTINCT'
				),
				'conditions' => array(
					'Supplier.deleted' => 0,
					'Supplier.cascade_id' => $AllCascadesUnder,
					'Supplier.order_id' => $SelectOrderByTestingcomp
				)
			)
		);

		$Output = array();

		$SupplierConditions['fields'] = array('Supplier.delivery_no');
		$SupplierConditions['conditions']['Supplier.deleted'] = 0;
		$SupplierConditions['conditions']['Supplier.cascade_id'] = $AllCascadesUnder;
		$SupplierConditions['conditions']['Supplier.order_id'] = $SelectOrderByTestingcomp;

		if(isset($this->_controller->request->data['Statistic']['supplier']) && !empty($this->_controller->request->data['Statistic']['supplier'])){
			$SupplierConditions['conditions']['Supplier.supplier'] = $this->_controller->request->data['Statistic']['supplier'];
			$SupplierConditions['conditions']['Supplier.supplier !='] = '';
		}

		if(isset($this->_controller->request->data['Statistic']['planner']) && !empty($this->_controller->request->data['Statistic']['planner'])){
			$SupplierConditions['conditions']['Supplier.planner'] = $this->_controller->request->data['Statistic']['planner'];
			$SupplierConditions['conditions']['Supplier.planner !='] = '';
		}

		if(isset($this->_controller->request->data['Statistic']['area_of_responsibility']) && !empty($this->_controller->request->data['Statistic']['area_of_responsibility'])){
			$SupplierConditions['conditions']['Supplier.area_of_responsibility'] = $this->_controller->request->data['Statistic']['area_of_responsibility'];
			$SupplierConditions['conditions']['Supplier.area_of_responsibility !='] = '';
		}

		$Output['Cascade'] = $ThisCascade['Cascade']['discription'];
		$Output['ordered'] = 0;
		$Output['not_ordered'] = 0;
		$Output['all_orders'] = 0;
		if(count($SupplierOption) > 0){

			$Output = $this->_StatisticDetail01($Output,$SupplierConditions);
		}

		$Return['Output'] = $Output;
		$Return['AllCascadesUnder'] = $AllCascadesUnder;
		$Return['SelectOrderByTestingcomp'] = $SelectOrderByTestingcomp;

		return $Return;
	}

	protected function _StatisticDetail01($data,$SupplierConditions){
				
		$SupplierConditions['conditions']['Supplier.delivery_no'] = '';
		unset($SupplierConditions['fields']);	

		$NotOrdered = $this->_controller->Supplier->find('count',$SupplierConditions);

		unset($SupplierConditions['conditions']['Supplier.delivery_no']);
			
		$SupplierConditions['conditions']['Supplier.delivery_no !='] = '';
		
		$Ordered = $this->_controller->Supplier->find('count',$SupplierConditions);
			
		unset($SupplierConditions['conditions']['Supplier.delivery_no !=']);
			
		$AllOrdres = $this->_controller->Supplier->find('count',$SupplierConditions);
			
		$data['ordered'] = $Ordered;
		$data['not_ordered'] = $NotOrdered;
		$data['all_orders'] = $AllOrdres;

		return $data;
	}


	public function CsvOutput($data,$type){

		$Output = array();

		if(!isset($data['responsibility'])) return $Output;

		if(isset($data['statistic']['days_arrears_array'])) unset($data['statistic']['days_arrears_array']);

		$CsvoutputHeadline = array(
			'expeditings_complete' => __('Expeditings/gesamt',true),
			'expeditings_evaluable' => __('Expeditings/auswertbar',true),
			'expeditings_not_complete' => __('Expeditings/nicht auswertbar',true),
			'start_ist_date' => __('Start/Ist',true),
			'end_ist_date' => __('End/Ist',true),
			'start_soll_date' => __('Start/soll',true),
			'end_soll_date' => __('End/soll',true),
			'days_arrears' => __('Verzug/Tage',true),
			'percent' => __('Fortschritt/Prozent',true)
		);

		$CsvoutputTemplate = array(
			'expeditings_complete' => 0,
			'expeditings_evaluable' => 0,
			'expeditings_not_complete' => 0,
			'start_ist_date' => '',
			'end_ist_date' => '',
			'start_soll_date' => '',
			'end_soll_date' => '',
			'days_arrears' => 0,
			'percent' => 0,
		);

		$CsvoutputTemplateDetail = array(
			'expeditings_complete' => '',
			'expeditings_evaluable' => '',
			'expeditings_not_complete' => '',
			'date_ist' => '',
			'end_ist_date' => '',
			'date_soll' => '',
			'end_soll_date' => '',
			'date_diff_days' => 0,
			'percent' => '',
		);

		$Output[] = $CsvoutputHeadline;

		foreach ($data['responsibility'] as $key => $value) {

			$Output[] = array($key);

			foreach ($value as $_key => $_value) {

				if($_key == 'statistic'){

				}

				if($_key == 'suppliers'){

					foreach ($_value as $__key => $__value) {

						$Output[] = array($__key);

						if(isset($__value['statistic'])){

							if(isset($__value['statistic']['days_arrears_array'])) unset($__value['statistic']['days_arrears_array']);

							$Csvoutput = $CsvoutputTemplate;

							foreach ($Csvoutput as $___key => $___value) {

								if(!isset($__value['statistic'][$___key])) continue;

								$Csvoutput[$___key] = $__value['statistic'][$___key];
							}

							$Output[] = $Csvoutput;

						}

						$Output[] = array();
					}
				}
			}
		}

		return $Output;
	}

	public function ExpeditingTimePeriode($Data,$_Data,$Key) {

		$Data[$Key]['Expediting']['trend'] = 0;
		$Data[$Key]['Expediting']['trend_karenz'] = 0;

		$Priority = array('critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);

		$DateIstArray = array();
		$DateIstCheck = false;
		$DateSollArray = array();
		$DateSollCheck = false;
		$DateIstTimeStamp = 0;
		$DateSollTimeStamp = 0;
		$CurrentDate = time();
		$CurrentDate = CakeTime::format($CurrentDate,'%Y-%m-%d');
		$CurrentDate = CakeTime::fromString($CurrentDate);
		$Karenz = 0;

		if($_Data['Expediting']['karenz'] > 0) $Karenz = $_Data['Expediting']['karenz'] * 86400;

		if($_Data['Expediting']['date_ist'] != NULL){
			$DateIstArray = explode('-',$_Data['Expediting']['date_ist']);
			$DateIstCheck = checkdate($DateIstArray[1],$DateIstArray[2],$DateIstArray[0]);
			if($DateIstCheck == true){
				$DateIstTimeStamp = CakeTime::fromString($_Data['Expediting']['date_ist']);
			}
		}
		if($_Data['Expediting']['date_soll'] != NULL){
			$DateSollArray = explode('-',$_Data['Expediting']['date_soll']);
			$DateSollCheck = checkdate($DateSollArray[1],$DateSollArray[2],$DateSollArray[0]);
			if($DateSollCheck == true){
				$DateSollTimeStamp = CakeTime::fromString($_Data['Expediting']['date_soll']);
				$CurrentDateKarenz = $DateSollTimeStamp + $Karenz;
			}
		}

		if($DateIstCheck == false){
			$Data[$Key]['Expediting']['class'] = 'future';
		}
		if($DateIstCheck == true && $DateSollCheck == true){
			$Data[$Key]['Expediting']['class'] = 'finished';
			$Data[$Key]['Expediting']['trend'] = ($DateIstTimeStamp - $DateSollTimeStamp) / 86400;
		}

		if($DateSollCheck == true && $DateIstCheck == false){

			$Data[$Key]['Expediting']['class'] = 'future';

			if($DateSollTimeStamp >= $CurrentDate){
				$Data[$Key]['Expediting']['class'] = 'plan';
				$Data[$Key]['Expediting']['trend'] = floor(($DateSollTimeStamp - $CurrentDate) / 86400);
			} else {
				if($CurrentDateKarenz >= $CurrentDate){
					$Data[$Key]['Expediting']['class'] = 'delayed';
					$Data[$Key]['Expediting']['trend'] = ($CurrentDateKarenz - $DateSollTimeStamp) / 86400;
				} elseif($CurrentDateKarenz < $CurrentDate){
					$Data[$Key]['Expediting']['class'] = 'critical';
					$Data[$Key]['Expediting']['trend'] = ($CurrentDate - $CurrentDateKarenz) / 86400;
				}
			}
		}

		if($DateIstCheck == true && $DateSollCheck == false){
			$Data[$Key]['Expediting']['class'] = 'finished';
			$Data[$Key]['Expediting']['trend'] = 0;
		}

		$Data[$Key]['Expediting']['priority'] = $Priority[$Data[$Key]['Expediting']['class']];

		return $Data[$Key];
	}

	public function ExpeditingStatus($orderID) {

		if(Configure::read('ExpeditingManager') == false) return false;

		App::uses('CakeTime', 'Utility');

		$this->_controller->loadModel('Supplier');
		$this->_controller->loadModel('Expediting');

		$PriorityArray = array(1 => 'critical',2 => 'delayed',3 => 'plan',4 => 'future',5 => 'finished');

		$Supplier = $this->_controller->Supplier->find('first',array('conditions'=>array('Supplier.order_id'=>$orderID)));
                if(count($Supplier) == 0) {


                }

		if (!empty($Supplier))$Expeditings = $this->_controller->Expediting->find('all',array(
			'order' => array('Expediting.sequence ASC'),
			'conditions' => array(
				'Expediting.deleted' => 0,
				'Expediting.supplier_id' => $Supplier['Supplier']['id']
				)
			)
		);

		if(count($Supplier) == 0)  {
			$reportsArrayContainer [] = array(
				'haedline' => __('Add Suppliere',true),
				'class' => array('icon_add'),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('suppliers'),
				'action' =>  array('add/'.join('/',array(implode('/',$this->_controller->request->projectvars['VarsArray'])))),

				'discription' =>array(__('Add Expediting',true))
		);

                } else {

		$Priority = 5;

		foreach($Expeditings as $__key => $__Expeditings){
			$Expeditings[$__key] = $this->ExpeditingTimePeriode($Expeditings,$__Expeditings,$__key);
			if($Expeditings[$__key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$__key]['Expediting']['priority'];
		}

		$Supplier['Expediting'] = $Expeditings;

		$Supplier['Supplier']['priority'] = $Priority;
		$Supplier['Supplier']['class'] = $PriorityArray[$Priority];

        $reportsArrayContainer[] = array(
				'haedline' => __('Expediting',true),
				'class' => array('icon_info'),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('suppliers'),
				'action' => array('overview/'.join('/',array($Supplier['Supplier']['topproject_id'],$Supplier['Supplier']['cascade_id'],$Supplier['Supplier']['id']))),
				'discription' =>array(__('Overview Expediting (only read)',true))
		);

		$reportsArrayContainer[]  = array(
				'haedline' => __('Expediting',true),
				'class' => array('icon_'.$Supplier['Supplier']['class']),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('expeditings'),
				'action' => array('detail/'.join('/',array($Supplier['Supplier']['topproject_id'],$Supplier['Supplier']['cascade_id'],$Supplier['Supplier']['id']))),
				'discription' =>array(__('Overview Expediting (date)',true))
		);

		$reportsArrayContainer[]  = array(
				'class' => array('icon_files'),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('suppliers'),
				'action' => array('files/'.join('/',array($Supplier['Supplier']['topproject_id'],$Supplier['Supplier']['cascade_id'],$Supplier['Supplier']['id']))),
				'discription' =>array(__('File manager',true))
		);

		$reportsArrayContainer[]  = array(
				'class' => array('icon_images'),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('suppliers'),
				'action' => array('images/'.join('/',array($Supplier['Supplier']['topproject_id'],$Supplier['Supplier']['cascade_id'],$Supplier['Supplier']['id']))),
				'discription' =>array(__('Image manager',true))
		);

        $reportsArrayContainer[] = array(

				'class' => array('icon_edit'),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('suppliers'),
				'action' => array('edit/'.join('/',array($Supplier['Supplier']['topproject_id'],$Supplier['Supplier']['cascade_id'],$Supplier['Supplier']['id']))),
				'discription' =>array(__('Suppliere edit',true))
		);

        $reportsArrayContainer[] = array(

				'class' => array('icon_history'),
				'link_class' => array('modal'),
				'parms' => null,
				'controller' => array('suppliers'),
				'action' => array('history/'.join('/',array($Supplier['Supplier']['topproject_id'],$Supplier['Supplier']['cascade_id'],$Supplier['Supplier']['order_id']))),
				'discription' =>array(__('Show history',true))
		);

		}

		$HeaderIsSet = false;

		foreach($reportsArrayContainer as $_key => $_reportsArrayContainer){
			if(!isset($_reportsArrayContainer['haedline'])) continue;
			if(isset($_reportsArrayContainer['haedline']) && $HeaderIsSet === false) $HeaderIsSet = true;
			elseif(isset($_reportsArrayContainer['haedline']) && $HeaderIsSet === true) unset($reportsArrayContainer[$_key]['haedline']);
		}

		return $reportsArrayContainer;
	}

	public function OrderExpeditingMerge($IdIn,$IdFrom,$Direction) {

		$DirectionFromMerge = array();
		$DirectionFromMerge['lauf_nr'] = 'lauf_nr';
		$DirectionFromMerge['topproject_id'] = 'topproject_id';
		$DirectionFromMerge['order_id'] = 'order_id';
		$DirectionFromMerge['testingcomp_id'] = 'testingcomp_id';
		$DirectionFromMerge['development_id'] = 'development_id';
		$DirectionFromMerge['auftrags_nr'] = 'equipment';
		$DirectionFromMerge['order_no'] = 'delivery_no';
		$DirectionFromMerge['referenz_no'] = 'referenz_no';
		$DirectionFromMerge['description'] = 'description';
		$DirectionFromMerge['planer'] = 'planner';
		$DirectionFromMerge['contact_person'] = 'contact_person';
		$DirectionFromMerge['contact_person_mail'] = 'contact_person_mail';
		$DirectionFromMerge['kategorie'] = 'kategorie';
		$DirectionFromMerge['equipmenttyp'] = 'equipment_typ';
		$DirectionFromMerge['areaofresponsibility'] = 'area_of_responsibility';
		$DirectionFromMerge['examination_object'] = 'description';
		$DirectionFromMerge['manufacturer'] = 'supplier';
		$DirectionFromMerge['supplier_project_no'] = 'supplier_project_no';
		$DirectionFromMerge['material'] = 'material_id';
		$DirectionFromMerge['remarks'] = 'remarks';

		if($Direction == 'Order') $From = 'Supplier';
		if($Direction == 'Supplier') $From = 'Order';
		if($Direction == 'Supplier') array_flip($DirectionFromMerge);

		$this->_controller->loadModel($Direction);
		$this->_controller->loadModel($From);
		$this->_controller->$Direction->recursive = -1;
		$this->_controller->$From->recursive = -1;

		$DataFrom = $this->_controller->$From->find('first',array('conditions' => array($From . '.id' => $IdFrom)));
		if(count($DataFrom) == 0) return false;

		if($IdIn == 0 && $Direction == 'Supplier'){
			$DataIn = $this->_controller->$Direction->find('first',array('conditions' => array($Direction . '.order_id' => $IdFrom)));
		} else {
			$DataIn = $this->_controller->$Direction->find('first',array('conditions' => array($Direction . '.id' => $IdIn)));
		}

		if(count($DataIn) == 0) return false;

		$NewData = array();
		$NewData[$Direction]['id'] = $DataIn[$Direction]['id'];

		foreach($DirectionFromMerge as $_key => $_value){
			if(isset($DataFrom[$From][$_value])) if($Direction == 'Order') $NewData[$Direction][$_key] = $DataFrom[$From][$_value];
			if(isset($DataFrom[$From][$_key])) if($Direction == 'Supplier') $NewData[$Direction][$_value] = $DataFrom[$From][$_key];
		}

		if($this->_controller->$Direction->save($NewData)) return true;
		else return false;

	}

	public function SelectExpeditingFilter($AllCascadesUnder) {

		if(!isset($this->_controller->request->data['Statistic'])) return $AllCascadesUnder;

		if(isset($this->_controller->request->data['Statistic'])){
			if(isset($this->_controller->request->data['Statistic']['supplier']) && !empty($this->_controller->request->data['Statistic']['supplier'])){
				$SupplierOption = $this->_controller->Supplier->find('all',array('fields' => array('Supplier.cascade_id DISTINCT'),'conditions' => array('Supplier.deleted' => 0,'Supplier.supplier' => $this->_controller->request->data['Statistic']['supplier'],'Supplier.cascade_id' => $AllCascadesUnder)));
				$SupplierOption = Hash::extract($SupplierOption, '{n}.Supplier.cascade_id');
				$AllCascadesUnder = $SupplierOption;
			}
		}

		return $AllCascadesUnder;
	}

	protected function _CheckDateSollChange($Data){

		if(!isset($this->_controller->request->data['Expediting']['date_soll'])) return false;

		$DateSollNew = new DateTime($this->_controller->request->data['Expediting']['date_soll']);
		$DateSollOld = new DateTime($Data['Expediting']['date_soll']);

		$Interval['soll'] = $DateSollOld->diff($DateSollNew);

		if($Interval['soll']->format('%R%a') == '+0') return false;
		else return true;

	}

	protected function _CheckDateIstChange($Data){

		if(!isset($this->_controller->request->data['Expediting']['date_ist'])) return false;

		$DateIstNew = new DateTime($this->_controller->request->data['Expediting']['date_ist']);
		$DateIstOld = new DateTime($Data['Expediting']['date_ist']);

		$Interval['ist'] = $DateIstOld->diff($DateIstNew);

		if($Interval['ist']->format('%R%a') == '+0') return false;
		else return true;

	}

	protected function _GetFileExtensions($Type){

		$Data = array();

		switch ($Type) {

			case 'file':
			$Data = array('pdf','jpeg','png','gif');
			break;

			case 'image':
			$Data = array('jpg','jpeg','png','gif','JPG','JPEG','PNG');
			break;

		}

		$Data = array_flip($Data);

		return $Data;
	}

	protected function _SaveUploadFileData($Data,$Type){

		$Save = false;

		switch ($Type) {

			case 'file':
			$Save = $this->_controller->Supplierfile->save($Data);
			break;

			case 'image':
			$Save = $this->_controller->Supplierimage->save($Data);
			break;

		}

		return $Save;
	}

	public function UpdateCascadeAndOrderLinking($data){

		if(!isset($data['Testingcomp'])) return;
		if(!isset($data['Testingcomp']['Testingcomp'])) return;
		if(empty($data['Testingcomp']['Testingcomp'])) return;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];

		$Parentlist = $this->_controller->Navigation->CascadeGetParentList($cascadeID,array());

		$this->_controller->loadModel('TestingcompsCascade');
		$this->_controller->loadModel('OrdersTestingcomp');

		if(empty($Parentlist)) return;

		$Parentlist = Hash::extract($Parentlist, '{n}.Cascade.id');

//		$this->_UpdateOrderLinking($orderID,$data['Testingcomp']['Testingcomp']);
		$this->_UpdateCascadeLinking($Parentlist,$data['Testingcomp']['Testingcomp']);

	}

	protected function _UpdateCascadeLinking($Parentlist,$testingcompIds){

		foreach($testingcompIds as $key => $value){

			foreach($Parentlist as $_key => $_value){

				$IsIn = $this->_controller->TestingcompsCascade->find('first',array(
					'conditions' => array(
							'cascade_id' => $_value,
							'testingcomp_id' => $value
						)
					)
				);
	
				if(count($IsIn) == 1) continue;

				$SaveData = array(
					'id' => 0,
					'cascade_id' => $_value,
					'testingcomp_id' => $value,
				);
	
				$Check = $this->_controller->TestingcompsCascade->save($SaveData);

				if($Check === false) return false;	
				
			}	
		}

		return true;
	}

	protected function _UpdateOrderLinking($orderId,$testingcompsIds){

		//Vorhandene Verlinkungen löschen
		$this->_controller->OrdersTestingcomp->deleteAll(array('OrdersTestingcomp.order_id' => $orderId), false);

		//und aus Request neu anlegen
		foreach($testingcompsIds as $key => $value){

			$SaveData = array(
				'id' => 0,
				'order_id' => $orderId,
				'testingcomp_id' => $value,
			);

			$Check = $this->_controller->OrdersTestingcomp->save($SaveData);

			if($Check === false) return false;
			
		}

		return true;
	}

	public function SelectOrderByTestingcomp(){

		$this->_controller->loadModel('Order');
		$this->_controller->loadModel('OrdersTestingcomps');

		$this->_controller->Order->recursive = -1;

		$OrdersTestingcomps = $this->_controller->OrdersTestingcomps->find('all', array(
			'conditions' => array(
				'OrdersTestingcomps.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id')
				)
			)
		);
	
		if (count($OrdersTestingcomps) == 0) {
			return array();
		}
	
		$OrdersTestingcomps = Hash::extract($OrdersTestingcomps, '{n}.OrdersTestingcomps.order_id');
	
		$Orders = $this->_controller->Order->find('list', array('conditions' => array('Order.id' => $OrdersTestingcomps)));
	
		if (count($Orders) == 0) {
			return array();
		}
	
		return $Orders;
	}

	public function CollectMailsendInfos($Data){

		$Data = $this->_CollectMailsendInfosSupplierfile($Data);

		return $Data;
	}

	protected function _CollectMailsendInfosSupplierfile($Data){

		if(!isset($this->_controller->request->data['Supplierfile'])) return $Data;

		$Data = $this->_CollectSupplierSinglefile($Data,$this->_controller->request->data['Supplierfile']['id']);

		return $Data;
	}

	public function ReductExpditingTyps($Data){

		$Expeditings = $this->_controller->Expediting->find('list',array(
			'fields' => array('expediting_type_id'),
			'conditions' => array(
				'Expediting.supplier_id' => $Data['Supplier']['id']
				)
			)
		);
		
		if(count($Expeditings) == 0){

			$Data['FlashMessages'][]  = array('type' => 'warning','message' => __('No expediting steps available.'));

			$Data = $this->_controller->MessagesTool->GenerateFlashMessage($Data);


			return $Data;
		}

		$ExpeditingTypes = $this->_controller->ExpeditingType->find('list',
		array(
			'fields' => array('id','description'),
			'order' => array('description'),
			'conditions' => array(
				'ExpeditingType.id' => $Expeditings,
				'ExpeditingType.deleted' => 0,
				)
			)
		);

		$Data['ExpeditingTypes'] = $ExpeditingTypes;

		return $Data;
	}
}
