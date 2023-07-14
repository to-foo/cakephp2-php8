<?php
App::uses('AppController', 'Controller');
/**
 * EquipmentTypes Controller
 *
 * @property EquipmentType $EquipmentType
 */
class SuppliersController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data','Formating','Image','Email','ExpeditingTool','Csv','MessagesTool');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'ajax';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		// Es wird das in der Auswahl gewählte Project in einer Session gespeichert

		$this->Navigation->ReportVars();

		$this->loadModel('Cascade');
		$this->loadModel('Expeditingset');
		$this->loadModel('User');
		$this->loadModel('Topproject');
		$this->loadModel('Expediting');
		$this->loadModel('Testingcomp');
		$this->loadModel('Order');
		$this->loadModel('Roll');
		$this->loadModel('Supplierimage');
		$this->loadModel('Supplierfile');
		$this->loadModel('ExpeditingEvent');
		$this->loadModel('ExpeditingType');
		$this->loadModel('ExpeditingsRoll');
		$this->loadModel('CascadeGroup');
		$this->loadModel('Rkl');

		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('files','images','image','getfile','getmanyfiles','savepostdata','pdf','diagramm','export','download');

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
		$this->set('locale', $this->Lang->Discription());
		$this->set('SettingsArray',array());

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}

		$this->ExpeditingTool->RequestPostdataSession();
	}

	function afterFilter() {
		$this->ExpeditingTool->SavePostdataSession();
		$this->Navigation->lastURL();
	}

/**
 * index method *
 * @return void
 */
 	public function control(){

//		$this->autoRender = false;
	 	$this->layout = 'json';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$Supplier = $this->Supplier->find('first',array('fields' => array('id','stop_mail'), 'conditions' => array('Supplier.id' => $supplierID)));
		if($Supplier['Supplier']['stop_mail'] == 0) {
			$Supplier['Supplier']['stop_mail'] = 1;
			$Supplier['Supplier']['remove_class'] = 'icon_on_mail';
			$Supplier['Supplier']['add_class'] = 'icon_stop_mail';
			$Supplier['Supplier']['title'] = __('Email delivering is active, click to deactivate',true);
		}
		elseif($Supplier['Supplier']['stop_mail'] == 1) {
			$Supplier['Supplier']['stop_mail'] = 0;
			$Supplier['Supplier']['remove_class'] = 'icon_stop_mail';
			$Supplier['Supplier']['add_class'] = 'icon_on_mail';
			$Supplier['Supplier']['title'] = __('Email delivering is deactive, click to activate',true);
		}

		if($this->Supplier->save($Supplier))$Supplier['Supplier']['save'] = 1;
		else $Supplier['Supplier']['save'] = 0;

		$this->set('data',json_encode($Supplier));
	}

 	protected function _mail_control(){
	}

	public function statistic(){

		App::uses('CakeTime', 'Utility');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		$xml = $this->Xml->DatafromXml('display_expediting','file',null);

		if(!is_object($xml['settings'])){
			pr('Einstellungen konnten nicht geladen werden.');
			return;
		} else {
			$this->set('xml',$xml['settings']);
		}

		$PriorityStatus = array('critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);
		$StatusOptionForBread = array_flip($PriorityStatus);
		$StatusOption = array(1 => __('critical',true),2 => __('delayed',true),3 => __('plan',true),4 => __('future',true),5 => __('finished',true));
		$PriorityText = array(
						'critical' => __('Critical (soll date > current date + waiting period)',true),
						'delayed' => __('Delayed (soll date > current date and <  current date + waiting period)',true),
						'plan' => __('In plan (soll date < current date)',true),
						'future' => __('No date informations stored',true),
						'finished' => __('Completed',true)
					);

		foreach($PriorityStatus as $_key => $_data){
			unset($PriorityStatus[$_key]);
			$PriorityStatus[$_key]['count'] = 0;
			$PriorityStatus[$_key]['text'] = $PriorityText[$_key];
		}

		$ExpeditingNavi = array();

		$SettingsArray = array();

		$breads = array();

		// Noch eine Überprüfung einbauen, Beruchtigungszugriff
		$AllCascadesUnder = $this->Navigation->CascadeGetChildrenList(intval($cascadeID));
		// Falls das Array mit den Cascaden nochmals gefilter werden soll, wenn entsprechende Postdaten kommen
		$AllCascadesUnder = $this->ExpeditingTool->SelectExpeditingFilter($AllCascadesUnder);
		$SelectOrderByTestingcomp = $this->Data->SelectOrderByTestingcomp();
		$ChildrenList = $this->Navigation->CascadeGetNextChildren(intval($cascadeID),array('id','discription'));
		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);
		$ParentCasace = $this->Navigation->CascadeGetParentId(intval($cascadeID));

		$ParentCascaeTerm = $this->request->projectvars['VarsArray'];
		$ParentCascaeTerm[1] = $ParentCasace;
		$ParentCascaeTerm[2] = 0;

		$ExpeditingNavi['ExpeditingLink'] = array(
			'discription' => __('Equipment list of this selection',true),
			'controller' => 'suppliers',
			'action' => 'index',
			'pass' => $this->request->projectvars['VarsArray'],
			'class' => 'expediting_navi icon_expediting',
		);

		$ExpeditingNavi['PrintLink'] = array(
			'discription' => __('Print current selection',true),
			'controller' => 'suppliers',
			'action' => 'pdf',
			'pass' => $ParentCascaeTerm,
			'class' => 'expediting_print icon_print',
		);

		if($ParentCasace > 0){

			$ExpeditingNavi['UpwardLink'] = array(
				'discription' => __('Show parent cascade',true),
				'controller' => 'suppliers',
				'action' => 'statistic',
				'pass' => $ParentCascaeTerm,
				'class' => 'expediting_navi icon_upward',
			);

		}

		$ExpeditingSelect = array(
			'controller' => 'suppliers',
			'action' => 'statistic',
		);

		$HeadLine = $breads;
		$HeadLineFirst = array_shift($HeadLine);
		$HeadLineFirst = array_shift($HeadLine);
		$HeadLine = Hash::extract($HeadLine, '{n}.discription');
		$HeadLine = implode(' > ',$HeadLine);

		if(isset($this->request->data['Statistic']['supplier'])){

			$FilterOptions['fields'] = 'Supplier.cascade_id DISTINCT';
			$FilterOptions['conditions']['Supplier.deleted'] = 0;
			$FilterOptions['conditions']['Supplier.cascade_id'] = $AllCascadesUnder;

			if(isset($this->request->data['Statistic']['supplier']) && $this->request->data['Statistic']['supplier'] != ''){
				$FilterOptions['conditions']['Supplier.supplier'] = $this->request->data['Statistic']['supplier'];
				$FilterOptions['conditions']['Supplier.supplier !='] = '';
			}

			if(isset($this->request->data['Statistic']['planner']) && !empty($this->request->data['Statistic']['planner'])){
				$FilterOptions['conditions']['Supplier.planner'] = $this->request->data['Statistic']['selected']['planner'];
				$FilterOptions['conditions']['Supplier.planner !='] = '';
			}

			if(isset($this->request->data['Statistic']['area_of_responsibility']) && !empty($this->request->data['Statistic']['area_of_responsibility'])){
				$FilterOptions['conditions']['Supplier.area_of_responsibility'] = $this->request->data['Statistic']['selected']['area_of_responsibility'];
				$FilterOptions['conditions']['Supplier.area_of_responsibility !='] = '';
			}

			$SupplierOption = $this->Supplier->find('all',$FilterOptions);

			$SupplierOption = Hash::extract($SupplierOption, '{n}.Supplier.cascade_id');
			$AllCascadesUnder = $SupplierOption;
		}

		$SupplierOptionConditions['Supplier.deleted'] = 0;
		$SupplierOptionConditions['Supplier.cascade_id'] = $AllCascadesUnder;
		$SupplierOptionConditions['Supplier.order_id'] = $SelectOrderByTestingcomp;

		$SupplierOptionConditions['Supplier.supplier !='] = '';
		$SupplierOption = $this->Supplier->find('all',array('order' => array('Supplier.supplier'),'fields' => array('Supplier.supplier DISTINCT'),'conditions' => $SupplierOptionConditions));
		$SupplierOption = Hash::extract($SupplierOption, '{n}.Supplier.supplier');
		unset($SupplierOptionConditions['Supplier.supplier !=']);

		$SupplierOptionConditions['Supplier.planner !='] = '';
		$PlannerOption = $this->Supplier->find('all',array('order' => array('Supplier.planner'),'fields' => array('Supplier.planner DISTINCT'),'conditions' => $SupplierOptionConditions));
		$PlannerOption = Hash::extract($PlannerOption, '{n}.Supplier.planner');
		unset($SupplierOptionConditions['Supplier.planner !=']);

		$SupplierOptionConditions['Supplier.area_of_responsibility !='] = '';
		$AreaOfResponsibilityOption = $this->Supplier->find('all',array('order' => array('Supplier.area_of_responsibility'),'fields' => array('Supplier.area_of_responsibility DISTINCT'),'conditions' => $SupplierOptionConditions));
		$AreaOfResponsibilityOption = Hash::extract($AreaOfResponsibilityOption, '{n}.Supplier.area_of_responsibility');
		unset($SupplierOptionConditions['Supplier.area_of_responsibility !=']);

		$SupplierConditions['conditions']['Supplier.deleted'] = 0;
		$SupplierConditions['conditions']['Supplier.cascade_id'] = $AllCascadesUnder;
		$SupplierConditions['conditions']['Supplier.order_id'] = $SelectOrderByTestingcomp;

		if(isset($this->request->data['Statistic']['supplier'])){
			$SupplierConditions['conditions']['Supplier.supplier'] = $this->request->data['Statistic']['supplier'];
			$SupplierConditions['conditions']['Supplier.supplier !='] = '';
		}

		if(isset($this->request->data['Statistic']['planner'])){
			$SupplierConditions['conditions']['Supplier.planner'] = $this->request->data['Statistic']['planner'];
			$SupplierConditions['conditions']['Supplier.planner !='] = '';
		}

		if(isset($this->request->data['Statistic']['area_of_responsibility'])){
			$SupplierConditions['conditions']['Supplier.area_of_responsibility'] = $this->request->data['Statistic']['area_of_responsibility'];
			$SupplierConditions['conditions']['Supplier.area_of_responsibility !='] = '';
		}

		$Supplier = $this->Supplier->find('all',$SupplierConditions);

		foreach($Supplier as $_key => $_Supplier){

			$Expeditings = $this->Expediting->find('all',array('order' => array('Expediting.sequence ASC'), 'conditions' => array('Expediting.supplier_id' => $_Supplier['Supplier']['id'])));

			$Priority = 5;

			foreach($Expeditings as $__key => $__Expeditings){
				$Expeditings[$__key] = $this->ExpeditingTool->ExpeditingTimePeriode($Expeditings,$__Expeditings,$__key);
				if($Expeditings[$__key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$__key]['Expediting']['priority'];
			}

			$Supplier[$_key]['Expediting'] = $Expeditings;

			$Supplier[$_key]['Supplier']['priority'] = $Priority;

			if(isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
				if($Priority != $this->request->data['Statistic']['status']){
					unset($Supplier[$_key]);
					continue;
				}
			}

			++$PriorityStatus[$StatusOptionForBread[$Supplier[$_key]['Supplier']['priority']]]['count'];

		}

		$options['Supplier.deleted'] = 0;
		$options['Supplier.cascade_id'] = $AllCascadesUnder;
		$options['Supplier.order_id'] = $SelectOrderByTestingcomp;

		if(isset($this->request->data['Statistic']['supplier']) && !empty($this->request->data['Statistic']['supplier'])){
			$options['Supplier.supplier'] = $this->request->data['Statistic']['supplier'];
			$options['Supplier.supplier !='] = '';
			$SupplierOptionValue = array_flip($SupplierOption);
			$this->request->data['Statistic']['selected']['supplier'] = $SupplierOptionValue[$this->request->data['Statistic']['supplier']];
		}

		if(isset($this->request->data['Statistic']['planner']) && !empty($this->request->data['Statistic']['planner'])){
			$options['Supplier.planner'] = $this->request->data['Statistic']['planner'];
			$options['Supplier.planner !='] = '';

			$PlannerOptionValue = array_flip($PlannerOption);
			$this->request->data['Statistic']['selected']['planner'] = $PlannerOptionValue[$this->request->data['Statistic']['planner']];
		}

		if(isset($this->request->data['Statistic']['area_of_responsibility']) && !empty($this->request->data['Statistic']['area_of_responsibility'])){
			$options['Supplier.area_of_responsibility'] = $this->request->data['Statistic']['area_of_responsibility'];
			$options['Supplier.area_of_responsibility !='] = '';

			$AreaOfResponsibilityOptionValue = array_flip($AreaOfResponsibilityOption);
			$this->request->data['Statistic']['selected']['area_of_responsibility'] = $AreaOfResponsibilityOptionValue[$this->request->data['Statistic']['area_of_responsibility']];
		}

		if(isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
			$options['Supplier.status'] = $this->request->data['Statistic']['status'];
			$options['Supplier.status !='] = '';
			$thisStatus = $StatusOptionForBread[$this->request->data['Statistic']['status']];
			$thisStatusKey = $PriorityStatus[$thisStatus];
			unset($PriorityStatus);
			$PriorityStatus[$thisStatus] = $thisStatusKey;
		}

		foreach($StatusOptionForBread as $_key => $_data){
			if(!isset($PriorityStatus[$_data])) unset($StatusOption[$_key]);
		}

		$CascadeIds = $this->Navigation->CascadeGetFromTestingcomp();

		$AllCascadesUnder = array_intersect($CascadeIds, $AllCascadesUnder);

		if(isset($this->request->data['Statistic'])){

			$ChildrenListCorrection = array();
			$ChildrenListCorrectionArray = array();
			$CurrentLevel = ++$CascadeForBread[0]['Cascade']['level'];

			foreach($AllCascadesUnder as $_key => $_data){
				$ChildrenListCorrection[$_key] = $this->Navigation->CascadeGetParentList($_data,array());
			}
			foreach($ChildrenListCorrection as $_key => $_data){
				foreach($_data as $__key => $__data){
					if($__data['Cascade']['level'] != $CurrentLevel) continue;
					$ChildrenListCorrectionArray[$__data['Cascade']['id']] = $__data['Cascade']['discription'];
				}
			}

			$ChildrenList = $ChildrenListCorrectionArray;
		}

		$Priority = array(1 => 'icon_critical',2 => 'icon_delayed',3 => 'icon_plan',4 => 'icon_future',5 => 'icon_finished');

		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);

		if(isset($this->request->data['Statistic']['supplier'])){
			$HeadLine .= ' > ' . $this->request->data['Statistic']['supplier'];
		}
		if(isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
			$HeadLine .= ' > ' . $StatusOption[$this->request->data['Statistic']['status']];
		}

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

		$ExpeditingLinks = $this->Autorisierung->AclCheckLinks($ExpeditingLinks);

		$response = array();
	
		$response['CascadeForBread'] = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$response = $this->ExpeditingTool->SubmenueOnlyCascades($response);

/*
		$response['BreadcrumpArray'][] = array(
			'title' => __('Statistic',true),
			'controller' => 'suppliers',
			'action' => 'statistic',
			'params' => $this->request->projectvars['VarsArray'],
			'class' => 'ajax',
		);
*/
		$this->request->data = $response;

		$breads = array();

		$breads[] = array(
						'discription' => __('Expediting manager', true),
						'controller' => 'expeditings',
						'action' => 'index',
						'pass' => null
		);

		$this->set('SettingsArray',array());
		$this->set('xml',$xml);
		$this->set('breads',$breads);
		$this->set('ExpeditingLinks', $ExpeditingLinks);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('Supplier', $Supplier);
		$this->set('Priority', $Priority);
		$this->set('ExpeditingNavi',$ExpeditingNavi);
		$this->set('ChildrenList',$ChildrenList);
		$this->set('PriorityStatus',$PriorityStatus);
		$this->set('ExpeditingSelect',$ExpeditingSelect);
		$this->set('HeadLine',$HeadLine);
		$this->set('SupplierOption',$SupplierOption);
		$this->set('StatusOption',$StatusOption);
		$this->set('PlannerOption',$PlannerOption);
		$this->set('AreaOfResponsibilityOption',$AreaOfResponsibilityOption);

	}

	public function statisticdetail(){

	 	$this->layout = 'blank';

		$case = $this->request->data['case'];

		switch ($case) {
			case 1:
			$this->_statisticdetail_1();
			break;
			case 2:
			$this->_statisticdetail_2();
			break;
			case 3:
			$this->_statisticdetail_3();
			break;
		}
	}

	protected function _statisticdetail_1(){

		$Output = $this->ExpeditingTool->StatisticDetail01();

		if(isset($this->request->data['stoprendering']) && $this->request->data['stoprendering'] == true){
			$this->autoRender = false;
			$Data['Output'] = $Output;
			return $Data;
		}

		$this->set('Output',$Output['Output']);

		$this->render('statistics/statisticdetail_1');
	}

	protected function _statisticdetail_2(){

		App::uses('CakeTime', 'Utility');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

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

		$Output = array();

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		// Noch eine Überprüfung einbauen, Beruchtigungszugriff
		$AllCascadesUnder = $this->ExpeditingTool->CascadeGetChildrenList(intval($cascadeID));

		// Falls das Array mit den Cascaden nochmals gefilter werden soll, wenn entsprechende Postdaten kommen
		$AllCascadesUnder = $this->ExpeditingTool->SelectExpeditingFilter($AllCascadesUnder);
		$SelectOrderByTestingcomp = $this->Data->SelectOrderByTestingcomp();

		$Suppliers = $this->Supplier->find('list',array(
			'conditions' => array(
				'Supplier.deleted' => 0,'Supplier.cascade_id' => $AllCascadesUnder,
				'Supplier.order_id' => $SelectOrderByTestingcomp
				)
			)
		);

		$this->Expediting->recursive = 0;

		$ExpeditingOption = $this->Expediting->find('list',
			array(
				'fields' => array(
					'expediting_type_id','id'
				),
				'groups' => array('expediting_type_id')
			)
		);

		if(count($ExpeditingOption) == 0) return;

		$ExpeditingTypes = $this->ExpeditingType->find('list',
			array(
				'fields' => array(
					'id',
					'description'
				),
				'conditions' => array(
				)
			)
		);

		$Output['Output'] = array();
		$Statistic['Output'] = array();
		$Output['OutputDetail'] = array();
		$Supplier = array();

		foreach($Suppliers as $key => $data){

//			$Supplier = $this->_SupplierData($Supplier,$data,$ExpeditingTypes);
			$Out = $this->ExpeditingTool->CollectSupplierDataStatistic($data);
			$Supplier[] = $Out;

			$Output = $this->ExpeditingTool->SupplierOutputs($Output,$Out);
			$Output = $this->ExpeditingTool->SupplierOutput($Output,$Out);

		}

		if(count($Output['Output']) == 1 && isset($this->request->data['Statistic']['supplier']) && !empty($this->request->data['Statistic']['supplier'])) unset($Output['Output']);

		if(!isset($Output)) $Output = array();

		if(isset($this->request->data['stoprendering']) && $this->request->data['stoprendering'] == true){
			$this->autoRender = false;
			$Data['Supplier'] = $Supplier;
			$Data['Output'] = $Output['Output'];
//			$Data['OutputDetail'] = $Output['Expeditings']['OutputDetail'];
			return $Data;
		}

		$this->set('Supplier',$Supplier);
		$this->set('Output',$Output);
//		$this->set('OutputDetail',$Output['OutputDetail']);

		$this->render('statistics/statisticdetail_2');
	}

	protected function _SupplierData($Suppliers,$id,$ExpeditingTypes){

		$Supplier = $this->Supplier->find('first',array(
			'conditions' => array(
				'Supplier.id' => $id,
				)
			)
		);

		$Suppliers[$Supplier['Supplier']['id']] = $Supplier;

		return $Suppliers;
	} 

	protected function _SupplierOutput($Data,$Supplier,$ExpeditingTypes){

		$PriorityStatus = array('total' => 1,'critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);
		$PriorityStatusFlip = array_flip($PriorityStatus);

		$Output = $Data['Output'];

		foreach($Output as $_key => $_data){

			$StatusTotalArray = array('total' => 0,'critical' => 0,'delayed' => 0,'plan' => 0,'future' => 0,'finished' => 0);
			$ExpeditingOptionCount = count($ExpeditingTypes);

			foreach($_data as $__key => $__data){

				foreach($Supplier as $___key => $___data){

					if($___data['Supplier']['id'] == $___data){
					
						break;
					}
				}

				$Data['Output'][$_key][$__key]['total'] = count($Supplier[$_key]['Expediting']);



				if(
					isset($this->request->data['Statistic']['status']) &&
					!empty($this->request->data['Statistic']['status']) &&
					isset($Data['Output'][$_key][$__key][$PriorityStatusFlip[$this->request->data['Statistic']['status']]]) &&
					$Data['Output'][$_key][$__key][$PriorityStatusFlip[$this->request->data['Statistic']['status']]] < 1
					){
					unset($Data['Output'][$_key][$__key]);
					--$ExpeditingOptionCount;
				}

				foreach($__data as $___key => $___data){

					if(isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
						if($PriorityStatusFlip[$this->request->data['Statistic']['status']] != $___key) continue;
					}

					$StatusTotalArray[$___key] += @count($Data['OutputDetail'][$_key][$__key][$___key]);
				}
				
			}

			$StatusTotalArray['total'] = count($Supplier[$_key]['Expediting']);
			$Data['Output'][$_key][__('Total',true)] = $StatusTotalArray;

			if(isset($_key) && $Data['Output'][$_key][__('Total',true)]['total'] == 0) {
				unset($Data['Output'][$_key]);
				continue;
			}

			if(count($Data['Output'][$_key]) == 1 && key($Data['Output'][$_key]) == __('Total',true)) unset($Data['Output'][$_key]);


			if(isset($Data['Output'][$_key]) && count($Data['Output'][$_key]) == 2 && isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
				unset($Data['Output'][$_key][__('Total',true)]);
				$ThisKey = key($Data['Output'][$_key]);
				$FirstElement = array_shift($Data['Output'][$_key]);
				$Data['Output'][$_key][$ThisKey] = $FirstElement;
				$Data['Output'][$_key][__('Total',true)] = $FirstElement;
			}
			
		}

		return $Data;
	}

	protected function _statisticdetail_3(){

		if(!isset($this->request->data['Statistic']['supplier']) || empty($this->request->data['Statistic']['supplier'])){

			$this->set('Supplier',array());
			$this->set('Output',array());
			$this->set('OutputDetail',array());
			$this->render('statistics/statisticdetail_3');

			return;
		}

		App::uses('CakeTime', 'Utility');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		$PriorityStatus = array('total' => 1,'critical' => 1,'delayed' => 2,'plan' => 3,'future' => 4,'finished' => 5);
		$PriorityStatusFlip = array_flip($PriorityStatus);
		$PriorityStatusArray = array('total' => 1,'critical' => 0,'delayed' => 0,'plan' => 0,'future' => 0,'finished' => 0);
		$PriorityStatusArrayDetail = array('total' => array(),'critical' => array(),'delayed' => array(),'plan' => array(),'future' => array(),'finished' => array());

		$PriorityText = array(
						'critical' => __('Critical (soll date > current date + waiting period)',true),
						'delayed' => __('Delayed (soll date > current date and <  current date + waiting period)',true),
						'plan' => __('In plan (soll date < current date)',true),
						'future' => __('No date informations stored',true),
						'finished' => __('Completed',true)
					);

		$Output = array();

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		// Noch eine Überprüfung einbauen, Beruchtigungszugriff
		$AllCascadesUnder = $this->Navigation->CascadeGetChildrenList(intval($cascadeID));

		// Falls das Array mit den Cascaden nochmals gefilter werden soll, wenn entsprechende Postdaten kommen
		$AllCascadesUnder = $this->ExpeditingTool->SelectExpeditingFilter($AllCascadesUnder);
		$SelectOrderByTestingcomp = $this->Data->SelectOrderByTestingcomp();

		$SupplierOption = $this->Supplier->find('all',array('fields' => array('Supplier.supplier DISTINCT'),'conditions' => array('Supplier.supplier !=' => '','Supplier.deleted' => 0,'Supplier.cascade_id' => $AllCascadesUnder,'Supplier.order_id' => $SelectOrderByTestingcomp)));
		$SupplierOption = Hash::extract($SupplierOption, '{n}.Supplier.supplier');

		$this->Expediting->recursive = 0;

		$ExpeditingOption = $this->Expediting->find('all',array('fields' => array('Expediting.description DISTINCT'),'conditions' => array('Expediting.description !=' => '')));
		$ExpeditingOption = Hash::extract($ExpeditingOption, '{n}.Expediting.description');

		$ExpeditingOption = array_flip($ExpeditingOption);

		foreach($ExpeditingOption as $_key => $_data){
			$ExpeditingOption[$_key] = $PriorityStatusArray;
			$ExpeditingOptionArray[$_key] = $PriorityStatusArrayDetail;
		}

		$Output = array();
		$OutputDetail = array();

		$SupplierConditions['conditions']['Supplier.deleted'] = 0;
		$SupplierConditions['conditions']['Supplier.count_ordered !='] = 0;
		$SupplierConditions['conditions']['Supplier.cascade_id'] = $AllCascadesUnder;
		$SupplierConditions['conditions']['Supplier.order_id'] = $SelectOrderByTestingcomp;

		foreach($SupplierOption as $_key => $_data){
			if(isset($this->request->data['Statistic']['supplier']) && !empty($this->request->data['Statistic']['supplier'])){
				if($_data != $this->request->data['Statistic']['supplier']) continue;
				$SupplierConditions['conditions']['Supplier.supplier !='] = '';
			}
			$SupplierConditions['conditions']['Supplier.supplier'] = $_data;
			$Supplier[$_data] = $this->Supplier->find('all',$SupplierConditions);
		}

		foreach($Supplier as $_key => $_data){

			$Output[$_key] = $ExpeditingOption;
			$OutputDetail[$_key] = $ExpeditingOptionArray;

			foreach($_data as $__key => $__data){

				$Expediting = $this->Expediting->find('all',array('limit' => 10,'conditions' => array('Expediting.supplier_id' => $__data['Supplier']['id'])));

				$Priority = 5;

				$Supplier[$_key][$__key]['Expeditingstatistic'] = $ExpeditingOption;

				foreach($Expediting as $___key => $___data){

					$Expediting[$___key] = $this->ExpeditingTool->ExpeditingTimePeriode($Expediting,$___data,$___key);
					++$Output[$_key][$Expediting[$___key]['Expediting']['description']][$Expediting[$___key]['Expediting']['class']];

					array_push($OutputDetail[$_key][$Expediting[$___key]['Expediting']['description']][$Expediting[$___key]['Expediting']['class']], $__data['Supplier']['id']);

					if($Expediting[$___key]['Expediting']['priority'] < $Priority) $Priority = $Expediting[$___key]['Expediting']['priority'];

					$Supplier[$_key][$__key]['Supplier']['priority'] = $Priority;
					$Supplier[$_key][$__key]['Expediting'] = $Expediting;
					++$Supplier[$_key][$__key]['Expeditingstatistic'][$Expediting[$___key]['Expediting']['description']][$Expediting[$___key]['Expediting']['class']];
				}
			}
		}


		foreach($Output as $_key => $_data){

			$StatusTotalArray = array('total' => 0,'critical' => 0,'delayed' => 0,'plan' => 0,'future' => 0,'finished' => 0);
			$ExpeditingOptionCount = count($ExpeditingOption);

			foreach($_data as $__key => $__data){

				$Output[$_key][$__key]['total'] = count($Supplier[$_key]);

				if(
					isset($this->request->data['Statistic']['status']) &&
					!empty($this->request->data['Statistic']['status']) &&
					isset($Output[$_key][$__key][$PriorityStatusFlip[$this->request->data['Statistic']['status']]]) &&
					$Output[$_key][$__key][$PriorityStatusFlip[$this->request->data['Statistic']['status']]] < 1
					){
					unset($Output[$_key][$__key]);
//					if($ExpeditingOptionCount > 0) --$ExpeditingOptionCount;
					--$ExpeditingOptionCount;
				}

				foreach($__data as $___key => $___data){
					$StatusTotalArray[$___key] += count($OutputDetail[$_key][$__key][$___key]);
				}
			}

			$StatusTotalArray['total'] = count($Supplier[$_key]) * $ExpeditingOptionCount;
			$Output[$_key][__('Total',true)] = $StatusTotalArray;

			if(count($Output[$_key]) == 1 && key($Output[$_key]) == __('Total',true)) unset($Output[$_key]);

			if(isset($Output[$_key]) && count($Output[$_key]) == 2 && isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
				unset($Output[$_key][__('Total',true)]);
				$ThisKey = key($Output[$_key]);
				$FirstElement = array_shift($Output[$_key]);
				$Output[$_key][$ThisKey] = $FirstElement;
				$Output[$_key][__('Total',true)] = $FirstElement;
			}
		}

		if(isset($this->request->data['stoprendering']) && $this->request->data['stoprendering'] == true){
//			$this->autoRender = false;
			$Data['Supplier'] = $Supplier;
			$Data['Output'] = $Output;
			$Data['OutputDetail'] = $OutputDetail;
			return $Data;
		}

		$this->set('Supplier',$Supplier);
		$this->set('Output',$Output);
		$this->set('OutputDetail',$OutputDetail);

		$this->render('statistics/statisticdetail_3');
	}

	public function savepostdata() {

		$this->autoRender = false;
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		$PrintSession['projectID'] = $projectID;
		$PrintSession['cascadeID'] = $cascadeID;

		if(isset($this->request->data['Statistic'])){

			if(isset($this->request->data['Statistic']['supplier']) && $this->request->data['Statistic']['supplier'] != ''){
				$PrintSession['supplier'] = $this->request->data['Statistic']['supplier'];
			}
			if(isset($this->request->data['Statistic']['status']) && $this->request->data['Statistic']['status'] != '' && $this->request->data['Statistic']['status'] != '0'){
				$PrintSession['status'] = $this->request->data['Statistic']['status'];
			}
		}

		 $this->Session->write('PrintSessionExpediting',$PrintSession);

	}

	public function export() {

		$this->autoRender = false;

		$data = $this->ExpeditingTool->GetTimeRangeDelivering();
		$data = $this->ExpeditingTool->CsvOutput($data,$type = 'Delivering');

		$this->Csv->exportCsv($data,'statistics.csv');

	}

	public function pdf() {

//		$this->autoRender = false;
	 	$this->layout = 'pdf';

		$this->request->projectvars['VarsArray'][0] = $this->Session->read('PrintSessionExpediting.projectID');
		$this->request->projectvars['VarsArray'][1] = $this->Session->read('PrintSessionExpediting.cascadeID');
		$Supplier = $this->Session->read('PrintSessionExpediting.supplier');
		$Status = $this->Session->read('PrintSessionExpediting.status');

		if(isset($Supplier) && !empty($Supplier)) $this->request->data['Statistic']['supplier'] = $this->Session->read('PrintSessionExpediting.supplier');
		if(isset($Status) && !empty($Status))$this->request->data['Statistic']['status'] = $this->Session->read('PrintSessionExpediting.status');

		$this->request->data['stoprendering'] = true;
		$this->Session->delete('PrintSessionExpediting.supplier');
		$HelpArray = array('TA2020','2020+','Projekt');

		$PdfOutput = array();

		$Data = NULL;
		$Data = $this->_statisticdetail_1();
		$PdfOutput['StatisticDetail1'] = $Data;

		$Data = NULL;
		$Data = $this->_statisticdetail_2();
		$PdfOutput['StatisticDetail2'] = $Data;

		$Data = $this->_statisticdetail_3();
		$PdfOutput['StatisticDetail3'] = $Data;

		$Data = NULL;

		foreach($HelpArray as $_key => $_data){

			$this->request->data['diagramm'] = $_data;
			$Data = NULL;
			$Data = $this->diagramm();

			$PdfOutput['DiagrammData'][$_data] = $Data;
			$Diagramm = new View($this);
			$Diagramm->autoRender = false;
			$Diagramm->layout=null;
			$Diagramm->set('barcolors',$Data['barcolors']);
			$Diagramm->set('ImageDimension',$Data['ImageDimension']);
			$Diagramm->set('DataForPlot',$Data['DataForPlot']);
			$Diagramm->set('DiagrammData',$Data['DiagrammData']);
			$Diagramm->set('TableData',$Data['TableData']);
			$Diagramm->set('PriorityStatus',$Data['PriorityStatus']);
			$Diagramm->set('Headline',$Data['Headline']);
			$Diagramm->set('Key',$_data);
			$Diagramm->set('return', true);
			$DiagrammImageString = $Diagramm->render('diagramm/diagramm');
			$PdfOutput['Diagramm'][$_data] = $DiagrammImageString;
		}

		$this->set('PdfOutput',$PdfOutput);

		$this->autoRender = false;

		$this->render('pdf', 'pdf');

	}

	public function download() {

		$this->autoRender = false;
		$change_diagramm_view = end($this->request->params['pass']);

		switch ($change_diagramm_view) {

			case '2':
			$data = $this->ExpeditingTool->GetTimeRangeDelivering();

			$ImageView = new View($this);
			$ImageView->autoRender = false;
			$ImageView->layout = null;
			$ImageView->set('data',$data);
			$ImageView->set('return',true);

			$Images = $ImageView->render('diagramm/diagramm_delivery_gnatt');

 			break;

			case '3':
			$data = $this->ExpeditingTool->GetTimeRangeDelivering();
			$data = $this->ExpeditingTool->GetTimeLineDelivering($data);

			$ImageView = new View($this);
			$ImageView->autoRender = false;
			$ImageView->layout = null;
			$ImageView->set('data',$data);
			$ImageView->set('return',true);

			$Images = $ImageView->render('diagramm/diagramm_delivery_line');

 			break;

		}

		if($this->Image->DownloadImage($Images) == false){
			$this->redirect(array_merge(array('action' => 'index'),$this->request->projectvars['VarsArray']));
		}
	}

	public function diagramm() {

		App::uses('CakeTime', 'Utility');
	 	$this->layout = 'jpgraph';
		$this->autoRender = false;

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		switch ($this->request->data['case']) {
			case '1':
			if(isset($this->request->data['stoprendering']) && $this->request->data['stoprendering'] == true){
				return $this->ExpeditingTool->GetStatisticDiagrammData(true);
			} else {
				$this->ExpeditingTool->GetStatisticDiagrammData(false);
			}
			break;

			case '2':

			$SettingsArray = array();
			$data = $this->ExpeditingTool->GetTimeRangeDelivering();

			$this->set('data', $data);
			$this->set('SettingsArray',array());
			$this->render('diagramm/diagramm_delivery_gnatt', 'diagramm/modal_jpgraph');

			break;

			case '3':

			$SettingsArray = array();
			$data = $this->ExpeditingTool->GetTimeRangeDelivering();
			$data = $this->ExpeditingTool->GetTimeLineDelivering($data);
			$this->set('data', $data);
			$this->set('SettingsArray',array());
			$this->render('diagramm/diagramm_delivery_line', 'diagramm/modal_jpgraph');

			break;
		}
	}

	public function quicksearch() {
		$this->layout = 'blank';

		App::uses('Sanitize', 'Utility');

		$term = Sanitize::escape($this->request->data['Quicksearch']['searching_autocomplet']);

		$conditions =array(
			'limit' => 10,
//			'fields' => array('id','unit','cascade_id'),
			'conditions' => array(
				'Supplier.unit LIKE' => '%'.$term.'%',
				'Supplier.deleted' => 0,
			)
		);

		$Supplier = $this->Supplier->find('all', $conditions);

		$response = array();

		if(!empty($Supplier)){
			foreach ($Supplier as $key => $value) {
				$response[] = array(
					'label' => $value['Supplier']['unit'],
					'value' => $value['Supplier']['id'],
					'cascade' => $value['Supplier']['cascade_id'],
					'project' => $value['Supplier']['topproject_id']
				);
			}
		}

		$this->set('response',json_encode($response));
		$this->render('json');
	}

	public function index() {
//		$this->assing_mail_to_order_supplier();
//		$this->assing_roll_to_expediting();
//		$this->assing_contactmail_to_order();
//		$this->assing_mail_to_order();

//		$this->add_suplieres();
//		$this->add_Expediting();
//		$this->set_supplier_rights();
//		$this->set_status();
//		$this->set_extern_supplier_right(1);
//		die();

		App::uses('CakeTime', 'Utility');

		if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $landig_page = 1;
		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		if(isset($this->request->data['CascadeId']) && $this->request->data['CascadeId'] > 0){

	//		$cascadeID = $this->request->data['CascadeId'];
	//		$this->request->projectvars['VarsArray'][1] = $cascadeID;
			$this->layout = 'blank';

		}

		if($projectID == 0) $cascadeID = 0;

		$this->Navigation->GetSessionForPaging();
		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		$response = array();

		$response['xml'] = $this->Xml->DatafromXml('display_expediting','file',null);

		if(!is_object($response['xml']['settings'])){
			pr('Einstellungen konnten nicht geladen werden.');
			return;
		} else {
//			$this->set('xml',$xml['settings']);
		}
	
		$response = $this->ExpeditingTool->ExpeditingStatusMessages($response);

		// Noch eine Überprüfung einbauen, Beruchtigungszugriff

		$response['ChildrenList'] = $this->Navigation->CascadeGetNextChildren(intval($cascadeID),array('id','discription'));
		$response['CascadeForBread'] = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$response = $this->ExpeditingTool->SubmenueOnlyCascades($response);
		$response = $this->ExpeditingTool->FirstSubmenueOnlyCascades($response);
		$response['ParentCasace'] = $this->Navigation->CascadeGetParentId(intval($cascadeID));
		$response = $this->ExpeditingTool->SelectCascadeInfos($response);

		$response = $this->ExpeditingTool->SelectCascadeInfosStart($response);
		$response = $this->ExpeditingTool->SelectCascadeSkipSuppliers($response);
		$response = $this->ExpeditingTool->SelectCascadeSkipSuppliersNoEntrys($response);
		$response = $this->ExpeditingTool->SelectCascadeSuppliers($response);
		$response = $this->ExpeditingTool->AddLinkWhenEmpty($response);		
		$response = $this->ExpeditingTool->FormatHeadline($response);
		$response = $this->ExpeditingTool->ExpeditingNavi($response);
		$breads = $this->ExpeditingTool->ExpeditingBreadCrump($response);

		if(empty($response['AllCascadesUnder'])) $response['AllCascadesUnder'] = array($cascadeID);

		$response['CascadeIds'] = $this->Navigation->CascadeGetFromTestingcomp();
		$response['AllCascadesUnder'] = array_intersect($response['CascadeIds'], $response['AllCascadesUnder']);
		$response['SelectOrderByTestingcomp'] = $this->Data->SelectOrderByTestingcomp();

		$response = $this->ExpeditingTool->TableFilterOptions($response);
		$response = $this->ExpeditingTool->ChildrenListCorrection($response);
		$response = $this->ExpeditingTool->ExpeditingLinks($response);
		$response = $this->ExpeditingTool->SupplierTableInfos($response);
//		die();

		if(isset($this->request->data['CascadeId']) && $this->request->data['CascadeId'] > 0){

			$this->set('response',json_encode($response));

			$this->render('json');
			return;

		}

		$ExpeditingNavi = array();
		$SettingsArray = array();

		if(isset($response['Statistic']['supplier'])) $response['HeadLine'] .= ' > ' . $response['Statistic']['supplier'];

		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);
		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$this->request->data = $response;

		$this->Navigation->SetSessionForPaging();

		if(isset($landig_page) && $landig_page == 1){

			$this->render('landingpage','blank');
			return;

		}

		if(isset($landig_page_large) && $landig_page_large == 1){

			$this->render('index_landingpage','blank');
			return;

		}

	}

	public function detail() {

		App::uses('CakeTime', 'Utility');
	 	$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$suppliereID = $this->request->projectvars['VarsArray'][2];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		$Supplier = $this->Supplier->find('first',array('conditions' => array('Supplier.id' => $suppliereID)));

		$Testingcomp = $this->Testingcomp->find('first',array('conditions' => array('Testingcomp.id' => $Supplier['Supplier']['supplier'])));
		if(count($Testingcomp) > 0) $Supplier = array_merge($Supplier,$Testingcomp);

		$Expeditings = $this->Expediting->find('all',array('order' => array('Expediting.sequence ASC'), 'conditions' => array('Expediting.supplier_id' => $Supplier['Supplier']['id'])));

		$Priority = 5;

		foreach($Expeditings as $__key => $__Expeditings){
			$Expeditings[$__key] = $this->ExpeditingTool->ExpeditingTimePeriode($Expeditings,$__Expeditings,$__key);
			if($Expeditings[$__key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$__key]['Expediting']['priority'];
		}

		if(count($Expeditings) > 0){
			$Supplier['Expediting'] = $Expeditings;
			$Supplier['Supplier']['priority'] = $Priority;
		}

		if(isset($this->request->data['Supplier']) || $this->request->is('put')) {
 			if($this->Supplier->save($this->request->data )){

				$this->Autorisierung->Logger($suppliereID,$this->request->data['Supplier']);
				$this->Session->setFlash(__('Expediting was saved'));

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'index';
				$Terms = implode('/',$this->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->set('FormName',$FormName);

			} else {
				$this->Session->setFlash(__('Expediting could not be saved. Please, try again.'));
			}
		}

		$this->request->data = $Supplier;

		$this->set('Supplier',$Supplier);

	}

	public function add() {

		$this->layout = 'modal';
		$locale = $this->Lang->Discription();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$this->loadModel('CascadeGroup');

		$Supplier = $this->ExpeditingTool->CollectSupplierDataAdd();

		if(isset($this->request->data['Supplier']) || $this->request->is('put')) {

			$Check = $this->ExpeditingTool->SaveSupplierData($Supplier);

			if($Check === true) {

				$Supplier = $this->ExpeditingTool->CollectSupplierDataAdd();
				$Supplier = $this->ExpeditingTool->RemoveDataForAdd($Supplier);

				$this->request->data = $Supplier;

				return;

			} else {

				$this->Flash->error(__('Value could not be saved. Please, try again.',true),array('key' => 'error'));

			}

		}

		$Supplier = $this->ExpeditingTool->RemoveDataForAdd($Supplier);

		$this->request->data = $Supplier;
		$testingcomps = $this->Order->Testingcomp->find('list',array('fields'=>array('id','name')));
		$cascadegoups = $this->CascadeGroup->find('list',array('fields'=>array('id',$locale)));
		$emailaddress = $Supplier['emailaddress'];

		$arrayData = $this->Xml->DatafromXml('Order','file',null);

		$this->request->data = $this->Data->DropdownData(array('Order'),$arrayData,$this->request->data);

		$this->set(compact('testingcomps','emailaddress','cascadegoups'));

	}

	public function edit() {

		$this->layout = 'modal';
		$locale = $this->Lang->Discription();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataEdit();

		if(isset($this->request->data['Suppliere'])){

			if(isset($this->request->data['Suppliere']['Testingcomp']))$OrdersTestingcomp['Testingcomp']['Testingcomp'] =  $this->request->data['Suppliere']['Testingcomp'];
			if(isset($this->request->data['Suppliere']['Emailaddress']))$OrdersTestingcomp['Emailaddress']['Emailaddress'] =  $this->request->data['Suppliere']['Emailaddress'];

			$OrdersTestingcomp['Order']['id'] = $Supplier['Supplier']['order_id'];

			$this->ExpeditingTool->UpdateCascadeAndOrderLinking($OrdersTestingcomp);

			unset($this->request->data['Suppliere']);
		}

		if(isset($this->request->data['Supplier']) || $this->request->is('put')) {

			if($this->Supplier->save($this->request->data)){

				if(isset($this->request->data['not_closing'])) $NotClosing = 1;

				$this->Order->save($OrdersTestingcomp);
	
				// durch die unterschiedlichen Spaltennamen, müssen die Werte Expeditin und Order hiermit sycronisiert werden
				$this->ExpeditingTool->OrderExpeditingMerge($Supplier['Supplier']['order_id'],$Supplier['Supplier']['id'],'Order');
				$this->ExpeditingTool->CheckTestingcompsCascades($OrdersTestingcomp);

				//$this->Autorisierung->Logger($suppliereID,$this->request->data['Supplier']);

				$this->Flash->success(__('Value was saved'),array('key' => 'success'));

				$lastArrayURL = $this->Session->read('lastArrayURL');

				if(!isset($NotClosing)){

					$FormName = array();
					$FormName['controller'] = $lastArrayURL['controller'];
					$FormName['action'] = $lastArrayURL['action'];
					$FormName['terms'] = implode('/',$lastArrayURL['pass']);
	
					$this->set('FormName',$FormName);
	
				} else {
					$Supplier = $this->ExpeditingTool->CollectSupplierDataEdit();
				}

			} else {

				$this->Flash->error(__('Value could not be saved. Please, try again.',true),array('key' => 'error'));

			}
		}

		$this->loadModel('CascadeGroup');

		$this->request->data = $Supplier;
		$testingcomps = $this->Order->Testingcomp->find('list',array('fields'=>array('id','name')));
		$cascadegoups = $this->CascadeGroup->find('list',array('fields'=>array('id',$locale)));
		$emailaddress = $Supplier['emailaddress'];
		$arrayData = $this->Xml->DatafromXml('Order','file',null);
		$this->request->data = $this->Data->DropdownData(array('Order'),$arrayData,$this->request->data);

		$this->set(compact('testingcomps','emailaddress','cascadegoups'));
	}

	public function delete() {

		$this->layout = 'modal';
		$locale = $this->Lang->Discription();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataEdit();

		if(isset($this->request->data['Supplier']) || $this->request->is('put')) {

			if($this->Supplier->save($this->request->data)){

				$this->request->data = $Supplier;

				$this->Flash->success(__('Value was deleted'),array('key' => 'success'));

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'index';
				$Terms = $this->request->projectvars['VarsArray'];

				$Terms[2] = 0;

				$FormName['terms'] = implode('/',$Terms);
				$this->set('FormName',$FormName);

			} else {

				$this->Flash->error(__('Value could not be saved. Please, try again.',true),array('key' => 'error'));

			}

		} else {
			$this->Flash->warning(__('Should this Expediting be deleted?',true),array('key' => 'warning'));
		}

		$this->request->data = $Supplier;

	}

	public function duplicate() {

		$this->layout = 'modal';

		$locale = $this->Lang->Discription();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataEdit();
		$SupplierNew = $this->ExpeditingTool->DuplicateSupplierData($Supplier);

		if($SupplierNew === false){

			return;

		} elseif (is_array($SupplierNew)) {

			$this->request->data['Supplier'] = $Supplier['Supplier'];

			return;

		}

		if(!isset($this->request->data['step'])) $step = 1;

		if($step == 1) {

//			$xml = $this->Xml->DatafromXml('Rkl','file',null);
			if(isset($xml['settings'])) $Supplier['Xml']['Rkl'] = $xml['settings'];

//			$this->request->data['Xml'] = $Supplier['Xml'];
//			$this->request->data['Rkl'] = $Supplier['Rkl'];
			$this->request->data['Supplier'] = $Supplier['Supplier'];
			$this->Flash->warning(__('Should this element be duplicated',true) . ': ' . $Supplier['Supplier']['unit'] . ' ' . $Supplier['Supplier']['equipment'],array('key' => 'warning'));
			$this->Flash->hint(__('Passen Sie Unit, Ausrüstung und Rohrklasse an.',true),array('key' => 'hint'));
		}
	}

	 public function image($id = null) {

	 	$this->layout = 'blank';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
	 	$id = $this->request->projectvars['VarsArray'][5];

	 	$thisreportimages = $this->Supplierimage->find('first', array('conditions' => array('Supplierimage.id' => $id)));
	 	$SettingsArray = array();

	 	$this->set('SettingsArray', $SettingsArray);

	 	if(count($thisreportimages) == 1){

			$imagePath = Configure::read('supplier_folder') . $supplierID . DS . $thisreportimages['Supplierimage']['file'];

			if(file_exists($imagePath))
	 		{
	 			$imageinfo = getimagesize($imagePath);
	 			$imageContent = file_get_contents($imagePath);
	 			$imData = base64_encode($imageContent);
	 			$this->set('mime',$imageinfo['mime']);
	 			$this->set('imagePath',$imagePath);
	 			$this->set('imData',$imData);
	 		}
	 	}
	 }

	public function history() {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		$AllCascadesUnder = $this->Navigation->CascadeGetChildrenList(intval($cascadeID));

		$this->loadModel('Log');
		$this->Log->recursive = 0;

		$options = array(
			'order' => array('Log.id DESC'),
			'conditions'=>array(
				'Log.topproject_id' => $projectID,
				'Log.cascade_id' => $cascadeID,
				'Log.order_id' => $supplierID
			)
		);

		$Log = $this->Log->find('all',$options);

		$Lang = $this->Lang->Discription();
		$output = $this->Data->HistoryDataExpediting($Log,$Lang);


	}

	public function images($id = null) {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		// Überprüfung einfügen, Zugehörigkeit der Dateien
		$Supplier = $this->ExpeditingTool->CollectSupplierDataEdit();

		if(isset($this->request->data['close_reload'])){

			$lastArrayURL = $this->Session->read('lastArrayURL');

			$FormName = array();
			$FormName['controller'] = $lastArrayURL['controller'];
			$FormName['action'] = $lastArrayURL['action'];
			$FormName['terms'] = implode('/',$lastArrayURL['pass']);

			$this->set('FormName', $FormName);
			$this->set('Supplier', $Supplier);

			return;

		}

		$Supplier = $this->ExpeditingTool->ExpeditingFileUpload($Supplier,'image');
		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		if(isset($_FILES) && count($_FILES) > 0){
			$this->autoRender = false;
			return;
		}

		$Supplier = $this->ExpeditingTool->ReductExpditingTyps($Supplier);

		$this->request->data = $Supplier;

		// quick and dirty
		$ExpeditingLinks['delimage']['controller'] = 'suppliers';
		$ExpeditingLinks['delimage']['action'] = 'delimage';
		$ExpeditingLinks = $this->Autorisierung->AclCheckLinks($ExpeditingLinks);

		$SettingsArray = array();
		$this->set('SettingsArray', $SettingsArray);
		$this->set('Supplier', $Supplier);
		$this->set('ExpeditingLinks', $ExpeditingLinks);
	}

	public function getmanyfiles(){

		$this->layout = 'json';

		$this->ExpeditingTool->SendFiles();
		$out = $this->ExpeditingTool->CollectFiles();

		$this->set('request',json_encode(array('message' => $out)));

	}

	public function files($id = null) {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		if(isset($this->request->data['Expediting']['expediting_type'])) $ExpeditingType = $this->request->data['Expediting']['expediting_type'];

		// Überprüfung einfügen, Zugehörigkeit der Dateien
		$Supplier = $this->ExpeditingTool->CollectSupplierDataEdit();
		$Supplier = $this->ExpeditingTool->CollectExpeditingDetailByExpeditingType($Supplier);

		$this->set('Supplier',$Supplier);

		if(isset($this->request->data['close_reload'])){

			$lastArrayURL = $this->Session->read('lastArrayURL');

			$FormName = array();
			$FormName['controller'] = $lastArrayURL['controller'];
			$FormName['action'] = $lastArrayURL['action'];
			$FormName['terms'] = implode('/',$lastArrayURL['pass']);

			$this->set('FormName', $FormName);
			$this->set('Supplier', $Supplier);

			return;

		}

		$Supplier = $this->ExpeditingTool->ExpeditingFileUpload($Supplier,'file');

		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		if(isset($_FILES) && count($_FILES) > 0){
			$this->autoRender = false;
			return;
		}

		$Supplier = $this->ExpeditingTool->ReductExpditingTyps($Supplier);

		$this->request->data = $Supplier;

		if(isset($ExpeditingType)) $this->request->data['Order']['ExpeditingType'] = $ExpeditingType;

// quick and dirty
$ExpeditingLinks['delfile']['controller'] = 'suppliers';
$ExpeditingLinks['delfile']['action'] = 'delfile';
$ExpeditingLinks = $this->Autorisierung->AclCheckLinks($ExpeditingLinks);

		$SettingsArray = array();
		$this->set('SettingsArray', $SettingsArray);
		$this->set('Supplier', $Supplier);
		$this->set('ExpeditingLinks', $ExpeditingLinks);
	}

	public function getfile() {

	 	$this->layout = 'blank';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
		$fileID = $this->request->projectvars['VarsArray'][5];

		// Überprüfung einfügen, Zugehörigkeit der Dateien
		$file = $this->Supplierfile->find('first', array('conditions' => array('Supplierfile.id' => $fileID)));

		if(count($file) == 0) return;

		$savePath = Configure::read('supplier_folder') . $file['Supplierfile']['supplier_id'] . DS . $file['Supplierfile']['file'];

		if(file_exists($savePath) == false) return;

		$this->set('file', $file);
		$this->set('savePath', $savePath);

	}

	public function delfile($id = null) {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];
	 	$id = $this->request->projectvars['VarsArray'][5];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataSingleModal('file');
		$Supplier = $this->ExpeditingTool->CollectExpeditingDetailByExpeditingType($Supplier);

		if(isset($Supplier['Supplierfile']['file_exists'])) $Supplier['FlashMessages'][]  = array('type' => 'warning','message' => __('Should this file be deleted.',true));
		else $Supplier['FlashMessages'][]  = array('type' => 'warning','message' => __('File not found.',true) . ' ' . __('Only the database entry is deleted',true));

		$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

		if(isset($this->request->data['Supplierfile'])){

			if($this->Supplierfile->delete($Supplier['Supplierfile']['id'])){

				$this->Flash->success(__('The database entry was deleted.',true),array('key' => 'success'));

				$Supplier['MailMessage']  = "Es wurde eine hochgeladene Datei aus dem Expediting gelöscht.\n";
				$Supplier['MailMessage'] .= __('Place/Equipment',true) . " " . $Supplier['HeadLine'] . ' ' . $Supplier['Expediting']['description'] . "\n";
				$Supplier['MailMessage'] .= __('User',true) . " " . $this->Auth->user('name') . " (" . $this->Auth->user('Testingcomp.firmenname') . ")\n";
				$Supplier['MailMessage'] .= __('Name of file') . " " . $Supplier['Supplierfile']['basename'];
		
				$Supplier = $this->ExpeditingTool->SendMessageAfterUpload($Supplier);
		
				$Supplier = $this->MessagesTool->GenerateFlashMessage($Supplier);

				// Speicherpfad für den Upload
				$delPath = Configure::read('supplier_folder') . $Supplier['Supplierfile']['supplier_id'] . DS . $Supplier['Supplierfile']['file'];
				$delFile = new File($delPath);

				$SupplierLog = $Supplier['Supplierfile'];

				// Wenn die Dateien existieren werden sie gelöscht
				if(file_exists($delFile->path)){
					if($delFile->delete()){
			 			$this->Autorisierung->Logger($Supplier['Supplierfile']['supplier_id'],$SupplierLog);
					}
					else {
						$this->Flash->error(__('File was not delete.',true),array('key' => 'error'));
					}
				}
				else {
					$this->Flash->error(__('The File does not exist.',true),array('key' => 'error'));
				}

				$this->Flash->success(__('File has been deleted.',true),array('key' => 'success'));

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'overview';
				$Terms = implode('/',$this->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->set('FormName',$FormName);


			} else {

				$this->Flash->error(__('File could not be deleted.',true),array('key' => 'error'));

			}
		} else {


		}

		$this->request->data = $Supplier;

		$SettingsArray = array();

		$this->set('SettingsArray',$SettingsArray);

	}

	public function delimage($id = null) {

		$this->layout = 'modal';

	 	$id = $this->request->projectvars['VarsArray'][5];

		$Supplier = $this->ExpeditingTool->CollectSupplierDataSingleModal('image');

		if(isset($this->request->data['Supplierimage'])){

			if($this->Supplierimage->delete($Supplier['Supplierimage']['id'])){

				// Speicherpfad für den Upload
				$delPath = Configure::read('supplier_folder') . $Supplier['Supplierimage']['supplier_id'] . DS . $Supplier['Supplierimage']['file'];
				$delFile = new File($delPath);

				$delInfo = array('image' => 0);

				// Wenn die Dateien existieren werden sie gelöscht
				if(file_exists($delFile->path)){
					if($delFile->delete()){
						if($this->Supplierimage->delete($id)){
			 				$this->Autorisierung->Logger($Supplier['Supplierimage']['supplier_id'],$Supplier);
						}
					}
					else {
						$this->Flash->error(__('Image was not delete.',true),array('key' => 'error'));
					}
				}
				else {
					$this->Flash->error(__('The image does not exist.',true),array('key' => 'error'));
				}

				$this->Flash->success(__('Value has been deleted.',true),array('key' => 'success'));

				$FormName['controller'] = 'suppliers';
				$FormName['action'] = 'overview';
				$Terms = implode('/',$this->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->set('FormName',$FormName);


			} else {

				$this->Flash->error(__('Image could not be deleted.',true),array('key' => 'error'));

			}
		} else {

			$this->Flash->warning(__('Should this image be deleted.',true),array('key' => 'warning'));

		}
		
		$this->request->data = $Supplier;

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'expeditings','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray',$SettingsArray);

	}

	public function overview(){

		App::uses('CakeTime', 'Utility');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$supplierID = $this->request->projectvars['VarsArray'][2];

		if(isset($this->request->data['expediting_type'])) $ExpeditingType = $this->request->data['expediting_type'];

		App::uses('CakeTime', 'Utility');

		if(isset($this->request->data['expediting_add_id'])){

			$this->set('ExpeditingAddId',$this->request->data['expediting_add_id']);
			$this->Flash->success(__('Value has been saved. Drag the new value to the right position.',true),array('key' => 'success'));

		}

		$this->Navigation->ModulNavigation($cascadeID,'expediting');

		if($this->Session->check('OpenSupplierEdit')){

			$this->Session->delete('OpenSupplierEdit');
			$this->set('OpenSupplierEdit',1);

		}


		$Supplier = $this->ExpeditingTool->CollectSupplierData();

		if($Supplier['Supplier']['expeditingset_id'] > 0 && $Supplier['Supplier']['dynamic_termination'] == 1){
			$this->Flash->hint(__('The dynamic date adjustment for this expediting is active.',true). ' ' . __('If the order or delivery date is changed, the expediting dates will be adjusted.'),array('key' => 'hint'));
		}

		if($Supplier['Supplier']['stop_mail'] == 1) $this->Flash->warning(__('The email dispatch for this equipment has been deactivated.',true),array('key' => 'warning'));
		if($Supplier['Supplier']['stop_mail'] == 0) $this->Flash->hint(__('The email dispatch for this equipment is active.',true),array('key' => 'hint'));
		if(empty($Supplier['ToMail'])) $this->Flash->warning(__('No email adresses available.',true),array('key' => 'warning'));

		$xml = $this->ExpeditingTool->AddXml($Supplier);

		$this->request->data = $Supplier;

		if(isset($ExpeditingType)) $this->request->data['ChooseExpeditingStep'] = $ExpeditingType;

		$breads = array();

		$breads[] = array(
						'discription' => __('Expediting manager', true),
						'controller' => 'expeditings',
						'action' => 'index',
						'pass' => null
		);

		$this->set('SettingsArray',array());
		$this->set('xml',$xml);
		$this->set('breads',$breads);

	}

	protected function set_status() {
return;

		App::uses('CakeTime', 'Utility');

		$Supplier = $this->Supplier->find('all');

		foreach($Supplier as $_key => $_Supplier){

			if(isset($Testingcomps[$Supplier[$_key]['Supplier']['supplier']])) $Supplier[$_key]['Supplier']['supplier'] = $Testingcomps[$Supplier[$_key]['Supplier']['supplier']];

			$Expeditings = $this->Expediting->find('all',array('order' => array('Expediting.sequence ASC'), 'conditions' => array('Expediting.supplier_id' => $_Supplier['Supplier']['id'])));

			$Priority = 5;

			foreach($Expeditings as $__key => $__Expeditings){
				$Expeditings[$__key] = $this->ExpeditingTool->ExpeditingTimePeriode($Expeditings,$__Expeditings,$__key);
				if($Expeditings[$__key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$__key]['Expediting']['priority'];
			}

			$Supplier[$_key]['Expediting'] = $Expeditings;

			$Supplier[$_key]['Supplier']['priority'] = $Priority;

$data['id'] = $Supplier[$_key]['Supplier']['id'];
$data['status'] = $Priority;
$this->Supplier->save($data);
$data = array();

		}
die();
	}

	protected function add_Expediting() {

		die('überarbeiten');
return;

		$SettingsArray = array();
		$DataArray = array();
		$ExpeditingArray = array(0,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48);
		$ExpeditingArray = array_flip($ExpeditingArray);

		$ExpeditingDEscriptionArray[22] = 'Übergabe Vorprüfunterlagen an R-ST';
		$ExpeditingDEscriptionArray[25] = 'Freigabe Vorprüfunterlagen R-ST';
		$ExpeditingDEscriptionArray[28] = 'Lieferung Vormaterial';
		$ExpeditingDEscriptionArray[31] = 'Fertigungsbeginn';
		$ExpeditingDEscriptionArray[34] = 'Haltepunkte Prüffolgeplan (ZfP, Wärmebehandlung)';
		$ExpeditingDEscriptionArray[37] = 'Fertigungsende';
		$ExpeditingDEscriptionArray[40] = 'Abnahme (Druckprüfung, Maßkontrolle)';
		$ExpeditingDEscriptionArray[43] = 'Lieferung';
		$ExpeditingDEscriptionArray[46] = 'Eingang Dokumentation';

		$ExpeditingSequence[22] = 1;
		$ExpeditingSequence[25] = 2;
		$ExpeditingSequence[28] = 3;
		$ExpeditingSequence[31] = 4;
		$ExpeditingSequence[34] = 5;
		$ExpeditingSequence[37] = 6;
		$ExpeditingSequence[40] = 7;
		$ExpeditingSequence[43] = 8;
		$ExpeditingSequence[46] = 9;

		$row = 0;
		if (($handle = fopen(Configure::read('root_folder') . 'csv' . DS . 'expediting_complett.csv', 'r')) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
				$data_new = array();
				foreach($data as $_key => $_data){
					foreach($ExpeditingArray as $__key => $__ExpeditingArray){
						if(isset($data[$__key])) $data_new[$__key] = $data[$__key];
					}
				}
				$x = 1;
				$DataArray[$data_new[0]] = $data_new;

				unset($DataArray[$data_new[0]][0]);

        		$row++;
			}
			fclose($handle);
		}

		$DataNewSort = array();

		foreach($DataArray as $_key => $_DataArray){
			$x = 0;
			foreach($_DataArray as $__key => $_data_new){
				if(is_int($x / 3) == true){

					$DataNewLoad = array(
										'supplier_id' => $_key,
										'categorie' => null,
										'sequence' => $ExpeditingSequence[$__key],
										'description' => $ExpeditingDEscriptionArray[$__key],
										'date_soll' => $this->Formating->DateFromExcel($_DataArray[$__key]),
										'date_ist' => $this->Formating->DateFromExcel($_DataArray[$__key + 1]),
										'status' => $_DataArray[$__key + 2]
									);

				$this->Expediting->create();
				$this->Expediting->save($DataNewLoad);

				}
				$x++;
			}
		}
	}

	protected function set_extern_supplier_right($id){

		$this->Testingcomp->recursive = -1;
		$Testingcomp = $this->Testingcomp->find('first',array('conditions'=>array('Testingcomp.id' => $id)));

		$Ordes = $this->Order->find('list',array(
										'fields' => array('id','cascade_id'),
										'conditions' => array('Order.testingcomp_id' => $id)
										)
									);

		$x = 0;

		foreach($Ordes as $_key => $_Order){

			$options = array();
			$options['Order']['id'] = $_key;
			$options['Testingcomp']['Testingcomp'] = array($id);

			$CascadeGetParentList = $this->Navigation->CascadeGetParentList($_Order,array());
			$CascadeGetParentListInsert['Cascade'] = array();

			if(count($CascadeGetParentList) > 0){
				foreach($CascadeGetParentList as $_key => $_CascadeGetParentList){
					$CascadeGetParentListInsert['Cascade'][] = $_CascadeGetParentList['Cascade']['id'];
				}
				$options['Cascade'] = $CascadeGetParentListInsert;

			} else {
				pr('nix');
			}

			if ($this->Order->save($options)) {
				$x++;
//				pr('okay');
			} else {
//				pr('nicht okay');
			}
		}

		pr($x);
	}

	protected function set_supplier_rights(){
return;

		$supplieres = $this->Supplier->find('all');

		$this->Testingcomp->recursive = -1;

		foreach($supplieres as $_key => $_supplieres){
			$options = array();
			$options['Order']['id'] = $_supplieres['Supplier']['order_id'];
			$options['Order']['planer'] = $_supplieres['Supplier']['planner'];
			$options['Order']['description'] = $_supplieres['Supplier']['description'];
			$options['Order']['equipment_typ'] = $_supplieres['Supplier']['equipment_typ'];
			$options['Order']['area_of_responsibility'] = $_supplieres['Supplier']['area_of_responsibility'];
//			pr($_supplieres['Supplier']['cascade_id']);
			$Testingcomp = $this->Testingcomp->find('first',array('conditions'=>array('Testingcomp.name' => trim($_supplieres['Supplier']['supplier']))));

			if(!isset($Testingcomp['Testingcomp']['id']))pr($_supplieres);

			$options['Testingcomp']['Testingcomp'] = array($Testingcomp['Testingcomp']['id'],1);

			$CascadeGetParentList = $this->Navigation->CascadeGetParentList($_supplieres['Supplier']['cascade_id'],array());
			$CascadeGetParentListInsert['Cascade'] = array();

			if(count($CascadeGetParentList) > 0){
				foreach($CascadeGetParentList as $_key => $_CascadeGetParentList){
					$CascadeGetParentListInsert['Cascade'][] = $_CascadeGetParentList['Cascade']['id'];
				}
				$options['Cascade'] = $CascadeGetParentListInsert;

			} else {
				pr('nix');
			}

			if ($this->Order->save($options)) {
				pr('okay ' . $options['Order']['id']);
			} else {
				pr('nicht okay');
			}

		}

		die();
	}

	protected function add_suplieres() {
return;

		$SettingsArray = array();
		$DataArray = array();
		$TableRows = array();

		$this->loadModel('SupliereImport');

		$Testingcomp = $this->Testingcomp->Testingcompcat->find('first',array('conditions' => array('Testingcompcat.identification' => array('sp'))));

		$Testingcomps = Hash::combine($Testingcomp, 'Testingcomp.{n}.id','Testingcomp.{n}.name');

		$Schema = $this->SupliereImport->schema();
		foreach($Schema as $_key => $_schema){
			$TableRows[] = $_key;
		}

		$row = 0;
		if (($handle = fopen(Configure::read('root_folder') . 'csv' . DS . 'expediting_complett.csv', 'r')) !== FALSE) {
    	while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
		$DataArray[$row] = $data;
        $row++;
		}
		fclose($handle);
		}
$x = 1;
		foreach($DataArray as $_key => $_DataArray){
			$RowsForSQL = array();
			foreach($TableRows as $__key => $_TableRows){

				if($Schema[$_TableRows]['type'] == 'date'){
					$_DataArray[$__key] = date('Y-m-d',strtotime($_DataArray[$__key]));
				}
				if($_TableRows == 'unit'){
					$_DataArray[$__key] = str_replace(' ','',$_DataArray[$__key]);
					$_DataArray[$__key] = str_replace('U','',$_DataArray[$__key]);
					$_DataArray[$__key] = str_replace('u','',$_DataArray[$__key]);
					$_DataArray[$__key] = 'U' . $_DataArray[$__key];
				}
				if($_TableRows == 'equipment'){
					$_DataArray[$__key] = str_replace(' ','',$_DataArray[$__key]);
				}
				if($_TableRows == 'supplier' && trim($_DataArray[$__key]) != ''){
					$supplier = trim($_DataArray[$__key]);
					$testingcomp_id = array_search($supplier,$Testingcomps);
					if($testingcomp_id != false){
						$_DataArray[$__key] = $testingcomp_id;
					}
					unset($supplier);
				}

				$RowsForSQL[$_TableRows] = trim($_DataArray[$__key]);
			}

			$DataArray[$_key] = $RowsForSQL;

			$Cascade = $this->Cascade->find('first',array('conditions' => array('Cascade.discription' => $DataArray[$_key]['unit'])));

			if(count($Cascade) == 0) {
				pr('"'.$DataArray[$_key]['unit'].'"');
			}
			$Order = $this->Order->find('all',array('conditions' => array('Order.cascade_id' => $Cascade['Cascade']['id'],'Order.auftrags_nr' => $DataArray[$_key]['equipment'])));
			if(count($Order) > 0){

			} elseif(count($Order) == 0){
				$InsertOrder = array();
				$InsertOrder['auftrags_nr'] = $DataArray[$_key]['equipment'];
				$InsertOrder['topproject_id'] = $Cascade['Cascade']['topproject_id'];
				$InsertOrder['cascade_id'] =  $Cascade['Cascade']['id'];
				$InsertOrder['material'] = $DataArray[$_key]['material_id'];
				$InsertOrder['equipmenttyp'] = $DataArray[$_key]['equipment_typ'];
				$InsertOrder['examination_object'] = $DataArray[$_key]['description'];
				$InsertOrder['order_no'] = $DataArray[$_key]['delivery_no'];
				$InsertOrder['referenz_no'] = $DataArray[$_key]['supplier_project_no'];
				$InsertOrder['manufacturer'] = $DataArray[$_key]['supplier'];
				$InsertOrder['remarks'] = $DataArray[$_key]['peculiarity'];
				$InsertOrder['planer'] = $DataArray[$_key]['planner'];
				$InsertOrder['contact_person'] = $DataArray[$_key]['contact_person'];
				$InsertOrder['contact_person_mail'] = $DataArray[$_key]['contact_person_mail'];
				$InsertOrder['kategorie'] = $DataArray[$_key]['kategorie'];
				$InsertOrder['areaofresponsibility'] = $DataArray[$_key]['area_of_responsibility'];

				$this->Order->create();
				$this->Order->save($InsertOrder);
				$DataArray[$_key]['order_id'] = $this->Order->getLastInsertID();
				$DataArray[$_key]['cascade_id'] = $Cascade['Cascade']['id'];
				$DataArray[$_key]['topproject_id'] = $Cascade['Cascade']['topproject_id'];
				$DataArray[$_key]['testingcomp_id'] = 0;


			}
if($DataArray[$_key]['order_id'] == 0){
pr($x);
pr($DataArray[$_key]);
pr('_____________');
		}
$x++;
		}

		foreach($DataArray as $_key => $_DataArray){
			if($_DataArray['order_id'] == 0) continue;
			$this->Supplier->create();
			$this->Supplier->save($_DataArray);
		}

	}

	protected function assing_contactmail_to_order(){
return;

		$this->Order->Emailaddress->recursive = -1;

		$Supplier = $this->Supplier->find('list',array('fields' => array('id','order_id')));
		$InputData = array();

		foreach($Supplier as $__key => $__data){

//			$Email = $this->Order->Emailaddress->find('first',array('fields' => array('id'),'conditions' => array('Emailaddress.email' => array($__data))));

//			if(count($Email) == 0) continue;

			$InputData['Order']['id'] = $__data;
			$InputData['Emailaddress']['Emailaddress'] = 12;
			$this->Order->save($InputData);
			$InputData = array();
		}


		die('fertig');

	}

	protected function assing_roll_to_expediting() {
return;

//		$Key = 8;

		$Description[0]['Description'] = 'Übergabe Vorprüfunterlagen an R-ST';
		$Description[0]['Roll'] = array(2,3,7);

		$Description[1]['Description'] = 'Freigabe Vorprüfunterlagen R-ST';
		$Description[1]['Roll'] = array(2,3,7);

		$Description[2]['Description'] = 'Lieferung Vormaterial';
		$Description[2]['Roll'] = array(2,3,4,7);

		$Description[3]['Description'] = 'Fertigungsbeginn';
		$Description[3]['Roll'] = array(2,3,4,7);

		$Description[4]['Description'] = 'Haltepunkte Prüffolgeplan (ZfP, Wärmebehandlung)';
		$Description[4]['Roll'] = array(2,3,4,7);

		$Description[5]['Description'] = 'Fertigungsende';
		$Description[5]['Roll'] = array(2,4);

		$Description[6]['Description'] = 'Abnahme (Druckprüfung, Maßkontrolle)';
		$Description[6]['Roll'] = array(2,4);

		$Description[7]['Description'] = 'Lieferung';
		$Description[7]['Roll'] = array(2,3);

		$Description[8]['Description'] = 'Eingang Dokumentation';
		$Description[8]['Roll'] = array(2,3);

		$Expediting = $this->Expediting->find('list',array('fields' => array('id'),'conditions' => array('Expediting.description' => $Description[$Key]['Description'])));

		foreach($Expediting as $_key => $_data){
				$SaveRolls['Expediting']['id'] = $_data;
				$SaveRolls['Roll']['Roll'] = $Description[$Key]['Roll'];
				$this->Expediting->save($SaveRolls);
		}

		die('fertig');
		
	}

	protected function assing_mail_to_order_supplier() {
return;

		$ThisSupplier[35] = 'Kosik';
		$ThisSupplier[36] = 'Mayr+Wilhelm';
		$ThisSupplier[37] = 'A&W';
		$ThisSupplier[38] = 'Bertsch';
		foreach($ThisSupplier as $_key => $_data){

			$Supplier = $this->Supplier->find('list',array('fields' => array('id','order_id'),'conditions' => array('Supplier.supplier' => $_data)));
			$Email = $this->Order->Emailaddress->find('first',array('fields' => array('id'),'conditions' => array('Emailaddress.id' => array($_key))));
			if(count($Supplier) == 0) continue;
			if(count($Email) == 0) continue;
			foreach($Supplier as $__key => $__data){
				$InputData['Order']['id'] = $__data;
				$InputData['Emailaddress']['Emailaddress'] = array($_key);
				$this->Order->save($InputData);
				$InputData = array();
			}
		}
		pr('okay');
	}

	protected function assing_mail_to_order() {
return;

//		$Units[16] = array('U691','U611','U451','U631','U641','U664','U891.2');
//		$Units[17] = array('U691','U611','U451','U631','U641','U664','U891.2');

//		$Units[14] = array('U651','U763','U661','U662','U663','U681','U801','U802','U822','U823');
//		$Units[15] = array('U651','U763','U661','U662','U663','U681','U801','U802','U822','U823');

//		$Units[20] = array('U491','U821','U891.1');
//		$Units[32] = array('U491','U821','U891.1');

//		$Units[18] = array('U831');
//		$Units[19] = array('U831');

		$this->Order->Emailaddress->recursive = -1;


		foreach($Units as $_key => $_data){

			$Supplier = $this->Supplier->find('list',array('fields' => array('id','order_id'),'conditions' => array('Supplier.unit' => $_data)));
			$Email = $this->Order->Emailaddress->find('first',array('fields' => array('id'),'conditions' => array('Emailaddress.id' => array($_key))));

			if(count($Email) == 0) continue;

			$InputData = array();

			foreach($Supplier as $__key => $__data){
				$InputData['Order']['id'] = $__data;
				$InputData['Emailaddress']['Emailaddress'] = array($_key);
				$this->Order->save($InputData);
				$InputData = array();
			}
		}

		die('fertig');
	}
}
