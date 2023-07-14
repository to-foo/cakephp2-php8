<script type="text/javascript">
$(document).ready(function(){

  function UpdateEvaluationnTable(data){

    if(data.Field == "position") return false;

    $.each(data.Results, function(key, value) {

      let elm = $("table.editable").find("p[data-id='" + value + "'][data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-type='editpos']");

      elm.text(data.Value);
      elm.removeClass("attention_field");

    });
  }

  function UpdateEvaluationnTableSelect(data){

    if(data.Field == "position") return false;

    $.each(data.Results, function(key, value) {

      let elm = $("table.editable").find("p[data-id='" + value + "'][data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-type='editpos']");

      elm.text(data.Value);
      elm.removeClass("attention_field");

    });

  }

  function UpdateEvaluationnMessage(data){
console.log(data);
    $("table.editable p.editable").removeClass("attention_field_json_success");
    $("table.editable p.editable").removeClass("attention_field_json_error");

    if(data.Updatetype == "editpos") __MessagePos(data);
    if(data.Updatetype == "editweld") __MessagePos(data);
    if(data.Updatetype == "editall") __MessageAll(data);
    if(data.Updatetype == "editwelddescription") __MessageWeldDescription(data);
    if(data.Updatetype == "editweldposition") __MessageWeldPosition(data);

    

//    elm.addClass("attention_field_json_success");

  }

  function __MessageWeldPosition(data){

    if(data.Message == "success"){
      var Class = "attention_field_json_success";
    }
    if(data.Message == "error"){
      var Class = "attention_field_json_error";
    }

    let Id = "#" + $("table.editable").find("p[data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-id='" + data.Result + "'][data-type='editpos']").attr("id");

      $(Id).addClass(Class);

      let elm = document.querySelector(Id);

      elm.style.animationName = "none";

      requestAnimationFrame(() => {
        setTimeout(() => {
          elm.style.animationName = ""
        }, 0);
      });

    if(data.Message == "error"){

      $(Id).text(data.Value);

    }

  }

  function __MessageWeldDescription(data){

    if(data.Message == "success"){
      var Class = "attention_field_json_success";
    }
    if(data.Message == "error"){
      var Class = "attention_field_json_error";
    }

    let Id = "#" + $("table.editable").find("p[data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-id='" + data.Result + "']").attr("id");

    $(Id).addClass(Class);

    let elm = document.querySelector(Id);

    elm.style.animationName = "none";

    requestAnimationFrame(() => {
      setTimeout(() => {
        elm.style.animationName = ""
      }, 0);
    });

    if(data.Message == "error"){

      $(Id).text(data.Value);

    }

  }

  function __MessagePos(data){

    if(data.Message == "success"){
      var Class = "attention_field_json_success";
    }
    if(data.Message == "error"){
      var Class = "attention_field_json_error";
    }

    $.each(data.Results, function(key, value) {

      let Id = "#editable_" + data.Field + "_" + value;

      $(Id).addClass(Class);

      let elm = document.querySelector(Id);

      elm.style.animationName = "none";

      requestAnimationFrame(() => {
        setTimeout(() => {
          elm.style.animationName = ""
        }, 0);
      });

    });

  }

  function __MessageAll(data){

    if(data.Message == "success"){
      var Class = "attention_field_json_success";
    }
    if(data.Message == "error"){
      var Class = "attention_field_json_error";
    }

    if($("table.editable").find("p[data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-type='editpos']").hasClass(Class)){

      $.each($("table.editable").find("p[data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-type='editpos']"), function() {

        let Id = "#" + $(this).attr("id");
        let elm = document.querySelector(Id);

        elm.style.animationName = "none";

        requestAnimationFrame(() => {
          setTimeout(() => {
            elm.style.animationName = ""
          }, 0);
        });
      });

    } else {

      $("table.editable").find("p[data-model='" + data.Model + "'][data-field='" + data.Field + "'][data-type='editpos']").addClass(Class);

    }
  }

  function UpdateEditableRadio(data){

      $("table.editable").find("p[data-type='editweld']").not("p[data-field='description']").text("Edit");
      $("table.editable").find("p[data-type='editall']").text("Edit");

  }

  function UpdateEditableSelect(data){

    if(!data.fieldid) return false;

    let Id = data.fieldid;

    $(data.options).each( function(key,val) {

      $("<option/>").val(val.key).text(val.val).appendTo(Id + " form select");

    });

    $(Id + " form select option[value='" + data.id + "']").attr('selected',true);

  }

  $('.editabletext').editable(function(value, settings) {

    if(value == "") return false;

//    json_stop_animation();

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisClass = $(this).attr("class");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
    Field = "data[" + ThisModel + "][" + ThisField + "]";
    FieldId = "data[" + ThisModel + "][id]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: "Class", value: ThisClass});
    data.push({name: Field, value: value});
    data.push({name: FieldId, value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#UrlEditTableUp").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        UpdateEvaluationnTable(data);
        UpdateEditableRadio(data);
        UpdateEvaluationnMessage(data);
      }
    });

    return(value);

	}, {
    type   : 'text',
    onblur : "submit",
    submit : 'OK',
    cancel : 'Cancel',
    placeholder : "Edit",

	});

  $('.editableselect').editable(function(value, settings) {

    if(value == "") return false;

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisClass = $(this).attr("class");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
    Field = "data[" + ThisModel + "][" + ThisField + "]";
    FieldId = "data[" + ThisModel + "][id]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: "Class", value: ThisClass});
    data.push({name: Field, value: value});
    data.push({name: FieldId, value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#UrlEditTableUp").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        UpdateEvaluationnTableSelect(data);
        UpdateEditableRadio(data);
        UpdateEvaluationnMessage(data);
      }
    });

    return(value);

	}, {
    data : function(){

      data = new Array();

      ThisId = $(this).attr("data-id");
      ThisFieldId = $(this).attr("id");
      ThisClass = $(this).attr("class");
      ThisModel = $(this).attr("data-model");
      ThisField = $(this).attr("data-field");
      ThisType = $(this).attr("data-type");

      data.push({name: "json_true", value: 1});
      data.push({name: "ajax_true", value: 1});
      data.push({name: "Mod", value: "report"});
      data.push({name: "Modul", value: "evaluation_area"});
      data.push({name: "Class", value: ThisClass});
      data.push({name: "Model", value: ThisModel});
      data.push({name: "Field", value: ThisField});
      data.push({name: "ThisFieldId", value: ThisFieldId});
      data.push({name: "Type", value: ThisType});
      data.push({name: "DataId", value: ThisId});

      $.ajax({
        type	: "POST",
        cache	: false,
        url		: "dropdowns/get",
        data	: data,
        dataType: "json",
        success: function(data) {
          UpdateEditableSelect(data);
        }
      });
      return false;
    },
    type   : 'select',
    onblur : "submit",
    submit : 'OK',
    cancel : 'Cancel',
    placeholder : "Edit",

	});

  $('.editableradio').editable(function(value, settings) {

    if(value == "") return false;

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisClass = $(this).attr("class");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
    Field = "data[" + ThisModel + "][" + ThisField + "]";
    FieldId = "data[" + ThisModel + "][id]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: "Class", value: ThisClass});
    data.push({name: Field, value: value});
    data.push({name: FieldId, value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#UrlEditTableUp").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        UpdateEvaluationnTableSelect(data);
        UpdateEditableRadio(data);
        UpdateEvaluationnMessage(data);
      }
    });

    return(value);

	}, {
    data : function(){

      data = new Array();

      ThisId = $(this).attr("data-id");
      ThisFieldId = $(this).attr("id");
      ThisClass = $(this).attr("class");
      ThisModel = $(this).attr("data-model");
      ThisField = $(this).attr("data-field");
      ThisType = $(this).attr("data-type");

      data.push({name: "json_true", value: 1});
      data.push({name: "ajax_true", value: 1});
      data.push({name: "Mod", value: "report"});
      data.push({name: "Modul", value: "evaluation_area"});
      data.push({name: "Class", value: ThisClass});
      data.push({name: "Model", value: ThisModel});
      data.push({name: "Field", value: ThisField});
      data.push({name: "ThisFieldId", value: ThisFieldId});
      data.push({name: "Type", value: ThisType});
      data.push({name: "DataId", value: ThisId});

      $.ajax({
        type	: "POST",
        cache	: false,
        url		: "dropdowns/get",
        data	: data,
        dataType: "json",
        success: function(data) {
          UpdateEditableSelect(data);
        }
      });
      return false;
    },
    type   : 'select',
    onblur : "submit",
    submit : 'OK',
    cancel : 'Cancel',
    placeholder : "Edit",

	});

});
</script>
