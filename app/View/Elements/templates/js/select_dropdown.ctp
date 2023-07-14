<script type="text/javascript">
$(document).ready(function() {

	function formatOption (option) {
    var $option = $(
      '<div><strong>' + option.text + '</strong></div><div>' + option.title + '</div>'
    );
    return $option;
  };

	function PutAttention(data){

		$.each(data.AttentionInfo, function(key, value) {

			let Name = value.name;
			let Val = value.value;

			if($("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").hasClass("input") === true){
				if($("form#ReportnumberEditForm").find("[name='" + Name + "']").val() == Val){
					__PutAttentionInfosInputSelect(Name);
				}
			}

			if($("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").hasClass("select") === true){
				if($("form#ReportnumberEditForm").find("[name='" + Name + "'] option:selected").text() == Val){
					__PutAttentionInfosInputSelect(Name);
				}
			}

			if($("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").hasClass("radio") === true){
				if($("input[name='"+Name+"']:checked").val() == Val){
					__PutAttentionInfosRadio(Name);
				}
			}

		});

		$(".attention_field").on("change", function() {
			console.log($(this).attr("type"));
			$(this).removeClass('attention_field');

			if($(this).attr("type") == "radio"){
				let Name = $(this).attr("name");
				$("form#ReportnumberEditForm").find("[name='" + Name + "']").removeClass("attention_field");
				$("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").removeClass("attention_field");
			}

		});

	}

	function PutTemplateData(data){

		$.each(data.data, function(key, value) {

			$.each(value, function(_key, _value) {

				__PutTemplateDataInput(_value)
				__PutTemplateDataRadio(_value)
				__PutTemplateDataSelect(_value)
				__PutTemplateDataMultiple(_value)
				__PutTemplateDataTextarea(_value)

			});
		});

		__PutAttentionInfos(data);

		json_request_stop_animation();

		return data;
	}

	function __PutAttentionInfos(data){

		$("form#ReportnumberEditForm input, form#ReportnumberEditForm select, form#ReportnumberEditForm textarea").removeClass("attention_field");

		if(!data.attention) return false;

		$.each(data.attention, function(key, value) {

			let Model = key;

			$.each(value, function(key2, value2) {

				let Field = key2;
				let Name = "data["+Model+"]["+Field+"]";

				if($("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").hasClass("radio") === true){
					__PutAttentionInfosRadio(Name);
					return false;
				}

				if($("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").hasClass("input") === true){
					__PutAttentionInfosInputSelect(Name);
				}

				if($("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").hasClass("select") === true){
					__PutAttentionInfosInputSelect(Name);
				}

			});
		});

	}

	function __PutAttentionInfosInputSelect(Name){

		$("form#ReportnumberEditForm").find("[name='" + Name + "']").removeClass("attention_field");
		$("form#ReportnumberEditForm").find("[name='" + Name + "']").addClass("attention_field");

	}

	function __PutAttentionInfosRadio(Name){

		$("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").removeClass("attention_field");
		$("form#ReportnumberEditForm").find("[name='" + Name + "']").closest("div").addClass("attention_field");

	}

	function __PutTemplateDataInput(value){

		var name = value.name;
		var id = "#" + value.id;
		var val = value.value;

		if($('[name="'+name+'"]').length == 0) return false;
		if($(id).is("input") == false) return false;
		if($(id).attr("type") == "radio") return false;

		$('[name="'+name+'"]').val(val);

//		$(id).trigger("change");

	}

	function __PutTemplateDataMultiple(value){

		var name = value.name;
		var id = "#" + value.id;
		var val = value.value;

		if($('[name="'+name+'"]').length == 0) return false;

//		var id = "select#" + $('[multiple="multiple"]').attr("id");

		if($(id).is("select") == false) return false;
		if($(id).attr("multiple") == undefined) return false;

		$(id + " option").prop("selected", false);

		if($(id + ' option:contains("'+val+'")').length > 0){
			$(id + ' option:contains("'+val+'")').prop('selected', true);
		} else {

		}

		$("fieldset.multiple_field select").multiSelect('refresh');
	}

	function __PutTemplateDataSelect(value){

		var name = value.name;
		var val = value.value;
		var id = "#" + value.id;

		if($(id).length == 0) return false;

		if($(id).attr("multiple") == "multiple") return false;

		if($(id).is("select") == false) return false;

		$(id + " option").prop("selected", false);

		if($(id + ' option:contains("'+val+'")').length > 0){
			$(id + ' option:contains("'+val+'")').prop('selected', true);
		} else {
			$("<option/>").val(val).text(val).appendTo(id);
			$(id + ' option:contains("'+val+'")').prop('selected', true);
		}


//		$(id).trigger("change");

	}

	function __PutTemplateDataRadio(value){

		var name = value.name;
		var val = value.value;

		if($('[name="'+name+'"]').length == 0) return false;

		var id = "#" + $('[name="'+name+'"]').attr("id");

		if($(id).is("input") == false) return false;
		if($(id).attr("type") != "radio") return false;

		$.each($("form.editreport input[name='"+name+"']"), function(key, vale) {

			var tid = "#" + $(this).attr('id');
			$(tid).removeAttr('checked');

			if($(tid).val() == val){
				$(tid).attr('checked','checked');
			}

		});

		$("div.radio:not(.ui-buttonset)").controlgroup("refresh");

		//		$(id).trigger("change");

	}

	function __PutTemplateDataTextarea(value){

		var name = value.name;
		var val = value.value;

		if($('[name="'+name+'"]').length == 0) return false;

		var id = "#" + $('[name="'+name+'"]').attr("id");

		if($(id).is("textarea") == false) return false;

		$('[name="'+name+'"]').val(val);

//		$(id).trigger("change");

	}

	$("select#TemplateDropdownSelect").select2({
		placeholder: {
			id: '0', // the value of the option
			text: 'Select an template'
		},
		allowClear: true,
    templateResult: formatOption
  });

	$("select#TemplateDropdownSelect").on("change", function() {

		json_request_load_animation();

	  var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "reportnumber_id", value: $("#ReportnumberId").val()});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: "templates/view/" + $(this).val(),
	    data	: data,
	    dataType: "json",
	    success: function(data) {
	      PutTemplateData(data);
	    },
	    complete: function(data) {
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
	});

	function FindAttention(){

		var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "find_attention", value: 1});
		data.push({name: "reportnumber_id", value: $("#ReportnumberId").val()});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: "templates/view/" + 0,
	    data	: data,
	    dataType: "json",
	    success: function(data) {
	      PutAttention(data);
	    },
	    complete: function(data) {
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

	FindAttention();

});
</script>
