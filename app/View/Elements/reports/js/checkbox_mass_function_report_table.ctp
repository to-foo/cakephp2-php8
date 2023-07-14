<?php
$massaction_url =  $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'mass_report_action'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('MassActionUrl',array('type' => 'hidden','value' => $massaction_url));
?>
<script>
$(function(){

  var all_ids = new Array();

  $(".checkbox").controlgroup();

  $( ".controlgroup_vertical" ).controlgroup({
    "direction": "vertical"
  });

  $( "#mass_function_report_table_start" ).selectmenu({
    change: function(){
      massfunction($(this).val());
    }
  });

  $("table.reports_table tr th .checkbox input[type=checkbox]").on("change", function() {

    var status = this.checked;

    $("table.reports_table tr .checkbox_massfunction input[type=checkbox]").prop("checked", status);

    $( ".checkbox" ).controlgroup( "refresh" );

  });

  $("table.reports_table tr .checkbox_massfunction input[type=checkbox]").on("change", function() {
  });

  massfunction = function(action){

    if(action == 0) return false;

    var all_ids = new Array();

    $("table.reports_table tr .checkbox_massfunction input[type=checkbox]").each(function(key,value){

      if ($(this).prop('checked')) {
        all_ids.push($(this).val());
      }

    });

    if(all_ids.length == 0) return false;

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

  	var data = new Array();
  	data.push({name: "ajax_true", value: 1});
  	data.push({name: "dialog", value: 1});
    data.push({name: "data[Reportnumber][MassSelect]", value: action});
    data.push({name: "data[Reportnumber][MassSelectetIDs]", value: all_ids});

  	$("#dialog").empty();

  	$.ajax({
  		type	: "POST",
  		cache	: false,
      url		: $("#MassActionUrl").val(),
  		data	: data,
  		success: function(data) {
        $("#mass_function_report_table_0").prop("checked", false);
        $("table.reports_table tr .checkbox_massfunction input[type=checkbox]").prop("checked", false);
        $("table.reports_table tr th .checkbox #mass_function_report_table_start").val(0);
        $(".checkbox").controlgroup("refresh");
        $(".controlgroup_vertical").controlgroup("refresh");
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

  };
});
</script>
