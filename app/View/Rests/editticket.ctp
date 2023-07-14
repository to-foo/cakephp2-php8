<div class="modalarea tickets form">
<h2>
<?php 
if(isset($this->request->data['Ticket'])){
	echo __('Edit ticket') . ' ' . $this->request->data['Ticket']['id'] . ' - ' . $this->request->data['Ticket']['created']; 
}
?>
</h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($ErrorFormName) && count($ErrorFormName) > 0){
//	echo $this->element('js/reload_container',array('FormName' => $ErrorFormName));
	echo $this->element('js/ajax_stop_loader');
	echo '</div>';
	return;
	}

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

<div class="hint">
<div class=" flex_info">
<?php
echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['technical_place'],'label' => __('Techical Place',true)));
echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['count_welds'],'label' => __('Count Welds',true)));
echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['spools'],'label' => __('Spools',true)));
echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['created'],'label' => __('Created',true)));
?>
</div>

<?php echo $this->element('rest/spool_infos');?>
</div>
<?php echo $this->element('rest/testingreport_infos',array('type' => 'OpenReports'));?>
<div class="hint">
<?php
if(!empty($this->request->data['Ticket']['reportwelds'])){
	if($this->request->projectvars['VarsArray'][16] == 0) $this->request->projectvars['VarsArray'][16] = $this->request->data['Ticket']['id'];
	echo $this->Html->link(__('Create report', true),array_merge(array('action' => 'createreport'),$this->request->projectvars['VarsArray']),array('title' => __('Create report', true),'class' => 'round  mymodal'));
}

echo $this->Html->link(__('Delete ticket', true),array_merge(array('action' => 'deleteticket'),$this->request->projectvars['VarsArray']),array('title' => __('Delete ticket', true),'class' => 'round  mymodal'));

?>
</div>
<?php echo $this->Form->create('Ticket', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Ticket','testingmethods' => false));?>
</fieldset>


	<?php
	echo '<fieldset class="multiple_field">';
	echo $this->Form->input('reportwelds',array('multiple' => true,'label' => __('Welds',true),'options' => $report_testingmethods));
	echo '</fieldset>';
	?>

	<?php
	if(!empty($this->request->data['Ticket']['reportwelds'])){
		if($this->request->projectvars['VarsArray'][16] == 0) $this->request->projectvars['VarsArray'][16] = $this->request->data['Ticket']['id'];
		echo $this->Html->link(__('Create report', true),array_merge(array('action' => 'createreport'),$this->request->projectvars['VarsArray']),array('title' => __('Create report', true),'class' => 'round  mymodal'));
	}
	?>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Submit',true)));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TicketEditticketForm'));
echo $this->element('js/form_multiple_fields');

echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
//echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/show_pdf_link');
echo $this->element('rest/js/edit_testingcomp');
echo $this->element('js/json_request_animation');

?>
