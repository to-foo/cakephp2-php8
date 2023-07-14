<script type="text/javascript">

$(document).ready(function(){

	check_form_elms = function(id){

		$(id + " .require").change(function() {

			var ThisId = "#" + $(this).attr("id");
			var ThisIdMess = ThisId + "Message";

			if($(this).val().length > 0){
				$(this).removeClass("error");
				$(ThisIdMess).remove();
			}

		});


		$("#CascadeOrders0, #CascadeOrders1, #CascadeOrders2").change(function(){
			if($(this).val() == 2){
				$("#CascadeGroupCascadeGroup").prop("disabled",false);
				$("form#CascadeAddForm").find("input[name='data[Cascade][Expediting]']").prop("disabled",false);
				$("form#CascadeAddForm").find("input[name='data[Cascade][Advance]']").prop("disabled",false);
				$("div.radio:not(.ui-buttonset)").controlgroup("refresh");
			} else {
				$("#CascadeGroupCascadeGroup").prop("disabled",true);
				$("#CascadeGroupCascadeGroup").val("");
				$("form#CascadeAddForm").find("input[name='data[Cascade][Expediting]']").prop('checked',false);
				$("form#CascadeAddForm").find("input[name='data[Cascade][Advance]']").prop('checked',false);
				$("#CascadeExpediting0").prop('checked',true);
				$("#CascadeAdvance0").prop('checked',true);
				$("form#CascadeAddForm").find("input[name='data[Cascade][Expediting]']").prop("disabled",true);
				$("form#CascadeAddForm").find("input[name='data[Cascade][Advance]']").prop("disabled",true);
				$("div.radio:not(.ui-buttonset)").controlgroup("refresh");
			}
		});
	}

	json_request_cascade = function(data){

		if(data.error) json_request_cascade_error(data);
		if(data.success) json_request_cascade_success(data);

	}

	json_request_cascade_success = function(data){

		$("#flash_error").remove();
		$("#flash_success").remove();

		$(data.success).each(function(key,val){

				var id = "#" + val.id;
				var message = '<div id="flash_success" class="message_info"><span id="' + val.id + 'Message" class="success">' + val.message + '</span></div>';
				var messageinner = '<span id="' + val.id + 'Message" class="success">' + val.message + '</span>';

				if($("#flash_success").length == 0){
					$(message).insertAfter("div#dialog h2");
				} else {
					$(id + "Message").remove();
					$("#flash_success").append(message);
				}

		});

		if(data.url){
			if(data.url.modal){
				setTimeout(load_modal_after_saving(data.url),10000);
			} else {
				setTimeout(load_ajax_after_saving(data.url),10000);
			}
		}
	}

	load_modal_after_saving = function(url){

		$("#AjaxSvgLoader").show();

		var postdata = new Array();
		postdata.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url.modal.url,
			data	: postdata,
			success: function(data) {

				$("#dialog").html(data);
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
/*
			$.ajax({
				type	: "POST",
				cache	: false,
				url		: url.ajax.url,
				data	: postdata,
				success: function(data) {

					$("#container").html(data);
					$("#container").show();

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
*/
	}

	load_ajax_after_saving = function(url){

		$("#AjaxSvgLoader").show();

		var postdata = new Array();
		postdata.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url.ajax.url,
			data	: postdata,
			success: function(data) {

				$("#container").html(data);
				$("#container").show();

				$("#AjaxSvgLoader").hide();

				$("#dialog").dialog().dialog("close");
				$("#dialog").empty();
				$("#dialog").css("overflow","inherit");
				$("#dialog").dialog("destroy");

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

	json_request_cascade_error = function(data){

		$("#flash_success").remove();

		var x = 0;

		$(data.error).each(function(key,val){

				var id = "#" + val.id;
				var message = '<div id="flash_error" class="message_info"><span id="' + val.id + 'Message" class="error">' + val.message + '</span></div>';
				var messageinner = '<span id="' + val.id + 'Message" class="error">' + val.message + '</span>';

				$(id).addClass("error");

				if($("#flash_error").length == 0){
					$(message).insertAfter("div#dialog h2");
				} else {
					$(id + "Message").remove();
					$("#flash_error").append(message);
				}

		});

	}

	$("#<?php echo $name;?>").bind("submit", function() {

		var data = $(this).serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "save_result", value: 1});

		$.ajax({
	    type	: "POST",
	    cache	: false,
			url		: $(this).attr("action"),
	    data	: data,
	    dataType: "json",
	    success: function(data) {
				json_request_cascade(data);
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

	check_form_elms("#<?php echo $name;?>");

});
</script>
