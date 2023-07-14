<div class="modalarea">
<h2><?php echo __('Change status', true); ?></h2> 
<?php echo $this->Form->create('Reportnumber', array('class' => 'dialogform')); ?>
<div class="hint"><p><?php echo __('What do you want to do ?', true); ?></p></div>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('controller', array('type' => 'hidden', 'name' => 'controller', 'value' => $this->request->data['controller']));
		echo $this->Form->input('action', array('type' => 'hidden', 'name' => 'action', 'value' => $this->request->data['action']));
	?>
	<legend class="links">
    	<?php
		echo $this->Html->Link($supervisor_text,array('action' => 'status2'),array('class' => 'round', 'id' => 'ReportnumberSupervisorTrue'));
		echo $this->Html->Link(__('revoke examiner release'),array('action' => 'status2'),array('class' => 'round', 'id' => 'ReportnumberExaminerTrue'));
		?>
<!--        
		<a class="mymodal" title="Search for orders" href="/dekra_09_03_2015/orders/search/19/1/338/4/681/0/0">test 1</a>
		<a class="mymodal" title="Search for orders" href="/dekra_09_03_2015/orders/search/19/1/338/4/681/0/0">test 2</a>
-->
		</legend>   
</fieldset>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div> 
<?php 
//echo $this->Form->input('supervisor_true',array('type' => 'reset','value' => $supervisor_text,'label' => false,'div' => true));
//echo $this->Form->end(__('revoke examiner release')); 
echo $this->Form->end(null); 
?>
</div><a 
<div class="clear" id="testdiv"></div>
<?php 
if(isset($reportnumberID) && $reportnumberID > 0){
	echo $this->JqueryScripte->RefreshAfterDialog($this->request->data['Reportnumber']['id'],$evalutionID,$FormName);
	echo $this->JqueryScripte->DialogClose();
	} 
?>

<script type="text/javascript">
	$(document).ready(function(){

					$("#ReportnumberSupervisorTrue").click(function() {
						var data = $("#ReportnumberStatus2Form").serializeArray();
						data.push({name: "data[Reportnumber][status]", value: <?php echo $this->request->data['Reportnumber']['status'];?>});
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: $("#ReportnumberStatus2Form").attr("action"),
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						});						

					$("#ReportnumberExaminerTrue").click(function() {
						var data = $("#ReportnumberStatus2Form").serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: $("#ReportnumberStatus2Form").attr("action"),
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						});


	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
