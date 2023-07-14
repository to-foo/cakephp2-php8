<div class="modalarea">
<h2><?php echo __('Order', true).' '.$orders['Order']['auftrags_nr'] . ' - ' . $testingmethods[$testingmethodID]; ?></h2>
<?php  
echo $statusDiscError; 

echo $this->Form->input('Jump',array(
		'class' => 'editprice choose', 
		'label' => false, 
		'options' => $testingmethods, 
		'empty' => __('Select Testingmethod', true),
		'div' => false,
	)
);

?>
<div class="invoice detail">
<div class="clear edit"></div>
<div class="clear"></div>
</div> 
<div class="clear" id="testdiv"></div>
<div class="reportnumbers  detail">
<div class="related accordion">
</div>

<?php
echo '<h3 class="link">';
echo '<a href="javascript:" rel="toggle0" class="toggle up" title="'.__('Hide', true).'"><span>';
echo 'Übersicht Auftragsdaten ';
echo '</span></a></h3>';
echo '<table cellpadding="0" cellspacing="0" class="toggle0">';
echo '<tr><td class="related" >';
echo $this->ViewData->ShowOrderData($orders,$settings,$locale); 
echo '</td></tr></table>';

echo '<h3 class="link">';
echo '<a href="javascript:" rel="toggle1" class="toggle close" title="'.__('show/Hide', true).'"><span>';
echo 'Übersicht Prüfberichte ('.$statusDisc.') ';
echo '</span></a></h3>';
echo '<table cellpadding="0" cellspacing="0" class="toggle1">';
echo '<tr>';
echo '<td><strong>'.__('Report', true).'</strong></td>';
echo '<td><strong>'.__('Testing team', true).'</strong></td>';
echo '<td><strong>'.__('Begin of test', true).'</strong></td>';
echo '<td><strong>'.__('End of test', true).'</strong></td>';
echo '<td><strong>'.__('Duration of Test', true).'</strong></td>';
echo '<td><strong>'.__('No. of welds', true).'</strong></td>';
echo '<td><strong>'.__('thereof NE-welds', true).'</strong></td>';
echo '<td></td>';
echo '<td><strong>x</strong></td>';
echo '<td><strong>X</strong></td>';
echo '</tr>';

$_time_summary = 0;
$_weldnumber_summary = 0;
$_weldnumber_ne = 0;
//		pr($dataArray);

if(isset($dataArray[$verfahren])){
	foreach($dataArray[$verfahren] as $_dataArray){

	if(count($dataArray) == 0) continue;

	echo '<tr class="';

	if(count($AktivDeaktiv) > 0 && isset($AktivDeaktiv[$_dataArray['infos']['id']])){
		echo 'deaktiv';
	}
	else {
		echo 'altrow';
	}

	echo '">';
	
	echo '<td>';
	echo $this->Html->link(@$_dataArray['infos']['year'].'-'.$_dataArray['infos']['number'], array(
					'controller' => 'reportnumbers', 
					'action' => 'edit', 
					$_dataArray['infos']['topproject_id'],
					$_dataArray['infos']['equipment_type_id'],
					$_dataArray['infos']['equipment_id'],
					$_dataArray['infos']['order_id'],
					$_dataArray['infos']['report_id'],
					$_dataArray['infos']['id']
					),
					array('class' => 'left')
				);
	echo '</td>';
	echo '<td>'.@$_dataArray['personal'].'</td>';
	echo '<td>'.@$_dataArray['beginn'].'</td>';
	echo '<td>'.@$_dataArray['end'].'</td>';
	echo '<td>'.@$_dataArray['time'].' h</td>';
	echo '<td>'.@$_dataArray['weldnumber'].'</td>';
	echo '<td>'.@$_dataArray['weld_ne'].'</td>';
	echo '<td class="hide"><span>'.@$_dataArray['infos']['id'].'</span></td>';
	echo '<td><input title="Prüfbericht aus dieser Abrechnung ausschließen" type="checkbox" class="aktivdeaktiv" value="'.$_dataArray['infos']['id'].'" ';

	if(count($AktivDeaktiv) > 0 && isset($AktivDeaktiv[$_dataArray['infos']['id']])){
	}
	else {
		echo 'checked="checked"';
	}

	echo ' /></td>';
	echo '<td><input title="Prüfbericht aus sämtlichen Abrechnung entfernen" type="checkbox" class="canceln" value="'.$_dataArray['infos']['id'].'" /></td>';
	echo '</tr>';

	if(isset($_dataArray['time'])){
		$_time_summary = $_time_summary + $_dataArray['time'];
	}
	if(isset($_dataArray['weldnumber'])){
		$_weldnumber_summary = $_weldnumber_summary + $_dataArray['weldnumber'];
	}
	if(isset($_dataArray['weld_ne'])){
		$_weldnumber_ne = $_weldnumber_ne + $_dataArray['weld_ne'];
	}
	}
}

echo '<tr class="">';
	echo '<td><strong>Gesamt</strong></td>';
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>'; 
	echo '<td><strong>'.$_time_summary.' h</strong></td>';
	echo '<td><strong>'.$_weldnumber_summary.'</strong></td>';
	echo '<td><strong>'.$_weldnumber_ne.'</strong></td>'; 
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
echo '</tr>';
echo '</table>';

echo '<h3 class="link">';
echo '<a href="javascript:" rel="toggle2" class="toggle" title="'.__('Show/Hide', true).'"><span>';
echo __('Overview Testing areas / Welds', true).' ('.$statusDisc.') ';
echo '</span></a></h3>';

echo '<table cellpadding="0" cellspacing="0" class="toggle2">';

echo '<tr><td colspan="8">';

if($WeldsTotal > $WeldsComplet){
	echo '<p class="hinweis">'.$WeldsComplet.' '.__('of', true).' '.$WeldsTotal.' '.__('Welds are ready for invocation. Invocation can be saved when all welds are ready.').'</p>';
	echo '<p class="hinweis">'.__('For invocation dimensions must be entered into report.').'</p>';
}
else {
	echo '<p class="hinweis">'.$WeldsComplet.' '.__('of', true).' '.$WeldsTotal.' '.__('welds are ready for invocation.', true).'</p>';
}
echo '</td></tr>';
//pr($InvoiceQuerys);
if(isset($InvoiceQuerys)){
	foreach($InvoiceQuerys as $key1 => $_InvoiceQuerys){
		echo '<tr><td colspan="8"><strong>'.$InvoiceQuerysLabel[$key1]['label'].'</strong></td></tr>';
//				echo '<tr><td colspan="8">'.$InvoiceQuerysLabel[$key1][$key2]['label'].'</td></tr>';
				echo '<tr>';
				echo '<td>'.__('Amount', true).'</td>';
				echo '<td>'.__('Dimensions', true).'</td>';
				echo '<td>'.__('Position', true).'</td>';
				echo '<td>'.__('Unit price', true).'</td>';
				echo '<td>'.__('Total price', true).'</td>';
				echo '<td></td>';
				echo '</tr>';

		foreach($_InvoiceQuerys as $key2 => $__InvoiceQuerys){
			if(isset($__InvoiceQuerys['number']) && $__InvoiceQuerys['number'] > 0){
				echo '<tr class="altrow">';
				echo '<td class="number">'.$__InvoiceQuerys['number'].'</td>';
				echo '<td class="dimension">'.$__InvoiceQuerys['infos']['dimension'].'</td>';
				echo '<td class="select">';
				echo $this->Form->input($key1.'_'.$key2,array(
							'class' => 'createprice', 
							'label' => false, 
							'options' => $__InvoiceQuerys['select'], 
							'empty' => __('Please select', true), 
							'div' => false,
//							'default' => $selectet
							)
						);
								
				echo $this->Form->input('number_'.$key1.'_'.$key2,array(
							'type' => 'hidden', 
							'label' => false, 
							'value' => $__InvoiceQuerys['number']
							)
						);
				echo '</td>';
					
				if(isset($__InvoiceQuerys['selectet'])){
					$defaultArray = array('default' => $__InvoiceQuerys['selectet']);	
				}
				else {
					$defaultArray = array();	
				}

				if(isset($__InvoiceQuerys['infos']['saveselect'])){$selectet = $__InvoiceQuerys['infos']['saveselect'];}
				else {$selectet = null;}

				echo '<td class="singleprice"><span class="singles'.$key1.'_'.$key2.'">';
				if(isset($__InvoiceQuerys['infos']['singleprice'])){
					echo $__InvoiceQuerys['infos']['singleprice'];	
				}
				echo '</span></td>';

				echo '<td class="price"><span class="collect'.$key1.'_'.$key2.'">';
				if(isset($__InvoiceQuerys['infos']['price'])){
					echo $__InvoiceQuerys['infos']['price'];	
				}
				echo '</span></td>';

				echo '<td class="ids hide"><span>';
				foreach($__InvoiceQuerys['ids'] as $ids){
					echo $ids.' ';
				}
		
				echo '<span></td>';

				echo '</tr>';

			}
		}
	}
}
echo '</table>';

echo '<h3 class="link">';
echo '<a href="javascript:" rel="toggle3" class="toggle" title="'.__('Show/Hide', true).'"><span>';
echo __('Overview additional services', true).' ('.$statusDisc.') ';
echo '</span></a></h3>';

echo '<table cellpadding="0" cellspacing="0" class="toggle3">';
echo '<tr>';
echo '<td>';
echo '<span class="round button" id="create">' . __('New additional part', true) . '</span>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '<div id="createinvoice">';
$x = 0;

if(isset($AdditionalServices)){
foreach($AdditionalServices as $_key => $_AdditionalServices){
	foreach($_AdditionalServices as $__AdditionalServices){
		
		if(trim($__AdditionalServices->cell0) != 'Position' && trim($__AdditionalServices->cell0) != null){

			echo '<div class="element" id="number'.$x.'">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr class="altrow">';
			echo '<td class="i1 select">';
			
			echo $this->Form->input('select'.$x,array(
					'class' => 'editprice', 
					'label' => false, 
					'options' => $Invoice, 
					'empty' => '(Bitte wählen)',
					'div' => false,
					'default' => trim($__AdditionalServices->cell0)
					)
				);

			echo '</td>';
			echo '<td class="i2" style="width:15%">'.trim($__AdditionalServices->cell1).'</td>';
			echo '<td class="i3 select">';
			
			echo $this->Form->input('examinierer1'.$x,array(
					'class' => 'editprice', 
					'label' => false, 
					'options' => $ExaminiererArray, 
					'empty' => __('Please select', true),
					'div' => false,
					'default' => trim($__AdditionalServices->cell2)
					)
				);
				
			echo '</td>';
			echo '<td class="i4 select">';

			echo $this->Form->input('examinierer2'.$x,array(
					'class' => 'editprice', 
					'label' => false, 
					'options' => $ExaminiererArray, 
					'empty' => __('Please select', true),
					'div' => false,
					'default' => trim($__AdditionalServices->cell3)
					)
				);
			
			echo '</td>';
			echo '<td class="i5 datum" >'.trim($__AdditionalServices->cell4).'</td>';
			echo '<td class="i6">'.trim($__AdditionalServices->cell5).'</td>';
			echo '<td class="i7 number">'.trim($__AdditionalServices->cell6).'</td>';
			echo '<td class="i8">'.trim($__AdditionalServices->cell7).'</td>';
			echo '<td class="i9">'.trim($__AdditionalServices->cell8).'</td>';
			echo '<td class="i10"><a href="javascript:" class="remove" rel="'.$x.'">'.trim($__AdditionalServices->cell9).'</a></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			
			echo '
			<script>
			$(function() {

				$("a.remove").click(function() {

					$("#number" + $(this).attr("rel")).remove();
		
						testcounter = 0;
						$("div.element").each(function(index, value) {
							testcounter++;
						});		
		
					if(testcounter == 0){
						counter = 0;
						conterforajax = 0;
						}
				});

				$("div#number'.$x.' .number").editable("'.Router::url(array('controller'=>'invoices','action'=>'number')).'", { 
					indicator : "<img src=\'img/indicator.gif\'>",
					name   : "numbers",
					id   : "elementid",
					select : true, 
					onblur : "submit",
					submitdata : {
						ajax_true: 1, 
						number: '.$x.',
						invoicesid: $("#select'.$x.'").val()
					},
					cssclass : "editable",
					method : "POST",
				});

				$("div#number'.$x.' .datum").editable("'.Router::url(array('controller'=>'invoices','action'=>'date')).'", { 
					indicator : "<img src=\'img/indicator.gif\'>",
					name   : "date",
					id   : "elementid",
					select : true, 
					onblur : "submit",
					submitdata : {
						ajax_true: 1 
					},
					cssclass : "editable",
					method : "POST",
				});

				$("#select'.$x.'").change(function() {

					$.ajax({
						type	: "POST",
						cache	: false,
						url		: "'.Router::url(array('controller' => 'invoices', 'action' => 'add')).'",
						data	: ({
							ajax_true: 1, 
							number: '.$x.',
							value: $(this).val()
						}),
						success: function(data) {
						$("#number'.$x.'").html(data);
						$("#number'.$x.'").show();
						}
					});
				});
			});
			</script>
			';

			$x++;
			}
		}
}
}
echo '</div>';
echo '<table cellpadding="0" cellspacing="0">';
echo '<tr>';
echo '<td>';
echo '<span class="round button" id="make">'.__('Save billing', true).'</span>';
echo '</td>';
echo '</tr>';
echo '</table>';

?>
</div>
<div class="clear" id="testdiv"></div>
<div class="clear" id="savediv">
<?php
if(isset($PrintLinks)){
	$x = 1;
	foreach($PrintLinks as $_PrintLinks){
		echo $this->Html->link($_PrintLinks, array(
					'controller' => 'invoices', 
					'action' => 'print', $orders['Order']['id'], $x
					),
					array('class' => 'round')
				);
	$x++;
	}
}
?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<script>
$(function() {

$(".toggle0,.toggle1,.toggle2,.toggle3").hide();

$("#Jump").change(function() {
	$.ajax({
		type	: "POST",
		cache	: false,
		url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $testingmethodID, $this->request->projectvars['reportnumberID'])); ?>",
		data	: {
			ajax_true: 1, 
			testingmethodID: $(this).val()
		},
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").show();
		}
	});
});
	
$(".toggle").click(function() {

	$("." + $(this).attr("rel")).slideToggle("slow");
	$(this).toggleClass("close");
});

$(".createprice").change(function() {
		var location = this.getAttribute("id");
		var target1 = ".singles" + location;
		var target2 = ".collect" + location;
		var numberlocation = "#number_" + location;
		var number = $(numberlocation).val();
		var id = $(this).val();


		var data1 = $(this).serializeArray();
		data1.push({name: "ajax_true", value: 1});
		data1.push({name: "id", value: id});

		var data2 = $(this).serializeArray();
		data2.push({name: "ajax_true", value: 1});
		data2.push({name: "number", value: number});
		data2.push({name: "id", value: id});
		data2.push({name: "keys", value: location});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoicedata')); ?>",
			data	: data1,
			success: function(data) {
			$(target1).html(data);
			$(target1).show();
			}
		});
		
		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoicedata')); ?>",
			data	: data2,
			success: function(data) {
			$(target2).html(data);
			$(target2).show();
			}
		});
		
		$(this).parent('td').parent('tr').removeClass('error')
		return false;	
		
	});

$(".aktivdeaktiv").click(function() {

	var data = $("#fakeform").serializeArray();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "aktiv_deaktiv", value: $(this).val()});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'], 2)); ?>",
		data	: data,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").show();
		}
	});
})

$(".canceln").click(function() {

	checkCanceln = confirm("Mit dieser Aktion erhält dieser Prüfbericht den Status \"Abgerechnet\", ohne bei der Abrechnung berücksichtigt zu werden.");
	if (checkCanceln == false) {
		$(this).css("checked","");
		return false;	
	}

	var data = $("#fakeform").serializeArray();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "canceln", value: $(this).val()});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'], 2)); ?>",
		data	: data,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").show();
		}
	});
});

$("#make").click(function() {

		var table1 = $(this).serializeArray();
		var billingcounter = 0;
		var StopScript = 0;
		
		$("table.toggle1 tr.altrow td").each(function(index, value) {
			table1.push({name: "1t"+billingcounter, value: $(this).text()});
			billingcounter++;
		});		

		var billingcounter = 0;

		$("table.toggle2 tr.altrow td").each(function(index, value) {
			if($(this).attr("class") == "select"){
				table1.push({name: "2t"+billingcounter, value: $(this).children().val()});
				
				// Wenn die Positon nicht ergänzt wurde
				if($(this).children().attr("class") == "createprice" && $(this).children().val() == ''){
					$(".toggle2").show();
					StopScript = 1;
					$(this).parent('tr').addClass('error');
				}
			}
			else {
				table1.push({name: "2t"+billingcounter, value: $(this).text()});

			}
			billingcounter++;
		});	
		var billingcounter = 0;

		if(StopScript == 1){
			alert("Bitte ergänzen Sie die fehlenden Angaben.");
			return false;
		}

		$("#createinvoice table tr.altrow td").each(function(index, value) {
			if($(this).children().attr("class") == "editprice"){
				table1.push({name: "3t"+billingcounter, value: $(this).children().val()});
			}
			else {
				table1.push({name: "3t"+billingcounter, value: $(this).text()});
			}
			billingcounter++;
		});		

		var billingcounter = 0;

		table1.push({name: "ajax_true", value: 1});
		table1.push({name: "testingmethodID", value: <?php echo $this->request->projectvars['reportID'];?>});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'save', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'])); ?>",
			data	: table1,
			success: function(data) {
			$("#savediv").html(data);
			$("#savediv").show();
			}
		});
});

counter = 0;
conterforajax = 0;

$("#create").click(function() {
				
		$("#createinvoice .element").each(function () {
			counter++;
		});

		if(counter == 0){
			$('<div class="element" id="number'+counter+'"></div>').prependTo('#createinvoice');
			insertAfterElement = "#number"+counter;
			conterforajax = counter;
			counter++;
		}
		else {			
			$('<div class="element" id="number'+counter+'"></div>').insertAfter('#'+$(".element").last().attr('id'));
			insertAfterElement = "#number"+counter;
			conterforajax = counter;
			counter++;
		}
		
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'create', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'])); ?>",
			data	: {ajax_true: 1, number: conterforajax},
			success: function(data) {
			$(insertAfterElement).html(data);
			$(insertAfterElement).show();
			}
		});
		
	});

});
</script>
