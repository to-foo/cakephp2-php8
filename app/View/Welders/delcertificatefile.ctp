<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' - ' . 
__('qualification') . ' ' .
$certificate_data['WelderCertificate']['third_part'] . '/' .
$certificate_data['WelderCertificate']['sector'] . '/' .
$certificate_data['WelderCertificate']['certificat'] . '/' .
ucfirst($certificate_data['WelderCertificate']['weldingmethod']) . '/' .
$certificate_data['WelderCertificate']['level']
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>

<?php
$this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['WelderCertificate']['id'];
$this->request->projectvars['VarsArray'][17] = 0;
$url_back = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'certificate'), $this->request->projectvars['VarsArray']));
?>
<div class="error"><p>
<?php echo $hint;?>
</p></div>

<?php echo $this->Form->create('WelderCertificateData', array('class' => 'dialogform')); ?>
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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
