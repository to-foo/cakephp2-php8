<div class="modalarea">
	<h2>
		<?php echo __('Add master dropdown data'); ?> > <?php echo $this->request->data['DropdownsMaster']['name']; ?>
	</h2>
	<?php echo $this->element('Flash/_messages'); ?>
	<?php
	echo $this->element('js/ajax_stop_loader');

	if (isset($FormName)) {
		if (count($FormName) > 0) {
			echo $this->element('js/reload_container', array('FormName' => $FormName));
			echo $this->element('js/close_modal_auto');
			echo '</div>';
			return;
		}

	}

	?>

	<?php echo $this->Form->create('DropdownsMastersData', array('class' => 'login')); ?>
	<fieldset>
		<?php echo $this->Form->input('value'); ?>
		<?php echo $this->Form->input('status', array('options' => array('0' => __('active', true), '1' => __('deactiv', true)), 'default' => '0', 'type' => 'radio')); ?>
	</fieldset>
	<?php echo $this->Form->end(__('Submit', true)); ?>

	<div class="hint">	
	<?php
	foreach($EditLinkContainer as $key => $value){

		echo $this->Html->link(
			$value['discription'],
			array_merge(
				array(
					'controller' => $value['controller'],
					'action' => $value['action']
					),
					$value['parms']
				),
				array(
					'class' => $value['link_class'],
					'id' => $value['link_id']
				)
			);

	}
	?>
	</div>
</div>
<?php echo $this->element('js/form_send_modal', array('FormId' => 'DropdownsMastersDataMasteradddataForm')); ?>
<?php echo $this->element('js/form_button_set'); ?>
<?php echo $this->element('js/ajax_mymodal_link'); ?>
<?php echo $this->element('js/ajax_modal_request'); ?>
<?php echo $this->element('dropdowns/js/master_link_back_to_report');?>
