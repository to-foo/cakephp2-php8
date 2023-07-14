<div class="modalarea">
<h2>
<?php  

?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<?php
if($uploaderror > 0){
	echo '<div class="error">';
	echo '<p>';
	echo __('Es ist ein Fehler aufgetreten, die Datei konnte nicht gespeichert werden.',true);
	echo '<br>';
	echo $message;
	echo '</p>';
	echo '</div>';
	echo $this->JqueryScripte->ModalFunctions();
	return;
}
?>
<div class="hint">
<?php
if($this->request->data[$models['file']]['description'] == ''){ 
	echo '<p>';
	echo __('Please enter a description for the uploaded file',true);
	echo '</p>';
}

echo '<p>';
echo __('Originally filename',true) .': ';
echo h($this->request->data[$models['file']]['originally_filename']);
echo '</p>';
?>
</div>
<div id="">
<?php echo $this->Form->create($models['file'], array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('description',array('label' => __('File description',true)));?>
<p class="clear">
<?php
$this->request->projectvars['VarsArray'][17] = $this->request->data[$models['file']]['id'];
 
// echo $this->Html->link(__('Show file'),array_merge(array('action' => 'geteyecheckfiles'),$this->request->projectvars['VarsArray']),array('class' => 'round','target'=>'_blank'));
// echo $this->Html->link(__('Delete file'),array_merge(array('action' => 'deleyecheckfiles'),$this->request->projectvars['VarsArray']),array('class' => 'round mymodal'));

?>
</p>
</fieldset>
<?php echo $this->Form->end(__('Submit',true)); ?>
</div>
</div>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders', 'action' => 'welderfilesdescription'),$this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(function(){
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<script type="text/javascript">
$(document).ready(function(){
});
</script>
