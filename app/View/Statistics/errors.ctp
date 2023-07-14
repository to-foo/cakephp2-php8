<?php
$weldErrors = array();
$total = 0;

if(isset($errors['FF'])) unset($errors['FF']);

foreach(
	Hash::extract(
		array_map(
			function($report) {
				if(!isset($report['Evaluation'])) return $report;

				$report['Evaluation'] = array_filter(
					$report['Evaluation'],
					function($eval) {
						return $eval['result'] != '';
					}
				);

				return $report;
			},
			$data['extra']['reports']
		),
		'{n}.Evaluation.{n}.error_array.{n}'
	)
 as $error) {
	if(!isset($errors[trim($error)])) continue;

	@$weldErrors[$errors[trim($error)]]['text'] = $errors[trim($error)];
	@$weldErrors[$errors[trim($error)]]['count']++;
	$total++;
}

$keys = array_keys($weldErrors);
natsort($keys);
$weldErrors = array_merge(array_flip($keys), $weldErrors);

?>
<div id="graph" class="error_graph"></div>
<!--
<table border="0" cellspacing="0" cellpadding="0" class="error_table">
-->
<?php
/*
$lines = 0;
echo $this->Html->tableHeaders(array(__('Error number', true), __('Amount',  true)));
foreach($weldErrors as $number=>$error) {
	echo '<tr>';
	echo '<td>'.$error['text'].'</td><td>'.$error['count'].'</td>';
	if($lines++ == 0) {
	}
	echo '</tr>';
}
*/
$data = array(
	'extra' => array(
		'width' => $data['extra']['width'] * 2 / 3 ,
		'height' => $data['extra']['height'],
	),
	'type' => 'welderrors',
	'ajax_true' => 1
);
//$data['extra']['reports'] = Hash::extract($data['extra']['reports'], '{n}.Reportnumber.id');
?>
<!--
</table>
-->
<script type="text/javascript">
	$.ajax({
		url: "<?php echo $this->Html->url(array_merge(array('action'=>'diagram'), $VarsArray)); ?>",
		type: "POST",
		data: <?php echo json_encode($data); ?>,
		success: function(data) {
			$(document).ready(function(e) {
				$("#graph").html(data);
			});
		}
	});
</script>
<?php

