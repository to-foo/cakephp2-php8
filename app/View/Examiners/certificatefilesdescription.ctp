<div class="modalarea">
<h2>
<?php
echo __($models['top']) . ' ' .
$certificate_data[$models['top']]['name'] . ' - ' .
__('qualification') . ' ' .
$certificate_data[$models['main']]['third_part'] . '/' .
$certificate_data[$models['main']]['sector'] . '/' .
$certificate_data[$models['main']]['certificat'] . '/' .
ucfirst($certificate_data[$models['main']]['testingmethod']) . ' - ' .
__('documents',true)
;
?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if($uploaderror > 0){
	echo '<div class="error">';
	echo '<p>';
	echo __('Es ist ein Fehler aufgetreten, die Datei konnte nicht gespeichert werden.',true);
	echo '<br>';
	echo $message;
	echo '</p>';
	echo '</div>';
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/ajax_mymodal_link');
	echo $this->element('js/close_modal');
	echo $this->element('js/minimize_modal');
	echo $this->element('js/maximize_modal');
	return;
}
?>
<div class="hint">
<?php
if($this->request->data[$models['file']]['description'] == ''){
	echo '<p>';
	echo __('Please enter a description for the uploaded file',true);
	echo '</p>';
}

echo '<p>';
echo __('Originally filename',true) .': ';
echo h($this->request->data[$models['file']]['originally_filename']);
echo '</p>';
?>
</div>
<div id="">
<?php echo $this->Form->create($models['file'], array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('description',array('label' => __('File description',true)));?>
<p class="clear">
<?php
$this->request->projectvars['VarsArray'][17] = $this->request->data[$models['file']]['id'];
?>
</p>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Submit',true)));?>
</div>
</div>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners', 'action' => 'filesdescription'),$this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(function(){
});
</script>
<?php
echo $this->element('js/form_buttons');
echo $this->element('js/form_datefield');
echo $this->element('js/form_send_modal',array('FormId' => 'CertificatefileCertificatefilesdescriptionForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
