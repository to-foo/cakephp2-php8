<?php  CakeLog::write('dump', $this->fetch('script')); ?>
<div id="message_wrapper_dialog"><?php CakeLog::write('dump',  $this->Session->flash()); ?></div>
<?php CakeLog::write('dump', $this->Navigation->sysLinksModal(isset($SettingsArray) ? $SettingsArray : array()));?>
<?php CakeLog::write('dump',  $this->fetch('content'));?>

