<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Add evaluation template'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	return;

}
unset($this->request->data['Template']['evaluation_temp'][0]);
?>
<?php echo $this->Form->create('TemplatesEvaluation', array('class' => 'login')); ?>
<div class="flex">
<fieldset class="template_data_1">
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('name');?>
<?php echo $this->Form->input('description');?>
</fieldset>
<fieldset>
<table class="advancetool">
<thead>
<tr>
<th></th>
<?php
$Model = $this->request->data['Evaluationmodel'];
foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {

  echo '<th>';
  echo $value;
  echo '</th>';

}
?>
</tr>
</thead>
<tbody>
<?php
$i = 1;
foreach ($this->request->data['TemplatesEvaluation']['data'][$Model] as $key => $value) {
echo '<tr>';
foreach ($value as $_key => $_value) {
	echo '<td>';
	echo $_value['value'];
	echo '</td>';
}
echo '</tr>';
$i++;
}
?>
</tbody>
</table>
</fieldset>
</div>
<?php
echo $this->element('form_submit_button',array('action' => 'close','description' => __('Submit',true)));
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplatesEvaluationEvaluationForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('templates/js/edit_evaluation');
?>
