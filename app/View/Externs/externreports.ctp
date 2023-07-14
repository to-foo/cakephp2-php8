<?php
if($message !=  null){ 
	echo '<div class="message_wrapper"><div class="message">';
	echo $message;
	echo '</div></div>'; 
	return;
}
?>
<div class="extern_reports" id="tabs">
	<div class="header">
    	<div class="header_inner">
MBQ-Qualitätssicherungs-GmbH • Mittelstraße 14a • 06333 Hettstedt-Walbeck • Tel. 0 34 76 | 55 43 56 • Fax. 0 34 76 | 55 43 57 • <a href="mailto:info@mbq-gmbh.de">info@mbq-gmbh.de</a>
	        <div class="impressum"><?php if($impressum != null) echo $impressum;?></div>
        </div>
    </div>
  <ul>
  <?php
	foreach($ReportHeadline as $_key => $_ReportHeadline){
		echo '<li><a href="#' . $_key . '">' . $_ReportHeadline . '</a></li>';
	}
	?>
  </ul>
<?php
foreach($ReportData as $_key => $_ReportData){
	if($_key == 'Evaluations') continue;
	echo '<div id="' .  $_key . '">';
	echo '<dl class="report">';
	foreach($_ReportData as $__key => $__ReportData){
		echo '<dd>' . $__ReportData['value'] . '</dd>';
		echo '<dt>' . $__ReportData['discription'] . '</dt>';
	}
	echo '</dl>';
	echo '</div>';
}

echo '<div id="Evaluation">';
	foreach($ReportData['Evaluations']['data'] as $_key => $_data){ 
	echo '<dl class="report">';
		
		echo '<dl class="discription"><span>' . $ReportData['Evaluations']['headline']['description']['discription'] . ' ' . $_data['discription'] . '</span>';
		
		foreach($_data['weld'] as $__key => $__data){
			foreach($__data as $___key => $___data){
				
				
				if(isset($ReportData['Evaluations']['headline'][$___key])){

				if($___key == 'description') continue;

				echo '<dd>';			
				echo $___data;
				echo '</dd>';			
				echo '<dt>';			
				echo $ReportData['Evaluations']['headline'][$___key]['discription'];
				echo '</dt>';			
				}
			}
		}
		
		echo '</dl>';
		
	echo '</dl>';
	}
echo '</div>';
?>
</div>

<script type="text/javascript">
$(document).ready(function(){

	$("#tabs").tabs();

/*	
	$("dl.report").hide();
	$("dl.Info").show();
	$("ul.extern_menue li.Infos").addClass("active");
*/
});
</script>
