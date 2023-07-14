<div class="modalarea">
<h2><?php echo __('Transfer values below to the current dropdown'); ?></h2>
<?php
echo '<div class="clear"></div>';
if(count($modelDatas) == 0){
	echo '<div class="hint"><p>';
	echo __('Es konnten keine zum Zielfeld unterschiedlichen Werte im Ursprungsfeld gefunden werden.');
	echo '</p></div></div>';
	echo $this->JqueryScripte->ModalFunctions();
	return;
}
echo '<div class="hint"><p>';
echo __('Die Werte des Feldes') . ' <b>'. $hint_array['old']['description'] . '/'. $hint_array['old']['verfahren'] . '</b> ';
echo __('in das Feld') . ' <b>' . $hint_array['new']['description'] . '/'. $hint_array['new']['verfahren'] . '</b> ' . __('kopieren') . '.<p>';
echo __('Es werden nur Werte übernommen, die im Zielfeld noch nicht vorhanden sind.',true) . '</p>';

if(isset($hint_array['new']['dependencies']) && is_array($hint_array['new']['dependencies']) && count($hint_array['new']['dependencies'] > 0)){
	echo '</p><p>';
	echo __('Das Feld') . ' ' . $hint_array['old']['description'] . '/'. $hint_array['old']['verfahren'] . ' ' . __('besitzt folgende abhängige Werte, die mit kopiert werden können.');
	echo '</p><ul>';
	
	foreach($hint_array['old']['dependencies'] as $_key => $_dependencies){
		echo '<li>';
		echo $_dependencies . ' (' . __('Ursprung') . ') > ' . $hint_array['new']['dependencies'][$_key] . ' (' . __('Ziel') . ')';
		echo '</li>';
	}
	
	echo '</ul><p>';
	echo __('Abhängige Werte ebenfalls kopieren?');
	echo '</p><p>';

	echo $this->Form->input(__('Werte kopieren',true), array(
    	'type' => 'checkbox', 
		'value' => 1,
		'legend' => false,
		'checked' => true,
		'id' => 'get_dependencies'
	));
}

echo '</p></div>';
echo '<div class="clear"></div>';
$this->request->projectvars['VarsArray'][11] = 1;
echo $this->Html->link(__('Transfer these values'), array_merge(array('action' => 'linking'),$this->request->projectvars['VarsArray']), array('class'=>'round','id'=>'linking_start'));

?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Value'); ?></th>
			<th><?php echo __('modified'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($modelDatas as $modelData):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo h($modelData[$Model]['discription']); ?>&nbsp;</td>
		<td><?php echo h($modelData[$Model]['modified']); ?>&nbsp;</td>
	</tr>
<?php endforeach; ?>
	</table>

</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#dialog').scrollTop(0);

		$("#get_dependencies").change(function(){
			if($("#get_dependencies").attr("checked") == "checked"){
				$("#get_dependencies").closest("div").find("span").text("<?php echo __('Abhängige Werte mit kopieren');?>");
			}
			else {
				$("#get_dependencies").closest("div").find("span").text("<?php echo __('Abhängige Werte nicht mit kopieren');?>");
			}

		});
		
		$("#linking_start").click(function(){
			
			var dependencies = 0;
			
			if($("#get_dependencies").attr("checked") == "checked"){
				dependencies = 1;
			}
			
			$("#dialog").load($(this).attr("href"), {
					"ajax_true": 1,
					"dependencies": dependencies
				})
			return false;
		});
		
	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>