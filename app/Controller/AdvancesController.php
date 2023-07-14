<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

/**Test
 *
 *
 * @property Advance $Advance
 */
class AdvancesController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Drops','AdvanceTool');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'modal';
	protected $writeprotection = false;

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		// Es wird das in der Auswahl gewählte Project in einer Session gespeichert

		$this->Navigation->ReportVars();

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
		$this->loadModel('AdvancesCascade');
		$this->loadModel('AdvancesCascadesTestingcomp');
		$this->loadModel('AdvancesCascadesUser');
		$this->loadModel('AdvancesOrder');
		$this->loadModel('AdvancesData');
		$this->loadModel('AdvancesTestingcomp');
		$this->loadModel('AdvancesDataDependency');
		$this->loadModel('AdvancesDataReport');
		$this->loadModel('AdvancesType');
		$this->loadModel('AdvancesHistory');
		$this->loadModel('AdvancesTopproject');

		$AdvancesType = $this->AdvancesType->find('list',array('order' => array('ndt','type'),'fields' => array('id','type')));

		$this->advance_type = $AdvancesType;

//		$this->set('AdvancesType' => $AdvancesType);

		$lastmodalURL = explode('/',$this->Session->read('lastmodalURL'));

		$noAjaxIs = 0;
		$noAjax = array('checklist','printstatistic');

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

		$this->set('roll', $this->Auth->user('roll_id'));
		$this->set('lang', $this->Lang->Choice());
		$this->set('select_lang', Configure::read('Config.language'));
		$this->set('selected', $this->Lang->Selected());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterRender() {
		$last = $this->Session->read();
		// Bei anderen Actions im Dropdown controller nicht mitschreiben
		if($last['lastmodalArrayURL']['controller'] != $this->request->controller) $this->Navigation->lastURL();
	}

/**
 * index method
 *
 * @return void
 */

	public function master() {

		$this->Autorisierung->Protect();

		$testingmethods = $this->Testingmethods->find('list',array('fields' => array('id','verfahren'),'order' => array('verfahren')));
		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$reports = $this->Report->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$DropdownsMaster = $this->DropdownsMaster->find('all', array('conditions' => array('DropdownsMaster.deleted' => 0)), array('order' => array('name asc')));
//		$breads = $this->Navigation->Breads(null);

		$breads[] = array(
						'discription' => __('Dropdown manager', true),
						'controller' => 'dropdowns',
						'action' => 'master',
						'pass' => null
		);

		$this->set('DropdownsMaster', $DropdownsMaster);
		$this->set('breads', $breads);
		$this->set('SettingsArray', array());

	}

	public function index() {

		$this->Autorisierung->Protect();

		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

		$this->layout = 'ajax';
		$SettingsArray = array();

	//	$breads = $this->Navigation->Breads(null);

		$breads[] = array(
						'discription' => __('Advance manager', true),
						'controller' => 'advances',
						'action' => 'index',
						'pass' => null
		);

		$Advance = $this->Advance->find('all',array('order' => array('name asc')));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$this->request->data['Testingcomp']['selected'] = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
		$this->request->data['Topproject']['selected'] = array();
		$this->request->data['Cascade']['selected'] = array();

		$this->set(compact('testingcomps','topprojects'));
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('Advance', $Advance);

		if(isset($landig_page_large) && $landig_page_large == 1){

			$this->render('landingpage_large','blank');
			return;

		}
	}

	public function add() {

		$this->Autorisierung->Protect();

		$this->layout = 'modal';
		$SettingsArray = array();

		if (isset($this->request->data['Advance']) || $this->request->is('put')) {

			$this->request->data['Advance']['user_id'] = $this->Auth->user('id');

			$this->Advance->create();

			if ($this->Advance->save($this->request->data)) {

				$id = $this->Advance->getInsertID();

				$this->request->data['Advance']['id'] = $id;

				$this->request->projectvars['VarsArray'][0] = $id;

				$this->Autorisierung->Logger($this->request->data['Advance']['id'], $this->request->data);

				$FormName = array();
				$FormName['controller'] = 'advances';
				$FormName['action'] = 'edit';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('FormName', $FormName);

				$this->Session->setFlash(__('The entry has been saved'));
			} else {
				$this->Session->setFlash(__('The entry could not be saved. Please, try again.'));
			}
		}

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$this->request->data['Advance']['status'] = 0;
		$this->request->data['Testingcomp']['selected'] = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
		$this->request->data['Topproject']['selected'] = array();
		$this->set(compact('testingcomps','topprojects'));
		$this->set('SettingsArray', $SettingsArray);

	}

	public function edit() {

		$this->Autorisierung->Protect();

		$locale = $this->Lang->Discription();

		if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $landig_page = 1;

		$id = $this->request->projectvars['VarsArray'][0];

		if(!isset($this->request->data['modul'])){

			$scheme_start = $this->AdvanceTool->GetStartCascade();

			if($scheme_start == 0) return;

			$this->request->data['scheme_start'] = $scheme_start;
			$Modul = 0;

		} elseif(isset($this->request->data['modul']) && isset($this->request->data['scheme_start'])){

			$scheme_start = $this->request->data['scheme_start'];
			$this->request->data['scheme_start'] = $scheme_start;
			$Modul = 1;

		}

		$this->Navigation->ModulNavigation($scheme_start,'progress');

		$SettingsArray = array();
		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$breads[] = array(
						'discription' => __('Advance manager', true),
						'controller' => 'advances',
						'action' => 'index',
						'pass' => null
		);

		$breads[] = array(
						'discription' => $Advance['Advance']['name'],
						'controller' => 'advances',
						'action' => 'edit',
						'pass' => $this->request->projectvars['VarsArray']
		);

		if($Modul == 0){

			$Advance = $this->AdvanceTool->CascadeSchemaAll($Advance);
			$Advance = $this->AdvanceTool->CreateSchemaMenue($Advance);
			$Menue = $Advance['Menue'];
			$Advance = $this->AdvanceTool->AssignAllAdvanceOrders($Advance);
			$Advance = $this->AdvanceTool->SeparadeLastAdvanceOrder($Advance);
			$Advance = $this->AdvanceTool->CreateAdvanceOrders($Advance);
			$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCats($Advance);
			$Advance = $this->AdvanceTool->AssignAllChildCascades($Advance);
			$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCatsDescription($Advance);
			$Advance = $this->AdvanceTool->SepardeAdvanceOrdersDescription($Advance);
			$Advance = $this->AdvanceTool->CollectStatisticOrderData($Advance);
			$Advance = $this->AdvanceTool->CollectStatisticCascadeGroupData($Advance);
			$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);
			$Advance = $this->AdvanceTool->CollectTestReports($Advance);
			$Advance = $this->AdvanceTool->CollectStatisticCascadeData($Advance);
			$Advance = $this->AdvanceTool->AdvancesTypes($Advance);
			$Advance = $this->AdvanceTool->UpdateCascadeStatusStart($Advance);
			$Advance = $this->AdvanceTool->UpdateCascadeStatusSummary($Advance);
			$Advance = $this->AdvanceTool->CreateDateScale($Advance);
			$Advance = $this->AdvanceTool->CreateRingPlot($Advance);
			$Advance = $this->AdvanceTool->CreateGanttPlotStartOverview($Advance);
			$Advance['Menue'] = $Menue;

			$this->request->data = $Advance;
			$this->request->data['Testingcomp']['selected'] = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
			$this->request->data['Topproject']['selected'] = array();

			$this->set(compact('testingcomps','topprojects'));
			$this->set('StartId', $scheme_start);
			$this->set('Advance', $Advance);

			if(isset($landig_page) && $landig_page == 1){

//				$this->layout = 'blank';
				$this->render('landingpage','blank');

			}
		}

		$this->set('breads', $breads);
		$this->set('SettingsArray', array());

		if($Modul == 1){

				$JsonScheme = $this->json_scheme();

				$this->layout = 'ajax';
				$this->set('JsonScheme', $JsonScheme);
				$this->render('edit_json');

		}
	}

	public function advance() {

		$this->layout = 'json';
		$this->request->data['scheme_start'];

		$Protect = $this->Autorisierung->ProtectController();

		if($Protect === false){

			$this->set('request',json_encode(array()));
			$this->render();
			return;

		}

		$Protect = $this->AdvanceTool->IsThisMyAdvance($this->request->data['scheme_start']);

		if($Protect == 0){

			$this->set('request',json_encode(array()));
			$this->render();
			return;

		}

		if(isset($this->request->data['advance_id'])) $AdvanceDataId = intval($this->request->data['advance_id']);
		else $AdvanceDataId = 0;

		if(isset($this->request->data['update'])) $Update = intval($this->request->data['update']);
		else $Update = 0;

		if(!isset($this->request->data['json_false']) && $AdvanceDataId == 0){
			$this->set('request',json_encode(array()));
			$this->render();
			return;
		}

		$EndTime = $this->AdvanceTool->CheckAdvanceEndTime();

		if($EndTime === false){
			$this->set('request',json_encode(array()));
			return;
		}

		if(isset($this->request->data['json_false']) && $this->request->data['json_false'] == 1){
			$this->AdvancesDataDependency->save($this->request->data['AdvancesDataDependency']);
			$Advance = array('result' => $this->request->data['AdvancesDataDependency']['remark']);
		} else {
			$Advance = $this->AdvanceTool->UpdateAdvance($AdvanceDataId,$Update);
			$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);
		}

		$this->set('request',json_encode($Advance));

	}

	public function order_edit() {

		$this->Autorisierung->Protect();

		$this->layout = 'modal';
		$SettingsArray = array();

		$AdvanceId = $this->request->projectvars['VarsArray'][0];
		$CascadeId = $this->request->projectvars['VarsArray'][1];
		$OrderId = $this->request->projectvars['VarsArray'][2];

		if(isset($this->request->data['AdvancesOrder'])) $result = $this->AdvanceTool->UpdateAdvanceData();

		$Advance = $this->AdvanceTool->GetAdvanceData();

		if(isset($this->request->data['json_true']) && $this->request->data['json_true'] == 1){

			$Request = $this->AdvanceTool->DelAdvanceData($Advance);
			$this->layout = 'json';
			$this->set('request',json_encode($Request));
			$this->render('json_edit');

		} else {

			$this->request->data = $Advance;
			$AdvanceType = $this->advance_type = $this->AdvanceTool->HumanizArray($this->advance_type);
			$this->set('AdvanceType',$AdvanceType);
			$this->set('SettingsArray',$SettingsArray);

		}
	}

	public function add_equipment() {

		$this->Autorisierung->Protect();

		$this->layout = 'modal';
		$SettingsArray = array();

		$locale = $this->Lang->Discription();
		$id = $this->request->projectvars['VarsArray'][0];
		$CascadeId = $this->request->projectvars['VarsArray'][1];
		$this->request->data['scheme_start'] = $CascadeId ;

		$this->AdvanceTool->CheckForAdvanceOrder();

		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));
		$CascadeGroup = $this->CascadeGroup->find('list',array('fields' => array('id',$locale)));

		$Advance['CascadeGroup'] = $CascadeGroup;

		$Advance = $this->AdvanceTool->CurrentCascade($Advance);
		$Advance = $this->AdvanceTool->CascadeSchemaSingle($Advance);

		$Advance = $this->AdvanceTool->AddEquipment($Advance);

		$this->request->data = $Advance;

		$this->set('SettingsArray',$SettingsArray);
		$this->set(compact('CascadeGroup'));

	}

	public function delete_equipment() {

		$this->Autorisierung->Protect();

		$this->layout = 'modal';
		$SettingsArray = array();

		$locale = $this->Lang->Discription();
		$id = $this->request->projectvars['VarsArray'][0];
		$CascadeId = $this->request->projectvars['VarsArray'][1];
		$this->request->data['scheme_start'] = $CascadeId ;

		$this->AdvanceTool->CheckForAdvanceOrder();

		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));
		$CascadeGroup = $this->CascadeGroup->find('list',array('fields' => array('id',$locale)));

		$Advance['CascadeGroup'] = $CascadeGroup;

		$Advance = $this->AdvanceTool->CurrentCascade($Advance);
		$Advance = $this->AdvanceTool->CascadeSchemaSingle($Advance);

		if(!isset($this->request->data['Order']['cascade_id'])) $this->Flash->warning(__('Should the following equipment really be deleted',true) . ': ' . $Advance['Cascade']['discription'], array('key' => 'warning'));

		$Advance = $this->AdvanceTool->DeleteEquipment($Advance);
		$this->request->data = $Advance;

		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'advances','action' => 'advance_settings', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray',$SettingsArray);
		$this->set(compact('CascadeGroup'));

	}

	public function advance_settings() {

		$this->Autorisierung->Protect();

		$this->layout = 'modal';
		$SettingsArray = array();
		$locale = $this->Lang->Discription();
		$id = $this->request->projectvars['VarsArray'][0];
		$CascadeId = $this->request->projectvars['VarsArray'][1];
		$this->request->data['scheme_start'] = $CascadeId;
		$LinkArray = array();

		$LastInLine = $this->AdvanceTool->LastInLineCascade();

		if($LastInLine === true){

			$LinkArray['delete_equipment'] = array(
				'discription' => __('Delete this equipment'),
				'controller' => 'advances',
				'action' => 'delete_equipment',
				'terms' => $this->request->projectvars['VarsArray'],
				'title' => __('Delete this equipment'),
				'class' => 'round mymodal'
			);

		}

		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$this->AdvanceTool->EditAdvanceCascade($Advance);

		$Advance = $this->AdvanceTool->AdvanceElements($Advance);
		$this->request->data = $Advance;

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$this->set('LinkArray', $LinkArray);
		$this->set('SettingsArray', $SettingsArray);

	}

	public function advance_add() {

		$this->Autorisierung->Protect();

		$this->layout = 'modal';
		$SettingsArray = array();

		if(!isset($this->request->data['AdvancesDataDependency']['order_id'])) return;

		if(isset($this->request->data['AdvancesDataDependency']['scheme_start'])){

			$Protect = $this->AdvanceTool->IsThisMyAdvance($this->request->data['AdvancesDataDependency']['scheme_start']);

			if($Protect == 0){

				$JSONName['parameter']['cascade_id'] = $this->request->data['AdvancesDataDependency']['scheme_start'];
				$this->set('JSONName',$JSONName);

				$this->render();
				return;
			}

		}

		if(isset($this->request->data['AdvancesDataDependency']['cascade_id'])){

			$Protect = $this->AdvanceTool->IsThisMyAdvance($this->request->data['AdvancesDataDependency']['cascade_id']);

			if($Protect == 0){

				$JSONName['parameter']['cascade_id'] = $this->request->data['AdvancesDataDependency']['cascade_id'];
				$this->set('JSONName',$JSONName);

				$this->render();
				return;
			}

		}


		$this->AdvanceTool->AddAdvance();

		$OrderId = $this->request->data['AdvancesDataDependency']['order_id'];
		$CascadeId = $this->request->data['AdvancesDataDependency']['cascade_id'];
		$id = $this->request->projectvars['VarsArray'][0];

		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$options =array('conditions' => array('Order.id' => $OrderId));
		$this->Order->recursive = -1;
		$Order = $this->Order->find('first', $options);

		$this->advance_type = $this->AdvanceTool->HumanizArray($this->advance_type);

		$this->request->data['AdvancesDataDependency']['AdvancesType'] = $this->advance_type;
		$this->request->data['AdvancesDataDependency']['cascade_id'] = $CascadeId;
		$this->request->data['AdvancesDataDependency']['order_id'] = $OrderId;
		$this->request->data['Order'] = $Order['Order'];

		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'advances','action' => 'order_edit', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray',$SettingsArray);

	}

	public function advance_delete() {

		$this->Autorisierung->Protect();

		$this->layout = 'json';

//		$this->autoRender = false;

		if(isset($this->request->data['AdvancesDataDependency'])){

			$update = $this->request->data['AdvancesDataDependency'];
			$update['deleted'] = 1;

			if($this->AdvancesDataDependency->save($update)){

				$this->Autorisierung->Logger($this->request->data['AdvancesDataDependency']['id'], $this->request->data['AdvancesDataDependency']);

				$Advance['CurrentStatus']['update'] = 'success';
				$Advance['CurrentStatus']['row'] = 'tr#advance_detail_' . $this->request->data['AdvancesDataDependency']['id'];
				$Advance['CurrentStatus']['message'] = __('The value has been deleted.',true);


			} else {

				$Advance['CurrentStatus']['row'] = 'tr#advance_detail_' . $this->request->data['AdvancesDataDependency']['id'];
				$Advance['CurrentStatus']['update'] = 'error';
				$Advance['CurrentStatus']['message'] = __('The value could not be deleted. Please, try again.',true);

			}

		} else {

			$Advance['CurrentStatus']['row'] = 0;
			$Advance['CurrentStatus']['update'] = 'error';
			$Advance['CurrentStatus']['message'] = __('The value could not be deleted. Please, try again.',true);

		}

		$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);

		$this->set('request',json_encode($Advance));

	}

	public function auto() {

		$this->Autorisierung->Protect();

//		$this->autoRender = false;

		$this->layout = 'json';

		$Response = array(0 => 'test 1',1 => 'test 2',3 => 'test 3');

		$this->set('Response',json_encode($Response));
	}

	public function json_scheme() {

		$this->Autorisierung->Protect();

		$this->layout = 'blank';

		$EndTime = $this->AdvanceTool->CheckAdvanceEndTime();

		$locale = $this->Lang->Discription();
		$id = $this->request->projectvars['VarsArray'][0];

		if(isset($this->request->data['scheme_start'])) $CascadeId = $this->request->data['scheme_start'];
		else $CascadeId = 0;

		if(isset($this->request->data['modul'])) $Modul = 1;
		else $Modul = 0;

		$this->Navigation->ModulNavigation($CascadeId,'progress');

		$this->AdvanceTool->CheckForAdvanceOrder();

		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$Advance = $this->AdvanceTool->CurrentCascade($Advance);

		if(isset($this->request->data['select_menue']) && count($this->request->data['select_menue'])) $Advance = $this->AdvanceTool->CascadeSchemaSingle($Advance);
		else $Advance = $this->AdvanceTool->CascadeSchemaAll($Advance);

		$Advance = $this->AdvanceTool->CreateSchemaMenue($Advance);
		$Menue = $Advance['Menue'];

		$Advance = $this->AdvanceTool->AssignAllAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeLastAdvanceOrder($Advance);
		$Advance = $this->AdvanceTool->CreateAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCats($Advance);
		$Advance = $this->AdvanceTool->AssignAllChildCascades($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCatsDescription($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceOrdersDescription($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticOrderData($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticCascadeGroupData($Advance);
		$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);
		$Advance = $this->AdvanceTool->CollectTestReports($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticCascadeData($Advance);
		$Advance = $this->AdvanceTool->UpdateCascadeStatus($Advance);
		$Advance = $this->AdvanceTool->BreadcrumpArrayCorrectur($Advance);
		$Advance = $this->AdvanceTool->CreateSubmenue($Advance);
		$Advance = $this->AdvanceTool->CreateEquipmentmenue($Advance);
		$Advance = $this->AdvanceTool->AdvancesTypes($Advance);
		$Advance = $this->AdvanceTool->CollectXML($Advance);
		$Advance = $this->AdvanceTool->SortOjects($Advance);
		$Advance = $this->AdvanceTool->CreateDateScale($Advance);
		$Advance = $this->AdvanceTool->CreateRingPlot($Advance);
		$Advance = $this->AdvanceTool->CreateGanttPlot($Advance);
//		$Advance = $this->AdvanceTool->CreateLinePlot($Advance);
		$Xml = $this->Xml->DatafromXml('data_dependency_display_zfp','file',null);

		$Advance['Menue'] = $Menue;
		$this->request->data = $Advance;

		if($EndTime === false){
//			$this->Session->setFlash(__('The processing period has been exceeded.'));

			$this->Flash->warning(__('The processing period has been exceeded. No data will be saved.',true), array(
			    'key' => 'hint',
					'params' => array(
			         'visibility' => 2000, // Sichtbarkeit in Millisekungen, bei 0 unendlich
			     )
				)
			);
		}

		if($Modul == 1){

			$JsonScheme = new View($this);
			$JsonScheme->autoRender = false;
			$JsonScheme->layout = null;
			$JsonScheme->set('StartId',$CascadeId);
			$JsonScheme->set('display_xml',$Xml['settings']);
			$Output = $JsonScheme->render('json_scheme');

			return $Output;

		} else {

			$this->set('StartId',$CascadeId);
			$this->set('display_xml',$Xml['settings']);

		}
	}

	function checklist() {

		$this->Autorisierung->Protect();

		$this->layout = 'pdf';

		$ProgressId = $this->request->projectvars['VarsArray'][0];
		$CascadeId = $this->request->projectvars['VarsArray'][1];
		$OrderId = $this->request->projectvars['VarsArray'][2];

		$this->request->data['scheme_start'] = $CascadeId;


		$this->AdvanceTool->CheckForAdvanceOrder();

		$options =array('conditions' => array('Advance.id' => $ProgressId));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$Advance = $this->AdvanceTool->CurrentCascade($Advance);

		if(isset($Advance['Cascade'])) $this->request->data['select_menue'][$Advance['Cascade']['id']] = $Advance['Cascade']['discription'];

		if(isset($this->request->data['select_menue']) && count($this->request->data['select_menue'])) $Advance = $this->AdvanceTool->CascadeSchemaSingle($Advance);
		else $Advance = $this->AdvanceTool->CascadeSchemaAll($Advance);

		$Advance = $this->AdvanceTool->CreateSchemaMenue($Advance);
		$Advance = $this->AdvanceTool->AssignAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeLastAdvanceOrder($Advance);
		$Advance = $this->AdvanceTool->CreateAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCats($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCatsDescription($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceOrdersDescription($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticOrderData($Advance);
		$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);
		$Advance = $this->AdvanceTool->CollectTestReports($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticCascadeData($Advance);
		$Advance = $this->AdvanceTool->UpdateCascadeStatus($Advance);
		$Advance = $this->AdvanceTool->CollectMesurePoints($Advance);
		$Advance = $this->AdvanceTool->GetTestingsForChecklist($Advance);

//		$Advance = $this->AdvanceTool->CreateDateScale($Advance);
//		$Advance = $this->AdvanceTool->CreateRingPlot($Advance);
//		$Advance = $this->AdvanceTool->CreateGanttPlot($Advance);
//		$Advance = $this->AdvanceTool->CreateLinePlot($Advance);

		$Xml = $this->Xml->CollectXMLFromFile('advance' . DS . 'settings');

		$permissions = array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');

		$imagefolder = Configure::read('root_folder');

		if(file_exists($imagefolder . DS . 'advance' . DS . 'logo' . DS . 'logo.svg')) $Logo = $imagefolder . DS . 'advance' . DS . 'logo' . DS . 'logo.svg';
				if(file_exists($imagefolder . DS . 'advance' . DS . 'logo' . DS .'additional'.DS. 'logo.png')) $LogoAdditional = $imagefolder . DS . 'advance' . DS . 'logo' . DS .'additional'.DS. 'logo.png';

//		$HttpSocket = new HttpSocket(array('ssl_cafile' => 'C:\wamp64\bin\php\php7.3.5\extras\ssl\cacert.pem'));

		$this->set('Logo',$Logo);
		$this->set('LogoAdditional',$LogoAdditional);
		$this->set('permissions',$permissions);
		$this->set('xml',$Xml);
		$this->set('Advance',$Advance);

	}

	function printstatistic() {

		$this->Autorisierung->Protect();

		$this->layout = 'pdf';

		$ProgressId = $this->request->projectvars['VarsArray'][0];
		$CascadeId = $this->request->projectvars['VarsArray'][1];
		$OrderId = $this->request->projectvars['VarsArray'][2];

		$this->request->data['scheme_start'] = $CascadeId;


		$this->AdvanceTool->CheckForAdvanceOrder();

		$options =array('conditions' => array('Advance.id' => $ProgressId));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$Advance = $this->AdvanceTool->CurrentCascade($Advance);

		if(isset($Advance['Cascade'])) $this->request->data['select_menue'][$Advance['Cascade']['id']] = $Advance['Cascade']['discription'];

		if(isset($this->request->data['select_menue']) && count($this->request->data['select_menue'])) $Advance = $this->AdvanceTool->CascadeSchemaSingle($Advance);
		else $Advance = $this->AdvanceTool->CascadeSchemaAll($Advance);

		$Advance = $this->AdvanceTool->CreateSchemaMenue($Advance);
		$Advance = $this->AdvanceTool->AssignAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeLastAdvanceOrder($Advance);
		$Advance = $this->AdvanceTool->CreateAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCats($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCatsDescription($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceOrdersDescription($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticOrderData($Advance);
		$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);
		$Advance = $this->AdvanceTool->CollectTestReports($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticCascadeData($Advance);
		$Advance = $this->AdvanceTool->UpdateCascadeStatus($Advance);
		$Advance = $this->AdvanceTool->CollectMesurePoints($Advance);
		$Advance = $this->AdvanceTool->GetTestingsForChecklist($Advance);

//		$Advance = $this->AdvanceTool->CreateDateScale($Advance);
//		$Advance = $this->AdvanceTool->CreateRingPlot($Advance);
//		$Advance = $this->AdvanceTool->CreateGanttPlot($Advance);
//		$Advance = $this->AdvanceTool->CreateLinePlot($Advance);

		$Xml = $this->Xml->CollectXMLFromFile('advance' . DS . 'settings');

		$permissions = array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');

		$imagefolder = Configure::read('root_folder');

		if(file_exists($imagefolder . DS . 'advance' . DS . 'logo' . DS . 'logo.svg')) $Logo = $imagefolder . DS . 'advance' . DS . 'logo' . DS . 'logo.svg';
				if(file_exists($imagefolder . DS . 'advance' . DS . 'logo' . DS .'additional'.DS. 'logo.png')) $LogoAdditional = $imagefolder . DS . 'advance' . DS . 'logo' . DS .'additional'.DS. 'logo.png';

//		$HttpSocket = new HttpSocket(array('ssl_cafile' => 'C:\wamp64\bin\php\php7.3.5\extras\ssl\cacert.pem'));

		$this->set('Logo',$Logo);
		$this->set('LogoAdditional',$LogoAdditional);
		$this->set('permissions',$permissions);
		$this->set('xml',$Xml);
		$this->set('Advance',$Advance);

	}

	public function reload_statistic() {

		$this->Autorisierung->Protect();

		$this->layout = 'blank';

		$locale = $this->Lang->Discription();
		$id = $this->request->projectvars['VarsArray'][0];

		if(isset($this->request->data['scheme_start'])) $StartId = $this->request->data['scheme_start'];
		else $StartId = 0;

		$this->AdvanceTool->CheckForAdvanceOrder();

		$options =array('conditions' => array('Advance.id' => $id));
		$Advance = $this->Advance->find('first', $options);

		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Testingcomp', array('AdvancesTestingcomp','testingcomp_id','advance_id'));
		$Advance = $this->Data->BelongsToManySelected($Advance, 'Advance', 'Topproject', array('AdvancesTopproject','topproject_id','advance_id'));

		$testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$Advance = $this->AdvanceTool->CurrentCascade($Advance);

		if(isset($this->request->data['select_menue']) && count($this->request->data['select_menue'])) $Advance = $this->AdvanceTool->CascadeSchemaSingle($Advance);
		else $Advance = $this->AdvanceTool->CascadeSchemaAll($Advance);

		$Advance = $this->AdvanceTool->AssignAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SeparadeLastAdvanceOrder($Advance);
		$Advance = $this->AdvanceTool->CreateAdvanceOrders($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCats($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceCascadeCatsDescription($Advance);
		$Advance = $this->AdvanceTool->SepardeAdvanceOrdersDescription($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticOrderData($Advance);
		$Advance = $this->AdvanceTool->UpdateOrderStatus($Advance);
		$Advance = $this->AdvanceTool->CollectTestReports($Advance);
		$Advance = $this->AdvanceTool->CollectStatisticCascadeData($Advance);
		$Advance = $this->AdvanceTool->UpdateCascadeStatus($Advance);
		$Advance = $this->AdvanceTool->CreateDateScale($Advance);
		$Advance = $this->AdvanceTool->CreateRingPlot($Advance);
		$Advance = $this->AdvanceTool->CreateGanttPlot($Advance);
		$Advance = $this->AdvanceTool->CreateLinePlot($Advance);

		$this->request->data = $Advance;
		$this->set('StartId',$StartId);

	}
}
