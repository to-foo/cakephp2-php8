<div class="modalarea detail">
<h2><?php echo __('Custom weld label'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php $label = $settings['settings']->$ReportPdf->settings->QM_WELDLABEL;?>

<?php
echo $this->Form->create('Reportnumber',
  array(
    'type' => 'post',
    'class' => 'login',
    'url'=>array_merge(array('controller'=>'Reportnumbers','action'=>'printweldlabel'),  $VarsArray)
  )
);

echo '<fieldset>';

echo $this->Form->input('custom_form', array('type'=>'hidden', 'value'=>1));

foreach($label->items as $item) {

  $caption = '';

	if(!empty($settings['settings']->{$item->model}->{$item->item}->pdf)) $caption = $settings['settings']->{$item->model}->{$item->item}->pdf->description;

	if(trim($item->caption) != '') $caption = trim($item->caption);

	$caption = preg_replace('/[: ]+$/', '', $caption);

	$class = '';

	if(isset($item->type) && trim($item->type) != '') $class = trim($item->type);

	echo $this->Form->input($item->model.'.'.$item->item, array('type'=>'text', 'label'=>$caption, 'class'=>$class));
}
echo '</fieldset>';

echo $this->Form->end(__('Print weld label'));
?>
</div>

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

  $("form#ReportnumberPrintweldlabelForm").bind("submit", function() {

    json_request_load_animation();

		var data = $(this).serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
    data.push({name: "showpdf", value: 1});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $(this).attr("action"),
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

		return false;

	});
});
</script>
