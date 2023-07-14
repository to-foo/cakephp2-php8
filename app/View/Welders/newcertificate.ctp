<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('add certificate')
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('WelderCertificate', array('class' => 'dialogform')); ?>
	<fieldset>
<?php // pr($this->request->data['DropdownInfo']);// echo $this->Form->input('third_part',array('type' => 'select','options' => array('' => '', 1 => 'DGZfP', 2 => 'TÜV'))); ?>
	<?php
		echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Certificate');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'certificate'), $this->request->projectvars['VarsArray']));

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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
