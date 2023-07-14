<h4><?php echo __("Certificats dates and tasks",true); ?></h4>
<?php
foreach ($summary as $key => $value) {
	foreach ($value as $_key => $_value) {
		if(isset($_value['summary']['errors'])) echo $this->element('examiner/qualifications_errors_landingpage',array('summary' => $_value['summary']['errors'],'class' => 'error'));
		if(isset($_value['summary']['future'])) echo $this->element('examiner/qualifications_errors_landingpage',array('summary' => $_value['summary']['future'],'class' => 'future'));
	}
}

//echo $this->element('js/ajax_link');
?>
