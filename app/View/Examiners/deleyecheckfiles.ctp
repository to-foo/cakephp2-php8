<div class="modalarea">
<h2>
<?php echo __($models['top']) . ' ' . $certificate_data[$models['top']]['name'] . ' - ' . __('qualification') . ' ' . $certificate_data[$models['main']]['certificat'] . ' - ' . __('documents',true);?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint"><p><?php echo __('Will you delete this file?',true);?></p></div>
<div id="">
<?php echo $this->Form->create($models['file'], array('class' => 'dialogform')); ?>
<fieldset><?php echo $this->Form->input('id');?></fieldset>
<?php echo $this->Form->end(__('Delete',true)); ?>
</div>

</div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners', 'action' => 'filesdescription'),$this->request->projectvars['VarsArray']));
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
