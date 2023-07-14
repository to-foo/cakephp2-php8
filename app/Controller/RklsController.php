<?php
App::uses('AppController', 'Controller');

class RklsController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Rkls','Data','ExpeditingTool','Drops','Image','MessagesTool');
	public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
	public $layout = 'ajax';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');

		$this->Navigation->ReportVars();

		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('shortview');

		$this->loadModel('RklNumber');

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
		$this->set('SettingsArray',array());
		$this->set('locale', $this->Lang->Discription());

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	public function classes() {

		$this->layout = 'blank';

		$xml = $this->Xml->DatafromXml('Rkl','file',null);
/*
		$options = array();

		$this->paginate = array(
				'conditions' => array($options),
				'order' => array('id' => 'desc'),
				'limit' => 25
		);

		$Rkls = $this->paginate('Rkl');
*/
		$this->set('SearchFieldsStandard',$xml['settings']);

	}

	public function index() {

		$this->layout = 'blank';

		$xml = $this->Xml->DatafromXml('Rkl','file',null);
/*
		$options = array();

		$this->paginate = array(
				'conditions' => array($options),
				'order' => array('id' => 'desc'),
				'limit' => 25
		);

		$Rkls = $this->paginate('Rkl');
*/
		$this->set('SearchFieldsStandard',$xml['settings']);

	}

	public function autofast() {

		$this->layout = 'json';

		$request = array();

		$request = $this->Rkls->AutoFastAdditional($request);
		$request = $this->Rkls->UpdateSearchResult($request);
		$request = $this->Rkls->ShowSearchResult($request);
		$request = $this->Rkls->SaveSearchResult($request);


		if(isset($this->request->data['show_search_result'])){

			$xml = $this->Xml->DatafromXml('Rkl','file',null);

			$this->request->data = $request;

			$this->set('xml',$xml['settings']);

			$this->render('edit','blank');

		} else {
			$this->set('response',json_encode($request));
		}

	}

	public function rkls() {

		$this->layout = 'json';

		$response = array();

		$response = $this->Rkls->AutoFastAdditional($response);
		$response = $this->Rkls->UpdateSearchResultRkls($response);
		$response = $this->Rkls->ShowSearchResultRkls($response);
		$response = $this->Rkls->ShowSearchResultRklsDupli($response);

		$xml = $this->Xml->DatafromXml('Rkl','file',null);

		//		$request = $this->Rkls->SaveSearchResult($request);
		if(isset($this->request->data['show_search_result']) && !isset($this->request->data['json_true'])){


			$response = $this->Rkls->FlattenRequestData($response,$xml);

			$this->request->data = $response;

			$this->set('xml',$xml['settings']);
			$this->render('edit_rkls','blank');

		} else {
			$response['Locale'] = $this->Lang->Discription();
			$response['Xml'] = $xml['settings'];
			$this->set('response',json_encode($response));
		}

	}

	public function view() {
	}

	public function add() {
	}

	public function edit() {
	}

	public function delete() {
	}
}
