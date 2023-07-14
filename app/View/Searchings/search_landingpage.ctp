<h3><?php echo __('Global search testing reports') ?></h3>
<div id="fast_searchform_container">
	<div class="quicksearch">
	<?php
	echo $this->Html->link(__('Search',true),
		array(
			'controller' => 'topprojects',
			'action' => 'index'
		),
		array(
			'class' => 'icon backlink',
			'title' => __('Back',true)
		)
	);
	?>
	</div>
<?php
if(empty($SearchFieldsStandard)){
	echo '</div>';
	echo $this->element('landingpage/js/ajax_link_lage_window',array('name' => 'a.backlink'));
	return;
}
?>
<?php echo $this->Form->create('Generally'); ?>
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
			echo $this->Form->input($Model.$Field,array(
				'class' => 'autocompletion',
				'label' => $local,
				'id' => $Model.$Field,
				'name' => 'data['.$Model.']['.$field.']',
				)
			);
			echo $this->Form->input('Searching' . $Model.$Field,array(
				'type' => 'hidden',
				'id' => 'Searching' . $Model . $Field,
				'name' => 'data[Searching][' . $Model . '][' . $field . ']',
				'value' => 0
				)
			);
			break;
			case 'dropdown':

			if(!isset($this->request->data[$Model])) break;
			if(!isset($this->request->data[$Model][$Field])) break;

			if(count($this->request->data[$Model][$Field]['value']) == 1 && isset($this->request->data[$Model][$Field]['value'][0]) && empty($this->request->data[$Model][$Field]['value'][0])) break;

			echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
				'class' => 'dropdown',
				'label' => $local,
				'id' => $Model.$Field,
				'name' => 'data['.$Model.']['.$field.']',
				'options' => $this->request->data[$Model][$Field]['value'],
//				'selected' => $this->request->data[$Model][$Field]['selected'],
				$IsDisabled
				)
			);
			echo $this->Form->input('Searching' . $Model.$Field,array(
				'type' => 'hidden',
				'id' => 'Searching' . $Model . $Field,
				'name' => 'data[Searching][' . $Model . '][' . $field . ']',
				'value' => 0
				)
			);
			break;
		}

	}
?>
</fieldset>
<fieldset>
<?php echo $this->Form->button(__('Testing reports',true),array('type' => 'button','id' => 'SendThisReportForm','disabled' => 'disabled','data-desc' => __('Testing reports',true))); ?>
<?php echo $this->Form->button(__('Reset',true),array('type' => 'button','id' => 'ResetThisForm')); ?>
</fieldset>
<?php echo $this->Form->end(); ?>
<?php //echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));?>

</div>
<div id="fast_searchresult_container"></div>
<?php
$autourl = $this->Html->url(array_merge(array('controller' => 'searchings','action' => 'autofast'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('AutoUrl',array('type' => 'hidden','value' => $autourl));

echo $this->element('landingpage/js/ajax_link_lage_window',array('name' => 'a.backlink'));
echo $this->element('landingpage/js/fastsearch',array('SearchFormName' => '#GenerallySearchForm'));
?>
