<?php
App::uses('AppController', 'Controller');
/**
 * EquipmentTypes Controller
 *
 * @property EquipmentType $EquipmentType
 */
class ExpeditingsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data','ExpeditingTool','Drops','Image','MessagesTool');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'ajax';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');

		$this->Navigation->ReportVars();

		//		$this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

		$this->loadModel('User');
		$this->loadModel('Testingcomp');
		$this->loadModel('Cascade');
		$this->loadModel('CascadeGroup');
		$this->loadModel('CascadesOrder');
		$this->loadModel('OrdersTestingcomp');
		$this->loadModel('TestingcompsCascade');
		$this->loadModel('CascadegroupsCascade');
		$this->loadModel('Order');
		$this->loadModel('Topproject');
		$this->loadModel('Supplier');
		$this->loadModel('Supplierfile');
		$this->loadModel('Supplierimage');
		$this->loadModel('Roll');
		$this->loadModel('ExpeditingEvent');
		$this->loadModel('ExpeditingType');
		$this->loadModel('ExpeditingsRoll');
		$this->loadModel('Expeditingset');
		$this->loadModel('ExpeditingReportnumber');

		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('shortview');

		// Test ob die aktuelle Funktion per Ajax oder direkt aufgerufen werden soll
		foreach($noAjax as $_noAjax){
			if($_noAjax == $this->request->params['action']){
				$noAjaxIs++;
				break;
			}
		}

		if($noAjaxIs == 0){
			$this->Navigation->ajaxURL();
		}

		$this->Lang->Choice();
		$this->Lang->Change();

		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->set('SettingsArray',array());

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterFilter() {
		$this->Navigation->lastURL();
	}

/**
 * index method *
 * @return void
 */

 	public function start() {

		$this->layout = 'blank';

		$SettingsStartArray = array();

//		$SettingsStartArray['rkls'] = array('discription' => __('Tube class catalog',true), 'controller' => 'Rkls','action' => 'index', 'terms' => null);
//		$SettingsStartArray['rklc'] = array('discription' => __('Tube classes',true), 'controller' => 'Rkls','action' => 'classes', 'terms' => null);
//		$SettingsStartArray['repairs'] = array('discription' => __('Repairs',true), 'controller' => 'expeditings','action' => 'landing', 'terms' => 0);
		$SettingsStartArray['news'] = array('discription' => __('New Manufacturing',true), 'controller' => 'expeditings','action' => 'landing', 'terms' => 1);

		$this->set('SettingsStartArray',$SettingsStartArray);

	}

	public function addexpeditingobject() {

		if($this->request->projectvars['VarsArray'][0] > 0) $Data = $this->_addexpeditingobjectnew();
		if($this->request->projectvars['VarsArray'][0] == 0) $Data = $this->_addexpeditingobjectrep();

	}

	protected function _addexpeditingobjectnew(){

		$this->_addexpeditingobjectnew1();
		$this->_addexpeditingobjectnew2();

	}

	protected function _addexpeditingobjectnew1(){

		if($this->request->projectvars['VarsArray'][0] != 1) return;
		if(!empty($this->request->projectvars['VarsArray'][1])) return;

		if(!empty($this->request->data['landig_page_large'])) $landig_page_large = 1;

		if(!empty($landig_page_large)) $this->layout = 'blank';
		else $this->layout = 'modal';

		$Data = $this->ExpeditingTool->AddNewExpeditingObject();

		$this->request->data = $Data;

		$this->Flash->hint(__('Wo soll das neue Expediting angelegt werden?',true),array('key' => 'hint'));
		if(!empty($landig_page_large)) $this->render('addexpeditingobject');
		else $this->render('addexpeditingobjectmodal');

		return $Data;

	}

	protected function _addexpeditingobjectnew2(){

		if(empty($this->request->projectvars['VarsArray'][0])) return;
		if(empty($this->request->projectvars['VarsArray'][1])) return;

		if(isset($this->request->data['landig_page_large'])) $landig_page_large = 1;

		if(isset($landig_page_large)) $this->layout = 'blank';
		else $this->layout = 'modal';

		$topproject_id = intval($this->request->projectvars['VarsArray'][0]);
		$casade_id = intval($this->request->projectvars['VarsArray'][1]);

		$Data = $this->_saveexpeditingobjectnew1();

		if(!empty($Data)){

			if(isset($landig_page_large)){
				$this->render('addexpeditingobjectname');
			} else {

				$this->Session->write('OpenSupplierEdit',1);
   				$this->render('addexpeditingobjectnamemodal');
			}

			return;
		}

		$this->request->data['Expediting']['topproject_id'] = $topproject_id;
		$this->request->data['Expediting']['cascade_id'] = $casade_id;

		$data = $this->ExpeditingTool->GetCurrentCascade($casade_id);

		$Message = '';

		foreach ($data as $key => $value) {
			$Message .= $value['Cascade']['discription'] . ' ';
		}

		$this->Flash->hint(__('Add technical place to',true) . ' ' . $Message,array('key' => 'hint'));

		if(isset($landig_page_large)) $this->render('addexpeditingobjectname');
		else $this->render('addexpeditingobjectnamemodal');

	}

	protected function _saveexpeditingobjectnew1(){

		if(!isset($this->request->data['Expediting'])) return;

		$topproject_id = intval($this->request->projectvars['VarsArray'][0]);
		$casade_id = intval($this->request->projectvars['VarsArray'][1]);
		$testingcomp_id = $this->Auth->user('testingcomp_id');

		if(empty($this->request->data['Expediting']['description'])){
			$this->Flash->error(__('Enter a name for the expediting',true),array('key' => 'error'));
			return;
		}

		$description = $this->request->data['Expediting']['description'];

		$this->Cascade->recursive = -1;

		$CascadesCheck = $this->Cascade->find('all',array(
				'conditions' => array(
					'Cascade.parent' => $casade_id,
					'Cascade.discription' => $description,
				)
			)
		);

		if(!empty($CascadesCheck)){
			$this->Flash->error(__('The following name already exists',true) . ' "' . $description . '"',array('key' => 'error'));
			return;
		}

		$Cascade = $this->ExpeditingTool->GetCurrentCascade($casade_id);
		$Cascade = end($Cascade);

		$CascadesCheck = $this->Cascade->find('first',array(
				'conditions' => array(
					'Cascade.parent' => $Cascade['Cascade']['id'],
				)
			)
		);

		$InsertCascade['topproject_id'] = $topproject_id;
		$InsertCascade['discription'] = trim($description);
		$InsertCascade['status'] = 1;
		$InsertCascade['level'] = ++$Cascade['Cascade']['level'];
		$InsertCascade['parent'] = $Cascade['Cascade']['id'];
		$InsertCascade['orders'] = 1;
		$InsertCascade['child'] = 0;

		$this->Cascade->create();

		$Test = $this->Cascade->save($InsertCascade);

		if($Test === false){
			$this->Flash->error(__('The value could not be saved.',true),array('key' => 'error'));
			return;
		}

		$CascadeId = $this->Cascade->getLastInsertID();

		$InsertTestingcompsCascade['cascade_id'] = $CascadeId;
		$InsertTestingcompsCascade['testingcomp_id'] = $testingcomp_id;

		$this->TestingcompsCascade->create();

		$Test = $this->TestingcompsCascade->save($InsertTestingcompsCascade);

		if($Test === false){
			$this->Flash->error(__('The value could not be saved.',true),array('key' => 'error'));
			return;
		}

		$Data = array();

		$Data['Cascade'] = $this->Cascade->find('first',array(
				'conditions' => array(
					'Cascade.id' => $CascadeId,
				)
			)
		);

		$InsertOrder['topproject_id'] = $topproject_id;
		$InsertOrder['cascade_id'] = $CascadeId;
		$InsertOrder['auftrags_nr'] = $description;
		$InsertOrder['testingcomp_id'] = $testingcomp_id;

		$this->Order->create();

		$Test = $this->Order->save($InsertOrder);

		if($Test === false){
			$this->Flash->error(__('The value could not be saved.',true),array('key' => 'error'));
			return;
		}

		$OrderId = $this->Order->getLastInsertID();

		$InsertCascadesOrder['cascade_id'] = $CascadeId;
		$InsertCascadesOrder['order_id'] = $OrderId;

		$this->CascadesOrder->create();

		$Test = $this->CascadesOrder->save($InsertCascadesOrder);

		if($Test === false){
			$this->Flash->error(__('The value could not be saved.',true),array('key' => 'error'));
			return;
		}

		$InsertOrdersTestingcomp['testingcomp_id'] = $testingcomp_id;
		$InsertOrdersTestingcomp['order_id'] = $OrderId;

		$this->OrdersTestingcomp->create();

		$Test = $this->OrdersTestingcomp->save($InsertOrdersTestingcomp);

		if($Test === false){
			$this->Flash->error(__('The value could not be saved.',true),array('key' => 'error'));
			return;
		}

		$Data['Order'] = $this->Order->find('first',array(
				'conditions' => array(
					'Order.id' => $OrderId,
				)
			)
		);

		$CurrentCascade = $this->ExpeditingTool->GetCurrentCascade($CascadeId);

		$Unit = '';

		foreach ($CurrentCascade as $key => $value) {
			$Unit .= $value['Cascade']['discription'] . ' ';
		}

		$InsertSupplier['topproject_id'] = $topproject_id;
		$InsertSupplier['cascade_id'] = $CascadeId;
		$InsertSupplier['order_id'] = $OrderId;
		$InsertSupplier['testingcomp_id'] = $testingcomp_id;
		$InsertSupplier['unit'] = trim($Unit);
		$InsertSupplier['equipment'] = $description;
		$InsertSupplier['new_or_rep'] = 1;

		$this->Supplier->create();

		$Test = $this->Supplier->save($InsertSupplier);

		if($Test === false){
			$this->Flash->error(__('The value could not be saved.',true),array('key' => 'error'));
			return;
		}

		$SupplierId = $this->Supplier->getLastInsertID();

		$Data['Supplier'] = $this->Supplier->find('first',array(
				'conditions' => array(
					'Supplier.id' => $SupplierId,
				)
			)
		);

		$url = Router::url(array('controller' => 'suppliers', 'action' => 'overview', $topproject_id, $CascadeId, $SupplierId));

		$this->set('RequestUrl',$url);

		return $Data;

	}

	protected function _addexpeditingobjectrep(){

		if($this->request->projectvars['VarsArray'][0] != 0) return;

		$Data = $this->ExpeditingTool->FindNotAssignedOrders();

		return $Data;

	}

	public function landing($id) {

		$this->layout = 'blank';

		App::uses('CakeTime', 'Utility');

		if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $landig_page = 1;
		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		$this->Navigation->GetSessionForPaging();

		$Response = array();

		$Response['Xml'] = $this->Xml->DatafromXml('display_expediting','file',null);

		if(!is_object($Response['Xml']['settings'])){
			$Response['error']['message'] = __('Settings cant be loadet.',true);
			return;
		}

		$Response['PriorityStatus'] = array('critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);
		$Response['Priority'] = array(1 => 'icon_critical',2 => 'icon_delayed',3 => 'icon_plan',4 => 'icon_future',5 => 'icon_finished');
		$Response['StatusOptionForBread'] = array_flip($Response['PriorityStatus']);
		$Response['StatusOption'] = array(1 => __('critical',true),2 => __('delayed',true),3 => __('plan',true),4 => __('future',true),5 => __('finished',true));
		$Response['PriorityText'] = array(
						'critical' => __('Critical (soll date > current date + waiting period)',true),
						'delayed' => __('Delayed (soll date > current date and <  current date + waiting period)',true),
						'plan' => __('In plan (soll date < current date)',true),
						'future' => __('No date informations stored',true),
						'finished' => __('Completed',true)
		);

		foreach($Response['PriorityStatus'] as $_key => $_data){
			unset($Response['PriorityStatus'][$_key]);
			$Response['PriorityStatus'][$_key]['count'] = 0;
			$Response['PriorityStatus'][$_key]['text'] = $Response['PriorityText'][$_key];
		}

		$ExpeditingNavi = array();
		$SettingsArray = array();

		$Response['AllCascadesUnder'] = $this->Navigation->CascadeGetAllChildren(intval($cascadeID),array());
		$Response['AllCascadesUnder'] = $this->ExpeditingTool->SelectExpeditingFilter($Response['AllCascadesUnder']);
		$Response['ChildrenList'] = $this->Navigation->CascadeGetNextChildren(intval($cascadeID),array('id','discription'));
		$Response['CascadeForBread'] = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$Response['ParentCasace'] = $this->Navigation->CascadeGetParentId(intval($cascadeID));
		$Response['SelectOrderByTestingcomp'] = $this->ExpeditingTool->SelectOrderByTestingcomp();

		$options['Supplier.deleted'] = 0;
		$options['Supplier.new_or_rep'] = $id;

		if(count($Response['SelectOrderByTestingcomp']) > 0){
			$options['Supplier.order_id'] = $Response['SelectOrderByTestingcomp'];
		} else {
			$options['Supplier.order_id'] = 0;
		}

		$this->paginate = array(
			'conditions' => array($options),
			'order' => array('unit' => 'asc','equipment' => 'asc'),
			'limit' => 500
		);

		$Response['Supplier'] = $this->paginate('Supplier');

		$Response = $this->ExpeditingTool->MergeExpeditingsToSuppliers($Response);

		$this->request->data = $Response;

	}



	public function index() {

		$this->layout = 'ajax';
		$SettingsArray = array();

		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

		$breads[] = array(
						'discription' => __('Expediting manager', true),
						'controller' => 'expeditings',
						'action' => 'index',
						'pass' => null
		);

		$options =array('fields' => array('topproject_id DISTINCT'));
		$Suppliers = $this->Supplier->find('all', $options);
		$Suppliers = Hash::extract($Suppliers, '{n}.Supplier.topproject_id');

		$this->Topproject->recursive = -1;
		$this->Cascade->recursive = -1;

		$Topprojects = $this->Topproject->find('all',array('conditions' => array('id' => $Suppliers),'order' => array('projektname')));

		foreach ($Topprojects as $key => $value) {

			$options =array(
				'conditions' => array(
					'Cascade.topproject_id' => $value['Topproject']['id'],
					'Cascade.level' => 0,
				)
			);

			$Cascade = $this->Cascade->find('first', $options);

			$Topprojects[$key]['Cascade'] = $Cascade['Cascade'];

		}

		$this->request->data['Testingcomp']['selected'] = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
		$this->request->data['Topproject']['selected'] = array();
		$this->request->data['Cascade']['selected'] = array();

		$this->set('Topprojects', $Topprojects);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);

		if(isset($landig_page_large) && $landig_page_large == 1){

			$this->render('landingpage_large','blank');
			return;

		}
	}

	public function shortview() {
		if(!isset($this->request->data['type'])) $this->_shortexpediting();
		elseif(isset($this->request->data['type'])){
			if($this->request->data['type'] == 1) $this->_shortexpediting();
			if($this->request->data['type'] == 2) $this->_shortfiles();
		}
	}

	protected function _shortfiles() {

		$this->layout = 'blank';

		$this->loadModel('Supplier');
		$this->loadModel('Supplierfile');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		$this->Autorisierung->ConditionsCasdadeTest($cascadeID);
		$this->Autorisierung->ConditionsExpeditingTest($supplierID);

		$Supplier = $this->Supplier->find('first',array('fields'=>array('id'),'conditions'=>array('Supplier.id'=>$supplierID)));
		$Orderfile = $this->Supplierfile->find('all',array('conditions'=>array('Supplierfile.supplier_id'=>$Supplier['Supplier']['id'])));

		foreach($Orderfile as $_key => $_Orderfile){
			$savePath = Configure::read('supplier_folder') . $_Orderfile['Supplierfile']['supplier_id'] . DS . $_Orderfile['Supplierfile']['file'];
			if(file_exists($savePath) == true){
				$Orderfile[$_key]['Supplierfile']['file_exists'] = true;
			} else {
				$Orderfile[$_key]['Supplierfile']['file_exists'] = false;
			}
		}

		$this->set('Data',$Orderfile);

		$this->render('shortfiles');
	}

	protected function _shortexpediting() {

		App::uses('CakeTime', 'Utility');
		$this->layout = 'blank';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$expeditingID = $this->request->projectvars['VarsArray'][2];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		$this->Autorisierung->ConditionsCasdadeTest($cascadeID);
		$this->Autorisierung->ConditionsExpeditingTest($expeditingID);


		$Expeditings = $this->Expediting->find('all',array(
			'order' => array('Expediting.sequence ASC'),
			'conditions' => array(
				'Expediting.deleted' => 0,
				'Expediting.supplier_id' => $expeditingID
				)
			)
		);

		if(count($Expeditings) == 0){
			$this->set('Message',__('For this equipment exists no epediting data.'));
			return;
		}

		foreach($Expeditings as $_key => $_Expeditings) $Expeditings[$_key] = $this->ExpeditingTool->ExpeditingTimePeriode($Expeditings,$_Expeditings,$_key);

		$this->set('Expeditings',$Expeditings);

		$this->render('shortexpediting');
	}

	public function addexpediting() {

		$this->layout = 'modal';

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray',$SettingsArray);

		if(isset($this->request->data['ExpeditingType'])){

			$this->ExpeditingType->create();
			if($this->ExpeditingType->save($this->request->data['ExpeditingType'])){

				$this->Flash->success(__('Value has been saved.',true),array('key' => 'success'));

				$this->set('FormName',$this->request->data['ExpeditingType']['UrlLoadLastPage']);


			} else {

				$this->Flash->error(__('Value could not be saved .',true),array('key' => 'error'));

			}
		}
	}

	public function editexpediting() {

		$this->__editexpediting();
		$this->__editexpeditingfast();


	}

	public function __editexpediting() {

		if(isset($this->request->data['json_true'])) return;

		$this->layout = 'modal';
//mps/expeditings/addexpediting/13/113/33/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'expeditings','action' => 'addexpediting', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray',$SettingsArray);

		$ExpeditingType = $this->ExpeditingType->find('all',array('conditions' => array('ExpeditingType.deleted' => 0)));

		foreach ($ExpeditingType as $key => $value) {

			$Expediting = $this->Expediting->find('first',array(
				'conditions' => array(
					'Expediting.deleted' => 0,
					'Expediting.expediting_type_id' => $value['ExpeditingType']['id']
					)
				)
			);

			if(count($Expediting) == 0) {
				$ExpeditingType[$key]['ExpeditingType']['delete_okay'] = 1;
			} else {
				$ExpeditingType[$key]['ExpeditingType']['delete_okay'] = 0;
			}
		}

		$this->request->data['ExpeditingType'] = $ExpeditingType;

	}

	public function __editexpeditingfast() {

		if(!isset($this->request->data['json_true'])) return;
		if(($this->request->data['json_true']) != 1) return;

		 $this->layout = 'json';

		 if(isset($this->request->data['ExpeditingType'])){

			 $Request['data'] = $this->request->data['ExpeditingType'];

			 if($this->ExpeditingType->save($this->request->data['ExpeditingType'])){

				 $Request['value'] = 'Success';
				 $Request['data']['class'] = 'finished';

				 $this->set('request',json_encode($Request));

			 } else {

				 $Request['value'] = 'Error';
				 $this->set('request',json_encode($Request));

			 }
		 }

		 $this->render('editexpediting_json');

		 return;

	}

	public function expeditingevents() {

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectExpeditingEventsSingleModal();

		$this->request->data = $Supplier;

	}

	public function add() {

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->ExpeditingAddStep();
		$Supplier = $this->ExpeditingTool->CollectSupplierDataExpeditingAdd();
		$Roll = $this->Roll->find('list');

		$Supplier['Expediting']['hold_witness_point'] = 0;

		$this->request->data = $Supplier;

		$this->set(compact('Roll'));

		$VarsArray = $this->request->projectvars['VarsArray'];

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'indextemplate', 'terms' => $VarsArray);

		$this->set('SettingsArray',$SettingsArray);

	}

	public function deleteexpeditings(){

		
		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->ExpeditingAddStep();
		$Supplier = $this->ExpeditingTool->CollectSupplierData();
		$Supplier = $this->ExpeditingTool->CreateTempateForm($Supplier);
		$Supplier = $this->ExpeditingTool->ExpedtigintDeleteAll($Supplier);

		$this->request->data = $Supplier;

	} 

	public function createtemplate() {

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectSupplierData();

	//	$Supplier = $this->ExpeditingTool->EditExpeditingset($Supplier);
		$Supplier = $this->ExpeditingTool->CreateTempateDataShow($Supplier);

		unset($Supplier['Expeditingset']);
		unset($Supplier['ExpeditingSetsExpeditingType']);

		$this->request->data = $Supplier;

		$VarsArray = $this->request->projectvars['VarsArray'];

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'indextemplate', 'terms' => $VarsArray);

		$this->set('SettingsArray',$SettingsArray);

	}

	public function indextemplate() {

		$this->layout = 'modal';

		$Expeditingset = $this->Expeditingset->find('all',array(
			'conditions' => array(
					//TODO: Options für Benutzerberechtigung einfügen
				)
			)
		);

		$VarsArray = $this->request->projectvars['VarsArray'];
		$SettingsArray = array();

		$this->set('SettingsArray',$SettingsArray);
		$this->set('Expeditingset',$Expeditingset);

	}

	public function deltemplate() {

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectExpeditingset();
		$Supplier = $this->ExpeditingTool->EditTempateDataShow($Supplier);
		$Supplier = $this->ExpeditingTool->DeleteTemplate($Supplier);

		$this->request->data = $Supplier;

		$VarsArray = $this->request->projectvars['VarsArray'];

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'indextemplate', 'terms' => $VarsArray);

		$this->set('SettingsArray',$SettingsArray);

		$this->Flash->warning(__('Delete') .' ' . __('ITP') . ' ' . $Supplier['Expeditingset']['name'],array('key' => 'warning'));	


	}

	public function edittemplate() {
		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectExpeditingset();
		$Supplier = $this->ExpeditingTool->EditExpeditingset($Supplier);
		$Supplier = $this->ExpeditingTool->EditTempateDataShow($Supplier);

		$this->request->data = $Supplier;

		$VarsArray = $this->request->projectvars['VarsArray'];

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'indextemplate', 'terms' => $VarsArray);

		$this->set('SettingsArray',$SettingsArray);

	}

	public function addtemplate() {

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectSupplierData();
		$Supplier = $this->ExpeditingTool->CreateTempateData($Supplier);
		$Supplier = $this->ExpeditingTool->CreateTempateForm($Supplier);
		$Supplier = $this->ExpeditingTool->CreateTempateSave($Supplier);
		
		$this->request->data = $Supplier;

		$VarsArray = $this->request->projectvars['VarsArray'];
		$SettingsArray = array();
		unset($VarsArray[3]);
		$SettingsArray['showindex'] = array('discription' => __('Show Templates',true), 'controller' => 'expeditings','action' => 'indextemplate', 'terms' => $VarsArray);

		$this->set('SettingsArray',$SettingsArray);

	}

	public function addexpeditingstep(){

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectExpeditingset();
		$Supplier = $this->ExpeditingTool->AddExpeditingStepData($Supplier);
		$Supplier = $this->ExpeditingTool->RemoveUsedExpeditingtyps($Supplier);

		$VarsArray = $this->request->projectvars['VarsArray'];
		$VarsArray[4] = 0;

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'edittemplate', 'terms' => $VarsArray);

		if(isset($this->request->data['ExpeditingSetsExpeditingType'])){

		} else {

			$this->Flash->hint(__('Template') . ' ' . $Supplier['Expeditingset']['name'],array('key' => 'hint'));

			$priode_date = array('' => '','order_date' => __('order_date'),'delivery_date' => __('delivery_date'));
			$priode_sign = array('' => '','+' => __('Plus'),'-' => __('Minus'));
			$priode_measure = array('' => '','day' => __('days'),'week' => __('weeks'),'month' => __('months'));
	
			$this->set('SettingsArray',$SettingsArray);
			$this->set('Rolls',$Supplier['Rolls']);
			$this->set(compact('priode_date','priode_sign','priode_measure'));	

		}

		if(count($Supplier['ExpeditingTypes']) == 0){
			$this->Flash->warning(__('There are no more expediting steps available.'),array('key' => 'warning'));
		}

		$this->request->data = $Supplier;

	}

	public function editexpeditingstep(){

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectCurrentExpeditingset();
		$Supplier = $this->ExpeditingTool->EditExpeditingStepData($Supplier);
		$Supplier = $this->ExpeditingTool->EditExpeditingStepDataShow($Supplier);

		$SettingsArray = array();
		$VarsArray = $this->request->projectvars['VarsArray'];
		$SettingsArray['dellink'] = array('discription' => __('Delete Expediting Step',true), 'controller' => 'expeditings','action' => 'deleteexpeditingstep', 'terms' => $VarsArray);
		$VarsArray[4] = 0;
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'edittemplate', 'terms' => $VarsArray);

		if(isset($this->request->data['ExpeditingSetsExpeditingType'])){


		} else {

			$this->Flash->hint(__('Template') . ' ' . $Supplier['Expeditingset']['name'] . ' > ' . $Supplier['ExpeditingSetsExpeditingType']['description'],array('key' => 'hint'));

			$priode_date = array('' => '','order_date' => __('order_date'),'delivery_date' => __('delivery_date'));
			$priode_sign = array('' => '','+' => __('Plus'),'-' => __('Minus'));
			$priode_measure = array('' => '','day' => __('days'),'week' => __('weeks'),'month' => __('months'));
	
			$this->set('SettingsArray',$SettingsArray);
			$this->set('Rolls',$Supplier['Rolls']);
			$this->set(compact('priode_date','priode_sign','priode_measure'));	

		}

		$this->request->data = $Supplier;

	}

	public function deleteexpeditingstep(){

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectCurrentExpeditingset();
		$Supplier = $this->ExpeditingTool->DeleteExpeditingStepData($Supplier);
		$Supplier = $this->ExpeditingTool->EditExpeditingStepDataShow($Supplier);

		$VarsArray = $this->request->projectvars['VarsArray'];
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'editexpeditingstep', 'terms' => $VarsArray);

		if(isset($this->request->data['ExpeditingSetsExpeditingType'])){


		} else {

			$this->Flash->warning(__('Delete') .' ' . __('Template') . ' ' . $Supplier['Expeditingset']['name'] . ' > ' . $Supplier['ExpeditingSetsExpeditingType']['description'],array('key' => 'warning'));	
			$this->set('SettingsArray',$SettingsArray);

		}

		$this->request->data = $Supplier;

	}

	public function detail() {

		$this->__detail();
		$this->__detailjson();

	}

	protected function __detail() {

		if(isset($this->request->data['json_true'])) return;

		App::uses('CakeTime', 'Utility');
	 	$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
		$ExpeditingId = $this->request->projectvars['VarsArray'][3];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		$this->Autorisierung->ConditionsCasdadeTest($cascadeID);
		$this->Autorisierung->ConditionsExpeditingTest($supplierID);

		$Supplier = $this->ExpeditingTool->CollectExpeditingEventsSingleModal();
		$SupplierBefore = $Supplier;
		$Supplier = $this->ExpeditingTool->SaveSupplierDataExpeditingEditDetail($Supplier);
		$Supplier = $this->ExpeditingTool->SendMessageAfterEdit($Supplier,$SupplierBefore);
		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		$this->request->data = $Supplier;

	}

	protected function __detailjson() {

		if(!isset($this->request->data['json_true'])) return;

		App::uses('CakeTime', 'Utility');
	 	$this->layout = 'json';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
		$ExpeditingTypeId = $this->request->projectvars['VarsArray'][3];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		$this->Autorisierung->ConditionsCasdadeTest($cascadeID);
		$this->Autorisierung->ConditionsExpeditingTest($supplierID);

		$Supplier = $this->ExpeditingTool->CollectExpeditingEventsSingleModal();

//		$SupplierBefore = $Supplier;

		$Supplier = $this->ExpeditingTool->SaveSupplierDataExpeditingEditDetail($Supplier);
//		$this->ExpeditingTool->SendMessageAfterEdit($Supplier,$SupplierBefore);

		$this->set('request',json_encode($Supplier));

		$this->render('detailjson');
		return;

	}

	public function expeditingstepinfo(){

		$this->layout = 'json';

		$Supplier = $this->ExpeditingTool->CollectExpeditingEventsSingleModal();

		$this->set('request',json_encode($Supplier));

	}

	public function edit() {

		// Hier muss noch eine Überprüfung rein
		$this->__editfast();
		$this->__edit();


	}

	public function __editfast() {

		if(!isset($this->request->data['json_true'])) return;
		if(($this->request->data['json_true']) != 1) return;

		 $this->layout = 'json';

		 $SupplierBefore = $this->ExpeditingTool->CollectExpeditingEventsSingleModal();
		 $SupplierBefore = $this->ExpeditingTool->CollectMailsendInfos($SupplierBefore);
		 
		 $this->ExpeditingTool->SaveSupplierDataFast();

		 $Supplier = $this->ExpeditingTool->CollectExpeditingEventsSingleModal();
		 $Supplier = $this->ExpeditingTool->CollectMailsendInfos($Supplier);

		 $this->ExpeditingTool->SendMessageAfterEdit($Supplier,$SupplierBefore);

		 $this->render('edit_json');

		 return;

	}

	public function __edit() {

		if(isset($this->request->data['json_true'])) return;

		$this->layout = 'modal';

		if(isset($this->request->projectvars['VarsArray'][4])) $ExpeditingType = $this->request->projectvars['VarsArray'][4];


		$Supplier = $this->ExpeditingTool->CollectSupplierDataSingleModal('image');

		if(isset($this->request->data['Supplierimage'])){

			if($this->Supplierimage->save($this->request->data['Supplierimage'])){

				$this->Flash->success(__('Value has been saved.',true),array('key' => 'success'));

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'overview';
				$Terms = implode('/',$this->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->set('FormName',$FormName);

			} else {

				$this->Flash->error(__('Value could not be saved .',true),array('key' => 'error'));

			}
		}

		$this->request->data = $Supplier;
		if(isset($ExpeditingType)) $this->request->data['Expediting']['expediting_type'] = $ExpeditingType;

		return;
	}

	public function sendmail() {
	 	$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectSupplierDataSingleModal('file');
		$Supplier = $this->ExpeditingTool->SendFilesPerMail($Supplier);
		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		$this->request->data = $Supplier;

	}

	public function delmanyfiles(){

		$this->layout = 'modal';

		if(isset($this->request->data['Expediting']['files'])) $Files = $this->request->data['Expediting']['files'];
		if(isset($this->request->data['Suppliers']['files'])) $Files = $this->request->data['Suppliers']['files'];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataSingleModal('file');
		$Supplier = $this->ExpeditingTool->ExpeditingFileDelete($Supplier);
		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		unset($Supplier['Expediting']);

		$this->request->data = $Supplier;
		$this->request->data['Expediting']['files'] = $Files;

	}

	public function sendmanyfiles(){

		$this->layout = 'modal';

		if(isset($this->request->data['Expediting']['files'])) $Files = $this->request->data['Expediting']['files'];
		if(isset($this->request->data['Suppliers']['files'])) $Files = $this->request->data['Suppliers']['files'];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataSingleModal('file');
	//	$out = $this->ExpeditingTool->CollectFiles();
		$Supplier = $this->ExpeditingTool->SendManyFilesPerMail($Supplier);
		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		unset($Supplier['Expediting']);

		$this->request->data = $Supplier;
		$this->request->data['Expediting']['files'] = $Files;

	}

	public function getmanyfiles(){

		$this->layout = 'json';

		$this->ExpeditingTool->SendFiles();
		$out = $this->ExpeditingTool->CollectFiles();

		$this->set('request',json_encode(array('message' => $out)));

	}

	public function delete() {

	 	$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectSupplierDataExpeditingEdit();

		if(isset($this->request->data['Expediting'])){

			$this->request->data['Expediting']['deleted'] = 1;
//			$this->request->data['Expediting']['deleted'] = 0;

			if($this->Expediting->save($this->request->data['Expediting'])){

				$this->Flash->success(__('Value has been deleted.',true),array('key' => 'success'));

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'overview';
				$Terms = implode('/',$this->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->set('FormName',$FormName);

			} else {

				$this->Flash->error(__('Value could not be deleted .',true),array('key' => 'error'));

			}
		} else {

			$this->request->data = $Supplier;

			$this->Flash->warning(__('Should the expediting step "' . $Supplier['Expediting']['description'] . '" be deleted?',true),array('key' => 'warning'));

		}
	}

	public function sortevent() {

		$this->layout = 'json';

		$Supplier = $this->ExpeditingTool->SortExpeditings();

		$this->set('response',json_encode($Supplier));


	}

	public function addevent() {

		$this->layout = 'modal';

		$Supplier = $this->ExpeditingTool->CollectSupplierData();
		$Supplier = $this->ExpeditingTool->CollectUnusedExpeditingSteps($Supplier);
		$Supplier = $this->ExpeditingTool->AddUnusedExpeditingSteps($Supplier);

		$this->request->data = $Supplier;

		$priode_date = array('' => '','order_date' => __('order_date'),'delivery_date' => __('delivery_date'));
		$priode_sign = array('' => '','+' => __('Plus'),'-' => __('Minus'));
		$priode_measure = array('' => '','day' => __('days'),'week' => __('weeks'),'month' => __('months'));

		$this->set('SettingsArray',array());
		$this->set('Rolls',$Supplier['Rolls']);
		$this->set(compact('priode_date','priode_sign','priode_measure'));	

	}

	public function showreports() {
		$this->layout = 'modal';

		$expeditingID = $this->request->projectvars['VarsArray'][3];
		$reportnumbers= array();
		$reportnumberids = array();
		$reports = $this->ExpeditingReportnumber->find('list',array('fields'=>array( 'reportnumber_id','expediting_id'),'conditions' => array('ExpeditingReportnumber.expediting_id' => $expeditingID)));
		foreach ($reports as $rkey => $rvalue) {
				$reportnumberids [] = $rkey;
		}

		$this->loadModel('Reportnumber');
//		$this->Reportnumber->recursive = -1;
		$reportnumbers = $this->Reportnumber->find('all',array('conditions'=> array('Reportnumber.id'=>$reportnumberids)));

		foreach ($reportnumbers as $key => $value) {

			$Testingmethod = ucfirst($value['Testingmethod']['value']);
			$ReportGenerally = 'Report'.$Testingmethod.'Generally';
			$ReportSpecific = 'Report'.$Testingmethod.'Specific';

			$this->loadModel($ReportGenerally);
			$this->loadModel($ReportSpecific);
			$GenerallyData =  $this->$ReportGenerally->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'])));
			$SpecificData = 	$this->$ReportSpecific->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'])));

			$reportnumbers[$key][$ReportGenerally] = $GenerallyData[$ReportGenerally];
			$reportnumbers[$key][$ReportSpecific] = $SpecificData[$ReportSpecific];
		}

		$this->set('reportnumbers', $reportnumbers);
	}
}
