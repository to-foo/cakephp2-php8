<?php 
if(isset($reportnumberID) && $reportnumberID > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,$evalutionID,$FormName);
	echo $this->JqueryScripte->DialogClose();
	} 
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
