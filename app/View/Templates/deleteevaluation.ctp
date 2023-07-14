<div class="modalarea">
<h2><?php echo __('Delete evaluation template');?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	return;

}
?>
<div class="hint">
<div class="current_content flex_info">
<div class="flex_item">
<div class="label">
<?php echo __('Name',true);?>
</div>
<div class="content">
<p class="editable" rev="name" rel="<?php echo $this->request->data['TemplatesEvaluation']['id'];?>">
<?php echo $this->request->data['TemplatesEvaluation']['name'];?>
</p>
</div>
</div>
<div class="flex_item">
<div class="label">
<?php echo __('Description',true);?>
</div>
<div class="content">
<p class="editable" rev="description" rel="<?php echo $this->request->data['TemplatesEvaluation']['id'];?>">
<?php echo $this->request->data['TemplatesEvaluation']['description'];?>
</p>
</div>
</div>
</div>
</div>
<div class="hint">
<table class="advancetool">
<thead>
<tr>
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
echo '<tr id="template_data_row_'.$i.'">';

foreach ($value as $_key => $_value) {
	$id = 'templates_' . $_value['field'] . '_' . $i;
	$name = 'data[TemplatesEvaluations][data][' . $i . '][' . $_value['field'] . ']';
	$formname = 'data[TemplateEvaluation][data][' . $_value['field'] . ']';

	echo '<td class="template_data" id="' . $id . '" name="' . $name . '" form-name="'.$formname.'" >';
	echo $_value['value'];
	echo '</td>';
}
echo '</tr>';
$i++;
}
?>
</tbody>
</table>
</div>
<?php echo $this->Form->create('TemplatesEvaluation', array('class' => 'login'));?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('delete_evaluation_template',array('type' => 'hidden','name' => 'delete_evaluation_template', 'value' => 1));?>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete',true)));?>

<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplatesEvaluationDeleteForm'));
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
