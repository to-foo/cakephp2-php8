<?php
if(Configure::check('BarcodeSearchScanner') == false) return false;
if(Configure::read('BarcodeSearchScanner') == false) return false;

echo $this->Html->link('notes_input_link','javascript:',array('id' => 'notes_input_link','title' => __('Scann function is deactiv.',true),'class' => 'icon icon_barcode icon_barcode_deactive'));
echo '<div id="notes_summary"></div>';
echo '<div id="hidden_for_you">';
echo $this->Form->textarea('notes_input');
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function(){

	$(window).bind('keydown', function(event) {
		if(event.ctrlKey || event.metaKey) {
			switch (String.fromCharCode(event.which).toLowerCase()) {
				case 'b':
				event.preventDefault();
				$("textarea#notes_input").focus();
				$("textarea#notes_input").focus();
				$("a#notes_input_link").removeClass("icon_barcode_deactive");
				$("a#notes_input_link").removeClass("icon_barcode_error");
				$("a#notes_input_link").addClass("icon_barcode_active");
				$("a#notes_input_link").prop("title","<?php echo __('Scanner is active',true);?>");
				break;
			}
		}
	});

	function intervalclear(){ 
		if($("textarea#notes_input").val().length < 16){
			$("textarea#notes_input").val("");
		}
	}
	
//	setInterval(intervalclear, 3000);

	$("textarea#notes_input").focus();

	function activate(){
					$("textarea#notes_input").focus();
					$("a#notes_input_link").removeClass("icon_barcode_deactive");
					$("a#notes_input_link").removeClass("icon_barcode_error");
					$("a#notes_input_link").addClass("icon_barcode_active");
					$("a#notes_input_link").prop("title","<?php echo __('Scanner is active');?>");
	}
	
	if($("textarea#notes_input").is(":focus")){
		$("a#notes_input_link").removeClass("icon_barcode_deactive");
		$("a#notes_input_link").addClass("icon_barcode_active");
		$("a#notes_input_link").prop("title","<?php echo __('Scanner is active',true);?>");
	}

	if(!($("textarea#notes_input").is(":focus"))){
		$("a#notes_input_link").removeClass("icon_barcode_deactive");
		$("a#notes_input_link").addClass("icon_barcode_error");
		$("a#notes_input_link").prop("title","<?php echo __('Please click to activate the scanner',true);?> (<?php echo __('shortcut ctrl+c',true);?>)");
	}

	$("textarea#notes_input").focusout(function() {
		$("a#notes_input_link").removeClass("icon_barcode_active");
		$("a#notes_input_link").removeClass("icon_barcode_error");
		$("a#notes_input_link").addClass("icon_barcode_deactive");
		$("a#notes_input_link").prop("title","<?php echo __('Please click to activate the scanner');?> (<?php echo __('shortcut ctrl+c',true);?>)");
	});
	
	$("#hidden_for_you").css("width","1px");
	$("#hidden_for_you").css("height","1px");
	$("#hidden_for_you").css("overflow","hidden");
	
	$("a#notes_input_link").click(function() {
		$("#notes_summary").empty();
		$("textarea#notes_input").val("");
		$("textarea#notes_input").focus();
		activate();
	});
	
	$("textarea#notes_input").on("keyup", function() {

		if($(this).val().length == 1){
			settimer = setTimeout(intervalclear, 1000);
		}
		
		if($(this).val().length != 16){
			return;
		}
		
		$("#AjaxSvgLoader").show(); 
		
		var barcode = parseInt($(this).val());
		$("textarea#notes_input").val("");

		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "barcode", value: barcode});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo $this->Html->url(array('controller' => 'reportnumbers','action' => 'barcode'));?>",
			data	: data,
			dataType: "json",
			success: function(data) {
		   		if(data.error.length > 0){
					$("textarea#notes_input").val("");
					$("#AjaxSvgLoader").hide(); 
					var alert_text = confirm(data.error);
					if (alert_text == true) activate();
					else activate();
					activate();
					clearTimeout(settimer);
				} 
			
				if(data.url.length > 0){
						
					var post = $(this).serializeArray();
					post.push({name: "ajax_true", value: 1});
	
					$.ajax({
						type	: "POST",
						cache	: false,
						url		: data.url,
						data	: post,
						success: function(data) {
							clearTimeout(settimer);
							$("#container").html(data);
							$("#container").show();
							$("#AjaxSvgLoader").hide(); 
						}
					});
				}
			}
		});
	});
});
</script>

