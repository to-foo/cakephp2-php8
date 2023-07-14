<?php
$Lang = Configure::read('Config.language');
$Dateformat = Configure::read('Dateformat');

$Language = "en";
$Date = "Y-m-d";
$Datetime = "Y-m-d H:i";
$Time = "H:i:s";

if($Lang == "deu") $Language = "de";
if($Lang == "eng") $Language = "en";

if(isset($Dateformat[$Lang]['date'])) $Date = $Dateformat[$Lang]['date'];
if(isset($Dateformat[$Lang]['datetime'])) $Datetime = $Dateformat[$Lang]['datetime'];
if(isset($Dateformat[$Lang]['time'])) $Time = $Dateformat[$Lang]['time'];

echo $this->Form->input('LanguageForPicker',array('type' => 'hidden','value' => $Language));
echo $this->Form->input('DateForPicker',array('type' => 'hidden','value' => $Date));
echo $this->Form->input('DateTimeForPicker',array('type' => 'hidden','value' => $Datetime));
echo $this->Form->input('TimeForPicker',array('type' => 'hidden','value' => $Time));
?>
<script>
$(function() {

		$(".date").datetimepicker({
			timepicker:		false,
			format:			$("#DateForPicker").val(),
			lang:				$("#GenerallyLanguageForPicker").val(),
			scrollInput:	false
		});

});
</script>
