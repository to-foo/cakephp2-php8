<?php
$attribut_disabled = false;
if($reportnumber['Reportnumber']['status'] > 0) $attribut_disabled = true;
if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;
?>
<div class="quicksearch">
	<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
	<?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers detail">
	<h2><?php echo $this->Pdf->ConstructReportName($reportnumber,3) ?></h2>
	<?php echo $this->element('Flash/_messages');?>
	<?php
	if(isset($writeprotection) && $writeprotection) {
		echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error'));
		echo $this->Html->tag('p', '&nbsp;');
	}
	?>
	<div class="clear edit">
		<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
		<div id="refresh_revision_container"></div>
	</div>
	<div class="revisionsform">

		<?php
		if ($reportnumber['Reportnumber'] ['revision'] > 0   && !empty ($reportnumber['RevisionValues'])) {

			$field = 'all';
			$modelpart = 'Reportimage';

		}
		?>
	</div>
	<div class="uploadform">
		<h3><?php echo __('Imageupload', true);?></h3>
		<?php

		if($attribut_disabled === false){

			echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
			echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
			echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "image/jpeg,image/png"));
			echo $this->element('form_upload_report',array('writeprotection' => $writeprotection));
		}
		?>

		<?php

		echo $this->Form->create('ImageCount', array('class' => 'open_close_order'));
		echo '<fieldset>';
		echo $this->Form->input('imagecount',
		array(
			'label' => __('Wie viele Bilder sollen auf einer Druckseite zusammengefasst werden?',true),
			'options' => array(0 => __('keine Bilder drucken',true),1 => __('1 Bild pro Seite',true),2 => __('2 Bilder pro Zeile',true),4 => __('4 Bilder pro Zeile',true))
		)
	);
	echo '</fieldset>';
	echo $this->Form->end();

	?>
	<div class="clear"></div>
	<?php echo $this->element('image/show_report_images',array('attribut_disabled' => $attribut_disabled));?>
	<span class="clear"></span>
</div>
</div>
</div>
<div class="clear" id="testdiv"></div>
<?php
$url = $this->Html->url(array('controller' => 'reportnumbers', 'action' => 'images',
$reportnumber['Reportnumber']['topproject_id'],
$reportnumber['Reportnumber']['cascade_id'],
$reportnumber['Reportnumber']['order_id'],
$reportnumber['Reportnumber']['report_id'],
$reportnumber['Reportnumber']['id']
)
);
?>
<div id="foo"></div>
<?php
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));

if($attribut_disabled === false) echo $this->element('image/sortable',array('url' => $this->Html->url(array_merge(array('controller' => 'reportnumbers', 'action' => 'imagediscription'),$this->request->projectvars['VarsArray']))));
if($attribut_disabled === false) echo $this->element('js/form_upload_report',array('container' => '#container','url' => $url,'FileLabel' => __('Select files', true),'Extension' => 'pdf'));
echo $this->element('image/contextmenue',array('name' => 'div.images a.image_function'));
echo $this->element('image/fancybox');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/images_print_checkbox',array('attribut_disabled' => $attribut_disabled));
echo $this->element('js/images_print_imagecount');
echo $this->element('js/images_disable',array('attribut_disabled' => $attribut_disabled));
echo $this->element('js/tooltip_revision');

if(Configure::read('RefreshReport') == true && $reportnumber['Reportnumber']['status'] == 0) echo $this->element('refresh_report');
if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) echo $this->element('refresh_revision');
?>
