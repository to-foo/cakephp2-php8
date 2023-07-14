<?php
App::uses('AppController', 'Controller');
/**
 * Dependencies Controller
 *
 * @property Dependency $Dependency
 * @property AuthComponent $Auth
 * @property AclComponent $Acl
 * @property AutorisierungComponent $Autorisierung
 * @property CookieComponent $Cookie
 * @property NavigationComponent $Navigation
 * @property LangComponent $Lang
 * @property SicherheitComponent $Sicherheit
 * @property XmlComponent $Xml
 * @property DataComponent $Data
 * @property RequestHandlerComponent $RequestHandler
 */
class DependenciesController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Auth', 'Acl', 'Autorisierung', 'Cookie', 'Navigation', 'Lang', 'Sicherheit', 'Xml', 'Data', 'RequestHandler', 'Sicherheit', 'Drops', 'Depentency','Qualification');
	public $layout = 'modal';

	function beforeFilter() {
		// muss man noch ne Funkton draus machen
		$Auth = $this->Session->read('Auth');
		if(!($Auth['User']['id'])){
			$this->redirect(array('controller' => 'users', 'action' => 'logout'));
		}

		App::import('Vendor', 'Authorize');
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$this->request->data['dialog'] = 1;
		$this->request->data['ajax_true'] = 1;
		$this->Autorisierung->Protect();

		$this->loadModel('User');
		$this->loadModel('Topproject');
		$this->Navigation->ajaxURL();
		$this->Lang->Choice();
		$this->Lang->Change();
		$this->Navigation->ReportVars();
		$this->request->lang = $this->Lang->Discription();
		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->set('locale', $this->Lang->Discription());

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
		if($this->request->reportnumberID > 0){
			$this->include_index();
		} else {
			$this->include_modalindex();
		}
	}

	public function include_index() {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->request->projectvars['VarsArray'][4];
		$evalutionID = $this->request->projectvars['VarsArray'][5];
		$dropdownID = $this->request->projectvars['VarsArray'][6];
		$count = $this->request->projectvars['VarsArray'][7];

		if(!$this->Dependency->Dropdown->exists($dropdownID)) {
			$this->render('closemodal');
			return;
		}

		$this->Dependency->recursive = 1;
		$this->Dependency->Dropdown->recursive = 0;
		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = 0;

		$this->set('reportnumber', $reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID))));

		$dropdown = $this->Dependency->Dropdown->find('first', array('fields'=>array('Dropdown.id', 'Dropdown.model', 'Dropdown.field', 'Dropdown.'.$this->Lang->Discription()), 'conditions'=>array('Dropdown.id'=>$dropdownID)));

		$arrayData = $this->Xml->DatafromXml($reportnumber['Testingmethod']['value'],'file',ucfirst($reportnumber['Testingmethod']['value']));
		$this->set('settings', $arrayData['settings']);

		$_dropdownXml = $arrayData['settings']->{$dropdown['Dropdown']['model']}->{$dropdown['Dropdown']['field']};
		$fields = array();
		if(isset($_dropdownXml->dependencies->child)) {
			foreach($_dropdownXml->dependencies->child as $child) {
				$child = $arrayData['settings']->{$dropdown['Dropdown']['model']}->$child;
				$fields[trim($child->key)]['discription'] = trim($child->discription->{$this->Lang->Discription()});
			}
		}
		$conditions_for_this_value = array();

		$conditions = array(
				$conditions_for_this_value,
				'DropdownsValue.dropdown_id' => $dropdownID
		);

		$this->paginate = array_merge($this->paginate, array('conditions'=>$conditions,'order' => array('DropdownsValue.discription' => 'ASC')));
		$dependencies = $this->paginate('DropdownsValue');

		$this->Dependency->recursive = -1;
		$fieldsCount = array();

		foreach($dependencies as $_key => $_dependencies){
			//			pr($_dependencies);
			$dependenciesvalue = $this->Dependency->find('all', array('conditions' => array('Dependency.dropdowns_value_id' => $_dependencies['DropdownsValue']['id'])));
			foreach($dependenciesvalue as $_dependenciesvalue){
				@$fieldsCount[$_dependencies['DropdownsValue']['id']][$_dependenciesvalue['Dependency']['field']]['count']++;
			}
		}

		$this->set('fieldsCount', $fieldsCount);
		$this->set('fields', $fields);
		$this->set('dropdown', $dropdown);
		//		$this->set('dependencies', $this->paginate('Dependency'));
		$this->set('dependencies', $dependencies);

		$SettingsArray = array();
//		$this->request->projectvars['VarsArray'][10] = 0;
		$SettingsArray['dropdownslink'] = array('discription' => __('Dropdowns',true), 'controller' => 'dropdowns','action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);
	}

	public function include_modalindex() {
		$this->layout = 'modal';

		$id = $this->request->projectvars['VarsArray'][6];
		$count = $this->request->projectvars['VarsArray'][7];

		if(!$this->Dependency->Dropdown->exists($id)) {
			$this->render('closemodal');
			return;
		}

		$options = array(
			'conditions' => array(
				'Dropdown.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
				'ODropdown.id' => $id
			),
			'fields'=>array('Dropdown.id', 'Dropdown.linking', 'Dropdown.model', 'Dropdown.field', 'Dropdown.'.$this->Lang->Discription()),
			'joins' =>array(
				array(
					'table' => 'dropdowns',
					'alias' => 'ODropdown',
					'conditions' => array(
						'Dropdown.model = ODropdown.model',
						'Dropdown.field = ODropdown.field',
					)
				)
			)
		);

		$count = count($dropdown = $this->Dependency->Dropdown->find('all', $options));

		$fieldsCount = array();

		if($count > 0) {
			$ModelArray = array($dropdown[0]['Dropdown']['model'], $dropdown[0]['Dropdown']['field']);

			$arrayData = $this->Xml->DatafromXml(strtolower($ModelArray[0]),'file',ucfirst(strtolower($ModelArray[0])));
			$this->set('settings', $arrayData['settings']);

			$_dropdownXml = $arrayData['settings']->{$dropdown[0]['Dropdown']['model']}->{$dropdown[0]['Dropdown']['field']};
			$fields = array();
			if(isset($_dropdownXml->dependencies->child)) {
				foreach($_dropdownXml->dependencies->child as $child) {
					$child = $arrayData['settings']->{$dropdown[0]['Dropdown']['model']}->$child;
					$fields[trim($child->key)]['discription'] = trim($child->discription->{$this->Lang->Discription()});
				}
			}

			$conditions_for_this_value = array();
			$conditions = array(
					$conditions_for_this_value,
					'DropdownsValue.dropdown_id' => $id
			);

			$this->paginate = array_merge($this->paginate, array('conditions'=>$conditions,'order' => array('DropdownsValue.discription' => 'ASC')));
			$dependencies = $this->paginate('DropdownsValue');

			$this->Dependency->recursive = -1;
			foreach($dependencies as $_key => $_dependencies){
				//			pr($_dependencies);
				$dependenciesvalue = $this->Dependency->find('all', array('conditions' => array('Dependency.dropdowns_value_id' => $_dependencies['DropdownsValue']['id'])));
				foreach($dependenciesvalue as $_dependenciesvalue){
					@$fieldsCount[$_dependencies['DropdownsValue']['id']][$_dependenciesvalue['Dependency']['field']]['count']++;
				}
			}
		}

		$this->set('fieldsCount', $fieldsCount);
		$this->set('fields', $fields);
		$this->set('dropdown', $dropdown);
		//		$this->set('dependencies', $this->paginate('Dependency'));
		$this->set('dependencies', $dependencies);

		$SettingsArray = array();
		$this->request->projectvars['VarsArray'][10] = 0;
		$SettingsArray['dropdownslink'] = array('discription' => __('Dropdowns',true), 'controller' => 'dropdowns','action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['backlink'] = array('discription' => __('back to list',true), 'controller' => strtolower(Inflector::pluralize($ModelArray[0])),'action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);
	}

	public function overview() {
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->request->projectvars['VarsArray'][4];
		$evalutionID = $this->request->projectvars['VarsArray'][5];
		$dropdownID = $this->request->projectvars['VarsArray'][6];
		$count = $this->request->projectvars['VarsArray'][7];
		$dropdownvalueID = $this->request->projectvars['VarsArray'][8];
		$linkinOkay = $this->request->projectvars['VarsArray'][9];

		$this->Dependency->recursive = 1;
		$this->Dependency->Dropdown->recursive = -1;
		$thisfieldDiscription = null;
		$thisfield = null;

		if($this->request->reportnumberID > 0) {
			$this->loadModel('Reportnumber');
			$this->Reportnumber->recursive = 0;

			$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID)));
		} else {
			$reportnumber = array();
		}
		$this->set('reportnumber', $reportnumber);

		$dropdown = $this->Dependency->Dropdown->find('first', array(
			'fields' => array(
				'Dropdown.id',
				'Dropdown.model',
				'Dropdown.field',
				'Dropdown.'.$this->Lang->Discription()
			),
			'conditions' => array(
				'Dropdown.id'=>$dropdownID
			)
		));

		$model = strtolower($dropdown['Dropdown']['model']);
		if(preg_match('/^report([a-z0-9]+)(generally|specific|evaluation)$/', $model, $_model)) {
			$model = $_model[1];
		}

		$arrayData = $this->Xml->DatafromXml($model,'file',ucfirst($model));
		$this->set('settings', $arrayData['settings']);

		$_dropdownXml = $arrayData['settings']->{$dropdown['Dropdown']['model']}->{$dropdown['Dropdown']['field']};

		$fields = array();

		if(isset($_dropdownXml->dependencies->child)) {
			$x = 1;
			foreach($_dropdownXml->dependencies->child as $child) {
				$child = $arrayData['settings']->{$dropdown['Dropdown']['model']}->$child;
				$fields[trim($child->key)] = trim($child->discription->{$this->Lang->Discription()});

				if($x == $linkinOkay){
					$thisfield = trim($child->key);
					$thisfieldDiscription = trim($child->discription->{$this->Lang->Discription()});
				}

			$x++;
			}
		}

		$conditions = array(
			'Dependency.dropdowns_value_id' => $dropdownvalueID,
			'Dependency.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
			'Dependency.field' => $thisfield,
			'Dropdown.id' => $dropdownID
		);

		$this->paginate = array_merge($this->paginate, array('conditions'=>$conditions,'order' => array('Dependency.field')));

		$thisDropdownsValue = $this->Dependency->DropdownsValue->find('first',array('conditions' => array('DropdownsValue.id' => $this->request->linkinID)));

		$this->set('thisDropdownsValue', $thisDropdownsValue);
		$this->set('thisfieldDiscription', $thisfieldDiscription);
		$this->set('fields', $fields);
		$this->set('dropdown', $dropdown);
		$this->set('dependencies', $this->paginate('Dependency'));

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller'=>$this->request->controller, 'action' => 'index', 'terms' => $this->request->params['pass']);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller'=>$this->request->controller, 'action' => 'add', 'terms' => $this->request->params['pass']);
		$this->set('SettingsArray', $SettingsArray);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->request->projectvars['VarsArray'][4];
		$evalutionID = $this->request->projectvars['VarsArray'][5];
		$dropdownID = $this->request->projectvars['VarsArray'][6];
		$count = $this->request->projectvars['VarsArray'][7];
		$dropdownvalueID = $this->request->projectvars['VarsArray'][8];
		$linkinOkay = $this->request->projectvars['VarsArray'][9];

		if(!$this->Dependency->Dropdown->exists($dropdownID)) {
			$this->render('closemodal');
			return;
		}

		if ($this->request->is('post') && isset($this->request->data['Dependency'])) {
			$this->request->data['Dependency'] = array_merge(array(
				'dropdown_id'=>$dropdownID,
				'testingcomp_id'=>$this->Session->read('Auth.User.testingcomp_id')
			), $this->request->data['Dependency']);
			$this->request->data['Dependency']['dropdowns_value_id'] = $dropdownvalueID;

			$this->Dependency->create();
			if ($this->Dependency->save($this->request->data)) {
				$this->set('message', __('The dependency has been saved'));
				$this->set('afterEdit', $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'overview'), $this->request->params['pass'])), 50));
				$this->render('redirect');
				return;
			} else {
				$this->Session->setFlash(__('The dependency could not be saved. Please, try again.'));
			}
		}

		$this->Dependency->Dropdown->recursive = -1;
		$this->set('dropdown', $dropdown = $this->Dependency->Dropdown->find('first', array('fields'=>array('Dropdown.'.$this->Lang->Discription(), 'Dropdown.model', 'Dropdown.field'), 'conditions'=>array('Dropdown.id'=>$dropdownID))));

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = 0;
		$this->set('reportnumber', $reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID))));

		$this->Dependency->Dropdown->recursive = -1;
		$this->set('dropdown', $dropdown = $this->Dependency->Dropdown->find('first', array('conditions'=>array('Dropdown.id'=>$dropdownID))));

		$dropdownsValueList = array();
		$dropdownsValue = $this->Dependency->DropdownsValue->find('first', array('fields'=>array('DropdownsValue.model', 'DropdownsValue.field'), 'conditions'=>array('DropdownsValue.id'=>$dropdownvalueID)));

		if(count($dropdownsValue) > 0){
			$dropdownsValueList = $this->Dependency->DropdownsValue->find('list', array('fields'=>array('DropdownsValue.id', 'DropdownsValue.discription'), 'conditions'=>array('DropdownsValue.model'=>$dropdownsValue['DropdownsValue']['model'], 'DropdownsValue.field'=>$dropdownsValue['DropdownsValue']['field'])));
			if(count($dropdownsValueList) == 0) $dropdownsValueList = array();
		}

		$this->set('dropdowns_value_id', $dropdownsValueList);

		$model = strtolower($dropdown['Dropdown']['model']);
		if(preg_match('/^report([a-z0-9]+)(generally|specific|evaluation)$/', $model, $_model)) {
			$model = $_model[1];
		}

		$arrayData = $this->Xml->DatafromXml($model,'file',ucfirst($model));
		$this->set('settings', $arrayData['settings']);

		$_dropdownXml = $arrayData['settings']->{$dropdown['Dropdown']['model']}->{$dropdown['Dropdown']['field']};
		$fields = array();
		if(isset($_dropdownXml->dependencies->child)) {
			$x = 1;
			foreach($_dropdownXml->dependencies->child as $child) {
				$child = $arrayData['settings']->{$dropdown['Dropdown']['model']}->$child;
				if($linkinOkay == $x){
					$fields[trim($child->key)] = trim($child->discription->{$this->Lang->Discription()});
					break;
				}
				$x++;
			}
		}

		$thisDropdownsValue = $this->Dependency->DropdownsValue->find('first',array('conditions' => array('DropdownsValue.id' => $dropdownvalueID)));
		$this->set('thisDropdownsValue', $thisDropdownsValue);

		$this->set('field', $fields);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller'=>$this->request->controller, 'action' => 'overview', 'terms' => $this->request->params['pass']);
		$this->set('SettingsArray', $SettingsArray);
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->request->projectvars['VarsArray'][4];
		$evalutionID = $this->request->projectvars['VarsArray'][5];
		$dropdownID = $this->request->projectvars['VarsArray'][6];
		$count = $this->request->projectvars['VarsArray'][7];
		$dropdownvalueID = $this->request->projectvars['VarsArray'][8];
		$linkinOkay = $this->request->projectvars['VarsArray'][9];
		$dependencyID = $this->request->projectvars['VarsArray'][10];

		$this->layout = 'modal';
		$this->Dependency->id = $this->Sicherheit->Numeric($dependencyID);

		$url = Router::url(array_merge(array('action'=>'overview'), $this->request->projectvars['VarsArray']));

		if (!$this->Dependency->exists($this->Dependency->id)) {
			$this->set('afterEdit', $this->Navigation->afterEditModal($url, 50));
			return;
		}

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = 0;
		$this->set('reportnumber', $reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID))));

		$dropdown = $this->Dependency->DropdownsValue->Dropdown->find('first', array('conditions'=>array('Dropdown.id'=>$dropdownID)));

		$model = strtolower($dropdown['Dropdown']['model']);
		if(preg_match('/^report([a-z0-9]+)(generally|specific|evaluation)$/', $model, $_model)) {
			$model = $_model[1];
		}

		$arrayData = $this->Xml->DatafromXml($model,'file',ucfirst($model));
		$this->set('settings', $arrayData['settings']);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller'=>$this->request->controller, 'action' => 'overview', 'terms' => array_slice($this->request->params['pass'], 0, count($this->request->params['pass'])-1));
		$this->set('SettingsArray', $SettingsArray);

		$thisDropdownsValue = $this->Dependency->DropdownsValue->find('first',array('conditions' => array('DropdownsValue.id' => $dropdownvalueID)));
		$this->set('thisDropdownsValue', $thisDropdownsValue);

		if (isset($this->request->data['Dependency']) && ($this->request->is('post') || $this->request->is('put'))) {
			if ($this->Dependency->save($this->request->data)) {
				$this->set('message', __('The dependency has been saved'));
				$this->set('afterEdit', $this->Navigation->afterEditModal($url, 50));
				return;
			} else {
				$this->Session->setFlash(__('The dependency could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Dependency.' . $this->Dependency->primaryKey => $this->Dependency->id));
			$this->Dependency->recursive = 1;
			$this->request->data = $this->Dependency->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete() {
		$this->layout = 'modal';
		$this->Dependency->id = $this->request->projectvars['VarsArray'][10];

		if ($this->Dependency->exists($this->Dependency->id)) {
			if ($this->Dependency->delete($this->Dependency->id)) {
				$this->set('message', __('Dependency deleted'));
			} else {
				$this->set('message',__('Dependency was not deleted'));
			}
		}

		$VarsArray = $this->request->projectvars['VarsArray'];
		$VarsArray[14] = 0;
		$url = Router::url(array_merge(array('action'=>'index'), $VarsArray));
		$this->set('afterEdit', $this->Navigation->afterEditModal($url, 50));

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller'=>$this->request->controller, 'action' => 'index', 'terms' => array_slice($this->request->params['pass'], 0, count($this->request->params['pass'])-1));
		$this->set('SettingsArray', $SettingsArray);
	}

	public function get() {

		App::uses('Sanitize', 'Utility');
//		$this->autoRender = false;

		$this->layout = 'json';

	 	// Die IDs des Projektes und des Auftrages werden getestet
	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$reportID = $this->request->projectvars['VarsArray'][3];
	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];
		$dropdownID = intval($this->request->projectvars['VarsArray'][6]);
		$dropdownValue = intval($this->request->projectvars['VarsArray'][10]);

		if($dropdownValue == 0 && isset($this->request->data['val']) && $this->request->data['val'] > 0){
			$dropdownValue = $this->request->data['val'];
		}

		if(!isset($this->request->data['type'])){

			$fields = array();

			$this->set('fields', json_encode($fields));

			return;
		}

		switch($this->request->data['type']){
			case('Report'):
				$this->loadModel('Reportnumber');
				$this->Autorisierung->IsThisMyReport($reportnumberID);
				$this->Reportnumber->recursive = -1;
				$Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumberID)));
				switch($this->request->data['field']){

					case('select'):

					$fields = $this->Depentency->GetDependenciesForSelect($Reportnumber);
					$fields = $this->Depentency->SortDependenciesForSelect($fields);

					if($fields === false){
						$this->set('fields', json_encode(array()));
						return;
					}

					$fields = array_merge($fields,$Reportnumber);

					break;
					case('multiple'):

					$fields = $this->Depentency->GetDependenciesForMultiSelect($Reportnumber);
					$fields = $this->Depentency->SortDependenciesForSelect($fields);
					$fields = array_merge($fields,$Reportnumber);

					if($fields === false){
						$this->set('fields', json_encode(array()));
						return;
					}

					break;
				}
			break;
		}

		if(!isset($fields)) $fields = array();

		$this->set('fields', json_encode($fields));

	}
}
