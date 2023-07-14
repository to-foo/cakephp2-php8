<?php echo $this->element('js/ajax_send_modal_form');?>
<?php echo $this->element('js/form_button_set');?>

<div class="modalarea detail">
<h2><?php echo __('Mass actions') . ' ' . $this->Pdf->ConstructReportName($reportnumber)?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
  echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<?php
if(isset($this->request->data['Reportnumber']['action']) && $this->request->data['Reportnumber']['MassSelect'] == 'sign'){
  echo $this->element('reports/sign_master_mass_action');
  return;
}
?>
<?php echo $this->element('reports/ask_mass_action');?>
</div>
