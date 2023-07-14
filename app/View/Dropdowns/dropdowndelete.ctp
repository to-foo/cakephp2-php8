<div class="inhalt"><?php echo $this->Session->flash(); ?></div>
<div class="clear"></div>
<?php
if(isset($FormName) && count($FormName) > 0){

	if($saveOK == 4){
		echo $this->element('js/modal_redirect',array('FormName' => $FormName));
		return;
	}
}

if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog($reportnumberID,$evalutionID,$FormName);
	echo $this->JqueryScripte->DialogClose();
	}
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
