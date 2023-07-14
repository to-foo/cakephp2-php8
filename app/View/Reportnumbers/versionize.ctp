<div class="modalarea">
<h2><?php echo __('Revision',true);?></h2>
<div class="error">
<p>
<strong>
<?php if(isset($message)) echo '<p>' . $message . '</p>';?>
<?php echo __('Will you create a revision of this report?',true);?>
</strong>
</p><p>
<?php echo __('This function deactivated the current report and create a new report with a new report no.',true);?><br />
<?php echo __('All changes will be documented in the new report.',true);?>
</p>
</div>
<?php echo $this->Form->create('Reportnumber',array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id',array('value' => $this->request->reportnumberID));?>
<?php echo $this->Form->input('Cancel',array('type' => 'reset','value' => __('Cancel', true),'label' => false,'div' => false)); ?>
<?php echo $this->Form->input(__('Create revision', true),array('type' => 'submit','value' => __('Save', true),'label' => false,'div' => false)); ?>
<?php echo $this->Form->end(); ?>
</div>
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose(1.5);
	}
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<script type="text/javascript">
$("#ReportnumberCancel").click(function() {
	$(document).ready(function(){
		$("#dialog").dialog();
		if ($("#dialog").dialog("isOpen") === true) {
			$("#dialog").dialog("close");
		}
		});
	});

</script>
