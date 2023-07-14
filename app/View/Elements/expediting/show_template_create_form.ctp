<?php
?>
<?php echo $this->Form->create('Expeditingset', array('class' => 'login','autocomplete' => 'off')); ?>
<?php
echo $this->Form->input('id');
echo '<fieldset>';
echo $this->Form->input('name');
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('description');
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'back'));?>
<div class="flex_info_column">
<?php

?>
<div class="flex_item">
<div class="flex_info">
<?php
$VarsArray = $this->request->projectvars['VarsArray'];
/*
echo $this->Html->link(__('Add expediting step',true),
array_merge(
  array(
    'controller'=>'expeditings',
    'action' => 'addexpeditingstep'),
    $VarsArray
),
array(
  'title' => __('Add expediting step',true),
  'class'=>'round mymodal'
),
);
*/
?>
</div>
</div>
</div>
</div>