<?php 
if(!isset($Reportnumber['Reportnumber']['revision_refresh'])) return;
echo '<div class="revision_time"><span class="' . $Reportnumber['Reportnumber']['revision_refresh']['style_color'] . '">';
echo __('Diese Revision wird automatisch geschlossen in:',true) . ' ';
echo $Reportnumber['Reportnumber']['revision_refresh']['time_to_show'];
echo '</span></div>';

if(!isset($lastURL)) $lastURL = null;
?>

<?php
if($Reportnumber['Reportnumber']['revision_refresh']['close'] == true){
	echo'<script type="text/javascript">';
	echo 'window.clearTimeout(refreshTimer);';
	echo '$("#container").load("' . $lastURL . '", {"ajax_true": 1});';
	echo '</script>';
}

?>
