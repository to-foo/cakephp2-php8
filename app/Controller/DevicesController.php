<?php
App::uses('AppController', 'Controller');
App::uses('ConnectionManager', 'Model');

/**
 * Examiners Controller
 *
 * @property Examiner $Examiner
 * @property AutorisierungComponent $Autorisierung
 * @property LangComponent $Lang
 * @property NavigationComponent $Navigation
 * @property SicherheitComponent $Sicherheit
 */
class DevicesController extends AppController {

	public $components = array('Drops','Formating','Qualification','Data','Session','Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue', 'Paginator','Xml','Search','Drops');
	protected $writeprotection = false;

	public $helpers = array('Lang','Navigation','JqueryScripte','Pdf','Quality');
	public $layout = 'ajax';

	public function _validateDate($date)
	{
		if(!is_string($date)) return false;
	    $d = DateTime::createFromFormat('Y-m-d', $date);
	    return $d && $d->format('Y-m-d') == $date;
	}

	function beforeFilter() {

		if(Configure::read('DeviceManager') == false){
			die();
		}

		App::import('Vendor', 'Authorize');
		$this->loadModel('User');
		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('email','quicksearch','files','getfiles','certificatefile','getcertificatefile','pdf', 'pdfinv', 'autocomplete');

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

		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$this->Lang->Choice();
		$this->Lang->Change();
		$this->Navigation->ReportVars();

		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('menues', $this->Navigation->Menue());
//		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('lang_discription', $this->Lang->Discription());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->set('locale', $this->Lang->Discription());
	  $searchdescription = '';

	  if(Configure::check('DeviceManagerSearchField')){
	  	$quicksearchfield = Configure::read('DeviceManagerSearchField');
	  }
	  else $quicksearchfield = '';
	   $arrayData = $this->Xml->XmltoArray('Device','file',null);
	   $locale = $this->Lang->Discription();
	   foreach($arrayData->Device->children() as $_settings => $value){
	      if (trim($value->key) == $quicksearchfield) {
	          $searchdescription =  trim($value->discription->$locale);
	      }
	  }

		// Werte für die Schnellsuche
		if(Configure::check('DeviceManagerShowSummary') && Configure::read('DeviceManagerShowSummary') == true){
			$ControllerQuickSearch =
				array(
					'Model' => 'Device',
					'Field' => $quicksearchfield,
					'description' =>$searchdescription,
					'minLength' => 1,
					'targetcontroller' => 'devices',
					'target_id' => 'device_id',
					'targetation' => 'view',
					'Quicksearch' => 'QuickDevicesearch',
					'QuicksearchForm' => 'QuickDevicesearchForm',
					'QuicksearchSearchingAutocomplet' => 'QuickDevicesearchSearchingAutocomplet',
			);

			$this->set('ControllerQuickSearch',$ControllerQuickSearch);
		}

		$DeviceManagerAutocomplete = null;

		if(Configure::check('DeviceManagerSearchAutocomplete') && Configure::read('DeviceManagerSearchAutocomplete') == true){
			$DeviceManagerAutocomplete =
				array(
					'Model' => 'Device',
				);
		}

	//	$this->request->data['DeviceManagerAutocomplete'] = $DeviceManagerAutocomplete;

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterFilter() {
		$this->Navigation->lastURL();

	}

	public function autocomplete() {

		App::uses('Sanitize', 'Utility');

 		$this->layout = 'json';

		$fields = explode('_',Inflector::underscore($this->request->data['field']));

		if(count($fields) < 2){
			$this->set('test',json_encode(array()));
			return;
		}

		unset($fields[0]);

		$field = Sanitize::clean(implode('_',$fields));

		$data = Sanitize::escape($this->request->data['term']);

		$Source = $this->Device->getDataSource('columns');
		$table = $Source->describe('devices');

		if(!isset($table[$field])){
			$this->set('test',json_encode(array()));
			return;
		}

		$options = array(
						'limit' => 20,
						'fields' => array($field),
						'group' => array($field),
						'conditions' => array(
							'Device.deleted' => 0,
							'Device.active' => 1,
							'Device.' . $field . ' LIKE' => '%' . $data . '%'
							)
						);



		$result = $this->Device->find('list',$options);

//$this->Autorisierung->WriteToLog($result);


		$this->set('test',json_encode($result));

	}



	public function pdf() {

	 	$this->layout = 'pdf';

		if($this->request->projectvars['VarsArray'][16] == 0 && isset($this->request->data['device_id']) && $this->request->data['device_id'] > 0){
			$this->request->projectvars['VarsArray'][16] = $this->Sicherheit->Numeric($this->request->data['device_id']);
		}

		if($this->request->projectvars['VarsArray'][16] > 0){
			$device_id = $this->request->projectvars['VarsArray'][16];
		}
		else {
			return;
		}

		if(isset($this->request->data['showpdf']) && $this->request->data['showpdf'] == 1) $ShowPdf = 1;

		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');

		$arrayData = $this->Xml->DatafromXml('DeviceEdit','file',null);
		if(!is_array($arrayData)) return;

		$this->set('xml_device',$arrayData['settings']);

		$arrayData = null;
		$arrayData = $this->Xml->DatafromXml('DeviceCertificateEdit','file',null);
		if(!is_array($arrayData)) return;

		$this->set('xml_device_cert',$arrayData['settings']);

		$options = array(
						'conditions' => array(
											'Device.deleted' => 0,
											'Device.id' => $device_id,
											'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
										)
						);

		$this->Device->recursive = 1;
		$Device = $this->Device->find('first',$options);

		if($Device['Examiner']['id'] == 0)unset($Device['Examiner']);

		if(count($Device) == 0) return;
		$options = array(
						'order' => array('certificat ASC'),
						'conditions' => array(
											'DeviceCertificate.deleted' => 0,
											'DeviceCertificate.device_id' => $device_id,
											'DeviceCertificate.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
										)
						);

		$this->Device->DeviceCertificate->recursive = 1;
		$DeviceCertificate = $this->Device->DeviceCertificate->find('all',$options);

		if(count($DeviceCertificate) > 0){

			$this->Device->DeviceCertificate->DeviceCertificateData->recursive = -1;
			foreach($DeviceCertificate as $_key => $_DeviceCertificate){

				$options = array(
					'order' => array('DeviceCertificateData.id DESC'),
					'conditions' => array(
										'DeviceCertificateData.deleted' => 0,
										'DeviceCertificateData.device_id' => $device_id,
										'DeviceCertificateData.device_certificate_id' => $_DeviceCertificate['DeviceCertificate']['id'],
										'DeviceCertificateData.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
									)
								);
				$DeviceCertificateData = $this->Device->DeviceCertificateData->find('first',$options);

				if(count($DeviceCertificateData) > 0){
					$DeviceCertificate[$_key]['DeviceCertificateData'] = $DeviceCertificateData['DeviceCertificateData'];
				}
				else{
					$DeviceCertificate[$_key]['DeviceCertificateData'] = array();
				}
			}

			$Device['DeviceCertificate'] = $DeviceCertificate;
		}

		foreach($Device['DeviceCertificate'] as $_key => $_DeviceCertificate){
			$Device['DeviceCertificate'][$_key] = $this->Qualification->DeviceCertification($_DeviceCertificate,$model);
		}

		$this->loadModel('Testingcomp');
		$options = array(
						'conditions' => array(
											'Testingcomp.id' => $Device['Device']['testingcomp_id'],
										)
						);
		$this->Testingcomp->recursive = -1;
		$Testingcomp = $this->Testingcomp->find('first',$options);
		$Device = array_merge($Device,$Testingcomp);

		$adress = null;
		$testingcomp = null;
		$contact = null;
		$testingmethods = null;

		// Prüfverfahren in String
		if(count($Device['DeviceTestingmethod']) > 0){
			foreach($Device['DeviceTestingmethod'] as $_key => $_DeviceTestingmethod){
				$testingmethods .= $_DeviceTestingmethod['verfahren'] . ' ';
			}
		}

		$Device['DeviceTestingmethod']['summary'] = $testingmethods;

		if(!empty($Device['Testingcomp']['firmenname']))$testingcomp .= $Device['Testingcomp']['firmenname'] . "\n";
		if(!empty($Device['Testingcomp']['firmenzusatz']))$testingcomp .= $Device['Testingcomp']['firmenzusatz'] . "\n";
		if(!empty($Device['Testingcomp']['strasse']))$testingcomp .= $Device['Testingcomp']['strasse'] . "\n";
		if(!empty($Device['Testingcomp']['plz']))$testingcomp .= $Device['Testingcomp']['plz'] . " ";
		if(!empty($Device['Testingcomp']['ort']))$testingcomp .= $Device['Testingcomp']['ort'] . "\n";
		if(!empty($Device['Testingcomp']['telefon']))$testingcomp .= __('Tel',true) . ': ' . $Device['Testingcomp']['telefon'] . "\n";
		if(!empty($Device['Testingcomp']['telefax']))$testingcomp .= __('Fax',true) . ': ' . $Device['Testingcomp']['telefax'] . "\n";
		if(!empty($Device['Testingcomp']['internet']))$testingcomp .= $Device['Testingcomp']['internet'] . "\n";
		if(!empty($Device['Testingcomp']['email']))$testingcomp .= $Device['Testingcomp']['email'] . "\n";

		$Device['Testingcomp']['summary'] = $testingcomp;

		$CurrentTestingcomp = $this->Auth->user('Testingcomp');

		if(!empty($CurrentTestingcomp['firmenname']))$adress .= $this->Auth->user('Testingcomp.firmenname') . "\n";
		if(!empty($CurrentTestingcomp['firmenzusatz']))$adress .= $this->Auth->user('Testingcomp.firmenzusatz') . "\n";
		if(!empty($CurrentTestingcomp['strasse']))$adress .= $this->Auth->user('Testingcomp.strasse') . "\n";
		if(!empty($CurrentTestingcomp['plz']))$adress .= $this->Auth->user('Testingcomp.plz') . " ";
		if(!empty($CurrentTestingcomp['ort']))$adress .= $this->Auth->user('Testingcomp.ort') . " ";

		if(!empty($CurrentTestingcomp['telefon']))$contact .= __('Tel',true) . ': ' . $this->Auth->user('Testingcomp.telefon') . "\n";
		if(!empty($CurrentTestingcomp['telefax']))$contact .= __('Fax',true) . ': ' . $this->Auth->user('Testingcomp.telefax') . "\n";
		if(!empty($CurrentTestingcomp['internet']))$contact .= $this->Auth->user('Testingcomp.internet') . "\n";
		if(!empty($CurrentTestingcomp['email']))$contact .= $this->Auth->user('Testingcomp.email') . " ";

		if(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png'){
			$this->request->data['deviceinfo']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png';
		}

		$this->request->data['deviceinfo']['info'] = $Device;
		$this->request->data['deviceinfo']['contact'] = $contact;
		$this->request->data['deviceinfo']['adress'] = $adress;

		if(isset($ShowPdf) && $ShowPdf == 1){

			$this->layout = 'json';

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;
			$PDFView->set('return_show',true);

			$Data = $PDFView->render('pdf');

			$this->set('request',json_encode(array('string' => $Data)));
			$this->render('pdf_show');

			return;

		}
	}

	public function quicksearch() {
		App::uses('Sanitize', 'Utility');

 		$this->layout = 'json';
                 if(Configure::check('DeviceManagerSearchField')){
                    $quicksearchfield = Configure::read('DeviceManagerSearchField');
                }


                  if(Configure::check('DeviceManagerDisplayFields')){
                    $displaysearchfields = Configure::read('DeviceManagerDisplayFields');
                    $displayfields = explode(".", $displaysearchfields);
                  }
		$term = Sanitize::stripAll($this->request->data['term']);
		$term = Sanitize::paranoid($term);

		$options = array(

						'limit' => 20,
						//'group' => array('intern_no'),
						'conditions' => array(
//								'Device.testingcomp_id' => $this->Autorisierung->ConditionsTopprojects(),
								'Device.'.$quicksearchfield.' LIKE' => '%'.$term.'%',
								'Device.deleted' => 0
							)
						);


		$this->Device->recursive = -1;
		$device = $this->Device->find('all', $options);

		$response = array();

		foreach($device as $_key => $_device){
                    $responsevalue = '';
                    foreach ($displayfields as $dkey => $dvalue) {
                       isset($_device['Device'][$dvalue]) ? $responsevalue.= $_device['Device'][$dvalue].' ':'';
                    }
			array_push($response, array('key' => $_device['Device']['id'],'value' => $responsevalue));
		}


		$this->set('response',json_encode($response));
	}

	public function barcode() {

		if(Configure::read('BarcodeDeviceScanner') === false) die(__('Deactiv',true));

		App::uses('Sanitize', 'Utility');

 		$this->layout = 'json';
		$barcode = Sanitize::stripAll($this->request->data['barcode']);
		$barcode = Sanitize::paranoid($barcode);

		$options = array(
						'conditions' => array(
//								'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTopprojects(),
								'Device.barcode' => $barcode,
								'Device.deleted' => 0
							)
						);

		$this->Device->recursive = 1;
		$device = $this->Device->find('all', $options);

		if(!empty($device)){

			if(count($device) == 1){
				$message['text'] = __('1 Device found',true);
				$message['status'] = 'one';
				$message['class'] = 'hint';
				$this->request->projectvars['VarsArray'][15] = $device[0]['DeviceTestingmethod'][0]['id'];
				$this->request->projectvars['VarsArray'][16] = $device[0]['Device']['id'];
			}
			if(count($device) > 1){
				$message['text'] = count($device).' '. __('Devices found',true).'.';
				$message['status'] = 'more';
				$message['class'] = 'hint';
			}
		}
		elseif(empty($device)){
			$message['text'] = __('Device not found',true);
			$message['class'] = 'error';
		}

		$this->set('message',$message);
		$this->set('device',$device);
	}

	public function save($id = null) {
		// Die IDs des Projektes und des Auftrages werden getestet

		$this->layout = 'blank';

//		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);


		$this_id = $this->request->data['this_id'];
		$orginal_data = $this->request->data['orginal_data'];
		$save_data = array();

		unset($this->request->data['orginal_data']);

		foreach($this->request->data as $_key => $_data){
			if(!is_array($_data)){
				unset($this->request->data[$_key]);
			}
		}

		$Model = key($this->request->data);

		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// Unbedingt noch eine Modelüberprüfung einbauen
		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!

		if($Model == 'Device'){
			$id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		}
		if($Model == 'DeviceCertificate'){
			$id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		}
		if($Model == 'DeviceCertificateData'){
			$id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);
		}

		if(!isset($id)){
			$this->set('error',__('The value could not be saved. Wrong parameters!'));
			$this->render();
			return;
		}

		if(!$this->loadModel($Model)) {
			$this->loadModel($Model);
		}

		if (!$this->$Model->exists($id)) {
			throw new NotFoundException(__('Invalid ID'));
		}

		$field_key = key($this->request->data[key($this->request->data)]);

		if($field_key == '0'){
			$field_key = key($this->request->data[$Model][0]);
			$this_value = $this->request->data[$Model][0][$field_key];
		}
		else {
			$this_value = $this->request->data[$Model][$field_key];
		}

		foreach($orginal_data as $_key => $_orginal_data){
			foreach($_orginal_data as $__key => $__orginal_data){
				if($__key == '0'){
					$orginal_data[$_key][key($__orginal_data)] = $__orginal_data[key($__orginal_data)];
					unset($orginal_data[$_key][0]);
				}
				if(is_array($__orginal_data)) {
					if(is_numeric(key($__orginal_data))){
						$this_value_array = explode(',',str_replace(' ','',$this_value));
					}
				}
			}
		}

		$save_data[$Model]['id'] = $id;
		$save_data[$Model]['user_id'] = $this->Auth->user('id');

		if(isset($orginal_data[$Model][$field_key]) && is_array($orginal_data[$Model][$field_key])){
			$save_data[$Model][$field_key] = $this_value_array;
		}
		else {
			$save_data[$Model][$field_key] = $this_value;
		}

		$orginalData = $this->$Model->find('first',array('conditions' => array($Model.'.id' => $id)));

		$last_value = $this_value;
		$this_field = key($this->request->data[$Model]);
		$last_id_for_radio = $Model . Inflector::camelize($field_key);

		$this->set('this_id',$this_id);
		$this->set('last_value',$last_value);
		$this->set('this_value',$this_value);
		$this->set('last_id_for_radio',$last_id_for_radio);

		if($orginalData[$Model]['active'] == 0 && $this_field != 'active'){
			$this->set('closed',__('The value could not be saved. The record is deactive!'));
			$this->render();
			return;
		}

		if($orginalData[$Model]['deleted'] == 1){
			$this->set('closed',__('The value could not be saved. The record is deleted!'));
			$this->render();
			return;
		}

		if($this->$Model->save($save_data)) {
			$this->Autorisierung->Logger($id,$orginal_data);
			$this->set('success',__('The value has been saved.'));
		}
		else {
			$this->set('error',__('The value could not be saved. Please try again!'));
		}
	}

	public function index() {
		$this->Session->delete('DeviceActive');
		if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $landig_page = 1;
		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

		$this->Session->delete('searchdevice');
		$this->Session->delete('SearchFormDataDevice.History');


		$this->Navigation->ResetSessionForPaging();

		$methods = array();

		if($this->request->projectvars['VarsArray'][1] > 0) {
			$this->Device->recursive = 1;
			$_dev = $this->Device->find('first', array('conditions'=>array('Device.id'=>$this->request->projectvars['VarsArray'][1])));
			$methods = Hash::extract($_dev, 'DeviceTestingmethod.{n}.id');
		}

		$devices_testingmethods = $this->Device->DeviceTestingmethod->find('all',array(
				'conditions' => array_filter(array(
						'DeviceTestingmethod.id' => $methods
				)),
				'contain' => array(
					'Device' => array(
						'fields' => array('id'),
						'conditions' => array(
							'Device.testingcomp_id' => $this->Auth->user('testingcomp_id')
						)
					)
				)
			)
		);

		$ManyModelArray = array('DeviceTestingmethod','id','id');

		foreach($devices_testingmethods as $_key => $_devices_testingmethods){

			$iddevices = array();
			$iddevices = $this->Data->BelongsToManySelected($_devices_testingmethods,'DeviceTestingmethod','Device',array('TestingmethodsDevices','device_id','testingmethod_id'));
			$iddevices = $iddevices ['Device']['selected'];
			$devices = $this->Device->find('all',array('conditions'=>array('Device.id' =>$iddevices,'Device.testingcomp_id' =>$this->Auth->user('testingcomp_id')))) ;
			$devices_testingmethods[$_key]['Device'] = $devices;

			if(count($devices) == 0) unset($devices_testingmethods[$_key]);

		}

		$SettingsArray = array();

		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}

		if(Configure::read('DocumentManager') == true){
			$SettingsArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
		}


		$SettingsArray['addsearching'] = array('discription' => __('Search device', true), 'controller' => 'devices', 'action'=>'search', 'terms'=>null, 'rel'=>'devices');

		$SettingsArray['addsearchingmonitoring'] = array('discription' => __('Search monitoring', true), 'controller' => 'devices', 'action'=>'searchmonitoring', 'terms'=>null, 'rel'=>'devices');

		$breads = $this->Navigation->Breads(null);

		$breads[1]['discription'] = __('Devices',true);
		$breads[1]['controller'] = 'devices';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;

		$this->set('breads', $breads);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('devices_testingmethods', $devices_testingmethods);

		if(isset($landig_page) && $landig_page == 1){

			$this->render('landingpage','blank');
			return;

		}

		if(isset($landig_page_large) && $landig_page_large == 1){

			$this->render('landingpage_large','blank');
			return;

		}
	}

	public function overview() {
	//	$currentFormData = $this->request->data;

		$this->Navigation->GetSessionForPaging();
		$this->Session->delete('searchdevice');
		$this->Session->delete('Device.form');



			$order = $this->Data->SortDevicesTable();
			$active = 1;
			if(isset($this->request->data['SortDevice']['active'])){
				$active = $this->request->data['SortDevice']['active'];
			}else{
				if ($this->Session->check('DeviceActive')== true) {
					$active = $this->Session->read('DeviceActive');
					$this->request->data['SortDevice']['active'] = $active;
				}
			}
			if(!isset($this->_controller->request->query['is_infinite'])){
				$this->Session->write('SortDevicesTable', $order);
				$this->Session->write('DeviceActive', $active);
			}

		$device = $this->Data->SortDevicesTable();



		if($this->request->projectvars['VarsArray'][15] > 0){
			$testingmethod_id = $this->request->projectvars['VarsArray'][15];
		}
		else {
			if(isset($this->request->data['QuickDevicesearch'])) {
				$device_id = ($this->Sicherheit->Numeric($this->request->data['device_id']));

				if($device_id == 0){
					throw new NotFoundException(__('Invalid value XX'));
				}
			}
			else {
				throw new NotFoundException(__('Invalid value XXX'));
			}
		}

		$xml = $this->Xml->DatafromXml('display_device','file',null);

		if(!is_object($xml['settings'])){
			pr('Einstellungen konnten nicht geladen werden.');
			return;
		} else {
			$this->set('xml',$xml['settings']);
		}

		$this->Device->recursive = 1;
		$this->Device->DeviceTestingmethod->recursive = -1;

		if(isset($testingmethod_id)){
			$testingmethod = $this->Device->DeviceTestingmethod->find('first',array('conditions' => array('DeviceTestingmethod.id' => $testingmethod_id)));
		}

		//$order = array('Device.intern_no' =>'asc','Device.device_type' => 'asc','Device.name' => 'asc','Device.producer' => 'asc','Device.registration_no' => 'asc','Device.working_place' => 'asc');
		if(!isset($device_id)){
			$this->paginate = array(
				'order' => $order,
				'limit' => 10,
				'conditions' => array(
						'Device.deleted' => 0,
						'Device.active' => $active

					),
				'joins' => array(
					array(
						'table' => 'devices_testingmethods_devices',
						'alias' => 'DeviceTestingmethod',
						'type' 	=> 'INNER',
						'conditions' => array(
    	        	        'DeviceTestingmethod.testingmethod_id' => $testingmethod_id,
	        	            'DeviceTestingmethod.device_id = Device.id'
						)
					)
				)
			);

			$devices = $this->paginate('Device');
		}
		elseif(isset($device_id)){
			$this->paginate = array(
				'order' => $order,
				'limit' => 1,
				'conditions' => array(
					'Device.id' => $device_id
				)
			);
			$devices = $this->paginate('Device');

			if(count($devices) == 0){
				$this->render();
				return;
			}
			elseif(count($devices) > 0){
				$this->request->projectvars['VarsArray'][15] = $devices[0]['DeviceTestingmethod'][0]['id'];
			}

			// Die Url für das Actionatribut im Quicksearchform muss angepasst werden
			// Wenn das Eingabefeld geleert wird muss die aktuelle Seite mit Parametern aufgerufen werden
			// sonst kommt ein Error
			$this->request->here = $this->request->here.'/'.implode('/',$this->request->projectvars['VarsArray']);
		}

		if(isset($testingmethod)){
			$this->set('testingmethod', $testingmethod);
		}
		// die Einzelnen Überwachungen den Geräten zuordnen
		foreach($devices as $_key => $_devices){
			$monitorings[$_devices['Device']['id']] = $this->Qualification->MonitoringSummary($_devices,array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));

		}

		$SettingsArray = array();

		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}

		if(Configure::read('DocumentManager') == true){
			$SettingsArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
		}

		$SettingsArray['addsearching'] = array('discription' => __('Search device', true), 'controller' => 'devices', 'action'=>'search', 'terms'=>null, 'rel'=>'devices');
		$SettingsArray['addsearchingmonitoring'] = array('discription' => __('Search monitoring', true), 'controller' => 'devices', 'action'=>'searchmonitoring', 'terms'=>null, 'rel'=>'devices');

		$breads = $this->Navigation->Breads(null);

		$breads[1]['discription'] = __('Devices',true);
		$breads[1]['controller'] = 'devices';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;

		$breads[2]['discription'] = $testingmethod['DeviceTestingmethod']['verfahren'];
		$breads[2]['controller'] = 'devices';
		$breads[2]['action'] = 'overview';
		$breads[2]['pass'] = $this->request->projectvars['VarsArray'];

		$this->set('breads', $breads);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('devices', $devices);
		$this->set('monitorings', $monitorings);
//		$this->set('DeviceData', $currentFormData);

		$this->Navigation->SetSessionForPaging();

		// Infinite scroll
		if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true) $this->Navigation->InfiniteScrollPaging('overview_infinite');

	}

	public function searchmonitoring()
	{
		$HistorySearch = $this->Search->MatchHistoryForFormDevice();

		$this->loadModel('DeviceCertificateData');

		$locale = $this->Lang->Discription();

		$reqData = $this->request->data;

		$SearchFieldsStandard = $this->Xml->XmltoArray('device_search_monitoring', 'file', null);
		if(isset($this->request->data['json']) && $this->request->data['json'] == 1){
			$json = 1;
		}

		if(isset($json) && $json == 1) {
			$this->Session->delete('searchdevice.sorting');
			$FieldValues = $this->Search->SearchModulFields($SearchFieldsStandard, array(), array(), 'update');
		} else {
			if($this->Session->check('searchdevice.sorting') == true && !isset($this->request->data['SortDevice'])) $this->request->data['SortDevice'] = $this->Session->read('searchdevice.sorting');
			$FieldValues = $this->Search->SearchModulFields($SearchFieldsStandard, array(), array(), 'initial');
		}
		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}
		if(Configure::read('DocumentManager') == true){
			$SettingsArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
		}
		$SettingsArray['addsearching'] = array('discription' => __('Search device', true), 'controller' => 'devices', 'action'=>'search', 'terms' => null, 'rel' => 'devices');
		$SettingsArray['addsearchingmonitoring'] = array('discription' => __('Search monitoring', true), 'controller' => 'devices', 'action'=>'searchmonitoring', 'terms'=>null, 'rel'=>'devices');

		$breads = $this->Navigation->Breads(null);
		$breads[1]['discription'] = __('Devices',true);
		$breads[1]['controller'] = 'devices';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;

		$breads[2]['discription'] = __('Search',true);
		$breads[2]['controller'] = 'devices';
		$breads[2]['action'] = 'searchmonitoring';
		$breads[2]['pass'] = null;

		if(isset($this->request->data['DeviceCertificateData'])){
			$this->Session->delete('searchdevicecertificatedata');

			$this->request->data['DeviceCertificateData'] =	array_filter($this->request->data['DeviceCertificateData']);

			$this->Session->write('searchdevicecertificatedata', $reqData);

			$FieldValues = $this->Search->SearchModulResults($FieldValues, 'DeviceCertificateData');
			if(!empty($FieldValues)) {$ResultIDs = Hash::extract($FieldValues['DeviceCertificateData'], '{s}.ids');}

			if(count($ResultIDs) == 1) $IDs = $ResultIDs[0];
			if(count($ResultIDs) > 1) $IDs = call_user_func_array('array_intersect', $ResultIDs);

			$device_ids = 	$this->DeviceCertificateData->find('all', array('fields' => array('device_id'), 'conditions' => array('DeviceCertificateData.id' => $IDs,'DeviceCertificateData.deleted' => 0,'DeviceCertificateData.active' => 1)));
			if(!empty($device_ids)) {
				$device_ids = Hash::extract($device_ids, '{n}.{s}.device_id');
			}
			$device_ids =	$this->Device->find('list',array('fields'=>array('Device.id'),'conditions'=>array('Device.id'=>$device_ids,'deleted'=>0)));

			$device_ids = array_unique($device_ids);

			if(isset($ResultIDs)) {
				$FieldValues['Result'] = $this->Search->SearchModulFields($SearchFieldsStandard, array(), $device_ids, 'update');
				$FieldValues['ResultCount'] = count($device_ids);
				$devices = $this->Session->delete('searchdevice.devices');
			}

			if(isset($json) && $json == 1){
				$this->set('response', json_encode($FieldValues));
				$this->layout = 'blank';
				$this->render('json');
				return;
			}

			if(isset($this->request->data['show_result']) && $this->request->data['show_result'] == 1){
				if(isset($device_ids)) {
					$this->Session->write('searchdevice.devices',$device_ids);
				}


				//History erweitern und ggf löschen wenn zu viele Einträge vorhanden
				if(isset($this->request->data['DeviceCertificateData'])&&!empty($this->request->data['DeviceCertificateData'])){

					$request = array('Device' => $this->request->data['DeviceCertificateData']);
					$request ['devices'] = $IDs;
					$HistoryString = '';
					if($this->Session->check('SearchHistoryString') == true) $HistoryString = $this->Session->read('SearchHistoryString');

					$request ['CurrentSearch']= $HistoryString;
					$this->Session->delete('SearchHistoryString');
				 	$HistorySearchUpdate['History']= $this->Session->read('SearchFormDataDevice.History');

					$HistorySearchUpdate['History'][time()] = $request;
					if(count($HistorySearchUpdate['History'])> 5) array_shift($HistorySearchUpdate['History']);
					$this->Session->delete('SearchFormDataDevice.History');
					$this->Session->write('SearchFormDataDevice.History',$HistorySearchUpdate['History']);
				}

			}
		}else{
			if(!empty($this->Session->read('searchdevice.devices'))){
				$device_ids = $this->Session->read('searchdevice.devices');
			}
		}
		$OrderBy = array('Device.intern_no' =>'asc','Device.device_type' => 'asc','Device.name' => 'asc','Device.producer' => 'asc','Device.registration_no' => 'asc','Device.working_place' => 'asc');
		$active = 1;
		if(isset($this->request->data['SortDevice'])) {
			$active = $this->request->data['SortDevice']['active'];
			if(	$this->Session->check('searchdevice.sorting') == true) {
				$CurrentSession = $this->Session->read('searchdevice.sorting');
				if($CurrentSession['active'] <> $active) {
					 $this->request->params['named']['page']=1;
				 }
			}
			$this->Session->write('searchdevice.sorting', $this->request->data['SortDevice']);
		}
		$OrderBy = $this->Data->SortDevicesTable();
		if(isset($device_ids)) {
			$this->paginate = array(
				'order' => $OrderBy,
				'limit' => 20,
				'conditions' => array(
					'Device.id' => $device_ids,
					'Device.deleted' => 0,
					'Device.active' => $active
				)
			);
		}

		$devices = $this->paginate('Device');
		//$this->Session->write('searchdevice.devices', $devices);

		if(isset($devices)&& !empty($devices)){
			foreach($devices as $_key => $_devices){
				$monitorings[$_devices['Device']['id']] = $this->Qualification->MonitoringSummary($_devices, array('top' => 'Device', 'main' => 'DeviceCertificate', 'sub' => 'DeviceCertificateData'));
			}

			if(isset($monitorings)){
				$this->set('monitorings', $monitorings);
			}


		}
		$xml = $this->Xml->DatafromXml('display_device','file',null);

		if(isset($this->request->data['show_result']) && $this->request->data['show_result'] == 1){
			$this->set('SettingsArray', $SettingsArray);
			$this->set('breads', $breads);
			$this->set('SettingsArray', $SettingsArray);
			$this->set('devices',$devices);
			$this->set('xml',$xml['settings']);
			$this->set('DeviceData', $this->Session->read('searchdevicecertificatedata'));
			$this->set('CurrentModel', 'DeviceCertificateData');
			$this->render('searchresult');
		}

		$this->request->data = $FieldValues;
		$this->set('SearchFieldsStandard', $SearchFieldsStandard);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('HistorySearch', $HistorySearch);

	}

	public function search()
	{
		$HistorySearch = $this->Search->MatchHistoryForFormDevice();

		$locale = $this->Lang->Discription();

		$reqData = $this->request->data;

		$SearchFieldsStandard = $this->Xml->XmltoArray('device_search_standard', 'file', null);
		if(isset($this->request->data['json']) && $this->request->data['json'] == 1){
			$json = 1;
		}

		if(isset($json) && $json == 1) {
			$this->Session->delete('searchdevice.sorting');
			$FieldValues = $this->Search->SearchModulFields($SearchFieldsStandard, array(), array(), 'update');
		} else {
			if($this->Session->check('searchdevice.sorting') == true && !isset($this->request->data['SortDevice'])) $this->request->data['SortDevice'] = $this->Session->read('searchdevice.sorting');
			$FieldValues = $this->Search->SearchModulFields($SearchFieldsStandard, array(), array(), 'initial');
		}
		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}
		if(Configure::read('DocumentManager') == true){
			$SettingsArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
		}
		$SettingsArray['addsearching'] = array('discription' => __('Search device', true), 'controller' => 'devices', 'action'=>'search', 'terms' => null, 'rel' => 'devices');
		$SettingsArray['addsearchingmonitoring'] = array('discription' => __('Search monitoring', true), 'controller' => 'devices', 'action'=>'searchmonitoring', 'terms'=>null, 'rel'=>'devices');

		$breads = $this->Navigation->Breads(null);
		$breads[1]['discription'] = __('Devices',true);
		$breads[1]['controller'] = 'devices';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;

		$breads[2]['discription'] = __('Search',true);
		$breads[2]['controller'] = 'devices';
		$breads[2]['action'] = 'search';
		$breads[2]['pass'] = null;

		if(isset($this->request->data['Device'])){
			$this->Session->delete('searchdevicecertificatedata');

			$this->request->data['Device'] =	array_filter($this->request->data['Device']);

			$this->Session->write('searchdevicecertificatedata', $reqData);

			$FieldValues = $this->Search->SearchModulResults($FieldValues, 'Device');
			if(!empty($FieldValues)) {$ResultIDs = Hash::extract($FieldValues['Device'], '{s}.ids');}

			if(count($ResultIDs) == 1) $IDs = $ResultIDs[0];
			if(count($ResultIDs) > 1) $IDs = call_user_func_array('array_intersect', $ResultIDs);

			$device_ids = 	$IDs;//$this->DeviceCertificateData->find('all', array('fields' => array('device_id'), 'conditions' => array('DeviceCertificateData.id' => $IDs,'DeviceCertificateData.deleted' => 0,'DeviceCertificateData.active' => 1)));

			//$device_ids =	$this->Device->find('list',array('fields'=>array('Device.id'),'conditions'=>array('Device.id'=>$device_ids,'active'=>1,'deleted'=>0)));

			$device_ids = array_unique($device_ids);

			if(isset($ResultIDs)) {
				$FieldValues['Result'] = $this->Search->SearchModulFields($SearchFieldsStandard, array(), $device_ids, 'update');
				$FieldValues['ResultCount'] = count($device_ids);
				$devices = $this->Session->delete('searchdevice.devices');
			}

			if(isset($json) && $json == 1){
				$this->set('response', json_encode($FieldValues));
				$this->layout = 'blank';
				$this->render('json');
				return;
			}

			if(isset($this->request->data['show_result']) && $this->request->data['show_result'] == 1){
				if(isset($device_ids)) {
					$this->Session->write('searchdevice.devices',$device_ids);
				}


				//History erweitern und ggf löschen wenn zu viele Einträge vorhanden
				if(isset($this->request->data['Device'])&&!empty($this->request->data['Device'])){

					$request = array('Device' => $this->request->data['Device']);
					$request ['devices'] = $IDs;
					$HistoryString = '';
					if($this->Session->check('SearchHistoryString') == true) $HistoryString = $this->Session->read('SearchHistoryString');

					$request ['CurrentSearch']= $HistoryString;
					$this->Session->delete('SearchHistoryString');
				 	$HistorySearchUpdate['History']= $this->Session->read('SearchFormDataDevice.History');

					$HistorySearchUpdate['History'][time()] = $request;
					if(count($HistorySearchUpdate['History'])> 5) array_shift($HistorySearchUpdate['History']);
					$this->Session->delete('SearchFormDataDevice.History');
					$this->Session->write('SearchFormDataDevice.History',$HistorySearchUpdate['History']);
				}

			}
		}else{
			if(!empty($this->Session->read('searchdevice.devices'))){
				$device_ids = $this->Session->read('searchdevice.devices');
			}
		}
		$active = 1;
		if(isset($this->request->data['SortDevice'])) {

			$active = $this->request->data['SortDevice']['active'];
			if(	$this->Session->check('searchdevice.sorting') == true) {
				$CurrentSession = $this->Session->read('searchdevice.sorting');
				if($CurrentSession['active'] <> $active) {
					 $this->request->params['named']['page']=1;
					 $this->Session->delete('searchdevice.sorting');
				 }

			}
			$this->Session->write('searchdevice.sorting', $this->request->data['SortDevice']);
		}

		$OrderBy = array('Device.intern_no' =>'asc','Device.device_type' => 'asc','Device.name' => 'asc','Device.producer' => 'asc','Device.registration_no' => 'asc','Device.working_place' => 'asc');
		$OrderBy = $this->Data->SortDevicesTable();


		$optionsarray = array(
			'order' => $OrderBy,
			'limit' => 20,
			'conditions' => array(
				'Device.id' => $device_ids,
				'Device.deleted' => 0,
				'Device.active' => $active
			)
		);
		if(isset($device_ids)) {
			$this->paginate = $optionsarray;
		}
		$devices = $this->paginate('Device');
		//$this->Session->write('searchdevice.devices', $devices);

		if(isset($devices)&& !empty($devices)){
			foreach($devices as $_key => $_devices){
				$monitorings[$_devices['Device']['id']] = $this->Qualification->MonitoringSummary($_devices, array('top' => 'Device', 'main' => 'DeviceCertificate', 'sub' => 'DeviceCertificateData'));
			}

			if(isset($monitorings)){
				$this->set('monitorings', $monitorings);
			}


		}
		$xml = $this->Xml->DatafromXml('display_device','file',null);

		if(isset($this->request->data['show_result']) && $this->request->data['show_result'] == 1){
			$this->set('SettingsArray', $SettingsArray);
			$this->set('breads', $breads);
			$this->set('SettingsArray', $SettingsArray);
			$this->set('devices',$devices);
			$this->set('xml',$xml['settings']);
			$this->set('DeviceData', $this->Session->read('searchdevicecertificatedata'));
			$this->set('CurrentModel', 'Device');
			$this->render('searchresult');
		}

		$this->request->data = $FieldValues;
		$this->set('SearchFieldsStandard', $SearchFieldsStandard);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('HistorySearch', $HistorySearch);




	}


	public function summary() {

		$this->layout = 'modal';
		$certificates = array();

		$arrayData = $this->Xml->DatafromXml('DeviceCertificate','file',null);

		//das muss noch geändert werden, die Daten müssen aus der Datenbank kommen
		$certificat_types = $arrayData['settings']->DeviceCertificate->certificat->select->static->value;
		$summary = array();

		$summary = array(
						'futurenow' => array(),
						'future' => array(),
						'errors' => array(),
						'warnings' => array(),
						'hints' => array(),
						'deactive' => array()
					);

		foreach($summary as $_key => $_summary){

			if(count($_summary) == 0) continue;

			foreach($certificat_types as $_certificat_types){
				$summary[$_key][trim($_certificat_types)] = array();
			}
		}

		$certificat_types = array();
		$models = array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData');


		$certificates_options = array(
									'order' => array('certificat'),
									'conditions' => array(
													'DeviceCertificate.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
													'DeviceCertificate.active' => 1,
													'DeviceCertificate.deleted' => 0,
												)
											);


		$certificate = $this->Device->DeviceCertificate->find('all',$certificates_options);
		$this->Device->DeviceCertificate->DeviceCertificateData->recursive = -1;
		$this->Device->recursive = 1;
		foreach($certificate as $_key => $_certificate){

			$certificates_options = array(
									'order' => array('id desc'),
									'conditions' => array(
													'DeviceCertificateData.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
													'DeviceCertificateData.device_certificate_id' => $_certificate['DeviceCertificate']['id'],
													'DeviceCertificateData.active' => 1,
													'DeviceCertificateData.deleted' => 0,
												)
											);

			$testingmethod_options = array(
									'conditions' => array(
													'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
													'Device.id' => $_certificate['Device']['id'],
												)
											);

			$DeviceCertificateData = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificates_options);
			$testingmethod = $this->Device->find('first',$testingmethod_options);

			if(isset($testingmethod['DeviceTestingmethod'][0])){
				$certificate[$_key]['DeviceTestingmethod'] = $testingmethod['DeviceTestingmethod'][0];
			}
			else {
				$certificate[$_key]['DeviceTestingmethod'] = array();
			}

			$certificate[$_key]['DeviceCertificateData'] = $DeviceCertificateData['DeviceCertificateData'];
			$certificate[$_key] = $this->Qualification->DeviceCertification($certificate[$_key],$models);

			$certificat_types[$certificate[$_key]['DeviceCertificate']['certificat']][$certificate[$_key]['Device']['id']][$certificate[$_key]['DeviceCertificate']['id']] = $certificate[$_key];
			$summary = $this->Qualification->DeviceCertificationSummary($summary,$certificate[$_key],$models);

		}

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),

					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		$this->set('summary',$summary);
		$this->set('summary_desc',$summary_desc);
	}

	public function files() {
		$this->layout = 'modal';

		$models['top'] = 'Device';
		$models['main'] = NULL;
		$models['sub'] = NULL;
		$models['file'] = 'Devicefile';
		$certificatefile_id = 0;
		$this->loadModel($models['file']);

			$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
			$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

			if (!$this->Device->exists($device_id)) {
				throw new NotFoundException(__('Invalid device'));
			}


		$path = Configure::read('device_folder') . $device_id. DS . 'documents' . DS;
		$this->__files($path,0,$device_id,$certificatefile_id,$models);

		$SettingsArray = array();
//		$SettingsArray['devicelink'] = array('discription' => __('Back to overview',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
//		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);
	}

	protected function __files($path,$examiner_id,$certificate_id,$certificatefiles_id,$models) {

		$outputarray = $this->Qualification->DokuFiles($path,$examiner_id,$certificate_id,$certificatefiles_id,$models);

		$this->set('_data', $outputarray['_data']);
		$this->set('_files', $outputarray['_files']);
		$this->set('models', $outputarray['models']);
	}

	public function monitorings() {

		if($this->request->projectvars['VarsArray'][15] == 0) return;
		if($this->request->projectvars['VarsArray'][16] == 0) return;

		$device_id = $this->request->projectvars['VarsArray'][16];
		$testingmethod_id = $this->request->projectvars['VarsArray'][15];

		$arrayData = $this->Xml->DatafromXml('Device','file',null);
		$xmlcert = $this->Xml->DatafromXml('DeviceCertificate','file',null);
		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');

		$device = $this->_collectData($device_id);

		if(count($device) > 0){

			foreach($device['DeviceCertificate'] as $key => $value){

				$option['conditions']['DeviceCertificateData.device_certificate_id'] = $value['id'];
				$option['conditions']['DeviceCertificateData.deleted'] = 0;
				$option['order'] = array('DeviceCertificateData.id DESC');
				$DeviceCertificateData = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$option);
				$device['Certificates'][$key] = $this->Qualification->DeviceCertification($DeviceCertificateData,$model);

			}
		}

		// Muss noch im Model korrigiert werden
		// jedes Gerät hat bloß eine Testingmethod?????
//		$device['DeviceTestingmethod'] = $device['DeviceTestingmethod'][0]['DeviceTestingmethod'];

		$device = $this->Data->GeneralDropdownData(array('Device'), $arrayData, $device);

		$this->request->data = $device;

		$SettingsArray = array();

		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}

		$SettingsArray['addsearching'] = array('discription' => __('Search device', true), 'controller' => 'devices', 'action'=>'search', 'terms'=>null, 'rel'=>'devices');
		$SettingsArray['addsearchingmonitoring'] = array('discription' => __('Search monitoring', true), 'controller' => 'devices', 'action'=>'searchmonitoring', 'terms'=>null, 'rel'=>'devices');

		$breads = $this->Navigation->Breads(null);

		$breads[1]['discription'] = __('Devices',true);
		$breads[1]['controller'] = 'devices';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;
		$breads[2]['discription'] = array_shift($device['DeviceTestingmethod']['DeviceTestingmethod']);
		$breads[2]['controller'] = 'devices';
		$breads[2]['action'] = 'overview';
		$breads[2]['pass'] = $this->request->projectvars['VarsArray'];

		$breads[3]['discription'] = $device['Device']['device_type'] . '/' . $device['Device']['name'] . ' (' . $device['Device']['registration_no'] . ')';
		$breads[3]['controller'] = 'devices';
		$breads[3]['action'] = 'view';
		$breads[3]['pass'] = $this->request->projectvars['VarsArray'];

		$breads[4]['discription'] = __('Monitorings',true);
		$breads[4]['controller'] = 'devices';
		$breads[4]['action'] = 'monitorings';
		$breads[4]['pass'] = $this->request->projectvars['VarsArray'];

		$this->set('breads', $breads);

		$this->set('arrayData', $arrayData);
		$this->set('xmlcert', $xmlcert['settings']);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('device', $device);
	}

/*
Kann gelöscht werden

*/
	public function monitoring() {

		$this->layout = 'modal';

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);
		$edit_monitoring = false;

		if(isset($this->request->data['edit_monitoring']) && $this->request->data['edit_monitoring'] == true){
			$edit_monitoring = true;
		}

		$certificate_data_option = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
										'DeviceCertificateData.id' => $certificate_data_id,
										'DeviceCertificateData.deleted' => 0,
										)
									);

		$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);

		$this->request->data = $certificate_data;

		if($edit_monitoring == true) $this->request->data['edit_monitoring'] = true;
		if($edit_monitoring == false) $this->request->data['edit_monitoring'] = false;

		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');

		$certificate_data = $this->Qualification->DeviceCertification($certificate_data,$model);


		$arrayData = $this->Xml->DatafromXml('DeviceCertificateEdit','file',null);
		$this->request->data = $this->Data->GeneralDropdownData(array('Device'), $arrayData, $this->request->data);

		$SettingsArray = array();
//		$SettingsArray['devicelink'] = array('discription' => __('Back to list',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
//		$SettingsArray['backlink'] = array('discription' => __('Back to monitorings of device',true) . ' ' . $certificate_data['Device']['name'], 'controller' => 'devices','action' => 'monitorings', 'terms' => $this->request->projectvars['VarsArray']);
//		$SettingsArray['addlink'] = array('discription' => __('Add monitoring',true), 'controller' => 'devices','action' => 'addmonitoring', 'terms' => $this->request->projectvars['VarsArray']);

		$this->request->projectvars['VarsArray'][17] = $certificate_id;
		$this->request->projectvars['VarsArray'][18] = $certificate_data_id;

		$this->set('SettingsArray', $SettingsArray);
		$this->set('arrayData', $arrayData);
		$this->set('certificate_data', $certificate_data);
		$this->set('settings', $arrayData['headoutput']);
	}

	public function delmonitoring() {

		$this->layout = 'modal';

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$device_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$device_certifificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

		$this->request->projectvars['VarsArray'][17] = $device_certifificate_id;
		$this->request->projectvars['VarsArray'][18] = $device_certifificate_data_id;

		if(isset($this->request->data['DeviceManagerAutocomplete'])) unset($this->request->data['DeviceManagerAutocomplete']);

		// Überprüfung ob Gerät, Benutzer, Firma zusammenpassen

		if (!$this->Device->exists($device_id)) {
			throw new NotFoundException(__('Invalid id'));
		}

		if (!$this->Device->DeviceCertificate->DeviceCertificateData->exists($device_certifificate_data_id)) {
			throw new NotFoundException(__('Invalid id'));
		}

		$certificate_data_option = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
										'DeviceCertificateData.id' => $device_certifificate_data_id,
										'DeviceCertificateData.deleted' => 0,
										)
									);

		$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);


		if (isset($this->request->data['DeviceCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {

			if($device_certifificate_id != $this->request->data['DeviceCertificate']['id']){
				$this->Flash->error(__('The monitoring infos could not be deleted.'), array('key' => 'error'));
				return;
			}

			unset($this->request->data['Order']);
			$this->request->data['DeviceCertificate']['id'] = $device_certifificate_id;
			$this->request->data['DeviceCertificate']['user_id'] = $this->Auth->user('id');
			$this->request->data['DeviceCertificate']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
			$this->request->data['DeviceCertificate']['recertification_in_year'] = $certificate_data['DeviceCertificate']['recertification_in_year'];
			$this->request->data['DeviceCertificate']['horizon'] = $certificate_data['DeviceCertificate']['horizon'];
			$this->request->data['DeviceCertificate']['deleted'] = 1;

			if($this->Device->DeviceCertificate->save($this->request->data)) {

				$this->Device->DeviceCertificate->DeviceCertificateData->updateAll(
    				array(
						'DeviceCertificateData.deleted' => 1,
						),
    				array(
						'DeviceCertificateData.device_certificate_id' => $device_certifificate_id,
						)
				);

				$this->Flash->success(__('The monitoring has been deleted'), array('key' => 'success'));

				$this->Autorisierung->Logger($device_certifificate_data_id, $this->request->data['DeviceCertificate']);

				unset($this->request->params['pass']['17']);
				unset($this->request->params['pass']['18']);

				unset($this->request->projectvars['VarsArray']['17']);
				unset($this->request->projectvars['VarsArray']['18']);

				$FormName['controller'] = 'devices';
				$FormName['action'] = 'monitorings';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('saveOK',1);
				$this->set('FormName',$FormName);
				return;

			} else {
				$this->Flash->error(__('The monitoring infos could not be deleted. Please, try again.'), array('key' => 'error'));
			}
		} else {

			$certificate_data_option = array(
										'order' => array('DeviceCertificateData.id DESC'),
										'conditions' => array(
											'DeviceCertificateData.id' => $device_certifificate_data_id,
											'DeviceCertificateData.deleted' => 0,
											)
										);

			$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);

			$this->request->data = $certificate_data;

			$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceCertificateEdit');

			$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');
			$this->Qualification->SingleCertificateSummary($certificate_data,$model);

			$this->set('certificate_data', $certificate_data);
			$this->set('settings', $arrayData['settings']);

			$this->Flash->hint(__('Do you want to delete the following monitoring?') .' ' . 		$this->request->data['DeviceCertificate']['certificat'] , array('key' => 'hint'));

		}
	}

	public function addmonitoring() {

		$this->layout = 'modal';

		$device_id = 0;

		if($this->request->projectvars['VarsArray'][16] > 0){
			$device_id = $this->request->projectvars['VarsArray'][16];
		}

		$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceCertificate');

		$options = array(
						'conditions' => array(
											'Device.deleted' => 0,
											'Device.id' => $device_id,
											'Device.testingcomp_id' => $this->Auth->user('testingcomp_id')
										)
						);


		if (isset($this->request->data['DeviceCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->request->data['DeviceCertificate']['device_id'] = $device_id;
			$this->request->data['DeviceCertificate']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
			$this->request->data['DeviceCertificate']['user_id'] = $this->Auth->user('id');

			$this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('DeviceCertificate'));

			$DeviceCertificateExist = $this->Device->DeviceCertificate->find('first',array(
														'conditions'=>array(
															'DeviceCertificate.deleted' => 0,
															'DeviceCertificate.device_id' => $device_id,
															'DeviceCertificate.certificat' => $this->request->data['DeviceCertificate']['certificat'],

															)
														)
													);
			if(count($DeviceCertificateExist) > 0){
				$this->Flash->error(__('The monitoring infos could not be saved. The following monitoring exists for this device').': '.$this->request->data['DeviceCertificate']['certificat'], array('key' => 'error'));
			}

			if(count($DeviceCertificateExist) == 0){

				$this->Device->DeviceCertificate->create();

				if ($this->Device->DeviceCertificate->save($this->request->data['DeviceCertificate'])) {

					foreach($this->request->data['DeviceCertificate'] as $_key => $_DeviceCertificate){
						$this->request->data['DeviceCertificateData'][$_key] = $_DeviceCertificate;
					}

					$this->request->data['DeviceCertificateData']['device_certificate_id'] = $this->Device->DeviceCertificate->getLastInsertId();
					$this->request->data['DeviceCertificateData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
					$this->request->data['DeviceCertificateData']['user_id'] = $this->Auth->user('id');
					$this->request->data['DeviceCertificateData']['device_id'] = $device_id;
					$this->request->data['DeviceCertificateData']['first_certification'] = 0;
					$this->request->data['DeviceCertificateData']['certified'] = 1;
					$this->request->data['DeviceCertificateData']['certified_date'] = $this->request->data['DeviceCertificate']['first_registration'];
					$this->request->data['DeviceCertificateData']['renewal_in_year'] = 0;
					$this->request->data['DeviceCertificateData']['apply_for_recertification'] = 0;
					$this->request->data['DeviceCertificateData']['deleted'] = 0;
					$this->request->data['DeviceCertificateData']['active'] = 1;

					$this->Device->DeviceCertificate->DeviceCertificateData->create();

					if ($this->Device->DeviceCertificate->DeviceCertificateData->save($this->request->data['DeviceCertificateData'])) {

						$this->Flash->success(__('The monitoring infos has been saved.'), array('key' => 'success'));

						$this->Session->delete('DeviceCertificate.form');
						$this->request->params['pass']['17'] = $this->Device->DeviceCertificate->getLastInsertId();
						$this->request->params['pass']['18'] = $this->Device->DeviceCertificate->DeviceCertificateData->getLastInsertId();

						$this->request->params['action'] = 'monitoring';
						$request_url = explode('/',$this->request->url);
						$request_here = explode('/',$this->request->here);
						$request_url[1] = 'monitoring';
						$request_here[3] = 'monitoring';
						$this->request->url = implode('/',$request_url);
						$this->request->here = implode('/',$request_here);

						$FormName['controller'] = 'devices';
						$FormName['action'] = 'monitorings';
						$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

						$this->set('saveOK',1);
						$this->set('FormName',$FormName);

					} else {
						$this->Device->DeviceCertificate->delete($this->Device->DeviceCertificate->getLastInsertId());
						$this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
					}

				} else {
					$this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
				}
			}
		}

		$device = $this->Device->find('first',$options);

		$SettingsArray = array();

		if(!empty($arrayData['settings']->Device)){
			foreach($arrayData['settings']->Device->children() as $_key => $_settings){
				$this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
			}
		}
                $this->Data->SessionFormload('DeviceCertificate');
		$this->request->data = $this->Data->GeneralDropdownData(array('DeviceCertificate'), $arrayData, $this->request->data);
		$this->request->data['DeviceCertificate']['file'] = 0;
		$this->request->data['DeviceCertificate']['extern_intern'] = 0;
		$this->request->data['DeviceCertificate']['active'] = 1;
		$this->request->data['DeviceCertificate']['status'] = 0;

		$this->set('arrayData', $arrayData);
		$this->set('settings', $arrayData['settings']);
		$this->set('device', $device);
		$this->set('SettingsArray', $SettingsArray);
	}

	public function editmonitoring() {

		$this->layout = 'modal';
		$this->loadModel('DeviceCertificate');
		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$device_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$device_certifificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

		$this->request->projectvars['VarsArray'][17] = $device_certifificate_id;
		$this->request->projectvars['VarsArray'][18] = $device_certifificate_data_id;

		if (!$this->Device->exists($device_id)) {
			return;
		}

		if (!$this->Device->DeviceCertificate->DeviceCertificateData->exists($device_certifificate_data_id)) {
			return;
		}

		$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceCertificateEdit');

		if (isset($this->request->data['DeviceCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->request->data['DeviceCertificate']['id'] = $device_certifificate_id;
			$this->request->data['DeviceCertificate']['user_id'] = $this->Auth->user('id');

			$this->request->data['DeviceCertificateData']['id'] = $device_certifificate_data_id;
			$this->request->data['DeviceCertificateData']['user_id'] = $this->Auth->user('id');

			unset($this->request->data['Order']);
			$device_certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->save($this->request->data['DeviceCertificateData']);

				if ($device_certificate_data) {
					$this->request->data['DeviceCertificateData']['recertification_in_year'] = $device_certificate_data['DeviceCertificateData']['recertification_in_year'];
					$certificate = $this->request->data['DeviceCertificateData'];
					$certificate['id'] = 	$device_certifificate_id;
					if($this->Device->DeviceCertificate->save($certificate)) {
						$this->Flash->success(__('The monitoring infos has been saved'), array('key' => 'success'));
						$this->Autorisierung->Logger($device_certifificate_data_id, $this->request->data['DeviceCertificateData']);
					}
				} else {
					$this->Flash->error(__('The monitoring infos could not be saved'), array('key' => 'error'));
				}



			$FormName['controller'] = 'devices';
			$FormName['action'] = 'monitorings';
			$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

			$this->set('FormName',$FormName);

		}

		$certificate_data_option = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
										'DeviceCertificateData.id' => $device_certifificate_data_id,
										'DeviceCertificateData.deleted' => 0,
										)
									);

		$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);

    $this->request->data = $this->Data->DropdownData(array('DeviceCertificateData'), $arrayData, $this->request->data);
		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');
		$Device = $this->Qualification->SingleCertificateSummary($certificate_data,$model);

		$this->request->data = $Device;

		$SettingsArray = array();

		$this->set('arrayData', $arrayData);
		$this->set('settings', $arrayData['settings']);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('certificate_data', $certificate_data);

	}

	public function replacemonitoring() {

		$this->layout = 'modal';

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass'][16]);
		$device_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass'][17]);
		$device_certifificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass'][18]);

		$this->request->projectvars['VarsArray'][17] = $device_certifificate_id;
		$this->request->projectvars['VarsArray'][18] = $device_certifificate_data_id;

		if (!$this->Device->exists($device_id)) {
			throw new NotFoundException(__('Invalid id 1'));
		}

		if (!$this->Device->DeviceCertificate->exists($device_certifificate_id)) {
			throw new NotFoundException(__('Invalid id 2'));
		}

		if (!$this->Device->DeviceCertificate->DeviceCertificateData->exists($device_certifificate_data_id)) {
			throw new NotFoundException(__('Invalid id 3'));
		}

//		$arrayData = $this->Xml->DatafromXml('DeviceCertificateRefresh','file',null);
		$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceCertificateRefresh');

		$certificate_data_option = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
										'DeviceCertificateData.id' => $device_certifificate_data_id,
										'DeviceCertificateData.deleted' => 0,
										)
									);

		$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);

		if (isset($this->request->data['DeviceCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {

			// Die Daten aus dem Formular kommen in unterschiedlichen Arrays und werden vereiningt
			$replace_data = $this->request->data['DeviceCertificateData'];

			// Der alte Datensatz aus der Datenbank wird mit den neuen Werten aus dem Formular aktualisierst
			foreach($certificate_data['DeviceCertificateData'] as $_key => $_certificate_data){
				if(isset($replace_data[$_key])){
					$certificate_data['DeviceCertificateData'][$_key] = $replace_data[$_key];
				}
			}

			// Die ID wird entfernt, und noch ein paar Werte aktualisiert
			unset($certificate_data['DeviceCertificateData']['id']);
			$certificate_data['DeviceCertificateData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
			$certificate_data['DeviceCertificateData']['user_id'] = $this->Auth->user('id');

			// Die veralteten Datensätze werden auf inaktiv gestellt
			$this->Device->DeviceCertificate->DeviceCertificateData->updateAll(
    				array(
						'DeviceCertificateData.active' => 0,
						),
    				array(
						'DeviceCertificateData.device_certificate_id' => $certificate_data['DeviceCertificate']['id'],
						'DeviceCertificateData.deleted' => 0,
						)
				);

			// neuer Datensatz und speichern
			$this->Device->DeviceCertificate->DeviceCertificateData->create();

			if ($this->Device->DeviceCertificate->DeviceCertificateData->save($certificate_data)) {

				$certificate_data_option = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
										'DeviceCertificateData.id' => $this->Device->DeviceCertificate->DeviceCertificateData->getLastInsertId(),
										'DeviceCertificateData.deleted' => 0,
										)
									);

				$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);

				$this->Flash->success(__('The monitoring infos has been saved.'), array('key' => 'success'));

				$this->request->params['pass']['18'] = $this->Device->DeviceCertificate->DeviceCertificateData->getLastInsertId();

				$this->request->params['controller'] = 'monitoring';

				$FormName['controller'] = 'devices';
				$FormName['action'] = 'monitorings';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('settings',array());
				$this->set('certificate_data_old_infos',$certificate_data);
				$this->set('FormName',$FormName);

				return;
				} else {
					$this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
				}

		}

		$this->request->data = $certificate_data;

		$this->request->data['DeviceCertificateData']['certified_date'] = null;
		$this->request->data['DeviceCertificateData']['status'] = 0;

		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');
		$this->Qualification->SingleCertificateSummary($certificate_data,$model);

		$SettingsArray = array();

		$this->set('arrayData', $arrayData);
		$this->set('settings', $arrayData['settings']);
		$this->set('SettingsArray', $SettingsArray);
	}

	public function add() {
		/*echo '<pre>';
		echo 'test';
		echo '</pre>';*/
		$this->layout = 'modal';
    $locale = $this->Lang->Discription();

		$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceEdit');

		if (isset($this->request->data['Device']) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->Device->set($this->request->data);
			$errors = array();

			if($this->Device->validates()) {
			}
			else {
				$errors = $this->Device->validationErrors;
			}

			$this->Device->create();

			$this->request->data['Device']['user_id'] = $this->Auth->user('id');
			$this->request->data['Device']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

			if(empty($this->request->data['DeviceTestingmethod']['DeviceTestingmethod'])){
				$this->Flash->error(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'error'));
			}

			$this->request->data = $this->Data->ChangeDropdownData($this->request->data,$arrayData,'Device');

			if(isset($this->request->data['DeviceManagerAutocomplete'])) unset($this->request->data['DeviceManagerAutocomplete']);

			$savedevice = array(
				'Device' => $this->request->data['Device'],
				'DeviceTestingmethod' => array(
					'DeviceTestingmethod' => $this->request->data['DeviceTestingmethod']['DeviceTestingmethod']
				)
			);

			if ($this->Device->save($savedevice) && !empty($this->request->data['DeviceTestingmethod']['DeviceTestingmethod'])) {

				$this->request->data['Device']['user_id'] = $this->Auth->user('id');
        $this->request->data['Device']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

				$this->Flash->success(__('The device has been saved'), array('key' => 'success'));

				$this->Session->delete('Device.form');
				$this->Autorisierung->Logger($this->Device->getLastInsertID(), $this->request->data['Device']);
				$last_device_id = $this->Device->getLastInsertID();

				$device = $this->Device->find('first',array('conditions' => array('Device.id' => $last_device_id)));

				if(isset($device['DeviceTestingmethod'][0]['id'])) $this->request->projectvars['VarsArray'][15] = $device['DeviceTestingmethod'][0]['id'];
				else $this->request->projectvars['VarsArray'][15] = 0;

				$this->request->projectvars['VarsArray'][16] = $device['Device']['id'];

				$FormName['controller'] = 'devices';
				$FormName['action'] = 'view';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('FormName',$FormName);
				$this->set('saveOK',1);

			} else {
				$this->Flash->error(__('Das Gerät konnte nicht gespeichert werden.'), array('key' => 'error'));
			}
		}

		$this->Device->recursive = -1;
		$this->Device->Examiner->recursive = -1;
		$schema = $this->Device->schema();

		$device = array('Device' => array());

		foreach ($schema as $key => $value) {
			$device['Device'][$key] = null;
		}

		$Examiner = $this->Device->Examiner->find('all',
			array(
				'fields' => array(
					'name',
					'first_name',
					'id'),
				'conditions' => array(
					'Examiner.testingcomp_id'=>$this->Autorisierung->ConditionsTestinccomps(),
					'Examiner.deleted' => 0,
				'Examiner.active' => 1),
				'order' =>'name ASC'
			)
		);

		$device['Examiners'] = Hash::combine($Examiner,'{n}.Examiner.id',array('%s %s', '{n}.Examiner.name', '{n}.Examiner.first_name'));
		unset($Examiner);

    $testingmethod = $this->Device->DeviceTestingmethod->find('list',array(
			'fields' => array('id','verfahren'),
			'order' => array('verfahren')
			)
		);

		$device['DeviceTestingmethod']['DeviceTestingmethod'] = $testingmethod;
		unset($testingmethod);

		$this->loadModel('Testingcomp');

		$this->Testingcomp->recursive = -1;
		$testingcomps = $this->Testingcomp->find('list',array(
			'fields' => array('id','firmenname'),
			'conditions' => array(
				'Testingcomp.id' => $this->Auth->user('testingcomp_id')
				)
			)
		);

		$device['Testingcomp']['Testingcomps'] = $testingcomps;

		$device = $this->Data->DropdownData(array('Device'), $arrayData, $device);
		$device['Device']['status'] = 0;

		$this->request->data = $device;

		$this->request->data['Device']['testingcomp_id'] =  $this->Auth->user('testingcomp_id');
		$this->request->data = $this->Data->DropdownData(array('Device'), $arrayData, $this->request->data);
		$this->request->data = $this->Drops->CollectMasterDropdown($this->request->data);
		$this->request->data = $this->Data->SortAllDropdownArraysNatural($this->request->data);

		$this->request->data['Device']['active'] = 1;

		$SettingsArray = array();

  	$SettingsArray['addlink'] = array('discription' => __('DeviceTestingmethods',true), 'controller' => 'devicetestingmethods','action' => 'index', 'terms' => null,); // 03.08.2017

		$this->Data->SessionFormload();

		$this->set('SettingsArray', $SettingsArray);
		$this->set('settings', $arrayData['settings']);
	}

	public function duplicate() {

		if($this->request->projectvars['VarsArray'][16] > 0){
			$device_id = $this->request->projectvars['VarsArray'][16];
		}
		else {
			throw new NotFoundException(__('Invalid value 2'));
		}

		if(isset($this->request->data['Device']) && ($this->request->is('post') || $this->request->is('put'))) {

			$row = $this->Device->findById($device_id);
			$_testingmethods = array();

			if(isset($row['DeviceTestingmethod']) && is_array($row['DeviceTestingmethod'])){
				foreach($row['DeviceTestingmethod'] as $_testingmethod){
					$_testingmethods['DeviceTestingmethod'][] = $_testingmethod['id'];
				}
			}

			unset($row['Device']['id']);
			unset($row['DeviceTestingmethod']);


			$row['Device']['name'] = __('Copy of',true) . ' ' . $row['Device']['name'];
			$row['Device']['user_id'] = $this->Auth->user('id');
			$row['DeviceTestingmethod'] = $_testingmethods;
			$this->Device->create();

			if ($this->Device->save($row)) {
				$this->Session->setFlash(__('The device has been saved'));
				$this->Autorisierung->Logger($this->Device->getLastInsertID(), $this->request->data['Device']);
				$device = $this->Device->find('first',array('conditions' => array('Device.id' => $this->Device->getLastInsertID())));

				$this->request->projectvars['VarsArray'][15] = $device['DeviceTestingmethod'][0]['id'];
				$this->request->projectvars['VarsArray'][16] = $device['Device']['id'];

				$this->__view();
			} else {
				$this->Session->setFlash(__('The device could not be saved. Please, try again.'));
			}
		}

		$options = array(
						'conditions' => array(
											'Device.id' => $device_id,
											'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
										)
						);

		$device = $this->Device->find('first',$options);

		$this->request->data = $device;

		$lastModalURLArray['controller'] = 'devices';
		$lastModalURLArray['action'] = 'edit';
		$lastModalURLArray['pass'] = $this->request->projectvars['VarsArray'];
		$lastModal = $this->Session->read('lastModalURLArray');

		if(is_array($lastModal) && count($lastModal) == 3){
			$lastModalURLArray['controller'] = $this->Session->read('lastModalURLArray.controller');
			$lastModalURLArray['action'] = $this->Session->read('lastModalURLArray.action');
			$lastModalURLArray['pass'] = $this->Session->read('lastModalURLArray.pass');
		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => $lastModalURLArray['controller'],'action' => $lastModalURLArray['action'], 'terms' => $lastModalURLArray['pass']);

		$this->set('SettingsArray', $SettingsArray);
		$this->set('device', $device);
	}

	public function view() {

		$device_id = $this->request->projectvars['VarsArray'][16];

		if($device_id == 0 && isset($this->request->data['device_id']) && $this->request->data['device_id'] > 0){
			$this->request->projectvars['VarsArray'][16] = $this->request->data['device_id'];
			$device_id =  $this->request->data['device_id'];
		}

		$this->Session->delete('Device.form');

		$device = $this->_collectData($device_id);

		if($this->request->projectvars['VarsArray'][15] == 0) $this->request->projectvars['VarsArray'][15] = key($device['DeviceTestingmethod']['DeviceTestingmethod']);

		$arrayData = $this->Xml->DatafromXml('DeviceEdit','file',null);

		if(count($device['DeviceTestingmethod']['DeviceTestingmethod']) == 0){
			$this->Flash->error(__('Das Gerät ist keiner Kategorie zugeordnet.'), array('key' => 'error'));
			return;
		}

		$SettingsArray = array();

		if(Configure::read('CertifcateManager') == true){
			$SettingsArray['examinerlink'] = array('discription' => __('Examiners administration',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
		}

		if(Configure::read('DocumentManager') == true){
			$SettingsArray['documentlink'] = array('discription' => __('Document administration',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
		}

		$SettingsArray['addsearching'] = array('discription' => __('Search device', true), 'controller' => 'devices', 'action'=>'search', 'terms'=>null, 'rel'=>'devices');
		$SettingsArray['addsearchingmonitoring'] = array('discription' => __('Search monitoring', true), 'controller' => 'devices', 'action'=>'searchmonitoring', 'terms'=>null, 'rel'=>'devices');

		$this->request->projectvars['VarsArray'][16] = $device_id;

		$breads = $this->Navigation->Breads(null);

		$breads[1]['discription'] = __('Devices',true);
		$breads[1]['controller'] = 'devices';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;

		$breads[2]['discription'] = $device['DeviceTestingmethod']['DeviceTestingmethod'][key($device['DeviceTestingmethod']['DeviceTestingmethod'])];
		$breads[2]['controller'] = 'devices';
		$breads[2]['action'] = 'overview';
		$breads[2]['pass'] = $this->request->projectvars['VarsArray'];

		if(isset($device['Device'])){
			$breads[3]['discription'] = $device['Device']['device_type'] . '/' . $device['Device']['name'] . ' (' . $device['Device']['intern_no'] . ')';
			$breads[3]['controller'] = 'devices';
			$breads[3]['action'] = 'view';
			$breads[3]['pass'] = $this->request->projectvars['VarsArray'];
		}

		$this->set('breads', $breads);
		$this->set('SettingsArray', $SettingsArray);
//		$this->set('device', $device);
//		$this->set('testingmethod', $testingmethod);
		$this->set('arrayData', $arrayData);

	}

	public function edit() {

		$this->layout = 'modal';
		$locale = $this->Lang->Discription();

		if($this->request->projectvars['VarsArray'][16] > 0) $device_id = $this->request->projectvars['VarsArray'][16];
		else $device_id = 0;

		if (isset($this->request->data['Device']) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->request->data['Device']['user_id'] = $this->Auth->user('id');
			$this->request->data['Device']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

			if(empty($this->request->data['DeviceTestingmethod']['DeviceTestingmethod'])){
				$this->Flash->error(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'error'));
			}

			$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceEdit');
			$this->request->data = $this->Data->ChangeDropdownData($this->request->data,$arrayData,'Device');
			if(isset($this->request->data['DeviceManagerAutocomplete'])) unset($this->request->data['DeviceManagerAutocomplete']);

			if ($this->Device->save($this->request->data) && !empty($this->request->data['DeviceTestingmethod']['DeviceTestingmethod'])) {

				$this->Flash->success(__('The device has been saved'), array('key' => 'success'));
				$this->request->data['Device']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
				$this->Autorisierung->Logger($device_id, $this->request->data['Device']);
				$this->Session->delete('Device.form');

				$this->request->projectvars['VarsArray'][15] = $this->request->data['DeviceTestingmethod']['DeviceTestingmethod'];
				$this->request->projectvars['VarsArray'][16] = $device_id;

				$FormName['controller'] = 'devices';
				$FormName['action'] = 'view';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('FormName',$FormName);
				$this->set('saveOK',1);

			} else {
				$this->Flash->error(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'error'));
			}
		}

		$this->_collectData($device_id);

		$SettingsArray = array();
		$SettingsArray['dellink'] = array('discription' => __('Delete this device',true), 'controller' => 'devices','action' => 'del', 'terms' => $this->request->projectvars['VarsArray']);

    //$this->Data->SessionFormload();

		$this->set('SettingsArray', $SettingsArray);

	}

	public function del() {

		$this->layout = 'modal';

		if($this->request->projectvars['VarsArray'][16] > 0) $device_id = $this->request->projectvars['VarsArray'][16];
		else $device_id = 0;

		$testingmethod_id = $this->request->projectvars['VarsArray'][15];

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'devices','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);

		if(isset($this->request->data['Device']) && ($this->request->is('post') || $this->request->is('put'))) {

			$device = $this->_collectData($device_id);

			unset($device['DropdownInfo']);
			unset($device['CustomDropdownFields']);
			unset($device['JSON']);
			unset($device['Dropdowns']);
			unset($device['Examiners']);

			$device['Device']['deleted'] = 1;
			$device['Device']['active'] = 0;
			$device['Device']['user_id'] = $this->Auth->user('id');

			$Insert['Device'] = array(
				'id' => $device['Device']['id'],
				'deleted' => $device['Device']['deleted'],
				'active' => $device['Device']['active'],
				'user_id' => $device['Device']['user_id'],
			);

			// vermeidet den Validationerror
			$Insert['DeviceTestingmethod']['DeviceTestingmethod'] = key($device['DeviceTestingmethod']['DeviceTestingmethod']);

			if ($this->Device->save($device)) {

				$this->Flash->success(__('The device has been deleted'), array('key' => 'success'));

				$this->Autorisierung->Logger($this->Device->getLastInsertID(), $device['Device']);

				$FormName['controller'] = 'devices';
				$FormName['action'] = 'overview';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

				$this->set('FormName',$FormName);
				$this->set('saveOK',1);
				return;

			} else {
//				pr($this->Device->validationErrors);
				$this->Flash->error(__('The device could not be deleted. Please, try again.'), array('key' => 'error'));
			}
		} else {

			$device = $this->_collectData($device_id);

			$Message  = __('Do you want to delete this device?',true);
			$Message .= ' ' . $device['Device']['device_type'] . ' ' . $device['Device']['name'] . ' ' . $device['Device']['producer'] . ' ' . $device['Device']['registration_no'];

			$this->Flash->hint($Message, array('key' => 'hint'));

		}
	}

	protected function _collectData($device_id){

		$this->Device->Dropdown->recursive = -1;
		$arrayData['settings'] = $this->Xml->CollectXMLFromFile('DeviceEdit');

		$options = array(
			'conditions' => array(
				'Device.id' => $device_id,
				'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
			)
		);

		$device = $this->Device->find('first',$options);

		if(isset($device['DeviceCertificate'])){
			foreach ($device['DeviceCertificate'] as $key => $value) {
				if($value['deleted'] == 1) unset($device['DeviceCertificate'][$key]);
			}
		}

		$this->Device->Examiner->recursive = -1;
		$Examiner = $this->Device->Examiner->find('all',
			array(
				'fields' => array(
					'name',
					'first_name',
					'id'),
				'conditions' => array(
					'Examiner.testingcomp_id'=>$this->Autorisierung->ConditionsTestinccomps(),
					'Examiner.deleted' => 0,
					'Examiner.active' => 1),
				'order' =>'name ASC'
			)
		);

		$device['Examiners'] = Hash::combine($Examiner,'{n}.Examiner.id',array('%s %s', '{n}.Examiner.name', '{n}.Examiner.first_name'));

		$device['Examiners'][0] = '';

		asort($device['Examiners']);

		$idtestmethod = array();
    	$idtestmethod = $this->Data->BelongsToManySelected($device,'Device','DeviceTestingmethod',array('TestingmethodsDevices','testingmethod_id','device_id'));
    	$idtestmethod = $idtestmethod['DeviceTestingmethod']['selected'];
    	$testingmethod = $this->Device->DeviceTestingmethod->find('list',array('fields' => array('id','verfahren'),'conditions' =>array('DeviceTestingmethod.id' =>$idtestmethod)));

		$device['DeviceTestingmethod']['DeviceTestingmethod'] = $testingmethod;

		$this->loadModel('Testingcomp');

		$this->Testingcomp->recursive = -1;
		$testingcomps = $this->Testingcomp->find('list',array(
			'fields' => array('id','firmenname'),
			'conditions' => array(
				'Testingcomp.id' => $this->Auth->user('testingcomp_id')
				)
			)
		);

		$device['Testingcomp']['Testingcomps'] = $testingcomps;

		$device = $this->Data->DropdownData(array('Device'), $arrayData, $device);
		$device['Device']['status'] = 0;

		$this->request->data = $device;

		$this->set('settings', $arrayData['settings']);

		return $device;

	}

	public function devicefilesdescription() {

		$this->layout = 'json';

		$StopRequets = false;

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

		if($testingmethod_id == 0) $StopRequets = true;
		if($device_id == 0) $StopRequets = true;
		if($file_id == 0) $StopRequets = true;

		if(!isset($this->request->data['json_value_old'])) $StopRequets = true;
		if(!isset($this->request->data['json_value'])) $StopRequets = true;
		if(empty($this->request->data['json_value_old'])) $StopRequets = true;
		if(empty($this->request->data['json_value'])) $StopRequets = true;

		if($StopRequets == true){

			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $file_id;
			$data['StatusText'] = __('This entry does not exist',true);

			$this->set('request',json_encode($data));
			return;
		}

		$insert['Devicefile']['id'] = $file_id;
		$insert['Devicefile']['description'] = $this->request->data['json_value'];

		$this->loadModel('Devicefile');


		if($this->Devicefile->save($insert)) {
			$this->Autorisierung->Logger($insert['Devicefile']['id'], $insert['Devicefile']);
			$data['StatusText'] = $insert['Devicefile']['description'];
			$data['RemoveTableRow'] = 'tr_' . $file_id;
			$data['StatusClass'] = 'success';

		} else {
			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $file_id;
			$data['StatusText'] = __('This entry could not be saved',true);
		}

		$this->set('request',json_encode($data));

	}

	/*
	/* wird nicht mehr gebraucht und gelöscht
	*/
	protected function __filesdescription($models,$successtarget) {

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$file_id = 0;

		if(isset($this->request->params['pass']['17']) && $this->Sicherheit->Numeric($this->request->params['pass']['17']) > 0){
			$file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		}

		if (!$this->{$models['top']}->exists($device_id)) {
			throw new NotFoundException(__('Invalid device'));
		}

		$this->loadModel($models['file']);

		if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
			if ($this->$models['file']->save($this->request->data)) {
				$this->Session->setFlash(__('The value has been saved'));
				$this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


				if($models['file'] == 'Devicesfile'){
					$_here = explode('/',$this->request->here);
					$_here[3] = 'files';
					$this->request->here = implode('/',$_here);
					$this->files();
				}
				$this->render($successtarget);
			} else {
				$this->Session->setFlash(__('The value could not be saved. Please, try again.'));
			}
		}

		if($models['top'] != NULL && $models['main'] != NULL && $models['sub'] != NULL){

			$certificate_data_option = array(
									'order' => array($models['sub'].'.id DESC'),
									'conditions' => array(
										$models['sub'].'.certificate_id' => $certificate_id,
										$models['sub'].'.deleted' => 0,
										)
									);

			$certificate_data = $this->{$models['top']}->{$models['main']}->{$models['sub']}->find('first',$certificate_data_option);
		}

		if($models['top'] != NULL && $models['main'] == NULL && $models['sub'] == NULL){

			$certificate_data_option = array(
									'order' => array($models['top'].'.id DESC'),
									'conditions' => array(
										$models['top'].'.id' => $device_id,
										$models['top'].'.deleted' => 0,
										)
									);
			$certificate_data = $this->{$models['top']}->find('first',$certificate_data_option);
		}

		if($file_id == 0){
			$certificate_data_option = array(
									'order' => array($models['file'].'.id DESC'),
									'conditions' => array(
										$models['file'].'.parent_id' => $device_id,
										$models['file'].'.user_id' => $this->Auth->user('id'),
										$models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
										)
									);
		}
		elseif($file_id > 0){
			$certificate_data_option = array(
									'conditions' => array(
										$models['file'].'.id' => $file_id,
										$models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
										)
									);
		}

		$certificate_files = $this->$models['file']->find('first',$certificate_data_option);

		$this->request->data = $certificate_files;


		$this->set('certificate_files', $certificate_files);
		$this->set('certificate_data', $certificate_data);
		$this->set('models', $models);
	}

	public function getfiles() {
		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

		$models = array('main' => 'Devicefile','sub' => null);
		$path = Configure::read('device_folder') . $device_id. DS . 'documents' . DS;

		$this->loadModel($models['main']);
		$this->__getfiles($path,$models);
	}

	protected function __getfiles($path,$models) {

		$this->layout = 'blank';
		$examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

		$certificate_data_option = array(
									'conditions' => array(
										$models['main'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
										$models['main'].'.id' => $file_id
										)
									);

		$certificate_data = $this->{$models['main']}->find('first',$certificate_data_option);

		if(count($certificate_data) == 0){
			$this->Session->setFlash(__('The file does not exist.'));
			$this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')),$this->request->projectvars['VarsArray']));
		}
		elseif(count($certificate_data) == 1){
			if(file_exists($path . $certificate_data[$models['main']]['name'])){
				$this->set('path',$path . $certificate_data[$models['main']]['name']);
			}
			else {
				$this->Session->setFlash(__('The file cannot be found.'));
				$this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')),$this->request->projectvars['VarsArray']));
			}
		}
	}


/*
/* wird nicht mehr gebraucht und gelöscht
*/
	protected function __delfiles($models,$successtarget) {

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

		$this->loadModel($models['file']);

		if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->request->data[$models['file']]['deleted'] = 1;

			if ($this->$models['file']->save($this->request->data)) {

				$this->Session->setFlash(__('The file has been deleted'));
				$this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


				$_here = explode('/',$this->request->here);
				$_here[3] = 'files';
				$this->request->here = implode('/',$_here);
				$this->files();
				$this->render($successtarget);
			} else {
				$this->Session->setFlash(__('The file could not be deleted. Please, try again.'));
			}
		}

		$certificate_data_option = array(
									'order' => array($models['top'].'.id DESC'),
									'conditions' => array(
										$models['top'].'.id' => $device_id,
										$models['top'].'.deleted' => 0,
										)
									);

		$certificate_data = $this->{$models['top']}->find('first',$certificate_data_option);

		$certificate_data_option = array(
								'conditions' => array(
									$models['file'].'.id' => $file_id,
									$models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
									)
								);

		$certificate_files = $this->$models['file']->find('first',$certificate_data_option);

		$this->request->data = $certificate_files;

		$this->set('models', $models);
		$this->set('certificate_data', $certificate_data);
		$this->set('certificate_files', $certificate_files);
	}

	public function deldevicefiles() {

		$this->layout = 'json';

		$StopRequets = false;
		$data = array();

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

		if($testingmethod_id == 0) $StopRequets = true;
		if($device_id == 0) $StopRequets = true;
		if($file_id == 0) $StopRequets = true;

		if($StopRequets == true){

			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $file_id;
			$data['StatusText'] = __('This entry does not exist',true);

			$this->set('request',json_encode($data));
			return;
		}

		$insert['Devicefile']['id'] = $file_id;
		$insert['Devicefile']['deleted'] = 1;

		$this->loadModel('Devicefile');


		if($this->Devicefile->save($insert)) {

			$Devicefile = $this->Devicefile->find('first',array('conditions' => array('Devicefile.id' => $file_id)));
			$path = Configure::read('device_folder') . $device_id. DS . 'documents' . DS . $Devicefile['Devicefile']['name'];
			$file = new File($path);
			$DeleteStatus = $file->delete();

			$this->Autorisierung->Logger($insert['Devicefile']['id'], $insert['Devicefile']);

			if($DeleteStatus === true){
				$data['StatusText'] = __('The file has been deleted',true);
				$data['RemoveTableRow'] = 'tr_' . $file_id;
				$data['StatusClass'] = 'success';
			} else {
				$data['StatusClass'] = 'error';
				$data['RemoveTableRow'] = 'tr_' . $file_id;
				$data['StatusText'] = __('An error occurred while deleting.',true);
			}
		} else {
			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $file_id;
			$data['StatusText'] = __('The file could not be deleted. Please, try again.',true);
		}

		$this->set('request',json_encode($data));

	}

	public function certificatefile() {

		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);
		$models = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');
		$path = Configure::read('device_folder') . $device_id. DS . $certificate_id . DS . $certificate_data_id . DS;
		$this->fileupload($path,$models);

		if(isset($this->request->data['close_reload']) && $this->request->data['close_reload'] == 1) {

			$FormName['controller'] = 'devices';
			$FormName['action'] = 'monitorings';
			$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);
			$this->set('message_class', 'success');

			$this->set('FormName',$FormName);

		} else {

			$this->Flash->hint(__('There is a valid certificate.') . ' ' . __('When you upload a new file, the existing file will be deleted.'), array('key' => 'hint'));

		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'editmonitoring', 'terms' => $this->request->params['pass']);

		$this->set('SettingsArray', $SettingsArray);

	}

	protected function fileupload($path,$models) {

		$this->layout = 'modal';
                $sub = $models['sub'];
                $top = $models['top'];
                $main = $models['main'];

		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

		$this->request->projectvars['VarsArray'][17] = $certificate_id;
		$this->request->projectvars['VarsArray'][18] = $certificate_data_id;

		$_data_option = array(
					'conditions' => array(
						$sub . '.id' => $certificate_data_id,
						$sub . '.device_certificate_id' => $certificate_id,
						$sub . '.device_id' => $device_id,
						$sub . '.deleted' => 0,
						)
					);

		$_data = $this->$top->$main->$sub->find('first',$_data_option);

		if($_data[$models['sub']]['certified_file'] != '' && file_exists($path . $_data[$models['sub']]['certified_file'])){
			$this->set('hint',__('Do you want to overwrite the existing file?',true));
		}

		$this->set('data',$_data);
		$this->set('models',$models);

		$Extension = 'pdf';

		if(isset($_FILES) && count($_FILES) > 0){

			// Infos zur Datei
			$fileinfo = pathinfo($_FILES['file']['name']);
			$filename_new = $device_id.'_'.$certificate_id.'_'.$certificate_data_id.'_'.time().'.'.$fileinfo['extension'];

			if($Extension == strtolower($fileinfo['extension'])){

				if (isset($_FILES['error']['file'])) {
						$this->Session->write('FileUploadErrors', $_FILES['error']['file']);
				}

				// Vorhandene Dateien werden gelöscht
				// Vorhandene Dateien werden gelöscht
				if ($_FILES['file']['error'] == 0) {

						$folder = new Folder($path);
						$folder->delete();

						if (!file_exists($path)) $dir = new Folder($path, true, 0755);
				}

				move_uploaded_file($_FILES['file']["tmp_name"], $path. DS .$filename_new);

				$dataFiles = array(
						'id' => $certificate_data_id,
						'certified_file' => $filename_new,
						'user_id' => $this->Auth->user('id')
					);

				$this->$top->$main->$sub->save($dataFiles);

			}
		}
	}

	public function getcertificatefile() {

		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);
		$models = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');
		$path = Configure::read('device_folder') . $device_id. DS . $certificate_id . DS . $certificate_data_id . DS;
		$this->getfile($path,$models);

	}

	protected function getfile($path,$models) {

		$this->layout = 'blank';

		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

		$certificate_data_option = array(
									'order' => array('certified_date DESC'),
									'conditions' => array(
										$models['sub'].'.id' => $certificate_data_id
										)
									);

                $certificate_data = $this->{$models['top']}->{$models['main']}->{$models['sub']}->find('first',$certificate_data_option);

		$this->set('path',$path . $certificate_data[$models['sub']]['certified_file']);
	}

	public function delcertificatefile() {

		$this->layout = 'modal';

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'editmonitoring', 'terms' => $this->request->params['pass']);

		$this->set('SettingsArray', $SettingsArray);

		$device_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
		$certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
		$certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

		$this->request->projectvars['VarsArray'][17] = $certificate_id;
		$this->request->projectvars['VarsArray'][18] = $certificate_data_id;

		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');
		$arrayData = $this->Xml->DatafromXml('DeviceCertificateEdit','file',null);

		if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->request->data[$model['sub']]['user_id'] = $this->Auth->user('id');

			if($this->{$model['top']}->{$model['main']}->{$model['sub']}->save($this->request->data)) {

				$path = Configure::read($folder) . $device_id. DS . $certificate_id . DS . $certificate_data_id . DS;
				$folder = new Folder($path);
				$folder->delete();
				$this->Flash->success(__('The file has been delete',true), array('key' => 'success'));
				$this->Autorisierung->Logger($certificate_data_id, $this->request->data[$model['sub']]);
			} else {
				$this->Flash->error(__('The file could not be delete. Please, try again.',true), array('key' => 'error'));
			}

			$FormName['controller'] = 'devices';
			$FormName['action'] = 'monitorings';
			$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

			$this->set('FormName',$FormName);

		} else {
			$this->Flash->hint(__('Do you want to delete the certificate file of this monitor?',true), array('key' => 'hint'));
		}

		$certificate_data_option = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
										'DeviceCertificateData.id' => $certificate_data_id,
										'DeviceCertificateData.deleted' => 0,
										)
									);

		$certificate_data = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificate_data_option);

    $this->request->data = $this->Data->DropdownData(array('DeviceCertificateData'), $arrayData, $this->request->data);
		$Device = $this->Qualification->SingleCertificateSummary($certificate_data,$model);

		$this->request->data = $Device;

		$this->set('settings', $arrayData);

	}

	public function email_certificate() {

		$this->layout = 'modal';

		$message  = null;
		$message .= __('Eine Zusammenfassung von anstehenden und fehlerhaften Überwachungen, wird an die eingegebene E-Mail-Adresse versand.');
		$message .= '<br>';
		$message .= __('Sie können Meldungen abwählen in dem Sie die Checkbox der Meldung deaktivieren.');

		$this->set('message',$message);

		$_ids = array();

		$arrayData = $this->Xml->DatafromXml('DeviceCertificate','file',null);
		$certificat_types = $arrayData['settings']->DeviceCertificate->certificat->select->static->value;

		$certificat_types_summary = array();


		if(isset($this->request->data['Device']) && ($this->request->is('post') || $this->request->is('put'))) {

			$emailto = $this->request->data['Device']['email'];
			$isValid = Validation::email($emailto);

			if($isValid === true){
				$_id = array();
				foreach($this->request->data['Device'] as $_key => $_data){
					if(is_numeric($_key)){
						if($_data == 1){
							$_id[] = $_key;
						}
					}
				}

				$_ids = array('DeviceCertificate.id' => $_id);
			}
		}

		$certificates_options = array(
									'order' => array('DeviceCertificateData.id DESC'),
									'conditions' => array(
													'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
													'Device.deleted' => 0,
													'DeviceCertificateData.active' => 1,
													'DeviceCertificateData.deleted' => 0,
													$_ids
												)
											);

		$certificate = $this->Device->DeviceCertificate->DeviceCertificateData->find('all',$certificates_options);

		$certficates = array();

		$summary = array(
						'errors' => array(),
						'warnings' => array(),
						'futurenow' => array(),
						'future' => array(),
						'hints' => array(),
						'deactive' => array()
					);

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),

					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		foreach($certificate as $_key => $_certificates){

			$certificate[$_key] = $this->Qualification->DeviceCertification($_certificates,array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));
			foreach($certificat_types as $_certificat_types){
				if($_certificates['DeviceCertificate']['certificat'] != trim($_certificat_types)) continue;
				$summary = $this->Qualification->DeviceCertificationSummary($summary,$certificate[$_key],array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'),trim($_certificat_types));
			}
		}

//pr($summary);
		foreach($summary_desc as $__key => $__summary_desc){
			if(count($summary[$__key]) > 0){
				foreach($summary[$__key] as $___key => $___summary){
					foreach($certificat_types as $_key => $_certificat_types){
					}
				}
			}
		}


//pr($certificat_types_summary);
/*

			// Nach Fehler/Hinweisarten sortieren
			foreach($summary as $__key => $_summary){
				if(isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0){
					$summary[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
				}
			}
*/

		$SettingsArray = array();
//		$SettingsArray['devicelink'] = array('discription' => __('Back to overview',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
//		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);

		$this->__email($summary,$summary_desc,'summary_monitoring','Monitoringinfos');
	}

	protected function __email($summary,$summary_desc,$mailview,$subject) {
		$lastModalURLArray['controller'] = 'devices';
		$lastModalURLArray['action'] = 'index';
		$lastModalURLArray['pass'] = array();
		$lastModal = $this->Session->read('lastModalURLArray');

		if(is_array($lastModal) && count($lastModal) == 3){
			$lastModalURLArray['controller'] = $this->Session->read('lastModalURLArray.controller');
			$lastModalURLArray['action'] = $this->Session->read('lastModalURLArray.action');
			$lastModalURLArray['pass'] = $this->Session->read('lastModalURLArray.pass');
		}

		$SettingsArray = array();

		$this->set('lastModalURLArray', $lastModalURLArray);
		$this->set('summary_desc', $summary_desc);
		$this->set('summary', $summary);
		$this->set('SettingsArray', $SettingsArray);

		if (isset($this->request->data['Device']) && ($this->request->is('post') || $this->request->is('put'))) {

			$emailto = $this->request->data['Device']['email'];
			$isValid = Validation::email($emailto);
			$url['controller'] = $this->request->data['url']['controller'];
			$url['action'] = $this->request->data['url']['action'];

			if(!isset($this->request->data['url']['pass'])){
				$url['pass'] = array();
			}
			else {
				$url['pass'] = $this->request->data['url']['pass'];
			}

			if($isValid === true){

				App::uses('CakeEmail', 'Network/Email');

				$commentar = null;

				if($this->request->data['Device']['comment'] != ''){
					App::uses('Sanitize', 'Utility');
					$commentar = Sanitize::stripScripts($this->request->data['Device']['comment']);
					$this->set('commentar',$commentar);
				}

				$email = new CakeEmail('default');
				$email->domain(FULL_BASE_URL);
				$email->from(array('reminder@qm-systems.info' => 'MPS Prüfberichtsdatenbank'));
				$email->sender(array('reminder@qm-systems.info' => 'MPS Prüfberichtsdatenbank'));
				$email->to($emailto);
				$email->subject($subject);
				$email->template($mailview,'summary');
				$email->emailFormat('both');
				$email->viewVars(array(
							'summary_desc' => $summary_desc,
							'summary' => $summary,
							'commentar' => $commentar,
    						)
						);

				$email->send();

				$SettingsArray = array();
				$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'email_certificate', 'terms' => $this->request->projectvars['VarsArray']);

				$this->set('SettingsArray', $SettingsArray);
				$this->set('lastModalURLArray', $url);
				$this->set('message',__('E-Mail versendet an '.$emailto,true));
				$this->render('email_successful_send','modal');

			}

			elseif($isValid === false){
				$this->set('message',__('E-Mail konte nicht versendet werden. Sie haben keine gültige E-Mail-Adresse angegeben.',true));
				$this->set('lastModalURLArray', $url);
			}
		}
	}


	public function pdfinv() {

		$this->layout = 'pdf';

		if ($this->Session->check('searchdevice.devices')) $sessiondevice = $this->Session->read('searchdevice.devices');
		if(empty($sessiondevice))   {


			if($this->request->projectvars['VarsArray'][15] > 0){
				$testingmethod_id = $this->request->projectvars['VarsArray'][15];
			}
			else {
				if(isset($this->request->data['QuickDevicesearch'])) {
					$device_id = ($this->Sicherheit->Numeric($this->request->data['device_id']));

					if($device_id == 0){
						throw new NotFoundException(__('Invalid value'));
					}
				}
				else {
					throw new NotFoundException(__('Invalid value'));
				}
			}
		}

		if(isset($this->request->data['showpdf']) && $this->request->data['showpdf'] == 1) $ShowPdf = 1;

		$this->Device->recursive = 1;
		$this->Device->DeviceTestingmethod->recursive = -1;
		if(isset($testingmethod_id)){
			$testingmethod = $this->Device->DeviceTestingmethod->find('first',array('conditions' => array('DeviceTestingmethod.id' => $testingmethod_id)));
		}

		//		$OrderBy = array('Device.intern_no' => 'asc','Device.device_type' => 'asc','Device.name' => 'asc','Device.producer' => 'asc','Device.registration_no' => 'asc','Device.working_place' => 'asc');
		$OrderBy = array('Device.device_type' => 'asc','Device.name' => 'asc','Device.producer' => 'asc','Device.registration_no' => 'asc','Device.working_place' => 'asc');
		$conditions = array(
									'Device.deleted' => 0
								);

		if(isset($sessiondevice) && !empty($sessiondevice)) {
			$device = $this->Session->read('searchdevice.devices');
			$conditions['Device.id'] = $device;

			$devices = $this->Device->find('all',array(
				'order' => $OrderBy,
				'conditions' => $conditions));
		}
		elseif(!isset($device_id)){

			$devices = $this->Device->find('all',array(
				'order' => $OrderBy,
				'conditions' => $conditions,

				'joins' => array(
					array(
						'table' => 'devices_testingmethods_devices',
						'alias' => 'DeviceTestingmethod',
						'type' 	=> 'INNER',
						'conditions' => array(
							'DeviceTestingmethod.testingmethod_id' => $testingmethod_id,
							'DeviceTestingmethod.device_id = Device.id'
						)
					)
				)
			));

		}
		elseif(isset($device_id)){
			$devices = $this->Device->find('all',array(
				'order' => $OrderBy,
				'limit' => 1,
				'conditions' => array(
					'Device.id' => $device_id
				)
			));
			//$devices = $this->paginate('Device');

			if(count($devices) == 0){
				$this->render();
				return;
			}
			elseif(count($devices) > 0){
				$this->request->projectvars['VarsArray'][15] = $devices[0]['DeviceTestingmethod'][0]['id'];
			}

			// Die Url für das Actionatribut im Quicksearchform muss angepasst werden
			// Wenn das Eingabefeld geleert wird muss die aktuelle Seite mit Parametern aufgerufen werden
			// sonst kommt ein Error
			$this->request->here = $this->request->here.'/'.implode('/',$this->request->projectvars['VarsArray']);
		}



		if(isset($testingmethod)){
			$this->request->data['testingmethod'] = $testingmethod ['DeviceTestingmethod'] ;
		}


		$arrayData = null;
		$arrayData = $this->Xml->DatafromXml('Device_Inv','file',null);
		if(!is_array($arrayData)) return;


		if(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png'){
			$this->request->data['Testingcomp'] = $this->Auth->user('Testingcomp');
			$this->request->data['Testingcomp']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png';
		}

		$model = array('top' => 'Device','main' => 'DeviceCertificate','sub' => 'DeviceCertificateData');

		foreach ($devices as $key => $value) {

			$Devicedata [$key] = $value ['Device'];
			//$Devicedata [$key] ['examiner'] = $value ['Examiner'] ['first_name'].' '.$value ['Examiner'] ['name'];
			$monitorings = $this->Qualification->MonitoringSummary($value,array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));
			$deviceid = $value ['Device'] ['id'];
			$monitorings = isset($monitorings ['summary'] ['monitoring']) ? $monitorings ['summary'] ['monitoring'] : '';
			$uberwachungen = '';

			if (!empty($monitorings)){
				foreach ($monitorings as $key2 => $_monitorings) {
					foreach($_monitorings as $key3 => $value2) {
						if(!isset($value2[$deviceid]['info']['certificat'])) continue;
						$uberwachungen .= $value2 [$deviceid] ['info']['certificat'] . ', '.$value2 [$deviceid] ['info'] ['next_certification_date']."\n";
					}
					$Devicedata [$key]  ['monitorings'] = $uberwachungen;
				}
			}
			else $Devicedata [$key]  ['monitorings'] ='' ;
		}


		$devices = $Devicedata;

		if(isset($ShowPdf) && $ShowPdf == 1){

			$this->layout = 'json';

			$this->autoRender = false;

			$PDFView = new View($this);
			$PDFView->autoRender = false;
			$PDFView->layout = null;
			$PDFView->set('xml_deviceinv',$arrayData["settings"]);
			$PDFView->set('Devices',$Devicedata);
			$PDFView->set('return_show',true);

			$Data = $PDFView->render('pdfinv');

			$this->set('request',json_encode(array('string' => $Data)));
			$this->render('pdf_show');

			return;

		}

		$this->set('xml_deviceinv',$arrayData["settings"]);
		$this->set('Devices',$devices);

	}

	public function sessionstorage(){
		if($this->request->data['req_controller'] == 'devices'){

			$act = $this->request->data['req_action'];
			switch($act){
				case 'addmonitoring':
				$this->Data->Sessionstoraging('DeviceCertificate');
				break;
				default:
				$this->Data->Sessionstoraging();
			}
			// $this->Data->Sessionstoraging();
		}
	}


	public function update () {

			if ( !empty($this->request->data['Device'])) {
				App::uses('Sanitize', 'Utility');
				foreach ($this->request->data['Device'] as $key => $value) {

					if(!empty($value)) {
						$options ['Device.'.$key] = $value;
					}
				}
				$this->Device->recursive = 0;
				$options = array(
							//	'fields'=>array('Device.id'),
								'conditions' => array(
									$options
									)
								);

				$result = $this->Device->find('all',$options);

				//var_dump($result);
				//$result['result'] = $result[0]['Rkl'];
				//$result['fieldids'] = $fieldids;

				$this->set('response',json_encode($result));


			}

	}

}
