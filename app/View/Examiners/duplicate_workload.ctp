<div class="inhalt">
<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<?php echo $this->element('Flash/_messages');?>
<div class="clear"></div>
</div>
<?php if(isset($afterEDIT)) echo $afterEDIT; ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
