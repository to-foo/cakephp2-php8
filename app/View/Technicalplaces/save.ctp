<script type="text/javascript">
$(document).ready(function(){

if(!$("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		$("#<?php echo $this_id;?>").closest("div").css("background-image","url(img/indicator.gif)");
		$("#<?php echo $this_id;?>").closest("div").css("background-repeat","no-repeat");
		$("#<?php echo $this_id;?>").closest("div").css("background-position","95% 10%");
		$("#<?php echo $this_id;?>").closest("div").css("background-size","auto 1em");
}
if($("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		if($($("#<?php echo $this_id;?>").closest("div").find("legend")).length) {
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("padding-right","2em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-image","url(img/indicator.gif)");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-size","auto 1em");
		} else { 
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("padding-right","1.5em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-image","url(img/indicator.gif)");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-size","auto 1em");
		}
	}

function set(){

<?php if(isset($success)):?>
	if(!$("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		$("#<?php echo $this_id;?>").closest("div").css("background-image","url(img/icon_green_negativ_lock_check_single.png)");
		$("#<?php echo $this_id;?>").closest("div").css("background-repeat","no-repeat");
		$("#<?php echo $this_id;?>").closest("div").css("background-position","95% 10%");
		$("#<?php echo $this_id;?>").closest("div").css("background-size","auto 1em");
		$("#<?php echo $this_id;?>").closest("div").attr("title","<?php echo $success;?>");
	}
	if($("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		if($($("#<?php echo $this_id;?>").closest("div").find("legend")).length) {
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("padding-right","2em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-image","url(img/icon_green_negativ_lock_check_single.png)");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-size","auto 1em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").attr("title","<?php echo $success;?>");
		} else { 
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("padding-right","1.5em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-image","url(img/icon_green_negativ_lock_check_single.png)");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-size","auto 1em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").attr("title","<?php echo $success;?>");
		}
	}
	
	$("#<?php echo $this_id;?>").attr("disabled",false);
	$("#<?php echo $this_id . '_submit';?>").remove();
<?php endif?>
<?php if(isset($error)):?>
	if(!$("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		$("#<?php echo $this_id;?>").closest("div").css("background-image","url(img/icon_red_negativ_hint_single.png)");
		$("#<?php echo $this_id;?>").closest("div").css("background-repeat","no-repeat");
		$("#<?php echo $this_id;?>").closest("div").css("background-position","95% 10%");
		$("#<?php echo $this_id;?>").closest("div").css("background-size","auto 1em");
		$("#<?php echo $this_id;?>").closest("div").attr("title","<?php echo $error;?>");
	}
	if($("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		if($($("#<?php echo $this_id;?>").closest("div").find("legend")).length) {
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("padding-right","2em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-image","url(img/icon_red_negativ_hint_single.png)");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-size","auto 1em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").attr("title","<?php echo $error;?>");
			$("#<?php echo $this_id;?>").attr("checked", false);
			$("#<?php echo $last_id_for_radio . $last_value;?>").attr("checked", true);
			$("#<?php echo $this_id;?>").button("refresh");
		} else {
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("padding-right","1.5em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-image","url(img/icon_red_negativ_hint_single.png)");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-size","auto 1em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").attr("title","<?php echo $error;?>");
			$("#<?php echo $this_id;?>").attr("checked", <?php  echo ($last_value == 1) ? 'true' : 'false';?>);
			$("#<?php echo $this_id;?>").button("refresh");
		}
	}
	$("#<?php echo $this_id;?>").attr("disabled",false);
	$("#<?php echo $this_id . '_submit';?>").remove();
	$("#<?php echo $this_id;?>").val("<?php echo $last_value;?>");
	alert("<?php echo $error;?>");
<?php endif?>
<?php if(isset($closed)):?>
	if(!$("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		$("#<?php echo $this_id;?>").closest("div").css("background-image","url(img/icon_red_negativ_hint_single.png)");
		$("#<?php echo $this_id;?>").closest("div").css("background-repeat","no-repeat");
		$("#<?php echo $this_id;?>").closest("div").css("background-position","95% 10%");
		$("#<?php echo $this_id;?>").closest("div").css("background-size","auto 1em");
		$("#<?php echo $this_id;?>").closest("div").attr("title","<?php echo $closed;?>");
	}
	if($("#<?php echo $this_id;?>").closest("div").hasClass("radio")){
		if($($("#<?php echo $this_id;?>").closest("div").find("legend")).length) {
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("padding-right","2em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-image","url(img/icon_red_negativ_hint_single.png)");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("legend").css("background-size","auto 1em");
			$("#<?php echo $this_id;?>").closest("div").find("legend").attr("title","<?php echo $closed;?>");
			$("#<?php echo $this_id;?>").attr("checked", false);
			$("#<?php echo $this_id;?>").attr("checked", <?php  echo ($last_value == 1) ? 'true' : 'false';?>);
			$("#<?php echo $last_id_for_radio . $last_value;?>").attr("checked", true);
			$("#<?php echo $this_id;?>").button("refresh");
		} else {
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("padding-right","1.5em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-image","url(img/icon_red_negativ_hint_single.png)");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-repeat","no-repeat");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-position","center right");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").css("background-size","auto 1em");
			$("#<?php echo $this_id;?>").closest("div").find("span.ui-button-text").attr("title","<?php echo $closed;?>");
			$("#<?php echo $this_id;?>").attr("checked", <?php  echo ($last_value == 1) ? 'true' : 'false';?>);
			$("#<?php echo $this_id;?>").button("refresh");
		}
	}
	$("#<?php echo $this_id;?>").val("<?php echo $last_value;?>");
	$("#<?php echo $this_id;?>").attr("disabled",false);
	$("#<?php echo $this_id . '_submit';?>").remove();
	alert("<?php echo $closed;?>");
<?php endif?>
<?php if(isset($hint)):?>
	alert("<?php echo $hint;?>");
<?php endif?>
;}

setTimeout(set, 1000);
});
</script>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>