<div class="modalarea detail">
<h2>
<?php echo __('Create new ITP'); ?> 
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo '</div>';
	return;
}
?>
<?php echo $this->element('expediting/show_template_create_form');?>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingsetCreatetemplateForm'));
 ?>
