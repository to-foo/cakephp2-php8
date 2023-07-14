<?php
$ReportEvaluation = 'Report' . ucfirst($reportnumber['Testingmethod']['value']) . 'Evaluation';

if($reportnumber['Reportnumber']['delete'] != 1){
  $colors = array();
  $title = array();
  $title[] = __('Testing areas');
  if(Configure::read('WeldManager.color')) {
    if(isset($reportnumber['weld_types'][0]) && $reportnumber['weld_types'][0] == 1){
      $colors[] = 'unevaled_welds';
      $title[] = __('Unevaled welds',true);
    }
    if(isset($reportnumber['weld_types'][1]) && $reportnumber['weld_types'][1] == 1){
      $colors[] = 'evaled_welds';
      $title[] = __('Evaled welds',true);
    }
    if(isset($reportnumber['weld_types'][2]) && $reportnumber['weld_types'][2] == 1){
      $colors[] = 'declined_welds';
      $title[] = __('Defect welds',true);
    }
  }
  if(Configure::read('WeldManager.show')) {
    if(!isset($xml[$reportnumber['Testingmethod']['value']])) { CakeLog::write('missing-testingmethod', print_r($reportnumber['Reportnumber']['id'], true)); }
    else {
      if(!Configure::read('WeldManager.hideUseless') || !empty($xml[$reportnumber['Testingmethod']['value']]['settings']->{$ReportEvaluation}->id)) {
        echo $this->Navigation->makeLink('reportnumbers','testingAreas',$title,'round_white modal'.(!empty($colors) ? ' '.join(' ',$colors) : null),null,$this->request->projectvars['VarsArray']);
      }
    }
  }
} else {
  echo $this->Navigation->makeLink('reportnumbers','history',__('History'),'round modal',null,$this->request->projectvars['VarsArray']);
}
?>
