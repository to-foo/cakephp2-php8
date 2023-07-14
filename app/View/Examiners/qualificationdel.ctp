<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $certificate_data['Examiner']['name'] . ' ' . __('delete qualification');?></h2>
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
<?php echo $this->Form->create('Certificate', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete',true)));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'qualification'), $this->request->projectvars['VarsArray']));

?>
</div>
<div class="clear"></div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'CertificateQualificationdelForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
