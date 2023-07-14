<div class="modalarea">
<h2>
<?php  
echo __($models['top']) . ' ' . 
$certificate_data[$models['top']]['name'] . ' - ' . 
__('qualification') . ' ' .
$certificate_data[$models['main']]['third_part'] . '/' .
$certificate_data[$models['main']]['sector'] . '/' .
$certificate_data[$models['main']]['certificat'] . '/' .
ucfirst($certificate_data[$models['main']]['weldingmethod']) . ' - ' .
__('documents',true)
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<div class="hint"><p>
<?php echo __('Will you delete this file?',true);?>
</p></div>
<div id="">
<?php echo $this->Form->create($models['file'], array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->Form->end(__('Delete',true)); ?>
</div>

</div>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders', 'action' => 'filesdescription'),$this->request->projectvars['VarsArray']));
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
