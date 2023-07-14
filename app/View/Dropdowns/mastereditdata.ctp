<div class="modalarea">
<h2><?php echo __('Edit master dropdown data'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php

	echo $this->element('js/ajax_stop_loader');

	if(isset($FormName) && $this->request->data['DropdownsMastersData']['action_after_saving'] == 1){
		if(count($FormName) > 0){
			echo $this->element('js/reload_container',array('FormName' => $FormName));
			echo $this->element('js/modal_close_hide_auto');
			echo '</div>';
			return;
			}

	} elseif(isset($FormName) && $this->request->data['DropdownsMastersData']['action_after_saving'] == 0){

		if(count($FormName) > 0 && isset($FormName2) && count($FormName2) > 0){
			echo $this->element('js/reload_container',array('FormName' => $FormName));
			echo $this->element('js/modal_redirect',array('FormName' => $FormName2));
			echo '</div>';
			return;
			}

	}
?>
<?php echo $this->Form->create('DropdownsMastersData', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('value');?>
<?php echo $this->Form->input('status',array('options' => array('0' => __('active',true), '1' => __('deactiv',true)),'type' => 'radio'));?>
</fieldset>
<fieldset>
<?php echo $this->Form->input('action_after_saving',array('options' => array('0' => __('New entry',true), '1' => __('Close window',true)),'type' => 'radio'));?>
</fieldset>

<?php
if($this->request->data['DropdownsMaster']['dependencies'] == 1){

	echo '<fieldset>';
	echo $this->Html->link(__('Edit dependency',true),
		array_merge(
			array(
				'action' => 'masterdependency'
			),
			$this->request->projectvars['VarsArray']
		),
		array(
			'title' => __('Edit dependency',true),
			'class' => 'round mymodal'
		)
	);

	echo '</fieldset>';
}

?>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));?>
</div>
<?php echo $this->element('js/form_send_modal',array('FormId' => 'DropdownsMastersDataMastereditdataForm'));?>
<?php echo $this->element('js/form_button_set');?>
