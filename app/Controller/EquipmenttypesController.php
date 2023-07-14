<?php
App::uses('AppController', 'Controller');
/**
 * EquipmentTypes Controller
 *
 * @property EquipmentType $EquipmentType
 */
class EquipmentTypesController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'ajax';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		// Es wird das in der Auswahl gewählte Project in einer Session gespeichert
		$this->Session->write('orderURL', null);

//		if($this->request->params['action'] == 'index') {
			$this->Session->write('projectURL', $this->request->params['pass'][0]);
//		}

		$this->Navigation->ReportVars();

		$this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

		$this->loadModel('User');
		$this->loadModel('Topproject');
		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('quicksearch','quicksearchtype');

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

		$this->Session->delete('search.params');
		$this->Session->delete('search.results');

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
 	public function quicksearchtype() {

		App::uses('Sanitize', 'Utility');
 		$this->layout = 'json';
		$breads = $this->Navigation->Breads(null);
		
		$term = Sanitize::stripAll($this->request->data['term']);
		$term = Sanitize::paranoid($term);
		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		
		$options = array(
						'fields' => array('id','discription'),
						'limit' => 5,
						'conditions' => array(
								'EquipmentType.topproject_id' => $projectID,
								'EquipmentType.discription LIKE' => '%'.$term.'%'
							)
						);


		$this->EquipmentType->recursive = -1;
		$equipment = $this->EquipmentType->find('all', $options);

		$response = array();

		foreach($equipment as $_key => $_equipment){
			array_push($response, array('key' => $_equipment['EquipmentType']['id'],'value' => $_equipment['EquipmentType']['discription']));
		}
		
		$this->set('test',json_encode($response));
	}

	public function quicksearch() {

		App::uses('Sanitize', 'Utility');
 		$this->layout = 'json';
		$breads = $this->Navigation->Breads(null);
		
		$term = Sanitize::stripAll($this->request->data['term']);
		$term = Sanitize::paranoid($term);
		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		
		
		$options = array(
						'fields' => array('id','discription'),
						'limit' => 5,
						'conditions' => array(
								'Equipment.topproject_id' => $projectID,
								'Equipment.equipment_type_id' => $equipmentType,
								'Equipment.discription LIKE' => '%'.$term.'%'
							)
						);


		$this->EquipmentType->Equipment->recursive = -1;
		$equipment = $this->EquipmentType->Equipment->find('all', $options);

		$response = array();

		foreach($equipment as $_key => $_equipment){
			array_push($response, array('key' => $_equipment['Equipment']['id'],'value' => $_equipment['Equipment']['discription']));
		}
		
		$this->set('test',json_encode($response));
	}
 
	public function index() {
		$this->include_index();
	}

	protected function include_index() {
		$this->Data->EquipmentTypesIndex();
	}

	public function overview() {

		$this->EquipmentType->recursive = 0;
		$breads = $this->Navigation->Breads(null);

		$options['EquipmentType.topproject_id'] = $this->request->projectvars['VarsArray'][0];
		$options['EquipmentType.status'] = 0;
		
		if(isset($this->request->data['Quicksearch']['this_id']) && $this->request->data['Quicksearch']['this_id'] > 0){

			$this_id = $this->Sicherheit->Numeric($this->request->data['Quicksearch']['this_id']);
			
			if (!$this->EquipmentType->exists($this_id)) {
				throw new NotFoundException(__('Invalid equipment type'));
			}
			else {
				$options['EquipmentType.id'] = $this_id;
			}
		}

		$this->paginate = array(
    		'conditions' => array($options),
			'order' => array('discription' => 'asc'),
			'limit' => 25
		);

		$equipmentTypes = $this->paginate('EquipmentType');

		$SettingsArray = array();
//		$SettingsArray['statistik'] = array('discription' => __('Statistics',true), 'controller' => 'statistics','action' => 'index', 'terms' => $this->request->projectvars['VarsArray'],);
		$SettingsArray['settingslink'] = array('discription' => __('Equipment Type Settings',true), 'controller' => 'equipmenttypes','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$breads[1]['controller'] = 'equipmenttypes';
		$breads[1]['action'] = 'overview';
		$breads[1]['pass'] = $this->request->projectvars['projectID'];

		$this->Navigation->SubdivisionCorrection('equipmenttypes','overview');

		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('equipmentTypes', $equipmentTypes);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {

		$id = $this->request->projectvars['VarsArray'][1];
		$breads = $this->Navigation->Breads(null);

		if (!$this->EquipmentType->exists($id)) {
			throw new NotFoundException(__('Invalid equipment type'));
		}

		$this->EquipmentType->recursive = 1;
		$options = array('conditions' => array(
											'EquipmentType.' . $this->EquipmentType->primaryKey => $id,
											'EquipmentType.status' => 0,
											)
										);
										
		$equipmentTypes = $this->EquipmentType->find('first', $options);

		// leider holt er die Equipments nicht mit, muss noch gefixt werden
		$this->loadModel('Equipment');
		$this->Equipment->recursive = -1;

		$optionsEquipment['Equipment.topproject_id'] = $this->request->projectvars['VarsArray'][0];
		$optionsEquipment['Equipment.equipment_type_id'] = $this->request->projectvars['VarsArray'][1];

		// Je nach dem, in wieviele Untergruppen das Projekt aufgeteilt ist
		// werden die überflüssigen Suchparameter entfernt
		$_x = 1;
		if($this->request->TopprojectSubdivision == 3)$_x_stop = 1;
		if($this->request->TopprojectSubdivision == 4)$_x_stop = 2;
		
		foreach($optionsEquipment as $_key => $_options){
			if($_x > $_x_stop){
				unset($optionsEquipment[$_key]);
			}
			$_x++;
		}

		$optionsEquipment['Equipment.status'] = 0;

		if(isset($this->request->data['Quicksearch']['this_id']) && $this->request->data['Quicksearch']['this_id'] > 0){

			$this_id = $this->Sicherheit->Numeric($this->request->data['Quicksearch']['this_id']);
			
			if (!$this->Equipment->exists($this_id)) {
				throw new NotFoundException(__('Invalid equipment'));
			}
			else {
				$optionsEquipment['Equipment.id'] = $this_id;
			}
		}

		$this->paginate = array(
    		'conditions' => array($optionsEquipment),
			'order' => array('discription' => 'asc'),
			'limit' => 25
		);

		$equipments = $this->paginate('Equipment');

		$SettingsArray = array();
		$SettingsArray['settingslink'] = array('discription' => __('Equipment Settings',true), 'controller' => 'equipments','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);

		if(Configure::read('GlobalReportNumbers') == true){
			$SettingsArray['movelink'] = array('discription' => __('Move this equipment type',true), 'controller' => 'equipmenttypes','action' => 'move', 'terms' => $this->request->projectvars['VarsArray']);
		}
		
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$breads[1]['controller'] = 'equipmenttypes';
		$breads[1]['action'] = 'overview';
		$breads[1]['pass'] = $this->request->projectvars['projectID'];

		$breads[] = array(
				'discription' => $equipmentTypes['EquipmentType']['discription'],
        		'controller' => 'equipmenttypes',
				'action' => 'view',
				'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1]
			);

		$breads = $this->Navigation->SubdivisionBreads($breads);

		$this->Navigation->SubdivisionCorrection('equipmenttypes','view');

		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('equipmentType', $equipmentTypes);
		$this->set('equipments', $equipments);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {

		$this->layout = 'modal';

		$id = $this->request->projectvars['VarsArray'][1];

		if (isset($this->request->data['EquipmentType']) || $this->request->is('put')) {
			$this->EquipmentType->create();

			$this->request->data['EquipmentType']['topproject_id'] = $this->request->projectvars['VarsArray'][0];

			if ($this->EquipmentType->save($this->request->data)) {
				$this->Session->setFlash(__('The equipment type has been saved'));
				$this->include_index();
			} else {
				$this->Session->setFlash(__('The equipment type could not be saved. Please, try again.'));
			}
		}
		else {
			$SettingsArray['addlink'] = array('discription' => __('Add new order',true), 'controller' => 'equipmenttypes','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
			$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'equipmenttypes','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
			$this->set('SettingsArray', $SettingsArray);
		}
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
		$SettingsArray['addlink'] = array('discription' => __('Add new order',true), 'controller' => 'equipmenttypes','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'equipmenttypes','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);

		$id = $this->request->projectvars['VarsArray'][1];

		if (!$this->EquipmentType->exists($id)) {
			throw new NotFoundException(__('Invalid equipment type'));
		}

		if (isset($this->request->data['EquipmentType']) || $this->request->is('put')) {

			$this->request->data['EquipmentType']['topproject_id'] = $this->request->projectvars['VarsArray'][0];

			if ($this->EquipmentType->save($this->request->data)) {
				$this->Session->setFlash(__('The equipment type has been saved'));

				$this->include_index();
			} else {
				$this->Session->setFlash(__('The equipment type could not be saved. Please, try again.'));
			}
		}
		else {

			$options = array('conditions' => array('EquipmentType.' . $this->EquipmentType->primaryKey => $id));
			$this->request->data = $this->EquipmentType->find('first', $options);

			$topprojects = $this->EquipmentType->Topproject->find('list');
			$this->set(compact('topprojects'));

			$this->set('SettingsArray', $SettingsArray);
		}
	}

 	public function move($id = null) {
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$id = $this->request->projectvars['VarsArray'][3];
		
		$equipmenttype = $this->EquipmentType->find('first',array('conditions'=> array(
																		'EquipmentType.id' => $equipmentType,
																	)
																)
															);
		$topprojects = $this->EquipmentType->Topproject->find('list',array(
																	'fields'=> array('id','projektname'),
																	'order'=> array('projektname'),
																	'conditions' => array(
																		'status' => 0
																		)
																	)
																);

		if (!$this->EquipmentType->exists($equipmentType)) {
			throw new NotFoundException(__('Invalid equipment'));
		}

		if(Configure::read('GlobalReportNumbers') == false){
			return;
		}

		if ($this->request->is('post') && isset($this->request->data['Equipment'])) {
			if(isset($this->request->data['Equipment']['topproject_id']) && $this->request->data['Equipment']['topproject_id'] != ''){
				$this->set('equipmenttypes', $this->request->data['Equipment']['topproject_id']);
				$this->set('thisequipment', $this->request->data['Equipment']['topproject_id']);

				if(isset($this->request->data['Equipment']['thisequipment']) && $this->request->data['Equipment']['thisequipment'] != ''){					
						$order_old = array(
										'topproject_id' => 		$equipmenttype['Topproject']['id'],
										);
						$order_new = array(
										'testingcomp_id' => 	$this->Auth->user('testingcomp_id'),
										'topproject_id' => $this->request->data['Equipment']['topproject_id'],
										'equipment_type_id' => $equipmenttype['EquipmentType']['id'],
										);

					$this->loadModel('Order');
					$this->loadModel('Deliverynumber');
					$this->loadModel('DevelopmentData');
					$this->loadModel('Equipment');
					$this->loadModel('Reportnumber');

					$this->Order->updateAll(
						array(
							'Order.topproject_id' => $order_new['topproject_id'],
							'Order.testingcomp_id' => $order_new['testingcomp_id'],
						),
						array('Order.equipment_type_id' => $order_new['equipment_type_id'])
					);	
					$this->Deliverynumber->updateAll(
						array(
							'Deliverynumber.topproject_id' => $order_new['topproject_id'],
							'Deliverynumber.testingcomp_id' => $order_new['testingcomp_id'],
						),
						array('Deliverynumber.equipment_type_id' => $order_new['equipment_type_id'])
					);	
					$this->DevelopmentData->updateAll(
						array(
							'DevelopmentData.topproject_id' => $order_new['topproject_id'],
						),
						array('DevelopmentData.equipment_type_id' => $order_new['equipment_type_id'])
					);
					$this->EquipmentType->updateAll(
						array(
							'EquipmentType.topproject_id' => $order_new['topproject_id'],
						),
						array('EquipmentType.id' => $order_new['equipment_type_id'])
					);
					$this->Equipment->recursive = -1;
					$this->Equipment->updateAll(
						array(
							'Equipment.topproject_id' => $order_new['topproject_id'],
						),
						array('Equipment.equipment_type_id' => $order_new['equipment_type_id'])
					);
					$this->Reportnumber->recursive = -1;
					$this->Reportnumber->updateAll(
						array(
							'Reportnumber.topproject_id' => $order_new['topproject_id'],
							'Reportnumber.testingcomp_id' => $order_new['testingcomp_id'],
						),
						array('Reportnumber.equipment_type_id' => $order_new['equipment_type_id'])
					);

					$FormName = array();
					$FormName['controller'] = 'equipmenttypes';
					$FormName['action'] = 'view';
					$FormName['terms'] = $order_new['topproject_id'].'/'.$order_new['equipment_type_id'];
		
					$this->Session->setFlash(__('Equipment type ' . $equipmenttype['EquipmentType']['discription']) .' ' .__('has been moved to this location.',true));
					$this->set('FormName', $FormName);
					$this->set('saveOK', 1);
				}
			}
		}

		$SettingsArray = array();
		$SettingsArray['editlink'] = array('discription' => __('Edit',true), 'controller' => 'orders','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

//		$this->set('headline', $headline);
		$this->set('SettingsArray', $SettingsArray);
		
		$this->set('equipmenttype', $equipmenttype);
		$this->set('topprojects', $topprojects);
		$this->set('locale', $this->Lang->Discription());

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
		$id = $this->request->projectvars['VarsArray'][1];

		$this->EquipmentType->id = $id;

		if (!$this->EquipmentType->exists()) {
			throw new NotFoundException(__('Invalid equipment type'));
		}
		
		$this->EquipmentType->recursive = -1;
		
		if(isset($this->request->data['EquipmentType']['id'])){
			
			// Test ob Berechtigung für dieses Equipment vorliegt fehlt noch
			$del = 1;

			$this->loadModel('Reportnumber');
			$this->Reportnumber->recursive = -1;
			$this->Reportnumber->Equipment->recursive = -1;
			$this->Reportnumber->Order->recursive = -1;
		
			$this->EquipmentType->updateAll(
				array('EquipmentType.status' => $del),
				array('EquipmentType.id' => $id)
			);

			$this->Reportnumber->updateAll(
				array('Reportnumber.delete' => 1),
				array('Reportnumber.equipment_type_id' => $id)
			);

			$this->Reportnumber->Equipment->updateAll(
				array('Equipment.status' => $del),
				array('Equipment.equipment_type_id' => $id)
			);

			$this->Reportnumber->Order->updateAll(
				array('Order.deleted' => 1),
				array('Order.equipment_type_id' => $id)
			);		

			$logArray = array('delete' => 'Equipment type gelöscht');
			$this->Autorisierung->Logger($id,$logArray);

			$this->Session->setFlash(__('Equipment type deleted'));
			$this->include_index();
		}

		$equipmenttype = $this->EquipmentType->find('first',array('conditions' => array('EquipmentType.id' => $id)));
		$this->request->data = $equipmenttype;
		
		$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'equipmenttypes','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
		$this->set('SettingsArray',$SettingsArray);
	}
}
