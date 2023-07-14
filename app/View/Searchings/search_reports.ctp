<div class="inhalt">
<h2></h2>
<?php echo $this->element('Flash/_messages');?>
</div>
<?php echo $this->Form->create('Generally',array('class'=>'login')); ?>
<fieldset>
	<?php
	$Lang = Configure::read('Config.language');
	$Dateformat = Configure::read('Dateformat');

	$Language = "en";
	$Date = "Y-m-d";
	$Datetime = "Y-m-d H:i";
	$Time = "H:i:s";

	if($Lang == "deu") $Language = "de";
	if($Lang == "eng") $Language = "en";

	if(isset($Dateformat[$Lang]['date'])) $Date = $Dateformat[$Lang]['date'];
	if(isset($Dateformat[$Lang]['datetime'])) $Datetime = $Dateformat[$Lang]['datetime'];
	if(isset($Dateformat[$Lang]['time'])) $Time = $Dateformat[$Lang]['time'];

	echo $this->Form->input('LanguageForPicker',array('type' => 'hidden','value' => $Language));
	echo $this->Form->input('DateForPicker',array('type' => 'hidden','value' => $Date));
	echo $this->Form->input('DateTimeForPicker',array('type' => 'hidden','value' => $Datetime));
	echo $this->Form->input('TimeForPicker',array('type' => 'hidden','value' => $Time));

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
			echo $this->element('searching/search_form_autocomplete',array('Model' => $Model,'Field' => $Field,'field' => $field,'local' => $local,'IsDisabled' => $IsDisabled));
			break;
			case 'dropdown':
			echo $this->element('searching/search_form_dropdown',array('Model' => $Model,'Field' => $Field,'field' => $field,'local' => $local,'IsDisabled' => $IsDisabled));
			break;
			case 'multiselect':
			echo $this->element('searching/search_form_multiselect',array('Model' => $Model,'Field' => $Field,'field' => $field,'local' => $local,'IsDisabled' => $IsDisabled));
			break;
			case 'date':
			echo $this->element('searching/search_form_date',array('Model' => $Model,'Field' => $Field,'field' => $field,'local' => $local,'IsDisabled' => $IsDisabled));
			break;
		}
	}

?>
</fiedset>
<?php echo $this->Form->end(); ?>
<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
