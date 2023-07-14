<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('add vision test')
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('WelderEyecheck', array('class' => 'dialogform')); ?>
	<fieldset>
    <legend><?php echo __('Grunddaten',true);?></legend>
	<?php
		echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Eyecheck');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'editeyecheck'), $this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(document).ready(function(){
<?php
if(isset($certificate_data['WelderEyecheckData'])){
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
