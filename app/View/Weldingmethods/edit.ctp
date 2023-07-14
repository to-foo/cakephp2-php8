<div class="modalarea">
<h2><?php echo __('Edit weldingmethod'); ?></h2>
 <div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>  
<?php if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshDialogAfterDialog($FormName,3000);
	
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;

}?>
<?php echo $this->Form->create('Weldingmethod', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
        
                echo $this->Form->input('id');
		echo $this->Form->input('value');
		echo $this->Form->input('name');
		echo '</fieldset><fieldset>';
		echo $this->Form->input('verfahren');
                
	?>
	</fieldset>
<?php   echo'<fieldset class="multiple_field">';
    
                echo $this->Form->input('Testingmethod',array(
                    'label' => __('Testingmethod',true),
				'multiple' => 'multiple',
				'empty' => ' ',
                ));  
                 echo'</fieldset>';?>
          
<?php
echo $this->Form->end(__('Submit', true));
?>
</div>
<div class="clear" id="testdiv"></div>
<script>
$(document).ready(function(){
    $('#TestingmethodTestingmethod').multiSelect();
    $('#TestingmethodTestingmethod').multiSelect({ selectableOptgroup: true });
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>