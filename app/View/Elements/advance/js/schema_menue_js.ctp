<?php
$AutoUrl = $this->Html->url(array_merge(array('action' => 'auto'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('AutoUrl',array('type' => 'hidden','value' => $AutoUrl));

?>
<script type="text/javascript">
$(document).ready(function() {

	$("a.showpdflink").click(function() {

	  json_request_load_animation();

	  var data = new Array();

	  data.push({name: "ajax_true", value: 1});
	  data.push({name: "showpdf", value: 1});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: $(this).attr("href"),
	    data	: data,
	    dataType: "json",
	    success: function(data) {
	      EmbedPDF(data);
	    },
	    complete: function(data) {
	      json_request_stop_animation();
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

	$("input.autocompletion").autocomplete({

		minLength: 2,
		delay: 4,
		source:

		function(request,response) {

			var data = new Array();
			data.push({name: "term", value: request.term});
			data.push({name: "ajax_true", value: 1});

			$.ajax({
				type: "POST",
				url: $("#AutoUrl").val(),
				dataType: "json",
				data: data,
				success:
					function(data) {
						response(data);
						},
					});
			},
			change: function( event, ui ) {
			},
			close: function(event,ui) {

			},
			select: function(event,ui) {
			}
	});

	function EmbedPDF(data){

	  var string = "data:application/pdf;base64," + data.string;

		$("div#wrapper_pdf_container").show();
		$("div#show_pdf_contaniner").show();
		$("div#show_pdf_container_navi").show();

		PDFObject.embed(string, "div#show_pdf_contaniner");

		$("a#show_pdf_contaniner_button").click(function() {

			$("div#wrapper_pdf_container").hide();
			$("div#show_pdf_contaniner").hide();
			$("div#show_pdf_container_navi").hide();

		});
	}

	function DeleteAdvance(data){

		if(data.CurrentStatus == undefined) return;

		if(data.CurrentStatus.update == "success") $(data.CurrentStatus.row).hide();

		if(data.CurrentStatus.update == "error") {
			alert(data.CurrentStatus.message);
		}
	}


	function UpdateDiagramm(data){

		$("#advance_diagrammcontent").empty();
		$("#advance_diagrammcontent").addClass("loading");

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $("#StartId").val()});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#DiagrammUrl").val(),
			data	: data,
			success: function(data) {
				$("#advance_diagrammcontent").removeClass("loading");
				$("#advance_diagrammcontent").html(data);
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

	}

	function UpdateStatistic(data){

		if(data.Status == undefined) return;

		$.each(data.Status, function(i,value) {

			if(value.result){
				$("." + value.group_advance + " ." + value.advance_count + " .value").text(value.result.count);
				$("." + value.group_advance + " ." + value.advance_open + " .value").text(value.result.open);
				$("." + value.group_advance + " ." + value.advance_okay + " .value").text(value.result.okay);
				$("." + value.group_advance + " ." + value.advance_error + " .value").text(value.result.error);
				$("." + value.group_advance + " ." + value.advance_line + " .this_advance").animate({ width: value.result.advance_all_percent + "%"});
				$("." + value.group_advance + " ." + value.advance_line + " .this_advance").css("background",value.advance_line_color);
			} else {
				$("." + value.group_advance + " ." + value.advance_count + " .value").text(0);
				$("." + value.group_advance + " ." + value.advance_open + " .value").text(0);
				$("." + value.group_advance + " ." + value.advance_okay + " .value").text(0);
				$("." + value.group_advance + " ." + value.advance_error + " .value").text(0);
				$("." + value.group_advance + " ." + value.advance_line + " .this_advance").animate({ width: "0%"});
				$("." + value.group_advance + " ." + value.advance_line + " .this_advance").css("background","#fff");
			}

		});

	}

	function UpdateTable(data){

		if(data.CurrentStatus == undefined) return;

		status = data.CurrentStatus.update;
		message = data.CurrentStatus.message;
		tr_row_id = "#advance_detail_" + data.AdvancesDataDependency.id;
		value = data.AdvancesDataDependency.value;
		status_value_class = tr_row_id + " td span.status_" + data.AdvancesDataDependency.value;

		if(status == "error"){
			alert(message);
			return;
		}

		if(value == 0) current_status = "deaktiv";
		if(value == 1) current_status = "okay";
		if(value == 2) current_status = "error";

		$(tr_row_id).removeAttr("class");
		$(tr_row_id + " td span").removeClass("deaktiv");
		$(tr_row_id + " td span").removeClass("okay");
		$(tr_row_id + " td span").removeClass("error");
		$(tr_row_id + " td span").removeClass("delay");
		$(tr_row_id + " td span").removeClass("future");
		$(tr_row_id + " td span").addClass("empty");
		$(status_value_class).removeClass("empty");
		$(status_value_class).addClass(current_status);

		$(tr_row_id + " td span.icon_delete").removeClass("empty");

	}

	$(".statistic_days_gone").animate({ width: $("#StatisticDaysGone").val()});

	$('input.advance_line').each(function(){
		$("div." + $(this).attr("id")).animate({ width: $(this).val()});
	})

	$('input.advance_color_line').each(function(){
		$("div." + $(this).attr("id")).css("background-color",$(this).val());
	})

	current_area_detail = "div#advance_tablecontent";
	$("div.advance_content").hide();
	$(current_area_detail).show();

	$("ul.editmenue li a").on('click', function (e) {

		id = "#" + $(this).attr('rel');
		linkid = "#" + "link_" + $(this).attr('rel');

		$("div.advance_content").hide();
		$(id).show();
		$("ul.editmenue li").removeClass("active");
		$("ul.editmenue li").addClass("deactive");
		$("ul.editmenue li" + linkid).addClass("active");

			return false;
	});

	$('.editable').editable(function(value, settings) {

			AdvanceId = $(this).attr("data-id");
			AdvanceField = $(this).attr("data-field");
			Field = "data[AdvancesDataDependency][" + AdvanceField + "]";

			data = new Array();

			data.push({name: "ajax_true", value: 1});
			data.push({name: "json_false", value: 1});
			data.push({name: "scheme_start", value: $("#StartId").val()});
			data.push({name: "data_area", value: "advance_area"});
			data.push({name: Field, value: value});
			data.push({name: "data[AdvancesDataDependency][id]", value: AdvanceId});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $("#EditUrl").val(),
				data	: data,
				dataType: "json",
				success: function(data) {
					var output;
					output = data.result
					return output;
				}
			});

	    return(value);
	}, {
	    type   : "textarea",
			onblur : "submit",
			placeholder : "Edit",
	});

	$("select#SelectMenue").on('change', function (e) {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $(this).val()});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#SchemeUrl").val(),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
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

	$("select#SelectEquipment").on('change', function (e) {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $(this).val()});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#SchemeUrl").val(),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
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

	$("select#SelectSubMenue").on('change', function (e) {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $(this).val()});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#SchemeUrl").val(),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
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

	$("a.scheme_menue_link").click(function() {

		$("#AjaxSvgLoader").show();

		data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $(this).attr('rel')});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		if($(this).attr('rev').length > 0)  data.push({name: "cascade_group_id", value: $(this).attr('rev')});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr('href'),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
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

	$("a.cascade_group").click(function() {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "scheme_start", value: $("#StartId").val()});
		data.push({name: "cascade_group_id", value: $(this).attr("rel")});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
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

	$("span.edit_advance").on('click', function (e) {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "advance_id", value: $(this).text()});
		data.push({name: "scheme_start", value: $("#StartId").val()});

		if($(this).hasClass("edit_empty")) data.push({name: "update", value: 0});
		if($(this).hasClass("edit_okay")) data.push({name: "update", value: 1});
		if($(this).hasClass("edit_error")) data.push({name: "update", value: 2});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#EditUrl").val(),
			data	: data,
			dataType: "json",
			success: function(data) {
				UpdateTable(data);
				UpdateStatistic(data);
				UpdateDiagramm(data);
				$("#AjaxSvgLoader").hide();
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

	$("span.icon_delete").on('click', function (e) {

		bgcolor = $(this).closest("tr td").css("background-color");
		$(this).closest("tr td").css("background-color","#ff9b9b");

		check = confirm("<?php echo __('Should this value be deleted?',true);?>");

		if (check == false) {
			$(this).closest("tr td").css("background-color",bgcolor);
			return false;
		}

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
		data.push({name: "data[AdvancesDataDependency][id]", value: $(this).text()});
		data.push({name: "scheme_start", value: $("#StartId").val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#DelUrl").val(),
			data	: data,
			dataType: "json",
			success: function(data) {
				$("#AjaxSvgLoader").hide();
				DeleteAdvance(data);
				UpdateStatistic(data);
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

		$(this).closest("tr td").css("background-color",bgcolor);
		return false;

	});

	$("a.advance_settings").click(function (e) {

		$("#AjaxSvgLoader").show();

		$(".ui-dialog").show();
		$("#dialog").dialog().dialog("close");
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

		data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $(this).attr('rel')});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

//		if($(this).attr('rev').length > 0)  data.push({name: "cascade_group_id", value: $(this).attr('rev')});

		$("#dialog").empty();

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").dialog("open");
				$("#dialog").show();
				$("#AjaxSvgLoader").hide();
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

	$("a.add_advance_point").click(function (e) {

		$("#AjaxSvgLoader").show();

		$(".ui-dialog").show();
		$("#dialog").dialog().dialog("close");
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

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: $("ul.editmenue li.active a").attr("rel")});
		data.push({name: "data[AdvancesDataDependency][scheme_start]", value: $("#StartId").val()});
		data.push({name: "data[AdvancesDataDependency][order_id]", value: $(this).attr("rel")});
		data.push({name: "data[AdvancesDataDependency][cascade_id]", value: $("#StartId").val()});

		$("#dialog").empty();

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").dialog("open");
				$("#dialog").show();
				$("#AjaxSvgLoader").hide();
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

	$("a.modal").click(function (e) {

		$("#AjaxSvgLoader").show();

		$(".ui-dialog").show();
		$("#dialog").dialog().dialog("close");
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

		data = new Array();
		data.push({name: "ajax_true", value: 1});

		$("#dialog").empty();

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").dialog("open");
				$("#dialog").show();
				$("#AjaxSvgLoader").hide();
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

  $("a.blank").click(function (e) {

		$("#AjaxSvgLoader").show();

		data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: $(this).attr("rel")});
    data.push({name: "scheme_start", value: $(this).attr("rev")});

		$("select#SelectMenue option").each(function(){data.push({name: "select_menue[" + $(this).val() + "]", value: $(this).text()})});

		if($("#CascadeGroupId").val().length > 0) data.push({name: "cascade_group_id", value: $("#CascadeGroupId").val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
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

	$("a#advancemanager").css("visibility","hidden");

	$("a.back_history").on('click',function(e) {

		$("a#advancemanager").css("visibility","hidden");

	  $("a#advancemanager").show();
	  $("a#advancemanager").css("visibility","visible");
		$("a#advancemanager").addClass("ajax");
	  $("a#advancemanager").attr("href",$("#CurrentUrl").val());
		$("a#advancemanager").attr("rel",$("#StartId").val());
		$("a#advancemanager").attr("rev",$("#SchemeUrl").val());

		$("#AjaxSvgLoader").show();

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#AjaxSvgLoader").hide();
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
