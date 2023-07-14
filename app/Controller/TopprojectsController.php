<?php
App::uses('AppController', 'Controller');

/**
 * Topprojects Controller
 *
 * @property Topproject $Topproject
 */
class TopprojectsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue','Data','Xml','Search','Drops');

	public $helpers = array('Lang','Navigation','JqueryScripte');
	public $layout = 'ajax';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		$this->loadModel('User');

		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('quicksearch','quickreportsearch');

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
		$this->set('menues', $this->Navigation->Menue());
//		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->Session->write('projectURL', null);
		$this->Navigation->ReportVars();
		$this->set('locale', $this->Lang->Discription());
		$this->set('SettingsArray',array());
		$this->Session->delete('search.params');
		$this->Session->delete('search.results');

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}

	}

	function afterFilter() {
		$this->Navigation->lastURL();
	}

    function beforeRender() {

		$model = Inflector::singularize($this->name);

		foreach($this->{$model}->hasAndBelongsToMany as $_key => $_data) {
			if(isset($this->{$model}->validationErrors[$_key])) {
				$this->{$model}->{$_key}->validationErrors[$_key] = $this->{$model}->validationErrors[$_key];
			}
		}
	}

	public function start() {

//		$this->layout = 'start';

		$this->Navigation->ResetAllSessionForPaging();

		// Das Array für das Settingsmenü
		$SettingsStartArray = array();

		$SettingsStartArray['ndt_reporting'] = array('discription' => __('NDTManager',true), 'controller' => 'topprojects','action' => 'index', 'terms' => null);

		if(Configure::read('CertifcateManager') == true){
			$SettingsStartArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}

		if(Configure::read('DeviceManager') == true){
			$SettingsStartArray['devicelink'] = array('discription' => __('Devices administration',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
		}

		if(Configure::read('DocumentManager') == true){
			$SettingsStartArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
		}

		if(Configure::check('DropdownsManager') == true && Configure::read('DropdownsManager') == true){
			$SettingsStartArray['dropdownsmasterlink'] = array('discription' => __('Dropdown administration',true), 'controller' => 'dropdowns','action' => 'master', 'terms' => null);
		}

		if(Configure::read('TestinginstructionManager') == true){
			$SettingsStartArray['testinginstructionlink'] = array('discription' => __('Testinginstruction administration',true), 'controller' => 'testinginstructions','action' => 'index', 'terms' => null);
		}

		if(Configure::check('ProgressesEnabled') == true && Configure::read('ProgressesEnabled') == true){
			$SettingsStartArray['advancelink'] = array('discription' => __('AdvanceManager',true), 'controller' => 'advances','action' => 'index', 'terms' => null);
		}

		if(Configure::check('ExpeditingManager') == true && Configure::read('ExpeditingManager') == true){
			$SettingsStartArray['expediting'] = array('discription' => __('ExpeditingManager',true), 'controller' => 'expeditings','action' => 'start', 'terms' => null);
		}
		if(Configure::read('DropdownsManager') == true){
			$SettingsStartArray['dropdownsmasterlink'] = array('discription' => __('Dropdown administration',true), 'controller' => 'dropdowns','action' => 'master', 'terms' => null);
		}
    if(Configure::read('WelderManager') == true){
			$SettingsStartArray['welderlink'] = array('discription' => __('Welder administration',true), 'controller' => 'welders','action' => 'indexcomp', 'terms' => null);
		}
		if(Configure::check('TestinginstructionManager') == true && Configure::read('TestinginstructionManager') == true){
			$SettingsStartArray['testinginstructionlink'] = array('discription' => __('Testinginstruction administration',true), 'controller' => 'testinginstructions','action' => 'index', 'terms' => null);
		}
		if(Configure::check('TemplateManagement') == true && Configure::read('TemplateManagement') == true){
			$SettingsStartArray['templatelink'] = array('discription' => __('Report Template Administration',true), 'controller' => 'templates','action' => 'index', 'terms' => null);
		}

		if(Configure::check('MonitoringManager') == true && Configure::read('MonitoringManager') == true){
			$SettingsStartArray['monitoringlink'] = array('discription' => __('Monitoring',true), 'controller' => 'monitorings','action' => 'index', 'terms' => null);
		}


		if(!Configure::check('search.orders') || Configure::read('search.orders')) {
//			$SettingsArray['addsearching'] = array('discription' => __('Searching for orders or reports',true), 'controller' => 'topprojects','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		} else {
//			$SettingsArray['addsearching'] = array('discription' => __('Searching for reports',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		}

		if(Configure::check('IncommingGoodInspectionMananger') == true && Configure::read('IncommingGoodInspectionMananger') == true){
			$SettingsStartArray['incomminggoodinspectionlink'] = array('discription' => __('Incomming Good Inspection',true), 'controller' => 'incomminggoodinspections','action' => 'index', 'terms' => null);
		}

		$SettingsStartArray = $this->Autorisierung->AclCheckLinks($SettingsStartArray);

		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);

		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$LandingPageUrl = $this->Navigation->SelectLandingPageAutoload();

		$user_id = $this->Auth->user('id');
		$this->loadModel('Changelog');
		$this->loadModel('ChangelogUser');

		$ChangelogIds = $this->Changelog->find("list");
		$ChangelogUserIds = $this->ChangelogUser->find("all", array(
			'conditions' => array(
				'user_id' => $user_id
				)
			)
		);

		$ChangelogUserIds = Hash::extract($ChangelogUserIds, '{n}.ChangelogUser.changelog_id');

		$NotViewedIds = array_diff($ChangelogIds, $ChangelogUserIds);

		if(count($NotViewedIds) > 0){

			$max_date = $this->Changelog->find("first", array(
				'order' => array('id DESC'),
				'conditions' => array(
						'Changelog.id'=> $NotViewedIds
					),
				),
			);

			foreach ($NotViewedIds as $key => $value) {
				$savearray = array('id' => 0,'user_id'=> $user_id,'changelog_id'=> $value);
				$this->ChangelogUser->save($savearray);
			}

			$displaychangelog = 1;
			$this->set('displaychangelog', $displaychangelog);
			$this->set('changelog', $max_date);
		}

		$this->set('LandingPageUrl', $LandingPageUrl);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('SettingsStartArray', $SettingsStartArray);

	}
/**
 * index method
 *
 * @return void
 */
	public function index() {

		// Alle Sessioneinträge für alle gespeicherten Seiten in allen besuchten Tabellen zurückstellen
		$this->Navigation->ResetAllSessionForPaging();

		$this->_index(0);

		// Das Array für das Settingsmenü
		$SettingsArray = array();

		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}
    if(Configure::read('WelderManager') == true){
			$SettingsArray['welderlink'] = array('discription' => __('Welder administration',true), 'controller' => 'welders','action' => 'indexcomp', 'terms' => null);
		}
		if(Configure::read('DeviceManager') == true){
			$SettingsArray['devicelink'] = array('discription' => __('Devices administration',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
		}
		if(Configure::read('DocumentManager') == true){
			$SettingsArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
		}
		if(Configure::check('TestinginstructionManager') == true && Configure::read('TestinginstructionManager') == true){
			$SettingsArray['testinginstructionlink'] = array('discription' => __('Testinginstruction administration',true), 'controller' => 'testinginstructions','action' => 'index', 'terms' => null);
		}
		if(Configure::check('ProgressesEnabled') == true && Configure::read('ProgressesEnabled') == true){
			$SettingsArray['advancelink'] = array('discription' => __('Progresstool administration',true), 'controller' => 'advances','action' => 'index', 'terms' => null);
		}
		if(Configure::check('DropdownsManager') == true && Configure::read('DropdownsManager') == true){
			$SettingsArray['dropdownsmasterlink'] = array('discription' => __('Dropdown administration',true), 'controller' => 'dropdowns','action' => 'master', 'terms' => null);
		}

		if(Configure::read('DevelopmentsEnabled') == true){
			$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);
		}

		$SettingsArray['addlink'] = array('discription' => __('Add new project',true), 'controller' => 'topprojects','action' => 'add', 'terms' => null);
		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);


		if(!Configure::check('search.orders') || Configure::read('search.orders')) {
//			$SettingsArray['addsearching'] = array('discription' => __('Searching for orders or reports',true), 'controller' => 'topprojects','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		} else {
//			$SettingsArray['addsearching'] = array('discription' => __('Searching for reports',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		}

		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$SubMenueArray = array();
		array_push($SubMenueArray, array('title' => __('View project'),'controller' => 'topprojects','action' => 'view','open' => 'dialog','uiIcon' => 'qm_show'));
		array_push($SubMenueArray, array('title' => __('Edit project'),'controller' => 'topprojects','action' => 'edit','open' => 'dialog','uiIcon' => 'qm_edit'));
		array_push($SubMenueArray, array('title' => __('Delete project'),'controller' => 'topprojects','action' => 'delete','open' => 'dialog','uiIcon' => 'qm_delete'));

		$SubMenueArray = $this->Autorisierung->AclCheckSubmenueMenue($SubMenueArray);

		$this->set('SubMenueArray', $SubMenueArray);
		$this->set('SettingsArray', $SettingsArray);
	}

	public function status() {

		$this->_index(1);

		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);

		$this->layout = 'modal';
		$this->set('SettingsArray', $SettingsArray);
		$this->set('action', 'reopen');
	}

	public function deleted() {

		$this->_index(2);

		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);

		$this->layout = 'modal';
		$this->set('SettingsArray', $SettingsArray);
		$this->set('action', 'restore');
	}

	protected function _index($status) {

//pr($this->Session->read('conditionsTopprojects'));
//		Auswertung Stunden Nünchritz
//		$this->_workingcodes();

		$ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

		$this->loadModel('Cascade');

		if(isset($this->request->data['Quicksearch']['this_id']) && $this->request->data['Quicksearch']['this_id'] > 0){
			$ConditionsTopprojects = array_intersect(array($this->request->data['Quicksearch']['this_id']),$ConditionsTopprojects);
		}

		$option = array();
		$options['Cascade.topproject_id'] = $ConditionsTopprojects;
		$options['Cascade.level'] = 0;
//		$options['Topproject.status'] = array(0,1);

		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $limet = 500000;
		else $limet = 25;

		$Direction = 'ASC';

		if(Configure::check('TopprojectsIndexSorting') && Configure::read('TopprojectsIndexSorting') != false) $Direction = Configure::read('TopprojectsIndexSorting');

		$this->paginate = array(
    	'conditions' => array($options),
			'order' => array('Cascade.discription' => $Direction),
			'limit' => $limet
		);

		$Cascade = $this->paginate('Cascade');

		$this->Cascade->recursive = -1;

		foreach($Cascade as $_key => $_cascade){
				$Cascade[$_key]['Cascade']['link'] = array(
														'controller' => 'cascades',
														'action' => 'index',
														'term' => array($_cascade['Topproject']['id'],$_cascade['Cascade']['id'],0),
														);

		}

		$redirectfrom = FULL_BASE_URL . $this->webroot . 'users/loggedin';
		$redirectto = FULL_BASE_URL . $this->webroot;

		$subdevision_link = array(
				'controller' => 'topprojects',
				'action' => 'index',
			);

		$this->set('subdevision_link', $subdevision_link);
		$this->set('redirectfrom', $redirectfrom);
		$this->set('redirectto', $redirectto);
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set('topprojects', $Cascade);

		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) 		$this->render('index_landingpage','blank');

	}

	public function last_ten() {

		$this->layout = 'blank';

		$ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

		$this->loadModel('Cascade');
		$this->loadModel('Reportnumber');

		$this->Reportnumber->recursive = -1;
		$this->Topproject->recursive = -1;
		$this->Cascade->recursive = -1;

		$LastProjects = array();
		foreach ($ConditionsTopprojects as $key => $value) {

			$option = array(
				'order' => array('modified DESC'),
				'fields' => array('topproject_id','modified'),
				'conditions' => array(
					'Reportnumber.topproject_id' => $value,
					'Reportnumber.modified_user_id' => AuthComponent::user('id')
				)
			);

			$Reportnumbers = $this->Reportnumber->find('first',$option);

			if(count($Reportnumbers) > 0){

				$topproject = $this->Topproject->find('first',array('conditions' => array('Topproject.id' => $value)));
				$cascades = $this->Cascade->find('first',array('conditions' => array('Cascade.topproject_id' => $value,'Cascade.level' => 0)));

				$LastProjects[$value] = $Reportnumbers['Reportnumber'];
				$LastProjects[$value]['projektname'] = $topproject['Topproject']['projektname'];
				$LastProjects[$value]['link']['controller'] = 'cascades';
				$LastProjects[$value]['link']['action'] = 'index';
				$LastProjects[$value]['link']['term'] = array($value,$cascades['Cascade']['id'],0);
			}

		}

		$LastProjects = Hash::sort($LastProjects, '{n}.modified', 'desc');

		$this->set('LastProjects',$LastProjects);

	}

	// nur für interne Timyauswertung
	protected function _workingcodes() {

		App::uses('CakeTime', 'Utility');

		$this->loadModel('WorkingCodeImport');
		$this->loadModel('WorkingCode');
		$WorkingCodeImport = $this->WorkingCodeImport->find('all');
		$output = array();
		$export = array();
		foreach($WorkingCodeImport as $_key => $_data){
//			pr($_data);
			$datetime = explode(' ',trim($_data['WorkingCodeImport']['time']));
			$SqlTimeArray = explode('.',$datetime[0]);
			$SqlTime = $SqlTimeArray[2] . '-' . $SqlTimeArray[1] . '-' . $SqlTimeArray[0] . ' ' . $datetime[1];

			$ThisData = array();
			$ThisData['WorkingCode']['name'] = trim($_data['WorkingCodeImport']['last_name']) . ' ' . trim($_data['WorkingCodeImport']['first_name']);
			$ThisData['WorkingCode']['in_or_out'] = $_data['WorkingCodeImport']['in_or_out'];
			$ThisData['WorkingCode']['stamp'] = $_data['WorkingCodeImport']['time'];
			$ThisData['WorkingCode']['date'] = $datetime[0];
			$ThisData['WorkingCode']['time'] = $datetime[1];
			$ThisData['WorkingCode']['sqltime'] = $SqlTime;
			$ThisData['WorkingCode']['timestamp'] = strtotime($SqlTime);
			$output[] = $ThisData;
			$export[$ThisData['WorkingCode']['name']] = array();
		}

		foreach($output as $_key => $_data){
			$ThisData = array();
			$ThisData['name'] = $_data['WorkingCode']['name'];
			$ThisData['time'] = $_data['WorkingCode']['time'];
			$ThisData['timestamp'] = $_data['WorkingCode']['timestamp'];
			$export[$_data['WorkingCode']['name']][$_data['WorkingCode']['date']][$_data['WorkingCode']['in_or_out']] = $ThisData;
		}

		foreach($export as $_key => $_data){
			$TimeTotal = 0;
			foreach($_data as $__key => $__data){
				if(isset($__data['Herein']) && isset($__data['Hinaus'])){
					$Diff = round(($__data['Hinaus']['timestamp'] - $__data['Herein']['timestamp']) / 3600,2);
					$export[$_key][$__key]['Zeitraum'] = $Diff;
					$TimeTotal = $TimeTotal + $Diff;
					$export[$_key][$__key]['Stunden'] = $TimeTotal;
				}
				if(isset($__data['Herein']) && !isset($__data['Hinaus'])){
					$export[$_key][$__key]['Zeitraum'] = 'nicht ausgeloggt (8 Stunden berechnet)';
					$TimeTotal = $TimeTotal + 8;
					$export[$_key][$__key]['Stunden'] = $TimeTotal;
				}
				if(!isset($__data['Herein']) && isset($__data['Hinaus'])){
					$export[$_key][$__key]['Zeitraum'] = 'nicht eingeloggt (8 Stunden berechnet)';
					$TimeTotal = $TimeTotal + 8;
					$export[$_key][$__key]['Stunden'] = $TimeTotal;
				}
				if(!isset($__data['Herein']) && !isset($__data['Hinaus'])){
					$export[$_key][$__key]['Zeitraum'] = 'nicht gestempelt';
					$TimeTotal = $TimeTotal + 0;
					$export[$_key][$__key]['Stunden'] = $TimeTotal;
				}
			}
			$export[$_key]['Gesamt']['Zeitraum'] = $TimeTotal;
		}

		$this->set('data',$export);
		$this->render('workingcode','blank');
	}

	public function search() {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->request->projectvars['VarsArray'][4];
		$evalId = $this->request->projectvars['VarsArray'][5];
		$weldedit = $this->request->projectvars['VarsArray'][6];

		$testingreportsCount = 0;

		$fields = $this->Data->SearchUniversal('search');

		$this->set('SettingsArray', null);
		$this->set('reportName', @$SearchFields['report_id']['Result'][$reportID]);
		$this->set('reportID', $reportID);
		$this->set('projectID', $projectID);
		$this->set('cascadeID', $cascadeID);
		$this->set('orderID', $orderID);
	}

	public function quicksearch() {

		App::uses('Sanitize', 'Utility');

 		$this->layout = 'json';

		if(isset($this->request->data['term'])) $term = Sanitize::stripAll($this->request->data['term']);

		if(!isset($term)){
			$this->set('test',json_encode(array()));
			return;
		}

		$term = Sanitize::escape($term);
		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];

		$options = array(
						'fields' => array('id','projektname'),
						'limit' => 5,
						'conditions' => array(
								'Topproject.id' => $this->Autorisierung->ConditionsTopprojects(),
								'Topproject.projektname LIKE' => '%'.$term.'%'
							)
						);
		$this->Topproject->recursive = -1;
		$topproject = $this->Topproject->find('all', $options);

		$response = array();

		foreach($topproject as $_key => $_topproject){
			array_push($response, array('key' => $_topproject['Topproject']['id'],'value' => $_topproject['Topproject']['projektname']));
		}

		$this->set('test',json_encode($response));
	}

	public function quickreportsearch() {

		App::uses('Sanitize', 'Utility');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		$this->loadModel('Reportnumber');
		$this->loadModel('Cascade');

 		$this->layout = 'json';

		$breads = $this->Navigation->Breads(null);

		$response = array();
		$CascadeChildrenList = array();

		$CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));
		if(count($CascadeCurrent) > 0)$CascadeChildrenList = $this->Navigation->CascadeGetChildrenList($CascadeCurrent['Cascade']['id']);

		if($this->request->data['targetcontroller'] == 'reportnumbers' && $this->request->data['targetation'] == 'view'){

			$term = Sanitize::clean($this->request->data['term']);

			$term_array = explode('-',$term);

			$options = array();
			$options['Reportnumber.delete'] = 0;

			if($projectID == 0){
				$options['Reportnumber.topproject_id'] = $this->Autorisierung->ConditionsTopprojects();
			} else {
				$options['Reportnumber.topproject_id'] = $projectID;
			}

			if(count($term_array) == 1){
				$options['Reportnumber.number'] = intval($term_array[0]);
			}
			if(count($term_array) == 2){
				$options['Reportnumber.number'] = intval($term_array[1]);
				$options['Reportnumber.year'] = intval($term_array[0]);
			}

			$ReportnumberTestingCompOption = null;

			if(AuthComponent::user('Testingcomp.roll_id') > 4 && AuthComponent::user('Testingcomp.extern') == 0){
				$ReportnumberTestingCompOption['Reportnumber.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
				array_push($options, $ReportnumberTestingCompOption);
			} else {
				$ReportnumberTestingCompOption = null;
			}

			if(AuthComponent::user('Testingcomp.extern') == 1) {
							if(Configure::check('ShowExternReportStatus') == true && Configure::read('ShowExternReportStatus') <> ''){
					$options['Reportnumber.status >='] = Configure::read('ShowExternReportStatus') ;
				}else{$options['Reportnumber.status'] = 2;}
			}
			if(count($CascadeChildrenList) > 0) $options['Reportnumber.cascade_id'] = $CascadeChildrenList;

			$this->Reportnumber->recursive = -1;
			$reportnumbers = $this->Reportnumber->find('all', array(
														'conditions' => $options,
														'order' => array('id DESC')
													)
												);

			$options = array(
						'fields' => array('id','projektname'),
						'conditions' => array(
								'Topproject.id' => $this->Autorisierung->ConditionsTopprojects(),
							)
						);

			$this->Topproject->recursive = -1;
			$topproject = $this->Topproject->find('list', $options);

			$this->Reportnumber->Testingmethod->recursive = -1;
			$testingmethods = $this->Reportnumber->Testingmethod->find('list');

			$this->Reportnumber->Report->recursive = -1;
			$reports = $this->Reportnumber->Report->find('list');

			$response = array();

			if(count($reportnumbers) == 0){
				$this->set('test',json_encode($response));
				return;
			}

			$this->Reportnumber->Order->recursive = -1;

			foreach($reportnumbers as $_key => $_reportnumbers){

				$CascadeForBread = array_reverse($this->Navigation->CascadeGetBreads(intval($_reportnumbers['Reportnumber']['cascade_id'])));

				$_url  = null;
				$_url .= '/'.$_reportnumbers['Reportnumber']['topproject_id'];
				$_url .= '/'.$_reportnumbers['Reportnumber']['cascade_id'];
				$_url .= '/'.$_reportnumbers['Reportnumber']['order_id'];
				$_url .= '/'.$_reportnumbers['Reportnumber']['report_id'];
				$_url .= '/'.$_reportnumbers['Reportnumber']['id'];

				$_value  = null;
				$_value .= $_reportnumbers['Reportnumber']['year'] . '-';
				$_value .= $_reportnumbers['Reportnumber']['number'] . ' (';
				$_value .= $testingmethods[$_reportnumbers['Reportnumber']['testingmethod_id']] . ') ';

				if(count($CascadeForBread) > 0){
					foreach($CascadeForBread as $_key => $_CascadeForBread){
						$_value .= ' > ' . $_CascadeForBread['Cascade']['discription'];

					}
				}

				if(isset($reports[$_reportnumbers['Reportnumber']['report_id']])){
					$_value .= ' > ' . $reports[$_reportnumbers['Reportnumber']['report_id']];
				}

				array_push($response, array(
									'key' => $_url,
									'value' => $_value
									)
								);
			}
		}

		if(isset($this->request->data['targetcontroller'])) {
			if($this->request->data['targetcontroller'] == 'reportnumbers' && $this->request->data['targetation'] == 'index'){

				$term = Sanitize::clean($this->request->data['term']);
				$conditions['limit'] = 20;
				$conditions['order'] = array('Order.created DESC');
				$conditions['conditions']['Order.auftrags_nr LIKE'] = '%'.$term.'%';
				$conditions['conditions'] ['Order.deleted'] = 0;

				if($projectID > 0) $conditions['conditions']['Order.topproject_id'] = $projectID;

				$this->Reportnumber->Order->recursive = 0;
				$order = $this->Reportnumber->Order->find('all',$conditions);

				$response = array();

				if(count($order) == 0){
					$this->set('test',json_encode($response));
					return;
				}

				foreach($order as $_key => $_order) {

					$CascadeForBread = array_reverse($this->Navigation->CascadeGetBreads(intval($_order['Order']['cascade_id'])));

					$_url  = null;
					$_url .= 'reportnumbers/index';
					$_url .= '/'.$_order['Order']['topproject_id'];
					$_url .= '/'.$_order['Order']['cascade_id'];
					$_url .= '/'.$_order['Order']['id'];

					$_value  = null;
					$_value .= $_order['Order']['auftrags_nr'];

					if(count($CascadeForBread) > 0){
						foreach($CascadeForBread as $_key => $_CascadeForBread){
							$_value .= ' > ' . $_CascadeForBread['Cascade']['discription'];

						}
					}

					array_push($response, array(
										'key' => $_url,
										'value' => $_value
										)
									);

				}


			}
		}

		$this->set('test',json_encode($response));

	}

	public function settings() {

		$this->layout = 'modal';

		$menues = array(
					'reports' => array(
						'haedline' => null,
            			'controller' => 'reports',
						'discription' => __('list reports',true),
						'actions' => 'index',
						'params' => null
						),
					'testingcomps' => array(
						'haedline' => null,
            			'controller' => 'testingcomps',
						'discription' => __('list user groups',true),
						'actions' => 'index',
						'params' => null
						),
/*
					'users' => array(
						'haedline' => null,
            			'controller' => 'users',
						'discription' => __('list user',true),
						'actions' => 'index',
						'params' => null
						),
*/
					'testingmethods' => array(
						'haedline' => null,
            			'controller' => 'testingmethods',
						'discription' => __('list test methods',true),
						'actions' => 'index',
						'params' => null
						),
					'topprojects_status' => array(
						'haedline' => null,
            			'controller' => 'topprojects',
						'discription' => __('Closed projects',true),
						'actions' => 'status',
						'params' => null
						),
					'topprojects_deleted' => array(
						'haedline' => null,
            			'controller' => 'topprojects',
						'discription' => __('Deleted projects',true),
						'actions' => 'deleted',
						'params' => null
						),
					'changelogs_add' => array(
						'haedline' => null,
            			'controller' => 'changelogs',
						'discription' => __('Create').' '.__('Changelog'),
						'actions' => 'add',
						'params' => null
					),
					'changelogs_view' => array(
						'haedline' => null,
									'controller' => 'changelogs',
						'discription' => __('Show %s', __('Changelog')),
						'actions' => 'view',
						'params' => null
						)
					);


		$SettingsArray = array();
		$this->set('SettingsArray', $SettingsArray);
		$this->set('menues', $menues );
		$this->render('/Topprojects/settings');
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {

		$this->layout = 'modal';

		if (!$this->Topproject->exists($id)) {
			throw new NotFoundException(__('Invalid topproject'));
		}

		$options =
			array(
				'conditions' => array(
					'Topproject.'.$this->Topproject->primaryKey => $id
				),
				'contain' => array(
					'Testingcomp' => array(
						'conditions' => array(
							'Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps()
						)
					),
					'Report' => array()
				)
			);

		$_topproject = $this->Topproject->find('first', $options);
		$this->Autorisierung->ConditionsTesting($_topproject['Topproject']['id'],'conditionsTopprojects');
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set('topproject', $_topproject);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {

		$this->layout = 'modal';

		if (isset($this->request->data['Topproject'])) {

			$this->loadModel('Cascade');

			$this->Topproject->create();

			//Der ganze Subdevisionkram muss raus
			$this->request->data['Topproject']['subdivision'] = 0;
			$this->request->data['Topproject']['email'] = '';
//pr($this->request->data);
//die();
			if($this->Topproject->save($this->request->data)) {

				$id = $this->Topproject->getInsertID();

				$this->Drops->MasterDropdownsProject($id);

				$conditionsTopprojects = $this->Session->read('conditionsTopprojects');
				$conditionsTopprojects[$id] = $id;
				$this->Session->write('conditionsTopprojects', array_unique($conditionsTopprojects));

				$CascadeData['Cascade']['topproject_id'] = $id;
				$CascadeData['Cascade']['discription'] = $this->request->data['Topproject']['projektname'];
				$CascadeData['Cascade']['status'] = 1;
				$CascadeData['Cascade']['level'] = 0;
				$CascadeData['Cascade']['parent'] = 0;
				$CascadeData['Cascade']['child'] = 0;
//				$CascadeData['Cascade']['orders'] = 0;
				$CascadeData['Testingcomp']['Testingcomp'] = $this->request->data['Testingcomp']['Testingcomp'];

				$this->Cascade->create();
				$this->Cascade->save($CascadeData);

				$this->Autorisierung->Logger($id,$this->request->data);

				$options =
					array(
						'conditions' => array(
							'Topproject.'.$this->Topproject->primaryKey => $id
						),
						'contain' => array(
							'Testingcomp' => array(
								'conditions' => array(
								'Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps()
								)
							),
							'Report' => array()
						)
					);

				$this->request->data = $this->Topproject->find('first', $options);

				$this->request->projectvars['VarsArray'][0] = $id;

				$this->Autorisierung->ConditionsStart();
				$this->Flash->success(__('Topproject was saved', array('key' => 'success')));
				$FormName['controller'] = 'topprojects';
				$FormName['action'] = 'edit';
				$Terms = implode('/',$this->request->projectvars['VarsArray']);
				$FormName['terms'] = $Terms;
				$this->set('FormName',$FormName);

			} else {
				$this->Flash->error(__('Topproject could not be saved. Please, try again.', array('key' => 'error')));
			}
		}

		$this->loadModel('Testingcomp');
		$this->loadModel('Report');
		$this->Report->recursive = -1;
		$this->Testingcomp->recursive = -1;

		$reports = $this->Report->find('list',array('fields'=>array('id','name')));
		$testingcomps = $this->Testingcomp->find('list',array('fields'=>array('id','name')));

		$this->request->data['Testingcomp']['selected'] = array(AuthComponent::user('testingcomp_id'));
		$this->request->data['Topproject']['add_master_dropdowns'] = 0;

		$this->set(compact('testingcomps', 'reports'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
 public function edit($id = null) {

	 $this->layout = 'modal';

	 if (!$this->Topproject->exists($id)) {
		 throw new NotFoundException(__('Invalid topproject'));
	 }

	 if (isset($this->request->data['Topproject'])) {
		 $this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['id'],'conditionsTopprojects');

		 if ($this->Topproject->save($this->request->data)) {

			 $this->Drops->MasterDropdownsProject($id);
			 $this->Drops->UpdateMasterDropdownsProject($id);

			 $status = $this->request->data['Topproject']['status'];
			 $this->Data->ProjectStatus($this->request->data['Topproject']['id'],$status,'deactive');

			 $this->Session->delete('TopprojectSubdivision.' . $id);

			 $this->Autorisierung->Logger($id,$this->request->data);
			 $this->Autorisierung->ConditionsStart();
			 $this->Flash->success('Topproject was saved', array('key' => 'success'));

			 $FormName['controller'] = 'topprojects';
			 $FormName['action'] = 'index';
			 $Terms = implode('/',$this->request->projectvars['VarsArray']);
			 $FormName['terms'] = $Terms;
			 $this->set('FormName',$FormName);
		 } else {
			 $this->Flash->error('Topproject could not be saved. Please, try again.', array('key' => 'error'));
		 }
	 }

	 $options =
	 array(
		 'conditions' => array(
			 'Topproject.'.$this->Topproject->primaryKey => $id
		 ),
		 'contain' => array(
			 'Testingcomp' => array(
				 'conditions' => array(
					 'Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps()
				 )
			 ),
			 'Report' => array()
		 )
	 );

	 $SubdivisionMax = Configure::read('SubdivisionMax');

	 $steps = Configure::read('SubdivisionValues');

	 $status = array(0 => __('open',true),1 => __('closed',true));

	 $Topproject = $this->Topproject->find('first', $options);

	 $Topproject = $this->Data->BelongsToManySelected($Topproject,'Topproject','Report',array('ReportsTopprojects','report_id','topproject_id'));
	 $Topproject = $this->Data->BelongsToManySelected($Topproject,'Topproject','Testingcomp',array('TestingcompsTopprojects','testingcomp_id','topproject_id'));

	 $Topproject = $this->Drops->CollectProjectMasterDropdowns($Topproject);

	 $this->request->data = $Topproject;
	 $this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['id'],'conditionsTopprojects');

	 $this->loadModel('Testingcomp');
	 $this->loadModel('Report');
	 $this->Report->recursive = -1;
	 $this->Testingcomp->recursive = -1;

	 $reports = $this->Report->find('list',array('fields'=>array('id','name')));
	 $testingcomps = $this->Testingcomp->find('list',array('fields'=>array('id','name')));

	 $this->set('status', $status);
	 $this->set('steps', $steps);
	 $this->set(compact('testingcomps', 'reports'));
 }

	public function subdevisions($id = null) {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$reportID = $this->request->projectvars['VarsArray'][4];

		$this->layout = 'blank';

		if (!$this->Topproject->exists($projectID)) {
			throw new NotFoundException(__('Invalid topproject'));
		}

		if(isset($this->request->data['Topproject']) && $this->request->is('post')) {

			$this->Autorisierung->ConditionsTesting($projectID,'conditionsTopprojects');

			$topproject = $this->Topproject->find('first',array('conditions' => array('Topproject.id' => $projectID)));

			$this->loadModel('Deliverynumber');
			$this->Deliverynumber->recursive = -1;
			$deliverynumber = $this->Deliverynumber->find('first',array('conditions' => array('Deliverynumber.topproject_id' => $projectID)));

			$this->request->data['Topproject']['identification'] = $topproject['Topproject']['identification'];
			$this->Topproject->id = $projectID;

			$SessionSubdevision = $this->Session->read('TopprojectSubdivision.' . $projectID);

			if(isset($SessionSubdevision) && $SessionSubdevision > 0) {

				$controller = $this->request->data['Topproject']['controller'];
				$action = $this->request->data['Topproject']['action'];

				$FormName = array(
							'controller' => $controller,
							'action' => $action
						);

				$FormName['terms'] = $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2].'/'.$this->request->projectvars['VarsArray'][3].'/'.$this->request->projectvars['VarsArray'][4].'/'.$this->request->projectvars['VarsArray'][5];

				$FormName = array(
					'controller' => 'topprojects',
					'action' => 'index',
					//'terms' => $deliverynumber['Deliverynumber']['topproject_id']
				);

				$this->set('FormName', $FormName);
			} else {
				$this->Session->setFlash(__('Topproject could not be saved. Please, try again.'));
			}
		}
	}

	public function reopen($id = null) {
		$this->change_status($id,0,'deactive');
	}

	public function restore($id = null) {
		$this->change_status($id,0,'deactive');
	}

	protected function change_status($id,$status,$row) {
		$this->layout = 'modal';

		if (!$this->Topproject->exists($id)) {
			throw new NotFoundException(__('Invalid topproject'));
		}

		$this->Autorisierung->ConditionsTesting($id,'conditionsTopprojects');

		$this->Topproject->recursive = -1;
		$this->request->data = $this->Topproject->find('first',array('conditions' => array('Topproject.id' => $id)));
		$this->request->data['Topproject']['status'] = $status;

		if ($this->Topproject->updateAll(array('Topproject.status' => 0),array('Topproject.id' => $id))) {

			$this->Data->ProjectStatus($id,$status,$row);

			$this->Autorisierung->Logger($id,array('Topproject.status' => 0));
			$this->Autorisierung->ConditionsStart();
			$this->Session->setFlash(__('Topproject was reopened'));
			$this->set('afterEDIT', $this->Navigation->afterEDIT('topprojects/index',0));

		} else {
			$this->Session->setFlash(__('Topproject could not be reopened. Please, try again.'));
		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'status', 'terms' => null);
		$this->set('SettingsArray',$SettingsArray);
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {

		$this->layout = 'modal';

		$this->Topproject->id = $id;
		if (!$this->Topproject->exists()) {
			throw new NotFoundException(__('Invalid topproject'));
		}

//		$this->Autorisierung->ConditionsTopprojectsTest($id);

		$afterEdit = $this->request->webroot.$this->request->params['controller'];

		$this->Topproject->recursive = -1;
		$topprojects = $this->Topproject->find('first', array(
					'conditions' => array(
						'Topproject.'.$this->Topproject->primaryKey => $id
					)));

		if(isset($this->request->data['Topproject']['id'])){

			if($this->Topproject->updateAll(array('Topproject.status' => 2),array('Topproject.id' => $this->request->data['Topproject']['id']))) {

				$this->Data->ProjectStatus($this->request->data['Topproject']['id'],1,'deactive');

				$this->Autorisierung->Logger($topprojects['Topproject']['id'],$topprojects);
				$this->Session->setFlash(__('Topproject was deleted'));
				$this->Autorisierung->ConditionsStart();
//				pr($this->Navigation->afterEDIT('index',0));
				$this->set('afterEDIT', $this->Navigation->afterEDIT('',0));
			}
			else {
				$this->Session->setFlash(__('Topproject was not deleted'));
			}
		}
		$this->request->data = $topprojects;
	}
}
