<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($afterEdit)) echo $afterEdit;
if(isset($time)) echo $this->JqueryScripte->DialogClose(intval($time));
else echo $this->JqueryScripte->DialogClose();
