<div id="sign_container">
<div class="current_content hide_infos_div item">

<?php if(isset($allmails)) echo '<dl id=""><span>' . __('This report will send to following email(s)') . ': ' . $allmails . '</span></dl>';?>

<dl id="status_ws"><span><?php echo __('Not connected to WSS-Service',true);?></span></dl>
<dl id="Status_pad_0"><span><?php echo __('Sign pad not conected',true);?></span></dl>
<?php if($SignatoryClosing == false) echo '<dl id="" class="fail"><span>' . __('This report will not be closed automatically after signing.') . '</span></dl>';?>
<?php if($SignatoryClosing == true) echo '<dl id="" class="success"><span>' . __('This report will be closed automatically after signing.') . '</span></dl>';?>
<dl id="PadType_0"><?php echo __('Pad Type',true);?>: <span></span></dl>
<dl id="SerialNumber_0"><?php echo __('Serial Number',true);?>: <span></span></dl>
<dl id="FirmwareVersion_0"><?php echo __('Firmware Version',true);?>: <span></span></dl>
<dl id="status_ws">
<?php
echo $this->Form->create('Reportnumber', array('class' => 'login'));

echo $this->Form->input('MassSelectetIDs',array('type' => 'hidden'));
echo $this->Form->input('MassSelect',array('type' => 'hidden'));
echo $this->Form->input('action',array('type' => 'hidden'));

echo '<input class="button get_signature open_pad" id="SignBtn" name="SignBtn" type="button" value="' . __('open sign pad',true) . '" onclick="" />';
echo '<input class="button delete_signature" id="SignBtnRetry" name="SignBtnRetry" type="button" value="' . __('Clear Signature',true) . '" onclick="" />';
echo '<input class="button confirm_signature" id="SignBtnConfirm" name="SignBtnConfirm" type="button" value="' . __('Confirm Signature',true) . '" onclick="" />';
echo '<input type="text" id="showPaletteOnly" class="showPaletteOnly" value="rgb(0, 0, 255)"/>';
echo $this->Form->end();
?>
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
echo $this->element('reports/js/signpad_comunication');
?>
