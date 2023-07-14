<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
/*
if(isset($PrintLinks)){
	$x = 1;
	foreach($PrintLinks as $_PrintLinks){
		echo $this->Html->link($_PrintLinks, array(
					'controller' => 'invoices', 
					'action' => 'print', $orderID, $x
					),
					array('class' => 'round')
				);
	$x++;
	}
}
*/
?>
<script>
$(function() {

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'], $status)); ?>",
			data	: {ajax_true: 1, number: conterforajax},
			success: function(data) {
			$("#dialog").html(data);
			$("#dialog").show();
			}
	
		});
});
</script>

