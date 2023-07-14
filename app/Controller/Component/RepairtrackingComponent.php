<?php
class RepairtrackingComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	/*
	Felder die beim Reparaturbericht nicht dupliziert werden sollen
	die Spaltennamen sollten später aus der XML kommen
	*/
	public function RemoveNoDuplicateFields($reportnumber) {

		if(!is_array($reportnumber)) return array();

		$NoDuplicateFields = array(
			'Generally' => array(
				'examiner',
				'examiner_certificate_no',
				'supervision',
				'supervisor_date',
				'supervisor_certificate_no',
				'supervisor_company_date',
				'supervisor_company',
				'controller_certificate_no',
				'third_part_date',
				'third_part_certificate_no',
				'third_part',
			)
		);

		$verfahren = $this->_controller->request->verfahren;
	 	$Verfahren = $this->_controller->request->Verfahren;
	 	$ReportTestingMethod = 'Report'.$Verfahren;

		foreach ($NoDuplicateFields as $key => $value) {

			$Model = $ReportTestingMethod . $key;

			if(!isset($reportnumber[$Model])) continue;
			if(count($reportnumber[$Model]) == 0) continue;

			foreach ($value as $_key => $_value) {

				if(!isset($reportnumber[$Model][$_value])) continue;
				unset($reportnumber[$Model][$_value]);
			}

		}
		return $reportnumber;
	}

	// Geht nach Componente Reprts
	public function GetRepairReport($reportnumber) {

	 	$verfahren = $this->_controller->request->verfahren;
	 	$Verfahren = $this->_controller->request->Verfahren;
	 	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';

		$reportnumber['Repairs']['Statistic']['count'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['progress'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['open'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['success'] = 0;
		$reportnumber['Repairs']['Statistic']['status']['error'] = 0;

		$this->_controller->request->Model = $ReportEvaluation;

		$this->_controller->loadModel('Repair');

		if(!isset($reportnumber[$ReportEvaluation])) return $reportnumber;

		foreach($reportnumber[$ReportEvaluation] as $_key => $_data){

			if($_data[$ReportEvaluation]['deleted'] == 1) continue;

			$optionsRepair = array('conditions' => 
				array(
					'Repair.reportnumber_id' => $reportnumber['Reportnumber']['id'],
					'Repair.evaluation_id_mistake' => $_data[$ReportEvaluation]['id']
				)
			);

			$Repair = $this->_controller->Repair->find('first',$optionsRepair);

			if(count($Repair) == 0 && $_data[$ReportEvaluation]['result'] != 2) continue;

			if(count($Repair) == 0 && $_data[$ReportEvaluation]['result'] == 2){

				++$reportnumber['Repairs']['Statistic']['count'];
				++$reportnumber['Repairs']['Statistic']['status']['open'];
				$reportnumber['Repairs'][$_key]['mistake'] = $_data;
				$reportnumber['Repairs'][$_key]['class'] = 'open';

				continue;
			}

			++$reportnumber['Repairs']['Statistic']['count'];

			$optionsRepairreport = array('conditions' => array('Reportnumber.id' => $Repair['Repair']['reportnumber_id']));
			$RepairReport = $this->_controller->Reportnumber->find('first',$optionsRepairreport);

			if(count($RepairReport) == 0) continue;

			if($RepairReport['Reportnumber']['status'] < 2){

				$reportnumber['Repairs'][$_key]['history'] = array();
				$reportnumber['Repairs'][$_key]['mistake'] = $_data;
				$reportnumber['Repairs'][$_key]['class'] = 'progress';


				$optionsEvaluation = array(
										'conditions' => array(
											$ReportEvaluation.'.id' => $Repair['Repair']['evaluation_id'],
											$ReportEvaluation.'.deleted' => 0
											)
										);

										$Evaluation = $this->_controller->$ReportEvaluation->find('first',$optionsEvaluation);

				$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];

				if($Evaluation[$ReportEvaluation]['result'] == 0){
					$repair_class = 'progress';
					$repair_message = __('Repair report created and in progress.',true);
					++$reportnumber['Repairs']['Statistic']['status']['progress'];
				}
				if($Evaluation[$ReportEvaluation]['result'] == 1){
					$repair_class = 'progress';
					$repair_message = __('Repair report created and in progress, the report is not closed.',true);
					++$reportnumber['Repairs']['Statistic']['status']['progress'];
				}
				if($Evaluation[$ReportEvaluation]['result'] == 2){
					$repair_class = 'progress';
					$repair_message = __('Repair report created and in progress, the report is not closed.',true);
					++$reportnumber['Repairs']['Statistic']['status']['progress'];
				}

				$Evaluation[$ReportEvaluation]['class_for_repair_view'] = $repair_class;
				$Evaluation[$ReportEvaluation]['message_for_repair_view'] = $repair_message;

				$reportnumber['Repairs'][$_key]['class'] = $repair_class;
				$reportnumber['Repairs'][$_key]['history'][] = $Evaluation;

				continue;
			}

			$optionsEvaluation = array(
									'conditions' => array(
										$ReportEvaluation.'.id' => $Repair['Repair']['evaluation_id'],
										$ReportEvaluation.'.deleted' => 0
									)
								);

			$Evaluation = $this->_controller->$ReportEvaluation->find('first',$optionsEvaluation);

			if(count($Evaluation) == 0) continue;

			$reportnumber['Repairs'][$_key]['history'] = array();
			$reportnumber['Repairs'][$_key]['mistake'] = $_data;

			if($Evaluation[$ReportEvaluation]['result'] == 0){
				$reportnumber['Repairs'][$_key]['class'] = 'open';
				$repair_message = __('No repair reports created.',true);
				++$reportnumber['Repairs']['Statistic']['status']['open'];
			}
			if($Evaluation[$ReportEvaluation]['result'] == 1){
				$reportnumber['Repairs'][$_key]['class'] = 'success';
				$repair_message = __('Repair completed successfully.',true);
				++$reportnumber['Repairs']['Statistic']['status']['success'];
			}
			if($Evaluation[$ReportEvaluation]['result'] == 2){
				$reportnumber['Repairs'][$_key]['class'] = 'error';
				$repair_message = __('Repair failed.',true);
				++$reportnumber['Repairs']['Statistic']['status']['error'];
			}

			$Evaluation[$ReportEvaluation]['class_for_repair_view'] = $reportnumber['Repairs'][$_key]['class'];
			$Evaluation[$ReportEvaluation]['message_for_repair_view'] = $repair_message;

			$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];

			if($Evaluation[$ReportEvaluation]['result'] == 2){

				$Output['evaluation'] = array();

				$reportnumber = $this->SearchRepairEvaluations($reportnumber,$_key,$Evaluation,$ReportEvaluation,$Output,false);

			} else {
				array_push($reportnumber['Repairs'][$_key]['history'],$Evaluation);
			}
		}

		return $reportnumber;
	}

	public function CheckIsRepairReport($reportnumber) {

		if(!is_array($reportnumber)) return false;
		if($reportnumber['Reportnumber']['repair_for'] == 0) return $reportnumber;

		$reportnumber['RepairReport'] = true;
		$reportnumber['RepairReportStatus'] = 1;

		$Model = 'Report' . ucfirst($reportnumber['Testingmethod']['value']) . 'Evaluation';

		if(!ClassRegistry::isKeySet($Model)) $this->_controller->loadModel($Model);
		if(!ClassRegistry::isKeySet('Repair')) $this->_controller->loadModel('Repair');

		$Mistakes = $this->_controller->$Model->find('all',array('conditions' => array('reportnumber_id' => $reportnumber['Reportnumber']['id'])));

		if(count($Mistakes) == 0) return $reportnumber;

		$reportnumber['MistakeThis'] = $Mistakes;

		$MistakeReport = $this->_controller->Repair->find('first',array('conditions' => array('Repair.reportnumber_id' => $reportnumber['Reportnumber']['id'])));

		if(count($MistakeReport) == 0) return $reportnumber;

		$MistakeEvaluations = $this->_controller->Repair->find('all',array('conditions' => array('Repair.reportnumber_id_mistake' => $MistakeReport['Repair']['reportnumber_id_mistake'])));

		if(count($MistakeEvaluations) == 0) return $reportnumber;

		$ResultOverview = array(0 => 0, 1 => 0, 2 => 0, 3 => count($Mistakes));

		$xxx = Hash::extract($Mistakes, '{n}.'.$Model.'.evaluation_id');

		foreach($Mistakes as $_key => $_data){

			$MistakeEvaluationsOfThis = $this->_controller->Repair->find('first',array('conditions' => array('Repair.evaluation_id' => $_data[$Model]['id'])));

			++$ResultOverview[$_data[$Model]['result']];

			if(count($MistakeEvaluationsOfThis) > 0) break;

		}

		$RepairReport = $this->_controller->Reportnumber->find('first',array('conditions' =>array('Reportnumber.id' => $MistakeEvaluationsOfThis['Repair']['reportnumber_id_mistake'])));
		$reportnumber['ResultOverview'] = $ResultOverview;
		$reportnumber['MistakeReport'] = $RepairReport;

		unset($MistakeReport);
		unset($Mistakes);

		return $reportnumber;

	}

	public function QuickCheckRepairStatus($reportnumber) {

		if(!is_array($reportnumber)) return false;
		if(!isset($reportnumber['Reportnumber']['result'])) return $reportnumber;
		if($reportnumber['Reportnumber']['result'] != 2) return $reportnumber;

		$Model = 'Report' . ucfirst($reportnumber['Testingmethod']['value']) . 'Evaluation';
		if(!ClassRegistry::isKeySet($Model)) $this->_controller->loadModel($Model);
		if(!ClassRegistry::isKeySet('Repair')) $this->_controller->loadModel('Repair');

		$Mistakes = $this->_controller->$Model->find('all',array('conditions' => array('result' => 2,'reportnumber_id' => $reportnumber['Reportnumber']['id'])));
		if(count($Mistakes) == 0) return $reportnumber;

		$reportnumber['RepairReporting'] = true;

		$MistakeReport = $this->_controller->Repair->find('first',array('conditions' => array('Repair.reportnumber_id_mistake' => $reportnumber['Reportnumber']['id'])));
/*
pr($reportnumber['Reportnumber']);
pr($MistakeReport);
pr('-----');
*/
		if(count($MistakeReport) == 0){
			$reportnumber['Repair']['repair_status'] = 0;
			return $reportnumber;
		}

		$MistakeEvaluations = $this->_controller->Repair->find('all',array('conditions' => array('Repair.reportnumber_id_mistake' => $MistakeReport['Repair']['reportnumber_id_mistake'])));
		if(count($MistakeEvaluations) == 0) return $reportnumber;

		$MistakeReport['MistakeInRepair'] = $MistakeEvaluations;
		$MistakeReport['Mistake'] = $Mistakes;

		$reportnumber = array_merge($reportnumber,$MistakeReport);

		unset($MistakeReport);
		unset($MistakeEvaluations);
		unset($Mistakes);

		if(count($reportnumber['MistakeInRepair']) < count($reportnumber['Mistake'])){
			$reportnumber['Repair']['repair_status'] = 0;
			return $reportnumber;
		}
		if(count($reportnumber['MistakeInRepair']) == count($reportnumber['Mistake'])){

			foreach($reportnumber['MistakeInRepair'] as $_key => $_data){

				$RepairEvaluation = $this->_controller->$Model->find('first',array('conditions' => array($Model.'.id' => $_data['Repair']['evaluation_id'])));
				$RepairReport = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $RepairEvaluation[$Model]['reportnumber_id'])));

				if(isset($RepairReport['Reportnumber']) && $RepairReport['Reportnumber']['status'] == 0){
					$reportnumber['Repair']['repair_status'] = 0;
					return $reportnumber;
				}

				if($RepairEvaluation[$Model]['result'] == 2){
					$reportnumber['Repair']['repair_status'] = 2;
					$reportnumber = $this->__RepairReportsRecursive($reportnumber,$_data,$Model);
					return $reportnumber;
				}

			}
		}

		$reportnumber['Repair']['repair_status'] = 1;

		return $reportnumber;
	}

	protected function __RepairReportsRecursive($reportnumber,$data,$Model){

//		unset($reportnumber['Repair']['repair_status']);

		$MistakeEvaluations = $this->_controller->Repair->find('first',array('conditions' => array('Repair.evaluation_id_mistake' => $data['Repair']['evaluation_id'])));
		if(count($MistakeEvaluations) == 0){
			if($reportnumber['Repair']['repair_status'] > 0) return $reportnumber;
			$reportnumber['Repair']['repair_status'] = 0;
			return $reportnumber;
		}

		$RepairEvaluation = $this->_controller->$Model->find('first',array('conditions' => array($Model.'.id' => $MistakeEvaluations['Repair']['evaluation_id'])));
		$RepairReport = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $MistakeEvaluations['Repair']['reportnumber_id'])));

		if($RepairReport['Reportnumber']['status'] == 0){
			$reportnumber['Repair']['repair_status'] = 0;
			return $reportnumber;
		}
		if($RepairEvaluation[$Model]['result'] == 1){
			$reportnumber['Repair']['repair_status'] = 1;
			return $reportnumber;
		}
		if($RepairEvaluation[$Model]['result'] == 2){
			$MistakeReport = $this->_controller->Repair->find('first',array('conditions' => array('Repair.reportnumber_id_mistake' => $RepairReport['Reportnumber']['id'])));
//pr($MistakeReport);
			$reportnumber['Repair']['repair_status'] = 2;
			$reportnumber = $this->__RepairReportsRecursive($reportnumber,$MistakeReport,$Model);
			return $reportnumber;
		}

		return $reportnumber;
	}


	public function SearchRepairEvaluations($reportnumber,$_key,$data,$model,$Output,$repairs) {

		if($repairs == false) $optionsRepair = array('conditions' => array('Repair.evaluation_id_mistake' => $data[$model]['id']));
		elseif(is_array($repairs)) $optionsRepair = array('conditions' => array('Repair.evaluation_id_mistake' => $repairs[$model]['id']));

		array_push($Output['evaluation'],$data);

		$Repair = $this->_controller->Repair->find('first',$optionsRepair);

		if(count($Repair) == 0){
			$Output[$model]['class_for_repair_view'] = 'open';
			$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];
			$reportnumber['Repairs'][$_key]['class'] = 'open';
			$Output['class'] = 'open';
			return $reportnumber;
		}

		$optionsRepairreport = array('conditions' => array('Reportnumber.id' => $Repair['Repair']['reportnumber_id']));
		$RepairReport = $this->_controller->Reportnumber->find('first',$optionsRepairreport);

		if($RepairReport['Reportnumber']['status'] < 2){

			$repair_message = __('Repair report created and in progress',true);

			$optionsEvaluation = array(
									'conditions' => array(
										$model.'.id' => $Repair['Repair']['evaluation_id'],
										$model.'.deleted' => 0
										)
									);

			$Evaluation = $this->_controller->$model->find('first',$optionsEvaluation);
			$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];
			$Evaluation[$model]['class_for_repair_view'] = 'progress';
			$Evaluation[$model]['message_for_repair_view'] = $repair_message;

			$Output['class'] = 'progress';
			$reportnumber['Repairs'][$_key]['class'] = 'progress';
			$data[$model]['class_for_repair_view'] = 'progress';
			array_push($Output['evaluation'],$Evaluation);
			$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];
			return $reportnumber;
		}

		if(count($Repair) == 0){

			$reportnumber['Repairs'][$_key]['class'] = 'open';
			$Output['class'] = 'open';
			return $reportnumber;

		} else {

			$optionsEvaluation = array(
									'conditions' => array(
										$model.'.id' => $Repair['Repair']['evaluation_id'],
										$model.'.deleted' => 0
										)
									);

			$Evaluation = $this->_controller->$model->find('first',$optionsEvaluation);

			$Evaluation['Reportnumber'] = $RepairReport['Reportnumber'];

			if($Evaluation[$model]['result'] == 0){

				$repair_message = __('Repair report created and in progress.',true);

				$Output['class'] = 'open';
				$reportnumber['Repairs'][$_key]['class'] = 'open';
				$Evaluation[$model]['message_for_repair_view'] = $repair_message;
				$Evaluation[$model]['class_for_repair_view'] = 'open';
				array_push($Output['evaluation'],$Evaluation);
				$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];

				return $reportnumber;

			}
			if($Evaluation[$model]['result'] == 1){

				$repair_message = __('Repair completed successfully.',true);

				$Output['class'] = 'success';
				$reportnumber['Repairs'][$_key]['class'] = 'success';
				$Evaluation[$model]['message_for_repair_view'] = $repair_message;
				$Evaluation[$model]['class_for_repair_view'] = 'success';
				array_push($Output['evaluation'],$Evaluation);
				$reportnumber['Repairs'][$_key]['history'] = $Output['evaluation'];

				--$reportnumber['Repairs']['Statistic']['status']['error'];
				++$reportnumber['Repairs']['Statistic']['status']['success'];

				return $reportnumber;

			}
			if($Evaluation[$model]['result'] == 2){

				$repair_message = __('Repair failed.',true);

				$Evaluation['class'] = 'error';
				$reportnumber['Repairs'][$_key]['class'] = 'error';
				$Evaluation[$model]['message_for_repair_view'] = $repair_message;
				$Evaluation[$model]['class_for_repair_view'] = 'error';
				$Evaluation[$model]['message_for_repair_view'] = __('Repair failed',true);

				$reportnumber = $this->SearchRepairEvaluations($reportnumber,$_key,$Evaluation,$model,$Output,null);

				return $reportnumber;
			}
		}
	}
	// wird ersetzt durch Componente Report GetRepairStatus
	public function GetRepairStatus($reportnumber,$Model) {

		foreach($reportnumber['Repairs'] as $_key => $_data){
			if(isset($_data['class'])) $ReportRepairStatus[$_data['class']] = $_data['class'];
		}

		if(isset($ReportRepairStatus['error'])){
			$reportnumber['Repairs']['Statistic']['ReportRepairStatus'] = 'error';
			return $reportnumber;
		}
		if(isset($ReportRepairStatus['open'])){
			$reportnumber['Repairs']['Statistic']['ReportRepairStatus'] = 'open';
			return $reportnumber;
		}
		if(isset($ReportRepairStatus['progress'])){
			$reportnumber['Repairs']['Statistic']['ReportRepairStatus'] = 'progress';
			return $reportnumber;
		}
		if(isset($ReportRepairStatus['success'])){
			$reportnumber['Repairs']['Statistic']['ReportRepairStatus'] = 'success';
			return $reportnumber;
		}

		$reportnumber['Repairs']['Statistic']['ReportRepairStatus'] = 'none';

		return $reportnumber;

	}

// geht nach Componente Reports
	public function MapRepairs($reportnumber,$Model) {

		if(!isset($reportnumber['Repairs'])) return $reportnumber;

		foreach($reportnumber['Repairs'] as $_key => $_data){

//			if($_key == 'Statistic') continue;

			foreach($_data as $__key => $__data){
				if($__key == 'history'){
					$LastElement = end($__data);
					$reportnumber[$Model][$_key][$Model]['class_for_repair_view'] = $LastElement[$Model]['class_for_repair_view'];
					$reportnumber[$Model][$_key][$Model]['RepairReport'] = $LastElement['Reportnumber'];
					unset($LastElement);
				}
			}
		}

		return $reportnumber;
	}


	public function LookForRepairreport($reportnumber) {

	 	if(!Configure::check('RepairManager')) return $reportnumber;
	 	if(Configure::read('RepairManager') == false) return $reportnumber;

		$this->_controller->loadModel('Repair');
		$RepairOptions = array('conditions' => array('Repair.reportnumber_id_mistake' => array($reportnumber['Reportnumber']['id'])));
		$Repair = $this->_controller->Repair->find('first',$RepairOptions);

		if(count($Repair) > 0){
			$reportnumber['Reportnumber']['report_has_repairs'] = 1;
		}

		return $reportnumber;
	}
}
