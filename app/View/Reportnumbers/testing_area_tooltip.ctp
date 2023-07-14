<div class="tooltip detail">
<h3><?php echo __('Testing areas') . ' ' . $this->Pdf->ConstructReportName($reportnumber)?></h3>
<div class="current_content">
<?php
foreach($welds as $_key => $_welds){
	if($_welds['discription'] == '') continue;
	if(is_array($_welds['weld'])){
		foreach($_welds['weld'] as $__key => $__welds){
			$output = null;
                        
			if(isset($_welds['discription'])) $output .= $_welds['discription'];
			//if(isset($__welds['position'])) $output .= '/' . $__welds['position'];
			if(isset($__welds['dimension'])) $output .= ' - ' . $__welds['dimension'];
			if(!empty($settings->$ReportEvaluation->result->radiooption->value[intval($__welds['result'])])) $output .= ' - ' . trim($settings->$ReportEvaluation->result->radiooption->value[intval($__welds['result'])]);
                       if ($__welds['result'] == 2) { break;}
                        
		}
		
		echo '<dl>';
		echo $output;
		echo '</dl>';
	}
}
?>
</div>
</div>
