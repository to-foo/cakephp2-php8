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

$Selectable = "<div class='custom_header'>" .  __('Selectable items',true) . "</div>";
$Selection = "<div class='custom_header'>" . __('Selection items',true) . "</div>";
?>
<script>
$(function() {

		$(".date").datetimepicker({
			format:				$("#DateForPicker").val(),
			timepicker:		false,
			lang:					$("#LanguageForPicker").val(),
			scrollInput:	false
		});

    $("fieldset.multiple_field_error select").multiSelect({
			selectableOptgroup: true,
			selectableHeader: "<?php echo $Selectable;?>",
		  selectionHeader: "<?php echo $Selection;?>",
			afterDeselect: function(values){
			}
		});

		$('span.select_multi').click(function() {

			parent_div = $(this).parents("div.select");
			this_select_id = "#" + $(parent_div).children("select").attr("id");

			if($(this).hasClass("select_all")) $(this_select_id).multiSelect("select_all");
			if($(this).hasClass("deselect_all")) $(this_select_id).multiSelect("deselect_all");

    });
});
</script>
