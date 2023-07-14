<?php
App::uses('AppController', 'Controller');
/**
 * Reportnumbers Controller
 *
 * @property Reportnumber $Reportnumber
 */
class InvoicesController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Invoices','Search','Csv');
	public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html');
	public $layout = 'ajax';

	function beforeFilter() {

		App::import('Vendor', 'Authorize');
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		// Es wird das in der Auswahl gewählte Project in einer Session gespeichert

		$this->Autorisierung->ConditionsTopprojectsTest($this->request->params['pass'][0]);
		$this->Navigation->ReportVars();

		$this->loadModel('User');
		$this->loadModel('Topproject');
		$this->Autorisierung->Protect();

		$noAjaxIs = 0;
		$noAjax = array('printinvoice','export');

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
		$this->set('locale', $this->Lang->Discription());
		
		
		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterFilter() {
		$this->Navigation->lastURL();
	}
	
	public function overview($id = null) {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];

		$order = array();
		$this->loadModel('Order');

		// nochmal die Daten des Aufrtags  mit den Prüfberichten aus der Datenbank holen
		$options = array('conditions' => array(
							'Order.id' => $orderID
							)
						);

		$this->Order->recursive = 0;
		$order = $this->Order->find('first', $options);

		$menue = $this->Navigation->NaviMenue(null);

		// Die in diesem Auftrag enthalten Prüfverfahren wählen
		$this->loadModel('Reportnumber');

		// Die Daten zum Auftrag holen
		$reportsID = array();
		$this->Reportnumber->recursive = -1;
		$optionsReports = array('conditions' => array(
								'Reportnumber.topproject_id' => $projectID ,
								'Reportnumber.equipment_type_id' => $equipmentType, 
								'Reportnumber.equipment_id' => $equipment, 
								'Reportnumber.order_id' => $orderID, 
							),
						'fields' => array(
							'DISTINCT testingmethod_id'
							)
						);

		$reports = $this->Reportnumber->find('all', $optionsReports);

		foreach($reports as $_reports){
			$reportsID[] = $_reports['Reportnumber']['testingmethod_id'];
		}
		
		// nun die Testungmethodes zu den IDs
		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = -1;
		$optionsTestingmethods = array('conditions' => array(
								'Testingmethod.id' => $reportsID, 
							)
						);

		$testingmethods = $this->Testingmethod->find('all', $optionsTestingmethods);

		$StatistikArray = $this->Navigation->StatistikInvoices($testingmethods,$orderID,$projectID);

		// Breadnavi erstellen
		$breads = $this->Navigation->Breads(null);

		$this->set('SettingsArray', null);
		$this->set('StatistikArray', $StatistikArray);
		$this->set('order', $order);
		$this->set('orders', $order);
		$this->set('testingmethods', $testingmethods);

		// bei geschlossenem Auftrag
		if($order['Order']['status'] == 1){
			$this->set('message',__('Der Auftrag wurde geschlossen und kann nicht mehr bearbeitet werden.',true));
			$this->render('invoice');
			return;
		}
	}
	
	public function invoice($id = null) {

		$this->_include_invoice($id = null);

	}

	protected function _include_invoice($id = null) {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$testingmethodID = $this->request->projectvars['VarsArray'][4];
		$status = $this->request->projectvars['VarsArray'][5];

		// Wenn das Prüfverfahren per Post kommt, werden die Werte hier ausgetauscht
		if(isset($this->request->data['testingmethodID']) && $this->Sicherheit->Numeric($this->request->data['testingmethodID']) > 0){
			$testingmethodID = $this->request->data['testingmethodID'];
			$this->request->projectvars['VarsArray'][4] = $this->request->data['testingmethodID'];
			$this->request->projectvars['reportID'] = $this->request->data['testingmethodID'];
		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'invoices','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);
		$this->set('SettingsArray', $SettingsArray);

		// Wenn Prüfberichte von der Abrechnung ausgenommen werden sollen
		// beim normalen laden der Seite wird die Session immer gelöscht
		if(!isset($this->request->data['aktiv_deaktiv'])){
			$this->Session->write('aktiv_deaktiv', array());
		}
		// Wenn die Seite über den Aktiv/Deaktiv-Link geladen wird
		elseif(isset($this->request->data['aktiv_deaktiv'])){
			
			// Wert auf Zahl prüfen
			$aktiv_deaktiv = $this->Sicherheit->Numeric(trim($this->request->data['aktiv_deaktiv']));

			// Wenn noch keine Werte im Array sind, wird es angelegt
			if(count($this->Session->read('aktiv_deaktiv')) == 0){
				$AktivDeaktiv = array();
				// als Schlüssel wird die ID benutzt
				$AktivDeaktiv[$aktiv_deaktiv] = $aktiv_deaktiv;
				$this->Session->write('aktiv_deaktiv', $AktivDeaktiv);
			}
			else {
				// ansonsten wird das Array geladen
				$AktivDeaktiv = $this->Session->read('aktiv_deaktiv');
				// Wenn der Schlüssel noch nicht vorhanden ist, wird angelegt
				if(!isset($AktivDeaktiv[$aktiv_deaktiv])){
					$AktivDeaktiv[$aktiv_deaktiv] = $aktiv_deaktiv;			
				}
				// ansonsten wird der aktuelle Wert gelöscht
				elseif(isset($AktivDeaktiv[$aktiv_deaktiv])){
					unset($AktivDeaktiv[$aktiv_deaktiv]);
				}
				
				$this->Session->write('aktiv_deaktiv', $AktivDeaktiv);
			}
		}

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;

		// Wenn ein Prüfbericht aus der Abrechnung entfernt werden soll
		if(isset($this->request->data['canceln'])){
			$canceln = $this->Sicherheit->Numeric(trim($this->request->data['canceln']));
			$cancelnArray = array('fields' => array(
												'Reportnumber.number', 
												'Reportnumber.year'
											),
								  'conditions' => array(
								  				'Reportnumber.id' => $canceln
											)		
										);
			$cancelnReportnumber = $this->Reportnumber->find('first',$cancelnArray);
			
			if($this->Reportnumber->updateAll(
					array('Reportnumber.settled' => 3),
					array('Reportnumber.id' => $canceln)
				)
			){
				$this->Session->setFlash(__('The testingreport with the following reportnumber was canceled:', true) . ' '.$cancelnReportnumber['Reportnumber']['year'].'-'.$cancelnReportnumber['Reportnumber']['number']);
			}
			else {
				$this->Session->setFlash(__('The testingreport with the following reportnumber could not be canceled:', true) . ' '.$cancelnReportnumber['Reportnumber']['year'].'-'.$cancelnReportnumber['Reportnumber']['number']);
			}
		}
		
		// da laufend geschlossenen Prüfberichte pro Auftrag abgerechnet werden können
		// werden die einzelnen Abrechnungen in Sets gespeichert, welche mit dem Zeitstempel 
		// unterschieden werden
		if(isset($this->request->data['lf_number'])){
			$lf_number = $this->Sicherheit->Numeric(trim($this->request->data['lf_number']));
			$LfNumberArray = array('laufende_nr' => $lf_number);
			if($lf_number == 0){
			$LfNumberArray = null;
			}
		}
		elseif(isset($this->request->params['pass'][5])){
			$lf_number = $this->Sicherheit->Numeric(trim($this->request->params['pass'][5]));
			$LfNumberArray = array('laufende_nr' => $lf_number);
			if($lf_number == 0){
			$LfNumberArray = null;
			}
		}
		else{
			$lf_number = 0;
		}

		if(isset($this->request->data['testingmethodID'])){
			$testingmethodID = $this->Sicherheit->Numeric(trim($this->request->data['testingmethodID']));
		}

		// Prüfverfahren auslesen
		$optionsTestingmethode = array('conditions' => array(
								'id' => $testingmethodID 
							)
						);		

		$testingmethods = $this->Reportnumber->Testingmethod->find('first', $optionsTestingmethode);
		$verfahren = $testingmethods['Testingmethod']['value'];						
		$Verfahren = ucfirst($testingmethods['Testingmethod']['value']);						
		
		// Die Daten zum Auftrag holen
		$this->Reportnumber->Order->recursive = -1;
		$optionsOrder = array('conditions' => array(
								'Order.id' => $orderID, 
								'Order.topproject_id' => $projectID 
							)
						);

		// Daten des Auftrages auslesen
		$orders = $this->Reportnumber->Order->find('first', $optionsOrder);

		$this->set('orders', $orders);
		
		// bei geschlossenem Auftrag
		if($orders['Order']['status'] == 1){
			$this->set('message',__('Der Auftrag wurde geschlossen und kann nicht mehr bearbeitet werden.',true));
			$this->render();
			return;
		}
		
		// Die Einstellungen zum Darstellen der Daten
		$settings = $this->Xml->DatafromXml('Order','file',null);
		$this->set('settings', $settings['settings']);

		if($status == 2){
			$optionsTestingmethods = array(
								'fields' => array('DISTINCT Reportnumber.testingmethod_id'),
								'conditions' => array(
									'Reportnumber.topproject_id' => $projectID, 
									'Reportnumber.equipment_type_id' => $equipmentType, 
									'Reportnumber.equipment_id' => $equipment, 
									'Reportnumber.order_id' => $orderID, 
									'Reportnumber.delete' => 0, 
								)
							);

			$this->Reportnumber->recursive = -1;
			$ThisTestingmethods = $this->Reportnumber->find('all',$optionsTestingmethods);

			$AllTestingmethods = array();
			foreach($ThisTestingmethods as $_ThisTestingmethods){
				$AllTestingmethods[$_ThisTestingmethods['Reportnumber']['testingmethod_id']] = $_ThisTestingmethods['Reportnumber']['testingmethod_id']; 
			}

			$this->Reportnumber->Testingmethod->recursive = -1;
			$optionsTestingmethod = array(
									'fields' => array('Testingmethod.verfahren'),
									'conditions' => array('Testingmethod.id' => $AllTestingmethods)
									);
									
			$this->set('testingmethodID',$testingmethodID);
			$this->set('testingmethods',$this->Reportnumber->Testingmethod->find('list',$optionsTestingmethod));

			$statusDisc = __('Open settlements', true);
			$this->set('AktivDeaktiv', $this->Session->read('aktiv_deaktiv'));
			$this->set('statusDiscError', null);
			$this->set('statusDisc', $statusDisc);
		}

		if($status == 3){

			$laufendeNrArray = $this->Invoices->EditInvoices($projectID, $equipmentType, $equipment, $orderID, $testingmethodID, $status);

			$optionReportnumbersOpen = array('conditions' => array(
								'Reportnumber.topproject_id' => $projectID, 
								'Reportnumber.order_id' => $orderID, 
								'equipment_type_id' => $equipmentType, 
								'equipment_id' => $equipment, 								
								'Reportnumber.testingmethod_id' => $testingmethodID ,
								'Reportnumber.delete' => 0,
								'status >' => 1,
								'settled !=' => 3,
								'delete' => 0,
							)
						);
			$ReportnumbersOpen = $this->Reportnumber->find('count', $optionReportnumbersOpen);

			$statusDisc = __('Closed settlements', true);
			$statusDiscError  = '<div class="clear">';	
//			$statusDiscError .= '<p>';
			$statusDiscError .= '<a class="mymodal round" href="';
			$statusDiscError .= Router::url(array('controller' => 'invoices', 'action' => 'invoice', $projectID, $equipmentType, $equipment, $orderID, $testingmethodID, 2));
			$statusDiscError .= '">';
			$statusDiscError .= __('Show reports to be settled', true) . ' ('. $ReportnumbersOpen.')';
			$statusDiscError .= '</a>';
//			$statusDiscError .= '</p>';
			$statusDiscError .= '</div>';
			$this->set('statusDiscError', $statusDiscError);
			$this->set('statusDisc', $statusDisc);
		}		

		// Breadnavi erstellen
		$breads = $this->Navigation->Breads(null);

		// Status 3 markiert bereits abgerechnete Prüfberichte
		// abgerechnete Berichte können betrachtet und geändert werden

		if($status == 3){
			switch ($verfahren) {
			case 'rt':		
				$this->render($verfahren.'/invoiceok');
			break;	
			case 'pt':		
				$this->render($verfahren.'/invoiceok');
			break;	
			default:
				$this->render('default/invoiceok');
			break;	
			}

			return;
		}

		$this->Invoices->NewInvoices($projectID,$equipmentType,$equipment,$orderID,$testingmethodID,$status);

		switch ($verfahren) {
		case 'rt':		
			$this->render($verfahren.'/invoice');
		break;	
		case 'pt':		
			$this->render($verfahren.'/invoice');
		break;	
		default:
			$this->render('default/invoice');
		break;	
		}
	}
	
	public function invoicedata() {

		if($this->request->data['id'] != ''){
			$id = $this->Sicherheit->Numeric($this->request->data['id']);
		
			$invoices = $this->Invoice->find('first', array('conditions' => array('Invoice.id' => $id)));
			$value = $invoices['Invoice']['preis'];
			$value = strtr($invoices['Invoice']['preis'], array("," => "."));
			$InvoiceQuerys = $this->Session->read('InvoiceQuerys');	
			
			// Wenn die Anzahl mitgeschickt wird
			if(isset($this->request->data['number'])){
				$number = $this->Sicherheit->Numeric($this->request->data['number']);
				$value = $value * $number;
			}
		
			// Wenn die Keys mitgeschickt werden
			if(isset($this->request->data['keys'])){
				$keys = explode('_', $this->request->data['keys']);
				$InvoiceQuerys = $this->Session->read('InvoiceQuerys');

				// um das Array als XML zu speichern müssen die Keys manpuliert werden, keine Ziffern
				$InvoiceQuerysXML = array();
				
				@$InvoiceQuerysXML[$keys[0]]['x'.$keys[1]]['x'.$keys[2]]['x'.$keys[3]]['selectet'] = $this->request->data['id'];
				@$InvoiceQuerysXML[$keys[0]]['x'.$keys[1]]['x'.$keys[2]]['x'.$keys[3]]['price'] = $value;
				@$InvoiceQuerysXML[$keys[0]]['x'.$keys[1]]['x'.$keys[2]]['x'.$keys[3]]['singleprice'] = $invoices['Invoice']['preis'];

				$this->Session->write('InvoiceQuerys', $InvoiceQuerysXML);
			}
			
		$value = number_format($value, 3);
		}
		else {
			// Wenn ein Preisdatensatz auf Null zurückgestellt wird
			if(isset($this->request->data['keys'])){
				$keys = explode('_', $this->request->data['keys']);
				$InvoiceQuerys = $this->Session->read('InvoiceQuerys');
				$InvoiceQuerysXML[$keys[0]]['x'.$keys[1]]['x'.$keys[2]]['x'.$keys[3]]['selectet'] = null;
				$InvoiceQuerysXML[$keys[0]]['x'.$keys[1]]['x'.$keys[2]]['x'.$keys[3]]['price'] = null;
				$InvoiceQuerysXML[$keys[0]]['x'.$keys[1]]['x'.$keys[2]]['x'.$keys[3]]['singleprice'] = null;
				$this->Session->write('InvoiceQuerys', $InvoiceQuerysXML);
			}
			$value = '';
		}
				
		$this->set('value', $value);
 		$this->render(null, 'csv');
	}

	public function create() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$testingmethodID = $this->request->projectvars['VarsArray'][4];

		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = -1;
		$Testingmethod = $this->Testingmethod->find('first', array(
												'conditions' => array(
													'Testingmethod.id' => $testingmethodID
													)
												)
											);
		$this->layout = 'csv';

		$number = $this->Sicherheit->Numeric($this->request->data['number']);

		// nochmal die gesamte Abrechnungsliste für die zusätzlichen Leistungen aus der Datenbank holen
		$this->loadModel('Invoice');
		$Invoice = $this->Invoice->find('list', array(
											'fields' => array('kurztext'),
											'conditions' => array(
													'Invoice.testingmethode' => array($Testingmethod['Testingmethod']['value'],'all'),
												)
											)
										);
		$this->set('number', $number);
		$this->set('Invoice', $Invoice);
	}

	public function add() {
		$this->layout = 'blank';

		$number = $this->Sicherheit->Numeric($this->request->data['number']);
		$value = $this->Sicherheit->Numeric($this->request->data['value']);

		// nochmal die gesamte Abrechnungsliste für die zusätzlichen Leistungen aus der Datenbank holen
		$this->loadModel('Invoice');
		$thisInvoice = $this->Invoice->find('first', array(
												'conditions' => array(
													'Invoice.id' => $value
													)
												)
											);

		$Invoice = $this->Invoice->find('list', array(
												'fields' => array(
													'kurztext'
													),
												'conditions' => array(
													'Invoice.position LIKE' => '%.%'
													)
												)
											);
		// nochmal alle Prüfer aus der Datenbank für die Dropdownliste
		$this->loadModel('Examiner');
		$this->Examiner->recursive = -1;
		$ExaminiererArray = $this->Examiner->find('list', array(
												'fields' => array(
													'id','name'
													)
												)
											);

		// Das brauchen wir nicht mehr
		$Examiner = array();

		$this->set('ExaminiererArray', $ExaminiererArray);
		$this->set('value', $value);
		$this->set('number', $number);
		$this->set('thisInvoice', $thisInvoice);
		$this->set('Invoice', $Invoice);

		
	}

	public function number() {

		$this->layout = 'csv';

		$row = $this->Sicherheit->Numeric($this->request->data['Additional']['row']);	
		$id = $this->Sicherheit->Numeric($this->request->data['Additional']['id']);	
		$numbers = ereg_replace(",", ".", $this->request->data['Additional']['number']);	
		
		// Den Preis aus der Datenbank holen
		$thisInvoice = $this->Invoice->find('first', array(
												'conditions' => array(
													'Invoice.id' => $id
													)
												)
											);

		$price = ereg_replace(',','.', $thisInvoice['Invoice']['preis']);
		
		if(is_numeric($numbers)) {
			$pricetotal = $price * $numbers;
		}
		else {
			$pricetotal = $price;
		}
		
		$pricetotal = number_format($pricetotal,3);
		
		$this->set('pricetotal', $pricetotal);
		$this->set('number', $row);
		$this->set('numbers', $numbers);
	}

	public function date() {
		
		$date = $this->request->data['date'];

		$this->set('date', $date);
	 	$this->render(null, 'modal');
	}

	public function editadditional() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$testingmethodID = $this->request->projectvars['VarsArray'][4];
		$status = $this->request->projectvars['VarsArray'][5];
		
		if($this->request->projectvars['VarsArray'][6] > 0){
			$id = $this->request->projectvars['VarsArray'][6];
		}

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'invoices','action' => 'invoice', 'terms' => $this->request->projectvars['VarsArray']);
		$this->set('SettingsArray', $SettingsArray);

		if($this->request->projectvars['VarsArray'][6] > 0){
			$laufendeNr = $this->request->projectvars['VarsArray'][6];
		}

		if(isset($this->request->data['laufende_nr'])){
			$laufendeNr = $this->Sicherheit->Numeric(trim($this->request->data['laufende_nr']));
		}

		if(isset($this->request->data['Statisticsadditional']['laufende_nr'])){
			$laufendeNr = $this->Sicherheit->Numeric(trim($this->request->data['Statisticsadditional']['laufende_nr']));
		}

		$this->loadModel('Statisticsadditional');
		$this->loadModel('Invoice');
		$this->loadModel('Examiner');
		$this->loadModel('Testingmethod');
		$Testingmethod = $this->Testingmethod->find('first', array(
												'conditions' => array(
													'Testingmethod.id' => $testingmethodID
													)
												)
											);

		$this->Examiner->recursive = -1;

		if(isset($this->request['data']['Statisticsadditional'])){
			
			$data = $this->request['data']['Statisticsadditional'];

			$kurztext = $this->Sicherheit->Numeric($data['kurztext']);
			$number = $this->Sicherheit->Numeric($data['number']);
			$examiniererID1 = $this->Sicherheit->Numeric($data['examinierer_1']);
			$examiniererID2 = $this->Sicherheit->Numeric($data['examinierer_2']);
			$invoices = $this->Invoice->find('first', array(
												'fields' => array('kurztext','me','preis'),
												'conditions' => array(
													'id' => $kurztext	
												)
											)
										);
										
			if(count($invoices) > 0){
				$user = $this->Session->read('Auth');
				$price = number_format($invoices['Invoice']['preis'],3);
							
				$data['laufende_nr'] = $laufendeNr;
				$data['topproject_id'] = $projectID;
				$data['testingmethod_id'] = $testingmethodID;
				$data['order_id'] = $orderID;
				$data['examinierer_1'] = $examiniererID1;
				$data['examinierer_2'] = $examiniererID2;
				$data['position'] = $kurztext;
				$data['kurztext'] = $invoices['Invoice']['kurztext'];
				$data['amount'] = $invoices['Invoice']['me'];
				$data['price'] = $price;
				$data['price_total'] = number_format($invoices['Invoice']['preis'] * $number,3);
				$data['user_id'] = $user['User']['id'];
			}
			if($this->Statisticsadditional->save($data)){
				$this->Session->setFlash(__('Additional created'));
				$this->_include_invoice($id = null);
				return;
			}
		}
										
		$kurztext = $this->Invoice->find('list', array(
												'fields' => array('id','kurztext'),
												'conditions' => array(
													'OR' =>
														array(
														'Invoice.testingmethode' => trim($Testingmethod['Testingmethod']['value']),
														'Invoice.testingmethode LIKE ' => ''
														)
													)
												)
											);
										
		$examinierers = $this->Examiner->find('list', array(
											'fields' => array('id','name'),
											)
										);

		if($id > 0){
			$optionStatisticsadditional = array('conditions' => array(
													'id' => $id
												)
											);
		
			$statisticsadditionals = ($this->Statisticsadditional->find('first',$optionStatisticsadditional));
			$this->request->data = $statisticsadditionals;
		}
		elseif($id == 0){
			$this->request->data = array('Statisticsadditional' => array('laufende_nr' => $laufendeNr));
		}
		
	 	$this->set('examinierers', $examinierers);
	 	$this->set('kurztext', $kurztext);
		
	 	$this->render(null, 'modal');
	}

	public function editstandard() {
		
		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$testingmethodID = $this->request->projectvars['VarsArray'][4];
		$status = $this->request->projectvars['VarsArray'][5];
		$id = $this->Sicherheit->Numeric(trim($this->request->params['pass'][6]));
		$laufendeNr = $this->Sicherheit->Numeric(trim($this->request->params['pass'][7]));

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'invoices','action' => 'invoice', 'terms' => $this->request->projectvars['VarsArray']);
		$this->set('SettingsArray', $SettingsArray);

		$this->request->projectvars['VarsArray'][6] = $id;
		$this->request->projectvars['VarsArray'][7] = $laufendeNr;

		$ArrayBildnummer = $this->Invoices->ArrayBildnummer();
		$ArraySoucre = $this->Invoices->ArraySoucre();
		
		$this->loadModel('Statisticsstandard');
		$this->loadModel('Invoice');
		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = -1;

		$Testingmethod = $this->Testingmethod->find('first', array(
												'conditions' => array(
													'Testingmethod.id' => $testingmethodID
													)
												)
											);

		$invoicespositions = $this->Invoice->find('list', array(
											'fields' => array('id','kurztext'),
											'conditions' => array(
													'Invoice.testingmethode' => trim($Testingmethod['Testingmethod']['value']),
												)
											)
										);

		if(isset($this->request['data']['Statisticsstandard'])){
			
			$data = $this->request['data']['Statisticsstandard'];
			$invoice_id = $this->Sicherheit->Numeric($data['position']);
			$id = $this->Sicherheit->Numeric($data['id']);

			$invoices = $this->Invoice->find('first', array(
											'conditions' => array(
													'Invoice.id' => $invoice_id,
												)
											)
										);
										
			$optionStatisticsadditional = array('conditions' => array(
													'id' => $id
												)
											);
		
			$statisticsadditionals = ($this->Statisticsstandard->find('first',$optionStatisticsadditional));
										
			if(count($invoices) > 0 && count($statisticsadditionals)){
				
			unset($statisticsadditionals['Statisticsstandard']['created']);
			unset($statisticsadditionals['Statisticsstandard']['modified']);
			
			$user = $this->Session->read('Auth');
			
			$statisticsadditionals['Statisticsstandard']['position'] = $invoices['Invoice']['kurztext'];
			$statisticsadditionals['Statisticsstandard']['price'] = $invoices['Invoice']['preis'];
			$statisticsadditionals['Statisticsstandard']['price_total'] = number_format($invoices['Invoice']['preis'] * $statisticsadditionals['Statisticsstandard']['number'],3);
			$statisticsadditionals['Statisticsstandard']['user_id'] = $user['User']['id'];
			$statisticsadditionals['Statisticsstandard']['deaktiv'] = $data['deaktiv'];
			}

			if($this->Statisticsstandard->save($statisticsadditionals)){
				$this->set('closeModalreloadContent', true);				
			}
			
			$this->_include_invoice($id = null);
			return;

		}

		if($id > 0){
			$optionStatisticsadditional = array('conditions' => array(
													'id' => $id
												)
											);
		
			$statisticsadditionals = ($this->Statisticsstandard->find('first',$optionStatisticsadditional));

			// Die Strahlenquelle
			if(isset($ArraySoucre[$statisticsadditionals['Statisticsstandard']['source']])){
				$source = trim($ArraySoucre[$statisticsadditionals['Statisticsstandard']['source']]);
			}
			else{
				$source = $ArraySoucre['Röntgenröhre'];
			}

			// Die Bildnummer
			foreach($ArrayBildnummer as $_key => $_ArrayBildnummer){
				foreach($_ArrayBildnummer as $__key => $__ArrayBildnummer){
					if($__ArrayBildnummer == $statisticsadditionals['Statisticsstandard']['picture_no']){
						$picture = trim($_key);
						break;
					}
				}
			}

			$this->request->data = $statisticsadditionals;
		}

		$selected = 0;

		foreach($invoicespositions as $_key => $_invoicespositions){ 
			if($_invoicespositions == $this->request->data['Statisticsstandard']['position']){
				$selected = $_key;
			}
		}

	 	$this->set('selected', $selected);
	 	$this->set('invoicespositions', $invoicespositions);
		
   		$this->layout = 'modal';
	}

	public function deleteadditional() {
		
   		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$testingmethodID = $this->request->projectvars['VarsArray'][4];
		$status = $this->request->projectvars['VarsArray'][5];
		$id = $this->request->projectvars['VarsArray'][6];
		$this->loadModel('Statisticsadditional');

		$this->Statisticsadditional->id = $id;

		if (!$this->Statisticsadditional->exists()) {
			throw new NotFoundException(__('Invalid data'));
		}
		

		if(isset($this->request->data['Statisticsadditional'])){
				
			if($this->Statisticsadditional->delete()) {

				$this->request->projectvars['VarsArray'][6] = 0;
				$this->request->projectvars['VarsArray'][7] = 0;
				$this->request->projectvars['evalId'] = 0;
				$this->request->projectvars['weldedit'] = 0;

				$this->Session->setFlash(__('Data was deleted'));
				$this->_include_invoice($id = null);
				return;
			}
		}

		$this->request->data = $this->Statisticsadditional->find('first',array('conditions' => array('Statisticsadditional.id' => $id)));

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'invoices','action' => 'invoice', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);
		$this->set('message', __('Do you want delete this additional',true));

	}

	public function save() {

		$this->layout = 'blank';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$testingmethodID = $this->request->projectvars['VarsArray'][4];
		$status = $this->request->projectvars['VarsArray'][5];
		$postData = $this->request->data;

		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = -1;
		$Testingmethod = $this->Testingmethod->find('first', array(
												'conditions' => array(
													'Testingmethod.id' => $testingmethodID
													)
												)
											);

		$verfahren = $Testingmethod['Testingmethod']['value'];
		$Verfahren = ucfirst($Testingmethod['Testingmethod']['value']);
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		
		$ReportsOrderTestingmethods = array();
		$Insert = array();
		$ReportsOrderTestingmethods[$verfahren] = $Testingmethod;

		$this->loadModel('Reportnumber');
		$this->loadModel($ReportGenerally);
		$this->loadModel($ReportSpecific);
		$this->loadModel($ReportEvaluation);
		
		$this->Reportnumber->recursive = -1;
		
		foreach($this->request->data['Report']['Invoice'] as $_key => $_report){

			// Prüfberichte noch auf Zugehörigkeit testen
			// *******************************************

			$Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $this->Sicherheit->Numeric($_report))));
			$Reportgenerally = $this->$ReportGenerally->find('first',array('conditions' => array($ReportGenerally.'.reportnumber_id' => $this->Sicherheit->Numeric($_report))));
			$Reportspecific = $this->$ReportSpecific->find('first',array('conditions' => array($ReportSpecific.'.reportnumber_id' => $this->Sicherheit->Numeric($_report))));
			$Reportevaluation = $this->$ReportEvaluation->find('all',array('conditions' => array($ReportEvaluation.'.reportnumber_id' => $this->Sicherheit->Numeric($_report))));

			$ReportsOrderTestingmethods[$verfahren]['Reports'][$_key] = $Reportnumber;
			$ReportsOrderTestingmethods[$verfahren]['Reports'][$_key][$ReportGenerally] = $Reportgenerally;
			$ReportsOrderTestingmethods[$verfahren]['Reports'][$_key][$ReportSpecific] = $Reportspecific;
			$ReportsOrderTestingmethods[$verfahren]['Reports'][$_key][$ReportEvaluation] = $Reportevaluation;
		}

		// Das Array für die zusammengefassten Prüfbereiche vorbereiten
		if($Verfahren == 'Rt'){

			// alle im Auftrag vorhandenen Abmessungen auslesen
			// wird gebraucht um später das Nahtarray aufzuteilen
			$allDimensions = array();
				$allDimensions = $this->$ReportEvaluation->find('list', array(
						'fields' => array('dimension'),
						'conditions' => array(
							'reportnumber_id' => $this->request->data['Report']['Invoice'], 
							'dimension !=' => '' 
						)
					)
				);

			$Dimensions = array_values((array_unique($allDimensions)));

			$output = $this->Invoices->StatisticsStandardCreateRT($ReportsOrderTestingmethods,$verfahren);

			$dataArray = $output['dataArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$WeldsTotal = $output['WeldsTotal'];		
			$WeldsComplet = $output['WeldsComplet'];		
			$dataArrayError = $output['dataArrayError'];

			// Die Einträge für die Statisticsgenerall vorbereiten
			foreach($this->request->data['Report']['Invoice'] as $_key => $_reports){
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['topproject_id'] = $projectID;
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['testingmethod_id'] = $testingmethodID;
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['order_id'] = $orderID;
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['reportnumber_id'] = $_reports;
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['reportnumber'] = $dataArray[$verfahren][$_reports]['infos']['number'];
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['examinierer'] = 0;
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['source'] = $dataArray[$verfahren][$_reports]['radiation_source'];
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['welds'] = $dataArray[$verfahren][$_reports]['weldnumber'];
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['welds_ne'] = $dataArray[$verfahren][$_reports]['weld_ne'];
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['films_10_x_24'] = $dataArray[$verfahren][$_reports]['film_10_24'];
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['films_10_x_48'] = $dataArray[$verfahren][$_reports]['film_10_48'];
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['user_id'] = $this->Auth->user('id');
				$Insert['Statisticsgenerall'][$_key]['Statisticsgenerall']['ids'] = $dataArray[$verfahren][$_reports]['weld_ne'];
			}
			
			// die Nähte aus den Prüfberichten werden nach Strahlenquelle, Bildnummer und Abmessung zusammengefasst
			// es werden die passenden Werte aus der Abrechnungstabelle geladen und den entsprechenden Nähten zugeordnet
	
			$output = $this->Invoices->WeldArrayRT($InvoiceQuerys,$dataArray,$Dimensions,$WeldsTotal,$WeldsComplet,$verfahren);


			// Die Einträge für die Statisticsstandard vorbereiten
			foreach($this->request->data['Evaluation']['Invoice'] as $_key => $_evaluation){
				foreach($_evaluation as $__key => $__evaluation){
					foreach($__evaluation as $___key => $___evaluation){
						foreach($___evaluation as $____key => $____evaluation){

							$invoice = $this->Invoice->find('first',array('conditions'=>array('Invoice.id'=>$this->Sicherheit->Numeric($____evaluation))));
							$output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['selected'][$____evaluation] = $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['select'][$____evaluation];
						
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['topproject_id'] = $projectID;
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['testingmethod_id'] = $testingmethodID;
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['order_id'] = $orderID;
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['number'] = $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['number'];
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['dimension'] = $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['infos']['dimension'];
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['picture_no'] = $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['infos']['image_no'];
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['source'] = $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['infos']['radiation_source'];
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['position'] = $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['select'][$____evaluation];
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['price'] = $invoice['Invoice']['preis'];
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['price_total'] = round($invoice['Invoice']['preis'] * $output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['number'],3);
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['user_id'] = $this->Auth->user('id');
							$Insert['Statisticsstandard'][$____key]['Statisticsstandard']['ids'] = implode(' ',$output['InvoiceQuerys'][$_key][$__key][$___key][$____key]['ids']);
						}
					}
				}
			}
		}
		else {
		}

		// Wenn zusätzliche Leistungen angegeben sind
		if($this->request->data['Additional'] && count($this->request->data['Additional']) > 0){
			foreach($this->request->data['Additional'] as $_key => $_additional){
				$invoice = $this->Invoice->find('first',array('conditions'=>array('Invoice.id'=>$this->Sicherheit->Numeric($_additional['Invoice']))));
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['topproject_id'] = $projectID;
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['testingmethod_id'] = $testingmethodID;
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['order_id'] = $orderID;
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['position'] = $invoice['Invoice']['kurztext'];
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['amount'] = $invoice['Invoice']['maenge'];
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['number'] = $_additional['Number'];
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['price'] = $invoice['Invoice']['preis'];
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['price_total'] = round($_additional['Number'] * $invoice['Invoice']['preis'],3);
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['user_id'] = $this->Auth->user('id');
				$Insert['Statisticsadditional'][$_key]['Statisticsadditional']['ids'] = 0;
			}


		}

		// Die Links zum Drucken
		$PrintLinks = array('Aufmaß drucken');	
		$this->set('PrintLinks', $PrintLinks);
		$this->set('orderID', $orderID);

		$this->Invoices->SaveInvoices($Insert,$verfahren);
		
		$this->set('status',3);
		$this->Session->setFlash(__('Billing has been recorded.', true));
	}
	
	public function waitings($id = null) {
		
		$this->loadModel('Reportnumber');
		$this->loadModel('Order');

		$projectID = $this->Sicherheit->Numeric($this->request->params['pass'][0]);
		$orderID = $this->Sicherheit->Numeric($this->request->params['pass'][1]);
		$orderKat = $this->Sicherheit->Numeric($this->request->params['pass'][2]);

		// Falls schon Wartezeitdaten in der Datenbank existieren
		$optionsOrder = array(
							'conditions' => array(
								'id' => $orderID
							),
							'fields' => array(
								'waiting_times',
							)										
						);

		$this->Order->recursive = -1;
		$thisOrder = $this->Order->find('first', $optionsOrder);
		
		if($thisOrder['Order']['waiting_times'] != ''){
			$XmlFromDatabase = Xml::toArray(Xml::build($thisOrder['Order']['waiting_times']));
		}
		else {
			$XmlFromDatabase = array();
		}

		// Breadnavi erstellen
		$breads = $this->Navigation->Breads(null);
		$__breads = array();

		foreach($breads as $_key => $_breads){
			$__breads[] = $_breads;
			
		}
		

		// Wenn das Sessionarray bloß aktualisiert wurde
		if(isset($this->request->data['mode'])){
			
			$mode = $this->Sicherheit->Numeric($this->request->data['mode']);
			
			if($mode == 2){	
			
				// Das Waitingsarray wird nochmal durchlaufen um nicht mehr vorhandenen overlaps zu entfernen
				$Examiniers = $this->Session->read('Waitings');

				// Es werden die Timestamps auf überschneidung verglichen
				foreach($Examiniers as $_key => $_Examiniers){
					if(isset($_Examiniers['data'])){
						foreach($_Examiniers['data'] as $__key => $data){
				
							$__keyArray = array();
							$___keyArray = array();
				
							foreach($_Examiniers['data'] as $___key => $__Examiniers){
								if($data['reportnumber_id'] != $__Examiniers['reportnumber_id']){
									if(
									($__Examiniers['waiting_start_timestamp'] >= $data['waiting_start_timestamp'] && $__Examiniers['active'] != 0) 
									&& ($__Examiniers['waiting_start_timestamp'] < $data['waiting_stop_timestamp'] && $__Examiniers['active'] != 0)
									){
										$__keyArray[] = $__key;
										$___keyArray[] = $___key;
									}
								}
							}
				
							$Examiniers[$_key]['data'][$___key]['overlap'] = $__keyArray;
							$Examiniers[$_key]['data'][$__key]['overlap'] = $___keyArray;
						}
					}
				}
				
				$this->Session->write('Waitings', $Examiniers);
				
				$this->set('Examiniers', $this->Session->read('Waitings'));
				$this->set('breads', $__breads);
				$this->set('menues', null);
				$this->set('projectID', $projectID);
				$this->set('orderID', $orderID);
				return;
			}
		}

		$options = array('conditions' => array('Reportnumber.order_id' => $orderID, 'Reportnumber.topproject_id' => $projectID));
		
		// Prüfverfahren auslesen
		$this->Reportnumber->Testingmethod->recursive = -1;
		$testingmethods = $this->Reportnumber->Testingmethod->find('all');


		$ReportsOrderTestingmethods = array();
		
		// Die Daten zum Auftrag holen
		$this->Reportnumber->Order->recursive = -1;
		$optionsOrder = array('conditions' => array(
								'Order.id' => $orderID, 
								'Order.topproject_id' => $projectID 
							)
						);

		$optionsAllReportnumners = array(
									'conditions' => array(
											'order_id' => $orderID, 
											'topproject_id' => $projectID,
											'status' => 1
										),
									'fields' => array(
											'testingmethod_id',
											'number', 
											'id'
										)
									);
									
		// Daten des Auftrages auslesen
		$orders = $this->Reportnumber->Order->find('first', $optionsOrder);

		// Die Prüfberichte des Auftrages werden pro Prüfverfahren ausgelesen
		$this->Reportnumber->recursive = -1;
		
		// Alle Protokollnummern auslesen
		$allReportnumners = $this->Reportnumber->find('all', $optionsAllReportnumners);

		$waitingQuery = array();
		$testingmethodsQuery = array();
		$reportsQuery = array();
		$Examiniers = array();
		$WaitingHasChange = array();

		// aus dem Testingmethodarray die Sachen rausschmeißen, die wir nicht brauchen
		foreach($testingmethods as $_testingmethods){
			$testingmethodsQuery[$_testingmethods['Testingmethod']['id']] = $_testingmethods['Testingmethod']['value'];
		}
		
		// die vorher gesammelten PrüfberichtsIDs werden den Prüfverfahren zugeordnet
		foreach($allReportnumners as $_allReportnumners){
			$waitingQuery[$testingmethodsQuery[$_allReportnumners['Reportnumber']['testingmethod_id']]][$_allReportnumners['Reportnumber']['id']] = $_allReportnumners['Reportnumber']['number'];
		}

		// die Query zu den einzelnen Prüfverfahrne werden erstellt und die Daten aus der Datenbank geholt
		foreach($waitingQuery as $_key => $_waitingQuery){
			
			// die keys des Array sind die IDs, diese werden in ein extra Array gepackt für die Querys
			foreach($_waitingQuery as $__key => $__waitingQuery){
				$___key[] = $__key;
			}

			$ReportGenerally = 'Report'.ucfirst($_key).'Generally';
			$this->loadModel($ReportGenerally);
			$this->loadModel('Examiner');
//			if($_key == 'rt' || $_key == 'mt'){
				$optionsAllReportGenerally = array(
									'conditions' => array(
											'reportnumber_id' => $___key,
											'waiting_start != ' => '0000-00-00 00:00:00',
											'waiting_stop != ' => '0000-00-00 00:00:00',
										),
									'fields' => array(
											'id',
											'auftrags_nr',
											'block',
											'kks',
											'bauteil',
											'reportnumber_id',
											'examiner',
											'examinierer2',
											'supervision',
											'waiting_start', 
											'waiting_stop',
											'time_start',
											'time_stop'
										)										
									);
//			}

			$reportsQuery[$_key] = $this->$ReportGenerally->find('all', $optionsAllReportGenerally);
		}
		
		// die F-Nummern der Prüfer aus der Datenbank holen
		foreach($reportsQuery as $_key => $_reportsQuery){
			if(count($_reportsQuery) > 0){
				foreach($_reportsQuery as $__key => $__reportsQuery){
					
					$ReportGenerally = 'Report'.ucfirst($_key).'Generally';					
					
					// Suchoptionen für den ersten Prüfer
					
					$ExaminiererNumber = array();
					
					if($__reportsQuery[$ReportGenerally]['examiner'] != ''){
						$Examiniers[$__reportsQuery[$ReportGenerally]['examiner']] = array();
					}

					if($__reportsQuery[$ReportGenerally]['examinierer2'] != ''){
						$Examiniers[$__reportsQuery[$ReportGenerally]['examinierer2']] = array();
					}
										
					$optionsExaminer = array(
									'conditions' => array(
											'Examiner.discription' => $__reportsQuery[$ReportGenerally]['examiner'],
											'Examiner.testingcomp_id' => $this->Session->read('conditionsTestingcomps')
											),
									'fields' => array(
											'id',
											'number'
											)
									);
					
					if(count($this->Examiner->find('first', $optionsExaminer)) == 1){				
						$ExaminiererNumber = ($this->Examiner->find('first', $optionsExaminer));
						$reportsQuery[$_key][$__key][$ReportGenerally]['examiner_number'] = $ExaminiererNumber['Examiner']['number'];
						$Examiniers[$__reportsQuery[$ReportGenerally]['examiner']]['number'] = $ExaminiererNumber['Examiner']['number'];
					}

					// Suchoptionen für den zweiten Prüfer

					$ExaminiererNumber = array();

					$optionsExaminer = array(
									'conditions' => array(
											'Examiner.discription' => $__reportsQuery[$ReportGenerally]['examinierer2'],
											'Examiner.testingcomp_id' => $this->Session->read('conditionsTestingcomps')
											),
									'fields' => array(
											'id',
											'number'
											)
									);
					
					if(count($this->Examiner->find('first', $optionsExaminer)) == 1){				
						$ExaminiererNumber = ($this->Examiner->find('first', $optionsExaminer));
						$reportsQuery[$_key][$__key][$ReportGenerally]['examiner2_number'] = $ExaminiererNumber['Examiner']['number'];
						$Examiniers[$__reportsQuery[$ReportGenerally]['examinierer2']]['number'] = $ExaminiererNumber['Examiner']['number'];
					}						
				}
			}
		}

		$Examiniers_help = array();
		
		// Der Key im Examiner Array muss getauscht werden, Name gegen Nummer
		foreach($Examiniers as $_key => $_Examiniers){
			$Examiniers_help[$_Examiniers['number']]['number'] = $_Examiniers['number'];
			$Examiniers_help[$_Examiniers['number']]['name'] = $_key;
		}
		
		$Examiniers = $Examiniers_help;		

		// Nun werden alle Wartezeiten den Examinieres Array zugeordnet
		foreach($Examiniers as $_key => $_Examiniers){

			foreach($reportsQuery as $__key => $__reportsQuery){
			
									
				$ReportGenerally = 'Report'.ucfirst($__key).'Generally';

				foreach($__reportsQuery as $___key => $___reportsQuery){
					
					$times = array(
								'reportnumber_id'			=> $___reportsQuery[$ReportGenerally]['reportnumber_id'],
								'verfahren'					=> ucfirst($__key),
								'number'					=> $waitingQuery[$__key][$___reportsQuery[$ReportGenerally]['reportnumber_id']],
								'reason'					=> null,
								'time_start'				=> $___reportsQuery[$ReportGenerally]['time_start'],
								'waiting_start'				=> $___reportsQuery[$ReportGenerally]['waiting_start'],
								'waiting_stop'				=> $___reportsQuery[$ReportGenerally]['waiting_stop'],								
								'waiting_start_timestamp'	=> strtotime($___reportsQuery[$ReportGenerally]['waiting_start']),
								'waiting_stop_timestamp'	=> strtotime($___reportsQuery[$ReportGenerally]['waiting_stop']),
								'waiting_start_year'		=> strftime('%Y', strtotime($___reportsQuery[$ReportGenerally]['waiting_start'])),
								'waiting_start_month'		=> strftime('%m', strtotime($___reportsQuery[$ReportGenerally]['waiting_start'])),
								'waiting_start_day'		=> strftime('%d', strtotime($___reportsQuery[$ReportGenerally]['waiting_start'])),
								'waiting_start_hour'		=> strftime('%H', strtotime($___reportsQuery[$ReportGenerally]['waiting_start'])),
								'waiting_start_min'		=> strftime('%M', strtotime($___reportsQuery[$ReportGenerally]['waiting_start'])),
								'waiting_stop_year'		=> strftime('%Y', strtotime($___reportsQuery[$ReportGenerally]['waiting_stop'])),
								'waiting_stop_month'		=> strftime('%m', strtotime($___reportsQuery[$ReportGenerally]['waiting_stop'])),
								'waiting_stop_day'		=> strftime('%d', strtotime($___reportsQuery[$ReportGenerally]['waiting_stop'])),
								'waiting_stop_hour'		=> strftime('%H', strtotime($___reportsQuery[$ReportGenerally]['waiting_stop'])),
								'waiting_stop_min'		=> strftime('%M', strtotime($___reportsQuery[$ReportGenerally]['waiting_stop'])),
								'waiting_time'				=> round(((strtotime($___reportsQuery[$ReportGenerally]['waiting_stop']) - strtotime($___reportsQuery[$ReportGenerally]['waiting_start'])) / 60) / 60, 2),
								'active'					=> 1
								);

					// Wenn der key mit den Prüfern übereinstimmt findet die Zuordnung statt
					if($_key == $___reportsQuery[$ReportGenerally]['examiner_number'] || $_key == $___reportsQuery[$ReportGenerally]['examiner2_number']){
						$r = 'r'.$___reportsQuery[$ReportGenerally]['reportnumber_id'];
						$Examiniers[$_key]['data'][$r] = $times;
						
						// Der (falls vorhandene) Eintrag aus der Datenbank wird mit dem neu generierte verglichen
						if(isset($XmlFromDatabase['waitings'][$_key]['data'][$r])){
							
							if($XmlFromDatabase['waitings'][$_key]['data'][$r]['waiting_start_timestamp'] == $Examiniers[$_key]['data'][$r]['waiting_start_timestamp'] && $XmlFromDatabase['waitings'][$_key]['data'][$r]['waiting_stop_timestamp'] == $Examiniers[$_key]['data'][$r]['waiting_stop_timestamp']){
								$Examiniers[$_key]['data'][$r]['active'] = $XmlFromDatabase['waitings'][$_key]['data'][$r]['active'];
								$Examiniers[$_key]['data'][$r]['reason'] = $XmlFromDatabase['waitings'][$_key]['data'][$r]['reason'];
							}
							else{
								$Examiniers[$_key]['data'][$r]['reference'] = __('Attention, this waiting time has changed.', true);
							}
						}
					}

					$times = array();
				}
			}
		}

		// Es werden die Timestamps auf überschneidung verglichen
		// die erste Anfangswartezeit und letzte Endwartezeit ermittelt
		$FirstStartWaiting = PHP_INT_MAX;
		$LastStopWaiting = 0;
		
		foreach($Examiniers as $_key => $_Examiniers){
			foreach($_Examiniers['data'] as $__key => $data){
				$__keyArray = array();
				$___keyArray = array();
				
				foreach($_Examiniers['data'] as $___key => $__Examiniers){
					if($data['reportnumber_id'] != $__Examiniers['reportnumber_id']){
						if($__Examiniers['waiting_start_timestamp'] >= $data['waiting_start_timestamp'] 
						&& $__Examiniers['waiting_start_timestamp'] < $data['waiting_stop_timestamp']){
							$__keyArray[] = $__key;
							$___keyArray[] = $___key;
						}
						if($__Examiniers['waiting_start_timestamp'] < $FirstStartWaiting){
							$FirstStartWaiting = $__Examiniers['waiting_start_timestamp'];
						}
						if($__Examiniers['waiting_stop_timestamp'] >= $LastStopWaiting){
							$LastStopWaiting = $__Examiniers['waiting_stop_timestamp'];
						}
					}
				}

				$Examiniers[$_key]['data'][$___key]['overlap'] = $__keyArray;
				$Examiniers[$_key]['data'][$__key]['overlap'] = $___keyArray;
			}
			
		}
		
		$Examiniers['generally']['FirstStartWaiting'] = date('d.m.Y', $FirstStartWaiting);
		$Examiniers['generally']['LastStopWaiting'] = date('d.m.Y', $LastStopWaiting);

		// Feld für bestellte Arbeitsleistung hinzufügen
		if(!isset($XmlFromDatabase['waitings']['generally']['ordered_work']) && $XmlFromDatabase['waitings']['ordered_work'] == ''){
			$Examiniers['generally']['ordered_work'] = __(null, true);
		}
		else {
			$Examiniers['generally']['ordered_work'] = $XmlFromDatabase['waitings']['generally']['ordered_work'];
		}
		
		// hier wird aus dem Array ein XML-Obejkt gemacht und gespeichert
		$this->Xml->SaveWaitingXml($Examiniers,$orderID);

		$breads = $this->Navigation->BreadForReport($projectID,$orderID,0);

		$breads[] = array(
						'discription' => __('Waiting times',true),
						'controller' => 'invoices',
						'action' => 'waitings/'.$projectID.'/'.$orderID,
						'pass' => null,
						);	

		// das alles wird in einer Session gespeichert
		$this->Session->write('Waitings', $Examiniers);
		$this->set('Examiniers', $Examiniers);
		$this->set('breads', $breads);
		$this->set('menues', null);
		$this->set('projectID', $projectID);
		$this->set('orderID', $orderID);
		
	}
	
	public function change() {

		$projectID = $this->Sicherheit->Numeric($this->request->params['pass'][0]);
		$orderID = $this->Sicherheit->Numeric($this->request->params['pass'][1]);
		$key = $this->Sicherheit->OnlyLetterNumber($this->request->params['pass'][2]);
		$key = $this->request->params['pass'][2];
		$x = $this->Sicherheit->Numeric($this->request->params['pass'][3]);
		$i = $this->Sicherheit->Numeric($this->request->params['pass'][4]);
		$examinierer = $this->request->data['examinierer'];

		$Waitings = $this->Session->read('Waitings');		
		if(($Waitings[$examinierer]['data'][$key]['active']) == 1){
			$Waitings[$examinierer]['data'][$key]['active'] = 0;
		}
		else{
			$Waitings[$examinierer]['data'][$key]['active'] = 1;
		}

		$this->Xml->SaveWaitingXml($Waitings,$orderID);

		// das alles wird in einer Session gespeichert
		$this->Session->write('Waitings', $Waitings);
		
		$this->set('value', 'test');
 		$this->render(null, 'modal');
	}	
	
	public function remark() {
		
		$elementid = explode('_',$this->request->data['elementid']);
		
		$orderID = $elementid[1];
		$reportID = $elementid[2];
		$xx = $elementid[3];
		$ii = $elementid[4];
		
		// muss noch getestet werden
		$Waitings = $this->Session->read('Waitings');

		$x = 0;
		foreach($Waitings as $_key => $_Waitings){
					
			if($x == $xx){
				$Waitings[$_key]['data'][$reportID]['reason'] = $this->request->data['remark'];
				break;
			}
			
			$x++;
		}
		$this->Xml->SaveWaitingXml($Waitings,$orderID);

		$this->Session->write('Waitings', $Waitings);
		$this->set('remark', $this->request->data['remark']);
 		$this->render(null, 'modal');
	}
		
	public function workreason() {
				
		// muss noch getestet werden
		$Waitings = $this->Session->read('Waitings');
		$Waitings['generally']['ordered_work'] = $this->request->data['remark']; 

		$this->Xml->SaveWaitingXml($Waitings,$this->Session->read('orderURL'));

		$this->Session->write('Waitings', $Waitings);

		$this->set('remark', $this->request->data['remark']);
 		$this->render(null, 'modal');
	}	
	public function waitingtest() {
		
		//Wenn noch kein Prüfer angegeben ist, wird abgebrochen
		if($this->request->data['examinierer1'] == '' && $this->request->data['examinierer2'] == ''){
			die();
		}
		if($this->request->data['date'] == ''){
			die();
		}

		$this->loadModel('Examiner');
		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = -1;
		
		// Alle Prüfverfahren holen
		$testingmethods = $this->Testingmethod->find('list',array(
										'fields' => array(
											'value'
										)
									)
								);

		$id = $this->Sicherheit->Numeric($this->request->data['id']);
/*
		pr($this->request->data['date']);
		pr($this->request->data['examinierer1']);
		pr($this->request->data['examinierer2']);
*/
		
		$examinierer = array();
		$examinierer[1] = null;
		$examinierer[2] = null;

		if($this->request->data['examinierer1'] != ''){
			$examinierer[1]['examinierer']['name'] = $this->request->data['examinierer1'];
		}
		if($this->request->data['examinierer2'] != ''){
			$examinierer[2]['examinierer']['name'] = $this->request->data['examinierer2'];
		}
		
		
		foreach($examinierer as $_key => $_examinierer){
			$examinierers = $this->Examiner->find('first',array(
										'fields' => array(
											'id'
										),
										'conditions' => array(
											'discription' => $_examinierer['examinierer']['name']
										)
									)
								);
			$examinierer[$_key]['examinierer']['id'] =	$examinierers['Examiner']['id'];		
			$examinierer[$_key]['examinierer']['date'] =	$this->request->data['date'];		
		}
		$this->render(null, 'modal');
	}
	
	public function export() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$orderID = $this->request->projectvars['VarsArray'][3];

				$this->loadModel('Topproject');
				$this->loadModel('Testingmethod');
				$this->loadModel('Order');

				$data = array();

				$label = array(
					'topproject_id' => array(
								'label' => 'Projekt',
								'model' => 'Topproject',
								'option' => $projectID,
								'field' => 'projektname'
							),
					'testingmethod_id' => array(
								'label' => 'Prüfverfahren',
								'model' => 'Testingmethod',
								'option' => 'thisData',
								'field' => 'verfahren'
							),
					'order_id' => array(
								'label' => 'Auftrag',
								'model' => 'Order',
								'option' => 'thisData',
								'field' => 'auftrags_nr'
							),
					'number' => array(
								'label' => 'Anzahl'
							),
					'dimension' => array(
								'label' => 'Abmessung'
							),
					'position' => array(
								'label' => 'Leistung',
							),
					'price' => array(
								'label' => 'Preis'
							),
					'price_total' => array(
								'label' => 'Gesamtpreis'
							),
					'created' => array(
								'label' => 'Erstellt'
							),
					'modified' => array(
								'label' => 'Bearbeitet'
							)
					);

				$Models = array('Statisticsstandard','Statisticsadditional');
				$options = array('conditions' => array(
										'order_id' => $orderID,
										'topproject_id' => $projectID,
									)
								);

				foreach($Models as $_Models){
					$this->loadModel($_Models);
					$x = count($data);
					foreach($this->$_Models->find('all', $options) as $_key => $_Statistics){
						foreach($_Statistics as $__key => $__Statistics){
								foreach($label as $__key => $__label){
									if(isset($__Statistics[$__key])){
										$_data = $__Statistics[$__key];
									}
									else {
										$_data = ' ';
									}

									// Wenn die Daten zu einem ID geholt werden sollen
									if(isset($__label['model']) && $__label['model'] != '' && $_data != ''){
										$this->$__label['model']->recursive = -1;

										if($__label['option'] == 'thisData'){
											$condi = $_data;
										}
										else{
											$condi = $__label['option'];
										}

										$option = array(
											'fields' => array($__label['field']),
											'conditions' => array(
												'id' => $condi
											)
										);
										$select = $this->$__label['model']->find('first',$option);
										if(count($select) > 0){
											$_data = ($select[$__label['model']][$__label['field']]);
										}
									}

									$data[$x][utf8_decode($__label['label'])] = $_data;
								}
						}
					$x++;
					}
				}
				$this->Csv->exportCsv($data);
	}	


	public function printinvoice() {

   		$this->layout = 'pdf';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$part = $this->request->projectvars['VarsArray'][5];

		$partArray = null;
		
		if($part > 0){
			$partArray = array('laufende_nr' => $part);
		}

		// Auftragsdaten laden
		$this->loadModel('Order');
		$this->loadModel('Statisticsstandard');
		$this->loadModel('Statisticsadditional');

		$optionsOrder = array(
			'conditions' => array(
				'id' => $orderID
					)
				);

		$optionsStatistic = array(
			'conditions' => array(
				'topproject_id' => $projectID,
				'order_id' => $orderID,
				'deaktiv !=' => 1,
				$partArray,
				)
			);

		$optionsAdditional = array(
			'conditions' => array(
				'topproject_id' => $projectID,
				'order_id' => $orderID,
				$partArray,
				)
			);

		$this->Order->recursive = -1;
		$this->Invoice->recursive = -1;

		$thisOrder = $this->Order->find('first', $optionsOrder);
		$thisStatisticsstandard = $this->Statisticsstandard->find('all', $optionsStatistic);
		$thisStatisticsadditional = $this->Statisticsadditional->find('all', $optionsAdditional);

		$thisStatistics = array();

		// Die beiden Arrays werden zusammengeschrieben
		foreach($thisStatisticsstandard as $_thisStatisticsstandard){
			$thisStatistics[] =  $_thisStatisticsstandard['Statisticsstandard'];
		}
		foreach($thisStatisticsadditional as $_thisStatisticsadditional){
			$thisStatistics[] =  $_thisStatisticsadditional['Statisticsadditional'];
		}

		// wenn nix da ist
		if($thisOrder['Order']['id'] == null){die('no data');}

		$invoices = array();
pr($thisOrder);
die();
		$line = $this->Search->LineForInvoice($thisOrder);

 		$InvoiceWidth = array(
						'lv_pos' => 25,
						'leistungs_nr' => 25,
						'bezeichnung' => 90,
						'datum' => 35,
//						'pruefer' => 25,
						'menge' => 16,
						'einheit' => 16,
						'einzel' => 16,
						'gesamt' => 16,
						'geprueft' => 11,
					);


		$invoicesNew = array();

		// die Leistungen
		$x = 0;
		$totalprice = 0;
		
		foreach($thisStatistics as $_thisStatistics){
			$optionsInvoice = array(
								'fields' => array(
									'me',
									'position_2',
									'kurzposition',
									'kurztext'
								),
								'conditions' => array(
								'id' => $_thisStatistics['position']
								)
							);
			
			$thisInvoice = $this->Invoice->find('first', $optionsInvoice);

			// das Array aus der Datenbank für die PDF umschreiben
			foreach($InvoiceWidth as $_key => $_InvoiceWidth){
				if($_key == 'lv_pos'){
					$invoicesNew[$x][$_key] = $thisInvoice['Invoice']['kurzposition'];
				}
				if($_key == 'leistungs_nr'){
					$invoicesNew[$x][$_key] = $thisInvoice['Invoice']['position_2'];
				}
				if($_key == 'bezeichnung'){
					$invoicesNew[$x][$_key] = $thisInvoice['Invoice']['kurztext'];
				}
				if($_key == 'datum'){
					$datum = ' ';
					if(isset($_thisStatistics['date']) && $_thisStatistics['date'] != ''){
						$datum = $_thisStatistics['date'];
					}
					$invoicesNew[$x][$_key] = $datum;
				}
				
			if($_key == 'menge'){
				$invoicesNew[$x][$_key] = $_thisStatistics['number'];
			}
			if($_key == 'einheit'){
				$invoicesNew[$x][$_key] = $thisInvoice['Invoice']['me'];
			}
			if($_key == 'einzel'){
				$invoicesNew[$x][$_key] = number_format(round($_thisStatistics['price'],2),2,',','.');
			}
			if($_key == 'gesamt'){
				$totalprice = $totalprice + round($_thisStatistics['price_total'],2);
				$invoicesNew[$x][$_key] = number_format(round($_thisStatistics['price_total'],2),2,',','.');
			}
			if($_key == 'geprueft'){
				$invoicesNew[$x][$_key] = ' ';
			}
		}
	$x++;

	}

	$totalprice = number_format($totalprice,2,',','.');
	$invoices = array();
	$invoices = $invoicesNew;
	
	$this->set('InvoiceWidth', $InvoiceWidth);
	$this->set('invoices', $invoices);
	$this->set('totalprice', $totalprice);
	$this->set('line', $line);
	$this->set('part', $part);
	$this->set('orderID', $orderID);
	$this->set('thisOrder', $thisOrder);
	}
}

