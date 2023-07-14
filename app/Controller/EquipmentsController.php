<?php
App::uses('AppController', 'Controller');
/**
 * Equipment Controller
 *
 * @property Equipment $Equipment
 */
class EquipmentsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'ajax';

	function beforeFilter() {

// test

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
		$noAjax = array('quicksearch');

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

		$this->Session->delete('search.params');
		$this->Session->delete('search.results');

		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
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
		$this->Data->EquipmentIndex();
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
 	public function quicksearch() {

		App::uses('Sanitize', 'Utility');
 		$this->layout = 'json';
		$breads = $this->Navigation->Breads(null);

		$term = Sanitize::stripAll($this->request->data['term']);
		$term = Sanitize::paranoid($term);
		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];

		$this->loadModel('Order');

		$options = array(
						'fields' => array('id','auftrags_nr'),
						'limit' => 5,
						'conditions' => array(
								'Order.topproject_id' => $projectID,
								'Order.equipment_type_id' => $equipmentType,
								'Order.equipment_id' => $equipment,
								'Order.auftrags_nr LIKE' => '%'.$term.'%'
							)
						);


		$this->Order->recursive = -1;
		$equipment = $this->Order->find('all', $options);

		$response = array();

		foreach($equipment as $_key => $_equipment){
			array_push($response, array('key' => $_equipment['Order']['id'],'value' => $_equipment['Order']['auftrags_nr']));
		}

		$this->set('test',json_encode($response));
	}

	public function view($id = null) {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$id = $this->request->projectvars['VarsArray'][2];
		$deleted = 0;
//		$status = array('number' => 1, 'value' => __('close', true));
		$status = array('number' => 0, 'value' => __('open', true));
		$OrdersStatus = array('all' => 0,'open' => 0,'closet' => 0);

		$breads = $this->Navigation->Breads(null);

		if (!$this->Equipment->exists($id)) {
			throw new NotFoundException(__('Invalid equipment'));
		}

		if(isset($this->request->data['Equipment']['gender']) && $this->request->data['Equipment']['gender'] < 3){
			if($this->request->data['Equipment']['gender'] == 0){
				$status = array('number' => 0, 'value' => __('open', true));
			}
			if($this->request->data['Equipment']['gender'] == 1){
				$status = array('number' => 1, 'value' => __('close', true));
			}
			if($this->request->data['Equipment']['gender'] == 2){
				$status = array('number' => 2, 'value' => __('all', true));
			}
		}

		$options = array('conditions' => array('Equipment.' . $this->Equipment->primaryKey => $id));
		$equipment = $this->Equipment->find('first', $options);

		$this->loadModel('Deliverynumber');
		$this->Deliverynumber->recursive = 0;

		if($this->Auth->user('roll_id') > 4){
			$reports_testingcomp = array('Deliverynumber.testingcomp_id' => $this->Auth->user('testingcomp_id'));
		}
		else {
			$reports_testingcomp = null;
		}

		if($this->Auth->user('Testingcomp.extern') == 1){
			$reports_testingcomp = null;
		}

		$optionsDeliverynumber = array(
									'Deliverynumber.topproject_id' => $this->request->projectvars['VarsArray'][0],
									'Deliverynumber.equipment_type_id' => $this->request->projectvars['VarsArray'][1],
									'Deliverynumber.equipment_id' => $this->request->projectvars['VarsArray'][2],
//									'Deliverynumber.testingcomp_id' => $this->Autorisierung->ProjectMember($projectID),
								);


		// Je nach dem, in wieviele Untergruppen das Projekt aufgeteilt ist
		// werden die überflüssigen Suchparameter entfernt
		if($this->request->TopprojectSubdivision == 2){
			$optionsDeliverynumber = array(
									'Deliverynumber.topproject_id' => $this->request->projectvars['VarsArray'][0],
								);
		}


		// Wenn die eingeloggte Benutzergruppe kein Prüfunternehmen, sondern ein
		// Auftraggeber oder Überwachungsfirma ist, sollen alle Aufträge, unabhängig wer diese
		// angelegt hat, angezeigt werden, nur bei Prüffirmen wird gefiltert

		if($this->Auth->user('Testingcomp.extern') == 0){
			array_push($optionsDeliverynumber,$this->Autorisierung->ConditionsTestingcompRoll(4,'Deliverynumber'));
		}

		if(isset($this->request->data['Quicksearch']['this_id']) && $this->request->data['Quicksearch']['this_id'] > 0){

			$this_id = $this->Sicherheit->Numeric($this->request->data['Quicksearch']['this_id']);

				$optionsDeliverynumber['Deliverynumber.order_id'] = $this_id;
		}

		$optionsDeliverynumber['Order.deleted'] = $deleted;
		$OrdersStatus['all'] = $this->Deliverynumber->find('count',array('conditions' => $optionsDeliverynumber));

		$optionsDeliverynumber['Order.status'] = 0;
		$OrdersStatus['open'] = $this->Deliverynumber->find('count',array(
															'conditions' => $optionsDeliverynumber
														)
													);
		$optionsDeliverynumber['Order.status'] = 1;
		$OrdersStatus['closet'] = $this->Deliverynumber->find('count',array(
															'conditions' => $optionsDeliverynumber
														)
													);
		unset($optionsDeliverynumber['Order.status']);

		if($status['number'] < 2){
			$optionsDeliverynumber['Order.status'] = $status['number'];
		}

//		pr($this->Autorisierung->ProjectMember($projectID));

		$this->paginate = array(
    		'conditions' => array($optionsDeliverynumber),
			'order' => array('id' => 'desc'),
			'limit' => 25
		);

		$deliverynumbers = $this->paginate('Deliverynumber');

		$reportsArrayContainer = $this->Navigation->SubMenue(
											'Report',
											'Topproject',
											__('Show', true),
											'show',
											'reportnumbers',
											$this->request->projectvars['VarsArray']
										);

		if(count($reportsArrayContainer) == 0) {
			$this->Session->setFlash(__('Please select a report typ in the project settings.'));
		}

		$SettingsArray = array();
		$SettingsArray['addlink'] = array('discription' => __('Create new order',true), 'controller' => 'orders','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['upload'] = array('discription' => __('Upload new order',true), 'controller' => 'orders','action' => 'create', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['movelink'] = array('discription' => __('Move this equipment',true), 'controller' => 'equipments','action' => 'move', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method',true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
		$SettingsArray['progresstool'] = array('discription' => __('Show progress',true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);
		if(!Configure::check('search.orders') || Configure::read('search.orders')) {
			$SettingsArray['addsearching'] = array('discription' => __('Searching for orders or reports',true), 'controller' => 'topprojects','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		} else {
			$SettingsArray['addsearching'] = array('discription' => __('Searching for reports',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);
		}

		$SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

		$breads[1]['controller'] = 'equipmenttypes';
		$breads[1]['action'] = 'overview';
		$breads[1]['pass'] = $this->request->projectvars['projectID'];


		$breads[] = array(
				'discription' => $equipment['EquipmentType']['discription'],
        		'controller' => 'equipmenttypes',
				'action' => 'view',
				'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1]
			);

		$breads[] = array(
				'discription' => $equipment['Equipment']['discription'],
        		'controller' => 'equipments',
				'action' => 'view',
				'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2]
			);

		$breads = $this->Navigation->SubdivisionBreads($breads);

		$this->Navigation->SubdivisionCorrection('equipments','view');

		$this->set('OrdersStatus', $OrdersStatus);
		$this->set('status', $status);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('equipment', $equipment);
		$this->set('deliverynumbers', $deliverynumbers);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {

		$this->layout = 'modal';

		$id = $this->request->projectvars['VarsArray'][2];

		if (isset($this->request->data['Equipment']) || $this->request->is('put')) {
			$this->Equipment->create();
			$this->request->data['Equipment']['topproject_id'] = $this->request->projectvars['VarsArray'][0];
			$this->request->data['Equipment']['equipment_type_id'] = $this->request->projectvars['VarsArray'][1];

			if ($this->Equipment->save($this->request->data)) {
				$this->Session->setFlash(__('The equipment has been saved'));
				$this->include_index();
			} else {
				$this->Session->setFlash(__('The equipment could not be saved. Please, try again.'));
			}
		}
		else {

			$this->loadModel('EquipmentType');
			$options = array('conditions' => array('EquipmentType.' . $this->EquipmentType->primaryKey => $this->request->projectvars['VarsArray'][1]));
			$equipmentType = $this->EquipmentType->find('first', $options);

			$this->set('Headline', $equipmentType['Topproject']['projektname'] . ' / ' . $equipmentType['EquipmentType']['discription']);

			$SettingsArray['addlink'] = array('discription' => __('Add new order',true), 'controller' => 'equipmenttypes','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
			$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'equipments','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
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
		$SettingsArray['addlink'] = array('discription' => __('Add new order',true), 'controller' => 'equipments','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'equipments','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);

		$id = $this->request->projectvars['VarsArray'][2];

		if (!$this->Equipment->exists($id)) {
			throw new NotFoundException(__('Invalid equipment'));
		}
		if (isset($this->request->data['Equipment']) || $this->request->is('put')) {
			if ($this->Equipment->save($this->request->data)) {
				$this->Session->setFlash(__('The equipment has been saved'));
				$this->include_index();
			} else {
				$this->Session->setFlash(__('The equipment could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Equipment.' . $this->Equipment->primaryKey => $id));
			$this->request->data = $this->Equipment->find('first', $options);
			$this->set('SettingsArray', $SettingsArray);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
 	public function move($id = null) {
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$id = $this->request->projectvars['VarsArray'][3];

		$this->loadModel('Reportnumber');
		$this->Equipment->recursive = 2;
		$optionsEquipment = array('conditions' => array('Equipment.id' => $equipment));
		$Equipment = $this->Equipment->find('first',$optionsEquipment);

		if (!$this->Equipment->exists($equipment)) {
			throw new NotFoundException(__('Invalid equipment'));
		}

		$GlobalReportNumbers = array('Topproject.id' => $this->Autorisierung->ConditionsTopprojects());
		if(Configure::read('GlobalReportNumbers') == true){
			$GlobalReportNumbers = array('Topproject.id' => $this->Autorisierung->ConditionsTopprojects());
		}
		if(Configure::read('GlobalReportNumbers') == false){
			$GlobalReportNumbers = array('Topproject.id' => $projectID);
		}

		$topproject = $this->Equipment->EquipmentType->Topproject->find(
												'list',array(
												'fields'=> array('id','projektname'),
												'order'=> array('projektname'),
												'conditions' => array(
													$GlobalReportNumbers,
													'Topproject.status' => 0
												)
											)
										);

		$this->set('topproject',$topproject);

		if ($this->request->is('post') && isset($this->request->data['Equipment'])) {
			if(isset($this->request->data['Equipment']['topproject_id']) && $this->request->data['Equipment']['topproject_id'] != ''){

				$GlobalReportNumbersAll = array('topproject_id' => $this->Autorisierung->ConditionsTopprojects());
				$GlobalReportNumbersOne = array('topproject_id' => $this->request->data['Equipment']['topproject_id']);
				$GlobalReportNumbersOneVar = $this->request->data['Equipment']['topproject_id'];

				if(Configure::read('GlobalReportNumbers') == true){
					$GlobalReportNumbersAll = array('topproject_id' => $this->Autorisierung->ConditionsTopprojects());
					$GlobalReportNumbersOne = array('topproject_id' => $this->request->data['Equipment']['topproject_id']);
					$GlobalReportNumbersOneVar = $this->request->data['Equipment']['topproject_id'];
				}
				if(Configure::read('GlobalReportNumbers') == false){
					$GlobalReportNumbersAll = array('topproject_id' => $projectID);
					$GlobalReportNumbersOne = array('topproject_id' => $projectID);
					$GlobalReportNumbersOneVar = $projectID;
				}

				$equipmenttypes = $this->Equipment->EquipmentType->find(
																'list',array(
																'fields'=> array('id','discription'),
																'conditions' => array(
																	$GlobalReportNumbersAll,
																	$GlobalReportNumbersOne,
																	'status' => 0
																)
															)
														);

				$this->set('equipmenttypes', $equipmenttypes);

				if(isset($this->request->data['Equipment']['equipment_type_id']) && $this->request->data['Equipment']['equipment_type_id'] != ''){
					$this->set('thisequipment', $this->request->data['Equipment']['equipment_type_id']);

					if(isset($this->request->data['Equipment']['thisequipment']) && $this->request->data['Equipment']['thisequipment'] != ''){
						$order_old = array(
										'topproject_id' => 		$Equipment['Equipment']['topproject_id'],
										'equipment_type_id' => 	$Equipment['Equipment']['equipment_type_id'],
										'equipment_id' => 		$Equipment['Equipment']['id']
										);
						$order_new = array(
										'testingcomp_id' => 	$this->Auth->user('testingcomp_id'),
										'topproject_id' => $GlobalReportNumbersOneVar,
										'equipment_type_id' => $this->request->data['Equipment']['equipment_type_id'],
										'equipment_id' => $Equipment['Equipment']['id']
										);

						$this->loadModel('Order');
						$this->loadModel('Deliverynumber');
						$this->loadModel('DevelopmentData');
						$this->loadModel('Reportnumber');

						$this->Order->updateAll(
							array(
								'Order.topproject_id' => $order_new['topproject_id'],
								'Order.testingcomp_id' => $order_new['testingcomp_id'],
								'Order.equipment_type_id' => $order_new['equipment_type_id'],
							),
							array('Order.equipment_id' => $order_new['equipment_id'])
						);
						$this->Deliverynumber->updateAll(
							array(
								'Deliverynumber.topproject_id' => $order_new['topproject_id'],
								'Deliverynumber.testingcomp_id' => $order_new['testingcomp_id'],
								'Deliverynumber.equipment_type_id' => $order_new['equipment_type_id'],
							),
							array('Deliverynumber.equipment_id' => $order_new['equipment_id'])
						);
						$this->DevelopmentData->updateAll(
							array(
								'DevelopmentData.topproject_id' => $order_new['topproject_id'],
								'DevelopmentData.equipment_type_id' => $order_new['equipment_type_id'],
							),
							array('DevelopmentData.equipment_id' => $order_new['equipment_id'])
						);
						$this->Equipment->recursive = -1;
						$this->Equipment->updateAll(
							array(
								'Equipment.topproject_id' => $order_new['topproject_id'],
								'Equipment.equipment_type_id' => $order_new['equipment_type_id'],
							),
							array('Equipment.id' => $order_new['equipment_id'])
						);
						$this->Reportnumber->recursive = -1;
						$this->Reportnumber->updateAll(
							array(
								'Reportnumber.topproject_id' => $order_new['topproject_id'],
								'Reportnumber.testingcomp_id' => $order_new['testingcomp_id'],
								'Reportnumber.equipment_type_id' => $order_new['equipment_type_id'],
							),
							array('Reportnumber.equipment_id' => $order_new['equipment_id'])
						);

						$FormName = array();
						$FormName['controller'] = 'equipments';
						$FormName['action'] = 'view';
						$FormName['terms'] = $order_new['topproject_id'].'/'.$order_new['equipment_type_id'].'/'.$order_new['equipment_id'];

						$this->Session->setFlash(__('Equipment ' . $Equipment['Equipment']['discription']) .' ' .__('has been moved to this location.',true));
						$this->set('FormName', $FormName);
						$this->set('saveOK', 1);
					}
				}
			}
		}

		$options = array(
			'conditions' => array(
								'Reportnumber.topproject_id' => $projectID,
								'Reportnumber.order_id' => $id
							)
						);


		$this->Reportnumber->recursive = 0;

		$SettingsArray = array();
		$SettingsArray['editlink'] = array('discription' => __('Edit',true), 'controller' => 'orders','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

//		$this->set('headline', $headline);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('Equipment', $Equipment);
//		$this->set('settings', $arrayData['settings']);
		$this->set('locale', $this->Lang->Discription());

	}

	public function delete($id = null) {

		$this->layout = 'modal';
		$id = $this->request->projectvars['VarsArray'][2];

		$this->Equipment->id = $id;

		if (!$this->Equipment->exists()) {
			throw new NotFoundException(__('Invalid equipment type'));
		}

		if(isset($this->request->data['Equipment']['id'])){

			// Test ob Berechtigung für dieses Equipment vorliegt fehlt noch
			$del = 1;

			$this->loadModel('Reportnumber');
			$this->Reportnumber->recursive = -1;
			$this->Reportnumber->Order->recursive = -1;

			$this->Equipment->updateAll(
				array('Equipment.status' => $del),
				array('Equipment.id' => $id)
			);

			$this->Reportnumber->updateAll(
				array('Reportnumber.delete' => 1),
				array('Reportnumber.equipment_id' => $id)
			);

			$this->Reportnumber->Order->updateAll(
				array('Order.deleted' => 1),
				array('Order.equipment_id' => $id)
			);

			$logArray = array('delete' => 'Equipment gelöscht');
			$this->Autorisierung->Logger($id,$logArray);

			$this->Session->setFlash(__('Equipment deleted'));
			$this->include_index();
		}

		$equipment = $this->Equipment->find('first',array('conditions' => array('Equipment.id' => $id)));
		$this->request->data = $equipment;

		$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'equipments','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
		$this->set('SettingsArray',$SettingsArray);
	}
}
