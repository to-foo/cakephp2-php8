<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('Sanitize', 'Utility');
/**
* Reportnumbers Controller
*
*
* @property Rest $Reportnumber
*/
class RestsController extends AppController {

	public $components = array('Auth','Acl','Csv','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Search','Xml','Data','Drops','RequestHandler','Image','Pdf','Qualification','Additions','Formating','Testinstruction','Repairtracking','Rest');
	public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html','Additions');
	public $layout = 'ajax';
	protected $writeprotection = false;

	// Das ist ein Testkommentar für GIT

	function beforeFilter() {
		$this->Navigation->GetSessionForPaging();

		if($this->RequestHandler->isAjax()) {
			if (!$this->Auth->login()) {
				header('Requires-Auth: 1');
			}
		}

		if (!$this->Auth->login()) {
			//			$this->redirect(array('controller' => 'users', 'action' => 'logout'));
		}

		// muss man noch ne Funkton draus machen
		$Auth = $this->Session->read('Auth');

		/*
		if(!($Auth['User']['id'])){
		$this->redirect(array('controller' => 'users', 'action' => 'logout'));
	}
	*/
	App::import('Vendor', 'Authorize');
	App::uses('Folder', 'Utility');
	App::uses('File', 'Utility');
	$this->loadModel('User');
	$this->loadModel('Topproject');
	$this->loadModel('Testingcomp');
	//	$this->Autorisierung->Protect();

	$this->Lang->Choice();
	$this->Lang->Change();
	$this->Navigation->ReportVars();

	$noAjaxIs = 0;
	$noAjax = array('image','pdf','printweldlabel','printqrcode','quicksearch','getfile','export','printresultcsv','printrevisions','modulchilddata','testinstruction');

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

	$lang = $this->Lang->Discription();
	$this->request->lang = $lang;

	//	$this->Reportnumber->Report->Reportlock->recursive = -1;
	//$this->writeprotection = 0 < $this->Reportnumber->Report->Reportlock->find('count', array('conditions'=>array('Reportlock.topproject_id'=>$this->request->projectID, 'Reportlock.report_id'=>$this->request->reportID)));
	$this->set('writeprotection', $this->writeprotection);
	$this->set('lang', $this->Lang->Choice());
	$this->set('selected', $this->Lang->Selected());
	$this->set('login_info', $this->Navigation->loggedUser());
	$this->set('lang_choise', $this->Lang->Choice());
	$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
	$this->set('locale', $lang);
	$this->set('SettingsArray', array());

	if(isset($this->Auth)) {
		$this->set('authUser', $this->Auth);
	}

	$SettingsArray = array();
	$this->set('SettingsArray', $SettingsArray);

}

function afterFilter() {

	$this->Navigation->lastURL();
	$this->Navigation->SetSessionForPaging();


}

public function getspooldata () {

	$this->loadModel('Ticket');
	$this->layout = 'json';

	$datajsn = $this->Rest->RestRequest();
	$this->set('data',json_encode($datajsn));

}

private function priorityToText($ticket)
{
	foreach ($ticket as $key_ticket => $ticketEntry) {
		foreach ($ticket[$key_ticket] as $key => $value) {
			if(isset($ticket[$key_ticket][$key]["Ticket"]["priority"])){
				$currentPriority = $ticket[$key_ticket][$key]["Ticket"]["priority"];
				switch ($currentPriority) {
					case 0:
					$currentPriority = __('low');
					break;
					case 1:
					$currentPriority = __('high');
					break;
				}
				$ticket[$key_ticket][$key]["Ticket"]["priority_text"] = $currentPriority;
			}
		}
	}

	return $ticket;
}

private function getUserNames($ticket){

	$this->User->recursive = -1;

	foreach ($ticket as $key_ticket => $ticketEntry) {
		foreach ($ticket[$key_ticket] as $key => $value) {

			if(!isset($ticket[$key_ticket][$key]["Ticket"]["user_id"])) continue;

			$current_user_id = $ticket[$key_ticket][$key]["Ticket"]["user_id"];

			$user = $this->User->find('first', array(
				'conditions' => array(
					'User.id' => $current_user_id
					)
				)
			);

			if(isset($user['User']['name'])) {
				$ticket[$key_ticket][$key]["Ticket"]["user_name"] = $user['User']['name'];
			} else {
				$ticket[$key_ticket][$key]["Ticket"]["user_name"] = '-';
			}
		}
	}	

	return $ticket;
}

	private function getSpoolArray($ticket)
	{
		foreach ($ticket as $key_ticket => $ticketEntry) {
			foreach ($ticket[$key_ticket] as $key => $value) {

				if(!empty($ticket[$key_ticket][$key]["Ticket"]["spools"])){

					$ticket[$key_ticket][$key]["Ticket"]["spool_array"] = explode('/',$ticket[$key_ticket][$key]["Ticket"]["spools"]);

				} else {
					$ticket[$key_ticket][$key]["Ticket"]["spool_array"] = array();
				}
			}
		}

		return $ticket;
	}

	private function getPossibleCompanies($ticket)
	{

		$this->Reportnumber->recursive = -1;
		$this->Testingcomp->recursive = -1;

		foreach ($ticket as $key_ticket => $ticketEntry) {
			foreach ($ticket[$key_ticket] as $key => $value) {

				if(!isset($ticket[$key_ticket][$key]['Reportnumbers'])) continue;
				if(count($ticket[$key_ticket][$key]['Reportnumbers']) == 0) continue;

				foreach ($ticket[$key_ticket][$key]['Reportnumbers'] as $_key => $_value) {

					$Reportnumber = $this->Reportnumber->findById($_value['TicketReportnumber']['reportnumber_id']);

					if(count($Reportnumber) == 0) continue;

					$ProjectMemberIds = $this->Autorisierung->ProjectMember($Reportnumber['Reportnumber']['topproject_id']);
					$ProjectMember = $this->Testingcomp->find('list',array('order' => array('name'),'fields' => array('id','name'),'conditions' => array('Testingcomp.id' => $ProjectMemberIds)));

					$Testingcomp = $this->Testingcomp->findById($Reportnumber['Reportnumber']['testingcomp_id']);

					$ticket[$key_ticket][$key]['Reportnumbers'][$_key]["Testingcomp"] = json_encode($ProjectMember);

					$ticket[$key_ticket][$key]['Reportnumbers'][$_key]['TicketReportnumber']['testincomp_id'] = $Reportnumber['Reportnumber']['testingcomp_id'];
					$ticket[$key_ticket][$key]['Reportnumbers'][$_key]['TicketReportnumber']['Testingcomp'] = $Testingcomp['Testingcomp']['name'];

				}
			}
		}

		return $ticket;
	}

	private function getTicketweldsWorking($ticket,$area){

		$this->loadModel('Testingmethod');
		$this->loadModel('TicketweldsTestingmethods');

		$this->Testingmethod->recursive = -1;

		$weldcount = 0;

		foreach ($ticket[$area] as $key => $value) {

			$TicketweldsTestingmethods = $this->TicketweldsTestingmethods->find('all',array(
				'conditions' => array(
					'TicketweldsTestingmethods.ticket_id' => $value['Ticket']['id']
					)
				)
			);

			if(empty($TicketweldsTestingmethods)){

				$ticket[$area][$key]['Weldstatistik']['working'] = $weldcount;
				continue;

			}

			foreach ($TicketweldsTestingmethods as $_key => $_value) {

				$Testingmethod = $this->Testingmethod->find('first',array(
					'conditions'=>array(
						'Testingmethod.id' => $_value['TicketweldsTestingmethods']['testingmethod_id']
						)
					)
				);

				if(!isset($ticket[$area][$key]['Weldstatistik']['working'][$Testingmethod['Testingmethod']['verfahren']])){
					$ticket[$area][$key]['Weldstatistik']['working'][$Testingmethod['Testingmethod']['verfahren']] = 0;
				}

				$ticket[$area][$key]['Weldstatistik']['working'][$Testingmethod['Testingmethod']['verfahren']]++;

			}
		}

		return $ticket;
	}

	private function getTicketweldsCompleted($ticket,$area){

		foreach($ticket[$area] as $key => $value){

			if(!isset($value['Reportnumbers'])){

				$ticket[$area][$key]['Weldstatistik']['complete'] = 0;

				continue;
			}

			if(!is_array($value['Reportnumbers'])) continue;

			foreach($value['Reportnumbers'] as $_key => $_value){

				$reportnumber_id = $_value['TicketReportnumber']['reportnumber_id'];

				$Reportnumber = $this->Reportnumber->find('first',array(
					'conditions' => array(
						'Reportnumber.id' => $reportnumber_id
						)
					)
				);

				if(count($Reportnumber) == 0) continue;

				$Testingmethod = $this->Testingmethod->find('first',array(
					'conditions'=>array(
						'Testingmethod.id' => $Reportnumber['Reportnumber']['testingmethod_id']
						)
					)
				);

				$EvaluationModel = 'Report' . ucfirst($Testingmethod['Testingmethod']['value']) . 'Evaluation';

				$this->loadModel($EvaluationModel);

				$Evaluation = $this->{$EvaluationModel}->find('count',array(
					'group' => array('description'),
					'conditions' => array(
						$EvaluationModel . '.reportnumber_id' => $Reportnumber['Reportnumber']['id']
						)
					)
				);

				if($Evaluation == 0) continue;

				$ticket[$area][$key]['Weldstatistik']['complete'][$Testingmethod['Testingmethod']['verfahren']] = $Evaluation;

			}
		}

		return $ticket;
	}

	private function getTicketweldsIncomming($ticket,$area)
	{

		$weldcount = 0;

		foreach ($ticket[$area] as $key => $value) {
			
//			if($value['Ticket']['id'] != 298) continue;

			foreach ($value['TicketData'] as $_key => $_value) {

				$json = json_decode($_value['json'],true);

				foreach($json['data']['welds'] as $__key => $__value) {

					if($__value['weld_number'] == 13){

//						pr($__key);
//						pr($__value);
	
					}
				}

				$weldcount += count($json['data']['welds']);
			}

			$ticket[$area][$key]['Weldstatistik']['incomming'] = $weldcount;

			$weldcount = 0;

		}

		return $ticket;

	}

	public function tickets(){

		$this->_tickets();
		$this->_activeareas();

	}

	protected function _activeareas(){

		if(!isset($this->request->data['active_area'])) return;
		if(empty($this->request->data['active_area'])) return;

		$this->layout = 'blank';

		$this->Session->write('TicketsActiveAreas',$this->request->data['active_area']);

		$this->set('Response',json_encode(array()));

		$this->render('json','blank');

	}

	protected function _tickets(){

		$this->loadModel('Ticket');
		$this->loadModel('Report');
		$this->loadModel('Reportnumber');
		$this->loadModel('TicketReportnumber');
		$this->loadModel('User');

		if($this->Session->check('TicketsActiveAreas') == false) $this->Session->write('TicketsActiveAreas','main_area');

		$breads = array();
		$breads = $this->Navigation->Breads(null);

		$projectID = $this->request->projectvars['projectID'];
		$cascadeID = $this->request->projectvars['cascadeID'];
		$reportID =	$this->request->projectvars['reportID'];
		$ticket_id_show_container = 0;

		if(!empty($this->request->data['ticket_id'])) $ticket_id_show_container = $this->request->data['ticket_id'];

		$this->set('ticket_id_show_container',$ticket_id_show_container);

		$ticket = array();

		$tickets = $this->Ticket->find('all',array('order'=>array('Ticket.priority DESC','Ticket.created DESC'),'conditions'=> array('topproject_id'=>$projectID,'cascade_id'=>$cascadeID,'report_id'=> $reportID)));

		$this->request->data['data_area'] = 'data_area';

		foreach ($tickets as $key => $value) {

			if($value['Ticket']['status'] == 0) {
				$ticket['data_area'][] = $value;

				$Reportnumbers = $this->TicketReportnumber->find('all',array('conditions'=>array('ticket_id'=>$value['Ticket']['id'])));

				if(count($Reportnumbers) == 0) continue;

				foreach($Reportnumbers as $r_key => $r_value) {
					$current_id = $r_value['TicketReportnumber']['reportnumber_id'];
					$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $current_id)));

					if(count($reportnumber) == 0) continue;

					$number = $reportnumber['Reportnumber']['number'];
					$year = $reportnumber['Reportnumber']['year'];
					$testingmethod_name = $reportnumber['Testingmethod']['name'];

					$r_value['TicketReportnumber']['name'] = $testingmethod_name;
					$r_value['TicketReportnumber']['year'] = $year;
					$r_value['TicketReportnumber']['number'] = $number;
					$r_value['TicketReportnumber']['status'] = $reportnumber['Reportnumber']['status'];
					$r_value['TicketReportnumber']['status_class'] = 'icon_close_' . $reportnumber['Reportnumber']['status'];
					$Reportnumbers[$r_key] = $r_value;
				}

				$value['Reportnumbers'] = $Reportnumbers;

			} else {

				//zugeordnete Berichte
				$Reportnumbers = $this->TicketReportnumber->find('all',array('conditions'=>array('ticket_id'=>$value['Ticket']['id'])));

				if(!empty($Reportnumbers)) {
					foreach($Reportnumbers as $r_key => $r_value) {
						$current_id = $r_value['TicketReportnumber']['reportnumber_id'];
						$reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $current_id)));

						if(count($reportnumber) == 0) continue;

						$number = $reportnumber['Reportnumber']['number'];
						$year = $reportnumber['Reportnumber']['year'];
						$testingmethod_name = $reportnumber['Testingmethod']['name'];

						$r_value['TicketReportnumber']['topproject_id'] = $reportnumber['Reportnumber']['topproject_id'];
						$r_value['TicketReportnumber']['cascade_id'] = $reportnumber['Reportnumber']['cascade_id'];
						$r_value['TicketReportnumber']['order_id'] = $reportnumber['Reportnumber']['order_id'];
						$r_value['TicketReportnumber']['testingmethod_id'] = $reportnumber['Reportnumber']['testingmethod_id'];
						$r_value['TicketReportnumber']['report_id'] = $reportnumber['Reportnumber']['report_id'];
						$r_value['TicketReportnumber']['name'] = $testingmethod_name;
						$r_value['TicketReportnumber']['year'] = $year;
						$r_value['TicketReportnumber']['number'] = $number;
						$r_value['TicketReportnumber']['status'] = $reportnumber['Reportnumber']['status'];
						$r_value['TicketReportnumber']['status_class'] = 'icon_close_' . $reportnumber['Reportnumber']['status'];
						$r_value['ReportnumberLink'] = array(
							$reportnumber['Reportnumber']['topproject_id'],
							$reportnumber['Reportnumber']['cascade_id'],
							$reportnumber['Reportnumber']['order_id'],
							$reportnumber['Reportnumber']['report_id'],
							$reportnumber['Reportnumber']['id'],0,0,0,3
						);
						$Reportnumbers[$r_key] = $r_value;
					}

					$value['Reportnumbers'] = $Reportnumbers;
				}

				$ticket['data_areaclosed'][] = $value;
			}

			$tickets[$key]['Ticket']['spools_array'] = explode('/',$tickets[$key]['Ticket']['spools']);

		}

		$ticket = $this->priorityToText($ticket);
		$ticket = $this->getUserNames($ticket);
		$ticket = $this->getSpoolArray($ticket);
		$ticket = $this->getTicketweldsIncomming($ticket,'data_area');
		$ticket = $this->getTicketweldsIncomming($ticket,'data_areaclosed');

		$ticket = $this->getReportsFromCompleted($ticket);
		$ticket = $this->getPossibleCompanies($ticket);
		$ticket = $this->getTicketweldsWorking($ticket,'data_area');
		$ticket = $this->getTicketweldsWorking($ticket,'data_areaclosed');
		$ticket = $this->getTicketweldsCompleted($ticket,'data_area');
		$ticket = $this->getTicketweldsCompleted($ticket,'data_areaclosed');
		$ticket = $this->getOpenTicketwelds($ticket);
		$ticket = $this->getAllOpenTicketwelds($ticket);

		$this->set('ticket', $ticket);

		if(isset($this->request->data['table_only']) && $this->request->data['table_only'] == 1){
			$this->render('tickets_table','blank');
			return;
		}

		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);

		if(!empty($orderID)){
			$breads[] = array(
				'discription' => isset($testingreports[0]['Order']['auftrags_nr']) && isset($testingreports[0]['Order']['status']) ? $testingreports[0]['Order']['auftrags_nr'] . ' (' . $status['value'] . ')' : '',
				'controller' => 'reportnumbers',
				'action' => 'index',
				'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2].'/'.$this->request->projectvars['VarsArray'][3]
			);
		}
		$this->Report->recursive = -1;
		$report = $this->Report->find('first',array('conditions'=>array('Report.id'=>$reportID)));
		$breads[] = array(
			'discription' => isset($report['Report']['name'])? $report['Report']['name'] : '',
			'controller' => 'reportnumbers',
			'action' => 'show',
			'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2].'/'.$this->request->projectvars['VarsArray'][3].'/'.$this->request->projectvars['VarsArray'][4]
		);

		$breads[] = array(
			'discription' => __('Tickets'),
			'controller' => 'rests',
			'action' => 'tickets',
			'pass' => $this->request->projectvars['VarsArray'][0].'/'.$this->request->projectvars['VarsArray'][1].'/'.$this->request->projectvars['VarsArray'][2].'/'.$this->request->projectvars['VarsArray'][3].'/'.$this->request->projectvars['VarsArray'][4]
		);
		$this->set('breads', $breads);

	}

	private function getAllOpenTicketwelds($ticket){
/*
		$keyarray = array('data_area','data_areaclosed');
		$output = array('Incomming' => 0,'Working' => 0);

		foreach($keyarray as $key => $value){

			$output = $this->_getAllOpenTicketweldsOpen($output,$value,$ticket);
			$output = $this->_getAllOpenTicketweldsClose($output,$value,$ticket);

		}
*/
		return $ticket;
	}

	private function _getAllOpenTicketweldsOpen($output,$val,$ticket){

		if($val != 'data_area') return $output;

		foreach($ticket[$val] as $key => $value){

			if(!isset($value['Weldstatistik'])) continue;

			$output['Incomming'] += $value['Weldstatistik']['incomming'];

			if(!isset($value['Weldstatistik']['working'])) continue;
			if(!is_array($value['Weldstatistik']['working'])) continue;
			if(count($value['Weldstatistik']['working']) == 0) continue;

			foreach($value['Weldstatistik']['working'] as $_key => $_value){

				$output['Working'] += $_value;
			}


		}

		return $output;
	}

	private function _getAllOpenTicketweldsClose($output,$val,$ticket){

		if($val != 'data_areaclosed') return $output;


		return $output;
	}


	private function getOpenTicketwelds($ticket){

		$ticket = $this->_getOpenTicketwelds($ticket,'data_areaclosed');
		$ticket = $this->_getOpenTicketwelds($ticket,'data_area');

		return $ticket;
	}

	private function _getOpenTicketwelds($ticket,$area){

		if(!isset($ticket[$area])) return $ticket;

		foreach($ticket[$area] as $key => $value){

			if(!isset($value['Weldstatistik'])) continue;

			if(!isset($value['Weldstatistik']['complete'])){

				$ticket[$area][$key]['Weldstatistik']['outcomming'] = 0;

			} elseif(is_array($value['Weldstatistik']['complete']) && count($value['Weldstatistik']['complete']) > 0){

				$outcomming = 0;

				foreach($value['Weldstatistik']['complete'] as $_key => $_value){

					$outcomming += $_value;

				}

				$ticket[$area][$key]['Weldstatistik']['outcomming'] = $outcomming;

				$ticket[$area][$key]['Weldstatistik']['open'] = $ticket[$area][$key]['Weldstatistik']['incomming'] - $ticket[$area][$key]['Weldstatistik']['outcomming'];

			}
		}

		return $ticket;
	}

	public function createreport() {

		if(isset($this->request->data['json_true'])) $this->_editreport();
		else $this->__createreport();

	}

	public function _editreport() {

		$this->_editreporttestingcomp();
		$this->_editreportpriority();
		$this->_editreportstatus();

	}

	protected function _editreportstatus() {

		$this->layout = 'blank';

		if(!isset($this->request->data['status'])) return false;
		if(!isset($this->request->data['ticket_id'])) return false;
		if(!isset($this->request->data['ticket_url'])) return false;

		$this->loadModel('Ticket');
		$this->Ticket->recursive = -1;

		$data = array();

		$ticket_id = $this->request->data['ticket_id'];
		$ticket_url = $this->request->data['ticket_url'];

		$Ticket = $this->Ticket->findById($ticket_id);

		if(empty($Ticket)){
			$this->set('Response',json_encode(array('Message' => 'Error')));
			$this->render('json');
			return;
		}

		$Ticket['Ticket']['status'] = 0;

		$Test = $this->Ticket->save($Ticket['Ticket']);

		if($Test === false){
			$data['Message'] = 'Error';
			$this->set('Response',json_encode($data));
		} else {

			$this->Session->write('TicketsActiveAreas','data_area');

			$data['Message'] = 'Success';
			$data['TicketUrl'] = $ticket_url;
			$this->set('Response',json_encode($data));
		}

		$this->render('json');

	}

	protected function _editreportpriority() {

		$this->layout = 'blank';

		if(!isset($this->request->data['priority'])) return false;
		if(!isset($this->request->data['ticket_id'])) return false;

		$this->loadModel('Ticket');
		$this->Ticket->recursive = -1;

		$data = array();

		$ticket_id = $this->request->data['ticket_id'];
		$priority = $this->request->data['priority'];
		$value = $this->request->data['value'];

		$Ticket = $this->Ticket->findById($ticket_id);

		if(empty($Ticket)){
			$this->set('Response',json_encode(array('Message' => 'Error')));
			$this->render('json');
			return;
		}

		$Ticket['Ticket']['priority'] = $value;

		$Test = $this->Ticket->save($Ticket['Ticket']);
		if($Test === false){
			$data['Message'] = 'Error';
			$this->set('Response',json_encode($data));

		} else {

			if($Test['Ticket']['priority'] == 1) $Test['Ticket']['priority_text'] = 'hoch';
			if($Test['Ticket']['priority'] == 0) $Test['Ticket']['priority_text'] = 'niedrig';

			$data['Message'] = 'Success';
			$data['Ticket'] = $Test['Ticket'];
	//		$this->set('Response',$data['Ticket']['priority_text']);
			$this->set('Response',json_encode($data));
		}

		$this->render('json');

	}

	protected function _editreporttestingcomp() {

		$this->layout = 'blank';

		if(!isset($this->request->data['testingcomp_id'])) return false;
		if(!isset($this->request->data['reportnumber_id'])) return false;

		$data = array();

		$reportnumber_id = $this->request->data['reportnumber_id'];
		$testingcomp_id = $this->request->data['value'];
		$testingcomp_name = $this->request->data['testingcomp_id'];

		$this->loadModel('Reportnumber');
		$this->loadModel('Testingcomp');

		$this->Reportnumber->recursive = -1;
		$this->Testingcomp->recursive = -1;

		$Reportnumber = $this->Reportnumber->findById($reportnumber_id);

		if(empty($Reportnumber)){
			$data['Testingcomp'] = $testingcomp_name;
			$data['Reportnumber'] = $reportnumber_id;
			$this->set('Response',json_encode($data));
			$this->render('json');
			return;
		}

		$data['Reportnumber'] = $Reportnumber['Reportnumber'];

		$Testingcomp = $this->Testingcomp->findById($testingcomp_id);

		if(empty($Testingcomp)){
			$data['Message'] = 'Error';
			$data['Testingcomp'] = $testingcomp_name;
			$this->set('Response',json_encode($data));
			$this->render('json');
			return;
		}

		$data['Testingcomp'] = $Testingcomp['Testingcomp']['name'];

		if($data['Reportnumber']['status'] > 0){
			$data['Message'] = 'Error';
			$data['Testingcomp'] = $testingcomp_name;
			$this->set('Response',json_encode($data));
			$this->render('json');
			return;
		}

		$ProjectMemberIds = $this->Autorisierung->ProjectMember($Reportnumber['Reportnumber']['topproject_id']);

		if(empty($ProjectMemberIds)){
			$data['Message'] = 'Error';
			$data['Testingcomp'] = $testingcomp_name;
			$this->set('Response',json_encode($data));
			$this->render('json');
			return;
		}

		$key = array_search($testingcomp_id, $ProjectMemberIds);

		if($key === false){
			$data['Message'] = 'Error';
			$data['Testingcomp'] = $testingcomp_name;
			$this->set('Response',json_encode($data));
			$this->render('json');
			return;
		}

		$Reportnumber['Reportnumber']['testingcomp_id'] = $testingcomp_id;

		$Test = $this->Reportnumber->save($Reportnumber['Reportnumber']);

		if($Test === false){
			$data['Message'] = 'Error';
			$data['Testingcomp'] = $testingcomp_name;
			$this->set('Response',json_encode($data));

		} else {
			$data['Message'] = 'Success';
			$this->set('Response',json_encode($data));
		}

		$this->render('json');

	}

	public function deleteticket(){

		$this->layout = "modal";
		$this->loadModel('Ticket');
		$this->loadModel('TestingmethodsReports');
		$this->loadModel('Testingmethod');
		$this->loadModel('TicketweldsTestingmethods');
		$this->loadModel('TicketReportnumber');
		$this->loadModel('Ticketweld');
		$this->loadModel('Reportnumber');

		$ticketid = $this->request->projectvars['VarsArray'][16];

		if(isset($this->request->data['ticket_id'])) $ticketid = $this->request->data['ticket_id'];

		if (!$this->Ticket->exists($ticketid)) {
			throw new NotFoundException(__('Invalid Ticket'));
		}

		if ($this->request->is(array('post', 'put'))) {
			if(isset($this->request->data['Ticket']['id'])){

				$this->request->data['Ticket']['id'] = $ticketid;

				$delete_ticket = $this->Ticket->delete(array('Ticket.id' => $ticketid));
				$delete_ticket_data = $this->Ticket->TicketData->deleteAll(array('TicketData.ticket_id' => $ticketid));
				$delete_ticket_reportnumbers = $this->TicketReportnumber->deleteAll(array('TicketReportnumber.ticket_id' => $ticketid),false,false);
				$delete_ticketwelds = $this->Ticketweld->deleteAll(array('Ticketweld.ticket_id' => $ticketid));
				$delete_ticketwelds_testingmethod = $this->TicketweldsTestingmethods->deleteAll(array('TicketweldsTestingmethods.ticket_id' => $ticketid));

				if($delete_ticket && $delete_ticket_data) {
					$FormName['controller'] = 'rests';
					$FormName['action'] = 'tickets';
					$FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);


					$this->set('FormName', $FormName);
					$this->set('saveOK', 1);
					return;

				} else {

					$this->Flash->error(__('An error has occurred.'), array('key' => 'error'));
					$this->set('saveOK', 0);
				}
			}
		} else {
	
		}

		$options = array('conditions' => array('Ticket.id' => $ticketid));

		$Ticket = $this->Ticket->find('first', $options);

		$Ticket = $this->Rest->JsonDecode($Ticket);
		$Ticket = $this->Rest->CollectTicketOpenReports($Ticket,2);
		$this->request->data = $Ticket;


		$this->Flash->warning(__('Do you really want to delete this ticket?'), array('key' => 'warning'));
		if(isset($Ticket['OpenReports']['Reportnumbers'])) $this->Flash->warning(__('Test reports that have already been created lose their assignment to this ticket.'), array('key' => 'warning'));

	}

	public function editticket() {

		$this->layout = 'modal';
		$this->loadModel('Ticket');
		$this->loadModel('TestingmethodsReports');
		$this->loadModel('Testingmethod');
		$this->loadModel('TicketweldsTestingmethods');
		$this->loadModel('TicketReportnumber');
		$this->loadModel('Reportnumber');


		$projectID = $this->request->projectvars['projectID'];
		$cascadeID = $this->request->projectvars['cascadeID'];
		$orderID = $this->request->projectvars['orderID'];
		$reportID =	$this->request->projectvars['reportID'];
		$testingmethodID =	$this->request->projectvars['VarsArray'][5];
		$ticketid = $this->request->projectvars['VarsArray'][16];

		//wenn die Anfrage nach dem Scan kommt
		if(isset($this->request->data['ticket_id'])) $ticketid = $this->request->data['ticket_id'];
		if(isset($this->request->data['Ticket']['id'])) $ticketid = $this->request->data['Ticket']['id'];

		if(empty($ticketid)){

			$FormName['controller'] = 'rests';
			$FormName['action'] = 'tickets';
			$FormName['terms'] = implode('/',array($projectID,$cascadeID,$orderID,$reportID,$testingmethodID));

			$this->Flash->error(__('There has been an error.') . ' ' . __('No data was received.'), array('key' => 'error'));

			$this->set('ErrorFormName', $FormName);

			return;
		}

		//Abfrage nach Ticketid
		if (!$this->Ticket->exists($ticketid)) {
			throw new NotFoundException(__('Invalid ticket'));
		}

		//        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);
		$arrayData['settings'] = $this->Xml->CollectXMLFromFile('Ticket');

		//$this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('Ticket'));

		if (isset($this->request->data['Ticket']) && ($this->request->is('post') || $this->request->is('put'))) {

			$ticketdata = $this->Ticket->TicketData->find('all',array('fields'=>array('json'),'conditions'=>array('ticket_id'=>$ticketid)));
			$ticketdata = Hash::extract($ticketdata, '{n}.{s}.json');
			$ticket_uid_weld = array();

			foreach ($ticketdata as $key => $value) {

				$data = json_decode($value,true);

				if(!isset($data['data']['welds']) || empty($data['data']['welds'])) continue;

				foreach($data['data']['welds'] as $_key =>$_value){
					$ticket_uid_weld[$_value['system_uid']] = $_value['weld_number'];
				}

			}

			if(isset($id)) $this->request->data['id'] = $id;

			if (isset($this->request->data['Ticket']['reportwelds']) && !empty($this->request->data['Ticket']['reportwelds'])) {

				$TestingmethodsReports = $this->request->data['Ticket']['reportwelds'];

				unset($this->request->data['Ticket']['reportwelds']);

				foreach ($TestingmethodsReports as $tr_key => $tr_value) {
					$save = explode('_',$tr_value);
					$savearray[]['TicketweldsTestingmethods'] = array('testingmethod_id'=>$save[0],'ticketweld_id'=>$save[1],'ticket_id'=>$ticketid);
				}

				if(count($savearray) > 0) {

					$this->TicketweldsTestingmethods->deleteAll(array('TicketweldsTestingmethods.ticket_id' => $ticketid), false);

				}

				foreach ($savearray as $savekey => $savevalue) {

					if(isset($ticket_uid_weld[$savevalue['TicketweldsTestingmethods']['ticketweld_id']])){
						$savevalue['TicketweldsTestingmethods']['weld_number'] = $ticket_uid_weld[$savevalue['TicketweldsTestingmethods']['ticketweld_id']];
					} else {
						$savevalue['TicketweldsTestingmethods']['weld_number'] = '-';
					}

					$this->TicketweldsTestingmethods->create();
					$this->TicketweldsTestingmethods->save($savevalue['TicketweldsTestingmethods']);
				}
			}

			if (isset($this->request->data['Ticket']['reportwelds']) && empty($this->request->data['Ticket']['reportwelds'])) {
				$this->TicketweldsTestingmethods->deleteAll(array('TicketweldsTestingmethods.ticket_id' => $ticketid), false);
			}

			if ($this->Ticket->save($this->request->data)) {

					$this->Flash->success(__('The Ticket has been saved'), array('key' => 'success'));
					if(!empty($id)){
						$this->Autorisierung->Logger($id, $this->request->data['Ticket']);
					}
					//Weiterleitung nach dem Speichern vorbereiten
					$Ticket = $this->Ticket->find('first',array('conditions'=>array('Ticket.id'=>$ticketid)));

					if($Ticket['Ticket']['status'] == 1) $this->Rest->__CloseTicketDeleteTicketwelds($Ticket['Ticket']['id']);

					$projectID = $Ticket['Ticket']['topproject_id'];
					$cascadeID = $Ticket['Ticket']['cascade_id'];
					$reportID = $Ticket['Ticket']['report_id'];
					$orderID = 0;
					
					$this->request->projectvars['VarsArray'] = array($projectID,$cascadeID,$orderID,$reportID);

					$FormName['controller'] = 'rests';
					$FormName['action'] = 'tickets';
					$FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);


					$this->set('FormName', $FormName);
					$this->set('saveOK', 1);
					
				} else {
					$this->Flash->error(__('The ticket could not be saved. Please, try again.'), array('key' => 'error'));
				}
			}

			$options = array('conditions' => array('Ticket.id' => $ticketid));

			$Ticket = $this->Ticket->find('first', $options);

			$Ticket = $this->Rest->JsonDecode($Ticket);
//				$Ticket = $this->Rest->CollectTicketcloseReports($Ticket);
			$Ticket = $this->Rest->CollectTicketOpenReports($Ticket,2);
			$this->request->data = $Ticket;

			$reportID = $this->request->data['Ticket']['report_id'];
			$testingmethodids = $this->TestingmethodsReports->find('all',array('fields'=>array('testingmethod_id'),'conditions'=>array('report_id'=>$reportID)));
			$testingmethodids = Hash::extract($testingmethodids, '{n}.{s}.testingmethod_id');
			$testingmethods = $this->Testingmethod->find('list',array('fields'=>array('id','name'),'conditions'=>array('Testingmethod.id'=>$testingmethodids)));
			$ticketdata = $this->Ticket->TicketData->find('all',array('fields'=>array('json'),'conditions'=>array('ticket_id'=>$ticketid)));
			$ticketdata = Hash::extract($ticketdata, '{n}.{s}.json');

			$i = 0;
			$ticketwelds = $this->TicketweldsTestingmethods->find('all',array('fields'=>array('testingmethod_id', 'ticketweld_id'),'conditions'=>array('TicketweldsTestingmethods.ticket_id'=>$ticketid)));

			foreach ($testingmethods as $tkey => $tvalue) {	//	var_dump($ticketdata);

				foreach ($ticketdata as $key => $value) {

					$i++;
					$data = json_decode($value,true);

					if(!isset($data['data']['welds']) || empty($data['data']['welds'])) continue;

					foreach ($data['data']['welds'] as $wkey => $wvalue) {

						$Optiongroup[$tvalue][$tkey.'_'.$wvalue['system_uid'].'_'.$wvalue['nennweite_1']] = $wvalue['weld_number'] . ' (' . $Ticket['TicketData'][$key]['sheet_no'] . ')';
					}
				}

		
				break;
			}

			$Optiongroup = $this->Rest->CheckReportByXml($Optiongroup);
			$Optiongroup = $this->Rest->SortAndInfoWelds($Ticket,$Optiongroup);

			//selektierte Werte
			foreach($ticketwelds as $key => $value) {
				$reportweldid = $value['TicketweldsTestingmethods']['testingmethod_id'].'_'.$value['TicketweldsTestingmethods']['ticketweld_id'];
				$this->request->data['Ticket']['reportwelds'][] = $reportweldid;
			}

			$SettingsArray = array();
			if(isset($Optiongroup)) $this->set('report_testingmethods',$Optiongroup);
			$this->set('SettingsArray', $SettingsArray);
			$this->set('arrayData', $arrayData);
			$this->set('settings', $arrayData['settings']);
		}

		public function __createreport() {

			if(isset($this->request->data['json_true'])) return;

			$this->layout = 'modal';

			$this->loadModel('Ticket');
			$this->loadModel('TestingmethodsReports');
			$this->loadModel('Testingmethod');
			$this->loadModel('TicketweldsTestingmethods');
			$this->loadModel('TicketReportnumber');
			$this->loadModel('Reportnumber');

			$projectID = $this->request->projectvars['projectID'];
			$cascadeID = $this->request->projectvars['cascadeID'];
			$orderID = $this->request->projectvars['orderID'];
			$reportID =	$this->request->projectvars['reportID'];
			$testingmethodID =	$this->request->projectvars['VarsArray'][5];
			$ticketid = $this->request->projectvars['VarsArray'][16];

			$ticketweld_testingmethods = $this->TicketweldsTestingmethods->find('all',array('conditions'=>array('ticket_id'=>$ticketid)));

			if(empty($ticketweld_testingmethods)){
				$this->Flash->error(__('There are no welds seltected!'), array('key' => 'error'));
			}

 			$return	 = $this->Rest->SaveReport($projectID,$cascadeID,$reportID,$ticketid);

			if(isset($this->request->data['Ticket']) && $return === false){
				$this->Flash->error(__('There are no welds seltected!'), array('key' => 'error'));
			}

			$ticketweld_testingmethods = $this->TicketweldsTestingmethods->find('all',array('conditions'=>array('ticket_id'=>$ticketid)));

			$Ticket = $this->Ticket->find('first',array('conditions'=>array('Ticket.id'=>$ticketid)));
			$Ticket = $this->Rest->JsonDecode($Ticket);
			$Ticket = $this->Rest->CollectTicketOpenReports($Ticket,2);

			$this->request->data = $Ticket;

			$ticketweld_testingmethods = $this->Rest->CollectTicketWeldMethod($ticketweld_testingmethods);

			$this->Session->write('TicketsActiveAreas','data_areaclosed');

			$this->set('Ticket', $Ticket);
			$this->set('report_testingmethods', $ticketweld_testingmethods);

			$this->request->data['Ticket']['id'] = $ticketid;

		}

		private function getReportsFromCompleted($ticket){

			$this->Reportnumber->recursive = 0;

			foreach($ticket['data_area'] as $key => $value){

				//zugeordnete Berichte
				$Reportnumbers = $this->TicketReportnumber->find('all',array(
					'conditions'=>array(
						'ticket_id'=>$value['Ticket']['id']
						)
					)
				);

				if(empty($Reportnumbers)) continue;

				foreach($Reportnumbers as $_key => $_value) {

					$reportnumber_id = $_value['TicketReportnumber']['reportnumber_id'];

					$reportnumber = $this->Reportnumber->find('first', array(
						'conditions' => array(
								'Reportnumber.id' => $reportnumber_id,
								'Reportnumber.delete' => 0,
							)
						)
					);

					if(count($reportnumber) == 0) continue;

					$Reportnumbers[$_key]['TicketReportnumber']['topproject_id'] = $reportnumber['Reportnumber']['topproject_id'];
					$Reportnumbers[$_key]['TicketReportnumber']['cascade_id'] = $reportnumber['Reportnumber']['cascade_id'];
					$Reportnumbers[$_key]['TicketReportnumber']['order_id'] = $reportnumber['Reportnumber']['order_id'];
					$Reportnumbers[$_key]['TicketReportnumber']['testingmethod_id'] = $reportnumber['Reportnumber']['testingmethod_id'];
					$Reportnumbers[$_key]['TicketReportnumber']['report_id'] = $reportnumber['Reportnumber']['report_id'];
					$Reportnumbers[$_key]['TicketReportnumber']['name'] = $reportnumber['Testingmethod']['name'];
					$Reportnumbers[$_key]['TicketReportnumber']['year'] = $reportnumber['Reportnumber']['year'];
					$Reportnumbers[$_key]['TicketReportnumber']['number'] = $reportnumber['Reportnumber']['number'];
					$Reportnumbers[$_key]['TicketReportnumber']['status'] = $reportnumber['Reportnumber']['status'];
					$Reportnumbers[$_key]['TicketReportnumber']['status_class'] = 'icon_close_' . $reportnumber['Reportnumber']['status'];
					$Reportnumbers[$_key]['ReportnumberLink'] = array(
						$reportnumber['Reportnumber']['topproject_id'],
						$reportnumber['Reportnumber']['cascade_id'],
						$reportnumber['Reportnumber']['order_id'],
						$reportnumber['Reportnumber']['report_id'],
						$reportnumber['Reportnumber']['id'],0,0,0,3
					);
				
				}

				$ticket['data_area'][$key]['Reportnumbers'] = $Reportnumbers;
				
			}

			return $ticket;
		}
	}
	?>
