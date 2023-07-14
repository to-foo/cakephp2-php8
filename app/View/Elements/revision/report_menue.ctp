<script>
	$(function() {

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

		json_request_load_animation = function(){

			$("#JsonSvgLoader").show();

		}

		json_request_stop_animation = function(){

			$("#JsonSvgLoader").hide();

		}

		$(".edit a").tooltip({
			track: true
			});

			$("#text_delete_report").click(function() {
				checkDuplicate = confirm("'.$text_delete_report.'");

				if (checkDuplicate == false) {
					return false;
				}
			});

			$(".edit a.print").click(function() {

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

<?php
foreach($ReportMenue['Menue'] as $_key => $_ReportMenue){

	$CssClass = 'icon ';
	$CssClass .= isset($_ReportMenue['class']) ? $_ReportMenue['class'] : null;

 	echo $this->Html->link($_ReportMenue['title'],
	array_merge(
		array(
			'controller' => $_ReportMenue['controller'],
			'action' => $_ReportMenue['action']
			),
		$_ReportMenue['vars']
		),
		array(
			'class' => $CssClass,
			'title' => isset($_ReportMenue['title']) ? $_ReportMenue['title'] : null,
			'id' => isset($_ReportMenue['id']) ? $_ReportMenue['id'] : null,
			'target' => isset($_ReportMenue['target']) ? $_ReportMenue['target'] : null,
			'disabled' => isset($_ReportMenue['disabled']) ? $_ReportMenue['disabled'] : null,
		)
	);

}

if($data['Reportnumber']['delete'] > 0){
	echo '<div class="clear"></div>';
	return;
}

if(isset($ReportMenue['CloseMethode']['Methode'])){

	$closeMethod = $ReportMenue['CloseMethode']['Methode'];

	switch ($closeMethod) {
		case 'showReportVerification':
			echo $this->Navigation->showReportVerification($data);
		break;

		case 'showReportVerificationByTime':
			echo $this->Navigation->showReportVerificationByTime($data);
		break;

		default:
		echo $this->Navigation->showReportVerification($data);
		break;
	}
}

echo $this->element('templates/select_dropdown');
?>
<div class="clear"></div>
<?php
echo $this->element('js/ajax_link_global',array('name' => '.ajaxreport'));
echo $this->element('js/ajax_modal_link_global',array('name' => '.modalreport'));
?>
