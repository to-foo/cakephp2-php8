<?php
if(Configure::read('BarcodeDeviceScanner') == false) return;
echo '<dd id="notes_summary"></dd>';
$barcodelink = $this->Html->link('notes_input_link','javascript:',array('id' => 'notes_input_link','title' => __('Scann function is deactiv.',true),'class' => 'icon icon_qrcode icon_qrcode_deactive'));
?>
<script type="text/javascript">
$(document).ready(function(){

	function FindEnd(str) {
    	var breakTag = "ende";
    	return (str + "").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, "$1" + breakTag + "$2");
	}

	function RemoveEnd(str) {
    	var breakTag = "ende";
    	return (str + "").replace(breakTag, "");
	}
	
	$("#DeviceBarcode").wrap( "<dd id=\"hidden_for_you\"></dd>" );
	$('<?php echo $barcodelink;?>').insertAfter($("#hidden_for_you"));
	
	$("#hidden_for_you").css("width","1px");
	$("#hidden_for_you").css("height","1px");
	$("#hidden_for_you").css("overflow","hidden");

	if($("dd#hidden_for_you input").val() != 0){
//		alert($("dd#hidden_for_you input").val());
	}

	$("a#notes_input_link").click(function() {
		$("#notes_summary").empty();
		$("dd#hidden_for_you input").val("");
		$("dd#hidden_for_you input").focus();
		$("a#notes_input_link").removeClass("icon_qrcode_deactive");
		$("a#notes_input_link").removeClass("icon_qrcode_error");
		$("a#notes_input_link").addClass("icon_qrcode_active");
		$("a#notes_input_link").prop("title","Scanner is active");
	});	
	

//	$("dd#hidden_for_you input").on("keyup", function() {});
});
</script>
