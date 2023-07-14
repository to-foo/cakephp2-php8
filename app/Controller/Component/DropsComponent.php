<?php
class DropsComponent extends Component {
	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}
	/**
	* Starts up ExportComponent for use in the controller
	*
	* @param Controller $controller A reference to the instantiating controller object
	* @return void
	*/

	public function CheckMasterAddFields(){

		$Output = array();

		foreach($this->_controller->request->data as $key => $value) {

			if(!is_array($value)) continue;

			foreach ($value as $_key => $_value) {

				if(empty($_value)) $Output['ms-' . $key.$_key] = array('val' => 'required');

			}
		}

		if(count($Output) > 0){

			$this->_controller->set('Errors',json_encode($Output));
			$this->_controller->Flash->error(__('Required fields missing',true), array('key' => 'error'));

		}

		return $Output;
	}

	public function AddMasterDropdownTestingcomp($data){

		$module = $this->_controller->request->data['modul'];

		$this->_controller->loadModel('Testingcomp');
		$this->_controller->Testingcomp->recursive = -1;

		$this->_controller->loadModel('User');
//		$this->_controller->User->recursive = -1;

		foreach ($module as $key => $value) {

			if(!isset($data[$key]['data'])) continue;
			if(count($data[$key]['data']) == 0) continue;

			foreach($data[$key]['data'] as $_key => $_value) {

				$data[$key]['data'][$_key]['data'] = $this->_AddMasterDropdownTestingcomp($data[$key]['data'][$_key]['data']);

			}
		}

		return $data;
	}

	protected function _AddMasterDropdownTestingcomp($data){

		foreach($data as $key => $value){

			$data[$key] = $this->_AddMasterDropdownTestingcompByTestingcomp($data[$key]);
			$data[$key] = $this->_AddMasterDropdownTestingcompByUser($data[$key]);
		}

		return $data;
	}

	protected function _AddMasterDropdownTestingcompByTestingcomp($data){

		if($data['DropdownsMaster']['testingcomp_id'] == 0) return $data;

		$testingcomp = $this->_controller->Testingcomp->find('first',array(
			'conditions' => array(
				'Testingcomp.id' => $data['DropdownsMaster']['testingcomp_id']
				)
			)
		);

		if(count($testingcomp) == 0) return $data;

		$data['Testingcomp'] = $testingcomp['Testingcomp'];

		return $data;
	}

	protected function _AddMasterDropdownTestingcompByUser($data){

		if(isset($data['Testingcomp'])) return $data;

		$user = $this->_controller->User->find('first',array(
			'conditions' => array(
				'User.id' => $data['DropdownsMaster']['user_id']
				)
			)
		);

		if(count($user) == 0) return $data;

		$Insert = array(
			'id' => $data['DropdownsMaster']['id'],
			'testingcomp_id' => $user['Testingcomp']['id'],
		);

		$this->_controller->DropdownsMaster->save($Insert);

		$data['Testingcomp'] = $user['Testingcomp'];

		return $data;
	}

	public function CollectTestingmethods($data) {

		if(count($data) == 0) return $data;

		$output = array();

		$module = $this->_controller->request->data['modul'];

		$this->_controller->loadModel('DropdownsMastersTestingmethod');

		$this->_controller->Testingmethod->recursive = -1;
		$Testingmethods = $this->_controller->Testingmethod->find('list',array(
			'order' => array('verfahren'),
			'fields' => array(
				'id','verfahren')
			)
		);

		foreach ($module as $key => $value) {

			if(!isset($data[$key]['data'])) continue;
			if(count($data[$key]['data']) == 0) continue;

			$IdsDropdownsMaster = Hash::extract($data[$key]['data'], '{n}.DropdownsMaster.id');

			$TestingmethodIds = $this->_controller->DropdownsMastersTestingmethod->find('all',array('conditions' => array('DropdownsMastersTestingmethod.dropdowns_masters_id' => $IdsDropdownsMaster)));

			$IdsTestingmethodIds = Hash::extract($TestingmethodIds, '{n}.DropdownsMastersTestingmethod.testingmethod_id');

			$IdsTestingmethodIds = array_unique($IdsTestingmethodIds);

			$output[$key]['desc'] = $value;
			$output[$key]['data'] = array();

			// Die Ids werden nochmal anhand der Verfahrensnamen geordnet
			$TestingmethodIdsOrdered = $this->_controller->Testingmethod->find('list',array(
				'conditions' => array('Testingmethod.id' => $IdsTestingmethodIds),
				'order' => array('verfahren'),
				'fields' => array(
					'id','id')
				)
			);

			$IdsTestingmethodIds = $TestingmethodIdsOrdered;

			foreach ($IdsTestingmethodIds as $_key => $_value) {

				$TestingmethodIds = $this->_controller->DropdownsMastersTestingmethod->find('all',array(
					'conditions' => array(
							'DropdownsMastersTestingmethod.testingmethod_id' => $_value
						)
					)
				);

				$DropdownmasterIds = Hash::extract($TestingmethodIds, '{n}.DropdownsMastersTestingmethod.dropdowns_masters_id');

				$conditions = array(
					'conditions' => array(
						'DropdownsMaster.modul' => $key,
						'DropdownsMaster.id' => $DropdownmasterIds,
						'DropdownsMaster.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
						'DropdownsMaster.deleted' => 0,

					),
					'order' => array('name asc')
				);

				$DropdownsMaster = $this->_controller->DropdownsMaster->find('all',$conditions);

				if(count($DropdownsMaster) == 0) continue;

				if(isset($Testingmethods[$_value])) {
					$output[$key]['data'][$_value] = array(
						'desc' => $Testingmethods[$_value],
						'data' => $DropdownsMaster,
					);
				}

			}
		}

		return $output;
	}

	// hier wird die Datenhashfunktion für die Dropdownfelder in eine Funktion gepackt
	public function DropdownData($ReportArray,$arrayData,$reportnumbers) {

	$Verfahren = null;
	if(isset($reportnumbers['Testingmethod']['value']))$Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);

	$this->_controller->loadModel('DropdownsValue');
	$this->_controller->loadModel('Dropdown');
	$this->_controller->DropdownsValue->recursive = -1;
	$this->_controller->Dropdown->recursive = -1;

	if($this->_controller->Auth->user('Roll.id') == 1){
		$this->_controller->loadModel('Testingcomp');
		$this->_controller->Testingcomp->recursive = -1;
		$ProjectMember = $this->_controller->Autorisierung->ProjectMember($this->_controller->request->projectvars['VarsArray'][0]);
		$testingcomps = $this->_controller->Testingcomp->find('all',array('conditions' => array('Testingcomp.id' => $ProjectMember,'Testingcomp.roll_id >' => 4)));
	}

	$reportnumbers['CustomDropdownFields'] = array();

	foreach($ReportArray as $_ReportArray){

		$TestingCompOptionDropdownsValue = null;
		$GlobalOptionDropdownsValue = null;
		$TestingCompOptionDropdowns = null;

		if(AuthComponent::user('Testingcomp.roll_id') > 4){
			$TestingCompOptionDropdownsValue['DropdownsValue.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
			$GlobalOptionDropdownsValue['DropdownsValue.global'] = 1;
			$TestingCompOptionDropdowns['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
		} else {
			$TestingCompOptionDropdownsValue = null;
			$GlobalOptionDropdownsValue = null;
//			$TestingCompOptionDropdowns = null;
			$TestingCompOptionDropdowns['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
		}

		foreach($arrayData['settings']->$_ReportArray as $settings){

			$dropdownArrayHelper = array();

			// das brauchen wir um das Array mit den Bewertungen rauszufiltern
			if($Verfahren != null)$StopForeachArray = explode($Verfahren,$_ReportArray);

				foreach($settings as $_settings){

					if(isset($_settings->fieldtype) && trim($_settings->fieldtype) == 'checkbox') {
						$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = array('', trim($_settings->discription->{trim($this->_controller->Lang->Discription())}));
						$reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)]);
					}
					if(isset($_settings->fieldtype) && trim($_settings->fieldtype) == 'radio')
					{
						$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $this->_controller->radiodefault;
						if(isset($_settings->radiooption->value)) {
							$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = array();
							foreach($_settings->radiooption->value as $val) {array_push($reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)], $val); }
						}
						$reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)]);
					}
					elseif($_settings->select->model != ''){

						if(array_search(strtolower(trim($_settings->select->custom_field)), array('1','yes')) !== false){
							if($_settings->xpath('select/custom_field_roll/value[contains(text(), "'.$this->_controller->Session->read('Auth.User.Roll.id').'")]')) {
								$reportnumbers['CustomDropdownFields'][] = $_ReportArray.'0'.ucfirst(Inflector::camelize($_settings->key));
							}
						}

						if(count($values = $_settings->xpath('select/static/value'))) {
							$values = array_map('trim', $values);
							$values[$reportnumbers[trim($_ReportArray)][trim($_settings->key)]] = $reportnumbers[trim($_ReportArray)][trim($_settings->key)];

							$dropdownArrayHelper = array_unique(array_combine($values, $values));

 							$reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($dropdownArrayHelper);
 							$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $dropdownArrayHelper;
							continue;
						}

						// die ID des Dropdowns aus der Dropdown-Tabelle holen
						// Wichtig ist die Suchreihenfolge, da immer der aktuellste


						// Falls ein Bentuzer Zugriff auf mehrere Testingcomps hat, könnte die falsche ausgewählt werden
						// Daher nur Testingcomp nehmen, die auch in der Datenbank beim Benutzer steht
						$dropdownCondition = trim($_settings->select->condition);
						$option = array(
								'conditions' => array(
									// Testingcomp weglassen sonst können Benutzer ohne Recht, ein Dropdown anzulegen keine Werte sehen
									//'Dropdown.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
									'Dropdown.report_id' => $this->_controller->request->reportID,
									'Dropdown.model LIKE ' => trim($_settings->model),
									'Dropdown.field LIKE ' => trim($_settings->key),
									$TestingCompOptionDropdowns
									),
								'fields' => array($this->_controller->Dropdown->primaryKey, 'linking'),
								'order' => array('Dropdown.id DESC')
						);
						$dropdown = $this->_controller->Dropdown->find('first',$option);

						// Wenn ein Dropdownfeld nur von einem Hauptadmin befüllt werden kann
						// und ein niedrigerer Nutzer noch keinen Dropdowneintrag für das Feld hat
						// wird der Eintrag hier erstellt
						if(count($dropdown) == 0 && !empty($_settings->select->roll->edit->value)){

							$addDropdownOption['dropdownid'] = trim($_settings->model) . '0' . trim($_settings->key);
							$addDropdownOption['model'] = trim($_settings->model);
							$addDropdownOption['field'] = trim($_settings->key);
							$addDropdownOption['linking'] = 0;
							$addDropdownOption['eng'] = trim($_settings->discription->eng);
							$addDropdownOption['deu'] = trim($_settings->discription->deu);
							$addDropdownOption['testingcomp_id'] = AuthComponent::user('Testingcomp.id');
							$addDropdownOption['report_id'] = $this->_controller->request->projectvars['reportID'];
							$addDropdownOption['testingmethod_id'] = $reportnumbers['Testingmethod']['id'];

							$this->_controller->Dropdown->create();
							$this->_controller->Dropdown->save($addDropdownOption);
							$dropdown = $this->_controller->Dropdown->find('first',array('conditions' => array('Dropdown.id' => $this->_controller->Dropdown->getInsertID())));
						}

						// wenn ein Eintrag exisitiert
						if(count($dropdown) > 0){

							$options = array(
								'conditions' => array(
											'DropdownsValue.dropdown_id' => $dropdown['Dropdown']['id'],//['Dropdown']['id'],
											'DropdownsValue.report_id' => $this->_controller->request->reportID,
										),
								'order' => array('DropdownsValue.discription ASC')
							);

							$dropdowns = $this->_controller->DropdownsValue->find('all',$options);

							if(count($dropdowns) > 0){
								$reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)] = $dropdowns;
							} elseif(count($dropdowns) == 0) {
								$reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)][0] = array('DropdownsValue' => array('dropdown_id' => $dropdown['Dropdown']['id']));
							}

							// Die Dropdownwerte für das Selectfeld nutzbar machen
							$dropdownArrayHelper = array();
							foreach($dropdowns as $_dropdowns){
								$dropdownArrayHelper[$_dropdowns['DropdownsValue']['id']] = $_dropdowns['DropdownsValue']['discription'];
							}

							// Test ob der gespeicherte Wert bereits in der Dropdownlist enthalten ist
							$AddThis = 0;
							foreach($dropdownArrayHelper as $_dropdownArrayHelper){

								if(!isset($StopForeachArray)) continue;

								if($StopForeachArray[1] != 'Evaluation' && isset($reportnumbers[$_ReportArray][trim($_settings->key)])){
//									pr($reportnumbers[$_ReportArray][0][trim($_settings->key)]);

									if(trim($_dropdownArrayHelper) == trim($reportnumbers[$_ReportArray][trim($_settings->key)])){
										break;
										$AddThis = 1;
									}

									// Wenn der Wert noch nicht vorhanden ist wird er hier hinzugefügt
									$multiselect = isset($_settings->multiselect);
									if($AddThis == 0 && !$multiselect && trim($reportnumbers[$_ReportArray][trim($_settings->key)]) != ''){
										$dropdownArrayHelper[trim($reportnumbers[$_ReportArray][trim($_settings->key)])] = trim($reportnumbers[$_ReportArray][trim($_settings->key)]);
									}
								}

								if($StopForeachArray[1] == 'Evaluation' && isset($reportnumbers[$_ReportArray]) && !empty($reportnumbers[$_ReportArray])){
									foreach($reportnumbers[$_ReportArray] as $_key => $_evalutions){
										if(isset($reportnumbers[$_ReportArray][trim($_settings->key)])){
											$dropdownArrayHelper[$reportnumbers[$_ReportArray][trim($_settings->key)]] = $reportnumbers[$_ReportArray][trim($_settings->key)];
										}
									}
								}
							}

							$AddThis = 0;
							if(isset($dropdownArrayHelper)) $dropdownArrayHelper = array_unique($dropdownArrayHelper); // Doppelte Werte herausfiltern und numerische Indices bevorzugen
							elseif(!isset($dropdownArrayHelper)) $dropdownArrayHelper = array();

 							$reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($dropdownArrayHelper);

 							$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $dropdownArrayHelper;


						} else {
// 							$reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = array();
// 							$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = array();
//							$reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)] = array();
						}
 					}
				}

				unset($dropdownArrayHelper);
			}
		}
//		pr($reportnumbers['Dropdowns']['ReportRtSpecific']['radiation_source']);

		// Die Globale  Werte hinzufügen
		if(isset($reportnumbers['Dropdowns']) && count($reportnumbers['Dropdowns']) > 0){

			foreach($reportnumbers['Dropdowns'] as $_key => $_dropdowns){

				foreach($_dropdowns as $__key => $__dropdowns){
					$options = array(
						'conditions' => array(
							'DropdownsValue.model' => $_key,
							'DropdownsValue.field' => $__key,
							'DropdownsValue.report_id' => $this->_controller->request->reportID,
							$GlobalOptionDropdownsValue,
//							$TestingCompOptionDropdownsValue,
							)
						);

					$dropdowns = $this->_controller->DropdownsValue->find('all',$options);

					if(is_array($dropdowns)){

						// Wenn der Schlüssel nicht vorhanden ist
						if(!isset($reportnumbers['DropdownInfo'][$_key][$__key])) continue;

						// Wenn der Schlüssel vorhanden ist, aber keine Einträge vorhanden sind
						if(!isset($reportnumbers['DropdownInfo'][$_key][$__key][0]['DropdownsValue']['id'])) unset($reportnumbers['DropdownInfo'][$_key][$__key][0]);

						if(isset($reportnumbers['DropdownInfo'][$_key][$__key])){
							$reportnumbers['DropdownInfo'][$_key][$__key] = array_merge($reportnumbers['DropdownInfo'][$_key][$__key],$dropdowns);
						}
//						pr($dropdowns);

						foreach($dropdowns as $___dropdowns){
							$reportnumbers['Dropdowns'][$_key][$__key][$___dropdowns['DropdownsValue']['id']] = $___dropdowns['DropdownsValue']['discription'];
							$dropdownArrayHelper[$___dropdowns['DropdownsValue']['id']] = $___dropdowns['DropdownsValue']['discription'];

						}

						if(!isset($dropdownArrayHelper) || !is_array($dropdownArrayHelper) || $dropdownArrayHelper == null) $dropdownArrayHelper = array();

						$dropdownArrayHelper = array_unique($dropdownArrayHelper);
						ksort($dropdownArrayHelper);

						$reportnumbers['Dropdowns'][$_key][$__key] = $dropdownArrayHelper;
						$reportnumbers['JSON'][$_key][$__key] = json_encode($dropdownArrayHelper);

						$dropdownArrayHelper = array();

					} else {
						$reportnumbers['DropdownInfo'][$_key][$__key][0]['DropdownsValue']['dropdown_id'] = 'XXX';
					}
				}
			}
		}

		$reportnumbers = $this->_controller->Data->SortAllDropdownArraysNatural($reportnumbers);

		return $reportnumbers;
	}

	public function CollectDropdownMaster($typ) {

		$locale = $this->_controller->Lang->Discription();
		$id = $this->_controller->request->projectvars['VarsArray'][15];
		$DataId = $this->_controller->request->projectvars['VarsArray'][16];
		$ValueId = $this->_controller->request->projectvars['VarsArray'][17];

		$this->_controller->loadModel('DropdownsMaster');
		$this->_controller->loadModel('DropdownsMastersDependency');
		$this->_controller->loadModel('DropdownsMastersDependenciesField');
		$this->_controller->loadModel('Testingmethods');
		$this->_controller->loadModel('Testingcomp');
		$this->_controller->loadModel('Report');
		$this->_controller->loadModel('Topproject');

		$options = array('conditions' => array('DropdownsMaster.'.$this->_controller->DropdownsMaster->primaryKey => $id, 'DropdownsMaster.deleted = ' => 0));
		$DropdownsMaster = $this->_controller->DropdownsMaster->find('first', $options);
		unset($DropdownsMaster['DropdownsMastersData']);

		$options = array('conditions' => array('DropdownsMastersData.id' => $DataId));
		$DropdownsMastersData = $this->_controller->DropdownsMaster->DropdownsMastersData->find('first', $options);
		$DropdownsMaster['DropdownsMastersData'] = $DropdownsMastersData['DropdownsMastersData'];

		$options = array('order' => array('DropdownsMastersData.value ASC'),'fields' => array('value'),'conditions' => array('DropdownsMastersData.dropdowns_masters_id' => $DropdownsMaster['DropdownsMaster']['id']));
		$DropdownsMastersDatas = $this->_controller->DropdownsMaster->DropdownsMastersData->find('list', $options);
		$DropdownsMaster['DropdownsMastersDatas'] = $DropdownsMastersDatas;

		$parms = $this->_controller->request->projectvars['VarsArray'];

		$DropdownsMaster['DropdownsMastersDatasUrl'] = array();

		foreach ($DropdownsMastersDatas as $key => $value) {

			$parms[16] = $key;
			$url = Router::url(
				array_merge(
					array(
						'controller' => $this->_controller->request->params['controller'],
						'action' => $this->_controller->request->params['action'],
					),
					$parms
				)
			);
			$new_url = array($url =>$value);
			$DropdownsMaster['DropdownsMastersDatasUrl'] = array_merge($DropdownsMaster['DropdownsMastersDatasUrl'],$new_url);
		}

		if($ValueId > 0) return $DropdownsMaster;

		$DropdownsMaster = $this->_controller->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Testingcomp', array('DropdownsMastersTestingcomp','testingcomp_id','dropdowns_masters_id'));
		$DropdownsMaster = $this->_controller->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Report', array('DropdownsMastersReport','report_id','dropdowns_masters_id'));
		$DropdownsMaster = $this->_controller->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Topproject', array('DropdownsMastersTopproject','topproject_id','dropdowns_masters_id'));
		$DropdownsMaster = $this->_controller->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Testingmethod', array('DropdownsMastersTestingmethod','testingmethod_id','dropdowns_masters_id'));

		$DropdownsMaster = $this->CollectFieldsForMaster($DropdownsMaster);

		$testingmethods = $this->_controller->Testingmethods->find('list',array('fields' => array('id','verfahren'),'order' => array('verfahren')));
		$testingcomps = $this->_controller->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$reports = $this->_controller->Report->find('list',array('fields' => array('id','name'),'order' => array('name')));
		$topprojects = $this->_controller->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

		$DropdownsMaster['DependencyFields'] = $DropdownsMaster['DropdownsFields'];
		unset($DropdownsMaster['DependencyFields'][$DropdownsMaster['DropdownsMaster']['field']]);
		$dependencies = $DropdownsMaster['DependencyFields'];

		$DropdownsMaster = $this->CollectDependenciesField($DropdownsMaster);
		$DropdownsMaster = $this->AddDescriptionOfField($DropdownsMaster);
		$DropdownsMaster = $this->SeparateDependenciesField($DropdownsMaster);


		if($typ == 'html') $this->_controller->set(compact('testingcomps', 'reports','topprojects','testingmethods','dependencies'));

		return $DropdownsMaster;

	}

	public function CreateNewDropdownEntry($xml,$reportnumber) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
		$evalutionID = $this->_controller->request->projectvars['VarsArray'][5];
		$id = $this->_controller->request->projectvars['VarsArray'][6];
		$count = $this->_controller->request->projectvars['VarsArray'][7];

		if($id > 0) return $id;

		$Editmenue = $this->_controller->Session->read('editmenue');

		if(empty($Editmenue)) return $id;

		$EditmenueName = $Editmenue[$reportnumber['Reportnumber']['id']];

		if($EditmenueName == 'Specify') $EditmenueName = 'Specific';
		if($EditmenueName == 'General') $EditmenueName = 'Generally';
		if($EditmenueName == 'TestingArea') $EditmenueName = 'Evaluation';

		if(!isset($Editmenue[$reportnumber['Reportnumber']['id']])) return $id;

		$Verfahren = ucfirst($reportnumber['Testingmethod']['value']);

		$Model = 'Report' . $Verfahren . $EditmenueName;

		$x = 0;

		foreach ($xml['settings']->$Model->children() as $key => $value) {
			if(empty($value->output->screen)) continue;

			$x++;

			if($count == $x) {

				$field = trim($value->key);
				$Field = Inflector::camelize($field);

				$Option['Dropdown.dropdownid'] = $Model . '0' . $Field;
				$Option['Dropdown.model'] = $Model;
				$Option['Dropdown.field'] = $field;
				$Option['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
				$Option['Dropdown.report_id'] = $reportnumber['Report']['id'];
				$Option['Dropdown.testingmethod_id'] = $reportnumber['Testingmethod']['id'];

				$Insert['dropdownid'] = $Model . '0' . $Field;
				$Insert['model'] = $Model;
				$Insert['field'] = $field;
				$Insert['linking'] = 0;
				$Insert['testingcomp_id'] = AuthComponent::user('Testingcomp.id');
				$Insert['report_id'] = $reportnumber['Report']['id'];
				$Insert['testingmethod_id'] = $reportnumber['Testingmethod']['id'];

				foreach($value->discription->children() as $_key => $_value) $Insert[$_key] = trim($_value);

				$Dropdown = $this->_controller->Dropdown->find('first',array('conditions' => $Option));

				if(count($Dropdown) > 0) return $Dropdown['Dropdown']['id'];

				$this->_controller->Dropdown->create();
				$this->_controller->Dropdown->save($Insert);
				$Dropdownid = $this->_controller->Dropdown->getInsertID();
				break;
			}
		}
		return $Dropdownid;
	}

	public function CheckAuthorization($data) {

		if(!isset($data['Reportnumber'])) return false;
		if(!is_array($data['Reportnumber'])) return false;
		if(count($data['Reportnumber']) == 0) return false;

		if(ClassRegistry::isKeySet('DropdownsMaster') === false) $this->_controller->loadModel('DropdownsMaster');
		if(ClassRegistry::isKeySet('DropdownsMastersDependenciesField') === false) $this->_controller->loadModel('DropdownsMastersDependenciesField');

		$output = array();

		$AuthArray = array(
			'topproject_id' => 'Topproject',
			'report_id' => 'Report',
			'testingmethod_id' => 'Testingmethod',
			'testingcomp_id' => 'Testingcomp',
		);

		$output = array(
			'topproject_id' => array(),
			'report_id' => array(),
			'testingmethod_id' => array(),
			'testingcomp_id' => array(),
		);

		foreach ($AuthArray as $key => $value) {

			$Model = 'DropdownsMasters' . $value;
			$field = $key;

			if($value == 'Testingcomp') continue;


			$DropdownsAuth = $this->_controller->DropdownsMaster->{$Model}->find('all',array(
				'fields' => array('dropdowns_masters_id'),
				'order' => array('dropdowns_masters_id'),
				'conditions' => array($field => $data['Reportnumber'][$field])
				)
			);

			if(count($DropdownsAuth) == 0) return false;

			$DropdownsAuth = Hash::extract($DropdownsAuth, '{n}.' . $Model . '.dropdowns_masters_id');

			$output[$key] = $DropdownsAuth;

		}

		$Model = 'DropdownsMastersTestingcomp';
		$field = 'testingcomp_id';

		$DropdownsAuth = $this->_controller->DropdownsMaster->{$Model}->find('all',array(
			'fields' => array('dropdowns_masters_id'),
			'order' => array('dropdowns_masters_id'),
			'conditions' => array($field => $this->_controller->Auth->user('Testingcomp.id'))
			)
		);

		$DropdownsAuth = Hash::extract($DropdownsAuth, '{n}.' . $Model . '.dropdowns_masters_id');

		$output['testingcomp_id'] = $DropdownsAuth;

		$output = call_user_func_array('array_intersect', array_values($output));

		if(count($output) == 0) return false;

		return $output;

	}


	public function CheckAuthorizationModul($data) {

		if(!isset($data)) return false;
		if(!is_array($data)) return false;
		if(count($data) == 0) return false;

		if(ClassRegistry::isKeySet('DropdownsMaster') === false) $this->_controller->loadModel('DropdownsMaster');
		if(ClassRegistry::isKeySet('DropdownsMastersDependenciesField') === false) $this->_controller->loadModel('DropdownsMastersDependenciesField');

		$output = array();

		$AuthArray = array(

			'testingcomp_id' => 'Testingcomp',
		);

		$output = array(

			'testingcomp_id' => array(),
		);
		foreach ($AuthArray as $key => $value) {

			$Model = 'DropdownsMasters' . $value;
			$field = $key;
			$fielddata = Hash::extract($data, '{s}.'.$field);

			$DropdownsAuth = $this->_controller->DropdownsMaster->{$Model}->find('list',array('fields' => array('dropdowns_masters_id'),'conditions' => array($field => $fielddata[0])));

			if(count($DropdownsAuth) == 0) return false;

			$output = $DropdownsAuth;

		}

		//$output = call_user_func_array('array_intersect', $output);

		if(count($output) == 0) return false;
		return $output;

	}
	public function RegistryProjekt(){

		if(!isset($this->_controller->request->data['Topproject']['Topproject'])) return;
		if(empty($this->_controller->request->data['Topproject']['Topproject'])) return;
		if(count($this->_controller->request->data['Topproject']['Topproject']) == 0) return;

		$options = array('conditions' => array('Topproject.id' => $this->_controller->request->data['Topproject']['Topproject']));

		$Topprojects = $this->_controller->Topproject->find('list',$options);

		if(count($Topprojects) == 0) return;

		$this->_controller->Topproject->updateAll(
    	array('Topproject.add_master_dropdowns' => 1),
    	array('Topproject.id' => $Topprojects)
		);

	}

	public function CollectProjectMasterDropdowns($Data){

		if(Configure::check('DropdownsManager') == false) return $Data;
		if(Configure::read('DropdownsManager') == false) return $Data;

		if(ClassRegistry::isKeySet('DropdownsMaster') === false) $this->_controller->loadModel('DropdownsMaster');
		if(ClassRegistry::isKeySet('DropdownsMastersTopproject') === false) $this->_controller->loadModel('DropdownsMastersTopproject');

		if($Data['Topproject']['add_master_dropdowns'] == 0) return $Data;
		$Id = $Data['Topproject']['id'];

		$DropdownsMastersTopproject = $this->_controller->DropdownsMastersTopproject->find('all',array('conditions' => array('DropdownsMastersTopproject.topproject_id' => $Id)));

		$IDs = Hash::extract($DropdownsMastersTopproject, '{n}.DropdownsMastersTopproject.dropdowns_masters_id');

		$this->_controller->DropdownsMaster->recursive = -1;
		$dropdownsmaster = $this->_controller->DropdownsMaster->find('list',array('fields' => array('name','modul','id'),'conditions' => array('DropdownsMaster.status' => 0,'DropdownsMaster.deleted' => 0)));

		if(count($dropdownsmaster) == 0) return $Data;

		$dropdownsmasters = array();

		foreach ($dropdownsmaster as $key => $value) $dropdownsmasters[$key] = key($value) . ' (' . __($value[key($value)],true) . ')';

		$DropdownsMasterSelectedFields = $this->_controller->DropdownsMaster->find('list',array('fields' => array('id','id'),'conditions' => array('DropdownsMaster.id' => $IDs,'DropdownsMaster.status' => 0,'DropdownsMaster.deleted' => 0)));

		$Data['Dropdownsmaster']['selected'] = array();
		if(count($DropdownsMasterSelectedFields ) > 0)	$Data['Dropdownsmaster']['selected'] = array_merge($Data['Dropdownsmaster']['selected'],$DropdownsMasterSelectedFields);

		$this->_controller->set(compact('dropdownsmasters'));

		return $Data;

	}

	public function UpdateMasterDropdownsProject(){

		if(!isset($this->_controller->request->data['Topproject']['Dropdownsmaster'])) return;

		if(ClassRegistry::isKeySet('DropdownsMaster') === false) $this->_controller->loadModel('DropdownsMaster');
		if(ClassRegistry::isKeySet('DropdownsMastersTopproject') === false) $this->_controller->loadModel('DropdownsMastersTopproject');

		$Id = $this->_controller->request->data['Topproject']['id'];

		$this->_controller->DropdownsMastersTopproject->deleteAll(array('DropdownsMastersTopproject.topproject_id' => $Id), false);

		if(empty($this->_controller->request->data['Topproject']['Dropdownsmaster'])) return true;
		if(count($this->_controller->request->data['Topproject']['Dropdownsmaster']) == 0) return true;

		foreach ($this->_controller->request->data['Topproject']['Dropdownsmaster'] as $key => $value) {

			$Save = array('topproject_id' => $Id, 'dropdowns_masters_id' => $value);
			$this->_controller->DropdownsMastersTopproject->create();
			$this->_controller->DropdownsMastersTopproject->save($Save);

		}
	}

	public function MasterDropdownsProject($Id){

		if(Configure::check('DropdownsManager') == false) return true;
		if(Configure::read('DropdownsManager') == false) return true;

		if($Id == 0) return true;
		if(!isset($this->_controller->request->data['Topproject']['add_master_dropdowns'])) return true;

		if($this->_controller->request->data['Topproject']['add_master_dropdowns'] == 1) $this->__AddMasterDropdownsNewProject($Id);
		if($this->_controller->request->data['Topproject']['add_master_dropdowns'] == 0) $this->__RemoveMasterDropdownsNewProject($Id);

		return true;

	}

	protected function __AddMasterDropdownsNewProject($Id){

		if(Configure::check('DropdownsManager') == false) return true;
		if(Configure::read('DropdownsManager') == false) return true;

		if($Id == 0) return true;

		$this->_controller->loadModel('DropdownsMastersTopproject');
		$this->_controller->loadModel('DropdownsMaster');

		$DropdownsMastersTopproject = $this->_controller->DropdownsMastersTopproject->find('all',array('conditions' => array('DropdownsMastersTopproject.topproject_id' => $Id)));

		if(count($DropdownsMastersTopproject) > 0) return true;

		$DropdownsMaster = $this->_controller->DropdownsMaster->find('list', array('fields' => array('id','id')),
																																			   array('conditions' => array('DropdownsMaster.deleted =' => 0)));

		if(count($DropdownsMaster) == 0) return true;

		foreach ($DropdownsMaster as $key => $value) {

			$Save = array('topproject_id' => $Id, 'dropdowns_masters_id' => $value);
			$this->_controller->DropdownsMastersTopproject->create();
			$this->_controller->DropdownsMastersTopproject->save($Save);

		}

		return true;

	}

	protected function __RemoveMasterDropdownsNewProject($Id){

		if(Configure::check('DropdownsManager') == false) return true;
		if(Configure::read('DropdownsManager') == false) return true;

		if($Id == 0) return true;

		$this->_controller->loadModel('DropdownsMastersTopproject');
		$this->_controller->DropdownsMastersTopproject->deleteAll(array('DropdownsMastersTopproject.topproject_id' => $Id), false);

		return true;

	}

	public function CollectContentDropdown($data,$table_index){

		$Model = $this->_controller->request->tablenames[$table_index];

		if(!isset($data[$Model]['reportnumber_id'])) return $data;

		$ReportnumberId = $data[$Model]['reportnumber_id'];

		foreach ($data['xml']['settings']->{$Model}->children() as $key => $value) {

			if(empty($value->select->model)) continue;
			if(empty($value->dependencies)) continue;
			if(empty($value->dependencies->parent)) continue;

			if(count($value->dependencies->parent) == 0) continue;

			$Field = trim($value->key);

			foreach ($value->dependencies->parent as $_key => $_value) {

				$Val = trim($_value);
				$ValArray = explode('.',$Val);

				$ValModel = $ValArray[0];
				$ValField = $ValArray[1];

				if(ClassRegistry::isKeySet($ValModel) === false) $this->_controller->loadModel($ValModel);

				$results = $this->_controller->{$ValModel}->findByReportnumberId($ReportnumberId);

				if(empty($results[$ValModel][$ValField])) continue;

				$data['Dropdowns'][$Model][$Field][$results[$ValModel][$ValField]] = $results[$ValModel][$ValField];

			}
		}

		return $data;

	}

	public function CollectRadioValues($data){

		if(!isset($data['xml']['settings'])) return $data;

		$Model = $data['Tablenames']['Evaluation'];

		foreach($data['xml']['settings']->{$Model}->children() as $value){

				if(empty($value->fieldtype)) continue;
				if(trim($value->fieldtype) != 'radio') continue;

				if(empty($value->radiooption)) {

				} else {

				}
		}

		return $data;
	}

	public function CollectMasterDropdown($data){

		if(Configure::check('DropdownsManager') == false) return $data;
		if(Configure::read('DropdownsManager') == false) return $data;

		$this->_controller->loadModel('DropdownsMaster');
		$this->_controller->loadModel('DropdownsMastersDependency');
		$this->_controller->loadModel('DropdownsMastersDependenciesField');

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

		if(isset($data['Reportnumber'])) { // wenn im Prüfberichtsmodul gearbeitet wird
			
				$MasterIds = $this->CheckAuthorization($data);

				if($MasterIds === false) return $data;

				$Verfahren = $this->_controller->request->Verfahren;

				$this->_controller->DropdownsMaster->recursive = -1;

				$DropdownsMasterFields = $this->_controller->DropdownsMaster->find('all',array(
					'fields' => array('field','modul','id'),
					'conditions' => array(
						'DropdownsMaster.id' => $MasterIds,
						'DropdownsMaster.status' => 0,
						'DropdownsMaster.deleted' => 0
						)
					)
				);

				foreach ($DropdownsMasterFields as $key => $value) {

					$Modul = $value['DropdownsMaster']['modul'];
					$ModelArea = explode('_',$value['DropdownsMaster']['modul']);
					$Model = Inflector::camelize($ModelArea[0]);
					$field = $value['DropdownsMaster']['field'];
					$Model = 'Report' . $Verfahren . $Model;

					$MasterParms = array();
					for ($i = 0; $i <= 17; $i++) array_push($MasterParms,0);

					//Wenn Verlnüpfte Daten aus der Gerätedatenbank vorhanden sind ////////////////////////
					$deviceids = $this->GetDeviceDropdownsMasters($value['DropdownsMaster']['id']);
					if (!empty($deviceids)) {
						$MasterParms[15] = $value['DropdownsMaster']['id'];
						$DropdownsMastersData = $this->DeviceDataForMaster($deviceids);
					} else{
						//////////////////////////////////////////////////////////////////////////////////////
						$options = array(
							'conditions' => array(
								'DropdownsMaster.id' => $MasterIds,
								'DropdownsMaster.modul' => $Modul,
								'DropdownsMaster.field' => $field,
								'DropdownsMaster.deleted = ' => 0
							),
							'fields' => array('id')
						);

						$DropdownsMasters = $this->_controller->DropdownsMaster->find('list',$options);

						$DropdownsMastersData = $this->_controller->DropdownsMaster->DropdownsMastersData->find('list',array(
							'order' => array('value ASC'),
							'fields' => array('value'),
							'conditions' => array('DropdownsMastersData.status' => 0,'DropdownsMastersData.dropdowns_masters_id' => $DropdownsMasters)
							)
						);

						$MasterParms = $this->_controller->request->projectvars['VarsArray'];

						$MasterParms[15] = key($DropdownsMasters);
					}

					unset($data['Dropdowns'][$Model][$field]);
					unset($data['DropdownInfo'][$Model][$field]);
					unset($data['JSON'][$Model][$field]);

					$data['Dropdowns'][$Model][$field] = $DropdownsMastersData;
					$data['JSON'][$Model][$field] = json_encode($DropdownsMastersData);
					$data['MasterDropdowns'][$Model][$field] = $DropdownsMasters;
					$data['MasterDropdownsUrl'][$Model][$field]['controller'] = 'dropdowns';
					$data['MasterDropdownsUrl'][$Model][$field]['action'] = 'modaladddata';
					$data['MasterDropdownsUrl'][$Model][$field]['parm'] = $MasterParms;
					$data['MasterJSON'][$Model][$field] = json_encode($DropdownsMasters);

					$HasDependencies = $this->CheckMasterDropdownDependencies($value);

					$data['MasterDropdownsDependency'][$Model][$field] = $HasDependencies;
			}

		} else {// alle anderen Module
			$MasterIds = $this->CheckAuthorizationModul($data);
			if($MasterIds === false) return $data;

			$this->_controller->DropdownsMaster->recursive = -1;
			$DropdownsMasterFields = $this->_controller->DropdownsMaster->find('all',array('fields' => array('field','modul','id'),'conditions' => array('DropdownsMaster.id' => $MasterIds,'DropdownsMaster.status' => 0,'DropdownsMaster.deleted' => 0)));

			foreach ($DropdownsMasterFields as $key => $value) {

				$Modul = $value['DropdownsMaster']['modul'];
				$ModelArea = explode('_',$value['DropdownsMaster']['modul']);
				$Model = Inflector::camelize($ModelArea[0]);
				$field = $value['DropdownsMaster']['field'];
				$Model = ucfirst($Model);

				$options = array(
					'conditions' => array(
						'DropdownsMaster.id' => $MasterIds,
						'DropdownsMaster.modul' => $Modul,
						'DropdownsMaster.field' => $field,
						'DropdownsMaster.deleted = ' => 0
					),
					'fields' => array('id')
				);

				$DropdownsMasters = $this->_controller->DropdownsMaster->find('list',$options);
				$DropdownsMastersData = $this->_controller->DropdownsMaster->DropdownsMastersData->find('list',array(
					'order' => array('value ASC'),
					'fields' => array('value'),
					'conditions' => array('DropdownsMastersData.status' => 0,'DropdownsMastersData.dropdowns_masters_id' => $DropdownsMasters)
					)
				);

				$MasterParms = array();
				for ($i = 0; $i <= 17; $i++) array_push($MasterParms,0);
				$MasterParms[15] = key($DropdownsMasters);


				if(count($DropdownsMastersData) == 0) continue;

				unset($data['Dropdowns'][$Model][$field]);
				unset($data['DropdownInfo'][$Model][$field]);
				unset($data['JSON'][$Model][$field]);
				$data['Dropdowns'][$Model][$field] = $DropdownsMastersData;
				$data['JSON'][$Model][$field] = json_encode($DropdownsMastersData);
				$data['MasterDropdowns'][$Model][$field] = $DropdownsMasters;
				$data['MasterDropdownsUrl'][$Model][$field]['controller'] = 'dropdowns';
				$data['MasterDropdownsUrl'][$Model][$field]['action'] = 'masteredit';
				$data['MasterDropdownsUrl'][$Model][$field]['parm'] = $MasterParms;
				$data['MasterJSON'][$Model][$field] = json_encode($DropdownsMasters);

				$HasDependencies = $this->CheckMasterDropdownDependencies($value);

				$data['MasterDropdownsDependency'][$Model][$field] = $HasDependencies;

			}
		}		

		return $data;
	}

	public function CheckDropdownAddLinks($data){

		if(!isset($data['MasterDropdownsUrl'])) return $data;

		foreach($data['MasterDropdownsUrl'] as $key => $value){

			foreach($value as $_key => $_value){

				$dropdown_id = $_value['parm'][15];

				$DropdownMaster = $this->_controller->DropdownsMaster->find('first',array(
					'DropdownsMaster.id' => $dropdown_id,
					'DropdownsMaster.testingcomp_id' => $this->_controller->Auth->user('Testingcomp.id'),
					)
				);

				if(count($DropdownMaster) == 0){
					unset($data['MasterDropdownsUrl'][$key]);
				}

			}
		}

		return $data;
	}

	public function CollectMasterDependency($data){

		if(Configure::check('DropdownsManager') == false) return $data;
		if(Configure::read('DropdownsManager') == false) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];

		$MasterIds = $this->CheckAuthorization($data);

		if($MasterIds == false) return $data;

		$Verfahren = $this->_controller->request->Verfahren;

		$Report['Generally'] = 'Report' . $Verfahren . 'Generally';
		$Report['Specific'] = 'Report' . $Verfahren . 'Specific';
		$Report['Evaluation'] = 'Report' . $Verfahren . 'Evaluation';

		$this->_controller->DropdownsMaster->recursive = -1;

		$DropdownsMasterFields[$Report['Generally'] ] = $this->_controller->DropdownsMaster->find('list',array(
			'fields' => array('field'),
			'conditions' => array('DropdownsMaster.id' => $MasterIds,
				'DropdownsMaster.modul' => 'generally_area',
				'DropdownsMaster.status' => 0,
				'DropdownsMaster.deleted' => 0)
			)
		);
		$DropdownsMasterFields[$Report['Specific']] = $this->_controller->DropdownsMaster->find('list',array(
			'fields' => array('field'),
			'conditions' => array(
				'DropdownsMaster.id' => $MasterIds,
				'DropdownsMaster.modul' => 'specific_area',
				'DropdownsMaster.status' => 0,
				'DropdownsMaster.deleted' => 0
				)
			)
		);
		$DropdownsMasterFields[$Report['Evaluation']] = $this->_controller->DropdownsMaster->find('list',array(
			'fields' => array('field'),
			'conditions' => array('DropdownsMaster.id' => $MasterIds,'DropdownsMaster.modul' => 'evaluation_area','DropdownsMaster.status' => 0,'DropdownsMaster.deleted' => 0)
			)
		);

		foreach($DropdownsMasterFields as $key => $value){

			if(!isset($data['Dropdowns'][$key])) continue;

			foreach ($value as $_key => $_value) {

				if(!isset($data['Dropdowns'][$key][$_value])) continue;
				if(empty($data['Dropdowns'][$key][$_value])) continue;

				$DependenciesFields = $this->_controller->DropdownsMastersDependenciesField->find('list',array(
					'fields' => array('field'),
					'conditions' => array('dropdowns_masters_id' => $_key)
					)
				);

				if(count($DependenciesFields) == 0) continue;

				foreach ($DependenciesFields as $__key => $__value) {

					$options['conditions']['DropdownsMastersDependency.field'] = $__value;
					$options['conditions']['DropdownsMastersDependency.dropdowns_masters_id'] = $_key;
					$options['fields'] = array('value');
					$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('list',$options);

					unset($data['Dropdowns'][$key][$__value]);
					unset($data['DropdownInfo'][$key][$__value]);
					unset($data['JSON'][$key][$__value]);

					$data['Dropdowns'][$key][$__value] = $DropdownsMastersDependency;
					$data['JSON'][$key][$__value] = json_encode($DropdownsMastersDependency);

				}
			}
		}

		return $data;
	}

	public function SortNatDropdowns($data) {

		if(!isset($data)) return $data;
		if(count($data) == 0) return $data;

		$sort = Hash::extract($data['DropdownsMastersData'], '{n}.value');
		$output['DropdownsMastersData'] = array();

		natsort($sort);

		foreach($sort as $key => $value){

			$output['DropdownsMastersData'][$key] = $data['DropdownsMastersData'][$key];
		}

		$data['DropdownsMastersData'] = $output['DropdownsMastersData'];

		return $data;
	}

	public function SortDropdowns($data,$desc,$type) {

		if(empty($desc)) return $data;
		if(empty($type)) return $data;

		foreach ($data as $key => $value) {

			foreach ($value as $_key => $_value) {

				if(count($data[$key][$_key]) == 0) continue;

				$data[$key][$_key] = Hash::sort($data[$key][$_key], '{n}', $desc, $type);

			}
		}


		return $data;
	}

	public function CheckMasterDropdownDependencies($data){

		if(count($data) == 0) return false;

		foreach ($data as $key => $value) {
			$DependenciesFields = $this->_controller->DropdownsMastersDependenciesField->find('list',array('conditions' => array('dropdowns_masters_id' => $value['id'])));
		}

		if(count($DependenciesFields) == 0) return false;
		else return true;

		return false;
	}

	public function CollectDependenciesField($data){

		$id = $this->_controller->request->projectvars['VarsArray'][15];
		$data['DependencyFields']['selected'] = array();

		$DependenciesFields = $this->_controller->DropdownsMastersDependenciesField->find('list',array('fields' => array('field'),'conditions' => array('DropdownsMastersDependenciesField.dropdowns_masters_id' => $id)));

		if(count($DependenciesFields) == 0) return $data;

		$Fields = array();
		$Fields[] = '';

		foreach ($DependenciesFields as $key => $value){
			$Fields[$value] = $value;
		}

		$data['DependencyFields']['selected'] = $Fields;

		if(isset($data['DropdownsMaster']['id']) && isset($data['DropdownsMastersData']['id'])){
			$options['conditions']['DropdownsMastersDependency.dropdowns_masters_id'] = $data['DropdownsMaster']['id'];
			$options['conditions']['DropdownsMastersDependency.dropdowns_masters_data_id'] = $data['DropdownsMastersData']['id'];

			foreach ($DependenciesFields as $key => $value){
				$options['conditions']['DropdownsMastersDependency.field'] = $value;
				$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('all',$options);
				$data['DropdownsMastersDependency'][$value] = $DropdownsMastersDependency;
				$Fields[$value] = $value;
			}
		}

		return $data;
	}


	public function AddDescriptionOfField($data){

		if(!isset($data['DropdownsMaster'])) return $data;
		if(empty($data['DropdownsMaster']['field'])) return $data;
		if(!isset($data['DropdownsFields'][$data['DropdownsMaster']['field']])) return $data;

		$data['DropdownsMaster']['field_name'] = $data['DropdownsFields'][$data['DropdownsMaster']['field']];

		return $data;
	}

	public function SeparateThisDependenciesField($data,$field){

		if(!isset($data['DependencyFields'])) return $data;
		if(!isset($data['DependencyFields'][$field])) return $data;

		$this_field = $data['DependencyFields'][$field];

		unset($data['DependencyFields']);

		$data['DependencyFields'][$field] = $this_field;

		return $data;
	}

	public function SeparateDependenciesField($data){

		if(!isset($data['DropdownsFields'])) return $data;
		if(!isset($data['DependencyFields'])) return $data;
		if(!isset($data['DependencyFields']['selected'])) return $data;

		$Fields = array();

		foreach ($data['DependencyFields']['selected'] as $key => $value) {
			if(isset($data['DropdownsFields'][$value])) $Fields[$value] = $data['DropdownsFields'][$value];
		}

		unset($data['DropdownsFields']);
		unset($data['DependencyFields']);

		$data['DependencyFields'] = $Fields;

		return $data;
	}

	public function CollectFieldsForMaster($data){

		if($data['DropdownsMaster']['modul'] == 'device') $Modul = 'Device';
		if($data['DropdownsMaster']['modul'] == 'examiner') $Modul = 'Examiner';
		if($data['DropdownsMaster']['modul'] == 'document') $Modul = 'Document';

		if(isset($Modul)) {
			if($Modul == 'Device' || $Modul == 'Examiner'|| $Modul == 'Document') {

				$lang = Configure::read('Config.language');
				$fields = array('' => '');
				$xml = $this->_controller->Xml->DatafromXml($Modul,'file',null);

				if(empty($xml['settings']->$Modul)) return;

				foreach ($xml['settings']->$Modul->children() as $_key => $_value) {

				if(empty($_value->output->screen)) continue;

					$fields[trim($_value->key)] = trim($_value->discription->$lang);
				}

				asort($fields);

				$data['DropdownsFields'] = $fields;

				return $data;

			}
		}

		$test = $this->_AddMasterDropdownTestingcompByTestingcomp($data);
		$data['ThisTestingcomp'] = $test['Testingcomp'];

		if(!isset($data['Testingmethod'])){

			$data['DropdownsFields'] = array();
			$data['DropdownsFields'] = $this->_CollectDropdownFields($data);
			return $data;

		} 


		if(!isset($data['Testingmethod']['selected'])){

			$data['DropdownsFields'] = array();
			$data['DropdownsFields'] = $this->_CollectDropdownFields($data);
			return $data;

		} 

		if(!is_array($data['Testingmethod']['selected'])){

			$data['DropdownsFields'] = array();
			$data['DropdownsFields'] = $this->_CollectDropdownFields($data);
			return $data;

		}

		if(count($data['Testingmethod']['selected']) == 0){

			$data['DropdownsFields'] = array();
			$data['DropdownsFields'] = $this->_CollectDropdownFields($data);
			return $data;

		}

		if(empty($data['DropdownsMaster']['modul'])){

			$data['DropdownsFields'] = array();
			$data['DropdownsFields'] = $this->_CollectDropdownFields($data);
			return $data;

		}

		$data['DropdownsFields'] = $this->_CollectDropdownFields($data);

		return $data;

	}

	protected function _CollectDropdownFields($data){

		$lang = Configure::read('Config.language');
		$fields = array('' => '');

		if($data['DropdownsMaster']['modul'] == 'generally_area') $Modul = 'Generally';
		if($data['DropdownsMaster']['modul'] == 'specific_area') $Modul = 'Specific';
		if($data['DropdownsMaster']['modul'] == 'evaluation_area') $Modul = 'Evaluation';

		$testingmethods = $this->_controller->Testingmethods->find('list',array('fields' => array('id','value'),'conditions' => array('Testingmethods.id' => $data['Testingmethod']['selected'])));

		if(count($testingmethods) == 0) return array();

		foreach ($testingmethods as $key => $value) {

			$verfahren = $value;
			$Verfahren = Inflector::camelize($verfahren);
			$ThisModul = 'Report' . $Verfahren . $Modul;

			$testingmethod_xml = $this->_controller->Xml->DatafromXml($verfahren,'file',$Verfahren);

			if(empty($testingmethod_xml['settings']->{$ThisModul})) continue;

			foreach ($testingmethod_xml['settings']->{$ThisModul}->children() as $_key => $_value) {

				if(empty($_value->output->screen)) continue;

				$fields[trim($_value->key)] = trim($_value->discription->{$lang}) . ' (' . Inflector::humanize(trim($_value->key)) . ')';
			}
		}

		asort($fields);

		return $fields;
	}

	public function FindCurrentDependencyValue($data){

		$locale = $this->_controller->Lang->Discription();
		$id = $this->_controller->request->projectvars['VarsArray'][15];
		$DataId = $this->_controller->request->projectvars['VarsArray'][16];
		$ValueId = $this->_controller->request->projectvars['VarsArray'][17];

		$option = array('conditions' => array('DropdownsMastersDependency.id' => $ValueId));

		if($this->_controller->DropdownsMastersDependency->exists($ValueId) == false){
			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusText'] = __('This entry does not exist',true);

			return $data;

		} else {

			$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('first',$option);
			$data['DropdownsMastersDependency'] = $DropdownsMastersDependency['DropdownsMastersDependency'];

		}

		return $data;
	}

	public function EditCurrentDependencyValue($data){

		$locale = $this->_controller->Lang->Discription();
		$id = $this->_controller->request->projectvars['VarsArray'][15];
		$DataId = $this->_controller->request->projectvars['VarsArray'][16];
		$ValueId = $this->_controller->request->projectvars['VarsArray'][17];

		$JsonValueOld = $this->_controller->request->data['json_value_old'];
		$JsonValue = $this->_controller->request->data['json_value'];

		if($this->_controller->DropdownsMastersDependency->exists($ValueId) == false){
			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusText'] = __('This entry does not exist',true);

			return $data;
		}

		$option['conditions']['DropdownsMastersDependency.value'] = $JsonValue;
		$option['conditions']['DropdownsMastersDependency.field'] = $data['DropdownsMastersDependency']['field'];
		$option['conditions']['DropdownsMastersDependency.dropdowns_masters_id'] = $data['DropdownsMaster']['id'];
		$option['conditions']['DropdownsMastersDependency.dropdowns_masters_data_id'] = $data['DropdownsMastersData']['id'];

		$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('first',$option);

		if(count($DropdownsMastersDependency) > 0){

			$data['StatusClass'] = 'hint';
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusText'] = __('Entry already exists',true) . ': "' . $JsonValue . '"';

			return $data;
		}

		$option = array();
		$option['conditions']['DropdownsMastersDependency.id'] = $ValueId;
		$option['conditions']['DropdownsMastersDependency.id'] = $ValueId;
		$option['conditions']['DropdownsMastersDependency.value'] = $JsonValueOld;
		$option['conditions']['DropdownsMastersDependency.field'] = $data['DropdownsMastersDependency']['field'];
		$option['conditions']['DropdownsMastersDependency.dropdowns_masters_id'] = $data['DropdownsMaster']['id'];
		$option['conditions']['DropdownsMastersDependency.dropdowns_masters_data_id'] = $data['DropdownsMastersData']['id'];

		$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('first',$option);

		if(count($DropdownsMastersDependency) == 0){

			$data['StatusClass'] = 'hint';
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusText'] = __('This entry could not be saved',true);

			return $data;
		}

		$insert['DropdownsMastersDependency']['id'] = $data['DropdownsMastersDependency']['id'];
		$insert['DropdownsMastersDependency']['value'] = $JsonValue;

		if($this->_controller->DropdownsMastersDependency->save($insert)){
			$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('first',array('conditions' => array('DropdownsMastersDependency.id' => $data['DropdownsMastersDependency']['id'])));
			$data['DropdownsMastersDependency'] = $DropdownsMastersDependency['DropdownsMastersDependency'];
			$data['StatusText'] = $JsonValue;
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusClass'] = 'success';

		} else {
			$data['StatusText'] = __('This entry could not be saved',true);
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusClass'] = 'error';
		}

		return $data;
	}

	public function CollectModulDropdown($reportnumbers) {

		if(Configure::check('CertifcateManagerReportInsert') === false) return $reportnumbers;
		if(Configure::read('CertifcateManagerReportInsert') == false) return $reportnumbers;

		if(count($reportnumbers) == 0) return $reportnumbers;

		$ReportGenerally = $this->_controller->request->tablenames[0];

		// Die Werte aus der Dropdownverwaltung werden gelöscht
		if(isset($reportnumbers['Dropdowns'][$ReportGenerally]['examiner'])) unset($reportnumbers['Dropdowns'][$ReportGenerally]['examiner']);
		if(isset($reportnumbers['Dropdowns'][$ReportGenerally]['supervision'])) unset($reportnumbers['Dropdowns'][$ReportGenerally]['supervision']);


		$reportnumbers = $this->_controller->Depentency->GetFromCertificateModulManyTestingmethods($reportnumbers);

		return $reportnumbers;

	}

	public function ChangeDropdownData($ReportArray,$arrayData,$Model) {

		$this->_controller->loadModel('DropdownsValue');

		if(!is_array($Model)) $Model = array($Model);

		foreach($Model as $_Model) {

			if(!isset($ReportArray[$_Model])) continue;

			foreach($ReportArray[$_Model] as $key => $value) {
				if(isset($ReportArray['Dropdowns'])) {
					if(isset($ReportArray['Dropdowns'][$_Model][$key])) {
						if(array_search($value, $ReportArray['Dropdowns'][$_Model][$key]) !== false){
							$_key = array_search($value, $ReportArray['Dropdowns'][$_Model][$key]);
							if(isset($ReportArray['Dropdowns'][$_Model][$key][$_key])){
							$ReportArray[$_Model][$key] = $ReportArray['Dropdowns'][$_Model][$key][$_key];
							continue;
							}
						}
						if(array_search($value, $ReportArray['Dropdowns'][$_Model][$key]) === false)
						{
							continue;
						}
					}
				}

				// bei leerem Wert und Feld, welches kein Dropdown ist, nicht weiter suchen
				if(empty($value) || !is_numeric($value)) continue;

				if(!isset($arrayData['settings']->$_Model->$key->select->model) || empty($arrayData['settings']->$_Model->$key->select->model)) continue;

				try {
					$this->_controller->DropdownsValue->recursive = 0;
					$value = $this->_controller->DropdownsValue->find('first', array(
						'conditions'=>array(
							'DropdownsValue.id' => $value
						)
					));

					// Falls das Dropdown nicht selbst zum Feld gehört, testen, ob es verlinkt ist
					if(isset($value['Dropdown'])){
						if($value['Dropdown']['model'] != $_Model || $value['Dropdown']['field'] != $key) {
							$this->_controller->DropdownsValue->Dropdown->recursive = -1;
							if($this->_controller->DropdownsValue->Dropdown->find('count', array(
								'conditions'=>array(
									'Dropdown.linking'=>$value['Dropdown']['id'],
									'Dropdown.model' => $_Model,
									'Dropdown.field' => $key
								)
							)) == 0) continue;
						}
					}

					if(!empty($value)) $ReportArray[$_Model][$key] = $value['DropdownsValue']['discription'];
				} catch(Exception $ex) {
					$log = $this->_controller->DropdownsValue->getDatasource()->getLog();
					$log = end($log['log']);
					$this->_controller->log($log['query']);
					$this->_controller->log($ex->getMessage());
				}
			}
		}

		return $ReportArray;
	}

	public function DeleteCurrentDependencyValue($data){

		$locale = $this->_controller->Lang->Discription();
		$id = $this->_controller->request->projectvars['VarsArray'][15];
		$DataId = $this->_controller->request->projectvars['VarsArray'][16];
		$ValueId = $this->_controller->request->projectvars['VarsArray'][17];

		$option = array('conditions' => array('DropdownsMastersDependency.id' => $ValueId));

		if($this->_controller->DropdownsMastersDependency->exists($ValueId) == false){
			$data['StatusClass'] = 'error';
			$data['RemoveTableRow'] = 'tr_' . $ValueId;
			$data['StatusText'] = __('This entry does not exist',true);
			return $data;
		} else {

			$DropdownsMastersDependency = $this->_controller->DropdownsMastersDependency->find('first',$option);
			$data['DropdownsMastersDependency'] = $DropdownsMastersDependency['DropdownsMastersDependency'];

			if($this->_controller->DropdownsMastersDependency->delete($ValueId)){
				$data['StatusText'] = __('The entry was deleted',true);
				$data['RemoveTableRow'] = 'tr_' . $ValueId;
				$data['StatusClass'] = 'success';

			} else {
				$data['StatusText'] = __('The entry could not be deleted',true);
				$data['RemoveTableRow'] = 'tr_' . $ValueId;
				$data['StatusClass'] = 'error';
			}
		}

		return $data;
	}

	public function GetDevicesForDropdown($deviceids) {
		$devices = array();
		$this->_controller->loadModel('Device');
		$this->_controller->Device->recursive = -1;
		$devices = $this->_controller->Device->find('all',array('conditions'=>array('Device.id'=>$deviceids)));
		return $devices;
	}

	public function DeviceDataForMaster($ids){
		$this->_controller->loadModel('Device');
		$DropdownsMastersData = $this->_controller->Device->find('list',array(
			'order' => array('name ASC'),
			'fields' => array('name'),
			'conditions' => array('Device.id'=>$ids,'Device.active' => 1,'Device.deleted' => 0)
			)
		);
		return $DropdownsMastersData;
	}

	public function GetDeviceDropdownsMasters($ids){
		$this->_controller->loadModel('DevicesDropdownsMaster');
		$deviceids = $this->_controller->DevicesDropdownsMaster->find('list',array('fields'=>array('device_id','device_id'),'conditions'=>array('dropdowns_masters_id'=>$ids)));
		if(empty($deviceids)) return array();
		if(!empty($deviceids)) return $deviceids;

	}

	public function GetReportDropdownsMastersJson(){

		$output = array();

		$output = $this->__GetReportDropdownsMastersJson($output);
		$output = $this->__GetReportRadioMastersJson($output);

		return $output;

	}

	protected function __GetReportRadioMastersJson($output){

		$Check = strstr($this->_controller->request->data['Class'], 'editableradio');

		if($Check === false) return $output;

		if(!isset($this->_controller->request->data['json_true'])) return array();
		if(!isset($this->_controller->request->data['Mod'])) return array();
		if($this->_controller->request->data['Mod'] != 'report') return array();
		if(!isset($this->_controller->request->data['Model'])) return array();
		if(!isset($this->_controller->request->data['Field'])) return array();
		if(!isset($this->_controller->request->data['Modul'])) return array();
		if(!isset($this->_controller->request->data['DataId'])) return array();

		$Model = $this->_controller->request->data['Model'];
		$Field = $this->_controller->request->data['Field'];
		$FieldId = '#' . $this->_controller->request->data['ThisFieldId'];
		$Modul = $this->_controller->request->data['Modul'];
		$DataId = $this->_controller->request->data['DataId'];
		$Type = $this->_controller->request->data['Type'];

		$this->_controller->loadModel('Reportnumber');
		$this->_controller->loadModel($Model);

		$TableData = $this->_controller->{$Model}->find('first',array('conditions' => array($Model . '.id' => $DataId)));

		if(empty($TableData)) return $output;

		$ReportnumberId = $TableData[$Model]['reportnumber_id'];

		$this->_controller->Reportnumber->recursive = 0;

		$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $ReportnumberId)));

		$Check = $this->CheckAuthorization($Reportnumber);

		if($Field == 'result') $Check = true;

		if($Check === false) return $output;

		$xml = $this->_controller->Xml->LoadXmlFileForReport($Reportnumber);

		if(trim($xml['settings']->{$Model}->{$Field}->fieldtype) != 'radio') return $output;

		$output['datamodel'] = $Model;
		$output['datafield'] = $Field;
		$output['type'] = $Type;
		$output['dataid'] = $DataId;
		$output['fieldid'] = $FieldId;
		$output['options'][] = array('key' => ' ','val' => ' ');

		if(empty($xml['settings']->{$Model}->{$Field}->radiooption->value)){

		} else {

			$x = 0;

			foreach ($xml['settings']->{$Model}->{$Field}->radiooption->value as $key => $value) {

				$output['options'][] = array('key' => $x,'val' => trim($value));

				if($x == $TableData[$Model][$Field]) $output['value'] = trim($value);

				$x++;
			}

		}

		return $output;

	}

	protected function __GetReportDropdownsMastersJson($output){

		$Check = strstr($this->_controller->request->data['Class'], 'editableselect');

		if($Check === false) return $output;

		if(!isset($this->_controller->request->data['json_true'])) return array();
		if(!isset($this->_controller->request->data['Mod'])) return array();
		if($this->_controller->request->data['Mod'] != 'report') return array();
		if(!isset($this->_controller->request->data['Model'])) return array();
		if(!isset($this->_controller->request->data['Field'])) return array();
		if(!isset($this->_controller->request->data['Modul'])) return array();
		if(!isset($this->_controller->request->data['DataId'])) return array();

		$Model = $this->_controller->request->data['Model'];
		$Field = $this->_controller->request->data['Field'];
		$FieldId = '#' . $this->_controller->request->data['ThisFieldId'];
		$Modul = $this->_controller->request->data['Modul'];
		$DataId = $this->_controller->request->data['DataId'];
		$Type = $this->_controller->request->data['Type'];

		$this->_controller->loadModel($Model);
		$this->_controller->loadModel('Reportnumber');
		$this->_controller->loadModel('DropdownsMaster');

		$TableData = $this->_controller->{$Model}->find('first',array('conditions' => array($Model . '.id' => $DataId)));

		if(empty($TableData)) return array();

		$ReportnumberId = $TableData[$Model]['reportnumber_id'];

		$this->_controller->Reportnumber->recursive = -1;

		$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $ReportnumberId)));

		$Check = $this->CheckAuthorization($Reportnumber);

		if($Check === false) return array();

		$options = array(
			'conditions' => array(
				'DropdownsMaster.modul' => $Modul,
				'DropdownsMaster.field' => $Field,
				'DropdownsMaster.deleted' => 0
			)
		);

		$DropdownsMaster = $this->_controller->DropdownsMaster->find('first', $options);

		$DropdownsMaster = $this->SortNatDropdowns($DropdownsMaster);

		if(empty($DropdownsMaster)) return array();
		if(empty($DropdownsMaster['DropdownsMastersData'])) return array();

		$output['datamodel'] = $Model;
		$output['datafield'] = $Field;
		$output['type'] = $Type;
		$output['dataid'] = $DataId;
		$output['value'] = $TableData[$Model][$Field];
		$output['fieldid'] = $FieldId;
		$output['options'][] = array('key' => ' ','val' => ' ');

		foreach ($DropdownsMaster['DropdownsMastersData'] as $key => $value) {

			if($value['value'] == $TableData[$Model][$Field]) $output['id'] = $value['value'];

			$output['options'][] = array('key' => $value['value'],'val' => $value['value']);
		}

		return $output;

	}

}
