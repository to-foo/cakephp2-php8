<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Reportnumbers');
App::import('Controller', 'Components');

class ExternController extends AppController {

//	public $components = array('Auth','Acl','Autorisierung','Cookie','Lang','RequestHandler');
	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Image', 'Pdf');
	public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html');
	public $layout = 'incomming';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		
		if(isset($this->request->params['pass'][0])){
//			$this->Auth->logout();
		}

		$this->Autorisierung->Protect();
		$this->loadModel('User');

		$this->Lang->Choice();
		$this->Lang->Change();
		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('locale', $this->Lang->Discription());
	}

	public function index() {
		$this->redirect(array('controller' => 'extern', 'action' => 'externreports'));
	}
	
	public function incomming($id = null) {

			$ClientIp = $this->Autorisierung->GetClientIp();

			if($id == null && $this->Auth->user('id') == '' && !isset($this->request->data['Extern'])) {
				$this->set('stop_login',__('Der Vorgang ist ungültig oder bereits abgeschlossen.',true));
				return;
			}

			if($this->request->is('post') && $this->Cookie->read('ticket') && $this->Session->check('UserInfo')) {

				if (isset($this->request->data['Language']['beschreibung'])) {
					$this->loadModel('Language');
					$lang = $this->Language->find('first', array('conditions'=>array('id'=>intval($this->request->data['Language']['beschreibung']))));
			
					if(count($lang)) {
						$this->Cookie->write('lang', intval($lang['Language']['id']), false, '+365 day');
						Configure::write('Config.language', $lang['Language']['locale']);
						setlocale(LC_ALL, $lang['Language']['iso']);
					}
				}
				
				$this->request->data['User']['username'] = $this->request->data['Extern']['username'];
				$this->request->data['User']['password'] = $this->request->data['Extern']['password'];
				unset($this->request->data['Extern']);


				if ($this->Auth->login()) {
//				if ($this->Auth->login($this->request->data)) {
					$user = $this->Extern->find('first',array('conditions' => array('Extern.username' => $this->Auth->user('Extern.username'))));
	
					// Die in Hex umgewandelte ID wird in raw zurückgewandelt
					if (ctype_xdigit($this->Session->read('UserInfo.reportnumber_id')) && strlen($this->Session->read('UserInfo.reportnumber_id')) % 2 == 0) {
						$hex_id = hex2bin($this->Session->read('UserInfo.reportnumber_id'));
					} else {
						$this->set('message',__('Die Entschlüsselte Daten sind fehlerhaft.',true));
						return;
					}
										
					$UserInfo = $this->Session->read('UserInfo');
					$UserInfoSave = $this->Session->read('UserInfo');

					$UserInfoSave['user_id'] = $this->Auth->user('id');
					$UserInfoSave['testingcomp_id'] = $this->Auth->user('testingcomp_id');

					$decrypted_id = $this->Autorisierung->DecriptIncommingId($UserInfoSave['reportnumber_id']);
					
					if($decrypted_id != false) $UserInfoSave['reportnumber_id'] = $decrypted_id ;

					$this->Extern->ExternData->create();
					
					if ($this->Extern->ExternData->save($UserInfoSave)) {
//						pr($UserInfoSave);
						$ticket_id = bin2hex(Security::rijndael(($this->Extern->ExternData->getLastInsertId()), Configure::read('SignatoryHash'), 'encrypt'));
						$UserInfo['ticket_id'] = $ticket_id;
						$this->Session->write('UserInfo',$UserInfo);
						$this->Cookie->write('ticket',$UserInfo['ticket_id']);
						$this->redirect(array('controller' => 'extern', 'action' => 'externreports'));
					} else {
						$this->Cookie->delete('ticket');
						$this->Session->delete('UserInfo');
						$this->Auth->logout();
					}

					$this->redirect(array('controller' => 'extern', 'action' => 'externreports'));
				} else {
					$this->Cookie->delete('ticket');
					$this->Session->delete('UserInfo');
					$this->Session->setFlash(__('Invalid username or password, try again'));
					$this->redirect(array('controller' => 'extern', 'action' => 'incomming', $id));
				}
			return;
			}

		// Beim Zurückkehren auf die Loginseite wird alles gelöscht und ausgeloggt
		if($this->Auth->user()){
			$this->Cookie->delete('ticket');
			$this->Session->delete('UserInfo');
			$this->Auth->logout();
		}

		// Die beim Erstellen des QR-Codes chiffrierte ID wird deshiffriert
		$decrypted_id = $this->Autorisierung->DecriptIncommingId($id);
		// User ID 

		if($decrypted_id == false){
			$this->set('stop_login',__('Die angelieferten Daten sind fehlerhaft, der Loginvorgang wurde abgebrochen.',true));
			return;
		}
		
		// Ticket aus ID IP und Timestamp erstellen
		$Ticket = Security::hash($ClientIp.$id, null, true);
		
		// Alle Infos über User holen und in ein Array schreiben
		$UserInfo = array_merge(array('ticket' =>$Ticket),array('reportnumber_id' =>$id),array('ip' =>$ClientIp),get_browser(null, true),$this->Autorisierung->IpInfo($ClientIp,'Location'));
		
		// Die gelieferte IP wird auf plausibilität getestet
		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;
		$report_number_test = $this->Reportnumber->find('first',array('fields' => array('Reportnumber.id'), 'order' => array('Reportnumber.id desc')));

		if($report_number_test['Reportnumber']['id'] < $decrypted_id) {
			$this->set('stop_login',__('Die empfangenen Daten sind nicht plausibel, der Loginvorgang wurde abgebrochen.',true));
			return;
		}

		// Cookie mit Ticket schreiben
		$this->Session->write('UserInfo', $UserInfo);
		$this->Cookie->write('ticket', $Ticket, false, '1 hour');
	}

	public function externreports() {

		if($this->Session->read('UserInfo.ticket_id') != $this->Cookie->read('ticket')) $this->redirect(array('controller' => 'extern', 'action' => 'incomming'));

		$ticket_id = $this->Autorisierung->DecriptIncommingId($this->Session->read('UserInfo.ticket_id'));
		$reportnumber_id = $this->Autorisierung->DecriptIncommingId($this->Session->read('UserInfo.reportnumber_id'));
		$ticket = $this->Autorisierung->DecriptIncommingId($this->Cookie->read('ticket'));
		
		if(!is_int($ticket_id)) $this->redirect(array('controller' => 'extern', 'action' => 'incomming'));
		if(!is_int($reportnumber_id)) $this->redirect(array('controller' => 'extern', 'action' => 'incomming'));
		if(!is_int($ticket)) $this->redirect(array('controller' => 'extern', 'action' => 'incomming'));

		if($ticket_id != $ticket) $this->redirect(array('controller' => 'extern', 'action' => 'incomming'));

		$locale = $this->Lang->Discription();

		// Die gelieferte IP wird auf plausibilität getestet
		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;
		$report_number_test = $this->Reportnumber->find('first',array('fields' => array('Reportnumber.id'), 'order' => array('Reportnumber.id desc')));
		if($report_number_test['Reportnumber']['id'] < $reportnumber_id)  $this->redirect(array('controller' => 'extern', 'action' => 'incomming'));
		unset($report_number_test);
		
		// Daten des Prüfberichts holen
		$this->Reportnumber->recursive = 1;
		$Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumber_id)));

//pr($Reportnumber);

		// 
		$message = null;
		if($Reportnumber['Reportnumber']['status'] == 0){ 
			$message = __('This test report is still being processed. Please try again later.',true);
		}
		if($Reportnumber['Reportnumber']['new_version_id'] > 0){ 
			$message =  __('The test report has been replaced by a new version.',true);
		}
		if($Reportnumber['Reportnumber']['delete'] > 0){ 
			$message =  __('The report has been deleted.',true);
		}

		$this->set('message',$message);
		$this->set('Reportnumber',$Reportnumber['Reportnumber']);

		$Verfahren = ucfirst($Reportnumber['Testingmethod']['value']);
		$verfahren = $Reportnumber['Testingmethod']['value'];				

	 	$ReportKey['Archiv'] = 'Report'.$Verfahren.'Archiv';
	 	$ReportKey['Generally'] = 'Report'.$Verfahren.'Generally';
	 	$ReportKey['Specific'] = 'Report'.$Verfahren.'Specific';
	 	$ReportKey['Evaluations'] = 'Report'.$Verfahren.'Evaluation';
	 	$ReportKey['Pdf'] = 'Report'.$Verfahren.'Pdf';

		$ReportHeadline['Info'] = __('Report infos',true);
		$ReportHeadline['Generally'] = __('Object data',true);
		$ReportHeadline['Specific'] = __('Testing data',true);
		$ReportHeadline['Evaluation'] = __('Evaluations',true);

		$ReportData = array();
		$ReportData['Info'] = array();
		$ReportData['Info']['testingmethod'] = array('discription' => 'Prüfverfahren', 'value' => $Reportnumber['Testingmethod']['verfahren']);
		$ReportData['Info']['created'] = array('discription' => 'Erstellt', 'value' => $Reportnumber['Reportnumber']['created']);
		$ReportData['Info']['modified'] = array('discription' => 'Zu letzt modifiziert', 'value' => $Reportnumber['Reportnumber']['modified']);
		$ReportData['Info']['print'] = array('discription' => 'Das erste mal gedruckt', 'value' => date('Y-m-d h:m', $Reportnumber['Reportnumber']['print']));
	 	
	 	$this->loadModel($ReportKey['Archiv']);
		$Reportarchiv = $this->$ReportKey['Archiv']->find('first',array('conditions' => array($ReportKey['Archiv'].'.reportnumber_id' => $reportnumber_id)));

		$Reportobjekt = $this->Xml->XmltoArray($Reportarchiv[$ReportKey['Archiv']]['data'],'string',null);
	 	$xml = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);
		
		$impressum = null;
		
		if(!empty($xml['settings']->$ReportKey['Pdf']->additional->datafooter->element->text)){
			$impressum = trim($xml['settings']->$ReportKey['Pdf']->additional->datafooter->element->text);
		}
		
		unset($ReportKey['Archiv']);
		unset($Reportarchiv);

		// Allgemeine- und Prüfdaten aufnehmen
		foreach($ReportKey as $_key => $_ReportKey){

			if($_key == 'Evaluations') continue;
		
			foreach($xml['settings']->$_ReportKey->children() as $__key => $__ReportKey){
		
				if(empty($__ReportKey->key)) continue;
				if(empty($__ReportKey->pdf->output)) continue;

				if(!empty($__ReportKey->pdf->description)) $discription = str_replace('|br|','/',trim($__ReportKey->pdf->description));
				else $discription = __('no discription',true); 

				if(!empty($__ReportKey->key)) $key = trim($__ReportKey->key);
				else continue;
		
				if(!empty($Reportobjekt->$_ReportKey->$key)){
					$value = trim($Reportobjekt->$_ReportKey->$key);
					
				}
				else $value = '-';
				
				// Masseinheit anfügen
				if(!empty($__ReportKey->pdf->measure) && !empty($Reportobjekt->$_ReportKey->$key)) $value .= ' ' . $__ReportKey->pdf->measure;					

				if($__ReportKey->fieldtype == 'radio' && !empty($__ReportKey->radiooption)){
					$value = trim($__ReportKey->radiooption->value[intval($value)]);
				}

				$ReportData[$_key][$key] = array('discription' => $discription, 'value' => $value);
			}
		}

		
		$evaluations['headline'] = array();
		$evaluations['data'] = array();
		$radiooption = array(0 => '-',1 => 'ja/yes',2 => 'nein/no');

		if(!empty($xml['settings']->$ReportKey['Evaluations']->children()) && count($Reportobjekt->$ReportKey['Evaluations']) > 0){
			foreach($Reportobjekt->$ReportKey['Evaluations'] as $_key => $_Evaluations){
				$_evaluations = array();
				foreach($_Evaluations as $__key => $__Evaluations){
					
					if($xml['settings']->$ReportKey['Evaluations']->$__key->fieldtype == 'radio'){
						if(!empty($xml['settings']->$ReportKey['Evaluations']->$__key->radiooption)){
							$__Evaluations = trim($xml['settings']->$ReportKey['Evaluations']->$__key->radiooption->value[intval($__Evaluations)]);
						} else {
							$__Evaluations = $radiooption[intval($__Evaluations)];
						}
					}
					
					if(trim($__Evaluations) != ''){
						$_evaluations[$__key] = trim($__Evaluations);
					} else {
						$_evaluations[$__key] = '-';
					}
				}

			$evaluations['data'][] = $_evaluations;
			}
		}

		$evaluations['data'] = $this->Data->WeldSorting($evaluations['data']);
		$_headline = array();
		
		if(!empty($xml['settings']->$ReportKey['Evaluations']->children()) && count($Reportobjekt->$ReportKey['Evaluations']) > 0){
			foreach($Reportobjekt->$ReportKey['Evaluations'] as $_key => $_Evaluations){

				foreach($_Evaluations as $__key => $__Evaluations){
					if(empty($xml['settings']->$_key->$__key)) continue;
					if(empty($xml['settings']->$_key->$__key->key)) continue;
					if(empty($xml['settings']->$_key->$__key->pdf->output)) continue;

					$_headline['key'] = trim($xml['settings']->$_key->$__key->key);
					
					if(!empty($xml['settings']->$_key->$__key->pdf->description)){
						$_headline['discription'] = str_replace('|br|','/',trim($xml['settings']->$_key->$__key->pdf->description));
					}
					else $_headline['discription'] = 	__('no discription',true);			

					$evaluations['headline'][trim($xml['settings']->$_key->$__key->key)] = $_headline;
					$_headline = array();
				}
				break;
			}
		}
		
		$ReportData['Evaluations'] = $evaluations;

		unset($evaluations);		
		unset($Reportobjekt);
		unset($ReportKey);
		unset($xml);

		$this->set('ReportHeadline',$ReportHeadline);
		$this->set('impressum',$impressum);
		$this->set('ReportData',$ReportData);

	}
}
