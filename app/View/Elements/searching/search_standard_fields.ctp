<?php if(!is_object($SearchFieldsStandard)) return;?>
<fieldset>
<?php

foreach($SearchFieldsStandard->fields->children() as $_key => $_fields){

	$fieldtype = trim($_fields->fieldtype);
	$Model = trim($_fields->model);
	$Field = Inflector::camelize(trim($_fields->option));
	$field = trim($_fields->option);
	$local = trim($_fields->description->$locale);
	$id = $Model . $Field;

	if(isset($_fields['disabled']) && $_fields['disabled'] == 'disabled') $IsDisabled = array('disabled' => 'disabled');
	else $IsDisabled = null;

	switch($fieldtype){
		case 'autocomplete':
			echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
			'class' => 'autocomplete',
			'label' => $local,
			'id' => $Model.$Field,
			'name' => 'data['.$Model.']['.$field.']',
			)
		);
		break;
		case 'dropdown':
		if(!isset($this->request->data[$Model])) break;
		if(!isset($this->request->data[$Model][$Field])) break;

		if(count($this->request->data[$Model][$Field]['value']) == 1 && isset($this->request->data[$Model][$Field]['value'][0]) && empty($this->request->data[$Model][$Field]['value'][0])) break;

		echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
			'class' => 'dropdown',
			'multiple' => false,
			'label' => $local,
			'id' => $Model.$Field,
			'name' => 'data['.$Model.']['.$field.']',
			'options' => $this->request->data[$Model][$Field]['value'],
			'selected' => $this->request->data[$Model][$Field]['selected'],
			$IsDisabled
			)
		);
		break;
		case 'multiple':

		if(!isset($this->request->data[$Model])) break;
		if(!isset($this->request->data[$Model][$Field])) break;

		if(count($this->request->data[$Model][$Field]['value']) == 1 && isset($this->request->data[$Model][$Field]['value'][0]) && empty($this->request->data[$Model][$Field]['value'][0])) break;

		echo '<fieldset class="multiple_field">';
		echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
			'class' => 'dropdown',
			'multiple' => true,
			'label' => $local,
			'id' => $Model.$Field,
			'name' => 'data['.$Model.']['.$field.']',
			'options' => $this->request->data[$Model][$Field]['value'],
			'selected' => $this->request->data[$Model][$Field]['selected'],
			$IsDisabled
			)
		);
		echo '</fieldset>';
		break;
		case 'date':
		if(!isset($this->request->data[$Model][$Field]['end'][0]) && !isset($this->request->data[$Model][$Field]['start'][0])) break;
		echo '<div class="input text">';
		echo '<label for="'.$Model.$Field.'Start">'.$this->request->data[$Model][$Field]['description'].'</label>';
		echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('from',true) . ')',array(
				'class' => 'date',
				'id' => $Model.$Field.'Start',
				'name' => 'data['.$Model.']['.$field.'][start]',
				'placeholder' => __('from',true),
				'div' => false,
				'label' => false,
			)
		);
		echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('to',true) . ')',array(
				'class' => 'date',
				'id' => $Model.$Field.'End',
				'name' => 'data['.$Model.']['.$field.'][end]',
				'placeholder' => __('to',true),
				'div' => false,
				'label' => false,
			)
		);
		echo '</div>';

		echo '
		<script>
		$(document).ready(function(){
			$("#'.$Model.$Field.'Start, #'.$Model.$Field.'End").prop({"readOnly": false}).datetimepicker({
				timepicker:false,
				format:"Y-m-d",
				lang:"de",
			});
		});
		</script>
		';

		break;
	}
}
?>
</fieldset>
