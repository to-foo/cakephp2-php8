<script type="text/javascript">
$(document).ready(function() {


	// soll später durch das Element ersetzt werden
	json_request_load_animation = function(){

		$("#JsonSvgLoader").show();

	}

	json_request_stop_animation = function(){

		$("#JsonSvgLoader").hide();

	}

	function EmbedPDF(data){

	  let string = "data:application/pdf;base64," + data.string;

		$(".ui-dialog").hide();
		$("div#wrapper_pdf_container").show();
	  $("div#show_pdf_contaniner").show();
	  $("div#show_pdf_container_navi").show();

		PDFObject.embed(string, "div#show_pdf_contaniner",{suppressConsole: true});

		var url = $("div.breadcrumbs").find("a:last").attr("href");

		var data = new Array();
		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
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

	  $("a#show_pdf_contaniner_button").click(function() {

			$("#dialog").dialog().dialog("close");
			$("#dialog").empty();
			$("#dialog").css("overflow","inherit");
			$("#dialog").dialog("destroy");

			$("div#wrapper_pdf_container").hide();
	    $("div#show_pdf_contaniner").hide();
	    $("div#show_pdf_container_navi").hide();

	  });

	}

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

});
</script>
