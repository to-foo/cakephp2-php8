<?php
class AdvanceToolComponent extends Component {
	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function GetStartCascade(){

		$Output = 0;

		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$AdvancesTopproject = $this->_controller->AdvancesTopproject->find('first',array('conditions' => array('AdvancesTopproject.advance_id' => $id)));

		if(count($AdvancesTopproject ) == 0) return 0;

		$TopprojectId = $AdvancesTopproject['AdvancesTopproject']['topproject_id'];

		if($TopprojectId == 0) return 0;

		$this->_controller->Cascade->recursive = -1;

		$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.topproject_id' => $TopprojectId,'Cascade.level' => 0)));

		if(count($Cascade ) == 0) return 0;

		$Output = $Cascade['Cascade']['id'];

		return $Output;

	}

	public function IsThisMyAdvance($id){

		if(empty($id)) return 0;

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$options = array(
			'conditions' => array(
				'AdvancesCascadesUser.advance_id' => $AdvanceId,
				'AdvancesCascadesUser.cascade_id' => $id,
				'AdvancesCascadesUser.user_id' => $this->_controller->Auth->user('id')

			)
		);

		$AdvancesCascadesUser = $this->_controller->AdvancesCascadesUser->find('first',$options);

		if(count($AdvancesCascadesUser) == 0) return 0;

		return $id;
	}

	public function DeleteEquipment($data){

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $this->_controller->request->projectvars['VarsArray'][1];

		if(!isset($this->_controller->request->data['Order'])) return $data;
		if(!isset($this->_controller->request->data['Order']['cascade_id'])) return $data;
		if(empty($this->_controller->request->data['Order']['cascade_id'])) return $data;
		if($this->_controller->request->data['Order']['cascade_id'] == 0) return $data;

		$options = array(
			'conditions' => array(
			'AdvancesCascade.advance_id' => $AdvanceId,
			'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		$AdvancesCascade['AdvancesCascade']['deleted'] = 1;

		if(count($AdvancesCascade) == 0) return $data;

		if($this->_controller->AdvancesCascade->save($AdvancesCascade)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 008',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		$AdvancesCascadeId = $AdvancesCascade['AdvancesCascade']['id'];

		$options = array(
			'conditions' => array(
				'AdvancesData.advance_id' => $AdvanceId,
				'AdvancesData.cascade_id' => $CascadeId,
			)
		);

		$AdvancesData = $this->_controller->AdvancesData->find('all', $options);

		if(count($AdvancesData) == 0){

			$this->_controller->Flash->success(__('The following equipment has been deleted',true) . ': ' . $data['Cascade']['discription'], array('key' => 'success'));
			return $data;

		}

		foreach ($AdvancesData as $key => $value) {
			$AdvancesData[$key]['AdvancesData']['deleted'] = 1;
		}

		$options = array(
			'conditions' => array(
				'AdvancesDataDependency.advance_id' => $AdvanceId,
				'AdvancesDataDependency.cascade_id' => $CascadeId,
			)
		);

		$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

		if(count($AdvancesDataDependency) == 0){

			$this->_controller->Flash->success(__('The following equipment has been deleted',true) . ': ' . $data['Cascade']['discription'], array('key' => 'success'));
			return $data;

		}

		foreach ($AdvancesDataDependency as $key => $value) {

			$AdvancesDataDependency[$key]['AdvancesDataDependency']['deleted'] = 1;

		}

		if($this->_controller->AdvancesData->saveAll($AdvancesData)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 009',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		if($this->_controller->AdvancesDataDependency->saveAll($AdvancesDataDependency)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 009',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}


		$this->_controller->Flash->success(__('The following equipment has been deleted',true) . ': ' . $data['Cascade']['discription'], array('key' => 'success'));

		$JSONName['parameter']['cascade_id'] = $data['Cascade']['parent'];
		$this->_controller->set('JSONName',$JSONName);

		return $data;
	}


	public function AddEquipment($data){

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $this->_controller->request->projectvars['VarsArray'][1];

		if(!isset($this->_controller->request->data['Order'])) return $data;

		if(empty($this->_controller->request->data['Order']['CascadeGroup']) || $this->_controller->request->data['Order']['CascadeGroup'] == 0){

			$this->_controller->Flash->error('Please select equipment type.', array('key' => 'error'));
			return $data;

		}

		if(empty($this->_controller->request->data['Order']['auftrags_nr'])){

			$this->_controller->Flash->error('Please select descripton.', array('key' => 'error'));
			return $data;

		}

		$CascadeGroup = $this->_controller->request->data['Order']['CascadeGroup'];

		if(!isset($data['CascadeGroup'][$CascadeGroup])){

			$this->_controller->Flash->error('Please select equipment type.', array('key' => 'error'));
			return $data;

		}

		if(!isset($data['Scheme'][$CascadeId])){

			$this->_controller->Flash->error(__('An error has occurred',true), array('key' => 'error'));
			return $data;

		}

		if(!isset($data['Scheme'][$CascadeId]['children'])){

			$this->_controller->Flash->error(__('An error has occurred',true), array('key' => 'error'));
			return $data;

		}

		foreach ($data['Scheme'][$CascadeId]['children'] as $key => $value) {

			if($value['discription'] == $this->_controller->request->data['Order']['auftrags_nr']){

				$this->_controller->Flash->error(__('The following equipment already exists',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
				return $data;

			}
		}

		$Insert = array(
			'topproject_id' => key($data['Topproject']['selected']),
			'discription' => $this->_controller->request->data['Order']['auftrags_nr'],
			'status' => 1,
			'level' => ++$data['Cascade']['level'],
			'parent' => $data['Cascade']['id'],
			'orders' => 1,
			'child' => 0
		);

		$this->_controller->Cascade->create();
		if($this->_controller->Cascade->save($Insert)){

			$EquipmentCascadeId = $this->_controller->Cascade->getLastInsertID();

		} else {

			$this->_controller->Flash->error(__('An error has occurred 001',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'topproject_id' => key($data['Topproject']['selected']),
			'cascade_id' => $EquipmentCascadeId,
			'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
			'auftrags_nr' => $this->_controller->request->data['Order']['auftrags_nr'],
			'status' => 0,
			'deleted' => 0,
		);

		$this->_controller->Order->create();
		if($this->_controller->Order->save($Insert)){

			$OrderId = $this->_controller->Order->getLastInsertID();

		} else {

			$this->_controller->Flash->error(__('An error has occurred 002',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
			'cascade_id' => $EquipmentCascadeId,
		);

		$this->_controller->TestingcompsCascade->create();
		if($this->_controller->TestingcompsCascade->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 003',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'cascade_group_id' => $CascadeGroup,
			'cascade_id' => $EquipmentCascadeId,
		);

		$this->_controller->CascadegroupsCascade->create();
		if($this->_controller->CascadegroupsCascade->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 005',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'order_id' => $OrderId,
			'cascade_id' => $EquipmentCascadeId,
		);

		$this->_controller->CascadesOrder->create();
		if($this->_controller->CascadesOrder->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 006',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		$options = array(
			'conditions' => array(
			'AdvancesCascade.advance_id' => $AdvanceId,
			'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		unset($Insert);
		$Insert = array(
			'advance_id' => $AdvanceId,
			'cascade_id' => $EquipmentCascadeId,
			'start' => $AdvancesCascade['AdvancesCascade']['start'],
			'end' => $AdvancesCascade['AdvancesCascade']['end'],
			'karenz' => $AdvancesCascade['AdvancesCascade']['karenz'],
			'deleted' => 0,
		);

		$this->_controller->AdvancesCascade->create();
		if($this->_controller->AdvancesCascade->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 008',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'advance_id' => $AdvanceId,
			'cascade_id' => $EquipmentCascadeId,
			'order_id' => $OrderId,
			'start' => $AdvancesCascade['AdvancesCascade']['start'],
			'end' => $AdvancesCascade['AdvancesCascade']['end'],
			'karenz' => $AdvancesCascade['AdvancesCascade']['karenz'],
			'status' => 0,
			'deleted' => 0,
		);

		$this->_controller->AdvancesOrder->create();
		if($this->_controller->AdvancesOrder->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 009',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'advance_id' => $AdvanceId,
			'cascade_id' => $EquipmentCascadeId,
			'testincomp_id' => $this->_controller->Auth->user('testingcomp_id'),
		);

		$this->_controller->AdvancesCascadesTestingcomp->create();
		if($this->_controller->AdvancesCascadesTestingcomp->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 010',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);
		$Insert = array(
			'advance_id' => $AdvanceId,
			'cascade_id' => $EquipmentCascadeId,
			'user_id' => $this->_controller->Auth->user('id'),
		);

		$this->_controller->AdvancesCascadesUser->create();
		if($this->_controller->AdvancesCascadesUser->save($Insert)){

		} else {

			$this->_controller->Flash->error(__('An error has occurred 011',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'error'));
			return $data;

		}

		unset($Insert);

		$this->_controller->Flash->success(__('The following equipment has been created',true) . ': ' . $this->_controller->request->data['Order']['auftrags_nr'], array('key' => 'success'));

		$JSONName['parameter']['cascade_id'] = $EquipmentCascadeId;
		$this->_controller->set('JSONName',$JSONName);

		return $data;
	}

	public function CheckAdvanceEndTime(){

		$id = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $this->_controller->request->data['scheme_start'];

		if($id == 0) return false;
		if($CascadeId == 0) return false;

		$options = array(
			'conditions' => array(
				'AdvancesCascade.advance_id' => $id,
				'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($AdvancesCascade) == 0) return false;

		$CurrentDate = new DateTime();

		$End = new DateTime($AdvancesCascade['AdvancesCascade']['end']);
		$End->modify('+'.$AdvancesCascade['AdvancesCascade']['karenz'].' day');

		$End->format('Y-m-d');

		if(strtotime($End->format('Y-m-d')) < strtotime($CurrentDate->format('Y-m-d'))) return false;

		return true;

	}

	public function RoundValue($data){

		if(!is_float($data)) return $data;

		$rouder = 2;

		round($data,$rouder);

		$data = number_format($data, $rouder, '.', '');


		return $data;
	}

	public function RoundValueTest($data){

		if(!is_float($data)) return $data;
		if($data == 0) return $data;

		$rouder = 4;

		round($data,$rouder);

		$data = number_format($data, 2, '.', '');

		return $data;
	}

	public function CollectXML($Advance){

		if(!isset($Advance['Status'])) return $Advance;

		$Xml = array();
		$CountMax = 0;

		foreach ($Advance['Status'] as $key => $value) {

			if($key == 'all_advance_methods') continue;

			$options = array(
				'conditions' => array(
					'AdvancesType.type' => $key,
				)
			);

			$AdvancesType = $this->_controller->AdvancesType->find('first', $options);

			if(isset($Xml[$AdvancesType['AdvancesType']['xml']])) continue;

			$Xml[$AdvancesType['AdvancesType']['xml']]['xml'] = $this->_controller->Xml->DatafromXml($AdvancesType['AdvancesType']['xml'],'file',null);

			$Count = count($Xml[$AdvancesType['AdvancesType']['xml']]['xml']['settings']->section->item);

			if($Count > $CountMax) $CountMax = $Count;

			$options = array(
				'conditions' => array(
					'AdvancesType.xml' => $AdvancesType['AdvancesType']['xml'],
				)
			);

			$AdvancesTypes = $this->_controller->AdvancesType->find('list', $options);

			$Xml[$AdvancesType['AdvancesType']['xml']]['typs'] = $AdvancesTypes;

		}

		$Xml['MaxCount'] = $CountMax;
		$Advance['Xml'] = $Xml;

		return $Advance;
	}

	public function AdvanceElements($Advance){

		$id = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $this->_controller->request->projectvars['VarsArray'][1];
		$testingcomp_id = $this->_controller->Auth->user('testingcomp_id');


		if($CascadeId == 0 && isset($this->_controller->request->data['scheme_start'])) $CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array(
			'conditions' => array(
				'AdvancesCascade.advance_id' => $id,
				'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		$Advance = array_merge($Advance,$AdvancesCascade);

		$this->_controller->Cascade->recursive = -1;

		$options = array(
			'conditions' => array(
				'Cascade.id' => $CascadeId,
			)
		);

		$Cascade = $this->_controller->Cascade->find('first', $options);

		$Advance['AdvancesCascade']['cascade_discription'] = $Cascade['Cascade']['discription'];

		$options = array(
			'conditions' => array(
				'AdvancesCascade.cascade_id' => $Cascade['Cascade']['parent'],
			)
		);

		$ParentCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($ParentCascade) == 0){
			$Advance['AdvancesCascade']['start_global'] = NULL;
			$Advance['AdvancesCascade']['end_global'] = NULL;

		} else {
			$Advance['AdvancesCascade']['start_global'] = $ParentCascade['AdvancesCascade']['start'];
			$Advance['AdvancesCascade']['end_global'] = $ParentCascade['AdvancesCascade']['end'];
		}

		$options = array(
			'fields' => array('testingcomp_id','testingcomp_id'),
			'conditions' => array(
				'AdvancesCascadesTestingcomp.advance_id' => $id,
				'AdvancesCascadesTestingcomp.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascadesTestingcomp = $this->_controller->AdvancesCascadesTestingcomp->find('list', $options);

		$Advance['AdvancesCascade']['AdvancesCascadesTestingcomp'] = $AdvancesCascadesTestingcomp;

		$options = array(
			'fields' => array('order_id'),
			'conditions' => array(
				'AdvancesOrder.advance_id' => $id,
				'AdvancesOrder.cascade_id' => $CascadeId,
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('list', $options);

		$Advance['AdvancesCascade']['AdvancesOrderIDs'] = $AdvancesOrder;

		$this->_controller->Order->recursive = -1;

		$options = array(
			'fields' => array('id','id'),
			'conditions' => array(
				'Order.id' => $Advance['AdvancesCascade']['AdvancesOrderIDs'],
				'Order.deleted' => 0
			)
		);

		$Orders = $this->_controller->Order->find('list', $options);

		$Advance['AdvancesCascade']['AdvancesOrderIn'] = $Orders;

		$options = array(
			'fields' => array('id','auftrags_nr'),
			'conditions' => array(
				'Order.cascade_id' => $CascadeId,
				'Order.deleted' => 0
			)
		);

		$Orders = $this->_controller->Order->find('list', $options);

		$Advance['AdvancesCascade']['AdvancesOrderAll'] = $Orders;

		$options = array(
			'fields' => array('testingcomp_id'),
			'conditions' => array(
				'AdvancesTestingcomp.advance_id' => $id,
			)
		);

		$AdvancesTestingcomp = $this->_controller->AdvancesTestingcomp->find('list',$options);

		if(count($AdvancesTestingcomp) == 0) return $Advance;

		$this->_controller->User->recursive = -1;

		if($Cascade['Cascade']['parent'] > 0){

			$options = array(
				'conditions' => array(
					'AdvancesCascadesUser.advance_id' => $id,
					'AdvancesCascadesUser.cascade_id' => $Cascade['Cascade']['parent'],
				)
			);

			$AdvancesParentCascadesUser = $this->_controller->AdvancesCascadesUser->find('all',$options);
			$AdvancesParentCascadesUser = Hash::extract($AdvancesParentCascadesUser, '{n}.AdvancesCascadesUser.user_id');

		} else {

			$AdvancesParentCascadesUser = NULL;

		}

		foreach ($AdvancesTestingcomp as $key => $value) {

			$options = array(
				'conditions' => array(
					'Testingcomp.id' => $value,
				)
			);

			$Testingcomp = $this->_controller->Testingcomp->find('first',$options);

			$options = array(
				'fields' => array('name'),
				'conditions' => array(
					'User.testingcomp_id' => $value,
					'User.id' => $AdvancesParentCascadesUser
				)
			);

			if($Cascade['Cascade']['level'] == 0 && $Cascade['Cascade']['parent'] == 0){

				$options = array(
					'fields' => array('name'),
					'conditions' => array(
						'User.testingcomp_id' => $value,
					)
				);

			}

			$Users = $this->_controller->User->find('list',$options);
			$Advance['Testingcomps'][$Testingcomp['Testingcomp']['firmenname']] = $Users;

		}

		$options = array(
			'conditions' => array(
				'AdvancesCascadesUser.advance_id' => $id,
				'AdvancesCascadesUser.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascadesUser = $this->_controller->AdvancesCascadesUser->find('all',$options);

		if(count($AdvancesCascadesUser) == 0) $Advance['SelectedUsers'] = array();

		$AdvancesCascadesUser = Hash::extract($AdvancesCascadesUser, '{n}.AdvancesCascadesUser.user_id');
		$Advance['SelectedUsers'] = $AdvancesCascadesUser;

		return $Advance;
	}

	public function UpdateAdvance($AdvanceId,$Update){

		$Output = array();

		$update = array(
			'id' => $AdvanceId,
			'value' => $Update,
		);

		if($this->_controller->AdvancesDataDependency->save($update)){


			$Output['CurrentStatus']['update'] = 'success';
			$Output['CurrentStatus']['message'] = __('The value has been saved.',true);

			$this->_controller->Autorisierung->Logger($AdvanceId, $update);

		} else {

			$Output['CurrentStatus']['update'] = 'error';
			$Output['CurrentStatus']['message'] = __('The value could not be saved.',true);

		}

		$options = array(
			'conditions' => array(
				'AdvancesDataDependency.id' => $AdvanceId,
				'AdvancesDataDependency.deleted' => 0,
			)
		);

		$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('first', $options);

		$Output = array_merge($Output,$AdvancesDataDependency);

		return $Output;
	}

	public function UpdateCascadeStatusStart($data){

		if(!isset($data['Scheme'])) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array(
			'conditions' => array(
				'AdvancesCascade.cascade_id' => $CascadeId,
				'AdvancesCascade.deleted' => 0,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($AdvancesCascade) == 0) return $data;

		$data['AdvancesCascade'] = $AdvancesCascade['AdvancesCascade'];

		$Scheme = $data['Scheme'];

		$data = $this->__CheckCascadeDate($data,$AdvancesCascade);

//		$data = $this->__CollectDailyCascadenStatusStart($data);

		return $data;
	}

	public function UpdateCascadeStatus($data){

//		if(isset($data['AdvancesCascade'])) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array(
			'conditions' => array(
				'AdvancesCascade.cascade_id' => $CascadeId,
				'AdvancesCascade.deleted' => 0,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($AdvancesCascade) == 0) return $data;

		$data['AdvancesCascade'] = $AdvancesCascade['AdvancesCascade'];

		$Scheme = $data['Scheme'];

		$data = $this->__CheckCascadeDate($data,$AdvancesCascade);

//		$data = $this->__CollectDailyCascadenStatus($data);

		return $data;
	}

	protected function __CheckCascadeDate($data,$AdvancesCascade){

		if(count($AdvancesCascade) == 0) return $data;

		$current = new DateTime();
		$delay = $AdvancesCascade['AdvancesCascade']['karenz'];
		$start = new DateTime($AdvancesCascade['AdvancesCascade']['start']);
		$end = new DateTime($AdvancesCascade['AdvancesCascade']['end']);
		$end_delay = new DateTime($AdvancesCascade['AdvancesCascade']['end']);
		$end_delay->modify('+'.$delay.' day');

		$interval = $start->diff($end);
		$interval_delay = $start->diff($end_delay);
		$resttime = $current->diff($end);
		$CurrentStartDiff = $current->diff($start);
		$CurrentEndDiff = $current->diff($end);
		$CurrentEndDelayDiff = $current->diff($end_delay);

		if($CurrentEndDelayDiff->invert == 1) $data['AdvancesCascade']['interval_current'] = 0;
		else $data['AdvancesCascade']['interval_current'] = $CurrentEndDelayDiff->format('%a');

		$data['AdvancesCascade']['interval_past'] = $interval_delay->format('%a') - $data['AdvancesCascade']['interval_current'];
		$data['AdvancesCascade']['interval_delay'] = $interval_delay->format('%a');

		$data['AdvancesCascade']['interval_percent'] = $this->RoundValue(100 * $data['AdvancesCascade']['interval_past'] / $data['AdvancesCascade']['interval_delay']);

		$data['AdvancesCascade']['interval'] = $interval->format('%a');
		$data['AdvancesCascade']['resttime'] = $resttime->format('%a');
		$data['AdvancesCascade']['resttime_invert'] = $resttime->invert == 1;
		$data['AdvancesCascade']['days_gone'] = $CurrentStartDiff->days;
		$data['AdvancesCascade']['current'] = $current->format('Y-m-d');
		$data['AdvancesCascade']['end_delay'] = $end_delay->format('Y-m-d');;

		$data['AdvancesCascade']['status']['end_delay'] = $CurrentEndDelayDiff->invert;
		$data['AdvancesCascade']['status']['end'] = $CurrentEndDiff->invert;
		$data['AdvancesCascade']['status']['start'] = $CurrentStartDiff->invert;

		return $data;

	}

	public function UpdateOrderStatus($Advance){

		if(!isset($this->_controller->request->data['scheme_start'])) return $Advance;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$output = array();

		$options = array(
			'conditions' => array(
				'AdvancesOrder.cascade_id' => $CascadeId,
				'AdvancesOrder.deleted' => 0,
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

		if(count($AdvancesOrder) == 0) return $Advance;

		$options = array(
			'conditions' => array(
				'AdvancesCascade.cascade_id' => $CascadeId,
				'AdvancesCascade.deleted' => 0,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($AdvancesCascade) == 0) return $Advance;

		$output = array_merge($AdvancesCascade,$AdvancesOrder);

		$options = array(
			'conditions' => array(
				'AdvancesData.cascade_id' => $CascadeId,
			)
		);

		if(count($this->_controller->advance_type) == 0) return $Advance;

		$AdvancesData = array();

		foreach ($this->_controller->advance_type as $key => $value) {

			$options['conditions']['AdvancesData.type'] = $key;
			$AdvancesDataPart = $this->_controller->AdvancesData->find('first', $options);

			if(count($AdvancesDataPart) == 0) continue;

			$AdvancesData[] = $AdvancesDataPart;

			unset($options['conditions']['AdvancesData.type']);

		}

		if(count($AdvancesData) == 0) return $Advance;

		$output['AdvancesData'] = $AdvancesData;
		$output['Status'] = array();

		$output['Status']['all_advance_methods']['result']['count'] = 0;
		$output['Status']['all_advance_methods']['result']['open'] = 0;
		$output['Status']['all_advance_methods']['result']['okay'] = 0;
		$output['Status']['all_advance_methods']['result']['error'] = 0;

		foreach ($output['AdvancesData'] as $key => $value) {

			$options = array(
				'conditions' => array(
					'AdvancesType.id' => $value['AdvancesData']['type'],
				)
			);

			$AdvancesType = $this->_controller->AdvancesType->find('first', $options);
			if(count($AdvancesType) == 0) continue;

			$Type = $AdvancesType['AdvancesType']['type'];

			$output['Status'][$Type] = array();

			$output['Status'][$Type]['group_advance'] = 'group_advance_' . $Type;
			$output['Status'][$Type]['advance_count'] = 'advance_count_' . $Type;
			$output['Status'][$Type]['advance_open'] = 'advance_open_' . $Type;
			$output['Status'][$Type]['advance_okay'] = 'advance_okay_' . $Type;
			$output['Status'][$Type]['advance_error'] = 'advance_error_' . $Type;
			$output['Status'][$Type]['advance_line'] = 'advance_line_' . $Type;


			$options = array(
				'conditions' => array(
					'AdvancesDataDependency.cascade_id' => $value['AdvancesData']['cascade_id'],
					'AdvancesDataDependency.order_id' => $value['AdvancesData']['order_id'],
					'AdvancesDataDependency.advances_type_id' => $value['AdvancesData']['type'],
					'AdvancesDataDependency.deleted' => 0,
				)
			);

			$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

			if(count($AdvancesDataDependency) == 0) continue;

			$Status = Hash::extract($AdvancesDataDependency, '{n}.AdvancesDataDependency');
			$StatusOpen = Hash::extract($Status, '{n}[value=0].id');
			$StatusOkay = Hash::extract($Status, '{n}[value=1].id');
			$StatusError = Hash::extract($Status, '{n}[value=2].id');

			$advance_rep = $this->RoundValue(100 * count($StatusError) / count($AdvancesDataDependency));
			$advance_all = $this->RoundValue(100 * (count($StatusOkay) + count($StatusError)) / count($AdvancesDataDependency));

			$output['Status'][$Type]['result']['count'] = count($AdvancesDataDependency);
			$output['Status'][$Type]['result']['open'] = count($StatusOpen);
			$output['Status'][$Type]['result']['okay'] = count($StatusOkay);
			$output['Status'][$Type]['result']['error'] = count($StatusError);

			$output['Status'][$Type]['result']['advance_all_percent'] = $advance_all;
			$output['Status'][$Type]['result']['advance_rep_percent'] = $advance_rep;
			$output['Status'][$Type]['result']['advance_all_deci'] = $advance_all / 100;
			$output['Status'][$Type]['result']['advance_rep_deci'] = $advance_rep / 100;

			$output['Status']['all_advance_methods']['result']['count'] += $output['Status'][$Type]['result']['count'];
			$output['Status']['all_advance_methods']['result']['open'] += $output['Status'][$Type]['result']['open'];
			$output['Status']['all_advance_methods']['result']['okay'] += $output['Status'][$Type]['result']['okay'];
			$output['Status']['all_advance_methods']['result']['error'] += $output['Status'][$Type]['result']['error'];

			if($advance_rep > 0) $output['Status'][$Type]['advance_line_color'] = '#ff0000';
			else $output['Status'][$Type]['advance_line_color'] = '#038000';

		}

		if($output['Status']['all_advance_methods']['result']['count'] > 0){

			$advance_rep = $this->RoundValue(100 * $output['Status']['all_advance_methods']['result']['error'] / $output['Status']['all_advance_methods']['result']['count']);
			$advance_all = $this->RoundValue(100 * ($output['Status']['all_advance_methods']['result']['okay'] + $output['Status']['all_advance_methods']['result']['error']) / $output['Status']['all_advance_methods']['result']['count']);

		} else {

			$advance_rep = 0;
			$advance_all = 0;

		}

		$output['Status']['all_advance_methods']['result']['advance_all_percent'] = $advance_all;
		$output['Status']['all_advance_methods']['result']['advance_rep_percent'] = $advance_rep;
		$output['Status']['all_advance_methods']['result']['advance_all_deci'] = $advance_all / 100;
		$output['Status']['all_advance_methods']['result']['advance_rep_deci'] = $advance_rep / 100;

		if($advance_rep > 0) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff0000';
		else $output['Status']['all_advance_methods']['advance_line_color'] = '#038000';

		$output = array_merge($output,$Advance);

		$this->__UpdateDailyStatus($output);
		$output = $this->__CollectDailyStatus($output);

		return $output;
	}

	protected function __CollectDailyCascadenStatusStart($data){

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $data['AdvancesCascade']['cascade_id'];

		$AdvanceType = $this->_controller->advance_type;
		$AdvanceType = array_flip($AdvanceType);

		return $data;
	}

	protected function __CollectDailyCascadenStatus($data){

		$LastInLine = $this->LastInLineCascade();

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $data['AdvancesCascade']['cascade_id'];

		$AdvanceType = $this->_controller->advance_type;
		$AdvanceType = array_flip($AdvanceType);

		$CascadeChildren = $this->_controller->Navigation->CascadeGetAllChildren($CascadeId,array());

		if(count($CascadeChildren) == 0) return $data;

		$options = array('conditions' => array('Order.cascade_id' => $CascadeChildren));
		$OrderIDs = $this->_controller->Order->find('list', $options);

		if(count($OrderIDs) == 0) return $data;

		$options = array(
			'fields' => array('date','advance'),
			'order' => array('date'),
			'conditions' => array(
				'AdvancesHistory.advance_id' => $AdvanceId,
				'AdvancesHistory.cascade_id' => $CascadeChildren,
			)
		);

		$CurrentDate = CakeTime::format(time(),'%Y-%m-%d');
		$StopDate = date('Y-m-d', strtotime($CurrentDate . ' -1 day'));

		if($StopDate > $CurrentDate) $ScaleDate['Advance'] = array('start' => $data['AdvancesCascade']['start'],'end' => $StopDate);
		else $ScaleDate['Advance'] = array('start' => $data['AdvancesCascade']['start'],'end' => $data['AdvancesCascade']['start']);

		$DateScale = $this->CreateDateScale($ScaleDate);
		$DateScaleTemplate = array_flip($DateScale['AdvanceTimeRange']['DateFormat']);

		foreach($DateScaleTemplate as $key => $value) $DateScaleTemplate[$key] = 0;

		$ProgressLine = array();

		foreach ($data['Status'] as $key => $value) {

			if($key == 'all_advance_methods') continue;
			if(!isset($AdvanceType[$key])) continue;

			$options['conditions']['AdvancesHistory.advances_type_id '] = $AdvanceType[$key];

			foreach($OrderIDs as $_key => $_value){

				$DateScaleOrder = $DateScaleTemplate;

				$options['conditions']['AdvancesHistory.order_id '] = $_value;
				$AdvancesHistory = $this->_controller->AdvancesHistory->find('list', $options);

				if(count($AdvancesHistory) == 0) continue;

				$ValueBefore = 0;

				foreach ($DateScaleOrder as $__key => $__value) {

					if(isset($AdvancesHistory[$__key])){
						$DateScaleOrder[$__key] = $AdvancesHistory[$__key];
						$ValueBefore = $AdvancesHistory[$__key];

					} else {
						$DateScaleOrder[$__key] = $ValueBefore;
					}
				}

				$ProgressLine[$key]['Single'][$_value] = $DateScaleOrder;

			}

			if(!isset($ProgressLine[$key])) continue;
			if(count($ProgressLine[$key]['Single']) == 0) continue;

			if(count($ProgressLine[$key]['Single']) == 1){

				$ProgressLine[$key]['All'] = $ProgressLine[$key]['Single'][key($ProgressLine[$key]['Single'])];

				$Xaxis = $DateScale['AdvanceTimeRange']['TimeStamp'];
				$Yaxis = array_values($ProgressLine[$key]['All']);

				foreach ($Yaxis as $_key => $_value) {
					$Yaxis[$_key] = $_value * 100;
				}

				$ProgressLine[$key]['AdvancesLinePlot']['xaxis'] = $Xaxis;
				$ProgressLine[$key]['AdvancesLinePlot']['yaxis'] = $Yaxis;

			} else {

				$ProgressLine[$key]['All'] = $DateScaleTemplate;

				foreach ($ProgressLine[$key]['All'] as $_key => $_value) {

					foreach ($ProgressLine[$key]['Single'] as $__key => $__value) {

						$ProgressLine[$key]['All'][$_key] += $__value[$_key];

					}

					$Result = number_format($this->RoundValue($ProgressLine[$key]['All'][$_key] / count($ProgressLine[$key]['Single']) * 100), 2, '.', '');

					$ProgressLine[$key]['All'][$_key] = $Result;

					$Xaxis = $DateScale['AdvanceTimeRange']['TimeStamp'];
					$Yaxis = array_values($ProgressLine[$key]['All']);

					$ProgressLine[$key]['AdvancesLinePlot']['xaxis'] = $Xaxis;
					$ProgressLine[$key]['AdvancesLinePlot']['yaxis'] = $Yaxis;

				}
			}

			$data['Status'][$key]['Diagramm'] = $ProgressLine[$key];
		}

		$data['Status']['all_advance_methods'] = array_merge($data['Status']['all_advance_methods'],$DateScale);

		foreach ($data['Status'] as $key => $value) {

			$data['Status'][$key]['Color']['advance_line_color'] = '#038000';

			if($data['AdvancesCascade']['status']['end'] == 1) $data['Status'][$key]['Color']['advance_line_color'] = '#ff7e00';

			if($data['AdvancesCascade']['status']['end_delay'] == 1) $data['Status'][$key]['Color']['advance_line_color'] = '#ff0000';

			if($data['Status'][$key]['result']['advance_all_deci'] == 1) $data['Status'][$key]['Color']['advance_line_color'] = '#038000';

			if($data['Status'][$key]['result']['advance_rep_deci'] > 0) $data['Status'][$key]['Color']['advance_line_color'] = '#ff0000';

			if($value['result']['count'] == 0) unset($data['Status'][$key]);


		}

		return $data;
	}

	protected function __CollectDailyStatus($data){

		$LastInLine = $this->LastInLineCascade();

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $data['AdvancesCascade']['cascade_id'];

		$AdvanceType = $this->_controller->advance_type;
		$AdvanceType = array_flip($AdvanceType);

		if($LastInLine === true){

			$OrderId = $data['AdvancesOrder']['order_id'];
			$options = array(
				'fields' => array('date','advance'),
				'order' => array('date'),
				'conditions' => array(
					'AdvancesHistory.advance_id' => $AdvanceId,
					'AdvancesHistory.cascade_id' => $CascadeId,
					'AdvancesHistory.order_id' => $OrderId,
				)
			);

		}

		if($LastInLine === false){

			$CascadeChildren = $this->_controller->Navigation->CascadeGetAllChildren($CascadeId,array());

			$options = array(
				'fields' => array('date','advance'),
				'order' => array('date'),
				'conditions' => array(
					'AdvancesHistory.advance_id' => $AdvanceId,
					'AdvancesHistory.cascade_id' => $CascadeChildren,
				)
			);

		}

		foreach ($data['Status'] as $key => $value) {

			if($key == 'all_advance_methods') continue;
			if(!isset($AdvanceType[$key])) continue;

			$options['conditions']['AdvancesHistory.advances_type_id '] = $AdvanceType[$key];
			$AdvancesHistory = $this->_controller->AdvancesHistory->find('all', $options);

			if(count($AdvancesHistory) == 0) continue;

			$data['Status'][$key]['AdvancesHistory'] = Hash::extract($AdvancesHistory, '{n}.AdvancesHistory');

			$StartDate = date('Y-m-d', strtotime($data['Status'][$key]['AdvancesHistory'][0]['date'] . ' -1 day'));
			$date = new DateTime($StartDate);
			$timestamp = $date->getTimestamp();

			$data['Status'][$key]['AdvancesLinePlot']['xaxis'][0] = $timestamp;
			$data['Status'][$key]['AdvancesLinePlot']['yaxis'][0] = 0;

			foreach ($data['Status'][$key]['AdvancesHistory'] as $_key => $_value) {

				$date = new DateTime($_value['date']);
				$timestamp = $date->getTimestamp();

				$data['Status'][$key]['AdvancesLinePlot']['xaxis'][] = $timestamp;
				$data['Status'][$key]['AdvancesLinePlot']['yaxis'][] = $_value['advance'] * 100;

			}

		}

		$StartDate = null;
		$EndDate = null;
		$Tickline = array();

		foreach ($data['Status'] as $key => $value) {

			if($key == 'all_advance_methods') continue;

			if(!isset($value['AdvancesHistory'])) continue;
			if(count($value['AdvancesHistory']) == 0) continue;

			foreach ($value['AdvancesHistory'] as $_key => $_value) {


				$currentdate = new DateTime($_value['date']);
				$currenttimestamp = $currentdate->getTimestamp();

				$Tickline['date'][] = $_value['date'];
				$Tickline['timestamp'][] = $currenttimestamp;

				if($StartDate == null){

					$StartDate = $_value['date'];

				} else {

					$startdate = new DateTime($StartDate);
					$starttimestamp = $startdate->getTimestamp();

					if($currenttimestamp < $starttimestamp){
						$StartDate = $_value['date'];
					}
				}

				if($EndDate == null){

					$EndDate = $_value['date'];

				} else {

					$enddate = new DateTime($EndDate);
					$endtimestamp = $enddate->getTimestamp();

					if($endtimestamp < $currenttimestamp){

						$EndDate = $_value['date'];

					}
				}
			}
		}

		if(!isset($Tickline['date'])) return $data;

		$Tickline['date'] = array_unique($Tickline['date']);
		sort($Tickline['date']);
		$Tickline['timestamp'] = array_unique($Tickline['timestamp']);
		sort($Tickline['timestamp']);

		$startdate = new DateTime($StartDate);
		$starttimestamp = $startdate->getTimestamp();

		$enddate = new DateTime($EndDate);
		$endtimestamp = $enddate->getTimestamp();

		$data['Status']['all_advance_methods']['StartDate'] = $StartDate;
		$data['Status']['all_advance_methods']['EndDate'] = $EndDate;
		$data['Status']['all_advance_methods']['StartDateTimestamp'] = $starttimestamp;
		$data['Status']['all_advance_methods']['EndDateTimestamp'] = $endtimestamp;
		$data['Status']['all_advance_methods']['Tickline'] = $Tickline;

		return $data;
	}

	protected function __CreateDailyStatus($data){

		$CurrentDate = CakeTime::format(time(),'%Y-%m-%d');
		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$Insert['advance_id'] = $data['advance_id'];
		$Insert['cascade_id'] = $data['cascade_id'];
		$Insert['order_id'] = $data['order_id'];
		$Insert['advances_type_id'] = $data['type'];
		$Insert['date'] = $CurrentDate;
		$Insert['advance'] = number_format(floatval(0.00), 2, '.', '');

		$this->_controller->AdvancesHistory->create();
		$this->_controller->AdvancesHistory->save($Insert);

	}

	protected function __UpdateDailyStatus($data){

		$CurrentDate = CakeTime::format(time(),'%Y-%m-%d');

		if(strtotime($data['AdvancesCascade']['end']) < strtotime($CurrentDate)) return;

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $data['AdvancesCascade']['cascade_id'];
		$OrderId = $data['AdvancesOrder']['order_id'];

		$AdvanceType = $this->_controller->advance_type;
		$AdvanceType = array_flip($AdvanceType);

		$options = array(
			'conditions' => array(
				'AdvancesHistory.advance_id' => $AdvanceId,
				'AdvancesHistory.cascade_id' => $CascadeId,
				'AdvancesHistory.order_id' => $OrderId,
				'AdvancesHistory.date' => $CurrentDate,
			)
		);

		foreach ($data['Status'] as $key => $value) {

			if($key == 'all_advance_methods') continue;
			if(!isset($AdvanceType[$key])) continue;

			$options['conditions']['AdvancesHistory.advances_type_id '] = $AdvanceType[$key];
			$AdvancesHistory = $this->_controller->AdvancesHistory->find('first', $options);

			if(count($AdvancesHistory) == 1 && isset($value['result'])){

				$Update['id'] = $AdvancesHistory['AdvancesHistory']['id'];
				$Update['advance'] = number_format(floatval($value['result']['advance_all_deci']), 2, '.', '');
				$this->_controller->AdvancesHistory->save($Update);

				unset($Update);

			} else {

				// für jeden Tag muss ein Datum eingefügt werden
				$options = array(
					'order' => array('date DESC'),
					'conditions' => array(
						'AdvancesHistory.advance_id' => $AdvanceId,
						'AdvancesHistory.cascade_id' => $CascadeId,
						'AdvancesHistory.order_id' => $OrderId,
						'AdvancesHistory.advances_type_id' => $AdvanceType[$key],
					)
				);

				$AdvancesHistory = $this->_controller->AdvancesHistory->find('first', $options);

				if(count($AdvancesHistory) == 0){

					$Insert['advance_id'] = $AdvanceId;
					$Insert['cascade_id'] = $CascadeId;
					$Insert['order_id'] = $OrderId;
					$Insert['advances_type_id'] = $AdvanceType[$key];
					$Insert['date'] = $CurrentDate;

					if(isset($value['result'])) $Insert['advance'] = number_format(floatval($value['result']['advance_all_deci']), 2, '.', '');
					else $Insert['advance'] = 0;

					$this->_controller->AdvancesHistory->create();
					$this->_controller->AdvancesHistory->save($Insert);

					$options = array(
						'order' => array('date DESC'),
						'conditions' => array(
							'AdvancesHistory.advance_id' => $AdvanceId,
							'AdvancesHistory.cascade_id' => $CascadeId,
							'AdvancesHistory.order_id' => $OrderId,
							'AdvancesHistory.advances_type_id' => $AdvanceType[$key],
						)
					);

					$AdvancesHistory = $this->_controller->AdvancesHistory->find('first', $options);

				}

				$_StartDate = date('Y-m-d', strtotime($AdvancesHistory['AdvancesHistory']['date'] . ' +1 day'));
				$StartDate = date('Y-m-d', strtotime($_StartDate));
				$StopDate = date('Y-m-d', strtotime($CurrentDate));

				if($StopDate > $StartDate) return;

				$period = new DatePeriod(
					new DateTime($StartDate),
		     	new DateInterval('P1D'),
		     	new DateTime($StopDate)
			 	);

				foreach ($period as $_key => $_value) {

					$_date = new DateTime($_value->format('Y-m-d'));

					$Insert['advance_id'] = $AdvanceId;
					$Insert['cascade_id'] = $CascadeId;
					$Insert['order_id'] = $OrderId;
					$Insert['advances_type_id'] = $AdvanceType[$key];
					$Insert['date'] = $_date->format('Y-m-d');
					$Insert['advance'] = $AdvancesHistory['AdvancesHistory']['advance'];

					$this->_controller->AdvancesHistory->create();
					$this->_controller->AdvancesHistory->save($Insert);

					unset($Insert);

				}

				if(isset($value['result'])){

					$Insert['advance_id'] = $AdvanceId;
					$Insert['cascade_id'] = $CascadeId;
					$Insert['order_id'] = $OrderId;
					$Insert['advances_type_id'] = $AdvanceType[$key];
					$Insert['date'] = $CurrentDate;
					$Insert['advance'] = number_format(floatval($value['result']['advance_all_deci']), 2, '.', '');

					$this->_controller->AdvancesHistory->create();
					$this->_controller->AdvancesHistory->save($Insert);

					unset($Insert);
				}

			}
		}
	}

	public function CollectAdvanceOrders($data){

		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$this->_controller->Order->recursive = -1;

		$options = array('conditions' => array('AdvancesOrder.advance_id' => $id));
		$AdvancesOrder = $this->_controller->AdvancesOrder->find('all', $options);

		if(count($AdvancesOrder) == 0) {
			$data['AdvancesOrder'] = array();
			return $data;
		}

		$data['AdvancesOrder'] = $AdvancesOrder;
		$AdvancesType = $this->_controller->advance_type;

		foreach ($data['AdvancesOrder'] as $key => $value) {

			$options = array('conditions' => array('Order.id' => $value['AdvancesOrder']['order_id']));
			$Order = $this->_controller->Order->find('first', $options);

			if(count($Order) == 0) continue;

			$ParentList = $this->_controller->Navigation->CascadeGetParentList($Order['Order']['cascade_id'],array());

			if(count($ParentList) > 0){
				$Parent = array();
				foreach ($ParentList as $_key => $_value) $Parent[$_value['Cascade']['id']] = $_value['Cascade']['discription'];
				$Parent = array_reverse($Parent, true);
				$data['AdvancesOrder'][$key]['CascadeParent'] = $Parent;
			}

			$data['AdvancesOrder'][$key]['Order'] = $Order['Order'];

			$options = array(
				'conditions' => array(
					'AdvancesData.advance_id' => $value['AdvancesOrder']['advance_id'],
					'AdvancesData.order_id' => $value['AdvancesOrder']['order_id'],
					'AdvancesData.status' => 0,
					'AdvancesData.deleted' => 0,
				)
			);

			$AdvancesData = $this->_controller->AdvancesData->find('all', $options);

			if(count($AdvancesData) == 0) continue;

			$ProgessTypes = array();

			foreach ($AdvancesData as $_key => $_value) {

				if(!isset($AdvancesType[$_value['AdvancesData']['type']])) continue;

				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['type'] = $_key;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['count'] = 0;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['open'] = 0;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['okay'] = 0;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['error'] = 0;

				$AdvancesData[$_key]['AdvancesData']['description'] = __($AdvancesType[$_value['AdvancesData']['type']],true);
				$AdvancesData[$_key]['AdvancesData']['AdvancesDataDependency'][$AdvancesType[$_value['AdvancesData']['type']]] = array();

				$options = array(
					'conditions' => array(
						'AdvancesDataDependency.advance_id' => $_value['AdvancesData']['advance_id'],
						'AdvancesDataDependency.order_id' => $_value['AdvancesData']['order_id'],
						'AdvancesDataDependency.advances_data_id' => $_value['AdvancesData']['id'],
						'AdvancesDataDependency.deleted' => 0,
					)
				);

				$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

				if(count($AdvancesDataDependency) > 0){

					$AdvancesData[$_key]['AdvancesData']['AdvancesDataDependency'][$AdvancesType[$_value['AdvancesData']['type']]] = $AdvancesDataDependency;
					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['count'] = count($AdvancesDataDependency);

					$Status = Hash::extract($AdvancesDataDependency, '{n}.AdvancesDataDependency');
					$StatusOpen = Hash::extract($Status, '{n}[value=0].id');
					$StatusOkay = Hash::extract($Status, '{n}[value=1].id');
					$StatusError = Hash::extract($Status, '{n}[value=2].id');

					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['open'] = count($StatusOpen);
					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['okay'] = count($StatusOkay);
					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['error'] = count($StatusError);
				}
			}

			$data['AdvancesOrder'][$key]['AdvancesDataTypes'] = $ProgessTypes;
			$data['AdvancesOrder'][$key]['AdvancesData'] = $AdvancesData;

			if(count($data['AdvancesOrder'][$key]['CascadeParent']) > 0){
				$output = $this->CollectOrderSheme($data['AdvancesOrder'][$key],$data['AdvancesOrder'][$key]['CascadeParent'],$data['Scheme']);
				$data['AdvancesOrder'][$key]['AdvancesDataOutput'] = $output;
			}
		}

		return $data;
	}

	public function CreateSchemaMenue($data){

		if(count($data) == 0){

			$data['Menue'] = array();
			return $data;

		}

		if(isset($this->_controller->request->data['select_menue']) && count($this->_controller->request->data['select_menue'])){

			$data['Menue'] = $this->_controller->request->data['select_menue'];
			return $data;

		}

		$Output = array();
		$Output[0] = ' ';
		$Space = '';

		foreach ($data['Scheme'] as $key => $value) {

			if(!isset($value['discription'])) continue;

			$Output[$key] = $Space . $value['discription'];

			$Output = $this->__CreateSchemaMenue($value,$Output,$Space,0);

		}

		$data['Menue'] = $Output;

		return $data;

	}

	protected function __CreateSchemaMenue($data,$Output,$Space,$Deep){

		if(!isset($data['children'])) return $Output;

		$deep = 2;
		$Spacer = '-';
		$Space = $Space . $Spacer;

		if($deep == $Deep) return $Output;
		++$Deep;

		foreach ($data['children'] as $key => $value) {

			if(!isset($value['discription'])) continue;

			$Output[$key] = $Space . $value['discription'];
			$Output = $this->__CreateSchemaMenue($value,$Output,$Space,$Deep);

		}

		return $Output;
	}

	protected function __CollectAdvanceOrders($key){

		$id = $this->_controller->request->projectvars['VarsArray'][0];

		$AdvanceType = $this->_controller->advance_type;
		$AdvanceType = array_flip($AdvanceType);

		$this->_controller->Order->recursive = -1;
		$options = array('conditions' => array('Order.cascade_id' => $key));
		$OrderIds = $this->_controller->Order->find('list', $options);

		$options = array('conditions' => array('AdvancesOrder.advance_id' => $id,'AdvancesOrder.order_id' => $OrderIds,'AdvancesOrder.deleted' => 0));
		$AdvancesOrder = $this->_controller->AdvancesOrder->find('all', $options);

		if(count($AdvancesOrder) == 0) return array();
		if(count($this->_controller->advance_type) == 0) return array();

		$data['AdvancesOrder'] = $AdvancesOrder;
		$AdvancesType = $this->_controller->advance_type;

		foreach ($data['AdvancesOrder'] as $key => $value) {

			$options = array('conditions' => array('Order.id' => $value['AdvancesOrder']['order_id']));
			$Order = $this->_controller->Order->find('first', $options);

			if(count($Order) == 0) continue;

			$options = array(
				'conditions' => array(
					'AdvancesData.advance_id' => $value['AdvancesOrder']['advance_id'],
					'AdvancesData.order_id' => $value['AdvancesOrder']['order_id'],
					'AdvancesData.status' => 0,
					'AdvancesData.deleted' => 0,
				)
			);

			$AdvancesData = array();

			foreach ($this->_controller->advance_type as $_key => $_value) {

				$options['conditions']['AdvancesData.type'] = $_key;
				$AdvancesDataPart = $this->_controller->AdvancesData->find('first', $options);

				if(count($AdvancesDataPart) == 0) continue;
				$AdvancesData[] = $AdvancesDataPart ;

				unset($options['conditions']['AdvancesData.type']);

			}

			if(count($AdvancesData) == 0){

				$data['AdvancesOrder'][$key]['AdvancesOrder']['topproject_id'] = $Order['Order']['topproject_id'];
				$data['AdvancesOrder'][$key]['AdvancesOrder']['cascade_id'] = $Order['Order']['cascade_id'];
				$data['AdvancesOrder'][$key]['AdvancesOrder']['name'] = $Order['Order']['auftrags_nr'];
				$data['AdvancesOrder'][$key]['AdvancesDataTypes'] = array();
				$data['AdvancesOrder'][$key]['AdvancesData'] = array();

				continue;
			}

			$ProgessTypes = array();

			foreach ($AdvancesData as $_key => $_value) {

				$AdvancesData[$_key]['AdvancesData']['name'] = $Order['Order']['auftrags_nr'];

				if(!isset($AdvancesType[$_value['AdvancesData']['type']])) continue;

				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['type'] = $_key;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['count'] = 0;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['open'] = 0;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['okay'] = 0;
				$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['error'] = 0;

				$AdvancesData[$_key]['AdvancesData']['description'] = __($AdvancesType[$_value['AdvancesData']['type']],true);
				$AdvancesData[$_key]['AdvancesData']['AdvancesDataDependency'][$AdvancesType[$_value['AdvancesData']['type']]] = array();

				$options = array(
					'conditions' => array(
						'AdvancesDataDependency.advance_id' => $_value['AdvancesData']['advance_id'],
						'AdvancesDataDependency.order_id' => $_value['AdvancesData']['order_id'],
						'AdvancesDataDependency.advances_data_id' => $_value['AdvancesData']['id'],
						'AdvancesDataDependency.deleted' => 0,
					)
				);

				$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

				if(count($AdvancesDataDependency) > 0){

					$AdvancesData[$_key]['AdvancesData']['AdvancesDataDependency'][$AdvancesType[$_value['AdvancesData']['type']]] = $AdvancesDataDependency;
					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['count'] = count($AdvancesDataDependency);

					$Status = Hash::extract($AdvancesDataDependency, '{n}.AdvancesDataDependency');
					$StatusOpen = Hash::extract($Status, '{n}[value=0].id');
					$StatusOkay = Hash::extract($Status, '{n}[value=1].id');
					$StatusError = Hash::extract($Status, '{n}[value=2].id');

					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['open'] = count($StatusOpen);
					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['okay'] = count($StatusOkay);
					$ProgessTypes[$AdvancesType[$_value['AdvancesData']['type']]]['error'] = count($StatusError);
				}
			}

			$data['AdvancesOrder'][$key]['AdvancesOrder']['topproject_id'] = $Order['Order']['topproject_id'];
			$data['AdvancesOrder'][$key]['AdvancesOrder']['cascade_id'] = $Order['Order']['cascade_id'];
			$data['AdvancesOrder'][$key]['AdvancesOrder']['name'] = $Order['Order']['auftrags_nr'];
			$data['AdvancesOrder'][$key]['AdvancesDataTypes'] = $ProgessTypes;
			$data['AdvancesOrder'][$key]['AdvancesData'] = $AdvancesData;

			$NewSorData = array();

			foreach ($AdvanceType as $_key => $_value) {

				if(!isset($data['AdvancesOrder'][$key]['AdvancesDataTypes'][$_key])) continue;

				$NewSorData[$_key] = $data['AdvancesOrder'][$key]['AdvancesDataTypes'][$_key];

			}

			$data['AdvancesOrder'][$key]['AdvancesDataTypes'] = $NewSorData;
		}

		return $data;
	}

	public function SeparadeAdvanceOrders($data){

		$CascadeId = $this->_controller->request->data['scheme_start'];

		if($CascadeId == 0) return $data;

		if(isset($data['Scheme'][$CascadeId]['children'])  && count($data['Scheme'][$CascadeId]['children']) > 0){
			$data['Scheme'] = $data['Scheme'][$CascadeId]['children'];
			return $data;
		}

		if(isset($data['Scheme'][$CascadeId]['children'])  && count($data['Scheme'][$CascadeId]['children']) == 0){
			return $data;
		}

		if(isset($data['Scheme'][$CascadeId]['children']) && count($data['Scheme'][$CascadeId]['children']) == 0) return $data;

		foreach ($data['Scheme'] as $key => $value) {

			$Output = $this->__SeparadeAdvanceOrdersRecursive($value['children'],$CascadeId,$key);

			if(is_array($Output) && count($Output) > 0){
				$data['Scheme'] = $Output;
				return $data;
			}
		}
	}

	public function SeparadeLastAdvanceOrder($data){

		if(isset($data['Scheme']['AdvancesOrder'])) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array('conditions' => array('Cascade.parent' => $CascadeId));
		$Cascade = $this->_controller->Cascade->find('first',$options);

		if(count($Cascade) > 0) return $data;

		$Output = array();

		$result = $this->__CollectAdvanceOrders($CascadeId);

		if(count($result) == 0) return $data;

		$Output['discription'] = $data['Cascade']['discription'];
		$Output['children'] = array();
		$Output['AdvancesOrder'] = $result['AdvancesOrder'];

		if(count($result) == 0) return $data;

		if(isset($data['Scheme'][$CascadeId]['status'])) $Output['status'] = $data['Scheme'][$CascadeId]['status'];
		else $Output['status'] = array();

		$data['Scheme'] = $Output;

		return $data;
	}

	protected function __SeparadeAdvanceOrdersRecursive($data,$CascadeId,$CurrentKey){

		if(isset($data[$CascadeId]['children'])) return $data[$CascadeId]['children'];

		foreach ($data as $key => $value) {

			$Output = array();

			if(isset($value['children'][$CascadeId]['children'])){

				if(count($value['children'][$CascadeId]['children']) == 0 && isset($value['children'][$CascadeId]['status'])){
					$result = $this->__CollectAdvanceOrders($CascadeId);
					$value['AdvancesOrder'] = $result['AdvancesOrder'];
					return $value;
				}

				return $value['children'][$CascadeId]['children'];
			}
			if(count($value['children']) > 0) $Output = $this->__SeparadeAdvanceOrdersRecursive($value['children'],$CascadeId,$key);

			if(is_array($Output) && count($Output) > 0) return $Output;

		}
	}

	public function SepardeAdvanceOrdersDescription($data){

		if(Configure::check('CascadeLoadModules') === false) return $data;
		if(Configure::read('CascadeLoadModules') === false) return $data;

		if(!isset($data['Scheme']['AdvancesOrder'])) return $data;
		if(count($data['Scheme']['AdvancesOrder']) == 0) return $data;

		$locale = $this->_controller->Lang->Discription();
		$CascadeId = $this->_controller->request->data['scheme_start'];

		foreach ($data['Scheme']['AdvancesOrder'] as $key => $value) {

			$options = array('conditions'=>array('CascadegroupsCascade.cascade_id' => $value['AdvancesOrder']['cascade_id']));
			$CascadegroupsCascadeID = $this->_controller->CascadegroupsCascade->find('first',$options);

			if(count($CascadegroupsCascadeID) == 1){

				$options = array('conditions' => array('CascadeGroup.id' => $CascadegroupsCascadeID['CascadegroupsCascade']['cascade_group_id']));
				$CascadeGroup = $this->_controller->CascadeGroup->find('first',$options);

				$data['Scheme']['AdvancesOrder'][$key]['AdvancesOrder']['cascade_cat'] = $CascadeGroup['CascadeGroup'][$locale];
				$data['Scheme']['AdvancesOrder'][$key]['AdvancesOrder']['cascade_cat_id'] = $CascadeGroup['CascadeGroup']['id'];

			} else {

				$data['Scheme']['AdvancesOrder'][$key]['AdvancesOrder']['cascade_cat'] = __('unassigned',true);
				$data['Scheme']['AdvancesOrder'][$key]['AdvancesOrder']['cascade_cat_id'] = 0;

			}

			$options = array('conditions' => array('Cascade.id' => $value['AdvancesOrder']['cascade_id']));
			$Cascade = $this->_controller->Cascade->find('first',$options);
			$options = array('conditions' => array('Cascade.id' => $Cascade['Cascade']['parent']));
			$Cascade = $this->_controller->Cascade->find('first',$options);
			$data['Scheme']['AdvancesOrder'][$key]['AdvancesOrder']['cascade_cat_parent_name'] = $Cascade['Cascade']['discription'];
			$data['Scheme']['AdvancesOrder'][$key]['AdvancesOrder']['cascade_cat_parent_id'] = $Cascade['Cascade']['id'];

		}

		$data = $this->__CreateBreadCrumpNavi($data);

		$data['CascadeGroupId'] = $data['Scheme']['AdvancesOrder'][0]['AdvancesOrder']['cascade_cat_id'];

		return $data;
	}

	protected function __CreateBreadCrumpNavi($data){

		if(isset($this->_controller->request->data['cascade_group_id'])) $CascadeGroupId = $this->_controller->request->data['cascade_group_id'];

		$locale = $this->_controller->Lang->Discription();
		$CascadeId = $this->_controller->request->data['scheme_start'];


		$BreadcrumpList = array_reverse($this->_controller->Navigation->CascadeGetParentList($CascadeId,array()));
		$BreadcrupArray = array();
		foreach ($BreadcrumpList as $key => $value) {

			$BreadcrupArray[] = array(
				'title' => $value['Cascade']['discription'],
				'class' => 'scheme_menue_link advance_breadcrump',
				'controller' => 'advances',
				'action' => 'json_scheme',
				'params' => $this->_controller->request->projectvars['VarsArray'],
				'data' => array(
					'scheme_start' => $value['Cascade']['id']
				),
			);
		}

		if(isset($CascadeGroupId) && $CascadeGroupId == 0){

			$BreadcrupArray[] = array(
				'title' => __('unassigned',true),
				'class' => 'scheme_menue_link advance_breadcrump cascade_cat',
				'controller' => 'advances',
				'action' => 'json_scheme',
				'params' => $this->_controller->request->projectvars['VarsArray'],
				'end' => 1,
				'data' => array(
					'scheme_start' => $value['Cascade']['id'],
					'cascade_group_id' => $CascadeGroupId
				),
			);

			$data['BreadcrumpArray'] = $BreadcrupArray;

			return $data;

		}

		if(isset($CascadeGroupId) && $CascadeGroupId > 0){

			$options = array('conditions' => array('CascadeGroup.id' => $CascadeGroupId));
			$CascadeGroup = $this->_controller->CascadeGroup->find('first',$options);

			$BreadcrupArray[] = array(
				'title' => $CascadeGroup['CascadeGroup'][$locale],
				'class' => 'scheme_menue_link advance_breadcrump cascade_cat',
				'controller' => 'advances',
				'action' => 'json_scheme',
				'params' => $this->_controller->request->projectvars['VarsArray'],
				'end' => 1,
				'data' => array(
					'scheme_start' => $value['Cascade']['id'],
					'cascade_group_id' => $CascadeGroupId
				),
			);

			$data['BreadcrumpArray'] = $BreadcrupArray;

			return $data;

		}

		$LastCrumpArray = array_pop($BreadcrupArray);

		if(!isset($data['Scheme']['AdvancesOrder'][0]['AdvancesOrder'])){

			$LastCrumpArray['end'] = 1;
			$BreadcrupArray[] = $LastCrumpArray;
			$data['BreadcrumpArray'] = $BreadcrupArray;

			return $data;
		}

		if(isset($data['Scheme']['AdvancesOrder'][0]['AdvancesOrder']['cascade_cat'])) $CascadeCat = $data['Scheme']['AdvancesOrder'][0]['AdvancesOrder']['cascade_cat'];
		else $CascadeCat = __('unassigned',true);

		if(isset($data['Scheme']['AdvancesOrder'][0]['AdvancesOrder']['cascade_cat_id'])) $CascadeCatId = $data['Scheme']['AdvancesOrder'][0]['AdvancesOrder']['cascade_cat_id'];
		else $CascadeCatId = 0;

		$BreadcrupArray[] = array(
			'title' => $CascadeCat,
			'class' => 'scheme_menue_link advance_breadcrump cascade_cat',
			'controller' => 'advances',
			'action' => 'json_scheme',
			'params' => $this->_controller->request->projectvars['VarsArray'],
			'data' => array(
				'scheme_start' => $data['Cascade']['parent'],
				'cascade_group_id' => $CascadeCatId
			),
		);

		$LastCrumpArray['end'] = 1;

		$BreadcrupArray[] = $LastCrumpArray;

		$data['BreadcrumpArray'] = $BreadcrupArray;

		return $data;

	}

	public function BreadcrumpArrayCorrectur($data){

		if(!isset($data['BreadcrumpArray'])) return $data;
		if(!isset($data['BreadcrumpArray'][0])) return $data;

		unset($data['BreadcrumpArray'][0]);

		return $data;

	}

	public function SepardeAdvanceCascadeCatsDescription($data){

		if(Configure::check('CascadeLoadModules') === false) return $data;
		if(Configure::read('CascadeLoadModules') === false) return $data;
		if(!isset($this->_controller->request->data['scheme_start'])) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$BreadcrumpList = array_reverse($this->_controller->Navigation->CascadeGetParentList($CascadeId,array()));
		$BreadcrumpList = Hash::extract($BreadcrumpList, '{n}.Cascade.discription');
		$Breadcrump = implode(' > ',$BreadcrumpList);
		$data['CascadeGroupTitle'] = $Breadcrump;

		$data = $this->__CreateBreadCrumpNavi($data);

		if(!isset($this->_controller->request->data['scheme_start'])) return $data;
		if(!isset($this->_controller->request->data['cascade_group_id'])) return $data;

		if($this->_controller->request->data['scheme_start'] == 0) return $data;

		if(!isset($data['Scheme'][key($data['Scheme'])])) return $data;
		if(!isset($data['Scheme'][key($data['Scheme'])]['children'])) return $data;
		if(count($data['Scheme'][key($data['Scheme'])]['children']) > 0) return $data;

		$CascadeGroupId = $this->_controller->request->data['cascade_group_id'];
		$CascadeId = $this->_controller->request->data['scheme_start'];
		$data = $this->__SepardeAdvanceCascadeCatsDescription($data,$CascadeGroupId,$CascadeId);

		return $data;
	}

	protected function __SepardeAdvanceCascadeCatsDescription($data,$CascadeGroupId,$CascadeId){

		if(Configure::check('CascadeLoadModules') === false) return $data;
		if(Configure::read('CascadeLoadModules') === false) return $data;

		$locale = $this->_controller->Lang->Discription();
		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array('conditions' => array('Cascade.id' => $CascadeId));
		$Cascade = $this->_controller->Cascade->find('first',$options);


		$BreadcrumpList = array_reverse($this->_controller->Navigation->CascadeGetParentList($CascadeId,array()));
		$BreadcrumpList = Hash::extract($BreadcrumpList, '{n}.Cascade.discription');

		$data = $this->__CreateBreadCrumpNavi($data);

		$BreadcrumpList[] = __('unassigned',true);
		$Breadcrump = implode(' > ',$BreadcrumpList);
		$data['CascadeGroupTitle'] = $Breadcrump;

		$options = array('conditions' => array('CascadeGroup.id' => $CascadeGroupId));
		$CascadeGroup = $this->_controller->CascadeGroup->find('first',$options);

		if(count($CascadeGroup) > 0) $BreadcrumpList[] = $CascadeGroup['CascadeGroup'][$locale];
		else $BreadcrumpList[] = __('unassigned',true);

		$Breadcrump = implode(' > ',$BreadcrumpList);
		$data['CascadeGroupTitle'] = $Breadcrump;

		return $data;

	}

	public function CreateAdvanceOrders($data){

		if($data != null) return $data;
		if(is_array($data)) return $data;
		if($this->_controller->request->data['scheme_start'] == 0) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];
		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$this->_controller->Order->recursive = -1;

		$options = array('conditions' => array('Cascade.id' => $CascadeId));
		$Cascade = $this->_controller->Cascade->find('first',$options);

		if(count($Cascade) == 0) return $data;

		$options = array('conditions' => array('Order.cascade_id' => $CascadeId));
		$Order = $this->_controller->Order->find('first',$options);

		if(count($Order) == 0) return $data;

		$options = array('conditions' => array('Cascade.id' => $Cascade['Cascade']['parent']));
		$ParentCascade = $this->_controller->Cascade->find('first',$options);

		if(count($ParentCascade) == 0) return $data;

		$options = array('conditions' => array('Cascade.parent' => $ParentCascade['Cascade']['id']));
		$ChildrenCascade = $this->_controller->Cascade->find('all',$options);

		if(count($ChildrenCascade) == 0) return $data;

		$data['Scheme']['discription'] = $ParentCascade['Cascade']['discription'];

		foreach ($ChildrenCascade as $key => $value) {

			$data['Scheme']['children'][$value['Cascade']['id']]['discription'] = $value['Cascade']['discription'];
			$data['Scheme']['children'][$value['Cascade']['id']]['children'] = array();
			$data['Scheme']['children'][$value['Cascade']['id']]['status'] = array();

			if($value['Cascade']['id'] == $CascadeId){
				$Parents = $this->_controller->Navigation->CascadeGetParentList($CascadeId,array());

				$Parents = array_reverse(Hash::extract($Parents, '{n}.Cascade.id'));

				foreach ($Parents as $_key => $_value) {

					$options = array('conditions' =>
						array(
							'AdvancesCascade.advance_id' => $AdvanceId,
							'AdvancesCascade.cascade_id' => $_value,
						)
					);

					$AdvancesCascade = $this->_controller->AdvancesCascade->find('first',$options);

					if(count($AdvancesCascade) == 0){

						$Insert = $this->__GetParentTimePeriod($_value);

						$this->_controller->AdvancesCascade->create();
						$this->_controller->AdvancesCascade->save($Insert);
					}
				}
			}
		}

		$options = array('conditions' =>
			array(
				'AdvancesCascade.advance_id' => $AdvanceId,
				'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first',$options);

		$options = array('conditions' =>
			array(
				'AdvancesOrder.advance_id' => $AdvanceId,
				'AdvancesOrder.cascade_id' => $CascadeId,
				'AdvancesOrder.order_id' => $Order['Order']['id']
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first',$options);

		if(count($AdvancesOrder) == 0){

			$Insert['advance_id'] = $AdvanceId;
			$Insert['cascade_id'] = $CascadeId;
			$Insert['order_id'] = $Order['Order']['id'];
			$Insert['start'] = $AdvancesCascade['AdvancesCascade']['start'];
			$Insert['end'] = $AdvancesCascade['AdvancesCascade']['end'];

			$this->_controller->AdvancesOrder->create();
			$this->_controller->AdvancesOrder->save($Insert);

		}

		$data = $this->__GetFirstAdvancesOrder($data,$Order,$Cascade,$ParentCascade);

		return $data;
	}

	protected function __GetFirstAdvancesOrder($data,$Order,$Cascade,$ParentCascade){

		if(isset($data['Scheme']['AdvancesOrder'])) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];
		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$locale = $this->_controller->Lang->Discription();

		$options = array('conditions' =>
			array(
				'AdvancesOrder.advance_id' => $AdvanceId,
				'AdvancesOrder.cascade_id' => $CascadeId,
				'AdvancesOrder.order_id' => $Order['Order']['id']
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first',$options);

		$options = array('conditions'=>array('CascadegroupsCascade.cascade_id' => $CascadeId));
		$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('first',$options);

		$AdvancesOrder['AdvancesOrder']['topproject_id'] = $Order['Order']['topproject_id'];
		$AdvancesOrder['AdvancesOrder']['name'] = $Cascade['Cascade']['discription'];
		$AdvancesOrder['AdvancesOrder']['cascade_cat_parent_name'] = $ParentCascade['Cascade']['discription'];
		$AdvancesOrder['AdvancesOrder']['cascade_cat_parent_id'] = $ParentCascade['Cascade']['id'];

		if(count($CascadegroupsCascade) == 0){

			$AdvancesOrder['AdvancesOrder']['cascade_cat'] = __('unassigned',true);
			$AdvancesOrder['AdvancesOrder']['cascade_cat_id'] = 0;

		} else {

			$CascadeGroup = $this->_controller->CascadeGroup->find('first',array('conditions' => array('CascadeGroup.id' => $CascadegroupsCascade['CascadegroupsCascade']['cascade_group_id'])));

			$AdvancesOrder['AdvancesOrder']['cascade_cat'] = $CascadeGroup['CascadeGroup'][$locale];
			$AdvancesOrder['AdvancesOrder']['cascade_cat_id'] = $CascadegroupsCascade['CascadegroupsCascade']['cascade_group_id'];

		}

		$AdvancesOrder['AdvancesDataTypes'] = array();
		$AdvancesOrder['AdvancesData'] = array();

		$data['Scheme']['AdvancesOrder'][0] = $AdvancesOrder;

		return $data;

	}

	protected function __GetParentTimePeriod($id){

		$CascadeId = $this->_controller->request->data['scheme_start'];
		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$Insert['advance_id'] = $AdvanceId;
		$Insert['cascade_id'] = $id;

		$options = array('fields' => array('parent'), 'conditions' => array('Cascade.id' => $id));
		$ParentCascade = $this->_controller->Cascade->find('list',$options);

		$options = array('conditions' =>
			array(
				'AdvancesCascade.advance_id' => $AdvanceId,
				'AdvancesCascade.cascade_id' => $ParentCascade,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first',$options);

		if(count($AdvancesCascade) == 0) return $Insert;

		$Insert['start'] = $AdvancesCascade['AdvancesCascade']['start'];
		$Insert['end'] = $AdvancesCascade['AdvancesCascade']['start'];

		return $Insert;

	}

	public function SepardeAdvanceCascadeCats($data){

		if(isset($this->_controller->request->data['cascade_group_id'])) return $data;
		if(!isset($this->_controller->request->data['scheme_start'])) return $data;
		if($this->_controller->request->data['scheme_start'] == 0) return $data;

		if(!isset($data['Scheme'])) return $data;
		if(!isset($data['Scheme']['children'])) return $data;

		if(Configure::check('CascadeLoadModules') === false) return $data;
		if(Configure::read('CascadeLoadModules') === false) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$CascadeGroup = $this->_controller->CascadeGroup->find('list',array('fields' => array('id','deu','eng','xml_name','model')));
		$CascadeGroup[0] = __('unassigned',true);

		asort($CascadeGroup);
		$Categories = $CascadeGroup;

		foreach ($CascadeGroup as $key => $value) $CascadeGroup[$key] = array();

		$options = array('conditions'=>array('Cascade.parent'=>$CascadeId,));
		$Cascade = $this->_controller->Cascade->find('list',$options);

		foreach ($Categories as $key => $value) {

			$options = array('conditions'=>array('CascadegroupsCascade.cascade_group_id'=>$key,'CascadegroupsCascade.cascade_id'=>$Cascade));
			$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('all',$options);

			if(count($CascadegroupsCascade) == 0) continue;

			$CascadegroupsCascade = Hash::extract($CascadegroupsCascade, '{n}.CascadegroupsCascade.cascade_id');

			$CascadeGroup[$key]['Description'] = $value;
			$CascadeGroup[$key]['Id'] = $key;
			$CascadeGroup[$key]['Cascades'] = $CascadegroupsCascade;

		}

		foreach ($CascadeGroup as $key => $value) {

			if(count($value) == 0) unset($CascadeGroup[$key]);

		}

		if(count($CascadeGroup) > 0) $data['Scheme']['CascadeGroup'] = $CascadeGroup;

		return $data;
	}

	public function CheckForAdvanceOrder(){

		if(!isset($this->_controller->request->data['scheme_start'])) return;
		if(empty($this->_controller->request->data['scheme_start'])) return;

		$CascadeId = $this->_controller->request->data['scheme_start'];
		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$Children = $this->_controller->Navigation->CascadeGetAllChildren($CascadeId,array());

		if(count($Children) > 0) return;

		$this->_controller->Order->recursive = -1;

		$options = array('conditions' =>
			array(
				'Order.cascade_id' => $CascadeId
			)
		);

		$Order = $this->_controller->Order->find('first',$options);

		if(count($Order) == 0) return;

		$OrderId = $Order['Order']['id'];

		if($Order['Order']['cascade_id'] != $CascadeId) return;

		$options = array('conditions' =>
			array(
				'AdvancesOrder.advance_id' => $AdvanceId,
				'AdvancesOrder.cascade_id' => $CascadeId
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first',$options);

		if(count($AdvancesOrder) > 0) return;

		$options = array('conditions' =>
			array(
				'AdvancesOrder.advance_id' => $AdvanceId,
				'AdvancesOrder.cascade_id' => $CascadeId,
				'AdvancesOrder.order_id' => $OrderId,
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first',$options);

		if(count($AdvancesOrder) > 0) return;

		$Parent = $this->_controller->Navigation->CascadeGetParentId($CascadeId);

		if($Parent == 0) return;

		$options = array('conditions' =>
			array(
				'AdvancesCascade.cascade_id' => $Parent,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first',$options);

		if(count($AdvancesCascade) == 0) return;

		$Insert['advance_id'] = $AdvanceId;
		$Insert['cascade_id'] = $CascadeId;
		$Insert['start'] = $AdvancesCascade['AdvancesCascade']['start'];
		$Insert['end'] = $AdvancesCascade['AdvancesCascade']['end'];

		$this->_controller->AdvancesCascade->create();
		$this->_controller->AdvancesCascade->save($Insert);

		$Insert['order_id'] = $OrderId;

		$this->_controller->AdvancesOrder->create();
		$this->_controller->AdvancesOrder->save($Insert);

	}

	public function AssignAllAdvanceOrders($data){

		$LastInLine = $this->LastInLineCascade();

		if(!isset($data['Scheme'])) return $data;
		if($LastInLine === true) return $data;

		$CascadeId = key($data['Scheme']);

		$data['Scheme'] = $this->__AssignAllAdvanceOrders($data['Scheme']);
		$data['Scheme'] = $data['Scheme'][key($data['Scheme'])];
		if(isset($data['Scheme']['status'])) $data['Status'] = $data['Scheme']['status'];

		$data['Scheme']['id'] = $CascadeId;

		$data['Status']['all_advance_methods']['result']['count'] = 0;
		$data['Status']['all_advance_methods']['result']['open'] = 0;
		$data['Status']['all_advance_methods']['result']['okay'] = 0;
		$data['Status']['all_advance_methods']['result']['error'] = 0;

		$advance_rep = 0;
		$advance_all = 0;

		$AdvanceType = $this->_controller->advance_type;

		$output = array();
		$output['Status']['all_advance_methods']['result']['count'] = 0;
		$output['Status']['all_advance_methods']['result']['open'] = 0;
		$output['Status']['all_advance_methods']['result']['okay'] = 0;
		$output['Status']['all_advance_methods']['result']['error'] = 0;

		$options = array(
			'conditions' => array(
				'AdvancesCascade.advance_id' => $data['Advance']['id'],
				'AdvancesCascade.cascade_id' => $CascadeId,
				'AdvancesCascade.deleted' => 0,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);
		$AdvancesCascadeDate = $this->__CheckCascadeDate(array(),$AdvancesCascade);

		if(count($AdvancesCascade) > 0 && count($AdvancesCascadeDate) > 0) $data['AdvancesCascade'] = array_merge($AdvancesCascade['AdvancesCascade'],$AdvancesCascadeDate['AdvancesCascade']);

		foreach ($AdvanceType as $key => $value) {

			if(!isset($data['Status'][$value])) continue;


			$output['Status'][$value]['group_advance'] = 'group_advance_' . $value;
			$output['Status'][$value]['advance_count'] = 'advance_count_' . $value;
			$output['Status'][$value]['advance_open'] = 'advance_open_' . $value;
			$output['Status'][$value]['advance_okay'] = 'advance_okay_' . $value;
			$output['Status'][$value]['advance_error'] = 'advance_error_' . $value;
			$output['Status'][$value]['advance_line'] = 'advance_line_' . $value;

			$advance_rep = 0;
			$advance_all = 0;

			if($data['Status'][$value]['count'] > 0){
				$advance_rep = $this->RoundValue(100 * $data['Status'][$value]['error'] / $data['Status'][$value]['count']);
				$advance_all = $this->RoundValue(100 * ($data['Status'][$value]['okay'] + $data['Status'][$value]['error'] ) / $data['Status'][$value]['count']);
			}

			$output['Status'][$value]['result']['count'] = $data['Status'][$value]['count'];
			$output['Status'][$value]['result']['open'] = $data['Status'][$value]['open'];
			$output['Status'][$value]['result']['okay'] = $data['Status'][$value]['okay'];
			$output['Status'][$value]['result']['error'] = $data['Status'][$value]['error'];

			$output['Status'][$value]['result']['advance_all_percent'] = $advance_all;
			$output['Status'][$value]['result']['advance_rep_percent'] = $advance_rep;
			$output['Status'][$value]['result']['advance_all_deci'] = $advance_all / 100;
			$output['Status'][$value]['result']['advance_rep_deci'] = $advance_rep / 100;

			$output['Status'][$value]['advance_line_color'] = '#038000';

			if($data['AdvancesCascade']['status']['end'] == 1) $output['Status'][$value]['advance_line_color'] = '#ff7e00';
			if($data['AdvancesCascade']['status']['end_delay'] == 1) $output['Status'][$value]['advance_line_color'] = '#ff0000';
			if($output['Status'][$value]['result']['advance_all_percent'] == '100' && $output['Status'][$value]['result']['error'] == 0) $output['Status'][$value]['advance_line_color'] = '#038000';
			if($output['Status'][$value]['result']['error'] > 0) $output['Status'][$value]['advance_line_color'] = '#ff0000';

			$output['Status']['all_advance_methods']['result']['count'] += $output['Status'][$value]['result']['count'];
			$output['Status']['all_advance_methods']['result']['open'] += $output['Status'][$value]['result']['open'];
			$output['Status']['all_advance_methods']['result']['okay'] += $output['Status'][$value]['result']['okay'];
			$output['Status']['all_advance_methods']['result']['error'] += $output['Status'][$value]['result']['error'];

		}

		$advance_rep = 0;
		$advance_all = 0;

		if($output['Status']['all_advance_methods']['result']['count'] > 0){
			$advance_rep = $this->RoundValue(100 * $output['Status']['all_advance_methods']['result']['error'] / $output['Status']['all_advance_methods']['result']['count']);
			$advance_all = $this->RoundValue(100 * ($output['Status']['all_advance_methods']['result']['okay'] + $output['Status']['all_advance_methods']['result']['error'] ) / $output['Status']['all_advance_methods']['result']['count']);
		}

		$output['Status']['all_advance_methods']['result']['advance_all_percent'] = $advance_all;
		$output['Status']['all_advance_methods']['result']['advance_rep_percent'] = $advance_rep;
		$output['Status']['all_advance_methods']['result']['advance_all_deci'] = $advance_all / 100;
		$output['Status']['all_advance_methods']['result']['advance_rep_deci'] = $advance_rep / 100;

		$output['Status']['all_advance_methods']['advance_line_color'] = '#3c5bbe';

		$options = array(
			'conditions' => array(
				'AdvancesCascade.advance_id' => $data['Advance']['id'],
				'AdvancesCascade.cascade_id' => $this->_controller->request->data['scheme_start'],
			)
		);

		if($AdvancesCascadeDate['AdvancesCascade']['status']['end'] == 1) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff7e00';
		if($AdvancesCascadeDate['AdvancesCascade']['status']['end_delay'] == 1) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff0000';
		if($output['Status']['all_advance_methods']['result']['advance_all_deci'] == '1') $output['Status']['all_advance_methods']['advance_line_color'] = '#038000';
		if($output['Status']['all_advance_methods']['result']['advance_rep_deci'] > 0) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff0000';

		$data['Status'] = $output['Status'];

		if(isset($this->_controller->request->data['cascade_group_id']) && $this->_controller->request->data['cascade_group_id'] > 0){
			foreach ($data['Scheme']['children'] as $key => $value) {

				$children = $this->__AssignAllAdvanceOrders(array($key => array('discription' => $value['discription'],'children' => array())));

				$Complete = 0;

				if(!isset($children[$key]['status'])) continue;

				foreach ($children[$key]['status'] as $_key => $_value) {
					if($_value['complete'] == 1) ++$Complete;
				}

				if($Complete == count($children[$key]['status'])){
						$children[$key]['complete'] = 1;
				} else {
					$children[$key]['complete'] = 0;
				}
				$data['Scheme']['children'][$key] = $children[$key];
			}
		}

		$data = $this->__AssignAdvancesCascadesDates($data);

		return $data;
	}

	protected function __AssignAdvancesCascadesDates($data){

		if(!isset($data['Scheme']['children'])) return $data;

		$AdvanceId = $data['Advance']['id'];

		foreach($data['Scheme']['children'] as $key => $value){

			$options = array(
				'conditions' => array(
					'AdvancesCascade.advance_id' => $AdvanceId,
					'AdvancesCascade.cascade_id' => $key,
				)
			);

			$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

			$CheckCascadeDate = $this->__CheckCascadeDate(array(),$AdvancesCascade);

			if(isset($AdvancesCascade['AdvancesCascade']['start'])) $CheckCascadeDate['AdvancesCascade']['start'] = $AdvancesCascade['AdvancesCascade']['start'];
			if(isset($AdvancesCascade['AdvancesCascade']['end'])) $CheckCascadeDate['AdvancesCascade']['end'] = $AdvancesCascade['AdvancesCascade']['end'] ;
			if(isset($CheckCascadeDate['AdvancesCascade'])) $data['Scheme']['children'][$key]['schedule'] = $CheckCascadeDate['AdvancesCascade'];

			$data['Scheme']['children'][$key]['status']['all_advance_methods']['count'] = 0;
			$data['Scheme']['children'][$key]['status']['all_advance_methods']['open'] = 0;
			$data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] = 0;
			$data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] = 0;

			foreach ($data['Scheme']['children'][$key]['status'] as $_key => $_value) {

				if($_key == 'all_advance_methods') continue;

				$data['Scheme']['children'][$key]['status']['all_advance_methods']['count'] += $_value['count'];
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['open'] += $_value['open'];
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] += $_value['okay'];
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] += $_value['error'];


			}

			if($data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] + $data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] > 0){
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_all_percent'] = 100 * ($data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] + $data['Scheme']['children'][$key]['status']['all_advance_methods']['error']) / $data['Scheme']['children'][$key]['status']['all_advance_methods']['count'];
			} else {
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_all_percent'] = 0;
			}

			$data['Scheme']['children'][$key]['schedule']['status_color'] = '#636363';
			$data['Scheme']['children'][$key]['schedule']['status_text'] = __('future',true);

			if(isset($data['Scheme']['children'][$key]['schedule']['status'])){

				if($data['Scheme']['children'][$key]['schedule']['status']['start'] == 0){
					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#636363';
					$data['Scheme']['children'][$key]['schedule']['status_text'] = __('not started',true);
					$data['Scheme']['children'][$key]['schedule']['status_class'] = '';
			}

				if($data['Scheme']['children'][$key]['schedule']['status']['start'] == 1){
					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#3c5bbe';
//					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#038000';
					$data['Scheme']['children'][$key]['schedule']['status_text'] = __('plan',true);
					$data['Scheme']['children'][$key]['schedule']['status_class'] = 'intime';
			}

				if($data['Scheme']['children'][$key]['schedule']['status']['end'] == 1){
					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#ff7e00';
					$data['Scheme']['children'][$key]['schedule']['status_text'] = __('delayed',true);
					$data['Scheme']['children'][$key]['schedule']['status_class'] = 'delay';
			}

				if($data['Scheme']['children'][$key]['schedule']['status']['end_delay'] == 1){
					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#ff0000';
					$data['Scheme']['children'][$key]['schedule']['status_text'] = __('critical',true);
					$data['Scheme']['children'][$key]['schedule']['status_class'] = 'tolate';
			}

				if($data['Scheme']['children'][$key]['schedule']['interval_percent'] == '100'){
					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#038000';
					$data['Scheme']['children'][$key]['schedule']['status_text'] = __('completed',true);
					$data['Scheme']['children'][$key]['schedule']['status_class'] = 'okay';
			}

				if($data['Scheme']['children'][$key]['schedule']['interval_percent'] == '100' && $data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] == 1){
					$data['Scheme']['children'][$key]['schedule']['status_color'] = '#ff0000';
					$data['Scheme']['children'][$key]['schedule']['status_text'] = __('complete with repairs',true);
					$data['Scheme']['children'][$key]['schedule']['status_class'] = 'tolate';
			}

			}
		}

		return $data;
	}

	protected function __AssignAllAdvanceOrders($data){

		$CascadeID = key($data);
		$AdvancesType = $this->_controller->advance_type;

		$CascadeIDs = $this->_controller->Navigation->CascadeGetAllChildren($CascadeID,array());

		if(empty($CascadeIDs)) $CascadeIDs = array($CascadeID);

		$CascadeIDs = $this->__RemoveDeletedElements($CascadeIDs,2);

		if(isset($this->_controller->request->data['cascade_group_id']) && $this->_controller->request->data['cascade_group_id'] > 0){

			$CascadeGroupId = $this->_controller->request->data['cascade_group_id'];

			$options = array();
			$options = array('conditions'=>array('CascadegroupsCascade.cascade_group_id' => $CascadeGroupId,'CascadegroupsCascade.cascade_id' => $CascadeIDs));
			$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('all',$options);
			$CascadegroupsCascade = Hash::extract($CascadegroupsCascade, '{n}.CascadegroupsCascade.cascade_id');

			$CascadeIDs = array_intersect($CascadeIDs, $CascadegroupsCascade);
		}

		$options = array();
		$options = array(
			'conditions' => array(
				'Order.deleted' => 0,
				'Order.cascade_id' => $CascadeIDs,
			),
			'fields' => array('id')
		);

		$OrderIds = $this->_controller->Order->find('list',$options);

		if(count($OrderIds) == 0) return $data;
		foreach ($AdvancesType as $key => $value) {

			$options = array(
				'conditions' => array(
					'AdvancesDataDependency.order_id' => $OrderIds,
					'AdvancesDataDependency.advances_type_id' => $key,
					'AdvancesDataDependency.deleted' => 0,
				)
			);

			$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

			if(count($AdvancesDataDependency) == 0) continue;

			$Status = Hash::extract($AdvancesDataDependency, '{n}.AdvancesDataDependency');
			$StatusOpen = Hash::extract($Status, '{n}[value=0].id');
			$StatusOkay = Hash::extract($Status, '{n}[value=1].id');
			$StatusError = Hash::extract($Status, '{n}[value=2].id');

			$data[$CascadeID]['status'][$value] = array();
			$data[$CascadeID]['status'][$value]['count'] = count($Status);
			$data[$CascadeID]['status'][$value]['open'] = count($StatusOpen);
			$data[$CascadeID]['status'][$value]['okay'] = count($StatusOkay);
			$data[$CascadeID]['status'][$value]['error'] = count($StatusError);
			$data[$CascadeID]['status'][$value]['complete'] = 0;

			if(count($Status) == count($StatusOkay)) $data[$CascadeID]['status'][$value]['complete'] = 1;

		}

		return $data;
	}

	public function AssignAllChildCascades($data){

		return $data;

	}

	public function UpdateCascadeStatusSummary($data){

		if(!isset($data['Scheme'])) return $data;
		if(!isset($data['Scheme']['children'])) return $data;
		if(count($data['Scheme']['children']) == 0) return $data;

		$AdvanceId = $data['AdvancesCascade']['advance_id'];

		foreach ($data['Scheme']['children'] as $key => $value) {

			if(!isset($value['Status'])) continue;

			$options = array(
				'conditions' => array(
					'AdvancesCascade.advance_id' => $AdvanceId,
					'AdvancesCascade.cascade_id' => $key,
				)
			);

			$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

			$AdvancesCascade = $this->__CheckCascadeDate(array(),$AdvancesCascade);

			foreach ($value['Status'] as $_key => $_value) {

				$data['Scheme']['children'][$key]['status']['all_advance_methods']['count'] += $_value['result']['count'];
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['open'] += $_value['result']['open'];
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] += $_value['result']['okay'];
				$data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] += $_value['result']['error'];

			}

			if($data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] + $data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] > 0) $data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_all_percent'] = $this->RoundValue(100 * ($data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] + $data['Scheme']['children'][$key]['status']['all_advance_methods']['error']) / $data['Scheme']['children'][$key]['status']['all_advance_methods']['count']);
			else $data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_all_percent'] = 0;

			if($data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] > 0) $data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_rep_percent'] = $this->RoundValue(100 * $data['Scheme']['children'][$key]['status']['all_advance_methods']['error'] / $data['Scheme']['children'][$key]['status']['all_advance_methods']['count']);
			else $data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_rep_percent'] = 0;

			$data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_all_deci'] = $data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_all_percent'] / 100;
			$data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_rep_deci'] = $data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_rep_percent'] / 100;

			$data['Scheme']['children'][$key]['status']['advance_line_color'] = '#636363';

			if($AdvancesCascade['AdvancesCascade']['status']['start'] == 1) $data['Scheme']['children'][$key]['status']['advance_line_color'] = '#3c5bbe';
			if($AdvancesCascade['AdvancesCascade']['status']['end'] == 1) $data['Scheme']['children'][$key]['status']['advance_line_color'] = '#ff7e00';
			if($AdvancesCascade['AdvancesCascade']['status']['end_delay'] == 1) $data['Scheme']['children'][$key]['status']['advance_line_color'] = '#ff0000';
			if($data['Scheme']['children'][$key]['status']['all_advance_methods']['okay'] == $data['Scheme']['children'][$key]['status']['all_advance_methods']['count']) $data['Scheme']['children'][$key]['status']['advance_line_color'] = '#038000';
			if($data['Scheme']['children'][$key]['status']['all_advance_methods']['advance_rep_percent'] > 0) $data['Scheme']['children'][$key]['status']['advance_line_color'] = '#ff0000';
		}

		$advance_all = array();
		$advance_all['count'] = 0;
		$advance_all['open'] = 0;
		$advance_all['okay'] = 0;
		$advance_all['error'] = 0;

		foreach($data['Scheme']['status'] as $key => $value) {

			$advance_all['count'] += $value['count'];
			$advance_all['open'] += $value['open'];
			$advance_all['okay'] += $value['okay'];
			$advance_all['error'] += $value['error'];

		}

		if($advance_all['okay'] + $advance_all['error'] > 0) $advance_all['advance_all_percent'] = $this->RoundValue(100 * ($advance_all['okay'] + $advance_all['error']) / $advance_all['count']);
		else $advance_all['advance_all_percent'] = 0;

		if($advance_all['error'] > 0) $advance_all['advance_rep_percent'] = $this->RoundValue(100 * $advance_all['error'] / $advance_all['count']);
		else $advance_all['advance_rep_percent'] = 0;

		$advance_all['advance_all_deci'] = $advance_all['advance_all_percent'] / 100;
		$advance_all['advance_rep_deci'] = $advance_all['advance_rep_percent'] / 100;

		$data['Scheme']['status']['all_advance_methods'] = $advance_all;

		return $data;

	}

	public function AssignAdvanceOrders($data){


		foreach ($data['Scheme'] as $key => $value) {
			$value = $this->__AssignAdvanceOrders($key,$value);
			$value['children'] = $this->__AssignAdvanceOrdersRecursive($value['children']);
			unset($data['Scheme'][$key]);
			$data['Scheme'][$key] = $value;

		}

		return $data;
	}

	protected function __AssignAdvanceOrdersRecursive($data){

		if(count($data) == 0) return $data;

		$this->_controller->Order->recursive = -1;

		$AdvancesType = $this->_controller->advance_type;

		foreach ($data as $key => $value) {

			$CascadeIDs = $this->_controller->Navigation->CascadeGetAllChildren($key,array());

			if(empty($CascadeIDs)) $CascadeIDs = $key;

			$OrderIds = $this->_controller->Order->find('list',array('fields' => array('id'),'conditions'=>array('Order.deleted' => 0,'Order.cascade_id' => $CascadeIDs)));

			if(count($OrderIds) == 0) continue;

			if(count($value['children']) == 0) $data[$key]['status'] = array();
			foreach ($AdvancesType as $_key => $_value) {

				$options = array(
					'conditions' => array(
						'AdvancesDataDependency.order_id' => $OrderIds,
						'AdvancesDataDependency.advances_type_id' => $_key,
						'AdvancesDataDependency.deleted' => 0,
					)
				);

				$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

				if(count($AdvancesDataDependency) == 0) continue;

				$Status = Hash::extract($AdvancesDataDependency, '{n}.AdvancesDataDependency');
				$StatusOpen = Hash::extract($Status, '{n}[value=0].id');
				$StatusOkay = Hash::extract($Status, '{n}[value=1].id');
				$StatusError = Hash::extract($Status, '{n}[value=2].id');

				$value['status'][$_value] = array();
				$value['status'][$_value]['open'] = count($StatusOpen);
				$value['status'][$_value]['okay'] = count($StatusOkay);
				$value['status'][$_value]['error'] = count($StatusError);

				$value['children'] = $this->__AssignAdvanceOrdersRecursive($value['children']);

				$data[$key] = $value;

			}
		}

		return $data;
	}

	protected function __AssignAdvanceOrders($key,$value){

		$AdvancesType = $this->_controller->advance_type;

		$CascadeIDs = $this->_controller->Navigation->CascadeGetAllChildren($key,array());

		if(empty($CascadeIDs)) $CascadeIDs = $key;

		$OrderIds = $this->_controller->Order->find('list',array('fields' => array('id'),'conditions'=>array('Order.deleted' => 0,'Order.cascade_id' => $CascadeIDs)));

		if(count($OrderIds) == 0) return $value;

		foreach($AdvancesType as $_key => $_value){

			$options = array(
				'conditions' => array(
					'AdvancesDataDependency.order_id' => $OrderIds,
					'AdvancesDataDependency.advances_type_id' => $_key,
					'AdvancesDataDependency.deleted' => 0,
				)
			);

			$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

			if(count($AdvancesDataDependency) == 0) continue;

			$Status = Hash::extract($AdvancesDataDependency, '{n}.AdvancesDataDependency');
			$StatusOpen = Hash::extract($Status, '{n}[value=0].id');
			$StatusOkay = Hash::extract($Status, '{n}[value=1].id');
			$StatusError = Hash::extract($Status, '{n}[value=2].id');

			$value['status'][$_value] = array();
			$value['status'][$_value]['open'] = count($StatusOpen);
			$value['status'][$_value]['okay'] = count($StatusOkay);
			$value['status'][$_value]['error'] = count($StatusError);

		}

		return $value;
	}

	public function CollectOrderSheme($data,$CascadeList,$Scheme){

		$Output = array();

		$Output = $this->__FindAdvancesInScheme($data,$CascadeList,$Scheme);

		return $Output;
	}

	protected function __FindAdvancesInScheme($data,$CascadeList,$Scheme){

		$Output = $Scheme;

		if(count($CascadeList) == 0){
			$Scheme = $data['AdvancesDataTypes'];
			return $Scheme;
		}

		foreach ($CascadeList as $key => $value) {

			unset($CascadeList[$key]);

			foreach($Scheme as $_key => $_value){
				if($key != $_key) unset($Output[$_key]);
			}

			unset($Output[$key]['children']);

			if(count($CascadeList) > 0) $Output[$key]['children'] = $this->__FindAdvancesInScheme($data,$CascadeList,$Scheme[$key]['children']);
			if(count($CascadeList) == 0) $Output[$key]['AdvancesDataTypes'] = $this->__FindAdvancesInScheme($data,$CascadeList,$Scheme[$key]['children']);

			break;
		}
		return $Output;
	}

	protected function __AddCascadeGroupOptions($data){

		if(!isset($this->_controller->request->data['cascade_group_id'])) return $data;
		if($data['conditions']['Cascade.parent'] != $this->_controller->request->data['scheme_start']) return $data;

		$CascadeGroupId = $this->_controller->request->data['cascade_group_id'];
		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array('conditions'=>array('Cascade.parent' => $CascadeId));
		$Cascade = $this->_controller->Cascade->find('list',$options);

		if($CascadeGroupId > 0){
			$options = array('conditions'=>array('CascadegroupsCascade.cascade_group_id' => $CascadeGroupId,'CascadegroupsCascade.cascade_id' => $Cascade));
			$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('all',$options);
			$CascadegroupsCascade = Hash::extract($CascadegroupsCascade, '{n}.CascadegroupsCascade.cascade_id');
		} else {
			$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('all');
			$CascadegroupsCascade = Hash::extract($CascadegroupsCascade, '{n}.CascadegroupsCascade.cascade_id');
			$CascadegroupsCascade = array_intersect($CascadegroupsCascade, $Cascade);
			$CascadegroupsCascade = array_diff($Cascade, $CascadegroupsCascade);
		}

		$data['conditions']['Cascade.id'] = $CascadegroupsCascade;

		return $data;
	}

	protected function __CascadeGetTree($id) {

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$options = array(
			'conditions' => array(
				'AdvancesCascadesUser.advance_id' => $AdvanceId,
				'AdvancesCascadesUser.cascade_id' => $id,
				'AdvancesCascadesUser.user_id' => $this->_controller->Auth->user('id')

			)
		);

		$AdvancesCascadesUser = $this->_controller->AdvancesCascadesUser->find('first',$options);

		if(count($AdvancesCascadesUser) == 0){

			$Output['discription'] = '';
			$Output['children'] = array();

			return array();

		}

		$Cascade = $this->_controller->Cascade->find('first',array('fields' => array('discription'),'conditions'=>array('Cascade.id'=>$id)));

		if(count($Cascade) == 0) return array();

		$Output['discription'] = $Cascade['Cascade']['discription'];
		$Output['children'] = array();

		$options = array();

		$options['fields'] = array('id');
		$options['conditions']['Cascade.parent'] = $id;

		$options = $this->__AddCascadeGroupOptions($options);
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',$options);

		foreach ($Cascade as $key => $value) {
			$Output['children'][$key] = $this->__CascadeGetTree($key,'recursive');
			if(count($Output['children'][$key]) == 0){
				unset($Output['children'][$key]);
			}
		}

		return $Output;
	}

	protected function __CascadeGetTreeList($Output) {

		$Cascade = $this->_controller->Cascade->find('list',array('conditions'=>array('Cascade.parent' => $Output)));

		if(count($Cascade) == 0) return $Output;

		$Output = array_merge($Output,$Cascade);
		$Output = array_unique($Output);

		foreach ($Output as $key => $value) {
			$Out = $this->__CascadeGetTreeListRecursive($value);
			$Output = array_merge($Output,$Out);
			$Output = array_unique($Output);
		}

		sort($Output);
		$Output = array_unique($Output);

		return $Output;
	}

	protected function __CascadeGetTreeListRecursive($Input) {

		$Cascade = $this->_controller->Cascade->find('list',array('conditions'=>array('Cascade.parent' => $Input)));

		if(count($Cascade) == 0) return $Cascade;

		foreach ($Cascade as $key => $value) {
			$Out = $this->__CascadeGetTreeListRecursive($value);
			$Cascade = array_merge($Cascade,$Out);
			$Cascade = array_unique($Cascade);
		}

		sort($Cascade);
		$Cascade = array_unique($Cascade);

		return $Cascade;
	}

	public function CurrentCascade($data){

		if(!isset($this->_controller->request->data['scheme_start'])) return $data;

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options['conditions']['Cascade.id'] = $CascadeId;
		$Cascade = $this->_controller->Cascade->find('first',$options);

		if(count($Cascade) == 0) return $data;

		$data['Cascade'] = $Cascade['Cascade'];

		return $data;

	}

	public function CascadeSchemaAll($data){

		$Output = array();

		if(!isset($data['Topproject']['selected'])) return $Output;
		if(count($data['Topproject']['selected']) == 0) return $Output;

		$options['conditions']['Cascade.level'] = 0;

		foreach ($data['Topproject']['selected'] as $key => $value) {

			if($value < 1) continue;

			$options['conditions']['Cascade.topproject_id'] = $value;
			$Cascade = $this->_controller->Cascade->find('first',$options);
			$Output[$Cascade['Cascade']['id']] = $this->__CascadeGetTree($Cascade['Cascade']['id']);

		}

		$data['Scheme'] = $Output;
		return $data;
	}

	public function CascadeSchemaSingle($data){

		$Output = array();

		if(!isset($data['Topproject']['selected'])) return $Output;
		if(count($data['Topproject']['selected']) == 0) return $Output;

		if(isset($this->_controller->request->data['scheme_start'])) $CascadeId = $this->_controller->request->data['scheme_start'];


		$options['conditions']['Cascade.id'] = $CascadeId;

		foreach ($data['Topproject']['selected'] as $key => $value) {

			if($value < 1) continue;

			$options['conditions']['Cascade.topproject_id'] = $value;
			$Cascade = $this->_controller->Cascade->find('first',$options);
			$Output[$Cascade['Cascade']['id']] = $this->__CascadeGetTree($Cascade['Cascade']['id']);

			$Output[$Cascade['Cascade']['id']]['children'] = $this->__RemoveDeletedElements($Output[$Cascade['Cascade']['id']]['children'],1);

		}

		$data['Scheme'] = $Output;
		return $data;
	}

	public function AdvanceStatus($data){

		$CurrentDate = CakeTime::format(time(),'%Y-%m-%d');
		foreach ($data['AdvancesData'] as $key => $value) {

			if($value['AdvancesData']['result'] > 0){
				switch($value['AdvancesData']['result']){
					case 1:
						$data['AdvancesData'][$key]['Status']['text'] = __('This step of advance is finished',true);
						$data['AdvancesData'][$key]['Status']['class'] = 'advance_okay';
						break;
						case 2:
							$data['AdvancesData'][$key]['Status']['text'] = __('This step of advance is finished with errors',true);
							$data['AdvancesData'][$key]['Status']['class'] = 'advance_error';
							break;
					}
				continue;
			}

			if($value['AdvancesData']['termin_end'] < $CurrentDate ){
				$data['AdvancesData'][$key]['Status']['text'] = __('The date of this advance is overdue',true);
				$data['AdvancesData'][$key]['Status']['class'] = 'icon_status_overdue';
				continue;
			}
			if($value['AdvancesData']['termin_start'] > $CurrentDate ){
				$data['AdvancesData'][$key]['Status']['text'] = __('The date of this advance is not yet due',true);
				$data['AdvancesData'][$key]['Status']['class'] = 'icon_status_notyetdue';
				continue;
			}
			if($value['AdvancesData']['termin_start'] >= $CurrentDate){
				$data['AdvancesData'][$key]['Status']['text'] = __('The date has been reached',true);
				$data['AdvancesData'][$key]['Status']['class'] = 'icon_status_now';
				continue;
			}
		}

		return $data;
	}

	public function EditAdvanceCascade($Advance) {

		if(empty($this->_controller->request->data)) return;
		if(empty($this->_controller->request->data['AdvancesCascade'])) return;

		$Advance = $this->AdvanceElements($Advance);

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];

		$data = $this->_controller->request->data['AdvancesCascade'];
		$update = $data;

		unset($update['AdvancesOrderAll']);

		$options = array(
			'conditions' => array(
				'AdvancesOrder.advance_id' => $data['advance_id'],
				'AdvancesOrder.cascade_id' => $data['cascade_id'],
			)
		);

		$Response = $this->__UpdateAdvanceCascade($data,$Advance);

		if($Response === false){
			$this->_controller->Flash->error(__('Value could not be saved. Please, try again.',true), array('key' => 'error'));
			return;
		}

		$Response = $this->__UpdateAdvanceOrder($data,$Advance);

		if($Response === false){
			$this->_controller->Flash->error(__('Value could not be saved. Please, try again.',true), array('key' => 'error'));
			return;
		}

		$Response = $this->__UpdateAdvanceUser($data);

		if($Response === false){
			$this->_controller->Flash->error(__('Value could not be saved. Please, try again.',true), array('key' => 'error'));
			return;
		}

		$Response = $this->__UpdateAdvanceUserSubordinate($data,$Advance);

		if($Response === false){
			$this->_controller->Flash->error(__('Value could not be saved. Please, try again.',true), array('key' => 'error'));
			return;
		}

		$Response = $this->__UpdateAdvanceCascadeSubordinate($data,$Advance);

		if($Response === false){
			$this->_controller->Flash->error(__('Value could not be saved. Please, try again.',true), array('key' => 'error'));
			return;
		}

		$Response = $this->__UpdateAdvanceDataSubordinate($data,$Advance);

		if($Response === false){
			$this->_controller->Flash->error(__('Value could not be saved. Please, try again.',true), array('key' => 'error'));
			return;
		}

		$this->_controller->Flash->success(__('The value has been saved',true), array('key' => 'success'));

		$JSONName['parameter']['cascade_id'] = $this->_controller->request->data['AdvancesCascade']['cascade_id'];
		$this->_controller->set('JSONName',$JSONName);

	}

	protected function __UpdateAdvanceOrder($data,$Advance) {

		if(count($data) == 0) return;

		unset($data['AdvancesUsers']);

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];
		$AdvanceId = $Advance['AdvancesCascade']['advance_id'];

		$options = array(
			'conditions' => array(
				'AdvancesOrder.advance_id' => $AdvanceId,
				'AdvancesOrder.cascade_id' => $CascadeId,
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

		if(count($AdvancesOrder) == 0) return true;

		$data['id'] = $AdvancesOrder['AdvancesOrder']['id'];

		if($this->_controller->AdvancesOrder->save($data)) return true;
		else return false;

	}

	protected function __UpdateAdvanceCascade($data,$Advance) {

		if(count($data) == 0) return;

		unset($data['AdvancesUsers']);

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];
		$AdvanceId = $Advance['AdvancesCascade']['advance_id'];

		$options = array(
			'conditions' => array(
				'AdvancesCascade.advance_id' => $AdvanceId,
				'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($AdvancesCascade) == 0) return false;

		$data['id'] = $AdvancesCascade['AdvancesCascade']['id'];

		if($this->_controller->AdvancesCascade->save($data)){

			$Response = $this->__UpdateAdvanceMaxSubordinateDate($data,$Advance,$AdvancesCascade);

			if($Response === false) return false;
			else return true;

		}
		else return false;

	}

	protected function __UpdateAdvanceUser($data) {


		$Delete['AdvancesCascadesUser.advance_id'] = $data['advance_id'];
		$Delete['AdvancesCascadesUser.cascade_id'] = $data['cascade_id'];

		$this->_controller->AdvancesCascadesUser->deleteAll($Delete, false);

		if(empty($data['AdvancesUsers'])){
			$Response = $this->__RemoveSubordinateAdvanceUser($data);
			return true;
		}

		$Save['advance_id'] = $data['advance_id'];
		$Save['cascade_id'] = $data['cascade_id'];


		foreach ($data['AdvancesUsers'] as $key => $value) {
			$Save['user_id'] = $value;
			$this->_controller->AdvancesCascadesUser->create();
			$this->_controller->AdvancesCascadesUser->save($Save);
		}

		$Response = $this->__RemoveSubordinateAdvanceUser($data);

		return true;
	}

	protected function __RemoveSubordinateAdvanceUser($data) {

		if(!isset($data['remove_user'])) return true;
		if(empty($data['remove_user'])) return true;

		$RemoveUsers = explode(',',$data['remove_user']);

		if(count($RemoveUsers) == 0) return true;

		$AdvanceId = $data['advance_id'];
		$CascadeId = $data['cascade_id'];

		$AllCascadeIds = $this->__CascadeGetTreeList(array($CascadeId => $CascadeId));

		if(count($AllCascadeIds) == 0) return true;

		$Delete['AdvancesCascadesUser.cascade_id'] = $AllCascadeIds;
		$Delete['AdvancesCascadesUser.user_id'] = $RemoveUsers;

		$this->_controller->AdvancesCascadesUser->deleteAll($Delete, false);

		return true;
	}

	protected function __UpdateAdvanceCascadeSubordinate($data,$Advance) {

		if($data['cascade_date'] == 0) return;

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];
		$AdvanceId = $Advance['AdvancesCascade']['advance_id'];

		$AllCascadeIds = $this->__CascadeGetTreeList(array($CascadeId => $CascadeId));

		if(count($AllCascadeIds) == 0) return true;

		foreach ($AllCascadeIds as $_key => $_value) {

			$options = array(
				'conditions' => array(
					'AdvancesCascade.advance_id' => $AdvanceId,
					'AdvancesCascade.cascade_id' => $_value,
					'AdvancesCascade.deleted' => 0,
				)
			);

			$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

			if(count($AdvancesCascade) == 0) continue;

			$Save['id'] = $AdvancesCascade['AdvancesCascade']['id'];
			$Save['start'] = $data['start'];
			$Save['end'] = $data['end'];

			$this->_controller->AdvancesCascade->save($Save);

			$Save = array();

			$options = array(
				'conditions' => array(
					'AdvancesOrder.advance_id' => $AdvanceId,
					'AdvancesOrder.cascade_id' => $_value,
					'AdvancesOrder.deleted' => 0,
				)
			);

			$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

			if(count($AdvancesOrder) == 0) continue;

			$Save['id'] = $AdvancesOrder['AdvancesOrder']['id'];
			$Save['start'] = $data['start'];
			$Save['end'] = $data['end'];

			$this->_controller->AdvancesOrder->save($Save);

			$Save = array();
		}

		return true;
	}

	protected function __UpdateAdvanceMaxSubordinateDate($data,$Advance,$AdvancesCascade) {

		if($data['start'] > $AdvancesCascade['AdvancesCascade']['start'] || $data['end'] < $AdvancesCascade['AdvancesCascade']['end'] ){
			$AdjustSubordinate = true;
		} else {
			$AdjustSubordinate = false;
		}

		if($AdjustSubordinate === false) return true;

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];
		$AdvanceId = $Advance['AdvancesCascade']['advance_id'];

		$AllCascadeIds = $this->__CascadeGetTreeList(array($CascadeId => $CascadeId));

		if(count($AllCascadeIds) == 0) return true;

		foreach ($AllCascadeIds as $_key => $_value) {

			$options = array(
				'conditions' => array(
					'AdvancesCascade.advance_id' => $AdvanceId,
					'AdvancesCascade.cascade_id' => $_value,
					'AdvancesCascade.deleted' => 0,
				)
			);

			$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

			if(count($AdvancesCascade) == 1){

				$Save['id'] = $AdvancesCascade['AdvancesCascade']['id'];
				$Save['start'] = $data['start'];
				$Save['end'] = $data['end'];

				$this->_controller->AdvancesCascade->save($Save);

				$Save = array();

			}

			$options = array(
				'conditions' => array(
					'AdvancesOrder.advance_id' => $AdvanceId,
					'AdvancesOrder.cascade_id' => $_value,
					'AdvancesOrder.deleted' => 0,
				)
			);

			$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

			if(count($AdvancesOrder) == 1){

				$Save['id'] = $AdvancesOrder['AdvancesOrder']['id'];
				$Save['start'] = $data['start'];
				$Save['end'] = $data['end'];

				$this->_controller->AdvancesOrder->save($Save);

				$Save = array();

			}

			$options = array(
				'conditions' => array(
					'AdvancesData.advance_id' => $AdvanceId,
					'AdvancesData.cascade_id' => $_value,
					'AdvancesData.deleted' => 0,
				)
			);

			$AdvancesData = $this->_controller->AdvancesData->find('all', $options);

			if(count($AdvancesData) > 0) {

				foreach($AdvancesData as $__key => $__data){

					$Save['id'] = $__data['AdvancesData']['id'];
					$Save['start'] = $data['start'];
					$Save['end'] = $data['end'];

					$this->_controller->AdvancesData->save($Save);

					$Save = array();

				}
			}
		}

		return true;
	}

	protected function __UpdateAdvanceDataSubordinate($data,$Advance) {

		if($data['cascade_date'] == 0) return;

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];
		$AdvanceId = $Advance['AdvancesCascade']['advance_id'];

		$AllCascadeIds = $this->__CascadeGetTreeList(array($CascadeId => $CascadeId));

		if(count($AllCascadeIds) == 0) return true;

		foreach ($AllCascadeIds as $_key => $_value) {

			$options = array(
				'conditions' => array(
					'AdvancesData.advance_id' => $AdvanceId,
					'AdvancesData.cascade_id' => $_value,
					'AdvancesData.deleted' => 0,
				)
			);

			$AdvancesData = $this->_controller->AdvancesData->find('all', $options);

			if(count($AdvancesData) == 0) continue;

			foreach($AdvancesData as $__key => $__data){

				$Save['id'] = $__data['AdvancesData']['id'];
				$Save['start'] = $data['start'];
				$Save['end'] = $data['end'];

				$this->_controller->AdvancesData->save($Save);

				$Save = array();

			}
		}

		return true;
	}

	protected function __UpdateAdvanceUserSubordinate($data,$Advance) {

		if($data['cascade_users'] == 0) return true;

		if(count($data['AdvancesUsers']) == 0) return;
		if($data['cascade_users'] == 0) return;

		$CascadeId = $Advance['AdvancesCascade']['cascade_id'];
		$AdvanceId = $Advance['AdvancesCascade']['advance_id'];

		$AllCascadeIds = $this->__CascadeGetTreeList(array($CascadeId => $CascadeId));

		if(count($AllCascadeIds) == 0) return true;

		$Delete['AdvancesCascadesUser.advance_id'] = $data['AdvancesUsers'];
		$Delete['AdvancesCascadesUser.cascade_id'] = $AllCascadeIds;

		$this->_controller->AdvancesCascadesUser->deleteAll($Delete, false);

		foreach ($data['AdvancesUsers'] as $key => $value) {

			foreach ($AllCascadeIds as $_key => $_value) {
				$Save['advance_id'] = $AdvanceId;
				$Save['user_id'] = $value;
				$Save['cascade_id'] = $_value;
				$this->_controller->AdvancesCascadesUser->create();
				$this->_controller->AdvancesCascadesUser->save($Save);
				$Save = array();
			}
		}

		return true;
	}

	protected function __DelAllAdvanceOrder($data,$AdvancesOrder) {

		$OrderIds = Hash::extract($AdvancesOrder, '{n}.AdvancesOrder.order_id');

		if(!empty($data['AdvancesOrderAll'])) return false;

		$options = array(
			'conditions' => array(
				'AdvancesOrder.advance_id' => $data['advance_id'],
				'AdvancesOrder.order_id' => $OrderIds,
			)
		);

		$AdvancesOrderDel = $this->_controller->AdvancesOrder->find('list', $options);

		foreach ($AdvancesOrderDel as $key => $value) {

			$Delete = array('id' => $value,'deleted' => 1);
			$this->_controller->AdvancesOrder->save($Delete);

		}

		return true;

	}

	protected function __DelAdvanceOrder($data,$AdvancesOrder) {

		$OrderIds = Hash::extract($AdvancesOrder, '{n}.AdvancesOrder.order_id');

		if(count($OrderIds) <= count($data['AdvancesOrderAll'])) return;

		$OrderDelId = array_diff($OrderIds,$data['AdvancesOrderAll']);

		foreach ($OrderDelId as $key => $value) {

			$options = array(
				'conditions' => array(
					'AdvancesOrder.advance_id' => $data['advance_id'],
					'AdvancesOrder.order_id' => $value,
				)
			);

			$AdvancesOrderDel = $this->_controller->AdvancesOrder->find('first', $options);

			if(count($AdvancesOrderDel) == 1){

				$Delete = array('id' => $AdvancesOrderDel['AdvancesOrder']['id'],'deleted' => 1);
				$this->_controller->AdvancesOrder->save($Delete);

			}
		}
	}

	public function GetAdvanceData(){

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];
		$CascadeId = $this->_controller->request->projectvars['VarsArray'][1];
		$OrderId = $this->_controller->request->projectvars['VarsArray'][2];

		$options =array('conditions' =>
			array(
				'Advance.id' => $AdvanceId,
				'Advance.deleted ' => 0
			)
		);

		$Advance = $this->_controller->Advance->find('first', $options);

		if(count($Advance) == 0) return array();

		$Advance = $this->_controller->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->_controller->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$options =array('conditions' =>
			array(
				'AdvancesOrder.advance_id' => $AdvanceId,
				'AdvancesOrder.cascade_id' => $CascadeId,
				'AdvancesOrder.order_id' => $OrderId,
				'AdvancesOrder.deleted ' => 0
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

		$AdvancesOrder['AdvancesOrder']['start_global'] = $Advance['Advance']['start'];
		$AdvancesOrder['AdvancesOrder']['end_global'] = $Advance['Advance']['end'];

		$Advance = array_merge($Advance,$AdvancesOrder);

		$options =array('conditions' =>
			array(
				'AdvancesData.advance_id' => $AdvanceId,
				'AdvancesData.cascade_id' => $CascadeId,
				'AdvancesData.order_id' => $OrderId,
				'AdvancesData.deleted ' => 0
			)
		);

		$AdvancesData = $this->_controller->AdvancesData->find('all', $options);

		$Advance['AdvancesData'] = $AdvancesData;

		$options =array('conditions' =>
			array(
				'Order.id' => $OrderId,
				'Order.deleted ' => 0
			)
		);

		$this->_controller->Order->recursive = -1;
		$Order = $this->_controller->Order->find('first', $options);

		$Advance = array_merge($Advance,$Order);

		$options = array('conditions'=>array('CascadegroupsCascade.cascade_id' => $CascadeId));
		$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('first',$options);

		if(count($CascadegroupsCascade) == 0) $Advance['CascadegroupsCascade'] = 0;
		else $Advance['CascadegroupsCascade'] = $CascadegroupsCascade['CascadegroupsCascade']['cascade_group_id'];

		$CascadeGroup = $this->_controller->CascadeGroup->find('list',array('fields' => array('id','deu','eng','xml_name','model')));
		$CascadeGroup[0] = __('unassigned',true);

		$Advance['CascadeGroup'] = $CascadeGroup;

		return $Advance;

	}

	public function UpdateAdvanceData(){

		$Advance = $this->GetAdvanceData();

		$DateTest = true;

		$date['Global']['start'] = strtotime($Advance['Advance']['start']);
		$date['Advance']['start'] = strtotime($this->_controller->request->data['AdvancesOrder']['start']);
		$date['Global']['end'] = strtotime($Advance['Advance']['end']);
		$date['Advance']['end'] = strtotime($this->_controller->request->data['AdvancesOrder']['end']);

		if($date['Advance']['start'] <= $date['Global']['start']) $DateTest = false;
		if($date['Advance']['end'] > $date['Global']['end']) $DateTest = false;

		if($DateTest === false){
			$this->_controller->Session->setFlash(__('The entry could not be saved. Please, try again.'));
			return;
		}


		if($this->_controller->AdvancesOrder->save($this->_controller->request->data)) $this->_controller->Session->setFlash(__('The entry has been saved'));
		else $this->_controller->Session->setFlash(__('The entry could not be saved. Please, try again.'));

		if(isset($this->_controller->request->data['AdvancesOrder']['equipment_type'])){

			$options = array('conditions'=>array('CascadegroupsCascade.cascade_id' => $Advance['AdvancesOrder']['cascade_id']));
			$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('first',$options);

			if(count($CascadegroupsCascade) == 0){

				$Insert['cascade_group_id'] = $this->_controller->request->data['AdvancesOrder']['equipment_type'];
				$Insert['cascade_id'] = $Advance['AdvancesOrder']['cascade_id'];

				$this->_controller->CascadegroupsCascade->create();
				$this->_controller->CascadegroupsCascade->save($Insert);

			} else {

				$this->_controller->CascadegroupsCascade->updateAll(
    			array('CascadegroupsCascade.cascade_group_id' => $this->_controller->request->data['AdvancesOrder']['equipment_type']),
    			array('CascadegroupsCascade.cascade_id' => $Advance['AdvancesOrder']['cascade_id'])
				);

			}
		}

		return;

	}

	public function DelAdvanceData($data){

		$Output = array();

		if(empty($this->_controller->request->data)) return;
		if(empty($this->_controller->request->data['AdvancesData'])) return;
		if(empty($this->_controller->request->data['AdvancesData']['id'])) return;

		$Id = $this->_controller->request->data['AdvancesData']['id'];

		$Delete = array('id' => $Id,'deleted' => 1);

		if($this->_controller->AdvancesData->save($Delete)){

			$this->_controller->AdvancesDataDependency->updateAll(
	    	array('AdvancesDataDependency.deleted' => 1),
	    	array('AdvancesDataDependency.advances_data_id' => $Id)
			);

			$this->_controller->Autorisierung->Logger($Id, $this->_controller->request->data['AdvancesData']);

			$Output['Status']['update'] = 'success';
			$Output['Status']['row'] = 'tr#advance_detail_' . $Id;
			$Output['Status']['message'] = __('The value has been deleted.',true);

		} else {

			$Output['Status']['row'] = 'tr#advance_detail_' . $Id;
			$Output['Status']['update'] = 'error';
			$Output['Status']['message'] = __('The value could not be deleted. Please, try again.',true);

		}

		return $Output;

	}

	public function AddAdvance(){

		if(empty($this->_controller->request->data)) return;
		if(empty($this->_controller->request->data['AdvancesDataDependency'])) return;
		if(!isset($this->_controller->request->data['AdvancesDataDependency']['AdvancesType'])) return;

		$id = $this->_controller->request->projectvars['VarsArray'][0];
		$testingcomp_id = $this->_controller->Auth->user('testingcomp_id');

		$data = $this->_controller->request->data;
		if(count($data['AdvancesDataDependency']) < 3) return;

		$Count = $data['AdvancesDataDependency']['count'];

		unset($data['AdvancesDataDependency']['count']);

		$options = array(
			'conditions' => array(
				'AdvancesOrder.advance_id' => $id,
				'AdvancesOrder.order_id' => $data['AdvancesDataDependency']['order_id'],
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

		if(count($AdvancesOrder) == 0) return;

		$options = array('conditions' => array(
			'AdvancesData.order_id' => $data['AdvancesDataDependency']['order_id'],
			'AdvancesData.type' => $data['AdvancesDataDependency']['AdvancesType']
			)
		);

		$AdvanceData = $this->_controller->AdvancesData->find('first',$options);

		if(count($AdvanceData) == 0){

			$Insert = array();
			$Insert['advance_id'] = $id;
			$Insert['order_id'] = $data['AdvancesDataDependency']['order_id'];
			$Insert['cascade_id'] = $data['AdvancesDataDependency']['cascade_id'];
			$Insert['type'] = $data['AdvancesDataDependency']['AdvancesType'];
			$Insert['start'] = $AdvancesOrder['AdvancesOrder']['start'];
			$Insert['end'] = $AdvancesOrder['AdvancesOrder']['end'];


			$this->_controller->AdvancesData->create();
			$this->_controller->AdvancesData->save($Insert);
			$AdvanceData = $this->_controller->AdvancesData->find('first',$options);

			$this->__CreateDailyStatus($Insert);

		}

		$Insert = array();
		$Insert['testingcomp_id'] = $testingcomp_id;
		$Insert['advance_id'] = $id;
		$Insert['cascade_id'] = $data['AdvancesDataDependency']['cascade_id'];
		$Insert['order_id'] = $data['AdvancesDataDependency']['order_id'];
		$Insert['advances_type_id'] = $data['AdvancesDataDependency']['AdvancesType'];
		$Insert['advances_data_id'] = $AdvanceData['AdvancesData']['id'];

		for($i = 0; $i < $Count; $i++) {

			$Insert['description'] = $data['AdvancesDataDependency']['description'];
			$this->_controller->AdvancesDataDependency->create();
			if($this->_controller->AdvancesDataDependency->save($Insert)){

				$JSONName['parameter']['cascade_id'] = $this->_controller->request->data['AdvancesDataDependency']['cascade_id'];
				$this->_controller->set('JSONName',$JSONName);

			} else {
				return false;
			}
		}

		return true;
	}

	public function CollectStatisticCascadeData($data){

		if(isset($data['Scheme']['AdvancesOrder'])) return $data;
		if(isset($data['Scheme']['CascadeGroup'])) return $data;
		if(!isset($data['Scheme']['children'])) return $data;

		$data['Scheme']['children'] = $this->__CollectCascadeData($data['Scheme']['children']);

		$AdvanceTypes = $this->_controller->advance_type;
		$StatusArray = array('count' => 0,'open' => 0,'okay' => 0,'error' => 0);

		$AdvanceTypes = array_flip($AdvanceTypes);

		foreach($AdvanceTypes as $key => $value){
			$AdvanceTypes[$key] = $StatusArray;

		}

		foreach ($data['Scheme']['children'] as $key => $value) {

			$AdvanceType[$key] = $AdvanceTypes;

		}

		foreach ($data['Scheme']['children'] as $key => $value) {

			if(!isset($value['Status'])) continue;

			foreach($value['Status'] as $_key => $_value){

				$AdvanceType[$key][$_key] = $StatusArray;

				$AdvanceType[$key][$_key]['open'] = 0;
				$AdvanceType[$key][$_key]['okay'] = 0;
				$AdvanceType[$key][$_key]['error'] = 0;
				$AdvanceType[$key][$_key]['count'] = 0;

				if($_value['result']['count'] == 0) continue;

				$AdvanceType[$key][$_key]['open'] += $_value['result']['open'];
				$AdvanceType[$key][$_key]['okay'] += $_value['result']['okay'];
				$AdvanceType[$key][$_key]['error'] += $_value['result']['error'];
				$AdvanceType[$key][$_key]['count'] += ($_value['result']['error'] + $_value['result']['okay'] + $_value['result']['open']);

			}

			$output['Status'] = array();

			$output['Status']['all_advance_methods']['result']['count'] = 0;
			$output['Status']['all_advance_methods']['result']['open'] = 0;
			$output['Status']['all_advance_methods']['result']['okay'] = 0;
			$output['Status']['all_advance_methods']['result']['error'] = 0;

			$advance_rep = 0;
			$advance_all = 0;

			foreach ($AdvanceType[$key] as $_key => $_value) {

				if($_value['count'] == 0) continue;

			  $output['Status'][$_key] = array();

			  $output['Status'][$_key]['group_advance'] = 'group_advance_' . $_key;
			  $output['Status'][$_key]['advance_count'] = 'advance_count_' . $_key;
			  $output['Status'][$_key]['advance_open'] = 'advance_open_' . $_key;
			  $output['Status'][$_key]['advance_okay'] = 'advance_okay_' . $_key;
			  $output['Status'][$_key]['advance_error'] = 'advance_error_' . $_key;
			  $output['Status'][$_key]['advance_line'] = 'advance_line_' . $_key;

			  if($_value['count'] > 0){
			    $advance_rep = $this->RoundValue(100 * $_value['error'] / $_value['count']);
			    $advance_all = $this->RoundValue(100 * ($_value['okay'] + $_value['error'] ) / $_value['count']);
			  }

			  $output['Status'][$_key]['result']['count'] = $_value['count'];
			  $output['Status'][$_key]['result']['open'] = $_value['open'];
			  $output['Status'][$_key]['result']['okay'] = $_value['okay'];
			  $output['Status'][$_key]['result']['error'] = $_value['error'];

			  $output['Status'][$_key]['result']['advance_all_percent'] = $advance_all;
			  $output['Status'][$_key]['result']['advance_rep_percent'] = $advance_rep;
			  $output['Status'][$_key]['result']['advance_all_deci'] = $advance_all / 100;
			  $output['Status'][$_key]['result']['advance_rep_deci'] = $advance_rep / 100;

				$output['Status'][$_key]['advance_line_color'] = '#038000';

				if($data['Scheme']['children'][$key]['schedule']['status']['end'] == 1) $output['Status'][$_key]['advance_line_color'] = '#ff7e00';
				if($data['Scheme']['children'][$key]['schedule']['status']['end_delay'] == 1) $output['Status'][$_key]['advance_line_color'] = '#ff0000';
			  if($output['Status'][$_key]['result']['count'] == $output['Status'][$_key]['result']['okay']) $output['Status'][$_key]['advance_line_color'] = '#038000';
				if($output['Status'][$_key]['result']['error'] > 0) $output['Status'][$_key]['advance_line_color'] = '#ff0000';


			  $output['Status']['all_advance_methods']['result']['count'] += $output['Status'][$_key]['result']['count'];
			  $output['Status']['all_advance_methods']['result']['open'] += $output['Status'][$_key]['result']['open'];
			  $output['Status']['all_advance_methods']['result']['okay'] += $output['Status'][$_key]['result']['okay'];
			  $output['Status']['all_advance_methods']['result']['error'] += $output['Status'][$_key]['result']['error'];

			  if(array_sum($output['Status']['all_advance_methods']['result']) == 0) return $data;

			  $advance_rep = $this->RoundValue(100 * $output['Status']['all_advance_methods']['result']['error'] / $output['Status']['all_advance_methods']['result']['count']);
			  $advance_all = $this->RoundValue(100 * ($output['Status']['all_advance_methods']['result']['okay'] + $output['Status']['all_advance_methods']['result']['error']) / $output['Status']['all_advance_methods']['result']['count']);

			  $output['Status']['all_advance_methods']['result']['advance_all_percent'] = $advance_all;
			  $output['Status']['all_advance_methods']['result']['advance_rep_percent'] = $advance_rep;
			  $output['Status']['all_advance_methods']['result']['advance_all_deci'] = $advance_all / 100;
			  $output['Status']['all_advance_methods']['result']['advance_rep_deci'] = $advance_rep / 100;

				$output['Status']['all_advance_methods']['advance_line_color'] = '#3c5bbe';

				if($data['Scheme']['children'][$key]['schedule']['status']['end'] == 1) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff7e00';
				if($data['Scheme']['children'][$key]['schedule']['status']['end_delay'] == 1) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff0000';
				if($output['Status']['all_advance_methods']['result']['count'] == $output['Status']['all_advance_methods']['result']['okay']) $output['Status']['all_advance_methods']['advance_line_color'] = '#038000';
				if($advance_rep > 0) $output['Status']['all_advance_methods']['advance_line_color'] = '#ff0000';


			}

			if(isset($data['Scheme']['children'][$key])){
				$data['Scheme']['children'][$key]['Status'] = $output['Status'];
			}

			unset($output);

		}

		return $data;
	}

	protected function __CollectCascadeData($data){

		$AdvanceType = $this->_controller->advance_type;

		foreach ($data as $key => $value) {

			if(!isset($value['children'])) continue;

			$data[$key]['children'] = $this->__CollectChildCascadeData($value['children']);
			$CascadeChildren = $this->_controller->Navigation->CascadeGetAllChildren($key,array());

			if(count($CascadeChildren) == 0) continue;

			foreach ($AdvanceType as $_key => $_value) {

				$options = array(
					'conditions' => array(
						'AdvancesDataDependency.cascade_id' => $CascadeChildren,
						'AdvancesDataDependency.advances_type_id' => $_key,
						'AdvancesDataDependency.deleted' => 0,
					)
				);

				$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

				if(count($AdvancesDataDependency) == 0) continue;

				$data[$key]['Status'][$_value]['result']['count'] = 0;
				$data[$key]['Status'][$_value]['result']['open'] = 0;
				$data[$key]['Status'][$_value]['result']['okay'] = 0;
				$data[$key]['Status'][$_value]['result']['error'] = 0;

				foreach ($AdvancesDataDependency as $__key => $__value) {

					++$data[$key]['Status'][$_value]['result']['count'];

					if($__value['AdvancesDataDependency']['value'] == 0) ++$data[$key]['Status'][$_value]['result']['open'];
					if($__value['AdvancesDataDependency']['value'] == 1) ++$data[$key]['Status'][$_value]['result']['okay'];
					if($__value['AdvancesDataDependency']['value'] == 2) ++$data[$key]['Status'][$_value]['result']['error'];

				}
			}

			if(!isset($data[$key]['Status'])) continue;

			foreach($data[$key]['Status'] as $_key => $_value){

				$advance_rep = $this->RoundValue(100 * $_value['result']['error'] / $_value['result']['count']);
				$advance_all = $this->RoundValue(100 * ($_value['result']['okay'] + $_value['result']['error']) / $_value['result']['count']);

				$data[$key]['Status'][$_key]['result']['advance_all_percent'] = $advance_all;
				$data[$key]['Status'][$_key]['result']['advance_rep_percent'] = $advance_rep;
				$data[$key]['Status'][$_key]['result']['advance_all_deci'] = $advance_all / 100;
				$data[$key]['Status'][$_key]['result']['advance_rep_deci'] = $advance_rep / 100;

				if($_value['result']['error'] > 0) $data[$key]['Status'][$_key]['advance_line_color'] = '#ff0000';
				if($_value['result']['error'] == 0) $data[$key]['Status'][$_key]['advance_line_color'] = '#038000';

			}
		}

		return $data;
	}

	protected function __CollectChildCascadeData($data){

		if(count($data) == 0) return  $data;

		$AdvanceType = $this->_controller->advance_type;
		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		foreach ($data as $key => $value) {

			$CascadeChildren = $this->_controller->Navigation->CascadeGetAllChildren($key,array());

			if(count($CascadeChildren) == 0) continue;

			foreach ($AdvanceType as $_key => $_value) {

				$options = array(
					'conditions' => array(
						'AdvancesDataDependency.cascade_id' => $CascadeChildren,
						'AdvancesDataDependency.advances_type_id' => $_key,
						'AdvancesDataDependency.deleted' => 0,
					)
				);

				$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

				if(count($AdvancesDataDependency) == 0) continue;

				$data[$key]['Status'][$_value]['result']['count'] = 0;
				$data[$key]['Status'][$_value]['result']['open'] = 0;
				$data[$key]['Status'][$_value]['result']['okay'] = 0;
				$data[$key]['Status'][$_value]['result']['error'] = 0;

				foreach ($AdvancesDataDependency as $__key => $__value) {

					++$data[$key]['Status'][$_value]['result']['count'];

					if($__value['AdvancesDataDependency']['value'] == 0) ++$data[$key]['Status'][$_value]['result']['open'];
					if($__value['AdvancesDataDependency']['value'] == 1) ++$data[$key]['Status'][$_value]['result']['okay'];
					if($__value['AdvancesDataDependency']['value'] == 2) ++$data[$key]['Status'][$_value]['result']['error'];

				}
			}

			$ScheduleRequest['Scheme']['children'][$key] = $data[$key];
			$ScheduleRequest['Advance']['id'] = $AdvanceId;
			$ScheduleRequest = $this->__AssignAdvancesCascadesDates($ScheduleRequest);
			$data[$key] = $ScheduleRequest['Scheme']['children'][$key];

			if(!isset($data[$key]['Status'])) continue;

			foreach ($data[$key]['Status'] as $_key => $_value) {
				$data[$key]['status']['all_advance_methods']['count'] += $_value['result']['count'];
				$data[$key]['status']['all_advance_methods']['open'] += $_value['result']['open'];
				$data[$key]['status']['all_advance_methods']['okay'] += $_value['result']['okay'];
				$data[$key]['status']['all_advance_methods']['error'] += $_value['result']['error'];
			}

			$data[$key]['status']['all_advance_methods']['advance_all_deci'] = ($data[$key]['status']['all_advance_methods']['okay'] + $data[$key]['status']['all_advance_methods']['error']) / $data[$key]['status']['all_advance_methods']['count'];
			$data[$key]['status']['all_advance_methods']['advance_all_percent'] = 100 * ($data[$key]['status']['all_advance_methods']['okay'] + $data[$key]['status']['all_advance_methods']['error']) / $data[$key]['status']['all_advance_methods']['count'];

		}

		return $data;
	}

	public function CollectStatisticCascadeGroupData($data){

		if(!isset($data['Scheme']['CascadeGroup'])) return $data;
		if(!is_array($data['Scheme']['CascadeGroup'])) return $data;
		if(count($data['Scheme']['CascadeGroup']) == 0) return $data;

		$AdvanceType = $this->_controller->advance_type;

		foreach ($data['Scheme']['CascadeGroup'] as $key => $value) {

			if(!isset($value['Cascades'])) continue;
			if(!is_array($value['Cascades'])) continue;
			if(count($value['Cascades']) == 0) continue;

			foreach ($AdvanceType as $_key => $_value) {


				$options = array(
					'conditions' => array(
						'AdvancesDataDependency.cascade_id' => $value['Cascades'],
						'AdvancesDataDependency.advances_type_id' => $_key,
						'AdvancesDataDependency.deleted' => 0,
					)
				);

				$AdvancesDataDependency = $this->_controller->AdvancesDataDependency->find('all', $options);

				if(count($AdvancesDataDependency) == 0) continue;

				$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['count'] = 0;
				$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['open'] = 0;
				$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['okay'] = 0;
				$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['error'] = 0;

				foreach ($AdvancesDataDependency as $__key => $__value) {

					++$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['count'];

					if($__value['AdvancesDataDependency']['value'] == 0) ++$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['open'];
					if($__value['AdvancesDataDependency']['value'] == 1) ++$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['okay'];
					if($__value['AdvancesDataDependency']['value'] == 2) ++$data['Scheme']['CascadeGroup'][$key]['Status'][$_value]['result']['error'];

				}
			}

			if(!isset($value['Cascades'])) continue;
			if(count($value['Cascades']) == 0) continue;

			$options = array(
				'conditions' => array(
					'Cascade.id' => $value['Cascades'][0],
				)
			);

			$CascadeParent = $this->_controller->Cascade->find('first', $options);

			$options = array(
				'conditions' => array(
					'Cascade.id' => $CascadeParent['Cascade']['parent'],
				)
			);

			$CascadeParent = $this->_controller->Cascade->find('first', $options);

			$options = array(
				'conditions' => array(
					'AdvancesCascade.advance_id' => $data['Advance']['id'],
					'AdvancesCascade.cascade_id' => $CascadeParent['Cascade']['id'],
					'AdvancesCascade.deleted' => 0,
				)
			);

			$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

			$AdvancesCascadeDate = $this->__CheckCascadeDate(array(),$AdvancesCascade);

			$data['Scheme']['CascadeGroup'][$key]['Schedule'] = $AdvancesCascadeDate['AdvancesCascade'];

			if(!isset($data['Scheme']['CascadeGroup'][$key]['Status'])) continue;

			foreach($data['Scheme']['CascadeGroup'][$key]['Status'] as $_key => $_value){

				$advance_rep = $this->RoundValue(100 * $_value['result']['error'] / $_value['result']['count']);
				$advance_all = $this->RoundValue(100 * ($_value['result']['okay'] + $_value['result']['error']) / $_value['result']['count']);

				$data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['result']['advance_all_percent'] = $advance_all;
				$data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['result']['advance_rep_percent'] = $advance_rep;
				$data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['result']['advance_all_deci'] = $advance_all / 100;
				$data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['result']['advance_rep_deci'] = $advance_rep / 100;

				$data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['advance_line_color'] = '#038000';

				if($data['Scheme']['CascadeGroup'][$key]['Schedule']['status']['end'] == 1) $data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['advance_line_color'] = '#ff7e00';
				if($data['Scheme']['CascadeGroup'][$key]['Schedule']['status']['end_delay'] == 1) $data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['advance_line_color'] = '#ff0000';
				if($data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['result']['count'] == $data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['result']['okay']) $data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['advance_line_color'] = '#038000';
				if($_value['result']['error'] > 0) $data['Scheme']['CascadeGroup'][$key]['Status'][$_key]['advance_line_color'] = '#ff0000';

			}

			unset($options);

			foreach ($value['Cascades'] as $_key => $_value) {

				$options = array(
					'conditions' => array(
						'AdvancesCascade.advance_id' => $data['Advance']['id'],
						'AdvancesCascade.cascade_id' => $_value,
						'AdvancesCascade.deleted' => 0,
					)
				);

				$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

				if(count($AdvancesCascade) == 0) continue;

				$AdvancesCascadeDate = $this->__CheckCascadeDate(array(),$AdvancesCascade);

				$options = array(
					'conditions' => array(
						'Cascade.id' => $_value,
					)
				);

				$Cascade = $this->_controller->Cascade->find('first', $options);

				$data['Scheme']['CascadeGroup'][$key]['AdvancesCascade'][$_value]['AdvancesCascade'] = $AdvancesCascadeDate['AdvancesCascade'];
				$data['Scheme']['CascadeGroup'][$key]['AdvancesCascade'][$_value]['Cascade'] = $Cascade['Cascade'];

			}

		}

		return $data;
	}

	protected function __FindMinMaxDate($MinStart,$mod){

	}

	public function CollectStatisticOrderData($data){

		if(!isset($data['Scheme']['AdvancesOrder'][0])) return $data;

		$AdvancesOrder = $data['Scheme']['AdvancesOrder'][0];
		unset($data['Scheme']['AdvancesOrder']);

		$data['Scheme']['AdvancesOrder'] = $AdvancesOrder;

		$Element = $this->AdvanceElements($data);

		unset($Element['Scheme']);
		unset($Element['Menue']);
		unset($Element['BreadcrumpArray']);

		$delay = $Element['AdvancesCascade']['karenz'];

		$current = new DateTime();
		$start = new DateTime($Element['AdvancesCascade']['start']);
		$end = new DateTime($Element['AdvancesCascade']['end']);
		$end_delay = new DateTime($Element['AdvancesCascade']['end']);
		$end_delay->modify('+'.$delay.' day');

		$CurrentStartDiff = $current->diff($start);
		$CurrentEndDiff = $current->diff($end);
		$CurrentEndDelayDiff = $current->diff($end_delay);

		$interval = $start->diff($end);
		$resttime = $current->diff($end);

		$days_gone = $this->RoundValue(100 * ($interval->format('%a') - $resttime->format('%a')) / $interval->format('%a'));

		$Element['AdvancesCascade']['interval'] = $interval->format('%a');
		$Element['AdvancesCascade']['resttime'] = $resttime->format('%a');
		$Element['AdvancesCascade']['days_gone'] = $days_gone;
		$Element['AdvancesCascade']['status']['end_delay'] = $CurrentEndDelayDiff->invert;
		$Element['AdvancesCascade']['status']['end'] = $CurrentEndDiff->invert;
		$Element['AdvancesCascade']['status']['start'] = $CurrentStartDiff->invert;

		foreach ($data['Scheme']['AdvancesOrder']['AdvancesDataTypes'] as $key => $value) {

			if($value['count'] == 0) continue;

			$all = $value['count'];
			$all_e_ne = $value['okay'] + $value['error'];
			$all_e = $value['okay'];
			$all_ne = $value['error'];

			$advance_rep = $this->RoundValue(100 * $all_ne / $all);
			$advance_all = $this->RoundValue(100 * $all_e_ne / $all);

			$data['Scheme']['AdvancesOrder']['Date'] = $Element['AdvancesCascade'];
			$data['Scheme']['AdvancesOrder']['AdvancesDataTypes'][$key]['statistic']['advance_all_percent'] = $advance_all;
			$data['Scheme']['AdvancesOrder']['AdvancesDataTypes'][$key]['statistic']['advance_rep_percent'] = $advance_rep;
			$data['Scheme']['AdvancesOrder']['AdvancesDataTypes'][$key]['statistic']['advance_all_deci'] = $advance_all / 100;
			$data['Scheme']['AdvancesOrder']['AdvancesDataTypes'][$key]['statistic']['advance_rep_deci'] = $advance_rep / 100;

		}

		$advance_all = 0;
		$advance_all_count = 0;

		foreach ($data['Scheme']['AdvancesOrder']['AdvancesDataTypes'] as $key => $value) {

			if(!isset($value['statistic'])) continue;

			$advance_all += $value['statistic']['advance_all_percent'];
			$advance_all_count++;
		}

		if($advance_all_count == 0 && $advance_all == 0){
			$data['Scheme']['AdvancesOrder']['Statistic']['advance_all_'] = 0;
			$data['Scheme']['AdvancesOrder']['Statistic']['advance_all__count'] = 0;
			$data['Scheme']['AdvancesOrder']['Statistic']['advance_all_percent'] = 0;
		} else {
			$data['Scheme']['AdvancesOrder']['Statistic']['advance_all_'] = $advance_all;
			$data['Scheme']['AdvancesOrder']['Statistic']['advance_all__count'] = $advance_all_count * 100;
			$data['Scheme']['AdvancesOrder']['Statistic']['advance_all_percent'] = $this->RoundValue($advance_all * 100 / $advance_all_count * 100);
		}

		return $data;
	}


	public function CreateEquipmentmenue($data){

		$LastInLine = $this->LastInLineCascade();

		if($LastInLine === false) return $data;

		$options = array(
			'order' => array('discription'),
			'fields' => array('id','discription'),
			'conditions' => array(
				'Cascade.parent' => $data['Cascade']['parent'],
			)
		);

		$Cascades = $this->_controller->Cascade->find('list', $options);

		$data['Equipmentmenue'] = $Cascades;

		return $data;
	}

	public function CreateSubmenue($data){

		if(!isset($data['Scheme']['children'])) return $data;
		if(count($data['Scheme']['children']) == 0) return $data;

		$Menue = array(0 => ' ');

		foreach($data['Scheme']['children'] as $key => $value) {
			$Menue[$key] = $value['discription'];
		}

		if(count($Menue) == 0) return $data;

		asort($Menue);

		$data['Submenue'] = $Menue;

		return $data;
	}

	public function HumanizArray($data){

		foreach ($data as $key => $value) {
			$data[$key] = __($value,true);
		}

		natsort($data);

		return $data;
	}

	public function LastInLineCascade(){

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$options = array(
			'conditions' => array(
				'Cascade.parent' => $CascadeId,
			)
		);

		$Cascade = $this->_controller->Cascade->find('first', $options);

		if(count($Cascade) == 0) return true;
		else return false;

	}

	public function CreateGanttPlotStartOverview($data){

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);


		$Images = $ImageView->render('diagramm/cascade_gantt_plot_start');

		$data['GanttDiagrammStart'] = base64_encode($Images);

		return $data;

	}

	public function CreateGanttPlot($data){

		$LastInLine = $this->LastInLineCascade();

		if($LastInLine === true) $data = $this->__CreateOrderGanttPlot($data);
		if($LastInLine === false) $data = $this->__CreateCascadeGanttPlot($data);
		if(isset($data['Scheme']['CascadeGroup']) && count($data['Scheme']['CascadeGroup']) > 0) $data = $this->__CreateCascadeGanttPlotGroups($data);

		return $data;
	}

	protected function __CreateOrderGanttPlot($data){

		if(!isset($data['Status'])) return $data;
		if(!isset($data['Scheme']['AdvancesOrder'])) return $data;

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/order_gantt_plot');

		$data['GanttDiagramm'] = base64_encode($Images);

		return $data;

	}

	protected function __CreateCascadeGanttPlot($data){

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/cascade_gantt_plot_units');

		if(empty($Images)){
			$ImageView = new View($this->_controller);
			$ImageView->autoRender = false;
			$ImageView->layout = null;
			$ImageView->set('data',$data);
			$ImageView->set('return',true);

			$Images = $ImageView->render('diagramm/cascade_gantt_plot');
		}

		$data['GanttDiagramm'] = base64_encode($Images);

		return $data;

	}

	protected function __CreateCascadeGanttPlotGroups($data){

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/cascade_gantt_plot_groups');

		$data['GanttDiagramm'] = base64_encode($Images);

		return $data;

	}

	public function CreateRingPlot($data){

		$LastInLine = $this->LastInLineCascade();

		if($LastInLine === true) $data = $this->__CreateOrderRingPlot($data);
		if($LastInLine === false) $data = $this->__CreateCascadeRingPlot($data);

		return $data;
	}

	protected function __CreateOrderRingPlot($data){

		if(!isset($data['Status'])) return $data;
		if(count($data['Status']) == 0) return $data;

		foreach ($data['Status'] as $key => $value) {

			if($key == 'all_advance_methods') continue;

			$ImageView = new View($this->_controller);
			$ImageView->autoRender = false;
			$ImageView->layout = null;
			$ImageView->set('key',$key);
			$ImageView->set('data',$value);
			$ImageView->set('return',true);
			$Images = $ImageView->render('diagramm/order_ring_plot');

			$data['RingPlotDiagramm'][$key] = base64_encode($Images);

		}

		return $data;
	}

	protected function __CreateCascadeRingPlot($data){

		if(!isset($data['Status'])) return $data;

		foreach ($data['Status'] as $key => $value) {

			if($value['result']['count'] == 0) continue;

			$ImageView = new View($this->_controller);
			$ImageView->autoRender = false;
			$ImageView->layout = null;
			$ImageView->set('key',$key);
			$ImageView->set('data',$value);
			$ImageView->set('return',true);
			$Images = $ImageView->render('diagramm/cascade_ring_plot');

			$data['RingPlotDiagramm'][$key] = base64_encode($Images);

		}

		return $data;
	}

	public function CreateLinePlot($data){

/*
DSUTILS_MONTH
DSUTILS_MONTH1
DSUTILS_MONTH2
DSUTILS_MONTH3
DSUTILS_MONTH6
DSUTILS_WEEK1
DSUTILS_WEEK2
DSUTILS_WEEK4
DSUTILS_DAY1
DSUTILS_DAY2
DSUTILS_DAY4
DSUTILS_YEAR1
DSUTILS_YEAR2
DSUTILS_YEAR5
*/

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/order_line_plot');

		$data['LineDiagramm'] = base64_encode($Images);

		$LastInLine = $this->LastInLineCascade();

		if($LastInLine === true) $data = $this->__CreateOrderLinePlot($data);
		if($LastInLine === false) $data = $this->__CreateCascadeLinePlot($data);

		return $data;
	}

	protected function __CreateOrderLinePlot($data){

		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/order_line_plot');

		$data['LineDiagramm'] = base64_encode($Images);

		return $data;
	}

	protected function __CreateCascadeLinePlot($data){
		$ImageView = new View($this->_controller);
		$ImageView->autoRender = false;
		$ImageView->layout = null;
		$ImageView->set('data',$data);
		$ImageView->set('return',true);
		$Images = $ImageView->render('diagramm/cascade_line_plot');

		$data['LineDiagramm'] = base64_encode($Images);

		return $data;
	}

	public function CreateDateScale($data){

		if(!isset($data['Advance'])) return $data;

		$TimeRange = array();
		$StartDate = date('Y-m-d', strtotime($data['Advance']['start'] . ' -1 day'));
		$StopDate = date('Y-m-d', strtotime($data['Advance']['end'] . ' +2 day'));

		$period = new DatePeriod(
			new DateTime($StartDate),
     	new DateInterval('P1D'),
     	new DateTime($StopDate)
	 	);

		foreach ($period as $key => $value) {

			$date = new DateTime($value->format('Y-m-d'));
			$timestamp = $date->getTimestamp();

    	$TimeRange['DateFormat'][] = $value->format('Y-m-d');
			$TimeRange['TimeStamp'][] = $timestamp;
		}

		$TimeRange['StartDate'] = $StartDate;
		$TimeRange['EndDate'] = $StopDate;

		$StartDate = new DateTime($StartDate);
		$timestamp = $date->getTimestamp();
		$TimeRange['StartDateTimestamp'] = $timestamp;

		$StopDate = new DateTime($StopDate);
		$timestamp = $date->getTimestamp();
		$TimeRange['EndDateTimestamp'] = $timestamp;


		$data['AdvanceTimeRange'] = $TimeRange;

		return $data;
	}

	public function CollectMesurePoints($data){

		$data['Scheme']['CollectMesurePoints'] = array();

		foreach ($data['Scheme']['AdvancesOrder']['AdvancesData'] as $key => $value) {

			if(!isset($value['AdvancesData']['AdvancesDataDependency'][key($value['AdvancesData']['AdvancesDataDependency'])])) continue;

			$data['Scheme']['CollectMesurePoints'][key($value['AdvancesData']['AdvancesDataDependency'])] = array('open' => array(),'close' => array(),'repair' => array());

			foreach ($value['AdvancesData']['AdvancesDataDependency'][key($value['AdvancesData']['AdvancesDataDependency'])] as $_key => $_value) {

				if($_value['AdvancesDataDependency']['value'] == 0) $data['Scheme']['CollectMesurePoints'][key($value['AdvancesData']['AdvancesDataDependency'])]['open'][] = $_value['AdvancesDataDependency']['description'];
				if($_value['AdvancesDataDependency']['value'] == 1) $data['Scheme']['CollectMesurePoints'][key($value['AdvancesData']['AdvancesDataDependency'])]['close'][] = $_value['AdvancesDataDependency']['description'];
				if($_value['AdvancesDataDependency']['value'] == 2) $data['Scheme']['CollectMesurePoints'][key($value['AdvancesData']['AdvancesDataDependency'])]['repair'][] = $_value['AdvancesDataDependency']['description'];

			}
		}

		return $data;

	}

	public function SortOjects($data){

		if(isset($data['Scheme']['children'])) $data = $this->__SortSchemeChildren($data);

		return $data;
	}

	protected function __SortSchemeChildren($data){

		if(count($data['Scheme']['children']) == 0) return $data;

		$keys = array_keys($data['Scheme']['children']);

		array_multisort(array_column($data['Scheme']['children'], 'discription'), SORT_DESC, SORT_NUMERIC, $data['Scheme']['children'], $keys);

		$data['Scheme']['children'] = array_combine($keys, $data['Scheme']['children']);

		return $data;
	}

	public function CollectTestReports($data){

		if(!isset($data['Scheme']['AdvancesOrder'])) return $data;

		$AdvanceId = $this->_controller->request->projectvars['VarsArray'][0];

		$this->_controller->loadModel('Testingmethod');
		$this->_controller->loadModel('Reportnumber');
		$this->_controller->Reportnumber->recursive = 0;


		foreach ($data['Scheme']['AdvancesOrder']['AdvancesData'] as $key => $value) {
			$Type = key($data['Scheme']['AdvancesOrder']['AdvancesData'][$key]['AdvancesData']['AdvancesDataDependency']);

			$options = array(
				'conditions' => array(
					'Testingmethod.value' => $Type,
				)
			);

			$Testingmethod = $this->_controller->Testingmethod->find('first', $options);

			if(count($Testingmethod) == 0) continue;

			$Reports = $this->__CollectReports($Testingmethod);

			$data['Scheme']['AdvancesOrder']['AdvancesData'][$key]['AdvancesDataReport'][$Type] = $Reports;


		}

		return $data;

	}

	protected function __CollectReports($data){

		$CascadeId = $this->_controller->request->data['scheme_start'];

		$Output = array();

		// Zugehörigkeit des Berichts muss noch geprüft werden
		$options = array(
			'conditions' => array(
				'Reportnumber.cascade_id' => $CascadeId,
				'Reportnumber.testingmethod_id' => $data['Testingmethod']['id'],
			)
		);

		$Reportnumbers = $this->_controller->Reportnumber->find('all', $options);

		if(count($Reportnumbers ) == 0) return false;

		foreach ($Reportnumbers as $key => $value) {

			unset($Reportnumbers[$key]['Topproject']);
			unset($Reportnumbers[$key]['Order']);
			unset($Reportnumbers[$key]['Report']);
			unset($Reportnumbers[$key]['Testingcomp']);
			unset($Reportnumbers[$key]['EquipmentType']);
			unset($Reportnumbers[$key]['Equipment']);

			$Reportnumbers[$key]['PrintLink'] = array(
				$value['Reportnumber']['topproject_id'],
				$value['Reportnumber']['cascade_id'],
				$value['Reportnumber']['order_id'],
				$value['Reportnumber']['report_id'],
				$value['Reportnumber']['id']
			);

 		}

		return $Reportnumbers;
	}


	protected function __RemoveDeletedElements($data,$Mode){

		if($Mode == 1) $Ids = array_keys($data);
		if($Mode == 2) $Ids = $data;

		$options = array(
			'fields' => array('id','cascade_id'),
			'conditions' => array(
				'AdvancesCascade.cascade_id' => $Ids,
				'AdvancesCascade.deleted' => 1,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('list', $options);

		if(count($AdvancesCascade) == 0) return $data;

		if($Mode == 1){

			foreach ($AdvancesCascade as $key => $value){
				unset($data[$value]);
			}
		}

		if($Mode == 2){

			foreach ($AdvancesCascade as $key => $value){
				if(array_search($value, $data) != false) unset($data[array_search($value, $data)]);
			}
		}

		return $data;
	}

	public function AdvancesTypes($data) {

		$AdvancesType = $this->_controller->AdvancesType->find('all',array('order' => 'type','conditions' => array('AdvancesType.deleted' => 0)));

		if(count($AdvancesType) == 0) return $data;

		$data['AdvancesType'] = $AdvancesType;

		return $data;

	 }

	public function GetTestingsForChecklist ($Data) {
		$options = array(
			'fields' => array('id','type'),
			'conditions' => array(
			'AdvancesType.ndt' => 1,
			'AdvancesType.deleted' => 0,
			)
		);
		$AdvancesType['ndt'] = $this->_controller->AdvancesType->find('list', $options);

		$options = array(
			'fields' => array('id','type'),
			'conditions' => array(
			'AdvancesType.ndt' => 0,
			'AdvancesType.deleted' => 0,
			)
		);
		$AdvancesType['non_ndt'] = $this->_controller->AdvancesType->find('list', $options);
		$Data['AdvancesType'] = $AdvancesType;
		return $Data;
	 }
}
