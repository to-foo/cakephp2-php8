<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $data['DocumentCertificate']['certificat'];?></h2>
<?php
/*
$this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['Certificate']['id'];
$this->request->projectvars['VarsArray'][17] = 0;
*/
$url_back = $this->Html->url(array_merge(array('controller' => 'documents','action' => 'monitoring'), $this->request->projectvars['VarsArray']));
?>
<div class="error"><p>
<?php echo $hint;?>
</p></div>

<?php echo $this->Form->create('DocumentCertificateData', array('class' => 'dialogform')); ?>
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

<?php  echo $this->JqueryScripte->ModalFunctions(); ?>

<script type="text/javascript">
$(document).ready(function(){
});
</script>