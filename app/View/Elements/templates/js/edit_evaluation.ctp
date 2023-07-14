<?php
$TemplateUrl = $this->Html->url(array_merge(array('controller' => 'templates','action' => 'json'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisTemplateUrl', array('type' => 'hidden','value' => $TemplateUrl));
echo $this->Form->input('ThisTemplateWarningMessage', array('type' => 'hidden','value' => __('Press the submit button to save changes.',true)));
?>

<script type="text/javascript">
$(document).ready(function(){

	function ShowWarningMessage(){

		if($(".message_info").length){$(".message_info").remove();}

		let flash = '<div class="message_info"><span class="warning">' + $("#ThisTemplateWarningMessage").val() + '</span></div>';

		$(flash).insertAfter("table.advancetool");

	}

	function UpdateTemplateTable(data){

		if(!data.TemplateEvaluation) return false;

		ShowWarningMessage();

		let rowCount = $("table.advancetool tbody tr").length;
		let lastRowcount = ++rowCount;

		let el = "<tr id=\"template_data_row_" + lastRowcount + " \">";

		el += '<td>';
		el += '<a href="javascript:" class="icon icon_delete" title="Löschen">Löschen</a>';
		el += '</td>';

		$(data.TemplateEvaluation.data).each(function(key,value){

			el += '<td class="template_data" name="data[TemplatesEvaluations][data]['+rowCount+']['+value.field+']" id="templates_'+value.field+'_'+rowCount+'">'+value.value+'</td>';

		});

		el += "</tr>";

		if($("table.advancetool tbody tr").length == 0){
			$("table.advancetool tbody").append(el);
		} else {
			$("table.advancetool tbody tr:last").after(el);
		}

		$("table.advancetool tbody").sortable();

		$("table.advancetool a.icon_delete").click(function() {
			$(this).closest('tr').remove();
			ShowWarningMessage();
		});

	}

	$("a#SaveTemplate").click(function() {

		var data = new Array;
		var url = $(this).closest('form').attr('action');

		data.push({name: "ajax_true", value: 1});
//		data.push({name: "show_blank", value: 1});
		data.push({name: "edit_template", value: 1});

		$("table.advancetool tbody tr td.template_data").each(function(){

			data.push({name: $(this).attr("name"), value: $(this).text()});

		});

//		$("#dialog").dialog().dialog("close");
		$("#maximizethismodal").hide();

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

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
//			dataType: "json",
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#AjaxSvgLoader").hide();
			},
			complete: function(data) {
//				json_request_stop_animation();
			},
			statusCode: {
				404: function() {
					alert( "page not found" );
					location.reload();
				}
			},
			statusCode: {
				403: function() {
					alert( "page blocked" );
					location.reload();
				}
			}
		});

		return false;

	});

	$("div.current_content p.editable").editable($("#ThisTemplateUrl").val(), {

			placeholder : 'Editieren',
      submit : 'OK',
      cancel : 'Cancel',
      type   : 'textarea',
      cssclass : 'testinstruction_editable',
      cancelcssclass : 'editable_cancel',
      submitcssclass : 'editable_submit',

      submitdata : function(data) {

		//		console.log($(this).attr('class'));
        var send = new Array();

				send.push({
					data_class: $(this).attr('class'),
					edit_template: 1,
					field: $(this).attr('rev'),
					id: $(this).attr('rel'),
				});

				return send[0];
      },
      callback : function(data) {
				return "bla";
		 	}
  });

	$("table.advancetool a.icon_delete").click(function() {
		$(this).closest('tr').remove();
		ShowWarningMessage();
	});

	$("table.advancetool a.icon_dupli").click(function() {

		var trid  = "#" + $(this).closest('tr').attr("id");

		$("table tr" + trid + " td.template_data").each(function(){

			var formname = $(this).attr("form-name");
			var formtext = $(this).text();

			$("form#TemplateEvaluationEvaluationForm").find('input[name="' + formname + '"]').val(formtext);

		});


	});

	$("a#AddSaveTemplate").click(function() {

		var data = $("#TemplateEvaluationEvaluationForm").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_blank", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#TemplateEvaluationEvaluationForm").attr("action"),
			data	: data,
			dataType: "json",
			success: function(data) {
				UpdateTemplateTable(data);
			},
			complete: function(data) {
//				json_request_stop_animation();
			},
			statusCode: {
				404: function() {
					alert( "page not found" );
					location.reload();
				}
			},
			statusCode: {
				403: function() {
					alert( "page blocked" );
					location.reload();
				}
			}
		});

		return false;

	});

	$("#TemplateEvaluationEvaluationForm").bind("submit", function() {

//		json_request_load_animation();

		var data = $(this).serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_blank", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("action"),
			data	: data,
			dataType: "json",
			success: function(data) {
				UpdateTemplateTable(data);
			},
			complete: function(data) {
//				json_request_stop_animation();
			},
			statusCode: {
				404: function() {
					alert( "page not found" );
					location.reload();
				}
			},
			statusCode: {
				403: function() {
					alert( "page blocked" );
					location.reload();
				}
			}
		});

		return false;

	});
});
</script>
