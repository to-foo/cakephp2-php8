<?php
class CascadesComponent extends Component {

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

	public function GetCompleteCascadeObject(){

		$projectID = $this->_controller->request->projectID;
		$cascadeID = $this->_controller->request->cascadeID;
		$orderID = $this->_controller->request->orderID;

		$lang = $this->_controller->request->lang;

		$Data = array();

		$this->_controller->Cascade->recursive = 1;
		$Data['CascadeCurrent'] = $this->_controller->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));

		$Data['CascadeChild'] = $this->_controller->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $Data['CascadeCurrent']['Cascade']['id'])));
		$Data['CascadeParent'] = $this->_controller->Cascade->find('first', array('conditions' => array('Cascade.id' => $Data['CascadeCurrent']['Cascade']['parent'])));

		$Data['CascadeCurrent'] = $this->_controller->Data->BelongsToManySelected($Data['CascadeCurrent'], 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));
		$Data['Testingcomps'] = $this->_controller->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname'),'conditions' => array('Testingcomp.id' => $Data['CascadeCurrent']['Testingcomp']['selected'])));

		if(Configure::check('CascadeLoadModules') == false) return $Data;
		if(Configure::read('CascadeLoadModules') == false) return $Data;

		$Data['CascadeCurrent'] = $this->_controller->Data->BelongsToManySelected($Data['CascadeCurrent'], 'Cascade', 'CascadeGroup', array('CascadegroupsCascades','cascade_group_id','cascade_id'));

		$CascadeTree[] = $Data['CascadeCurrent'];
		$Data['CascadeTree'] = $this->_controller->Navigation->CascadeGetBreads($CascadeTree);

		$Data['CascadeGroup'] = $this->_controller->Cascade->CascadeGroup->find('first',array('conditions' => array('CascadeGroup.id' => $Data['CascadeCurrent']['CascadeGroup']['selected'])));
		$Data['CascadeGroups'] = $this->_controller->Cascade->CascadeGroup->find('list', array('fields' => array('id',$lang),'conditions' => array('CascadeGroup.id' => $Data['CascadeCurrent']['CascadeGroup']['selected'])));

		if (empty($Data['CascadeGroups'])) {
				$Data['CascadeGroups'] = $this->_controller->Cascade->CascadeGroup->find('list', array('fields' => array('id',$lang)));
		}

		if(empty($Data['CascadeGroups'])) return $Data;

		$xmlname = $Data['CascadeGroup']['CascadeGroup']['xml_name'];
		$cascadegroupmodel = $Data['CascadeGroup']['CascadeGroup']['model']; // Model wird benötigt um die Datenbanktabelle anzusprechen

		if (!empty($cascadegroupmodel)) {

				$this->_controller->loadModel($cascadegroupmodel);
				$Data['GroupData'] = $this->_controller->{$cascadegroupmodel}->find('first', array('conditions'=>array('cascade_id'=>$cascadeID)));

				if(count($Data['GroupData']) > 0) $groupid = $Data['GroupData'][$cascadegroupmodel]['id'];

		}

		if(empty($cascadegroupmodel)) return $Data;
		if(!empty($cascadegroupmodel)) $Data['Xml'] = $this->_controller->Xml->LoadXmlFile('technicalplace', $xmlname);

		return $Data;
	}

	public function CheckInputForAdd($Request,$Data){

		$Message = false;

		if(empty($Request['Cascade']['discription'])){

			$Message['error'][] = array(
				'message' => __('Description has no value',true),
				'id' => 'CascadeDiscription'
			);

		}

		$CascadeIds = Hash::extract($Data['CascadeChild'], '{n}.Cascade.id');
		$Cascade = $this->_controller->Cascade->find('all', array(
			'conditions' =>
				array(
					'Cascade.id' => $CascadeIds,
					'Cascade.discription' => $Request['Cascade']['discription']
				)
			)
		);

		if(count($Cascade) == 1){

			$Message['error'][] = array(
				'message' => __('Description already exists',true),
				'id' => 'CascadeDiscription'
			);


		}

		switch ($Request['Cascade']['orders']) {
			case 0:

			break;
			case 1:

			break;
			case 2:

			if(empty($Request['CascadeGroup']['CascadeGroup'])){

				$Message['error'][] = array(
					'message' => __('CascadeGroup has no value',true),
					'id' => 'CascadeGroupCascadeGroup'
				);

			}

			break;
		}

		return $Message;
	}

	public function SaveNewCascade($Data){
		$Data = $this->__SaveCascade($Data);
		if(isset($Data['Message'])) return $Data;

		$Data = $this->__SaveCascadeOrder($Data);
		if(isset($Data['Message'])) return $Data;

		$Data = $this->__SaveCascadeGroups($Data);
		if(isset($Data['Message'])) return $Data;

		$Data = $this->__SaveCascadeOrderModule($Data);
		if(isset($Data['Message'])) return $Data;

		return $Data;
	}

	protected function __SaveCascade($Data){

		if(!isset($this->_controller->request->data['Cascade'])){

			$Data['Message']['error'][] = array(
				'message' => __('New cascade cannot be saved',true),
				'id' => ''
			);

			return $Data;
		}

		unset($this->_controller->request->data['Cascade']['id']);

		$this->_controller->Cascade->create();

		if($this->_controller->Cascade->save($this->_controller->request->data)) {

			$Id = $this->_controller->Cascade->getLastInsertID();
			$Data['CascadeNew'] = $this->_controller->Cascade->find('first', array('conditions' => array('Cascade.id' => $Id)));

			$this->_controller->Autorisierung->Logger($Id, $this->_controller->request->data['Cascade']);

			$Data['CascadeCurrentData']['child'] = 0;

			$this->_controller->Cascade->save($Data['CascadeCurrentData']);

			$this->_controller->request->projectvars['VarsArray'][1] = $Id;

			$Data['Url']['ajax']['type'] = 'ajax';
			$Data['Url']['ajax']['container'] = '#container';
			$Data['Url']['ajax']['controller'] = 'cascades';
			$Data['Url']['ajax']['action'] = 'index';
			$Data['Url']['ajax']['terms'] = implode('/', $this->_controller->request->projectvars['VarsArray']);
			$Data['Url']['ajax']['url'] = Router::url(['controller' => 'cascades', 'action' => 'index', $Data['CascadeNew']['Cascade']['topproject_id'],$Data['CascadeNew']['Cascade']['id'],0]);


		} else {

			$Data['error'][] = array(
				'message' => __('New cascade cannot be saved',true),
				'id' => ''
			);

			return $Data;

		}

		return $Data;
	}

	protected function __SaveCascadeOrder($Data){

		if($this->_controller->request->data['Cascade']['orders'] == 0) return $Data;

		$Insert = array(
			'topproject_id' => $Data['CascadeNew']['Cascade']['topproject_id'],
			'cascade_id' => $Data['CascadeNew']['Cascade']['id'],
			'auftrags_nr' => $Data['CascadeNew']['Cascade']['discription'],
			'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
		);

		$this->_controller->Order->create();

		if($this->_controller->Order->save($Insert)){

			$OrderId = $this->_controller->Order->getLastInsertID();
			$Data['Order'] = $this->_controller->Order->find('first', array('conditions' => array('Order.id' => $OrderId)));

			$Insert = array(
				'order_id' => $OrderId,
				'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
			);

			$this->_controller->OrdersTestingcomp->create();
			$this->_controller->OrdersTestingcomp->save($Insert);

			$Insert = array(
				'order_id' => $OrderId,
				'cascade_id' => $Data['CascadeNew']['Cascade']['id'],
			);

			$this->_controller->CascadesOrder->create();
			$this->_controller->CascadesOrder->save($Insert);

		} else {

			$Data['error'][] = array(
				'message' => __('New order cannot be saved',true),
				'id' => ''
			);

		}

		return $Data;
	}

	protected function __SaveCascadeGroups($Data){

		if(!isset($this->_controller->request->data['CascadeGroup']['CascadeGroup']))  return $Data;
		if(empty($this->_controller->request->data['CascadeGroup']['CascadeGroup']))  return $Data;

		$Data['Url']['modal']['type'] = 'modal';
		$Data['Url']['modal']['container'] = '#dialog';
		$Data['Url']['modal']['controller'] = 'cascades';
		$Data['Url']['modal']['action'] = 'edit';
		$Data['Url']['modal']['terms'] = implode('/', $this->_controller->request->projectvars['VarsArray']);
		$Data['Url']['modal']['url'] = Router::url(['controller' => 'cascades', 'action' => 'edit', $Data['CascadeNew']['Cascade']['topproject_id'],$Data['CascadeNew']['Cascade']['id'],0]);

		return $Data;
	}

	protected function __SaveCascadeOrderModule($Data){

		return $Data;
	}

	public function SaveSupplierByAddOrders($id){

		$Order = $this->_controller->Order->find('first',array('conditions' => array('Order.id' => $id)));

		if(count($Order) == 0) return;

		$this->_controller->loadModel('Supplier');
		$this->_controller->loadModel('CascadeGroup');
		$this->_controller->loadModel('CascadegroupsCascades');

		$Supplier = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.order_id' => $id)));

		if(count($Supplier) > 0) return;

		$cascadeID = $this->_controller->request->cascadeID;
		$projectID = $this->_controller->request->projectID;
		$testingcompID = $this->_controller->Auth->user('testingcomp_id');

		$CascadegroupsCascades = $this->_controller->CascadegroupsCascades->find('first',array('conditions' => array('CascadegroupsCascades.cascade_id' => $cascadeID)));

		if(count($CascadegroupsCascades) == 0) return;

		$lang = $this->_controller->request->lang;

		$CascadeGroup = $this->_controller->CascadeGroup->find('first',array('conditions' => array('CascadeGroup.id' => $CascadegroupsCascades['CascadegroupsCascades']['cascade_group_id'])));

		$Insert = $Order['Order'];

		$Insert['area_of_responsibility'] = 'PMS';
		$Insert['status'] = 0;
		$Insert['deleted'] = 0;
		$Insert['order_id'] = $Insert['id'];
		$Insert['equipment'] = $Insert['auftrags_nr'];
		$Insert['description'] = 'Rohrleitungen';
		$Insert['equipment_typ'] = $CascadeGroup['CascadeGroup'][$lang];

		unset($Insert['id']);
		unset($Insert['created']);
		unset($Insert['modified']);

		$this->_controller->Supplier->create();
		$this->_controller->Supplier->save($Insert);

		return true;
	}

	public function SelectTechicalPlaceDataForAddOrders($data){

		if(Configure::check('CascadeLoadModules') == false) return $data;
		if(Configure::read('CascadeLoadModules') == false) return $data;

		$projectID = $this->_controller->request->projectID;
		$cascadeID = $this->_controller->request->cascadeID;
		$orderID = $this->_controller->request->orderID;

		$this->_controller->loadModel('CascadegroupsCascades');
		$this->_controller->loadModel('Technicalplace');

		$this->_controller->Cascade->recursive = -1;

		$CascadeCurrent = $this->_controller->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));

		if(count($CascadeCurrent) == 0) return $data;

		$CascadegroupsCascades = $this->_controller->CascadegroupsCascades->find('first', array('conditions' => array('CascadegroupsCascades.cascade_id' => $cascadeID)));

		if(count($CascadegroupsCascades) == 0) return $data;

		$Technicalplace = $this->_controller->Technicalplace->find('first', array('conditions' => array('Technicalplace.cascade_id' => $cascadeID)));

		if(count($Technicalplace) == 0) return $data;

		unset($Technicalplace['Technicalplace']['id']);

		$data['Order'] = $Technicalplace['Technicalplace'];
		$data['Order']['auftrags_nr'] = $CascadeCurrent['Cascade']['discription'];

		return $data;
	}

	public function SepardeCascadeCats($data){

		if(Configure::check('CascadeLoadModules') === false) return $data;
		if(Configure::read('CascadeLoadModules') === false) return $data;

		if(!isset($data['child'])) return $data;
		if(count($data['child']) == 0) return $data;
		if($data['child'][0]['Cascade']['orders'] == 0) return $data;

		$locale = $this->_controller->Lang->Discription();

		$this->_controller->loadModel('Order');
		$this->_controller->loadModel('CascadeGroup');
		$this->_controller->loadModel('CascadegroupsCascade');

		$CascadeGroup = $this->_controller->CascadeGroup->find('list',array('fields' => array('id','deu','eng','xml_name','model')));

		$CascadeGroup[0] = __('unassigned',true);

		asort($CascadeGroup);
		$Categories = $CascadeGroup;

		foreach ($CascadeGroup as $key => $value) {
			$CascadeGroup[$key] = array();
		}

		foreach($data['child'] as $key => $value){


			$ChildTest = $this->_controller->Navigation->CascadeGetAllChildren($value['Cascade']['id'],array());

			if(count($ChildTest) == 0){

				$Orders = $this->_controller->Order->find('first',array('conditions' => array('Order.cascade_id' => $value['Cascade']['id'])));
				if(count($Orders) == 0) continue;

			}

			if(count($ChildTest) > 0) continue;

			$ChildCascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.parent' => $value['Cascade']['id'])));
	//		if(count($ChildCascade) == 0) continue;

			$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('first',array('conditions' => array('CascadegroupsCascade.cascade_id' => $value['Cascade']['id'])));

			if(count($CascadegroupsCascade) == 1){
				$CascadeGroup[$CascadegroupsCascade['CascadegroupsCascade']['cascade_group_id']][] = $value;
			} else {
				$CascadeGroup[0][] = $value;
			}

		}

		foreach ($Categories as $key => $value) {
			if(count($CascadeGroup[$key]) == 0) unset($CascadeGroup[$key]);
		}

		if(count($CascadeGroup) == 1) return $data;

		if(count($CascadeGroup) > 0){

			$data['CascadegroupsName'] = $Categories;
			$data['Cascadegroups'] = $CascadeGroup;

		}

		return $data;
	}

	public function SelectCascadeCat($data){

		if(!isset($this->_controller->request->data['cascade_group'])) return $data;

		$CascadeGroupId = $this->_controller->request->data['cascade_group'];

		if(!isset($data['Cascadegroups'][$CascadeGroupId])) return $data;
		if(count($data['Cascadegroups'][$CascadeGroupId]) == 0) return $data;

		unset($data['child']);

		$data['child'] = $data['Cascadegroups'][$CascadeGroupId];

		$this->_controller->set('CascadegroupsName',$data['CascadegroupsName'][$CascadeGroupId]);

		unset($data['CascadegroupsName']);
		unset($data['Cascadegroups']);

		return $data;
	}

	public function DeleteCascadeIncElements(){

		$projectID = $this->_controller->request->projectID;
		$cascadeID = $this->_controller->request->cascadeID;
		$orderID = $this->_controller->request->orderID;

		$CaOptionCascadeId = array('cascade_id' => $cascadeID);

		$this->_controller->loadModel('CascadesOrder');
		$this->_controller->loadModel('CascadegroupsCascades');
		$this->_controller->loadModel('Order');
		$this->_controller->loadModel('Orderfile');
		$this->_controller->loadModel('OrdersTestingcomps');
		$this->_controller->loadModel('OrdersDevelopments');
		$this->_controller->loadModel('OrdersEmailadresses');
		$this->_controller->loadModel('Deliverynumber');
		$this->_controller->loadModel('Supplier');
		$this->_controller->loadModel('Supplierimage');
		$this->_controller->loadModel('Supplierfile');
		$this->_controller->loadModel('Reportnumber');

		$OrderIDs = $this->_controller->Order->find('list',array('conditions' => array('Order.cascade_id' => $CaOptionCascadeId)));
		$OrOptionOrderId = array('order_id' => $OrderIDs);
		$OrOptionId = array('Order.id' => $OrderIDs);

		$this->_controller->Cascade->delete($cascadeID);
		$this->_controller->Order->deleteAll($CaOptionCascadeId, false);
		$this->_controller->CascadesOrder->deleteAll($CaOptionCascadeId, false);
		$this->_controller->CascadegroupsCascades->deleteAll($CaOptionCascadeId, false);
		$this->_controller->OrdersTestingcomps->deleteAll($OrOptionOrderId, false);
		$this->_controller->OrdersDevelopments->deleteAll($OrOptionOrderId, false);
		$this->_controller->OrdersEmailadresses->deleteAll($OrOptionOrderId, false);
		$this->_controller->Orderfile->deleteAll($OrOptionOrderId, false);
		$this->_controller->Deliverynumber->deleteAll($OrOptionOrderId, false);
		$this->_controller->Supplier->deleteAll($OrOptionOrderId, false);

		$this->_controller->Reportnumber->updateAll(
		    array('Reportnumber.delete' => 1),
		    array('Reportnumber.order_id' => $OrderIDs)
		);

		$this->_DeleteCascadeOrderFiles($OrderIDs);

		return true;
	}

	protected function _DeleteCascadeOrderFiles($orders){

		if(!is_array($orders)) return;
		if(count($orders) == 0) return;

		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$delPath = Configure::read('order_folder');


		foreach ($orders as $key => $value) {

			if(empty($value)) continue;
			if($value == 0) continue;

			$folder = new Folder($delPath . $value);

			if ($folder->delete()) {
			} else {
			}
		}
	}
}
