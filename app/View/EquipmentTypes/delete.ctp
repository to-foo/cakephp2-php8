<div class="modalarea detail">
<h2><?php echo __('Delete Equipment Type'); ?></h2>
<?php echo $this->Form->create('EquipmentType', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo '<div class="error"><p>';
		echo __('Achtung, beim Löschen dieser Komponente werden alle untergeordneten Komponenten, Aufträge und Prüfberichte ebenfalls gelöscht!',true);
		echo '</p><p>';
		echo $this->Navigation->makeLink('equipmenttypes','index',__('Back'),'round mymodal',null,$this->request->projectvars['VarsArray']);
		echo '</p></div>';
//		echo $this->Form->input('topproject_id');
//		echo $this->Form->input('discription');
//		echo $this->Form->input('status');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Delete')); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
