<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
/**
* Testingcomps Controller
*
* @property Soapservice $Soapservice
*/
class SoapservicesController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Paginator', 'SelectValue','RequestHandler', 'Data', 'Xml','Soap');
	public $helpers = array('Lang','Navigation','JqueryScripte');
	public $layout = 'ajax';

	function beforeFilter() {
		App::import('Vendor', 'Authorize');
		$this->loadModel('User');
		//	$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('abacusfindodre');

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


	public function abacusfindodre() {


		App::uses('Sanitize', 'Utility');

		$this->layout = 'json';
		//	print_r($this->request->data['term']);
		if(isset($this->request->data['term'])) {
			$term = Sanitize::stripAll($this->request->data['term']);
			$input = $term;
		}
		$Service = $this->Soapservice->find('first',array('conditions'=>array('testingcomp_id'=>$this->Auth->user('testingcomp_id'))));
		$wsdl = Configure::read('xml_folder') . 'soap' . DS . $this->Auth->user('testingcomp_id').DS.'order'.DS.'ordesalesorder.wsdl';
		$logindata = array('UserName'=>$Service['Soapservice']['username'], 'Password'=>$Service['Soapservice']['password'], 'Mandant'=>$Service['Soapservice']['mandant']);
		$orderdata = $this->Soap->OdreFindRequest($logindata,$Service['Soapservice']['webservice_url'],$wsdl,$input);

		if(!empty($orderdata[0])){

			$response['OrderData'] = $orderdata[0]['SalesOrderHeader']['SalesOrderHeaderFields'];

			if(!empty($response['OrderData'])){
				$wsdl = Configure::read('xml_folder') . 'soap' . DS . $this->Auth->user('testingcomp_id').DS.'customer'.DS.'Customer.wsdl';
				$inputadress = $response['OrderData']['CustomerNumber'];
				$dataforadress = $this->Soap->AdressFindRequest($logindata,'https://abacus.joerimann.com:443/abaconnect/services/Customer_2019_00',$wsdl,$inputadress);
					//	echo '<pre>';var_dump($dataforadress[0]->Customer->AddressData);echo '</pre>'; die();

					if(!empty($dataforadress[0])){
						$response['AdressData']	= $dataforadress[0]['Customer']['AddressData'];

						$response['AdressData'] = $response['AdressData'][0];
					}
				}
			}
			//bezeichnung für schnittstelle aus datenbank holen evtl verknüpfen mit bootstrap
			$returndata = array();
			$reportnumber = '';
			if(isset($this->request->data['reportnumber'])){

				$this->loadModel('Reportnumber');
				$this->Reportnumber->recursive = 1;
				$reportnumber = $this->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id'=>$this->request->data['reportnumber'])));
				$verfahren = $reportnumber ['Testingmethod']['value'];
				//$this->Soap->SendFromReport($reportnumber,$verfahren);

				$xml = $this->Xml->XmltoArray($verfahren,'file',ucfirst($verfahren));
				$Generally = 'Report'.ucfirst($verfahren).'Generally';
				foreach ($xml->$Generally->children() as $key => $value) {
					if (!isset($value->webservice)) continue;
					if (!isset($value->webservice->fieldname)) continue;
					if (empty($response)) continue;
					$data = array();
					foreach ($value->webservice->fieldname->value as $v_key => $v_value) {
						$returndata['Report'.ucfirst($verfahren).'Generally'.Inflector::camelize($key)] = '';
						if(!isset($response[trim($value->webservice->model)])) continue;
						if(!isset($response[trim($value->webservice->model)][trim($v_value)])) continue;
						if(empty($response[trim($value->webservice->model)][trim($v_value)])) continue;
						$extractdata = $response[trim($value->webservice->model)][trim($v_value)];

						$data[] = $extractdata;
						$returndata['Report'.ucfirst($verfahren).'Generally'.Inflector::camelize($key)]= implode(' ',$data);
						// code...
					}
					//	$reurndata[$key] =

				}
			}

			$this->set('response',$returndata);

		}
	}
