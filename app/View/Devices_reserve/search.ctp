<div class="">
<h2><?php echo __('Search device',true); ?></h2>
<div class="quicksearch">
<?php echo $this->element('barcode_scanner');?>
<div class="clear"></div>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('Device',array('class'=>'search_form')); ?>
<?php echo $this->element('searching/search_standard_fields',array('SearchFieldsStandard' => $SearchFieldsStandard));?></fieldset>
<?php echo $this->Form->button(__('Search devices',true),array('type' => 'submit','id' => 'SendThisDeviceForm')); ?>
<?php //echo $this->Form->button(__('Reset',true),array('type' => 'button','id' => 'ResetThisForm')); ?>
<?php echo $this->Form->end(); ?>
</div>
<?php echo $this->element('js/form_multiple_fields');?>
<?php echo $this->element('js/form_send_by_change',array('FormId' => 'DeviceSearchForm'));?>
<?php echo $this->element('js/form_send',array('FormId' => 'DeviceSearchForm'));?>
<?php echo $this->element('js/ajax_modal_link');?>
<?php echo $this->element('js/ajax_breadcrumb_link');?>
<?php echo $this->element('js/ajax_link');?>
<?php echo $this->element('js/device_autocomplete');?>
