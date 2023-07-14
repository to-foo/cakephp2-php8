<div class="modalarea">
<h2><?php echo __('Delete additional', true); ?></h2>
<div class="hint"><p><?php echo $message; ?></p></div>
<?php echo $this->Form->create('Statisticsadditional', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->end(__('Delete', true)); ?>
</div>
<?php
/*
if(isset($closeModalreloadContent) && $closeModalreloadContent == true){
	echo'
	<script type="text/javascript">
		$(document).ready(function(){
			
			$("#dialog").dialog("close");
			
			var data = $(this).serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "aditionalsave", value: 6});
		
			$.ajax({
				type	: "POST",
				cache	: true,
				url		: "' . Router::url(array('controller' => 'invoices', 'action' => 'invoice', $urlVars['projectID'], $urlVars['orderID'], $urlVars['orderKat'], $urlVars['testingmethodID'], $urlVars['status'])) . '",
				data	: data,
				success: function(data) {
		    		$("#container").html(data);
		    		$("#container").show();
				}
			});
		});
	</script>
	';
}
*/
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
