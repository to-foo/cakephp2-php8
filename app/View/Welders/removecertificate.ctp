<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('qualification') . ' ' .
$certificate_data['WelderCertificate']['third_part'] . '/' .
$certificate_data['WelderCertificate']['sector'] . '/' .
$certificate_data['WelderCertificate']['certificat'] . '/' .
ucfirst($certificate_data['WelderCertificate']['weldingmethod'])
;
?>
</h2>
<div class="error"><p>
<?php echo $hint;?>
</p></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('WelderCertificate', array('class' => 'dialogform')); ?>
<?php echo $this->Form->id('id',array('type' => 'hidden')); ?>
<?php echo $this->Form->end($del_button);?>
</div>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'certificates'), $this->request->projectvars['VarsArray']));
?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
} 
?>

<script type="text/javascript">
$(document).ready(function(){
<?php
if($this->request->projectvars['VarsArray'][16] == 0){
	echo '
	$("#dialog").load("'.$url.'", {
		"ajax_true": 1,
	})
	';
}
?>	
});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
