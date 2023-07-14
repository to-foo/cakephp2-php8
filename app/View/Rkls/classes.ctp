<div class="reportnumbers index inhalt">
<h3><?php echo __('Tube class catalog',true);?></h3>
<div id="fast_searchform_container">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Back',true),
  array(
    'controller' => 'expeditings',
    'action' => 'start'
  ),
  array(
    'class' => 'icon backlink',
    'title' => __('Back',true)
  )
);
?>
</div>

<?php echo $this->Form->create('Generally'); ?>
<fieldset>
<?php
	foreach($SearchFieldsStandard->RklNumber->fields->children() as $_key => $_fields){

		$fieldtype = trim($_fields->fieldtype);
		$Model = trim($_fields->model);
		$Field = Inflector::camelize(trim($_fields->option));
		$field = trim($_fields->option);
		$local = trim($_fields->discription->$locale);
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
		}

	}
?>
</fieldset>
<?php echo $this->Form->button(__('Search results',true),array('type' => 'button','id' => 'SendThisReportForm','disabled' => 'disabled','data-desc' => __('Search results',true))); ?>
<?php echo $this->Form->button(__('Reset',true),array('type' => 'button','id' => 'ResetThisForm')); ?>
<?php echo $this->Form->end(); ?>
</div>
<div id="fast_searchresult_container"></div>

</div>
<?php
$autourl = $this->Html->url(array_merge(array('controller' => 'rkls','action' => 'rkls'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('AutoUrl',array('type' => 'hidden','value' => $autourl));

echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.backlink'));
echo $this->element('landingpage/js/fastsearch',array('SearchFormName' => '#GenerallyClassesForm'));
?>
