<?php
class AutorisierungComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ProtectController() {

		$this->_controller->Auth->authenticate = array(
			AuthComponent::ALL => array(
				'userModel' => 'User',
				'scope' => array(
					'User.enabled' => 1,
				)
			),
			'Form'
		);

		if($this->_controller->Auth->user('id') && $this->_controller->Auth->user('id') != ''){

			// es wird nach Rollenzugehörigkeit getestet
			$aro = array('model' => 'Roll','foreign_key' => $this->_controller->Auth->user('roll_id'));

			// das zu prüfenden Objekt wird ermittelt
			$action = $this->_controller->request->params['controller'] . '/' . $this->_controller->request->params['action'];

			if($this->_controller->Acl->check($aro, $action) == true) return true;
			elseif($this->_controller->Acl->check($aro, $action) == false) return false;
			else return false;

		}
	}

	public function Protect($settings = null, $field = null, $model = null) {

		$this->_controller->Auth->allow('login','logout','loggedin','incomming','externreports');
		$this->_controller->Auth->loginError = __('Invalid user or password!', true);
		$this->_controller->Auth->authError = __('Sorry, not enough permission!', true);

		if($this->_controller->request->params['controller'] == 'externs' && $this->_controller->request->params['action'] == 'externreports' && empty($this->_controller->Auth->user())){
			$this->_controller->redirect(array('controller' => 'externs', 'action' => 'incomming'));
		}

		$LastUrl = explode('/',$this->_controller->Session->read('lastURL'));

		@$thisunauthorizedRedirect = '/';

		if(count($LastUrl) > 1){
			$thisunauthorizedRedirect .= $LastUrl[0] . '/' . $LastUrl[1];
		}
		if(count($LastUrl) > 2){
			foreach($LastUrl as $_key => $_LastUrl){
				if($_key > 1){
					$thisunauthorizedRedirect .= '/' . $_LastUrl;
				}
			}
		}

		// Wenn im Dialog nicht authorisiert wurde
		if(isset($this->_controller->request->data['dialog']) && $this->_controller->request->data['dialog'] == 1){
			$thisunauthorizedRedirect = 'Authorizes/notauthorize';
		}

		$thisunauthorizedRedirect = '/Authorizes/notauthorize';

//		$thisunauthorizedRedirect = '/Authorizes/index';

		$this->_controller->Auth->unauthorizedRedirect =  $thisunauthorizedRedirect;
//		$this->_controller->Auth->authorize = array('actions');
//		$this->_controller->Auth->authorize = array('Actions');

		$this->_controller->Auth->authenticate = array(
			AuthComponent::ALL => array(
				'userModel' => 'User',
				'scope' => array(
					'User.enabled' => 1,
				)
			),
			'Form'
		);

//		$this->__CreateRedirectUrl();

		// manueller ARO-Check
		if($this->_controller->Auth->user('id') && $this->_controller->Auth->user('id') != ''){

			// das muss in Zukunft etwas eleganter gelöst werden
			if($this->_controller->Auth->user('extern') == 2 && $this->_controller->request->params['controller'] != 'externs'){
				$this->_controller->Auth->logout();
				$this->_controller->redirect(array('controller' => 'externs', 'action' => 'externreports'));
			}

			// es wird nach Rollenzugehörigkeit getestet
			$aro = array('model' => 'Roll','foreign_key' => $this->_controller->Auth->user('roll_id'));

			// das zu prüfenden Objekt wird ermittelt
			$action = $this->_controller->request->params['controller'] . '/' . $this->_controller->request->params['action'];
			// wenn der Check nicht bestanden wird, wird in den entsprechenden View umgeleitet
			// und das Script beendet

			if(isset($settings) && !empty($settings))  {

				if(isset($settings->$model->$field->select->roll) && !empty($settings->$model->$field->select->roll)){

					$naction = $this->_controller->request->params['action'];
					$roll = $settings->$model->$field->select->roll->$naction;
					$roll=trim($roll->value);

					if(!empty($roll)&& $aro['foreign_key'] > $roll){
						// Wenn der Request kein Ajaxrequest ist
						if(!isset($this->_controller->request->data['ajax_true'])){
							$this->_controller->Session->write('AclError',__('No rights for this function'));
							$this->_controller->Session->write('AclErrorUrl',$this->_controller->Session->read('lastURL'));
						}

						// Wenn der Request ein Ajaxrequest ist
						if(isset($this->_controller->request->data['ajax_true']) && $this->_controller->request->data['ajax_true'] == 1){

							CakeLog::write('acl', 'Forbidden: '.$action.' - '.print_r($aro, true));

							$this->_controller->set('authUser',null);
							$this->_controller->set('SettingsArray',null);
							$this->_controller->set('timeout',3000);
							$this->_controller->set('ajax_url',$this->_controller->Session->read('lastURL'));
							$this->_controller->render('/Authorizes/notauthorize', 'modal');

						}

						$this->_controller->response->send();
						$this->_controller->_stop();
					}
				}
			}

			else {

				if($this->_controller->Acl->check($aro, $action) == false){

					// Wenn der Request kein Ajaxrequest ist
					if(!isset($this->_controller->request->data['ajax_true'])){
						$this->_controller->Session->write('AclError',__('No rights for this function'));
						$this->_controller->Session->write('AclErrorUrl',$this->_controller->Session->read('lastURL'));
					}
					// Wenn der Request ein Ajaxrequest ist
					if(isset($this->_controller->request->data['ajax_true']) && $this->_controller->request->data['ajax_true'] == 1){

					CakeLog::write('acl', 'Forbidden: '.$action.' - '.print_r($aro, true));

					$this->_controller->set('authUser',null);
					$this->_controller->set('SettingsArray',null);
					$this->_controller->set('timeout',3000);
					$this->_controller->set('ajax_url',$this->_controller->Session->read('lastURL'));

					$this->_controller->render('/Authorizes/notauthorize', 'modal');

					}

					$this->_controller->response->send();
					$this->_controller->_stop();
				}
        	}
		}
//pr($this->_controller->request);
	}

	public function CreateRedirectUrl(){
		if($this->_controller->layout == 'ajax') $this->_controller->Session->write('RedirectUrl',$this->_controller->request->here);

		if(!empty($this->_controller->Auth->user('id'))) return;
		if($this->_controller->request->params['controller'] == 'users') return;
	}

	public function PasswordConditions(){

		$Password['NumCharacters'] = 8;
		$Password['UseLowercase'] = 1;
		$Password['UseUppercase'] = 1;
		$Password['UseNumbers'] = 1;
		$Password['UseSpecial'] = 1;


		if(Configure::check('Password') == false) return $Password;

		$PasswordConditions = Configure::read('Password');
		$Roll = AuthComponent::user('roll_id');

		if(isset($PasswordConditions[$Roll])){
			$Password = $PasswordConditions[$Roll];
			return $Password;
		}

		if(isset($PasswordConditions[0])){
			$Password = $PasswordConditions[0];
			return $Password;
		}

		return $Password;
	}

	public function PasswordCheck(){

		$Response = false;

		$PasswordConditions = $this->PasswordConditions();

		$password = $this->_controller->request->data['User']['passwd'];
		$password_old = Security::hash($this->_controller->request->data['User']['password'], null, true);

		$this->_controller->User->recursive = -1;
		$user = $this->_controller->User->find('first', array('conditions' => array('User.id' => $this->_controller->request->data['User']['id'])));

		if($user['User']['password'] != $password_old){
//			$this->_controller->Flash->error('Password testing failed 0.', array('key' => 'error'));
//			return false;
		}

		if(count($this->_controller->request->data['User']) < 4){
			$this->_controller->Flash->error('Password testing failed 1.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if($this->_controller->request->data['User']['passwd'] != $this->_controller->request->data['User']['passwd_confirm']){
			$this->_controller->Flash->error('Password testing failed 2.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if(strlen($password) == 0){
			$this->_controller->Flash->error('Password testing failed 3.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if(strlen($password) < $PasswordConditions['NumCharacters']){
			$this->_controller->Flash->error('Password testing failed 4.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if($PasswordConditions['UseNumbers'] == '1' && !preg_match("#[0-9]+#",$password) ) {
			$this->_controller->Flash->error('Password testing failed 5.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if($PasswordConditions['UseUppercase'] == '1' && !preg_match("#[A-Z]+#",$password)) {
			$this->_controller->Flash->error('Password testing failed 6.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if($PasswordConditions['UseLowercase'] == '1' && !preg_match("#[a-z]+#",$password)) {
			$this->_controller->Flash->error('Password testing failed 7.', array('key' => 'error'));
			return false;
		} else {
			$Response = true;
		}

		if($PasswordConditions['UseSpecial'] == '1' && preg_match('/[[:punct:]]/', $password, $matches)) {
			$Response = true;
		} else {
			$this->_controller->Flash->error('Password testing failed 8.', array('key' => 'error'));
			return false;
		}
/*
		pr($this->_controller->request->data['User']);
		var_dump($PasswordConditions);
		var_dump($Response);
*/
		return $Response;
	}

	public function AclCheck($adress = null){


		if(is_array($adress) && count($adress) == 2) {
			$action = $adress[0] . '/' . $adress[1];
		} else {
			$action = $this->_controller->request->params['controller'] . '/' . $this->_controller->request->params['action'];
		}

		$aro = array('model' => 'Roll','foreign_key' => AuthComponent::user('roll_id'));

		return $this->_controller->Acl->check($aro, $action);

	}

	public function AclCheckLinks($data){
		foreach($data as $_key => $_data){

			if($this->AclCheck(array(0 => $_data['controller'],1 => $_data['action'])) == false){

				$log = array_merge($_data,$this->_controller->Auth->user());

				if(is_array($log)){
					$log = print_r($log, true);
					CakeLog::write('acl', $log);
				}

				unset($data[$_key]);
			}
		}

		return $data;
	}

	public function AclCheckMenue($data){
		foreach($data as $_key => $_data){

			if(count($_data['controller']) == 0) continue;

			foreach($_data['controller'] as  $__key => $_controller){
				$action = explode('/',$_data['action'][$__key]);
				if($this->AclCheck(array(0 => $_controller,1 => $action[0])) == false){
					if(isset($data[$_key]['class'][$__key ]))		unset($data[$_key]['class'][$__key ]);
					if(isset($data[$_key]['link_class'][$__key ]))	unset($data[$_key]['link_class'][$__key ]);
					if(isset($data[$_key]['controller'][$__key ]))	unset($data[$_key]['controller'][$__key ]);
					if(isset($data[$_key]['discription'][$__key ]))	unset($data[$_key]['discription'][$__key ]);
					if(isset($data[$_key]['action'][$__key ]))		unset($data[$_key]['action'][$__key ]);
				}

				if(count($data[$_key]['controller']) == 0) unset($data[$_key]);
			}
		}

		return $data;
	}

	public function AclCheckSubmenueMenue($data){

		foreach($data as $_key => $_data){
			if($this->_controller->Autorisierung->AclCheck(array(0 => $_data['controller'],1 => $_data['action'])) == false){
				unset($data[$_key]);
			}
		}

		return $data;
	}

	public function CheckOrdersforTestingcomp($data){

		return $data;
	}

	public function AllTestForReportSaving($data){
		$this->IsThisMyReport($data['Reportnumber']['id']);

	 	// Wenn die Prüfberichtsmappe gesperrt ist
	 	if($this->_controller->writeprotection) {
//	 		return false;
	 	}

	 	// Wenn der Prüfbericht abgerechnet ist
	 	if($data['Reportnumber']['settled'] > 0){
	 		return false;
	 	}

	 	// Wenn der Prüfbericht gelöscht ist
	 	if($data['Reportnumber']['delete'] > 0){
	 		return false;
	 	}

	 	// Wenn der Prüfbericht deaktiviert ist
	 	if($data['Reportnumber']['deactive'] > 0){
	 		return false;
	 	}

	 	// Wenn der Prüfbericht geschlossen ist
	 	if($data['Reportnumber']['status'] > 0){

			$data = $this->_controller->Data->RevisionCheckTime($data);

			if(!isset($data['Reportnumber']['revision_write'])){
	 			return false;
			}
			if(isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1){
			}
	 	}

		return true;
	}

	// Beim Einloggen werden Sessionvariable mit den zugestandenen Projekten und Prüffirmen gefüllt
	public function ConditionsStart() {
		$_testingcomp = $this->_controller->Auth->user('Testingcomp');
		$_rolls = $this->_controller->Auth->user('Roll');

		$this->_controller->loadModel('TestingcompsTopprojects');
//		$this->_controller->TestingcompsTopprojects->find('all');
		$testingcompConditions = array();
		$_valueArray = array();

		foreach($this->_controller->TestingcompsTopprojects->find('all') as $_Value) {
			// === ist B�se, wenn Daten direkt aus Datenbank kommen, weil der Typ nicht fest vorgegeben ist
			//     --> lieber einheitlich auf Integer casten

			//if($_Value['TestingcompsTopprojects']['testingcomp_id'] === $_testingcomp['id'] && $_rolls['id'] > 4) {
			if((int)($_Value['TestingcompsTopprojects']['testingcomp_id']) == (int)($_testingcomp['id']) && $_rolls['id'] > 4) {
				$testingcompConditions[] = $_Value['TestingcompsTopprojects']['topproject_id'];
			}
			elseif($_rolls['id'] < 5) {
				$testingcompConditions[] = $_Value['TestingcompsTopprojects']['topproject_id'];
			}
		}

		if($_rolls['id'] > 4) {
			$this->_controller->Session->write('conditionsTestingcomps', array($_testingcomp['id']));
		}
		elseif($_rolls['id'] < 5) {
			$this->_controller->loadModel('Testingcomps');
			foreach($this->_controller->Testingcomps->find('all') as $_value) {
				$_valueArray[] = $_value['Testingcomps']['id'];
			}
			$this->_controller->Session->write('conditionsTestingcomps', array_unique($_valueArray));
		}
		$this->_controller->Session->write('conditionsTopprojects', array_unique($testingcompConditions));
	}

	public function ProjectMember($id) {
		$testingcomps = array();
		$this->_controller->loadModel('TestingcompsTopprojects');
		$testingcomps_topprojects = $this->_controller->TestingcompsTopprojects->find('all',array(
						'fields' => array('testingcomp_id'),
						'conditions' => array(
//							'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
							'topproject_id' => $id
							)
						)
					);
		foreach($testingcomps_topprojects as $_key => $_testingcomps_topprojects){
			array_push($testingcomps,$_testingcomps_topprojects['TestingcompsTopprojects']['testingcomp_id']);
		}

		return $testingcomps;
	}

	// Bedingung wird mit den zugestandenen Prüffirmen befüllt
	public function ConditionsTestinccomps() {
		return $this->_controller->Session->read('conditionsTestingcomps');
	}

	// Einfacher Test mittels der zugestandenen Prüffirmen
	public function ConditionsTestinccompsTest($value) {
		$TestOK = null;
		foreach($this->_controller->Autorisierung->ConditionsTestinccomps() as $ConditionsTestinccomps){
			if($value == $ConditionsTestinccomps){
			$TestOK = $ConditionsTestinccomps;
			break;
			}
		}
		if($TestOK == null){
			$this->_controller->redirect(array('controller' => 'users', 'action' => 'logout'));
		}
	}

	// Bedingung wird mit den zugestandenen Projekten befüllt
	public function ConditionsTopprojects() {
		return $this->_controller->Session->read('conditionsTopprojects');
	}

	// Einfacher Test mittels der zugestandenen Projekte
	public function ConditionsTopprojectsTest($value) {

		$topproject = $this->_controller->Session->read('conditionsTopprojects');

		if(empty($topproject) || array_search($value, $topproject) === false) {
//			die();
//			$this->_controller->redirect(array('controller' => 'topprojects', 'action' => 'index'));
		}

		$TestOK = null;
	}

	public function ConditionsCasdadeTest($value) {
		return true;
	}

	public function ConditionsExpeditingTest($value) {
		return true;
	}

	// Mit dieser Funktion werden die Zugriffsbedingungen anhand der VerknÃ¼pfungtabelle festgelegt
	public function Conditions($val,$va2) {

		$this->_controller->loadModel($val);
		$Conditions = array();

		$find = $this->_controller->$val->find('all');
		$conditionsTopprojects = $this->_controller->Session->read('conditionsTopprojects');

		if(count($find) == 0) return $Conditions;
		if(count($conditionsTopprojects) == 0) return $Conditions;

		foreach($find as $_Value) {

			foreach($conditionsTopprojects as $_conditionsTopprojects) {
				if($_conditionsTopprojects == $_Value[$val]['topproject_id']){
					$Conditions[] = $_Value[$val][$va2];
				}
			}
		}

		return $Conditions;

	}

	// Mit dieser Funktion werden die Zugriffsbedingungen anhand der Verknüpfungtabelle festgelegt
	public function ConditionsVariabel($val,$va2,$va3) {
		$loadModel = $val.'s'.$va2.'s';
		$this->_controller->loadModel($loadModel);
		$Conditions = array();
		foreach($this->_controller->$loadModel->find('all') as $_Value) {
//		pr($_Value[$loadModel]);
			if($_Value[$loadModel][strtolower($va2).'_id'] == $va3){
				$Conditions[] = $_Value[$loadModel][strtolower($val).'_id'];
			}
		}
		return $Conditions;
	}

	// Mit dieser Funktion wird mittels der Verknüpfungtabelle überprüft,
	// ob der Benutzer Zugriff auf den aktuellen Datensatz hat
	public function ConditionsTest($val,$va2,$va3) {

		$UserOK = null;
		$Conditions = $this->_controller->Autorisierung->Conditions($val,$va2);

		// Wenn noch nix angelegt wurde
		if(count($Conditions) == 0) return;

		foreach($Conditions as $_Value) {
			if($_Value == $va3) {
				$UserOK = $_Value;
				break;
			}
		}
		if($UserOK == null){
			$this->_controller->redirect(array('controller' => 'users', 'action' => 'logout'));
		}

	}

	// Beim Erstelle/Updaten werden die Werte der verknüpften Tabellen getestet
	public function ConditionsTestingCrUp($va1,$va2) {
		if($va2 == null) {
			return;
		}
		$ValOK = null;
		foreach($va2 as $_va2){
			foreach($this->_controller->Session->read($va1) as $conditions) {
				if($_va2 == $conditions) {
					$ValOK = $conditions;
					break;
				}
			}
		}
		if($ValOK == null) {
			$this->_controller->redirect(array('controller' => 'users', 'action' => 'logout'));
		}
	}

	public function ConditionsTesting($va1,$conditionsSelect) {

		if($va1 == null) {
			return;
		}

		$UserOK = null;

		if(!is_array($va1)) {
			foreach($this->_controller->Session->read($conditionsSelect) as $_Value) {
					if($_Value == $va1) {
						$UserOK = $_Value;
						break;
					}
				}
			}

		if(is_array($va1)) {
			foreach($this->_controller->Session->read($conditionsSelect) as $_Value) {
				foreach($va1 as $_va1) {
					if($_Value == $_va1) {
							$UserOK = $_Value;
							break;
						}
					}
				}
			}

		if($UserOK == null){
//			pr('get out');
			$this->_controller->redirect(array('controller' => 'users', 'action' => 'logout'));
		}
		else {
//			pr('good to see you');
		}
	}

	public function ConditionsUserRoll() {
		$rightsArray = null;
		// Ab Benutzerstufe 6 sehen die User nicht die höheren Benutzer
		if($this->_controller->Auth->user('roll_id') > 5){
			$rightsArray = array('User.roll_id >' => 5);
		}
		return $rightsArray;
	}

	public function ConditionsTestingcompRoll($step,$table) {

		if($this->_controller->Auth->user('roll_id') > $step){
			$condition = array($table . '.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'));
		}
		else {
			$condition = null;
		}
		return $condition;
	}

	// Es werden alle User mit einem Rollenid höher 5 gesammelt
	public function UsertoFind() {
		$UsertoFindArray = array();
		$this->_controller->User->recursive = -1;
		$UsertoFind = ($this->_controller->User->find('list',array(
									'conditions' => array(
										'User.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
										$this->_controller->Autorisierung->ConditionsUserRoll()
										)
									)
								)
							);

		foreach($UsertoFind as $key => $_UsertoFind){
			$UsertoFindArray[$key] = $key;
		}
		return $UsertoFindArray;
	}

	// Prüfbericht wird auf Zugehörigkeit getestet
	public function IsThisMyReport($id) {

		if (!$this->_controller->Reportnumber->exists($id)) {
			throw new NotFoundException(__('Invalid reportnumber'));
		}

		$this->_controller->Reportnumber->recursive = -1;
		$reportnumberTest = $this->_controller->Reportnumber->find('first', array('conditions' => array('id' => $id)));

		$IsOkay = 0;

		// Bei externen Unternehmen müssen alle Bericht im Objekt angezeigt werden
		if(AuthComponent::user('Testingcomp.extern') == 1) $IsOkay = 2;

		// Zuerst ob das Projekt übereinstimmt
		foreach($this->_controller->Session->read('conditionsTopprojects') as $_conditionsTopprojects){
			if($_conditionsTopprojects == $reportnumberTest['Reportnumber']['topproject_id']){
				$IsOkay++;
				break;
			}
		}

		// Test ob die Prüfunternehmen übereinstimmen
		foreach($this->_controller->Session->read('conditionsTestingcomps') as $_conditionsTestingcomps){
			if($_conditionsTestingcomps == $reportnumberTest['Reportnumber']['testingcomp_id']){
				$IsOkay++;
				break;
			}
		}

		// Test ob die Firmenkennung des eingeloggten Users mit der des Prüfberiches übereinstimmt
		if(($this->_controller->Auth->user('testingcomp_id') == $reportnumberTest['Reportnumber']['testingcomp_id']) || $this->_controller->Auth->user('roll_id') < 5){
			$IsOkay++;
		}

		if(Configure::check('ShowWelderTestGlobal')&& Configure::read('ShowWelderTestGlobal')== true ) {

			$this->_controller->loadmodel('Testingmethod');
			$testingmethod =  $this->_controller->Testingmethod->find('first', array('conditions' => array('Testingmethod.id' => $reportnumberTest['Reportnumber']['testingmethod_id'])));

			if ($testingmethod ['Testingmethod']['value'] == 'vtst') $IsOkay = 3;
		}
		if(Configure::check('ShowNeGlobally')&& Configure::read('ShowNeGlobally')== true && Configure::check('RepairsEnabled')&& Configure::read('RepairsEnabled') && $reportnumberTest['Reportnumber']['result'] == 2 ) {
			 $IsOkay = 3;
		}
		if($IsOkay == 3){
			return true;
		}
		else {

			$LoggerArray = array('AutorisierungError' => array('user' => $this->_controller->Auth->user(),'data' => $reportnumberTest));

			$this->_controller->Session->setFlash(__('This report is not yours.'));

			if($this->_controller->layout == 'ajax'){
				$this->_controller->set('shutdown',true);
				$this->_controller->render('unauthorized');
				return false;
			}
			if($this->_controller->layout == 'modal') {
				$this->_controller->set('shutdown',true);
				$this->_controller->render('unauthorized_modal');
				return false;
			}
		}
	}


	public function Logger($id,$message=array()) {

		$this->_controller->loadModel('Log');

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];


		if($this->_controller->request->projectvars['VarsArray'][0]){
			$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		}
		else {
			$projectID = 0;
		}

		if($this->_controller->request->projectvars['VarsArray'][1]){
			$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		}
		else {
			$cascadeID = 0;
		}

		if($this->_controller->request->projectvars['VarsArray'][2]){
			$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		}
		else {
			$orderID = 0;
		}

		if($this->_controller->Auth->user('id')){
			$userId = $this->_controller->Auth->user('id');
		}
		else {
			$userId = 0;
		}

		if($id == null) $id = 0;

		$Message = array('Content'=>array());

		// falls die Keys aus Ziffern bestehen, muss das korregiert werden, sonst kommt eine Fehlermeldung
		if(isset($message) && count($message) > 0){
			foreach($message as $_key => $_message){
				$Message['Content']['n'.$_key] = $_message;
			}
		}

		$XmlMessage = Xml::fromArray($Message, array('format' => 'tags')); // You can use Xml::build() too
		$XmlMessageXML = $XmlMessage->asXML();

		//$controller = @$this->_controller->Auth->request->params['controller'];
		//$action = @$this->_controller->Auth->request->params['action'];

		$controller = @$this->_controller->request->controller;
		$action = @$this->_controller->request->action;

		$data = array(
			'topproject_id' =>	$projectID,
			'cascade_id' => $cascadeID,
			'order_id' => $orderID,
			'current_id' => $id,
			'user_id' => $userId,
			'message' => $XmlMessageXML,
			'controller' => $controller,
			'action' => $action
		);

		$this->_controller->Log->create();
		$this->_controller->Log->save($data);
	}

	public function History($id, $Verfahren) {
		return $this->HistoryNew($id);
		//return $this->HistoryOld($id, $Verfahren);
	}

	public function HistoryOld($id,$Verfahren) {

		$this->_controller->loadModel('Log');
		$this->_controller->loadModel('User');
		$this->_controller->User->recursive = -1;
		$this->_controller->Reportnumber->recursive = 0;

		// Daten für den neuen Eintrag vorbereiten
		$logs = array();
		$model = array($this->_controller->request->params['controller'],'users');
		$projectURL = $this->_controller->request->projectvars['VarsArray'][0];
		$orderURL = $this->_controller->request->projectvars['VarsArray'][3];
		$userId = $this->_controller->Auth->user('id');

		if($this->_controller->request->projectvars['VarsArray'][0]){
			$projectURL = $this->_controller->request->projectvars['VarsArray'][0];
		}

		if($this->_controller->request->projectvars['VarsArray'][3]){
			$orderURL = $this->_controller->request->projectvars['VarsArray'][3];
		}


		$this->_controller->paginate = array(
    		'conditions' => array(
								'controller' => $model,
								'topproject_id' => $projectURL,
								'order_id' => $orderURL,
								'current_id' => $id
							),
			'order' => array('id' => 'asc'),
			'limit' => 2500
		);

		$logs = $this->_controller->paginate('Log');

		$logsArray = array();
		$result = array();
		$resultProgress = array();
		$allActions = array();

		// Der Name des Bearbeiters wird eingefügt
		// Der XML-String wird in ein Array umgewandelt
		// Das Array wird in die verschiedenen Actions aufgeteilt

		if(count($logs) > 0){
			foreach($logs as $_key => $_logs){
				$user = $this->_controller->User->find('first', array('conditions' => array('User.' . $this->_controller->User->primaryKey => $_logs['Log']['user_id'])));
				$logs[$_key]['Log']['message'] = $this->_controller->Xml->XmltoArray($_logs['Log']['message'],'string', null);
				$logs[$_key]['Log']['user'] = $user['User']['name'];
				$logs[$_key]['Log']['num'] = $_key;
				$logsArray[$_logs['Log']['action']][] = $logs[$_key]['Log'];
				$allActions[$_logs['Log']['action']] = $_logs['Log']['action'];
			}

			foreach($allActions as $_key => $_allActions){

				if($_key == 'add'){
					$logs = $this->_controller->Autorisierung->HistoryReportAdd($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'duplicat'){
					$logs = $this->_controller->Autorisierung->HistoryReportDublicat($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'duplicatevalution'){
					$logs = $this->_controller->Autorisierung->HistoryReportDublicatevalution($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'edit'){
					$logs = $this->_controller->Autorisierung->HistoryReportEdit($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'editevalution'){
					$logs = $this->_controller->Autorisierung->HistoryReportEditevalution($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'weldassistent'){
					$logs = $this->_controller->Autorisierung->HistoryReportWeldassistent($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'delete'){
					$logs = $this->_controller->Autorisierung->HistoryReportDelete($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'deleteevalution'){
					$logs = $this->_controller->Autorisierung->HistoryReportDeleteevalution($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'status'){
					$logs = $this->_controller->Autorisierung->HistoryReportStatus($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'loggedin'){
					$logs = $this->_controller->Autorisierung->HistoryReportLoggedin($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'editableUp'){
					$logs = $this->_controller->Autorisierung->HistoryReportEditableUp($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'images'){
					$logs = $this->_controller->Autorisierung->HistoryReportImages($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'imagediscription'){
					$logs = $this->_controller->Autorisierung->HistoryReportImagediscription($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'files'){
					$logs = $this->_controller->Autorisierung->HistoryReportFiles($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'delfile'){
					$logs = $this->_controller->Autorisierung->HistoryReportDelfile($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'status1'){
					$logs = $this->_controller->Autorisierung->HistoryStatus1($_key,$logs,$logsArray,$Verfahren);
				}
				if($_key == 'status2'){
					$logs = $this->_controller->Autorisierung->HistoryStatus2($_key,$logs,$logsArray,$Verfahren);
				}

			}
		}
		else{
		}
//pr($logs);
		return $logs;
	}

	public function HistoryNew($id) {

		$this->_controller->loadModel('Log');
		$this->_controller->Log->recursive = 0;

		$db = $this->_controller->Log->getDataSource();
		$options = array(
			'conditions'=>array(
				'Log.controller' => array(strtolower($this->_controller->name), 'users'),
				'Log.current_id' => $id
			)
		);

		if($this->_controller->name == 'Reportnumbers') {
			$verfahren = $this->_controller->Reportnumber->Testingmethod->find('list', array(
				'limit'=>1,
				'fields'=>array('Testingmethod.id','Testingmethod.value'),
				'conditions'=>array('Reportnumber.id'=>$id),
				'joins'=>array(array('table'=>'reportnumbers', 'alias'=>'Reportnumber', 'conditions'=>array('Reportnumber.testingmethod_id=Testingmethod.id')))
			));
			$verfahren = reset($verfahren);
			$this->_controller->Reportnumber->ExaminerTime->recursive = -1;
			$options['conditions'] = array(
				'OR' => array(
					$options['conditions'],
					array(
//						'Log.controller'=>'examiners',
//						'Log.action LIKE' => '%workload%',
						'OR' => array(
							'Log.message LIKE'=> '%<reportnumber\_id>'.$id.'</reportnumber\_id>%',
							'Log.message LIKE'=> '%<nreportnumber\_id>'.$id.'</nreportnumber\_id>%',
						)
					)
				)
			);
		}


		if(!empty($this->_controller->request->projectvars['VarsArray'][0])) $options['conditions']['topproject_id'] = $this->_controller->request->projectvars['VarsArray'][0];
		if(!empty($this->_controller->request->projectvars['VarsArray'][3])) $options['conditions']['order_id'] = $this->_controller->request->projectvars['VarsArray'][3];

		//pr($options);

		//$logs = $this->_controller->Log->find('all', $options);

		$options = array_merge(
			array(
				'conditions'=>array(
					'Log.controller'=>strtolower($this->_controller->name),
					'Log.id'=>$id
				),
				'order' => array('Log.created' => 'asc'),
			),
			array('class'=>'mymodal'),
			$options
		);
		$this->_controller->paginate = array_merge(
			$options,
			array('limit'=>35)
		);

//		pr($this->_controller->paginate);
		$this->_controller->paginate('Log');
		$logs = $this->_controller->Log->find('all', $options);

//		pr(array_map(function($elem) {return $elem['Log']['action']; }, $logs));
		$logReturn = array();
		$reportData = array('part'=>'generally');

		foreach($logs as $log) {
			if(preg_match('/<nreportnumber_id>([\d]+)<\/nreportnumber_id>/i', $log['Log']['message'], $repnum)) {
				$this->_controller->Reportnumber->recursive = 0;
				$reportnumber = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>end($repnum))));

//				$verfahren = $reportnumber['Testingmethod']['value'];
			}
			$log['Log']['message'] = $this->_controller->Xml->XmltoArray($log['Log']['message'],'string', null);

			if(preg_match('/status\s*\d/', $log['Log']['action'])) $log['Log']['action'] = 'status';
			if(isset($log['Log']['message']->nprint)) {
				switch(trim($log['Log']['message']->nprint)) {
					case '0':
					case '1':
					case '2':
						$log['Log']['controller']='reportnumbers';
						$log['Log']['action'] = 'pdf';
						break;

					case 'label':
						$log['Log']['controller']='reportnumbers';
						$log['Log']['action'] = 'pdf';
						break;

					case 'workload':
						$log['Log']['controller']='examiners';
						$log['Log']['action'] = 'print_workload';
				}
			}
			$method = 'History'.ucfirst(Inflector::camelize($log['Log']['controller'])).ucfirst(Inflector::camelize($log['Log']['action']));
			if(method_exists($this, $method)) {
				$data = $this->$method($log, $reportData);
				if(is_array($data)) {
					if(!empty($data[1]) and is_array($data[1])) {
						foreach($data[1] as $id=>&$entry) {
							if(preg_match('/([a-z0-9-_]+):/i', $entry, $keys)) {
								$entry = preg_replace('/^(.*[ ]{0,1})'.$keys[1].':/', '$1'.$this->KeyToDiscription($keys[1], $verfahren).':', $entry);
							}
						}
					}

					$logReturn[] = array_merge(
						array(
							$log['Log']['created'],
							$log['User']['name']
						),
						$data
					);
				}
			} else {
				pr('missing: '.$method);
//				pr($log['Log']);
			}
			//pr($method);
		}

		return $logReturn;

		//return $logReturn;
	}

	public function KeyToDiscription($keyname, $testingmethod) {
		$xml = $this->_controller->Xml->XmltoArray($testingmethod, 'file', ucfirst($testingmethod));
		foreach($xml as $part) {
			if(isset($part->{trim($keyname)})) {
				if(!empty($part->{trim($keyname)}->discription->deu)) {
					$keyname = trim($part->{trim($keyname)}->discription->deu);
					break;
				}
			}
		}

		return $keyname;
	}

	public function HistoryExaminersAddWorkload($log, &$prevData) {
		$part = 'workload';

		$result = array(
			__('Examiner workload added'),
			array()
		);

		if(!isset($log['Log']['message']->nexaminer)) {
			$this->_controller->loadModel('Examiner');
			$exam = $this->_controller->Examiner->find('first', array('conditions'=>array('Examiner.'.$this->_controller->Examiner->primaryKey=>trim($log['Log']['message']->nexaminer_id))));
			if(!empty($exam)) $log['Log']['message']->nexaminer = $exam['Examiner']['display'];
			else $log['Log']['message']->nexaminer = __('Unknown examiner');
		}

		foreach($log['Log']['message'] as $key=>$data) {
			$prevData[$part][trim($log['Log']['message']->nid)][$key] = $data;
		}
		$result[1] = array($log['Log']['message']->nexaminer, null, null);

		if(!empty($log['Log']['message']->nwaiting_time_start)) {
			$wstart = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->nwaiting_time_start));
			if(is_object($wstart)) $result[1][1] .= $wstart->format('d.m.Y H:i');
		}
		if(!empty($log['Log']['message']->nwaiting_time_end)) {
			$wend = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->nwaiting_time_end));

			if(isset($wstart) && is_object($wstart) && $wstart->format('Y-m-d') == $wend->format('Y-m-d')) {
				if(is_object($wend)) $result[1][1] .= ' - '.$wend->format('H:i');
			} else {
				if(is_object($wend)) $result[1][1] .= ' - '.$wend->format('d.m.Y H:i');
			}
		}

		if(!empty($log['Log']['message']->ntesting_time_start)) {
			$tstart = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->ntesting_time_start));
			if(is_object($tstart)) $result[1][2] .= $tstart->format('d.m.Y H:i');
		}
		if(!empty($log['Log']['message']->ntesting_time_end)) {
			$tend = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->ntesting_time_end));

			if(isset($tstart) && is_object($tstart) && $tstart->format('Y-m-d') == $tend->format('Y-m-d')) {
				if(is_object($tend)) $result[1][2] .= ' - '.$tend->format('H:i');
			} else {
				if(is_object($tend)) $result[1][2] .= ' - '.$tend->format('d.m.Y H:i');
			}
		}

		$result[1] = array_map(function($elem) { return trim($elem, ' -'); }, $result[1]);
		if(!empty($result[1][1])) $result[1][1] = '<strong>'.__('Waiting time: ').'</strong>'.$result[1][1];
		if(!empty($result[1][2])) $result[1][2] = '<strong>'.__('Testing time: ').'</strong>'.$result[1][2];

		$result[1] = array(join('<br />', array_filter($result[1])));
		return $result;
	}

	public function HistoryExaminersDeleteWorkload($log, &$prevData) {
		$part = 'workload';

		$result = array(
			__('Examiner workload deleted'),
			array()
		);

		if(!isset($log['Log']['message']->nexaminer)) {
			$this->_controller->loadModel('Examiner');
			$exam = $this->_controller->Examiner->find('first', array('conditions'=>array('Examiner.'.$this->_controller->Examiner->primaryKey=>trim($log['Log']['message']->nexaminer_id))));
			if(!empty($exam)) $log['Log']['message']->nexaminer = $exam['Examiner']['display'];
			else $log['Log']['message']->nexaminer = __('Unknown examiner');
		}

		$result[1] = array(trim($log['Log']['message']->nexaminer), null, null);

		if(!isset($log['Log']['message']->nwaiting_time_start)) {
			$wstart = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->nwaiting_time_start));
			if($wstart) $result[1][1] .= $wstart->format('d.m.Y H:i');
		}
		if(!isset($log['Log']['message']->nwaiting_time_end)) {
			$wend = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->nwaiting_time_end));
			if($wend) {
				if(isset($wstart) && $wstart->format('Y-m-d') == $wend->format('Y-m-d')) {
					$result[1][1] .= ' - '.$wend->format('H:i');
				} else {
					$result[1][1] .= ' - '.$wend->format('d.m.Y H:i');
				}
			}
		}

		if(!isset($log['Log']['message']->ntesting_time_start)) {
			$tstart = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->ntesting_time_start));
			if($tstart) $result[1][2] .= $tstart->format('d.m.Y H:i');
		}
		if(!isset($log['Log']['message']->ntesting_time_end)) {
			$tend = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->ntesting_time_end));
			if($tend) {
				if(isset($tstart) && $tstart->format('Y-m-d') == $tend->format('Y-m-d')) {
					$result[1][2] .= ' - '.$tend->format('H:i');
				} else {
					$result[1][2] .= ' - '.$tend->format('d.m.Y H:i');
				}
			}
		}

		$result[1] = array_map(function($elem) { return trim($elem, ' -'); }, $result[1]);
		if(!empty($result[1][1])) $result[1][1] = '<strong>'.__('Waiting time: ').'</strong>'.$result[1][1];
		if(!empty($result[1][2])) $result[1][2] = '<strong>'.__('Testing time: ').'</strong>'.$result[1][2];

		$result[1] = array(join('<br />', array_filter($result[1])));

		if(isset($prevData[$part][trim($log['Log']['message']->nid)])) {
			unset($prevData[$part][trim($log['Log']['message']->nid)]);
		}

		if(empty($result[1])) $result[1] = null;
		return $result;
	}

	public function HistoryExaminersDuplicateWorkload($log, &$prevData) {
		$part = 'workload';

		$result = array(
			__('Examiner workload duplicated'),
			array()
		);

		if(!isset($log['Log']['message']->nexaminer)) {
			$this->_controller->loadModel('Examiner');
			$exam = $this->_controller->Examiner->find('first', array('conditions'=>array('Examiner.'.$this->_controller->Examiner->primaryKey=>trim($log['Log']['message']->nexaminer_id))));
			if(!empty($exam)) $log['Log']['message']->nexaminer = $exam['Examiner']['display'];
			else $log['Log']['message']->nexaminer = __('Unknown examiner');
		}

		foreach($log['Log']['message'] as $key=>$data) {
			$prevData[$part][trim($log['Log']['message']->nid)][$key] = $data;
		}
		$result[1] = array($log['Log']['message']->nexaminer, null, null);

		if(!empty($log['Log']['message']->nwaiting_time_start)) {
			$wstart = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->nwaiting_time_start));
			$result[1][1] .= $wstart->format('d.m.Y H:i');
		}
		if(!empty($log['Log']['message']->nwaiting_time_end)) {
			$wend = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->nwaiting_time_end));

			if(isset($wstart) && $wstart->format('Y-m-d') == $wend->format('Y-m-d')) {
				$result[1][1] .= ' - '.$wend->format('H:i');
			} else {
				$result[1][1] .= ' - '.$wend->format('d.m.Y H:i');
			}
		}

		if(!empty($log['Log']['message']->ntesting_time_start)) {
			$tstart = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->ntesting_time_start));
			$result[1][2] .= $tstart->format('d.m.Y H:i');
		}
		if(!empty($log['Log']['message']->ntesting_time_end)) {
			$tend = DateTime::createFromFormat('Y-m-d H:i:s', trim($log['Log']['message']->ntesting_time_end));

			if(isset($tstart) && $tstart->format('Y-m-d') == $tend->format('Y-m-d')) {
				$result[1][2] .= ' - '.$tend->format('H:i');
			} else {
				$result[1][2] .= ' - '.$tend->format('d.m.Y H:i');
			}
		}

		$result[1] = array_map(function($elem) { return trim($elem, ' -'); }, $result[1]);
		if(!empty($result[1][1])) $result[1][1] = '<strong>'.__('Waiting time: ').'</strong>'.$result[1][1];
		if(!empty($result[1][2])) $result[1][2] = '<strong>'.__('Testing time: ').'</strong>'.$result[1][2];

		$result[1] = array(join('<br />', array_filter($result[1])));
		return $result;
	}
	public function HistoryExaminersEditWorkload($log, &$prevData) {
		$part = 'workload';

		$result = array(
			__('Examiner workload edited'),
			array()
		);

		if(!isset($log['Log']['message']->nexaminer)) {
			$this->_controller->loadModel('Examiner');
			$exam = $this->_controller->Examiner->find('first', array('conditions'=>array('Examiner.'.$this->_controller->Examiner->primaryKey=>trim($log['Log']['message']->nexaminer_id))));
			if(!empty($exam)) $log['Log']['message']->nexaminer = $exam['Examiner']['display'];
			else $log['Log']['message']->nexaminer = __('Unknown examiner');
		}

		$result[1][] = $log['Log']['message']->nexaminer;

		if(isset($log['Log']['message']->nforce_save)) {
			$result[1][] = '<em>'.__('Saved despite collisions or errors').'</em>';
			unset($log['Log']['message']->nforce_save);
		}
		//pr($prevData[$part][trim($log['Log']['message']->nid)]);
		//pr($log['Log']['message']);

		foreach($log['Log']['message'] as $key=>$data) {
			if(!empty($data) && preg_match('/^n(waiting|testing)\_time/i', $key)) {
				$format = array('Y','m','d','H','i','s');
				$tmp = preg_split('/[ :-]+/', trim($data));
				$format = array_intersect_key($format, $tmp);

				$data = DateTime::createFromFormat(join('-', $format), join('-', $tmp))->format('Y-m-d H:i');


			}

			if($key != 'nexaminer_id') {
				if(isset($prevData[$part][trim($log['Log']['message']->nid)][$key])) {
					$old = trim($prevData[$part][trim($log['Log']['message']->nid)][$key]);

					if($old != trim($data)) {
						$result[1][] = '<strong>'.preg_replace('/^\s*n(.*)\s*$/', '$1', $key).'</strong><br />'.__('Old').': '.$old.'<br />'.__('New').': '.trim($data);
					}
				} else {
					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*)\s*$/', '$1', $key).':</strong><br />'.__('New').': '.trim($data);
				}
			}

			$prevData[$part][trim($log['Log']['message']->nid)][$key] = trim($data);
		}

		return $result;
	}

	public function HistoryReportnumbersAdd($log) {
		return array(
			__('Testingreport created'),
			null
		);
	}

	public function HistoryReportnumbersDelete($log) {
		return array(
			__('Testingreport deleted'),
			null
		);
	}

	public function HistoryReportnumbersDeleteevalution($log, &$prevData) {
		$this->_controller->Reportnumber->recursive = 0;
		$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.'.$this->_controller->Reportnumber->primaryKey=>trim($log['Log']['message']->nreportnumber_id))));

		$result = array(
			__('Evaluation deleted'),
			null
		);

		$part = 'evaluation';

		unset($prevData[$part][trim($log['Log']['message']->nid)]);
		foreach($log['Log']['message'] as $key=>$data) {
			$result[1][] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
		}

		return $result;
	}

	public function HistoryReportnumbersDelfile($log) {
		return array(
			__('File deleted'),
			array($log['Log']['message']->nReportfile->basename)
		);
	}

	public function HistoryReportnumbersDuplicat($log) {
		$result = array(
				__('Testingreport duplicated'),
				array()
		);

		$oldKey = (isset($log['Log']['message']->noldId) ? trim($log['Log']['message']->noldId) : (isset($log['Log']['message']->oldId) ? trim($log['Log']['message']->oldId) : null));

		if(empty($oldKey)) {
			foreach($log['Log']['message'] as $key=>$data) {
				$result[1][] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
			}
		} else {
			$this->_controller->loadModel('Reportnumber');
			$this->_controller->Reportnumber->recursive = 0;

			$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$oldKey)));
			if(empty($report)) {
				$result[1][] = __('removed report');
			} else {
				$year = $report['Reportnumber']['year'];
				if($year < 2000) $year += 2000;
				$result[1][] = (trim($log['Log']['message']->nthisId) == trim($log['Log']['message']->nnewId) ? __('original report') : __('target report') ).': '
						.$report['Topproject']['identification'].' / '
								.$report['Report']['identification'].' / '
										.$report['Testingmethod']['name'].' '
												.$year.'-'.$report['Reportnumber']['number'];
			}
		}

		return $result;
	}

	public function HistoryReportnumbersVersionize($log) {
		$result = array(
			__('Revision created'),
			array()
		);

		foreach($log['Log']['message'] as $key=>$data) {
			$result[1][] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
		}

		return $result;
	}

	public function HistoryReportnumbersDuplicatevalution($log) {
		$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $log['Log']['current_id'])));
		$Verfahren = ucfirst($report['Testingmethod']['value']);
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$result = array(
			__('Evaluation duplicated'),
			array()
		);
		$part = 'evaluation';

		if(!isset($prevData[$part])) $prevData[$part] = array();
		$eval = trim(isset($log['Log']['message']->n0) ? $log['Log']['message']->n0->id : $log['Log']['message']->nid);

		if(isset($log['Log']['message']->n0)) {
			foreach($log['Log']['message'] as $key=>$weld) {
				// Wenn der Modelname mit ins Array geschrieben wurde, diesen überspringen
				if(trim(key($weld)) == $ReportEvaluation) $weld = $log['Log']['message']->$key->$ReportEvaluation;
				if(isset($weld->id)) $eval = trim($weld->id);

				foreach($weld as $key=>$data) {
					$prevData[$part][$eval]['n'.ltrim(trim($key),'n')] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
					$result[1][$eval][]=$prevData[$part][$eval];
				}
			}
		} else {
			foreach($log['Log']['message'] as $key=>$data) {
				$prevData[$part][$eval][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
			}
			$result[1][]=$prevData[$part][$eval];
		}

		$result = array(
			__('Evaluation duplicated'),
			array_values($prevData[$part][$eval])
		);

		return $result;
	}

	public function HistoryReportnumbersEdit($log, &$prevData) {
		$this->_controller->Reportnumber->recursive = 0;

		if(isset($log['Log']['message']->nAutorisierungError)){
			$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => trim($log['Log']['message']->nAutorisierungError->data->Reportnumber->id))));
			return array(
				__('Authorisation error'),
				__('%s tried to open testingreport %s',
					$log['Log']['message']->nAutorisierungError->user->name,
					// siehe /app/view/Helper/PdfHelper/ContructReportName()
					$report['Topproject']['identification'].'/'.$report['Report']['identification'].'/'.$report['Reportnumber']['number'].'/'.$report['Reportnumber']['year']
				)
			);
		}

		$this->_controller->Reportnumber->recursive = 0;
		$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $log['Log']['current_id'])));
		$Verfahren = ucfirst($report['Testingmethod']['value']);

		// XML holen, um den Keys das Model zuzuweisen
		$xml = $this->_controller->Xml->XmltoArray($report['Testingmethod']['value'], 'file', ucfirst($report['Testingmethod']['value']));

/*
		// zwischen General- und Prüfdaten zu unterscheiden
		$part = $prevData['part'];
		if($part == 'generally') $prevData['part']='specific';
		else $prevData['part']='generally';

		if(!isset($prevData[$part])) $prevData[$part] = array();

		if(empty($prevData[$part])) {
			foreach($log['Log']['message'] as $key=>$data) {
				$prevData[$part][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
			}

			$result = array(
				__('First entry for %s', __(strtolower($part).' data')),
				array_values($prevData[$part])
			);
		} else {
			$result = array(
				__('Edited %s', __(ucfirst($part).' data')),
				array()
			);

			foreach($log['Log']['message'] as $key=>$data) {
				if(!isset($prevData[$part][trim($key)])) {
					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': <br />'.__('New').': '.trim($data);
					$prevData[$part][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
				} elseif($prevData[$part][trim($key)] != preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data)) {
					if(!preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][trim($key)]), $old)) {
						$old = array(null, null);
					}

					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
					$prevData[$part][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
				}
			}
		}
*/

//	foreach($log['Log']['message'] as $key=>$data) {
//			$model = $xml->xpath('*/*[key="'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).'"]/model');
//			$part = strtolower(preg_replace('/Report'.$Verfahren.'(generally|specific)/i', '$1', reset($model)));
//			if(empty($prevData[$part])) {
//				$prevData[$part][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
//				$result = array(
//						__('First entry for %s', __(strtolower($part).' data')),
//						array_values($prevData[$part])
//				);
//			} else {
//				$result = array(
//						__('Edited %s', __(ucfirst($part).' data')),
//						array()
//				);
//
//				if(!isset($prevData[$part][trim($key)])) {
//					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': <br />'.__('New').': '.trim($data);
//					$prevData[$part][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
//				} elseif($prevData[$part][trim($key)] != preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data)) {
//					if(!preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][trim($key)]), $old)) {
//						$old = array(null, null);
//					}
//
//					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
//					$prevData[$part][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
//				}
//			}
//		}
//
//		if(empty($result[1])) return null;
//		return $result;
//	}

		foreach($log['Log']['message'] as $key=>$data) {
			$part = strtolower(preg_replace('/[n]*Report'.$Verfahren.'(generally|specific)/i', '$1', $key));
			foreach($data as $_field=>$_value) {
				if(empty($prevData[$part])) {
					$prevData[$part][trim($_field)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $_field).': '.trim($_value);
					$result = array(
						__('First entry for %s', __(strtolower($part).' data')),
						array_values($prevData[$part])
					);
				} else {
					$result = array(
						__('Edited %s', __(ucfirst($part).' data')),
						array()
					);

					if(!isset($prevData[$part][trim($_field)])) {
						if(trim($_value) == '') return null;

						$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $_field).':</strong>'.'<br />'.__('Old').': <br />'.__('New').': '.trim($_value);
						$prevData[$part][trim($_field)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $_field).': '.trim($_value);
					} elseif($prevData[$part][trim($_field)] != preg_replace('/^\s*n(.*?)\s*$/', '$1', $_field).': '.trim($_value)) {
						if(!preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][trim($_field)]), $old)) {
							$old = array(null, null);
						}

						if(trim($_value) == $old[1]) return null;

						$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $_field).':</strong>'.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($_value);
						$prevData[$part][trim($_field)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $_field).': '.trim($_value);
					}
				}
			}
		}

		if(empty($result[1])) return null;
		return $result;
	}

	public function HistoryReportnumbersEditevalution($log, &$prevData) {
		$this->_controller->Reportnumber->recursive = 0;

		if(isset($log['Log']['message']->nAutorisierungError)){
			$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => trim($log['Log']['message']->nAutorisierungError->data->Reportnumber->id))));
			return array(
				__('Authorisation error'),
				__('%s tried to open testingreport %s',
					$log['Log']['message']->nAutorisierungError->user->name,
					// siehe /app/view/Helper/PdfHelper/ContructReportName()
					$report['Topproject']['identification'].'/'.$report['Report']['identification'].'/'.$report['Reportnumber']['number'].'/'.$report['Reportnumber']['year']
				)
			);
		}

		$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $log['Log']['current_id'])));
		$Verfahren = ucfirst($report['Testingmethod']['value']);

		$part = 'evaluation';

		if(!isset($prevData[$part])) $prevData[$part] = array();

		$eval = trim(isset($log['Log']['message']->n0) ? $log['Log']['message']->n0->id : $log['Log']['message']->nid);

		if(!isset($prevData[$part][$eval])) {
			$result = array(
				__('New entry for %s', __(strtolower($part).' data')),
				array()
			);

			if(isset($log['Log']['message']->n0)) {
				foreach($log['Log']['message'] as $weld) {
					foreach($weld as $key=>$data) {
/*						if(!isset($prevData[$part][$eval]['n'.ltrim(trim($key),'n')]) || !preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][$eval]['n'.ltrim(trim($key),'n')]), $old)) {
							$old = array(null, null);
						}

						if($old[1] == trim($data)) continue;
*/
//						$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong> '.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
						$result[1][] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
						$prevData[$part][$eval]['n'.ltrim(trim($key),'n')] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
					}
				}
			} else {
				foreach($log['Log']['message'] as $key=>$data) {
/*					if(!isset($prevData[$part][$eval][trim($key)]) || !preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][$eval][trim($key)]), $old)) {
						$old = array(null, null);
					}

					if($old[1] == trim($data)) continue;
*/
//					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong> '.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
					$result[1][] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
					$prevData[$part][$eval][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
				}
			}
		} else {
			if(isset($log['Log']['message']->n0)) {
				$result = array(
					__('Edited complete weld'),
					array()
				);

				foreach($log['Log']['message'] as $weld) {
					if(isset($weld->id)) $eval = trim($weld->id);
					foreach($weld as $key=>$data) {
						if(!isset($prevData[$part][$eval]['n'.ltrim(trim($key),'n')]) || $prevData[$part][$eval]['n'.ltrim(trim($key),'n')] != preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data)) {

							if(!isset($prevData[$part][$eval]['n'.ltrim(trim($key),'n')]) || !preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][$eval]['n'.ltrim(trim($key),'n')]), $old)) {
								$old = array(null, null);
							}

							if($old[1] == trim($data)) continue;
							$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
							$prevData[$part][$eval]['n'.ltrim(trim($key),'n')] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
						}
					}
					break;
				}
			} else {
				$result = array(
					__('Edited %s', __($part.' data')),
					array()
				);

				foreach($log['Log']['message'] as $key=>$data) {
					if(!isset($prevData[$part][$eval][trim($key)]) || $prevData[$part][$eval][trim($key)] != preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data)) {
						if(!isset($prevData[$part][$eval][trim($key)]) || !preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][$eval][trim($key)]), $old)) {
							$old = array(null, null);
						}

						if($old[1] == trim($data)) continue;
						$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
						$prevData[$part][$eval][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
					}
				}
			}
		}
		if(empty($result[1])) return null;
		return $result;
	}

	public function HistoryReportnumbersEditableUp($log, &$prevData) {
		$part = 'evaluation';

		if(isset($log['Log']['message']->nsorting)) {
			if(!isset($prevData[$part]['nsorting']) || $prevData[$part]['nsorting'] != 'sorting: '.trim($log['Log']['message']->nsorting)) {
				$prevData[$part][trim($log['Log']['message']->nid)]['nsorting'] = 'sorting: '.trim($log['Log']['message']->nsorting);
				$result = array(
					__('Sorted evaluation'),
					array()
				);

				foreach($log['Log']['message'] as $data) {
					$result[1][] = trim($data);
				}

			} else {
				return null;
			}
		} else {
			$result = array(
				__('Edited %s directly', __($part.' data')),
				array()
			);

			$eval = trim($log['Log']['message']->nid);
			foreach($log['Log']['message'] as $key=>$data) {
				if(!isset($prevData[$part][$eval][trim($key)]) || $prevData[$part][$eval][trim($key)] != preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data)) {
					if(!isset($prevData[$part][$eval][trim($key)]) || !preg_match('/^[^:]*:\s*(.*)$/', trim($prevData[$part][$eval][trim($key)]), $old)) {
						$old = array(null, null);
					}

					if($old[1] == trim($data)) continue;
					$result[1][] = '<strong>'.preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).':</strong>'.'<br />'.__('Old').': '.$old[1].'<br />'.__('New').': '.trim($data);
					$prevData[$part][$eval][trim($key)] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
//					$result[1][] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
				}
			}
		}
		return $result;
	}

	public function HistoryReportnumbersFiles($log) {
		return array(
			__('File uploaded'),
			array($log['Log']['message']->nbasename)
		);

	}

	public function HistoryReportnumbersImagediscription($log, &$prevData) {
		$part = 'images';

		if(isset($log['Log']['message']->ndelmessage)) {
			$result = array(
				__('Image deleted'),
				$prevData[$part][trim($log['Log']['message']->nnid)]['name']
			);
			unset($prevData[$part][trim($log['Log']['message']->nnid)]);

			return $result;
		}

		$result = array(
			null,
			array()
		);

		$img = trim($log['Log']['message']->nid);
		if(!isset($prevData[$part][$img]['discription']) || $prevData[$part][$img]['discription'] != trim($log['Log']['message']->ndiscription)) {
			if(!isset($prevData[$part][$img]['discription'])) {
				$result[0] = __('Image discription set');
				if(!empty($prevData[$part][$img]['name'])) $result[1][] = '<strong>'.__('Imagename').': </strong>'.$prevData[$part][$img]['name'];
				$result[1][] = '<strong>discription</strong>: '.trim($log['Log']['message']->ndiscription);
			} else {
				$result[0] = __('Image discription changed');
				if(!empty($prevData[$part][$img]['name'])) $result[1][] = '<strong>'.__('Imagename').': </strong>'.$prevData[$part][$img]['name'];
				$result[1][] = '<strong>discription</strong>:<br />'.__('old').': '.(isset($prevData[$part][$img]['discription']) ? trim($prevData[$part][$img]['discription']) : '').'<br />'.__('new').': '.trim($log['Log']['message']->ndiscription);
			}
			$prevData[$part][$img]['discription'] = trim($log['Log']['message']->ndiscription);
		}

		if(empty($result[1])) return null;
		return $result;
	}

	public function HistoryReportnumbersImages($log, &$prevData) {
		$part = 'images';

		$prevData[$part][trim($log['Log']['message']->nid)] = array(
			'name' => isset($log['Log']['message']->nbasename) ? trim($log['Log']['message']->nbasename) : '',
			'discription' => ''
		);

		return array(
			__('Image uploaded'),
			empty($prevData[$part][trim($log['Log']['message']->nid)]['name']) ? '' : array($prevData[$part][trim($log['Log']['message']->nid)]['name'])
		);
	}

	public function HistoryReportnumbersPdf($log) {
		$result = array(
			null,
			null
		);

		switch(trim($log['Log']['message']->nprint)) {
			case '0':
			case '1':
				$result[0] = __('Testingreport printed');
				break;

			case '2':
				$result[0] = __('Raw data testingreport printed');
				break;

			case 'label':
				$result[0] = __('Evaluation label printed');
				switch($log['Log']['message']->nTestingmethod->value) {
					case 'rt':
						$result[1][] = trim($log['Log']['message']->nReportRtEvaluation->description).' '.trim($log['Log']['message']->nReportRtEvaluation->film_position);
						break;

					case 'ht':
					case 'ht1':
						$result[1][] = trim($log['Log']['message']->nReportRtEvaluation->description).' '.trim($log['Log']['message']->nReportRtEvaluation->position).' '.trim($log['Log']['message']->nReportRtEvaluation->comment);
						break;

					default:
						$result[1][] = trim($log['Log']['message']->nReportRtEvaluation->description);
						break;
				}
				break;

			default:
				return null;
		}

		return $result;
	}

	public function HistoryReportnumbersStatus($log) {
		$state = array(__('opened'), __('closed'), __('invoiced'));
		return array(
			__('Testingreport state changed'),
			$state[intval($log['Log']['message']->nReportnumber->status)]
		);
	}

	public function HistoryReportnumbersSave($log, &$prevData) {
		// F�r Savemethode Verfahren und entsprechendes Models aus dem XML holen und danach entweder Edit oder Editevaluation-Auswertung aufrufen
		$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => trim($log['Log']['current_id']))));
		$verfahren = $report['Testingmethod']['value'];

		$xml = $this->_controller->Xml->DatafromXml($verfahren, 'file', ucfirst($verfahren));
		foreach($log['Log']['message']->children() as $child) {
			$key = ltrim(trim($child->getName()),'n');
			$obj = $xml['settings']->xpath('*/*[key="'.$key.'"]');
			$models = array();
			if(count($obj > 0)) {
				foreach($obj as $_obj) {
					$models[] = trim($_obj->model);
				}
			}
			$models = array_unique($models);

			if(count($models) > 1) {
				CakeLog('debug', __('Key %s wird in Verfahren %s %s mal verwendet.', $key, $verfahren, count($models)));
			}

			$models = reset($models);
			if(preg_match('/Report'.ucfirst($verfahren).'(Generally|Specific)/', $models)) {
				return $this->HistoryReportnumbersEdit($log, $prevData);
			} elseif(preg_match('/Report'.ucfirst($verfahren).'Evaluation/', $models)) {
				return $this->HistoryReportnumbersEditevalution($log, $prevData);
			}
		}

		$model = 'Report'.ucfirst($verfahren);
		die();
	}

	public function HistoryReportnumbersWeldassistent($log, &$prevData) {
		$part = 'evaluation';

		if(isset($log['Log']['message']->ndelete)) {
			return array(
				__('Deleted all welds by weld assistent'),
				null
			);

			$prevData[$part] = array();
		}

		$result = array(
				__('Created welds by weld assistent'),
				array()
			);

		$this->_controller->Reportnumber->recursive = 0;
		$report = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.'.$this->_controller->Reportnumber->primaryKey=>$log['Log']['message']->n0->reportnumber_id)));

		$eval = null;
		foreach($log['Log']['message'] as $weld) {
			switch($report['Testingmethod']['value']) {
				case 'ht':
				case 'ht1':
					$result[1][] = trim($weld->description).' '.trim($weld->position).' '.trim($weld->comment);
					break;

				case 'rt':
					$result[1][] = trim($weld->description).' '.trim($weld->film_position);
					break;
			}
			if(isset($weld->id)) $eval = trim($weld->id);

			foreach($weld as $key=>$data) {

				$prevData[$part][$eval]['n'.ltrim(trim($key),'n')] = preg_replace('/^\s*n(.*?)\s*$/', '$1', $key).': '.trim($data);
			}
		}

		return $result;
	}

	public function HistoryReportAdd($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){
			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Erstellt';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfbericht angelegt';
		}

		return $logs;
	}

	public function HistoryReportDublicat($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){
			$oldReport = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => trim($_resultProgress['message']->noldId))));
			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Dupliziert';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfbericht dupliziert aus: '.ucfirst($oldReport['Testingmethod']['value']).'-'.$oldReport['Reportnumber']['number'].', '.$oldReport['Topproject']['projektname'];
		}
		return $logs;
	}

	public function HistoryReportDublicatevalution($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$this->_controller->loadModel($ReportEvaluation);

//pr($logsArray[$_key]);
		foreach($logsArray[$_key] as $_resultProgress){

			$resultProgressMessage = array();
			foreach($_resultProgress['message'] as $_key => $_message){
				if($_key == 'noldid'){
					$oldReport = $this->_controller->$ReportEvaluation->find('first', array('conditions' => array('id' => trim($_message))));

					if(count($oldReport) == 1){
						$logs[$_resultProgress['num']]['Log']['aktion'] = 'Dupliziert';
						$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfbereich '.$oldReport[$ReportEvaluation]['description'].' wurde dupliziert.';
					}

					break;
				}
				else {
					foreach($_message->$ReportEvaluation as $__key => $__message){
						foreach($__message as $___key => $___message){
							$resultProgressMessage[] = array($___key => trim($___message));
						}
					}

					$logs[$_resultProgress['num']]['Log']['aktion'] = 'Dubliziert';
					$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $resultProgressMessage;
					$logs[$_resultProgress['num']]['Log']['keyforXML'] = $ReportEvaluation;

				}
			}
		}

		return $logs;
	}

	public function HistoryReportEdit($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		// um zwischen General- und Prüfdaten zu unterscheiden
		$helpArray  = array('specific','generally');

		// Prüf und Objektdaten werden getrennt
		foreach($logsArray[$_key] as $_key => $_result){

			if($_key % 2 == 0){
				$resultProgress['specific'][] = $_result;
			}
			else {
				$resultProgress['generally'][] = $_result;
			}
		}
/*
			if(!isset($_result['message']->nAutorisierungError)){
			}
*/
		foreach($helpArray as $_helpArray){
			foreach($resultProgress[$_helpArray] as $_key => $_resultProgress){

				$resultProgressMessage = array();

				if(isset($_resultProgress['message']->nAutorisierungError)){
					$logs[$_resultProgress['num']]['Log']['aktion'] = 'Autorisierungsfehler';
					$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $_resultProgress['message']->nAutorisierungError->user->name . ' hat versucht Prüfbericht '.trim($_resultProgress['message']->nAutorisierungError->data->Reportnumber->year) .'-'. trim($_resultProgress['message']->nAutorisierungError->data->Reportnumber->number) . ' aufzurufen';
				}

				elseif(!isset($_resultProgress['message']->nAutorisierungError)){

					if($_helpArray == 'generally'){
						$logs[$_resultProgress['num']]['Log']['keyforXML'] = $ReportSpecific;
					}
					if($_helpArray == 'specific'){
						$logs[$_resultProgress['num']]['Log']['keyforXML'] = $ReportGenerally;
					}

					// Der Erste Eintrag
					if($_key == 0){
						foreach($_resultProgress['message'] as $__key => $message){
							$resultProgressMessage[] = array(substr($__key, 1) => trim($message));
						}
						$logs[$_resultProgress['num']]['Log']['aktion'] = 'Erster Eintrag';
						$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $resultProgressMessage;
					}

					// Ansonnsten wird verglichen was sich geändert hat
					else{
						foreach($_resultProgress['message'] as $__key => $_message){
							foreach($resultProgress[$_helpArray][$_key - 1]['message'] as $___key => $__message){
								if($__key == $___key && trim($_message) != trim($__message)){
									$resultProgressMessage[] = array(substr($__key, 1) => array('old' => trim($__message), 'new' => trim($_message)));
								}
							}
						}

						if(count($resultProgressMessage) > 0){
							$logs[$_resultProgress['num']]['Log']['aktion'] = 'Bearbeitet';
							$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $resultProgressMessage;
						}
						else {
						}
					}
				}
			}
		}
		return $logs;
	}

	public function HistoryReportEditevalution($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		$welds = array('Edit' => array(), 'Add' => array());

		// die Einträge gleicher Nähte muss zusammengefasst werden
		// zuerst werden alle IDs gesammelt und in ein Array geschrieben
		foreach($logsArray[$_key] as $__key => $_resultProgress){
			// Wenn der Logeintrag aus einer Gesamtnahtbearbeitung stammt
			if(!isset($_resultProgress['message']->nid)){
				foreach($_resultProgress['message'] as  $___key => $___message){
					$welds['Edit'][trim($___message->id)] = array();
				}
			}
			// wenn der Logeintrag aus einer Nahtbereichsbearbeitung stammt
			elseif(isset($_resultProgress['message']->nid) && trim($_resultProgress['message']->nid) != ''){
				$welds['Edit'][trim($_resultProgress['message']->nid)] = array();
			}
			else{
			}
		}
		// hier werden die Logeinträge in die Edit und Add aufgeteilt
		foreach($logsArray[$_key] as $___key => $___resultProgress){
			if(!isset($___resultProgress['message']->nid)){
				$weldAll = ($___resultProgress['message']->n0);
				$___resultProgress['message'] = null;
				foreach($weldAll as $____key => $____weldAll){
					foreach($____weldAll as $_____key => $_____weldAll){
						$thisKey = 'n'.(trim($_____key));
						$___resultProgress['message']->$thisKey = trim($_____weldAll);
					}
					$welds['Edit'][trim($weldAll->id)][] = $___resultProgress;
				}
			}
			elseif(isset($___resultProgress['message']->nid) && trim($___resultProgress['message']->nid) != ''){
				$welds['Edit'][trim($___resultProgress['message']->nid)][] = $___resultProgress;
			}
			else {
				$welds['Add'][] = $___resultProgress;
			}
		}

		// Die Daten werden in das neuen Array im Logarray geschrieben
		// wobei auch Vorher und Nachher verglichen wird
		foreach($welds as $_key => $_welds){
			foreach($_welds as $__key => $__welds){
				foreach($__welds as $___key => $___welds){
					$resultProgressMessage = array();
					if($_key  == 'Edit'){
						if($___key == 0){
							foreach($___welds['message'] as $____key => $____welds){
								$resultProgressMessage[] = array(substr($____key, 1) => trim($____welds));
							}
							$logs[$___welds['num']]['Log']['aktion'] = trim($___welds['message']->ndescription).' das erste mal bearbeitet';
						}
						elseif($___key > 0){
							foreach($___welds['message'] as $____key => $____welds){
								foreach($welds['Edit'][$__key][$___key - 1]['message'] as $_____key => $_____welds){
									if($____key == $_____key && trim($____welds) != trim($_____welds)){
										$resultProgressMessage[] = array(substr($____key, 1) => array('old' => trim($_____welds), 'new' => trim($____welds)));
									}
								}
							}
							$logs[$___welds['num']]['Log']['aktion'] = trim($___welds['message']->ndescription).' bearbeitet';
						}
						$logs[$___welds['num']]['Log']['messageformatiert'] = $resultProgressMessage;
						$logs[$___welds['num']]['Log']['keyforXML'] = $ReportEvaluation;
					}
				}
				if($_key  == 'Add'){
					$resultProgressMessage = array();
					foreach($__welds['message'] as $___key => $___welds){
						$resultProgressMessage[] = array(substr($___key, 1) => trim($___welds));
					}
					$logs[$__welds['num']]['Log']['aktion'] = trim($__welds['message']->ndescription) . ' erstellt';
					$logs[$__welds['num']]['Log']['messageformatiert'] = $resultProgressMessage;
					$logs[$__welds['num']]['Log']['keyforXML'] = $ReportEvaluation;
				}
			}
		}
		return $logs;
	}

	public function HistoryReportEditableUp($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $__key => $_resultProgress){
			$resultProgressMessage = array();

			foreach($_resultProgress['message'] as $___key => $___welds){
				$resultProgressMessage[] = array(substr($___key, 1) => trim($___welds));
			}

			$this->_controller->loadModel($ReportEvaluation);
			$discription = $this->_controller->$ReportEvaluation->find('first',array('conditions' => array('id' => trim($logsArray[$_key][$__key]['message']->nid))));

			if(count($discription) == 1){
				$Discription = null;
				$Discription .= $discription[$ReportEvaluation]['description'];
				$Discription .= ' ' . $discription[$ReportEvaluation]['position'];

				if($Verfahren == 'Ht'){
					$Discription .= ' ' . $discription[$ReportEvaluation]['comment'];
				}

				$logs[$logsArray[$_key][$__key]['num']]['Log']['aktion'] = $Discription. ' direkt bearbeitet';
				$logs[$logsArray[$_key][$__key]['num']]['Log']['messageformatiert'] = $resultProgressMessage;
				$logs[$logsArray[$_key][$__key]['num']]['Log']['keyforXML'] = $ReportEvaluation;
			}
		}

		return $logs;
	}

	public function HistoryReportDelete($_key,$logs,$logsArray,$Verfahren) {
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){
			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Gelöscht';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfbericht gelöscht';
		}

		return $logs;
	}

	public function HistoryReportStatus($_key,$logs,$logsArray,$Verfahren) {
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){

			$status = null;

			if(trim($_resultProgress['message']->nReportnumber->status) == ''){
				$status = 'geöffnet';
			}
			if(trim($_resultProgress['message']->nReportnumber->status) == 1){
				$status = 'geschlossen';
			}
			if(trim($_resultProgress['message']->nReportnumber->status) == 2){
				$status = 'abgerechnet';
			}

			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Statusänderung';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $status;
		}

		return $logs;
	}

	public function HistoryReportLoggedin($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_key => $_resultProgress){

			$status = null;

			if(isset($_resultProgress['message']->nprint) && trim($_resultProgress['message']->nprint) == 1){
				$status = 'Prüfbericht gedruckt';
			}
			if(isset($_resultProgress['message']->nprint) && trim($_resultProgress['message']->nprint) == 2){
				$status = 'Rohdatenprotokoll gedruckt';
			}

			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Druckvorgang';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $status;
		}

		return $logs;
	}

	public function HistoryReportDeleteevalution($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_key => $_resultProgress){
			$resultProgressMessage = array();
			foreach($_resultProgress['message'] as $__key => $__message){
				$resultProgressMessage[substr($__key, 1)] = substr($__key, 1) . ': ' . trim($__message);
			}
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $resultProgressMessage;
			$logs[$_resultProgress['num']]['Log']['aktion'] = trim($_resultProgress['message']->ndescription) . ' gelöscht';
			$logs[$_resultProgress['num']]['Log']['keyforXML'] = $ReportEvaluation;
		}

		return $logs;
	}

	public function HistoryReportWeldassistent($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$resultProgressMessage = array();

		$x = 0;
		foreach($logsArray[$_key] as $_resultProgress){
			foreach($_resultProgress['message'] as $_key => $_message){
				foreach($_message as $__key => $__message){
					if($__key != 'reportnumber_id'){
						@$resultProgressMessage[$x] .= $__message.' ';
					}
				}

				// Wenn alle Nähte durch den Assistenten gelöscht wurden
				if(trim($_message) == 'all'){
					$logs[$_resultProgress['num']]['Log']['aktion'] = 'Gelöscht';
					@$resultProgressMessage[$x] = 'Alle Nähte durch Nahtassistenten gelöscht ';
				}
				else {
					$logs[$_resultProgress['num']]['Log']['aktion'] = 'Erstellt durch Nahtassistent';
				}

				$x++;
			}

			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = $resultProgressMessage;
			$logs[$_resultProgress['num']]['Log']['keyforXML'] = $ReportEvaluation;
			$resultProgressMessage = array();
			$x = 0;
		}

		return $logs;
	}

	public function HistoryReportImages($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$resultProgressMessage = array();

		foreach($logsArray[$_key] as $__key => $__logsArray){

			foreach($__logsArray['message'] as $___key => $___message){
				if(substr($___key, 1) != 'user_id'){

					if(substr($___key, 1) == 'name'){
						$RoottumbsPath  = ROOT . DS . 'app' .DS .  'files'. DS . $logs[$__logsArray['num']]['Log']['topproject_id'] . DS . 'images'. DS . $logs[$__logsArray['num']]['Log']['current_id'] . DS . trim($___message);
						if(file_exists($RoottumbsPath)){
							$savetumbsPath  = '/' .  'files'. '/' . $logs[$__logsArray['num']]['Log']['topproject_id'] . '/' . 'images'. '/' . $logs[$__logsArray['num']]['Log']['current_id'] . '/';
							$resultProgressMessage['imgsrc'] = $savetumbsPath.trim($___message);
						}
						else {
							$resultProgressMessage['image'] = 'Das Bild existiert nicht mehr';
						}
					}
				}
			}

			$logs[$__logsArray['num']]['Log']['aktion'] = 'Bild hinzugefügt';
			$logs[$__logsArray['num']]['Log']['messageformatiert'] = $resultProgressMessage;
			$resultProgressMessage = array();
		}

		return $logs;
	}

	public function HistoryReportImagediscription($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$helpArray = array();
		$resultProgressMessage = array();

		// die Einträge werden anhand der ID geordnet
		foreach($logsArray[$_key] as $__key => $__logsArray){
			$helpArray[trim($__logsArray['message']->nid)][] = $__logsArray;
		}

		foreach($helpArray as $_key => $_helpArray){
			foreach($_helpArray as $__key => $__helpArray){

				if(isset($__helpArray['message']->ndiscription)){
					// Beim ersten Eintrag
					if($__key == 0){
						$logs[$__helpArray['num']]['Log']['aktion'] = 'Bildbeschreibung hinzugefügt';
						$logs[$__helpArray['num']]['Log']['messageformatiert'] = trim($__helpArray['message']->ndiscription) . ' (' . trim($__helpArray['message']->nid) .')';
					}
					// Wenn ein Eintrag editiert wurde
					else {
						$logs[$__helpArray['num']]['Log']['aktion'] = 'Bildbeschreibung editiert';
						$logs[$__helpArray['num']]['Log']['messageformatiert'] = array('old' => 'Alter Wert: ' . trim($_helpArray[$__key - 1]['message']->ndiscription), 'new' => 'Neuer Wert: ' . trim($_helpArray[$__key]['message']->ndiscription)  . ' (' . trim($__helpArray['message']->nid) .')');
					}
				}
				if(isset($__helpArray['message']->ndelmessage)){
//					pr(trim($__helpArray['message']->ndelmessage->Reportimage->id));
					$logs[$__helpArray['num']]['Log']['aktion'] = 'Bild gelöscht';
					$logs[$__helpArray['num']]['Log']['messageformatiert'] = trim($__helpArray['message']->ndelmessage->Reportimage->discription) . ' (' . trim($__helpArray['message']->ndelmessage->Reportimage->id) .')';
				}
			}
		}

		return $logs;
	}

	public function HistoryReportFiles($_key,$logs,$logsArray,$Verfahren) {

		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$resultProgressMessage = array();

		foreach($logsArray[$_key] as $__key => $__logsArray){
			foreach($__logsArray['message'] as $___key => $___message){
				if(substr($___key, 1) != 'user_id'){

					if(substr($___key, 1) == 'name'){

						$RoottumbsPath = ROOT . DS . 'app' .DS . 'files' . DS . $logs[$__logsArray['num']]['Log']['topproject_id'] . DS . 'files' . DS . $logs[$__logsArray['num']]['Log']['current_id'] . DS . trim($___message);

						if(file_exists($RoottumbsPath)){
							$resultProgressMessage['file'] = trim($__logsArray['message']->nbasename);
						}
						else {
							$resultProgressMessage['file'] = 'Die Datei ' . trim($__logsArray['message']->nbasename) . ' existiert nicht mehr';
						}
					}
				}
			}

			$logs[$__logsArray['num']]['Log']['aktion'] = 'Datei hinzugefügt';
			$logs[$__logsArray['num']]['Log']['messageformatiert'] = $resultProgressMessage;
			$resultProgressMessage = array();
		}

		return $logs;
	}

	public function HistoryReportDelfile($_key,$logs,$logsArray,$Verfahren) {
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){
			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Gelöscht';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Datei "'.trim($_resultProgress['message']->nReportfile->basename).'" gelöscht';
		}

		return $logs;
	}

	public function HistoryStatus1($_key,$logs,$logsArray,$Verfahren) {
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){
			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Freigabe';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüferfreigabe erteilt';
		}

		return $logs;
	}



	public function HistoryStatus2($_key,$logs,$logsArray,$Verfahren) {
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){

			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = null;

			if($_resultProgress['message']->nReportnumber->status == 0){
				$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfaufsichtfreigabe und Prüferfreigabe zurückgezogen';
			}
			if($_resultProgress['message']->nReportnumber->status == 1){
				$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfaufsichtfreigabe zurückgezogen';
			}
			if($_resultProgress['message']->nReportnumber->status == 2){
				$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Prüfaufsichtfreigabe erteilt';
			}

			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Freigabe';
		}

		return $logs;
	}
	public function HistoryTemplate($_key,$logs,$logsArray,$Verfahren) {
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		foreach($logsArray[$_key] as $_resultProgress){
			$logs[$_resultProgress['num']]['Log']['aktion'] = 'Vorlage';
			$logs[$_resultProgress['num']]['Log']['messageformatiert'] = 'Daten aus Vorlage geladen';
		}

		return $logs;
	}
	public function IpInfo($ip = NULL, $purpose = "location", $deep_detect = TRUE) {

		$output = NULL;

		if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {

			$ip = $_SERVER["REMOTE_ADDR"];

			if ($deep_detect) {
				if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_CLIENT_IP'];
			}
		}

		$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
		$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
		$continents = array(
			"AF" => "Africa",
			"AN" => "Antarctica",
			"AS" => "Asia",
			"EU" => "Europe",
			"OC" => "Australia (Oceania)",
			"NA" => "North America",
			"SA" => "South America"
		);

		if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {

			$output = array();

			$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

			if(@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {

				switch ($purpose) {
                case 'location':
                    $output = array(
                        'city'          => @$ipdat->geoplugin_city,
                        'state'          => @$ipdat->geoplugin_regionName,
                        'country'        => @$ipdat->geoplugin_countryName,
                        'country_code'   => @$ipdat->geoplugin_countryCode,
                        'continent'      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        'continent_code' => @$ipdat->geoplugin_continentCode,
                        'geoplugin_latitude' => @$ipdat->geoplugin_latitude,
                        'geoplugin_longitude' => @$ipdat->geoplugin_longitude,
                        'continent_code' => @$ipdat->geoplugin_continentCode,
                        'continent_code' => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case 'address':
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case 'city':
                    $output = @$ipdat->geoplugin_city;
                    break;
                case 'state':
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case 'region':
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case 'country':
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case 'countrycode':
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
			}
		}
	}

	return $output;

	}

	public function GetClientIp() {

    	$ipaddress = '';

		if (isset($_SERVER['HTTP_CLIENT_IP']))$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    	else if(isset($_SERVER['HTTP_X_FORWARDED']))$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))$ipaddress = $_SERVER['REMOTE_ADDR'];
		else $ipaddress = 'UNKNOWN';

		return $ipaddress;
	}

	public function DecriptIncommingId($id) {

		// Der gelieferte String sollte nur aus alphanumerischen Kram bestehen
		App::uses('Sanitize', 'Utility');
		$id = Sanitize::paranoid($id);

		// Die in Hex umgewandelte ID wird in raw zurückgewandelt
		if (ctype_xdigit($id) && strlen($id) % 2 == 0) {
			$hex_id = hex2bin($id);
		} else {
			return false;
		}

		// Die beim Erstellen des QR-Codes chiffrierte ID wird deshiffriert
		$decrypted_id = intval(Security::rijndael($hex_id, Configure::read('SignatoryHash'), 'decrypt'));

		if(is_int($decrypted_id)){
			return $decrypted_id;
		} else {
			return false;
		}
	}

	public function WriteToLog($log) {

		if(is_object($log)) return false;

		if(is_array($log)){
			$log = print_r($log, true);
			$log = str_replace(array("\n","\t"), " ", $log);
			$log = preg_replace('/\s+/', ' ',$log);
		}

		CakeLog::write('activity', $log);
	}

	public function AfterSuccessLogin(){

		$max_fail_login = 3; // Anzahl ungültiger Loginversuche
		$max_unlogged_time = 7776000; // maximale Zeit zwischen zwei Logins (in diesem Fall 90 Tage)

		if(Configure::check('MaxFailLogin') == true) $max_fail_login = Configure::read('MaxFailLogin');
		if(Configure::check('MaxUnloggedTime')) $max_unlogged_time = Configure::read('MaxUnloggedTime');

		$login_fail = 1;
		$login_counter = $this->_controller->Auth->user('counter_fine');
		$login_counter++;

		$UserLoginData = array();
		$UserLoginData['User']['id'] = $this->_controller->Auth->user('id');
		$UserLoginData['User']['counter_fine'] = $login_counter;
		$UserLoginData['User']['counter_fail'] = $login_fail;
		$UserLoginData['User']['testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');
		$UserLoginData['User']['roll_id'] = $this->_controller->Auth->user('roll_id');
		$UserLoginData['User']['enabled'] = $this->_controller->Auth->user('enabled');
		$UserLoginData['User']['lastlogin'] = time();

		$last_login = $this->Auth->_controller->user('lastlogin');
		$last_login_current = time() - $max_unlogged_time;

		App::import('Vendor', 'clientinfo');

		$ClientInfo = new clientinfo();
		$SystemInfo = array();

		$SystemInfo['browser_clear'] = $ClientInfo->showInfo("browser");
		$SystemInfo['browser_version_clear'] = $ClientInfo->showInfo("version");
		$SystemInfo['os_clear'] = $ClientInfo->showInfo("os");

		$ClientIp = $this->GetClientIp();

//		$UserInfo = array_merge(array('ip' =>$ClientIp),get_browser(null, true),$this->IpInfo($ClientIp,'Location'));
		$AllClientInfo = array_merge($SystemInfo,array());

		$AllClientInfo['user_id'] = $this->_controller->Auth->user('id');
		$AllClientInfo['testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');

		$this->loadModel('UserData');

		$this->_controller->UserData->create();
		$this->_controller->UserData->save($AllClientInfo);

		// Wenn sich der User zu lange nicht eingeloggt hat, wird das Konto deaktiviert
		if($last_login < $last_login_current){
			$this->_controller->Session->setFlash(__('This account has been suspended due to inactivity',true));
			$this->_controller->redirect($this->Auth->logout());
		}

		if($this->_controller->Auth->user('counter_fail') > $max_fail_login){
			$this->_controller->Session->setFlash(__('This account is blocked, to many invalid login attempts',true));
			$this->_controller->redirect($this->Auth->logout());
		}

		if($this->_controller->User->save($UserLoginData)){
			$this->Logger($this->_controller->Auth->user('id'),$this->_controller->Auth->user());
			$this->_controller->redirect(array('controller' => 'users', 'action' => 'redirectafterlogin'));
		} else {
			$this->_controller->redirect($this->_controller->Auth->logout());
		}
	}

	public function CheckExaminerLink($examinerid){
		$current_roll_id = $this->_controller->Auth->user('roll_id');
		$current_user_id = $this->_controller->Auth->user('id');

		if($current_roll_id < 8) {
			$examinerreturn = $examinerid;
			return $examinerreturn;
		} else {
			$this->_controller->loadModel('Examiner');
			$current_examiner = $this->_controller->Examiner->find('first', array('conditions' => array('Examiner.id' => $examinerid,
																																															'Examiner.user_id' => $current_user_id)));
			if(is_countable($current_examiner) && count($current_examiner) < 1) {
				$this->_controller->redirect(array('controller' => 'users', 'action' => 'redirectafterlogin'));
			}
		}

	}

	public function CheckTestingcompsLinks($testingcompid) {
	   if ($this->_controller->Auth->user('roll_id') < 4) {
	       $testingcompidreturn = $testingcompid;
	       return $testingcompidreturn;
	   }else {
			if ($testingcompid == $this->_controller->Auth->user('testingcomp_id')) {
				$testingcompidreturn = $testingcompid;
				return $testingcompidreturn;
			} else {
				$this->_controller->Session->setFlash(__('This is not yours'));
				$this->_controller->redirect(array('controller' => 'users', 'action' => 'redirectafterlogin'));
			}
	   }
	}
}
