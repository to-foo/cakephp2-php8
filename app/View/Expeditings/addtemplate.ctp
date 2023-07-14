<div class="modalarea detail">
<h2><?php echo __('Create Expediting with this ITP'); ?></h2>
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
<?php 
echo $this->element('expediting/show_template_data');
echo $this->element('expediting/show_template_form');
echo $this->element('expediting/show_template_empty_date');
?>

<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingTemplateAddtemplateForm'));
 ?>
