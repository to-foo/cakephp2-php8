<div class="inhalt ">
<!-- Beginn Headline -->
<h2><?php echo __('Edit evaluation template'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
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

<?php echo $this->Form->create('TemplateEvaluation', array('class' => 'login')); ?>
<div class="flex_info flex_info_no_wrap">
<div class="flex_item">
	<?php
	$x = 1;
	foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {

	  echo $this->Form->input($value,
			array(
				'label' => $this->request->data['Template']['evaluation_temp_description'][$key],
				'name' => 'data[TemplateEvaluation][data][' . $value . ']',
			)
		);
		$x++;
	}

	echo $this->Html->link(__('Add',true),'javascript:',array('id' =>'AddSaveTemplate','class'=>'template_navi round','title' => __('Add',true)));
	//echo $this->element('form_submit_button',array('action' => 'close','description' => __('Add',true)));
	?>
</div>
<div class="flex_item">
<table class="advancetool">
<thead>
<tr>
<th></th>
<?php
$Model = $this->request->data['Evaluationmodel'];

foreach ($this->request->data['Template']['evaluation_temp'] as $key => $value) {

  echo '<th>';
	echo $this->Html->link($this->request->data['Template']['evaluation_temp_description'][$key],
	array_merge(
		array(
			'controller' => 'templates',
			'action' => 'json'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class'=>'round attention_template_field',
		'title' => $value,
		'rel' => $value,
		'rev' => $Model
		)
	);
  echo '</th>';

}
?>
</tr>
</thead>
<tbody>
<?php
$i = 1;
foreach ($this->request->data['TemplatesEvaluation']['data'][$Model] as $key => $value) {

if($this->request->data['TemplatesEvaluation']['modified'] == $this->request->data['TemplatesEvaluation']['created']) continue;

echo '<tr id="template_data_row_'.$i.'">';
echo '<td>';
echo $this->Html->link(__('Delete',true),'javascript:',array('class'=>'icon icon_delete','title' => __('Delete',true)));
echo $this->Html->link(__('Duplicate',true),'javascript:',array('class'=>'icon icon_dupli','title' => __('Duplicate',true)));
echo '</td>';

foreach ($value as $_key => $_value) {

	$AttentionClass = '';

	if(isset($this->request->data['TemplatesEvaluation']['data']['Attention'][$Model][$_value['field']])) $AttentionClass = 'attention_field';

	$id = 'templates_' . $_value['field'] . '_' . $i;
	$name = 'data[TemplatesEvaluations][data][' . $i . '][' . $_value['field'] . ']';
	$formname = 'data[TemplateEvaluation][data][' . $_value['field'] . ']';

	echo '<td class="template_data ' . $AttentionClass . '" id="' . $id . '" name="' . $name . '" form-name="'.$formname.'" >';
	echo '<p class="editable">';
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
echo $this->Html->link(__('Submit',true),'javascript:',array('id' =>'SaveTemplate','class'=>'template_navi round','title' => __('Submit',true)));
?>
</div>
</div>
<?php echo $this->Form->end(); ?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplatesEvaluationEvaluationForm'));
echo $this->element('templates/js/edit_evaluation');
echo $this->element('templates/js/edit_attentions');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
