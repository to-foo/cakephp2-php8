<div class="error_occurred">
<div class="errormessage">
<?php $timeout = 2000;?>
<h2><?php echo __('Entschuldigung', true);?></h2>
<p>
<?php echo __('Es ist ein Fehler aufgetreten, die Fehlermeldung wurde in den Log-Dateien dokumentiert.');?>
<br />
<?php echo __('Benutzen Sie den unten stehenden Link um zur Startseite der Anwendung zurückzukehren');?>
<br /><br />
<?php echo '<a href="' . $this->webroot . '" class="round_red">Start</a>';?>
</p>
</div>
</div>
<?php die();?> 