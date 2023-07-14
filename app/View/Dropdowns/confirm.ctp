<div class="modalarea">
<h2><?php echo __('Delete dropdown value'); ?></h2>
<div class="hint"><p>
<?php
echo $message;
echo '<br>';
if(isset($dropdownData['DropdownsValue']['discription']) && isset($dropdowns['Dropdown'][$lang])){
	echo $dropdownData['DropdownsValue']['discription'] . ' ' . __('aus') . ' ' . $dropdowns['Dropdown'][$lang];
} else {
	if(isset($dropdownData['DropdownsValue']['discription'])){
		echo $dropdownData['DropdownsValue']['discription'];
	}
}

?>
</p></div>
<?php echo $this->Form->create('Dropdown', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('_delete',array('type' => 'hidden','value' => 1));
	?>
    </fieldset><fieldset>
   <?php
    echo $this->Form->input('after_saving',array('type' => 'radio','options' => array(2 => __('back to list',true),1 => __('close this window',true)),'value' => 2,'legend' => __('What you want to do after saving?',true)));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Delete')); ?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
