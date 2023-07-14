<div class="modalarea dropdowns form">
<h2><?php echo __('Edit'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($validationErrors)) echo $this->element('validation_error_list');

if(isset($FormName) && count($FormName) > 0){

	if($saveOK == 4){
		echo $this->element('js/modal_redirect',array('FormName' => $FormName));
		return;
	}

	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/ajax_mymodal_link');
	echo $this->element('js/close_modal');
	echo $this->element('js/minimize_modal');

	if($saveOK == 1) echo $this->element('js/close_modal_auto');
	if($saveOK == 2 && isset($FormName2) && $FormName2['controller'] != 'dropdowns') echo $this->element('js/modal_redirect',array('FormName' => $FormName2));
	if($saveOK == 3 && isset($FormName2)) echo $this->element('js/modal_redirect',array('FormName' => $FormName2));

	echo '</div>';
	return;
}
?>

<?php echo $this->Form->create($Model, array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('discription');
		if(AuthComponent::user('Roll.id') < 5){
			echo $this->Form->input('global',array('type' => 'checkbox'));
		}
	?>
    </fieldset><fieldset>
   <?php
	 	echo '<br/>';
    echo $this->Form->input('after_saving',array('type' => 'radio','options' => array(2 => __('back to list',true),1 => __('close this window',true),3 => __('add new entry',true)),'value' => 2,'legend' => __('What you want to do after saving?',true)));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'DropdownsValueDropdowneditForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/form_button_set');
?>
