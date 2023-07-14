<?php echo $this->Form->input('AttentionUrl',array('type' => 'hidden','value' => $id));?>
<script type="text/javascript">
$(document).ready(function() {


	function PutAttentionOverview(data){


		$.each(data, function(key, value) {
			$.each(value.Attention, function(key2, value2) {

				let elm = $("table.editable").find("p[data-model='" + value2.model + "'][data-field='" + value2.field + "'][data-id='" + value2.evaluation_id + "'][data-type='editpos']");
				elm.addClass('attention_field');

				if(elm.text() != value2.value){
					if(elm.text() != "Edit" + value2.value){
						elm.removeClass('attention_field');
					}
				}
			});
		});

	}

	function PutAttentionEdit(data){

		$.each(data, function(key, value) {
			$.each(value.Attention, function(key2, value2) {

				let Name = "data["+value2.model+"]["+value2.field+"]";;
				let Val = value2.value;

				if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").hasClass("input") === true){
					if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").val() == Val){
						__PutAttentionInfosInputSelect(Name);
					}
				}

				if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").hasClass("select") === true){
					if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "'] option:selected").text() == Val){
						__PutAttentionInfosInputSelect(Name);
					}
				}

				if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").hasClass("radio") === true){
					if($("input[name='"+Name+"']:checked").val() == Val){
						__PutAttentionInfosRadio(Name);
					}
				}

			});
		});

		$(".attention_field").on("change", function() {

			$(this).removeClass('attention_field');

			if($(this).attr("type") == "radio"){
				let Name = $(this).attr("name");
				$("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").removeClass("attention_field");
				$("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").removeClass("attention_field");
			}

		});

	}

	function __PutAttentionInfosInputSelect(Name){

		$("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").removeClass("attention_field");
		$("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").addClass("attention_field");

	}

	function __PutAttentionInfosRadio(Name){

		$("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").removeClass("attention_field");
		$("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").addClass("attention_field");

	}

	function FindAttention(id){

		var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "find_attention", value: 1});
		data.push({name: "reportnumber_id", value: $("#ReportnumberId").val()});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: "templates/show/" + id,
	    data	: data,
	    dataType: "json",
	    success: function(data) {

	      if(data.overview){PutAttentionOverview(data.overview);}
				if(data.edit){PutAttentionEdit(data.edit);}

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

	FindAttention($("#AttentionUrl").val());

});
</script>
