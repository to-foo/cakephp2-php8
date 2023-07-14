<div class="error_occurred">
<div class="errormessage">
<?php $timeout = 2000;?>
<h2><?php echo __('Entschuldigung', true);?></h2>
<p>
<?php echo __('Es konnte keine Verbindung zum Datenbankserver aufgebaut werden, die Fehlermeldung wurde in den Log-Dateien dokumentiert.');?>
<br />
<?php echo __('Sie können versuchen, die Verbindung erneut aufzubauen');?>
<br /><br />
<?php echo '<a href="' . $this->webroot . '" class="round_red">Aktualisieren</a>';?>
</p>
</div>
</div>
<?php die();?>  