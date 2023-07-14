<div class="modalarea">
<h2><?php echo __('Expediting details')?> <?php echo $Supplier['Supplier']['equipment']?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php if(isset($SupplierOption)):?>
<script type="text/javascript">

$(document).ready(function(){
	$("#expediting_list_<?php echo $SupplierOption['id'];?>").removeClass("icon_epediting icon_critical icon_delayed icon_plan icon_future icon_finished").addClass("<?php echo $Priority[$SupplierOption['status']];?>");
});

</script>
<?php endif;?>

<?php
//if($Message != NULL) echo '<div class="error"><p>' . $Message . '</p></div>';

?>
<?php echo $this->Form->create('Expediting', array('class' => 'expeditingform login','autocomplete' => 'off')); ?>
<?php
echo '<div class="expediting_form">';

//pr($this->request->data);

foreach($this->request->data as $_key => $_data){
echo '<h3 class="'.$_data['Expediting']['class'].'">';
echo $_data['Expediting']['description'];
echo '</h3>';
echo '<div class="expediting_form_container expediting '.$_data['Expediting']['class'].'">';
echo '<fieldset class="expediting '.$_data['Expediting']['class'].'">';
echo $this->Form->input('Id', array(
    'id' => 'ExpeditingDateIst' . $_key . 'Id',
    'name' => 'data[Expediting][' . $_key . '][id]',
	'value' => $_data['Expediting']['id'],
	'type' => 'hidden'
));

echo $this->Form->input('Datum/Soll', array(
  'id' => 'ExpeditingDateSoll' . $_key . 'Month',
  'name' => 'data[Expediting][' . $_key . '][date_soll]',
	'value' => $_data['Expediting']['date_soll'],
	'class' => 'date',
));
echo $this->Form->input('Datum/Ist', array(
  'id' => 'ExpeditingDateIst' . $_key . 'Month',
  'name' => 'data[Expediting][' . $_key . '][date_ist]',
	'value' => $_data['Expediting']['date_ist'],
	'class' => 'date',
));
echo $this->Form->input('hold_witness_point', array(
	'id' => 'ExpeditingDateIst' . $_key . 'HoldWitnessPoint',
  'name' => 'data[Expediting][' . $_key . '][hold_witness_point]',
	'options' => array(0 => '-',1 => __('Witness'),2 => __('Hold')),
	'type' => 'radio'
));

echo '</fieldset><fieldset>';
echo $this->Form->input('remark', array(
    'id' => 'ExpeditingRemark' . $_key . 'Remark',
    'name' => 'data[Expediting][' . $_key . '][remark]',
	'value' => $_data['Expediting']['remark'],
));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Roll',array(
							'id' => 'Roll' . $_key . 'Roll',
							'name' => 'data[Roll][' . $_key . '][Roll]',
							'multiple' => true,
							'label' => __('Involved rolls for e-mail shipping',true),
							'options' => $Roll,
							'selected' => $_data['Roll']['selected']
						)
					);
echo '</fieldset>';
echo '</div>';
}
echo '</div>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'reset'));?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_accordion',array('step' => $Step));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingDetailForm'));
?>
