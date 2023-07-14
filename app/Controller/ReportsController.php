<?php
App::uses('AppController', 'Controller');
/**
 * Reports Controller
 *
 * @property Report $Report
 */
class ReportsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue','Data');

	public $helpers = array('Lang','Navigation','JqueryScripte');
	public $layout = 'modal';

	function beforeFilter() {
		App::import('Vendor', 'Authorize');
		$this->loadModel('User');
		$this->Autorisierung->Protect();
		$this->Navigation->ReportVars();
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

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->layout = 'modal';
		$this->include_index();
	}

	protected function include_index() {
		$this->Report->recursive = 0;
//		$Report = $this->paginate(array('Report.id' => $this->Autorisierung->Conditions('ReportsTopprojects','report_id')));
		$Report = $this->paginate();

		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'reports','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

		$this->set('SettingsArray', $SettingsArray);

		$this->set('breads', $this->Navigation->Breads(null));
		$this->set('reports', $Report);
		$this->render('index','modal');
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {

		if (!$this->Report->exists($id)) {
			throw new NotFoundException(__('Invalid report'));
		}

		$this->Report->recursive = -1;

		$this->Autorisierung->ConditionsTest('ReportsTopprojects','report_id',$id);
		$options = array('conditions' => array('Report.' . $this->Report->primaryKey => $id));

		$options =
			array(
				'conditions' => array(
					'Report.'.$this->Report->primaryKey => $id
				),
				'contain' => array(
					'Testingmethod' => array()
				)
			);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'reports','action' => 'index', 'terms' => null);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'reports','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

		$this->set('SettingsArray', $SettingsArray);

		$this->set('report', $this->Report->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {

		$this->layout = 'modal';

		$contitions = array('conditions' => array('id' => $this->Autorisierung->ConditionsTopprojects()));

		if (isset($this->request->data['Report'])) {
			$this->Autorisierung->ConditionsTestingCrUp('conditionsTopprojects',$this->request->data['Topproject']['Topproject']);
			$this->Report->create();

			if ($this->Report->save($this->request->data)) {
				$id = $this->Report->getInsertID();
				$this->Autorisierung->Logger($id,$this->request->data);
				$this->Session->setFlash(__('The report has been saved'));
//				$this->include_index();
			} else {
				$this->Session->setFlash(__('The report could not be saved. Please, try again.'));
			}
		}

		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'reports','action' => 'index', 'terms' => null);


		$testingmethods = $this->SelectValue->SelectData('Testingmethod','verfahren',null);
//		$topprojects = $this->SelectValue->SelectData('Topproject','projektname','conditionsTopprojects');
		$this->set('breads', $this->Navigation->Breads(null));
//		$this->set(compact('topprojects', 'testingmethods'));
		$this->set(compact('testingmethods'));
		$this->set('SettingsArray', $SettingsArray);
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

		$contitions = array('conditions' => array('id' => $this->Autorisierung->ConditionsTopprojects()));

		if (!$this->Report->exists($id)) {
			throw new NotFoundException(__('Invalid report'));
		}
		if (isset($this->request->data['Report']) || $this->request->is('put')) {
//			$this->Autorisierung->ConditionsTest('ReportsTopprojects','report_id',$this->request->data['Report']['id']);
			if ($this->Report->save($this->request->data)) {
				$this->Autorisierung->Logger($this->request->data['Report']['id'],$this->request->data);
				$this->Report->Reportlock->deleteAll(array('Reportlock.report_id'=>$this->request->data['Report']['id']), false, false);

				$user = $this->Auth->user('id');
				$id = $this->request->data['Report']['id'];

				if(!empty($this->request->data['Report']['Reportlock'])){
					$this->Report->Reportlock->saveMany(array_map(
						function($elem) use($user, $id) {
							return array('topproject_id'=>$elem, 'report_id'=>$id, 'user_id'=>$user);
						},
						$this->request->data['Report']['Reportlock']
					));
				}

				$this->Session->setFlash(__('The report has been saved'));

			} else {
				$this->Session->setFlash(__('The report could not be saved. Please, try again.'));
			}
		}

		$options =
			array(
				'conditions' => array(
					'Report.'.$this->Report->primaryKey => $id
				),
				'contain' => array(
					'Topproject' => array(
						'conditions' => $this->Autorisierung->ConditionsTopprojects()),
					'Testingmethod',
				)
			);

			$this->Report->recursive = -1;

			$Report = $this->Report->find('first', $options);
			$this->Report->Reportlock->recursive = 1;

			$Report = $this->Data->BelongsToManySelected($Report,'Report','Testingmethod',array('TestingmethodsReports','testingmethod_id','report_id'));
			$Report = $this->Data->BelongsToManySelected($Report,'Report','Topproject',array('ReportsTopprojects','topproject_id','report_id'));
//			$Report = $this->Data->BelongsToManySelected($Report,'Report','Topproject',array('ReportsTopprojects','topproject_id','report_id'));
			$this->request->data = $Report;

			$this->request->data['Reportlock'] = $this->Report->Reportlock->find('list', array(
				'fields'=>array('Topproject.id', 'Topproject.projektname'),
				'conditions'=>array('Reportlock.report_id'=>$id),
				'joins'=>array(
					array(
						'table'=>'topprojects',
						'alias'=>'Topproject',
						'conditions'=>array('Topproject.id = Reportlock.topproject_id')
					)
				)
			));

			// Das Array für das Settingsmenü
			$SettingsArray = array();
			$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'reports','action' => 'index', 'terms' => null);
			$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'reports','action' => 'add', 'terms' => null);


			$this->set('SettingsArray', $SettingsArray);

			$topprojects = $this->SelectValue->SelectData('Topproject','projektname','conditionsTopprojects');
			$testingmethods = $this->SelectValue->SelectData('Testingmethod','verfahren',null);

			$TopprojectsLocked = array();
			foreach($this->request->data['Topproject']['selected'] as $_key => $_data){
				if(!isset($topprojects[$_key])) continue;
				$TopprojectsLocked[$_key] = $topprojects[$_key];
			}

			$this->set('TopprojectsLocked', $TopprojectsLocked);
			$this->set(compact('topprojects', 'testingmethods'));
	}

	public function save($id = null) {

		$this->layout = 'blank';
		$contitions = array('conditions' => array('id' => $this->Autorisierung->ConditionsTopprojects()));
		if (!$this->Report->exists($id)) {
			throw new NotFoundException(__('Invalid report'));
		}

		$this_id = $this->request->data['this_id'];
		$this->set('this_id',$this_id);

		if($this->Report->save($this->request->data)) {
			$this->Autorisierung->Logger($id,$this->request->data);

			if(isset($this->request->data['Report']['Reportlock']) && is_array($this->request->data['Report']['Reportlock'])) {
				$this->Report->Reportlock->deleteAll(array('Reportlock.report_id'=>$this->request->data['Report']['id']), false, false);

				$user = $this->Auth->user('id');
				$this->Report->Reportlock->saveMany(array_map(
					function($elem) use($user, $id) {
						return array('topproject_id'=>$elem, 'report_id'=>$id, 'user_id'=>$user);
					},
					$this->request->data['Report']['Reportlock']
				));
			}
			$this->set('success',__('The value has been saved.'));
		}
		else {
			$this->set('error',__('The value could not be saved. Please try again!'));
		}
	}
/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {

		$this->Report->id = $id;
		if (!$this->Report->exists()) {
			throw new NotFoundException(__('Invalid report'));
		}

		$this->Report->recursive = -1;
		$reports = $this->Report->find('first', array(
					'conditions' => array(
						'Report.'.$this->Report->primaryKey => $id
					)));

		if ($this->Report->delete()) {
			$this->Autorisierung->Logger($id,$reports);
			$this->Session->setFlash(__('Report deleted'));
			$this->include_index();
		}
		else {
			$this->Session->setFlash(__('Report was not deleted'));
		}

		$this->Report->recursive = 0;
//		$Report = $this->paginate(array('Report.id' => $this->Autorisierung->Conditions('ReportsTopprojects','report_id')));
		$Report = $this->paginate();

		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'reports','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);


		$this->set('SettingsArray', $SettingsArray);

		$this->set('breads', $this->Navigation->Breads(null));
		$this->set('reports', $Report);

	}
}
