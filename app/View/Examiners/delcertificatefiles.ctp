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
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<div class="hint"><p>
<?php echo __('Will you delete this file?',true);?>
</p><p><b>
<?php echo $certificate_files['Certificatefile']['description'];?>
</b></p></div>
<div id="">
<?php echo $this->Form->create($models['file'], array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->Form->end(__('Delete',true)); ?>
</div>

</div>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners', 'action' => 'filesdescription'),$this->request->projectvars['VarsArray']));

echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
