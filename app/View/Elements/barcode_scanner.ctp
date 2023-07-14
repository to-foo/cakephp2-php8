<?php
if(Configure::read('BarcodeDeviceScanner') == false) return;

echo $this->Html->link('notes_input_link','javascript:',array('id' => 'notes_input_link','title' => __('Scann function is deactiv.',true),'class' => 'icon icon_qrcode icon_qrcode_deactive'));
echo '<div id="notes_summary"></div>';
echo '<div id="hidden_for_you">';
echo $this->Form->textarea('notes_input');
echo '</div>';
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
	
	$("textarea#notes_input").focus();
	
	if($("textarea#notes_input").is(":focus")){
		$("a#notes_input_link").removeClass("icon_qrcode_deactive");
		$("a#notes_input_link").addClass("icon_qrcode_active");
		$("a#notes_input_link").prop("title","Scanner is active");
	}

	if(!($("textarea#notes_input").is(":focus"))){
		$("a#notes_input_link").removeClass("icon_qrcode_deactive");
		$("a#notes_input_link").addClass("icon_qrcode_error");
		$("a#notes_input_link").prop("title","Please click to activate the scanner");
	}

	$("textarea#notes_input").focusout(function() {
		$("a#notes_input_link").removeClass("icon_qrcode_active");
		$("a#notes_input_link").removeClass("icon_qrcode_error");
		$("a#notes_input_link").addClass("icon_qrcode_deactive");
		$("a#notes_input_link").prop("title","Please click to activate the scanner");
	});
	
	$("#hidden_for_you").css("width","1px");
	$("#hidden_for_you").css("height","1px");
	$("#hidden_for_you").css("overflow","hidden");
	
	$("a#notes_input_link").click(function() {
		$("#notes_summary").empty();
		$("textarea#notes_input").val("");
		$("textarea#notes_input").focus();
		$("a#notes_input_link").removeClass("icon_qrcode_deactive");
		$("a#notes_input_link").removeClass("icon_qrcode_error");
		$("a#notes_input_link").addClass("icon_qrcode_active");
		$("a#notes_input_link").prop("title","Scanner is active");
	});
	
	$("textarea#notes_input").on("keyup", function() {
//		console.log(FindEnd($(this).val()));
		var barcode = FindEnd($(this).val());
		
		if(barcode.indexOf('ende') > -1){

			barcode = RemoveEnd(barcode);
//			$("#notes_summary").text(barcode);
			$(':focus').blur();

			var data = $("#fakeform").serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "barcode", value: barcode});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?php echo $this->Html->url(array('action' => 'barcode'));?>",
				data	: data,
				success: function(data) {
		    		$("#notes_summary").html(data);
		    		$("#notes_summary").show();
				}
			});
			
		}		
	});
});
</script>

