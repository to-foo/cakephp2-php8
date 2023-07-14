<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $this->request->data['DeviceCertificate']['certificat'];?></h2>
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
<?php echo $this->Form->create('DeviceCertificateData', array('class' => 'login')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->input('certified_file',array('type' => 'hidden', 'value' => '')); ?>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete',true)));?>
</div>

<?php
echo $this->element('js/form_send_modal',array('FormId' => 'DeviceCertificateDataDelcertificatefileForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
