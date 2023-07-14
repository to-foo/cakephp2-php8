<?php
//echo $this->fetch('script');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
<?php if(isset($SettingsArray)) echo $this->Navigation->sysLinksModal($SettingsArray);?>
<?php echo $this->fetch('content');?>
<div id="footer" class="clear"></div>
