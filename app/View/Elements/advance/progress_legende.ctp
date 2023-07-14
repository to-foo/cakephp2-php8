<?php
echo '<div class="progress_legend">';
echo '<div class="future tooltip" title="' . __('Geplant',true) . '"></div>';
echo '<div class="plan tooltip" title="' . __('Im Plan',true) . '"></div>';
echo '<div class="finished tooltip" title="' . __('Abgeschlossen',true) . '"></div>';
echo '<div class="delayed tooltip" title="' . __('Verspätet innerhalb Karenztage',true) . '"></div>';
echo '<div class="critical tooltip" title="' . __('Kritisch außerhalb Karenztage oder Reparatur',true) . '"></div>';
echo '</div>';

if(!isset($this->request->data['AdvancesType'])) return;

echo '<div class="progress_legend">';

foreach($this->request->data['AdvancesType'] as $key => $value){
	echo '<div class="future tooltip" title="' . $value['AdvancesType']['description'] . '">';
	echo '<span>';
	echo $value['AdvancesType']['type'];
	echo '</span>';
	echo '</div>';
}

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	$("div.progress_legend div.tooltip").tooltip();
});
</script>
