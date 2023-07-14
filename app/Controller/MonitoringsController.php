<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

/**
 *
 *
 * @property Advance $Advance
 */
class MonitoringsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Drops','MonitoringTool');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'modal';
	protected $writeprotection = false;

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		App::uses('Sanitize', 'Utility');
		// Es wird das in der Auswahl gewählte Project in einer Session gespeichert

		$this->Navigation->ReportVars();

		$this->loadModel('User');
		$this->loadModel('Testingcomp');
		$this->loadModel('Cascade');
		$this->loadModel('CascadeGroup');
		$this->loadModel('CascadegroupsCascade');
		$this->loadModel('Order');
		$this->loadModel('Topproject');

		$lastmodalURL = explode('/',$this->Session->read('lastmodalURL'));

		$noAjaxIs = 0;
		$noAjax = array();

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

		$this->set('roll', $this->Auth->user('roll_id'));
		$this->set('lang', $this->Lang->Choice());
		$this->set('select_lang', Configure::read('Config.language'));
		$this->set('selected', $this->Lang->Selected());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterRender() {
		$last = $this->Session->read();
		// Bei anderen Actions im Dropdown controller nicht mitschreiben
		if($last['lastmodalArrayURL']['controller'] != $this->request->controller) $this->Navigation->lastURL();
	}

/**
 * index method
 *
 * @return void
 */

	public function index() {

		$this->layout = 'ajax';
		$SettingsArray = array();

		if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $landig_page = 1;
		if(isset($this->request->data['option']) && !empty($this->request->data['option'])) $option = $this->request->data['option'];

		$data = $this->MonitoringTool->GetData();

		$this->request->data = $data;

		$breads[] = array(
						'discription' => __('Monitoring manager', true),
						'controller' => 'monitorings',
						'action' => 'index',
						'pass' => null
		);

		$Task = $this->MonitoringTool->getTasks();

		$this->set('tasks', $Task);
		$this->set('monitoring', $data);

		if(isset($landig_page) && $landig_page == 1){

			switch ($option) {
				case 'contact':

				$this->render('contact_landingpage','blank');

				break;

				case 'odo':
				$this->render('odo_landingpage','blank');

				break;

				default:

				$this->render('index_landingpage','blank');

				break;
			}

		}


		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);

	}

	public function json_scheme() {

		$this->layout = 'blank';

		$Response = $this->MonitoringTool->GetNewData();
		sort($Response);

		$this->set('Response',json_encode($Response));

	}
}
