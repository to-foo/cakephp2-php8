<fieldset>
<?php
foreach($SearchFieldsAdditional->fields->children() as $_key => $_fields){

	$fieldtype = trim($_fields->fieldtype);
	$Model = trim($_fields->model);
	$Field = Inflector::camelize(trim($_fields->option));
	$field = trim($_fields->option);
	$local = trim($_fields->description->$locale);
	$id = $Model . $Field;

	switch($fieldtype){
		case 'dropdown':
		if(count($this->request->data[$Model][$Field]['value']) == 1 && empty($this->request->data[$Model][$Field]['value'][0])) break;		
		echo $this->Form->input(trim($_fields->option),array(
			'class' => 'dropdown',
			'label' => $local, 
			'id' => $Model.$Field,
			'name' => 'data['.$Model.']['.$field.']',
			'options' => $this->request->data[$Model][$Field]['value']
			)
		);
		break;

		case 'autocomplete':
		echo $this->Form->input(trim($_fields->option),array(
			'class' => 'autocompletion',
			'label' => $local, 
			'id' => $Model.$Field,
			'name' => 'data['.$Model.']['.$field.']',
			)
		);
		break;

		case 'date':

		if(!isset($this->request->data[$Model][$Field]['end'][0]) && !isset($this->request->data[$Model][$Field]['start'][0])) break;
		echo '<div class="input text">';
		echo '<label for="'.$Model.$Field.'Start">'.$local.'</label>';
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