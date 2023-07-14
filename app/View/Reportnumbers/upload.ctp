<div class="modalarea">
<h2><?php echo __('File upload')?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?> </div>
<?php if(isset($writeprotection) && $writeprotection) echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error')); ?>
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber,3) ?></h2>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
</div>

	<div class="uploadform">
		<?php
		echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
		echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
		echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "image/*"));
		echo $this->element('form_upload_report',array('writeprotection' => $writeprotection));
		?>

		<div class="clear"></div>
<?php
if(isset($data)){

	echo $this->Form->create('Import',array('class' => 'import_data editreport'));
	echo '<fieldset>';
	echo $this->Form->input('ajax_true', array('id' => 'AjaxTrue','name' => 'ajax_true','type' => 'hidden', 'value' => 1));

	$options = array(
    	'1' => __('Import Generally',true),
    	'2' => __('Import Specifice',true),
    	'3' => __('Import Evaluation',true),
	);

	if(!isset($data->ReportGenerally))unset($options[1]);
	if(!isset($data->ReportSpecific))unset($options[2]);
	if(!isset($data->ReportEvaluation))unset($options[3]);

	echo $this->Form->select('Import.field', $options,
		array(
    		'label' => false,
    		'div' => false,
    		'multiple' => 'checkbox',
			'selected' => array(1,2,3)
		)
	);

	echo $this->Html->link(__('Import data',true), 'javascript:', array('id' => 'import_xml_link','class' => 'icon icon_load_infos','title' => __('Import data',true)));
	echo '</fieldset>';
	echo $this->Form->end();

	echo '<div class="current_content_header">';
	if(isset($data->ReportGenerally) && !empty($data->ReportGenerally)) echo '<span class="active" id="current_content_generally">' . __('Order Data',true) . '</span>';
	if(isset($data->ReportSpecific) && !empty($data->ReportSpecific)); echo '<span id="current_content_specific">' . __('Testing Data',true) . '</span>';
	if(isset($data->ReportEvaluation) && !empty($data->ReportEvaluation)); echo '<span id="current_content_evaluation">' . __('Evaluation Data',true) . '</span>';
	echo '</div>';

	if(isset($data->ReportGenerally) && !empty($data->ReportGenerally)){
		echo '<div class="current_content hide_info_div current_content_generally">';
		foreach($data->ReportGenerally->children() as $_key => $_data){
			echo '<dl>';
			echo trim($_data->discription->$locale);
			echo ': <strong>';
			echo trim($_data->value);
			echo '</strong>';
			echo '</dl>';
		}
		echo '</div>';
	}
	if(isset($data->ReportSpecific) && !empty($data->ReportSpecific)){
		echo '<div class="current_content hide_info_div current_content_specific">';
		foreach($data->ReportSpecific->children() as $_key => $_data){
			echo '<dl>';
			echo (trim($_data->discription->$locale));
			echo ': <strong>';
			echo (trim($_data->value));
			echo '</strong>';
			echo '</dl>';

			if($_key == 'heads'){
				foreach($_data->children() as $__key => $__data){
					if(!empty($__data)){
						foreach($__data as $___key => $___data){
							echo '<dl>';
							echo (trim($___data->discription->$locale));
							echo ': <strong>';
							echo (trim($___data->value));
							echo '</strong>';
							echo '</dl>';
						}
					}
				}
			}

		}
		echo '</div>';
	}
	if(isset($data->ReportEvaluation) && !empty($data->ReportEvaluation)){
		echo '<div class="current_content hide_info_div current_content_evaluation">';
		foreach($data->ReportEvaluation->children() as $_key => $_data){
			foreach($_data->children() as $__key => $__data){
				echo '<dl>';
				echo (trim($__data->discription->$locale));
				echo ': <strong>';
				echo (trim($__data->value));
				echo '</strong>';
				echo '</dl>';
			}
			echo '<dl></dl>';
		}
		echo '</div>';
	}
}
?>
	</div>
</div>
</div>
<?php
$url = $this->Html->url(array_merge(array('action' => 'upload'),$this->request->projectvars['VarsArray']));
$urlOkay = $this->Html->url(array_merge(array('action' => 'edit'),$this->request->projectvars['VarsArray']));
?>
<?php  echo $this->JqueryScripte->LeftMenueHeight(); ?>

<script type="text/javascript">
	$(document).ready(function(){

		<?php if(isset($toInsertOkay))echo '$("#container").load("' . $urlOkay . '",{"ajax_true":1})';?>

		$("div.current_content").hide();
		$("div.current_content").first().show();

		$("div.current_content_header span").click(function(){
			$("div.current_content_header span").removeClass("active");
			$(this).addClass("active");
			$("div.current_content").hide();
			$("div." + $(this).attr("id")).show();
		});

		$("a#import_xml_link").click(function(){
			$('.import_data').submit();
			return false;
		});

		$("form.import_data").bind("submit", function() {

				var data = $(this).serializeArray();

				$.ajax({
						type	: "POST",
						cache	: false,
						url		: $(this).attr("action"),
						data	: data,
						success: function(data) {
		    				$("#container").html(data);
		    				$("#container").show();
						}
					});
					return false;
				});

	});
</script>
