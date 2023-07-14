<?php
$Model = $this->request->Model . 'Result';
echo $this->Form->input('WeldEditStatus',array('type' => 'hidden','value' => $this->request->projectvars['VarsArray'][6]));
echo $this->Form->input('FastSaveJsonUrl',array('type' => 'hidden','value' => $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'savejson'),$this->request->projectvars['VarsArray']))));
echo $this->Form->input('FastSaveController',array('type' => 'hidden','value' => $this->request->controller));
echo $this->Form->input('FastSaveAction',array('type' => 'hidden','value' => $this->request->action));
echo $this->Form->input('BreadDescription',array('type' => 'hidden','value' => __('Edit Evalution', true)));
echo $this->Form->input('DeleteDescription',array('type' => 'hidden','value' => __('Should this value be deleted', true)));

if(isset($this->request->data['paginationOverview']['has_no_position'])) echo $this->Form->input('HasNoPosition',array('type' => 'hidden','value' => 1));
?>

<script type="text/javascript">
$(document).ready(function(){

	loader = '<div class="loader"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div></div>';
	SuccesIcon = '<div class="check_circle success_circle"><div class="check-circle__mark"></div></div>';
	ErrorIcon = '<div class="check_circle error_circle"><div class="check-circle__mark"></div></div>';
	HintIcon = '<div class="check_circle hint_circle"><div class="check-circle__mark"></div></div>';
	HintIconText = "<?php echo __('The content cannot be edited. The values of the test sections are different. To edit this value, switch to the test section mode.',true);?>";
	HintWeldEditText = "<?php echo __('This value can only edit in complete test area mode',true);?>";

	$("#JsonSvgLoader").hide();

	$('form.editreport').on('keyup keypress', function(e) {

		activeObj = document.activeElement;

		if(activeObj.tagName == "TEXTAREA"){
			// do nothing
		} else {
			var keyCode = e.keyCode || e.which;
			if(keyCode === 13) {
				e.preventDefault();
				return false;
			}
		}
	});

	$("div.pagin_links a").tooltip();

	function count(array){

		var c = 0;
		for(i in array) if(array[i] != undefined) c++;
		return c;

	}

	CollectFormData = function(){

		var orginal_data = {'controller': $("#FastSaveController").val(), 'action': $("#FastSaveAction").val()};

		$("form.editreport input:not(.hide_box), form.editreport select, form.editreport textarea").each(function(){


			if($(this).closest("div").hasClass("radio")){

				if($(this).attr("checked") == "checked"){
					orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
				} else {
					orginal_data["orginal_" + $(this).attr("name")] = 0;
				}

			}

			if($(this).closest("div").hasClass("number") || $(this).closest("div").hasClass("text") || $(this).closest("div").hasClass("select") || $(this).closest("div").hasClass("textarea")) {

				var active_div_width = $(this).closest("div").width() + 20;
				orginal_data["orginal_" + $(this).attr("name")] = $(this).val();

			}

			if($(this).closest("div").hasClass("checkbox")){

				var active_div_width = $(this).closest("div").width() + 20;

				if($(this).attr("checked") === undefined){
					orginal_data["orginal_" + $(this).attr("name")] = 0;
				} else {
					orginal_data["orginal_" + $(this).attr("name")] = 1;
				}
			}
		});

		return orginal_data;
	}

	function AddNewEvaluation(data){

		if(data.paginationOverview.current_id != 0) return false;

	}

	function CheckUpdateRevision(data){

		if(data.CurrentTable !== undefined) var model = data.CurrentTable;
		if(data.ReportModel !== undefined) var model = data.ReportModel;

		if(data.paginationOverview !== undefined) var id = data.paginationOverview.current_id;
		if(data.evalId !== undefined) var id = data.evalId;

		if(count(data.RevisionValues) == 0) return false;
		if(count(data.RevisionValues[model]) == 0) return false;
		if(data.RevisionValues[model][id] == undefined) return false;

		$.each(data.RevisionValues[model][id], function (i,val) {

			var name = "data[" + model + "][" + i + "]";
			var elm = $("form#ReportnumberEditevalutionForm").find("[name='" + name + "']");
			var id = "#" + elm.attr("id");
			var id_circle = "#" + elm.attr("id") + "_circle";
			var content = "";
			var icon = '<div id="' + elm.attr("id") + '_circle" title="loading..." class="check_circle rev_circle"><div class="check-circle__mark"></div></div>';


			content += "Revision: " + val.revision + "<br>";
			content += "Old: " + val.last_value + "<br>";
			content += "New: " + val.this_value + "<br>";

			elm.closest("div").append(icon).tooltip({
				content: content,
				position: {
					my: "left+15 center",
					at: "right center",
					of: id_circle
					}
				}
			);

		});

	}

	function FlashMessage(data){

		if(!data.FlashMessages) return true;
		if(count(data.FlashMessages) == 0) return true;

		var Mess = "";
		var Output = true;

		$.each(data.FlashMessages, function (i,val) {

			if(val.type == "error"){

				Output = false;

				var ThisId = "#" + data.CurrentTable + val.field;

				$("div.loader").remove();
				json_request_animation("Error");

				$(ThisId).closest("div").append(ErrorIcon);
				$(ThisId).closest("div").attr("title",val.message);
				$(ThisId).closest("div").tooltip();

			}
		});

		return Output;
	}

	function StatusCheck(data){

		if(data.Reportnumber.status == 0) return false;

		var model = data.CurrentTable;
		var evaluation = data.Camelize[model];

		$.each(evaluation, function (i) {

			__StatusInputRequest(data,evaluation[i],model,i);
			__StatusSelectRequest(data,evaluation[i],model,i);
			__StatusRadioRequest(data,evaluation[i],model,i);
			__StatusTextareaRequest(data,evaluation[i],model,i);
			__StatusMultipleRequest(data,evaluation[i],model,i);

//			__StatusInputRequest(data,evaluation[i],model,i);

		});

	}

	function ChangeLinkCss(data){

		if(!data.results) return false;

		$.each(data.results.position, function (key,val) {

			if(count(val) > 0){

				$.each(val, function (key2,val2) {

					$("div.pagin_links").find("a[rel='" + key2 + "']:not('.icon_weld')").removeClass("small_error_result small_okay_result small_no_result");

					if(val2 == 0) {$("div.pagin_links").find("a[rel='" + key2 + "']:not('.icon_weld')").addClass("small_no_result");}
					if(val2 == 1) {$("div.pagin_links").find("a[rel='" + key2 + "']:not('.icon_weld')").addClass("small_okay_result");}
					if(val2 == 2) {$("div.pagin_links").find("a[rel='" + key2 + "']:not('.icon_weld')").addClass("small_error_result");}

				});
			}
		});

		$.each(data.results.discription, function (key,val) {

			$("div.pagin_links").find("a[data-desc='" + key + "']:not('.icon_exam_part')").removeClass("small_error_result small_okay_result small_no_result");

			if(val == 0) {$("div.pagin_links").find("a[data-desc='" + key + "']:not('.icon_exam_part')").addClass("small_no_result");}
			if(val == 1) {$("div.pagin_links").find("a[data-desc='" + key + "']:not('.icon_exam_part')").addClass("small_okay_result");}
			if(val == 2) {$("div.pagin_links").find("a[data-desc='" + key + "']:not('.icon_exam_part')").addClass("small_error_result");}

		});


		if(data.current_weld_edit_status == 1){
			$('#ReportnumberDeleteDescription').attr('data-desc', data.current_weld_description);
		}

		if(data.current_weld_edit_status == 0){
			$('#ReportnumberDeleteDescription').attr('data-desc', data.current_weld_description + "/" + data.current_positon_description);
		}

	}

	function __StatusInputRequest(data,evaluation,model,i){

		var ThisId = "#" + model + i;

		if(!$(ThisId).length) return false;

		if(data.Reportnumber.revision_write == 1) return false;

		if($(ThisId).attr("type") != "text") return false;

		if(data.Reportnumber.status > 0) $(ThisId).attr("disabled",true);

	}

	function __StatusSelectRequest(data,evaluation,model,i){

		var ThisId = "#" + model + i;

		if(data.Reportnumber.revision_write == 1) return false;

		if(!$(ThisId).is("select")) return false;
		if(!$(ThisId).length) return false;
		if($(ThisId).attr("multiple") == "multiple") return false;

		if(data.Reportnumber.status > 0) $(ThisId).attr("disabled",true);

	}

	function __StatusTextareaRequest(data,evaluation,model,i){

		var ThisId = "#" + model + i;

		if(data.Reportnumber.revision_write == 1) return false;

		if(!$(ThisId).is("textarea")) return false;
		if(!$(ThisId).length) return false;

		if(data.Reportnumber.status > 0) $(ThisId).attr("disabled",true);

	}

	function __StatusRadioRequest(data,evaluation,model,i){

		var field_underscore = data.Underscore[model][i];

		if(data.Reportnumber.revision_write == 1) return false;

		if(data.xml.settings[model][field_underscore] == undefined) return false;
		if(data.xml.settings[model][field_underscore]["fieldtype"] != "radio") return false;

		var ThisName = "data[" + model + "][" + field_underscore + "]";
		var ThisId = "#" + model + i + evaluation;

		if(!$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").attr("type") == "radio") return false;

		if(!$(ThisId).length) return false;
		if($(ThisId).attr("type") == "text") return false;
		if($(ThisId).is("select")) return false;
		if($(ThisId).is("textarea")) return false;

		if(data.Reportnumber.status > 0) $("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").attr("disabled",true);

		$("div.radio:not(.ui-buttonset)").controlgroup("refresh");

	}

	function __StatusMultipleRequest(data,evaluation,model,i){

		var ThisId = "#" + model + i;

		if(data.Reportnumber.revision_write == 1) return false;

		if(!$(ThisId).is("select")) return false;
		if(!$(ThisId).length) return false;
		if($(ThisId).attr("multiple") != "multiple") return false;

		if(data.Reportnumber.status > 0){
			$(ThisId).attr("disabled",true);
			if($("input" + ThisId).length) $("input" + ThisId).attr("disabled",true);
		}

		$(ThisId).multiSelect('refresh');

	}

	function JsonRequestForm(data){

		if(data.deletetElement) return false;

		var model = data.CurrentTable;
		var evaluation = data.Camelize[model];
		var weld = data.paginationOverview.current_weld_description;

		$("#ReportnumberFastSaveJsonUrl").val(data.SaveJsonUrl);
		$(".check_circle").remove();

		$("div.input, div.select").removeAttr('title');

		$.each(evaluation, function (i) {

			var field_underscore = data.Underscore[model][i];

			__ChangeInputRequest(evaluation[i],model,i);
			__ChangeSelectRequest(evaluation[i],model,i);
			__ChangeMultipleRequest(evaluation[i],model,i);
			__ChangeTextareaRequest(evaluation[i],model,i);
			__ChangeRadioRequest(evaluation[i],model,i,data,field_underscore);

		});

		UpdateFormElements(data);

		if(count(data.paginationOverview.results.position[weld]) == 1) return false;

		if(data.paginationOverview.current_weld_edit_status == 0){

			if(count(data.paginationOverview.results.position[data.paginationOverview.current_weld_description]) == 1) return false

			var DescriptionId = "#" + data.CurrentTable + "Description";
			$(DescriptionId).attr("disabled",true);
			$(DescriptionId).closest("div").append(HintIcon);
			$(DescriptionId).closest("div").find("div.check_circle").attr("title",HintWeldEditText);
			$(DescriptionId).closest("div").find("div.check_circle").tooltip();

		}

	}

	function UpdateFormElements(data){

//		if(data.Reportnumber.status > 0) return false;
		if(data.paginationOverview.current_weld_edit_status != 1) return;

		__UpdateFormInputs(data);
		__UpdateFormSelects(data);
		__UpdateFormMultiple(data);
		__UpdateFormTextarea(data);
		__UpdateFormRadio(data);

	}

	function __UpdateFormInputs(data){

		$.each(data.XmlNotInUse, function (key,value) {

			var ThisId = "#" + value;

			if($(ThisId).attr("type") != "text") return true;

			$(ThisId).val("");
			$(ThisId).attr("disabled",true);
			$(ThisId).closest("div").append(HintIcon);
			$(ThisId).closest("div").find("div.check_circle").attr("title",HintIconText);
			$(ThisId).closest("div").find("div.check_circle").tooltip();

		});

	}

	function __UpdateFormSelects(data){

		$.each(data.XmlNotInUse, function (key,value) {

			var ThisId = "#" + value;

			if(!$(ThisId).is("select")) return true;
			if(!$(ThisId).length) return true;
			if($(ThisId).attr("multiple") == "multiple") return true;

			$(ThisId + " option").removeAttr("selected");
			$(ThisId).attr("disabled",true);
			$(ThisId).closest("div").append(HintIcon);
			$(ThisId).closest("div").find("div.check_circle").attr("title",HintIconText);
			$(ThisId).closest("div").find("div.check_circle").tooltip();

		});

	}

	function __UpdateFormMultiple(data){

		$.each(data.XmlNotInUse, function (key,value) {

			var ThisId = "#" + value;
			if(!$(ThisId).is("select")) return true;
			if(!$(ThisId).length) return true;
			if($(ThisId).attr("multiple") != "multiple") return true;

			$(ThisId + " option").removeAttr("selected");
			$(ThisId).attr("disabled",true);
			if($("input" + ThisId).length) $("input" + ThisId).attr("disabled",true);
			$(ThisId).multiSelect('refresh');
			$(ThisId).closest("div").append(HintIcon);
			$(ThisId).closest("div").find("div.check_circle").attr("title",HintIconText);
			$(ThisId).closest("div").find("div.check_circle").tooltip();

		});

	}

	function __UpdateFormTextarea(data){

		$.each(data.XmlNotInUse, function (key,value) {

			var ThisId = "#" + value;
			if(!$(ThisId).is("textarea")) return true;

			$(ThisId).text("");
			$(ThisId).attr("disabled",true);
			$(ThisId).closest("div").append(HintIcon);
			$(ThisId).closest("div").find("div.check_circle").attr("title",HintIconText);
			$(ThisId).closest("div").find("div.check_circle").tooltip();

		});

	}

	function __UpdateFormRadio(data){

		$.each(data.XmlNotInUse, function (key,value) {

			var ThisId = "#" + value + "0";

			if(!$(ThisId).length) return true;
			if($(ThisId).attr("type") == "text") return true;
			if($(ThisId).is("select")) return true;
			if($(ThisId).is("textarea")) return true;
			if(!$(ThisId).hasClass('ui-checkboxradio')) return true;


			$(ThisId).parent("fieldset").find("input:radio").prop("checked", false);
			$(ThisId).parent("fieldset").find("input:radio").attr("disabled",true);
			$(ThisId).closest("div").append(HintIcon);
			$(ThisId).closest("div").find("div.check_circle").attr("title",HintIconText);
			$(ThisId).closest("div").find("div.check_circle").tooltip();

			$("div.radio:not(.ui-buttonset)").controlgroup("refresh");

		});

	}

	function __ChangeInputRequest(evaluation,model,i){

		var ThisId = "#" + model + i;

		if(!$(ThisId).length) return false;
    $(ThisId).val("");
		if($(ThisId).attr("type") != "text") return false;

		$(ThisId).val(evaluation);
		$(ThisId).attr("disabled",false);

	}

	function __ChangeRadioRequest(evaluation,model,i,data,field_underscore){

		if(data.xml.settings[model][field_underscore] == undefined) return false;
		if(data.xml.settings[model][field_underscore]["fieldtype"] != "radio") return false;

		var ThisName = "data[" + model + "][" + field_underscore + "]";
		var ThisId = "#" + model + i + evaluation;

		if(!$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").attr("type") == "radio") return false;

		if(!$(ThisId).length) return false;
		if($(ThisId).attr("type") == "text") return false;
		if($(ThisId).is("select")) return false;
		if($(ThisId).is("textarea")) return false;

		$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").attr("disabled",false);
		$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").removeAttr("checked");
		$(ThisId).prop("checked", true);

		$("div.radio:not(.ui-buttonset)").controlgroup("refresh");

	}

	function __ChangeTextareaRequest(evaluation,model,i){

		var ThisId = "#" + model + i;

		if(!$(ThisId).is("textarea")) return false;
		if(!$(ThisId).length) return false;


		$(ThisId).text(evaluation);
		$(ThisId).attr("disabled",false);

	}

	function __ChangeSelectRequest(evaluation,model,i){

		var ThisId = "#" + model + i;

		if(!$(ThisId).is("select")) return false;
		if(!$(ThisId).length) return false;
		if($(ThisId).attr("multiple") == "multiple") return false;

		$(ThisId + " option").removeAttr("selected");

		if(i == "Position" && !evaluation){
			$(ThisId).attr("disabled",false);
			$(ThisId).val("").prop('selected',true);
			return false;
		}

		$(ThisId + ' option').each(function(){

			if($(this).text() == evaluation){
				$(this).prop('selected',true);
			}

    });

		$(ThisId).attr("disabled",false);

	}

	function __ChangeMultipleRequest(evaluation,model,i){

		var ThisId = "#" + model + i;

		if(!$(ThisId).is("select")) return false;
		if(!$(ThisId).length) return false;
		if($(ThisId).attr("multiple") != "multiple") return false;

		$(ThisId).attr("stop-saving",1);

		$(ThisId + " option").removeAttr("selected");

		$(ThisId).multiSelect("deselect_all");

		if($("input" + ThisId).length) $("input" + ThisId).val("");
		if($("input" + ThisId).length) $("input" + ThisId).attr("disabled",false);

		var evaluation_array = evaluation.split(',');

		$(evaluation_array).each(function(key,val){

			$(ThisId + ' option[value="' + val + '"]').prop('selected',true);

		});

		$(ThisId).attr("disabled",false);

		if($("input" + ThisId).length) $("input" + ThisId).val(evaluation);
		if($("input" + ThisId).length) $("input" + ThisId).attr("disabled",false);

		$(ThisId).multiSelect('refresh');

		$(ThisId).removeAttr("stop-saving");

	}

	function __UpdateRadioFieldAfterSaving(data){

		var ThisId = "#" + data.this_id;
		var ThisName = "data[" + data.ReportModel + "][" + data.field_for_saveField + "]";

		if(!$(ThisId).length) return false;
		if($(ThisId).attr("type") == "text") return false;
		if($(ThisId).is("select")) return false;
		if($(ThisId).is("textarea")) return false;

		$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").removeAttr("checked");
		$(ThisId + "[name='" + ThisName + "'][value='" + data.this_value + "']").attr('checked','checked');

	}

	function PaginationOverview(data){

//		if(data.Reportnumber.status > 0) return false;

		if(data.SaveAjaxUrl) return false;

		var current_id = data.paginationOverview.current_id;
		var current_result = data.paginationOverview.current_result;
		var model = data.CurrentTable;

		$("div.pagin_links ").find(".icon_dupli").attr("href",data.DupliJsonUrl);
		$("div.pagin_links ").find(".icon_del").attr("href",data.DeleteJsonUrl);
		$("div.pagin_links ").find(".icon_label").attr("href",data.LabelJsonUrl);

		$("div.pagin_links a.icon_weld").removeClass("small_active");
		$("div.pagin_links a.icon_exam_part").removeClass("small_active");
		$("div.pagin_links a.icon_weld").addClass("small_passiv");
		$("div.pagin_links a.icon_exam_part").addClass("small_passiv");

		if(data.paginationOverview.current_weld_edit_status == 1){

			var current_page_link = $("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']");

			$("div.pagin_links ").find(".icon_dupli").attr("title",data.paginationOverview.current_function_tooltip);
			$("div.pagin_links ").find(".icon_del").attr("title",data.paginationOverview.current_function_tooltip);
			$("div.pagin_links ").find(".icon_label").attr("title",data.paginationOverview.current_function_tooltip);

			current_page_link.removeClass("small_passiv");
			current_page_link.addClass("small_active");
			current_page_link.attr('rev', '1');

		} else {

			var current_page_link = $("div.pagin_links ").find("a[rel=" + current_id + "]");

			$("div.pagin_links ").find(".icon_dupli").attr("title",data.paginationOverview.current_position_tooltip);
			$("div.pagin_links ").find(".icon_del").attr("title",data.paginationOverview.current_position_tooltip);
			$("div.pagin_links ").find(".icon_label").attr("title",data.paginationOverview.current_function_tooltip);

			current_page_link.removeClass("small_passiv");
			current_page_link.addClass("small_active");
			current_page_link.removeAttr("rev");

		}

		if(data.paginationOverview.current_weld_edit_status != 1){

			$("div.pagin_links a.icon_weld").hide();
			$("div.pagin_links a.icon_exam_part").hide();
			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']").show();
			$("div.pagin_links a.icon_weld").removeClass("small_passiv");
			$("div.pagin_links a.icon_weld").addClass("small_activ");

			$("div.pagin_links").find("a[rel='" + data.paginationOverview.current_id + "']:not('.icon_weld')").attr("title",data.paginationOverview.current_position_tooltip);
			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").attr("title",data.paginationOverview.current_weld_tooltip);

		}

		if(data.paginationOverview.current_weld_edit_status == 0){

			var test_element = $("div.pagin_links ").find("a[rel='" + data.paginationOverview.current_id + "']:not('.icon_weld')");

			if(test_element.length == 0){

				var new_position_link = '<a href="' + data.EditJsonUrl + '" id="position_for_link_' + data.paginationOverview.current_id + '" rel="' + data.paginationOverview.current_id + '" data-desc="' + data.paginationOverview.current_weld_description + '" class="icon icon_small icon_exam_part json small_no_result small_active " title="' + data.paginationOverview.current_position_tooltip + '">' + data.paginationOverview.current_positon_description + '</a>';

				$(new_position_link).insertAfter($("div.pagin_links ").find(".icon_exam_part:last"));
				$("div.pagin_links ").find("a[rel='" + data.paginationOverview.current_id + "']").tooltip();

				JsonLink("a#position_for_link_" + data.paginationOverview.current_id);

			}
		}

		if(data.paginationOverview.current_weld_edit_status == 1){

			$("div.pagin_links a.icon_weld:not(.small_active)").show();
			$("div.pagin_links a.icon_exam_part").hide();

			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").attr("title",data.paginationOverview.current_weld_tooltip);
			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").removeClass("small_passiv");
			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").addClass("small_active");

			var test_element = $("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')");

			if(test_element.length == 0 && $("#ReportnumberHasNoPosition").length == 0){

				var new_description_link = '<a href="' + data.EditJsonUrl + '" id="description_for_link_' + data.paginationOverview.current_id + '" rev="1" rel="' + data.paginationOverview.current_id + '" data-desc="' + data.paginationOverview.current_weld_description + '" class="icon icon_small icon_weld json small_no_result small_activ small_active" title="' + data.paginationOverview.current_weld_tooltip + '">' + data.paginationOverview.current_weld_description + '</a>';

				$(new_description_link).insertAfter($("div.pagin_links ").find(".icon_weld:last"));
				$("div.pagin_links ").find("a[rel='" + data.paginationOverview.current_id + "']").tooltip();

				JsonLink("a#description_for_link_" + data.paginationOverview.current_id);

				var positions = data.paginationOverview.results.position[data.paginationOverview.current_weld_description];

				$.each(positions, function (key,value) {

					var new_position_link = '<a href="' + data.paginationOverview.position_url[key] + '" id="position_for_link_' + key + '" rel="' + key + '" data-desc="' + data.paginationOverview.current_weld_description + '" class="icon icon_small icon_exam_part json small_no_result small_passiv icon_hidden" title="' + data.paginationOverview.position_tooltip[key] + '">' + data.paginationOverview.position_tooltip[key] + '</a>';
					$(new_position_link).insertAfter("a#description_for_link_" + data.paginationOverview.current_id);
					$("div.pagin_links ").find("a[rel='" + key + "']").tooltip();
					JsonLink("a#position_for_link_" + key);

				});
			}

			if($("#ReportnumberHasNoPosition").length > 0){

				if($("div.pagin_links ").find("a[rel='" + data.paginationOverview.current_id + "']").length == 0){
					var new_description_link = '<a href="' + data.EditJsonUrl + '" id="description_for_link_' + data.paginationOverview.current_id + '" rev="0" rel="' + data.paginationOverview.current_id + '" data-desc="' + data.paginationOverview.current_weld_description + '" class="icon icon_small icon_weld json small_no_result small_activ small_active" title="' + data.paginationOverview.current_weld_tooltip + '">' + data.paginationOverview.current_weld_description + '</a>';

					$(new_description_link).insertAfter($("div.pagin_links ").find(".icon_weld:last"));
					$("div.pagin_links ").find("a[rel='" + data.paginationOverview.current_id + "']").tooltip();

					JsonLink("a#description_for_link_" + data.paginationOverview.current_id);

				}
			}

			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").show();

			if(data.paginationOverview.current_id == 0){

				$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").show();

			}

		}

		if(count(data.paginationOverview.results.position[data.paginationOverview.current_weld_description]) == 1){
			$("div.pagin_links a.icon_exam_part").hide();
		}

		if(data.BreadcrubUrl){
			$("div.breadcrumbs a").last().attr("href",data.BreadcrubUrl);
			$("div.breadcrumbs a").last().text(data.paginationOverview.current_weld_description_bread);
		}

		$("#ReportnumberWeldChanger").empty();

		if($("#ReportnumberHasNoPosition") && $("#ReportnumberHasNoPosition").val() == 1){

			$.each(data.paginationOverview.dropdownmenue, function (i,val) {

				$("<option/>").val(i).text(val).appendTo("#ReportnumberWeldChanger");

			});

		} else {

			$.each(data.paginationOverview.dropdownmenue, function (i,val) {

				var optgroup = $("<optgroup label='" + i + "'>");

				$.each(val, function (i2,val2) {
					var op = "<option value='" + val2.key + "'>" + val2.value + "</option>";
					optgroup.append(op);
				});

				$("#ReportnumberWeldChanger").append(optgroup);

			});
		}

		$("#ReportnumberWeldChanger option").prop('selected',false);

		if(data.paginationOverview.current_weld_edit_status == 1){
			$("#ReportnumberWeldChanger option[value='" + data.paginationOverview.current_weld_description + "']").prop('selected',true);
		}
		else {
			$("#ReportnumberWeldChanger option[value='" + data.paginationOverview.current_id + "']").prop('selected',true);
		}

		if($("#ReportnumberHasNoPosition").length) {
			$("#ReportnumberWeldChanger option[value='" + data.paginationOverview.current_id + "']").prop('selected',true);
		}

		$("#SectionModeContainer").text(data.paginationOverview.section_mode);


	}

	function __UpdateFunctionsLinks(data){
	}

	function __ChangePaginationDescription(data){

		if(data.SaveJsonUrl){

			$("#ReportnumberFastSaveJsonUrl").val(data.SaveJsonUrl);

			var old = $("div.pagin_links ").find("a[data-desc='0']").attr("title");
			old += ": " + data.this_value;

			$("div.pagin_links ").find("a[data-desc='0']").attr("title",old);
			$("div.pagin_links ").find("a[data-desc='0']").attr("rel",data.evalId);
			$("div.pagin_links ").find("a[data-desc='0']").attr("href",data.EditJsonUrl);
			$("div.pagin_links ").find("a[data-desc='0']").attr("data-desc",data.this_value);

			$("div.pagin_links ").find(".icon_dupli").attr("href",data.DupliJsonUrl);
			$("div.pagin_links ").find(".icon_del").attr("href",data.DeleteJsonUrl);
			$("div.pagin_links ").find(".icon_label").attr("href",data.LabelJsonUrl);


			var title = $("div.pagin_links ").find(".icon_dupli").attr("title");
			$("div.pagin_links ").find(".icon_dupli").attr("title",title + data.this_value,);

			var title = $("div.pagin_links ").find(".icon_del").attr("title");
			$("div.pagin_links ").find(".icon_del").attr("title",title + data.this_value,);

			var title = $("div.pagin_links ").find(".icon_label").attr("title");
			$("div.pagin_links ").find(".icon_label").attr("title",title + data.this_value,);

		}

		if(!data.paginationOverview) return false;

		if(data.field_for_saveField == "description"){

			var old = $("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.last_value + "']").attr("title");
			var old_split = old.split(':');
			var akt = old_split[1].replace(data.paginationOverview.last_value , data.paginationOverview.new_value);

			old_split[1] = akt;
			var res = old_split.join(': ')

			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.last_value + "']").attr("title",res);
			$("div.pagin_links ").find("a[data-desc='" + data.paginationOverview.last_value + "']").attr("data-desc",data.paginationOverview.new_value);

			$("div.pagin_links ").find(".icon_dupli").attr("title",res);
			$("div.pagin_links ").find(".icon_del").attr("title",res);
			$("div.pagin_links ").find(".icon_label").attr("title",res);

			var res = $("#ReportnumberBreadDescription").val() + " " + data.paginationOverview.new_value;

			$("div.breadcrumbs a").last().text(res);

			$('#ReportnumberWeldChanger option[value="' + data.evalId + '"]').text(data.paginationOverview.new_value);

		}

		if(data.field_for_saveField == "position"){

			var old = $("div.pagin_links ").find("a[rel='" + data.evalId + "']:not('.icon_weld')").attr("title");

			if(old != undefined){

				var old_split = old.split(':');

				var akt = old_split[2].replace(data.paginationOverview.last_value , data.paginationOverview.new_value);

				old_split[2] = akt;
				var res = old_split.join(':')

				$("div.pagin_links ").find("a[rel='" + data.evalId + "']:not('.icon_weld')").attr("title",res);
				$("div.pagin_links ").find(".icon_dupli").attr("title",res);
				$("div.pagin_links ").find(".icon_del").attr("title",res);

				$('#ReportnumberWeldChanger option[value="' + data.evalId + '"]').text(data.paginationOverview.new_value);

			}
		}
	}

	function UpdateFieldAfterSaving(data){

		__UpdateRadioFieldAfterSaving(data);
	}

	function FormRequestActionOkay(data){

		$("div.loader").remove();

		var ThisId = "#" + data.this_id;

		$(ThisId).removeAttr('disabled');
		$(ThisId).removeAttr('rel');

		if(data.SaveOkay == true){

			var NewEntry = $("div.pagin_links ").find("a[rel='0']:not('.icon_exam_part')").attr("rel");


			if(NewEntry == "0"){

				$("div.pagin_links ").find("a[rel='0']:not('.icon_exam_part')").attr("href",data.EditJsonUrl);
				$("div.pagin_links ").find("a[rel='0']:not('.icon_exam_part')").attr("rel",data.this_value);
				$("div.breadcrumbs a").last().attr("href",data.EditJsonUrl);
				$("div.breadcrumbs a").last().text(data.this_value);

//				$("#ReportnumberWeldChanger option[value='0']").text(data.this_value);
//				$("#ReportnumberWeldChanger option[value='0']").val(data.this_value);

				var optgroup = $("<optgroup label='New'>");
				var op = "<option value='" + data.this_value + "'>" + data.this_value + "</option>";

				optgroup.append(op);

				$("#ReportnumberWeldChanger").append(optgroup);

			}

			if(data.SaveJsonUrl) $("#ReportnumberFastSaveJsonUrl").val(data.SaveJsonUrl);
			if(data.EditJsonUrl) $("#CurrentEditEvaluationUrl").val(data.EditJsonUrl);

			if(data.field_for_saveField == "position"){

				var NewOptgroup = $("#ReportnumberWeldChanger").find("optgroup[label='New']");


				if(NewOptgroup.length == 1){

					var desc = $("#ReportnumberWeldChanger").find("optgroup[label='New'] option").val();

					$("#ReportnumberWeldChanger").find("optgroup[label='New']").remove();

					var last_label = parseInt($("#ReportnumberWeldChanger").find("optgroup:last").attr("label"));

					last_label++;

					var optgroup = $("<optgroup label='" + last_label + "'>");
					var op = "<option value='" + desc + "'>" + desc + "</option>";

					optgroup.append(op);

					var op = "<option value='" + data.evalId + "'>" + data.this_value_for_saveField + "</option>";

					optgroup.append(op);

					$("#ReportnumberWeldChanger").append(optgroup);

					$("#ReportnumberWeldChanger").find("option").prop('selected',false);
					$("#ReportnumberWeldChanger").find("option[value='" + data.evalId + "']").prop('selected',true);

					JsonReload($("#CurrentEditEvaluationUrl").val());
					$("div.pagin_links a#description_for_link_0").remove();

				}
			}


			UpdateFieldAfterSaving(data);

			$(ThisId).closest("div").append(SuccesIcon);

			__ChangePaginationDescription(data);

			if(data.field_for_saveField == "result" && data.evalId){

				if(data.WeldEdit == 0){
					var current_link_icon = $("div.pagin_links ").find("a[rel=" + data.evalId + "]:not('.icon_weld')");
				}
				if(data.WeldEdit == 1){
					var current_link_icon = $("div.pagin_links ").find("a[rel=" + data.evalId + "]:not('.icon_exam_part')");
				}

				current_link_icon.removeClass('small_error_result');
				current_link_icon.removeClass('small_no_result');
				current_link_icon.removeClass('small_okay_result');

				if(data.this_value_for_saveField == "0") current_link_icon.addClass('small_no_result');
				if(data.this_value_for_saveField == "1") current_link_icon.addClass('small_okay_result');
				if(data.this_value_for_saveField == "2") current_link_icon.addClass('small_error_result');

				if(data.WeldEdit == 0){

					$.each(data.EvaluationIds, function (i,val) {

						var current_weld_icon = $("div.pagin_links ").find("a[rel=" + val + "]:not('.icon_exam_part')");

						current_weld_icon.removeClass('small_error_result');
						current_weld_icon.removeClass('small_no_result');
						current_weld_icon.removeClass('small_okay_result');

						if(data.WeldResult == 2){
							current_weld_icon.addClass('small_error_result');
							return true;
						}
						if(data.WeldResult == 1){
							current_weld_icon.addClass('small_okay_result');
							return true;
						}
						if(data.WeldResult == 0){
							current_weld_icon.addClass('small_no_result');
							return true;
						}
					});
				}
			}

			$(ThisId).closest("div").removeAttr("title");
			$(ThisId).closest("div").tooltip();

			if($(".error_data_field").length > 0)$(".error_data_field").attr("disabled",false);

		} else {

			$(ThisId).closest("div").append(ErrorIcon);
			$(ThisId).val(data.last_value);

			var Mess = "";

			$.each(data.FlashMessages, function (i) {

				var message = data.FlashMessages[i];

				if(message.type != "error") return true;

				Mess += message.message + "\n";

			});

			$(ThisId).closest("div").attr("title",Mess);
			$(ThisId).closest("div").tooltip();

		}

	}

	function FormRequestActionError(data){

		$("div.loader").remove();
		json_request_animation("Error");

	}

	function __CheckFieldEmpty(field){

		var ThisName = "data[" + $("#CurrentModel").val() + "][" + field +"]";

		if($("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").length === 0) return true;
		if($("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").prop("disabled") == true) return true;

 		if($("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").val().length === 0){

			$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").addClass("error");
			return false;

		} else {

			$("form#ReportnumberEditevalutionForm").find("input[name='" + ThisName + "']").removeClass("error");
			return true;

		}
	}

	function __RemoveErrorClass(Id){

		ThisId = "#" + Id;

		if($(ThisId).length === 0) return true;

 		if($(ThisId).val().length === 0){

		} else {

			$(ThisId).removeClass("error");

		}
	}

	function JsonAfterDelete(data){

		if(!data.deletetElement) return false;

		$("#JsonSvgLoader").hide();

		json_request_animation("Success");

		if(data.BreadcrubUrl){

			$("div.pagin_links").find("a[data-desc='" + data.paginationOverview.current_weld_description + "']:not('.icon_exam_part')").attr("href",data.BreadcrubUrl);

		}

		if(data.SaveAjaxUrl){

			var postdata = new Array();
			postdata.push({name: "ajax_true", value: 1});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: data.SaveAjaxUrl,
				data	: postdata,
				success: function(data) {
					$("#container").html(data);
					$("#container").show();
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

		} else {

			var weld = data.paginationOverview.current_weld_description;

			$.each(data.deletetElement, function (key,value) {

				$("div.pagin_links").find("a[rel='" + key + "']:not('.icon_weld')").remove();

			});

			JsonReload(data.EditJsonUrl);

		}
	}

	function JsonReload(url){

		var data = new Array();

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: url,
      data	: data,
      dataType: "json",
      success: function(data) {
				UpdateFormElements(data)
				PaginationOverview(data);
				StatusCheck(data);
				ChangeLinkCss(data.paginationOverview);
				return false;
    	},
			statusCode: {
		    404: function() {
		      alert( "page not found" );
					location.reload();
		    },
				403: function() {
		      alert( "page blocked" );
					location.reload();
		    }
		  },
			error: function(){
				json_request_animation("Error");
		  },
			complete: function(data) {
			}
    });
	}

	function JsonLink(handle){

		$(handle).click(function() {

			if($(this).hasClass('icon_del')){
				var test = confirm($("#ReportnumberDeleteDescription").val() + ": " + $("#ReportnumberDeleteDescription").attr("data-desc"));
				if(test == false) return false;
			}

			if(__CheckFieldEmpty("description") === false) return false;
			if(__CheckFieldEmpty("position") === false) return false;

			var data = new Array();
			var url = $(this).attr("href");

			$(".check_circle").remove();

			if($(this).attr("rev") == 1 && $(this).hasClass("small_active")){

				var url_new = url.split("/");

				url_new.splice(9);
				url_new.push(0);

				var url = url_new.join("/");

			}

			json_request_load_animation();

	    data.push({name: "json_true", value: 1});
	    data.push({name: "ajax_true", value: 1});

	    $.ajax({
	      type	: "POST",
	      cache	: false,
	      url		: url,
	      data	: data,
	      dataType: "json",
	      success: function(data) {

					var out = FlashMessage(data);

					if(out == true){

						JsonAfterDelete(data);
						JsonRequestForm(data);
						PaginationOverview(data);
						StatusCheck(data);
						CheckUpdateRevision(data);
						AddNewEvaluation(data);
						ChangeLinkCss(data.paginationOverview);

					}
	    	},
				statusCode: {
			    404: function() {
			      alert( "page not found" );
						location.reload();
			    },
					403: function() {
			      alert( "page blocked" );
						location.reload();
			    }
			  },
				error: function(){
					json_request_animation("Error");
			  },
				complete: function(data) {
					json_request_stop_animation();
				}
	    });

			return false;
		})
	}

	$("#ReportnumberWeldChanger").change(function() {

		if(__CheckFieldEmpty("description") === false) return false;
		if(__CheckFieldEmpty("position") === false) return false;

		$(".check_circle").remove();

		var data = new Array();
		var url = $("#CurrentEditEvaluationUrl").val();
		var this_id = $(this).val();

		var url_new = url.split("/");

		url_new[8] = this_id;
		url_new[9] = 0;

		if($("div.pagin_links ").find("a[data-desc='" + this_id + "']").length > 0){
			url_new[8] = $("div.pagin_links ").find("a[data-desc='" + this_id + "']").attr("rel");
			url_new[9] = 1;
		}

		if($("#ReportnumberHasNoPosition").length){
			url_new[9] = 1;
		}

		var url = url_new.join("/");

		json_request_load_animation();

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: url,
      data	: data,
      dataType: "json",
      success: function(data) {
				JsonAfterDelete(data);
        JsonRequestForm(data);
				PaginationOverview(data);
				StatusCheck(data);
    	},
			statusCode: {
		    404: function() {
		      alert( "page not found" );
					location.reload();
		    },
				403: function() {
		      alert( "page blocked" );
					location.reload();
		    }
		  },
			error: function(){
				json_request_animation("Error");
		  },
			complete: function(data) {
				json_request_stop_animation();
			}
    });

		return false;
	})

	$("form.editreport input:not(.hide_box), form.editreport select:not(#ReportnumberWeldChanger), form.editreport textarea").change(function() {

		$(".check_circle").remove();

		var AttrStopSaving = $(this).attr('stop-saving');

		if ($(this).attr('stop-saving') == 1) return false;

		var orginal_data = CollectFormData();

		$("#content .edit a.print").data('prevent',1);

		$(this).attr({"disabled":"disabled","rel":"saving"});

		$(this).closest("div").append(loader);

		var val = $(this).val();
		var this_id = $(this).attr("id");
		var this_submit_id = $(this).attr("id") + "_submit";
		var name = $(this).attr("name");

		__RemoveErrorClass(this_id);

		// Bei Multiselectfeldern nicht das Select als Quelle verwenden, wenn das Event vom Textfeld kommt.
		if($(this).closest("div").hasClass("select") && $(this).attr("multiple") == undefined){
			if($(this).is('select')) {
				if($(this).find("option:selected").attr("rel") == "custom"){
					$(this).closest("div").find("label").css("background-image","none");

					return false;
				}
				val = $(this).find('option:selected').text();
			} else {
				$(this).siblings('select').val($(this).val().split(/[\r\n ,;]+/));
			}
		}

		if($(this).closest("div").hasClass("checkbox")){
			if($(this).attr("checked") === undefined){
				val = 0;
			} else {
				val = 1;
			}
		}

		if($(this).attr("multiple") != undefined){

			if($(this).attr("id") == "ReportRtEvaluationError"){

			} else {

			if(val.length > 0){
				input = "";
				$(val).each(function(key,value){
					if(value.length > 0){
						text = $("#"+this_id+" option[value="+value+"]").text();
						if(text.length > 0){
							input += text + "\n";
						}
					}
				});
			} else {
				input = null;
			}

			val = input;
			}
		}

		var url = $("#ReportnumberFastSaveJsonUrl").val();
		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "this_id", value: this_id});
		data.push({name: name, value: val});

		$.each(orginal_data, function(key, value) {
			data.push({name: key, value: value});
		});

		if($(this).hasClass("edit_after_closing")){
			data.push({name: "edit_after_closing", value: 1});
		}

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			dataType: "json",
			beforeSend: function(){
				$("#ReportnumberEditevalutionForm :input").prop("readonly", true);
			},
			success: function(data) {
				FormRequestActionOkay(data);
				CheckUpdateRevision(data);
			},
			error: function(data){
				FormRequestActionError(data);
			},
			complete: function(data) {
				$("#content .edit a.print").data('prevent',0);
				$("#ReportnumberEditevalutionForm :input").prop("readonly", false);
		}
		});

		return false;
	});

	JsonReload($("#CurrentEditEvaluationUrl").val());
	JsonLink("a.json");

});
</script>
