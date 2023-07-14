<div class="modalarea">
<h2>
<?php
echo __($models['top']) . ' ' .
$certificate_data[$models['top']]['name'] . ' - ' .
__('qualification') . ' ' .
$certificate_data[$models['main']]['certificat'] . ' - ' .
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

	echo $this->element('js/ajax_mymodal_link');
	echo $this->element('js/close_modal');
	echo $this->element('js/minimize_modal');
	echo $this->element('js/form_multiple_fields');
	echo $this->element('js/ajax_modal_request');
	echo $this->element('js/form_button_set');

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
<?php echo $this->Form->create($models['file'], array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('description',array('label' => __('File description',true)));?>
<p class="clear">
<?php
$this->request->projectvars['VarsArray'][17] = $this->request->data[$models['file']]['id'];
?>
</p>
</fieldset>
<?php echo $this->Form->end(__('Submit',true)); ?>
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
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/form_button_set');
?>
