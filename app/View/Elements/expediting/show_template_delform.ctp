<?php 
if(isset($this->request->data['Template'])) return; 
if(isset($EditUrl)) return; 
?>

<?php echo $this->Form->create('ExpeditingTemplate', array('class' => 'login','autocomplete' => 'off')); ?>
<?php
echo '<fieldset>';
echo $this->Form->input('DeleteExpeditingId', array('type' =>'hidden','val' => $this->request->data['Supplier']['id']));
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'close'));?>