<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class IncomminggoodinspectionsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Drops');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'modal';
	protected $writeprotection = false;

	function beforeFilter() {

		App::import('Vendor', 'Authorize');

		$this->Navigation->ReportVars();

		$lastmodalURL = explode('/',$this->Session->read('lastmodalURL'));

		$noAjaxIs = 0;
		$noAjax = array('checklist','printstatistic');

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

	function index() {

		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

		$JsonAutocomplete = array(
			'QuicksearchIncommingGoodInspectionSearchingOrderNo' => array('B123','B1234','B12345'),
			'QuicksearchIncommingGoodInspectionSearchingMaterial' => array('M123','M1234','M12345'),
			'QuicksearchIncommingGoodInspectionSearchingCharge' => array('C123','C1234','C12345'),
			'QuicksearchIncommingGoodInspectionSearchingTechnicalPlace' => array('T123','T1234','T12345'),
		);

		$this->set('JsonAutocomplete',$JsonAutocomplete);

		if(isset($landig_page_large) && $landig_page_large == 1){

			$this->render('landingpage_large','blank');
			return;

		}
	}
}
