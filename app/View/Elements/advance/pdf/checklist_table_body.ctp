<?php
$headlabel = __('Maßnahmen');
echo $this->element('advance/pdf/checklist_table_header',array('tcpdf' => $tcpdf,'headlabel'=>$headlabel));

foreach ($Advance['AdvancesType']['non_ndt'] as $key => $value) {

	if(isset($tcpdf->Advance['Scheme']['CollectMesurePoints'][$value])) echo $this->element('advance/pdf/checklist_table_body_cell',array('key' => $value, 'tcpdf' => $tcpdf));
	if(!isset($tcpdf->Advance['Scheme']['CollectMesurePoints'][$value])) echo $this->element('advance/pdf/checklist_table_body_cell_empty',array('key' => $value, 'tcpdf' => $tcpdf));;

}

$headlabel = __('Verfahren');
echo $this->element('advance/pdf/checklist_table_header',array('tcpdf' => $tcpdf,'headlabel'=>$headlabel));

foreach ($Advance['AdvancesType']['ndt'] as $key => $value) {

	if(isset($tcpdf->Advance['Scheme']['CollectMesurePoints'][$value])) echo $this->element('advance/pdf/checklist_table_body_cell',array('key' => $value, 'tcpdf' => $tcpdf));
	if(!isset($tcpdf->Advance['Scheme']['CollectMesurePoints'][$value])) echo $this->element('advance/pdf/checklist_table_body_cell_empty',array('key' => $value, 'tcpdf' => $tcpdf));;

}

$Testings = array_merge($Advance['AdvancesType']['ndt'],$Advance['AdvancesType']['non_ndt']);
echo $this->element('advance/pdf/checklist_table_header_repair',array('tcpdf' => $tcpdf));

foreach ($Testings as $key => $value) {

	if(isset($tcpdf->Advance['Scheme']['CollectMesurePoints'][$value])) echo $this->element('advance/pdf/checklist_table_body_cell_repair',array('key' => $value, 'tcpdf' => $tcpdf));

}

?>
