<?php
class StatisticComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ReformateDateFormating($Data,$StandardXml,$AdditionalXml){

		$StandardModels = array('Testingcomp' => true,'Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true,'Report' => true);

		foreach ($Data['Current'] as $key => $value) {

			if(isset($StandardModels[$key])) continue;

			foreach($Data['Current'][$key] as $_key => $_value){

				if(trim($AdditionalXml->fields->$_key->fieldtype) != 'date') continue;
				if(!is_array($_value)) continue;
				if(count($_value) != 2) continue;
				if(!isset($_value['start'])) continue;
				if(!isset($_value['end'])) continue;

				$Data['Current'][$key][$_key]['start'] = $this->__ReConfigDateFormate($_value['start'],'isotime');
				$Data['Current'][$key][$_key]['end'] = $this->__ReConfigDateFormate($_value['end'],'isotime');

			}
		}

		return $Data;
	}

	public function ReduceToTimerange($Data,$SearchFormData,$AdditionalXml){

		$StandardModels = array('Testingcomp' => true,'Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true,'Report' => true);

		foreach ($SearchFormData['Current'] as $key => $value) {

			if(isset($StandardModels[$key])) continue;
			if(!isset($SearchFormData['Current'][$key])) continue;

			foreach($SearchFormData['Current'][$key] as $_key => $_value){

				if(trim($AdditionalXml->fields->$_key->fieldtype) != 'date') continue;

				if(!is_array($_value)) return $Data;
				if(count($_value) != 2) return $Data;
				if(!isset($_value['start'])) return $Data;
				if(!isset($_value['end'])) return $Data;
				if(!isset($SearchFormData['Current'][$key][$_key])) return $Data;
				if(!isset($SearchFormData['Current'][$key][$_key]['start'])) return $Data;
				if(!isset($SearchFormData['Current'][$key][$_key]['end'])) return $Data;
				if(empty($SearchFormData['Current'][$key][$_key]['start'])) return $Data;
				if(empty($SearchFormData['Current'][$key][$_key]['end'])) return $Data;

				break;
			}
		}

		if(!isset($_value['start'])) return $Data;
		if(!isset($_value['end'])) return $Data;

		foreach ($Data as $key => $value) {

			if($key > $_value['end']) unset($Data[$key]);
			if($key < $_value['start']) unset($Data[$key]);

		}

		return $Data;
	}

	public function EvaluationCorrection($Reportnumber,$SearchFormData,$Model){

		/*
		* Evaluationdaten die in der SearchingValue-Tabelle gespeichert sind,
		* können nur über die Reportnumber, nicht aber über die EvaluationID, angesprochen werden,
		* hierdurch tauchen in der Statistik mit unter falsche Werte auf
		* z.B. bei zwei Schweißern in einem Prüfbericht
		* diese ungültigen Reportnummern werden hier entfernt
		*/

		if(!isset($SearchFormData['Current']['Evaluation'])) return $Reportnumber;
		if(count($SearchFormData['Current']['Evaluation']) == 0) return $Reportnumber;
		if(count($SearchFormData['Result']['Reports']) == 0) return $Reportnumber;
		if(count($Reportnumber) == 0) return $Reportnumber;

		$EvaluationReportnumbers = array();

		foreach($SearchFormData['Current']['Evaluation'] as $_key => $_data){

			$Searching = $this->_controller->Searching->find('first',array('conditions' => array('Searching.id' => $_data)));

			if(count($Searching) == 0) continue;

			$EvaluationReportnumbers = array();

			if(empty($this->_controller->$Model->schema($Searching['Searching']['field']))) continue;

			$EvaluatinOptions['conditions']['reportnumber_id'] = $Reportnumber;
			$EvaluatinOptions['conditions'][$Searching['Searching']['field']] = $Searching['Searching']['value'];
			$EvaluatinOptions['conditions']['deleted'] = 0;
			$EvaluatinOptions['fields'] = array('reportnumber_id');
			$EvaluationIDs = $this->_controller->$Model->find('list',$EvaluatinOptions);

			if(count($EvaluationIDs) > 0) $Reportnumber = array_intersect($Reportnumber,$EvaluationIDs);

			$EvaluationReportnumbers = array_merge($EvaluationReportnumbers, $EvaluationIDs);
		}

		return $Reportnumber;
	}

	public function DateDiff($str_interval,$dt_menor,$dt_maior,$relative=false){

		$MonthsCount = array(1,2,3,4,5,6,7,8,9,10,11,12);

		$Output = 0;

		$StartYear = intval(CakeTime::format($dt_menor, '%Y'));
		$StartMonth = intval(CakeTime::format($dt_menor, '%m'));
		$EndMonth = intval(CakeTime::format($dt_maior, '%m'));
		$EndYear = intval(CakeTime::format($dt_maior, '%Y'));


		foreach($MonthsCount as $_key => $_data){

			if($Output == 0 && $_data != $StartMonth) continue;
			if($_data > $EndMonth && $StartYear == $EndYear) break;
			if($_data == 12 && $StartYear < $EndYear){
				$Output = $this->DateDiffRekursiv($Output,++$StartYear.'-01-01 00:00:00',$dt_maior);
				break;
			};
			++$Output;
		}

		return $Output;
	}

	public function DateDiffRekursiv($Output,$dt_menor,$dt_maior){

		$MonthsCount = array(1,2,3,4,5,6,7,8,9,10,11,12);

		$StartYear = intval(CakeTime::format($dt_menor, '%Y'));
		$StartMonth = intval(CakeTime::format($dt_menor, '%m'));
		$EndMonth = intval(CakeTime::format($dt_maior, '%m'));
		$EndYear = intval(CakeTime::format($dt_maior, '%Y'));

		foreach($MonthsCount as $_key => $_data){

			if($Output == 0 && $_data != $StartMonth) continue;
			if($_data > $EndMonth && $StartYear == $EndYear) break;
			if($_data == 12 && $StartYear < $EndYear){
				++$Output;
				$Output = $this->DateDiffRekursiv($Output,++$StartYear.'-01-01 00:00:00',$dt_maior);
				break;
			};
			++$Output;
		}
		return $Output;
	}


	public function ListDayDiff($DtMenor,$DtMaior){

		$StartDate = intval(CakeTime::format($DtMenor, '%d'));
		$EndDate = intval(CakeTime::format($DtMaior, '%d'));

		$Month = intval(CakeTime::format($DtMenor, '%m'));
		$Year = intval(CakeTime::format($DtMenor, '%Y'));

		for($count = $StartDate; $count <= $EndDate; $count++){
			$DayList[$count]['Start'] = $Year . '-' . $Month . '-' . $count . ' 00:00:00';
			$DayList[$count]['End'] = $Year . '-' . $Month . '-' . $count . ' 23:59:59';
		}

		return $DayList;
	}

	public function ListDateDiff($DivCount,$DtMenor,$DtMaior){

		if($DivCount == 0) return array();

		$Year = intval(CakeTime::format($DtMenor, '%Y'));
		$StartMonth = intval(CakeTime::format($DtMenor, '%m'));
		$ThisMonth = $StartMonth;

		$output[$Year][] = array('Year' => $Year,'Month' => $StartMonth);

		for($count = 2; $count <= $DivCount; $count++){

			++$ThisMonth;

			if($ThisMonth > 12){
				$ThisMonth = 1;
				++$Year;
			}

			$output[$Year][] = array('Year' => $Year,'Month' => $ThisMonth);
		}

		return $output;
	}

	public function CollectEvaluations($_key,$__key,$Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData) {

		foreach($Testingmethod as $___key => $___data){

			$Welders = array();
			$EvaluationModel = 'Report' . ucfirst($___data) . 'Evaluation';

			$Reportnumber = $this->EvaluationCorrection($Reportnumber,$SearchFormData,$EvaluationModel);

			$EvaluatinOptions['conditions']['reportnumber_id'] = $Reportnumber;
			$EvaluatinOptions['conditions']['description !='] = '';
			$EvaluatinOptions['conditions']['deleted'] = 0;
			$EvaluatinOptions['conditions']['result'] = 2;
			$EvaluationIDs = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			unset($EvaluatinOptions);

			$EvaluatinOptions['conditions']['id'] = $EvaluationIDs;
			$EvaluatinOptions['fields'] = array('description','id','reportnumber_id',);
			$Evaluation = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			$CountEvaluation = 0;

			foreach($Evaluation as $____key => $____data){
				$CountEvaluation += count($____data);
			}

			$MonthsList['result'][$TestingmethodName[$___key]]['welder'] = array();
			$MonthsList['result'][$TestingmethodName[$___key]]['ne_count'] = $CountEvaluation;
			$MonthsList['result'][$TestingmethodName[$___key]]['ne'] = $Evaluation;

			if(!empty($this->_controller->$EvaluationModel->schema('welder'))){
				$EvaluatinOptions['fields'] = array('description','welder','reportnumber_id',);
				$Welder = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);
				$Welders['ne'] = $Welder;
			}

			unset($Evaluation);

			$CountEvaluation = 0;

			unset($EvaluatinOptions);

			$EvaluatinOptions['conditions']['reportnumber_id'] = $Reportnumber;
			$EvaluatinOptions['conditions']['description !='] = '';
			$EvaluatinOptions['conditions']['deleted'] = 0;
			$EvaluatinOptions['conditions']['result'] = 1;
			$EvaluationIDs = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			unset($EvaluatinOptions);

//pr($Reportnumber);
//pr($EvaluationIDs);
			$EvaluatinOptions['conditions']['id'] = $EvaluationIDs;
			$EvaluatinOptions['fields'] = array('description','id','reportnumber_id',);
			$Evaluation = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			$CountEvaluation = 0;

			foreach($Evaluation as $____key => $____data){
				$CountEvaluation += count($____data);
			}
			$MonthsList['result'][$TestingmethodName[$___key]]['e_count'] = $CountEvaluation;
			$MonthsList['result'][$TestingmethodName[$___key]]['e'] = $Evaluation;

			if(!empty($this->_controller->$EvaluationModel->schema('welder'))){
				$EvaluatinOptions['fields'] = array('description','welder','reportnumber_id',);
				$Welder = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);
				$Welders['e'] = $Welder;
			}

			unset($Evaluation);

			$CountEvaluation = 0;

			unset($EvaluatinOptions);

			$EvaluatinOptions['conditions']['reportnumber_id'] = $Reportnumber;
			$EvaluatinOptions['conditions']['description !='] = '';
			$EvaluatinOptions['conditions']['deleted'] = 0;
			$EvaluatinOptions['conditions']['result'] = 0;
			$EvaluationIDs = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			unset($EvaluatinOptions);

			$EvaluatinOptions['conditions']['id'] = $EvaluationIDs;
			$EvaluatinOptions['fields'] = array('description','id','reportnumber_id',);
			$Evaluation = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			$CountEvaluation = 0;

			foreach($Evaluation as $____key => $____data){
				$CountEvaluation += count($____data);
			}

			$MonthsList['result'][$TestingmethodName[$___key]]['non_count'] = $CountEvaluation;
			$MonthsList['result'][$TestingmethodName[$___key]]['non'] = $Evaluation;

			if(!empty($this->_controller->$EvaluationModel->schema('welder'))){
				$EvaluatinOptions['fields'] = array('description','welder','reportnumber_id',);
				$Welder = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);
				$Welders['non'] = $Welder;
			}

			unset($EvaluatinOptions);

			$CountEvaluation = 0;

			if(isset($Welders['ne']) && count($Welders['ne']) > 0){
				foreach($Welders['ne'] as $____key => $____data){
					foreach($____data as $_____key => $_____data){
						if(isset($Welders['e'][$____key][$_____key])) unset($Welders['e'][$____key][$_____key]);
					}
				}

				if(isset($Welders['e'][$____key]) && count($Welders['e'][$____key]) == 0) unset($Welders['e'][$____key]);
			}

			foreach($MonthsList['result'][$TestingmethodName[$___key]]['ne'] as $____key => $____data){
				foreach($____data as $_____key => $_____data){
					if(isset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key][$_____key])){
						--$MonthsList['result'][$TestingmethodName[$___key]]['e_count'];
						unset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key][$_____key]);
					}
					if(isset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key]) && count($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key]) == 0) unset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key]);
				}
			}

			foreach($MonthsList['result'][$TestingmethodName[$___key]]['non'] as $____key => $____data){
				foreach($____data as $_____key => $_____data){
					if(isset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key][$_____key])){
						--$MonthsList['result'][$TestingmethodName[$___key]]['e_count'];
						unset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key][$_____key]);
					}
					if(isset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key]) && count($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key]) == 0) unset($MonthsList['result'][$TestingmethodName[$___key]]['e'][$____key]);
				}
			}

			$MonthsList['result'][$TestingmethodName[$___key]]['welder'] = $Welders;

/*
unset($MonthsList['reportnumber_id']);
unset($MonthsList['result'][$TestingmethodName[$___key]]['ne']);
unset($MonthsList['result'][$TestingmethodName[$___key]]['e']);
unset($MonthsList['result'][$TestingmethodName[$___key]]['non']);
*/
			$MonthsList['result'][$TestingmethodName[$___key]]['all_count'] = $MonthsList['result'][$TestingmethodName[$___key]]['ne_count'] + $MonthsList['result'][$TestingmethodName[$___key]]['e_count'] +$MonthsList['result'][$TestingmethodName[$___key]]['non_count'];

			if(isset($MonthsList['result']['all_testingmethods']['ne_count'])) $MonthsList['result']['all_testingmethods']['ne_count'] += $MonthsList['result'][$TestingmethodName[$___key]]['ne_count'];
			if(isset($MonthsList['result']['all_testingmethods']['e_count'])) $MonthsList['result']['all_testingmethods']['e_count'] += $MonthsList['result'][$TestingmethodName[$___key]]['e_count'];
			if(isset($MonthsList['result']['all_testingmethods']['non_count'])) $MonthsList['result']['all_testingmethods']['non_count'] += $MonthsList['result'][$TestingmethodName[$___key]]['non_count'];
			if(isset($MonthsList['result']['all_testingmethods']['all_count'])) $MonthsList['result']['all_testingmethods']['all_count'] += $MonthsList['result'][$TestingmethodName[$___key]]['all_count'];
//if(isset($Reportnumber[31091])) pr($MonthsList[$_key][$__key]);

			if($MonthsList['result'][$TestingmethodName[$___key]]['all_count'] == 0){
				unset($MonthsList['result'][$TestingmethodName[$___key]]);
			}
		}

		return $MonthsList;

	}


	public function StatisticTable($SearchFormData) {

		if(!is_array($SearchFormData)) return false;

		if(count($SearchFormData['Result']['Reports']) == 0) return false;

		$this->_controller->loadModel('Reportnumber');
		$this->_controller->loadModel('Topproject');

		$Topproject = $this->_controller->Topproject->find('first',array('conditions' => array('Topproject.id' => $SearchFormData['Current']['Topproject']['id'])));
		$Topproject = $this->_controller->Data->BelongsToManySelected($Topproject,'Topproject','Report',array('ReportsTopprojects','report_id','topproject_id'));

		if(count($Topproject['Report']['selected']) == 0) return;

		$TestingmethodIds = array();

		$ShowInDays = false;

		$ReportnumberOptions['conditions']['Reportnumber.status >'] = 0;
		$ReportnumberOptions['conditions']['Reportnumber.id'] = $SearchFormData['Result']['Reports'];

		if(isset($this->_controller->request->data['Reportnumber']['years']) && !empty($this->_controller->request->data['Reportnumber']['years'])){

			$YearFilter = intval($this->_controller->request->data['Reportnumber']['years']);
			$ReportnumberOptions['conditions']['YEAR(Reportnumber.date_of_test)'] = $YearFilter;

		}

		if(isset($this->_controller->request->data['Reportnumber']['months']) && !empty($this->_controller->request->data['Reportnumber']['months'])){

			$MonthFilter = intval($this->_controller->request->data['Reportnumber']['months']);
			$ReportnumberOptions['conditions']['MONTH(Reportnumber.date_of_test)'] = $MonthFilter;
		}

		foreach($Topproject['Report']['selected'] as $_key => $_data){

			$Data['Report']['id'] = $_key;
			$Testingmethod = $this->_controller->Data->BelongsToManySelected($Data,'Report','Testingmethod',array('TestingmethodsReports','testingmethod_id','report_id'));

			foreach($Testingmethod['Testingmethod']['selected'] as $__key => $__data){
				$TestingmethodIds[$__key] = $__data;
			}
		}

		// Zeitspanne eingrenzen
		$this->_controller->Reportnumber->recursive = -1;

//			$SearchFormData['Result']['Reports'] = array_intersect($Reportnumber, $SearchFormData['Result']['Reports']);
		$SearchFormData['Result']['Reports'] = $this->_controller->Reportnumber->find('list',$ReportnumberOptions);

		if(count($SearchFormData['Result']['Reports']) == 0) return;

		$MinReport = $this->_controller->Reportnumber->find('first',array('fields' => array('date_of_test'), 'conditions' => array('Reportnumber.id' => min($SearchFormData['Result']['Reports']))));
		$MaxReport = $this->_controller->Reportnumber->find('first',array('fields' => array('date_of_test'), 'conditions' => array('Reportnumber.id' => max($SearchFormData['Result']['Reports']))));

		$MinReport['Reportnumber']['date_of_test'] =  $this->__ReConfigDateFormate($MinReport['Reportnumber']['date_of_test'],'isotime');
		$MaxReport['Reportnumber']['date_of_test'] =  $this->__ReConfigDateFormate($MaxReport['Reportnumber']['date_of_test'],'isotime');

		if(!isset($YearFilter)) $YearFilter = intval(CakeTime::format($MinReport['Reportnumber']['date_of_test'], '%Y'));
		if(!isset($MonthFilter)) $MonthFilter = intval(CakeTime::format($MinReport['Reportnumber']['date_of_test'], '%m'));

		$CountofMonths = round($this->DateDiff('m',$MinReport['Reportnumber']['date_of_test'],$MaxReport['Reportnumber']['date_of_test'],false));


		$ReportDate['Min'] = explode(' ',$MinReport['Reportnumber']['date_of_test']);
		$ReportDate['Max'] = explode(' ',$MaxReport['Reportnumber']['date_of_test']);

		$DateMin = new DateTime($ReportDate['Min'][0]);
		$DateMax = new DateTime($ReportDate['Max'][0]);
		$BetweenDate = $DateMin->diff($DateMax);

		$TimeRange = $this->__TimeRange($ReportDate['Min'][0],$ReportDate['Max'][0]);

		$Testingmethod = $this->_controller->Testingmethod->find('list',array('fields' => array('id','value'), 'conditions' => array('Testingmethod.id' => $TestingmethodIds)));

		$ReportnumberOptions['fields'] = array('testingmethod_id');
		$ReportnumberOptions['group'] = array('testingmethod_id');

		$TestingmethodContained = $this->_controller->Reportnumber->find('list',$ReportnumberOptions);

		unset($ReportnumberOptions['fields']);
		unset($ReportnumberOptions['group']);

		foreach($Testingmethod as $_key => $_data){
			$EvaluationModel = 'Report' . ucfirst($_data) . 'Evaluation';
			$this->_controller->loadModel($EvaluationModel);
		}

		$TestingmethodName = $Testingmethod;

		if(isset($this->_controller->request->data['Reportnumber']['testingmethod']) && $this->_controller->request->data['Reportnumber']['testingmethod'] != 'all_testingmethods'){
			foreach($Testingmethod as $_key => $_data){
				if($_data != $this->_controller->request->data['Reportnumber']['testingmethod']) unset($Testingmethod[$_key]);
			}
		}

		switch($BetweenDate->days){
			// Tagesanzeige
			case ($BetweenDate->days < 40 && $BetweenDate->y < 2):
			$DataList['Day'] = $this->__StatisticDays($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange);
			break;

			// Monatsanzeige
			case ($BetweenDate->days >= 40 && $BetweenDate->days < 400 && $BetweenDate->y < 2):
			$DataList['Month'] = $this->__StatisticMonths($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange);
			break;

			case ($BetweenDate->days > 40  && $BetweenDate->y == 1):
			$DataList['Month'] = $this->__StatisticMonths($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange);
			break;

			// Jahresanzeige
			case ($BetweenDate->days >= 400  && $BetweenDate->y > 1):
			$DataList['Year'] = array();
			$DataList['Year'] = $this->__StatisticYears($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange);
			break;
		}

		$Testingmethods = $this->_controller->Testingmethod->find('list',array('fields' => array('value','verfahren'), 'conditions' => array('Testingmethod.id' => $TestingmethodContained)));
		$this->_controller->request->Testingmethods = $Testingmethods;

		$Testingmethods['all_testingmethods'] = __('all testingmethods',true);

		$this->_controller->set('Testingmethods',$Testingmethods);
		$this->_controller->set('PeriodoFTime',array(CakeTime::format($MinReport['Reportnumber']['date_of_test'], '%d.%m.%Y'),CakeTime::format($MaxReport['Reportnumber']['date_of_test'], '%d.%m.%Y')));

		return $DataList;

	}

	protected function __CollectEvaluations($_key,$__key,$___key,$Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData) {

		$output = array();

		$output = $this->__CollectEvaluationsAll($Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData);
/*
		if($_key != NULL && $__key != NULL && $___key == NULL) $output = $this->__CollectEvaluationsMonth($Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData);

		if($_key != NULL && $__key == NULL && $___key == NULL) $output = $this->__CollectEvaluationsYear($Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData);
*/
		return $output;
	}

	protected function __FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,$status){

			$evals = array();
			$evals['evalation'] = array();

			if($status == 0) $status_value = 'non';
			if($status == 1) $status_value = 'e';
			if($status == 2) $status_value = 'ne';

			$evals['welder'][$status_value] = array();

			$EvaluatinOptions['conditions']['reportnumber_id'] = $Reportnumber;
			$EvaluatinOptions['conditions']['description !='] = '';
			$EvaluatinOptions['conditions']['deleted'] = 0;
			$EvaluatinOptions['conditions']['result'] = $status;
			$EvaluationIDs = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			if(count($EvaluationIDs) == 0) return $output;

			// checken ob ein anderer Abschnitt dieser Naht
			// ne ist
			if($status != 2){

				foreach ($EvaluationIDs as $key => $value) {

					$Check = $this->_controller->$EvaluationModel->find('first',array('fields' => array('description','reportnumber_id'),'conditions' => array($EvaluationModel . '.id' => $value)));

					$CheckOptions['conditions']['reportnumber_id'] = $Check[$EvaluationModel]['reportnumber_id'];
					$CheckOptions['conditions']['description'] = $Check[$EvaluationModel]['description'];
					$CheckOptions['conditions']['deleted'] = 0;
					$CheckOptions['conditions']['result'] = 2;
					$CheckOptions['fields'] = array('description','position','result');

					$Check = $this->_controller->$EvaluationModel->find('first',$CheckOptions);

					if(count($Check) == 1) unset($EvaluationIDs[$key]);
				}

			}

			unset($EvaluatinOptions);

			$EvaluatinOptions['conditions']['id'] = $EvaluationIDs;
			$EvaluatinOptions['fields'] = array('description','id','reportnumber_id',);
			$Evaluation = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			$CountEvaluation = 0;

			foreach($Evaluation as $____key => $____data){
				$CountEvaluation += count($____data);
			}

			$evals['evalation']['result'][$TestingmethodName[$_key]][$status_value . '_count'] = $CountEvaluation;
			$evals['evalation']['result'][$TestingmethodName[$_key]][$status_value] = $Evaluation;

			if(!empty($this->_controller->$EvaluationModel->schema('welder'))){
				$EvaluatinOptions['fields'] = array('description','welder','reportnumber_id',);
				$Welder = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);
				$evals['welders'][$status_value] = $Welder;
			}

			unset($Evaluation);

			$CountEvaluation = 0;

			unset($EvaluatinOptions);

			if(isset($evals['welders'][$status_value])) $output['result'][$TestingmethodName[$_key]]['welder'][$status_value] = $evals['welders'][$status_value];
			else $output['result'][$TestingmethodName[$_key]]['welder'][$status_value] = array();

			$output['result'][$TestingmethodName[$_key]][$status_value . '_count'] = $evals['evalation']['result'][$TestingmethodName[$_key]][$status_value . '_count'];
			$output['result'][$TestingmethodName[$_key]][$status_value] = $evals['evalation']['result'][$TestingmethodName[$_key]][$status_value];

			return $output;

	}

	protected function __MatchAllTestingmethods($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key){

		$output['result']['all_testingmethods']['e_count'] = 0;
		$output['result']['all_testingmethods']['ne_count'] = 0;
		$output['result']['all_testingmethods']['non_count'] = 0;
		$output['result']['all_testingmethods']['all_count'] = 0;

		foreach($output['result'] as $_key => $_data){

			if($_key == 'all_testingmethods') continue;

			if(isset($_data['e_count'])) $e = $_data['e_count'];
			else $e = 0;
			if(isset($_data['ne_count'])) $ne = $_data['ne_count'];
			else $ne = 0;
			if(isset($_data['non_count'])) $non = $_data['non_count'];
			else $non = 0;

			$output['result']['all_testingmethods']['e_count'] += $e;
			$output['result']['all_testingmethods']['ne_count'] += $ne;
			$output['result']['all_testingmethods']['non_count'] += $non;
			$output['result']['all_testingmethods']['all_count'] += $e + $ne + $non;
		}

		return $output;
	}

	protected function __CollectEvaluationsAll($Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData) {

		$output = array();

		foreach($Testingmethod as $_key => $_data){

			$Welders = array();
			$EvaluationModel = 'Report' . ucfirst($_data) . 'Evaluation';
			$Reportnumber = $this->EvaluationCorrection($Reportnumber,$SearchFormData,$EvaluationModel);

			$output = $this->__FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,2);
			$output = $this->__FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,1);
			$output = $this->__FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,0);


		}

		$output = $this->__MatchAllTestingmethods($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key);

		return $output;
	}

	protected function __CollectEvaluationsMonth($Testingmethod,$TestingmethodName,$Reportnumber,$MonthsList,$SearchFormData) {

		$output = array();

		foreach($Testingmethod as $_key => $_data){

			$Welders = array();
			$EvaluationModel = 'Report' . ucfirst($_data) . 'Evaluation';
			$Reportnumber = $this->EvaluationCorrection($Reportnumber,$SearchFormData,$EvaluationModel);

			$output = $this->__FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,2);
			$output = $this->__FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,1);
			$output = $this->__FetchEvaluation($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,0);


		}

		$output = $this->__MatchAllTestingmethods($output,$EvaluationModel,$TestingmethodName,$Reportnumber,$Welders,$_key,0);

		return $output;

	}

	protected function __StatisticYears($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange){

		$output = array();

		foreach($TimeRange['Calendar'] as $_key => $_data){
			$ReportnumberOptions = array(
							'conditions' => array(
									'Reportnumber.id' => $SearchFormData['Result']['Reports'],
									'Reportnumber.status >' => 0,
									'YEAR(Reportnumber.date_of_test)' => $_key,
								)
							);

			$Reportnumber = $this->_controller->Reportnumber->find('list',$ReportnumberOptions);

			$TimeRange['Calendar']['CalendarEntrys'][$_key]['reportnumber_id'] = $Reportnumber;
			$TimeRange['Calendar']['CalendarEntrys'][$_key]['result']['all_testingmethods']['ne_count'] = 0;
			$TimeRange['Calendar']['CalendarEntrys'][$_key]['result']['all_testingmethods']['e_count'] = 0;
			$TimeRange['Calendar']['CalendarEntrys'][$_key]['result']['all_testingmethods']['non_count'] = 0;
			$TimeRange['Calendar']['CalendarEntrys'][$_key]['result']['all_testingmethods']['all_count'] = 0;

			$TimeRange['Calendar']['CalendarEntrys'][$_key]= $this->__CollectEvaluations($_key,NULL,NULL,$Testingmethod,$TestingmethodName,$Reportnumber,$TimeRange['Calendar']['CalendarEntrys'][$_key],$SearchFormData);
		}

		$this->__SetViewFormVars($TimeRange);

		foreach($TimeRange['Calendar']['CalendarEntrys'] as $_key => $_data) $output[$_key] = $_data['result']['all_testingmethods'];

		return array($TimeRange,$output);

	}

	protected function __StatisticMonths($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange){

		$output = array();

		foreach($TimeRange['Calendar'] as $_key => $_data){
//			pr($_data['CalendarAll']);
			foreach($_data['CalendarEntrys'] as $__key => $__data){

					$ReportnumberOptions = array(
									'conditions' => array(
											'Reportnumber.id' => $SearchFormData['Result']['Reports'],
											'Reportnumber.status >' => 0,
											'YEAR(Reportnumber.date_of_test)' => $_key,
											'MONTH(Reportnumber.date_of_test)' => $__key,
										)
									);
					$Reportnumber = $this->_controller->Reportnumber->find('list',$ReportnumberOptions);

					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key]['reportnumber_id'] = $Reportnumber;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key]['result']['all_testingmethods']['ne_count'] = 0;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key]['result']['all_testingmethods']['e_count'] = 0;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key]['result']['all_testingmethods']['non_count'] = 0;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key]['result']['all_testingmethods']['all_count'] = 0;

					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key] = $this->__CollectEvaluations($_key,$__key,NULL,$Testingmethod,$TestingmethodName,$Reportnumber,$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key],$SearchFormData);

			}
		}

		$this->__SetViewFormVars($TimeRange);

		foreach($TimeRange['Calendar']['CalendarEntrys'] as $_key => $_data){

			foreach($_data as $__key => $__data){
				$output[$_key . '-' . $__key] = $__data['result']['all_testingmethods'];
			}
		}

		return array($TimeRange,$output);
	}

	protected function __StatisticDays($SearchFormData,$Testingmethod,$TestingmethodName,$BetweenDate,$TimeRange){

		$output = array();

		foreach($TimeRange['Calendar'] as $_key => $_data){
//			pr($_data['CalendarAll']);
			foreach($_data['CalendarEntrys'] as $__key => $__data){
				foreach($__data as $___key => $___data){

					$ReportnumberOptions = array(
									'conditions' => array(
											'Reportnumber.id' => $SearchFormData['Result']['Reports'],
											'Reportnumber.status >' => 0,
											'YEAR(Reportnumber.date_of_test)' => $_key,
											'MONTH(Reportnumber.date_of_test)' => $__key,
											'DAY(Reportnumber.date_of_test)' => $___key,
										)
									);

					$Reportnumber = $this->_controller->Reportnumber->find('list',$ReportnumberOptions);

					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key][$___key]['reportnumber_id'] = $Reportnumber;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key][$___key]['result']['all_testingmethods']['ne_count'] = 0;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key][$___key]['result']['all_testingmethods']['e_count'] = 0;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key][$___key]['result']['all_testingmethods']['non_count'] = 0;
					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key][$___key]['result']['all_testingmethods']['all_count'] = 0;

					$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key][$___key] = $this->__CollectEvaluations($_key,$__key,$___key,$Testingmethod,$TestingmethodName,$Reportnumber,$TimeRange['Calendar']['CalendarEntrys'][$_key][$__key],$SearchFormData);

				}
			}
		}

		$this->__SetViewFormVars($TimeRange);

		foreach($TimeRange['Calendar']['CalendarEntrys'] as $_key => $_data){

			foreach($_data as $__key => $__data){
				foreach($__data as $___key => $___data){
					$output[$_key . '-' . $__key . '-' . $___key] = $___data['result']['all_testingmethods'];
				}
			}
		}

		return array($TimeRange,$output);

	}

	protected function __SetViewFormVars($TimeRange) {

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),11 =>  __('November', true),12 =>  __('December', true));

		$Years[0] = '';
		$Months[0] = '';

		foreach($TimeRange['Calendar']['CalendarEntrys'] as $_key => $_data){

			$Years[$_key] = $_key;

			if(isset($_data['result'])) continue;

			foreach($_data as $__key => $__data) $Months[intval($__key)] = $months[intval($__key)];
		}

		if(count($Months) == 1) $Months = array_merge($Months, $months);

		$this->_controller->set('Years',$Years);
		$this->_controller->set('Months',$Months);
	}

	protected function __TimeRange($MinDate,$MaxDate) {

		$output['Calendar'] = array();
		$output['Counts']['year'] = 0;
		$output['Counts']['month'] = 0;

		$MinYear = explode('-',$MinDate);
		$MaxYear = explode('-',$MaxDate);

		for($x = intval($MinYear[0]);; $x++) {

			$CalenderData = $this->__CalendarData($x,$MinDate,$MaxDate);

			$output['Counts']['month'] += $CalenderData['MonthCount'];
			$output['Counts']['Detail'][$x]['count'] = $CalenderData['MonthCount'];
			$output['Counts']['Detail'][$x]['start'] = $CalenderData['MonthStart'];
			$output['Counts']['Detail'][$x]['end'] = $CalenderData['MonthEnd'];
			$output['Calendar'][$x] = $CalenderData;

			if($x == intval($MaxYear[0])) break;
		}

		$output['Counts']['year'] = count($output['Calendar']);

		return $output;
	}

	protected function __ConfigDateFormate($Date,$Format){

		if($Format == 'timestamp'){
			$DateArray = new DateTime($Date);
			$Date = $DateArray->getTimestamp();
			return $Date;
		}

		if(Configure::check('Dateformat') === false) return $Date;

		$Dateformat = Configure::read('Dateformat');
		$Lang = $this->_controller->request->lang;
		if(!is_array($Dateformat)) return $Date;
		if(count($Dateformat) == 0) return $Date;
		if(!isset($Dateformat[$Lang])) return $Date;
		if(!isset($Dateformat[$Lang][$Format])) return $Date;

		$DateArray = new DateTime($Date);
		$NewDate = $DateArray->format($Dateformat[$Lang][$Format]);

		return $NewDate;

	}

	protected function __ReConfigDateFormate($Date,$Format){

		if($Format == 'timestamp'){
			$DateArray = new DateTime($Date);
			$Date = $DateArray->getTimestamp();
			return $Date;
		}

		if(Configure::check('Dateformat') === false) return $Date;

		if($Format == 'isotime'){
			$DateArray = new DateTime($Date);
			$Date = $DateArray->format('Y-m-d');
			return $Date;
		}

		return $Date;

	}

	protected function __CalendarData($Year,$MinDate,$MaxDate) {

		$MinDate = $this->__ReConfigDateFormate($MinDate,'isotime');
		$MaxDate = $this->__ReConfigDateFormate($MaxDate,'isotime');

		$output['CalendarAll'] = array();
		$output['CalendarEntrys'] = array();

		$StopEntryStartPut = true;
		$StopEntryEndPut = false;
		$MonthCount = 0;
		$MonthStart = 0;

		$LeapYear = $this->__CheckLeapYear($Year);

		$Template[1] = array('01' => 31,'02' => 29,'03' => 31,'04' => 30,'05' => 31,'06' => 30,'07' => 31,'08' => 31,'09' => 30,10 => 31,11 => 30,12 => 31);
		$Template[2] = array('01' => 31,'02' => 28,'03' => 31,'04' => 30,'05' => 31,'06' => 30,'07' => 31,'08' => 31,'09' => 30,10 => 31,11 => 30,12 => 31);

		$MinDateArray = explode('-',$MinDate);
		$MinDateYear = 		$MinDateArray[0];
		$MinDateMonth = 	$MinDateArray[1];
		$MinDateYearDay = 	$MinDateArray[2];

		$MaxDateArray = explode('-',$MaxDate);
		$MaxDateYear = 		$MaxDateArray[0];
		$MaxDateMonth = 	$MaxDateArray[1];
		$MaxDateYearDay = 	$MaxDateArray[2];

		foreach($Template[$LeapYear] as $_key => $_data){

			$output['CalendarAll'][$_key] = array();

			if($Year == $MinDateYear && $_key < $MinDateMonth) $StopEntryStartPut = true;
			else $StopEntryStartPut = false;

			if($Year == $MaxDateYear && $_key -1 == $MaxDateMonth) $StopEntryEndPut = true;

			if($StopEntryStartPut === false && $StopEntryEndPut === false) $MonthCount++;

			if($StopEntryStartPut === false && $MonthStart == 0) $MonthStart = $_key;

			for($x = 1; $x <= intval($_data); $x++) {

				if(strlen(strval($x)) == 1){
					$output['CalendarAll'][$_key][strval('0' . $x)] = array();
					if($StopEntryStartPut === false && $StopEntryEndPut === false){
						$output['CalendarEntrys'][$_key][strval('0' . $x)] = array();
					}
				} else {
					$output['CalendarAll'][$_key][strval($x)] = array();
					if($StopEntryStartPut === false && $StopEntryEndPut === false){
						$output['CalendarEntrys'][$_key][strval($x)] = array();
					}
				}
			}
		}

		$output['MonthStart'] = $MonthStart;
		$output['MonthEnd'] = array_key_last($output['CalendarEntrys']);
		$output['MonthCount'] = $MonthCount;

		return $output;
	}

	protected function __CheckLeapYear($Year) {

		if(($Year % 400) == 0 || (($Year % 4) == 0 && ($Year % 100) != 0)){
			$output = 1;
		} else {
			$output = 2;
		}

		return $output;
	}

	public function SearchWelderMistake($Data) {

		if(!is_array($Data)) return false;

		$IncludetTestingmethods = array('rt','rt1');

		$this->_controller->loadModel('ErrorNumber');
		$ErrorNumber = $this->_controller->ErrorNumber->find('list',array('fields' => array('number','text')));
		$ErrorTemplate = array_keys($ErrorNumber);
		$ErrorTemplate = array_flip($ErrorTemplate);

		foreach($ErrorTemplate as $_key => $_data){
			$ErrorTemplate[$_key] = 0;
		}

		$WelderMistakes = array();

		if(isset($Data['Year'])) $WelderMistakes[0] = $this->__WelderMistakeYear($Data,$ErrorTemplate,$ErrorNumber,$IncludetTestingmethods);
		if(isset($Data['Month'])) $WelderMistakes = $this->__WelderMistakeMonth($Data,$ErrorTemplate,$ErrorNumber,$IncludetTestingmethods);
		if(isset($Data['Day'])) $WelderMistakes = $this->__WelderMistakeDay($Data,$ErrorTemplate,$ErrorNumber,$IncludetTestingmethods);

		$OrderBy = $WelderMistakes[key($WelderMistakes)]['Mistakes']['errors'];
		arsort($OrderBy);

		$OrderSortCount = array('label' => array(),'description' => array(),'errors' => array());

		foreach($OrderBy as $_key => $_data){

			if(!isset($WelderMistakes[key($WelderMistakes)]['Mistakes']['label'][$_key])) continue;

			$OrderSortCount['label'][$_key] = $WelderMistakes[key($WelderMistakes)]['Mistakes']['label'][$_key];
			$OrderSortCount['description'][$_key] = $WelderMistakes[key($WelderMistakes)]['Mistakes']['description'][$_key];
			$OrderSortCount['errors'][$_key] = $WelderMistakes[key($WelderMistakes)]['Mistakes']['errors'][$_key];
		}

		unset($WelderMistakes[key($WelderMistakes)]['Mistakes']);

		$WelderMistakes[key($WelderMistakes)]['Mistakes'] = $OrderSortCount;

		return array($WelderMistakes,$Data);

	}

	protected function __WelderMistakeDay($Data,$ErrorTemplate,$ErrorNumber,$IncludetTestingmethods) {

		$ReplaceSigns = array(",","\n","\r");

		$WelderMistakes = array();

		foreach($Data['Day']['Calendar']['CalendarEntrys'] as $_key => $_data){

			$AllErrorNumberByPeriode = array();
			$AllErrorNumberCounter = $ErrorTemplate;


			foreach($_data as $__key => $__data){
				foreach($__data as $___key => $___data){

					$ErrorNumberByPeriode = array();
					$ErrorNumberCounter = $ErrorTemplate;

					$ErrorNumberByPeriode = $this->__CollectWelderMistakes($IncludetTestingmethods,$ErrorNumberCounter,$___data);

					foreach($ErrorNumberByPeriode as $____key => $____data){
						if(!isset($ErrorNumberCounter[$____data])) continue;
						++$ErrorNumberCounter[$____data];
						++$AllErrorNumberCounter[$____data];
					}

					foreach($ErrorNumberCounter as $____key => $____data){
						if($____data > 0){
							$Data[$_key][$__key]['Mistakes']['description'][$____key] = $ErrorNumber[$____key];
							continue;
						}
						unset($ErrorNumberCounter[$___key]);
					}
				}
				$AllErrorNumberCount = array_sum($AllErrorNumberCounter);

				foreach($AllErrorNumberCounter as $__key => $__data){
					if($__data > 0) {
						$WelderMistakes[$_key]['Mistakes']['label'][$__key] = $__key . "\n" . round(100 * $__data / $AllErrorNumberCount,2) . " " . __('Percent',true);
						$WelderMistakes[$_key]['Mistakes']['description'][$__key] = $ErrorNumber[$__key] . ' (' . $__data . ')';
						continue;
					}

					unset($AllErrorNumberCounter[$__key]);
				}
			}
			$WelderMistakes[$_key]['Mistakes']['errors'] = $AllErrorNumberCounter;
		}

		return $WelderMistakes;

	}

	protected function __WelderMistakeMonth($Data,$ErrorTemplate,$ErrorNumber,$IncludetTestingmethods) {

		$WelderMistakes = array();

		foreach($Data['Month']['Calendar']['CalendarEntrys'] as $_key => $_data){

			$AllErrorNumberByPeriode = array();
			$AllErrorNumberCounter = $ErrorTemplate;

			foreach($_data as $__key => $__data){

				$ErrorNumberByPeriode = array();
				$ErrorNumberCounter = $ErrorTemplate;

				$ErrorNumberByPeriode = $this->__CollectWelderMistakes($IncludetTestingmethods,$ErrorNumberCounter,$__data);

				foreach($ErrorNumberByPeriode as $___key => $___data){
					if(!isset($ErrorNumberCounter[$___data])) continue;
					++$ErrorNumberCounter[$___data];
					++$AllErrorNumberCounter[$___data];
				}

				foreach($ErrorNumberCounter as $___key => $___data){
					if($___data > 0){
						$Data[$_key][$__key]['Mistakes']['description'][$___key] = $ErrorNumber[$___key];
						continue;
					}
					unset($ErrorNumberCounter[$___key]);
				}

				$Data[$_key][$__key]['Mistakes']['errors'] = $ErrorNumberCounter;
			}

			$AllErrorNumberCount = array_sum($AllErrorNumberCounter);

			foreach($AllErrorNumberCounter as $__key => $__data){
				if($__data > 0) {
					$WelderMistakes[$_key]['Mistakes']['label'][$__key] = $__key . "\n" . round(100 * $__data / $AllErrorNumberCount,2) . " " . __('Percent',true);
					$WelderMistakes[$_key]['Mistakes']['description'][$__key] = $ErrorNumber[$__key] . ' (' . $__data . ')';
					continue;
				}

				unset($AllErrorNumberCounter[$__key]);
			}

			$WelderMistakes[$_key]['Mistakes']['errors'] = $AllErrorNumberCounter;
		}

		return $WelderMistakes;

	}

	protected function __WelderMistakeYear($Data,$ErrorTemplate,$ErrorNumber,$IncludetTestingmethods) {

		$WelderMistakes = array();

		$AllErrorNumberByPeriode = array();
		$AllErrorNumberCounter = $ErrorTemplate;

		foreach($Data['Year']['Calendar']['CalendarEntrys'] as $__key => $__data){

			$ErrorNumberByPeriode = array();
			$ErrorNumberCounter = $ErrorTemplate;

			$ErrorNumberByPeriode = $this->__CollectWelderMistakes($IncludetTestingmethods,$ErrorNumberCounter,$__data);

			foreach($ErrorNumberByPeriode as $___key => $___data){
				if(!isset($ErrorNumberCounter[$___data])) continue;
				++$ErrorNumberCounter[$___data];
				++$AllErrorNumberCounter[$___data];
			}

			foreach($ErrorNumberCounter as $___key => $___data){
				if($___data > 0){
					$Data[$__key]['Mistakes']['description'][$___key] = $ErrorNumber[$___key];
					continue;
				}
				unset($ErrorNumberCounter[$___key]);
			}

			$Data[$__key]['Mistakes']['errors'] = $ErrorNumberCounter;

			$AllErrorNumberCount = array_sum($AllErrorNumberCounter);

			foreach($AllErrorNumberCounter as $___key => $___data){
				if($___data > 0) {
					$WelderMistakes['Mistakes']['label'][$___key] = $___key . "\n" . round(100 * $___data / $AllErrorNumberCount,2) . " " . __('Percent',true);
					$WelderMistakes['Mistakes']['description'][$___key] = $ErrorNumber[$___key] . ' (' . $___data . ')';
					continue;
				}
				unset($AllErrorNumberCounter[$__key]);
			}
		}

		$WelderMistakes['Mistakes']['errors'] = $AllErrorNumberCounter;

		return $WelderMistakes;

	}

	protected function __CollectWelderMistakes($IncludetTestingmethods,$ErrorNumberCounter,$data){

		$ErrorNumberByPeriode = array();
		$ReplaceSigns = array(",","\n","\r");

		foreach($IncludetTestingmethods  as $___key => $___data){

			if(!isset($data['result'][$___data])) continue;
			if(!isset($data['result'][$___data]['ne'])) continue;
			if(count(array_keys($data['result'][$___data]['ne'])) == 0) continue;

			$Reportnumber = array_keys($data['result'][$___data]['ne']);
			$EvaluationModel = 'Report' . ucfirst($___data) . 'Evaluation';
			$EvaluatinOptions['conditions']['reportnumber_id'] = $Reportnumber;
			$EvaluatinOptions['conditions']['error !='] = '';
			$EvaluatinOptions['fields'] = array('error');
			$Evaluations = $this->_controller->$EvaluationModel->find('list',$EvaluatinOptions);

			if(count($Evaluations) > 0){
				foreach($Evaluations as $____key => $____data){

					$____data = str_replace($ReplaceSigns, ' ', $____data);
					$____data = trim(preg_replace('/\s+/', ' ', $____data));

					$MistakeArray = explode(' ',$____data);

					if(count($MistakeArray) > 0){
						foreach($MistakeArray as $_____key => $_____data){
						array_push($ErrorNumberByPeriode,$_____data);
						}
					}
				}
			}
		}

		return $ErrorNumberByPeriode;
	}

	protected function __ConvertWelderData($Data) {

		$output = array();

		switch (key($Data)){
			case 'Month':
			foreach($Data['Month']['Calendar']['CalendarEntrys'] as $_key => $_data){
				foreach($_data as $__key => $__data) $output[] = $__data;
			}
			break;

			case 'Day':
			foreach($Data['Day']['Calendar']['CalendarEntrys'] as $_key => $_data){
				foreach($_data as $__key => $__data){
					foreach($__data as $___key => $___data) $output[] = $___data;
				}
			}
			break;
		}

		return $output;
	}

	public function WelderOverview($Data,$SearchFormData) {

		if(!is_array($Data)) return false;
		if(!isset($SearchFormData['Current']['Generally']['welding_company'])) return false;
		if($SearchFormData['Current']['Generally']['welding_company'] == 0) return false;
		if(!isset($this->_controller->request->Testingmethods)) return false;

		$Welders = array();

		$Data = $this->__ConvertWelderData($Data);

			foreach($Data as $__key => $__data){
				if(!isset($__data['result'])) continue;
				foreach($__data['result'] as $___key => $___data){
					if($___key == 'all_testingmethods') continue;
					if(isset($___data['welder'])){
						foreach($___data['welder'] as $____key => $____data){
							if(isset($____data) && count($____data) > 0){
								foreach($____data as $_____key => $_____data){
									foreach($_____data as $______key => $______data){
										if(!isset($Welders[$______data])){
											$Welders[$______data]['e'] = 0;
											$Welders[$______data]['ne'] = 0;
											$Welders[$______data]['non'] = 0;
										}
										if(isset($___data['e'][$_____key][$______key])) ++$Welders[$______data]['e'];
										if(isset($___data['ne'][$_____key][$______key])) ++$Welders[$______data]['ne'];
										if(isset($___data['non'][$_____key][$______key])) ++$Welders[$______data]['non'];
									}
								}
							}
						}
					}
				}
			}
		return $Welders;
	}
}
