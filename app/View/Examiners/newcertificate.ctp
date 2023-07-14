<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data['Examiner']['name'] . ' ' .
__('add certificate')
;
?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('Certificate', array('class' => 'dialogform')); ?>
	<fieldset>
<?php // pr($this->request->data['DropdownInfo']);// echo $this->Form->input('third_part',array('type' => 'select','options' => array('' => '', 1 => 'DGZfP', 2 => 'TÜV'))); ?>
	<?php
		echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Certificate');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'certificate'), $this->request->projectvars['VarsArray']));

if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
}

?>
<script type="text/javascript">
$(document).ready(function(){
<?php
if(isset($certificate_data['CertificateData'])){
	echo '
	$("#dialog").load("'.$url.'", {
		"ajax_true": 1,
	})
	';
}
?>
});
</script>
</div>
<div class="clear"></div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/form_button_set');
?>
