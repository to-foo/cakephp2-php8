<?php
$TemplateUrl = $this->Html->url(array_merge(array('controller' => 'templates','action' => 'json'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisTemplateUrl', array('type' => 'hidden','value' => $TemplateUrl));
?>

<script type="text/javascript">
$(document).ready(function(){

	function UpdateAttentionField2(data){

		let Id = data.data.Id;
		let Name = data.data.Name;
		let Class = data.data.Class;

		$("table.advancetool").find("td[form-name='" + Name + "']").removeClass("attention_field");
		if(Class != "") $("table.advancetool").find("td[form-name='" + Name + "']").addClass("attention_field");

	}

	function UpdateAttentionField1(data){

		let Id = data.data.Id;
		let Name = data.data.Name;
		let Class = data.data.Class;
		let Model = data.data.Model;
		let Field = data.data.Field;

		$("form#TemplateDataEditForm").find("[rev='" + Model + "'][rel='" + Field + "'][class='attention_template_field']").removeClass("icon_attention_unmarked");
		$("form#TemplateDataEditForm").find("[rev='" + Model + "'][rel='" + Field + "'][class='attention_template_field']").addClass("icon_attention_marked");

		let elm = $("form#TemplateDataEditForm").find("[rev='" + Model + "'][rel='" + Field + "']").not("[href='javascript:']");

		if(Class != ""){

			elm.removeClass("icon_attention_unmarked");
			elm.addClass("icon_attention_marked");

		} else {

			elm.removeClass("icon_attention_marked");
			elm.addClass("icon_attention_unmarked");

		}

/*
		if($("form#TemplateDataEditForm").find("[name='" + Name + "']").closest("div").hasClass("radio") === true){
			__UpdateAttentionRadio(data);
			return false;
		}

		if($("form#TemplateDataEditForm").find("[name='" + Name + "']").closest("div").hasClass("select") === true){
			__UpdateAttentionInputSelect(data);
			return false;
		}

		if($("form#TemplateDataEditForm").find("[name='" + Name + "']").closest("div").hasClass("input") === true){
			__UpdateAttentionInputSelect(data);
			return false;
		}
*/
	}

	function __UpdateAttentionInputSelect(data){

		let Id = data.data.Id;
		let Name = data.data.Name;
		let Class = data.data.Class;

		$("form#TemplateDataEditForm").find("[name='" + Name + "']").removeClass("attention_field");
		if(Class != "") $("form#TemplateDataEditForm").find("[name='" + Name + "']").addClass("attention_field");

	}

	function __UpdateAttentionRadio(data){

		let Id = data.data.Id;
		let Name = data.data.Name;
		let Class = data.data.Class;

		$("form#TemplateDataEditForm").find("[name='" + Name + "']").closest("div").removeClass("attention_field");
		if(Class != "") $("form#TemplateDataEditForm").find("[name='" + Name + "']").closest("div").addClass("attention_field");

	}

	$( "a.attention_template_field").on( "click", function() {

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_blank", value: 1});
		data.push({name: "attention", value: 1});
		data.push({name: "field", value: $(this).attr('rel')});
		data.push({name: "model", value: $(this).attr('rev')});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			dataType: "json",
			success: function(data) {
				if(data.type == 1) UpdateAttentionField1(data);
				if(data.type == 2) UpdateAttentionField2(data);
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
