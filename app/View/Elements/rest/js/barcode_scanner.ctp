<?php
echo $this->Form->input('RestProgressText',
	array(
		'id' => 'RestProgressText',
		'type' => 'hidden',
		'value' => __("The barcode has been scanned.",true) . ": " . __("Work in progress...",true)
	)
);
echo $this->Form->input('RestProgressTextInput',
	array(
		'id' => 'RestProgressTextInput',
		'type' => 'hidden',
		'value' => __("The entered ticket id is searched.",true) . ": " . __("Work in progress...",true)
	)
);
echo $this->Form->input('RestStartText',
	array(
		'id' => 'RestStartText',
		'type' => 'hidden',
		'value' => __("Waiting for input.",true)
	)
);

?>
<script type="text/javascript">
$(document).ready(function(){


	var timeout_time = 5000;

	$(window).bind('keydown', function(event) {
		if(event.ctrlKey || event.metaKey) {
			switch (String.fromCharCode(event.which).toLowerCase()) {
				case 'b':
				event.preventDefault();
				$("textarea#TicketscanNotesInput").focus();
				$("textarea#TicketscanNotesInput").focus();
				$("a#notes_input_link").removeClass("icon_barcode_deactive");
				$("a#notes_input_link").removeClass("icon_barcode_error");
				$("a#notes_input_link").addClass("icon_barcode_active");
				$("a#notes_input_link").prop("title","<?php echo __('Scanner is active',true);?>");
				$("span#notes_summary").text("<?php echo __('Scanner is active',true);?>");
				break;
			}
		}
	});

	function loadzxing() {

		let selectedDeviceId;
		const codeReader = new ZXing.BrowserBarcodeReader()

		codeReader.getVideoInputDevices().then((videoInputDevices) => {

		const sourceSelect = document.getElementById('sourceSelect')
		selectedDeviceId = videoInputDevices[0].deviceId

		if (videoInputDevices.length > 1) {

			videoInputDevices.forEach((element) => {

				const sourceOption = document.createElement('option')
				sourceOption.text = element.label
				sourceOption.value = element.deviceId
				sourceSelect.appendChild(sourceOption)

			})

			sourceSelect.onchange = () => {

			selectedDeviceId = sourceSelect.value;

		}

		const sourceSelectPanel = document.getElementById('sourceSelectPanel')
		sourceSelectPanel.style.display = 'block'

		}

		document.getElementById('startButton').addEventListener('click', () => {

			let videoel = document.getElementById("video");
			videoel.style.display = "block";

			codeReader.decodeOnceFromVideoDevice(selectedDeviceId, 'video').then((result) => {

				codeReader.reset();

				let videoel = document.getElementById("video");
				videoel.style.display = "none";

				barcode = result.text

				if(barcode.length < 10){
					return;
				}

				$("div.notes_request_message").removeClass("in_progress");
				$("div.notes_request_message").removeClass("in_error");
				$("div.notes_request_message").removeClass("in_success");

				$("div.notes_request_message").addClass("in_progress");
				$("div.notes_request_message").html($("#RestProgressText").val());

				startdisabled();

				var data = new Array();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "barcode", value: barcode});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		:  $("#TicketscanUpdateUrl").val(),
					data	: data,
					dataType: "json",
					success: function(data) {
						requestdata(data);
					},
					error: function (xhr, error) {
						requestdata(error);
					}
				});

			}).catch((err) => {

				$("div.notes_request_message").removeClass("in_progress");
				$("div.notes_request_message").removeClass("in_error");
				$("div.notes_request_message").removeClass("in_success");

				$("div.notes_request_message").addClass("in_error");
				$("div.notes_request_message").html(err);

			})

		})

		document.getElementById('resetButton').addEventListener('click', () => {

			document.getElementById('result').textContent = '';

			let videoel = document.getElementById("video");
			videoel.style.display = "none";

			codeReader.reset();

		})}).catch((err) => {

			$("div.notes_request_message").removeClass("in_progress");
				$("div.notes_request_message").removeClass("in_error");
				$("div.notes_request_message").removeClass("in_success");

				$("div.notes_request_message").addClass("in_error");
				$("div.notes_request_message").html(err);

		})
	}	

	loadzxing();

	function intervalclear(){
		if($("textarea#TicketscanNotesInput").val().length < 10){
			$("textarea#TicketscanNotesInput").val("");

		}
	}

//	setInterval(intervalclear, 3000);

	$("textarea#TicketscanNotesInput").focus();

	function activate(){

		if($("#notes_input_link").hasClass("disabled")) return false;

		$("textarea#TicketscanNotesInput").focus();
		$("a#notes_input_link").removeClass("icon_barcode_deactive");
		$("a#notes_input_link").removeClass("icon_barcode_error");
		$("a#notes_input_link").addClass("icon_barcode_active");
		$("a#notes_input_link").prop("title","<?php echo __('Scanner is active');?>");
		$("span#notes_summary").text("<?php echo __('Scanner is active',true);?>");

		$("div.notes_request_message").removeClass("in_progress");
		$("div.notes_request_message").removeClass("in_error");
		$("div.notes_request_message").removeClass("in_success");

		$("div.notes_request_message").html($("#RestStartText").val());

	}

	function failed(data){

		setTimeout(function(){
			$("a").css('pointer-events', 'initial');
			$("input, select, textarea").attr("disabled",false);
			$("#notes_input_link").removeClass("disabled");
			$("#notes_input_link").removeClass("icon_barcode_progress");
			$("#notes_input_link").addClass("icon_barcode_active");
			$("#notes_summary").removeClass("disabled");

			$("div.notes_request_message").removeClass("in_progress");
			$("div.notes_request_message").removeClass("in_error");
			$("div.notes_request_message").removeClass("in_success");
			$("div.notes_request_message").addClass("in_error");
			$("div.notes_request_message").html(data.error);

		},timeout_time);

	}

	function success(data,ticketid){

		setTimeout(function(){
			$("a").css('pointer-events', 'initial');
			$("input, select, textarea").attr("disabled",false);
			$("#notes_input_link").removeClass("disabled");
			$("#notes_input_link").removeClass("icon_barcode_progress");
			$("#notes_input_link").addClass("icon_barcode_active");
			$("#notes_summary").removeClass("disabled");

			$("div.notes_request_message").removeClass("in_progress");
			$("div.notes_request_message").removeClass("in_error");
			$("div.notes_request_message").removeClass("in_success");

			$("div.notes_request_message").addClass("in_success");
			$("div.notes_request_message").html(data);

			$("textarea#TicketscanNotesInput").val("");

			var data = new Array();
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

			$("#dialog").dialog(dialogOpts);

			data.push({name: "ajax_true", value: 1});
			data.push({name: "ticket_id", value: ticketid});
			$.ajax({
				type	: "POST",
				cache	: false,
				url		: this.window.location.href + 'rests/editticket/' + $("#TicketVarsArray").val(),
				data	: data,
				//dataType: "json",
				success: function(data) {
					$("#dialog").html(data);
					$("#dialog").dialog("open");
					$("#dialog").show();
					$("#dialog").css('overflow','scroll');
					$("#AjaxSvgLoader").hide();
					$("#notes_summary").empty();
					$("textarea#TicketscanNotesInput").val("");
					$("textarea#TicketscanNotesInput").focus();

					$("ul.editmenue li").removeClass("active").addClass("deactive");
					$("ul.editmenue li#link_data_area").removeClass("deactive").addClass("active");
					$("div.areas").hide();
         			$("div#data_area").show();

					activate();
				}
			});

		},timeout_time);


	}

	if($("textarea#TicketscanNotesInput").is(":focus")){

		if($("a#notes_input_link").hasClass("disabled")) return false;

		$("a#notes_input_link").removeClass("icon_barcode_deactive");
		$("a#notes_input_link").addClass("icon_barcode_active");
		$("a#notes_input_link").prop("title","<?php echo __('Scanner is active',true);?>");
		$("span#notes_summary").text("<?php echo __('Scanner is active',true);?>");
	}

	if(!($("textarea#TicketscanNotesInput").is(":focus"))){

		if($("a#notes_input_link").hasClass("disabled")) return false;

		$("a#notes_input_link").removeClass("icon_barcode_deactive");
		$("a#notes_input_link").addClass("icon_barcode_error");
		$("a#notes_input_link").prop("title","<?php echo __('Please click to activate the scanner',true);?> (<?php echo __('shortcut ctrl+c',true);?>)");
		$("span#notes_summary").text("<?php echo __('Please click to activate the scanner',true);?> (<?php echo __('shortcut ctrl+c',true);?>)");
	}

	$("textarea#TicketscanNotesInput").focusout(function() {

		if($("a#notes_input_link").hasClass("disabled")) return false;

		$("a#notes_input_link").removeClass("icon_barcode_active");
		$("a#notes_input_link").removeClass("icon_barcode_error");
		$("a#notes_input_link").addClass("icon_barcode_deactive");
		$("a#notes_input_link").prop("title","<?php echo __('Please click to activate the scanner');?> (<?php echo __('shortcut ctrl+c',true);?>)");
		$("span#notes_summary").text("<?php echo __('Please click to activate the scanner',true);?> (<?php echo __('shortcut ctrl+c',true);?>)");
	});

	$("#hidden_for_you").css("width","1px");
	$("#hidden_for_you").css("height","1px");
	$("#hidden_for_you").css("overflow","hidden");

	$("a#notes_input_link, span#notes_summary").click(function() {

		if($("a#notes_input_link").hasClass("disabled")) return false;

		$("#notes_summary").empty();
		$("textarea#TicketscanNotesInput").val("");
		$("textarea#TicketscanNotesInput").focus();
		activate();
	});

	function startdisabled(){

		$("a").css('pointer-events', 'none');
		$("input, select, textarea").attr("disabled","disabled");
		$("#notes_input_link").addClass("disabled");
		$("#notes_input_link").removeClass("icon_barcode_deactive");
		$("#notes_input_link").removeClass("icon_barcode_active");
		$("#notes_input_link").addClass("icon_barcode_progress");
		$("#notes_summary").addClass("disabled");

	}

	function stopdisabled(){

		setTimeout(function(){
			$("a").css('pointer-events', 'initial');
			$("input, select, textarea").attr("disabled",false);
			$("#notes_input_link").removeClass("disabled");
			$("#notes_input_link").removeClass("icon_barcode_progress");
			$("#notes_input_link").addClass("icon_barcode_active");
			$("#notes_summary").removeClass("disabled");
		},5000);

	}

	function requesterror(data){

		 return failed(data.ReferenceError);

	}

	function requestdata(data){

		if(typeof(data.message) == "undefined") return failed("String empty");
		if(data.message == null) return failed("String empty");
		if(data.message.length == 0) return failed("String empty");

		if(data.message.error && data.message.error.length > 0) return failed(data.message);

		if(data.code == "NOT_FOUND") return failed(data.message);

		return success(data.message.success,data.ticketid);
	}

	$("textarea#TicketscanNotesInput").on("keyup", function() {

		if($(this).val().length == 1){
			settimer = setTimeout(intervalclear, 5000);

		}

		if($(this).val().length < 10){
			return;
		}

		barcode = $(this).val();

		$("textarea#TicketscanNotesInput").val("");

		$("div.notes_request_message").removeClass("in_progress");
		$("div.notes_request_message").removeClass("in_error");
		$("div.notes_request_message").removeClass("in_success");

		$("div.notes_request_message").addClass("in_progress");
		$("div.notes_request_message").html($("#RestProgressText").val());

		startdisabled();

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "barcode", value: barcode});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		:  $("#TicketscanUpdateUrl").val(),
			data	: data,
			dataType: "json",
			success: function(data) {
				requestdata(data);
			},
			error: function (xhr, error) {
				requestdata(error);
		}
		});
	});

	$("input#TicketscanTicketNumber").change(function() {

		if($(this).val().length == 1){
			settimer = setTimeout(intervalclear, 5000);
		}

		if($(this).val().length < 10){
			return;
		}

		barcode = $(this).val();

		console.log(barcode);

		$("input#TicketscanTicketNumber").val("");

		$("div.notes_request_message").removeClass("in_progress");
		$("div.notes_request_message").removeClass("in_error");
		$("div.notes_request_message").removeClass("in_success");

		$("div.notes_request_message").addClass("in_progress");
		$("div.notes_request_message").html($("#RestProgressTextInput").val());

		startdisabled();

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "barcode", value: barcode});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		:  $("#TicketscanUpdateUrl").val(),
			data	: data,
			dataType: "json",
			success: function(data) {
				requestdata(data);
			},
			error: function (xhr, error) {
				requestdata(error);
			}
		});
	});
});
</script>
