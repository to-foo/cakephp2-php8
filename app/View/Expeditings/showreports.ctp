<div class="modalarea detail">
<h2><?php echo __('Testing Reports'); ?> </h2>
<?php echo $this->element('Flash/_messages');?>

<?php
		if(count($reportnumbers) == 0){
			echo '<div class="error">';
			echo __('No testing reports found', true);
			echo '</div>';
		}
	if(count($reportnumbers) == 0) return;

echo '<table class="table_resizable table_infinite_sroll"><tr>';
echo '<th>' . __('Number',true) . '</th>';
echo '<th></th>';
echo '<th>' . __('created',true) . '</th>';
echo '</tr>';
?>

<?php foreach ($reportnumbers as $key => $value) {

echo '<tr>';
echo '<td>';
$class = 'round showpdflink';
$action = 'pdf';
if($value['Reportnumber']['status'] == 0){

	echo $this->Html->link(h($this->Pdf->ConstructReportName($value, 2)),array(
						'controller' => 'reportnumbers',
						'action' => $action,
						$value['Reportnumber']['topproject_id'],
						$value['Reportnumber']['cascade_id'],
						$value['Reportnumber']['order_id'],
						$value['Reportnumber']['report_id'],
						$value['Reportnumber']['id'],0,0,0,3
					),
					array(
						'class' => $class,
					)
				);

} else {

	echo $this->Html->link(h($this->Pdf->ConstructReportName($value, 2)),array(
						'controller' => 'reportnumbers',
						'action' => $action,
						$value['Reportnumber']['topproject_id'],
						$value['Reportnumber']['cascade_id'],
						$value['Reportnumber']['order_id'],
						$value['Reportnumber']['report_id'],
						$value['Reportnumber']['id'],0,0,0,0
					),
					array(
						'class' => $class,
					)
				);

}
echo '</td>';

echo '<td>';
echo $value['Testingmethod']['verfahren'];
echo '</td>';

echo '<td>';
echo $value['Reportnumber']['created'];
echo '</td>';

echo '</tr>';



}?>
<script>
$(document).ready(function() {

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
			$(".ui-dialog").show();

		});
	}

	$("a.showpdflink").click(function() {

//	  json_request_load_animation();
		$(".ui-dialog").hide();

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
//	      json_request_stop_animation();
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
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
?>
