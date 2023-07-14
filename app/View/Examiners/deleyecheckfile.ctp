<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $certificate_data['Examiner']['name'] . ' - ' . $certificate_data['Eyecheck']['certificat'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="error"><p><?php echo $hint;?></p></div>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}

$this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['Eyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = 0;
$url_back = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'eyecheck'), $this->request->projectvars['VarsArray']));
?>

<?php echo $this->Form->create('EyecheckData', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->input('certified_file',array('type' => 'hidden', 'value' => '')); ?>
<?php echo $this->Form->end($del_button);?>
</div>

<?php
echo $this->element('js/form_buttons');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
