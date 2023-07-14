<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
/**
 * Testingcomps Controller
 *
 * @property Testingcomp $Testingcomp
 */
class TestingcompsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Paginator', 'SelectValue', 'Data', 'Xml');
	public $helpers = array('Lang','Navigation','JqueryScripte');
	public $layout = 'modal';

	function beforeFilter() {
		App::import('Vendor', 'Authorize');
		$this->loadModel('User');
		$this->Autorisierung->Protect();
		$this->Navigation->ajaxURL();
		$this->Lang->Choice();
		$this->Lang->Change();
		$this->Navigation->ReportVars();
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
		$this->include_index();
	}

	protected function include_index() {

		$this->layout = 'modal';

		$this->Testingcomp->recursive = 0;

		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingcomps','action' => 'add', 'terms' => null);

		$this->set('SettingsArray', $SettingsArray);
		$this->set('testingcomps', $this->paginate(array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));

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

		$this->loadModel('Roll');

		if (!$this->Testingcomp->exists($id)) {
			throw new NotFoundException(__('Invalid testingcomp'));
		}

		$this->Autorisierung->ConditionsTesting($id,'conditionsTestingcomps');

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'testingcomps','action' => 'index', 'terms' => null);

		$options = array('conditions' => array('Testingcomp.' . $this->Testingcomp->primaryKey => $id));
		$testingcomp = $this->Testingcomp->find('first', $options);
//		$optionsUser = array('conditions' => array('User.testingcomp_id' => $id),'order'=>array('name ASC'));
		$optionsUser = array('conditions' => array('User.testingcomp_id' => $id,'User.hidden' => 0,'User.id !=' => 1),'order'=>array('name ASC'));
		$this->Testingcomp->User->recursive = -1;
		$testingcomp['User'] = $this->Testingcomp->User->find('all',$optionsUser);
    	$testingcomp['User'] = $this->Data->GetUserInfos($testingcomp['User']);

		$roll = $this->Auth->user('Roll');

		$rolls = $this->Roll->find('list', array('conditions' => array('id >=' => $roll['id'])));

		// Werte für die Schnellsuche
		$ControllerQuickSearch =
		array(
						'Model' => 'User',
						'Field' => 'name',
						'description' => __('User name', true),
						'minLength' => 2,
						'targetcontroller' => 'users',
						'target_id' => 'id',
						'targetation' => 'edit',
						'Quicksearch' => 'QuickUsersearch',
						'QuicksearchForm' => 'QuickUsersearchForm',
						'QuicksearchSearchingAutocomplet' => 'QuickUsersearchSearchingAutocomplet',
		);

		$this->set('ControllerQuickSearch', $ControllerQuickSearch);

		$this->set('Rolls', $rolls);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('testingcomp', $testingcomp);
	}


/**
 * add method
 *
 * @return void
 */
	public function add() {

		$this->layout = 'modal';
		$id = null;
		$afterEdit = null;

		if (isset($this->request->data['Testingcomp'])) {
			$this->Testingcomp->create();
			if ($this->Testingcomp->save($this->request->data)) {
				$this->Flash->success(__('Testingcomp was saved'), array('key' => 'success'));
				$this->Autorisierung->ConditionsStart();
				$id = $this->Testingcomp->getInsertID();
				$this->Autorisierung->Logger($id,$this->request->data);

				$FormName = array();
				$FormName['controller'] = 'testingcomps';
				$FormName['action'] = 'view';
				$FormName['terms'] = implode('/',array($id));

				$this->set('FormName', $FormName);

			} else {
				$this->Flash->error(__('Testingcomp could not be saved. Please, try again.'), array('key' => 'error'));

			}
		}

		$testingcompcats = $this->Testingcomp->Testingcompcat->find('list',array('fields' => array('id','name'),'order' =>'name ASC'));
		$developments = $this->Testingcomp->Development->find('list',array('fields' => array('id','name'),'order' =>'name ASC'));
		$rolls = $this->Testingcomp->Roll->find('list');
		$contitions = array('conditions' => array('id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','topproject_id')));
		$contitions = array('fields' => array('id','projektname'));
		$topprojects = $this->Testingcomp->Topproject->find('list', $contitions);


		// Das Array für das Settingsmenü
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'testingcomps','action' => 'index', 'terms' => array());

		$this->set('SettingsArray', $SettingsArray);
		$this->set('id', $id);
		$this->set('afterEdit', $afterEdit);
		$this->set('topprojects_select', $this->SelectValue->SelectData('Topprojects','projektname',$contitions));
		$this->set(compact('rolls','topprojects','testingcompcats','developments'));
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
		if (!$this->Testingcomp->exists($id)) {
			throw new NotFoundException(__('Invalid testingcomp'));
		}
		$this->Autorisierung->ConditionsTesting($id,'conditionsTestingcomps');

		if (isset($this->request->data['Testingcomp']) || $this->request->is('put')) {
			$this->Autorisierung->ConditionsTesting($this->request->data['Testingcomp']['id'],'conditionsTestingcomps');

			if ($this->Testingcomp->save($this->request->data)) {

				$this->Autorisierung->Logger($id,$this->request->data);
				$this->Flash->success(__('Testingcomp was saved'), array('key' => 'success'));

				$FormName = array();
				$FormName['controller'] = 'testingcomps';
				$FormName['action'] = 'view';
				$FormName['terms'] = implode('/',array($id));

				$this->set('FormName', $FormName);

			} else {
				$this->Flash->error(__('Testingcomp could not be saved. Please, try again.'), array('key' => 'error'));
			}
		} else {
		}

		$options = array('conditions' => array('Testingcomp.' . $this->Testingcomp->primaryKey => $id));
		$Testingcomp = $this->Testingcomp->find('first', $options);
		$Testingcomp = $this->Data->BelongsToManySelected($Testingcomp,'Testingcomp','Topproject',array('TestingcompsTopprojects','topproject_id','testingcomp_id'));
		$Testingcomp = $this->Data->BelongsToManySelected($Testingcomp,'Testingcomp','Testingcompcat',array('TestingcompcategoriesTestingcomps','testingcomp_category_id','testingcomp_id'));
		$Testingcomp = $this->Data->BelongsToManySelected($Testingcomp,'Testingcomp','Development',array('TestingcompsDevelopments','development_id','testingcomp_id'));

		$this->request->data = $Testingcomp;

		$folder = new Folder(Configure::read('company_logo_folder').$id);
		$logos = $folder->find('logo.*');
		if(count($logos))
		{
			$logos = array_map(
				function($elem, $prefix) { return $prefix.DS.$elem; },
				$logos,
				array_fill(0, count($logos), Configure::read('company_logo_folder').$id)
			);
		}
              //  $this->Testingcomp->TestingcompCategory->recursive = -1;
		$testingcompcats = $this->Testingcomp->Testingcompcat->find('list',array('fields' => array('id','name'),'order' =>'name ASC'));
		$developments = $this->Testingcomp->Development->find('list',array('fields' => array('id','name'),'order' =>'name ASC'));
		$rolls = $this->Testingcomp->Roll->find('list');
		$contitions = array('conditions' => array('id' => $this->Autorisierung->Conditions('TestingcompsTopprojects','topproject_id')));
		$contitions = array('fields' => array('id','projektname'));
		$topprojects = $this->Testingcomp->Topproject->find('list', $contitions);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'testingcomps','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'testingcomps','action' => 'add', 'terms' => null);

		$this->set('SettingsArray', $SettingsArray);
		$this->set('logos', $logos);
		$this->set(compact('rolls', 'topprojects', 'testingcompcats','developments'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Testingcomp->id = $id;
		$afterEdit = null;
		if (!$this->Testingcomp->exists()) {
			throw new NotFoundException(__('Invalid testingcomp'));
		}

		$this->Autorisierung->ConditionsTesting($id,'conditionsTestingcomps');

		$this->Testingcomp->recursive = -1;
		$testingcomps = $this->Testingcomp->find('first', array(
					'conditions' => array(
						'Testingcomp.'.$this->Testingcomp->primaryKey => $id
					)));

		if ($this->Testingcomp->delete()) {
			$this->Autorisierung->ConditionsStart();
			$this->Autorisierung->Logger($id,$testingcomps);
			$logoDir = new Folder(Configure::read('company_logo_folder').$id);
			$ok = true;
			foreach($logoDir->findRecursive() as $file) {
				$k = unlink(Configure::read('company_logo_folder').$id.DS.$file);
				if(!$k) $this->log(__('Deletetion of %s failed', Configure::read('company_logo_folder').$id.DS.$file));
				$ok = $ok && $k;
			}
			$logoDir->delete();
			$this->Session->setFlash(__('Testingcomp was deleted'));
			$this->include_index();
		}
		else {
			$this->Session->setFlash(__('Testingcomp was not deleted'));
		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'testingcomps','action' => 'index', 'terms' => null);
//		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'users','action' => 'add', 'terms' => null);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);
		$this->set('afterEdit', $afterEdit);
		$this->set('SettingsArray', $SettingsArray);

	}

	public function setLogo($id = null) {
		if (!$this->Testingcomp->exists($id)) {
			throw new NotFoundException(__('Invalid testingcomp'));
		}
		$this->Autorisierung->ConditionsTesting($id,'conditionsTestingcomps');

       	$this->Testingcomp->recursive = 0;
		$testingcomp = $this->Testingcomp->find('first', array('conditions'=>array('Testingcomp.'.$this->Testingcomp->primaryKey=>$id)));
		$this->loadModel('Logo');
		$this->Logo->testingcomp = array($id);

		$SettingsArray = array();
		$SettingsArray['backlink'] = array_merge(
			array('discription' => __('Back',true), 'terms' => null),
			array('controller'=>'testingcomps', 'action'=>'edit', $id)
		);

        if (isset($this->request->data['Order']['file']) && $this->request->is('post')) {

            // Validate and move the file
            if($this->Logo->upload(array('file'=>$this->request->data['Order']['file']))) {
 				$this->Testingcomp->read(null, $id);
				$this->Testingcomp->set('logopfad', join('|', $this->Logo->last));
				$this->Testingcomp->save();
 				//$testingcomp->set('logoppfad', join('|', $this->Logo->last));
				//$testingcomp->save();
                $this->Session->setFlash('The image was successfully uploaded.');
            } else {
                $this->Session->setFlash('There was an error with the uploaded file');
            }
        }

		$this->set('SettingsArray', $SettingsArray);
        $this->set('images',$this->Logo->get());
		$this->set('logopath', $testingcomp['Testingcomp']['logopfad']);
	}

	public function search() {
		$this->layout = 'modal';

		if(isset($this->request->data['type'])) {
			$method = 'search_'.$this->request->data['type'];
			if(method_exists($this, $method)) {
				if($this->$method() !== false)
					$this->render($method);
				return;
			}
		}

		$projectID = $this->request->projectID;
		$orderKat = $this->request->orderKat;
		$orderID = $this->request->orderID;
		$reportID = $this->request->reportID;
		$reportnumberID = $this->request->reportnumberID;
		$evalId = $this->request->evalId;
		$weldedit = $this->request->weldedit;

		$testingreportsCount = 0;

		$fields = $this->Data->SearchUniversal('searchcompany');

		if(isset($this->request->data['type']) && $this->request->data['type'] == 'geturl') {
			foreach($this->request->data as $model=>$val) {
				if(!is_array($val)) continue;
				foreach($fields['autocompletes'] as $_autocomplete) {
					if($_autocomplete['Model'] == $model) {
						if(isset($_autocomplete['urlController'])) $controller =  $_autocomplete['urlController']; else $controller = strtolower(Inflector::pluralize(Inflector::underscore($_autocomplete['Model'])));
						if(isset($_autocomplete['urlAction'])) $action =  $_autocomplete['urlAction']; else $action = 'view';

						$data = array();
						switch($model) {
							case 'Device':
								$data = $this->request->projectvars['VarsArray'];
								$data[1] = $this->request->data[$_autocomplete['Model']][$_autocomplete['Key']];
								break;

							case 'Examiner':
								$data = $this->request->projectvars['VarsArray'];
								$data[15] = $this->request->data[$_autocomplete['Model']][$_autocomplete['Key']];
								break;

							default:
								$data = array($this->request->data[$_autocomplete['Model']][$_autocomplete['Key']]);
						}
						echo json_encode(array('url'=>(Router::url(array_merge(array('controller'=>$controller, 'action'=>$action), $data))), 'modal'=>intval($_autocomplete['Dialog'])));
					}
				}

			}
			exit();
		}

		$json = array();
		foreach($fields['autocompletes'] as $key=>$value) {
			if($this->loadModel($value['Model'])) {
				$autocomplete = $this->$value['Model']->findByIndex($value['Key']);

				// nach Testingcomp aussortieren
				$ids = array();
				$testingcomp_ids = $this->Autorisierung->ConditionsTestinccomps();
				if(isset($this->request->data['testingcomp_id']) && $this->request->data['testingcomp_id'] > 0) {
					$testingcomp_ids = $this->request->data['testingcomp_id'];
				}

				$ids = Hash::extract($autocomplete, '{n}.'.($this->$value['Model']->table).'.'.($this->$value['Model']->primaryKey));

				$ids = array_values($this->$value['Model']->find('list', array(
					'fields'=>array($this->$value['Model']->primaryKey),
					'conditions'=>array(
						$this->$value['Model']->primaryKey=>$ids,
						'testingcomp_id' => $testingcomp_ids
					)
				)));


				foreach($autocomplete as $_autocomplete) {
					$_autocomplete = reset($_autocomplete);
					if(array_search($_autocomplete['id'], $ids)===false) continue;

					$val = trim(preg_replace('/[\s]+/', ' ', join(' ', array_diff_key($_autocomplete, array('id'=>0)))));
					if(!empty($val)) $json[$value['Model'].ucfirst(Inflector::camelize($value['Key']))][$_autocomplete[$this->$value['Model']->primaryKey]] = $val;
				}
			}
		}

		$this->set('autocomplete', $json);
		$this->set('SettingsArray', null);
		$this->set('reportName', @$SearchFields['report_id']['Result'][$reportID]);
		$this->set('reportID', $reportID);
		$this->set('projectID', $projectID);
		$this->set('orderID', $orderID);
		$this->set('orderKat', $this->Session->read('orderKat'));
	}

	protected function search_examiners() {
		$this->loadModel('Examiner');
		$this->Examiner->recursive = 0;

		$fields = $this->Data->SearchUniversal('examiners');
		$joins = array(
			'Certificate' => array(
					'table'=>'certificates',
					'alias'=>'Certificate',
					'conditions'=>array('Certificate.examiner_id = Examiner.id', 'Certificate.active=1', 'Certificate.deleted=0')
			),
			'CertificateData' => array(
					'table'=>'certificate_datas',
					'alias'=>'CertificateData',
					'conditions'=>array('CertificateData.examiner_id = Examiner.id', 'CertificateData.active=1', 'CertificateData.deleted=0')
			),
		);

		if(isset($this->request->data['Examiner'])) {
			$this->request->data = array_intersect_key($this->request->data, array_flip(array('Examiner', 'Certificate', 'CertificateData', 'type', 'testingcomp_id', 'showresult')));
			$data = array();
			foreach($this->request->data as $key => $val) {
				if(!is_array($val)) continue;
				//$data[$key] = array_filter($val);
				foreach(array_filter($val) as $_key=>$val) {
					$area = false;
					foreach($fields['autocompletes'] as $_field) {
						if($_field['Model'] == $key && $_field['Key'] == preg_replace('#(_from|_to)$#', '', $_key)) {
							$area = $_field['Area'] != 0;
							break;
						}
					}

					if(!$area) $data[$key][$_key.' LIKE'] = '%'.$val.'%';
					else {
						$_key = preg_replace('#_from$#', ' >=', $_key);
						$_key = preg_replace('#_to$#', ' <=', $_key);

						$data[$key][$_key] = $val;
					}
				}
			}
			$data['Examiner']['testingcomp_id'] = $this->request->data['testingcomp_id'];

			$conditions = array('joins'=>array_values(array_intersect_key($joins, array_filter($data))), 'conditions'=>Hash::flatten(array_filter($data)));
			if(isset($this->request->data['showresult'])) {
				$conditions['limit'] = 25;
				$this->paginate = $conditions;

				$this->Examiner->recursive = 1;
				$results = $this->Paginator->paginate('Examiner');

				foreach($results as $key=>$val) {
					foreach($this->Examiner->Certificate->find('all', array('conditions'=>array('Certificate.examiner_id'=>$val['Examiner']['id']))) as $cert)
						$results[$key]['Certificate'][] = $cert['Certificate'];
				}

				$this->set('results', $results);
				$this->render('results_examiners');
				return false;
			} else {
				$resultsCount = $this->Examiner->find('count', $conditions);
			}
		} else {
			$conditions = array('Examiner.testingcomp_id' => array_keys($fields['dropdowns']['testingcomp_id']['Result']));
			$resultsCount = $this->Examiner->find('count', array('conditions'=>$conditions));
		}

		$this->set('resultsCount', $resultsCount);
	}

	protected function search_devices() {
		$this->loadModel('Examiner');
		$this->Examiner->recursive = 0;

		$fields = $this->Data->SearchUniversal('devices');
		$joins = array(
				'DeviceCertificate' => array(
						'table'=>'device_certificates',
						'alias'=>'DeviceCertificate',
						'conditions'=>array('DeviceCertificate.device_id = Device.id', 'DeviceCertificate.active=1', 'DeviceCertificate.deleted=0')
				),
				'DeviceCertificateData' => array(
						'table'=>'device_certificate_datas',
						'alias'=>'DeviceCertificateData',
						'conditions'=>array('DeviceCertificateData.device_id = Device.id', 'DeviceCertificateData.active=1', 'DeviceCertificateData.deleted=0')
				),
				'DeviceTestingmethodJoin' => array(
						'table'=>'devices_testingmethods_devices',
						'alias'=>'DeviceTestingmethodJoin',
						'conditions'=>array('DeviceTestingmethodJoin.device_id = Device.id')
				),
				'DeviceTestingmethod' => array(
						'table'=>'device_testingmethods',
						'alias'=>'DeviceTestingmethod',
						'conditions'=>array('DeviceTestingmethodJoin.testingmethod_id = DeviceTestingmethod.id')
				),
		);

		if(isset($this->request->data['Device'])) {
			$this->request->data = array_intersect_key($this->request->data, array_flip(array('Device', 'DeviceCertificate', 'DeviceCertificateData', 'DeviceTestingmethod', 'type', 'testingcomp_id', 'showresult')));
			$data = array();
			foreach($this->request->data as $key => $val) {
				if(!is_array($val)) continue;
				//$data[$key] = array_filter($val);
				foreach(array_filter($val) as $_key=>$val) {
					$area = false;
					foreach($fields['autocompletes'] as $_field) {
						if($_field['Model'] == $key && $_field['Key'] == preg_replace('#(_from|_to)$#', '', $_key)) {
							$area = $_field['Area'] != 0;
							break;
						}
					}

					// TODO: Dropdowns noch ohne % % schreiben (Prüfung auf Eintrag in $fields['dropdowns']
					if(!$area) $data[$key][$_key.' LIKE'] = '%'.$val.'%';
					else {
						$_key = preg_replace('#_from$#', ' >=', $_key);
						$_key = preg_replace('#_to$#', ' <=', $_key);

						$data[$key][$_key] = $val;
					}
				}
			}
			if($this->request->data['testingcomp_id'] != '') $data['Device']['testingcomp_id'] = $this->request->data['testingcomp_id'];

			$conditions = array('joins'=>array_values(array_intersect_key($joins, array_merge(array_filter($data), array('DeviceTestingmethodJoin'=>0)))), 'conditions'=>Hash::flatten(array_filter($data)));

			if(isset($this->request->data['showresult'])) {
				$conditions['limit'] = 25;
				$this->paginate = $conditions;
				$this->Device->recursive = 1;
				$results = $this->Paginator->paginate('Device');

				$this->set('results', $results);
				$this->render('results_devices');
				return false;
			} else {
				$resultsCount = $this->Device->find('count', $conditions);
			}
		} else {
			$conditions = array('Device.testingcomp_id' => array_keys($fields['dropdowns']['testingcomp_id']['Result']));
			$resultsCount = $this->Device->find('count', array('conditions'=>$conditions));
		}

		$this->set('resultsCount', $resultsCount);
	}
}
