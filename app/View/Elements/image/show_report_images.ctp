<?php if(count($reportimages) == 0)  return;?>

<div class="images">
<ul>

<?php
foreach($reportimages as $_reportimages){
	if(($_reportimages['Reportimage']['file_exists']) == false) continue;
	echo $this->element('image/show_report_image',array('_reportimages' => $_reportimages,'attribut_disabled' => $attribut_disabled));
}
?>
</ul>
