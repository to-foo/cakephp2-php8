<div class="modalarea">
<h2><?php echo __('Show QR-Code');?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
App::import('Vendor','qrcode');
// neues Objekt anlegen
if(isset($settings->$ReportPdf->settings->QM_QRCODE_SINGLE->show) && $settings->$ReportPdf->settings->QM_QRCODE_SINGLE->show == 1){
	$action = 'view';
	$direct_link_discription = __('Direct link to this report');
	$term = implode('/',$this->request->projectvars['VarsArray']);

	$QRurl = Configure::read('QrCodeWeldlabelAdresse') . $key;
}

echo $this->Navigation->makeLink(
							'reportnumbers',
							'printqrcode',
							__('Print this QR-Code'),
							'showpdflink round',
							null,
							$this->request->projectvars['VarsArray']
						);

?>

</div>
<?php
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
