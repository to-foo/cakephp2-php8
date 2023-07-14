<div class="modalarea welders">
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
	echo $this->element('js/form_datefield');
	echo $this->element('js/ajax_send_modal_form');
	echo $this->element('js/ajax_mymodal_link');
	echo $this->element('js/close_modal');
	echo $this->element('js/minimize_modal');
	echo $this->element('js/maximize_modal');
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
<?php echo $this->Form->create($models['file'], array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('description',array('label' => __('File description',true)));?>
     <?php echo $this->Form->input('date_from',array('type' => 'text','class' => 'date','label' => __('date from',true)));?>
    <?php echo $this->Form->input('date_of_expiry',array('type' => 'text','class' => 'date','label' => __('date of expiry',true)));?>
    <?php echo $this->Form->input('horizon',array('label' => __('Horizon',true).' '.__('months')) );?>
<p class="clear">
<?php
$this->request->projectvars['VarsArray'][17] = $this->request->data[$models['file']]['id'];
 

?>
</p>
</fieldset>
<?php echo $this->Form->end(__('Submit',true)); ?>
</div>
</div>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders', 'action' => 'weldingcompfilesdescription'),$this->request->projectvars['VarsArray']));
?>

<?php 
echo $this->element('js/form_send_modal',array('FormId' => 'WeldingcompfileWeldingcompfilesdescriptionForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
 ?>

