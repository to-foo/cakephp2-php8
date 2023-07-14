<div class="modalarea">
<h2><?php echo __('Evaluation template details');?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo '</div>';

	echo $this->element('js/reload_evaluation',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal');

	return;
	}
?>
<table class="advancetool">
<thead>
<tr>
<?php
$Model = $this->request->data['Evaluationmodel'];

foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {

  echo '<th>';
  echo trim($this->request->data['Xml']['settings']->$Model->$value->discription->$locale);
  echo '</th>';

}
?>
</tr>
</thead>
<tbody>
<?php
// attention_field
$i = 1;
foreach ($this->request->data['TemplatesEvaluation']['data'][$Model] as $key => $value) {
echo '<tr>';
foreach ($value as $_key => $_value) {

	$AttentionField = '';
	if(isset($this->request->data['TemplatesEvaluation']['data']['Attention'][$Model][$_value['field']])) $AttentionField = 'attention_field';

	echo '<td>';
	echo '<p class="' . $AttentionField . '">';
	echo $_value['value'];
	echo '</p>';
	echo '</td>';
}
echo '</tr>';
$i++;
}
?>
</tbody>
</table>
<?php
if(count($this->request->data['TemplatesEvaluation']['data'][$Model]) == 0){

	echo '</div>';

	echo $this->element('js/form_send_modal',array('FormId' => 'TemplatesEvaluationShowForm'));
	echo $this->element('js/form_button_set');
	echo $this->element('js/minimize_modal');
	echo $this->element('js/close_modal');
	echo $this->element('js/ajax_mymodal_link');

	return;
}
?>
<?php
echo $this->Form->create('TemplatesEvaluation', array('class' => 'login'));

echo $this->Form->input('id');

if(!isset($delete_current_evaluations)){

	echo '<fieldset>';

	$options = array();
	$options[0] = __('Do not remove existing test areas');
	$options[1] = __('Remove existing test areas');

	$default = 0;

	$attributes = array(
		'default' => $default,
		'legend' => __('Should existing test areas be removed from the current report?',true),
	);

	echo '<div class="radio">';
	echo $this->Form->radio('remove_test_areas', $options, $attributes);
	echo '</div>';

	echo '</fieldset>';

} else {

	echo $this->Form->input('remove_test_areas',array('type' => 'hidden', 'value' => 1));

}

echo $this->element('form_submit_button',array('action' => 'back','description' => __('Apply',true)));
?>

</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplatesEvaluationShowForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
?>
