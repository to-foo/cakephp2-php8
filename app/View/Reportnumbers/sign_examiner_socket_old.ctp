<div class="quicksearch">
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
</div>
<div class="reportnumbers detail">
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber) ?></h2>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>

</div>
<h3><?php echo __('Signatur');?> <?php echo $sign_for;?></h3>

<?php
if(Configure::check('FieldsSignForm')&& Configure::read('FieldsSignForm')== true){

	echo $this->Form->create('Reportnumber', array('class' => 'editreport'));
	echo '<div class="pagin_links pagin_links_top">';
	echo '<div class="clear"></div>';
	echo '</div>';

	echo $this->Form->input('id');
	echo $this->Form->input('rel_menue',array('value' => isset($rel_menue) ? $rel_menue : null,'type' => 'hidden'));
	//echo $this->ViewData->EditGeneralyDataSign($reportnumber,$generaloutput,$locale,'General',$signtype);
	echo $this->element('form/reportformsign',array('data' => $reportnumber,'setting' => $generaloutput,'lang' => $locale,'step' => 'General',$signtype));
	echo $this->element('fast_save_report_js');
	echo $this->element('onchange_js');

	echo '<div class="buttons">';

	echo $this->Form->end();
echo '</div>';
}
?>

<?php
//pr($reportnumber['Reportnumber']);
if($reportnumber['Reportnumber']['print'] > 0 && $SignatoryAfterPrinting == false) {
	echo $this->Html->tag('p', __('You can not sign the report after the first printing.'), array('class'=>'error'));
	echo '</div>';
	echo $this->element('js/ajax_breadcrumb_link');
	echo $this->element('js/ajax_link');
	echo $this->element('js/ajax_modal_link');
	echo $this->element('js/revisions_link');
	echo $this->element('js/form_button_set');
	echo $this->element('js/form_multiple_fields');
	echo $this->element('js/custom_dropdown');
	echo $this->element('js/edit_report_tab_menue');
	echo $this->element('js/edit_report_specialcharacter');
	echo $this->element('modul_child_data');
	echo $this->element('fast_save_report_js');
	echo $this->element('js/dropdown_depentency');
	return false;
}

if(isset($errors) && count($errors) >  0){
	echo $this->Html->tag('p', __('You can not sign the report until all required fields have been completed.'), array('class'=>'error'));
	echo '</div>';
	echo $this->element('js/ajax_breadcrumb_link');
	echo $this->element('js/ajax_link');
	echo $this->element('js/ajax_modal_link');
	echo $this->element('js/revisions_link');
	echo $this->element('js/form_button_set');
	echo $this->element('js/form_multiple_fields');
	echo $this->element('js/custom_dropdown');
	echo $this->element('js/edit_report_tab_menue');
	echo $this->element('js/edit_report_specialcharacter');
	echo $this->element('modul_child_data');
	echo $this->element('fast_save_report_js');
	echo $this->element('js/dropdown_depentency');
	return false;
}
?>
<div class="clear"></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($stop_sign) && $stop_sign === true){
	echo '</div>';
	echo $this->element('js/ajax_breadcrumb_link');
	echo $this->element('js/ajax_link');
	echo $this->element('js/ajax_modal_link');
	echo $this->element('js/revisions_link');
	echo $this->element('js/form_button_set');
	echo $this->element('js/form_multiple_fields');
	echo $this->element('js/custom_dropdown');
	echo $this->element('js/edit_report_tab_menue');
	echo $this->element('js/edit_report_specialcharacter');
	echo $this->element('modul_child_data');
	echo $this->element('fast_save_report_js');
	echo $this->element('js/dropdown_depentency');
	return;
}
?>
<div id="sign_container">
<div class="current_content hide_infos_div">

<?php if(isset($allmails)) echo '<dl id=""><span>' . __('This report will send to following email(s)') . ': ' . $allmails . '</span></dl>';?>

<dl id="status_ws"><span><?php echo __('Not connected to WSS-Service',true);?></span></dl>
<dl id="Status_pad_0"><span><?php echo __('Sign pad not conected',true);?></span></dl>
<?php if($SignatoryClosing == false) echo '<dl id="" class="fail"><span>' . __('This report will not be closed automatically after signing.') . '</span></dl>';?>
<?php if($SignatoryClosing == true) echo '<dl id="" class="success"><span>' . __('This report will be closed automatically after signing.') . '</span></dl>';?>
<dl id="PadType_0"><?php echo __('Pad Type',true);?>: <span></span></dl>
<dl id="SerialNumber_0"><?php echo __('Serial Number',true);?>: <span></span></dl>
<dl id="FirmwareVersion_0"><?php echo __('Firmware Version',true);?>: <span></span></dl>
<dl id="status_ws">
<input class="button get_signature open_pad" id="SignBtn" name="SignBtn" type="button" value="<?php echo __('open sign pad',true);?>" onclick="" />
<input class="button delete_signature" id="SignBtnRetry" name="SignBtnRetry" type="button" value="<?php echo __('Clear Signature',true);?>" onclick="" />
<input class="button confirm_signature" id="SignBtnConfirm" name="SignBtnConfirm" type="button" value="<?php echo __('Confirm Signature',true);?>" onclick="" />
<input type="text" id="showPaletteOnly" class="showPaletteOnly" value="rgb(0, 0, 255)"/>
</dl>
<dl>
</dl>
<ul id="logXX"></ul>
</div>
<canvas id="sigCanvas" ></canvas>

<img id="Signature_0" alt="Signature 0" style="display:none" src="signo/White.png"/>

<textarea style="display:none;" id="SignData_0" rows="3" cols="40" readonly="readonly" style="border-style:hidden; resize:none;"></textarea>
</div>
<?php
//echo $this->element('sign/js_signpad_comunication_test_api');
echo $this->element('sign/js_signpad_comunication');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/revisions_link');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/custom_dropdown');
echo $this->element('js/edit_report_tab_menue');
echo $this->element('js/edit_report_specialcharacter');
echo $this->element('modul_child_data');
echo $this->element('fast_save_report_js');
echo $this->element('js/dropdown_depentency');
?>
