<div class="modalarea">
<h2><?php echo __('Order', true).' '.$orders['Order']['auftrags_nr'] . ' - ' . $testingmethods[$this->request->projectvars['reportID']]; ?></h2>
<?php
echo '<p>';
echo $statusDiscError; 

echo $this->Form->input('Jump',array(
		'class' => 'editprice choose', 
		'label' => false, 
		'options' => $testingmethods, 
		'empty' => __('Select Testingmethod', true),
		'selected' => $this->request->projectvars['reportID'],
		'div' => false,
	)
);

echo '</p><p class="clear">';

echo $this->Form->input('AccountsSelect',array(
		'class' => 'editprice view', 
//		'label' => __('Show invocation parts', true), 
		'label' => false, 
		'options' => $laufendeNrArray, 
		'default' => $lf_number,
		'empty' => __('Select invoices', true),
		'div' => false,
	)
);
/*
echo $this->Form->input('NewAditionalSelect',array(
		'class' => 'editprice add', 
//		'label' => __('Create new additional service', true), 
		'label' => false, 
		'options' => $laufendeNrArray, 
		'div' => false,
		'empty' => __('Add new aditional', true),
	)
);

echo $this->Form->input('AccountsPrint',array(
		'class' => 'editprice print', 
//		'label' => __('print invocation', true), 
		'label' => false, 
		'options' => $laufendeNrArray, 
		'empty' => __('Print invoices', true),
		'div' => false,
	)
);
*/
echo $this->Form->input('Csv',array(
		'class' => 'editprice csv', 
//		'label' => __('Export in Excel-Format', true), 
		'label' => false, 
		'options' => $laufendeNrArray, 
		'empty' => __('Export invoices', true),
		'div' => false,
	)
);
echo '</p>';
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
echo '<a href="javascript:" rel="toggle1" class="toggle close" title="'.__('Show/Hide', true).'"><span>';
echo 'Übersicht Prüfberichte ('.$statusDisc.') ';
echo '</span></a></h3>';
echo '<table cellpadding="0" cellspacing="0" class="toggle1">';
echo '<tr>';
echo '<td><strong>'.__('Report', true).'</strong></td>';
echo '<td><strong>'.__('Radiation source', true).'</strong></td>';
echo '<td><strong>'.__('No. of welds', true).'</strong></td>';
echo '<td><strong>'.__('thereof NE-welds', true).'</strong></td>';
echo '<td><strong>'.__('Films 10x24', true).'</strong></td>';
echo '<td><strong>'.__('Films 10x48', true).'</strong></td>';
echo '<td><strong>'.__('created', true).'</strong></td>';
echo '<td><strong>'.__('Creator', true).'</strong></td>';
echo '</tr>';

$_time_summary = 0;
$_weldnumber_summary = 0;
$_weldnumber_ne = 0;
$_film_10_24_summary = 0;
$_film_10_48_summary = 0;

if(isset($statisticsgenerall)){
	
	foreach($statisticsgenerall as $_dataArray){
		echo '<tr class="altrow">';
		echo '<td>';
		echo $this->Html->link(@$_dataArray['Statisticsgenerall']['reportnumber'], array(
					'controller' => 'reportnumbers', 
					'action' => 'edit', 
					$_dataArray['Reportnumber']['topproject_id'],					
					$_dataArray['Reportnumber']['equipment_type_id'],					
					$_dataArray['Reportnumber']['equipment_id'],					
					$_dataArray['Reportnumber']['order_id'],					
					$_dataArray['Reportnumber']['report_id'],					
					$_dataArray['Reportnumber']['id']
					),
					array('class' => 'left round')
				);
		echo '</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['source'].'</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['welds'].'</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['welds_ne'].'</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['films_10_x_24'].'</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['films_10_x_48'].'</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['modified'].'</td>';
		echo '<td>'.@$_dataArray['Statisticsgenerall']['user_id'].'</td>';
		echo '</tr>';
		
		if(isset($_dataArray['Statisticsgenerall']['examination_time'])){
			$_time_summary = $_time_summary + $_dataArray['Statisticsgenerall']['examination_time'];
		}
		if(isset($_dataArray['Statisticsgenerall']['welds'])){
			$_weldnumber_summary = $_weldnumber_summary + $_dataArray['Statisticsgenerall']['welds'];
		}
		if(isset($_dataArray['Statisticsgenerall']['welds_ne'])){
			$_weldnumber_ne = $_weldnumber_ne + $_dataArray['Statisticsgenerall']['welds_ne'];
		}
		if(isset($_dataArray['Statisticsgenerall']['films_10_x_24'])){
			$_film_10_24_summary = $_film_10_24_summary + $_dataArray['Statisticsgenerall']['films_10_x_24'];
		}
		if(isset($_dataArray['Statisticsgenerall']['films_10_x_48'])){
			$_film_10_48_summary = $_film_10_48_summary + $_dataArray['Statisticsgenerall']['films_10_x_48'];
		}
		
	}

	echo '<tr class="altrow">';
		echo '<td><strong>Gesamt</strong></td>';
		echo '<td>&nbsp;</td>';
		echo '<td><strong>'.$_weldnumber_summary.'</strong></td>';
		echo '<td><strong>'.$_weldnumber_ne.'</strong></td>'; 
		echo '<td><strong>'.$_film_10_24_summary.'</strong></td>';
		echo '<td><strong>'.$_film_10_48_summary.'</strong></td>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
	echo '</tr>';
}
echo '</table>';

echo '<h3 class="link">';
echo '<a href="javascript:" rel="toggle2" class="toggle" title="'.__('Show/Hide', true).'"><span>';
echo 'Übersicht Prüfbereiche/Schweißnähte ('.$statusDisc.') ';
echo '</span></a></h3>';

echo '<table cellpadding="0" cellspacing="0" class="toggle2">';

if(isset($statisticsstandard)){
	foreach($statisticsstandard as $key1 => $_InvoiceQuerys){
		if($key1 == 'price_total'){
			echo '<tr>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td><strong>'.$_InvoiceQuerys.'</strong></td>';
//			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '</tr>';
			break;
		}
	
//		echo '<tr><td colspan="10"><strong>'.$InvoiceQuerysLabel[$key1]['label'].'</strong></td></tr>';
		foreach($_InvoiceQuerys as $key2 => $__InvoiceQuerys){
			
			if($key1 == 'price_total'){
				
				echo '<tr>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td><strong>';
				echo $__InvoiceQuerys;
				echo '</strong></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '</tr>';
				break;
			}
			
			echo '<tr><th colspan="10">'.$InvoiceQuerysLabel[$key1][$key2]['label'].'</th></tr>';
				echo '<tr class="altrow">';
				echo '<th>'.__('Dimensions', true).'</th>';
				echo '<th>'.__('Image-No.', true).'</th>';
				echo '<th>'.__('Radiation source', true).'</th>';
				echo '<th>'.__('Position', true).'</th>';
				echo '<th>'.__('Unit price', true).'</th>';
				echo '<th>'.__('Amount', true).'</th>';
				echo '<th>'.__('Total price', true).'</th>';
//				echo '<th>'.__('Creation time', true).'</th>';
				echo '<th>'.__('Editor', true).'</th>';
				echo '<th></th>';
				echo '</tr>';

			foreach($__InvoiceQuerys as $key3 => $___InvoiceQuerys){
				foreach($___InvoiceQuerys as $key4 => $____InvoiceQuerys){

					if(isset($____InvoiceQuerys['number']) && $____InvoiceQuerys['number'] > 0){
						echo '<tr class="';
						
						if($____InvoiceQuerys['deaktiv'] == 1){
							echo __('deactive', true);
						}
						else {
							echo 'altrow';
						}

						echo '">';
						echo '<td>'.$____InvoiceQuerys['dimension'].'</td>';
						echo '<td>'.$____InvoiceQuerys['picture_no'].'</td>';
						echo '<td>'.$____InvoiceQuerys['source'].'</td>';
						echo '<td>'.$____InvoiceQuerys['position'].'</td>';
						echo '<td>'.$____InvoiceQuerys['price'].'</td>';
						echo '<td>'.$____InvoiceQuerys['number'].'</td>';
						echo '<td>'.$____InvoiceQuerys['price_total'].'</td>';
//						echo '<td>'.$____InvoiceQuerys['modified'].'</td>';
						echo '<td>'.$____InvoiceQuerys['user_id'].'</td>';
						echo '<td class="actions">';
						echo $this->Html->link(__('Edit'), array('action' => 'editstandard', 
						$this->request->projectvars['projectID'], 
						$this->request->projectvars['equipmentType'], 
						$this->request->projectvars['equipment'], 
						$this->request->projectvars['orderID'], 
						$this->request->projectvars['reportID'], 
						$this->request->projectvars['reportnumberID'], 
						$____InvoiceQuerys['id'], 
						$____InvoiceQuerys['laufende_nr']
						),
						array('class'=>'icon icon_edit mymodal', 'title'=> __('Edit', true)));
						echo '</td>';
						echo '</tr>';
					}
				}
			}
		}
	}
}

echo '</table>';
echo '<h3 class="link">';
echo '<a href="javascript:" rel="toggle3" class="toggle" title="'.__('Show/Hide', true).'"><span>';
echo __('Overview additional services', true).' ('.$statusDisc.') ';
echo '</span></a></h3>';
echo '<table cellpadding="0" cellspacing="0" class="toggle3"><tbody>';
	echo '<tr class="altrow">';
	echo '<th>'. __('Created', true) .'</th>';
	echo '<th>'. __('Discription', true) .'</th>';
	echo '<th>'. __('Amount', true) .'</th>';
	echo '<th>'. __('Amount', true) .'</th>';
	echo '<th>'. __('Unit price', true) .'</th>';
	echo '<th>'. __('Total price', true) .'</th>';
	echo '<th>'. __('Editor', true) .'</th>';
	echo '<th>'. __('Modification time', true) .'</th>';
	echo '<th>&nbsp;</th>';
	echo '</tr>';
foreach($statisticsadditional as  $_key => $_statisticsadditional){

	if(!isset($_statisticsadditional['Statisticsadditional']['id'])){	
		echo '<tr class="altrow">';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td><strong>';
		echo $_statisticsadditional['Statisticsadditional']['price_total'];
		echo '</strong></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
		break;
	}
	
	echo'<tr class="altrow">';
	echo'<td class="i0">'.$_statisticsadditional['Statisticsadditional']['created'].'</td>';
	echo'<td class="i0">'.$_statisticsadditional['Statisticsadditional']['position'].'</td>';
	echo'<td class="i4">'.$_statisticsadditional['Statisticsadditional']['amount'].'</td>';
	echo'<td class="i5">'.$_statisticsadditional['Statisticsadditional']['number'].'</td>';
	echo'<td class="i6">'.$_statisticsadditional['Statisticsadditional']['price'].'</td>';
	echo'<td class="i7">'.$_statisticsadditional['Statisticsadditional']['price_total'].'</td>';
	echo'<td class="i9">'.$_statisticsadditional['Statisticsadditional']['user_id'].'</td>';
	echo'<td class="i8">'.$_statisticsadditional['Statisticsadditional']['modified'].'</td>';
	echo'<td class="i11 actions">';

/*
	echo $this->Html->link(__('Edit'), array(
										'action' => 'editadditional',
										$this->request->projectvars['projectID'], 
										$this->request->projectvars['equipmentType'], 
										$this->request->projectvars['equipment'], 
										$this->request->projectvars['orderID'], 
										$this->request->projectvars['reportID'], 
										$this->request->projectvars['reportnumberID'], 
										$_statisticsadditional['Statisticsadditional']['id'],
										$_statisticsadditional['Statisticsadditional']['laufende_nr']
										)
										, 
									array(
										'class'=>'icon icon_edit mymodal'
										)
									);
*/									
	echo $this->Html->link(__('Delete'), array('action' => 'deleteadditional', 
										$this->request->projectvars['projectID'], 
										$this->request->projectvars['equipmentType'], 
										$this->request->projectvars['equipment'], 
										$this->request->projectvars['orderID'], 
										$this->request->projectvars['reportID'], 
										$this->request->projectvars['reportnumberID'], 
										$_statisticsadditional['Statisticsadditional']['id'],
										$_statisticsadditional['Statisticsadditional']['laufende_nr']
										),
										array('class'=>'icon icon_delete mymodal deleteadditional')
										);

	echo'</td>';
	echo'</tr>';
}
echo '</tbody></table>';

echo '<table cellpadding="0" cellspacing="0" class="toggle4"';
echo '<tr>';
echo '<td class="invoisedropdown">';
echo '</td>';
echo '<td class="invoisedropdown">';


echo '</td>';
echo '<td>';
echo '</span></td>';
echo '</tr>';
echo '</table>';
echo '<div class="heigth">';
echo 'Gesamtsumme: '.$price_total_total;
echo '</div>';
echo '</div>';
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<script>
$(function() {
	
$(".toggle0,.toggle1,.toggle2,.toggle3").hide();

$(".toggle").click(function() {

	$("." + $(this).attr("rel")).slideToggle("slow");
	$(this).toggleClass("close");
});

$("#NewAditionalSelect").change(function() {
	
	if($(this).val() == 0){
		return false;
	}
		
	var modalheight = Math.ceil(($(window).height() * 90) / 100);
	var modalwidth = Math.ceil(($(window).width() * 90) / 100);

	var dialogOpts = {
		modal: false,
		width: modalwidth,
		height: modalheight,
		autoOpen: false,
		draggable: true,
		resizeable: true
		};

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'editadditional', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'], $this->request->projectvars['reportnumberID'],0)); ?>",
		data	: {
			ajax_true: 1, 
			laufende_nr: $(this).val()
		},
		success: function(data) {
			$("#dialog").dialog(dialogOpts);
			$("#dialog").html(data);
			$("#dialog").dialog("open");
		}
	});
	
});

$("#AccountsSelect").change(function() {
	$.ajax({
		type	: "POST",
		cache	: false,
		url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'], $this->request->projectvars['reportnumberID'])); ?>",
		data	: {
			ajax_true: 1, 
			lf_number: $(this).val()
		},
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").show();
		}
	});
});

$("#AccountsPrint").change(function() {
//alert($(this).val());
//return false;	
	var url = "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'printinvoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'])); ?>/" + $(this).val();

	if(url){
		window.location = url;
	}
	return false;
});

$("#Jump").change(function() {
	$.ajax({
		type	: "POST",
		cache	: false,
		url		: "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'invoice', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'], $this->request->projectvars['reportnumberID'])); ?>",
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

$("#Csv").change(function() {

	if($(this).val() == ''){return false;}
	
	var url = "<?php echo Router::url(array('controller' => 'invoices', 'action' => 'export', $this->request->projectvars['projectID'], $this->request->projectvars['equipmentType'], $this->request->projectvars['equipment'], $this->request->projectvars['orderID'], $this->request->projectvars['reportID'])); ?>/" + $(this).val();

	if(url){
		window.location = url;
	}
	return false;
});

<?php
if(isset($aditionalsave)){
	if($aditionalsave == 3 || $aditionalsave == 6){
		echo '$(".toggle3").show();';
	}
	if($aditionalsave == 2){
		echo '$(".toggle2").show();';
	}
}
?>
});
</script>