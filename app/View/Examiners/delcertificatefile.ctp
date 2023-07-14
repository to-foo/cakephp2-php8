<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data['Examiner']['name'] . ' - ' .
__('qualification') . ' ' .
$certificate_data['Certificate']['third_part'] . '/' .
$certificate_data['Certificate']['sector'] . '/' .
$certificate_data['Certificate']['certificat'] . '/' .
ucfirst($certificate_data['Certificate']['testingmethod']) . '/' .
$certificate_data['Certificate']['level']
;
?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
$this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['Certificate']['id'];
$this->request->projectvars['VarsArray'][17] = 0;
$url_back = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'certificate'), $this->request->projectvars['VarsArray']));
?>
<div class="error"><p>
<?php echo $hint;?>
</p></div>

<?php echo $this->Form->create('CertificateData', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->input('certified_file',array('type' => 'hidden', 'value' => '')); ?>
<?php echo $this->Form->end($del_button);?>

</div>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
}
?>

<script type="text/javascript">
$(document).ready(function(){

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
