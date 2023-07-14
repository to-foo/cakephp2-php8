<?php 
echo $this->Form->input('EditUrl',array(
  'type' => 'hidden',
  'value' => Router::url(
    array(
      'controller' => 'expeditings',
      'action'=>'edit',
      $this->request->projectvars['VarsArray'][0],
      $this->request->projectvars['VarsArray'][1],
      $this->request->projectvars['VarsArray'][2]
      )
    )
  )
);
?>
<script type="text/javascript">
$(document).ready(function(){

  var update_expediting_typ_status = function() {

    var show_el = "expediting_step_" + $("#ChooseExpeditingStep").val();

    $("table.advancetool tr.expediting_step").hide();
    $("div.advancetool div.expediting_step").hide();
    $("div.advancetool li.expediting_step").hide();

    $("table.advancetool tr." + show_el).show();
    $("div.advancetool div." + show_el).show();
    $("div.advancetool li." + show_el).show();

    if($("#ChooseExpeditingStep").val() == ""){
      $("table.advancetool tr.expediting_step").show();
      $("div.advancetool div.expediting_step").show();
      $("div.advancetool li.expediting_step").show();
    }
  }

  var update_date_ist = function(data){

    var id = data.id;
    var date = data.data;
    var classes = data.class;
    var tr = "tr_sort_" + id;

    if(classes == "finished"){
      $("tr#" + tr).find("span[data-field='date_ist']").text(date);
    } else {
      $("tr#" + tr).find("span[data-field='date_ist']").text("000-00-00");
    }

  }

  var change_class = function(data) {

    var tr_sort = "#tr_sort_" + data.id;
    var classes = data.class;

    $(tr_sort).removeClass('critical');
    $(tr_sort).removeClass('delayed');
    $(tr_sort).removeClass('plan');
    $(tr_sort).removeClass('future');
    $(tr_sort).removeClass('finished');

    $(tr_sort + " td a.show_history").removeClass('critical');
    $(tr_sort + " td a.show_history").removeClass('delayed');
    $(tr_sort + " td a.show_history").removeClass('plan');
    $(tr_sort + " td a.show_history").removeClass('future');
    $(tr_sort + " td a.show_history").removeClass('finished');

    $(tr_sort).addClass(data.class);
    $(tr_sort + " td a.show_history").addClass(data.class);

    if(classes == "finished"){
      $(tr_sort).find("input[class='expediting_date_checkbox']").prop('checked', true)
    } else {
      $(tr_sort).find("input[class='expediting_date_checkbox']").prop('checked', false)
    }

  }

  $("a.tooltip_content").tooltip({
    content: function () {
      var output = $(this).next().html();
      return output;
    }
  });

  var update_table = function(data) {

    $(data).each(function(index,value){

      var tr_id = "#tr_sort_" + value.Expediting.id;

      if(value.Expediting.stop_for_next_step == 0){
        $(tr_id + " td.editist").removeClass("hide");

      } else {
        $(tr_id + " td.editist").addClass("hide");
      }


    });

  }

  update_expediting_typ_status();

  $('.expediting_date_checkbox').change(function(value, settings) {

    json_stop_animation();

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");

    if($(this).is(':checked') == false) ThisValue = "1970-01-01";
    if($(this).is(':checked') == true) ThisValue = $(this).attr("data-value");

    Field = "data[" + ThisModel + "][" + ThisField + "]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: Field, value: ThisValue});
    data.push({name: "data[" + ThisModel + "][id]", value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#EditUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        change_class(data.data);
        update_table(data.expediting);
        update_date_ist(data.data);
        json_request_animation(data.value);
        var returndata = data.data.data;
      }
    });

  });

  $('.editabledate').editable(function(value, settings) {

    json_stop_animation();

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
    Field = "data[" + ThisModel + "][" + ThisField + "]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: Field, value: value});
    data.push({name: "data[" + ThisModel + "][id]", value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#EditUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        change_class(data.data);
        update_table(data.expediting);
        json_request_animation(data.value);
        var returndata = data.data.data;
      }
    });

    if(value == "1970-01-01") return "0000-00-00";
    else return value;

	}, {

    type   : "datepicker",
    datepicker : {
    format: "yy-mm-dd"
    },
    submit : 'OK',
    cancel : 'Cancel',
//    onblur : "submit",
    placeholder : "0000-00-00",

	});

  $("a.modal").on('click', function (e) {

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

  $('.editabletext').editable(function(value, settings) {

    json_stop_animation();

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
    Field = "data[" + ThisModel + "][" + ThisField + "]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: Field, value: value});
    data.push({name: "data[" + ThisModel + "][id]", value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#EditUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        json_request_animation(data.value);
      }
    });

    return(value);

	}, {
    type   : 'textarea',
    submit : 'OK',
    cancel : 'Cancel',
    placeholder : "Edit",
//    onblur : "ignore"
	});

  $("select#ChooseExpeditingStep").change(function() {

    update_expediting_typ_status();

  });

  $("table.advancetool tbody tr td div.checkbox input.expediting_type_checkbox").click(function(){

      var involved_class = "tr.expediting_step_" + $(this).val() + " td div.checkbox input.expediting_file_checkbox";

      if($(this).prop("checked") == true){
        $(involved_class).prop('checked', true);
      }
      else if($(this).prop("checked") == false){
        $(involved_class).prop('checked', false);
      }

  });

  $("a.massaction_download").click(function(){

    var url = $(this).attr("href");
    var each_element = "table.advancetool tr.expediting_step_" + $(this).attr("rev") + " td div.checkbox input[type=checkbox]";
    var involved_class = "tr.expediting_step_" + $(this).val() + " td div.checkbox input.expediting_file_checkbox";
    var ids = [];
    var data = new Array();

    $(each_element).each(function(index,value){

      if($(this).closest('tr').hasClass('subheadline')){
        return true;
      }

      if($(this).prop("checked") == true){

        ids.push($(this).val());
        $(this).prop('checked', false);

      }
    });

    if(ids.length == 0){
      alert("Please select some elements.");
      return false;
    }

    $("table.advancetool input").prop("checked", false);

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: "data[Expediting][files]", value: ids});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: url,
      data	: data,
      dataType: "json",
      success: function(data) {
        if(data.message == true) location.href = url;
        else json_request_animation("Error");

//        else alert("Error");
      }
    });

    return false;
  })

  $("a.massaction_mail").click(function(){

    var url = $(this).attr("href");
    var each_element = "table.advancetool tr.expediting_step_" + $(this).attr("rev") + " td div.checkbox input[type=checkbox]";
    var involved_class = "tr.expediting_step_" + $(this).val() + " td div.checkbox input.expediting_file_checkbox";
    var ids = [];
    var data = new Array();

    $("#AjaxSvgLoader").show();

    $(each_element).each(function(index,value){

      if($(this).closest('tr').hasClass('subheadline')){
        return true;
      }

      if($(this).prop("checked") == true){

        ids.push($(this).val());
        $(this).prop('checked', false);

      }
    });


    if(ids.length == 0){
      $("#AjaxSvgLoader").hide();
      alert("Please select some elements.");
      return false;
    }

    $("table.advancetool input").prop("checked", false);

    data.push({name: "ajax_true", value: 1});
    data.push({name: "data[Expediting][files]", value: ids});

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
    $("#dialog").empty();

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
  })

  $("a.massaction_upload").click(function(){

    var url = $(this).attr("href");
    var data = new Array();

    $("#AjaxSvgLoader").show();

    data.push({name: "ajax_true", value: 1});
    data.push({name: "data[Expediting][expediting_type]", value: $(this).attr("rev") });

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
    $("#dialog").empty();

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
  })
});
</script>
