
<div class="clear"></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div id="sign_container">
<div class="current_content hide_infos_div">

<dl id="status_ws"><span><?php echo __('Not connected to WSS-Service',true);?></span></dl>
<dl id="Status_pad_0"><span><?php echo __('Sign pad not conected',true);?></span></dl>
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
echo $this->element('sign/js_signpad_comunication');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
