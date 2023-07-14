<?php
App::uses('AppController', 'Controller');
/**
 * Testingmethods Controller
 *
 * @property Receiver $Receiver
 */
class ReeiversController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue','Xml');
	protected $writeprotection = false;

	public $helpers = array('Lang','Navigation','JqueryScripte');
	public $layout = 'modal';

	function beforeFilter() {
		App::import('Vendor', 'Authorize');
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		$this->Navigation->ReportVars();
		$this->loadModel('User');
		$this->Autorisierung->Protect();
		$this->Navigation->ajaxURL();
		$this->Lang->Choice();
		$this->Lang->Change();
		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('menues', $this->Navigation->Menue());
//		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->loadModel('User');
		$this->loadModel('Testingcomp');
		$testingcomps = $this->Testingcomp->find('list');
		$users = $this->User->find('list');

		//$this->Testingmethod->Report->Reportlock->recursive = -1;
		//$this->writeprotection = 0 < $this->Receiver->Report->Reportlock->find('count', array('conditions'=>array('Reportlock.topproject_id'=>$this->request->projectID, 'Reportlock.report_id'=>$this->request->reportID)));
		$this->set('writeprotection', $this->writeprotection);

		$this->set(compact('users'));
		$this->set(compact('testingcomps'));

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterFilter() {
		$this->Navigation->lastURL();
	}

/**
 * index method
 *
 * @return void
 */
	public function index() {

		$this->layout = 'modal';

		$this->Receiver->recursive = 0;

			$SettingsArray = array();
			$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
//			$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'qualifications','action' => 'add', 'terms' => null);
//			$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//			$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

		$this->set('SettingsArray', $SettingsArray);


		$this->set('testingmethods', $this->paginate());
	}

	public function listing() {
		
		if($this->writeprotection) {
			$this->set('error', __('Report is writeprotected - changes will not be saved.'));
			$this->render('/closemodal','modal');
			return;
		}

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		if(isset($this->request->projectvars['VarsArray'][4]) && $this->request->projectvars['VarsArray'][4] > 0) $reportnumberID = $this->request->projectvars['VarsArray'][4];
		$modaloutput = false;

		$this->layout = 'modal';

		if(isset($this->request->params['pass'][3]) && $this->request->params['pass'][3] == 1){
			$modaloutput = true;
		}

		$reportsArray = $this->Navigation->SubMenue('Report','Topproject',__('Create', true),'listing','testingmethods',$this->Session->read('projectURL'));

		$optionReport = array(
						'conditions' => array(
								'Report.id' => $reportID
								)
							);

		$this->Testingmethod->Report->recursive = -1;
		$report = $this->Testingmethod->Report->find('first',$optionReport);
		$this->Session->write('reportURL', $report['Report']['id']);
		$this->loadModel('Topproject');
		$this->loadModel('Order');
		$this->Topproject->recursive = 0;
		$this->Order->recursive = -1;
		$optionTopproject = array('conditions' => array('Topproject.id' => $projectID));
		$topproject = $this->Topproject->find('first',$optionTopproject);

		if($orderID > 0){
			$optionOrder = array('conditions' => array('Order.id' => $orderID));
			$order = $this->Order->find('first',$optionOrder);
		}

		$menueArray = array(
			'general' => array(
				'haedline' => __('Actions', true),
				'controller' => 'reportnumbers',
				'discription' => array('Search Report','List Report'),
				'actions' => array('index','index/'.$projectID.'/'.$orderID),
				'params' => array(null,null),
			));

		$options =
			array(
				'order' => array('Testingmethod.verfahren ASC'),
				'conditions' => array(
					'Testingmethod.id' => $this->Autorisierung->ConditionsVariabel('Testingmethod','Report',$reportID),
					'Testingmethod.enabled' => 1
				)
			);

		// TODO: noch die Unterscheidung nach Unterordnungen und �berordnungen
		if($this->request->reportnumberID > 0 && isset($this->request->data['linked']) && $this->request->data['linked'] == 1) {
			$this->Autorisierung->IsThisMyReport($reportnumberID);
			$this->loadModel('Reportnumber');
			$this->Reportnumber->recursive = 0;

			$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID)));
			$options['conditions']['Testingmethod.allow_children'] = !intval($reportnumber['Testingmethod']['allow_children']);
			$this->set('allow_children', !intval($reportnumber['Testingmethod']['allow_children']));
		}

		// Falls es keine Pr�fverfahren zum anzeigen gibt, gehts zur�ck
		if(count($this->Receiver->find('all', $options)) == 0) {
				$this->Session->setFlash($report['Report']['name'].__(' has no testingmethod', true));
				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->Session->read('lastURL'),2000));
			};

		$this->set('projectID', $projectID);
		$this->set('orderID', $orderID);
		$this->set('reportID', $reportID);
		$this->set('SettingsArray', array());

		$this->set('reportName', $report['Report']['name']);
		$this->set('reportid', $report['Report']['id']);
		$this->set('menues', $menueArray);
		$this->set('reports', $reportsArray);

		$this->Receiver->recursive = 0;
		$testingmethods = $this->Receiver->find('all', $options);

		$testingmethodIncIDs = array();
		$testingmethod = array();

	  // Die benötigten Variablen f�r den Link werden hinzugef�gt
		foreach($testingmethods as $_key => $_testingmethods) {
			
			if($this->Xml->TestXmlExists($_testingmethods['Testingmethod']['value']) == false){
				unset($testingmethods[$_key]);
				continue;
			} else {
			}

			$_testingmethods['Testingmethod']['projectID'] = $projectID;
			$_testingmethods['Testingmethod']['orderID'] = $orderID;
			$_testingmethods['Testingmethod']['reportID'] = $reportID;
			$testingmethodIncIDs[] = $_testingmethods;
		}

		// Es werden die Testingmethods herausgefilter
		// f�r welche es keine XML-Vorlage gibt
		$dir = new Folder(Configure::read('xml_folder') . 'testingmethod');
		foreach($testingmethodIncIDs as $_testingmethods) {
			$files = $dir->findRecursive($_testingmethods['Testingmethod']['value'].'.xml');
			if(count($files) > 0) {
				$testingmethod[] = $_testingmethods;
			}
		}

		if(isset($order['Order']['status']) && $order['Order']['status'] == 1){
			$testingmethod = array();
		}

		$this->set('testingmethods', $testingmethod);

		if($modaloutput == true){
			$this->render('/Testingmethods/listingmodal');
		}
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Receiver->exists($id)) {
			throw new NotFoundException(__('Invalid testingmethod'));
		}
		$this->Autorisierung->ConditionsTest('TestingmethodsTopprojects','testingmethod_id',$id);

		$options =
			array(
				'conditions' => array(
					'Testingmethod.'.$this->Receiver->primaryKey => $id
				),
				'contain' => array(
					
					'Topproject' => array(
						'conditions' => array(
							'Topproject.id' => $this->Autorisierung->ConditionsTopprojects()
						)
					)
				)
			);

		$result = $this->Receiver->find('first', $options);

		$this->set('breads', $this->Navigation->Breads(null));
		$this->set('receiver', $this->Receiver->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if (isset($this->request->data['Receiver'])) {
			$this->Receiver->create();
			$this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['Topproject'],'conditionsTopprojects');
			if ($this->Receiver->save($this->request->data)) {
				$this->Session->setFlash(__('Testingmethod was saved'));
				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->request->params['controller'],5000));
			} else {
				$this->Session->setFlash(__('Testingmethod could not be saved. Please, try again.'));
			}
		}
		$OptionsTopprojectsSelect = array('conditions' => array('Topprojects.id' => $this->Autorisierung->ConditionsTopprojects()));
		$OptionsTopprojects = array('conditions' => array('id' => $this->Autorisierung->ConditionsTopprojects()));

		$topprojects = $this->Receiver->find('list',$OptionsTopprojects);
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set(compact('topprojects'));
		$this->set('topprojects_select', $this->SelectValue->SelectData('Topprojects','projektname',$OptionsTopprojectsSelect));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Receiver->exists($id)) {
			throw new NotFoundException(__('Invalid Receiver'));
		}
		if (isset($this->request->data['Receiver']) || $this->request->is('put')) {
			$this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['Topproject'],'conditionsTopprojects');
			if ($this->Receiver->save($this->request->data)) {
				$this->Session->setFlash(__('Receiver was saved'));
				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->request->params['controller'],5000));
			} else {
				$this->Session->setFlash(__('Testingmethod could not be saved. Please, try again.'));
			}
		} else {

		$OptionsReceiver =
			array(
				'conditions' => array(
					'Receiver.'.$this->Receiver->primaryKey => $id
				),
				'contain' => array(
					
					'Topproject' => array(
						'conditions' => array(
							'Topproject.id' => $this->Autorisierung->ConditionsTopprojects()
						)
					)
				)
			);

			$this->request->data = $this->Receiver->find('first', $OptionsReceiver);
			$_topprojectIdArray = array();

			if(count($this->request->data['Topproject']) > 0){
				foreach($this->request->data['Topproject'] as $_topproject) {
					$_topprojectIdArray[] = $_topproject['id'];
				}
			}

			$this->Autorisierung->ConditionsTesting($_topprojectIdArray,'conditionsTopprojects');

		}

		$OptionsTopprojectsSelect = array('conditions' => array('Topprojects.id' => $this->Autorisierung->ConditionsTopprojects()));
		$OptionsTopprojects = array('conditions' => array('id' => $this->Autorisierung->ConditionsTopprojects()));
		$topprojects = $this->Receiver->Topproject->find('list', $OptionsTopprojects);
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set(compact('topprojects'));
		$this->set('topprojects_select', $this->SelectValue->SelectData('Topprojects','projektname',$OptionsTopprojectsSelect));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Receiver->id = $id;

		$options =
			array(
				'conditions' => array(
					'Receiver.'.$this->Receiver->primaryKey => $this->Receiver->id
				),
				'contain' => array(
					
					'Topproject' => array(
						'conditions' => array(
							'Topproject.id' => $this->Autorisierung->ConditionsTopprojects()
						)
					)
				)
			);

		$_testingmethod = $this->Receiver->find('first', $options);

		$_topprojectIdArray = array();
		foreach($_testingmethod['Topproject'] as $_topproject) {
			$_topprojectIdArray[] = $_topproject['id'];
		}

		$this->Autorisierung->ConditionsTesting($_topprojectIdArray,'conditionsTopprojects');
		if (!$this->Receiver->exists()) {
			throw new NotFoundException(__('Invalid testingmethod'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Receiver->delete()) {
			$this->Session->setFlash(__('Receiver was deleted'));
			$this->set('afterEDIT', $this->Navigation->afterEDIT($this->request->params['controller'],5000));
		}
		$this->Session->setFlash(__('Receiver was not deleted'));
		$this->set('breadcrumbs', $this->Navigation->BreadcrumbReports(null,array('start')));
		$this->set('afterEDIT', $this->Navigation->afterEDIT($this->request->params['controller'],5000));
	}
}
