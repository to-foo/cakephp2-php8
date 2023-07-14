<div class="modalarea">
<h2><?php echo __('Delete topproject'); ?></h2>
<?php echo $this->Form->create('Topproject', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo '<div class="error"><p>';
		echo __('Achtung, beim Löschen dieses Projekts werden alle untergeordneten Komponenten, Aufträge und Prüfberichte ebenfalls gelöscht!',true);
		echo '</p><p>';
	?>
	</fieldset>
    <div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>        
<?php
echo $this->Form->end(__('Delete', true));
?>
</div>
<div class="clear" id="testdiv"></div>
<?php 
if(isset($afterEDIT)){
	echo $afterEDIT; 
	echo $this->JqueryScripte->DialogClose();
}
echo $this->JqueryScripte->ModalFunctions();
?>