<div class="modalarea" class="clear">
<div class="clear">
 </div>
<div class="form inhalt clear">
	<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>	

</div>
<div class="clear" id="testdiv"></div>
<div id="savediv"></div>
</div>

<?php 
echo $this->JqueryScripte->RefreshAfterDialog(null,null,$FormName);
echo $this->JqueryScripte->DialogClose();
?>