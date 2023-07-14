<div class="modalarea detail">
<h2><?php echo __('Delete Equipment'); ?></h2>
<?php echo $this->Form->create('Equipment', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo '<div class="error"><p>';
		echo __('Achtung, beim Löschen dieser Komponente werden alle untergeordneten Aufträge und Prüfberichte ebenfalls gelöscht!',true);
		echo '</p><p>';
		echo $this->Navigation->makeLink('equipments','index',__('Back'),'round mymodal',null,$this->request->projectvars['VarsArray']);
		echo '</p></div>';
//		echo $this->Form->input('topproject_id');
//		echo $this->Form->input('discription');
//		echo $this->Form->input('status');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Delete')); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
