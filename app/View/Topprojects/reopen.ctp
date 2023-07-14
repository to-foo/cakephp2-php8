<div class="modalarea">
<h2><?php echo __('Reopen topproject'); ?></h2>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>        
</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){
	echo $afterEDIT;
	echo $this->JqueryScripte->DialogClose(); 
	}
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>