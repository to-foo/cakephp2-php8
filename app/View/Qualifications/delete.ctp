<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<div class="inhalt"><?php echo $this->Session->flash(); ?></div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>