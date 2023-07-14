<?php
$TemplateUrl = $this->Html->url(array_merge(array('controller' => 'templates','action' => 'json'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisTemplateUrl', array('type' => 'hidden','value' => $TemplateUrl));
?>

<script type="text/javascript">
$(document).ready(function(){

	function UpdateTemplateTable(data){

		if(!data.TemplateEvaluation) return false;

		var rowCount = $("table.advancetool tbody tr").length;
		var el = "<tr>";

		el += '<td><a href="javascript:" class="icon icon_delete" rel="11" title="Löschen">Löschen</a></td>';

		$(data.TemplateEvaluation.data).each(function(key,value){
			el += '<td class="template_data" name="data[TemplatesEvaluations][data]['+rowCount+']['+value.field+']" id="templates_'+value.field+'_'+rowCount+'">'+value.value+'</td>';

		});

		el += "</tr>";

		$("table.advancetool tr:last").after(el);
		$("table.advancetool tbody").sortable();

		$("table.advancetool a.icon_delete").click(function() {
			$(this).closest('tr').remove();
		});

	}

	$("a#SaveTemplate").click(function() {

		var data = new Array;
		var url = $(this).closest('form').attr('action');

		data.push({name: "ajax_true", value: 1});
		data.push({name: "save_template", value: 1});

		data.push({name: "template_name", value: $("#TemplateEvaluationName").val()});
		data.push({name: "template_descripton", value: $("#TemplateEvaluationDescription").val()});

		$("table.advancetool tbody tr td.template_data").each(function(){

			data.push({name: $(this).attr("name"), value: $(this).text()});

		});

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
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").dialog("open");
				$("#dialog").show();
				$("#dialog").css('overflow','scroll');
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
