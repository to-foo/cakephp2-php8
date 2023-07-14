<?php
App::uses('AppController', 'Controller');
/**
 * Qualifications Controller
 *
 * @property Qualification $Qualification
 */
class QualificationsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit');
	public $helpers = array('Lang','Navigation','JqueryScripte');
	public $layout = 'modal';

	function beforeFilter() {
		App::import('Vendor', 'Authorize');
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
		$this->set('breads', $this->Navigation->Breads(null));
		
		$this->Qualification->recursive = 0;

			$SettingsArray = array();
			$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
			$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'qualifications','action' => 'add', 'terms' => null);
//			$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//			$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
		
		$this->set('SettingsArray', $SettingsArray);

		$this->set('qualifications', $this->paginate(
						array(
							'Qualification.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
							'Qualification.user_id' => $this->Autorisierung->UsertoFind()
							)
						)
					);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Qualification->exists($id)) {
			throw new NotFoundException(__('Invalid qualification'));
		}
		$options = array('conditions' => array('Qualification.' . $this->Qualification->primaryKey => $id));

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'qualifications','action' => 'index', 'terms' => null);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'qualifications','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
	
		$this->set('SettingsArray', $SettingsArray);

		$this->set('qualification', $this->Qualification->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if (isset($this->request->data['Qualification'])) {
			$this->Qualification->create();
			if ($this->Qualification->save($this->request->data)) {
				$id = $this->Qualification->getInsertID();	
				$this->Autorisierung->Logger($id,$this->request->data);
				$this->Session->setFlash(__('The qualification was saved'));
				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->request->params['controller'],5000));
			} else {
				$this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
			}
		}
		$testingmethods = $this->Qualification->Testingmethod->find('list');

		$roll = $this->Auth->user('Roll');
		
		$usersOption = null;
		$testingcompsOption = null;

		if($roll['id'] < 5){
		}
		else {
			$usersOption = array('testingcomp_id' => $this->Auth->user('testingcomp_id'));
			$testingcompsOption = array('id' => $this->Auth->user('testingcomp_id'));
		}
		
		$users = $this->Qualification->User->find('list', array(
				'conditions' => $usersOption
			)
		);
		$testingcomps = $this->Qualification->Testingcomp->find('list', array(
				'conditions' => $testingcompsOption
			)
		);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'qualifications','action' => 'index', 'terms' => null);
//		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'qualifications','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
		
		$this->set('SettingsArray', $SettingsArray);

		$this->set(compact('testingmethods', 'users', 'testingcomps'));
	}

	public function addforexaminierer($id = null) {

		$dropdownID = $this->Sicherheit->Numeric($this->request->params['pass'][0]);
		$examiniererID = $this->Sicherheit->Numeric($this->request->params['pass'][1]);

		if (isset($this->request->data['Qualification'])) {
			$this->Qualification->create();
			if ($this->Qualification->save($this->request->data)) {
				$id = $this->Qualification->getInsertID();	
				$this->Autorisierung->Logger($examiniererID,$this->request->data);
				$this->Session->setFlash(__('The qualification was saved'));
				$this->set('close', 1);
				$this->set('dropdownID', $dropdownID);
				$this->set('examiniererID', $examiniererID);
				$this->render('addforexaminierer','modal');
				return;
			} else {
				$this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
			}
		}

		$testingmethods = $this->Qualification->Testingmethod->find('list');

		$testingcomps = $this->Qualification->Testingcomp->find('list', array(
			'conditions' => array(
				'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','testingcomp_id')
				)
			)
		);

		$this->set('dropdownID', $dropdownID);
		$this->set('examiniererID', $examiniererID);
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set(compact('testingmethods', 'users', 'testingcomps'));
		$this->render('addforexaminierer','modal');
	}

	public function editforexaminierer($id = null) {

		$dropdownID = $this->Sicherheit->Numeric($this->request->params['pass'][0]);
		$examiniererID = $this->Sicherheit->Numeric($this->request->params['pass'][1]);
		$qualificationID = $this->Sicherheit->Numeric($this->request->params['pass'][2]);

		if (isset($this->request->data['Qualification'])) {
			if ($this->Qualification->save($this->request->data)) {
				$this->Autorisierung->Logger($examiniererID,$this->request->data);
				$this->Session->setFlash(__('The qualification was saved'));
				$this->set('close', 1);
				$this->set('dropdownID', $dropdownID);
				$this->set('examiniererID', $examiniererID);
				$this->render('editforexaminierer','modal');
				return;
			} else {
				$this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
			}
		}

		$options = array('conditions' => array('Qualification.' . $this->Qualification->primaryKey => $qualificationID));
		$this->request->data = $this->Qualification->find('first', $options);

		$testingmethods = $this->Qualification->Testingmethod->find('list');

		$testingcomps = $this->Qualification->Testingcomp->find('list', array(
			'conditions' => array(
				'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','testingcomp_id')
				)
			)
		);

		$this->set('dropdownID', $dropdownID);
		$this->set('examiniererID', $examiniererID);
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set(compact('testingmethods', 'users', 'testingcomps'));
		$this->render('editforexaminierer','modal');
	}

	public function deleteforexaminierer($id = null) {

		$dropdownID = $this->Sicherheit->Numeric($this->request->params['pass'][0]);
		$examiniererID = $this->Sicherheit->Numeric($this->request->params['pass'][1]);
		$qualificationID = $this->Sicherheit->Numeric($this->request->params['pass'][2]);

		if (isset($this->request->data['Qualification'])) {
			$this->Qualification->recursive = -1;
			pr($this->request->data['Qualification']['id']);
			$this->Qualification->id = $this->request->data['Qualification']['id'];
			if ($this->Qualification->delete()) {
				$this->Autorisierung->Logger($examiniererID,$this->request->data);
				$this->Session->setFlash(__('The qualification was deleted'));
				$this->set('close', 1);
				$this->set('dropdownID', $dropdownID);
				$this->set('examiniererID', $examiniererID);
				$this->render('deleteforexaminierer','modal');
				return;
			} else {
				$this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
			}
		}

		$options = array('conditions' => array('Qualification.' . $this->Qualification->primaryKey => $qualificationID));
		$this->request->data = $this->Qualification->find('first', $options);

		$testingmethods = $this->Qualification->Testingmethod->find('list');

		$testingcomps = $this->Qualification->Testingcomp->find('list', array(
			'conditions' => array(
				'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','testingcomp_id')
				)
			)
		);

		$this->set('dropdownID', $dropdownID);
		$this->set('examiniererID', $examiniererID);
		$this->set('breads', $this->Navigation->Breads(null));
		$this->set(compact('testingmethods', 'users', 'testingcomps'));
		$this->render('deleteforexaminierer','modal');
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Qualification->exists($id)) {
			throw new NotFoundException(__('Invalid qualification'));
		}
		if (isset($this->request->data['Qualification']) || $this->request->is('put')) {
			if ($this->Qualification->save($this->request->data)) {
				$this->Autorisierung->Logger($id,$this->request->data);
				$this->Session->setFlash(__('The qualification was saved'));
//				$this->set('afterEDIT', $this->Navigation->afterEDIT($this->request->params['controller'],5000));
			} else {
				$this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Qualification.' . $this->Qualification->primaryKey => $id));
			$this->request->data = $this->Qualification->find('first', $options);
		}
		$testingmethods = $this->Qualification->Testingmethod->find('list');

		$users = $this->Qualification->User->find('list', array(
			'conditions' => array(
				'testingcomp_id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','testingcomp_id')
				)
			)
		);

		$testingcomps = $this->Qualification->Testingcomp->find('list', array(
			'conditions' => array(
				'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','testingcomp_id')
				)
			)
		);

			$SettingsArray = array();
			$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'qualifications','action' => 'index', 'terms' => null);
			$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'qualifications','action' => 'add', 'terms' => null);
//			$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//			$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
		
		$this->set('SettingsArray', $SettingsArray);


		$this->set(compact('testingmethods', 'users', 'testingcomps'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Qualification->id = $id;
		if (!$this->Qualification->exists()) {
			throw new NotFoundException(__('Invalid qualification'));
		}

		$this->Qualification->recursive = -1;
		$qualifications = $this->Qualification->find('first', array(
					'conditions' => array(
						'Qualification.'.$this->Qualification->primaryKey => $id
					)));

		if ($this->Qualification->delete()) {
			$this->Autorisierung->Logger($id,$qualifications);
			$this->Session->setFlash(__('Qualification was deleted'));
		}
		else {
			$this->Session->setFlash(__('Qualification was not deleted'));
		}
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'qualifications','action' => 'index', 'terms' => null);
//		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'users','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
		
		$this->set('SettingsArray', $SettingsArray);
		
	}
}
