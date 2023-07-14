<?php
App::uses('AppController', 'Controller');
/**
 * Orders Controller
 *
 * @property Order $Order
 */
class OrdersController extends AppController {

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data','Csv','Image','ExpeditingTool','Drops','Cascades');
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

                $noAjaxIs = 0;
		$noAjax = array('pdf','test','image');

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

		$this->loadModel('User');
		$this->loadModel('Topproject');
		$this->Autorisierung->Protect();
		//$this->Navigation->ajaxURL();
		$this->Lang->Choice();
		$this->Lang->Change();

		$this->request->lang = $this->Lang->Discription();

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

/**
 * index method
 *
 * @return void
 */
	public function index() {

		// Fileuploaddaten löschen
		$this->Session->write('uploadError', null);
		$this->Session->write('uploadOk', null);
		$this->Session->write('CsvError', null);
		$this->Session->write('orderKat', null);

		$this->Order->recursive = -1;

		$projectID = $this->request->projectID;
		$orderKat = $this->request->orderKat;
		$orderID = $this->request->orderID;

		$OpenInClose = null;

		// Wenn diese Variable fehlt, werden die offenen Aufträge angezeigt
			if($this->request->orderID == 1){
				$CloseOpenOrder = 1;
				$CloseOpenOrderDisc = __('Show open orders', true);

				$OpenInClose = array('Order.kks' => $this->Order->find('list',array(
					'fields' => array('kks'),
					'conditions' => array(
							'Order.topproject_id' => $projectID,
							'Order.status' => 1,
							'Order.deleted' => 0
							)
						)
					)
				);
			}

			if($this->request->orderID == 0){
				$CloseOpenOrder = 0;
				$CloseOpenOrderDisc = __('Show closed orders', true);

				// Wenn nur geschlossen Aufträge angezeigt werden sollen
				// Werden halb offenen Aufträge anhand des Status aus dem Array gelöscht
				$OpenInClose = array('Order.kks' => $this->Order->find('list',array(
					'fields' => array('kks'),
					'conditions' => array(
							'Order.topproject_id' => $projectID,
							'Order.status' => 0,
							'Order.deleted' => 0
							)
						)
					)
				);
			}

		$menue = $this->Navigation->NaviMenue(null);
		$breads = $this->Navigation->Breads(null);

		$this->set('projectID', $projectID);

		// Rückbau der Unterscheidungen von Revision und Laufender Betrieb
		$this->request->orderKat = 1;

		if($this->request->orderKat != 0 && !isset($this->request['data']['Search']['searching'])){

			$orderKat = $this->request->orderKat;

			$this->Session->write('orderKat', $orderKat);

			// für die Links
			if($CloseOpenOrder == 0){$CloseOpenOrderLink = 1;}
			else{$CloseOpenOrderLink = 0;}

			if($orderKat == 2){

				$conditionsArray = array(
					'Order.topproject_id' => $projectID,
					'Order.status' => array($CloseOpenOrder),
					'Order.revision' => '1',
					'Order.deleted' => 0
					);

				$breads[] = array(
					'discription' => __('Revision', true),
        		    'controller' => 'orders',
    		        'action' => 'index',
	    	        'pass' => $projectID.'/2/'.$CloseOpenOrder
					);

				$menue[] = array(
					'haedline' => null,
					'class' => 'ajax',
					'controller' => 'orders',
					'discription' => __('Ongoing process', true),
					'actions' => 'index',
					'params' => $projectID.'/1/'.$CloseOpenOrder
					);

				$countArray = array('Order.revision' => '1');
			}

			elseif($orderKat == 1){
				$conditionsArray = array(
					'Order.topproject_id' => $projectID,
					'Order.status' => array($CloseOpenOrder),
//					'Order.laufender_betrieb' => '1'
					'Order.deleted' => 0
					);

			$breads[] = array(
				'discription' => __('Orders', true),
        	    'controller' => 'orders',
    	        'action' => 'index',
	            'pass' => $projectID.'/1'
				);

			$menue[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => 'orders',
				'discription' => __('Revision', true),
				'actions' => 'index',
				'params' => $projectID.'/2'
				);

			$countArray = array('Order.laufender_betrieb' => '1');

			}

				$conditionsArray = array(
					'Order.topproject_id' => $projectID,
					'Order.status' => array($CloseOpenOrder),
//					'Order.laufender_betrieb' => '1'
					'Order.deleted' => 0
					);

			$this->paginate = array(
    	    	'conditions' => $conditionsArray,
    	    	'limit' => 25,
				'fields' => array('Order.auftrags_nr'),
				'group' => array('Order.auftrags_nr'),
    	    	'order' => array('id' => 'desc')
    		);

			$Order = $this->paginate('Order');

			// Die IDs zu den Auftragsnummern
			foreach($Order  as $_key => $_Order){

				$countOrderID = $this->Order->find('first',array(
					'fields' => array('id'),
					'conditions' => array(
						'Order.topproject_id' => $projectID,
						'Order.auftrags_nr' => $_Order['Order']['auftrags_nr'],
						'Order.deleted' => 0
						)
					)
				);
				$Order[$_key]['Order']['id'] = $countOrderID['Order']['id'];
			}

			$countOrderOption = array(
					'fields' => array('DISTINCT auftrags_nr'),
					'conditions' => array(
						'Order.topproject_id' => $projectID,
						'Order.status' => array($CloseOpenOrder),
						'Order.revision' => 1,
						'Order.deleted' => 0
						)
					);

			$countOrder = $this->Order->find('all',$countOrderOption);

			if($orderKat == 1){
				$haedline = __('Orders', true);
			}
			if($orderKat == 2){
				$haedline = __('Revision', true);
			}

			// Die KKS Nummer zu den Aufträgen
			foreach($Order as $_key =>$_Order){
				$OrderKKS = $this->Order->find('all',array(
					'fields' => array('id','kks','status'),
					'conditions' => array(
						'Order.auftrags_nr' => $_Order['Order']['auftrags_nr'],
						$OpenInClose,
						'Order.deleted' => 0
						),
					'order' => array('kks' => 'DESC')
					)
				);

				// Die IDs zu den KKS-Nummern holen
				foreach($OrderKKS as $_keyKKS => $_OrderKKS){

					$OrderKKSId = $this->Order->find('first',array(
						'fields' => array('id'),
						'conditions' => array(
							'Order.id' => $_OrderKKS['Order']['id'],
							'Order.deleted' => 0
							)
						)
					);

					$OrderKKS[$_keyKKS]['Order']['id'] = $OrderKKSId['Order']['id'];
				}


				$Order[$_key]['Order']['kks'] = $OrderKKS;
			}

			// Rückbau Laufender Betrieb Revision DEKRA
			$this->request->projectvars['VarsArray'][1] = 1;

			$SettingsArray = array();
			$SettingsArray['addlink'] = array('discription' => __('Add new order',true), 'controller' => 'orders','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
			$SettingsArray['upload'] = array('discription' => __('Upload new order',true), 'controller' => 'orders','action' => 'create', 'terms' => $this->request->projectvars['VarsArray']);
			$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

			$breads[1]['controller'] = 'orders';

			// den Parameter für laufender Betrieb oder Revision anfügen
			$menue[0]['params'] = $menue[0]['params'].'/'.$orderKat;
			$this->set('SettingsArray', $SettingsArray);
			$this->set('countOrder', $countOrder);
			$this->set('CloseOpenOrderDisc', $CloseOpenOrderDisc);
			$this->set('CloseOpenOrder', $CloseOpenOrder);
			$this->set('CloseOpenOrderLink', $CloseOpenOrderLink);
			$this->set('orderKat', $orderKat);
			$this->set('haedline', $haedline);
			$this->set('countOrder', count($countOrder));
			$this->set('orders', $Order);
			$this->set('breads', $breads);
			$this->set('menues', $menue);


			$this->render('/Orders/orders');
		}

		// Wenn die Seite über die Schnellsuche aufgerufen wurde
		if(isset($this->request['data']['Search']['searching'])){
			$SearchQuery = $this->request['data']['Search']['searching'];

			$searchterm = 'auftrags_nr';

			if($this->request['data']['Search']['hidden'] == 1){
				$searchterm = 'auftrags_nr';
			}
 			if($this->request['data']['Search']['hidden'] == 2){
				$searchterm = 'kks';
			}

			$this->paginate = array(
    	    	'conditions' => array(
								'Order.topproject_id' => $projectID,
								'Order.'.$searchterm .' LIKE' => $SearchQuery,
								'Order.deleted' => 0
								),
    	   		'limit' => 10,
    	   		'order' => array('id' => 'desc')
    		);

			$Order = $this->paginate('Order');

			$countOrder = $this->Order->find('count',array(
					'conditions' => array(
						'Order.topproject_id' => $projectID,
						'Order.auftrags_nr LIKE' => $SearchQuery,
						'Order.deleted' => 0
						)
					)
				);

			// Test ob laufender Betrieb oder Revision
			if($Order[0]['Order']['revision'] == 1){
			$orderKat = 2;
			$haedline = __('Revision', true);
			$breads[] = array(
				'discription' => __('Revision', true),
        	    'controller' => 'orders',
    	        'action' => 'index',
	            'pass' => $projectID.'/2'
				);
			}
			elseif($Order[0]['Order']['laufender_betrieb'] == 1){
			$orderKat = 1;
			$haedline = __('Ongoing process', true);
			$breads[] = array(
				'discription' => __('Ongoing process', true),
        	    'controller' => 'orders',
    	        'action' => 'index',
	            'pass' => $projectID.'/1'
				);
			}

			// Die KKS Nummer zu den Aufträgen
			foreach($Order as $_key =>$_Order){
				$OrderKKS = $this->Order->find('all',array(
					'fields' => array('kks'),
					'conditions' => array(
						'Order.auftrags_nr' => $_Order['Order']['auftrags_nr'],
						'Order.deleted' => 0
						)
					)
				);
				$Order[$_key]['Order']['kks'] = $OrderKKS;
			}

			$breads[1]['controller'] = 'orders';

			$this->set('countOrder', $countOrder);
			$this->set('CloseOpenOrderDisc', $CloseOpenOrderDisc);
			$this->set('CloseOpenOrder', $CloseOpenOrder);
			$this->set('haedline', $haedline);
			$this->set('orderKat', $orderKat);
			$this->set('breads', $breads);
			$this->set('menues', $menue);
			$this->set('orders', $Order);
			$this->render('/Orders/orders');
		}

		$menue = array();
		$SettingsArray = array();

		$breads[1]['controller'] = 'orders';

		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads', $breads);
		$this->set('menues', $menue);
	}

	public function overview() {

		// Fileuploaddaten löschen
		$this->Session->write('uploadError', null);
		$this->Session->write('uploadOk', null);
		$this->Session->write('CsvError', null);

		$this->Order->recursive = -1;

		$projectID = $this->request->projectID;
		$orderKat = $this->request->orderKat;
		$orderID = $this->request->orderID;

		if($projectID != $this->request->projectID){
			die('Error');
		}

		$menue = $this->Navigation->NaviMenue(null);
		$breads = $this->Navigation->Breads(null);

		// Wenn diese Variable fehlt, werden die offenen Aufträge angezeigt
			if($this->request->reportID == 1){
				$CloseOpenOrder = 1;
				$CloseOpenOrderDisc = __('Show open orders', true);
			}
			if($this->request->reportID == 0){
				$CloseOpenOrder = 0;
				$CloseOpenOrderDisc = __('Show closed orders', true);
			}

			// für die Links
			if($CloseOpenOrder == 0){$CloseOpenOrderLink = 1;}
			else{$CloseOpenOrderLink = 0;}

		// Die passende Auftragsnummer zur ID holen
		$countOrder = $this->Order->find('first',array(
				'fields' => array(
					'auftrags_nr'
				),
				'conditions' => array(
					'Order.topproject_id' => $projectID,
					'Order.id' => $orderID,
					'Order.deleted' => 0
					)
				)
			);

			/*
		// Die passende Aufträge zur Auftragsnummer holen
		$Orders = $this->Order->find('all',array(
				'conditions' => array(
					'Order.topproject_id' => $projectID,
					'Order.auftrags_nr' => $countOrder['Order']['auftrags_nr'],
					'Order.status' => array($CloseOpenOrder)
					)
				)
			);

		// Wenn es keinen offenen Aufträge mehr gibt
		if($this->request->reportID != 1 && count($Orders) == 0){
			$Orders = $this->Order->find('all',array(
				'conditions' => array(
					'Order.topproject_id' => $projectID,
					'Order.auftrags_nr' => $countOrder['Order']['auftrags_nr'],
					'Order.status' => 1
					)
				)
			);

			$CloseOpenOrder = 0;
			$CloseOpenOrderDisc = __('Show open orders', true);
			$CloseOpenOrderLink = 0;

			$this->request->reportID = 1;
			$this->set('noopenorder',1);
		}
			 */

		$options = array(
			'Order.topproject_id' => $projectID,
			'Order.auftrags_nr' => $countOrder['Order']['auftrags_nr'],
			'Order.status' => array($CloseOpenOrder),
			'Order.deleted' => 0
		);

		if($this->Order->find('count', array('conditions'=>$options)) == 0 && $this->request->reportID != 1)
		{
			$options['Order.status'] = 1;

			$CloseOpenOrder = 0;
			$CloseOpenOrderDisc = __('Show open orders', true);
			$CloseOpenOrderLink = 0;

			$this->request->reportID = 1;
			$this->set('noopenorder',1);
		}

		// Test ob laufender Betrieb oder Revision
		$out = $this->Navigation->OrderKat($orderKat);
		$haedline = $out['haedline'];
		$breads[] = $out['breads'][0];

		// Breadcrumb für die aktuelle Seite
		if($orderKat == 1){
			$breads[] = array(
				'discription' => __('Orders', true),
        		'controller' => 'orders',
				'action' => 'index',
				'pass' => $projectID.'/'.$orderKat
			);
		}

		if($orderKat == 2){
			$breads[] = array(
				'discription' => __('Revision', true),
        		'controller' => 'orders',
				'action' => 'index',
				'pass' => $projectID.'/'.$orderKat
			);
		}

		$breads[] = array(
				'discription' => __('Order', true).' '.$countOrder['Order']['auftrags_nr'],
        		'controller' => 'orders',
				'action' => 'overview',
				'pass' => $projectID.'/'.$orderKat.'/'.$orderID
			);

		$menue[] = array(
			'haedline' => null,
			'class' => 'ajax',
			'controller' => 'orders',
			'discription' => __('Revision', true),
			'actions' => 'index',
			'params' => $projectID.'/2'
			);

		$menue[] = array(
			'haedline' => null,
			'class' => 'ajax',
			'controller' => 'orders',
			'discription' => __('Ongoing process', true),
			'actions' => 'index',
			'params' => $projectID.'/1'
			);

		$breads[1]['controller'] = 'orders';

		// Überschrift ergänzen
		$haedline .= ' ' . __('Order', true) . ' '.$countOrder['Order']['auftrags_nr'];

		$SettingsArray = array();
		$SettingsArray['addlink'] = array('discription' => __('Add new order',true), 'controller' => 'orders','action' => 'add', 'terms' => array($projectID,$orderKat));
		$SettingsArray['upload'] = array('discription' => __('Upload new order',true), 'controller' => 'orders','action' => 'create', 'terms' => array($projectID,$orderKat));
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => array(0,$projectID,0),);

		$Orders = $this->paginate('Order', $options);
//		echo '<div style="display: none" id="dumpdiv">'; var_dump($Orders = $this->paginate('Order', $options)); echo '</div>';
		$this->set('SettingsArray', $SettingsArray);
		$this->set('CloseOpenOrderDisc', $CloseOpenOrderDisc);
		$this->set('CloseOpenOrder', $CloseOpenOrder);
		$this->set('CloseOpenOrderLink', $CloseOpenOrderLink);
		$this->set('countOrder', count($Orders));
		$this->set('haedline', $haedline);
		$this->set('projectID', $projectID);
		$this->set('orderKat', $orderKat);
		$this->set('orderID', $orderID);
		//$this->set('Orders', $Orders);
		$this->set('Orders', $Orders);
		$this->set('breads', $breads);
		$this->set('menues', $menue);
	}

	public function reports() {

		// Fileuploaddaten löschen
		$this->Session->write('uploadError', null);
		$this->Session->write('uploadOk', null);
		$this->Session->write('CsvError', null);

		$this->Order->recursive = -1;

		$projectID = $this->request->projectID;
		$orderKat = $this->request->orderKat;
		$orderID = $this->request->orderID;
		$reportID = $this->request->reportID;

		// Wenn die Übersicht für einen Prüfbericht aufgerufen wird
		if(isset($this->request->data['report_id'])){
			$report_id = $this->request->data['report_id'];
		}

		// Wenn die Übersicht für den gesamten Auftrag aufgerufen wird
		if($reportID == 0){
			$orderAllID = $this->request->reportID;
		}

		if($projectID != $this->request->projectID){
			die('Error');
		}

		// die Prüfbericht zu dem Auftrag holen
		$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $orderID));
		$orders = $this->Order->find('first', $options);

		// alle betroffenen Prüfbericht holen
		if(isset($report_id)){
			$optionsReportnumber = array('conditions' => array('Reportnumber.id' => $report_id));
		}

		elseif(isset($orderAllID)){
			$options = array(
						'fields' => array('id'),
						'conditions' => array(
								'Order.auftrags_nr' => $orders['Order']['auftrags_nr']
							)
						);
			$orderIDs = $this->Order->find('list', $options);
			$optionsReportnumber = array('conditions' => array('Reportnumber.order_id' => $orderIDs));
		}

		else {
			$optionsReportnumber = array('conditions' => array('Reportnumber.order_id' => $orderID));
		}

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;
		$reports = $this->Reportnumber->find('all', $optionsReportnumber);

		$allEvalution = array();
		$allEvalutionArray = array();
		$allTestingmethods = array();
		$WeldArray = array();
		$neTestArray = array();
		$neTestArray['result'] = '';

		foreach($reports as $_reports){
			$allTestingmethods[$_reports['Reportnumber']['testingmethod_id']] = $_reports['Reportnumber']['testingmethod_id'];
			$allEvalution[$_reports['Reportnumber']['testingmethod_id']][] = $_reports['Reportnumber']['id'];
		}

		// alle betroffenen Prüfverfahren holen
		$this->loadModel('Testingmethod');
		$optionsTestingmethod = array('conditions' => array('Testingmethod.id' => $allTestingmethods));
		$this->Testingmethod->recursive = -1;
		$testingmethods = $this->Testingmethod->find('all', $optionsTestingmethod);

		// die Prüfergebnisse anhand der Prüfverfahren holen
		foreach($testingmethods as $_testingmethods){
			$ReportEvaluation = 'Report' . ucfirst($_testingmethods['Testingmethod']['value']) . 'Evaluation';
			$this->loadModel($ReportEvaluation);
			$option = array('conditions' => array($ReportEvaluation.'.reportnumber_id' => $allEvalution[$_testingmethods['Testingmethod']['id']]));
			$allEvalutionArray[ucfirst($_testingmethods['Testingmethod']['value'])] = $this->$ReportEvaluation->find('all', $option);
		}

		foreach($allEvalutionArray as $_key => $_allEvalutionArray){
			foreach($_allEvalutionArray as $__key => $__allEvalutionArray){
				foreach($__allEvalutionArray as $___key => $___allEvalutionArray){
					if($___allEvalutionArray['description'] == ''){
						$discription = 'no_discription';
					}
					else{
						$discription = $___allEvalutionArray['description'];
					}

				// Die Prüfberichtsbezeichnung in das Array einfügen
				foreach($reports as $_reports){
					if($_reports['Reportnumber']['id'] == $___allEvalutionArray['reportnumber_id']){
						$___allEvalutionArray['number'] = $_reports['Reportnumber']['number'];
						$___allEvalutionArray['year'] = $_reports['Reportnumber']['year'];
						break;
					}
				}

				$WeldArray[$_key][$___allEvalutionArray['reportnumber_id']][$discription][] = $___allEvalutionArray;

				}
			}
		$neTestArray['result'] = '';
		}

		$this->set('WeldArray',$WeldArray);

		if(isset($report_id)){
			$this->render('/Reportnumbers/reports','modal');
		}
		else {
			$this->render('reports','modal');
		}
	}

	public function status($id = null) {

		$this->layout = 'modal';

		// Variablen überprüfen
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $orderID));
		$this->Order->recursive = 1;
		$orders = $this->Order->find('first', $options);

		if(isset($this->request->data['Order']) && $this->request->is('put')) {

			if($orders['Order']['status'] == 1){
				$status = 0;
			}
			else {
				$status = 1;
			}

			$updateArray['Order']['id'] = $orderID;
			$updateArray['Order']['status'] = $status;

			// Wenn der Auftrag wieder geöffnet wird, werden alle Abrechnungen gelöscht
			if($status == 0){
				$updateArray['Order']['invoices'] = null;
			}

			$updateArray['Order']['id'] = $orderID;
			$updateArray['Order']['status'] = $status;
			$updateArray['Order']['invoices'] = null;

			if($this->Order->save($updateArray)){
				$this->Order->Reportnumber->updateAll(
								array(
									'Reportnumber.status' => 2
								),
								array(
									'Reportnumber.order_id' => $orders['Order']['id']
								)
							);

				if($status != 0){
					$this->Session->setFlash(__('The order has been locked.'));
					$logArray = array('status' => 'Auftrag geschlossen');
					$this->Autorisierung->Logger($id,$logArray);
				}
				elseif($status == 0) {
					$this->Session->setFlash(__('The order has been reopened.'));
					$logArray = array('status' => 'Auftrag  erneut geöffnet');
					$this->Autorisierung->Logger($id,$logArray);
				}
			}

			$orders = $this->Order->find('first', $options);

			$forwarding = array_pop($this->request->params['pass']);

			if($forwarding == 1){
				$FormName = array('controller' => 'reportnumbers', 'action' => 'index', 'terms' => implode('/',$this->request->projectvars['VarsArray']));
			}
			if($forwarding == 2){
				$FormName = array('controller' => 'developments', 'action' => 'orders', 'terms' => $orders['Development'][0]['id']);
			}
			$this->set('FormName',$FormName);
			$this->set('afterChange',1);
		}

		$this->request->data = $orders;

		if($orders['Order']['status'] == 1){
			$message = __('Will you you change the state to open.', true);
		}
		if($orders['Order']['status'] == 0){
			$message = __('Will you you change the state to close.', true);
		}


		$SettingsArray = array();

		$this->set('SettingsArray',$SettingsArray);
		$this->set('message',$message);
	}

	public function search() {
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$reportID = $this->request->projectvars['VarsArray'][4];
		$reportnumberID = $this->request->projectvars['VarsArray'][5];
		$evalId = $this->request->projectvars['VarsArray'][6];
		$weldedit = $this->request->projectvars['VarsArray'][7];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		$testingreportsCount = 0;

		$fields = $this->Data->SearchUniversal('searchorder');

		$this->set('reportName', @$SearchFields['report_id']['Result'][$reportID]);
		$this->set('SettingsArray', null);
		$this->set('projectID', $projectID);
		$this->set('reportID', $reportID);
		$this->set('orderID', $orderID);
	}

	public function quicksearch() {
		// Wenn ein Prüfbericht über die Direktsuche aufgerufen wird

		if(isset($this->request->data['Order']['search']) && $this->request->data['Order']['search'] != ''){

			$this->loadModel('Reportnumber');
			$this->Reportnumber->recursive = 0;

			$orderKat = null;
			$projectID = null;
			$orderID = null;
			$message = null;
			$reportnumber = array();
			$number = array();
			$termNum = array();

			// Wenn der Term eine Nummer ist, wird versucht eine Protok0llnummer zufinden
			if(is_numeric($this->request->data['Order']['search'])) {
				$options = array('conditions' => array(
													'Reportnumber.number' => $this->request->data['Order']['search'],
													'Reportnumber.topproject_id' => $this->request->projectID
												)
											);
				$reportnumber[0] = $this->Reportnumber->find('first', $options);
			}
			// Wenn der Term keine Nummer ist
			else{
				// Wird erst geschaut ob der Term aus der Autocomplett kommt
				// Stimmt das Suchmuster wird der Prüfbericht gesuchr
				$term = explode('-',$this->request->data['Order']['search']);

				if(is_numeric(trim($term[0]))){
					$number[0] = $term[0];
					$termNum = explode(' ',$term[1]);
				}
				if(isset($termNum[0]) && is_numeric(trim($termNum[0]))){
					$number[1] = $termNum[0];
				}
				if(count($number) == 2){
					$options = array('conditions' => array(
														'Reportnumber.number' => $number[1],
														'Reportnumber.year' => $number[0],
														'Reportnumber.topproject_id' => $this->request->projectID
													)
												);
					$reportnumber[0] = $this->Reportnumber->find('first', $options);
				}
				else {

					$menue = $this->Navigation->NaviMenue(null);
					$breads = $this->Navigation->Breads(null);
					$message = '<p class="error">'. __('Please enter the searched report`s running number', true) .'</p>';

					$this->set('message', $message);
					$this->set('reportnumbers', $reportnumber);
					$this->set('orderKat', $orderKat);
					$this->set('projectID', $projectID);
					$this->set('orderID', $orderID);
					$this->set('menues', $menue);
					$this->set('breads', $breads);

					return;
				}


			}

			if($this->Reportnumber->find('count', $options) > 0){
			// die Variablen für den View
			if($reportnumber[0]['Order']['revision'] == 1){
				$orderKat = 2;
			}
			elseif($reportnumber[0]['Order']['laufender_betrieb'] == 1){
				$orderKat = 1;
			}

			$this->Session->write('orderKat', $orderKat);
			$projectID = $reportnumber[0]['Reportnumber']['topproject_id'];
			$orderID = $reportnumber[0]['Reportnumber']['order_id'];
			}

		}

		$menue = $this->Navigation->NaviMenue(null);
		$breads = $this->Navigation->Breads(null);

		$this->set('message', $message);
		$this->set('reportnumbers', $reportnumber);
		$this->set('orderKat', $orderKat);
		$this->set('projectID', $projectID);
		$this->set('orderID', $orderID);
		$this->set('menues', $menue);
		$this->set('breads', $breads);
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

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$id = $this->request->projectvars['VarsArray'][3];

		$this->loadModel('Equipment');
		$this->Equipment->recursive = 2;
		$optionsEquipment = array('conditions' => array('Equipment.id' => $equipment));
		$equipments = $this->Equipment->find('first',$optionsEquipment);

		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}

		$this->Session->write('orderURL', $id);
		$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
//		$this->Order->recursive = 2;
		$orders = $this->Order->find('first', $options);

		$headline = $equipments['EquipmentType']['Topproject']['projektname'].' > '.$equipments['EquipmentType']['discription'].' > '.$equipments['Equipment']['discription'].' > '.$orders['Deliverynumber']['topproject_id'].$orders['Deliverynumber']['equipment_id'].$orders['Deliverynumber']['equipment_type_id'].$orders['Deliverynumber']['order_id'];

		$options = array(
			'conditions' => array(
								'Reportnumber.topproject_id' => $projectID,
								'Reportnumber.order_id' => $id
							)
						);

		$optionsPaginate['Reportnumber.order_id'] = $id;
		$optionsPaginate['Reportnumber.topproject_id'] = $projectID;

		$this->paginate = array(
    		'conditions' => array($optionsPaginate),
			'order' => array('id' => 'desc'),
			'limit' => 25
		);

		$this->Order->Reportnumber->recursive = 0;

		$testingreports = $this->paginate('Reportnumber');

		$this->request->data = $orders;
		$arrayData = $this->Xml->DatafromXml('Order','file',null);

		$SettingsArray = array();
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'orders','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['upload'] = array('discription' => __('Upload new order',true), 'controller' => 'orders','action' => 'create', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['editlink'] = array('discription' => __('Edit',true), 'controller' => 'orders','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('headline', $headline);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('testingreports', $testingreports);
		$this->set('settings', $arrayData['settings']);
		$this->set('locale', $this->Lang->Discription());
		$this->set('orders', $orders);
	}

	public function select() {
		$this->set('select', array(1 => 'Allgemeiner Auftrag'));
		$this->render(null, 'modal');
	}

	public function create() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];

		$this->layout = 'modal';

		if($this->Session->check('testedCsvArray')){

			$testedCsvArray = $this->Session->read('testedCsvArray');

			foreach($testedCsvArray['Order'] as $_key => $_order){
				pr($_order);
				$this->Order->create();

				$_order['topproject_id'] = $projectID;
				$_order['testingcomp_id'] = $this->Auth->user('testingcomp_id');

				if ($this->Order->save($_order)) {
					$this->loadModel('Deliverynumber');
					$this->request->data['Deliverynumber']['topproject_id'] = $this->request->projectvars['VarsArray'][0];
					$this->request->data['Deliverynumber']['equipment_type_id'] = $this->request->projectvars['VarsArray'][1];
					$this->request->data['Deliverynumber']['equipment_id'] = $this->request->projectvars['VarsArray'][2];
					$this->request->data['Deliverynumber']['order_id'] = $this->Order->getInsertID();
					$this->request->data['Deliverynumber']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
					$this->Deliverynumber->create();
					$this->Deliverynumber->save($this->request->data['Deliverynumber']);

					$deliverynumber['Order']['id'] = $this->Order->getInsertID();
					$deliverynumber['Order']['equipment_type_id'] = $equipmentType;
					$deliverynumber['Order']['equipment_id'] = $equipment;
					$deliverynumber['Order']['deliverynumber'] = $this->request->projectvars['VarsArray'][0].$this->request->projectvars['VarsArray'][2].$this->request->projectvars['VarsArray'][1].$this->Order->getInsertID();
					$this->Order->save($deliverynumber);

					$this->Autorisierung->Logger($this->Order->getInsertID(),$_order);
					$this->Session->setFlash(__('The order has been saved'));

					$FormName['controller'] = 'equipments';
					$FormName['action'] = 'view';
						$Terms =
						$projectID.'/'.
						$equipmentType.'/'.
						$equipment;

					$FormName['terms'] = $Terms;

					$this->set('FormName',$FormName);
					$this->set('saveOK', 1);
					$this->set('reportnumberID', null);
					$this->set('evalutionID', null);
				} else {
					$this->Session->setFlash(__('The order could not be saved. Please, try again.'));
				}
			}

/*


			if ($this->Order->save($this->Session->read('testedCsvArray'))) {

				$this->loadModel('Deliverynumber');
				$this->request->data['Deliverynumber']['topproject_id'] = $this->request->projectvars['VarsArray'][0];
				$this->request->data['Deliverynumber']['equipment_type_id'] = $this->request->projectvars['VarsArray'][1];
				$this->request->data['Deliverynumber']['equipment_id'] = $this->request->projectvars['VarsArray'][2];
				$this->request->data['Deliverynumber']['order_id'] = $this->Order->getInsertID();
				$this->request->data['Deliverynumber']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
//				$this->request->data['Deliverynumbers']['number'];
				$this->Deliverynumber->create();
				$this->Deliverynumber->save($this->request->data['Deliverynumber']);

				// Die Dekranummer wird zu Suchzwecken mit eingefügt
				$deliverynumber['Order']['id'] = $this->Order->getInsertID();
				$deliverynumber['Order']['equipment_type_id'] = $equipmentType;
				$deliverynumber['Order']['equipment_id'] = $equipment;
				$deliverynumber['Order']['deliverynumber'] = $this->request->projectvars['VarsArray'][0].$this->request->projectvars['VarsArray'][2].$this->request->projectvars['VarsArray'][1].$this->Order->getInsertID();
				$this->Order->save($deliverynumber);

				$this->Session->write('orderURL', $this->Order->getInsertID());
				$this->Autorisierung->Logger($this->Order->getInsertID(),$this->request->data['Order']);
				$this->Session->setFlash(__('The order has been saved'));

				$FormName['controller'] = 'equipments';
				$FormName['action'] = 'view';
				$Terms =
					$projectID.'/'.
					$equipmentType.'/'.
					$equipment;

				$FormName['terms'] = $Terms;

				$this->set('FormName',$FormName);
				$this->set('saveOK', 1);
				$this->set('reportnumberID', null);
				$this->set('evalutionID', null);
			} else {
				$this->Session->setFlash(__('The order could not be saved. Please, try again.'));
			}
*/
			$this->Session->delete('testedCsvArray');
		}

		if(isset($this->request->data['Order']['file']))
		{

			$mimetype = $this->request->data['Order']['file']['type'];

			switch($mimetype) {
				case 'application/pdf':
					$this->loadModel('OrderPdf');

					$text = $this->OrderPdf->getText($this->request->data['Order']['file']['tmp_name']);
					break;
				case 'application/vnd.ms-excel':

					$CsvArray = $this->Csv->getUploadExcelCsv($this->request->data['Order']['file']['tmp_name']);

					if(count($CsvArray) > 100) break;

					if(isset($CsvArray) && count($CsvArray) > 0)  $this->Session->write('uploadetCsvArray',$CsvArray);
					break;
			}
		}


		if(isset($this->request->data['show_data'])){

			$arrayData = $this->Xml->DatafromXml($projectID,'file',null,'orders' . DS . 'templates' . DS . 'upload' . DS);

			if($this->Session->check('uploadetCsvArray')){

				$uploadetCsvArray = $this->Session->read('uploadetCsvArray');
				$head = array_shift($uploadetCsvArray);
				// Leerzeichen entfernen
				// damit die Werte mit denen aus der XML übereinstimmen
				$orders = array();
				$deleted_orders = 0;

				foreach($head as $_key => $_head){
					$head[$_key] = trim(str_replace(' ','',$_head));
				}
				foreach($uploadetCsvArray as $_key => $_uploadetCsvArray){

					$this->Order->recursive = -1;
					$existOrder = $this->Order->find('first',array('conditions' => array('Order.auftrags_nr' => $_uploadetCsvArray['auftrags_nr'])));
					if(count($existOrder) > 0) {
						unset($orders['Order'][$_key]);
					$deleted_orders++;
					}

					$orders['Order'][$_key] = $_uploadetCsvArray;
				}

				$orders_kompakt = array();

				foreach($orders['Order'] as $_key => $_orders){
//					$orders_kompakt[$_orders['auftrags_nr']] = $_orders;
					foreach($_orders as $__key => $__orders){
						if($__orders != ''){
							@$orders_kompakt['Order'][$_orders['auftrags_nr']][$__key] = $orders_kompakt['Order'][$_orders['auftrags_nr']][$__key] . '; ' . $__orders;
						}
					}
				}


				$this->set('deleted_orders',$deleted_orders);
				$this->set('orders',$orders);
				$this->set('arrayData',$arrayData);
				$this->set('uploadetCsvArray',array('head' => $head,'body' => $uploadetCsvArray));
				$this->Session->delete('uploadetCsvArray');
				$this->Session->write('testedCsvArray',$orders);
			} else {
				$this->Session->setFlash(__('The uploaded file is not compatible.'));
			}
		}
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {

		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];

		// Daten einlesen
		$arrayData = $this->Xml->DatafromXml('Order','file',null);

		$this->loadModel('Cascade');
		$this->loadModel('Testingcomp');


		$optionsCascade = array('conditions' => array('Cascade.id' => $cascadeID));
		$cascade = $this->Cascade->find('first',$optionsCascade);

		$headline = ($cascade['Topproject']['projektname'].' > '.$cascade['Cascade']['discription']);

		$developments = $this->Order->Development->find('list');
		$emailaddress = $this->Order->Emailaddress->find('list',array('order' => array('email'), 'fields'=>array('id','email')));

		if($this->Auth->user('Roll.id') > 4){
			$Options['conditions']['id'] = $this->Auth->user('testingcomp_id');
		}

		$Options['order'] = array('name');
		$Options['fields'] = array('id','name');
		$testingcomps = $this->Order->Testingcomp->find('list',$Options);

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		if (isset($this->request->data['Order']) && $this->request->is('post')) {

			$this->request->data['Order']['topproject_id'] = $projectID;
			$this->request->data['Order']['cascade_id'] = $cascadeID;
//			$this->request->data['Order']['block'] = $equipments['EquipmentType']['Topproject']['projektname'];
			$this->request->data['Order']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
			$this->request->data = $this->Data->ChangeDropdownData($this->request->data ,$arrayData,array('Order'));

			$CascadeGetParentList = $this->Navigation->CascadeGetParentList($cascadeID,array());
			$CascadeGetParentListInsert['Cascade'] = array();

			if(count($CascadeGetParentList) > 0){
				foreach($CascadeGetParentList as $_key => $_CascadeGetParentList){
					$CascadeGetParentListInsert['Cascade'][] = $_CascadeGetParentList['Cascade']['id'];
				}
			}

			$this->request->data['Cascade'] = $CascadeGetParentListInsert;

			$this->Order->create();

			if ($this->Order->save($this->request->data)) {

				$c_o_id = array('order_id' =>$this->Order->getLastInsertID(),'cascade_id' => $this->request->data['Cascade']['Cascade']);

//				$this->Cascades->SaveSupplierByAddOrders($c_o_id);

//				$this->Order->CascadesOrder->create();
//				$this->Order->CascadesOrder->save($c_o_id);

				$t_o_id = array('order_id' =>$this->Order->getLastInsertID(),'testingcomp_id' => $this->request->data['Testingcomp']['Testingcomp']);
//				$this->Order->OrdersTestingcomp->create();
//				$this->Order->OrdersTestingcomp->save($t_o_id);

				$this->Autorisierung->Logger($this->Order->getInsertID(),$this->request->data['Order']);
				$this->Session->setFlash(__('The order has been saved'));

				$FormName['controller'] = 'reportnumbers';
				$FormName['action'] = 'index';
				$Terms = $projectID.'/'.$cascadeID.'/'.$t_o_id ['order_id'];

				$FormName['terms'] = $Terms;

				$this->set('FormName',$FormName);
				$this->set('saveOK', 1);
				$this->set('reportnumberID', null);
				$this->set('evalutionID', null);
			} else {
				$this->Session->setFlash(__('The order could not be saved. Please, try again.'));
			}
		}

		$this->request->data = $this->Data->DropdownData(array('Order'),$arrayData,$this->request->data);
		$this->request->data['Order']['status'] = 0;
		$this->request->data['Testingcomp']['selected'] = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
		$this->request->data['Emailaddress']['selected'] = array();

		$this->request->data = $this->Cascades->SelectTechicalPlaceDataForAddOrders($this->request->data);

//		$this->request->data['Order']['examination_object'] = $cascade['Cascade']['discription'];

		$SettingsArray = array();
//		$SettingsArray['upload'] = array('discription' => __('Upload new order',true), 'controller' => 'orders','action' => 'create', 'terms' => $this->request->projectvars['VarsArray']);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		if(isset($this->request->data['texts'])) {
			// PDF nummeriert Zeilen in absteigender Reihenfolge
			$this->set('texts', $this->request->data['texts']);
		} else {
			$this->set('texts', null);
		}

		$this->set('headline', $headline);
		$this->set('developments', $developments);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('locale', $this->Lang->Discription());
		$this->set('settings', $arrayData['settings']);
		$this->set('orderoutput', $arrayData['orderoutput']);
		$this->set(compact('testingcomps','emailaddress'));
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

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		$this->Cascades->SaveSupplierByAddOrders($orderID);

		$options = array(
					'conditions' =>
						array(
							'Order.'.$this->Order->primaryKey=>$id,
							'Order.topproject_id'=>$this->Autorisierung->ConditionsTopprojects()
						),
    				'contain' =>
						array('Testingcomp')
					);

//		$this->Order->recursive = -1;
//		$this->Order->contain();
		$oldOrder = $this->Order->find('first', $options);

		$this->loadModel('Testingcomp');
		$this->loadModel('Roll');

		$arrayData = $this->Xml->DatafromXml('Order','file',null);


		$developments = $this->Order->Development->find('list');
		$this->set('developments', $developments);

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		if (!$this->Order->exists($id)) {
			$this->render('close');
			return;
		}

		$EmailList = $this->Order->Emailaddress->find('list',array('order' => array('email'), 'fields'=>array('id','email','roll')));

		if($this->Auth->user('Roll.id') > 4){
			$Options['conditions']['id'] = $this->Auth->user('testingcomp_id');
		}

		$Options['order'] = array('name');
		$Options['fields'] = array('id','name');
		$testingcomps = $this->Order->Testingcomp->find('list',$Options);

		$this->Roll->recursive = -1;

		$emailaddress = array();

		foreach($EmailList as $_key => $_data){
			$Roll = $this->Roll->find('first',array('conditions' => array('Roll.id' => $_key)));
			foreach($_data as $__key => $__data){
				$emailaddress[$__key] = $__data . ' (' . $Roll['Roll']['name']. ')';
			}
		}


		$headline = null;

		if(empty($oldOrder)) {
			$this->render('close');
			return;
		}


		if (isset($this->request->data['Order']) && $this->request->is('put')) {

			if(isset($this->request->data['Order']['topproject_id'])) unset($this->request->data['Order']['topproject_id']);

//			$this->request->data = $this->Data->DropdownData(array('Order'),$arrayData,$this->request->data);
			$this->request->data = $this->Data->ChangeDropdownData($this->request->data,$arrayData,array('Order'));

			// Array korrigieren
			foreach($this->request->data['Order'] as $_key => $_order){
				if($_key == 0 && is_array($_order)){
					$this->request->data['Order'] = array_merge($this->request->data['Order'],$_order);
					unset($this->request->data['Order'][0]);
				}
			}

			// Key und Value in den Dropdownwerten tauschen
			foreach($this->request->data['Order'] as $_key => $_order){
				if(isset($this->request->data['Dropdowns']['Order'][$_key][$_order]) && !empty($arrayData['settings']->Order->$_key->select->model)){
					$this->request->data['Order'][$_key] =
					$this->request->data['Dropdowns']['Order'][$_key][$_order];
				}
			}

			unset($this->request->data['Dropdowns']);
			unset($this->request->data['CustomDropdownFields']);
			unset($this->request->data['DropdownInfo']);
			unset($this->request->data['JSON']);

			$CascadeGetParentList = $this->Navigation->CascadeGetParentList($oldOrder['Order']['cascade_id'],array());
			$CascadeGetParentListInsert['Cascade'] = array();
			if(count($CascadeGetParentList) > 0){
				foreach($CascadeGetParentList as $_key => $_CascadeGetParentList){
					$CascadeGetParentListInsert['Cascade'][] = $_CascadeGetParentList['Cascade']['id'];
				}

			$this->request->data['Cascade'] = $CascadeGetParentListInsert;

			}

			if ($this->Order->save($this->request->data)) {
                              //  $this->Session->delete('Order.form');
//				$this->Session->write('orderURL', $id);
				$this->Autorisierung->Logger($id,$this->request->data['Order']);
				$this->Session->setFlash(__('The order has been saved'));

				// durch die unterschiedlichen Spaltennamen, müssen die Werte Expeditin und Order hiermit sycronisiert werden
				$this->ExpeditingTool->OrderExpeditingMerge(0,$orderID,'Supplier');

				$FormName['controller'] = 'reportnumbers';
				$FormName['action'] = 'index';
				$Terms =
					$projectID.'/'.
					$cascadeID.'/'.
					$orderID
					;
				$FormName['terms'] = $Terms;

				$this->set('saveOK',1);
				$this->set('FormName',$FormName);

			} else {
				$this->Session->setFlash(__('The order could not be saved. Please, try again.'));
			}
		} else {

			$options = array('conditions' => array(
					'Order.' . $this->Order->primaryKey => $id,
//					'Deliverynumber.testingcomp_id' => $this->Auth->user('testingcomp_id')
				)
			);

			$this->Order->recursive = 1;

			$order = $this->Order->find('first', $options);
//			$order = $this->Data->TestincompSelected($order);
			$order = $this->Data->BelongsToManySelected($order,'Order','Testingcomp',array('OrdersTestingcomps','testingcomp_id','order_id'));
			$order = $this->Data->BelongsToManySelected($order,'Order','Emailaddress',array('OrdersEmailadresses','emailadress_id','order_id'));
			$order = $this->Data->BelongsToManySelected($order,'Order','Development',array('OrdersDevelopments','development_id','order_id'));

			if(count($order) == 0){
				$this->render('close');
				return;
			}

			$this->request->data = $order;

			$this->request->data = $this->Data->GeneralDropdownData(array('Order'), $arrayData, $this->request->data);

			if($this->request->data['Order']['status'] > 0){
				$FormName = array('controller' => 'reportnumbers', 'action' => 'index');
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);
				$this->Session->setFlash(__('The order is closed'));
				$this->set('SettingsArray', null);
				$this->set('reportnumberID', 1);
				$this->set('evalutionID', 0);
				$this->set('FormName', $FormName);
				$this->render('close');
			}
		}

		$menueArray = array(
			'general' => array(
				'haedline' => __('Actions', true),
				'controller' => 'orders',
				'discription' => array('Show Orders'),
				'actions' => array('index'),
				'params' => array($projectID),
			));

		// Falls nach dem Fileupload und neu anlegen eines Auftrages, Fehler in der Session
		// gespeichert wurden, wir diese hier an das View gesendet.
		if($this->Session->read('uploadError')){
			$this->set('uploadError', $this->Session->read('uploadError'));
//			$this->Session->write('uploadError', null);
		}

		$arrayDropdown = array();
		$options = null;

		// Daten fÃ¼r mÃ¶gliche vorhandene Dropdownfelder und Multiselects holen
		$this->request->data = $this->Data->DropdownData(array('Order'),$arrayData,$this->request->data);

		$this->request->data['Order']['remarks'] = str_replace("\r\n", " ", $this->request->data['Order']['remarks']);
		$this->request->data['Order']['remarks'] = str_replace("\r", " ", $this->request->data['Order']['remarks']);
		$this->request->data['Order']['remarks'] = str_replace("\n", " ", $this->request->data['Order']['remarks']);

//		$this->request->data = $this->Data->GeneralDropdownData(array('Order'), $arrayData, $this->request->data);

		// Bestellnummer in dem Array speichern

		$SettingsArray = array();
//		$SettingsArray['viewlink'] = array('discription' => __('view',true), 'controller' => 'orders','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
//		$SettingsArray['upload'] = array('discription' => __('Upload new order',true), 'controller' => 'orders','action' => 'create', 'terms' => $this->request->projectvars['VarsArray']);
//		$SettingsArray['editlink'] = array('discription' => __('Edit',true), 'controller' => 'orders','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching for order',true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

		$this->set('headline', $headline);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('locale', $this->Lang->Discription());
		$this->set('settings', $arrayData['settings']);
		$this->set('orderoutput', $arrayData['orderoutput']);
		$this->set('arrayData', $arrayData);
		$this->set(compact('testingcomps','emailaddress'));
	}

	public function move($id = null) {
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$equipmentType = $this->request->projectvars['VarsArray'][1];
		$equipment = $this->request->projectvars['VarsArray'][2];
		$orderID = $this->request->projectvars['VarsArray'][3];
		$id = $this->request->projectvars['VarsArray'][3];

		$GlobalReportNumbers = array();
		$GlobalReportNumbers['Topproject.subdivision >'] = 1;

		$GlobalReportNumbers['Topproject.id'] = $this->Autorisierung->ConditionsTopprojects();

		if(Configure::read('GlobalReportNumbers') == true){
			$GlobalReportNumbers['Topproject.id'] = $this->Autorisierung->ConditionsTopprojects();
		}
		if(Configure::read('GlobalReportNumbers') == false){
			$GlobalReportNumbers['Topproject.id'] = $projectID;
		}

		$this->Order->recursive = 2;
		$order = $this->Order->find('first',array('conditions' => array('Order.id' => $orderID)));
		$this->Order->Topproject->recursive = -1;
		$topproject = ($this->Order->Topproject->find('list',array(
														'fields'=> array('id','projektname'),
														'order'=> array('projektname'),
														'conditions' => array(
															$GlobalReportNumbers,
															'status' => 0
														)
													)
												)
											);

		if ($this->request->is('post') && isset($this->request->data['Order'])) {
			if(isset($this->request->data['Order']['topproject_id']) && $this->request->data['Order']['topproject_id'] != ''){

				$GlobalReportNumbersAll = array('topproject_id' => $this->Autorisierung->ConditionsTopprojects());
				$GlobalReportNumbersOne = array('topproject_id' => $this->request->data['Order']['topproject_id']);
				$GlobalReportNumbersOneVar = $this->request->data['Order']['topproject_id'];

				if(Configure::read('GlobalReportNumbers') == true){
					$GlobalReportNumbersAll = array('topproject_id' => $this->Autorisierung->ConditionsTopprojects());
					$GlobalReportNumbersOne = array('topproject_id' => $this->request->data['Order']['topproject_id']);
					$GlobalReportNumbersOneVar = $this->request->data['Order']['topproject_id'];
				}
				if(Configure::read('GlobalReportNumbers') == false){
					$GlobalReportNumbersAll = array('topproject_id' => $projectID);
					$GlobalReportNumbersOne = array('topproject_id' => $projectID);
					$GlobalReportNumbersOneVar = $projectID;
				}

				$equipmenttypes = $this->Order->Deliverynumber->EquipmentType->find(
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

				if(isset($this->request->data['Order']['equipment_type_id']) && $this->request->data['Order']['equipment_type_id'] != ''){
					$equipments = $this->Order->Deliverynumber->Equipment->find(
														'list',array(
														'fields'=> array('id','discription'),
														'conditions' => array(
															$GlobalReportNumbersAll,
															$GlobalReportNumbersOne,
															'equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
															'status' => 0
														)
													)
												);

					$this->set('equipments', $equipments);

					if(isset($this->request->data['Order']['equipment_id']) && $this->request->data['Order']['equipment_id'] != ''){
						$thisequipment = $this->Order->Deliverynumber->Equipment->find(
															'list',array(
															'fields'=> array('id','discription'),
															'conditions' => array(
																$GlobalReportNumbersAll,
																$GlobalReportNumbersOne,
																'equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
																'id' => $this->request->data['Order']['equipment_id'],
																'status' => 0
															)
														)
													);

						$this->set('thisequipment', $thisequipment);
					}

					if(isset($this->request->data['Order']['thisequipment']) && $this->request->data['Order']['thisequipment'] != ''){
						$order_old = array(
										'order_id' => $order['Order']['id'],
										'topproject_id' => $order['Topproject']['id'],
										'equipment_type_id' => $order['Deliverynumber']['EquipmentType']['id'],
										'equipment_id' => $order['Deliverynumber']['Equipment']['id']
										);
						$order_new = array(
										'order_id' => $order['Order']['id'],
										'topproject_id' => $GlobalReportNumbersOneVar,
										'equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
										'equipment_id' => $this->request->data['Order']['equipment_id']
										);

						$this->loadModel('DevelopmentData');
						$this->loadModel('Reportnumber');

						$this->Order->updateAll(
							array(
								'Order.topproject_id' => $GlobalReportNumbersOneVar,
								'Order.equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
								'Order.equipment_id' => $this->request->data['Order']['equipment_id'],
							),
							array('Order.id' => $order['Order']['id'])
						);
						$this->Order->Deliverynumber->updateAll(
							array(
								'Deliverynumber.topproject_id' => $GlobalReportNumbersOneVar,
								'Deliverynumber.equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
								'Deliverynumber.equipment_id' => $this->request->data['Order']['equipment_id'],
							),
							array('Deliverynumber.order_id' => $order['Order']['id'])
						);
						$this->DevelopmentData->updateAll(
							array(
								'DevelopmentData.topproject_id' => $GlobalReportNumbersOneVar,
								'DevelopmentData.equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
								'DevelopmentData.equipment_id' => $this->request->data['Order']['equipment_id'],
							),
							array('DevelopmentData.order_id' => $order['Order']['id'])
						);
						$this->Reportnumber->recursive = -1;
						$this->Reportnumber->updateAll(
							array(
								'Reportnumber.topproject_id' => $GlobalReportNumbersOneVar,
								'Reportnumber.equipment_type_id' => $this->request->data['Order']['equipment_type_id'],
								'Reportnumber.equipment_id' => $this->request->data['Order']['equipment_id'],
							),
							array('Reportnumber.order_id' => $order['Order']['id'])
						);

						$FormName = array();
						$FormName['controller'] = 'reportnumbers';
						$FormName['action'] = 'index';
						$FormName['terms'] = $GlobalReportNumbersOneVar.'/'.$this->request->data['Order']['equipment_type_id'].'/'.$this->request->data['Order']['equipment_id'].'/'.$order['Order']['id'];

						$this->Session->setFlash(__('Order ' . $order['Order']['auftrags_nr']) .' ' .__('has been moved to this location.',true));
						$this->set('FormName', $FormName);
						$this->set('saveOK', 1);

					}
				}
			}
		}

		$SettingsArray = array();
//		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'orders','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);

//pr($order);
//		$this->set('headline', $headline);
		$this->set('order', $order);
		$this->set('topproject', $topproject);
		$this->set('SettingsArray', $SettingsArray);
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

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];


		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}

		$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
		$this->Order->recursive = 0;
		$order = $this->Order->find('first', $options);
		$order['Order']['deleted'] = 1;
		$this->request->data = $order;

		$headline = $order['Order']['auftrags_nr'];
		$SettingsArray = array();

		$this->set('headline', $headline);
		$this->set('SettingsArray', null);
		$this->set('message', __('Will you delete this order?', true));
		$this->set('SettingsArray',$SettingsArray);

		if($order['Order']['status'] > 0){
				$FormName = array('controller' => 'reportnumbers', 'action' => 'index');
				$FormName['terms'] = $projectID.'/'.$cascadeID.'/'.$orderID;

				$this->Session->setFlash(__('The order is closed'));
				$this->set('SettingsArray', null);
				$this->set('reportnumberID', 1);
				$this->set('evalutionID', 0);
				$this->set('FormName', $FormName);
				$this->render('close');
		}

		if(isset($this->request->data['Order']) && $this->request->is('put')) {
			if ($this->Order->save($this->request->data)) {

				$this->loadModel('Reportnumber');
				$this->Reportnumber->recursive = -1;

				$this->Reportnumber->updateAll(
					array('Reportnumber.delete' => 1),
					array('Reportnumber.order_id' => $this->request->data['Order']['id'])
				);

				$this->Session->setFlash(__('The order has been delete.'));
				$logArray = array('delete' => 'Auftrag gelöscht');

				$this->ExpeditingTool->SupplierDelete($orderID);
				$this->Autorisierung->Logger($id,$logArray);

				$FormName = array('controller' => 'cascades', 'action' => 'index', 'terms' => $projectID.'/'.$cascadeID);

				$this->set('FormName',$FormName);
				$this->set('afterChange',1);
			}
			else {
				$this->Session->setFlash(__('Order was not deleted.'));
				$logArray = array('delete' => 'Fehler beim löschen');
				$this->Autorisierung->Logger($id,$logArray);
			}
		}
	}

	public function files($id = null) {

		$ImageExtension = array('jpg','jpeg','png','gif');
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		$this->Order->recursive = 0;
		$orders = $this->Order->find('first', array('conditions' => array('Order.id' => $orderID)));
		$this->loadModel('Orderfile');
		$orderfiles = $this->Orderfile->find('all', array('conditions' => array('Orderfile.order_id' => $orderID)));
		$savePath = Configure::read('order_folder') . $orderID . DS ;

		if(isset($_FILES['file']) && count($_FILES['file']) > 0){
			$this->request->data['Order'] = $_FILES;
		}

		if(isset($this->request['data']['Order'])){

			// Infos zur Datei
			$fileinfo = pathinfo($this->request['data']['Order']['file']['name']);

			$Microtime = microtime();
			$Microtime = str_replace(array('.',' '),'_',$Microtime);

	 		$saveFile = $orderID.'_'.$Microtime . '_' . uniqid() . '.' . $fileinfo['extension'];

			// Wenn nicht vorhanden Ordner erzeugen
			if(!file_exists($savePath)){
				$dir = new Folder($savePath, true, 0755);
			}

			move_uploaded_file($this->request['data']['Order']['file']["tmp_name"],$savePath.'/'.$saveFile);

			$dataFiles = array(
					'order_id' => $orders['Order']['id'],
					'file' => $saveFile,
					'basename' => $fileinfo['basename'],
					'user_id' => $this->Auth->user('id')
				);

			$this->Orderfile->save($dataFiles);
			$orderfiles = $this->Orderfile->find('all', array('conditions' => array('Orderfile.order_id' => $orderID)));

			foreach($orderfiles as $_key => $_Orderfile){
				$savePath = Configure::read('order_folder') . $_Orderfile['Orderfile']['order_id'] . DS . $_Orderfile['Orderfile']['file'];
				if(file_exists($savePath) == true){
					$orderfiles[$_key]['Orderfile']['file_exists'] = true;
				} else {
					$orderfiles[$_key]['Orderfile']['file_exists'] = false;
				}
			}

			$lastArrayURL = $this->Session->read('lastArrayURL');

			$FormName = array();
			$FormName['controller'] = $lastArrayURL['controller'];
			$FormName['action'] = $lastArrayURL['action'];
			$FormName['terms'] = implode('/',$lastArrayURL['pass']);

			$this->set('FormName', $FormName);
			$this->set('saveOK',1);
		}

		foreach($orderfiles as $_key => $_Orderfile){
			$savePath = Configure::read('order_folder') . $_Orderfile['Orderfile']['order_id'] . DS . $_Orderfile['Orderfile']['file'];
			if(file_exists($savePath) == true){
				$orderfiles[$_key]['Orderfile']['file_exists'] = true;
			} else {
				$orderfiles[$_key]['Orderfile']['file_exists'] = false;
			}
		}

		$SettingsArray = array();
//		$this->set('validationErrors', $this->Reportnumber->getValidationErrors($arrayData, $reportnumbers));		$this->set('SettingsArray', $SettingsArray);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('orders', $orders);
		$this->set('orderfiles', $orderfiles);
	}

	public function images($id = null) {

		$ImageExtension = array('jpg','jpeg','png','gif');
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		$this->Order->recursive = 0;
		$orders = $this->Order->find('first', array('conditions' => array('Order.id' => $orderID)));
		$this->loadModel('Orderimage');
		$orderfiles = $this->Orderimage->find('all', array('conditions' => array('Orderimage.order_id' => $orderID)));
//		$savePath = ROOT . DS . 'app' .DS . 'files' . DS .'orders' .  DS . 'files' .DS . $orderID . DS;
		$savePath = Configure::read('order_folder') . $orderID . DS ;

		if(isset($this->request['data']['Order'])){

	 		if($_FILES['data']['error']['Order']['file'] > 0){
	 			$max_upload = (int)(ini_get('upload_max_filesize'));
	 			$this->Session->setFlash(Configure::read('FileUploadErrors.' . $_FILES['data']['error']['Order']['file']) . ' ' . __('Your file is bigger than',true) . ' ' . $max_upload . 'MB');
	 			die();
	 		}

			// Infos zur Datei
			$fileinfo = pathinfo($this->request['data']['Order']['file']['name']);

			$saveFile = $orderID.'_'.time().'.'.$fileinfo['extension'];

			// Wenn nicht vorhanden Ordner erzeugen
			if(!file_exists($savePath)){
				$dir = new Folder($savePath, true, 0755);
			}

			move_uploaded_file($this->request['data']['Order']['file']["tmp_name"],$savePath . $saveFile);

			$dataFiles = array(
					'order_id' => $orders['Order']['id'],
					'file' => $saveFile,
					'name' => $saveFile,
					'user_id' => $this->Auth->user('id')
				);

			$this->Orderimage->save($dataFiles);
			$orderfiles = $this->Orderimage->find('all', array('conditions' => array('Orderimage.order_id' => $orderID)));

			$lastArrayURL = $this->Session->read('lastArrayURL');

			$FormName = array();
			$FormName['controller'] = $lastArrayURL['controller'];
			$FormName['action'] = $lastArrayURL['action'];
			$FormName['terms'] = implode('/',$lastArrayURL['pass']);

			$this->set('FormName', $FormName);
			$this->set('saveOK',1);
		}

	 	foreach($orderfiles  as $_key => $_reportimages){
	 		if(file_exists($savePath.$_reportimages['Orderimage']['name'])){
	 			$orderfiles[$_key]['Orderimage']['imagedata'] = 'data:png;base64,'.$this->Image->Tumbs($savePath,$_reportimages['Orderimage']['name'],300,300,null);
	 		}
	 	}

		$SettingsArray = array();

		$this->set('SettingsArray', $SettingsArray);
		$this->set('orders', $orders);
		$this->set('reportimages', $orderfiles);
	}

	 public function image($id = null) {

	 	$this->layout = 'blank';

	 	$projectID = $this->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
	 	$orderID = $this->request->projectvars['VarsArray'][2];
	 	$id = $this->request->projectvars['VarsArray'][3];

	 	$this->loadModel('Orderimage');
	 	$thisreportimages = $this->Orderimage->find('first', array('conditions' => array('Orderimage.id' => $id)));

	 	$SettingsArray = array();

	 	$this->set('SettingsArray', $SettingsArray);

	 	if(count($thisreportimages) == 1){

			$imagePath = Configure::read('order_folder') . $orderID . DS . $thisreportimages['Orderimage']['name'];

	 		if(file_exists($imagePath))
	 		{
	 			$imageinfo = getimagesize($imagePath);
	 			$imageContent = file_get_contents($imagePath);
	 			$imData = base64_encode($imageContent);
	 			$this->set('mime',$imageinfo['mime']);
	 			$this->set('imagePath',$imagePath);
	 			$this->set('imData',$imData);
	 		}
	 	}
	 }

	public function delfile($id = null) {

		$this->layout = 'modal';

		$this->loadModel('Orderfile');

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][3];


		// zum löschen notwändige Daten aus der Datenbank holen
		$reportfiles = $this->Orderfile->find('first', array('conditions' => array('Orderfile.id' => $id)));
		$this->request->data = $reportfiles;

		// Speicherpfad für den Upload
		$delPath = Configure::read('order_folder') . $reportfiles['Orderfile']['order_id'] . DS . $reportfiles['Orderfile']['file'];

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'orders','action' => 'files', 'terms' => $this->request->projectvars['VarsArray']);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('Reportfiles', $reportfiles);

		if (isset($this->request->data['Orderfile']) && $this->request->is('put')) {

			// Wenn die Dateien existieren werden sie gelöscht
			if(file_exists($delPath)){
				if(@unlink($delPath)){

					// Datenbankeintrag löschen $id
					if($this->Orderfile->delete($id)){
						$this->Session->setFlash(__('File was delete'));

						$FormName = array();
						$FormName['controller'] = 'reportnumbers';
						$FormName['action'] = 'index';

						$Terms = $projectID.'/'.$cascadeID.'/'.$orderID;

						$FormName['terms'] = $Terms;

						$this->set('saveOK',1);
						$this->set('FormName',$FormName);
						}
					}
					else {
						$this->Session->setFlash(__('File was not delete'));
					}
			}
			else {
				if($this->Orderfile->delete($id)){
					$this->Session->setFlash(__('Database record deleted'));
				} else {
					$this->Session->setFlash(__('Database record could not be deleted. Please, try again.'));

				}
			}
		}
	}

	public function save() {
		$this->layout = 'blank';

		$topprojectID = $this->request->projectID;
		$equipmentTypeID = $this->request->equipmentType;
		$equipmentID = $this->request->equipment;
		$id = $this->request->orderID;

		$Model = 'Order';

		if(!$this->$Model->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}

		$this_id = $this->request->data['this_id'];
		$orginal_data = $this->request->data['orginal_data'];
		unset($this->request->data['orginal_data']);

		foreach($this->request->data as $_key => $_data){
			if(!is_array($_data)){
				unset($this->request->data[$_key]);
			}
		}

		$orginalData = $this->$Model->find('first',array('conditions' => array($Model.'.id' => $id)));

		$field_key = key($this->request->data[$Model]);

		if($field_key == '0'){
			$field_key = key($this->request->data[$Model][0]);
			$this_value = $this->request->data[$Model][0][$field_key];
		}
		else {
			$this_value = $this->request->data[$Model][$field_key];
		}

		$last_value = $orginalData[$Model][$field_key];
		$this_field = key($this->request->data[$Model]);
		$last_id_for_radio = $Model . Inflector::camelize($field_key);

		$this->set('this_id',$this_id);
		$this->set('last_value',$last_value);
		$this->set('this_value',$this_value);
		$this->set('last_id_for_radio',$last_id_for_radio);

		if($orginalData[$Model]['status'] > 0 && $this_field != 'status'){
			$this->set('closed',__('The value could not be saved. The record is deactive!'));
			$this->render();
			return;
		}

		if($orginalData[$Model]['deleted'] == 1){
			$this->set('closed',__('The value could not be saved. The record is deleted!'));
			$this->render();
			return;
		}

		$option = array(
				'id' => $id,
				$field_key => $this_value
		);

		if($this->$Model->save($option)) {
			$this->Autorisierung->Logger($id,$this->request->data[$Model]);
			$this->set('success',__('The value has been saved.'));
		}
		else {
			$this->set('error',__('The value could not be saved. Please try again!'));
		}
	}

    public function pdf() {

		$this->layout = 'pdf';

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeId = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$id = $this->request->projectvars['VarsArray'][2];

		$OrderData = $this->Xml->DatafromXml('Order','file',null);
		$this->set('xml_order',$OrderData['settings']);
		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}

		$options = array('conditions'=>array(
			'Order.'.$this->Order->primaryKey=>$id,
			'Order.topproject_id'=>$this->Autorisierung->ConditionsTopprojects()
		));

		$oldOrder = $this->Order->find('first', $options);


		if(empty($oldOrder)) {
			$this->redirect(array('controller' => 'users', 'action' => 'logout'));
		}


		if (isset($this->request->data['Order']) && $this->request->is('put')) {

                    if(isset($this->request->data['Order']['topproject_id'])) unset($this->request->data['Order']['topproject_id']);
                    $order = $this->request->data;

                }else {

			$options = array('conditions' => array(
					'Order.' . $this->Order->primaryKey => $id,
//					'Deliverynumber.testingcomp_id' => $this->Auth->user('testingcomp_id')
				)
			);

			$this->Order->recursive = 1;

			$order = $this->Order->find('first', $options);

			if(count($order) == 0){
				$this->render('close');
				return;
			}


                }



                $this->loadModel('DevelopmentData');
		$options = array(
						'conditions' => array(
											'DevelopmentData.order_id' => $order['Order']['id'],
                                                                                        'DevelopmentData.topproject_id' => $order['Order'] ['topproject_id'],
										)
						);
		$this->DevelopmentData->recursive = -1;
		$DevelopmentData = $this->DevelopmentData->find('all',$options);
                $this->loadModel('Testingmethod');
                $this->Testingmethod->recursive = -1;
                $DevelopmentSettings = $this->Xml->DatafromXml('Development','file',null);
                foreach ($DevelopmentData as $key => $value) {

                    $Testmethod = $this->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $DevelopmentData [$key] ['DevelopmentData'] ['testing_method'])));
                    $DevelopmentData [$key] ['DevelopmentData'] ['testing_method'] = $Testmethod ['Testingmethod'] ['name']  ;
                }

                //$this->loadModel('Testingmethod');
               // $this->Testingmethod->find('first',array('conditions' => Testingmethod.id => ));

                $this->set('xml_development',$DevelopmentSettings['settings']);
                $this->set('developmentdata',$DevelopmentData);
                $this->loadModel('Testingcomp');
		$options = array(
						'conditions' => array(
											'Testingcomp.id' => $order['Order']['testingcomp_id'],
										)
						);
		$this->Testingcomp->recursive = -1;
		$Testingcomp = $this->Testingcomp->find('first',$options);
		$this->request->data = array_merge($order,$Testingcomp);
                    if(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png'){
                        $this->request->data['Testingcomp'] = $this->Auth->user('Testingcomp');
			$this->request->data['Testingcomp']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png';
		}


        }

      /*  public function sessionstorage(){
            if($this->request->data['req_controller'] == 'orders'){

                $act = $this->request->data['req_action'];
                if(!empty($act)){
                   switch($act){
                        default:
                        $this->Data->Sessionstoraging();
            }
                }

        // $this->Data->Sessionstoraging();
            }
        }   */

    public function duplicat () {


		$this->layout = 'modal';
 		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];

		$OrderData = $this->Xml->XmltoArray('Order','file',null);

		$this->set('xml_order',$OrderData);
		$order = $this->Order->findById($orderID);

		$this->loadModel('Expediting');
		$this->loadModel('Supplier');
		$this->loadModel('OrdersEmailadresses');

		$SupplierOld = $this->Supplier->find('first',array('conditions' => array('Supplier.order_id' => $orderID)));

		if(isset($this->request->data['Order'])) {

			foreach ($OrderData->Order->children() as $okey => $oval)  {
				if(empty($oval->dublicate)) unset($order ['Order'] [$okey]);
			}

			unset($order['Order']['status']);
			$order['Order']['auftrags_nr'] = $this->request->data['Order']['auftrags_nr'];

			$CascadesOrder = $this->Order->CascadesOrder->find('all',array('conditions' => array('CascadesOrder.order_id' => $orderID)));
			$OrdersTestingcomp = $this->Order->OrdersTestingcomp->find('all',array('conditions' => array('OrdersTestingcomp.order_id' => $orderID)));
			$OrdersEmail = $this->OrdersEmailadresses->find('all',array('conditions' => array('OrdersEmailadresses.order_id' => $orderID)));

			foreach($CascadesOrder as $_key => $_data) $order['Cascade']['Cascade'][$_key] = $_data['CascadesOrder']['cascade_id'];
			foreach($OrdersTestingcomp as $_key => $_data) $order['Testingcomp']['Testingcomp'][] = $_data['OrdersTestingcomp']['testingcomp_id'];
			foreach($OrdersEmail as $_key => $_data) $order['Emailaddress']['Emailaddress'][] = $_data['OrdersEmailadresses']['emailadress_id'];

			$this->Order->create();

			if($this->Order->save($order)){

//				$c_o_id = array('order_id' =>$this->Order->getLastInsertID(),'cascade_id' => $cascadeID);
//				$this->Order->CascadesOrder->create();
//				$this->Order->CascadesOrder->save($c_o_id);

//				$t_o_id = array('order_id' =>$this->Order->getLastInsertID(),'testingcomp_id' => $this->Auth->user('testingcomp_id'));
//				$this->Order->OrdersTestingcomp->create();
//				$this->Order->OrdersTestingcomp->save($t_o_id);


				$this->loadModel('Deliverynumber');

				unset($order['Deliverynumber']);

				$order['Deliverynumber']['id'] = 0;
				$order['Deliverynumber']['order_id'] = $this->Order->getInsertID();
				$order['Deliverynumber']['topproject_id'] = $this->request->projectvars['VarsArray'][0];
				$order['Deliverynumber']['cascade_id'] = $this->request->projectvars['VarsArray'][1];
				$order['Deliverynumber']['order_id'] = $this->Order->getInsertID();
				$order['Deliverynumber']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

				$this->Deliverynumber->create();
				$this->Deliverynumber->save($order['Deliverynumber']);

				$Supplier = $this->Supplier->find('first',array('conditions' => array('Supplier.order_id' => $orderID)));

				unset($Supplier['Supplier']['id']);
				unset($Supplier['Supplier']['lauf_nr']);
				unset($Supplier['Supplier']['created']);
				unset($Supplier['Supplier']['modified']);

				$Supplier['Supplier']['status'] = 5;
				$Supplier['Supplier']['order_id'] = $this->Order->getInsertID();
				$Supplier['Supplier']['equipment'] = $order['Order']['auftrags_nr'];

				$this->Supplier->create();

				if($this->Supplier->save($Supplier['Supplier'])){

					$this->ExpeditingTool->OrderExpeditingMerge(0,$Supplier['Supplier']['order_id'],'Supplier');

					$ExpetidingDEscriptionArray[20] = 'Übergabe Vorprüfunterlagen an R-ST';
					$ExpetidingDEscriptionArray[23] = 'Freigabe Vorprüfunterlagen R-ST';
					$ExpetidingDEscriptionArray[26] = 'Lieferung Vormaterial';
					$ExpetidingDEscriptionArray[29] = 'Fertigungsbeginn';
					$ExpetidingDEscriptionArray[32] = 'Haltepunkte Prüffolgeplan (ZfP, Wärmebehandlung)';
					$ExpetidingDEscriptionArray[35] = 'Fertigungsende';
					$ExpetidingDEscriptionArray[38] = 'Abnahme (Druckprüfung, Maßkontrolle)';
					$ExpetidingDEscriptionArray[41] = 'Lieferung';
					$ExpetidingDEscriptionArray[44] = 'Eingang Dokumentation';

					$ExpetidingSequence[20] = 1;
					$ExpetidingSequence[23] = 2;
					$ExpetidingSequence[26] = 3;
					$ExpetidingSequence[29] = 4;
					$ExpetidingSequence[32] = 5;
					$ExpetidingSequence[35] = 6;
					$ExpetidingSequence[38] = 7;
					$ExpetidingSequence[41] = 8;
					$ExpetidingSequence[44] = 9;

					$this->loadModel('Expediting');

					foreach ($ExpetidingDEscriptionArray as $skey => $svalue) {
						$supplyID =  $this->Supplier->getLastInsertID();
						$Expediting = $this->Expediting->find('first',array('conditions' => array('Expediting.supplier_id' => $SupplierOld['Supplier']['id'],'Expediting.description' => $svalue)));
						$CurrentRoll = $this->Data->BelongsToManySelected(array('Expediting' => array('id' => $Expediting['Expediting']['id'])),'Expediting','Roll',array('ExpeditingsRolls','roll_id','expediting_id'));

						$DataNewLoad['Expediting'] = array(
							'supplier_id' => $supplyID,
							'categorie' => null,
							'sequence' => $ExpetidingSequence[$skey],
							'description' => $ExpetidingDEscriptionArray[$skey],
							'status' => 4,
							'karenz' => 10
						);

						$DataNewLoad['Roll']['Roll'] = $CurrentRoll['Roll']['selected'];

						$this->Expediting->create();
						$this->Expediting->save($DataNewLoad);

					}
				}

				$this->Autorisierung->Logger($this->Supplier->getLastInsertID(),$order['Order']);

				$this->request->projectvars['VarsArray'][2] = $this->Order->getInsertID();

				$FormName = array('controller' => 'reportnumbers', 'action' => 'index', 'terms' => implode('/',$this->request->projectvars['VarsArray']));

				$this->set('FormName',$FormName);
				$this->set('saveOK',1);
			}
		}

		$order['Order']['auftrags_nr'] = '';
		$this->request->data = $order;

		$SettingsArray = array();
		$this->set('SettingsArray',$SettingsArray);

    }
}
