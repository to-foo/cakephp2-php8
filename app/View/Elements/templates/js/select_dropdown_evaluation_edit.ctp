<script type="text/javascript">
$(document).ready(function() {

	function PutAttention(data){


		$.each(data.AttentionInfo, function(key, value) {

			let Name = value.name;
			let Val = value.value;

			console.log(Name);

			if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").hasClass("input") === true){
	//			if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").val() == Val){
					__PutAttentionInfosInputSelect(Name);
	//			}
			}

			if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").hasClass("select") === true){
	//			if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "'] option:selected").text() == Val){
					__PutAttentionInfosInputSelect(Name);
	//			}
			}

			if($("form#ReportnumberEditevalutionForm").find("[name='" + Name + "']").closest("div").hasClass("radio") === true){
	//			if($("input[name='"+Name+"']:checked").val() == Val){
					__PutAttentionInfosRadio(Name);
	//			}
			}

		});

		$(".attention_field").on("change", function() {
			console.log($(this).attr("type"));
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

	function FindAttention(){

		var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "find_attention", value: 1});
		data.push({name: "evaluation_id", value: $("#CurrentEditEvaluationId").val()});

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
