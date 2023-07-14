<div class="modalarea">
<h2><?php echo __('Suppliere edit')?> <?php echo $this->request['data']['Supplier']['unit']?></h2>
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
<?php echo $this->Form->create('Suppliere', array('class' => 'login','autocomplete' => 'off')); ?>
<?php
echo '<fieldset>';
echo $this->Form->input('Supplier.id');
echo $this->Form->input('Supplier.equipment',array('type' => 'text','label' => __('Equipment',true)));
echo $this->Form->input('Supplier.equipment_typ',array('empty' => __('Chose one'),'options' => $cascadegoups,'label' => __('Equipmenttyp',true)));
echo $this->Form->input('Supplier.description',array('type' => 'text','label' => __('Description',true)));
echo $this->Form->input('Supplier.planner',array('type' => 'text','label' => __('Planner',true)));
echo $this->Form->input('Supplier.contact_person',array('type' => 'text','label' => __('Kontaktperson',true)));
echo $this->Form->input('Supplier.count',array('type' => 'text','label' => __('Count',true)));
echo $this->Form->input('Supplier.count_ordered',array('type' => 'text','label' => __('Count Ordered',true)));
echo $this->Form->input('Supplier.kategorie',array('type' => 'text','label' => __('Kategorie',true)));
echo $this->Form->input('Supplier.tech_requis',array('type' => 'text','label' => __('Erforderliche Technologie',true)));
echo $this->Form->input('Supplier.material_id',array('type' => 'text','label' => __('Material Id',true)));
echo $this->Form->input('Supplier.area_of_responsibility',array('type' => 'text','label' => __('Area Of Responsibility',true)));
echo $this->Form->input('Supplier.modi_no',array('type' => 'text','label' => __('Mod-Nr.',true)));
echo $this->Form->input('Supplier.delivery_no',array('type' => 'text','label' => __('Delivery No.',true)));
echo $this->Form->input('Supplier.supplier',array('type' => 'text','label' => __('Supplier',true)));
echo $this->Form->input('Supplier.supplier_project_no',array('type' => 'text','label' => __('Supplier No.',true)));
echo $this->Form->input('Supplier.order_date',array('type' => 'text','class' => 'date','label' => __('Order Date',true)));
echo $this->Form->input('Supplier.delivery_date',array('type' => 'text','class' => 'date','label' => __('Delivery Date',true)));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('Supplier.dynamic_termination',array('options' => array(0 => 'nein',1 => 'ja'),'label' => __('Dynamic Termination',true)));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('Supplier.spare_part_list');
echo $this->Form->input('Supplier.peculiarity');
echo '</fieldset>';
echo '<fieldset>';

echo $this->Form->input('Supplier.stop_mail',array(
	'type'=>'radio',
	'options' => array(0 => __('active'),1 => __('deactive')),
	'legend' => __('Email delivering',true)
	)
);

echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $this->request->data['Testingcomp']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Emailaddress',array('multiple' => true,'label' => __('Involved email adresses',true),'options' => $emailaddress,'selected' => $this->request->data['Emailaddress']['selected']));
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'SuppliereEditForm'));
echo $this->element('expediting/js/load_suppliers');
 ?>
