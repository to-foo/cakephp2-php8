<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($discription)) return;

$targetcontroller = 'reportnumbers';
$targetation = 'view';

echo $this->Form->create('QuicksearchAssignedReport',array('id' => 'QuicksearchAssignedReportForm','class' => 'quip_search_form default'));

echo $this->Form->input('hidden', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 1,
				)
			);

echo $this->Form->input('this_id', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 0,
				)
			);

echo $this->Form->input('searching_autocomplet', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => $discription,
				'placeholder' => $discription,
				'formaction' => 'autocomplete'
				)
			);

echo $this->Form->input('json_url', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array_merge(array('action' => 'assignedReports'),$this->request->projectvars['VarsArray'])),
			)
		);

echo $this->Form->input('min_length', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $minLength,
			)
		);

echo $this->Form->input('target_controller', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $targetcontroller,
			)
		);

echo $this->Form->input('target_action', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $targetation,
			)
		);

		echo $this->Form->input('assigned_id', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 0,
					)
				);

echo $this->Form->end();
?>

<script>
$(function() {

	function EmbedPDF(data){

	  var string = "data:application/pdf;base64," + data.string;

		$("div#wrapper_pdf_container_assigned_report").show();
		$("div#show_pdf_contaniner_assigned_report").show();
		$("div#show_pdf_container_navi_assigned_report").show();

		PDFObject.embed(string, "div#show_pdf_contaniner_assigned_report");

		$("a#show_pdf_contaniner_button_assigned_report_cancle").click(function() {

			$("div#wrapper_pdf_container_assigned_report").hide();
			$("div#show_pdf_contaniner_assigned_report").hide();
			$("div#show_pdf_container_navi_assigned_report").hide();

		});

		$("a#show_pdf_contaniner_button_assigned_report_add").click(function() {

			$.ajax({
				type	: "POST",
				url: $("#QuicksearchAssignedReportJsonUrl").val(),
//				dataType: "json",
				data: {
					ajax_true: 1,
					assigned_id: $("#QuicksearchAssignedReportAssignedId").val(),
					setGenerally: $('.modalarea .hint input:checked').val()
				},
				success: function(data) {
					$("#dialog").html(data);
				},
			});

			$("div#wrapper_pdf_container_assigned_report").hide();
			$("div#show_pdf_contaniner_assigned_report").hide();
			$("div#show_pdf_container_navi_assigned_report").hide();

		});
	}

	$("div.modalarea div.hint form.login input[type=radio]").change(function() {
		if($('div.modalarea div.hint form.login input:checked').val() == 1){
			$("#flash_warning").slideDown("slow");
		} else {
			$("#flash_warning").slideUp("slow");
		}
	});

	$("form#QuicksearchAssignedReportForm").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	$("#QuicksearchAssignedReportSearchingAutocomplet").blur(function(){
		$("#QuicksearchAssignedReportSearchingAutocomplet").val("");
		$("#QuicksearchAssignedReportForm").removeClass("search").addClass("default");
	});

	var i = 0;

	$("#QuicksearchAssignedReportForm input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: $("#QuicksearchAssignedReportMinLength").val(),
			delay: 4,
			source: function(request,response) {

				$("#QuicksearchAssignedReportForm").removeClass("default").addClass("search");

				$.ajax({
					type	: "POST",
					url: $("#QuicksearchAssignedReportJsonUrl").val(),
					dataType: "json",
					data: {
						term : request.term,
						ajax_true: 1,
						quicksearch_true: 1,
					},
					success: function(data) {
						response(data);
						$("#QuicksearchAssignedReportForm").removeClass("search").addClass("default");
					},
				});
			},
			select: function(event,ui) {

				$("#AjaxSvgLoader").show();
				var data = new Array();

				$("#QuicksearchAssignedReportAssignedId").val(ui.item.value);

				data.push({name: "ajax_true", value: 1});
				data.push({name: "showpdf", value: 1});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: ui.item.url,
					data	: data,
					dataType: "json",
					success: function(data) {
						$("#AjaxSvgLoader").hide();
						EmbedPDF(data);
					}
				});

				return false;
			}
		});
		i++;
	});

	$("#QuicksearchAssignedReportSearchingAutocomplet").change(function() {
		if($("#QuicksearchAssignedReportSearchingAutocomplet").val() == "" && $("#QuicksearchAssignedReportThisId").val() == 0){
			$("#container").load($("#QuicksearchAssignedReportForm").attr("action"), {"ajax_true": 1});
		}
	});

	$("form#QuicksearchAssignedReportForm").bind("submit", function() {

		if($("#this_id").val() == 0){
			return false;
		}

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
			}
		});
		return false;
	});
});
</script>
