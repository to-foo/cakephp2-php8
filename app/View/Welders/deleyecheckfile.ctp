<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' - ' . 
$certificate_data['WelderEyecheck']['certificat'] 
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>

<?php
$this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['WelderEyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = 0;
$url_back = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'eyecheck'), $this->request->projectvars['VarsArray']));
?>
<div class="error"><p>
<?php echo $hint;?>
</p></div>

<?php echo $this->Form->create('WelderEyecheckData', array('class' => 'dialogform')); ?>
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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
