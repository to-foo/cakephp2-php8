<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
/**
 * Reportnumbers Controller
 *
 *
 * @property Reportnumber $Reportnumber
 */
class ReportnumbersController extends AppController {

	public $components = array('Auth','Acl','Csv','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Search','Signing','Xml','Data','Drops','RequestHandler','Image','Pdf','Qualification','Additions','Formating','Testinstruction','Repairtracking','Rest','SaveData','MoveData','Report','MonitoringTool','ExpeditingTool','Templates','Soap','Depentency');
	public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html','Additions');
	public $layout = 'ajax';
	protected $writeprotection = false;

	// Das ist ein Testkommentar für GIT

	function beforeFilter() {

		$this->Navigation->GetSessionForPaging();

		if($this->RequestHandler->isAjax()) {
			if (!$this->Auth->login()) {
				header('Requires-Auth: 1');
			}
		}

		if (!$this->Auth->login()) {
			//			$this->redirect(array('controller' => 'users', 'action' => 'logout'));
		}

		// muss man noch ne Funkton draus machen
		$Auth = $this->Session->read('Auth');
              /*
		 if(!($Auth['User']['id'])){
		 $this->redirect(array('controller' => 'users', 'action' => 'logout'));
		 }
		 */
		App::import('Vendor', 'Authorize');
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		$this->loadModel('User');
		$this->loadModel('Topproject');

		$this->Autorisierung->Protect();

		$this->Lang->Choice();
		$this->Lang->Change();
		$this->Navigation->ReportVars();

		$noAjaxIs = 0;
		$noAjax = array('image','pdf','printweldlabel','printqrcode','quicksearch','getfile','export','printresultcsv','printrevisions','modulchilddata','testinstruction','checkdigipipe');

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

		$lang = $this->Lang->Discription();
		$this->request->lang = $lang;

		$this->Reportnumber->Report->Reportlock->recursive = -1;
		$this->writeprotection = 0 < $this->Reportnumber->Report->Reportlock->find('count', array('conditions'=>array('Reportlock.topproject_id'=>$this->request->projectID, 'Reportlock.report_id'=>$this->request->reportID)));

		$this->set('writeprotection', $this->writeprotection);
		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->set('locale', $lang);
	 	$this->set('SettingsArray', array());

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}

		$SettingsArray = array();
		$this->set('SettingsArray', $SettingsArray);


	}

	function afterFilter() {

		$this->Navigation->lastURL();
		$this->Navigation->SetSessionForPaging();

	}

	public function errors() {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];


		$this->Reportnumber->recursive = 0;
		$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$this->request->reportnumberID)));

	 	$reportnumber = $this->Data->GetReportData($reportnumber,array('Generally','Specific','Evaluation','Archiv'));

	 	$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren ;

		$xml = $this->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

	 	$ReportGenerally = $this->request->tablenames[0];
	 	$ReportSpecific = $this->request->tablenames[1];
	 	$ReportEvaluation = $this->request->tablenames[2];
	 	$ReportArchiv = $this->request->tablenames[3];
	 	$ReportSettings = $this->request->tablenames[4];
	 	$ReportPdf = $this->request->tablenames[5];

		$this->loadModel($ReportEvaluation);
		$Evaluations = $this->$ReportEvaluation->find('all', array('conditions' => array('reportnumber_id' => $this->request->reportnumberID,'deleted' => 0)));

		$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

		unset($reportnumber[$ReportEvaluation]);

		$reportnumber[$ReportEvaluation] = $Evaluations;

	 	$reportnumber = $this->Data->RevisionCheckTime($reportnumber);
		$this->Image->AdditionallyLogoOnOff($xml,$reportnumber);

		$this->request->data = $reportnumber;

		$unevaluated = array();

		foreach($reportnumber[$ReportEvaluation] as $eval) {
			if(isset($eval[$ReportEvaluation]['result'])){
				if($eval[$ReportEvaluation]['result'] == '-') {
					$unevaluated[$eval[$ReportEvaluation]['description']] = $eval[$ReportEvaluation]['description'];
				}
			}
		}

		$errors = $this->Reportnumber->getValidationErrors($xml, $reportnumber, $verfahren);

		// Digitale Unterschriften vorraussetzen der Configwert bestimmt die Stufe
		if(Configure::check('SignsError') && Configure::read('SignsError') > 0) {
			$step = Configure::read('SignsError');
			$errors = $this->Signing->ValidateSign($step,$errors,$id,$reportnumber,$xml,$ReportGenerally);
		}

		if($reportnumber['Reportnumber']['status'] > 0){
			$errors = array();
		}

		$ReportPrintMenue = $this->Navigation->createReportPrintMenue($reportnumber);
		$EmailAdresses = $this->Reportnumber->getMailAdresses($xml, $reportnumber);
		$this->Data->getReportSigns($xml, $reportnumber);

		$lastArrayURL = $this->Session->read('lastArrayURL');

		$FormName['controller'] = $lastArrayURL['controller'];
		$FormName['action'] = $lastArrayURL['action'];
		$FormName['terms'] = implode('/', $lastArrayURL['pass']);

		$this->set('EmailAdresses',$EmailAdresses);
		$this->set('FormName',$FormName);

		$this->set(compact('xml','errors','unevaluated', 'reportnumber'));

		$this->Autorisierung->IsThisMyReport($id);

		if($this->request->action == 'emailreport' || $this->request->action == 'status1') {

			if(count($errors) > 0) return $errors;
			else return array();

		}
	}

	public function results() {

		$this->layout = 'modal';
		$this->loadModel('Order');

		$projectID = $this->request->projectID;
		$orderKat = $this->request->orderKat;
		$orderID = $this->request->orderID;

		$thisReport = $this->Order->find('first',array(
				'conditions' => array(
						'Order.id' => $orderID,
						'Order.topproject_id' => $projectID
				)
		)
				);

		$options = array(
				'Order.topproject_id' => $thisReport['Order']['topproject_id'],
				'Order.auftrags_nr' => $thisReport['Order']['auftrags_nr'],
				'Order.kks' => $thisReport['Order']['kks'],
		);


		if(isset($this->request->reportID) && $this->request->reportID == 1){
			$options = array(
					'Order.topproject_id' => $thisReport['Order']['topproject_id'],
					'Order.id' => $orderID
			);
		}

		$this->paginate = array(
				'conditions' => array($options),
				'order' => array('id' => 'desc'),
				'limit' => 25
		);

		$this->Reportnumber->recursive = 0;
		$results = $this->paginate('Reportnumber');

		foreach($results as $_key => $_results){
			$Verfahren = ucfirst($_results['Testingmethod']['value']);
			$verfahren = $_results['Testingmethod']['value'];
			$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

			if(!$this->$ReportEvaluation){
				$this->loadModel($ReportEvaluation);
			}

			$thisEvaluation = $this->$ReportEvaluation->find('all',array(
					'fields' => array('DISTINCT description'),
					'conditions' => array(
							'reportnumber_id' => $_results['Reportnumber']['id']
					)
			)
					);

			$results[$_key]['ReportEvaluation'] = $thisEvaluation;

		}

		$this->set('results',$results);
	}

	public function index() {

		// Die IDs des Projektes und des Auftrages werden getestet

		$this->Navigation->ResetSessionForPaging();
		$breads = $this->Navigation->Breads(null);

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];

		$this->loadModel('Cascade');
		$this->loadModel('Order');

		$this->Navigation->ModulNavigation($cascadeID,'ndt');

		$cascade = $this->Cascade->find('first',array('conditions' => array('Cascade.id' => $cascadeID)));

		if(!isset($this->request['data']['Reportnumber']['searching'])){

			// Es wird das in der Auswahl gewählte Project in einer Session gespeichert
//			$this->Session->write('projectURL', $projectID);
			$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		}

		elseif(isset($this->request['data']['Reportnumber']['searching']) && $this->request['data']['Reportnumber']['hidden'] == 2){
			$options = array('conditions' => array(
					'Order.topproject_id' => $this->request->projectID,
					'Order.kks' => $this->request['data']['Reportnumber']['searching']
			)
			);

			$this->Order->recursive = -1;
			$OrderQuickSearch = $this->Topproject->Order->find('all', $options);

			// Wenn kein Ergebis vorliegt
			if(count($OrderQuickSearch) == 0){
				$this->Session->setFlash(__('Sorry, no Searchresults.'));
				$this->redirect(array('controller' => 'orders', 'action' => 'index', $this->request->projectID));
			}

			$this->Order->recursive = 1;

			$projectID = $OrderQuickSearch[0]['Order']['topproject_id'];
			$orderID = $OrderQuickSearch[0]['Order']['id'];
		}

		$project = null;

		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));

		$order = array();
		// nochmal die Daten des Aufrtags  mit den Prüfberichten aus der Datenbank holen
		$options = array('conditions' => array('Order.id' => $orderID));

		$order = $this->Order->find('first', $options);

		$orderID = $order['Order']['id'] ?? -1;

		$this->Reportnumber->recursive = -1;

		if($this->Auth->user('Roll.id') > 4){
			$testingcomp_option['Reportnumber.testingcomp_id'] = $this->Auth->user('Testingcomp.id');
		} else {
			$testingcomp_option = array();
		}

		$ReportnumberCountOption = array(
										'conditions'=>array(
											'Reportnumber.order_id' => $orderID,
											$testingcomp_option
											)
										);

		if(AuthComponent::user('Testingcomp.extern') == 1){

			$ReportnumberCountOption = array(
										'conditions'=>array(
											'Reportnumber.topproject_id' => $projectID,
											)
										);

		}


		$ReportnumberCount = count($this->Reportnumber->find('first',$ReportnumberCountOption));

		if($ReportnumberCount == 1){
			$ReportnumberLink = array('description' => __('Show', true),'controller' => 'reportnumbers','action' => 'show');
		} else {
			$ReportnumberLink = array('description' => __('Create', true),'controller' => 'testingmethods','action' => 'listing');
		}

		$reportsArrayContainer = $this->Navigation->SubMenue(
				'Report',
				'Topproject',
				$ReportnumberLink['description'],
				$ReportnumberLink['action'],
				$ReportnumberLink['controller'],
				$this->request->projectvars['VarsArray']
				);

		// Wenn in diesem Auftrag ein Progress dargestellt werdn soll
		if(Configure::read('DevelopmentsEnabled') == true){
			$reportsDevelopmentMenu = $this->Data->DevelopmentMenue($order,array());
			$this->set('reportsDevelopmentMenu',$reportsDevelopmentMenu);
		}

		$ExpeditingStatus = $this->ExpeditingTool->ExpeditingStatus($orderID);

//		if($ExpeditingStatus != false) $reportsArrayContainer = array_merge ($reportsArrayContainer,$ExpeditingStatus);


		$status = null;

		if(isset($cascade['Cascade']) && $cascade['Cascade']['orders'] > 0){

			$this->request->projectvars['VarsArray'] [3] = 0;
                        $this->request->projectvars['VarsArray'] [4] = 0;
			$OrderMenue['haedline'] = __('Order functions',true);
			$OrderMenue['classes'] = array();
			$OrderMenue['link_class'] = array();
			$OrderMenue['parms'] = array();
			$OrderMenue['controller'] = array();
			$OrderMenue['action'] = array();
			$OrderMenue['discription'] = array();

			if($order['Order']['status'] == 0){
				$OrderMenue['classes'][0] = 'icon_discription icon_edit';
				$OrderMenue['link_class'][0] = 'modal';
				$OrderMenue['parms'][0] = null;
				$OrderMenue['controller'][0] = 'orders';
				$OrderMenue['action'][0] = 'edit/'.implode('/',$this->request->projectvars['VarsArray']);
				$OrderMenue['discription'][0] = __('Edit', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'];

				$this->loadModel('Orderfile');
				$orderfiles = $this->Orderfile->find('count', array('conditions' => array('Orderfile.order_id' => $orderID)));

				$OrderMenue['classes'][1] = 'icon_discription icon_files';
				$OrderMenue['link_class'][1] = 'modal';
				$OrderMenue['parms'][1] = null;
				$OrderMenue['controller'][1] = 'orders';
				$OrderMenue['action'][1] = 'files/'.implode('/',$this->request->projectvars['VarsArray']);
				$OrderMenue['discription'][1] =  __('Files', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'] . ' (' . $orderfiles . ' ' . __('Files', true) . ')';

			}


			// Wenn der Status des Auftrages auf 1 steht, kann kein Prüfbericht mehr angelegt werden
			if($order['Order']['status'] == 1){
			}

			if($order['Order']['status'] == 0){
				$status = array('number' => 0, 'value' => __('open', true), 'class' => 'icon_discription icon_status_open');
			}
			if($order['Order']['status'] == 1){
				$status = array('number' => 1, 'value' => __('closed', true), 'class' => 'icon_discription icon_status_close');
			}

			$OrderMenue['classes'][2] = $status['class'];
			$OrderMenue['link_class'][2] = 'modal';
			$OrderMenue['parms'][2] = null;
			$OrderMenue['controller'][2] = 'orders';
			$OrderMenue['action'][2] = 'status/'.implode('/',$this->request->projectvars['VarsArray']).'/1';
			$OrderMenue['discription'][2] =  __('Change status ', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'] . ' Status: ' . $status['value'];
/*
			$OrderMenue['classes'][3] = 'icon_discription icon_invoices';
			$OrderMenue['link_class'][3] = 'modal';
			$OrderMenue['parms'][3] = null;
			$OrderMenue['controller'][3] = 'invoices';
			$OrderMenue['action'][3] = 'overview/'.implode('/',$this->request->projectvars['VarsArray']);
			$OrderMenue['discription'][3] =  __('Invoice', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'] . ' Status: '. $status['value'];
*/
			$OrderMenue['classes'][4] = 'icon_discription icon_delete';
			$OrderMenue['link_class'][4] = 'modal';
			$OrderMenue['parms'][4] = null;
			$OrderMenue['controller'][4] = 'orders';
			$OrderMenue['action'][4] = 'delete/'.implode('/',$this->request->projectvars['VarsArray']);
			$OrderMenue['discription'][4] =  __('Delete', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'] . ' Status: '. $status['value'];

			$OrderMenue['classes'][5] = 'icon_discription icon_print';
			$OrderMenue['link_class'][5] = 'printlink';
			$OrderMenue['parms'][5] = null;
			$OrderMenue['controller'][5] = 'orders';
			$OrderMenue['action'][5] = 'pdf/'.implode('/',$this->request->projectvars['VarsArray']);
			$OrderMenue['discription'][5] =  __('Print', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'] . ' Status: '. $status['value'];

			$OrderMenue['classes'][6] = 'modal icon_dublicate';
			$OrderMenue['link_class'][6] = 'modal';
			$OrderMenue['parms'][6] = null;
			$OrderMenue['controller'][6] = 'orders';
			$OrderMenue['action'][6] = 'duplicat/'.implode('/',$this->request->projectvars['VarsArray']);
			$OrderMenue['discription'][6] =  __('Duplicate', true) . ' ' . __('Order') . ' ' . $order['Order']['auftrags_nr'] . ' Status: '. $status['value'];

			$reportsArrayContainer[] = array(
					'haedline' => $OrderMenue['haedline'],
					'class' => $OrderMenue['classes'],
					'link_class' => $OrderMenue['link_class'],
					'parms' => null,
					'controller' => $OrderMenue['controller'],
					'action' => $OrderMenue['action'],
					'discription' => $OrderMenue['discription']
			);


//TODO nicht lassen so, schlechtes Programming das sein
			if(isset($order['Order']['monitoring']) && $order['Order']['monitoring'] == 1) {
				$this->loadModel('Monitoring');
				$monitoring = $this->MonitoringTool->GetData();
				$this->request->data = $monitoring;
			}

			if(Configure::read('StatisticsEnabled') == true){
/*
				$reportsArrayContainer[] = array(
						'haedline' => __('Statistics',true),
						'class' => array('icon_discription icon_statistics'),
						'link_class' => array('ajax'),
						'parms' => null,
						'controller' => array('statistics'),
						'action' => array('index/'. implode('/',$this->request->projectvars['VarsArray'])),
						'discription' =>array(__('Statistic',true))
				);
*/
			}
			if(Configure::read('WorkloadManager') == true){
				$reportsArrayContainer[] = array(
						'haedline' => __('Testing and waiting times',true),
						'class' => array('icon_discription icon_worktime'),
						'link_class' => array('modal'),
						'parms' => null,
						'controller' => array('examiners'),
						'action' => array('view/'. implode('/',$this->request->projectvars['VarsArray'])),
						'discription' =>array(__('Examiner workload',true))
				);
			}
		}

		if(Configure::read('StripsManager') == true){
			$reportsArrayContainer[] = array(
					'haedline' => __('Strips manager',true),
					'class' => array('icon_discription icon_worktime'),
					'link_class' => array('modal'),
					'parms' => null,
					'controller' => array('stripdata'),
					'action' => array('index/'.join('/',$this->request->projectvars['VarsArray'])),
					'discription' =>array(__('Edit TCP Strips',true))
			);
		}

//		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		if(count($reportsArrayContainer) == 0) {
			$this->Session->setFlash(__('Please select a report typ in the project settings.'));
		}

		$reportsArrayContainer = $this->Autorisierung->AclCheckMenue($reportsArrayContainer);

		$testingmethods = null;

		$StatistikArray = $this->Navigation->StatistikInvoices($testingmethods,$orderID,$projectID);

		$menueArray = array(
				'general' => array(
						'haedline' => __('Actions', true),
						'controller' => 'reportnumbers',
						'discription' => array('Search Report'),
						'action' => array('index'),
						'params' => array(null),
				));

		$reportnumber = null;

		$breads = $this->Navigation->Breads(null);
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);
		$breads = $this->Navigation->RemoveLastElementByGroup($breads);

		if(isset($order['Order']['auftrags_nr'])) {
			$breads[] = array(
					'discription' => $order['Order']['auftrags_nr'] . ' (' . $status['value'] .')',
					'controller' => 'reportnumbers',
					'action' => 'index',
					'pass' => implode('/',$this->request->projectvars['VarsArray'])
			);
		}

		$breads = array_values($breads);

		$menue = $this->Navigation->NaviMenue(null);

		//Status feststellen
		if(isset($order['Order'])){
			if($order['Order']['status'] == 0){
				$statusDisc = __('offen', true);
			}
			//Status feststellen
			if($order['Order']['status'] == 1){
				$statusDisc = __('geschlossen', true);
			}
		}

		$SettingsArray = array();
		$SettingsArray['movelink'] = array('discription' => __('move order',true), 'controller' => 'cascades','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'reportnumbers','action' => 'settings', 'terms' => $this->request->projectvars['VarsArray'],);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);
		if(!Configure::check('search.orders') || Configure::read('search.orders')) {
//			$SettingsArray['addsearching'] = array('discription' => __('Searching for orders or reports',true), 'controller' => 'topprojects','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		} else {
//			$SettingsArray['addsearching'] = array('discription' => __('Searching for reports',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		}



		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$projects = array_values($this->Autorisierung->ConditionsTopprojects());
		$_projectID = array_search($projectID, $projects);
		$optionsNext = isset($projects[$_projectID+1]) ? $projects[$_projectID+1] : 0;
		$optionsBefore = isset($projects[$_projectID-1]) ? $projects[$_projectID-1] : 0;

		$optionsNext = array(
				'fields' => array('id','projektname', 'subdivision'),
				'conditions' => array('Topproject.id' => $optionsNext),
				'limit' => 1
		);

		$optionsBefore = array(
				'fields' => array('id','projektname','subdivision'),
				'conditions' => array('Topproject.id' => $optionsBefore),
				'limit' => 1
		);

//		$optionsNext = $this->Navigation->Subdivisions($this->Topproject->find('all', $optionsNext));
//		$optionsBefore = $this->Navigation->Subdivisions($this->Topproject->find('all', $optionsBefore));

//		$this->set('reportBefore',reset($optionsBefore));
//		$this->set('reportNext',reset($optionsNext));
		$this->set('status', $status);
		if(isset($statusDisc)){
			$this->set('statusDisc', $statusDisc);
		}
		$this->set('SettingsArray', $SettingsArray);
		//		$this->set('orderKat', $orderKat);
		$this->set('projectID', $projectID);
		$this->set('orderID', $orderID);
		$this->set('order', $order);
		$this->set('breads', $breads);
		$this->set('menues', $menue);
		//		$this->set('reports', $reportsArray);
		$this->set('reportsContainer', $reportsArrayContainer);
	}

	public function show() {

		$this->Navigation->GetSessionForPaging();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$breads = $this->Navigation->Breads(null);

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		$optionsTestingmethod =
		array(
				'conditions' => array(
						'Testingmethod.id' => $this->Autorisierung->ConditionsVariabel('Testingmethod','Report',$reportID)
				)
		);

		// die im Set vorhandenen Prüfberichte für das Menü
		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = 0;
		$testingmethods = $this->Testingmethod->find('all', $optionsTestingmethod);
		$options = array('conditions' => array('Topproject.id' => $projectID));

/* Florian 06.09.2017 */

		$options = array(
				'Reportnumber.topproject_id' => $projectID,
				'Reportnumber.cascade_id' => $cascadeID,
				//'Reportnumber.order_id' => $orderID,
				'Reportnumber.report_id' => $reportID
		);
                 //07.11.2018 Schmidt  __________________________________________________________________
        $orderID > 0 ? $options['Reportnumber.order_id'] = $orderID:'';
             /*07.11.2018  Schmidt: das muss später wieder Weg Abhängig davon ob in der Tabelle Cascade -> order auf 1 steht,
              * wird in "$this->request->projectvars['VarsArray'][2]" die entsprechende order_id eingetragen. Bisher gab es immer eine Order zu jedem Bericht
              * (ausgeblendeter Auftrag) weshalb in reportumber die order_id immer größer 0 ist und somit nach neuerung der Cascade keine Berichte gefunden werden.
              * um Entwicklung und Produktion parallel laufen lassen zu können bleibt dieser Weg erstmal bis Produktion allein läuft*/



		// Wenn die Anzeige von NE-Fremdberichten aktiviert ist,
		// zusätzlich zu eigenen Berichten auch Prüfberichte mit NE-Nähten anzeigen
		if(Configure::check('ShowNeGlobally') && Configure::read('ShowNeGlobally')) {
			// NE-Berichte von Fremdfirmen noch hinzufügen
			// Die Prüfverfahren und die IDs der gefundenen Reports
			$this->Reportnumber->recursive = -1;

			// verwendete Verfahren aus Prüfberichten holen
			// array(id=>verfahren)
			$foundReports = ($this->Reportnumber->find('list',array('fields' => array('testingmethod_id'),'conditions' => array_merge($options, array('Reportnumber.status > '=>1,'Reportnumber.delete'=>0, 'Reportnumber.moved_id'=>0)))));
			$this->Reportnumber->Testingmethod->recursive = -1;
			// Verfahrens-IDs in Kürzel umwandeln, um Models zu laden
			$foundMethods = $this->Reportnumber->Testingmethod->find('list', array('fields'=>array('value'), 'conditions'=>array('id'=>$foundReports)));
			$ne_ids = array();

			// verwendete Prüfverfahren durchgehen und Testen, ob eine Result-Spalte vorhanden ist
			// Prüfberichte, welche eine NE-Naht aufweisen mit in die Prüfberichte aufnehmen
			foreach($foundMethods as $_method) {
				$_model = 'Report'.ucfirst($_method).'Evaluation';
				if($this->loadModel($_model)) {
					if($this->$_model->hasField('result')) {
						$this->$_model->recursive = -1;

						// für das Prüfverfahren die Reportnumber-IDs aus den vorher gefundenen IDs ausfiltern,
						// bei denen eine NE-Naht vorhanden ist
						$_ne_ids = $this->$_model->find('list', array(
							'fields'=>array('reportnumber_id'),
							'conditions'=>array(
								'deleted'=>0,									// gelöschte Nähte auslassen
								'result'=>2,									// nur NE-Nähte beachten
								'reportnumber_id'=>array_keys($foundReports)	// nur Nähte von nicht gelöschten Prüfberichten verwenden
							)
						));
						$ne_ids = array_merge($ne_ids, $_ne_ids);
					}
				}
			}

			if($this->Auth->user('Testingcomp.testingcomp_typ') == 0){
				$options['or']['Reportnumber.testingcomp_id'] = $this->Session->read('conditionsTestingcomps');
			} else {
//				unset($options['or']['Reportnumber.testingcomp_id']);
			}
			if(count($ne_ids)){
				$options['or']['Reportnumber.id'] = $ne_ids;
			}
		} else {
			if($this->Auth->user('Testingcomp.testingcomp_typ') == 0){
				$options['Reportnumber.testingcomp_id'] = $this->Session->read('conditionsTestingcomps');
			} else {
//				unset($options['Reportnumber.testingcomp_id']);
			}
		}




		// Wenn diese Funktion von der Suche aufgerufen wurde
		if(isset($this->request->data['Reportnumber'])){
			foreach($this->request->data['Reportnumber'] as $_key  => $_search){
				if($_search != ''){
					$value = $this->Sicherheit->Numeric($_search);
					$options['Reportnumber.'.$_key] = $value;
				}
			}

			// Die Prüfverfahren und die IDs der gefundenen Reports
			$this->Reportnumber->recursive = -1;
			$foundReports = ($this->Reportnumber->find('list',array('fields' => array('testingmethod_id'),'conditions' => $options)));

			$thisModelIDArrays = $this->Data->SearchAdditional($testingmethods,$foundReports);
			$thisModelIDs = $thisModelIDArrays['IDs'];


			// Wenn keine passenden Prüfberichte gefunden wurden
			// wird die SuchID auf null gesetzt
			if(count($thisModelIDs) > 0){
				$thisModelIDs[] = 0;
			}

			$options['Reportnumber.id'] = $thisModelIDs;
		}

		if(isset($this->request->data['Quicksearch']['this_id']) && $this->request->data['Quicksearch']['this_id'] > 0){

			$this_id = $this->Sicherheit->Numeric($this->request->data['Quicksearch']['this_id']);
			$this->Autorisierung->IsThisMyReport($this->Sicherheit->Numeric($this->request->data['Quicksearch']['this_id']));
			$searching_autocomplet = explode('-',trim($this->request->data['Quicksearch']['searching_autocomplet']));

			if($this->Reportnumber->find('count',array('conditions' => array('Reportnumber.id' => $this_id))) == 1){
				$options['Reportnumber.id'] = $this_id;
			}
		}
		// Wenn sich ein externes Unternehmen eingeloggt hat, müssen alle Berichte des Projekts angezeigt werden
		if(AuthComponent::user('Testingcomp.extern') == 1){

			$options = array();

			$options = array(
					'Reportnumber.topproject_id' => $projectID,
					'Reportnumber.cascade_id' => $cascadeID,
					'Reportnumber.order_id' => $orderID,
					'Reportnumber.report_id' => $reportID
			);

			$options['Reportnumber.delete'] = 0;
			$options['Reportnumber.deactive'] = 0;

			if(Configure::check('ShowExternReportStatus') == true && Configure::read('ShowExternReportStatus') <> ''){
				$options['Reportnumber.status >'] =  Configure::read('ShowExternReportStatus');
			}else {
				$options['Reportnumber.status'] = 2;
			}

		}

		$options['Reportnumber.testingmethod_id !='] = 0;

		if(Configure::read('ShowChildrenReports') == false){
			$options['Reportnumber.parent_id'] = 0;
		}

		// Verschobene Berichte nicht mit anzeigen (moved_id > 0)
		$options['Reportnumber.moved_id'] = 0;

		$this->paginate = array(
				'conditions' => array($options),
				'order' => array('id' => 'desc'),
				'limit' => 25
		);

		$this->Reportnumber->recursive = 0;
		$testingreports = $this->paginate('Reportnumber');

		$allTestingmethods = $this->Reportnumber->Testingmethod->find('list',array('fields'=>array('id','verfahren')));

		$this->Reportnumber->recursive = -1;

		// Generally-Daten noch hinzufügen
		$xml = array();

                if(Configure::check('ShowWelderTestGlobal') && Configure::read('ShowWelderTestGlobal') == true &&  $this->Auth->user('roll_id') < 8 && $this->Auth->user('roll_id') > 4 ){
                    // $testingreportids = $this->Data->GetWelderTest($testingreports,$testingmethods,$projectID,$cascadeID,$orderID,$reportID);
                     $testingreportswithvtst = $this->Data->GetWelderTest($options,$testingmethods,$projectID,$cascadeID,$orderID,$reportID);
                     !empty($testingreportswithvtst)?$testingreports=$testingreportswithvtst:'';
                }

		// Test ob ein Bericht Unterberichte hat
		// Test ob ein Bericht eine Revision ist
		// Test ob ein Bericht geschlossen werden muss
		foreach($testingreports as $_id => $_testingreports){

			$_testingreports['Reportnumber']['handling_time'] = 0;

			if($_testingreports['Reportnumber']['print'] > 0){
			}

			$testingreports[$_id] = $this->Data->HandlingTime($_testingreports);

			// Wenn der Prüfbericht nach einem Zeitraum geschlossen werden soll
			if(Configure::check('CloseMethode') && Configure::read('CloseMethode') == 'showReportVerificationByTime'){


				if(Configure::check('CloseMethodeTime')){
					$limit = intval(Configure::read('CloseMethodeTime'));
				}

				if($_testingreports['Reportnumber']['print'] > 0 && $_testingreports['Reportnumber']['status'] == 0){

					if(time() > ($_testingreports['Reportnumber']['print'] + $limit)){
						$testingreports[$_id]['Reportnumber']['status'] = 2;
					}
				}
			}

			if(isset($_testingreports['Reportnumber']['old_version_id']) && $_testingreports['Reportnumber']['old_version_id'] > 0){
				$old_version = $this->Reportnumber->find('first',array(
																	'conditions' => array(
																		'Reportnumber.id' => $_testingreports['Reportnumber']['old_version_id'],
																		'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps')
																		)
																	)
																);
				if(!isset($old_version['Reportnumber'])){
					$testingreports[$_id]['OldVersion']['info'] = __('Old version can not be displayed',true);

				} elseif(isset($old_version['Reportnumber'])){
					$testingreports[$_id]['OldVersion'] = $old_version;
				}
			}

			if($_testingreports['Testingmethod']['allow_children'] == 1){

				$alowed_children = $this->Reportnumber->find('count',array('conditions'=>array('Reportnumber.parent_id'=>$_testingreports['Reportnumber']['id'])));
				if(($alowed_children) > 0){
					$testingreports[$_id]['Children'] = $alowed_children;
				}
			}

	 		if(Configure::check('RepairManager') && Configure::read('RepairManager') == true){
				$testingreports[$_id] = $this->Repairtracking->QuickCheckRepairStatus($testingreports[$_id]);
				$testingreports[$_id] = $this->Repairtracking->CheckIsRepairReport($testingreports[$_id]);
	 		}
			
			if(Configure::read('WeldManager.show') && !isset($xml[$_testingreports['Testingmethod']['value']])){

				$xml[$_testingreports['Testingmethod']['value']] = $this->Xml->DatafromXmlForReport($_testingreports['Testingmethod']['value'],$_testingreports);

			}

			$testingreports[$_id] = $this->Data->WeldTypes($testingreports[$_id],$xml);

		}

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		// wenn eine Suche kein Ergebnis bracht werden wieder alle Prüfberichte angezeigt
		if(count($testingreports) == 0){
			$options = array(
					'Reportnumber.topproject_id' => $projectID,
					'Reportnumber.cascade_id' => $cascadeID,
					'Reportnumber.order_id' => $orderID,
					'Reportnumber.report_id' => $reportID,
					'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps')
			);

			$this->paginate = array(
					'conditions' => array($options),
					'order' => array('Reportnumber.id' => 'DESC'),
					'limit' => 25
			);

			$this->Reportnumber->recursive = 0;
			$testingreports = $this->paginate('Reportnumber');
		}

		foreach($testingreports as $key => $val) {
			$testingreports[$key] = $val = $this->Data->EnforceParentReportnumber($val);
			$testingreports[$key] = $this->Repairtracking->LookForRepairreport($testingreports[$key]);
		}

		$this->set('reportnumbers', $testingreports);

		// Die Bezeichnung des Prüfberichtsset
		if(count($testingreports) > 0){
			$reportnumber = $testingreports[0];
		}
		else {
			$reportnumber['Report']['name'] = null;
		}

		$testingmethodIncIDs = array();
		$testingmethod = array();

		// Die benötigten Variablen für den Link werden hinzugefügt
		foreach($testingmethods as $_testingmethods) {
			$_testingmethods['Testingmethod']['projectID'] = $projectID;
			$_testingmethods['Testingmethod']['cascadeID'] = $cascadeID;
			$_testingmethods['Testingmethod']['orderID'] = $orderID;
			$_testingmethods['Testingmethod']['reportID'] = $reportID;
			$testingmethodIncIDs[] = $_testingmethods;
		}

		$ControllermenueArray = array('reportnumbers','dropdowns');
		$DiscriptionmenueArray = array(__('Search Report', true),__('Dropdown Boxes',true));
		$ParamsmenueArray = array(null,null);

		// Es werden die Testingmethods herausgefilter
		// für welche es keine XML-Vorlage gibt
		$dir = new Folder(Configure::read('xml_folder'));
		foreach($testingmethodIncIDs as $_testingmethods) {
			$files = $dir->findRecursive($_testingmethods['Testingmethod']['value'].'.xml');
			if(count($files) == 1) {
				$ControllermenueArray[] = 'reportnumbers';
				$DiscriptionmenueArray[] = __('Create', true).' '.$_testingmethods['Testingmethod']['verfahren'];
				$ParamsmenueArray[] = null;
			}
		}

		$reportsArray = $this->Navigation->SubMenue(
				'Report',
				'Topproject',
				__('Create ', true),
				'listing',
				'testingmethods',
				$reportID
				);

		// Das Array für das Settingsmenü
		$SettingsArray = array();

		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
//		$SettingsArray['statistik'] = array('discription' => __('Statistics', true), 'controller'=>'searchings', 'action'=>'statistic', 'terms'=>$this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);

		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		if(isset($testingreports[0]['Order']['status']) && $testingreports[0]['Order']['status'] == 0){
			$status = array('number' => 0, 'value' => __('open', true));
		}
		if(isset($testingreports[0]['Order']['status']) && $testingreports[0]['Order']['status'] == 1){
			$status = array('number' => 1, 'value' => __('close', true));
		}

		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);
		$breads = $this->Navigation->RemoveLastElementByGroup($breads);

		if($orderID > 0){
			$breads[] = array(
				'discription' => isset($testingreports[0]['Order']['auftrags_nr']) && isset($testingreports[0]['Order']['status']) ? $testingreports[0]['Order']['auftrags_nr'] . ' (' . $status['value'] . ')' : '',
				'controller' => 'reportnumbers',
				'action' => 'index',
				'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2].'/'.$this->request->projectvars['VarsArray'][3]
			);
		}

		$breads[] = array(
				'discription' => isset($testingreports[0]['Report']['name'])? $testingreports[0]['Report']['name'] : '',
				'controller' => 'reportnumbers',
				'action' => 'show',
				'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2].'/'.$this->request->projectvars['VarsArray'][3].'/'.$this->request->projectvars['VarsArray'][4]
		);

		$this->loadModel('ReportsTopproject');
		$reports = array_values($this->ReportsTopproject->find('list', array('fields'=>array('ReportsTopproject.report_id','ReportsTopproject.report_id'), 'conditions'=>array('ReportsTopproject.topproject_id'=>$projectID))));

		$pos = array_search($reportID, $reports);
		$reportBefore = array_slice($reports, 0, $pos);
		$reportNext = array_slice($reports, $pos+1);

                //Schmidt 18.08.2017-----------------------------------------
                 $this->Reportnumber->Report->recursive = -1;
                foreach($reportNext as $key) {
                    $options['Reportnumber.report_id'] = $key;
                    $next = $this->Reportnumber->find('first',array('conditions' => $options));
                    if(!empty ($next)){
                        $reportNext = array('fields'=>array('Report.id','Report.name'), 'limit'=>1, 'order'=>array('Report.id'=>'asc'), 'conditions'=>array('Report.id'=>$next ['Reportnumber']['report_id']));
                        $this->set('reportNext', $reportNext = (count($reportNext)>1 ? $this->Reportnumber->Report->find('all', $reportNext) : array()));
                        break;
                    }
                }

                foreach($reportBefore as $key) {
                    $options['Reportnumber.report_id'] = $key;
                    $before = $this->Reportnumber->find('first',array('conditions' => $options));
                    if(!empty ($before)){
                        $reportBefore = array('fields'=>array('Report.id','Report.name'), 'limit'=>1, 'order'=>array('Report.id'=>'desc'), 'conditions'=>array('Report.id'=>$before['Reportnumber']['report_id']));
                        $this->set('reportBefore',$reportBefore = (count($reportBefore)>1 ? $this->Reportnumber->Report->find('all', $reportBefore) : array()));
                        break;
                    }
                }
                 //Schmidt 18.08.2017-----------------------------------------



		$display = $this->Xml->XmltoArray('display', 'file',null);

		$items = $display->xpath('section[url="'.strtolower($this->request->controller).'/'.strtolower($this->request->action).'"]/item');

		if(!is_array($items)) $items = array();

		if(empty($items)) {
			$items = json_decode('[
			    {"model": "Testingmethod", "key": "name", "sortable": "1", "description": "Testingmethod", "hidden": "0"},
			    {"model": "Generally", "key": "examination_object", "sortable": "1", "description": "Examination object", "hidden": "0"},
			    {"model": "Testingcomp", "key": "verfahren", "sortable": "1", "description": "Testingcompany", "hidden": "0"},
			    {"model": "User", "key": "name", "sortable": "1", "description": "Username", "hidden": "0"},
			    {"model": "Reportnumber", "key": "modified", "sortable": "1", "description": "modified", "hidden": "0"}
			]');
		}

		$SubMenueArray = array();
		array_push($SubMenueArray, array('title' => __('Show'),'controller' => 'reportnumbers','action' => 'view','open' => 'container','uiIcon' => 'qm_show'));
		array_push($SubMenueArray, array('title' => __('Edit'),'controller' => 'reportnumbers','action' => 'edit','open' => 'container','uiIcon' => 'qm_edit'));
		array_push($SubMenueArray, array('title' => __('Duplicat'),'controller' => 'reportnumbers','action' => 'duplicat','open' => 'dialog','uiIcon' => 'qm_duplicate'));

		if(Configure::read('DisablePrintOnErrors') == true) {
			array_push($SubMenueArray, array('title' => __('Print'),'controller' => 'reportnumbers','action' => 'errors','open' => 'dialog','uiIcon' => 'qm_print'));
		} else {
			array_push($SubMenueArray, array('title' => __('Print'),'controller' => 'reportnumbers','action' => 'pdf','open' => 'window','uiIcon' => 'qm_print'));
		}

//		array_push($SubMenueArray, array('title' => __('Move'),'controller' => 'reportnumbers','action' => 'move','open' => 'dialog','uiIcon' => 'qm_move'));
		array_push($SubMenueArray, array('title' => __('Delete'),'controller' => 'reportnumbers','action' => 'delete','open' => 'container','uiIcon' => 'qm_delete'));
		array_push($SubMenueArray, array('title' => __('Move'),'controller' => 'reportnumbers','action' => 'move','open' => 'dialog','uiIcon' => 'qm_duplicate'));

		$SubMenueArray = $this->Autorisierung->AclCheckSubmenueMenue($SubMenueArray);

		$this->set('SubMenueArray', $SubMenueArray);
		$this->set('items', $items);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('reports', $reportsArray);
		$this->set('reportName', $reportnumber['Report']['name']);
		$this->set('reportID', $this->request->projectvars['VarsArray'][4]);
		$this->set('projectID', $this->request->projectvars['VarsArray'][0]);
		$this->set('orderID', $this->request->projectvars['VarsArray'][3]);
		$this->set('allTestingmethods','allTestingmethods');
		$this->set('xml', $xml);

		$this->Navigation->SetSessionForPaging();

		if(AuthComponent::user('Testingcomp.extern') == 1){
			$this->render('show_extern');
		}
	}

	public function searchReportList() {
		$this->layout = 'modal';

		$this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

		$projectID = $this->request->projectID;
		$orderKat = $this->request->orderKat;
		$orderID = $this->request->orderID;
		$reportID = $this->request->reportID;

		if(isset($this->request->data['searchglobal'])){
			$searchglobal = $this->Sicherheit->Numeric($this->request->data['searchglobal']);
		}

		$searchFields = $this->Xml->XmlToArray('search','file',null);

		$fields = array('autocompletes'=>array(), 'dropdowns'=>array());

		foreach($searchFields as $field)
		{
			if(trim($field->autocomplete) == 1 || trim($field->autocomplete) == true)
			{
				array_push(
						$fields['autocompletes'],
						array('Model'=>trim($field->model), 'Key'=>trim($field->key), 'Caption'=>trim($field->value), 'Break'=>trim($field->break))
						);
			}
			else if(trim($field->model) != '')
			{
				$fields['dropdowns'][trim($field->key)] = array(
						'Model' => trim($field->model),
						'OptionField' => trim($field->option),
						'OutputField' => trim($field->output),
						'Value' => $field->value,
						'Break' => trim($field->break)
				);
			}
		}

		$Topproject = $this->Topproject->find('first',array('conditions' => array('Topproject.id' => $this->request->projectID)));
		$this->loadModel('Report');
		$testingmethodesglobal = array();

		$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.'.$this->Reportnumber->primaryKey=>$this->request->data['id'])));

		$this->Report->recursive = -1;

		foreach($Topproject['Report'] as $_reports){
			$Reports = $this->Report->find('first',array('conditions' => array('Report.id' => $_reports['id'])));
			foreach($Reports['Testingmethod'] as $_testingmethod){
				if($_testingmethod['value'] == 'alg' xor $reportnumber['Testingmethod']['value']=='alg')
				{
					$testingmethodesglobal[$_testingmethod['id']] = $_testingmethod['id'];
				}
			}
		}

		$optionsTestingmethod =
		array(
				'conditions' => array(
						'Testingmethod.id' => $testingmethodesglobal
				)
		);

		// die im Set vorhandenen Prüfberichte für das Menü
		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = 0;
		$testingmethods = $this->Testingmethod->find('all', $optionsTestingmethod);

		// Wenn die Suchd von der Projektebene aufgerufen wurde müssen die Reports entsprechend des Projektes gewählt werden
		$thisReports = array();
		if(count($testingmethods) == 0){


			$this->loadModel('Topproject');
			$topproject = $this->Topproject->find('first', array('conditions' => array('topproject.id' => $projectID)));

			foreach($topproject['Report']as $_key => $_Report){
				$thisReports[$_Report['id']] = $_Report['name'];
			}
		}

		$autocompletesData = ($this->Data->SearchAutocomplets($testingmethods, $fields));

		//$autocompletesData = ($this->Data->SearchAutocomplets($testingmethods,$autocompletes));

		if((isset($this->request->data['Reportnumber'])) && $this->request->is('post')) {

			$options = array();
			$options['conditions']['Reportnumber.topproject_id'] = $this->request->projectID;

			foreach($this->request->data['Reportnumber'] as $_key => $_requestReportnumber){
				if(strlen($_requestReportnumber > 0)){
					$options['conditions']['Reportnumber.'.$_key] = $_requestReportnumber;
				}
			}
			// Die in der Suche vorhandenen Prüfverfahren finden
			$optionsTestingmethods = $options;
			$optionsTestingmethods['fields'] = array('DISTINCT testingmethod_id');

			$this->Reportnumber->recursive = -1;
			$testingreportsTestingmethods = $this->Reportnumber->find('all', $optionsTestingmethods);
			$thisTestingmethods = array();

			foreach($testingreportsTestingmethods as $_testingreportsTestingmethods){
				$thisTestingmethods[] = $_testingreportsTestingmethods['Reportnumber']['testingmethod_id'];
			}

			$additionalIDArrays = ($this->Data->SearchAdditional($testingmethods,$thisTestingmethods));

			$additionalIDs = $additionalIDArrays['IDs'];

			if(!empty($additionalIDs)){
				$options['conditions']['Reportnumber.id'] = $additionalIDs;
			}
		}
		else {
			$options = array(
					'conditions' => array(
							'Reportnumber.topproject_id' => $this->request->projectID,
							'Reportnumber.order_id' => $orderID,
							'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps')
					)
			);
		}

		$options['conditions'] = array_merge(
				$options['conditions'],
				array(
						'Reportnumber.testingmethod_id' => $testingmethodesglobal,
						'or'=> array(
								'Reportnumber.parent_id' => null,
								'Reportnumber.parent_id !=' => $reportnumber['Reportnumber']['id']
						)
				)
				);

		$this->Reportnumber->recursive = 0;
		$testingreports = $this->Reportnumber->find('all', $options);

		$testingreportsCount = count($testingreports);

		foreach($fields['dropdowns'] as $_key => $_SearchFields)
		{
			$options = array(
					'fields' => array('DISTINCT '.$_key),
					'conditions' => array(
							'Reportnumber.topproject_id' => $this->request->projectID,
							'Reportnumber.order_id' => $orderID,
							'Reportnumber.testingmethod_id' => $testingmethodesglobal,
							'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps')
					)
			);

			$testingreportsForDropdown = $this->Reportnumber->find('all', $options);
			//echo '<pre>'.print_r($testingreportsForDropdown, true).'</pre>';
			foreach($testingreportsForDropdown as $_testingreportsForDropdown){
				$fields['dropdowns'][$_key]['Query'][$_testingreportsForDropdown['Reportnumber'][$_key]] = $_testingreportsForDropdown['Reportnumber'][$_key];
			}

		}

		// Die IDs werden mit den Klarnamen ausgetauscht
		foreach($fields['dropdowns'] as $_key => $_SearchFields)
		{
			// Model laden
			$this->loadModel($_SearchFields['Model']);
			$this->$_SearchFields['Model']->recursive = -1;

			$fields['dropdowns'][$_key]['Result'] = $this->$_SearchFields['Model']->find('list',array(
					'fields' => array($_SearchFields['Model'].'.'.$_SearchFields['OutputField']),
					'conditions' => array(
							$_SearchFields['Model'].'.'.$_SearchFields['OptionField'] => @$_SearchFields['Query']
					)
			)
					);

			if($_SearchFields['Model'] == 'Report' && $_SearchFields['OptionField'] == 'id' && $reportID > 0){
				if(!isset($this->request->data['Reportnumber']['report_id'])){
					$fields['dropdowns'][$_key]['Value'] = $reportID;
				}
				elseif(isset($this->request->data['Reportnumber']['report_id']) && $this->request->data['Reportnumber']['report_id'] != null){
					$fields['dropdowns'][$_key]['Value'] = $this->request->data['Reportnumber']['report_id'];
				}
			}
		}

		$this->set('testingreportsCount', $testingreportsCount);
		$this->set('SearchFields', $fields);
		$this->set('reportName', @$SearchFields['report_id']['Result'][$reportID]);
		$this->set('reportID', $reportID);
		$this->set('projectID', $projectID);
		$this->set('orderID', $orderID);
		$this->set('orderKat', $this->request->orderKat);
		$this->set('reportnumber', $reportnumber);

		if(isset($this->request->data['showresult'])) {
			$this->set('testingreports', $testingreports);
			$this->set('type', $reportnumber['Testingmethod']['value']!='alg');
			$this->render('list_reports');
		}
	}

	public function quicksearch() {

		$this->layout = 'json';

		$breads = $this->Navigation->Breads(null);

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$term_array = explode(' ',$term = preg_replace( '/[^0-9]/', ' ', $this->request->data['term']));

		$options = array(
				'Reportnumber.topproject_id' => $projectID,
				'Reportnumber.cascade_id' => $cascadeID,
				'Reportnumber.order_id' => $orderID,
				'Reportnumber.report_id' => $reportID,
				'Reportnumber.number' => $term_array,
				'Reportnumber.delete' => 0,
				'Reportnumber.deactive' => 0
		);

		if($this->request->TopprojectSubdivision == 1){
			$option_del = array(2,3,4);

			$_x = 1;
			foreach($options as $_key => $_options){
				if(in_array($_x, $option_del)){
					unset($options[$_key]);
				}
				$_x++;
			}
		}

		$reportnumbers = $this->Reportnumber->find('list', array(
				'fields' => array('year','number','id'),
				'conditions' => $options
		)
				);

		$response = array();

		foreach($reportnumbers as $_key => $_reportnumbers){
			array_push($response, array('key' => $_key,'value' => key($_reportnumbers) . '-' . $_reportnumbers[key($_reportnumbers)]));
		}

		$this->set('test',json_encode($response));
	}

	public function view($id = null) {

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	// Wenn die Anfrage von der Schnellsuche kommt
	 	if(isset($this->request->data['Reportnumbers']['search']) && $this->request->data['Reportnumbers']['search'] != ''){

	 		$searchterm = explode(' ',(trim($this->request->data['Reportnumbers']['search'])));
	 		$searchtermNumber = explode('-',$searchterm[0]);

	 		$year = $this->Sicherheit->Numeric($searchtermNumber[0]);
	 		$number = $this->Sicherheit->Numeric($searchtermNumber[1]);

	 		$options = array(
	 				'fields' => array('id','report_id'),
	 				'conditions' =>
	 				array(
	 						'Reportnumber.topproject_id' => $projectID,
	 						'Reportnumber.order_id' => $orderID,
	 						'Reportnumber.year' => $year,
	 						'Reportnumber.number' => $number
	 				)
	 		);

	 		$this->Reportnumber->recursive = -1;
	 		$reportnumber = $this->Reportnumber->find('first', $options);

	 		$this->Autorisierung->IsThisMyReport($reportnumber['Reportnumber']['id']);
	 		$id = $reportnumber['Reportnumber']['id'];
	 		$reportID = $reportnumber['Reportnumber']['report_id'];

	 		$this->request->reportID = $reportID;
	 		$this->request->reportnumberID = $id;

	 	}

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumber = $this->Reportnumber->find('first', $options);


	 	$reportnumber = $this->Data->GetReportData($reportnumber,array('Generally','Specific','Evaluation'));

	 	$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren ;

	 	$ReportGenerally = $this->request->tablenames[0];
	 	$ReportSpecific = $this->request->tablenames[1];
	 	$ReportEvaluation = $this->request->tablenames[2];
	 	$ReportArchiv = $this->request->tablenames[3];
	 	$ReportSettings = $this->request->tablenames[4];
	 	$ReportPdf = $this->request->tablenames[5];

		$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation,$ReportArchiv,$ReportSettings,$ReportPdf);

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	$reportnumber = $this->Data->EnforceParentReportnumber($reportnumber);

	 	if(isset($reportnumber['Reportnumber']['_number'])) {
	 		$reportnumber['Reportnumber']['number'] = $reportnumber['Reportnumber']['_number'];
	 	}

	 	$this->Data->Archiv($reportnumber['Reportnumber']['id'],$Verfahren);

	 	$optionsSettings = array('conditions' => array($ReportSettings.'.reportnumber_id' => $id));
		$this->loadModel($ReportSettings);
	 	$Settings = $this->$ReportSettings->find('first', $optionsSettings);
	 	$optionsArchiv = array('conditions' => array($ReportArchiv.'.reportnumber_id' => $id));
		$this->loadModel($ReportArchiv);
	 	$Archiv = $this->$ReportArchiv->find('first', $optionsArchiv);

	 	$arrayArchiv = $this->Xml->DatafromXml(($Archiv[$ReportArchiv]['data']),'string',$Verfahren);

	 	// XML-Objekt in Array umwandeln
	 	$Archiv = array();
	 	// Die Bewertungen werden in ein extra Array gepackt
	 	$Evaluation = array();
	 	$EvoCount = 0;
	 	foreach($arrayArchiv['settings']->$ReportEvaluation as $_key =>$_ReportEvaluation){
	 		foreach($_ReportEvaluation as $__key => $__ReportEvaluation){
	 			$Evaluation[trim($_key)][trim($EvoCount)][trim($_key)][trim($__key)] = trim($__ReportEvaluation);
	 		}
	 		$EvoCount++;
	 	}

	 	// das XML-Objekt wird in ein Array umgewandelt
	 	foreach($arrayArchiv as $_key => $_arrayArchiv){
	 		foreach($_arrayArchiv as $__key => $__arrayArchiv){
	 			$Archiv[$__key] = null;
	 			foreach($__arrayArchiv as $___key => $___arrayArchiv){
	 				$Archiv[$__key][$___key] = trim($___arrayArchiv);
	 			}
	 		}
	 	}

	 	// Die vorhandene Bewertung wird gelöscht, da nur die erste gespeichert wurde
	 	unset($Archiv[$ReportEvaluation]);
	 	// und das zuvor erstellte Auswertungarray wird in das Hauptarray integriert
	 	$Archiv = array_merge($Archiv, $Evaluation);
	 	$Archiv['Tableshema'] = $ReportArray;

	 	$this->request->data = $Archiv;

	 	$breaddiscription = array($Archiv['Report']['name']);

	 	// Der Status muss nachgetragen werden, da der nicht automatisch in das Archiv aufgenommen wird
	 	$Archiv['Reportnumber']['status'] = $reportnumber['Reportnumber']['status'];

	 	$breads = $this->Navigation->BreadForReport($reportnumber,'view');

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
//	 	$SettingsArray['movelink'] = array('discription' => __('Move',true), 'controller' => 'reportnumbers','action' => 'move', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
//	 	$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'reportnumbers','action' => 'settings', 'terms' => $this->request->projectvars['VarsArray'],);
//	 	$SettingsArray['workloadlink'] = array('discription' => __('Examiner workload'), 'controller' => 'examiners', 'action'=>'list_workload', 'terms' => $this->request->projectvars['VarsArray']/*, 'disabled'=>isset($reportnumbers['Reportnumber']['status']) && $reportnumbers['Reportnumber']['status'] != 0*/);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);

		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

	 	if(isset($Archiv[$ReportEvaluation])) $welds = $this->Data->WeldSorting($Archiv[$ReportEvaluation]);
		else  $welds = array();

	 	$breads = $this->Navigation->SubdivisionBreads($breads);

	 	if(Configure::check('RepairManager') && Configure::read('RepairManager') == true){
		 	$Archiv = $this->Repairtracking->GetRepairReport($Archiv);
		 	if(isset($Archiv[$ReportEvaluation])) $Archiv = $this->Repairtracking->GetRepairStatus($Archiv,$Archiv[$ReportEvaluation]);
	 	}

		$ReportMenue = $this->Navigation->createReportMenue($Archiv,$arrayData['settings']);

		if(isset($Archiv[$ReportEvaluation]))$evaluation = $this->Data->WeldSorting($Archiv[$ReportEvaluation]);
		else $evaluation = array();

		$this->Reportnumber->recursive = -1;

	 	$optionBeforeArray = array();
	 	$optionNextArray = array();

	 	$optionNextArray = array(
	 				'Reportnumber.topproject_id' => $projectID,
	 				'Reportnumber.cascade_id' => $cascadeID,
	 				'Reportnumber.order_id' => $orderID,
	 				'Reportnumber.report_id' => $reportID,
	 				'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
	 				'Reportnumber.id >' => $id,
	 	);

		$optionBeforeArray = array(
	 				'Reportnumber.topproject_id' => $projectID,
	 				'Reportnumber.cascade_id' => $cascadeID,
	 				'Reportnumber.order_id' => $orderID,
	 				'Reportnumber.report_id' => $reportID,
	 				'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
	 				'Reportnumber.id <' => $id,
	 	);

	 	$optionsNext = array(
	 			'fields' => array('id','number','year','testingmethod_id'),
	 			'order' => array('Reportnumber.id' => 'asc'),
	 			'conditions' => $optionNextArray,
	 	);
	 	$optionsBefore = array(
	 			'fields' => array('id','number','year','testingmethod_id'),
	 			'order' => array('Reportnumber.id' => 'desc'),
	 			'conditions' => $optionBeforeArray,
	 	);

	 	$reportBefore = $this->Reportnumber->find('first', $optionsBefore);
	 	$reportNext = $this->Reportnumber->find('first', $optionsNext);

	 	$this->set('reportBefore',$reportBefore);
	 	$this->set('reportNext',$reportNext);

	 	//		$Archiv['Reportnumber']['orderKat'] = $orderKat;
	 	$this->set('validationErrors', $this->Reportnumber->getValidationErrors($arrayData, $Archiv));
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('verfahren', $verfahren);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('ReportMenue', $ReportMenue);
	 	$this->set('breads', $breads);
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('headoutput', null);
	 	$this->set('generaloutput', $arrayData['generaloutput']);
	 	$this->set('specificoutput',!empty($arrayData['specificoutput']) ? $arrayData['specificoutput'] : array());
	 	$this->set('welds', @$welds);
	 	$this->set('evaluationoutput',!empty($arrayData['evaluationoutput']) ? $arrayData['evaluationoutput'] : array());
	 	$this->set('reportnumber', $Archiv);
	 	$this->set('isChild', !empty($reportnumber['Reportnumber']['parent_id']));
	 	$this->set('hasChildren', $this->Reportnumber->find('count', array('conditions'=>array('Reportnumber.parent_id'=>$id))));
	 	$this->set('evaluation', $evaluation);
	 	$this->set('locale', $this->Lang->Discription());
	 }

	 public function add() {

		$this->layout = 'modal';

	 	if($this->writeprotection) {
	 		$this->set('afterEDIT', $this->Navigation->afterEDIT(Router::url(array_merge(array('controller'=>'reportnumbers', 'action'=>'show'), $this->request->projectvars['VarsArray'])), 50));
	 		$this->Flash->set('This project is write protected, you cant write testingreports here.');
			$this->render();
	 		return;
	 	}

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];
	 	$testingmethodID = $this->request->projectvars['VarsArray'][5];
		$expeditingID =  $this->request->projectvars['VarsArray'][6];

	 	// Test, ob der zu verknüpfende Bericht auch aufgerufen werden darf
	 	if(isset($this->request->data['linked']) && $this->request->data['linked'] == 1) {
	 		if(!$this->Reportnumber->exists($reportnumberID)) die();
	 		if(!$this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID, 'Reportnumber.delete'=>0)))) die();
	 		$this->Autorisierung->IsThisMyReport($reportnumberID);
	 	}

	 	$id = $this->Data->ResetNumberByYear();

	 	if($id == false){
	 		$this->Session->setFlash(__('The new report could not be saved. Please, try again.'));
	 		$this->render();
	 		return;
	 	}

	 	$options = array('Reportnumber'=>array('id' => $id, 'testingmethod_id' => $testingmethodID,'cascade_id' => $cascadeID,'order_id' => $orderID));

	 	// Verknüpfte Berichte zuweisen, wenn nötig
	 	if(isset($this->request->data['linked']) && $this->request->data['linked'] == 1) {
	 		$this->Reportnumber->recursive = 0;
	 		$currentReport = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID)));

	 		if($currentReport['Testingmethod']['allow_children'] == 1) {
	 			$subindex = $this->Reportnumber->find('list', array('fields'=>array('id', 'subindex'), 'order'=>array('subindex'=>'desc'), 'conditions'=>array('Reportnumber.parent_id'=>$reportnumberID, 'Reportnumber.delete'=>0)));
	 			if(empty($subindex)) $subindex = array(-1);

	 			$options['Reportnumber']['parent_id'] = $reportnumberID;
	 			$options['Reportnumber']['subindex'] = (reset($subindex) + 1);
	 		} else if($currentReport['Testingmethod']['allow_children'] == 0) {
	 			$subindex = $this->Reportnumber->find('list', array('fields'=>array('id', 'subindex'), 'order'=>array('subindex'=>'desc'), 'conditions'=>array('Reportnumber.parent_id'=>$id, 'Reportnumber.delete'=>0)));
	 			if(empty($subindex)) $subindex = array(-1);

	 			$this->Reportnumber->save(array('Reportnumber'=>array('id'=>$reportnumberID, 'parent_id'=>$id, 'subindex'=>(reset($subindex) + 1))), array('callbacks'=>false));
	 		}
	 	}

	 	$this->Reportnumber->save($options);
		if(isset($expeditingID) && !empty($expeditingID) && $expeditingID > 0 ) {

			$this->loadModel('ExpeditingReportnumbers');
			$this->ExpeditingReportnumbers->create();
			$expediting = array('expediting_id'=> $expeditingID, 'reportnumber_id' => $id);
			$this->ExpeditingReportnumbers->save($expediting);
		}
	 	// die Datensätze in den zugehörigen Datentabellen anlegen
	 	$options = array('conditions' => array('Reportnumber.id' => $id));
	 	$data = $this->Reportnumber->find('first',$options);

	 	$this->loadModel('Testingmethod');
	 	$optionsTestingmethod = array('conditions' => array('Testingmethod.id' => $data['Reportnumber']['testingmethod_id']));
	 	$testingmethod = $this->Testingmethod->find('first',$optionsTestingmethod);

	 	$master = $testingmethod['Testingmethod']['allow_children'];
	 	$verfahren = $testingmethod['Testingmethod']['value'];
	 	$Verfahren = ucfirst($testingmethod['Testingmethod']['value']);
	 	$Version = ucfirst($testingmethod['Testingmethod']['version']);

	 	// Feldinfos für den Generallyteil des Prüfberichtes wird geholt
	 	$generallyFororder = ($this->Xml->XmltoArray(html_entity_decode($this->Xml->ArchivDatafromXml($verfahren)),'string',null));

	 	$ReportData[0] = 'Report'.ucfirst($verfahren).'Generally';
	 	$ReportData[1] = 'Report'.ucfirst($verfahren).'Specific';
	 	$ReportData[2] = 'Report'.ucfirst($verfahren).'Evaluation';
	 	$ReportData[3] = 'Report'.ucfirst($verfahren).'Setting';
	 	$ReportData[4] = 'Report'.ucfirst($verfahren).'Archiv';

	 	foreach($ReportData as $_ReportData){
	 		$this->loadModel($_ReportData);
	 	}

	 	$ReportDataId = null;

	 	foreach($ReportData as $_key => $_ReportData) {
	 		// Wenn im Abschnitt kein ID-Eintrag definiert ist, dann keinen Eintrag anlegen
	 		if(count($generallyFororder->xpath($_ReportData.'/*[key="id"]')) == 0) {
	 			$ReportDataId .= ' -';
	 			continue;
	 		}


	 		if($this->$_ReportData->create() && $_ReportData <>  'Report'.ucfirst($verfahren).'Evaluation'){
	 			$this->$_ReportData->save(array('reportnumber_id' => $id));
			}

	 		$ReportDataId .= ' '.$this->$_ReportData->getInsertID();

	 		// Die Daten aus der Bestellung übernehmen
	 		if($_ReportData == 'Report'.ucfirst($verfahren).'Generally' && $orderID > 0) {
	 			$this->Reportnumber->Order->recursive = -1;
	 			$dataOld = $this->Reportnumber->Order->find('first',array('conditions'=>array('Order.id' => $orderID)));

	 			// ein neues Array für die Generallydaten mit der aktuellen id wird angelegt
	 			$orderforGeneral =	array('id' => $this->$_ReportData->getInsertID());
	 			// ein Array mit den Orderinfos wird für den Generalyteil des Prüfberichtes befüllt
                                $generally = $ReportData[0];
	 			foreach($generallyFororder->$generally as $_generallyFororder){
	 				foreach($_generallyFororder as $key => $value){
	 					if(isset($dataOld['Order'][$key]) && $key != 'id' && $key != 'created'){
	 						$orderforGeneral[$key] = $dataOld['Order'][$key];

	 					}
	 				}
	 			}

	 			// Bei Berichtsverknüpfung zusätzlich noch Generallydaten aus altem Bericht übernehmen
	 			if(isset($this->request->data['setGenerally']) && $this->request->data['setGenerally'] == 1) {

	 				$this->Reportnumber->recursive = 0;
	 				$oldReport = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID)));
	 				$this->loadModel($model = 'Report'.ucfirst($oldReport['Testingmethod']['value']).'Generally');
	 				$oldData = $this->$model->find('first', array('conditions'=>array($model.'.reportnumber_id' => $reportnumberID)));

	 				foreach($oldData[$model]as $key=>$value) {

	 					// alle Datenwerte in die neue Generally-Tabelle kopieren, sofern das Feld existiert
	 					if(array_search($key, array('id', 'reportnumber_id', 'created', 'modified')) !== false) continue;
	 					if($this->$_ReportData->hasField($key)) $orderforGeneral[$key] = $value;
	 				}
	 			}

	 			$this->$_ReportData->save($orderforGeneral);
	 			$this->Autorisierung->Logger($id,$orderforGeneral);

	 		}

	 		// Bei Berichtsverknüpfung zusätzlich noch Generallydaten aus altem Bericht übernehmen
	 		if($_ReportData == 'Report'.ucfirst($verfahren).'Generally' && isset($this->request->data['setGenerally']) && $this->request->data['setGenerally'] == 1) {

	 			$this->Reportnumber->recursive = 0;
	 			$oldReport = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID)));
	 			$this->loadModel($model = 'Report'.ucfirst($oldReport['Testingmethod']['value']).'Generally');
	 			$oldData = $this->$model->find('first', array('conditions'=>array($model.'.reportnumber_id' => $reportnumberID)));
	 			foreach($oldData[$model]as $key=>$value) {

	 				// alle Datenwerte in die neue Generally-Tabelle kopieren, sofern das Feld existiert
	 				if(array_search($key, array('id', 'reportnumber_id', 'created', 'modified')) !== false) continue;
	 				if($this->$_ReportData->hasField($key)) $orderforGeneral[$key] = $value;
	 			}

	 			$this->$_ReportData->save($orderforGeneral);
	 			$this->Autorisierung->Logger($id,$orderforGeneral);
	 		}
	 	}

		// bei einem Schweißertest werden dessen Daten in den Prüfbericht übernommen
		if(isset($this->request->data['welder_id'])) $this->Data->SetDataForWelderTest($this->request->data['welder_id'],$data,$generallyFororder,$verfahren);

	 	// die erzeugten IDs aus den angelegten Datensätzen
	 	// in die entsprechenden Felder der Reportnummertabelle eintragen
	 	$data = array(
	 			'id' =>	$id,
				'version' => $Version,
	 			'master' =>	$master,
	 			'data_ids' => trim($ReportDataId)
	 	);

	 	$this->Reportnumber->save($data);

	 	// den ersten Archiveintrag vornehmen
	 	$this->Data->Archiv($id,$Verfahren);
	 	$ArchivDatafromXml = $this->Xml->ArchivDatafromXml($verfahren);

	 	// Die aktuelle XML-Vorlage wird in der Settingstabelle gespeichert
	 	$optionsArchiv = array('conditions' => array('reportnumber_id' => $id));
	 	//		$data = $this->$ReportData[3]->find('first',$optionsArchiv);
	 	$settingsArchiv = array(
	 			'id' => isset($data[$ReportData[3]]['id']) ? $data[$ReportData[3]]['id'] : '' ,
	 			'data' => 1
	 	);

		$FormName['controller'] = 'reportnumbers';
		$FormName['action'] = 'edit';
		$FormName['terms'] = implode('/',array($projectID,$cascadeID,$orderID,$reportID,$id));

	 	// Weiterleitung zum bearbeiten
	 	$this->set('FormName', $FormName);
	 }

	 public function enforce() {

	 	if($this->writelock) {
	 		$this->render('/closemodal',null);
	 		return;
	 	}

		$this->Report->EnforceAssignedReports();

	 }

	 public function edit($id = null) {

	 	$this->Session->write('Id_for_refresh',true);

		$this->request->projectvars['VarsArray'][5] = 0;
		$this->request->projectvars['VarsArray'][6] = 0;

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$breads = $this->Navigation->Breads(null);
		$rel_menue = null;

	 	if(isset($this->request['data']['rel_menue'])){
	 		$rel_menue = $this->request['data']['rel_menue'];
	 		$this->Session->write('editmenue',array($id => $rel_menue));

			if(isset($this->request['data']['error'])){
				$ShowValidationErros = $this->request['data']['field_id'];
			}
	 	}
	 	elseif($this->Session->read('editmenue')) {
	 		$editmenue = $this->Session->read('editmenue');
	 		if(isset($editmenue[$id])){
	 			$rel_menue = $editmenue[$id];
	 		}
	 	}

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}


	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);

	 	if(isset($reportnumbers['Reportnumber']['_number'])) {
	 		$reportnumbers['Reportnumber']['number'] = $reportnumbers['Reportnumber']['_number'];
	 	}

	 	$this->Reportnumber->HiddenField->recursive = -1;
	 	$reportnumbers['HiddenField'] = array_map(function($elem) { return $elem['HiddenField']; }, $this->Reportnumber->HiddenField->find('all', array('conditions'=>array('HiddenField.reportnumber_id'=>$id))));

	 	$testingmethods = $this->Reportnumber->Report->Testingmethod->find('list');

	 	// Test ob der Bericht gelöscht wurde
	 	$this->Data->DeleteTest($reportnumbers);

	 	// Test ob der Bericht noch geschlossen werden muss
		$reportnumbers = $this->Data->StatusTest($reportnumbers);

	 	if(isset($this->request->data['hideField'])) {
	 		if(preg_match('/HiddenField(Report[A-Za-z0-9]+(Generally|Specific|Evaluation))0(.+)/', $this->request->data['field'], $field)) {
	 			$value = $this->request->data['value'] == 'true';

	 			$hiddenField = $this->Reportnumber->HiddenField->find('first', array('conditions'=>array('field'=> $field[1] . '.' . $field[3])));
	 			// Abbrechen, wenn die Aktion nicht nötig ist (schon gelöscht oder bereits angelegt)
	 			if(count($hiddenField) > 0 && !$value) die();
	 			if(count($hiddenField) == 0 && $value) die();

	 			if(count($hiddenField) > 0) $hiddenField['HiddenField'] = array_merge(array('id'=>null, 'reportnumber_id'=>$id), $hiddenField['HiddenField']);
	 			else $hiddenField = array('HiddenField' => array('id'=>null, 'reportnumber_id'=>$id, 'model'=>$field[1], 'field'=>$field[3], 'user_id'=>$this->Auth->user('id')));

	 			if(!$value) {
	 				$this->Reportnumber->HiddenField->save($hiddenField, array('callbacks'=>false));
	 			} else {
	 				$this->Reportnumber->HiddenField->delete($hiddenField['HiddenField']['id'], false);
	 			}
	 			exit();
	 		}
	 	}

	 	$this->Reportnumber->recursive = -1;

	 	$optionBeforeArray = array();
	 	$optionNextArray = array();

	 	$optionNextArray = array(
	 				'Reportnumber.topproject_id' => $projectID,
	 				'Reportnumber.cascade_id' => $cascadeID,
	 				'Reportnumber.order_id' => $orderID,
	 				'Reportnumber.report_id' => $reportID,
	 				'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
	 				'Reportnumber.id >' => $id,
	 	);

		$optionBeforeArray = array(
	 				'Reportnumber.topproject_id' => $projectID,
	 				'Reportnumber.cascade_id' => $cascadeID,
	 				'Reportnumber.order_id' => $orderID,
	 				'Reportnumber.report_id' => $reportID,
	 				'Reportnumber.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
	 				'Reportnumber.id <' => $id,
	 	);

	 	$optionsNext = array(
	 			'fields' => array('id','number','year','testingmethod_id'),
	 			'order' => array('Reportnumber.id' => 'asc'),
	 			'conditions' => $optionNextArray,
	 	);
	 	$optionsBefore = array(
	 			'fields' => array('id','number','year','testingmethod_id'),
	 			'order' => array('Reportnumber.id' => 'desc'),
	 			'conditions' => $optionBeforeArray,
	 	);

	 	$reportBefore = $this->Reportnumber->find('first', $optionsBefore);
	 	$reportNext = $this->Reportnumber->find('first', $optionsNext);

	 	$this->set('reportBefore',$reportBefore);
	 	$this->set('reportNext',$reportNext);
	 	$this->set('testingmethods',$testingmethods);

	 	$this->Reportnumber->recursive = 0;
	 	// verschieden Varablen bereitstellen

	 	$reportnumbers = $this->Data->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));
	 	$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren;

	 	$ReportGenerally = $this->request->tablenames[0];
	 	$ReportSpecific = $this->request->tablenames[1];
	 	$ReportEvaluation = $this->request->tablenames[2];
	 	$ReportArchiv = $this->request->tablenames[3];
	 	$ReportSettings = $this->request->tablenames[4];
	 	$ReportPdf = $this->request->tablenames[5];

		$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

//	 	$reportnumbers = $this->Drops->DropdownData($ReportArray,$arrayData,$reportnumbers);
//	 	$reportnumbers = $this->Data->MultiselectData($ReportArray,$arrayData,$reportnumbers);
//	 	$reportnumbers = $this->Data->ChangeDropdownData($reportnumbers, $arrayData, $ReportArray);
		$reportnumbers = $this->Drops->CollectMasterDropdown($reportnumbers);
//		$reportnumbers = $this->Drops->CollectMasterDependency($reportnumbers);
//		pr($reportnumbers['Dropdowns']['ReportRtSpecific']['test_arrangement_acc_to']);

		$reportnumbers = $this->Drops->CollectModulDropdown($reportnumbers);
	 	$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Data->SortAllDropdownArraysNatural($reportnumbers);
		$reportnumbers = $this->Drops->CheckDropdownAddLinks($reportnumbers);

		// Wenn keine Daten im Array sind
	 	if(!isset($arrayData['generaloutput'])){$arrayData['generaloutput'] = null;}
	 	if(!isset($arrayData['specificoutput'])){$arrayData['specificoutput'] = null;}
	 	if(!isset($arrayData['evaluationoutput'])){$arrayData['evaluationoutput'] = null;}

		$modelsarray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

		$revisionvalues = $this->Data->CheckRevisionValues($reportnumbers,$modelsarray,$arrayData['settings']);

	 	$reportnumbers['RevisionValues'] = $revisionvalues;

	 	if(Configure::check('RepairManager') && Configure::read('RepairManager') == true){
		 	$reportnumbers = $this->Repairtracking->GetRepairReport($reportnumbers);
		 	$reportnumbers = $this->Repairtracking->GetRepairStatus($reportnumbers,$ReportEvaluation);
	 	}

		$this->Search->CheckSearchingValueForReport($id,$Verfahren,$reportnumbers);

		$reportnumbers = $this->Templates->TemplatesDropdown($reportnumbers);

	 	$this->request->data = $reportnumbers;

	 	$breads = $this->Navigation->BreadForReport($reportnumbers,'edit');

	 	$welds = @$this->Data->WeldSorting($reportnumbers[$ReportEvaluation]);

	 	// Die Dropdown müssen für Jeditabel vorbereitet werden
	 	$Dropdown = array();
	 	if(isset($reportnumbers['JSON'])){
	 		$Dropdown = $reportnumbers['JSON'];
	 	}

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);

	 	if(Configure::read('WorkloadManager') == true){
//	 		$SettingsArray['workloadlink'] = array('discription' => __('Examiner workload'), 'controller' => 'examiners', 'action'=>'list_workload', 'terms' => $this->request->projectvars['VarsArray']);
	 	}

	 	$urlvariablen = null;

	 	$editmenue = array();
	 	$editmenue[0] = array(
	 			'id' => 'EditGeneral',
	 			'class' => 'active',
	 			'discription' => __('Show General infos',true),
	 			'rel' => 'General'
	 	);
	 	$editmenue[1] = array(
	 			'id' => 'EditSpecify',
	 			'class' => 'deaktive',
	 			'discription' => __('Show Specify infos',true),
	 			'rel' => 'Specify'
	 	);
	 	$editmenue[2] = array(
	 			'id' => 'TestingArea',
	 			'class' => 'deaktive',
	 			'discription' => __('Show Testing area',true),
	 			'rel' => 'TestingArea'
	 	);


	 	if(isset($rel_menue)){
	 		switch($rel_menue) {
	 			case ('General'):
	 				$editmenue[0]['class'] = 'active';
	 				$editmenue[1]['class'] = 'deaktive';
	 				$editmenue[2]['class'] = 'deaktive';
	 				break;
	 			case ('Specific'):
	 				$editmenue[0]['class'] = 'deaktive';
	 				$editmenue[1]['class'] = 'active';
	 				$editmenue[2]['class'] = 'deaktive';
	 				break;
				case ('Specify'):
		 			$editmenue[0]['class'] = 'deaktive';
		 			$editmenue[1]['class'] = 'active';
		 			$editmenue[2]['class'] = 'deaktive';
		 			break;
	 			case ('TestingArea'):
	 				$editmenue[0]['class'] = 'deaktive';
	 				$editmenue[1]['class'] = 'deaktive';
	 				$editmenue[2]['class'] = 'active';
	 				break;
	 		}

	 	}

		$this->request->data['EditMenue']['Menue'] = $editmenue;
		$this->request->data['EditMenue']['Status'] = $rel_menue;

		$this->Additions->Add($arrayData);

		$this->Testinstruction->CheckFormularData($reportnumbers,$arrayData['settings']);

		$ReportMenue = $this->Navigation->createReportMenue($reportnumbers,$arrayData['settings']);
//pr($ReportMenue);
		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);
		$reportnumbers['ModulData']  = $this->Data->GetModulData($arrayData,$modelsarray,$reportnumbers);


		// Wenn nicht ausgefüllte Pflichtfelder markiert werden sollen
		if(isset($ShowValidationErros)){

			$ValidationErrors = $this->Reportnumber->getValidationErrors($arrayData, $reportnumbers);
			$ValidationErrorsJSON = array();
			foreach($ValidationErrors as $_key => $_data){
				foreach($_data as $__key => $__data){
					$Model = 'Report' . $Verfahren . $_key;
					$arrayData['settings']->$Model->$__key->validate->error = 1;
				}
			}
		}

		if($this->Session->read('editmenue') == NULL){
			$this->Session->write('editmenue',array($id => 'General'));
		} else {
			$editmenue = $this->Session->read('editmenue');
			if(!isset($editmenue[$id])){
				$editmenue[$id] = 'General';
				$this->Session->write('editmenue',$editmenue);
			}
		}

		$this->Navigation->ModulNavigation($cascadeID,'ndt');

	 	$this->set('ReportMenue', $ReportMenue);
	 	$this->set('editmenue', $editmenue);
	 	$this->set('urlvariablen', $urlvariablen);
	 	$this->set('projectID', $projectID);
	 	$this->set('cascadeID', $cascadeID);
	 	$this->set('orderID', $orderID);
	 	$this->set('reportID', $reportID);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('dropdowns', $Dropdown);
	 	$this->set('verfahren', $verfahren);
	 	$this->set('breads', $breads);
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('welds', @$welds);
	 	$this->set('generaloutput', $arrayData['generaloutput']);
	 	$this->set('specificoutput', $arrayData['specificoutput']);
	 	$this->set('evaluationoutput', $arrayData['evaluationoutput']);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('locale', $this->Lang->Discription());
	 	$this->set('name', 'Reportnumber');

	 	$users = $this->Reportnumber->User->find('list');
	 	$this->set(compact('users'));

		$this->Autorisierung->IsThisMyReport($id);

	 }

	 // wird in den Controller Testinginstructions verschoben
	 public function testinstruction(){

//		 $this->autoRender = false;
		 $this->layout = 'blank';

		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];

		 $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
		 $this->Reportnumber->recursive = 0;
		 $reportnumbers = $this->Reportnumber->find('first', $options);
		 $reportnumbers = $this->Data->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));
		 $verfahren = $this->request->verfahren;
		 $Verfahren = $this->request->Verfahren;

		 $this->request->data['Testingmethod']['id'] = $reportnumbers['Testingmethod']['id'];

		 $arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

		 $this->Testinstruction->CheckFormularData($reportnumbers,$arrayData['settings']);

		 $request['data'] = $this->request->testinstruction->data;

		 $this->set('response',json_encode($request));
	 }

	 public function evaluation($id = null) {

	 	$this->layout = 'blank';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Reportnumber->autoClose($id);
	 	$this->Autorisierung->IsThisMyReport($id);

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$this->Data->DeleteTest($reportnumbers);

		$reportnumbers = $this->Report->SetModelNames($reportnumbers);

		$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren;
		$reportnumbers['xml'] = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

		$reportnumbers = $this->Report->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));
		$reportnumbers = $this->Report->GetRepairReport($reportnumbers);
		$reportnumbers = $this->Report->GetRepairStatus($reportnumbers);
		$reportnumbers = $this->Report->MapRepairs($reportnumbers);
//	 	$reportnumbers = $this->Data->DropdownData($reportnumbers['Tablenames'],$reportnumbers['xml'],$reportnumbers);
		$reportnumbers = $this->Drops->CollectMasterDropdown($reportnumbers);

		$reportnumbers = $this->Report->WeldSorting($reportnumbers);
		$reportnumbers = $this->Report->DevelopmentsEnabled($reportnumbers);
		$Dropdown = $this->Report->DropdownJson($reportnumbers);

	 	$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Report->CheckRevisionValues($reportnumbers);

		$this->request->data = $reportnumbers;
		$arrayData = $reportnumbers['xml'];

		// Wenn keine Daten im Array sind
	 	if(!isset($arrayData['generaloutput'])){$arrayData['generaloutput'] = null;}
	 	if(!isset($arrayData['specificoutput'])){$arrayData['specificoutput'] = null;}
	 	if(!isset($arrayData['evaluationoutput'])){$arrayData['evaluationoutput'] = null;}

	 	$this->set('validationErrors', $this->Reportnumber->getValidationErrors($arrayData, $reportnumbers));
	 	$this->set('dropdowns', $Dropdown);
	 	$this->set('verfahren', $verfahren);
	 	$this->set('settings', $arrayData['settings']);
//	 	$this->set('welds', $Welds);
	 	$this->set('evaluationoutput', $arrayData['evaluationoutput']);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('locale', $this->Lang->Discription());
	 	$this->set('name', 'Reportnumber');

	 }

	 public function resetEvaluation() {
	 	$this->layout = 'modal';

	 	$id = $this->request->reportnumberID;
	 	$this->Autorisierung->IsThisMyReport($id);

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$this->Data->DeleteTest($reportnumbers);

	 	$model = 'Report'.ucfirst($reportnumbers['Testingmethod']['value']).'Evaluation';

	 	$this->loadModel($model);
	 	$this->$model->recursive = -1;

	 	$ids = $this->$model->find('list', array('fields'=>array('id'), 'conditions'=>array('deleted'=>0, 'reportnumber_id'=>$id)));
	 	$ids = array_values(array_map(function($_id) use($model) { return array($model=>array('id'=>$_id, 'sorting'=>0)); }, $ids));

	 	if($this->$model->saveMany($ids, array('callbacks'=>false))) {
	 		$this->Session->setFlash(__('Sorting was reset'));
	 	} else {
	 		$this->Session->setFlash(__('Sorting could not be reset'));
	 	}

	 	$this->set('afterEdit', $this->Navigation->afterEdit(Router::url(array_merge(array('controller'=>'reportnumbers', 'action'=>'edit'), $this->request->projectvars['VarsArray'])), 100));

	 	$this->render('/closemodal', 'modal');
	 }

	 public function savejson($id = null) {

		 App::uses('Sanitize', 'Utility');

	 	// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evalId = $this->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->request->projectvars['VarsArray'][6];

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$safevars['projectID'] = $projectID;
	 	$safevars['cascadeID'] = $cascadeID;
	 	$safevars['orderID'] = $orderID;
	 	$safevars['reportID'] = $reportID;
	 	$safevars['id'] = $id;
	 	$safevars['evalId'] = $evalId;
	 	$safevars['weldedit'] = $weldedit;

	 	$this->layout = 'json';

/*
	 	if(!$this->Session->read('Auth')) {
	 		$this->Session->setFlash(__('You were logged out due inactivity'));
	 		$this->set('afterEDIT', $this->Navigation->afterEDIT(Router::url(array('controller'=>'users', 'action'=>'login'))));
	 		$this->render('afteredit');
	 		return;
	 	}
*/
	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

		$aktion_for_revision = 'edit';

		$this->request->controller = $this->request->data['controller'];
	 	$this->request->action = $this->request->data['action'];

	 	$this->Autorisierung->IsThisMyReport($id);
		$this->Reportnumber->recursive = 1;
	 	$reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));
		$reportnumber['SafeVars'] = $safevars;
		$reportnumber = $this->Data->EditAfterClosing($reportnumber);
		$reportnumber = $this->Data->EditOnlyBy($reportnumber);

		$reportnumber['this_id'] = $this->request->data['this_id'];
	 	$reportnumber['orginal_data'] = $this->request->data['orginal_data'];

		unset($this->request->data['orginal_data']);

	 	foreach($this->request->data as $_key => $_data){
	 		if(!is_array($_data)){
	 			unset($this->request->data[$_key]);
	 		}
	 	}

		$reportnumber['ReportModel'] = key($this->request->data);
		$this->loadModel($reportnumber['ReportModel']);
		$reportnumber = $this->SaveData->LastValue($reportnumber);
		$reportnumber = $this->SaveData->ThisValue($reportnumber);
		$reportnumber = $this->SaveData->LastIdForRadio($reportnumber);
		$reportnumber = $this->SaveData->TransportArrayOne($reportnumber);
		$reportnumber = $this->SaveData->StatusTest($reportnumber);


		$reportnumber = $this->SaveData->CheckEvaluationDescriptions($reportnumber);

		if(isset($reportnumber['FlashMessages']) && count($reportnumber['FlashMessages']) > 0){

			$DelArray = array('User','Reportfile','Reportimage','EquipmentType','ExaminerTime','SafeVars','Reportnumber','LogArray','LastRecord','lastRecord','_id','option','TransportArray','last_value','orginal_data');
			$reportnumber = $this->SaveData->DeleteElements($reportnumber,$DelArray);
	 		$this->set('request', json_encode($reportnumber));
	 		$this->render();
	 		return;
	 	}

		$reportnumber = $this->SaveData->CreateOption($reportnumber);
		$reportnumber = $this->SaveData->WeldEdit($reportnumber);
		$reportnumber = $this->SaveData->PositionEdit($reportnumber);
		$reportnumber = $this->SaveData->LastRecord($reportnumber);
		$reportnumber = $this->SaveData->TransportArrayTwo($reportnumber);
		$reportnumber = $this->SaveData->CleanErrors($reportnumber);
		$reportnumber = $this->SaveData->SaveData($reportnumber);

		$this->Search->UpdateTable($reportnumber,$reportnumber['ReportModel'],$reportnumber['field_for_saveField'],$reportnumber['this_value_for_saveField']);

		$reportnumber = $this->SaveData->SaveRevision($reportnumber);
		$reportnumber = $this->SaveData->SaveTechnicalPlace($reportnumber);
		$reportnumber = $this->SaveData->UpdateLastUser($reportnumber);
		$reportnumber = $this->SaveData->UpdateReportnumberResult($reportnumber);
		$reportnumber = $this->SaveData->UpdateDevelopmentData($reportnumber);
		$reportnumber = $this->SaveData->CheckWeldResult($reportnumber);

		$this->Autorisierung->Logger($id,$reportnumber['LogArray']);

		if(!isset($reportnumber['SaveOkay'])){
			$Data['FlashMessages'][]  = array('type' => 'error','message' => __('The value could not be saved. Please try again!'));
		}

		$DelArray = array('User','Reportfile','Reportimage','EquipmentType','ExaminerTime','SafeVars','Reportnumber','LogArray','LastRecord','lastRecord','_id','option','TransportArray','last_value','orginal_data');

		$reportnumber = $this->SaveData->DeleteElements($reportnumber,$DelArray);

		$this->set('request', json_encode($reportnumber));

	 }

	 public function save($id = null) {

		 App::uses('Sanitize', 'Utility');

	 	// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evalId = $this->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->request->projectvars['VarsArray'][6];

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$safevars['projectID'] = $projectID;
	 	$safevars['cascadeID'] = $cascadeID;
	 	$safevars['orderID'] = $orderID;
	 	$safevars['reportID'] = $reportID;
	 	$safevars['id'] = $id;
	 	$safevars['evalId'] = $evalId;
	 	$safevars['weldedit'] = $weldedit;

	 	$this->layout = 'blank';

	 	if(!$this->Session->read('Auth')) {
	 		$this->Session->setFlash(__('You were logged out due inactivity'));
	 		$this->set('afterEDIT', $this->Navigation->afterEDIT(Router::url(array('controller'=>'users', 'action'=>'login'))));
	 		$this->render('afteredit');
	 		return;
	 	}

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Autorisierung->IsThisMyReport($id);
	 	$this->Reportnumber->recursive = -1;
	 	$reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));

		 if(isset($this->request->data['examiner_id'])) {
			$this->Reportnumber->id = $id;
			$this->Reportnumber->saveField('examiner_id', $this->request->data['examiner_id']);
		}

		if(isset($this->request->data['supervisor_id'])) {
			$this->Reportnumber->id = $id;
			$this->Reportnumber->saveField('supervisor_id', $this->request->data['supervisor_id']);
		}

		if(!isset($this->request->data['this_id'])) return;

	 	$this_id = $this->request->data['this_id'];
	 	$orginal_data = $this->request->data['orginal_data'];
	 	$this->request->controller = $this->request->data['controller'];
	 	$this->request->action = $this->request->data['action'];

		$reportnumber = $this->Data->EditAfterClosing($reportnumber);
		$reportnumber = $this->Data->EditOnlyBy($reportnumber);

	 	unset($this->request->data['orginal_data']);

	 	foreach($this->request->data as $_key => $_data){
	 		if(!is_array($_data)){
	 			unset($this->request->data[$_key]);
	 		}
	 	}

	 	$ReportModel = key($this->request->data);

		if(isset($orginal_data[$ReportModel][key($this->request->data[$ReportModel])])){
			$last_value = $orginal_data[$ReportModel][key($this->request->data[$ReportModel])];
		} else {
			$last_value = null;
		}

	 	$this_value = $this->request->data[$ReportModel][key($this->request->data[$ReportModel])];
		if(is_array($this_value)) $this_value = implode(',',$this_value);

		$this_value = $this->Data->SanitizeInput($this_value);

	 	$last_id_for_radio = $ReportModel . Inflector::camelize(key($this->request->data[$ReportModel]));

		// diese Array wird der Revison übergeben, wenn nötig
		$TransportArray['model'] = $ReportModel;
		$TransportArray['row'] = key($this->request->data[$ReportModel]);
		$TransportArray['this_id'] = $this_id;
		$TransportArray['last_value'] = $last_value;
		$TransportArray['this_value'] = $this_value;
		$TransportArray['last_id_for_radio'] = $last_id_for_radio;
		$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');

	 	$this->set('ReportModel', $ReportModel);
	 	$this->set('this_id',$this_id);
	 	$this->set('last_value',$last_value);
	 	$this->set('this_value',$this_value);
	 	$this->set('last_id_for_radio',$last_id_for_radio);

		// Wenn die Prüfberichtsmappe gesperrt ist
	 	if(isset($reportnumber['Reportnumber']['no_rights']) && $reportnumber['Reportnumber']['no_rights'] == 1) {
	 		$this->set('error', __('Report is writeprotected - changes will not be saved.'));
	 		$this->render();
	 		return;
	 	}

	 	// Wenn die Prüfberichtsmappe gesperrt ist
	 	if($this->writeprotection) {
	 		$this->set('error', __('Report is writeprotected - changes will not be saved.'));
	 		$this->render();
	 		return;
	 	}

	 	// Wenn der Prüfbericht abgerechnet ist
	 	if($reportnumber['Reportnumber']['settled'] > 0){
	 		$this->set('closed',__('The value could not be saved. The report is closed!'));
	 		$this->render();
	 		return;
	 	}

	 	// Wenn der Prüfbericht gelöscht ist
	 	if($reportnumber['Reportnumber']['delete'] > 0){
	 		$this->set('closed',__('The value could not be saved. The report is marked as deleted!'));
	 		$this->render();
	 		return;
	 	}

	 	// Wenn der Prüfbericht deaktiviert ist
	 	if($reportnumber['Reportnumber']['deactive'] > 0){
	 		$this->set('closed',__('The value could not be saved. The report is marked as deactivate!'));
	 		$this->render();
	 		return;
	 	}

	 	// Wenn der Prüfbericht geschlossen ist
	 	if($reportnumber['Reportnumber']['status'] > 0){

			$reportnumber = $this->Data->RevisionCheckTime($reportnumber);

			if(!isset($reportnumber['Reportnumber']['revision_write'])){
	 			$this->set('closed',__('The value could not be saved. The report is closed!'));
	 			$this->render();
	 			return;
			}
			if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1){
			}
	 	}

		$aktion_for_revision = 'edit';

	 	$this->loadModel($ReportModel);

		$Request = $this->Data->CheckDate();

		if($Request === false){
	 		$this->set('error',__('The value could not be saved. Date format not valide.'));
	 		$this->render();
	 		return;
	 	}

	 	if(strpos($ReportModel,'Evaluation') === false){
	 		$option = array($ReportModel.'.reportnumber_id' => $id);
	 	}

	 	if(strpos($ReportModel,'Evaluation') !== false){

	 		$id_for_development = array($evalId);

			$aktion_for_revision = 'editevaluation';
			$TransportArray['table_id'] = $evalId;

	 		if($evalId == 0){
	 			$this->$ReportModel->create();
	 			$this->$ReportModel->save(array('reportnumber_id' => $id));
	 			$evalId = $this->$ReportModel->getInsertID();
	 			$id_for_development = array($evalId);

				$aktion_for_revision = 'addevaluation';

	 			$this->set('afterEDIT', $this->Navigation->afterEDIT('reportnumbers/editevalution/'.$projectID.'/'.$cascadeID.'/'.$orderID.'/'.$reportID.'/'.$id.'/'.$evalId.'/0',0));
	 		}

	 		$option = array($ReportModel.'.reportnumber_id' => $id,$ReportModel.'.id' => $evalId,$ReportModel.'.deleted' => 0);
	 	}

	 	// Das muss noch im Helper gefixt werden
	 	if(is_array($this_value)){
	 		$this_value = $this_value[0];
	 	}

	 	if($weldedit == 1 && $evalId > 0 && strpos($ReportModel,'Evaluation') !== false){
	 		// IDs von allen Nahtabschnitten mit der selben Description holen
	 		$_ids = $this->$ReportModel->find('all',
	 				array(
	 						'fields' => array($ReportModel.'.id'),
	 						'joins' => array(
	 								array(
	 										'alias' => 'O'.$ReportModel,
	 										'table' => $this->$ReportModel->table,
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

	 		$id_for_development = array();
			$_id = array();
			$this_value_for_saveField = '';
	 		foreach($_ids as $__ids){
	 			$id_for_development[] = $__ids[$ReportModel]['id'];
	 		}

	 		$_option = array($ReportModel=>array(key($this->request->data[$ReportModel]) => $this_value));
	 		$option = array_map(function($elem) use($_option) { return array_merge_recursive($elem, $_option); }, $_ids);
	 		$field_for_saveField = key($this->request->data[$ReportModel]);
			$_id = $id_for_development;
			$this_value_for_saveField = $this_value;
	 	}
	 	else {

	 		$_id = $this->$ReportModel->find('list',array('conditions'=>$option, 'limit'=>1, 'fields'=>array('id','id')));
			$Testvar = array('1234');
	 		$option = array($ReportModel.'.id' => reset($_id), $ReportModel.'.'.key($this->request->data[$ReportModel]) => $this_value);
	 		$option = array(Hash::expand($option));
	 		$field_for_saveField = key($this->request->data[$ReportModel]);
	 		$this_value_for_saveField = $this_value;
	 	}

		$lastRecord = $this->$ReportModel->findById(reset($_id));

		$TransportArray['table_id'] = reset($_id);
		$TransportArray['last_value'] = $lastRecord[$ReportModel][$field_for_saveField];
		$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');
		$TransportArray['last_value'] = $lastRecord[$ReportModel][$field_for_saveField];

		if(isset($TransportArray)) $reportnumber['LastRecord'] = $TransportArray;

		$this->Search->UpdateTable($reportnumber,$ReportModel,$field_for_saveField,$this_value_for_saveField);

	 	if(is_array($option)) {

	 		if(isset($field_for_saveField) && isset($this_value_for_saveField)){

	 			$this->$ReportModel->id = reset($_id);

	 			$this->$ReportModel->saveField($field_for_saveField ,$this_value_for_saveField);

				if(Configure::check('SignatoryDeleteRequired') && Configure::read('SignatoryDeleteRequired') == true){
						$this->Data->RequiredFieldDeleteSignatory($reportnumber,$field_for_saveField);
				}

	 			$LogArray = array($ReportModel => array($field_for_saveField => $this_value_for_saveField));

//		 		$this->Search->UpdateTable($reportnumber,$ReportModel,$field_for_saveField,$this_value_for_saveField);
	 		}
	 		else {

	 			foreach($option as $_key => $_option){

					$lastRecord = $this->$ReportModel->findById($_option[$ReportModel]['id']);

					$TransportArray['last_value'] = $lastRecord[$ReportModel][$field_for_saveField];
					$TransportArray['table_id'] = $_option[$ReportModel]['id'];
					$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');

					if(isset($TransportArray)) $reportnumber['LastRecord'] = $TransportArray;;

	 				$this->$ReportModel->id = $_option[$ReportModel]['id'];
	 				$this->$ReportModel->saveField($field_for_saveField, $_option[$ReportModel][$field_for_saveField]);

					$this->Search->UpdateTable($reportnumber,$ReportModel,$field_for_saveField,$_option[$ReportModel][$field_for_saveField]);

	 				$LogArray = array($ReportModel => array($field_for_saveField => $_option[$ReportModel][$field_for_saveField]));

	 			}
	 		}
	 		$reportnumber['Reportnumber']['revision_progress'] > 0?$this->Data->SaveRevision($reportnumber,$TransportArray,$aktion_for_revision):'';
	 		//Technical place aktualisieren
	 		if(preg_match('/TechnicalPlace/', $this_id)) {
	 			$_data = $this->$ReportModel->find('first', array('fields'=>array('id', 'technical_place_level1', 'technical_place_level2', 'technical_place_level3'), 'conditions'=>array('reportnumber_id'=>$id)));
	 			$technical_place = $_data[$ReportModel]['technical_place_level1'].'='.$_data[$ReportModel]['technical_place_level2'].'='.$_data[$ReportModel]['technical_place_level3'];
	 			$technical_place = trim(preg_replace('/[=]+/', '=', $technical_place), '=');
	 			$saveData[$ReportModel] = array('technical_place'=>$technical_place);
	 			$this->$ReportModel->save($saveData);
	 			$this->Autorisierung->Logger($id, $saveData);
	 		}

	 		// Reportnumberstabelle, User aktualisieren
	 		$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'));
	 		$this->Reportnumber->save($reportdata);
	 		if(strpos($ReportModel,'Evaluation') !== false && isset($LogArray[$ReportModel])){
				 $LogArray = $LogArray[$ReportModel];
				$LogArray['id'] = $evalId;
			}

		//Reportnumber Prüfdatum eintragen
			$this->Data->UpdateDateOfTest($id);

			if(strpos($ReportModel,'Evaluation') !== false && $field_for_saveField == 'result'){
				$evalne = $this->$ReportModel->find('all',array('conditions' => array(
				$ReportModel.'.reportnumber_id'=>$id,
				$ReportModel.'.deleted'=>0,
				$ReportModel.'.result'=>2
				)));
				if(isset($evalne) && !empty($evalne)){
					$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 2);
				} else {
					$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 1);
				}

				$this->Reportnumber->save($reportdata);
			}

	 		$this->Autorisierung->Logger($id,$LogArray);

//	 		$this->request->data[$ReportModel][0]['reportnumber_id'] = $id;
	 		$this->request->data[$ReportModel]['reportnumber_id'] = $id;

	 		if(Configure::read('DevelopmentsEnabled') == true && strpos($ReportModel,'Evaluation') !== false && $field_for_saveField == 'result'){

	 			if($weldedit == 0 && $evalId > 0 && count($id_for_development) == 1){
	 				$forDevelopment = $this->$ReportModel->find('list',array('fields'=>array('description'),'conditions'=>array($ReportModel.'.reportnumber_id'=>$id)));
	 				$id_for_development = array_keys($forDevelopment,$forDevelopment[$id_for_development[0]]);
	 			}

	 			$this->Data->DevelopmentReset($orderID,$id_for_development);
	 			$this->set('DevelopmentReset',true);
	 		}

	 		//			$this->Autorisierung->Logger($id,$this->request->data[$ReportModel][0]);
	 		if(isset($this_value_for_saveField) && $this_value_for_saveField == ''){
	 			$this->set('empty',__('The value has been removed.'));
	 		}
	 		else {
	 			$this->set('success',__('The value has been saved.'));
	 		}

	 		// Wenn ein Prüfbericht aufgerufen und bearbeitet wird
	 		if($this->Session->check('Id_for_refresh') == true){
	 			$this->Session->delete('Id_for_refresh');
	 		}
	 	}
	 	else {
	 		$this->set('error',__('The value could not be saved. Please try again!'));
	 	}
	 }

	 public function refresh($id = null) {

	 	$this->layout = 'blank';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evalId = $this->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->request->projectvars['VarsArray'][6];

	 	$this->Reportnumber->recursive = -1;
	 	$Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));

		// Wenn der Prüfbericht nach einem Zeitraum geschlossen werden soll
		if(Configure::check('CloseMethode') && Configure::read('CloseMethode') == 'showReportVerificationByTime'){

			if(Configure::check('CloseMethodeTime')){
				$limit = intval(Configure::read('CloseMethodeTime'));
				$limit_green = round(($limit / 100 * 50) / 60,0);
				$limit_orange = round(($limit / 100 * 10) / 60,0);
			} else {
				$limit = 0;
				$limit_green = 0;
				$limit_orange = 0;
			}

			$printtime = intval($Reportnumber['Reportnumber']['print']);
			$closetime = $printtime + $limit;
			$currenttime = time();
			$countdown = ($closetime - $currenttime);
			$countdown_min = round($countdown / 60,0);

			$format = '%d.%m.%Y %H:%M:%S';

			if($countdown > 0){
				$this->set('countdown',$countdown_min);
				$this->set('limit',round($limit / 60,0));
				$this->set('limit_green',$limit_green);
				$this->set('limit_orange',$limit_orange);
			}

			if($currenttime > $closetime && $Reportnumber['Reportnumber']['print'] > 0){
	 			$this->Reportnumber->autoClose($id);
				$this->set('closereport',true);
				$this->set('limit',round($limit / 60,0));
				$this->set('printtime',strftime($format,$printtime));
				$this->set('closetime',strftime($format,$closetime));
				$this->set('currenttime',strftime($format,$currenttime));
		 		$this->set('lastURL',$this->Session->read('lastURL'));
			}
		}

	 	if($this->Session->check('Id_for_refresh') == true){
			$this->set('stopmodal',true);
			return;
//	 		$this->_stop();
	 	}

	 	if($this->Session->check('Id_for_refresh') == false && $Reportnumber['Reportnumber']['modified_user_id'] != $this->Auth->user('id')){
	 		$this->User->recursive = -1;
	 		$User = $this->User->find('first',array('conditions' => array('User.id' => $Reportnumber['Reportnumber']['modified_user_id'])));
	 		$this->loadModel('Log');
	 		$this->Log->recursive = -1;
	 		$Log = $this->Log->find('first',array(
	 				'order' => array('Log.id DESC'),
	 				'conditions' => array(
	 						'Log.user_id' => $Reportnumber['Reportnumber']['modified_user_id'],
	 						'Log.controller' => 'reportnumbers',
	 				)
	 		)
	 				);

	 		$log = $this->Xml->XmltoArray($Log['Log']['message'],'string',null);
	 		$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'));
	 		$this->Reportnumber->save($reportdata);
	 		$this->set('User',$User);
	 		$this->set('Reportnumber',$Reportnumber);
	 		$this->set('edit_conflict',true);
	 		$this->set('lastURL',$this->Session->read('lastURL'));
	 	}
	 }

	 public function refreshrevision() {

	 	$this->layout = 'blank';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evalId = $this->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->request->projectvars['VarsArray'][6];

	 	$this->Autorisierung->IsThisMyReport($id);

	 	$this->Reportnumber->recursive = -1;
	 	$Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));

	 	$Reportnumber = $this->Data->RevisionCheckTime($Reportnumber);

		if(!isset($Reportnumber['Reportnumber']['revision_write']) || (isset($Reportnumber['Reportnumber']['revision_write']) && $Reportnumber['Reportnumber']['revision_write'] != 1)){
			$this->set('Reportnumber',$Reportnumber);
			$this->set('lastURL',$this->Session->read('lastURL'));
			return;
		}

		$Reportnumber['Reportnumber']['revision_refresh']['in_reporttime'] = Configure::read('RevisionInReportTime');
		$Reportnumber['Reportnumber']['revision_refresh']['starttime'] = $this->Session->read('revision.' . $id);
		$Reportnumber['Reportnumber']['revision_refresh']['endtime'] = $this->Session->read('revision.' . $id) + Configure::read('RevisionInReportTime');
		$Reportnumber['Reportnumber']['revision_refresh']['currenttime'] = time();
		$Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] = $this->Session->read('revision.' . $id) + Configure::read('RevisionInReportTime') - time();
		$Reportnumber['Reportnumber']['revision_refresh']['time_to_end_percent'] = round(100 * $Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] / $Reportnumber['Reportnumber']['revision_refresh']['in_reporttime'],2);

		if($Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] <= 0) $Reportnumber['Reportnumber']['revision_refresh']['close'] = true;
		elseif($Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] > 0) $Reportnumber['Reportnumber']['revision_refresh']['close'] = false;

		$Reportnumber['Reportnumber']['revision_refresh']['style_color'] = 'green';

		if($Reportnumber['Reportnumber']['revision_refresh']['time_to_end_percent'] < 20) $Reportnumber['Reportnumber']['revision_refresh']['style_color'] = 'orange';
		if($Reportnumber['Reportnumber']['revision_refresh']['time_to_end_percent'] < 5) $Reportnumber['Reportnumber']['revision_refresh']['style_color'] = 'red';

		$format = '%d-%m-%Y %X';

		$Reportnumber['Reportnumber']['revision_refresh']['starttime_format'] = strftime($format,$Reportnumber['Reportnumber']['revision_refresh']['starttime']);
		$Reportnumber['Reportnumber']['revision_refresh']['endtime_format'] = strftime($format,$Reportnumber['Reportnumber']['revision_refresh']['endtime']);
		$Reportnumber['Reportnumber']['revision_refresh']['currenttime_format'] = strftime($format,$Reportnumber['Reportnumber']['revision_refresh']['currenttime']);

		$Reportnumber['Reportnumber']['revision_refresh']['time_to_end_min'] = round($Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] / 60,0);
		$Reportnumber['Reportnumber']['revision_refresh']['time_to_end_hour'] = round($Reportnumber['Reportnumber']['revision_refresh']['time_to_end_min'] / 60,2);

		if($Reportnumber['Reportnumber']['revision_refresh']['time_to_end_hour'] >= 1) $Reportnumber['Reportnumber']['revision_refresh']['time_to_show'] = $Reportnumber['Reportnumber']['revision_refresh']['time_to_end_hour'] . ' ' .  __('hour(s)');
 		if($Reportnumber['Reportnumber']['revision_refresh']['time_to_end_min'] < 60) $Reportnumber['Reportnumber']['revision_refresh']['time_to_show'] = $Reportnumber['Reportnumber']['revision_refresh']['time_to_end_min'] . ' ' .  __('minute(s)');
 		if($Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] < 120) $Reportnumber['Reportnumber']['revision_refresh']['time_to_show'] = $Reportnumber['Reportnumber']['revision_refresh']['time_to_end'] . ' ' .  __('second(s)');

		$this->set('format',$format);
		$this->set('Reportnumber',$Reportnumber);
		$this->set('lastURL',$this->Session->read('lastURL'));
	 }

	 public function repair() {

	 	if(Configure::read('RepairManager') == false) return;

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evaluationID = $this->request->projectvars['VarsArray'][5];

	 	$this->layout = 'modal';

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	if(!Configure::check('ShowNeGlobally') || Configure::read('ShowNeGlobally') == false) {
		 	$this->Autorisierung->IsThisMyReport($id);
	 	}


	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);

	 	if($reportnumbers['Order']['status'] > 0) return;

	 	$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Data->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));
		$reportnumbers = $this->Repairtracking->RemoveNoDuplicateFields($reportnumbers);

		$ReportGenerally = $this->request->tablenames[0];
		$ReportSpecific = $this->request->tablenames[1];
		$ReportEvaluation = $this->request->tablenames[2];
		$ReportArchiv = $this->request->tablenames[3];
		$ReportSetting = $this->request->tablenames[4];

		$ReportTable['Generally'] = $ReportGenerally;
		$ReportTable['Specific'] = $ReportSpecific;
		$ReportTable['Evaluation'] = $ReportEvaluation;
		$ReportTable['Archiv'] = $ReportArchiv;
		$ReportTable['Setting'] = $ReportSetting;

		if($evaluationID > 0){
			unset($reportnumbers);
			$ReportOfRepairOptions = array('fields' => array('reportnumber_id'),'conditions' => array($ReportEvaluation . '.id' => $evaluationID));
			$ReportOfRepair = $this->$ReportEvaluation->find('first',$ReportOfRepairOptions);
			$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $ReportOfRepair[$ReportEvaluation]['reportnumber_id']));
		 	$reportnumbers = $this->Reportnumber->find('first', $options);
		 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);
		 	$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
			$reportnumbers = $this->Data->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));
		}

		$reportnumbers = $this->Repairtracking->GetRepairReport($reportnumbers);

		unset($reportnumbers['Repairs']['Statistic']);

		foreach($reportnumbers['Repairs'] as $_key => $_data){
			if(isset($_data['history'])) continue;

			if($evaluationID == 0){
			} else {
				if($evaluationID != $_data['mistake'][$this->request->tablenames[2]]['id']){
					unset($reportnumbers['Repairs'][$_key]);
				}
			}
		}


		if($evaluationID > 0){
			foreach($reportnumbers[$this->request->tablenames[2]] as $_key => $_data){
				if($this->request->projectvars['evalId'] == $_data[$this->request->tablenames[2]]['id']){
					$this->set('evaluation',$_data[$this->request->tablenames[2]]);
					$SingleEvaluation = $_data[$this->request->tablenames[2]];
					break;
				}
			}
		}

		// reparierte Nähte aussortieren
		foreach($reportnumbers['Repairs'] as $_key => $_data){
			if($reportnumbers['Repairs'][$_key]['class'] != 'open'){
				unset($reportnumbers['Repairs'][$_key]);
			}
		}

		if(isset($this->request->data['Reportnumber']['is_repair'])){

			$ReportnumberRepairs = $reportnumbers;

			$verfahren = $this->request->verfahren;
			$Verfahren = $this->request->Verfahren;

		 	$settings = $this->Xml->XmltoArray($verfahren,'file',$Verfahren);

	 		$GlobalReportNumbers = array();

	 		if(Configure::check('GlobalReportNumbers')) {
	 			switch(strtolower(Configure::read('GlobalReportNumbers'))) {
	 				case 'report':
	 					$GlobalReportNumbers['Reportnumber.report_id'] = $reportID;

	 				case 'order':
	 					$GlobalReportNumbers['Reportnumber.order_id'] = $orderID;

	 				case 'topproject':
	 					$GlobalReportNumbers['Reportnumber.topproject_id'] = $projectID;
	 			}
	 		}

	 		$options = array(
	 				'conditions' => array_filter($GlobalReportNumbers),
	 				'order' => 'Reportnumber.id DESC',
	 				'limit' => 1
	 		);

	 		$newID = false;

	 		if(!empty($reportnumbers)) {
	 			$newID = $this->Data->ResetNumberByYear($reportnumbers);
	 		}

	 		if($newID == false){
	 			$this->Set('message',__('The new report could not be saved. Please, try again.'));
				$this->Session->setFlash('The new report could not be saved. Please, try again.', 'default', array(), 'bad');
	 			$this->render('error');
	 			return;
	 		}

	 		$dataLog = array(
	 				'thisId' => $id,
	 				'oldId' => $id,
	 				'newId' => $newID
	 		);

	 		$this->Autorisierung->Logger($id,$dataLog);

	 		$dataLog = array(
	 				'thisId' => $newID,
	 				'oldId' => $id,
	 				'newId' => $newID
	 		);

	 		$this->Autorisierung->Logger($newID,$dataLog);

			// Die Ids aus der Suchtabelle müssen übernommenwerden
			// die Funktion muss noch verfeinert werden
//			$this->Search->UpdateTableForDuplication($newID,$id);
			$this->Search->DeleteSearchTableForDuplication($newID);

	 		// neu angelegten und zu duplizierenden Datensatz holen
	 		$optionsNew = array('conditions' => array('Reportnumber.id' => $newID));

	 		$dataNew = $this->Reportnumber->find('first',$optionsNew);

	 		$dataUpdate = array();
	 		$revisionArray = array();
	 		$ReportDataId = null;

	 		$data = array(
	 				'id' =>	$newID,
	 				'report_id' => $reportnumbers['Reportnumber']['report_id'],
	 				'order_id' => $reportnumbers['Reportnumber']['order_id'],
	 				'cascade_id' => $reportnumbers['Reportnumber']['cascade_id'],
	 				'testingmethod_id' => $reportnumbers['Reportnumber']['testingmethod_id'],
	 				'testingcomp_id' => $this->Auth->user('testingcomp_id'),
	 				'user_id' => $this->Auth->user('id'),
	 				'master' => $reportnumbers['Reportnumber']['master'],
					'version' => $reportnumbers['Reportnumber']['version'],
					'repair_for' => $id
	 		);

	 		$this->Reportnumber->save($data);

	 		$RepairData = array();

			if(isset($SingleEvaluation) && count($SingleEvaluation) > 0){
				unset($SingleEvaluation['id']);
				unset($SingleEvaluation['created']);
				unset($SingleEvaluation['modified']);
				unset($SingleEvaluation['reportnumber_id']);

				$SingleEvaluation['remark'] = __('Repair of',true) . ': ' . $reportnumbers['Reportnumber']['year'] . '-' . $reportnumbers['Reportnumber']['number'] . ' (' . $SingleEvaluation['description'] . '/' . $SingleEvaluation['position'] . ')';
				$SingleEvaluation['reportnumber_id'] = $newID;
				$SingleEvaluation['result'] = 0;
				$SingleEvaluation['rep_area'] = NULL;
				$SingleEvaluation['error'] = NULL;
				$SingleEvaluation['evaluation_id_mistake'] = $evaluationID;

				$SingleEvaluation['description'] .= ' R';
				$RepairData[$ReportTable['Evaluation']][] = $SingleEvaluation;

			} else {
				foreach($reportnumbers[$ReportEvaluation] as $_key => $_data){

					$InsertData = $_data[$ReportTable['Evaluation']];

					unset($InsertData['id']);
					unset($InsertData['created']);
					unset($InsertData['modified']);
					unset($InsertData['reportnumber_id']);

					$InsertData['remark'] = __('Repair of',true) . ': ' . $reportnumbers['Reportnumber']['year'] . '-' . $reportnumbers['Reportnumber']['number'] . ' (' . $InsertData['description'] . '/' . $InsertData['position'] . ')';
					$InsertData['reportnumber_id'] = $newID;
					$InsertData['result'] = 0;
					$InsertData['rep_area'] = NULL;
					$InsertData['error'] = NULL;
					$InsertData['description'] .= ' R';
					$InsertData['evaluation_id_mistake'] = $_data[$ReportEvaluation]['id'];

					$RepairData[$ReportTable['Evaluation']][] = $InsertData;
				}
			}

			$reportnumbers = $this->Repairtracking->RemoveNoDuplicateFields($reportnumbers);

			foreach($ReportTable as $_key => $_data){

				$this->loadModel($_data);

	 			if($_key == 'Evaluation') continue;

				unset($reportnumbers[$_data]['id']);
				unset($reportnumbers[$_data]['reportnumber_id']);
				unset($reportnumbers[$_data]['created']);
				unset($reportnumbers[$_data]['modified']);

				$reportnumbers[$_data]['reportnumber_id'] = $newID;

				$RepairData[$_data] = $reportnumbers[$_data];
			}

	 		foreach($ReportTable as $_key => $_data) {
				
	 			if($_key == 'Evaluation') continue;
	 			if($_key == 'Archiv') continue;

	 			$this->$_data->create();
	 			$this->$_data->save($RepairData[$_data]);
	 			$ReportDataId .= ' '.$this->$_data->getInsertID();

	 		}

			$this->loadModel($ReportEvaluation);
			$this->loadModel('Repair');

			foreach($RepairData[$ReportEvaluation] as $_key => $_data){

				$this->$ReportEvaluation->create();

				if($this->$ReportEvaluation->save($_data)){
					$InsertRepairStatistik = array();
					$InsertRepairStatistik['topproject_id'] = $projectID;
					$InsertRepairStatistik['cascade_id'] = $cascadeID;
					$InsertRepairStatistik['order_id'] = $orderID;
					$InsertRepairStatistik['report_id'] = $ReportnumberRepairs['Reportnumber']['report_id'];
					$InsertRepairStatistik['testingmethod_id'] = $ReportnumberRepairs['Reportnumber']['testingmethod_id'];
					$InsertRepairStatistik['testingcomp_id'] = $this->Auth->user('testingcomp_id');
					$InsertRepairStatistik['user_id'] = $this->Auth->user('id');
					$InsertRepairStatistik['revision'] = $ReportnumberRepairs['Reportnumber']['revision'];
					$InsertRepairStatistik['errors'] = $ReportnumberRepairs[$ReportEvaluation][$_key][$ReportEvaluation]['error'];
					$InsertRepairStatistik['reportnumber_id'] = $newID;
					$InsertRepairStatistik['evaluation_id'] = $this->$ReportEvaluation->getInsertID();
					$InsertRepairStatistik['reportnumber_id_mistake'] = $ReportnumberRepairs['Reportnumber']['id'];
					$InsertRepairStatistik['evaluation_id_mistake'] = $_data['evaluation_id_mistake'];

					$this->Repair->create();
					$this->Repair->save($InsertRepairStatistik);

				} else {
				// Error
				}
			}

	 		// Die gesammelte IDs der erzeugten Datensätze werden
	 		// wiederum in der Reportnumberstabelle gespeichert
	 		$data = array(
	 				'id' =>	$newID,
	 				'data_ids' => trim($ReportDataId)
	 		);

	 		$this->Reportnumber->save($data);

	 		// Archiv updaten
	 		$this->Data->Archiv($newID,$Verfahren);
			$this->Session->setFlash('Der Reparaturbericht wurde erstellt', 'default', array(), 'good');

	 		$FormName['controller'] = 'reportnumbers';
	 		$FormName['action'] = 'view';
	 		$FormName['terms'] =
	 		$this->request->projectvars['VarsArray'][0].'/'.
	 		$this->request->projectvars['VarsArray'][1].'/'.
	 		$this->request->projectvars['VarsArray'][2].'/'.
	 		$this->request->projectvars['VarsArray'][3].'/'.
	 		$newID;

	 		$this->set('FormName', $FormName);

		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'reportnumbers','action' => 'repairs', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray',$SettingsArray);
		$this->set('reportnumbers',$reportnumbers);
	 }

	 public function repairs() {

	 	if(Configure::read('RepairManager') == false) return;

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evalId = $this->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->request->projectvars['VarsArray'][6];

	 	if($this->request->reportnumberID < 1) return;

		if($this->Autorisierung->IsThisMyReport($id) === false) return;

	 	// Bezeichnungen für Nahtergebnisse (-, e, ne)
	 	$resultCaptionInitial = array(__('not evaluated'), __('accepted'), __('defect'));

		$resultCaption = array(
	 			array(__('unchanged'), __('accepted'), __('defect')),
	 			array(__('not evaluated'), __('unchanged'), __('defect')),
	 			array(__('not evaluated'), __('repaired'), __('unchanged'))
	 	);

	 	$resultIcons = array('test-skip-icon.png', 'test-pass-icon.png', 'test-fail-icon.png');

	 	// Hilfsarray, um festzustellen, wann keine neuen Berichte mehr gefunden werden
	 	// sonst läuft die while-Schleife unten endlos
	 	$old_rep = array();

	 	// erste ID für die Suche nach Reparaturberichten aus URL holen
	 	$reportnumberID = $this->request->reportnumberID;

		$this->Reportnumber->recursive = 1;

		$reportnumber = $this->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id' => $reportnumberID,'Reportnumber.status >' => 0)));

		if(count($reportnumber) == 0){
			$this->Flash->hint(__('Der Prüfbericht muss geschlossen sein, um Reparaturberichte zu erzeugen.',true),array('key' => 'hint'));
			$this->render();
			return;
		}

		$reportnumber = $this->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id' => $reportnumberID,'Reportnumber.result' => 2)));

		if(count($reportnumber) == 0){
			$this->Flash->hint(__('There are no errors in this report',true),array('key' => 'hint'));
			$this->render();
			return;
		}

	 	$reportnumber = $this->Data->RevisionCheckTime($reportnumber);
	 	$reportnumber = $this->Data->GetReportData($reportnumber,array('Generally','Specific','Evaluation'));
	 	$reportnumber = $this->Repairtracking->GetRepairReport($reportnumber);

		$arrayData = $this->Xml->DatafromXml($this->request->verfahren,'file',$this->request->Verfahren);

	 	$SettingsArray = array();

		if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) unset($SettingsArray['addrepairlink']);

	 	$this->set('arrayData', $arrayData);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('reportnumber', $reportnumber);

		$this->Autorisierung->IsThisMyReport($id);

	 }

	 public function mass_report_action() {

		$this->layout = 'modal';

		// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

		if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Autorisierung->IsThisMyReport($id);

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumber = $this->Reportnumber->find('first', $options);

	 	$reportnumber = $this->Data->RevisionCheckTime($reportnumber);

	 	$Verfahren = ucfirst($reportnumber['Testingmethod']['value']);
	 	$verfahren = $reportnumber['Testingmethod']['value'];
	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	$this->request->verfahren = $Verfahren;

		$this->set('reportnumber',$reportnumber);

		$SignLevel  = 2;

		if(Configure::check('MassActionSignLevel')) {
		 $SignLevel = Configure::read('MassActionSignLevel');
		}

		if(isset($this->request->data['signature'])) $this->Data->SigntransportMassAction(array(),1,$SignLevel );

		if(isset($this->request->data['signature']) && !isset($this->request->data['image'])){
			$this->autoRender = false;
	 		return;
		}

		if(!isset($this->request->data['Reportnumber']) || !isset($this->request->data['Reportnumber']['MassSelect']) || !isset($this->request->data['Reportnumber']['MassSelectetIDs'])){
			$this->Flash->error('No data send.', array('key' => 'error'));
			$this->render();
	 		return;
		}

		$reportnumbers = $this->Data->AskMassReportAction($reportnumber);
    	$SignLevel =2;
	
		if(Configure::check('MassActionSignLevel')) {
		 $SignLevel = Configure::read('MassActionSignLevel');
		}

	 	if(isset($this->request->data['image'])) $this->Data->SigntransportMassAction($reportnumbers,2,$SignLevel);

		$this->set('reportnumbers',$reportnumbers);

	 }

	 public function massActions($id = null) {

		$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
		$evalId = $this->request->projectvars['VarsArray'][5];
		$weldedit = $this->request->projectvars['VarsArray'][6];

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Autorisierung->IsThisMyReport($id);

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumber = $this->Reportnumber->find('first', $options);

	 	$reportnumber = $this->Data->RevisionCheckTime($reportnumber);

	 	$Verfahren = ucfirst($reportnumber['Testingmethod']['value']);
	 	$verfahren = $reportnumber['Testingmethod']['value'];
	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
	 	$this->request->verfahren = $Verfahren;

	 	$x = 0;
	 	$actio_array = array();
		$attribut_disabled = false;
		$message = null;

		$noActionRequired = array('weldlabel');

	 	// Wenn der Prüfbericht geschlossen ist
	 	if($reportnumber['Reportnumber']['status'] > 0){

			$attribut_disabled = true;
	 		$message = __('The value could not be saved. The report is closed!');

			foreach ($noActionRequired as $key => $value) {
				if ($value == $this->request->data['Reportnumber']['MassSelect']) {
					$attribut_disabled = false;
					$message = null;
					break;
				}
			}
	 	}

		if(isset($reportnumber['Reportnumber']['revision_write'])){

			$this->Flash->warning(__('The report is under revision.'), array('key' => 'warning'));

		}

	 	// Wenn der Prüfbericht gelöscht ist
	 	if($reportnumber['Reportnumber']['delete'] > 0){
			$attribut_disabled = true;
	 		$message = __('The value could not be saved. The report is marked as deleted!');
	 	}

	 	// Wenn der Prüfbericht deaktiviert ist
	 	if($reportnumber['Reportnumber']['deactive'] > 0){
			$attribut_disabled = true;
	 		$message = __('The value could not be saved. The report is marked as deactivate!');
	 	}

		if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1){
			$attribut_disabled = false;
		}

	 	if($attribut_disabled == true){
	 		$this->set('message_class', 'error');
//	 		$this->set('message',$message);
			$this->Flash->error($message, array('key' => 'error'));
	 		$this->set('evaluation',array());
	 		$this->set('reportnumber',$reportnumber);
	 		$this->render();
	 		return;
		}

	 	foreach($this->request->data['Reportnumber'] as $_key => $_welds){
	 		if(preg_match("/^check/", $_key) && $_welds > 0) {
	 			$this->Sicherheit->Numeric($_welds);
	 			$actio_array[$_welds] = $_welds;
	 			$x++;
	 		}
	 	}

		if(
			$x == 0 &&
			(!isset($this->request->data['Reportnumber']['MassSelect']) || array_search($this->request->data['Reportnumber']['MassSelect'], $noActionRequired) === false) &&
			$attribut_disabled === true
			) {
	 			$this->set('message_class', 'hint');
	 			$this->set('message', __('Es wurden keine Prüfbereiche ausgewählt.'));
	 			$this->set('evaluation', null);
	 	}
	 	else
		{

	 		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 		$ReportSetting = 'Report'.$Verfahren.'Setting';
	 		$ReportArchiv = 'Report'.$Verfahren.'Archiv';

	 		$this->loadModel($ReportEvaluation);

	 		$optionsEvaluation = array('conditions' => array(
	 				$ReportEvaluation.'.reportnumber_id' => $id,
	 				$ReportEvaluation.'.id' => $actio_array,
	 				$ReportEvaluation.'.deleted' => 0,
	 		)
	 		);
	 		$optionsSettings = array('conditions' => array(
	 				$ReportSetting.'.reportnumber_id' => $id,
	 		)
	 		);

	 		$evaluation = $this->$ReportEvaluation->find('all', $optionsEvaluation);

			$data_for_revision = array();

			// diese Array wird der Revison übergeben, wenn nötig
			$TransportArray['model'] = $ReportEvaluation;
			$TransportArray['row'] = null;
			$TransportArray['last_value'] = implode(',',$actio_array);
			$TransportArray['this_value'] = null;
			$TransportArray['last_id_for_radio'] = 0;
			$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');

			switch($this->request->data['Reportnumber']['MassSelect']) {

				case ('template'):

					if ($this->request->data['Reportnumber']['okay'] == 1) {

						$TemplateUrl = Router::url(array_merge(array('controller' => 'templates', 'action' => 'get'),$this->request->projectvars['VarsArray']));

						$this->set('reportnumber',$reportnumber);
						$this->set('TemplateUrl',$TemplateUrl);
						$this->set('send_template',true);

						return;
	
					}

					$this->set('message_class', 'hint');
					$this->set('message_button', __('Apply template',true));
					$this->Flash->warning(__('Sollen die aufgeführten Prüfbereiche durch eine Vorlage überschrieben werden?'), array('key' => 'warning'));

				break;


	 			case ('duplicatevalution'):

	 				if($this->request->data['Reportnumber']['okay'] == 1){
	 					// XML-Settings einlesen
	 					$this->loadModel($ReportSetting);
	 					$setting = $this->$ReportSetting->find('first',$optionsSettings);
	 					$settings = $this->Xml->XmltoArray($verfahren,'file',ucfirst($verfahren));

						if($reportnumber['Reportnumber']['revision_progress'] > 0){
							$SaveRevisionIds = $this->Data->SaveRevision($reportnumber,$TransportArray,'duplicatevalution/weld',$massaction = 'duplicatevalution');
						}

	 					$dataDubli = array();
	 					$x = 0;

	 					foreach($evaluation as $_key => $_evaluation){
	 						foreach ($settings->$ReportEvaluation->children() as  $_key => $_settings){
	 							if(isset($_settings->dublicate) && $_settings->dublicate == 'yes' && $_settings->model == $ReportEvaluation){

	 								$dataDubli[$x][$ReportEvaluation][trim($_settings->key)] = $_evaluation[$ReportEvaluation][trim($_settings->key)];
	 							}
	 						}

							if($evalId > 0 && $weldedit == 0) $dataDubli[$x][$ReportEvaluation]['position'] .= ' ' . __('Copy',true);
							else $dataDubli[$x][$ReportEvaluation]['description'] .= ' ' . __('Copy',true);

	 						$dataDubli[$x][$ReportEvaluation]['reportnumber_id'] = $id;
	 						unset($dataDubli[$x][$ReportEvaluation]['id']);

	 						$x++;
	 					}

	 					$this->$ReportEvaluation->saveAll($dataDubli);

	 					// Die letzte Id für die Weiterleitung holen
	 					$LastID = explode(',',$this->$ReportEvaluation->getInsertID($x+1));
						$LastOnlyID = array();
	 					foreach($dataDubli as $key => $data) {
	 						$dataDubli[$key][$ReportEvaluation]['id'] = $LastID[$key];
							$LastOnlyID[] = $LastID[$key];
	 					}

	 					$LastID = $this->$ReportEvaluation->getInsertID();

						$dataDubli['massAction'] = $this->request->data['Reportnumber']['MassSelect'];
						$TransportArray['table_id'] = $LastID;

						if($reportnumber['Reportnumber']['revision_write'] > 0){
							$this->Data->UpdateRevision($SaveRevisionIds,$LastOnlyID,'duplicatevalution');
						}

	 					$this->Autorisierung->Logger($id,$dataDubli);
//	 					$this->Autorisierung->Logger($id,array_map(function($elem) use($ReportEvaluation) { return $elem[$ReportEvaluation]; }, $dataDubli));
//	 					$this->Session->setFlash(__('The Area has been duplicated', true));
	 					$this->Data->Archiv($id,$Verfahren);

	 					$FormName = array();
	 					$FormName['controller'] = 'reportnumbers';
	 					$FormName['action'] = 'edit';
	 					$FormName['terms'] =
	 					$this->request->projectvars['VarsArray'][0] . '/' .
	 					$this->request->projectvars['VarsArray'][1] . '/' .
	 					$this->request->projectvars['VarsArray'][2] . '/' .
	 					$this->request->projectvars['VarsArray'][3] . '/' .
	 					$this->request->projectvars['VarsArray'][4] . '/' .
	 					$this->request->projectvars['VarsArray'][5];

//	 					$this->Session->setFlash(__('The welds') . ' ' . __('has been duplicate.',true));
	 					$this->set('FormName', $FormName);
	 					$this->set('saveOK', 1);

	 				}

	 				$this->set('message_class', 'hint');
	 				$this->set('message_button', __('Duplicate',true));
//	 				$this->set('message', __('Sollen die aufgeführten Prüfbereiche dupliziert werden?'));
					$this->Flash->hint(__('Sollen die aufgeführten Prüfbereiche dupliziert werden?'), array('key' => 'hint'));

	 				break;

	 			case ('deleteevalution'):

	 				if($this->request->data['Reportnumber']['okay'] == 1){
					
						if($reportnumber['Reportnumber']['revision_write'] > 0){
							$SaveRevisionIds = $this->Data->SaveRevision($reportnumber,$TransportArray,'deleteevalution/weld',$massaction = 'deleteevalution','Das ist ein Test');
						}

						$EvaluationOptions = array($ReportEvaluation.'.reportnumber_id' => $id,$ReportEvaluation.'.id' => $actio_array);

						$LastRecord = $this->$ReportEvaluation->find('all',array('conditions' => $EvaluationOptions));

	 					if($this->$ReportEvaluation->deleteAll($EvaluationOptions , false)){

							$this->Search->DeleteLinkingEvaluationEntries($reportnumber,$LastRecord);

	 						$this->Data->DevelopmentReset($orderID,$actio_array);

	 						$logger = array();

	 						foreach($evaluation as $_evaluation){
	 							if(isset($_evaluation[$ReportEvaluation]['position'])) $logger[$_evaluation[$ReportEvaluation]['id']] = $_evaluation[$ReportEvaluation]['description'] . '/' . $_evaluation[$ReportEvaluation]['position'] . ' ' . __('delete',true);
								else $logger[$_evaluation[$ReportEvaluation]['id']] = $_evaluation[$ReportEvaluation]['description'] . ' ' . __('delete',true);

	 						}

	 						$this->Data->Archiv($id,$Verfahren);
							$logger['massAction'] = $this->request->data['Reportnumber']['MassSelect'];
	 						$this->Autorisierung->Logger($id,$logger);

	 						$FormName = array();
	 						$FormName['controller'] = 'reportnumbers';
	 						$FormName['action'] = 'edit';
	 						$FormName['terms'] =
	 						$this->request->projectvars['VarsArray'][0] . '/' .
	 						$this->request->projectvars['VarsArray'][1] . '/' .
	 						$this->request->projectvars['VarsArray'][2] . '/' .
	 						$this->request->projectvars['VarsArray'][3] . '/' .
	 						$this->request->projectvars['VarsArray'][4] . '/' .
	 						$this->request->projectvars['VarsArray'][5];

	 						$this->set('FormName', $FormName);
	 						$this->set('saveOK', 1);

	 					}
	 					else {
	 						$this->set('FormName', $FormName);
	 						$this->set('saveOK', 1);
	 					}
	 				}

	 				$this->set('message_class', 'error');
	 				$this->set('message_button', __('Delete',true));
//	 				$this->set('message', __('Sollen die aufgeführten Prüfbereiche gelöscht werden?'));
					$this->Flash->error(__('Sollen die aufgeführten Prüfbereiche gelöscht werden?'), array('key' => 'error'));
	 				break;

	 			case ('result_e'):

	 				if($this->request->data['Reportnumber']['okay'] == 1){

						if($reportnumber['Reportnumber']['revision_write'] > 0){
							$SaveRevisionIds = $this->Data->SaveRevision($reportnumber,$TransportArray,'editevaluation',$massaction = 'result',$value = 1);

						}

	 					if($this->$ReportEvaluation->updateAll(
	 							array($ReportEvaluation.'.result' => 1),
	 							array(
	 									$ReportEvaluation.'.reportnumber_id' => $id,
	 									$ReportEvaluation.'.id' => $actio_array,
	 							)
	 							)){
	 								$this->Data->DevelopmentReset($orderID,$actio_array);

	 								$logger = array();

									$evalne = $this->$ReportEvaluation->find('all',array('conditions' => array(
										$ReportEvaluation.'.reportnumber_id'=>$id,
										$ReportEvaluation.'.deleted'=>0,
										$ReportEvaluation.'.result'=>2
									)));

									if(isset($evalne) && !empty($evalne)){
										$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 2);
									} else {
										$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 1);
									}

									$this->Reportnumber->save($reportdata);

	 								foreach($evaluation as $_evaluation){
	 									$logger[$_evaluation[$ReportEvaluation]['id']] = $_evaluation[$ReportEvaluation]['description'] . '/' . $_evaluation[$ReportEvaluation]['position'] . ' ' . __('result',true) . ' e';
	 								}

									$logger['massAction'] = $this->request->data['Reportnumber']['MassSelect'];

	 								$this->Data->Archiv($id,$Verfahren);
	 								$this->Autorisierung->Logger($id,$logger);

	 								$FormName = array();
	 								$FormName['controller'] = 'reportnumbers';
	 								$FormName['action'] = 'edit';
	 								$FormName['terms'] =
	 								$this->request->projectvars['VarsArray'][0] . '/' .
	 								$this->request->projectvars['VarsArray'][1] . '/' .
	 								$this->request->projectvars['VarsArray'][2] . '/' .
	 								$this->request->projectvars['VarsArray'][3] . '/' .
	 								$this->request->projectvars['VarsArray'][4] . '/' .
	 								$this->request->projectvars['VarsArray'][5];

//	 								$this->Session->setFlash(__('The welds') . ' ' . __('has been updated.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 							else {
//	 								$this->Session->setFlash(__('The welds') . ' ' . __('was not updated.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 				}

	 				$this->set('message_class', 'hint');
	 				$this->set('message_button', __('Mit erfüllt bewerten',true));
//	 				$this->set('message', __('Sollen die aufgeführten Prüfbereiche mit erfüllt bewertet werden?'));
					$this->Flash->hint(__('Sollen die aufgeführten Prüfbereiche mit erfüllt bewertet werden?'), array('key' => 'hint'));
	 				break;

	 			case ('result_ne'):

	 				if($this->request->data['Reportnumber']['okay'] == 1){

						if($reportnumber['Reportnumber']['revision_write'] > 0){
							$SaveRevisionIds = $this->Data->SaveRevision($reportnumber,$TransportArray,'editevaluation',$massaction = 'result',$value = 2);

						}

	 					if($this->$ReportEvaluation->updateAll(
	 							array($ReportEvaluation.'.result' => 2),
	 							array(
	 									$ReportEvaluation.'.reportnumber_id' => $id,
	 									$ReportEvaluation.'.id' => $actio_array,
	 							)
	 							)){
	 								$this->Data->DevelopmentReset($orderID,$actio_array);

	 								$logger = array();

									$evalne = $this->$ReportEvaluation->find('all',array('conditions' => array(
										$ReportEvaluation.'.reportnumber_id'=>$id,
										$ReportEvaluation.'.deleted'=>0,
										$ReportEvaluation.'.result'=>2
									)));

									if(isset($evalne) && !empty($evalne)){
										$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 2);
									} else {
										$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 1);
									}

									$this->Reportnumber->save($reportdata);

	 								foreach($evaluation as $_evaluation){
	 									$logger[$_evaluation[$ReportEvaluation]['id']] = $_evaluation[$ReportEvaluation]['description'] . '/' . $_evaluation[$ReportEvaluation]['position'] . ' ' . __('result',true) . ' ne';
	 								}

									$logger['massAction'] = $this->request->data['Reportnumber']['MassSelect'];

	 								$this->Data->Archiv($id,$Verfahren);
	 								$this->Autorisierung->Logger($id,$logger);

	 								$FormName = array();
	 								$FormName['controller'] = 'reportnumbers';
	 								$FormName['action'] = 'edit';
	 								$FormName['terms'] =
	 								$this->request->projectvars['VarsArray'][0] . '/' .
	 								$this->request->projectvars['VarsArray'][1] . '/' .
	 								$this->request->projectvars['VarsArray'][2] . '/' .
	 								$this->request->projectvars['VarsArray'][3] . '/' .
	 								$this->request->projectvars['VarsArray'][4] . '/' .
	 								$this->request->projectvars['VarsArray'][5];

	// 								$this->Session->setFlash(__('The welds') . ' ' . __('has been updated.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 							else {
//	 								$this->Session->setFlash(__('The welds') . ' ' . __('was not updated.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 				}

	 				$this->set('message_class', 'hint');
	 				$this->set('message_button', __('Mit nicht erfüllt bewerten',true));
					$this->Flash->hint(__('Sollen die aufgeführten Prüfbereiche mit nicht erfüllt bewertet werden?'), array('key' => 'hint'));
	 				break;

	 			case ('result_no'):

	 				if($this->request->data['Reportnumber']['okay'] == 1){

						if($reportnumber['Reportnumber']['revision_write'] > 0){
							$SaveRevisionIds = $this->Data->SaveRevision($reportnumber,$TransportArray,'editevaluation',$massaction = 'result',$value = 0);
						}

	 					if($this->$ReportEvaluation->updateAll(
	 							array($ReportEvaluation.'.result' => 0),
	 							array(
	 									$ReportEvaluation.'.reportnumber_id' => $id,
	 									$ReportEvaluation.'.id' => $actio_array,
	 							)
	 							)){
	 								$this->Data->DevelopmentReset($orderID,$actio_array);

	 								$logger = array();

									$evalne = $this->$ReportEvaluation->find('all',array('conditions' => array(
										$ReportEvaluation.'.reportnumber_id'=>$id,
										$ReportEvaluation.'.deleted'=>0,
										$ReportEvaluation.'.result'=>2
									)));

									if(isset($evalne) && !empty($evalne)){
										$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 2);
									} else {
										$reportdata = array('id' => $id,'modified_user_id' => $this->Auth->user('id'),'result' => 1);
									}

									$this->Reportnumber->save($reportdata);

	 								foreach($evaluation as $_evaluation){
	 									$logger[$_evaluation[$ReportEvaluation]['id']] = $_evaluation[$ReportEvaluation]['description'] . '/' . $_evaluation[$ReportEvaluation]['position'] . ' ' . __('result',true) . ' -';
	 								}

									$logger['massAction'] = $this->request->data['Reportnumber']['MassSelect'];

	 								$this->Data->Archiv($id,$Verfahren);
	 								$this->Autorisierung->Logger($id,$logger);

	 								$FormName = array();
	 								$FormName['controller'] = 'reportnumbers';
	 								$FormName['action'] = 'edit';
	 								$FormName['terms'] =
	 								$this->request->projectvars['VarsArray'][0] . '/' .
	 								$this->request->projectvars['VarsArray'][1] . '/' .
	 								$this->request->projectvars['VarsArray'][2] . '/' .
	 								$this->request->projectvars['VarsArray'][3] . '/' .
	 								$this->request->projectvars['VarsArray'][4] . '/' .
	 								$this->request->projectvars['VarsArray'][5];

	// 								$this->Session->setFlash(__('The welds') . ' ' . __('has been updated.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 							else {
	 //								$this->Session->setFlash(__('The welds') . ' ' . __('was not updated.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 				}

	 				$this->set('message_class', 'hint');
	 				$this->set('message_button', __('Bewertungen entfernen',true));
					$this->Flash->hint(__('Sollen die Bewertungen der aufgeführten Prüfbereiche entfernt werden?'), array('key' => 'hint'));
	 				break;

	 			case ('errors_remove'):

	 				if($this->request->data['Reportnumber']['okay'] == 1){

						if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] > 0){
							$SaveRevisionIds = $this->Data->SaveRevision($reportnumber,$TransportArray,'errors_remove/weld',$massaction = 'error');
						}

	 					if($this->$ReportEvaluation->updateAll(
	 							array($ReportEvaluation.'.error' => null),
	 							array(
	 									$ReportEvaluation.'.reportnumber_id' => $id,
	 									$ReportEvaluation.'.id' => $actio_array,
	 							)
	 							)){

	 								$logger = array();

	 								foreach($evaluation as $_evaluation){
	 									$logger[$_evaluation[$ReportEvaluation]['id']] = $_evaluation[$ReportEvaluation]['description'] . '/' . $_evaluation[$ReportEvaluation]['position'] . ' ' . __('result',true) . ' -';
	 								}

									$logger['massAction'] = $this->request->data['Reportnumber']['MassSelect'];

	 								$this->Data->Archiv($id,$Verfahren);
	 								$this->Autorisierung->Logger($id,$logger);

	 								$FormName = array();
	 								$FormName['controller'] = 'reportnumbers';
	 								$FormName['action'] = 'edit';
	 								$FormName['terms'] =
	 								$this->request->projectvars['VarsArray'][0] . '/' .
	 								$this->request->projectvars['VarsArray'][1] . '/' .
	 								$this->request->projectvars['VarsArray'][2] . '/' .
	 								$this->request->projectvars['VarsArray'][3] . '/' .
	 								$this->request->projectvars['VarsArray'][4] . '/' .
	 								$this->request->projectvars['VarsArray'][5];

	 //								$this->Session->setFlash(__('The error codes') . ' ' . __('has been removed.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 							else {
	 //								$this->Session->setFlash(__('The error code') . ' ' . __('was not removed.',true));
	 								$this->set('FormName', $FormName);
	 								$this->set('saveOK', 1);
	 							}
	 				}

	 				$this->set('message_class', 'hint');
	 				$this->set('message_button', __('Fehler entfernen',true));
					$this->Flash->hint(__('Sollen die Fehler-Codes der aufgeführten Prüfbereiche entfernt werden?'), array('key' => 'hint'));
	 				break;

	 			case 'weldlabel':

	 				$this->set('message_class', 'hint');
	 				$this->set('message_button', __('Print weld label',true));
	 				$this->set('message', null);
	 				$this->set('evaluation', $evaluation);

	 				if(empty($evaluation)) {

						$settings = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);
						$ReportPdf = 'Report'.$Verfahren.'Pdf';
						$ReportGenerally = 'Report'.$Verfahren.'Generally';

						unset($optionsEvaluation['conditions'][$ReportEvaluation.'.id']);
						$evaluation = $this->$ReportEvaluation->find('all', $optionsEvaluation);
						$description = null;
						$x = 0;
						foreach($evaluation as $_key => $_evaluation){
							if($x > 0) $description .= '; ';
							$description .= $_evaluation[$ReportEvaluation]['description'];
							$x++;
						}

						if($description != null){
							$this->request->data[$ReportEvaluation]['description'] = $description;
						}


						if(isset($settings['settings']->$ReportPdf->settings->QM_WELDLABEL->format->description)){
							$settings['settings']->$ReportPdf->settings->QM_WELDLABEL->format->description->value = $description;
						}

						$this->loadModel($ReportArchiv);
						$Archiv = $this->$ReportArchiv->find('first',array('conditions'=>array($ReportArchiv.'.reportnumber_id'=>$id)));
				 		$XmlData = utf8_decode($Archiv[$ReportArchiv]['data']);
				 		$XmlData = Xml::build(html_entity_decode($XmlData));

						$data = array();

						foreach($settings['settings']->$ReportPdf->settings->QM_WELDLABEL->items as $_items){

							if(trim($_items->model) != $ReportGenerally) continue;

							$field = trim($_items->item);
							$data[trim($_items->item)] = trim($XmlData->$ReportGenerally->$field);

							unset($field);
						}

						$this->request->data[$ReportGenerally] = $data;
						$this->request->data[$ReportEvaluation]['reportnumber_id'] = $this->Pdf->ConstructReportName($reportnumber,2);

						$this->Flash->hint(__('No welds selected for printing. Put your data for a universal label in the form below.'), array('key' => 'hint'));
	 					$this->set('settings', $settings);
	 					$this->set('ReportPdf',$ReportPdf);
	 					$this->set('ReportGenerally',$ReportGenerally);
	 					$this->set('ReportEvaluation',$ReportEvaluation);

	 					$this->render('weldlabel');
	 				}

	 				break;
	 		}

	 		if(isset($this->request->data['Reportnumber']['okay']) && $this->request->data['Reportnumber']['okay'] == 1){
	 			if($reportnumber['Reportnumber']['revision_progress'] > 0) {
					$aktion_for_revision = 'massActions';
//					$this->Data->SaveRevision($reportnumber,$TransportArray,$aktion_for_revision);
				}
			}

	 		$this->set('evaluation', $evaluation);
	 	}

	 	$this->set('evaluationoutput', $arrayData['evaluationoutput']);
	 	$this->set('result', array('' => '-',0 => '-',1 => 'e',2 => 'ne'));
	 	$this->set('reportnumber', $reportnumber);
	 }

	 public function last_ten() {

	 	$this->layout = 'modal';

		$this->Reportnumber->recursive = -1;
		$this->Topproject->recursive = -1;
		$this->Reportnumber->Testingmethod->recursive = -1;

		$Reportnumbers = array();

		$Options = array(
			'fields' => array('DISTINCT testingmethod_id'),
			'conditions' => array(
					'Reportnumber.modified_user_id' => $this->Auth->user('id'),
					'Reportnumber.delete' => 0,
					'Reportnumber.parent_id' => 0,
					'Reportnumber.deactive' => 0,
				)
			);

		$Methods = $this->Reportnumber->find('all',$Options);

		$Methods = Hash::extract($Methods, '{n}.Reportnumber.testingmethod_id');
		$MethodOrder = array();

		foreach ($Methods as $key => $value) {

			$Options = array(
				'order' => array('id DESC'),
	//			'fields' => array('id','modified','testingmethod_id'),
				'conditions' => array(
						'Reportnumber.testingmethod_id' => $value,
						'Reportnumber.modified_user_id' => $this->Auth->user('id'),
						'Reportnumber.delete' => 0,
						'Reportnumber.parent_id' => 0,
						'Reportnumber.deactive' => 0,
					)
				);

				$Method = $this->Reportnumber->find('first',$Options);

				$date = new DateTime($Method['Reportnumber']['modified']);
				$MethodOrder[$date->getTimestamp()] = $Method;

		}

		krsort($MethodOrder);

		foreach ($MethodOrder as $key => $value) {

			$Options = array(
				'order' => array('modified DESC'),
				'limit' => 10,
				'conditions' => array(
						'Reportnumber.modified_user_id' => $this->Auth->user('id'),
						'Reportnumber.testingmethod_id' => $value['Reportnumber']['testingmethod_id'],
						'Reportnumber.delete' => 0,
						'Reportnumber.parent_id' => 0,
						'Reportnumber.deactive' => 0,
					)
				);

				$Reports = $this->Reportnumber->find('list',$Options);

				if(count($Reports) == 0) continue;

				$Testingmethods = $this->Reportnumber->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $value['Reportnumber']['testingmethod_id'])));

				if(count($Testingmethods) == 0) continue;

				$Xml = $this->Xml->DatafromXml($Testingmethods['Testingmethod']['value'],'file', ucfirst($Testingmethods['Testingmethod']['value']));
//				$Xml = $this->Xml->DatafromXmlForReport($Testingmethods['Testingmethod']['value'],$_testingreports);

				$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['testingmetohd_id'] = $value['Reportnumber']['testingmethod_id'];
				$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['Testingmethod'] = $Testingmethods['Testingmethod'];

				$Model = 'Report'.ucfirst($Testingmethods['Testingmethod']['value']).'Generally';

				if(!ClassRegistry::isKeySet($Model)) $this->loadModel($Model);

				foreach ($Reports as $_key => $_value) {

					$report = $this->{$Model}->findByReportnumberId($_value);
					$reportnumber = $this->Reportnumber->findById($_value);

					if(count($reportnumber) == 0) continue;
					if(count($report) == 0) continue;

					$topproject = $this->Topproject->findById($reportnumber['Reportnumber']['topproject_id']);

					if(count($topproject) == 0) continue;

					$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['model'] = $Model;
					$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['reportnumber_id'][$_value] = $reportnumber;
					$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['Report'][$_value] = $report[$Model];
					$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['Topproject'][$_value] = $topproject['Topproject'];

				}

				$Reportnumbers[$value['Reportnumber']['testingmethod_id']]['Xml'] = $Xml['settings'];

		}

		$this->set('reportnumbers',$Reportnumbers);

		if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $this->render('last_ten_landingpage','blank');

	 }

	 public function testingAreas($id = null) {

	 	$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

		if(isset($this->request->data['layout'])) {
			switch($this->request->data['layout']){
				case 1:
				$this->layout = 'blank';
				$this->view = 'testing_area_tooltip';
				break;
				case 2:
				$this->layout = 'modal';
				$this->view = 'testing_area_result';
				$this->set('result_url',$this->request->data['result_url']);
				break;
			}
		}

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

/* Florian Zumpe 06.09.2017 --- leere Berichtsmappen öffnen, falls NE-Berichte vorhanden sind */
	 	// Überprüfung nur, falls die globale Anzeige von NE-Berichten abgeschalten ist
	 	if(!Configure::check('ShowNeGlobally') || !Configure::read('ShowNeGlobally')) {
	 		$this->Autorisierung->IsThisMyReport($id);
	 	}
/* Florian Zumpe 06.09.2017 --- leere Berichtsmappen öffnen, falls NE-Berichte vorhanden sind */

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);

	 	$reportnumbers = $this->Data->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));

	 	$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren ;

	 	$ReportGenerally = $this->request->tablenames[0];
	 	$ReportSpecific = $this->request->tablenames[1];
	 	$ReportEvaluation = $this->request->tablenames[2];
	 	$ReportArchiv = $this->request->tablenames[3];
	 	$ReportSettings = $this->request->tablenames[4];
	 	$ReportPdf = $this->request->tablenames[5];

		$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	if(Configure::check('RepairManager') && Configure::read('RepairManager') == true){
		 	$reportnumbers = $this->Repairtracking->GetRepairReport($reportnumbers);
		 	$reportnumbers = $this->Repairtracking->GetRepairStatus($reportnumbers,$ReportEvaluation);
		 	$reportnumbers = $this->Repairtracking->MapRepairs($reportnumbers,$ReportEvaluation);
	 	}

	 	$this->loadModel($ReportArchiv);
		$optionsArchiv = array('conditions' => array($ReportArchiv.'.reportnumber_id' => $id));
	 	$Archiv = $this->$ReportArchiv->find('first', $optionsArchiv);
	 	$arrayArchiv = $this->Xml->DatafromXml(($Archiv[$ReportArchiv]['data']),'string',$Verfahren);

	 	// XML-Objekt in Array umwandeln
	 	$Archiv = array();
	 	// Die Bewertungen werden in ein extra Array gepackt
	 	$Evaluation = array();
	 	$EvoCount = 0;
	 	foreach($arrayArchiv['settings']->$ReportEvaluation as $_key =>$_ReportEvaluation){
	 		foreach($_ReportEvaluation as $__key => $__ReportEvaluation){
	 			$Evaluation[trim($_key)][trim($EvoCount)][$ReportEvaluation][trim($__key)] = trim($__ReportEvaluation);
	 		}
	 		$EvoCount++;
	 	}

	 	// Der Status muss nachgetragen werden, da der nicht automatisch in das Archiv aufgenommen wird
	 	$Archiv['Reportnumber']['id'] = $reportnumbers['Reportnumber']['id'];
	 	$Archiv['Reportnumber']['topproject_id'] = $reportnumbers['Reportnumber']['topproject_id'];
	 	$Archiv['Reportnumber']['cascade_id'] = $reportnumbers['Reportnumber']['cascade_id'];
	 	$Archiv['Reportnumber']['order_id'] = $reportnumbers['Reportnumber']['order_id'];
	 	$Archiv['Reportnumber']['report_id'] = $reportnumbers['Reportnumber']['report_id'];
	 	$Archiv['Reportnumber']['testingmethod_id'] = $reportnumbers['Reportnumber']['testingmethod_id'];
	 	$Archiv['Reportnumber']['status'] = $reportnumbers['Reportnumber']['status'];

	 	$this->request->data = $reportnumbers;

	 	$welds = @$this->Data->WeldSorting($Evaluation[$ReportEvaluation]);

	 	// Wenn in diesem Auftrag ein Progress dargestellt werdn soll
	 	if(Configure::read('DevelopmentsEnabled') == true){
	 		$this->loadModel('Development');
	 		$this->loadModel('Order');
	 		$order = $this->Order->find('first',array('conditions' => array('Order.id' => $orderID)));

	 		if(isset($order['Development']) && count($order['Development']) > 0){

	 			foreach($welds as $_key => $_reportnumbers){
	 				foreach($_reportnumbers['weld'] as $__key => $_welds){
	 					$development = $this->Development->DevelopmentData->find('first',array('conditions' => array('DevelopmentData.evaluation_id 	' => $_welds['id'])));

	 					if(count($development) > 0){
	 						$welds[$_key]['development'] = $development['DevelopmentData']['result'];
	 						break;
	 					}
	 					else {
	 						$welds[$_key]['development'] = 0;
	 					}
	 				}
	 			}
	 		}
	 	}

		$SettingsArray = array();

	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('ReportEvaluation', 'Report' . $Verfahren . 'Evaluation');
	 	$this->set('verfahren', $verfahren);
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('evaluationoutput', $arrayData['evaluationoutput']);
	 	$this->set('welds', $welds);
	 }

	 public function move() {

		 // Wenn die Prüfberichtsmappe gesperrt ist
		 if($this->writeprotection) {
			 $this->set('error', __('Report is writeprotected - changes will not be saved.'));
			 $this->render('/closemodal','modal');
			 return;
		 }

		 if(isset($this->request->data['json_true']) && $this->request->data['json_true'] == 1) $json_true = 1;
		 else $json_true = 0;

		 if($json_true == 1) $this->layout = 'blank';
		 else $this->layout = 'modal';

		 // Die IDs des Projektes und des Auftrages werden getestet
		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];

		 $data['projectID'] = $projectID;
		 $data['cascadeID'] = $cascadeID;
		 $data['orderID'] = $orderID;
		 $data['reportID'] = $reportID;
		 $data['reportID'] = $reportID;
		 $data['id'] = $id;

		 $data['CascadeTree']['array'] = $this->Navigation->CascadeGetBreads($cascadeID);
//		 $data['CascadeTree'] = $this->MoveData->NatSortAndStringAndUrl($data['CascadeTree']);
		 $data['CascadeTree'] = $this->MoveData->NatSortAndStringAndUrl($data['CascadeTree']);
//		 pr($data['CascadeTree']);

		 $savearray = array('id' => $id);
		 $reports = array();

		 if (!$this->Reportnumber->exists($id)) return;

		 $this->loadmodel('Cascade');
		 $this->loadmodel('Order');
		 $this->loadmodel('ReportsTopprojects');
		 $this->loadmodel('Report');

		 $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
		 $this->Reportnumber->recursive = 0;
		 $data['Reportnumber'] = $this->Reportnumber->find('first', $options);

		 $this->Data->DeleteTest($data['Reportnumber']);

		 $this->Reportnumber->Topproject->recursive = -1;

		 $data = $this->MoveData->CurrentCascadeBreadcrump($data);
		 $data = $this->MoveData->CurrentCascade($data);
		 $data = $this->MoveData->CurrentOrder($data);
		 $data = $this->MoveData->CurrentHiddenOrder($data);
		 $data = $this->MoveData->CurrentReports($data);
		 $data = $this->MoveData->CurrentReportsNoOrders($data);
		 $data = $this->MoveData->MoveReport($data);
		 $data = $this->MoveData->MoveSigns($data);
		 $data = $this->MoveData->MoveFiles($data);

		 if($json_true == 1){

			$this->set('response', json_encode($data));
			$this->render('json');
			return;

		} else {
			$Mes = __('From',true) . ' ' . $data['CascadeTree']['string'] . ' > ' . $data['Reportnumber']['Report']['name'] . ' > ' . __('Report',true) . ' ' . $data['Reportnumber']['Reportnumber']['year'] . '-' . $data['Reportnumber']['Reportnumber']['number'];
			$this->Flash->warning($Mes, array('key' => 'warning'));

			$data = $this->MoveData->CheckReportnumberHandling($data);

			$this->request->data = $data;

		}
	 }

	 public function addchild($id = null, $child = null) {
	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	if (!$this->Reportnumber->exists($child)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Autorisierung->IsThisMyReport($id);
	 	$this->Reportnumber->updateAll(
	 			array('Reportnumber.parent_id'=>$id),
	 			array(
	 					'Reportnumber.'.$this->Reportnumber->primaryKey=>$child,
	 					'Reportnumber.parent_id'=>null
	 			)
	 			);

	 	$this->Data->Archiv($this->request->reportnumberID,$Verfahren);

	 	$this->redirect(array('action'=>'edit', $id));
	 }

	 public function removechild() {

		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];


		 	if (!$this->Reportnumber->exists($id)) {
	 			throw new NotFoundException(__('Invalid reportnumber'));
	 		}

	 		$this->Autorisierung->IsThisMyReport($id);

	 		$this->Reportnumber->recursive = 1;
	 		$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$id)));

			if($reportnumber['Reportnumber']['parent_id'] == 0){

			} elseif($reportnumber['Reportnumber']['parent_id'] > 0) {

				$UpdateAssignedData = array('id' => $id,'parent_id' => 0,'subindex' => 0);

				$this->Reportnumber->save($UpdateAssignedData);

				$this->Report->AssignedReportRewriteSubindex($reportnumber['Reportnumber']['parent_id']);

				$Verfahren = $reportnumber['Testingmethod']['value'];

				$this->Data->Archiv($this->request->reportnumberID,$Verfahren);

				$VarsArray = $this->request->projectvars['VarsArray'];
				$VarsArray[4] = $reportnumber['Reportnumber']['parent_id'];

				$FormName['controller'] = 'reportnumbers';
				$FormName['action'] = 'edit';
				$FormName['terms'] = implode('/', $VarsArray);
				$this->set('FormName', $FormName);
				$this->set('reportnumbers', $reportnumber);

				$this->Flash->success(__('Association was removed',true), array('key' => 'success'));

			}

	 	$this->render('enforce', 'modal');

	 }

	 public function setparent() {
	 	$id = null;
	 	$child = null;
	 	if(isset($this->request->data['type'])) {
	 		switch($this->request->data['type']) {
	 			case 'parent':
	 				$id = $this->request->data['id'];
	 				$child = $this->request->reportnumberID;
	 				break;

	 			case 'child':
	 				$id = $this->request->reportnumberID;
	 				$child = $this->request->data['id'];
	 				break;
	 		}
	 	}
	 	$report = $this->request->reportnumberID;

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	if (!$this->Reportnumber->exists($child)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Autorisierung->IsThisMyReport($id);

	 	$this->Reportnumber->recursive = 1;
	 	$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$id)));
	 	$Verfahren = ucfirst($reportnumber['Testingmethod']['value']);

	 	$subindex = $this->Reportnumber->find('list', array('fields'=>array('id', 'subindex'), 'order'=>array('subindex'=>'desc'), 'conditions'=>array('Reportnumber.parent_id'=>$id, 'Reportnumber.delete'=>0)));
	 	if(empty($subindex)) $subindex = array(-1);

	 	$this->Reportnumber->updateAll(
	 			array('Reportnumber.parent_id'=>$id, 'Reportnumber.subindex'=>(reset($subindex)+1)),
	 			array(
	 					'Reportnumber.'.$this->Reportnumber->primaryKey=>$child,
	 			)
	 			);
	 	$this->Data->Archiv($id,$Verfahren);

	 	$this->Session->setFlash(__('Report was associated'));
	 	$this->render('/closemodal', 'modal');
	 	//		$this->redirect(array_merge(array('controller'=>'reportnumbers', 'action'=>'edit'), $this->request->projectvars['VarsArray']));
	 	//$this->redirect(array('action'=>'edit', $report === null ? $id : $report));
	 }


	 public function assignedReports() {

		 $this->_assignedReports();
		 $this->_assignedToReports();
		 $this->_assignedReportsQuicksearch();
		 $this->_assignedReportsQuickMastersearch();

	 }

	 protected function _assignedReportsQuickMastersearch(){

		 if(!isset($this->request->data['quicksearchmaster_true'])) return;

		 $this->layout = 'blank';

		 App::uses('Sanitize', 'Utility');

		 $term = Sanitize::clean($this->request->data['term']);

		 // Die IDs des Projektes und des Auftrages werden getestet
		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];

		 $reportnumber = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

		 $options = array(
			 'fields' => array('testingmethod_id','testingmethod_id'),
			 'conditions' => array(
				 'TestingmethodsReports.report_id' => $reportnumber['Report']['id'],
			 )
		 );

		 $this->loadModel('TestingmethodsReports');

		 $TestingmethodsReports = $this->TestingmethodsReports->find('list',$options);

		 $optionsTestingmethod =
		 array(
			 'fields' => array('id','id'),
 				'conditions' => array(
 						'Testingmethod.id' => $TestingmethodsReports,
						'Testingmethod.allow_children' => 1
 				)
 		);

 		// die im Set vorhandenen Prüfberichte für das Menü
 		$this->loadModel('Testingmethod');
 		$this->Testingmethod->recursive = 0;
 		$Testingmethods = $this->Testingmethod->find('list', $optionsTestingmethod);

		 $term_array = explode('-',$term);

		 $options = array();

		 $options['Reportnumber.delete'] = 0;
		 $options['Reportnumber.topproject_id'] = $projectID;
		 $options['Reportnumber.cascade_id'] = $cascadeID;
		 $options['Reportnumber.order_id'] = $orderID;
		 $options['Reportnumber.report_id'] = $reportID;
		 $options['Reportnumber.testingmethod_id'] = $Testingmethods;
		 $options['Reportnumber.status'] = 0;
		 $options['Reportnumber.testingcomp_id'] = array();

		 if(count($term_array) == 1){
//			 $options['Reportnumber.number'] = intval($term_array[0]);
		 }

		 if(count($term_array) == 2){

			 if(!empty($term_array[1])) $options['Reportnumber.number'] = intval($term_array[1]);
			 if(!empty($term_array[0])) $options['Reportnumber.year'] = intval($term_array[0]);

		 }

		 if(AuthComponent::user('Testingcomp.roll_id') > 4 && AuthComponent::user('Testingcomp.extern') == 0){
			 $options['Reportnumber.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
		 } else {
			 unset($options['Reportnumber.testingcomp_id']);
		 }

		 $this->Reportnumber->recursive = 0;

		 $reportnumbers = $this->Reportnumber->find('all', array(
													 'conditions' => $options,
													 'order' => array('Reportnumber.id DESC'),
													 'fields' => array('Reportnumber.id','Reportnumber.number','Reportnumber.year','Testingmethod.verfahren'),
													 'limit' => 20
												 )
											 );

		 if(!empty($reportnumbers)){

			 $response = array();

			 $projectID = $this->request->projectvars['VarsArray'][0];
			 $cascadeID = $this->request->projectvars['VarsArray'][1];
			 $orderID = $this->request->projectvars['VarsArray'][2];
			 $reportID = $this->request->projectvars['VarsArray'][3];

			 foreach ($reportnumbers as $key => $value) {

				$AssignedUrl = Router::url(array('controller'=>'reportnumbers', 'action'=>'pdf', $projectID, $cascadeID, $orderID, $reportID, $value['Reportnumber']['id'],0,0,0,3));

			 	$response[] = array(
					'url' => $AssignedUrl,
					'value' => $value['Reportnumber']['id'],
					'label' => $value['Reportnumber']['year'] . '-' . $value['Reportnumber']['number'] . ' ' . $value['Testingmethod']['verfahren']
				);

			 }

		 } else {

			 $response = array();

		 }

		 $this->set('response',json_encode($response));

		 $this->render('json');

	 }

	 protected function _assignedReportsQuicksearch(){

		 if(!isset($this->request->data['quicksearch_true'])) return;

		 $this->layout = 'blank';

		 App::uses('Sanitize', 'Utility');

		 $term = Sanitize::clean($this->request->data['term']);

		 // Die IDs des Projektes und des Auftrages werden getestet
		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];

		 $reportnumber = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));


		 $term_array = explode('-',$term);

		 $options = array();

		 $options['Reportnumber.delete'] = 0;
		 $options['Reportnumber.topproject_id'] = $projectID;
		 $options['Reportnumber.cascade_id'] = $cascadeID;
		 $options['Reportnumber.order_id'] = $orderID;
		 $options['Reportnumber.report_id'] = $reportID;
		 $options['Reportnumber.testingmethod_id !='] = $reportnumber['Testingmethod']['id'];
		 $options['Reportnumber.parent_id'] = 0;
		 $options['Reportnumber.status'] = 0;
		 $options['Reportnumber.print'] = 0;
		 $options['Reportnumber.testingcomp_id'] = array();

		 if(count($term_array) == 1){
//			 $options['Reportnumber.number'] = intval($term_array[0]);
		 }

		 if(count($term_array) == 2){

			 if(!empty($term_array[1])) $options['Reportnumber.number'] = intval($term_array[1]);
			 if(!empty($term_array[0])) $options['Reportnumber.year'] = intval($term_array[0]);

		 }

		 if(AuthComponent::user('Testingcomp.roll_id') > 4 && AuthComponent::user('Testingcomp.extern') == 0){
			 $options['Reportnumber.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
		 } else {
			 unset($options['Reportnumber.testingcomp_id']);
		 }

		 $this->Reportnumber->recursive = 0;

		 $reportnumbers = $this->Reportnumber->find('all', array(
													 'conditions' => $options,
													 'order' => array('Reportnumber.id DESC'),
													 'fields' => array('Reportnumber.id','Reportnumber.number','Reportnumber.year','Testingmethod.verfahren'),
													 'limit' => 20
												 )
											 );

		 if(!empty($reportnumbers)){

			 $response = array();

			 $projectID = $this->request->projectvars['VarsArray'][0];
			 $cascadeID = $this->request->projectvars['VarsArray'][1];
			 $orderID = $this->request->projectvars['VarsArray'][2];
			 $reportID = $this->request->projectvars['VarsArray'][3];

			 foreach ($reportnumbers as $key => $value) {

				$AssignedUrl = Router::url(array('controller'=>'reportnumbers', 'action'=>'pdf', $projectID, $cascadeID, $orderID, $reportID, $value['Reportnumber']['id'],0,0,0,3));

			 	$response[] = array(
					'url' => $AssignedUrl,
					'value' => $value['Reportnumber']['id'],
					'label' => $value['Reportnumber']['year'] . '-' . $value['Reportnumber']['number'] . ' ' . $value['Testingmethod']['verfahren']
				);

			 }

		 } else {

			 $response = array();

		 }

		 $this->set('response',json_encode($response));

		 $this->render('json');

	 }

	 protected function _assignedToReports(){

		 if(isset($this->request->data['quicksearch_true'])) return;

		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];

		 $this->layout = 'modal';

 	 	if(!$this->Reportnumber->exists($this->request->reportnumberID)) {
 	 		throw new NotFoundException(__('Invalid reportnumber'));
 	 	}

		$this->Reportnumber->recursive = 0;
 	 	$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $this->request->reportnumberID)));

		if($reportnumber['Testingmethod']['allow_children'] != 0) return;

		if($reportnumber['Reportnumber']['parent_id'] > 0){

			$parent = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $reportnumber['Reportnumber']['parent_id'])));

			$reportnumbers['Parent'] = $parent;
			$reportnumbers['Child'] = $reportnumber;

			$this->Flash->success(__('This report is subordinate to the following report.',true) . ': ' . $this->Pdf->ConstructReportName($reportnumbers['Parent']), array('key' => 'success'));

			$this->set(compact('reportnumbers'));
			$this->render('assigned_to_reports');

		} else {

			$reportnumbers['Child'] = $reportnumber;

			$this->Flash->hint(__('Dieser Prüfbericht kann einem übergeordenten Bericht zugeordnet werden.',true), array('key' => 'hint'));
			$this->Flash->warning(__('Warning, general data in the report being linked will be overwritten.',true), array('key' => 'warning'));

			$this->set(compact('reportnumbers'));
			$this->render('assigned_to_reports');

		}


	 }

	 protected function _assignedReports(){

		 if(isset($this->request->data['quicksearch_true'])) return;

		 $projectID = $this->request->projectvars['VarsArray'][0];
		 $cascadeID = $this->request->projectvars['VarsArray'][1];
		 $orderID = $this->request->projectvars['VarsArray'][2];
		 $reportID = $this->request->projectvars['VarsArray'][3];
		 $id = $this->request->projectvars['VarsArray'][4];

		 $this->layout = 'modal';

 	 	if(!$this->Reportnumber->exists($this->request->reportnumberID)) {
 	 		throw new NotFoundException(__('Invalid reportnumber'));
 	 	}

		$this->Reportnumber->recursive = 0;
 	 	$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $this->request->reportnumberID)));

		$this->Report->SortAssignedReports($reportnumber);

 	 	$this->Reportnumber->recursive = 0;
 	 	$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $this->request->reportnumberID)));

		$this->Report->MasterAssignedReport($reportnumber);

 	 	$reportnumber = $this->Data->EnforceParentReportnumber($reportnumber);

 	 	if(isset($reportnumber['Reportnumber']['_number'])) {
 	 		$reportnumber['Reportnumber']['number'] = $reportnumber['Reportnumber']['_number'];
 	 	}

 	 	$SettingsArray = array();

 	 	$this->Reportnumber->recursive = 0;
 	 	$assignedReports = $this->Reportnumber->find('all', array('conditions'=>array('Reportnumber.id'=>$reportnumber['Reportnumber']['parent_id'])));
 	 	$conditions = array('conditions'=>array('Reportnumber.parent_id'=>$reportnumber['Reportnumber']['id']), 'order'=>array('Reportnumber.subindex'));
 	 	$assignedReports = array_merge($assignedReports, $this->Reportnumber->find('all', $conditions));

 	 	foreach($assignedReports as $key => $val) {

 	 		$val = $this->Data->EnforceParentReportnumber($val);

 	 		if(isset($val['Reportnumber']['_number'])) $val['Reportnumber']['number'] = $val['Reportnumber']['_number'];

 	 		$assignedReports[$key] = $val;
 	 	}

		$this->Flash->hint(__('Only test reports that are in the same report folder as the current report can be linked.',true), array('key' => 'hint'));
		$this->Flash->hint(__('The test reports to be linked must not have been printed or closed.',true), array('key' => 'hint'));
		$this->Flash->warning(__('Warning, general data in the report being linked will be overwritten.',true), array('key' => 'warning'));

 	 	$this->set(compact('reportnumber','assignedReports', 'SettingsArray'));

		$this->render('assigned_reports');

	 }

	 public function versionize() {
	 	$this->layout = 'modal';

		if(isset($this->request->data['Reportnumber'])){

			$this->request->revision = $this->request->reportnumberID;
			$this->request->data['Reportnumber']['Generally'] = 1;
			$this->request->data['Reportnumber']['Specific'] = 1;
			$this->request->data['Reportnumber']['Evaluation'] = 1;

		 	$this->duplicat();

			if($this->Reportnumber->getInsertID(1) == 0){
				return;
			}

		 	$this->Reportnumber->recursive = -1;
		 	$new = $this->Reportnumber->getInsertID(1);
		 	$this->Reportnumber->save(array('id'=>$this->request->reportnumberID, 'delete'=>1, 'new_version_id'=>$new));
		 	$this->Reportnumber->save(array('id'=>$new, 'old_version_id'=>$this->request->reportnumberID));

			$FormName['controller'] = 'reportnumbers';
			$FormName['action'] = 'edit';

			$Terms =
				$this->request->projectvars['VarsArray'][0].'/'.
				$this->request->projectvars['VarsArray'][1].'/'.
				$this->request->projectvars['VarsArray'][2].'/'.
				$this->request->projectvars['VarsArray'][3].'/'.
				$this->request->projectvars['VarsArray'][4].'/'.
				$new
				;

			$FormName['terms'] = $Terms;

			$this->set('saveOK',1);
			$this->set('FormName',$FormName);
		}
	 }

	 public function duplicat($id = null) {

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$repair_status = 2;
	 	$isRepair = isset($this->request->data['repair']) && false !== array_search(strtolower($this->request->data['repair']), array('1', 'true'));

		$this->Autorisierung->IsThisMyReport($id);

		if(isset($this->request->data['Reportnumber']['is_repair'])){
			$isRepair = $this->request->data['Reportnumber']['is_repair'];
		}

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

		if(isset($this->request->data['repair_status'])){
		 	$repair_status = $this->request->data['repair_status'];
		}

	 	// zu duplizierende ID speichern
	 	$oldId = $id;

		// die letzte Prüfberichtsnummer aus der Datenbank holen
	 	$this->Reportnumber->recursive = 0;
	 	$optionsOld = array('conditions' => array('Reportnumber.id' => $oldId));
	 	$dataOld = $this->Reportnumber->find('first',$optionsOld);

	 	if($dataOld['Order']['status'] == 1){
	 		return;
	 	}

	 	$dataOld = $this->Data->RevisionCheckTime($dataOld);

		if(isset($dataOld['Reportnumber']['revision_write']) && $dataOld['Reportnumber']['revision_write'] == 1){
			$this->set('dataOld',$dataOld);
			$this->set('parts',array());
	 		$this->render();
			return;
		}

	 	$this->layout = 'modal';

	 	$verfahren = $dataOld['Testingmethod']['value'];
	 	$Verfahren = ucfirst($dataOld['Testingmethod']['value']);

	 	// XML-Settings einlesen
	 	$settings = $this->Xml->XmltoArray($verfahren,'file',$Verfahren);

	 	$parts_set = array('Generally','Specific','Evaluation');

	 	foreach($settings as $_key => $_settings){
	 		$part = explode($Verfahren,$_key);
	 		if(isset($part[1])) $parts_is[] = $part[1];
	 	}

	 	$parts = array_intersect($parts_set, $parts_is);

	 	$this->set('dataOld', $dataOld);
	 	$this->set('parts', $parts);
	 	$this->set('settings', $settings);

	 	if(!isset($this->request->data['Reportnumber'])) {
	 		return;
	 	}

	 	if((isset($this->request->data['Reportnumber']) || $this->request->is('put')) && $repair_status == 2) {

			if($dataOld['Reportnumber']['version'] != $dataOld['Testingmethod']['version']  && Configure::check('OverwriteReportVersionTest') == false){
	 			$this->Set('message',__('Die Version des zu kopierenden Prüfberichtes ist veraltet und kann nicht mehr dupliziert werden.'));
				return;
			}

	 		// Settings werden immer mitkopiert
	 		$this->request->data['Reportnumber']['Setting'] = 1;

	 		$GlobalReportNumbers = array();
	 		//			if(Configure::read('GlobalReportNumbers') == true)$GlobalReportNumbers = array();
	 		//			if(Configure::read('GlobalReportNumbers') == false) $GlobalReportNumbers = array('Reportnumber.topproject_id' => $projectID);
	 		$topproject = $this->Reportnumber->Topproject->find('all', array('conditions'=>array('Topproject.id'=>$projectID)));
//	 		$topproject = $this->Navigation->Subdivisions($topproject);

	 		if(Configure::check('GlobalReportNumbers')) {
	 			switch(strtolower(Configure::read('GlobalReportNumbers'))) {
	 				case 'report':
	 					if($topproject[0]['Topproject']['subdivision'] != 1) $GlobalReportNumbers['Reportnumber.report_id'] = $reportID;

	 				case 'order':
	 					if($topproject[0]['Topproject']['subdivision'] != 1) $GlobalReportNumbers['Reportnumber.order_id'] = $orderID;

	 				case 'topproject':
	 					$GlobalReportNumbers['Reportnumber.topproject_id'] = $projectID;
	 			}
	 		}

	 		$options = array(
	 				'conditions' => array_filter($GlobalReportNumbers),
	 				'order' => 'Reportnumber.id DESC',
	 				'limit' => 1
	 		);

	 		$id = false;

	 		if(!empty($dataOld)) {
	 			$id = $this->Data->ResetNumberByYear($dataOld);
	 		}

	 		if($id == false){
	 			$this->Set('message',__('The new report could not be saved. Please, try again.'));
	 			$this->render('error');
	 			return;
	 		}

	 		$dataLog = array(
	 				'thisId' => $oldId,
	 				'oldId' => $oldId,
	 				'newId' => $id
	 		);

	 		$this->Autorisierung->Logger($oldId,$dataLog);

	 		$dataLog = array(
	 				'thisId' => $id,
	 				'oldId' => $oldId,
	 				'newId' => $id
	 		);

	 		$this->Autorisierung->Logger($id,$dataLog);

	 		// neu angelegten und zu duplizierenden Datensatz holen
	 		$optionsNew = array('conditions' => array('Reportnumber.id' => $id));
	 		//			$optionsOld = array('conditions' => array('Reportnumber.id' => $oldId));
	 		$dataNew = $this->Reportnumber->find('first',$optionsNew);

			// aktuelle Version nachfragen
			$Version = $this->Reportnumber->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $dataOld['Reportnumber']['testingmethod_id'])));

	 		$dataUpdate = array();
	 		$revisionArray = array();
	 		$ReportDataId = null;

	 		$data = array(
	 				'id' =>	$id,
	 				'report_id' => $dataOld['Reportnumber']['report_id'],
	 				'cascade_id' => $dataOld['Reportnumber']['cascade_id'],
	 				'testingmethod_id' => $dataOld['Reportnumber']['testingmethod_id'],
	 				'master' => $dataOld['Reportnumber']['master'],
					'version' => $Version['Testingmethod']['version']
	 		);

	 		if($isRepair) $data['repair_for'] = $oldId;

	 		$this->Reportnumber->save($data);

	 		// Die Daten aus den Datentabellen des zu duplizierenden Datensatzes werden geholt
	 		$ReportData[0] = 'Report'.$Verfahren.'Generally';
	 		$ReportData[1] = 'Report'.$Verfahren.'Specific';
	 		$ReportData[2] = 'Report'.$Verfahren.'Setting';

			if(!isset($this->request->revision)){
				if(Configure::check('StopDuplicate.Generally') && Configure::read('StopDuplicate.Generally') == true) unset($ReportData[0]);
				if(Configure::check('StopDuplicate.Specific') && Configure::read('StopDuplicate.Specific') == true) unset($ReportData[1]);
			}

	 		// Die Prüfergebnisse werden gesondet behandelt
	 		$ReportDataEvaluation = 'Report'.$Verfahren.'Evaluation';

	 		// die zu duplizierenden Werte aus den Datentabellen holen
	 		foreach($ReportData as $_ReportData){
	 			$this->loadModel($_ReportData);
	 			$optionData = array('conditions' => array($_ReportData.'.reportnumber_id' => $oldId));
	 			$arrayData = ($this->$_ReportData->find('first', $optionData));
	 			$dataOld = array_merge($dataOld, $arrayData);
	 		}

	 		// Wenn die Bewertung mit dupliziert wird
	 		// Die zugehörigen Prüfergebnisse werden aus der Datenbank geholt
	 		if(Configure::read('StopDuplicate.Evaluation') == false || isset($data['repair_for']) || isset($this->request->revision)){

			// Prüfbereiche werden nur bei einem Reparaturbericht mitdupliziert
			if(isset($data['repair_for'])) $this->request->data['Reportnumber']['Evaluation'] = 1;

	 		if($this->request->data['Reportnumber']['Evaluation'] == 1){

	 			$this->loadModel($ReportDataEvaluation);
	 			$optionData = array('conditions' => array(
	 					$ReportDataEvaluation.'.reportnumber_id' => $oldId,
	 					$ReportDataEvaluation.'.deleted'=>0
	 			));

	 			if($isRepair) {
	 				// Wert für NE aus den Settings holen

	 				if(isset($settings->$ReportDataEvaluation->result->radiooption->value)) {
	 					$ne = array_search('ne', $settings->xpath('Report'.$Verfahren.'Evaluation/result/radiooption/value'));
	 					if($ne === false) $ne = 2;
	 				} else {
	 					$ne = 2;
	 				}


	 				$optionData['conditions'][$ReportDataEvaluation.'.result'] = $ne;
	 			}

	 			$arrayData = ($this->$ReportDataEvaluation->find('all', $optionData));
	 			$dataOld[$ReportDataEvaluation] = $arrayData;
	 			$dataOldEvolution = $dataOld[$ReportDataEvaluation];

	 			// Die Bewertungen werden gesondert behandelt
		 			foreach($settings->$ReportDataEvaluation as $_settings) {
		 				$x = 0;
		 				foreach($dataOldEvolution as $_dataOldEvaluation){
		 					foreach($_settings as $__settings) {

								if(trim($__settings->key) == 'id') continue;

								if(isset($this->request->revision)) $__settings->dublicate = 'yes';
		 						if(isset($__settings->dublicate) && $__settings->dublicate == 'yes') {
	 								$dataUpdateEvaluation[$x][trim($__settings->model)][trim($__settings->key)] =
	 								$_dataOldEvaluation[$ReportDataEvaluation][trim($__settings->key)];
	 							}
	 						}
	 						$dataUpdateEvaluation[$x][$ReportDataEvaluation]['reportnumber_id'] = $id;
	 						$x++;
	 					}
	 				}

	 			// Die neuen Auswertungen werden hier gespeichert
				if(isset($dataUpdateEvaluation) && is_array($dataUpdateEvaluation) && count($dataUpdateEvaluation) > 0){
					foreach($dataUpdateEvaluation as $_dataUpdateEvaluation) {
						$this->$ReportDataEvaluation->create();
						$this->$ReportDataEvaluation->save($_dataUpdateEvaluation);
					}
				}
	 		}
			}

	 		// das Updatearray wird mit den zu duplizierenden Daten befüllt
	 		foreach($settings as $_key => $_settings) {

	 			$part = explode($Verfahren,$_key);

				if(count($part) == 1) continue;

	 			foreach($_settings as $__settings) {

					if(trim($__settings->key) == 'id') continue;

					if(isset($this->request->revision)) $__settings->dublicate = 'yes';

					if(($isRepair || (isset($__settings->dublicate) && $__settings->dublicate == 'yes')) && isset($dataOld[trim($__settings->model)][trim($__settings->key)])){
	 					if(isset($this->request->data['Reportnumber'][$part[1]])){
	 						if($this->request->data['Reportnumber'][$part[1]] == 1){
	 							$dataUpdate[trim($__settings->model)][trim($__settings->key)] = $dataOld[trim($__settings->model)][trim($__settings->key)];
	 						}
	 						elseif($this->request->data['Reportnumber'][$part[1]] == 0){
	 							$dataUpdate[trim($__settings->model)] = array();
	 						}
	 					}
	 				}
	 			}
	 		}

			$dataUpdate = $this->Additions->PositioningTableDublicate($Verfahren,$settings,$dataUpdate,$dataOld);

	 		// und die neuen Daten werden gespeichert
	 		foreach($ReportData as $_ReportData) {
	 			$dataUpdate[$_ReportData]['reportnumber_id'] = $id;
	 			$this->$_ReportData->create();
	 			$this->$_ReportData->save($dataUpdate[$_ReportData]);
	 			$ReportDataId .= ' '.$this->$_ReportData->getInsertID();
	 		}

			// Die Ids aus der Suchtabelle müssen übernommenwerden
			// die Funktion muss noch verfeinert werden
//			$this->Search->UpdateTableForDuplication($id,$oldId);
			$this->Search->DeleteSearchTableForDuplication($id);
			$this->Search->UpdateSearchTableForDuplication($id,$Verfahren,$dataUpdate);

	 		// Die gesammelte IDs der erzeugten Datensätze werden
	 		// wiederum in der Reportnumberstabelle gespeichert
	 		$data = array(
	 				'id' =>	$id,
	 				'data_ids' => trim($ReportDataId)
	 		);
			$this->Data->UpdateDateOfTest($id);
	 		$this->Reportnumber->save($data);

	 		// Archiv updaten
	 		$this->Data->Archiv($id,$Verfahren);

	 		$FormName['controller'] = 'reportnumbers';
	 		$FormName['action'] = 'edit';
	 		$FormName['terms'] =
	 		$this->request->projectvars['VarsArray'][0].'/'.
	 		$this->request->projectvars['VarsArray'][1].'/'.
	 		$this->request->projectvars['VarsArray'][2].'/'.
	 		$this->request->projectvars['VarsArray'][3].'/'.
	 		$id;

	 		$this->set('FormName', $FormName);
	 		$this->set('saveOK', 1);
	 		$this->set('breads', null);
	 		$this->Session->setFlash(__('The new report has been saved.'));

			if(isset($this->request->revision)){
				return;
			}
		}

		if($isRepair == 1){
			$this->render('repair','modal');
		}
	 }

	 public function editevalution($id = null) { 

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$this->Autorisierung->IsThisMyReport($id);

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	if(isset($this->request->projectvars['VarsArray'][5]) && $this->request->projectvars['VarsArray'][5] != 0) {
	 		$evalId = $this->request->projectvars['VarsArray'][5];
	 	}
	 	else {
	 		$evalId = null;
	 	}

	 	// Wenn die gesamte Naht bearbeitet werden soll
	 	if(isset($this->request->projectvars['VarsArray'][6]) && $this->request->projectvars['VarsArray'][6] == 1){
	 		$weldedit = 1;
	 	} else {
	 		$weldedit = 0;
	 		$this->request->projectvars['VarsArray'][6] = 0;
	 		$this->request->projectvars['weldedit'] = 0;
	 	}

		if($this->request->projectvars['VarsArray'][6] == 0 && $this->request->projectvars['VarsArray'][5] == 0){
			$weldedit = 1;
	 		$this->request->projectvars['VarsArray'][6] = 1;
	 		$this->request->projectvars['weldedit'] = 1;

		}
	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Data->StatusTest($reportnumbers);
		$reportnumbers = $this->Report->SetModelNames($reportnumbers);

		$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren;

		$reportnumbers['xml'] = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
		$reportnumbers = $this->Report->GetReportData($reportnumbers,array('Evaluation'));
		//$reportnumbers = $this->Report->GetReportData($reportnumbers,array('Specific'));
		$reportnumbers = $this->Report->GetWeldPagination($reportnumbers,$weldedit);
		$reportnumbers = $this->Report->WeldEdit($reportnumbers,$weldedit);
		$reportnumbers = $this->Report->CheckRevisionValues($reportnumbers);

		$arrayData = $reportnumbers['xml'];

		if(isset($this->request->data['json_true']) && $this->request->data['json_true'] == 1){

			$reportnumbers = $this->Report->AddSaveJsonUrl($reportnumbers);
			$reportnumbers = $this->Report->FunctionsUrlUpdate($reportnumbers);
			$reportnumbers = $this->Report->AddElementsForNewEval($reportnumbers);
			$reportnumbers = $this->Report->ChangeKeysForJson($reportnumbers);

			$DelArray = array('User','Topproject','Order','Report','User','EquipmentType','Equipment','Testingcomp','Tablenames','CurrentTables','TablesInUse','orginal_data');
			$reportnumbers = $this->SaveData->DeleteElements($reportnumbers,$DelArray);

			$this->layout = 'json';
			$this->set('request',json_encode($reportnumbers));
			$this->render('editevalution_json');
			return;
		}

//		$reportnumbers = $this->Report->DropdownData($reportnumbers,$arrayData);
		$reportnumbers = $this->Data->MultiselectData($reportnumbers['Tablenames'],$arrayData,$reportnumbers);
	 	$reportnumbers = $this->Data->ChangeDropdownData($reportnumbers, $arrayData, $reportnumbers['Tablenames']);
		$reportnumbers = $this->Drops->CollectMasterDropdown($reportnumbers);
		$reportnumbers = $this->Drops->CollectMasterDependency($reportnumbers);
		$reportnumbers = $this->Drops->CollectContentDropdown($reportnumbers,2);
		$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);
		$reportnumbers = $this->Data->SortthisArray($reportnumbers,'DropdownsValue','discription','asc', 'natural');
		$reportnumbers = $this->Data->SortAllDropdownArraysNatural($reportnumbers);
		$reportnumbers = $this->Data->SortAllArrays($reportnumbers,'Multiselects','asc', 'natural');
		$reportnumbers = $this->Report->CheckDevelopmentsEnabled($reportnumbers);

		$arrayData = $this->Report->RelMenueValidationErros($reportnumbers,$arrayData);
		$this->Testinstruction->CheckFormularData($reportnumbers,$arrayData['settings']);
	 	$breads = $this->Navigation->BreadForReport($reportnumbers,'edit');

		$BreadsParams = $this->request->projectvars['VarsArray'];
		$BreadsParams[6] = 1;

	 	$breads[] = array(
	 			'discription' => (isset($reportnumbers['paginationOverview']['current_weld_description_bread'])) ? $reportnumbers['paginationOverview']['current_weld_description_bread'] : '',
	 			'controller' => 'reportnumbers',
	 			'action' => 'editevalution',
	 			'pass' => $BreadsParams
	 	);

		if(!isset($arrayData['generaloutput'])){$arrayData['generaloutput'] = null;}
	 	if(!isset($arrayData['specificoutput'])){$arrayData['specificoutput'] = null;}
	 	if(!isset($arrayData['evaluationoutput'])){$arrayData['evaluationoutput'] = null;}

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
//	 	$SettingsArray['movelink'] = array('discription' => __('Move',true), 'controller' => 'reportnumbers','action' => 'move', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
//	 	$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'reportnumbers','action' => 'settings', 'terms' => $this->request->projectvars['VarsArray'],);

	 	if(Configure::read('WorkloadManager') == true){
//	 		$SettingsArray['workloadlink'] = array('discription' => __('Examiner workload'), 'controller' => 'examiners', 'action'=>'list_workload', 'terms' => $this->request->projectvars['VarsArray']);
	 	}

		$editmenue = $this->Report->EditMenue();
		$this->request->data['EditMenue']['Menue'] = $editmenue;
//		$this->request->data['EditMenue']['Status'] = $rel_menue;

	 	$breads = $this->Navigation->SubdivisionBreads($breads);
		$ReportMenue = $this->Navigation->createReportMenue($reportnumbers,$arrayData['settings']);
		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$this->request->data = $reportnumbers;

		if(!empty($arrayData['settings']->{$reportnumbers['Tablenames']['Pdf']}->settings->QM_WELDLABEL)){
	 		$this->set('ShowWeldLabel',true);
	 	}

		$this->set('arrayData',$arrayData);
		$this->set('verfahren',$verfahren);
//		$this->set('paginationOverview',$paginationOverview);
	 	$this->set('ReportMenue', $ReportMenue);
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('editmenue', $editmenue);
	 	$this->set('evalId', $evalId);
	 	$this->set('breads', $breads);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('locale', $this->Lang->Discription());
	 	$this->set('name', 'Reportnumber');
	 	$this->set('evaluationoutput', $arrayData['evaluationoutput']);
	 }


	 public function duplicatevalution($id = null) {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Autorisierung->IsThisMyReport($id);

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	if(isset($this->request->projectvars['VarsArray'][5]) && $this->request->projectvars['VarsArray'][5] != 0) {
	 		$evalId = $this->request->projectvars['VarsArray'][5];
	 	}
	 	else {
	 		$evalId = null;
	 	}

	 	// Wenn die gesamte Naht bearbeitet werden soll
	 	if(isset($this->request->projectvars['VarsArray'][6]) && $this->request->projectvars['VarsArray'][6] == 1){
	 		$weldedit = 1;
	 	} else {
	 		$weldedit = 0;
	 		$this->request->projectvars['VarsArray'][6] = 0;
	 		$this->request->projectvars['weldedit'] = 0;
	 	}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
		$reportnumbers = $this->Data->StatusTest($reportnumbers);
		$reportnumbers = $this->Report->SetModelNames($reportnumbers);

		$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren;

		$reportnumbers['xml'] = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
		$reportnumbers = $this->Report->GetReportData($reportnumbers,array('Evaluation'));
		$reportnumbers = $this->Report->WeldEdit($reportnumbers,$weldedit);
		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Report->CheckRevisionValues($reportnumbers);

		$reportnumbers = $this->Report->CollectDataForEvaluationDuplicaten($reportnumbers);
		$reportnumbers = $this->Report->DuplicateEvaluation($reportnumbers);
		$reportnumbers = $this->Report->GetReportData($reportnumbers,array('Evaluation'));
		$reportnumbers = $this->Report->GetWeldPagination($reportnumbers,$weldedit);
		$reportnumbers = $this->Report->WeldEdit($reportnumbers,$weldedit);
		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Report->CheckRevisionValues($reportnumbers);
//		$reportnumbers = $this->Report->AddSaveJsonUrl($reportnumbers);
		$reportnumbers = $this->Report->ChangeKeysForJson($reportnumbers);

		$DelArray = array('Topproject','Order','Report','User','EquipmentType','Equipment','Testingcomp','Tablenames','CurrentTables','TablesInUse','orginal_data');

		$reportnumbers = $this->SaveData->DeleteElements($reportnumbers,$DelArray);
//		$this->autoRender = false;

		$this->layout = 'json';
		$this->set('request',json_encode($reportnumbers));

	 }


	 public function duplicatevalutionXXX($id = null) {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$Id = $this->request->projectvars['VarsArray'][4];
	 	$evalutionId = $this->request->projectvars['VarsArray'][5];

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	// Wenn die gesamte Naht dupliziert werden soll
	 	if(isset($this->request->projectvars['VarsArray'][6]) && $this->request->projectvars['VarsArray'][6] == 1){
	 		$welddubli = 1;
	 	}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);

	 	$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

		$attribut_disabled = false;

	 	// Statustest
	 	if(
	 			$reportnumbers['Reportnumber']['status'] > 0 ||
	 			$reportnumbers['Reportnumber']['status'] > 0 ||
	 			$reportnumbers['Reportnumber']['deactive'] > 0 ||
	 			$reportnumbers['Reportnumber']['settled'] > 0 ||
	 			$reportnumbers['Reportnumber']['delete'] > 0
	 			){
					$attribut_disabled = true;
				}

		if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

	 	if($attribut_disabled === true){

	 				$this->request->data = $reportnumbers;
	 				$this->Session->setFlash(__('The Testingreport was closed.'));
	 				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->Session->read('lastURL'),2000));

	 				$this->set('evalutionID', 0);
	 				$this->set('FormName', array('controller' => 'reportnumbers', 'action' => 'edit'));
	 				$this->set('breads', null);
	 				$this->render();
	 				$this->response->send();
	 				$this->_stop();
	 	}


	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
	 	$verfahren = $reportnumbers['Testingmethod']['value'];

	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 	$ReportSetting = 'Report'.$Verfahren.'Setting';

	 	$this->loadModel($ReportEvaluation);
	 	$this->loadModel($ReportSetting);

	 	$optionsOld =	array(
	 			'conditions' =>	array(
	 					$ReportEvaluation.'.id' => $evalutionId,
	 					$ReportEvaluation.'.deleted'=>0
	 			)
	 	);
	 	$optionsSettings =	array(
	 			'conditions' =>	array(
	 					$ReportSetting.'.reportnumber_id' => $id
	 			)
	 	);

	 	// alte Daten holen und Array bearbeiten
	 	$Evaluation = 'Report'.$Verfahren.'Evaluation';
	 	$Setting = 'Report'.$Verfahren.'Setting';

	 	// XML-Settings einlesen
	 	$setting = $this->$ReportSetting->find('first',$optionsSettings);
	 	$settings = $this->Xml->XmltoArray($verfahren,'file',ucfirst($verfahren));


	 	$data = $this->$ReportEvaluation->find('first',$optionsOld);
	 	$oldDescription = $data[$ReportEvaluation];

		// diese Array wird der Revison übergeben, wenn nötig
		$TransportArray['model'] = $Evaluation;
		$TransportArray['row'] = null;
		$TransportArray['this_value'] = null;
		$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');
		$TransportArray['last_id_for_radio'] = 0;
	 	$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');

	 	// Wenn die gesamte Naht dubliziert werden soll
	 	if(isset($welddubli) && $welddubli == 1){

	 		$optionsWeld =	array(
	 				'conditions' =>	array(
	 						$ReportEvaluation.'.description' => $data[$Evaluation]['description'],
	 						$ReportEvaluation.'.reportnumber_id' => $id,
	 						$ReportEvaluation.'.deleted' => 0
	 				)
	 		);

	 		$data = $this->$ReportEvaluation->find('all',$optionsWeld);
	 		$dataDubli = array();
			$actio_array = array();

	 		$x = 0;
	 		foreach($data as $key => $_data){

				$actio_array[] = $_data[$ReportEvaluation]['id'];

	 			// XML-Settings durchsuche und zu duplizierende Werte suchen
	 			// && $__settings->dublicate == 'yes'
	 			foreach($settings as $_settings) {
	 				foreach($_settings as $__settings) {
	 					if(isset($__settings->dublicate) && $__settings->dublicate == 'yes' && $__settings->model == $ReportEvaluation){
	 						$dataDubli[$x][$ReportEvaluation][trim($__settings->key)] = $_data[$ReportEvaluation][trim($__settings->key)];
	 					}
	 				}
	 			}


	 			$dataDubli[$key][$ReportEvaluation]['reportnumber_id'] = $Id;

	 			$dataDubli[$key][$ReportEvaluation]['description'] = $dataDubli[$key][$ReportEvaluation]['description'].' ('.__('Copy', true).')';

	 			unset($dataDubli[$key][$ReportEvaluation]['id']);
	 			$x++;
	 		}

	 		$this->$ReportEvaluation->saveAll($dataDubli);

			$TransportArray['last_value'] = implode(',',$actio_array);
			$reportnumbers['Reportnumber']['revision_progress'] > 0? $this->Data->SaveRevision($reportnumbers,$TransportArray,'duplicatevalution/weld'):'';

	 		// Die letzte Id für die Weiterleitung holen
	 		$LastID = explode(',',$this->$ReportEvaluation->getInsertID($x+1));

	 		foreach($dataDubli as $key => $data) {
	 			$dataDubli[$key][$ReportEvaluation]['id'] = $LastID[$key];
	 		}
				// Die Ids aus der Suchtabelle müssen übernommenwerden
				// die Funktion muss noch verfeinert werden
//				$this->Search->UpdateTableForDuplication($idNew,$evalutionId);

	 		$LastID = $this->$ReportEvaluation->getInsertID();

	 		$TransportArray['table_id'] =$LastID;
			$TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');
			$TransportArray['last_value'] = implode(',',$actio_array);
			$reportnumbers['Reportnumber']['revision_progress'] > 0 ? $this->Data->SaveRevision($reportnumbers,$TransportArray,'duplicatevalution/weld'):'';

	 		$this->Autorisierung->Logger($id,array_map(function($elem) use($ReportEvaluation) { return $elem[$ReportEvaluation]; }, $dataDubli));

	 		// Die Daten werden noch einmal geholt und im Archiv gespeichert
	 		//			$data = $this->Reportnumber->find('first',$optionsArchiv);

	 		// den Archiveintrag vornehmen
	 		//			$this->Data->Archiv($Id,$Verfahren);

	 		$FormName['controller'] = 'reportnumbers';
	 		$FormName['action'] = 'edit';

	 		$this->Session->setFlash(__('The Area has been duplicated', true));

	 		$this->Data->Archiv($this->request->reportnumberID,$Verfahren);

	 		$this->set('breads', null);
	 		$this->set('id', $Id);
	 		$this->set('evalutionID', 0);
	 		$this->set('afterEDIT', $this->Navigation->afterEDIT('reportnumbers/editevalution/'.$projectID.'/'.$cascadeID.'/'.$orderID.'/'.$reportID.'/'.$id.'/'.$LastID.'/1',0));

	 	}

	 	else {
	 		if(!empty($data)) {
	 			$dataDubli = array();

	 			// XML-Settings durchsuche und zu duplizierende Werte suchen
	 			// && $__settings->dublicate == 'yes'
	 			foreach($settings as $_settings) {
	 				foreach($_settings as $__settings) {
	 					if(isset($__settings->dublicate)  && $__settings->dublicate == 'yes' && $__settings->model == $ReportEvaluation) {
	 						//						pr($__settings);
	 						$dataDubli[trim($__settings->model)][trim($__settings->key)] = $data[trim($__settings->model)][trim($__settings->key)];
	 					}
	 				}
	 			}

	 			unset($dataDubli[$ReportEvaluation]['id']);

	 			$dataDubli[$ReportEvaluation]['reportnumber_id'] = $Id;

	 			if(isset($dataDubli[$ReportEvaluation]['position'])){
	 				$dataDubli[$ReportEvaluation]['position'] = $dataDubli[$ReportEvaluation]['position'].' ('.__('Copy', true).')';
	 			}
	 			else {
	 				$dataDubli[$ReportEvaluation]['description'] = $dataDubli[$ReportEvaluation]['description'].' ('.__('Copy', true).')';
	 			}

	 			// neue Daten speicher und anzeigen
	 			$this->$ReportEvaluation->save($dataDubli);

	 			$dataDubli[$ReportEvaluation]['id'] = $idNew = $this->$ReportEvaluation->getInsertID();

	 			$this->Autorisierung->Logger($id,$dataDubli);

				$TransportArray['last_value'] = $dataDubli[$ReportEvaluation]['id'];
                                $TransportArray['table_id'] = $idNew;
                                $TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');
				$reportnumbers['Reportnumber']['revision_progress'] > 0 ? $this->Data->SaveRevision($reportnumbers,$TransportArray,'duplicatevalution/weld_area'):'';

	 			$this->Session->setFlash(__('The Evalution Area has been duplicated', true));

	 			$this->Data->Archiv($this->request->reportnumberID,$Verfahren);
	 		}
	 		$this->set('breads', null);
	 		$this->set('afterEDIT', $this->Navigation->afterEDIT('reportnumbers/editevalution/'.$projectID.'/'.$cascadeID.'/'.$orderID.'/'.$reportID.'/'.$id.'/'.$idNew,0));
	 	}
	 }

	 public function pdf() {

	 	$this->layout = 'pdf';
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];
	 	$version = $this->request->projectvars['VarsArray'][8];

		if(isset($this->request->data['collectpdf']) && $this->request->data['collectpdf'] == 1) $CollectPdf = 1;
		if(isset($this->request->data['showpdf']) && $this->request->data['showpdf'] == 1) $ShowPdf = 1;

	 	$options = array('conditions' => array(
	 			'Reportnumber.id' => $reportnumberID,
//	 			'Reportnumber.topproject_id' => $projectID
	 	));

		$MailDeliver = array();

		if(isset($this->request->data['Reportnumber']['Remark'])) $MailDeliver['Remark'] = $this->request->data['Reportnumber']['Remark'];
		if(isset($this->request->data['Reportnumber']['Mails'])) $MailDeliver['Mails'] = $this->request->data['Reportnumber']['Mails'];

	 	// aktuellen Bericht und alle verknï¿½pften Berichte holen
	 	$this->Reportnumber->recursive = 1;
	 	$reportnumber = $this->Reportnumber->find('first', $options);
		$reports = $reportnumber;

		// kann dan wohl auch weg
		if(!isset($reports['Reportnumber'])){
			CakeLog::write('problems', print_r($this->request,true));
			CakeLog::write('problems', print_r($options,true));
			CakeLog::write('problems', print_r($report,true));
		}

		if(count($reports) == 0){
			$this->Session->setFlash(__('This report could not be printed. Please, try again.'));
			$this->redirect(array('controller' => 'users', 'action' => 'loggedin'));
		}

		// Wenn print auf 0 steht, wird der Bericht zum ersten mal gedruckt<br />
		// und somit eine Erstschrift
		// muss noch an die anderen Schließmethoden angepasst werden
//		$permissions = array( 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');

		if($version == 0){

			if($reports['Reportnumber']['status'] == 0 || $reports['Reportnumber']['print'] == 0){

				$version = 1;
//				$permissions = array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');
				$this->set('print_version',__('Original/original',true));

			}

			if($reports['Reportnumber']['print'] > 0 && $reports['Reportnumber']['status'] > 0){

				$version = 2;
//				$permissions = array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');
				$this->set('print_version',__('Duplikat/duplicate',true));
	
			}
		} elseif ($version == 3){
			$this->set('print_version',__('Probedruck/proof print',true));
		}

//		$this->set('permissions',$permissions);

	 	//Abhängige Berichte ins Array einfügen
	 	if(Configure::read('PrintChildReports')) {
		 	$childReports = $this->Pdf->FetchLinkedReports($reportnumberID);
		 	$reports = array_merge(array($reports), $this->Reportnumber->find('all', array('conditions'=>array('Reportnumber.id'=>$childReports))));
	 	} else {
	 		$reports = array($reports);
	 	}

	 	$pdfData = array();

	 	foreach($reports as $key=>$report) {
	 		$Verfahren = ucfirst($report['Testingmethod']['value']);

	 		// solange der Prüfbericht nicht geschlossen ist wird das Archiv aktualisiert
	 		if($report['Reportnumber']['status'] < 2){
	 			$this->Data->Archiv($report['Reportnumber']['id'],$Verfahren);
	 			$reports[$key] = $this->Reportnumber->find('first', array('conditions' => array(
	 					'Reportnumber.id' => $report['Reportnumber']['id']
	 			)));
	 		}
	 	}

	 	$this->set('url_qr',$_SERVER['HTTP_HOST'] . $this->base);

	 	foreach($reports as $report) {

	 		$Verfahren = ucfirst($report['Testingmethod']['value']);

	 		// falls Bilder vorhanden sind
	 		$this->loadModel('Reportimage');

	 		$reportimages = $this->Reportimage->find('all', array('order' =>array('sorting ASC'), 'conditions' => array('Reportimage.reportnumber_id' => $report['Reportnumber']['id'], 'Reportimage.print'=>1)));

			$this->request->PDFImageCount = 1;
			if($this->Session->check('PDFImageCount') == true) $this->request->PDFImageCount = $this->Session->read('PDFImageCount');

	 		$verfahren = $report['Testingmethod']['value'];
	 		$ReportArchiv = 'Report'.$Verfahren.'Archiv';
	 		$ReportSetting = 'Report'.$Verfahren.'Setting';
	 		$ReportGenerally = 'Report'.$Verfahren.'Generally';
	 		$ReportSpecific = 'Report'.$Verfahren.'Specific';
	 		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 		$ReportPdf = 'Report'.$Verfahren.'Pdf';

	 		$ReportTableNames = array(
	 				'Archiv' => 'Report'.$Verfahren.'Archiv',
	 				'Setting' => 'Report'.$Verfahren.'Setting',
	 				'Generally' => 'Report'.$Verfahren.'Generally',
	 				'Specific' => 'Report'.$Verfahren.'Specific',
	 				'Evaluation' => 'Report'.$Verfahren.'Evaluation',
	 		);

	 		$this->loadModel($ReportArchiv);
	 		$this->loadModel($ReportGenerally);
	 		$this->loadModel($ReportSpecific);

	 		$optionsReport = array('conditions' => array('reportnumber_id' => $report['Reportnumber']['id']));
	 		$reportArchiv = $this->$ReportArchiv->find('first',$optionsReport);

	 		$this->Reportnumber->HiddenField->recursive = -1;
	 		$hiddenFields = $this->Reportnumber->HiddenField->find('all', $optionsReport);

	 		$data = array();
	 		$settings = array();

	 		$data = utf8_decode($reportArchiv[$ReportArchiv]['data']);

	 		//			$data = Xml::toArray(Xml::build(html_entity_decode($data)));
	 		$data = Xml::build(html_entity_decode($data));
	 		//			$data = $this->Xml->XmltoArray($reportArchiv[$ReportArchiv]['data'],'string');
	 		$value_data = array();
	 		foreach($data as $_key => $_data){

	 			if($_key == $ReportEvaluation) continue;

	 			if(!empty($_data)){
	 				foreach($_data as $__key => $__data){
	 					if(is_string(trim($__data))){
	 						$value_data[$_key][$__key] = utf8_decode(trim($__data));
	 					}
	 				}
	 			}
	 		}

	 		$number = 0;
	 		foreach($data->$ReportEvaluation as $_data){
	 			foreach($_data as $__key => $__data){
	 				$value_data[$ReportEvaluation][trim($_data->id)][$__key] = utf8_decode(trim($__data));
	 			}
	 			$number++;
	 		}

	 		$settingsData = $this->Xml->DatafromXmlForReport($verfahren,$report);

	 		$settings = $settingsData['settings'];

			$this->Data->CreateRevisionsList($reportnumber,$data,$settings);

	 		$reportvendor = 'report';

	 		if(!empty($settings->$ReportPdf->settings->QM_REPORT_VENDOR)){
	 			if(file_exists(APP . 'Vendor' . DS . trim($settings->$ReportPdf->settings->QM_REPORT_VENDOR) . '.php')){
	 				$reportvendor = trim($settings->$ReportPdf->settings->QM_REPORT_VENDOR);
	 			}
	 		}

	 		// aus dem Auswertungsobjekt wird ein Array erstellt
	 		$arrayEvaluation = array();
	 		$x = 0;

	 		foreach($data->$ReportEvaluation as $_key => $_ReportEvaluation) {
	 			//			foreach($data['archiv'][$ReportEvaluation] as $_key => $_ReportEvaluation) {
	 			foreach($_ReportEvaluation as $__key => $__ReportEvaluation){
	 				$arrayEvaluation[$x][$_key][$__key] = trim($__ReportEvaluation);
	 			}
	 			$x++;
	 		}

			if(Configure::check('ErrorDescriptionOutput') && Configure::read('ErrorDescriptionOutput') == true) {

					$data = $this->Pdf->ErrorDescriptionOutput($arrayEvaluation,'Report'.$Verfahren,$data);

			};

	 		$this->Pdf->ReplaceData($data,$settings,$arrayEvaluation,$ReportTableNames);


	 		if($report['Testingmethod']['allow_children']){
	 			$data = $this->Pdf->InsertDataFromChildReports($reports, $data);
	 		}

	 		$pdfData[] = array(
	 				'hiddenFields' => $hiddenFields,
	 				'revChanges' => $this->Data->GetRevisionChanges($report['Reportnumber']['id']),
	 				'reportimages' => $reportimages,
	 				'version' => $version,
	 				'Verfahren' => $Verfahren,
	 				'allow_children' => $report['Testingmethod']['allow_children'],
	 				'data' => $data,
	 				'settings' => $settings,
	 				'user' => $this->Auth->user()
	 		);
	 	}

	 	if($reports[0]['Testingmethod']['value'] == 'wkn') {
	 		$this->Reportnumber->Testingmethod->recursive = -1;
	 		$_methods = $this->Reportnumber->Testingmethod->find('list', array('fields'=>array('name', 'verfahren'), 'group' => array('name'), 'conditions'=>array('value !='=>'wkn')));
	 		if(isset($_methods['ALG'])) $_methods['ALG'] = 'Kurz- / Langbericht';
	 		$_methods['US–W'] = 'Ultraschallprüfung';
	 		$_methods[__('Pressuretest')] = null;
	 		$_methods['______'] = null;

	 		/*
	 		 $pdfData[0]['methods'] = $_methods;
	 		 */

	 		// Array mit Prüfverfahren sortieren und filtern
	 		$sorting = array_flip(array('MT', 'PT',  'RT', 'US–W', 'VT', __('Pressuretest'), '______'));
	 		$pdfData[0]['methods'] = array_merge(array_intersect_key($sorting, $_methods), array_intersect_key($_methods, $sorting));

	 	}

		$this->loadmodel('Reportfile');
        
		$attachmentdiscriptions = '';
        $attachments = $this->Reportfile->find('all',array('fields' => 'discription','conditions' =>array('Reportfile.reportnumber_id' =>$report['Reportnumber']['id'])));

		if(!empty($attachments)){

			foreach ($attachments as $attachmentskey => $attachmentsvalue) {
    			$attachmentdiscriptions .= ', '.$attachmentsvalue['Reportfile'] ['discription'];
    		}

			if (isset($attachmentdiscriptions) && $attachmentdiscriptions[0] == ',') {

				$attachmentdiscriptions = substr($attachmentdiscriptions, 1);
			
			}
		}

		$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumberID));
		$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);
		$optionsGenerally = array('conditions' => array($ReportGenerally.'.reportnumber_id' => $reportnumberID));

	 	$Generally = $this->$ReportGenerally->find('first', $optionsGenerally);

		 if(Configure::check('CertifcateManagerReportInsert') && Configure::read('CertifcateManagerReportInsert') == true) {
			$firstKey = array_key_first($Generally);

			if(isset($Generally[$firstKey]['examiner_certificat'])) {
				$examiner_certificat = explode(' ', $Generally[$firstKey]['examiner_certificat']);
				$this->loadModel('Certificate');
				$certificate = $this->Certificate->find('first', array('conditions' => array('certificat' => $examiner_certificat[0])));
				$examiner_stamp_paths = $this->Pdf->GetStampPaths($reportnumbers,$settingsData);

				if(is_array($examiner_stamp_paths)) $this->set('examiner_stamp_path', $examiner_stamp_paths);
			}
		}

	 	// Die zugehörigen Prüfdaten werden aus der Datenbank geholt
	 	$optionsSpecific = array('conditions' => array($ReportSpecific.'.reportnumber_id' => $reportnumberID));
	 	$Specific = $this->$ReportSpecific->find('first', $optionsSpecific);

	 	// Die zugehörigen Prüfergebnisse werden aus der Datenbank geholt

	 	$reportnumber = array_merge($reportnumbers, $Generally, $Specific);
    $this->request->data= $reportnumbers;

    $additions = $this->Additions->Add($settingsData);
    $this->set('additionsdata', $additions);

		$output = $this->Pdf->MakeSignatory($ReportGenerally,$report,$settingsData,$version);

		if($reports[0]['Testingmethod']['allow_children']){
		 	$output  =  $this->Pdf->MakeSignatoryChilds($ReportGenerally,$reports,$settingsData,$version);
		}

	 	if(isset($output['xmp'])) $this->set('xmp',$output['xmp']);
	 	if(isset($output['signature']) && count($output['signature']) > 0) $this->set('signature',$output['signature']);

		isset($attachmentdiscriptions) && !empty($attachmentdiscriptions)? $this->set('attachments',$attachmentdiscriptions): '';

		$this->set('value_data', $value_data);
	 	$this->set('reportvendor', $reportvendor);
	 	$this->set('pdfData', $pdfData);

		if(isset($reportnumber['Testingcomp']['report_email'])){
			$compemail = $reportnumber['Testingcomp']['report_email'];
			$this->set('compemail', $compemail);
		}
		if(isset($reportnumber['Testingcomp']['report_email'])){
    	$projectmail =  $reportnumber['Topproject']['email'];
    	$this->set('projectmail', $projectmail);
		}

		if($version < 3){
			$this->Reportnumber->updateAll(
				array('Reportnumber.print' => time()),
				array('Reportnumber.id' => $reportnumberID,'Reportnumber.print' => 0)
			);
		}
		//Unterberichte schließen
		if(Configure::check('CloseChildReportsOnPrint') && Configure::read('CloseChildReportsOnPrint') == true) {
			$childsprint = $this->Reportnumber->find('all',array('conditions' => array('Reportnumber.parent_id'=>$reportnumberID)));
				if(!empty($childsprint)) {
					foreach ($childsprint as $ckey => $cvalue) {
					$cvalue['Reportnumber'] ['print'] = time();
					$this->Reportnumber->save($cvalue['Reportnumber']);
				// code...
				}
			}
		}

		$dataLog = array('print' => $version,'data' =>$this->request->data);

		if(isset($MailDeliver['Remark'])) $this->request->data['Reportnumber']['Remark'] = $MailDeliver['Remark'];
		if(isset($MailDeliver['Mails'])) $this->request->data['Reportnumber']['Mails'] = $MailDeliver['Mails'];

	 	$this->Autorisierung->Logger($reportnumberID,$dataLog);

		$SecID = str_pad($reportnumber['Reportnumber']['id'], 16 ,'0', STR_PAD_LEFT);

		$this->set('SecID', $SecID);
		$this->set('locale', $this->Lang->Discription());

		if(isset($ShowPdf) && $ShowPdf == 1){

			$this->layout = 'json';

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;
			$PDFView->set('return_show',true);

			$Data = $PDFView->render('pdf');

			$this->set('request',json_encode(array('string' => $Data)));
			$this->render('pdf_show');

			return;

		}

		if(isset($CollectPdf) && $CollectPdf == 1){

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;
			$PDFView->set('return',true);

			$Data = $PDFView->render('pdf');

			return $Data;
		}


	 	if($reports[0]['Testingmethod']['value'] == 'wkn') {

			$name = $this->Pdf->CreatePdfDataName($reportnumber,$Verfahren) . '.pdf';

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;

			$Data = $PDFView->render('pdf_wkn');

			$this->response->body($Data);
			$this->response->type('application/pdf');

			$this->response->header(array('Content-type: application/pdf'));
			$this->response->download($name);

			return $this->response;

	 	} else {

			$name = $this->Pdf->CreatePdfDataName($reportnumber,$Verfahren) . '.pdf';

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;

			$Data = $PDFView->render('pdf');

			$this->response->body($Data);
			$this->response->type('application/pdf');

			$this->response->header(array('Content-type: application/pdf'));

			$this->response->download($name);

			return $this->response;

 		}
	 }

	 public function editweldlabel($id = null) {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
	 	$verfahren = $reportnumbers['Testingmethod']['value'];

	 	$ReportGenerally = 'Report'.$Verfahren.'Generally';
	 	$ReportSpecific = 'Report'.$Verfahren.'Specific';
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 	$ReportSetting = 'Report'.$Verfahren.'Setting';
	 	$ReportArchiv = 'Report'.$Verfahren.'Archiv';
	 	$ReportPdf = 'Report'.$Verfahren.'Pdf';

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	$this->loadModel($ReportGenerally);
	 	$this->loadModel($ReportEvaluation);
	 	$this->loadModel($ReportSetting);

	 	$this->set('arrayData',$arrayData);

	 	// die Settings des zu bearbeitenden Datensatzes holen
	 	$optionSetting = array('conditions' => array($ReportGenerally.'.reportnumber_id' => $id));

	 	// die Daten des zu bearbeitenden Datensatzes holen
	 	$optionEvaluation = array(
	 							'order' => array(
	 								$ReportEvaluation.'.sorting' => 'asc',
//	 								'NumericOnly('.$ReportEvaluation.'.description)*1'=>'asc',
	 								$ReportEvaluation.'.description' => 'asc',
	 								$this->$ReportEvaluation->primaryKey=>'asc'
	 							),
								'conditions' => array(
									$ReportEvaluation.'.reportnumber_id' => $id,
									$ReportEvaluation.'.deleted' => 0
									)
								);


	 	$generally = $this->$ReportGenerally->find('first', $optionSetting);
	 	$data = $this->$ReportEvaluation->find('all', $optionEvaluation);

		$AllDescription = null;

		$x = 0;
		foreach($data as $_key => $_data){
			if($x > 0) $AllDescription .= '; ';
			$AllDescription .= $_data[$ReportEvaluation]['description'];
			$x++;
		}

	 	$this->set('AllDescription',$AllDescription);
	 	$this->set('generally',$generally);
	 	$this->set('data',$data);

	 }

	 public function printweldlabel() {

	 	$this->layout = 'pdf';
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = array($this->request->projectvars['VarsArray'][4]);
	 	$weldID = array($this->request->projectvars['VarsArray'][5]);

	 	if(isset($this->request->data['Reportnumber']) && is_array($this->request->data['Reportnumber']) && count($this->request->data['Reportnumber'])) {
	 		$weldID = array_unique(array_values(Hash::flatten($this->request->data['Reportnumber'])));
	 	}

		if(isset($this->request->data['showpdf']) && $this->request->data['showpdf'] == 1) $ShowPdf = 1;

	 	$options = array('conditions' => array(

				'Reportnumber.id' => $id,
//	 			'Reportnumber.topproject_id' => $projectID
	 	));

	 	$report = $this->Reportnumber->find('first', $options);

		if(!isset($report['Testingmethod'])){
			CakeLog::write('problems', print_r($this->request,true));
			CakeLog::write('problems', print_r($options,true));
			CakeLog::write('problems', print_r($report,true));
		}

	 	$Verfahren = ucfirst($report['Testingmethod']['value']);
	 	$verfahren = $report['Testingmethod']['value'];
	 	$ReportArchiv = 'Report'.$Verfahren.'Archiv';
	 	$ReportSetting = 'Report'.$Verfahren.'Setting';
	 	$ReportGenerally = 'Report'.$Verfahren.'Generally';
	 	$ReportSpecific = 'Report'.$Verfahren.'Specific';
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 	$ReportPdf = 'Report'.$Verfahren.'Pdf';

	 	$settingsData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
	 	$settings = $settingsData['settings'];

	 	$this->loadModel($ReportGenerally);
	 	$this->loadModel($ReportSpecific);
	 	$this->loadModel($ReportEvaluation);

		$printdata = array();

	 	if(isset($this->request->data['Reportnumber']['custom_form'])) {

			if(isset($ShowPdf) && $ShowPdf == 1){

				$SecID = str_pad($report['Reportnumber']['id'], 16 ,'0', STR_PAD_LEFT);

				$this->layout = 'json';

				$this->autoRender = false;

				$PDFView = new View($this);
				$PDFView->autoRender = false;
				$PDFView->layout = null;
				$PDFView->set('return_show',true);
				$PDFView->set('printdata',$printdata);
				$PDFView->set('ReportGenerally', $ReportGenerally);
			 	$PDFView->set('ReportSpecific', $ReportSpecific);
			 	$PDFView->set('ReportEvaluation', $ReportEvaluation);
			 	$PDFView->set('ReportPdf', $ReportPdf);
			 	$PDFView->set('report', $report);
			 	$PDFView->set('settings', $settings);
			 	$PDFView->set('SecID', $SecID);

				$Data = $PDFView->render('printweldlabel');

				$this->set('request',json_encode(array('string' => $Data)));
				$this->render('pdf_show');

				return;

			}

	 		$this->set('settings', $settings);
	 		$this->set('ReportPdf', $ReportPdf);
	 		$this->set('report', $report);
	 		$this->render('custom_label');
	 		return false;
	 	}

	 	if($weldID > 0){
			foreach($weldID as $_weldID) {

		 		$eval = $this->$ReportEvaluation->find('first', array('conditions'=>array(
	 				'reportnumber_id'=>$id,
	 				$ReportEvaluation.'.'.$this->$ReportEvaluation->primaryKey=>$_weldID,
	 				$ReportEvaluation.'.deleted' => 0
		 		)));

		 		// Gelöschte und bereits verarbeitete Nähte überspringen (bei Druck mehrere Nahtabschnitte)
		 		if(!isset($eval[$ReportEvaluation]['description'])  || isset($printdata[$eval[$ReportEvaluation]['description']])) continue;

		 		$data = $this->$ReportGenerally->find('first', array('conditions'=>array(
		 				'reportnumber_id'=>$id
		 		)));

		 		$data = array_merge($data, $this->$ReportSpecific->find('first', array('conditions'=>array(
		 				'reportnumber_id'=>$id
		 		))));

	 			$data = array_merge($data, $eval);

	 			$results = $this->$ReportEvaluation->find('all',array('conditions'=>array('reportnumber_id'=>$id,'description'=>$data[$ReportEvaluation]['description'], 'deleted'=>0)));

	 			$result = $settings->$ReportEvaluation->result->radiooption->value;
	 			$resultlist = array();
				$position = null;

	 			$x = 0;

				foreach($results as $_key => $_results){

					if(isset($_results[$ReportEvaluation]['position']) && $_results[$ReportEvaluation]['position'] != ''){
						if($x > 0) $position .= '; ';
						$position .= $_results[$ReportEvaluation]['position'];
					}

	 				$resultlist[trim($result[$_results[$ReportEvaluation]['result']])][] = $_key;
		 			$x++;
	 			}

				if($position != null) $data[$ReportEvaluation]['position'] = $position;

				unset($x);

	 			$printdata[$data[$ReportEvaluation]['description']] = compact('result', 'resultlist', 'data');

				$this->set('printdata', $printdata);
				$this->set('resultlist', $resultlist);
				$this->set('result', $result);
				$this->set('data', $data);
			 	$this->set('filmnumber', count($results));
	 		}

	 		$this->set('printdata', $printdata);
	 		$this->set('resultlist', $resultlist);
	 		$this->set('result', $result);
	 		$this->set('data', $data);
	 		$this->set('filmnumber', count($results));
	 	}
		elseif(isset($this->request->data['Reportnumber']['custom_form']) && $this->request->data['Reportnumber']['custom_form'] == 1) {
	 		$data = $this->request->data;
	 		$this->set('printdata', array('result'=>$settings->$ReportEvaluation->result->radiooption->value, 'resultlist'=>array(), 'data'=>$data));
	 		$this->set('resultlist', array());
	 		$this->set('result', $settings->$ReportEvaluation->result->radiooption->value);
	 		$this->set('data', $data);
	 		$this->set('filmnumber', '');
		}

	 	if(isset($this->request->data['Description'])){

			$Description = $this->request->data['Description'];

	 		$data = $this->$ReportGenerally->find('first', array('conditions'=>array(
	 				'reportnumber_id'=>$reportID
	 		)));

	 		$data = array_merge($data, $this->$ReportSpecific->find('first', array('conditions'=>array(
	 				'reportnumber_id'=>$reportID
	 		))));

	 		$data = array_merge($data, $eval);
			$this->set('data', $data);
			$this->set('description', $Description);

	 	}

		$SecID = str_pad($report['Reportnumber']['id'], 16 ,'0', STR_PAD_LEFT);

		if(isset($ShowPdf) && $ShowPdf == 1){

			$this->layout = 'json';

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;
			$PDFView->set('return_show',true);
			$PDFView->set('ReportGenerally', $ReportGenerally);
		 	$PDFView->set('ReportSpecific', $ReportSpecific);
		 	$PDFView->set('ReportEvaluation', $ReportEvaluation);
		 	$PDFView->set('ReportPdf', $ReportPdf);
		 	$PDFView->set('report', $report);
		 	$PDFView->set('settings', $settings);
		 	$PDFView->set('SecID', $SecID);

			$Data = $PDFView->render('printweldlabel');

			$this->set('request',json_encode(array('string' => $Data)));
			$this->render('pdf_show');

			return;

		}

	 	$this->set('ReportGenerally', $ReportGenerally);
	 	$this->set('ReportSpecific', $ReportSpecific);
	 	$this->set('ReportEvaluation', $ReportEvaluation);
	 	$this->set('ReportPdf', $ReportPdf);
	 	$this->set('report', $report);
	 	$this->set('settings', $settings);
	 	$this->set('SecID', $SecID);
	 }

	 public function printqrcode() {

	 	$this->layout = 'pdf';

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
	 	$evalId = $this->request->projectvars['VarsArray'][5];
		$weldedit = $this->request->projectvars['VarsArray'][6];

	 	$options = array('conditions' => array(
	 			'Reportnumber.id' => $id,
	 			'Reportnumber.topproject_id' => $projectID
	 	));

	 	$report = $this->Reportnumber->find('first', $options);

	 	$Verfahren = ucfirst($report['Testingmethod']['value']);
	 	$verfahren = $report['Testingmethod']['value'];
	 	$ReportArchiv = 'Report'.$Verfahren.'Archiv';
	 	$ReportSetting = 'Report'.$Verfahren.'Setting';
	 	$ReportGenerally = 'Report'.$Verfahren.'Generally';
	 	$ReportSpecific = 'Report'.$Verfahren.'Specific';
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 	$ReportPdf = 'Report'.$Verfahren.'Pdf';


	 	if(isset($evalId) && $evalId > 0){


	 		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 		$this->loadModel($ReportEvaluation);

	 		$optionsWeld =	array(
	 				'fields' => array('description'),
	 				'conditions' =>	array(
	 						$ReportEvaluation.'.id' => $evalId,
	 						$ReportEvaluation.'.reportnumber_id' => $id
	 				)
	 		);

	 		// Die Nahtbezeichnung wird anhand er id geholt
	 		// falls die Nahtbezeichnug geändert wurde
	 		$diskriptionWeld = $this->$ReportEvaluation->find('first',$optionsWeld);

	 		// neuen Conditions um alle ids der Naht zu holen
	 		$optionsWeld =	array(
	 				'conditions' =>	array(
	 						$ReportEvaluation.'.description' => $diskriptionWeld[$ReportEvaluation]['description'],
	 						$ReportEvaluation.'.reportnumber_id' => $id
	 				)
	 		);


	 		$idsWeld = $this->$ReportEvaluation->find('all',$optionsWeld);

	 		$this->set('welds', $idsWeld);
	 	}

	 	$settingsData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
	 	$settings = $settingsData['settings'];

	 	$this->set('ReportGenerally', $ReportGenerally);
	 	$this->set('ReportSpecific', $ReportSpecific);
	 	$this->set('ReportEvaluation', $ReportEvaluation);
	 	$this->set('ReportPdf', $ReportPdf);
	 	$this->set('report', $report);
	 	$this->set('settings', $settings);
	 }

	 public function images() {

		$this->Sicherheit->ClamavScan();

		$ApplicationType = array('image/jpeg','image/png');

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

		$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

	 	$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Data->StatusTest($reportnumbers);

	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);
	 	if(isset($reportnumbers['Reportnumber']['_number'])) {
	 		$reportnumbers['Reportnumber']['number'] = $reportnumbers['Reportnumber']['_number'];
	 	}

	 	$this->loadModel('Reportimage');

		$conditions = array(
			'order' => array('sorting ASC'),
			'conditions' => array(
				'Reportimage.reportnumber_id' => $id
			)
		);

	 	$reportimages = $this->Reportimage->find('all', $conditions);

	 	$savePath = Configure::read('report_folder') . $projectID . DS . 'images' . DS . $reportnumbers['Reportnumber']['id'] . DS;

	 	if(isset($this->request->data['image_id'])) {
	 		$cond = array('reportnumber_id'=>$this->request->data['reportnumber_id'], 'id'=>$this->request->data['image_id']);
	 		$this->Reportimage->updateAll(array('print'=>intval($this->request->data['image_value'])), $cond);
	 		echo json_encode($this->Reportimage->find('first', array('conditions'=>$cond, 'fields'=>array('Reportimage.id', 'Reportimage.print'))));
	 		die();
	 	}

		if(isset($_FILES['file']) && count($_FILES['file']) > 0){

			$this->autoRender = false;

			$ExtensionGrant = false;

			foreach($ApplicationType as $key => $value){
				if($value == $_FILES['file']['type']){
					$ExtensionGrant = true;
					break;
				}
			}

			if($ExtensionGrant === false) die();

			$this->request->data['Order'] = $_FILES;
		}

		if(isset($this->request['data']['Order'])){

			$info = getimagesize($this->request['data']['Order']['file']["tmp_name"]);
			$basename = $this->request['data']['Order']['file']['name'];
			$suffix = false;

			if($info['mime'] == 'image/jpeg') $suffix = 'jpg';
			if($info['mime'] == 'image/png') $suffix = 'png';

			if($suffix == false){

				$this->Flash->error($_FILES['file']['name'] . ' ' .  __('Wrong file format',true), array('key' => 'error'));
	 			unset($_FILES);
				unset($this->request->data['Order']);

			}
		}

	 	if(isset($this->request['data']['Order'])){

			$attribut_disabled = false;

			if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
			if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

			if($attribut_disabled === true) die();

	 		if($_FILES['file']['error'] > 0){
	 			$max_upload = (int)(ini_get('upload_max_filesize'));
				$this->Flash->error(Configure::read('FileUploadErrors.' . $_FILES['file']['error']) . ' ' . __('Your file is bigger than',true) . ' ' . $max_upload . 'MB' . ' ' . $dataImage['basename'], array('key' => 'error'));
	 			unset($_FILES);
				unset($this->request->data['Order']);
	 		}

			$errortest = $this->Image->CheckFileSize($this->request['data']['Order']['file']["tmp_name"]);

			if($errortest === false){
				$this->Flash->error(__('Internal error, file too big.',true), array('key' => 'error'));
	 			unset($_FILES);
				unset($this->request->data['Order']);
				$this->render();
				return;
			}

			$Microtime = microtime();
			$Microtime = str_replace(array('.',' '),'_',$Microtime);

	 		$saveFile = $reportnumbers['Topproject']['id'].'_'.$reportnumbers['Reportnumber']['id'].'_'.$reportnumbers['User']['id'].'_'.$Microtime . '_' . uniqid() . '.' . $suffix;

	 		// Wenn nicht vorhanden Ordner erzeugen
	 		if(!file_exists($savePath)){
	 			$dir = new Folder($savePath, true, 0755);
	 		}

	 		move_uploaded_file($this->request['data']['Order']['file']["tmp_name"],$savePath.'/'.$saveFile);

			$sort = $this->Image->MaxSortImages($reportimages);

	 		$dataImage = array(
	 				'reportnumber_id' => $reportnumbers['Reportnumber']['id'],
	 				'basename' => $basename,
	 				'name' => $saveFile,
					'sorting' => $sort,
	 				'user_id' => $reportnumbers['Reportnumber']['user_id']
	 		);

	 		$this->loadModel('Reportimage');
	 		$this->Reportimage->save($dataImage);

	 		$dataImage['basename'] = $this->request['data']['Order']['file']['name'];
	 		$dataImage['id'] = $this->Reportimage->getLastInsertId();

			// diese Array wird der Revison übergeben, wenn nötig
			$TransportArray['model'] = 'Reportimage';
			$TransportArray['row'] = null;
			$TransportArray['this_value'] = $dataImage['name'];
			$TransportArray['last_id_for_radio'] =0;
			$TransportArray['table_id'] = $dataImage['id'];
			$TransportArray['last_value'] = null;

			$reportnumbers['Reportnumber']['revision_progress'] > 0 ? $this->Data->SaveRevision($reportnumbers,$TransportArray,'images/addimage'):'';

			$this->Flash->success(__('The following image was saved successfully:',true) . ' ' . $dataImage['basename'], array('key' => 'success'));

	 		$this->Autorisierung->Logger($id,$dataImage);
	 	} else {
	 		foreach($reportimages  as $_key => $_reportimages){
	 			if(file_exists($savePath.$_reportimages['Reportimage']['name'])){
	//				$this->Image->CheckResizeFileSize($savePath . $_reportimages['Reportimage']['name']);
	 				$reportimages[$_key]['Reportimage']['imagedata'] = 'data:png;base64,'.$this->Image->Tumbs($savePath,$_reportimages['Reportimage']['name'],250,175,null);
	 			}
	 		}
	 	}

	 	$menue = $this->Navigation->NaviMenue(null);

	 	$breads = $this->Navigation->BreadForReport($reportnumbers,'images');

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);

	 	// Unterdaten für den Prüfbericht holen, da diese für die Validierung benötigt werden
	 	$arrayData = $this->Xml->DatafromXml(strtolower($reportnumbers['Testingmethod']['value']),'file',ucfirst(strtolower($reportnumbers['Testingmethod']['value'])));

	 	$models = array();

	 	foreach($arrayData['settings']->xpath('/settings/*/*[validate]/model') as $model) {
	 		$models = array_unique($models + array(trim($model)));
	 	}

	 	if(!empty($models)) {
	 		foreach($models as $model) {
	 			$this->loadModel($model);
	 			if(preg_match('/Evaluation$/', $model)) {
	 				$reportnumbers[$model] = $this->$model->find('all', array('conditions'=>array($model.'.reportnumber_id'=>$reportnumbers['Reportnumber']['id'])));
	 			} else {
	 				$reportnumbers = array_merge($reportnumbers, $this->$model->find('first', array('conditions'=>array($model.'.reportnumber_id'=>$reportnumbers['Reportnumber']['id']))));
	 			}
	 		}
	 	}

		$modelsarray = array('Reportimage');
		$revisionvalues = $this->Data->CheckRevisionValues($reportnumbers,$modelsarray);

	 	$reportnumbers['RevisionValues'] = $revisionvalues;

	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
	 	$verfahren = $reportnumbers['Testingmethod']['value'];
	 	$this->request->verfahren = $Verfahren;

	 	$breads = $this->Navigation->SubdivisionBreads($breads);
		$ReportMenue = $this->Navigation->createReportMenue($reportnumbers,$arrayData['settings']);
		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$reportimages = $this->Data->CheckFileExists($reportimages,$savePath);

	 	$this->set('ReportMenue', $ReportMenue);
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('reportimages', $reportimages);
	 	$this->set('breads', $breads);
	 	$this->set('menues', $menue);

		$this->Autorisierung->IsThisMyReport($id);

	 }

	 public function imagediscription($id = null) {

		$this->loadModel('Reportimage');

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$testingreportID = $this->request->projectvars['VarsArray'][4];
	 	$id = $this->request->projectvars['VarsArray'][6];

		$this->layout = 'modal';

		if(isset($this->request['data']['delete_image']) && $this->request['data']['delete_image'] == 1) $delete_image = 1;

	 	$this->Autorisierung->IsThisMyReport($testingreportID);

	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $testingreportID)));
		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

		if(isset($this->request->data['json_true'])){

			$data =$this->Image->SortImages($reportnumbers);

			$this->layout = 'blank';
			$this->set('request', json_encode($data));
			$this->render('savejson');
			return;
		}

		$attribut_disabled = $this->Image->CheckReportStatus($reportnumbers);

		if($attribut_disabled === true){
	//		$this->Flash->warning(__('This report has been closed, image properties can no longer be edited.'), array('key' => 'warning'));
		}


		$this->Image->EditImageDescription($reportnumbers);
		$this->Image->DeleteImage($reportnumbers);

	 	$reportimages = $this->Reportimage->find('first', array('conditions' => array('Reportimage.id' => $id)));

		if(count($reportimages) == 0){
			$this->render('imagedelete');
			return;
		}

	 	$savePath = Configure::read('report_folder') . $projectID . DS . 'images' . DS . $reportimages['Reportimage']['reportnumber_id'] . DS;
	 	$savetumbsPath  = '/files/'.$this->request->projectID.'/images/'.$reportimages['Reportimage']['reportnumber_id'].'/';

	 	if(file_exists($savePath.$reportimages['Reportimage']['name'])){
	 		$reportimages['Reportimage']['imagedata'] = 'data:png;base64,'.$this->Image->Tumbs($savePath,$reportimages['Reportimage']['name'],500,500,$savetumbsPath);
	 	}

	 	$this->request->projectvars['VarsArray'][4] = $reportimages['Reportimage']['reportnumber_id'];
	 	$this->request->projectvars['VarsArray'][6] = $reportimages['Reportimage']['id'];

	 		$SettingsArray = array();
	 		$this->Navigation->setImageLink('imagediscription',$reportimages['Reportimage']['reportnumber_id'],$SettingsArray);

			$this->request->data = array_merge($reportnumbers,$reportimages);

	 		$this->set('reportnumbers', $reportnumbers);
	 		$this->set('reportimages', $reportimages);
			$this->set('SettingsArray', array());

			if(isset($delete_image) && $delete_image == 1) {

				$this->render('imagedelete');
			}
	 }

	 public function image($id = null) {

		$this->layout = 'blank';

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$testingreportID = $this->request->projectvars['VarsArray'][4];
	 	$id = $this->request->projectvars['VarsArray'][6];

	 	$this->loadModel('Reportimage');
	 	$thisreportimages = $this->Reportimage->find('first', array('conditions' => array('Reportimage.id' => $id)));

	 	if(count($thisreportimages) == 1){

	 		$imagePath = Configure::read('report_folder') . $projectID . DS . 'images' . DS . $testingreportID . DS . $thisreportimages['Reportimage']['name'];

	 		if(file_exists($imagePath))
	 		{
	 			$imageinfo = getimagesize($imagePath);
	 			$imageContent = file_get_contents($imagePath);
	 			$imData = base64_encode($imageContent);
	 			$this->set('imageinfo',$imageinfo);
	 			$this->set('imagePath',$imagePath);
	 			$this->set('imData',$imData);
	 		}
	 	}
	 }

	 public function video($id = null) {
	 	$this->layout = 'modal';

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$testingreportID = $this->request->projectvars['VarsArray'][4];
	 	$id = $this->request->projectvars['VarsArray'][5];

	 	$this->loadModel('Reportvideo');
	 	$thisreportimages = $this->Reportvideo->find('first', array('conditions' => array('Reportvideo.id' => $this->request->evalId)));

	 	$SettingsArray = array();


	 	$this->set('SettingsArray', $SettingsArray);

	 	if(count($thisreportimages) == 1){

	 		$videoPath = $this->request->base . '/' . 'files' . '/' . $this->request->projectID . '/' . 'videos' .'/' . $thisreportimages['Reportvideo']['reportnumber_id'] . '/' . $thisreportimages['Reportvideo']['name'];

	 		$this->set('videoPath', $videoPath);
	 	}
	 }

	 public function videos() {

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
	 	$this->loadModel('Reportvideo');
	 	$reportimages = $this->Reportvideo->find('all', array('conditions' => array('Reportvideo.reportnumber_id' => $id)));

	 	// Speicherpfad für den Upload
	 	$savePath = ROOT . DS . 'app' .DS . 'files' . DS . $reportnumbers['Topproject']['id'] . DS . 'videos' .DS . $reportnumbers['Reportnumber']['id'] . DS;

	 	$savePathWeb = ROOT . DS . 'app' .DS . 'webroot'. DS . 'files' . DS . $reportnumbers['Topproject']['id'] . DS . 'videos' .DS . $reportnumbers['Reportnumber']['id'] . DS;

	 	if(isset($this->request['data']['Order'])){

	 		$saveFile = $reportnumbers['Topproject']['id'].'_'.$reportnumbers['Reportnumber']['id'].'_'.$reportnumbers['User']['id'].'_'.time().'.mp4';

	 		// Wenn nicht vorhanden Ordner erzeugen
	 		if(!file_exists($savePath)){
	 			$dir = new Folder($savePath, true, 0755);
	 		}
	 		if(!file_exists($savePathWeb)){
	 			$dir = new Folder($savePathWeb, true, 0755);
	 		}

	 		move_uploaded_file($this->request['data']['Order']['file']["tmp_name"],$savePath. DS .$saveFile);
	 		//			move_uploaded_file($this->request['data']['Order']['file']["tmp_name"],$savePath. DS .$saveFile);

	 		copy($savePath. DS .$saveFile, $savePathWeb. DS .$saveFile);

	 		$dataImage = array(
	 				'reportnumber_id' => $reportnumbers['Reportnumber']['id'],
	 				'name' => $saveFile,
	 				'user_id' => $reportnumbers['Reportnumber']['user_id']
	 		);

	 		$this->Reportvideo->save($dataImage);

	 		$dataImage['basename'] = $this->request['data']['Order']['file']['name'];
	 		$dataImage['id'] = $this->Reportvideo->getLastInsertId();
	 		$this->Autorisierung->Logger($id,$dataImage);
	 	}

	 	$savetumbsPath  = '/files/'.$reportnumbers['Topproject']['id'].'/videos/'.$reportnumbers['Reportnumber']['id'].'/';

	 	$menue = $this->Navigation->NaviMenue(null);

	 	$breads = $this->Navigation->BreadForReport($reportnumbers,'videos');

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
//	 	$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'reportnumbers','action' => 'settings', 'terms' => $this->request->projectvars['VarsArray'],);
//	 	$SettingsArray['workloadlink'] = array('discription' => __('Examiner workload'), 'controller' => 'examiners', 'action'=>'list_workload', 'terms' => $this->request->projectvars['VarsArray']/*, 'disabled'=>isset($reportnumbers['Reportnumber']['status']) && $reportnumbers['Reportnumber']['status'] != 0*/);

	 	// Unterdaten für den Prüfbericht holen, da diese für die Validierung benötigt werden
	 	$arrayData = $this->Xml->DatafromXml(strtolower($reportnumbers['Testingmethod']['value']),'file',ucfirst(strtolower($reportnumbers['Testingmethod']['value'])));
	 	$models = array();
	 	foreach($arrayData['settings']->xpath('/settings/*/*[validate]/model') as $model) {
	 		$models = array_unique($models + array(trim($model)));
	 	}

	 	if(!empty($models)) {
	 		foreach($models as $model) {
	 			$this->loadModel($model);
	 			if(preg_match('/Evaluation$/', $model)) {
	 				$reportnumbers[$model] = $this->$model->find('all', array('conditions'=>array($model.'.reportnumber_id'=>$reportnumbers['Reportnumber']['id'])));
	 			} else {
	 				$reportnumbers = array_merge($reportnumbers, $this->$model->find('first', array('conditions'=>array($model.'.reportnumber_id'=>$reportnumbers['Reportnumber']['id']))));
	 			}
	 		}
	 	}

	 	$breads = $this->Navigation->SubdivisionBreads($breads);

	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('validationErrors', $this->Reportnumber->getValidationErrors($arrayData, $reportnumbers));
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('reportfiles', $reportimages);
	 	$this->set('breads', $breads);
	 	$this->set('menues', $menue);
	 }

	 public function upload($id = null) {

		if(!Configure::check('XmlImportReport') || Configure::read('XmlImportReport') == false) return false;

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);

	 	$verfahren = $reportnumbers['Testingmethod']['value'];
	 	$Verfahren = ucfirst($verfahren);
	 	$this->request->verfahren = $Verfahren;

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	if(isset($reportnumbers['Reportnumber']['_number'])) {
	 		$reportnumbers['Reportnumber']['number'] = $reportnumbers['Reportnumber']['_number'];
	 	}


		if($this->Session->check('Import.xml')){

			$data_object = Xml::build(html_entity_decode($this->Session->read('Import.xml')));
			$this->Session->write('Insert.xml',$this->Session->read('Import.xml'));
			$this->Session->delete('Import.xml');

			if(isset($data_object) && is_object($data_object)){
				$this->set('data', $data_object);
			}
		}

		if(isset($this->request->data['Data'])){
			$xmlcheck = XMLReader::open($this->request->data['Data']['file']['tmp_name']);
			$xmlcheck->setParserProperty(XMLReader::VALIDATE, true);
			$file = new File($this->request->data['Data']['file']['tmp_name']);
			if($file->executable() == true) die('tot');
			$xml_string = $file->read(true, 'r');
			$this->Session->write('Import.xml',$xml_string);
		}

		if(isset($this->request->data['Import'])){

			if(isset($this->request->data['Import']['field']) && is_array($this->request->data['Import']['field']) && count($this->request->data['Import']['field']) > 0){
				$parts = $this->request->data['Import']['field'];
			} else {
				return false;
			}

			if(isset($parts)){

				$toInsert = array();
				$toInsertOkay = 0;

				foreach($parts as $_key => $_parts){
					if($_parts == 1) $toInsert[$_parts] = $_parts;
					if($_parts == 2) $toInsert[$_parts] = $_parts;
					if($_parts == 3) $toInsert[$_parts] = $_parts;
				}

				$data_object = Xml::build(html_entity_decode($this->Session->read('Insert.xml')));

		 		$verfahren = $reportnumbers['Testingmethod']['value'];
		 		$Verfahren = ucfirst($verfahren);
			 	$this->request->verfahren = $Verfahren;
			 	$ReportGenerally = 'Report'.$Verfahren.'Generally';
			 	$ReportSpecific = 'Report'.$Verfahren.'Specific';
			 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
			 	$ReportArchiv = 'Report'.$Verfahren.'Archiv';
			 	$ReportSettings = 'Report'.$Verfahren.'Setting';
			 	$ReportPdf = 'Report'.$Verfahren.'Pdf';

			 	$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

			 	$this->loadModel($ReportGenerally);
			 	$this->loadModel($ReportSpecific);
			 	$this->loadModel($ReportEvaluation);

				$ReportGenerallyId = $this->$ReportGenerally->find('first',array('fields' => array('id'),'conditions' => array($ReportGenerally . '.reportnumber_id' => $id)));
				$ReportSpecificId = $this->$ReportSpecific->find('first',array('fields' => array('id'),'conditions' => array($ReportSpecific . '.reportnumber_id' => $id)));

//			 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

				if(isset($toInsert[1]) && $toInsert[1] == 1){
					if(isset($data_object->ReportGenerally) && !empty($data_object->ReportGenerally)){

						$inputArray['id'] = $ReportGenerallyId[$ReportGenerally]['id'];
						$inputArray['reportnumber_id'] = $id;

						foreach($data_object->ReportGenerally->children() as $_key => $_data){
							$inputArray[trim($_data->key)] = trim($_data->value);
						}
	 					if($this->$ReportGenerally->save($inputArray)){
							$toInsertOkay++;
						}
					}
				}

				$inputArray = array();

				if(isset($toInsert[2]) && $toInsert[2] == 2){
					if(isset($data_object->ReportSpecific) && !empty($data_object->ReportSpecific)){

						$inputArray['id'] = $ReportSpecificId[$ReportSpecific]['id'];
						$inputArray['reportnumber_id'] = $id;

						foreach($data_object->ReportSpecific->children() as $_key => $_data){
							if($_key == 'heads'){
								$x = 1;
								foreach($_data->children() as $__key => $__data){
									foreach($__data as $___key => $___data){
										$inputArray[trim($___data->key) . '_0' . $x] = trim($___data->value);
									}

									$x++;
								}
							}

							if(!isset($_data->key) && empty($_data->key)) continue;
							$inputArray[trim($_data->key)] = trim($_data->value);
						}
	 					if($this->$ReportSpecific->save($inputArray)){
							$toInsertOkay++;
						}
					}
				}

				$inputArray = array();

				if(isset($toInsert[3]) && $toInsert[3] == 3){
					if(isset($data_object->ReportEvaluation) && !empty($data_object->ReportEvaluation)){
						foreach($data_object->ReportEvaluation->children() as $_key => $_data){
							$inputArray['reportnumber_id'] = $id;
							foreach($_data as $__key => $__data){
								$inputArray[trim($__data->key)] = trim($__data->value);
							}
			 				$this->$ReportEvaluation->create();
		 					$this->$ReportEvaluation->save($inputArray);
							$inputArray = array();
						}
						// bisher sinnlos, muss noch ne logik rein
						$toInsertOkay++;
					}
				}
			}
		 	$this->set('toInsertOkay', true);
		}

		$breads = $this->Navigation->BreadForReport($reportnumbers,'upload');
		$breads = $this->Navigation->SubdivisionBreads($breads);

	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$ReportMenue = $this->Navigation->createReportMenue($reportnumbers,$arrayData['settings']);
		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('breads', $breads);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('ReportMenue', $ReportMenue);
	 }

	 public function export($id = null) {

		if(!Configure::check('XmlExportReport') || Configure::read('XmlExportReport') == false) return false;

		$this->autoRender = false;

		if(!Configure::check('XmlImportReport') || Configure::read('XmlImportReport') == false) return false;

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

//	 	$this->Reportnumber->autoClose($id);
	 	$this->Autorisierung->IsThisMyReport($id);

	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);

	 	$verfahren = $reportnumbers['Testingmethod']['value'];
	 	$Verfahren = ucfirst($verfahren);
	 	$this->request->verfahren = $Verfahren;

	 	if(isset($reportnumbers['Reportnumber']['_number'])) {
	 		$reportnumbers['Reportnumber']['number'] = $reportnumbers['Reportnumber']['_number'];
	 	}


		$verfahren = $reportnumbers['Testingmethod']['value'];
		$Verfahren = ucfirst($verfahren);
		$this->request->verfahren = $Verfahren;
		$ReportArchiv = 'Report'.$Verfahren.'Archiv';
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		$ReportArchiv = 'Report'.$Verfahren.'Archiv';
		$ReportSettings = 'Report'.$Verfahren.'Setting';
		$ReportPdf = 'Report'.$Verfahren.'Pdf';

		$InputArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);
		$OutputArray = array('ReportGenerally','ReportSpecific','ReportEvaluation');

		$OutputArray = array(0 => array('input' => $ReportGenerally,'output' => 'ReportGenerally'),1 => array('input' => $ReportSpecific,'output' => 'ReportSpecific'),2 => array('input' => $ReportEvaluation,'output' => 'ReportEvaluation'));

		$this->loadModel($ReportArchiv);

		$DataNew = array();
		$archiv = $this->$ReportArchiv->find('first',array('conditions' => array($ReportArchiv.'.reportnumber_id' => $id)));
/*
		$Data = $this->Xml->XmltoArray($archiv[$ReportArchiv]['data'],'string',$verfahren);

		$DataNew['MetaData']['Testingmethod'] = $verfahren;
		$DataNew['MetaData']['Device'] = '';
		$DataNew['MetaData']['Timestamp'] = time();
		$DataNew['MetaData']['Timeszone'] = date_default_timezone_get();

		foreach($OutputArray as $_key => $_OutputArray){
			foreach($Data->$_OutputArray['input']->children() as $__key => $__data){

				if($_OutputArray['output'] == 'ReportEvaluation'){
//					$DataNew[$_OutputArray['output']] = null;
					continue;
				}

				$DataNew[$_OutputArray['output']][trim($__key)] = trim($__data);
			}
		}

		$XmlData =  $this->Xml->ArraytoXml($DataNew);
*/
		$this->Xml->exportXML($archiv[$ReportArchiv]['data'],'report.xml');
	 }

	 public function files($id = null) {

		$this->Sicherheit->ClamavScan();

		$ImageExtension = array('pdf');
		$ThisImageExtension = 'pdf';
	 	$ApplicationType = array('application/pdf');

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
	 	$reportnumbers = $this->Data->EnforceParentReportnumber($reportnumbers);
	 	if(isset($reportnumbers['Reportnumber']['_number'])) {
	 		$reportnumbers['Reportnumber']['number'] = $reportnumbers['Reportnumber']['_number'];
	 	}

		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		$reportnumbers = $this->Data->StatusTest($reportnumbers);

	 	$this->loadModel('Reportfile');
	 	$reportfiles = $this->Reportfile->find('all', array('conditions' => array('Reportfile.reportnumber_id' => $id)));

		$attribut_disabled = false;

		if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;

		if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

	 	// Speicherpfad für den Upload
	 	//		$savePath = ROOT . DS . 'app' .DS . 'files' . DS . $reportnumbers['Topproject']['id'] . DS . 'files' .DS . $reportnumbers['Reportnumber']['id'] . DS;
	 	$savePath = Configure::read('report_folder') . $projectID . DS . 'files' . DS . $reportnumbers['Reportnumber']['id'] . DS;

		if(isset($_FILES['file']) && count($_FILES['file']) > 0){

			$this->autoRender = false;

			$this->request->data['Order'] = $_FILES;

			$MimeContentType = mime_content_type($_FILES['file']['tmp_name']);

			$ExtensionGrant = false;

			foreach($ApplicationType as $key => $value){
				if($value == $MimeContentType){
					$ExtensionGrant = true;
					break;
				}
			}

			if($ExtensionGrant === false){
				$this->Flash->error($_FILES['file']['name'] . ' ' .  __('Wrong file format',true), array('key' => 'error'));
				unset($_FILES);
				unset($this->request->data['Order']);
			}
		}

		if(isset($this->request['data']['Order']) && $this->request->data['Order']['file'] > 0){
			$file_size = $this->request->data['Order']['file']['size'] / 1024 / 1024;
			$max_upload = (int)(ini_get('upload_max_filesize'));
			if($file_size > $max_upload){
				$this->Flash->error(Configure::read('FileUploadErrors.' . $_FILES['data']['error']['Order']['file']) . ' ' . __('Your file is bigger than',true) . ' ' . $max_upload . 'MB', array('key' => 'error'));
				unset($this->request->data['Order']);
			}
		}

	 	if(isset($this->request['data']['Order'])){

			if($attribut_disabled === true){
				if(!Configure::check('FileUploadAfterClosing')) die();
				if(Configure::check('FileUploadAfterClosing') && Configure::read('FileUploadAfterClosing') == false) die();
        if(Configure::check('FileUploadAfterClosing') && Configure::read('FileUploadAfterClosing') == true) $attribut_disabled = true;
			}

			$regex = '/^[^.]*.[^.]*$/';
			$TestRegex = preg_match($regex, $this->request['data']['Order']['file']['name']);

			if($TestRegex == 0 && isset($dataFiles)){
				$this->Flash->error("The following file has a wrong file name and can't uploaded:" . ' ' . $dataFiles['basename'], array('key' => 'error'));
				return;
			}

			// Infos zur Datei
	 		$fileinfo = pathinfo($this->request['data']['Order']['file']['name']);

			$Microtime = microtime();
			$Microtime = str_replace(array('.',' '),'_',$Microtime);

	 		$saveFile = $reportnumbers['Topproject']['id'].'_'.$reportnumbers['Reportnumber']['id'].'_'.$reportnumbers['User']['id'].'_'.$Microtime . '_' . uniqid() . '.' . $ThisImageExtension;

	 		// Wenn nicht vorhanden Ordner erzeugen
	 		if(!file_exists($savePath)){
	 			$dir = new Folder($savePath, true, 0755);
	 		}

	 		move_uploaded_file($this->request['data']['Order']['file']["tmp_name"],$savePath.'/'.$saveFile);

	 		$dataFiles = array(
	 				'reportnumber_id' => $reportnumbers['Reportnumber']['id'],
	 				'name' => $saveFile,
	 				'basename' => $fileinfo['basename'],
	 				'user_id' => $reportnumbers['Reportnumber']['user_id']
	 		);

	 		$this->loadModel('Reportfile');
	 		$this->Reportfile->save($dataFiles);

			// diese Array wird der Revison übergeben, wenn nötig
			$TransportArray['model'] = 'Reportfile';
			$TransportArray['row'] = null;
			$TransportArray['this_value'] = null;
			$TransportArray['last_id_for_radio'] = 0;
			$TransportArray['table_id'] = $this->Reportfile->getLastInsertId();
			$TransportArray['last_value'] = $dataFiles['name'];

			$reportnumbers['Reportnumber']['revision_progress'] > 0 ?$this->Data->SaveRevision($reportnumbers,$TransportArray,'files/addfile'):'';

	 		$this->Autorisierung->Logger($reportnumbers['Reportnumber']['id'],$dataFiles);

	 		$reportfiles = $this->Reportfile->find('all', array('conditions' => array('Reportfile.reportnumber_id' => $id)));
			$this->Flash->success('The following file was saved successfully:' . ' ' . $dataFiles['basename'], array('key' => 'success'));
	 	}

	 	$menue = $this->Navigation->NaviMenue(null);

	 	$breads = $this->Navigation->BreadForReport($reportnumbers,'files');

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);

	 	// Unterdaten für den Prüfbericht holen, da diese für die Validierung benötigt werden
	 	$arrayData = $this->Xml->DatafromXml(strtolower($reportnumbers['Testingmethod']['value']),'file',ucfirst(strtolower($reportnumbers['Testingmethod']['value'])));
	 	$models = array();
	 	foreach($arrayData['settings']->xpath('/settings/*/*[validate]/model') as $model) {
	 		$models = array_unique($models + array(trim($model)));
	 	}

	 	if(!empty($models)) {
	 		foreach($models as $model) {
	 			$this->loadModel($model);
	 			if(preg_match('/Evaluation$/', $model)) {
	 				$reportnumbers[$model] = $this->$model->find('all', array('conditions'=>array($model.'.reportnumber_id'=>$reportnumbers['Reportnumber']['id'])));
	 			} else {
	 				$reportnumbers = array_merge($reportnumbers, $this->$model->find('first', array('conditions'=>array($model.'.reportnumber_id'=>$reportnumbers['Reportnumber']['id']))));
	 			}
	 		}
	 	}

	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
	 	$verfahren = $reportnumbers['Testingmethod']['value'];
	 	$this->request->verfahren = $Verfahren;

	 	$breads = $this->Navigation->SubdivisionBreads($breads);
		$ReportMenue = $this->Navigation->createReportMenue($reportnumbers,$arrayData['settings']);
		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

    $modelsarray = array('Reportfile');
  	$revisionvalues = $this->Data->CheckRevisionValues($reportnumbers,$modelsarray);

	 	$reportnumbers['RevisionValues'] = $revisionvalues;

		$reportfiles = $this->Data->CheckFileExists($reportfiles,$savePath);

	 	$this->set('ReportMenue', $ReportMenue);
		$this->set('SettingsArray', $SettingsArray);
	 	$this->set('settings', $arrayData['settings']);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('reportfiles', $reportfiles);
	 	$this->set('breads', $breads);
	 	$this->set('menues', $menue);

		$this->Autorisierung->IsThisMyReport($id);

	 }

	 public function getfile($id = null) {

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];
	 	$id = $this->request->projectvars['VarsArray'][6];

	 	$this->layout = 'blank';

	 	$this->loadModel('Reportfile');
	 	$this->Reportfile->recursive = -1;
	 	$reportfiles = $this->Reportfile->find('first', array('conditions' => array('Reportfile.id' => $id)));

		if(count($reportfiles) == 0) $this->autoRender = false;

	 	// Speicherpfad für den Upload
	 	if(count($reportfiles) > 0){
	 		$savePath = Configure::read('report_folder') . $projectID . DS . 'files' .DS .
	 		$reportfiles['Reportfile']['reportnumber_id'] . DS . $reportfiles['Reportfile']['name'];
	 		$this->set('savePath', $savePath);
	 	}
	 }

	 public function delfile($id = null) {

	 	$this->loadModel('Reportfile');
	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];
	 	$id = $this->request->projectvars['VarsArray'][6];

	 	// zum löschen notwändige Daten aus der Datenbank holen
	 	$reportfiles = $this->Reportfile->find('first', array('conditions' => array('Reportfile.id' => $id)));
	 	$reportID = $reportfiles['Reportfile']['reportnumber_id'];

	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $reportfiles['Reportfile']['reportnumber_id'])));
		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

		$attribut_disabled = false;

		if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
		if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

		if($attribut_disabled === true) die();

	 	// Speicherpfad für den Upload
	 	$delPath = Configure::read('report_folder') . $projectID . DS . 'files' .DS . $reportfiles['Reportfile']['reportnumber_id'] . DS . $reportfiles['Reportfile']['name'];

		if(isset($this->request['data']['Reportfile'])) {

			if($this->Reportfile->delete($this->request['data']['Reportfile']['id'])){

				if(file_exists($delPath)) @unlink($delPath);

				$this->Autorisierung->Logger($reportnumbers['Reportnumber']['id'],$reportfiles);

				// diese Array wird der Revison übergeben, wenn nötig
				$TransportArray['model'] = 'Reportfile';
				$TransportArray['row'] = null;
				$TransportArray['this_value'] = null;
				$TransportArray['last_id_for_radio'] = 0;
				$TransportArray['table_id'] = $id;
				$TransportArray['last_value'] = $reportfiles['Reportfile']['name'];

				$reportnumbers['Reportnumber']['revision_progress'] > 0 ?$this->Data->SaveRevision($reportnumbers,$TransportArray,'files/delfile'):'';

				$reportfiles = $this->Reportfile->find('all', array('conditions' => array('Reportfile.reportnumber_id' => $reportID)));

				$this->Session->setFlash(__('File was delete'));

				$FormName = array();

				$FormName['controller'] = 'reportnumbers';
				$FormName['action'] = 'files';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('FormName', $FormName);
				$this->set('reportnumber', $reportnumbers);
				$this->set('reportfiles', $reportfiles);

			} else {
				$this->Session->setFlash(__('The file could not be deleted. Please try again.'));
		 		$reportfiles = $this->Reportfile->find('first', array('conditions' => array('Reportfile.id' => $reportID)));

				$this->request->data = $reportfiles;

		 		$this->set('reportnumber', $reportnumbers);
		 		$this->set('reportfiles', $reportfiles);
			}

		} else {
//			$this->Session->setFlash(__('The file does not exist. Will you delete the database record?'));
	 		$reportfiles = $this->Reportfile->find('first', array('conditions' => array('Reportfile.id' => $id)));

			$this->request->data = $reportfiles;

	 		$this->set('reportnumber', $reportnumbers);
	 		$this->set('reportfiles', $reportfiles);
		}

	 }

	 public function status1() {

	 	$controller = $this->request->data['controller'];
	 	$action = $this->request->data['action'];

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

		if($this->Autorisierung->IsThisMyReport($id) === false){

			$this->autoRender = false;
			return;
			
		} 

	 	// aktuellen Status abrufen
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
		$errors = array();
		$errors = $this->errors();

	 	// Wenn der Status gröÃŸer als 1 ist, ist ein Fehler aufgetreten
	 	// Die Funktion bricht hier ab
	 	if($reportnumbers['Reportnumber']['status'] > 0){
	 		$this->Autorisierung->Logger($id,array('Error' => 'Status ist größer als 0, Aktion wird abgebrochen.'));
			$this->autoRender = false;
	 		return;
	 	}

	 	$status = 1;
	 	$value = __('The Report was closed.', true);
	 	$class = "close";
	 	$toggleclass = "closed1";
	 	$disabled = "disabled ";

	 	// Array mit Updatedaten erstellen
	 	$updateArray['Reportnumber']['id'] = $id;
	 	$updateArray['Reportnumber']['status'] = $status;

		if(is_countable($errors)){
			if(count($errors) > 0) $this->set('errors',$errors);
			if(count($errors) == 0) {

				if($this->Reportnumber->save($updateArray)){

					$this->Autorisierung->Logger($id,$updateArray);

					if($status != 1){
						$this->Reportnumber->Order->updateAll(
							array('Order.status' => 0),
							array('Order.id' => $reportnumbers['Order']['id'])
						);
					}
				} else {
					$value = __('The status could not be changed', true);
					$class = "error";
				}
			}
		} else {
			$value = __('The status could not be changed', true);
			$class = "error";
		}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = -1;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
	 	$this->request->data = $reportnumbers;

	 	$thisAction = 'status2';
	 	$thisClass = 'closed1 modalsmall';
	 	$thisTitle = __('Close this Report', true);

	 	$FormName = array('controller' => 'reportnumbers', 'action' => 'view');
		$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

	 	$this->set('reportnumberID', $reportnumbers['Reportnumber']['id']);
	 	$this->set('evalutionID', 0);
	 	$this->set('FormName', $FormName);
	 	$this->set('SettingsArray', array());
	 	$this->set('controller', $controller);
	 	$this->set('action', $action);
	 	$this->set('thisAction', $thisAction);
	 	$this->set('thisClass', $thisClass);
	 	$this->set('thisTitle', $thisTitle);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('disabled', $disabled);
	 	$this->set('class', $class);
	 	$this->set('toggleclass', $toggleclass);
	 	$this->set('value', $value);


	 	$this->render('status');
	 }

	 public function status2() {

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$controller = $this->request->data['controller'];
	 	$action = $this->request->data['action'];

	 	if(isset($this->request->data['Reportnumber']['id'])){

	 		$id = $this->Sicherheit->Numeric($this->request->data['Reportnumber']['id']);

	 		$this->Reportnumber->recursive = 0;
	 		$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

	 		if(isset($this->request->data['Reportnumber']['status']) && $this->request->data['Reportnumber']['status'] != ''){
	 			$status = $this->Sicherheit->Numeric($this->request->data['Reportnumber']['status']);


	 			if($status == 1){
	 				$this->request->data['Reportnumber']['status'] = 2;
	 			}
	 			if($status == 2){
	 				$this->request->data['Reportnumber']['status'] = 1;
	 			}
	 		}

	 		else {
	 			$this->request->data['Reportnumber']['status'] = 0;
	 		}

			$this->request->data['Reportnumber']['print'] = 0;

	 		// Wenn Abrechnungen zurückgenommen werden müssem
	 		$this->Data->RollbackInvoice($reportnumbers);

	 		if($this->Reportnumber->save($this->request->data)){

	 			$this->Autorisierung->Logger($id,$this->request->data);

	 			$FormName = array('controller' => $controller, 'action' => $action);
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

	 			$this->set('reportnumberID', $reportnumbers['Reportnumber']['id']);
	 			$this->set('evalutionID', 0);
	 			$this->set('FormName', $FormName);

	 			/*
	 			 if($status == 1){
	 			 }
	 			 else {
	 			 $this->Reportnumber->Order->updateAll(
	 			 array('Order.status' => 0),
	 			 array('Order.id' => $reportnumbers['Order']['id'])
	 			 );

	 			 }
	 			 */
	 		}
	 		else {
	 			$value = __('The status could not be changed', true);
	 			$class = "error";
	 		}
	 	}

	 	// aktuellen Status abrufen
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
		$this->Data->Archiv($reportnumbers['Reportnumber']['id'],$Verfahren);

/*
	 	foreach($reportnumbers as $key=>$report) {
	 		$Verfahren = ucfirst($report['Testingmethod']['value']);
	 		$this->Data->Archiv($report['Reportnumber']['id'],$Verfahren);

	 	}
*/
	 	$supervisor_text = $reportnumbers['Reportnumber']['status'] < 2
	 	? __('grant supervision release', true)
	 	: __('revoke supervision release', true);

	 	$this->set('supervisor_text', $supervisor_text);
	 	$this->set('SettingsArray', array());
	 	$this->request->data = $reportnumbers;

	 	$this->request->data['controller'] = $controller;
	 	$this->request->data['action'] = $action;


	 }

	 public function statustime() {

	 	$this->layout = 'modal';
	 }


	 public function delete($id = null) {

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Reportnumber->id = $id;
	 	if (!$this->Reportnumber->exists()) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$this->Reportnumber->recursive = 0;
	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$report = $this->Reportnumber->find('first', $options);

	 	// Wenn Abrechnungen zurückgenommen werden müssem
	 	$this->Data->RollbackInvoice($report);
	 	//		$verfahren = $report['Testingmethod']['value'];
	 	//		$Verfahren = ucfirst($report['Testingmethod']['value']);

	 	// Wert für Delete wird auf gelöscht gestellt
	 	unset($report['Reportnumber']['created']);
	 	unset($report['Reportnumber']['modified']);
	 	$report['Reportnumber']['delete'] = 1;

	 	$verfahren = $report['Testingmethod']['value'];
	 	$Verfahren = ucfirst($report['Testingmethod']['value']);

	 	if($this->Reportnumber->save($report)){
	 		$this->Reportnumber->ExaminerTime->recursive = -1;
	 		$workloads = $this->Reportnumber->ExaminerTime->find('all', array('conditions'=>array('collision REGEXP'=>'"link":"[^"]+'.$report['Reportnumber']['id'].'"')));
	 		$workloads = array_map(function($elem) {unset($elem['ExaminerTime']['collision']); $elem['ExaminerTime']['force_save'] = 1; return $elem;}, $workloads);

	 		$this->Reportnumber->ExaminerTime->saveMany($workloads);

	 		$this->Autorisierung->Logger($id,$report['Reportnumber']);
	 		$this->Data->Archiv($this->request->reportnumberID,$Verfahren);
	 		$this->Session->setFlash(__('Report deleted'));
	 	}
	 	else {
	 		$this->Session->setFlash(__('Report was not deleted'));
	 	}

	 	$this->set('bread', null);
	 	$this->set('afterEDIT', $this->Navigation->afterEDIT('reportnumbers/show/'.$projectID.'/'.$cascadeID.'/'.$orderID.'/'.$reportID,0));
	 }

	 public function deleteevalution($id = null) {


		 		$projectID = $this->request->projectvars['VarsArray'][0];
		 		$cascadeID = $this->request->projectvars['VarsArray'][1];
		 		$orderID = $this->request->projectvars['VarsArray'][2];
		 		$reportID = $this->request->projectvars['VarsArray'][3];
		 	 	$id = $this->request->projectvars['VarsArray'][4];
		 	 	$this->Autorisierung->IsThisMyReport($id);

		 	 	if (!$this->Reportnumber->exists($id)) {
		 	 		throw new NotFoundException(__('Invalid reportnumber'));
		 	 	}

		 	 	if(isset($this->request->projectvars['VarsArray'][5]) && $this->request->projectvars['VarsArray'][5] != 0) {
		 	 		$evalId = $this->request->projectvars['VarsArray'][5];
		 	 	}
		 	 	else {
		 	 		$evalId = null;
		 	 	}

		 	 	// Wenn die gesamte Naht bearbeitet werden soll
		 	 	if(isset($this->request->projectvars['VarsArray'][6]) && $this->request->projectvars['VarsArray'][6] == 1){
		 	 		$weldedit = 1;
		 	 	} else {
		 	 		$weldedit = 0;
		 	 		$this->request->projectvars['VarsArray'][6] = 0;
		 	 		$this->request->projectvars['weldedit'] = 0;
		 	 	}

		 		if($this->request->projectvars['VarsArray'][6] == 0 && $this->request->projectvars['VarsArray'][5] == 0){
		 			$weldedit = 1;
		 	 		$this->request->projectvars['VarsArray'][6] = 1;
		 	 		$this->request->projectvars['weldedit'] = 1;

		 		}
		 	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
		 	 	$this->Reportnumber->recursive = 0;
		 	 	$reportnumbers = $this->Reportnumber->find('first', $options);
		 		$reportnumbers = $this->Data->StatusTest($reportnumbers);
		 		$reportnumbers = $this->Report->SetModelNames($reportnumbers);

		 		$verfahren = $this->request->verfahren;
		 	 	$Verfahren = $this->request->Verfahren;
		 		$reportnumbers['xml'] = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
		 		$reportnumbers = $this->Report->GetReportData($reportnumbers,array('Evaluation'));
//		 		$reportnumbers = $this->Report->GetWeldPagination($reportnumbers,$weldedit);
//		 		$reportnumbers = $this->Report->WeldEdit($reportnumbers,$weldedit);
		 		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
		 		$reportnumbers = $this->Report->CheckRevisionValues($reportnumbers);
				$reportnumbers = $this->Report->StatusTest($reportnumbers);

				if(isset($reportnumber['FlashMessages']) && count($reportnumber['FlashMessages']) > 0){
			 		$this->set('request', json_encode($reportnumber));
			 		$this->render();
			 		return;
			 	}

				$reportnumbers = $this->Report->DeleteEvalution($reportnumbers);
				$reportnumbers = $this->Report->GetWeldPagination($reportnumbers,$weldedit);
				$reportnumbers = $this->Report->WeldEdit($reportnumbers,$weldedit);
				$reportnumbers = $this->Report->FunctionsUrlUpdate($reportnumbers);

				$DelArray = array('Reportnumber','Topproject','Order','Report','User','EquipmentType','Equipment','Testingcomp','Tablenames','CurrentTables','TablesInUse','orginal_data','xml');
				$reportnumbers = $this->SaveData->DeleteElements($reportnumbers,$DelArray);

/*
	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	if(isset($this->request->projectvars['VarsArray'][6]) && $this->request->projectvars['VarsArray'][6] == 1){
	 		$welddelete = 1;
	 	}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$report = $this->Reportnumber->find('first', $options);

	 	$report = $this->Data->RevisionCheckTime($report);
		$attribut_disabled = false;

	 	// Statustest
	 	if(
	 			$report['Reportnumber']['status'] > 0 ||
	 			$report['Reportnumber']['status'] > 0 ||
	 			$report['Reportnumber']['deactive'] > 0 ||
	 			$report['Reportnumber']['settled'] > 0 ||
	 			$report['Reportnumber']['delete'] > 0
	 			){
					$attribut_disabled = true;
				}

		if(isset($report['Reportnumber']['revision_write']) && $report['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

	 	if($attribut_disabled === true){

	 				$this->Session->setFlash(__('The Testingreport was closed.'));
	 				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->Session->read('lastURL'),2000));

	 				$this->set('evalutionID', 0);
	 				$this->set('FormName', array('controller' => 'reportnumbers', 'action' => 'edit'));
	 				$this->set('breads', null);
	 				$this->render();
	 				$this->response->send();
	 				$this->_stop();
	 	}

	 	$Verfahren = ucfirst($report['Testingmethod']['value']);
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 	$this->loadModel($ReportEvaluation);
	 	$optionsEvalution = array('conditions' => array('reportnumber_id' => $id));
	 	$evalutions = $this->$ReportEvaluation->find('all', $optionsEvalution);

		// diese Array wird der Revison übergeben, wenn nötig
		$TransportArray['model'] = $ReportEvaluation;
		$TransportArray['row'] = null;
		$TransportArray['this_value'] = null;
		$TransportArray['last_id_for_radio'] = 0;
		$TransportArray['delete'] = 1;

	 	// zuerst wird die zu löschende Nahtbezeichnung geholt
	 	$options = array('conditions' => array($ReportEvaluation.'.id' => $evalutionId));
	 	$delEvalution = $this->$ReportEvaluation->find('first', $options);

	 	// dann alle Prüfabschnitte der zu löschenden Naht
	 	$deleteWeld = $delEvalution[$ReportEvaluation]['description'];

	 	$options = array('conditions' => array(
	 			$ReportEvaluation.'.reportnumber_id' => $id,
	 			$ReportEvaluation.'.description' => $deleteWeld
		 	)
	 	);

	 	$evalutionAllId = $this->$ReportEvaluation->find('list', $options);

		$id_for_revision = array($evalutionId);
		$action_for_revision = 'deleteevalution/weld_area';

	 	// Wenn eine ganze Naht gelöscht werden soll
	 	if(isset($welddelete) && $welddelete == 1){
	 		$evalutionId = $evalutionAllId;
			$id_for_revision = $evalutionAllId;
			$action_for_revision = 'deleteevalution/weld';
	 	}

		if(isset($welddelete) && $welddelete == 1) $LastRecord = $this->$ReportEvaluation->find('all',array('conditions' => array($ReportEvaluation . '.id' => $evalutionAllId)));
		else $LastRecord = $this->$ReportEvaluation->find('all',array('conditions' => array($ReportEvaluation . '.id' => $evalutionId)));

		if(isset($welddelete) && $welddelete == 1) $deletOk = $this->$ReportEvaluation->deleteAll(array($ReportEvaluation.'.id' => $evalutionAllId), false);
		else $deletOk = $this->$ReportEvaluation->deleteAll(array($ReportEvaluation.'.id' => $evalutionId), false);

	 	if($deletOk == 1){

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

	 	}
	 	elseif($deletOk == null){
	 		$this->Session->setFlash(__('Evalution was not deleted'));
	 	}
*/
		$this->layout = 'json';
		$this->set('request',json_encode($reportnumbers));

	 }

	 public function editableUp() {

		$this->_editableUp();
		$this->_editableUpJson();
		$this->_editableUpAdditions();

//		if(isset($this->request->data['model']))
//		if(isset($this->request->data['json_true'])) $this->_editableUpJson();
	 }

	 protected function _editableUpJson() {

		if(!isset($this->request->data['json_true'])) return;

		$this->layout = 'json';

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$id = $this->request->projectvars['VarsArray'][4];
		$evalId = $this->request->projectvars['VarsArray'][5];
		$weldedit = $this->request->projectvars['VarsArray'][6];

		if(isset($this->request->data['Class'])) $classes = explode(' ',trim($this->request->data['Class']));
		else $classes = array();

		$Updatetype = '';

		if(!empty($classes)){
			$Updatetype = $classes[1];
		}

		if (!$this->Reportnumber->exists($id)) {
			$Json['Message'] = 'error';
			$this->set('value',json_encode(	$Json));
	 		$this->render('editable');
	 		return;
		}

		$this->Autorisierung->IsThisMyReport($id);

		$this->Reportnumber->recursive = 0;
	 	$data['reportnumber'] = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));

		$Test = $this->Report->CheckReportStatusBeforSave($data['reportnumber']);

		if($Test === false){
			$Json['Message'] = 'error';
			$this->set('value',json_encode($Json));
	 		$this->render('editable');
	 		return;
		}

		$DataId = $this->Report->DataIdForEditable();

		unset($this->request->data['Class']);
		unset($this->request->data['json_true']);
		unset($this->request->data['ajax_true']);

		$Model = key($this->request->data);

		if(!isset($this->request->data[$Model]['id'])){
			$Json['Message'] = 'error';
			$this->set('value',json_encode($Json));
	 		$this->render('editable');
	 		return;
		}

		$Field = key($this->request->data[$Model]);

		$this->loadModel($Model);

		$Check = $this->Report->CheckWelddescriptionBeforSave($data['reportnumber']);

		if($Check === false){

			$CurrentDescription = $this->{$Model}->find('first',array(
				'fields' => array('description'),
				'conditions' => array(
						$Model . '.id' => $DataId,
					)
				)
			);

			$Json['Message'] = 'error';
			$Json['Messagetext'] = __('This weld description already exists.',true);
			$Json['Results'] = $DataId;
			$Json['Result'] = $this->request->data[$Model]['id'];
			$Json['Field'] = $Field;
			$Json['Model'] = $Model;
			$Json['Value'] = $CurrentDescription[$Model]['description'];
			$Json['Class'] = 'col_' . $Field;
			$Json['Classes'] = $classes;
			$Json['Updatetype'] = 'editwelddescription';

			$this->set('value',json_encode($Json));

			$this->render('editable');
			return;
		}

		if($Field == 'description') $Json['Result'] = $this->request->data[$Model]['id'];

		$Check = $this->Report->CheckWeldpositionBeforSave($data['reportnumber']);

		if($Check === false){

			$CurrentPosition = $this->{$Model}->find('first',array(
//				'fields' => array('position'),
				'conditions' => array(
						$Model . '.id' => $this->request->data[$Model]['id'],
					)
				)
			);

			$Json['Message'] = 'error';
			$Json['Messagetext'] = __('This weld position description already exists.',true);
			$Json['Results'] = $DataId;
			$Json['Result'] = $this->request->data[$Model]['id'];
			$Json['Field'] = $Field;
			$Json['Model'] = $Model;
			$Json['Value'] = $CurrentPosition[$Model]['position'];
			$Json['Class'] = 'col_' . $Field;
			$Json['Classes'] = $classes;
			$Json['Updatetype'] = 'editweldposition';

			$this->set('value',json_encode($Json));

			$this->render('editable');
			return;
		}

		if($Field == 'position') $Json['Result'] = $this->request->data[$Model]['id'];

		$Ids = $this->request->data[$Model]['id'];


		unset($this->request->data[$Model]['id']);

		$Schema = $this->$Model->schema();

		if(!isset($Schema[$Field])){
			$Json['Message'] = 'error';
			$this->set('value',json_encode($Json));
	 		$this->render('editable');
	 		return;
		}

		$data['reportnumber']['ReportModel'] = key($this->request->data);

		$data['reportnumber']['this_id'] = $data['reportnumber']['ReportModel'] . Inflector::camelize(key($this->request->data[$data['reportnumber']['ReportModel']]));

		$ValueForSave = $this->request->data[$Model][$Field];

		$ValueForSave = $this->Report->CheckErrorCode($ValueForSave);

		if($ValueForSave === false){

			$CurrentPosition = $this->{$Model}->find('first',array(
				'conditions' => array(
					$Model . '.id' => $Ids,
					)
				)
			);
				
			$Json['Message'] = 'error';
			$Json['Results'] = $DataId;
			$Json['Field'] = $Field;
			$Json['Model'] = $Model;
			$Json['Updatetype'] = $Updatetype;
			$Json['Value'] = $CurrentPosition[$Model][$Field];

			$this->set('value',json_encode($Json));
	 		$this->render('editable');
	 		return;
		}

		foreach ($DataId as $key => $value) {

			$Find = $this->$Model->find('first',array('conditions' => array($Model . '.id' => $value)));
			$Save = array('id' => $value, $Field => $ValueForSave);

			if($this->$Model->save($Save)){

				$Reportnumber = $data['reportnumber'];
				
				$Reportnumber['last_value'] = $Find[$Model][$Field];
				$Reportnumber['this_value'] = $ValueForSave;
				$Reportnumber['last_id_for_radio'] = 0;
				$Reportnumber['aktion_for_revision'] = 'editevaluation';

				$Reportnumber = $this->SaveData->TransportArrayOne($Reportnumber);

				$Reportnumber['TransportArray']['table_id'] = $value;

				$Reportnumber = $this->SaveData->SaveRevision($Reportnumber);
		

			} else {
				
				$Json['Message'] = 'error';
				$this->set('value',json_encode($Json));
		 		$this->render('editable');
		 		return;

			}
		}

		$Value = trim($ValueForSave);

		if(strlen($Value) == 0) $Value = 'Edit';

		$Json['Results'] = $DataId;
		$Json['Field'] = $Field;
		$Json['Model'] = $Model;
		$Json['Value'] = $Value;
		$Json['Class'] = 'col_' . $Field;
		$Json['Classes'] = $classes;
		$Json['Updatetype'] = $Updatetype;

		$Test = $this->Report->UpdateReportRepairStatus($Model);

		$reportdata = array(
			'id' => $id,
			'modified_user_id' => $this->Auth->user('id')
		);


		$this->Reportnumber->save($reportdata);

		$this->Autorisierung->Logger($id,$this->request->data[$Model]);

		$Json['Message'] = 'success';

		if($Field == 'description') $Json['Updatetype'] = 'editwelddescription';
		if($Field == 'position') $Json['Updatetype'] = 'editweldposition';

		$Check = array_search('editableradio', $classes);

		if($Check !== false){

			$xml = $this->Xml->LoadXmlFileForReport($data['reportnumber']);

			$fielddata = json_encode($xml['settings']->{$Model}->{$Field}->radiooption->value);
			$fielddata = json_decode($fielddata,true);

			$Json['Value'] = $fielddata[$Value];

		}

		$this->set('value',json_encode($Json));
		$this->render('editable');

		return;
	 }

	 protected function _editableUpAdditions() {

		if(!isset($this->request->data['model'])) return;

	 	$this->layout = 'csv';
	 	$this->autoRender = false;

		App::uses('Sanitize', 'Utility');

		$id = intval($this->request->data['report_number']);
		$value = $this->request->data['value'];
		$additional = Sanitize::clean($this->request->data['additional']);
		$model = Sanitize::clean($this->request->data['model']);
		$field = Sanitize::clean($this->request->data['id']);

	 	$this->Reportnumber->recursive = -1;

		$Reportnumber = $this->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id'=>$id)));

		if($this->Autorisierung->AllTestForReportSaving($Reportnumber) === false){
			$this->set('value',$value);
			$this->render('editable');
		}

		$reportnumber_id = $Reportnumber['Reportnumber']['id'];

		$this->set('value','Error');

		if(!$this->loadModel($model)) return;
		$schema = $this->$model->schema();
		if(!isset($schema[$field])) return;

		$ModelData = $this->$model->find('first',array('conditions'=>array($model.'.reportnumber_id'=>$reportnumber_id)));

//		$value = $this->Formating->ValueForSQL($schema[$field],$value);

		if($ModelData[$model][$field] != $value && $value !== false){

			$InsertData = array('id' => $ModelData[$model]['id'],$field => $value);

			$this->$model->save($InsertData);
			$this->Additions->Logger($reportnumber_id, $InsertData);
		}

		if($value === false){
			$ModelData = $this->$model->find('first',array('conditions'=>array($model.'.reportnumber_id'=>$reportnumber_id)));
			$value = $ModelData[$model][$field];

		}

		$value = $this->Formating->ValueForView($schema[$field],$value);

		$this->set('value',$value);
		$this->render('editable');
	 }

	 protected function _editableUp() {

		if(isset($this->request->data['json_true'])) return;
		if(isset($this->request->data['model'])) return;

		App::uses('Sanitize', 'Utility');

	 	$this->layout = 'blank';
	 	$this->autoRender = false;

	 	$reportnumber = null;
	 	$testingmethod = null;

	 	if(isset($this->request->data['value'])) $this->request->data = array($this->request->data);

	 	// Sortierung muss geloggt werden, nachdem alle Einträge verarbeitet wurden
	 	$isSorting = false;
	 	$ReportEvaluation = null;

	 	foreach(array_intersect_key($this->request->data, array_flip(array_filter(array_keys($this->request->data),'is_numeric'))) as $data) {

	 		$value = $data['value'];
			$value = Sanitize::stripAll($value);

	 		$reportId = $this->Sicherheit->Numeric($data['report_number']);
	 		$arrayId = explode('_',$data['id']);
	 		$isSorting |= $arrayId[1] == 'sorting';

	 		//settype($arrayId[2], "integer");
	 		$id = $this->Sicherheit->Numeric(end($arrayId));

	 		$this->Reportnumber->recursive = -1;
	 		if($reportnumber == null) {
	 			// Daten festlegen, die für das Archivieren nnötig sind
	 			$reportnumber = $this->Reportnumber->find('first', array('conditions' => array('id' => $reportId)));

	 			$reportnumber = $this->Data->RevisionCheckTime($reportnumber);

	 			$this->request->params['pass'] = array(
	 					$reportnumber['Reportnumber']['topproject_id'],
	 					$reportnumber['Reportnumber']['cascade_id'],
	 					$reportnumber['Reportnumber']['order_id'],
	 					$reportnumber['Reportnumber']['report_id'],
	 					$reportnumber['Reportnumber']['id'],
	 					end($arrayId)
	 			);

	 			$this->Navigation->ReportVars();
	 		}

	 		$this->Reportnumber->Testingmethod->recursive = -1;
	 		if($testingmethod == null) $testingmethod = $this->Reportnumber->Testingmethod->find('first', array('conditions' => array('id' => $reportnumber['Reportnumber']['testingmethod_id'])));
	 		$verfahren = $testingmethod['Testingmethod']['value'];
	 		$Verfahren = ucfirst($testingmethod['Testingmethod']['value']);

	 		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 		$this->loadModel($ReportEvaluation);
	 		$Report = $this->$ReportEvaluation->find('first', array('conditions' => array('id' => $id)));

	 		$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 		$MiddleValue = 'MiddleValue'.$Verfahren;

			if(!method_exists($this->Data, $MiddleValue)) $MiddleValue = 'MiddleValueGeneral';

	 		$dataArray = $this->Data->$MiddleValue($id,$ReportEvaluation,$Report,$arrayId,$value,$arrayData,$reportnumber);
/*
			$evalne = $this->$ReportEvaluation->find('all',array(
				'conditions' => array(
					$ReportEvaluation.'.reportnumber_id' => $reportId,
					$ReportEvaluation.'.deleted'=>0,
					$ReportEvaluation.'.result'=>2
					)
				)
			);

			if(isset($evalne) && !empty($evalne)) $reportdata = array('id' => $reportId,'modified_user_id' => $this->Auth->user('id'),'result' => 2);
			else $reportdata = array('id' => $reportId,'modified_user_id' => $this->Auth->user('id'),'result' => 1);

//			$this->Reportnumber->save($reportdata);
*/
	 		if(count($this->request->data) > 1) continue;
	 		$this->render('editable');
	 	}

	 	if($isSorting) {
	 		$fields = array('description');
	 		switch($testingmethod['Testingmethod']['value']) {
	 			case 'ht':
	 			case 'ht1':
	 				$fields = array('description', 'position', 'comment');
	 				break;

	 			case 'rt':
	 				$fields = array('description', 'position');
	 		}

	 		$sorting = array_map(
	 				function($elem) {
	 					return is_array($elem) ? join(' ', reset($elem)) : $elem;
	 				},
	 				$this->$ReportEvaluation->find('all', array(
	 						'fields'=> $fields,
	 						'order'=>array('sorting'=>'asc'),
	 						'conditions'=>array('reportnumber_id'=>$this->request->data[0]['report_number'])
	 				))
	 				);

	 		$this->Autorisierung->Logger($this->request->data[0]['report_number'], array('sorting'=>$sorting));
	 	}

		$data = $this->request->data;

		if(isset($data['json_true'])) unset($data['json_true']);
		if(isset($data['ajax_true'])) unset($data['ajax_true']);
		if(isset($data['Class'])) unset($data['Class']);

		$VerfahrenUnderArray = explode('_',Inflector::underscore($ReportEvaluation));
		$Verfahren = Inflector::camelize($VerfahrenUnderArray[1]);

	 	$this->Data->Archiv($this->request->reportnumberID,$Verfahren);
	 }

	 public function history($id = null) {

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0] ?? -1;
	 	$cascadeID = $this->request->projectvars['VarsArray'][1] ?? -1;
	 	$orderID = $this->request->projectvars['VarsArray'][2] ?? -1;
	 	$reportID = $this->request->projectvars['VarsArray'][3] ?? -1;
	 	$id = $this->request->projectvars['VarsArray'][4] ?? -1;

		$this->Autorisierung->IsThisMyReport($id);

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

		$this->loadModel('Log');
		$this->Log->recursive = 0;

		$options = array(
			'order' => array('Log.id DESC'),
			'conditions'=>array(
				'Log.controller' => array(strtolower($this->name), 'users','templates'),
				'Log.current_id' => $id
			)
		);

		$logs = $this->Log->find('all', $options);
		$lang = $this->Lang->Discription();
		$output = $this->Data->HistoryData($logs,$lang);
		$logs = $output['logs'];
		$arrayData = $output['arrayData'];

	 	// XML-Daten laden
	 	// History laden
	 	$this->set('settings', $arrayData);
	 	$this->set('logs', $logs);
//	 	$this->render(null);
	 }

	 public function settings() {

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$menues = array();
/*
	 	$menues = array(
	 			'reports' => array(
	 					'haedline' => null,
	 					'controller' => 'dropdowns',
	 					'discription' => 'Dropdowns auflisten',
	 					'actions' => 'index',
	 					'params' => join('/', $this->request->projectvars['VarsArray'])//$projectID
	 			)
	 	);
*/
	 	$this->set('SettingsArray', null);
	 	$this->set('menues', $menues);
	 	$this->render('/Topprojects/settings');
	 }

	 public function en1435() {
	 	$this->layout = 'modal';
	 	$this->set('SettingsArray', null);
	 	$this->set('En1435', $this->Data->En1435());
	 	$this->set('SettingsArray', array());
	 }

	 public function editsession() {
	 	$this->layout = 'blank';
	 	if(isset($this->request->data['id']) && isset($this->request->data['editmenue'])){
	 		$this->Session->write('editmenue',array($this->request->data['id'] => $this->request->data['editmenue']));
	 	}
	 	$this->autoRender = false;
	 }

	 public function showqrcode() {
	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	if (!$this->Reportnumber->exists($id)) {
	 		throw new NotFoundException(__('Invalid reportnumber'));
	 	}

	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);

	 	// verschieden Varablen bereitstellen
	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
	 	$verfahren = $reportnumbers['Testingmethod']['value'];


	 	if(isset($this->request->projectvars['evalId']) && $this->request->projectvars['evalId'] > 0){

	 		$evalId = $this->request->projectvars['evalId'];
	 		$weldedit = $this->request->projectvars['weldedit'];

	 		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	 		$this->loadModel($ReportEvaluation);

	 		$optionsWeld =	array(
	 				'fields' => array('description'),
	 				'conditions' =>	array(
	 						$ReportEvaluation.'.id' => $evalId,
	 						$ReportEvaluation.'.reportnumber_id' => $id,
	 						$ReportEvaluation.'.deleted' => 0
	 				)
	 		);

	 		// Die Nahtbezeichnung wird anhand er id geholt
	 		// falls die Nahtbezeichnug geändert wurde
	 		$diskriptionWeld = $this->$ReportEvaluation->find('first',$optionsWeld);

	 		// neuen Conditions um alle ids der Naht zu holen
	 		$optionsWeld =	array(
	 				'conditions' =>	array(
	 						$ReportEvaluation.'.description' => $diskriptionWeld[$ReportEvaluation]['description'],
	 						$ReportEvaluation.'.reportnumber_id' => $id,
	 						$ReportEvaluation.'.deleted' => 0
	 				)
	 		);


	 		$idsWeld = $this->$ReportEvaluation->find('all',$optionsWeld);
	 		$this->set('welds', $idsWeld);

	 	}

		$key = $this->Sicherheit->SoduimEncrypt($reportnumbers['Reportnumber']['id']);

	 	$settings = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
	 	$this->set('SettingsArray', array());
	 	$this->set('settings', $settings['settings']);
	 	$this->set('ReportEvaluation', 'Report'.$Verfahren.'Evaluation');
	 	$this->set('ReportPdf', 'Report'.$Verfahren.'Pdf');
	 	$this->set('reportnumbers', $reportnumbers);
	 	$this->set('key', $key);
	 }

	 protected function __signMember($type) {

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

	 	$reportnumbers = $this->Data->GetReportData($reportnumbers,array('Generally','Specific','Evaluation'));

	 	$verfahren = $this->request->verfahren;
	 	$Verfahren = $this->request->Verfahren ;

	 	$ReportGenerally = $this->request->tablenames[0];
	 	$ReportSpecific = $this->request->tablenames[1];
	 	$ReportEvaluation = $this->request->tablenames[2];
	 	$ReportArchiv = $this->request->tablenames[3];
	 	$ReportSettings = $this->request->tablenames[4];
	 	$ReportPdf = $this->request->tablenames[5];

		$ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	// Daten für mögliche vorhandene Dropdownfelder und Multiselects holen
		$reportnumbers = $this->Drops->DropdownData($ReportArray,$arrayData,$reportnumbers);
 		$reportnumbers = $this->Data->MultiselectData($ReportArray,$arrayData,$reportnumbers);
 		$reportnumbers = $this->Data->ChangeDropdownData($reportnumbers, $arrayData, $ReportArray);
 		$reportnumbers = $this->Drops->CollectMasterDropdown($reportnumbers);
 		$reportnumbers = $this->Drops->CollectMasterDependency($reportnumbers);
 		$reportnumbers = $this->Drops->CollectModulDropdown($reportnumbers);
 		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);
 		$reportnumbers = $this->Data->SortAllDropdownArraysNatural($reportnumbers);

		$this->loadModel($ReportEvaluation);

		$evaluations = $this->$ReportEvaluation->find('all', array(
				'conditions' =>
					array(
						'reportnumber_id' => $id,
						'deleted' => 0
					)
				)
			);

		unset($reportnumbers[$ReportEvaluation]);

		$reportnumbers[$ReportEvaluation] = $evaluations;

		$unevaluated = array();
		foreach($reportnumbers[$ReportEvaluation] as $eval) {
			if(isset($eval[$ReportEvaluation]['result'])){
				if($eval[$ReportEvaluation]['result'] == '-') {
					$unevaluated[$eval[$ReportEvaluation]['description']] = $eval[$ReportEvaluation]['description'];
				}
			}
		}

		$xml = $this->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		$errors = $this->Reportnumber->getValidationErrors($xml, $reportnumbers, $verfahren);

		if(count($errors) > 0) $this->set('errors', $errors);

    	$allmails = array();

        foreach ($xml['settings']->children() as $skey => $svalue) {

			foreach($svalue as $sv_key => $_svalue){

				if ($_svalue->emailfield !=1) continue;

                $emails = explode(" ",$reportnumbers[$skey][$sv_key]);
                $allmails = array_merge($emails, $allmails);
            }

        }

		$allmails[] = $reportnumbers['Testingcomp']['report_email'];
        $allmails[] = $reportnumbers['Topproject']['email'];
        $allmails = array_unique($allmails);

        $this->set('allmails',$allmails);

		// nacheinander unterschreiben als Standard
		$SignatoryCascading = true;
		$SignatoryAfterPrinting = true;
		$SignatoryClosing = true;

		if(Configure::check('SignatoryCascading')) $SignatoryCascading = Configure::read('SignatoryCascading');
		if(Configure::check('SignatoryAfterPrinting')) $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
		if(Configure::check('SignatoryClosing')) $SignatoryClosing = Configure::read('SignatoryClosing');

	 	$this->loadModel('Sign');
		$Signtest = $this->Sign->find('all',array('conditions' => array('Sign.reportnumber_id' => $id)));

		switch ($type) {
			case 1:
			$sign_for_action = 'signExaminer';
			$sign_for = __('Examiner',true);
			if(count($Signtest) > 0 && $SignatoryCascading == true){
				$this->Session->setFlash(__('You can not sign this report again.',true));
			 	$this->set('stop_sign', true);
			}
			break;
			case 2:
			$sign_for_action = 'signSupervisor';
			$sign_for = __('Supervisor',true);
			if(count($Signtest) != 1 && $SignatoryCascading == true){
				$this->Session->setFlash(__('You can not sign this report.',true) . ' ' . __('A lower priority signature is missing.',true));
			 	$this->set('stop_sign', true);
			}
			break;
			case 3:
			$sign_for_action = 'signThirdPart';
			$sign_for = __('Third part',true);
			if(count($Signtest) != 2 && $SignatoryCascading == true){
				$this->Session->setFlash(__('You can not sign this report.',true) . ' ' . __('A lower priority signature is missing.',true));
			 	$this->set('stop_sign', true);
			}
			break;
			case 4:
			$sign_for_action = 'signFourPart';

			$sign_for = __('Four part',true);
			if(count($Signtest) != 2 && $SignatoryCascading == true){
				$this->Session->setFlash(__('You can not sign this report.',true) . ' ' . __('A lower priority signature is missing.',true));
			 	$this->set('stop_sign', true);
			}
			break;
		}

		$Sign = $this->Sign->find('first',array('conditions' => array('Sign.reportnumber_id' => $id,'Sign.signatory' => $type)));

	 	$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
	 	$this->request->verfahren = $Verfahren;
	 	$verfahren = $reportnumbers['Testingmethod']['value'];
	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

	 	$ReportSettings = 'Report'.$Verfahren.'Setting';
	 	$ReportPdf = 'Report'.$Verfahren.'Pdf';

	 	$this->loadModel($ReportSettings);
	 	$optionsSettings = array('conditions' => array($ReportSettings.'.reportnumber_id' => $id));
	 	$Settings = $this->$ReportSettings->find('first', $optionsSettings);

	 	$menue = $this->Navigation->NaviMenue(null);

	 	$breads = $this->Navigation->BreadForReport($reportnumbers,'sign');

		$breads[] = array(
						'discription' => __('Signature',true) . ' ' . $sign_for,
            			'controller' => 'reportnumbers',
            			'action' => $sign_for_action,
						'pass' => implode('/',$this->request->projectvars['VarsArray'])
						);

	 	$breads = $this->Navigation->SubdivisionBreads($breads);
		$ReportMenue = $this->Navigation->createReportMenue($reportnumbers,$arrayData['settings']);

		$this->Session->delete('Sign');
		$this->Session->write('Sign',null);
		$this->Session->write('Sign.Signatory',$type);

		$WriteSignatoryColor['blue']['r'] = 0;
		$WriteSignatoryColor['blue']['g'] = 0;
		$WriteSignatoryColor['blue']['b'] = 255;

		if(Configure::check('WriteSignatoryColor')){
			$WriteSignatoryColor = Configure::read('WriteSignatoryColor');
		}

	 	// Das Array für das Settingsmenü
	 	$SettingsArray = array();
	 	$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
	 	$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

	 	$this->set('SignatoryClosing', $SignatoryClosing);
	 	$this->set('SignatoryAfterPrinting', $SignatoryAfterPrinting);
	 	$this->set('SignatoryCascading', $SignatoryCascading);
	 	$this->set('WriteSignatoryColor', $WriteSignatoryColor);
	 	$this->set('ReportMenue', $ReportMenue);
	 	$this->set('settings', $Settings);
	 	$this->set('SettingsArray', $SettingsArray);
	 	$this->set('reportnumber', $reportnumbers);
	 	$this->set('breads', $breads);
	 	$this->set('menues', $menue);
	 	$this->set('sign_for', $sign_for);
		$this->set('generaloutput', $arrayData['generaloutput']);
		$this->set('signtype',$type);

		if(count($Sign) == 1){
			$this->Reportnumber->User->recursive = -1;
			$this->Reportnumber->Testingcomp->recursive = -1;
			$User = $this->Reportnumber->User->find('list',array('fields' => array('name'), 'conditions' => array('User.id' => $Sign['Sign']['user_id'])));
			$Testingcomp = $this->Reportnumber->Testingcomp->find('list', array('fields' => array('firmenname'), 'conditions' => array('Testingcomp.id' => $Sign['Sign']['testingcomp_id'])));

			$openImage = $this->Image->setSignImage($reportnumbers,$Sign,$type);
		 	$this->set('openImage', $openImage);

		 	$this->set('User', reset($User));
		 	$this->set('Testingcomp', reset($Testingcomp));
		 	$this->set('Sign', $Sign);
		 	$this->render('sign_show');
		} else {
		 	$this->render('sign_examiner_socket');
		}
	 }

	 public function signExaminer() {
		 $this->__signMember(1);
		 return;
	 }

	 public function signSupervisor() {
		 $this->__signMember(2);
		 return;
	 }

	 public function signThirdPart() {
		 $this->__signMember(3);

		 return;
	 }

	 public function signFourPart() {
		 $this->__signMember(4);
		 return;
	 }

	 public function sign() {
/*
		 $report_id_chiper = bin2hex(Security::cipher($this->request->projectvars['VarsArray'][4], Configure::read('SignatoryHash')));
 		$project_id_chiper = bin2hex(Security::cipher($this->request->projectvars['VarsArray'][0], Configure::read('SignatoryHash')));
 pr($project_id_chiper);
 pr($report_id_chiper);
*/
	 	$this->Data->SignReport();
	 }


	 public function signtransport() {

	 	$this->layout = 'blank';
		$id = $this->request->projectvars['VarsArray'][4];
		$this->Session->write('Sign.Id',$id);

		// nacheinander unterschreiben als Standard
		$SignatoryCascading = true;
		$SignatoryAfterPrinting = true;
		$SignatoryClosing = true;

		if(Configure::check('SignatoryCascading')) $SignatoryCascading = Configure::read('SignatoryCascading');
		if(Configure::check('SignatoryAfterPrinting')) $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
		if(Configure::check('SignatoryClosing')) $SignatoryClosing = Configure::read('SignatoryClosing');

		$color = 'rgb(0, 0, 0)';

		if(isset($this->request->data['signature'])) $this->Session->write('Sign.Signature',$this->request->data['signature']);
		if(isset($this->request->data['image'])) $this->Session->write('Sign.Image',$this->request->data['image']);
		if(isset($this->request->data['color']) && !empty($this->request->data['color']))  $this->Session->write('Sign.Color',$this->request->data['color']);

		$Signatory = $this->Session->read('Sign');

		if(count($Signatory) < 5){

			$Response['success'] = true;
			$this->set('Response',json_encode($Response));
			return;
		}

		$Response['success'] = true;
		$this->set('Response',json_encode($Response));

		$this->Reportnumber->recursive = -1;

		$Data = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $Signatory['Id'])));
		$Data = $this->Data->RevisionCheckTime($Data);

		$attribut_disabled = false;
		$currentstatus = $Data['Reportnumber']['status'];
		if($Data['Reportnumber']['status'] > 0) $attribut_disabled = true;

		$Data['Signatory'] = $Signatory;
		$Data = $this->Signing->CreateImage($Data);
		$Data = $this->Signing->CreateSign($Data);
		$Data = $this->Signing->SaveSign($Data);
		$Data = $this->Signing->UpdateReportnumberData($Data);
		$Data = $this->Signing->ConvertBase64($Data);

		$this->set('Response',json_encode($Data));

   }

	 public function removeSign() {

	 	$this->layout = 'modal';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

		// nacheinander unterschreiben als Standard
		$SignatoryCascading = true;
		$SignatoryAfterPrinting = true;
		$SignatoryClosing = true;

		if(Configure::check('SignatoryCascading')) $SignatoryCascading = Configure::read('SignatoryCascading');
		if(Configure::check('SignatoryAfterPrinting')) $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
		if(Configure::check('SignatoryClosing')) $SignatoryClosing = Configure::read('SignatoryClosing');

	 	if((isset($this->request['data']['remove_sign'])) && $this->request['data']['remove_sign'] == 1 && $this->request->is('post')) {

	 		$this->loadModel('Sign');

	 		if (!$this->Reportnumber->exists($id)) {
	 			throw new NotFoundException(__('Invalid reportnumber'));
	 		}

	 		$this->Autorisierung->IsThisMyReport($id);

	 		$this->Reportnumber->recursive = 0;
	 		$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
			$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

			$attribut_disabled = false;

//			if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
			if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

			if($attribut_disabled == true) die();

			$report_id_chiper = bin2hex(Security::cipher($reportnumbers['Reportnumber']['id'], Configure::read('SignatoryHash')));
			$project_id_chiper = bin2hex(Security::cipher($reportnumbers['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));
			$path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS;

	 		if($this->Sign->deleteAll(array('Sign.reportnumber_id' => $id), false)){

	 			unlink($path);
				$folder = new Folder($path);
				if($folder->delete())pr('gelöscht');
				else pr('nicht gelöscht');

	 			$this->Reportnumber->recursive = 0;
	 			$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

	 			$this->request->data['Reportnumber']['id'] = $id;

	 			if($SignatoryClosing == true) $this->request->data['Reportnumber']['status'] = 0;
//	 			if($SignatoryAfterPrinting == true) $this->request->data['Reportnumber']['print'] = 0;
	 			$this->request->data['Reportnumber']['print'] = 0;

	 			// Wenn Abrechnungen zurückgenommen werden müssem
	 			$this->Data->RollbackInvoice($reportnumbers);

	 			$this->Reportnumber->save($this->request->data);

	 			$this->Autorisierung->Logger($id,$this->request->data);

				// diese Array wird der Revison übergeben, wenn nötig
				$TransportArray['model'] = 'Sign';
				$TransportArray['row'] = null;
				$TransportArray['last_id_for_radio'] = null;
				$TransportArray['table_id'] = $id;
				$reportnumbers['Reportnumber'] ['revision_progress'] > 0 ? $this->Data->SaveRevision($reportnumbers,$TransportArray,'sign/remove'): '';

	 			$FormName = array('controller' => 'reportnumbers', 'action' => 'sign');
	 			$FormName['terms'] = $projectID.'/'.$cascadeID.'/'.$orderID.'/'.$reportID.'/'.$id;
	 			$this->set('FormName', $FormName);

	 			return false;
	 		}
	 	}
	 	else {
	 		$this->Data->RemoveSign('examiner');
	 	}
	 }

	 public function printresultcsv() {
		 $arrayData = $this->Xml->DataFromXml('display','file',null);

		 //$reports = $this->Session->read('search.testingreportresult');
		 $this->request->data = $this->Session->read('search.params');
		 $this->Reportnumber->recursive = 1;

		 $cond = $this->Session->read('SearchFormData');
		 $results = $cond ['Result'] ['Reports'];

		 $testingreports = $this->Reportnumber->find('all',array('conditions'=>array('Reportnumber.id' => $results)));
		 if ($this->Auth->user('roll_id')> 4 && AuthComponent::user('Testingcomp.extern') <> 1) {
			 $testingreports = $this->Reportnumber->find('all',array('conditions'=>array('Reportnumber.id' => $results,'Reportnumber.testingcomp_id'=>$this->Auth->user('testingcomp_id'))));
		 }

		 foreach($testingreports as $_id=>$_report) {
			 // Falls der Prüfbericht untergeordnet ist und die Nummer vom Eltern bericht erzwungen wird
			 $_report = $testingreports[$_id] = $this->Data->EnforceParentReportnumber($testingreports[$_id]);
			 if(isset($_report['Reportnumber']['_number'])) $testingreports[$_id]['Reportnumber']['number'] = $_report['Reportnumber']['_number'].' ('.$_report['Reportnumber']['number'].')';
		 }

		 foreach($testingreports as $id=>$report) {
			 $generally = 'Report'.ucfirst($report['Testingmethod']['value']).'Generally';
			 $evaluation = 'Report'.ucfirst($report['Testingmethod']['value']).'Evaluation';
			 $this->loadModel($generally);
			 $testingreports[$id] = array_merge($testingreports[$id], $this->$generally->find('first', array('conditions'=>array('reportnumber_id'=>$report['Reportnumber']['id']))));
			 $this->loadModel($evaluation);
			 $testingreports[$id][$evaluation] = $this->$evaluation->find('all', array('conditions'=>array('reportnumber_id'=>$report['Reportnumber']['id'])));

			 if($this->$evaluation->hasField('result')) {

				 $result_array = array(0,1,2);

				 foreach($result_array as $_result_array){
					 $result = $this->$evaluation->find('list',array(
						 'limit' => 1,
						 'fields' => array($evaluation.'.result'),
						 'conditions' => array(
							 $evaluation.'.reportnumber_id' => $report['Reportnumber']['id'],
							 $evaluation.'.result' => $_result_array,
							 $evaluation.'.deleted' => 0
						 )
					 )
				 );
				 $testingreports[$id]['weld_types'][$_result_array] = count($result);
			 }
		 } else {
			 $testingreports[$id]['weld_types'] = array();
		 }
	 }


	 $data[] = array('Suchergebnisse');
	 $data[] = array(' ');
	 $head = array();
	 $head[] = __('Number',true);

	 foreach($arrayData['settings']->section->children() as $key => $val){
		 if(!isset($val->csv)) continue;
		 if(!isset($val->description)) continue;

		 $description = trim($val->description);
		 $description = utf8_decode($description);
		 $head[] = __($description, true);

	 }
	 if(Configure::check('PrintFilmConsumeCsv')) $head[] = __('Rate of film consumption',true);
	 $head[] = __('Number of Welds');

	 $data[]= $head;
	 foreach ($testingreports as $reportkey => $report) {

		 $ReportTableNames = array(
			 'Archiv' => 'Report'.ucfirst($report['Testingmethod']['value']).'Archiv',
			 'Setting' => 'Report'.ucfirst($report['Testingmethod']['value']).'Setting',
			 'Generally' => 'Report'.ucfirst($report['Testingmethod']['value']).'Generally',
			 'Specific' => 'Report'.ucfirst($report['Testingmethod']['value']).'Specific',
			 'Evaluation' => 'Report'.ucfirst($report['Testingmethod']['value']).'Evaluation',
		 );
		 //    pr($report); die();
		 $reportnames = $this->Pdf->ConstructReportName($report);
		 //pr($reportnames);
		 $datas = array();
		 $testingmethod = ucfirst($report['Testingmethod']['value']);
		 $datarows = array();
		 $datarows[] = $reportnames;
		 foreach($arrayData['settings']->section->children() as $key => $val){
			 $model = '';
			 if(!isset($val->description)) continue;
			 if(!isset($val->csv)) continue;
			 if($val->model == "Generally" || $val->model == "Specific" || $val->model == "Evaluation") {
				 $model = 'Report'.$testingmethod.trim($val->model);
			 } else { $model = trim($val->model);}
			 $input = Hash::extract($report,$model.'.'.trim($val->key));
			 if($val->fieldtype == 'radio'){
				 $input[0] = $val->radiooption->value[$input[0]];
			 }
			 !empty($input)? $datarows[] = utf8_decode($input[0]):$datarows[] = '-';

			 //pr($datarows);
		 }
		 if(Configure::check('PrintFilmConsumeCsv') && $testingmethod =='Rt') {
			 $consume = $this->Pdf->RateOfFilmConsumptionCsv($report);
			 if(!empty($consume)) {$datarows[] = $consume;} else{$datarows[]= " ";}
		 }
		 // =$report[$ReportTableNames['Evaluation']];
		 $eves = $this->{$ReportTableNames['Evaluation']}->find('all',array(

			 'conditions' => array(
				 $ReportTableNames['Evaluation'].'.reportnumber_id' => $report['Reportnumber']['id'],
				 $ReportTableNames['Evaluation'].'.deleted' => 0
			 )
		 )
	 );
	 $countwelds = $this->Pdf->WeldCountWithDimension($eves,$ReportTableNames,$report);

	 $datarows[] = $countwelds;
	 $data[] = $datarows;
 }


 $this->Csv->exportCsv($data,'search.csv',60,';');
}

  public function formatadress() {

	     	$this->loadModel('Dropdown');
        $this->Dropdown->recursive = 2;
        $dd = $this->Dropdown->find('all',array('conditions'=>array('dropdownid LIKE'=> '%ReportRtGenerally0auftraggeber%')));

	}

	public function revision() {

		$this->layout = 'modal';

		$this->set('url',$this->Session->read('lastURL'));

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

	 	$this->Autorisierung->IsThisMyReport($id);
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));
		$verfahren = $reportnumber['Testingmethod']['value'];
		$Verfahren = ucfirst($verfahren);


		// Wenn die Prüfberichtsmappe gesperrt ist
	 	if($this->writeprotection) {
	 		$this->set('error', array(__('Report is writeprotected - changes will not be saved.')));
			$this->set('class','versionize');
	 		$this->render();
	 		return;
	 	}

		if($reportnumber['Reportnumber']['delete'] > 0){
	 		return;
		}

		if($reportnumber['Reportnumber']['deactive'] > 0){
	 		return;
		}

		$class = $this->Data->GetRevisionLinkClass($reportnumber);

	 	if(!isset($this->request->data['Reportnumber'])){

	 		$this->request->data = $reportnumber;

			if($class['class'] == 'versionize_progress'){
				$description['text'] = __('Soll die Revision des aktuellen Prüfberichts beendet werden?',true);
				$description['submit'] = __('Revision beenden',true);
				$description['reason']= 0;
			} else {
				$description['text'] = __('Soll von diesem Prüfbericht eine Revision erstellt werden?',true);
				$description['submit'] = __('Revision erstellen',true);
				$description['reason']= 1;
			  $this->Session->write('reportnumberstatus',$reportnumber['Reportnumber']['status']);
				$this->Session->write('reportnumberprint',$reportnumber['Reportnumber']['print']);
			}

			if(
				Configure::check('RevisionSignaturDelete') && Configure::read('RevisionSignaturDelete') == true &&
				Configure::check('WriteSignatory') && Configure::read('WriteSignatory') == true
				) {
				$description['text'] .= ' ' .  __('All existing signatures will be deleted.',true);
			}

			$this->set('class',$class);
			$this->set('description',$description);
			$this->render('revision_ask');
			return;
		}


	 	$this->layout = 'blank';

		$this->set('class',$class['class']);

		$this->loadModel('Revision');
		$Revision = $this->Revision->find('first',array('conditions' => array('Revision.revision' => $reportnumber['Reportnumber']['revision'],'Revision.reportnumber_id' => $reportnumber['Reportnumber']['id'])));

		if($this->Session->check('revision.' . $id) && $reportnumber['Reportnumber']['revision_progress'] == $this->Session->read('revision.' . $id)){

			if(count($Revision) == 0){
				--$reportnumber['Reportnumber']['revision'];
				$revisiondata['Reportnumber']['status'] = $this->Session->read('reportnumberstatus');
				$revisiondata['Reportnumber']['print'] = $this->Session->read('reportnumberprint');
			}

			$revisiondata['Reportnumber']['id'] = $id;
			$revisiondata['Reportnumber']['revision'] = $reportnumber['Reportnumber']['revision'];
			//$revisiondata['Reportnumber']['print'] = 0;
			$revisiondata['Reportnumber']['revision_progress'] = 0;

			$this->Reportnumber->save($revisiondata);
			$this->Session->delete('revision.' . $id);
			$this->Data->Archiv($reportnumber['Reportnumber']['id'],$Verfahren);

			$class['class'] = 'versionize';
			$class['title'] = __('Create a revision without duplicating');
			$this->set('class',$class['class']);
//			$this->set('class','versionize');
	 		$this->render();
	 		return;
		}

		if($reportnumber['Reportnumber']['revision_progress'] > 0 && !$this->Session->check('revision.' . $id)){

			if(time() - Configure::read('RevisionInReportTime') <= $reportnumber['Reportnumber']['revision_progress']) {
		 		$this->render();
				return;
			}
			if(time() - Configure::read('RevisionInReportTime') > $reportnumber['Reportnumber']['revision_progress']) {
				$current_time = time();
				$this->Session->write('revision.' . $id,$current_time);
				$revisiondata['Reportnumber']['id'] = $id;
				$revisiondata['Reportnumber']['revision_progress'] = $current_time;

				$this->Session->delete('revision' .$id.'reason');
				isset($this->request->data['Reportnumber'] ['reason']) &&!empty($this->request->data['Reportnumber'] ['reason'])?$this->Session->write('revision' .$id.'reason',$this->request->data['Reportnumber'] ['reason']):'';

				$this->Reportnumber->save($revisiondata);
				$this->Data->Archiv($reportnumber['Reportnumber']['id'],$Verfahren);
	 			$reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));
				$class = $this->Data->GetRevisionLinkClass($reportnumber);
				$this->set('class',$class['class']);
//				$this->set('class','versionize_progress');
	 			$this->render();
		 		return;
			}
		}

		if($reportnumber['Reportnumber']['revision_progress'] > 0){
			$this->set('error', array(__('This function is blocked by another user.')));
	 		$this->render();
//			$this->set('class','versionize_blocked');
	 		return;
		}

		$this->Data->RevisionDeleteSignatory($reportnumber);

		$current_time = time();
		$this->Session->write('revision.' . $id,$current_time);
		$this->Session->delete('revision' .$id.'reason');

		isset($this->request->data['Reportnumber'] ['reason']) && !empty($this->request->data['Reportnumber'] ['reason']) ?$this->Session->write('revision' . $id.'reason',$this->request->data['Reportnumber'] ['reason']):'';

		$revisiondata = array();

		$revisiondata['Reportnumber']['id'] = $id;
		$revisiondata['Reportnumber']['revision_progress'] = $current_time;
		$revisiondata['Reportnumber']['revision'] = $reportnumber['Reportnumber']['revision'] + 1;

		$this->Reportnumber->save($revisiondata);

		$this->Data->RevisionDeleteSignatory($reportnumber);

		$this->Data->Archiv($reportnumber['Reportnumber']['id'],$Verfahren);
		$class['class'] = 'versionize_progress';
		$class['title'] = __('Revision is in progress.');
		$this->Autorisierung->Logger($id);
		$this->set('class',$class['class']);
	}

	public function showrevisions() {

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$id = $this->request->projectvars['VarsArray'][4];

		if(isset($this->request->data['tooltip'])&& $this->request->data['tooltip'] == 1) {

			$this->layout = 'blank';
			$model =$this->request->data['model'];
			$field =$this->request->data['field'];


			$evtrue = strpos($model, 'Evaluation');

			$evtrue > 0 && isset($this->request->projectvars['VarsArray'][5])&& $this->request->projectvars['VarsArray'][5] > 0 ? $table_id = $this->request->projectvars['VarsArray'][5] : $table_id = '';

			if (!$this->Reportnumber->exists($id)) {
				throw new NotFoundException(__('Invalid reportnumber'));
			}

			$this->Autorisierung->IsThisMyReport($id);

			$options = array(
				'conditions' => array(
					'Revision.reportnumber_id' => $id,
					'Revision.order_id' =>$orderID,
					'Revision.report_id' =>$reportID,
					),
				'order' => 'Revision.id DESC'
			);

			isset($table_id) && $table_id > 0 ? $options['conditions']['Revision.table_id'] = $table_id:'';
			isset($model) && !empty($model) ? $options['conditions']['Revision.model'] = $model:'';
			isset($field) && !empty($field) && $field <> 'all'? $options['conditions']['Revision.row'] = $field:'';

			$this->loadModel('Revision');
			$this->loadModel('User');
			$this->Revision->recursive = 1;
			$revision = $this->Revision->find('all',$options);
                      // pr($revision);

			foreach ($revision as $key => $value) {
				$descripts = '';
				if($value['Revision'] ['action'] <> 'editevaluation' && $evtrue > 0){

					$weldIDs = $value['Revision'] ['last_value'];
					$weldIDs = explode(',', $weldIDs);

					$this->loadModel($model);
					$evs = $this->$model->find('all',array('fields'=> array('description'),'conditions'=>array('id' => $weldIDs)));

					if(!empty($evs)){
						if(count($evs) > 1) {

							foreach ($evs as $_evs => $value_evs) {
								$descripts .=  $value_evs [$model] ['description'].', ';
							}
					} else {
						$descripts = $evs [0][$model]['description']; }
					}

					$revision[$key] ['Revision']['last_value'] = $descripts;

				}

				/* wenn Bilder/ Dateien revisioniert werden*/

				$user = $this->User->find('first',array('conditions'=>array('User.id' => $value['Revision']['user_id'])));

				$revision[$key] ['Revision']['user'] = $user ['User']['name'];

			}

			$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
			$this->Reportnumber->recursive = 0;
			$reportnumbers = $this->Reportnumber->find('first', $options);
			$verfahren = $reportnumbers['Testingmethod']['value'];
			$Verfahren = ucfirst($verfahren);

			$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

			$this->view = 'showrevisions_tooltip';
			$this->set('revision', $revision);
			$this->set('field', $field);
			$this->set('settings', $arrayData['settings']);
		} else {

			$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
			$this->Reportnumber->recursive = 0;
			$reportnumbers = $this->Reportnumber->find('first', $options);

			$verfahren = $reportnumbers['Testingmethod']['value'];
			$Verfahren = ucfirst($verfahren);

			$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

			$this->layout = 'modal';

			$this->loadModel('Revision');
			$this->loadModel('User');
			$this->Revision->recursive = -1;

			$options = array(
				'conditions' => array(
					'Revision.reportnumber_id' => $id,
				),
				'order' => array(
					'Revision.id ASC'
				),
				'group' => array(
					'Revision.revision'
				),
				'fields' => array(
					'revision'
				)
			);


			$version = $this->Revision->find('list',$options);

			if(count($version) == 0) return;

			$revision = array();

			$this->Revision->recursive = 0;
			$this->User->recursive = -1;

			$EvModel = 'Report'.$Verfahren.'Evaluation';
			$this->loadModel($EvModel);

			foreach($version as $key => $value){

				$options = array(
					'conditions' => array(
						'Revision.reportnumber_id' => $id,
						'Revision.revision' => $value,
					),
					'order' => array(
						'Revision.id ASC'
					)
				);

				$rev = $this->Revision->find('all',$options);
				
				if(count($rev) == 0) continue;

				foreach($rev as $_key => $_value){

					$user = $this->User->find('first',array(
						'conditions' => array(
							'User.id' => $_value['Revision']['user_id']
							)
						)	
					);

					$rev[$_key]['Revision']['user'] = $user['User']['name'];

					if(!empty($_value['Revision']['row'])) continue;
					if(!is_numeric($_value['Revision']['last_value'])) continue;

					$evs = $this->$EvModel->find('first',array(
						'conditions' => array(
							$EvModel . '.id' => $_value['Revision']['last_value']
							)
						)
					);

					if(count($evs) == 0) continue;

					$rev[$_key]['Evaluation'] = $evs[$EvModel];

				}

				$revision[$value] = $rev;

			}

			/*
			if(count($revision) > 0){

				foreach($revision as $key => $value) {

					$descripts = '';

					if($value['Revision'] ['action'] <> 'editevaluation' && $value['Revision'] ['model'] == 'Report'.$Verfahren.'Evaluation'){

						$EvModel = 'Report'.$Verfahren.'Evaluation';
						$weldIDs = $value['Revision'] ['last_value'];
						$weldIDs = explode(',', $weldIDs);

						$this->loadModel($EvModel);

						$evs = $this->$EvModel->find('all',array('fields'=> array('description'),'conditions'=>array('id' => $weldIDs)));

						if(!empty($evs)){
							if(count($evs) > 1) {
								foreach ($evs as $_evs => $value_evs) {
									$descripts .=  $value_evs [$EvModel] ['description'].', ';
								}
							} else {
								$descripts = $evs [0][$EvModel]['description']; }
							}

						$revision[$key] ['Revision']['last_value'] = $descripts;

					}

					$user = $this->User->find('first',array('conditions'=>array('User.id' => $value['Revision']['user_id'])));

					$revision[$key] ['Revision']['user'] = $user ['User']['name'];

					if($value['Revision'] ['action'] == 'editevaluation'){

						$model = $value['Revision'] ['model'];

						$this->loadModel($model);
						$evs = $this->$model->find('first',array('conditions'=>array('id' => $value['Revision']['table_id'])));

						$description = $evs[$model]['description'];

						if(isset($evs[$model]['position'])){

							$description .= ' ' . $evs[$model]['position'];

						}

						$revision[$key]['Revision']['description'] = $description;

					}

				}

				foreach($revision as $_rv => $_rv_value){
					$revisionbynumber [$_rv_value['Revision']['revision']][] = $_rv_value;
				}

				$revision = $revisionbynumber;
			}
			*/

			$this->set('revision', $revision);
			$this->set('settings', $arrayData['settings']);
			$this->set('locale', $this->Lang->Discription());

		}
    }

    public function printrevisions (){

	$projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $reportID = $this->request->projectvars['VarsArray'][3];
        $id = $this->request->projectvars['VarsArray'][4];
        $testingmethodID = $this->request->projectvars['VarsArray'][5];
        $version = $this->request->projectvars['VarsArray'][8];



        $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
        $this->Reportnumber->recursive = 0;
        $reportnumbers = $this->Reportnumber->find('first', $options);
        $verfahren = $reportnumbers['Testingmethod']['value'];
        $Verfahren = ucfirst($verfahren);
        $arrayDataPdf = $this->Xml->DatafromXml('Revision', 'file', null);
        $arrayData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);



        $ReportArchiv = 'Report' . $Verfahren . 'Archiv';
        $ReportSetting = 'Report' . $Verfahren . 'Setting';
        $ReportGenerally = 'Report' . $Verfahren . 'Generally';
        $ReportSpecific = 'Report' . $Verfahren . 'Specific';
        $ReportEvaluation = 'Report' . $Verfahren . 'Evaluation';
        $ReportPdf = 'ReportPdf';

        $ReportTableNames = array(
            'Archiv' => 'Report' . $Verfahren . 'Archiv',
            'Setting' => 'Report' . $Verfahren . 'Setting',
            'Generally' => 'Report' . $Verfahren . 'Generally',
            'Specific' => 'Report' . $Verfahren . 'Specific',
            'Evaluation' => 'Report' . $Verfahren . 'Evaluation',
        );

        $this->loadModel($ReportArchiv);
        $optionsReport = array('conditions' => array('reportnumber_id' => $reportnumbers['Reportnumber']['id']));
        $reportArchiv = $this->$ReportArchiv->find('first', $optionsReport);

        $this->Reportnumber->HiddenField->recursive = -1;
        $hiddenFields = $this->Reportnumber->HiddenField->find('all', $optionsReport);

        $data = array();
        $settings = array();

        $data = utf8_decode($reportArchiv[$ReportArchiv]['data']);

        //			$data = Xml::toArray(Xml::build(html_entity_decode($data)));
        $data = Xml::build(html_entity_decode($data));
        //			$data = $this->Xml->XmltoArray($reportArchiv[$ReportArchiv]['data'],'string');
        $value_data = array();
        foreach ($data as $_key => $_data) {

            if ($_key == $ReportEvaluation)
                continue;

            if (!empty($_data)) {
                foreach ($_data as $__key => $__data) {
                    if (is_string(trim($__data))) {
                        $value_data[$_key][$__key] = utf8_decode(trim($__data));
                    }
                }
            }
        }

        $number = 0;
        foreach ($data->$ReportEvaluation as $_data) {
            foreach ($_data as $__key => $__data) {
                $value_data[$ReportEvaluation][trim($_data->id)][$__key] = utf8_decode(trim($__data));
            }

            $number++;
        }

        $settingsData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);
        $settings = $arrayDataPdf['settings'];

        $this->Data->CreateRevisionsList($reportnumbers, $data, $settings);

        $reportvendor = 'report';

        if (!empty($settings->$ReportPdf->settings->QM_REPORT_VENDOR)) {
            if (file_exists(APP . 'Vendor' . DS . trim($settings->$ReportPdf->settings->QM_REPORT_VENDOR) . '.php')) {
                $reportvendor = trim($settings->$ReportPdf->settings->QM_REPORT_VENDOR);
            }
        }

        // aus dem Auswertungsobjekt wird ein Array erstellt
        $arrayEvaluation = array();
        $x = 0;

        foreach ($data->$ReportEvaluation as $_key => $_ReportEvaluation) {
            //			foreach($data['archiv'][$ReportEvaluation] as $_key => $_ReportEvaluation) {
            foreach ($_ReportEvaluation as $__key => $__ReportEvaluation) {
                $arrayEvaluation[$x][$_key][$__key] = trim($__ReportEvaluation);
            }
            $x++;
        }

        $this->Pdf->ReplaceData($data, $settings, $arrayEvaluation, $ReportTableNames);

        if ($reportnumbers['Testingmethod']['allow_children']) {
            $data = $this->Pdf->InsertDataFromChildReports($reportnumbers, $data);
        }

        $pdfData[] = array(
            'hiddenFields' => $hiddenFields,
            'revChanges' => $this->Data->GetRevisionChanges($reportnumbers['Reportnumber']['id']),
            //'reportimages' => $reportimages,
            'version' => $version,
            'Verfahren' => $Verfahren,
            'allow_children' => $reportnumbers['Testingmethod']['allow_children'],
            'data' => $data,
            'settings' => $settings,
            'user' => $this->Auth->user()
        );
        // pr($arrayData);
        // pr($id);
        $this->layout = 'modal';
        $options = array(
            'conditions' => array(
                'Revision.reportnumber_id' => $id,
                'Revision.order_id' => $orderID,
                'Revision.report_id' => $reportID,
            ),
            'order' => 'Revision.id asc',
        );
        $this->loadModel('Revision');
        $this->loadModel('User');
        $this->Revision->recursive = 1;
        $revision = $this->Revision->find('all', $options);
        $revisionbynumber = array();
        foreach ($revision as $key => $value) {
            $descripts = '';
            if ($value['Revision'] ['action'] <> 'editevaluation' && $value['Revision'] ['model'] == 'Report' . $Verfahren . 'Evaluation') {
                $EvModel = 'Report' . $Verfahren . 'Evaluation';
                $weldIDs = $value['Revision'] ['last_value'];
                $weldIDs = explode(',', $weldIDs);
                $this->loadModel($EvModel);
                //  pr($weldIDs);
                $evs = $this->$EvModel->find('all', array('fields' => array('description'), 'conditions' => array('id' => $weldIDs)));

                if (!empty($evs)) {
                    if (count($evs) > 1) {
                        foreach ($evs as $_evs => $value_evs) {
                            $descripts .= $value_evs [$EvModel] ['description'] . ', ';
                        }
                    } else {
                        $descripts = $evs [0][$EvModel]['description'];
                    }
                }
                $revision[$key] ['Revision']['last_value'] = $descripts;
            }
            /* wenn Bilder/ Dateien revisioniert werden */



            $user = $this->User->find('first', array('conditions' => array('User.id' => $value['Revision']['user_id'])));

            $revision[$key] ['Revision']['user'] = $user ['User']['name'];

            if(isset($revision[$key] ['Revision']['action'])) {
                $actions = array('edit','delete','duplicate','add');
                foreach ($actions as $act => $act_value) {

                    strpos($revision[$key] ['Revision']['action'], $act_value) !== false ? $revision[$key] ['Revision']['action'] = __($act_value) :'';
                    $revision[$key] ['Revision']['action'] == 'images/delimage' || $revision[$key] ['Revision']['action'] == 'files/delfile'  ? $revision[$key] ['Revision']['action']= __('deleted file'):'';
                    $revision[$key] ['Revision']['action'] == 'images/addimage' || $revision[$key] ['Revision']['action'] == 'files/addfile'  ? $revision[$key] ['Revision']['action']= __('added file'):'';
                    $revision[$key] ['Revision']['action'] == 'images/discription' || $revision[$key] ['Revision']['action'] == 'files/discription'  ? $revision[$key] ['Revision']['action']= __('file discription changed'):'';
                }
            }
        }

        foreach ($revision as $_rv => $_rv_value) {
            $revisionbynumber [$_rv_value['Revision']['revision']][] = $_rv_value;
        }

        $revision = $revisionbynumber;
        $this->Autorisierung->Logger($id);
        $this->set('revision', $revision);
        $this->set('xml_revision', $arrayDataPdf['settings']);
        $this->set('reportsettings', $settingsData['settings']);
        // $this->set('settings', $arrayData['settings']);
        $this->set('pdfData', $pdfData);
        $this->set('locale', $this->Lang->Discription());
        //pr($revisionbynumber);
    }

 public function filediscription($id = null) {

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$testingreportID = $this->request->projectvars['VarsArray'][4];
	 	$id = $this->request->projectvars['VarsArray'][6];

	 	$this->Autorisierung->IsThisMyReport($testingreportID);

	 	$reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $testingreportID)));

		$reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

		$attribut_disabled = false;

		if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
		if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

	 	$this->layout = 'modal';

	 	$this->loadModel('Reportfile');

	 	$this->set('projectID',$projectID);
	 	$this->set('orderID',$orderID);
	 	$this->set('reportID',$reportID);
	 	$this->set('testingreportID',$testingreportID);

	 	if(isset($this->request['data']['Reportfile'])){


			if($attribut_disabled === true){
	 			die();
			}

	 		// zum bearbeiten notwendige Daten aus der Datenbank holen
	 		$reportfiles = $this->Reportfile->find('first', array('conditions' => array('Reportfile.id' => $id)));

	 		if($this->Reportfile->save($this->request['data']['Reportfile'])){
	 			$this->Autorisierung->Logger($testingreportID,$this->request['data']['Reportfile']);

				// diese Array wird der Revison übergeben, wenn nötig
				$TransportArray['model'] = 'Reportfile';
				$TransportArray['row'] = 'discription';
				$TransportArray['last_id_for_radio'] = 0;
				$TransportArray['last_value'] = $reportfiles['Reportfile']['discription'];
				$TransportArray['this_value'] = $this->request['data']['Reportfile']['discription'];
				$TransportArray['table_id'] = $this->request['data']['Reportfile']['id'];
                                $TransportArray['reason'] = $this->Session->read('revision'.$id.'reason');
				$reportnumbers['Reportnumber']['revision_progress'] > 0 ?$this->Data->SaveRevision($reportnumbers,$TransportArray,'files/discription'):'';


				 $this->Flash->success(__('Discription was saved'), array('key' => 'success'));

	 		}
	 		else {
				$this->Flash->error(__('Discription was not saved'), array('key' => 'error'));

	 		}
	 	}

	 	else {
	 		$reportfiles = $this->Reportfile->find('first', array('conditions' => array('Reportfile.id' => $id)));


	 		$this->request->projectvars['VarsArray'][4] = $reportfiles['Reportfile']['reportnumber_id'];
	 		$this->request->projectvars['VarsArray'][6] = $reportfiles['Reportfile']['id'];

	 		$SettingsArray = array();
	 		//$this->Navigation->setImageLink('imagediscription',$reportfiles['Reportfile']['reportnumber_id'],$SettingsArray);

	 		$this->set('reportfiles', $reportfiles);
	 		$this->render(null);
	 	}
		$this->set('reportnumbers', $reportnumbers);

	 }

 function modulchilddata () {
    $this->layout = 'json';

    $projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];
                $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
	 	$this->Reportnumber->recursive = 0;
	 	$reportnumbers = $this->Reportnumber->find('first', $options);
                $verfahren = $reportnumbers['Testingmethod']['value'];
	 	$Verfahren = ucfirst($verfahren);
	 	$this->request->verfahren = $Verfahren;
	 	$ReportGenerally = 'Report'.$Verfahren.'Generally';
	 	$ReportSpecific = 'Report'.$Verfahren.'Specific';
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

    $this->loadModel($ReportGenerally);
	 	$this->loadModel($ReportSpecific);
	 	$this->loadModel($ReportEvaluation);

	 	$arrayData = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
    $arrayData = $arrayData['settings'];

    $datas = $this->request->data[$ReportGenerally];

    foreach ($datas as $datakey => $datavalue) {

    	if (isset($arrayData->$ReportGenerally->$datakey->select->moduldata)) {
      	$moduldataxml = $arrayData->$ReportGenerally->$datakey->select->moduldata;

        $model = trim($arrayData->$ReportGenerally->$datakey->select->moduldata->model);

        $field = trim($arrayData->$ReportGenerally->$datakey->select->moduldata->field);

        $fieldvalue = $datavalue;
        If(!empty($fieldvalue)) {
        	$childvalues = $this->Data->ModulChildData($arrayData,$ReportGenerally,$this->request->data,$moduldataxml,$model,$field,$fieldvalue);
        } else $childvalues = '';
	        	$response = $childvalues;
        }
    }
	 $response = $response[$ReportGenerally] ;
	 $responses = json_encode($response);
   $this->request->data = '';
	 $this->request->data = $responses;
   $this->set('response',$responses);


         }
	function imagecount() {

		$this->layout = 'json';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
	 	$id = $this->request->projectvars['VarsArray'][4];

		if(isset($this->request->data['count'])){
			$Count = $this->Sicherheit->Numeric($this->request->data['count']);
			$this->Session->write('PDFImageCount',$Count);
			$this->set('PDFCount',json_encode(array('count' => $this->Session->read('PDFImageCount'))));
		}
		if(!isset($this->request->data['count']) && $this->Session->check('PDFImageCount') == false){
			$this->set('PDFCount',json_encode(array('count' => 1)));
		}
		if(!isset($this->request->data['count']) && $this->Session->check('PDFImageCount') == true){
			$this->set('PDFCount',json_encode(array('count' => $this->Session->read('PDFImageCount'))));
		}
	}

	function barcode() {

		$this->layout = 'json';

		$reportnumber_id = intval($this->request->data['barcode']);

	 	if (!$this->Reportnumber->exists($reportnumber_id)) {
			$this->set('output',json_encode(array('url' => '','error' => __('Testingreport does not exist in this database',true))));
	 		$this->render();
			return;
	 	}

	 	$Result = $this->Autorisierung->IsThisMyReport($reportnumber_id);

		if($Result == false){
			$this->set('output',json_encode(array('url' => '','error' => __('Testingreport can not be displayed',true))));
	 		$this->render();
			return;
		}

		$this->Reportnumber->recursive = -1;
		$Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumber_id)));

		$Vars[] = $Reportnumber['Reportnumber']['topproject_id'];
		$Vars[] = $Reportnumber['Reportnumber']['cascade_id'];
		$Vars[] = $Reportnumber['Reportnumber']['order_id'];
		$Vars[] = $Reportnumber['Reportnumber']['report_id'];
		$Vars[] = $Reportnumber['Reportnumber']['id'];

		$url = Router::url(array_merge(array('controller' => 'reportnumbers','action' => 'edit'),$Vars));

		$this->set('output',json_encode(array('url' => $url,'error' => '')));
	}

	// kann gelöscht werden
	public function send_mass_collectpdf(){

		if(Configure::check('sendmailreport') === false) return;
		if(Configure::read('sendmailreport') === false) return;

		$MassSelectetIDs = explode(',',$this->request->data['Reportnumber']['MassSelectetIDs']);
		$MassSelect = explode('_',$this->request->data['Reportnumber']['MassSelect']);
		$Step = 2;

		if($MassSelect[0] != 'sign') return;

		// Funktioniert im Augenblick nur für Wacker Nünchritz
		// bei Bericht schließen mit Stufe 2
		$AclCheck = $this->Autorisierung->AclCheck(array(0 => 'reportnumbers',1 => 'signSupervisor'));

		if($AclCheck === false) return;

		if(count($MassSelectetIDs) == 0) return;
		if(count($MassSelectetIDs) > 25) return;

		$projectvars = $this->request->projectvars;

		foreach ($MassSelectetIDs as $key => $value) {

			$reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $value)));

	//		if(count($reportnumber) == 0) continue;

			$this->request->projectvars['VarsArray'][0] = $reportnumber['Reportnumber']['topproject_id'];
		 	$this->request->projectvars['VarsArray'][1] = $reportnumber['Reportnumber']['cascade_id'];
		 	$this->request->projectvars['VarsArray'][2] = $reportnumber['Reportnumber']['order_id'];
		 	$this->request->projectvars['VarsArray'][3] = $reportnumber['Reportnumber']['report_id'];
		 	$this->request->projectvars['VarsArray'][4] = $reportnumber['Reportnumber']['id'];
		 	$this->request->projectvars['VarsArray'][8] = 2;

			$this->request->projectvars['projectID'] = $reportnumber['Reportnumber']['topproject_id'];
			$this->request->projectvars['cascadeID'] = $reportnumber['Reportnumber']['cascade_id'];
			$this->request->projectvars['orderID'] = $reportnumber['Reportnumber']['order_id'];
			$this->request->projectvars['reportID'] = $reportnumber['Reportnumber']['report_id'];
			$this->request->projectvars['reportnumberID'] = $reportnumber['Reportnumber']['id'];


			$this->request->data['projectID'] = 1;
			$this->collectpdf();

		}

		$this->request->projectvars = $projectvars;

	}

		public function collectpdf($attachments = 0){

			$projectID = $this->request->projectvars['VarsArray'][0];
			$cascadeID = $this->request->projectvars['VarsArray'][1];
			$orderID = $this->request->projectvars['VarsArray'][2];
			$reportID = $this->request->projectvars['VarsArray'][3];
			$id = $this->request->projectvars['VarsArray'][4];
	

			if($attachments == 1) $this->request->data['Reportnumber']['attachments'] = "1";

			$this->layout = 'blank';

			$Attachments = 0;

			if(isset($this->request->data['Reportnumber']['attachments']))  $Attachments = $this->request->data['Reportnumber']['attachments'];

			$this->request->data['collectpdf'] = 1;

			$this->pdf();

			$this->autoRender = true;

			$Attachments = $this->Email->SendReportWithAttachments($Attachments);

			if ($Attachments === true) {

				$options = array('conditions' => array(
					'Reportnumber.id' => $id,
				));
   
		 	 	$this->Reportnumber->recursive = 1;
				$reportnumber = $this->Reportnumber->find('first', $options);
				$reportnumber = $this->Data->GetReportData($reportnumber,array('Generally','Specific','Evaluation'));
				$verfahren = $reportnumber['Testingmethod']['value'];

				$xml = $this->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

				$EmailAdresses = $this->Reportnumber->getMailAdresses($xml, $reportnumber);
		
    			$this->Flash->success('Email was sent successfully to:'.' '. implode(', ',$EmailAdresses), array('key' => 'success'));
				
			} else {

				$this->Reportnumber->save(array('id' => $id,'print' => 0));
    			$this->Flash->error('An error occurred while sending the email', array('key' => 'error'));

			}			

	}

	public function collectpdfraw(){


			$this->layout = 'blank';

			$Attachments = 0;

			$this->request->data['collectpdf'] = 1;

			$this->pdf();

			$this->autoRender = true;

	}

	public function checkdigipipe(){

		$this->layout = 'blank';

		//	$this->Rest->CheckDigipie();
		// http://mps/reportnumbers/edit/14/36/0/14/74144

		$Id = 75608;

		$Data = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $Id)));
		$Data = $this->Data->RevisionCheckTime($Data);

		$Data = $this->Signing->UpdateReportnumberDataTest($Data,$Id);
	}
	
}