<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $certificate_data['DeviceCertificate']['certificat'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<div class="hint">
<p>
<?php
echo __('Next scheduled monitoring',true). ': ';
echo '<span class="" id="date_next_certifcation" title="';
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['DeviceCertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p><?php echo ($certificate_data_old_infos['DeviceCertificateData']['time_to_next_certification']);?></p>
<p>
<?php
if($this->request->data['DeviceCertificate']['file'] == 1) echo $this->Html->link(__('Change monitoring file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Change monitoring file',true),'class' => 'mymodal round'));
?>
</p>
</div>
<hr>
<?php
if($this->request->data['DeviceCertificateData']['file'] == 1){
	if(isset($this->request->data['DeviceCertificateData']['certified_file_pfath']) && !empty($this->request->data['DeviceCertificateData']['certified_file_pfath'])){
		echo '<div class="hint"><p>';
		echo __('There is a valid certificate.');
		echo ' ';
		echo __('When you upload a new file, the existing file will be deleted.');
		echo '</p><p>';
		echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round ','target' => '_blank'));
		echo $this->Html->link(__('Replace certificate file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));
		echo $this->Html->link(__('Delete certificate file',true), array_merge(array('action' => 'delcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));
		echo '</p></div>';
	}
}
?>
<?php echo $this->Form->create('DeviceCertificateData', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'DeviceCertificateData','testingmethods' => false));?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));?>
</div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	}
?>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'DeviceCertificateDataEditmonitoringForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
