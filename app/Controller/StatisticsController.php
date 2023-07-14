<?php
App::uses('AppController', 'Controller');
App::uses('Xml', 'Utility');

class StatisticsController extends AppController {

	public $uses = null;
//	public $components = array('Autorisierung', 'Sicherheit', 'Xml');

	public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Image', 'Pdf', 'Csv');
	public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html','Image');

	protected $errorNumbers = null;

	public function beforeFilter() {

		$this->Lang->Choice();
		$this->Lang->Change();

		parent::beforeFilter();

		App::import('Vendor', 'Authorize');
		if(array_search($this->request->action, array('exportpdf','exportcsv')) === false)
			$this->Navigation->ajaxURL();

		$this->Autorisierung->Protect();
		$this->Navigation->ReportVars();

		// Fehlernummer laden
		$this->loadModel('ErrorNumber');
		$errors = $this->ErrorNumber->find('list', array(
			'fields'=>array('number', 'text')
		));
		$this->errorNumbers = $errors;
		$this->set('errors', $errors);

	}

	function afterFilter() {
	}

	public function index($id = null) {
/*
		$this->Session->write('test', 1);
		$this->loadModel('Autocomplete');
		$this->layout = 'modal';

		$projectID = $this->request->projectvars['projectID'];

		if($projectID == 0) {
			$projectID = $this->Autorisierung->ConditionsTopprojects();
		} else {
			$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		}

		$this->loadModel('Reportnumber');
		//$searchFields = $this->Xml->XmlToArray('search','file');
		$searchFields = $this->Xml->XmlToArray('statistics','file',null);
		//die(print_r($xml, true));

		$fields = array('autocompletes'=>array(), 'dropdowns'=>array());

		foreach($searchFields->fields->children() as $field)
		{
			if(trim($field->autocomplete) == 1 || trim($field->autocomplete) == true)
			{
				array_push(
					$fields['autocompletes'],
					array(
						'Model'=>isset($field->model) ? trim($field->model) : null,
						'Key'=>isset($field->key) ? trim($field->key) : null,
						'Caption'=>isset($field->value) ? trim($field->value) : null,
						'Break'=>isset($field->break) ? trim($field->break) : null,
						'Area'=>isset($field->area) ? trim($field->area) : null
					)
				);
			}
			else if(trim($field->model) != '')
			{
				// inline-bedingungen mit (condition) ? true : false scheinen noch buggy zu sein, wenn man mehrere verkettet
				// darum in Einzelnschritten
				$option = array_merge(array('value'=>null, 'format'=>null), (array)$field->option);
				if(isset($option[0])) { $option['value'] = $option[0]; unset($option[0]); }

				$output = array_merge(array('value'=>null, 'format'=>null), (array)$field->output);
				if(isset($output[0])) { $output['value'] = $output[0]; unset($output[0]); }

				$fields['dropdowns'][trim($field->key)] = array(
					'Model' => isset($field->model) ? trim($field->model) : null,
					'OptionField' => array(
						'value' => trim($option['value']),
						'format'=> trim($option['format'])
					),
					'OutputField' => array(
						'value' => trim($output['value']),
						'format'=> trim($output['format'])
					),
					'Value' => isset($field->value) ? trim($field->value) : null,
					'Break' => isset($field->break) ? trim($field->break) : null,
					'Area'=> isset($field->area) ? trim($field->area) : null
				);

				if(isset($field->value->option) && $field->value->option->count() > 0) {
					foreach($field->value->option as $_option) {
						$fields['dropdowns'][trim($field->key)]['Result'][trim($_option->id)] = trim($_option->value->{$this->Lang->Discription()});
					}
				}
			}
		}

		$this->loadModel('Topproject');
		$this->Topproject->recursive = 1;
		$Topproject = $this->Topproject->find('first',array('conditions' => array_filter(array('Topproject.id' => $projectID))));

		$this->loadModel('Report');
		$testingmethodesglobal = array();

		foreach($Topproject['Report'] as $_reports){
			$Reports = $this->Report->find('first',array('conditions' => array('Report.id' => $_reports['id'])));
			foreach($Reports['Testingmethod'] as $_testingmethod){
				$testingmethodesglobal[$_testingmethod['id']] = $_testingmethod['id'];
			}
		}
		$optionsTestingmethod =
		array(
			'conditions' => array(
				'Testingmethod.id' => $testingmethodesglobal
			)
		);

		// die im Set vorhandenen Prüfberichte für das Menü
		$this->loadModel('Testingmethod');
		$this->Testingmethod->recursive = 0;
		$testingmethods = $this->Testingmethod->find('all', $optionsTestingmethod);

		// Wenn die Suchd von der Projektebene aufgerufen wurde müssen die Reports entsprechend des Projektes gewählt werden
		$thisReports = array();
		if(count($testingmethods) == 0){


			$this->loadModel('Topproject');
			$topproject = $this->Topproject->find('first', array('conditions' => array('topproject.id' => $projectID)));

			foreach($topproject['Report']as $_key => $_Report){
				$thisReports[$_Report['id']] = $_Report['name'];
			}
		}

//		$autocompletesData = ($this->Data->SearchAutocomplets($testingmethods, $fields));

		$years = array_unique($this->Reportnumber->find('list', array(
			'fields'=>array(
				'Reportnumber.year',
			),
		)));
		rsort($years);

		if(isset($this->request->data['Reportnumber'])) $this->request->data['Reportnumber'] = array_filter($this->request->data['Reportnumber']);

		if(isset($this->request->data['Reports'])) {
			foreach($this->request->data['Reports'] as $key=>$field) {
				$this->request->data['Reports'][$key] = array_filter($field);
				if(empty($this->request->data['Reports'][$key])) unset($this->request->data['Reports'][$key]);
			}
		}


		if( !empty($this->request->data['Reportnumber']) || isset($this->request->data['Reports']) && $this->request->is('post') ) {
			$options = array();
			$options['conditions']['Reportnumber.topproject_id'] = $projectID;
			$options['conditions']['Reportnumber.status >='] = 2;
			$options['conditions']['Reportnumber.delete'] = 0;

			if(AuthComponent::user('Testingcomp.extern') == 0){
				$options['conditions']['Reportnumber.testingcomp_id'] = $this->Autorisierung->ConditionsTestinccomps();
			}

			$conditions = array();

			foreach(array_filter($this->request->data['Reportnumber'], function($elem) {return trim($elem) !== '';}) as $field=>$value) {
				if(array_search($field, array('topproject_id', 'report_id', 'testingmethod_id', 'testingcomp_id')) !== false) continue;
				$count = count($conditions);

				// Suchbereiche in Mysql übersetzen
				$suffix = null;

				if(preg_match('/_from$/', $field)){
					$value = date("Y-m-d",strtotime($value));
					$suffix = ' >=';
				}
				if(preg_match('/_to$/', $field)){
					$_value = explode('-',$value);
					$value .= '-' . cal_days_in_month(CAL_GREGORIAN, $_value[1], $_value[0]);
					$suffix = ' <=';
				}

				$field = preg_replace('/_(from|to)$/', '', $field);

				$conditions[$count] = array_merge(
					array(
						'model' => trim($fields['dropdowns'][$field]['Model']),
						'field' => $field,
						'discription'.$suffix => $value
					),
					array_filter(array(
						'testingcomp_id'	=> isset($this->request->data['Reportnumber']['testingcomp_id']) && !empty($this->request->data['Reportnumber']['testingcomp_id'])
											? $this->request->data['Reportnumber']['testingcomp_id']
											: $this->Autorisierung->ConditionsTestinccomps(),
						'topproject_id'		=> isset($this->request->data['Reportnumber']['topproject_id']) && !empty($this->request->data['Reportnumber']['topproject_id'])
											? $this->request->data['Reportnumber']['topproject_id']
											: $this->request->projectID,
						'equipment_type_id'	=> isset($_delivery['equipment_type_id']) ? $_delivery['equipment_type_id'] : isset($this->request->data['Reportnumber']['equipment_type_id']) && !empty($this->request->data['Reportnumber']['equipment_type_id'])
											? $this->request->data['Reportnumber']['equipment_type_id']
											: $this->request->equipmentType,
						'equipment_id'		=> isset($_delivery['equipment_id']) ? $_delivery['equipment_id'] : isset($this->request->data['Reportnumber']['equipment_id']) && !empty($this->request->data['Reportnumber']['equipment_id'])
											? $this->request->data['Reportnumber']['equipment_id']
											: $this->request->equipmentID,
						'order_id'			=> isset($this->request->data['Reportnumber']['order_id']) && !empty($this->request->data['Reportnumber']['order_id'])
											? $this->request->data['Reportnumber']['order_id']
											: $this->request->orderID,
						'report_id'			=> isset($this->request->data['Reportnumber']['report_id']) && !empty($this->request->data['Reportnumber']['report_id'])
											? $this->request->data['Reportnumber']['report_id']
											: $this->request->reportID,
						'testingmethod_id'	=> isset($this->request->data['Reportnumber']['testingmethod_id']) && !empty($this->request->data['Reportnumber']['testingmethod_id'])
											? $this->request->data['Reportnumber']['testingmethod_id']
											: 0,
					))
				);
			}
			foreach($this->request->data['Reports'] as $model=>$_model) {
				foreach(array_filter($_model, function($elem) {return $elem !== '';}) as $field=>$value) {
					if(array_search($field, array('topproject_id', 'report_id', 'testingmethod_id', 'testingcomp_id')) !== false) continue;
					$count = count($conditions);
					// Suchbereiche in Mysql übersetzen
					$suffix = ' LIKE';

					if($field == 'created_from' && !empty($value)){
						$value .= ' 00:00:00';
					}

					if($field == 'created_to' && !empty($value)){
						$value .= ' 23:59:59';
					}

					if(preg_match('/_from$/', $field)) $suffix = ' >=';
					if(preg_match('/_to$/', $field)) $suffix = ' <=';

					$field = preg_replace('/_(from|to)$/', '', $field);


					$conditions[$count] = array_merge(
						array(
							'model' => $model,
							'field' => $field,
							'discription'.$suffix => $suffix != ' LIKE' ? $value : '%'.$value.'%',
						),
						array_filter(array(
							'testingcomp_id'	=> isset($this->request->data['Reportnumber']['testingcomp_id']) && !empty($this->request->data['Reportnumber']['testingcomp_id'])
												? $this->request->data['Reportnumber']['testingcomp_id']
												: $this->Autorisierung->ConditionsTestinccomps(),
							'topproject_id'		=> isset($this->request->data['Reportnumber']['topproject_id']) && !empty($this->request->data['Reportnumber']['topproject_id'])
												? $this->request->data['Reportnumber']['topproject_id']
												: $this->request->projectID,
							'equipment_type_id'	=> isset($_delivery['equipment_type_id']) ? $_delivery['equipment_type_id'] : isset($this->request->data['Reportnumber']['equipment_type_id']) && !empty($this->request->data['Reportnumber']['equipment_type_id'])
												? $this->request->data['Reportnumber']['equipment_type_id']
												: $this->request->equipmentType,
							'equipment_id'		=> isset($_delivery['equipment_id']) ? $_delivery['equipment_id'] : isset($this->request->data['Reportnumber']['equipment_id']) && !empty($this->request->data['Reportnumber']['equipment_id'])
												? $this->request->data['Reportnumber']['equipment_id']
												: $this->request->equipmentID,
							'order_id'			=> isset($this->request->data['Reportnumber']['order_id']) && !empty($this->request->data['Reportnumber']['order_id'])
												? $this->request->data['Reportnumber']['order_id']
												: $this->request->orderID,
							'report_id'			=> isset($this->request->data['Reportnumber']['report_id']) && !empty($this->request->data['Reportnumber']['report_id'])
												? $this->request->data['Reportnumber']['report_id']
												: $this->request->reportID,
							'testingmethod_id'	=> isset($this->request->data['Reportnumber']['testingmethod_id']) && !empty($this->request->data['Reportnumber']['testingmethod_id'])
												? $this->request->data['Reportnumber']['testingmethod_id']
												: 0,
						))
					);
				}
			}

			if(empty($conditions)) {
				$conditions = array(
					array_filter(array(

						'testingcomp_id'	=> isset($this->request->data['Reportnumber']['testingcomp_id']) && !empty($this->request->data['Reportnumber']['testingcomp_id'])
											? $this->request->data['Reportnumber']['testingcomp_id']
											: $this->Autorisierung->ConditionsTestinccomps(),

						'topproject_id'		=> isset($this->request->data['Reportnumber']['topproject_id']) && !empty($this->request->data['Reportnumber']['topproject_id'])
											? $this->request->data['Reportnumber']['topproject_id']
											: $this->request->projectID,
						'equipment_type_id'	=> isset($_delivery['equipment_type_id']) ? $_delivery['equipment_type_id'] : isset($this->request->data['Reportnumber']['equipment_type_id']) && !empty($this->request->data['Reportnumber']['equipment_type_id'])
											? $this->request->data['Reportnumber']['equipment_type_id']
											: $this->request->equipmentType,
						'equipment_id'		=> isset($_delivery['equipment_id']) ? $_delivery['equipment_id'] : isset($this->request->data['Reportnumber']['equipment_id']) && !empty($this->request->data['Reportnumber']['equipment_id'])
											? $this->request->data['Reportnumber']['equipment_id']
											: $this->request->equipmentID,
						'order_id'			=> isset($this->request->data['Reportnumber']['order_id']) && !empty($this->request->data['Reportnumber']['order_id'])
											? $this->request->data['Reportnumber']['order_id']
											: $this->request->orderID,
						'report_id'			=> isset($this->request->data['Reportnumber']['report_id']) && !empty($this->request->data['Reportnumber']['report_id'])
											? $this->request->data['Reportnumber']['report_id']
											: $this->request->reportID,
						'testingmethod_id'	=> isset($this->request->data['Reportnumber']['testingmethod_id']) && !empty($this->request->data['Reportnumber']['testingmethod_id'])
											? $this->request->data['Reportnumber']['testingmethod_id']
											: 0,
					))
				);

				if( AuthComponent::user('Testingcomp.extern') == 1){
					unset($conditions[0]['testingcomp_id']);
				}
			}

			$this->Autocomplete->recursive = -1;
			$autocomplete = $this->Autocomplete;

			// Für alle Felder passende Menge an ReportnumberIDs holen
			$conditions = array_map(function($_cond) use($autocomplete){
				return array_unique($autocomplete->find('list', array('fields'=>array('reportnumber_id'), 'conditions'=>$_cond)));
			}, $conditions);


			// Schnittmenge der ReportnumberIDs ermitteln
			$reportnumber_ids = array();
			foreach($conditions as $ids) {
				if(empty($ids)) {
					$reportnumber_ids = array();
					break;
				}

				$reportnumber_ids = empty($reportnumber_ids) ? $ids : array_intersect($reportnumber_ids, $ids);
			}

			$options['conditions']['Reportnumber.id'] = $reportnumber_ids;
			$options['conditions']['Reportnumber.delete'] = 0;

			if(AuthComponent::user('Roll.id') < 5){
				$options['conditions']['Reportnumber.testingcomp_id !='] = 0;
			} else {
//				$options['conditions']['Reportnumber.testingcomp_id'] = $this->Autorisierung->ConditionsTestinccomps();
			}

		}
		else {

			$options['conditions'] = array();

			if($this->request->projectvars['projectID'] > 0)$options['conditions']['Reportnumber.topproject_id'] = $this->request->projectvars['projectID'];
			if($this->request->projectvars['equipmentType'] > 0)$options['conditions']['Reportnumber.equipment_type_id'] = $this->request->projectvars['equipmentType'];
			if($this->request->projectvars['equipment'] > 0)$options['conditions']['Reportnumber.equipment_id'] = $this->request->projectvars['equipment'];
			if($this->request->projectvars['orderID'] > 0)$options['conditions']['Reportnumber.order_id'] = $this->request->projectvars['orderID'];

			$options['conditions']['Reportnumber.delete'] = 0;
//			$options['conditions']['Reportnumber.status'] > 1;

			if(AuthComponent::user('Roll.id') < 5 || AuthComponent::user('Testingcomp.extern') == 1){
				$options['conditions']['Reportnumber.testingcomp_id !='] = 0;
			} else {
				$options['conditions']['Reportnumber.testingcomp_id'] = $this->Autorisierung->ConditionsTestinccomps();
			}
		}

		$options['fields'] = array('Reportnumber.testingmethod_id');
		$options['group'] = array('Reportnumber.testingmethod_id');

		$this->Reportnumber->recursive = -1;

		$optionTestingmethod = $this->Reportnumber->find('list', $options);

		$_methods = $this->Reportnumber->Testingmethod->find('list',array('fields' => array('id','value'), 'conditions' => array('Testingmethod.id' => $optionTestingmethod)));

		$searchoutput = $this->Data->GetSearchFields($_methods);

		unset($options['fields']);
		unset($options['group']);

		$testingreportsCount = count($testingreports = $this->Reportnumber->find('all', $options));

		// Für die schneller Zuordnung aller Nahtabschnitte die Prüfberichte mit Reportnumber.id als Index merken
		$testingreports = array_combine(array_map('strval', Hash::extract($testingreports, '{n}.Reportnumber.id')), $testingreports);

		$hint = null;

		if(isset($reportnumber_ids) && count($testingreports) < count($reportnumber_ids)){
			$hint = __('Es konnten nicht alle gefundenen Prüfberichte ausgewertet werden, möglicherweise wurden Berichte gelöscht oder nicht geschlossen.',true);
		}

		$CountTestreports = count($testingreports);

		$methods = array();
		$welders = array();

		$output = $this->Data->GetWeldEvaluation($_methods,$testingreports);

		$testingreports = $output['testingreports'];
		$welds = $output['welds'];
		$welderrors = $output['welderrors'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),11 =>  __('November', true),12 =>  __('December', true));

		$weldbyTime = array();
		$month_count = array();
		$year_count = array();

		$output = $this->Data->GetWeldStatistic($welds);

		$welds = $output['welds'];
		$welders = $output['welders'];
		$weldbyTime = $output['weldbyTime'];
		$year_count = $output['year_count'];
		$month_count = $output['month_count'];

		$timedifferenc['years_all'] = 0;
		$timedifferenc['monts_all'] = 0;
		$timedifferenc['days_all'] = 0;

		foreach($weldbyTime as $_key => $_weldbyTime){
			$timedifferenc['years_all']++;
			foreach($_weldbyTime as $__key => $__weldbyTime){
//				$monts_all += cal_days_in_month(CAL_GREGORIAN, $__key, $_key);
				$timedifferenc['monts_all']++;
				foreach($__weldbyTime as $___key => $___weldbyTime){
					$timedifferenc['days_all']++;
				}
			}
		}

		// Hier wird anhand der Anzahl von Jahr, Monat und Tag entschieden, welche Art Diagramm angezeigt wird
		$diagrammdata = 'day';

		if($timedifferenc['days_all'] > 31){
			if($timedifferenc['monts_all'] < 13){
				$diagrammdata = 'month';
			} else {
				$diagrammdata = 'year';
			}
		}

		$testingreports = array_values($testingreports);

		$xml = array();

		$this->Session->write('statistics.monthCount', $month_count);
		$this->Session->write('statistics.reports', Hash::extract($testingreports, '{n}.Reportnumber.id'));

		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'], 'rel'=>'statistics');
		$SettingsArray['printlink'] = array('discription' => __('Print statistics',true), 'controller' => 'statistics', 'download'=>'statistics.pdf', 'action' => 'exportpdf', 'rel'=>'pdf', 'terms' => array());
		$SettingsArray['download'] = array('discription' => __('Export statistics',true), 'controller' => 'statistics', 'download'=>'statistics.csv', 'action' => 'exportcsv', 'class'=>'download', 'rel'=>'csv', 'terms' => array());

		$this->set('SettingsArray', $SettingsArray);
		$this->set('years', $years);
		$this->set('showDailyOverview', count($month_count)==1);
		$this->set('testingreports', $testingreports);
		$this->set('welds', $welds);
		$this->set('welders', $welders);
		$this->set('weldbyTime', $weldbyTime);
		$this->set('welderrors', $welderrors);
		$this->set('months', $months);
		$this->set('searchoutput', $searchoutput);
		$this->set('CountTestreports', $CountTestreports);
		$this->set('diagrammdata', $diagrammdata);
		$this->set('hint', $hint);
*/
	}

	public function errors() {
//		$reports = $this->Session->read('statistics.reports');
//		$reports = array_filter($reports, function($report) {return isset($report['Evaluation']) && !empty($report['Evaluation']);});

		$reports = $this->Session->read('statistics.reports');

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = 0;
		$testingreports = $this->Reportnumber->find('all', array('conditions'=>array('Reportnumber.id'=>$reports)));

		// Für die schneller Zuordnung aller Nahtabschnitte die Prüfberichte mit Reportnumber.id als Index merken
		$testingreports = array_combine(array_map('strval', Hash::extract($testingreports, '{n}.Reportnumber.id')), $testingreports);
		$_methods = array_unique(Hash::extract($testingreports, '{n}.Testingmethod.value'));
		$methods = array();

		foreach($_methods as $_method) {
			$xml = $this->Xml->XmltoArray($_method, 'file', ucfirst($_method));
			// In der Statistik nur Prüfverfahren berücksichtigen, wenn dessen Nahtbereiche ein Ergebnis haben
			if(count($xml->xpath('Report'.ucfirst($_method).'Evaluation/*[key="result"]')) == 0) {
				continue;
			}

			foreach(array('Generally', 'Specific', 'Evaluation') as $part) {
				$rpart = 'Report'.ucfirst($_method).$part;
				$this->loadModel($rpart);

				// Bei Evaluation wird auch nach deleted gefiltert
				$thisconditions =  array($rpart.'.reportnumber_id'=>array_keys($testingreports));
				if($part == 'Evaluation') $thisconditions[$rpart.'.deleted'] = 0;

				$options = array('conditions' => $thisconditions);
				$_part = $this->$rpart->find('all', $options);
				$evals = array();
				if($part == 'Evaluation') {
					$evals = Hash::combine($_part, '{n}.{s}.description','{n}.{s}','{n}.{s}.reportnumber_id');
					foreach(Hash::extract($_part, '{n}.{s}') as $__part) {
						$evals[$__part['reportnumber_id']][$__part['description']]['result'] = max(intval($evals[$__part['reportnumber_id']][$__part['description']]['result']), intval($__part['result']));
						if(isset($__part['error'])) {
							if(!isset($evals[$__part['reportnumber_id']][$__part['description']]['error_array'])) {
								$__part['error'] = str_replace(PHP_EOL, ',', $__part['error']);
								$evals[$__part['reportnumber_id']][$__part['description']]['error_array'] = explode(',', $__part['error']);
							} else {
								$evals[$__part['reportnumber_id']][$__part['description']]['error_array'] = array_unique(array_merge($evals[$__part['reportnumber_id']][$__part['description']]['error_array'], explode(',', $__part['error'])));
							}

							$evals[$__part['reportnumber_id']][$__part['description']]['error'] = join(', ', $evals[$__part['reportnumber_id']][$__part['description']]['error_array'] = array_filter($evals[$__part['reportnumber_id']][$__part['description']]['error_array']));
						}
					}

					foreach(Hash::extract($evals,'{n}.{s}') as $_eval) {
						$testingreports[$_eval['reportnumber_id']][$part][] = $_eval;
					}
				} else {
					foreach($_part as $__part) {
						$__part = reset($__part);
						$testingreports[$__part['reportnumber_id']][$part] = $__part;
					}
				}
			}
		}

		$testingreports = array_values($testingreports);

		$xml = array();
		// Radioauswahlfelder durch richtige Werte ersetzen
		foreach($testingreports as $id=>$report) {
			if(!isset($xml[$report['Testingmethod']['value']])) {
				$xml[$report['Testingmethod']['value']] = $this->Xml->XmltoArray($report['Testingmethod']['value'], 'file', ucfirst($report['Testingmethod']['value']));
			}

			foreach(preg_grep_key('/(Generally|Specific|Evaluation)$/', $report) as $model=>$values) {
				$_model = $model;
				$model = 'Report'.ucfirst($report['Testingmethod']['value']).$model;

				if($_model == 'Evaluation') {
					foreach($values as $eval=>$_values) {
						foreach($_values as $key=>$value) {
							if(isset($xml[$report['Testingmethod']['value']]->$model->$key->radiooption)) {
								$testingreports[$id][$_model][$eval][$key] = trim($xml[$report['Testingmethod']['value']]->$model->$key->radiooption->value[intval($value)]);
							}
						}
					}
				} else {
					foreach($values as $key=>$value) {
						if(isset($xml[$report['Testingmethod']['value']]->$model->$key->radiooption)) {
							$testingreports[$id][$_model][$key] = trim($xml[$report['Testingmethod']['value']]->$model->$key->radiooption->value[intval($value)]);
						}
					}
				}
			}
		}

		$this->request->data['extra'] = array(
			//'reports' => $reports,
			'reports' => $testingreports,
			'width' => $this->request->data['extra']['width'],
			'height' => $this->request->data['extra']['height']
		);

		$this->set('data', $this->request->data);
		$this->layout = 'csv';
	}

	public function diagram($type='overview'){

		$type = $this->request->data['type'];
		if(empty($type)) $type = 'overview';

		$reports = $this->Session->read('statistics.reports');
		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = 0;

		$testingreports = $this->Reportnumber->find('all', array('conditions'=>array('Reportnumber.id'=>$reports)));


		if($type == 'reports'){

			$options = array();

			$options['Reportnumber.id'] = $reports;

			$this->paginate = array(
        		'conditions' => $options,
        		'limit' => 100,
        		'order' => array('Reportnumber.id' => 'asc')
    		);

			$reportnumbers = $this->paginate('Reportnumber');
			$display = $this->Xml->XmltoArray('display', 'file',null);
			$items = $display->xpath('section[url="reportnumbers/show"]/item');

			if(!is_array($items)) $items = array();

			if(empty($items)) {
				$items = json_decode('[
				    {"model": "Testingmethod", "key": "name", "sortable": "1", "description": "Testingmethod", "hidden": "0"},
				    {"model": "Generally", "key": "examination_object", "sortable": "1", "description": "Examination object", "hidden": "0"},
				    {"model": "Testingcomp", "key": "verfahren", "sortable": "1", "description": "Testingcompany", "hidden": "0"},
				    {"model": "User", "key": "name", "sortable": "1", "description": "Username", "hidden": "0"},
				    {"model": "Reportnumber", "key": "modified", "sortable": "1", "description": "modified", "hidden": "0"}
				]');
			}

			$this->set('items', $items);
			$this->set('reportnumbers',$reportnumbers);
			$this->render($type, 'blank');
			return;
		}

		$options['conditions'] = array('Reportnumber.id' => $reports);
		$options['fields'] = array('Reportnumber.testingmethod_id');
		$options['group'] = array('Reportnumber.testingmethod_id');

		$optionTestingmethod = $this->Reportnumber->find('list', $options);
		$_methods = $this->Reportnumber->Testingmethod->find('list',array('fields' => array('id','value'), 'conditions' => array('Testingmethod.id' => $optionTestingmethod)));

		unset($options);

		// Für die schneller Zuordnung aller Nahtabschnitte die Prüfberichte mit Reportnumber.id als Index merken
		$testingreports = array_combine(array_map('strval', Hash::extract($testingreports, '{n}.Reportnumber.id')), $testingreports);
//		$_methods = array_unique(Hash::extract($testingreports, '{n}.Testingmethod.value'));
		$methods = array();

		$output = $this->Data->GetWeldEvaluation($_methods,$testingreports);

		$testingreports = $output['testingreports'];
		$welds = $output['welds'];
		$welderrors = $output['welderrors'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),'11' =>  __('November', true),12 =>  __('December', true));

		$weldbyTime = array();
		$month_count = array();
		$year_count = array();

		$output = $this->Data->GetWeldStatistic($welds);

		$welds = $output['welds'];
		$welders = $output['welders'];
		$weldbyTime = $output['weldbyTime'];
		$year_count = $output['year_count'];
		$month_count = $output['month_count'];

		$timedifferenc['years_all'] = 0;
		$timedifferenc['monts_all'] = 0;
		$timedifferenc['days_all'] = 0;

		foreach($weldbyTime as $_key => $_weldbyTime){
			$timedifferenc['years_all']++;
			foreach($_weldbyTime as $__key => $__weldbyTime){
//				$monts_all += cal_days_in_month(CAL_GREGORIAN, $__key, $_key);
				$timedifferenc['monts_all']++;
				foreach($__weldbyTime as $___key => $___weldbyTime){
					$timedifferenc['days_all']++;
				}
			}
		}

		// Hier wird anhand der Anzahl von Jahr, Monat und Tag entschieden, welche Art Diagramm angezeigt wird
		$diagrammdata = 'day';

		if($timedifferenc['days_all'] > 31){
			if($timedifferenc['monts_all'] < 13){
				$diagrammdata = 'month';
			} else {
				$diagrammdata = 'year';
			}
		}

//		$testingreports = array_values($testingreports);

		$xml = array();
		$this->request->data['extra'] = array(
			'welds' => $welds,
			'welders' => $welders,
			'welderrors' => $welderrors,
			'reports' => $testingreports,
			'timedifferenc' => $timedifferenc,
			'diagrammdata' => $diagrammdata,
			'width' => $this->request->data['extra']['width'],
			'height' => $this->request->data['extra']['height']
		);

 		$this->set('data', $this->request->data);
		$this->autoRender = false;
		$this->render($type, 'jpgraph');
	}

	public function exportpdf() {

		$this->autoRender = false;

		$reports = $this->Session->read('statistics.reports');
		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;

		$testingreports = $this->Reportnumber->find('all', array('conditions'=>array('Reportnumber.id'=>$reports)));

		$options['conditions'] = array('Reportnumber.id' => $reports);
		$options['fields'] = array('Reportnumber.testingmethod_id');
		$options['group'] = array('Reportnumber.testingmethod_id');

		$optionTestingmethod = $this->Reportnumber->find('list', $options);
		$_methods = $this->Reportnumber->Testingmethod->find('list',array('fields' => array('id','value'), 'conditions' => array('Testingmethod.id' => $optionTestingmethod)));

		unset($options);

		// Für die schneller Zuordnung aller Nahtabschnitte die Prüfberichte mit Reportnumber.id als Index merken
		$testingreports = array_combine(array_map('strval', Hash::extract($testingreports, '{n}.Reportnumber.id')), $testingreports);
//		$_methods = array_unique(Hash::extract($testingreports, '{n}.Testingmethod.value'));
		$methods = array();

		$output = $this->Data->GetWeldEvaluation($_methods,$testingreports);

		$testingreports = $output['testingreports'];
		$welds = $output['welds'];
		$welderrors = $output['welderrors'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),'11' =>  __('November', true),12 =>  __('December', true));

		$weldbyTime = array();
		$month_count = array();
		$year_count = array();
/*
		$output = $this->Data->GetWeldEvaluation($_methods,$testingreports);

		$testingreports = $output['testingreports'];
		$welds = $output['welds'];
		$welderrors = $output['welderrors'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),'11' =>  __('November', true),12 =>  __('December', true));

		$weldbyTime = array();
		$month_count = array();
		$year_count = array();
*/
		$output = $this->Data->GetWeldStatistic($welds);

		$welds = $output['welds'];
		$welders = $output['welders'];
		$weldbyTime = $output['weldbyTime'];
		$year_count = $output['year_count'];
		$month_count = $output['month_count'];
		$monts_all = 0;

		// kann wohl weg
		foreach($weldbyTime as $_key => $_weldbyTime){
			foreach($_weldbyTime as $__key => $__weldbyTime){
				$monts_all += cal_days_in_month(CAL_GREGORIAN, $__key, $_key);
			}
		}

		$testingreports = array_values($testingreports);

		$xml = array();

		$month_count = $this->Session->read('statistics.monthCount');

		$testingreports = array_filter($testingreports, function($_report) {return isset($_report['Evaluation']) && !empty($_report['Evaluation']);});

		if($this->Session->check('GetSearchFields')){
			$GetSearchFields = $this->Session->read('GetSearchFields');
			$GetSearchFieldsString = array();
			foreach($GetSearchFields as $_key => $_GetSearchFields){
				$GetSearchFieldsString[] = $_GetSearchFields['field'] .': ' . $_GetSearchFields['value'] . ' ';
			}
			$this->set('GetSearchFieldsString', $GetSearchFieldsString);
		}

		$timedifferenc['years_all'] = 0;
		$timedifferenc['monts_all'] = 0;
		$timedifferenc['days_all'] = 0;

		foreach($weldbyTime as $_key => $_weldbyTime){
			$timedifferenc['years_all']++;
			foreach($_weldbyTime as $__key => $__weldbyTime){
//				$monts_all += cal_days_in_month(CAL_GREGORIAN, $__key, $_key);
				$timedifferenc['monts_all']++;
				foreach($__weldbyTime as $___key => $___weldbyTime){
					$timedifferenc['days_all']++;
				}
			}
		}

		// Hier wird anhand der Anzahl von Jahr, Monat und Tag entschieden, welche Art Diagramm angezeigt wird
		$diagrammdata = 'day';

		if($timedifferenc['days_all'] > 31){
			if($timedifferenc['monts_all'] < 13){
				$diagrammdata = 'month';
			} else {
				$diagrammdata = 'year';
			}
		}

		$setDataArray['extra'] = array(
			'welds' => $welds,
			'welders' => $welders,
			'welderrors' => $welderrors,
			'reports' => $testingreports,
			'monts_all' => $monts_all,
			'timedifferenc' => $timedifferenc,
			'diagrammdata' => $diagrammdata,
			'width' => 1200,
			'height' =>700,
			'months' => $months
		);

		$this->request->data['extra'] = array(
			'welds' => $welds,
			'welders' => $welders,
			'welderrors' => $welderrors,
			'reports' => $testingreports,
			'monts_all' => $monts_all,
			'timedifferenc' => $timedifferenc,
			'diagrammdata' => $diagrammdata,
			'width' => 1200,
			'height' => 700,
			'months' => $months
		);

		$overviewData = null;
		$welderData = null;
		$errorData = null;

		// Monatsüberblick
		$monthView = new View($this);
		$monthView->autoRender = false;
		$monthView->layout=null;

		$monthView->set('return', true);
		$monthView->set('data', $setDataArray);
		$overviewData = $monthView->render('overview');

		if(strpos($overviewData, 'PNG') === false)$overviewData = false;

		// Schweißer
		$welderView = new View($this);
		$welderView->autoRender = false;
		$welderView->layout=null;

		$welderView->set('return', true);
		$welderView->set('data', $setDataArray);
		$welderData = $welderView->render('welders');

		if(strpos($welderData, 'PNG') === false){
			$welderCaption = strip_tags($welderData);
			$welderData = false;
		} else {
			$welderCaption = __('Involved welders', true);
		}

		$errorView = new View($this);
		$errorView->autoRender = false;
		$errorView->layout=null;

		$errorView->set('return', true);
		$errorView->set('data', $setDataArray);

		$errorData = $errorView->render('welderrors');

		if($errorData === "false") $errorData = false;

		$this->set('overviewCaption', count($month_count) > 1 ? __('Welds by months', true) : __('Welds by days', true));
		$this->set('welderCaption', $welderCaption);
		$this->set('errorCaption', __('Weld errors by frequency', true));

		$this->set('overviewData', $overviewData);
		$this->set('welderData', $welderData);
		$this->set('errorData', $errorData);

		// PDF rendern
		$this->set('testingreports', $testingreports);
		$this->set('settings', $this->Xml->XmlToArray('statistics','file',null));

		$this->render('export', 'pdf');
	}

	public function exportcsv() {

		$this->autoRender = false;

		$reports = $this->Session->read('statistics.reports');
		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;

		$testingreports = $this->Reportnumber->find('all', array('conditions'=>array('Reportnumber.id'=>$reports)));

		$options['conditions'] = array('Reportnumber.id' => $reports);
		$options['fields'] = array('Reportnumber.testingmethod_id');
		$options['group'] = array('Reportnumber.testingmethod_id');

		$optionTestingmethod = $this->Reportnumber->find('list', $options);
		$_methods = $this->Reportnumber->Testingmethod->find('list',array('fields' => array('id','value'), 'conditions' => array('Testingmethod.id' => $optionTestingmethod)));

		unset($options);

		// Für die schneller Zuordnung aller Nahtabschnitte die Prüfberichte mit Reportnumber.id als Index merken
		$testingreports = array_combine(array_map('strval', Hash::extract($testingreports, '{n}.Reportnumber.id')), $testingreports);
//		$_methods = array_unique(Hash::extract($testingreports, '{n}.Testingmethod.value'));
		$methods = array();

		$output = $this->Data->GetWeldEvaluation($_methods,$testingreports);

		$testingreports = $output['testingreports'];
		$welds = $output['welds'];
		$welderrors = $output['welderrors'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),'11' =>  __('November', true),12 =>  __('December', true));

		$weldbyTime = array();
		$month_count = array();
		$year_count = array();
/*
		$output = $this->Data->GetWeldEvaluation($_methods,$testingreports);

		$testingreports = $output['testingreports'];
		$welds = $output['welds'];
		$welderrors = $output['welderrors']['all'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),'11' =>  __('November', true),12 =>  __('December', true));

		$weldbyTime = array();
		$month_count = array();
		$year_count = array();
*/
		$output = $this->Data->GetWeldStatistic($welds);

		$welds = $output['welds'];
		$welders = $output['welders'];
		$weldbyTime = $output['weldbyTime'];
		$year_count = $output['year_count'];
		$month_count = $output['month_count'];
		$monts_all = 0;

		foreach($weldbyTime as $_key => $_weldbyTime){
			foreach($_weldbyTime as $__key => $__weldbyTime){
				$monts_all += cal_days_in_month(CAL_GREGORIAN, $__key, $_key);
			}
		}

		$testingreports = array_values($testingreports);

		$xml = array();

		$month_count = $this->Session->read('statistics.monthCount');

//		$testingreports = array_filter($testingreports, function($_report) {return isset($_report['Evaluation']) && !empty($_report['Evaluation']);});


		if($this->Session->check('GetSearchFields')){
			$GetSearchFields = $this->Session->read('GetSearchFields');
			$GetSearchFieldsString = array();
			foreach($GetSearchFields as $_key => $_GetSearchFields){
				$GetSearchFieldsString[] = $_GetSearchFields['field'] .': ' . $_GetSearchFields['value'] . ' ';
			}
		}

		$data = array();
		$data[] = array(__('Suchkriterien',true));

		// Suchbegriffe in das Ausgabearray einfügen
		foreach($GetSearchFieldsString as $_GetSearchFieldsString){
			$data[] = array($_GetSearchFieldsString);
		}

		unset($welds['statistics']['all']['reports']);

		$data[] = array(' ');
		$data[] = array(__('Weld type total', true));
		$data[] = array(__('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true));
		$data[] = $welds['statistics']['all'];
		$data[] = array(' ');
		$data[] = array(__('Month', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true));

		foreach($welds['statistics']['month'] as $_key => $_month){
			$date = explode('.',$_key);
			$month = $date[1];
			$year = $date[0];
			$data[] = array($year . '-' . $month, $_month['all'], $_month['e'], $_month['ne'], $_month['-']);
		}

		$data[] = array(' ');
		$data[] = array(__('Welders', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true), __('Percentage', true));

		foreach($welders as $_key => $_welders) {
			$data[] = array($_key,$_welders['all'],$_welders['e'],$_welders['ne'],$_welders['-'], @round(100 * $_welders['e'] / ($_welders['all'] - $_welders['-']),2));
		}

		$data[] = array(' ');
		$data[] = array(__('Type of error', true), __('Amount (all Welds)', true), __('Amount (NE-Welds)', true));

		foreach($welderrors['all'] as $_key => $chunk){
			$data[] = array($chunk['code'],$chunk['value'],isset($welderrors['ne'][$_key]) ? $welderrors['ne'][$_key]['value'] : 0);
		}

		$data = array_map_recursive('utf8_decode', $data);

		$this->Csv->exportCsv($data,'statistics.csv');
	}

	public function one($id = null) {
		$this->autoRender = false;
		$this->set('testingreports', $testingreports = $this->Session->read('statistics.reports'));
		$this->set('settings', $this->Xml->XmlToArray('statistics','file',null));
		$this->set('data', array('extra'=>array(
			'reports' => $testingreports,
			'width' => 800,
			'height' => 600
		)));
		$this->render('one', 'jpgraph');

//		$this->layout='jpgraph';
//		$this->set('id', $id);
	}

}
