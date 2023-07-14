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

$Selectable = "<div class='custom_header'>" .  __('Selectable items',true) . " <span class='select_multi select_all icon_next icon_small' title='" . __('select all',true) . "'>" . __('select all',true) . "</span></div>";
$Selection = "<div class='custom_header'>" . __('Selection items',true) . " <span class='select_multi deselect_all icon_prev icon_small' title='" . __('deselect all',true) . "'>" . __('deselect all',true) . "</span></div>";
?>

<script type="text/javascript">
$(document).ready(function() {

  var remove_user =  new Array();
  elmu = '<div class="message_info message_info_user"><span class="warning"><?php echo __('WARNING!',true);?> <?php echo __('Not selected users will be removed from all subordinated elements.',true);?></span></div>';

  $("fieldset.multiple_field select").multiSelect({
    selectableOptgroup: true,
    selectableHeader: "<?php echo $Selectable;?>",
    selectionHeader: "<?php echo $Selection;?>",
    afterDeselect: function(values){
      remove_user.push(values);
      if( $("div.message_info_user").length) $("div.message_info_user").remove();
      $(elmu).insertAfter("#dialog h2");
    }
  });

  $('span.select_multi').click(function() {

    parent_div = $(this).parents("div.select");
    this_select_id = "#" + $(parent_div).children("select").attr("id");

    if($(this).hasClass("select_all")) $(this_select_id).multiSelect("select_all");
    if($(this).hasClass("deselect_all")) $(this_select_id).multiSelect("deselect_all");

  });

  $("input.date").change(function() {

    elmd = '<div class="message_info message_info_date"><span class="warning"><?php echo __('WARNING!',true);?> <?php echo __('If the progress period is shortened, subordinate periods are adjusted.',true);?></span></div>';
    if( $("div.message_info_date").length) $("div.message_info_date").remove();
    $(elmd).insertAfter("#dialog h2");
  });

  $("input.warning_text").change(function() {

    if( $("div.message_info_cascade").length) $("div.message_info_cascade").remove();

    elmc = '<div class="message_info message_info_cascade"><span class="warning"><?php echo __('WARNING!',true);?> <?php echo __('This setting overwrites all subordinate values.',true);?></span></div>';
    val = $(this).val();

    if(val == 1) $(elmc).insertAfter("#dialog h2");
    if(val == 0) $("div.message_info_cascade").remove();

	});

  $("form.dialogform").bind("submit", function() {

		$("#AjaxSvgLoader").show();

		var data = $(this).serializeArray();

    data.push({name: "data[AdvancesCascade][remove_user]", value: remove_user});
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("action"),
			data	: data,
			success : function(data) {
		    	$("#dialog").html(data);
		    	$("#dialog").show();
					$("#AjaxSvgLoader").hide();
			}
		});

		return false;
	});
});
</script>
