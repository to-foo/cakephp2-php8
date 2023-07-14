		<td>
		<?php
		
		echo $this->Form->input('select_'.$number,array(
					'class' => 'createprice', 
					'label' => false, 
					'options' => $Invoice, 
					'empty' => '(Bitte wählen)',
					'default' => null
					)
				);
		
		?>
		</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
		<td class="actions"><a href="javascript:" class="icon icon_delete">remove</a></td>
<script>
$(function() {

	$("#number<?php echo $number;?> .actions a").click(function() {
		$("#number<?php echo $number;?>").remove();
		
		testcounter = 0;
		$("div.element").each(function(index, value) {
			testcounter++;
		});		
		
		if(testcounter == 0){
			counter = 0;
			conterforajax = 0;
		}
	});
	
$("#select_<?php echo $number;?>").change(function() {
				
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('controller' => 'invoices', 'action' => 'add'),$this->request->projectvars['VarsArray'])); ?>",
			data	: ({
				ajax_true: 1, 
				number: <?php echo $number;?>,
				value: $(this).val()
				}),
			success: function(data) {
			$("#number<?php echo $number;?>").html(data);
			$("#number<?php echo $number;?>").show();
			}
		});
	});
});
</script>
