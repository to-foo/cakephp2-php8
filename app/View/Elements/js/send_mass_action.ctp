<script type="text/javascript">
$(document).ready(function(){

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

	$("#send_mass_action").click(function() {

		var data = $("#tmpForm").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});

		if($("#MassSelectType").val() == "weldlabel") {

			json_request_load_animation();

			data.push({name: "showpdf", value: 1});

			$.ajax({
		    type	: "POST",
		    cache	: false,
				url		: $("#tmpForm").attr("action"),
		    data	: data,
		    dataType: "json",
		    success: function(data) {
		      EmbedPDF(data);
		    },
		    complete: function(data) {
		      json_request_stop_animation();
					$("#dialog").dialog("close");
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

		} else {

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $("#tmpForm").attr("action"),
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

		}

		return false;

	});
});
</script>
