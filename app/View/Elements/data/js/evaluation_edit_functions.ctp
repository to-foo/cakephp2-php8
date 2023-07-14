<script type="text/javascript">
$(document).ready(function(){


  updateRows = function(event, ui) {

    $(".sortable").addClass("is_sorting").sortable().sortable( "option", "disabled", true);

    data = {ajax_true: 1};

 //   $(this).find("tr[rel]").each(function(i, e) {
    $("table.sortable").find("tr[rel]").each(function(i, e) {
      data[i] = {
        value: i,
        id: "editable_sorting_"+$(e).attr("rel"),
        report_number: $("#ReportnumberId").val()
      };

      $(e).find(".col_sorting span").text(i);
    });

    $.post($("#UrlEditTableUp").val(), data, function(e) {
      $(".sortable").removeClass("is_sorting").sortable().sortable( "option", "disabled", false);
    });

  }

  if($(".sortable").length != 0) {
  $(".sortable").sortable({
    items: "> tbody",
    appendTo: "parent",
    helper: "clone",
    cursor: "move",
    update: updateRows
  }).children("tbody").sortable({
    items: "tr:not(tr:first-child)",
    cursor: "move",
    update: updateRows
  });
  }

  $(".reportnumber_all_welds").click(function() {

    $('input:checkbox').prop('checked', this.checked);

  });

  $(".check_weld").click(function() {

    var weld_id = $(this).attr("id");
    var weld_val = $(this).val();

    if(this.checked == false) var check = false;
    else var check = true;

    $("input.check_weld_position").each(function(){
      if($(this).attr("weld-id") == weld_val){
        $(this).prop("checked", check);
      }
    });
  });

  $("a#printWeldLabels").click(function() {

    form = "<form id=\"tmpForm\" action=\""+$(this).attr("href")+"\" method=\"POST\" target=\"_blank\">";

    data = $("#ReportnumberMassFunktion").serializeArray();

    print = 0;

    for(id in data) {
      if(data[id]["name"].lastIndexOf("data[Reportnumber]", 0) === 0 && data[id]["value"] != 0){
        print = 1;
        form += "<input type=\"text\" name=\""+data[id]["name"]+"\" value=\""+data[id]["value"]+"\" />";
      }
    }

  form += "</form>";

  if(print == 1) {
    $("body").append(form);
    $("body > #tmpForm").submit().remove();
  } else {
    alert("'.__('No welds selected').'");
  }

  return false;

  });

  $(".check_weld_position").click(function() {

    var weld_id = $(this).attr("weld-id");
    var pos_count = 0;
    var pos_count_checked = 0;

    $("#ReportnumberMassFunktion input.check_weld_position").each(function(){

      if($(this).attr("weld-id") == weld_id){

        pos_count++;

        if($(this).attr("checked") == "checked"){
          pos_count_checked++;
        }
      }
    });

    if(pos_count_checked == 0){
      $("#ReportnumberWeldhead" + weld_id).attr("checked", false);
//      $("#ReportnumberWeldhead" + weld_id).button("refresh");
    }

    if(pos_count_checked == pos_count){
      $("#ReportnumberWeldhead" + weld_id).attr("checked", true);
//      $("#ReportnumberWeldhead" + weld_id).button("refresh");
    }
  });

  $("a.context").click(function() {

    var weld_id = '#' + $(this).attr("rev");
    var weld_val = $(this).attr("rel");
    var data_url = $(this).attr("data-url");
    var data_mode = $(this).attr("data-mode");

    if(data_mode == "weld"){

      $(weld_id).prop("checked", true);

      $("input.check_weld_position").each(function(){
        if($(this).attr("weld-id") == weld_val){
          $(this).prop("checked", true);
        }
      });

    }

    if(data_mode == "position"){

      $(weld_id).prop("checked", true);

    }

    var data = $("#ReportnumberMassFunktion").serializeArray();

    data.push({name: "data[Reportnumber][MassSelect]", value: data_url});
    data.push({name: "ajax_true", value: 1});
    data.push({name: "dialog", value: 1});

    $(weld_id).prop("checked", false);

    $("input.check_weld_position").each(function(){
      if($(this).attr("weld-id") == weld_val){
        $(this).prop("checked", false);
      }
    });

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
      url		: $(this).attr("href"),
      data	: data,
      success: function(data) {
        $("#dialog").html(data);
  			$("#dialog").dialog("open");
  			$("#dialog").show();
  			$("#dialog").css('overflow','scroll');
  			$("#AjaxSvgLoader").hide();
      }
    });

    return false;
  });


  $("#ReportnumberMassSelect").change(function() {

    if($("#ReportnumberMassSelect option:selected").val() == 0) return false;

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

    var data = $("#ReportnumberMassFunktion").serializeArray();

    data.push({name: "ajax_true", value: 1});
    data.push({name: "dialog", value: 1});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#UrlMassAction").val(),
      data	: data,
      success: function(data) {
        $("#dialog").html(data);
  			$("#dialog").dialog("open");
  			$("#dialog").show();
  			$("#dialog").css('overflow','scroll');
  			$("#AjaxSvgLoader").hide();
      }
    });

    $("#ReportnumberMassSelect").val(0);

    $('input:checkbox').prop('checked', false);

    $("#ReportnumberMassSelect option[value=\'0\']").attr("selected",true);

    return false;

  });

});
</script>
