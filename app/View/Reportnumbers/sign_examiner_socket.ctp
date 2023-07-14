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
$RedirectUrl = $this->Html->url(array_merge(array('action' => 'sign'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('RedirectUrl',array('type' => 'hidden','value' => $RedirectUrl));

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
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));

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
<div class="flex">
<div class="current_content hide_infos_div item">
<?php
echo '<dl id=""><p>' . __('This report will send to following email(s)') . ':</p>';

if(isset($allmails) && is_array($allmails) && count($allmails) > 0){
	echo '<ul>';

	foreach ($allmails as $key => $value) {

		if(empty($value)) continue;

		echo '<li>' . $value . '</li>';
	}

	echo '</ul>';
}
echo '</dl>';
?>
<dl id="status_ws"><span><?php echo __('Not connected to WSS-Service',true);?></span></dl>
<?php if($SignatoryClosing == false) echo '<dl id="" class="fail"><span>' . __('This report will not be closed automatically after signing.') . '</span></dl>';?>
<?php if($SignatoryClosing == true) echo '<dl id="" class="success"><span>' . __('This report will be closed automatically after signing.') . '</span></dl>';?>
<dl><?php echo __('Pad Type',true);?>: <span id ="PadType_0"></span></dl>
<dl><?php echo __('Serial Number',true);?>: <span id="SerialNumber_0"></span></dl>
<dl><?php echo __('Firmware Version',true);?>: <span id="FirmwareVersion_0"></span></dl>
<dl><?php echo __('RSA Support',true);?>: <span id="RSASupport_0"></span></dl>
<dl><?php echo __('Biometry Cert. ID',true);?>: <span id="biometryCertID_0"></span></dl>
<dl><?php echo __('RSAScheme',true);?>: <span id="RSAScheme_0"></span></dl>
<dl id="mode_ws">
	<select name="PadConnectionTypeListName" id="PadConnectionTypeList">
			<option value="USB" selected>signotec signature USB pad</option>
			<option value="Serial">signotec signature Serial pad</option>
	</select>
</dl>
<dl id="status_ws">
<input class="button get_signature open_pad" id="SignBtn" name="SignBtn" type="button" value="<?php echo __('open sign pad',true);?>" onclick="getSignature();" />

<input class="button delete_signature" id="SignBtnRetry" name="SignBtnRetry" type="button" value="<?php echo __('Clear Signature',true);?>" onclick="" />
<input class="button confirm_signature" id="SignBtnConfirm" name="SignBtnConfirm" type="button" value="<?php echo __('Confirm Signature',true);?>" onclick="" />

<input type="hidden" id="showPaletteOnly" class="showPaletteOnly" value="rgb(0, 0, 255)"/>
<select id="signaturePenColorSelect">
		<option name="signaturePenColorBlue" value="0x00ff0000" selected>blue</option>
</select>
</dl>
</div>
<div class="item current_signs">
<canvas id="sigCanvas" width="320" height="160"></canvas>
<img id="Signature_0" alt="Signature 0" src="signo/White.png" />
</div>
</div>
</div>
<textarea id="log" style="display:none;"></textarea>
<?php
echo $this->element('sign/js_signpad_comunication_test_api');
//echo $this->element('sign/js_signpad_comunication');
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
echo $this->element('js/json_request_animation');
?>
