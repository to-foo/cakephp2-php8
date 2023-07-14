<?php
class NavigationComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ModulNavigation($CascadeId,$Modul) {

		switch ($Modul) {
			case 'progress':
			$this->__ModulNavigationProgress($CascadeId);
			break;

			case 'expediting':
			$this->__ModulNavigationExpediting($CascadeId);
			break;

			case 'ndt':
			$this->__ModulNavigationNdt($CascadeId);
			break;

		}
	}

	protected function __ModulNavigationProgress($CascadeId) {

		$ModalLinks = array();

		$options = array(
			'conditions' => array(
			'Cascade.id' => $CascadeId,
			'Cascade.status' => 1,
			)
		);

		$Cascade = $this->_controller->Cascade->find('first', $options);

		if(count($Cascade) == 0) return;

		$ModalLinks['progress_modul']['name'] = __('Progress modul');
		$ModalLinks['progress_modul']['url'] = $this->_controller->request->here;
		$ModalLinks['progress_modul']['layout'] = $this->_controller->layout;
		$ModalLinks['progress_modul']['selected'] = 1;

		$ModalLinks = $this->__ModulNavigationGetExpeditingLink($ModalLinks, $CascadeId);

		$ModalLinks['ndt_modul']['name'] = __('NDT modul');
		$ModalLinks['ndt_modul']['url'] = Router::url(['controller' => 'cascades', 'action' => 'index', $Cascade['Cascade']['topproject_id'],$Cascade['Cascade']['id']]);
		$ModalLinks['ndt_modul']['layout'] = 'ajax';
		$ModalLinks['ndt_modul']['selected'] = 0;

		$this->_controller->set('ModalLinks',$ModalLinks);

	}

	protected function __ModulNavigationExpediting($CascadeId) {

		$ModalLinks = array();

		$ModalLinks = $this->__ModulNavigationGetProgressLink($ModalLinks, $CascadeId);

		$options = array(
			'conditions' => array(
			'Cascade.id' => $CascadeId,
			'Cascade.status' => 1,
			)
		);

		$Cascade = $this->_controller->Cascade->find('first', $options);

		if(count($Cascade) == 0) return;

		$ModalLinks['expediting_modul']['name'] = __('Expediting modul');
		$ModalLinks['expediting_modul']['url'] = $this->_controller->request->here;
		$ModalLinks['expediting_modul']['layout'] = $this->_controller->layout;
		$ModalLinks['expediting_modul']['selected'] = 1;

		$ModalLinks['ndt_modul']['name'] = __('NDT modul');
		$ModalLinks['ndt_modul']['url'] = Router::url(['controller' => 'cascades', 'action' => 'index', $Cascade['Cascade']['topproject_id'],$Cascade['Cascade']['id']]);
		$ModalLinks['ndt_modul']['layout'] = 'ajax';
		$ModalLinks['ndt_modul']['selected'] = 0;

		$this->_controller->set('ModalLinks',$ModalLinks);

	}

	protected function __ModulNavigationNdt($CascadeId) {

		$ModalLinks = array();

		$ModalLinks = $this->__ModulNavigationGetProgressLink($ModalLinks, $CascadeId);

		$ModalLinks = $this->__ModulNavigationGetExpeditingLink($ModalLinks, $CascadeId);

		$ModalLinks['ndt_modul']['name'] = __('NDT modul');
		$ModalLinks['ndt_modul']['url'] = $this->_controller->request->here;
		$ModalLinks['ndt_modul']['layout'] = $this->_controller->layout;
		$ModalLinks['ndt_modul']['selected'] = 1;


		$this->_controller->set('ModalLinks',$ModalLinks);

	}

	protected function __ModulNavigationGetExpeditingLink($ModalLinks, $CascadeId) {

		$options = array(
			'conditions' => array(
			'Cascade.id' => $CascadeId,
			'Cascade.status' => 1,
			)
		);

		$Cascade = $this->_controller->Cascade->find('first', $options);

		if(count($Cascade) == 0) return $ModalLinks;

		if(ClassRegistry::isKeySet('Supplier') === false) $this->_controller->loadModel('Supplier');

		$options = array(
			'conditions' => array(
			'Supplier.cascade_id' => $CascadeId,
			'Supplier.deleted' => 0,
			)
		);

		$Supplier = $this->_controller->Supplier->find('first', $options);

		if(count($Supplier) == 0) return $ModalLinks;

		$ModalLinks['expediting_modul']['name'] = __('Expediting modul');
		$ModalLinks['expediting_modul']['url'] = Router::url(['controller' => 'suppliers', 'action' => 'overview', $Cascade['Cascade']['topproject_id'],$CascadeId,$Supplier['Supplier']['id']]);
		$ModalLinks['expediting_modul']['layout'] = 'ajax';
		$ModalLinks['expediting_modul']['selected'] = 0;

		return $ModalLinks;
	}

	protected function __ModulNavigationGetProgressLink($ModalLinks, $CascadeId) {

		if(ClassRegistry::isKeySet('Advance') === false) $this->_controller->loadModel('Advance');
		if(ClassRegistry::isKeySet('AdvancesCascade') === false) $this->_controller->loadModel('AdvancesCascade');
		if(ClassRegistry::isKeySet('AdvancesCascadesTestingcomp') === false) $this->_controller->loadModel('AdvancesCascadesTestingcomp');
		if(ClassRegistry::isKeySet('AdvancesCascadesUser') === false) $this->_controller->loadModel('AdvancesCascadesUser');
		if(ClassRegistry::isKeySet('AdvancesOrder') === false) $this->_controller->loadModel('AdvancesOrder');

		$options = array(
			'conditions' => array(
			'AdvancesCascade.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascade = $this->_controller->AdvancesCascade->find('first', $options);

		if(count($AdvancesCascade) == 0) return;

		$options = array(
			'conditions' => array(
				'AdvancesOrder.advance_id' => $AdvancesCascade['AdvancesCascade']['advance_id'],
				'AdvancesOrder.cascade_id' => $CascadeId,
			)
		);

		$AdvancesOrder = $this->_controller->AdvancesOrder->find('first', $options);

		if(count($AdvancesOrder) == 0) return $ModalLinks;

		$options = array(
			'conditions' => array(
				'AdvancesCascadesTestingcomp.advance_id' => $AdvancesCascade['AdvancesCascade']['advance_id'],
				'AdvancesCascadesTestingcomp.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
				'AdvancesCascadesTestingcomp.cascade_id' => $CascadeId,
			)
		);

		$AdvancesCascadesTestingcomp = $this->_controller->AdvancesCascadesTestingcomp->find('first', $options);

		if(count($AdvancesCascadesTestingcomp) == 0) return $ModalLinks;

		$options = array(
			'conditions' => array(
				'AdvancesCascadesUser.advance_id' => $AdvancesCascade['AdvancesCascade']['advance_id'],
				'AdvancesCascadesUser.cascade_id' => $CascadeId,
			)
		);

		$AdvancesParentCascadesUser = $this->_controller->AdvancesCascadesUser->find('first',$options);

		if(count($AdvancesParentCascadesUser) == 0) return $ModalLinks;

		$ModalLinks['progress_modul']['name'] = __('Progress modul');
		$ModalLinks['progress_modul']['url'] = Router::url(['controller' => 'advances', 'action' => 'edit', $AdvancesCascade['AdvancesCascade']['advance_id']]);
		$ModalLinks['progress_modul']['layout'] = 'ajax';
		$ModalLinks['progress_modul']['selected'] = 0;
		$ModalLinks['progress_modul']['post']['modul'] = 'ndt';
		$ModalLinks['progress_modul']['post']['data_area'] = 'advance_area';
		$ModalLinks['progress_modul']['post']['scheme_start'] = $CascadeId;

		return $ModalLinks;
	}

	public function afterEDIT($value,$time) {
		$output = null;
		$output .= '<script type="text/javascript">
				$(document).ready(function(){

						$(window).scrollTop(0);
						setTimeout( function(){
							var data = $(this).serializeArray();
							data.push({name: "ajax_true", value: 1});

							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.$value.'",
								data	: data,
								success: function(data) {
		    					$("#container").html(data);
		    					$("#container").show();
								}
							});
							return false;
						} , '.$time.');
					});
				</script>';
	return $output;
	}

	public function setImageLink($action,$id,$SettingsArray) {

		$reportimagesBeforeNext = $this->_controller->Reportimage->find('all',	array('conditions' => array(
																						'Reportimage.reportnumber_id' => $id,
																						),
																					)
																				);

		$reportimages = array();
		$reportimagesBefore = array();
		$reportimagesNext = array();

		foreach($reportimagesBeforeNext as $_key => $_reportimagesBeforeNext){

			if($this->_controller->request->projectvars['VarsArray'][6] == $_reportimagesBeforeNext['Reportimage']['id']){

				$reportimages = $_reportimagesBeforeNext;
				if(isset($reportimagesBeforeNext[$_key - 1])){
						$reportimagesBefore = $reportimagesBeforeNext[$_key - 1];
						$SettingsArray['beforelink'] = array('discription' => __('Image before',true), 'controller' => 'reportnumbers','action' => $action, 'terms' =>

array(
$this->_controller->request->projectID,
$this->_controller->request->cascadeID,
$this->_controller->request->orderID,
$this->_controller->request->reportID,
$this->_controller->request->reportnumberID,
0,
$reportimagesBefore['Reportimage']['id']
)
);
}

				if(isset($reportimagesBeforeNext[$_key + 1])){
						$reportimagesNext = $reportimagesBeforeNext[$_key + 1];
						$SettingsArray['nextlink'] = array('discription' => __('Next image',true), 'controller' => 'reportnumbers','action' => $action, 'terms' =>

array(
$this->_controller->request->projectID,
$this->_controller->request->cascadeID,
$this->_controller->request->orderID,
$this->_controller->request->reportID,
$this->_controller->request->reportnumberID,
0,
$reportimagesNext['Reportimage']['id']
)
);
}

				$this->_controller->set('ImageCurrent', $_key + 1);
				break;
			}
		}

		$this->_controller->set('ImageTotal', count($reportimagesBeforeNext));
		$this->_controller->set('SettingsArray', $SettingsArray);
	}

	public function afterEditModal($value,$time) {
		$output = null;
		$output .= '<script type="text/javascript">
				$(document).ready(function(){

						$(window).scrollTop(0);
						setTimeout( function(){
							var data = $(this).serializeArray();
							data.push({name: "ajax_true", value: 1});

							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.$value.'",
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						} , '.$time.');
					});
				</script>';
	return $output;
	}

	public function deleteOption() {
		$output = null;
		$output .= '<script type="text/javascript">
				$(document).ready(function(){
						$("#'.$this->_controller->Session->read('dropdownid').' option[value!=\'\']").remove();
					});
				</script>';
	return $output;
	}

	public function putOption($value) {
		$output = null;
		$output .= '<script type="text/javascript">
				$(document).ready(function(){';

				foreach($value as $_key => $_value){
					foreach($_value as $__key => $__value){
						if(isset($__value['number'])){
							$output .= '$("<option/>").val("'.$__value['number'].'").text("'.$__value['discription'].'").appendTo("#'.$this->_controller->Session->read('dropdownid').'");';
						}
						else {
							$output .= '$("<option/>").val("'.$__value['discription'].'").text("'.$__value['discription'].'").appendTo("#'.$this->_controller->Session->read('dropdownid').'");';
						}
					}
				}
		$output .= '});
				</script>';
	return $output;
	}

	public function ajaxURL() {

		if(isset($this->_controller->request->params['named']['page'])){
			return;
		}

		if($this->_controller->Auth->user('id')){
			$ajax_true = null;

			if(isset($this->_controller->request->data['ajax_true']) && $this->_controller->request->data['ajax_true'] == 1) {
				$ajax_true = 1;
			}
			if(isset($this->_controller->request->data['Order']['ajax_true']) && $this->_controller->request->data['Order']['ajax_true'] == 1) {
				$ajax_true = 1;
			}

			if($ajax_true == null && $this->_controller->request->params['action'] != 'loggedin'){
				$this->_controller->Session->write('ajaxURL', $this->_controller->params->url);
				$this->_controller->redirect(array('controller' => 'users', 'action' => 'loggedin'));
			}
		}
	}

	public function lastURL() {

		$this->_controller->Autorisierung->CreateRedirectUrl();

		if($this->_controller->layout == 'ajax'){
			$this->_controller->Session->write('lastURL', $this->_controller->params->url);
			$this->_controller->Session->write('lastArrayURL.controller', $this->_controller->params->controller);
			$this->_controller->Session->write('lastArrayURL.action', $this->_controller->params->action);
			$this->_controller->Session->write('lastArrayURL.pass', $this->_controller->params->pass);
			$this->_controller->Session->write('lastArrayURL.microtime', microtime());
		}
		if($this->_controller->layout == 'modal'){
			$this->_controller->Session->write('lastmodalURL', $this->_controller->params->url);
			$this->_controller->Session->write('lastmodalArrayURL.controller', $this->_controller->params->controller);
			$this->_controller->Session->write('lastmodalArrayURL.action', $this->_controller->params->action);
			$this->_controller->Session->write('lastmodalArrayURL.pass', $this->_controller->params->pass);
			$this->_controller->Session->write('lastmodalArrayURL.microtime', microtime());
		}
	}

	public function loggedUser() {

		$this->_controller->loadModel('User');
		$rolls = $this->_controller->User->Roll->find('list');

		if($this->_controller->Auth->user('id') != ''){

			$login_infos  = array();
			$login_infos['discription'] = $this->_controller->Auth->user('name') . ' ('.$rolls[$this->_controller->Auth->user('roll_id')].') ';

			$login_infos['link'] = array(
				'conroller' => 'users',
				'action' => 'password',
				'term' => array($this->_controller->Auth->user('testingcomp_id'),$this->_controller->Auth->user('id')),
				'options' => array('class' => 'modal'));

			return $login_infos;
		}
	}

	public function Breads($value) {
		$bread = array();
		$items = array();
		$projekt = null;
		$order = null;

		// Der Rootlink steht immer Breadcrumbarry
		$items['discription'] = __('Start', true);
		$items['controller'] = 'topprojects';
		$items['action'] = 'index';
		$items['pass'] = null;
		$bread[] = $items;

//		return $bread;
		return array();
	}

	public function BreadForReport($reportnumbers,$action) {

		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];

		if($reportnumbers['Order']['status'] == 0){
			$status = array('number' => 0, 'value' => __('open', true));
		}
		if($reportnumbers['Order']['status'] == 1){
			$status = array('number' => 1, 'value' => __('close', true));
		}

		$breads = $this->Breads(null);
		$cascade = $this->CascadeGetBreads($cascadeID);
		$breads = $this->CascadeCreateBreadcrumblist($breads,$cascade);
		$breads = $this->RemoveLastElementByGroup($breads);

		if($cascade[0]['Cascade']['orders'] > 0){
			$breads[] = array(
				'discription' => $reportnumbers['Order']['auftrags_nr'] . ' (' . $status['value'] . ')',
        		'controller' => 'reportnumbers',
				'action' => 'index',
				'pass' =>
					$reportnumbers['Reportnumber']['topproject_id'].'/'.
					$reportnumbers['Reportnumber']['cascade_id'].'/'.
					$reportnumbers['Reportnumber']['order_id']
			);
		}

		$breads[] = array(
				'discription' => $reportnumbers['Report']['name'],
        		'controller' => 'reportnumbers',
				'action' => 'show',
				'pass' =>
					$reportnumbers['Reportnumber']['topproject_id'].'/'.
					$reportnumbers['Reportnumber']['cascade_id'].'/'.
					$reportnumbers['Reportnumber']['order_id'].'/'.
					$reportnumbers['Reportnumber']['report_id']
			);
                $separator = Configure::read('Separator');
				$breads[] = array(

				'discription' => $this->_controller->Pdf->ConstructReportname($reportnumbers),
        		'controller' => 'reportnumbers',
				'action' => $action,
				'pass' =>
					$reportnumbers['Reportnumber']['topproject_id'].'/'.
					$reportnumbers['Reportnumber']['cascade_id'].'/'.
					$reportnumbers['Reportnumber']['order_id'].'/'.
					$reportnumbers['Reportnumber']['report_id'].'/'.
					$reportnumbers['Reportnumber']['id']
			);

		return $breads;
	}

	public function OrderKat($val) {
		// Test ob laufender Betrieb oder Revision
		if($val == 2){
			$haedline = __('Revision', true);
			$breads[] = array(
				'discription' => __('Revision', true),
        	    'controller' => 'orders',
    	        'action' => 'index',
	            'pass' => $this->_controller->request->projectID.'/2'
				);
		}
		elseif($val == 1){
			$haedline = __('Ongoing process', true);
			$breads[] = array(
				'discription' => __('Ongoing process', true),
        	    'controller' => 'orders',
    	        'action' => 'index',
	            'pass' => $this->_controller->request->projectID.'/1'
				);
		}

		$output['haedline'] = null;
		$output['breads'] = null;

		return $output;
	}

	public function BreadsDropdown($bread,$value) {

		$items = array();

		if($this->_controller->request->params['action'] == 'index') {
			$items['discription'] = $value['Report']['Report']['name'];
			$items['controller'] = 'reportnumbers';
			$items['action'] = 'show'.'/'.$value['Report']['Report']['id'].'/'.$this->_controller->request->projectID.'/'.$this->_controller->request->orderID;
			$items['pass'] = null;
			$bread[] = $items;

			$items['discription'] = __('Dropdowns', true);
			$items['controller'] = 'dropdowns';
			$items['action'] = 'index';
			$items['pass'] = $value['Report']['Report']['id'];
			$bread[] = $items;

			$items['discription'] = $value['Report']['Dropdown'][Configure::read('Config.language')];
			$items['controller'] = $value['Report']['Dropdown']['wert'];
			$items['action'] = 'index';
			$items['pass'] = $value['Report']['Report']['id'];
			$bread[] = $items;
			}

		if($this->_controller->request->params['action'] == 'dropdownindex') {
			$items['discription'] = $value['Report']['Report']['name'];
			$items['controller'] = 'reportnumbers';
			$items['action'] = 'show'.'/'.$value['Report']['Report']['id'].'/'.$this->_controller->request->projectID.'/'.$this->_controller->request->orderID;
			$items['pass'] = null;
			$bread[] = $items;

			$items['discription'] = __('Dropdowns', true);
			$items['controller'] = 'dropdowns';
			$items['action'] = 'index';
			$items['pass'] = $value['Report']['Report']['id'];
			$bread[] = $items;

			$items['discription'] = $value['Report']['Dropdown'][Configure::read('Config.language')];
			$items['controller'] = 'dropdowns';
			$items['action'] = 'dropdownindex/'.$value['Report'][$this->_controller->modelClass]['id'];
			$items['pass'] = $value['Report']['Report']['id'];
			$bread[] = $items;
		}

		if($this->_controller->request->params['action'] == 'dropdownedit') {

			$items['discription'] = $value['Report']['Report']['name'];
			$items['controller'] = 'reportnumbers';
			$items['action'] = 'show'.'/'.$value['Report']['Report']['id'].'/'.$this->_controller->request->projectID.'/'.$this->_controller->request->orderID;
			$items['pass'] = null;
			$bread[] = $items;

			$items['discription'] = __('Dropdowns', true);
			$items['controller'] = 'dropdowns';
			$items['action'] = 'index';
			$items['pass'] = $value['Report']['Report']['id'];
			$bread[] = $items;

			$items['discription'] = $value['Report']['Dropdown'][Configure::read('Config.language')];
			$items['controller'] = 'dropdowns';
			$items['action'] = 'dropdownindex/'.$value['Report'][$this->_controller->modelClass]['id'];
			$items['pass'] = $value['Report']['Report']['id'];
			$bread[] = $items;

			$items['discription'] = __('Edit', true);
			$items['controller'] = 'dropdowns';
			$items['action'] = 'dropdownedit/'.$value['Report']['Dropdown']['id'];
			$items['pass'] = $value['Report'][$value['Report']['Dropdown']['model']]['id'];
			$bread[] = $items;
		}

		if($this->_controller->request->params['action'] == 'dropdownadd') {
			$items['discription'] = $value['Report']['name'];
			$items['controller'] = 'reportnumbers';
			$items['action'] = 'show'.'/'.$value['Report']['id'].'/'.$this->_controller->request->projectID.'/'.$this->_controller->request->orderID;
			$items['pass'] = null;
			$bread[] = $items;

			$items['discription'] = __('Dropdowns', true);
			$items['controller'] = 'dropdowns';
			$items['action'] = 'index';
			$items['pass'] = $value['Report']['id'];
			$bread[] = $items;

			$items['discription'] = $value['Dropdown'][Configure::read('Config.language')];
			$items['controller'] = 'dropdowns';
			$items['action'] = 'dropdownindex/'.$value['Dropdown']['id'];
			$items['pass'] = $value['Report']['id'];
			$bread[] = $items;

			$items['discription'] = __('Add', true);
			$items['controller'] = 'dropdowns';
			$items['action'] = 'dropdownadd';
			$items['pass'] = $value['Dropdown']['id'];
			$bread[] = $items;
		}

		return $bread;
	}

	// Funktion für das linke Seitenmenü in der Auftragsverwaltung
	public function NaviMenue($value) {

		$output = array();
		$controller = $this->_controller->request->params['controller'];
		$action = $this->_controller->request->params['action'];

		switch($controller) {
			case ('dropdowns'):
				switch($action) {
				case ('index'):
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'reportnumbers',
						'discription' => $value['Report']['name'],
						'actions' => 'show/'.$value['Report']['id'].'/'.$this->_controller->request->projectID.'/'.$this->_controller->request->orderID,
						'params' => null,
					);
				break;
				case ('edit'):
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'reportnumbers',
						'discription' => $value['Report']['name'],
						'actions' => 'show/'.$value['Report']['id'].'/'.$this->_controller->request->projectID.'/'.$this->_controller->request->orderID,
						'params' => null,
					);
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'dropdowns',
						'discription' => __('Back to Dropdown Boxes', true),
						'actions' => 'index/'.$value['Report']['id'].'/',
						'params' => null,
					);
				break;
				}
			break;
			case ('orders'):
			/*
				$output[] = array(
					'haedline' => null,
					'class' => 'ajax',
					'controller' => 'orders',
					'discription' => __('Search order', true),
					'actions' => 'search',
					'params' => null,
				);
			*/
			switch($action) {
				case ('index'):
					$orderID = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][0]);
					/*
					$output[] = array(
						'haedline' => null,
						'class' => 'modalsmall',
						'controller' => 'orders',
						'discription' => __('Create order through a file', true),
						'actions' => 'select',
						'params' => $orderID,
					);
					*/
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Create order manually', true),
						'actions' => 'add',
						'params' => $orderID,
					);
				break;
				case ('search'):
					$orderID = $this->_controller->request->orderID;
					/*
					$output[] = array(
						'haedline' => null,
						'class' => 'modalsmall',
						'controller' => 'orders',
						'discription' => __('Create order through a file', true),
						'actions' => 'select',
						'params' => $orderID,
					);
					*/
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Create order manually', true),
						'actions' => 'add',
						'params' => $orderID,
					);
				break;
				case ('edit'):
					$orderID = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][1]);
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Back to list', true),
						'actions' => 'index',
						'params' => $orderID,
					);
					/*
					$output[] = array(
						'haedline' => null,
						'class' => 'modalsmall',
						'controller' => 'orders',
						'discription' => __('Create order through a file', true),
						'actions' => 'select',
						'params' => $orderID,
					);
					*/
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Create order manually', true),
						'actions' => 'add',
						'params' => $orderID,
					);
				break;
				case ('add'):
					$orderID = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][0]);
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Back to list', true),
						'actions' => 'index',
						'params' => $this->_controller->request->projectID,
					);
					/*
					$output[] = array(
						'haedline' => null,
						'class' => 'modalsmall',
						'controller' => 'orders',
						'discription' => __('Create order through a file', true),
						'actions' => 'select',
						'params' => $this->_controller->Session->read('projectURL'),
					);
					*/
				break;
				case ('create'):
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Back to list', true),
						'actions' => 'index',
						'params' => $this->_controller->request->projectID,
					);
					$output[] = array(
						'haedline' => null,
						'class' => 'ajax',
						'controller' => 'orders',
						'discription' => __('Create order manually', true),
						'actions' => 'add',
						'params' => $this->_controller->request->projectID,
					);
				break;
			}
			break;
		}
	return $output;
	}

	public function NaviMenueAddDropdown($output,$value) {
		$controller = $this->_controller->request->params['controller'];
		$action = $this->_controller->request->params['action'];

		switch($action) {
		case ('dropdownindex'):
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => 'dropdowns',
				'discription' => __('Dropdowns', true),
				'actions' => 'index',
				'params' => $value['Report']['id'],
			);
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => $this->_controller->request->params['controller'],
				'discription' => __('Create', true).' '.$this->_controller->modelClass,
				'actions' => 'dropdownadd',
				'params' => $value['Dropdown']['id'],
			);
		break;
		case ('dropdownadd'):
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => 'dropdowns',
				'discription' => __('Dropdowns', true),
				'actions' => 'index',
				'params' => $value['Report']['id'],
			);

			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => $this->_controller->request->params['controller'],
				'discription' => __('Back to list', true),
				'actions' => 'dropdownindex/'.$value['Dropdown']['id'],
				'params' => $value['Report']['id'],
			);
		break;
		case ('dropdownedit'):
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => 'dropdowns',
				'discription' => __('Dropdowns', true),
				'actions' => 'index',
				'params' => $value['Report']['id'],
				);
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => $this->_controller->request->params['controller'],
				'discription' => __('Back to list', true),
				'actions' => 'dropdownindex/'.$value['Dropdown']['id'],
				'params' => $value['Report']['id'],
			);
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => $this->_controller->request->params['controller'],
				'discription' => __('Create', true).' '.$this->_controller->modelClass,
				'actions' => 'dropdownadd',
				'params' => $value['Dropdown']['id'],
			);
			$output[] = array(
				'haedline' => null,
				'class' => 'ajax',
				'controller' => $this->_controller->request->params['controller'],
				'discription' => __('Delete this', true).' '.$this->_controller->modelClass,
				'actions' => 'delete',
				'params' => $value[$this->_controller->modelClass]['id'],
			);
		break;
		}
	return $output;
	}

	public function Menue() {

		// Alle Controller, die in das Menü aufgenommen werden sollen
		$mainmenue = array('topprojects' => null, 'reports' => null, 'testingcomps' => null, 'users' => null, 'qualifications' => null, 'testingmethods' => null);
		$mainmenue_haedline = array(
			'topprojects' => __('Projects', true),
			'reports' => __('Reports', true),
			'testingcomps' => __('Testingcompanies', true),
			'users' => __('Users', true),
			'qualifications' => __('Qualifications', true),
			'testingmethods' => __('Testingmethods', true)
		);
		$mainmenue_elemente = array('topprojects', 'reports', 'testingcomps', 'users', 'qualifications', 'testingmethods');
		$actions_methods = array('1' => 'index','2' => 'view','3' => 'add','4' => 'edit','5' => 'delete');
		$actions_del = array(1,3,4);
		$actions = array();
		$_id  = array();

		// Die Texte der Links, ausgelagert zu automatischen Übersetzung
		$mainmenue_lang = array(
			'topprojects' => array(
					__('List projects', true),
					__('Show this project', true),
					__('New project', true),
					__('Edit this project', true),
					__('Delete this project', true)
					),
			'reports' => array(
					__('List reports', true),
					__('Show this report', true),
					__('New report', true),
					__('Edit this report', true),
					__('Delete this report', true)
					),
			'testingcomps' => array(
					__('List testingcompanies', true),
					__('Show this testingcompany', true),
					__('New testingcompany', true),
					__('Edit this testingcompany', true),
					__('Delete this testingcompany', true)
					),
			'users' => array(
					__('List users', true),
					__('Show this user', true),
					__('New user', true),
					__('Edit this user', true),
					__('Delete this user', true)
					),
			'qualifications' => array(
					__('List qualifications', true),
					__('Show this qualification', true),
					__('New qualification', true),
					__('Edit this qualification', true),
					__('Delete this qualification', true)
					),
			'testingmethods' => array(
					__('List testingmethods', true),
					__('Show this testingmethod', true),
					__('New testingmethod', true),
					__('Edit this testingmethod', true),
					__('Delete this testingmethod', true)
					),
			);

		// Wenn eine ID als Variable vorliegt, wird diese in eine Variable geschrieben
		// Wenn die ID nicht vorliegt, wird die Variable auf 0 gesetzt
		if($this->_controller->params['pass']){$params = $this->_controller->params['pass'][0];}
		else{$params = 0;}

		// Die verschiedenen Actions werden durchlaufen. View,
		// Edit und Delete sollen nur zur Verfügung stehen wenn die ID-Variable existent ist
		for($x = 1; $x < count($actions_methods) + 1; $x++) {
			if($x == 1 || $x == 3){
				$actions[] = $actions_methods[$x];
				$_id[$x-1] = null;
			}
			elseif($params != 0) {
				$actions[] = $actions_methods[$x];
				$_id[$x-1] = $params;
			}
			else {
				$actions[] = null;
				$_id[$x-1] = null;
			}
		}

		foreach($mainmenue_elemente as $_mainmenue_elemente) {
			if($this->_controller->params['controller'] === $_mainmenue_elemente){

				// Wenn keine ID-Variable vorhanden ist, werden View, Edit und Delete
				// aus den Arrays gelöscht
				if($params === 0) {
					foreach($actions_del as $del) {
						unset($mainmenue_lang[$_mainmenue_elemente][$del]);
						unset($actions[$del]);
						unset($_id[$del]);
					}
				}

				// Die aktuelle Aktion soll nicht im Menübaum auftauchen und wird auch gelöscht
				foreach($actions as $key => $current) {
					if($this->_controller->params['action'] === $current) {
						unset($mainmenue_lang[$_mainmenue_elemente][$key]);
						unset($actions[$key]);
						unset($_id[$key]);
						break;
					}
				}

				$mainmenue[$_mainmenue_elemente] = array(
					'haedline'	 => $mainmenue_haedline[$_mainmenue_elemente],
					'controller'	=> $_mainmenue_elemente,
					'discription'	=> $mainmenue_lang[$_mainmenue_elemente],
					'actions' 		=> $actions,
					'params'		=> $_id);
			}
			else {
				$mainmenue[$_mainmenue_elemente] = array(
					'haedline'	 => $mainmenue_haedline[$_mainmenue_elemente],
					'controller'	=> $_mainmenue_elemente,
					'discription'	=> $mainmenue_lang[$_mainmenue_elemente][0],
					'actions' 		=> 'index',
					'params'		=> null);
			}
		}
		return $mainmenue;
	}

	public function StatistikInvoices($testingmethods,$orderID,$projectID) {

//		$statusArray = array(0,1,2,3);
		$statusArray = 1;
		$StatistikArray = array();
		$reports = array();

		// für die Statistik werden offene, geschlossen und abgerechnete Prüfbericht gezählt
		if($testingmethods != null && is_array($testingmethods)){
			foreach($testingmethods as $_key => $_testingmethods){
				$optionsReports = array('conditions' => array(
						'Reportnumber.delete' => 0,
						'Reportnumber.order_id' => $orderID,
						'Reportnumber.topproject_id' => $projectID,
						'Reportnumber.testingmethod_id' => $_testingmethods['Testingmethod']['id'] ,
						'Reportnumber.status >' => 1,
						'Reportnumber.settled <' => 3
					)
				);

				$reports[$_key][0] = $this->_controller->Reportnumber->find('count', $optionsReports);

				$optionsReports = array('conditions' => array(
						'Reportnumber.delete' => 0,
						'Reportnumber.order_id' => $orderID,
						'Reportnumber.topproject_id' => $projectID,
						'Reportnumber.testingmethod_id' => $_testingmethods['Testingmethod']['id'] ,
						'Reportnumber.settled' => 3
					)
				);

				$reports[$_key][1] = $this->_controller->Reportnumber->find('count', $optionsReports);

				// noch die gelöschten Berichte
				$optionsReports = array('conditions' => array(
						'Reportnumber.delete' => 1,
						'Reportnumber.order_id' => $orderID,
						'Reportnumber.topproject_id' => $projectID,
						'Reportnumber.testingmethod_id' => $_testingmethods['Testingmethod']['id'] ,
					)
				);
				$reports[$_key][2] = $this->_controller->Reportnumber->find('count', $optionsReports);

				$StatistikArray[$_key] = __('bereit für Aufmaß', true).' '.$reports[$_key][0];

			}
		}
		else{
			$optionsReports = array('conditions' => array(
					'Reportnumber.delete' => 0,
					'Reportnumber.order_id' => $orderID,
					'Reportnumber.topproject_id' => $projectID,
					'Reportnumber.status >' => 1,
					'Reportnumber.settled <' => 3
				));
			$reports[0] = $this->_controller->Reportnumber->find('count', $optionsReports);

			$optionsReports = array('conditions' => array(
					'Reportnumber.delete' => 0,
					'Reportnumber.order_id' => $orderID,
					'Reportnumber.topproject_id' => $projectID,
					'Reportnumber.settled' => 3
				));
			$reports[1] = $this->_controller->Reportnumber->find('count', $optionsReports);

			// noch die gelöschten Berichte
			$optionsReports = array('conditions' => array(
					'Reportnumber.delete' => 1,
					'Reportnumber.order_id' => $orderID,
					'Reportnumber.topproject_id' => $projectID,
				)
			);
			$reports[2] = $this->_controller->Reportnumber->find('count', $optionsReports);
			$StatistikArray =  __('bereit für Aufmaß', true).' '.$reports[0];
		}

		return $StatistikArray;
	}
        public function createReportPrintMenue($data) {
            $output = array();
            $Javascript = null;
            $ReportMenue = array();

                /*if(Configure::check('ProofPrinting') && Configure::check('ProofPrinting') == true){
		$this->request->projectvars['VarsArray'][8] = 3;
		echo $this->Html->link(__('Proof print', true),
				array_merge(
					array('action' => 'pdf'),
					$this->request->projectvars['VarsArray']
				),
				array('class'=>'round printlink', 'target'=>'_blank', 'title' => __('Print this report'), 'disabled'=>(isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1))
			);
		$this->request->projectvars['VarsArray'][8] = 0;
	}*/
            if(Configure::check('ProofPrinting') && Configure::check('ProofPrinting') == true){
                $this->_controller->request->projectvars['VarsArray'] [8] = 3;
                array_push($ReportMenue, array(
						'title' => __('Proof print', true),
						'controller' => 'reportnumbers',
						'action' => 'pdf',
						'class' => 'round printlink',
						'id' => null,
						'target' => '_blank',
						'vars' => $this->_controller->request->projectvars['VarsArray']
						)
					);
                $this->_controller->request->projectvars['VarsArray'] [8] = 0;
            }

            $Assignedclass = 'modalreport assigned ';
            $Assignedtitle = __('Assigned reports');

            if($data['Reportnumber']['parent_id'] > 0){
              $Assignedclass .= 'active';
              $Assignedtitle = __('Assigned report');
            }

            array_push($ReportMenue, array(
                'title' => $Assignedtitle,
                'controller' => 'reportnumbers',
                'action' => 'assignedReports',
                'class' => $Assignedclass,
                'id' => null,
                'disabled' => null,
                'target' => null,
                'vars' => $this->_controller->request->projectvars['VarsArray'],
            )
            );

            foreach($ReportMenue as $_key => $_ReportMenue){
			if($this->_controller->Autorisierung->AclCheck(array(0 => $_ReportMenue['controller'],1 => $_ReportMenue['action'])) == false){
				unset($ReportMenue[$_key]);
			}
		}

		$output['Menue'] = $ReportMenue;

  }

	public function createReportMenue($data,$settings) {

	  $ReportPdf  = 'Report' . $this->_controller->request->Verfahren . 'Pdf';
	  $ReportEvaluation  = 'Report' . $this->_controller->request->Verfahren . 'Evaluation';

	  $output = array();
	  $Javascript = null;
	  $ReportMenue = array();

	  array_push($ReportMenue, array(
	          'title' => __('Show this report', true),
	          'controller' => 'reportnumbers',
	          'action' => 'view',
	          'class' => 'ajaxreport view',
	          'id' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  array_push($ReportMenue, array(
	          'title' => __('Edit this report'),
	          'controller' => 'reportnumbers',
	          'action' => 'edit',
	          'class' => 'ajaxreport edit',
	          'id' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  array_push($ReportMenue, array(
	          'title' => __('Duplicate this report'),
	          'controller' => 'reportnumbers',
	          'action' => 'duplicat',
	          'class' => 'modalreport dublicate',
	          'id' => 'dublicate_report',
	          'disabled' => isset($this->_controller->writeprotection) && $this->_controller->writeprotection,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  if(Configure::read('DisablePrintOnErrors') == true) {
	    array_push($ReportMenue, array(
	          'title' => __('Print this report'),
	          'controller' => 'reportnumbers',
	          'action' => 'errors',
	          'class' => 'icon_devices_pdf modalreport',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  } else {
	    array_push($ReportMenue, array(
	          'title' => __('Print this report'),
	          'controller' => 'reportnumbers',
	          'action' => 'pdf',
	          'class' => 'print',
	          'id' => null,
	          'disabled' => null,
	          'target' => '_blank',
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  array_push($ReportMenue, array(
	          'title' => __('Show images'),
	          'controller' => 'reportnumbers',
	          'action' => 'images',
	          'class' => 'ajaxreport images',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  array_push($ReportMenue, array(
	          'title' => __('Show files'),
	          'controller' => 'reportnumbers',
	          'action' => 'files',
	          'class' => 'ajaxreport files',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  if(Configure::read('RepairManager') == true){

	    $Class = 'modalreport repairs';

	    if(isset($data['Repairs']['Statistic']['ReportRepairStatus'])) $Class .= ' '.$data['Repairs']['Statistic']['ReportRepairStatus'];

	    array_push($ReportMenue, array(
	          'title' => __('Show repair history'),
	          'controller' => 'reportnumbers',
	          'action' => 'repairs',
	          'class' => $Class,
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	    unset($Class);
	  }

	  if(!Configure::check('LinkingEnabled') || Configure::read('LinkingEnabled')) {
	    if(!empty($data['Testingmethod']['allow_children']) && strtolower($data['Testingmethod']['allow_children']) != 'false') {

	      $linking_anable_class = ' ';
	      $linking_anable_title = __('Enforce parent reportnumber',true).', '.__('subordinate reports show original reportnumber', true);

	      if($data['Reportnumber']['enforce_number'] == 1){
	        $linking_anable_class = ' active';
	        $linking_anable_title = __('Enforce parent reportnumber',true).', '.__('subordinate reports show parent reportnumber', true);
	      }

	      array_push($ReportMenue, array(
	          'title' => $linking_anable_title,
	          'controller' => 'reportnumbers',
	          'action' => 'enforce',
	          'class' => 'modalreport enforce'.$linking_anable_class,
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	    }

	    array_push($ReportMenue, array(
	          'title' => __('Assigned reports'),
	          'controller' => 'reportnumbers',
	          'action' => 'assignedReports',
	          'class' => 'modalreport assigned',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  }

	  array_push($ReportMenue, array(
	          'title' => __('Show report history'),
	          'controller' => 'reportnumbers',
	          'action' => 'history',
	          'class' => 'modalreport history',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );

	  if(!empty($settings->$ReportPdf->settings->QM_QRCODE_REPORT)){
	    array_push($ReportMenue, array(
	          'title' => __('Show QR-Code'),
	          'controller' => 'reportnumbers',
	          'action' => 'showqrcode',
	          'class' => 'modalreport qrcode',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  if(isset($data[$ReportEvaluation]['id'])){
	    if(!empty($settings->$ReportPdf->settings->QM_QRCODE_REPORT)){
	      array_push($ReportMenue, array(
	          'title' => __('Print Label'),
	          'controller' => 'printweldlabel',
	          'action' => 'showqrcode',
	          'class' => 'label',
	          'id' => null,
	          'disabled' => null,
	          'target' => '_blank',
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	    }
	  }

	  if(Configure::read('WriteSignatory') == true){
	    array_push($ReportMenue, array(
	          'title' => __('Signatures'),
	          'controller' => 'reportnumbers',
	          'action' => 'sign',
	          'class' => 'ajaxreport sign',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  if(array_search(strtolower($this->_controller->request->action), array('edit', 'editevalution')) !== false){
	    array_push($ReportMenue, array(
	          'title' => __('Add specialcharacter'),
	          'controller' => 'specialcharacters',
	          'action' => 'index',
	          'class' => 'modalreport specialchars',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  if($data['Reportnumber']['status'] == 0){
	    array_push($ReportMenue, array(
	          'title' => __('Delete this report'),
	          'controller' => 'reportnumbers',
	          'action' => 'delete',
	          'class' => 'delete',
	          'id' => 'text_delete_report',
	          'disabled' => isset($this->_controller->writeprotection) && $this->_controller->writeprotection,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  if(Configure::check('XmlImportReport') && Configure::read('XmlImportReport') == true && $data['Reportnumber']['status'] == 0) {
	    array_push($ReportMenue, array(
	          'title' => __('Import xml data'),
	          'controller' => 'reportnumbers',
	          'action' => 'upload',
	          'class' => 'upload',
	          'id' => 'text_upload_report',
	          'disabled' => isset($this->_controller->writeprotection) && $this->_controller->writeprotection,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  if(Configure::check('XmlExportReport') && Configure::read('XmlExportReport') == true) {
	    array_push($ReportMenue, array(
	          'title' => __('Export data as xml'),
	          'controller' => 'reportnumbers',
	          'action' => 'export',
	          'class' => 'export',
	          'id' => 'text_export_report',
	//						'disabled' => isset($this->_controller->writeprotection) && $this->_controller->writeprotection,
	          'target' => '_blank',
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  if(Configure::check('RevisionInReport') && Configure::read('RevisionInReport') == true && $data['Reportnumber']['status'] > 0) {

	    $class = $this->_controller->Data->GetRevisionLinkClass($data);
	    if(Configure::read('NotAllowRevision') == $data['Reportnumber']['revision'] && $data['Reportnumber']['revision_progress'] == 0){

	      $class['class'] = 'stop_versionize';
	      $class['title'] = __('The number of possible revisions has been reached',true);

	      array_push($ReportMenue, array(
	        'title' => $class['title'],
	        'controller' => 'reportnumbers',
	        'action' => 'edit',
	        'class' => 'ajax ' . $class['class'],
	        'id' => 'revision_in_report_reached',
	        'vars' => $this->_controller->request->projectvars['VarsArray']
	        )
	      );

	    } else {

	      if($data['Reportnumber']['status'] > 0){
	        array_push($ReportMenue, array(
	          'title' => $class['title'],
	          'controller' => 'reportnumbers',
	          'action' => 'revision',
	          'class' => 'modalreport ' . $class['class'],
	          'id' => 'revision_in_report',
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	      }
	    }

	    if($data['Reportnumber']['revision'] > 0){
	      array_push($ReportMenue, array(
	          'title' => 'show all revisions',
	          'controller' => 'reportnumbers',
	          'action' => 'showrevisions',
	          'class' => 'modalreport showallrevisions' ,
	          'id' => 'showallrevisions',
	//						'disabled' => isset($this->_controller->writeprotection) && $this->_controller->writeprotection,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	      }

	  }

	  if(Configure::check('sendmailreport') && Configure::read('sendmailreport') == true && $data['Reportnumber']['status'] > 0) {
	    array_push($ReportMenue, array(
	          'title' => 'send report as email',
	          'controller' => 'reportnumbers',
	          'action' => 'emailreport',
	          'class' => 'emailreport modalreport' ,
	          'id' => 'emailreport',
	//						'disabled' => isset($this->_controller->writeprotection) && $this->_controller->writeprotection,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

		array_push($ReportMenue, array(
	          'title' => __('Move report'),
	          'controller' => 'reportnumbers',
	          'action' => 'move',
	          'class' => 'modalreport icon_move',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	  		);

		if(Configure::read('TemplateManagement') == true){
	    array_push($ReportMenue, array(
	          'title' => __('Save as template'),
	          'controller' => 'templates',
	          'action' => 'add',
	          'class' => 'modalreport icon_template',
	          'id' => null,
	          'disabled' => null,
	          'target' => null,
	          'vars' => $this->_controller->request->projectvars['VarsArray']
	          )
	        );
	  }

	  foreach($ReportMenue as $_key => $_ReportMenue){
	    if($this->_controller->Autorisierung->AclCheck(array(0 => $_ReportMenue['controller'],1 => $_ReportMenue['action'])) == false){
	      unset($ReportMenue[$_key]);
	    }
	  }

	  $output['Menue'] = $ReportMenue;



	  $closeMethod = Configure::read('CloseMethode');
	  $output['CloseMethode']['Methode'] = $closeMethod;
	  $output['CloseMethode']['Reportnumber'] = $data['Reportnumber'];


	  if($closeMethod == 'showReportVerificationByTime'){

	    $output['CloseMethode']['Level'] = Configure::check('RestrictReopenFromStatus') ? intval(Configure::read('RestrictReopenFromStatus')) : 2;
	    $output['CloseMethode']['Reopen'] = !Configure::check('AllowReopen') || $data['Reportnumber']['status'] < $output['CloseMethode']['Level'] || (bool)Configure::read('AllowReopen');

	    if(Configure::check('VersionizeReport') && Configure::read('VersionizeReport') == true) {

	      switch(intval($data['Reportnumber']['status'])) {
	        case 2:
	          $output['CloseMethode']['controller'] = 'reportnumbers';
	          $output['CloseMethode']['action'] = $output['CloseMethode']['Reopen'] ? 'status2' : 'versionize';
	          $output['CloseMethode']['class'] = 'closed2'.($output['CloseMethode']['Reopen'] ? null : ' versionize');
	          $output['CloseMethode']['title'] = $output['CloseMethode']['Reopen'] ? __('Reopen this Report', true) : __('Create new version');
	        break;

	        case 3:
	          $output['CloseMethode']['controller'] = 'reportnumbers';
	          $output['CloseMethode']['action'] = $output['CloseMethode']['Reopen'] ? 'status2' : 'versionize';
	          $output['CloseMethode']['class'] = 'closed3'.($output['CloseMethode']['Reopen'] ? null : ' versionize');
	          $output['CloseMethode']['title'] = $output['CloseMethode']['Reopen'] ? __('Reopen this Report', true) : __('Create new version');
	        break;
	      }
	    }
	  }

	  if(isset($output['CloseMethode']['controller'])){
	    if($this->_controller->Autorisierung->AclCheck(array(0 => $output['CloseMethode']['controller'],1 => $output['CloseMethode']['action'])) == false){
	      unset($output['CloseMethode']);
	    }
	  }

	  return $output;
	}

	public function SubMenue($va1,$va2,$va3,$va4,$va5,$value) {
		// $va1 = 1. Model
		// $va2 = 2. Model
		// $va3 = Beschreibung
		// $va4 = Aktion
		// $va5 = Controller
		// $id = Parm
		// Test ob Array

		$orderId = null;
		$projectId = null;

		if(!is_array($value)) {
			$id = $value;
			}

		$model = $va1.'s';
		$this->_controller->loadModel($model);
		$this->_controller->loadModel('Reportnumber');

		$optionen = array('id' => $this->_controller->Autorisierung->ConditionsVariabel($va1,$va2,$this->_controller->request->projectvars['VarsArray'][0]));
		$report = $this->_controller->$model->find('all', array('conditions' => $optionen ));
		$menueReports = array();
		$controllerReports = array();
		$actionsReports = array();
		$discriptionReports = array();
		$paramsReports = array();
		$discription = $va3;
		$action = $va4;
		$controller = $va5;
		$options = array();

		foreach($report as $_report) {

			// Test ob schon Prüfberichte dieses Typs vorhanden sind
			// Wenn ja wird der Link auf anzeigen gestellt
			// Wenn nein wird auf Bericht erstellen gestellt
			$reportnumbers = array();

			$options = array(
				'topproject_id' => $this->_controller->request->projectvars['VarsArray'][0],
				'cascade_id' => $this->_controller->request->projectvars['VarsArray'][1],
//				'order_id' => $this->_controller->request->projectvars['VarsArray'][2],
				'report_id' => $_report['Reports']['id'],
			);

			if($this->_controller->request->projectvars['orderID'] > 0) $options['order_id'] = $this->_controller->request->projectvars['orderID'];

			// Wenn sich ein externes Unternehmen eingeloggt hat, müssen alle Berichte des Projekts angezeigt werden
			if(AuthComponent::user('Testingcomp.extern') == 1){
				unset($options[0]);
			}

			$this->_controller->Reportnumber->recursive = -1;

			$reportnumbers = $this->_controller->Reportnumber->find('first', array(
				'conditions' => $options,
				)
			);


		$reportsAll = count($reportnumbers);
		$reportsClose = 0;
		$reportsSetteld = 0;

		// die Links werden nur korregiert, wenn sie im Inhalt angezeigt werden
		$statistikDetail = null;
		if($va4 == 'show') {

			$statistikDetail = null;

/* Florian Zumpe 06.09.2017 --- leere Berichtsmappen öffnen, falls NE-Berichte vorhanden sind */

			if(!Configure::check('ShowNeGlobally') || !Configure::read('ShowNeGlobally')) {
				if(count($reportnumbers) == 0) {
					$action = 'listing';
					$controller = 'testingmethods';
					$discription = __('Create', true);
				}
				elseif(count($reportnumbers) > 0) {
					$action = $va4;
					$controller = $va5;
					$discription = $va3;
				}
			} else if(Configure::read('ShowNeGlobally')) {
				$action = $va4;
				$controller = $va5;
				$discription = $va3;
			}
		}

/* Florian Zumpe 06.09.2017 */

		$discriptionReports[] = $discription.' '.$_report[$model]['name'].' '.$statistikDetail;
		$controllerReports[] = $controller;
		$actionsReports[] = $action.'/'.$this->_controller->request->projectvars['VarsArray'][0].'/'.$this->_controller->request->projectvars['VarsArray'][1].'/'.$this->_controller->request->projectvars['VarsArray'][2].'/'.$_report[$model]['id'];
		$paramsReports[] = null;
		}

		$class = array();
		$linkclass = array();

		foreach($actionsReports as $_key => $_actionsReports){
			$pfad = explode('/',$_actionsReports);
			if($pfad[0] == 'show'){
				$class[$_key] = 'icon_discription icon_view ';
				$linkclass[$_key] = 'ajax ';
			}
			if($pfad[0] == 'listing') {
				$class[$_key] = 'icon_discription icon_add ';
				$linkclass[$_key] = 'modal ';
			}
		}

		$menueArray = array(
			array(
				'haedline' => __('Testing reports',true),
				'class' => $class,
				'link_class' => $linkclass,
				'controller' => $controllerReports,
				'discription' => $discriptionReports,
				'action' => $actionsReports,
				'params' => $paramsReports,
			)
		);

		$menue = array($menueArray[0]);

		return $menue;
	}

	public function ReportVars() {

		$breadcrumpList = Configure::read('breadcrumpList');

		if(isset($this->_controller->request->params['pass'][0]) && $this->_controller->request->params['pass'][0] > 0){
			$this->_controller->loadModel('Topproject');
			$this->_controller->Topproject->recursive = 0;
			$options = array('conditions' => array('Topproject.id' => $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][0])));
			$topproject = $this->_controller->Topproject->find('first', $options);
		}

		$VarsArray['VarsArray'] = $breadcrumpList;

		if(is_array($VarsArray['VarsArray'])){
			foreach($VarsArray['VarsArray'] as $_key => $_VarsArray){

				$__thekey = $_VarsArray['param'];

				if(isset($this->_controller->request->params['pass'][$_key]) && $this->_controller->request->params['pass'][$_key] != null){
					$VarsArray['VarsArray'][$_key] = intval($this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][$_key]));
					$VarsArray[$__thekey] = intval($this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][$_key]));
					$this->_controller->request->$__thekey = intval($this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][$_key]));
				}
				else {
					$VarsArray['VarsArray'][$_key] = 0;
					$this->_controller->request->$__thekey = 0;
				}
			}
		}

		if($VarsArray['VarsArray'] === NULL) $VarsArray['VarsArray'] = array();

		$this->_controller->request->projectvars = $VarsArray;
		$this->_controller->set('VarsArray', $VarsArray['VarsArray']);
		return $VarsArray['VarsArray'];
	}

	public function Deliverynumber() {
		$this->_controller->loadModel('Deliverynumber');
		$this->_controller->Deliverynumber->recursive = 0;
		$deliverynumber = $this->_controller->Deliverynumber->find('first',array('conditions' => array('Deliverynumber.id' => $this->_controller->request->projectvars['VarsArray'][3])));
//		$dekra_deliverynumber = 'DEKRA '.$deliverynumber['Deliverynumber']['topproject_id'].$deliverynumber['Deliverynumber']['equipment_id'].$deliverynumber['Deliverynumber']['order_id'].$deliverynumber['Deliverynumber']['id'];
		$dekra_deliverynumber = $deliverynumber['Order']['auftrags_nr'];
		return $dekra_deliverynumber;
	}

	public function EditMenue() {

		$this->_controller->set('rel_menue', 'General');

		$editmenue = array();

		$editmenue[0] = array(
							'controller' => $this->_controller->request->params['controller'],
							'action' => 'edit',
							'parms' => $this->_controller->request->projectvars['VarsArray'],
							'id' => 'EditGeneral',
							'class' => 'active',
							'discription' => __('Show General infos',true),
							'rel' => 'General'
						);
		$editmenue[1] = array(
							'controller' => $this->_controller->request->params['controller'],
							'action' => 'edit',
							'parms' => $this->_controller->request->projectvars['VarsArray'],
							'id' => 'EditSpecify',
							'class' => 'deaktive',
							'discription' => __('Show Specify infos',true),
							'rel' => 'Specify'
						);
		$editmenue[2] = array(
							'controller' => $this->_controller->request->params['controller'],
							'action' => 'edit',
							'parms' => $this->_controller->request->projectvars['VarsArray'],
							'id' => 'TestingArea',
							'class' => 'deaktive',
							'discription' => __('Show Testing area',true),
							'rel' => 'TestingArea'
						);

		$lastURL = explode('/',$this->_controller->Session->read('lastURL'));

		if($lastURL[1] == 'editevalution'){
			$editmenue[0]['class'] = 'deaktive';
			$editmenue[2]['class'] = 'active';
		}

		if($this->_controller->request->params['action'] == 'editevalution'){
			$editmenue[0]['class'] = 'deaktive';
			$editmenue[1]['class'] = 'deaktive';
			$editmenue[2]['class'] = 'deaktive';
			$editmenue[3] = array(
							'controller' => $this->_controller->request->params['controller'],
							'action' => 'editevalution',
							'parms' => $this->_controller->request->projectvars['VarsArray'],
							'id' => 'Examination',
							'class' => 'active',
							'discription' => __('Examination',true),
							'rel' => 'Examination'
						);
		}

		if(isset($this->_controller->request->data['Reportnumber']['rel_menue'])){
			if($this->_controller->request->data['Reportnumber']['rel_menue'] == 'General'){
				$editmenue[0]['class'] = 'active';
				$editmenue[1]['class'] = 'deaktive';
				$editmenue[2]['class'] = 'deaktive';
			}
			if($this->_controller->request->data['Reportnumber']['rel_menue'] == 'Specify'){
				$editmenue[0]['class'] = 'deaktive';
				$editmenue[1]['class'] = 'active';
				$editmenue[2]['class'] = 'deaktive';
			}
			if($this->_controller->request->data['Reportnumber']['rel_menue'] == 'TestingArea'){
				$editmenue[0]['class'] = 'deaktive';
				$editmenue[1]['class'] = 'deaktive';
				$editmenue[2]['class'] = 'active';
			}
		}

		if(isset($this->_controller->request->data['rel_menue'])){

			$this->_controller->set('rel_menue', $this->_controller->request->data['rel_menue']);

			if($this->_controller->request->data['rel_menue'] == 'General'){
				$editmenue[0]['class'] = 'active';
				$editmenue[1]['class'] = 'deaktive';
				$editmenue[2]['class'] = 'deaktive';
			}
			if($this->_controller->request->data['rel_menue'] == 'Specify'){
				$editmenue[0]['class'] = 'deaktive';
				$editmenue[1]['class'] = 'active';
				$editmenue[2]['class'] = 'deaktive';
			}
			if($this->_controller->request->data['rel_menue'] == 'TestingArea'){
				$editmenue[0]['class'] = 'deaktive';
				$editmenue[1]['class'] = 'deaktive';
				$editmenue[2]['class'] = 'active';
			}
		}

		$this->_controller->set('editmenue', $editmenue);
	}
/*
	public function Subdivisions($topprojects) {

		$breadcrumpList = Configure::read('breadcrumpList');
		$SubdivisionMax = Configure::read('SubdivisionMax');
		$this->_controller->loadModel('Deliverynumber');
		$this->_controller->Deliverynumber->recursive = -1;

		$ShowOrderQuickSearch = 0;

		foreach($topprojects as $_key => $_topprojects){

			$SessionSubdevision = $this->_controller->Session->read('TopprojectSubdivision.' . $_topprojects['Topproject']['id']);

//			$topprojects[$_key]['Topproject']['subdivision_standard'] = $topprojects[$_key]['Topproject']['subdivision'];

//			if($_topprojects['Topproject']['subdivision'] == $SubdivisionMax && !isset($SessionSubdevision)) continue;

			$deliverynumber = $this->_controller->Deliverynumber->find('first',array('conditions' => array('Deliverynumber.topproject_id' => $_topprojects['Topproject']['id'])));

			if(count($deliverynumber) == 0){

				$this->_controller->loadModel('EquipmentType');
				$this->_controller->loadModel('Equipment');
				$this->_controller->loadModel('Order');

				$EquipmentTypeData = array(
									'topproject_id' => $_topprojects['Topproject']['id'],
									'discription' => 'EquipmentType'
									);
				$this->_controller->EquipmentType->save($EquipmentTypeData);
				$EquipmentTypeId = $this->_controller->EquipmentType->getInsertID();

				$EquipmentData = array(
									'topproject_id' => $_topprojects['Topproject']['id'],
									'equipment_type_id' => $EquipmentTypeId,
									'discription' => 'Equipment'
									);
				$this->_controller->Equipment->save($EquipmentData);
				$EquipmentId = $this->_controller->Equipment->getInsertID();

				$OrderData = array(
									'topproject_id' => $_topprojects['Topproject']['id'],
									'equipment_id' =>$EquipmentId,
									'equipment_type_id' =>$EquipmentTypeId,
									'auftrags_nr' => 'Order'
									);
				$this->_controller->Order->save($OrderData);
				$OrderId = $this->_controller->Order->getInsertID();

				$DeliverynumberData = array(
									'topproject_id' => $_topprojects['Topproject']['id'],
									'equipment_id' =>$EquipmentId,
									'equipment_type_id' =>$EquipmentTypeId,
									'order_id' => $OrderId,
									'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
									);
				$this->_controller->Deliverynumber->save($DeliverynumberData);
				$DeliverynumberId = $this->_controller->Deliverynumber->getInsertID();

				$deliverynumber = $this->_controller->Deliverynumber->find('first',array('conditions' => array('Deliverynumber.id' => $DeliverynumberId)));
			}
			if(count($deliverynumber) == 1){

				if(isset($SessionSubdevision) && $SessionSubdevision > 0){
					$topprojects[$_key]['Topproject']['subdivision'] = $SessionSubdevision;
					$_topprojects['Topproject']['subdivision'] = $SessionSubdevision;
				}

				$topprojects[$_key]['Deliverynumber'] = $deliverynumber['Deliverynumber'];
			}
		}

		$this->_controller->set('ShowOrderQuickSearch',$ShowOrderQuickSearch);

		return $topprojects;
	}
*/
	public function SubdivisionBreads($breads) {

		$breadcrumpList = Configure::read('breadcrumpList');
		$SubdivisionMax = Configure::read('SubdivisionMax');

		switch($this->_controller->request->params['controller']) {
			case ('equipmenttypes'):
				switch($this->_controller->request->params['action']) {
					case('view'):
					switch($this->_controller->request->TopprojectSubdivision) {
						case(3):
						$breads[1]['action'] = 'view';
						$breads[1]['pass'] = $breads[2]['pass'];
						unset($breads[2]);
						break;
					}
				break;
				}
			break;
			case ('equipments'):
				switch($this->_controller->request->params['action']) {
					case('view'):
					switch($this->_controller->request->TopprojectSubdivision) {
						case(2):
						$breads[1]['controller'] = 'equipments';
						$breads[1]['action'] = 'view';
						$breads[1]['pass'] = $breads[3]['pass'];
						unset($breads[2]);
						unset($breads[3]);
						break;

						case(3):
						$breads[1]['action'] = 'view';
						$breads[1]['pass'] = $breads[2]['pass'];
						unset($breads[2]);
						break;
					}
					break;
				}
			break;
			case ('reportnumbers'):
				switch($this->_controller->request->params['action']) {
					case('show'):
					case('edit'):
					case('editevalution'):
					case('view'):
					case('images'):
					case('videos'):
					case('files'):
					case('sign'):
					case('signExaminer'):
					case('signSupervisor'):
					case('signThirdPart'):
					switch($this->_controller->request->TopprojectSubdivision) {
						case(4):
						break;
						case(3):
						$breads[1]['controller'] = $breads[2]['controller'];
						$breads[1]['action'] = $breads[2]['action'];
						$breads[1]['pass'] = $breads[2]['pass'];
						unset($breads[2]);
						break;
						case(2):
						$breads[1]['controller'] = $breads[3]['controller'];
						$breads[1]['action'] = $breads[3]['action'];
						$breads[1]['pass'] = $breads[3]['pass'];
						unset($breads[2]);
						unset($breads[3]);
						break;
						case(1):
						$breads[1]['controller'] = $breads[4]['controller'];
						$breads[1]['action'] = $breads[4]['action'];
						$breads[1]['pass'] = $breads[4]['pass'];
						unset($breads[2]);
						unset($breads[3]);
						unset($breads[4]);
						break;
					}
					break;
/*
					case('show'):
					switch($this->_controller->request->TopprojectSubdivision) {
						case (2):
						$breads[1]['controller'] = $breads[3]['controller'];
						$breads[1]['action'] = $breads[3]['action'];
						$breads[1]['pass'] = $breads[3]['pass'];
						unset($breads[2]);
						unset($breads[3]);
						break;
						case (3):
						$breads[1]['controller'] = $breads[2]['controller'];
						$breads[1]['action'] = $breads[2]['action'];
						$breads[1]['pass'] = $breads[2]['pass'];
						unset($breads[2]);
						break;
					}
					break;
*/
					case('index'):
					switch($this->_controller->request->TopprojectSubdivision) {
						case(3):
						$breads[1]['controller'] = $breads[2]['controller'];
						$breads[1]['action'] = $breads[2]['action'];
						$breads[1]['pass'] = $breads[2]['pass'];
						unset($breads[2]);
						break;
						case (2):
						$breads[1]['controller'] = $breads[3]['controller'];
						$breads[1]['action'] = $breads[3]['action'];
						$breads[1]['pass'] = $breads[3]['pass'];
						unset($breads[2]);
						unset($breads[3]);

						break;
						case (1):
						$breads[1]['controller'] = $breads[4]['controller'];
						$breads[1]['action'] = $breads[4]['action'];
						$breads[1]['pass'] = $breads[4]['pass'];
						unset($breads[2]);
						unset($breads[3]);
						unset($breads[4]);
						break;
					}
					break;
			}
			break;
		}

		return $breads;
	}

	public function SubdivisionCorrection($controller,$action) {

		$subdevision_link = array(
				'controller' => $controller,
				'action' => $action,
			);

		$subdevision_options = array(
				'1' => __('Einstufig'),
				'2' => __('Zweistufig'),
				'3' => __('Dreistufig'),
				'4' => __('Vierstufig')
			);

		foreach($subdevision_options as $_key => $_subdevision_options){
			if($_key > $this->_controller->request->TopprojectSubdivisionStandard){
				unset($subdevision_options[$_key]);
			}
		}

		$this->_controller->set('subdevision_options', $subdevision_options);
		$this->_controller->set('subdevision_link', $subdevision_link);

	}

	public function SetSessionForPaging() {

		if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
//			return false;
		}
		if(isset($this->_controller->request->params['named']['page'])){
			$this->_controller->Session->write(
											'paging.'.
											$this->_controller->request->params['controller'].'.'.
											$this->_controller->request->params['action'],
										array(
											'page' => $this->_controller->request->params['named']['page'],
											'pass' => $this->_controller->request->params['pass'],
											'order' => $this->_controller->request->params['named'],
											'time' => time()
											)
										);
		}
	}

	public function SetTableAnchor() {

		if($this->_controller->Session->check('paging') === false) return;

		$Paging = $this->_controller->Session->read('paging');

		if(!isset($Paging['examiners']['index'])) return;
		if(!isset($Paging['examiners']['index']['anchor'])) return;
		if($Paging['examiners']['index']['anchor'] == 0) return;

		$this->_controller->set('TableAnchor', $Paging['examiners']['index']['anchor']);

	}

	public function GetSessionForPaging() {

		if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true) return false;

		if(isset($this->_controller->request->params['named']['page'])){
		} else {
			if($this->_controller->Session->check('paging.'.$this->_controller->request->params['controller'].'.'.$this->_controller->request->params['action'])){

				if(!isset($this->_controller->request->params['named']['page'])){

					$this->_controller->request->params['named']['page'] = $this->_controller->Session->read('paging.'.$this->_controller->request->params['controller'].'.'.$this->_controller->request->params['action'].'.page');
					$this->_controller->request->params['pass'] = $this->_controller->Session->read('paging.'.$this->_controller->request->params['controller'].'.'.$this->_controller->request->params['action'].'.pass');
					$this->_controller->Session->delete('paging.'.$this->_controller->request->params['controller'].'.'.$this->_controller->request->params['action']);

				}
			}
		}
	}

	public function InfiniteScrollPaging($render_view) {

		if(Configure::check('InfiniteScroll') === false) return;
		if(Configure::read('InfiniteScroll') != true) return;

		$is_infinite = 0;

		if(isset($this->_controller->request->query['is_infinite']) && $this->_controller->request->query['is_infinite'] == 1) $is_infinite = 1;
		if(isset($this->_controller->request->data['is_infinite']) && $this->_controller->request->data['is_infinite'] == 1) $is_infinite = 1;


		$paging = $this->_controller->params['paging'];
		$paging_key = key($paging);
		$paging = $paging[$paging_key];
		$page = $paging['page'];

		$next_page = intval($page);
		$prev_page = intval($page);
		$next_page++;
		$prev_page--;

		$paging['start'] = $prev_page * $paging['limit'] + 1;
		$paging['end'] = $prev_page * $paging['limit'] + $paging['current'];
		$paging['tr_marker'] = 'page_' . $page;

		$InfinitePaging['Next'] = null;
		$InfinitePaging['Prev'] = null;

		$InfinitePaging['Prev']['Desc'] = __('previous');
		$InfinitePaging['Next']['Desc'] = __('next');

		$InfinitePaging['Prev']['Controller'] = $this->_controller->params['controller'];
		$InfinitePaging['Next']['Controller'] = $this->_controller->params['controller'];


		$InfinitePaging['Prev']['Action'] = $this->_controller->params['action'];
		$InfinitePaging['Next']['Action'] = $this->_controller->params['action'];

		$InfinitePaging['Next']['Page'] = $next_page;
		$InfinitePaging['Prev']['Page'] = $prev_page;

		if($paging['page'] == 1){
			$InfinitePaging['Prev']['Controller'] = false;
			$InfinitePaging['Prev']['Action'] = false;
			$InfinitePaging['Prev']['Page'] = false;
		}

		if($paging['page'] == $paging['pageCount']){
			$InfinitePaging['Next']['Controller'] = false;
			$InfinitePaging['Next']['Action'] = false;
			$InfinitePaging['Next']['Page'] = false;
		}

		$pageclass = null;
		for($i = 1; $i <= $paging['pageCount']; $i++) {
			if($i == $paging['page']){
				$pageclass = 'current_page';
				$paging['current_page'] = $i;
			}
			if($i == $paging['page'] + 1){
				$pageclass = 'next_page';
				$paging['next_page'] = $i;
			}
			if($i == $paging['page'] - 1){
				$pageclass = 'prev_page';
				$paging['prev_page'] = $i;
			}
			$paging['pages'][$i]['class'] = $pageclass;
			$paging['pages'][$i]['value'] = $i;
			$paging['pages'][$i]['url'] = Router::url(array_merge(array('controller' => $this->_controller->params['controller'],'action' => $this->_controller->params['action'] , 'page' => $i),$this->_controller->request->projectvars['VarsArray']));
			$pageclass = null;
		}

		$this->_controller->set('InfinitePaging', $InfinitePaging);
		$this->_controller->set('paging', $paging);

		if($is_infinite == 1) $this->_controller->render($render_view,'blank');
	}

	public function ResetSessionForPaging() {
		$this->_controller->Session->delete('paging.'.$this->_controller->request->params['controller']);
	}

	public function ResetAllSessionForPaging() {
		$this->_controller->Session->delete('paging');
	}

	public function CascadeCreateBreadcrumblist($breads,$CascadeForBread) {

		$CascadeForBread = array_reverse($CascadeForBread);

		foreach($CascadeForBread as $_key => $_CascadeForBread){
			$breads[] = array('discription' => $_CascadeForBread['Cascade']['discription'],'controller' => 'cascades','action' => 'index','pass' => $_CascadeForBread['Cascade']['topproject_id'] . '/' . $_CascadeForBread['Cascade']['id'] . '/' . $this->_controller->request->orderID);
		}

		return $breads;
	}

	public function CascadeGetBreads($data) {

		$output = null;

		if(is_array($data)) $output = $this->CascadeGetParent($data);

		if(is_int($data)){
			$this->_controller->loadModel('Cascade');
			$this->_controller->Cascade->recursive = -1;
			$Cascade = $this->_controller->Cascade->find('all',array('conditions'=>array('Cascade.id'=>$data)));
			$output = $this->CascadeGetParent($Cascade);
		}

		return $output;
	}

	public function CascadeGetParentId($id) {

			$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $id)));

			if(count($Cascade) == 0) return false;

			return $Cascade['Cascade']['parent'];

	}

	public function CascadeGetParent($data) {

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$output = array();

		if(is_array($data)){

			end($data);
			if(isset($data[key($data)]['Cascade']['parent'])) $parent = $data[key($data)]['Cascade']['parent'];
			else $parent = 0;

		} else {
			$id = $data;
			$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $id)));

			if(count($Cascade) == 0) return 0;

			$parent = $Cascade['Cascade']['parent'];

		}

		$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $parent)));

		if(count($Cascade) > 0){
			$data[] = $Cascade;
			$data = $this->CascadeGetBreads($data);
		}

		return $data;
	}

	public function NatSortAndStringAndUrl($data){

		krsort($data['array']);

		foreach ($data['array'] as $key => $value) {

			$href = Router::url(array_merge(array('action' => 'move'),$this->_controller->request->projectvars['VarsArray']));
			$data['bread_array'][] = '<a href="' . $href . '" class="move_navigation" data-cascade="'.$value['Cascade']['id'].'">'.$value['Cascade']['discription'].'</a>';

		}

		$results = Hash::extract($data['array'], '{n}.Cascade.discription');

		$data['string'] = implode(' > ',$results);
		$data['bread'] = implode(' > ',$data['bread_array']);

		return $data;
	}

	public function CascadeGetParentList($id,$data) {

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('first',array('conditions' => array('Cascade.id' => $id)));
		$data[count($data)] = $Cascade;

		if(!isset($Cascade['Cascade']) || $Cascade['Cascade']['level'] == 0) return $data;
		elseif($Cascade['Cascade']['level'] > 0){
			 $data = $this->CascadeGetParentList($Cascade['Cascade']['parent'],$data);
		}

		return $data;
	}

	public function ResetAndBackupAllExaminerSessions()
    {
        //Backup Examiner.testingcomp
        if ($this->_controller->Session->check('Examiner.testingcomp')) {
            $examiner_testingcomp_id_backup = $this->_controller->Session->read('Examiner.testingcomp');
            $this->_controller->Session->write('Backup.Examiner.testingcomp', $examiner_testingcomp_id_backup);
            $this->_controller->Session->delete('Examiner.testingcomp');
        }

        //Backup ConditionsExaminer
        if ($this->_controller->Session->check('ConditionsExaminer')) {
            $conditions_examiner_backup = $this->_controller->Session->read('ConditionsExaminer');
            $this->_controller->Session->write('Backup.ConditionsExaminer', $conditions_examiner_backup);
            $this->_controller->Session->delete('ConditionsExaminer');
        }

        //Backup SortExaminerTable
        if ($this->_controller->Session->check('SortExaminerTable')) {
            $sort_examiner_table_backup = $this->_controller->Session->read('SortExaminerTable');
            $this->_controller->Session->write('Backup.SortExaminerTable', $sort_examiner_table_backup);
            $this->_controller->Session->delete('SortExaminerTable');
        }
    }

    public function RestoreAllExaminerSessions()
    {
        //Restore Examiner.testingcomp
        if ($this->_controller->Session->check('Backup.Examiner.testingcomp')) {
            $examiner_testingcomp_id_backup = $this->_controller->Session->read('Backup.Examiner.testingcomp');
            $this->_controller->Session->write('Examiner.testingcomp', $examiner_testingcomp_id_backup);
            $this->_controller->Session->delete('Backup.Examiner.testingcomp');
        }

        //Restore ConditionsExaminer
        if ($this->_controller->Session->check('Backup.ConditionsExaminer')) {
            $conditions_examiner_backup = $this->_controller->Session->write('Backup.ConditionsExaminer');
            $this->_controller->Session->write('ConditionsExaminer', $conditions_examiner_backup);
            $this->_controller->Session->delete('Backup.ConditionsExaminer');
        }

        //SortExaminerTable
        if ($this->_controller->Session->check('Backup.SortExaminerTable')) {
            $sort_examiner_table_backup = $this->_controller->Session->read('Backup.SortExaminerTable');
            $this->_controller->Session->write('SortExaminerTable', $sort_examiner_table_backup);
            $this->_controller->Session->delete('Backup.SortExaminerTable');
        }
    }

	public function CascadeGetTree($id) {

		$Cascade = $this->_controller->Cascade->find('first',array('fields' => array('discription'),'conditions'=>array('Cascade.id'=>$id)));

		$Output['discription'] = $Cascade['Cascade']['discription'];
		$Output['children'] = array();

		$options['fields'] = array('id');
		$options['conditions']['Cascade.parent'] = $id;

		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',$options);

		if(count($Cascade) == 0) return $Output;

		foreach ($Cascade as $key => $value) {
			$Output['children'][$key] = $this->CascadeGetTree($key,'recursive');
		}

		return $Output;
	}

	public function CascadeGetNextChildren($id,$fields) {

		if(!is_array($fields)) return false;
		if(count($fields) == 0) return false;

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',array('fields' => $fields,'conditions'=>array('Cascade.parent'=>$id)));
	
		if($this->_controller->Auth->user('roll_id') == 1) return $Cascade;
		$ParentList = array();
		$CascadeOutput = array();

		$FromTestingcomp = $this->CascadeGetFromTestingcomp();

		if(count($FromTestingcomp) == 0) return array();

		foreach($FromTestingcomp as $_key => $_data){
			$ParentList = array_merge($ParentList, $this->CascadeGetParentList($_key,array()));
		}

		if(count($ParentList) == 0) return array();

		foreach($ParentList as $_key => $_data){

			if(!isset($_data['Cascade'])) continue;

			if(isset($Cascade[$_data['Cascade']['id']])) $CascadeOutput[$_data['Cascade']['id']] = $Cascade[$_data['Cascade']['id']];

		}

		if(count($CascadeOutput) == 0) return false;

		return $CascadeOutput;
	}

	public function CascadeGetAllChildren($id,$Output) {

		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',array('fields' => array('id'),'conditions'=>array('Cascade.parent'=>$id)));

		if(count($Cascade) == 0) return $Output;

		$Output = array_merge($Output, $Cascade);

		foreach ($Cascade as $key => $value) {
			$Output = $this->CascadeGetAllChildren($value,$Output);
		}

		return $Output;
	}

	public function CascadeGetChildrenList($id) {

		$output[] = intval($id);

		$this->_controller->loadModel('Cascade');
		$this->_controller->Cascade->recursive = -1;
		$Cascade = $this->_controller->Cascade->find('list',array('fields' => array('id'),'conditions'=>array('Cascade.parent'=>$id)));
		$this->_controller->loadModel('TestingcompsCascades');
		$CascadeTestingcompIds = $this->_controller->TestingcompsCascades->find('list',array('fields'=>array('testingcomp_id','cascade_id'),'conditions'=>array('testingcomp_id'=>$this->_controller->Auth->user('testingcomp_id'),'cascade_id'=>$Cascade)));
		$Cascade = array();
		$Cascade = $CascadeTestingcompIds;
			if(count($Cascade) > 0){
			foreach($Cascade as $_key => $_Cascade){
				$output_2 = $this->CascadeGetChildrenList($_Cascade);
				foreach($output_2 as $__key => $__output_2){
					$output[] = $__output_2;
				}
			}
		}

		return $output;
	}

	public function CascadeGetLastChilds($ids) {

		$Cascade = $this->_controller->Cascade->find('all',array('conditions' => array('Cascade.id'=>$ids)));

		if(count($Cascade) == 0) return array();

		$Levels = Hash::extract($Cascade, '{n}.Cascade.level');
		$MaxLevel = max($Levels);

		foreach($Cascade as $_key => $_data){
			if($_data['Cascade']['level'] < $MaxLevel) unset($Cascade[$_key]);
		}

		return ($Cascade);
	}

	public function OrdersGetFromTestingcomp() {
		$this->_controller->loadModel('Order');
		$this->_controller->loadModel('CascadesOrder');
		$OrderIds = $this->_controller->Order->OrdersTestingcomp->find('list',array('fields' => array('order_id','order_id'), 'conditions' => array('OrdersTestingcomp.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'))));
		return $OrderIds;
	}

	public function CascadeGetFrom($CascadeTree) {

		$CascadeIDs = array();
		$this->_controller->loadModel('TestingcompsCascades');
/*
$this->_controller->loadModel('Order');
$this->_controller->loadModel('OrdersTestingcomps');

$this->_controller->Order->reqursive = -1;
$Orders = $this->_controller->Order->find('all');
$InserData['testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');

foreach($Orders as $_key => $_data){
$InserData['order_id'] = $_data['Order']['id'];
pr($InserData);
$this->_controller->OrdersTestingcomps->create();
if($this->_controller->OrdersTestingcomps->save($InserData))pr('Okay');
else pr($InserData);
}
die('tot');
*/
		$Options['TestingcompsCascades.testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');
		foreach($CascadeTree as $_key => $_data){

			if($_data['orders'] > 0) continue;

			$Options['TestingcompsCascades.cascade_id'] = $_data['id'];
			$TestingcompsCascades = $this->_controller->TestingcompsCascades->find('first',array('conditions' => $Options));

			if(count($TestingcompsCascades) == 1) $CascadeIDs[$TestingcompsCascades['TestingcompsCascades']['cascade_id']] = $TestingcompsCascades['TestingcompsCascades']['cascade_id'];
		}

		return $CascadeIDs;
	}

	public function CascadeGetFromTestingcomp() {
		$this->_controller->loadModel('Order');
		$this->_controller->loadModel('CascadesOrder');

		$OrderIds = $this->_controller->Order->OrdersTestingcomp->find('list',array('fields' => array('order_id','order_id'), 'conditions' => array('OrdersTestingcomp.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'))));
		$CascadeIds = $this->_controller->CascadesOrder->find('list',array('fields' => array('cascade_id','cascade_id'), 'conditions' => array('CascadesOrder.order_id' => $OrderIds)));
		return $CascadeIds;
	}

	public function RemoveLastElementByGroup($data) {

		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];


		$this->_controller->loadModel('CascadegroupsCascade');
		$CascadegroupsCascade = $this->_controller->CascadegroupsCascade->find('first',array('conditions' => array('CascadegroupsCascade.cascade_id' => $cascadeID)));

		if(count($CascadegroupsCascade) == 0) return $data;

		array_pop($data);

		return $data;
	}

	public function SelectLandingPageAutoload() {

		$IsConfig = array('ContactManagerWidget','MonitoringManagerWidget','ExpeditingManagerWidget','ProgressesEnabledWidget','CertifcateManagerWidget','DeviceManagerWidget');

		// Default
		$Data['large'] = Router::url(array_merge(array('controller' => 'topprojects','action' => 'index'),array()));

		$this->_controller->loadModel('Landingpage');
		$Landingpage = $this->_controller->Landingpage->find('first',array('conditions' => array('Landingpage.user_id' => AuthComponent::user('id'))));

		if(count($Landingpage) == 0) return $Data;

		// Seite für großes Fenster
		foreach ($Landingpage['LandingpagesData'] as $key => $value) {

			if($value['position'] != 1) continue;

			if($value['position'] == 1){

				$Data['large'] = Router::url(array_merge(array('controller' => $value['controller'],'action' => $value['action']),array()));
				break;
			}
		}

		// Seiten für Widgets
		foreach ($Landingpage['LandingpagesData'] as $key => $value) {

			if($value['position'] != 2) continue;

			$Data['widget'][$value['modul']]['url']  = Router::url(array_merge(array('controller' => $value['controller'],'action' => $value['action'])));
			$Data['widget'][$value['modul']]['controller'] = $value['controller'];
			$Data['widget'][$value['modul']]['action'] = $value['action'];
			$Data['widget'][$value['modul']]['place'] = $value['place'];
			$Data['widget'][$value['modul']]['option'] = $value['options'];

			if(!empty($value['config'])) $Data['widget'][$value['modul']]['config'] = $value['config'];

		}

		if(!isset($Data['widget'])) return $Data;

		foreach ($Data['widget'] as $key => $value) {

			if($this->_controller->Autorisierung->AclCheck(array(0 => $value['controller'],1 => $value['action'])) == false){
				unset($Data['widget'][$key]);
				continue;
			}

			if(!isset($value['config'])) continue;

			if(Configure::check($value['config']) == false){
				unset($Data['widget'][$key]);
				continue;
			}

			if(Configure::read($value['config']) == false){
				unset($Data['widget'][$key]);
				continue;
			}
		}

		return $Data;

	}

	public function SaveLandingPageValue(){

		if(!isset($this->_controller->request->data['Landingpage'])) return true;

		$this->__SaveLandingPageValueLarge();
		$this->__SaveLandingPageValueWidget();
		$this->__DeleteLandingPageValueLarge();
		$this->__DeleteLandingPageValueWidget();

		return true;
	}

	protected function __DeleteLandingPageValueWidget(){

		if(!empty($this->_controller->request->data['Landingpage']['widget'])) return true;

		$Landingpage = $this->_controller->Landingpage->find('first',array('conditions' => array('Landingpage.user_id' => AuthComponent::user('id'))));

		$this->_controller->Landingpage->LandingpagesData->deleteAll(array(
			'LandingpagesData.landingpages_id' => $Landingpage['Landingpage']['id'],
			'LandingpagesData.position' => 2,
			),
			false
		);

	}

	protected function __DeleteLandingPageValueLarge(){

		if(!empty($this->_controller->request->data['Landingpage']['large'])) return true;

		$Landingpage = $this->_controller->Landingpage->find('first',array('conditions' => array('Landingpage.user_id' => AuthComponent::user('id'))));

		$this->_controller->Landingpage->LandingpagesData->deleteAll(array(
			'LandingpagesData.landingpages_id' => $Landingpage['Landingpage']['id'],
			'LandingpagesData.position' => 1,
			),
			false
		);

	}

	protected function __SaveLandingPageValueLarge(){

		if(!isset($this->_controller->request->data['Landingpage']['large'])) return true;
		if(empty($this->_controller->request->data['Landingpage']['large'])) return true;

		$Landingpage = $this->_controller->Landingpage->find('first',array('conditions' => array('Landingpage.user_id' => AuthComponent::user('id'))));

		$LandingpagesData = $this->_controller->Landingpage->LandingpagesData->find('first',array(
			'conditions' => array(
				'LandingpagesData.landingpages_id ' => $Landingpage['Landingpage']['id'],
				'LandingpagesData.landingpages_template_id' => $this->_controller->request->data['Landingpage']['large']
				)
			)
		);

		if(count($LandingpagesData) == 1) return;

		$LandingpagesTemplate = $this->_controller->LandingpagesTemplate->find('first',array(
			'conditions' => array(
				'LandingpagesTemplate.id' => $this->_controller->request->data['Landingpage']['large'],
				'LandingpagesTemplate.position' => 1
				)
			)
		);

		$LandingpagesTemplate['LandingpagesTemplate']['landingpages_id'] = $Landingpage['Landingpage']['id'];
		$LandingpagesTemplate['LandingpagesTemplate']['landingpages_template_id'] = $LandingpagesTemplate['LandingpagesTemplate']['id'];

		unset($LandingpagesTemplate['LandingpagesTemplate']['id']);

		$LandingpagesData = $this->_controller->Landingpage->LandingpagesData->find('first',array(
			'conditions' => array(
				'LandingpagesData.landingpages_id ' => $Landingpage['Landingpage']['id'],
				'LandingpagesData.position' => 1
				)
			)
		);

		if(count($LandingpagesData) == 1) $this->_controller->Landingpage->LandingpagesData->delete($LandingpagesData['LandingpagesData']['id']);

		$this->_controller->Landingpage->LandingpagesData->create();
		$this->_controller->Landingpage->LandingpagesData->save($LandingpagesTemplate['LandingpagesTemplate']);

		return true;

	}

	protected function __SaveLandingPageValueWidget(){

		if(!isset($this->_controller->request->data['Landingpage']['widget'])) return true;
		if(empty($this->_controller->request->data['Landingpage']['widget'])) return true;
		if(count($this->_controller->request->data['Landingpage']['widget']) == 0) return true;

		$Landingpage = $this->_controller->Landingpage->find('first',array('conditions' => array('Landingpage.user_id' => AuthComponent::user('id'))));

		$this->_controller->Landingpage->LandingpagesData->deleteAll(array(
			'LandingpagesData.landingpages_id' => $Landingpage['Landingpage']['id'],
			'LandingpagesData.position' => 2,
			),
			false
		);

		foreach ($this->_controller->request->data['Landingpage']['widget'] as $key => $value) {

			$LandingpagesTemplate = $this->_controller->LandingpagesTemplate->find('first',array(
				'conditions' => array(
					'LandingpagesTemplate.id' => $value
					)
				)
			);

			$LandingpagesTemplate['LandingpagesTemplate']['landingpages_id'] = $Landingpage['Landingpage']['id'];
			$LandingpagesTemplate['LandingpagesTemplate']['landingpages_template_id'] = $LandingpagesTemplate['LandingpagesTemplate']['id'];

			unset($LandingpagesTemplate['LandingpagesTemplate']['id']);

			$this->_controller->Landingpage->LandingpagesData->create();
			$this->_controller->Landingpage->LandingpagesData->save($LandingpagesTemplate['LandingpagesTemplate']);

		}

		return true;

	}

	public function SelectLandingPageValue() {

		$this->_controller->loadModel('Landingpage');
		$Landingpage = $this->_controller->Landingpage->find('first',array('conditions' => array('Landingpage.user_id' => AuthComponent::user('id'))));

		if(count($Landingpage) == 0){
			$InsertLandingpage = array(
				'user_id' => AuthComponent::user('id'),
				'testingcomp_id' => AuthComponent::user('testingcomp_id'),
			);

			$this->_controller->Landingpage->create();
			$this->_controller->Landingpage->save($InsertLandingpage);

		}

		$Data = array();

		if(count($Landingpage) == 0) return $Data;

		// Seite für großes Fenster
		foreach ($Landingpage['LandingpagesData'] as $key => $value) {

			if($value['position'] != 1) continue;

			if($value['position'] == 1){

				$Data['large'] = $value['landingpages_template_id'];
				break;
			}
		}

		if(!isset($Data['large'])) $Data['large'] = array();

		// Seiten für Widgets
		foreach ($Landingpage['LandingpagesData'] as $key => $value) {

			if($value['position'] != 2) continue;


			if($this->_controller->Autorisierung->AclCheck(array(0 => $value['controller'],1 => $value['action'])) == false){
				unset($Data['widget'][$key]);
				continue;
			}

			$Data['widget'][$value['id']] = $value['landingpages_template_id'];

		}

		if(!isset($Data['widget'])) $Data['widget'] = array();

		return $Data;

	}

	public function SelectLandingPageLarge(){

		$Data = array();
		$locale = $this->_controller->Lang->Discription();

		$LandingpagesTemplate = $this->_controller->LandingpagesTemplate->find('all',array(
			'order' => array($locale),
			'conditions' => array(
				'LandingpagesTemplate.position' => 1
				)
			)
		);

		foreach ($LandingpagesTemplate as $key => $value) {

			if($this->_controller->Autorisierung->AclCheck(array(0 => $value['LandingpagesTemplate']['controller'],1 => $value['LandingpagesTemplate']['action'])) == false) continue;

			$Data[$value['LandingpagesTemplate']['id']] = $value['LandingpagesTemplate'][$locale];

		}

		return $Data;
	}

	public function SelectLandingPageWidgets(){

		$Data = array();
		$locale = $this->_controller->Lang->Discription();

		$LandingpagesTemplate = $this->_controller->LandingpagesTemplate->find('all',array(
			'order' => array($locale),
			'conditions' => array(
				'LandingpagesTemplate.position' => 2
				)
			)
		);

		foreach ($LandingpagesTemplate as $key => $value) {

			if($this->_controller->Autorisierung->AclCheck(array(0 => $value['LandingpagesTemplate']['controller'],1 => $value['LandingpagesTemplate']['action'])) == false) continue;
			if(!empty($value['LandingpagesTemplate']['config'])){
				if(Configure::check($value['LandingpagesTemplate']['config']) == true && Configure::read($value['LandingpagesTemplate']['config']) == true) {
					$Data[$value['LandingpagesTemplate']['id']] = $value['LandingpagesTemplate'][$locale];
				}
			} else {
				$Data[$value['LandingpagesTemplate']['id']] = $value['LandingpagesTemplate'][$locale];
			}
		}

		return $Data;
	}
}
