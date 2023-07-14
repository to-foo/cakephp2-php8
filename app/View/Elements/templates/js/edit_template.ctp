<?php
$TemplateUrl = $this->Html->url(array_merge(array('controller' => 'templates','action' => 'json'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisTemplateUrl', array('type' => 'hidden','value' => $TemplateUrl));
?>

<script type="text/javascript">
$(document).ready(function(){

	function RemoveTableRow(data){

		if(data.Message.error){

			alert(data.Message.error);

		} else {

			$("table.advancetool").find("a[rel='" + data.TemplatesEvaluation.id + "']").closest("tr").remove();

		}

	}

	function UpdateTemplateTable(data){

		if(data.DataEditPos){__UpdateTemplateTablePos(data)};
		if(data.DataEditWeld){__UpdateTemplateTableWeld(data)};
		if(data.DataEditTemp){__UpdateTemplateTableTemplate(data)};

	}

	function __UpdateTemplateTablePos(data){

		var id = "#" + data.DataFieldId;
		var val = data.Value;
		var field = data.DataField;
		var weld = data.DataWeld;

		$(id + " p.editable").text(val);
	}

	function __UpdateTemplateTableWeld(data){

		var id = "#" + data.DataFieldId;
		var val = data.Value;
		var field = data.DataField;
		var weld = data.DataWeld;

		$("table.advancetool").find("p[data-field='"+field+"'][data-weld='"+weld+"']").text(val);

		$(id + " p.editable").text(val);
	}

	function __UpdateTemplateTableTemplate(data){

		var id = "#" + data.DataFieldId;
		var val = data.Value;
		var field = data.DataField;
		var weld = data.DataWeld;

		$("table.advancetool").find("p[data-field='"+field+"']").text(val);

		$(id + " p.editable").text(val);
	}

  $("table.advancetool p.editable").editable($("#ThisTemplateUrl").val(), {

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

	$( "table.advancetool a.delete_evaluation_template").on( "click", function() {

		confirmtext = $(this).attr("title");
		var check = confirm(confirmtext);
		if (check == false) return false;
		if (check == null) return false;

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_blank", value: 1});
		data.push({name: "id", value: $(this).attr("rel")});
		data.push({name: "delete_evaluation_template", value: 1})

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			dataType: "json",
			success: function(data) {
				RemoveTableRow(data);
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

	$( "table.advancetool a.dupli_pos, table.advancetool a.delete_pos" ).on( "click", function() {

//		json_request_load_animation();

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_blank", value: 1});
		data.push({name: "data_class", value: $(this).attr('class')});
		data.push({name: "data_weld", value: $(this).attr('rel')});
		data.push({name: "data_position", value: $(this).attr('rev')});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			dataType: "json",
			success: function(data) {
				$("title.advancetool").html(data);
				$("title.advancetool").show();
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

	$( "form div a.delete_template_field").on( "click", function() {
		$(this).closest("div").remove();
	});
});
</script>
