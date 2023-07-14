<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Add evaluation template'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/ajax_stop_loader');
	return;

}

?>
<?php echo $this->Form->create('TemplateEvaluation', array('class' => 'login')); ?>
<div class="flex">
<fieldset>
<?php
echo $this->Form->input('name',array('label' => __('Bezeichnung für das Template',true)));
echo $this->Form->input('description',array('label' => __('Beschreibung für das Template',true)));
?>
<hr class="separation">
<p>Test</p>
<?php

//[evaluation_temp_description]
$x = 1;
foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {
  echo $this->Form->input($value,array('name' => 'data[TemplateEvaluation][data]['.$value.']'));
	$x++;
}
?>
</fieldset>
<fieldset>
<table class="advancetool">
<thead>
<tr>
<th></th>
<?php
foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {

  echo '<th>';
  echo $value;
  echo '</th>';

}
?>
</tr>
</thead>
<tbody>
<tr>
<td>

</td>

<?php
$i = 1;
foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {

  echo '<td class="'.$value.'"></td>';

	$i++;

}
?>
</tr>
</tbody>
</table>
<?php
echo $this->Html->link(__('Submit',true),'javascript:',array('id' =>'SaveTemplate','class'=>'template_navi round','title' => __('Submit',true)));
echo $this->Html->link(__('Reset',true),'javascript:',array('id' =>'ResetTemplate','class'=>'template_navi round','title' => __('Reset',true)));
?>
</fieldset>
</div>
<?php
echo $this->element('form_submit_button',array('action' => 'close','description' => __('Add',true)));
?>
</div>
<?php
//echo $this->element('js/form_button_set');
//echo $this->element('js/form_multiple_fields');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('templates/js/add_evaluation');
?>
