<?php
if(Configure::check('ExaminerManagerTableMonitoring') == false) return;
if(Configure::read('ExaminerManagerTableMonitoring') == false) return;
?>
<td>
<span class="discription_mobil">
<?php echo __('Monitorings'); ?>:
</span>
<span class="summary_span">
<?php

if(isset($monitorings[$_examiner['Examiner']['id']]['summary']['monitoring'])){

$thissummarymon = array();

foreach($monitorings[$_examiner['Examiner']['id']]['summary']['monitoring'] as $_mkey => $_mqualifications){

												//$thissummarymonotpring = null;
$thissummarymon = $this->Quality->MonitoringSummarySingle($_mkey,$_mqualifications);
												//pr($_mqualifications);
											 // pr($thissummary);
echo '<div class="container_monsummary_single container_summarymon_single_'.$_mqualifications[key($_mqualifications)][$_examiner['Examiner']['id']]['info']['examiner_monitoring_id']. '">';
echo $thissummarymon;
echo '</div>';

$thissummarymon = null;
//pr($_mqualifications);
$this_mlink = $this->request->projectvars['VarsArray'];
$this_mlink[15] = $_examiner['Examiner']['id'];
$this_mlink[16] = $_mqualifications[key($_mqualifications)][$_examiner['Examiner']['id']]['info']['examiner_monitoring_id'];
$this_mlink[17] = $_mqualifications[key($_mqualifications)][$_examiner['Examiner']['id']]['info']['id'];

echo $this->Html->link($_mkey,array_merge(array('action' => 'monitorings'),$this_mlink),array('title' => $_mkey,'rev'=> $_mkey,'rel'=>$_mqualifications[key($_mqualifications)][$_examiner['Examiner']['id']]['info']['examiner_monitoring_id'], 'class' => 'summarymon_tooltip ajax icon monitoring_'.$_mkey));

}
}
?>
</span>
</td>
