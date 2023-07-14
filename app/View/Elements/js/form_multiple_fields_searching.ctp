<?php
$Lang = Configure::read('Config.language');
$Dateformat = Configure::read('Dateformat');

$Selectable = "<div class='custom_header'>" .  __('Selectable items',true) . " <span class='select_multi select_all icon_next icon_small' title='" . __('select all',true) . "'>" . __('select all',true) . "</span></div>";
$Selection = "<div class='custom_header'>" . __('Selection items',true) . " <span class='select_multi deselect_all icon_prev icon_small' title='" . __('deselect all',true) . "'>" . __('deselect all',true) . "</span></div>";
?>
<script>
$(function() {

    $("fieldset.multiple_field select").multiSelect({
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
