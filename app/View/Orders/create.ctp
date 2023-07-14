<div class="modalarea">
<h2><?php echo __('Create order'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<div class="clear uploadform">
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose(1.5);
	echo '</div>';
	echo $this->JqueryScripte->ModalFunctions();
	return;
}
?>

<?php

echo $this->Form->create('Order', array('type' => 'file'));
echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('file', array(
'type' => 'file',
'label' => false, 'div' => false,
'class' => 'fileUpload',
'multiple' => 'multiple'
));	
echo $this->Form->button('Upload', array('type' => 'submit', 'id' => 'px-submit', 'class' => 'upload'));
echo $this->Form->button('Clear', array('type' => 'reset', 'id' => 'px-clear', 'class' => 'upload'));


//echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('upload_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('fileselect', array('type' => 'hidden', 'value' => 1));

echo $this->Form->end();
?>
</div>
<div class="clear current_content">
<?php 
if(isset($orders)){
		
	echo '<div class="hint"><p>';
	echo __('Anzahl der gefunden Datensätze',true) . ': ' . count($orders['Order']);
	echo '</p>';
	if($deleted_orders == 1){
		echo $deleted_orders .' ' . __('Datensatz wurde entfernt, da die Auftragsnummer schon vorhanden ist.');
	}
	if($deleted_orders > 1){
		echo $deleted_orders .' ' . __('Datensätze wurde entfernt, da die Auftragsnummern schon vorhanden sind.');
	}
	echo '</div>';

	foreach($orders['Order'] as $_key => $_orders){
		$data['Order'] = $_orders;
		
		if(!isset($data['Order']['auftrags_nr'])) {
			echo '<div class="error"><p>';
			echo __('The uploaded file is not compatible.',true);
			echo ' ';
			echo __('No orders could be found.',true);
			echo '</p></div>';
			break;
		}
		
		echo '<div class="import_order order_' . $_key . '">';
		echo '<h3>';
		echo __('Order') . ' ' . $data['Order']['auftrags_nr'];
		echo '</h3>';
		echo $this->ViewData->ShowDataList($arrayData,$locale,'Order','dl',$data);
		echo '</div>';
	}

	echo $this->Html->Link('Aufträge speichern',array_merge(array('controller' => 'orders', 'action' => 'create'),$this->request->projectvars['VarsArray']),array('class' => 'round mymodal'));	
}
?>

</div>		
</div>
<div class="clear" id="testdiv"></div>

<?php $url = $this->Html->url(array_merge(array('controller' => 'orders', 'action' => 'create'),$this->request->projectvars['VarsArray']));?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>

<script type="text/javascript">

$(function(){
	
//	$("#dialog div.modalarea table#csvupload").css('width',$("#dialog div.modalarea").width() + 'px');
	
	$('.fileUpload').fileUploader({
		limit: 1,
		allowedExtension: 'csv',
		afterUpload: function(data) {
//			var data = $(".fakeform").serializeArray();
			var data = [];
			data.push({name: "ajax_true", value: 1});
			data.push({name: "show_data", value: 1});
			$.ajax({
				type	: "POST",
				cache	: true,
				url		: '<?php echo $url; ?>',
				data	: data,
				success: function(data) {
					$("#dialog").html(data);
					$("#dialog").show();
				}
			});
		},
	});
	
	$("a#closethismodal").click(function() {
		$("#dialog").dialog("close");
		return false;	
	});	
});
</script>
