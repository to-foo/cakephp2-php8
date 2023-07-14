<div class="modalarea detail">
<h2><?php echo __('Edit expediting types'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName)){
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}

echo $this->element('expediting/expediting_typs_table');
echo $this->element('expediting/js/expediting_type_edit_js');
?>
</div>
<?php
echo $this->element('js/json_link');
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingTypeAddexpeditingForm'));
 ?>
