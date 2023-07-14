<?php
if($_reportimages['Reportimage']['print'] == 0){
	$image_checkbox_title = __('Um dieses Bild in die PDF-Ausgabe aufzunehmen, aktivieren Sie bitte das Drucksymbol oben links im Bild.',true);
}
if($_reportimages['Reportimage']['print'] == 1){
	$image_checkbox_title = __('Um dieses Bild aus der PDF-Ausgabe zu entfernen, deaktivieren Sie bitte das Drucksymbol oben links im Bild.',true);
}


echo $this->Html->link('',
'javascript:',
array(
	'class' => '',
	'style' => 'background-image:url('.$_reportimages['Reportimage']['imagedata'].')',
	'title' => $image_checkbox_title,
	'escape' => false
	)
);

?>
