		<td class="select">
		<?php
		echo $this->Form->input('select_'.$number,array(
					'name' => 'data[Additional]['.$number.'][Invoice]',
					'class' => 'editprice', 
					'label' => false, 
					'options' => $Invoice, 
					'empty' => '(Bitte wählen)',
					'div' => false,
					'default' => $thisInvoice['Invoice']['id']
					)
				);
		
		?>
		</td>
		<td class="number_hide" >
		<?php
		echo $this->Form->input('data_additional_'.$number.'_number',array(
					'name' => 'data[Additional]['.$number.'][Number]',
					'type' => 'hidden', 
					'label' => false, 
					'div' => false,
					)
				);
		
		?>
        
        </td>
		<td class="stk"><?php echo $thisInvoice['Invoice']['me'];?></td>
		<td class="number" title="<?php echo __('Edit');?>"><?php echo $thisInvoice['Invoice']['maenge'];?></td>
		<td class="singleprice"><?php echo $thisInvoice['Invoice']['preis'];?></td>
		<td class="price"><?php echo $thisInvoice['Invoice']['preis'];?></td>
		<td class="actions"><a href="javascript:" class="icon icon_delete">remove</a></td>
<script>
$(function() {
	$("#number<?php echo $number;?> .actions a.icon_delete").click(function() {
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

	$(".number").editable("<?php echo Router::url(array_merge(array('controller' => 'invoices', 'action' => 'number'),$this->request->projectvars['VarsArray'])); ?>", { 
		indicator : "<img src='img/indicator.gif'>",
		name   : "data[Additional][number]",
		id   : 'elementid',
		select : true, 
		onblur : 'submit',
		submitdata : {
				ajax_true: 1,
				"data[Additional][row]": <?php echo $number;?>,
				"data[Additional][id]": $("#select_<?php echo $number;?>").val()
			},
		cssclass : "editable",
		method : 'POST',
	});

	
/*
	$(".datum").editable("<?php echo Router::url(array_merge(array('controller' => 'invoices', 'action' => 'date'),$this->request->projectvars['VarsArray'])); ?>", { 
		indicator : "<img src='img/indicator.gif'>",
		name   : 'date',
		id   : 'elementid',
		select : true, 
		onblur : 'submit',
		submitdata : {
				ajax_true: 1
			},
		cssclass : "editable",
		method : 'POST',
	});
*/	
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
