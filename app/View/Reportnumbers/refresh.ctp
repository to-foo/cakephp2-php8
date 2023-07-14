<?php if(isset($countdown)):?>
<script type="text/javascript">
	$("#for_report_countdown_value").hide();
	if($("#report_countdown_value").length === 0) {
		$('<span class="report_countdown" id="report_countdown_value">Test</span>').insertAfter('a#text_delete_report');
	}
	$("#report_countdown_value").html($("#for_report_countdown_value").text());

	<?php
	$countdown = intval($countdown);
	switch($countdown) {

		case ($countdown == 1):
		echo '$("#report_countdown_value").css("background-color","#ae1f21");';
		break;

		case ($countdown < $limit_orange):
		echo '$("#report_countdown_value").css("background-color","#ae1f21");';
		break;

		case ($countdown >= $limit_orange):
		echo '$("#report_countdown_value").css("background-color","#f29d21");';
		break;

		case ($countdown >= $limit_green):
		echo '$("#report_countdown_value").css("background-color","#5c8331");';
		break;

		default:
		echo '$("#report_countdown_value").css("background-color","#ae1f21");';
		break;
	}
	?>
</script>

<?php
if($countdown < 1){
	$countdown = __('Less than 1',true);
}
echo '<span class="for_report_countdown" id="for_report_countdown_value">';
echo __('Time to lock') . ': ' . $countdown . ' ' . __('min',true);
echo '</span>';
?>

<?php endif;?>
<?php if(isset($closereport) && $closereport == true):?>
<div id="refresh_dialog_window" class= "refresh_dialog" title="Schließung">
<h3><?php echo __('Prüfbericht wird geschlossen',true);?></h3>
<p class="konflikt">
<?php echo  __('ACHTUNG!',true) . ' ' . __('Dieser Prüfbericht wird geschlossen.',true);?>.
<br />
<br />
<?php echo __('Der Druckvorgang erfolgte',true).': '.$printtime;?>
<br />
<?php echo __('Das Zeitlimit betrug',true).': '.$limit. __(' min',true);?>
<br />
<br /><br />
<?php
echo $this->Html->Link(__('Close window',true),'javascript:',array('class' => 'close_window'));
?>
</p>
</div>

<?php if(!isset($lastURL)) $lastURL = null;?>

<script type="text/javascript">
window.clearTimeout(refreshTimer);
$("#refresh_dialog_window").dialog({
	modal: true,
	height: 300,
	width: 500
});
$(".close_window").click(function() {
	$(".refresh_dialog").dialog("destroy");
	$("#container").load("<?php echo $lastURL;?>", {"ajax_true": 1});

});
</script>
<?php return;?>
<?php endif;?>
<?php if(isset($stopmodal) && $stopmodal == true) return;?>
<?php if(isset($edit_conflict) && $edit_conflict == true):?>
<div id="refresh_dialog_window" class= "refresh_dialog" title="Bearbeitungskonflikt">
<h3><?php echo __('Bearbeitungskonflikt',true);?></h3>
<p class="konflikt">
<?php echo  __('WARNUNG!',true) . ' ' . __('Dieser Prüfbericht wurde eben bearbeitet von',true) . ' ' . $User['User']['name'];?>.
<br />
<?php
echo $this->Html->Link(__('Close window',true),'javascript:',array('class' => 'close_window'));
?>
</p>
</div>
<script type="text/javascript">
window.clearTimeout(refreshTimer);
$("#refresh_dialog_window").dialog({
	modal: true,
	height: 300,
	width: 500
});
$(".close_window").click(function() {
	$(".refresh_dialog").dialog("close");
	$("#container").load("<?php echo $lastURL;?>", {"ajax_true": 1});

});
</script>
<?php endif;?>
