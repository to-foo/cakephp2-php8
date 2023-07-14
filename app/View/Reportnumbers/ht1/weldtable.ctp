<div class="modalarea">
<h2><?php echo __('Weld assistent', true); ?></h2> 
<div class="clear">
 <a href="javascript:" id="show_hide" class="round">Formular zeigen</a> 
 <a href="javascript:" id="weld_save" class="round">Daten speichern</a> 
 </div>
<div class="form inhalt clear">
<table cellpadding="0" cellspacing="0" class="weldassistent">
<tr>
<th>Nahtbezeichnung</th>
<th>Position</th>
<th>Messreihe</th>
</tr>
<?php 
	foreach($WeldSet as $_key => $_WeldSet){
		foreach($_WeldSet as $__key => $__WeldSet){
			foreach($__WeldSet as $___key => $___WeldSet){
				echo '<tr class="altrow">';
				echo '<td class="editable">';
				echo $___WeldSet['description'];
				echo '</td>';
				echo '<td class="editable">';
				echo $___WeldSet['position'];
				echo '</td>';
				echo '<td class="editable">';
				echo $___WeldSet['messreihen'];
				echo '</td>';
				echo '</tr>';
			}
		echo '<tr><td colspan="3"></td></tr>';
		}
		echo '<tr><td colspan="3"></td></tr>';
		echo '<tr><td colspan="3"></td></tr>';
	}
?>
</table>
<?php echo $this->Form->create('weldassistent',array('class' => 'weldassistent')); ?>
<legend>Naht-Assistent</legend> 
<fieldset class="extraarea weldassistent">
<?php
echo $this->ViewData->ShowWeldAssistentData($settings);


?>
</fieldset>
<?php
echo $this->Form->end();
?>
</div>
<div class="clear" id="testdiv"></div>
<div id="savediv"></div>
</div>
<script type="text/javascript">
	$(document).ready(function(){

		$("#closethismodal").click(function() {
			$("#dialog").dialog("close");
			return false;	
		});
		
		$("input[type=checkbox]").button();
		
		<?php
		if($show == 'table'){
			echo'$("form.weldassistent").hide();';
			echo'$("#show_hide").text("Formular zeigen");';
		}
		elseif($show == 'form'){
			echo'$("table.weldassistent").hide();';
			echo'$("#show_hide").text("Tabelle zeigen");';
		}
		?>
		
		$("a#show_hide").click(function() {
			$("form.weldassistent").toggle();
			$("table.weldassistent").toggle();
				if ($("table.weldassistent").is(':visible')) {
    				$("#show_hide").text("Formular zeigen");
				}
				else {
    				$("#show_hide").text("Tabelle zeigen");
				}
		});

				$(".editable").editable("<?php echo Router::url(array('action'=>'editable'));?>", { 
					indicator : "<img src=\'img/indicator.gif\'>",
					tooltip   : 'test',
					onblur : "submit",
					submitdata : {
						ajax_true: 1, 
					},
					cssclass : "editable",
					method : "POST",
				});

		$("form.weldassistent input, form.weldassistent select, form.weldassistent textarea").change(function() {

			var data = $("form.weldassistent").serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "dialog", value: 1});
			data.push({name: "show", value: 1});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: $("form.weldassistent").attr("action"),
				data	: data,
				success: function(data) {
		    		$("#dialog").html(data);
		    		$("#dialog").show();
				}
			});
			return false;
		});
		
		// allgemeines Formular
		$("form").bind("submit", function() {
							
			var data = $(this).serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "dialog", value: 1});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: $(this).attr("action"),
				data	: data,
				success: function(data) {
		    		$("#dialog").html(data);
		    		$("#dialog").show();
				}
			});
			return false;
		});

		$("#weld_save").click(function() {
			var table1 = $(this).serializeArray();
			var billingcounter = 0;
			var StopScript = 0;
			
			if($("#weldassistentDelMessreihen").attr("checked") == "checked"){
				check = confirm("Sie haben die Option \"Vorhandene Prüfbereiche löschen\" gewählt.\nSollen vorhandene Prüfbereiche gelöscht werden?");
				if (check == false) {
					return false;	
				}
				else {
					table1.push({name: "weldassistentDelMessreihen", value: 1});
				}
			}

			$("table.weldassistent tr.altrow td").each(function(index, value) {
				table1.push({name: "1t"+billingcounter, value: $(this).text()});
				billingcounter++;
			});		

			table1.push({name: "ajax_true", value: 1});
			table1.push({name: "save_welds", value: 1});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $("form.weldassistent").attr("action"),
				data	: table1,
				success: function(data) {
					$("#dialog").html(data);
					$("#dialog").show();
				}
			});
		});
	});
</script>
