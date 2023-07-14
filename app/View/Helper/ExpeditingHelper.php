<?php
class ExpeditingHelper extends AppHelper {

	var $helpers = array('Html','Form','Ajax','Javascript','Navigation');

	public function showExpeditings($menues) {

	$output = null;
	$CssClass = 'ajax';
	$StatusClass = 'okay';

	$UnfinishedContainer = false;
	$OverContainer = false;
	$OkayContainer = false;
	$NotStartedContainer = false;
	$ErrorContainer = false;

	$linkArray = array();

	$output .= '<ul class="listemax">';

	foreach ($menues as $key => $menue){

		if($menue['data']['status'] == 0) {
			$StatusClass = 'unfinished';
			$UnfinishedContainer = true;
		}
		if($menue['data']['status'] == 1) {
			$StatusClass = 'over';
			$OverContainer = true;
		}
		if($menue['data']['status'] == 2) { 
			$StatusClass = 'okay';
			$OkayContainer = true;
		}
		if($menue['data']['iss'] == 0)	{ 
			$StatusClass = 'notstarted';
			$NotStartedContainer = true;
			$UnfinishedContainer = false;
		}

		if($menue['data']['error'] == 1) $ErrorContainer = true;
		
		if(isset($menue['controller'])){$linkArray['controller'] = $menue['controller'];}		

		if(isset($menue['action'])){$linkArray['action'] = $menue['action'];}		

		if(isset($menue['term'])){$linkArray['term'] = $menue['term'];}		

		if(isset($menue['discription'])){$linkArray['discription'] = $menue['discription'];}		
		else {$linkArray['discription'] = __('No discription');}

		if(isset($menue['class'])){$linkArray['class'] = $menue['class'];}
		else {$linkArray['class'] = $CssClass;}
		
		if(count($linkArray) < 5) continue;		

		$output .= '<li class="' . $StatusClass . '">';

		$output .= $this->Html->link($linkArray['discription'], array_merge(array('controller' => $linkArray['controller'], 'action' => $linkArray['action']),$linkArray['term']),array('class' => $linkArray['class']));

		$output .= '<span class="info">';
		
		$output .= $menue['data']['percent'] . '% - ' . $menue['data']['iss'] . '/' . $menue['data']['soll'];
		$output .= ' - ' . __('endet am',true) . ' ' . $menue['data']['deliver_date'];
		
		$output .= '</span>';

		$output .= '<span class="diagramm" style=" background-color:' . $menue['data']['bg_color_outer'] . '; width:' . $menue['data']['soll'] . 'em">';

		$output .= '<span class="diagramm_inner" style=" background-color:' . $menue['data']['bg_color_inner'] . '; width:' . $menue['data']['iss'] . 'em"></span>';

		$output .= '</span>';

		if($OverContainer == true){
			$output .= '<span class="status status_over" title="' . __('time delay',true) . '"></span>';
		}
		if($OkayContainer == true){
			$output .= '<span class="status status_okay" title="' . __('complete',true) . '"></span>';
		}
		if($NotStartedContainer == true){
			$output .= '<span class="status status_notstarted" title="' . __('not started',true) . '"></span>';
		}
		if($UnfinishedContainer == true){
			$output .= '<span class="status status_unfinished" title="' . __('in progress',true) . '"></span>';
		}
		
		if($ErrorContainer == true){
			$output .= '<span class="status status_error" title="' . __('error',true) . '"></span>';
		}
		
		$output .= '</li>';

		$StatusClass = null;
		$linkArray = array();
		$UnfinishedContainer = false;
		$OverContainer = false;
		$OkayContainer = false;
		$NotStartedContainer = false;
		$ErrorContainer = false;
	}
	
	$output .= '</ul>';

	return $output;
	}

	public function showExpeditingDetails() {

	$output = null;
	$CssClass = 'ajax';
	$StatusClass = 'okay';
	
	$RadioOptions = $this->_View->viewVars['RadioOptions'];
	$menues = $this->_View->viewVars['expeditingContainer'];
	
	$Points = array(0 => '-','1' => __('Witness Point',true),2 => __('Hold Point',true));

	$linkArray = array();

	$output .= $this->Form->create('Expeditings', array('class' => 'dialogform'));
	
	$output .= '<fieldset>';	
	$output .= '<ul class="listemax">';

	foreach ($menues as $key => $menue){

		if($menue['data']['status'] == 0) $StatusClass = 'unfinished';
		if($menue['data']['status'] == 1) $StatusClass = 'defects';
		if($menue['data']['status'] == 2) $StatusClass = 'okay';
		
		if(isset($menue['controller'])){$linkArray['controller'] = $menue['controller'];}		

		if(isset($menue['action'])){$linkArray['action'] = $menue['action'];}		

		if(isset($menue['term'])){$linkArray['term'] = $menue['term'];}		

		if(isset($menue['discription'])){$linkArray['discription'] = $menue['discription'];}		
		else {$linkArray['discription'] = __('No discription');}

		if(isset($menue['class'])){$linkArray['class'] = $menue['class'];}
		else {$linkArray['class'] = $CssClass;}
		
		if(count($linkArray) < 5) continue;		

		$output .= '<li class="' . $StatusClass . '">';

		$output .= $this->Html->link($linkArray['discription'], array_merge(array('controller' => $linkArray['controller'], 'action' => $linkArray['action']),$linkArray['term']),array('class' => $linkArray['class']));
		
		$output .= ' - ' . __('endet am',true) . ' ' . $menue['data']['deliver_date'];
		
		$output .= '<span class="right">';
		
		$output .= $this->Form->input('field_' . $menue['data']['id'], array(
										'legend' => false, 
										'options' => $RadioOptions,
										'type' => 'radio',
										'value' => $menue['data']['status']
										)
									);	

		$output .= $this->Form->input('point_' . $menue['data']['id'], array(
										'legend' => false, 
										'options' => $Points,
										'type' => 'radio',
										'value' => $menue['data']['point']
										)
									);	

		$output .= '</span>';
		$output .= '</li>';

		$linkArray = array();
	}
	
	$output .= '</ul>';
	$output .= '</fieldset>';	
	$output .= $this->Form->end();

	return $output;
	}
}